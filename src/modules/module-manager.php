<?php
/**
 * Module manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use awsmug\Torro_Forms\Error;

/**
 * Class for managing modules.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\Modules\Actions\Module         actions()
 * @method awsmug\Torro_Forms\Modules\Form_Settings\Module   form_settings()
 * @method awsmug\Torro_Forms\Modules\Result_Handlers\Module result_handlers()
 * @method Leaves_And_Love\Plugin_Lib\Options                options()
 * @method Leaves_And_Love\Plugin_Lib\Assets                 assets()
 * @method Leaves_And_Love\Plugin_Lib\AJAX                   ajax()
 * @method awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager  forms()
 */
class Module_Manager extends Service {
	use Container_Service_Trait {
		Container_Service_Trait::__call as private __callService;
	}

	/**
	 * Registered modules.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Default modules definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $default_modules = array();

	/**
	 * Whether the hooks for the service have been added.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var bool
	 */
	protected $hooks_added = false;

	/**
	 * The Option API service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_options = 'Leaves_And_Love\Plugin_Lib\Options';

	/**
	 * Assets service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_assets = 'Leaves_And_Love\Plugin_Lib\Assets';

	/**
	 * AJAX service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_ajax = 'Leaves_And_Love\Plugin_Lib\AJAX';

	/**
	 * The form manager service definition.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @static
	 * @var string
	 */
	protected static $service_forms = 'awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager';

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix   The instance prefix.
	 * @param array  $services {
	 *     Array of service instances.
	 *
	 *     @type Leaves_And_Love\Plugin_Lib\Options       $options       The Option API instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Assets        $assets        The Assets API instance.
	 *     @type Leaves_And_Love\Plugin_Lib\AJAX          $ajax          The AJAX API instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler $error_handler The error handler instance.
	 * }
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );

		$this->default_modules = array(
			'actions'         => 'awsmug\Torro_Forms\Modules\Actions\Module',
			'form_settings'   => 'awsmug\Torro_Forms\Modules\Form_Settings\Module',
			'result_handlers' => 'awsmug\Torro_Forms\Modules\Result_Handlers\Module',
		);

		//TODO: register default modules
	}

	/**
	 * Magic call method.
	 *
	 * Supports retrieval of a module or an internally used service.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $method_name Method name. Should be the name of a service.
	 * @param array  $args        Method arguments. Unused here.
	 * @return awsmug\Torro_Forms\Modules\Module|Leaves_And_Love\Plugin_Lib\Service|null The module, service instance, or null
	 *                                                                                   if neither exist.
	 */
	public function __call( $method_name, $arguments ) {
		if ( isset( $this->modules[ $method_name ] ) ) {
			return $this->modules[ $method_name ];
		}

		return $this->__callService( $method_name, $arguments );
	}

	/**
	 * Returns a specific registered module.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Module slug.
	 * @return awsmug\Torro_Forms\Modules\Module|awsmug\Torro_Forms\Error Module instance, or error object if module is not registered.
	 */
	public function get( $slug ) {
		if ( ! isset( $this->modules[ $slug ] ) ) {
			return new Error( $this->get_prefix() . 'module_not_exist', sprintf( __( 'A module with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		return $this->modules[ $slug ];
	}

	/**
	 * Returns all registered modules.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$slug => $module_instance` pairs.
	 */
	public function get_all() {
		return $this->modules;
	}

	/**
	 * Returns all active modules.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$slug => $module_instance` pairs.
	 */
	public function get_all_active() {
		$options = $this->options()->get( 'general_settings', array() );
		if ( isset( $options['modules'] ) && is_array( $options['modules'] ) ) {
			return array_intersect_key( $this->modules, array_flip( $options['modules'] ) );
		}

		return $this->modules;
	}

	/**
	 * Registers a new module.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug              Module slug.
	 * @param string $module_class_name Module class name.
	 * @return bool|awsmug\Torro_Forms\Error True on success, error object on failure.
	 */
	public function register( $slug, $module_class_name ) {
		if ( isset( $this->modules[ $slug ] ) ) {
			return new Error( $this->get_prefix() . 'module_already_exist', sprintf( __( 'A module with the slug %s already exists.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( ! class_exists( $module_class_name ) ) {
			return new Error( $this->get_prefix() . 'module_class_not_exist', sprintf( __( 'The class %s does not exist.', 'torro-forms' ), $module_class_name ), __METHOD__, '1.0.0' );
		}

		if ( ! is_subclass_of( $module_class_name, Module::class ) ) {
			return new Error( $this->get_prefix() . 'module_class_not_allowed', sprintf( __( 'The class %s is not allowed for a module.', 'torro-forms' ), $module_class_name ), __METHOD__, '1.0.0' );
		}

		$this->modules[ $slug ] = new $module_class_name( $this->get_prefix(), array(
			'manager'       => $this,
			'error_handler' => $this->error_handler(),
		) );

		return true;
	}

	/**
	 * Unregisters a new module.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $slug Module slug.
	 * @return bool|awsmug\Torro_Forms\Error True on success, error object on failure.
	 */
	public function unregister( $slug ) {
		if ( ! isset( $this->modules[ $slug ] ) ) {
			return new Error( $this->get_prefix() . 'module_not_exist', sprintf( __( 'A module with the slug %s does not exist.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		if ( isset( $this->default_modules[ $slug ] ) ) {
			return new Error( $this->get_prefix() . 'module_is_default', sprintf( __( 'The default module %s cannot be unregistered.', 'torro-forms' ), $slug ), __METHOD__, '1.0.0' );
		}

		unset( $this->modules[ $slug ] );

		return true;
	}

	/**
	 * Adds the service hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function add_hooks() {
		if ( $this->hooks_added ) {
			return false;
		}

		foreach ( $this->get_all_active() as $module ) {
			$module->add_hooks();
		}

		$this->hooks_added = true;

		return true;
	}

	/**
	 * Removes the service hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function remove_hooks() {
		if ( ! $this->hooks_added ) {
			return false;
		}

		foreach ( $this->get_all_active() as $module ) {
			$module->remove_hooks();
		}

		$this->hooks_added = false;

		return true;
	}
}
<?php
/**
* @package WordPress
* @subpackage SurveyVal
* @author Sven Wagener
* @copyright 2014, Rheinschmiede
* @link http://rheinschmiede.de
* @license http://www.opensource.org/licenses/gpl-2.0.php GPL License
*/

// No direct access is allowed
if( ! defined( 'ABSPATH' ) ) exit;

class SurveyVal_SurveyElement_RangeEmotional extends SurveyVal_SurveyElement{
	
	public function __construct( $id = null ){
		$this->slug = 'RangeEmotional';
		$this->title = __( 'Range Emotional', 'surveyval-locale' );
		$this->description = __( 'Add a question which can be answered within a  emotional range scale.', 'surveyval-locale' );
		$this->icon = SURVEYVAL_URLPATH . '/assets/images/icon-range-emotional.png';
		
		$this->answer_syntax = '<input type="range" min="0" max="1000" name="%s" value="%s" />';
		$this->answer_params = array( 'name', 'value' );
		
		parent::__construct( $id );
	}
	
	public function settings_fields(){
		$this->settings_fields = array(
			'description' => array(
				'title'			=> __( 'Description', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'The description will be shown after the question.', 'surveyval-locale' ),
				'default'		=> ''
			),
			'range_from' => array(
				'title'			=> __( 'Range from', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the beginning of the scale.', 'surveyval-locale' ),
				'default'		=> 'Good'
			), 
			'range_to' => array(
				'title'			=> __( 'Range to', 'surveyval-locale' ),
				'type'			=> 'text',
				'description' 	=> __( 'This value will be shown at the end of the scale', 'surveyval-locale' ),
				'default'		=> 'Bad'
			),
			'show_value' => array(
				'title'			=> __( 'Show selected Value', 'surveyval-locale' ),
				'type'			=> 'radio',
				'description' 	=> __( 'Showing the selected value in a box after the field.', 'surveyval-locale' ),
				'values'		=> array(
					'yes'	=> __( 'Yes', 'surveyval-locale' ),
					'no'	=> __( 'No', 'surveyval-locale' ),
				),
				'default'		=> 'no'
			), 
		);
	}
	
	public function before_answer(){
		$html.= '<span class="surveyval-range-from">' . $this->settings['range_from'] . '</span> ';
		return $html;
	}
	
	public function after_answer(){
		$html.= ' <span class="surveyval-range-to">' . $this->settings['range_to'] . '</span>';
		
		if( 'yes' == $this->settings[ 'show_value' ] ):
			$field_id = 'surveyval-range-empotional-show-value-' . $this->id;
			
			$html.= '<div class="surveyval-range-empotional-show-value" id="' . $field_id . '"></div>';
			
			$html.= '<script language="javascript">
			jQuery(function ($){
				var emotional_input = $(\'input[name=surveyval_response\\\[' . $this->id . '\\\]]\');
				var show_field = $( "#' . $field_id . '" );
				
				show_field.text( emotional_input.val() );
				
				emotional_input.change( function(){
					show_field.text( emotional_input.val() );
				});
			});
			</script>';
			// $html.= '<pre>' . print_r( $this, TRUE ) . '</pre>';
		endif;
		
		return $html;
	}	
	
	public function validate( $input ){
		return TRUE;
	}
	
	public function after_question(){
		if( !empty( $this->settings[ 'description' ] ) ):
			$html = '<p class="surveyval-element-description">';
			$html.= $this->settings[ 'description' ];
			$html.= '</p>';
		endif;
		
		return $html;
	}
}
sv_register_survey_element( 'SurveyVal_SurveyElement_RangeEmotional' );






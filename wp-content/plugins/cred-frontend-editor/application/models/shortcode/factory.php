<?php

/**
 * Class CRED_Shortcode_Factory
 *
 * @since m2m
 */
class CRED_Shortcode_Factory {
	
	/**
	 * @var CRED_Frontend_Form_Flow
	 */
	private $frontend_form_flow;
	
	/**
	 * @var Toolset_Shortcode_Attr_Interface
	 */
	private $attr_item_chain;
	
	/**
	 * @var array
	 */
	private $helpers;
	
	public function __construct(
		CRED_Frontend_Form_Flow $frontend_form_flow, 
		Toolset_Shortcode_Attr_Interface $attr_item_chain,
		array $helpers
	) {
		$this->frontend_form_flow = $frontend_form_flow;
		$this->attr_item_chain = $attr_item_chain;
		$this->helpers = $helpers;
	}

	/**
	 * @param $shortcode
	 *
	 * @return false|CRED_Shortcode_Base_View
	 */
	public function get_shortcode( $shortcode ) {
		switch( $shortcode ) {
			// Form elements, type agnostic
			case CRED_Shortcode_Form_Submit::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Form_Submit( $this->frontend_form_flow );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			case CRED_Shortcode_Form_Cancel::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Form_Cancel( $this->frontend_form_flow );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			case CRED_Shortcode_Form_Feedback::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Form_Feedback( $this->frontend_form_flow );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			// Association form elements:
			// Association form
			case CRED_Shortcode_Association_Form::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Form( $this->frontend_form_flow );
				if ( $shortcode_object->condition_is_met() ) {
					return new CRED_Shortcode_Base_View( $shortcode_object );
				}
				return new CRED_Shortcode_Base_View( new CRED_Shortcode_Empty() );
			// Association form link
			case CRED_Shortcode_Association_Form_Link::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Form_Link( $this->attr_item_chain );
				if ( $shortcode_object->condition_is_met() ) {
					return new CRED_Shortcode_Base_View( $shortcode_object );
				}
				return new CRED_Shortcode_Base_View( new CRED_Shortcode_Empty() );
			// Association form elements
			case CRED_Shortcode_Association_Form_Container::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Form_Container( $this->helpers['association'] );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			case CRED_Shortcode_Association_Title::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Title( $this->helpers['association'] );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			case CRED_Shortcode_Association_Field::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Field( $this->helpers['association'] );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			case CRED_Shortcode_Association_Role::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Association_Role( $this->helpers['association'] );
				return new CRED_Shortcode_Base_View( $shortcode_object );
			
			case CRED_Shortcode_Delete_Association::SHORTCODE_NAME:
				$shortcode_object = new CRED_Shortcode_Delete_Association( $this->attr_item_chain );
				if ( $shortcode_object->condition_is_met() ) {
					$shortcode_gui = new CRED_Shortcode_Delete_Association_GUI();
					return new CRED_Shortcode_Base_View( $shortcode_object );
				}
				return new CRED_Shortcode_Base_View( new CRED_Shortcode_Empty() );
		}

		return false;
	}
}
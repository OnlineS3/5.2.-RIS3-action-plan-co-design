<?php

/**
 * Main shortcodes controller for Toolset Forms.
 * 
 * @since m2m
 */
final class CRED_Shortcodes {

	public function initialize() {
		
		$frontend_form_flow = new CRED_Frontend_Form_Flow();
		$relationship_service = new Toolset_Relationship_Service();
		$attr_item_chain = new Toolset_Shortcode_Attr_Item_From_Views( 
			new Toolset_Shortcode_Attr_Item_M2M(
				new Toolset_Shortcode_Attr_Item_Legacy(
					new Toolset_Shortcode_Attr_Item_Id(),
					$relationship_service
				),
				$relationship_service
			),
			$relationship_service
		);
		
		$helpers = array(
			'association' => new CRED_Shortcode_Association_Helper( $frontend_form_flow, $relationship_service, $attr_item_chain )
		);
		
		$factory = new CRED_Shortcode_Factory( $frontend_form_flow, $attr_item_chain, $helpers );
		
		$association_shortcodes = array(
			// Form elements, type agnostic
			CRED_Shortcode_Form_Submit::SHORTCODE_NAME,
			CRED_Shortcode_Form_Cancel::SHORTCODE_NAME,
			CRED_Shortcode_Form_Feedback::SHORTCODE_NAME, 
			// Association form elements:
			// Association form
			CRED_Shortcode_Association_Form::SHORTCODE_NAME, 
			// Association form link
			CRED_Shortcode_Association_Form_Link::SHORTCODE_NAME, 
			// Association form elements
			CRED_Shortcode_Association_Form_Container::SHORTCODE_NAME, 
			CRED_Shortcode_Association_Title::SHORTCODE_NAME, 
			CRED_Shortcode_Association_Field::SHORTCODE_NAME, 
			CRED_Shortcode_Association_Role::SHORTCODE_NAME,
			// Delete association shortcode
			CRED_Shortcode_Delete_Association::SHORTCODE_NAME
		);
		
		foreach ( $association_shortcodes as $shortcode_string ) {
			if ( $shortcode = $factory->get_shortcode( $shortcode_string ) ) {
				add_shortcode( $shortcode_string, array( $shortcode, 'render' ) );
			};
		}
		
	}

}
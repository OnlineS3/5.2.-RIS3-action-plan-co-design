<?php

class CRED_Ajax_Handler_Get_Shortcode_Attributes extends Toolset_Ajax_Handler_Abstract{

	function process_call( $arguments ) {
		$this->ajax_begin( 
			array( 
				'nonce' => CRED_Ajax::CALLBACK_GET_SHORTCODE_ATTRIBUTES, 
				'parameter_source' => 'get', 
				'is_public' => true 
			) 
		);

		$shortcode = toolset_getget( 'shortcode' );
		
		if ( empty( $shortcode ) ) {
			$this->ajax_finish( array( 'message' => __( 'Wrong or missing shortcode.', 'wp-cred' ) ), false );
		}
		
		$shortcodes_with_attributes = apply_filters( 'cred_shortcodes_with_data', array() );
		
		if ( ! array_key_exists( $shortcode, $shortcodes_with_attributes ) ) {
			$this->ajax_finish( array( 'message' => __( 'Not registered shortcode.', 'wp-cred' ) ), false );
		}
		
		$shortcode_data = $shortcodes_with_attributes[ $shortcode ];
		
		if ( 
			! isset( $shortcode_data['callback'] )
			|| ! is_callable( $shortcode_data['callback'] )
		) {
			$this->ajax_finish( array( 'message' => __( 'Could not load shortcode attributes.', 'wp-cred' ) ), false );
		}
		
		$parameters = toolset_getget( 'parameters', array() );
		$overrides = toolset_getget( 'overrides', array() );
		$pagenow = toolset_getget( 'credPagenow' );
		$page = toolset_getget( 'credPage' );
		
		$results = call_user_func( $shortcode_data['callback'], $parameters, $overrides, $pagenow, $page );
		
		$this->ajax_finish( $results, true );
	}
	
}
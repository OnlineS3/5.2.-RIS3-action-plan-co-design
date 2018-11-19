<?php

/**
 * Class responsible to register the entry point when an AJAXed Form is submitted
 *
 * @since 1.9.4
 */
class CRED_Form_Ajax_Init {

	public function initialize() {
		add_action( 'template_redirect', array( $this, 'register_entry_point' ), 10 );
	}

	/**
	 * When Forms are submitted we need to register a dedicated entry point in order to
	 * re-create the saved form. We need to have at least a Form submition
	 *
	 * @return bool
	 */
	public function register_entry_point() {

		if ( ! is_admin() ) {
			if ( isset( $_POST )
				&& ! empty( $_POST )
				&& array_key_exists( CRED_StaticClass::PREFIX . 'form_id', $_POST )
				&& array_key_exists( CRED_StaticClass::PREFIX . 'form_count', $_POST )
			) {
				return CRED_Form_Builder::initialize()->get_form( false, false );
			}
		}
	}
}
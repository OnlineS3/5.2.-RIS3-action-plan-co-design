<?php

/**
 * Handler for the cred_get_available_forms filter API.
 *
 * @since m2m
 */
class CRED_Api_Handler_Get_Available_Forms extends CRED_Api_Handler_Abstract implements CRED_Api_Handler_Interface {
	


	public function __construct() { }

	/**
	 * In the case of post nd user forms, this filter returns an array with two main entries, 'new' and 'edit', 
	 * holding the forms to create a new or to edit a specific object type.
	 *
	 * For association forms, the array contains the existing forms as top level elements.
	 *
	 * Each form is returned as an object with the following properties:
	 * - ID
	 * - post_title
	 * - post_name
	 *
	 * @param array $arguments Original action/filter arguments.
	 *
	 * @return array
	 */
	function process_call( $arguments ) {

		$domain = toolset_getarr( $arguments, 1 );
		
		if ( ! array_key_exists( $domain, $this->domain_data ) ) {
			return array();
		}
		
		$cached = get_transient( $this->domain_data[ $domain ]['transient'] );
		
		if ( false !== $cached ) {
			return $cached;
		}
		
		switch ( $domain ) {
			case CRED_Form_Domain::POSTS:
			case CRED_Form_Domain::USERS:
				return $this->generate_new_edit_transient( $domain );
				break;
			case CRED_Form_Domain::ASSOCIATIONS:
				return $this->generate_shared_transient( $domain );
				break;
		}

		return array();
	}
	
	/**
	 * Generate the transient for post or user forms.
	 *
	 * Note that those forms are stored with a status of "private".
	 *
	 * @since m2m
	 */
	private function generate_new_edit_transient( $domain ) {
		global $wpdb;
		$forms_transient_to_update = array(
			'new'	=> array(),
			'edit'	=> array()
		);
		
		$forms_available = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT ID, post_title, post_name FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_status in ('private') 
				ORDER BY post_title",
				$this->domain_data[ $domain ]['post_type']
			)
		);
		
		foreach ( $forms_available as $form_candidate ) {
			$current_form_type = 'new';
			$form_settings = (array) get_post_meta( $form_candidate->ID, '_cred_form_settings', true );
			if (
				! is_array( $form_settings ) 
				|| empty( $form_settings ) 
				|| ! array_key_exists( 'form', $form_settings ) 
				|| ! array_key_exists( 'type', $form_settings['form'] )
			) {
				continue;
			}
			$current_form_type = $form_settings['form']['type'];
			$forms_transient_to_update[ $current_form_type ][] = $form_candidate;
		}
		
		set_transient( $this->domain_data[ $domain ]['transient'] , $forms_transient_to_update, WEEK_IN_SECONDS );
		
		return $forms_transient_to_update;
	}
	
	/**
	 * Generate the transient for association forms.
	 *
	 * Note that those forms are stored with a status of "publish".
	 *
	 * @since m2m
	 */
	private function generate_shared_transient( $domain ) {
		global $wpdb;
		
		$forms_available = $wpdb->get_results(
			$wpdb->prepare( 
				"SELECT ID, post_title, post_name FROM {$wpdb->posts}
				WHERE post_type = %s
				AND post_status in ('publish') 
				ORDER BY post_title",
				$this->domain_data[ $domain ]['post_type']
			)
		);
		
		set_transient( $this->domain_data[ $domain ]['transient'] , $forms_available, WEEK_IN_SECONDS );
		
		return $forms_available;
	}
	
}
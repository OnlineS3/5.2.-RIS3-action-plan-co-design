<?php

/**
 * Caching system for Toolset CRED_Cache
 *
 * Currently, used to invalidate the cache of the known, published Toolset Forms,
 * used by the Toolset Forms shortcode generator.
 *
 * @since 1.9.3
 */
class CRED_Cache {
	
	const POST_FORMS_TRANSIENT_KEY = 'cred_transient_published_post_forms';
	const USER_FORMS_TRANSIENT_KEY = 'cred_transient_published_user_forms';
	const ASSOCIATION_FORMS_TRANSIENT_KEY = 'cred_transient_published_rel_forms';
	
	public function initialize() {
		
		add_action( 'save_post',	array( $this, 'delete_shortcodes_gui_transients_action' ), 10, 2 );
		add_action( 'delete_post',	array( $this, 'delete_shortcodes_gui_transients_action' ), 10 );
		
		add_action( 'user_register',				array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'profile_update',				array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'delete_user',					array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'added_user_meta',				array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'updated_user_meta',			array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'deleted_user_meta',			array( $this, 'delete_transient_usermeta_keys' ) );
		
		add_action( 'types_fields_group_saved',		array( $this, 'delete_transient_usermeta_keys' ) );
		
		add_action( 'wpcf_save_group',				array( $this, 'delete_transient_usermeta_keys' ) );
		add_action( 'wpcf_group_updated',			array( $this, 'delete_transient_usermeta_keys' ) );
		
	}
	
	/**
	 * Invalidate cred_transient_published_*** cache when:
	 * 	creating, updating or deleting a post form
	 * 	creating, updating or deleting an user form
	 *
	 *
	 * @since 1.9.3
	 */
	
	function delete_shortcodes_gui_transients_action( $post_id, $post = null  ) {
		if ( is_null( $post ) ) {
			$post = get_post( $post_id );
			if ( is_null( $post ) ) {
				return;
			}
		}
		$slugs = array( 'cred-form', 'cred-user-form', CRED_Association_Form_Main::ASSOCIATION_FORMS_POST_TYPE );
		if ( ! in_array( $post->post_type, $slugs ) ) {
			return;
		}
		switch ( $post->post_type ) {
			case 'cred-form':
				delete_transient( 'cred_transient_published_post_forms' );
				break;
			case 'cred-user-form':
				delete_transient( 'cred_transient_published_user_forms' );
				break;
			case CRED_Association_Form_Main::ASSOCIATION_FORMS_POST_TYPE:
				delete_transient( 'cred_transient_published_rel_forms' );
				break;
		}
	}
	
	function delete_transient_usermeta_keys() {
		delete_transient( 'cred_transient_usermeta_keys_visible512' );
		delete_transient( 'cred_transient_usermeta_keys_all512' );
	}
	
}
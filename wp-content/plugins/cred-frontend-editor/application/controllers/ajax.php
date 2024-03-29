<?php

class CRED_Ajax extends Toolset_Ajax {

	const HANDLER_CLASS_PREFIX = 'CRED_Ajax_Handler_';

	// Action names
	const CALLBACK_CREATE_ASSOCIATION_FORM = 'association_form_add_new';

	const CALLBACK_EDIT_ASSOCIATION_FORM = 'association_form_edit';

	const CALLBACK_DELETE_ASSOCIATION_FORM = 'association_form_delete';

	const CALLBACK_DUPLICATE_ASSOCIATION_FORM = 'association_form_duplicate';

	const CALLBACK_GET_RELATIONSHIP_FIELDS = 'get_relationship_fields';

	
	const CALLBACK_ASSOCIATION_FORM_AJAX_SUBMIT = 'association_form_ajax_submit';

	const CALLBACK_ASSOCIATION_FORM_AJAX_FIND_ROLE = 'association_form_ajax_role_find';

	const CALLBACK_GET_SHORTCODE_ATTRIBUTES = 'get_shortcode_attributes';
	
	const CALLBACK_DISMISS_ASSOCIATION_SHORTCODE_INSTRUCTIONS = 'dismiss_association_shortcode_instructions';
	
	const CALLBACK_GET_ASSOCIATION_FORM_DATA = 'get_association_form_data';
	
	const CALLBACK_CREATE_FORM_TEMPLATE = 'create_form_template';
	
	const CALLBACK_DELETE_ASSOCIATION = 'delete_association';
	
	private static $callbacks = array(
		self::CALLBACK_CREATE_ASSOCIATION_FORM,
		self::CALLBACK_EDIT_ASSOCIATION_FORM,
		self::CALLBACK_DELETE_ASSOCIATION_FORM,
		self::CALLBACK_DUPLICATE_ASSOCIATION_FORM,
		self::CALLBACK_GET_RELATIONSHIP_FIELDS
	);
	
	private static $public_callbacks = array(
		self::CALLBACK_ASSOCIATION_FORM_AJAX_SUBMIT,
		self::CALLBACK_ASSOCIATION_FORM_AJAX_FIND_ROLE,
		self::CALLBACK_GET_SHORTCODE_ATTRIBUTES,
		self::CALLBACK_DISMISS_ASSOCIATION_SHORTCODE_INSTRUCTIONS,
		self::CALLBACK_GET_ASSOCIATION_FORM_DATA,
		self::CALLBACK_CREATE_FORM_TEMPLATE,
		self::CALLBACK_DELETE_ASSOCIATION
	);


	// This will be neccessary after toolsetcommon-315 is merged.
	private static $cred_instance;


	public static function get_instance() {
		if( null === self::$cred_instance ) {
			self::$cred_instance = new self();
		}
		return self::$cred_instance;
	}

	/**
	 * @inheritdoc
	 *
	 * @param bool $capitalized
	 *
	 * @return string
	 * @since 2.0
	 */
	protected function get_plugin_slug( $capitalized = false ) {
		return ( $capitalized ? 'CRED' : 'cred' );
	}


	/**
	 * @inheritdoc
	 * @return array
	 * @since 2.0
	 */
	protected function get_callback_names() {
		return self::$callbacks;
	}
	
	/**
	 * @inheritdoc
	 * @return array
	 * @since m2m
	 */
	protected function get_public_callback_names() {
		return self::$public_callbacks;
	}


	/**
	 * Handles all initialization of everything except AJAX callbacks itself that is needed when
	 * we're DOING_AJAX.
	 *
	 * Since this is executed on every AJAX call, make sure it's as lightweight as possible.
	 *
	 * @since 2.1
	 */
	protected function additional_ajax_init() {
		$cred_ajax_media_upload_fix = new CRED_Ajax_Media_Upload_Fix();
		$cred_ajax_media_upload_fix->initialize();
		$cred_ajax_init = new CRED_Form_Ajax_Init();
		$cred_ajax_init->initialize();
	}
}
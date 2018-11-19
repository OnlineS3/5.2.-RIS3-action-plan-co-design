<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Performs a check if plugin upgrade requires some changes
 */
function mrp_upgrade_check() {
	
	// Check if we need to do an upgrade from a previous version
	$previous_plugin_version = get_option( MRP_Multi_Rating::VERSION_OPTION );
	
	if ( ! isset( $previous_plugin_version ) ) {
		//return;
	}
	
	if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 3.1 ) {
		mrp_upgrade_to_3_1();
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 3.2 ) {
		mrp_upgrade_to_3_2();
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 4 ) {
		mrp_upgrade_to_4_0();
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 4.1 ) {
		mrp_upgrade_to_4_1();
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 5 ) {
		mrp_upgrade_to_5_0();
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 5.2 ) {
		mrp_upgrade_to_5_2();
	}

	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 5.3 ) {
		MRP_Multi_Rating::activate_plugin(); // to add default capabilitites to roles
	}
	
	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 5.4 ) {
		MRP_Multi_Rating::activate_plugin(); // increases max comment length to 2000 characters
	}

	if ( $previous_plugin_version != MRP_Multi_Rating::VERSION && $previous_plugin_version < 5.5 ) {
		mrp_upgrade_to_5_5(); // GDPR removed ip address tracking used for duplicate checking
	}
	
	update_option( MRP_Multi_Rating::VERSION_OPTION, MRP_Multi_Rating::VERSION ); // latest version upgrade complete
}


/**
 * Remove IP address db column in rating entries table. This is no longer used for duplicate 
 * checks to ensure GDPR compliance.
 */
function mrp_upgrade_to_5_5() {
	global $wpdb;
	$query = 'ALTER TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' DROP COLUMN ip_address';
	$wpdb->query( $query );

	// if the duplicate checking currently uses IP addresses, change the option to use cookies instead
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$duplicate_check_methods = $general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION];
	if ( in_array( 'ip_address', $duplicate_check_methods ) ) {
		$general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION] = array( 'cookie' );
	}
	update_option( MRP_Multi_Rating::GENERAL_SETTINGS,  $general_settings );
}


/**
 * Upgrades plugin to v5.2
 */
function mrp_upgrade_to_5_2() {
	
	MRP_Multi_Rating::activate_plugin();  // for db schema changes e.g. not applicable rating items
	
	$license_key = get_option( 'mrp_license_key' );
	
	if ( isset( $license_key ) && strlen( trim( $license_key ) ) > 0 ) {
		
		$license_settings = array(
				MRP_Multi_Rating::LICENSE_KEY_OPTION => $license_key
		);
		
		update_option( MRP_Multi_Rating::LICENSE_SETTINGS,  $license_settings );
		delete_option( 'mrp_license_key' );
	}
	
}

/**
 * Upgrades plugin to v5.0
 */
function mrp_upgrade_to_4_1() {

	MRP_Multi_Rating::activate_plugin(); // for db schema changes
	
}


/**
 * Upgrades plugin to v5.0
 */
function mrp_upgrade_to_5_0() {

	try {
		
		global $wpdb;
		$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME );
		
		MRP_Multi_Rating::activate_plugin(); // for db schema changes, create new table...
	
		// insert rows into new rating form item table
		foreach ( $rows as $rating_form ) {
			
			$rating_form_id = $rating_form->rating_form_id;
			
			// rating items
			if ( isset( $rating_form->rating_items ) && strlen( trim( $rating_form->rating_items ) ) > 0 ) {
	
				// get required and weight values for all rating items
				$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME;
				$rows = $wpdb->get_results( $query );
					
				$old_rating_items_data = array();
				foreach ( $rows as $row ) {
					$old_rating_items_data[$row->rating_item_id] = array(
							'weight' => $row->weight,
							'required' => $row->required
					);
				}
				
				$query = 'INSERT INTO ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME
						. ' ( rating_form_id, item_id, item_type, required, weight ) VALUES';
				$values = array();
				
				$rating_items = explode( ',', $rating_form->rating_items );
				foreach ( $rating_items as $rating_item_id ) {	
					$values[] = $wpdb->prepare( '( %d, %d, %s, %d, %f )', $rating_form_id, $rating_item_id, 'rating-item', 
							$old_rating_items_data[$rating_item_id]['required'], $old_rating_items_data[$rating_item_id]['weight'] );
				}
				
				$query .= implode( ",\n", $values );
				$wpdb->query( $query );
				
				$wpdb->show_errors();
				
			}
			
			// custom fields
			if ( isset( $rating_form->custom_fields ) && strlen( trim( $rating_form->custom_fields ) ) > 0 ) {
				
				// get required and weight values for all rating items
				$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME;
				$rows = $wpdb->get_results( $query );
					
				$old_custom_fields_data = array();
				foreach ( $rows as $row ) {
					$old_custom_fields_data[$row->custom_field_id] = array(
							'required' => $row->required
					);
				}
				
				$query = 'INSERT INTO ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME
				. ' ( rating_form_id, item_id, item_type, required ) VALUES';
				$values = array();
				
				$custom_fields = explode( ',', $rating_form->custom_fields );
				foreach ( $custom_fields as $custom_field_id ) {
					$values[] = $wpdb->prepare( '( %d, %d, %s, %d )', $rating_form_id, $custom_field_id, 'custom-field',
							$old_custom_fields_data[$custom_field_id]['required'] );
				}
				
				$query .= implode( ",\n", $values );
				$wpdb->query( $query );
				
				$wpdb->show_errors();
			}
		}
	} catch ( Exception $e ) {
		// do nothing
	}
	
	/**
	 * Migrate settings
	 */
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
	$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );
	$email_settings = (array) get_option( MRP_Multi_Rating::EMAIL_SETTINGS );
	$styles_settings = (array) get_option( MRP_Multi_Rating::STYLES_SETTINGS );
	
	$old_comment_settings = (array) get_option( 'mrp_comments_settings' );
	$old_moderation_settings = (array) get_option( 'mrp_moderation_settings');
	$old_position_settings = (array) get_option( 'mrp_position_settings' );
	$old_filter_settings = (array) get_option( 'mrp_filter_settings' );
	
	/* 
	 * General settings to Auto Placement settings:
	 * - enabled post types
	 */
	if ( isset( $general_settings[MRP_Multi_Rating::POST_TYPES_OPTION] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::POST_TYPES_OPTION] = $general_settings[MRP_Multi_Rating::POST_TYPES_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::POST_TYPES_OPTION] ); for backwards compat
	}
	 
	 
	/* General settings to Advanced settings
	 * - rating algorithm
	 * - template strip newlines
	 * - disallowed user roles
	 * - hide rating form on submit
	 * - hide post meta box
	 */
	if ( isset( $general_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION] ) ) {
		$advanced_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION] = $general_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION] ); for backwards compat
	} 
	if ( isset( $general_settings[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] ) ) {
		$advanced_settings[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] = $general_settings[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] ); for backwards compat
	}
	if ( isset( $general_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION] ) ) {
		$advanced_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION] = $general_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION] ); for backwards compat
	}
	if ( isset( $general_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ) ) {
		$advanced_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] = $general_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ); for backwards compat
	}
	if ( isset( $general_settings[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] ) ) {
		$advanced_settings[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] = $general_settings[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION];
		// unset( $general_settings[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] ); for backwards compat
	}
	
	/*
	 * Moderation settings to General settings
	 * - auto approve ratings
	 */
	if ( isset( $old_moderation_settings[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] ) ) {
		$general_settings[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] = $old_moderation_settings[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS];
	}
	
	/*
	 * Moderation settings to Email settings
	 * - all settings
	 * - if any e-mail exists in approved notification email list, notify post author or notify submitter, turn on rating approved notification
	 * - if any e-mail exists in moderation notification email list, turn on rating approved notification
	 */
	if ( isset( $old_moderation_settings['mrp_rating_approved_notification'] ) ) {
		
		// Notify the post author
		// Notify the user who submitted the rating (if possible)
		foreach ( $old_moderation_settings['mrp_rating_approved_notification'] as $notify_person ) {
			
			if ( $notify_person == 'author' ) {
				$email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] = true;
			} else if ( $notify_person == 'user' ) {
				$email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] = true;
			}
		}
		
		$email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] = true;
	}
	if ( isset( $old_moderation_settings['mrp_rating_approved_notification_email_list'] ) ) {
		$email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS] = $old_moderation_settings['mrp_rating_approved_notification_email_list'];
		$email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] = true;
	}
	if ( isset( $old_moderation_settings['mrp_rating_moderation_notification_email_list'] ) ) {
		$email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS] = $old_moderation_settings['mrp_rating_moderation_notification_email_list'];
		$email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] = true;
	}
	
	// delete_option( 'mrp_moderation_settings' ); for backwards compat

	/*
	 * WP Comments settings to Auto Placement settings
	 * - if comment form integration is enabled, set comment form as auto placement
	 * - if comment text integration is enabled, turn on show overall rating in comment text
	 * - show rating items, custom fields
	 * - if include rating is optional, turn on optional ratings
	 * - include rating default
	 */
	if ( isset( $old_comment_settings['mrp_comment_form_multi_rating'] ) && $old_comment_settings['mrp_comment_form_multi_rating'] == true ) {
		$auto_placement_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION] = 'comment_form';
	}
	if ( isset( $old_comment_settings['mrp_comment_text_multi_rating'] ) && $old_comment_settings['mrp_comment_text_multi_rating']  == true ) {
		$auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION] = true;
	}
	if ( isset( $old_comment_settings[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] = $old_comment_settings[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION];
	}
	if ( isset( $old_comment_settings[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] = $old_comment_settings[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION];
	}
	if ( isset( $old_comment_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] ) 
			&& $old_comment_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == 'optional' ) {
				$auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] = true;
	}
	if ( isset( $old_comment_settings[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] = $old_comment_settings[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION];
	}
	
	// delete_option( 'mrp_comments_settings' ); for backwards compat
	
	/*
	 * Filter settings to Auto Placement settings
	 * - exclude home, search and archive pages
	 */
	if ( isset( $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] = $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE];
	}
	if ( isset( $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] = $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS];
	}
	if ( isset( $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] ) ) {
		$auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] = $old_filter_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES];
	}
	
	/*
	 * Auto placement to General settings
	 * - generate rich snippets
	 */
	if ( isset( $old_position_settings[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] ) ) {
		$general_settings[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] = $old_position_settings[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION];
	}
	
	/*
	 * Old auto placement settings 
	 */
	$auto_placement_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION] = $old_position_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION];
	$auto_placement_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION] = $old_position_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION];
	
	/*
	 * Styles settings
	 * - convert font awesome version to icon font library
	 */
	if ( isset( $styles_settings[MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION] ) ) {
		$styles_settings[MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION] = 'font-awesome-' . $styles_settings[MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION];
	}
	
	// delete_option( 'mrp_filter_settings' ); for backwards compat
	update_option( MRP_Multi_Rating::GENERAL_SETTINGS,  $general_settings );
	update_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, $auto_placement_settings );
	update_option( MRP_Multi_Rating::ADVANCED_SETTINGS, $advanced_settings );
	update_option( MRP_Multi_Rating::EMAIL_SETTINGS,  $email_settings );
	update_option( MRP_Multi_Rating::STYLES_SETTINGS,  $styles_settings );
	 
	/*
	 * Filters
	 * - migrate fitlers for whitelist/blacklist post ids, category ids and page URL's. blacklist means do not allow auto placement.
	 */
	$post_ids = preg_split( '~[,]+~', $old_filter_settings['mrp_filtered_posts'], -1, PREG_SPLIT_NO_EMPTY );
	$term_ids = preg_split( '~[,]+~', $old_filter_settings['mrp_filtered_categories'], -1, PREG_SPLIT_NO_EMPTY );
	$terms = array();
	foreach ( $term_ids as $term_id ) {
		$term = get_term( $term_id, 'category' );
		array_push( $terms, $term->name );
	}
	$temp_page_urls = preg_split( '~[,]+~', str_replace( '&#13;&#10;', ',', $old_filter_settings['mrp_filtered_page_urls'] ), -1, PREG_SPLIT_NO_EMPTY );
	$page_urls = array();
	foreach ( $temp_page_urls as $temp_page_url ) {
		array_push( $page_urls, trim( str_replace( '&#13;&#10;', '', $temp_page_url ) ) );
	}
	$post_ids_allow_auto_placement = ( $old_filter_settings['mrp_post_filter_type'] == 'whitelist' ) ? true : false;
	$category_ids_allow_auto_placement = ( $old_filter_settings['mrp_category_filter_type'] == 'whitelist' ) ? true : false;
	$page_urls_allow_auto_placement = ( $old_filter_settings['mrp_page_url_filter_type'] == 'whitelist' ) ? true : false;
	
	
	$filters = get_option( 'mrp_filters' );
	if ( ! is_array( $filters ) ) {
		$filters = array();
	}
	
	if ( count( $post_ids ) > 0 ) {
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Post Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array() ,
				'post_types' => array(),
				'rating_form_position' => $post_ids_allow_auto_placement,
				'rating_results_position' => $post_ids_allow_auto_placement,
				'rating_form_id' => '',
				'priority' => 10,
				'override_post_meta' => false,
				'page_urls' => array(),
				'post_ids' => $post_ids
		) );
	}
	
	if ( count( $terms ) > 0 ) {
		array_push( $filters, array(
				'filter_type' => 'taxonomy',
				'filter_name' => __( 'Category Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => $terms,
				'post_types' => array(),
				'rating_form_position' => $category_ids_allow_auto_placement,
				'rating_results_position' => $category_ids_allow_auto_placement,
				'rating_form_id' => '',
				'priority' => 10,
				'override_post_meta' => false,
				'page_urls' => array(),
				'post_ids' => array()
		) );
	}
	
	if ( count( $page_urls ) > 0 ) {
		array_push( $filters, array(
				'filter_type' => 'page-urls',
				'filter_name' => __( 'Page URL Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array(),
				'post_types' => array(),
				'rating_form_position' => $page_urls_allow_auto_placement,
				'rating_results_position' => $page_urls_allow_auto_placement,
				'rating_form_id' => '',
				'priority' => 10,
				'override_post_meta' => false,
				'page_urls' => $page_urls,
				'post_ids' => array()
		) );
	}
	
	update_option( 'mrp_filters',  $filters );
}


/**
 * Upgrades plugin to v4.0
 */
function mrp_upgrade_to_4_0() {	
	MRP_Multi_Rating::activate_plugin();
}


/**
 * Upgrades plugin to v3.2
 */
function mrp_upgrade_to_3_2() {

	MRP_Multi_Rating::activate_plugin();
	
	global $wpdb;
	$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME );
	
	// migrate include_zero column to new required column
	$count = 0;
	foreach ( $results as $rating_item ) {
		if ( isset( $rating_item->include_zero ) ) {
			$required = ! $rating_item->include_zero; 
			$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, 
					array( 'required' => $required ), 
					array( 'rating_item_id' => $rating_item->rating_item_id ), 
					array( '%d' ), 
					array( '%d' ) 
			);
			$count++;
		}
	}
}


/**
 * Upgrades plugin to v3.1
 */
function mrp_upgrade_to_3_1() {
	
	// custom settings
	$custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
	
	// custom text settings
	if ( isset( $custom_text_settings['mrp_submit_once_validation_fail_message'] ) ) {
		$custom_text_settings[MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION] = $custom_text_settings['mrp_submit_once_validation_fail_message'];
		unset( $custom_text_settings['mrp_submit_once_validation_fail_message'] );
	}
	
	// custom text settings
	if ( isset( $custom_text_settings['mrp_allow_anonymous_ratings_failure_message_option'] ) ) {
		$custom_text_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION] = $custom_text_settings['mrp_allow_anonymous_ratings_failure_message_option'];
		unset( $custom_text_settings['mrp_allow_anonymous_ratings_failure_message_option'] );
	}
	
	update_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, $custom_text_settings);
	
}



/**
 * Recursive function to remove a directory and all it's sub-directories and contents
 * @param  $dir
 */
function mrp_recursive_rmdir_and_unlink( $dir ) {
	
	if ( is_dir( $dir ) ) {
		
		$objects = scandir( $dir );
		
		foreach ( $objects as $object ) {
			if ( $object != '.' && $object != '..' ) {
				
				if ( filetype($dir . DIRECTORY_SEPARATOR . $object ) == 'dir' ) {
					mrp_recursive_rmdir_and_unlink( $dir. DIRECTORY_SEPARATOR . $object );
				} else {
					unlink( $dir . DIRECTORY_SEPARATOR . $object );
				}
				
			}
		}
		
		reset( $objects );
		rmdir( $dir );
	}
}
?>
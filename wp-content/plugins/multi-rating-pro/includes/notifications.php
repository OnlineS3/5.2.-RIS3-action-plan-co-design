<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rating moderation notification
 */
function mrp_rating_moderation_notification( $rating_entry ) {
	
	$email_settings = (array) get_option( MRP_Multi_Rating::EMAIL_SETTINGS );
	
	if ( $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] ) {
		
		$rating_form_id = $rating_entry['rating_form_id'];
		$post_id = $rating_entry['post_id'];
		$user_id = $rating_entry['user_id'];
		$rating_entry_id = $rating_entry['rating_entry_id'];
		$rating_item_values = $rating_entry['rating_item_values'];
		$custom_field_values = $rating_entry['custom_field_values'];
		$title = isset( $rating_entry['title'] ) ? $rating_entry['title']  : '';
		$name = isset( $rating_entry['name'] ) ? $rating_entry['name'] : '';
		$email = isset( $rating_entry['email'] ) ? $rating_entry['email'] : '';
		$comment = isset( $rating_entry['comment'] ) ? $rating_entry['comment'] : '';
		$comment_id = $rating_entry['comment_id'];
		$entry_date = $rating_entry['entry_date'];
			
		$from_email = $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_EMAIL];
		$from_name = $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_NAME];
		$subject = $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_SUBJECT];
		$heading = $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_HEADING];
		$message = $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_TEMPLATE];
		
		/**
		 * Substitute the following template tags:
		 * {display_name} - The user's display name
		 * {username} - The user's username on the site
		 * {user_email} - The user's email address
		 * {site_name} - Your site name
		 * {post_permalink} - Permalink to post with name
		 * {rating_entry_id} - The unique ID number for this rating entry
		 * {rating_details} - The rating details
		 * {date} - The date of the rating entry
		 * {rating_moderation_link} - Link to rating entries page
		 * {edit_rating_link} - Link to edit rating page
		 */
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$user_info = get_userdata( $user_id );
		$username = isset( $user_info ) ? $user_info->user_login : '';
		$post_permalink = get_the_permalink( $post_id );
		$rating_details = mrp_get_rating_details( $rating_entry );
		$date = date( 'F j, Y, g:ia', strtotime( $entry_date ) );
		$rating_moderation_link = admin_url() . 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&entry-status=pending';
		$edit_rating_link = admin_url() . 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&rating-entry-id=' . $rating_entry_id;
		
		if ( strlen( $name ) == 0 ) {
			$name = __( 'Anonymous', 'multi-rating-pro' );
		}
		
		$template_tags = array(
				'{display_name}' => trim( $name ),
				'{username}' => trim( $username ),
				'{user_email}' => trim( $email ),
				'{site_name}' => trim( $site_name ),
				'{post_permalink}' => $post_permalink,
				'{rating_entry_id}' => $rating_entry_id,
				'{date}' => $date,
				'{rating_moderation_link}' => $rating_moderation_link,
				'{edit_rating_link}' => $edit_rating_link
		);
		
		$template_tags = apply_filters( 'mrp_rating_moderation_notification_template_tags', $template_tags, $rating_entry );
		
		foreach ( $template_tags as $string => $value ) {
			$message = str_replace( $string, $value, $message );
		}
		
		$message = str_replace( "\r\n", "<br />", $message );
		
		// now add {rating_details} html if required
		$message = str_replace( '{rating_details}', $rating_details, $message );
			
		$emails = array();
		$moderators = preg_split( '/[\r\n,]+/', $email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS], -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $moderators as $moderator_email ) {
			if ( is_email( $moderator_email ) ) {
				array_push( $emails, $moderator_email );
			}
		}
	
		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
		$headers[] = 'Content-Type: text/html';

		add_filter( 'wp_mail_content_type', 'mrp_set_html_content_type' );
		foreach ( $emails as $moderator_email ) {
			@wp_mail( $moderator_email, wp_specialchars_decode( $subject ), $message, $headers );
		}
		remove_filter( 'wp_mail_content_type', 'mrp_set_html_content_type' );
		
	}
}


/**
 * Rating approved notification
 */
function mrp_rating_approved_notification( $rating_entry ) {
	
	$email_settings = (array) get_option( MRP_Multi_Rating::EMAIL_SETTINGS );

	if ( $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] ) {
		
		$rating_form_id = $rating_entry['rating_form_id'];
		$post_id = $rating_entry['post_id'];
		$user_id = $rating_entry['user_id'];
		$rating_entry_id = $rating_entry['rating_entry_id'];
		$rating_item_values = $rating_entry['rating_item_values'];
		$custom_field_values = $rating_entry['custom_field_values'];
		$title = isset( $rating_entry['title'] ) ? $rating_entry['title']  : '';
		$name = isset( $rating_entry['name'] ) ? $rating_entry['name'] : '';
		$email = isset( $rating_entry['email'] ) ? $rating_entry['email'] : '';
		$comment = isset( $rating_entry['comment'] ) ? $rating_entry['comment'] : '';
		$comment_id = isset( $rating_entry['comment_id'] ) ? $rating_entry['comment_id'] : null;
		$entry_date = $rating_entry['entry_date'];
			
		$from_email = $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_EMAIL];
		$from_name = $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_NAME];
		$subject = $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_SUBJECT];
		$heading = $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_HEADING];
		$message = $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TEMPLATE];

		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		/**
		 * Substitute the following template tags:
		 * {display_name} - The user's display name
		 * {username} - The user's username on the site
		 * {user_email} - The user's email address
		 * {site_name} - Your site name
		 * {post_permalink} - Permalink to post with name
		 * {rating_entry_id} - The unique ID number for this rating entry
		 * {rating_details} - The rating details
		 * {date} - The date of the rating entry
		*/
		$site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
		$user_info = get_userdata( $user_id );
		$username = isset( $user_info ) ? $user_info->user_login : '';
		$post_permalink = get_the_permalink( $post_id );
		$rating_details = mrp_get_rating_details( $rating_entry );
		$date = date( 'F j, Y, g:ia', strtotime( $entry_date ) );
		
		if ( strlen( $name ) == 0 ) {
			$name = __( 'Anonymous', 'multi-rating-pro' );
		}
		
		$template_tags = array(
				'{display_name}' => trim( $name ),
				'{username}' => $username,
				'{user_email}' => trim( $email ),
				'{site_name}' => trim( $site_name ),
				'{post_permalink}' => $post_permalink,
				'{rating_entry_id}' => $rating_entry_id,
				'{date}' => $date
		);
		
		$template_tags = apply_filters( 'mrp_rating_approved_notification_template_tags', $template_tags, $rating_entry );

		foreach ( $template_tags as $string => $value ) {
			$message = str_replace( $string, $value, $message );
		}

		$message = str_replace( "\r\n", "<br />", $message );
		
		// now add {rating_details} html if required
		$message = str_replace( '{rating_details}', $rating_details, $message );
			
		$emails = array();
		$to_emails = preg_split( '/[\r\n,]+/', $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS], -1, PREG_SPLIT_NO_EMPTY );
		foreach ( $to_emails as $to_email ) {
			if ( is_email( $to_email ) ) {
				array_push( $emails, $to_email );
			}
		}
		
		if ( $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] && strlen( $user_email ) > 0 && is_email( $user_email ) ) {
			array_push( $emails, $user_email );
		}
		if ( $email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] ) {
			$post_author = get_post_field( 'post_author', $post_id );
			array_push( $emails, get_the_author_meta( 'user_email', $post_author ) );
		}

		$headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
		$headers[] = 'Content-Type: text/html';

		add_filter( 'wp_mail_content_type', 'mrp_set_html_content_type' );
		foreach ( $emails as $email ) {
			@wp_mail( $email, wp_specialchars_decode( $subject ), $message, $headers );
		}
		remove_filter( 'wp_mail_content_type', 'mrp_set_html_content_type' );

	}
}

/**
 * Returns rating details for e-mail notifications
 * 
 * @param $rating_entry_id
 * @return html
 */
function mrp_get_rating_details( $rating_entry ) {
	
	$rating_result = MRP_Multi_Rating_API::get_rating_entry_result( array( 'rating_entry_id' => $rating_entry['rating_entry_id'] ) );
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	
	$rating_form_id = $rating_entry['rating_form_id'];
	$post_id = $rating_entry['post_id'];
	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
	
	$custom_fields = MRP_Multi_Rating_API::get_custom_fields( $rating_form_id );
	$rating_items = MRP_Multi_Rating_API::get_rating_items( array( 'rating_form_id' => $rating_form_id ) );
	
	ob_start();
	mrp_get_template_part( 'notification', 'rating-details', true, array(
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'show_rating_items' => true,
			'show_custom_fields' => true,
			'custom_fields' => $custom_fields,
			'rating_items' => $rating_items,
			'rating_result' => $rating_result,
			'rating_item_values' => $rating_entry['rating_item_values'],
			'custom_field_values' => $rating_entry['custom_field_values'],
			'show_overall_rating' => true,
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'title' => isset( $rating_entry['title'] ) ? $rating_entry['title'] : null,
			'comment' => isset( $rating_entry['comment'] ) ? $rating_entry['comment'] : null
			
	) );
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}

/**
 * 
 * @param unknown $content_type
 * @return string
 */
function mrp_set_html_content_type( $content_type ) {
	return 'text/html';
}


/**
 * Save rating notification
 *
 * @param array $rating_entry
 * @param bool $is_new
 * @param bool $entry_status_changed
 */ 
function mrp_save_rating_notifiction( $rating_entry, $is_new = false, $entry_status_changed = false ) {
		 
	if ( $is_new || ( $entry_status_changed && $rating_entry['entry_status'] == 'approved' ) ) {
		
		$user_id = $rating_entry['user_id'];
		
		if ( $user_id && $user_id != 0 ) {
			$user_info = get_userdata( $user_id );
			$rating_entry['name'] = $user_info->display_name;
			$rating_entry['email'] = $user_info->user_email;
		}
				
		if ( $rating_entry['entry_status'] == 'approved' ) {
			mrp_rating_approved_notification( $rating_entry );
		} else {
			mrp_rating_moderation_notification( $rating_entry );
		}	
	}
	
}
add_action( 'mrp_after_save_rating_entry_success', 'mrp_save_rating_notifiction', 10, 3 );
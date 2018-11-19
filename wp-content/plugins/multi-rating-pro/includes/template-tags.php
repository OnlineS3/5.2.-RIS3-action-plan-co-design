<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays the rating form
 *
 * @param unknown_type $params
 */
function mrp_rating_form( $params = array() ) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get the post id
	$post_id = null;
	if ( isset( $params['post_id'] ) ) {
		if ( strlen( trim( $params['post_id'] ) ) > 0 && is_numeric( $params['post_id'] ) ) {
			$post_id = $params['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	extract( wp_parse_args( $params, array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
			'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
			'rating_form_id' => $rating_form_id,
			'echo' => true,
			'class' => '',
			'user_can_update_delete' => $general_settings[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING],
			'sequence' => null,
			'show_status_message' => true
	) ) );

	if ( is_string( $user_can_update_delete ) ) {
		$user_can_update_delete = $user_can_update_delete == 'true' ? true : false;
	}

	if ( ! $post_id ) {
		return; // No post Id available to display rating form
	}

	// get user ID
	global $wp_roles;
	$user = wp_get_current_user();
	$user_id = $user->ID;

	// do not show rating form if user is not allowed to rate
	if ( MRP_Utils::disallowed_user_roles_check( $user_id ) ) {
		return;
	}

	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

	$rating_items = $rating_form['rating_items'];
	$custom_fields = $rating_form['custom_fields'];
	$review_fields = $rating_form['review_fields'];

	$show_title_input = isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] );
	$show_name_input = isset( $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] );
	$show_email_input = isset( $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] );
	$show_comment_textarea = isset( $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] );

	if ( isset( $params['show_title_input'] ) ) {
		$show_title_input = ( $params['show_title_input'] || $params['show_title_input'] == 'true' ) ? true : false;
	}
	if ( isset( $params['show_name_input'] ) ) {
		$show_name_input = ( $params['show_name_input'] || $params['show_name_input'] == 'true' ) ? true : false;
	}
	if ( isset( $params['show_email_input'] ) ) {
		$show_email_input = ( $params['show_email_input'] || $params['show_email_input'] == 'true' ) ? true : false;
	}
	if ( isset( $params['show_comment_textarea'] ) ) {
		$show_comment_textarea = ( $params['show_comment_textarea'] || $params['show_comment_textarea'] == 'true' ) ? true : false;
	}

	if ( $sequence == null ) {
		$sequence = MRP_Utils::$sequence++;
	}

	$rating_entry_id = null;
	$entry_status = null;
	$title2 = ''; // there's two titles... the rating form title and also the rating entry title
	$name = '';
	$email = '';
	$comment = '';
	$comment_id = null;
	$rating_item_values = array();
	$custom_field_values = array();

	$rating_entry_id = MRP_Multi_Rating_API::user_rating_exists(
			array( 'rating_form_id' => $rating_form_id, 'post_id' => $post_id, 'user_id' => $user_id ) );

	// if user has already submitted the rating form, set default values and allow them to delete or update
	if ( $user_id != 0 && $rating_entry_id ) {

		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );

		if ( $rating_entry ) {
			$title2 = $rating_entry['title'];
			$name =  $rating_entry['name'];
			$email = $rating_entry['email'];
			$comment = $rating_entry['comment'];
			$comment_id = $rating_entry['comment_id'];
			$entry_status = $rating_entry['entry_status'];
			$rating_item_values = $rating_entry['rating_item_values'];
			$custom_field_values = $rating_entry['custom_field_values'];
		}
	}

	ob_start();
	mrp_get_template_part( 'rating-form', null, true, array_merge( $params, array(
			'title' => $title,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'show_title_input' => $show_title_input,
			'show_name_input' => $show_name_input,
			'show_email_input' => $show_email_input,
			'show_comment_textarea' => $show_comment_textarea,
			'submit_button_text' => $submit_button_text,
			'update_button_text' => $update_button_text,
			'delete_button_text' => $delete_button_text,
			'existing_rating_message' => $custom_text_settings[ MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION ],
			'rating_awaiting_moderation_message' => $custom_text_settings[MRP_Multi_Rating::RATING_AWAITING_MODERATION_MESSAGE_OPTION],
			'class' => $class,
			'user_can_update_delete' => $user_can_update_delete,
			'entry_status' => $entry_status,
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'rating_entry_id' => $rating_entry_id,
			'rating_items' => $rating_items,
			'custom_fields' => $custom_fields,
			'user_id' => $user_id,
			'email' => empty( $email ) ? '' : $email,
			'title2' => empty( $title2 ) ? '' : $title2,
			'name' => empty( $name ) ? '' : $name,
			'comment' => empty( $comment ) ? '' : $comment,
			'comment_id' => empty( $comment_id ) ? '' : $comment_id,
			'sequence' => $sequence,
			'show_status_message' => $show_status_message,
			'rating_item_values' => $rating_item_values,
			'custom_field_values' => $custom_field_values,
			'allow_anonymous_ratings' => MRP_Utils::allow_anonymous_rating_check( $post_id, $user_id )
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}

/**
 * Displays the rating item results
 *
 * @param unknown_type $params
 */
function mrp_rating_item_results( $params = array() ) {

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get the post id
	$post_id = null;
	if ( isset( $params['post_id'] ) ) {
		if ( strlen( trim( $params['post_id'] ) ) > 0 && is_numeric( $params['post_id'] ) ) {
			$post_id = $params['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( wp_parse_args( $params, array(
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'rating_form_id' => $rating_form_id,
			'show_count' => true,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'post_id' => $post_id, // default to current post, set to "" to use all posts
			'echo' => true,
			'class' => '',
			'preserve_max_option' => true,
			'show_options' => false, // @deprecated
			'layout' => 'no_options',
			'rating_item_ids' => null,
			'user_roles' => null,
			'rating_entry_ids' => null,
			'entry_status' => 'approved',
			'to_date' => null,
			'from_date' => null,
			'comments_only' => null,
			'taxonomy' => null,
			'term_id' => null,
			'post_ids' => null,
			'before_count' => '(',
			'after_count' => ')',
			'sort_by' => null,
			'show_rank' => false
	) ) );

	if ( is_string( $show_count ) ) {
		$show_count == 'true' ? true : false;
	}
	if ( is_string( $echo ) ) {
		$echo = $echo == 'true' ? true : false;
	}
	if ( is_string( $preserve_max_option ) ) {
		$preserve_max_option == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank == 'true' ? true : false;
	}

	// temp
	if ( is_string( $show_options ) ) {
		$show_options = $show_options == 'true' ? true : false;

		if ( $show_options ) {
			$layout = 'options_block';
		}
	}

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$from_date = null;
		}
	}

	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$to_date = null;
		}
	}

	if ( isset( $post_ids ) && strlen( trim( $post_ids ) ) > 0 ) {
		$post_id = null;
		$temp_post_ids = array();

		foreach ( explode( ',', $post_ids ) as $temp_post_id ) {
			array_push( $temp_post_ids, $temp_post_id );
		}

		$post_ids = implode( ',', $temp_post_ids );

	} else {
		$post_ids = null;
	}

	if ( ! ( isset( $user_roles ) && strlen( trim( $user_roles ) ) > 0 ) ) {
		$user_roles = 0;
	}

	if ( ! ( isset( $rating_item_ids ) && strlen( trim( $rating_item_ids ) ) > 0 ) ) {
		$rating_item_ids = null;
	}

	if ( ! ( isset( $rating_entry_ids ) && strlen( trim( $rating_entry_ids ) ) > 0 ) ) {
		$rating_entry_ids = null;
	}

	$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'rating_item_ids' => $rating_item_ids
	) );

	$rating_item_results = array();
	$count_entries = 0;
	foreach ( $rating_items as $rating_item ) {

		$rating_result = MRP_Multi_Rating_API::get_rating_item_result( array_merge( $params, array(
				'rating_item' => $rating_item,
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_id,
				'entry_status' => $entry_status,
				'user_roles' => $user_roles,
				'rating_entry_ids' => $rating_entry_ids,
				'to_date' => $to_date,
				'from_date' => $from_date,
				'taxonomy' => $taxonomy,
				'term_id' => $term_id,
				'comments_only' => $comments_only,
				'post_ids' => $post_ids
		) ) );

		if ( intval( $rating_result['count_entries'] ) > $count_entries ) {
			$count_entries = intval( $rating_result['count_entries'] );
		}

		$option_value_text_lookup = array();
		if ( isset( $rating_item['option_value_text'] ) ) {
			$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
		}

		array_push( $rating_item_results, array(
				'rating_item' => $rating_item,
				'rating_result' => $rating_result,
				'option_value_text_lookup' => $option_value_text_lookup
		) );
	}

	// sort by
	if ( $sort_by == 'highest_rated' ) {
		uasort( $rating_item_results, 'mrp_sort_highest_rated_rating_items' );
	}

	ob_start();
	mrp_get_template_part( 'rating-item-results', null, true, array_merge( $params, array(
			'result_type' => $result_type,
			'show_count'=> $show_count,
			'no_rating_results_text' => $no_rating_results_text,
			'count_entries' => $count_entries,
			'class' => $class,
			'preserve_max_option' => $preserve_max_option,
			'layout' => $layout,
			'rating_item_results' => $rating_item_results,
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'before_count' => $before_count,
			'after_count' => $after_count,
			'show_rank' => $show_rank
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}


/**
 * Displays the rating result
 *
 * @param unknown_type $atts
 * @return void|string
 */
function mrp_rating_result( $params = array()) {

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get the post id
	$post_id = null;
	if ( isset( $params['post_id'] ) ) {
		if ( strlen( trim( $params['post_id'] ) ) > 0 && is_numeric( $params['post_id'] ) ) {
			$post_id = $params['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( wp_parse_args( $params, array(
			'post_id' => $post_id,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' =>  $rating_form_id,
			'show_rich_snippets' => false, // @deprecated
			'show_title' => false,
			'show_count' => true,
			'echo' => true,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'class' => '',
			'before_count' => '(',
			'after_count' => ')',
			'user_roles' => null,
			'rating_item_ids' => null,
			'comments_only' => false,
			'to_date' => null,
			'from_date' => null,
			'rating_entry_ids' => null,
			'generate_microdata' => false
	) ) );

	if ( is_string( $show_rich_snippets ) ) {
		$show_rich_snippets = $show_rich_snippets == 'true' ? true : false;
		$generate_microdata = $show_rich_snippets;
	}
	if ( is_string( $generate_microdata ) ) {
		$generate_microdata = $generate_microdata == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $echo ) ) {
		$echo = $echo == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$from_date = null;
		}
	}

	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$to_date = null;
		}
	}

	if ( ! $post_id ) {
		return; // No post Id available to display rating form
	}

	if ( ! ( isset( $user_roles ) && strlen( trim( $user_roles ) ) > 0 ) ) {
		$user_roles = null;
	}

	if ( ! ( isset( $rating_item_ids ) && strlen( trim( $rating_item_ids ) ) > 0 ) ) {
		$rating_item_ids = null;
	}

	if ( ! ( isset( $rating_entry_ids ) && strlen( $rating_entry_ids ) > 0 ) ) {
		$rating_entry_ids = null;
	}

	$rating_result = MRP_Multi_Rating_API::get_rating_result( array_merge( $params, array(
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'user_roles' => $user_roles,
			'rating_item_ids' => $rating_item_ids,
			'comments_only' => $comments_only,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'rating_entry_ids' => $rating_entry_ids
	) ) );

	ob_start();
	mrp_get_template_part( 'rating-result', null, true, array_merge( $params, array(
			'no_rating_results_text' => $no_rating_results_text,
			'generate_microdata' => $generate_microdata,
			'show_title' => $show_title,
			'show_date' => false,
			'show_count' => $show_count,
			'no_rating_results_text' => $no_rating_results_text,
			'result_type' => $result_type,
			'class' => $class . ' rating-result-' . $rating_form_id . '-' . $post_id,
			'rating_result' => $rating_result,
			'before_count' => $before_count,
			'after_count' => $after_count,
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'ignore_count' => false,
			'preserve_max_option' => false,
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}


/**
 * Displays rating entry details list
 *
 * @param $params
 */
function mrp_rating_entry_details_list( $params = array() ) {

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get the post id
	$post_id = null;
	if ( isset( $params['post_id'] ) ) {
		if ( strlen( trim( $params['post_id'] ) ) > 0 && is_numeric( $params['post_id'] ) ) {
			$post_id = $params['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( wp_parse_args( $params, array(
			'post_id' => $post_id, // default to current post, set to "" to use all posts
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' =>  $rating_form_id,
			'show_date' => true,
			'comments_only' => true,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'before_name' => '',
			'after_name' => '',
			'before_comment' => '',
			'after_comment' => '',
			'show_name' => true,
			'show_comment' => true,
			'show_rating_items' => true,
			'show_custom_fields' => true,
			'limit' => null,
			'echo' => true,
			'show_category_filter' => false, // @deprecated
			'category_id' => 0,
			'class' => '',
			'before_date' => '',
			'after_date' => '',
			'taxonomy' => null,
			'term_id' => 0,
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecated
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'show_filter' => false,
			'show_overall_rating' => true,
			'show_permalink' => false,
			'rating_entry_ids' => null,
			'sort_by' => 'highest_rated',
			'show_avatar' => true,
			'user_roles' => null,
			'rating_item_ids' => null,
			'to_date' => null,
			'from_date' => null,
			'offset' => 0,
			'post_ids' => null,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_ENTRIES_LIST_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'user_id' => null,
			'entry_status' => 'approved',
			'layout' => 'table',
			'add_author_link' => true,
			'show_title' => true,
			'generate_microdata' => false,

			// new
			'show_load_more' => false
	) ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_category_filter;
	}

	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}
	if ( is_string( $show_date ) ) {
		$show_date = $show_date == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_name ) ) {
		$show_name = $show_name == 'true' ? true : false;
	}
	if ( is_string( $show_permalink ) ) {
		$show_permalink = $show_permalink == 'true' ? true : false;
	}
	if ( is_string( $show_comment ) ) {
		$show_comment = $show_comment == 'true' ? true : false;
	}
	if ( is_string( $show_rating_items ) ) {
		$show_rating_items = $show_rating_items == 'true' ? true : false;
	}
	if ( is_string( $echo ) ) {
		$echo = $echo == 'true' ? true : false;
	}
	if ( is_string( $show_overall_rating ) ) {
		$show_overall_rating = $show_overall_rating == 'true' ? true : false;
	}
	if ( is_string( $show_custom_fields ) ) {
		$show_custom_fields = $show_custom_fields == 'true' ? true : false;
	}
	if ( is_string( $show_permalink ) ) {
		$show_permalink = $show_permalink == 'true' ? true : false;
	}
	if ( is_string( $show_avatar ) ) {
		$show_avatar = $show_avatar == 'true' ? true : false;
	}
	if ( is_string( $show_load_more ) ) {
		$show_load_more = $show_load_more == 'true' ? true : false;
	}
	if ( ! isset( $rating_form_id ) || ! is_numeric( $rating_form_id ) ) {
		$rating_form_id = null;
	}
	if ( strlen( trim( $post_id ) ) == 0 ) {
		$post_id = null;
	}
	if ( strlen( trim ( $entry_status ) ) == 0 ) {
		$entry_status = null;
	}
	if ( is_string( $generate_microdata ) ) {
		$generate_microdata = $generate_microdata == 'true' ? true : false;
	}
	if ( is_string( $add_author_link ) ) {
		$add_author_link = $add_author_link == 'true' ? true : false;
	}

	if ( isset( $post_ids ) && strlen( trim( $post_ids ) ) > 0 ) {
		$post_id = null;
		$temp_post_ids = array();

		foreach ( explode( ',', $post_ids ) as $temp_post_id ) {
			array_push( $temp_post_ids, $temp_post_id );
		}

		$post_ids = implode( ',', $temp_post_ids );
	} else {
		$post_ids = null;
	}

	if ( ! ( isset( $user_roles ) && strlen( trim( $user_roles ) ) > 0 ) ) {
		$user_roles = null;
	}

	if ( ! ( isset( $rating_item_ids ) && strlen( trim( $rating_item_ids ) ) > 0 ) ) {
		$rating_item_ids = null;
	}
	if ( ! ( isset( $rating_entry_ids ) && strlen( trim( $rating_entry_ids ) ) > 0 ) ) {
		$rating_entry_ids = null;
	}

	// show the filter for taxonomy
	if ( $show_filter == true && isset( $_REQUEST['term-id'] ) ) {
		// override category id if set in HTTP request
		$term_id = $_REQUEST['term-id'];
	}

	if ( $show_filter && $taxonomy == null ) {
		$taxonomy = 'category';
	}

	if ( $category_id != 0) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	if ( $term_id == 0 ) {
		$term_id = null; // so that all terms are returned
	}

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$from_date = null;
		}
	}

	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$to_date = null;
		}
	}

	$rating_entry_result_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array_merge( $params, array(
			'taxonomy' => $taxonomy,
			'term_id' => $term_id,
			'limit' => $limit,
			'rating_form_id' => $rating_form_id,
			'sort_by' => $sort_by,
			'post_id' => $post_id,
			'rating_item_ids' => $rating_item_ids,
			'user_roles' => $user_roles,
			'rating_entry_ids' => $rating_entry_ids,
			'user_id' => $user_id,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'offset' => $offset,
			'post_ids' => $post_ids,
			'entry_status' => $entry_status
	) ) );

	foreach ( $rating_entry_result_list['rating_results'] as $index => $rating_entry_result ) {
		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_result['rating_entry_id'] ) );
		$rating_entry_result_list['rating_results'][$index] = array_merge( array( 'rating_result' => $rating_entry_result, 'rank' => $index ), $rating_entry );
	}

	$custom_fields = MRP_Multi_Rating_API::get_custom_fields();
	$rating_items = MRP_Multi_Rating_API::get_rating_items();


	ob_start();
	mrp_get_template_part( 'rating-entry-details', 'list', true, array_merge( $params, array(
			'rating_entry_result_list' => $rating_entry_result_list['rating_results'],
			'result_type' => $result_type,
			'show_date' => $show_date,
			'show_name' => $show_name,
			'show_comment' => $show_comment,
			'show_rating_items' => $show_rating_items,
			'show_custom_fields' => $show_custom_fields,
			'custom_fields' => $custom_fields,
			'rating_items' => $rating_items,
			'show_filter' => $show_filter,
			'show_overall_rating' => $show_overall_rating,
			'show_post' => false,
			'show_avatar' => $show_avatar,
			'before_name' => $before_name,
			'after_name' => $after_name,
			'before_comment' => $before_comment,
			'after_comment' => $after_comment,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'no_rating_results_text' => $no_rating_results_text,
			//'show_category_filter' => $show_category_filter,
			//'category_id' => $category_id,
			'term_id' => $term_id,
			'taxonomy' => $taxonomy,
			'class' => $class,
			'filter_button_text' => $filter_button_text,
			//'category_label_text' => $category_label_text
			'filter_label_text' => $category_label_text,
			'show_permalink' => $show_permalink,
			'show_title' => $show_title,
			'layout' => $layout,
			'generate_microdata' => $generate_microdata,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'title' => $title,
			'add_author_link' => $add_author_link,

			// new
			'params' => $params,
			'sequence' => MRP_Utils::$sequence++,
			'show_load_more' => $show_load_more
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}


/**
 * Displays a rating results list. This is used by the Rating Result List widet and shortcode.
 *
 * @param unknown_type $params
 * @return string
 */
function mrp_rating_results_list( $params = array() ) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	$rating_form_id = MRP_Utils::get_rating_form( null, $params );

	extract( wp_parse_args( $params, array(
			'rating_form_id' => $rating_form_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_count' => true,
			'echo' => true,
			'show_category_filter' => true, // @deprecated
			'category_id' => 0, // 0 = All, // uses the category taxonomy
			'limit' => 10, // modified was count
			'show_rank' => true,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'show_title' => true,
			'class' => '',
			'taxonomy' => null,
			'term_id' => 0, // 0 = All
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecated
			'show_featured_img' => true,
			'image_size' => 'thumbnail',
			'sort_by' => 'highest_rated',
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'show_filter' => false,
			'rating_item_ids' => null,
			'user_roles' => null,
			'to_date' => null,
			'from_date' => null,
			'comments_only' => null,
			'offset' => 0,
			'rating_entry_ids' => null,
			'post_ids' => null
	) ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_category_filter;
	}

	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}
	if ( is_string( $show_count) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $echo ) ) {
		$echo = $echo == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_featured_img ) ) {
		$show_featured_img = $show_featured_img == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}

	// show the filter for taxonomy
	if ( $show_filter == true && isset( $_REQUEST['term-id'] ) ) {
		// override category id if set in HTTP request
		$term_id = $_REQUEST['term-id'];
	}

	if ( $show_filter && $taxonomy == null ) {
		$taxonomy = 'category';
	}

	if ( $category_id != 0) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	if ( $term_id == 0 ) {
		$term_id = null; // so that all terms are returned
	}

	if ( ! ( isset( $user_roles ) && strlen( $user_roles ) > 0 ) ) {
		$user_roles = null;
	}

	if ( isset( $post_ids ) && strlen( trim( $post_ids ) ) > 0 ) {
		$temp_post_ids = array();

		foreach ( explode( ',', $post_ids ) as $temp_post_id ) {
			array_push( $temp_post_ids, $temp_post_id );
		}

		$post_ids = implode( ',', $temp_post_ids );
	} else {
		$post_ids = null;
	}

	if ( ! ( isset( $rating_item_ids ) && strlen( $rating_item_ids ) > 0 ) ) {
		$rating_item_ids = null;
	}

	if ( ! ( isset( $rating_entry_ids ) && strlen( $rating_entry_ids ) > 0 ) ) {
		$rating_entry_ids = null;
	}

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$from_date = null;
		}
	}

	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$to_date = null;
		}
	}

	$rating_results_list = MRP_Multi_Rating_API::get_rating_result_list( array_merge( $params, array(
			'limit' => $limit,
			'offset' => $offset,
			'rating_form_id' => $rating_form_id,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id,
			'sort_by' => $sort_by,
			'rating_item_ids' => $rating_item_ids,
			'user_roles' => $user_roles,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'rating_entry_ids' => $rating_entry_ids,
			'comments_only' => $comments_only,
			'post_ids' => $post_ids
	) ) );

	ob_start();
	mrp_get_template_part( 'rating-result', 'list', true, array_merge( $params, array(
			'show_title' => $show_title,
			'show_count' => $show_count,
			//'show_category_filter' => $show_category_filter,
			'show_filter' => $show_filter,
			'category_id' => $category_id,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'title' => $title,
			'show_rank' => $show_rank,
			'no_rating_results_text' => $no_rating_results_text,
			'result_type' => $result_type,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id,
			'filter_button_text' => $filter_button_text,
			//'category_label_text' => $category_label_text,
			'filter_label_text' => $filter_label_text,
			'show_featured_img' => $show_featured_img,
			'image_size' => $image_size,
			'generate_microdata' => false,
			'class' => $class,
			'rating_results' => $rating_results_list['rating_results'],
			'before_count' => '(',
			'after_count' => ')',
			'ignore_count' => false,
			'preserve_max_option' => false,
			'before_date' => '',
			'after_date' => ''
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}

/**
 * Displays ratings for a specified user
 *
 * @param $params
 */
function mrp_user_rating_results( $params = array()) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	$rating_form_id = MRP_Utils::get_rating_form( null, $params );

	// get user id, ignore username if user_id is set. Default to current logged in user if
	// both username and user_id are not set
	$user_id = isset( $params['user_id'] ) ? $params['user_id'] : null;
	if ( $user_id == null) {

		if ( isset( $params['username'] ) ) {
			$user = get_user_by( 'login', $params['username'] );
			if ( $user && $user->ID ) {
				$user_id = $user->ID;
			}
		} else {

			if ( isset( $_REQUEST['user-id'] ) && is_numeric( $_REQUEST['user-id'] ) ) {
				$user_id = intval( $_REQUEST['user-id'] );
			} else {

				global $wp_roles;
				$user = wp_get_current_user();
				if ( $user && $user->ID ) {
					$user_id = $user->ID;
				}
			}
		}
	}

	extract( wp_parse_args( $params, array(
			'rating_form_id' => $rating_form_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'echo' => true,
			'show_category_filter' => true, // @deprecated
			'category_id' => 0, // 0 = All, // uses the category taxonomy
			'limit' => 10,
			'show_rank' => true,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'show_title' => true,
			'class' => '',
			'taxonomy' => null,
			'term_id' => 0, // 0 = All
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecated
			'show_featured_img' => true,
			'image_size' => 'thumbnail',
			'show_date' => true,
			'before_date' => '',
			'after_date' => '',
			'sort_by' => 'highest_rated',
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'show_filter' => false,
			'to_date' => null,
			'from_date' => null,
			'rating_item_ids' => null,
			'comments_only' => null,
			'offset' => 0,
			'rating_entry_ids' => null,
			'entry_status' => 'approved'
	) ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_category_filter;
	}

	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}
	if ( is_string( $show_date ) ) {
		$show_date = $show_date == 'true' ? true : false;
	}
	if ( is_string( $echo ) ) {
		$echo = $echo == 'true' ? true : false;
	}
	if ( is_string($show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_featured_img ) ) {
		$show_featured_img = $show_featured_img == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( ! isset( $rating_form_id ) || ! is_numeric( $rating_form_id ) ) {
		$rating_form_id = null;
	}

	if ( ! ( isset( $rating_item_ids ) && strlen( $rating_item_ids ) > 0 ) ) {
		$rating_item_ids = null;
	}

	if ( ! ( isset( $rating_entry_ids ) && strlen( $rating_entry_ids ) > 0 ) ) {
		$rating_entry_ids = null;
	}
	if ( strlen( trim( $entry_status ) ) == 0 ) {
		$entry_status = null;
	}

	$rating_results_list = array();

	// if user is logged in, retrieve their ratings and list them
	if ( $user_id != 0 ) {

		// show the filter for taxonomy
		if ( $show_filter == true && isset( $_REQUEST['term-id'] ) ) {
			// override category id if set in HTTP request
			$term_id = $_REQUEST['term-id'];
		}

		if ( $show_filter && $taxonomy == null ) {
			$taxonomy = 'category';
		}

		if ( $category_id != 0) {
			$term_id = $category_id;
			$taxonomy = 'category';
		}

		if ( $term_id == 0 ) {
			$term_id = null; // so that all terms are returned
		}

		if ( $from_date != null && strlen( $from_date ) > 0 ) {
			list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
			if ( ! checkdate( $month , $day , $year ) ) {
				$from_date = null;
			}
		}

		if ( $to_date != null && strlen($to_date) > 0 ) {
			list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
			if ( ! checkdate( $month , $day , $year ) ) {
				$to_date = null;
			}
		}

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array_merge( $params, array(
				'limit' => $limit,
				'rating_form_id' => $rating_form_id,
				'taxonomy' => $taxonomy,
				'term_id' => $term_id,
				'sort_by' => $sort_by,
				'rating_item_ids' => $rating_item_ids,
				'user_id' => $user_id,
				'to_date' => $to_date,
				'from_date' => $from_date,
				'rating_entry_ids' => $rating_entry_ids,
				'comments_only' => $comments_only,
				'offset' => $offset,
				'entry_status' => $entry_status,
		) ) );

	}

	ob_start();
	mrp_get_template_part( 'rating-result', 'list', true, array_merge( $params, array(
			'show_title' => $show_title,
			'show_count' => false,
			//'show_category_filter' => $show_category_filter,
			'show_filter' => $show_filter,
			'category_id' => $category_id,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'title' => $title,
			'show_rank' => $show_rank,
			'no_rating_results_text' => $no_rating_results_text,
			'result_type' => $result_type,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id,
			'filter_button_text' => $filter_button_text,
			//'category_label_text' => $category_label_text,
			'filter_label_text' => $filter_label_text,
			'show_featured_img' => $show_featured_img,
			'image_size' => $image_size,
			'generate_microdata' => false,
			'class' => $class,
			'rating_results' => isset( $rating_results_list['rating_results'] ) ? $rating_results_list['rating_results'] : array(),
			'before_count' => '',
			'after_count' => '',
			'ignore_count' => false,
			'preserve_max_option' => false,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'show_date'=> $show_date
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}




/**
 * Gets the rating result and selected rating items for a comment
 *
 * @param $params
 */
function mrp_comment_rating_result( $params = array()) {

	$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	$show_overall_rating = $auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION];
	$show_title = $auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION];
	$show_rating_items = $auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION];
	$show_custom_fields = $auto_placement_settings[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION];

	extract( wp_parse_args( $params, array(
			'comment_id' => null,
			'comment_text' => null,
			'class' => '',
			'echo' => true,
			'show_overall_rating' => $show_overall_rating,
			'show_rating_items' => $show_rating_items,
			'show_custom_fields' => $show_custom_fields,
			'show_title' => $show_title,
			'generate_microdata' => $general_settings[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] && is_singular(),
			'entry_status' => 'approved',
			'approved_comments_only' => true
	) ) );

	if ( is_string( $generate_microdata ) ) {
		$generate_microdata = $generate_microdata == 'true' ? true : false;
	}
	if ( is_string( $show_rating_items ) ) {
		$show_rating_items = $show_rating_items == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_overall_rating ) ) {
		$show_overall_rating = $show_overall_rating == 'true' ? true : false;
	}
	if ( is_string( $show_custom_fields ) ) {
		$show_custom_fields = $show_custom_fields == 'true' ? true : false;
	}

	$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array(
			'comment_id' => $comment_id,
			'entry_status' => $entry_status,
			'approved_comments_only' => $approved_comments_only
	) );

	if ( $rating_entry == null ) {
		$comment_text = ( $comment_text == null ) ? get_comment_text( $comment_id ) : $comment_text;
		return $comment_text;
	}

	$rating_entry_id = $rating_entry['rating_entry_id'];
	$rating_form_id = $rating_entry['rating_form_id'];

	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_entry['rating_form_id'] );

	$rating_result = null;
	if ( $show_overall_rating ) {
		$rating_result = MRP_Multi_Rating_API::get_rating_entry_result( array_merge( $params, array( 'rating_entry_id' => $rating_entry_id ) ) );
	}

	ob_start();
	mrp_get_template_part( 'comment-text', null, true, array_merge( $params, array(
			'title' => $rating_entry['title'],
			'rating_result' => $rating_result,
			'custom_field_values' => $rating_entry['custom_field_values'],
			'rating_item_values' => $rating_entry['rating_item_values'],
			'rating_items' => $rating_form['rating_items'],
			'custom_fields' => $rating_form['custom_fields'],
			'class' => '',
			'show_title' => $show_title,
			'show_overall_rating' => $show_overall_rating,
			'show_rating_items' => $show_rating_items,
			'show_custom_fields' => $show_custom_fields,
			'rating_form_id' => $rating_entry['rating_form_id'],
			'post_id' => $rating_entry['post_id'],
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'comment_text' => ( $comment_text == null ) ? $rating_entry['comment'] : $comment_text,
			'generate_microdata' => $generate_microdata,
			'name' => $rating_entry['name'],
			'entry_date' => $rating_entry['entry_date']
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}


/**
 * Displays the comment rating form
 *
 * @param $params
 */
function mrp_comment_rating_form( $params = array() ) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get the post id
	global $post;

	$post_id = null;
	if ( isset( $post ) ) {
		$post_id = $post->ID;
	}

	if ( $post_id == null ) {
		return; // No post Id available
	}

	extract( wp_parse_args( $params, array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			//'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
			'class' => '',
			'echo' => true
	) ) );

	ob_start();
	comment_form( array(
			'title_reply' => $title,
			'label_submit' => $submit_button_text,
			/*'fields' => $fields*/
	), $post_id );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}

/**
 * Displays the user ratings dashboard
 *
 * @param unknown_type $params
 */
function mrp_user_ratings_dashboard( $params = array()) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// get user ID for logged in user
	global $wp_roles;
	$user = wp_get_current_user();
	$user_id = $user->ID;

	extract( wp_parse_args( $params, array(
			'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
			'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
			'echo' => true,
			'class' => 'user-ratings-dashboard',
			'user_can_update_delete' => $general_settings[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING],
			'before_date' => '',
			'after_date' => '',
			'show_count' => false,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'before_count' => '',
			'after_count' => '',
			'entry_status' => '',
			'approved_comments_only' => false,
			'show_count_comments' => true,
			'limit' => null,
			'offset' => 0,
			'sort_by' => 'highest_rated',
			'published_posts_only' => true,
			'comments_only' => false,
			'from_date' => null,
			'to_date' => null,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'category_id' => 0, // 0 = All,
			'taxonomy' => null,
			'term_id' => 0, // 0 = All,
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'show_filter' => false,
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION]
	) ) );

	if ( is_string( $user_can_update_delete ) ) {
		$user_can_update_delete = $user_can_update_delete == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $approved_comments_only ) ) {
		$approved_comments_only = $approved_comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_count_comments ) ) {
		$show_count_comments = $show_count_comments == 'true' ? true : false;
	}
	if ( is_string( $published_posts_only ) ) {
		$published_posts_only = $published_posts_only == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}

	// show the filter for taxonomy
	if ( $show_filter == true && isset( $_REQUEST['term-id'] ) ) {
		// override category id if set in HTTP request
		$term_id = $_REQUEST['term-id'];
	}

	if ( $show_filter && $taxonomy == null ) {
		$taxonomy = 'category';
	}

	if ( $category_id != 0) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	if ( $term_id == 0 ) {
		$term_id = null; // so that all terms are returned
	}

	$count_pending = 0;
	$avg_rating_result = 0;
	$most_recent_date = null;
	$count_entries = 0;
	$count_comments = 0;
	$display_name = null;
	$rating_results = array();

	if ( $user_id != 0 ) {

		$rating_entry_result_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array_merge( $params, array(
				'limit' => $limit,
				'offset' => $offset,
				'sort_by' => $sort_by,
				'entry_status' => $entry_status,
				'approved_comments_only' => $approved_comments_only,
				'published_posts_only' => $published_posts_only,
				'user_id' => $user_id,
				'comments_only' => $comments_only,
				'from_date' => $from_date,
				'to_date' => $to_date,
				'taxonomy' => $taxonomy,
				'term_id' => $term_id
		) ) );

		if ( $rating_entry_result_list ) {

			foreach ( $rating_entry_result_list['rating_results'] as $index => $rating_entry_result ) {
				$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_result['rating_entry_id'] ) );
				$rating_entry_result_list['rating_results'][$index] = array_merge( array( 'rating_result' => $rating_entry_result ), $rating_entry );
			}

			$rating_results = $rating_entry_result_list['rating_results'];

			$count_pending = $rating_entry_result_list['count_pending'];
			$count_entries = $rating_entry_result_list['count_entries'];
			$avg_rating_result = $rating_entry_result_list['avg_rating_result'];
			$most_recent_date = $rating_entry_result_list['most_recent_date'];
			$count_comments = $rating_entry_result_list['count_comments'];

		}

		$user_info = get_userdata( $user_id );
		$display_name = $user_info->display_name;
	}

	ob_start();
	mrp_get_template_part( 'user-ratings-dashboard', null, true, array_merge( $params, array(
			'title' => $title,
			'user_display_name' => $display_name,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'submit_button_text' => $submit_button_text,
			'update_button_text' => $update_button_text,
			'delete_button_text' => $delete_button_text,
			'class' => $class,
			'user_can_update_delete' => $user_can_update_delete,
			'user_id' => $user_id,
			'rating_results' => $rating_results,
			'show_date' => true,
			'show_entry_status' => true,
			'avg_rating_result' => $avg_rating_result,
			'most_recent_date' => $most_recent_date,
			'count_entries' => $count_entries,
			'count_pending' => $count_pending,
			'count_comments' => $count_comments,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'show_count' => $show_count,
			'result_type' => $result_type,
			'before_count' => $before_count,
			'after_count' => $after_count,
			'show_count_comments' => $show_count_comments,
			'no_rating_results_text' => $no_rating_results_text,
			'show_filter' => $show_filter,
			'term_id' => $term_id,
			'taxonomy' => $taxonomy,
			'filter_button_text' => $filter_button_text,
			'filter_label_text' => $filter_label_text
	) ) );
	$html = ob_get_contents();
	ob_end_clean();

	$html = apply_filters( 'mrp_template_html', $html );

	if ( $echo == true ) {
		echo $html;
	}

	return $html;
}

/**
 * Rating entries details list load more
 */
function mrp_rating_entries_details_list_load_more() {

	$ajax_nonce = $_POST['nonce'];

	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID . '-nonce' ) ) {
		$params = json_decode( stripslashes( $_POST['params'] ), true );
		// set new offset to load more
		$offset = $params['offset'] + $params['limit'];
		$params['offset'] = $offset;

		$rating_entry_result_list = MRP_Multi_Rating_API::get_rating_entry_result_list( $params );

		foreach ( $rating_entry_result_list['rating_results'] as $index => $rating_entry_result ) {
			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_result['rating_entry_id'] ) );
			$rating_entry_result_list['rating_results'][$index] = array_merge( array( 'rating_result' => $rating_entry_result, 'rank' => $index ), $rating_entry );
		}

		$custom_fields = MRP_Multi_Rating_API::get_custom_fields();
		$rating_items = MRP_Multi_Rating_API::get_rating_items();

		ob_start();
		foreach ( $rating_entry_result_list['rating_results'] as $rating_entry ) {
			mrp_get_template_part( 'rating-entry-details', null, true, array_merge( $params, array(
					'rating_entry' => $rating_entry,
					'rating_items'  => $rating_items,
					'custom_fields' => $custom_fields
			) ) );
		}
		$html = ob_get_contents();
		ob_end_clean();

		$html = apply_filters( 'mrp_template_html', $html );

		$has_more = ( $offset + $params['limit'] + 1 ) <= $rating_entry_result_list['count_entries'];
		if ( isset( $params['entry_status'] ) && $params['entry_status'] == 'pending' ) {
			$has_more = ( $offset + $params['limit'] + 1 ) <= $rating_entry_result_list['count_pending'];
		}

		$ajax_response = json_encode( array (
				'status' => 'success',
				'data' => array(
						'sequence' => intval( $_POST['sequence'] ),
						'params' => $params,
						'has_more' => $has_more
				),
				'html' => $html
		) );

		echo $ajax_response;

	}

	die();
}
add_action( 'wp_ajax_rating_entries_details_list', 'mrp_rating_entries_details_list_load_more' );
add_action( 'wp_ajax_nopriv_rating_entries_details_list', 'mrp_rating_entries_details_list_load_more' );

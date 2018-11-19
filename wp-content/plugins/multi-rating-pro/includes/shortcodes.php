<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shortcode to display the rating form
 */
function mrp_rating_form_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_rating_form', $atts ) ) {
		return;
	}

	// get the post id
	$post_id = null;
	if ( isset( $atts['post_id'] ) ) {
		if ( strlen( trim( $atts['post_id'] ) ) > 0 && is_numeric( $atts['post_id'] ) ) {
			$post_id = $atts['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
			'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
			'rating_form_id' => $rating_form_id,
			'class' => '',
			'user_can_update_delete' => $general_settings[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING]
	), $atts ) );

	if ( $post_id == null ) {
		return; // No post Id available
	}

	if ( is_string( $user_can_update_delete ) ) {
		$user_can_update_delete = $user_can_update_delete == 'true' ? true : false;
	}

	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
	$review_fields = $rating_form['review_fields'];

	$show_title_input = isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] );
	$show_name_input = isset( $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] );
	$show_email_input = isset( $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] );
	$show_comment_textarea = isset( $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] );

	/*
	 * Shortcode attributes can override whether to show review field, but only if the review field
	 * has breen added to the rating form...
	 */
	if ( $show_title_input && isset( $atts['show_title_input'] )  ) {
		if ( is_string( $atts['show_title_input'] ) ) {
			$show_title_input = ( $atts['show_title_input'] == 'true' ) ? true : false;
		} else {
			$show_title_input = $atts['show_title_input'];
		}
	}
	if ( $show_name_input && isset( $atts['show_name_input'] )  ) {
		if ( is_string( $atts['show_name_input'] ) ) {
			$show_name_input = ( $atts['show_name_input'] == 'true' ) ? true : false;
		} else {
			$show_name_input = $atts['show_name_input'];
		}
	}
	if ( $show_email_input && isset( $atts['show_email_input'] )  ) {
		if ( is_string( $atts['show_email_input'] ) ) {
			$show_email_input = ( $atts['show_email_input'] == 'true' ) ? true : false;
		} else {
			$show_email_input = $atts['show_email_input'];
		}
	}
	if ( $show_comment_textarea && isset( $atts['show_comment_textarea'] ) ) {
		if ( is_string( $atts['show_comment_textarea'] ) ) {
			$show_comment_textarea = ( $atts['show_comment_textarea'] == 'true' ) ? true : false;
		} else {
			$show_comment_textarea = $atts['show_comment_textarea'];
		}
	}

	return mrp_rating_form( apply_filters( 'mrp_rating_form_params', array_merge( $atts, array(
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'title' => $title,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'submit_button_text' => $submit_button_text,
			'update_button_text' => $update_button_text,
			'delete_button_text' => $delete_button_text,
			'show_title_input' => $show_title_input,
			'show_name_input' => $show_name_input,
			'show_email_input' => $show_email_input,
			'show_comment_textarea' => $show_comment_textarea,
			'echo' => false,
			'class' => $class . ' mrp-shortcode',
			'user_can_update_delete' => $user_can_update_delete
	) ) ) );
}
add_shortcode( 'mrp_rating_form', 'mrp_rating_form_shortcode' );


/**
 * Shortcode to display the rating result
 */
function mrp_rating_result_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_rating_result', $atts ) ) {
		return;
	}

	// get the post id
	$post_id = null;
	if ( isset( $atts['post_id'] ) ) {
		if ( strlen( trim( $atts['post_id'] ) ) > 0 && is_numeric( $atts['post_id'] ) ) {
			$post_id = $atts['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'no_rating_results_text' =>  $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' => $rating_form_id,
			'show_rich_snippets' => false, // @deprecated
			'show_title' => false,
			'show_count' => true,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'class' => '',
			'before_count' => '(',
			'after_count' => ')',
			'user_roles' => '',
			'rating_item_ids' => null,
			'comments_only' => false,
			'from_date' => null,
			'to_date' => null,
			'rating_entry_ids' => null,
			'generate_microdata' => false
	), $atts ) );

	if ( $post_id == null ) {
		return; // No post Id available
	}

	if ( is_string( $show_rich_snippets ) ) {
		$show_rich_snippets = $show_rich_snippets == 'true' ? true : false;
		$generate_microdata = $show_rich_snippets;
	}
	if ( is_string( $generate_microdata ) ) {
		$generate_microdata = $generate_microdata == 'true' ? true : false;
	}
	if ( is_string( $show_title) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}

	return mrp_rating_result( apply_filters( 'mrp_rating_result_params', array_merge( $atts, array(
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'no_rating_results_text' => $no_rating_results_text,
			'generate_microdata' => $generate_microdata,
			'show_title' => $show_title,
			'show_date' => false,
			'show_count' => $show_count,
			'echo' => false,
			'result_type' => $result_type,
			'class' => $class . ' mrp-shortcode',
			'before_count' => $before_count,
			'after_count' => $after_count,
			'user_roles' => $user_roles,
			'rating_item_ids' => $rating_item_ids,
			'comments_only' => $comments_only,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'rating_entry_ids' => $rating_entry_ids
	) ) ) );
}
add_shortcode( 'mrp_rating_result', 'mrp_rating_result_shortcode' );


/**
 * Shortcode to display the rating item results
 *
 */
function mrp_rating_item_results_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_rating_item_results', $atts ) ) {
		return;
	}

	// get the post id
	$post_id = null;
	if ( isset( $atts['post_id'] ) ) {
		if ( strlen( trim( $atts['post_id'] ) ) > 0 && is_numeric( $atts['post_id'] ) ) {
			$post_id = $atts['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( shortcode_atts( array(
			'post_id' => $post_id, // default to current post, set to "" to use all posts
			'no_rating_results_text' =>  $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' => $rating_form_id, // default to current rating form id, set to "" to use all rating forms
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'show_count' => true,
			'class' => '',
			'preserve_max_option' => true,
			'show_options' => false, // @deprecated
			'layout' => 'no_options',
			'rating_item_ids' => null,
			'user_roles' => null,
			'rating_entry_ids' => null,
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
	), $atts ) );

	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $preserve_max_option ) ) {
		$preserve_max_option = $preserve_max_option == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}

	// temp
	if ( is_string( $show_options ) ) {
		$show_options = $show_options == 'true' ? true : false;

		if ( $show_options == true ) {
			$layout = 'options_block';
		}
	}

	return mrp_rating_item_results( apply_filters( 'mrp_rating_item_results_params', array_merge( $atts, array(
			'post_id' => $post_id,
			'no_rating_results_text' => $no_rating_results_text,
			'rating_form_id' => $rating_form_id,
			'result_type' => $result_type,
			'show_count' => $show_count,
			'echo' => false,
			'class' => $class . ' mrp-shortcode',
			'preserve_max_option' => $preserve_max_option,
			'layout' => $layout,
			'rating_item_ids' => $rating_item_ids,
			'user_roles' => $user_roles,
			'rating_entry_ids' => $rating_entry_ids,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'comments_only' => $comments_only,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id,
			'post_ids' => $post_ids,
			'before_count' => $before_count,
			'after_count' => $after_count,
			'sort_by' => $sort_by,
			'show_rank' => $show_rank
	) ) ) );

}
add_shortcode( 'mrp_rating_item_results', 'mrp_rating_item_results_shortcode' );


/**
 * Shortcode function for displaying rating results list
 *
 * @param $atts
 * @return string
 */
function mrp_rating_results_list_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_rating_results_list', $atts ) ) {
		return;
	}

	$rating_form_id = MRP_Utils::get_rating_form( null, $atts );

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( shortcode_atts( array(
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'rating_form_id' =>  $rating_form_id,
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_count' => true,
			'show_category_filter' => true, // @deprecated
			'limit' => 10,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'show_rank' => true,
			'show_title' => true,
			'class' => '',
			'category_id' => 0, // 0 = All,
			'taxonomy' => null,
			'term_id' => 0, // 0 = All,
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecated
			'show_featured_img' => true,
			'image_size' => 'thumbnail',
			'show_filter' => false,
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'sort_by' => 'highest_rated',
			'rating_item_ids' => null,
			'user_roles' => null,
			'comments_only' => null,
			'from_date' => null,
			'to_date' => null,
			'rating_entry_ids' => null,
			'offset' => 0,
			'post_ids' => null
	), $atts ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_filter;
	}

	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $show_title ) ) {
		$show_title = $show_title == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_featured_img ) ) {
		$show_featured_img = $show_featured_img == 'true' ? true : false;
	}

	if ( $category_id != 0 ) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	return mrp_rating_results_list( apply_filters( 'mrp_rating_results_list_params', array_merge( $atts, array(
			'no_rating_results_text' => $no_rating_results_text,
			'show_count' => $show_count,
			'echo' => false,
			'title' => $title,
			'rating_form_id' => $rating_form_id,
			'show_filter' => $show_filter,
			'limit' => $limit,
			'result_type' => $result_type,
			'show_rank' => $show_rank,
			'show_title' => $show_title,
			'class' => $class . ' mrp-shortcode',
			'before_title' => $before_title,
			'after_title' => $after_title,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id, // 0 = All
			'filter_button_text' => $filter_button_text,
			'filter_label_text' => $filter_label_text,
			'show_featured_img' => $show_featured_img,
			'image_size' => $image_size,
			'sort_by' => $sort_by,
			'rating_item_ids' => $rating_item_ids,
			'user_roles' => $user_roles,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'comments_only' => $comments_only,
			'offset' => $offset,
			'rating_entry_ids' => $rating_entry_ids,
			'post_ids' => $post_ids
	) ) ) );

}
add_shortcode( 'mrp_rating_results_list', 'mrp_rating_results_list_shortcode' );

/**
 * Shortcode function for displaying the top rating results
 *
 * @param unknown_type $atts
 * @return string
 */
function mrp_user_rating_results_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_user_rating_results', $atts ) ) {
		return;
	}

	$rating_form_id = MRP_Utils::get_rating_form( null, $atts );

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	// get user id, ignore username if user_id is set. Default to current logged in user if
	// both username and user_id are not set
	$user_id = isset( $atts['user_id'] ) ? $atts['user_id'] : null;
	if ( $user_id == null) {
		if ( isset( $atts['username'] ) ) {
			$user = get_user_by( 'login', $atts['username'] );
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

	extract( shortcode_atts( array(
			'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'show_category_filter' => true, // @deprecated
			'show_date' => true,
			'show_rank' => true,
			'before_date' => '',
			'after_date' => '',
			'category_id' => 0, // 0 = All,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'limit' => 10,
			'class' => '',
			'show_title' => true,
			'taxonomy' => null,
			'term_id' => 0, // 0 = All
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecated
			'rating_form_id' => $rating_form_id, // TODO update docs to say default, was null previously
			'show_filter' => false,
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'sort_by' => 'highest_rated',
			'show_featured_img' => true,
			'image_size' => 'thumbnail',
			'rating_item_ids' => null,
			'show_count' => true,
			'user_roles' => null,
			'comments_only' => null,
			'from_date' => null,
			'to_date' => null,
			'rating_entry_ids' => null,
			'offset' => 0
	), $atts ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_filter;
	}

	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}
	if ( is_string( $show_date ) ) {
		$show_date = $show_date == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
	}
	if ( is_string( $show_count ) ) {
		$show_count = $show_count == 'true' ? true : false;
	}
	if ( is_string( $comments_only ) ) {
		$comments_only = $comments_only == 'true' ? true : false;
	}
	if ( is_string( $show_featured_img ) ) {
		$show_featured_img = $show_featured_img == 'true' ? true : false;
	}

	if ( $category_id != 0 ) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	return mrp_user_rating_results( apply_filters( 'mrp_user_rating_results_params', array_merge( $atts, array(
			'no_rating_results_text' => $no_rating_results_text,
			'show_date' => $show_date,
			'echo' => false,
			'title' => $title,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'show_rank' => $show_rank,
			'result_type' => $result_type,
			'limit' => $limit,
			'class' => $class . ' mrp-shortcode',
			'show_title' => $show_title,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id, // 0 = All
			'user_id' => $user_id,
			'filter_button_text' => $filter_button_text,
			'filter_label_text' => $filter_label_text,
			'show_filter' => $show_filter,
			'sort_by' => $sort_by,
			'show_featured_img' => $show_featured_img,
			'image_size' => $image_size,
			'rating_form_id' => $rating_form_id,
			'rating_item_ids' => $rating_item_ids,
			'user_roles' => $user_roles,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'comments_only' => $comments_only,
			'offset' => $offset,
			'rating_entry_ids' => $rating_entry_ids
	) ) ) );
}
add_shortcode( 'mrp_user_rating_results', 'mrp_user_rating_results_shortcode' );


/**
 * Shortcode to display rating entry details list
 *
 * @param unknown_type $atts
 */
function mrp_rating_entry_details_list_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_rating_result_reviews', $atts ) ) {
		return;
	}

	// get the post id
	$post_id = null;
	if ( isset( $atts['post_id'] ) ) {
		if ( strlen( trim( $atts['post_id'] ) ) > 0 && is_numeric( $atts['post_id'] ) ) {
			$post_id = $atts['post_id'];
		}
	} else {
		global $post;

		if ( isset( $post ) ) {
			$post_id = $post->ID;
		}
	}

	$rating_form_id = MRP_Utils::get_rating_form( $post_id );

	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

	extract( shortcode_atts( array(
			'post_id' => $post_id, // default to current post, set to "" to use all posts
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'rating_form_id' =>  $rating_form_id,
			'show_date' => true,
			'comments_only' => false,
			'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
			'before_name' => '',
			'after_name' => '',
			'before_comment' => '',
			'after_comment' => '',
			'show_name' => true,
			'show_comment' => true,
			'before_date' => '',
			'after_date' => '',
			'rating_entry_ids' => null,
			'limit' => 10,
			'category_id' => 0,
			'show_category_filter' => false, // @deprecated
			'class' => '',
			'taxonomy' => null,
			'term_id' => 0, // 0 = All
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'category_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION], // @deprecate
			'show_filter' => false,
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
			'sort_by' => 'highest_rated',
			'show_custom_fields' => true,
			'show_rating_items' => true,
			'show_overall_rating' => true,
			'show_permalink' => false,
			'user_roles' => null,
			'show_avatar' => true,
			'rating_item_ids' => null,
			'to_date' => null,
			'from_date' => null,
			'offset' => 0,
			'show_rank' => true,
			'post_ids' => null,
			'user_id' => null,
			'entry_status' => 'approved',
			'layout' => 'table',
			'generate_microdata' => false,
			'add_author_link' => true,

			// new
			'show_load_more' => false
	), $atts ) );

	// temp
	if ( is_string( $show_category_filter ) ) {
		$show_category_filter = $show_category_filter == 'true' ? true : false;
		$show_filter = $show_filter;
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
	if ( is_string( $show_comment ) ) {
		$show_comment = $show_comment == 'true' ? true : false;
	}
	if ( is_string( $show_rating_items ) ) {
		$show_rating_items = $show_rating_items == 'true' ? true : false;
	}
	if ( is_string( $show_rank ) ) {
		$show_rank = $show_rank == 'true' ? true : false;
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
	if ( is_string( $generate_microdata ) ) {
		$generate_microdata = $generate_microdata == 'true' ? true : false;
	}
	if ( is_string( $add_author_link ) ) {
		$add_author_link = $add_author_link == 'true' ? true : false;
	}
	if ( strlen( trim ( $entry_status ) ) == 0 ) {
		$entry_status = null;
	}

	if ( $category_id != 0 ) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}

	return mrp_rating_entry_details_list( apply_filters( 'mrp_rating_entry_details_list_params', array_merge( $atts, array(
			'post_id' => $post_id,
			'no_rating_results_text' => $no_rating_results_text,
			'rating_form_id' =>  $rating_form_id,
			'show_date' => $show_date,
			'comments_only' => $comments_only,
			'echo' => false,
			'result_type' => $result_type,
			'before_name' => $before_name,
			'after_name' => $after_name,
			'before_comment' => $before_comment,
			'after_comment' => $after_comment,
			'show_name' => $show_name,
			'show_comment' => $show_comment,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'rating_entry_ids' => $rating_entry_ids,
			'limit' => $limit,
			'show_rating_items' => $show_rating_items,
			'show_rank' => $show_rank,
			'result_type' => $result_type,
			'class' => $class . ' mrp-shortcode',
			'taxonomy' => $taxonomy,
			'term_id' => $term_id, // 0 = All
			'filter_button_text' => $filter_button_text,
			'filter_label_text' => $filter_label_text,
			'show_filter' => $show_filter,
			'sort_by' => $sort_by,
			'show_custom_fields' => $show_custom_fields,
			'show_permalink' => $show_permalink,
			'show_overall_rating' => $show_overall_rating,
			'sort_by' => $sort_by,
			'user_roles' => $user_roles,
			'rating_item_ids' => $rating_item_ids,
			'user_id' => $user_id,
			'to_date' => $to_date,
			'from_date' => $from_date,
			'offset' => $offset,
			'post_ids' => $post_ids,
			'show_avatar' => $show_avatar,
			'entry_status' => $entry_status,
			'layout' => $layout,
			'generate_microdata' => $generate_microdata,
			'add_author_link' => $add_author_link,
			'show_load_more' => $show_load_more
	) ) ) );
}
add_shortcode( 'mrp_rating_result_reviews', 'mrp_rating_entry_details_list_shortcode' ); // deprecated
add_shortcode( 'mrp_rating_entry_details_list', 'mrp_rating_entry_details_list_shortcode' );

/**
 * Shortcode to display the rating items in the WP comments form
 * @param unknown_type $atts
 */
function mrp_comment_rating_form_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_comment_rating_form', $atts ) ) {
		return;
	}

	// get the post id
	global $post;

	$post_id = null;
	if (isset( $post ) ) {
		$post_id = $post->ID;
	}

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	// TODO somehow find a way to set the rating form using a shortcode attribute

	/* if a rating form is not specified in post meta, use default settings
	$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
	if ( $rating_form_id == '') {
		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
	}*/

	extract( shortcode_atts( array(
			'post_id' => $post_id,
			'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			//'rating_form_id' => $rating_form_id,
			'class' => ''
	), $atts ) );

	if ( $post_id == null ) {
		return; // No post Id available
	}

	return mrp_comment_rating_form( apply_filters( 'mrp_comment_rating_form_params', array_merge( $atts, array(
			'post_id' => $post_id,
			'title' => $title,
			'submit_button_text' => $submit_button_text,
			//'rating_form_id' => $rating_form_id,
			'class' => $class,
			'echo' => false
	) ) ) );
}
add_shortcode( 'mrp_comment_rating_form' , 'mrp_comment_rating_form_shortcode' );


/**
 * Shortcode to display the user dashboard
 */
function mrp_user_ratings_dashboard_shortcode( $atts = array(), $content = null, $tag ) {

	$atts = ( is_array( $atts ) ) ? $atts : array();

	$can_do_shortcode = ! ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) );
	if ( ! apply_filters( 'mrp_can_do_shortcode', $can_do_shortcode, 'mrp_user_dashboard', $atts ) ) {
		return;
	}

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

	extract( shortcode_atts( array(
			'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
			'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION],
			'before_title' => '<h4>',
			'after_title' => '</h4>',
			'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
			'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
			'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
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
			'sort_by' => 'most_recent',
			'published_posts_only' => true,
			'comments_only' => false,
			'from_date' => null,
			'to_date' => null,
			'category_id' => 0, // 0 = All,
			'taxonomy' => null,
			'term_id' => 0, // 0 = All,
			'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
			'show_filter' => false,
			'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
	), $atts ) );

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
	if ( $category_id != 0 ) {
		$term_id = $category_id;
		$taxonomy = 'category';
	}
	if ( is_string( $show_filter ) ) {
		$show_filter = $show_filter == 'true' ? true : false;
	}

	return mrp_user_ratings_dashboard( apply_filters( 'mrp_user_ratings_dashboard_params', array_merge( $atts, array(
			'title' => $title,
			'before_title' => $before_title,
			'after_title' => $after_title,
			'submit_button_text' => $submit_button_text,
			'update_button_text' => $update_button_text,
			'delete_button_text' => $delete_button_text,
			'echo' => false,
			'class' => $class . ' mrp-shortcode',
			'user_can_update_delete' => $user_can_update_delete,
			'before_date' => $before_date,
			'after_date' => $after_date,
			'show_count' => $show_count,
			'result_type' => $result_type,
			'before_count' => $before_count,
			'after_count' => $after_count,
			'entry_status' => $entry_status,
			'approved_comments_only' => $approved_comments_only,
			'show_count_comments' => $show_count_comments,
			'limit' => $limit,
			'offset' => $offset,
			'sort_by' => $sort_by,
			'published_posts_only' => $published_posts_only,
			'comments_only' => $comments_only,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'no_rating_results_text' => $no_rating_results_text,
			'show_filter' => $show_filter,
			'taxonomy' => $taxonomy,
			'term_id' => $term_id, // 0 = All
			'filter_button_text' => $filter_button_text,
			'filter_label_text' => $filter_label_text,
	) ) ) );
}
add_shortcode( 'mrp_user_ratings_dashboard', 'mrp_user_ratings_dashboard_shortcode' );


/* to assist upgrading from the free version to the Pro version so you don't have to update post content
add_shortcode( 'mr_rating_form', 'mrp_rating_form_shortcode' );
add_shortcode( 'mr_rating_result', 'mrp_rating_result_shortcode' );
add_shortcode( 'mr_rating_results_list', 'mrp_rating_results_list' );
*/

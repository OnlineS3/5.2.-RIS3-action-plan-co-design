<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Support for WPML plugin
 */

/**
 * Get translated post id
 *
 * @param unknown $post_id
 * @param unknown $language_code
 */
function mrp_wpml_object_id( $post_id, $language_code = null) {

	if ( $language_code == null ) {
		$language_code = apply_filters( 'wpml_default_language', null );
	}

	return apply_filters( 'wpml_object_id',  $post_id, get_post_type( $post_id ), true, $language_code );
}
add_filter( 'mrp_object_id', 'mrp_wpml_object_id', 10, 2 );

/**
 * Register a single string
 *
 * @param unknown $name
 * @param unknown $value
 */
function mrp_wpml_register_single_string( $name, $value ) {
	do_action( 'wpml_register_single_string', 'multi-rating-pro', $name, $value );
}
add_action( 'mrp_register_single_string', 'mrp_wpml_register_single_string', 10, 2 );

/**
 * Translate a single string
 *
 * @param unknown $original_value
 * @param unknown $name
 * @param unknown $language_code
 */
function mrp_wpml_translate_single_string( $original_value, $name, $language_code = null ) {
	if ( $language_code == null ) {
		$language_code = apply_filters( 'wpml_current_language', null );
	}
	return apply_filters( 'wpml_translate_single_string', $original_value, 'multi-rating-pro', $name, $language_code );
}
add_filter( 'mrp_translate_single_string', 'mrp_wpml_translate_single_string', 10, 3 );

/**
 * Returns default language
 *
 * @param unknown $empty_value
 */
function mrp_wpml_default_language( $empty_value = null ) {
	return apply_filters( 'wpml_default_language', $empty_value );
}
add_filter( 'mrp_default_language', 'mrp_wpml_default_language', 10, 1 );


/**
 * Join posts by language
 *
 * @param unknown $select
 * @return unknown
 */
function mrp_wpml_query_join_object( $query_join, $table_prefix = null, $params = array() ) {

	global $wpdb;
	$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'icl_translations temp1 ON ';

	if ( $table_prefix ) {
		$query_join .= $table_prefix . '.';
	}

	$query_join .= 'post_id = temp1.element_id';

	return $query_join;
}
add_filter( 'mrp_user_rating_exists_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_rating_results_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_rating_result_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_rating_entry_result_list_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_missing_rating_results_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_missing_rating_entries_query_join', 'mrp_wpml_query_join_object', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_join', 'mrp_wpml_query_join_object', 10, 3 );


/**
 * Filter posts by specific language
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param string $table_prefix
 */
function mrp_wpml_query_where_post( $query_where, $params, $table_prefix = null ) {

	$post_id = $params['post_id'];
	$all_language_translations = isset( $params['all_language_translations'] ) ? $params['all_language_translations'] : false;

	global $wpdb;
	if ( $all_language_translations ) { // all translated posts
		// do not assume trid equals default language post_id
		$trid = $wpdb->get_var( $wpdb->prepare( 'SELECT trid FROM ' . $wpdb->prefix . 'icl_translations WHERE element_id = %d', $post_id ) );
		$query_where = $wpdb->prepare( ' temp1.trid = %d', $trid );
	} else { // only current language post
		$query_where = $wpdb->prepare( ' temp1.element_id = %d', $post_id );
	}

	return $query_where;

}
/**
 * Ensure we check for entries across all language posts
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param string $table_prefix
 * @return unknown
 */
function user_rating_exists_check_all_posts( $query_where, $params, $table_prefix = null ) {
	$params['all_language_translations'] = true;
	return mrp_wpml_query_where_post( $query_where, $params, $table_prefix );
}
add_filter( 'mrp_user_rating_exists_query_where_post', 'user_rating_exists_check_all_posts', 10, 3 );
add_filter( 'mrp_rating_result_query_where_post', 'mrp_wpml_query_where_post', 10, 3 );
add_filter( 'mrp_rating_entry_result_list_query_where_post', 'mrp_wpml_query_where_post', 10, 3 );
add_filter( 'mrp_rating_results_query_where_post', 'mrp_wpml_query_where_post', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_where_post', 'mrp_wpml_query_where_post', 10, 3 );


/**
 * Checks post type
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param unknown $table_prefix
 * @return string
 */
function mrp_wpml_query_where_post_type( $query_where, $params, $table_prefix = null ) {

	$all_language_translations = isset( $params['all_language_translations'] ) ? $params['all_language_translations'] : true;

	$language_code = apply_filters( 'wpml_current_language', null );
	if ( $language_code == 'all' ) {
		$language_code = apply_filters( 'wpml_default_language', null );
	}

	if ( $all_language_translations == false ) { // only get current language
		$query_where .= ' AND temp1.language_code = "' . $language_code . '"';
	}

	return $query_where . ' AND temp1.element_type LIKE "post_%%" ';

}
add_filter( 'mrp_rating_entry_result_list_query_where', 'mrp_wpml_query_where_post_type', 10, 3 );
add_filter( 'mrp_user_rating_exists_query_where', 'mrp_wpml_query_where_post_type', 10, 3 );
add_filter( 'mrp_rating_result_query_where', 'mrp_wpml_query_where_post_type', 10, 3 );
add_filter( 'mrp_rating_results_query_where', 'mrp_wpml_query_where_post_type', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_where', 'mrp_wpml_query_where_post_type', 10, 3 );


/**
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param string $table_prefix
 */
function mrp_wpml_missing_query_where_post( $query_where, $params, $table_prefix = null ) {
	if ( ! isset( $params['all_language_translations'] ) ) { // in case set to false in shortcode for example
		$params['all_language_translations'] = true;
	}
	return mrp_wpml_query_where_post( $query_where, $params, $table_prefix );
}
add_filter( 'mrp_missing_rating_entries_query_where_post', 'mrp_wpml_missing_query_where_post', 10, 3 );


/**
 *
 * @param unknown $post_where
 * @param unknown $post_id
 * @return unknown
 */
function mrp_wpml_query_where_original( $query_where, $params = array(), $table_prefix = null ) {
	// do not have the post ids but want to get all post ids in the defualt language
	return ' AND temp1.element_type LIKE "post_%%" AND temp1.source_language_code IS NULL';
}
add_filter( 'mrp_missing_rating_results_query_where', 'mrp_wpml_query_where_original', 10, 3 );


/**
 *
 * @param unknown $params
 */
function mrp_wpml_all_language_translations_param_on( $params ) {
	$params['all_language_translations'] = true;
	return $params;
}
add_filter( 'mrp_calculate_rating_result_params', 'mrp_wpml_all_language_translations_param_on', 10, 1 );
add_filter( 'mrp_calculate_rating_item_result_params', 'mrp_wpml_all_language_translations_param_on', 10, 1 );


/**
 * Turn off showing rating entries for all language translations, unless content
 * shown in WP-admin for all languages or specifically turned on.
 *
 * @param unknown $params
 */
function mrp_wpml_rating_entry_details_list_params( $params ) {

	$language_code = apply_filters( 'wpml_current_language', null );
	if ( $language_code == 'all' ) {
		// e.g. in WP-admin selecting all languages
		$params['all_language_translations']  = true;
	} else if ( ! isset( $params['all_language_translations'] ) ) {
		// in case set to false in shortcode for example
		$params['all_language_translations'] = false;
	}

	return $params;
}
add_filter( 'mrp_rating_entry_details_list_params', 'mrp_wpml_rating_entry_details_list_params', 10, 1 );

/**
 * Turn off showing post ratings for all language translations, unless specifically
 * turned on.
 *
 * @param unknown $params
 */
function mrp_wpml_rating_results_list_params( $params ) {

	if ( ! isset( $params['all_language_translations'] ) ) {
		// in case set to false in shortcode for example
		$params['all_language_translations'] = false;
	}

	return $params;
}
add_filter( 'mrp_rating_results_list_params', 'mrp_wpml_rating_results_list_params', 10, 1 );


/**
 * Finds translated posts and deletes their calculated ratings
 */
function mrp_wpml_delete_rating_result_translated_posts( $params, $and ) {

	if ( isset( $params['post_id'] ) ) {

		// ensure this function is not called more than once
		remove_action( 'mrp_delete_rating_result', 'mrp_wpml_delete_rating_result_translated_posts', 10 );

		$post_id = intval( $params['post_id'] );
		$element_type = 'post_' . get_post_type( $post_id );

		$trid = apply_filters( 'wpml_element_trid', null, $post_id, $element_type );
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );

		$temp_params = $params;
		foreach ( $translations as $key => $value ) {

			if ( $value->element_id != $post_id ) {
				$temp_params['post_id'] = $value->element_id;
				MRP_Multi_Rating_API::delete_calculated_ratings( $temp_params, $and );
			}
		}
	}

}
add_action( 'mrp_delete_rating_result', 'mrp_wpml_delete_rating_result_translated_posts', 10, 2 );



/**
 * Finds translated posts, calculates and saves their calculated ratings
 */
function mrp_wpml_save_rating_result_translated_posts( $rating_result, $params ) {

	if ( isset( $rating_result['post_id'] ) ) {

		// ensure this function is not called more than once
		remove_action( 'mrp_save_rating_result', 'mrp_wpml_save_rating_result_translated_posts', 10 );

		$post_id = intval( $rating_result['post_id'] );
		$element_type = 'post_' . get_post_type( $post_id );

		$trid = apply_filters( 'wpml_element_trid', null, $post_id, $element_type );
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, $element_type );

		$temp_params = $params;
		$temp_params['rating_form_id'] = $rating_result['rating_form_id']; // for some reason this is lost...
		foreach ( $translations as $key => $value ) {

			if ( $value->element_id != $post_id ) {
				$temp_params['post_id'] = $value->element_id;
				$temp_rating_result = mrp_calculate_rating_result( $temp_params );
				MRP_Multi_Rating_API::save_rating_result( $temp_rating_result, $temp_params );
			}
		}
	}

}
add_action( 'mrp_save_rating_result', 'mrp_wpml_save_rating_result_translated_posts', 10, 2 );

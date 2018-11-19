<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Support for Polylang plugin
 */

/**
 * Get translated post id
 *
 * @param unknown $element_id
 * @param unknown $language_code
 */
function mrp_polylang_object_id( $post_id, $language_code = null ) {
	if ( function_exists( 'pll_get_post' ) && function_exists( 'pll_default_language' ) ) {
		return pll_get_post( $post_id, pll_default_language() ); // current language
	}
	return $post_id;
}
add_filter( 'mrp_object_id', 'mrp_polylang_object_id', 10, 2 );


/**
 * Register a single string
 *
 * @param unknown $name
 * @param unknown $value
*/
function mrp_polylang_register_single_string( $name, $value ) {
	if ( is_admin() && function_exists( 'pll_register_string' ) ) {
		pll_register_string( $name, $value, 'multi-rating-pro' );
	}
}
add_action( 'mrp_register_single_string', 'mrp_polylang_register_single_string', 10, 2 );

/**
 * Translate a single string
 *
 * @param unknown $original_value
 * @param unknown $name
 * @param unknown $language_code
*/
function mrp_polylang_translate_single_string( $original_value, $name, $language_code = null ) {
	if ( function_exists( 'pll_translate_string' ) ) {
		return pll_translate_string( $original_value, $language_code );
	}
	return $original_value;
}
add_filter( 'mrp_translate_single_string', 'mrp_polylang_translate_single_string', 10, 3 );

/**
 * Returns default language
 *
 * @param unknown $empty_value
*/
function mrp_polylang_default_language( $empty_value = null ) {
	if ( function_exists( 'pll_default_language' ) ) {
		return pll_default_language( 'locale' );
	}
	return $empty_value;
}
add_filter( 'mrp_default_language', 'mrp_polylang_default_language', 10, 1 );

/**
 * Join posts by language
 *
 * @param unknown $select
 * @return unknown
 */
function mrp_polylang_query_join_object( $query_join, $table_prefix = null, $params = array() ) {

	//if ( ! isset( $params['taxonomy'] ) ) {
		global $wpdb;
		$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel2 ON rel2.object_id = ';
		if ( $table_prefix != null) {
			$query_join .= $table_prefix . '.';
		}
		$query_join .= 'post_id';
		$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax2 ON tax2.term_taxonomy_id = rel2.term_taxonomy_id';
		$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t2 ON t2.term_id = tax2.term_id';
	//}

	return $query_join;
}
add_filter( 'mrp_user_rating_exists_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_rating_results_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_rating_result_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_rating_entry_result_list_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_missing_rating_results_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_missing_rating_entries_query_join', 'mrp_polylang_query_join_object', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_join', 'mrp_polylang_query_join_object', 10, 3 );


/**
 * Filter posts by specific language
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param string $table_prefix
*/
function mrp_polylang_query_where_post( $query_where, $params, $table_prefix = null ) {

	$post_id = isset( $params['post_id'] ) ? $params['post_id'] : null;
	$all_language_translations = isset( $params['all_language_translations'] ) ? $params['all_language_translations'] : false;

	// if post type is not translatable, we don't need to feth any other language translations
	if ( $post_id && ! pll_is_translated_post_type( get_post_type( $post_id ) ) ) {
		return $query_where;
	}

	global $wpdb;

	if ( $all_language_translations ) { // all translated posts
		$translations = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( 'SELECT description FROM ' . $wpdb->prefix . 'term_relationships rel, ' . $wpdb->prefix . 'term_taxonomy tax WHERE rel.object_id = %d AND tax.taxonomy = "post_translations" AND tax.term_taxonomy_id = rel.term_taxonomy_id', $post_id ) ) );

		if ( count( $translations ) > 0 ) {
			$post_ids = array_values( $translations );
			$query_where = ' rel2.object_id IN ( ' . implode( ', ', $post_ids ) . ' )';
		}
		$query_where .= ' AND tax2.taxonomy = "language"';

	} else { // only current language post
		$query_where = $wpdb->prepare( ' rel2.object_id = %d AND tax2.taxonomy = "language"', $post_id );
	}

	return $query_where;

}
add_filter( 'mrp_rating_result_query_where_post', 'mrp_polylang_query_where_post', 10, 3 );
add_filter( 'mrp_rating_entry_result_list_query_where_post', 'mrp_polylang_query_where_post', 10, 3 );
add_filter( 'mrp_rating_results_query_where_post', 'mrp_polylang_query_where_post', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_where_post', 'mrp_polylang_query_where_post', 10, 3 );


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
	return mrp_polylang_query_where_post( $query_where, $params, $table_prefix );
}
add_filter( 'mrp_user_rating_exists_query_where_post', 'user_rating_exists_check_all_posts', 10, 3 );

/**
 * Checks post type
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param unknown $table_prefix
 * @return string
*/
function mrp_polylang_query_where_taxonomy_language( $query_where, $params, $table_prefix = null ) {

	$post_id = isset( $params['post_id'] ) ? $params['post_id'] : null;

	// if post type is not translatable, so we don't need to do anything here
	if ( $post_id && ! pll_is_translated_post_type( get_post_type( $post_id ) ) ) {
		return $query_where;
	}

	global $polylang;
	//if ( isset( $polylang ) ) {
	$pll_post_types = $polylang->model->get_translated_post_types( false );
	//}

	global $wpdb;
	$non_translatable = 'SELECT * FROM  ' . $wpdb->posts . ' p2 WHERE rel2.object_id = p2.ID AND p2.post_type IN ( "' . implode( '", "', $pll_post_types ) . '" )';

	$all_language_translations = isset( $params['all_language_translations'] ) ? $params['all_language_translations'] : true;

	//$query_where .= ' AND ( (';

	if ( $all_language_translations === false ) { // only get current language
		$query_where .= ' AND ( ( t2.slug = "' . pll_current_language( 'slug' ) . '" AND tax2.taxonomy = "language" ) OR ( tax2.taxonomy IS NULL ) )';
	} else {
		$query_where .= ' AND ( ( NOT EXISTS ( ' . $non_translatable . ' ) ) OR ( tax2.taxonomy = "language" ) )';
	}
	return $query_where;

}
add_filter( 'mrp_rating_entry_result_list_query_where', 'mrp_polylang_query_where_taxonomy_language', 10, 3 );
add_filter( 'mrp_user_rating_exists_query_where', 'mrp_polylang_query_where_taxonomy_language', 10, 3 );
add_filter( 'mrp_rating_result_query_where', 'mrp_polylang_query_where_taxonomy_language', 10, 3 );
add_filter( 'mrp_rating_results_query_where', 'mrp_polylang_query_where_taxonomy_language', 10, 3 );
add_filter( 'mrp_rating_item_entries_query_where', 'mrp_polylang_query_where_taxonomy_language', 10, 3 );

/**
 *
 * @param unknown $query_where
 * @param unknown $params
 * @param string $table_prefix
*/
function mrp_polylang_missing_query_where_post( $query_where, $params, $table_prefix = null ) {
	$params['all_language_translations'] = true;
	return mrp_polylang_query_where_post( $query_where, $params, $table_prefix );
}
add_filter( 'mrp_missing_rating_entries_query_where_post', 'mrp_polylang_missing_query_where_post', 10, 3 );


/**
 *
 * @param unknown $post_where
 * @param unknown $post_id
 * @return unknown
*/
function mrp_polylang_query_where_original( $query_where, $params = array(), $table_prefix = null ) {
	// do not have the post ids but want to get all post ids in the default language

	global $polylang;
	//if ( isset( $polylang ) ) {
	$pll_post_types = $polylang->model->get_translated_post_types( false );
	//}

	global $wpdb;
	$non_translatable = '(SELECT * FROM  ' . $wpdb->posts . ' p2 WHERE rel2.object_id = p2.ID AND p2.post_type IN ( "' . implode( '", "', $pll_post_types ) . '" ) ) )';

	$query_where .= ' AND ( (';

	$current_language_slug =  pll_current_language();
	if ( $current_language_slug ) {
		$query_where .= ' t2.slug = "' . pll_current_language() . '" AND';
	} else {
		$query_where .= ' NOT EXISTS( ' . $non_translatable . ' ) OR (';
	}

	$query_where .= ' tax2.taxonomy = "language" ) )';
}
add_filter( 'mrp_missing_rating_results_query_where', 'mrp_polylang_query_where_original', 10, 3 );


/**
 *
 * @param unknown $params
*/
function mrp_polylang_all_language_translations_param_on( $params ) {
	$params['all_language_translations'] = true;
	return $params;
}
add_filter( 'mrp_calculate_rating_result_params', 'mrp_polylang_all_language_translations_param_on', 10, 1 );
add_filter( 'mrp_calculate_rating_item_result_params', 'mrp_polylang_all_language_translations_param_on', 10, 1 );


/**
 *
 * @param unknown $params
*/
function mrp_polylang_all_language_translations_param_off( $params ) {

	$current_language_slug =  pll_current_language();
	if ( $current_language_slug == false ) {
		// e.g. in WP-admin selecting all languages
		$params['all_language_translations']  = true;
	} else if ( ! isset( $params['all_language_translations'] ) ) {
		// in case set to false in shortcode for example
		$params['all_language_translations'] = false;
	}

	return $params;
}
add_filter( 'mrp_rating_entry_details_list_params', 'mrp_polylang_all_language_translations_param_off', 10, 1 );


/**
 * Finds translated posts and deletes their calculated ratings
 */
function mrp_polylang_delete_rating_result_translated_posts( $params, $and ) {

	if ( isset( $params['post_id'] ) ) {

		// ensure this function is not called more than once
		remove_action( 'mrp_delete_rating_result', 'mrp_polylang_delete_rating_result_translated_posts', 10 );

		$post_id = intval( $params['post_id'] );

		// if post type is not translatable, so we don't need to do anything here
		if ( $post_id && ! pll_is_translated_post_type( get_post_type( $post_id ) ) ) {
			return;
		}

		global $wpdb;
		$translations = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( 'SELECT description FROM ' . $wpdb->prefix . 'term_relationships rel, ' . $wpdb->prefix . 'term_taxonomy tax WHERE rel.object_id = %d AND tax.taxonomy = "post_translations" AND tax.term_taxonomy_id = rel.term_taxonomy_id', $post_id ) ) );

		if ( count( $translations ) > 0 ) {

			$temp_params = $params;
			foreach ( $translations as $key => $value ) {

				if ( $value != $post_id ) {
					$temp_params['post_id'] = $value;
					MRP_Multi_Rating_API::delete_calculated_ratings( $temp_params, $and );
				}
			}
		}
	}

}
add_action( 'mrp_delete_rating_result', 'mrp_polylang_delete_rating_result_translated_posts', 10, 2 );



/**
 * Finds translated posts, calculates and saves their calculated ratings
 */
function mrp_polylang_save_rating_result_translated_posts( $rating_result, $params ) {

	if ( isset( $rating_result['post_id'] ) ) {

		// ensure this function is not called more than once
		remove_action( 'mrp_save_rating_result', 'mrp_polylang_save_rating_result_translated_posts', 10 );

		$post_id = intval( $rating_result['post_id'] );

		// if post type is not translatable, so we don't need to do anything here
		if ( $post_id && ! pll_is_translated_post_type( get_post_type( $post_id ) ) ) {
			return;
		}

		global $wpdb;
		$translations = maybe_unserialize( $wpdb->get_var( $wpdb->prepare( 'SELECT description FROM ' . $wpdb->prefix . 'term_relationships rel, ' . $wpdb->prefix . 'term_taxonomy tax WHERE rel.object_id = %d AND tax.taxonomy = "post_translations" AND tax.term_taxonomy_id = rel.term_taxonomy_id', $post_id ) ) );

		if ( count( $translations ) > 0 ) {

			$temp_params = $params;
			$temp_params['rating_form_id'] = $rating_result['rating_form_id']; // for some reason this is lost...
			foreach ( $translations as $key => $value ) {

				if ( $value != $post_id ) {
					$temp_params['post_id'] = $value;
					$temp_rating_result = mrp_calculate_rating_result( $temp_params );
					MRP_Multi_Rating_API::save_rating_result( $temp_rating_result, $temp_params );
				}
			}
		}
	}

}
add_action( 'mrp_save_rating_result', 'mrp_polylang_save_rating_result_translated_posts', 10, 2 );

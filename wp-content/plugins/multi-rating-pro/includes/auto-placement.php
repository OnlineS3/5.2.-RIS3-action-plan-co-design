<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filters the_content() to perform auto placements of the rating form on a page or post
 *
 * @param $content
 * @return filtered content
 */
function mrp_filter_the_content( $content ) {
	
	// get the post id
	global $post;
	
	$post_id = null;
	if ( ! isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( ! isset($post) && ! isset( $post_id ) ) {
		return $content; // No post id available to display rating form
	}
	
	$can_apply_filter = ! ( is_feed() || ! in_the_loop() || ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) );
	if ( ! apply_filters( 'mrp_can_apply_auto_placement', $can_apply_filter, 'the_content', $content, $post_id ) ) {
		return $content;
	}
	
	$rating_form_id = MRP_Utils::get_rating_form( $post_id );
	
	$rating_form_html = null;
	$rating_result_html = null;
	
	$rating_form_position = mrp_get_rating_form_position( $post_id );
	
	if ( $rating_form_position == 'before_content' || $rating_form_position == 'after_content' ) {
		
		$rating_form_callback = apply_filters( 'mrp_rating_form_callback', 'mrp_rating_form_callback', $post_id, $rating_form_id );
		$rating_form_html = call_user_func_array( $rating_form_callback, array( $post_id, $rating_form_id, $rating_form_position ) );
	
	}

	$rating_result_position = mrp_get_rating_results_position( $post_id );
	
	if ( $rating_result_position == 'before_content' || $rating_result_position == 'after_content' ) {
		
		$rating_result_callback = apply_filters( 'mrp_rating_result_callback', 'mrp_rating_result_callback', $post_id, $rating_form_id );
		$rating_result_html = call_user_func_array( $rating_result_callback, array( $post_id, $rating_form_id, $rating_result_position ) );
		
	}
		
	$filtered_content = '';
	
	if ( $rating_result_position == 'before_content' && $rating_result_html != null ) {
		$filtered_content .= $rating_result_html;
	}
	
	if ( $rating_form_position == 'before_content' && $rating_form_html != null ) {
		$filtered_content .= $rating_form_html;
	}
	
	$filtered_content .= $content;
	
	if ( $rating_result_position == 'after_content' && $rating_result_html != null ) {
		$filtered_content .= $rating_result_html;
	}
	
	if ( $rating_form_position == 'after_content' && $rating_form_html != null ) {
		$filtered_content .= $rating_form_html;
	}
		
	do_action( 'mrp_after_auto_placement', 'the_content', $post_id, $rating_form_id );
	
	return $filtered_content;
}
add_filter( 'the_content', 'mrp_filter_the_content' );



/**
 * Filters the_title() to perform auto placement of the rating results next to the page or post title
 *
 * @param $content
 * @return filtered content
 */
function mrp_filter_the_title( $title ) {
	
	// get the post id
	global $post;
	
	$post_id = null;
	if ( ! isset( $post_id ) && isset( $post ) ) {
		$post_id = $post->ID;
	} else if ( ! isset($post) && ! isset( $post_id ) ) {
		return $title; // No post id available to display rating result
	}

	$can_apply_filter = ! ( is_feed() || ! in_the_loop() || ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) );
	if ( ! apply_filters( 'mrp_can_apply_auto_placement', $can_apply_filter, 'the_title', $title, $post_id ) ) {
		return $title;
	}
	
	$rating_form_id = MRP_Utils::get_rating_form( $post_id );
	
	$rating_result_position = mrp_get_rating_results_position( $post_id );
	
	$rating_result_html = null;
	
	if ( $rating_result_position == 'before_title' || $rating_result_position == 'after_title' ) {
		
		$rating_result_callback = apply_filters( 'mrp_rating_result_callback', 'mrp_rating_result_callback', $post_id, $rating_form_id );
		$rating_result_html = call_user_func_array( $rating_result_callback, array( $post_id, $rating_form_id, $rating_result_position ) );
		
	}
	
	$filtered_title = '';

	if ( $rating_result_position == 'before_title' && $rating_result_html != null ) {
		$filtered_title .= $rating_result_html;
	}

	$filtered_title .= $title;

	if ( $rating_result_position == 'after_title' && $rating_result_html != null ) {
		$filtered_title .= $rating_result_html; 
	}
	
	do_action( 'mrp_after_auto_placement', 'the_title', $post_id, $rating_form_id );
	
	return $filtered_title;
}
add_filter( 'the_title', 'mrp_filter_the_title' );

/**
 * Makes sure filter is only called once per post. Otherwise the rating results or rating form could be displayed 
 * multiple times depending on the theme compatibility. This filter can be removed easily if needed to suit your theme needs
 * 
 * @param $filter
 * @param $post_id
 * @param $rating_form_id
 */
function mrp_check_auto_placement( $filter, $post_id, $rating_form_id)  {
	// only apply filter once... hopefully, this is the post title...
	if ( in_the_loop() && ( is_single() || is_page() || is_attachment() ) ) {
		remove_filter( $filter, 'mrp_filter_' . $filter );
	}
}
add_action( 'mrp_after_auto_placement', 'mrp_check_auto_placement', 10, 3);

/**
 * Checks filters settings to determine whether auto placement can be applied
 * 
 * @param boolean $can_apply_filter
 * @param string $filter_name
 * @param string $value
 * @param int $post_id
 * @return $can_apply_filter
 */
function mrp_can_apply_auto_placement( $can_apply_filter, $filter_name, $value, $post_id ) {
	
	if ( $can_apply_filter ) {

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
	
		$can_apply_filter = ( MRP_Utils::check_post_type_enabled( $post_id )
				&& ( ! is_home() || ( is_home() && $auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == false ) )
				&& ( ! is_search() || ( is_search() && $auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] == false ) )
				&& ( ! is_archive() || ( is_archive() && $auto_placement_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == false) ) );
	}

	return $can_apply_filter;
}
add_filter( 'mrp_can_apply_auto_placement', 'mrp_can_apply_auto_placement', 10, 4 );


/**
 * Displays rating form. Function can be replaced by mrp_rating_form_callback filter.
 * 
 * @param unknown $post_id
 * @param unknown $rating_form_id
 * @param unknown $rating_form_position
 * @return html
 */
function mrp_rating_form_callback( $post_id, $rating_form_id, $rating_form_position ) {
	
	return mrp_rating_form( array(
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'echo' => false,
			'class' => 'mrp-filter ' . $rating_form_position
	) );
	
}

/**
 * Displays rating result. Function can be replaced by mrp_rating_result_callback filter.
 *
 * @param unknown $post_id
 * @param unknown $rating_form_id
 * @param unknown $rating_form_position
 * @return html
 */
function mrp_rating_result_callback( $post_id, $rating_form_id, $rating_result_position ) {
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	
	return mrp_rating_result( array(
			'rating_form_id' => $rating_form_id,
			'post_id' => $post_id,
			'echo' => false,
			'show_date' => false,
			'generate_microdata' => $general_settings[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] && is_singular(),
			'class' => 'mrp-filter ' . $rating_result_position
	) );
	
}
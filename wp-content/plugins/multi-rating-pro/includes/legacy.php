<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Legacy stuff can go here for backwards compatibility
 */

add_shortcode( 'display_rating_item_results', 'mrp_rating_item_results_shortcode' ); // @deprecated
add_shortcode( 'display_rating_form', 'mrp_rating_form_shortcode' ); // @deprecated
add_shortcode( 'display_rating_result', 'mrp_rating_result_shortcode' ); // @deprecated
add_shortcode( 'display_top_rating_results', 'mrp_rating_results_list_shortcode' ); // @deprecated
add_shortcode( 'display_user_rating_results', 'mrp_user_rating_results_shortcode' ); // @deprecated
add_shortcode( 'display_rating_result_reviews', 'mrp_rating_result_reviews_shortcode' ); // @deprecated
add_shortcode( 'display_comment_rating_form' , 'mrp_comment_rating_form_shortcode' ); // @deprecated
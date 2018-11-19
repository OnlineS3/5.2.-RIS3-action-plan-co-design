<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for post meta.
*
* @author dpowney
*
*/
class MRP_Post_Meta_Test extends WP_UnitTestCase {
	
	/**
	 * Tests for overall rating present in post meta
	 *
	 * @group func39
	 */
	function test_save_rating_action() {
		
		global $wpdb;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
				'max_option_value' => 5
		) );
			
		$rating_item_id = $wpdb->insert_id;
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$user_id = $this->factory->user->create();
		
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
		
		// add rating
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		
		// clean, should match
		$adjusted_star_result = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating', true );
		$count_entries = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries', true );
		$this->assertEquals( $adjusted_star_result, $rating_result['adjusted_star_result'] );
		$this->assertEquals( $count_entries, $rating_result['count_entries'] );
		$this->assertEquals( 1, $rating_result['count_entries'] );
		
		// now delete rating result and check it's delete from post meta
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		$adjusted_star_result = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating', true );
		$count_entries = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries', true );
		$this->assertEquals( $adjusted_star_result, null );
		$this->assertEquals( $count_entries, null );
		
		// now add rating back
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		// create another for a different pos
		$post_id2 = $this->factory->post->create( array( 'post_title' => 'Test Post 2' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id2,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 3
		), array( '%d', '%d', '%d' ) );
		
		// and add another using a different rating form
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
				'name' => 'Rating form 2'
		), array( '%s' ) );
		
		$rating_form_id2 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id2,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 5
		), array( '%d', '%d', '%d' ) );
		
		$rating_result1 = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$rating_result2 = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id2, 'rating_form_id' => $rating_form_id ) );
		$this->assertEquals( 3, $rating_result2['adjusted_star_result'] );
		$rating_result3 = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id2 ) );
		$this->assertEquals( 5, $rating_result3['adjusted_star_result'] );
		
		// now delete using rating_form_id only, also check the other rating with a different rating form still exists
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_form_id' => $rating_form_id ) );
		
		$adjusted_star_result = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating', true );
		$this->assertEquals( $adjusted_star_result, null );
		$adjusted_star_result2 = get_post_meta( $post_id2, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating', true );
		$this->assertEquals( $adjusted_star_result2, null );
		
		$adjusted_star_result3 = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id2 . '_star_rating', true );
		$this->assertEquals( $adjusted_star_result3, $rating_result3['adjusted_star_result'] );
		
		// now delete only post_id
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id ) );
		
		$adjusted_star_result3 = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id2 . '_star_rating', true );
		$this->assertEquals( $adjusted_star_result3, null );
		
	}
	
	public function setUp() {
		
		parent::setUp();
		
		global $wpdb;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array( 'name' => __( 'Default rating form', 'multi-rating-pro' ) ) );
		$rating_form_id = $wpdb->insert_id;
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION] = $rating_form_id;
		$general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION] = 2;
		
		update_option( MRP_Multi_Rating::GENERAL_SETTINGS, $general_settings );
	}

	public function tearDown() {
	
		parent::tearDown();
	
		global $wpdb;
	
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE 1' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE 1' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' WHERE 1' );
		//$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_SUBJECT_TBL_NAME . ' WHERE 1' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' WHERE 1' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' WHERE 1' );
		$wpdb->query( 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME . ' WHERE 1' );
	
		wp_cache_delete( 'mrp_rating_forms' );
		wp_cache_delete( 'mrp_filters' );
		wp_cache_delete( 'mrp_rating_items' );
	}
}
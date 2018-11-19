<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for rating item results
 * 
 * @author dpowney
 *
 */
class MRP_Rating_Item_Result_Test extends WP_UnitTestCase {
	
	/**
	 * Tests rating item result for two rating items in a rating form. Checks two entries.
	 * 
	 * @group func44
	 */
	public function test_rating_item_result1() {
	
		global $wpdb;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 1',
				'max_option_value' => 5
		) );
			
		$rating_item_id1 = $wpdb->insert_id;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 2',
				'max_option_value' => 3
		) );
			
		$rating_item_id2 = $wpdb->insert_id;
	
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
	
		$user_id = $this->factory->user->create();
	
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array( 
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id
		) );
		$rating_item = $rating_items[$rating_item_id1];
		
		/*
		 * Entry 1
		 * 3.125 stars, 5/8 score, 63% (0.625)
		 */
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 4 // <<<<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
	
		$rating_result = MRP_Multi_Rating_API::get_rating_item_result( 
				array( 'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'rating_item' => $rating_item // <<<<<<<<<<<<<<<
				) );
	
		// 4 stars, 4/5 score, 80% (0.8)
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		$this->assertEquals( $rating_item_id1, $rating_result['rating_item_id'] );
	
		/*
		 * Entry 2 anonymous
		 * 5 stars, 8/8 score and 100%
		 */
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 5 // <<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 3
		), array( '%d', '%d', '%d' ) );
		
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
	
		/*
		 * Rating result
		 */
		$rating_result = MRP_Multi_Rating_API::get_rating_item_result( 
				array( 'post_id' => $post_id, 
						'rating_form_id' => $rating_form_id,
						'rating_item' => $rating_item // <<<<<<<<<<<<<<<
		 ) );
	
		// 4.5 stars, 4.5/5 score, 90% (.90)
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 4.5, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4.5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 90, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		$this->assertEquals( $rating_item_id1, $rating_result['rating_item_id'] );
	}
	
	/**
	 * Tests post_ids parameter
	 * 
	 * @group func7
	 */
	public function test_rating_item_result3() {
		
		global $wpdb;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 1',
				'max_option_value' => 5
		) );
			
		$rating_item_id1 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 2',
				'max_option_value' => 5
		) );
			
		$rating_item_id2 = $wpdb->insert_id;
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$post_ids = $this->factory->post->create_many( 3 );
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array(
				'rating_form_id' => $rating_form_id
		) );
		$rating_item = $rating_items[$rating_item_id1];
	
		// 4 entries - rating item id (1, 3), (4) and (5) and
		
		// 0
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 1 // <<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 5 // doesn't matter
		), array( '%d', '%d', '%d' ) );
		
		
		// 0
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 3 // <<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 5 // doesn't matter
		), array( '%d', '%d', '%d' ) );
		
		
		// 4
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[1],
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 4 // <<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 5 // doesn't matter
		), array( '%d', '%d', '%d' ) );
		
		
		// 5
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[2],
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 5 // <<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 5 // doesn't matter
		), array( '%d', '%d', '%d' ) );

		
		// let's use posts 0 and 1 = 1,3 and 4 = ( 1/5 + 3/5 + 4/5 ) / 3 * 5 = (  0.2 + 0.6 + 0.8 ) / 3 * 5 = 2.65/5
		$rating_result = MRP_Multi_Rating_API::get_rating_item_result(
				array( 'post_ids' => $post_ids[0] . ',' . $post_ids[1],
						'rating_form_id' => $rating_form_id,
						'rating_item' => $rating_item // <<<<<<<<<<<<<<<
				) );
		
		// 4.5 stars, 4.5/5 score, 90% (.90)
		$this->assertEquals( 2.67, $rating_result['adjusted_star_result'] );
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


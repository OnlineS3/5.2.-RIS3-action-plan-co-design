<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for rating results.
 * 
 * @author dpowney
 *
 */
class MRP_Rating_Result_Test extends WP_UnitTestCase {
	
	/**
	 * Tests rating result for a single entry
	 * 
	 * @group func90
	 */
	function test_rating_result1() {
		
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
		
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result for 10 entries
	 * 
	 * @group func
	 */
	public function test_rating_result2() {
		
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
		
		$rating_item_values = array( 5, 4, 5, 4, 3, 1, 5, 3, 5, 5 ); // = total = 40. 80%, 4/5 or 4/5 stars
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
		
		foreach ( $rating_item_values as $rating_item_value ) {
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
					'value' => $rating_item_value
			), array( '%d', '%d', '%d' ) );
		}
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

		$this->assertEquals( 10, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result for two rating items in a rating form. Checks for 1 entry 
	 * and then 2 entries
	 * 
	 * @group func
	 */
	public function test_rating_result3() {
		
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
		
		/*
		 * Entry 1
		 * 3.125 stars, 5/8 score, 62.5% (0.625)
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
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		
		// 3.125 stars, 5/8 score, 62.5% (.625)
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	
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
				'value' => 5
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
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		
		// 4.06 stars ( ( 3.13 + 5 )  / 2 = 4.065 ), 6.5 ( ( 5 + 8 ) / 2 ), 81.25 ( 0.8125) 
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 4.07, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 6.5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 81.25, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result for two rating items in a rating form. Checks for 2 entries where 1 is
	 * not approved
	 * 
	 * @group funczz
	 */
	public function test_rating_result4() {
	
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
	
		/*
		 * Entry 1
		 * 3.125 stars, 5/8 score, 62.5% (0.625)
		*/
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved', // <<<<<<<<<<<<<<<<
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
	
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
	
		// 3.125 stars, 5/8 score, 62.5% (.625)
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	
		/*
		 * Entry 2 anonymous
		 * 5 stars, 8/8 score and 100%
		*/
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'pending', // <<<<<<<<<<<<<<<<<<
				'user_id' => 0, // anonymous
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 5
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
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		
		// 3.125 stars, 5/8 score, 62.5% (.625) i.e. not changed given a new entry is not approved
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result for user roles filter where 1 entry is anonymous and the other is
	 * an administrator.
	 * 
	 * @group func
	 */
	public function test_rating_result5() {
	
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
	
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
	
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
	
		/*
		 * Entry 1
		 * 3.125 stars, 5/8 score, 62.5% (0.625)
		*/
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved', // <<<<<<<<<<<<<<<<
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
	
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 
				'post_id' => $post_id, 
				'rating_form_id' => $rating_form_id,
				'user_roles' => 'administrator'
		) );
	
		// 3.125 stars, 5/8 score, 62.5% (.625)
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	
		/*
		 * Entry 2 anonymous
		 * 5 stars, 8/8 score and 100%
		*/
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => 'approved',
				'user_id' => 0, // anonymous <<<<<<<<<<<<<<<<<<
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 5
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
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'user_roles' => 'administrator'
		) );
	
		// 3.125 stars, 5/8 score, 62.5% (.625) i.e. not changed given a new entry is not by an administrator
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result for 10 entries filters. Filters applicable are entry_status, 
	 * approved_comments_only, rating_item_ids, user_roles, rating_entry_ids, user_id, 
	 * comments_only, from_date and to_date
	 * 
	 * @group funcza
	 */
	public function test_rating_result6() {
	
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
	
		$user_id1 = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user_id2 = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user_id3 = $this->factory->user->create( array( 'role' => 'subscribor' ));
		$user_id4 = $this->factory->user->create( array( 'role' => 'subscribor' ) );
		$user_id5 = $this->factory->user->create( array( 'role' => 'subscribor' ) );
		$user_id6 = $this->factory->user->create( array( 'role' => 'subscribor' ) );
		
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
		
		$comment_id1 = $this->factory->comment->create( array( 'user_id' => $user_id1, 'comment_post_ID' => $post_id, 'comment_approved' => '1' ) );
		$comment_id2 = $this->factory->comment->create( array( 'user_id' => $user_id3, 'comment_post_ID' => $post_id, 'comment_approved' => '0' ) );
		$comment_id3 = $this->factory->comment->create( array( 'user_id' => $user_id4, 'comment_post_ID' => $post_id, 'comment_approved' => '1' ) );
				
		$rating_entries = array( 
				// rating item value, entry date, user id, comment id, add comment, entry status,
				array( 	2, '2015/01/01 00:00:00', $user_id1, $comment_id1, false, true ), 	// 0.4     c
				array( 	3, '2015/02/01 00:00:00', $user_id2, null, false, true ), 			// 0.6     
				array( 	3, '2015/03/01 00:00:00', $user_id3, $comment_id2, false, false ), 	// 0.6  x  
				array( 	1, '2015/04/01 00:00:00', $user_id4, $comment_id3, false, true ), 	// 0.2     c
				array( 	4, '2015/05/01 00:00:00', $user_id5, null, false, true ), 			// 0.8
				array( 	5, '2015/06/01 00:00:00', null, null, false, true ), 				// 1
				array( 	5, '2015/07/01 00:00:00', null, null, true, true ), 				// 1       c
				array( 	4, '2015/08/01 00:00:00', null, null, true, false ), 				// 0.8  x
				array( 	2, '2016/01/01 00:00:00', null, null, false, true ), 				// 0.4
				array( 	1, '2016/02/01 00:00:00', null, null, false, false ) 				// 0.2  x
		); 
		
		$rating_entry_ids = array();
		
		foreach ( $rating_entries as $rating_entry ) {
			
			$data = array(
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id,
					'entry_date' => $rating_entry[1]
			);
			
			$data_format = array( '%d', '%d', '%s' );
			
			if ( is_numeric( $rating_entry[2] ) ) {
				$data['user_id'] = $rating_entry[2];
				array_push( $data_format, '%d' );
			}
			if ( is_numeric( $rating_entry[3] ) ) {
				$data['comment_id'] = $rating_entry[3];
				array_push( $data_format, '%d' );
			}
			if ( $rating_entry[4] == true ) {
				$data['comment'] = 'Test comment';
				array_push( $data_format, '%s' );
			}
			if ( $rating_entry[5] == true ) {
				$data['entry_status'] = 'approved';
				array_push( $data_format, '%s' );
			} else {
				$data['entry_status'] = 'pending';
				array_push( $data_format, '%s' );
			}
			
			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, $data, $data_format );
				
			$rating_entry_id = $wpdb->insert_id;
			
			array_push( $rating_entry_ids, $rating_entry_id );
				
			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
					'rating_item_entry_id' => $rating_entry_id,
					'rating_item_id' => $rating_item_id,
					'value' => $rating_entry[0]
			), array( '%d', '%d', '%d' ) );
		}
	
		// 7 entries. 4.4 / 7 = 62.86%, 22/35 * 5 = 3.14 
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id
		) );
	
		$this->assertEquals( 7, $rating_result['count_entries'] );
		$this->assertEquals( 3.14, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 3.14, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 62.86, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
		// 2 entries 100% and 5 stars
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'from_date' => '2015/06/01 00:00:00',
				'to_date' => '2015/12/31 00:00:00'
		) );
		
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 5, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 100, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
		// 2 entries 100% and 5 stars
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'from_date' => '2015/06/01 00:00:00',
				'to_date' => '2015/12/31 00:00:00'
		) );
		
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 5, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 100, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
		// 10 entries 3.3, 66% (0.66), 3.3 stars
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_status' => '', // all
				'approved_comments_only' => false
		) );
		
		$this->assertEquals( 10, $rating_result['count_entries'] );
		$this->assertEquals( 3, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 3, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 60, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
		// 3 entries, 1.6 > 8 / 15 = .53333 53.33% or 2.67 stars
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'comments_only' => true
		) );
		
		$this->assertEquals( 3, $rating_result['count_entries'] );
		$this->assertEquals( 2.67, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 2.67, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 53.33, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
		// 2 entries, .5 > 50% or 2.5 stars
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'rating_entry_ids' => $rating_entry_ids[0] . ',' . $rating_entry_ids[1] . ',' . $rating_entry_ids[2]
		) );
		
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 2.5, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 2.5, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 50, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	/**
	 * Tests rating result list
	 * 
	 * @group func2
	 */
	public function test_rating_result_list1() {
		
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
		
		$post_ids = $this->factory->post->create_many( 5 );
		
		$user_id1 = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user_id2 = $this->factory->user->create( array( 'role' => 'subscribor' ));
		
		$comment_id1 = $this->factory->comment->create( array( 'user_id' => $user_id1, 'comment_post_ID' => $post_ids[1], 'comment_approved' => '1' ) );
		$comment_id2 = $this->factory->comment->create( array( 'user_id' => $user_id2, 'comment_post_ID' => $post_ids[1], 'comment_approved' => '1' ) );
		$comment_id3 = $this->factory->comment->create( array( 'user_id' => $user_id1, 'comment_post_ID' => $post_ids[2], 'comment_approved' => '1' ) );
		$comment_id4 = $this->factory->comment->create( array( 'comment_post_ID' => $post_ids[2], 'comment_approved' => '0' ) );
		
				
		$post_ratings = array(
				// post_ids[0] 5/5
				array( 
						array( 5, '2015/01/01 00:00:00', $user_id1, null ),
						array( 5, '2015/02/01 00:00:00', $user_id2, null ),
						array( 5, '2015/03/01 00:00:00', null, null ),
				),
				// post_ids[1] 6/20 = 1.5/5
				array( 
						array( 1, '2015/01/01 00:00:00', $user_id1, $comment_id1 ),
						array( 2, '2015/02/01 00:00:00', $user_id2, $comment_id2 ),
						array( 1, '2015/03/01 00:00:00', null, null ),
						array( 2, '2015/04/01 00:00:00', null, null ),
				),
				// post_ids[2] 5/15 = 1.66/5
				array(
						array( 3, '2015/01/01 00:00:00', $user_id1, $comment_id3 ),
						array( 1, '2015/02/01 00:00:00', null, $comment_id4 ),
						array( 1, '2015/03/01 00:00:00', null, null ),
				),
				// post_ids[3] 9/10 = 4.5/5
				array(
						array( 5, '2015/01/01 00:00:00', null, null ),
						array( 4, '2015/02/01 00:00:00', null, null ),
				),
				// post_ids[4] 5/5
				array(
						array( 5, '2015/01/01 00:00:00', $user_id2, null ),
				)
		);
		
		$rating_entry_ids = array();
		
		$index = 0;
		foreach ( $post_ids as $post_id ) {
			
			$post_ratings_data = $post_ratings[$index];
			
			foreach ( $post_ratings_data as $post_ratings_data ) {
				
				$data = array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_date' => $post_ratings_data[1]
				);
					
				$data_format = array( '%d', '%d', '%s' );
					
				if ( is_numeric( $post_ratings_data[2] ) ) {
					$data['user_id'] = $post_ratings_data[2];
					array_push( $data_format, '%d' );
				}
				
				if ( is_numeric( $post_ratings_data[3] ) ) {
					$data['comment_id'] = $post_ratings_data[3];
					array_push( $data_format, '%d' );
				}
					
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, $data, $data_format );
			
				$rating_entry_id = $wpdb->insert_id;
					
				array_push( $rating_entry_ids, $rating_entry_id );
			
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id,
						'value' => $post_ratings_data[0]
				), array( '%d', '%d', '%d' ) );
			}
			
			$index++;
		}
		
		// highest rated
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id
		) );

		$this->assertEquals( 5, count( $rating_result_list['rating_results'] ) );
		
		// check sort is OK
		$this->assertEquals( true, ( $rating_result_list['rating_results'][0]['adjusted_star_result'] >= $rating_result_list['rating_results'][1]['adjusted_star_result'] ) );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][1]['adjusted_star_result'] >= $rating_result_list['rating_results'][2]['adjusted_star_result'] ) );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][2]['adjusted_star_result'] >= $rating_result_list['rating_results'][3]['adjusted_star_result'] ) );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][3]['adjusted_star_result'] >= $rating_result_list['rating_results'][4]['adjusted_star_result'] ) );
		
		// highest rated: 0, 4, 3, 2, 1
		$this->assertEquals( $post_ids[0], ( $rating_result_list['rating_results'][0]['post_id'] ) );
		$this->assertEquals( $post_ids[4], ( $rating_result_list['rating_results'][1]['post_id'] ) );
		$this->assertEquals( $post_ids[3], ( $rating_result_list['rating_results'][2]['post_id'] ) );
		$this->assertEquals( $post_ids[2], ( $rating_result_list['rating_results'][3]['post_id'] ) );
		$this->assertEquals( $post_ids[1], ( $rating_result_list['rating_results'][4]['post_id'] ) );
		
		// most entries
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'sort_by' => 'most_entries'
		) );
		
		// check sort is OK
		// most entries: 1=4, 0=3, 2/3=2, 4=1
		$this->assertEquals( 4, ( $rating_result_list['rating_results'][0]['count_entries'] ) );
		$this->assertEquals( 3, ( $rating_result_list['rating_results'][1]['count_entries'] ) );
		$this->assertEquals( 2, ( $rating_result_list['rating_results'][2]['count_entries'] ) ); // one comment is not approved so it's 2 instead of 3
		$this->assertEquals( 2, ( $rating_result_list['rating_results'][3]['count_entries'] ) );
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][4]['count_entries'] ) );
		
		// most entries
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'from_date' => '2015/03/01 00:00:00',
				'to_date' => '2015/06/01 00:00:00'
		) );
		
		// only 3 posts with entries between from and to dates, check count entries
		$this->assertEquals( 3, count( $rating_result_list['rating_results'] ) );
		
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][0]['count_entries'] ) );
		$this->assertEquals( 2, ( $rating_result_list['rating_results'][1]['count_entries'] ) );
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][2]['count_entries'] ) );
		
		// most entries
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'user_roles' => 'administrator,subscribor',
				'sort_by' => 'most_entries'
		) );
		
		// only 4 posts with user roles, check counts
		$this->assertEquals( 4, count( $rating_result_list['rating_results'] ) );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][0]['count_entries'] ) > 0 );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][1]['count_entries'] ) > 0 );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][2]['count_entries'] ) > 0 );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][3]['count_entries'] ) > 0 );
		
		
		// count comments
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'comments_only' => true,
				'approved_comments_only' => false,
				'sort_by' => 'most_entries'
		) );
		
		// only 2 posts with comments
		$this->assertEquals( 2, count( $rating_result_list['rating_results'] ) );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][0]['count_entries'] ) > 0 );
		$this->assertEquals( true, ( $rating_result_list['rating_results'][1]['count_entries'] ) > 0 );
		
		/*
		 * Add two new entries
		 */
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id
		), array( '%d', '%d', '%s' ) );
			
		$rating_entry_id = $wpdb->insert_id;
			
		array_push( $rating_entry_ids, $rating_entry_id );
			
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id
		), array( '%d', '%d', '%s' ) );
			
		$rating_entry_id = $wpdb->insert_id;
			
		array_push( $rating_entry_ids, $rating_entry_id );
			
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 1
		), array( '%d', '%d', '%d' ) );
		
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_form_id' => $rating_form_id ) );
		
		/*
		 * Check if rating result list gets updated
		 */
		
		// most entries
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'sort_by' => 'most_entries'
		) );
		
		// check sort is OK
		// most entries: 0=5, 1=4, 2=3, 3=2, 4=1
		$this->assertEquals( 5, ( $rating_result_list['rating_results'][0]['count_entries'] ) );
		$this->assertEquals( 4, ( $rating_result_list['rating_results'][1]['count_entries'] ) );
		$this->assertEquals( 2, ( $rating_result_list['rating_results'][2]['count_entries'] ) );
		$this->assertEquals( 2, ( $rating_result_list['rating_results'][3]['count_entries'] ) );
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][4]['count_entries'] ) );
		
		// rating entry ids
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'sort_by' => 'most_entries',
				'rating_entry_ids' => $rating_entry_ids[0] . ',' . $rating_entry_ids[3] . ',' . $rating_entry_ids[7]
		) );
		
		// pick 1 entry from 3 posts
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][0]['count_entries'] ) );
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][1]['count_entries'] ) );
		$this->assertEquals( 1, ( $rating_result_list['rating_results'][2]['count_entries'] ) );
	}
	
	/**
	 * Tests rating result with bayesian average ratings
	 *
	 * @group func1a
	 */
	function test_bayesian_average_rating1() {
		
		global $wpdb;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 1',
				'max_option_value' => 5
		) );
			
		$rating_item_id1 = $wpdb->insert_id;
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$post_ids =  $this->factory->post->create_many( 2 );
		
		
		add_filter( 'mrp_rating_result_query', 'mrp_bayesian_rating_result_query', 10, 2 );
		add_filter( 'mrp_rating_results_query_select', 'mrp_bayesian_rating_results_query_select', 10, 2 );
		add_filter( 'mrp_rating_results_query_from', 'mrp_bayesian_rating_results_query_from', 10, 2 );
			
		/*
		 * First post has 3 ratings: 4, 4 and 5
		 */
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id
				), array( '%d', '%d' ) );
		
		$rating_entry_id1 = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id1,
				'rating_item_id' => $rating_item_id1,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id
		), array( '%d', '%d' ) );
		
		$rating_entry_id1 = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id1,
				'rating_item_id' => $rating_item_id1,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[0],
				'rating_form_id' => $rating_form_id
		), array( '%d', '%d' ) );
		
		$rating_entry_id1 = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id1,
				'rating_item_id' => $rating_item_id1,
				'value' => 5
		), array( '%d', '%d', '%d' ) );
		
		/*
		 * Second post has 1 rating: 5
		 */
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_ids[1],
				'rating_form_id' => $rating_form_id
		), array( '%d', '%d' ) );
		
		$rating_entry_id1 = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id1,
				'rating_item_id' => $rating_item_id1,
				'value' => 5
		), array( '%d', '%d', '%d' ) );
		
		// lets ensure rating results have already been calculated, otherwise it does not work the first time
		MRP_Multi_Rating_API::get_rating_result_list( array( 'rating_form_id' => $rating_form_id ) );
		
		/*
		 * Bayesian rating
		 * avg_num_votes = ( 3 + 1 ) / 2 = 2
		 * avg_rating = 4.33333... + 5 / 2 = 4.66666...
		 * 
		 * ( ( avg_num_votes * avg_rating ) + ( this_num_votes * this_rating ) ) / ( avg_num_votes + this_num_votes )
		 * First post: ( ( 2 * 4.66666 ) + ( 3 * 4.33333 ) ) / ( 2 + 3 ) = ( 9.33333 + 12.99999 ) / 5 = 4.46
		 * Second post: ( ( 2 * 4.66666 ) + ( 1 * 5 ) / ( 2 + 1 ) = ( 9.33333 + 5 ) / 3 = 4.78
		 */
	
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_ids[0], 'rating_form_id' => $rating_form_id ) );
		
		$this->assertEquals( 3, $rating_result['count_entries'] );
		$this->assertEquals( 4.46, $rating_result['adjusted_star_result'] );
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_ids[1], 'rating_form_id' => $rating_form_id ) );
		
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4.78, $rating_result['adjusted_star_result'] );
		
	}
	
	/**
	 * Tests rating result for a multiple entries across multiple rating forms
	 *
	 * @group func33d
	 */
	function test_rating_result7() {
	
		global $wpdb;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
				'max_option_value' => 5
		), array( '%s', '%d' ) );
			
		$rating_item_id = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 2',
				'max_option_value' => 5
		), array( '%s', '%d' ) );
			
		$rating_item_id2 = $wpdb->insert_id;
	
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id1 = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME
				. ' WHERE rating_form_id = %d', $rating_form_id1 ), ARRAY_A );
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id1
		) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
				'name' => 'Rating form 2'
		), array( '%s' ) );
		
		$rating_form_id2 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
	
		$user_id = $this->factory->user->create();
	
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id1,
				'entry_status' => 'approved',
				'user_id' => $user_id,
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id,
				'value' => 4
		), array( '%d', '%d', '%d' ) );
		
		// add a rating entry to second rating form
		
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
				'value' => 1
		), array( '%d', '%d', '%d' ) );
		
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id1 );
		$this->assertEquals( 1, count( $rating_form['rating_items'] ) );
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id1 ) );
		
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id1, $rating_result['rating_form_id'] );
		
		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id ) );
		
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id1 ) );
		
		$this->assertEquals( 1, $rating_result_list['rating_results'][0]['count_entries'] );
		$this->assertEquals( 4, $rating_result_list['rating_results'][0]['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result_list['rating_results'][0]['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result_list['rating_results'][0]['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result_list['rating_results'][0]['adjusted_percentage_result'] );
		
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id2 ) );
	}
	
	function test_rating_result_attachment() {

		$post_id = $this->factory->post->create();
		$attachment_id = $this->factory->attachment->create_object( 'test.png', $post_id, array(
				'post_mime_type' => 'image/jpeg',
				'post_type' => 'attachment'
		) );
		
		$post_id2 = $this->factory->post->create(); // not used
		$attachment_id = $this->factory->attachment->create_object( 'test2.png', $post_id2, array(
				'post_mime_type' => 'image/jpeg',
				'post_type' => 'attachment'
		) );
		
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
		
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
		
	}
	
	
	/**
	 * Tests rating result for a single entry with different rating item weights
	 *
	 * @group func91a
	 */
	function test_rating_result_weight1() {
	
		global $wpdb;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
				'max_option_value' => 5
		) );
			
		$rating_item_id1 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
				'max_option_value' => 5
		) );
			
		$rating_item_id2 = $wpdb->insert_id;
	
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id,
				'weight' => 1.5 // <<<<<<<<<<<<< 50% more
		) );
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id,
				'weight' => 0.5
		) );
	
		$user_id = $this->factory->user->create();
	
		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );
	
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
				'value' => 2 // 2 * 
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 5 
		), array( '%d', '%d', '%d' ) );
		
		// so ( 2 * 1.5 ) + ( 5 * 0.5 ) = 3 + 2.5 = 5.5/10 score and 2.75/5 stars
	
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
	
		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 2.75, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 5.5, $rating_result['adjusted_score_result'] ); // out of 10
		$this->assertEquals( 10, $rating_result['total_max_option_value'] );
		$this->assertEquals( 55, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );
	}
	
	
	
	/**
	 * Tests rating result for a multiple entries across multiple rating forms
	 *
	 * @group rating-filters
	 */
	function test_rating_result8_filters() {
	
		global $wpdb;
	
		// create rating items
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
				'max_option_value' => 5
		), array( '%s', '%d' ) );
			
		$rating_item_id1 = $wpdb->insert_id;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 2',
				'max_option_value' => 3
		), array( '%s', '%d' ) );
		
		$rating_item_id2 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 3',
				'max_option_value' => 2
		), array( '%s', '%d' ) );
			
		$rating_item_id3 = $wpdb->insert_id;
	
		// rating form 1 setup
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id1 = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		global $wpdb;
		$results = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id1
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id1
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id3,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id1
		) );
	
		// rating form 2 setup
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
				'name' => 'Rating form 2'
		), array( '%s' ) );
	
		$rating_form_id2 = $wpdb->insert_id;
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
		
		// rating form 3 setup
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
				'name' => 'Rating form 3'
		), array( '%s' ) );
		
		$rating_form_id3 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id3
		) );
		
		$post_id1 = $this->factory->post->create( array( 'post_title' => 'Post 1' ) );
	
		/*
		 * So now we have 3 rating items and 3 rating forms
		 * 
		 * Rating form 1 has rating items 1, 2 and 3
		 * Rating form 2 has rating items 1 and 2
		 * Rating form 3 has rating items 1 only
		 * 
		 * Now lets add some rating entries
		 * 
		 * Entry	Post	Rating Form		1		2		3		Overall Score
		 * 1		1		1				5		3		2		10/10
		 * 2		1		1				2		2		2		6/10
		 * 3		1		2				2		2		N/A		4/8
		 * 4		1		3				2		N/A		N/A		2/5	
		 * 
		 * See here http://multiratingpro.com/filter-combine-ratings/
		 */
		
		// Entry 1
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id1,
				'rating_form_id' => $rating_form_id1,
				'entry_status' => 'approved',
				'user_id' => 0,
		), array( '%d', '%d', '%s', '%d' ) );
	
		$rating_entry_id = $wpdb->insert_id;
	
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 5
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 3
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id3,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
	
		
		// Entry 2
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id1,
				'rating_form_id' => $rating_form_id1,
				'entry_status' => 'approved',
				'user_id' => 0,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id3,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
		
		// Entry 3
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id1,
				'rating_form_id' => $rating_form_id2,
				'entry_status' => 'approved',
				'user_id' => 0,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
		
		// Entry 4
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
				'post_id' => $post_id1,
				'rating_form_id' => $rating_form_id3,
				'entry_status' => 'approved',
				'user_id' => 0,
		), array( '%d', '%d', '%s', '%d' ) );
		
		$rating_entry_id = $wpdb->insert_id;
		
		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id1,
				'value' => 2
		), array( '%d', '%d', '%d' ) );
		
	
		// filter by rating item ids 1 and 2 (post 1 only)
		// ===============================================
		// 8/8 + 4/8 = 12/16 = 6/8, 3.75/5 and 75% 
		$rating_item_ids = $rating_item_id1 . ', ' . $rating_item_id2;
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id1, 'rating_form_id' => $rating_form_id1, 'rating_item_ids' => $rating_item_ids ) );
	
		$this->assertEquals( 2, $rating_result['count_entries'] );
		$this->assertEquals( 3.75, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 6, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 8, $rating_result['total_max_option_value'] );
		$this->assertEquals( 75, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id1, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id1, $rating_result['rating_form_id'] );
		
		// filter by all rating forms (post 1 only)
		// =======================================
		
		// so entries are 10/10, 6/10, 4/8, 2/5. then adjust to /10 = 10/10, 6/10, 5/10 and 4/10
		// average score is ( 10 + 6 + 5 + 4) / 4 = 24 / 4 = 6.25
		// average star is ( 5 + 3 + 2.5 + 2 ) / 4= 13.5 / 4 = 3.125
		// average percentage is 1 + .6 + .5 + .4 = ( 2.5 / 4 ) * 100 = 62.5
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id1, 'rating_form_id' => '' ) );
		$this->assertEquals( 62.5, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( 3.13, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['count_entries'] );
		$this->assertEquals( 6.25, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 10, $rating_result['total_max_option_value'] );
		
		// now filter by all rating forms and rating item 2 (post 1 only)
		// ==============================================================
		// so entries are 3/3, 2/3, 2/3 and last post does not have rating item 2
		// average score is ( 3 + 2 + 2 ) / 4 = 7 / 3 = 2.33
		// average star is ( 5 + 3.33... + 3.33... ) / 4 = 11.66... / 4 = 3.89
		// average percentage is 1 + .66.. + .66... = ( 2.33.. / 4 ) * 100 = 77.78
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id1, 'rating_form_id' => '', 'rating_item_ids' => $rating_item_id2 ) );
		$this->assertEquals( 77.78, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( 3.89, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 3, $rating_result['count_entries'] ); // since one does not have rating item id 2...
		$this->assertEquals( 2.33, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 3, $rating_result['total_max_option_value'] );
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items();
		
		// lets check rating item results for rating item 1 across all rating forms and posts
		// 5/5, 2/5, 2/5 and 2/5 = 11/20 = 55% and 2.75/5
		$rating_result = MRP_Multi_Rating_API::get_rating_item_result( array( 'rating_item' => $rating_items[$rating_item_id1] ) );
		
		$this->assertEquals( 55, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( 2.75, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['count_entries'] );
		$this->assertEquals( 2.75, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 0, $rating_result['option_totals'][0] );
		$this->assertEquals( 0, $rating_result['option_totals'][1] );
		$this->assertEquals( 3, $rating_result['option_totals'][2] );
		$this->assertEquals( 0, $rating_result['option_totals'][3] );
		$this->assertEquals( 0, $rating_result['option_totals'][4] );
		$this->assertEquals( 1, $rating_result['option_totals'][5] );
		
		// lets check rating item results for rating item 2 across all rating forms and posts
		// note one of the entries does not have rating item 2...
		// 3/3, 2/3, 2/3 and N/A = 7/9 = 77.78% and 3.89/5
		$rating_result = MRP_Multi_Rating_API::get_rating_item_result( array( 'rating_item' => $rating_items[$rating_item_id2] ) );
		
		$this->assertEquals( 77.78, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( 3.89, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 3, $rating_result['count_entries'] );
		$this->assertEquals( 2.33, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 3, $rating_result['total_max_option_value'] );
		$this->assertEquals( 0, $rating_result['option_totals'][0] );
		$this->assertEquals( 0, $rating_result['option_totals'][1] );
		$this->assertEquals( 2, $rating_result['option_totals'][2] );
		$this->assertEquals( 1, $rating_result['option_totals'][3] );
		$this->assertEquals( false, isset( $rating_result['option_totals'][4] ) );
		$this->assertEquals( false, isset( $rating_result['option_totals'][5] ) );
	}
	
	public function setUp() {
	
		parent::setUp();
		
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


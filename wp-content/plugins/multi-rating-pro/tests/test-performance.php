<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for performance.
 * 
 * @author dpowney
 *
 */
class MRP_Performance_Test extends WP_UnitTestCase {
	
	/**
	 * Tests rating result for two rating items in a rating form. Checks for 100 posts with up to 20 
	 * entries per post.
	 * 
	 * @group perf
	 */
	public function test_performance1() {
		
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
				
		//$this->factory->post->create_many( 15000 );
		$post_ids =  $this->factory->post->create_many( 100 );
		
		foreach ( $post_ids as $post_id ) {
			
			$index = 0;
			for ( $index; $index < 20; $index++ ) {
				$user_id = 
			
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_status' => 'approved'
				), array( '%d', '%d', '%s' ) );
				
				$rating_entry_id = $wpdb->insert_id;
				
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id1,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
				
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id2,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
			}
		}
		
		microtime();
		
		$start = microtime(true);
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array( 
				'rating_form_id' => $rating_form_id,
				'limit' => 10
		 ) );
		$end = microtime(true);
		
		echo PHP_EOL . 'Time 1a start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
		$this->assertEquals( 10, count( $rating_result_list['rating_results'] ) );
		
		$start = microtime(true);
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10
		) );
		$end = microtime(true);
		$this->assertEquals( 10, count( $rating_result_list['rating_results'] ) );
		
		echo PHP_EOL . 'Time 1b start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
		
		MRP_Multi_Rating_API::delete_calculated_ratings( array(
				'rating_form_id' => $rating_form_id 	
		) );
		
		add_filter( 'mrp_rating_result_query', 'mrp_bayesian_rating_result_query', 10, 2 );
		add_filter( 'mrp_rating_results_query_select', 'mrp_bayesian_rating_results_query_select', 10, 2 );
		add_filter( 'mrp_rating_results_query_from', 'mrp_bayesian_rating_results_query_from', 10, 2 );
		
		$start = microtime(true);
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10
		) );
		$end = microtime(true);
		
		echo PHP_EOL . 'Time 2a start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
		$this->assertEquals( 10, count( $rating_result_list['rating_results'] ) );
		
		$start = microtime(true);
		$rating_result_list = MRP_Multi_Rating_API::get_rating_result_list( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10
		) );
		$end = microtime(true);
		$this->assertEquals( 10, count( $rating_result_list['rating_results'] ) );
		
		echo PHP_EOL . 'Time 2b start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
	}	
	
	/**
	 * Tests rating entry results. Checks for 100 posts with up to 20
	 * entries per post.
	 * 
	 * @group perf
	 */
	public function test_performance3() {
	
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
	
		//$this->factory->post->create_many( 15000 );
		$post_ids =  $this->factory->post->create_many( 100 );
	
		foreach ( $post_ids as $post_id ) {
				
			$index = 0;
			for ( $index; $index < 20; $index++ ) {
				$user_id =
					
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_status' => 'approved'
				), array( '%d', '%d', '%s' ) );
	
				$rating_entry_id = $wpdb->insert_id;
	
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id1,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
	
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id2,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
			}
		}
		
		microtime();
	
		$start = microtime(true);
		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10,
				'post_id' => $post_ids[0]
		) );
		$end = microtime(true);
	
		echo PHP_EOL . 'Time 3a start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
		$this->assertEquals( 10, count( $rating_results_list['rating_results'] ) );
	
		$start = microtime(true);
		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10,
				'post_id' => $post_ids[0]
		) );
		$end = microtime(true);
		$this->assertEquals( 10, count( $rating_results_list['rating_results'] ) );
	
		echo PHP_EOL . 'Time 3b start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
	}
	
	/**
	 * Tests rating item result for two rating items in a rating form. Checks for 100 posts with up to 20
	 * entries per post.
	 * 
	 * @group perf
	 */
	public function test_performance4() {
	
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
	
		//$this->factory->post->create_many( 15000 );
		$post_ids =  $this->factory->post->create_many( 100 );
	
		foreach ( $post_ids as $post_id ) {
	
			$index = 0;
			for ( $index; $index < 20; $index++ ) {
				$user_id =
					
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_status' => 'approved'
				), array( '%d', '%d', '%s' ) );
	
				$rating_entry_id = $wpdb->insert_id;
	
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id1,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
	
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id2,
						'value' => rand( 1, 5 )
				), array( '%d', '%d', '%d' ) );
			}
		}
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array( 'post_id' => $post_ids[0] ) );
		
		microtime();
		
		$start = microtime(true);
		$rating_item_result = MRP_Multi_Rating_API::get_rating_item_result( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10,
				'rating_item' => $rating_items[$rating_item_id1],
				'post_id' => null // all posts
		) );
		$end = microtime(true);
	
		echo PHP_EOL . 'Time 4a start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
	
		$start = microtime(true);
		$rating_item_result = MRP_Multi_Rating_API::get_rating_item_result( array(
				'rating_form_id' => $rating_form_id,
				'limit' => 10,
				'rating_item' => $rating_items[$rating_item_id1],
				'post_id' => null // all posts
		) );
		$end = microtime(true);
	
		echo PHP_EOL . 'Time 4b start ' . $start . ' - end ' . $end . ': ' . floatval( $end - $start ) . PHP_EOL;
	
		//$this->assertEquals( true, ( $end - $start ) < 5000 );
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


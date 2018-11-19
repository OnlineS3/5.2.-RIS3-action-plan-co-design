<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for rating items
 * @author dpowney
 *
 */
class MRP_Rating_Item_Test extends WP_UnitTestCase {
	
	/**
	 * Simple test for getting a rating item and checking correct values are returned
	 * 
	 * @group func
	 */
	function test_get_rating_items() {
		
		global $wpdb;
		
		$description = 'Hello world';
		$max_option_value = 5;
		$default_option_value = 5;
		$option_value_text = '0=Test';
		$type = 'star_rating';
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => $description,
				'max_option_value' => $max_option_value,
				'default_option_value' => $default_option_value,
				'option_value_text' => $option_value_text,
				'type' => $type
		) );
			
		$rating_item_id = $wpdb->insert_id;
		
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array( 'rating_item_ids' => $rating_item_id ) ); 

		$this->assertEquals( count( $rating_items ), 1 );
		
		$rating_item = $rating_items[$rating_item_id];
		
		$this->assertEquals( $rating_item_id, $rating_item['rating_item_id'] );
		$this->assertEquals( $description, $rating_item['description'] );
		$this->assertEquals( $max_option_value, $rating_item['max_option_value'] );
		$this->assertEquals( $default_option_value, $rating_item['default_option_value'] );
		$this->assertEquals( $option_value_text, $rating_item['option_value_text'] );
		$this->assertEquals( $type, $rating_item['type'] );
	}
	
	/**
	 * Tests getting two rating item for a rating form but three rating items exist in db
	 * 
	 * @group func95
	 */
	public function test_get_rating_items2() {
		
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
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 3',
				'max_option_value' => 5
		) );
			
		$rating_item_id3 = $wpdb->insert_id;
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id3,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id
		) );
		
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		$this->assertEquals( 2, count( $rating_form['rating_items'] ) );
	}
	
	/**
	 * Tests getting two rating item for a rating form but three rating items exist in db and two rating forms
	 *
	 * @group func
	 */
	public function test_get_rating_items3() {
	
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
	
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing 3',
				'max_option_value' => 5
		) );
			
		$rating_item_id3 = $wpdb->insert_id;
	
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id1 = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
	
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
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME, array(
				'name' => 'My Rating Form'
		), array( '%s', '%s' ) );
		
		$rating_form_id2 = $wpdb->insert_id;
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id1,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
		
		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
				'item_type' => 'rating-item',
				'rating_form_id' => $rating_form_id2
		) );
		
		$rating_form1 = MRP_Multi_Rating_API::get_rating_form( $rating_form_id1 );

		$this->assertEquals( 2, count( $rating_form1['rating_items'] ) );
		
		$rating_form2 = MRP_Multi_Rating_API::get_rating_form( $rating_form_id2 );
		
		$this->assertEquals( 2, count( $rating_form2['rating_items'] ) );
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

	
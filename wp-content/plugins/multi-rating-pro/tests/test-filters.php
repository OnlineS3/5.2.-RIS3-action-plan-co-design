<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for filters.
*
* @author dpowney
*
*/
class MRP_Filters_Test extends WP_UnitTestCase {

	/**
	 * Tests for no filters with the plugin settings and also post meta table
	 *
	 * @group func89
	 */
	function test_no_filters() {

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];

		$post_id = $this->factory->post->create( array( 'post_title' => 'Test Post' ) );

		add_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, '' );

		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );

		$this->assertEquals( $rating_form_id, $current_rating_form_id );

		$rating_form_id = 2; // doesn't matter if it's not created properly

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, $rating_form_id );

		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );

		$this->assertEquals( $rating_form_id, $current_rating_form_id );

	}

	/**
	 * Tests a filter for a category called test overrides the rating form in the settings and post meta
	 *
	 * @group func84
	 */
	function test_filters_taxonomy1() {

		$rating_form_id = 3;

		$taxonomy_name = 'category';
		$term_name = 'test';

		register_taxonomy( $taxonomy_name, 'post', array (
				'public' => false,
				'publicly_queryable' => true
		) );
		$term = $this->factory->term->create_and_get( array(
				'taxonomy' => $taxonomy_name,
				'name' => $term_name
		) );

		$post_id = $this->factory->post->create();
		wp_set_object_terms( $post_id, $term->slug, $taxonomy_name );

		$filters = array();

		array_push( $filters, array(
				'filter_type' => 'taxonomy',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => $taxonomy_name,
				'terms' => array( $term_name ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'before_title',
				'rating_form_id' => $rating_form_id, // <<<<<<<<<<<<<<<<<<<<<
				'priority' => 10,
				'override_post_meta' => false // <<<<<<<<<<<<<<<<,
		) );

		update_option( 'mrp_filters',  $filters );

		// should match filter
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertEquals( $rating_form_id, $current_rating_form_id );

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];

		// rating form should not use the default rating form settings, so it should
		// still be the same as the filer
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertNotEquals( intval( $rating_form_id ), intval( $current_rating_form_id ) );

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, 8 );

		// post meta does not override so it should still be the same as the filer
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertEquals( 3, $current_rating_form_id );

	}


	/**
	 * Tests a filter for a a category called test overrides the rating form in the settings and post meta
	 *
	 * @group func89
	 */
	function test_filters_taxonomy2() {

		$rating_form_id = 3;

		$taxonomy_name = 'category';
		$term_name = 'test';

		register_taxonomy( $taxonomy_name, 'post', array (
				'public' => false,
				'publicly_queryable' => true
		) );
		$term = $this->factory->term->create_and_get( array(
				'taxonomy' => $taxonomy_name,
				'name' => $term_name
		) );

		$post_id = $this->factory->post->create();
		wp_set_object_terms( $post_id, $term->slug, $taxonomy_name );

		$filters = array();

		array_push( $filters, array(
				'filter_type' => 'taxonomy',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => $taxonomy_name,
				'terms' => array( $term_name ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'before_title',
				'rating_form_id' => $rating_form_id, // <<<<<<<<<<<<<<<<<<<<<
				'priority' => 10,
				'override_post_meta' => true // <<<<<<<<<<<<<<<<,
		) );

		update_option( 'mrp_filters',  $filters );

		// should match filter
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertEquals( $rating_form_id, $current_rating_form_id );

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];

		// rating form should not use the default rating form settings, so it should
		// still be the same as the filer
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertNotEquals( $rating_form_id, $current_rating_form_id );

		$rating_form_id = 3;

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, $rating_form_id );

		// post meta overrides filter so it should match
		$current_rating_form_id = MRP_Utils::get_rating_form( $post_id );
		$this->assertEquals( $rating_form_id, $current_rating_form_id );

	}



	/**
	 * Tests auto placement
	 *
	 * Key Combinations
	 * 1. No filters
	 * 2. Filter (override post meta only)
	 * 3. Filter (only override post meta if post meta == '')
	 *
	 * @group func44
	 */
	function test_auto_placement1() {

		$post_id = $this->factory->post->create();

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
		$auto_placement_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION] = 'comment_form';
		update_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, $auto_placement_settings );

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, '' );

		$filters = array();
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content', // <<<<<<<<<<<<<<<<,
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );

		/*
		 * Scenario 1
		 *
		 * Default = comment_form
		 * Post Meta = ''
		 * Filter = before_content (overrride post meta)
		 *
		 * Expected = before_content
		 */

		// should match filter
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'before_content', $rating_form_position );

		$filters = array();
		update_option( 'mrp_filters',  $filters ); // no filters now

		/*
		 * Scenario 2
		 *
		 * Default = comment_form
		 * Post Meta = ''
		 * No filters
		 *
		 * Expected = comment_form
		 */

		// should match auto placement settings since there are no filters and post meta uses default settings
		//$rating_form_position = mrp_get_rating_form_position( $post_id );
		//$this->assertEquals( 'comment_form', $rating_form_position );

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, 'after_content' );

		/*
		 * Scenario 3
		 *
		 * Default = comment_form
		 * Post Meta = after_content
		 * No filters
		 *
		 * Expected = after_content
		 */

		// should match post meta
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'after_content', $rating_form_position );

		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => '', // <<<<<<<<<<<<<<<<,
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );

		/*
		 * Scenario 4
		 *
		 * Default = comment_form
		 * Post Meta = after_content
		 * Filter = '' (override post meta)
		 *
		 * Expected = comment_form
		 */

		// should match auto placement settings since there are no filters and post meta uses default settings
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'comment_form', $rating_form_position );

		// now change filter not to override post meta
		$filters = array();
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'comment_form', // <<<<<<<<<<<<<<<<,
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => false, // <<<<<<<<<<<<<<<<<
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );

		/*
		 * Scenario 5
		 *
		 * Default = comment_form
		 * Post Meta = after_content
		 * Filter = comment_form' (!!!! not overriding post meta)
		 *
		 * Expected = after_content
		 */

		// should be the same, filter does not override post meta
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'after_content', $rating_form_position );

		// now change filter not to override post meta
		$filters = array();
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'after_content', // <<<<<<<<<<<<<<<<,
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => false, // <<<<<<<<<<<<<<<<<
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );

		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, '' );


		/*
		 * Scenario 6
		 *
		 * Default = comment_form
		 * Post Meta = ''
		 * Filter = after_content (!!!! not overriding post meta)
		 *
		 * Expected = after_content - since post meta is empty
		 */

		// now should use filter since post meta says use default
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'after_content', $rating_form_position );

		// now change filter not to override post meta
		$filters = array();
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => '', // <<<<<<<<<<<<<<<<,
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => false, // <<<<<<<<<<<<<<<<<
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );


		/*
		 * Scenario 7
		 *
		 * Default = comment_form
		 * Post Meta = ''
		 * Filter = '' (!!!! not overriding post meta)
		 *
		 * Expected = comment_form
		 */

		// now should use filter since post meta says use default
		$rating_form_position = mrp_get_rating_form_position( $post_id );
		$this->assertEquals( 'comment_form', $rating_form_position );
	}



	/**
	 * Tests auto placement
	 *
	 * Key Combinations
	 * 1. No filters
	 * 2. Filter (override post meta only)
	 * 3. Filter (only override post meta if post meta == '')
	 *
	 * @group func44
	 */
	function test_filter_priority() {

		$post_id = $this->factory->post->create();

		$filters = array();
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 10,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter 2', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 15,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter 3', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 5, // <<<<<<<<<<<<<<<<,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		array_push( $filters, array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter 4', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array( ) ,
				'post_types' => array(),
				'rating_form_position' => 'before_content',
				'rating_results_position' => 'after_title',
				'rating_form_id' => 1,
				'priority' => 20,
				'override_post_meta' => true,
				'post_ids' => array( $post_id )
		) );
		update_option( 'mrp_filters',  $filters );

		$filter = MRP_Utils::get_filter( $post_id );
		$this->assertEquals( 'Sample Filter 3', $filter['filter_name'] );
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

<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Tests for rating entry results
 *
 * @author dpowney
 *
 */
class MRP_Rating_Entry_Result_Test extends WP_UnitTestCase {

	/**
	 * Tests rating entry result.
	 *
	 * @group func127
	 */
	public function test_rating_entry_result1() {

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

		$post_ids = $this->factory->post->create_many( 2 );

		$user_id1 = $this->factory->user->create( array( 'role' => 'administrator', 'display_name' => 'Barry Taylor', 'user_email' => 'barrytaylor@mail.com' ) );
		$user_id2 = $this->factory->user->create( array( 'role' => 'subscribor', 'display_name' => 'Craig Adams', 'user_email' => 'craigadams@mail.com' ) );

		$comment_id1 = $this->factory->comment->create( array( 'user_id' => $user_id1, 'comment_post_ID' => $post_ids[1], 'comment_approved' => '1', 'comment_content' => 'My comment3' ) );
		$comment_id2 = $this->factory->comment->create( array( 'user_id' => $user_id2, 'comment_post_ID' => $post_ids[1], 'comment_approved' => '1', 'comment_content' => 'My comment4' ) );


		// rating item value, entry date, user id, comment id, name, e-mail, comment
		$rating_entries = array(
				array(
						array( 5, '2015-01-01 00:00:00', $user_id1, null, null, null, 'My comment1' ),
						array( 4, '2015-02-15 00:00:00', $user_id2, null, null, null, null ), // no comment
						// anonymous
						array( 3, '2015-03-15 00:00:00', null, null, 'John Smith', 'johnsmith@mail.com', 'My comment2' ),
				),
				array(
						array( 1, '2015-01-01 00:00:00', $user_id1, $comment_id1, null, null, null ),
						array( 2, '2015-02-01 00:00:00', $user_id2, $comment_id2, null, null, null ),
						// anonymous
						array( 1, '2015-03-01 00:00:00', null, null, 'Bob Johnson', 'bobjohnson@mail.com', 'My comment5' ),
						array( 2, '2015-04-01 00:00:00', null, null, 'Shaun James', 'shanjames@mail.com', 'My comment6' ),
				)
		);

		$rating_entry_ids = array();

		$index = 0;
		foreach ( $post_ids as $post_id ) {

			$rating_entries_data = $rating_entries[$index];

			foreach ( $rating_entries_data as $rating_entry_data ) {

				$data = array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'entry_date' => $rating_entry_data[1],
						'entry_status' => 'approved'
				);

				$data_format = array( '%d', '%d', '%s', '%s' );

				if ( is_numeric( $rating_entry_data[2] ) ) {
					$data['user_id'] = $rating_entry_data[2];
					array_push( $data_format, '%d' );
				}

				if ( is_numeric( $rating_entry_data[3] ) ) {
					$data['comment_id'] = $rating_entry_data[3];
					array_push( $data_format, '%d' );
				}

				if ( is_string( $rating_entry_data[4] ) ) {
					$data['name'] = $rating_entry_data[4];
					array_push( $data_format, '%s' );
				}

				if ( is_string( $rating_entry_data[5] ) ) {
					$data['email'] = $rating_entry_data[5];
					array_push( $data_format, '%s' );
				}

				if ( is_string( $rating_entry_data[6] ) ) {
					$data['comment'] = $rating_entry_data[6];
					array_push( $data_format, '%s' );
				}

				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, $data, $data_format );

				$rating_entry_id = $wpdb->insert_id;

				array_push( $rating_entry_ids, $rating_entry_id );

				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
						'rating_item_entry_id' => $rating_entry_id,
						'rating_item_id' => $rating_item_id,
						'value' => $rating_entry_data[0]
				), array( '%d', '%d', '%d' ) );
			}

			$index++;
		}

		$rating_entry_result = MRP_Multi_Rating_API::get_rating_entry_result( array( 'rating_entry_id' => $rating_entry_ids[0] ) );

		$this->assertEquals( false, isset( $rating_entry_result['count_entries'] ) );
		$this->assertEquals( $rating_entry_ids[0], $rating_entry_result['rating_entry_id'] ); // here <<<<<<<<<<<<,
		$this->assertEquals( 5, $rating_entry_result['adjusted_star_result'] );
		$this->assertEquals( 5, $rating_entry_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_entry_result['total_max_option_value'] );
		$this->assertEquals( 100, $rating_entry_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_ids[0], $rating_entry_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_entry_result['rating_form_id'] );

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_ids[0]
		) );

		$this->assertEquals( 3, count( $rating_results_list['rating_results'] ) );
		$this->assertEquals( 3, $rating_results_list['count_entries'] );
		$this->assertEquals( 2, $rating_results_list['count_comments'] );

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_ids[0],
				'comments_only' => true
		) );

		$this->assertEquals( 2, count( $rating_results_list['rating_results'] ) );

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id
		) );

		// 2 posts, 7 entries between them
		$this->assertEquals( 7, count( $rating_results_list['rating_results'] ) );

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'from_date' => '2015-02-01 00:00:00',
				'to_date' => '2015-03-16 00:00:00',
				'sort_by' => 'highest_rated'
		) );

		$this->assertEquals( 4, count( $rating_results_list['rating_results'] ) );
		$this->assertEquals( 4, $rating_results_list['rating_results'][0]['adjusted_star_result'] );
		$this->assertEquals( $rating_entry_ids[1], $rating_results_list['rating_results'][0]['rating_entry_id'] );
		$this->assertEquals( 3, $rating_results_list['rating_results'][1]['adjusted_star_result'] );
		$this->assertEquals( $rating_entry_ids[2], $rating_results_list['rating_results'][1]['rating_entry_id'] );
		$this->assertEquals( 2, $rating_results_list['rating_results'][2]['adjusted_star_result'] );
		$this->assertEquals( $rating_entry_ids[4], $rating_results_list['rating_results'][2]['rating_entry_id'] );
		$this->assertEquals( 1, $rating_results_list['rating_results'][3]['adjusted_star_result'] );
		$this->assertEquals( $rating_entry_ids[5], $rating_results_list['rating_results'][3]['rating_entry_id'] );

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
				'rating_form_id' => $rating_form_id,
				'from_date' => '2015-02-01 00:00:00',
				'to_date' => '2015-03-16 00:00:00',
				'sort_by' => 'most_recent'
		) );

		// 2, 5, 1, 4
		$this->assertEquals( 4, count( $rating_results_list['rating_results'] ) );
		$this->assertEquals( $rating_entry_ids[2], $rating_results_list['rating_results'][0]['rating_entry_id'] );
		$this->assertEquals( $rating_entry_ids[5], $rating_results_list['rating_results'][1]['rating_entry_id'] );
		$this->assertEquals( $rating_entry_ids[1], $rating_results_list['rating_results'][2]['rating_entry_id'] );
		$this->assertEquals( $rating_entry_ids[4], $rating_results_list['rating_results'][3]['rating_entry_id'] );

		// array( 5, '2015-01-01 00:00:00', $user_id1, null, null, null, 'My comment1' ),
		$rating_entry1 = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_ids[0] ) );

		$user1_data = get_userdata( $user_id1 );
		$this->assertEquals( $user1_data->display_name, $rating_entry1['name'] );
		$this->assertEquals( $user1_data->user_email, $rating_entry1['email'] );
		$this->assertEquals( 'My comment1', $rating_entry1['comment'] );
		$this->assertEquals( '2015-01-01 00:00:00', $rating_entry1['entry_date'] );
		$this->assertEquals( 'approved', $rating_entry1['entry_status'] );
		$this->assertEquals( $user_id1, $rating_entry1['user_id'] );
		$this->assertEquals( $post_ids[0], $rating_entry1['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_entry1['rating_form_id'] );
		$this->assertEquals( $rating_entry_ids[0], $rating_entry1['rating_entry_id'] );

		//array( 1, '2015-01-01 00:00:00', $user_id1, $comment_id1, null, null, null ),
		$rating_entry3 = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_ids[3] ) );

		$user1_data = get_userdata( $user_id1 );
		$this->assertEquals( $user1_data->display_name, $rating_entry3['name'] );
		$this->assertEquals( $user1_data->user_email, $rating_entry3['email'] );
		$this->assertEquals( 'My comment3', $rating_entry3['comment'] );
		$this->assertEquals( '2015-01-01 00:00:00', $rating_entry3['entry_date'] );
		$this->assertEquals( 'approved', $rating_entry3['entry_status'] );
		$this->assertEquals( $user_id1, $rating_entry3['user_id'] );
		$this->assertEquals( $post_ids[1], $rating_entry3['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_entry3['rating_form_id'] );
		$this->assertEquals( $rating_entry_ids[3], $rating_entry3['rating_entry_id'] );

		$rating_entries = MRP_Multi_Rating_API::get_rating_entries( array( 'user_id' => $user_id1 ) );
		$this->assertEquals( 2, count( $rating_entries ) );
	}


	/**
	 * Tests rating entry result.
	 *
	 * @group func90
	 */
	public function test_rating_entry_na() {

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
				'rating_form_id' => $rating_form_id
		) );

		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, array(
				'item_id' => $rating_item_id2,
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
				'rating_item_id' => $rating_item_id1,
				'value' => 4
		), array( '%d', '%d', '%d' ) );

		// should be 4/5

		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME, array(
				'rating_item_entry_id' => $rating_entry_id,
				'rating_item_id' => $rating_item_id2,
				'value' => -1 // <--- N/A selected
		), array( '%d', '%d', '%d' ) );

		$rating_entry_result = MRP_Multi_Rating_API::get_rating_entry_result( array( 'rating_entry_id' => $rating_entry_id ) );

		$this->assertEquals( 4, $rating_entry_result['adjusted_star_result'] );
		$this->assertEquals( 8, $rating_entry_result['adjusted_score_result'] );
		$this->assertEquals( 10, $rating_entry_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_entry_result['adjusted_percentage_result'] );

		// lets check
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 8, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 10, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );

	}



/**
	 * Tests user rating exists API function.
	 *
	 * @group user
	 */
	public function test_user_rating_exists() {

		global $wpdb;

		$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME, array(
				'description' => 'Testing',
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
				'value' => 4
		), array( '%d', '%d', '%d' ) );

		// should be 4/5

		$rating_entry_result = MRP_Multi_Rating_API::get_rating_entry_result( array( 'rating_entry_id' => $rating_entry_id ) );

		$this->assertEquals( 4, $rating_entry_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_entry_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_entry_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_entry_result['adjusted_percentage_result'] );

		// lets check
		$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

		$this->assertEquals( 1, $rating_result['count_entries'] );
		$this->assertEquals( 4, $rating_result['adjusted_star_result'] );
		$this->assertEquals( 4, $rating_result['adjusted_score_result'] );
		$this->assertEquals( 5, $rating_result['total_max_option_value'] );
		$this->assertEquals( 80, $rating_result['adjusted_percentage_result'] );
		$this->assertEquals( $post_id, $rating_result['post_id'] );
		$this->assertEquals( $rating_form_id, $rating_result['rating_form_id'] );

		/*
		 * Now the fun starts...
		 */
		$wrong_user_id = $user_id + 1;
		$wrong_rating_form_id = $rating_form_id + 1;
		$wrong_post_id = $post_id + 1;
		$wrong_rating_entry_id = $rating_entry_id + 1;

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'user_id' => $user_id, 'rating_entry_id' => $rating_entry_id ) );
		$this->assertEquals( $rating_entry_id, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'user_id' => $wrong_user_id, 'rating_entry_id' => $rating_entry_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'user_id' => $user_id, 'rating_entry_id' => $wrong_rating_entry_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id, 'user_id' => $user_id ) );
		$this->assertEquals( $rating_entry_id, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'post_id' => $wrong_post_id, 'rating_form_id' => $rating_form_id, 'user_id' => $user_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'post_id' => $post_id, 'rating_form_id' => $wrong_rating_form_id, 'user_id' => $user_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'rating_form_id' => $rating_form_id, 'user_id' => $user_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'post_id' => $post_id, 'rating_form_id' => $wrong_rating_form_id ) );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( array( 'rating_entry_id' => $rating_entry_id ) );
		$this->assertEquals( false, $result );

		// backwards compatibility
		$result = MRP_Multi_Rating_API::user_rating_exists( null, null, $user_id, $rating_entry_id );
		$this->assertEquals( $rating_entry_id, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( null, null, $wrong_user_id, $rating_entry_id );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( $rating_form_id, $post_id, $user_id, null );
		$this->assertEquals( $rating_entry_id, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( $rating_form_id, $post_id, $wrong_user_id, null );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( $wrong_rating_form_id, $post_id, $user_id, null );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( $rating_form_id, $wrong_post_id, $user_id, null );
		$this->assertEquals( false, $result );

		// missing parameters
		$result = MRP_Multi_Rating_API::user_rating_exists( null, $post_id, $user_id, null );
		$this->assertEquals( false, $result );

		$result = MRP_Multi_Rating_API::user_rating_exists( $rating_form_id, $post_id, null, null );
		$this->assertEquals( false, $result );
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


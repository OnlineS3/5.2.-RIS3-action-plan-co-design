<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rating form class
 *
 * @author dpowney
 *
 */
class MRP_Rating_Form {

	/**
	 * Used to uniquely identify a rating form on a page
	 */
	public static $sequence = 0;

	/**
	 * Constructor
	 */
	function __construct() {
	}

	/**
	 * Saves a rating form entry. If rating entry id is present then we're updating an existing rating.
	 */
	public static function save_rating() {

		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {

			// check parameters
			if ( ! isset( $_POST['postId'] ) || ( isset( $_POST['postId'] ) && ! is_numeric( $_POST['postId'] ) )
					|| ! isset( $_POST['ratingFormId'] ) || ( isset($_POST['ratingFormId']) && ! is_numeric($_POST['ratingFormId'] ) ) ) {
				die();
			}

			$sequence = isset( $_POST['sequence'] ) && is_numeric( $_POST['sequence'] ) ? intval( $_POST['sequence'] ) : null;
			$post_id = isset( $_POST['postId'] ) && is_numeric( $_POST['postId'] ) ? intval( $_POST['postId'] ) : null;
			$rating_form_id = isset( $_POST['ratingFormId'] ) && is_numeric( $_POST['ratingFormId'] ) ? intval( $_POST['ratingFormId'] ) : null;
			$rating_entry_id = isset( $_POST['ratingEntryId'] ) && is_numeric( $_POST['ratingEntryId'] ) ? intval( $_POST['ratingEntryId'] ) : null;

			$is_update = ( $rating_entry_id != null );

			// get user id
			global $wp_roles;
			$user = wp_get_current_user();
			$user_id = $user->ID;

			$rating_items = isset( $_POST['ratingItems'] ) ? $_POST['ratingItems'] : array();
			$custom_fields = isset( $_POST['customFields'] ) ? $_POST['customFields'] : array();

			$rating_item_values = array();
			foreach ( $rating_items as $rating_item ) {
				$rating_item_values[$rating_item['id']] = $rating_item['value'];
				if ( isset( $rating_item['isNotApplicable'] ) && $rating_item['isNotApplicable'] == 'true' ) {
					$rating_item_values[$rating_item['id']] = -1;
				}
			}

			$custom_field_values = array();
			foreach ( $custom_fields as $custom_field ) {
				$custom_field_values[$custom_field['id']] = $custom_field['value'];
			}

			$rating_entry = array(
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id,
					'rating_entry_id' => $rating_entry_id,
					'entry_date' => current_time( 'mysql' ),
					'title' => isset( $_POST['title'] ) ? $_POST['title'] : null,
					'name' => isset( $_POST['name'] ) ? $_POST['name'] : null,
					'email' => isset( $_POST['email'] ) ?  $_POST['email'] : null,
					'comment' => isset( $_POST['comment'] ) ? $_POST['comment'] : null,
					'user_id' => $user_id,
					'rating_item_values' => $rating_item_values,
					'custom_field_values' => $custom_field_values
			);

			$save_rating_response = MRP_Multi_Rating_API::save_rating_entry( $rating_entry );

			$validation_results = $save_rating_response['validation_results'];
			$rating_entry = $save_rating_response['rating_entry'];

			$validation_results = apply_filters( 'mrp_after_rating_form_validation_save', $validation_results, $rating_entry );

			if ( MRP_Utils::has_validation_error( $validation_results ) ) {

				$ajax_response = json_encode( array (
						'status' => 'error',
						'data' => array(
								'sequence' => $sequence,
								'rating_form_id' => $rating_form_id,
								'post_id' => $post_id,
								'action' => 'save'
						),
						'validation_results' => $validation_results
				) );

				echo $ajax_response;

				die();
			}

			$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
			$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );
			$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

			$message = $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION];
			if ( $is_update ) {
				$message = $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION];
			}

			ob_start();
			mrp_get_template_part( 'rating-result', null, true, array(
				'no_rating_results_text' => '',
				'generate_microdata' => false,
				'show_title' => false,
				'show_date' => false,
				'show_count' => true,
				'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
				'class' =>  'rating-result-' . $rating_form_id . '-' . $post_id . ' mrp-filter ' . mrp_get_rating_results_position( $post_id ),
				'rating_result' => $rating_result,
				'before_count' => '(',
				'after_count' => ')',
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'ignore_count' => false,
				'preserve_max_option' => false,
				'before_date' => '',
				'after_date' => ''
			) );
			$html = ob_get_contents();
			ob_end_clean();

			$ajax_response = (array) apply_filters( 'mrp_save_rating_response', array(
					'status' => 'success',
					'data' => array(
							'sequence' => $sequence,
							'rating_form_id' => $rating_form_id,
							'post_id' => $post_id,
							'action' => 'save',
							'html' => $html,
							'rating_entry_id' => $rating_entry['rating_entry_id'],
							'rating_result' => $rating_result,
							'hide_rating_form' => ( $user_id == 0 || $advanced_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ),
							'user_id' => $user_id != 0 ? $user_id : null
					),
					'message' => array( $message )
			) );

			echo json_encode( $ajax_response );
		}

		die();
	}

	/**
	 * Deletes a rating form entry
	 */
	public static function delete_rating() {

		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID . '-nonce' ) ) {

			global $wpdb;

			// check parameters
			if ( ! isset( $_POST['postId'] ) || ( isset( $_POST['postId'] ) && ! is_numeric( $_POST['postId'] ) )
					|| ! isset( $_POST['ratingFormId'] ) || ( isset($_POST['ratingFormId']) && ! is_numeric($_POST['ratingFormId'] ) )
					|| ! isset( $_POST['ratingEntryId'] ) || ( isset($_POST['ratingEntryId']) && ! is_numeric($_POST['ratingEntryId'] ) ) ) {
				die();
			}

			$sequence = isset( $_POST['sequence'] ) && is_numeric( $_POST['sequence'] ) ? intval( $_POST['sequence'] ) : null;
			$post_id = isset( $_POST['postId'] ) && is_numeric( $_POST['postId'] ) ?  intval( $_POST['postId'] ) : null;
			$rating_form_id = isset( $_POST['ratingFormId'] ) && is_numeric( $_POST['ratingFormId'] ) ? intval( $_POST['ratingFormId'] ) : null;
			$rating_entry_id = isset( $_POST['ratingEntryId'] ) && is_numeric( $_POST['ratingEntryId'] ) ? intval( $_POST['ratingEntryId'] ) : null;

			// get user id
			global $wp_roles;
			$user = wp_get_current_user();
			$user_id = $user->ID;

			$rating_entry = array(
					'rating_entry_id' => $rating_entry_id,
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id,
					'custom_fields' => $custom_field_values,
					'user_id' => $user_id
			);

			$validation_results = array();

			// check if user belongs to rating entry id
			if ( $user_id == 0 || ( $user_id != 0
					&& ! MRP_Multi_Rating_API::user_rating_exists(
							array( 'rating_form_id' => $rating_form_id, 'post_id' => $post_id, 'user_id' => $user_id, 'rating_entry_id' => $rating_entry_id ) ) ) ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'message' => __( 'You cannot delete a rating you did not submit or the rating may already be deleted.', 'multi-rating-pro' )
				) );
			}

			$validation_results = apply_filters( 'mrp_after_rating_form_validation_delete', $validation_results, $rating_entry );

			if ( MRP_Utils::has_validation_error( $validation_results ) ) {

				$ajax_response = json_encode( array (
						'status' => 'error',
						'data' => array(
								'sequence' => $sequence,
								'rating_form_id' => $rating_form_id,
								'post_id' => $post_id,
								'action' => 'delete'
						),
						'validation_results' => $validation_results
				) );

				echo $ajax_response;

				die();

			}

			MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );

			$rating_result = MRP_Multi_Rating_API::get_rating_result( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

			$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

			ob_start();
			mrp_get_template_part( 'rating-result', null, true, array(
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'generate_microdata' => false,
				'show_title' => false,
				'show_date' => false,
				'show_count' => true,
				'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
				'class' =>  'rating-result-' . $rating_form_id . '-' . $post_id . ' mrp-filter ' . mrp_get_rating_results_position( $post_id ),
				'rating_result' => $rating_result,
				'before_count' => '(',
				'after_count' => ')',
				'post_id' => $post_id,
				'ignore_count' => false,
				'preserve_max_option' => false,
				'before_date' => '',
				'after_date' => ''
			) );
			$html = ob_get_contents();
			ob_end_clean();

			$ajax_response = (array) apply_filters( 'mrp_delete_rating_response', array(
					'status' => 'success',
					'message' => array( $custom_text_settings[ MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION] ),
					'data' => array(
							'sequence' => $sequence,
							'rating_form_id' => $rating_form_id,
							'post_id' => $post_id,
							'action' => 'delete',
							'html' => $html,
							'rating_result' => $rating_result,
							'hide_rating_form' => ( $user_id == 0 || $advanced_settings[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ),
							'user_id' => $user_id != 0 ? $user_id : null
					),
			) );

			echo json_encode( $ajax_response );
		}

		die();
	}

	/**
	 * Returns rating form in a JSON response
	 *
	 * @return Ambigous <void, unknown>
	 */
	public static function get_rating_form() {

		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce($ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {

			// check parameters
			if ( ! isset( $_POST['postId'] ) || ( isset( $_POST['postId'] ) && ! is_numeric( $_POST['postId'] ) )
					|| ! isset( $_POST['ratingFormId'] ) || ( isset($_POST['ratingFormId']) && ! is_numeric($_POST['ratingFormId'] ) ) ) {
				die();
			}

			global $wpdb;

			$sequence = isset( $_POST['sequence'] ) && is_numeric( $_POST['sequence'] ) ? intval( $_POST['sequence'] ) : null;
			$post_id =  isset( $_POST['postId'] ) && is_numeric( $_POST['postId'] ) ? intval( $_POST['postId'] ) : null;
			$rating_form_id = isset( $_POST['ratingFormId'] ) && is_numeric( $_POST['ratingFormId'] ) ? intval( $_POST['ratingFormId'] ) : null;
			$rating_entry_id = isset( $_POST['ratingEntryId'] ) && is_numeric( $_POST['ratingEntryId'] ) ? intval( $_POST['ratingEntryId'] ) : null;

			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

			$html = mrp_rating_form( array(
					'rating_form_id' => $rating_form_id,
					'post_id' => $post_id,
					'title' => '', // so no title is shown
					'submit_button_text' => $custom_text_settings[MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION],
					'update_button_text' => $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
					'delete_button_text' => $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
					'user_can_update_delete' => $general_settings[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING],
					'echo' => false,
					'class' => ' user-dashboard',
					'sequence' => $sequence,
					'show_status_message' => false
			) );

			$ajax_response = json_encode( array(
					'status' => 'success',
					'data' => array(
							'sequence' => $sequence,
							'rating_form_id' => $rating_form_id,
							'post_id' => $post_id,
							'html' => $html
					)
			) );

			echo $ajax_response;
		}

		die();
	}
}

?>

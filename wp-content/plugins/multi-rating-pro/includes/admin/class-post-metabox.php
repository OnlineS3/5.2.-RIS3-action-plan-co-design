<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Post metabox class
 */
class MRP_Post_Metabox {

	/**
	 * Constructor
	 */
	function __construct() {

		if ( current_user_can( 'mrp_manage_ratings' ) ) {
			add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		}
		add_action( 'save_post', array( $this, 'save_post_meta' ) );

	}

	/**
	 * Adds the meta box container
	 */
	public function add_meta_box( $post_type ) {

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
		$post_types = $auto_placement_settings[MRP_Multi_Rating::POST_TYPES_OPTION];

		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array( $post_types );
		}

		if ( $post_types != null && in_array( $post_type, $post_types ) ) {
			add_meta_box( 'mrp_meta_box', __( 'Multi Rating Pro', 'multi-rating-pro' ), array( $this, 'display_meta_box_content' ), $post_type, 'side', 'default' );
		}

	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_post_meta( $post_id ) {

		if ( ! isset( $_POST['mrp_meta_box_nonce_action'] ) ) {
			return $post_id;
		}

		if ( ! wp_verify_nonce( $_POST['mrp_meta_box_nonce_action'], 'mrp_meta_box_nonce' ) ) {
			return $post_id;
		}

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		$rating_form_id = $_POST['rating-form-id'];

		$rating_form_position = $_POST['rating-form-position'];
		$rating_results_position = $_POST['rating-results-position'];

		$allow_anonymous = $_POST['allow-anonymous'];
		if ( $allow_anonymous !== "" ) { // note ("" == false) = true
			$allow_anonymous = ( $allow_anonymous == 'true' ) ? 'true' : 'false';
		}

		// Update the meta field.
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, $rating_form_id );
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, $rating_form_position );
		update_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, $rating_results_position );
		update_post_meta( $post_id, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, $allow_anonymous );
	}


	/**
	 * Displays the meta box content
	 *
	 * @param WP_Post $post The post object.
	 */
	public function display_meta_box_content( $post ) {

		wp_nonce_field( 'mrp_meta_box_nonce', 'mrp_meta_box_nonce_action' );

		$rating_form_id = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
		$rating_form_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, true );
		$rating_results_position = get_post_meta( $post->ID, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
		$allow_anonymous = get_post_meta( $post->ID, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, true );

		if ( $allow_anonymous !== '' ) {
			$allow_anonymous = ( $allow_anonymous == 'true' ) ? true : false;
		}
		?>

		<p>
			<label for="rating-form-id"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php mrp_rating_form_select( $rating_form_id, true, null, 'rating-form-id', 'rating-form-id', 'widefat' ); ?>
		</p>
		<p>
			<label for="rating-form-position"><?php _e( 'Rating Form Position', 'multi-rating-pro' ); ?></label>
			<?php mrp_rating_form_position_select( $rating_form_position, 'widefat' ); ?>
		</p>
		<p>
			<label for="rating-results-position"><?php _e( 'Rating Results Position', 'multi-rating-pro' ); ?></label>
			<?php mrp_rating_results_position_select( $rating_results_position, 'widefat' ); ?>
		</p>
		<p>
			<label for="allow-anonymous"><?php _e( 'Allow Anonymous Ratings', 'multi-rating-pro' ); ?></label>
			<select name="allow-anonymous" class="widefat">
				<option value="" <?php selected( '', $allow_anonymous, true ); ?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
				<option value="true" <?php selected( 'true', $allow_anonymous, true ); ?>><?php _e( 'Yes', 'multi-rating-pro' ); ?></option>
				<option value="false" <?php selected( 'false', $allow_anonymous, true ); ?>><?php _e( 'No', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<?php
	}
}

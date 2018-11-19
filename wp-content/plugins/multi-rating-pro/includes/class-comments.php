<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Comments class
 *
 * @author dpowney
 *
 */
class MRP_Comments {

	/**
	 * Constructor
	 */
	function __construct() {

		add_action( 'init', array( &$this, 'setup_comment_form_fields' ) );
		add_action( 'wp_insert_comment', array( $this, 'comment_inserted' ), 3, 1 );
		add_action( 'pre_comment_on_post', array( $this, 'validate_rating_entry' ), 10, 1 );
		add_filter( 'comment_text', array( $this, 'comment_text' ), 31, 2 ); // after wpautop filter - wpautop sometimes adds line breaks...
		add_action( 'transition_comment_status', array( $this, 'comment_status_changed' ), 10, 3 );
		add_action( 'delete_comment', array( $this, 'delete_comment' ) );
	}

	/**
	 * Delete all associated ratings with comment id
	 *
	 * @param $post_id
	 */
	public function delete_comment( $comment_id ) {
		MRP_Multi_Rating_API::delete_rating_entry( null, $comment_id );
	}

	/**
	 * If a comment status changes, we may need to update the rating results cache
	 *
	 * @param $new_status
	 * @param $old_status
	 * @param $comment
	 */
	function comment_status_changed( $new_status, $old_status, $comment ) {

		if ( $old_status != $new_status ) {

			$post_id = $comment->comment_post_ID;
			$rating_form_id = MRP_Utils::get_rating_form( $post_id );

			MRP_Multi_Rating_API::delete_calculated_ratings( array(
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id
			) );

			do_action( 'mrp_comment_status_changed', array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
		}
	}

	/**
	 * Setup comment form fields
	 */
	function setup_comment_form_fields() {
		if ( is_user_logged_in() ) {
			add_action( 'comment_form_logged_in_after', array( $this, 'comment_form_logged_in_after' ) );
		} else {
			add_filter( 'comment_form_default_fields', array( $this, 'comment_form_default_fields' ) );
		}
	}

	/**
	 * comment_text() filter to show the rating results along with the comments
	 *
	 * @param $comment_text
	 * @param $comment
	 * @return string
	 */
	function comment_text( $comment_text, $comment = null ) {

		if ( ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) || $comment == null ) {
			return $comment_text;
		}

		$comment_id = $comment->comment_ID;

		return mrp_comment_rating_result( array(
				'comment_id' => $comment_id,
				'comment_text' => $comment_text,
				'echo' => false,
				'entry_status' => 'approved',
				'approved_comments_only' => true
		 ) );
	}

	/**
	 * Add rating items to the comment form when a user is logged in
	 *
	 * @return unknown|string
	 */
	function comment_form_logged_in_after() {

		// get the post id
		global $post;

		$post_id = null;
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset( $post) && !isset( $post_id ) ) {
			return; // No post id available
		}

		if ( ! apply_filters( 'mrp_can_apply_auto_placement', true, 'comment_form_logged_in_after', null, $post_id ) ) {
			return;
		}

		$rating_form_position = mrp_get_rating_form_position( $post_id );
		if ( $rating_form_position != 'comment_form' ) {
			return;
		}

		// get user id
		global $wp_roles;
		$user = wp_get_current_user();
		$user_id = $user->ID;

		if ( MRP_Utils::disallowed_user_roles_check( $user_id ) ) {
			return;
		}

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

		$rating_form_id = MRP_Utils::get_rating_form( $post->ID );
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		if ( $rating_form == null ) {
			return;
		}

		$rating_items = $rating_form['rating_items'];
		$custom_fields = $rating_form['custom_fields'];
		$review_fields = $rating_form['review_fields'];

		$show_title = isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] );

		if ( $user_id != 0 && MRP_Multi_Rating_API::user_rating_exists(
				array( 'rating_form_id' => $rating_form_id, 'post_id' => $post_id, 'user_id' => $user_id ) ) ) {
			return;
		}

		MRP_Utils::$sequence++;

		$html = '';

		/**
		 * Include rating
		 */
		$default_include_rating  = $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION];
		if ( $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == true ) {
			$checked = ( $default_include_rating == true ) ? ' checked="checked"' : '';

			ob_start();
			mrp_get_template_part( 'comment-form', 'include-rating', true, array(
				'checked' => ( $default_include_rating == true ) ? 'checked="checked"' : null
			) );
			$html .= ob_get_contents();
			ob_end_clean();
		}

		ob_start();
		if ( isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] ) ) {
			?>
			<p class="mrp review-field">
				<label for="title-<?php echo MRP_Utils::$sequence; ?>" class="input-label"><?php _e( 'Title', 'multi-rating-pro' ); ?></label><br />
				<input type="text" name="title-<?php echo MRP_Utils::$sequence; ?>" size="30" placeholder="<?php _e( 'Enter title...', 'multi-rating-pro' ); ?>" class="title" maxlength="100"></input>
			</p>
			<?php
		}
		$html .= ob_get_contents();
		ob_end_clean();

		/**
		 * Rating items
		 */
		foreach ( $rating_items as $rating_item ) {

			$rating_item_id = $rating_item['rating_item_id'];
			$element_id = 'rating-item-' . $rating_item_id . '-' . MRP_Utils::$sequence;
			$description = $rating_item['description'];
			$rating_item_type = $rating_item['type'];
			$max_option_value =  $rating_item['max_option_value'];
			$default_option_value = $rating_item['default_option_value'];
			$required = $rating_item['required'];
			$option_value_text = $rating_item['option_value_text'];
			$only_show_text_options = $rating_item['only_show_text_options'];
			$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
			$allow_not_applicable = $rating_item['allow_not_applicable'];

			ob_start();
			mrp_get_template_part( 'rating-form', 'rating-item', true, array(
				'rating_item_id' => $rating_item_id,
				'element_id' => $element_id,
				'description' => $description,
				'max_option_value' => $max_option_value,
				'default_option_value' => $default_option_value,
				'required' => $required,
				'option_value_text' => $option_value_text,
				'class' => 'mrp-comment-form-field',
				'style' => ( $default_include_rating == true ) ? null : 'display: none;',
				'element_id' => $element_id,
				'rating_item_type' => $rating_item_type,
				'option_value_text_lookup' => $option_value_text_lookup,
				'only_show_text_options' => $only_show_text_options,
				'allow_not_applicable' => $allow_not_applicable,
			) );

			?>
			<!-- hidden field to get rating item id -->
			<input type="hidden" value="<?php echo $rating_item_id; ?>" class="rating-item-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo MRP_Utils::$sequence; ?>" id="hidden-rating-item-id-<?php echo $rating_item_id; ?>" />
			<?php

			$html .= ob_get_contents();
			ob_end_clean();
		}

		/**
		 * Custom fields
		 */
		foreach ( $custom_fields as $custom_field ) {

			$custom_field_id = $custom_field['custom_field_id'];
			$label = $custom_field['label'];
			$required = $custom_field['required'];
			$max_length = $custom_field['max_length'];
			$type = $custom_field['type'];
			$placeholder = $custom_field['placeholder'];
			$element_id = 'custom-field-' . $custom_field_id . '-' . MRP_Utils::$sequence;
			$value = '';

			ob_start();
			mrp_get_template_part( 'rating-form', 'custom-fields', true, array(
				'custom_field_id' => $custom_field_id,
				'label' => $label,
				'required' => $required,
				'max_length' => $max_length,
				'type' => $type,
				'placeholder' => $placeholder,
				'value' => $value,
				'style' => ( $default_include_rating == true ) ? null : 'display: none;',
				'class' => 'mrp-comment-form-field',
				'element_id' => $element_id
			) );
			$html .= ob_get_contents();
			ob_end_clean();
		}

		// hidden field to identify the rating form
		$html .= '<input type="hidden" name="rating-form-id" value="' . $rating_form_id . '" />';
		$html .= '<input type="hidden" name="sequence" value="' . MRP_Utils::$sequence . '" />';

		echo $html;
	}

	/**
	 * Adds the rating items to the comments form when a user is not logged in
	 *
	 * @param $fields
	 * @return string
	 */
	function comment_form_default_fields( $fields ) {

		// get the post id
		global $post;

		$post_id = null;
		if ( ! isset( $post_id ) && isset( $post ) ) {
			$post_id = $post->ID;
		} else if ( ! isset( $post ) && ! isset( $post_id ) ) {
			return $fields; // No post id available
		}

		if ( ! apply_filters( 'mrp_can_apply_auto_placement', true, 'comment_form_default_fields', $fields, $post_id ) ) {
			return $fields;
		}

		$rating_form_position = mrp_get_rating_form_position( $post_id );
		if ( $rating_form_position != 'comment_form' ) {
			return $fields;;
		}

		if ( ! MRP_Utils::allow_anonymous_rating_check( $post_id, 0 ) ) {
			return $fields;
		}

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

		$rating_form_id = MRP_Utils::get_rating_form( $post->ID );
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		if ( $rating_form == null ) {
			return $fields;
		}

		$rating_items = $rating_form['rating_items'];
		$custom_fields = $rating_form['custom_fields'];
		$review_fields = $rating_form['review_fields'];

		$show_title = isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] );

		/**
		 * Include rating
		 */
		$default_include_rating = $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION];
		if ( $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == true ) {
			$checked = ( $default_include_rating == true ) ? ' checked="checked"' : '';

			ob_start();
			mrp_get_template_part( 'comment-form', 'include-rating', true, array(
				'checked' => ( $default_include_rating == true ) ? 'checked="checked"' : null
			) );
			$html = ob_get_contents();
			ob_end_clean();

			$fields['include-rating'] = $html;
		}

		MRP_Utils::$sequence++;

		ob_start();
		if ( isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] ) ) {
			?>
			<p class="mrp review-field">
				<label for="title-<?php echo MRP_Utils::$sequence; ?>" class="input-label"><?php _e( 'Title', 'multi-rating-pro' ); ?></label><br />
				<input type="text" name="title-<?php echo MRP_Utils::$sequence; ?>" size="30" placeholder="<?php _e( 'Enter title...', 'multi-rating-pro' ); ?>" class="title" maxlength="100"></input>
			</p>
			<?php
		}
		$html = ob_get_contents();
		ob_end_clean();

		$fields['title-' . MRP_Utils::$sequence] = $html;

		/**
		 * Rating items
		 */
		foreach ( $rating_items as $rating_item ) {

			$rating_item_id = $rating_item['rating_item_id'];
			$element_id = 'rating-item-' . $rating_item_id . '-' . MRP_Utils::$sequence ;
			$description = $rating_item['description'];
			$rating_item_type = $rating_item['type'];
			$max_option_value =  $rating_item['max_option_value'];
			$default_option_value = $rating_item['default_option_value'];
			$required = $rating_item['required'];
			$option_value_text = $rating_item['option_value_text'];
			$only_show_text_options = $rating_item['only_show_text_options'];
			$allow_not_applicable = $rating_item['allow_not_applicable'];
			$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );

			ob_start();
			mrp_get_template_part( 'rating-form', 'rating-item', true, array(
					'rating_item_id' => $rating_item_id,
					'element_id' => $element_id,
					'description' => $description,
					'max_option_value' => $max_option_value,
					'default_option_value' => $default_option_value,
					'required' => $required,
					'option_value_text' => $option_value_text,
					'class' => 'mrp-comment-form-field',
					'style' => ( $default_include_rating == true ) ? null : 'display: none;',
					'element_id' => $element_id,
					'rating_item_type' => $rating_item_type,
					'option_value_text_lookup' => $option_value_text_lookup,
					'only_show_text_options' => $only_show_text_options,
					'allow_not_applicable' => $allow_not_applicable
			) );

			?>
			<!-- hidden field to get rating item id -->
			<input type="hidden" value="<?php echo $rating_item_id; ?>" class="rating-item-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo MRP_Utils::$sequence; ?>" id="hidden-rating-item-id-<?php echo $rating_item_id; ?>" />
			<?php

			$html = ob_get_contents();
			ob_end_clean();

			$fields[$element_id] = $html;

		}

		/**
		 * Custom fields
		 */
		$custom_fields = MRP_Multi_Rating_API::get_custom_fields( $rating_form_id );

		foreach ( (array) $custom_fields as $custom_field ) {

			$custom_field_id = $custom_field['custom_field_id'];
			$label = $custom_field['label'];
			$required = $custom_field['required'];
			$max_length = $custom_field['max_length'];
			$type = $custom_field['type'];
			$placeholder = $custom_field['placeholder'];
			$element_id = 'custom-field-' . $custom_field_id . '-' . MRP_Utils::$sequence;
			$value = '';

			ob_start();
			mrp_get_template_part( 'rating-form', 'custom-fields', true, array(
				'custom_field_id' => $custom_field_id,
				'label' => $label,
				'required' => $required,
				'max_length' => $max_length,
				'type' => $type,
				'placeholder' => $placeholder,
				'value' => $value,
				'style' => ( $default_include_rating == true ) ? null : 'display: none;',
				'class' => 'mrp-comment-form-field',
				'element_id' => $element_id
			) );
			$html = ob_get_contents();
			ob_end_clean();

			$fields[$element_id] = $html;
		}


		// hidden field to identify the rating form
		$fields['rating_form_id'] = '<input type="hidden" name="rating-form-id" value="' . $rating_form_id . '" />';
		$fields['sequence'] = '<input type="hidden" name="sequence" value="' . MRP_Utils::$sequence . '" />';

		return $fields;
	}

	/**
	 * Gets rating entry
	 *
	 * @param unknown $comment_id
	 * @return multitype:unknown NULL multitype:unknown  Ambigous <string, unknown>
	 */
	function create_rating_entry( $comment_id = null ) {

		$post_id = isset( $_POST['comment_post_ID'] ) && is_numeric( $_POST['comment_post_ID'] ) ? intval( $_POST['comment_post_ID'] ) : null;
		$rating_form_id = isset( $_POST['rating-form-id'] ) && is_numeric( $_POST['rating-form-id'] ) ? intval( $_POST['rating-form-id'] ) : null;
		$sequence = isset( $_POST['sequence'] ) && is_numeric( $_POST['sequence'] ) ? intval( $_POST['sequence'] ) : null;
		$title = isset( $_POST['title-' . $sequence] ) ? $_POST['title-' . $sequence] : null;

		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		$rating_items = $rating_form['rating_items'];
		$custom_fields = $rating_form['custom_fields'];

		// get user id
		global $wp_roles;
		$user = wp_get_current_user();
		$user_id = $user->ID;

		$rating_item_values = array();
		foreach ( $rating_items as $rating_item ) {
			if ( isset( $_POST['rating-item-' . $rating_item['rating_item_id'] . '-' . $sequence] ) ) {
				$rating_item_values[$rating_item['rating_item_id']]  = intval( $_POST['rating-item-' . $rating_item['rating_item_id'] . '-' . $sequence] );
			}
			// if not applicable, override value
			if ( isset( $_POST['rating-item-' . $rating_item['rating_item_id'] . '-' . $sequence . '-not-applicable'] ) ) {
				$rating_item_values[$rating_item['rating_item_id']] = -1;
			}
		}

		$custom_field_values = array();
		foreach ( $custom_fields as $custom_field ) {
			if ( isset( $_POST['custom-field-' . $custom_field['custom_field_id'] . '-' . $sequence] ) ) {
				$custom_field_values[$custom_field['custom_field_id']] = $_POST['custom-field-' . $custom_field['custom_field_id'] . '-' . $sequence];
			}
		}

		return array(
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'entry_date' => current_time( 'mysql' ),
				'title' => $title,
				'comment_id' => $comment_id,
				'user_id' => $user_id,
				'rating_item_values' => $rating_item_values,
				'custom_field_values' => $custom_field_values
		);
	}

	/**
	 * Validates the rating form before the comment is saved
	 *
	 * @param $post_id
	 */
	function validate_rating_entry( $post_id ) {

		if ( ! isset( $_POST['rating-form-id'] ) ) {
			return;
		}

		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

		// check if the rating is optional and whether the user wanted to save a rating with their comment
		if ( $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == 'optional' && ! isset( $_POST['include-rating'] ) ) {
			return;
		}

		$validation_results = MRP_Utils::validate_rating_entry( array(), $this->create_rating_entry() );

		if (MRP_Utils::has_validation_error( $validation_results ) ) {

			$messages = '';
			foreach ( $validation_results as $validation_result ) {

				if ( $validation_result['severity'] == 'error' ) {
					$messages .= '<p class="error">' . $validation_result['message'] . '</p>';
				}
			}

			if ( defined('DOING_AJAX') && DOING_AJAX ) {
				die( $messages );
			}
			wp_die( $messages );
		}
	}


	/**
	 * Save rating after comment has been inserted
	 *
	 * @param $comment_id
	 * @param $comment_object
	 */
	function comment_inserted( $comment_id ) {

		if ( ! isset( $_POST['rating-form-id'] ) || ! isset( $_POST['comment_post_ID'] ) ) {
			return;
		}

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

		// check if the rating is optional and whether the user wanted to save a rating with their comment
		if ( $auto_placement_settings[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == true && ! isset( $_POST['include-rating'] ) ) {
			return;
		}

		$rating_entry = $this->create_rating_entry( $comment_id );

		MRP_Multi_Rating_API::save_rating_entry( $rating_entry );

	}
}

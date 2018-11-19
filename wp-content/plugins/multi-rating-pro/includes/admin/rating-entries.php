<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the rating entries screen
 *
 * @since 0.1
 */
function mrp_rating_entries_screen() {
	?>
	<div class="wrap" id="mrp-rating-entries">
		<?php
		if ( isset( $_REQUEST['rating-entry-id'] ) ) {
			if ( ! current_user_can( 'mrp_moderate_ratings' ) && ! current_user_can( 'mrp_manage_ratings' ) ) {
				return;
			}

			$rating_entry_id = null;
			$rating_entry = null;

			if ( strlen( $_REQUEST['rating-entry-id'] ) > 0 ) {
				$rating_entry_id = intval( $_REQUEST['rating-entry-id'] );
				$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array(
					'rating_entry_id' => $rating_entry_id
				) );

				if ( $rating_entry == null ) {
					echo '<div class="error"><p>' . __( 'An error has occured.', 'multi-rating-pro' ) . '</p></div>';
					return;
				}

				?>
				<h2><?php printf( __( 'Edit Rating Entry #%d', 'multi-rating-pro' ), $rating_entry_id ); ?></h2>
			<?php
			} else {
				?><h2><?php  _e( 'Add New Rating Entry', 'multi-rating-pro' ); ?></h2><?php
			}

			if ( ! current_user_can( 'mrp_manage_ratings' ) && $rating_entry == null ) {
				echo '<div class="error"><p>' . __( 'An error has occured.', 'multi-rating-pro' ) . '</p></div>';
				return;
			}

			$rating_form_id = ( $rating_entry != null ) ?  $rating_entry['rating_form_id'] : MRP_Utils::get_rating_form();
			$post_id = ( $rating_entry != null ) ?  $rating_entry['post_id'] : null;
			$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

			$custom_fields = $rating_form['custom_fields'];
			$rating_items = $rating_form['rating_items'];
			$review_fields = $rating_form['review_fields'];

			$user_id = 0;
			if ( $rating_entry != null ) {
				$user_id = $rating_entry['user_id'];
			}

			$date = '';
			$time = '';
			if ( $rating_entry != null ) {
				$parts = explode( ' ', $rating_entry['entry_date'] );
				$date = isset( $parts[0] ) ? $parts[0] : '';
				$time = isset( $parts[1] ) ? $parts[1] : '';
			}
			?>

			<form name="rating-entry" id="rating-entry" method="post" action="#">

				<?php
				// only users who can manage ratings can add or edit the post, rating form, user and entry date
				?>
				<table class="form-table">
					<tr class="form-field">
						<th scope="row"><label><?php _e( 'Post', 'multi-rating-pro' ); ?></label></th>
						<td>
							<?php
							if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
								$post = get_post( $post_id );
								echo $post->post_title;
							} else {
								?>
								<input name="post-id" id="post-id" type="number" class="small-text" value="<?php echo $post_id; ?>" required />
								<label><?php _e( 'Enter Post ID', 'multi-rating-pro' ); ?></label>
								<?php
							} ?>
						</td>
					</tr>
					<tr class="form-field">
						<th scope="row"><label><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label></th>
						<td>
							<?php
							if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
								$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
								echo $rating_form['name'];
							} else {
								mrp_rating_form_select( $rating_form_id, false, false, 'rating-form-id', 'rating-form-id', 'regular-text' );
							}
							?>
						</td>
					</tr>
					<?php
					if ( current_user_can( 'mrp_manage_ratings' ) ) {
						?>
						<tr class="form-field">
							<th scope="row" valign="top"><label for="user-id"><?php _e( 'User', 'multi-rating-pro' ); ?></label></th>
							<td>
								<?php
								wp_dropdown_users( array(
				    					'show_option_all'			=> false,
				    					'show_option_none'			=> ' ',
				   						'option_none_value'			=> 0,
										'selected'					=> $user_id,
				    					'include_selected'			=> true,
				    					'name'						=> 'user-id',
				    					'class'						=> 'small-text'
								) );
								?>
							</td>
						</tr>
						<?php
					} ?>

					<tr class="form-field">
						<th scope="row" valign="top">
							<label for="entry-date"><?php _e( 'Date & Time', 'multi-rating-pro' ); ?></label>
						</th>
						<td>
							<?php
							if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
								echo $rating_entry['entry_date'];
							} else {
								?>
								<input type="text" class="date-picker small-text" name="date" id="date" placeholder="yyyy-MM-dd" value="<?php echo $date; ?>" required />
								<input type="text" class="time-picker small-text" name="time" id="time" placeholder="HH:mm:ss" value="<?php echo $time; ?>" />
								<?php
							}
							?>
						</td>
					</tr>
				</table>

				<h3><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></h3>

				<table class="form-table">
					<?php
					/*
					 * Title
					 */
					if ( isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] ) ) {
						mrp_review_field_field( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID], $rating_entry );
					}

					/*
					 * Rating Items
					 */
					foreach ( $rating_items as $rating_item ) {
						mrp_rating_item_field( $rating_item, $rating_entry );
					}

					/*
					 * Name, E-mail and Comment
					 */
					if ( isset( $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] ) ) {
						mrp_review_field_field( $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID], $rating_entry );
					}

					if ( isset( $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] ) ) {
						mrp_review_field_field( $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID], $rating_entry );
					}

					if ( isset( $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] ) ) {
						mrp_review_field_field( $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID], $rating_entry );
					}

					/*
					 * Custom Fields
					 */
					foreach ( $custom_fields as $custom_field ) {
						mrp_custom_field_field( $custom_field, $rating_entry );
					}
					?>
				</table>

				<input type="hidden" name="rating-entry-id" id="rating-entry-id" value="<?php if ( isset( $rating_entry ) ) { echo $rating_entry_id; } ?>" />
				<input type="hidden" name="rating-entry-form-submitted" id="rating-entry-form-submitted" value="true" />
				<?php
				submit_button( __( 'Update', 'multi-rating-pro' ), 'primary', 'update-rating-btn', true, null );
				?>

			</form>
			<?php
		} else {
			?>
			<h2>
				<?php
				_e( 'Rating Entries', 'multi-rating-pro' );

				// only users with capability mrp_manage_ratings can manually add ratings
				if ( current_user_can( 'mrp_manage_ratings' ) ) {
					?><a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG; ?>&rating-entry-id="><?php _e( 'Add New', 'multi-rating-pro' ); ?></a><?php
				} ?>
			</h2>

			<form method="get" id="rating-entries-table-form" action="<?php echo admin_url( 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG ); ?>">
				<?php
				$rating_entry_table = new MRP_Rating_Entry_Table();
				$rating_entry_table->prepare_items();
				$rating_entry_table->views();
				$rating_entry_table->display();
				?>
				<input type="hidden" name="page" value="<?php echo MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG; ?>" />
			</form>
			<?php
		}
		?>
	</div>
	<?php
}


/**
 * Form submit to add or edit a rating entry.
 * Note redirects back to the entries page on success if editing.
 */
function mrp_rating_entry_form_submit() {

	if ( ! ( current_user_can( 'mrp_moderate_ratings' ) || current_user_can( 'mrp_manage_ratings' ) ) ) {
		return;
	}

	$rating_entry = array();
	if ( isset( $_POST['rating-entry-id'] ) && strlen( $_POST['rating-entry-id'] ) > 0 ) {
		$rating_entry_id = intval( $_POST['rating-entry-id'] );
		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );

		if ( $rating_entry == null ) {
			echo '<div class="error"><p>' . __( 'An error has occured. Unable to find existing rating entry.', 'multi-rating-pro' ) . '</p></div>';
			return;
		}
	}

	// only users who can manage ratings can edit the post, rating form, user and entry date
	if ( current_user_can( 'mrp_manage_ratings' ) ) {

		if ( isset( $_POST['post-id'] ) ) {
			$rating_entry['post_id'] = intval( $_POST['post-id'] );
		}

		if ( isset( $_POST['rating-form-id'] ) ) {
			$rating_entry['rating_form_id'] = intval( $_POST['rating-form-id'] );
		}

		if ( isset( $_POST['user-id'] ) ) {
			$rating_entry['user_id'] = intval( $_POST['user-id'] );
		}

		if ( isset( $_POST['date'] ) && strlen( $_POST['date'] ) > 0 ) {
			$rating_entry['entry_date'] = $_POST['date'];

			if ( isset( $_POST['time'] ) && strlen( $_POST['time'] ) > 0 ) {
				$rating_entry['entry_date'] .= ' ' . $_POST['time'];
			} else {
				$rating_entry['entry_date'] .= ' 00:00:00';
			}
		}
	}

	if ( isset( $_POST['title'] ) ) {
		$rating_entry['title'] = $_POST['title'];
	}

	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_entry['rating_form_id'] );
	$rating_items = $rating_form['rating_items'];
	$custom_fields = $rating_form['custom_fields'];

	$rating_item_values = array();
	foreach ( $rating_items as $rating_item ) {
		$rating_item_id = $rating_item['rating_item_id'];
		if ( isset( $_POST['rating-item-' . $rating_item_id . '-not-applicable'] ) ) {
			$rating_item_values[$rating_item_id] = -1;
		} else if ( isset( $_POST['rating-item-' . $rating_item_id] ) ) {
			$rating_item_values[$rating_item_id] = $_POST['rating-item-' . $rating_item_id];
		}
	}
	$rating_entry['rating_item_values'] = $rating_item_values;

	if ( isset( $_POST['name'] ) ) {
		$rating_entry['name'] = $_POST['name'];
	}

	if ( isset( $_POST['email'] ) ) {
		$rating_entry['email'] = $_POST['email'];
	}

	if ( isset( $_POST['comment'] ) ) {
		$rating_entry['comment'] = esc_textarea( $_POST['comment'] );
	}

	$custom_field_values = array();
	foreach ( $custom_fields as $custom_field ) {
		$custom_field_id = $custom_field['custom_field_id'];
		if ( isset( $_POST['custom-field-' . $custom_field_id] ) ) {
			$value = $_POST['custom-field-' . $custom_field_id];
			$custom_field_values[$custom_field_id] = $value;
		}
	}
	$rating_entry['custom_field_values'] = $custom_field_values;

	$save_rating_entry_response = MRP_Multi_Rating_API::save_rating_entry( $rating_entry );
	$validation_results = $save_rating_entry_response['validation_results'];

	if ( MRP_Utils::has_validation_error( $validation_results ) ) {
		echo '<div class="error">';
		foreach ( $validation_results as $validation_result ) {
			echo '<p>' . $validation_result['message'] . '</p>';
		}
		echo '</div>';
		return;
	}

	$redirect_url = 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&post-id=' . $rating_entry['post_id'] . '&rating-form-id=' . $rating_entry['rating_form_id'];

	if ( isset( $_REQUEST['username'] ) ) {
		$redirect_url .= '&username=' . $_REQUEST['username'];
	}
	if ( isset( $_REQUEST['to-date'] ) )  {
		$redirect_url .= '&to-date=' . $_REQUEST['to-date'];
	}
	if ( isset( $_REQUEST['from-date'] ) )  {
		$redirect_url .= '&from-date=' . $_REQUEST['from-date'];
	}
	if ( isset( $_REQUEST['comments-only'] ) )  {
		$redirect_url .= '&comments-only=' . $_REQUEST['comments-only'];
	}
	if ( isset( $_REQUEST['paged'] ) )  {
		$redirect_url .= '&paged=' . $_REQUEST['paged'];
	}

	wp_redirect( $redirect_url );
	exit();
}
if ( isset( $_POST['rating-entry-form-submitted'] ) && $_POST['rating-entry-form-submitted'] == 'true' ) {
	add_action( 'admin_init', 'mrp_rating_entry_form_submit' );
}




/**
 * Rating item field
 */
function mrp_rating_item_field( $rating_item, $rating_entry ) {

	$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
	$rating_item_id = $rating_item['rating_item_id'];
	$rating_item_values = $rating_entry['rating_item_values'];
	$is_not_applicable = isset( $rating_item_values[$rating_item_id] ) && $rating_item_values[$rating_item_id] == -1 ? true : false

	?>
	<tr class="form-field">
		<th scope="row"><label><?php echo $rating_item['description']; ?></label></td>
		<td>
			<?php

			echo '<select class="rating-item small-text" name="rating-item-' . $rating_item_id . '" id="rating-item-' . $rating_item_id . '">';

			$index = 0;
			if ( $rating_item['required'] == true ) {
				$index = 1;
			}

			for ( $index; $index <= $rating_item['max_option_value']; $index++ ) {
				$is_selected = false;
				if ( isset( $rating_entry['rating_item_values'][$rating_item_id] )
						&& $rating_entry['rating_item_values'][$rating_item_id] == $index
						|| ( $is_not_applicable && $rating_item['default_option_value'] ) == $index ) {
					$is_selected = true;
				}

				$option_text = $index;
				if ( isset( $option_value_text_lookup[$index] ) ) {
					$option_text = $option_value_text_lookup[$index];
				}

				echo '<option value="' . $index . '"';
				if ( $is_selected ) {
					echo ' selected="selected"';
				}
				echo '>' . $option_text . '</option>';
			}

			echo '</select>';

			if ( $rating_item['allow_not_applicable'] ) {
				?>
				<input type="checkbox" name="rating-item-<?php echo $rating_item_id; ?>-not-applicable" <?php
				if ( $is_not_applicable ){
					?>checked="checked"><?php
				}
				?>
				<label><?php _e( 'Not applicable', 'multi-rating-pro' ); ?></label>
				<?php
			}
			?>
		</td>
	</tr>
	<?php
}


/**
 * Custom field field
 */
function mrp_custom_field_field( $custom_field, $rating_entry ) {

	$custom_field_id = $custom_field['custom_field_id'];
	$label = $custom_field['label'];
	$required = $custom_field['required'];
	$max_length = $custom_field['max_length'];
	$type = $custom_field['type'];
	$placeholder = $custom_field['placeholder'];
	$value = isset( $rating_entry['custom_field_values'][$custom_field_id] ) ? $rating_entry['custom_field_values'][$custom_field_id] : '';

	$required_html5 = $required ? 'required' : '';
	?>
	<tr class="form-field">
		<th scope="row"><label for="custom-field-<?php echo $custom_field_id; ?>"><?php echo $label; ?></label></td>
		<td>
			<?php
			if ( $type == 'input' ) {
				echo '<input type="text" class="regular-text custom-field" name="custom-field-' . $custom_field_id . '" size="30" placeholder="' .  $placeholder . '" id="custom-field-' . $custom_field_id . '" value="' . $value . '" maxlength="' . $max_length . '" ' . $required_html5 . '></input></p>';
			} else {
				echo '<textarea class="custom-field" rows="5" name="custom-field-' . $custom_field_id . '" placeholder="' .  $placeholder . '" id="custom-field-' . $custom_field_id . '" maxlength="' . $max_length . '" ' . $required_html5 . '>' . $value . '</textarea></p>';
			} ?>
		</td>
	</tr>
	<?php
}


/**
 * Review field field
 */
function mrp_review_field_field( $review_field, $rating_entry ) {

	$required_html5 = isset( $review_field['required'] ) && $review_field['required'] ? 'required' : '';

	if ( $review_field['review_field_id'] == MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID ) {
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="title"><?php _e( 'Title', 'multi-rating-pro' ); ?></label></th>
			<td><input class="regular-text review-field" name="title" id="title" type="text" value="<?php echo $rating_entry['title']; ?>" size="40"placeholder="<?php _e( 'Enter a title...', 'multi-rating-pro' ); ?>" <?php echo $required_html5; ?>/></td>
		</tr>
		<?php
	} else if ( $review_field['review_field_id'] == MRP_Multi_Rating::NAME_REVIEW_FIELD_ID ) {
		$name = $rating_entry['name'];
		$disabled = '';
		if ( $rating_entry['user_id'] != 0 ) {
			$user_info = get_userdata( $rating_entry['user_id'] );
			$name = $user_info->display_name;
			$disabled = 'disabled';
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="name"><?php _e( 'Name', 'multi-rating-pro' ); ?></label></th>
			<td><input class="regular-text review-field" name="name" id="name" type="text" value="<?php echo $name; ?>" size="40" placeholder="<?php _e( 'Enter a name...', 'multi-rating-pro' ); ?>" <?php echo $required_html5; ?> <?php echo $disabled; ?>/></td>
		</tr>
		<?php
	} else if ( $review_field['review_field_id'] == MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID ) {
		$email = $rating_entry['email'];
		$disabled = '';
		if ( $rating_entry['user_id'] != 0 ) {
			$user_info = get_userdata( $rating_entry['user_id'] );
			$email = $user_info->user_email;
			$disabled = 'disabled';
		}
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="email"><?php _e( 'Email', 'multi-rating-pro' ); ?></label></th>
			<td><input class="regular-text review-field" name="email" id="email" type="email" value="<?php echo $email; ?>" size="40" placeholder="<?php _e( 'Enter an e-mail address...', 'multi-rating-pro' ); ?>" <?php echo $required_html5; ?> <?php echo $disabled; ?>/></td>
		</tr>
		<?php
	} else {
		$disabled = ( $rating_entry['comment_id'] != null ) ? 'disabled' : '';
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="comment"><?php _e( 'Comment', 'multi-rating-pro' ); ?></label></th>
			<td><textarea name="comment" id="comment" rows="5" cols="50" class="large-text review-field" placeholder="<?php _e( 'Enter a comment...', 'multi-rating-pro' ); ?>" <?php echo $required_html5; ?> <?php echo $disabled; ?>><?php echo $rating_entry['comment']; ?></textarea></td>
		</tr>
		<?php
	}
}

/**
 * Get rating form details by id
 */
function mrp_get_edit_rating_form() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID . '-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$rating_form_id = intval( $_POST['ratingFormId'] );
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		$rating_entry = null;
		if ( isset( $_POST['ratingEntryId'] ) ) {
			$rating_entry = intval( $_POST['ratingEntryId'] );
			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array(
				'rating_entry_id' => $rating_entry_id
			) );
		}

 		if ( isset( $_POST['userId'] ) ) {
			$user_id = intval( $_POST['userId'] );
			if ( $rating_entry == null ) {
				$rating_entry = array();
			}
			$rating_entry['user_id'] = $user_id;
		}

		foreach ( $rating_form['rating_items'] as $rating_item_id => & $rating_item ) {
			ob_start();
			mrp_rating_item_field( $rating_item, $rating_entry );
			$html = ob_get_contents();
			ob_end_clean();
			$rating_item['html'] = $html;
		}

		foreach ( $rating_form['cutom_fields'] as $custom_field_id => & $custom_field ) {
			ob_start();
			mrp_custom_field_field( $custom_field, $rating_entry );
			$html = ob_get_contents();
			ob_end_clean();
			$custom_field['html'] = $html;
		}

		foreach ( $rating_form['review_fields'] as $review_field_id => & $review_field ) {
			ob_start();
			mrp_review_field_field( $review_field, $rating_entry );
			$html = ob_get_contents();
			ob_end_clean();
			$review_field['html'] = $html;
		}

		$ajax_response = json_encode( array (
				'status' => 'success',
				'data' => array(
					'rating_form' => $rating_form,
				)
		) );

		echo $ajax_response;
	}

	die();
}
add_action( 'wp_ajax_get_edit_rating_form', 'mrp_get_edit_rating_form' );


/**
 * Get user info by user id
 */
function mrp_get_user_info() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID . '-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$user_id = intval( $_POST['userId'] );
		$user_info = get_userdata( $user_id );

		$ajax_response = json_encode( array (
				'status' => 'success',
				'data' => array(
					'name' => $user_info->display_name,
					'email' => $user_info->user_email
				)
		) );

		echo $ajax_response;
	}
	die();
}
add_action( 'wp_ajax_get_user_info', 'mrp_get_user_info' );

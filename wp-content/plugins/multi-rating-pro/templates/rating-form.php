<div class="rating-form <?php echo $class; ?>">
	<?php
	$user_can_update_delete = apply_filters( 'mrp_user_can_update_delete', $user_can_update_delete, $user_id, $rating_entry_id, $post_id, $rating_form_id );
	
	// if already submitted a rating and user cannot update or delete, do not show rating form
	if ( ! ($rating_entry_id != null && ! $user_can_update_delete ) ) {
		
		if ( ! empty( $title ) ) {
		
			$before_title = apply_filters( 'mrp_rating_form_before_title', $before_title, $post_id, $rating_form_id );
			$after_title = apply_filters( 'mrp_rating_form_after_title', $after_title, $post_id, $rating_form_id );
		
			echo "$before_title" . esc_html( $title ) . "$after_title";
		}
		?>
		
		<form id="rating-form-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" action="#">
		<?php
			// Show title input
			if ( $show_title_input == true ) {
				?>
				<p class="mrp review-field">
					<label for="title-<?php echo $sequence; ?>" class="input-label"><?php _e( 'Title', 'multi-rating-pro' ); ?></label><br />
					<input type="text" name="title-<?php echo $sequence; ?>" size="30" placeholder="<?php _e( 'Enter title...', 'multi-rating-pro' ); ?>" id="mrp-title-<?php echo $sequence; ?>" class="title" value="<?php echo esc_attr( $title2 ); ?>" maxlength="100"></input>
					<span id="title-<?php echo $sequence; ?>-error" class="mrp-error"></span>
				</p>
				<?php
			}
		
			do_action( 'mrp_rating_form_before_rating_items', $post_id, $rating_form_id, $rating_items );
	
			// rating items
			foreach ( (array) $rating_items as $rating_item ) {
				
				$rating_item_id = $rating_item['rating_item_id'];
				$element_id = 'rating-item-' . $rating_item_id . '-' . $sequence ;
				$description = $rating_item['description'];
				$rating_item_type = $rating_item['type'];
				$max_option_value =  $rating_item['max_option_value'];
				$required = $rating_item['required'];
				$option_value_text = $rating_item['option_value_text'];
				$only_show_text_options = $rating_item['only_show_text_options'];
				$allow_not_applicable = $rating_item['allow_not_applicable'];
				$is_not_applicable = isset( $rating_item_values[$rating_item_id] ) && $rating_item_values[$rating_item_id] == -1 ? true : false;
				$default_option_value = isset( $rating_item_values[$rating_item_id] ) && ! $is_not_applicable ? $rating_item_values[$rating_item_id] : $rating_item['default_option_value'];
				$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $option_value_text );
				
				mrp_get_template_part( 'rating-form', 'rating-item', true, array(
					'rating_item_id' => $rating_item_id,
					'element_id' => $element_id,
					'description' => $description,
					'max_option_value' => $max_option_value,
					'default_option_value' => $default_option_value,
					'required' => $required,
					'option_value_text' => $option_value_text,
					'class' => null,
					'style' => null,
					'rating_item_type' => $rating_item_type,
					'option_value_text_lookup' => $option_value_text_lookup,
					'rating_entry_id' => $rating_entry_id,
					'only_show_text_options' => $only_show_text_options,
					'allow_not_applicable' => $allow_not_applicable,
					'is_not_applicable' => $is_not_applicable
				) );
				
				?>
				<!-- hidden field to get rating item id -->
				<input type="hidden" value="<?php echo $rating_item_id; ?>" class="rating-item-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" id="hidden-rating-item-id-<?php echo $rating_item_id; ?>" />
				<?php
			}
			
			do_action( 'mrp_rating_form_before_optional_fields', $post_id, $rating_form_id );
			
			// Show name input. Do not show name input if logged in, the user's display name is used
			if ( $show_name_input == true && $user_id == 0 && $comment_id == null ) {
				?>
				<p class="mrp review-field">
					<label for="name-<?php echo $sequence; ?>" class="input-label"><?php _e( 'Name', 'multi-rating-pro' ); ?></label><br />
					<input type="text" name="name-<?php echo $sequence; ?>" size="30" placeholder="<?php _e( 'Enter your name...', 'multi-rating-pro' ); ?>" id="mrp-name-<?php echo $sequence; ?>" class="name" value="<?php echo esc_attr( $name ); ?>" maxlength="100"></input>
					<span id="name-<?php echo $sequence; ?>-error" class="mrp-error"></span>
				</p>
				<?php
			}
			
			// Show email input. Do not show email input if logged in, the user's email is used
			if ( $show_email_input == true && $user_id == 0 && $comment_id == null ) {
				?>
				<p class="mrp review-field">
					<label for="email-<?php echo $sequence; ?>" class="input-label"><?php _e( 'Email', 'multi-rating-pro' ); ?></label><br />
					<input type="text" name="email-<?php echo $sequence; ?>" size="30" placeholder="<?php  _e( 'Enter your email address...', 'multi-rating-pro' ); ?>" id="mrp-email-<?php echo $sequence; ?>" class="name" value="<?php echo esc_attr( $email ); ?>" maxlength="100"></input>
					<span id="email-<?php echo $sequence; ?>-error" class="mrp-error"></span>
				</p>
				<?php
			}
			
			// Show comment textarea
			if ( $show_comment_textarea == true && $comment_id == null ) {
				?>
				<p class="mrp review-field">
					<label for="comment-<?php echo $sequence; ?>" class="textarea-label"><?php _e( 'Comments', 'multi-rating-pro' ); ?></label><br />
					<textarea rows="5" cols="50" name="comment-<?php echo $sequence; ?>" placeholder="<?php _e( 'Enter comments...', 'multi-rating-pro' ); ?>" id="mrp-comment-<?php echo $sequence; ?>" class="comments" maxlength="2000"><?php echo esc_textarea( $comment ); ?></textarea>
					<span id="comment-<?php echo $sequence; ?>-error" class="mrp-error"></span>
				</p>
				<?php
			}
			
			do_action( 'mrp_rating_form_before_custom_fields', $post_id, $rating_form_id, $custom_fields );
			
			foreach ( (array) $custom_fields as $custom_field ) {
			
				$custom_field_id = $custom_field['custom_field_id'];
				$label = $custom_field['label'];
				$required = $custom_field['required'];
				$max_length = $custom_field['max_length'];
				$type = $custom_field['type'];
				$placeholder = $custom_field['placeholder'];
				$element_id = 'custom-field-' . $custom_field_id . '-' . $sequence;
			
				$value = '';
				if ( isset( $custom_field_values[$custom_field_id] ) ) {
					$value = $custom_field_values[$custom_field_id];
				}
				
				mrp_get_template_part( 'rating-form', 'custom-fields', true, array(
						'custom_field_id' => $custom_field_id,
						'label' => $label,
						'required' => $required,
						'max_length' => $max_length,
						'type' => $type,
						'placeholder' => $placeholder,
						'value' => $value,
						'element_id' => $element_id
				) );
				
				?>
				<!-- hidden field to get custom field id -->
				<input type="hidden" value="<?php echo $custom_field_id; ?>" class="custom-field-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" id="hidden-custom-field-id-<?php echo $custom_field_id; ?>" />
				<?php 
			}
			
			// If a rating entry exists, show a message. Messages will appear here after form submission too.
			if ( $rating_entry_id != null && $show_status_message == true ) {
				if ( $entry_status != 'approved' ) {
					?>
					<p class="message mrp"><?php echo esc_html( $rating_awaiting_moderation_message ); ?></p>
					<?php
				} else {
					?>
					<p class="message mrp"><?php echo esc_html( $existing_rating_message ); ?></p>
					<?php
				}
			}
			
			do_action( 'mrp_rating_form_before_buttons' );
			
			$button_text = $submit_button_text;
			if ( $rating_entry_id != null ) {
				$button_text = $update_button_text;
				?>
				<input type="hidden" value="<?php echo $rating_entry_id; ?>" id="ratingEntryId-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" />
				<input type="button" class="btn btn-default delete-rating"  id="deleteBtn-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" value="<?php echo esc_attr( $delete_button_text ); ?>"></input>
				<?php
			}
			
			?>
			<input type="button" class="btn btn-default save-rating" id="saveBtn-<?php echo $rating_form_id; ?>-<?php echo $post_id; ?>-<?php echo $sequence; ?>" value="<?php echo esc_attr( $button_text ); ?>"></input>
			<input type="hidden" name="sequence" value="<?php echo $sequence; ?>" />
	
			<?php 
			do_action( 'mrp_rating_form_after_buttons' );
			?>
		</form>
		<?php
	}
	
	?>
</div>
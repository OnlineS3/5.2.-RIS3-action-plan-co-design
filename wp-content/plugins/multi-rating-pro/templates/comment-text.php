<div class="mrp-comment-text" <?php if ( $generate_microdata ) { echo 'itemscope itemtype="http://schema.org/Review"'; } ?>>
	<?php

	do_action( 'mrp_comment_text_before_rating' );

	if ( $show_title && strlen( $title ) > 0 ){
		?><p class="mrp mrp-title" <?php if ( $generate_microdata ) { echo 'itemprop="name"'; } ?>><?php echo $title; ?></p><?php
	}

	/*
	 * Comment text here
	 */
	if ( $generate_microdata ) {
		echo '<span itemprop="reviewBody">';
	}
	echo $comment_text;
	if ( $generate_microdata ) {
		echo '</span>';
	}

	if ( $show_overall_rating && $rating_result ) {
		?>
		<p class="mrp">
			<?php
			mrp_get_template_part( 'rating-result', null, true, array(
				'ignore_count' => true,
				'generate_microdata' => false,
				'show_title' => false,
				'show_date' => false,
				'show_count' => false,
				'result_type' => $result_type,
				'class' => $class . ' mrp-comment-rating-result',
				'rating_result' => $rating_result,
				'post_id' => $post_id,
				'rating_form_id' => $rating_form_id,
				'preserve_max_option' => false,
				'generate_microdata' => $generate_microdata,
				'is_review' => true
			) );
			?>
		</p>
		<?php
	}

	if ( $show_rating_items ) {
		foreach ( $rating_item_values as $rating_item_id => $value ) {

			if ( $value != -1 ) { // only show if applicable
				$rating_item = $rating_items[$rating_item_id];

				$type = $rating_item['type'];
				$max_option_value = $rating_item['max_option_value'];
				$description = $rating_item['description'];
				?>

				<p class="mrp rating-item-result">
					<label class="description"><?php echo esc_html( $description ); ?></label>

					<?php
					if ( $type == 'star_rating' ) {

						$template_part_name = 'star-rating';
						if ( $use_custom_star_images ) {
							$template_part_name = 'custom-star-images';
						}

						mrp_get_template_part( 'rating-result', $template_part_name, true, array(
							'max_stars' => $max_option_value,
							'star_result' => $value
						) );

					} else if ( $type == 'thumbs' ) {

						mrp_get_template_part( 'thumbs-value', null, true, array(
							'value' => $value
						) );

					} else {

						$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
						$value_text = isset( $option_value_text_lookup[$value] ) ? $option_value_text_lookup[$value] : $value;
						$value_text = apply_filters( 'mrp_value_text', $value_text, $type, $value, $max_option_value );

						?>
						<span class="value-text"><?php echo esc_html( $value_text ); ?></span>
						<?php

					}
					?>
				</p>
				<?php
			}
		}
	}

	if ( $show_custom_fields ) {
		foreach ( $custom_field_values as $custom_field_id => $value ) {

			mrp_get_template_part( 'custom-field-value', null, true, array(
				'custom_field' => $custom_fields[$custom_field_id],
				'value_text' => $value
		 	) );
		}
	}

	do_action( 'mrp_comment_text_after_rating' );

	if ( $generate_microdata ) {
		$post_obj = get_post( $post_id );

		echo '<span itemprop="itemReviewed" itemscope itemtype="http://schema.org/Thing">'
				. '<meta itemprop="name" content="' . $post_obj->post_title . '" />'
				. '</span>';

		echo '<meta itemprop="datePublished" content="' . mysql2date( 'Y-m-d', $entry_date ) . '" />';

		echo '<span itemprop="author" itemscope itemtype="http://schema.org/Person">'
				. '<meta itemprop="name" content="' . $name . '" />'
				. '</span>';
	}

	?>
</div>

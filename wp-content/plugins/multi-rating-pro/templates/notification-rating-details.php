<?php 
/**
 * Rating details used for e-mail notifications
 */

/**
 * Title
 */
if ( isset( $title ) && strlen( $title ) > 0 ) {
	echo '<br />' . __( 'Title: ', 'multi-rating-pro' ) . $title;
}
					
/**
 * Comment
 */
if ( isset( $comment ) && strlen( $comment ) > 0 ) {
	echo '<br />' . __( 'Comment: ', 'multi-rating-pro' ) . wp_kses_post( nl2br( $comment ) );
}

/**
 * Overall Rating
 */
echo '<br />' . __( 'Overall Rating: ', 'multi-rating-pro' );

if ( $result_type == MRP_Multi_Rating::SCORE_RESULT_TYPE ) {
		
	mrp_get_template_part( 'rating-result', 'score', true, array( 'rating_result' => $rating_result ) );
		
} else if ( $result_type == MRP_Multi_Rating::PERCENTAGE_RESULT_TYPE ) {
		
	mrp_get_template_part( 'rating-result', 'percentage', true, array( 'rating_result' => $rating_result ) );
		
} else {
	
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$max_stars = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];
	$star_result = $rating_result['adjusted_star_result'];
	
	?>
	<span class="star-result">
		<?php 
		$out_of_text = apply_filters( 'mrp_out_of_text', '/' );
		echo $star_result . esc_html( $out_of_text ) . $max_stars; 
		?>
	</span>
	<?php
}


/**
 * Rating Items
 */
foreach ( $rating_item_values as $rating_item_id => $value ) {
		
	if ( $value != -1 ) { // only show if applicable		
		$rating_item = $rating_items[$rating_item_id];
		?>
		<br />
		
		<label class="description"><?php echo esc_html( $rating_item['description'] ); ?>:</label>
			
		<span class="rating-result">
			<?php 

			$type = $rating_item['type'];
			$max_option_value = $rating_item['max_option_value'];
						
			if ( $type == 'star_rating' ) {
					
				$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
				$max_stars = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];
				?>
				<span class="star-result">
					<?php 
					$out_of_text = apply_filters( 'mrp_out_of_text', '/' );
					echo $value . esc_html( $out_of_text ) . $max_stars; 
					?>
				</span>
				<?php
			} else { // select, radio or thumbs
				
				$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
				$value_text = isset( $option_value_text_lookup[$value] ) ? $option_value_text_lookup[$value] : $value;
				$value_text = apply_filters( 'mrp_value_text', $value_text, $type, $value, $max_option_value );
				
				?>
				<span class="value-text"><?php echo esc_html( $value_text ); ?></span>
				<?php
			}
			?>
		</span>
		<?php
	}
}
				
/**
 * Custom fields
 */
foreach ( $custom_field_values as $custom_field_id => $value_text ) {
	
	$custom_field =  $custom_fields[$custom_field_id];
	?>
	<br />
	
	<label class="description"><?php echo esc_html( $custom_field['label'] ); ?>:</label>
	
	<?php 
	if ( $custom_field['type'] === 'textarea' ) {
		$value_text = nl2br( $value_text );
		?><br /><?php
	}
	
	$value_text = apply_filters( 'mrp_value_text',  $value_text, $custom_field['type'] );
	?>
	<span class="value-text"><?php echo $value_text; ?></span>
	<?php
}
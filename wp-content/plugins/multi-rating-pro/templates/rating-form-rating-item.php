<?php 
$allow_not_applicable = isset( $allow_not_applicable ) && $allow_not_applicable;
$is_not_applicable =  isset( $is_not_applicable ) && $is_not_applicable;

/**
 * Rating form rating item template
 */
?>
<p class="rating-item mrp <?php if ( isset( $class ) ) { echo esc_attr( $class ); } ?>" <?php if ( isset( $style ) ) { echo 'style="' . esc_attr( $style ) . '"'; } ?>>
	<?php 
	if ( ! isset( $show_rating_item_label ) || ( isset( $show_rating_item_label ) && $show_rating_item_label ) ) { ?>
		<label class="description" for="<?php echo $element_id; ?>"><?php echo esc_html( $description ); ?></label>
	<?php } ?>
			
	<?php
	if ( $rating_item_type == "star_rating" ) {
		
		$template_part_name = 'star-rating';
		if ( $use_custom_star_images ) {
			$template_part_name = 'custom-star-images';
		}
		
		mrp_get_template_part( 'rating-form', $template_part_name, true, array(
			'max_option_value' => $max_option_value,
			'default_option_value' => $default_option_value,
			'element_id' => $element_id,
			'rating_item_type' => $rating_item_type,
			'option_value_text_lookup' => $option_value_text_lookup,
			'required' => $required
		) );
		
	} else if ( $rating_item_type == 'thumbs' ) {
		
		if ( $default_option_value < 0 || $default_option_value > 1 ) {
			$default_option_value = 1;
		}
			
		mrp_get_template_part( 'rating-form', 'thumbs', true, array(
			'default_option_value' => $default_option_value,
			'element_id' => $element_id,
			'rating_item_type' => $rating_item_type,
			'option_value_text_lookup' => $option_value_text_lookup
		) );
		
	} else if ( $rating_item_type == 'select' ){
		
		mrp_get_template_part( 'rating-form', 'select', true, array(
			'element_id' => $element_id,
			'max_option_value' => $max_option_value,
			'default_option_value' => $default_option_value,
			'option_value_text_lookup' => $option_value_text_lookup,
			'required' => $required,
			'rating_item_type' => $rating_item_type,
			'only_show_text_options' => $only_show_text_options
		) );
	
	} else { // radio
			
		mrp_get_template_part( 'rating-form', 'radio', true, array(
			'default_option_value' => $default_option_value,
			'element_id' => $element_id,
			'max_option_value' => $max_option_value,
			'option_value_text_lookup' => $option_value_text_lookup,
			'required' => $required,
			'rating_item_type' => $rating_item_type,
			'only_show_text_options' => $only_show_text_options
		) );
	}	
	
	if ( $allow_not_applicable ) {
		?>
		<input type="checkbox" name="<?php echo $element_id; ?>-not-applicable" class="mrp-not-applicable" <?php checked( $is_not_applicable ); ?> /><label><?php _e( 'Not applicable', 'multi-rating-pro' ); ?></label>
		<?php	
	}
	?>
	
	<span id="<?php echo $element_id; ?>-error" class="mrp-error"></span>
</p>
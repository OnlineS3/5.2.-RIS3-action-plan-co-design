<?php
/**
 * Rating form template for select dropdowns
 */
?>
<select name="<?php echo $element_id; ?>" id="<?php echo $element_id; ?>">
	
	<?php
	// option values
	$index = 0;
	if ( $required == true && ! $only_show_text_options ) {
		$index = 1;
	}

	for ( $index; $index <= $max_option_value; $index++ ) {
			
		if ( $only_show_text_options && ! isset( $option_value_text_lookup[$index] ) ) {
			continue;
		}
		
		$is_selected = false;
		if ( $default_option_value == $index ) {
			$is_selected = true;
		}
			
		$option_value_text = $index;
		if ( isset( $option_value_text_lookup[$index] ) ) {
			$option_value_text = $option_value_text_lookup[$index];
		}
		
		$option_value_text = apply_filters( 'mrp_option_value_text', $option_value_text, $rating_item_type, $index, $max_option_value, $option_value_text_lookup );
			
		?>
		<option value="<?php echo $index; ?>"<?php
			
			if ( $is_selected ) {
				?> selected="selected"<?php
			}
			
		?>><?php echo esc_html( $option_value_text ); ?></option>
		<?php
	} ?>
</select>
<?php 
/**
 * Rating form template for star rating
 */

$include_minus = apply_filters( 'mrp_rating_form_include_minus', true);
?>

<span class="mrp-star-rating mrp-star-rating-select"><?php
	
	// add star icons
	$index = 0;
	for ( $index; $index <= $max_option_value; $index++ ) {
		
		if ( $index == 0 ) {
			
			if ( $required == false && isset( $icon_classes['minus'] ) ) {
				$class = $icon_classes['minus'] . ' index-' . $index . '-' . $element_id;
				
				?>
				<i <?php if ( ! $include_minus ) { echo 'style="display: none"'; } ?> id="index-<?php echo $index; ?>-<?php echo $element_id; ?>" class="<?php echo $class; ?>"></i>
				<?php
			}
			
			continue;
		}
		
		$class = $icon_classes['star_full'];

		// if default value is less than the current index, it must be empty
		if ( $default_option_value < $index ) {
			$class = $icon_classes['star_empty'];
		}
		
		$id = 'index-' . $index . '-' . $element_id;
		$class .= ' index-' . $index . '-' . $element_id;
			
		$option_value_text = $index;
		if ( isset( $option_value_text_lookup[$index] ) ) {
			$option_value_text = $option_value_text_lookup[$index];
		}
		
		$option_value_text = apply_filters( 'mrp_option_value_text', $option_value_text, $rating_item_type, $index, $max_option_value, $option_value_text_lookup );
		
		?>
		<i title="<?php echo esc_attr( $option_value_text ); ?>" id="<?php echo $id; ?>" class="<?php echo $class; ?>"></i>
		<?php
	}
?>
</span>	
			
<!-- hidden field for storing selected star rating value -->
<input type="hidden" name="<?php echo $element_id; ?>" id="<?php echo $element_id; ?>" value="<?php echo $default_option_value; ?>">
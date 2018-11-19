<?php 
/**
 * Rating form template for custom fields
 */
?>
<p class="mrp custom-field <?php if ( isset( $class ) ) { echo esc_attr( $class ); } ?>" <?php if ( isset( $style ) ) { echo 'style="' . esc_attr( $style ) . '"'; } ?>>

	<label for="<?php echo $element_id; ?>"><?php echo esc_html( $label ); ?></label><br />
	
	<?php
	
	$required_html = $required ? 'required' : '';
	
	if ( $type == 'input' ) {
		?>
		<input type="text" name="<?php echo $element_id; ?>" size="30" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="<?php echo $element_id; ?>" value="<?php echo esc_attr( $value ); ?>" maxlength="<?php echo $max_length; ?>" <?php echo $required_html; ?>></input>
		<?php
	} else if ( $type == 'textarea' ) {
		?>
		<textarea rows="5" cols="50" name="<?php echo $element_id; ?>" placeholder="<?php echo esc_attr( $placeholder ); ?>" id="<?php echo $element_id; ?>" maxlength="<?php echo $max_length; ?>" <?php echo $required_html; ?>><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}
	
	?>
	<span id="<?php echo $element_id; ?>-error" class="mrp-error"></span>
	<?php
	
	do_action( 'mrp_rating_form_custom_field',  $element_id, $placeholder, $value, $max_length, $required );
?>
</p>
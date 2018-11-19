<p class="mrp custom-field">
	<label class="description"><?php echo esc_html( $custom_field['label'] ); ?></label>
	
	<?php if ( $custom_field['type'] === 'textarea' ) {
		$value_text = nl2br( $value_text );
		?><br /><?php
	}
	
	$value_text = apply_filters( 'mrp_value_text',  $value_text, $custom_field['type'] );
	?>
	<span class="value-text"><?php echo $value_text; ?></span>
</p>
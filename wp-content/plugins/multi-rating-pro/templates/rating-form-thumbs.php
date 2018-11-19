<?php
/**
 * Rating form template for thumbs up/thumbs down or like/dislike
 */
?>

<span class="mrp-thumbs mrp-thumbs-select">
	<?php
	$thumbs_down_class = $icon_classes['thumbs_down_on'];
	if ( $default_option_value != 0 ) {
		$thumbs_down_class = $icon_classes['thumbs_down_off'];
	}

	$thumbs_down_title = '';
	if ( isset( $option_value_text_lookup[0] ) ) {
		$thumbs_down_title = $option_value_text_lookup[0];
	}
	$thumbs_down_title = apply_filters( 'mrp_option_value_text', $thumbs_down_title, $rating_item_type, 0, 1, $option_value_text_lookup );


	do_action( 'mrp_rating_form_before_thumbs_down' );

	?>
	<i title="<?php echo esc_attr( $thumbs_down_title ); ?>" id="index-0-<?php echo $element_id; ?>" class="<?php echo $thumbs_down_class; ?> index-0-<?php echo $element_id; ?>"></i>
	<?php

	$thumbs_up_class = $icon_classes['thumbs_up_on'];
	if ( $default_option_value != 1 && $default_option_value == 0 ) {
		$thumbs_up_class = $icon_classes['thumbs_up_off'];
	}

	$thumbs_up_title = '';
	if ( isset( $option_value_text_lookup[1] ) ) {
		$thumbs_up_title = $option_value_text_lookup[1];
	}
	$thumbs_up_title = apply_filters( 'mrp_option_value_text', $thumbs_up_title, $rating_item_type, 1, 1, $option_value_text_lookup );

	do_action( 'mrp_rating_form_before_thumbs_up' );
	?>

	<i title="<?php echo esc_attr( $thumbs_up_title ); ?>" id="index-1-<?php echo $element_id; ?>" class="<?php echo $thumbs_up_class; ?> index-1-<?php echo $element_id; ?>"></i>

	<!-- hidden field for storing selected star rating value -->
	<input type="hidden" name="<?php echo $element_id; ?>" id="<?php echo $element_id; ?>" value="<?php echo $default_option_value; ?>">

</span>

<?php
/**
 * Rating result score rating template
 */
$generate_microdata = isset( $generate_microdata ) && $generate_microdata;
?>
<span class="score-result">
	<?php
	$out_of_text = apply_filters( 'mrp_out_of_text', '/' );

	if ( $generate_microdata ) {
		echo '<span itemprop="ratingValue">';
	}
	echo mrp_number_format( $rating_result['adjusted_score_result'] );
	if ( $generate_microdata ) {
		echo '</span>';
	}

	echo esc_html( $out_of_text );

	if ( $generate_microdata ) {
		echo '<span itemprop="bestRating">';
	}
	echo mrp_number_format( $rating_result['total_max_option_value'] );
	if ( $generate_microdata ) {
		echo '</span>';
	}
	?>
</span>

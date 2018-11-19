<?php 
/**
 * Comment form include rating checkbox template
 */
?>
<p class="mrp">
	<input type="checkbox" value="true" name="include-rating" id="include-rating" <?php if ( isset( $checked ) ) { echo $checked; } ?>/>
	<label for="include-rating"><?php _e( 'Include rating', 'multi-rating-pro' ); ?></label>
</p>
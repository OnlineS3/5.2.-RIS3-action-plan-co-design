<?php 
/**
 * Rating result custom star images template
 */
$generate_microdata = isset( $generate_microdata ) && $generate_microdata;
?>
<span class="mrp-star-rating">
	
	<?php
    $index = 0;			
    for ( $index; $index < $max_stars; $index++ ) {
    		
    	$class = 'mrp-custom-full-star';
    	
    	if ( $star_result < $index+1 ) {
    	    
    		$diff = $star_result - $index;
    		
    		if ( $diff > 0 ) {
    			
    			if ( $diff >= 0.3 && $diff <= 0.7 ) {
    				$class ='mrp-custom-half-star';
    			} else if ( $diff < 0.3 ) {
    				$class = 'mrp-custom-empty-star';
    			} else {
    				$class = 'mrp-custom-full-star';
    			}
    			
    		} else {
    			$class = 'mrp-custom-empty-star';
    		}
    	
    	} else {
    		$class = 'mrp-custom-full-star';
    	}
    	
    	?>
    	<span class="<?php echo $class; ?>" width="<?php echo $image_width; ?>px" height="<?php echo $image_height; ?>px"></span>
    	<?php
   	} 			
?>
</span>

<span class="star-result">
	<?php
	$out_of_text = apply_filters( 'mrp_out_of_text', '/' );
	
	if ( $generate_microdata ) {
		echo '<span itemprop="ratingValue">';
	}
	echo $star_result;
	if ( $generate_microdata ) {
		echo '</span>';
	}
	
	echo esc_html( $out_of_text );
	
	if ( $generate_microdata ) {
		echo '<span itemprop="bestRating">';
	}
	echo $max_stars;
	if ( $generate_microdata ) {
		echo '</span>';
	}
	?>
</span>
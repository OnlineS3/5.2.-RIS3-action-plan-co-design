<?php 
/**
 * Rating Item Results template
 */
?>
		
<div class="rating-item-results <?php if ( isset( $class ) ) { echo esc_attr( $class ); } ?>" <?php if ( isset( $style ) ) { echo 'style="' . esc_attr( $class ) . '"'; } ?>>

	<?php 
	if ( $count_entries == 0 ) {
		
		$no_rating_results_text = apply_filters( 'mrp_no_rating_results_text', $no_rating_results_text, $post_id );
		
		?>
		<p class="mrp"><?php echo esc_html( $no_rating_results_text ); ?></p>
		<?php
	} else {

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];
		
		?>
		<table>
			<?php
			$rank = 1;
			foreach ( $rating_item_results as $rating_item_result ) {
				$rating_result = $rating_item_result['rating_result'];
				$rating_item = $rating_item_result['rating_item'];
				$option_value_text_lookup = $rating_item_result['option_value_text_lookup'];
				$count_entries = $rating_result['count_entries'];
						
				?>
				<tr class="rating-item-result <?php echo $layout; ?>">
				<?php
					
				if ( $layout == 'options_block' ) {
					?>
					<td colspan="3">
					<?php
				} else {
					if ( $show_rank ) {
						?><td><span class="rank"><?php echo $rank++; ?></span></td><?php
					}
					?>
					<td>
					<?php
				}
				?>
				<label class="description"><?php echo esc_html( $rating_item['description'] ); ?></label>
				</td>

				<?php
				if ( $layout == 'options_block' ) {
					?>
					</tr>
					<?php
				}
				
				if ( $layout == 'options_block' ) {
					
					foreach ( array_reverse( $rating_result['option_totals'], true ) as $value => $total ) {

						// skip if required is true
						if ( ( isset( $rating_item['required'] ) && $rating_item['required'] == true && $value == 0 ) 
								|| ( isset( $rating_item['only_show_text_options'] ) && $rating_item['only_show_text_options'] == true && ! isset( $option_value_text_lookup[$value] ) ) ) {
							continue;
						}
						
						$percentage = 0;
						if ( $count_entries > 0 ) {
							$percentage = (float) round( ( $total / doubleval( $count_entries ) ) * 100, $decimal_places );
						}
						
						$option_value_text = $value;
						if ( isset( $option_value_text_lookup[$value] ) ) {
							$option_value_text = $option_value_text_lookup[$value];
						}
							
						?>
						<tr class="rating-item-result <?php echo $layout; ?>">	
						
							<td>
								<span class="option">
									<?php 
									if ( $rating_item['type'] == 'thumbs' && $value <= 1 ) {
										?>
										<span class="option thumbs">
											<?php 
											$thumbs_class = $icon_classes['thumbs_up_on'];
											if ( $value == 0 ) {
												$thumbs_class = $icon_classes['thumbs_down_on'];
											}	
											?>
											<i class="<?php echo $thumbs_class; ?>"></i> 
										</span><?php
									}
									
									echo esc_html( $option_value_text ); ?></span>
							</td>
							
							<td>
								<span class="mrp-counter-back">
									<span class="mrp-counter-bar" style="width: <?php echo $percentage; ?>%; background-color: <?php echo $star_rating_colour; ?>;"></span>
								</span>
							</td>

							<td>
								<span class="total">
								<?php
								if ( $result_type == 'percentage' ) {
									echo $percentage . '%';
								} else {
									echo $total;
								}
								?>
								</span>
							</td>
						
						</tr>
						<?php
					}
				} else if ( $layout == 'options_inline' ) {
					?>
					
					<td>
					<?php
					foreach ( $rating_result['option_totals'] as $value => $total ) {
						
						// skip if required is true
						if ( ( isset( $rating_item['required']) && $rating_item['required'] == true && $value == 0 ) 
								|| ( isset( $rating_item['only_show_text_options'] ) && $rating_item['only_show_text_options'] == true && ! isset( $option_value_text_lookup[$value] ) ) ) {
							continue;
						}
						
						$percentage = 0;
						if ( $count_entries > 0 ) {
							$percentage = (float) round( ( $total / doubleval( $count_entries ) ), $decimal_places ) * 100;
						}
								
						$option_value_text = $value;
						if ( isset( $option_value_text_lookup[$value] ) ) {
							$option_value_text = $option_value_text_lookup[$value];
						}
							
						?>
						<span class="option"><?php
						
							if ( $rating_item['type'] == 'thumbs' && $value <= 1 ) {
								
								$thumbs_class = $icon_classes['thumbs_up_on'];
								if ( $value == 0 ) {
									$thumbs_class = $icon_classes['thumbs_down_on'];
								}	
								?>
								<i class="<?php echo $thumbs_class; ?>"></i>
								<?php
							}
	
							echo esc_html( $option_value_text ); ?></span>
							
							<span class="total">&nbsp;(<?php
							if ( $result_type == 'percentage' ) {
								echo $percentage . '%';
							} else {
								echo $total;
							}
							
						?>)</span>
						<?php
					}
					?>
					</td>
					<?php
				} else {
					?>
					<td>
						<?php 
						if ( $count_entries > 0 ) {
							mrp_get_template_part( 'rating-result', null, true, array(
								'no_rating_results_text' => '',
								'ignore_count' => true,
								'generate_microdata' => false,
								'show_title' => false,
								'show_date' => false,
								'show_count' => $show_count,
								'before_count' => $before_count,
								'after_count' => $after_count,
								'result_type' => $result_type,
								'class' => $class . ' rating-item-result-' . $rating_form_id . '-' . $post_id,
								'rating_result' => $rating_result,
								'post_id' => $post_id,
								'rating_form_id' => $rating_form_id,
								'preserve_max_option' => $preserve_max_option,
								'before_date' => '',
								'after_date' => ''
							) );
						} else {
							echo $no_rating_results_text;
						}
						?>
					</td>
					<?php
				}
			} ?>
		</table>
		<?php
	} ?>
</div>
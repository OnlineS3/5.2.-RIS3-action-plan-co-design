<div class="user-ratings-dashboard <?php if ( isset( $class ) ) { echo esc_attr( $class ); } ?>">

	<?php
	if ( ! empty ( $title ) ) {
		
		$before_title = apply_filters( 'mrp_user_ratings_dashboard_before_title', $before_title );
		$after_title = apply_filters( 'mrp_user_ratings_dashboard_after_title', $after_title );
		
		echo "$before_title" . esc_html( $title ) . "$after_title";
	}
	
	if ( $user_id == 0 ) {
		?>
		<p class="mrp"><?php _e( 'Please login to view your ratings.', 'multi-rating-pro' ) ; ?></p>
		<?php
	} else {
		if ( $show_filter == true && $taxonomy ) {
			?>
			<form action="" class="mrp-filter" method="POST">
				
				<label for="term-id"><?php echo esc_html( $filter_label_text ); ?></label>
				<select id="term-id" name="term-id" class="term-id">
				
				<?php
				$selected = '';
				if ( $term_id == 0) {
					$selected = 'selected="selected"';
				}
				?>
				
				<option value="" <?php echo $selected; ?>><?php _e( 'All', 'multi-rating-pro' ); ?></option>
				
				<?php
				$terms = get_terms( $taxonomy );
				foreach ( $terms  as $current_term ) {
					$selected = '';
					if ( $current_term->term_id === $term_id ) {
						$selected = 'selected="selected"';
					}
					?>
					
					<option value="<?php echo esc_attr( $current_term->term_id ); ?>" <?php echo $selected; ?>><?php echo esc_html( $current_term->name ); ?></option>
					<?php
				}
				?>
				</select>
				
				<input type="submit" value="<?php echo esc_attr( $filter_button_text ); ?>" />
			</form>
		<?php
		}
		
		?>
		<table>
			<tbody>
				<tr>
					<th scope="col"><?php _e( 'Average Rating', 'multi-rating-pro' ); ?></th>
					<td><?php 
					
						if ( $count_entries != 0 ) {
							$template_part_name = 'star-rating';
							if ( $use_custom_star_images ) {
								$template_part_name = 'custom-star-images';
							}
							
							mrp_get_template_part( 'rating-result', $template_part_name, true, array( 
								'max_stars' => 5, 
								'star_result' => $avg_rating_result,
								'no_rating_results_text' => $no_rating_results_text
							) );
						}
						?>
					</td>
				</tr>
				<tr>
					<th scope="col"><?php _e( 'Ratings Count', 'multi-rating-pro' ); ?></th>
					<td>
						<?php
						if ( $count_entries  == 0 ) {
							$no_rating_results_text = apply_filters( 'mrp_no_rating_results_text', $no_rating_results_text );
							?>
							<span class="no-rating-results-text"><?php echo esc_html( $no_rating_results_text ); ?></span>
							<?php 
						} else {
							echo $count_entries;
							if ( $count_pending > 0 ) {
								echo ' (' . $count_pending . ' ' . __( 'pending', 'multi-rating-pro' ) . ')';
							}
						}
						?>
					</td>
				</tr>
				<?php if ( $show_count_comments ) { ?>
				<tr>
					<th scope="col"><?php _e( 'Comments Count', 'multi-rating-pro' ); ?></th>
					<td>
						<?php 
						if ( $count_entries != 0 ) {
							echo $count_comments;
						} ?>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<th scope="col"><?php _e( 'Most Recent Entry', 'multi-rating-pro' ); ?></th>
					<td>
						<?php
						if ( $most_recent_date != null ) {
							?>
							<span class="entry-date"><?php echo "$before_date" . mysql2date( get_option( 'date_format' ) . ', ' . get_option( 'time_format' ), $most_recent_date ) . "$after_date"; ?></span>
							<?php
						}
						?>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="user-ratings-dashboard-list">
			<table>
				<tr>
					<th><?php _e( 'Title', 'multi-rating-pro' ); ?></th>
					<th><?php _e( 'Rating', 'multi-rating-pro' ); ?></th>
					<?php 
					if ( $show_date == true ) {
						?>
						<th><?php _e( 'Date', 'multi-rating-pro' ); ?></th>
						<?php
					} 
					?>
					<?php 
					if ( $show_entry_status == true ) {
						?>
						<th><?php _e( 'Status', 'multi-rating-pro' ); ?></th>
						<?php
					} 
					?>
					<?php 
					if ( $user_can_update_delete == true ) {
						?>
						<th><?php _e( 'Actions', 'multi-rating-pro' ); ?></th>
						<?php
					} 
					?>
				</tr>
				
				<?php
				
				if ( $count_entries == 0 ) {
					
					$colspan = 2;
					if ( $user_can_update_delete ) {
						$colspan++;
					}
					if ( $show_entry_status ) {
						$colspan++;
					}
					if ( $show_date ) {
						$colspan++;
					}

					?>
					<tr><td colspan="<?php echo $colspan; ?>"><?php echo $no_rating_results_text; ?></td></tr>
					<?php
				} else {
					
					$index = 1;
						
					foreach ( $rating_results as $rating_entry_result ) {
						
						$post_id = $rating_entry_result['post_id'];
						$rating_form_id = $rating_entry_result['rating_form_id'];
						$post_obj = get_post( $post_id );
			
						$sequence = MRP_Utils::$sequence++;
						$row_id = $rating_form_id . '-' . $post_id . '-' . $sequence;
						
						?>
						<tr class="rating-info-<?php echo $row_id; ?>">
							<?php
							
							do_action( 'mrp_rating_results_list_row_before_first_td', $post_id, $rating_entry_result );
							
							?>
							<td>
								<a class="title" href="<?php echo esc_attr( get_the_permalink( $post_id ) ); ?>"><?php echo esc_html( $post_obj->post_title ); ?></a>
							</td>
							
							<td>
								<?php
								mrp_get_template_part( 'rating-result', null, true, array(
									'no_rating_results_text' => '',
									'ignore_count' => true,
									'generate_microdata' => false,
									'show_title' => false,
									'before_title' => $before_title,
									'after_title' => $after_title,
									'show_date' => false,
									'show_count' => $show_count,
									'result_type' => $result_type,
									'class' => $class . ' rating-result-list-' . $rating_form_id . '-' . $post_id,
									'rating_result' => $rating_entry_result['rating_result'],
									'before_count' => $before_count,
									'after_count' => $after_count,
									'post_id' => $post_id,
									'rating_form_id' => $rating_form_id,
									'preserve_max_option' => false,
									'before_date' => $before_date,
									'after_date' => $after_date,
									'icon_classes' => $icon_classes,
									'use_custom_star_images' => $use_custom_star_images,
									'image_width' => $image_width,
									'image_height' => $image_height
								) );
								?>
							</td>
							
							<?php
							if ( isset( $show_date ) && $show_date == true && isset( $rating_entry_result['entry_date'] ) ) {
								?>
								<td>
									<span class="entry-date"><?php echo "$before_date" . mysql2date( get_option( 'date_format' ), $rating_entry_result['entry_date'] ) . "$after_date"; ?></span>
								</td>
								<?php
							}
							
							if ( $show_entry_status ) {
								$status_text = ( $rating_entry_result['entry_status'] == 'approved' ) ? __( 'Approved', 'multi-rating-pro' ) : __( 'Pending', 'multi-rating-pro' );
								?>
								<td>
									<span class="entry-status"><?php echo $status_text; ?></span>
								</td>
								<?php
							}
							
							if ( $user_can_update_delete == true ) {
								?>
								<td class="rating-actions">
									<a href="" id="edit-<?php echo $row_id; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a>
									<a style="display: none;" href="" id="cancel-<?php echo $row_id; ?>"><?php _e( 'Cancel', 'multi-rating-pro' ); ?></a>
								</td>
								<?php
							}
							
							do_action( 'mrp_rating_results_list_row_after_last_td', $post_id, $rating_entry_result );
							
							?>
						</tr>
						<?php
			
						$index++;
					}
				}
					
				?>
			</table>
		</div>
	<?php 
	}
	?>
</div>
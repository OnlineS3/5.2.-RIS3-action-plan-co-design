<?php 
/**
 * Rating entry details list template
 */
$generate_microdata = isset( $generate_microdata ) && $generate_microdata;
?>
<div class="rating-entry-details-list <?php if ( isset( $class ) ) { echo esc_attr( $class ); } ?>">

	<form action="" class="mrp-filter" method="POST">

		<?php
		if ( ! empty( $title ) ) {
			
			$before_title = apply_filters( 'mrp_entry_result_list_before_title', $before_title );
			$after_title = apply_filters( 'mrp_entry_result_list_after_title', $after_title );
			
			echo "$before_title" . esc_html( $title ) . "$after_title";
		}
		
		if ( $show_filter == true && $taxonomy ) {	
			?>			
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
			<?php
		}
		
		if ( count( $rating_entry_result_list ) == 0 ) {
			$no_rating_results_text = apply_filters( 'mrp_no_rating_results_text', $no_rating_results_text );
			?>
			<p class="mrp"><?php echo esc_html( $no_rating_results_text ); ?></p>
			<?php 
		} else {
			
			if ( $layout == 'table' ) {
				?>
				<table class="rating-entry-details-list-inner">
				<?php
			} else {
				?>
				<div class="rating-entry-details-list-inner">
				<?php
			}
			
			foreach ( $rating_entry_result_list as $rating_entry ) {

				mrp_get_template_part( 'rating-entry-details', null, true, array(
						'generate_microdata' => $generate_microdata,
						'result_type' => $result_type,
						'class' => $class,
						'rating_entry' => $rating_entry,
						'layout' => $layout,
						'show_title' => $show_title,
						'show_comment' => $show_comment,
						'before_comment' => $before_comment,
						'after_comment' => $after_comment,
						'show_overall_rating' => $show_overall_rating,
						'show_rating_items' => $show_rating_items,
						'show_custom_fields' => $show_custom_fields,
						'show_permalink' => $show_permalink,
						'show_avatar' => $show_avatar,
						'show_name' => $show_name,
						'before_name' => $before_name,
						'after_name' => $after_name,
						'show_date' => $show_date,
						'before_date' => $before_date,
						'after_date' => $after_date,
						'add_author_link' => $add_author_link,
						'rating_items' => $rating_items,
						'custom_fields' => $custom_fields,
						'show_post' => $show_post,
						'show_permalink' => $show_permalink,
				) );

			}

			?>
			
			<?php
			if ( $layout == 'table' ) {
				if ( $show_load_more ) { ?>
					<tr class="load-more-row">
						<td colspan="2">
							<a href="#" class="load-more" id="load-more-<?php echo $sequence; ?>"><?php _e( 'Load More', 'multi-rating-pro' ); ?></a>
							<input type="hidden" id="params-<?php echo $sequence; ?>" name="params" value="<?php echo esc_attr( json_encode( $params ) ); ?>" />
						</td>
					</tr>
					<?php } ?>
				</table>
				<?php
			} else {
				if ( $show_load_more ) { ?>
					<a href="#" class="load-more" id="load-more-<?php echo $sequence; ?>"><?php _e( 'Load More', 'multi-rating-pro' ); ?></a>
					<input type="hidden" id="params-<?php echo $sequence; ?>" name="params" value="<?php echo esc_attr( json_encode( $params ) ); ?>" />
				<?php } ?>
				</div>
				<?php
			}
		}
		?>
		
		
	
	</form>
</div>
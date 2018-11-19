<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Filters screen
 */
function mrp_filters_screen() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Filters', 'multi-rating-pro' ); ?>
			<a class="add-new-h2" href="#" id="add-filter"><?php _e( 'Add New', 'multi-rating-pro' ); ?></a>
		</h2>
		
		<?php 
		$filters = get_option( 'mrp_filters' );
		if ( ! is_array( $filters ) ) {
			$filters = array();
		}
		
		/* if ( count( $filters ) == 0 ) {
			array_push( $filters, array(
					'filter_type' => 'taxonomy',
					'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
					'taxonomies' => array( 'category' ),
					'terms' => array('') ,
					'post_types' => array(),
					'rating_form_position' => '',
					'rating_results_position' => '',
					'rating_form_id' => '',
					'priority' => 10,
					'override_post_meta' => false
			) );
			
			update_option( 'mrp_filters',  $filters );
		}*/
			
		?>
		<div id="post-body" class="metabox-holder columns-1">
			<div id="postbox-container" class="postbox-container" style="float: none;">
				<div id="poststuff" style="padding-top: 0px !important;">
					<div id="post-body" class="metabox-holder columns-1">
						<div id="postbox-container" class="postbox-container active">
							<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						   		<?php 
						   		$index = 1;
						   		foreach ( $filters as $filter ) {
						   			mrp_display_filter_metabox( $filter, $index++ ); 
						   		}
						   		?>
							</div>
						</div>
					</div>				
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Filter metabox
 */
function mrp_display_filter_metabox( $filter = array(), $index ) {

	$filter_type = $filter['filter_type'];
	$taxonomy = $filter['taxonomy'];
	$terms = $filter['terms'];
	$post_types = $filter['post_types'];
	$post_ids = $filter['post_ids'];
	$page_urls = $filter['page_urls'];
	$filter_name = $filter['filter_name'];
	$rating_form_id = $filter['rating_form_id'];
	$rating_form_position = $filter['rating_form_position'];
	$rating_results_position = $filter['rating_results_position'];
	$priority = $filter['priority'];
	$override_post_meta = $filter['override_post_meta'];
	
	?>
	<div id="filter-<?php echo $index; ?>" class="postbox">
		<div class="handlediv" title="Click to toggle"><br></div>
		
		<h3 class="hndle ui-sortable-handle">
			<span>
				<?php printf( __( 'Filter %d - %s', 'multi-rating-pro' ), $index, $filter_name ); ?>
			</span>
		</h3>
		
		<div class="inside">
			<form method="post" class="filter">
				
				<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
				<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
				
				<table class="form-table">
					<tbody>
						<tr>
							<td style="margin: 0px; padding: 0px; vertical-align: top; padding-right: 5px;">
								<table class="form-table">
									<tbody>
										<tr>
											<th scope="row"><label for="filter-name"><?php _e( 'Name', 'multi-rating-pro' ); ?></label></th>
											<td><input type="text" name="filter-name" id="filter-name" class="regular-text" value="<?php echo $filter_name; ?>" /></td>
										</td>
										<tr class="filter-type-row">
											<th scope="row"><label for="filter-type"><?php _e( 'Filter Type', 'multi-rating-pro' ); ?></label></th>
											<td>
												<select name="filter-type">
													<option value="taxonomy" <?php if ( $filter_type == 'taxonomy' ) { echo 'selected="selected"'; } ?>><?php _e( 'Taxonomy', 'multi-rating-pro' ); ?></option>
													<option value="post-type" <?php if ( $filter_type == 'post-type' ) { echo 'selected="selected"'; } ?>><?php _e( 'Post Type', 'multi-rating-pro' ); ?></option>
													<option value="post-ids" <?php if ( $filter_type == 'post-ids' ) { echo 'selected="selected"'; } ?>><?php _e( 'Post Ids', 'multi-rating-pro' ); ?></option>
													<option value="page-urls" <?php if ( $filter_type == 'page-urls' ) { echo 'selected="selected"'; } ?>><?php _e( 'Page URLs', 'multi-rating-pro' ); ?></option>
												</select>
											</td>
										</tr>
										<?php 
										if ( $filter_type == 'post-type' ) {
											mrp_filter_post_types( $post_types );
										} else if ( $filter_type == 'taxonomy' ) {
											mrp_filter_taxonomy( $taxonomy ); 
											mrp_filter_terms( $terms, $taxonomy );
										} else if ( $filter_type == 'post-ids' ) {
											mrp_filter_post_ids( $post_ids );
										} else {
											mrp_filter_page_urls( $page_urls );
										}
										?>
									</tbody>
								</table>
							</td>
							
							<td style="margin: 0px; padding: 0px; vertical-align: top; padding-left: 5px;">
								<table class="form-table">
									<tbody>
									    <tr>
											<th scope="row">
												<label for="rating-form-id"><?php _e( 'Default Rating Form', 'multi-rating-pro' ); ?></label>
											</th>
											<td>
												<?php mrp_rating_form_select( $rating_form_id ); ?>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<label for="rating-form-position"><?php _e( 'Rating Form Position', 'multi-rating-pro' ); ?></label>
											</th>
											<td>
												<?php mrp_rating_form_position_select( $rating_form_position ); ?>
												<label><?php _e( 'Auto placement position of the rating form.', 'multi-rating-pro' ); ?></label>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<label for="rating-results-position"><?php _e( 'Rating Results Position', 'multi-rating-pro' ); ?></label>
											</th>
											<td>
												<?php mrp_rating_results_position_select( $rating_results_position ); ?>
												<label><?php _e( 'Auto placement position of the rating result.', 'multi-rating-pro' ); ?></label>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<label for="priority"><?php _e( 'Priority', 'multi-rating-pro' ); ?></label>
											</th>
											<td>
												<input name="priority" class="small-text" type="number" value="<?php echo $priority; ?>" />
												<label><?php _e( 'Lower numbers have a higher priority.', 'multi-rating-pro' ); ?></label>
											</td>
										</tr>
										<tr>
											<th scope="row">
												<label for="override-post-meta"><?php _e( 'Override Post Meta', 'multi-rating-pro' ); ?></label>
											</th>
											<td>
												<input name="override-post-meta" type="checkbox" <?php if ( isset( $override_post_meta ) && $override_post_meta ) { echo 'checked="checked"'; } ?> />
												<label><?php _e( 'Override any settings configured in the Multi Rating post meta box.', 'multi-rating-pro' ); ?></label>
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
		     		</tbody>
				</table>
				
				<p>
					<input type="submit" class="button button-primary save-filter-btn" value="<?php _e( 'Save Changes', 'multi-rating-pro' ); ?>">
					<input type="button" class="button button-secondary delete-filter-btn" value="<?php _e( 'Delete Filter', 'multi-rating-pro' ); ?>">
				</p>
		   	</form>
		</div>
	</div>
	<?php
}

/**
 * Filter taxonomies
 *
 * @param array $selected
 * @param array $post_types
 */
function mrp_filter_taxonomy( $selected = '', $post_types = array( 'post', 'page' ) ) {
	?>
	<tr>
		<th scope="row"><label for="taxonony"><?php _e( 'Taxonomy', 'multi-rating-pro' ); ?></label></th>
		<td>
			<?php mrp_taxonomies_select( array( 
					'selected' => $selected,
					'post_types' => $post_types,
					'show_option_all' => false
			 ) ); ?>
		</td>
	</tr>
	<?php 
}

/**
 * Filter terms
 * 
 * @param array selected
 */
function mrp_filter_terms( $selected = array( '' ), $taxonomy = 'category' ) {
	?>
	<tr>
		<th scope="row"><label for="terms"><?php _e( 'Terms', 'multi-rating-pro' ); ?></label></th>
		<td class="filter-terms-col"><?php mrp_terms_checkboxes( array( 
				'selected' => $selected,
				'taxonomy' => $taxonomy
		) ); ?></td>
	</tr>
	<?php
}

/**
 * Filter post types
 */
function mrp_filter_post_types( $selected = array() ) {
	?>
	<tr>
		<th scope="row"><label for="post-types"><?php _e( 'Post Types', 'multi-rating-pro' ); ?></label></th>
		<td>
			<?php mrp_post_types_checkboxes( array( 
					'selected' => $selected 
			) ); ?>
		</td>
	</tr>
	<?php
}

/**
 * Filter post ids
 */
function mrp_filter_post_ids( $post_ids = array() ) {
	?>
	<tr>
		<th scope="row"><label for="post-ids"><?php _e( 'Post Ids', 'multi-rating-pro' ); ?></label></th>
		<td>
			<textarea name="post-ids" class="widefat" rows="3"><?php echo implode( ',', $post_ids ); ?></textarea>
			<p><?php _e( 'Enter the post ids, comma separated', 'multi-rating-pro' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Filter page URLs ids
 */
function mrp_filter_page_urls( $page_urls = array() ) {
	?>
	<tr>
		<th scope="row"><label for="page-urls"><?php _e( 'Page URLs', 'multi-rating-pro' ); ?></label></th>
		<td>
			<textarea name="page-urls" class="widefat" rows="6"><?php echo implode( '&#13;&#10;', $page_urls ); ?></textarea>
			<p><?php _e( 'Enter the page URLs, one per line', 'multi-rating-pro' ); ?></p>
		</td>
	</tr>
	<?php
}

/**
 * Changes filter type
 */
function mrp_change_filter_type() {
	
	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {
	
		$filter_type = $_POST['filterType'];
	
		ob_start();
		
		if ( $filter_type == 'post-type' ) {
			mrp_filter_post_types();
		} else if ( $filter_type == 'taxonomy' ) {
			mrp_filter_taxonomy();
			mrp_filter_terms();
		} else if ( $filter_type == 'post-ids' ) {
			mrp_filter_post_ids();
		} else {
			mrp_filter_page_urls();
		}
	
		$html = ob_get_contents();
		ob_end_clean();
	
		echo json_encode( array(
				'html' => $html,
				'success' => true
		) );
	}
	
	die();
}

/**
 * Saves a filter
 */
function mrp_save_filter() {
	
	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID . '-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {
		
		$post_ids = isset( $_POST['postIds'] ) ? preg_split( '/[,]+/', trim( $_POST['postIds'] ), -1, PREG_SPLIT_NO_EMPTY ) : array();
		$page_urls = isset( $_POST['pageUrls'] ) ? preg_split( '/[\r\n,]+/', trim( $_POST['pageUrls'] ), -1, PREG_SPLIT_NO_EMPTY ) : array();

		foreach ( $page_urls as $index => $page_url ) {
			$page_urls[$index] = MRP_Utils::normalize_url( $page_url );
		}
		
		$filter = array(
				'filter_type' => $_POST['filterType'],
				'filter_name' => $_POST['filterName'],
				'taxonomy' => $_POST['taxonomy'],
				'terms' => isset( $_POST['terms'] ) ? $_POST['terms'] : array(),
				'post_types' => isset( $_POST['postTypes'] ) ? $_POST['postTypes'] : array(),
				'rating_form_position' => isset( $_POST['ratingFormPosition'] ) ? $_POST['ratingFormPosition'] : '',
				'rating_results_position' => isset( $_POST['ratingResultsPosition'] ) ? $_POST['ratingResultsPosition'] : '',
				'rating_form_id' => $_POST['ratingFormId'],
				'priority' => $_POST['priority'],
				'override_post_meta' => isset( $_POST['overridePostMeta'] ) && $_POST['overridePostMeta'] == 'true' ? true : false,
				'post_ids' =>  $post_ids,
				'page_urls' =>  $page_urls
		);
		
		$filters = get_option( 'mrp_filters' );
		if ( ! is_array( $filters ) ) {
			$filters = array();
		}
		
		$index = $_POST['index'];
		
		if ( count( $filters ) == 0) {
			array_push( $filters, $filter );
		} else if ( isset( $filters[$index-1] ) ) {
			$filters[$index-1] = $filter;
		}
		
		update_option( 'mrp_filters',  $filters );
	}
	
	$messages_html = '<div class="updated" style="margin: 10px 0 10px !important; display: block;"><p>' . __( 'Filter saved.', 'multi-rating-pro' ) . '</p></div>';
	
	echo json_encode( array(
			'success' => true,
			'data' => array(
					'name' => sprintf( __( 'Filter %d - %s', 'multi-rating-pro' ), $index, esc_html( $_POST['filterName'] ) ),
					'messages_html' => $messages_html,
					'index' => count( $filters )
	) ) );

	die();
}


/**
 * Deletes a filter
 */
function mrp_delete_filter() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$index = $_POST['index'];

		$filters = get_option( 'mrp_filters' );
		
		if ( isset( $filters ) && is_array( $filters ) && isset( $filters[$index-1] ) ) {	
			
			unset( $filters[$index-1] );
			update_option( 'mrp_filters',  $filters );
					
			echo json_encode( array(
					'success' => true,
			) );
			
		} else {
			echo json_encode( array(
					'success' => false,
			) );
		}
	}

	die();
}


/**
 * Returns HTML for a new filter
 */
function mrp_add_filter() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		ob_start();
		
		$filters = get_option( 'mrp_filters' );
		if ( ! is_array( $filters ) ) {
			$filters = array();
		}
		
		$filter = array(
				'filter_type' => 'post-ids',
				'filter_name' => __( 'Sample Filter', 'multi-rating-pro' ),
				'taxonomy' => 'category',
				'terms' => array() ,
				'post_types' => array(),
				'rating_form_position' => '',
				'rating_results_position' => '',
				'rating_form_id' => '',
				'priority' => 10,
				'override_post_meta' => false,
				'page_urls' => array(),
				'post_ids' => array()
		);
		array_push( $filters, $filter );
		
		update_option( 'mrp_filters',  $filters );
		
		$index = count( $filters );

		mrp_display_filter_metabox( $filter, $index );

		$html = ob_get_contents();
		ob_end_clean();

		echo json_encode( array(
				'html' => $html,
				'data' => array( 'index' => $index )
		) );
	}

	die();
}

/**
 * Gets terms by taxonomy
 */
function mrp_get_terms() {
	
	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' )) {
		
		$taxonomy = $_POST['taxonomy'];
		
		ob_start();
		mrp_terms_checkboxes( array( 
				'selected' => array( '' ),
				'taxonomy' => $taxonomy
		) );
		$html = ob_get_contents();
		ob_end_clean();
		
		echo json_encode( array(
				'html' => $html,
				'success' => true
		) );
	}
	
	die();
}
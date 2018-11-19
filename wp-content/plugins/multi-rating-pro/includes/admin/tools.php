<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the tools screen
 */
function mrp_tools_screen() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Tools', 'multi-rating-pro' ); ?></h2>

		<?php
		if ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_export_ratings' ) ) {
			mrp_export_tool();
		}

		if ( current_user_can( 'mrp_manage_ratings' ) ) {
			mrp_delete_cache_tool();
		}

		if ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_delete_ratings' ) ) {
			mrp_delete_entries_tool();
		}

		if ( current_user_can( 'mrp_manage_ratings' ) ) {
			mrp_clean_db_tool();
			if ( mrp_migration_tool_supported() ) {
				mrp_migration_tool();
			}

		}

		// add other tools if you like
		do_action( 'mrp_tools' );
		?>

	</div>
	<?php
}

/**
 * Checks whether migration is possible
 */
function mrp_migration_tool_supported() {
	global $wpdb;
	return ( $wpdb->get_var('SHOW TABLES LIKE "' . $wpdb->prefix . 'mr_rating_item"') != null );
}

/**
 *
 */
function mrp_delete_entries_tool() {
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Delete Rating Entries', 'multi-rating-pro' ); ?></span></h3>

			<div class="inside">

				<p><?php _e( 'Permanently delete rating entries from the database.', 'multi-rating-pro' ); ?></p>
				<form method="post" id="clear-db-form">
					<p>
						<input type="text" name="username" id="username" class="" autocomplete="off" placeholder="Username">
						<input type="text" class="date-picker" autocomplete="off" name="from-date2" placeholder="From - yyyy-MM-dd" id="from-date2">
						<input type="text" class="date-picker" autocomplete="off" name="to-date2" placeholder="To - yyyy-MM-dd" id="to-date2">

						<?php
						mrp_posts_select( null, true );
						mrp_rating_form_select( null, false, true);
						?>

						<input type="hidden" name="clear-db" id="clear-db" value="false" />
						<?php
						submit_button( $text = __( 'Delete Rating Entries', 'multi-rating-pro' ), $type = 'delete', $name = 'clear-db-btn', $wrap = false, $other_attributes = null );
						?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Delete calculated ratings
 */
function mrp_delete_cache_tool() {
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Delete Calculated Ratings', 'multi-rating-pro' ); ?></span></h3>

			<div class="inside">

				<p><?php _e( 'Rating are calculated on page load if necessary and then stored in the database for performance.', 'multi-rating-pro' ); ?></p>

				<form method="post" id="refresh-db-form">
					<p>
						<?php
						mrp_posts_select( null, true );
						mrp_rating_form_select( null, false, true);
						?>
						<input type="hidden" name="refresh-db" id="refresh-db" value="false" />
						<?php
						submit_button( __( 'Delete Calculated Ratings', 'multi-rating-pro' ), 'secondary', 'refresh-db-btn', false, null );
						?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}


/**
 *
 */
function mrp_clean_db_tool() {
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Clean Database', 'multi-rating-pro' ); ?></span></h3>

			<div class="inside">

				<p><?php _e( 'Clean dastabase to remove any invalid or incomplete rating entries.', 'multi-rating-pro' ); ?></p>

				<form method="post" id="clean-db-form">
					<p>
						<input type="hidden" name="clean-db" id="clean-db" value="false" />
						<?php
						submit_button( __( 'Clean Database', 'multi-rating-pro' ), 'secondary', 'clean-db-btn', false, null );
						?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}


/**
 *
 */
function mrp_migration_tool() {
	global $wpdb;
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Migrate Rating Entries', 'multi-rating-pro' ); ?></span></h3>

			<div class="inside">

				<p><?php _e( 'Migrate rating entries from the free Multi Rating plugin to the Multi Rating Pro plugin.' )?>

				<form method="post" id="migrate-form">

					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></th>
								<td>
									<select id="rating-form-id" name="rating-form-id">
										<option value=""><?php _e( 'Create New', 'multi-rating-pro' ); ?></option>

										<?php
										$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
										$rows = $wpdb->get_results( $query, ARRAY_A );

										foreach ( $rows as $row ) {
											$name = apply_filters( 'mrp_translate_single_string', $row['name'], 'rating-form-' . $row['rating_form_id'] . '-name' );
											echo '<option value="' . esc_attr( $row['rating_form_id'] ) . '">' .  esc_html( stripslashes( $name ) ) . '</option>';
										} ?>
									</select>
									<br />
									<label for="rating-form-id"><?php _e( 'You can migrate all entries to a new rating form or to an existing rating form.', 'multi-rating-pro'  ); ?></label>
								</td>
							</tr>
							<tr>
								<th scope="row"><?php _e( 'Rating Items', 'multi-rating-pro' ); ?></th>
								<td>

									<div id="rating-items-map">
										<?php

										global $wpdb;
										$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'mr_rating_item' );

										?>
										<table class="widefat" style="width: auto !important;">
											<thead>
												<tr>
													<th><?php _e( 'From', 'multi-rating-pro' ); ?></th>
													<th><?php _e( 'To', 'multi-rating-pro' ); ?></th>
												</tr>
											</thead>
											<tbody>
												<?php
												$count = 0;

												foreach ( $rows as $row ) { ?>
													<tr <?php if ( $count % 2 == 0 ) echo 'class="alternate"'; ?>>
														<td><?php echo $row->description; ?></td>
														<td>
															<?php
															// TODO dynamic change rating items based on selected rating form
															$rating_items = MRP_Multi_Rating_API::get_rating_items();
															?>
															<select name="rating-items-map[<?php echo $row->rating_item_id; ?>]">
																<option value=""><?php _e( 'Create New', 'multi-rating-pro' ); ?></option>
																<?php foreach ( $rating_items as $rating_item ) { ?>
																	<option value="<?php echo esc_attr( $rating_item['rating_item_id'] ); ?>"><?php echo esc_html( $rating_item['description'] ); ?></option>
																<?php } ?>
															</select>
															<?php $count++; ?>
														</td>
													</tr>
												<?php } ?>
											</tbody>
										</table>
									</div>

									<p><?php _e( 'Map rating items. You may need to create new rating items beforehand if needed.', 'multi-rating-pro' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>

					<p>
						<input type="hidden" name="import-db" id="import-db" value="false" />
						<?php
						submit_button( $text = __( 'Migrate Rating Entries', 'multi-rating-pro' ), $type = 'delete', $name = 'import-db-btn', $wrap = false, $other_attributes = null );
						?>
					</p>
				</form>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Export ratings to a CSV file
 */
function mrp_export_tool() {
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Export Rating Entries', 'multi-rating-pro' ); ?></span></h3>

			<div class="inside">
				<p><?php _e( 'Download a CSV of rating entries.', 'multi-rating-pro' ); ?></p>

				<form method="post" id="export-rating-results-form">
					<p>
						<input type="text" name="username" id="username" class="" autocomplete="off" placeholder="Username">
						<input type="text" class="date-picker" autocomplete="off" name="from-date1" placeholder="From - yyyy-MM-dd" id="from-date1">
						<input type="text" class="date-picker" autocomplete="off" name="to-date1" placeholder="To - yyyy-MM-dd" id="to-date1">

						<?php
						mrp_posts_select( null, true );
						mrp_rating_form_select( null, false, true);
						?>
						<br />

						<input type="checkbox" name="entry-approved" id="entry-approved" value="true" />
						<label for="entry-approved"><?php _e( 'Approved ratings only', 'multi-rating-pro' ); ?></label>
						<br />

						<input type="checkbox" name="comments-only" id="comments-only" value="true" />
						<label for="comments-only"><?php _e( 'Ratings with comments only', 'multi-rating-pro' ); ?></label>
						<br />

						<input type="checkbox" name="show-rating-items" id="show-rating-items" value="true" checked />
						<label for="comments-only"><?php _e( 'Include rating items', 'multi-rating-pro' ); ?></label>
						<br />

						<input type="checkbox" name="show-custom-fields" id="show-custom-fields" value="true" checked />
						<label for="comments-only"><?php _e( 'Include custom fields', 'multi-rating-pro' ); ?></label>
					</p>

					<p>
						<input type="hidden" name="export-rating-results" id="export-rating-results" value="false" />
						<?php
						submit_button( __( 'Generate CSV', 'multi-rating-pro' ), 'secondary', 'export-btn', false, null );
						?>
					</p>
				</form>
			</div><!-- .inside -->
		</div>
	</div>
	<?php
}

/**
 * Process exporting rating entries to a CSV file
 */
function mrp_export_rating_entries() {

	if ( ! ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_export_ratings' ) ) ) {
		return;
	}

	if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$file_name = 'rating-results-' . date( 'YmdHis' ) . '.csv';

	$username = isset( $_POST['username'] ) ? $_POST['username'] : null;
	$from_date = isset( $_POST['from-date1'] ) ? $_POST['from-date1'] : null;
	$to_date = isset( $_POST['to-date1'] ) ? $_POST['to-date1'] : null;
	$post_id = isset( $_POST['post-id'] ) ? $_POST['post-id'] : null;
	$rating_form_id = isset( $_POST['rating-form-id'] ) ? $_POST['rating-form-id'] : null;
	$comments_only = isset( $_POST['comments-only'] ) ? true : false;
	$entry_approved = isset( $_POST['entry-approved'] ) ? true : false;
	$show_rating_items = isset( $_POST['show-rating-items'] ) ? true : false;
	$show_custom_fields = isset( $_POST['show-custom-fields'] ) ? true : false;

	$filters = array();

	$filters['user_id'] = null;
	if ( $username != null && strlen( $username ) > 0 ) {
		// get user id
		$user = get_user_by( 'login', $username );
		if ( $user && $user->ID ) {
			$filters['user_id'] = $user->ID;
		}
	}

	if ( $rating_form_id != null && strlen( $rating_form_id ) > 0 ) {
		$filters['rating_form_id'] = $rating_form_id;
	}

	if ( $post_id != null && strlen( $post_id ) > 0 ) {
		$filters['post_id'] = $post_id;
	}

	if ( $comments_only == true) {
		$filters['comments_only'] = true;
	}

	if ( $entry_approved == true) {
		$filters['entry_status'] = 'approved';
	} else {
		$filters['entry_status'] = null;
	}

	$filters['approved_comments_only'] = false;

	if ( $show_rating_items ) {
		$filters['show_rating_items'] = true;
	}

	if ( $show_custom_fields ) {
		$filters['show_custom_fields'] = true;
	}
	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
			if ( checkdate( $month , $day , $year )) {
			$filters['from_date'] = $from_date;
		}
	}

	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
			if ( checkdate( $month , $day , $year )) {
			$filters['to_date'] = $to_date;
		}
	}

	if ( mrp_generate_csv_report( $file_name, $filters ) ) {

		header('Content-type: text/csv');
		header('Content-Disposition: attachment; filename="' . $file_name . '"');
		readfile( MRP_PLUGIN_DIR . $file_name );
		// delete file
		unlink( MRP_PLUGIN_DIR . $file_name );
	}

	die();
}

/**
 * Generates rating results in CSV format.
 *
 * @param $file_name the file_name to save
 * @param $filters used to filter the report e.g. from_date, to_date, user_id etc...
 * @returns true if report successfully generated and written to file
 */
function mrp_generate_csv_report( $file_name, $filters ) {

	$show_custom_fields = isset( $filters['show_custom_fields'] ) ? $filters['show_custom_fields'] : false;
	$show_rating_items = isset( $filters['show_rating_items'] ) ? $filters['show_rating_items'] : false;
	$rating_form_id = isset( $filters['rating_form_id'] ) ? $filters['rating_form_id'] : null;

	$custom_fields = array();
	if ( $show_custom_fields ) {
		$custom_fields = MRP_Multi_Rating_API::get_custom_fields( $rating_form_id );
	}

	$rating_items = array();
	if ( $show_rating_items ) {
		$rating_items = MRP_Multi_Rating_API::get_rating_items( array( 'rating_form_id' => $rating_form_id ) );
	}

	$header_row =
			'"' . __( 'Entry Id', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Entry Date', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Entry Status', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Post Id', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Post Title', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Rating Form ID', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Rating Form Name', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Score Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Adjusted Score Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Total Max Option Value' , 'multi-rating-pro' ) . '",' .
			'"' . __( 'Percentage Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Adjusted Percentage Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Star Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Adjusted Star Rating Result', 'multi-rating-pro' ) . '",' .
			'"' . __( 'User Id', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Username', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Title', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Comment ID', 'multi-rating-pro' ) . '",' .
			'"' . __( 'Comment', 'multi-rating-pro' ) .'",' .
			'"' . __( 'Name', 'multi-rating-pro' ) . '",' .
			'"' . __( 'E-mail', 'multi-rating-pro' ) . '"';

	if ( $show_rating_items ) {
		foreach ( $rating_items as $rating_item ) {
			$header_row .= ',"' . $rating_item['description'] . '"';
		}
	}

	if ( $show_custom_fields ) {
		foreach ( $custom_fields as $custom_field ) {
			$header_row .= ',"' . $custom_field['label'] . '"';
		}
	}

	$rating_entry_list = MRP_Multi_Rating_API::get_rating_entry_result_list( $filters );

	$export_data_rows = array( $header_row );

	foreach ( $rating_entry_list['rating_results'] as $rating_result ) {

		$rating_entry_id = $rating_result['rating_entry_id'];
		$rating_form_id = $rating_result['rating_form_id'];
		$post_id =  $rating_result['post_id'];

		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

		if ( $rating_entry== null || $rating_form  == null ) {
			break;
		}

		$current_row =
				$rating_entry_id .',' .
				'"' . $rating_entry['entry_date'] . '",' .
				'"' . $rating_entry['entry_status'] . '",' .
				$post_id . ',' .
				'"' . trim( get_the_title( $post_id ) ) . '",' .
				$rating_form_id . ',' .
				'"' . trim( $rating_form['name'] ) . '",' .
				$rating_result['score_result'] . ',' .
				$rating_result['adjusted_score_result'] . ',' .
				$rating_result['total_max_option_value'] . ',' .
				$rating_result['percentage_result'] . ',' .
				$rating_result['adjusted_percentage_result'] . ',' .
				$rating_result['star_result'] . ',' .
				$rating_result['adjusted_star_result'] . ',' .
				$rating_entry['user_id'] . ',' .
				'"' . trim( $rating_entry['username'] ) . '",' .
				'"' . trim( $rating_entry['title'] ) . '",' .
				$rating_entry['comment_id'] . ',' .
				'"' . trim( $rating_entry['comment'] ) . '",' .
				'"' . trim( $rating_entry['name'] ) . '",' .
				'"' . $rating_entry['email'] . '"';

		if ( $show_rating_items ) {

			foreach ( $rating_items as $rating_item ) {
				$rating_item_id = $rating_item['rating_item_id'];
				$value_text = '';

				if ( isset( $rating_entry['rating_item_values'][$rating_item_id] ) ) {
					$value = $rating_entry['rating_item_values'][$rating_item_id];

					if ( $value >= 0 ) {
						$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
						$value_text = isset( $option_value_text_lookup[$value] ) ? $option_value_text_lookup[$value] : $value;
					}
				}

				$current_row .= ',"' . $value_text . '"';
			}
		}

		if ( $show_custom_fields ) {

			foreach ( $custom_fields as $custom_field ) {
				$custom_field_id = $custom_field['custom_field_id'];

				$value_text = '';
				if ( isset( $rating_entry['custom_field_values'][$custom_field_id] ) ) {
					$value_text = $rating_entry['custom_field_values'][$custom_field_id];
				}

				$current_row .= ',"' . $value_text . '"';
			}

		}

		$current_row = apply_filters( 'mrp_rating_entries_csv_row', $current_row, $rating_entry_id, $post_id, $rating_form_id, $rating_result, $rating_entry );

		array_push( $export_data_rows, $current_row );
	}

	$file = null;
	try {
		$file = fopen( MRP_PLUGIN_DIR . $file_name, 'w' );
		foreach ( $export_data_rows as $row ) {
			fputs( $file, $row . "\r\n" );
		}
		fclose( $file );
	} catch ( Exception $e ) {
		return false;
	}

	return true;
}

/**
 * Clears all rating results from the database
 */
function mrp_delete_rating_entries() {

	if ( ! ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_delete_ratings' ) ) ) {
		return;
	}

	if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$username = isset( $_POST['username'] ) ? $_POST['username'] : null;
	$from_date = isset( $_POST['from-date2'] ) ? $_POST['from-date2'] : null;
	$to_date = isset( $_POST['to-date2'] ) ? $_POST['to-date2'] : null;
	$post_id = isset( $_POST['post-id'] ) ? $_POST['post-id'] : null;
	$rating_form_id = isset( $_POST['rating-form-id'] ) ? $_POST['rating-form-id'] : null;

	$user_id = null;
	if ( $username ) {
		$user = get_user_by( 'login', $username );
		if ( $user && $user->ID ) {
			$user_id = $user->ID;
		}
	}

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$from_date = null;
		}
	}

	if ( $to_date != null && strlen( $to_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year ) ) {
			$to_date = null;
		}
	}

	$rating_entries = MRP_Multi_Rating_API::get_rating_entries( array(
			'user_id' => $user_id,
			'from_date' => $from_date,
			'to_date' => $to_date,
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id,
			'entry_status' => '',
			'published_posts_only' => false,
			'approved_comments_only' => false
	) );

	if ( count( $rating_entries ) > 0 ) {

		// TODO batch process?
		foreach ( $rating_entries as $rating_entry ) {
			MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );
		}

		echo '<div class="updated"><p>' . __( 'Database entries deleted successfully.', 'multi-rating-pro' ) . '</p></div>';

	} else {
		echo '<div class="error"><p>' . __( 'No entries found.', 'multi-rating-pro' ) . '</p></div>';
	}

}

/**
 * Cleans database.
 */
function mrp_clean_db( $show_message = true ) {

	if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
		return;
	}

	$deleted_rows = MRP_Multi_Rating_API::delete_orphaned_data();

	if ( $deleted_rows > 0 ) {
		echo '<div class="updated"><p>' . sprintf( __( 'Database cleaned successfully. %d rows deleted.', 'multi-rating-pro' ), $deleted_rows ) . '</p></div>';
	} else {
		echo '<div class="updated"><p>' . __( 'Database is clean. No rows deleted.', 'multi-rating-pro' ) . '</p></div>';
	}

}


/**
 * Refresh the rating results stored in the database so that they get recalculated
 */
function mrp_clear_calculated_ratings() {

	if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
		return;
	}

	if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	$post_id = isset( $_POST['post-id'] ) && is_numeric( $_POST['post-id'] ) ? intval( $_POST['post-id'] ) : null;
	$rating_form_id = isset( $_POST['rating-form-id'] ) && is_numeric( $_POST['rating-form-id'] ) ? intval( $_POST['rating-form-id'] ) : null;

	global $wpdb;

	$rating_form_ids = array();
	if ( $rating_form_id != null ) {

		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_form_id' => $rating_form_id) );

	} else {

		$query = 'SELECT rating_form_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME;
		$rows = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $rows as $row ) {

			MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_form_id' => $row['rating_form_id'] ) );

		}
	}

	if ( $post_id != null ) {

		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id ) );

	} else {

		$types = get_post_types();

		foreach( $types as $type ) {

			$query = new WP_Query( array( 'post_type' => $type ) );
			$all_posts = $query->get_posts( );

			foreach ( $all_posts as $post ) {

				MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post->ID ) );

			}
		}
	}

	echo '<div class="updated"><p>' . __( 'Database refreshed successfully.', 'multi-rating-pro' ) . '</p></div>';
}

/**
 * Imports rating entries from free version
 */
function mrp_migrate_rating_entries() {

	if ( ! current_user_can( 'mrp_manage_ratings' ) ) {
		return;
	}

	if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( 0 );
	}

	global $wpdb;

	$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'mr_rating_item' );

	if  ( ! is_array( $_POST['rating-items-map'] ) || ! isset( $_POST['rating-form-id'] ) ) {
		return;
	}

	$rating_items_map = $_POST['rating-items-map'];
	$rating_item_ids = array();

	foreach ( $rows as $row ) {

		$from_rating_item_id = $row->rating_item_id;

		if ( $rating_items_map[$from_rating_item_id] == '' ) {

			$weight = $row->weight;
			$description = $row->description;
			$default_option_value = $row->default_option_value;
			$max_option_value = $row->max_option_value;
			$type = $row->type;
			$required = $row->required;

			$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME,
					array( 'description' => $description, 'max_option_value' => $max_option_value, 'default_option_value' => $default_option_value,
							'weight' => $weight, 'option_value_text' => '', 'type' => $type, 'required' => $required ),
					array( '%s', '%d', '%d', '%f', '%s', '%s', '%d' )
			);

			$rating_item_id = intval( $wpdb->insert_id );

			$rating_items_map[$from_rating_item_id] = $rating_item_id;

			do_action( 'mrp_register_single_string', 'rating-item-' . $rating_item_id . '-description', $description );
		}

		array_push( $rating_item_ids, $rating_items_map[$from_rating_item_id] );
	}

	$rating_form_id =  $_POST['rating-form-id'];

	if ( $rating_form_id == null || $rating_form_id == '' ) {

		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
				array( 'name' => __( 'Rating Form', 'multi-rating-pro' ) ),
				array( '%s' )
		);

		$rating_form_id = intval( $wpdb->insert_id );

		foreach ( $rating_item_ids as $rating_item_id ) {
			$results = $wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
					array( 'item_id' => $rating_item_id, 'item_type' => 'rating-item', 'rating_form_id' => $rating_form_id ),
					array( '%d', '%s', '%d' )
			);
		}
	}

	$wpdb->show_errors();

	$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . 'mr_rating_item_entry' );

	foreach ( $rows as $row ) {

		$from_rating_entry_id = intval( $row->rating_item_entry_id );
		$post_id = intval( $row->post_id );
		$entry_date = $row->entry_date;
		$user_id = intval( $row->user_id );

		$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME,
				array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id, 'entry_date' => $entry_date,
						'entry_status' => 'approved', 'user_id' => $user_id, 'name' => '', 'email' => '', 'comment' => '' ),
				array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s' )
		);

		$rating_entry_id = intval( $wpdb->insert_id );

		$query = 'SELECT * FROM ' . $wpdb->prefix . 'mr_rating_item_entry_value WHERE rating_item_entry_id = %d';
		$entry_value_rows = $wpdb->get_results( $wpdb->prepare( $query, $from_rating_entry_id ) );

		foreach ( $entry_value_rows as $entry_value_row ) {

			$from_rating_item_id = intval( $entry_value_row->rating_item_id );
			$rating_item_id = intval( $rating_items_map[$from_rating_item_id] );
			$rating_item_value = intval( $entry_value_row->value );

			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME,
					array( 'rating_item_entry_id' => $rating_entry_id, 'rating_item_id' => $rating_item_id, 'value' => $rating_item_value ),
					array( '%d', '%d', '%d' )
			);
		}
	}

	echo '<div class="updated"><p>' . __( 'Rating entries imported successfully.', 'multi-rating-pro' ) . '</p></div>';
}


if ( isset( $_POST['export-rating-results'] ) && $_POST['export-rating-results'] == 'true' ) {
	add_action( 'admin_init', 'mrp_export_rating_entries' );
}

if ( isset( $_POST['clear-db'] ) && $_POST['clear-db'] === "true" ) {
	add_action( 'admin_init', 'mrp_delete_rating_entries', true );
}

if ( isset( $_POST['refresh-db'] ) && $_POST['refresh-db'] === "true" ) {
	add_action( 'admin_init', 'mrp_clear_calculated_ratings' );
}

if ( isset( $_POST['clean-db'] ) && $_POST['clean-db'] === "true" ) {
	add_action( 'admin_init', 'mrp_clean_db', 10, 1 );
}

if ( isset( $_POST['import-db'] ) && $_POST['import-db'] === "true" ) {
	add_action( 'admin_init', 'mrp_migrate_rating_entries' );
}
?>

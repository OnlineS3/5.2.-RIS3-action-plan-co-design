<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rating forms page
 */
function mrp_rating_forms_screen() {
	?>
	<div class="wrap" id="mrp-rating-forms">
		<?php
		if ( isset( $_GET['rating-form-id'] ) ) {

			if ( strlen( $_GET['rating-form-id'] ) == 0 ) {
				?><h2 style="display: block; float: left; width: auto;"><?php _e( 'Add New Rating Form', 'multi-rating-pro' ); ?></h2><?php
			} else {
				?><h2 style="display: block; float: left; width: auto;"><?php printf( __( 'Edit Rating Form #%d', 'multi-rating-pro' ), intval( $_GET['rating-form-id'] ) ); ?></h2><?php
			}

			$rating_form_id = isset( $_REQUEST['rating-form-id'] ) && is_numeric( $_REQUEST['rating-form-id'] )
					? intval( $_REQUEST['rating-form-id']  ) : null;
			$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

			$all_rating_items = MRP_Multi_Rating_API::get_rating_items();
			$all_custom_fields = MRP_Multi_Rating_API::get_custom_fields();
			?>

			<div style="display: block; float: right; margin-top: 9px;">
				<?php mrp_rating_form_select( $rating_form_id, false, false ); ?>
				<input id="switch-rating-form" type="button" class="button button-secondary" value="<?php _e( 'Switch', 'multi-rating-pro' ); ?>" />
			</div>


			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<div>
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><?php _e( 'Name', 'multi-rating-pro' ); ?></th>
									<td>
										<input type="text" id="name" name="name" maxlength="255" value="<?php if ( isset( $rating_form['name'] ) ) { echo $rating_form['name']; } ?>" class="regular-text">
										<input type="hidden" id="ratingFormId" value="<?php echo $rating_form_id; ?>" />
									</td>
								</tr>
							</tbody>
						</table>
					</div>


					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">
							<div id="mrp-rating-items" class="postbox ">
								<div class="handlediv" title="Click to toggle"><br></div><h3 class="hndle ui-sortable-handle"><span><?php _e( 'Rating Items', 'multu-rating-pro' ); ?></span></h3>
								<div class="inside">
									<form method="get" action="">
										<?php
										if ( count( $all_rating_items ) > 0 ) {
											?><ul><?php
												foreach ( $all_rating_items as $rating_item ) {
													?><li><input type="button" id="mrp-rating-item-<?php echo $rating_item['rating_item_id']; ?>" class="mrp-add-rating-item button button-secondary" value="<?php echo $rating_item['description']; ?>"<?php if ( isset( $rating_form['rating_items'][$rating_item['rating_item_id']] ) ) { echo ' disabled=\"true\"'; } ?>/></li><?php
												}
											?></ul><?php
										} else {
											_e( 'None', 'multi-rating-pro' ); ?><br /><?php
										}
										?>
										<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
										<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
									</form>
								</div>
							</div>
							<div id="mrp-review-fields" class="postbox">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Review Fields', 'multu-rating-pro' ); ?></span></h3>
								<div class="inside">
									<form method="get" action="">
										<ul>
											<li><input type="button" id="mrp-review-field-<?php echo MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID; ?>" class="mrp-add-review-field button button-secondary" value="<?php _e( 'Title', 'multi-rating-pro' ); ?>"<?php if ( isset( $rating_form['review_fields'][MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] ) ) { echo ' disabled=\"true\"'; } ?> /></li>
											<li><input type="button" id="mrp-review-field-<?php echo MRP_Multi_Rating::NAME_REVIEW_FIELD_ID; ?>" class="mrp-add-review-field button button-secondary" value="<?php _e( 'Name', 'multi-rating-pro' ); ?>"<?php if ( isset( $rating_form['review_fields'][MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] ) ) { echo ' disabled=\"true\"'; } ?> /></li>
											<li><input type="button" id="mrp-review-field-<?php echo MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID; ?>" class=" mrp-add-review-field button button-secondary" value="<?php _e( 'E-mail', 'multi-rating-pro' ); ?>"<?php if ( isset( $rating_form['review_fields'][MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] ) ) { echo ' disabled=\"true\"'; } ?> /></li>
											<li><input type="button" id="mrp-review-field-<?php echo MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID; ?>" class="mrp-add-review-field button button-secondary" value="<?php _e( 'Comment', 'multi-rating-pro' ); ?>"<?php if ( isset( $rating_form['review_fields'][MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] ) ) { echo ' disabled=\"true\"'; } ?> /></li>
										</ul>
										<p class="description"><?php _e( 'Name and e-mail are not displayed for logged in users.', 'multi-rating-pro' ); ?></p>
										<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
										<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
									</form>
								</div>
							</div>
							<div id="mrp-custom-fields" class="postbox ">
								<div class="handlediv" title="Click to toggle"><br></div>
								<h3 class="hndle ui-sortable-handle"><span><?php _e( 'Custom Fields', 'multu-rating-pro' ); ?></span></h3>
								<div class="inside">
									<form method="get" action="">
										<?php
										if ( count( $all_custom_fields ) > 0 ) {
											?><ul><?php
												foreach ( $all_custom_fields as $custom_field ) {
													?><li><input type="button" id="mrp-custom-field-<?php echo $custom_field['custom_field_id']; ?>" class="mrp-add-custom-field button button-secondary" value="<?php echo $custom_field['label']; ?>"<?php if ( isset( $rating_form['custom_fields'][$custom_field['custom_field_id']] ) ) { echo " disabled=\"true\""; } ?>/></li><?php
												}
											?></ul><?php
										} else {
											_e( 'None.', 'multi-rating-pro' ); ?><?php
										}
										?>
										<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
										<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
									</form>
								</div>
							</div>
						</div>
					</div>

					<div id="postbox-container-2" class="postbox-container">
						<table id="edit-rating-form" class="wp-list-table widefat fixed striped">
							<thead>
								<tr>
									<th scope="col" class="manage-column column-text column-primary"><?php _e( 'Text', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-required"><?php _e( 'Required', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-weight"><?php _e( 'Weight', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-allow-not-applicable"><?php _e( 'Allow N/A', 'multi-rating-pro' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php
								if ( ( isset( $rating_form['rating_items'] ) && count( $rating_form['rating_items'] ) > 0 )
										|| ( isset( $rating_form['custom_fields'] ) && count( $rating_form['custom_fields'] ) > 0 )
										|| ( isset( $rating_form['review_fields'] ) && count( $rating_form['review_fields'] ) > 0 ) ) {


									if ( isset( $rating_form['rating_items'] ) && count( $rating_form['rating_items'] ) > 0 ) {
										foreach ( $rating_form['rating_items'] as $rating_item ) {
											?>
											<tr>
												<td>
													<strong><?php echo $rating_item['description']; ?></strong>
													<div class="row-actions">
														<span class="id"><?php printf( __( 'ID: %d'), $rating_item['rating_item_id'] ); ?> | </span>
														<span class="edit"><a href="admin.php?page=<?php echo MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG; ?>&rating-item-id=<?php echo $rating_item['rating_item_id']; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a> | </span>
														<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span>
													</div>
													<input type="hidden" name="id" value="<?php echo $rating_item['rating_item_id']; ?>" />
													<input type="hidden" name="type" value="rating-item" />
												</td>
												<td><?php _e( 'Rating Item', 'multi-rating-pro' ); ?></td>
												<td><input name="required" type="checkbox" <?php if ( $rating_item['required'] ) { echo 'checked'; } ?> /></td>
												<td><input name="weight" type="number" step="0.01" min="0" class="small-text" value="<?php echo $rating_item['weight']; ?>" required /></td>
												<td><input name="allow-not-applicable" type="checkbox" <?php if ( $rating_item['allow_not_applicable'] ) { echo 'checked'; } ?> /></td>
											</tr>
											<?php
										}
									}
									if ( isset( $rating_form['custom_fields'] ) && count( $rating_form['custom_fields'] ) > 0 ) {
										foreach ( $rating_form['custom_fields'] as $custom_field ) {
											?>
											<tr>
												<td>
													<strong><?php echo $custom_field['label']; ?></strong>
													<div class="row-actions">
														<span class="id"><?php printf( __( 'ID: %d'), $custom_field['custom_field_id'] ); ?> | </span>
														<span class="edit"><a href="admin.php?page=<?php echo MRP_Multi_Rating::CUSTOM_FIELDS_PAGE_SLUG; ?>&custom-field-id=<?php echo $custom_field['custom_field_id']; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a> | </span>
														<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span>
													</div>
													<input type="hidden" name="id" value="<?php echo $custom_field['custom_field_id']; ?>" />
													<input type="hidden" name="type" value="custom-field" />
												</td>
												<td><?php _e( 'Custom Field', 'multi-rating-pro' ); ?></td>
												<td><input name="required" type="checkbox" <?php if ( $custom_field['required'] ) { echo 'checked'; } ?> /></td>
												<td></td>
												<td></td>
											</tr>
											<?php
										}
									}
									if ( isset( $rating_form['review_fields'] ) && count( $rating_form['review_fields'] ) > 0 ) {
										foreach ( $rating_form['review_fields'] as $review_field ) {
											?>
											<tr>
												<td>
													<strong><?php echo $review_field['label']; ?></strong>
													<div class="row-actions">
														<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span>
													</div>
													<input type="hidden" name="id" value="<?php echo $review_field['review_field_id']; ?>" />
													<input type="hidden" name="type" value="review-field" />
												</td>
												<td><?php _e( 'Review Field', 'multi-rating-pro' ); ?></td>
												<td><input name="required" type="checkbox" <?php if ( $review_field['required'] ) { echo 'checked'; } ?> /></td>
												<td></td>
												<td></td>
											</tr>
										<?php
										}
									}
								} else {
									?><tr class="mrp-none"><td colspan="4"><?php _e( 'No items.', 'multi-rating-pro' ); ?></td></tr><?php
								}
							 ?>
							</tbody>
							<tfoot>
								<tr>
									<th scope="col" class="manage-column column-text column-primary"><?php _e( 'Text', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-type"><?php _e( 'Type', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-required"><?php _e( 'Required', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-weight"><?php _e( 'Weight', 'multi-rating-pro' ); ?></th>
									<th scope="col" class="manage-column column-allow-not-applicable"><?php _e( 'Allow N/A', 'multi-rating-pro' ); ?></th>
								</tr>
							</tfoot>
						</table>

						<div class="clear"></div>

						<p>
							<input type="submit" class="button button-primary mrp-save-rating-form-btn" value="Save Changes">
						</p>
					</div>
				</div>
			</div>
		<?php } else {
			?>
			<h2><?php _e( 'Forms', 'multi-rating-pro' ); ?>
				<a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG; ?>&rating-form-id="><?php _e( 'Add New', 'multi-rating-pro' ); ?></a>
			</h2>

			<form method="post" id="rating-form-table-form">
				<?php
				$rating_form_table = new MRP_Rating_Form_Table();
				$rating_form_table->prepare_items();
				$rating_form_table->display();
				?>
			</form>
			<?php
		} ?>
	</div>
	<?php
}


/**
 * Gets a rating item
 */
function mrp_get_rating_item() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {

		$rating_item_id = $_POST['ratingItemId'];

		$all_rating_items = MRP_Multi_Rating_API::get_rating_items();

		$rating_item = $all_rating_items[$rating_item_id];

		echo json_encode( array(
				"item_meta" => sprintf( __( 'Id = %s', 'multi-rating-pro' ), $rating_item_id ),
				"order" => 1,
				"required" => true,
				"text" => $rating_item["description"],
				"id" => $rating_item_id,
				"type" => "rating-item"
		) );
	}

	die();
}


/**
 * Gets a custom field
 */
function mrp_get_custom_field() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {

		$custom_field_id = $_POST['customFieldId'];

		$all_custom_fields = MRP_Multi_Rating_API::get_custom_fields();

		$custom_field = $all_custom_fields[$custom_field_id];

		echo json_encode( array(
				"item_meta" => sprintf( __( 'Id = %s', 'multi-rating-pro' ), $custom_field_id ),
				"order" => 1,
				"required" => true,
				"text" => $custom_field["label"],
				"id" => $custom_field_id,
				"type" => "custom-field"
		) );
	}

	die();
}



/**
 * Saves a rating form
 */
function mrp_save_rating_form() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$rating_form_id = isset( $_POST['ratingFormId'] ) && is_numeric( $_POST['ratingFormId'] ) ? intval( $_POST['ratingFormId'] ) : null;
		$items = isset( $_POST['items'] ) ? $_POST['items'] : array();
		$name = isset( $_POST['name'] ) && strlen( $_POST['name'] ) > 0 ? $_POST['name'] : sprintf( __( 'Rating Form %s', 'multi-rating-pro' ), $rating_form_id );

		global $wpdb;

		if ( $rating_form_id == null ) {

			$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
					array( 'name' => $name ),
					array( '%s' )
			);

			$rating_form_id = intval( $wpdb->insert_id );

		} else {

			$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
					array( 'name' =>  $name ),
					array( 'rating_form_id' => $rating_form_id ),
					array( '%s' ),
					array( '%d' )
			);
		}

		$messages_html = '<div class="updated" style="margin-top: 3em;"><p>' . __( 'Rating form saved.', 'multi-rating-pro' ) . '</p></div>';

		do_action( 'mrp_register_single_string', 'rating-form-' . $rating_form_id . '-name', $name );

		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
				array( 'rating_form_id' => $rating_form_id ),
				array( '%d' )
		);

		foreach ( $items as $item ) {

			$item_id = isset( $item['id'] ) ? $item['id'] : null;
			$item_type = isset( $item['type'] ) ? $item['type'] : null;

			if ( $item_id == null || $item_type == null ) {
				break;
			}

			$weight = isset( $item['weight'] ) && is_numeric( $item['weight'] ) ? floatval( $item['weight'] ) : 1;
			if ( $weight < 0 ) {
				$weight = 1; // weight cannot less than 0
				$messages_html .= '<div class="error"><p>' . __( 'Weight must be greater than 0.', 'multi-rating-pro' ) . '</p></div>';
			}
			$required = isset( $item['required'] ) && $item['required'] == "true" ? true : false;
			$allow_not_applicable = isset( $item['allow-not-applicable'] ) && $item['allow-not-applicable'] == "true" ? true : false;

			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
					array( 'weight' => $weight, 'required' => $required, 'rating_form_id' => $rating_form_id, 'item_id' => $item_id,
							'item_type' => $item_type, 'allow_not_applicable' => $allow_not_applicable ),
					array( '%f', '%d', '%d', '%d', '%s', '%d' )
			);

		}

		echo json_encode( array(
				'success' => true,
				'data' => array( 'items' => $items, 'messages_html' => $messages_html, 'rating_form_id' => $rating_form_id )
		) );
	}

	die();
}
?>

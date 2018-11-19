<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Custom fields screen
 */
function mrp_custom_fields_screen() {
	?>
	<div class="wrap" id="mrp-custom-fields">
		<?php
		if ( isset( $_REQUEST['custom-field-id'] ) ) {
			
			if ( strlen( $_REQUEST['custom-field-id'] ) == 0 ) {
				?><h2><?php  _e( 'Add New Custom Field', 'multi-rating-pro' ); ?></h2><?php
			} else {
				?><h2><?php printf( __( 'Edit Custom Field #%d', 'multi-rating-pro' ), intval( $_REQUEST['custom-field-id'] ) ); ?></h2><?php
			}
				
			$custom_field_id = null;
			$label = __( 'Sample custom field');
			$placeholder = '';
			$max_length = 255;
			$type = 'input';
			
			if ( isset( $_GET['custom-field-id'] ) && is_numeric( $_GET['custom-field-id'] ) ) {
				$all_custom_fields = MRP_Multi_Rating_API::get_custom_fields();
				
				$custom_field_id = intval( $_GET['custom-field-id'] );
				$custom_field = $all_custom_fields[$custom_field_id];
				
				$label = $custom_field['label'];
				$placeholder = $custom_field['placeholder'];
				$max_length = $custom_field['max_length'];
				$type = $custom_field['type'];
			}
			?>
				
			<form id="edit-custom-field" method="post">
				<table class="form-table">
					<tr valign="form-field">
						<th scope="row"><label for="label"><?php _e( 'Label', 'multi-rating-pro' ); ?></label></th>
						<td>
							<input type="text" name="label" id="label" placeholder="<?php _e( 'Enter a label...', 'multi-rating-pro' ); ?>" maxlength="255" required class="regular-text" value="<?php echo $label; ?>" />
						</td>
					</tr>
					<tr valign="form-field">
						<th scope="row"><label for="type"><?php _e( 'Type', 'multi-rating-pro' ); ?></label></th>
						<td>
							<select name="type" id="type" class="small-text">
								<option value="input" <?php if ( $type == 'input' ) { echo 'selected="selected"'; } ?>><?php _e( 'Input', 'multi-rating-pro' ); ?></option>
								<option value="textarea" <?php if ( $type == 'textarea' ) { echo 'selected="selected"'; } ?>><?php _e( 'Textarea', 'multi-rating-pro' ); ?></option>
							</select>
						</td>
					</tr>
					<?php if ( $custom_field_id == null ) { ?>
						<tr valign="form-field">
							<th scope="row"><label for="max-length"><?php _e( 'Max Length', 'multi-rating-pro' ); ?></label></th>
							<td>
								<input type="number" name="max-length" id="max-length" placeholder="<?php _e( 'Enter max length...', 'multi-rating-pro' ); ?>" min="1" required value="255" class="small-text" value="<?php echo $max_length; ?>"/>
								<label><?php _e( 'What is the maximum character length of the custom field?', 'multi-rating-pro' ); ?></label>
							</td>
						</tr>
					<?php } ?>
					<tr valign="form-field">
						<th scope="row"><label for="placeholder"><?php _e( 'Placeholder', 'multi-rating-pro' ); ?></label></th>
						<td>
							<input type="text" name="placeholder" id="placeholder" placeholder="<?php _e( 'Enter a placeholder...', 'multi-rating-pro' ); ?>" maxlength="255" class="regular-text" value="<?php echo $placeholder; ?>" />
							<label><?php _e( 'Enter a HTML5 placeholder.', 'multi-rating-pro' ); ?></label>
						</td>
					</tr>
				</table>
				
				<p><input id="add-new-custom-field-btn" class="button button-primary" value="<?php _e( 'Save Changes', 'multi-rating-pro' ); ?>" type="submit" /></p>
				<input type="hidden" id="edit-custom-field-form-submitted" name="edit-custom-field-form-submitted" value="true" />
				<input type="hidden" name="custom-field-id" value="<?php if ( isset( $custom_field_id ) ) { echo $custom_field_id; } ?>" />
			</form>	
		<?php } else { ?>
			<h2><?php _e( 'Custom Fields', 'multi-rating-pro' ); ?><a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::CUSTOM_FIELDS_PAGE_SLUG; ?>&custom-field-id"><?php _e( 'Add New', 'multi-rating-pro' ); ?></a></h2>
			<form method="post" id="custom-field-table-form">
				<?php 
				$custom_fields_table = new MRP_Custom_Fields_Table();
				$custom_fields_table->prepare_items();
				$custom_fields_table->display();
				?>
			</form>
		<?php } ?>
	</div>
	<?php 
}


/**
 * Saves a custom field
 */
function mrp_save_custom_field() {
	
	$error_messages = array();

	if ( isset( $_POST['label'] ) && isset( $_POST['type'] ) && isset( $_POST['placeholder'] ) 
			&& current_user_can( 'mrp_manage_ratings' ) ) {
	
		$label = isset( $_POST['label'] ) ? $_POST['label'] : '';
		$type = isset( $_POST['type'] ) ? $_POST['type'] : 'input';
		$placeholder = isset( $_POST['placeholder'] ) ? $_POST['placeholder'] : '';
		$max_length =  isset( $_POST['max-length'] ) && is_numeric( $_POST['max-length'] ) ? intval( $_POST['max-length'] ) : 255;
		$custom_field_id = isset( $_POST['custom-field-id'] ) && is_numeric( $_POST['custom-field-id'] ) ? intval( $_POST['custom-field-id'] ) : null;
		
		/**
		 * Validate custom field
		 */
		if ( strlen( $label ) == 0 || strlen( $label ) > 255 ) {
			array_push( $error_messages, __( 'Label must be greater than 0 and cannot be greater than 255 characters.', 'multi-rating-pro') );
		}
		if ( strlen( $placeholder ) > 255 ) {
			array_push( $error_messages, __( 'Placeholder cannot be greater than 255 characters.', 'multi-rating-pro') );
		}
		if ( $custom_field_id == null && ( ! is_numeric( $max_length ) && intval( $max_length ) <= 0 ) ) {
			array_push( $error_messages,  __( 'Max length must be numeric and greater than 0.', 'multi-rating-pro') );
		}
		
		if ( count( $error_messages ) == 0 ) {
	
			global $wpdb;
			
			if ( $custom_field_id ) {
				
				// max length is not allowed to be updated as it would impact existing ratings
				
				$wpdb->update( $wpdb->prefix.MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME, 
						array( 'label' => $label, 'type' => $type, 'placeholder' => $placeholder ),
						array( 'custom_field_id' => $custom_field_id ), 
						array( '%s', '%s', '%s' ), 
						array( '%d' )
				);
			
			} else {
			
				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME, 
						array( 'label' => $label, 'type' => $type, 'max_length' => $max_length, 'placeholder' => $placeholder ), 
						array( '%s', '%s', '%d', '%s' )
				);
				
				$custom_field_id = intval( $wpdb->insert_id );
				
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );	
				$query = 'CREATE TABLE ' . $wpdb->prefix . 'mrp_custom_field_' . esc_sql( $custom_field_id ) . '(
						id bigint(20) NOT NULL AUTO_INCREMENT,
						value varchar(' . $max_length . ') NOT NULL,
						rating_entry_id bigint(20) NOT NULL,
						PRIMARY KEY  (id)
				) ENGINE=InnoDB AUTO_INCREMENT=1;';
				dbDelta( $query );
			}
			
			do_action( 'mrp_register_single_string', 'custom-field-' . $custom_field_id . '-label', $label );
			do_action( 'mrp_register_single_string', 'custom-field-' . $custom_field_id . '-placeholder', $placeholder );
		
		} 
		
		$wpdb->show_errors();
	} else {
		array_push( $error_messages, __( 'An error has occured.', 'multi-rating-pro' ) );
	}
	
	if ( count( $error_messages ) > 0) {
		echo '<div class="error">';
	
		foreach ( $error_messages as $error_message ) {
			echo '<p>' . $error_message . '</p>';
		}
		echo '</div>';
		return;
	}
	
	wp_redirect( 'admin.php?page=' . MRP_Multi_Rating::CUSTOM_FIELDS_PAGE_SLUG );
	exit();
}
if ( isset( $_POST['edit-custom-field-form-submitted'] ) && $_POST['edit-custom-field-form-submitted'] == 'true' ) {
	add_action( 'admin_init', 'mrp_save_custom_field' );
}
?>
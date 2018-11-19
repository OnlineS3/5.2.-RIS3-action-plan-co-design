<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the rating items screen
 *
 * @since 0.1
 */
function mrp_rating_items_screen() {
	?>
	<div class="wrap" id="mrp-rating-items">
	
		<?php 
		if ( isset( $_REQUEST['rating-item-id'] ) ) {
			
			if ( strlen( $_REQUEST['rating-item-id'] ) == 0 ) {
				?><h2><?php _e( 'Add New Rating Item', 'multi-rating-pro' ); ?></h2><?php
			} else {
				?><h2><?php printf( __( 'Edit Rating Item #%d', 'multi-rating-pro' ), intval( $_REQUEST['rating-item-id'] ) ); ?></h2><?php
			}
			?>
		
			<?php 
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			
			$type = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION];
			$label = __( 'Sample rating item', 'multi-rating-pro' );
			$max_option_value = 5;
			$default_option_value = 5;
			// default option texts
			$option_texts = array( 
					__( '0=No stars', 'multi-rating-pro' ), 
					__( '1=1 star', 'multi-rating-pro' ), 
					__( '2=2 stars', 'multi-rating-pro' ), 
					__( '3=3 stars', 'multi-rating-pro' ), 
					__( '4=4 stars', 'multi-rating-pro' ), 
					__( '5=5 stars', 'multi-rating-pro' ) 
			);
			$rating_item_id = null;
			$only_show_text_options = false;
			
			if ( isset( $_GET['rating-item-id'] ) && is_numeric( $_GET['rating-item-id'] )) {
				$rating_item_id = $_GET['rating-item-id'];
				$rating_items = MRP_Multi_Rating_API::get_rating_items();
				$rating_item = $rating_items[$rating_item_id];
				
				$type = $rating_item['type'];
				$label = $rating_item['description'];
				$max_option_value = $rating_item['max_option_value'];
				$default_option_value = $rating_item['default_option_value'];
				$option_texts = preg_split( '~[\r\n,]+~',  $rating_item['option_value_text'], -1, PREG_SPLIT_NO_EMPTY );
				$only_show_text_options = $rating_item['only_show_text_options'];
			}
			
			?>
			<form method="post" id="edit-rating-item">
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><?php _e( 'Label', 'multi-rating-pro' ); ?></th>
							<td>
								<input type="text" name="description" maxlength="255" value="<?php echo $label; ?>" class="regular-text" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Type', 'multi-rating-pro' ); ?></th>
							<td>
								<select name="type" id="type">
									<option value="select" <?php if ( $type == 'select' ) { echo 'selected="selected"'; } ?>><?php _e( 'Select', 'multi-rating-pro' ); ?></option>
									<option value="radio" <?php if ( $type == 'radio' ) { echo 'selected="selected"'; } ?>><?php _e( 'Radio', 'multi-rating-pro' ); ?></option>
									<option value="star_rating" <?php if ( $type == 'star_rating' ) { echo 'selected="selected"'; } ?>><?php _e( 'Stars', 'multi-rating-pro' ); ?></option>
									<option value="thumbs" <?php if ( $type == 'thumbs' ) { echo 'selected="selected"'; } ?>><?php _e( 'Thumbs', 'multi-rating-pro' ); ?></option>
								</select>
							</td>
						</tr>
						<tr valign="top" <?php if ( $type == 'thumbs' ) { echo 'style="display: none"'; } ?>>
							<th scope="row"><?php _e( 'Max Option', 'multi-rating-pro' ); ?></th>
							<td>
								<input name="max-option-value" type="number" value="<?php echo $max_option_value; ?>" min="0" class="small-text" required />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e( 'Option Text', 'multi-rating-pro' ); ?></th>
							<td>
								<table id="option-text" class="widefat" style="width: auto !important;">
									<thead>
								    	<tr>
								        	<th style="width: 50px !important;"><?php _e( 'Value', 'multi-rating-pro' ); ?></th>
								        	<th><?php _e( 'Text', 'multi-rating-pro' ); ?></th>
								        	<th style="width: 75px !important;"><?php _e( 'Action', 'multi-rating-pro' ); ?></th>
								     	</tr>
								  	</thead>
									<tbody>
								     	<?php    	
								      	$index = 0;
								      	foreach ( $option_texts as $option_text ) {
								      		$parts = explode( '=', $option_text );
								      		$text = isset( $parts[0] ) ? $parts[0] : '';
								      		$value = '';
								      	
								      		if ( isset( $parts[0] ) && isset( $parts[1] ) && count( $parts ) == 2 && is_numeric( $parts[0] ) ) {
								      			$value = intval( $parts[0] );
								      			$text = $parts[1];
								      		}
								      	
								      		$class = 'alternate';
								      		if ( $index++ % 2 == 1 ) {
								      			$class = '';
								      		}
								      		
								      		?>
								      		<tr class="<?php echo $class; ?>">
									        	<td><input type="number" name="option-value[]" class="small-text" value="<?php echo $value; ?>" /></td>
									        	<td><input type="text" name="option-text[]" class="regular-text" value="<?php echo $text; ?>" /></td>
									        	<td><span class="delete"><a class="delete-option-text submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span></td>
									     	</tr>
									     	<?php
								      	}
								     	?>
								  	</tbody>
								</table>
								
								<input type="button" class="button secondary-button" value="<?php _e( 'Add New', 'multi-rating-pro' ); ?>" id="add-option-text" style="margin-top: 5px; margin-bottom: 10px;" />
								<p <?php if ( $type == 'thumbs' ) { echo 'style="display: none;"'; } ?>><input name="only-show-text-options" type="checkbox" <?php if ( $only_show_text_options ) { echo 'checked="checked"'; } ?> /><label><?php _e( 'Only show options with text', 'multi-rating-pro' ); ?></label></p>
								
								<p class="description"><?php _e( 'You can set a rating item as required on a rating form which means 0 cannot be selected.<br />For thumbs only 0 and 1 option texts are allowed for thumbs down and thumbs up respectfully.', 'multi-rating-pro' ); ?></p>
								
							</td>
						</tr>
						<tr valign="top" <?php if ( $type == 'thumbs' ) { echo 'style="display: none"'; } ?>>
							<th scope="row"><?php _e( 'Default Option', 'multi-rating-pro' ); ?></th>
							<td>
								<input name="default-option-value" type="number" value="<?php echo $default_option_value; ?>" min="0" class="small-text" required />
							</td>
						</tr>
					</tbody>
				</table>
				
				<p><input id="add-new-rating-item-btn" class="button button-primary" value="<?php _e( 'Save Changes', 'multi-rating-pro' ); ?>" type="submit" /></p>
				<input type="hidden" id="edit-rating-item-form-submitted" name="edit-rating-item-form-submitted" value="true" />
				<input type="hidden" name="rating-item-id" value="<?php if ( isset( $rating_item_id ) ) { echo $rating_item_id; } ?>" />
			</form>
			<?php 
		} else {
			?>
			<h2><?php _e( 'Rating Items', 'multi-rating-pro' ); ?><a class="add-new-h2" href="admin.php?page=<?php echo MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG; ?>&rating-item-id="><?php _e( 'Add New', 'multi-rating-pro' ); ?></a></h2>
			<form method="post" id="rating-item-table-form">
				<?php 
				$rating_item_table = new MRP_Rating_Item_Table();
				$rating_item_table->prepare_items();
				$rating_item_table->display();
				?>
			</form>
			
			<p><input id="add-new-rating-item-btn" class="button button-primary" value="<?php _e( 'Save Changes', 'multi-rating-pro' ); ?>" type="submit" /></p>
		<?php 
		}
		?>
	</div>
	<?php 
}


/**
 * Form submit to add or edit a rating item
 */
function mrp_rating_item_form_submit() {

	$error_messages = array();

	if ( isset( $_POST['description'] ) && isset( $_POST['max-option-value'] )
			&& isset( $_POST['default-option-value'] ) && isset( $_POST['type'] ) 
			&& current_user_can( 'mrp_manage_ratings' ) ) {
				
		$description = isset( $_POST['description'] ) ? $_POST['description'] : '';
		$type = isset( $_POST['type'] ) ? $_POST['type'] : 'star_rating';
		$max_option_value = isset( $_POST['max-option-value'] ) ? $_POST['max-option-value'] : 5;
		$default_option_value = isset( $_POST['default-option-value'] ) ? $_POST['default-option-value'] : 5;
		$rating_item_id = isset( $_POST['rating-item-id'] ) && is_numeric( $_POST['rating-item-id'] ) ? $_POST['rating-item-id'] : null;
		$option_texts = isset( $_POST['option-text'] ) && is_array( $_POST['option-text'] ) ? $_POST['option-text'] : array();
		$option_values =isset( $_POST['option-value'] ) && is_array( $_POST['option-value'] ) ? $_POST['option-value'] : array();
		$only_show_text_options = isset( $_POST['only-show-text-options'] ) ? true : false;
		
		$option_value_text = '';
		
		if ( is_numeric( $max_option_value ) == false ) {
			array_push( $error_messages, __( 'Max option value cannot be empty and must be a whole number. ', 'multi-rating-pro' ) );
		}

		if ( is_numeric( $default_option_value ) == false ) {
			array_push( $error_messages, __( 'Default option value cannot be empty and must be a whole number. ', 'multi-rating-pro' ) );
		}
		
		if ( strlen(trim( $description ) ) == 0 ) {
			array_push( $error_messages, __( 'Label cannot be empty.', 'multi-rating-pro' ) );
		}
		
		if ( $default_option_value > $max_option_value ) {
			array_push( $error_messages, __( 'Default option value cannot be greater than the max option value.', 'multi-rating-pro' ) );
		}
		
		if ( $default_option_value > $max_option_value ) {
			array_push( $error_messages, $error_message .= __( 'Default option value cannot be greater than the max option value.', 'multi-rating-pro' ) );
		}
		
		$index = 0;
		$option_value_lookup = array();
		foreach ( $option_values as $option_value ) {
				
			if ( ! is_numeric( $option_value ) || $option_value < 0 ) {
				array_push( $error_messages, sprintf( __( 'Option value %s must be numeric and greater than or equal to 0.', 'multi-rating-pro' ) , $option_value ) );
			}
			
			if ( $type == 'thumbs' && $option_value > 1 ) {
				array_push( $error_messages, __( 'Only 0 and 1 option texts are allowed for thumbs.', 'multi-rating-pro' ) );
			}
			
			if ( $index != 0 ) {
				$option_value_text .= ',';
			}
			$option_text = $option_texts[$index];
			
			if ( strlen( trim( $option_text ) )  == 0 ) {
				array_push( $error_messages, __( 'Option text cannot be empty.', 'multi-rating-pro' ) );
			}
				
			if ( isset( $option_value_lookup[$option_value] ) ) {
				array_push( $error_messages, sprintf( __( 'Duplicate option value %s.', 'multi-rating-pro' ), $option_value ) );
			}
				
			if ( intval( $option_value ) > intval( $max_option_value ) ) {
				array_push( $error_messages, sprintf( __( 'Option value %s cannot be greater than max option value %s.', 'multi-rating-pro' ), $option_value, $max_option_value ) );
			}
				
			$option_value_text .= $option_value . '=' . $option_text;
			$option_value_lookup[$option_value] = $option_text;
			$index++;
		}
		
		if ( $type == 'thumbs' ) {
			// set max option value and default option value for thumbs up/down if required
			$max_option_value = 1;
			if ( $default_option_value > 1 ) {
				$default_option_value = 1;
			}
		}
		
		if ( count( $error_messages ) == 0 ) {
			global $wpdb;

			if ( $rating_item_id ) {
				
				$wpdb->update( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME, 
						array( 'description' => $description, 'max_option_value' => $max_option_value, 'default_option_value' => $default_option_value,
								'option_value_text' => $option_value_text, 'type' => $type, 'only_show_text_options' => $only_show_text_options ),
						array( 'rating_item_id' => $rating_item_id ), 
						array( '%s', '%d', '%d', '%s', '%s', '%d' ),
						array( '%d' )
				);
				
			} else {
				
				$results = $wpdb->insert(  $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME, 
						array( 'description' => $description, 'max_option_value' => $max_option_value, 'default_option_value' => $default_option_value,
								'option_value_text' => $option_value_text, 'type' => $type, 'only_show_text_options' => $only_show_text_options ),
						array( '%s', '%d', '%d', '%s', '%s', '%d' )
				);
				
				$rating_item_id = intval( $wpdb->insert_id );
			}
			
			do_action( 'mrp_register_single_string', 'rating-item-' . $rating_item_id . '-description', $description );
			do_action( 'mrp_register_single_string', 'rating-item-' . $rating_item_id . '-option-value-text', $option_value_text );
		}
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
	
	wp_redirect( 'admin.php?page=' . MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG );
	exit();
}
if ( isset( $_POST['edit-rating-item-form-submitted'] ) && $_POST['edit-rating-item-form-submitted'] == 'true' ) {
	add_action( 'admin_init', 'mrp_rating_item_form_submit' );
}
?>
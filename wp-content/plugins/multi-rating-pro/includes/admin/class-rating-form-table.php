<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Rating_Form_Table class
 * 
 * @author dpowney
 */
class MRP_Rating_Form_Table extends WP_List_Table {

	/**
	 * Constants
	 */
	const
	NAME_COLUMN 				= 'name',
	IS_DEFAULT_COLUMN 			= 'is_default',
	CHECKBOX_COLUMN 			= 'cb',
	ENTRIES_COLUMN 				= 'entries',
	RATING_FORM_ID_COLUMN 		= 'rating_form_id',
	SHORTCODE_COLUMN			= 'shortcode',
	DELETE_CHECKBOX 			= 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'		=> __( 'Rating Forms', 'multi-rating-pro' ),
				'plural' 		=> __( 'Rating Forms', 'multi-rating-pro' ),
				'ajax'			=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		
		if ( $which == 'top' ){
			echo "";
		}
		
		if ( $which == 'bottom' ){
			echo "";
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		
		return $columns= array(
				MRP_Rating_Form_Table::CHECKBOX_COLUMN 			=> '<input type="checkbox" />',
				MRP_Rating_Form_Table::NAME_COLUMN 				=> __( 'Name', 'multi-rating-pro' ),
				MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN 	=> __( 'ID', 'multi-rating-pro' ),
				MRP_Rating_Form_Table::ENTRIES_COLUMN			=> __( 'Entries', 'multi-rating-pro' ),
				MRP_Rating_Form_Table::SHORTCODE_COLUMN 		=> __( 'Shortcode', 'multi-rating-pro' ),
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;
		
		// Process any bulk actions first
		$this->process_bulk_action();

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array( );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME;
		
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		
		$rating_form_id = $item[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN];
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$default_rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		
		switch( $column_name ) {
			case MRP_Rating_Form_Table::CHECKBOX_COLUMN :
			case MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN :
				return $item[ $column_name ];
			break;
			
			case MRP_Rating_Form_Table::NAME_COLUMN : {
				
				$edit_rating_form_page = apply_filters( 'mrp_edit_rating_form_page', MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG . '&rating-form-id=' . $rating_form_id, $rating_form_id );
				$view_entries_page = apply_filters( 'mrp_view_entries_page', MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&rating-form-id=' . $rating_form_id, $rating_form_id );
				?>
				<strong>
					<?php echo apply_filters( 'mrp_translate_single_string', $item[ $column_name ], 'rating-form-' . $rating_form_id . '-name' ); ?>
					
				</strong>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'), $rating_form_id ); ?> | </span>
					<span class="edit"><a href="?page=<?php echo $edit_rating_form_page; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a> | </span>
				
					<?php 
					if ( $rating_form_id != $default_rating_form_id ) { 
						?>
						<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a> | <?php 
					}
					?>
					<span class="edit"><a href="?page=<?php echo $view_entries_page; ?>"><?php _e( 'View Entries', 'multi-rating-pro' ); ?></a>
				</div>
				
				<input type="hidden" name="rating-form-id" value="<?php echo $rating_form_id; ?>" />
				<?php
			}
			break;
			
			case MRP_Rating_Form_Table::ENTRIES_COLUMN :
				
				$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
						'rating_form_id' => $rating_form_id,
						'limit' => 1 // avoids retrieving lots of rows that are not used...
				) );
				
				echo $rating_results_list['count_entries'];
				
				break;
				
			case MRP_Rating_Form_Table::SHORTCODE_COLUMN:
				$shortcode = '<code>[mrp_rating_form rating_form_id="' . $rating_form_id . '"]</code>';
				if ( $rating_form_id == $default_rating_form_id ) {
					$shortcode = '<code>[mrp_rating_form]</code>';
				}
				
				echo apply_filters( 'mrp_rating_forms_shortcode_column', $shortcode, $rating_form_id );
			break;
				
			default:
				return print_r( $item, true ) ;
		}
	}
	
	/**
	 * checkbox column
	 * @param unknown_type $item
	 * @return string
	 */
	function column_cb( $item ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$default_rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		if ( $item[ MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN ] == $default_rating_form_id ) {
			return;
		}
		
		return sprintf(
				'<input type="checkbox" name="'.MRP_Rating_Form_Table::DELETE_CHECKBOX.'" value="%s" />', $item[MRP_Rating_Form_Table::RATING_FORM_ID_COLUMN]
		);
	}

	
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		
		$bulk_actions = array();

		if ( current_user_can( 'mrp_manage_ratings' ) ) {
			$bulk_actions = array_merge( $bulk_actions, array(
					'delete'    => __( 'Delete', 'multi-rating-pro' )
			) );
		}
		
		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		
		if ( $this->current_action() ==='delete' && isset( $_REQUEST['delete'] ) 
			&& current_user_can( 'mrp_manage_ratings' ) ) {
			
			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );

			foreach( $checked as $rating_form_id ) {
				MRP_Multi_Rating_API::delete_rating_form( $rating_form_id );	
			}
				
			echo '<div class="updated"><p>' . __( 'Rating form(s) deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
		}
	}
}

/**
 * Deletes a rating form
 */
function mrp_delete_rating_form() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) 
		&& current_user_can( 'mrp_manage_ratings' ) ) {

		$rating_form_id = isset( $_POST['ratingFormId'] ) && is_numeric( $_POST['ratingFormId'] ) ? $_POST['ratingFormId'] : null;

		MRP_Multi_Rating_API::delete_rating_form( $rating_form_id );

		echo json_encode( array(
				'success' => true,
				'data' => array( 'messages_html' => '<div class="updated"><p>' . __( 'Rating form deleted.', 'multi-rating-pro' ) . '</p></div>' )
		) );
	}

	die();
}

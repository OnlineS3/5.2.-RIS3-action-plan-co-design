<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ){
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Rating_Item_Table class
 * 
 * @author dpowney
 *
 */
class MRP_Rating_Item_Table extends WP_List_Table {

	const
	DESCRIPTION_COLUMN = 'description',
	MAX_OPTION_COLUMN = 'max_option_value',
	CHECKBOX_COLUMN = 'cb',
	RATING_ITEM_ID_COLUMN = 'rating_item_id',
	OPTION_VALUE_TEXT_COLUMN = 'option_value_text',
	DEFAULT_VALUE_COLUMN = 'default_option_value',
	TYPE_COLUMN = 'type',
	DELETE_CHECKBOX = 'delete[]';
	
	public $type_options = null;

	/**
	 * Constructor
	 */
	function __construct() {

		parent::__construct( array(
				'singular'=> __( 'Rating Item', 'multi-rating-pro' ),
				'plural' => __( 'Rating Items', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {
		if ( $which == 'top' ){
			echo '';
		}
		
		if ( $which == 'bottom' ){
			echo '';
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {
		
		return array(
				MRP_Rating_Item_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Rating_Item_Table::DESCRIPTION_COLUMN =>__( 'Label', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN =>__( 'ID', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::TYPE_COLUMN =>__( 'Type', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::MAX_OPTION_COLUMN =>__( 'Max Option', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN => __( 'Option Text', 'multi-rating-pro' ),
				MRP_Rating_Item_Table::DEFAULT_VALUE_COLUMN => __( 'Default Option', 'multi-rating-pro' )
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

		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME;
		
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {
		
		switch( $column_name ) {
			case MRP_Rating_Item_Table::CHECKBOX_COLUMN :
			case MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN :
			case MRP_Rating_Item_Table::MAX_OPTION_COLUMN :
			case MRP_Rating_Item_Table::DEFAULT_VALUE_COLUMN :
				return $item[$column_name];
				break;
			case MRP_Rating_Item_Table::OPTION_VALUE_TEXT_COLUMN :
				echo implode( ',<br />', preg_split( '/[\r\n,]+/', esc_html( stripslashes( $item[$column_name] ) ) ) );
				break;
			case MRP_Rating_Item_Table::TYPE_COLUMN :
				if ( $item[$column_name] == 'star_rating') {
					_e( 'Stars', 'multi-rating-pro' ) ;
				} else if ( $item[$column_name] == 'select') {
					_e( 'Select', 'multi-rating-pro' ) ;
				} else if ( $item[$column_name] == 'radio') {
					_e( 'Radio', 'multi-rating-pro' ) ;
				} else {
					_e( 'Thumbs', 'multi-rating-pro' ) ;
				}
				break;
			case MRP_Rating_Item_Table::DESCRIPTION_COLUMN :
					?><strong><?php echo $item[$column_name]; ?></strong>
					<div class="row-actions">
						<span class="id"><?php printf( __( 'ID: %d'), $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN] ); ?> | </span>
						<span class="edit"><a href="admin.php?page=<?php echo MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG; ?>&rating-item-id=<?php echo $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN]; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a> | </span>
						<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span>
					</div>
					<input type="hidden" name="rating-form-id" value="<?php echo $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN]; ?>" />
					<?php
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
	function column_cb($item) {
		
		return sprintf(
				'<input type="checkbox" name="' . MRP_Rating_Item_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Rating_Item_Table::RATING_ITEM_ID_COLUMN]
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
					'delete' => __( 'Delete', 'multi-rating-pro' )
			) );
		}
		
		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		
		if ( $this->current_action() == 'delete' && current_user_can( 'mrp_manage_ratings' ) ) {
			global $wpdb;
			
			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );
			
			foreach( $checked as $rating_item_id ) {
				MRP_Multi_Rating_API::delete_rating_item( $rating_item_id );
			}
			
			echo '<div class="updated"><p>' . __( 'Rating item(s) deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
		}
	}
}


/**
 * Deletes a rating item
 */
function mrp_delete_rating_item() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$rating_item_id = isset( $_POST['ratingItemId'] ) && is_numeric( $_POST['ratingItemId'] ) ? $_POST['ratingItemId'] : null;

		$messages_html = '<div class="updated"><p>' . __( 'Rating item deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
			
		MRP_Multi_Rating_API::delete_rating_item( $rating_item_id );

		echo json_encode( array(
				'success' => true,
				'data' => array( 'messages_html' => $messages_html )
		) );
	}

	die();
}
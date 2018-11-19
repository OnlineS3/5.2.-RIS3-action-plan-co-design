<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Custom_Fields_Table class
 * @author dpowney
 *
 */
class MRP_Custom_Fields_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	CUSTOM_FIELD_ID_COLUMN = 'custom_field_id',
	LABEL_COLUMN = 'label',
	TYPE_COLUMN = 'type',
	MAX_LENGTH_COLUMN = 'max_length',
	PLACEHOLDER_COLUMN = 'placeholder',
	DELETE_CHECKBOX = 'delete[]';
	
	public $type_options = null;

	/**
	 * Constructor
	 */
	function __construct() {
		
		parent::__construct( array(
				'singular'=> __( 'Custom Field', 'multi-rating-pro' ),
				'plural' => __( 'Custom Fields', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
		
		$this->type_options = array(
				'input' => __( 'Input', 'multi-rating-pro' ),
				'textarea' => __( 'Textarea', 'multi-rating-pro' )
		);
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
				MRP_Custom_Fields_Table::CHECKBOX_COLUMN => '<input type="checkbox" />',
				MRP_Custom_Fields_Table::LABEL_COLUMN => __( 'Label', 'multi-rating-pro' ),
				MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN => __( 'ID', 'multi-rating-pro' ),
				MRP_Custom_Fields_Table::TYPE_COLUMN => __( 'Type', 'multi-rating-pro' ),
				MRP_Custom_Fields_Table::MAX_LENGTH_COLUMN => __( 'Max Length', 'multi-rating-pro' ),
				MRP_Custom_Fields_Table::PLACEHOLDER_COLUMN => __( 'Placeholder', 'multi-rating-pro' )
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
		$hidden = array(  );
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// get table data
		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME . ' as cf';
		
		// pagination
		$item_count = $wpdb->query( $query ); //return the total number of affected rows
		$items_per_page = 10;
		$page_num = ! empty( $_GET['paged'] ) ? $_GET['paged'] : '';
		if ( empty( $page_num ) || ! is_numeric( $page_num ) || $page_num <= 0 ) {
			$page_num = 1;
		}
		$total_pages = ceil( $item_count / $items_per_page );
		// adjust the query to take pagination into account
		if ( ! empty( $page_num ) && ! empty( $items_per_page ) ) {
			$offset = ( $page_num -1 ) * $items_per_page;
			$query .= ' LIMIT ' . ( int ) $offset. ',' . ( int ) $items_per_page;
		}
		
		$this->set_pagination_args( array(
				'total_items' => $item_count,
				'total_pages' => $total_pages,
				'per_page' => $items_per_page
		) );
		
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
			
			case MRP_Custom_Fields_Table::CHECKBOX_COLUMN :
				return $item[ $column_name ];
				break;
			case MRP_Custom_Fields_Table::LABEL_COLUMN :
				?><strong><?php echo $item[$column_name]; ?></strong>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'), $item[MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN] ); ?> | </span>
					<span class="edit"><a href="admin.php?page=<?php echo MRP_Multi_Rating::CUSTOM_FIELDS_PAGE_SLUG; ?>&custom-field-id=<?php echo $item[MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN]; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a> | </span>
					<span class="delete"><a class="submitdelete" href="#"><?php _e( 'Delete', 'multi-rating-pro' ); ?></a></span>
				</div>
				<input type="hidden" name="custom-field-id" value="<?php echo $item[MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN]; ?>" />
				<?php
				break;
			case MRP_Custom_Fields_Table::TYPE_COLUMN :
				if ( $item[ $column_name ] == 'input' ) {
					_e( 'Input', 'multi-rating-pro' );
				} else {
					_e( 'Textarea', 'multi-rating-pro' );
				}
				break;
			case MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN :
			case MRP_Custom_Fields_Table::MAX_LENGTH_COLUMN :
			case MRP_Custom_Fields_Table::PLACEHOLDER_COLUMN :
				echo $item[$column_name];
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
		return sprintf(
				'<input type="checkbox" name="' . MRP_Custom_Fields_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Custom_Fields_Table::CUSTOM_FIELD_ID_COLUMN]
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {
		$bulk_actions = array(
				'delete' => __( 'Delete', 'multi-rating-pro' )
		);
		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {
		
		if ( $this->current_action() == 'delete' && isset( $_REQUEST['delete'] ) 
			&& current_user_can( 'mrp_manage_ratings' ) ) {
			
			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );
			
			global $wpdb;
			
			try {
				
				foreach( $checked as $custom_field_id ) {
					
					$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME, 
							array( 'custom_field_id' => $custom_field_id ),
							array( '%d' )
					);
					
					$query = 'DROP TABLE ' . $wpdb->prefix . 'mrp_custom_field_' . esc_sql( $custom_field_id );
					$rows_affected = $wpdb->query( $query );
					
					$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
							array( 'item_type' => 'custom-field', 'item_id' => $custom_field_id ),
							array( '%s', '%d' )
					);
					
					$deleted_rows = MRP_Multi_Rating_API::delete_orphaned_data();
				}
				
				echo '<div class="updated"><p>' . __( 'Custom field(s) deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
			
			} catch ( Exception $e ) {
				echo '<div class="error"><p>' . sprintf( __( 'An error has occured. %s', 'multi-rating-pro' ), $e->getMessage() ) . '</p></div>';
			}
		}
	}
}

/**
 * Deletes a custom field
 */
function mrp_delete_custom_field() {

	$ajax_nonce = $_POST['nonce'];
	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) && current_user_can( 'mrp_manage_ratings' ) ) {

		$custom_field_id = isset( $_POST['customFieldId'] ) && is_numeric( $_POST['customFieldId'] ) ? intval( $_POST['customFieldId'] ) : null;

		$messages_html = '<div class="updated"><p>' . __( 'Custom field deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
			
		try {
			global $wpdb;
				
			$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME, 
					array( 'custom_field_id' => $custom_field_id ),
					array( '%d' )
			);

			$query = 'DROP TABLE ' . $wpdb->prefix . 'mrp_custom_field_' . esc_sql( $custom_field_id );
			$wpdb->query( $query );

			$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
					array( 'item_type' => 'custom-field', 'item_id' => custom_field_id),
					array( '%d' )
			);
				
			mrp_clean_db( false );

		} catch ( Exception $e ) {
			$messages_html = '<div class="error"><p>' . sprintf( __( 'An error has occured. %s', 'multi-rating-pro' ), $e->getMessage() ) . '</p></div>';
		}

		echo json_encode( array(
				'success' => true,
				'data' => array( 'messages_html' => $messages_html )
		) );
	}

	die();
}
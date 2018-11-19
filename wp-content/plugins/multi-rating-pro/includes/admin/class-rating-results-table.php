<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


/**
 * MRP_Rating_Results_Table class
 *
 * @author dpowney
 *
 */
class MRP_Rating_Results_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	POST_ID_COLUMN = 'post_id',
	RATING_FORM_ID_COLUMN = 'rating_form_id',
	TITLE_COLUMN = 'title',
	RATING_RESULT_COLUMN = 'rating_result',
	SHORTCODE_COLUMN = 'shortcode',
	COUNT_ENTRIES_COLUMN = 'count_entries',
	ACTION_COLUMN = 'action',
	DELETE_CHECKBOX = 'delete[]';

	/**
	 * Constructor
	 */
	function __construct() {

		parent::__construct( array(
				'singular'=> __( 'Result', 'multi-rating-pro' ),
				'plural' => __( 'Results', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {

		if ( $which == "top" ){

			$post_id = '';
			if ( isset( $_REQUEST['post-id'] ) ) {
				$post_id = $_REQUEST['post-id'];
			}

			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
			if ( isset( $_REQUEST['rating-form-id'] ) && strlen( trim( $_REQUEST['rating-form-id'] ) ) > 0 ) {
				$rating_form_id = $_REQUEST['rating-form-id'];
			}

			$sort_by = '';
			if ( isset( $_REQUEST['sort-by'] ) ) {
				$sort_by = $_REQUEST['sort-by'];
			}

			global $wpdb;
			?>

			<div class="alignleft filters">

				<?php
				mrp_posts_select( $post_id, true );
				mrp_rating_form_select( $rating_form_id, false, true );
				?>

				<label for="sort-by"><?php _e('Sort By', 'multi-rating-pro' ); ?></label>
				<select id="sort-by" name="sort-by">
					<option value=""></option>
					<option value="highest_rated" <?php if ( $sort_by == 'highest_rated' ) { echo 'selected="selected"'; } ?>><?php _e( 'Highest Rated', 'multi-rating-pro' ); ?></option>
					<option value="lowest_rated" <?php if ( $sort_by == 'lowest_rated' ) { echo 'selected="selected"'; } ?>><?php _e( 'Lowest Rated', 'multi-rating-pro' ); ?></option>
					<option value="most_entries" <?php if ( $sort_by == 'most_entries' ) { echo 'selected="selected"'; } ?>><?php _e( 'Most Entries', 'multi-rating-pro' ); ?></option>
					<option value="post_title_asc" <?php if ( $sort_by == 'post_title_asc' ) { echo 'selected="selected"'; } ?>><?php _e( 'Post Title Ascending', 'multi-rating-pro' ); ?></option>
					<option value="post_title_desc" <?php if ( $sort_by == 'post_title_desc' ) { echo 'selected="selected"'; } ?>><?php _e( 'Post Title Descending', 'multi-rating-pro' ); ?></option>
				</select>

				<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
			</div>

			<?php
		}

		if ( $which == "bottom" ){

		}
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_columns()
	 */
	function get_columns() {

		$columns = array(
				MRP_Rating_Results_Table::POST_ID_COLUMN => __( 'Post', 'multi-rating-pro' ),
				MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN => __( 'Rating Form', 'multi-rating-pro' ),
				MRP_Rating_Results_Table::RATING_RESULT_COLUMN => __( 'Overall Rating', 'multi-rating-pro' ),
				MRP_Rating_Results_Table::SHORTCODE_COLUMN => __( 'Shortcode', 'multi-rating-pro' ),
		);

		if ( current_user_can( 'mrp_manage_ratings' ) ) {
			$columns = array_merge( array( MRP_Rating_Results_Table::CHECKBOX_COLUMN => '<input type="checkbox" />'), $columns );
		}

		return $columns;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::prepare_items()
	 */
	function prepare_items() {
		global $wpdb;

		$this->process_bulk_action();

		// Register the columns
		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$rating_form_id = $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ];
		if ( isset( $_REQUEST['rating-form-id'] ) && strlen( trim( $_REQUEST['rating-form-id'] ) ) > 0 ) {
			$rating_form_id = $_REQUEST['rating-form-id'];
		}
		$post_id = ( isset( $_REQUEST['post-id'] ) && strlen( trim( $_REQUEST['post-id'] ) ) > 0 ) ? $_REQUEST['post-id'] : null;
		$sort_by = ( isset( $_REQUEST['sort-by'] )  && strlen( trim( $_REQUEST['sort-by'] ) ) > 0 ) ? $_REQUEST['sort-by'] : null;
		$to_date = null;
		$from_date = null;
		$approved_entries_only = true;
		$approved_comments_only = true;
		$published_posts_only = false;

		if ( $from_date != null && strlen( $from_date ) > 0 ) {
			list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
			if ( ! checkdate( $month , $day , $year ) ) {
				$from_date = null;
			}
		}

		if ( $to_date != null && strlen($to_date) > 0 ) {
			list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
			if ( ! checkdate( $month , $day , $year ) ) {
				$to_date = null;
			}
		}

		// pagination
		$items_per_page = 10;
		$page_num = ! empty( $_GET['paged'] ) ? $_GET['paged'] : '';
		if ( empty( $page_num ) || ! is_numeric( $page_num ) || $page_num <= 0 ) {
			$page_num = 1;
		}
		$offset = 0;
		if ( ! empty( $page_num ) && ! empty( $items_per_page ) ) {
			$offset = ( $page_num -1 ) * $items_per_page;
		}

		$rating_results_list = MRP_Multi_Rating_API::get_rating_result_list( apply_filters( 'mrp_rating_results_list_params', array(
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_id,
				'to_date' => $to_date,
				'from_date' => $from_date,
				'approved_entries_only' => $approved_entries_only,
				'approved_comments_only' => $approved_comments_only,
				'published_posts_only' => $published_posts_only,
				'sort_by' => $sort_by,
				'limit' => $items_per_page,
				'offset' => $offset
		) ) );

		$total_items = intval( $rating_results_list['count'] );
		$total_pages = ceil( $total_items / $items_per_page );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page' => $items_per_page
		) );

		$this->items = $rating_results_list['rating_results'];

	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {

		$rating_form_id = $item[MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN];
		$post_id = $item[MRP_Rating_Results_Table::POST_ID_COLUMN];

		switch( $column_name ) {

			case MRP_Rating_Results_Table::SHORTCODE_COLUMN : {

				echo '<code>[mrp_rating_result post_id="' . $post_id . '" rating_form_id="' . $rating_form_id . '"]</code>';
				break;
			}

			case MRP_Rating_Results_Table::POST_ID_COLUMN : {
				?>
				<a href="<?php echo get_the_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'),  $post_id ); ?> | </span>
					<?php
					if ( current_user_can( 'edit_post', $post_id ) ) {
						?>
						<span class="edit"><a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>"><?php _e( 'Edit', 'multi-rating-pro'); ?></a></span>
						<?php
					}
					?>
				</div>
				<?php

				break;
			}

			case MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN : {
				$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
				echo $rating_form['name'];
				?>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'),  $rating_form_id );
					if ( current_user_can( 'mrp_manage_ratings' ) ) {
						?> | </span>
						<span class="edit"><a href="?page=<?php echo MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG; ?>&rating-form-id=<?php echo $rating_form_id; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a>
						<?php
					} ?>
					</span>
				</div>
				<?php

				break;
			}

			case MRP_Rating_Results_Table::RATING_RESULT_COLUMN : {

				if ( $item[MRP_Rating_Results_Table::COUNT_ENTRIES_COLUMN] > 0 ) {

					$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

					if ( $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] == 'star_rating' ) {

						$star_rating_out_of = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];
						$icon_classes = MRP_Utils::get_icon_classes( 'dashicons' ); // dashicons for admin

						mrp_get_template_part( 'rating-result', 'star-rating', true, array(
								'icon_classes' => $icon_classes,
								'max_stars' => $star_rating_out_of,
								'star_result' => $item['adjusted_star_result'],
								'generate_microdata' => false,

						) );

					} else if ( $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] == 'score' ) {
						echo $item['adjusted_score_result'] . '/' . $item['total_max_option_value'];
					} else {
						echo $item['adjusted_percentage_result'] . '%';
					}

					echo ' (' . $item[MRP_Rating_Results_Table::COUNT_ENTRIES_COLUMN] . ')';

					$view_entries_url = 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&post-id='
							. $item[MRP_Rating_Results_Table::POST_ID_COLUMN] . '&rating-form-id=' . $item[MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN];
					?>
					<div class="row-actions">
						<span class="edit"><a href="<?php echo $view_entries_url; ?>"><?php _e( 'View Entries', 'multi-rating-pro' ); ?></a></span>
					</div>
					<?php

					// TODO here?

				} else {
					_e( 'None', 'multi-rating-pro' );
				}
				break;
			}

			case Rating_Item_Entry_Table::CHECKBOX_COLUMN :
				return $item[ $column_name ];
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
				'<input type="checkbox" name="' . MRP_Rating_Results_Table::DELETE_CHECKBOX . '" value="%s" />', $item[MRP_Rating_Results_Table::POST_ID_COLUMN] . '-' . $item[MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN]
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {

		$bulk_actions = array();

		if ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_delete_ratings' ) ) {
			$bulk_actions = array(
					'delete'    => __( 'Delete', 'multi-rating-pro' )
			);
		}

		return $bulk_actions;
	}

	/**
	 * Handles bulk actions
	 */
	function process_bulk_action() {

		if ( $this->current_action() == 'delete'
			&& ( current_user_can( 'mrp_manage_ratings' ) || current_user_can( 'mrp_delete_ratings' ) ) ) {

			$checked = ( is_array( $_REQUEST['delete'] ) ) ? $_REQUEST['delete'] : array( $_REQUEST['delete'] );

			foreach( $checked as $id ) {

				$key = preg_split('/[-]+/', $id);

				$post_id = $key[0];
				$rating_form_id = $key[1];

				$rating_entries = MRP_Multi_Rating_API::get_rating_entries( array(
						'post_id' => $post_id,
						'rating_form_id' => $rating_form_id,
						'approved_comments_only' => false,
						'entry_status' => '',
						'published_posts_only' => false
				) );

				foreach ( $rating_entries as $rating_entry ) {
					MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );
				}

			}

			echo '<div class="updated"><p>' . __( 'Rating results deleted successfully.', 'multi-rating-pro' ) . '</p></div>';
		}
	}
}

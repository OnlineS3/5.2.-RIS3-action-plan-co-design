<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * MRP_Rating_Entry_Table class
 * @author dpowney
 *
 */
class MRP_Rating_Entry_Table extends WP_List_Table {

	const
	CHECKBOX_COLUMN = 'cb',
	ID_COLUMN = 'id',
	POST_ID_COLUMN = 'post_id',
	RATING_FORM_ID_COLUMN = 'rating_form_id',
	DATE_COLUMN = 'date',
	STATUS_COLUMN = 'status',
	USER_COLUMN = 'user',
	EMAIL_COLUMN = 'email',
	RATING_DETAILS_COLUMN = 'rating_details',
	BULK_ACTION_CHECKBOX = 'bulk_action[]';

	public $total_count = 0;
	public $approved_count = 0;
	public $pending_count = 0;

	/**
	 * Constructor
	 */
	function __construct() {

		parent::__construct( array(
				'singular'=> __( 'Entry', 'multi-rating-pro' ),
				'plural' => __( 'Entries', 'multi-rating-pro' ),
				'ajax'	=> false
		) );
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 2.1.7
	 * @return array $views All the views available
	 */
	public function get_views() {
		$current        = isset( $_GET['entry-status'] ) ? $_GET['entry-status'] : '';

		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$approved_count = '&nbsp;<span class="count">(' . $this->approved_count . ')</span>';
		$pending_count  = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';

		$views = array(
				'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( array( 'entry-status', 'paged' ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'multi-rating-pro' ) . $total_count ),
				'approved'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'entry-status' => 'approved', 'paged' => FALSE ) ), $current === 'approved' ? ' class="current"' : '', __( 'Approved', 'multi-rating-pro' ) . $approved_count ),
				'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( array( 'entry-status' => 'pending', 'paged' => FALSE ) ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'multi-rating-pro' ) . $pending_count )
		);

		return apply_filters( 'mrp_rating_entry_views', $views );
	}


	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::extra_tablenav()
	 */
	function extra_tablenav( $which ) {

		if ( $which == 'top' ){

			$post_id = '';
			if ( isset( $_REQUEST['post-id'] ) ) {
				$post_id = $_REQUEST['post-id'];
			}

			$rating_form_id = '';
			if (isset( $_REQUEST['rating-form-id'] ) ) {
				$rating_form_id = $_REQUEST['rating-form-id'];
			}

			$comments_only_checked = '';
			if ( isset( $_REQUEST['comments-only'] ) && $_REQUEST['comments-only'] == 'true' ) {
				$comments_only_checked = ' checked="checked"';
			}

			$username = '';
			if (isset( $_REQUEST['username'] ) ) {
				$username = $_REQUEST['username'];
			}

			$to_date = '';
			if (isset( $_REQUEST['to-date'] ) ) {
				$to_date = $_REQUEST['to-date'];
			}

			$from_date = '';
			if (isset( $_REQUEST['from-date'] ) ) {
				$from_date = $_REQUEST['from-date'];
			}

			$sort_by = '';
			if ( isset( $_REQUEST['sort-by'] ) ) {
				$sort_by = $_REQUEST['sort-by'];
			}

			global $wpdb;
			?>

			<div class="alignleft filters">
				<input type="text" name="username" id="username" class="" autocomplete="off" placeholder="Username" value="<?php echo $username; ?>" />
				<input type="text" class="date-picker" autocomplete="off" name="from-date" placeholder="From - yyyy-MM-dd" id="from-date" value="<?php echo $from_date; ?>" />
				<input type="text" class="date-picker" autocomplete="off" name="to-date" placeholder="To - yyyy-MM-dd" id="to-date" value="<?php echo $to_date; ?>" />

				<?php
				mrp_posts_select( $post_id, true );
				mrp_rating_form_select( $rating_form_id, false, true );
				?>

				<label for="sort-by"><?php _e('Sort By', 'multi-rating-pro' ); ?></label>
				<select id="sort-by" name="sort-by">
					<option value=""></option>
					<option value="highest_rated" <?php if ( $sort_by == 'highest_rated' ) { echo 'selected="selected"'; } ?>><?php _e( 'Highest Rated', 'multi-rating-pro' ); ?></option>
					<option value="lowest_rated" <?php if ( $sort_by == 'lowest_rated' ) { echo 'selected="selected"'; } ?>><?php _e( 'Lowest Rated', 'multi-rating-pro' ); ?></option>
					<option value="most_recent" <?php if ( $sort_by == 'most_recent' ) { echo 'selected="selected"'; } ?>><?php _e( 'Most Recent', 'multi-rating-pro' ); ?></option>
					<option value="oldest" <?php if ( $sort_by == 'oldest' ) { echo 'selected="selected"'; } ?>><?php _e( 'Oldest', 'multi-rating-pro' ); ?></option>
				</select>

				<input type="checkbox" name="comments-only" id="comments-only" value="true" <?php echo $comments_only_checked; ?>/>
				<label for="comments-only"><?php _e( 'Comments only', 'multi-rating-pro' ); ?></label>

				<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
			</div>
			<?php
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

		$columns = array();

		if ( count( $this->get_bulk_actions() ) > 0 ) {
			$columns = array_merge( $columns, array(
				MRP_Rating_Entry_Table::CHECKBOX_COLUMN => '<input type="checkbox" />'
			) );
		}

		$columns = array_merge( $columns, array(
				MRP_Rating_Entry_Table::ID_COLUMN =>__( 'ID', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::POST_ID_COLUMN => __( 'Post', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::RATING_FORM_ID_COLUMN => __( 'Rating Form', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::USER_COLUMN => __( 'User', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::DATE_COLUMN => __( 'Date', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::RATING_DETAILS_COLUMN => __( 'Rating Details', 'multi-rating-pro' ),
				MRP_Rating_Entry_Table::STATUS_COLUMN => __( 'Status', 'multi-rating-pro'),
		) );

		return $columns;
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
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$entry_status = isset( $_REQUEST['entry-status'] ) ? $_REQUEST['entry-status'] : null;
		$comments_only = ( isset( $_REQUEST['comments-only']) && $_REQUEST['comments-only'] == "true" ) ? true : false;
		$to_date = isset( $_REQUEST['to-date'] ) ? $_REQUEST['to-date'] : null;
		$from_date = isset( $_REQUEST['from-date'] ) ? $_REQUEST['from-date'] : null;
		$post_id  = isset( $_REQUEST['post-id'] ) ? $_REQUEST['post-id'] : null;
		$rating_form_id  = isset( $_REQUEST['rating-form-id'] ) ? $_REQUEST['rating-form-id'] : null;
		$sort_by = ( isset( $_REQUEST['sort-by'] )  && strlen( trim( $_REQUEST['sort-by'] ) ) > 0 ) ? $_REQUEST['sort-by'] : null;
		$username = isset( $_REQUEST['username'] ) ? $_REQUEST['username'] : null;
		$user_id = null;
		if ( $username && strlen( trim( $username ) ) > 0) {
			$user = get_user_by( 'login', $username );
			if ( $user && $user->ID ) {
				$user_id = $user->ID;
			} else {
				$user_id = -1;
			}
		}
		$approved_comments_only = null;
		$published_posts_only = null;

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

		$rating_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( apply_filters( 'mrp_rating_entry_details_list_params', array(
				'rating_form_id' => $rating_form_id,
				'post_id' => $post_id,
				'to_date' => $to_date,
				'from_date' => $from_date,
				'comments_only' => $comments_only,
				'entry_status' => $entry_status,
				'approved_comments_only' => $approved_comments_only,
				'published_posts_only' => $published_posts_only,
				'user_id' => $user_id,
				'sort_by' => $sort_by,
				'limit' => $items_per_page,
				'offset' => $offset
		 ) ) );

		$total_items = $rating_results_list['count_entries'];

		$this->approved_count = $rating_results_list['count_approved'];
		$this->pending_count = $rating_results_list['count_pending'];
		$this->total_count = $rating_results_list['count_entries'];

		$total_pages = ceil( $total_items / $items_per_page );

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'total_pages' => $total_pages,
				'per_page' => $items_per_page
		) );

		$this->items = array();
		foreach ( $rating_results_list['rating_results'] as $rating_entry_result ) {
			$rating_entry_id = $rating_entry_result['rating_entry_id'];
			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );
			array_push( $this->items, array_merge( array( 'rating_result' => $rating_entry_result ), $rating_entry ) );
		}
	}

	/**
	 * Default column
	 * @param unknown_type $item
	 * @param unknown_type $column_name
	 * @return unknown|mixed
	 */
	function column_default( $item, $column_name ) {

		switch( $column_name ) {

			case MRP_Rating_Entry_Table::USER_COLUMN :
				if ( strlen( trim( $item['name'] ) ) == 0 ) {
					_e( 'Anonymous', 'multi-rating-pro' );
				} else if ( $item['user_id'] != 0 && current_user_can('edit_users' ) ) {
					?><a href="user-edit.php?user_id='<?php echo $item['user_id']; ?>"><?php echo $item['name']; ?></a>
					<div class="row-actions">
						<span class="id"><?php printf( __( 'ID: %d'),  $item['user_id'] ); ?></span>
					</div>
					<?php
				} else {
					echo $item['name'];
				}
				break;

			case MRP_Rating_Entry_Table::CHECKBOX_COLUMN :
				return $item[$column_name];
				break;

			case MRP_Rating_Entry_Table::STATUS_COLUMN : {
				$is_approved = $item['entry_status'] == "approved" ? true : false;
				$row_id = $item['rating_entry_id'];

				echo '<div id="entry_status_text-' . $row_id . '">';
				if ( $is_approved ) {
					_e( 'Approved', 'multi-rating-pro' );
				} else {
					_e( 'Pending', 'multi-rating-pro' );
				}
				echo '</div>';

				$anchor_class = $is_approved ? 'unapproved' : 'approved';
				$anchor_id = 'entry_status-' . $row_id;
				$anchor_text = $is_approved ? __( 'Unapprove', 'multi-rating-pro' ) : __( 'Approve', 'multi-rating-pro' );

				echo '<div class="row-actions"><a href="#" id="' . $anchor_id . '" class="' . $anchor_class . '">' . $anchor_text . '</a></div>';

				break;
			}

			case MRP_Rating_Results_Table::POST_ID_COLUMN : {
				$post_id = $item[MRP_Rating_Entry_Table::POST_ID_COLUMN];
				?>
				<a href="<?php echo get_the_permalink( $post_id ); ?>"><?php echo get_the_title( $post_id ); ?></a>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'), $post_id ) ; ?> | </span>
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

				$rating_form_id = $item[MRP_Rating_Entry_Table::RATING_FORM_ID_COLUMN];
				$rating_form = MRP_Multi_Rating_API::get_rating_form( $item[MRP_Rating_Results_Table::RATING_FORM_ID_COLUMN] );
				echo $rating_form['name'];
				?>
				<div class="row-actions">
					<span class="id"><?php printf( __( 'ID: %d'),  $rating_form_id );
					if ( current_user_can( 'mrp_manage_ratings' ) ) {
						?> | </span>
						<span class="edit"><a href="?page=<?php echo MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG; ?>&rating-form-id=<?php echo $rating_form_id; ?>"><?php _e( 'Edit', 'multi-rating-pro' ); ?></a>
						<?php
					}
					?>
					</span>
				</div>
				<?php
				break;
			}

			case MRP_Rating_Entry_Table::DATE_COLUMN :
				echo date( get_option( 'date_format' ), strtotime( $item['entry_date'] ) );
				// 'F j, Y, g:i A'
				break;

			case MRP_Rating_Entry_Table::ID_COLUMN :
				echo $item['rating_entry_id'];
				break;

			case MRP_Rating_Entry_Table::RATING_DETAILS_COLUMN :

				if ( strlen( $item['title'])  > 0 ) {
					echo '<span class="mrp-rating-details">';
					printf( __( 'Title: %s', 'multi-rating-pro' ), $item['title'] );
					echo '</span>';
				}

				$rating_result = $item['rating_result'];

				$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

				echo '<span class="mrp-rating-details">';
				$overall_rating = '';
				if ( $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] == 'star_rating' ) {

					$star_rating_out_of = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];
					$icon_classes = MRP_Utils::get_icon_classes( 'dashicons' ); // dashicons for admin

					ob_start();
					mrp_get_template_part( 'rating-result', 'star-rating', true, array(
							'icon_classes' => $icon_classes,
							'max_stars' => $star_rating_out_of,
							'star_result' => $rating_result['adjusted_star_result'],
							'generate_microdata' => false,
					) );
					$overall_rating = ob_get_contents();
					ob_end_clean();

				} else if ( $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] == 'score' ) {
					$overall_rating .= $rating_result['adjusted_score_result'] . '/' . $rating_result['total_max_option_value'];
				} else {
					$overall_rating .= $rating_result['adjusted_percentage_result'] . '%';
				}

				printf( __( 'Overall Rating: %s', 'multi-rating-pro'), $overall_rating );
				echo '</span>';

				$edit_comment_url = '';
				if ( strlen( $item['comment']) > 0 ) {
					echo '<span class="mrp-rating-details">';
					printf( __( 'Comment: %s', 'multi-rating-pro' ), wp_trim_words( $item['comment'], 20, null ) );

					$comment_id = $item['comment_id'];
					if ( $comment_id != '' ) {

						$comment = get_comment( $comment_id );
						if ( $comment != null && $comment->comment_approved != 1 ) {
							echo ' (<span style="color: orange;">' . wp_get_comment_status( $comment_id ) . '</span>)';
						}

						if ( current_user_can( 'moderate_comments' ) ) {
							$edit_comment_url = 'comment.php?action=editcomment&c=' . $comment_id;
						}
					}
					echo '</span>';
				}

				$edit_rating_url = 'admin.php?page=' . MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG . '&rating-entry-id=' . $item['rating_entry_id'];
				if ( isset( $_REQUEST['username'] ) ) {
					$edit_rating_url .= '&username=' . $_REQUEST['username'];
				}
				if ( isset( $_REQUEST['to-date'] ) )  {
					$edit_rating_url .= '&to-date=' . $_REQUEST['to-date'];
				}
				if ( isset( $_REQUEST['from-date'] ) )  {
					$edit_rating_url .= '&from-date=' . $_REQUEST['from-date'];
				}
				if ( isset( $_REQUEST['comments-only'] ) )  {
					$edit_rating_url .= '&comments-only=' . $_REQUEST['comments-only'];
				}
				if ( isset( $_REQUEST['paged'] ) )  {
					$edit_rating_url .= '&paged=' . $_REQUEST['paged'];
				}
				?>
				<div class="row-actions">
					<?php
					if ( current_user_can( 'mrp_moderate_ratings' ) || current_user_can( 'mrp_manage_ratings' ) ) {
						?><span class="edit"><a href="<?php echo $edit_rating_url; ?>"><?php _e( 'Edit Rating', 'multi-rating-pro' ); ?></a></span>
						<?php
					}
					if ( $item['comment_id'] != '' && current_user_can( 'moderate_comments' ) ) {
						?> | <span class="edit"><a href="<?php echo $edit_comment_url; ?>"><?php _e( 'Edit Comment', 'multi-rating-pro' ); ?></a></span><?php
					}
					?>
				</div>

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
	function column_cb( $item ) {
		return sprintf(
				'<input type="checkbox" name="' . MRP_Rating_Entry_Table::BULK_ACTION_CHECKBOX . '" value="%s" />', $item['rating_entry_id']
		);
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_List_Table::get_bulk_actions()
	 */
	function get_bulk_actions() {

		$bulk_actions = array();

		if ( current_user_can( 'mrp_moderate_ratings' ) || current_user_can( 'mrp_manage_ratings' ) ) {
			$bulk_actions = array_merge( array(
					'approve' 		=> __( 'Approve', 'multi-rating-pro' ),
					'unapprove'		=> __( 'Unapprove', 'multi-rating-pro ')
			), $bulk_actions );
		}

		if ( current_user_can( 'mrp_delete_ratings' ) || current_user_can( 'mrp_manage_ratings' ) ) {
			$bulk_actions = array_merge( array(
					'delete' 		=> __( 'Delete', 'multi-rating-pro' )
			), $bulk_actions );
		}

		return $bulk_actions;
	}

	/**
	 * Handles bulk actions: delete, approve and unapprove
	 */
	function process_bulk_action() {

		if ( ! isset( $_REQUEST['bulk_action'] ) ) {
			return;
		}

		if ( $this->current_action() === 'delete'
			&& ! ( current_user_can( 'mrp_delete_ratings' ) || current_user_can( 'mrp_manage_ratings' ) ) ) {
			return; // should not be here
		}

		global $wpdb;

		$checked = ( is_array( $_REQUEST['bulk_action'] ) ) ? $_REQUEST['bulk_action'] : array( $_REQUEST['bulk_action'] );

		try {

			foreach( $checked as $rating_entry_id ) {
				$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );

				if ( $this->current_action() === 'delete' ) {
					MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );
				} else if ( $this->current_action() === 'approve' || $this->current_action() === 'unapprove' ) {
					$rating_entry['entry_status'] = ( $this->current_action() === 'approve' ? 'approved' : 'pending' );
					MRP_Multi_Rating_API::save_rating_entry( $rating_entry, false );
				}

			}

			if ( $this->current_action() === 'delete' ) {
				echo '<div class="updated"><p>' . __( 'Entries deleted successfully', 'multi-rating-pro' ) . '</p></div>';
			} else if ( $this->current_action() === 'approve' ) {
				echo '<div class="updated"><p>' . __( 'Entries approved successfully', 'multi-rating-pro' ) . '</p></div>';
			} else if ( $this->current_action() === 'unapprove' ) {
				echo '<div class="updated"><p>' . __( 'Entries unapproved successfully', 'multi-rating-pro' ) . '</p></div>';
			}

		} catch ( Exception $e ) {
			echo '<div class="error"><p>' . sprintf( __( 'An error has occured. %s', 'multi-rating-pro' ), $e->getMessage() ) . '</p></div>';
		}
	}

	/**
	 * Updates entry status
	 */
	public static function update_entry_status() {

		$ajax_nonce = $_POST['nonce'];
		if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {

			$rating_entry_id = ( isset( $_POST['ratingEntryId'] ) && is_numeric( $_POST['ratingEntryId'] ) ) ? intval( $_POST['ratingEntryId'] ) : null;
			$entry_status = ( $_POST['entryStatus'] == 'approved' ) ? 'approved' : 'pending';

			if ( ! $rating_entry_id ) {
				die();
			}

			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_id ) );

			$rating_entry['entry_status'] = $entry_status;

			$validation_results = MRP_Multi_Rating_API::save_rating_entry( $rating_entry, false );

			echo json_encode( array (
					'entry_status' => $entry_status,
					'rating_entry_id' => $rating_entry_id,
					'validation_results' => $validation_results
			) );
		}

		die();
	}
}

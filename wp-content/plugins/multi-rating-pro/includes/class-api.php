<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * API functions for Multi Rating Pro
 *
 * @author dpowney
 *
 */
class MRP_Multi_Rating_API {

	/**
	 * Returns whether a user has already submitted a rating form for a post
	 *
	 * @param array $params with rating_form_id, post_id, user_id and rating_entry_id
	 * @return rating entry id if exists, otherwise false.
	 */
	public static function user_rating_exists( $params = array() ) {

		/*
		 * Backwards compatibility for additional function parameters:
		 * $rating_form_id, $post_id, $user_id, $rating_entry_id = null
		 */
		if ( func_num_args() > 1 ) {

			$temp_params = $params;
			$params = array();

			// For some reason, probably due to the parameter default value, func_get_arg(0)
			// gets cast to an array....
			if ( $temp_params !== null ) {
				$params['rating_form_id'] = $temp_params;
			}

			if ( func_num_args() >= 2 && func_get_arg(1) !== null ) {
				$params['post_id'] = func_get_arg(1);
			}

			if ( func_num_args() >= 3  && func_get_arg(2) !== null ) {
				$params['user_id'] = func_get_arg(2);
			}

			if ( func_num_args() >= 4  && func_get_arg(3) !== null ) {
				$params['rating_entry_id'] = func_get_arg(3);
			}
		}

		$rating_form_id = isset( $params['rating_form_id'] ) && is_numeric( $params['rating_form_id'] ) ? intval( $params['rating_form_id'] ) : null;
		$post_id = isset( $params['post_id'] )  && is_numeric( $params['post_id'] )? intval( $params['post_id'] ) : null;
		$user_id = isset( $params['user_id'] )  && is_numeric( $params['user_id'] )? intval( $params['user_id'] ) : null;
		$rating_entry_id = isset( $params['rating_entry_id'] )  && is_numeric( $params['rating_entry_id'] )? intval( $params['rating_entry_id'] ) : null;;

		if ( $user_id == null || $user_id == 0 || ! ( ( $rating_form_id && $post_id ) || $rating_entry_id ) ) {
			return false;
		}

		global $wpdb;

		$query_from = 'SELECT rating_item_entry_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie';
		$query_join = apply_filters( 'mrp_user_rating_exists_query_join', '', 'rie', $params );

		$query_args = array();
		$query_where = ' WHERE rie.user_id = %d';
		array_push( $query_args, $user_id );

		if ( $rating_form_id && $post_id && $user_id != 0 ) {

			$query_where .= ' AND ' . apply_filters( 'mrp_user_rating_exists_query_where_post', $wpdb->prepare( ' rie.post_id = %d', $post_id ), $params, 'rie' );
			$query_where .= ' AND rie.rating_form_id = %d';
			array_push( $query_args, $rating_form_id );
		}

		if ( $rating_entry_id ) {

			$query_where .= ' AND rie.rating_item_entry_id = %d';
			array_push( $query_args, $rating_entry_id );
		}

		if ( count( $query_args ) > 0 ) {
			$query_where = $wpdb->prepare( $query_where, $query_args );
		}

		$query_where = apply_filters( 'mrp_user_rating_exists_query_where', $query_where, array(), 'rie' );

		$result = $wpdb->get_var( $query_from . $query_join . $query_where, 0 );

		if ( $result && is_numeric( $result ) && $result > 0 ) {
			return $result;
		}

		return false;
	}


	/**
	 * Gets rating results list given a rating form id and filters.
	 *
	 * @param array $params
	 * @return list of rating results
	 */
	public static function get_rating_result_list( $params = array() ) {

		extract( wp_parse_args( $params, array(
				'taxonomy' => null,
				'term_id' => 0,
				'limit' => 10,
				'offset' => 0,
				'rating_form_id' => null,
				'sort_by' => 'highest_rated',
				'entry_status' => 'approved',
				'approved_comments_only' => true,
				'published_posts_only' => true,
				'post_id' => null,
				'rating_item_ids' => null,
				'user_roles' => null,
				'rating_entry_ids' => null,
				'user_id' => null,
				'comments_only' => null,
				'from_date' => null,
				'to_date' => null,
				'post_ids' => null
		) ) );

		// Applicable filters for rating_result: entry_status, approved_comments_only, rating_item_ids,
		// user_roles, rating_entry_ids, user_id, comments_only, from_date and to_date
		$filter_params = array(
				'rating_item_ids' => $rating_item_ids,
				'entry_status' => $entry_status,
				'approved_comments_only' => $approved_comments_only,
				'user_roles' => $user_roles,
				'rating_entry_ids' => $rating_entry_ids,
				'user_id' => $user_id,
				'comments_only' => $comments_only,
				'from_date' => $from_date,
				'to_date' => $to_date
		);
		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $filter_params );

		global $wpdb;

		/*
		 * Select
		 */
		$query_select = 'SELECT post_id, rating_form_id, star_result, adjusted_star_result, score_result, adjusted_score_result, total_max_option_value,'
				. 'percentage_result, adjusted_percentage_result, count_entries';

		$query_select = apply_filters( 'mrp_rating_results_query_select', $query_select, $params );

		/*
		 * From
		*/
		$query_from = ' FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME;
		$query_from = apply_filters( 'mrp_rating_results_query_from', $query_from, $params );

		/*
		 * Join
		*/
		$query_join = '';
		if ( $published_posts_only || $sort_by == 'post_title_asc' ||
				$sort_by == 'post_title_desc' || $taxonomy != null) {
			$query_join = ' LEFT JOIN ' . $wpdb->posts . ' p ON post_id = p.ID';
		}

		if ( $taxonomy != null ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel ON rel.object_id = p.ID';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t ON t.term_id = tax.term_id';
		}

		$query_join = apply_filters( 'mrp_rating_results_query_join', $query_join, null, $params );

		/*
		 * Where
		*/
		$query_where = ' WHERE rating_entry_id IS NULL AND rating_item_id IS NULL AND filters_hash = %s';
		$where_args = array();
		array_push( $where_args, $filters_hash );
		$added_to_query = true;

		if ( isset( $post_id ) ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= apply_filters( 'mrp_rating_results_query_where_post', $wpdb->prepare( ' post_id = %d', $post_id ), $params );

			$added_to_query = true;
		}

		if ( $taxonomy ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' tax.taxonomy = %s';
			array_push( $where_args, $taxonomy );

			if ( $term_id ) {
				$query_where .= ' AND t.term_id = %d';
				array_push( $where_args, $term_id );
			}

			$added_to_query = true;
		}

		if ( isset( $rating_form_id ) ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rating_form_id = %d';
			array_push( $where_args, $rating_form_id );
			$added_to_query = true;
		}

		$post_ids = ( strlen( $post_ids ) > 0 ) ? explode( ',', $post_ids ) : null;
		if ( $post_ids && count( $post_ids ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' post_id IN ( ' . implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) ) . ' ) ';
			$where_args = array_merge( $where_args, $post_ids );
			$added_to_query = true;
		}

		if ( $published_posts_only ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' ( p.post_status = "publish" OR p.post_type = "attachment" ) ';
			$added_to_query = true;
		}

		if ( $added_to_query ) {
			$query_where .= ' AND';
		}
		$query_where .= ' count_entries > 0';

		if ( count ( $where_args ) > 0 ) {
			$query_where = $wpdb->prepare( $query_where, $where_args );
		}

		$query_where = apply_filters( 'mrp_rating_results_query_where', $query_where, $params );

		/*
		 * Group by
		*/

		// No need to group by if query is only concered with the mrp_rating_result table...
		$query_group_by = '';
		if ( $published_posts_only || $sort_by == 'post_title_asc' ||
				$sort_by == 'post_title_desc' || $taxonomy != null) {
			$query_group_by = ' GROUP BY post_id, rating_form_id';
		}
		$query_group_by = apply_filters( 'mrp_rating_results_query_group_by', $query_group_by, $params );

		/*
		 * Order by
		*/


		$query_order_by = ' ORDER BY adjusted_star_result DESC, count_entries DESC'; // highest rated
		if ( $sort_by == 'post_title_asc' ) {
			$query_order_by = ' ORDER BY p.post_title ASC';
		} else if ( $sort_by == 'post_title_desc' ) {
			$query_order_by = ' ORDER BY p.post_title DESC';
		} else if ( $sort_by == 'most_entries' ) {
			$query_order_by = ' ORDER BY count_entries DESC';
		} else if ( $sort_by == 'lowest_rated' ) {
			$query_order_by = ' ORDER BY adjusted_star_result ASC, count_entries ASC';
		}
		$query_order_by = apply_filters( 'mrp_rating_results_query_order_by', $query_order_by, $params );

		/*
		 * Limit
		*/
		$query_limit = '';
		if ( $limit && is_numeric( $limit ) ) {
			if ( intval( $limit ) > 0 ) {
				$query_limit .= ' LIMIT ' . intval( $offset ) . ', ' . intval( $limit );
			}
		}
		$query_limit = apply_filters( 'mrp_rating_results_query_limit', $query_limit, $params );

		/*
		 * Query results
		*/
		$query = $query_select . $query_from . $query_join . $query_where . $query_group_by . $query_order_by .  $query_limit;
		$query = apply_filters( 'mrp_rating_results_query', $query, $params );

		/*
		 * First check if any calculated ratings are missing. If there are, start background
		 * process to recalculate them
		 */
		$missing_query = 'SELECT DISTINCT re.post_id, re.rating_form_id FROM ' . $wpdb->prefix
				. MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' re';

		$missing_query .= apply_filters( 'mrp_missing_rating_results_query_join', '', null, $params );

		$missing_query .= ' WHERE NOT EXISTS ( '
				. $query_select . $query_from . $query_join . $query_where . $query_group_by . ' )';

		$missing_query .= apply_filters( 'mrp_missing_rating_results_query_where', '', $params, null );

		$results = $wpdb->get_results( $missing_query );

		$rating_results = array();
		foreach ( $results as $row ) {

			$rating_result = mrp_calculate_rating_result( array_merge( $filter_params, array(
					'post_id' => $row->post_id,
					'rating_form_id' => $row->rating_form_id
			) ) );

			array_push( $rating_results,  $rating_result );
		}

		if ( count( $rating_results ) > 0 ) {

			foreach ( array_chunk( $rating_results, 1000 ) as $rating_results_chunk ) {

				global $wpdb;
				$insert_query = 'INSERT INTO ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME
						. ' ( rating_form_id, post_id, filters_hash, count_entries,'
						. ' star_result, adjusted_star_result, score_result, adjusted_score_result,'
						. ' total_max_option_value, percentage_result, adjusted_percentage_result,'
						. ' last_updated_dt ) VALUES';

				$count = count( $rating_results_chunk );

				$index = 0;
				foreach ( $rating_results_chunk as $rating_result ) {

					$insert_query .= ' ( ' . $rating_result['rating_form_id'] .', ' . $rating_result['post_id'] . ', '
							. '"' . $filters_hash . '", ' . $rating_result['count_entries'] . ', '
							. $rating_result['star_result'] . ', ' . $rating_result['adjusted_star_result'] . ', '
							. $rating_result['score_result'] . ', ' . $rating_result['adjusted_score_result'] . ', '
							. $rating_result['total_max_option_value'] . ', ' . $rating_result['percentage_result'] . ', '
							. $rating_result['adjusted_percentage_result'] . ', "' . current_time( 'mysql' ) . '" )';

					$index++;

					if ( $index < $count ) {
						$insert_query .= ', ';
					}

					// Update post meta table
					update_post_meta( $rating_result['post_id'], MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_result['rating_form_id'], $rating_result );
					update_post_meta( $rating_result['post_id'], MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_result['rating_form_id'] . '_star_rating', $rating_result['adjusted_star_result'] );
					update_post_meta( $rating_result['post_id'], MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_result['rating_form_id'] . '_count_entries', $rating_result['count_entries'] );

					do_action( 'mrp_save_rating_result', $rating_result, $params );
				}

				$wpdb->query( $insert_query );

				$wpdb->show_errors();
			}
		}

		/*
		 * Now we get the rating results
		 */
		$results = $wpdb->get_results( $query, ARRAY_A, 0 );

		$rating_results = array();
		foreach ( $results as $row ) {

			array_push( $rating_results, array(
					'adjusted_star_result' => floatval( $row['adjusted_star_result'] ),
					'star_result' => floatval( $row['star_result'] ),
					'adjusted_score_result' => floatval( $row['adjusted_score_result'] ),
					'score_result' => floatval( $row['score_result'] ),
					'adjusted_percentage_result' => floatval( $row['adjusted_percentage_result'] ),
					'percentage_result' => floatval( $row['percentage_result'] ),
					'total_max_option_value' => $row['total_max_option_value'],
					'count_entries' => isset( $row['count_entries'] ) ? intval( $row['count_entries'] ) : null,
					'post_id' => isset( $row['post_id'] ) ? intval( $row['post_id'] ) : $post_id,
					'rating_form_id' => isset( $row['rating_form_id'] ) ? intval( $row['rating_form_id'] ) : $rating_form_id
			) );

		}

		/*
		 * Get some counts for paging and stats
		 */

		$query_count = 'SELECT COUNT(DISTINCT ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . '.id) ' . $query_from . $query_join . $query_where . $query_order_by;
		$count = $wpdb->get_var( $query_count );

		return array(
				'rating_results' => $rating_results,
				'count' => $count
		);
	}

	/**
	 * Gets rating entry results list given a rating form id, post id and filters.
	 *
	 * @param array $params
	 * @return array list of rating entry results
	 */
	public static function get_rating_entry_result_list( $params = array() ) {

		extract( wp_parse_args( $params, array(
				'taxonomy' => null,
				'term_id' => 0,
				'limit' => null,
				'offset' => 0,
				'rating_form_id' => null,
				'sort_by' => 'highest_rated',
				'entry_status' => 'approved',
				'approved_comments_only' => true,
				'published_posts_only' => true,
				'post_id' => null,
				'rating_item_ids' => null,
				'user_roles' => null,
				'rating_entry_ids' => null,
				'user_id' => null,
				'comments_only' => null,
				'from_date' => null,
				'to_date' => null,
				'post_ids' => null
		) ) );

		global $wpdb;

		// Applicable filters for rating_entry_result: rating_item_ids
		$filter_params = array(
				'rating_item_ids' => $rating_item_ids
		);
		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $filter_params );

		/*
		 * Select
		 */
		$query_select = 'SELECT rr.star_result, rr.adjusted_star_result, rr.score_result, rr.adjusted_score_result,'
				. ' rr.total_max_option_value, rr.percentage_result, rr.adjusted_percentage_result, rr.post_id,'
				. ' rr.rating_form_id, rie.rating_item_entry_id, rie.user_id, rie.entry_date';
		$query_select = apply_filters( 'mrp_rating_entry_result_list_query_select', $query_select, $params );

		/*
		 * From
		*/
		$query_from = ' FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' rr';
		$query_from = apply_filters( 'mrp_rating_entry_result_list_query_from', $query_from, $params );

		/*
		 * Join
		 */
		$query_join = ' INNER JOIN ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME
				. ' rie ON rr.rating_entry_id = rie.rating_item_entry_id';

		if ( $published_posts_only || $taxonomy != null || $sort_by == 'post_title_asc'
				|| $sort_by == 'post_title_desc' ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->posts . ' p ON rr.post_id = p.ID';
		}

		if ( $taxonomy != null ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel ON rel.object_id = p.ID';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t ON t.term_id = tax.term_id';
		}

		if ( $user_roles != null ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->users . ' u ON u.ID = rie.user_id';
			$query_join .= ' INNER JOIN ' . $wpdb->prefix . 'usermeta um ON u.ID = um.user_id';
		}

		if ( $approved_comments_only ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->comments . ' c ON rie.comment_id = c.comment_ID';
		}

		$query_join = apply_filters( 'mrp_rating_entry_result_list_query_join', $query_join, 'rr', $params );

		/*
		 * Where
		*/
		$query_where = ' WHERE rr.rating_entry_id IS NOT NULL AND rr.filters_hash = %s AND rr.rating_item_id IS NULL';
		$where_args = array();
		array_push( $where_args, $filters_hash );
		$added_to_query = true;

		if ( $post_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= apply_filters( 'mrp_rating_entry_result_list_query_where_post', $wpdb->prepare( ' rr.post_id = %d', $post_id ), $params, 'rr' );
			$added_to_query = true;
		}

		if ( $user_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.user_id = %d';
			array_push( $where_args, $user_id );
			$added_to_query = true;
		}

		if ( $user_roles != null ) {
			$user_roles = explode( ',', $user_roles );

			if ( is_array( $user_roles)  && count( $user_roles ) ) {

				if ( $added_to_query ) {
					$query_where .= ' AND';
					$added_to_query = true;
				}

				$query_where .= ' u.ID = rie.user_id AND um.meta_key  = "' . $wpdb->prefix . 'capabilities" AND (';
				$index = 1;
				foreach ( $user_roles as $user_role ) {
					$query_where .= ' um.meta_value LIKE "%%' . $wpdb->esc_like( $user_role ) . '%%"';
					if ( $index < count( $user_roles ) ) {
						$query_where .= ' OR';
					}
					$index++;
				}
				$query_where .= ' ) ';
				$added_to_query = true;
			}
		}

		$rating_entry_ids = ( strlen( $rating_entry_ids ) > 0 ) ? explode( ',', $rating_entry_ids ) : null;
		if ( $rating_entry_ids && count( $rating_entry_ids ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
				$added_to_query = true;
			}

			$query_where .= ' rr.rating_entry_id IN ( ' . implode( ', ', array_fill( 0, count( $rating_entry_ids ), '%d' ) ) . ' ) ';
			$where_args = array_merge( $where_args, $rating_entry_ids );
		}

		$post_ids = ( strlen( $post_ids ) > 0 ) ? explode( ',', $post_ids ) : null;
		if ( $post_ids && count( $post_ids ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
				$added_to_query = true;
			}

			$query_where .= ' rr.post_id IN ( ' . implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) ) . ' ) ';
			$where_args = array_merge( $where_args, $post_ids );
		}

		if ( $from_date ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_date >= %s';
			array_push( $where_args, $from_date );
			$added_to_query = true;
		}

		if ( $to_date ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_date <= %s';
			array_push( $where_args, $to_date );
			$added_to_query = true;
		}

		if ( $taxonomy ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' tax.taxonomy = %s';
			array_push( $where_args, $taxonomy );

			if ( $term_id ) {
			 	$query_where .= ' AND t.term_id = %d';
			 	array_push( $where_args, $term_id );
			}

			$added_to_query = true;
		}

		if ( $rating_form_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rr.rating_form_id = %d';
			array_push( $where_args, $rating_form_id );
			$added_to_query = true;
		}

		if ( $comments_only == true ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= '  ( ( rie.comment != "" AND rie.comment IS NOT NULL ) OR rie.comment_id IS NOT NULL )';
			$added_to_query = true;
		}

		if ( $approved_comments_only ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' ( ( rie.comment_id = "" OR rie.comment_id IS NULL ) OR ( rie.comment_id IS NOT NULL AND c.comment_approved = "1" ) )';
			$added_to_query = true;
		}

		if ( $published_posts_only ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' ( p.post_status = "publish" OR p.post_type = "attachment" ) ';
			$added_to_query = true;
		}

		$query_where2 = $query_where;
		$where_args2 = $where_args;

		if ( $entry_status && strlen( $entry_status ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_status = %s';
			array_push( $where_args, $entry_status );
			$added_to_query = true;
		}

		if ( count( $where_args ) > 0 ) {
			$query_where = $wpdb->prepare( $query_where, $where_args );
		}

		$query_where = apply_filters( 'mrp_rating_entry_result_list_query_where', $query_where, $params );

		/*
		 * Group by
		*/
		$query_group_by = ' GROUP BY rie.rating_item_entry_id, rr.id';
		$query_group_by = apply_filters( 'mrp_rating_entry_result_list_query_group_by', $query_group_by, $params );

		/*
		 * Order by
		*/
		$query_order_by = ' ORDER BY rr.adjusted_star_result DESC'; // highest rated by default
		if ( $sort_by == 'lowest_rated' ) {
			$query_order_by = ' ORDER BY rr.adjusted_star_result ASC';
		} else if ( $sort_by == 'most_recent' ) {
			$query_order_by = ' ORDER BY rie.entry_date DESC';
		} else if ( $sort_by == 'oldest' ) {
			$query_order_by = ' ORDER BY rie.entry_date ASC';
		} else if ( $sort_by == 'post_title_asc' ) {
			$query_order_by = ' ORDER BY p.post_title ASC';
		} else if ( $sort_by == 'post_title_desc' ) {
			$query_order_by = ' ORDER BY p.post_title DESC';
		}

		$query_order_by = apply_filters( 'mrp_rating_entry_result_list_query_order_by', $query_order_by, $params );

		/*
		 * Limit
		*/
		$query_limit = '';
		if ( $limit && is_numeric( $limit ) ) {
			if ( intval( $limit ) > 0 ) {
				$query_limit .= ' LIMIT ' . intval( $offset ) . ', ' . intval( $limit );
			}
		}
		$query_limit = apply_filters( 'mrp_rating_entry_result_list_query_limit', $query_limit, $params );

		/*
		 * Query results
		*/
		$query = $query_select . $query_from . $query_join . $query_where . $query_group_by . $query_order_by .  $query_limit;
		$query = apply_filters( 'mrp_rating_entry_result_list_query', $query, $params );

		/*
		 * First check if any calculated ratings are missing. If there are, start background
		 * process to recalculate them
		 */
		$missing_query = 'SELECT re.rating_item_entry_id, re.post_id, re.rating_form_id FROM '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' re';

		$missing_query .= apply_filters( 'mrp_missing_rating_entries_query_join', '', 're', $params );

		$missing_query .= ' WHERE NOT EXISTS (SELECT id FROM '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' rr WHERE rr.rating_entry_id = re.rating_item_entry_id '
				. 'AND rr.filters_hash = %s AND rr.post_id = re.post_id AND rr.rating_form_id = re.rating_form_id )';

		$query_args = array();
		array_push( $query_args, $filters_hash );

		if ( isset( $params['rating_form_id'] ) && $params['rating_form_id'] != null ) {
			$missing_query .= ' AND re.rating_form_id = %d';
			array_push( $query_args, $params['rating_form_id'] );
		}
		if ( isset( $params['post_id'] ) && $params['post_id'] != null && $params['post_id'] != 0 ) {
			$missing_params = $params; // instantiate new array in case params need to change...
			$missing_query .= ' AND ' . apply_filters( 'mrp_missing_rating_entries_query_where_post',
					$wpdb->prepare( 're.post_id = %d', $params['post_id'] ), $missing_params, 're' );
		}

		$results = $wpdb->get_results( $wpdb->prepare( $missing_query, $query_args ), ARRAY_A );

		if ( count( $results ) > 0 ) {

			$temp_rating_entry_ids = array();
			foreach ( $results as $row ) {
				array_push( $temp_rating_entry_ids, $row['rating_item_entry_id'] );
			}

			$rows = array();
			foreach ( array_chunk( $temp_rating_entry_ids, 1000 ) as $temp_rating_entry_ids_chunk ) {
				$entry_values_query = 'SELECT riev.rating_item_entry_id AS rating_entry_id, riev.rating_item_id, riev.value, rie.post_id, '
						. ' rie.rating_form_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
						. ' riev LEFT JOIN ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie ON'
						. ' riev.rating_item_entry_id = rie.rating_item_entry_id WHERE riev.rating_item_entry_id '
						. ' IN ( ' . implode( ', ', $temp_rating_entry_ids_chunk ) . ' ) ORDER BY riev.rating_item_entry_id';


				$rows = array_merge( $rows, $wpdb->get_results( $entry_values_query ) );
			}

			$rating_entry_results = array();
			$rating_entries = array();

			foreach ( $rows as $row ) {
				
				if ( ! isset( $rating_entries[$row->rating_entry_id] ) ) {

					$rating_item_values = array();
					$rating_item_values[$row->rating_item_id] = $row->value;

					$rating_entry = array(
							'rating_entry_id' => intval( $row->rating_entry_id ),
							'post_id' => intval( $row->post_id ),
							'rating_form_id' => intval( $row->rating_form_id ),
							'rating_item_values' => $rating_item_values
					);

					$rating_entries[$row->rating_entry_id] = $rating_entry;
				} else {

					$rating_entries[$row->rating_entry_id]['rating_item_values'][$row->rating_item_id] = $row->value;

				}
			}

			foreach ( $rating_entries as $rating_entry ) {

				$rating_entry_result = mrp_calculate_rating_entry_result( $rating_entry, array( 'rating_item_ids' => $rating_item_ids ) );

				if ( $rating_entry_result != null ) {
					array_push( $rating_entry_results, $rating_entry_result );
				}
			}

			if ( count( $rating_entry_results ) > 0 ) {

				foreach ( array_chunk( $rating_entry_results, 1000 ) as $rating_entry_result_chunk ) {

					$insert_query = 'INSERT INTO ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME
							. ' ( rating_form_id, post_id, rating_entry_id, filters_hash, star_result, adjusted_star_result,'
							. ' score_result, adjusted_score_result, total_max_option_value, percentage_result,'
							. ' adjusted_percentage_result, last_updated_dt ) VALUES';

					$count = count( $rating_entry_result_chunk );

					$index = 0;
					foreach ( $rating_entry_result_chunk as $rating_entry_result ) {

						$insert_query .= ' ( ' . $rating_entry_result['rating_form_id'] .', ' . $rating_entry_result['post_id'] . ', '
								. $rating_entry_result['rating_entry_id'] . ', "' . $filters_hash . '", ' . $rating_entry_result['star_result'] . ', '
								. $rating_entry_result['adjusted_star_result'] . ', ' . $rating_entry_result['score_result'] . ', '
								. $rating_entry_result['adjusted_score_result'] . ', ' . $rating_entry_result['total_max_option_value'] . ', '
								. $rating_entry_result['percentage_result'] . ', ' . $rating_entry_result['adjusted_percentage_result'] . ', '
								. '"' . current_time( 'mysql' ) . '" )';

						$index++;

						if ( $index < $count ) {
							$insert_query .= ', ';
						}
					}

					$wpdb->query( $insert_query );

					$wpdb->show_errors();
				}
			}

		}

		/*
		 * Now we get the rating entry results
		 */
		$results = $wpdb->get_results( $query, ARRAY_A, 0 );

		$rating_results = array();
		foreach ( $results as $row ) {

			array_push( $rating_results, array(
					'adjusted_star_result' => floatval( $row['adjusted_star_result'] ),
					'star_result' => floatval( $row['star_result'] ),
					'adjusted_score_result' => floatval( $row['adjusted_score_result'] ),
					'score_result' => floatval( $row['score_result'] ),
					'adjusted_percentage_result' => floatval( $row['adjusted_percentage_result'] ),
					'percentage_result' => floatval( $row['percentage_result'] ),
					'total_max_option_value' => $row['total_max_option_value'],
					'post_id' => intval( $row['post_id'] ),
					'rating_form_id' => intval( $row['rating_form_id'] ),
					'rating_entry_id' => intval( $row['rating_item_entry_id'] ),
					'user_id' => intval( $row['user_id'] ),
					'entry_date' => $row['entry_date']
			) );

		}

		/*
		 * Get some counts for paging and stats
		 */

		// does not include entry status
		if ( count( $where_args2 ) > 0 ) {
			$query_where2 = $wpdb->prepare( $query_where2, $where_args2 );
		}

		$query_where2 = apply_filters( 'mrp_rating_entry_result_list_query_where', $query_where2, $params );

		$query_count = 'SELECT COUNT(DISTINCT rating_entry_id) AS count_entries, SUM( CASE WHEN ( ( rie.comment != ""'
				. ' AND rie.comment IS NOT NULL ) OR ( rie.comment_id != "" AND rie.comment_id IS NOT NULL ) )'
				. ' THEN 1 ELSE 0 END ) AS count_comments, SUM( CASE WHEN rie.entry_status = "approved" THEN 1 ELSE 0 END ) AS'
				. ' count_approved, SUM( CASE WHEN rie.entry_status = "pending" THEN 1 ELSE 0 END ) AS count_pending,'
				. ' MAX( rie.entry_date ) AS most_recent_date, AVG( rr.adjusted_star_result )'
				. ' AS avg_rating_result ' . $query_from . $query_join . $query_where2;

		$row = $wpdb->get_row( $query_count );

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];

		return array(
				'rating_results' => $rating_results,
				'count_entries' => isset( $row->count_entries ) ? intval( $row->count_entries ) : 0,
				'count_comments' => isset( $row->count_comments ) ? intval( $row->count_comments ) : 0,
				'count_approved' => isset( $row->count_approved ) ? intval( $row->count_approved ) : 0,
				'count_pending' => isset( $row->count_pending ) ? intval( $row->count_pending ) : 0,
				'most_recent_date' => isset( $row->most_recent_date ) ? $row->most_recent_date : '',
				'avg_rating_result' => isset( $row->avg_rating_result ) ? (float) round( floatval( $row->avg_rating_result ), $decimal_places ) : 0
		);
	}

	/**
	 * Gets rating result give a post id, rating form id and filters.
	 *
	 * @param array $params
	 * @return array rating result
	 */
	public static function get_rating_result( $params = array() ) {

		$post_id = ( isset( $params['post_id'] ) && is_numeric( $params['post_id'] ) ) ? /* ( 'mrp_object_id', */ intval( $params['post_id'] ) /* ) */: null;
		$rating_form_id = ( isset( $params['rating_form_id'] ) && is_numeric( $params['rating_form_id'] ) ) ? intval( $params['rating_form_id'] ) : null;

		/*
		 * Support for deprecated function parameters
		 */
		if ( ! is_array( $params ) ) {
			$post_id = func_get_arg(0);

			if ( func_num_args() > 1 ) {
				$rating_form_id = func_get_arg(1);
			}

			// filters
			if ( func_num_args() > 2 ) {
				$params = func_get_arg(2);
			} else {
				$params = array();
			}

			$params['post_id'] = $post_id;
			$params['rating_form_id'] = $rating_form_id;

			if ( isset( $params['user_roles'] ) ) {
				$params['user_roles'] = implode( ', ', $params['user_roles'] );
			}
			if ( isset( $params['rating_item_ids'] ) ) {
				$params['rating_item_ids'] = implode( ', ', $params['rating_item_ids'] );
			}
		}

		// Applicable filters for rating_result: entry_status, approved_comments_only, rating_item_ids,
		// user_roles, rating_entry_ids, user_id, comments_only, from_date and to_date
		$filter_params = array(
				'rating_item_ids' => isset( $params['rating_item_ids'] ) ? $params['rating_item_ids'] : null,
				'entry_status' => isset( $params['entry_status'] ) ? $params['entry_status'] : 'approved',
				'approved_comments_only' => isset( $params['approved_comments_only'] ) ? $params['approved_comments_only'] : true,
				'user_roles' => isset( $params['user_roles'] ) ? $params['user_roles'] : null,
				'rating_entry_ids' => isset( $params['rating_entry_ids'] ) ? $params['rating_entry_ids'] : null,
				'user_id' => isset( $params['user_id'] ) ? $params['user_id'] : null,
				'comments_only' => isset( $params['comments_only'] ) ? $params['comments_only'] : false,
				'from_date' => isset( $params['from_date'] ) ? $params['from_date'] : null,
				'to_date' => isset( $params['to_date'] ) ? $params['to_date'] : null,
		);
		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $filter_params );

		if ( $post_id == null ) {
			return;
		}

		// Get rating result from the database if exists
		global $wpdb;

		$query_form = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' AS rr';
		$query_join = apply_filters( 'mrp_rating_result_query_join', '' , 'rr', $params );

		$query_args = array();
		$query_where = ' WHERE rr.rating_entry_id IS NULL AND rr.rating_item_id IS NULL AND rr.filters_hash = %s';
		array_push( $query_args, $filters_hash );

		$query_where .= ' AND ' . apply_filters( 'mrp_rating_result_query_where_post', $wpdb->prepare( ' rr.post_id = %d', $post_id ), $params, 'rr' );

		if ( $rating_form_id ) {
			$query_where .= ' AND rr.rating_form_id = %d';
			array_push( $query_args, $rating_form_id );
		}

		if ( count( $query_args ) > 0 ) {
			$query_where = $wpdb->prepare( $query_where, $query_args );
		}
		$query_where = apply_filters( 'mrp_rating_result_query_where', $query_where, $params, 'rr' );

		$query = apply_filters( 'mrp_rating_result_query', $query_form . $query_join . $query_where, $params );

		$row = $wpdb->get_row( $query, ARRAY_A, 0 );

		// If rating result does not exist in database, recalculate and save it
		if ( $row == null ) {
			$rating_result = mrp_calculate_rating_result( $params );
			MRP_Multi_Rating_API::save_rating_result( $rating_result, $filter_params );
			return $rating_result;
		}

		return array(
				'adjusted_star_result' => floatval( $row['adjusted_star_result'] ),
				'star_result' => floatval( $row['star_result'] ),
				'adjusted_score_result' => floatval( $row['adjusted_score_result'] ),
				'score_result' => floatval( $row['score_result'] ),
				'adjusted_percentage_result' => floatval( $row['adjusted_percentage_result'] ),
				'percentage_result' => floatval( $row['percentage_result'] ),
				'total_max_option_value' => $row['total_max_option_value'],
				'count_entries' => isset( $row['count_entries'] ) ? intval( $row['count_entries'] ) : null,
				'post_id' => isset( $row['post_id'] ) ? intval( $row['post_id'] ) : $post_id,
				'rating_form_id' => isset( $row['rating_form_id'] ) ? intval( $row['rating_form_id'] ) : $rating_form_id,
		);
	}

	/**
	 * Get rating entry result for a given rating entry id and filters. A rating entry result can be
	 * retrieved without a rating entry id only if a post id, rating form id and user id is provided.
	 *
	 * @param array $params
	 * @return array rating result
	 */
	public static function get_rating_entry_result( $params = array() ) {

		$post_id = ( isset( $params['post_id'] ) && is_numeric( $params['post_id'] ) ) ? intval( $params['post_id'] ) : null;
		$rating_form_id = ( isset( $params['rating_form_id'] ) && is_numeric( $params['rating_form_id'] ) ) ? intval( $params['rating_form_id'] ) : null;
		$rating_entry_id = ( isset( $params['rating_entry_id'] ) && is_numeric( $params['rating_entry_id'] ) ) ? intval( $params['rating_entry_id'] ) : null;
		$user_id = isset( $params['user_id'] ) ? $params['user_id'] : null;
		$rating_item_ids = isset( $params['rating_item_ids'] ) ? $params['rating_item_ids'] : null;

		if ( $rating_entry_id == null && ( $post_id && $rating_form_id && $user_id != 0 ) ) {
			$rating_entry_id = MRP_Multi_Rating_API::user_rating_exists( array(
					'rating_form_id' => $rating_form_id,
					'post_id' => $post_id,
					'user_id' => $user_id,
					'rating_entry_id' => $rating_entry_id
			) );
		}

		if ( $rating_entry_id == null ) {
			return;
		}

		// Get rating entry result from database if exists
		global $wpdb;

		// Applicable filters for rating_entry_result: rating_item_ids
		$filter_params = array(
				'rating_item_ids' =>  $rating_item_ids
		);
		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $filter_params );

		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' WHERE rating_entry_id = %d AND filters_hash = %s';
		$query = $wpdb->prepare( $query, $rating_entry_id, $filters_hash );

		$query = apply_filters( 'mrp_rating_entry_result_query', $query, $params );

		$row = $wpdb->get_row( $query, ARRAY_A, 0 );

		// If rating entry result does not exist in database, recalculate and save it
		if ( $row == null ) {
			$rating_result = mrp_calculate_rating_entry_result( $rating_entry_id );
			MRP_Multi_Rating_API::save_rating_result( $rating_result, $params );
			return $rating_result;
		}

		return array(
				'adjusted_star_result' => floatval( $row['adjusted_star_result'] ),
				'star_result' => floatval( $row['star_result'] ),
				'adjusted_score_result' => floatval( $row['adjusted_score_result'] ),
				'score_result' => floatval	( $row['score_result'] ),
				'adjusted_percentage_result' => floatval( $row['adjusted_percentage_result'] ),
				'percentage_result' => floatval( $row['percentage_result'] ),
				'total_max_option_value' => $row['total_max_option_value'],
				'post_id' => isset( $row['post_id'] ) ? intval( $row['post_id'] ) : $post_id,
				'rating_form_id' => isset( $row['rating_form_id'] ) ? intval( $row['rating_form_id'] ) : $rating_form_id,
				'rating_entry_id' => isset( $row['rating_entry_id'] ) ? intval( $row['rating_entry_id'] ) : $rating_entry_id
		);
	}

	/**
	 * Gets rating item result for a given post id, rating form id and filters.
	 *
	 * @param unknown_type $params
	 */
	public static function get_rating_item_result( $params = array() ) {

		extract( wp_parse_args( $params, array(
				'rating_item' => null,
				'rating_form_id' => null,
				'post_id' => null,
				'taxonomy' => null,
				'term_id' => 0,
				'entry_status' => 'approved',
				'approved_comments_only' => null,
				'published_posts_only' => null,
				'user_roles' => null,
				'rating_entry_ids' => null,
				'user_id' => null,
				'comments_only' => null,
				'from_date' => null,
				'to_date' => null,
				'post_ids' => null
		) ) );

		if ( ! ( isset( $rating_item ) && isset( $rating_item['rating_item_id'] )
				&& is_numeric( $rating_item['rating_item_id'] ) ) ) {
			return;
		}

		$params = apply_filters( 'mrp_calculate_rating_item_result_params', $params );

		// Applicable filters for rating_item_result: taxonomy, term_id, entry_status, approved_comments_only,
		// published_posts_only, user_roles, rating_entry_ids, user_id, comments_only, from_date, to_date and
		// post ids
		$filter_params = array(
				'taxonomy' => null,
				'term_id' => 0,
				'entry_status' => 'approved',
				'approved_comments_only' => null,
				'published_posts_only' => null,
				'user_roles' => null,
				'rating_entry_ids' => null,
				'user_id' => null,
				'comments_only' => null,
				'from_date' => null,
				'to_date' => null,
				'post_ids' => null
		);
		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $filter_params );

		// Get rating item result from the database if exists
		global $wpdb;

		$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME
				. ' WHERE rating_item_id = ' . $rating_item['rating_item_id'] . ' AND rating_entry_id IS NULL'
				. ' AND filters_hash = %s';
		$where_args = array();
		array_push( $where_args, $filters_hash );
		$added_to_query = true;

		if ( isset( $post_id ) ) {
			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' post_id = %d';
			array_push( $where_args, $post_id );
			$added_to_query = true;
		}

		if ( isset( $rating_form_id ) ) {
			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' rating_form_id = %d';
			array_push( $where_args, $rating_form_id );
			$added_to_query = true;
		}

		if ( count( $where_args ) > 0 ) {
			$query = $wpdb->prepare( $query, $where_args );
		}

		$query = apply_filters( 'mrp_rating_item_result_query', $query, $params );

		$row = $wpdb->get_row( $query, ARRAY_A, 0 );

		// If rating item result does not exist in database, recalculate and save it
		if ( $row == null ) {

			/*
			 * Select
			 */
			$query_select = 'SELECT riev.rating_item_id, riev.rating_item_entry_id, riev.value, rie.post_id, rie.rating_form_id';
			$query_select = apply_filters( 'mrp_rating_item_entries_query_select', $query_select, $params );

			/*
			 * From
			*/
			$added_to_query = true;
			$query_from = ' FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' riev';

			$query_from = apply_filters( 'mrp_rating_item_entries_query_from', $query_from, $params );

			/*
			 * Join
			*/
			$query_join = ' LEFT JOIN ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie ON rie.rating_item_entry_id = riev.rating_item_entry_id';

			if ( $published_posts_only || $taxonomy != null ) {
				$query_join .= ' LEFT JOIN ' . $wpdb->posts . ' p ON rie.post_id = p.ID';
			}

			if ( $taxonomy != null ) {
				$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel ON rel.object_id = p.ID';
				$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id';
				$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t ON t.term_id = tax.term_id';
			}


			if ( $user_roles != null ) {
				$query_join .= ' LEFT JOIN ' . $wpdb->users . ' u ON u.ID = rie.user_id';
				$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'usermeta um ON u.ID = um.user_id';
			}

			if ( $approved_comments_only ) {
				$query_join .= ' LEFT JOIN ' . $wpdb->comments . ' c ON rie.comment_id = c.comment_ID';
			}
			$query_join = apply_filters( 'mrp_rating_item_entries_query_join', $query_join, '', $params );

			/*
			 * Where
			*/
			$query_where = ' WHERE riev.rating_item_id = %d';
			$where_args = array();
			array_push( $where_args, $rating_item['rating_item_id'] );
			$added_to_query = true;

			if ( $post_id ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= apply_filters( 'mrp_rating_item_entries_query_where_post', $wpdb->prepare( ' rie.post_id = %d', $post_id ), $params, 'rie' );
				$added_to_query = true;
			}

			if ( $user_id ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' rie.user_id = %d';
				array_push( $where_args, $user_id );
				$added_to_query = true;
			}

			if ( $user_roles != null ) {
				$user_roles = explode( ',', $user_roles );

				if ( is_array( $user_roles)  && count( $user_roles ) ) {
					if ( $added_to_query ) {
						$query_where .= ' AND';
					}

					$query_where .= ' u.ID = rie.user_id AND um.meta_key  = "' . $wpdb->prefix . 'capabilities" AND (';
					$index = 1;
					foreach ( $user_roles as $user_role ) {
						$query_where .= ' um.meta_value LIKE "%%' . $wpdb->esc_like( $user_role ) . '%%"';
						if ( $index < count( $user_roles ) ) {
							$query_where .= ' OR ';
						}
						$index++;
					}
					$query_where .= ' ) ';
					$added_to_query = true;
				}
			}

			if ( $from_date ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' rie.entry_date >= %s';
				array_push( $where_args, $from_date );
				$added_to_query = true;
			}

			if ( $to_date ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' rie.entry_date <= %s';
				array_push( $where_args, $to_date );
				$added_to_query = true;
			}

			if ( $taxonomy ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' tax.taxonomy = %s';
				array_push( $where_args, $taxonomy );

				if ( $term_id ) {
					$query_where .= ' AND t.term_id = %d';
					array_push( $where_args, $term_id );
				}

				$added_to_query = true;
			}

			if ( $rating_form_id ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' rie.rating_form_id = %d';
				array_push( $where_args, $rating_form_id );
				$added_to_query = true;
			}

			if ( $comments_only == true ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= '  ( ( rie.comment != "" AND rie.comment IS NOT NULL ) OR rie.comment_id IS NOT NULL )';
				$added_to_query = true;
			}

			if ( $approved_comments_only ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' ( ( rie.comment_id = "" OR rie.comment_id IS NULL ) OR ( rie.comment_id IS NOT NULL AND c.comment_approved = "1" ) )';
				$added_to_query = true;
			}

			if ( $published_posts_only ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' ( p.post_status = "publish" OR p.post_type = "attachment" ) ';
				$added_to_query = true;
			}

			if ( $entry_status ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
				}

				$query_where .= ' rie.entry_status = %s';
				array_push( $where_args, $entry_status );
				$added_to_query = true;
			}

			$rating_entry_ids = ( strlen( $rating_entry_ids ) > 0 ) ? explode( ',', $rating_entry_ids ) : null;
			if ( $rating_entry_ids && count( $rating_entry_ids ) > 0 ) {

				if ( $added_to_query ) {
					$query_where .= ' AND';
					$added_to_query = true;
				}

				$query_where .= ' rie.rating_item_entry_id IN ( ' . implode( ', ', array_fill( 0, count( $rating_entry_ids ), '%d' ) ) . ' ) ';
				$where_args = array_merge( $where_args, $rating_entry_ids );
			}

			$post_ids = ( strlen( $post_ids ) > 0 ) ? explode( ',', $post_ids ) : null;
			if ( $post_ids && count( $post_ids ) > 0 ) {
				if ( $added_to_query ) {
					$query_where .= ' AND';
					$added_to_query = true;
				}

				$query_where .= ' rie.post_id IN ( ' . implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) ) . ' ) ';
				$where_args = array_merge( $where_args, $post_ids );
			}

			if ( count ( $where_args ) > 0 ) {
				$query_where = $wpdb->prepare( $query_where, $where_args );
			}

			$query_where = apply_filters( 'mrp_rating_item_entries_query_where', $query_where, $params );

			/*
			 * Group by
			*/
			$group_by_query = '';
			$query_group_by = apply_filters( 'mrp_rating_item_entries_query_group_by', $group_by_query, $params );

			/*
			 * Order by
			*/
			$query_order_by = '';
			$query_order_by = apply_filters( 'mrp_rating_item_entries_query_order_by', $query_order_by, $params );

			/*
			 * Limit
			*/
			$query_limit = '';
			$query_limit = apply_filters( 'mrp_rating_item_entries_query_limit', $query_limit, $params );

			/*
			 * Query results
			*/
			$query = $query_select . $query_from . $query_join . $query_where . $query_group_by . $query_order_by .  $query_limit;
			$query = apply_filters( 'mrp_rating_item_entries_query_query', $query, $params );

			$rows = $wpdb->get_results( $query );

			/*
			 * Now calculate rating item result
			*/
			$max_option_value = $rating_item['max_option_value'];

			// there's no such thing as adjusted rating results for rating items specifically
			$star_result = 0;
			$score_result = 0;
			$percentage_result = 0;

			// Init option totals
			$option_totals = array();
			for ( $index=0; $index <= $max_option_value; $index++ ) {
				$option_totals[$index] = 0;
			}

			$count_entries = count( $rows );

			foreach ( $rows as $row ) {

				$post_id = $row->post_id;
				$rating_form_id = $row->rating_form_id;

				$value = $row->value;

				if ( $value > 0 ) { // to cater for not applicable values

					if ( $value <= $max_option_value ) {
						$option_totals[$value]++;
					} else {
						$option_totals[$max_option_value]++;
						$value = $max_option_value;
					}

					$score_result += intval( $value );
				} else {
					$count_entries--;
				}
			}

			if ( $count_entries > 0 ) {

				$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
				$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];
				$star_rating_out_of = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];

				$score_result = (float) floatval( $score_result ) / $count_entries;
				$star_result = (float) round( ( floatval( $score_result ) / floatval( $max_option_value ) ) * $star_rating_out_of, $decimal_places );

				$percentage_result = (float) round( ( doubleval( $score_result ) / doubleval( $max_option_value ) ) * 100, $decimal_places);
				$score_result = (float) round( $score_result, $decimal_places ); // do this last so that rounding does not affect other results
			}

			$rating_result = array(
					'star_result' => $star_result,
					'adjusted_star_result' => $star_result,
					'score_result' => $score_result,
					'adjusted_score_result' => $score_result,
					'percentage_result' => $percentage_result,
					'adjusted_percentage_result' => $percentage_result,
					'total_max_option_value' => $max_option_value,
					'count_entries' => $count_entries,
					'option_totals' => $option_totals,
					'post_id' => $post_id,
					'rating_form_id' => $rating_form_id,
					'rating_item_id' => $rating_item['rating_item_id']
			);

			MRP_Multi_Rating_API::save_rating_result( $rating_result, $params );

			return $rating_result;
		}

		return array(
				'adjusted_star_result' => floatval( $row['adjusted_star_result'] ),
				'star_result' => floatval( $row['star_result'] ),
				'adjusted_score_result' => floatval( $row['adjusted_score_result'] ),
				'score_result' => floatval( $row['score_result'] ),
				'adjusted_percentage_result' => floatval( $row['adjusted_percentage_result'] ),
				'percentage_result' => floatval( $row['percentage_result'] ),
				'total_max_option_value' => $row['total_max_option_value'],
				'count_entries' => isset( $row['count_entries'] ) ? $row['count_entries'] : null,
				'option_totals' => isset( $row['option_totals'] ) ? unserialize( $row['option_totals'] ) : null,
				'post_id' => isset( $row['post_id'] ) ? intval( $row['post_id'] ) : $post_id,
				'rating_form_id' => isset( $row['rating_form_id'] ) ? intval( $row['rating_form_id'] ) : $rating_form_id,
				'rating_item_id' => isset( $row['rating_item_id'] ) ? intval( $row['rating_item_id'] ) : isset( $rating_item ) && isset( $rating_item['rating_item_id'] ) ? intval( $rating_item['rating_item_id'] ) : null
		);
	}

	/**
	 * Get rating entry details
	 *
	 * @param array $params rating_entry_id or comment id
	 * @return rating entry details
	 */
	public static function get_rating_entry( $params = array() ) {

		extract( wp_parse_args( $params, array(
				'rating_entry_id' => null,
				'comment_id' => null,
				'approved_comments_only' => false,
				'entry_status' => ''

		) ) );

		if ( $rating_entry_id == null && $comment_id == null ) {
			return;
		}

		global $wpdb;

		$query = 'SELECT rie.user_id, IFNULL( u.display_name, IFNULL( c.comment_author, rie.name ) ) AS name,'
				. ' IFNULL( u.user_email, IFNULL( c.comment_author_email, rie.email ) ) AS email, IFNULL( c.comment_content,'
				. ' rie.comment ) AS comment, IFNULL( u.user_login, "" ) AS username, rie.rating_form_id, rie.post_id,'
				. ' rie.rating_item_entry_id, rie.entry_date, rie.comment_id, rie.entry_status, rie.title FROM . '
				. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie LEFT JOIN ' . $wpdb->users
				. ' u ON u.ID = rie.user_id LEFT JOIN ' . $wpdb->comments . ' c ON rie.comment_id = c.comment_ID';
		$query_args = array();
		$added_to_query = true;

		if ( $rating_entry_id ) {
			$query .= ' WHERE rie.rating_item_entry_id = %d';
			array_push( $query_args, $rating_entry_id );
		} else {
			$query .= ' WHERE c.comment_ID = %d';
			array_push( $query_args, $comment_id );
		}

		if ( $approved_comments_only ) {
			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' ( ( rie.comment_id = "" OR rie.comment_id IS NULL ) OR ( rie.comment_id IS NOT NULL AND c.comment_approved = "1" ) )';
			$added_to_query = true;
		}

		if ( $entry_status && strlen( $entry_status ) > 0 ) {
			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' rie.entry_status = %s';
			array_push( $query_args, $entry_status );
			$added_to_query = true;
		}

		if ( count( $query_args ) > 0 ) {
			$query = $wpdb->prepare( $query, $query_args );
		}

		$row = $wpdb->get_row( $query );

		if ( $row == null ) {
			return;
		}

		$rating_form_id = $row->rating_form_id;
		$post_id = $row->post_id;
		$rating_entry_id = $row->rating_item_entry_id;
		$username = $row->username;
		$title = stripslashes( $row->title );
		$name = stripslashes( $row->name );
		$email = $row->email;
		$comment = stripslashes( $row->comment );
		$comment_id = $row->comment_id;
		$entry_date = $row->entry_date;
		$entry_status = $row->entry_status;
		$user_id = $row->user_id;

		$custom_field_values = MRP_Multi_Rating_API::get_custom_field_values( $rating_entry_id, $rating_form_id );
		$rating_item_values = MRP_Multi_Rating_API::get_rating_item_values( $rating_entry_id, $rating_form_id );

		return array(
				'username' => $username,
				'title' => $title,
				'name' => $name,
				'email' => $email,
				'comment' => $comment,
				'comment_id' => intval( $comment_id ),
				'entry_date' => $entry_date,
				'entry_status' => $entry_status,
				'user_id' => intval( $user_id ),
				'post_id' => intval( $post_id ),
				'rating_form_id' => intval( $rating_form_id ),
				'rating_entry_id' => intval( $rating_entry_id ),
				'rating_item_values' => $rating_item_values,
				'custom_field_values' => $custom_field_values
		);
	}

	/**
	 * Get rating item values for a given rating entry id and rating form id
	 *
	 * @param unknown $rating_entry_id
	 * @param unknown $rating_form_id
	 * @return multitype:NULL
	 */
	public static function get_rating_item_values( $rating_entry_id, $rating_form_id ) {

		if ( $rating_entry_id == null || $rating_form_id == null ) {
			return;
		}

		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
		$rating_items = $rating_form['rating_items'];

		$rating_item_values = array();

		$count = count( $rating_items );
		$index = 0;
		if ( $count > 0 ) {

			global $wpdb;
			$query = 'SELECT * FROM '.$wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME
					. ' AS riev WHERE rating_item_entry_id = ' . esc_sql( $rating_entry_id )
					. ' AND rating_item_id IN ( ' . implode(', ', array_fill( 0, $count, '%d' ) ) . ' )';
			$query_args = array();
			foreach ( $rating_items as $rating_item ) {
				array_push( $query_args, $rating_item['rating_item_id'] );
			}

			$rows = $wpdb->get_results( $wpdb->prepare( $query, $query_args ) );

			foreach ( $rows as $row ) {
				$rating_item_values[$row->rating_item_id] = $row->value;
			}
		}

		return $rating_item_values;
	}


	/**
	 * Get custom field values for a given rating entry id and rating form id
	 *
	 * @param unknown $rating_entry_id
	 * @param unknown $rating_form_id
	 * @return multitype:NULL
	 */
	public static function get_custom_field_values( $rating_entry_id, $rating_form_id ) {

		if ( $rating_entry_id == null || $rating_form_id == null ) {
			return;
		}

		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
		$custom_fields = $rating_form['custom_fields'];

		$custom_field_values = array();

		$count = count( $custom_fields );
		$index = 0;
		if ( $count > 0 ) {

			global $wpdb;

			$query = 'SELECT custom_field_id, value FROM (';

			foreach ( $custom_fields as $custom_field ) {
				$query .= '	SELECT ' . $custom_field['custom_field_id'] . ' AS custom_field_id, value FROM '
						. $wpdb->prefix . 'mrp_custom_field_' . $custom_field['custom_field_id']
						. ' WHERE rating_entry_id = ' . intval( $rating_entry_id );

				if ( $index++ < $count-1 ) {
					$query .= ' UNION ALL';
				}
			}

			$query .= ' ) subquery ORDER BY custom_field_id';

			$rows = $wpdb->get_results( $query );

			foreach ( $rows as $row ) {
				$custom_field_values[$row->custom_field_id] = stripslashes( $row->value );
			}
		}

		return $custom_field_values;
	}

	/**
	 * Deletes calculated ratings given post id, rating form id, rating item id and
	 * rating entry id's.
	 *
	 * @param array $params
	 * @param bool $and whether all params specified must be present to delete or not
	 */
	public static function delete_calculated_ratings( $params = array(), $and = false ) {

		extract( wp_parse_args( $params, array(
				'post_id' => null,
				'rating_form_id' => null,
				'rating_item_id' => null,
				'rating_entry_id' => null
		) ) );

		global $wpdb;

		$where = array();
		$where_format = array();

		if ( $post_id ) {
			if ( $and ) {
				$where['post_id'] = $post_id;
				array_push( $where_format, '%d' );
			} else {
				$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME,
						array( 'post_id' => $post_id ),
						array( '%d' )
				);
			}
		}

		if ( $rating_form_id ) {
			if ( $and ) {
				$where['rating_form_id'] = $rating_form_id;
				array_push( $where_format, '%d' );
			} else {
				$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME,
						array( 'rating_form_id' => $rating_form_id ),
						array( '%d' )
				);
			}
		}

		if ( $rating_item_id ) {
			if ( $and ) {
				$where['rating_item_id'] = $rating_item_id;
				array_push( $where_format, '%d' );
			} else {
				$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME,
						array( 'rating_item_id' => $rating_item_id ),
						array( '%d' )
				);
			}
		}

		if ( $rating_entry_id ) {
			if ( $and ) {
				$where['rating_entry_id'] = $rating_entry_id;
				array_push( $where_format, '%d' );
			} else {
				$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME,
						array( 'rating_entry_id' => $rating_entry_id ),
						array( '%d' )
				);
			}
		}

		if ( $and && count( $where ) > 0 ) {
			$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME, $where, $where_format );
		}

		// Delete from post meta table if required
		if ( $post_id && $rating_form_id ) {

			delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id );
			delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating' );
			delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries' );

		} else if ( $rating_form_id && ! $post_id ) {

			delete_post_meta_by_key( MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id );
			delete_post_meta_by_key( MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating' );
			delete_post_meta_by_key( MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries' );

		} else if ( ! $rating_form_id && $post_id ) {

			global $wpdb;
			$rows = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, ARRAY_A );

			foreach ( $rows as $row ) {
				$rating_form_id = $row['rating_form_id'];

				delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id );
				delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating' );
				delete_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries' );
			}

		}

		do_action( 'mrp_delete_rating_result', $params, $and );
	}

	/**
	 * Saves a rating result
	 *
	 * @param unknown $rating_result
	 * @param unknown $params
	 */
	public static function save_rating_result( $rating_result, $params ) {

		global $wpdb;

		$data = array(
				'last_updated_dt' => current_time( 'mysql' ),
				'adjusted_star_result' => $rating_result['adjusted_star_result'],
				'star_result' => $rating_result['star_result'],
				'adjusted_score_result' => $rating_result['adjusted_score_result'],
				'score_result' => $rating_result['score_result'],
				'total_max_option_value' => $rating_result['total_max_option_value'],
				'adjusted_percentage_result' =>  $rating_result['adjusted_percentage_result'],
				'percentage_result' =>  $rating_result['percentage_result']
		);
		$data_format = array( '%s', '%f', '%f', '%f', '%f', '%d', '%f', '%f' );

		if ( isset( $rating_result['count_entries'] ) ) {
			$data = array_merge( $data, array( 'count_entries' => $rating_result['count_entries'] ) );
			array_push( $data_format, '%d' );
		}
		if ( isset( $rating_result['option_totals'] ) ) {
			$data = array_merge( $data, array( 'option_totals' => serialize( $rating_result['option_totals'] ) ) );
			array_push( $data_format, '%s' );
		}

		$query = 'SELECT id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' WHERE';
		$where_args = array();
		$added_to_query = false;

		$post_id = null;
		if ( isset( $rating_result['post_id'] ) && is_numeric( $rating_result['post_id'] ) ) {
			$post_id = $rating_result['post_id'];
		} else if ( isset( $params['post_id'] ) && is_numeric( $params['post_id'] ) ) {
			$post_id = $params['post_id'];
		}

		if ( $post_id  ) {

			$data = array_merge( $data, array( 'post_id' => $post_id ) );
			array_push( $data_format, '%d' );

			$query .= ' post_id = %d';
			array_push( $where_args, $post_id );
			$added_to_query = true;
		}

		$rating_entry_id = null;
		if ( isset( $rating_result['rating_entry_id'] ) && is_numeric( $rating_result['rating_entry_id'] ) ) {
			$rating_entry_id = $rating_result['rating_entry_id'];
		} else if ( isset( $params['rating_entry_id'] ) && is_numeric( $params['rating_entry_id'] ) ) {
			$rating_entry_id = $params['rating_entry_id'];
		}

		if ( $rating_entry_id ) {

			$data = array_merge( $data, array( 'rating_entry_id' => $rating_entry_id ) );
			array_push( $data_format, '%d' );

			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' rating_entry_id = %d';
			array_push( $where_args, $rating_entry_id );
			$added_to_query = true;
		} else {

			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= '( rating_entry_id IS NULL OR rating_entry_id = "" )';
			$added_to_query = true;
		}

		if ( isset( $params['rating_item'] ) ) {

			$rating_item_id = $params['rating_item']['rating_item_id'];

			$data = array_merge( $data, array( 'rating_item_id' => $rating_item_id ) );
			array_push( $data_format, '%d' );

			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' rating_item_id = %d';
			array_push( $where_args, $rating_item_id );
			$added_to_query = true;
		} else {

			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= '( rating_item_id IS NULL OR rating_item_id = "" )';
			$added_to_query = true;
		}

		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $params );

		$data = array_merge( $data, array( 'filters_hash' => $filters_hash ) );
		array_push( $data_format, '%s' );

		if ( $added_to_query ) {
			$query .= ' AND';
		}

		$query .= ' filters_hash = %s';
		array_push( $where_args, $filters_hash );
		$added_to_query = true;

		$rating_form_id = null;
		if ( isset( $rating_result['rating_form_id'] )  && is_numeric( $rating_result['rating_form_id'] ) ) {
			$rating_form_id = $rating_result['rating_form_id'];
		} else if ( isset( $params['rating_form_id'] ) && is_numeric( $params['rating_form_id'] ) ) {
			$rating_form_id = $params['rating_form_id'];
		}

		if ( $rating_form_id ) {

			$data = array_merge( $data, array( 'rating_form_id' => $rating_form_id ) );
			array_push( $data_format, '%d' );

			if ( $added_to_query ) {
				$query .= ' AND';
			}

			$query .= ' rating_form_id = %d';
			array_push( $where_args, $rating_form_id );
			$added_to_query = true;
		}

		if ( count( $where_args ) > 0 ) {
			$query = $wpdb->prepare( $query, $where_args );
		}

		$id = $wpdb->get_var( $query );

		if ( $id != null ) {

			// we don't want to update these. wpdb update does not support updating ints to
			// null and there's no need to anyway. If it wasn't there then it's a new
			// rating result inserted (i.e. you would never get here)
			$index = array_search( 'filters_hash', array_keys( $data ) );
			if ( $index !== false ) {
				unset( $data['filters_hash'] );
				unset( $data_format[$index] );
			}
			$index = array_search( 'rating_entry_id', array_keys( $data ) );
			if ( $index !== false ) {
				unset( $data['rating_entry_id'] );
				unset( $data_format[$index] );
			}
			$index = array_search( 'rating_item_id', array_keys( $data ) );
			if ( $index !== false ) {
				unset( $data['rating_item_id'] );
				unset( $data_format[$index] );
			}

			$result = $wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME, $data, array( 'id' => $id ), $data_format, array( '%d' ) );
		} else {
			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME, $data, $data_format );
		}

		// Update post meta table if required
		if ( $post_id && $rating_form_id && $rating_entry_id == null ) {
			update_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id, $rating_result );
			update_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_star_rating', $rating_result['adjusted_star_result'] );
			update_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POST_META . '_' . $rating_form_id . '_count_entries', $rating_result['count_entries'] );
		}

		do_action( 'mrp_save_rating_result', $rating_result, $params );
	}

	/**
	 * Generates filters hash value
	 *
	 * @param array $params
	 * @return string filters hash
	 */
	public static function get_filters_hash( $params = array() ) {

		/* The following filters can influence rating results: rating_item_ids, rating_entry_ids,
		 * user_roles, user_id, comments_only, approved_comments_only = false, entry_status = "pending",
		 * comments_only, to_date and from_date
		 *
		 *  Note there are 3 types of filters hash parameter sets for rating_result, rating_entry_result
		 *  and rating_item_result
		 *
		 *  Applicable filters for rating_result: entry_status, approved_comments_only, rating_item_ids,
		 *  user_roles, rating_entry_ids, user_id, comments_only, from_date and to_date
		 *
		 *  Applicable filters for rating_entry_result: rating_item_ids
		 *
		 *  Applicable filters for rating_item_result: taxonomy, term_id, entry_status, approved_comments_only,
		 *  published_posts_only, user_roles, rating_entry_ids, user_id, comments_only, from_date, to_date and
		 *  post_ids
		 */

		extract( wp_parse_args( $params, array(
				'taxonomy' => null,
				'term_id' => 0,
				'entry_status' => 'approved',
				'approved_comments_only' => null,
				'published_posts_only' => null,
				'rating_item_ids' => null,
				'user_roles' => null,
				'rating_entry_ids' => null,
				'user_id' => null,
				'comments_only' => null,
				'from_date' => null,
				'to_date' => null,
				'post_ids' => null
		) ) );

		$filters = array();

		if ( $user_id != 0 ) {
			array_push( $filters, array( 'user_id' => $user_id ) );
		}

		if ( $taxonomy && strlen( trim( $taxonomy ) ) > 0 ) {
			array_push( $filters,  array( 'taxonomy' => $taxonomy ) );

			if ( $term_id && is_numeric( $term_id ) ) {
				array_push( $filters,  array( 'term_id' => intval( $term_id )  ) );
			}
		}

		if ( $comments_only && $comments_only == true ) {
			array_push( $filters,  array( 'comments_only' => $comments_only ) );
		}

		if ( $approved_comments_only && $approved_comments_only != true ) {
			array_push( $filters,  array( 'approved_comments_only' => $approved_comments_only ) );
		}

		if ( $from_date && strlen( trim( $from_date ) ) > 0 ) {
			array_push( $filters,  array( 'from_date' => $from_date ) );
		}

		if ( $to_date && strlen( trim( $to_date ) ) > 0 ) {
			array_push( $filters,  array( 'to_date' => $to_date ) );
		}

		if ( $published_posts_only && $published_posts_only != true ) {
			array_push( $filters,  array( 'published_posts_only' => $published_posts_only ) );
		}

		if ( $entry_status != 'approved' ) {
			array_push( $filters,  array( 'entry_status' => $entry_status ) );
		}

		if ( $rating_entry_ids && strlen( trim( $rating_entry_ids ) ) > 0 ) {
			array_push( $filters,  array( 'rating_entry_ids' => $rating_entry_ids ) );
		}

		if ( $user_roles && strlen( trim( $user_roles ) ) > 0 ) {
			array_push( $filters,  array( 'user_roles' => $user_roles ) );
		}

		if ( $rating_item_ids && strlen( trim( $rating_item_ids ) ) > 0 ) {
			array_push( $filters,  array( 'rating_item_ids' => $rating_item_ids ) );
		}

		if ( $post_ids && strlen( trim( $post_ids ) ) > 0 ) {
			array_push( $filters,  array( 'post_ids' => $post_ids ) );
		}

		$filters = apply_filters( 'mrp_rating_result_filters', $filters, $params );

		if ( count( $filters ) > 0 ) {
			return md5( serialize( $filters ) );
		}

		return "";
	}


	/**
	 * Reassigns a users rating entries to someone else
	 *
	 * @param unknown $user_id
	 * @param unknown $reassign
	 */
	public static function reassign_user_ratings( $user_id, $reassign ) {

		global $wpdb;

		$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME,
				array( 'user_id' => $reassign ),
				array( 'user_id' => $user_id ),
				array( '%d' ),
				array( '%d' )
		);

	}

	/**
	 * Deletes a rating entry
	 *
	 * @param unknown $rating_entry rating_entry_id or rating_entry object
	 * @param int $comment_id
	 */
	public static function delete_rating_entry( $rating_entry, $comment_id = null ) {

		if ( is_numeric( $rating_entry ) ) {
			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry ) );
		} else if ( is_numeric( $comment_id ) ) {
			$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'comment_id' => $comment_id ) );
		}

		if ( $rating_entry == null ) {
			return;
		}

		$rating_entry_id = $rating_entry['rating_entry_id'];
		$post_id = $rating_entry['post_id'];
		$rating_form_id = $rating_entry['rating_form_id'];
		$custom_fields = array_keys( $rating_entry['custom_field_values'] );
		$user_id = $rating_entry['user_id'];
		$comment_id = $rating_entry['comment_id'];

		global $wpdb;

		do_action( 'mrp_before_delete_rating_entry', array( 'rating_entry_id' => $rating_entry_id, 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );

		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME,
				array( 'rating_item_entry_id' => $rating_entry_id ),
				array( '%d' )
		);
		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME,
				array( 'rating_item_entry_id' => $rating_entry_id ),
				array( '%d' )
		);

		foreach ( $custom_fields as $custom_field_id ) {
			$wpdb->delete( $wpdb->prefix . 'mrp_custom_field_' . intval( $custom_field_id ),
					array( 'rating_entry_id' => $rating_entry_id ),
					array( '%d' )
			);
		}

		if ( $comment_id ) {
			wp_delete_comment( $comment_id, false );
		}

		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ), true );

		do_action( 'mrp_after_delete_rating_entry_success', array( 'rating_entry_id' => $rating_entry_id, 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ) );
	}

	/**
	 * Deletes a rating form
	 *
	 * @param unknown $rating_form_id
	 */
	public static function delete_rating_form( $rating_form_id ) {

		global $wpdb;

		do_action( 'mrp_before_delete_rating_form', array( 'rating_form_id' => $rating_form_id ) );

		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
				array( 'rating_form_id' => $rating_form_id ),
				array( '%d' )
		);
		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
				array( 'rating_form_id' => $rating_form_id ),
				array( '%d' )
		);

		do_action( 'mrp_after_delete_rating_form', array( 'rating_form_id' => $rating_form_id ) );
	}

	/**
	 * Deletes a rating form
	 *
	 * @param unknown $rating_form_id
	 */
	public static function delete_rating_item( $rating_item_id ) {

		global $wpdb;

		do_action( 'mrp_before_delete_rating_item', array( 'rating_item_id' => $rating_item_id ) );

		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME,
				array( 'rating_item_id' => $rating_item_id ),
				array( '%d' )
		);

		// find all rating forms which have the rating item
		$query = 'SELECT rating_form_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME . ' WHERE item_id = %d AND item_type = "rating-item"';
		$results = $wpdb->get_results( $wpdb->prepare( $query, $rating_item_id ) );

		foreach ( $results as $row ) {
			MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_form_id' => $row->rating_form_id ) );
		}

		$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
				array( 'item_id' => $rating_item_id, 'item_type' => 'rating-item' ),
				array( '%d', '%s' )
		);

		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'rating_item_id' => $rating_item_id ) );

		do_action( 'mrp_after_delete_rating_item', array( 'rating_item_id' => $rating_item_id ) );

	}

	/**
	 * Performs a cleanup of the database for any orphaned rating entries and associated data.
	 *
	 * <ol>
	 * <li>delete rating entries that are associated to a post or rating form if it does not exist</li>
	 * <li>delete rating entries that are associated to a comment if it does not exist</li>
	 * <li>delete rating item values that are not associated to any rating item or if rating entry does not exist</li>
	 * <li>delete any rating item values if the rating item is not associated to the rating form</li>
	 * <li>delete custom field values where rating entry does not exist</li>
	 * </ol>
	 *
	 * @return boolean|number number of deleted rows or false if error occurs
	 */
	public static function delete_orphaned_data() {

		if ( ! mrp_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
			@set_time_limit( 0 );
		}

		$deleted_rows = 0;

		global $wpdb;

		try {

			// 1. delete rating entries that are associated to a post or rating form if it does not exist
			$query = 'SELECT rie.rating_item_entry_id FROM ' . $wpdb->prefix
					. MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie WHERE NOT EXISTS ( SELECT'
					. ' * FROM ' . $wpdb->posts . ' p  WHERE rie.post_id = p.ID ) OR NOT EXISTS'
					. ' ( SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME
					. ' rf WHERE rie.rating_form_id = rf.rating_form_id )';

			$rows = $wpdb->get_results( $query, ARRAY_A );
			$rating_entry_ids = wp_list_pluck( $rows, 'rating_item_entry_id' );

			if ( count( $rating_entry_ids ) > 0 ) {

				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE'
						. ' rating_item_entry_id IN ( ' . implode( ', ', $rating_entry_ids ) . ' )';

				$deleted_rows += $wpdb->query( $query );
			}

			// 2. delete rating entries that are associated to a comment if it does not exist
			$query = 'SELECT rie.rating_item_entry_id FROM ' . $wpdb->prefix
					. MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie WHERE NOT EXISTS ( SELECT'
					. ' * FROM ' . $wpdb->comments . ' c  WHERE rie.comment_id = c.comment_ID )'
					. ' AND rie.comment_id IS NOT NULL';

			$rows = $wpdb->get_results( $query, ARRAY_A );
			$rating_entry_ids = wp_list_pluck( $rows, 'rating_item_entry_id' );

			if ( count( $rating_entry_ids ) > 0 ) {

				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE'
						. ' rating_item_entry_id IN ( ' . implode( ', ', $rating_entry_ids ) . ' )';

				$deleted_rows += $wpdb->query( $query );
			}

			// 3. delete rating item values that are not associated to any rating item or if rating entry does not exist
			$query = 'SELECT riev.rating_item_entry_value_id FROM ' . $wpdb->prefix
					. MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev ' . ' WHERE NOT'
					. ' EXISTS ( SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME
					. ' AS rie WHERE riev.rating_item_entry_id = rie.rating_item_entry_id ) OR NOT EXISTS'
					. ' ( SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME
					. ' AS ri WHERE riev.rating_item_id = ri.rating_item_id )';

			$rows = $wpdb->get_results( $query, ARRAY_A );
			$rating_entry_value_ids = wp_list_pluck( $rows, 'rating_item_entry_value_id' );

			if ( count( $rating_entry_value_ids ) > 0 ) {

				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE'
						. ' rating_item_entry_value_id IN ( ' . implode( ', ', $rating_entry_value_ids ) . ' )';

				$deleted_rows += $wpdb->query( $query );
			}

			// 4. delete custom field values where rating entry does not exist
			$custom_fields = MRP_Multi_Rating_API::get_custom_fields();
			if ( count( $custom_fields) > 0 ) {

				foreach ( $custom_fields as $custom_field ) {

					$query = 'SELECT cf.rating_entry_id  FROM ' . $wpdb->prefix
							. 'mrp_custom_field_' . $custom_field['custom_field_id'] . ' as cf WHERE NOT'
							. ' EXISTS( SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME
							. ' AS rie WHERE cf.rating_entry_id = rie.rating_item_entry_id )';

					$rows = $wpdb->get_results( $query, ARRAY_A );
					$rating_entry_ids = wp_list_pluck( $rows, 'rating_entry_id' );

					if ( count( $rating_entry_ids ) > 0 ) {

						$query = 'DELETE FROM ' . $wpdb->prefix . 'mrp_custom_field_' . $custom_field['custom_field_id']
								. ' WHERE rating_entry_id IN ( ' . implode( ', ', $rating_entry_ids ) . ' )';

						$deleted_rows += $wpdb->query( $query );
					}
				}
			}

			// 5. delete any rating item values if the rating item is not associated to the rating form
			$query = 'SELECT riev.rating_item_entry_value_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev LEFT JOIN '
					. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie ON '
					. ' rie.rating_item_entry_id = riev.rating_item_entry_id WHERE NOT EXISTS ( SELECT '
					. ' riev.rating_item_entry_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME
					. ' AS rfi WHERE rfi.item_type = "rating-item" AND rfi.item_id = riev.rating_item_id AND '
					. ' rie.rating_form_id = rfi.rating_form_id AND riev.rating_item_id = rfi.item_id GROUP BY '
					. ' riev.rating_item_entry_id)';

			$rows = $wpdb->get_results( $query, ARRAY_A );
			$rating_enty_value_ids = wp_list_pluck( $rows, 'rating_item_entry_value_id' );

			if ( count( $rating_entry_value_ids ) > 0 ) {

				$query = 'DELETE FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE'
						. ' rating_item_entry_value_id IN ( ' . implode( ', ', $rating_entry_value_ids ) . ' )';

				$deleted_rows += $wpdb->query( $query );
			}

		} catch ( Exception $e ) {
			return false;
		}

		return apply_filters( 'mrp_delete_orphaned_data', $deleted_rows );
	}

	/**
	 * Saves a rating form
	 *
	 * @param unknown $rating_form
	 */
	public static function save_rating_form( $rating_form ) {

		do_action( 'mrp_before_save_rating_form', $rating_form );

		$rating_form_id = $rating_form['rating_form_id'];
		$rating_items = $rating_form['rating_items'];
		$review_fields = $rating_form['review_fields'];
		$custom_fields = $rating_form['custom_fields'];
		$name = $rating_form['name'];

		$old_rating_form = null;
		if ( $rating_form_id ) {
			$old_rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
		}

		global $wpdb;

		if ( $old_rating_form == null ) { // new

			$results = $wpdb->insert(  $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
					array( 'name' => $name ),
					array( '%s' )
			);
			$rating_form_id = intval( $wpdb->insert_id );

		} else { // update

			$add_update_rows = array();
			$delete_rows = array();

			$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
					array( 'name' =>  $name ),
					array( 'rating_form_id' => $rating_form_id ),
					array( '%s' ), array( '%d' )
			);

		}

		do_action( 'mrp_register_single_string', 'rating-form-' . $rating_form_id . '-name', $name );

		/*
		 * Rating items
		 */
		foreach ( $rating_items as $rating_item ) {

			$item_id = $rating_item['rating_item_id'];
			$item_type = 'rating-item';
			$weight =  isset( $rating_item['weight'] ) && is_numeric( $rating_item['weight'] ) ? floatval( $rating_item['weight'] ) : 1;
			$required = isset( $rating_item['required'] ) ? $rating_item['required'] : false;

			if ( $weight < 0 ) {
				$weight = 1; // weight cannot less than 0
			}
			if ( is_string( $required ) ) {
				$required = $required == "true" ? true : false;
			}

			if ( ! isset( $old_rating_form['rating_items'][$rating_item['rating_item_id']] )
					|| array_key_exists( $rating_item['rating_item_id'], $old_rating_form['rating_items'] ) ) { // add or update

				array_push( $add_update_rows, array(
						'data' 		=> array( 'weight' => $weight, 'required' => $required, 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'format' 	=> array( '%f', '%d', '%d', '%d', '%s' )
				) );

			} else { // delete

				array_push( $delete_rows, array(
						'where' 		=> array( 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'where_format' 	=> array( '%d', '%d', '%s' )
				) );

			}
		}

		/*
		 * Custom fields
		 */
		foreach ( $custom_fields as $custom_field ) {

			$item_id = $custom_field['custom_field_id'];
			$item_type = 'custom-field';
			$required = isset( $custom_field['required'] ) ? $custom_field['required'] : false;

			if ( is_string( $required ) ) {
				$required = $required == "true" ? true : false;
			}


			if ( ! isset( $old_rating_form['custom_fields'][$custom_field['custom_field_id']] )
					|| array_key_exists( $custom_field['custom_field_id'], $old_rating_form['custom_fields'] ) ) { // add or update

				array_push( $add_update_rows, array(
						'data' 		=> array( 'required' => $required, 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'format' 	=> array( '%d', '%d', '%d', '%s' )
				) );

			} else { // delete

				array_push( $delete_rows, array(
						'where' 		=> array( 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'where_format' 	=> array( '%d', '%d', '%s' )
				) );

			}

		}

		/*
		 * Review fields
		 */
		foreach ( $review_fields as $review_field ) {

			$item_id = $review_field['review_field_id'];
			$item_type = 'review-field';
			$required = isset( $review_field['required'] ) ? $review_field['required'] : false;

			if ( is_string( $required ) ) {
				$required = $required == "true" ? true : false;
			}

			if ( ! isset( $old_rating_form['review_fields'][$custom_field['review_field_id']] )
					|| array_key_exists( $custom_field['review_field_id'], $old_rating_form['review_fields'] ) ) { // add or update

				array_push( $add_update_rows, array(
						'data' 		=> array( 'required' => $required, 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'format' 	=> array( '%d', '%d', '%d', '%s' )
				) );

			} else { // delete

				array_push( $delete_rows, array(
						'where' 		=> array( 'rating_form_id' => $rating_form_id, 'item_id' => $item_id, 'item_type' => $item_type ),
						'where_format' 	=> array( '%d', '%d', '%s' )
				) );

			}
		}

		foreach ( $add_update_rows as $add_update_row ) {
			$wpdb->replace( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, $add_update_row['data'], $add_update_row['format'] );
		}

		foreach ( $delete_rows as $delete_row ) {
			$wpdb->delete( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME, $delete_row['where'], $delete_row['where_format'] );
		}

		do_action( 'mrp_after_save_rating_form', $rating_form );

	}

	/**
	 * Saves or updates a rating entry
	 *
	 * @param unknown $rating_entry rating entry data to save
	 * @params boolean $validate_fields whether you want to validate rating item, custom fields and review fields
	 */
	public static function save_rating_entry( $rating_entry, $validate_fields = true ) {

		$validation_results = MRP_Utils::validate_rating_entry( array(), $rating_entry, $validate_fields );

		if ( MRP_Utils::has_validation_error( $validation_results ) ) {
			return array( 'validation_results' => $validation_results );
		}

		$is_new = ! isset( $rating_entry['rating_entry_id'] );
		$entry_status_changed = false;
		$allowed_tags = wp_kses_allowed_html( 'post' );

		$rating_form_id = $rating_entry['rating_form_id'];
		$post_id = $rating_entry['post_id'];
		$user_id = isset( $rating_entry['user_id'] ) && is_numeric( $rating_entry['user_id'] ) ? intval( $rating_entry['user_id'] ) : 0;
		$rating_entry_id = isset( $rating_entry['rating_entry_id'] ) ? $rating_entry['rating_entry_id'] : null;
		$rating_item_values = isset( $rating_entry['rating_item_values'] ) ? $rating_entry['rating_item_values'] : array();
		$custom_field_values = isset( $rating_entry['custom_field_values'] ) ? $rating_entry['custom_field_values'] : array();
		$title = isset( $rating_entry['title'] ) ? $rating_entry['title'] : null;
		$name = isset( $rating_entry['name'] ) ? $rating_entry['name'] : null;
		$email = isset( $rating_entry['email'] ) ? $rating_entry['email'] : null;
		$comment = isset( $rating_entry['comment'] ) ? wp_kses( $rating_entry['comment'], $allowed_tags ) : '';
		$comment_id = isset( $rating_entry['comment_id'] ) ? $rating_entry['comment_id'] : null;
		$entry_date = isset( $rating_entry['entry_date'] ) ? $rating_entry['entry_date'] : null;

		do_action( 'mrp_before_save_rating_entry', $rating_entry );

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$auto_approve_ratings = apply_filters( 'mrp_auto_approve_ratings', $general_settings[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS], $rating_entry );

		global $wpdb;
		global $allowedtags;

		$entry_status = isset( $rating_entry['entry_status'] ) ? $rating_entry['entry_status'] : ( $auto_approve_ratings ? 'approved' : 'pending' );
		$rating_entry['entry_status'] = $entry_status;

		if ( ! $is_new ) {

			// if a rating entry has changed, it need may need to be moderated again...
			$query = 'SELECT entry_status FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE rating_item_entry_id = %d';
			$old_entry_status = $wpdb->get_var( $wpdb->prepare( $query, $rating_entry_id ) );

			if ( $entry_status != $old_entry_status ) {
				$entry_status_changed = true;
			}

		} else { // new

			// update comment status if necessary
			if ( $comment_id && $entry_status == 'approved' ) {
				wp_set_comment_status( $comment_id, 'approve' );
			}
		}

		/*
		 * Save or update rating entry
		 */
		if ( $is_new ) {

			// note we do not need to addslashes since we're using $wpdb functions to insert/update
			$insert_data = array(
					'post_id' => intval( $post_id ),
					'rating_form_id' => intval( $rating_form_id ),
					'entry_date' => $entry_date,
					'entry_status' => $entry_status,
					'user_id' => intval( $user_id ),
					'title' => isset( $title ) ? $title : '',
					'name' => isset( $name ) ? $name : '',
					'email' => isset( $email ) ? $email : '',
					'comment' => ( ! $comment_id && isset( $comment ) ) ? $comment : '',
			);

			$insert_data_format = array( '%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s' );

			if ( isset( $comment_id ) && is_numeric( $comment_id ) ) {
				$insert_data = array_merge( $insert_data, array( 'comment_id' => intval( $comment_id ) ) );
				array_push( $insert_data_format, '%d' );
			}

			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME, $insert_data, $insert_data_format );

			$rating_entry_id = intval( $wpdb->insert_id );

			// insert rating items
			foreach ( $rating_item_values as $rating_item_id => $value ) {

				$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME,
						array( 'rating_item_entry_id' => $rating_entry_id, 'rating_item_id' => $rating_item_id, 'value' => $value ),
						array( '%d', '%d', '%d' )
				);
			}

			// insert custom fields
			foreach ( $custom_field_values as $custom_field_id => $value ) {
				$wpdb->insert( $wpdb->prefix. 'mrp_custom_field_' . intval( $custom_field_id ),
						array( 'value' => wp_kses( $value, apply_filters( 'mrp_allowed_tags', $allowedtags, $rating_entry ) ), 'rating_entry_id' => intval( $rating_entry_id ) ),
						array( '%s', '%d' )
				);
			}

			$rating_entry['rating_entry_id'] = $rating_entry_id;

		} else {

			$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME,
					array(
							'entry_date' => $entry_date,
							'title' => isset( $title ) ? $title : '',
							'name' => isset( $name ) ? $name : '',
							'email' => isset( $email ) ? $email : '',
							'comment' => ( ! $comment_id && isset( $comment ) ) ? $comment : '',
							'entry_status' => $entry_status,
							'user_id' => intval( $user_id ),
							'post_id' => intval( $post_id ),
							'rating_form_id' => intval( $rating_form_id )
							// comment_id not allowed
					),
					array( 'rating_item_entry_id' => $rating_entry_id ),
					array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d' ),
					array( '%d' )
			);

			foreach ( $rating_item_values as $rating_item_id => $value ) {

				$query = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' WHERE rating_item_entry_id = "' . $rating_entry_id . '" AND rating_item_id = "' . $rating_item_id . '"';
				$rows = $wpdb->get_col( $query, 0 );

				if ( $rows[0] == 0 ) {

					$wpdb->insert( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME,
							array( 'rating_item_entry_id' => $rating_entry_id, 'rating_item_id' => $rating_item_id, 'value' => $value ),
							array( '%d', '%d', '%d' )
					);

				} else {

					$wpdb->update( $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME,
							array( 'value' => $value ),
							array( 'rating_item_entry_id' => $rating_entry_id, 'rating_item_id' => $rating_item_id ),
							array( '%d' ),
							array( '%d', '%d' )
					);

				}
			}

			foreach ( $custom_field_values as $custom_field_id => $value ) {

				$query = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'mrp_custom_field_' . intval( $custom_field_id ) . ' WHERE rating_entry_id = %d';
				$rows = $wpdb->get_col( $wpdb->prepare( $query, $rating_entry_id ), 0 );

				if ( $rows[0] == 0 ) {

					$wpdb->insert( $wpdb->prefix . 'mrp_custom_field_' . intval( $custom_field_id ),
							array( 'rating_entry_id' => $rating_entry_id, 'value' => wp_kses( $value, apply_filters( 'mrp_allowed_tags', $allowedtags, $rating_entry ) ), ),
							array( '%d', '%s' )
					);

				} else {

					$wpdb->update( $wpdb->prefix . 'mrp_custom_field_' . intval( $custom_field_id ),
							array( 'value' => wp_kses( $value, apply_filters( 'mrp_allowed_tags', $allowedtags, $rating_entry ) ) ),
							array( 'rating_entry_id' => intval( $rating_entry_id ) ),
							array( '%s' ),
							array( '%d' )
					);

				}
			}

		}

		MRP_Multi_Rating_API::delete_calculated_ratings( array( 'post_id' => $post_id, 'rating_form_id' => $rating_form_id ), true );

		do_action( 'mrp_after_save_rating_entry_success', $rating_entry, $is_new, $entry_status_changed );

		// Set cookie if restriction type is used
		foreach ( $general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION] as $save_rating_restriction_type ) {
			if ( $save_rating_restriction_type == 'cookie' ) {
				if ( ! headers_sent() ) {
					$hours = intval( $general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION] );
					$time_limit = apply_filters( 'mrp_duplicate_check_time_limit', $hours * 60 * 60 );
					setcookie( MRP_Multi_Rating::POST_SAVE_RATING_COOKIE . '-' . $rating_form_id . '-' . $post_id, true, time() + $time_limit, COOKIEPATH, COOKIE_DOMAIN, false, true );
				}

				break;
			}
		}

		return array(
				'rating_entry' => $rating_entry,
				'validation_results' => $validation_results
		);

	}

	/**
	 * Get rating items
	 *
	 * @param array $params	rating_item_entry_id, post_id and rating_form_id
	 * @return rating items
	 */
	public static function get_rating_items( $params = array() ) {

		$rating_form_id = isset( $params['rating_form_id'] ) ? intval( $params['rating_form_id'] ) : null;
		$rating_item_ids = isset( $params['rating_item_ids'] ) ? $params['rating_item_ids'] : null;
		$rating_entry_id = isset( $params['rating_entry_id'] ) ? intval( $params['rating_entry_id'] ) : null;
		$post_id = isset( $params['post_id'] ) ? esc_sql( $params['post_id'] ) : null;

		$rating_items = wp_cache_get( 'mrp_rating_items' );

		global $wpdb;

		if ( $rating_items === false ) {

			$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME;
			$rows = $wpdb->get_results( $query );

			$rating_items = array();
			foreach ( $rows as $row ) {

				$rating_item_id = $row->rating_item_id;
				$description = apply_filters( 'mrp_translate_single_string', $row->description, 'rating-item-' . $rating_item_id . '-description' );
				$default_option_value = $row->default_option_value;
				$max_option_value = $row->max_option_value;
				$option_value_text = apply_filters( 'mrp_translate_single_string', $row->option_value_text, 'rating-item-' . $rating_item_id . '-option-value-text' );
				$type = $row->type;
				$only_show_text_options = $row->only_show_text_options ? true : false;

				$rating_items[$rating_item_id] = array(
						'max_option_value' => intval( $max_option_value ),
						'rating_item_id' => intval( $rating_item_id ),
						'description' => stripslashes( $description ),
						'default_option_value' => intval( $default_option_value ),
						'option_value_text' => stripslashes( $option_value_text ),
						'type' => $type,
						'only_show_text_options' => $only_show_text_options
				);
			}

			wp_cache_add( 'mrp_rating_items', $rating_items );
		}

		if ( $rating_item_ids ) {

			$temp_rating_items = $rating_items;
			$rating_items = array();

			foreach ( explode( ',', $rating_item_ids ) as $rating_item_id ) {
				if ( isset( $temp_rating_items[intval( $rating_item_id )] ) ) {
					$rating_items[intval( $rating_item_id )] = $temp_rating_items[intval( $rating_item_id )];
				}
			}

		} else if ( $rating_form_id ) {

			$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

			return $rating_form['rating_items'];

		} else if ( $rating_entry_id || $post_id ) {

			$temp_rating_items = $rating_items;
			$rating_items = array();

			$query = 'SELECT riev.rating_item_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' AS rie, '
					. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' AS riev '
					. 'WHERE rie.rating_item_entry_id = riev.rating_item_entry_id';
			$where_args = array();

			$added_to_query = true;

			// rating_entry_id
			if ( isset( $rating_entry_id ) ) {

				if ( $added_to_query == true ) {
					$query .= ' AND';
					$added_to_query = false;
				}

				$query .= ' rie.rating_item_entry_id = %d';
				array_push( $where_args, $rating_entry_id );
				$added_to_query = true;
			}

			// post_id
			if ( isset( $post_id ) ) {

				if ( $added_to_query == true ) {
					$query .= ' AND';
					$added_to_query = false;
				}

				$query .= ' rie.post_id = %d';
				array_push( $where_args, $post_id );
				$added_to_query = true;
			}

			if ( count( $where_args ) > 0 ) {
				$query = $wpdb->prepare( $query, $where_args );
			}

			$rating_item_ids = $wpdb->get_col( $query );

			foreach ( $rating_item_ids as $rating_item_id ) {
				$rating_items[intval( $rating_item_id )] = $temp_rating_items[intval( $rating_item_id )];
			}
		}

		return (array) $rating_items; // make sure it's cast
	}

	/**
	 * Get rating forms
	 *
	 * @return rating forms array
	 */
	public static function get_rating_forms() {

		global $wpdb;
		$query = 'SELECT rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;
		$rows = $wpdb->get_results( $query, ARRAY_A );

		$rating_forms = array();
		foreach ( $rows as $row ) {
			$rating_form = MRP_Multi_Rating_API::get_rating_form( $row['rating_form_id'] );
			$rating_forms[$row['rating_form_id']] = $rating_form;
		}

		return $rating_forms;
	}

	/**
	 * Returns rating forms data including rating items and custom fields
	 *
	 * @param int $rating_form_id
	 * @return array $rating_forms
	 */
	public static function get_rating_form( $rating_form_id ) {

		$rating_forms = wp_cache_get( 'mrp_rating_forms' );

		if ( $rating_forms === false ) {
			$rating_forms = array();
		}

		if ( ! isset( $rating_forms[$rating_form_id] ) ) {

			global $wpdb;

			$row = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME
					. ' WHERE rating_form_id = %d', $rating_form_id ), ARRAY_A, 0 );

			if ( ! isset( $row ) ) {
				return null;
			}

			$name = $row['name'];
			$rating_form_id = $row['rating_form_id'];

			$rows = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME
					. ' WHERE rating_form_id = %d', $rating_form_id ), ARRAY_A );

			$all_rating_items = (array) MRP_Multi_Rating_API::get_rating_items();
			$all_custom_fields = MRP_Multi_Rating_API::get_custom_fields();

			$rating_items = array();
			$custom_fields = array();
			$review_fields = array();

			foreach ( $rows as $row ) {

				if ( $row['item_type'] == 'rating-item' ) {

					$rating_item = $all_rating_items[intval( $row['item_id'] )];
					$rating_item['weight'] = $row['weight'];
					$rating_item['required'] = $row['required'];
					$rating_item['allow_not_applicable'] = $row['allow_not_applicable'];
					$rating_items[intval( $row['item_id'] )] = $rating_item;

				} else if ( $row['item_type'] == 'custom-field' ) {

					$custom_field = $all_custom_fields[intval( $row['item_id'] )];
					$custom_field['required'] = $row['required'];
					$custom_fields[intval( $row['item_id'] )] = $custom_field;

				} else {

					$review_field = array( 'required' => $row['required'], 'review_field_id' => intval( $row['item_id'] ) );

					if ( intval( $row['item_id'] ) == MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID ) {
						$review_field['label'] = __( 'Title', 'multi-rating-pro' );
						$review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] = $review_field;
					} else if ( intval( $row['item_id'] ) == MRP_Multi_Rating::NAME_REVIEW_FIELD_ID ) {
						$review_field['label'] = __( 'Name', 'multi-rating-pro' );
						$review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] = $review_field;
					} else if ( intval( $row['item_id'] ) == MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID ) {
						$review_field['label'] = __( 'Email', 'multi-rating-pro' );
						$review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] = $review_field;
					} else if ( intval( $row['item_id'] ) == MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID ) {
						$review_field['label'] = __( 'Comment', 'multi-rating-pro' );
						$review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] = $review_field;
					}

				}
			}

			$rating_form = array(
					'name' => apply_filters( 'mrp_translate_single_string', $name, 'rating-form-' . $rating_form_id . '-name' ),
					'rating_form_id' => $rating_form_id,
					'rating_items' => $rating_items,
					'custom_fields' => $custom_fields,
					'review_fields' => $review_fields
			);

			$rating_forms[$rating_form_id] = $rating_form;

			wp_cache_add( 'mrp_rating_forms', $rating_forms );
		}

		return isset( $rating_forms[$rating_form_id] ) ? $rating_forms[$rating_form_id] : null;
	}

	/**
	 * Retrieves the custom fields for a rating form
	 *
	 * @param $prating_form_id
	 */
	public static function get_custom_fields( $rating_form_id = null ) {

		$custom_fields = wp_cache_get( 'mrp_custom_fields' );

		if ( $custom_fields === false ) {

			global $wpdb;

			$custom_fields = array();

			$query = 'SELECT * FROM ' . $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME;

			$rows = $wpdb->get_results( $query, ARRAY_A );

			foreach ( $rows as $row ) {

				$custom_field_id = $row['custom_field_id'];
				$label = apply_filters( 'mrp_translate_single_string', $row['label'], 'custom-field-' . $custom_field_id . '-label' );
				$placeholder = apply_filters( 'mrp_translate_single_string', $row['placeholder'], 'custom-field-' . $custom_field_id . '-placeholder' );
				$max_length = $row['max_length'];
				$type = $row['type'];

				$custom_fields[$custom_field_id] = array(
						'custom_field_id' => intval( $custom_field_id ),
						'label' => stripslashes( $label ),
						'max_length' => intval( $max_length ),
						'type' => $type,
						'placeholder' => stripslashes( $placeholder )
				);
			}

			wp_cache_add( 'mrp_custom_fields', $custom_fields );
		}

		if ( $rating_form_id ) {

			$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );

			return $rating_form['custom_fields'];
		}

		return $custom_fields;
	}


	/**
	 * Gets rating entries of a rating form for a post with filters for user_id
	 *
	 * @param array $params post_id, rating_form_id, user_id and category_id, rating_item_entry_id, limit
	 * @return rating item entries
	 */
	public static function get_rating_entries( $params = array() ) {

		$params = wp_parse_args( $params, array(
				'post_id' => null,
				'rating_form_id' => null,
				'user_id' => null,
				'comments_only' => null,
				'rating_entry_ids' => null,
				'limit' => null,
				'comment_id' => null,
				'from_date' => null,
				'to_date' => null,
				'taxonomy' => null,
				'term_id' => 0,
				'approved_comments_only' => false,
				'user_roles' => null,
				'published_posts_only' => true,
				'entry_status' => 'approved'
		) );

		extract( $params );

		global $wpdb;

		/*
		 * Select
		 */
		$query_select = 'SELECT rie.user_id, rie.rating_item_entry_id, IFNULL( c.comment_author, rie.name ) AS name,'
				. ' IFNULL( c.comment_author_email, rie.email ) AS email, IFNULL( c.comment_content, rie.comment ) AS comment,'
				. ' rie.rating_form_id, rie.post_id, rie.entry_date, rie.comment_id, rie.entry_status, rie.title';
		$query_select = apply_filters( 'mrp_entries_query_select', $query_select, $params );

		/*
		 * From
		*/
		$query_from = ' FROM ' . $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' rie';
		$added_to_query = true;

		$query_from = apply_filters( 'mrp_entries_query_from', $query_from, $params );

		/*
		 * Join
		 */
		$query_join = '';

		if ( $published_posts_only || $taxonomy != null ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->posts . ' p ON rie.post_id = p.ID';
		}

		if ( $taxonomy != null ) {
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_relationships rel ON rel.object_id = p.ID';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'term_taxonomy tax ON tax.term_taxonomy_id = rel.term_taxonomy_id';
			$query_join .= ' LEFT JOIN ' . $wpdb->prefix . 'terms t ON t.term_id = tax.term_id';
		}


		if ( $user_roles != null ) {
			$query_join .= ' INNER JOIN ' . $wpdb->users . ' u ON u.ID = rie.user_id';
			$query_join .= ' INNER JOIN ' . $wpdb->prefix . 'usermeta um ON u.ID = um.user_id';
		}

		$query_join .= ' LEFT JOIN ' . $wpdb->comments . ' c ON rie.comment_id = c.comment_ID';
		$added_to_query = true;

		$query_join = apply_filters( 'mrp_entries_query_join', $query_join, $params );

		/*
		 * Where
		 */
		$query_where = '';
		$where_args = array();
		$added_to_query = false;
		if ( $rating_form_id || $post_id || $user_id || $user_roles || $taxonomy || $comment_id || $comments_only
				|| $rating_entry_ids || $from_date || $to_date || $published_posts_only ) {
			$query_where .= ' WHERE';
		}

		if ( $comment_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.comment_id = %d';
			array_push( $where_args, $comment_id );
			$added_to_query = true;
		}

		if ( $post_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.post_id = %d';
			array_push( $where_args, $post_id );
			$added_to_query = true;
		}

		if ( $from_date ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_date >= %s';
			array_push( $where_args, $from_date );
			$added_to_query = true;
		}

		if ( $to_date ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_date <= %s';
			array_push( $where_args, $to_date );
			$added_to_query = true;
		}

		$rating_entry_ids = ( strlen( $rating_entry_ids ) > 0 ) ? explode( ',', $rating_entry_ids ) : null;
		if ( $rating_entry_ids && count( $rating_entry_ids ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
				$added_to_query = true;
			}

			$query_where .= ' rie.rating_item_entry_id IN ( ' . implode( ', ', array_fill( 0, count( $rating_entry_ids ), '%d' ) ) . ' ) ';
			$where_args = array_merge( $where_args, $rating_entry_ids );
		}

		if ( $user_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.user_id = %d';
			array_push( $where_args, $user_id );
			$added_to_query = true;
		}

		if ( $user_roles != null ) {
			$user_roles = explode( ',', $user_roles );

			if ( is_array( $user_roles)  && count( $user_roles ) ) {

				if ( $added_to_query ) {
					$query_where .= ' AND ';
					$added_to_query = true;
				}

				$query_where .= 'u.ID = rie.user_id AND um.meta_key  = "' . $wpdb->prefix . 'capabilities" AND (';
				$index = 1;
				foreach ( $user_roles as $user_role ) {
					$query_where .= ' um.meta_value LIKE "%%' . $wpdb->esc_like( $user_role ) . '%%"';
					if ( $index < count( $user_roles ) ) {
						$query_where .= ' OR ';
					}
					$index++;
				}
				$query_where .= ' ) ';
				$added_to_query = true;
			}

		}

		if ( $taxonomy ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' tax.taxonomy = %s';
			array_push( $where_args, $taxonomy );

			if ( $term_id ) {
			 	$query_where .= ' AND t.term_id = %d';
			 	array_push( $where_args, $term_id );
			}

			$added_to_query = true;
		}

		if ( $comments_only == true ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= '  ( ( rie.comment != "" AND rie.comment IS NOT NULL ) OR rie.comment_id IS NOT NULL )';
			$added_to_query = true;
		}

		if ( $approved_comments_only ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' ( ( rie.comment_id = "" OR rie.comment_id IS NULL ) OR ( rie.comment_id IS NOT NULL AND c.comment_approved = "1" ) )';
			$added_to_query = true;
		}

		if ( $published_posts_only ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' ( p.post_status = "publish" OR p.post_type = "attachment" ) ';
			$added_to_query = true;
		}

		if ( $entry_status && strlen( $entry_status ) > 0 ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.entry_status = %s';
			array_push( $where_args, $entry_status );
			$added_to_query = true;
		}

		if ( $rating_form_id ) {
			if ( $added_to_query ) {
				$query_where .= ' AND';
			}

			$query_where .= ' rie.rating_form_id = %d';
			array_push( $where_args, $rating_form_id );
			$added_to_query = true;
		}

		if ( count( $where_args ) > 0 ) {
			$query_where = $wpdb->prepare( $query_where, $where_args );
		}

		$query_where = apply_filters( 'mrp_entries_query_where', $query_where, $params );

		/*
		 * Group by
		 */
		$group_by_query = ' GROUP BY rie.rating_form_id, rie.post_id, rie.rating_item_entry_id';
		$query_group_by = apply_filters( 'mrp_entries_query_group_by', $group_by_query, $params );

		/*
		 * Order by
		 */
		$query_order_by = ' ORDER BY rie.entry_date ASC';
		$query_order_by = apply_filters( 'mrp_entries_query_order_by', $query_order_by, $params );

		/*
		 * Limit
		 */
		$query_limit = '';
		if ( $limit && is_numeric( $limit ) ) {
			if ( intval( $limit ) > 0 ) {
				if ( ! $offset || ! ( $offset && is_numeric( $offset ) && intval( $offset ) >= 0 ) ) {
					$offset = 0;
				}
				$query_limit .= ' LIMIT ' . intval( $offset ) . ', ' . intval( $limit );
			}
		}
		$query_limit = apply_filters( 'mrp_entries_query_limit', $query_limit, $params );

		/*
		 * Query results
		 */
		$query = $query_select . $query_from . $query_join . $query_where . $query_group_by . $query_order_by .  $query_limit;
		$query = apply_filters( 'mrp_entries_query', $query, $params );

		$rows = $wpdb->get_results( $query );

		$rating_entries = array();
		foreach ( $rows as $row ) {

			$rating_entry_id = intval( $row->rating_item_entry_id );
			$rating_form_id = $row->rating_form_id;

			$custom_field_values = MRP_Multi_Rating_API::get_custom_field_values( $rating_entry_id, $rating_form_id );
			$rating_item_values = MRP_Multi_Rating_API::get_rating_item_values( $rating_entry_id, $rating_form_id );

			$rating_entry = array(
					'rating_entry_id' => $rating_entry_id,
					'user_id' => intval( $row->user_id ),
					'title' => stripslashes( $row->title ),
					'name' => stripslashes( $row->name ),
					'email' => stripslashes( $row->email ),
					'comment' => stripslashes( $row->comment ),
					'rating_form_id' => $rating_form_id,
					'post_id' => intval( $row->post_id ),
					'entry_date' => $row->entry_date,
					'comment_id' => intval( $row->comment_id ),
					'entry_status' => $row->entry_status,
					'rating_item_values' => $rating_item_values,
					'custom_field_values' => $custom_field_values
				);

			array_push( $rating_entries, $rating_entry );
		}

		return $rating_entries;
	}

	/**
	 * @deprecated since v5.2
	 */
	public static function display_rating_form( $params = array() ) { return mrp_rating_form( $params ); }
	public static function display_rating_result( $params = array()) { return mrp_rating_result( $params ); }
	public static function display_rating_entry_details_list( $params = array() ) { return mrp_rating_entry_details_list( $params ); }
	public static function display_rating_results_list( $params = array() ) { return mrp_rating_results_list( $params ); }
	public static function display_user_rating_results( $params = array() ) { return mrp_user_rating_results( $params ); }
	public static function get_comment_rating_result( $params = array() ) { return mrp_comment_rating_result( $params ); }
	public static function display_comment_rating_form( $params = array() ) { return mrp_comment_rating_form( $params ); }
	public static function display_user_ratings_dashboard( $params = array() ) { return mrp_user_ratings_dashboard( $params ); }
	public static function display_rating_item_results( $params = array() ) { return mrp_rating_item_results( $params ); }
	public static function delete_rating_result( $params = array(), $and = false ) { return MRP_Multi_Rating_API::delete_calculated_ratings( $params, $and); }

	/**
	 * @deprecated since v4.0
	 */
	public static function check_user_has_submitted_rating( $rating_form_id, $post_id, $user_id, $rating_entry_id = null ) { return MRP_Multi_Rating_API::user_rating_exists( $rating_form_id, $post_id, $user_id ); }
	public static function get_rating_item_entries( $params = array() ) { return MRP_Multi_Rating_API::get_rating_entries( $params ); }
	public static function calculate_rating_item_entry_result( $rating_item_entry_id, $rating_items = null ) { return MRP_Multi_Rating_API::get_rating_entry_result( array( 'rating_entry_id' => $rating_item_entry_id ) ); }
	public static function get_rating_results( $params = array() ) { return MRP_Multi_Rating_API::get_rating_result_list( $params ); }
	public static function get_user_rating_results( $params = array() ) { return MRP_Multi_Rating_API::get_rating_entry_result_list( $params ); }
	public static function display_rating_result_reviews( $params = array() ) { return MRP_Multi_Rating_API::display_rating_entry_details_list( $params ); }

}
?>

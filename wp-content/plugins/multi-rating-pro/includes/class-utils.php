<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Utils helper class
 * 
 * @author dpowney
 *
 */
class MRP_Utils {

	/**
	 * Used to uniquely identify a rating form on a page
	 */
	public static $sequence = 0;
	
	/**
	 * Validates a rating form
	 * 
	 * @param unknown $validation_results validate results to add to
	 * @param unknown $rating_entry data to validate
	 * @params boolean $validate_fields whether you want to validate rating item, custom fields and review fields
	 * 
	 * @return unknown $validation_results
	 */
	public static function validate_rating_entry( $validation_results = array(), $rating_entry, $validate_fields = true ) {
		
		$validation_results = apply_filters( 'mrp_before_rating_entry_validation', $validation_results, $rating_entry );
		
		$rating_form_id = $rating_entry['rating_form_id'];
		$post_id = $rating_entry['post_id'];
		
		$user_id = isset( $rating_entry['user_id'] ) ? $rating_entry['user_id'] : null;
		$rating_entry_id = isset( $rating_entry['rating_entry_id'] ) ? $rating_entry['rating_entry_id'] : null;
		$rating_item_values = isset( $rating_entry['rating_item_values'] ) ? $rating_entry['rating_item_values'] : array();
		$custom_field_values = isset( $rating_entry['custom_field_values'] ) ? $rating_entry['custom_field_values'] : array();
		$title = isset( $rating_entry['title'] ) ? $rating_entry['title'] : null;
		$name = isset( $rating_entry['name'] ) ? $rating_entry['name'] : null;
		$email = isset( $rating_entry['email'] ) ? $rating_entry['email'] : null;
		$comment = isset( $rating_entry['comment'] ) ? $rating_entry['comment'] : null;
		$comment_id = isset( $rating_entry['comment_id'] ) ? $rating_entry['comment_id'] : null;
		$entry_date = isset( $rating_entry['entry_date'] ) ? $rating_entry['entry_date'] : null;
		
		$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		
		$rating_items = $rating_form['rating_items'];
		$custom_fields = $rating_form['custom_fields'];
		$review_fields = $rating_form['review_fields'];
		
		// validate post id
		$post = get_post( $post_id );
		if ( ! $post ) {
			array_push( $validation_results, array(
					'severity' => 'error',
					'name' => 'invalid_post_id',
					'message' => __( 'An error has occured. Post id does not exist.', 'multi-rating-pro' )
			) );
		}
		// will save an extra db call if you want to add custom validation on post status for example
		$rating_entry['post'] = $post;
		
		// validate rating form id
		if ( $rating_form == null ) {
			array_push( $validation_results, array(
					'severity' => 'error',
					'name' => 'invalid_rating_form_id',
					'message' => __( 'An error has occured. Rating form id does not exist.', 'multi-rating-pro' )
			) );
		}
		
		if ( $validate_fields ) { // rating item, custom fields and review fields
			
			$has_selected_rating_items = false;
			// check if rating item, custom field or review fields are required
			foreach ( $rating_item_values as $rating_item_id => $value ) {
				
				$rating_item = isset( $rating_items[$rating_item_id] ) ? $rating_items[$rating_item_id] : null;
				
				// validate required, note that not applicable rating items have a value of -1
				if ( $value == 0 && $rating_item['required'] == true ) { 	
					array_push( $validation_results, array(
							'severity' => 'error',
							'name' => 'rating_item_required_error',
							'field' => 'rating-item-' . $rating_item_id,
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
	
				}
				
				if ( $value >= 0 ) {
					$has_selected_rating_items = true;
				}
			}
		
			if ( ! $has_selected_rating_items ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'name' => 'no_applicable_rating_items_error',
						'message' => __( 'At least one rating item must be applicable.', 'multi-rating-pro' )
				) );
			}
			
			foreach ( $custom_field_values as $custom_field_id => $value ) {
				
				$custom_field = isset( $custom_fields[$custom_field_id] ) ? $custom_fields[$custom_field_id] : null;
				
				// validate length
				if ( strlen( $value ) > $custom_field['max_length'] ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'custom-field-' . $custom_field_id,
							'name' => 'custom_field_length_error',
							'message' => sprintf( __( 'Field cannot be greater than %s characters.', 'multi-rating-pro' ), $custom_field['label'], $custom_field['max_length'] )
					) );
				}
					
				// validate required
				if ( strlen( $value ) == 0 && $custom_field['required'] ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'custom-field-' . $custom_field_id,
							'name' => 'custom_field_required_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
				}
			}
			
			if ( isset( $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID] ) ) {
				
				// validate required
				if ( isset( $title ) && strlen( $title ) == 0 && $review_fields[MRP_Multi_Rating::TITLE_REVIEW_FIELD_ID]['required'] == true ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'title',
							'name' => 'title_required_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
				}
				
				// validate length
				if ( isset( $title ) && strlen( $title ) > 255 ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'title',
							'name' => 'title_length_error',
							'message' => __( 'Title cannot be greater than 255 characters.', 'multi-rating-pro' ) 
					) );
				}
			}
			
			if ( isset( $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID] ) && $user_id == 0 ) {
			
				// validate required
				if ( isset( $name ) && strlen( $name ) == 0 && $review_fields[MRP_Multi_Rating::NAME_REVIEW_FIELD_ID]['required'] == true ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'name',
							'name' => 'name_required_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
				}
				
				// validate length
				if ( isset( $name ) && strlen( $name ) > 100 ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'name',
							'name' => 'name_length_error',
							'message' => __( 'Name cannot be greater than 100 characters.', 'multi-rating-pro' ) ) );
				}
			}
			
			if ( isset( $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID] ) && $user_id == 0 ) {
					
				// validate required
				if ( isset( $email ) && strlen( $email ) == 0 && $review_fields[MRP_Multi_Rating::EMAIL_REVIEW_FIELD_ID]['required'] == true ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'email',
							'name' => 'email_required_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
				}
				
				// validate length
				if ( isset( $email ) && strlen( $email ) > 255 ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'email',
							'name' => 'email_length_error',
							'message' => __( 'E-mail cannot be greater than 255 characters.', 'multi-rating-pro' )
					) );
				}
				
				// validate email
				if ( isset( $email ) && strlen( $email ) > 0 && ! is_email( $email ) ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'email',
							'name' => 'invalid_email',
							'message' => __( 'E-mail is invalid.', 'multi-rating-pro' )
					) );
				}
				
			}
			
			if ( isset( $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID] ) ) {
				
				// validate required
				if ( isset( $comment ) && strlen( $comment ) == 0 && $review_fields[MRP_Multi_Rating::COMMENT_REVIEW_FIELD_ID]['required'] == true ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'comment',
							'name' => 'comment_required_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION]
					) );
				}
				
				if ( isset( $comment ) && strlen( $comment ) > 2000 ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'field' => 'comment',
							'name' => 'comment_length_error',
							'message' => __( 'Comments cannot be greater than 2000 characters.', 'multi-rating-pro' )
					) );
				}
	
			}
		}
		
		if ( $rating_entry_id == null ) {
			
			if ( ! MRP_Utils::allow_anonymous_rating_check( $post_id, $user_id ) ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'name' => 'allow_anonymous_ratings_error',
						'message' => $custom_text_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION]
				) );
			}

			if ( $user_id == 0) { // check for duplicates

				if ( MRP_Utils::duplicate_rating_check( $post_id, $rating_form_id ) ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'name' => 'save_rating_restriction_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_ERROR_MESSAGE_OPTION]
					) );
				}
		
			} else { // check user roles
				
				if ( MRP_Utils::disallowed_user_roles_check( $user_id ) ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'name' => 'disallowed_user_roles_ratings_error',
							'message' => $custom_text_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_ERROR_MESSAGE_OPTION]
					) );
				}
			}
			
			// check if a rating exists for a user
			if ( $user_id != 0 && MRP_Multi_Rating_API::user_rating_exists( 
					array( 'rating_form_id' => $rating_form_id, 'post_id' => $post_id, 'user_id' => $user_id ) ) ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'name' => 'already_submitted_rating_form_error',
						'message' => $custom_text_settings[MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION]
				) );
			}
			
		} else { // editing an existing rating entry
			
			if ( $entry_date != null && strlen( $entry_date ) > 0 ) {
				if ( ! preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $entry_date ) ) {
					array_push( $validation_results, array(
							'severity' => 'error',
							'name' => 'invalid_entry_date_error',
							'message' => __( 'Invalid entry date', 'multi-rating-pro' )
					) );
					
				}
			}

			// check rating entry id matches rating_form_id, post_id and user_id combination
			// Note: we do not need to check this user's with mrp_manage_ratings capability
			// as they might be reassigning a rating entry to a different user
			// FIXME User's with the mrp_manage_ratings capability can accidentally assign multiple
			// ratings to the same rating form and post if not careful
			if ( $user_id != 0 && ! current_user_can( 'mrp_manage_ratings' )
					&& ! MRP_Multi_Rating_API::user_rating_exists( 
							array( 'rating_form_id' => $rating_form_id, 'post_id' => $post_id, 'user_id' => $user_id, 'rating_entry_id' => $rating_entry_id ) ) ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'name' => 'invalid_update_error',
						'message' =>__( 'An error has occured. Unable to match rating entry id with rating form id, post id and user id.', 'multi-rating-pro' )
				) );
			}
			
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
				
			$allow_user_update_or_delete_rating = $general_settings[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING];
			
			if ( $user_id != 0 && ! $allow_user_update_or_delete_rating && ! current_user_can( 'mrp_moderate_ratings' ) ) {
				array_push( $validation_results, array(
						'severity' => 'error',
						'name' => 'not_allowed_update_delete_rating',
						'message' => __( 'You are not allowed to update or delete ratings.', 'multi-rating-pro' )
				) );
			}
		}
		
		$validation_results = apply_filters( 'mrp_after_rating_entry_validation', $validation_results, $rating_entry );
		
		return $validation_results;
	}
	
	/**
	 * Gets the highest priority filter which applies
	 */
	public static function get_filter( $post_id ) {
			
		$filters = get_option( 'mrp_filters' );
		
		$selected = null;
		
		if ( is_array( $filters ) ) {
			// sort filters by priority...
			uasort( $filters, array( 'MRP_Utils' , 'sort_filter_by_lowest_priority' ) );
			
			// save time and get the post type, taxonomies and terms associated with post
			$post_type = get_post_type( $post_id );
			$taxonomies = get_object_taxonomies( $post_type, 'names' );
			
			$args = array(
					'post_type' => $post_type,
					'taxonomies' => $taxonomies
			);
			
			// check whether each filter can be applied. If multiple apply, the filter 
			// with the highest priority takes precedence and will be returned
			foreach ( $filters as $filter ) {
				if ( MRP_Utils::check_filter( $filter, $post_id, $args ) ) {		
					$selected = $filter;
				}
			}
		}
		
		return $selected;
	} 

	/**
	 * Checks filters for a post
	 */
	public static function check_filter( $filter, $post_id, $args = array() ) {
	
		$post_type = null;
		if ( isset( $args['post_type'] ) && is_array( $args['post_type'] ) ) {
			$post_type = $args['post_type'];
		} else {
			$post_type = get_post_type( $post_id );
		}
		
		// now check whether filter applies, remember "" = all
		if ( $filter['filter_type'] == 'post_type' ) {				
			
			if ( in_array( $post_type, $filter['post_types'] ) || in_array( '', $filter['post_types'] ) ) {
				return true;
			}
			
		} else if ( $filter['filter_type'] == 'taxonomy' ) { // taxonomy & terms
		
			$taxonomies = array();
			if ( isset( $args['taxonomies'] ) && is_array( $args['taxonomies'] ) ) {
				$taxonomies = $args['taxonomies'];
			} else {
				$taxonomies = get_object_taxonomies( $post_type, 'names' );
			}
			
			if ( in_array( $filter['taxonomy'], $taxonomies ) ) {
				
				if ( in_array( '', $filter['terms'] ) ) {
					return true;
				} else {
					$terms = wp_get_post_terms( $post_id, $filter['taxonomy'] );
						
					foreach ( $terms as $term ) {
						if ( in_array( $term->name, $filter['terms'] ) ) {
							return true;
						}
					}	
				}
			}
		} else if ( $filter['filter_type'] == 'post-ids' ) {
			
			if ( in_array( $post_id, $filter['post_ids'] ) ) {
				return true;
			}
			
		} else if ( $filter['filter_type'] == 'page-urls' ) {
			
			if ( in_array( get_permalink( $post_id ), $filter['page_urls'] ) ) {
				return true;
			}
			
		}
		
		return false;
	}
	
	/**
	 * Sorts filter by lowest priority
	 *
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	public static function sort_filter_by_lowest_priority( $a, $b ) {
	
		if ( ! isset( $a['priority'] ) ) {
			return 0;
		}
			
		if ( $a['priority'] == $b['priority'] ) {
			return 0;
		}
	
		return (  $a['priority'] > $b['priority'] ) ? -1 : 1;
	}

	
	/**
	 * Checks if post type is enabled
	 * 
	 * @param $post_id
	 */
	public static function check_post_type_enabled( $post_id ) {
		
		$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
		
		$post_types = $auto_placement_settings[MRP_Multi_Rating::POST_TYPES_OPTION];
		if ( ! isset( $post_types ) ) {
			return false;
		}
		
		if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
			$post_types = array( $post_types );
		}
		
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, $post_types ) ) {
			return false;
		}
		
		return true;
	}
	
	/** 
	 * Helper function to iterate validation results for errors
	 * 
	 * @param $validation_results
	 */
	public static function has_validation_error( $validation_results ) {
		foreach ( $validation_results as $validation_result ) {
			if ( $validation_result['severity'] == 'error' ) {
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Gets the current URL
	 *
	 * @return current URL
	 */
	public static function get_current_url() {
		$url = 'http';
	
		if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on') {
			$url .= "s";
		}
	
		$url .= '://';
	
		if ( $_SERVER['SERVER_PORT'] != '80') {
			$url .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
		} else {
			$url .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
		}
	
		return MRP_Utils::normalize_url( $url );
	}
	
	/**
	 * Normalizes the URL (some of the best parts of RFC 3986)
	 *
	 * @param unknown_type $url
	 * @return string
	 */
	public static function normalize_url( $url ) {
	
		// TODO return error for bad URLs
	
		// Process from RFC 3986 http://en.wikipedia.org/wiki/URL_normalization
	
		// Limiting protocols.
		if ( ! parse_url( $url, PHP_URL_SCHEME ) ) {
			$url = 'http://' . $url;
		}
	
		$parsed_url = parse_url( $url );
		if ( $parsed_url === false ) {
			return '';
		}
	
		// user and pass components are ignored
	
		// TODO Removing or adding “www” as the first domain label.
		$host = preg_replace( '/^www\./', '', $parsed_url['host'] );
	
		// Converting the scheme and host to lower case
		$scheme = strtolower( $parsed_url['scheme'] );
		$host = strtolower( $host );
	
		$path = $parsed_url['path'];
		// TODO Capitalizing letters in escape sequences
		// TODO Decoding percent-encoded octets of unreserved characters
	
		// Removing the default port
		$port = '';
		if ( isset( $parsed_url['port'] ) ) {
			$port = $parsed_url['port'];
		}
		if ( $port == 80 ) {
			$port = '';
		}
	
		// Removing the fragment # (do not get fragment component)
	
		// Removing directory index (i.e. index.html, index.php)
		$path = str_replace( 'index.html', '', $path );
		$path = str_replace( 'index.php', '', $path );
	
		// Adding trailing /
		$path_last_char = $path[strlen( $path ) -1];
		if ( $path_last_char != '/' ) {
			$path = $path . '/';
		}
	
		// TODO Removing dot-segments.
	
		// TODO Replacing IP with domain name.
	
		// TODO Removing duplicate slashes
		$path = preg_replace( "~\\\\+([\"\'\\x00\\\\])~", "$1", $path );
	
		// construct URL
		$url =  $scheme . '://' . $host . $path;
	
		// Add query params if they exist
		// Sorting the query parameters.
		// Removing unused query variables
		// Removing default query parameters.
		// Removing the "?" when the query is empty.
		$query = '';
		if ( isset( $parsed_url['query'] ) ) {
			$query = $parsed_url['query'];
		}
		if ( $query ) {
			$query_parts = explode( '&', $query );
			$params = array();
			foreach ( $query_parts as $param ) {
				$items = explode( '=', $param, 2 );
				$name = $items[0];
				$value = '';
				if ( count( $items ) == 2 ) {
					$value = $items[1];
				}
				$params[$name] = $value;
			}
			ksort( $params );
			$count_params = count( $params );
			if ( $count_params > 0 ) {
				$url .= '?';
				$index = 0;
				foreach ( $params as $name => $value ) {
					$url .= $name;
					if ( strlen( $value ) != 0 ) {
						$url .= '=' . $value;
					}
					if ( $index++ < ( $count_params - 1 ) ) {
						$url .= '&';
					}
				}
			}
		}
	
		// Remove some query params which we do not want
		$url = MRP_Utils::remove_query_string_params( $url, array() );
	
		return $url;
	}
	
	/**
	 * Removes query string parameters from URL
	 * @param $url
	 * @param $param
	 * @return string
	 *
	 * @since 1.2
	 */
	public static function remove_query_string_params( $url, $params ) {
		foreach ( $params as $param ) {
			$url = preg_replace( '/(.*)(\?|&)' . $param . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&' );
			$url = substr( $url, 0, -1 );
		}
		return $url;
	}
	
	/** 
	 * Gets the Font Awesome icon classes based on version
	 * 
	 * @param $icon_font_library
	 * @return array icon classes
	 */
	public static function get_icon_classes( $icon_font_library ) {
	
		$icon_classes = array();
		
		if ( $icon_font_library == 'font-awesome-4.0.3' || $icon_font_library == 'font-awesome-4.1.0' 
				|| $icon_font_library == 'font-awesome-4.2.0' || $icon_font_library == 'font-awesome-4.3.0'
				|| $icon_font_library == 'font-awesome-4.5.0' || $icon_font_library == 'font-awesome-4.6.3'
				|| $icon_font_library == 'font-awesome-4.7.0' ) {
			$icon_classes['star_full'] = 'fa fa-star mrp-star-full';
			$icon_classes['star_hover'] = 'fa fa-star mrp-star-hover';
			$icon_classes['star_half'] = 'fa fa-star-half-o mrp-star-half';
			$icon_classes['star_empty'] = 'fa fa-star-o mrp-star-empty';
			$icon_classes['minus'] = 'fa fa-minus-circle mrp-minus';
			$icon_classes['thumbs_up_on'] = 'fa fa-thumbs-up mrp-thumbs-up-on';
			$icon_classes['thumbs_up_off'] = 'fa fa-thumbs-o-up mrp-thumbs-up-off';
			$icon_classes['thumbs_down_on'] = 'fa fa-thumbs-down mrp-thumbs-down-on';
			$icon_classes['thumbs_down_off'] = 'fa fa-thumbs-o-down mrp-thumbs-down-off';
			$icon_classes['spinner'] = 'fa fa-spinner fa-spin mrp-spinner';
		} else if ( $icon_font_library == 'font-awesome-3.2.1' ) {
			$icon_classes['star_full'] = 'icon-star mrp-star-full';
			$icon_classes['star_hover'] = 'icon-star mrp-star-hover';
			$icon_classes['star_half'] = 'icon-star-half-full mrp-star-half';
			$icon_classes['star_empty'] = 'icon-star-empty mrp-star-empty';
			$icon_classes['minus'] = 'icon-minus-sign mrp-minus';
			$icon_classes['thumbs_up_on'] = 'icon-thumbs-up mrp-thumbs-up-on';
			$icon_classes['thumbs_up_off'] = 'icon-thumbs-up-alt mrp-thumbs-up-off';
			$icon_classes['thumbs_down_on'] = 'icon-thumbs-down mrp-thumbs-down-on';
			$icon_classes['thumbs_down_off'] = 'icon-thumbs-down-alt mrp-thumbs-down-off';
			$icon_classes['spinner'] = 'icon-spinner icon-spin mrp-spinner';
		} else if ( $icon_font_library == 'dashicons' ) {
			$icon_classes['star_full'] = 'dashicons dashicons-star-filled mrp-star-full';
			$icon_classes['star_hover'] = 'dashicons dashicons-star-filled mrp-star-hover';
			$icon_classes['star_half'] = 'dashicons dashicons-star-half mrp-star-half';
			$icon_classes['star_empty'] = 'dashicons dashicons-star-empty mrp-star-empty';
			$icon_classes['thumbs_up_on'] = 'dashicons dashicons-thumbs-up mrp-thumbs-up-on';
			$icon_classes['thumbs_up_off'] = 'dashicons dashicons-thumbs-up mrp-thumbs-up-off';
			$icon_classes['thumbs_down_on'] = 'dashicons dashicons-thumbs-down mrp-thumbs-down-on';
			$icon_classes['thumbs_down_off'] = 'dashicons dashicons-thumbs-down mrp-thumbs-down-off';
		}
		
		return apply_filters( 'mrp_icon_classes', $icon_classes, $icon_font_library );
	}


	/**
	 * Gets the client ip address
	 *
	 * @since 2.1
	 */
	public static function get_ip_address() {
		$client_IP_address = '';
		
		if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$client_IP_address = $_SERVER['HTTP_CLIENT_IP'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$client_IP_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_X_FORWARDED'] ) ) {
			$client_IP_address = $_SERVER['HTTP_X_FORWARDED'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED_FOR'] ) ) {
			$client_IP_address = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if ( isset( $_SERVER['HTTP_FORWARDED'] ) ) {
			$client_IP_address = $_SERVER['HTTP_FORWARDED'];
		} else if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$client_IP_address = $_SERVER['REMOTE_ADDR'];
		}
		
		return $client_IP_address;
	}
	
	/** 
	 * Helper function to retrieve list of image sizes and dimensions
	 * 
	 * @param $size
	 * @return 
	 */
	public static function get_image_sizes( $size = '' ) {
	
		global $_wp_additional_image_sizes;
	
		$sizes = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();
	
		// Create the full array with sizes and crop info
		foreach( $get_intermediate_image_sizes as $_size ) {
	
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'large' ) ) ) {
	
				$sizes[$_size]['width'] = get_option( $_size . '_size_w' );
				$sizes[$_size]['height'] = get_option( $_size . '_size_h' );
				$sizes[$_size]['crop'] = (bool) get_option( $_size . '_crop' );
	
			} elseif ( isset( $_wp_additional_image_sizes[$_size] ) ) {
				
				$sizes[$_size] = array(
						'width' => $_wp_additional_image_sizes[$_size]['width'],
						'height' => $_wp_additional_image_sizes[$_size]['height'],
						'crop' =>  $_wp_additional_image_sizes[$_size]['crop']
				);
			}
		}
	
		// Get only 1 size if found
		if ( $size ) {
			if( isset( $sizes[$size] ) ) {
				return $sizes[$size];
			} else {
				return false;
			}
		}
	
		return $sizes;
	}
	
	/**
	 * Gets the default rating form. You can use the mrp_default_rating_form filter to change the default 
	 * rating form. For instance, you could set a specific rating form for a post type or post taxonomy.
	 * 
	 * @param int $post_id
	 * @param array $params
	 * @return $rating_form_id
	 */
	public static function get_rating_form( $post_id = null, $params = array() ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$rating_form_id = $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		
		if ( $post_id != null ) {
			
			$filter = MRP_Utils::get_filter( $post_id );
			
			if ( $filter && $filter['rating_form_id'] != '' ) {
				$rating_form_id = $filter['rating_form_id'];
			}
			
			if ( $filter == null || ( $filter && $filter['override_post_meta'] ) ) {
				
				// if a rating form is not specified in post meta, use default settings
				$temp_rating_form_id = get_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_ID_POST_META, true );
				
				if ( $temp_rating_form_id == null || $temp_rating_form_id == '' ) {
					$rating_form_id = apply_filters( 'mrp_default_rating_form', $rating_form_id, $post_id, $params );
				} else {	
					$rating_form_id = $temp_rating_form_id;
				}
			}
		}

		$rating_form_id = apply_filters( 'mrp_rating_form', $rating_form_id, $post_id, $params );
		
		return intval( $rating_form_id );
		
	}
	
	
	/**
	 * Retrieves a lookup of option values with text for a rating item
	 *
	 * @param $option_value_text
	 * @return lookup array of option value text
	 */
	public static function get_option_value_text_lookup( $option_value_text ) {
		$option_value_text_array = preg_split( '~[\r\n,]+~',  $option_value_text, -1, PREG_SPLIT_NO_EMPTY );
			
		$option_value_text_lookup = array();
		foreach ( $option_value_text_array as $current_option_value_text ) {
			$parts = explode( '=', $current_option_value_text );
	
			$text = isset( $parts[0] ) ? $parts[0] : '';
				
			if ( isset( $parts[1] ) && count( $parts ) == 2 ) {
				$text = $parts[1];
			}

			if ( isset( $parts[0]) && is_numeric( $parts[0] ) ) {
				$value = intval( $parts[0] );
				$option_value_text_lookup[$value] = $text;
			}
				
		}
	
		return $option_value_text_lookup;
	}
	
	/**
	 * Checks whether anonymous ratings are allowed
	 * 
	 * @param unknown $post_id
	 * @param unknown $user_id
	 * @return boolean
	 */
	public static function allow_anonymous_rating_check( $post_id, $user_id ) {
		
		if ( $user_id != 0 )  {
			return true; // not applicable
		}
		
		// check whether anonymous ratings are allowed
		$allow_anonymous_ratings = get_post_meta( $post_id, MRP_Multi_Rating::ALLOW_ANONYMOUS_POST_META, true );
			
		if ( $allow_anonymous_ratings === "" ) { // note ("" == false) = true
			$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
			$allow_anonymous_ratings = $general_settings[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION];
		} else {
			$allow_anonymous_ratings = $allow_anonymous_ratings == "true" ? true : false;
		}
		
		return $allow_anonymous_ratings;
		
	}
	
	/**
	 * Duplicate rating check
	 * 
	 * @param unknown $post_id
	 * @param unknown $rating_form_id
	 * @return boolean
	 */
	public static function duplicate_rating_check( $post_id, $rating_form_id ) {
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$save_rating_restriction_types = $general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION];
		
		foreach ( $save_rating_restriction_types as $save_rating_restriction_type ) {
			
			if ( $save_rating_restriction_type == 'cookie' && isset( $_COOKIE[MRP_Multi_Rating::POST_SAVE_RATING_COOKIE . '-' . $rating_form_id . '-' . $post_id] ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Checks if user role is disallowed
	 * 
	 * @param unknown $user_id
	 * @return boolean
	 */
	public static function disallowed_user_roles_check( $user_id ) {
		
		if ( $user_id == 0 ) {
			return false;
		}
		
		$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );
		$disallowed_user_roles = $advanced_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION];
		
		if ( isset( $disallowed_user_roles) ) {
		
			if ( ! is_array( $disallowed_user_roles ) ) {
				$disallow_user_roles = array( $disallowed_user_roles );
			}
		
			$user = new WP_User( $user_id );
			global $wp_roles;

			if ( ! empty( $user->roles ) && is_array( $user->roles ) ) {
		
				foreach ( $user->roles as $user_role ) {

					if ( isset( $wp_roles->role_names[$user_role] ) && in_array( $wp_roles->role_names[$user_role], $disallowed_user_roles ) ) {
						return true;
					}
				}
			}
		}
		
		return false;
	}

}
?>
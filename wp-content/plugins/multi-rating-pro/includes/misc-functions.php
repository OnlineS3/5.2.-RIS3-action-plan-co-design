<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add plugin footer to admin dashboard
 *
 * @param       string $footer_text The existing footer text
 * @return      string
 */
function mrp_plugin_footer( $footer_text ) {

	$current_screen = get_current_screen();

	if ( $current_screen->parent_base == MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		$plugin_footer = sprintf( __( '<i>Thank you for using <a href="%1$s" target="_blank">Multi Rating Pro</a>!</i>', 'multi-rating-pro' ), 'https://multiratingpro.com' );

		return $plugin_footer . '<br />' . $footer_text;

	} else {
		return $footer_text;
	}
}
add_filter( 'admin_footer_text', 'mrp_plugin_footer' );


/**
 * Add to the WordPress version
 *
 * @param $default
 */
function mrp_footer_version( $default ) {

	$current_screen = get_current_screen();

	if ( $current_screen->parent_base == MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG ) {
		return 'Multi Rating Pro v' . MRP_PLUGIN_VERSION . '<br />' . $default;
	}

	return $default;
}
add_filter ('update_footer', 'mrp_footer_version', 999);

/**
 * Strip newlines from template HTML
 *
 * @param unknown $html
 * @return mixed
 */
function mrp_template_html_strip_newlines( $html ) {

	$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );

	if ( $advanced_settings[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] == true ) {
		$html = str_replace( array( "\r", "\n"), '', $html );
	}

	return $html;
}
add_filter( 'mrp_template_html', 'mrp_template_html_strip_newlines', 10, 1 );


/**
 * Bayesian average ratings query for single rating result
 *
 * @param unknown $query
 * @param unknown $params
 * @return string select
 */
function mrp_bayesian_rating_result_query( $query, $params ) {

	$post_id = intval( $params['post_id'] );
	$rating_form_id = intval( $params['rating_form_id'] );
	$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $params );

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$decimal_places = intval( $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION] );

	global $wpdb;

	$adjusted_star_result_column = 'ROUND( ( ( b.avg_star_result * b.avg_count + count_entries * star_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places . ') AS adjusted_star_result';
	$adjusted_score_result_column = 'ROUND( ( ( b.avg_score_result * b.avg_count + count_entries * score_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places .') AS adjusted_score_result';
	$adjusted_percentage_result_column = 'ROUND( ( ( b.avg_percentage_result * b.avg_count + count_entries * percentage_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places . ') AS adjusted_percentage_result';

	$select_query = 'SELECT star_result, score_result, percentage_result, total_max_option_value, count_entries, '
			. ' post_id, rating_form_id, ' . $adjusted_star_result_column . ', '
			. $adjusted_score_result_column . ', ' . $adjusted_percentage_result_column;

	$averages_query = 'SELECT AVG(star_result) AS avg_star_result, AVG(score_result) AS avg_score_result, '
			. 'AVG(percentage_result) AS avg_percentage_result, AVG(count_entries) as avg_count FROM ' . $wpdb->prefix
			. MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' WHERE rating_form_id = %d AND rating_entry_id IS NULL '
					. 'AND rating_item_id IS NULL AND filters_hash = %s';
	$averages_query = $wpdb->prepare( $averages_query, $rating_form_id, $filters_hash );

	$from_query = ' FROM ( ' . $averages_query . ' ) b, ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME;

	$where_query = ' WHERE post_id = %d AND rating_entry_id IS NULL AND rating_item_id IS NULL'
			. ' AND rating_form_id = %d AND filters_hash = %s';
	$where_query = $wpdb->prepare( $where_query, $post_id, $rating_form_id, $filters_hash );

	return $select_query . $from_query . $where_query;
}


/**
 * Bayesian ratings query select for rating resulsts list
 *
 * @param unknown $query_select
 * @param unknown $params
 * @return unknown
 */
function mrp_bayesian_rating_results_query_select( $query_select, $params ) {

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];

	$adjusted_star_result_column = 'ROUND( ( ( b.avg_star_result * b.avg_count + count_entries * star_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places . ') AS adjusted_star_result';
	$adjusted_score_result_column = 'ROUND( ( ( b.avg_score_result * b.avg_count + count_entries * score_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places . ') AS adjusted_score_result';
	$adjusted_percentage_result_column = 'ROUND( ( ( b.avg_percentage_result * b.avg_count + count_entries * percentage_result ) / ( b.avg_count + count_entries ) ), ' . $decimal_places . ') AS adjusted_percentage_result';

	$query_select = 'SELECT star_result, score_result, percentage_result, total_max_option_value, count_entries, '
			. ' post_id, rating_form_id, ' . $adjusted_star_result_column . ', '
			. $adjusted_score_result_column . ', ' . $adjusted_percentage_result_column;

	return $query_select;
}

/**
 * Bayesian ratings query from for rating results list
 *
 * @param unknown $query_from
 * @param unknown $params
 * @return string
 */
function mrp_bayesian_rating_results_query_from( $query_from, $params ) {

	$rating_form_id = intval( $params['rating_form_id'] );
	$filters_hash = MRP_Multi_Rating_API::get_filters_hash( $params );

	global $wpdb;

	$from_averages = '( SELECT AVG(star_result) AS avg_star_result, AVG(score_result) AS avg_score_result, '
			. 'AVG(percentage_result) AS avg_percentage_result, AVG(count_entries) AS avg_count FROM ' . $wpdb->prefix
			. MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' WHERE rating_form_id = %d AND rating_entry_id IS NULL AND '
			. 'rating_item_id IS NULL AND filters_hash = %s ) b';
	$from_averages = $wpdb->prepare( $from_averages, $rating_form_id, $filters_hash );

	return ' FROM ' . $from_averages . ', . ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME;
}

/**
 * Creates HTML checkbox list for public taxonomies
 *
 * @param array $params
 *
 * @return HTML checkboxes
 */
function mrp_taxonomies_checkboxes( $params = array() ) {

	$defaults = array(
			'selected' => array(),
			'echo' => true,
			'name' => 'taxonomies',
			'id' => '',
			'class' => '',
			'show_option_all' => true,
			'show_option_no_change' => '',
			'option_none_value' => '',
			'post_types' => array()
	);

	$r = wp_parse_args( $params, $defaults );
	extract( $r, EXTR_SKIP );

	$taxonomies = array();
	$args = array( 'public' => true, 'show_ui' => true );
	if ( isset( $post_types ) && is_array( $post_types) && count( $post_types ) > 0 ) {
		$taxonomies = wp_filter_object_list( get_object_taxonomies( $post_types, 'objects' ), $args );
	} else {
		$taxonomies = get_taxonomies( $args, 'objects' );
	}

	$output = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $id ) ) {
		$id = $name;
	}

	$all_checked = ( $show_option_all ) ? mrp_checked( $selected, '' ) : '';
	if ( $show_option_all ) {
		$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="" ' . $all_checked . '/>';
		$output .= '<label for="' . esc_attr( $name ) . '">' . __( 'All', 'multi-rating-pro' ) . '</label><br />';
	}

	if ( ! empty( $taxonomies ) ) {
		$disabled = strlen( $all_checked ) > 0 ? 'disabled="disabled"' : '';

		foreach( $taxonomies as $taxonomy ) {
			$checked = strlen( $all_checked ) || mrp_checked( $selected, $taxonomy->name ) ? 'checked="checked"' : '';
			$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="' . $taxonomy->name . '"' . $checked . ' ' . $disabled . '/>';
			$output .= '<label for="' . esc_attr( $name ) . '-' . $taxonomy->name . '">' . $taxonomy->labels->name . '</label><br />';
		}
	}

	$output = apply_filters( 'mrp_taxonomies_checkboxes', $output );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}


/**
 * Creates HTML select dropdown for public taxonomies
 *
 * @param array $params
 *
 * @return HTML dropdown
 */
function mrp_taxonomies_select( $params = array() ) {

	$defaults = array(
			'selected' => array(),
			'echo' => true,
			'name' => 'taxonomy',
			'id' => '',
			'class' => '',
			'show_option_all' => true,
			'show_option_no_change' => '',
			'option_none_value' => '',
			'post_types' => array()
	);

	$r = wp_parse_args( $params, $defaults );
	extract( $r, EXTR_SKIP );

	$taxonomies = array();
	$args = array( 'public' => true, 'show_ui' => true );
	if ( isset( $post_types ) && is_array( $post_types) && count( $post_types ) > 0 ) {
		$taxonomies = wp_filter_object_list( get_object_taxonomies( $post_types, 'objects' ), $args );
	} else {
		$taxonomies = get_taxonomies( $args, 'objects' );
	}

	$output = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $id ) ) {
		$id = $name;
	}

	$output = '<select class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '">';
	if ( $show_option_all ) {
		$output .= '<option value=""' . selected( '', $selected, false) . '>' . __( 'All', 'multi-rating-pro' ) . '</option>';
	}
	$taxonomies = array();
	$args = array( 'public' => true, 'show_ui' => true );
	if ( isset( $post_types ) && is_array( $post_types) && count( $post_types ) > 0 ) {
		$taxonomies = wp_filter_object_list( get_object_taxonomies( $post_types, 'objects' ), $args );
	} else {
		$taxonomies = get_taxonomies( $args, 'objects' );
	}

	foreach ( $taxonomies  as $current_taxonomy ) {
		$output .= '<option value="' . $current_taxonomy->name . '" ' . selected( $current_taxonomy->name, $selected, false ) . '>' . $current_taxonomy->labels->name . '</option>';
	}
	$output .= '</select>';

	$output = apply_filters( 'mrp_taxonomies_select', $output );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}


/**
 * Creates HTML checkbox list for public post types
 *
 * @param array $params
 *
 * @return HTML dropdown
 */
function mrp_post_types_checkboxes( $params = array() ) {

	$defaults = array(
			'selected' => array(),
			'echo' => true,
			'name' => 'post-types',
			'id' => 'post-types',
			'class' => '',
			'show_option_all' => true,
	);

	$r = wp_parse_args( $params, $defaults );
	extract( $r, EXTR_SKIP );

	$post_types = get_post_types( array(
			'public' => true,
			'show_ui' => true
	), 'objects');

	$output = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $id ) ) {
		$id = $name;
	}

	$all_checked = ( $show_option_all ) ? mrp_checked( $selected, '' ) : '';
	if ( $show_option_all ) {
		$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="" ' . $all_checked . '/>';
		$output .= '<label for="' . esc_attr( $name ) . '-all">' . __( 'All', 'multi-rating-pro' ) . '</label><br />';
	}

	if ( ! empty( $post_types ) ) {
		$disabled = strlen( $all_checked ) > 0 ? 'disabled="disabled"' : '';

		foreach( $post_types as $post_type ) {
			$checked = strlen( $all_checked ) || mrp_checked( $selected, $post_type->name ) ? 'checked="checked"' : '';
			$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" value="' . esc_attr( $post_type->name ) . '" id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" ' . $checked . ' ' . $disabled . '/>';
			$output .= '<label for="' . esc_attr( $name ) . '-' . $post_type->name . '">' .  $post_type->labels->name . '</label><br />';
		}

	}

	$output = apply_filters( 'mrp_post_types_checkboxes', $output );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}


/**
 * Creates HTML checkbox list for taxonomy terms
 *
 * @params array $params
 *
 * @return HTML dropdown
 */
function mrp_terms_checkboxes( $params = array() ) {

	$defaults = array(
			'selected' => array(),
			'echo' => true,
			'name' => 'terms',
			'id' => '',
			'class' => '',
			'show_option_all' => true,
			'taxonomy' => 'category'
	);

	$r = wp_parse_args( $params, $defaults );
	extract( $r, EXTR_SKIP );

	$terms = get_terms( $taxonomy, array( 'hide_empty' => true ) );

	$output = '';

	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $id ) ) {
		$id = $name;
	}

	$index = 0;
	$all_checked = ( $show_option_all ) ? mrp_checked( $selected, '' ) : '';
	if ( $show_option_all ) {
		$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" id="' . esc_attr( $id ) . '" value="" ' . $all_checked . '/>';
		$output .= '<label for="' . esc_attr( $name ) . '-all">' . __( 'All', 'multi-rating-pro' ) . '</label>';
		$index++;
	}

	if ( ! empty( $terms ) ) {
		$disabled = strlen( $all_checked ) > 0 ? 'disabled="disabled"' : '';

		foreach( $terms as $term ) {
			if ( $index > 5 ) {
				$output .= '<a href="#" class="more-terms">' . __( 'Show more', 'multi-rating-pro' ) . '</a>';
				$output .= '<div style="display: none;">';
			} else if ( $index > 0 ) {
				$output .= '<br />';
			}

			$checked = strlen( $all_checked ) || mrp_checked( $selected, $term->name ) ? 'checked="checked"' : '';
			$output .= '<input type="checkbox" name="' . esc_attr( $name ) . '" value="' . $term->name . '"' . $checked . ' ' . $disabled . '/>';
			$output .= '<label for="' . esc_attr( $name ) . '-' . $term->name . '">' . $term->name . '</label>';

			$index++;
		}

		if ( $index > 5 ) {
			$output .= '</div>';
		}
	}

	$output = apply_filters( 'mrp_terms_checkboxes', $output );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}


/**
 * Outputs checkbox checked HTML attribute if needed
 *
 * @param unknown $selected
 * @param unknown $value
 */
function mrp_checked( $selected = array(), $value = '' ) {

	if ( ! is_array( $selected ) && ( $selected == $value ) ) {
		return 'checked="checked"';
	} else {
		foreach ( $selected as $current ) {
			if ( $current == $value ) {
				return 'checked="checked"';
				break;
			}
		}
	}
}


/**
 * Rating form auto placement position select
 *
 * @param unknown $selected
 */
function mrp_rating_form_position_select( $selected = '', $class = '' ) {
	?>
	<select name="rating-form-position" class="<?php echo $class; ?>">
		<option value="<?php echo MRP_Multi_Rating::DO_NOT_SHOW; ?>" <?php selected( MRP_Multi_Rating::DO_NOT_SHOW, $selected, true); ?>><?php _e( 'Do not show', 'multi-rating-pro' ); ?></option>
		<option value="" <?php selected( '', $selected, true); ?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
		<option value="before_content" <?php selected( 'before_content', $selected, true); ?>><?php _e( 'Before content', 'multi-rating-pro' ); ?></option>
		<option value="after_content" <?php selected( 'after_content', $selected, true); ?>><?php _e( 'After content', 'multi-rating-pro' ); ?></option>
		<option value="comment_form" <?php selected( 'comment_form', $selected, true); ?>><?php _e( 'Comment form', 'multi-rating-pro' ); ?></option>
	</select>
	<?php
}

/**
 * Rating results auto placement position select
 *
 * @param unknown $selected
 */
function mrp_rating_results_position_select( $selected = '', $class = '' ) {
	?>
	<select name="rating-results-position" class="<?php echo $class; ?>">
		<option value="<?php echo MRP_Multi_Rating::DO_NOT_SHOW; ?>" <?php selected( MRP_Multi_Rating::DO_NOT_SHOW, $selected, true); ?>><?php _e( 'Do not show', 'multi-rating-pro' ); ?></option>
		<option value="" <?php selected( '', $selected, true); ?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
		<option value="before_title" <?php selected( 'before_title', $selected, true); ?>><?php _e( 'Before title', 'multi-rating-pro' ); ?></option>
		<option value="after_title" <?php selected( 'after_title', $selected, true); ?>><?php _e( 'After title', 'multi-rating-pro' ); ?></option>
		<option value="before_content" <?php selected( 'before_content', $selected, true); ?>><?php _e( 'Before content', 'multi-rating-pro' ); ?></option>
		<option value="after_content" <?php selected( 'after_content', $selected, true); ?>><?php _e( 'After content', 'multi-rating-pro' ); ?></option>
	</select>
	<?php
}

/**
 * Displays posts select drop down
 *
 * @param string $post_id
 * @param string $show_all
 * @param string $class
 */
function mrp_posts_select( $post_id = '', $show_all = false, $class = '' ) {
	?>
	<select name="post-id" id="post-id" class="<?php echo $class; ?>">

		<?php if ( $show_all ) { ?>
			<option value="" <?php if ( $post_id == '' || $post_id == null ) { echo 'selected="selected"'; } ?>><?php _e( 'All posts / pages', 'multi-rating-pro' ); ?></option>
		<?php }

		global $wpdb;
		$query = 'SELECT DISTINCT post_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME;

		$rows = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $rows as $row ) {
			$post = get_post( $row['post_id'] );

			$selected = '';
			if ( intval( $row['post_id'] ) == intval( $post_id ) ) {
				$selected = ' selected="selected"';
			}

			?>
			<option value="<?php echo $post->ID; ?>" <?php echo $selected; ?>>
				<?php echo get_the_title( $row['post_id'] ); ?>
			</option>
			<?php } ?>
		</select>
	<?php
}


/**
 * Rating form select
 *
 * @param string $rating_form_id
 * @param string $show_use_default
 * @param string $show_all
 */
function mrp_rating_form_select( $rating_form_id = '', $show_use_default = true, $show_all = false, $name = 'rating-form-id', $id = 'rating-form-id', $class = '' ) {
	?>
	<select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo $class; ?>">

		<?php if ( $show_use_default ) { ?>
			<option value="" <?php if ( $rating_form_id == '' || $rating_form_id == null ) { echo 'selected="selected"'; } ?>><?php _e( 'Use default settings', 'multi-rating-pro' ); ?></option>
		<?php } else if ( $show_all ) { ?>
			<option value="" <?php if ( $rating_form_id == '' || $rating_form_id == null  ) { echo 'selected="selected"'; } ?>><?php _e( 'All rating forms', 'multi-rating-pro' ); ?></option>
		<?php }

		global $wpdb;
		$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix .  MRP_Multi_Rating::RATING_FORM_TBL_NAME;

		$rows = $wpdb->get_results( $query, ARRAY_A );

		foreach ( $rows as $row ) {

			$selected = '';
			if ( intval( $row['rating_form_id'] ) == $rating_form_id ) {
				$selected = ' selected="selected"';
			}

			?>
			<option value="<?php echo $row['rating_form_id']; ?>" <?php echo $selected; ?>>
				<?php echo esc_html( stripslashes( apply_filters( 'mrp_translate_single_string', $row['name'], 'rating-form-' . $rating_form_id . '-name' ) ) ); ?>
			</option>
			<?php
		}
		?>
	</select>
	<?php
}


/**
 * Gets sticky posts
 *
 * @param unknown $posts_fields
 * @return string
 */
function mrp_posts_fields( $posts_fields ) {

	if ( is_main_query() ) {

		$sticky_posts = get_option( 'sticky_posts' );
		if ( is_array( $sticky_posts ) && ! empty( $sticky_posts ) ) {

			$sticky_posts =  implode( ', ', $sticky_posts );

			if ( isset( $posts_fields ) ) {

				global $wpdb;
				$field = "SUM(CASE WHEN " . $wpdb->posts . ".ID IN ( $sticky_posts ) THEN 1 ELSE 0 END) AS is_sticky";

				if ( ! is_array( $posts_fields ) ) {
					$posts_fields .= ', ' . $field;
				} else {
					array_push( $posts_fields[0], $field );
				}
			}
		}
	}

	return $posts_fields;
}


/**
 * Groups posts by ID
 *
 * @param unknown $group_by
 * @return string
 */
function mrp_posts_groupby( $group_by ) {

	if ( is_main_query() ) {
		global $wpdb;
		$group_by = $wpdb->posts . '.ID';
	}

	return $group_by;

}


/**
 * Adds WP loop sort by highest rating then count entries, keeps stick posts at top
 *
 * @param unknown $order_by
 * @param unknown $query
 * @return string
 */
function mrp_posts_orderby( $order_by ) {

	$order_by = '';
	if ( is_main_query() ) {

		$sticky_posts = get_option( 'sticky_posts' );
		if ( is_array( $sticky_posts ) && ! empty( $sticky_posts ) ) {
			$order_by .= 'is_sticky DESC, ';
		}

		$order_by .= 'adjusted_star_result DESC, count_entries DESC'; // highest rated
	}

	return $order_by;
}


/**
 * WP loop join tables for sorting by ratings
 *
 * @param unknown $join
 * @param unknown $query
 * @return string
 */
function mrp_posts_join( $join ) {

	if ( is_main_query() ) {
		global $wp_query, $wpdb;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$filters_hash = MRP_Multi_Rating_API::get_filters_hash( apply_filters( 'mrp_posts_join_filters', array() ) ); // if you want to add any Multi Rating pro specific filters e.g. rating_item_ids
		$join .= ' LEFT JOIN ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' rr ON ' . $wpdb->posts . '.ID'
				. ' = rr.post_id AND rr.filters_hash = "' . $filters_hash . '" AND rr.rating_item_id IS NULL AND rr.rating_entry_id IS NULL';
	}

	return $join;
}


/**
 * Gets rating form position. Auto placement settings are used by default, but can be override
 * by either post meta box or filters
 *
 * @param unknown $post_id
 * @return unknown
 */
function mrp_get_rating_form_position( $post_id ) {

	$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
	$rating_form_position = $auto_placement_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION];
	$rating_form_position_post_meta = get_post_meta( $post_id, MRP_Multi_Rating::RATING_FORM_POSITION_POST_META, true );
	$filter = MRP_Utils::get_filter( $post_id );

	/*
	 * Scenarios
	 * 1. No filters
	 * 2. Filter (override post meta only)
	 * 3. Filter (only override post meta if post meta == '')
	 */

	if ( $rating_form_position_post_meta !== '' ) {
		$rating_form_position = $rating_form_position_post_meta;
	}

	if ( $filter && $filter['rating_form_position'] !== '' && ( $filter['override_post_meta'] || $rating_form_position_post_meta == '' ) ) {
		$rating_form_position = $filter['rating_form_position'];
	} else if ( $filter && $filter['rating_form_position'] == '' && $filter['override_post_meta'] ) {
		$rating_form_position = $auto_placement_settings[MRP_Multi_Rating::RATING_FORM_POSITION_OPTION];
	}

	return $rating_form_position;
}

/**
 * Gets rating result position
 *
 * @param unknown $post_id
 * @return unknown
 */
function mrp_get_rating_results_position( $post_id ) {

	$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
	$rating_results_position = $auto_placement_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION];
	$rating_results_position_post_meta = get_post_meta( $post_id, MRP_Multi_Rating::RATING_RESULTS_POSITION_POST_META, true );
	$filter = MRP_Utils::get_filter( $post_id );

	/*
	 * Scenarios
	 * 1. No filters
	 * 2. Filter (override post meta only)
	 * 3. Filter (only override post meta if post meta == '')
	 */

	if ( $rating_results_position_post_meta !== '' ) {
		$rating_results_position = $rating_results_position_post_meta;
	}

	if ( $filter && $filter['rating_results_position'] !== '' && ( $filter['override_post_meta'] || $rating_results_position_post_meta == '' ) ) {
		$rating_results_position = $filter['rating_results_position'];
	} else if ( $filter && $filter['rating_results_position'] == '' && $filter['override_post_meta'] ) {
		$rating_results_position = $auto_placement_settings[MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION];
	}

	return $rating_results_position;
}


/**
 * Calculates a rating entry result
 *
 * @param unknown $rating_entry
 * @param array params
 * @return rating entry result
 */
function mrp_calculate_rating_entry_result( $rating_entry, $params = array() ) {

	if ( is_numeric( $rating_entry ) ) {
		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry ) );
	}

	if ( $rating_entry == null ) {
		return;
	}

	$post_id = $rating_entry['post_id'];
	$rating_form_id = $rating_entry['rating_form_id'];
	$rating_entry_id = $rating_entry['rating_entry_id'];
	$rating_item_ids = isset( $params['rating_item_ids'] ) ? $params['rating_item_ids'] : null;

	$rating_form = MRP_Multi_Rating_API::get_rating_form( $rating_form_id );
	$rating_items = $rating_form['rating_items'];

	// init values for calculations
	$total_max_option_value = 0;
	$total_adjusted_max_option_value = 0;

	$star_result = 0;
	$adjusted_star_result = 0;
	$score_result = 0;
	$adjusted_score_result = 0;
	$percentage_result = 0;
	$adjusted_percentage_result = 0;

	$rating_items_na = array(); // for rating items which are not applibale to the rating result

	// iterate each rating item value to calculate the average rating
	foreach ( $rating_entry['rating_item_values'] as $rating_item_id => $value ) {

		if ( isset( $rating_item_ids ) && count( $rating_item_ids ) > 0 ) {
			if ( ! in_array( $rating_item_id, explode( ',', $rating_item_ids ) ) ) {
				continue; // skip
			}
		}

		// check rating item is available, if it's been deleted it wont be included in rating result
		if ( isset( $rating_items[$rating_item_id] ) && isset( $rating_items[$rating_item_id]['max_option_value'] ) ) {

			// remember N/A selected rating items for later
			if ( $value == -1 ) {
				array_push( $rating_items_na, $rating_items[$rating_item_id] );
				continue;
			}

			// add value and max option values
			$max_option_value = $rating_items[$rating_item_id]['max_option_value'];
			if ( $value > $max_option_value ) {
				$value = $max_option_value;
			}

			// make adjustments to the rating for weights
			$weight = $rating_items[$rating_item_id]['weight'];

			// score result
			$score_result += intval( $value ) ;
			$adjusted_score_result += ( $value * $weight );

			$total_max_option_value += $max_option_value;
			$total_adjusted_max_option_value += ( $max_option_value * $weight );

		} else {
			continue; // skip
		}
	}

	if ( $total_max_option_value == 0 // this can happen if filtering by rating items where the rating entry does not have any rating items
			|| count( $rating_entry['rating_item_values'] )  == 0 ) {
		return null;
	}

	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];
	$star_rating_out_of = $general_settings[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION];

	// adjust rating entry result if there any N/A selected rating item values
	if ( count( $rating_items_na ) > 0 ) {

		$multiplier = floatval( $score_result ) / floatval( $total_max_option_value );
		$adjusted_multiplier = floatval( $adjusted_score_result ) / floatval( $total_adjusted_max_option_value );

		foreach ( $rating_items_na as $rating_item_na ) {
			$max_option_value = $rating_item_na['max_option_value'];
			$total_max_option_value += $max_option_value;
			$total_adjusted_max_option_value += $max_option_value;
			$score_result += ( $max_option_value * $multiplier );
			$adjusted_score_result += ( $max_option_value * $multiplier );
		}
	}

	$star_result = (float) round( ( floatval( $score_result ) / floatval( $total_max_option_value ) ) * $star_rating_out_of, $decimal_places );
	$adjusted_star_result = (float) round( ( floatval( $adjusted_score_result ) / floatval( $total_adjusted_max_option_value ) ) * $star_rating_out_of, $decimal_places );
	$percentage_result = (float) round( ( doubleval( $score_result ) / doubleval( $total_max_option_value ) ) * 100, $decimal_places );
	$adjusted_percentage_result = (float) round( ( floatval( $adjusted_score_result ) / floatval( $total_adjusted_max_option_value ) ) * 100, $decimal_places );
	$adjusted_score_result = (float) round( ( floatval( $adjusted_score_result ) / floatval( $total_adjusted_max_option_value ) ) * $total_max_option_value, $decimal_places );

	$rating_result = array(
			'adjusted_star_result' => $adjusted_star_result,
			'star_result' => $star_result,
			'total_max_option_value' => $total_max_option_value,
			'adjusted_score_result' => $adjusted_score_result,
			'score_result' => $score_result,
			'percentage_result' => $percentage_result,
			'adjusted_percentage_result' => $adjusted_percentage_result,
			'rating_entry_id' => $rating_entry_id,
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id
	);

	return $rating_result;

}

/**
 * Calculates the rating result of a rating form for a post with filters for user_id.
 *
 * @param array $params post_id, rating_items, rating_form_id and user_id
 * @return rating result
 */
function mrp_calculate_rating_result( $params = array() ) {

	$params = wp_parse_args( $params, array(
			'rating_item_ids' => null,
			'rating_form_id' => null,
			'post_id' => null,
			'user_id' => 0,
			'user_roles' => null,
			'approved_comments_only' => true,
			'entry_status' => 'approved',
			'rating_entry_ids' => null,
			'comments_only' => false,
			'to_date' => null,
			'from_date' => null,
			'taxonomy' => null,
			'term_id' => 0,
			'published_posts_only' => false,
			'post_ids' => null
	) );

	$params = apply_filters( 'mrp_calculate_rating_result_params', $params );

	extract( $params );

	if ( $post_id == null ) {
		return;
	}

	$rating_entry_results_list = MRP_Multi_Rating_API::get_rating_entry_result_list( $params );

	$count_entries = $rating_entry_results_list['count_entries'];
	if ( $entry_status == 'approved' ) {
		$count_entries = $rating_entry_results_list['count_approved'];
	} else if ( $entry_status == 'pending' ) {
		$count_entries = $rating_entry_results_list['count_pending'];
	}

	$rating_items = (array) MRP_Multi_Rating_API::get_rating_items( array(
			'rating_form_id' => $rating_form_id,
			'rating_item_ids' => $rating_item_ids,
			'post_id' => $post_id
	) );

	// get max option value
	$total_max_option_value = 0;
	foreach ( $rating_items as $rating_item ) {
		$total_max_option_value += $rating_item['max_option_value'];
	}

	$score_result_total = 0;
	$adjusted_score_result_total = 0;
	$star_result_total = 0;
	$adjusted_star_result_total = 0;
	$percentage_result_total = 0;
	$adjusted_percentage_result_total = 0;

	foreach ( $rating_entry_results_list['rating_results'] as $rating_entry_result ) {

		// this mean total max option value may also be different, so adjust the score result if it is as
		// this is out of the total max option value
		$adjustment = 1.0;
		if ( $rating_entry_result['total_max_option_value'] != $total_max_option_value ) {
			$adjustment = $total_max_option_value / $rating_entry_result['total_max_option_value'];
		}

		$score_result_total += ( $rating_entry_result['score_result'] * $adjustment );
		$adjusted_score_result_total += ( $rating_entry_result['adjusted_score_result'] * $adjustment );

		$star_result_total += $rating_entry_result['star_result'];
		$adjusted_star_result_total += $rating_entry_result['adjusted_star_result'];

		$percentage_result_total += $rating_entry_result['percentage_result'];
		$adjusted_percentage_result_total += $rating_entry_result['adjusted_percentage_result'];
	}

	$adjusted_star_result = 0;
	$star_result = 0;
	$adjusted_score_result = 0;
	$score_result = 0;
	$percentage_result = 0;
	$adjusted_percentage_result = 0;

	if ( $count_entries > 0 ) {

		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];

		$score_result = (float) round( floatval( $score_result_total ) / $count_entries, $decimal_places );
		$adjusted_score_result = (float) round( floatval( $adjusted_score_result_total ) / $count_entries, $decimal_places );
		$star_result = (float) round( floatval( $star_result_total ) / $count_entries, $decimal_places );
		$adjusted_star_result = (float) round( floatval( $adjusted_star_result_total ) / $count_entries, $decimal_places );
		$percentage_result = (float) round( floatval( $percentage_result_total ) / $count_entries, $decimal_places );
		$adjusted_percentage_result = (float) round( floatval( $adjusted_percentage_result_total ) / $count_entries, $decimal_places );
	}

	return array(
			'adjusted_star_result' => $adjusted_star_result,
			'star_result' => $star_result,
			'total_max_option_value' => $total_max_option_value,
			'adjusted_score_result' => $adjusted_score_result,
			'score_result' => $score_result,
			'percentage_result' => $percentage_result,
			'adjusted_percentage_result' => $adjusted_percentage_result,
			'count_entries' => $count_entries,
			'post_id' => $post_id,
			'rating_form_id' => $rating_form_id
	);
}


/**
 * Checks whether the Multi Rating post meta box needs to be hidden by default
 *
 * @param unknown $hidden
 * @param unknown $screen
 * @return unknown
 */
function mrp_default_hidden_meta_boxes( $hidden = array(), $screen ) {

	$post_type = $screen->post_type;

	$auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

	$post_types = $auto_placement_settings[MRP_Multi_Rating::POST_TYPES_OPTION];
	if ( ! is_array( $post_types ) && is_string( $post_types ) ) {
		$post_types = array( $post_types );
	}

	if ( $post_types != null && in_array( $post_type, $post_types ) ) {

		$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );

		// check option if we're hiding by default
		if ( $advanced_settings[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] ) {
			if ( ! isset( $hidden['mrp_meta_box'] ) ) {
				array_push( $hidden, 'mrp_meta_box' );
			}
		}
	}

	return $hidden;
}


/**
 * Delete all associated ratings by post id
 *
 * @param $post_id
 */
function mrp_deleted_post( $post_id ) {

	$rating_entries = MRP_Multi_Rating_API::get_rating_entries( array( 'post_id' => $post_id ) );

	foreach ( $rating_entries as $rating_entry ) {
		MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );
	}
}


/**
 * Delete all associated ratings by user id
 *
 * @param $user_id
 * @param $reassign user id
 */
function mrp_delete_user( $user_id, $reassign ) {

	global $wpdb;

	if ( $reassign == null ) { // do not reassign ratings to anyone ...
		$rating_entries = MRP_Multi_Rating_API::get_rating_entries( array( 'user_id' => $user_id ) );

		foreach ( $rating_entries as $rating_entry ) {
			MRP_Multi_Rating_API::delete_rating_entry( $rating_entry );
		}

	} else {
		MRP_Multi_Rating_API::reassign_user_ratings( $user_id, $reassign );
	}
}

/**
 * Init feeds
 */
function mrp_init_feeds(){

	if ( apply_filters( 'mrp_feed_enable', true ) ) {
		// you can change the feed name if you want via the filer
		add_feed( apply_filters( 'mrp_feed_name_rating_entries', 'mrp_rating_entries' ), 'mrp_rating_entries_feed' );
	}

}
add_action( 'init', 'mrp_init_feeds' );


/**
 * Generate feed for rating entries
 */
function mrp_rating_entries_feed( $is_comment ) {

	// options
	$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
	$general_settings = (array) MRP_Multi_Rating::instance()->settings->general_settings;

	$limit = apply_filters( 'mrp_feed_limit', get_option( 'posts_per_rss' ) );
	$include_featured_img = apply_filters( 'mrp_include_featured_img', true );
	$add_author_link = apply_filters( 'mrp_feed_add_author_link', true );
	$sort_by = apply_filters( 'mrp_feed_sort_by', 'most_recent' );
	$image_size = apply_filters( 'mrp_feed_image_size', 'thumbnail' );

	$rating_entry_result_list = MRP_Multi_Rating_API::get_rating_entry_result_list( array(
		'limit' => $limit,
		'rating_form_id' => '',
		'sort_by' => $sort_by,
		'post_id' => '',
		'entry_status' => 'approved'
	) );

	foreach ( $rating_entry_result_list['rating_results'] as $index => $rating_entry_result ) {
		$rating_entry = MRP_Multi_Rating_API::get_rating_entry( array( 'rating_entry_id' => $rating_entry_result['rating_entry_id'] ) );
		$rating_entry_result_list['rating_results'][$index] = array_merge( array( 'rating_result' => $rating_entry_result, 'rank' => $index ), $rating_entry );
	}

	mrp_get_template_part( 'rating-entries', 'feed', true, array(
			'rating_entry_result_list' => $rating_entry_result_list['rating_results'],
			'include_featured_img' => $include_featured_img,
	 		'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
	 		'add_author_link' => $add_author_link,
			'image_size' => $image_size
	) );
}



/**
 * Helper to sort highest rated rating items
 *
 * @param unknown_type $a
 * @param unknown_type $b
 */
function mrp_sort_highest_rated_rating_items( $a, $b ) {

	$a = isset( $a['rating_result'] ) ? $a['rating_result'] : $a;
	$b = isset( $b['rating_result'] ) ? $b['rating_result'] : $b;

	if ( $a['adjusted_score_result'] == $b['adjusted_score_result'] ) {

		if ( ! isset( $a['count'] ) ) {
			return 0;
		}

		if ( $a['count'] == $b['count'] ) {
			return 0;
		} else {
			return (  $a['count'] > $b['count'] ) ? -1 : 1;
		}
	}

	return ( $a['adjusted_score_result'] > $b['adjusted_score_result'] ) ? -1 : 1;
}


/**
 * Purge cache for W3TC, WP Super Cache, WP Fastest Cache and WP Engine cache implementations
 *
 * Based on Clear Cache for Me plugin https://wordpress.org/plugins/clear-cache-for-widgets
 */
function mrp_purge_cache() {

	// if W3 Total Cache is being used, clear the cache
	if ( function_exists( 'w3tc_pgcache_flush' ) ) {
		//w3tc_pgcache_flush();
	}

	// if WP Super Cache is being used, clear the cache
	else if ( function_exists( 'wp_cache_clean_cache' ) ) {
		global $file_prefix, $supercachedir;
		if ( empty( $supercachedir ) && function_exists( 'get_supercache_dir' ) ) {
			$supercachedir = get_supercache_dir();
		}
		wp_cache_clean_cache( $file_prefix );
	}

	// WP Engine
	else if ( class_exists( 'WpeCommon' ) ) {
		//be extra careful, just in case 3rd party changes things on us
		if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
			WpeCommon::purge_memcached();
		}
		if ( method_exists( 'WpeCommon', 'clear_maxcdn_cache' ) ) {
			WpeCommon::clear_maxcdn_cache();
		}
		if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
			WpeCommon::purge_varnish_cache();
		}
	}

	// WP Fastest Cache
	else if ( method_exists( 'WpFastestCache', 'deleteCache' ) && !empty( $wp_fastest_cache ) ) {
		$wp_fastest_cache->deleteCache();
	}

	// Comet Cache
	else if ( class_exists( 'quick_cache' ) && method_exists( 'quick_cache', 'clear' ) ) {
		quick_cache::clear();
	}

	// Cachify
	else if ( class_exists( 'Cachify' ) && method_exists( 'Cachify', 'flush_total_cache' ) ) {
	    Cachify::flush_total_cache();
	}

	// Clear Varnish caches
	/*
	if ('file' == $this->mode() && Avada()->settings->get('cache_server_ip')) {
		$this->clear_varnish_cache($this->file('url'));
	}*/
}


/*$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );
if ( isset( $advanced_settings[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] ) && $advanced_settings[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] ) {
	add_action( 'mrp_save_rating_result', 'mrp_purge_cache', 10 );
	add_action( 'mrp_delete_rating_result', 'mrp_purge_cache', 10 );
}*/


/**
 * Formats number but keeps trailing zero decimlas
 */
function mrp_number_format( $number ) {
	$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
	$decimal_places = $general_settings[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION];
	$keep_any_trailing_zeros = $general_settings[MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS];

	$separator = '.';
    $number_parts = explode( $separator, $number );

    $response = $number;
    if ( $keep_any_trailing_zeros && $decimal_places > 0) {
	    if( count( $number_parts ) > 1 ) {
	        $response = $number_parts[0] . $separator;
	        $response .= str_pad( $number_parts[1], $decimal_places, '0' );
	    } else {
	    	$number_parts[0] = $number_parts[0] . $separator;
	    	$response = str_pad( $number_parts[0], strlen( $number_parts[0]) + $decimal_places, '0' );
	    }
	}

    return $response;
}

<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Rating Results List Widget
 */
class MRP_Rating_Results_List_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {
		
		$id_base = 'rating_results_list';
		$name = __( 'Rating Results List', 'multi-rating-pro' );
		$widget_opts = array( 
				'classname' => 'rating-results-list-widget', 
				'description' => __( 'Displays a list of overall rating results for posts.', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );
		
		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		extract($args);
		
		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = empty( $instance['limit'] ) ? 10 : intval( $instance['limit'] );
		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		$taxonomy =  empty( $instance['taxonomy'] ) ? '' : $instance['taxonomy'];
		$term_id = 0;
		if ( ! empty( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = empty( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$show_featured_img = empty( $instance['show_featured_img'] ) ? false : $instance['show_featured_img'];
		$image_size = empty( $instance['image_size'] ) ? 'thumbnail' : $instance['image_size'];
		$header = empty( $instance['header'] ) ? 'h3' : $instance['header'];
		$sort_by =  empty( $instance['sort_by'] ) ? 'highest_rated' : $instance['sort_by'];
		$filter_label_text =  $instance['filter_label_text'];
		$show_rank = empty( $instance['show_rank'] ) ? false : $instance['show_rank'];
		$result_type = empty( $instance['result_type'] ) ? $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] : $instance['result_type'];
		
		$before_title = '<' . $header . ' class="widget-title">';
		$after_title = '</' . $header . '>';
		
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		mrp_rating_results_list( apply_filters( 'mrp_rating_results_list_params', array(
				'rating_form_id' => $rating_form_id,
				'limit' => $limit, 'title' => $title,
				'show_filter' => $show_filter,
				'taxonomy' => $taxonomy,
				'term_id' => $term_id,
				'class' => 'mrp-widget mrp-rating-results-list-widget',
				'before_title' => $before_title,
				'after_title' => $after_title,
				'show_featured_img' => $show_featured_img,
				'image_size' => $image_size,
				'sort_by' => $sort_by,
				'show_rank' => $show_rank,
				'filter_label_text' => $filter_label_text,
				'result_type' => $result_type
		) ) );

		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['taxonomy'] = $new_instance['taxonomy'];
		$instance['term_id'] = 0;
		if ( ! empty($new_instance['term_id'] ) && is_numeric( $new_instance['term_id'] ) ) {
			$instance['term_id'] = intval( $new_instance['term_id'] );
		}
		$instance['show_filter'] = false;
		if ( isset( $new_instance['show_filter'] ) && ( $new_instance['show_filter'] == 'true' ) ) {
			$instance['show_filter'] = true;
		}
		$instance['show_featured_img'] = false;
		if ( isset( $new_instance['show_featured_img'] ) && ( $new_instance['show_featured_img'] == 'true' ) ) {
			$instance['show_featured_img'] = true;
		}
		$instance['show_rank'] = false;
		if ( isset( $new_instance['show_rank'] ) && ( $new_instance['show_rank'] == 'true' ) ) {
			$instance['show_rank'] = true;
		}
		$instance['image_size'] = $new_instance['image_size'];
		$instance['header'] = $new_instance['header'];
		$instance['sort_by'] = $new_instance['sort_by'];
		$instance['filter_label_text'] = $new_instance['filter_label_text'];
		$instance['result_type'] = $new_instance['result_type'];
		
		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		

		$instance = wp_parse_args( (array) $instance, array(
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION],
				'limit' => 10,
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'show_featured_img' => true,
				'image_size' => 'thumbnail',
				'header' => 'h3',
				'sort_by' => 'highest_rated',
				'show_filter' => true,
				'taxonomy' => '',
				'term_id' => 0,
				'show_rank' => true,
				'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
				'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION]
		) );

		$title = strip_tags( $instance['title'] );
		$limit = intval( $instance['limit'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$taxonomy =  isset($instance['taxonomy']) ? trim($instance['taxonomy']) : '';
		if ( ! empty( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = empty( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$show_featured_img = empty( $instance['show_featured_img'] ) ? false : $instance['show_featured_img'];
		$image_size = $instance['image_size'];
		$header = $instance['header'];
		$sort_by = $instance['sort_by'];
		$show_rank = empty( $instance['show_rank'] ) ? false : $instance['show_rank'];
		$filter_label_text = $instance['filter_label_text'];
		$result_type = $instance['result_type'];

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit'); ?>"><?php _e( 'Limit', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" min="0" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'result_type' ); ?>"><?php _e( 'Result Type', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'result_type' ); ?>" id="<?php echo $this->get_field_id( 'result_type' ); ?>">
				<?php 
				$result_type_options = array(
						'star_rating' => __( 'Stars', 'multi-rating-pro' ),
						'percentage' => __( 'Percentage', 'multi-rating-pro' ),
						'score' => __( 'Score', 'multi-rating-pro' )
				);
				
				foreach ( $result_type_options as $result_type_option_value => $result_type_option_label ) {
					$selected = '';
					if ( $result_type_option_value == $result_type ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $result_type_option_value . '" ' . $selected . '>' . $result_type_option_label . '</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy', 'multi-rating-pro' ); ?></label>
			<select class="widefat mrp-rating-results-widget-taxonomy" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">				
				<?php
				//$selected = '';
				if ( $taxonomy === '' || $taxonomy == null ) {
					$selected = ' selected="selected"';
				}
				echo '<option value=""' . $selected . '></option>';
				
				$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
				foreach ( $taxonomies  as $current_taxonomy ) {
					$selected = '';
					if ( $current_taxonomy->name === $taxonomy ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_taxonomy->name . '"' . $selected . '>' . $current_taxonomy->labels->name . '</option>';
				} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'term_id' ); ?>"><?php _e( 'Terms', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'term_id' ); ?>" id="<?php echo $this->get_field_id( 'term_id' ); ?>">
			<?php
			$selected = '';
			if ( $taxonomy === '' || $taxonomy == null ) {
				echo '<option value="" selected="selected"></option>';
			} else {
				if ($term_id == 0) {
					$selected = ' selected="selected"';
				}
				echo '<option value="0"' . $selected . '>' . __( 'All', 'multi-rating-pro' ) . '</option>';
				$terms = get_terms( $taxonomy );
				foreach ( $terms  as $current_term ) {
					$selected = '';
					if ( $current_term->term_id == $term_id ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_term->term_id . '" ' . $selected . '>' . $current_term->name . '</option>';
				} 
			} ?>
			</select>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_filter' ); ?>" type="checkbox" value="true" <?php checked( true, $show_filter, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_filter' ); ?>"><?php _e( 'Show Filter', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'filter_label_text' ); ?>"><?php _e( 'Filter Label', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'filter_label_text' ); ?>" name="<?php echo $this->get_field_name( 'filter_label_text' ); ?>" type="text" value="<?php echo esc_attr( $filter_label_text ); ?>" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_featured_img' ); ?>" name="<?php echo $this->get_field_name( 'show_featured_img' ); ?>" type="checkbox" value="true" <?php checked( true, $show_featured_img, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_featured_img' ); ?>"><?php _e( 'Show Featured Image', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_rank' ); ?>" name="<?php echo $this->get_field_name( 'show_rank' ); ?>" type="checkbox" value="true" <?php checked( true, $show_rank, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_rank' ); ?>"><?php _e( 'Show Rank', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e('Image Size', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'image_size' ); ?>" id="<?php echo $this->get_field_id( 'image_size' ); ?>">
				<?php 
				$img_sizes = MRP_Utils::get_image_sizes();
				
				foreach ( $img_sizes as $img_size_name => $img_size_meta ) {
					$selected = '';
					if ( $img_size_name == $image_size ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $img_size_name . '" ' . $selected . '>' . $img_size_name . ' (' .  $img_size_meta['width'] . 'x' . $img_size_meta['height'] . ')</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e( 'Header', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'header' ); ?>" id="<?php echo $this->get_field_id( 'header' ); ?>">
				<?php 
				$header_options = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
				
				foreach ( $header_options as $header_option ) {
					$selected = '';
					if ( $header_option == $header ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $header_option . '" ' . $selected . '>' . strtoupper( $header_option ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_by' ); ?>"><?php _e( 'Sort By', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'sort_by' ); ?>" id="<?php echo $this->get_field_id( 'sort_by' ); ?>">
				<?php 
				$sort_by_options = array( 
						'highest_rated' => __( 'Highest Rated', 'multi-rating-pro' ),
						'lowest_rated' => __( 'Lowest Rated', 'multi-rating-pro'),
						'most_entries' => __( 'Most Entries', 'multi-rating-pro' ),
						'post_title_asc' => __( 'Post Title Ascending', 'multi-rating-pro' ),
						'post_title_desc' => __( 'Post Title Descending', 'multi-rating-pro' )
				);
				
				foreach ( $sort_by_options as $sort_by_options_value => $sort_by_options_name ) {
					$selected = '';
					if ( $sort_by_options_value == $sort_by ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $sort_by_options_value . '" ' . $selected . '>' . $sort_by_options_name . '</option>';
				}
				?>
			</select>
		</p>
		<?php	
	}
}

/**
 * User Rating Results List Widget
 */
class MRP_User_Rating_Results_List_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {

		$id_base = 'user_rating_results_list';
		$name = __( 'User Rating Results List', 'multi-rating-pro' );
		$widget_opts = array(
				'classname' => 'user-rating-results-list-widget',
				'description' => __( 'Displays a list of overall rating results for the current logged in user.', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		extract($args);

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = empty( $instance['limit'] ) ? 10 : intval( $instance['limit'] );
		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		$taxonomy =  empty( $instance['taxonomy'] ) ? '' : $instance['taxonomy'];
		$term_id = 0;
		if ( ! empty( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = empty( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$show_date = empty( $instance['show_date'] ) ? false : $instance['show_date'];
		$show_featured_img = empty( $instance['show_featured_img'] ) ? false : $instance['show_featured_img'];
		$image_size = empty( $instance['image_size'] ) ? 'thumbnail' : $instance['image_size'];
		$header = empty( $instance['header'] ) ? 'h3' : $instance['header'];
		$sort_by =  empty( $instance['sort_by'] ) ? 'highest_rated' : $instance['sort_by'];
		$filter_label_text =  $instance['filter_label_text'];
		$show_rank = empty( $instance['show_rank'] ) ? false : $instance['show_rank'];
		$result_type = empty( $instance['result_type'] ) ? $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] : $instance['result_type'];

		$before_title = '<' . $header . ' class="widget-title">';
		$after_title = '</' . $header . '>';
		
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		mrp_user_rating_results( apply_filters( 'mrp_user_rating_results_params', array(
				'rating_form_id' => $rating_form_id,
				'limit' => $limit, 'title' => $title,
				'show_filter' => $show_filter,
				'taxonomy' => $taxonomy,
				'term_id' => $term_id,
				'class' => 'mrp-widget mrp-user-rating-results-list-widget',
				'before_title' => $before_title,
				'after_title' => $after_title,
				'show_featured_img' => $show_featured_img,
				'image_size' => $image_size,
				'sort_by' => $sort_by,
				'show_rank' => $show_rank,
				'filter_label_text' => $filter_label_text,
				'result_type' => $result_type,
				'show_date' => $show_date
		) ) );

		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['taxonomy'] = $new_instance['taxonomy'];
		$instance['term_id'] = 0;
		if ( ! empty($new_instance['term_id'] ) && is_numeric( $new_instance['term_id'] ) ) {
			$instance['term_id'] = intval( $new_instance['term_id'] );
		}
		$instance['show_filter'] = false;
		if ( isset( $new_instance['show_filter'] ) && ( $new_instance['show_filter'] == 'true' ) ) {
			$instance['show_filter'] = true;
		}
		$instance['show_featured_img'] = false;
		if ( isset( $new_instance['show_featured_img'] ) && ( $new_instance['show_featured_img'] == 'true' ) ) {
			$instance['show_featured_img'] = true;
		}
		$instance['show_rank'] = false;
		if ( isset( $new_instance['show_rank'] ) && ( $new_instance['show_rank'] == 'true' ) ) {
			$instance['show_rank'] = true;
		}
		$instance['show_date'] = false;
		if ( isset( $new_instance['show_date'] ) && ( $new_instance['show_date'] == 'true' ) ) {
			$instance['show_date'] = true;
		}
		$instance['image_size'] = $new_instance['image_size'];
		$instance['header'] = $new_instance['header'];
		$instance['sort_by'] = $new_instance['sort_by'];
		$instance['filter_label_text'] = $new_instance['filter_label_text'];
		$instance['result_type'] = $new_instance['result_type'];

		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$instance = wp_parse_args( (array) $instance, array(
				'title' => $custom_text_settings[MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION], // TODO rename
				'limit' => 10,
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'show_featured_img' => true,
				'image_size' => 'thumbnail',
				'header' => 'h3',
				'sort_by' => 'highest_rated',
				'show_filter' => true,
				'taxonomy' => '',
				'term_id' => 0,
				'show_rank' => true,
				'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
				'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
				'show_date' => true
		) );

		$title = strip_tags( $instance['title'] );
		$limit = intval( $instance['limit'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$taxonomy =  isset($instance['taxonomy']) ? trim($instance['taxonomy']) : '';
		$term_id = 0;
		if ( ! empty( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = empty( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$show_date = empty( $instance['show_date'] ) ? false : $instance['show_date'];
		$show_featured_img = empty( $instance['show_featured_img'] ) ? false : $instance['show_featured_img'];
		$image_size = $instance['image_size'];
		$header = $instance['header'];
		$sort_by = $instance['sort_by'];
		$show_rank = empty( $instance['show_rank'] ) ? false : $instance['show_rank'];
		$filter_label_text = $instance['filter_label_text'];
		$result_type = $instance['result_type'];

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit'); ?>"><?php _e( 'Limit', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" min="0" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'result_type' ); ?>"><?php _e( 'Result Type', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'result_type' ); ?>" id="<?php echo $this->get_field_id( 'result_type' ); ?>">
				<?php 
				$result_type_options = array(
						'star_rating' => __( 'Stars', 'multi-rating-pro' ),
						'percentage' => __( 'Percentage', 'multi-rating-pro' ),
						'score' => __( 'Score', 'multi-rating-pro' )
				);
				
				foreach ( $result_type_options as $result_type_option_value => $result_type_option_label ) {
					$selected = '';
					if ( $result_type_option_value == $result_type ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $result_type_option_value . '" ' . $selected . '>' . $result_type_option_label . '</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy', 'multi-rating-pro' ); ?></label>
			<select class="widefat mrp-user-rating-results-widget-taxonomy" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">				
				<?php
				//$selected = '';
				if ( $taxonomy === '' || $taxonomy == null ) {
					$selected = ' selected="selected"';
				}
				echo '<option value=""' . $selected . '></option>';
				
				$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
				foreach ( $taxonomies  as $current_taxonomy ) {
					$selected = '';
					if ( $current_taxonomy->name === $taxonomy ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_taxonomy->name . '"' . $selected . '>' . $current_taxonomy->labels->name . '</option>';
				} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'term_id' ); ?>"><?php _e( 'Terms', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'term_id' ); ?>" id="<?php echo $this->get_field_id( 'term_id' ); ?>">
			<?php
			$selected = '';
			if ( $taxonomy === '' || $taxonomy == null ) {
				echo '<option value="" selected="selected"></option>';
			} else {
				if ( $term_id == 0) {
					$selected = ' selected="selected"';
				}
				echo '<option value="0"' . $selected . '>' . __( 'All', 'multi-rating-pro' ) . '</option>';
				$terms = get_terms( $taxonomy );
				foreach ( $terms  as $current_term ) {
					$selected = '';
					if ( $current_term->term_id == $term_id ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_term->term_id . '" ' . $selected . '>' . $current_term->name . '</option>';
				} 
			} ?>
			</select>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_filter' ); ?>" type="checkbox" value="true" <?php checked( true, $show_filter, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_filter' ); ?>"><?php _e( 'Show Filter', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'filter_label_text' ); ?>"><?php _e( 'Filter Label', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'filter_label_text' ); ?>" name="<?php echo $this->get_field_name( 'filter_label_text' ); ?>" type="text" value="<?php echo esc_attr( $filter_label_text ); ?>" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_featured_img' ); ?>" name="<?php echo $this->get_field_name( 'show_featured_img' ); ?>" type="checkbox" value="true" <?php checked( true, $show_featured_img, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_featured_img' ); ?>"><?php _e( 'Show Featured Image', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_rank' ); ?>" name="<?php echo $this->get_field_name( 'show_rank' ); ?>" type="checkbox" value="true" <?php checked( true, $show_rank, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_rank' ); ?>"><?php _e( 'Show Rank', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" type="checkbox" value="true" <?php checked( true, $show_date, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show Date', 'multi-rating-pro' ); ?></label>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e('Image Size', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'image_size' ); ?>" id="<?php echo $this->get_field_id( 'image_size' ); ?>">
				<?php 
				$img_sizes = MRP_Utils::get_image_sizes();
				
				foreach ( $img_sizes as $img_size_name => $img_size_meta ) {
					$selected = '';
					if ( $img_size_name == $image_size ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $img_size_name . '" ' . $selected . '>' . $img_size_name . ' (' .  $img_size_meta['width'] . 'x' . $img_size_meta['height'] . ')</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e( 'Header', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'header' ); ?>" id="<?php echo $this->get_field_id( 'header' ); ?>">
				<?php 
				$header_options = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
				
				foreach ( $header_options as $header_option ) {
					$selected = '';
					if ( $header_option == $header ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $header_option . '" ' . $selected . '>' . strtoupper( $header_option ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_by' ); ?>"><?php _e( 'Sort By', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'sort_by' ); ?>" id="<?php echo $this->get_field_id( 'sort_by' ); ?>">
				<?php 
				$sort_by_options = array( 
						'highest_rated' => __( 'Highest Rated', 'multi-rating-pro' ),
						'lowest_rated' => __( 'Lowest Rated', 'multi-rating-pro'),
						'post_title_asc' => __( 'Post Title Ascending', 'multi-rating-pro' ),
						'post_title_desc' => __( 'Post Title Descending', 'multi-rating-pro' ),
						'latest_entry_date' => __( 'Latest Entry Date', 'multi-rating-pro' )
				);
				
				foreach ( $sort_by_options as $sort_by_options_value => $sort_by_options_name ) {
					$selected = '';
					if ( $sort_by_options_value == $sort_by ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $sort_by_options_value . '" ' . $selected . '>' . $sort_by_options_name . '</option>';
				}
				?>
			</select>
		</p>
		<?php	
	}
}

/**
 * Rating Result Widget
 */
class MRP_Rating_Result_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {

		$id_base = 'rating_result';
		$name = __( 'Rating Result', 'multi-rating-pro' );
		$widget_opts = array(
				'classname' => 'rating-result-widget',
				'description' => __( 'Displays the overall rating result for the current post.', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		// https://codex.wordpress.org/Function_Reference/url_to_postid
		// FIXME may not work with attachments. See here: https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
		$post_id = url_to_postid( MRP_Utils::get_current_url() );

		if ( $post_id == 0 || $post_id == null ) {
			return; // Nothing to do.
		}

		if ( ! apply_filters( 'mrp_can_apply_widget', true, $post_id, $args, $instance ) ) {
			return; // do nothing
		}

		extract( $args );

		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );

		if ( $rating_form_id == '' ) {
			$rating_form_id = MRP_Utils::get_rating_form( $post_id );
		}

		echo $before_widget;

		mrp_rating_result( array(
				'rating_form_id' => $rating_form_id,
				'class' => 'mrp-widget',
				'post_id' => $post_id
		) );

		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );

		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$instance = wp_parse_args( (array) $instance, array(
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
		) );

		$rating_form_id = intval( $instance['rating_form_id'] );

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>
		
		<p><?php printf( __( 'Note: Widget will only display in templates for a single post or page. See <a href="%s">is_single()</a>.', 'multi-rating-pro' ), 'https://codex.wordpress.org/Function_Reference/is_single' );?></p>
		<?php	
	}
}



/**
 * Rating Form Widget
 */
class MRP_Rating_Form_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {

		$id_base = 'rating_form';
		$name = __( 'Rating Form', 'multi-rating-pro' );
		$widget_opts = array(
				'classname' => 'rating-form-widget',
				'description' => __( 'Displays a rating form.', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {
		
		// https://codex.wordpress.org/Function_Reference/url_to_postid
		// FIXME may not work with attachments. See here: https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
		$post_id = url_to_postid( MRP_Utils::get_current_url() );
		
		if ( $post_id == 0 || $post_id == null ) {
			return; // Nothing to do.
		}
		
		if ( ! apply_filters( 'mrp_can_apply_widget', true, $post_id, $args, $instance ) ) {
			return; // do nothing
		}

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		
		if ( $rating_form_id == '' ) {
			$rating_form_id = MRP_Utils::get_rating_form( $post_id );
		}
		
		$header = empty( $instance['header'] ) ? 'h3' : $instance['header'];
		
		$before_title = '<' . $header . ' class="widget-title">';
		$after_title = '</' . $header . '>';
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		mrp_rating_form( apply_filters( 'mrp_rating_form_params', array(
				'rating_form_id' => $rating_form_id,
				'class' => 'mrp-widget mrp-rating-form-widget',
				'before_title' => $before_title,
				'after_title' => $after_title,
				'title' => $title,
				'post_id' => $post_id
		) ) );

		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['header'] = $new_instance['header'];

		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		
		$instance = wp_parse_args( (array) $instance, array(
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION],
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'header' => 'h3'
		) );

		$title = strip_tags( $instance['title'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$header = $instance['header'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e( 'Header', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'header' ); ?>" id="<?php echo $this->get_field_id( 'header' ); ?>">
				<?php 
				$header_options = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
				
				foreach ( $header_options as $header_option ) {
					$selected = '';
					if ( $header_option == $header ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $header_option . '" ' . $selected . '>' . strtoupper( $header_option ) . '</option>';
				}
				?>
			</select>
		</p>
		
		<p><?php printf( __( 'Note: Widget will only display in templates for a single post or page. See <a href="%s">is_single()</a>.', 'multi-rating-pro' ), 'https://codex.wordpress.org/Function_Reference/is_single' );?></p>
		<?php	
	}
}



/**
 * Rating Item Results Widget
 */
class MRP_Rating_Item_Results_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {

		$id_base = 'rating_item_results';
		$name = __( 'Rating Item Results', 'multi-rating-pro' );
		$widget_opts = array(
				'classname' => 'rating-item-results-widget',
				'description' => __( 'Displays a breakdown of overall rating results per rating item for the current post.', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		// https://codex.wordpress.org/Function_Reference/url_to_postid
		// FIXME may not work with attachments. See here: https://pippinsplugins.com/retrieve-attachment-id-from-image-url/
		$post_id = url_to_postid( MRP_Utils::get_current_url() );

		if ( $post_id == 0 || $post_id == null ) {
			return; // Nothing to do.
		}

		if ( ! apply_filters( 'mrp_can_apply_widget', true, $post_id, $args, $instance ) ) {
			return; // do nothing
		}

		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$rating_form_id = empty( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		
		$layout = $instance['layout'];
		$preserve_max_option = empty( $instance['preserve_max_option'] ) ? false : $instance['preserve_max_option'];
		
		if ( $rating_form_id == '' ) {
			$rating_form_id = MRP_Utils::get_rating_form( $post_id );
		}

		$header = empty( $instance['header'] ) ? 'h3' : $instance['header'];

		$before_title = '<' . $header . ' class="widget-title">';
		$after_title = '</' . $header . '>';
		$title = apply_filters( 'widget_title', $title );

		echo $before_widget;

		mrp_rating_item_results( apply_filters( 'mrp_rating_item_results_params', array(
				'rating_form_id' => $rating_form_id,
				'class' => 'mrp-widget mrp-rating-item-results-widget',
				'before_title' => $before_title,
				'after_title' => $after_title,
				'title' => $title,
				'post_id' => $post_id,
				'layout' => $layout,
				'preserve_max_option' => $preserve_max_option
		) ) );

		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['header'] = $new_instance['header'];
		$instance['layout'] = $new_instance['layout'];
		$instance['preserve_max_option'] = $new_instance['preserve_max_option'];
		
		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$instance = wp_parse_args( (array) $instance, array(
				'title' => __( 'Rating Item Results', 'multi-rating-pro' ),
				'rating_form_id' => $general_settings[ MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION ],
				'header' => 'h3',
				'layout' => 'options_block',
				'preserve_max_option_value' => false
		) );

		$title = strip_tags( $instance['title'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$header = $instance['header'];
		$layout = $instance['layout'];
		$preserve_max_option = empty( $instance['preserve_max_option'] ) ? false : $instance['preserve_max_option'];

		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e( 'Header', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'header' ); ?>" id="<?php echo $this->get_field_id( 'header' ); ?>">
				<?php 
				$header_options = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
				
				foreach ( $header_options as $header_option ) {
					$selected = '';
					if ( $header_option == $header ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $header_option . '" ' . $selected . '>' . strtoupper( $header_option ) . '</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout', 'multi-rating-pro' ); ?></label>
			
			<select class="widefat" name="<?php echo $this->get_field_name( 'layout' ); ?>" id="<?php echo $this->get_field_id( 'layout' ); ?>">
				<option value="no_options" <?php if ( $layout == 'no_options' ) echo 'selected'; ?>><?php _e( 'No options', 'multi-rating-pro' ); ?></option>
				<option value="options_inline" <?php if ( $layout == 'options_inline' ) echo 'selected'; ?>><?php _e( 'Options inline', 'multi-rating-pro' ); ?></option>
				<option value="options_block" <?php if ( $layout == 'options_block' ) echo 'selected'; ?>><?php _e( 'Options block', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		
		<p>
			<input id="<?php echo $this->get_field_id( 'preserve_max_option' ); ?>" name="<?php echo $this->get_field_name( 'preserve_max_option' ); ?>" type="checkbox" value="true" <?php checked( true, $preserve_max_option, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'preserve_max_option' ); ?>"><?php _e( 'Preserve Max Option', 'multi-rating-pro' ); ?></label>
		</p>
		
		<p><?php printf( __( 'Note: Widget will only display in templates for a single post or page. See <a href="%s">is_single()</a>.', 'multi-rating-pro' ), 'https://codex.wordpress.org/Function_Reference/is_single' );?></p>
		<?php	
	}
}





/**
 * Rating Enty List Widget
 */
class MRP_Rating_Entry_List_Widget extends WP_Widget {

	/**
	 * Constructor
	 */

	function __construct( ) {

		$id_base = 'rating_entry_list';
		$name = __( 'Rating Entries List', 'multi-rating-pro' );
		$widget_opts = array(
				'classname' => 'rating-entry-list-widget',
				'description' => __( 'Displays a list of rating entries and their details (e.g. recent reviews).', 'multi-rating-pro' )
		);
		$control_ops = array( 'width' => 400, 'height' => 350 );

		parent::__construct( $id_base, $name, $widget_opts, $control_ops );
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::widget()
	 */
	function widget( $args, $instance ) {

		extract($args);
		
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

		$title = apply_filters( 'widget_title', ! isset( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$limit = ! isset( $instance['limit'] ) ? 10 : intval( $instance['limit'] );
		$rating_form_id = ! isset( $instance['rating_form_id'] ) ? null : intval( $instance['rating_form_id'] );
		$taxonomy =  ! isset( $instance['taxonomy'] ) ? '' : $instance['taxonomy'];
		$term_id = 0;
		if ( isset( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = ! isset( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$header = ! isset( $instance['header'] ) ? 'h3' : $instance['header'];
		$sort_by =  ! isset( $instance['sort_by'] ) ? 'highest_rated' : $instance['sort_by'];
		$filter_label_text =  $instance['filter_label_text'];
		$show_avatar = ! isset( $instance['show_avatar'] ) ? false : $instance['show_avatar'];
		$show_load_more = ! isset( $instance['show_load_more'] ) ? false : $instance['show_load_more'];
		$add_author_link = ! isset( $instance['add_author_link'] ) ? false : $instance['add_author_link'];
		$show_date = ! isset( $instance['show_date'] ) ? false : $instance['show_date'];
		$result_type = ! isset( $instance['result_type'] ) ? $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION] : $instance['result_type'];
		$show_overall_rating = ! isset( $instance['show_overall_rating'] ) ? false : $instance['show_overall_rating'];
		$show_rating_items = ! isset( $instance['show_rating_items'] ) ? false : $instance['show_rating_items'];
		$show_custom_fields = ! isset( $instance['show_custom_fields'] ) ? false : $instance['show_custom_fields'];
		$show_title = ! isset( $instance['show_title'] ) ? false : $instance['show_title'];
		$show_name = ! isset( $instance['show_name'] ) ? false : $instance['show_name'];
		$show_comment = ! isset( $instance['show_comment'] ) ? false : $instance['show_comment'];
		$current_post = ! isset( $instance['current_post'] ) ? false : $instance['current_post'];
		$current_user = ! isset( $instance['current_user'] ) ? false : $instance['current_user'];
		$show_permalink = ! isset( $instance['show_permalink'] ) ? false : $instance['show_permalink'];
		$layout = ! isset( $instance['layout'] ) ? false : $instance['layout'];
		
		$before_title = '<' . $header . ' class="widget-title">';
		$after_title = '</' . $header . '>';

		$title = apply_filters( 'widget_title', $title );
		
		$user_id = null;
		if ( $current_user ) {
			global $wp_roles;
			$user = wp_get_current_user();
			if ( $user && $user->ID ) {
				$user_id = $user->ID;
			} else {
				return; // widget is not displayed
			}
		}
		
		$post_id = null;
		if ( $current_post ) {
			$post_id = url_to_postid( MRP_Utils::get_current_url() );
			if ( $post_id == 0 ) {
				return; // widget is not displayed
			}
		}
		
		echo $before_widget;

		mrp_rating_entry_details_list( apply_filters( 'mrp_rating_entry_details_list_params', array( 
				'limit' => $limit,
				'offset' => 0,
				'rating_form_id' => $rating_form_id,
				'sort_by' => $sort_by,
				'post_id' => $post_id,
				'user_id' => $user_id,
				'entry_status' => 'approved',
				'title' => $title,
				'before_title' => $before_title,
				'after_title' => $after_title,
				'result_type' => $result_type,
				'show_name' => $show_name,
				'before_name' => '',
				'after_name' => '',
				'show_date' => $show_date,
				'before_date' => '',
				'after_date' => '',
				'show_comment' => $show_comment,
				'before_comment' => '',
				'after_comment' => '',
				'show_avatar' => $show_avatar,
				'show_filter' => $show_filter,
				'no_rating_results_text' => $custom_text_settings[MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION],
				'term_id' => $term_id,
				'taxonomy' => $taxonomy,
				'class' => '',
				'filter_button_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION],
				'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
				'show_overall_rating' => $show_overall_rating,
				'show_rating_items' => $show_rating_items,
				'show_custom_fields' => $show_custom_fields,
				'show_permalink' => $show_permalink,
				'show_title' => $show_title,
				'layout' => $layout,
				'show_load_more' => $show_load_more,
				'add_author_link' => $add_author_link
		) ) );
		
		echo $after_widget;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::update()
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['rating_form_id'] = intval( $new_instance['rating_form_id'] );
		$instance['taxonomy'] = $new_instance['taxonomy'];
		$instance['term_id'] = 0;
		if ( ! empty($new_instance['term_id'] ) && is_numeric( $new_instance['term_id'] ) ) {
			$instance['term_id'] = intval( $new_instance['term_id'] );
		}
		$instance['show_filter'] = false;
		if ( isset( $new_instance['show_filter'] ) && ( $new_instance['show_filter'] == 'true' ) ) {
			$instance['show_filter'] = true;
		}
		$instance['show_avatar'] = false;
		if ( isset( $new_instance['show_avatar'] ) && ( $new_instance['show_avatar'] == 'true' ) ) {
			$instance['show_avatar'] = true;
		}
		$instance['show_load_more'] = false;
		if ( isset( $new_instance['show_load_more'] ) && ( $new_instance['show_load_more'] == 'true' ) ) {
			$instance['show_load_more'] = true;
		}
		$instance['add_author_link'] = false;
		if ( isset( $new_instance['add_author_link'] ) && ( $new_instance['add_author_link'] == 'true' ) ) {
			$instance['add_author_link'] = true;
		}
		$instance['show_date'] = false;
		if ( isset( $new_instance['show_date'] ) && ( $new_instance['show_date'] == 'true' ) ) {
			$instance['show_date'] = true;
		}
		$instance['header'] = $new_instance['header'];
		$instance['sort_by'] = $new_instance['sort_by'];
		$instance['filter_label_text'] = $new_instance['filter_label_text'];
		$instance['result_type'] = $new_instance['result_type'];
		$instance['show_overall_rating'] = false;
		if ( isset( $new_instance['show_overall_rating'] ) && ( $new_instance['show_overall_rating'] == 'true' ) ) {
			$instance['show_overall_rating'] = true;
		}
		$instance['show_rating_items'] = false;
		if ( isset( $new_instance['show_rating_items'] ) && ( $new_instance['show_rating_items'] == 'true' ) ) {
			$instance['show_rating_items'] = true;
		}
		$instance['show_custom_fields'] = false;
		if ( isset( $new_instance['show_custom_fields'] ) && ( $new_instance['show_custom_fields'] == 'true' ) ) {
			$instance['show_custom_fields'] = true;
		}
		$instance['show_title'] = false;
		if ( isset( $new_instance['show_title'] ) && ( $new_instance['show_title'] == 'true' ) ) {
			$instance['show_title'] = true;
		}
		$instance['show_name'] = false;
		if ( isset( $new_instance['show_name'] ) && ( $new_instance['show_name'] == 'true' ) ) {
			$instance['show_name'] = true;
		}
		$instance['show_comment'] = false;
		if ( isset( $new_instance['show_comment'] ) && ( $new_instance['show_comment'] == 'true' ) ) {
			$instance['show_comment'] = true;
		}
		$instance['current_post'] = false;
		if ( isset( $new_instance['current_post'] ) && ( $new_instance['current_post'] == 'true' ) ) {
			$instance['current_post'] = true;
		}
		$instance['current_user'] = false;
		if ( isset( $new_instance['current_user'] ) && ( $new_instance['current_user'] == 'true' ) ) {
			$instance['current_user'] = true;
		}
		$instance['show_permalink'] = false;
		if ( isset( $new_instance['show_permalink'] ) && ( $new_instance['show_permalink'] == 'true' ) ) {
			$instance['show_permalink'] = true;
		}
		$instance['layout'] = $new_instance['layout'];
		
		return $instance;
	}

	/**
	 * (non-PHPdoc)
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;
		$general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS );

		$instance = wp_parse_args( (array) $instance, array(
				'title' => $custom_text_settings[MRP_Multi_Rating::RATING_ENTRIES_LIST_TITLE_TEXT_OPTION],
				'limit' => 3,
				'rating_form_id' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION],
				'header' => 'h3',
				'sort_by' => 'most_recent',
				'show_filter' => true,
				'taxonomy' => '',
				'term_id' => 0,
				'filter_label_text' => $custom_text_settings[MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION],
				'result_type' => $general_settings[MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION],
				'show_avatar' => true,
				'show_date' => true,
				'show_overall_rating' => true,
				'show_rating_items' => true,
				'show_custom_fields' => true,
				'show_title' => true,
				'show_name' => true,
				'show_comment' => true,
				'current_post' => false,
				'current_user' => false,
				'show_permalink' => true,
				'layout' => 'inline',
				'show_load_more' => false,
				'add_author_link' => true
		) );

		$title = strip_tags( $instance['title'] );
		$limit = intval( $instance['limit'] );
		$rating_form_id = intval( $instance['rating_form_id'] );
		$taxonomy =  isset( $instance['taxonomy'] ) ? trim( $instance['taxonomy'] ) : '';
		if ( isset( $instance['term_id'] ) && is_numeric( $instance['term_id'] ) ) {
			$term_id = intval( $instance['term_id'] );
		}
		$show_filter = empty( $instance['show_filter'] ) ? false : $instance['show_filter'];
		$header = $instance['header'];
		$sort_by = $instance['sort_by'];
		$show_avatar = ! isset( $instance['show_avatar'] ) ? true : $instance['show_avatar'];
		$show_load_more = ! isset( $instance['show_load_more'] ) ? false : $instance['show_load_more'];
		$add_author_link = ! isset( $instance['add_author_link'] ) ? true : $instance['add_author_link'];
		$show_date = ! isset( $instance['show_date'] ) ? false : $instance['show_date'];
		$filter_label_text = $instance['filter_label_text'];
		$result_type = $instance['result_type'];
		$show_overall_rating = ! isset( $instance['show_overall_rating'] ) ? false : $instance['show_overall_rating'];
		$show_rating_items = ! isset( $instance['show_rating_items'] ) ? false : $instance['show_rating_items'];
		$show_custom_fields = ! isset( $instance['show_custom_fields'] ) ? false : $instance['show_custom_fields'];
		$show_name = ! isset( $instance['show_name'] ) ? false : $instance['show_name'];
		$show_title = ! isset( $instance['show_title'] ) ? false : $instance['show_title'];
		$show_comment = ! isset( $instance['show_comment'] ) ? false : $instance['show_comment'];
		$show_permalink = ! isset( $instance['show_permalink'] ) ? false : $instance['show_permalink'];
		$current_user = ! isset( $instance['current_user'] ) ? false : $instance['current_user'];
		$current_post = ! isset( $instance['current_post'] ) ? false : $instance['current_post'];
		$layout = $instance['layout'];
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit'); ?>"><?php _e( 'Limit', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="number" min="0" value="<?php echo esc_attr( $limit ); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'rating_form_id' ); ?>"><?php _e( 'Rating Form', 'multi-rating-pro' ); ?></label>
			<?php 
			mrp_rating_form_select( $rating_form_id, false, true, $this->get_field_name( 'rating_form_id' ), $this->get_field_id( 'rating_form_id' ), 'widefat' );
			?>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'result_type' ); ?>"><?php _e( 'Result Type', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'result_type' ); ?>" id="<?php echo $this->get_field_id( 'result_type' ); ?>">
				<?php 
				$result_type_options = array(
						'star_rating' => __( 'Stars', 'multi-rating-pro' ),
						'percentage' => __( 'Percentage', 'multi-rating-pro' ),
						'score' => __( 'Score', 'multi-rating-pro' )
				);
				
				foreach ( $result_type_options as $result_type_option_value => $result_type_option_label ) {
					$selected = '';
					if ( $result_type_option_value == $result_type ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $result_type_option_value . '" ' . $selected . '>' . $result_type_option_label . '</option>';
				}
				?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy', 'multi-rating-pro' ); ?></label>
			<select class="widefat mrp-rating-results-widget-taxonomy" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">				
				<?php
				//$selected = '';
				if ( $taxonomy === '' || $taxonomy == null ) {
					$selected = ' selected="selected"';
				}
				echo '<option value=""' . $selected . '></option>';
				
				$taxonomies = get_taxonomies( array( 'public' => true ), 'objects', 'and' );
				foreach ( $taxonomies  as $current_taxonomy ) {
					$selected = '';
					if ( $current_taxonomy->name === $taxonomy ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_taxonomy->name . '"' . $selected . '>' . $current_taxonomy->labels->name . '</option>';
				} ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'term_id' ); ?>"><?php _e( 'Terms', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'term_id' ); ?>" id="<?php echo $this->get_field_id( 'term_id' ); ?>">
			<?php
			$selected = '';
			if ( $taxonomy === '' || $taxonomy == null ) {
				echo '<option value="" selected="selected"></option>';
			} else {
				if ($term_id == 0) {
					$selected = ' selected="selected"';
				}
				echo '<option value="0"' . $selected . '>' . __( 'All', 'multi-rating-pro' ) . '</option>';
				$terms = get_terms( $taxonomy );
				foreach ( $terms  as $current_term ) {
					$selected = '';
					if ( $current_term->term_id == $term_id ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $current_term->term_id . '" ' . $selected . '>' . $current_term->name . '</option>';
				} 
			} ?>
			</select>
		</p>

		<p>
			<input id="<?php echo $this->get_field_id( 'show_filter' ); ?>" name="<?php echo $this->get_field_name( 'show_filter' ); ?>" type="checkbox" value="true" <?php checked( true, $show_filter, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_filter' ); ?>"><?php _e( 'Show Filter', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'filter_label_text' ); ?>"><?php _e( 'Filter Label', 'multi-rating-pro' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'filter_label_text' ); ?>" name="<?php echo $this->get_field_name( 'filter_label_text' ); ?>" type="text" value="<?php echo esc_attr( $filter_label_text ); ?>" />
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_avatar' ); ?>" name="<?php echo $this->get_field_name( 'show_avatar' ); ?>" type="checkbox" value="true" <?php checked( true, $show_avatar, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_avatar' ); ?>"><?php _e( 'Show Avatar', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'add_author_link' ); ?>" name="<?php echo $this->get_field_name( 'add_author_link' ); ?>" type="checkbox" value="true" <?php checked( true, $add_author_link, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'add_author_link' ); ?>"><?php _e( 'Add Author Link', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" type="checkbox" value="true" <?php checked( true, $show_date, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Show Date', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php _e( 'Header', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'header' ); ?>" id="<?php echo $this->get_field_id( 'header' ); ?>">
				<?php 
				$header_options = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' );
				
				foreach ( $header_options as $header_option ) {
					$selected = '';
					if ( $header_option == $header ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $header_option . '" ' . $selected . '>' . strtoupper( $header_option ) . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'sort_by' ); ?>"><?php _e( 'Sort By', 'multi-rating-pro' ); ?></label>
			<select class="widefat" name="<?php echo $this->get_field_name( 'sort_by' ); ?>" id="<?php echo $this->get_field_id( 'sort_by' ); ?>">
				<?php 
				$sort_by_options = array( 
						'highest_rated' 	=> __( 'Highest Rated', 'multi-rating-pro' ),
						'lowest_rated' 		=> __( 'Lowest Rated', 'multi-rating-pro'),
						'most_recent' 		=> __( 'Most Recent', 'multi-rating-pro'),
						'oldest' 			=> __( 'Oldest', 'multi-rating-pro'),
				);
				
				foreach ( $sort_by_options as $sort_by_options_value => $sort_by_options_name ) {
					$selected = '';
					if ( $sort_by_options_value == $sort_by ) {
						$selected = ' selected="selected"';
					}
					echo '<option value="' . $sort_by_options_value . '" ' . $selected . '>' . $sort_by_options_name . '</option>';
				}
				?>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_overall_rating' ); ?>" name="<?php echo $this->get_field_name( 'show_overall_rating' ); ?>" type="checkbox" value="true" <?php checked( true, $show_overall_rating, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_overall_rating' ); ?>"><?php _e( 'Show Overall Rating', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_rating_items' ); ?>" name="<?php echo $this->get_field_name( 'show_rating_items' ); ?>" type="checkbox" value="true" <?php checked( true, $show_rating_items, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_rating_items' ); ?>"><?php _e( 'Show Rating Items', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_custom_fields' ); ?>" name="<?php echo $this->get_field_name( 'show_custom_fields' ); ?>" type="checkbox" value="true" <?php checked( true, $show_custom_fields, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_custom_fields' ); ?>"><?php _e( 'Show Custom Fields', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_title' ); ?>" name="<?php echo $this->get_field_name( 'show_title' ); ?>" type="checkbox" value="true" <?php checked( true, $show_title, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_title' ); ?>"><?php _e( 'Show Title', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_name' ); ?>" name="<?php echo $this->get_field_name( 'show_name' ); ?>" type="checkbox" value="true" <?php checked( true, $show_name, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_name' ); ?>"><?php _e( 'Show Name', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_comment' ); ?>" name="<?php echo $this->get_field_name( 'show_comment' ); ?>" type="checkbox" value="true" <?php checked( true, $show_comment, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_comment' ); ?>"><?php _e( 'Show Comment', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_permalink' ); ?>" name="<?php echo $this->get_field_name( 'show_permalink' ); ?>" type="checkbox" value="true" <?php checked( true, $show_permalink, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_permalink' ); ?>"><?php _e( 'Show Permalink', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'current_user' ); ?>" name="<?php echo $this->get_field_name( 'current_user' ); ?>" type="checkbox" value="true" <?php checked( true, $current_user, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'current_user' ); ?>"><?php _e( 'Current User', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'current_post' ); ?>" name="<?php echo $this->get_field_name( 'current_post' ); ?>" type="checkbox" value="true" <?php checked( true, $current_post, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'current_post' ); ?>"><?php _e( 'Current Post', 'multi-rating-pro' ); ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Layout', 'multi-rating-pro' ); ?></label>
			<select name="<?php echo $this->get_field_name( 'layout' ); ?>" id="<?php echo $this->get_field_id( 'layout' ); ?>">				
				<option value="table" <?php if ( $layout == 'table' ) { echo 'selected="selected"'; } ?>><?php _e( 'Table', 'multi-rating-pro' ); ?></option>
				<option value="inline" <?php if ( $layout == 'inline' ) { echo 'selected="selected"'; } ?>><?php _e( 'Inline', 'multi-rating-pro' ); ?></option>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_load_more' ); ?>" name="<?php echo $this->get_field_name( 'show_load_more' ); ?>" type="checkbox" value="true" <?php checked( true, $show_load_more, true ); ?>/>
			<label for="<?php echo $this->get_field_id( 'show_load_more' ); ?>"><?php _e( 'Show Load More', 'multi-rating-pro' ); ?></label>
		</p>
		<?php	
	}
}


/**
 * Checks filters settings to determine whether rating form or rating results widget can be applied
 *
 * @param boolean $can_apply_widget
 * @param string $filter_name
 * @param string $value
 * @param int $post_id
 * @return $can_apply_widget
 */
function mrp_can_apply_widget( $can_apply_widget, $post_id, $args, $instance ) {

	if ( $can_apply_widget ) {
		$auto_placememt_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );

		$can_apply_widget = ( MRP_Utils::check_post_type_enabled( $post_id )
				&& ( ! is_home() || ( is_home() && $auto_placememt_settings[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == false ) )
				&& ( ! is_search() || ( is_search() && $auto_placememt_settings[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] == false ) )
				&& ( ! is_archive() || ( is_archive() && $auto_placememt_settings[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == false) ) );
		
		if ( $can_apply_widget ) {
			$filter = MRP_Utils::get_filter( $post_id ) ;
				
			if ( $filter ) {
				return MRP_Utils::check_filter( $filter, $post_id );
			}
		}
	}

	return $can_apply_widget;
}
add_filter( 'mrp_can_apply_widget', 'mrp_can_apply_widget', 10, 4 );


/**
 * Gets terms by taxonomy and returns a JSON response
 */
function mrp_get_terms_by_taxonomy() {
	$ajax_nonce = $_POST['nonce'];

	$response = array();

	if ( wp_verify_nonce( $ajax_nonce, MRP_Multi_Rating::ID.'-nonce' ) ) {
		$taxonomy = isset( $_POST['taxonomy'] ) ? $_POST['taxonomy'] : 'category';
			
		$terms = get_terms( $taxonomy );
			
		array_push( $response, array( 'name' => __( 'All', 'multi-rating-pro' ), 'term_id' => 0 ) );
			
		foreach ( $terms as $term ) {
			array_push( $response, array( 'name' => $term->name, 'term_id' => $term->term_id ) );
		}
			
		echo json_encode( $response );
	}

	die();
}
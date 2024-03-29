<?php


add_action('admin_init', 'wpv_redirect_admin_listings');

/**
 * Prevents users from accessing the natural listing pages that WordPress creates for Views and Content Templates
 * and redirects them to the new listing pages.
 *
 * Note: Still needed regardless of register_post_type() args for those post types.
 *
 * @since unknown
 */
function wpv_redirect_admin_listings() {
	global $pagenow;
	/* Check current admin page. */
	if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view' ) {
		wp_redirect(admin_url('/admin.php?page=views', 'http'), 301);
		exit;
	} elseif ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view-template' ) {
		wp_redirect(admin_url('/admin.php?page=view-templates', 'http'), 301);
		exit;
	}
}


/**
 * Generate default View settings.
 *
 * Depending on a View purpose, generate default settings for a View.
 *
 * @param string $purpose Purpose of the view: 'all', 'pagination', 'slide', 'parametric' or 'full'. For invalid values
 *     'full' is assumed.
 *
 * @return array Array with desired values.
 *
 * @since 1.7
 */
function wpv_view_default_settings( $purpose = 'full' ) {

	/* Set the initial values for the View settings.
	 * Note: taxonomy_type is set in wpv-section-query-type.php to use the first available taxonomy. */
	$defaults = array(
			'view-query-mode' => 'normal',
			'view_description' => '',
			'view_purpose' => 'full',
			'query_type' => array( 'posts' ),
			'taxonomy_type' => array( 'category' ),
			'roles_type' => array( 'administrator' ),
			'post_type_dont_include_current_page' => true,
			'taxonomy_hide_empty' => true,
			'taxonomy_include_non_empty_decendants'	=> true,
			'taxonomy_pad_counts' => true, // check this setting application
			'orderby' => 'post_date',
			'order'	=> 'DESC',
			'orderby_second' => '',
			'order_second'	=> 'DESC',
			'taxonomy_orderby' => 'name',
			'taxonomy_order' => 'DESC',
			'users_orderby' => 'user_login',
			'users_order' => 'ASC',
			'limit'	=> -1,
			'offset' => 0,
			'taxonomy_limit' => -1,
			'taxonomy_offset' => 0,
			'users_limit' => -1,
			'users_offset' => 0,
			//'posts_per_page' => 10,// DEPRECATED
			'pagination' => array(
								//'disable',// DEPRECATED
								//'mode'							=> 'none',// DEPRECATED
								'type'							=> 'disabled',
								'posts_per_page'				=> 10,
								'effect'						=> 'fade',
								'duration'						=> 500,
								'speed'							=> 5,
								'preload_images'				=> true,
								'cache_pages'					=> true,
								'preload_pages'					=> true,
								'pre_reach'						=> 1,
								'spinner'						=> 'default',
								'spinner_image'					=> WPV_URL_EMBEDDED . '/res/img/ajax-loader.gif',
								'spinner_image_uploaded'		=> '',
								'callback_next'	=>				'' 
			),
			/*
			'ajax_pagination' => array(// DEPRECATED
				'disable',
				'style'							=> 'fade',
				'duration'						=> 500 
			),
			'rollover' => array(// DEPRECATED
				'preload_images'				=> true,
				'posts_per_page'				=> 1,
				'speed'							=> 5,
				'effect'						=> 'fade',
				'duration'						=> 500 
			),
			*/
			'filter_meta_html_state' => array(
				'html'							=> 'on',
				'css'							=> 'off',
				'js'							=> 'off',
				'img'							=> 'off' 
			),
			'filter_meta_html' => "[wpv-filter-start hide=\"false\"]\n[wpv-filter-controls][/wpv-filter-controls]\n[wpv-filter-end]",
			'filter_meta_html_css' => '',
			'filter_meta_html_js' => '',
			'layout_meta_html_state' => array(
				'html'							=> 'on',
				'css'							=> 'off',
				'js'							=> 'off',
				'img'							=> 'off' 
			),
			'layout_meta_html_css' => '',
			'layout_meta_html_js' => '' 
	);

	// purpose-specific modifications
	$defaults['view_purpose'] = $purpose;

	switch ( $purpose ) {
		case 'all':
			$defaults['sections-show-hide'] = array(
				'pagination' => 'off',
				'filter-extra-parametric' => 'off',
				'filter-extra' => 'off'	);
			break;
		case 'pagination':
			//$defaults['pagination'][0] = 'enable'; // disable --> enable // DEPRECATED
			//$defaults['pagination']['mode'] = 'paged';// DEPRECATED
			$defaults['pagination']['type'] = 'paged';
			$defaults['sections-show-hide'] = array( 'limit-offset' => 'off' );
			break;
		case 'slider':
			//$defaults['pagination'][0] = 'enable'; // disable --> enable // DEPRECATED
			//$defaults['pagination']['mode'] = 'rollover';// DEPRECATED
			$defaults['pagination']['type'] = 'rollover';
			$defaults['pagination']['posts_per_page'] = 1;
			$defaults['sections-show-hide'] = array();
			break;
		case 'parametric':
			$defaults['sections-show-hide'] = array(
					'query-options'	=> 'off',
					'limit-offset' => 'off',
					'pagination' => 'off',
					'content-filter' => 'off' );
			break;
		case 'full':
		default:
			$defaults['sections-show-hide'] = array( );
			// This has to stay here, because we're also catching invalid $purpose values.
			$defaults['view_purpose'] = 'full';
			break;
	}
	return $defaults;
}


/**
 * Generate default View layout settings.
 *
 * Depending on a View purpose, generate default settings for a View.
 *
 * @param string $purpose Purpose of the view: 'all', 'pagination', 'slide', 'parametric' or 'full'. For invalid values
 *     'full' is assumed.
 *
 * @return array Array with desired values.
 *
 * @since 1.7
 */
function wpv_view_default_layout_settings( $purpose ) {

	// almost all of this settings are only needed to create the layout on the fly, so they are not needed here
    $empty_loop_output = WPV_View_Base::generate_loop_output();

	$defaults = array(
        'additional_js' => '',
        'layout_meta_html' => $empty_loop_output['loop_output_settings']['layout_meta_html']
    );

	// Purpose-specific modifications
	switch ( $purpose ) {
		case 'all':
		case 'pagination':
			// nothing to do here... yet
			break;
		case 'slider':
			// Generate full loop output settings
			$result = WPV_View_Base::generate_loop_output( 'unformatted', array(), array() );
			$defaults = $result['loop_output_settings'];
			break;
		case 'parametric':
		case 'full':
		default:
			// nothing to do here... yet
			break;
	}
	return $defaults;
}


/**
 * Set default WordPress Archives settings and layout settings
 *
 * @param string $settings field: view_settings or view_layout_settings
 * @return array with desired values
 * @since unknown
 */
function wpv_wordpress_archives_defaults( $settings = 'view_settings', $purpose = 'all' ) {

    $empty_loop_output = WPV_View_Base::generate_loop_output();

	$defaults = array(
		'view_settings' => array(
			'view-query-mode'			=> 'archive',
			'view_description'			=> '',
			'view_purpose'				=> 'all',
			'sections-show-hide'		=> array(
                'content'	=> 'off',
            ),
			'orderby'					=> 'post_date',
			'order'						=> 'DESC',
			'orderby_second'			=> '',
			'order_second'				=> 'DESC',
			'pagination'				=> array(
												'type'						=> 'paged',
												'posts_per_page'			=> 'default',
												'effect'					=> 'fade',
												'duration'					=> 500,
												'manage_history'			=> 'on',
												'tolerance'					=> '',
												'preload_images'			=> true,
												'cache_pages'				=> true,
												'preload_pages'				=> true,
												'pre_reach'					=> 1,
												'spinner'					=> 'builtin',
												'spinner_image'				=> WPV_URL . '/res/img/ajax-loader.gif',
												'spinner_image_uploaded'	=> '',
												'callback_next'				=> '',
											),
			'filter_meta_html_state'	=> array(
												'html'				=> 'on',
												'css'				=> 'off',
												'js'				=> 'off',
												'img'				=> 'off' 
											),
			'filter_meta_html'			=> "[wpv-filter-start hide=\"false\"]\n[wpv-filter-controls][/wpv-filter-controls]\n[wpv-filter-end]",
			'filter_meta_html_css'		=> '',
			'filter_meta_html_js'		=> '',
			'layout_meta_html_state' => array(
												'html'				=> 'on',
												'css'				=> 'off',
												'js'				=> 'off',
												'img'				=> 'off' 
											),
			'layout_meta_html_css'		=> '',
			'layout_meta_html_js'		=> '',
		),
		'view_layout_settings' => array(
		    // almost all of this settings are only needed to create the layout on the fly, so they are not needed here
			'additional_js'				=> '',
			'layout_meta_html'			=> $empty_loop_output['loop_output_settings']['layout_meta_html'],
		),
	);
	
	// purpose-specific modifications
	$defaults['view_settings']['view_purpose'] = $purpose;
	
	switch ( $purpose ) {
		case 'all':
			$defaults['view_settings']['sections-show-hide'] = array(
				'filter-extra-parametric'	=> 'off',
				'filter-extra'				=> 'off'	
			);
			break;
		case 'parametric':
			$defaults['view_settings']['sections-show-hide'] = array(
				
			);
			break;
	}
	
	return $defaults[ $settings ];
}


/**
* Cleans the WordPress Media popup to be used in Views and WordPress Archives
*
* @param $strings elements to be included
* @return $strings without the unwanted sections
*/

add_filter( 'media_view_strings', 'custom_media_uploader' );

function custom_media_uploader( $strings ) {
	if ( isset( $_GET['page'] ) && ( 'view-archives-editor' == $_GET['page'] || 'views-editor' == $_GET['page'] ) ) {
		unset( $strings['createGalleryTitle'] ); //Create Gallery
	}
	return $strings;
}

/**
 * Add the Fields and Views button and dialogs.
 *
 * @param int $editor_id ID for the relevant textarea, to be set as active editor.
 *
 * @since unknown
 * @since 2.3.0 Unify the buttons on Views, stop generating one button for each query type.
 * @since 2.3.0 Remove the wrapper div.wpv-vicon-for-{query-type} around the buttons.
 */
function wpv_add_v_icon_to_codemirror( $editor_id ) {
	
	$fields_and_views_button_args = array(
		'output'	=> 'button'
	);
	
    if ( 
		isset( $_GET['page'] )
		&& 'views-editor' == $_GET['page']
		&& isset( $_GET['view_id'] ) 
		&& is_numeric( $_GET['view_id'] )
	) {
		
		// Add a basic button that will generate the dialog for posts, 
		// and then force generating the dialog for taxonomy terms and users.
		do_action( 'wpv_action_wpv_generate_fields_and_views_button', $editor_id, $fields_and_views_button_args );
		do_action( 'wpv_action_wpv_require_shortcodes_dialog_target', 'taxonomy' );
		do_action( 'wpv_action_wpv_require_shortcodes_dialog_target', 'users' );
		
    } else if (
		isset( $_GET['page'] )
		&& 'view-archives-editor' == $_GET['page']
		&& isset( $_GET['view_id'] ) 
		&& is_numeric( $_GET['view_id'] )
	) {
		
		// We should include termmeta fields here because users will need a way to show them for WPA taxonomy loops
		// Note that they should produce no output on all other archive loops
		// Add a basic button that will generate the dialog for posts.
		do_action( 'wpv_action_wpv_generate_fields_and_views_button', $editor_id, $fields_and_views_button_args );
		
	} else {
		
		// Add a basic button that will generate the dialog for posts.
		do_action( 'wpv_action_wpv_generate_fields_and_views_button', $editor_id, $fields_and_views_button_args );
		
	}
	
}

// Deprecated, keep only for the Loop Wizard :-/
function wpv_layout_taxonomy_V( $menu ) {
    // remove post items and add taxonomy items.
    global $wpv_shortcodes;
    $basic = __( 'Basic', 'wpv-views' );
    //$menu = array($basic => array());
	$allowed_menus = array(
		__( 'Post View', 'wpv-views' ) => true,
		__( 'Taxonomy View', 'wpv-views' ) => true,
		__( 'User View', 'wpv-views' ) => true
	);
	$allowed_menus = apply_filters( 'wpv_filter_wpv_editor_addon_keep_default_registered_menus_for_taxonomy', $allowed_menus );
	$menu = array_intersect_key( $menu, $allowed_menus );
    $taxonomy = array(
		'wpv-taxonomy-title',
		'wpv-taxonomy-link',
		'wpv-taxonomy-url',
		'wpv-taxonomy-slug',
		'wpv-taxonomy-id',
		'wpv-taxonomy-description',
		'wpv-taxonomy-post-count'
	);
    foreach ( $taxonomy as $key ) {
        $menu[ $basic ][ $wpv_shortcodes[ $key ][1] ] = array( 
			$wpv_shortcodes[ $key ][1], 
			$wpv_shortcodes[ $key ][0], 
			$basic, 
			''
		);
    }
	$nonce = wp_create_nonce('wpv_editor_callback');
	$wpv_shortcodes['wpv-taxonomy-field'] = array('wpv-taxonomy-field', __('Taxonomy field', 'wpv-views'), 'wpv_shortcode_wpv_tax_field');
	$menu[ $basic ][ __('Taxonomy field', 'wpv-views') ] = array( 
		__('Taxonomy field', 'wpv-views'), 
		'wpv-taxonomy-field', 
		$basic, 
		"WPViews.shortcodes_gui.wpv_insert_popup('wpv-taxonomy-field', '" . esc_js( __('Taxonomy field', 'wpv-views') ). "', {}, '" . $nonce . "', this )"
	);
	
    return $menu;
}

// Deprecated, keep only for the Loop Wizard :-/
function wpv_include_types_termmeta_fields( $menu ) {
	if ( function_exists('wpcf_init') ) {
		//Get types groups and fields
		$groups = wpcf_admin_fields_get_groups( 'wp-types-term-group' );
		$add = array();
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group_id => $group ) {
				if ( empty( $group['is_active'] ) ) {
					continue;
				}
				$fields = wpcf_admin_fields_get_fields_by_group( 
					$group['id'],
					'slug',
					true,
					false,
					true,
					'wp-types-term-group',
					'wpcf-termmeta' 
				);
				// @since m2m wpcf_admin_fields_get_fields_by_group returns strings for repeatng fields groups
				$fields = array_filter( $fields, 'is_array' );
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field_id => $field ) {
						$menu[$group['name']][$field['name']] = array(
							$field['name'],
							'types termmeta="'.$field['id'].'"][/types',
							$group['name'],
							'wpcfFieldsEditorCallback(\'' . $field['id'] . '\', \'views-termmeta\', -1)'
						);
						$add[] = $field['meta_key'];
					}
				}
			}
		}
		//Get un-grouped Types fields
		$cf_types = wpcf_admin_fields_get_fields( true, true, false, 'wpcf-termmeta' );
		foreach ( $cf_types as $cf_id => $cf ) {
			if ( ! in_array( $cf['meta_key'], $add ) ) {
				$menu[__('Types fields', 'wpv-views')][$cf['name']] = array(
					$cf['name'],
					'types termmeta="'.$cf['id'].'"][/types',
					__('Types fields', 'wpv-views'),
					'wpcfFieldsEditorCallback(\'' . $cf['id'] . '\', \'views-termmeta\', -1)'
				);
			}
		}
	}
	
    return $menu;
}

/**
 * Add usermeta V icon menu
 *
 **/
// Deprecated, keep only for the Loop Wizard :-/
function wpv_layout_users_V( $menu ) {
	$nonce = wp_create_nonce('wpv_editor_callback');
    $basic = __( 'Basic', 'wpv-views' );
    //$menu = array($basic => array());
	$allowed_menus = array(
		__( 'Post View', 'wpv-views' )		=> true,
		__( 'Taxonomy View', 'wpv-views' )	=> true,
		__( 'User View', 'wpv-views' )		=> true
	);
	$allowed_menus = apply_filters( 'wpv_filter_wpv_editor_addon_keep_default_registered_menus_for_users', $allowed_menus );
	$menu = array_intersect_key( $menu, $allowed_menus );
    $user_shortcodes = array(
			'ID'			=> array(
				'label'	=> __('User ID', 'wpv-views'),
				'code'	=> 'wpv-user field="ID"'
			),
			'user_email'		=> array(
				'label'	=> __('User Email', 'wpv-views'),
				'code'	=> 'wpv-user field="user_email"'
			),
			'user_login'		=> array(
				'label'	=> __('User Login', 'wpv-views'),
				'code'	=> 'wpv-user field="user_login"'
			),
			'user_firstname'	=> array(
				'label'	=> __('First Name', 'wpv-views'),
				'code'	=> 'wpv-user field="user_firstname"'
			),
			'user_lastname'		=> array(
				'label'	=> __('Last Name', 'wpv-views'),
				'code'	=> 'wpv-user field="user_lastname"'
			),
			'nickname'			=> array(
				'label'	=> __('Nickname', 'wpv-views'),
				'code'	=> 'wpv-user field="nickname"'
			),
			'display_name'		=> array(
				'label'	=> __('Display Name', 'wpv-views'),
				'code'	=> 'wpv-user field="display_name"'
			),
            'profile_picture'	=> array(
                'label'	=> __( 'Profile Picture', 'wpv-views' ),
                'code'	=> 'wpv-user field="profile_picture"'
            ),
			'user_nicename'		=> array(
				'label'	=> __('Nicename', 'wpv-views'),
				'code'	=> 'wpv-user field="user_nicename"'
			),
			'description'		=> array(
				'label'	=> __('Description', 'wpv-views'),
				'code'	=> 'wpv-user field="description"'
			),
			'yim'				=> array(
				'label'	=> __('Yahoo IM', 'wpv-views'),
				'code'	=> 'wpv-user field="yim"'
			),
			'jabber'			=> array(
				'label'	=> __('Jabber', 'wpv-views'),
				'code'	=> 'wpv-user field="jabber"'
			),
			'aim'				=> array(
				'label'	=> __('AIM', 'wpv-views'),
				'code'	=> 'wpv-user field="aim"'
			),
			'user_url'			=> array(
				'label'	=> __('User URL', 'wpv-views'),
				'code'	=> 'wpv-user field="user_url"'
			),
			'user_registered'	=> array(
				'label'	=> __('Registration Date', 'wpv-views'),
				'code'	=> 'wpv-user field="user_registered"'
			),
			'user_status'		=> array(
				'label'	=> __('User Status', 'wpv-views'),
				'code'	=> 'wpv-user field="user_status"'
			),
			'spam'				=> array(
				'label'	=> __('User Spam Status', 'wpv-views'),
				'code'	=> 'wpv-user field="spam"'
			),
		);
    foreach ( $user_shortcodes as $shortcode_slug => $shortcode_data ) {
		$menu[$basic][$shortcode_data['label']] = array( 
			$shortcode_data['label'], 
			$shortcode_data['code'], 
			$basic, 
			"WPViews.shortcodes_gui.wpv_insert_popup('wpv-user', '" . esc_js( $shortcode_data['label'] ) . "', {attributes:{field:'" . esc_js( $shortcode_slug ) . "'}}, '" . $nonce . "', this )"
		);
    }
	
	// Add the toolset-edit-user-link item, only when CRED is available
	// @since 2.4.0
	if ( defined( 'CRED_FE_VERSION' ) ) {
		if ( ! isset( $menu[ __( 'CRED Editing', 'wpv-views' ) ] ) ) {
			$menu[ __( 'CRED Editing', 'wpv-views' ) ] = array();
		}
		$menu[ __( 'CRED Editing', 'wpv-views' ) ][ __( 'CRED edit-user link', 'wpv-views' ) ] = array(
			__( 'CRED edit-user link', 'wpv-views' ),
			'toolset-edit-user-link',
			__( 'CRED Editing', 'wpv-views' ),
			"WPViews.shortcodes_gui.wpv_insert_popup('toolset-edit-user-link', '" . esc_js( __( 'CRED edit-user link', 'wpv-views' ) ) . "', {}, '" . $nonce . "', this )"
		);
	}
	
	if ( function_exists('wpcf_init') ) {
		//Get types groups and fields
		$groups = wpcf_admin_fields_get_groups( 'wp-types-user-group' );
		$add = array();
		if ( ! empty( $groups ) ) {
			foreach ( $groups as $group_id => $group ) {
				if ( empty( $group['is_active'] ) ) {
					continue;
				}
				$fields = wpcf_admin_fields_get_fields_by_group( 
					$group['id'],
					'slug',
					true,
					false,
					true,
					'wp-types-user-group',
					'wpcf-usermeta' 
				);
				// @since m2m wpcf_admin_fields_get_fields_by_group returns strings for repeatng fields groups
				$fields = array_filter( $fields, 'is_array' );
				if ( ! empty( $fields ) ) {
					foreach ( $fields as $field_id => $field ) {
						$menu[$group['name']][$field['name']] = array(
							$field['name'],
							'types usermeta="'.$field['id'].'"][/types',
							$group['name'],
							'wpcfFieldsEditorCallback(\'' . $field['id'] . '\', \'views-usermeta\', -1)'
						);
						$add[] = $field['meta_key'];
					}
				}
			}
		}
		//Get un-grouped Types fields
		$cf_types = wpcf_admin_fields_get_fields( true, true, false, 'wpcf-usermeta' );
		foreach ( $cf_types as $cf_id => $cf ) {
			if ( ! in_array( $cf['meta_key'], $add ) ) {
				$menu[__('Types fields', 'wpv-views')][$cf['name']] = array(
					$cf['name'],
					'types usermeta="'.$cf['id'].'"][/types',
					__('Types fields', 'wpv-views'),
					'wpcfFieldsEditorCallback(\'' . $cf['id'] . '\', \'views-usermeta\', -1)'
				);
			}
		}
	}
    return $menu;
}

/**
 * wpv_create_content_template
 *
 * Creates a new Content Template given a title and an optional suffix.
 *
 * Consider using WPV_Content_Template::create_new directly.
 *
 * @note Used by Layouts plugin.
 *
 * @param string $title
 * @param string $suffix
 * @param bool $force Whether to force the creation of the Template by incremental numbers added to the title in case it is already in use
 * @param string $content
 *
 * @return array {
 *     'success' => (int) The ID of the CT created
 *      'error' => (string) Error message
 *      'title' => (string) The title of the CT created or the one that made this fail
 *
 * @since 1.7
 * @deprecated Use WPV_Content_Template::create() instead.
 */
function wpv_create_content_template( $title, $suffix = '', $force = true, $content = '' ) {

	$real_suffix = '';
	if ( ! empty( $suffix ) ) {
		$real_suffix = ' - ' . $suffix;
	}
    $template_title = $title . $real_suffix;

    $result = array();

    if( $force ) {
        $ct = WPV_Content_Template::create( $template_title, true );
    } else {

        if( WPV_Content_Template::is_name_used( $template_title ) ) {
            $result['error'] = __( 'A Content Template with that title already exists. Please use another title.', 'wpv-views' );
            $result['title'] = $template_title;
            return $result;
        }

        $ct = WPV_Content_Template::create( $template_title, false );
    }

    if( null == $ct ) {
        $return['title'] = $template_title;
        $return['error'] = __( 'An error occurred while creating a Content Template.', 'wpv-views' );
    } else {

        $return['title'] = $ct->title;

        try {
            $ct->content_raw = $content;
            $return['success'] = $ct->id;
        } catch( Exception $e ) {
            $return['error'] = __( 'An error occurred while creating a Content Template.', 'wpv-views' );
        }

    }

    return $return;
}


/**
 * API function to create a new View
 *
 * @param $args array set of arguments for the new View
 *    'title' (string) (semi-mandatory) Title for the View
 *    'settings' (array) (optional) Array compatible with the View settings to override the defaults
 *    'layout_settings' (array) (optional) Array compatible with the View layout settings to override the defaults
 *
 * @return array response of the operation, one of the following
 *    $return['success] = View ID
 *    $return['error'] = 'Error message'
 *
 * @since 1.6.0
 *
 * @note overriding default Views settings and layout settings must provide complete data when the element is an array, because it overrides them all.
 *    For example, $args['settings']['pagination'] can not override just the "postsper page" options: it must provide a complete pagination implementation.
 *    This might change and be corrected in the future, keeping backwards compatibility.
 *
 * @todo once we create a default layout for a View, we need to make sure that:
 * - the _view_loop_template postmeta is created and updated - DONE
 * - the fields added to that loop Template are stored in the layout settings - PENDING
 * - check how Layouts can apply this all to their Views, to create a Bootstrap loop by default - PENDING
 *
 * @deprecated Since 1.10. Consider using WPV_View::create() or WPV_WordPress_Archive::create() instead.
 */
function wpv_create_view( $args ) {

    $title = wpv_getarr( $args, 'title' );
    $creation_args = $args;

    $view_settings = wpv_getarr( $args, 'settings', array() );
    $creation_args['view_settings'] = $view_settings;

    $query_mode = wpv_getarr( $view_settings, WPV_View_Base::VIEW_SETTINGS_QUERY_MODE, 'normal', array( 'normal', 'archive', 'layouts-loop' ) );

    try {

        if( 'normal' == $query_mode ) {
            $view = WPV_View::create( $title, $creation_args );
            $id = $view->id;
        } else {
            $wpa = WPV_WordPress_Archive::create( $title, $creation_args );
            $id = $wpa->id;
        }
        return array( 'success' => $id );

    } catch( WPV_RuntimeExceptionWithMessage $e ) {
        return array( 'error' => $e->getUserMessage() );
    } catch( Exception $e ) {
        return array( 'error' => __( 'The View could not be created.', 'wpv-views' ) );
    }
}


/**
 * Helper function for producing "current" CSS class for Tab design for admin screens when a condition is met.
 *
 * Inspired by WordPress checked() and selected() functions. You can either provide two values, which will be then
 * compared to each other, or one boolean value determining whether the class "current" should be produced.
 *
 * @param mixed|bool $first_value First value to compare with the second value or a boolean if second value is null.
 * @param mixed|null $second_value Second value to compare or null if first value should be used as a boolean.
 *     Default is null.
 * @param bool $echo If true, the result will be also echoed.
 *
 * @return string The result: 'class="current"' or an empty string.
 *
 * @since 1.8
 */ 
function wpv_current_class( $first_value, $second_value = null, $echo = true ) {
	if( $second_value == null ) {
		$condition = (bool) $first_value;
	} else {
		$condition = ( $first_value == $second_value );
	}
	
	$result = $condition ? 'class="current"' : '';

	if( $echo ) {
		echo $result;
	}

	return $result;
}


/**
 * Replace occurences of a View/Content Template/WordPress Archive ID by another ID in Views' settings.
 *
 * Specifically, all options starting with 'views_template_' are processed.
 *
 * @param int $replace_what The ID to be replaced.
 * @param int $replace_by New value.
 * @param null|array $settings If null, Views options are obtained from global $WP_Views and also saved there afterwards.
 *     Otherwise, an array with Views options is expected and after processing it is not saved, but returned instead.
 *
 * @return array|null Modified array of Views options if $settings was provided, null otherwise.
 *
 * @since 1.7
 * @deprecated Use WPV_Settings::replace_abstract_ct_associations instead.
 */
function wpv_replace_views_template_options( $replace_what, $replace_by, $settings = null ) {
	if( !is_array( $settings ) ) {
        $settings = WPV_Settings::get_instance()->get();
		$save_options = true;
	} else {
		$save_options = false;
	}

	foreach ( $settings as $option_name => $option_value ) {
		if ( ( strpos( $option_name, 'views_template_' ) === 0 )
			&& $option_value == $replace_what )
		{
			$settings[ $option_name ] = $replace_by;
		}
	}

	if( $save_options ) {
        WPV_Settings::get_instance()->save();
		return null;
	} else {
		return $settings;
	}
}



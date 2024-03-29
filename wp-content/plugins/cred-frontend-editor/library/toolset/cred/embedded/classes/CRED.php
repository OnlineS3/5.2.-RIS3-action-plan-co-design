<?php

require_once "CRED_StaticClass.php";
require_once "CRED_Generic_Response.php";
require_once "common/cred_functions.php";

/**
 * Main Class
 *
 * Main class of the plugin
 * Class encapsulates all hook handlers
 */
final class CRED_CRED {

    public static $_form_builder_instance;
    public static $help = array();
    public static $help_link_target = '_blank';
    public static $settingsPage = null;
    private static $prefix = '_cred_';

    /*
     * Initialize plugin enviroment
     */
    public function init() {
        
        add_filter( 'wpcf_exclude_meta_boxes_on_post_type', array('CRED_StaticClass', 'my_cred_exclude'), 10, 1 );

        //add_filter('get_items_with_flag', array(__CLASS__, "item_filter"), 10, 1);
        // plugin init
        // NOTE: Early Init, in order to catch up with early hooks by 3rd party plugins (eg Toolset Forms Commerce)
        // IMPORTANT NOTE: Now the priority must be set in order to be next to types and be carefull to Toolset Forms Commerce
        add_action( 'init', array(__CLASS__, '_init_'), 1 );

        // try to catch user shortcodes (defined by [...]) and solve shortcodes inside shortcodes
        // adding filter with priority before do_shortcode and other WP standard filters
        add_filter( 'the_content', 'cred_do_shortcode', 9 );
        add_action( 'wp_ajax_check_post_id', 'check_post_id_callback' );
        add_action( 'wp_ajax_nopriv_check_post_id', 'check_post_id_callback' );
    }

	/**
	 * @return CRED_Form_Builder
	 */
	public static function get_form_builder() {
		if ( self::$_form_builder_instance == null ) {
			self::$_form_builder_instance = CRED_Form_Builder::initialize();
		}

		return self::$_form_builder_instance;
	}

    /**
     * is_embedded if CRED_Admin class does not exist is embedded plugin
     *
     * @return bool
     */
    public static function is_embedded() {
        return (false === class_exists( 'CRED_Admin' ));
    }

	/**
	 * Main init hook
	 */
    public static function _init_() {
        global $wp_version, $post;

        // load textdomain
        new Toolset_Localization( 'wp-cred', CRED_LOCALE_PATH, 'wp-cred-%s' );

        // load help settings (once)
        self::$help = CRED_Loader::getVar( CRED_INI_PATH . "/help.ini.php" );
        // set up models and db settings
        CRED_Helper::prepareDB();
        // needed by others
        self::$settingsPage = admin_url( 'admin.php' ) . '?page=toolset-settings';
        //self::$settingsPage = admin_url('admin.php') . '?page=CRED_Settings';
        // localize forms, support for WPML
        CRED_Helper::localizeForms();
        // setup custom capabilities
        CRED_Helper::setupCustomCaps();
        // setup custom user caps
        CRED_Helper::setupCustomUserCaps();
        // setup extra admin hooks for other plugins
        CRED_Helper::setupExtraHooks();

        if ( is_admin() ) {
            if ( self::is_embedded() ) {
                self::initAdmin();
            } else {
                CRED_Admin::initAdmin();
            }
        } else {
            self::$_form_builder_instance = self::get_form_builder();

            //enqueue frontend script
            CRED_Asset_Manager::get_instance()->enqueue_frontend_assets();
        }

	    CRED_Frontend_File_Ajax_Upload_Manager::get_instance();

        // add form short code hooks and filters, to display forms on front end
        CRED_Helper::addShortcodesAndFilters();

        // handle Ajax calls
        CRED_Router::addCalls( array(
            'cred_skype_ajax' => array(
                'nopriv' => true,
                'callback' => array(__CLASS__, 'cred_skype_ajax')
            ),
            /* 'cred-ajax-tag-search' => array(
              'nopriv' => true,
              'callback' => array(__CLASS__, 'cred_ajax_tag_search')
              ), */
            'cred-ajax-delete-post' => array(
                'nopriv' => true,
                'callback' => array(__CLASS__, 'cred_ajax_delete_post')
            )
        ) );

        CRED_Router::addRoutes( 'cred', array(
            'Forms' => 0, // Forms controller
            'Posts' => 0, // Posts controller
            'Settings' => 0, // Settings controller
            'Generic_Fields' => 0  // Generic Fields controller
        ) );
        /* CRED_Router::addPages('cred', array(
          )); */
    }

	public static function _admin_init_() {
		//clear auto-drafting entries
        CRED_Helper::clearCREDAutoDrafts();
	}

    public static function initAdmin() {
        global $wp_version, $post;

        // add plugin menus
        // setup js, css assets
        CRED_Helper::setupAdmin();

        add_action( 'admin_menu', array(__CLASS__, 'admin_menu'), 20 );
    }

    public static function admin_menu() {
        if ( isset( $_GET['page'] ) && 'cred-embedded' == $_GET['page'] ) {
            $cap = 'manage_options';
            // DEVCYCLE this should not be in the tools.php menu at all
            add_submenu_page(
                    'admin.php', __( 'Embedded CRED', 'wp-cred' ), __( 'Embedded Toolset Forms', 'wp-cred' ), CRED_CAPABILITY, 'cred-embedded', 'cred_embedded_html' );
        }
        if ( isset( $_GET['page'] ) && 'cred-user-embedded' == $_GET['page'] ) {
            $cap = 'manage_options';
            // DEVCYCLE this should not be in the tools.php menu at all
            add_submenu_page(
                    'admin.php', __( 'User Embedded CRED', 'wp-cred' ), __( 'User Embedded Toolset Forms', 'wp-cred' ), CRED_CAPABILITY, 'cred-user-embedded', 'cred_user_embedded_html' );
        }
    }

    public static function media() {
        global $wp_version;
        /**
         * Fix compatibility Chirps theme
         */
        if (
                (
                (isset( $_GET['post_type'] ) && ($_GET['post_type'] == CRED_FORMS_CUSTOM_POST_NAME || $_GET['post_type'] == CRED_USER_FORMS_CUSTOM_POST_NAME)) ||
                (isset( $_GET['post'] ) && isset( $_GET['action'] ))
                ) &&
                class_exists( "G1_Theme_Admin" ) )
            remove_action( 'media_buttons', array(G1_Theme_Admin(), 'extend_gallery_settings') );
        /**
         * ###############################
         */
        // add media buttons for cred forms at editor
        add_action( 'media_buttons', array(__CLASS__, 'addFormsButton'), 20 );
    }

    // function to handle the media buttons associated to forms, like  Scaffold,Insert Shortcode, etc..
    public static function addFormsButton( $context ) {

        if ( !apply_filters( 'toolset_editor_add_form_buttons', true ) ) {
            return;
        }

        global $wp_version, $post;
        //static $add_only_once=0;

        if ( !isset( $post ) || empty( $post ) || !isset( $post->post_type ) ) {
            return '';
        }

        if ( $post->post_type == CRED_FORMS_CUSTOM_POST_NAME ) {

            $out = '';
            if ( 'content' == $context ) {
                $addon_buttons = array();
                $shortcode_but = '';
                $shortcode_but = CRED_Loader::tpl( 'insert-field-shortcode-button', array(
                            'help' => self::$help,
                            'help_target' => self::$help_link_target
                        ) );

                $shortcode2_but = '';
                $fields_model = CRED_Loader::get( 'MODEL/Fields' );
                $shortcode2_but = CRED_Loader::tpl( 'insert-generic-field-shortcode-button', array(
                            'gfields' => $fields_model->getTypesDefaultFields(),
                            'help' => self::$help,
                            'help_target' => self::$help_link_target
                        ) );


                $forms_model = CRED_Loader::get( 'MODEL/Forms' );
                $settings = $forms_model->getFormCustomField( $post->ID, 'form_settings' );
                $scaffold_but = '';
                $scaffold_but = CRED_Loader::tpl( 'scaffold-button', array(
                            'include_captcha_scaffold' => isset( $settings->form['include_captcha_scaffold'] ) ? $settings->form['include_captcha_scaffold'] : false,
                            'include_wpml_scaffold' => isset( $settings->form['include_wpml_scaffold'] ) ? $settings->form['include_wpml_scaffold'] : false,
                            'help' => self::$help,
                            'help_target' => self::$help_link_target
                        ) );

                $preview_but = '';

                $addon_buttons['scaffold'] = $scaffold_but;
                $addon_buttons['post_fields'] = $shortcode_but;
                $addon_buttons['generic_fields'] = $shortcode2_but;
                $addon_buttons['preview'] = $preview_but;


                $addon_buttons = apply_filters( 'cred_wpml_glue_generate_insert_button_block', $addon_buttons, $insert_after = 2 );
                $out = implode( '&nbsp;', array_values( $addon_buttons ) );
            }

            echo $out;
            
        } else if ( $post->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME ) {

			$out = '';
			if ( 'content' == $context ) {
				$addon_buttons = array();
				$shortcode_but = '';
				$shortcode_but = CRED_Loader::tpl( 'insert-user-field-shortcode-button', array(
							'help' => self::$help,
							'help_target' => self::$help_link_target
						) );

				$shortcode2_but = '';
				$fields_model = CRED_Loader::get( 'MODEL/Fields' );
				$shortcode2_but = CRED_Loader::tpl( 'insert-generic-field-shortcode-button', array(
							'gfields' => $fields_model->getTypesDefaultFields(),
							'help' => self::$help,
							'help_target' => self::$help_link_target
						) );

				$forms_model = CRED_Loader::get( 'MODEL/UserForms' );
				$settings = $forms_model->getFormCustomField( $post->ID, 'form_settings' );
				$scaffold_but = '';
				$scaffold_but = CRED_Loader::tpl( 'user-scaffold-button', array(
							'autogenerate_username_scaffold' => isset( $settings->form['autogenerate_username_scaffold'] ) ? $settings->form['autogenerate_username_scaffold'] : true,
							'autogenerate_nickname_scaffold' => isset( $settings->form['autogenerate_nickname_scaffold'] ) ? $settings->form['autogenerate_nickname_scaffold'] : true,
							'autogenerate_password_scaffold' => isset( $settings->form['autogenerate_password_scaffold'] ) ? $settings->form['autogenerate_password_scaffold'] : true,
							'include_captcha_scaffold' => isset( $settings->form['include_captcha_scaffold'] ) ? $settings->form['include_captcha_scaffold'] : false,
							'include_wpml_scaffold' => isset( $settings->form['include_wpml_scaffold'] ) ? $settings->form['include_wpml_scaffold'] : false,
							'help' => self::$help,
							'help_target' => self::$help_link_target
						) );

				$preview_but = '';
				ob_start();

				$preview_but = ob_get_clean();

				$addon_buttons['scaffold'] = $scaffold_but;
				$addon_buttons['post_fields'] = $shortcode_but;
				$addon_buttons['generic_fields'] = $shortcode2_but;
				$addon_buttons['preview'] = $preview_but;

				$addon_buttons = apply_filters( 'cred_wpml_glue_generate_insert_button_block', $addon_buttons, $insert_after = 2 );
				$out = implode( '&nbsp;', array_values( $addon_buttons ) );
			}

			echo $out;
			
        }
    }

    public static function route($path = '', $params = null, $raw = true) {
        return CRED_Router::getRoute( 'cred', $path, $params, $raw );
    }

    //Fix issue about https on frontend
    public static function routeAjax($action) {
        $url = admin_url( 'admin-ajax.php', 'http' ) . '?action=' . $action;
        //if is_ssl and url does not contains https
        if ( is_ssl() && strpos( $url, 'https://' ) === false ) {
            $url = str_replace( "http", "https", $url );
        }
        return $url;
    }

    /**
     * @deprecated since version 1.3.4
     * @global type $wpdb
     * @global type $sitepress
     * @global type $wp_version
     */
    // duplicated from wp ajax function
    public static function cred_ajax_tag_search() {
        global $wpdb;

        if ( isset( $_GET['tax'] ) ) {
            $taxonomy = sanitize_key( $_GET['tax'] );
            $tax = get_taxonomy( $taxonomy );
            if ( !$tax )
                wp_die( 0 );
            // possible issue here, anyway bypass for now
            /* if ( ! current_user_can( $tax->cap->assign_terms ) )
              wp_die( -1); */
        } else {
            wp_die( 0 );
        }

        $s = stripslashes( $_GET['q'] );

        $comma = _x( ',', 'tag delimiter', 'wp-cred' );
        if ( ',' !== $comma )
            $s = str_replace( $comma, ',', $s );
        if ( false !== strpos( $s, ',' ) ) {
            $s = explode( ',', $s );
            $s = $s[count( $s ) - 1];
        }
        $s = trim( $s );
        if ( strlen( $s ) < 2 )
            wp_die(); // require 2 chars for matching

        global $sitepress, $wp_version;
        $post_id = intval( $_GET['post_id'] );
        if ( isset( $sitepress ) && isset( $post_id ) ) {
            $post_type = get_post_type( $post_id );
            $post_language = $sitepress->get_element_language_details( $post_id, 'post_' . $post_type );
            $lang = $post_language->language_code;
            $current_language = $sitepress->get_current_language();
            //$sitepress->switch_lang($post_language->language_code, false);            
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
            $results = $wpdb->get_col( 
				$wpdb->prepare( 
					"SELECT t.name 
					FROM $wpdb->term_taxonomy AS tt 
					INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id 
					JOIN {$wpdb->prefix}icl_translations tr ON tt.term_taxonomy_id = tr.element_id 
					WHERE tt.taxonomy = %s 
					AND tr.language_code = %s 
					AND tr.element_type = %s 
					AND t.name LIKE %s", 
					array( $taxonomy, $lang, 'tax_' . $taxonomy, '%' . cred_wrap_esc_like( $s ) . '%' )
				) 
			);
            //$sitepress->switch_lang($current_language);
        } else {
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/187413931/comments
            $results = $wpdb->get_col( 
				$wpdb->prepare( 
					"SELECT t.name 
					FROM $wpdb->term_taxonomy AS tt 
					INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id 
					WHERE tt.taxonomy = %s 
					AND t.name LIKE %s", 
					array( $taxonomy, '%' . cred_wrap_esc_like( $s ) . '%' )
				) 
			);
        }

        echo join( $results, "\n" );
        wp_die();
    }

    public static function cred_ajax_delete_post() {
        CRED_Loader::get( "CONTROLLER/Posts" )->deletePost( $_GET, $_POST );
        wp_die();
    }

    // link CRED ajax call to wp-types ajax call (use wp-types for this)
    public static function cred_skype_ajax() {
        do_action( 'wp_ajax_wpcf_ajax' );
        wp_die();
    }

    public static function getPostAdminEditLink($post_id) {
        return admin_url( 'post.php' ) . '?action=edit&post=' . $post_id;
    }

    public static function getFormEditLink($form_id) {
        //return admin_url('post.php').'?action=edit&post='.$form_id;
        if ( self::is_embedded() )
            return admin_url( 'admin.php' ) . '?page=cred-embedded&cred_id=' . $form_id;
        else
            return get_edit_post_link( $form_id );
    }

    public static function getUserFormEditLink($form_id) {
        //return admin_url('post.php').'?action=edit&post='.$form_id;
        if ( self::is_embedded() )
            return admin_url( 'admin.php' ) . '?page=cred-user-embedded&cred_id=' . $form_id;
        else
            return get_edit_post_link( $form_id );
    }

    public static function getNewFormLink($abs = true) {
        return ($abs) ? admin_url( 'post-new.php' ) . '?post_type=' . CRED_FORMS_CUSTOM_POST_NAME : 'post-new.php?post_type=' . CRED_FORMS_CUSTOM_POST_NAME;
    }

    public static function getNewUserFormLink($abs = true) {
        return ($abs) ? admin_url( 'post-new.php' ) . '?post_type=' . CRED_USER_FORMS_CUSTOM_POST_NAME : 'post-new.php?post_type=' . CRED_USER_FORMS_CUSTOM_POST_NAME;
    }

}

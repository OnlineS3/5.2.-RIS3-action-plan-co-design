<?php

/**
 * Admin Helper Class
 *
 */
final class CRED_Admin_Helper
{

    static $import_messages;
    static $import_generic_error;

    public static function setupAdmin()
    {
        global $wp_version, $post;

        // determine current admin page
        CRED_Helper::getAdminPage(array(
            'post_type' => CRED_FORMS_CUSTOM_POST_NAME,
            'base' => 'admin.php',
            'pages' => array(
                'view-archives-editor',
                'views-editor',
                'CRED_Forms',
                'CRED_Fields',
                'CRED_Settings',
                'toolset-settings',
                'CRED_Help'
            )
        ));

        CRED_Helper::getAdminPage(array(
            'post_type' => CRED_USER_FORMS_CUSTOM_POST_NAME,
            'base' => 'admin.php',
            'pages' => array(
                'view-archives-editor',
                'views-editor',
                'CRED_User_Forms',
                'CRED_User_Fields',
                'CRED_Settings',
                'toolset-settings',
                'CRED_Help'
            )
        ));

        add_action('toolset_enqueue_scripts', array( __CLASS__, 'toolset_enqueue_scripts' ));

        add_filter('toolset_filter_register_menu_pages', array( __CLASS__, 'toolset_register_menu_pages' ), 50);

        add_filter('toolset_filter_register_export_import_section', array(
            __CLASS__,
            'register_export_import_section'
        ), 40);

        add_filter('toolset_filter_toolset_register_settings_section', array(
            __CLASS__,
            'register_settings_forms_section'
        ), 50);
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_wizard'
        ));
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_export'
        ), 20);
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_styling'
        ), 30);
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_other'
        ), 40);
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_recaptcha'
        ), 50);
        add_filter('toolset_filter_toolset_register_settings_forms_section', array(
            __CLASS__,
            'register_settings_forms_section_filter'
        ), 60);

        //Registering AJAX actions used in Toolset Forms settings section
        add_action('wp_ajax_cred_get_allowed_tags', array( __CLASS__, 'get_allowed_tags' ));
        add_action('wp_ajax_cred_set_allowed_tags', array( __CLASS__, 'set_allowed_tags' ));
        add_action('wp_ajax_cred_save_wizard_settings', array( __CLASS__, 'save_wizard_settings' ));
        add_action('wp_ajax_cred_save_export_settings', array( __CLASS__, 'save_export_settings' ));
        add_action('wp_ajax_cred_save_styling_settings', array( __CLASS__, 'save_styling_settings' ));
        add_action('wp_ajax_cred_save_other_settings', array( __CLASS__, 'save_other_settings' ));
        add_action('wp_ajax_cred_save_recaptcha_settings', array( __CLASS__, 'save_recaptcha_settings' ));

        self::$import_messages = null;
        self::$import_generic_error = false;
        add_action('wp_loaded', array( __CLASS__, 'import_on_form_submit' ));
        add_action('admin_notices', array( __CLASS__, 'import_notices_messages' ));

        CRED_Helper::setJSAndCSS();

        if ( version_compare($wp_version, '3.2', '>=') ) {
            if ( isset($post) && ( $post->post_type == CRED_FORMS_CUSTOM_POST_NAME ||
                    $post->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME )
            ) {
                remove_action('pre_post_update', 'wp_save_post_revision');
            }
        }

        /**
         * add debug information
         */
        add_filter('icl_get_extra_debug_info', array( __CLASS__, 'getExtraDebugInfo' ));

        /*
         * Add necessary JS for forms deletion handling
         */

        add_action("admin_footer", array( __CLASS__, "handlePostFormDeletionJS" ));
    }

    // add custom classes to our metaboxes, so they can be handled as needed
    public static function addMetaboxClasses( $classes )
    {
        array_push($classes, 'cred_related');

        return $classes;
    }

    public static function addMetaboxClasses2( $classes )
    {
        array_push($classes, 'cred_related');

        return $classes;
    }

    /**
     * add setting to debug
     * get setting functio
     */
    public static function getExtraDebugInfo( $extra_debug )
    {
        $sm = CRED_Loader::get('MODEL/Settings');
        $extra_debug[ 'CRED' ] = $sm->getSettings();
        if ( isset($extra_debug[ 'CRED' ][ 'recaptcha' ]) ) {
            unset($extra_debug[ 'CRED' ][ 'recaptcha' ]);
        }

        return $extra_debug;
    }

    public static function toolset_enqueue_scripts( $current_page )
    {
        switch ( $current_page ) {
            case 'toolset-settings':
                CRED_Loader::loadAsset('SCRIPT/cred_settings', 'cred_settings', true);
                do_action('toolset_enqueue_styles', array( 'wp-jquery-ui-dialog', 'toolset-dialogs-overrides-css' ));
                break;
        }
    }

    public static function toolset_register_menu_pages( $pages )
    {
        global $pagenow;
        $current_page = ( isset($_GET[ 'page' ]) ) ? sanitize_text_field($_GET[ 'page' ]) : '';
        $pages[] = array(
            'slug' => 'CRED_Forms',
            'menu_title' => __('Post Forms', 'wp-cred'),
            'page_title' => __('Post Forms', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'FormsMenuPage' ),
            'capability' => CRED_CAPABILITY
        );
        if (
            $pagenow == 'post-new.php' && isset($_GET[ 'post_type' ]) && $_GET[ 'post_type' ] == 'cred-form'
        ) {
            $new_post_form_url = CRED_CRED::getNewFormLink(false);
            $pages[] = array(
                'slug' => $new_post_form_url,
                'menu_title' => __('New Post Form', 'wp-cred'),
                'page_title' => __('New Post Form', 'wp-cred'),
                'callback' => '',
                'capability' => CRED_CAPABILITY
            );
        }
        if ( 'CRED_Fields' == $current_page ) {
            $pages[] = array(
                'slug' => 'CRED_Fields',
                'menu_title' => __('Toolset Forms Custom Fields', 'wp-cred'),
                'page_title' => __('Toolset Forms Custom Fields', 'wp-cred'),
                'callback' => array( 'CRED_Admin_Helper', 'FieldsMenuPage' ),
                'capability' => CRED_CAPABILITY
            );
        }
        $pages[] = array(
            'slug' => 'CRED_User_Forms',
            'menu_title' => __('User Forms', 'wp-cred'),
            'page_title' => __('User Forms', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'UserFormsMenuPage' ),
            'capability' => CRED_CAPABILITY
        );
        if (
            $pagenow == 'post-new.php' && isset($_GET[ 'post_type' ]) && $_GET[ 'post_type' ] == 'cred-user-form'
        ) {
            $new_user_form_url = CRED_CRED::getNewUserFormLink(false);
            $pages[] = array(
                'slug' => $new_user_form_url,
                'menu_title' => __('New User Form', 'wp-cred'),
                'page_title' => __('New User Form', 'wp-cred'),
                'callback' => '',
                'capability' => CRED_CAPABILITY
            );
        }
        if ( 'CRED_User_Fields' == $current_page ) {
            $pages[] = array(
                'slug' => 'CRED_User_Fields',
                'menu_title' => __('Toolset Forms User Fields', 'wp-cred'),
                'page_title' => __('Toolset Forms User Fields', 'wp-cred'),
                'callback' => array( 'CRED_Admin_Helper', 'UserFieldsMenuPage' ),
                'capability' => CRED_CAPABILITY
            );
        }

        CRED_Helper::$screens = array(
            'toplevel_page_CRED_Forms', //DEPRECATED
            'toolset_page_CRED_Forms',
            'toolset_page_CRED_User_Forms',
            'toolset_page_CRED_Fields',
            'toolset_page_CRED_User_Fields'
        );
        foreach ( CRED_Helper::$screens as $screen ) {
            add_action("load-" . $screen, array( __CLASS__, 'addScreenOptions' ));
        }

        return $pages;
    }

    // add screen options to table screens
    public static function addScreenOptions()
    {
        $screen = get_current_screen();
        if ( !is_array(CRED_Helper::$screens) || !in_array($screen->id, CRED_Helper::$screens) ) {
            return;
        }

        $args = array(
            'label' => __('Per Page', 'wp-cred'),
            'default' => 10,
            'option' => 'cred_per_page'
        );
        add_screen_option('per_page', $args);

        // instantiate table now to take care of column options
        // @todo why the user fields table is not instantiated here?
        switch ( $screen->id ) {
            case 'toplevel_page_CRED_Forms'://DEPRECATED
            case 'cred_page_CRED_Forms'://DEPRECATED
            case 'toolset_page_CRED_Forms':
                CRED_Loader::get('TABLE/Forms');
                break;
            case 'toplevel_page_CRED_User_Forms'://DEPRECATED
            case 'cred_page_CRED_User_Forms'://DEPRECATED
            case 'toolset_page_CRED_User_Forms':
                CRED_Loader::get('TABLE/UserForms');
                break;
            case 'cred_page_CRED_Fields'://DEPRECATED
            case 'toolset_page_CRED_Fields':
                CRED_Loader::get('TABLE/Custom_Fields');
                break;
        }
    }

    public static function FormsMenuPage()
    {
        CRED_Loader::load('VIEW/forms');
    }

    public static function UserFormsMenuPage()
    {
        CRED_Loader::load('VIEW/user_forms');
    }

    public static function FieldsMenuPage()
    {
        CRED_Loader::load('VIEW/custom_fields');
    }

    public static function UserFieldsMenuPage()
    {
        CRED_Loader::load('VIEW/custom_user_fields');
    }

    public static function ExportMenuSettings()
    {
        CRED_Loader::load('VIEW/export');
    }

    public static function ImportPostFormsSettings()
    {
        CRED_Loader::load('VIEW/import-post-forms');
    }

    public static function ImportUserFormsSettings()
    {
        CRED_Loader::load('VIEW/import-user-forms');
    }

    public static function register_export_import_section( $sections )
    {
        $sections[ 'cred' ] = array(
            'slug' => 'cred',
            'title' => __('Forms', 'wp-cred'),
            'icon' => '<i class="icon-cred-logo ont-icon-16"></i>',
            'items' => array(
                'export' => array(
                    'title' => __('Export Forms', 'wpv-views'),
                    'callback' => array( 'CRED_Admin_Helper', 'ExportMenuSettings' ),
                ),
                'import-post-forms' => array(
                    'title' => __('Import Post Forms', 'wpv-views'),
                    'callback' => array( 'CRED_Admin_Helper', 'ImportPostFormsSettings' ),
                ),
                'import-user-forms' => array(
                    'title' => __('Import User Forms', 'wpv-views'),
                    'callback' => array( 'CRED_Admin_Helper', 'ImportUserFormsSettings' ),
                )
            )
        );

        return $sections;
    }

    public static function import_on_form_submit()
    {
        if ( current_user_can(CRED_CAPABILITY) ) {
            $cred_import_file = null;
            if (
                isset($_POST[ 'import' ]) && $_POST[ 'import' ] == __('Import', 'wp-cred') && isset($_POST[ 'cred-import-nonce' ]) && wp_verify_nonce($_POST[ 'cred-import-nonce' ], 'cred-import-nonce')
            ) {
                if ( isset($_FILES[ 'import-file' ]) ) {
                    $cred_import_file = $_FILES[ 'import-file' ];
                    if ( $cred_import_file[ 'error' ] > 0 ) {
                        self::$import_generic_error = true;
                        $cred_import_file = null;
                    }
                }

                if (
                    $cred_import_file !== null && !empty($cred_import_file)
                ) {
                    $options = array();
                    if ( isset($_POST[ "cred-overwrite-forms" ]) ) {
                        $options[ 'overwrite_forms' ] = 1;
                    }
                    if ( isset($_POST[ "cred-overwrite-settings" ]) ) {
                        $options[ 'overwrite_settings' ] = 1;
                    }
                    if ( isset($_POST[ "cred-overwrite-custom-fields" ]) ) {
                        $options[ 'overwrite_custom_fields' ] = 1;
                    }
                    CRED_Loader::load('CLASS/XML_Processor');
                    self::$import_messages = CRED_XML_Processor::importFromXML($cred_import_file, $options);
                }
            }

            if (
                isset($_POST[ 'import' ]) && $_POST[ 'import' ] == __('Import', 'wp-cred') && isset($_POST[ 'cred-user-import-nonce' ]) && wp_verify_nonce($_POST[ 'cred-user-import-nonce' ], 'cred-user-import-nonce')
            ) {
                if ( isset($_FILES[ 'import-file' ]) ) {
                    $cred_import_file = $_FILES[ 'import-file' ];
                    if ( $cred_import_file[ 'error' ] > 0 ) {
                        self::$import_generic_error = true;
                        $cred_import_file = null;
                    }
                }

                if (
                    $cred_import_file !== null && !empty($cred_import_file)
                ) {
                    $options = array();
                    if ( isset($_POST[ "cred-overwrite-forms" ]) ) {
                        $options[ 'overwrite_forms' ] = 1;
                    }
                    if ( isset($_POST[ "cred-overwrite-settings" ]) ) {
                        $options[ 'overwrite_settings' ] = 1;
                    }
                    if ( isset($_POST[ "cred-overwrite-custom-fields" ]) ) {
                        $options[ 'overwrite_custom_fields' ] = 1;
                    }
                    CRED_Loader::load('CLASS/XML_Processor');
                    self::$import_messages = CRED_XML_Processor::importUserFromXML($cred_import_file, $options);
                }
            }
        }
    }

    public static function import_notices_messages()
    {
        $import_messages = self::$import_messages;
        $import_generic_error = self::$import_generic_error;
        $display_errors = array();
        $display_messages = array();
        if (
            $import_generic_error && isset($_POST[ 'type' ]) && $_POST[ 'type' ] == 'post_forms'
        ) {
            ?>
            <div class="message error"><p><?php echo __('Upload error or file not valid', 'wp-cred'); ?></p></div>
            <?php
        }
        if ( is_wp_error($import_messages) ) {
            ?>
            <div class="message error">
                <p><?php echo $import_messages->get_error_message($import_messages->get_error_code()); ?></p></div>
            <?php
        } elseif ( is_array($import_messages) ) {
            ?>
            <div class="message updated">
                <h3><?php echo __('Forms import summary:', 'wp-cred'); ?></h3>
                <ul>
                    <?php
                    /**
                     * show settings imported message
                     */
                    if ( $import_messages[ 'settings' ] ) {
                        printf('<li>%s</li>', __('General Settings Updated', 'wp-cred'));
                    }
                    ?>
                    <li><?php _e('Custom Fields Imported', 'wp-cred'); ?>
                        : <?php echo $import_messages[ 'custom_fields' ]; ?></li>
                    <li><?php _e('Forms overwritten', 'wp-cred'); ?> : <?php echo $import_messages[ 'updated' ]; ?></li>
                    <li><?php _e('Forms added', 'wp-cred'); ?> : <?php echo $import_messages[ 'new' ]; ?></li>
                </ul>
            </div>
            <?php if ( !empty($import_messages[ 'errors' ]) ) { ?>
                <div class="message error">
                    <ul>
                        <?php foreach ( $import_messages[ 'errors' ] as $err ) { ?>
                            <li><?php echo $err; ?></li>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <?php
        }
    }

    public static function SettingsSectionWizard()
    {
        CRED_Loader::load('VIEW/settings-wizard');
    }

    public static function SettingsSectionExport()
    {
        CRED_Loader::load('VIEW/settings-export');
    }

    public static function SettingsSectionStyling()
    {
        CRED_Loader::load('VIEW/settings-styling');
    }

    public static function SettingsSectionOther()
    {
        CRED_Loader::load('VIEW/settings-other');
    }

    public static function SettingsSectionRecaptcha()
    {
        CRED_Loader::load('VIEW/settings-recaptcha');
    }

    public static function SettingsSectionFilter()
    {
        CRED_Loader::load('VIEW/settings-filter');
    }

    public static function SettingsSectionUserForms()
    {
        CRED_Loader::load('VIEW/settings-user-forms');
    }

    public static function register_settings_forms_section( $sections )
    {
        $sections[ 'forms' ] = array(
            'slug' => 'forms',
            'title' => __('Forms', 'wpv-views')
        );

        return $sections;
    }

    public static function register_settings_forms_section_wizard( $sections )
    {
        $sections[ 'forms-wizard' ] = array(
            'slug' => 'forms-wizard',
            'title' => __('Forms Wizard', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionWizard' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_export( $sections )
    {
        $sections[ 'forms-export' ] = array(
            'slug' => 'forms-export',
            'title' => __('Export', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionExport' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_styling( $sections )
    {
        $sections[ 'forms-styling' ] = array(
            'slug' => 'forms-styling',
            'title' => __('Styling', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionStyling' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_other( $sections )
    {
        $sections[ 'forms-other' ] = array(
            'slug' => 'forms-other',
            'title' => __('Other', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionOther' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_recaptcha( $sections )
    {
        $sections[ 'forms-recaptcha' ] = array(
            'slug' => 'forms-recaptcha',
            'title' => __('reCAPTCHA API', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionRecaptcha' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_filter( $sections )
    {
        $sections[ 'forms-filter' ] = array(
            'slug' => 'forms-filter',
            'title' => __('Content Filter', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionFilter' )
        );

        return $sections;
    }

    public static function register_settings_forms_section_user_forms( $sections )
    {
        $sections[ 'forms-user-forms' ] = array(
            'slug' => 'forms-user-forms',
            'title' => __('Toolset User Forms', 'wp-cred'),
            'callback' => array( 'CRED_Admin_Helper', 'SettingsSectionUserForms' )
        );

        return $sections;
    }

    public static function get_allowed_tags()
    {
        if (
            !isset($_GET[ "wpnonce" ]) || !wp_verify_nonce($_GET[ "wpnonce" ], 'cred-manage-allowed-tags')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();
        ob_start();
        ?>
        <div class="toolset-dialog">
            <?php
            $_tags = wp_kses_allowed_html('post');

            if ( !isset($settings[ 'allowed_tags' ]) ) {
                $settings[ 'allowed_tags' ] = array();
                foreach ( $_tags as $key => $value ) {
                    $settings[ 'allowed_tags' ][ $key ] = isset($settings[ 'allowed_tags' ][ $key ]) ? $settings[ 'allowed_tags' ][ $key ] : 0;
                }
            }
            $allowed_tags = $settings[ 'allowed_tags' ];
            ?>
            <div style="border-bottom:solid 1px #ccc;padding-bottom:15px;">
                <label>
                    <input type="checkbox" id="js-cred-allowed-tags-select-all" size='50'
                           name="settings[allowed_tags][select_all]"/>
                    <strong><?php echo __('Select all', 'wp-cred'); ?></strong>
                </label>
            </div>
            <ul class="js-cred-allowed-tags-list" style="overflow:hidden">
                <?php
                foreach ( $_tags as $key => $value ) {
                    $checked = ( isset($settings[ 'allowed_tags' ][ $key ]) && $settings[ 'allowed_tags' ][ $key ] == 1 ) ? "checked" : "";
                    ?>
                    <li style="width:24%;float:left;">
                        <label>
                            <input <?php echo $checked; ?> type="checkbox" size='50'
                                                           name="settings[allowed_tags][<?php echo $key; ?>]"
                                                           value="<?php echo esc_attr($key); ?>"/>
                            <?php echo $key; ?>
                        </label>
                    </li>
                    <?php
                }
                ?>
            </ul>
        </div>
        <?php
        $content = ob_get_clean();
        $data = array(
            'content' => $content,
        );
        wp_send_json_success($data);
    }

    public static function set_allowed_tags()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }
        if (
            !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-manage-allowed-tags')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();
        if ( !isset($settings[ 'allowed_tags' ]) ) {
            $settings[ 'allowed_tags' ] = array();
        }
        $fields = isset($_POST[ 'fields' ]) ? $_POST[ 'fields' ] : array();
        $fields = array_map('esc_attr', $fields);
        $fields_data = array();
        foreach ( $fields as $tag ) {
            $fields_data[ $tag ] = 1;
        }
        $settings[ 'allowed_tags' ] = $fields_data;
        $settings_model->updateSettings($settings);
        ob_start();
        if ( sizeof($fields_data) > 0 ) {
            ?>
            <p class="js-cred-allowed-tags-summary-text">
                <?php
                _e('The following HTML tags are allowed:', 'wpv-views');
                ?>
            </p>
            <ul class="toolset-taglike-list">
                <?php foreach ( $fields_data as $enabled_tag => $enabled_val ): ?>
                    <li><?php echo esc_html($enabled_tag) ?></li>
                <?php endforeach; ?>
            </ul>
            <?php
        } else {
            ?>
            <p class="js-cred-allowed-tags-summary-text">
                <?php
                _e('No HTML tags have been selected.', 'wpv-views');
                ?>
            </p>
            <?php
        }
        $content = ob_get_clean();
        $settings_model->updateSettings($settings);
        $data = array(
            'content' => $content
        );
        wp_send_json_success($data);
    }

    public static function save_wizard_settings()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }
        if (
            !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-wizard-settings')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();

        $keys_to_check = array( 'wizard' );

        if (
            isset($_POST[ 'settings' ]) && !empty($_POST[ 'settings' ])
        ) {
            parse_str($_POST[ 'settings' ], $posted_settings);
        } else {
            $posted_settings = array();
        }

        foreach ( $keys_to_check as $key ) {
            if ( isset($posted_settings[ 'cred_' . $key ]) ) {
                $settings[ $key ] = $posted_settings[ 'cred_' . $key ];
            } else {
                $settings[ $key ] = 0;
            }
        }

        $settings_model->updateSettings($settings);
        wp_send_json_success();
    }

    public static function save_export_settings()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }
        if (
            !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-export-settings')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();

        $keys_to_check = array( 'export_settings', 'export_custom_fields' );

        if (
            isset($_POST[ 'settings' ]) && !empty($_POST[ 'settings' ])
        ) {
            wp_parse_str($_POST[ 'settings' ], $posted_settings);
            $posted_settings = cred_sanitize_array($posted_settings);
        } else {
            $posted_settings = array();
        }

        foreach ( $keys_to_check as $key ) {
            if ( isset($posted_settings[ 'cred_' . $key ]) ) {
                $settings[ $key ] = $posted_settings[ 'cred_' . $key ];
            } else {
                $settings[ $key ] = 0;
            }
        }

        $settings_model->updateSettings($settings);
        wp_send_json_success();
    }

    public static function save_styling_settings()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }

        if ( !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-styling-settings') ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }

        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();

        if ( isset($_POST[ 'settings' ]) && !empty($_POST[ 'settings' ]) ) {
            parse_str(sanitize_text_field($_POST[ 'settings' ]), $posted_settings);
        } else {
            $posted_settings = array();
        }

        //make sure to set cred_dont_load_cred_css value to 1 when not posted
        if ( !isset($posted_settings[ 'cred_dont_load_cred_css' ]) ) {
            $posted_settings[ 'cred_dont_load_cred_css' ] = 1;
        }
		
		$keys_to_check = array( 'dont_load_cred_css', 'dont_load_bootstrap_cred_css' );

        foreach ( $keys_to_check as $key ) {
            if ( isset($posted_settings[ 'cred_' . $key ]) ) {
                $settings[ $key ] = $posted_settings[ 'cred_' . $key ];
            } else {
                $settings[ $key ] = 0;
            }
        }
		
		// Keys to keep on legacy sites, only when they already exist in settings
		
		$keys_to_maybe_check = array( 'use_bootstrap' );
		
		foreach ( $keys_to_check as $key ) {
			if ( isset( $settings[ $key ] ) ) {
				if ( isset( $posted_settings[ 'cred_' . $key ] ) ) {
					$settings[ $key ] = $posted_settings[ 'cred_' . $key ];
				} else {
					$settings[ $key ] = 0;
				}
			}
        }

        $settings_model->updateSettings($settings);
        wp_send_json_success();
    }

    public static function save_other_settings()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }
        if (
            !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-other-settings')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }

        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();
		
		if (
            isset( $_POST[ 'settings' ] ) 
			&& ! empty( $_POST[ 'settings' ] )
        ) {
            parse_str( $_POST[ 'settings' ], $posted_settings );
        } else {
            $posted_settings = array();
        }

        $keys_to_check = array();

        foreach ( $keys_to_check as $key ) {
            if ( isset($posted_settings[ 'cred_' . $key ]) ) {
                $settings[ $key ] = sanitize_text_field( $posted_settings[ 'cred_' . $key ] );
            } else {
                $settings[ $key ] = 0;
            }
        }
		
		$keys_to_deprecate = array( 'syntax_highlight' );
		
		foreach ( $keys_to_check as $key ) {
            if ( isset( $settings[ $key ] ) ) {
                unset( $settings[ $key ] );
            }
        }

        $settings = apply_filters('cred_pe_general_settings_save', $settings, $posted_settings);

        $settings_model->updateSettings($settings);
        wp_send_json_success();
    }

    public static function save_recaptcha_settings()
    {
        if ( !current_user_can('manage_options') ) {
            $data = array(
                'type' => 'capability',
                'message' => __('You do not have permissions for that.', 'wpv-views')
            );
            wp_send_json_error($data);
        }
        if (
            !isset($_POST[ "wpnonce" ]) || !wp_verify_nonce($_POST[ "wpnonce" ], 'cred-recaptcha-settings')
        ) {
            $data = array(
                'type' => 'nonce',
                'message' => __('Your security credentials have expired. Please reload the page to get new ones.', 'wp-cred')
            );
            wp_send_json_error($data);
        }
        $settings_model = CRED_Loader::get('MODEL/Settings');
        $settings = $settings_model->getSettings();
        if ( !isset($settings[ 'recaptcha' ]) ) {
            $settings[ 'recaptcha' ] = array();
        }
        $keys_to_check = array( 'private_key', 'public_key' );

        if (
            isset($_POST[ 'settings' ]) && !empty($_POST[ 'settings' ])
        ) {
            parse_str($_POST[ 'settings' ], $posted_settings);
        } else {
            $posted_settings = array();
        }

        foreach ( $keys_to_check as $key ) {
            if ( isset($posted_settings[ 'cred_recaptcha_' . $key ]) ) {
                $settings[ 'recaptcha' ][ $key ] = $posted_settings[ 'cred_recaptcha_' . $key ];
            } else {
                $settings[ 'recaptcha' ][ $key ] = '';
            }
        }

        $settings_model->updateSettings($settings);
        wp_send_json_success();
    }

    public static function HelpMenuPage()
    {
        CRED_Loader::load('VIEW/help');
    }

    public static function DebugMenuPage()
    {
        $toolset_common_bootstrap = Toolset_Common_Bootstrap::getInstance();
        $toolset_common_sections = array(
            'toolset_debug'
        );
        $toolset_common_bootstrap->load_sections($toolset_common_sections);
    }

    // metabox placeholder for Module Manager plugin
    public static function addModManMetaBox( $form )
    {
        $key = ( $form->post_type == CRED_USER_FORMS_CUSTOM_POST_NAME ) ? _CRED_MODULE_MANAGER_USER_KEY_ : _CRED_MODULE_MANAGER_KEY_;
        $element = array( 'id' => $key . $form->ID, 'title' => $form->post_title, 'section' => $key );
        do_action('wpmodules_inline_element_gui', $element);
    }

    // placeholder
    public static function addFormContentMetaBox( $form )
    {

    }

	/**
	 * @param $form
	 * @param $args
     *
     * @deprecated 1.9.3 use CRED_Page_Extension_Form_Settings_Meta_Box
	 */
    public static function addFormSettingsMetaBox( $form, $args )
    {
        //Form Settings
	    $settings = $args[ 'args' ][ 'form_settings' ]->form;

	    $default_empty_action_post_type_label = esc_attr( __( '- - Select post type - -', 'wp-cred' ) );
	    $default_empty_action_post_label = esc_attr( __( '- - Select post - -', 'wp-cred' ) );

	    //Post Types
	    $post_types = get_post_types(array('public' => true, 'publicly_queryable' => true, 'show_in_nav_menus' => true), 'names');
	    $post_types = array_merge( array( '' => $default_empty_action_post_type_label ), $post_types );

	    $current_action_post = null;
	    ob_start();
	    ?>
	    <option value=""><?php echo $default_empty_action_post_label; ?></option>
        <?php
	    if (isset($settings['action_post'])
            && !empty($settings['action_post'])) {
		    $post = get_post( $settings['action_post'] );
		    $current_action_post = $post;
		    ?>
		    <option value="<?php echo esc_attr($post->ID); ?>"><?php echo $post->post_title; ?></option>
            <?php
	    }
	    $form_current_custom_post = ob_get_clean();

	    ob_start();
	    foreach ($post_types as $post_type_key => $post_type_value) {
            ?>
            <option value="<?php echo esc_attr($post_type_key); ?>" <?php selected( isset( $post ) && $post_type_value === $post->post_type ); ?>><?php echo $post_type_value; ?></option>
            <?php
	    }
	    $form_post_types = ob_get_clean();

	    //All Page List
        $page_query = new WP_Query(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
        ob_start();
        if ( $page_query->have_posts() ) {
            while ( $page_query->have_posts() ) {
                $page_query->the_post();
                ?>
                <option value="<?php esc_attr(the_ID()); ?>" <?php selected( isset($settings[ 'action_page' ]) && $settings[ 'action_page' ] == get_the_ID() ); ?>><?php the_title(); ?></option>
                <?php
            }
        }
        // just in case
        wp_reset_postdata();
        $form_action_pages = ob_get_clean();

        //enqueue template script
	    wp_enqueue_script( 'cred_form_settings_box' );
	    wp_localize_script( 'cred_form_settings_box', 'cred_form_settings_box', array(
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'form_current_action_post' => $current_action_post,
		    'has_current_action_post' => isset( $current_action_post ),
		    'form_current_action_post_id' => isset( $current_action_post ) ? esc_attr($current_action_post->ID) : null,
		    'form_current_action_post_title' => isset( $current_action_post ) ? $current_action_post->post_title : null,
		    'default_redirect_custom_post_min_posts_count_for_select2' => 15,
		    'default_empty_action_post_type' => $default_empty_action_post_type_label,
		    'default_empty_action_post' => $default_empty_action_post_label,
		    'default_select2_placeholder' => esc_attr( __( 'Type some characters..', 'wp-cred' ) ),
		    'form_type' => 'post'
	    ) );

	    //Print Template
	    echo CRED_Loader::tpl( 'form-settings-meta-box', array(
		    'form' => $form,
		    'settings' => $settings,
		    'post_types' => CRED_Loader::get( 'MODEL/Fields' )->getPostTypes(),
		    'form_post_types' => $form_post_types,
		    'form_current_custom_post' => $form_current_custom_post,
		    'default_empty_action_post_type' => $default_empty_action_post_type_label,
		    'default_empty_action_post' => $default_empty_action_post_label,
		    'form_action_pages' => $form_action_pages,
		    'help' => CRED_CRED::$help,
		    'help_target' => CRED_CRED::$help_link_target,
	    ) );
    }

	/**
	 * @param $form
	 * @param $args
	 *
	 * @deprecated 1.9.4 use CRED_Page_Extension_Form_Settings_Meta_Box
	 */
    public static function addUserFormSettingsMetaBox( $form, $args )
    {
	    //Form Settings
        $settings = $args[ 'args' ][ 'form_settings' ]->form;

	    //Post Types
	    $default_empty_action_post_type_label = esc_attr( __( '- - Select post type - -', 'wp-cred' ) );
	    $default_empty_action_post_label = esc_attr( __( '- - Select post - -', 'wp-cred' ) );

	    //Post Types
	    $post_types = get_post_types(array('public' => true, 'publicly_queryable' => true, 'show_in_nav_menus' => true), 'names');
	    $post_types = array_merge( array( '' => $default_empty_action_post_type_label ), $post_types );

	    $current_action_post = null;
	    ob_start();
	    ?>
        <option value=""><?php echo $default_empty_action_post_label; ?></option>
	    <?php
	    if (isset($settings['action_post'])
		    && !empty($settings['action_post'])) {
		    $post = get_post( $settings['action_post'] );
		    $current_action_post = $post;
		    ?>
            <option value="<?php echo esc_attr($post->ID); ?>"><?php echo $post->post_title; ?></option>
		    <?php
	    }
	    $form_current_custom_post = ob_get_clean();

	    ob_start();
	    foreach ($post_types as $post_type_key => $post_type_value) {
		    ?>
            <option value="<?php echo esc_attr($post_type_key); ?>" <?php selected( isset( $post ) && $post_type_value === $post->post_type ); ?>><?php echo $post_type_value; ?></option>
		    <?php
	    }
	    $form_post_types = ob_get_clean();

	    //All Page List
	    $page_query = new WP_Query(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ));
        ob_start();
        if ( $page_query->have_posts() ) {
            while ( $page_query->have_posts() ) {
                $page_query->the_post();
                ?>
                <option value="<?php the_ID() ?>" <?php selected( isset($settings[ 'action_page' ]) && $settings[ 'action_page' ] == get_the_ID() ); ?>><?php the_title(); ?></option>
                <?php
            }
        }
        global $wp_roles;
        // just in case
        wp_reset_postdata();
        $form_action_pages = ob_get_clean();

	    $selected_user_roles = array();
	    if ( isset( $settings['user_role'] ) && ! empty( $settings['user_role'] ) ) {
		    $selected_user_roles = json_decode( $settings['user_role'], true );
		    if ( is_array( $selected_user_roles ) ) {
			    array_filter( $selected_user_roles );
		    }
	    }

	    //enqueue template script
	    wp_enqueue_script( 'cred_form_settings_box' );
	    wp_localize_script( 'cred_form_settings_box', 'cred_form_settings_box', array(
		    'ajaxurl' => admin_url( 'admin-ajax.php' ),
		    'form_current_action_post' => $current_action_post,
		    'has_current_action_post' => isset( $current_action_post ),
		    'form_current_action_post_id' => isset( $current_action_post ) ? esc_attr($current_action_post->ID) : null,
		    'form_current_action_post_title' => isset( $current_action_post ) ? $current_action_post->post_title : null,
		    'default_redirect_custom_post_min_posts_count_for_select2' => 15,
		    'default_empty_action_post_type' => $default_empty_action_post_type_label,
		    'default_empty_action_post' => $default_empty_action_post_label,
		    'default_select2_placeholder' => esc_attr( __( 'Type some characters..', 'wp-cred' ) ),
		    'settings_form_type' =>  $settings['type'],
		    'user_roles' => $wp_roles->roles,
		    'selected_user_roles' => $selected_user_roles,
            'form_type' => 'user'
	    ) );

        echo CRED_Loader::tpl('user-form-settings-meta-box', array(
            'form' => $form,
            'settings' => $settings,
	        'post_types' => CRED_Loader::get( 'MODEL/Fields' )->getPostTypes(),
	        'form_post_types' => $form_post_types,
	        'form_current_custom_post' => $form_current_custom_post,
	        'default_empty_action_post_type' => $default_empty_action_post_type_label,
	        'default_empty_action_post' => $default_empty_action_post_label,
            'form_action_pages' => $form_action_pages,
            'user_roles' => $wp_roles->roles,
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    /**
     * @deprecated since 1.8
     *
     * @param type $form
     * @param type $args
     */
    public static function addPostTypeMetaBox( $form, $args )
    {
        $settings = $args[ 'args' ][ 'form_settings' ];
        echo CRED_Loader::tpl('post-type-meta-box', array(
            'post_types' => CRED_Loader::get('MODEL/Fields')->getPostTypes(),
            'settings' => $settings,
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    public static function addNotificationMetaBox( $form, $args )
    {
        $notification = $args[ 'args' ][ 'notification' ];
        $enable = ( isset($notification->enable) && $notification->enable ) ? 1 : 0;
        $notts = isset($notification->notifications) ? (array)$notification->notifications : array();

        echo CRED_Loader::tpl('notification-meta-box', array(
            'form' => $form,
            'enable' => $enable,
            'notifications' => $notts,
            'enableTestMail' => !CRED_Helper::$currentPage->isCustomPostNew,
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    public static function addNotificationMetaBox2( $form, $args )
    {
        $notification = $args[ 'args' ][ 'notification' ];
        $enable = ( isset($notification->enable) && $notification->enable ) ? 1 : 0;
        $notts = isset($notification->notifications) ? (array)$notification->notifications : array();

        echo CRED_Loader::tpl('notification-user-meta-box', array(
            'form' => $form,
            'enable' => $enable,
            'notifications' => $notts,
            'enableTestMail' => !CRED_Helper::$currentPage->isCustomPostNew,
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    /**
     * @deprecated since version 1.8
     *
     * @param type $form
     * @param type $args
     */
    public static function addExtraAssetsMetaBox( $form, $args )
    {
        $extra = $args[ 'args' ][ 'extra' ];

        echo CRED_Loader::tpl('extra-meta-box', array(
            'css' => isset($extra->css) ? $extra->css : '',
            'js' => isset($extra->js) ? $extra->js : '',
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    /**
     * @added since version 1.8
     *
     * @param type $form
     * @param type $args
     */
    public static function addExtraCSSMetaBox( $form, $args )
    {
        $extra = $args[ 'args' ][ 'extra' ];
        echo CRED_Loader::tpl('extra-css-meta-box', array(
            'css' => isset($extra->css) ? $extra->css : '',
            'js' => isset($extra->js) ? $extra->js : '',
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    /**
     * @added since 1.8
     *
     * @param type $form
     * @param type $args
     */
    public static function addExtraJSMetaBox( $form, $args )
    {
        $extra = $args[ 'args' ][ 'extra' ];

        echo CRED_Loader::tpl('extra-js-meta-box', array(
            'css' => isset($extra->css) ? $extra->css : '',
            'js' => isset($extra->js) ? $extra->js : '',
            'help' => CRED_CRED::$help,
            'help_target' => CRED_CRED::$help_link_target
        ));
    }

    public static function addMessagesMetaBox( $form, $args )
    {
        $extra = $args[ 'args' ][ 'extra' ];
        if ( isset($extra->messages) ) {
            $messages = $extra->messages;
        } else {
            $messages = false;
        }
        $model = CRED_Loader::get('MODEL/Forms');
        if ( !$messages ) {
            $messages = $model->getDefaultMessages();
        }

        echo CRED_Loader::tpl('text-settings-meta-box', array(
            'messages' => $messages,
            'descriptions' => $model->getDefaultMessageDescriptions()
        ));
    }

    public static function addMessagesMetaBox2( $form, $args )
    {
        $extra = $args[ 'args' ][ 'extra' ];
        if ( isset($extra->messages) ) {
            $messages = $extra->messages;
        } else {
            $messages = false;
        }
        $model = CRED_Loader::get('MODEL/UserForms');
        if ( !$messages ) {
            $messages = $model->getDefaultMessages();
        }

        echo CRED_Loader::tpl('text-settings-meta-box', array(
            'messages' => $messages,
            'descriptions' => $model->getDefaultMessageDescriptions()
        ));
    }

    public static function addCredAccessMessagesMetaBox( $form, $args )
    {
        global $wpcf_access;
        $is_access_active = ( isset($wpcf_access) && !empty($wpcf_access) );

	    $form_saved =
		    (
			    $args["args"]["form_settings"]->post["post_type"] != null ||
			    (
				    isset( $args["args"]["form_settings"]->form["user_role"] ) &&
				    (
					    ! empty($args["args"]["form_settings"]->form["user_role"]) &&
					    $args["args"]["form_settings"]->form["user_role"] != "[]"
				    )
			    )
		    );

        echo CRED_Loader::tpl('text-access-meta-box', array(
            'is_access_active' => $is_access_active,
            'form_type' => @$form->post_type,
            'form_saved'=> $form_saved
        ));
    }

	public static function add_how_to_display_meta_box( $form, $args )
	{
		echo CRED_Loader::tpl('how-to-display-meta-box', array());
	}

	public static function addSaveMetaBox( $form, $args )
    {
        echo CRED_Loader::tpl('save-form-meta-box', array(
            'delete_link' => do_shortcode("[cred_delete_post_link class='submitdelete deletion' text='Move to Trash' action='delete' message='" . __("Are you sure you want to delete this form?", "wp-cred") . "' message_show='1']"),
        ));
    }

    public static function handlePostFormDeletionJS()
    {
        if ( isset($_GET[ "action" ]) && $_GET[ "action" ] == "edit" ) {
            $js = "<script>jQuery(document).on('cred-post-delete-link-completed', function(){ if(window.pagenow && window.pagenow == 'cred-user-form'){ document.location = '" . admin_url("admin.php?page=CRED_User_Forms&form_deleted=1") . "'; } else { document.location = '" . admin_url("admin.php?page=CRED_Forms&form_deleted=1") . "'; } });</script>";
            echo $js;
        }
    }
}

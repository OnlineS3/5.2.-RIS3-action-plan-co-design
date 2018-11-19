<?php
/*
Plugin Name: Multi Rating Pro
Plugin URI: https://multiratingpro.com
Description: A powerful rating system and review plugin for WordPress, with advanced features.
Version: 5.5
Author: Daniel Powney
Author URI: https://danielpowney.com
License: GPL2

Text Domain: multi-rating-pro
Domain Path: languages

Multi Rating Pro is distributed under the terms of the GNU General Public License as
published by the Free Software Foundation, either version 2 of the License, or any
later version.

Multi Rating Pro is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.
<http://www.gnu.org/licenses/>
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * MRP_Multi_Rating plugin main class
 *
 * @author dpowney
 */
class MRP_Multi_Rating {

	/** Singleton *************************************************************/

	/**
	 * @var MRP_Multi_Rating The one true MRP_Multi_Rating
	 */
	private static $instance;

	/**
	 * Settings instance variable
	 */
	public $settings = null;

	/**
	 * Post metabox instance variable
	 */
	public $post_metabox = null;

	/**
	 * Comments system integration instance variable
	 */
	public $comments = null;

	/**
	 * Rating form instance variable
	 */
	public $rating_form = null;

	/**
	 * Constants
	 */
	const
	VERSION 										= '5.5',
	ID 												= 'mrp_',

	// tables
	RATING_SUBJECT_TBL_NAME 						= 'mrp_rating_subject',
	RATING_ITEM_TBL_NAME 							= 'mrp_rating_item',
	CUSTOM_FIELDS_TBL_NAME							= 'mrp_custom_fields',
	RATING_FORM_TBL_NAME							= 'mrp_rating_form',
	RATING_FORM_ITEM_TBL_NAME						= 'mrp_rating_form_item',
	RATING_ITEM_ENTRY_TBL_NAME						= 'mrp_rating_item_entry',
	RATING_ITEM_ENTRY_VALUE_TBL_NAME 				= 'mrp_rating_item_entry_value',
	RATING_RESULT_TBL_NAME							= 'mrp_rating_result',

	// settings
	CUSTOM_TEXT_SETTINGS 							= 'mrp_custom_text_settings',
	STYLES_SETTINGS 								= 'mrp_style_settings',
	GENERAL_SETTINGS 								= 'mrp_general_settings',
	ADVANCED_SETTINGS								= 'mrp_advanced_settings',
	EMAIL_SETTINGS									= 'mrp_email_settings',
	AUTO_PLACEMENT_SETTINGS							= 'mrp_auto_placement_settings',
	LICENSE_SETTINGS								= 'mrp_license',

	// options
	CUSTOM_CSS_OPTION 								= 'mrp_custom_css',
	RATING_RESULTS_POSITION_OPTION					= 'mrp_rating_results_position',
	RATING_FORM_POSITION_OPTION 					= 'mrp_rating_form',
	COMMENT_FORM_MULTI_RATING_OPTION 				= 'mrp_comment_form_multi_rating',
	COMMENT_TEXT_MULTI_RATING_OPTION 				= 'mrp_comment_text_multi_rating',
	COMMENT_SHOW_OVERALL_RATING_OPTION				= 'mrp_comment_show_overall_rating',
	COMMENT_SHOW_TITLE_OPTION						= 'mrp_comment_show_title',
	COMMENT_SHOW_RATING_ITEMS_OPTION				= 'mrp_comment_show_rating_items',
	COMMENT_SHOW_CUSTOM_FIELDS_OPTION				= 'mrp_comment_show_custom_fields',
	RATING_FORM_TITLE_TEXT_OPTION 					= 'mrp_rating_form_title_text',
	RATING_RESULTS_LIST_TITLE_TEXT_OPTION 			= 'mrp_rating_results_list_title_text',
	USER_RATING_RESULTS_TITLE_TEXT_OPTION 			= 'mrp_user_rating_results_title_text',
	USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION		= 'mrp_user_ratings_dashboard_title_text',
	RATING_ENTRIES_LIST_TITLE_TEXT_OPTION			= 'mrp_rating_entries_list_title_text',
	POST_TYPES_OPTION								= 'mrp_post_types',
	SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION			= 'mrp_submit_rating_form_button_text',
	UPDATE_RATING_FORM_BUTTON_TEXT_OPTION			= 'mrp_update_rating_form_button_text',
	DELETE_RATING_FORM_BUTTON_TEXT_OPTION			= 'mrp_delete_rating_form_button_text',
	FILTER_BUTTON_TEXT_OPTION						= 'mrp_filter_button_text',
	FILTER_LABEL_TEXT_OPTION						= 'mrp_filter_label_text',
	SUBMIT_RATING_SUCCESS_MESSAGE_OPTION 			= 'mrp_save_rating_success_message',
	UPDATE_RATING_SUCCESS_MESSAGE_OPTION			= 'mrp_update_rating_success_message',
	DELETE_RATING_SUCCESS_MESSAGE_OPTION			= 'mrp_delete_rating_success_message',
	NO_RATING_RESULTS_TEXT_OPTION					= 'mrp_no_rating_results_text',
	VERSION_OPTION									= 'mrp_version_option',
	HIDE_RATING_FORM_SUBMIT_OPTION					= 'mrp_hide_rating_form',
	FILTERED_POSTS_OPTION							= 'mrp_filtered_posts',
	FILTERED_PAGE_URLS_OPTION						= 'mrp_filtered_page_urls',
	FILTERED_CATEGORIES_OPTION						= 'mrp_filtered_categories',
	POST_FILTER_TYPE_OPTION							= 'mrp_post_filter_type',
	FILTER_EXCLUDE_HOME_PAGE						= 'mrp_filter_exclude_home_page',
	FILTER_EXCLUDE_SEARCH_RESULTS					= 'mrp_filter_exclude_search_results',
	FILTER_EXCLUDE_ARCHIVE_PAGES					= 'mrp_filter_exclude_archive_pages',
	PAGE_URL_FILTER_TYPE_OPTION						= 'mrp_page_url_filter_type',
	CATEGORY_FILTER_TYPE_OPTION						= 'mrp_category_filter_type',
	DEFAULT_RATING_FORM_OPTION						= 'mrp_default_rating_form',
	STAR_RATING_COLOUR_OPTION						= 'mrp_star_rating_colour',
	STAR_RATING_HOVER_COLOUR_OPTION					= 'mrp_star_rating_hover_colour',
	EXISTING_RATING_MESSAGE_OPTION					= 'mrp_submit_once_validation_error_message',
	ALLOW_ANONYMOUS_RATINGS_OPTION					= 'mrp_allow_anonymous_ratings',
	ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION 	= 'mrp_allow_anonymous_ratings_error_message',
	FIELD_REQUIRED_ERROR_MESSAGE_OPTION				= 'mrp_required_error_message',
	RATING_RESULT_REVIEWS_TITLE_TEXT_OPTION			= 'mrp_rating_result_reviews_title_text',
	GENERATE_MICRODATA_OPTION						= 'mrp_generate_rich_snippets',
	DEFAULT_RATING_RESULT_TYPE_OPTION				= 'mrp_default_rating_result_type',
	STAR_RATING_OUT_OF_OPTION						= 'mrp_star_rating_out_of',
	RATING_RESULT_DECIMAL_PLACES_OPTION				= 'mrp_rating_result_decimal_places',
	KEEP_ANY_TRAILING_ZEROS							= 'mrp_keep_any_trailing_zeros',
	SORT_WP_QUERY_OPTION							= 'mrp_sort_wp_query',
	SORT_COMMENT_QUERY_OPTION						= 'mrp_sort_comment_query',
	DO_ACTIVATION_REDIRECT_OPTION					= 'mrp_do_activiation_redirect',
	LOAD_ICON_FONT_LIBRARY_OPTION					= 'mrp_include_font_awesome',
	ICON_FONT_LIBRARY_OPTION						= 'mrp_font_awesome_version',
	COMMENT_FORM_OPTIONAL_RATING_OPTION				= 'mrp_comment_form_include_rating',
	COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION		= 'mrp_comment_form_default_include_rating_checkbox',
	SHOW_NAME_INPUT_OPTION							= 'mrp_show_name_input',
	SHOW_EMAIL_INPUT_OPTION							= 'mrp_show_email_input',
	SHOW_COMMENT_TEXTAREA_OPTION					= 'mrp_show_comment_textarea',
	SHOW_TITLE_INPUT_OPTION							= 'mrp_show_title_input',
	ALLOW_USER_UPDATE_OR_DELETE_RATING				= 'mrp_allow_user_update_or_delete_rating',
	AUTOMATICALLY_APPROVE_RATINGS					= 'mrp_automatically_approve_ratings',
	RATING_AWAITING_MODERATION_MESSAGE_OPTION		= 'mrp_rating_awaiting_moderation_message',
	USE_CUSTOM_STAR_IMAGES							= 'mrp_use_custom_star_images',
	CUSTOM_FULL_STAR_IMAGE							= 'mrp_custom_full_star_img',
	CUSTOM_HALF_STAR_IMAGE							= 'mrp_custom_half_star_img',
	CUSTOM_EMPTY_STAR_IMAGE							= 'mrp_custom_empty_star_img',
	CUSTOM_HOVER_STAR_IMAGE							= 'mrp_custom_hover_star_img',
	CUSTOM_STAR_IMAGE_WIDTH							= 'mrp_custom_star_img_width',
	CUSTOM_STAR_IMAGE_HEIGHT						= 'mrp_custom_star_img_height',
	SAVE_RATING_RESTRICTION_TYPES_OPTION			= 'mrp_save_rating_restriction_types',
	SAVE_RATING_RESTRICTION_HOURS_OPTION			= 'mrp_save_rating_restriction_hours',
	SAVE_RATING_RESTRICTION_ERROR_MESSAGE_OPTION 	= 'mrp_save_rating_restriction_error_message',
	AUTO_PURGE_CACHE_OPTION							= 'mrp_auto_purge_cache',
	DISABLE_CUSTOM_TEXT_OPTION						= 'mrp_disable_custom_text',

	RATING_APPROVED_EMAIL_ENABLE					= 'mrp_approved_email_enable',
	RATING_APPROVED_EMAIL_TO_EMAILS					= 'mrp_approved_email_to_emails',
	RATING_APPROVED_EMAIL_TO_POST_AUTHOR			= 'mrp_approved_email_to_post_author',
	RATING_APPROVED_EMAIL_TO_SUBMITTER				= 'mrp_approved_email_to_submitter',
	RATING_APPROVED_EMAIL_FROM_NAME					= 'mrp_approved_email_from_name',
	RATING_APPROVED_EMAIL_FROM_EMAIL				= 'mrp_approved_email_from_email',
	RATING_APPROVED_EMAIL_SUBJECT					= 'mrp_approved_email_subject',
	RATING_APPROVED_EMAIL_HEADING					= 'mrp_approved_email_heading',
	RATING_APPROVED_EMAIL_TEMPLATE					= 'mrp_approved_email_template',

	RATING_MODERATION_EMAIL_ENABLE					= 'mrp_moderation_email_enable',
	RATING_MODERATION_EMAIL_TO_EMAILS				= 'mrp_moderation_email_to_emails',
	RATING_MODERATION_EMAIL_FROM_NAME				= 'mrp_moderation_email_from_name',
	RATING_MODERATION_EMAIL_FROM_EMAIL				= 'mrp_moderation_email_from_email',
	RATING_MODERATION_EMAIL_SUBJECT					= 'mrp_moderation_email_subject',
	RATING_MODERATION_EMAIL_HEADING					= 'mrp_moderation_email_heading',
	RATING_MODERATION_EMAIL_TEMPLATE				= 'mrp_moderation_email_template',

	HIDE_POST_META_BOX_OPTION						= 'mrp_default_hide_post_meta_box',
	TEMPLATE_STRIP_NEWLINES_OPTION					= 'mrp_template_strip_newlines',
	DISALLOWED_USER_ROLES_RATINGS_OPTION			= 'mrp_disallowed_user_roles_ratings',
	DISALLOWED_USER_ROLES_RATINGS_ERROR_MESSAGE_OPTION = 'mrp_disallowed_user_roles_ratings_error_message',
	ERROR_MESSAGE_COLOUR_OPTION						= 'mrp_error_message_colour',
	RATING_ALGORITHM_OPTION							= 'mrp_rating_algorithm',
	USE_MINIFIED_ASSETS_OPTION						= 'mrp_use_minified_assets',
	DISABLE_STYLES_OPTION							= 'mrp_disable_styles',

	LICENSE_KEY_OPTION								= 'mrp_license',
	LICENSE_STATUS_OPTION							= 'mrp_license_status',

	// pages
	SETTINGS_PAGE_SLUG								= 'mrp_settings',
	RATING_ITEMS_PAGE_SLUG							= 'mrp_rating_items',
	RATING_RESULTS_PAGE_SLUG						= 'mrp_rating_results',
	RATING_ENTRIES_PAGE_SLUG						= 'mrp_rating_entries',
	ADD_NEW_RATING_ITEM_PAGE_SLUG					= 'mrp_add_new_rating_item',
	RATING_FORMS_PAGE_SLUG							= 'mrp_rating_forms',
	ADD_NEW_RATING_FORM_PAGE_SLUG					= 'mrp_add_new_rating_form',
	REPORTS_PAGE_SLUG								= 'mrp_reports',
	ABOUT_PAGE_SLUG									= 'mrp_about',
	TOOLS_PAGE_SLUG									= 'mrp_tools',
	FILTERS_PAGE_SLUG								= 'mrp_filters',
	EDIT_RATING_PAGE_SLUG							= 'mrp_edit_rating',
	CUSTOM_FIELDS_PAGE_SLUG							= 'mrp_custom_fields',
	ADD_NEW_CUSTOM_FIELD_PAGE_SLUG					= 'mrp_add_new_custom_field',

	// tabs
	GENERAL_SETTINGS_TAB							= 'mrp_general_settings',
	FILTER_SETTINGS_TAB								= 'mrp_filter_settings',
	CUSTOM_TEXT_SETTINGS_TAB						= 'mrp_text_settings',
	EMAIL_SETTINGS_TAB								= 'mrp_email_settings',
	ADVANCED_SETTINGS_TAB							= 'mrp_advanced_settings',
	STYLES_SETTINGS_TAB								= 'mrp_style_settings',
	AUTO_PLACEMENT_SETTINGS_TAB						= 'mrp_auto_placement_settings',
	LICENSES_SETTINGS_TAB							= 'mrp_licenses',

	// TODO
	ENTRIES_REPORT_TAB								= 'mrp_entries_report',

	// values
	TEXTAREA_NEWLINE	 							= '&#13;&#10;',
	WHITELIST_VALUE									= 'whitelist',
	BLACKLIST_VALUE									= 'blacklist',
	SCORE_RESULT_TYPE								= 'score',
	STAR_RATING_RESULT_TYPE							= 'star_rating',
	PERCENTAGE_RESULT_TYPE							= 'percentage',
	DO_NOT_SHOW										= 'do_not_show',
	TABLE_VIEW_FORMAT								= 'table',
	INLINE_VIEW_FORMAT								= 'inline',
	SELECT_ELEMENT									= 'select',
	TITLE_REVIEW_FIELD_ID							= 1,
	NAME_REVIEW_FIELD_ID							= 2,
	EMAIL_REVIEW_FIELD_ID							= 3,
	COMMENT_REVIEW_FIELD_ID							= 4,

	// post metabox
	RATING_FORM_ID_POST_META						= 'mrp_rating_form_id',
	ALLOW_ANONYMOUS_POST_META						= 'mrp_allow_anonymous',
	RATING_FORM_POSITION_POST_META					= 'mrp_rating_form_position',
	RATING_RESULTS_POSITION_POST_META				= 'mrp_rating_results_position',
	RATING_RESULTS_POST_META						= 'mrp_rating_result',

	// cookies
	POST_SAVE_RATING_COOKIE							= 'mrp_post_save_rating';

	/**
	 *
	 * @return MRP_Multi_Rating
	 */
	public static function instance() {

		if ( ! isset( self::$instance )
				&& ! ( self::$instance instanceof MRP_Multi_Rating ) ) {

			self::$instance = new MRP_Multi_Rating;

			add_action( 'admin_enqueue_scripts', array( self::$instance, 'assets' ) );

			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) { // Add menus and pages

				add_action( 'admin_menu', array( self::$instance, 'add_admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'admin_assets' ) );
				add_action( 'admin_init', array( self::$instance, 'redirect_about_page' ) );

			} else {
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'assets' ) );
			}

			add_action( 'widgets_init', array( self::$instance, 'register_widgets' ) );
			add_action( 'init', array( self::$instance, 'load_textdomain' ) );

			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->setup_ajax_callbacks();

			self::$instance->settings = new MRP_Settings();
			self::$instance->comments = new MRP_Comments();
			self::$instance->rating_form = new MRP_Rating_Form();

			// Add custom header styles
			$disable_styles = self::instance()->settings->style_settings[MRP_Multi_Rating::DISABLE_STYLES_OPTION];
			if ( ! $disable_styles ) {
				add_action( 'wp_head', array( self::$instance, 'mrp_head') );
			}

			if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {

				self::$instance->post_metabox = new MRP_Post_Metabox();

				add_filter( 'hidden_meta_boxes', 'mrp_default_hidden_meta_boxes', 10, 2);
				add_action( 'admin_init', array( self::$instance, 'plugin_updater' ) );

				add_action( 'deleted_post', 'mrp_deleted_post' );
				add_action( 'delete_user', 'mrp_delete_user', 11, 2 );

				add_action( 'admin_notices', array( self::$instance->settings, 'sl_admin_notices' ) );

			}

			add_image_size( 'Multi Rating Pro Thumbnail', 100, 100, true );

			$advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS );

			// add filters to enable bayesian averages if necessary
			if ( $advanced_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION] == 'bayesian_average' ) {
				add_filter( 'mrp_rating_result_query', 'mrp_bayesian_rating_result_query', 10, 2 );
				add_filter( 'mrp_rating_results_query_select', 'mrp_bayesian_rating_results_query_select', 10, 2 );
				add_filter( 'mrp_rating_results_query_from', 'mrp_bayesian_rating_results_query_from', 10, 2 );
			}

			// add filters to sort posts in WP main loop by highest rated
			if ( $advanced_settings[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] ) {
				add_filter( 'posts_orderby','mrp_posts_orderby' );
				add_filter( 'posts_join', 'mrp_posts_join' );
				add_filter( 'posts_groupby', 'mrp_posts_groupby' );
				add_filter( 'posts_fields', 'mrp_posts_fields' );
			}
		}

		return MRP_Multi_Rating::$instance;
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access private
	 * @since 5.2.5
	 * @return void
	 */
	private function setup_constants() {

		// Plugin Folder Path.
		if ( ! defined( 'MRP_PLUGIN_DIR' ) ) {
			define( 'MRP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MRP_PLUGIN_URL' ) ) {
			define( 'MRP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MRP_PLUGIN_VERSION' ) ) {
			define( 'MRP_PLUGIN_VERSION', '5.5' );
		}

	}


	/**
	 * Setup AJAX callback functions
	 */
	function setup_ajax_callbacks() {

		// move these closer to files where their actions are?
		if ( is_admin() ) {
			add_action( 'wp_ajax_save_custom_field_table_column', array( 'MRP_Custom_Fields_Table', 'save_custom_field_table_column' ) );
			add_action( 'wp_ajax_update_entry_status', array( 'MRP_Rating_Entry_Table', 'update_entry_status' ) );
			add_action( 'wp_ajax_get_terms_by_taxonomy', 'mrp_get_terms_by_taxonomy' );
			add_action( 'wp_ajax_change_filter_type', 'mrp_change_filter_type' );
			add_action( 'wp_ajax_save_filter', 'mrp_save_filter' );
			add_action( 'wp_ajax_delete_filter', 'mrp_delete_filter' );
			add_action( 'wp_ajax_add_filter', 'mrp_add_filter' );
			add_action( 'wp_ajax_get_terms', 'mrp_get_terms' );
			add_action( 'wp_ajax_get_rating_item', 'mrp_get_rating_item' );
			add_action( 'wp_ajax_get_custom_field', 'mrp_get_custom_field' );
			// FIXME AJAX action is the same as the free version...
			add_action( 'wp_ajax_save_rating_form', 'mrp_save_rating_form' );
			add_action( 'wp_ajax_delete_rating_form', 'mrp_delete_rating_form' );
			add_action( 'wp_ajax_delete_custom_field', 'mrp_delete_custom_field' );
			add_action( 'wp_ajax_delete_rating_item', 'mrp_delete_rating_item' );
		}
		// FIXME AJAX action is the same as the free version...
		add_action( 'wp_ajax_save_rating', array( 'MRP_Rating_form', 'save_rating' ) );
		add_action( 'wp_ajax_nopriv_save_rating', array( 'MRP_Rating_form', 'save_rating' ) );
		add_action( 'wp_ajax_delete_rating', array( 'MRP_Rating_form', 'delete_rating' ) );
		add_action( 'wp_ajax_nopriv_delete_rating', array( 'MRP_Rating_form', 'delete_rating' ) );
		add_action( 'wp_ajax_get_rating_form', array( 'MRP_Rating_form', 'get_rating_form' ) );
		add_action( 'wp_ajax_nopriv_get_rating_form', array( 'MRP_Rating_form', 'get_rating_form' ) );
	}

	/**
	 * Includes files
	 */
	function includes() {

		require_once MRP_PLUGIN_DIR . 'includes/template-tags.php';
		require_once MRP_PLUGIN_DIR . 'includes/shortcodes.php';
		require_once MRP_PLUGIN_DIR . 'includes/widgets.php';
		require_once MRP_PLUGIN_DIR . 'includes/class-utils.php';
		require_once MRP_PLUGIN_DIR . 'includes/class-api.php';
		require_once MRP_PLUGIN_DIR . 'includes/class-rating-form.php';
		require_once MRP_PLUGIN_DIR . 'includes/auto-placement.php';
		require_once MRP_PLUGIN_DIR . 'includes/class-comments.php';
		require_once MRP_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once MRP_PLUGIN_DIR . 'includes/class-settings.php';
		require_once MRP_PLUGIN_DIR . 'includes/notifications.php';
		require_once MRP_PLUGIN_DIR . 'includes/legacy.php';
		require_once MRP_PLUGIN_DIR . 'includes/template-functions.php';

		if ( is_admin() ) {
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-plugin-updater.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-rating-item-table.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-rating-form-table.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-rating-entry-table.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-rating-results-table.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-post-metabox.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/about.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/rating-forms.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/rating-items.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/rating-results.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/rating-entries.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/reports.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/settings.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/tools.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/filters.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/custom-fields.php';
			require_once MRP_PLUGIN_DIR . 'includes/admin/class-custom-fields-table.php';
		}

		if ( class_exists( 'SitePress' )  ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/wpml-compatibility.php';
		}
		if ( class_exists( 'Polylang' ) ) {
			require_once plugin_dir_path( __FILE__ ) . 'includes/polylang-compatibility.php';
		}
	}

	/**
	 * Activates the plugin
	 */
	public static function activate_plugin() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		try {

			global $wpdb, $charset_collate;

			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME . ' (
					rating_form_id bigint(20) NOT NULL AUTO_INCREMENT,
					name varchar(255) NOT NULL,
					PRIMARY KEY  (rating_form_id)
			) ' . $charset_collate;
			dbDelta( $query );

			$wpdb->show_errors();

			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME . ' (
					rating_form_id bigint(20) NOT NULL AUTO_INCREMENT,
					item_id bigint(20) NOT NULL,
					item_type varchar(50) NOT NULL,
					required tinyint(1) DEFAULT 0,
					allow_not_applicable tinyint(1) DEFAULT 0,
					weight double precision DEFAULT 1,
					PRIMARY KEY  (rating_form_id,item_id,item_type)
			) ' . $charset_collate;
			dbDelta( $query );

			$wpdb->show_errors();

			// subjects are rated by multiple rating items
			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME . ' (
					rating_item_id bigint(20) NOT NULL AUTO_INCREMENT,
					description varchar(255) NOT NULL,
					default_option_value int(11),
					max_option_value int(11),
					option_value_text varchar(1000),
					only_show_text_options tinyint(1) DEFAULT 0,
					type varchar(20) NOT NULL DEFAULT "select",
					PRIMARY KEY  (rating_item_id)
					) ' . $charset_collate;
			dbDelta( $query );

			// rating item entries and results are saved
			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' (
					rating_item_entry_id bigint(20) NOT NULL AUTO_INCREMENT,
					post_id bigint(20) NOT NULL,
					rating_form_id bigint(20) NOT NULL,
					entry_date datetime NOT NULL,
					entry_status varchar(20) NOT NULL DEFAULT "approved",
					title varchar(255),
					name varchar(100),
					email varchar(255),
					comment varchar(2000),
					user_id bigint(20) DEFAULT 0,
					comment_id bigint(20),
					PRIMARY KEY  (rating_item_entry_id),
					KEY ix_rating_entry (rating_item_entry_id,post_id,rating_form_id),
					KEY ix_comment (comment_id),
					KEY ix_user (user_id)
					) ' . $charset_collate;
			dbDelta( $query );

			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME . ' (
					rating_item_entry_value_id bigint(20) NOT NULL AUTO_INCREMENT,
					rating_item_entry_id bigint(20) NOT NULL,
					rating_item_id bigint(20) NOT NULL,
					value int(11) NOT NULL,
					PRIMARY KEY  (rating_item_entry_value_id),
					KEY ix_rating_entry (rating_item_entry_id)
					) ' . $charset_collate;
			dbDelta( $query );

			// custom fields
			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME . ' (
					custom_field_id bigint(20) NOT NULL AUTO_INCREMENT,
					label varchar(255) NOT NULL,
					type varchar(255) NOT NULL,
					max_length int(11) NOT NULL,
					placeholder varchar(255),
					PRIMARY KEY  (custom_field_id)
					) ' . $charset_collate;
			dbDelta( $query );

			// rating results
			$query = 'CREATE TABLE ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME . ' (
					id bigint(20) NOT NULL AUTO_INCREMENT,
					rating_form_id bigint(20),
					post_id bigint(20),
					rating_entry_id bigint(20),
					rating_item_id bigint(20),
					filters_hash varchar(32) NOT NULL,
					star_result float(6,3) NOT NULL,
					adjusted_star_result float(6,3) NOT NULL,
					score_result float(6,3) NOT NULL,
					adjusted_score_result float(6,3) NOT NULL,
					percentage_result float(6,3) NOT NULL,
					adjusted_percentage_result float(6,3) NOT NULL,
					total_max_option_value bigint(3) NOT NULL,
					count_entries bigint(20),
					option_totals text,
					last_updated_dt datetime NOT NULL,
					PRIMARY KEY  (id),
					KEY ix_rating_result (post_id,rating_form_id,rating_entry_id,rating_item_id,filters_hash,adjusted_star_result,count_entries),
					KEY ix_rating_entry (rating_entry_id)
					) ' . $charset_collate;
			dbDelta( $query );

		} catch ( Exception $e ) {
			// do nothing
		}

		/*
		 * Add default capabilities to roles:
		 * - "mrp_moderate_ratings" capability allows editing ratings. "editor" and "administrator" roles
		 * - "mrp_delete_ratings" capability allows delete ratings "editor" and "administrator" roles.
		 * - "mrp_export_ratings" capability allows exporting ratings. "administrator" role only
		 * - "mrp_manage_ratngs" capability full access to everything. "administrator" role only
		 */
		$editor_role = get_role( 'editor' );
		$admin_role = get_role( 'administrator' );

		if ( isset( $editor_role ) ) {
			$editor_role->add_cap( 'mrp_moderate_ratings' );
			$editor_role->add_cap( 'mrp_delete_ratings' );
			$editor_role->add_cap( 'mrp_ratings_menu' );
		}
		if ( isset( $admin_role ) ) {
			$admin_role->add_cap( 'mrp_moderate_ratings' );
			$admin_role->add_cap( 'mrp_delete_ratings' );
			$admin_role->add_cap( 'mrp_export_ratings' );
			$admin_role->add_cap( 'mrp_manage_ratings' );
			$admin_role->add_cap( 'mrp_ratings_menu' );
		}
	}

	/**
	 * Uninstalls the plugin
	 */
	public static function uninstall_plugin() {

		// delete options
		delete_option( MRP_Multi_Rating::GENERAL_SETTINGS );
		delete_option( MRP_Multi_Rating::ADVANCED_SETTINGS );
		delete_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
		delete_option( MRP_Multi_Rating::EMAIL_SETTINGS );
		delete_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
		delete_option( MRP_Multi_Rating::STYLES_SETTINGS );

		/* drop tables
		 global $wpdb;
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME );
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_VALUE_TBL_NAME );
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_TBL_NAME );
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_SUBJECT_TBL_NAME );
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME );
		 $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::RATING_RESULT_TBL_NAME );

		 // custom fields
		 $custom_fields = MRP_Multi_Rating_API::get_custom_fields( null );
		 foreach ( $custom_fields as $custom_field ) {
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'mrp_custom_field_' . $custom_field['custom_field_id'] );
			}
			$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . MRP_Multi_Rating::CUSTOM_FIELDS_TBL_NAME );
		*/
	}

	/**
	 * Checks for plugin updates in the WordPress admin
	 */
	function plugin_updater() {

		// retrieve the license from the database
		$license_settings = (array) get_option( MRP_Multi_Rating::LICENSE_SETTINGS );
		$license_key = isset( $license_settings[MRP_Multi_Rating::LICENSE_KEY_OPTION] ) ? trim( $license_settings[MRP_Multi_Rating::LICENSE_KEY_OPTION] ) : '';

		// setup the updater
		new MRP_Plugin_Updater( 'https://multiratingpro.com', __FILE__, array(
				'version' 	=> MRP_PLUGIN_VERSION, 				// current version number
				'license' 	=> $license_key, 					// license key (used get_option above to retrieve from DB)
				'item_name' => urlencode( 'Multi Rating Pro' ), // name of this plugin
				'author' 	=> 'Daniel Powney'  				// author of this plugin
		) );
	}

	/**
	 * Redirects to about page on activation
	 */
	function redirect_about_page() {

		if ( ! is_network_admin() && get_option( MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, false ) ) {
			delete_option( MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION );
			wp_redirect( 'admin.php?page=' . MRP_MULTI_RATING::ABOUT_PAGE_SLUG );
		}

	}

	/**
	 * Admin menus
	 */
	public function add_admin_menus() {

		add_menu_page( __( 'Multi Rating Pro', 'multi-rating-pro' ), __( 'Multi Rating Pro', 'multi-rating-pro' ), 'mrp_ratings_menu', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mrp_rating_results_screen', 'dashicons-star-filled', null );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, '', '', 'mrp_ratings_menu', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mrp_rating_results_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Ratings', 'multi-rating-pro' ), __( 'Ratings', 'multi-rating-pro' ), 'mrp_ratings_menu', MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, 'mrp_rating_results_screen' );

		global $wpdb;

		$query = 'SELECT COUNT(*) AS count FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' WHERE entry_status = "pending"';
		$pending_count = intval( $wpdb->get_var( $query ) );

		$pending_entries_counter = '';
		if ( $pending_count > 0 ) {
			$pending_entries_counter = ' <span class="awaiting-mod count-' . $pending_count . '"><span class="pending-count">' . $pending_count . '</span></span>';
		}

		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Entries', 'multi-rating-pro' ), __( 'Entries', 'multi-rating-pro' ) . $pending_entries_counter, 'mrp_ratings_menu', MRP_Multi_Rating::RATING_ENTRIES_PAGE_SLUG, 'mrp_rating_entries_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Forms', 'multi-rating-pro' ), __( 'Rating Forms', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::RATING_FORMS_PAGE_SLUG, 'mrp_rating_forms_screen' ); // mrp_rating_forms_screen
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Rating Items', 'multi-rating-pro' ), __( 'Rating Items', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::RATING_ITEMS_PAGE_SLUG, 'mrp_rating_items_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Custom Fields', 'multi-rating-pro' ), __( 'Custom Fields', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::CUSTOM_FIELDS_PAGE_SLUG, 'mrp_custom_fields_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Settings', 'multi-rating-pro' ), __( 'Settings', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::SETTINGS_PAGE_SLUG, 'mrp_settings_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Filters', 'multi-rating-pro' ), __( 'Filters', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::FILTERS_PAGE_SLUG, 'mrp_filters_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Tools', 'multi-rating-pro' ), __( 'Tools', 'multi-rating-pro' ), 'mrp_ratings_menu', MRP_Multi_Rating::TOOLS_PAGE_SLUG, 'mrp_tools_screen' );
		//add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'Reports', 'multi-rating-pro' ), __( 'Reports', 'multi-rating-pro' ), 'mrp_manage_ratings', MRP_Multi_Rating::REPORTS_PAGE_SLUG, 'mrp_reports_screen' );
		add_submenu_page( MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG, __( 'About', 'multi-rating-pro' ), __( 'About', 'multi-rating-pro' ), 'mrp_ratings_menu', MRP_Multi_Rating::ABOUT_PAGE_SLUG, 'mrp_about_screen' );
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function admin_assets() {

		wp_enqueue_script( 'jquery' );

		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( MRP_Multi_Rating::ID.'-nonce' ),
				'strings' => MRP_Multi_Rating::instance()->get_strings()
		);

		$disable_styles = MRP_Multi_Rating::instance()->settings->style_settings[MRP_Multi_Rating::DISABLE_STYLES_OPTION];
		$minified = MRP_Multi_Rating::instance()->settings->advanced_settings[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] ? '-min' : '';

		wp_enqueue_script( 'mrp-admin-script', MRP_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), MRP_PLUGIN_VERSION, true );
		wp_localize_script( 'mrp-admin-script', 'mrp_admin_data', $config_array );

		wp_enqueue_script( 'mrp-frontend-script', MRP_PLUGIN_URL . 'assets/js/frontend' . $minified . '.js', array('jquery'), MRP_PLUGIN_VERSION, true );
		wp_localize_script( 'mrp-frontend-script', 'mrp_frontend_data', $config_array );

		if ( ! $disable_styles ) {
			wp_enqueue_style( 'mrp-frontend-style', MRP_PLUGIN_URL . 'assets/css/frontend' . $minified . '.css' );
		}
		wp_enqueue_style( 'mrp-admin-style', MRP_PLUGIN_URL . 'assets/css/admin.css' );

		// flot
		wp_enqueue_script( 'flot', MRP_PLUGIN_URL . 'assets/js/flot/jquery.flot.js', array( 'jquery' ) );
		wp_enqueue_script( 'flot-categories', MRP_PLUGIN_URL .  'assets/js/flot/jquery.flot.categories.js', array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-time', MRP_PLUGIN_URL . 'assets/js/flot/jquery.flot.time.js', array( 'jquery', 'flot' ) );
		wp_enqueue_script( 'flot-selection', MRP_PLUGIN_URL . 'assets/js/flot/jquery.flot.selection.js', array( 'jquery', 'flot', 'flot-time' ) );

		// color picker
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );

		wp_enqueue_script( 'jquery-ui-accordion' );

		// date picker
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-style', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );

		wp_enqueue_media();
	}

	/**
	 * Javascript and CSS used by the plugin
	 *
	 * @since 0.1
	 */
	public function assets() {

		wp_enqueue_script( 'jquery' );

		$disable_styles = MRP_Multi_Rating::instance()->settings->style_settings[MRP_Multi_Rating::DISABLE_STYLES_OPTION];
		$minified = MRP_Multi_Rating::instance()->settings->advanced_settings[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] ? '-min' : '';

		if ( ! $disable_styles ) {
			wp_enqueue_style( 'mrp-frontend-style', MRP_PLUGIN_URL . 'assets/css/frontend' . $minified . '.css' );
		}

		// Allow support for other versions of Font Awesome
		$load_icon_font_library = MRP_Multi_Rating::instance()->settings->style_settings[MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION];
		$icon_font_library = MRP_Multi_Rating::instance()->settings->style_settings[MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION];

		$icon_classes = MRP_Utils::get_icon_classes( $icon_font_library );

		$protocol = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) ? 'https' : 'http';

		if ( $load_icon_font_library ) {
			if ( $icon_font_library == 'font-awesome-4.0.3' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
			} else if ( $icon_font_library == 'font-awesome-3.2.1' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' );
			} else if ( $icon_font_library == 'font-awesome-4.1.0' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'font-awesome-4.2.0' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'font-awesome-4.3.0' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'font-awesome-4.5.0' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'font-awesome-4.6.3' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'font-awesome-4.7.0' ) {
				wp_enqueue_style( 'font-awesome', $protocol . '://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' );
			} else if ( $icon_font_library == 'dashicons' ) {
				wp_enqueue_style( 'dashicons' );
			}
		}

		$config_array = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'ajax_nonce' => wp_create_nonce( MRP_Multi_Rating::ID.'-nonce' ),
				'icon_classes' => json_encode( $icon_classes ),
				'use_custom_star_images' => ( MRP_Multi_Rating::instance()->settings->style_settings[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] == true ) ? "true" : "false",
				'strings' => MRP_Multi_Rating::instance()->get_strings()
		);

		$config_array = apply_filters( 'mrp_config', $config_array );

		wp_enqueue_script( 'mrp-frontend-script', MRP_PLUGIN_URL . 'assets/js/frontend' . $minified . '.js', array( 'jquery' ), MRP_PLUGIN_VERSION, true );
		wp_localize_script( 'mrp-frontend-script', 'mrp_frontend_data', $config_array );

	}

	/**
	 * Register widgets
	 */
	public function register_widgets() {
		register_widget( 'MRP_Rating_Results_List_Widget' );
		register_widget( 'MRP_User_Rating_Results_List_Widget' );
		register_widget( 'MRP_Rating_Form_Widget' );
		register_widget( 'MRP_Rating_Result_Widget' );
		register_widget( 'MRP_Rating_Item_Results_Widget' );
		register_widget( 'MRP_Rating_Entry_List_Widget' );
	}
//add_action( 'widgets_init', 'mrp_register_widgets' );

	/**
	  * Load plugin text domain
	  */
	public function load_textdomain() {
		load_plugin_textdomain( 'multi-rating-pro', false, MRP_PLUGIN_DIR . '/languages/' );
	}

	/**
	 * WP head
	 */
	public function mrp_head() {

		if ( apply_filters( 'mrp_head_css', true ) ) { // in case you want to move the CSS into your theme instead

			?>
			<style type="text/css">
				<?php
				$styles_settings = (array) get_option( MRP_Multi_Rating::STYLES_SETTINGS );
				echo $styles_settings[MRP_Multi_Rating::CUSTOM_CSS_OPTION];

				$star_rating_colour = $styles_settings[MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION];
				$star_rating_hover_colour = $styles_settings[MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION];
				$error_message_colour = $styles_settings[MRP_Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION];
				$icon_font_library = $styles_settings[MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION];

				$image_width = $styles_settings[MRP_Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH];
				$image_height = $styles_settings[MRP_Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT];

				?>
				.mrp-custom-full-star {
					background: url(<?php echo parse_url( $styles_settings[MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE], PHP_URL_PATH ); ?>) no-repeat;
					width: <?php echo $image_width; ?>px;
					height: <?php echo $image_height; ?>px;
					background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
					image-rendering: -moz-crisp-edges;
					display: inline-block;
				}
				.mrp-custom-half-star {
					background: url(<?php echo parse_url( $styles_settings[MRP_Multi_Rating::CUSTOM_HALF_STAR_IMAGE], PHP_URL_PATH ); ?>) no-repeat;
					width: <?php echo $image_width; ?>px;
					height: <?php echo $image_height; ?>px;
					background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
					image-rendering: -moz-crisp-edges;
					display: inline-block;
				}
				.mrp-custom-empty-star {
					background: url(<?php echo parse_url( $styles_settings[MRP_Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE], PHP_URL_PATH ); ?>) no-repeat;
					width: <?php echo $image_width; ?>px;
					height: <?php echo $image_height; ?>px;
					background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
					image-rendering: -moz-crisp-edges;
					display: inline-block;
				}
				.mrp-custom-hover-star {
					background: url(<?php echo parse_url( $styles_settings[MRP_Multi_Rating::CUSTOM_HOVER_STAR_IMAGE], PHP_URL_PATH ); ?>) no-repeat;
					width: <?php echo $image_width; ?>px;
					height: <?php echo $image_height; ?>px;
					background-size: <?php echo $image_width; ?>px <?php echo $image_height; ?>px;
					image-rendering: -moz-crisp-edges;
					display: inline-block;
				}
				.mrp-star-hover {
					color: <?php echo $star_rating_hover_colour; ?> !important;
				}
				.mrp-star-full, .mrp-star-half, .mrp-star-empty {
					color: <?php echo $star_rating_colour; ?>;
				}
				.mrp-error {
					color: <?php echo $error_message_colour; ?>;
				}
				<?php // dashicons only has filled thumb icons
				if ( $icon_font_library == 'dashicons' ) {
					?>
					.mrp-thumbs-up-on, .mrp-thumbs-down-on {
						color: <?php echo $star_rating_colour; ?>;
					}
					<?php
				}
				?>
			</style>
			<?php
		}
	}

	/**
	 * Returns an array of strings that can be used in localized JavaScript files
	 */
	public function get_strings() {

		$custom_text_settings = (array) MRP_Multi_Rating::instance()->settings->custom_text_settings;

		return array(
				'submit_btn_text' 				=> $custom_text_settings[MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION],
				'delete_btn_text' 				=> $custom_text_settings[MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION],
				'confirm_clear_db_message' 		=> __( 'Are you sure you want to permanently delete ratings?', 'multi-rating-pro' ),
				'confirm_import_db_message' 	=> __( 'Are you sure you want to import ratings?', 'multi-rating-pro' ),
				'edit_label' 					=> __( 'Edit', 'multi-rating-pro' ),
				'delete_label' 					=> __( 'Delete', 'multi-rating-pro' ),
				'no_items_message' 				=> __( 'No items.', 'multi-rating-pro' ),
				'rating_item_label' 			=> __( 'Rating Item', 'multi-rating-pro' ),
				'custom_field_label' 			=> __( 'Custom Field', 'multi-rating-pro' ),
				'review_field_label' 			=> __( 'Review Field', 'multi-rating-pro' ),
				'approve_anchor_text'			=> __( 'Approve', 'multi-rating-pro' ),
				'unapprove_anchor_text'			=> __( 'Unapprove', 'multi-rating-pro' ),
				'approved_entry_status_text'	=> __( 'Approved', 'multi-rating-pro' ),
				'pending_entry_status_text'		=> __( 'Pending', 'multi-rating-pro' ),
				'id_text'						=> __( 'ID', 'multi-rating-pro' )
		);

	}

}


/**
 * Activate plugin
 */
function mrp_activate_plugin() {

	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		if ( ! is_network_admin() ) { // is network admin request?
			add_option(MRP_MULTI_RATING::DO_ACTIVATION_REDIRECT_OPTION, true);
		}
		MRP_Multi_Rating::activate_plugin();
	}
}
register_activation_hook( __FILE__, 'mrp_activate_plugin');


/**
 * Uninstall plugin
 */
function mrp_uninstall_plugin() {

	if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
		MRP_Multi_Rating::uninstall_plugin();
	}
}
register_uninstall_hook( __FILE__, 'mrp_uninstall_plugin' );


/**
 * Checks whether function is disabled.
 *
 * @param string  $function Name of the function.
 * @return bool Whether or not function is disabled.
 */
function mrp_is_func_disabled( $function ) {
	$disabled = explode( ',',  ini_get( 'disable_functions' ) );

	return in_array( $function, $disabled );
}


/**
 * Check for updates
 */
if ( is_admin() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
	require_once dirname( __FILE__ ) . '/includes/admin/upgrade-functions.php';
	mrp_upgrade_check();
}


/**
 * Instantiate plugin main class
 */
function mrp_multi_rating() {

	do_action( 'mrp_before_init' );

	MRP_Multi_Rating::instance();

	do_action( 'mrp_after_init' );
}
// Note WPML is initialized in "plugins_loaded" with priority 10, so priority needs > 10
add_action( 'plugins_loaded', 'mrp_multi_rating', 11 );

<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Plugin settings class
 */
class MRP_Settings {

	public $custom_text_settings = array();
	public $styles_settings = array();
	public $general_settings = array();
	public $advanced_settings = array();
	public $email_settings = array();
	public $auto_placement_settings = array();

	/**
	 * Constructor
	 */
	function __construct() {

		if ( is_admin() ) {
			add_action( 'admin_init', array( &$this, 'register_settings' ) );
			add_action( 'admin_init', array( &$this, 'activate_license' ) );
			add_action( 'admin_init', array( &$this, 'deactivate_license' ) );
		}

		$this->load_settings();
	}

	/**
	 * Reisters settings
	 */
	function register_settings() {
		$this->register_custom_text_settings();
		$this->register_styles_settings();
		$this->register_general_settings();
		$this->register_advanced_settings();
		$this->register_license_settings();
		$this->register_email_settings();
		$this->register_auto_placement_settings();
	}

	/**
	 * Retrieve settings from DB and sets default options if not set
	 */
	function load_settings() {

		$this->general_settings = (array) get_option( MRP_Multi_Rating::GENERAL_SETTINGS, array() );
		$this->advanced_settings = (array) get_option( MRP_Multi_Rating::ADVANCED_SETTINGS, array() );
		$this->email_settings = (array) get_option( MRP_Multi_Rating::EMAIL_SETTINGS, array() );
		$this->auto_placement_settings = (array) get_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, array() );
		$this->custom_text_settings = (array) get_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, array() );
		$this->style_settings = (array) get_option( MRP_Multi_Rating::STYLES_SETTINGS, array() );

		// Merge with defaults
		if ( $this->check_default_rating_form() ) {
			$this->generate_default_rating_form();
		}

		$this->general_settings = array_merge( array(
				MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION 		=> array( 'cookie' ),
				MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION 		=> 24,
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION 			=> true,
				MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING 		=> true,
				MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS 			=> true,
				MRP_Multi_Rating::GENERATE_MICRODATA_OPTION 				=> true,
				MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION 		=> 'star_rating',
				MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION 				=> 5,
				MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION 		=> 1,
				MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS			 		=> false,
		), $this->general_settings );

		$this->advanced_settings = array_merge( array(
				MRP_Multi_Rating::RATING_ALGORITHM_OPTION 					=> 'average',
				MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION					=> false,
				MRP_Multi_Rating::SORT_WP_QUERY_OPTION 						=> false,
				MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION 		=> array(),
				MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION 			=> true,
				MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION 			=> true,
				MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION					=> false,
				MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION 				=> true,
				//MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION					=> false,
				MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION				=> false
		), $this->advanced_settings );

		$rating_approved_email_template =
				__( "Hello,", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "A new rating #{rating_entry_id} by \"{display_name}\" has been approved.", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "Date: {date}", 'multi-rating-pro' ) . "\r\n"
				. __( "{rating_details}", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "Thank you.", 'multi-rating-pro' );

		$rating_moderation_email_template =
				__( "Hello,", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "A new rating #{rating_entry_id} by \"{display_name}\" requires moderation.", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "Date: {date}", 'multi-rating-pro' ) . "\r\n"
				. __( "{rating_details}", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "{rating_moderation_link}", 'multi-rating-pro' ) . "\r\n\r\n"
				. __( "Thank you.", 'multi-rating-pro' );

		$this->email_settings = array_merge( array(
				// rating approved e-mail
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE 				=> false,
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR 		=> false,
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER 		=> false,
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS 			=> get_option( 'admin_email' ),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_NAME 			=> get_option( 'blogname' ),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_EMAIL 			=> get_option( 'admin_email' ),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_SUBJECT 			=> __( 'Rating Approved', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_HEADING				=> __( 'Rating Approved', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TEMPLATE 			=> $rating_approved_email_template,
				// rating moderation e-mail
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE 			=> false,
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS		 	=> get_option( 'admin_email' ),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_NAME 		=> get_option( 'blogname' ),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_EMAIL 		=> get_option( 'admin_email' ),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_SUBJECT 			=> __( 'Rating Moderation', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_HEADING 			=> __( 'Rating Moderation', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_TEMPLATE 			=> $rating_moderation_email_template,
		), $this->email_settings );

		$this->auto_placement_settings = array_merge( array(
				MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION 			=> 'after_title',
				MRP_Multi_Rating::RATING_FORM_POSITION_OPTION 				=> 'after_content',
				MRP_Multi_Rating::POST_TYPES_OPTION 						=> 'post',
				MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES 				=> false,
				MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE 					=> false,
				MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS				=> false,
				// comment form
				MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION 		=> false,
				MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION => true,
				// comment text
				MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION 		=> true,
				MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION 				=> true,
				MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION 			=> true,
				MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION 		=> true
		), $this->auto_placement_settings );

		$default_custom_text = array(
				// titles
				MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION 			=> __( 'Please rate this', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION 	=> __( 'Ratings', 'multi-rating-pro' ),
				MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION 	=> __( 'Your Ratings', 'multi-rating-pro' ),
				MRP_Multi_Rating::USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION 	=> __( 'Ratings Dashboard', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_ENTRIES_LIST_TITLE_TEXT_OPTION 	=> __( 'Rating Entries', 'multi-rating-pro' ),
				// labels
				MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION 	=> __( 'Submit Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION 	=> __( 'Update Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION		=> __( 'Delete Rating', 'multi-rating-pro' ),
				MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION 					=> __( 'Category', 'multi-rating-pro' ),
				MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION					=> __( 'Filter', 'multi-rating-pro' ),
				// messages
				MRP_Multi_Rating::SAVE_RATING_RESTRICTION_ERROR_MESSAGE_OPTION => __( 'You cannot submit a rating for the same post multiple times.', 'multi-rating-pro' ),
				MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION 			=> __( 'No ratings yet.', 'multi-rating-pro' ),
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION => __( 'You must be logged in to submit a rating.', 'multi-rating-pro' ),
				MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION 		=> __( 'Field is required.', 'multi-rating-pro' ),
				MRP_Multi_Rating::RATING_AWAITING_MODERATION_MESSAGE_OPTION => __( 'Your rating is awaiting moderation.', 'multi-rating-pro' ),
				MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION 			=> __( 'You have already submitted this rating form.', 'multi-rating-pro' ),
				MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating submitted successfully.', 'multi-rating-pro' ),
				MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating updated successfully.', 'multi-rating-pro' ),
				MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION 		=> __( 'Rating deleted successfully.', 'multi-rating-pro' ),
				MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_ERROR_MESSAGE_OPTION => __( 'You are not allowed to submit a rating.', 'multi-rating-pro' )
		);

		$this->custom_text_settings = array_merge( $default_custom_text, $this->custom_text_settings );

		// If custom text is disabled, always use defaults
		if ( isset( $this->advanced_settings[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] ) && $this->advanced_settings[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] ) {
			$this->custom_text_settings = $default_custom_text;
			// TODO e-mail text settings
		}

		$this->style_settings = array_merge( array(
				MRP_Multi_Rating::CUSTOM_CSS_OPTION 						=> '',
				MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION					=> '#ffd700',
				MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION 			=> '#ffba00',
				MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION 			=> true,
				MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION 					=> 'font-awesome-4.7.0',
				MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES 					=> false,
				MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE 					=> '',
				MRP_Multi_Rating::CUSTOM_HALF_STAR_IMAGE 					=> '',
				MRP_Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE 					=> '',
				MRP_Multi_Rating::CUSTOM_HOVER_STAR_IMAGE 					=> '',
				MRP_Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH 					=> 32,
				MRP_Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT 					=> 32,
				MRP_Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION 				=> '#EC6464',
				MRP_Multi_Rating::DISABLE_STYLES_OPTION 					=> false
		), $this->style_settings );

		update_option( MRP_Multi_Rating::GENERAL_SETTINGS, $this->general_settings);
		update_option( MRP_Multi_Rating::ADVANCED_SETTINGS, $this->advanced_settings);
		update_option( MRP_Multi_Rating::EMAIL_SETTINGS, $this->email_settings);
		update_option( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, $this->auto_placement_settings);
		update_option( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, $this->custom_text_settings);
		update_option( MRP_Multi_Rating::STYLES_SETTINGS, $this->style_settings);

	}

	/**
	 * Moderation settings
	 */
	function register_email_settings() {

		register_setting( MRP_Multi_Rating::EMAIL_SETTINGS, MRP_Multi_Rating::EMAIL_SETTINGS, array( &$this, 'sanitize_email_settings' ) );

		add_settings_section( 'section_rating_approved_email', __( 'Rating Approved Email', 'multi-rating-pro' ), array( &$this, 'section_rating_approved_email_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB  );
		add_settings_section( 'section_rating_moderation_email', __( 'Rating Moderation Email', 'multi-rating-pro' ), array( &$this, 'section_rating_moderation_email_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB  );

		$setting_fields = array(
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE => array(
						'title' 	=> __( 'Enable Notification', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE,
								'label' 		=> __( 'Check this box if you want to enable rating approved e-mail notifications.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS => array(
						'title' 	=> __( 'To Emails', 'multi-rating-pro' ),
						'callback' 	=> 'field_rating_approved_email_to_emails',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email'
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_NAME => array(
						'title' 	=> __( 'From Name', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_NAME,
								'label' 		=> __( 'The name rating approved notifications are said to come from.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_EMAIL => array(
						'title' 	=> __( 'From Email', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_FROM_EMAIL,
								'label' 		=> __( 'Email to send rating approved notifications from.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_SUBJECT => array(
						'title' 	=> __( 'Email Subject', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_SUBJECT,
								'label' 		=> __( 'Enter the subject line for the rating approved notification email', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_HEADING => array(
						'title' 	=> __( 'Email Heading', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_HEADING,
								'label' 		=> __( 'Enter the heading for the rating approved notification email', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_APPROVED_EMAIL_TEMPLATE => array(
						'title' 	=> __( 'Email Template', 'multi-rating-pro' ),
						'callback' 	=> 'field_editor',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_approved_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_APPROVED_EMAIL_TEMPLATE,
								'footer' 		=> __( 'Enter the email that is sent whenever a rating is approved (manually or automatically). HTML is accepted. Available template tags:<br />'
										. '{display_name} - The user\'s display name<br />'
										. '{username} - The user\'s username on the site<br />'
										. '{user_email} - The user\'s email address<br />'
										. '{site_name} - Your site name<br />'
										. '{post_permalink} - Permalink to post with name<br />'
										. '{rating_entry_id} - The unique ID number for this rating entry<br />'
										. '{rating_details} - The rating details including title and comment<br />'
										. '{date} - The date of the rating entry', 'multi-rating-pro' ),
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE => array(
						'title' 	=> __( 'Enable Notification', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE,
								'label' 		=> __( 'Check this box if you want to enable rating moderation e-mail notifications.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS => array(
						'title' 	=> __( 'Emails', 'multi-rating-pro' ),
						'callback' 	=> 'field_rating_moderation_email_to_emails',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email'
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_NAME => array(
						'title' 	=> __( 'From Name', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_NAME,
								'label' 		=> __( 'The name rating moderation notifications are said to come from.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_EMAIL => array(
						'title' 	=> __( 'From Email', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_FROM_EMAIL,
								'label' 		=> __( 'Email to send rating assignment moderation notifications from.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_SUBJECT => array(
						'title' 	=> __( 'Email Subject', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_SUBJECT,
								'label' 		=> __( 'Enter the subject line for the rating moderation notification email', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_HEADING => array(
						'title' 	=> __( 'Email Heading', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_HEADING,
								'label' 		=> __( 'Enter the heading for the rating moderation notification email', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_MODERATION_EMAIL_TEMPLATE => array(
						'title' 	=> __( 'Email Template', 'multi-rating-pro' ),
						'callback' 	=> 'field_editor',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB,
						'section' 	=> 'section_rating_moderation_email',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::EMAIL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_MODERATION_EMAIL_TEMPLATE,
								'footer' 		=> __( 'Enter the email that is sent to a moderator to notify them a new rating requires approval. HTML is accepted. Available template tags:<br />'
										. '{display_name} - The user\'s display name<br />'
										. '{username} - The user\'s username on the site<br />'
										. '{user_email} - The user\'s email address<br />'
										. '{site_name} - Your site name<br />'
										. '{post_permalink} - Permalink to post with name<br />'
										. '{rating_entry_id} - The unique ID number for this rating entry<br />'
										. '{rating_details} - The rating details including title and comment<br />'
										. '{date} - The date of the rating entry<br />'
										. '{rating_moderation_link} - Link to rating entries page'
										. '{edit_rating_link} - Link to edit rating page', 'multi-rating-pro' )
						)
				),
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], isset( $setting_data['args'] ) ? $setting_data['args'] : array() );
		}
	}

	/**
	 * Rating approved email to emails
	 */
	function field_rating_approved_email_to_emails() {
		$to_emails = $this->email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS];
		$to_post_author = $this->email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] == true ? ' checked="checked"' : '';
		$to_submitter = $this->email_settings[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] == true ? ' checked="checked"' : '';
		?>
		<p><?php _e( 'Who should receive a notification whenever a rating is approved?', 'multi-rating-pro' ); ?></p><br />
		<input type="checkbox" value="true" name="<?php echo MRP_Multi_Rating::EMAIL_SETTINGS . '[' . MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR . ']'; ?>" <?php echo $to_post_author; ?> /><label><?php _e( 'Notify the post author', 'multi-rating-pro' ); ?></label><br />
		<input type="checkbox" value="true" name="<?php echo MRP_Multi_Rating::EMAIL_SETTINGS . '[' . MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER . ']'; ?>"  <?php echo $to_submitter; ?> /><label><?php _e( 'Notify the user who submitted the rating (if possible)', 'multi-rating-pro' ); ?></label><br /><br />
		<textarea name="<?php echo MRP_Multi_Rating::EMAIL_SETTINGS . '[' . MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_EMAILS . ']'; ?>" rows="5" cols="75"><?php echo $to_emails; ?></textarea>
		<p><?php _e( 'Enter email address(es), one per line.', 'multi-rating-pro' ); ?></p>
		<?php
	}

	/**
	 * Rating moderation email to emails
	 */
	function field_rating_moderation_email_to_emails() {
		$to_emails = $this->email_settings[MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS];
		?>
		<p><?php _e( 'Who should receive a notification whenever a new rating is awating moderation?', 'multi-rating-pro' ); ?></p><br />
		<textarea name="<?php echo MRP_Multi_Rating::EMAIL_SETTINGS . '[' . MRP_Multi_Rating::RATING_MODERATION_EMAIL_TO_EMAILS . ']'; ?>" rows="5" cols="75"><?php echo $to_emails; ?></textarea>
		<p><?php _e( 'Enter email address(es), one per line.', 'multi-rating-pro' ); ?></p>
		<?php
	}

	/**
	 * Rating approved email section description
	 */
	function section_rating_approved_email_desc() {
	}

	/**
	 * Rating moderation email section description
	 */
	function section_rating_moderation_email_desc() {
	}

	/**
	 * Sanitize the email settings
	 * @param $input
	 * @return $input
	 */
	function sanitize_email_settings( $input ) {

		if ( isset( $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] ) && $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] == 'true' ) {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] = true;
		} else {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_ENABLE] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] ) && $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] == 'true' ) {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] = true;
		} else {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_POST_AUTHOR] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] ) && $input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] == 'true' ) {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] = true;
		} else {
			$input[MRP_Multi_Rating::RATING_APPROVED_EMAIL_TO_SUBMITTER] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] ) && $input[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] == 'true' ) {
			$input[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] = true;
		} else {
			$input[MRP_Multi_Rating::RATING_MODERATION_EMAIL_ENABLE] = false;
		}

		return $input;
	}

	/**
	 * General settings
	 */
	function register_general_settings() {

		register_setting( MRP_Multi_Rating::GENERAL_SETTINGS, MRP_Multi_Rating::GENERAL_SETTINGS, array( &$this, 'sanitize_general_settings' ) );

		add_settings_section( 'section_general', null, array( &$this, 'section_general_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG );

		$setting_fields = array(
				MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION => array(
						'title' 	=> __( 'Default Rating Form', 'multi-rating-pro' ),
						'callback' 	=> 'field_default_rating_form',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general'
				),
				MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION => array(
						'title' 	=> __( 'Duplicate Check Method', 'multi-rating-pro' ),
						'callback' 	=> 'field_duplicate_check_method',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general'
				),
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION => array(
						'title' 	=> __( 'Allow Anonymous Ratings', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION,
								'label' 		=> __( 'Check this box if you want to allow anonymous users to submit ratings.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING => array(
						'title' 	=> __( 'Allow Update Ratings', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING,
								'label' 		=> __( 'Check this box if you want to allow logged in users to update or delete their own ratings.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS => array(
						'title' 	=> __( 'Auto Approve Ratings', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS,
								'label' 		=> __( 'Check this box if you want to enable automatic approval of ratings.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::GENERATE_MICRODATA_OPTION => array(
						'title' 	=> __( 'Generate Microdata', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::GENERATE_MICRODATA_OPTION,
								'label' 		=> __( 'Check this box if you want to add schema.org microdata markup for ratings and entries. This will generate rich snippets in search engine results pages (SERP).', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION => array(
						'title' 	=> __( 'Default Rating Result Type', 'multi-rating-pro' ),
						'callback' 	=> 'field_select',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DEFAULT_RATING_RESULT_TYPE_OPTION,
								'label' 		=> __( 'Choose a default rating result type.', 'multi-rating-pro' ),
								'select_options' => array(
										'star_rating' 		=> __( 'Stars', 'multi-rating-pro' ),
										'score'				=> __( 'Score', 'multi-rating-pro' ),
										'percentage'		=> __( 'Percentage', 'multi-rating-pro' )
								)
						)
				),
				MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION => array(
						'title' 	=> __( 'Star Rating Out Of', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION,
								'label' 		=> __( 'Set the default out of number for star ratings (e.g. out of 5). You will need refresh the database cache to ensure ratings are recalculated.', 'multi-rating-pro' ),
								'type'			=> 'number',
								'class'			=> 'small-text',
								'min'			=> 0
						)
				),
				MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION => array(
						'title' 	=> __( 'Rating Result Decimal Places', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION,
								'label' 		=> __( 'Set the maximum number of decimal places for rating results. ', 'multi-rating-pro' ),
								'type'			=> 'number',
								'class'			=> 'small-text',
								'max'			=> 3,
								'min'			=> 0
						)
				),
				MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS => array(
						'title' 	=> __( 'Keed Any Trailing Zeros', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_general',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS,
								'label' 		=> __( 'Check this box if you want to keep any trailing zeros for star rating and scrore result types.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::RATING_ALGORITHM_OPTION => array(
						'title' 	=> __( 'Rating Algorithm', 'multi-rating-pro' ),
						'callback' 	=> 'field_rating_algorithm',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced'
				),
				MRP_Multi_Rating::SORT_WP_QUERY_OPTION => array(
						'title' 	=> __( 'Sort WP Query', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SORT_WP_QUERY_OPTION,
								'label' 		=> __( 'Check this box if you want to enable sorting the main query by highest rated posts. Sticky posts will still be on top.', 'multi-rating-pro' )
						)
				),
				/* MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION => array(
						'title' 	=> __( 'Sort Comment Query', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION,
								'label' 		=> __( 'Check this box if you want to enable sorting the comment query by highest rated entries.', 'multi-rating-pro' )
						)
				),*/
				MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION => array(
						'title' 	=> __( 'Hide Rating Form Submit', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION,
								'label' 	=> __( 'Check this box if you want to hide the rating form on submit.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION => array(
						'title' 	=> __( 'Hide Post Meta Box', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION,
								'label' 	=> __( 'Check this box if you want to hide the post meta box by default.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION => array(
						'title' 	=> __( 'Disallowed User Roles', 'multi-rating-pro' ),
						'callback' 	=> 'field_disallowed_user_roles',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced'
				),
				MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION => array(
						'title' 	=> __( 'Template Strip Newlines', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::GENERAL_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION,
								'label' 	=> sprintf( __( 'Some plugins convert newlines to HTML paragraphs similar to <a href="%s">wpautop</a> (e.g. Visual Composer). Check this box if you want to prevent this from happening by stripping the newlines from the Multi Rating Pro templates.', 'multi-rating-pro' ), 'https://codex.wordpress.org/Function_Reference/wpautop' )
						)
				)
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], isset( $setting_data['args'] ) ? $setting_data['args'] : array() );
		}
	}

	/**
	 * General section description
	 */
	function section_general_desc() {
	}

	/**
	 * Duplicate check method field
	 */
	function field_duplicate_check_method() {

		$save_rating_restrictions_types = array(
				'cookie' => __( 'Cookie', 'multi-rating-pro')
		);

		$save_rating_restriction_types_checked = $this->general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION];
		
		foreach ( $save_rating_restrictions_types as $save_rating_restrictions_type => $save_rating_restrictions_label) {
			echo '<input type="checkbox" name="' . MRP_Multi_Rating::GENERAL_SETTINGS . '[' . MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION . '][]" value="' . $save_rating_restrictions_type . '"';
			if ( is_array($save_rating_restriction_types_checked ) ) {
				if ( in_array( $save_rating_restrictions_type, $save_rating_restriction_types_checked ) ) {
					echo 'checked="checked"';
				}
			} else {
				checked( $save_rating_restrictions_type, $save_rating_restriction_types_checked, true );
			}
			echo ' />&nbsp;<label class="checkbox-label">' . $save_rating_restrictions_label . '</label><br />';
		}
		?>

		<label><?php _e('Hours', 'multi-rating-pro'); ?></label>&nbsp;<input class="small-text" type="number" min="1" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>[<?php echo MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION; ?>]" value="<?php echo $this->general_settings[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION]; ?>" />
		<p><?php _e( 'Choose a method to prevent ratings for the same post multiple times. This only applies for anonymous users.', 'multi-rating-pro' ); ?></p>
		<?php
	}

	/**
	 * Rating algorithm
	 */
	function field_rating_algorithm() {
		?>
		<input type="radio" name="<?php echo MRP_Multi_Rating::ADVANCED_SETTINGS;?>[<?php echo MRP_Multi_Rating::RATING_ALGORITHM_OPTION; ?>]" value="average" <?php checked( 'average', $this->advanced_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION], true); ?> />
		<label><?php _e( 'Average', 'multi-rating-pro' ); ?></label><br />
		<input type="radio" name="<?php echo MRP_Multi_Rating::ADVANCED_SETTINGS;?>[<?php echo MRP_Multi_Rating::RATING_ALGORITHM_OPTION; ?>]" value="bayesian_average" <?php checked( 'bayesian_average', $this->advanced_settings[MRP_Multi_Rating::RATING_ALGORITHM_OPTION], true); ?> />
		<label><?php _e( 'Bayesian Average', 'multi-rating-pro' ); ?></label>
		<?php
	}

	/**
	 * Default rating form
	 */
	function field_default_rating_form() {
		$rating_form_id = $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];
		?>
		<select name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>[<?php echo MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION; ?>]">
			<?php
			global $wpdb;

			$query = 'SELECT name, rating_form_id FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME;
			$rows = $wpdb->get_results( $query, ARRAY_A );

			foreach ( $rows as $row ) {
				$selected = '';

				if ( intval( $row['rating_form_id'] ) == intval( $rating_form_id ) ) {
					$selected = ' selected="selected"';
				}

				echo '<option value="' . esc_attr( $row['rating_form_id'] ) . '"' . $selected . '>' .  esc_html( stripslashes( $row['name'] ) ) . '</option>';
			}
			?>
		</select>
		<label><?php _e( 'Set the default rating form for all posts.', 'multi-rating-pro' ); ?></label>
		<?php
	}

	/**
	 * Disallowed user roles ratings
	 */
	function field_disallowed_user_roles() {
		global $wp_roles;
		$user_roles = $wp_roles->get_names();

		$disallowed_user_roles_checked = $this->advanced_settings[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION];
		foreach ( $user_roles as $user_role ) {
			echo '<input type="checkbox" name="' . MRP_Multi_Rating::ADVANCED_SETTINGS . '[' . MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION . '][]" value="' . $user_role . '"';
			if ( is_array( $disallowed_user_roles_checked ) ) {
				if ( in_array( $user_role, $disallowed_user_roles_checked ) ) {
					echo 'checked="checked"';
				}
			} else {
				checked( $user_role, $disallowed_user_roles_checked, true );
			}
			echo ' />&nbsp;<label class="checkbox-label">' . $user_role . '</label><br />';
		}
	}


	/**
	 * Checks if a default rating form needs to be generated
	 */
	public function check_default_rating_form() {

		if ( isset( $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION] ) ) {
			$default_rating_form_id = $this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION];

			global $wpdb;
			$query = 'SELECT COUNT(rating_form_id) FROM ' . $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME
					. ' WHERE rating_form_id = %d';

			$count = $wpdb->get_var( $wpdb->prepare( $query, $default_rating_form_id ) );

			if ( $count == 0 ) {
				return true;
			}

		} else {
			return true;
		}

		return false;
	}

	/**
	 * Generates a default rating form with a sample rating item
	 */
	public function generate_default_rating_form() {

		$rating_form_id = null;

		try {
			global $wpdb;

			$name = __( 'Default rating form', 'multi-rating-pro' );
			$results = $wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_TBL_NAME,
					array( 'name' => $name ),
					array( '%s' )
			);
			$rating_form_id = intval( $wpdb->insert_id );

			do_action( 'mrp_register_single_string', 'rating-form-' . $rating_form_id . '-name', $name );

			$description = __( 'Sample rating item', 'multi-rarting' );
			$option_value_text = __( '0=No stars,1=1 star,2=2 stars,3=3 stars,4=4 stars,5=5 stars', 'multi-rating-pro' );

			$results = $wpdb->insert( $wpdb->prefix.MRP_Multi_Rating::RATING_ITEM_TBL_NAME,
					array(
							'description' => $description,
							'max_option_value' => 5,
							'default_option_value' => 5,
							'option_value_text' => $option_value_text,
							'type' => 'star_rating',
							'only_show_text_options' => false
					),
					array( '%s', '%d', '%d', '%s', '%s', '%d' )
			);
			$rating_item_id = intval( $wpdb->insert_id );

			$wpdb->insert( $wpdb->prefix . MRP_Multi_Rating::RATING_FORM_ITEM_TBL_NAME,
					array(
							'weight' => 1,
							'required' => true,
							'rating_form_id' => $rating_form_id,
							'item_id' => $rating_item_id,
							'item_type' => 'rating-item'
					),
					array( '%f', '%d', '%d', '%d', '%s' )
			);

			do_action( 'mrp_register_single_string', 'rating-item-' . $rating_item_id . '-description', $description );
			do_action( 'mrp_register_single_string', 'rating-item-' . $rating_item_id . '-option-value-text', $option_value_text );

		} catch ( Exception $e ) {
			// do nothing
		}

		// prevent duplicate default rating forms getting created
		$this->general_settings[MRP_Multi_Rating::DEFAULT_RATING_FORM_OPTION] = $rating_form_id;
	}

	/**
	 * Sanitize the general settings
	 *
	 * @param $input
	 * @return $input
	 */
	function sanitize_general_settings( $input ) {

		if ( ! isset( $input[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION] ) ) {
			$input[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION] = array();
		}

		if ( count($input[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_TYPES_OPTION] ) > 0 ) {
			if ( ! is_numeric( $input[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION] ) ) {
				add_settings_error(MRP_Multi_Rating::GENERAL_SETTINGS, 'non_numeric_save_rating_restriction_hours', __( 'Save rating restriction hours must be numeric.', 'multi-rating-pro' ) );
			} else if ( $input[MRP_Multi_Rating::SAVE_RATING_RESTRICTION_HOURS_OPTION] <= 0 ){
				add_settings_error(MRP_Multi_Rating::GENERAL_SETTINGS, 'invalid_save_rating_restriction_hours', __( 'Save rating restriction hours must be greater than 0.', 'multi-rating-pro' ) );
			}
		}

		if ( isset( $input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] ) && $input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING] ) && $input[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING] == 'true' ) {
			$input[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING] = true;
		} else {
			$input[MRP_Multi_Rating::ALLOW_USER_UPDATE_OR_DELETE_RATING] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] ) && $input[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] == 'true' ) {
			$input[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] = true;
		} else {
			$input[MRP_Multi_Rating::AUTOMATICALLY_APPROVE_RATINGS] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] ) && $input[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::GENERATE_MICRODATA_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] ) && $input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] ) && $input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ) && $input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] ) && $input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] ) && $input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] = false;
		}

		if ( ! is_numeric( $input[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION] ) || intval( $input[MRP_Multi_Rating::STAR_RATING_OUT_OF_OPTION] ) <= 0 ) {
			add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'validation_error_invalid_star_rating_out_of', __( 'Star rating out must be greater than 0.', 'multi-rating-pro' ), 'error' );
		}

		if ( ! is_numeric( $input[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION] ) || intval( $input[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION] ) < 0
				|| intval( $input[MRP_Multi_Rating::RATING_RESULT_DECIMAL_PLACES_OPTION] ) > 3  ) {
			add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'validation_error_invalid_rating_result_decimal_places', __( 'Rating result decimal places must be greater than 0 and less than or equal to 3.', 'multi-rating-pro' ), 'error' );
		}

		if ( isset( $input[MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS] ) && $input[MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS] == 'true' ) {
			$input[MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS] = true;
		} else {
			$input[MRP_Multi_Rating::KEEP_ANY_TRAILING_ZEROS] = false;
		}

		return $input;
	}


	/**
	 * General settings
	 */
	function register_advanced_settings() {

		register_setting( MRP_Multi_Rating::ADVANCED_SETTINGS, MRP_Multi_Rating::ADVANCED_SETTINGS, array( &$this, 'sanitize_advanced_settings' ) );

		add_settings_section( 'section_advanced', null, array( &$this, 'section_advanced_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB );

		$setting_fields = array(
				MRP_Multi_Rating::RATING_ALGORITHM_OPTION => array(
						'title' 	=> __( 'Rating Algorithm', 'multi-rating-pro' ),
						'callback' 	=> 'field_rating_algorithm',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced'
				),
				MRP_Multi_Rating::SORT_WP_QUERY_OPTION => array(
						'title' 	=> __( 'Sort WP Query', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SORT_WP_QUERY_OPTION,
								'label' 		=> __( 'Check this box if you want to enable sorting the main query by highest rated posts. Sticky posts will still be on top.', 'multi-rating-pro' )
						)
				),
				/* MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION => array(
				 'title' 	=> __( 'Sort Comment Query', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION,
								'label' 		=> __( 'Check this box if you want to enable sorting the comment query by highest rated entries.', 'multi-rating-pro' )
						)
				),*/
				MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION => array(
						'title' 	=> __( 'Hide Rating Form Submit', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION,
								'label' 	=> __( 'Check this box if you want to hide the rating form on submit.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION => array(
						'title' 	=> __( 'Hide Post Meta Box', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION,
								'label' 	=> __( 'Check this box if you want to hide the post meta box by default.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION => array(
						'title' 	=> __( 'Disallowed User Roles', 'multi-rating-pro' ),
						'callback' 	=> 'field_disallowed_user_roles',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced'
				),
				MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION => array(
						'title' 	=> __( 'Template Strip Newlines', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION,
								'label' 	=> sprintf( __( 'Some plugins convert newlines to HTML paragraphs similar to <a href="%s">wpautop</a> (e.g. Visual Composer). Check this box if you want to prevent this from happening by stripping the newlines from the Multi Rating Pro templates.', 'multi-rating-pro' ), 'https://codex.wordpress.org/Function_Reference/wpautop' )
						)
				),
				MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION => array(
						'title' 	=> __( 'Use Minified Assets', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION,
								'label' 		=> __( 'Check this box if you want to use minified CSS and JavaScript files.', 'multi-rating-pro' )
						)
				),
				/*MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION => array(
						'title' 	=> __( 'Auto Purge Cache', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION,
								'label' 		=> __( 'If you are using W3TC, WP Super Cache, WP Fastest Cache or WP Engine, check this box if you want to automatcally purge the cache whenever a rating entry is saved, updated or deleted., ', 'multi-rating-pro' )
						)
				),*/
				MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION => array(
						'title' 	=> __( 'Disable Custom Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB,
						'section' 	=> 'section_advanced',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::ADVANCED_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION,
								'label' 		=> __( 'Check this box to disable the custom text settings. This will allow language translation of the default source text.', 'multi-rating-pro' )
						)
				),
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], isset( $setting_data['args'] ) ? $setting_data['args'] : array() );
		}
	}


	/**
	 * Advanced section description
	 */
	function section_advanced_desc() {
	}


	/**
	 * Sanitize the advanced settings
	 *
	 * @param $input
	 * @return $input
	 */
	function sanitize_advanced_settings( $input ) {

		if ( isset( $input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] ) && $input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SORT_WP_QUERY_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] ) && $input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::SORT_COMMENT_QUERY_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] ) && $input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::HIDE_RATING_FORM_SUBMIT_OPTION] = false;
		}

		if ( ! isset( $input[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION] ) ) {
			$input[MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_OPTION] = array();
		}

		if ( isset( $input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] ) && $input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::HIDE_POST_META_BOX_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] ) && $input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::TEMPLATE_STRIP_NEWLINES_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] ) && $input[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::USE_MINIFIED_ASSETS_OPTION] = false;
		}

		/*if ( isset( $input[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] ) && $input[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::AUTO_PURGE_CACHE_OPTION] = false;
		}*/

		if ( isset( $input[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] ) && $input[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION] = false;
		}

		return $input;
	}

	/**
	 * Auto placement settings
	 */
	function register_auto_placement_settings() {

		register_setting( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS, array( &$this, 'sanitize_auto_placement_settings' ) );

		add_settings_section( 'section_auto_placement', null, array( &$this, 'section_auto_placement_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB );
		add_settings_section( 'section_comment_form', __( 'Comment Form', 'multi-rating-pro'), array( &$this, 'section_comment_form_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB );
		add_settings_section( 'section_comment_text', __( 'Comment Text', 'multi-rating-pro'), array( &$this, 'section_comment_text_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB );

		$post_types = get_post_types( array(
				'public' => true,
				'show_ui' => true
		), 'objects' );

		$post_type_checkboxes = array();
		foreach ( $post_types as $post_type ) {
			array_push( $post_type_checkboxes, array(
					'name' => $post_type->name,
					'label' => $post_type->labels->name
			) );
		}

		$setting_fields = array(
				MRP_Multi_Rating::RATING_FORM_POSITION_OPTION => array(
						'title' 	=> __( 'Rating Form Position', 'multi-rating-pro' ),
						'callback' 	=> 'field_select',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_FORM_POSITION_OPTION,
								'label' 		=> __( 'Default rating form position on a post. You can integrate ratings in the WordPress comment form.', 'multi-rating-pro' ),
								'select_options' => array(
										'do_not_show' 		=> __( 'Do not show', 'multi-rating-pro' ),
										'before_content'	=> __( 'Before content', 'multi-rating-pro' ),
										'after_content'		=> __( 'After content', 'multi-rating-pro' ),
										'comment_form'		=> __( 'Comment form', 'multi-rating-pro' )
								)
						)
				),
				MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION => array(
						'title' 	=> __( 'Rating Result Position', 'multi-rating-pro' ),
						'callback' 	=> 'field_select',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_RESULTS_POSITION_OPTION,
								'label' 		=> __( 'Default rating results position on a post.', 'multi-rating-pro' ),
								'select_options' => array(
										'do_not_show' 		=> __( 'Do not show', 'multi-rating-pro' ),
										'before_title'	=> __( 'Before title', 'multi-rating-pro' ),
										'after_title'		=> __( 'After title', 'multi-rating-pro' ),
										'before_content'	=> __( 'Before content', 'multi-rating-pro' ),
										'after_content'		=> __( 'After content', 'multi-rating-pro' ),
								)
						)
				),
				MRP_Multi_Rating::POST_TYPES_OPTION => array(
						'title' 	=> __( 'Post Types', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkboxes',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::POST_TYPES_OPTION,
								'description' 	=> __( 'Enable post types for auto placement of the rating form and rating results.', 'multi-rating-pro' ),
								'checkboxes' 	=> $post_type_checkboxes
						)
				),
				MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE => array(
						'title' 	=> __( 'Exclude Home Page', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE,
								'label' 		=> sprintf( __( 'Check this box if you want to disable auto placement on the home page. See %s function.', 'multi-rating-pro' ), '<a href="https://codex.wordpress.org/Function_Reference/is_home">is_home()</a>' )
						)
				),
				MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES => array(
						'title' 	=> __( 'Exclude Archive Pages', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES,
								'label' 		=> sprintf( __( 'Check this box if you want to disable auto placement on archive pages. An archive page includes a Category, Tag, Author or a Date based pages. See %s function.', 'multi-rating-pro' ),
				'<a href="https://codex.wordpress.org/Function_Reference/is_archive">is_archive()</a>' )
						)
				),
				MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS => array(
						'title' 	=> __( 'Exclude Search Results', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_auto_placement',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS,
								'label' 		=> sprintf( __( 'Check this box if you want to disable auto placement on search results. See %s function.', 'multi-rating-pro' ), '<a href="http://codex.wordpress.org/Function_Reference/is_search">is_search()</a>' )
						)
				),
				MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION => array(
						'title' 	=> __( 'Optional Ratings', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_form',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION,
								'label' 		=> __( 'Check this box if you want to make ratings optional in the comment form.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION => array(
						'title' 	=> __( 'Include Rating Default', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_form',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION,
								'label' 		=> __( 'Check this box if you want to default the include rating checkbox in the comment form to checked.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION => array(
						'title' 	=> __( 'Show Overall Rating', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_text',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION,
								'label' 		=> __( 'Check this box if you want to show the overall rating in the comment text.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION => array(
						'title' 	=> __( 'Show Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_text',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION,
								'label' 		=> __( 'Check this box if you want to show the review title.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION => array(
						'title' 	=> __( 'Show Rating Items', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_text',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION,
								'label' 		=> __( 'Check this box if you want to show rating items in the comment text.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION => array(
						'title' 	=> __( 'Show Custom Fields', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB,
						'section' 	=> 'section_comment_text',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION,
								'label' 		=> __( 'Check this box if you want to show custom fields comment text.', 'multi-rating-pro' )
						)
				)
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], $setting_data['args'] );
		}
	}

	/**
	 * Auto placement section description
	 */
	function section_auto_placement_desc() {
	}

	/**
	 * Comment form section description
	 */
	function section_comment_form_desc() {
		?><p><?php printf( __( 'WordPress comment form settings. See %s function.', 'multi-rating-pro' ), '<a href="https://codex.wordpress.org/Function_Reference/comment_form">comment_form()</a>' ); ?></p><?php
	}

	/**
	 * Comment text section description
	 */
	function section_comment_text_desc() {
		?><p><?php printf( __( 'Settings to show ratings in the comment text if submitted via the WordPress comment form. See %s function.', 'multi-rating-pro' ), '<a href="https://codex.wordpress.org/Function_Reference/comment_text">comment_text()</a>' ); ?></p><?php
	}

	/**
	 * Sanitize auto placement settings
	 */
	function sanitize_auto_placement_settings( $input ) {

		if ( ! isset( $input[MRP_Multi_Rating::POST_TYPES_OPTION] ) ) {
			$input[MRP_Multi_Rating::POST_TYPES_OPTION] = array();
		}

		if ( isset( $input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] ) && $input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] == 'true' ) {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] = true;
		} else {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_HOME_PAGE] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] ) && $input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] == 'true' ) {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] = true;
		} else {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_ARCHIVE_PAGES] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] ) && $input[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] == 'true' ) {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] = true;
		} else {
			$input[MRP_Multi_Rating::FILTER_EXCLUDE_SEARCH_RESULTS] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_FORM_OPTIONAL_RATING_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_FORM_INCLUDE_RATING_DEFAULT_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_SHOW_OVERALL_RATING_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_SHOW_RATING_ITEMS_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_SHOW_TITLE_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] ) && $input[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::COMMENT_SHOW_CUSTOM_FIELDS_OPTION] = false;
		}

		return $input;
	}

	/**
	 * Style settings
	 */
	function register_styles_settings() {

		register_setting( MRP_Multi_Rating::STYLES_SETTINGS, MRP_Multi_Rating::STYLES_SETTINGS, array( &$this, 'sanitize_style_settings' ) );

		add_settings_section( 'section_styles', null, array( &$this, 'section_styles_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB );
		add_settings_section( 'section_custom_images', __( 'Custom Images', 'multi-rating-pro' ), array( &$this, 'section_custom_images_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB );


		$icon_font_library_options = array(
				'font-awesome-4.7.0'		=> __( 'Font Awesome 4.7.0', 'multi-rating-pro' ),
				'font-awesome-4.6.3'		=> __( 'Font Awesome 4.6.3', 'multi-rating-pro' ),
				'font-awesome-4.5.0'		=> __( 'Font Awesome 4.5.0', 'multi-rating-pro' ),
				'font-awesome-4.3.0' 		=> __( 'Font Awesome 4.3.0', 'multi-rating-pro' ),
				'font-awesome-4.2.0'		=> __( 'Font Awesome 4.2.0', 'multi-rating-pro' ),
				'font-awesome-4.1.0' 		=> __( 'Font Awesome 4.1.0', 'multi-rating-pro' ),
				'font-awesome-4.0.3' 		=> __( 'Font Awesome 4.0.3', 'multi-rating-pro' ),
				'font-awesome-3.2.1' 		=> __( 'Font Awesome 3.2.1', 'multi-rating-pro' ),
				'dashicons' 				=> __( 'Dashicons', 'multi-rating-pro' )
		);
		$icon_font_library_options = apply_filters( 'mrp_icon_font_library_options', $icon_font_library_options );

		$setting_fields = array(
				MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION => array(
						'title' 	=> __( 'Primary Color', 'multi-rating-pro' ),
						'callback' 	=> 'field_color_picker',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::STAR_RATING_COLOUR_OPTION,
								'label'			=> __( 'Choose a color for selection.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION => array(
						'title' 	=> __( 'Secondary Color', 'multi-rating-pro' ),
						'callback' 	=> 'field_color_picker',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::STAR_RATING_HOVER_COLOUR_OPTION,
								'label'			=> __( 'Choose a color for on hover.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION => array(
						'title' 	=> __( 'Error Color', 'multi-rating-pro' ),
						'callback' 	=> 'field_color_picker',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::ERROR_MESSAGE_COLOUR_OPTION,
								'label'			=> __( 'Choose a color to highlight errors.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION => array(
						'title' 	=> __( 'Icon Font Library', 'multi-rating-pro' ),
						'callback' 	=> 'field_select',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::ICON_FONT_LIBRARY_OPTION,
								'label' 		=> null,
								'select_options' => $icon_font_library_options
						)
				),
				MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION => array(
						'title' 	=> __( 'Load Icon Font Library from CDN', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION,
								'label' 		=> __( 'Check this box if you want to load the font icon library from a CDN.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::CUSTOM_CSS_OPTION => array(
						'title' 	=> __( 'Custom CSS', 'multi-rating-pro' ),
						'callback' 	=> 'field_textarea',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_CSS_OPTION,
								'footer' 		=> __( 'Enter custom CSS styles above.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::DISABLE_STYLES_OPTION => array(
						'title' 	=> __( 'Disable Styles', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_styles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DISABLE_STYLES_OPTION,
								'label' 		=> __( 'Check this box to disable loading the plugin\'s CSS file.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES => array(
						'title' 	=> __( 'Enable Custom Images', 'multi-rating-pro' ),
						'callback' 	=> 'field_checkbox',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES,
								'label' 		=> __( 'Check this box if you want to enable custom images.', 'multi-rating-pro' )
						)
				),
				MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE => array(
						'title' 	=> __( 'Full Star Image', 'multi-rating-pro' ),
						'callback' 	=> 'field_upload',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE,
								'input_id'		=> 'custom-full-star-img',
								'button_id' 	=> 'custom-full-star-img-upload-btn',
								'preview_img_id' => 'custom-full-star-img-preview'
						)
				),
				MRP_Multi_Rating::CUSTOM_HALF_STAR_IMAGE => array(
						'title' 	=> __( 'Half Star Image', 'multi-rating-pro' ),
						'callback' 	=> 'field_upload',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_HALF_STAR_IMAGE,
								'input_id'		=> 'custom-half-star-img',
								'button_id' 	=> 'custom-half-star-img-upload-btn',
								'preview_img_id' => 'custom-half-star-img-preview'
						)
				),
				MRP_Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE => array(
						'title' 	=> __( 'Empty Star Image', 'multi-rating-pro' ),
						'callback' 	=> 'field_upload',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE,
								'input_id'		=> 'custom-empty-star-img',
								'button_id' 	=> 'custom-empty-star-img-upload-btn',
								'preview_img_id' => 'custom-empty-star-img-preview'
						)
				),
				MRP_Multi_Rating::CUSTOM_HOVER_STAR_IMAGE => array(
						'title' 	=> __( 'Hover Star Image', 'multi-rating-pro' ),
						'callback' 	=> 'field_upload',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_HOVER_STAR_IMAGE,
								'input_id'		=> 'custom-hover-star-img',
								'button_id' 	=> 'custom-hover-star-img-upload-btn',
								'preview_img_id' => 'custom-hover-star-img-preview'
						)
				),
				MRP_Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH => array(
						'title' 	=> __( 'Star Image Width', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH,
								'label' 		=> __( 'pixels', 'multi-rating-pro' ),
								'class'			=> 'small-text',
								'type'			=> 'number'
						)
				),
				MRP_Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT => array(
						'title' 	=> __( 'Star Image Height', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB,
						'section' 	=> 'section_custom_images',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::STYLES_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT,
								'label' 		=> __( 'pixels', 'multi-rating-pro' ),
								'class'			=> 'small-text',
								'type'			=> 'number'
						)
				),
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], $setting_data['args'] );
		}
	}


	/**
	 * Styles section description
	 */
	function section_styles_desc() {
	}

	/**
	 * Custom images description
	 */
	function section_custom_images_desc() {
		?><p><?php _e( 'Valid mime types are image/jpeg, image/png, image/bmp, image/tiff and image/x-icon.', 'multi-rating-pro' ); ?></p><?php
	}

	/**
	 * Sanitize style settings
	 */
	function sanitize_style_settings( $input ) {

		if ( isset( $input[MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION] ) && $input[MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::LOAD_ICON_FONT_LIBRARY_OPTION] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] ) && $input[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] == 'true' ) {
			$input[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] = true;
		} else {
			$input[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] = false;
		}

		if ( isset( $input[MRP_Multi_Rating::DISABLE_STYLES_OPTION] ) && $input[MRP_Multi_Rating::DISABLE_STYLES_OPTION] == 'true' ) {
			$input[MRP_Multi_Rating::DISABLE_STYLES_OPTION] = true;
		} else {
			$input[MRP_Multi_Rating::DISABLE_STYLES_OPTION] = false;
		}

		$input[MRP_Multi_Rating::CUSTOM_CSS_OPTION] = addslashes( $input[MRP_Multi_Rating::CUSTOM_CSS_OPTION] );

		if ( $input[MRP_Multi_Rating::USE_CUSTOM_STAR_IMAGES] ) {

			// make sure at least full, half and empty star images exist and are valid URL's
			if ( filter_var( $input[MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE], FILTER_VALIDATE_URL ) === false
					|| filter_var( $input[MRP_Multi_Rating::CUSTOM_HALF_STAR_IMAGE], FILTER_VALIDATE_URL ) === false
					|| filter_var( $input[MRP_Multi_Rating::CUSTOM_EMPTY_STAR_IMAGE], FILTER_VALIDATE_URL ) === false ) {
				add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'validation_error_custom_images', __( 'Full star, half star and empty star custom images are required.', 'multi-rating-pro' ), 'error' );
			}

			// check file types
			$valid_file_mime_types = array(
					'image/jpeg',
					'image/gif',
					'image/png',
					'image/bmp',
					'image/tiff',
					'image/x-icon'
			);

			if ( isset( $input[MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE] ) && strlen( $input[MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE] ) > 0 ) {

				$file_mime_type = wp_check_filetype( $input[MRP_Multi_Rating::CUSTOM_FULL_STAR_IMAGE] );

				if ( ! in_array( $file_mime_type['type'], $valid_file_mime_types) ) {
					add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'invalid_mime_type', __( 'Invalid image format. Valid mime types: image/jpeg, image/png, image/bmp, image/tiff and image/x-icon', 'multi-rating-pro' ), 'error' );
				}
			}

			// check image height and width are valid numbers within 1 and 128
			$custom_image_height = $input[MRP_Multi_Rating::CUSTOM_STAR_IMAGE_HEIGHT];
			$custom_image_width = $input[MRP_Multi_Rating::CUSTOM_STAR_IMAGE_WIDTH];

			if ( ! is_numeric( $custom_image_height ) ) {
				add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'non_numeric_custom_image_height', __( 'Custom image height must be numeric.', 'multi-rating-pro' ), 'error' );
			} else if ( intval($custom_image_height ) < 1 || intval( $custom_image_height ) > 128 ) {
				add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'range_error_custom_image_height', __( 'Custom image height cannot be less than 1 or greater than 128.', 'multi-rating-pro' ), 'error' );
			}

			if ( ! is_numeric( $custom_image_width ) ) {
				add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'non_numeric_custom_image_width', __( 'Custom image width must be numeric.', 'multi-rating-pro' ), 'error' );
			} else if ( $custom_image_width < 1 || $custom_image_width > 128 ) {
				add_settings_error( MRP_Multi_Rating::STYLES_SETTINGS, 'range_error_custom_image_width', __( 'Custom image width cannot be less than 1 or greater than 128.', 'multi-rating-pro' ), 'error' );
			}
		}

		return $input;

	}

	/**
	 * Custom Text settings
	 */
	function register_custom_text_settings() {

		register_setting( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS, array( &$this, 'sanitize_custom_text_settings' ) );

		add_settings_section( 'section_titles', __( 'Titles', 'multi-rating-pro' ), array( &$this, 'section_titles_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB );
		add_settings_section( 'section_labels', __( 'Labels', 'multi-rating-pro' ), array( &$this, 'section_labels_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB  );
		add_settings_section( 'section_messages', __( 'Messages', 'multi-rating-pro' ), array( &$this, 'section_messages_desc' ), MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB  );

		$disable_custom_text = $this->advanced_settings[MRP_Multi_Rating::DISABLE_CUSTOM_TEXT_OPTION];

		$setting_fields = array(
				MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION => array(
						'title' 	=> __( 'Rating Form Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_titles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_FORM_TITLE_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION => array(
						'title' 	=> __( 'Ratings List Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_titles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_RESULTS_LIST_TITLE_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::RATING_ENTRIES_LIST_TITLE_TEXT_OPTION => array(
						'title' 	=> __( 'Rating Entries List Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_titles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_ENTRIES_LIST_TITLE_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION => array(
						'title' 	=> __( 'User Ratings Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_titles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::USER_RATING_RESULTS_TITLE_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION => array(
						'title' 	=> __( 'User Ratings Dashboard Title', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_titles',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::USER_RATINGS_DASHBOARD_TITLE_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION => array(
						'title' 	=> __( 'Submit Button Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_labels',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SUBMIT_RATING_FORM_BUTTON_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION => array(
						'title' 	=> __( 'Update Button Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_labels',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::UPDATE_RATING_FORM_BUTTON_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION => array(
						'title' 	=> __( 'Delete Button Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_labels',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DELETE_RATING_FORM_BUTTON_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION => array(
						'title' 	=> __( 'Filter Button Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_labels',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FILTER_BUTTON_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION => array(
						'title' 	=> __( 'Filter Label Text', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_labels',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FILTER_LABEL_TEXT_OPTION,
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION => array(
						'title' 	=> __( 'Field Required Error Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::FIELD_REQUIRED_ERROR_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::RATING_AWAITING_MODERATION_MESSAGE_OPTION => array(
						'title' 	=> __( 'Awaiting Moderation Information Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::RATING_AWAITING_MODERATION_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION => array(
						'title' 	=> __( 'Allow Anonymous Error Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::ALLOW_ANONYMOUS_RATINGS_ERROR_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::SAVE_RATING_RESTRICTION_ERROR_MESSAGE_OPTION => array(
						'title' 	=> __( 'Duplicate Check Error Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SAVE_RATING_RESTRICTION_ERROR_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION => array(
						'title' 	=> __( 'No Ratings Information Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::NO_RATING_RESULTS_TEXT_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION => array(
						'title' 	=> __( 'Existing Rating Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::EXISTING_RATING_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION => array(
						'title' 	=> __( 'Submit Rating Success Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::SUBMIT_RATING_SUCCESS_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION => array(
						'title' 	=> __( 'Update Rating Success Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::UPDATE_RATING_SUCCESS_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION => array(
						'title' 	=> __( 'Delete Rating Success Message', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DELETE_RATING_SUCCESS_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				),
				MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_ERROR_MESSAGE_OPTION => array(
						'title' 	=> __( 'Disallowed User Roles Submit Rating Error Message.', 'multi-rating-pro' ),
						'callback' 	=> 'field_input',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB,
						'section' 	=> 'section_messages',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::DISALLOWED_USER_ROLES_RATINGS_ERROR_MESSAGE_OPTION,
								'class'			=> 'large-text',
								'readonly'		=> $disable_custom_text
						)
				)
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {

			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], $setting_data['args'] );
		}
	}

	/**
	 * Custom text section description
	 */
	function section_titles_desc() {
		echo '<p>' . __( 'Shortcode titles, widgets titles etc...' , 'multi-rating-pro' ) . '</label>';
	}

	/**
	 * Custom text section description
	 */
	function section_labels_desc() {
		echo '<p>' . __( 'Buttons, field labels etc...' , 'multi-rating-pro' ) . '</label>';
	}

	/**
	 * Custom text section description
	 */
	function section_messages_desc() {
		echo '<p>' . __( 'Validation error messages, information messages etc...' , 'multi-rating-pro' ) . '</label>';
	}

	/**
	 * Custom text section description
	 */
	function section_misc_desc() {
	}


	/**
	 * Sanitize custom text settings
	 */
	function sanitize_custom_text_settings( $input ) {
		return $input;
	}

	/**
	 * License settings
	 */
	function register_license_settings() {

		register_setting( MRP_Multi_Rating::LICENSE_SETTINGS, MRP_Multi_Rating::LICENSE_SETTINGS, array( &$this, 'sanitize_license_settings' ) );

		$settings_sections = (array) apply_filters( 'mrp_license_settings_sections', array( array(
				'id' => 'section_mrp_license',
				'title' => __( 'Multi Rating Pro', 'multi-rating-pro' ),
				'callback' => 'section_license_desc',
				'page' => MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::LICENSES_SETTINGS_TAB
		) ) );

		foreach ( $settings_sections as $settings_section ) {
			// $id, $title, $callback, $page
			add_settings_section( $settings_section['id'], $settings_section['title'], array( $this, $settings_section['callback'] ), $settings_section['page'] );
		}

		$setting_fields = apply_filters( 'mrp_license_setting_fields', array(
				MRP_Multi_Rating::LICENSE_KEY_OPTION => array(
						'title' 	=> __( 'License Key', 'multi-rating-pro' ),
						'callback' 	=> 'field_license_key',
						'page' 		=> MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::LICENSES_SETTINGS_TAB,
						'section' 	=> 'section_mrp_license',
						'args' => array(
								'option_name' 	=> MRP_Multi_Rating::LICENSE_SETTINGS,
								'setting_id' 	=> MRP_Multi_Rating::LICENSE_KEY_OPTION,
								'label' 		=> __( 'Enter your license key.', 'multi-rating-pro' )
						),
						'item_name' => urlencode( 'Multi Rating Pro' ), // the name of our product in EDD
				) )
		);

		foreach ( $setting_fields as $setting_id => $setting_data ) {
			// $id, $title, $callback, $page, $section, $args
			add_settings_field( $setting_id, $setting_data['title'], array( $this, $setting_data['callback'] ), $setting_data['page'], $setting_data['section'], $setting_data['args'] );
		}
	}

	function section_license_desc() {
	}

	/**
	 * Sanitizes the license option
	 */
	function sanitize_license_settings( $new_settings ) {

		$old_settings = (array) get_option( MRP_Multi_Rating::LICENSE_SETTINGS );

		$license_items = apply_filters( 'mrp_license_items', array(
				MRP_Multi_Rating::LICENSE_KEY_OPTION => urlencode( 'Multi Rating Pro' ) // the name of our product in EDD
		) );

		foreach ( $license_items as $license_key_name => $item_name ) {

			$old_license_key = $old_settings[$license_key_name];
			$new_license_key = $new_settings[$license_key_name];

			if ( $old_license_key && $old_license_key != $new_license_key ) {
				delete_option( $license_key_name . '_status' ); // new license has been entered, so must reactivate
			}

		}

		return $new_settings;
	}

	/**
	 * Activates the license key
	 */
	function activate_license() {

		$license_items = apply_filters( 'mrp_license_items', array(
				MRP_Multi_Rating::LICENSE_KEY_OPTION => urlencode( 'Multi Rating Pro' ) // the name of our product in EDD
		) );

		foreach ( $license_items as $license_key_name => $item_name ) {

			// listen for our activate button to be clicked
			if ( isset( $_POST[$license_key_name . '_activate'] ) ) {

				// run a quick security check
				if ( ! check_admin_referer( 'mrp_license_nonce', 'mrp_license_nonce' ) ) {
					return; // get out if we didn't click the Activate button
				}

				// retrieve the license from the database
				$license_settings = (array) get_option( MRP_Multi_Rating::LICENSE_SETTINGS );
				$license_key = isset( $license_settings[$license_key_name] ) ? trim( $license_settings[$license_key_name] ) : '';

				// data to send in our API request
				$api_params = array(
						'edd_action' => 'activate_license',
						'license'    => $license_key,
						'item_name'  => $item_name,
						'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_get( add_query_arg( $api_params, 'https://multiratingpro.com' ), array( 'timeout' => 15, 'sslverify' => false ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.', 'multi-rating-pro' );
					}

				} else {

					$license_data = json_decode( wp_remote_retrieve_body( $response ) );

					if ( false === $license_data->success ) {

						switch( $license_data->error ) {

							case 'expired' :

								$message = sprintf(
								__( 'Your license key expired on %s.', 'multi-rating-pro' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
								);
								break;

							case 'revoked' :

								$message = __( 'Your license key has been disabled.', 'multi-rating-pro' );
								break;

							case 'missing' :

								$message = __( 'Invalid license.', 'multi-rating-pro' );
								break;

							case 'invalid' :
							case 'site_inactive' :

								$message = __( 'Your license is not active for this URL.', 'multi-rating-pro' );
								break;

							case 'item_name_mismatch' :

								$message = sprintf( __( 'This appears to be an invalid license key for %s.', 'multi-rating-pro' ), 'Multi Rating Pro' );
								break;

							case 'no_activations_left':

								$message = __( 'Your license key has reached its activation limit.', 'multi-rating-pro' );
								break;

							default :

								$message = __( 'An error occurred, please try again.', 'multi-rating-pro' );
								break;
						}

					}

				}

				// Check if anything passed on a message constituting a failure
				if ( ! empty( $message ) ) {
					$base_url = admin_url( 'admin.php?page=' . MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::LICENSES_SETTINGS_TAB );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// $license_data->license will be either "active" or "inactive"
				update_option( $license_key_name . '_status', $license_data->license );
			}
		}
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	function sl_admin_notices() {

		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch( $_GET['sl_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					break;

			}
		}
	}

	/**
	 *  Deactivates the license key. This will descrease the site count
	 */
	function deactivate_license() {

		$license_items = apply_filters( 'mrp_license_items', array(
				MRP_Multi_Rating::LICENSE_KEY_OPTION => urlencode( 'Multi Rating Pro' ) // the name of our product in EDD
		) );

		foreach ( $license_items as $license_key_name => $item_name ) {

			// listen for our activate button to be clicked
			if ( isset( $_POST[$license_key_name . '_deactivate'] ) ) {

				// run a quick security check
				if ( ! check_admin_referer( 'mrp_license_nonce', 'mrp_license_nonce' ) ) {
					return; // get out if we didn't click the Activate button
				}

				// retrieve the license from the database
				$license_settings = (array) get_option( MRP_Multi_Rating::LICENSE_SETTINGS );
				$license_key = isset( $license_settings[$license_key_name] ) ? trim( $license_settings[$license_key_name] ) : '';

				// data to send in our API request
				$api_params = array(
						'edd_action' => 'deactivate_license',
						'license'    => $license_key,
						'item_name'  => $item_name,
						'url'        => home_url()
				);

				// Call the custom API.
				$response = wp_remote_post( 'https://multiratingpro.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

				// make sure the response came back okay
				if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

					if ( is_wp_error( $response ) ) {
						$message = $response->get_error_message();
					} else {
						$message = __( 'An error occurred, please try again.', 'multi-rating-pro' );
					}

					$base_url = admin_url( 'admin.php?page=' . MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::LICENSES_SETTINGS_TAB );
					$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

					wp_redirect( $redirect );
					exit();
				}

				// decode the license data
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				// $license_data->license will be either "deactivated" or "failed"
				if ( $license_data->license == 'deactivated' ) {
					delete_option( $license_key_name . '_status' );
				}
			}
		}
	}

	/**
	 * Checkbox setting
	 */
	function field_checkbox( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		?>
		<input type="checkbox" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]" value="true" <?php checked( true, isset( $settings[$args['setting_id']] ) ? $settings[$args['setting_id']] : false , true ); ?> />
		<?php
		if ( isset( $args['label'] ) ) { ?>
			<label><?php echo $args['label']; ?></label>
		<?php }
	}

	/**
	 * Checkbox setting
	 */
	function field_input( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		$class = isset( $args['class'] ) ? $args['class'] : 'regular-text';
		$type = isset( $args['type'] ) ? $args['type'] : 'text';
		$min = isset( $args['min'] ) && is_numeric( $args['min'] ) ? intval( $args['min'] ) : null;
		$max = isset( $args['max'] ) && is_numeric( $args['max'] ) ? intval( $args['max'] ) : null;
		$readonly = isset( $args['readonly'] ) && $args['readonly'] ? ' readonly' : '';
		?>
		<input class="<?php echo $class; ?>" type="<?php echo $type; ?>" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]"
				value="<?php echo $settings[$args['setting_id']]; ?>" <?php if ( $min !== null ) { echo ' min="' . $min . '"'; } ?>
				<?php if ( $max !== null) { echo ' max="' . $max . '"'; } echo $readonly; ?>/>
		<?php
		if ( isset( $args['label'] ) ) { ?>
			<label><?php echo $args['label']; ?></label>
		<?php }
	}

	/**
	 * Upload setting
	 */
	function field_upload( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		$button_id = isset( $args['button_id'] ) ? $args['button_id'] : '';
		$input_id = isset( $args['input_id'] ) ? $args['input_id'] : '';
		$preview_img_id = isset( $args['preview_img_id'] ) ? $args['preview_img_id'] : '';

		?>
		<input type="url" id="<?php echo $input_id; ?>" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]" value="<?php echo $settings[$args['setting_id']]; ?>" readonly class="regular-text" />
		<input type="submit" id="<?php echo $button_id; ?>" class="button" value="<?php _e( 'Upload', 'multi-rating-pro' ); ?>">
		<img src="<?php if ( strlen( $settings[$args['setting_id']] ) > 0 ) echo $settings[$args['setting_id']]; ?>" id="<?php echo $preview_img_id; ?>" style="margin-top: 5px; <?php if ( strlen( $settings[$args['setting_id']] ) == 0 ) { echo 'display: none;'; } else { echo 'display: block;'; } ?>" />
		<?php
	}

	/**
	 *
	 */
	function field_textarea( $args ) {
		$settings = (array) get_option( $args['option_name' ] );

		if ( isset( $args['label'] ) ) { ?>
			<p><?php echo $args['label']; ?></p><br />
		<?php } ?>
		<textarea name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]" rows="5" cols="75"><?php echo $settings[$args['setting_id']]; ?></textarea>
		<?php
		if ( isset( $args['footer'] ) ) { ?>
			<p><?php echo $args['footer']; ?></p><br />
		<?php }
	}

	/**
	 * Editor field
	 *
	 * @param unknown $args
	 */
	function field_editor( $args ) {

		$settings = (array) get_option( $args['option_name' ] );

		if ( ! empty( $args['label' ] ) ) {
			?>
			<p><?php echo $args['label']; ?></p><br />
			<?php
		}

		wp_editor( $settings[$args['setting_id']], $args['setting_id'], array(
				'textarea_name' => $args['option_name' ] . '[' . $args['setting_id'] . ']',
				'editor_class' => ''
		) );

		echo ( ! empty( $args['footer'] ) ) ? '<br/><p class="description">' . $args['footer'] . '</p>' : '';
	}

	/**
	 * Color picker field
	 *
	 * @param unknown $args
	 */
	function field_color_picker( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		?>
		<input type="text" class="color-picker" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]" value="<?php echo $settings[$args['setting_id']]; ?>" />
		<?php if ( isset( $args['label' ] ) ) { ?>
			<p><?php echo $args['label']; ?></p>
		<?php }
	}

	/**
	 * Color picker field
	 *
	 * @param unknown $args
	 */
	function field_select( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		$value = $settings[$args['setting_id']];
		?>
		<select name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]">
			<?php
			foreach ( $args['select_options'] as $option_value => $option_label ) {
				$selected = '';
				if ( $value == $option_value ) {
					$selected = 'selected="selected"';
				}
				echo '<option value="' . $option_value . '" ' . $selected . '>' . $option_label . '</option>';
			}
			?>
		</select>
		<?php
		if ( isset( $args['label'] ) ) { ?>
			<label><?php echo $args['label']; ?></label>
		<?php }
	}

	/**
	 * Checkboxes field
	 *
	 * @param unknown $args
	 */
	function field_checkboxes( $args ) {
		$settings = (array) get_option( $args['option_name' ] );
		$value = $settings[$args['setting_id']];

		foreach ( $args['checkboxes'] as $checkbox ) {

			$checked = '';
			if ( is_array( $value ) ) {
				if ( in_array( $checkbox['name'], $value ) ) {
					$checked = 'checked="checked"';
				}
			} else if ( $checkbox['name'] == $value ) {
				$checked = 'checked="checked"';
			}

			?>
			<input type="checkbox" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>][]" value="<?php echo $checkbox['name']; ?>" <?php echo $checked; ?> />
			<label class="checkbox-label"><?php echo $checkbox['label']; ?></label>
			<?php
		}

		if ( isset( $args['description'] ) ) {
			?>
			<p><?php echo $args['description']; ?></p>
			<?php
		}
	}

	/**
	 * License key field with activate / deactivate button
	 *
	 * @param unknown $args
	 */
	function field_license_key( $args ) {

		$settings = (array) get_option( $args['option_name' ] );
		$class = isset( $args['class'] ) ? $args['class'] : 'regular-text';

		$license_key = isset( $settings[$args['setting_id']] ) ? $settings[$args['setting_id']] : '';
		$license_status = get_option( $args['setting_id'] . '_status' );

		?>
		<input class="<?php echo $class; ?>" type="text" name="<?php echo $args['option_name']; ?>[<?php echo $args['setting_id']; ?>]" value="<?php echo $license_key; ?>" />
		<?php
		if ( isset( $args['label'] ) ) {
			?>
			<label><?php echo $args['label']; ?></label>
			<?php
		}

		if ( strlen( trim( $license_key ) ) > 0 ) { ?>
			<p>
				<?php
		 		if ( $license_status !== false && $license_status == 'valid' ) {
		 			?>
					<span style="color: green; vertical-align: middle;"><?php _e( 'Active', 'multi-rating-pro' ); ?> </span>
					<input type="submit" class="button-secondary" name="<?php echo $args['setting_id']; ?>_deactivate" value="<?php _e( 'Deactivate License', 'multi-rating-pro' ); ?>" />
					<?php
		 		} else {
					?>
					<input type="submit" class="button-secondary" name="<?php echo $args['setting_id']; ?>_activate" value="<?php _e( 'Activate License', 'multi-rating-pro' ); ?>" />
					<?php
				}
				wp_nonce_field( 'mrp_license_nonce', 'mrp_license_nonce' );
				?>
			</p>
			<?php
		 }
	}
}

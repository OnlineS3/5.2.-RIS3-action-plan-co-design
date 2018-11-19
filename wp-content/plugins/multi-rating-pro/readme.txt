=== Multi Rating Pro ===
Contributors: dpowney
Tags: rating, multi-rating, post rating, star, multi, criteria, rich snippet, testimonial, review, hReview, multi rating, feedback, user rating, wpml, feedback, question, star rating, 5 star, font awesome
Requires at least: 4.0
Tested up to: 4.9
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A powerful rating system and review plugin for WordPress.

== Changelog ==

= 5.5 (14/04/2018) =
* Bug: Invalid parameter used when counting bulk actions on rating entries page
* Tweak: Removed IP address duplicate checking for GDPR compliance. Cookies will be the default option used instead.
* Bug: Polylang compatability issue with the wrong db table prefix used in a query
* Tweak: Scalability fix recacluating ratings

= 5.4.5 (08/04/2018) =
* Bug: Fixed Polylang compatibility for disabled post types
* Bug: Polylang & WPML compatibility fix to recalculate translated post ratings on update
* Bug: Do not show the migration tool if rating items from free version are not available

= 5.4.4 (01/04/2018) =
* Bug: Fixed Polylang issue not showing rating entries for all languages in WP-admin
* Tweak: Added new option to keep any trailing zeros in star rating and score result types e.g. 3.0/5.0
* Bug: Fixed comment moderation not working when rating items are added to the comment form
* Bug: Fixed sql_mode=only_full_group_by incompatibility issue
* Bug: Fixed post_ids bug in mrp_rating_results_list() and mrp_rating_item_results() template tags
* Bug: Fixed a space issue between two HTML element attributes in rating-form-thumbs.php template

= 5.4.3 (11/12/2017) =
* Bug: Allow private post calculated ratings to be shown in WP-admin

= 5.4.2 (28/11/2017) =
* Bug: Fixed e-mail publicly shown instead of name in the [mrp_rating_entry_details_list] shortcode

= 5.4.1 (27/11/2017) =
* Bug: Fix to allow override whether to show a review field using shortcode attributes, but only if the review field has been added to the rating form

= 5.4 (17/11/2017) =

* Bug: Enabled displaying avatars for anonymous users by updating the get_avatar() call in the rating-entry-details.php template file to use email instead of the user id.
* Tweak: Increased maximum comment length to 2000 characters
* Bug: Rating entries are now stored against the current language post id in WPML
* New: Polylang plugin compatibility added in line with WPML implementation
* Tweak: Changed API function user_rating_exists() inputs to an array of parameters. Added backwards compatibility for old API function parameters
* Bug: Fixed percentage rounding error in rating item results template options block layout
* Bug: Fixed term_id filter not working when retrieving rating entries
* Bug: Fixed slash in custom field output
* Bug: Upon plugin activation, do not redirect to about page if network admin
* Tweak: Added extra isset checks in rating-result.php template file
* Bug: Fixed default rating item results widget title
* Bug: Fixed WPML admin-texts not working
* Tweak: Replaced the mrp_disable_custom_text filter with a new option in the Advanced settings tab
* Bug: Fixed incorrect CSS class of rating results shown after a rating is saved or deleted
* Bug: Fixed WPML string translations for rating form, rating items and custom fields

= 5.3.1 (27/06/2017) =

* Bug: Fixed email headers not set correctly

= 5.3.0 (07/06/2017) =

* Bug: Update post meta fields with ratings data when ratings are found missing and are calculated
* Bug: Fixed limit & offset wrong way around in db queries
* Tweak: Moved static sequence variable from rating form class to utility class
* New: Added AJAX load more for rating entry details shortcode & widget
* Tweak: Added id to header when editig rating entry, rating form, rating item and custom fields
* Tweak: Disabled reports menu until new enhanced reports are made available
* Tweak: Added capabilities that are needed to access various plugins tools (e.g. export)
* Tweak: Modified rating-entry-details-list template file for AJAX load more and added a new rating-entry-details template file
* New: Added add author link option to Rating Entry List widget
* Tweak: Set default Limit option from 10 to 3 in Rating Entry List widget
* Tweak: Remove unused utility functions validate_rating_items_id_list() and validate_custom_fields_id_list()
* New: Added new mrp_manage_ratings, mrp_export_ratings and mrp_delete_ratings role capabilities. Added tighter security checks for role capabilities.
* Tweak: Moved edit rating screen to rating entries page slug
* New: Allow users to manually add ratings from WP-admin (only available to users with mrp_manage_ratings capability)
* Tweak: Updated language translation file
* Tweak: Added wp_trim_words() to comment text in rating entries WP-admin table
* Bug: Fixed unable to moderate ratings with long comments from the WordPress comment system

= 5.2.8 (24/04/2017) =

* Bug: Removed stray die() in code after a rating entry has been manually approved on unapproved by a moderator
* Bug: Fixed typo in filters page for auto placement of rating result
* New: Added RSS feed for rating entries named mrp_rating_entries
* Bug: Fixed IP address duplicate checks not working

= 5.2.7 (06/04/2017) =

* Bug: Added @ to set_time_limit function call to suppress errors/warnings
* Bug: Added star rating items to be able to only show text options (in case you do not want to show 0 star option totals in the [mrp_rating_item_results] shortcode)

= 5.2.6 (03/04/2017) =

* Bug: Fixed missing mrp_after_rating_form_validation_save filter which is used by the reCAPTCHA add-on
* Tweak: Fined tuned filters used for hash lookups
* New: Added the ability to reassign a rating entry to a different user in WP-admin
* New: Added the ability to edit the rating entry date in WP-admin
* Bug: Fixed translations not loading
* Tweak: Updated validation to use current_user_can( 'mrp_moderate_ratings' ) when updating or deleting rating entries

= 5.2.5 (25/02/2017) =

* New: Font Awesome 4.7.0 support
* Bug: Fixed some potential SQL injection vulnerabilities
* Bug: Fixed a couple of cross site scripting (XSS) vulnerabilities in the rating form
* Bug: Small HTML5 validation improvement in rating forms page for weights
* Bug: WPML query fix
* Bug: Fixed title not showing for rating entries list widget
* Bug: Added add_author_link shortcode attribute to the [mrp_rating_entry_details_list] shortcode
* New: Removed hover color on stars when mouse out and no stars selected
* New: Added ability to sort by oldest rating entries
* Bug: Replaced GROUP_CONTACT usage from cleanup database queries as this truncates results when column length > 1024 characters
* New: Added mrp_disable_custom_text filter to turn off custom text settings allowing language translation of strings
* Bug: Fixed title review field not saving with WordPress comment form
* Bug: Fixed incorrect custom fields values showing in CSV file when not required
* Tweak: Updated language files
* Bug: Added HTML5 required validation to review fields in WP-admin edit rating page
* Tweak: Removed count comments from the calculated ratings db table and relevent objects
* Tweak: Reordered the migration tool to the bottom of the tools page
* Tweak: Changed CSV export files to now be temporarily saved in the plugin root directory to avoid potential permission issues

= 5.2.4 (12/12/2016) =

* Bug: Fixed call to undefined function apply_filter() in template-tags.php; changed to apply_filters()

= 5.2.3 (8/12/2016) =

* Bug: Added missing validation of post id and rating form id when saving a rating entry

= 5.2.2 (19/11/2016) =

* Bug: Fixed WPML incorrect query resulting in no ratings being retrieved
* New: Added mrp_before_save_rating_entry hook
* Bug: Fixed rating moderation not working if setting to allow users to update or delete ratings is turned on
* Bug: Fixed WPML compatibility for custom post types

= 5.2.1 (18/11/2016) =

* New: Added rank to rating item results
* New: Added sort_by to rating item results for the options_inline and no_options layouts
* Bug: Fixed WPML compatibility erro due to ambiguous post_id column when retrieving rating entries
* Bug: Fixed IP address not saved correctly with a new rating entry
* Bug: Fixed rating entries being incorrectly deleted on recalculate ratings and clean the database tools whenever a rating entry has an invalid rating item value (i.e. no longer associated to a rating item)

= 5.2 (14/11/2016) =

* Bug: Fixed review field required error not disappearing after submit
* Bug: Allow weight of 0 for rating items in a rating form
* Bug: Allow post meta to use default settings for rating form
* Bug: Fix for WPML to not show ratings for posts if they have not been translated to the current language yet
* Tweak: Cleaned up the tools page under the covers
* Bug: Fixed deleting rating item from rating items page causing any rating forms which had this rating item to be in error
* Tweak: Turned off auto placement in RSS feeds by checking the is_feed() function. Note a simple way to flush an RSS feed cache is to update a post.
* Bug: Added missing published post status check in the rating results list where clause
* New: Added the ability to dynamically set the user id on a page for the [mrp_user_rating_results] shortcode via URL query string parameter
* New: Deprecated the following API functions replaced by template tags in template-tags.php: display_rating_form(), display_rating_result() display_rating_entry_details_list(), display_rating_results_list(), display_user_rating_results(), get_comment_rating_result(), get_comment_rating_form(), display_user_ratings_dashboard(), display_rating_item_results() and delete_rating_result()
* New: Refactored code to use new API functions delete_calculated_ratings() delete_orphaned_data(), reassign_user_ratings(), save_rating_form(), delete_rating_form(), save_rating_entry() and delete_rating_entry()
* Tweak: Removed a couple of self:: with MRP_Multi_Rating::
* Bug: Added dashicons thumbs up and thumbs down support
* Tweak: Display star ratings in admin as dashicons
* Tweak: Added localized strings used in JavaScript
* Tweak: Moved JavaScript functions outside of jQuery ready so that they are available earlier
* Tweak: Removed mrp_rating_results_html filter and replaced with a mrp_rating_result_callback filter so you can replace the function which create the HTML for auto placement of the rating results
* New: Added mrp_rating_form_callback filter so you can replace the function which creates them HTML for auto placement of the rating form
* Tweak: Changed default decimal places from 2 to 1
* Tweak: Updated parameters passed to mrp_auto_approve_ratings filter, mrp_after_save_rating_success action and mrp_after_delete_rating_success action to use a rating entry array.
* New: Added mrp_templates_dir filter to modify the template directory
* Bug: Fixed widgets not saving the rating form option correctly
* Bug: Fixed missing stripslashes() on custom field values
* Bug: Improved sanitization of custom fields using wp_kses() to only allow certain HTML tags
* Bug: Fixed custom field values not showing in email notifications
* Bug: Added missing show_count shortcode attribute to the [mrp_rating_item_results] shortcode which is used only for the no_options layout
* New: Added after_count and before_count shortcode attributes to the [mrp_rating_item_results] shortcode
* Important: Removed API functions calculate_rating_entry_result and calculate_rating_entry_result2. Use mrp_calculate_rating_entry_result() function instead.
* Important: Removed mrp_after_save_rating_success filter. Use mrp_after_save_rating_entry_success instead.
* Important: Removed mrp_after_rating_item_validation, mrp_after_custom_field_validation, mrp_after_review_fields_validation and mrp_after_rating_form_validation filters. Replaced with mrp_before_rating_entry_validation and mrp_after_rating_entry_validation filters.
* New: Added ID to row actions to improve usability in wp-list-tables for rating results, rating entries, rating items, rating forms and custom fields
* New: Added ID of WordPress users to the rating entries table in wp-admin
* New: Added count of entries for each rating form in rating forms page
* New: Added new API function get_rating_forms()
* Bug: Fixed {post_permalink} template tag for notification emails
* New: Added mrp_rating_moderation_notification_template_tags and mrp_rating_approved_notification_template_tags in case you want to add your own e-mail templatre tags for substituting values e.g. {site_name}
* Tweak: Updated the plugin updater and licensing settings to cater for add-ons

= 5.1 (08/07/2016) =

* New: Added schema.org reviews for the [mrp_rating_entry_details_list] shortcode
* New: Added new mrp_after_delete_rating_form hook
* New: Added count_entries rating result post meta field
* Bug: Fixed stray tr in rating-entry-details-list.php template file
* Bug: Some JS fixes based on JSLint and also updated some jQuery selectors based on attribute values to be surrounded in quotes
* Bug: Fixed default rating item value not being set in the WP comment form
* Bug: Fixed e-mail label for HTML error in rating-form.php template file: <label for="email echo $sequence ?>"
* Bug: Removed textarea value attribute in rating-form.php template file
* Bug: Fixed missing closing span in rating-item-results.php template file
* Bug: Fixed possibility of rating result post meta being overriden with a rating entry result or rating item result
* Bug: Fixed WP sort setting incorrectly using the site wide default rating form at all times
* Bug: Fixed an issue where different rating entries were incorrectly showing the same custom fields values
* Tweak: Changed the rating-form.php template file so that messages are shown just above the buttons
* Tweak: WP head inline styles (e.g. star icon color) now included in disable styles option
* Tweak: Updated languages files
* Tweak: Deprecated show_rich_snippets shortcode attribute. You should now use the generate_microdata shortcode attribute.
* Tweak: Modified the generated microdata for aggregated ratings in the rating-result.php template file
* Tweak: Removed closing input tag for radio buttons in the rating form
* Bug: Fixed error in [mrp_user_rating_results] shortcode
* Bug: Fixed mrp_can_apply_widget checking filters
* Bug: Fixed rating-item-results.php template always showing 0 value even when not required for options_block layout
* Bug: Fixed bulk approving rating entries in wp-admin

= 5.0.4 (21/06/2016) =

* Bug: Properly init custom post meta box toggles only on required pages
* Bug: Fixed rounding too early when calculating percentage rating item results

= 5.0.3 (17/06/3016) =

* Bug: Fixed email template settigns not saving
* Bug: Fixed a couple of warnings trying to access values from rating items that have not been set e.g. Warning: Illegal string offset 'rating_item_id' or 'option_value_text'
* Bug: Fixed postbox toggle issues

= 5.0.2 (16/06/2016) =

* New: Added post meta field "mrp_rating_result_<rating_form_id>" which holds an array of rating result data e.g. "mrp_rating_result_1".
* New: Added post meta field "mrp_rating_result_<rating_form_id>_star_rating" which has the overall star rating e.g. "mrp_rating_result_1_star_rating".
* Bug: Fixed missing div in schema.org generated markup

= 5.0.1 (09/06/2016) =

* Bug: Fixed error in rating form when using font icon library Font Awesome v4.2.0

= 5.0 (06/06/2016) =

* New: A more intuitive admin UI for editing rating forms, rating items, custom fields and review fields
* New: Added Rating entry details list widget
* New: Added Polylang support
* Tweak: Updated WPML compatibility to use new action hooks instead of deprecated functions. WPML < 3.2 is no longer supported.
* New: Filters to set specific rating forms and auto placement settings for taxonomies, terms and post types which can override the post meta and default settings.
* Tweak: Required rating items are now set for specific rating forms instead of globally for a rating item.
* New: You can now set rating items, custom fields and review fields as required for specific rating forms.
* New: Added decimal places setting for star ratings
* New: For select and radio rating items, there's now a setting to only show options with text
* Tweak: Re-organised the plugin settings tabs
* New: Added star rating out of settings e.g. 4/10 instead of 2/5
* New: Added new setting to sort the WP loop by highest ratings
* Tweak: Auto placement settings have been refactored. There's only one auto placement position setting for the rating form now instead of two. This means you can only choose one position for the rating form.
* New: Comment text now has a setting to show review title if necessary
* New: New e-mail settings tab for rating moderation and approved notification e-mails. Each notification has settings to customize the the recipients, from name, from e-mail, e-mail subject and e-mail template (with substitutions).
* New: Changes the font icon options to be generic to allow other font icon libraries to be added.
* New: Added Dashicon support
* Tweak: Removed showing user's overall ratings after submit and any message substitutions.
* New: Do not show rating form if user is not allowed to submit a rating e.g. anonymous ratings or user role disallowed
* New: Added mrp_rating_results_html hook to change HTML for auto placement
* Tweak: Changed how the API caches rating items to be more efficient
* Tweak: Optional fields are now labelled as review fields
* New: Added review title to ratings in WP comment form
* Renamed mrp_can_apply_wp_comment_integration filter to mrp_can_apply_comment_form
* New: Added comment-text template
* New: Added new default result type setting
* Tweak: rating-result-reviews.php template is not longer used.
* Tweak: Renamed [mrp_rating_result_reviews] shortcode to [mrp_rating_entry_details_list]. The old shortcode name is now deprecated but will still work.
* Tweak: Added inline layout to [mrp_rating_entry_details_list] shortcode
* Bug: Fixed missing post title asc and desc sort for rating entries
* Bug: Fixed show_count for [mrp_rating_result_list] shortcode
* Tweak: Added comment_id parameter to get_rating_entry() API function.
* Tweak: Rating items not longer have required and weight properties. These are now set on the rating form for each rating item. This means the get_rating_items() API function response has also changed.
* Tweak: get_rating_form() API function has been refactored and now also returns associated rating items custom fields and reiew fields
* Tweak: Removed API functions get_custom_field_values_lookup() and get_rating_item_option_value_text_lookup()
* Tweak: Removed [get_comment_rating_result] shortcode
* Tweak: Removed required from custom fields table in WP-admin as this is now setup as a part of the rating form.
* Tweak: Moved MRP post meta box so that it's not at the top
* Tweak: Rating entries are now a separate menu item and if there are pending rating entries, a counter is shown in the menu.
* Tweak: Fixed admin reports CSS
* New: New email-notification-rating-details.php template
* Tweak: Removed optional-fields.php template
* Tweak: Removed rating-result-reviews-title.php template. The rating-result-reviews.php template has been replaced by rating-entry-details-list.php template
* Tweak: rating-form.php template: $already_submitted_rating_form_message renamed to $existing_rating_message, $selected_option_lookup removed and $custom_field_values_lookup removed
* Tweak: user-ratings-dashboard template: $rating_result renamed to $rating_entry_result
* New: Rating form is now hidden after submit for anonymous users
* New: Minus icon no longer shown for required star ratings
* Bug: Fixed custom star images to to be relative to support SSL/TLS
* New: Added option to disable styles in advanced settings
* New: Added option to turn use minified CSS and JS assets in styles settings
* Bug: Fixed Font Awesome 3.2.1 loading from CDN not working
* New: Added Font Awesome 4.6.3 support
* Tweak: mrp_can_apply_filter and mrp_can_apply_comment filters have both been renamed to mrp_can_apply_auto_placement
* Bug: Fixed pagination with filters on rating entries and rating results tables in wp-admin
* Bug: Fixed entry status filter not highlighting approved if current for rating entries table in wp-admin

= 4.1.9 =

* Bug: Fixed rich snippets
* Bug: Fixed comments_only not working for [mrp_rating_result_reviews] shortcode
* New: Added mrp_automatically_approve_ratings filter so you can add custom logic for auto approving ratings

= 4.1.8 (21/11/2015) =

* Bug: jQuery UI calls protocol agnostic i.e http or https
* Bug: Default show title option to false
* Bug: Removed usage of mysql_real_escape_string() in admin tables
* Bug: Fixed error message when custom field exceeds max length

= 4.1.7 (15/10/2015) =

* New: Added mrp_head_css WP filter in case you want to move the CSS added in the head into your theme instead
*	Bug: Added more stringent checks to decide whether to generate and set a default rating form in case it's accidentally deleted.

= 4.1.6 (17/09/2015) =

*	Tweak: Added additional CSS classes to widgets
*	Bug: Fixed attachments not being able to calculate ratings due to post status inherit instead of published
*	Bug: Fixed rating results table db error in wp-admin when using a bulk action
*	Bug: Added stripslashes for rating entry comment, title and name
*	Bug: Fixed rating form using mrp_rating_form_include_minus filter

= 4.1.5 (10/09/2015) =

* Bug: Fixed star hover color not working

= 4.1.4 (08/09/2015) =

*	Bug: Fixed missing name & email in the approved/moderation email notifications for WP users
*	New: Added filters mrp_save_rating_response and mrp_delete_rating_response to modify the AJAX response data

= 4.1.3 (13/08/2015) =

* Tweak: Made it easier to add your own schema.org microdata and override the default "http://schema.org/Article" micordata using new filter mrp_rating_result_microdata. The old filters mrp_rating_result_microdata_thing and mrp_rating_result_microdata_thing_properties are no longer supported.
* Bug: Added custom fields which was missing in the e-mail notifications

= 4.1.2 (10/08/2015) =

* Bug: Fixed export rating results to CSV file custom fields not populated
* Bug: Fixed schema.org microdata not populating image

= 4.1.1 (5/08/2015) =

* Bug: Fixed export rating results to CSV file when some ratings do not have custom field values
* Bug: Fixed schema.org microdata for itemtype Article (the post) in rating-result.php template missing required itemprops publishedDate, headline and image

= 4.1 (02/08/2015) =

* Bug: Auto placement error when cannot find $post of $post_id for the_content filter.
* Bug: Added plugin update task to reactivate automatically and apply db changes
* Bug: Fixed dbdelta key spacing as per https://core.trac.wordpress.org/ticket/32314

= 4.0.12 (28/07/2015) =

* Bug: Moved reCAPTCHA feature into a new add-on plugin to avoid namespace issues for PHP < 5.3

4.0.9 (24/07/2015) =

* New: Added title to reviews

= 4.0.8 (16/07/2015) =

* New: Added sample rating item to auto generated default rating form
* Bug: Fixed show_avatar for [mrp_rating_result_reviews] shortcode
* Bug: Fixed stray comma in rating item ids or custom field ids in the rating form causing an issue displaying the rating form
* Tweak: Set template strip newlines option to on by default
* New: Added reCaptcha integration in the rating form and WP comment form

= 4.0.7 (15/07/2015) =

* New: Added auto placement of rating results options before_content and after_content
* New: Added rating form widget
* New: Added rating results widget
* New: Added rating item results widget
* New: Added taxonomy filter support to the [mrp_user_ratings_dashboard] shortcode
* New: Added mrp_delete_rating_result action hook
* New: Added mrp_save_rating_result action hook
* New: Added mrp_rating_result_filters filter

= 4.0.6 (07/07/2015) =

* Fix: WPML rating item option value texts not being string translated
* Fix: WP comment integration enabled settings not working in previous version 4.0.5

= 4.0.5 (07/07/2015) =

* New: Added mrp_can_apply_wp_comment_integration filter to customize which posts have the WP comment integration enabled. Similar to mrp_can_apply_filter.
* Tweak: Added options to include rating item values and custom field values in the export rating results to CSV file tool
* Bug: Removed undefined $show_view_more in class-api

= 4.0.4 (06/07/2015) =

* Tweak: Changed post link in rating results and rating entries tables to the edit post page
* Bug: Fixed auto placement filter to properly return value (title or content) if no post id is found instead of nothing
* Bug: Fix warning notices in rating-item-results.php template when a new rating item is added but some rating entries already exist
* Bug: Fixed touch event on minus icon with custom star images in rating form not working properly

= 4.0.3 (04/07/2015) =

* Bug: Fixed e-mail being saved as comment for anonymous users
* Bug: Fixed rating moderation notification email with no rating in title

= 4.0.2 (03/07/2015) =

* Tweak: Moved the filter settings checks on top of the mrp_can_apply_filter filter.
* Bug: Fixed incorrectly placing update and delete buttons on the rating form after anonymous ratings
* New: Added new post_ids shortcode attribute for the [mrp_rating_result_reviews], [mrp_rating_results_list] and [mrp_rating_item_results] shortcodes to filter the results e.g. [mrp_rating_item_results post_ids="1,2"]

= 4.0.1 (01/07/2015) =

* Bug: Fixed unable to edit ratings in user ratings dashboard
* Bug: Fixed "An unknown error has occured" when updating a rating form.
* Bug: Fixed adding delete button after saving a rating and also changed the save button text for updates
* Bug: Fixed the "mrp_after_custom_field_validation" and "mrp_after_rating_form_field_validation" validation filters to be used correctly

= 4.0 (30/06/2015) =

* New: Added bayesian ratings option
* Tweak: Significant performance improvements approx. 9x faster. Added new db table for storing calculated rating results and optimized db indexes. Removed rating results cache as this is now redundant.
* Tweak: Changed thumbs rating item span class in templates from "thumbs" to "mrp-thumbs" and "thumbs-select" to "mrp-thumbs-select"
* New: Added JS dialog to confirm clearing rating entries in the Tools
* New: Added mrp_default_rating_form filter so you can customize settings the default rating form for each post e.g. by post type, taxonomny and custom fields etc...
* New: Added new import tool to migrate rating entries data from free to Pro plugin version
* Bug: Fixed several WPML issues (i.e. unable to submit ratings) where the original post in the default language was not always returned calling icl\_object\_id.
* Bug: Fixed custom field line break issue for textarea displaying <br /> tag

= 3.2 (12/4/2015) =

* Tweak: Replaced include zero option with required option for rating items.
* Tweak: Added option for rating form error message color.
* Tweak: Added field error alongside rating items.
* Tweak: Added minified JS and CSS.
* Tweak: Improved usability of star ratings by not setting a default value. Star ratings are now more interactive as the on hover state works straight away.
* New: Added show overall rating, show rating items and show custom fields options for WP comments integration.
* Bug: Fixed reviews template permalink using get_the_title() causing formatting issues.
* New: Add user_roles attribute to various shortcodes.
* New: Added rating_item_ids attribute to various shortcodes
* New: Added option for disallowed user roles when saving a rating

= 3.1.1 (5/4/2015) =

* Tweak: Improved data sanitization
* New: Added template strip newlines option
* Tweak: Added js and css minified
* Bug: On touch event incorrectly switching thumbs up/thumbs down icons
* Tweak: CSS frontend changes to improve consistenct on different themes

= 3.1 (19/03/2015) =

* Bug: Fixed security flaw related to name & comment fields. Please update.
* New: Added dashicons-star-filled as menu icon
* Bug: Added comma to deault option values text in wp-admin add new rating items page
* Bug: Fixed JS is checkbox checked for rating forms & custom fields tables in wp-admin
* New: Added user roles filter for rating results and new attribute for [mrp_rating_result] shortcode
* Tweak: esc_html and stripslashes template changes
* New: Font Awesome 4.3.0 support
* New: Added thumbs up/thumbs down icons to [mrp_rating_item_results] shortcode for the options_inline and options_block layouts
* Bug: Fixed missing rating_form_id attribute for [mrp_user_rating_results]
* New: Added widget_title filter
* New: Minor class name fix
* Bug: Default widget titles to h3

= 3.0.8 (02/3/2015) =

* Bug: Fixed trailing comma bug with adding new rating items to the default rating form
* New: Added JS on change when adding new rating items in WP-admin to default option value text for each rating item type

= 3.0.7 (01/03/2015) =

* Bug: Fixed custom star rating hover issue.

= 3.0.6 (01/03/2015) =

* Bug: Fixed not showing 0 values when include zero is false for rating items in shortcode [mrp_rating_item_results] with layout options_block or options_inline.
* Bug: Fixed undefined $temp_class error for rating form star rating template when a rating item has include_zero set to false
* Bug: Fixed undefined $temp_class error for rating form star rating.
* Small CSS changes for reviews consistent with other rating item results
* New: Added filters for entries query: select, from, join, where, order by, group by and limit
* New: Added support for Font Awesome 4.3.0

= 3.0.5 (24/2/2015) =

* Bug: Fixed sort where count was undefined.
* Bug: Fixed reviews show the same star rating results
* Bug: Fixed Rating Item IDs with trailing comma e.g. "1,2," causing DB error
* New: Added WP filters to the entries query for select, from, join, where, group by, order by and limit

= 3.0.4 (22/02/2015) =

* Bug: Fix to prevent duplicate default rating forms from getting created when default rating form no longer exists.
* New: Added sorting by entry count for equal ratings.
* New: Changed 5 star ratings to be show at the top for rating item results when using options_blocl layout.
* New: Added rating_item_ids shortcode attribute & API parameter for filtering rating item results.
* Tweak: Modified post_id param in display_rating_result_reviews  and display_rating_item_results API call to be defaulted to the global post and if it's set as an empty string "", all posts will be used.
* Bug: Fixed post_id error notice in rating result reviews template due to undefined post id when no ratings exist.
* Bug: Added additional WPML support for rating item descriptions and custom field labels & placeholders.

= 3.0.3 (18/02/2015) =

* Bug: Fixed option value text tooltip in WP comment form

= 3.0.2 (18/02/2015) =

* New: Add css pointer to thumbs
* Bug: Updated jQuery .on("hover") to .on("mouseenter mouseleave") as this was removed in jQuery 1.9
* Bug: Fixed bug in page URL filters
* New: Added title on hover showing the option value text for star ratings and thumbs
* Bug: Fixed delete rating not working and also not showing any updated results.
* Bug: Error notice in comments when checking page URL filters

= 3.0 (13/02/2015) =

* New: Added in-built template system
* New: Added more options to the User Rating Results widget including taxonomy, terms, result type, show filter, filter label, show featued image, show rank, show date, image size, header and sort by.
* New: Added more options to the Rating Results List widget (formally Top Rating Results widget) including taxonomy, terms, result type, show filter, filter label, show rank, header and sort by.
* New: Added a setting to be able to default hide the Multi Rating meta box.
* New: Added more sorting options to rating results in the WP-admin.
* New: Added CSS cursor pointer on hover of star rating icons.
* New: Added 3 new layouts to the rating item results template: no_options, options_inline and options_block. show_options shortcode attribute is now deprecated.
* Bug: Fixed comment_text filter after wpautop which was sometimes adding line breaks.
* Bug: Fixed AJAX returning rating result where rating results position for a post is do not show.
* Tweak: Renamed all shortcodes to have a prefix mrp_ (old shortcode names are deprecated but will still work).
* Tweak: Renamed the Top Rating Results widget to Rating Results List widget which is more generic and supports different sorting mechanisms.
* Tweak: Removed API function parameters and shortcode attributes title, before_title and after_title from the rating result reviews and rating item results
* Tweak: Refactored the User Rating Results widget and shortcode. Added show featured image option.
* Tweak: Refactored all shortcodes, widgets and the correspnding API functions to use new template system. Renamed some shortcode attributes names, widget options names and API function parameters to improve consistency.
* Tweak: Improved readability of frontend JS
* Tweak: General CSS improvements
* Tweak: Removed the view more functionality. This will added again later utilising AJAX instead of a page refresh.
* Tweak: Renamed the display\_top\_rating\_results() API function to display\_rating\_results\_list() (old API function is deprecated, but will still work).
* Tweak: Renamed email notification filters to have a mrp_ prefix.
* Tweak: Renamed the following API parameters and shortcode attributes.
* Important: Deleted class-rating-results.php file as it's no longer needed.
* Important: Deleted template functions from class-rating-form.php.
* Important: Deleted actions that no longer make sense due to the new template system. If you've used these actions to modify the template, it will no longer work.
* Important: Moved common sorting functions from API to utils class.

= 2.4 (6/01/2015) =

* Added email notification settings for approved ratings and ratings awaiting moderation
* Added loading spinner when saving rating form
* Improved styles in plugin settings page

= 2.3.1 (20/12/2014) =

* Refactored save rating restrictions to allow using cookies and or an IP address within a specified time in hours
* Added edit_ratings capability to allow Editor role to be able to edit ratings
* If user is logged in, save rating restriction is now ignored

= 2.2.7 (10/12/2014) =

* Removed some lines at the end of a file causing some issues
* Removed some stray JavaScript
* Fixed total item count in rating results entries table

= 2.2.1 (9/12/2014) =

* Added settings to upload your own star rating images to use instead of Font Awesome star icons
* Added after_auto_placement action hook
* Added mrp\_can\_apply\_filter and mr\_can\_do\_shortcode filters
* Modified the Top Rating Results widget, [display\_top\_rating\_results] shortcode and the display\_top\_rating\_results() API function to be able to display the featured image of a post
* Added more options to show feature image and thumbnail size to the Top Rating Results widget
* Added Font Awesome 4.2.0 support
* Upgraded plugin updater to new version
* Fixed ratings moderation showing posts with no approved ratings in Top Rating Results list
* Fixed Top Rating Results widget db error for entry status approved
* Fixed update/delete existing ratings which was did not use the post id to retrieve the correct entry id for a logged in user

= 2.2 (24/11/2014) =

* Added ratings moderation functionality
* JS transition to hide/show rating items in WP comment form depending on include rating checkbox
* Fixed default include rating option not saving

= 2.1.7 (30/10/2014) =

* Fix to only show published posts in the User Rating Results
* Added option to allow users to udpate or delete their own ratings
* Added settings sections for custom text for titles, labels, messages and misc
* Fixed bug incorrectly showing user ratings as anonymous reviews
* Added fix to reasign or delete associated ratings when a user is deleted
* Added fix to delete associated ratings when a comment is deleted
* Added generic get\_rating\_results() API function
* deprecated get\_top\_rating\_results() API function - use get\_rating\_results() API function instead passing in sort_by 'top\_rating\_results'

= 2.1.6 (26/10/2014) =

* Fixed rounding of rating result to 2 decimals
* Fixed is_admin() checks to also check AJAX requests to support plugins such as FacetWP
* added filter button custom text
* category label text
* Added option to allow users to update or delete their own ratings
* Added ability to sort rating results in WP-admin by post title asc, post title desc, top rating results and most entries
* Added settings sections for custom text for titles, labels, messages and misc
* Fixed bug incorrectly showing user ratings as anonymous reviews
* Added new actions mrp\_can\_apply\_filter and mrp\_can\_do\_shortcode

= 2.1 (11/10/2014) =

* Added custom fields
* Added edit rating feature in WP-admin
* Replaced storing username with user id
* Refactored star rating html generation
* Added WP filter for custom rating form validation
* Some CSS changes
* Improved usability of WP-admin tables

= 2.0.13 (7/10/2014) =

* Fixed issue for anonymous ratings using the WP comments system integration. Rating form id was not being correctly picked up and then the rating results cache was not being refreshed.

= 2.0.12 (16/09/2014) =

* Fixed weight issue calculating rating results

= 2.0.11 (07/9/2014) =

* Fixed rating form validation being triggered for optional ratings in the WP comment system
* Added new filters to exclude search results using is_search() function

= 2.0.10 (5/09/2014) =

* Added same validations to the WP comments system as the shortcode, auto placement and display_rating_form API function
* Added two new options for WP comment system integration for an include ratings checkbox which allows users to optionally submit ratings with their comments
* Added filters to allow custom rating form validation

= 2.0.8 (1/09/2014) =

* Fixed bug correctly calculating rating results for different weights
* Fixed on hover JS issue for selected star icon and include zero set to false for rating item
* Fixed show_count attribute for display\_user\_rating\_results shortcode

= 2.0.5 (25/08/2014) =

* Fixed comments status change issue with cached rating results

= 2.0.4 (24/08/2014) =

* Fixed star rating hover in JS

= 2.0.3 (22/08/2014) =

* Fixed rating results cache bug

= 2.0.2 (21/08/2014) =

* Fixed email validation bug
* Fixed thumbs down icon for Font Awesome versions 4.1.0 and 4.0.3


= 2.0.1 (14/08/2014) =

* Performance improvements. Added rating results cache.

= 2.0 (12/08/2014) =

* Major plugin refactor
* Several bug fixes
* Added more filters in WP-admin tables
* Added new Tools menu
* Added rating results table in WP-admin
* Hide rating form on submit
* Replaced JS alery after submitting rating form with HTML message
* Added action hooks

= 1.4.2 (07/08/2014) =

* Fixed bug displaying rating result reviews on a page using the shortcode

= 1.4.1 (30/07/2014) =

* Fixed bug calculating raitng results if a new rating item is added
* Modified how rating results are calculated
* Sorting of rating results by result type
* Fixed bug missing before_title after_title in display_top_rating_results shortcode
* Fixed bug in JS where trim is not supported in IE8
* Added support for custom taxonomies
* Fixed bug for unapproved comments showing up in rating results
* Fixed bug when deleting a rating item it does not remove it from the rating form
* Added rating form usage count in rating items page
* Some CSS wp-list-table changes in wp-admin
* Used implode to sanitize comma separated lists

= 1.4 (23/07/2014) =

* Support for different versions of Font Awesome added
* Plugin now i18n translation ready

= 1.3.14 (17/07/2014) =

* Fixed bug with allow anonymous ratings option not working

= 1.3.13 (14/07/2014) =

* Added thumbs up/thumbs down rating items
* Modified rating result calculations so that each rating item is equal (e.g. if one rating item has max option value of 3 and another 5, 3/3 is the same as 5/5)
* Added new attribute show_options to display the rating item results with selected option totals

= 1.3.12 (11/07/2014)

* WP comments system integration set to use default settings for new posts/pages

= 1.3.11 (09/07/2014) =

* added filters to export rating results to CSV file
* style changes to reports and import/export in WP-admin

= 1.3.10 (07/07/2014) =

* add on hover color for star rating select
* added two new reports to show amount of daily ratings and also to export the rating results as a csv file
* rating item include zero option
* fixed some bugs overriding worpress comments integration in meta box
* fixed bug overriding allow anonymous in meta abox

= 1.3.9 (18/06/2014) =

* Fixed bug with not showing rating items in comment form if user is logged in

= 1.3.8 (14/05/2014) =

* Fixed bug which incorrectly calculated rating result if there are multiple forms with different rating items

= 1.3.7 (14/05/2014) =

* Fixed some minor defects
* Rating items now shown out of their max option value. New attribute preserve_max_option can be set to false to make it out of 5.

= 1.3.5 (7/06/2014) =

* Several defect fixes

= 1.3.2 (29/05/2014) =

* Fixed category filter

= 1.3.1 =

* Added Fontawesome star icons
* Changed layouts and CSS
* Fixed some misc bugs
* Added WordPress comments integration

= 1.2.6 (26/05/2014) =

* Fixed bug in displaying reviews where the selected values were incorrectly the same

= 1.2.5 (22/05/2014) =

* Fixed issue returning a logged in user's existing rating form entry in a page hierarchy

= 1.2.4 (22/05/2024) =

* Fixed issue with new column added to DB table not being setup correctly on plugin activation

= 1.2.3 (10/05/2014) =

* Added radio button option for displaying rating items
* Fixed default loading of post types option as it caused a warning

= 1.2.2 (07/04/2014) =

* Fixed some shortcode attribute bugs
* Refactored HTML for rating form and rating results including some CSS styles

= 1.2.1 (29/04/2014) =

* Fixed show_category_filter API parameter
* Fixed rating results view not showing no rating results message

= 1.2 (28/04/2014) =

* Improved styles and alignment of generated HTML
* add some new shortcode attributes and fixed a couple of related bugs

= 1.1.8 (24/04/2014) =

* Fixed bug in utils.php when normalizing URL

= 1.1.7 (23/04/2014) =

* Increased comments to 1020 chars
* Added whitelist/blacklist filter for specific page URLs for displaying the rating form and rating result set positions
* optional to exclude the home page and archive pages (i.e. Category, Tag, Author or a Date based pages) from displaying the rating form and rating result set positions
* Added shortcode to display user ratings
* Refactored API function to calculate rating results
* Fixed several bugs related to new rating result review shortcode
* Added option to show category filters and set a category for displaying user and top rating results

= 1.1.6 (15/4/2014) =

* Added new shortcode display_rating_result_reviews

= 1.1.5 (4/4/2014) =

* Fixed bug in display_rating_form API function

= 1.1.4 (3/4/2014) =

* Fixed rating form view custom text for already submitted rating form from message

= 1.1.3 (1/4/2014) =

* Fixed shortcode attributes title and show_count for shortcode display_rating_item_results

= 1.1.2 (1/4/2014) =

* Fixed global post object not being set for display_rating_item_results shortcode

= 1.1.1 (28/03/2014) =

* Fixed bug checking if user has already submitted rating

= 1.1 (25/03/2014) =

* Logged in users can update and delete their existing ratings
* Allow/disallow anonymous user ratings option
* Widget to display existing ratings of a logged in user
* Apply category filters to the Top Rating Results and User Ratings widgets
* Post meta box in the WP-admin edit post page to change the defaults for the rating form, allow anonymous ratings option and change the display position settings of the rating form and rating results
* Fixed bug to produce Google rich snippets
* More star rating image sprites

= 1.0 Initial version =

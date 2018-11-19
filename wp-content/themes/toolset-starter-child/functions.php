<?php
if ( ! function_exists( 'ref_enqueue_main_stylesheet' ) ) {
	function ref_enqueue_main_stylesheet() {
		if ( ! is_admin() ) {
			wp_enqueue_style( 'main', get_template_directory_uri() . '/style.css', array(), null );
			wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array(), null );
		}
	}
	add_action( 'wp_enqueue_scripts', 'ref_enqueue_main_stylesheet', 100 );
}

// Update CSS within in Admin
function admin_style() {
  wp_enqueue_style('admin-styles', get_stylesheet_directory_uri().'/admin.css');
}
add_action('admin_enqueue_scripts', 'admin_style');

/**************************************************
 * Load custom cells types for Layouts plugin from the /dd-layouts-cells/ directory
 **************************************************/
if ( defined('WPDDL_VERSION') && !function_exists( 'include_ddl_layouts' ) ) {

	function include_ddl_layouts( $tpls_dir = '' ) {
		$dir_str = dirname( __FILE__ ) . $tpls_dir;
		$dir     = opendir( $dir_str );

		while ( ( $currentFile = readdir( $dir ) ) !== false ) {
			if ( is_file( $dir_str . $currentFile ) ) {
				include $dir_str . $currentFile;
			}
		}
		closedir( $dir );
	}

	include_ddl_layouts( '/dd-layouts-cells/' );
}


/**************************************************
 * Hide Toolset content button from the post editor for the Authors
 **************************************************/
function vm_wptypes_remove() {
    if ( current_user_can( 'author' ) ) {
        wp_enqueue_style( 'wptypes-special', get_stylesheet_directory_uri() . '/wptypes-special.css' );
    }
}
add_action( 'admin_init', 'vm_wptypes_remove' );


/**************************************************
 * Support YARRP plugin in custom post types
 **************************************************/
add_filter('wpcf_type', 'yarpp_support_func', 10, 2);
function yarpp_support_func($data, $post_type)
{
if(in_array($post_type, array(

'actions'

)))
{
$data['yarpp_support'] = true;
}
return $data;
}


/**************************************************
 * Add a shortcode to display wpDiscuz comments
 **************************************************/
function display_comments_shortcode() {
ob_start();
comments_template();
$res = ob_get_contents();
ob_end_clean();
return $res;
}
add_shortcode( 'display_comments', 'display_comments_shortcode' );

/**************************************************
 * Add a shortcode to display login logout buttons
 **************************************************/
function display_login_logout_shortcode() {
$user = wp_get_current_user();
if (is_user_logged_in()) {
return '<span class="btn register-btn">' . $user->user_email . '</span> <a class="btn login-btn" href="' . wp_logout_url( get_permalink() ) . '">Sign out</a>';
} else {
return '<a class="btn login-btn" href="http://actionplan.s3platform.eu/wp-login.php">Sign in</a> <a class="btn register-btn" href="http://actionplan.s3platform.eu/wp-login.php?action=register" role="button"> Sign up </a>';
}
}
add_shortcode( 'display_login_logout', 'display_login_logout_shortcode' );

?>
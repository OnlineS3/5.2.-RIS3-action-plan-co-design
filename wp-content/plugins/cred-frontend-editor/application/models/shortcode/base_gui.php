<?php

/**
 * class WPV_Shortcode_Base_GUI
 *
 * @since 2.5.0
 */
class CRED_Shortcode_Base_GUI implements CRED_Shortcode_Interface_GUI  {
	
	/**
	 * WPV_Shortcode_Base_GUI constructor.
	 */
	public function __construct() {
		add_filter( 'cred_shortcodes_with_data', array( $this, 'register_shortcode_data' ) );
		add_filter( 'cred_shortcode_group_before_register', array( $this, 'filter_shortcode_group_before_register' ), 10, 2 );
	}
	
	/**
	 * @param $cred_shortcodes
	 *
	 * @return array
	 *
	 * @since m2m
	 */
	public function register_shortcode_data( $cred_shortcodes ) {
		return $cred_shortcodes;
	}
	
	/**
	 * @return array
	 *
	 * @since m2m
	 */
	public function get_shortcode_data() {
		return;
	}
	
	/**
	 * Filter a shortcode group before registering it.
	 *
	 * @since m2m
	 */
	public function filter_shortcode_group_before_register( $group_data, $group_id ) {
		return $group_data;
	}
	
	/**
	 * Generate the shortcode GUi javascript callback.
	 *
	 * @param string $shortcode_slug
	 * @param string $shortcode_title
	 *
	 * @since m2m
	 */
	protected function get_shortcode_callback( $shortcode_slug, $shortcode_title ) {
		return "Toolset.CRED.shortcodeGUI.shortcodeDialogOpen({ shortcode: '" . esc_js( $shortcode_slug ) . "', title: '" . esc_js( $shortcode_title ) . "' })";
	}
	
}
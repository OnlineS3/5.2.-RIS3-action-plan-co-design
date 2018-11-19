<?php

/**
 * Main Toolset Forms controller.
 *
 * Determines if we're in admin or front-end mode or if an AJAX call is being performed. Handles tasks
 * that are common to all three modes, if there are any.
 *
 * @since 1.9
 */
class CRED_Main {

	public function initialize() {

		$this->add_hooks();

	}

	private function __clone() { }


	private function add_hooks() {
		// This is happening quite early. We need to wait for everything.
		add_action( 'toolset_common_loaded', array( $this, 'register_autoloaded_classes' ) );
		add_action( 'toolset_common_loaded', array( $this, 'init_cred_association_forms' ), 999 );
		add_action( 'after_setup_theme', array( $this, 'init_api' ), 999 );
		add_action( 'after_setup_theme', array( $this, 'init_assets_manager' ), 999 );
		add_action( 'after_setup_theme', array( $this, 'register_shortcode_generator' ), 999 );
		add_action( 'init', array( $this, 'on_init' ), 1 );
	}


	/**
	 * Register Toolset Forms classes with Toolset_Common_Autoloader.
	 *
	 * @since 1.9
	 */
	public function register_autoloaded_classes() {
		// It is possible to regenerate the classmap with Zend framework, for example:
		//
		// cd application
		// /srv/www/ZendFramework-2.4.9/bin/classmap_generator.php --overwrite
		$classmap = include( CRED_ABSPATH . '/application/autoload_classmap.php' );
		$legacy_classmap = $this->get_legacy_classmap();
		$classmap = array_merge( $classmap, $legacy_classmap );

		do_action( 'toolset_register_classmap', $classmap );
	}
	
	public function init_api() {
		CRED_Api::initialize();
	}

	public function init_assets_manager() {
		CRED_Asset_Manager::get_instance();
	}

	public function register_shortcode_generator() {
		$toolset_common_bootstrap = Toolset_Common_Bootstrap::get_instance();
		$toolset_common_sections = array( 'toolset_shortcode_generator' );
		$toolset_common_bootstrap->load_sections( $toolset_common_sections );
		$cred_shortcode_generator = new CRED_Shortcode_Generator();
		$cred_shortcode_generator->initialize();
	}


	/**
	 * Return the array of autoloaded classes in legacy Toolset Forms and their absolute paths.
	 *
	 * If you need to use a class from the legacy code in the new part, use this method for
	 * registering it with the autoloader instead of including files directly.
	 *
	 * @return string[string]
	 * @since 1.9
	 */
	private function get_legacy_classmap() {
		$classmap = array();

		return $classmap;
	}


    /**
     * Shortcut to Toolset_Common_Bootstrap::get_request_mode().
     *
     * @return string Toolset_Common_Bootstrap::MODE_*
     * @since 1.9
     */
    private function get_request_mode() {
        $tb = Toolset_Common_Bootstrap::getInstance();
        return $tb->get_request_mode();
    }


	/**
	 * Determine in which request mode we are and initialize the right dedicated controller.
	 *
	 * @since 1.9
	 */
	public function on_init() {
		if ( ! is_admin()
			|| cred_is_ajax_call()
		) {
			CRED_Frontend_Select2_Manager::get_instance();
		}

		//Init Notification Hooks
		CRED_Notification_Manager_Utils::get_instance();

		$cred_ajax = new CRED_Ajax();
		$cred_ajax->initialize();

		$this->try_to_start_output_buffering();

		switch( $this->get_request_mode() ) {
			case Toolset_Common_Bootstrap::MODE_ADMIN:
				// todo CRED_Admin controller
				break;
			case Toolset_Common_Bootstrap::MODE_FRONTEND:
				// todo CRED_Frontend controller
				break;
			case Toolset_Common_Bootstrap::MODE_AJAX:
				CRED_Ajax::initialize();
				break;
		}

		$cred_shortcodes = new CRED_Shortcodes();
		$cred_shortcodes->initialize();
		
		$cred_cache = new CRED_Cache();
		$cred_cache->initialize();
	}

	public function init_cred_association_forms(){
		// Only initialize association forms if M2M is activated
		if ( ! apply_filters( 'toolset_is_m2m_enabled', false ) ) {
			return;
		}
		$factory = new CRED_Association_Form_Controller_Factory();
		$factory_model = new CRED_Association_Form_Model_Factory();
		$helper = new CRED_Association_Form_Relationship_API_Helper();
		$association_forms = new CRED_Association_Form_Main( $factory, $factory_model, $helper );
		$association_forms->initialize();
	}

	/**
	 * Fix PHP Warning header already sent on redirection adding a ob_start() on submition
	 *
	 * @since 1.9.4
	 */
	public function try_to_start_output_buffering() {
		if ( ! is_admin()
			&& ! empty( $_POST )
			&& isset( $_GET['_tt'] )
		) {
			ob_start();
		}
	}
}

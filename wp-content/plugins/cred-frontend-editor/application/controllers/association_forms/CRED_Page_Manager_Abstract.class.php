<?php

abstract class CRED_Page_Manager_Abstract {

	const ACTION_PREFIX = 'cred_association_form_';
	public $gui_base = null;
	public $_twig = null;
	protected $model = null;
	protected $helper = null;
	protected $repository = null;
	protected $_dialog_box_factory = null;

	public function __construct( CRED_Association_Form_Model_Interface $model, CRED_Association_Form_Relationship_API_Helper $helper, CRED_Association_Form_Repository $repository = null ) {
		$this->helper = $helper;
		$this->model = $model;
		$this->repository = $repository;
		$this->add_hooks();
		$this->init_toolset_gui_base();
	}

	public function add_hooks(){

	}

	public function init_toolset_gui_base(){

		$this->gui_base = Toolset_Gui_Base::get_instance();
		$this->gui_base->init();
		return $this->gui_base;
	}

	/**
	 * Twig
	 */
	public function render_page( $module, $template_to_render ) {

		$twig = $this->get_twig();

		$context = $this->build_page_context();

		echo $twig->render( $module.'/' . $template_to_render . '.twig', $context );
	}

	abstract function build_js_data();

	abstract function build_strings_for_twig();

	public function build_page_context() {

		// Basics for the listing page which we'll merge with specific data later on.
		$base_context = $this->gui_base->get_twig_context_base(
			Toolset_Gui_Base::TEMPLATE_LISTING, $this->build_js_data()
		);

		$specific_context = array(
			'strings' => $this->build_strings_for_twig(),
		);

		$context = toolset_array_merge_recursive_distinct( $base_context, $specific_context );

		return $context;
	}

	public function get_twig() {
		if ( null === $this->_twig ) {

			$this->_twig = $this->gui_base->create_twig_environment(
				array(
					'associations' => CRED_ABSPATH . '/application/views/',
					'associations_editor_metaboxes' => CRED_ABSPATH . '/application/views/editor_metaboxes/',
				)
			);
			$this->_twig->addExtension(new Twig_Extension_Debug());

			$this->prepare_twig();
		}

		return $this->_twig;
	}

	protected function prepare_twig(){
		$this->_twig->addFunction( new Twig_SimpleFunction( '__', array( $this, 'translate' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'admin_url', array( $this, 'admin_url' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'get_permalink', array( $this, 'get_permalink' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'get_lang', array( $this, 'get_lang' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'is_wpml', array( $this, 'is_wpml' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'wp_nonce_field', array( $this, 'wp_nonce_field' ) ) );
		$this->_twig->addFunction( new Twig_SimpleFunction( 'print_content_editor_toolbar_buttons', array( $this, 'print_content_editor_toolbar_buttons' ) ) );
	}

	public function print_content_editor_toolbar_buttons( $editor_id ){
		do_action( 'cred_content_editor_print_toolbar_buttons', $editor_id );
		return '';
	}

	public function translate( $text, $domain = 'wp-cred' ) {
		return __( $text, $domain );
	}

	public function admin_url( $path = '', $scheme = 'admin' ) {
		if ( $path ) {
			return admin_url( $path, $scheme );
		} else {
			return admin_url();
		}
	}

	public function get_permalink( $post_id = 0, $leavename = false ) {
		return get_permalink( $post_id, $leavename );
	}

	public function get_lang( $post_id ) {
		$lang = apply_filters( 'wpml_post_language_details', null, $post_id );

		if ( $lang ) {
			return $lang['display_name'];
		}

		return '';
	}

	public function is_wpml() {
		return apply_filters( 'ddl-is_wpml_active_and_configured', false );
	}

	public function wp_nonce_field( $action = '', $name = '', $referer = true, $echo = true ){
		return wp_nonce_field( $action, $name, $referer, $echo );
	}

}
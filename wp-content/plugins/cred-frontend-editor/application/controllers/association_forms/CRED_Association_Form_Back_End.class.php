<?php
class CRED_Association_Form_Back_End extends CRED_Association_Form_Abstract{

	const JS_LISTING_HANDLE = 'toolset_cred_association_forms_back_end_listing_main_js';
	const JS_LISTING_REL_PATH = '/public/association_forms/js/listing_page/main.js';
	const JS_EDITOR_HANDLE = 'toolset_cred_association_forms_back_end_editor_main_js';
	const JS_EDITOR_REL_PATH = '/public/association_forms/js/editor_page/main.js';
	const CSS_ADMIN_MAIN_HANDLE = 'toolset_cred_association_forms_back_end_main_css';
	const CSS_ADMIN_REL_PATH = '/public/association_forms/css/backend_main.css';
	const CSS_EDITOR_HANDLE = 'toolset_cred_association_forms_editor_css';
	const CSS_EDITOR_REL_PATH = '/public/association_forms/css/editor.css';
	const CSS_WIZARD_HANDLE = 'toolset_cred_association_forms_wizard_css';
	const CSS_WIZARD_REL_PATH = '/public/association_forms/css/wizard.css';

	private $assets_to_load_js = array();
	private $assets_to_load_css = array();

	private $page = null;

	public function __construct( CRED_Association_Form_Model_Factory $model_factory, CRED_Association_Form_Relationship_API_Helper $helper = null ) {
		$this->set_page();
		parent::__construct( $model_factory, $helper );
	}

	private function set_page(){
		$this->page = toolset_getget( 'page', null );
	}

	public function get_page(){
		return $this->page;
	}

	/**
	 * implementation for add_hooks method in abstract
	 */
	public function add_hooks(){
		add_filter( 'toolset_filter_register_menu_pages', array( $this, 'add_pages' ), 50 );
	}

	/**
	 * Initialize back-end
	 */
	public function initialize(){
		parent::initialize();
		if( $this->get_page() === self::LISTING_SLUG ){
			$this->init_scripts_and_styles();
			$this->init_listing();
		} elseif( $this->get_page() === self::EDITOR_SLUG ){
			$this->init_scripts_and_styles();
			$this->init_editor();
			$this->init_editor_toolbar();
		}
	}
	
	public function init_editor_toolbar() {
		$content_editor_toolbar = new CRED_Association_Form_Content_Editor_Toolbar();
		$content_editor_toolbar->initialize();
	}

	private function init_scripts_and_styles(){
		$this->load_backend_assets();
		$toolset_gui_base = Toolset_Gui_Base::get_instance();
		$toolset_gui_base->init();
	}

	private function init_listing(){
		$this->model = $this->get_model('Collection' );
		$this->view = $this->get_view('Listing', $this->model, $this->helper, $this->get_repository_instance() );
	}

	private function init_editor(){
		$this->model = $this->get_model('Model' );
		$this->view = $this->get_view('Editor', $this->model, $this->helper );
	}

	private function get_repository_instance(){
		global $wpdb;
		return new CRED_Association_Form_Repository( $wpdb );
	}

	function add_pages( $pages ) {

		if( $this->get_page() === self::EDITOR_SLUG ){
			$pages[] = array(
				'slug' => 'cred_relationship_form',
				'menu_title' => __('Relationship Forms Editor', 'wp-cred'),
				'page_title' => __('Relationship Forms Editor', 'wp-cred'),
				'callback' => array( $this->view, 'print_page' ),
				'capability' => CRED_CAPABILITY
			);
		}

		$pages[] = array(
			'slug' => 'cred_relationship_forms',
			'menu_title' => __('Relationship Forms', 'wp-cred'),
			'page_title' => __('Relationship Forms', 'wp-cred'),
			'callback' => array( $this->view, 'print_page' ),
			'capability' => CRED_CAPABILITY
		);

		return $pages;
	}

	/**
	 * Load defined dependencies
	 */
	private function load_backend_assets(){
		$this->register_assets();
		$this->define_assets( $this->assets_to_load_js, $this->assets_to_load_css );
		$this->load_assets();
	}

	/**
	 * Register necessary java scripts and css for backend
	 */
	private function register_assets(){

		$this->assets_manager->register_style(
			self::CSS_ADMIN_MAIN_HANDLE,
			CRED_ABSURL . self::CSS_ADMIN_REL_PATH,
			array(
				'editor-buttons',
				'buttons',
				'cred_cred_style_dev',
				'cred_wizard_general_style',
				Toolset_Gui_Base::STYLE_GUI_BASE
			),
			CRED_FE_VERSION
		);

		// Load only for listing page
		if( $this->get_page() === self::LISTING_SLUG ){
			$this->assets_manager->register_script(
				self::JS_LISTING_HANDLE,
				CRED_ABSURL . self::JS_LISTING_REL_PATH,
				array(
					'jquery', 'backbone', 'underscore','ddl-abstract-dialog','ddl-dialog-boxes',
					Toolset_Gui_Base::SCRIPT_GUI_LISTING_PAGE_CONTROLLER,
					Toolset_Assets_Manager::SCRIPT_HEADJS,
					Toolset_Assets_Manager::SCRIPT_KNOCKOUT,
					Toolset_Assets_Manager::SCRIPT_UTILS
				),
				CRED_FE_VERSION
			);
			$this->assets_to_load_js['listing_main'] = self::JS_LISTING_HANDLE;
			$this->assets_to_load_css['listing_main'] = self::CSS_ADMIN_MAIN_HANDLE;
		} elseif( $this->get_page() === self::EDITOR_SLUG ){
			$this->assets_manager->register_script(
				self::JS_EDITOR_HANDLE,
				CRED_ABSURL . self::JS_EDITOR_REL_PATH,
				array(
					'jquery',
					'backbone',
					'underscore',
					'quicktags',
					'ddl-abstract-dialog',
					'ddl-dialog-boxes',
					Toolset_Assets_Manager::SCRIPT_TOOLSET_EVENT_MANAGER,
					Toolset_Assets_Manager::SCRIPT_TOOLSET_SHORTCODE,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_CSS,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_HTMLMIXED,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_JS,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_OVERLAY,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_UTILS_HINT,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_UTILS_HINT_CSS,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_UTILS_PANEL,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_UTILS_SEARCH,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_UTILS_SEARCH_CURSOR,
					Toolset_Assets_Manager::SCRIPT_CODEMIRROR_XML,
					Toolset_Assets_Manager::SCRIPT_ICL_EDITOR,
					Toolset_Assets_Manager::SCRIPT_ICL_MEDIA_MANAGER,
					Toolset_Gui_Base::SCRIPT_GUI_LISTING_PAGE_CONTROLLER,
					Toolset_Assets_Manager::SCRIPT_HEADJS,
					Toolset_Assets_Manager::SCRIPT_KNOCKOUT,
					Toolset_Assets_Manager::SCRIPT_UTILS,
					Toolset_Assets_Manager::SCRIPT_SELECT2
				),
				CRED_FE_VERSION
			);

			// Wizard css for editor
			$this->assets_manager->register_style(
				self::CSS_WIZARD_HANDLE,
				CRED_ABSURL . self::CSS_WIZARD_REL_PATH,
				array(),
				CRED_FE_VERSION
			);

			$this->assets_manager->register_style(
				self::CSS_EDITOR_HANDLE,
				CRED_ABSURL . self::CSS_EDITOR_REL_PATH,
				array(
					self::CSS_ADMIN_MAIN_HANDLE,
					self::CSS_WIZARD_HANDLE,
					Toolset_Assets_Manager::STYLE_CODEMIRROR,
					Toolset_Assets_Manager::STYLE_CODEMIRROR_CSS_HINT,
					Toolset_Assets_Manager::STYLE_SELECT2_CSS_OVERRIDES

				),
				CRED_FE_VERSION
			);

			$this->assets_to_load_js['editor_main'] = self::JS_EDITOR_HANDLE;
			$this->assets_to_load_css['editor_main'] = self::CSS_EDITOR_HANDLE;
		}
	}
}
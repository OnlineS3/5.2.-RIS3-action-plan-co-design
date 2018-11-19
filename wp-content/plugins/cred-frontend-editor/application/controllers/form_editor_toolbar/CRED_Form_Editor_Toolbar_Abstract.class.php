<?php

abstract class CRED_Form_Editor_Toolbar_Abstract {
	
	const JS_TOOLBAR_HANDLE = 'toolset_cred_%s_form_%s_editor_toolbar_js';
	const JS_TOOLBAR_REL_PATH = '/public/form_editor_toolbar/js/%s_form_%s.js';
	
	const EDITOR_ID = 'cred_%s_form_%s';
	
	protected $editor_id;
	
	protected $editor_domain;
	protected $editor_target;
	
	protected $scripts = array();
	protected $styles = array();
	
	public $assets_manager;
	
	public function initialize() {
		
		$this->assets_manager = Toolset_Assets_Manager::get_instance();
		
		$this->editor_id = sprintf( self::EDITOR_ID, $this->editor_domain, $this->editor_target );
		$this->js_toolbar_handle = sprintf( self::JS_TOOLBAR_HANDLE, $this->editor_domain, $this->editor_target );
		$this->js_toolbar_relpath = sprintf( self::JS_TOOLBAR_REL_PATH, $this->editor_domain, $this->editor_target );
		
		$this->add_hooks();
		$this->init_assets();
		$this->load_assets();
	}
	
	public function add_hooks() {
		add_action( 'cred_content_editor_print_toolbar_buttons', array( $this, 'print_toolbar_buttons' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_assets') );
		add_action( 'admin_footer', array( $this, 'print_templates' ) );
	}
	
	public function print_toolbar_buttons() {}
	
	public function init_assets() {}
	
	public function load_assets() {
		do_action( 'toolset_enqueue_scripts', $this->scripts );
		do_action( 'toolset_enqueue_styles', $this->styles );
	}
	
	public function print_templates() {}
	
}
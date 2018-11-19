<?php

/**
 * Repository for templates in Types.
 *
 * See Toolset_Renderer for a detailed usage instructions.
 *
 * @since m2m
 */
class CRED_Output_Template_Repository extends Toolset_Output_Template_Repository_Abstract {

	const CONTENT_EDITOR_TOOLBAR_SCAFFOLD_DIALOG = 'scaffold-dialog.phtml';
	const CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM = 'scaffold-item.phtml';
	const CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM_OPTIONS = 'scaffold-item_options.phtml';
	const CONTENT_EDITOR_TOOLBAR_FIELDS_DIALOG = 'fields-dialog.phtml';
	const CONTENT_EDITOR_TOOLBAR_FIELDS_ITEM = 'fields-item.phtml';
	
	const SHORTCODE_CRED_FORM_DIALOG = 'cred-form.phtml';
	const SHORTCODE_CRED_USER_FORM_DIALOG = 'cred-user-form.phtml';
	const SHORTCODE_CRED_DELETE_POST_DIALOG = 'cred-delete-post.phtml';
	const SHORTCODE_CRED_CHILD_DIALOG = 'cred-child.phtml';
	
	const SHORTCODE_CRED_RELATIONSHIP_FORM_WIZARD_DIALOG = 'cred-relationship-form.phtml';
	const SHORTCODE_CRED_DELETE_RELATIONSHIP_DIALOG = 'cred-delete-relationship.phtml';

	/**
	 * @var array Template definitions.
	 */
	private $templates = null;


	/** @var Toolset_Output_Template_Repository */
	private static $instance;

	/**
	 * CRED_Output_Template_Repository constructor.
	 *
	 * @param Toolset_Output_Template_Factory|null $template_factory_di
	 * @param Toolset_Constants|null $constants_di
	 *
	 * this can only be PUBLIC although singleton pattern is used since the parent class __construct is public
	 */
	public function __construct( Toolset_Output_Template_Factory $template_factory_di = null,
		Toolset_Constants $constants_di = null ) {
		parent::__construct( $template_factory_di, $constants_di );
		$this->set_templates();
	}


	/**
	 * @return Toolset_Output_Template_Repository
	 */
	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * @inheritdoc
	 * @return string
	 */
	protected function get_default_base_path() {
		return $this->constants->constant( 'CRED_TEMPLATES' );
	}

	/**
	 * For the sake of php < 5.6 initialise $templates variable in constructor to avoid fatal errors for string concatenation !!!!
	 */
	protected function set_templates(){
		$this->templates = array(
			self::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/editor_toolbars',
				'namespaces' => array()
			),
			self::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM => array(
				'base_path' => CRED_TEMPLATES . '/editor_toolbars',
				'namespaces' => array()
			),
			self::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM_OPTIONS => array(
				'base_path' => CRED_TEMPLATES . '/editor_toolbars',
				'namespaces' => array()
			),
			self::CONTENT_EDITOR_TOOLBAR_FIELDS_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/editor_toolbars',
				'namespaces' => array()
			),
			self::CONTENT_EDITOR_TOOLBAR_FIELDS_ITEM => array(
				'base_path' => CRED_TEMPLATES . '/editor_toolbars',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_FORM_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_USER_FORM_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_DELETE_POST_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_CHILD_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_RELATIONSHIP_FORM_WIZARD_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			),
			self::SHORTCODE_CRED_DELETE_RELATIONSHIP_DIALOG => array(
				'base_path' => CRED_TEMPLATES . '/dialogs/shortcodes',
				'namespaces' => array()
			)
		);
	}


	/**
	 * Get the array with template definitions.
	 *
	 * @return array
	 */
	protected function get_templates() {
		return $this->templates;
	}
}

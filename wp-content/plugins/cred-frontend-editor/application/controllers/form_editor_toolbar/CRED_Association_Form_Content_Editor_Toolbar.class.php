<?php

class CRED_Association_Form_Content_Editor_Toolbar extends CRED_Form_Editor_Toolbar_Abstract {
	
	protected $editor_domain = 'association';
	protected $editor_target = 'content';
	
	// TODO ove this to a proper Toolset_Ajax handler
	const NONCE = 'toolset_cred_association_form_content_editor_toolbar_nonce';
	
	public function print_toolbar_buttons() {
		$current_form_id = ( 
				'cred_relationship_form' == toolset_getget( 'page' )
				&& 'edit' == toolset_getget( 'action' ) 
			) 
			? (int) toolset_getget( 'id' ) 
			: 0;
		?>
		<button 
		id="<?php echo esc_attr( $this->editor_id ); ?>-scaffold" 
		class="button button-secondary cred-form-content-scaffold js-cred-form-content-scaffold" 
		title="<?php esc_attr_e( 'Auto-generate form content', 'wp-cred' ); ?>" 
		data-target="<?php echo esc_attr( $this->editor_id ); ?>"
		>
			<i class="fa fa-magic fa-lg" aria-hidden="true"></i>
			<?php esc_attr_e( 'Auto-generate form content', 'wp-cred' ); ?>
		</button>
		
		<button 
		id="<?php echo esc_attr( $this->editor_id ); ?>-fields" 
		class="button button-secondary cred-form-content-fields js-cred-form-content-fields" 
		title="<?php esc_attr_e( 'Add fields', 'wp-cred' ); ?>" 
		data-target="<?php echo esc_attr( $this->editor_id ); ?>"
		>
			<span class="dashicons dashicons-forms" style="vertical-align:text-top"></span>
			<?php esc_attr_e( 'Add fields', 'wp-cred' ); ?>
		</button>
		
		<button 
		id="<?php echo esc_attr( $this->editor_id ); ?>-media" 
		class="button button-secondary cred-form-content-media js-toolset-editor-media-manager" 
		title="<?php esc_attr_e( 'Add Media', 'wp-cred' ); ?>" 
		data-target="<?php echo esc_attr( $this->editor_id ); ?>" 
		data-postid="<?php echo $current_form_id; ?>"
		>
			<span class="dashicons dashicons-admin-media" style="vertical-align:text-top"></span>
			<?php esc_attr_e( 'Add Media', 'wp-cred' ); ?>
		</button>
		<?php
	}
	
	public function init_assets() {
		$this->assets_manager->register_script(
			$this->js_toolbar_handle,
			CRED_ABSURL . $this->js_toolbar_relpath,
			array(
				'jquery', 
				'jquery-ui-dialog', 
				'jquery-ui-tabs', 
				'jquery-ui-sortable', 
				'shortcode', 
				'underscore', 
				'wp-util', 
				Toolset_Assets_Manager::SCRIPT_TOOLSET_SHORTCODE,
				Toolset_Assets_Manager::SCRIPT_TOOLSET_MEDIA_MANAGER
			),
			CRED_FE_VERSION
		);
		
		wp_enqueue_media();
		
		$origin = admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' )  );
		$query_args['toolset_force_one_query_arg'] = 'toolset';
		$ajaxurl = esc_url( add_query_arg(
			$query_args,
			$origin
		) );
		
		$this->assets_manager->localize_script(
			$this->js_toolbar_handle,
			'cred_association_form_content_editor_toolbar_i18n',
			array(
				'action' => array(
					'loading' => __( 'Loading...', 'wp-cred' ),
					'insert' => __( 'Insert', 'wp-cred' ),
					'cancel' => __( 'Cancel', 'wp-cred' ),
					'back' => __( 'Back', 'wp-cred' )
				),
				'dialog' => array(
					'scaffold' => array(
						'wpml_active' =>  apply_filters( 'toolset_is_wpml_active_and_configured', false ),
						'header' => __( 'Autogenerate form content', 'wp-cred' ),
						'introduction' => __( 'Drag fields to arrange their position in the form. If fields are not defined as "required" in Types, you can also remove them from the form by clicking on the "eye" icons.', 'wp-cred' )
					),
					'fields' => array(
						'header' => __( 'Association fields', 'wp-cred' )
					),
					'shortcode' => array(
						'header' => __( 'Insert an association field', 'wp-cred' ),
						'group' => array(
							'header' => __( 'Options for this field', 'wp-cred' )
						)
					),
				),
				'messages' => array(
					'relationship_missing' => __( 'You need to select a relationship first', 'wp-cred' )
				),
				'data' => array(
					'ajaxurl' => $ajaxurl,
					'nonce' => wp_create_nonce( CRED_Ajax::CALLBACK_GET_RELATIONSHIP_FIELDS ),
					'shortcodes' => array(
						'form_container' => CRED_Shortcode_Association_Form_Container::SHORTCODE_NAME
					),
					'fields' => array(
						'fields' => array(
							'formElements' => array(
								'form_container' => array(
									'label' => __( 'Form container', 'wp-cred' ),
									'shortcode' => CRED_Shortcode_Association_Form_Container::SHORTCODE_NAME,
									'requiredItem' => true,
									'attributes' => array(),
									'options' => array()
								)
							)
						)
					),
					'scaffold' => array(
						'fields' => array(
							'formElements' => array(
								'feedback' => array(
									'label' => __( 'Form messages', 'wp-cred' ),
									'shortcode' => CRED_Shortcode_Form_Feedback::SHORTCODE_NAME,
									'requiredItem' => true,
									'attributes' => array(),
									'options' => array(
										'type' => array(
											'label'        => __( 'Use this HTML tag', 'wp-cred' ),
											'type'         => 'select',
											'options'      => array(
												'div' => __( 'Div', 'wp-cred' ),
												'span'  => __( 'Span', 'wp-cred' )
											),
											'defaultValue' => 'div'
										),
										'stylingCombo' => array(
											'type'   => 'group',
											'fields' => array(
												'class' => array(
													'label' => __( 'Additional classnames', 'wp-cred' ),
													'type'  => 'text'
												),
												'style' => array(
													'label' => __( 'Additional inline styles', 'wp-cred' ),
													'type'  => 'text'
												)
											),
											'description' => __( 'Include specific classnames in the messages container, or add your own inline styles.', 'wp-cred' )
										)
									)
								),
								'submit' => array(
									'label' => __( 'Submit button', 'wp-cred' ),
									'shortcode' => CRED_Shortcode_Form_Submit::SHORTCODE_NAME,
									'requiredItem' => true,
									'attributes' => array(),
									'options' => array(
										'type' => array(
											'label'        => __( 'Use this HTML tag', 'wp-cred' ),
											'type'         => 'select',
											'options'      => array(
												'button' => __( 'Button', 'wp-cred' ),
												'input'  => __( 'Input', 'wp-cred' )
											),
											'defaultValue' => 'button'
										),
										'stylingCombo' => array(
											'type'   => 'group',
											'fields' => array(
												'class' => array(
													'label' => __( 'Additional classnames', 'wp-cred' ),
													'type'        => 'text'
												),
												'style' => array(
													'label' => __( 'Additional inline styles', 'wp-cred' ),
													'type'        => 'text'
												)
											),
											'description' => __( 'Include specific classnames in the submit button, or add your own inline styles.', 'wp-cred' )
										)
									)
								),
								'cancel' => array(
									'label'             => __( 'Cancel link', 'wp-cred' ),
									'shortcode'         => CRED_Shortcode_Form_Cancel::SHORTCODE_NAME,
									'requiredItem'      => false,
									'attributes'        => array(),
									'searchPlaceholder' => __( 'Search', 'wp-cred' ),
									'select2nonce'      => wp_create_nonce( Toolset_Ajax::CALLBACK_SELECT2_SUGGEST_POSTS_BY_TITLE ),
									'options'           => array(
										'action'       => array(
											'label'        => __( 'This link will redirect to', 'wp-cred' ),
											'type'         => 'select',
											'options'      => array(
												'same_page'         => __( 'Same page, without any forced CT', 'wp-cred' ),
												'same_page_ct'      => __( 'Same page, forcing a different CT', 'wp-cred' ),
												'different_page_ct' => __( 'Different page, forcing a given CT', 'wp-cred' )
											),
											'defaultValue' => 'same_page'
										),
										'select_page'  => array(
											'label' => __( 'User will be redirected to', 'wp-cred' ),
											'type'  => 'select'
										),
										'select_ct'    => array(
											'label'   => __( 'Force following Content template', 'wp-cred' ),
											'type'    => 'select',
											'options' => array(),
										),
										'message'      => array(
											'label'       => __( 'Redirect confirmation message', 'wp-cred' ),
											'type'        => 'text',
											'placeholder' => __( 'You will be redirected, do you want to proceed?', 'wp-cred' ),
										),
										'stylingCombo' => array(
											'type'        => 'group',
											'fields'      => array(
												'class' => array(
													'label' => __( 'Additional classnames', 'wp-cred' ),
													'type'  => 'text'
												),
												'style' => array(
													'label' => __( 'Additional inline styles', 'wp-cred' ),
													'type'  => 'text'
												)
											),
											'description' => __( 'Include specific classnames in the cancel button, or add your own inline styles.', 'wp-cred' )
										)
									)
								)
							)
						)
					)
				)
			)
		);

		$this->scripts[ $this->js_toolbar_handle ] = $this->js_toolbar_handle;
		
		$this->styles[ Toolset_Assets_Manager::STYLE_TOOLSET_COMMON ] = Toolset_Assets_Manager::STYLE_TOOLSET_COMMON;
		$this->styles[ Toolset_Assets_Manager::STYLE_TOOLSET_DIALOGS_OVERRIDES ] = Toolset_Assets_Manager::STYLE_TOOLSET_DIALOGS_OVERRIDES;
		$this->styles[ Toolset_Assets_Manager::STYLE_SELECT2_CSS ] = Toolset_Assets_Manager::STYLE_SELECT2_CSS;
		$this->styles[ Toolset_Assets_Manager::STYLE_FONT_AWESOME ] = Toolset_Assets_Manager::STYLE_FONT_AWESOME;
		
		do_action( 'otg_action_otg_enforce_styles' );
	}
	
	public function print_templates() {
		
		do_action( 'toolset_action_require_shortcodes_templates' );
		
		$template_repository = CRED_Output_Template_Repository::get_instance();
		$renderer = Toolset_Renderer::get_instance();
		
		$renderer->render(
			$template_repository->get( CRED_Output_Template_Repository::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_DIALOG ),
			null
		);
		$renderer->render(
			$template_repository->get( CRED_Output_Template_Repository::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM ),
			null
		);
		$renderer->render(
			$template_repository->get( CRED_Output_Template_Repository::CONTENT_EDITOR_TOOLBAR_SCAFFOLD_ITEM_OPTIONS ),
			null
		);
		
		$renderer->render(
			$template_repository->get( CRED_Output_Template_Repository::CONTENT_EDITOR_TOOLBAR_FIELDS_DIALOG ),
			null
		);
		$renderer->render(
			$template_repository->get( CRED_Output_Template_Repository::CONTENT_EDITOR_TOOLBAR_FIELDS_ITEM ),
			null
		);
		
	}
	
}
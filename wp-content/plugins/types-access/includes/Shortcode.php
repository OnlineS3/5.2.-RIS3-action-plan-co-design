<?php

if ( ! class_exists( 'OTG_Access_Shortcode_Generator' ) ) {
	
	/**
	 * OTG_Access_Shortcode_Generator
	 *
	 * Shortcodes generator for Access.
	 *
	 * Inherits from Toolset_Shortcode_Generator which is the base class 
	 * used to register items in the backend Admin Bar item for Toolset shortcodes.
	 * Also used to generate the Access editor button and the dialogs for inserting shortcodes.
	 *
	 * @since 2.3.0
	 */
	
	class OTG_Access_Shortcode_Generator extends Toolset_Shortcode_Generator {

	    function __construct() {
			
			parent::__construct();
			
			/**
			 * ---------------------
			 * Admin Bar
			 * ---------------------
			 */
			
			// Track whether the Admin Bar item has been registered
			$this->admin_bar_item_registered = false;
			// Register the Access item in the backend Admin Bar
			add_filter( 'toolset_shortcode_generator_register_item',	array( $this, 'register_access_shortcode_generator' ), 30 );
			
			/**
			 * ---------------------
			 * Access button and dialogs
			 * ---------------------
			 */
			
			// Access button in native editors plus on demand:
			// - From media_buttons actions
			// - From custom otg_access_action_generate_access_button action for adding the button
			// - From Toolset custom editor toolbars
			add_action( 'media_buttons',										array( $this, 'generate_access_button' ), 30 );
			add_action( 'otg_access_action_generate_access_button',				array( $this, 'generate_access_custom_button' ), 30 );
			add_action( 'toolset_action_toolset_editor_toolbar_add_buttons',	array( $this, 'generate_access_custom_button' ), 30, 2 );
			
			// Track whether dialogs are needed, and have been rendered in the footer
			$this->footer_dialogs_needed			= false;
			$this->footer_dialogs_added				= false;
			
			// Print the shortcodes dialogs in the footer,
			// both in frotend and backend, as long as there is anything to print.
			// Do it as late as possible because page builders tend to register their templates,
			// including native WP editors, hence shortcode buttons, in wp_footer:10.
			add_action( 'wp_footer',										array( $this, 'render_footer_dialogs' ), PHP_INT_MAX );
			add_action( 'admin_footer',										array( $this, 'render_footer_dialogs' ), PHP_INT_MAX );
			
			/**
			 * ---------------------
			 * Assets
			 * ---------------------
			 */
			 
			// Register shortcodes dialogs assets
			add_action( 'init',											array( $this, 'register_assets' ) );
			add_action( 'wp_enqueue_scripts',							array( $this, 'frontend_enqueue_assets' ) );
			add_action( 'admin_enqueue_scripts',						array( $this, 'admin_enqueue_assets' ) );
			
			// Ensure that shortcodes dialogs assets re enqueued
			// both when using the Admin Bar item and when an Access button is on the page.
			add_action( 'otg_access_action_enforce_shortcodes_assets', 	array( $this, 'enforce_shortcodes_assets' ) );
			
		}
		
		/**
		 * register_assets
		 *
		 * Register assets needed for the Access button and dialogs.
		 *
		 * @since 2.3.0
		 */
		
		public function register_assets() {
			global $pagenow;
			wp_register_script( 'otg-access-shortcodes-gui-script', TACCESS_ASSETS_URL . '/js/shortcode.js', array( 'jquery', 'jquery-ui-dialog', 'icl_editor-script', 'underscore', 'toolset-event-manager' ), TACCESS_VERSION );
			$shortcodes_gui_translations = array(
				'insert_shortcode'			=> __( 'Insert shortcode', 'wpcf-access'),
				'create_shortcode'			=> __( 'Create shortcode', 'wpcf-access' ),
				'close'						=> __( 'Close', 'wpcf-access'),
				'cancel'					=> __( 'Cancel', 'wpcf-access' ),
				'dialog_title'				=> __( 'Conditionally-displayed text', 'wpcf-access' ),
				'dialog_title_generated'	=> __( 'Generated shortcode', 'wpcf-access' ),
				'pagenow'					=> $pagenow
			);
			wp_localize_script( 'otg-access-shortcodes-gui-script', 'otg_access_shortcodes_gui_texts', $shortcodes_gui_translations );
		}
		
		/**
		 * frontend_enqueue_assets
		 *
		 * Enforce the Access assets in fronted pages where we know they are needed.
		 *
		 * @since 2.3.0
		 */
		
		public function frontend_enqueue_assets() {
			
			if ( $this->is_access_button_disabled() ) {
				return;
			}
			
			if ( $this->is_frontend_editor_page() ) {
				$this->enforce_shortcodes_assets();
			}
			
		}
		
		/**
		 * admin_enqueue_assets
		 *
		 * Enforce the Access assets in backend pages where we know they are needed.
		 *
		 * @since 2.3.0
		 */
		
		public function admin_enqueue_assets() {
			
			if ( $this->is_access_button_disabled() ) {
				return;
			}
			
			if ( 
				$this->admin_bar_item_registered 
				|| $this->is_admin_editor_page() 
			) {
				$this->enforce_shortcodes_assets();
			}
			
		}
		
		/**
		 * enforce_shortcodes_assets
		 *
		 * Enforce the Access assets, 
		 * primarily fired when an Access button is printed or the Admin Bar item is registered.
		 *
		 * @note Style assets might be enqueued too late if the Admin Bar item is not needed,
		 * or in the frontend when editors are printed too late.
		 *
		 * @since 2.3.0
		 */
		
		public function enforce_shortcodes_assets() {
			
			wp_enqueue_script( 'otg-access-shortcodes-gui-script' );
			wp_enqueue_style( 'toolset-common' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_style( 'toolset-dialogs-overrides-css' );
			
			do_action( 'otg_action_otg_enforce_styles' );
			
		}
		
		/**
		 * register_access_shortcode_generator
		 *
		 * Register the Access entry in the Admin Bar item.
		 *
		 * @since 2.3.0
		 */
		
		public function register_access_shortcode_generator( $registered_sections ) {
			
			if ( $this->is_access_button_disabled() ) {
				return $registered_sections;
			}
			
			$this->footer_dialogs_needed = true;
			$this->admin_bar_item_registered = true;
			do_action( 'otg_access_action_enforce_shortcodes_assets' );
			$registered_sections[ 'access' ] = array(
				'id'		=> 'access',
				'title'		=> __( 'Access', 'wpcf-access' ),
				'href'		=> '#access_shortcodes',
				'parent'	=> 'toolset-shortcodes',
				'meta'		=> 'js-otg-access-shortcode-generator-node'
			);
			
			return $registered_sections;
			
		}
		
		/**
		 * generate_access_button
		 *
		 * Generate the Access button on native editors.
		 *
		 * @since 2.3.0
		 */
		
		public function generate_access_button( $editor ) {
			if ( 
				empty( $editor ) 
				|| strpos( $editor, 'acf-field' ) !== false
				|| strpos( $editor, 'acf-editor' ) !== false 
			) {
				return;
			}
			
			global $post;
			if ( 
				isset( $post ) 
				&& ! empty( $post ) 
				&& isset( $post->post_type )
				&& 'attachment' == $post->post_type
			) {
				return;
			}
			
			if ( $this->is_access_button_disabled() ) {
				return;
			}
			
			$this->footer_dialogs_needed = true;
			do_action( 'otg_access_action_enforce_shortcodes_assets' );
			?>
			<span class="button js-wpcf-access-editor-button" data-editor="<?php echo esc_attr( $editor ); ?>">
				<i class="icon-access-logo ont-icon-18 ont-color-gray"></i>
				<?php echo __( 'Access', 'wpcf-access' ); ?>
			</span>
			<?php
		}
		
		/**
		 * generate_access_custom_button
		 *
		 * Generate the Access button on custom editors.
		 *
		 * Usually, custom editor toolbars expect atual buttons wrapped in <li></li> HTML tags.
		 *
		 * @since 2.3.0
		 */
		
		public function generate_access_custom_button( $editor, $source = '' ) {
			if ( empty( $editor ) ) {
				return;
			}
			
			if ( $this->is_access_button_disabled() ) {
				return;
			}
			
			$this->footer_dialogs_needed = true;
			do_action( 'otg_access_action_enforce_shortcodes_assets' );
			?>
			<li>
				<button class="button-secondary js-wpcf-access-editor-button" data-editor="<?php echo esc_attr( $editor ); ?>">
					<i class="icon-access-logo ont-icon-18"></i>
					<?php echo __( 'Access', 'wpcf-access' ); ?>
				</button>
			</li>
			<?php
		}
		
		/**
		 * render_footer_dialogs
		 *
		 * Adds the HTML markup for the shortcode dialogs to both 
		 * backend and frontend footers, as late as possible, 
		 * because page builders tend to register their templates,
		 * including native WP editors, hence shortcode buttons, in wp_footer:10.
		 *
		 * @since 2.3.0
		 */
		
		function render_footer_dialogs() {
			if ( 
				$this->footer_dialogs_needed
				&& ! $this->footer_dialogs_added
			) {
				global $wp_roles;
				$roles = $wp_roles->roles;
				$this->footer_dialogs_added = true;
				?>
				<div id="wpcf-access-shortcodes-dialog-tpl" style="display: none;">
					<form id="access-shortcodes-form">
					
						<h3><?php echo __('Select roles: ', 'wpcf-access'); ?></h3>
						<ul class="toolset-mightlong-list">
						<?php
						foreach ( $roles as $levels => $roles_data ) {
							echo '<li>'
								. '<label>'
									. '<input type="checkbox" class="js-wpcf-access-list-roles" value="' . esc_attr( $roles_data['name'] ) . '" /> '
									. esc_html( $roles_data['name'] ) 
								. '</label>'
							. '</li>';
						}
						?>
							<li>
								<label>
									<input type="checkbox" class="js-wpcf-access-list-roles" value="Guest" />
									<?php echo __('Guest', 'wpcf-access'); ?>
								</label>
							</li>
						</ul>
		
						<h3><?php echo __('Enter the text for conditional display: ', 'wpcf-access'); ?></h3>
						<p>
							<textarea class="otg-access-shortcode-conditional-message js-wpcf-access-conditional-message" rows="6" style="width: 100%;height: 100px;"></textarea>
							<small><?php echo __('You will be able to add other fields and apply formatting after inserting this text', 'wpcf-access'); ?></small>
						</p>
						
						<h3><?php echo __('Will these roles see the text? ', 'wpcf-access'); ?></h3>
						<p>
							<label>
								<input type="radio" class="js-wpcf-access-shortcode-operator" name="wpcf-access-shortcode-operator" value="allow" /> <?php echo __('Only users belonging to these roles will see the text', 'wpcf-access'); ?>
							</label>
							<br>
							<label>
								<input type="radio" class="js-wpcf-access-shortcode-operator" name="wpcf-access-shortcode-operator" value="deny" /> <?php echo __('Everyone except these roles will see the text', 'wpcf-access'); ?>
							</label>
							<br>
						</p>

						<h3><?php echo __('Output format ', 'wpcf-access'); ?></h3>
						<p>
							<label>
								<input type="checkbox" class="js-wpcf-access-shortcode-format" name="wpcf-access-shortcode-format" value="raw" /> <?php echo __('Display the text without any formatting', 'wpcf-access'); ?>
							</label>
							<br>
						</p>
						
					</form>
				</div>
				<?php
			}
		}
		
		public function is_access_button_disabled() {
			$hide_access_button = apply_filters( 'toolset_editor_add_access_button', false );
            if ( is_array( $hide_access_button ) ) {
                $current_role = Access_Helper::wpcf_get_current_logged_user_role();
                if ( in_array( $current_role, $hide_access_button ) ) {
                    return true;
                }
            }
			return false;
		}
		
		/**
		 * display_shortcodes_target_dialog
		 *
		 * Generate a dummy dialog for the shortcode generation response on the Admin Bar item.
		 *
		 * @since 2.3.0
		 */
		
		public function display_shortcodes_target_dialog() {
			parent::display_shortcodes_target_dialog();
			if ( $this->admin_bar_item_registered ) {
				?>
				<div class="toolset-dialog-container">
					<div id="otg-access-shortcode-generator-target-dialog" class="toolset-shortcode-gui-dialog-container js-otg-access-shortcode-generator-target-dialog">
						<div class="toolset-dialog">
							<p>
								<?php echo __( 'This is the generated shortcode, based on the settings that you have selected:', 'wpcf-access' ); ?>
							</p>
							<span id="otg-access-shortcode-generator-target" style="font-family:monospace;display:block;padding:5px;background-color:#ededed"></span>
							<p>
								<?php echo __( 'You can now copy and paste this shortcode anywhere you want.', 'wpcf-access' ); ?>
							</p>
						</div>
					</div>
				</div>
				<?php
			}
		}
		
	}
	
}
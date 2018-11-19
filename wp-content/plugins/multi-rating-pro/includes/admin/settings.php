<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the settings screen
 */
function mrp_settings_screen() {
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : MRP_Multi_Rating::GENERAL_SETTINGS_TAB;
			$page = MRP_Multi_Rating::SETTINGS_PAGE_SLUG;
			
			$tabs = array (
					MRP_Multi_Rating::GENERAL_SETTINGS_TAB 			=> __( 'General', 'multi-rating-pro' ),
					MRP_Multi_Rating::ADVANCED_SETTINGS_TAB 		=> __( 'Advanced', 'multi-rating-pro' ),
					MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB 	=> __( 'Auto Placement', 'multi-rating-pro' ),
					MRP_Multi_Rating::EMAIL_SETTINGS_TAB 			=> __( 'Emails', 'multi-rating-pro' ),
					MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB 		=> __( 'Custom Text', 'multi-rating-pro' ),
					MRP_Multi_Rating::STYLES_SETTINGS_TAB 			=> __( 'Styles', 'multi-rating-pro' ),
					MRP_Multi_Rating::LICENSES_SETTINGS_TAB 		=> __( 'Licenses', 'multi-rating-pro' )
			);
			
			$tabs = apply_filters( 'mrp_settings_tabs', $tabs );
			
			foreach ( $tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			}
			?>
		</h2>
		
		<?php 
		if ( isset( $_GET['updated'] ) && isset( $_GET['page'] ) ) {
			add_settings_error( 'general', 'settings_updated', __('Settings saved.', 'multi-rating-pro' ), 'updated' );
		}

		settings_errors();
		
		if ( $current_tab == MRP_Multi_Rating::GENERAL_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::GENERAL_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::GENERAL_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::ADVANCED_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::ADVANCED_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::ADVANCED_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::ADVANCED_SETTINGS_TAB );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::EMAIL_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::EMAIL_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::EMAIL_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::EMAIL_SETTINGS_TAB);
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::CUSTOM_TEXT_SETTINGS_TAB );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::STYLES_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::STYLES_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::STYLES_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::STYLES_SETTINGS_TAB );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::AUTO_PLACEMENT_SETTINGS_TAB );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
		} else if ( $current_tab == MRP_Multi_Rating::LICENSES_SETTINGS_TAB ) {
			?>
			<form method="post" name="<?php echo MRP_Multi_Rating::LICENSE_SETTINGS; ?>" action="options.php">
				<?php
				wp_nonce_field( 'update-options' );
				settings_fields( MRP_Multi_Rating::LICENSE_SETTINGS );
				do_settings_sections( MRP_Multi_Rating::SETTINGS_PAGE_SLUG . '&tab=' . MRP_Multi_Rating::LICENSES_SETTINGS_TAB );
				submit_button( null, 'primary', 'submit', true, null);
				?>
			</form>
			<?php
			
		} else {
			
			do_action( 'mrp_settings_form', $current_tab );
		
		}
		?>
	</div>
	<?php 
}
?>
<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Displays the about admin screen
 */
function mrp_about_screen() {	
	?>
	<div class="wrap about-wrap">
		
		<div id="mrp-header">
			<img class="mrp-badge" src="<?php echo plugins_url( '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'badge-icon.png' , __FILE__ ); ?>" alt="<?php _e( 'Multi Rating Pro', 'multi-rating-pro' ); ?>" / >
			<h1><?php printf( __( 'Multi Rating Pro v%s', 'multi-rating-pro' ), MRP_PLUGIN_VERSION ); ?></h1>
			<p class="about-text">
				<?php _e( 'Premium version of the Multi Rating plugin available on WordPress.org.', 'multi-rating-pro' ); ?>
			</p>
		</div>
		
		<h2 class="nav-tab-wrapper">
			<?php
			$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'support';
			$page = MRP_Multi_Rating::ABOUT_PAGE_SLUG;
			$tabs = array (
					//'getting-started' => __( 'Getting Started', 'multi-rating-pro' ),
					'support' => __( 'Support', 'multi-rating-pro' ),
					//'documentation' => __( 'Documentation', 'multi-rating-pro' ),
					//'affiliates' => __( 'Affiliates', 'multi-rating-pro' )
			);
			
			foreach ( $tabs as $tab_key => $tab_caption ) {
				$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
				
				echo '<a class="nav-tab ' . $active . '" href="?page=' . $page . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
			} ?>
		</h2>
		
		<?php 
	if ( $current_tab == 'support') {
		?>
		<p><?php printf( __( 'Please check the <a href="%1$s">documentation</a> available first before submitting a technical <a href="%2$s">support request</a>. '
				. 'Please note that the WordPress.org support forum is not to be used for premium plugin support.', 'multi-rating-pro' ),
				'https://multiratingpro.com/documentation?utm_source=about&utm_medium=pro-plugin&utm_campaign=wp-admin&utm_content=documentation', 
				'https://multiratingpro.com/support?utm_source=about&utm_medium=pro-plugin&utm_campaign=wp-admin&utm_content=support' ); 
		?></p>
	<?php
	}
}
?>
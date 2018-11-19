<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the rating results screen
 *
 * @since 0.1
 */
function mrp_rating_results_screen() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Results', 'multi-rating-pro' ); ?></h2>
		
		<form method="get" id="rating-results-table-form" action="<?php echo admin_url( 'admin.php?page=' . MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG ); ?>">
			<?php 
			$rating_results_table = new MRP_Rating_Results_Table();
			$rating_results_table->prepare_items();
			$rating_results_table->display();
			?>
			<input type="hidden" name="page" value="<?php echo MRP_Multi_Rating::RATING_RESULTS_PAGE_SLUG; ?>" />
		</form>
	</div>
	<?php 
}
<?php 

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Shows the reports screen
 */
function mrp_reports_screen() {
	?>
	<div class="wrap">
		<h2><?php _e( 'Reports', 'multi-rating-pro' ); ?></h2>
		
		<!-- TODO  
		<select id="mrp-report-type" name="mrp-report-type">
			<option value="entries-over-time"><?php // _e( 'Entries Over Time', 'multi-rating-pro' ); ?></option>
			<option value="rating-items"><?php //_e( 'Rating Items', 'multi-rating-pro' ); ?></option>
		</select>
		<input type="submit" name="submit" id="submit" class="button" value="Show"> -->
		
		<?php 
		$report_type = isset( $_REQUEST['report-type'] ) ? $_REQUEST['report-type'] : 'entries-over-time';
		//if ( $report_type == 'entries-over-time' ) {
			mrp_entries_over_time_report(); 
		//} else {
		//	mrp_rating_items_report();
		//}
		?>
	</div>
	<?php
}


/**
 * Rating items report
 */
function mrp_rating_items_report() {

	$from_date = isset( $_REQUEST['from-date'] ) ? $_REQUEST['from-date'] : null;
	$to_date = isset( $_REQUEST['to-date'] ) ? $_REQUEST['to-date'] : null;
	$post_id = isset( $_REQUEST['post-id'] ) ? $_REQUEST['post-id'] : null;
	$rating_form_id = isset( $_REQUEST['rating-form-id'] ) ? $_REQUEST['rating-form-id'] : null;

	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year )) {
			$from_date = null;
		}
	}
	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year )) {
			$to_date = null;
		}
	}
	
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Rating Items', 'multi-rating-pro' ); ?></span></h3>
			
			<div class="inside">
				<form method="post" id="rating-items-form">
				
					<div class="tablenav top">
						<div class="alignleft actions">
							<input type="text" class="date-picker" autocomplete="off" name="from-date" placeholder="From - yyyy-MM-dd" id="from-date" value="<?php echo $from_date; ?>" />
							<input type="text" class="date-picker" autocomplete="off" name="to-date" placeholder="To - yyyy-MM-dd" id="to-date" value="<?php echo $to_date; ?>" />
								
							<?php 
							mrp_posts_select( $post_id, true );
							mrp_rating_form_select( $rating_form_id, false, true);
							?> 
									
							<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
						</div>
					</div>
					
				</form>
				
				<?php
				/* 
				 * TODO:
				 * - should be able to select posts (one or multiple)
				 * - should be able to select a single rating item
				 * - should be able to select one or more rating forms
				 */ 
				$rating_result = MRP_Multi_Rating_API::get_rating_item_result( array(
						'rating_item' => $rating_item,
						'rating_form_id' => $rating_form_id,
						'post_id' => $post_id,
						'to_date' => $to_date,
						'from_date' => $from_date
				) );
					
				if ( intval( $rating_result['count_entries'] ) > $count_entries ) {
					$count_entries = intval( $rating_result['count_entries'] );
				}
					
				$option_value_text_lookup = array();
				if ( isset( $rating_item['option_value_text'] ) ) {
					$option_value_text_lookup = MRP_Utils::get_option_value_text_lookup( $rating_item['option_value_text'] );
				}
				?>
			</div>
		</div>
	</div>
	<?php 
}


/**
 * Entries over time report
 */
function mrp_entries_over_time_report() {
	$from_date = isset( $_REQUEST['from-date'] ) ? $_REQUEST['from-date'] : null;
	$to_date = isset( $_REQUEST['to-date'] ) ? $_REQUEST['to-date'] : null;
	$post_id = isset( $_REQUEST['post-id'] ) ? $_REQUEST['post-id'] : null;
	$rating_form_id = isset( $_REQUEST['rating-form-id'] ) ? $_REQUEST['rating-form-id'] : null;
	
	if ( $from_date != null && strlen( $from_date ) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $from_date ); // default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year )) {
			$from_date = null;
		}
	}
	
	if ( $to_date != null && strlen($to_date) > 0 ) {
		list( $year, $month, $day ) = explode( '-', $to_date );// default yyyy-mm-dd format
		if ( ! checkdate( $month , $day , $year )) {
			$to_date = null;
		}
	}
	
	?>
	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Entries Over Time', 'multi-rating-pro' ); ?></span></h3>
			
			<div class="inside">
				<form method="post" id="entries-report-form">
				
					<div class="tablenav top">
						<div class="alignleft actions">
							<input type="text" class="date-picker" autocomplete="off" name="from-date" placeholder="From - yyyy-MM-dd" id="from-date" value="<?php echo $from_date; ?>" />
							<input type="text" class="date-picker" autocomplete="off" name="to-date" placeholder="To - yyyy-MM-dd" id="to-date" value="<?php echo $to_date; ?>" />
								
							<?php 
							mrp_posts_select( $post_id, true );
							mrp_rating_form_select( $rating_form_id, false, true);
							?> 
									
							<input type="submit" class="button" value="<?php _e( 'Filter', 'multi-rating-pro' ); ?>"/>
						</div>
					</div>
					
				</form>
					
						
				<?php 
				global $wpdb;
				$query = 'SELECT DISTINCT DATE(entry_date) AS day, count(*) as count FROM ' 
						. $wpdb->prefix . MRP_Multi_Rating::RATING_ITEM_ENTRY_TBL_NAME . ' as rie';
				$query_args = array();
				
				$added_to_query = false;
				if ( $rating_form_id || $post_id || $from_date || $to_date ) {
					$query .= ' WHERE';
				}
				
				if ( $rating_form_id ) {
					if ($added_to_query) {
						$query .= ' AND';
					}
				
					$query .= ' rie.rating_form_id = %d';
					array_push( $query_args, $rating_form_id );
					$added_to_query = true;
				}
					
				if ( $post_id ) {
					if ($added_to_query) {
						$query .= ' AND';
					}
				
					$query .= ' rie.post_id = %d';
					array_push( $query_args, $post_id );
					$added_to_query = true;
				}
				
				if ( $from_date ) {
					if ($added_to_query) {
						$query .= ' AND';
					}
				
					$query .= ' rie.entry_date >= %s';
					array_push( $query_args, $from_date );
					$added_to_query = true;
				}
						
				if ( $to_date ) {
					if ($added_to_query) {
						$query .= ' AND';
					}
				
					$query .= ' rie.entry_date <= %s';
					array_push( $query_args, $to_date );
					$added_to_query = true;
				}
				
				$query .= ' GROUP BY day ORDER BY rie.entry_date DESC';
				
				if ( count( $query_args ) > 0 ) {
					$query = $wpdb->prepare( $query, $query_args );
				}
				
				$rows = $wpdb->get_results($query);
					
				$time_data = array();
				foreach ($rows as $row) {
					$day = $row->day;
					$count = $row->count;
					// TODO if a day has no data, then make it 0 visitors.
					// Otherwise, it is not plotted on the graph as 0.
					array_push( $time_data, array( ( strtotime( $day ) * 1000 ), intval( $count ) ) );
				}
				?>
				
				<div class="flot-container">
					<div class="report-wrapper" style="height: 300px;">
						<div id="entry-count-placeholder" class="report-placeholder"></div>
					</div>
				</div>
				<div class="flot-container">
					<div class="report-wrapper" style="height: 100px;">
						<div id="entry-count-overview-placeholder" class="report-placeholder"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
									
	<script type="text/javascript">
		// Time graph
		jQuery(document).ready(function() {
			// add markers for weekends on grid
			function weekendAreas(axes) {
				var markings = [];
				var d = new Date(axes.xaxis.min);
				// go to the first Saturday
				d.setUTCDate(d.getUTCDate() - ((d.getUTCDay() + 1) % 7))
				d.setUTCSeconds(0);
				d.setUTCMinutes(0);
				d.setUTCHours(0);
				var i = d.getTime();
				// when we don't set yaxis, the rectangle automatically
				// extends to infinity upwards and downwards
				do {
					markings.push({ xaxis: { from: i, to: i + 2 * 24 * 60 * 60 * 1000 } });
					i += 7 * 24 * 60 * 60 * 1000;
				} while (i < axes.xaxis.max);
				return markings;
			}
			var options = {
				xaxis: {
					mode: "time",
					tickLength: 5
				},
				selection: {
					mode: "x"
				},
				grid: {
					markings: weekendAreas,
					hoverable : true,
					show: true,
					aboveData: false,
					color: '#BBB',
					backgroundColor: '#f9f9f9',
					borderColor: '#ccc',
					borderWidth: 2,
				},
				series : {
					lines: {
						show: true,
						lineWidth: 1
					},
					points: { show: true }
				}
			};
					
			var plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode($time_data); ?>], options);
		
			var overview = jQuery.plot("#entry-count-overview-placeholder", [<?php echo json_encode($time_data); ?>], {
				series: {
					lines: {
						show: true,
						lineWidth: 1
					},
					shadowSize: 0
				},
				xaxis: {
					ticks: [],
					mode: "time"
				},
				yaxis: {
					ticks: [],
					min: 0,
					autoscaleMargin: 0.1
				},
				selection: {
					mode: "x"
				},
				grid: {
					markings: weekendAreas,
					hoverable : true,
					show: true,
					aboveData: false,
					color: '#BBB',
					backgroundColor: '#f9f9f9',
					borderColor: '#ccc',
					borderWidth: 2,	
				},
			});
			
			function flot_tooltip(x, y, contents) {
				jQuery('<div id="flot-tooltip">' + contents + '</div>').css( {
					position: 'absolute',
					display: 'none',
					top: y + 5,
					left: x + 5,
					border: '1px solid #fdd',
						padding: '2px',
						'background-color': '#fee',
						opacity: 0.80
				}).appendTo("body").fadeIn(200);
			}
				
			jQuery("#entry-count-placeholder").bind("plotselected", function (event, ranges) {
				// do the zooming
						
				plot = jQuery.plot("#entry-count-placeholder", [<?php echo json_encode($time_data); ?>], jQuery.extend(true, {}, options, {
					xaxis: {
						min: ranges.xaxis.from,
						max: ranges.xaxis.to
					}
				}));
						
				// don't fire event on the overview to prevent eternal loop
				overview.setSelection(ranges, true);
			});
									
			jQuery("#entry-count-overview-placeholder").bind("plotselected", function (event, ranges) {
				plot.setSelection(ranges);
			});
			
			jQuery("#entry-count-placeholder").bind("plothover", function (event, pos, item) {
				if (item) {
			   		jQuery("#flot-tooltip").remove();
					var x = item.datapoint[0].toFixed(2), y = item.datapoint[1].toFixed(2);
					flot_tooltip( item.pageX - 30, item.pageY - 20, item.datapoint[1] );
			    } else {
			    	jQuery("#flot-tooltip").remove();
			    }
			});
		});
	</script> 
	<?php
}
<?php
/**
 * Rating entries feed template
 */
header ( 'Content-Type: ' . feed_content_type ( 'rss-http' ) . '; charset=' . get_option ( 'blog_charset' ), true );
echo '<?xml version="1.0" encoding="' . get_option ( 'blog_charset' ) . '"?' . '>';
?>
<rss version="2.0"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:atom="http://www.w3.org/2005/Atom"
	xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
	xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
	<?php do_action( 'rss2_ns' ); ?>>
	
	<channel>
		<title><?php bloginfo_rss( 'name' ); ?> - Feed</title>
		<atom:link href="<?php self_link(); ?>" rel="self" type="application/rss+xml" />
		<link><?php bloginfo_rss( 'url' ) ?></link>
		<description><?php bloginfo_rss( 'description' ) ?></description>
		<lastBuildDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_lastpostmodified( 'GMT' ), false); ?></lastBuildDate>
		<language><?php echo get_option( 'rss_language' ); ?></language>
		<sy:updatePeriod><?php echo apply_filters( 'rss_update_period', 'hourly' ); ?></sy:updatePeriod>
		<sy:updateFrequency><?php echo apply_filters( 'rss_update_frequency', '1' ); ?></sy:updateFrequency>
		<?php do_action( 'rss2_head' ); ?>
		
		<?php 
		
		foreach ( $rating_entry_result_list as $rating_entry ) {
				
			$rating_result = $rating_entry['rating_result'];
			$title = apply_filters( 'mrp_feed_rating_entry_title', $rating_entry['title'], $rating_entry );
			$name = $rating_entry['name'];
			$comment = $rating_entry['comment'];
			$post_id = $rating_entry['post_id'];
			$entry_date = $rating_entry['entry_date'];
			$rating_entry_id = $rating_entry['rating_entry_id'];
			$user_id = $rating_entry['user_id'];
			//$rating_form_id = $rating_entry['rating_form_id'];
			$post_obj = get_post( $post_id );
			
			?>
			<item>
				<?php 
				if ( strlen( $title ) > 0 ) {
					?>
					<title><?php echo $title; ?></title>
					<?php 
				} 
				?>
				<link><?php echo get_the_permalink( $post_id ); ?></link>
				<pubDate><?php echo mysql2date( 'D, d M Y H:i:s +0000', $entry_date, false); ?></pubDate>
				
				<?php if ( strlen( trim( $name ) ) > 0 ) {
					?><dc:creator><?php	echo esc_html( $name ); ?></dc:creator><?php
				}
				?>
				
				<guid isPermaLink="false"><?php echo site_url(); ?>?rating-entry-id=<?php echo $rating_entry_id; ?></guid>
				
				<!-- summary -->
				<description>
					<![CDATA[
						
						<?php
						if ( has_post_thumbnail( $post_id ) ) {
							echo get_the_post_thumbnail( $post_id, $image_size );
						}
						
						echo '<p>';
						
						if ( $result_type == MRP_Multi_Rating::SCORE_RESULT_TYPE ) {
							
							echo $rating_result['adjusted_score_result'] .
							$out_of_text = apply_filters( 'mrp_out_of_text', '/' );
							echo $out_of_text;
							echo $rating_result['total_max_option_value'];
								
						} else if ( $result_type == MRP_Multi_Rating::PERCENTAGE_RESULT_TYPE ) {
								
							echo $rating_result['adjusted_percentage_result'] . '%';
								
						} else { // star rating
						
							echo $rating_result['adjusted_star_result'];
							$out_of_text = apply_filters( 'mrp_out_of_text', '/' );	
							echo $out_of_text;
							echo $star_rating_out_of;
							_e( ' star', 'multi-rating-pro' );
							
						}
						
						_e( ' rating given by ', 'multi-rating-pro' );

						if ( strlen( trim( $name ) ) == 0 ) {
							echo __( 'Anonymous', 'multi-rating-pro' );
						} else if ( $add_author_link == false || $user_id == 0 ) {
							echo $name;
						} else {
							echo '<a href="' . get_author_posts_url( $user_id ) . '">' . $name . '</a>';
						}
						
						_e( ' on post ', 'mult-rating-pro' );
						
						echo '<a class="post-permalink" href="' . get_the_permalink( $post_id ) . '>">' . $post_obj->post_title . '</a>.';
						
						echo '</p>';
						?>
					]]>
				</description>
				
				<?php rss_enclosure(); ?>
				<?php do_action( 'rss2_item' ); ?>
			</item>
			<?php
		}
		?>
	</channel>
</rss>
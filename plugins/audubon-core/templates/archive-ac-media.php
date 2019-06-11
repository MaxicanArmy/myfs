<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header();
echo do_shortcode("[sg_popup id=2]"); ?>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div id="audubon-core">
			<div class="ac-gallery-header">
				<div class="container">
					<div class="row">
						<div class="col-xs-3">
							<h2>3D</h2>
						</div>
						<div class="col-xs-9">
							<?php echo do_shortcode("[sg_popup id=2 event='click']<button class='btn btn-primary help-popup'>?</button>[/sg_popup]"); ?>
							<a id="ac-launch-wizard" href="/audubon-core-wizard" class="btn btn-primary">Add Media</a>
						</div>
					</div>
					<p>View the myFOSSIL community 3d fossils in the web viewer or download the file using the link on the individual page to view it offline in your preferred imaging software.</p>
					<ul class="ac-media-nav">
						<li class="ac-media-nav-tab">ALL FILES</li>
					</ul>
				</div><!-- .ac-media-gallery-header -->
			</div>
			<div class="container ac-media-content">
				<?php
				global $wp_query, $post;
				$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
				$row_counter = 0;
				if ( $wp_query->have_posts() ) {

					do {
						if ($row_counter % 4 == 0)
							echo "<div class='row'>";

						$wp_query->the_post();
						echo "<div class='col-xs-12 col-sm-6 col-md-3 ac-gallery-item'><a href='" . get_post_type_archive_link(AudubonCoreMedia::POST_TYPE) . $post->ID . "'>";
						audubon_core::display_ac_media("thumb", $post, '320');
						echo $post->post_title ."</a></div>";

						if (++$row_counter % 4 == 0)
							echo "</div>";

					} while ( $wp_query->have_posts() );

					if ($row_counter % 4 != 0)
						echo "</div>";

					$args = array(
						'base'               => '/ac-media%_%',
						'format'             => '/page/%#%',
						'total'              => $wp_query->max_num_pages,
						'current'            => $paged,
						'show_all'           => false,
						'end_size'           => 3,
						'mid_size'           => 5,
						'prev_next'          => false,
						'prev_text'          => __('« Previous'),
						'next_text'          => __('Next »'),
						'type'               => 'plain',
						'add_args'           => false,
						'add_fragment'       => '',
						'before_page_number' => '',
						'after_page_number'  => ''
					);

					$paginate_links = paginate_links( $args );

					if ( $paginate_links ) {
				        echo '<div class="pagination">';
				        echo $paginate_links;
				        echo '</div><!--// end .pagination -->';
				    }
				} else {
					?>
				  <h1>Sorry...</h1>
				  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
				  <?php
				}
				?>
			</div><!-- .ac-media-gallery -->
		</div><!-- #audubon-core -->
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>

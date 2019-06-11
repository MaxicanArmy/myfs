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
<div id="emuseum-header" class="container-fluid">
  <div class="container">
    <h3>Welcome to the</h3>
    <h1>eMuseum<span>3D Fossil Gallery</span></h1>
  </div>
</div>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div id="audubon-core">
			<div class="ac-gallery-header">
				<div class="container">
					<div class="row">
						<div class="col-xs-3">
							<h2>3D Fossil Gallery</h2>
						</div>
						<div class="col-xs-9">
							<?php echo do_shortcode("[sg_popup id=2 event='click']<button class='btn btn-primary help-popup'>?</button>[/sg_popup]"); ?>
							<a id="ac-launch-wizard" href="/audubon-core-wizard" class="btn btn-primary">Add Media</a>
						</div>
					</div>
					<p>Click on a 3D fossil to view and manipulate the digital fossil in your web browser. When you click through you will also have the option to download the object file to view in your preferred software or send for printing.</p>
					<ul class="ac-media-nav">
						<li class="ac-media-nav-tab">ALL FILES</li>
					</ul>
				</div><!-- .ac-media-gallery-header -->
			</div>
			<div class="container ac-media-content">
				<?php
				$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

        $args = array(
        	'post_type'  => AudubonCoreMedia::POST_TYPE,
          'posts_per_page' => 12,
          'paged' => $paged,
        	'meta_query' => array(
        		array(
        			'key'     => 'resource_ext',
        			'value'   => 'stl',
        			'compare' => '=',
        		),
        	),
        );
        $query = new WP_Query( $args );

				global $post;
				$row_counter = 0;
				if ( $query->have_posts() ) {

					do {
						if ($row_counter % 4 == 0)
							echo "<div class='row'>";

						$query->the_post();

						echo "<div class='col-xs-12 col-sm-6 col-md-3 ac-gallery-item'><a href='" . get_post_type_archive_link(AudubonCoreMedia::POST_TYPE) . $post->ID . "'>";
						audubon_core::display_ac_media("thumb", $post, '320');
						echo $post->post_title ."</a></div>";

						if (++$row_counter % 4 == 0)
							echo "</div>";

					} while ( $query->have_posts() );

					if ($row_counter % 4 != 0)
						echo "</div>";

					$args = array(
						'base'               => '/3d-gallery%_%',
						'format'             => '/page/%#%',
						'total'              => $query->max_num_pages,
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
  		  wp_reset_postdata();
				?>
			</div><!-- .ac-media-gallery -->
		</div><!-- #audubon-core -->
	</main><!-- .site-main -->
</div><!-- .content-area -->
<div id="emuseum-footer" class="container-fluid">
  <div class="container">
    <p>Explore more of the <span>eMuseum</span> :</p>
    <div class="row">
      <div id="dwc-archive-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/dwc-specimen"><p>Fossil Specimens</p></a></div>
      <div id="exhibits-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/dwc-exhibits"><p>Exhibits</p></a></div>
      <div id="aboutus-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/emuseum-aboutus"><p>About Us</p></a></div>
    </div>
  </div>
</div>
<?php get_footer(); ?>

<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>
<div id="emuseum-header" class="container-fluid">
  <div class="container">
    <h3>Welcome to the</h3>
    <h1>eMuseum<span>Specimens</span></h1>
  </div>
</div>
<div class="container" id="darwin-core">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php if (is_user_logged_in()) : ?>
			<div class="row">
				<div class="col-xs-2 col-xs-offset-10">
					<a href="/create-specimen" class="btn btn-primary">New Specimen</a>
				</div>
			</div>
		<?php endif; ?>
			<div class="row">
				<div class="col-xs-12">
				<?php
				global $wp_query;
				$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

				if ( $wp_query->have_posts() ) {
					echo "<table id='specimen-archive-list'><th>&nbsp;</th><th>Taxon</th><th>Location</th><th>Geological Context</th>";
					while ( $wp_query->have_posts() ) :
						$wp_query->the_post();
						$specimen = new DarwinCoreSpecimen( get_the_ID() ); ?>
						<tr class="hover-hand" data-href="<?= get_post_type_archive_link(DarwinCoreSpecimen::POST_TYPE) ?><?= the_id() ?>/">
							<td><?php darwin_core::specimen_featured_thumbnail( get_the_ID() ); ?><p><?= get_the_title() ?><br />by <a href="<?= bp_core_get_user_domain( get_the_author_meta( 'ID' ) ) ?>"><?= get_the_author() ?></a></p></td>
							<td><?php $specimen->display_precise_meta( 'Taxon', 3 ); ?></td>
							<td><?php $specimen->display_precise_meta( 'Location', 2 ); ?></td>
							<td><?php $specimen->display_precise_meta( 'GeologicalContext', 3 ); ?></td>
				  	</tr>
					<?php
					endwhile;

					echo "</table>";
					$args = array(
						'base'               => '/dwc-specimen%_%',
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
				</div>
			</div>
		</main><!-- .site-main -->
	</div><!-- .content-area -->
</div>
<div id="emuseum-footer" class="container-fluid">
  <div class="container">
    <p>Explore more of the <span>eMuseum</span> :</p>
    <div class="row">
      <div id="threed-gallery-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/3d-gallery"><p>3D Specimens</p></a></div>
      <div id="exhibits-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/dwc-exhibits"><p>Exhibits</p></a></div>
	    <div id="aboutus-footer-jumpoff" class="col-md-4 emuseum-footer-jumpoff"><a href="/emusuem-aboutus"><p>About Us</p></a></div>
    </div>
  </div>
</div>
<?php get_footer(); ?>

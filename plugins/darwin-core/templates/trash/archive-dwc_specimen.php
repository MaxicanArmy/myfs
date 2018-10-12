<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<div class="container" id="darwin-core">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
		<?php if (is_user_logged_in()) : ?>
			<div class="row">
				<div class="col-xs-2 col-xs-offset-10">
					<?php wp_nonce_field('dwc_create_specimen_ajax', 'dwc_create_specimen_nonce'); ?>
					<a href="/darwin-core-wizard" class="btn btn-primary ajax-btn">New Specimen</a>
				</div>
			</div>
		<?php endif; ?>
			<div class="row">
				<div class="col-xs-12">
				<?php
				global $wp_query;
				$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

				if ( $wp_query->have_posts() ) { 
					while ( $wp_query->have_posts() ) { 
						$wp_query->the_post(); 
						?>
				  		<div class='dwc-specimen-gallery-entry'><h1><a href='<?= get_post_type_archive_link(DarwinCoreSpecimen::POST_TYPE) ?>/<?= the_id() ?>/'>View <?= the_title() ?></a></h1></div>
						<?php 
					}

					$args = array(
						'base'               => '/dwc_specimen%_%',
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
	</div>
</div><!-- .content-area -->
<?php get_footer(); ?>

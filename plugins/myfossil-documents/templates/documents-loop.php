<?php

/**
 * Template for displaying the Dcouments Loop
 * You can copy this file to your-theme
 * and then edit the layout.
 */

get_header();

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$args = array(
	'post_type'      => 'myfossil_document',
	'order'          => 'DESC',
	'paged'          => $paged,
	'posts_per_page' => 10,
);

$wp_query = new WP_Query( $args );
?>

	<div class="container documents-container container-no-padding page-styling no-border-top">

		<div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 sidebar sidebar-right page-padding pull-right">

			<div>
			    <h3>Search Documents</h3>
			    <form role="search" action="<?php echo site_url('/'); ?>" method="get" id="searchform">
			        <input type="text" class="form-control" id="s" name="s" placeholder="Search Documents"/>
			        <input type="hidden" name="post_type" value="myfossil_document" />
			           <button class="btn btn-primary btn-search" type="submit" role="button" ><span class="fa-stack">                        
                                <i class="fa fa-search fa-stack-1x fa-inverse"></i>
                            </span>Search documents <span class="caret"></span></button>
				</form>
			</div>

			<div>
				<h3>Categories</h3>
				<?php echo mf_documents_cats_list(); ?>
			</div>

		</div>

		<div class="col-xs-12 col-sm-12 col-md-8 col-lg-9 page-padding next-to-right-sidebar">

			<h1 class="page-title">All Documents</h1>

			<?php if ( $wp_query->have_posts() ) : ?>

				<div class=""><br/>
					<?php myfossil_paging_nav(); ?>
				</div>

				<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); 	?>

					<div class="entry-content">

						<h4 class="entry-title">
							<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>">
							<?php the_title(); ?></a>
						</h4>

						<?php the_excerpt(); ?>


						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( 'thumbnail' );
							echo '<br/>';
						}
						?>

						<br/>
						<strong>Category: </strong> <?php the_category(', ') ?>


					</div><!-- .entry-content -->

			<?php endwhile; ?>

			<div class=""><br/>
				<?php myfossil_paging_nav(); ?>
			</div>

			<?php else : ?>

				<?php get_template_part('content', 'none'); ?>

			<?php endif; ?>


			<?php wp_reset_postdata(); ?>

		</div>
	</div>

<script type="text/javascript">
jQuery(document).ready(function($) { 
	if (window.location.search.indexOf('mfs=1') > -1)
		jQuery('#s').focus(); 
});
</script>	

<?php get_footer(); ?>

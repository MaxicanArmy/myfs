<?php
/**
* A Simple Category Template
*/

get_header();
$cat = get_category( get_query_var( 'cat' ) );
$cat_slug = $cat->slug; ?> 

<section id="primary" class="site-content container">
	<div id="content" class="site-main default-container" role="main">
		<div class="col-xs-12 col-sm-12 col-md-4 col-lg-3 sidebar sidebar-right page-padding pull-right">
			<div>
			    <h3>Search <?php single_cat_title( '', true ); ?></h3>
			    <form role="search" action="<?php echo site_url('/'); ?>" method="get" id="searchform">
			    	<input type="hidden" class="form-control" id="category_name" name="category_name" value="<?php echo $cat_slug ?>" />
			        <input type="text" class="form-control" id="s" name="s" placeholder="Search <?php single_cat_title( '', true ); ?>"/>
			           	<button class="btn btn-primary btn-search" type="submit" role="button" >
			           		<span class="fa-stack"><i class="fa fa-search fa-stack-1x fa-inverse"></i></span>Search <?php single_cat_title( '', true ); ?>
                        </button>
				</form>
			</div>
		</div>

		<div class="col-xs-12 col-sm-12 col-md-8 col-lg-9 page-padding next-to-right-sidebar">

			<?php 
			// Check if there are any posts to display
			if ( have_posts() ) : ?>

			<header class="archive-header">
			<h1 class="archive-title">Category: <?php single_cat_title( '', true ); ?></h1>


			<?php
			// Display optional category description
			if ( category_description() ) : ?>
			<div class="archive-meta"><?php echo category_description(); ?></div>
			<?php endif; ?>
			</header>

			<?php
			global $wp_query;

			$big = 999999999; // need an unlikely integer

			echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages,
				'prev_text' => '<',
				'next_text' => '>'
			) );

			// The Loop
			while ( have_posts() ) : the_post(); ?>
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
			<small><?php echo get_the_time('F jS, Y') ?> by <?php the_author_posts_link() ?></small>

			<div class="entry">
			<?php the_excerpt(); ?>

			<p class="postmetadata"><?php
			  //comments_popup_link( 'No comments yet', '1 comment', '% comments', 'comments-link', 'Comments closed');
			?></p>
			</div>

			<?php endwhile; 
			
			global $wp_query;

			$big = 999999999; // need an unlikely integer

			echo paginate_links( array(
				'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
				'format' => '?paged=%#%',
				'current' => max( 1, get_query_var('paged') ),
				'total' => $wp_query->max_num_pages,
				'prev_text' => '<',
				'next_text' => '>'
			) );
			
			else: ?>
			<p>Sorry, no posts matched your criteria.</p>


			<?php endif; ?>
		</div>
		<div class="clearfix"></div>
	</div>
</section>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
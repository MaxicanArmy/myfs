<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Sixteen
 * @since Twenty Sixteen 1.0
 */

get_header(); ?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<div class="container" id="myfossil-collection">
		<?php if (is_user_logged_in()) : ?>
			<div class="row">
				<div class="col-xs-2 col-xs-offset-10">
					<?php wp_nonce_field('myfs_collection_create_ac_ajax', 'myfs_collection_create_ac_nonce'); ?>
					<a id="myfs-collection-create-ac" style="float:right" href="/collection/add-media/" class="btn btn-primary">Add STL</a>
				</div>
			</div>
		<?php endif; ?>
			<?php
			$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

			$args = array(
				'posts_per_page'   => 4,
				'paged'			   => $paged,
				'offset'           => '',
				'category'         => '',
				'category_name'    => '',
				'orderby'          => 'date',
				'order'            => 'DESC',
				'include'          => '',
				'exclude'          => '',
				'meta_key'         => 'resource_url',
				'meta_value'       => ' ',
				'meta_compare'	   => '!=',
				'post_type'        => 'ac_multimedia',
				'post_mime_type'   => '',
				'post_parent'      => '',
				'author'		   => '',
				'author_name'	   => '',
				'post_status'      => 'any',
				'suppress_filters' => true 
			);

			$query = new WP_Query( $args );

			$posts = $query->posts;

			$count = 0;
			foreach ( $posts as $current ) {
				$parent = get_post($current->post_parent);

				echo ($count % 4 == 0) ? "<div class='row stl-row'>" : "";
				echo "<div class='col-xs-12 col-sm-6 col-lg-3 gallery-stl'>";
				echo '<a style="display:inline-block;width:100%;text-align:center" href="/ac_multimedia/' . $current->ID . '/">View ' . ( ( $current->post_title != "" ) ? $current->post_title : $current->ID) . ' details</a>';

				$media_url = get_post_meta( $current->ID, 'resource_url', true );
				if ( $media_url !== '' ) {
					//need to be able to handle images here
					echo do_shortcode('[canvasio3D width="262" height="262" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $media_url . '" objScale=".75" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
				}
				else {
					echo do_shortcode('[canvasio3D width="262" height="262" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $current->guid . '" objScale=".75" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
				}

				echo "</div>";
				$count++;
				echo ($count % 4 == 0) ? "</div>" : "";
			}
			echo ($count % 4 != 0) ? "</div>" : ""; 

			$args = array(
				'base'               => '/collection/stl%_%',
				'format'             => '/page/%#%',
				'total'              => $query->max_num_pages,
				'current'            => $paged,
				'show_all'           => false,
				'end_size'           => 3,
				'mid_size'           => 5,
				'prev_next'          => true,
				'prev_text'          => __('«'),
				'next_text'          => __('»'),
				'type'               => 'plain',
				'add_args'           => false,
				'add_fragment'       => '',
				'before_page_number' => '',
				'after_page_number'  => ''
			);

			$paginate_links = paginate_links( $args );
			
			if ( $paginate_links ) {
				echo '<div class="row"><div class="col-xs-12">';
		        echo '<div class="pagination">';
		        echo $paginate_links;
		        echo '</div></div></div><!--// end .pagination -->';
		    }
			?>
		</div>
	</main><!-- .site-main -->
</div><!-- .content-area -->
<?php get_footer(); ?>

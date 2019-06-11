<?php
/**
 * Template Name: Fossil
 *
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package myfossil
 */
/*
get_header();

$req = array_key_exists( 'fossil_id', $wp_query->query_vars ) ? 'single' : 'list';

if ( $req == 'single' ):
    $fossil_id = $wp_query->query_vars['fossil_id'];
    $view = $wp_query->query_vars['fossil_view'];

    myfossil_fossil_render_single( $fossil_id, $view );
else:
    include_once( 'content-fossils-list.php' );
endif;

get_footer();
*/
$dwc_id = '';
if (preg_match("#fossils\/([0-9]+)#", $wp->request, $matches) === 1) {
  $fossil_id = $matches[1];

  $fossil_query_args = array(
    'post_type' => 'dwc_specimen',
    'posts_per_page' => 1, //need to change this to -1 when im done testing (or late stage of testing)
    'post_parent' => $fossil_id,
    'post_status' => array('publish','draft')
  );
  $fossils = new WP_Query( $fossil_query_args );
  if ( $fossils->have_posts() ) {
    $fossils->the_post();
    $dwc_id = get_the_ID();
  }
  wp_reset_postdata();
}
header('Location:/dwc-specimen/'.$dwc_id);

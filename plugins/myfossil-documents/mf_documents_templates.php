<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

//  load single document template from theme or plugin
function mf_documents_single_template( $single_template ) {
	global $post;
		
	if ( $post->post_type == 'myfossil_document' ) {

		$theme_template = locate_template( 'single-document.php' );
			
		if( file_exists( $theme_template ) )
			$single_template = $theme_template;
		else
		   $single_template = dirname( __FILE__ ) . '/templates/single-document.php';

	}

	return $single_template;
}
add_filter( 'single_template', 'mf_documents_single_template', 16 );


//  load document loop template from theme or plugin
function mf_documents_template_include( $template ) {
    global $wp_query;

	if( isset( $wp_query->post->post_title ) ) {
		
		$page_title = $wp_query->post->post_title;
	
		if ( $page_title == __( 'Documents', 'myfossil' ) ) {

			$theme_template = locate_template( 'documents-loop.php' );

			if( file_exists( $theme_template ) )
			   $template = $theme_template;
			else
			   $template = dirname( __FILE__ )  . '/templates/documents-loop.php';			
			
		}

	}
	
	return $template;
}
add_action( 'template_include', 'mf_documents_template_include' );


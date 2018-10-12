<?php
/*
Plugin Name: MyFossil Documents
Plugin URI: https://github.com/myfossil/myfossil-documents
Description: Create and manage Documents
Version: 1.0
Author: MyFossil
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if ( ! is_admin() ) {
	include_once( dirname( __FILE__ ) . '/mf_documents_templates.php' );
	include_once( dirname( __FILE__ ) . '/mf_documents_functions.php' );
}


function mf_documents_activation() {

	mf_add_documents_caps();

	mf_create_post_type_document();

	mf_create_documents_page();

	mf_create_misc_category();

	flush_rewrite_rules();

}
register_activation_hook(__FILE__, 'mf_documents_activation');


function mf_documents_deactivation () {
	mf_remove_documents_caps();
}
register_deactivation_hook(__FILE__, 'mf_documents_deactivation');


function mf_documents_uninstall () {
	return;
}
register_uninstall_hook( __FILE__, 'mf_documents_uninstall');


function mf_create_documents_page() {

    $page = get_page_by_path('documents');

    if( ! $page ){
		$documents_page = array(
		  'post_title'    => 'Documents',
		  'post_name'     => 'documents',
		  'post_status'   => 'publish',
		  'post_author'   => get_current_user_id(),
		  'post_type'     => 'page'
		);

		$post_id = wp_insert_post( $documents_page, true );
    }

}

function mf_create_misc_category() {

	if( !term_exists('misc') ) {
		wp_insert_term(
			'Misc',
			'category',
			array(
			  'description'	=> 'Miscellaneous',
			  'slug' 		=> 'misc'
			)
		);
	}
}


function mf_document_set_default_category( $post_id, $post, $update ) {

	$slug = 'myfossil_document';

	if ( $slug != $post->post_type )
		return;

	$terms_list = wp_get_post_terms( $post_id, 'category', array("fields" => "name"));

	//if( in_array('Uncategorized', $terms_list) )
	if( empty( $terms_list ) )
		wp_set_object_terms( $post_id, 'misc', 'category' );

}
//add_action( 'save_post', 'mf_document_set_default_category', 99, 3 );


function mf_create_post_type_document() {

	register_post_type( 'myfossil_document',
		array(
		  'labels' => array(
			'name' => __( 'Documents' ),
			'singular_name' => __( 'Document' ),
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New Document' ),
			'edit' => __( 'Edit' ),
			'edit_item' => __( 'Edit Document' ),
			'new_item' => __( 'New Document' ),
			'view' => __( 'View Documents' ),
			'view_item' => __( 'View Document' ),
			'search_items' => __( 'Search Documents' ),
			'not_found' => __( 'No Documents found' ),
			'not_found_in_trash' => __( 'No Documents found in Trash' ),
		),
		'public' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => 'document' ),
		'capability_type' => array('document', 'documents'),
		'exclude_from_search' => false,
		'has_archive' => true,
		'map_meta_cap' => true,
		'hierarchical' => false,
		"supports"	=> array("title", "editor", "thumbnail", "author", "comments", "trackbacks"),
		'taxonomies' => array('category'),
		)
	);
	//register_taxonomy_for_object_type('category', 'document');

	//$term_list = wp_get_post_terms(1910, 'category', array("fields" => "names"));
	//var_dump( $term_list );
	//if( in_array('Uncategorized', $term_list) )  echo '........in array';
	//if( empty( $term_list) )  echo '........empty';
}
add_action( 'init', 'mf_create_post_type_document' );


function mf_add_documents_caps() {

	$role = get_role( 'administrator' );
	$role->add_cap( 'delete_published_documents' );
	$role->add_cap( 'delete_others_documents' );
	$role->add_cap( 'delete_documents' );
	$role->add_cap( 'edit_others_documents' );
	$role->add_cap( 'edit_published_documents' );
	$role->add_cap( 'edit_documents' );
	$role->add_cap( 'publish_documents' );

}

function mf_remove_documents_caps() {
	global $wp_roles;

	$all_roles = $wp_roles->roles;

	foreach( $all_roles as $key => $value ){

		$role = get_role( $key );

		$role->remove_cap( 'delete_published_documents' );
		$role->remove_cap( 'delete_others_documents' );
		$role->remove_cap( 'delete_documents' );
		$role->remove_cap( 'edit_others_documents' );
		$role->remove_cap( 'edit_published_documents' );
		$role->remove_cap( 'edit_documents' );
		$role->remove_cap( 'publish_documents' );

	}
}
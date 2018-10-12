<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// so myfossil_documents cpt is found on assigned cat archive page
function mf_documents_query_post_type($query) {

	if(is_category() || is_tag()) {
		$post_type = get_query_var('post_type');
		if($post_type)
			$post_type = $post_type;
		else
			$post_type = array( 'myfossil_document', 'nav_menu_item');

		$query->set('post_type',$post_type);
		return $query;
	}

}
add_filter('pre_get_posts', 'mf_documents_query_post_type');


function mf_documents_cats_list() {

	$cat_str = '';

	$args_types = array(
		'type'                     => 'myfossil_document',
		'parent'                   => '',
		'orderby'                  => 'name',
		'order'                    => 'ASC',
		'hide_empty'               => 1,
		'hierarchical'             => 0,
		'exclude'                  => '1',
		'include'                  => '',
		'number'                   => '',
		'taxonomy'                 => 'category',
		'pad_counts'               => 1
	);

	$categories = get_categories($args_types);

	foreach($categories as $category) {

		$cat_str .= '<a href="' . site_url('/category/') . $category->slug . '/"/>' . $category->name . '</a>&nbsp;(' . $category->category_count . ')<br/>';

	}

	return $cat_str;

}

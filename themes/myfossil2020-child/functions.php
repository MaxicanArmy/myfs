<?php

if (!function_exists('myfossil2020_child_setup')):

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function myfossil2020_child_setup()
    {
        /*
         * Disable the admin bar
         */
        add_filter('show_admin_bar', '__return_false');
    }
endif; // myfossil_setup

add_action('after_setup_theme', 'myfossil2020_child_setup');

function my_theme_enqueue_styles() {

    $parent_style = 'myfossil2020'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.

	wp_enqueue_style('source-sans-pro', "http://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700");
	
	wp_enqueue_style('merriweather', "http://fonts.googleapis.com/css?family=Merriweather:300,400,700,300italic,400italic,700italic");

    wp_enqueue_style('bootstrap', "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");

    wp_enqueue_style('font-awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css");

    //wp_enqueue_script ( 'bootstrap-dropdown', get_stylesheet_directory_uri() . '/js/dropdown.js', array('jquery'), '20161014' );
    wp_enqueue_script( 'bootstrap-js', "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );

    wp_enqueue_style( 'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}
add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );

/**
 * Implement the custom nav walker class to use bootstrap.
 */
require get_stylesheet_directory() . '/inc/wp_bootstrap_navwalker.php';
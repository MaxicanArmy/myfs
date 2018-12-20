<?php

/* vim: set expandtab ts=4 sw=4 autoindent smartindent: */

/**
 * myfossil functions and definitions
 *
 * @package myfossil
 */

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if (!isset($content_width)) {
    $content_width = 640; /* pixels */

}
if (!function_exists('myfossil_setup')):

    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function myfossil_setup()
    {
        /*
         * Make theme available for translation.
         * Translations can be filed in the /languages/ directory.
         * If you're building a theme based on myfossil, use a find and replace
         * to change 'myfossil' to the name of your theme in all the template files
        */
        load_theme_textdomain('myfossil', get_template_directory() . '/languages');

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        /*
         * Enable support for Post Thumbnails on posts and pages.
         *
         * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
        */
        //add_theme_support( 'post-thumbnails' );

        // This theme uses wp_nav_menu() in one location (for now).
        register_nav_menus(array(
            'primary' => __('Primary Menu', 'myfossil') ,
        ));

        /*
         * Switch default core markup for search form, comment form, and comments
         * to output valid HTML5.
        */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        /*
         * Enable support for Post Formats.
         * See http://codex.wordpress.org/Post_Formats
        */
        add_theme_support('post-formats', array(
            'aside',
            'image',
            'video',
            'quote',
            'link',
        ));

        // Setup the WordPress core custom background feature.
        add_theme_support('custom-background', apply_filters('myfossil_custom_background_args', array(
            'default-color' => 'ffffff',
            'default-image' => '',
        )));

        add_theme_support( 'post-thumbnails' );

        /*
         * Disable the admin bar
         */
        add_filter('show_admin_bar', '__return_false');
    }
endif; // myfossil_setup

add_action('after_setup_theme', 'myfossil_setup');

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function myfossil_widgets_init()
{
    register_sidebar(array(
        'name' => __('Sidebar', 'myfossil') ,
        'id' => 'sidebar-1',
        'description' => '',
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget' => '</aside>',
        'before_title' => '<h1 class="widget-title">',
        'after_title' => '</h1>',
    ));
}
add_action('widgets_init', 'myfossil_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function myfossil_scripts()
{
    /* Styles */
	wp_enqueue_style( 'dashicons' );
    //wp_enqueue_style('boostrap-css', "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css");
    wp_enqueue_style('bootstrap-theme', get_template_directory_uri() . '/css/bootstrap-theme.min.css');
    wp_enqueue_style('bootstrap-min', get_template_directory_uri() . '/css/bootstrap.min.css');
    wp_enqueue_style('myfossil-style', get_template_directory_uri() . '/style.css', array(), '2.3.0');
    wp_enqueue_style('font-awesome', "//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css");
    //wp_enqueue_style('jquery-ui-theme', get_template_directory_uri() . '/static/css/jquery-ui.theme.min.css');
    //wp_enqueue_style('jquery-ui-structure', get_template_directory_uri() . '/static/css/jquery-ui.structure.min.css');
    wp_enqueue_style('ionicons', '//code.ionicframework.com/ionicons/1.5.2/css/ionicons.min.css' );
    //wp_enqueue_style('mentions', get_template_directory_uri() . '/static/css/bp-activity-mentions.min.css');       
            

    /* Scripts */
    //wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/static/js/bootstrap.min.js' );
    wp_enqueue_script( 'myfossil', get_template_directory_uri() . '/static/js/myfossil.min.js' );
    //wp_enqueue_script( 'comment-reply', get_template_directory_uri() . '/static/js/comment-reply.min.js' );
    //wp_enqueue_script( 'html5', get_template_directory_uri() . '/static/js/html5.min.js' );
    //wp_enqueue_script( 'respond', get_template_directory_uri() . '/static/js/respond.min.js' );
    //wp_enqueue_script( 'jquery-ui', get_template_directory_uri() . '/static/js/jquery-ui.min.js' );
    wp_enqueue_script( 'jquery-popup-overlay', get_template_directory_uri() . '/js/jquery.popupoverlay.min.js' );
    wp_enqueue_script( 'bootstrap-js', "//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js");

    if ( is_singular() && comments_open() && get_option('thread_comments') ) {
        wp_enqueue_script('comment-reply');
    }
    
    //wp_enqueue_script ( 'buddypress', get_template_directory_uri() . '/static/js/jquery.atwho.min.js' );    
    //wp_enqueue_script ( 'bpcar', get_template_directory_uri() . '/static/js/jquery.caret.min.js' );
    //wp_enqueue_script ( 'bpmen', get_template_directory_uri() . '/static/js/mentions.min.js' );
    wp_enqueue_script ( 'atmo-custom', get_template_directory_uri() . '/js/custom.js', array(), '2.2.4' );

    global $is_IE;
    if (!$is_IE) {
    	wp_enqueue_script ( 'wysiwyg-post-form', get_template_directory_uri() . '/js/wysiwyg-post-form.js', array(), '1.0.0' );
    }
}
add_action('wp_enqueue_scripts', 'myfossil_scripts');

/* 
 * Required so buddypress doesn't override our stylesheet 
 */
function my_dequeue_bp_styles() {
	wp_dequeue_style( 'bp-legacy-css' );
}
add_action( 'wp_enqueue_scripts', 'my_dequeue_bp_styles', 20 );

/**
 * Implement the Custom nav walker class to use bootstrap.
 */
require get_template_directory() . '/includes/wp_bootstrap_navwalker.php';

/**
 * Implement the Custom Header feature.
 */
// require get_template_directory() . '/includes/custom-header.php';

/**
 * Custom WordPress/BuddyPress filter hooks
 */
require get_template_directory() . '/includes/filters.php';

/**
 * Load plugins compatibility file.
 */
require get_template_directory() . '/includes/plugins.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/includes/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/includes/extras.php';

/**
 * Customizer additions.
 */
//require get_template_directory() . '/includes/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
//require get_template_directory() . '/includes/jetpack.php';

/**
 * Load bbPress compatibility file.
 */
// require get_template_directory() . '/includes/bbpress.php';


/*
 * This filter is required so that buddypress entries are not truncated. The fossil histories are buddypress posts in json format and if they are truncated then they are not recognizable json 
*/
function filter_bp_activity_excerpt_length() {
    return 1e+10;
}
add_filter( 'bp_activity_excerpt_length', 'filter_bp_activity_excerpt_length');
/*
function parse_meta( $meta ) {
    if ( ! $meta ) return array();

    $parsed_meta = array();
    foreach ( $meta as $k => $v )
        if ( !( strpos( $k, '_', 0) === 0 ) )
            $parsed_meta[ $k ] = $v[0];
    return $parsed_meta;
}


// change Notification labels
function mf_gettext_with_context( $translated, $text, $context, $domain ) {

	if ( 'buddypress' !== $domain )  
        return $translated; 
        
	switch ( $text ) {

        case 'Read':
            if( $context == 'Notification screen nav' )
                return 'Viewed';
			elseif( $context == 'Notification screen action' )
				return 'Mark as Viewed';
			else
                return 'Read';		
	
        case 'Unread':
            if( $context == 'Notification screen nav' )
                return 'Not Viewed';
			elseif( $context == 'Notification screen action' )
				return 'Mark as Not Viewed';
			else
				return 'Unread';
			
        default:
            return $translated;
    }

    return $translated;
}
add_filter( 'gettext_with_context', 'mf_gettext_with_context', 20, 4 );

// change Notification feedback messages
function mf_gettext( $translated_text, $text, $domain ) {
	
	if ( 'buddypress' !== $domain )  
        return $translated_text; 
        
	switch ( $text ) {
	
		case 'Notification successfully marked read.':
			return 'Notification successfully marked as Viewed.';
	
		case 'Notification successfully marked unread.':
			return 'Notification successfully marked as Not Viewed.';		
		
        default:
            return $translated_text;	
	
	}
}
add_filter( 'gettext', 'mf_gettext', 21, 3 );


function mf_remove_xprofile_links() {
    remove_filter( 'bp_get_the_profile_field_value', 'xprofile_filter_link_profile_data', 9, 2 );
}
add_action('bp_setup_globals', 'mf_remove_xprofile_links'); 




function mf_joined_group_action( $action, $activity ) {
	
	$action = str_replace ( 'joined the group', 'followed the group', $action );

	return $action;
	
}
add_filter( 'bp_groups_format_activity_action_joined_group', 'mf_joined_group_action', 25, 2 );


function mf_remove_activity_mentions_nav() {
	bp_core_remove_subnav_item( 'activity', 'mentions' );
}
add_action( 'bp_setup_nav', 'mf_remove_activity_mentions_nav', 25 );

// remove 'mentions' from link since 'personal' & 'mentions' are now combined
function mf_adjust_notification_description( $description ) {
	
	$description = str_replace('mentions/', '', $description);
	
	return $description;
	
}
add_filter('bp_get_the_notification_description', 'mf_adjust_notification_description', 25 );


//change the message re @ mention emails
function mf_change_email_message( $message, $poster_name, $content, $message_link, $settings_link ) {
	
	$message = str_ireplace('To view and respond to the message, log in and visit:', "Please DO NOT RESPOND TO THIS EMAIL; you must log in to the website to respond.  See link below.\n", $message);
	
	return $message; 
}
add_filter( 'bp_activity_at_message_notification_message', 'mf_change_email_message', 25, 5);

// find plain urls in activity content and auto link to new window
function mf_autolink_activity_content_body( $str ) {
	
	$str = links_add_target( make_clickable( $str ) );
	
	return $str;
	
	
}
add_filter( 'bp_get_activity_content_body', 'mf_autolink_activity_content_body' );


// open url in new window
function mf_activity_comment_new_window( $content ) {
	
	$content = links_add_target( $content );
	
	return $content; 
	
}
add_filter( 'bp_activity_comment_content', 'mf_activity_comment_new_window' );
*/

// even more change Notification labels -required because BP is inconsistent re gettext usage
function mf_change_notification_mark_link( $retval ) {
	$retval = str_replace('Read', 'Mark as Read', $retval);
	return $retval;
}
add_filter('bp_get_the_notification_mark_link', 'mf_change_notification_mark_link', 22, 1);

//limit access to wp-admin to admins only
add_action( 'init', 'blockusers_init' );
function blockusers_init() {
    if ( is_admin() && !current_user_can( 'administrator' ) && !( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
        wp_redirect( home_url() );
        exit;
    }
}

// this is to add a fake component to BuddyPress. A registered component is needed to add notifications
function custom_filter_notifications_get_registered_components( $component_names = array() ) {
	// Force $component_names to be an array
	if ( ! is_array( $component_names ) ) {
		$component_names = array();
	}
	// Add 'myfossil_notification' component to registered components array
	array_push( $component_names, 'myfossil_notification' );
	array_push( $component_names, 'myfsapp' );
	// Return component's with 'myfossil_notification' appended
	return $component_names;
}
add_filter( 'bp_notifications_get_registered_components', 'custom_filter_notifications_get_registered_components' );

// this gets the saved item id, compiles some data and then displays the notification
function custom_format_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
	// New custom notifications

	if ( 'fossil_comment_posted' === $action ) {

		$custom_title = 'fossil comment';
		$custom_link  = '/fossils/' . $item_id . '/';
		$custom_text = bp_core_get_user_displayname( $secondary_item_id ) . ' commented on your fossil';

		// WordPress Toolbar
		if ( 'string' === $format ) {
			$return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
		// Deprecated BuddyBar
		} else {
			$return = apply_filters( 'custom_filter', array(
				'text' => $custom_text,
				'link' => $custom_link
			), $custom_link, (int) $total_items, $custom_text, $custom_title );
		}
	} else if ( 'fossil_comment_reply_posted' === $action ) {

		$custom_title = 'fossil comment reply';
		$custom_link  = '/fossils/' . $item_id . '/';
		$custom_text = bp_core_get_user_displayname( $secondary_item_id ) . ' replied to a comment on your fossil';

		// WordPress Toolbar
		if ( 'string' === $format ) {
			$return = apply_filters( 'custom_filter', '<a href="' . esc_url( $custom_link ) . '" title="' . esc_attr( $custom_title ) . '">' . esc_html( $custom_text ) . '</a>', $custom_text, $custom_link );
		// Deprecated BuddyBar
		} else {
			$return = apply_filters( 'custom_filter', array(
				'text' => $custom_text,
				'link' => $custom_link
			), $custom_link, (int) $total_items, $custom_text, $custom_title );
		}
	} else {
		$return = $action;
	}

	return $return;
}
add_filter( 'bp_notifications_get_notifications_for_user', 'custom_format_buddypress_notifications', 10, 5 );

// send fossil owner an email when a new activity entry is created on one of their fossils
function mf_fossil_activity_posted( $post_id, $sender_id, $comment, $fossil_author_id ) {
	global $wpdb;

	if (( ! get_user_meta( $fossil_author_id, 'notification_fossils', true ) || 'yes' == get_user_meta( $fossil_author_id, 'notification_fossils', true )) && $sender_id != $fossil_author_id) {
	
		$sender_name = $wpdb->get_var( $wpdb->prepare( 
			"SELECT display_name FROM $wpdb->users WHERE ID = %d", 
			$sender_id
		) );
		
		$fossil_author_mail = get_the_author_meta( 'user_email', $fossil_author_id );
		$fossil_author_login_name = get_the_author_meta( 'user_login', $fossil_author_id );
		
		$to =  $fossil_author_mail;
		
		$subject = '[myFOSSIL] Comment on Fossil ' . $post_id;
		
		$message = $sender_name . ' just commented on your fossil!  Log in to view the comment and respond.';
		
		$message .= '<br/><br/>Comment: ' . stripslashes( $comment );
		
		$message .= '<br/><br/>Please DO NOT RESPOND TO THIS EMAIL; you must log in to the website to respond.  See link below.<br/>';
		
		$message .= '<a href="' . site_url() . '/fossils/' . $post_id . '">' . site_url() . '/fossils/' . $post_id . '</a>';
	
		$message .= '<br/><br/>---------------------<br/>';
		
		$message .= 'To disable these notifications please log in and go to: ';
		
		$message .= '<br/><a href="' . site_url() . '/members/' . $fossil_author_login_name . '/settings/notifications/">' . site_url() . '/members/' .  $fossil_author_login_name . '/settings/notifications/</a>';
	
	
		$headers = array();
		$headers[] = "Content-type: text/html";
		
		wp_mail( $to, $subject, $message, $headers  );

		//generate the buddypress notification
		$output = array(
	        'user_id'           => $fossil_author_id,
	        'item_id'           => $post_id,
	        'secondary_item_id' => $sender_id,
	        'component_name'    => 'myfossil_notification',
	        'component_action'  => 'fossil_comment_posted',
	        'date_notified'     => bp_core_current_time(),
	        'is_new'            => 1,
	    );
	    bp_notifications_add_notification( $output );
	
	}
}
add_action( 'myfossil_fossil_activity_posted', 'mf_fossil_activity_posted', 10, 4 );

// send fossil owner an email when a COMMENT is made on an activity entry on one of their fossils
function my_fossil_activity_comment_posted( $comment_id, $r, $activity ) {
	global $wpdb;
	
	$comment_row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}bp_activity WHERE id = $comment_id", ARRAY_A );
	
	$item_id = $comment_row['item_id'];
	
	$comment_parent_row = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}bp_activity WHERE id = $item_id", ARRAY_A );
	
	
	if ( $comment_parent_row['type'] == 'myfossil_fossil_comment' ) {
	
		if ( ! get_user_meta( $comment_parent_row['secondary_item_id'], 'notification_fossils', true ) || 'yes' == get_user_meta( $comment_parent_row['secondary_item_id'], 'notification_fossils', true ) ) {
	
			$fossil_author_mail = get_the_author_meta( 'user_email', $comment_parent_row['secondary_item_id'] );
			$fossil_author_login_name = get_the_author_meta( 'user_login', $comment_parent_row['secondary_item_id']  );	
		
			$to =  $fossil_author_mail;
			
			$subject = '[myFOSSIL] Comment on Fossil ' .  $comment_parent_row['item_id'];	
		
			$sender_name = $wpdb->get_var( $wpdb->prepare( 
				"SELECT display_name FROM $wpdb->users WHERE ID = %d", 
				$comment_row['user_id']
			) );
		
		
			$message = $sender_name . ' just replied to a comment on your fossil!  Log in to view the comment and respond.';
			
			$message .= '<br/><br/>Comment: ' . stripslashes( $comment_row['content'] );
			
			$message .= '<br/><br/>Please DO NOT RESPOND TO THIS EMAIL; you must log in to the website to respond.  See link below.<br/>';
			
			$message .= '<a href="' . site_url() . '/fossils/' . $comment_parent_row['item_id'] . '">' . site_url() . '/fossils/' . $comment_parent_row['item_id'] . '</a>';
		
			$message .= '<br/><br/>---------------------<br/>';
			
			$message .= 'To disable these notifications please log in and go to: ';
			
			$message .= '<br/><a href="' . site_url() . '/members/' . $fossil_author_login_name . '/settings/notifications/">' . site_url() . '/members/' .  $fossil_author_login_name . '/settings/notifications/</a>';
	
			
			$headers = array();
			$headers[] = "Content-type: text/html";
			
			wp_mail( $to, $subject, $message, $headers  );

			//generate the buddypress notification if the comment parent is not the fossil owner
			if ( $comment_parent_row['secondary_item_id'] !== $comment_row['user_id'] ) {
				$output = array(
			        'user_id'           => $comment_parent_row['secondary_item_id'],
			        'item_id'           => $comment_parent_row['item_id'],
			        'secondary_item_id' => $comment_row['user_id'],
			        'component_name'    => 'myfossil_notification',
			        'component_action'  => 'fossil_comment_reply_posted',
			        'date_notified'     => bp_core_current_time(),
			        'is_new'            => 1,
			    );
			    bp_notifications_add_notification( $output );
			}
		}
	}
}
add_action( 'bp_activity_comment_posted', 'my_fossil_activity_comment_posted', 15, 3 );

// turn on / off emails to fossil owner when dicussion content is created on their fossil pages
function mf_fossil_notification_settings() {
	global $current_user;

	?>

		<table class="notification-settings" id="groups-notification-settings">
	
			<thead>
			<tr>
				<th class="icon"></th>
				<th class="title"><?php _e( 'Fossils', 'buddypress' ) ?></th>
				<th class="yes"><?php _e( 'Yes', 'buddypress' ) ?></th>
				<th class="no"><?php _e( 'No', 'buddypress' )?></th>
			</tr>
			</thead>
	
			<tbody>
			<tr id="fossils-notification-settings-comments">
				<td></td>
				<td><?php _e( 'A comment is made on one of your Fossils', 'buddypress' ) ?></td>
				<td class="yes"><input type="radio" name="notifications[notification_fossils]" value="yes" <?php if ( !get_user_meta( $current_user->ID, 'notification_fossils', true ) || 'yes' == get_user_meta( $current_user->ID, 'notification_fossils', true ) ) { ?>checked="checked" <?php } ?>/></td>
				<td class="no"><input type="radio" name="notifications[notification_fossils]" value="no" <?php if ( get_user_meta( $current_user->ID, 'notification_fossils', true ) == 'no' ) { ?>checked="checked" <?php } ?>/></td>
			</tr>
	
			<?php do_action( 'mf_fossil_notification_settingss' ); ?>
	
			</tbody>
		</table>
<?php
}
add_action( 'bp_notification_settings', 'mf_fossil_notification_settings', 25 );


/**
 * Alters login screen, changing registration URL
 */

function mf_ufl_register( $registration_url ) {
	//$registration_url = sprintf( '<a href="%s">%s</a>', esc_url( wp_registration_url() ), __( 'Register' ) );
	
	$registration_url = '<a href="https://www.myfossil.org/register/" target="_blank">Create New Account!</a><br/>&nbsp;<br/>';
	
	return $registration_url;
	
}
add_filter( 'register', 'mf_ufl_register', 15, 1 );
//apply_filters( 'register', $registration_url )  in wp-login.php

/**
 * return an int of the number of Fossils for a specific member
 * called in \myfossil-theme\buddypress\members\members-loop.php
 */
function mf_member_fossil_count( $user_id ) {
	global $wpdb; 
	
	$fossil_count = $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'myfossil_fossil' AND post_author = $user_id AND post_status = 'publish'" );	
	
	if( $fossil_count == NULL )
		$fossil_count = 0;
	
	return $fossil_count; 
	
}

//enable @mentions on any page
function custom_bbpress_maybe_load_mentions_scripts( $retval = false ) {

	return true;
}
add_filter( 'bp_activity_maybe_load_mentions_scripts', 'custom_bbpress_maybe_load_mentions_scripts' );

//enable visual editor in forums
function bbp_enable_visual_editor( $args = array() ) {
	global $is_IE;

	if (!$is_IE) {
	    $args['tinymce'] = get_tiny_toolbars();
	    $args['teeny'] = false;
	    $args['media_buttons'] = false;
	    $args['quicktags'] = false;
	    add_filter( 'tiny_mce_before_init', 'bpfr_remove_menu_tiny_editor', 11 ); //this function is found in myFOSSIL2016 functions.php and removes the menu bar
	}
    return $args;
}
add_filter( 'bbp_after_get_the_content_parse_args', 'bbp_enable_visual_editor' );

// Edit TinyMCE
function myformatTinyMCE($in) {
    $in['statusbar'] = false;

    return $in; 
}
add_filter('tiny_mce_before_init', 'myformatTinyMCE' );

//Use this function with add_filter tiny_mce_before_init to remove the menubar from custom tinymce inputs
function bpfr_remove_menu_tiny_editor($settings) {
    $settings['menubar'] = false;

    return $settings;
}

add_filter('tiny_mce_before_init', 'vipx_filter_tiny_mce_before_init');
function vipx_filter_tiny_mce_before_init( $options ) {
 
    if ( ! isset( $options['extended_valid_elements'] ) ) {
        $options['extended_valid_elements'] = '';
    } else {
        $options['extended_valid_elements'] .= ',';
    }
 
    if ( ! isset( $options['custom_elements'] ) ) {
        $options['custom_elements'] = '';
    } else {
        $options['custom_elements'] .= ',';
    }
 
    $options['extended_valid_elements'] .= 'font[face|size|color|style]';
    $options['custom_elements']         .= 'font[face|size|color|style]';
    return $options;
}

/**
 * All tinyMCE inputs will have consistent toolbars when initializing tinyMCE settings array by using this function
 */
function get_tiny_toolbars() {
	return array(
            'toolbar1' => 'bold, italic, underline, strikethrough, |, bullist, numlist, |, forecolor, |, emoticons, |, link, unlink, |, undo, redo',
            'toolbar2' => ''
        );
}

/**
 * Override attributes allowed in buddypress posts
 */
function myfs_allowed_tags() {
	global $allowedtags;

	$allowedtags['span']['style'] = array();
	$allowedtags['span']['font'] = array();
	$allowedtags['ol'] = array();
	$allowedtags['ol']['style'] = array();
	$allowedtags['ul'] = array();
	$allowedtags['ul']['style'] = array();
	$allowedtags['li'] = array();
}
add_action( 'bp_activity_allowed_tags', 'myfs_allowed_tags', 10 );


function myfossil_group_creation_tabs() {
    global $bp;

    if ( !is_array( $bp->groups->group_creation_steps ) )
        return false;

    if ( !bp_get_groups_current_create_step() ) {
        $keys = array_keys( $bp->groups->group_creation_steps );
        $bp->groups->current_create_step = array_shift( $keys );
    }

    foreach ( (array) $bp->groups->group_creation_steps as $slug => $step ) {
        $is_enabled = bp_are_previous_group_creation_steps_complete( $slug );

        $selected = ( bp_get_groups_current_create_step() == $slug );
        if ( $selected ) {
            $tpl = '<li class="current active selected">';
        } else {
            $tpl = "<li>";
        }

        if ( $is_enabled ) {
            $tpl .= sprintf( '<a href="%s/%s/create/step/%s">%s</a>',
                    bp_get_root_domain(),
                    bp_get_groups_root_slug(),
                    $slug,
                    $step['name'] );
        } else {
            $tpl .= sprintf( '<a>%s</a>', $step['name'] );
        }

        $tpl .= "</li>";

        print $tpl;
    }

    unset( $is_enabled );

    do_action( 'groups_creation_tabs' );
}

function return_nav_echo() {
    ob_start();
    bp_get_loggedin_user_nav();
    return ob_get_clean();
}

/**
 * I don't know how the "activity" page is even being created on the groups page but this function removes it
 */
function bpex_remove_group_tabs() {  

/**
 * @since 2.6.0 Introduced the $component parameter.
 *
 * @param string $slug      The slug of the primary navigation item.
 * @param string $component The component the navigation is attached to. Defaults to 'members'.
 * @return bool Returns false on failure, True on success.
 */ 

	if ( ! bp_is_group() ) {
		return;
	}

	$slug = bp_get_current_group_slug();
        // all existing default group tabs are listed here. Uncomment or remove.
	//	bp_core_remove_subnav_item( $slug, 'home' );
		bp_core_remove_subnav_item( $slug, 'activity' );
	//	bp_core_remove_subnav_item( $slug, 'members' );
	//	bp_core_remove_subnav_item( $slug, 'send-invites' );
	//	bp_core_remove_subnav_item( $slug, 'admin' );
	//	bp_core_remove_subnav_item( $slug, 'forum' );

}
add_action( 'bp_actions', 'bpex_remove_group_tabs' );

// Rename Buddydrive tab on members page 
function rt_change_profile_tab_order() {
	global $bp;
	
	$bp->bp_nav['buddydrive']['name'] = 'My Files';
}
add_action( 'bp_setup_nav', 'rt_change_profile_tab_order', 999 );

//hook when user registers
add_action( 'user_register', 'myplugin_registration_save', 10, 1 );

function myplugin_registration_save( $user_id ) {

    // insert meta that user not logged in first time
    update_user_meta($user_id, 'prefix_first_login', '1');

}
/* No longer necessary as users go through the normal register -> activate -> login flow now, rather than Kent  creating their account 
// hook when user logs in
add_action('wp_login', 'your_function', 10, 2);

function your_function($user_login, $user) {

    $user_id = $user->ID;
    // getting prev. saved meta
    $registered = get_userdata( $user_id )->user_registered;
    $first_login = get_user_meta($user_id, 'prefix_first_login', true);
    // if first time login
    if( $first_login == '1' && strtotime( $registered ) > 1479762867 ) { //this timestamp needs to be changed when we do the merge
        update_user_meta($user_id, 'prefix_first_login', '0');
    	DP_Welcome_Pack::user_activated($user_id);
    }
}

*/
add_filter ('bps_field_data_for_search_form', 'create_states_dropdown');
function create_states_dropdown ($f)
{
    if ($f->label == 'Location')
    {
        $f->display = 'selectbox';
        $f->options = array(
			', AL'=>'ALABAMA',
			', AK'=>'ALASKA',
			', AZ'=>'ARIZONA',
			', AR'=>'ARKANSAS',
			', CA'=>'CALIFORNIA',
			', CO'=>'COLORADO',
			', CT'=>'CONNECTICUT',
			', DE'=>'DELAWARE',
			', DC'=>'DISTRICT OF COLUMBIA',
			', FL'=>'FLORIDA',
			', GA'=>'GEORGIA',
			', HI'=>'HAWAII',
			', ID'=>'IDAHO',
			', IL'=>'ILLINOIS',
			', IN'=>'INDIANA',
			', IA'=>'IOWA',
			', KS'=>'KANSAS',
			', KY'=>'KENTUCKY',
			', LA'=>'LOUISIANA',
			', ME'=>'MAINE',
			', MD'=>'MARYLAND',
			', MA'=>'MASSACHUSETTS',
			', MI'=>'MICHIGAN',
			', MN'=>'MINNESOTA',
			', MS'=>'MISSISSIPPI',
			', MO'=>'MISSOURI',
			', MT'=>'MONTANA',
			', NE'=>'NEBRASKA',
			', NV'=>'NEVADA',
			', NH'=>'NEW HAMPSHIRE',
			', NJ'=>'NEW JERSEY',
			', NM'=>'NEW MEXICO',
			', NY'=>'NEW YORK',
			', NC'=>'NORTH CAROLINA',
			', ND'=>'NORTH DAKOTA',
			', OH'=>'OHIO',
			', OK'=>'OKLAHOMA',
			', OR'=>'OREGON',
			', PA'=>'PENNSYLVANIA',
			', RI'=>'RHODE ISLAND',
			', SC'=>'SOUTH CAROLINA',
			', SD'=>'SOUTH DAKOTA',
			', TN'=>'TENNESSEE',
			', TX'=>'TEXAS',
			', UT'=>'UTAH',
			', VT'=>'VERMONT',
			', VA'=>'VIRGINIA',
			', WA'=>'WASHINGTON',
			', WV'=>'WEST VIRGINIA',
			', WI'=>'WISCONSIN',
			', WY'=>'WYOMING',
			);
    }

    return $f;
}

// this function extends the password reset link expiration time to 30 times the default 
function filter_password_reset_expiration($day_in_seconds){
	$week = $day_in_seconds * 7;
	return $week;
}

// add the filter 
add_filter( 'password_reset_expiration', 'filter_password_reset_expiration', 10, 1 );

/**
 * Modify the password hint
 */
add_filter( 'password_hint', function( $hint )
{
  return __( 'This generated password is only a suggestion. It&rsquo;s best to use upper and lower case letters with numbers and symbols, but not required.' );
} );

/* Analytics Code */
function gadwp_addcode($gadwp) {
	$commands = $gadwp->get(); // Get commands array
	$fields = array();
	$fields['option'] = 'userId';
	$fields['value'] = get_current_user_id();
	if ($fields['value']){
		$command = array($gadwp->prepare( 'set', $fields ));
		array_splice($commands, -1, 0, $command); //insert the command before send
	}
	$gadwp->set($commands); // Store the new commands array
}
add_action( 'gadwp_analytics_commands',  'gadwp_addcode', 10, 1 );

/* Remove from profile any registration fields that users can't edit later */
function bpfr_hide_profile_edit( $retval ) {	
	// remove field from edit tab
	if(  bp_is_profile_edit() ) {
		$retval['exclude_fields'] = '13,14,15,16,24,25,28,36,44,45,55,56,59,62,65,75,81,91,92,98,111,203'; // ID's separated by comma
	}		
	
	// hide the field on profile view tab
	 else if ( bp_is_member() ) {
		$retval['exclude_fields'] = '13,14,15,16,24,25,28,36,44,45,55,56,59,62,65,75,81,91,92,98,111,203'; // ID's separated by comma
	}	
	// allow field on registration page     
	if ( bp_is_register_page() ) {
		$retval['exclude_fields'] = '12,3,4,2,5,6,7,8,9,10,11'; // ID's separated by comma
	}
	/*
	if ( $data = bp_get_profile_field_data( 'field=3' ) ) : 
		$retval['exclude_fields'] = '13,14,15,16,24,25,28,36,44,45,55,56,59,62,65,75,81,91,92,98,111'; // ID's separated by comma
	endif;
	*/	
	
	return $retval;	
}
add_filter( 'bp_after_has_profile_parse_args', 'bpfr_hide_profile_edit' );

/* Create username from First & Last Name */
function bp_auto_generate_username(){

    $my_post = $_POST;
    $base = preg_replace( '/[^A-Za-z\-]/', '', strtolower( trim( $my_post['field_13'] ).'-'.trim( $my_post['field_14'] ) ) );
    $username = $base;
    $count = 2;

    while ( username_exists( $username ) ) {
    	$username = $base.'-'.$count++;
    } 

    $_POST['signup_username'] = $username;
    $_POST['field_1'] = $my_post['field_13'].' '.$my_post['field_14'];
    $_POST['field_5'] = $my_post['field_203'];

}
add_action( 'bp_signup_pre_validate', 'bp_auto_generate_username' );

/* Validate conditional intake forms */
function bp_validate_conditional_registration(){

    global $bp;
   	unset($bp->signup->errors['signup_username']); 

    if ( !isset($_POST['field_343'] ) ) { 
    	return;
    } else {
    	if ( $_POST['field_343'] === 'Yes' ) {
    		unset($bp->signup->errors['field_346']);
    		unset($bp->signup->errors['field_349']);
    		unset($bp->signup->errors['field_350']);
    		unset($bp->signup->errors['field_351']);
    	} 
    	else if ( $_POST['field_343'] === 'No' ) {
    		unset($bp->signup->errors['field_25']);
    		
    		if ( !isset( $_POST['field_346'] ) ) {
    			return;
    		} else {
    			if ( strpos($_POST['field_346'], 'No') !== false ) {
    				unset($bp->signup->errors['field_203']);
    				unset($bp->signup->errors['field_15']);
    				unset($bp->signup->errors['field_16']);
    				unset($bp->signup->errors['field_28']);
    				unset($bp->signup->errors['field_36']);
    				unset($bp->signup->errors['field_45']);
    				unset($bp->signup->errors['field_56']);
    				unset($bp->signup->errors['field_59']);
    				unset($bp->signup->errors['field_62']);
    				unset($bp->signup->errors['field_65']);
    				unset($bp->signup->errors['field_75']);
    				unset($bp->signup->errors['field_92']);
    				unset($bp->signup->errors['field_98']);
    				unset($bp->signup->errors['field_111']);
    				unset($bp->signup->errors['field_349']);
    				unset($bp->signup->errors['field_350']);
    				unset($bp->signup->errors['field_351']);
    			}
    		}
    	}
    }
}
add_action( 'bp_signup_validate', 'bp_validate_conditional_registration' );

function current_user_has_avatar( $user_id) {
	global $bp;

	if (strpos(bp_core_fetch_avatar( array( 'item_id' => $user_id, 'no_grav' => true, 'html'=>false) ), 'mystery-man') === false)	
		return true;

	return false;
}


/*
function myfs_bpro_bp_gate_compose( $allowed ) {
	if (bp_is_messages_compose_screen()) {
		$allowed = false;
	}

	return $allowed;
}
add_filter( 'bprwg_buddypress_allowed_areas', 'myfs_bpro_bp_gate_compose' );

function myfs_bpro_bbpress_gate_compose( $allowed ) {
	if (bp_is_messages_compose_screen()) {
		$allowed = false;
	}

	return $allowed;
}
add_filter( 'bprwg_bbpress_allowed_areas', 'myfs_bpro_bbpress_gate_compose' );
*/	
function bpfr_hide_tabs() {
	global $bp;
	if ( bp_is_user() && get_user_meta(get_current_user_id(), '_bprwg_is_moderated', true) == 'true' ) {
		bp_core_remove_nav_item( 'files' );
		bp_core_remove_subnav_item( 'messages', 'compose' );
	}	
}
add_action( 'bp_setup_nav', 'bpfr_hide_tabs', 15 );


/**
 * Filter the values displayed in the darwin-core gallery
 */
function alter_darwin_core_gallery_location( $terms ) {

	if ( empty( $terms['disclosed']['value'] ) ) {
		$terms = array();
	}
	else {
		unset( $terms['disclosed'] );
		unset( $terms['decimalLatitude'] );
		unset( $terms['decimalLongitude'] );
		unset( $terms['county'] );
	}

	return $terms;
}
add_filter( 'darwin_core_precise_meta_Location', 'alter_darwin_core_gallery_location' );

function myfs_set_location_disclosed_true( $id ) {
	update_post_meta( $id, 'Location_disclosed', 'true' );
}
add_action( 'darwin_core_create_specimen', 'myfs_set_location_disclosed_true');


function wds_change_bpro_new_user_notifications( $original_users ) {
	return array( get_bloginfo( 'admin_email' ) );
}
add_filter( 'bprwg_bp_notification_users', 'wds_change_bpro_new_user_notifications' );

add_filter( 'bbp_kses_allowed_tags', 'ntwb_bbpress_custom_kses_allowed_tags' );
function ntwb_bbpress_custom_kses_allowed_tags() {
	return array(
		// Links
		'a'          => array(
			'class'    => true,
			'href'     => true,
			'title'    => true,
			'rel'      => true,
			'class'    => true,
			'target'    => true,
		),
		// Quotes
		'blockquote' => array(
			'cite'     => true,
		),
		
		// Div
		'div' => array(
			'class'     => true,
		),
		
		// Span
		'span'             => array(
			'class'     => true,
			'atwho-inserted' => true,
			'contenteditable' => true,
			'data-atwho-at-query' => true
		),
		
		// Code
		'code'       => array(),
		'pre'        => array(
			'class'  => true,
		),
		// Formatting
		'em'         => array(),
		'strong'     => array(),
		'del'        => array(
			'datetime' => true,
		),
		// Lists
		'ul'         => array(),
		'ol'         => array(
			'start'    => true,
		),
		'li'         => array(),
		// Images
		'img'        => array(
			'class'    => true,
			'src'      => true,
			'border'   => true,
			'alt'      => true,
			'height'   => true,
			'width'    => true,
		),
		// Tables
		'table'      => array(
			'align'    => true,
			'bgcolor'  => true,
			'border'   => true,
		),
		'tbody'      => array(
			'align'    => true,
			'valign'   => true,
		),
		'td'         => array(
			'align'    => true,
			'valign'   => true,
		),
		'tfoot'      => array(
			'align'    => true,
			'valign'   => true,
		),
		'th'         => array(
			'align'    => true,
			'valign'   => true,
		),
		'thead'      => array(
			'align'    => true,
			'valign'   => true,
		),
		'tr'         => array(
			'align'    => true,
			'valign'   => true,
		)
	);
}
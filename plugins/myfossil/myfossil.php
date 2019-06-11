<?php
/**
 * Plugin Name: myFOSSIL
 * Description: Filters and Actions that support the myFOSSIL website and app on the site and also extends Wordpress REST Controllers to manage requests from the app.
 * Version: 0.1.0
 * Author: makkusu
 * Author URI: https://www.atmosphereapps.com
 *
 * @package myfossil
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/*
// Define constants.
define( 'MYFOSSIL_PLUGIN_VERSION', '1.0.0' );
define( 'MYFOSSIL_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

// Requirements.
require_once( ABSPATH . 'wp-content/plugins/rest-api/plugin.php');

// Main instance of plugin.
function myfs_rest_activity_controller() {
    return MYFS_REST_Activity_Controller::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['myfs_rest_activity_controller'] = myfs_rest_activity_controller();
*/
if ( !class_exists( 'MYFOSSIL' ) ) :
/**
 * Main myFOSSIL Class
 */
final class MYFOSSIL {
	/** Singleton *************************************************************/

	/**
	 * Main MYFOSSIL Instance
	 *
	 * Insures that only one instance of MYFOSSIL exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new MYFOSSIL;
			$instance->setup();
		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent myfossil from being loaded more than once.
	 *
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent myfossil from being cloned
	 *
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'myfossil' ), '2.1' ); }

	/**
	 * A dummy magic method to prevent myfossil from being unserialized
	 *
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'myfossil' ), '2.1' ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls
	 *
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Setup the default hooks and actions
	 */
	private function setup() {

		require plugin_dir_path( __FILE__ ) . 'myfs-core/myfs-core-functions.php';

		//need to remove these filters or the lightbox for images in the groups will be all one long slideshow
		remove_filter( 'bp_get_activity_content',               'bp_activity_make_nofollow_filter' );
		remove_filter( 'bp_get_activity_content_body',          'bp_activity_make_nofollow_filter' );
		remove_filter( 'bp_get_activity_parent_content',        'bp_activity_make_nofollow_filter' );
		remove_filter( 'bp_get_activity_latest_update',         'bp_activity_make_nofollow_filter' );
		remove_filter( 'bp_get_activity_latest_update_excerpt', 'bp_activity_make_nofollow_filter' );
		remove_filter( 'bp_get_activity_feed_item_description', 'bp_activity_make_nofollow_filter' );

		add_filter( 'bp_get_activity_content_body', array( $this, 'myfs_app_cleanse_newlines' ), 15, 1 );

		add_action('bp_init', array($this, 'myfs_add_shortcodes_to_activity_stream' ) );

		add_shortcode( 'dwc-specimen-created', array( $this, 'dwc_bp_specimen_shortcode' ) );
		add_shortcode( 'myfs-app-image', array( $this, 'myfs_app_image_shortcode' ) );

		add_filter( 'jwt_auth_token_before_dispatch', array ( $this, 'add_data_jwt_auth_response' ), 10, 2 );
		add_filter( 'jwt_auth_expire', array ( $this, 'extend_jwt_token_exp' ) );
		add_filter( 'bp_notifications_get_registered_components', array( $this, 'myfs_app_notifications_get_registered_components' ) );
		add_filter( 'bp_notifications_get_notifications_for_user', array( $this, 'myfs_app_buddypress_notifications' ), 10, 5 );

		add_action( 'bp_activity_after_save', array( $this, 'myfs_bp_activity_add_app_meta' ), 10, 1 );
	}

	/** Public Methods *******************************************************/

	public function myfs_bp_activity_add_app_meta( &$r ) {
		$app_item = false;

		switch ( $r->type ) {
			case 'dwc_specimen_created' :
			case 'app_image_update' :
				$app_item = true;
				break;
			case 'activity_comment' :
				if ( bp_activity_get_meta( $r->item_id, 'app_content' ) == 1 ) {

					$a = bp_activity_get( array( 'in' => array( $r->item_id ), 'max' => 1 ) );

					if ( $a['activities'][0]->user_id != get_current_user_id() ) {

						$notification_args = array(
							'recipient' => $a['activities'][0]->user_id,
							'title' => 'New comment!',
							'body' => bp_core_get_user_displayname( $r->user_id ).' commented on one of your posts.',
						);
						$push_result = myfs_send_push_notifications( $notification_args );
					}
				}
				break;
			case 'activity_update' :
				if ( $r->component == 'groups' )
					if ( groups_get_groupmeta( $r->item_id, 'type' ) == 'app-group' )
						$app_item = true;
				break;
		}

		if ( $app_item )
			bp_activity_add_meta( $r->id, 'app_content', true );
	}

	//This filter removes newlines and whitespace between HTML tags from buddypress posts so that image galleries fit on one line
	/* THIS IS USING REGEX ON HTML WHICH IS A SUPER BAD IDEA, THIS NEEDS TO BE REVIEWED AND CLEANED UP I SHOULD THINK*/
	public function myfs_app_cleanse_newlines( $content ) {
	    $content = preg_replace( "/(\>)\s*(\<)/m", '$1$2', str_replace( array( "\r", "\n" ), '', $content ) );

	    return $content;
	}

	//turns dwc-specimen shortcodes in to thumbnails that link to a lightbox image gallery for the activity stream
	public function dwc_bp_specimen_shortcode( $atts, $content = '', $tag = '' ) {
		$result = "";
		$ac_query = new WP_Query( array( 'posts_per_page' => 5, 'post_type' => 'ac_media', 'post_parent' => $atts['id'] ) );
		$ac_media = array();

		if ( $ac_query->have_posts() ) {
			while ( $ac_query->have_posts() ) {
				$ac_query->the_post();
				$ac_media[] = get_the_ID();
			}

			$att_query = new WP_Query( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_parent__in' => $ac_media ) );
			$attachments = array();

			while ( $att_query->have_posts() ) {
				$att_query->the_post();
				$attachments[] = get_the_ID();
			}

			$result = gallery_shortcode( array( 'id' => $atts['id'], 'ids' => $attachments, 'columns' =>  5, 'link' => 'file' ) );
		}

		return $result;
	}

	//turns app image shortcodes in to thumbnails that link to a lightbox image gallery for the activity stream
	public function myfs_app_image_shortcode( $atts, $content = '', $tag = '' ) {
	    return gallery_shortcode( array( 'id' => $atts['id'], 'ids' => $atts['id'], 'columns' =>  5, 'link' => 'file' ) );
	}

	// Enable Shortcodes for Side-wide Activity Stream
	public function myfs_add_shortcodes_to_activity_stream() {
		add_filter( 'bp_get_activity_content_body', 'do_shortcode', 1 );
	}

	public function extend_jwt_token_exp() {
		return time() + (DAY_IN_SECONDS * 7 * 26);
	}

	public function add_data_jwt_auth_response($data, $user) {

	    $data['id'] = (int) $user->data->ID;
		$data['name'] = $user->data->display_name;
		$data['nicename'] = $data['user_nicename'];
	    $data['first_name'] = ( xprofile_get_field_data( 13, $user->data->ID  ) ) ?: explode(' ', $data['name'], 2)[0];
	    $data['last_name'] = ( xprofile_get_field_data( 14, $user->data->ID  ) ) ?: explode(' ', $data['name'], 2)[1];
	    $data['avatar'] = get_avatar_url( $user->data->ID );
	    $data['location'] = xprofile_get_field_data( 12, $user->data->ID );
		$data['about'] = strip_tags( xprofile_get_field_data( 2, $user->data->ID ) );

		unset( $data['user_nicename'] );
		unset( $data['user_display_name'] );

	    return $data;
	}

	// this is to add a fake component to BuddyPress. A registered component is needed to add notifications
	public function myfs_app_notifications_get_registered_components( $component_names = array() ) {
		// Force $component_names to be an array
		if ( ! is_array( $component_names ) ) {
			$component_names = array();
		}
		// Add 'myfossil_notification' component to registered components array
		array_push( $component_names, 'myfsapp' );
		// Return component's with 'myfossil_notification' appended
		return $component_names;
	}

	// this gets the saved item id, compiles some data and then displays the notification
	function myfs_app_buddypress_notifications( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
		// New custom notifications

		if ( 'activity_liked' === $action ) {

			$custom_title = 'activity liked';
			$custom_link  = '/members/' . bp_core_get_username( get_current_user_id() ) . '/activity/' . $item_id;
			$custom_text = bp_core_get_user_displayname( $secondary_item_id ) . ' liked your activity';

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
}

/**
 * The main function responsible for returning the one true myfossil Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mfsm = myfossil(); ?>
 *
 * @return The one true myfossil Instance
 */
function myfossil() {
	return MYFOSSIL::instance();
}

/**
 * Hook myfossil early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before MYFOSSIL, to get their
 * actions, filters, and overrides setup without MYFOSSIL being in the way.
 */
/*
NOT SURE WHAT THIS DOES SO I'M REMOVING IT FOR NOW
if ( defined( 'MYFOSSIL_COLLECTION_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'myfossil_rest_api', (int) MYFOSSIL_COLLECTION_LATE_LOAD );
// "And now here's something we hope you'll really like!"
} else {
	myfossil_rest_api();
}
*/

// "And now here's something we hope you'll really like!"
myfossil();

endif; // class_exists check

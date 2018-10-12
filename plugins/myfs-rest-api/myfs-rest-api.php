<?php
/**
 * Plugin Name: myFOSSIL REST API
 * Description: Extends Wordpress REST Controllers to manage requests from the app.
 * Version: 0.1.0
 * Author: makkusu
 * Author URI: https://www.atmosphereapps.com
 *
 * @package myfs_app_rest_api
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/*
// Define constants.
define( 'MYFS_APP_REST_API_PLUGIN_VERSION', '1.0.0' );
define( 'MYFS_APP_REST_API_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

// Requirements.
require_once( ABSPATH . 'wp-content/plugins/rest-api/plugin.php');

// Main instance of plugin.
function myfs_rest_activity_controller() {
    return MYFS_REST_Activity_Controller::get_instance();
}

// Global for backwards compatibility.
$GLOBALS['myfs_rest_activity_controller'] = myfs_rest_activity_controller();
*/
if ( !class_exists( 'MYFS_REST_API' ) ) :
/**
 * Main myfs_app_rest_api Class
 */
final class MYFS_REST_API {
	/** Singleton *************************************************************/

	/**
	 * Main MYFS_APP_REST_API Instance
	 *
	 * Insures that only one instance of MYFS_APP_REST_API exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new MYFS_REST_API;
			$instance->setup();
		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent myfs_app_rest_api from being loaded more than once.
	 *
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent myfs_app_rest_api from being cloned
	 *
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'myfs_app_rest_api' ), '2.1' ); }

	/**
	 * A dummy magic method to prevent myfs_app_rest_api from being unserialized
	 *
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'myfs_app_rest_api' ), '2.1' ); }

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
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-activity-controller.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-user-controller.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-group-controller.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-comment-controller.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-specimen-controller.php';
		require plugin_dir_path( __FILE__ ) . 'includes/class-myfs-rest-notification-controller.php';

		add_action('rest_api_init', array( $this, 'create_rest_routes' ) );
	}

	/** Public Methods *******************************************************/

	/**
	 * Register the routes for all of the controllers in the app
	 */
	public function create_rest_routes() {
		// Activities.
		$controller = new MYFS_REST_Activity_Controller;
		$controller->register_routes();

		$controller = new MYFS_REST_User_Controller;
		$controller->register_routes();

		$controller = new MYFS_REST_Group_Controller;
		$controller->register_routes();

		$controller = new MYFS_REST_Comment_Controller;
		$controller->register_routes();

		$controller = new MYFS_REST_Specimen_Controller;
		$controller->register_routes();

		$controller = new MYFS_REST_Notification_Controller;
		$controller->register_routes();
	}
}

/**
 * The main function responsible for returning the one true myfs_app_rest_api Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mfsm = myfs_app_rest_api(); ?>
 *
 * @return The one true myfs_app_rest_api Instance
 */
function myfs_rest_api() {
	return MYFS_REST_API::instance();
}

/**
 * Hook myfs_app_rest_api early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before MYFS_APP_REST_API, to get their
 * actions, filters, and overrides setup without MYFS_APP_REST_API being in the way.
 */
/*
NOT SURE WHAT THIS DOES SO I'M REMOVING IT FOR NOW
if ( defined( 'MYFOSSIL_COLLECTION_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'myfs_app_rest_api', (int) MYFOSSIL_COLLECTION_LATE_LOAD );
// "And now here's something we hope you'll really like!"
} else {
	myfs_app_rest_api();
}
*/

// "And now here's something we hope you'll really like!"
myfs_rest_api();

endif; // class_exists check
<?php
/**
 * ./myfossil-specimen.php
 *
 * @author  Brandon Wood <bwood@atmoapps.com>
 * @package myFOSSIL
 */

namespace myFOSSIL\Plugin\Specimen;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the
 * plugin Dashboard. This file also includes all of the dependencies used by
 * the plugin, registers the activation and deactivation functions, and defines
 * a function that starts the plugin.
 *
 * @link              https://github.com/myfossil/myfossil-specimen
 * @package           myFOSSIL_Specimen
 *
 * @wordpress-plugin
 * Plugin Name:       myFOSSIL Specimen
 * Plugin URI:        https://github.com/myfossil/myfossil-specimen
 * Description:       Adds fossil management to WordPress + BuddyPress.
 * Version:           0.4.0
 * Author:            myFOSSIL
 * Author URI:        https://github.com/myfossil
 * License:           BSD
 * License URI:       http://opensource.org/licenses/bsd-license.html
 * Text Domain:       myfossil-specimen
 * Domain Path:       /languages
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Dependencies.
 */
require_once plugin_dir_path( realpath( __FILE__ ) ) . 'vendor/autoload.php';

/**
 * The code that defines the custom objects to be used.
 */
require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/models.php';

/**
 * The code that runs during plugin activation.
 */
require_once( plugin_dir_path( realpath( __FILE__ ) ) .
    'includes/class-myfossil-specimen-activator.php' );

/**
 * The code that runs during plugin deactivation.
 */
require_once( plugin_dir_path( realpath( __FILE__ ) ) .
    'includes/class-myfossil-specimen-deactivator.php' );

/**
 * This action is documented in:
 *  - includes/class-myfossil-specimen-activator.php
 */
register_activation_hook( realpath( __FILE__ ), array( __NAMESPACE__ .
        '\myFOSSIL_Specimen_Activator', 'activate' ) );

/**
 * This action is documented in:
 *  - includes/class-myfossil-specimen-deactivator.php
 */
register_deactivation_hook( realpath( __FILE__ ), array( __NAMESPACE__ .
        '\myFOSSIL_Specimen_Deactivator', 'deactivate' ) );

/**
 * The core plugin class that is used to define internationalization,
 * dashboard-specific hooks, and public-facing site hooks.
 */
require_once( plugin_dir_path( realpath( __FILE__ ) ) .
    'includes/class-myfossil-specimen.php' );


function myfossil_enqueue_scripts()
{
    wp_enqueue_script( 'myfossil-specimen-public-single', plugin_dir_url( realpath( __FILE__ ) ) .
        'static/js/public-single.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'myfossil-specimen-public-list', plugin_dir_url( realpath( __FILE__ ) ) .
        'static/js/public-list.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'myfossil-specimen-public-plugins', plugin_dir_url( realpath( __FILE__ ) ) .
        'static/js/public-plugins.min.js', array( 'jquery' ) );
}
add_action( 'wp_enqueue_scripts', __namespace__ . '\myfossil_enqueue_scripts' );

function myfossil_admin_enqueue_scripts()
{
    wp_enqueue_script( 'myfossil_specimen_admin', plugin_dir_url( realpath( __FILE__ ) ) .
        'static/js/admin.min.js', array( 'jquery' ) );
}
add_action( 'admin_enqueue_scripts', __namespace__ .
    '\myfossil_admin_enqueue_scripts' );

/*
function myfossil_bp_blogs_record_post_post_types( $post_types ) {
    if ( ! in_array( Fossil::POST_TYPE, $post_types ) )
        array_push( $post_types, Fossil::POST_TYPE );
    return $post_types;
}
add_filter( 'bp_blogs_record_post_post_types', __namespace__ .
        '\myfossil_bp_blogs_record_post_post_types', 10, 1 );
*/

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks, then kicking off
 * the plugin from this point in the file does not affect the page life cycle.
 *
 * @since    0.0.1
 */
function run_myfossil_specimen()
{

    $plugin = new myFOSSIL_Specimen();
    $plugin->run();

}
run_myfossil_specimen();

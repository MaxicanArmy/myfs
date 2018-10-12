<?php
/**
 * ./includes/class-myfossil-specimen.php
 *
 * A class definition that includes attributes and functions used across both
 * the public-facing side of the site and the dashboard.
 *
 * @subpackage  myFOSSIL/includes
 *
 * @link        https://github.com/myfossil
 * @since       0.0.1
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 */


namespace myFOSSIL\Plugin\Specimen;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, dashboard-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class myFOSSIL_Specimen
{

    /**
     * The loader that's responsible for maintaining and registering all hooks
     * that power the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      myFOSSIL_Specimen_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout
     * the plugin.  Load the dependencies, define the locale, and set the hooks
     * for the Dashboard and the public-facing side of the site.
     *
     * @since    0.0.1
     */
    public function __construct()
    {

        $this->plugin_name = 'myfossil-specimen';
        $this->version = '0.0.1';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();

    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - myFOSSIL_Specimen_Loader. Orchestrates the hooks of the plugin.
     * - myFOSSIL_Specimen_i18n. Defines internationalization functionality.
     * - myFOSSIL_Specimen_Admin. Defines all hooks for the dashboard.
     * - myFOSSIL_Specimen_Public. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the
     * hooks with WordPress.
     *
     * @since    0.0.1
     * @access   private
     */
    private function load_dependencies()
    {

        /**
         * Load HTTP request handling libraries
         *
         * @see composer.json
         */
        require_once plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'vendor/autoload.php';

        /**
         * The class responsible for orchestrating the actions and filters of
         * the core plugin.
         */
        require_once plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'includes/class-myfossil-specimen-loader.php';

        /**
         * The class responsible for defining internationalization
         * functionality of the plugin.
         */
        require_once plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'includes/class-myfossil-specimen-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the
         * Dashboard.
         */
        require_once plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'admin/class-myfossil-specimen-admin.php';

        /**
         * The class responsible for defining all actions that occur in the
         * public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'public/class-myfossil-specimen-public.php';

        $this->loader = new myFOSSIL_Specimen_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the myFOSSIL_Specimen_i18n class in order to set the domain and to
     * register the hook with WordPress.
     *
     * @since    0.0.1
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new myFOSSIL_Specimen_i18n();
        $plugin_i18n->set_domain( $this->get_plugin_name() );

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n,
            'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the dashboard functionality of the
     * plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new myFOSSIL_Specimen_Admin( $this->get_plugin_name(),
            $this->get_version() );

        /* Administration UI fixes */
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu' );

        /* WordPress custom post types */
        $this->loader->add_action( 'init',
            $plugin_admin, 'register_custom_post_types' );

        /* WordPress custom taxonomies */
        $this->loader->add_action( 'init',
            $plugin_admin, 'register_taxonomies' );
        $this->loader->add_action( 'wp_ajax_myfossil_load_terms',
            $plugin_admin, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_load_geochronology',
            $plugin_admin, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_load_default_fossils',
            $plugin_admin, 'ajax_handler' );

        /* BuddyPress setup */
        $this->loader->add_filter( 'bp_blogs_record_comment_post_types',
            $plugin_admin, 'add_buddypress_comments' );

    }

    /**
     * Register all of the hooks related to the public-facing functionality of
     * the plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new myFOSSIL_Specimen_Public(
            $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'init', $plugin_public,
            'add_rewrite_tags' );

        $this->loader->add_action( 'init', $plugin_public,
            'fix_fossil_rewrites' );

        /* Should not be able to update taxon of a specimen without logging in */
        $this->loader->add_action( 'wp_ajax_myfossil_save_taxon',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_save_dimensions',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_save_location',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_save_geochronology',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_save_lithostratigraphy',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_create_fossil',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_upload_fossil_image',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_delete_fossil_image',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_feature_fossil_image',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_save_status',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_fossil_comment',
            $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_fossil_delete',
            $plugin_public, 'ajax_handler' );

        $this->loader->add_action( 'bp_register_activity_actions',
            $plugin_public, 'bp_register_activity_actions' );
        $this->loader->add_action( 'bp_setup_nav', $plugin_public,
            'bp_add_member_fossil_nav_items', 100 );

        /* BuddyPress Activity JSON filtering */
        $this->loader->add_filter( 'bp_get_activity_content_body',
            $plugin_public, 'bp_get_activity_content_body' );

    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.0.1
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context
     * of WordPress and to define internationalization functionality.
     *
     * @since     0.0.1
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.0.1
     * @return    myFOSSIL_Specimen_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.0.1
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}


/**
 * Returns SQL partial that sets default character set and collation.
 *
 * @todo    Add WordPress hook(s)
 * @since   0.0.1
 * @access  public
 * @static
 * @see     {@link http://codex.wordpress.org/Creating_Tables_with_Plugins}
 * @return unknown
 */
function charset_collate()
{
    $charset_collate = '';
    if ( !empty( $wpdb->charset ) )
        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
    if ( !empty( $wpdb->collate ) )
        $charset_collate .= " COLLATE {$wpdb->collate}";
    return $charset_collate;
}

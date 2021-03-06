<?php
namespace myFOSSIL\Plugin\Resources;

define( 'ACF_LITE', true );
require_once 'acf/acf.php';

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both
 * the public-facing side of the site and the dashboard.
 *
 * @link       http://github.com/myfossil
 * @since      0.0.1
 *
 * @package    myFOSSIL_Resources
 * @subpackage myFOSSIL_Resources/includes
 */

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
 * @package    myFOSSIL_Resources
 * @subpackage myFOSSIL_Resources/includes
 */
class myFOSSIL_Resources
{

    /**
     * The loader that's responsible for maintaining and registering all hooks
     * that power the plugin.
     *
     * @since    0.0.1
     * @access   protected
     * @var      myFOSSIL_Resources_Loader    $loader    Maintains and registers all hooks for the plugin.
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

        $this->plugin_name = 'myfossil-resources';
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
     * - myFOSSIL_Resources_Loader. Orchestrates the hooks of the plugin.
     * - myFOSSIL_Resources_i18n. Defines internationalization functionality.
     * - myFOSSIL_Resources_Admin. Defines all hooks for the dashboard.
     * - myFOSSIL_Resources_Public. Defines all hooks for the public side of the site.
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
         * The class responsible for orchestrating the actions and filters of
         * the core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'includes/class-myfossil-resources-loader.php';

        /**
         * The class responsible for defining internationalization
         * functionality of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'includes/class-myfossil-resources-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the
         * Dashboard.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'admin/class-myfossil-resources-admin.php';

        /**
         * The class responsible for defining all actions that occur in the
         * public-facing side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) .
            'public/class-myfossil-resources-public.php';

        $this->loader = new myFOSSIL_Resources_Loader();

    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the myFOSSIL_Resources_i18n class in order to set the domain and to
     * register the hook with WordPress.
     *
     * @since    0.0.1
     * @access   private
     */
    private function set_locale()
    {

        $plugin_i18n = new myFOSSIL_Resources_i18n();
        $plugin_i18n->set_domain( $this->get_plugin_name() );

        $this->loader->add_action( 'plugins_loaded', $plugin_i18n,
                'load_plugin_textdomain' );

    }

    /**
     * Register all of the hooks related to the dashboard functionality
     * of the plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function define_admin_hooks()
    {

        $plugin_admin = new myFOSSIL_Resources_Admin( $this->get_plugin_name(),
                $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,
                'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin,
                'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'register_menus' );

        /* Custom Post Types */
        $this->loader->add_action( 'init', $plugin_admin, 'create_events' );

        /* AJAX */
        $this->loader->add_action( 'wp_ajax_myfossil_resources_load_data', $plugin_admin,
                'ajax_handler' );

        /* Advanced Custom Fields */
		$this->loader->add_filter( 'acf/settings/path', $plugin_admin, 'acf_settings_path' );
		$this->loader->add_filter( 'acf/settings/dir', $plugin_admin, 'acf_settings_dir' );
        add_filter('acf/settings/show_admin', '__return_false');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    0.0.1
     * @access   private
     */
    private function define_public_hooks()
    {

        $plugin_public = new myFOSSIL_Resources_Public(
                $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public,
                'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public,
                'enqueue_scripts' );

        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_states', $plugin_public,
                'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_types', $plugin_public,
                'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_places', $plugin_public,
                'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_events', $plugin_public,
                'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_filter_start_date',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_filter_end_date',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_events_states',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_events_types',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_list_events_month_years',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_myfossil_resources_create_place',
                $plugin_public, 'ajax_handler' );

        // Public
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_list_states',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_list_types',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_list_places',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_start_date', $plugin_public,
                'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_filter_start_date',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_filter_end_date',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_list_events_states',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action( 'wp_ajax_nopriv_myfossil_resources_list_events_types',
                $plugin_public, 'ajax_handler' );
        $this->loader->add_action(
                'wp_ajax_nopriv_myfossil_resources_list_events_month_years', $plugin_public,
                'ajax_handler' );

        // BuddyPress Groups fields
        $this->loader->add_action( 'groups_custom_group_fields_editable',
                $plugin_public, 'group_header_fields_markup' );

        $this->loader->add_action( 'groups_group_details_edited',
                $plugin_public, 'group_header_fields_save' );

        $this->loader->add_action( 'groups_created_group', $plugin_public,
                'group_header_fields_save' );

        $this->loader->add_action( 'bp_group_header_meta', $plugin_public,
                'output_header' );
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
     * @return    myFOSSIL_Resources_Loader    Orchestrates the hooks of the plugin.
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

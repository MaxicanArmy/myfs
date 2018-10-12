<?php
/**
 * ./admin/class-myfossil-specimen-admin.php
 *
 * The dashboard-specific functionality of the plugin.
 *
 * @author      Brandon Wood <btwood@atmoapps.com>
 * @package     myFOSSIL
 * @subpackage  myFOSSIL/admin
 *
 * @link       https://github.com/myfossil
 * @since      0.0.1
 */

namespace myFOSSIL\Plugin\Specimen;

/**
 * Template partials functions.
 */
require_once 'partials/myfossil-specimen-admin-display.php';

/**
 * The dashboard-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue
 * the dashboard-specific stylesheet and JavaScript.
 *
 * @package    myFOSSIL
 * @subpackage myFOSSIL/admin
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class myFOSSIL_Specimen_Admin
{

    /**
     * The ID of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $name    The ID of this plugin.
     */
    private $name;

    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @param string  $name    The name of this plugin.
     * @param string  $version The version of this plugin.
     */
    public function __construct( $name, $version )
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Returns the post_type strings of all paleontoligcal objects.
     *
     * @return  array   Array of WordPress post_types.
     */
    public static function post_types()
    {
        return array(
            Fossil::POST_TYPE,
            FossilDimension::POST_TYPE,
            FossilLocation::POST_TYPE,
            Reference::POST_TYPE,
            Stratum::POST_TYPE,
            Taxon::POST_TYPE,
            FossilTaxa::POST_TYPE,
            TimeInterval::POST_TYPE
        );
    }


    // {{{ WordPress: Setup and default data
    /**
     * Callback to register custom post types with WordPress.
     *
     * @access  public
     */
    public function register_custom_post_types()
    {
        Fossil::register_cpt();
        FossilDimension::register_cpt();
        FossilLocation::register_cpt();
        Reference::register_cpt();
        Stratum::register_cpt();
        Taxon::register_cpt();
        FossilTaxa::register_cpt();
        TimeInterval::register_cpt();
    }

    /**
     * Callback to register custom taxonomies with WordPress.
     *
     * @access  public
     */
    public function register_taxonomies()
    {
        $this->_ctax_taxa();
        $this->_ctax_geochronology();
        $this->_ctax_lithostratigraphy();
    }
    // {{{ WordPress taxonomy definition: Biological Taxonomy
    private function _ctax_taxa()
    {
        $labels = array(
            'name'                => __( 'Taxonomic Ranks', 'myfossil-specimen' ),
            'singular_name'       => __( 'Taxonomic Rank', 'myfossil-specimen' ),
            'menu_name'           => __( 'Taxonomic Ranks', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Taxonomic Rank:', 'myfossil-specimen' ),
            'all_items'           => __( 'Taxonomic Ranks', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Taxonomic Rank', 'myfossil-specimen' ),
            'view_item'           => __( 'View Taxonomic Rank', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Taxonomic Rank', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Taxonomic Rank', 'myfossil-specimen' ),
            'new_item_name'       => __( 'New Taxonomic Rank', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Taxonomic Ranks', 'myfossil-specimen' ),
            'not_found'           => __( 'Taxonomic Rank not found', 'myfossil-specimen' ),
        );

        $args = array(
            'labels'              => $labels,
            'rewrite'             => array(
                'slug' => 'taxon',
                'hierarchical' => false
            ),
            'labels'              => $labels,
            'hierarchical'        => true,
            'public'              => true,
            'query_var'           => true,
        );

        register_taxonomy( 'myfossil_taxa', array( 'myfossil_taxon' ), $args );
        register_taxonomy_for_object_type( 'myfossil_taxa', 'myfossil_taxon' );
    }
    // }}}
    // {{{ WordPress taxonomy definition: Geochronology
    private function _ctax_geochronology()
    {
        $labels = array(
            'name'                => __( 'Geochronologies', 'myfossil-specimen' ),
            'singular_name'       => __( 'Geochronology', 'myfossil-specimen' ),
            'menu_name'           => __( 'Geochronologies', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Geochronology:', 'myfossil-specimen' ),
            'all_items'           => __( 'Geochronologies', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Geochronology', 'myfossil-specimen' ),
            'view_item'           => __( 'View Geochronology', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Geochronology', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Geochronology', 'myfossil-specimen' ),
            'new_item_name'       => __( 'New Geochronology', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Geochronologies', 'myfossil-specimen' ),
            'not_found'           => __( 'Geochronology not found', 'myfossil-specimen' ),
        );

        $args = array(
            'labels'              => $labels,
            'rewrite'             => array(
                'slug' => 'geochronology',
                'hierarchical' => false
            ),
            'labels'              => $labels,
            'hierarchical'        => true,
            'public'              => true,
            'query_var'           => true,
        );

        register_taxonomy( 'myfossil_geochronologies', array( 'myfossil_time_interval' ), $args );
        register_taxonomy_for_object_type( 'myfossil_geochronologies', 'myfossil_time_interval' );
    }
    // }}}
    // {{{ WordPress taxonomy definition: Lithostratigraphy
    private function _ctax_lithostratigraphy()
    {
        $labels = array(
            'name'                => __( 'Lithostratigraphies', 'myfossil-specimen' ),
            'singular_name'       => __( 'Lithostratigraphy', 'myfossil-specimen' ),
            'menu_name'           => __( 'Lithostratigraphies', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Lithostratigraphy:', 'myfossil-specimen' ),
            'all_items'           => __( 'Lithostratigraphies', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Lithostratigraphy', 'myfossil-specimen' ),
            'view_item'           => __( 'View Lithostratigraphy', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Lithostratigraphy', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Lithostratigraphy', 'myfossil-specimen' ),
            'new_item_name'       => __( 'New Lithostratigraphy', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Lithostratigraphies', 'myfossil-specimen' ),
            'not_found'           => __( 'Lithostratigraphy not found', 'myfossil-specimen' ),
        );

        $args = array(
            'labels'              => $labels,
            'rewrite'             => array(
                'slug' => 'lithostratigraphy',
                'hierarchical' => false
            ),
            'labels'              => $labels,
            'hierarchical'        => true,
            'public'              => true,
            'query_var'           => true,
        );

        register_taxonomy( 'myfossil_lithostratigraphies', array( 'myfossil_stratum' ), $args );
        register_taxonomy_for_object_type( 'myfossil_lithostratigraphies', 'myfossil_stratum' );
    }
    // }}}


    private function _load_taxonomy_terms()
    {
        $this->_load_ctax_taxa();
        $this->_load_ctax_geochronologies();
        $this->_load_ctax_lithostratigraphies();
    }
    // {{{ WordPress taxonomy data loader: Biological Taxonomy
    private function _load_taxonomy( $taxonomy, $terms )
    {
        $parent_id = 0;
        foreach ( $terms as $term ) {
            $args = array(
                'parent' => $parent_id,
                'slug' => $term,
            );
            $_ = wp_insert_term( ucfirst( $term ), $taxonomy, $args );
            $parent_id = $_['term_id'];
        }
    }

    // {{{ Load Taxa
    private function _load_ctax_taxa()
    {
        $taxonomy = 'myfossil_taxa';
        $terms = array( 'domain', 'kingdom', 'phylum', 'class', 'order',
            'family', 'genus', 'species' );
        $this->_load_taxonomy( $taxonomy, $terms );
    }
    // }}}

    // {{{ Load Geochronologies
    private function _load_ctax_geochronologies()
    {
        $taxonomy = 'myfossil_geochronologies';
        $terms = array( 'eon', 'era', 'period', 'epoch', 'age', 'chron' );
        $this->_load_taxonomy( $taxonomy, $terms );
    }
    // }}}

    // {{{ Load Lithostratigraphies
    private function _load_ctax_lithostratigraphies()
    {
        $taxonomy = 'myfossil_lithostratigraphies';
        $terms = array( 'supergroup', 'group', 'formation', 'member', 'bed' );
        $this->_load_taxonomy( $taxonomy, $terms );
    }
    // }}}

    // }}}
    // {{{ WordPress taxonomy data loader: Geochronology
    private function _load_time_intervals()
    {
        /*
         * Map data from PBDB to meaningful intervals.
         *
         * @link http://paleobiodb.org/data1.1/scales/single.txt?id=1
         */
        $levels = array(
            1 => 'eon',
            2 => 'era',
            3 => 'period',
            4 => 'epoch',
            5 => 'age'
        );
        $geochronology = array();
        foreach ( $levels as $idx => $geoc )
            $geochronology[$idx] = term_exists( ucfirst( $geoc ),
                'myfossil_geochronologies' );

        /**
         *
         *
         * @todo create references posts.
         */

        /* Map columns from CSV */
        $fields = array(
            'level' => 'int',
            'interval_name' => 'str',
            'color' => 'str',
            'late_age' => 'float',
            'early_age' => 'float',
            'reference_no' => 'int'
        );

        // Set default filename
        $filename = plugin_dir_path( dirname( realpath( __FILE__ ) ) ) .
            'admin/data/intervals.csv';

        // Exit if the file doesn't exist.
        if ( !file_exists( $filename ) )
            return -2;

        // Load the entire CSV
        $csv = array_map( 'str_getcsv', file( $filename ) );

        // Load data as posts
        foreach ( $csv as $raw_data ) {
            // skip header
            if ( $raw_data[0] == 'level' ) continue;

            // Parse data from CSV reader
            $data = array();
            $idx = 0;
            foreach ( $fields as $field_name => $field_type ) {
                switch ( $field_type ) {
                case 'int':
                    $data[$field_name] = (int) $raw_data[$idx];
                    break;
                case 'float':
                    $data[$field_name] = (float) $raw_data[$idx];
                    break;
                case 'str':
                    $data[$field_name] = (string) $raw_data[$idx];
                    break;
                }
                $idx++;
            }

            $post_args = array(
                'post_title'    => $data['interval_name'],
                'post_status'   => 'publish',
                'post_type'     => 'myfossil_time_interval',
            );

            // Insert the post into the database
            $post_id = wp_insert_post( $post_args );

            // Check that it actually went in
            if ( !$post_id )
                return -1;

            // Set Geochronology
            wp_set_post_terms( $post_id, $geochronology[$data['level']],
                'myfossil_geochronologies' );

            // Load in ACF data for the Place
            $acf_fields = array( 'color', 'late_age', 'early_age' );
            foreach ( $acf_fields as $field_name )
                update_field( $field_name, $data[$field_name], $post_id );
        }

        return 1;
    }
    // }}}
    // }}}

    // {{{ WordPress: Admininstration panel UI
    /**
     * Callback to make the admin panel cleaner with respect to this plugin.
     *
     * @since   0.0.1
     */
    public function admin_menu()
    {
        $this->_cleanup_metaboxes();
        $this->_add_menu_separator();
        $this->_add_tools_page();
    }
    // {{{ Adminitrative menu cleanup: Remove custom field metaboxes
    /**
     * Removes custom field metaboxes from post types.
     */
    private function _cleanup_metaboxes()
    {
        foreach ( self::post_types() as $cpt ) {
            remove_meta_box( 'postcustom', $cpt, 'normal' );
        }
    }
    // }}}
    // {{{ Adminitrative menu cleanup: Add menu separators
    /**
     * Callback to add administration panel menu items.
     *
     * Adds menu separators to differentiate post types in the admin menu that
     * are part of this plugin.
     *
     * @access  private
     */
    private function _add_menu_separator()
    {
        global $menu;

        $position = 0;
        $sep_idx = 0;
        foreach ( $menu as $offset => $section ) {
            if ( substr( $section[2], 0, 9 ) == 'separator' )
                $sep_idx++;
        }

        foreach ( $menu as $offset => $section ) {
            $section_name = $section[0];
            if ( $section_name == 'Taxa' ) {
                // Backup until we don't hit a menu item
                $idx = $offset - 1;
                while ( array_key_exists( $idx, $menu ) && $idx > 0 )
                    $idx--;

                // Exit if we have no room for a separator somehow...
                if ( $idx == 0 ) break;

                // Create the separator
                $menu[$idx + 1] = array(
                    '',
                    'read',
                    "separator{$sep_idx}",
                    '',
                    'wp-menu-separator'
                );

                break;
            }
        }

        ksort( $menu );
    }
    // }}}
    // {{{ Adminitrative menu cleanup: Add Tools page
    /**
     * Callback to add a page for this plugin in the admin Tools menu.
     *
     * @access  private
     */
    private function _add_tools_page()
    {
        add_management_page( 'myFOSSIL Specimen', 'myFOSSIL Specimen',
            'manage_options', 'myfossil-specimen',
            'myFOSSIL\Plugin\Specimen\admin_tools_page' );
    }
    // }}}
    // }}}

    // {{{ AJAX
    /**
     * AJAX callback for wp_ajax_ hooks in the administration panel.
     *
     * @todo refactor to return more information than 0|1
     * @todo refactor to use more secure noncing
     *
     * @since   0.0.2
     */
    public function ajax_handler()
    {
        header( 'Content-Type: application/json' );

        // Check nonce
        if ( !check_ajax_referer( 'myfossil_nonce', 'nonce', false ) ) {
            $return_args = array(
                "result" => "Error",
                "message" => "403 Forbidden",
            );

            echo json_encode( $return_args );
            die;
        }

        switch ( $_POST['action'] ) {
        case 'myfossil_load_terms':
            $this->_load_taxonomy_terms();
            echo "1";
            die;
            break;

        case 'myfossil_load_geochronology':
            if ( $this->_load_time_intervals() > 0 )
                echo "1";
            else
                echo "0";
            die;
            break;
        }
    }
    // }}}

    // {{{ BuddyPress
    /**
     * Callback for enabling BuddyPress comments on custom post types.
     *
     * @access  public
     * @since   0.1.0
     * @param array   $bp_post_types Current list of post types that support BuddyPress comments.
     * @return  array   Array of WordPress post_type strings of all supported paleontological objects.
     */
    public function add_buddypress_comments( $bp_post_types )
    {
        foreach ( self::post_types() as $pt ) {
            array_push( $bp_post_types, $pt );
        }
        return $bp_post_types;
    }
    // }}}
}

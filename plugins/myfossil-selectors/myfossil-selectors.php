<?php
/*
Plugin Name: myFOSSIL Selectors
Plugin URI:  http://atmosphereapps.com
Description: The admin panel allows admins to set which forum, fossil, and slider should be featured on the front page to logged out users.
Version:     .1
Author:      Atmosphere Apps
Author URI:  http://atmosphereapps.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: myfossil-selectors
*/

class AtmoSelectors
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'myfossil_selector_page' ) );
        add_action( 'admin_init', array( $this, 'atmo_page_init' ) );
    }

    /**
     * Add options page
     */
    public function myfossil_selector_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'myFOSSIL Selectors Admin', 
            'myFOSSIL Selectors', 
            'manage_options', 
            'myfossil-selector-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'atmo_selectors' );
        ?>
        <div class="wrap">
            <h1>myFOSSIL Selectors</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'myfossil_selector_group' );
                do_settings_sections( 'myfossil-selector-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function atmo_page_init()
    {        
        register_setting(
            'myfossil_selector_group', // Option group
            'atmo_selectors', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
/*
        register_setting(
            'myfossil_selector_group', // Option group
            'atmo_fossil_selector', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        register_setting(
            'myfossil_selector_group', // Option group
            'atmo_slider_selector', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );
*/
        add_settings_section(
            'forum_section_id', // ID
            'Set Forum Number', // Title
            array( $this, 'print_section_info' ), // Callback
            'myfossil-selector-admin' // Page
        );

        add_settings_section(
            'fossil_section_id', // ID
            'Set Fossil Number', // Title
            array( $this, 'print_section_info' ), // Callback
            'myfossil-selector-admin' // Page
        );

        add_settings_section(
            'slider_section_id', // ID
            'Set Slider Number', // Title
            array( $this, 'print_section_info' ), // Callback
            'myfossil-selector-admin' // Page
        );

        add_settings_field(
            'forum_id', // ID
            'Forum ID', // Title 
            array( $this, 'forum_id_callback' ), // Callback
            'myfossil-selector-admin', // Page
            'forum_section_id' // Section           
        );

        add_settings_field(
            'fossil_id', // ID
            'Fossil ID', // Title 
            array( $this, 'fossil_id_callback' ), // Callback
            'myfossil-selector-admin', // Page
            'fossil_section_id' // Section           
        );

        add_settings_field(
            'slider_id', // ID
            'Slider ID', // Title 
            array( $this, 'slider_id_callback' ), // Callback
            'myfossil-selector-admin', // Page
            'slider_section_id' // Section           
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['forum_id'] ) )
            $new_input['forum_id'] = absint( $input['forum_id'] );

        if( isset( $input['fossil_id'] ) )
            $new_input['fossil_id'] = absint( $input['fossil_id'] );

        if( isset( $input['slider_id'] ) )
            $new_input['slider_id'] = absint( $input['slider_id'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function forum_id_callback()
    {
        printf(
            '<input type="text" id="forum_id" name="atmo_selectors[forum_id]" value="%s" />',
            isset( $this->options['forum_id'] ) ? esc_attr( $this->options['forum_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function fossil_id_callback()
    {
        printf(
            '<input type="text" id="fossil_id" name="atmo_selectors[fossil_id]" value="%s" />',
            isset( $this->options['fossil_id'] ) ? esc_attr( $this->options['fossil_id']) : ''
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function slider_id_callback()
    {
        printf(
            '<input type="text" id="slider_id" name="atmo_selectors[slider_id]" value="%s" />',
            isset( $this->options['slider_id'] ) ? esc_attr( $this->options['slider_id']) : ''
        );
    }
}

if( is_admin() )
    $my_settings_page = new AtmoSelectors();
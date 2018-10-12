<?php
namespace myFOSSIL\Plugin\Resources;

require_once 'partials/places.php';
require_once 'partials/events.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://github.com/myfossil
 * @since      0.0.1
 *
 * @package    myFOSSIL_Resources
 * @subpackage myFOSSIL_Resources/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to enqueue
 * the dashboard-specific stylesheet and JavaScript.
 *
 * @package    myFOSSIL_Resources
 * @subpackage myFOSSIL_Resources/public
 */
class myFOSSIL_Resources_Public
{

    /**
     * The ID of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Custom fields list, used throughout the plugin to define the fields. 
     *
	 * @since    0.0.1
	 * @access   public
	 * @var      string    $version    The current version of this plugin.
     */
    public $custom_fields; 

    public $group_types = array(
            'organization'     => "Organization",
            'group'            => "Interest Group",
            'society'          => "Society",
            'fossil-club'      => "Fossil Club",
            'city-park'        => "City Park",
            'state-park'       => "State Park",
            'national-park'    => "National Park",
            'museum'           => "Museum",
            'collecting-site'  => "Collecting Site",
            'app-group'        => "App Group",
            'other'            => "Other"

        );

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @var      string    $plugin_name       The name of the plugin.
     * @var      string    $version    The version of this plugin.
     * @param unknown $plugin_name
     * @param unknown $version
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->custom_fields = array(
                self::field_factory( 'type', 'Type', true ),
                self::field_factory( 'street_address', 'Street Address' ),
                self::field_factory( 'city', 'City', true ),
                self::field_factory( 'state', 'State', true ),
                self::field_factory( 'zip', 'Zip Code' ),
                self::field_factory( 'latitude', 'Latitude' ),
                self::field_factory( 'longitude', 'Longitude' ),
                self::field_factory( 'contact', 'Contact Information' ),
                self::field_factory( 'phone', 'Phone Number' ),
                self::field_factory( 'email', 'E-mail Address' ),
                self::field_factory( 'url', 'Website URL' ),
                self::field_factory( 'facebook', 'Facebook' ),
                self::field_factory( 'twitter', 'Twitter' ),
                self::field_factory( 'blog_url', 'Blog URL' )
            );

    }

    public static function rksort( &$array )
    {
        foreach ( $array as &$value )
            if ( is_array( $value ) )
                self::rksort( $value );
            return ksort( $array );
    }

    public static function month_name( $month )
    {
        $months = array( 'January', 'February', 'March', 'April', 'May',
            'June', 'July', 'August', 'September', 'October', 'November',
            'December' );
        return $months[( $month - 1 )];
    }

    /**
     * AJAX call handlers
     */
    public function ajax_handler()
    {
        header( 'Content-Type: application/json' );

        // Check nonce
        if ( ! check_ajax_referer( 'myfossil_resources_filter', 'nonce', false ) ) {
            $return_args = array(
                "result" => "Error",
                "message" => "403 Forbidden",
            );

            echo json_encode( $return_args );
            die;
        }


        /*
         * Grab BuddyPress Groups, which represent Places now.
         */
        $places = array();
        $groups = \BP_Groups_Group::get(
            array(
                'populate_extras' => true,
                'type' => 'alphabetical',
                'per_page' => -1
            )
        );

        foreach ( $groups['groups'] as $place ) {
            if ( ! bp_group_is_visible( $place ) ) {
                continue;
            }
            $p = $place;
            foreach ( groups_get_groupmeta( $place->id, '' ) as $k => $v ) {
                if ( is_array( $v ) && count( $v ) == 1 ) {
                    $v = array_pop( $v );
                }
                $p->{$k} = $v;
            }
            if ( ! property_exists( $p, 'type' ) ) {
                $p->type = 'other';
            }
            $places[] = $p;
        }

        $event_posts = get_posts(
                array(
                    'post_type' => 'event',
                    'posts_per_page' => -1
                )
            );

        $events = array();
        foreach ( $event_posts as $ep ) {
            $event = $ep;
            if ( ! array_key_exists( 'type', $event ) ) {
                $event->type = 'other';
            } else {
                $event->type = strtolower( $event->type );
            }
            $events[] = $event;
        }

        switch ( $_POST['action'] ) {
            case 'myfossil_resources_list_places':
                echo json_encode( $places );

                die;
                break;


            case 'myfossil_resources_list_states':
                $states = array();
                foreach ( $places as $pl ) {
                    if ( property_exists( $pl, 'state' ) ) {
                        $states[] = $pl->state;
                    }
                }
                asort( $states );
                echo json_encode( array_values( array_unique( $states ) ) );

                die;
                break;


            case 'myfossil_resources_list_types':
                $types = array();
                foreach ( $places as $pl ) {
                    if ( property_exists( $pl, 'type' ) ) {
                        $types[] = strtolower( $pl->type );
                    }
                }
                asort( $types );
                echo json_encode( array_values( array_unique( $types ) ) );

                die;
                break;

            case 'myfossil_resources_list_events':
                $ev_array = array();
                foreach ( $events as $ev ) {
                    $fields = parse_meta( get_post_meta( $ev->ID ) );

                    // Skip if the event does not have a start date/time defined
                    if ( ! array_key_exists( 'starts_at', $fields ) )
                        continue;

                    $fields['title'] = $ev->post_title;
                    $fields['content'] = $ev->post_content;

                    // Get datetime
                    $dt = date_parse( $fields['starts_at'] );
                    $fields['month_year'] = sprintf( "%04d-%02d", $dt['year'], $dt['month'] );

                    if ( array_key_exists( 'type', $fields ) ) {
                        $fields['type'] = strtolower( $fields['type'] );
                    } else {
                        $fields['type'] = 'other';
                    }

                    array_push( $ev_array, $fields );
                }

                echo json_encode( array( 'events' => $ev_array ) );

                die;
                break;

            case 'myfossil_resources_filter_start_date':
                $start_date = $_POST['start_date'];
                $start_date = date_create_from_format( "m/d/Y", $start_date );
                $end_date = date_create_from_format( "m/d/Y", $_POST['end_date'] );
                $ev_array = array();

                foreach ( $events as $ev ) {
                    $fields = parse_meta( get_post_meta( $ev->ID ) );
                    $fields[ 'title' ] = $ev->post_title;
                    $fields[ 'content' ] = $ev->post_content;
                    $ev_start_date = date_create_from_format( "mdY", $fields['starts_at'] );
                    $ev_end_date = date_create_from_format( "mdY", $fields['ends_at'] );
                    if ( empty( $end_date ) ) {
                        if ( $start_date <= $ev_start_date ) {
                            array_push( $ev_array, $fields );
                        }
                    } else { //means we have an end_date provider
                        if ( $start_date <= $ev_start_date && $end_date >= $ev_end_date ) {
                            array_push( $ev_array, $fields );
                        }
                    }
                }

                echo json_encode( array( 'events' => $ev_array ) );

                die;
                break;

            case 'myfossil_resources_filter_end_date':
                $end_date = $_POST['end_date'];
                $end_date = date_create_from_format( "m/d/Y", $end_date );
                $start_date = new \DateTime( 'now' );
                if ( !empty( $_POST['start_date'] ) ) {
                    $start_date = date_create_from_format( "m/d/Y", $_POST['start_date'] );
                }
                $ev_array = array();

                foreach ( $events as $ev ) {
                    $fields = parse_meta( get_post_meta( $ev->ID ) );
                    $fields[ 'title' ] = $ev->post_title;
                    $fields[ 'content' ] = $ev->post_content;
                    $ev_end_date = date_create_from_format( "mdY", $fields['ends_at'] );
                    $ev_start_date = date_create_from_format( "mdY", $fields['starts_at'] );

                    if ( $start_date <= $ev_start_date && $end_date >= $ev_end_date )
                        array_push( $ev_array, $fields );
                }

                echo json_encode( array( 'events' => $ev_array ) );

                die;
                break;

            case 'myfossil_resources_list_events_states':
                $states = array();
                foreach ( $events as $ev ) {
                    $meta = parse_meta( get_post_meta( $ev->ID ) );
                    if ( ! array_key_exists( 'State', $meta ) )
                        continue;

                    $states[] = $meta['State'];
                }
                asort( $states );

                echo json_encode( array_values( array_unique( $states ) ) );

                die;
                break;


            case 'myfossil_resources_list_events_types':
                $types = array();
                foreach ( $events as $ev )
                    $types[] = parse_meta( get_post_meta( $ev->ID ) )[ 'type' ];
                asort( $types );

                echo json_encode( array_values( array_unique( $types ) ) );

                die;
                break;


            case 'myfossil_resources_list_events_month_years':
                $month_years = array();
                foreach ( $events as $ev ) {
                    $meta = parse_meta( get_post_meta( $ev->ID ) );
                    if ( ! array_key_exists( 'starts_at', $meta ) )
                        continue;
                    $dt = date_parse( $meta['starts_at'] );
                    $key = sprintf( "%04d-%02d", $dt['year'], $dt['month'] );
                    $value = sprintf( "%s, %04d", self::month_name( $dt['month'] ), $dt['year'] );

                    $month_years[$key] = $value;
                }
                ksort( $month_years );
                echo json_encode( $month_years );

                die;
                break;
        }

    }

    // {{{ Enqueues
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    0.0.1
     */
    public function enqueue_styles()
    {

        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in myFOSSIL_Resources_Public_Loader as all of the hooks are
         * defined in that particular class.
         *
         * The myFOSSIL_Resources_Public_Loader will then create the
         * relationship between the defined hooks and the functions defined in
         * this class.
         */
        wp_enqueue_style( $this->plugin_name, plugin_dir_url( realpath( __FILE__ ) ) .
            'css/myfossil-resources-public.css', array(), $this->version,
            'all' );

    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    0.0.1
     */
    public function enqueue_scripts()
    {
        wp_enqueue_script( 'es5-shim',
            '//cdnjs.cloudflare.com/ajax/libs/es5-shim/4.0.3/es5-shim.js',
            array( 'jquery' ), $this->version, false );

        wp_enqueue_script( 'handlebars',
            '//cdnjs.cloudflare.com/ajax/libs/handlebars.js/2.0.0/handlebars.js',
            array( 'jquery' ), $this->version, false );

        wp_enqueue_script( 'googlemaps',
            '//maps.googleapis.com/maps/api/js?sensor=false',
            array( 'jquery' ), $this->version, false );
    }
    // }}}

    // {{{ Group enhancements
    /**
     * BuddyPress Group meta field utility class factory. 
     *
     * @author  Brandon Wood <btwood@geometeor.com>
     * @since   0.4.0
     * @static
     * @param   string  $id                 Meta field key
     * @param   string  $desc               Meta field description (used in labels)
     * @param   bool    $show   (optional)  Whether to show in the header, default false.
     * @return  stdClass
     */
    public static function field_factory( $id, $desc, $show=false ) {
        $f = new \stdClass;
        $f->id = $id;
        $f->description = $desc;
        $f->show = (bool) $show;
        return $f;
    }

    /**
     * Output form to edit custom meta group fields.
     *
     * @author  Brandon Wood <btwood@geometeor.com>
     * @since   0.4.0
     */
    public function group_header_fields_markup()
    {
        foreach ( $this->custom_fields as $field ) {
            switch ( $field->id ) {
                case 'type':
                    $this->_render_type_select( $field );
                    break;
    
                case 'contact':
                    printf( '<button id="improve-location" class="btn btn-default pull-right" type="button">%s</button>', 
                            '<i class="fa fa-fw fa-magic"></i> Improve Location Data' );
                    printf( '<br class="clearfix" />' );


                default:
                    printf( '<div class="form-group">' );
                    printf( '<label class="control-label" for="%s">%s</label>',
                            $field->id, $field->description );
                    printf( '<input class="form-control" id="%s" type="text" name="%s"
                            value="%s" placeholder="%s" />', $field->id, $field->id,
                            groups_get_groupmeta( bp_get_group_id(), $field->id ),
                            $field->description );
                    printf( '</div>' );
                    break;
            }
        }
    }

    public function _render_type_select($field) {
        ?>
        <div class="form-group">
            <label class="control-label" for="<?=$field->id ?>">
                <?=$field->description ?>
            </label>
            <select class="form-control" id="<?=$field->id ?>" name="<?=$field->id ?>">
            <?php foreach ( $this->group_types as $key => $value ) : ?>
                <?php $selected = ( bp_get_group_id() && ( groups_get_groupmeta( bp_get_group_id(), $field->id ) == $key )) ? ' selected="selected"' : ''; ?>   
                <option value="<?=$key ?>"<?=$selected ?>><?=$value ?></option>
            <?php endforeach; ?>
            </select>
        </div>
        <?php
    }

    /**
     * Save custom group meta data.
     *
     * @author  Brandon Wood <btwood@geometeor.com>
     * @since   0.4.0
     * @param   int     $group_id
     */
    public function group_header_fields_save( $group_id )
    {
        foreach ( $this->custom_fields as $field )
            if ( isset( $_POST[$field->id] ) )
                groups_update_groupmeta( $group_id, $field->id, $_POST[$field->id] );
    }


    /**
     * Custom group header.
     *
     * @author  Brandon Wood <btwood@geometeor.com>
     * @since   0.4.0
     * @param   int     $group_id
     */
    public function output_header() {
        $d = array(
                'Members' => groups_get_total_member_count( bp_get_group_id() ),
                'City' => groups_get_groupmeta( bp_get_group_id(), 'city' ),
                'State' => groups_get_groupmeta( bp_get_group_id(), 'state' )
            );

        printf ( '<dl class="inline">' );
        foreach ( $d as $dt => $dd )
            if ( $dd )
                printf( "<dt>%s</dt>\n<dd>%s</dd>\n\n", $dt, $dd );
        printf ( '</dl>' );
    }
    // }}}

}

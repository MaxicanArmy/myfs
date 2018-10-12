<?php
/**
 * ./models/TimeInterval.php
 *
 * @author Brandon Wood <bwood@atmoapps.com>
 * @package myFOSSIL
 */


namespace myFOSSIL\Plugin\Specimen;

/**
 * PaleoBio Database (PBDB) objects.
 *
 * These objects serve as a basis for all paleontological objects in myFOSSIL,
 * so using the myFOSSIL\PBDB library makes sense here.
 *
 * @since   0.0.1
 * @see     {@link https://github.com/myfossil/pbdb-php}
 */
use myFOSSIL\PBDB;

/**
 * TimeInterval.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class TimeInterval extends Base
{
    const POST_TYPE =  'myfossil_geochron';

    /**
     * TimeInterval.
     *
     * @todo    Add WordPress hook(s)
     * @since   0.0.1
     * @access  public
     * @param unknown $post_id (optional)
     * @param unknown $meta    (optional)
     */
    public function __construct( $post_id=null, $meta=null )
    {
        parent::__construct( $post_id, $meta );

        $this->pbdb = new PBDB\GeologicalTimeInterval;

        $this->_meta_keys = array( 'pbdb_id', 'parent_pbdb_id', 'level', 'color',
            'late_age_ma', 'early_age_ma', 'reference_id' );
    }

    /**
     *
     *
     * @param unknown $recursive (optional)
     * @return unknown
     */
    public function save( $recursive=false )
    {
        return parent::_save( self::POST_TYPE, $recursive );
    }

    // {{{ Custom Post Type

    /**
     */
    public static function register_cpt()
    {
        $labels = array(
            'name'                => __( 'Time Intervals', 'myfossil-specimen' ),
            'singular_name'       => __( 'Time Interval', 'myfossil-specimen' ),
            'menu_name'           => __( 'Time Intervals', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Time Interval:', 'myfossil-specimen' ),
            'all_items'           => __( 'Time Intervals', 'myfossil-specimen' ),
            'view_item'           => __( 'View Time Interval', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Time Interval', 'myfossil-specimen' ),
            'add_new'             => __( 'Add New', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Time Interval', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Time Interval', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Time Interval', 'myfossil-specimen' ),
            'not_found'           => __( 'Time Interval not found', 'myfossil-specimen' ),
            'not_found_in_trash'  => __( 'Time Interval not found in Trash', 'myfossil-specimen' ),
        );

        $args = array(
            'label'               => __( 'myfs_time_interval', 'myfossil-specimen' ),
            'description'         => __( 'Represents a time interval', 'myfossil-specimen' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'author', 'custom-fields',
                'comments' ),
            'hierarchical'        => true,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => null,
            'can_export'          => true,
            'has_archive'         => false,
            'rewrite'             => false,
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-backup'
        );

        register_post_type( self::POST_TYPE, $args );
    }
    // }}}

    /**
     *
     *
     * @param unknown $key
     * @return unknown
     */
    public function __get( $key )
    {
        if ( $key == 'reference' ) {
            if ( $this->reference_id ) {
                $this->_cache->reference = new Reference( $this->reference_id );
                return $this->_cache->reference;
            }
        }
        if ( in_array( $key, array( 'early_age', 'late_age' ) ) )
            $key = $key . '_ma';
        return parent::__get( $key );
    }

    /**
     *
     *
     * @param unknown $key
     * @param unknown $value
     * @return unknown
     */
    public function __set( $key, $value )
    {
        if ( in_array( $key, array( 'early_age', 'late_age' ) ) )
            $key = $key . '_ma';
        return parent::__set( $key, $value );
    }


    public function matches_search_query( $q ) {
        $q = strtolower( $q );
        $v = strtolower( $this->name );
        if ( strpos( $v, $q ) !== false || $v == $q ) {
            return true;
        }
        return false;
    }

    public static function bp_format_activity_json( $json, $tpl )
    {
        $geo0 = new TimeInterval;
        $geo1 = new TimeInterval;

        $changes = $json->changeset;
        $null_keys = array();
        foreach ( $changes as $item ) {
            $geo0->{ $item->key } = $item->from;
            $geo1->{ $item->key } = $item->to;

            if ( $item->from == null )
                $null_keys[] = $item->key;
        }

        if ( count( $changes ) == count( $null_keys ) )
            $geo0 = null;

        $tpl_path = 'activities/geochronology.htm';
        return $tpl->render( $tpl_path, array( 'from' => $geo0, 'to' => $geo1 ) );
    }

    /**
     *
     *
     * @return unknown
     */
    public function __toString()
    {
        $text_color = ( hexdec( $this->color ) > 0xffffff / 2 ) ? '#000' : '#fff';
        if ( $this->level && $this->name )
            if ( $this->color )
                return sprintf(
                    '<p class="fossil-property">%s</p>'
                    . '<span style="background-color: %s; color: %s; padding: 5px;">%s</span>',
                    $this->level, $this->color, $text_color, $this->name );
            else
                return sprintf(
                    "<span class=\"label label-primary\">%s</span> %s",
                    $this->level, $this->name );
            else
                return sprintf( '<span class="unknown">Unknown</span>' );
    }
}

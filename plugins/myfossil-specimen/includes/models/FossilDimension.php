<?php
/**
 * ./models/FossilDimension.php
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
 * FossilDimension.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class FossilDimension extends Base
{
    const POST_TYPE = 'myfossil_fossil_dim';

    /**
     * FossilDimension.
     *
     * @todo    Add WordPress hook(s)
     * @since   0.0.1
     * @access  public
     * @param unknown $post_id (optional)
     * @param unknown $args    (optional)
     */
    public function __construct( $post_id=null, $args=array() )
    {
        parent::__construct( $post_id, $args );

        $this->_meta_keys = array( 'length_meters', 'width_meters',
            'height_meters' );
    }

    /**
     * Save the FossilDimension object.
     *
     * @param bool    $recursive (optional)   Save children objects as well, default false.
     * @return  int     Object's WP_Post ID as saved in the database.
     */
    public function save( $recursive=false )
    {
        return parent::_save( self::POST_TYPE, $recursive );
    }

    // {{{ Custom Post Type
    /**
     * Register custom post type with WordPress.
     */
    public static function register_cpt()
    {
        $args = array(
            'supports'            => array( 'author', 'custom-fields', 'comments' ),
            'public'              => true,
            'show_ui'             => false,
            'rewrite'             => false,
        );

        return register_post_type( self::POST_TYPE, $args );
    }
    // }}}

    public function __get( $key )
    {
        if ( in_array( $key, array( 'length', 'width', 'height' ) ) )
            $key = $key . '_meters';
        return parent::__get( $key );
    }

    public function __set( $key, $value )
    {
        if ( in_array( $key, array( 'length', 'width', 'height' ) ) )
            $key = $key . '_meters';
        return parent::__set( $key, $value );
    }

    public function __toString()
    {
        if ( $this->length && $this->width ) {
            if ( $this->height ) {
                return sprintf( '%12.1f &times; %12.1f &times; %12.1f cm',
                    $this->as_cm( 'length' ),
                    $this->as_cm( 'width' ),
                    $this->as_cm( 'height' ) );
            } else {
                return sprintf( '%12.1f &times; %12.1f cm',
                    $this->as_cm( 'length' ),
                    $this->as_cm( 'width' ) );
            }
        }

        return 'undefined';
    }

    /**
     * Return dimension in units of centimeters.
     *
     * @param string  $key Dimension to return in centimeters.
     * @return float    Dimension in centeriments.
     */
    public function as_cm( $key )
    {
        return $this->$key * 100.;
    }

    public static function bp_format_activity_json( $json, $tpl )
    {
        $dim0 = new FossilDimension;
        $dim1 = new FossilDimension;

        $changes = $json->changeset;
        $null_keys = array();
        foreach ( $changes as $item ) {
            $dim0->{ $item->key } = $item->from;
            $dim1->{ $item->key } = $item->to;

            if ( $item->from == null )
                $null_keys[] = $item->key;
        }

        if ( count( $changes ) == count( $null_keys ) )
            $dim0 = null;

        $content = array( 'from' => $dim0, 'to' => $dim1 );
        $tpl_path = 'activities/dimension.htm';
        return $tpl->render( $tpl_path, $content );
    }
}

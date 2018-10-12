<?php
/**
 * ./models/Reference.php
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
 * Reference.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class Reference extends Base
{

    const POST_TYPE =  'myfossil_reference';

    /**
     * Reference.
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

        $this->_meta_keys = array( 'pbdb_id', 'authors', 'year', 'publication',
            'volume', 'series_number', 'formatted', 'doi', 'editors' );
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

    // {{{ Custom post type
    /**
     */
    public static function register_cpt()
    {
        $args = array(
            'supports'            => array( 'author', 'custom-fields', 'comments' ),
            'public'              => true,
            'show_ui'             => false,
            'rewrite'             => false,
        );

        register_post_type( self::POST_TYPE, $args );
    }
    // }}}
}

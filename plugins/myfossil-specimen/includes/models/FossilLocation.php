<?php
/**
 * ./includes/models/FossilLocation.php
 *
 * FossilLocation class.
 *
 *
 * @link        https://github.com/myfossil
 * @since       0.0.1
 * @subpackage  myFOSSIL/includes
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 */


namespace myFOSSIL\Plugin\Specimen;

/**
 * FossilLocation.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class FossilLocation extends Base
{
    const POST_TYPE = 'myfossil_fossil_loc';

    /**
     *
     *
     * @param unknown $post_id (optional)
     * @param unknown $meta    (optional)
     */
    public function __construct( $post_id=null, $meta=array() )
    {
        parent::__construct( $post_id, $meta );

        $this->_meta_keys = array( 'address', 'city', 'county', 'state',  'zip', 'country', 
            'latitude', 'longitude', 'map_url',
            'not_disclosed' );
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

    /**
     *
     *
     * @return unknown
     */
    public function __toString()
    {
        if ( $this->city && $this->state )
            return sprintf( "%s, %s\n", $this->city, $this->state );
        if ( $this->county && $this->state )
            return sprintf( "%s, %s\n", $this->county, $this->state );
        if ( $this->state && $this->country )
            return sprintf( "%s, %s\n", $this->state, $this->country );
        foreach ( array( 'country', 'state', 'county', 'city' ) as $k )
            if ( $this->{$k} )
            return (string) $this->{$k};
        return (string) null;
    }

    public function matches_search_query( $q ) {
        $q = strtolower( $q );
        foreach ( array( "country", "state", "county", "city", "address" ) as $k ) {
            $v = strtolower( $this->{ $k } );
            if ( strpos( $v, $q ) !== false || $v == $q ) {
                return true;
            }
        }
        return false;
    }


    // {{{ Custom Post Type

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

    public static function bp_format_activity_json( $json, $tpl )
    {
        $t0 = new FossilLocation;
        $t1 = new FossilLocation;

        $changes = $json->changeset;
        $null_keys = array();
        foreach ( $changes as $item ) {
            $t0->{ $item->key } = $item->from;
            $t1->{ $item->key } = $item->to;

            if ( $item->from == null )
                $null_keys[] = $item->key;
        }

        if ( count( $changes ) == count( $null_keys ) )
            $t0 = null;

        $tpl_path = 'activities/location.htm';
        return $tpl->render( $tpl_path, array( 'from' => $t0, 'to' => $t1 ) );
    }
}

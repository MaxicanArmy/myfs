<?php
/**
 * ./includes/models/FossilGeochronology.php
 *
 * @link        https://github.com/myfossil
 * @since       0.5.0
 * @subpackage  myFOSSIL/includes
 * @author      Brandon Wood <bwood@atmoapps.com>
 * @package     myFOSSIL
 */


namespace myFOSSIL\Plugin\Specimen;

/**
 * FossilGeochronology
 *
 * @since      0.5.0
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class FossilGeochronology extends Base
{
    const POST_TYPE =  'myfossil_geochrons';

    public function __construct( $post_id=null, $meta=array() )
    {
        parent::__construct( $post_id, $meta );
        $this->_meta_keys = self::create_meta_keys();
    }

    public static function create_meta_keys()
    {
        $meta_keys = array();
        foreach ( self::get_ranks() as $k ) {
            $meta_keys[] = sprintf( 'time_interval_id_%s', $k );
        }
        return $meta_keys;
    }

    public static function get_ranks()
    {
        return array( 'era', 'period', 'epoch', 'age' );
    }

    public function save( $recursive=false )
    {
        return parent::_save( self::POST_TYPE, $recursive );
    }

    public static function register_cpt()
    {
        $args = array(
            'supports'            => array( 'custom-fields' ),
            'capability_type'     => 'post',
        );

        register_post_type( self::POST_TYPE, $args );
    }

    public function __get( $key )
    {
        if ( in_array( $key, self::get_ranks() ) ) {
            return new TimeInterval(
                $this->{ sprintf( 'time_interval_id_%s', $key ) }
            );
        }

        return parent::__get( $key );
    }

    public function __set( $key, $value )
    {
        if ( in_array( $key, $this::get_ranks() ) ) {
            $key = sprintf( 'time_interval_id_%s', $key );
            $value = $value->ID;
        }

        return parent::__set( $key, $value );
    }

    public function matches_search_query( $q ) {
        $q = strtolower( $q );
        foreach ( $this::get_ranks() as $rank ) {
            $v = strtolower( $this->{ $rank }->name );
            if ( strpos( $v, $q ) !== false || $v == $q ) {
                return true;
            }
        }
        return false;
    }

    public function __toString()
    {
        foreach ( array_reverse(self::get_ranks()) as $rank ) {
            if ( $this->{ sprintf( 'time_interval_id_%s', $rank ) } ) {
                return (string) $this->{ $rank };
            }
        }
        return '<span class="unknown">Unknown</span>';
    }

}

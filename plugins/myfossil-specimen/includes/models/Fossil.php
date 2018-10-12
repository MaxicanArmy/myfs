<?php
/**
 * ./models/Fossil.php
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
 * Fossil.
 *
 * Represents a fossil specimen instance, which is expressed in WordPress as a
 * custom post type (CPT). This class contents methods that can setup the CPT
 * in WordPress and BuddyPress, if possible. Additionally, this class handles
 * all CRUD operations for Fossil specimen.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class Fossil extends Base
{
    /**
     * WordPress post_type.
     *
     * @access  const
     * @var     string  POST_TYPE
     */
    const POST_TYPE = 'myfossil_fossil';

    /**
     * Fossil.
     *
     * @since   0.0.1
     * @access  public
     * @param int     $post_id (optional) WordPress post ID for this object.
     * @param array   $meta    (optional) Metadata to associate with this object.
     */
    public function __construct( $post_id=null, $meta=array() )
    {
        parent::__construct( $post_id, $meta );

        $this->pbdb = new PBDB\FossilOccurence;

        $this->_meta_keys = array( 'pbdb_id', 'taxa_id', 'location_id',
                'time_interval_id', 'geochronology_id', 'stratum_formation_id',
                'stratum_group_id', 'stratum_member_id', 'dimension_id',
                'reference_id', 'image_id'
        );
    }

    // {{{ _save
    /**
     * Update or create new object in the database.
     *
     * If this object does not currently exist in the database, identified by
     * $this->id, then a new object will be created in the database and the new
     * id set to $this->id, otherwise overwrite all properties of the object
     * and update the last modified column.
     *
     * @todo    Add WordPress hook(s)
     *
     * @since   0.0.1
     * @access  public
     * @param bool    $recursive (optional)   Recurse saving of children objects as well, default false.
     * @param bool    $publish   (optional)     Whether to publish the post immediately, default false.
     * @return  bool    True upon success, false upon failure to save.
     */
    public function save( $recursive=false, $publish=false )
    {
        return parent::_save( self::POST_TYPE, $recursive, $publish );
    }
    // }}}

    public function matches_search_query( $q ) {
        $strata_match = false;
        foreach ( Stratum::get_ranks() as $rank ) {
            if ( property_exists( $this->strata, $rank ) ) {
                $v = strtolower( $this->strata->{ $rank }->name );
                if ( strpos( $v, $q ) !== false || $v == $q ) {
                    return true;
                }
            }
        }
        return $this->taxa->matches_search_query( $q )
            || $this->location->matches_search_query( $q )
            || $this->time_interval->matches_search_query( $q )
            || $this->geochronology->matches_search_query( $q )
            || $this->dimension->matches_search_query( $q )
            || intval( $q ) === $this->id;
    }

    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
    *This is a catch-all for when aspects of fossils are first created so that the buddypress activity doesn't just display an empty entry.
    */
    public static function bp_format_activity_json( $content, $tpl )
    {
        $json = @json_decode( $content );

        // Bail if it's not even JSON
        // Bail if we don't have a post type defined
        if ( ! self::isJson( $content )
            || ! property_exists( $json, 'post_type' )
            || ! property_exists( $json, 'changeset' ) ) 
            return $content;

        switch ( $json->post_type ) {
            case FossilDimension::POST_TYPE:
                return FossilDimension::bp_format_activity_json( $json, $tpl );
                break;
            case FossilLocation::POST_TYPE:
                return FossilLocation::bp_format_activity_json( $json, $tpl );
                break;
            case Stratum::POST_TYPE:
                $t0 = new Stratum;
                $t1 = new Stratum;

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

                $tpl_path = 'activities/lithostratigraphy.htm';
                return $tpl->render( $tpl_path, array( 'from' => $t0, 'to' => $t1 ) );
                return Stratum::bp_format_activity_json( $json, $tpl );
                break;
            case Taxon::POST_TYPE:
                return Taxon::bp_format_activity_json( $json, $tpl );
                break;
            case FossilTaxa::POST_TYPE:
                return FossilTaxa::bp_format_activity_json( $json, $tpl );
                break;
            case TimeInterval::POST_TYPE:
                return TimeInterval::bp_format_activity_json( $json, $tpl );
                break;
            default:
                return; // $content;
                break;
        }
    }

    // {{{ Custom Post Type
    /**
     * Register the custom post type for this class.
     *
     * Defines labels and arguments that will represent the custom post type
     * in Wordpress.
     */
    public static function register_cpt()
    {
        $labels = array(
            'name'                => __( 'Fossils', 'myfossil-specimen' ),
            'singular_name'       => __( 'Fossil', 'myfossil-specimen' ),
            'menu_name'           => __( 'Fossils', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Fossil:', 'myfossil-specimen' ),
            'all_items'           => __( 'Fossils', 'myfossil-specimen' ),
            'view_item'           => __( 'View Fossil', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Fossil', 'myfossil-specimen' ),
            'add_new'             => __( 'Add New', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Fossil', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Fossil', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Fossil', 'myfossil-specimen' ),
            'not_found'           => __( 'Fossil not found', 'myfossil-specimen' ),
            'not_found_in_trash'  => __( 'Fossil not found in Trash', 'myfossil-specimen' ),
        );

        $args = array(
            'label'               => __( self::POST_TYPE, 'myfossil-specimen' ),
            'description'         => __( 'Represents a fossil', 'myfossil-specimen' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'author',
                'thumbnail', 'custom-fields', 'comments', 'revisions',
                'post-formats' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => null,
            'can_export'          => true,
            'has_archive'         => false,
            'rewrite'             => array(
                'slug' => 'fossils/%fossil_id%',
                'with_front' => false,
                'feed' => true,
                'pages' => true
            ),
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-carrot'
        );

        register_post_type( self::POST_TYPE, $args );
    }
    // }}}

    /**
     * Return the URL for a given fossil post_id.
     *
     * @todo    refactor to use internal WordPress routing information, rather than this constant.
     *
     * @since   0.1.0
     * @param int     $fossil_id
     * @return  string   Base URL for the fossil, does not include domain (e.g. '/fossils/240')
     */
    public static function get_url( $fossil_id )
    {
        return sprintf( '/fossils/%d', $fossil_id );
    }

    // {{{ __get
    /**
     * Custom getters specific to Fossil's
     *
     * @todo    Break up swith statements into private methods
     *
     * @since   0.0.1
     * @access  public
     */
    public function __get( $key )
    {
        if ( property_exists( $this->_cache, $key ) )
            return $this->_cache->{$key};

        switch ( $key ) {
        case 'name':
            return sprintf( 'Fossil %06d', $this->id );
            break;

        case 'taxa':
            if ( $this->taxa_id ) {
                $this->_cache->taxa = new FossilTaxa( $this->taxa_id );
            } else {
                $this->_cache->taxa = new FossilTaxa;
            }
            return $this->_cache->taxa;
            break;

        case 'location':
            if ( $this->location_id ) {
                $this->_cache->location = new FossilLocation( $this->location_id );
            } else {
                $this->_cache->location = new FossilLocation;
            }
            return $this->_cache->location;
            break;

        case 'time':
        case 'time_interval':
            if ( $this->time_interval_id ) {
                $this->_cache->time_interval = new TimeInterval(
                        $this->time_interval_id );
            } else {
                $this->_cache->time_interval = new TimeInterval;
            }
            return $this->_cache->time_interval;
            break;

        case 'geochronology':
            if ( $this->geochronology_id ) {
                $this->_cache->geochronology = new FossilGeochronology(
                        $this->geochronology_id );
            } else {
                $this->_cache->geochronology = new FossilGeochronology;
            }
            return $this->_cache->geochronology;
            break;

        case 'strata':
            if ( ! property_exists( $this->_cache, 'strata' ) )
                $this->_cache->strata = new \stdClass;

            foreach ( Stratum::get_ranks() as $rank ) {
                $stratum_key = sprintf( 'stratum_%s_id', $rank );

                if ( ! $this->{ $stratum_key } )
                    continue;

                if ( ! property_exists( $this->_cache->strata, $rank )
                    || $this->_cache->strata->{ $rank }->id !== $this->{ $stratum_key } )
                    $this->_cache->strata->{ $rank } = new Stratum( $this->{ $stratum_key } );
            }

            return $this->_cache->strata;

            break;

        case 'dim':
        case 'dimension':
            if ( $this->dimension_id ) {
                $this->_cache->dimension = new FossilDimension( $this->dimension_id );
            } else {
                $this->_cache->dimension = new FossilDimension;
            }
            return $this->_cache->dimension;
            break;

        case 'reference':
            if ( $this->reference_id ) {
                $this->_cache->reference = new Reference( $this->reference_id );
            }
            return $this->_cache->reference;
            break;

        case 'history':
            return null;
            break;

        case 'image':
            if ( ! $this->id ) return;
            $_ = get_attached_media( 'image', $this->id );
            if ( $this->image_id ) {
                foreach ( $_ as $image ) {
                    if ( $image->ID == $this->image_id ) {
                        $m = $image;
                    }
                }
            }
            if ( !isset( $m ) || empty( $m ) ) {
                $m = array_pop( $_ );
            }
            if ( $m && $m->guid )
                return $m->guid;
            break;

        default:
            return parent::__get( $key );
            break;

        }

        return null;
    }
    // }}}

}

<?php
/**
 * ./includes/models/Taxon.php
 *
 * Taxon class.
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
 * Taxon.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class Taxon extends Base
{
    const POST_TYPE =  'myfossil_taxon';

    /**
     * Taxon.
     *
     * @todo    Add WordPress hook(s)
     * @since   0.0.1
     * @access  public
     * @param unknown $post_id (optional)
     * @param unknown $meta    (optional)
     */
    public function __construct( $post_id=null, $meta=array() )
    {
        parent::__construct( $post_id, $meta );

        $this->_meta_keys = array( 'pbdb_id', 'parent_pbdb_id', 'common_name',
            'rank', 'reference_id' );

        $this->pbdb = new PBDB\Taxon;
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
            'name'                => __( 'Taxa', 'myfossil-specimen' ),
            'singular_name'       => __( 'Taxon', 'myfossil-specimen' ),
            'menu_name'           => __( 'Taxa', 'myfossil-specimen' ),
            'parent_item_colon'   => __( 'Parent Taxon:', 'myfossil-specimen' ),
            'all_items'           => __( 'Taxa', 'myfossil-specimen' ),
            'view_item'           => __( 'View Taxon', 'myfossil-specimen' ),
            'add_new_item'        => __( 'Add New Taxon', 'myfossil-specimen' ),
            'add_new'             => __( 'Add New', 'myfossil-specimen' ),
            'edit_item'           => __( 'Edit Taxon', 'myfossil-specimen' ),
            'update_item'         => __( 'Update Taxon', 'myfossil-specimen' ),
            'search_items'        => __( 'Search Taxon', 'myfossil-specimen' ),
            'not_found'           => __( 'Taxon not found', 'myfossil-specimen' ),
            'not_found_in_trash'  => __( 'Taxon not found in Trash', 'myfossil-specimen' ),
        );

        $args = array(
            'label'               => __( self::POST_TYPE, 'myfossil-specimen' ),
            'description'         => __( 'Biological classification
                representations (taxa)', 'myfossil-specimen' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'author', 'thumbnail',
                'custom-fields', 'comments' ),
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
            'menu_icon'           => 'dashicons-editor-ul'
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

        return parent::__get( $key );
    }

    public static function bp_format_activity_json( $json, $tpl )
    {
        $t0 = new Taxon;
        $t1 = new Taxon;

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

        $tpl_path = 'activities/taxon.htm';

        return $tpl->render( $tpl_path, array( 'from' => $t0, 'to' => $t1 ) );
    }

    /**
     *
     *
     * @return unknown
     */
    public function __toString()
    {
        return sprintf( '<span class="fossil-property">%s</span> %s', $this->rank,
            $this->name );
    }
}

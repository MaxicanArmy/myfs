<?php
namespace myFOSSIL\Plugin\Specimen;

/* Single Fossil View */
require_once 'partials/single/index.php';

/* List View */
require_once 'partials/list/create-button.php';
require_once 'partials/list/table.php';
require_once 'partials/list/member.php';

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://atmoapps.com
 * @since      0.0.1
 *
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the dashboard-specific stylesheet and JavaScript.
 *
 * @package    myFOSSIL
 * @subpackage myFOSSIL/admin
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
class myFOSSIL_Specimen_Public
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

    private $twig_loader;
    private $twig;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.0.1
     * @var      string    $name       The name of the plugin.
     * @var      string    $version    The version of this plugin.
     */
    public function __construct( $name, $version )
    {

        $this->name = $name;
        $this->version = $version;

    }

    public function bp_register_activity_actions()
    {
        // Register BuddyPress Activity actions for each model.
        Fossil::register_buddypress_activities( Fossil::POST_TYPE );
        FossilDimension::register_buddypress_activities( FossilDimension::POST_TYPE );
        FossilLocation::register_buddypress_activities( FossilLocation::POST_TYPE );
        Stratum::register_buddypress_activities( Stratum::POST_TYPE );
        Taxon::register_buddypress_activities( Taxon::POST_TYPE );
        FossilTaxa::register_buddypress_activities( FossilTaxa::POST_TYPE );
        TimeInterval::register_buddypress_activities( TimeInterval::POST_TYPE );
    }

    /**
     * Returns `Twig_Environment` object.
     */
    public function get_twig()
    {
        if ( ! $this->twig ) {
            if ( ! $this->twig_loader ) {
                $template_dir = plugin_dir_path( realpath( __FILE__ ) ) . '/partials';
                $this->twig_loader = new \Twig_Loader_Filesystem( $template_dir );
            }
            $this->twig = new \Twig_Environment( $this->twig_loader,
                array( 'auto_reload' => true, 'autoescape' => false ) );
            $this->twig->addExtension( new \Twig_Extension_Debug() );
        }

        return $this->twig;
    }

    public function bp_get_activity_content_body( $content )
    {
        $json = @json_decode( $content );

        // Bail if it's not even JSON
        // Bail if we don't have a post type defined
        if ( ! self::isJson( $content )
            || ! property_exists( $json, 'post_type' )
            || ! property_exists( $json, 'changeset' ) )
            return $content;

        $tpl = $this->get_twig();
        switch ( $json->post_type ) {
        case FossilDimension::POST_TYPE:
            return FossilDimension::bp_format_activity_json( $json, $tpl );
            break;
        case FossilLocation::POST_TYPE:
            return FossilLocation::bp_format_activity_json( $json, $tpl );
            break;
        case Stratum::POST_TYPE:
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
        case FossilGeochronology::POST_TYPE:
            return FossilGeochronology::bp_format_activity_json( $json, $tpl );
            break;
        case Fossil::POST_TYPE:
        default:
            return; // $content;
            break;
        }
    }

    public static function isJson($string) {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public function bp_add_member_fossil_nav_items()
    {
        global $bp;

        \bp_core_new_nav_item(
            array(
                'name' => 'Fossils',
                'slug' => 'fossils',
                'default_subnav_slug' => 'fossils',
                'parent_url' => bp_displayed_user_domain(),
                'parent_slug' => $bp->members->slug . bp_displayed_user_id(),
                'position' => 50,
                'show_for_displayed_user' => true,
                'screen_function' => 'fossil_view_member_fossils'
            )
        );
    }

    public static function buddypress_add_activity( $type, $item_id, $action, $content, $secondary_item_id=0 )
    {
        $args = array(
            'component' => 'myfossil',
            'item_id' => $item_id,
            'user_id' => \bp_loggedin_user_id(),
            'content' => $content,
            'secondary_item_id' => $secondary_item_id,
            'action' => $action,
            'type' => $type
        );

        return \bp_activity_add( $args );
    }

    public static function upload_user_file( $file=array(), $post_id=0 )
    {
        require_once ABSPATH . 'wp-admin/includes/admin.php';

        $file_return = wp_handle_upload( $file, array( 'test_form' => false ) );

        if ( isset( $file_return['error'] ) || isset( $file_return['upload_error_handler'] ) ) {
            return array( $file_return, $file );
        } else {
            $filename = $file_return['file'];

            $attachment = array(
                'post_mime_type' => $file_return['type'],
                'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content' => '',
                'post_status' => 'inherit',
                'guid' => $file_return['url']
            );

            if ( ! in_array( $file_return['type'], array( 'image/jpeg', 'image/png' ) ) )
                return false;

            $attachment_id = wp_insert_attachment( $attachment, $file_return['url'], $post_id );

            require_once ABSPATH . 'wp-admin/includes/image.php';

            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $filename );
            wp_update_attachment_metadata( $attachment_id, $attachment_data );

            if ( 0 < intval( $attachment_id ) ) {
                $user_link = \bp_core_get_userlink( \bp_loggedin_user_id() );
                $fossil_link = sprintf( '<a href="/fossils/%d">Fossil #%06d</a>', $post_id, $post_id );
                $action = sprintf( '%s uploaded a new image to %s', $user_link, $fossil_link );
                $image_src = wp_get_attachment_url( $attachment_id );
                $content = sprintf( '<img src="%s" class="img-responsive" />', $image_src );
                self::buddypress_add_activity( 'uploaded_image', $post_id, $action, $content, $attachment_id );

                return $attachment_id;
            }
        }

        return -2;
    }

    /**
     * AJAX call handler
     */
    public function ajax_handler()
    {
        $action = $_POST['action'];

        $permitted = true;
        $permissions = array();

        if ( array_key_exists( 'post_id', $_POST ) ) {
            $post_id = $_POST['post_id'];

            // Check permissions
            $permissions = array(
                'myfossil_create_fossil'          => current_user_can( 'publish_posts' ),
                'myfossil_delete_fossil_image'    => current_user_can( 'delete_post', $post_id ),
                'myfossil_feature_fossil_image'   => current_user_can( 'edit_post', $post_id ),
                'myfossil_fossil_comment'         => is_user_logged_in(),
                'myfossil_fossil_delete'          => current_user_can( 'delete_post', $post_id ),
                'myfossil_save_dimensions'        => current_user_can( 'edit_post', $post_id ),
                'myfossil_save_geochronology'     => current_user_can( 'edit_post', $post_id ),
                'myfossil_save_lithostratigraphy' => current_user_can( 'edit_post', $post_id ),
                'myfossil_save_location'          => current_user_can( 'edit_post', $post_id ),
                'myfossil_save_status'            => current_user_can( 'edit_post', $post_id ),
                'myfossil_save_taxon'             => current_user_can( 'edit_post', $post_id ),
                'myfossil_upload_fossil_image'    => current_user_can( 'edit_post', $post_id ),
            );
        }

        if ( array_key_exists( $action, $permissions ) ) {
            if ( ! $permissions[$action] ) {
                // User not permitted to do this, trigger error below
                $permitted = false;
            }
        }

        header( 'Content-Type: application/json' );

        // Check nonce
        if ( ! check_ajax_referer( 'myfossil_specimen', 'nonce', false ) || ! $permitted ) {
            header( 'HTTP/1.0 403 Forbidden' );
            $return_args = array(
                "result" => "Error",
                "message" => "403 Forbidden",
            );

            echo json_encode( $return_args );
            die;
        }

        switch ( $_POST['action'] ) {
            // {{{ save
        case 'myfossil_save_taxon':
            $fossil = new Fossil( $_POST['post_id'] );

            if ( $fossil->taxa_id )
                $taxa = new FossilTaxa( $fossil->taxa_id );
            else
                $taxa = new FossilTaxa;

            foreach ( FossilTaxa::get_ranks() as $rank ) {
                $taxon = new Taxon;
                $taxon->name    = $_POST['taxa'][$rank];
                $taxon->rank    = $rank;
                $taxon->parent_id = $post_id;
                $taxa->{ sprintf( "taxon_id_%s", $rank ) } = $taxon->save();
            }

            $fossil->taxa_id = $taxa->save();

            echo json_encode( $fossil->save() );
            die;
            break;

        case 'myfossil_save_geochronology':
            $fossil = new Fossil( $_POST['post_id'] );

            if ( $fossil->time_interval_id )
                $ti = new TimeInterval( $fossil->time_interval_id );
            else
                $ti = new TimeInterval;

            $ti->pbdb_id = $_POST['geochronology']['pbdb'];
            $ti->color   = $_POST['geochronology']['color'];
            $ti->level   = $_POST['geochronology']['level'];
            $ti->name    = $_POST['geochronology']['name'];
            $ti->comment = $_POST['comment'];
            $ti->parent_id = $post_id;

            $fossil->time_interval_id = $ti->save();

            echo json_encode( $fossil->save() );
            die;
            break;

        case 'myfossil_save_lithostratigraphy':
            $fossil = new Fossil( $_POST['post_id'] );

            foreach ( Stratum::get_ranks() as $rank ) {
                if ( ! array_key_exists( $rank, $_POST['strata'] ) )
                    continue;

                $stratum_id_key = sprintf( 'stratum_%s_id', $rank );

                if ( $fossil->{ $stratum_id_key } )
                    $stratum = new Stratum( $fossil->{ $stratum_id_key } );
                else
                    $stratum = new Stratum;

                $stratum->name = $_POST['strata'][$rank];
                $stratum->comment = $_POST['comment'];
                $stratum->parent_id = $post_id;

                if ( ! empty( $stratum->name ) && ! is_null( $stratum->name ) ) {
                    $fossil->{ $stratum_id_key } = $stratum->save();
                }
            }

            echo json_encode( $fossil->save() );
            die;
            break;

        case 'myfossil_save_dimensions':
            $fossil = new Fossil( $_POST['post_id'] );

            if ( $fossil->dimension_id )
                $dim = new FossilDimension( $fossil->dimension_id );
            else
                $dim = new FossilDimension;

            // Dimensions coming in as *centimeters*
            $length = (float) $_POST['length'];
            $width  = (float) $_POST['width'];
            $height = (float) $_POST['height'];

            $dim->length = $length / 100.; // convert to meters
            $dim->width  = $width  / 100.; // convert to meters
            $dim->height = $height / 100.; // convert to meters
            $dim->comment = $_POST['comment'];
            $dim->parent_id = $post_id;

            $fossil->dimension_id = $dim->save();

            echo json_encode( $fossil->save() );
            die;
            break;

        case 'myfossil_save_location':
            $fossil = new Fossil( $_POST['post_id'] );

            if ( $fossil->location_id )
                $location = new FossilLocation( $fossil->location_id );
            else
                $location = new FossilLocation;

            foreach ( array( 'latitude', 'longitude', 'country', 'state',
                    'county', 'city' ) as $k ) {
                $location->{ $k } = $_POST['location'][$k];
            }

            $location->comment = $_POST['comment'];
            $location->not_disclosed = $_POST['not_disclosed'];
            $location->parent_id = $post_id;

            $fossil->location_id = $location->save();

            echo json_encode( $fossil->save() );
            die;
            break;

        case 'myfossil_save_status':
            if ( $_POST['post_id'] <= 0 ) die;

            $args = array(
                'ID'     => $_POST['post_id'],
                'post_status' => $_POST['post_status']
            );

            echo json_encode( wp_update_post( $args ) );
            die;
            break;
            // }}}

            // {{{ create
        case 'myfossil_create_fossil':
            $fossil = new Fossil;
            echo json_encode( $fossil->save() );
            die;
            break;
            // }}}

        case 'myfossil_fossil_comment':
            $post_id = $_POST['post_id'];
            $fossil = new Fossil( $post_id );
            $comment = $_POST['comment'];

            if ( empty( $comment ) ) die;

            $activity_id = \bp_activity_add(
                array(
                    'component' => Fossil::BP_COMPONENT_ID,
                    'item_id' => $post_id,
                    'user_id' => \bp_loggedin_user_id(),
                    'content' => $comment,
                    'secondary_item_id' => $fossil->wp_post->post_author,
                    'type' => Fossil::POST_TYPE . '_comment'
                )
            );

            echo $activity_id;

            die;
            break;

        case 'myfossil_fossil_delete':
            $post_id = $_POST['post_id'];
            if ( \current_user_can( 'delete_post', $post_id ) ) {
                wp_trash_post( $post_id );
                echo json_encode( $post_id );
            } else {
                echo json_encode( array( 'error' => 'You do not have
                                permission to delete this fossil' ) );
            }
            die;
            break;

        case 'myfossil_delete_fossil_image':
            if ( false === wp_delete_attachment( $_POST['image_id'] ) ) {
                echo json_encode( array( 'error' => 'Failed to delete image' ) );
            } else {
                echo json_encode( 1 );
            }
            die;
            break;

        case 'myfossil_feature_fossil_image':
            $post_id = $_POST['post_id'];
            $fossil = new Fossil( $post_id );
            $image_id = $_POST['image_id'];

            if ( $post_id && $fossil && $image_id ) {
                $fossil->image_id = $image_id;
                if ( $fossil->save() ) {
                    echo json_encode( 1 );
                } else {
                    echo json_encode( array( 'error' => "Error saving Fossil" ) );
                }
            }
            die;
            break;

        case 'myfossil_upload_fossil_image':
            if ( empty( $_FILES ) ) {
                header( 'HTTP/1.0 400 Bad Request' );
                echo json_encode(
                    array(
                        'result' => 'Error',
                        'message' => '400 Bad Request'
                    )
                );
                die;
            }

            $fh = $_FILES['files'];
            foreach ( $_FILES['files'] as $k => $v ) {
                $fh[$k] = $v[0];
            }

            $attachment_id = self::upload_user_file( $fh, $_POST['post_id'] );

            if ( ! $attachment_id )
                echo json_encode( array( 'error' => 'Invalid file type' ) );
            else
                echo json_encode(
                    array(
                        'post_id' => $attachment_id,
                        'src' => wp_get_attachment_url( $attachment_id )
                    )
                );
            die;
            break;
        }

    }

    public function add_rewrite_tags()
    {
        add_rewrite_tag( '%fossil_id%', '([^&/]+)' );
        add_rewrite_tag( '%fossil_view%', '(information|history|discussion)' );
    }

    public function fix_fossil_rewrites()
    {
        add_rewrite_rule(
            '^fossils/page/([^/]*)?',
            'index.php?pagename=fossils' . '&paged=$matches[1]',
            'top'
        );

        add_rewrite_rule(
            '^fossils/([^/]*)/(information|history|discussion|images|settings)/?',
            'index.php?pagename=fossils' . '&fossil_id=$matches[1]' .
            '&fossil_view=$matches[2]',
            'top'
        );

        add_rewrite_rule(
            '^fossils/([^/]*)/?',
            'index.php?pagename=fossils' . '&fossil_id=$matches[1]' .
            '&fossil_view=information',
            'top'
        );

    }

}

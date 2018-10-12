<?php
/**
 * ./includes/models/Base.php
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
 * Base.
 *
 * Abstract class that has common methods and properties to the paleontological
 * objects in this package.
 *
 * @since      0.0.1
 * @package    myFOSSIL
 * @subpackage myFOSSIL/includes
 * @author     Brandon Wood <bwood@atmoapps.com>
 */
abstract class Base
{
    // {{{ Constants
    /**
     * WordPress post_type.
     *
     * @access  const
     * @var     string  POST_TYPE
     */
    const POST_TYPE = null;

    /**
     * BuddyPress Activity component_id.
     *
     * @access  const
     * @var     string  BP_COMPONENT_ID
     * @since   0.1.0
     * @see     bp_activity_maybe_update
     */
    const BP_COMPONENT_ID = 'myfossil';

    // }}}
    // {{{ Properties

    /**
     * PBDB query object for this class.
     *
     * @access  public
     * @var     \myFOSSIL\PBDB\Object   $pbdb
     */
    public $pbdb;

    /**
     * WordPress post object (since WordPress decided to make WP_Post final).
     *
     * @var WP_Post $wp_post
     */
    public $wp_post;

    /**
     * Associative array that holds metadata about this object.
     *
     * @access  protected
     * @var     array       $_meta
     */
    protected $_meta;

    /**
     * Array that defines the valid keys of the $_meta associative array.
     *
     * @access  protected
     * @var     array       $_meta_keys
     * @see     get_meta_keys()
     * @see     $_meta
     */
    protected $_meta_keys;

    /**
     * Cache to hold child Objects to prevent hitting the database
     * unnecessarily.
     *
     * @access  protected
     * @var     \stdClass   $_cache
     */
    protected $_cache;

    /**
     * Holds updates to the Object that have been made via __set.
     *
     * @access  protected
     * @var     array       $_history
     */
    protected $_history;

    /**
     * Holds comment string about the update.
     *
     * @access  public
     * @var     string  $comment
     */
    public $comment;

    // }}}

    /**
     * Create Base class.
     *
     * @param int     $post_id (optional) WordPress post ID for this object.
     * @param array   $meta    (optional) Metadata to associate with this object.
     */
    public function __construct( $post_id=null, $meta=array() )
    {
        $this->_meta = new \stdClass;
        $this->_cache = new \stdClass;
        $this->_history = array();

        /*
         * Load the WordPress post, if defined
         */
        $this->wp_post = $post_id ? get_post( $post_id ) : null;
        if ( $this->wp_post && $this->wp_post->ID ) {
            /*
             * We have a WP_Post, so pull metadata off the post custom fields.
             */
            foreach ( get_post_custom( $this->wp_post->ID ) as $k => $v ) {
                if ( is_array( $v ) ) {
                    if ( count( $v ) == 1 ) {
                        $this->_meta->{ $k } = array_pop( $v );
                    }
                } else {
                    $this->_meta->{ $k } = $v;
                }
            }
        }

        /*
         * Load in metadata provided to the constructor, overwriting without
         * prompt or warning.
         */
        if ( is_array( $meta ) && count( $meta ) ) {
            foreach ( $meta as $k => $v ) {
                $this->_meta->{ $k } = $v;
            }
        }
    }

    // {{{ __get
    /**
     * Custom getter for properties that span multiple data sources.
     *
     * First attempts to get a local property, then tries the property private
     * array, then tries WordPress metadata, then tries PBDB, and then gives
     * up (returns null).
     *
     * Object Property => Object Meta Properties => WordPress => PBDB
     *
     * @since   0.0.1
     * @access  public
     * @param string  $key
     * @return  mixed    value of the property, null if not found.
     */
    public function __get( $key )
    {
        if ( $key == 'name' && $this->wp_post && $this->wp_post->post_title )
            return $this->wp_post->post_title;

        if ( $key == 'author' ) {
            if ( ! $this->wp_post->post_author ) return;
            $u = new \WP_User( $this->wp_post->post_author );
            return $u;
        }

        if ( $key == 'created_at' || $key == 'updated_at' ) {
            if ( ! $this->wp_post->post_author ) return;
            return $this->wp_post->post_date;
        }

        if ( ( $key == 'post_id' || $key == 'id' ) && $this->wp_post
            && $this->wp_post->ID )
            return $this->wp_post->ID;

        /* Try local property */
        if ( property_exists( $this, $key ) )
            return $this->$key;

        /* Try local metadata */
        if ( property_exists( $this->_meta, $key ) )
            return $this->_meta->{$key};

        /* Try WordPress */
        if ( $this->wp_post && $this->wp_post->ID && $this->wp_post->{$key} )
            return $this->wp_post->{$key};

        /* Try PBDB */
        if ( $this->pbdb && property_exists( $this->pbdb, $key ) )
            return $this->pbdb->{$key};

        return; // null
    }
    // }}}

    public function __isset( $key )
    {
        return $this->{ $key } !== null;
    }

    // {{{ __set
    /**
     * Custom setter for properties that span multiple data sources.
     *
     * @since   0.0.1
     * @access  public
     * @param string  $key
     * @param mixed   $value
     */
    public function __set( $key, $value )
    {
        if ( $key == 'comment' ) {
            $this->comment = $value;
            return;
        }

        if ( $this->{ $key } && (string) $this->{ $key } !== (string) $value ) {
            $this->_history[] = array(
                'key' => $key,
                'from' => str_replace("\\", "", $this->{ $key }),
                'to' => str_replace("\\", "", $value)
            );
        } elseif ( ! $this->{ $key } ) {
            $this->_history[] = array(
                'key' => $key,
                'from' => null,
                'to' => str_replace("\\", "", $value)
            );
        }

        if ( $key == 'id' && $this->wp_post ) {
            $this->wp_post->ID = $value;
        }

        if ( $key == 'name' ) {
            if ( $this->wp_post ) {
                $this->wp_post->post_title = $value;
            } else {
                $this->_meta->name = $value;
            }
        }

        /* Special cases when setting the PBDB ID */
        if ( $this->pbdb ) {
            if ( $key == 'pbdbid' || $key == 'pbdb_id' ) {
                $this->pbdb->pbdbid = $value;
            } elseif ( $key == 'parent_pbdb_id' || $key == 'parent_pbdbid' ) {
                $this->pbdb->parent_no = $value;
            }
        }

        /* Set local properties of Object */
        if ( property_exists( $this, $key ) ) {
            $this->{$key} = $value;
        } else {
            $this->_meta->{$key} = $value;
        }
    }
    // }}}

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
     * @param string  $post_type WordPress post_type of the object to save.
     * @param bool    $recursive (optional)   Recurse saving of children objects as well, default false.
     * @param bool    $publish   (optional)     Whether to publish the post immediately, default false.
     * @return  bool    True upon success, false upon failure to save.
     */
    protected function _save( $post_type, $recursive=false, $publish=false )
    {
        /*
         * Determine whether or not the database ID has been set yet.
         *
         * Because this function writes the WordPress post ID back to the
         * object upon save, if it is a new object, there would be no ID at
         * this point.
         *
         * @todo refactor to another method
         */
        $updating = (bool) $this->id;

        if ( $this->wp_post && $this->wp_post->ID ) {
            /*
             * Update the WordPress post.
             */
            $this->wp_post->ID = wp_insert_post( $this->wp_post );
        } else {
            /*
             * Create a new WordPress post.
             */
            $post_title = property_exists( $this->_meta, 'name' ) ? $this->_meta->name : null;
            $post_args = array(
                'post_type' => $post_type,
                'post_title' => $post_title
            );

            /*
             * Create new post in the WordPress database.
             */
            $post_id = wp_insert_post( $post_args );

            /*
             * Pull WP_Post object back from the database.
             */
            $this->wp_post = get_post( $post_id );
        }

        /*
         * Save children objects as well, iff we have been asked to and it's
         * possible.
         *
         * @todo refactor to another method
         */
        if ( $this->_cache && $recursive ) {
            foreach ( $this->_cache as $cache_key => $cached_object ) {
                $cached_object->parent_id = $this->id;

                if ( method_exists( $cached_object, 'save' ) ) {
                    /*
                     * The Object has a save method that will return its
                     * post_id in the database, so update the parent's tracking
                     * of this child by ID.
                     */
                    $this->_meta->{$cache_key . '_id'} = $cached_object->save();
                }
            }
        }

        /*
         * Update or create new meta data
         *
         * @todo refactor to another method
         */
        foreach ( $this->_meta_keys as $meta_key )
            if ( property_exists( $this->_meta, $meta_key ) && ! empty( $this->_meta->{$meta_key} ) )
                update_post_meta( $this->wp_post->ID, $meta_key, $this->_meta->{$meta_key} );

        /*
         * Attempt to post a BuddyPress Activity update, if possible and
         * warranted
         */
        $this->bp_activity_maybe_update( $post_type, $updating );

        return $this->id;
    }
    // }}}

    public function matches_search_query( $q ) {
        return False;
    }

    // {{{ get_meta_keys
    /**
     * Return the metadata keys that can be retrieved from this class.
     *
     * @since   0.1.1
     * @access  public
     * @return  array   Returns array of meta keys for this class.
     */
    public function get_meta_keys()
    {
        return $this->_meta_keys ? $this->_meta_keys : array();
    }
    // }}}

    // {{{ load
    /**
     * Load WordPress post object from database.
     *
     * @since   0.0.1
     * @access  public
     * @param int     $post_id (optional) Post ID to load.
     * @return  object  Returns current object instantiation.
     */
    public function load( $post_id=null )
    {
        $this->wp_post = $post_id ? get_post( $post_id ) : get_post( $this->wp_post->ID );
        return $this;
    }
    // }}}

    // {{{ flush_cache
    /**
     * Flush object cache.
     *
     * @since   0.0.1
     * @access  public
     */
    public function flush_cache()
    {
        unset( $this->_cache );
        $this->_cache = new \stdClass;
    }
    // }}}

    // {{{ BuddyPress integrations
    /**
     * Returns whether BuddyPress is active in the current environment.
     *
     * @since   0.1.0
     * @return  bool    True if BuddyPress is active, false if it is not.
     */
    public static function buddypress_active()
    {
        return function_exists( '\bp_is_active' );
    }

    /**
     * Add a BuddyPress Activity about the fossil save, if you can and should.
     *
     * If BuddyPress is not enabled or found, this function will return false.
     *
     * @since   0.1.0
     * @param string  $post_type BuddyPress Activity type prefix (typically WordPress post_type)
     * @param bool    $updating  (optional)    Whether the Object that the Activity is about is being updated (versus created, or commented, or deleted). Default false.
     * @return  bool|int                        Returns Activity ID on success, false on failure.
     */
    public function bp_activity_maybe_update( $post_type, $updating=false )
    {
        $activity_id = (int) 0;
        $updated = (bool) ( $updating && $this->_history );
        $created = (bool) ( ! $updating );

        /*
         * Only say that something was created if it's a Fossil, otherwise say
         * it was updated.
         *
         * Because Fossil objects hold all other objects, it makes more sense
         * to say that a Fossil was updated when a child is created on that
         * Fossil, rather than saying that something new altogether was
         * created.
         */
        if ( $created && $post_type !== Fossil::POST_TYPE ) {
            return false;
        }

        /*
         * Continue only if:
         *   - BuddyPress is enabled
         *   - We're updating something and it has changed OR we're creating
         *     something new (i.e. a new object in the database)
         */
        if ( self::buddypress_active() && ( $updated || $created ) ) {
            $bp_activity_type = $post_type . ( $updated ? '_updated' : '_created' );

            /*
             * Configure and add Activity.
             *
             * @see {@link http://goo.gl/COJIR0}
             */
            $args = array(
                'component' => self::BP_COMPONENT_ID,
                'item_id' => $this->id,
                'user_id' => \bp_loggedin_user_id(),
                'content' => json_encode( array( 'post_type' => $post_type,
                        'changeset' => $this->_history ), JSON_UNESCAPED_UNICODE ),
                'secondary_item_id' => $this->parent_id ? $this->parent_id : $this->wp_post->post_author,
                'type' => $bp_activity_type
            );
            $activity_id = \bp_activity_add( $args );

            if ( $activity_id > 0 ) {
                if ( ! empty( $this->comment ) ) {
                    $comment_id = \bp_activity_new_comment(
                        array(
                            'activity_id' => $activity_id,
                            'content' => $this->comment
                        )
                    );
                }
            }

            return $activity_id;
        }

        // If we made it this far, we didn't update.
        return false;
    }

    /**
     * Callback to register BuddyPress activity types.
     *
     * This is meant to be called from the children of this class.
     *
     * @since   0.1.0
     * @param string  $post_type BuddyPress Activity type prefix (typically WordPres post_type)
     * @return  bool                Returns false if BuddyPress is not available, otherwise true.
     */
    public static function register_buddypress_activities( $post_type )
    {
        // Bail if buddypress doesn't exist or have activity enabled
        if ( ! self::buddypress_active() )
            return false;

        $activity_actions = array( 'updated', 'comment', 'deleted', 'created' );
        foreach ( $activity_actions as $action ) {
            /*
             * Define parameters for defining an Activity action.
             *
             * From the BuddyPress documentation:
             *
             *   @param string $component_id The unique string ID of the component.
             *   @param string $type The action type.
             *   @param string $description The action description.
             *   @param callable $format_callback Callback for formatting the action string.
             *   @param string $label String to describe this action in the activity stream
             *          filter dropdown.
             *   @param array $context Activity stream contexts where the filter should appear.
             *          'activity', 'member', 'member_groups', 'group'
             *   @return bool False if any param is empty, otherwise true.
             *
             */
            $component_id = self::BP_COMPONENT_ID;
            $type         = sprintf( '%s_%s', $post_type, $action );
            $description  = sprintf( '%s %s', $post_type, $action );
            $format_cb    = sprintf( "%s::bp_format_activity", \get_called_class() );
            $label        = $post_type;
            $context      = array( 'myfossil' );

            \bp_activity_set_action( $component_id, $type, $description,
                $format_cb, $label, $context );
        }

        return true;
    }

    /**
     * BuddyPress Activity format callback function.
     *
     * Given an Activity action (e.g. "John created a new fossil") and the
     * BuddyPress Activity object itself, this returns formatted version of
     * the components of the Activity.
     *
     * @since   0.1.1
     * @param string  $action
     * @param object  $activity
     * @return  string  Formatted Action string with filter
     */
    public static function bp_format_activity( $action, $activity )
    {
        $initiator_link = \bp_core_get_userlink( $activity->user_id );

        /*
         * $activity->type is basically ${post_type}_${action}, so we can
         * explode the $activity->type string into the post type and the action
         * performed on the underscore character.
         */
        $verbs = explode( '_', $activity->type );
        $verb = end( $verbs ) == 'comment' ? 'commented on' : end( $verbs );

        /*
         * For Fossil objects, the item_id is the fossil id, however for all
         * other objects, the item_id is the object's id and the
         * secondary_item_id is the fossil's id.
         */
        if ( $activity->type === 'myfossil_fossil_comment' || $activity->type === 'myfossil_fossil_created' || $activity->type === 'myfossil_fossil_updated') {
            $fossil_link = sprintf( '<a href="/fossils/%d">Fossil #%06d</a>', $activity->item_id, $activity->item_id );
            $owner_link = ( $activity->user_id == $activity->secondary_item_id ) ? 'their own' : sprintf( "%s's", \bp_core_get_userlink( $activity->secondary_item_id ) );
            if ( $owner_link == 'their own' && $verb == 'created' ) $owner_link = 'a';
        } else {
            $fossil_link = sprintf( '<a href="/fossils/%d">Fossil #%06d</a>', $activity->secondary_item_id, $activity->secondary_item_id );
            $fossil = new Fossil( $activity->secondary_item_id );
            $owner_link = ( $activity->user_id == $fossil->post_author ) ? 'their own' : sprintf( "%s's", \bp_core_get_userlink( $activity->secondary_item_id ) );
            if ( $owner_link == 'their own' && $verb == 'created' )
                $owner_link = 'a';
        }

        $action = sprintf( '%s %s %s %s', $initiator_link, $verb,
            $owner_link, $fossil_link );

        return apply_filters( 'bp_myfossil_activity_' . $activity->type .
            '_format', $action, $activity );
    }

    public static function bp_format_activity_json( $json, $tpl )
    {
        return json_encode( $json );
    }

    // }}}

}

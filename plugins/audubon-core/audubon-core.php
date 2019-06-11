<?php

/**
 * Audubon Core Plugin
 *
 * @package myFOSSIL
 */

/**
 * Plugin Name: Audubon Core
 * Plugin URI:  https://myfossil.org
 * Description: Audubon Core Plugin allows for the uploading, downloading, and viewing of fossil media in the form of images (jpg|png), and 3d images (stl). It also allows the media's owner to associate metadata with the media based on Audubon Core standards.
 * Author:      Atmosphere Apps
 * Author URI:  https://atmosphereapps.com
 * Version:     0.1.0
 * Text Domain: myFOSSIL
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $audubon_core_db_version;
$audubon_core_db_version = '1.0';

if ( !class_exists( 'audubon_core' ) ) :
/**
 * Main audubon_core Class
 */
final class audubon_core {
	/** Singleton *************************************************************/

	/**
	 * Main Audubon Core Instance
	 *
	 * Insures that only one instance of Audubon Core exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new audubon_core;
			$instance->setup();

		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent audubon_core from being loaded more than once.
	 *
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent audubon_core from being cloned
	 *
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'audubon_core' ), '2.1' ); }

	/**
	 * A dummy magic method to prevent audubon_core from being unserialized
	 *
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'audubon_core' ), '2.1' ); }

	/**
	 * Magic method to prevent notices and errors from invalid method calls
	 *
	 */
	public function __call( $name = '', $args = array() ) { unset( $name, $args ); return null; }

	/** Private Methods *******************************************************/

	/**
	 * Setup the default hooks and actions
	 *
	 */
	private function setup() {
	    define( 'BP_MYFS_COLLECTION_DIR', dirname( __FILE__ ) );

		/**
		 * The code that defines the custom objects to be used.
		 */
		require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/AudubonCoreMedia.php';

		/**
		 * Functions require to interact with other plugin and wordpress core
		 */
		//require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/bp-support.php';

		//hooks
		add_action('wp_head', array( $this, 'audubon_core_ajaxurl' ) );					//define ajaxurl for frontend calls
		add_action( 'admin_menu', array($this, 'audubon_core_admin_settings_menu' ) );	//set up admin menu for managing plugin
		add_filter( 'wp_check_filetype_and_ext', array($this, 'allow_stls'), 100, 4 );	//allows STL files to be uploaded

		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_filter( 'single_template', array( $this, 'audubon_core_single_templates' ) );				//override template for single posts for audubon core posts
		add_filter( 'archive_template', array( $this, 'audubon_core_archive_templates' ) );				//override template for archives for audubon core
		add_filter( 'page_template', array( $this, 'audubon_core_page_templates' ) );					//override template for pages for audubon core

		add_action( 'wp_enqueue_scripts', array( $this, 'audubon_core_scripts' ) );						//frontend scripts & styles
		add_action( 'admin_enqueue_scripts', array( $this, 'audubon_core_admin_scripts' ) );			//admin scripts & styles

        add_action( 'admin_post_ac_update_classes', array( $this, 'process_ac_update_classes' ) ); 	//updates classes from admin console
        add_action( 'admin_post_ac_update_terms', array( $this, 'process_ac_update_terms' ) ); 		//updates terms from admin console

		add_action( 'bp_setup_nav', array( $this, 'bp_add_ac_nav_items' ) );						//add navigation items to buddypress

		add_action( 'pre_get_posts', array( $this, 'wpd_testimonials_query' ) );					//change the number of posts returned by default on the archive page

        add_action( 'wp_ajax_upload_ac_media', array( $this, 'upload_ac_media' ) ); 					//processes media upload
        add_action( 'wp_ajax_nopriv_upload_ac_media', array( $this, 'upload_ac_media' ) ); 				//processes media upload

        add_action( 'wp_ajax_upload_ac_media_url', array( $this, 'upload_ac_media_url' ) ); 			//processes media url upload
        add_action( 'wp_ajax_nopriv_upload_ac_media_url', array( $this, 'upload_ac_media_url' ) ); 		//processes media url upload

        add_action( 'wp_ajax_update_ac_media', array( $this, 'update_ac_media' ) ); 					//processes media update
        add_action( 'wp_ajax_nopriv_update_ac_media', array( $this, 'update_ac_media' ) ); 				//processes media update

        add_action( 'wp_ajax_delete_ac_media', array( $this, 'delete_ac_media' ) ); 					//processes media deletion
        add_action( 'wp_ajax_nopriv_delete_ac_media', array( $this, 'delete_ac_media' ) ); 				//processes media deletion

        add_action( 'wp_ajax_upload_ac_media_thumb', array( $this, 'upload_ac_media_thumb' ) ); 		//processes media thumbnail upload
        add_action( 'wp_ajax_nopriv_upload_ac_media_thumb', array( $this, 'upload_ac_media_thumb' ) ); 	//processes media thumbnail upload
	}

	/** Public Methods *******************************************************/
	function wpd_testimonials_query( $query ){
		if( ! is_admin() && $query->is_post_type_archive( 'ac_media' ) && $query->is_main_query() ) {
			$query->set( 'posts_per_page', 12 );
		}
	}

    public function register_custom_post_type() {
        $labels = array(
            'name'                => __( 'AC Media', 'audubon-core' ),
            'singular_name'       => __( 'AC Media', 'audubon-core' ),
            'menu_name'           => __( 'AC Media', 'audubon-core' ),
            'parent_item_colon'   => __( 'Parent AC Media:', 'audubon-core' ),
            'all_items'           => __( 'AC Media', 'audubon-core' ),
            'view_item'           => __( 'View AC Media', 'audubon-core' ),
            'add_new_item'        => __( 'Add New AC Media', 'audubon-core' ),
            'add_new'             => __( 'Add New', 'audubon-core' ),
            'edit_item'           => __( 'Edit AC Media', 'audubon-core' ),
            'update_item'         => __( 'Update AC Media', 'audubon-core' ),
            'search_items'        => __( 'Search AC Media', 'audubon-core' ),
            'not_found'           => __( 'AC Media not found', 'audubon-core' ),
            'not_found_in_trash'  => __( 'AC Media not found in Trash', 'audubon-core' ),
        );

        $args = array(
            'label'               => __( AudubonCoreMedia::POST_TYPE, 'audubon-core' ),
            'description'         => __( 'Represents a Audubon Core Media', 'audubon-core' ),
            'labels'              => $labels,
            'supports'            => array( 'title', 'editor', 'author',
                'thumbnail', 'custom-fields', 'comments', 'post-formats' ),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 27,
            'can_export'          => true,
            'has_archive'         => true,
            'rewrite'             => array(
                'slug' => 'ac-media',
                'with_front' => false,
                'feed' => true,
                'pages' => true
            ),
            'exclude_from_search' => true,
            'publicly_queryable'  => true,
            'capability_type'     => 'post',
            'menu_icon'           => 'dashicons-media-document'
        );

        register_post_type( AudubonCoreMedia::POST_TYPE, $args );
    }

	function audubon_core_single_templates($single) {
	    global $wp_query, $post;

	    /* Checks for single template by post type */
	    if ($post->post_type == AudubonCoreMedia::POST_TYPE) {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/single-ac-media.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/single-ac-media.php';
	        }
	    }
	    return $single;
	}


	function audubon_core_archive_templates($archive) {
	    global $wp_query, $post;

	    /* Checks for single template by post type */
	    if ($wp_query->query_vars['post_type'] == AudubonCoreMedia::POST_TYPE) {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/archive-ac-media.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/archive-ac-media.php';
	        }
	    }
	    return $archive;
	}

	function audubon_core_page_templates($archive) {
	    global $wp_query, $post;
	    /* Checks for single template by post type */
	    if ($wp_query->query_vars['pagename'] == 'audubon-core-wizard') {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/audubon-core-wizard.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/audubon-core-wizard.php';
	        }
	    } elseif ($wp_query->query_vars['pagename'] == '3d-gallery') {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/three-d-gallery.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/three-d-gallery.php';
	        }
	    }
	    return $archive;
	}

	/**
	 * Creates an Audubon Core Media post
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_parent	ID of Darwin Core Specimen if applicable
	 *
	 * @return int|WP_Error Post ID on success, WP_Error on failure
	 */
	public function create_ac_media( $post_parent ) {

		if ( $post_parent == 0 && class_exists( 'darwin_core' ) ) {
			$post_parent = darwin_core::instance()->create_dwc_specimen( 'publish' );
		}

		$args = array(
	        'post_title' => '',
	        'post_parent' => $post_parent,
	        'post_status' => 'publish',
	        'post_type' => AudubonCoreMedia::POST_TYPE
	    );

	    $id = wp_insert_post( $args );

		$update_args = array(
			'ID'           => $id,
			'post_title'   => 'Media ' . $id
		);
		wp_update_post( $update_args );

		return $id;
	}

	public function process_media_upload($filenames, $post_parent) {
		$id = self::create_ac_media( $post_parent );

		$attach_id = media_handle_upload( $filenames, $id );

		$media = get_post( $attach_id );
		$resource_url = $media->guid;

		preg_match( "#\.([A-Za-z]+)$#", $resource_url, $matches );
		update_post_meta( $id, 'resource_ext', strtolower($matches[1]) );
		update_post_meta( $id, 'resource_url', preg_replace('#^https?:#', '', $resource_url ) );

		return $id;
	}

	public function process_media_url_upload($resource_url, $post_parent) {
		$id = self::create_ac_media( $post_parent );

		$resource_url = preg_replace("#\?.*$#", "", $resource_url);
		$resource_url = preg_replace("#www.dropbox.com#", "dl.dropboxusercontent.com", $resource_url);
		$resource_url = preg_replace("#dropbox.com#", "dl.dropboxusercontent.com", $resource_url);

		preg_match( "#\.([A-Za-z]+)$#", $resource_url, $matches );
		update_post_meta( $id, 'resource_ext', strtolower($matches[1]) );
		update_post_meta( $id, 'resource_url', preg_replace('#^https?:#', '', $resource_url ) );

		return $id;
	}

	/**
	 * Processes the upload of Audubon Core Media
	 *
	 * @since 1.0.0
	 */
	public function upload_ac_media() {

		if ( !wp_verify_nonce( $_POST['upload_ac_media_nonce'], 'upload_ac_media' ) ) {
			wp_send_json_error( new WP_Error( 'upload_ac_media_nonce_error', __( "Shenanigans are afoot. Bailing on upload..." ) ), 401 );
		}

		if ($_FILES['ac_media_file']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error( new WP_Error( 'upload_ac_media_file_error', __( "There was a problem with the selected file." ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_ac_media_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$id = self::process_media_upload( 'ac_media_file', 0 );

		if ( !$id )
			wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_file_error', __( "There was a problem with the selected file." ) ), 400 );

		wp_send_json_success( $id , 201 );
	}

	/**
	 * Processes the upload of Audubon Core Media URL
	 *
	 * @since 1.0.0
	 */
	public function upload_ac_media_url() {

		if ( !wp_verify_nonce( $_POST['upload_ac_media_url_nonce'], 'upload_ac_media_url' ) ) {
			wp_send_json_error( new WP_Error( 'upload_ac_media_url_nonce_error', __( "Shenanigans are afoot. Bailing on upload..." ) ), 401 );
		}

		if ( empty( $_POST['media_url'] ) || !filter_var( $_POST['media_url'], FILTER_VALIDATE_URL ) ) {
			wp_send_json_error(new WP_Error( 'upload_ac_media_url_malformed_url_error', __( $_POST['media_url'] ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_ac_media_url_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$id = self::process_media_url_upload( $_POST['media_url'], 0 );

    	if ( !$id )
			wp_send_json_error( new WP_Error( 'upload_ac_media_url_error', __( "There was a problem with saving the URL." ) ), 400 );

		wp_send_json_success( $id , 201 );
	}

	/**
	 * Processes the upload of Audubon Core Media URL
	 *
	 * @since 1.0.0
	 */
	public function upload_ac_media_thumb() {

		if ( !wp_verify_nonce( $_POST['upload_ac_media_thumb_nonce'], 'upload_ac_media_thumb' ) ) {
			wp_send_json_error( new WP_Error( 'upload_ac_media_thumb_nonce_error', __( $_POST['upload_ac_media_thumb_nonce'] ) ), 401 );
		}

		if ($_FILES['ac_media_file_thumb']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error( new WP_Error( 'upload_ac_media_thumb_file_error', __( "There was a problem with the selected file." ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'update_ac_media_thumb_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$post = get_post( $_POST['ac_media_id'] );

		if ( $post->post_type !== 'ac_media') {
    		wp_send_json_error( new WP_Error( 'update_ac_media_thumb_post_type_error', __( "That isn't an Audubon Core Media..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'update_ac_media_thumb_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

    	if ( $attach_id = media_handle_upload( 'ac_media_file_thumb', 0 ) ) {
			update_post_meta( $post->ID, 'thumb_id', $attach_id );
    	}
		else {
    		wp_send_json_error( new WP_Error( 'update_ac_media_thumb_failure_error', __( "Something went wrong saving the thumbnail." ) ), 400 );
		}

		wp_send_json_success( $id , 201 );
	}

	/**
	 * Processes the update of an Audubon Core Media
	 *
	 * @since 1.0.0
	 */
    public function update_ac_media() {

		if ( !wp_verify_nonce( $_POST['update_ac_media_nonce'], 'update_ac_media' ) ) {
			wp_send_json_error( new WP_Error( 'update_ac_media_nonce_error', __( "Shenanigans are afoot. Bailing on update..." ) ), 401 );
		}

		$post = get_post( $_POST['ac_media_id'] );

		if ( $post->post_type !== 'ac_media') {
    		wp_send_json_error( new WP_Error( 'update_ac_media_post_type_error', __( "That isn't an Audubon Core Media..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'update_ac_media_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$ac_title = empty($_POST['post_title'] ) ? 'Media '.$post->ID : $_POST['post_title'];

		$args = array(
			'ID'           	=> $post->ID,
			'post_title'   	=> $ac_title,
			'post_name'		=> $post->ID
		);
		wp_update_post( $args);

        $media = new AudubonCoreMedia( $post->ID );
        $media->save($_POST);

		wp_send_json_success( $post->ID , 200 );
    }

	/**
	 * Processes the deletion of an Audubon Core Media
	 *
	 * @since 1.0.0
	 */
	public function delete_ac_media() {
		if ( !wp_verify_nonce( $_POST['delete_ac_media_nonce'], 'delete_ac_media' ) ) {
			wp_send_json_error( new WP_Error( 'delete_ac_media_nonce_error', __( "Shenanigans are afoot. Bailing..." ) ), 401 );
		}

		$post = get_post( $_POST['ac_media_id'] );

		if ( $post->post_type !== 'ac_media') {
    		wp_send_json_error( new WP_Error( 'delete_ac_media_post_type_error', __( "That isn't a Audubon Core Media..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) ) {
    		wp_send_json_error( new WP_Error( 'delete_ac_media_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

    	$args = array(
			'post_parent' => $post->ID,
			'post_type' => 'any'
		);
		$children = get_children( $args );

		if ( wp_delete_post( $post->ID ) ) {

			foreach ($children as $child) {
				wp_delete_attachment( $child->ID  );
			}

		} else {
    		wp_send_json_error( new WP_Error( 'delete_ac_media_failure_error', __( "Something went wrong during deletion." ) ), 400 );
		}

		wp_send_json_success( $post->ID , 200 );
	}

	public static function display_dwc_associated_media($dwc_id) {
	?>
	<div id="audubon-core">
		<?php
		$args = array(
			'post_parent' => $dwc_id,
			'post_type' => 'ac_media'
		);
		$children = get_children($args);

		if ( count($children) > 0 ) {
			echo '<div class="slick-holder">';
			foreach ($children as $child) {

				echo "<div class='ac-wrapper'>";
				audubon_core::display_ac_media("main", $child, '320');

				echo "<p><a href='/ac-media/" . $child->ID . "'>View " . $child->post_title . "</a></p></div><!-- .ac-wrapper -->";
			}
			echo "</div><!-- .slick-holder -->";
		}
		?>
	</div><!-- #audubon-core -->
	<?php
	}

	public static function get_image_summary_html($dwc_id) {
		$rval = "";
		$args = array(
			'post_parent' => $dwc_id,
			'post_type' => 'ac_media'
		);
		$children = get_children($args);

		if ( count($children) > 0 ) {
			foreach ($children as $ac_media) {
				$rval .= "<div class='ac-wrapper'>";
				$thumb_id = get_post_meta( $ac_media->ID, 'thumb_id', true );
				$media_url = get_post_meta( $ac_media->ID, 'resource_url', true );
				$media_ext = get_post_meta( $ac_media->ID, 'resource_ext', true );

				if ($media_ext == 'stl') {
					if ($thumb_id != "" && $thumb_id != NULL) {
						$rval .= "<div class='crop'><img src='" . wp_get_attachment_image_src( $thumb_id, 'thumbnail' )[0] . "' class='stl-thumbnail' /></div>";
					} else {
						$rval .= "<div class='crop'><img src='/wp-content/uploads/2017/06/stl-no-thumb.png' class='stl-thumbnail' /></div>";
					}
				} else {
					$args = array(
						'post_parent' => $ac_media->ID,
						'post_type' => 'attachment'
					);
					$attachments = get_children($args);
					$media = array_shift( $attachments );
					if ( !is_null( $media ) ) {
						$rval .= "<div class='crop'>".wp_get_attachment_image( $media->ID, 'thumbnail', false, array('class'=>'wp-image-'.$media->ID) )."</div>";
					}
				}
				$rval .= "<p><a href='/ac-media/" . $ac_media->ID . "'>View " . $ac_media->post_title . "</a></p></div><!-- .ac-wrapper -->";
			}
		}

		return $rval;
	}

	public static function display_ac_media($context, $ac_media, $size) { //context should be "thumb" or "main"
		$thumb_id = get_post_meta( $ac_media->ID, 'thumb_id', true );
		$media_url = get_post_meta( $ac_media->ID, 'resource_url', true );
		$media_ext = get_post_meta( $ac_media->ID, 'resource_ext', true );

		if ($context === "thumb") {
			if ($media_ext == 'stl') {
				if ($thumb_id != "" && $thumb_id != NULL) {
					echo "<div class='crop'><img src='" . wp_get_attachment_image_src( $thumb_id, 'thumbnail' )[0] . "' class='stl-thumbnail' /></div>";
				} else {
					echo "<div class='crop'><img src='/wp-content/uploads/2017/06/stl-no-thumb.png' class='stl-thumbnail' /></div>";
				}
			} else {
				$args = array(
					'post_parent' => $ac_media->ID,
					'post_type' => 'attachment'
				);
				$attachments = get_children($args);
				$media = array_shift( $attachments );
				if ( !is_null( $media ) ) {
					echo "<div class='crop'>".wp_get_attachment_image( $media->ID, 'thumbnail', false, array('class'=>'wp-image-'.$media->ID) )."</div>";
				}
			}
		} else if ($context === "main") {
			if ($media_ext == 'stl') {
				echo do_shortcode('[canvasio3D width="'.$size.'" height="'.$size.'" border="1" borderCol="#F6F6F6" dropShadow="0" backCol="#000000" backImg="..." mouse="on" rollMode="off" rollSpeedH="0" rollSpeedV="0" objPath="' . $media_url . '" objScale="1.0" objColor="#777777" lightSet="7" reflection="off" refVal="5" objShadow="off" floor="off" floorHeight="42" lightRotate="off" Help="off"] [/canvasio3D]');
			} else {
				$args = array(
					'post_parent' => $ac_media->ID,
					'post_type' => 'attachment'
				);
				$attachments = get_children($args);
				$media = array_shift( $attachments );
				if ( !is_null( $media ) ) {
					echo "<a href='".$media_url."'>".wp_get_attachment_image( $media->ID, array($size,$size), false, array('class'=>'wp-image-'.$media->ID) )."</a>";
				}
			}
		}
	}
/*
	public function display_dwc_media_wizard($dwc, $owner) {
	?>
	<div id="step-1-wrapper">
		<p>The only requirement for starting an entry is an image (or 3d image) of the specimen. You can upload files of type .jpg|.png|.gif or .stl (3d images).</p>
		<p>If you are already hosting the file on another site (for example dropbox) you can provide the URL instead of uploading. Please be aware that the URL must be a direct hotlink to the media or it may not display properly later.</p>
		<div id="ac-dragndrop"><p>Drag &amp; Drop File Here</p></div>
		<form>
		<?php wp_nonce_field('ac_media_ajax_upload', 'ac_media_ajax_upload_nonce'); ?>
				<input type="hidden" id="ac_media_action" name="ac_media_action" value="ac_media_upload_dwc_wizard" />
		</form>
		<br><br>
		<div id="status1"></div>
		<div class="row">
			<div class="col-xs-12">
				<button type="button" id="step-1-continue" href="#" class="btn btn-success" disabled>Continue</button>
			</div>
		</div>
	</div>
	<?php
	}
*/
	public function allow_stls($filetype_ext_data, $file, $filename, $mimes) {
		if ( substr($filename, -4) === '.stl' ) {
			$filetype_ext_data['ext'] = 'stl';
			$filetype_ext_data['type'] = 'application/octet-stream';
		}
		return $filetype_ext_data;
	}

	public function audubon_core_admin_settings_menu() {
		//add_menu_page( 'My Top Level Menu Example', 'Top Level Menu', 'manage_options', 'myplugin/myplugin-admin-page.php', 'myplguin_admin_page', 'dashicons-tickets', 6  );
		add_options_page( 'Audubon Core Options', 'Audubon Core', 'manage_options', 'audubon-core-terms', array($this, 'audubon_core_options' ) );
	}

	public function audubon_core_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		global $wpdb;
        $classes_table_name = $wpdb->prefix . 'audubon_core_classes';
        $terms_table_name = $wpdb->prefix . 'audubon_core_terms';
        $vocabulary_table_name = $wpdb->prefix . 'audubon_core_vocabulary';

        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'first';
        echo "<h1>Audubon Core Settings</h1>";
		$this->options_page_tabs($tab);

		$org = array();
		$class_layout = array();

        $classes = $wpdb->get_results( "SELECT " .
            $classes_table_name . ".classID, " .
            $classes_table_name . ".className, " .
            $classes_table_name . ".displayName AS 'classDisplayName', " .
            $classes_table_name . ".layout FROM " .
            $classes_table_name . " ORDER BY " .
            $classes_table_name . ".classID ASC;", 'ARRAY_A' );

        foreach ($classes as $current) {
        	$org[$current['classID']]['parent'] = $current;
        }

		if($tab == 'first' ) {
			$class_layout = array();
			$class_layout[1] = array();
			$class_layout[2] = array();
        	$flag = true;

		    echo "<form method='post' action='".esc_url( admin_url('admin-post.php') )."'>
	        	<input type='hidden' name='action' value='ac_update_classes' />";
		    wp_nonce_field('ac_update_classes_post', 'ac_update_classes_nonce');
	        echo "<table id='ac-classes' class='ac-classes'><tbody>
	        	<tr><th class='ac-class-heading'><h1>Classes</h1></th></tr>
    			<tr><th>Class Name</th><th>Display Name</th></tr>";
	        foreach ($classes as $current) {
	        	$matrix = explode(',', $current['layout']);
	        	if ($matrix[0] == 0)
		        	$class_layout[$matrix[0]][(count($class_layout[0])+1)] = $current;
		        else
		        	$class_layout[$matrix[0]][$matrix[1]] = $current;

	        	echo "<tr class='".(($flag = !$flag) ? 'row-even' : 'row-odd')."'>
	        	<td><input type='text' name='className[".$current['classID']."]' value='".$current['className']."' /></td>
	        	<td><input type='text' name='displayName[".$current['classID']."]' value='".$current['classDisplayName']."' /></td>
	        	</tr>";
	        }
        	echo "<tr class='".(($flag = !$flag) ? 'row-even' : 'row-odd')."'>
	        	<td><input type='text' name='className[0]' value='' placeholder='Add Class Name' /></td>
	        	<td><input type='text' name='displayName[0]' value='' placeholder='Add Display Name' /></td>
	        	</tr>";

	        echo "</tbody></table><h1>Layout</h1>";

	        ksort($class_layout);
	        ksort($class_layout[1]);
	        ksort($class_layout[2]);
	        for($col_key=1;$col_key<=2;$col_key++) { //$class_layout as $col_key => $current
	        	$current = $class_layout[$col_key];
	        	echo "<div class='class-sort-container'>";
	        	echo "<h3>Column ".$col_key."</h3>";
	        	echo "<ul id='col-".$col_key."' class='class-sort'>";
	        	foreach ($current as $row_key =>$selection) {
		        	echo "<li id='row-".$row_key."'>".$selection['classDisplayName']."<input class='layout' type='hidden' name='layout[".$selection['classID']."]' value='".$selection['layout']."' /></li>";
		        }
		        echo "</ul></div>";
	        }
	        echo "<input style='display:block;clear:both;' class='btn btn-primary' type='submit' name='update' value='Save' />
	        	</form>";
		}
		else if ($tab == 'second') {
	        $enabled_terms = $wpdb->get_results( "SELECT " .
	            $classes_table_name . ".className, " .
	            $classes_table_name . ".displayName AS 'classDisplayName', " .
	            $classes_table_name . ".layout, " .
	            $terms_table_name . ".termID, " .
	            $terms_table_name . ".termName, " .
	            $terms_table_name . ".displayName, " .
	            $terms_table_name . ".valueType, " .
	            $terms_table_name . ".enabled, " .
	            $terms_table_name . ".layoutParent, " .
	            $terms_table_name . ".layoutOrder FROM " .
	            $classes_table_name . " INNER JOIN " . $terms_table_name . " ON " .
	            $classes_table_name . ".classID=" . $terms_table_name . ".layoutParent ORDER BY " .
	            $terms_table_name . ".layoutParent ASC, " .
	            $terms_table_name . ".layoutOrder ASC;", 'ARRAY_A' );

	        foreach ($enabled_terms as $current) {
	        	$org[$current['layoutParent']]['children'][] = $current;
	        }

        	$flag = true;

		    echo "<div class='ac-admin-tab'><form method='post' action='".esc_url( admin_url('admin-post.php') )."'>
	        	<input type='hidden' name='action' value='ac_update_terms' />";
		    wp_nonce_field('ac_update_terms_post', 'ac_update_terms_nonce');

	        foreach ($org as $class_id => $values) {
	        	echo "<h1>".$values['parent']['classDisplayName']."</h1>
	        		<table id='ac-terms' class='ac-terms'><thead><tr><th>Term Name</th><th>Display Name</th><th>Value Type</th><th>Enabled</th></tr></thead><tbody id='".$class_id."'>";

	        	if (isset($values['children']))	{
		        	foreach ($values['children'] as $current) {
		        		echo "<tr class='".(($flag = !$flag) ? 'row-even' : 'row-odd')."'>
				        	<td><input type='text' name='termName[".$current['termID']."]' value='".$current['termName']."' /></td>
				        	<td><input type='text' name='displayName[".$current['termID']."]' value='".$current['displayName']."' /></td>
				        	<td>".$this->display_value_type($current)."</td>
				        	<td><input type='checkbox' name='enabled[".$current['termID']."]' value='true' ".(($current['enabled'] == true) ? 'checked' : '')." /></td>
				        	<td>
				        	<input class='layout-parent' type='hidden' name='layoutParent[".$current['termID']."]' value='".$current['layoutParent']."' />
				        	<input class='layout-order' type='hidden' name='layoutOrder[".$current['termID']."]' value='".$current['layoutOrder']."' />
				        	</td>
				        	</tr>";
		        	}
		        }
		        else {
		        	echo "<tr class='ac-term-placeholder'><td></td><td></td><td></td><td></td></tr>";
		        }

        		echo "</tbody><tfoot><tr class='".(($flag = !$flag) ? 'row-even' : 'row-odd')."'>
		        	<td><input type='text' name='termName[new_".$class_id."]' value='' placeholder='Add Term Name' /></td>
		        	<td><input type='text' name='displayName[new_".$class_id."]' value='' placeholder='Add Display Name' /></td>
		        	<td>".$this->display_value_type(array('termID' => "new_".$class_id, 'valueType' => 'text'))."</td>
		        	<td><input type='checkbox' name='enabled[new_".$class_id."]' value='true' /></td>
		        	<td>
		        	<input class='layout-parent' type='hidden' name='layoutParent[new_".$class_id."]' value='".$class_id."' />
			        <input class='new-layout-order-".$class_id."' type='hidden' name='layoutOrder[new_".$class_id."]' value='".++$current['layoutOrder']."' />
		        	</td>
		        	</tr></tfoot></table>";
	        }
			echo "<input class='btn btn-primary' type='submit' name='update' value='Save' /></form></div>";
		}
	}

	public function options_page_tabs($current = 'first') {
	    $tabs = array(
	        'first'   => __("Classes", 'plugin-textdomain'),
	        'second'  => __("Terms", 'plugin-textdomain')
	    );
	    $html =  '<h2 class="nav-tab-wrapper">';
	    foreach( $tabs as $tab => $name ){
	        $class = ($tab == $current) ? 'nav-tab-active' : '';
	        $html .=  '<a class="nav-tab ' . $class . '" href="options-general.php?page=audubon-core-terms&tab=' . $tab . '">' . $name . '</a>';
	    }
	    $html .= '</h2>';
	    echo $html;
	}

	private function display_value_type($selection) {
		$preset_types = array('text', 'select', 'boolean');

		$html = "<select name='valueType[".$selection['termID']."]'>";
		foreach ($preset_types as $current) {
			$html .= "<option value='".$current."'".(($selection['valueType'] == $current) ? ' selected': '').">".$current."</option>";
		}
		$html .= "</select>";
		return $html;
	}

	public function audubon_core_ajaxurl() {

	   echo '<script type="text/javascript">
	           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
	           var maxUploadSize = ' . wp_max_upload_size() . '
	         </script>';
	}

    public function bp_add_ac_nav_items()
    {
        global $bp;

        bp_core_new_nav_item(
            array(
                'name' => '3D',
                'slug' => 'ac_media',
                'default_subnav_slug' => 'ac_media',
                'parent_url' => bp_displayed_user_domain(),
                'parent_slug' => $bp->members->slug . bp_displayed_user_id(),
                'position' => 60,
                'show_for_displayed_user' => true,
                'screen_function' => array( $this, 'audubon_core_bp_display_collection_page')
            )
        );
    }

    public function audubon_core_bp_display_collection_page() {
	    //add title and content here - last is to call the members plugin.php template
	    add_action( 'bp_template_title', array( $this, 'audubon_core_bp_show_screen_title') );
	    add_action( 'bp_template_content', array( $this, 'audubon_core_bp_show_screen_content') );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

	function audubon_core_bp_show_screen_title() {
	    echo '3D Models Gallery';
	}

	function audubon_core_bp_show_screen_content() {
?>
		<div id="audubon-core">
			<div class="row">
				<div class="col-xs-3">
					<h2>3D</h2>
				</div>
				<div class="col-xs-9">
					<?php echo do_shortcode("[sg_popup id=2 event='click']<button class='btn btn-primary help-popup'>?</button>[/sg_popup]"); ?>
					<a id="ac-launch-wizard" href="/audubon-core-wizard" class="btn btn-primary">Add Media</a>
				</div>
			</div>
			<?php
			$query_args = array(
				'author' => bp_displayed_user_id(),
				'post_type' => 'ac_media',
				'posts_per_page' => -1,
				'post_status' => 'publish'
			);

			if (bp_displayed_user_id() == get_current_user_id())
				$query_args['post_status'] = array('publish', 'draft');

			$wp_query = new WP_Query( $query_args );

			global $post;
			$row_counter = 0;
			if ( $wp_query->have_posts() ) {

				do {
					if ($row_counter % 4 == 0)
						echo "<div class='row'>";

					$wp_query->the_post();
					echo "<div class='col-xs-12 col-sm-6 col-md-3 ac-gallery-item'><a href='" . get_post_type_archive_link(AudubonCoreMedia::POST_TYPE) . $post->ID . "'>";
					audubon_core::display_ac_media("thumb", $post, '320');
					echo $post->post_title ."</a></div>";

					if (++$row_counter % 4 == 0)
						echo "</div>";

				} while ( $wp_query->have_posts() );

				if ($row_counter % 4 != 0)
					echo "</div>";
			} else {
				?>
			  <h1>Sorry...</h1>
			  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
			  <?php
			}
?>
		</div><!-- #audubon-core -->
<?php
	}

    public function audubon_core_scripts() {
    	wp_enqueue_script( 'jquery' );
   		//wp_enqueue_style( 'bootstrap', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
    	//wp_enqueue_script( 'bootstrap-js', '//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', array('jquery'), true);
    	wp_enqueue_style( 'audubon-core-css', plugins_url( '/style.css', __FILE__ ) );
    	wp_enqueue_script( 'audubon-core-js', plugins_url( '/scripts.js', __FILE__ ), array(), '1.0.0' );
    	wp_enqueue_style( 'slick-css', plugins_url( '/slick/slick.css', __FILE__ ) );
    	wp_enqueue_style( 'slick-theme-css', plugins_url( '/slick/slick-theme.css', __FILE__ ) );
    	wp_enqueue_script( 'slick-js', plugins_url( '/slick/slick.min.js', __FILE__ ) );
    	//wp_enqueue_script( 'googlemaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyCiawlM6d6FHKwLVNKP8eaO9eaug8DUSbk', array( 'jquery' ) );
    	//wp_enqueue_script( 'popup-overlay-js', '//cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.13/jquery.popupoverlay.js', array( 'jquery' ) );
    }

    public function audubon_core_admin_scripts() {
    	wp_enqueue_style( 'audubon-core-admin-css', plugins_url( '/admin-style.css', __FILE__ ) );
    	wp_enqueue_script( 'jquery' );
    	wp_enqueue_script( 'jquery-ui-core' );
    	wp_enqueue_script( 'jquery-ui-sortable' );
    	wp_enqueue_script( 'audubon-core-admin-js', plugins_url( '/admin-scripts.js', __FILE__ ) );
    }
/*
	public function process_ac_create_media() {

		if ( wp_verify_nonce( $_POST['ac_media_create_nonce'], 'ac_media_create_post' ) ) {

			$dwc_id = (!isset($_POST['dwc_specimen_id']) || $_POST['dwc_specimen_id'] === '') ? 0 : $_POST['dwc_specimen_id'];

			if ($dwc_id == 0 && class_exists('DarwinCoreSpecimen')) {
				$dwc_defaults = array(
			        'post_title' => '',
			        'post_status' => 'draft',
			        'post_type' => DarwinCoreSpecimen::POST_TYPE
			    );

			    $dwc_id = wp_insert_post( $dwc_defaults );

				$dwc_update = array(
					'ID'           	=> $dwc_id,
					'post_title'   	=> 'Specimen ' . $dwc_id,
					'post_name'		=> $dwc_id
				);
				wp_update_post( $dwc_update );
			}

			$ac_defaults = array(
		        'post_title' => '',
		        'post_parent' => $dwc_id,
		        'post_status' => 'publish',
		        'post_type' => AudubonCoreMedia::POST_TYPE
		    );

		    $ac_id = wp_insert_post( $ac_defaults );

			$ac_update = array(
				'ID'           => $ac_id,
				'post_title'   => 'Media ' . $ac_id
			);
			wp_update_post( $ac_update );

			if ($_FILES['wp_custom_attachment']['error'] === UPLOAD_ERR_OK) {
				$attach_id = media_handle_upload( 'wp_custom_attachment', $ac_id );

				$media = get_post($attach_id);
				update_post_meta($ac_id, 'resource_url', $media->guid);

			}
			else if ($_POST['resource_url'] !== "") {
				update_post_meta($ac_id, 'resource_url', $_POST['resource_url']);
			}
	        wp_redirect(get_site_url() .  '/ac_media/' . $ac_id .'/');
		}
	}
*/

	static function audubon_core_install() {
		global $wpdb;
		global $audubon_core_db_version;

		$classes_table_name = $wpdb->prefix . 'audubon_core_classes';
		$terms_table_name = $wpdb->prefix . 'audubon_core_terms';
		$vocabulary_table_name = $wpdb->prefix . 'audubon_core_vocabulary';

		$charset_collate = $wpdb->get_charset_collate();

		$classes_sql = "CREATE TABLE $classes_table_name (
			classID mediumint(9) NOT NULL AUTO_INCREMENT,
			className varchar(55) DEFAULT '' NOT NULL,
			displayName varchar(55) DEFAULT '' NOT NULL,
			layout varchar(9) DEFAULT '0,0' NOT NULL,
			core boolean DEFAULT true NOT NULL,
			PRIMARY KEY  (classID)
		) $charset_collate;";

		$terms_sql = "CREATE TABLE $terms_table_name (
			termID mediumint(9) NOT NULL AUTO_INCREMENT,
			termName varchar(55) DEFAULT '' NOT NULL,
			displayName varchar(55) DEFAULT '' NOT NULL,
			valueType varchar(55) DEFAULT 'text' NOT NULL,
			class mediumint(9) NOT NULL,
			enabled boolean NOT NULL,
			core boolean DEFAULT true NOT NULL,
			layoutParent smallint(3) DEFAULT 0 NOT NULL,
			layoutOrder smallint(3) DEFAULT 0 NOT NULL,
			PRIMARY KEY  (termID)
		) $charset_collate;";

		$vocabulary_sql = "CREATE TABLE $vocabulary_table_name (
			vocabularyID mediumint(9) NOT NULL AUTO_INCREMENT,
			termID mediumint(9) NOT NULL,
			vocab varchar(99) DEFAULT '' NOT NULL,
			displayVocab varchar(99) DEFAULT '' NOT NULL,
			PRIMARY KEY  (vocabularyID)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $classes_sql );
		dbDelta( $terms_sql );
		dbDelta( $vocabulary_sql );

		add_option( 'audubon_core_db_version', $audubon_core_db_version );

		if ( post_exists("Audubon Core Wizard") === 0 ) {
			$post = array(
		          'comment_status' => 'closed',
		          'ping_status' =>  'closed' ,
		          'post_author' => get_current_user_id(),
		          'post_date' => current_time('Y-m-d H:i:s'),
		          'post_name' => 'Audubon Core Wizard',
		          'post_status' => 'publish' ,
		          'post_title' => 'Audubon Core Wizard',
		          'post_type' => 'page',
		    );
		    //insert page and save the id
		    $newvalue = wp_insert_post( $post, false );
		}
	}

	static function audubon_core_install_data() {
		global $wpdb;

		$classes_table_name = $wpdb->prefix . 'audubon_core_classes';
		$terms_table_name = $wpdb->prefix . 'audubon_core_terms';
		$vocabulary_table_name = $wpdb->prefix . 'audubon_core_vocabulary';

		$vocab = array();

		$input = array(
			'Management' => array('Management Vocabulary', array(
				'dcterms_identifier' => array('Identifier', 'text', false),
				'dc_type' => array('Type', 'text', false),
				'dcterms_type' => array('Type', 'text', false),
				'ac_subtypeLiteral' => array('Subtype Literal', 'text', false),
				'ac_subtype' => array('Subtype', 'text', false),
				'dcterms_title' => array('Title', 'text', false),
				'dcterms_modified' => array('Modified', 'text', false),
				'xmp_metadataDate' => array('Metadata Date', 'text', false),
				'ac_metadataLanguageLiteral' => array('Metadata Language Literal', 'text', false),
				'ac_providerManagedID' => array('Provider Managed ID', 'text', false),
				'xmp_rating' => array('Rating', 'text', false),
				'ac_commenter' => array('Commenter', 'text', false),
				'ac_commenterLiteral' => array('Commenter Literal', 'text', false),
				'ac_comments' => array('Comments', 'text', false),
				'ac_reviewer' => array('Reviewer', 'text', false),
				'ac_reviewerLiteral' => array('Reviewer Literal', 'text', false),
				'ac_reviewerComments' => array('Reviewer Comments', 'text', false),
				'dcterms_available' => array('Available', 'text', false),
				'ac_hasServiceAccessPoint' => array('Has Service Access Point', 'text', false)
				)
			),
			'Attribution' => array('Attribution', array(
				'dc_rights' => array('Rights', 'text', false),
				'dcterms_rights' => array('Rights', 'text', false),
				'xmprights_owner' => array('Owner', 'text', false),
				'xmprights_usageTerms' => array('Usage Terms', 'text', false),
				'xmprights_webStatement' => array('Web Statement', 'text', false),
				'ac_licenseLogoURL' => array('License Logo URL', 'text', false),
				'photoshop_credit' => array('Credit', 'text', false),
				'ac_attributionLogoURL' => array('Attribution Logo URL', 'text', false),
				'ac_attributionLinkURL' => array('Attribution Link URL', 'text', false),
				'ac_fundingAttribution' => array('Funding Attribution', 'text', false),
				'dc_source' => array('Source', 'text', false),
				'dcterms_source' => array('Source', 'text', false)
				)
			),
			'Agents' => array('Agents Vocabulary', array(
				'dc_creator' => array('Creator', 'text', false),
				'dcterms_creator' => array('Creator', 'text', false),
				'ac_providerLiteral' => array('Provider Literal', 'text', false),
				'ac_provider' => array('Provider', 'text', false),
				'ac_metadataProviderLiteral' => array('Metadata Provider Literal', 'text', false),
				'ac_metadataProvider' => array('Metadata Provider', 'text', false),
				'ac_metadataCreatorLiteral' => array('Metadata Creator Literal', 'text', false),
				'ac_metadataCreator' => array('Metadata Creator', 'text', false)
				)
			),
			'ContentCoverage' => array('Content Coverage Vocabulary', array(
				'dcterms_description' => array('Description', 'text', false),
				'ac_caption' => array('Caption', 'text', false),
				'dc_language' => array('Language', 'text', false),
				'dcterms_language' => array('Language', 'text', false),
				'ac_physicalSetting' => array('Physical Setting', 'text', false),
				'iptc4xmpext_cvTerm' => array('CV Term', 'text', false),
				'ac_subjectCategoryVocabulary' => array('Subject Category Vocabulary', 'text', false),
				'ac_tag' => array('Tag', 'text', false)
				)
			),
			'Geography' => array('Geography Vocabulary', array(
				'iptc4xmpext_locationShown' => array('Location Shown', 'text', false),
				'iptc4xmpext_worldRegion' => array('World Region', 'text', false),
				'iptc4xmpext_countryCode' => array('Country Code', 'text', false),
				'iptc4xmpext_countryName' => array('Country Name', 'text', false),
				'iptc4xmpext_provinceState' => array('Province State', 'text', false),
				'iptc4xmpext_city' => array('City', 'text', false),
				'iptc4xmpext_sublocation' => array('Sublocation', 'text', false)
				)
			),
			'TemporalCoverage' => array('Temporal Coverage Vocabulary', array(
				'dcterms_temporal' => array('Temporal', 'text', false),
				'xmp_createDate' => array('Create Date', 'text', false),
				'ac_timeOfDay' => array('Time of Day', 'text', false)
				)
			),
			'Taxonomic Coverage' => array('Taxonomic Coverage Vocabulary', array(
				'ac_taxonCoverage' => array('Taxon Coverage', 'text', false),
				'dwc_scientificName' => array('Scientific Name', 'text', false),
				'dwc_identificationQualifier' => array('Identification Qualifier', 'text', false),
				'dwc_vernacularName' => array('Vernacular Name', 'text', false),
				'dwc_nameAccordingTo' => array('Name According To', 'text', false),
				'dwc_scientificNameID' => array('Scientific Name ID', 'text', false),
				'dwc_otherScientificName' => array('Other Scientific Name', 'text', false),
				'dwc_identifiedBy' => array('Identified By', 'text', false),
				'dwc_dateIdentified' => array('Date Identified', 'text', false),
				'ac_taxonCount' => array('Taxon Count', 'text', false),
				'ac_subjectPart' => array('Subject Part', 'text', false),
				'dwc_sex' => array('Sex', 'text', false),
				'dwc_lifeStage' => array('Life Stage', 'text', false),
				'ac_subjectOrientation' => array('Subject Orientation', 'text', false)
				)
			),
			'ResourceCreation' => array('Resource Creation Vocabulary', array(
				'iptc4xmpext_locationCreated' => array('Location Created', 'text', false),
				'ac_digitizationDate' => array('Digitization Date', 'text', false),
				'ac_captureDevice' => array('Capture Device', 'text', false),
				'ac_resourceCreationTechnique' => array('Resource Creation Technique', 'text', false)
				)
			),
			'RelatedResources' => array('Related Resources Vocabulary', array(
				'ac_idOfContainingCollection' => array('ID of Containing Collection', 'text', false),
				'ac_relatedResourceID' => array('Related Resource ID', 'text', false),
				'ac_providerID' => array('Provider ID', 'text', false),
				'ac_derivedFrom' => array('Derived From', 'text', false),
				'ac_associatedSpecimenReference' => array('Associated Specimen Reference', 'text', false),
				'ac_associatedObservationReference' => array('Associated Observation Reference', 'text', false)
				)
			),
			'ServiceAccessPoint' => array('Service Access Point Vocabulary', array(
				'ac_accessURI' => array('Access URI', 'text', false),
				'dc_format' => array('Format', 'text', false),
				'dcterms_format' => array('Format', 'text', false),
				'ac_variantLiteral' => array('Variant Literal', 'text', false),
				'ac_variant' => array('Variant', 'text', false),
				'ac_variantDescription' => array('Variant Description', 'text', false),
				'ac_furtherInformationURL' => array('Further Information URL', 'text', false),
				'ac_licensingException' => array('Licensing Exception', 'text', false),
				'ac_serviceExpectation' => array('Service Expectation', 'text', false),
				'ac_hash_function' => array('Hash Function', 'text', false),
				'ac_hashValue' => array('Hash Value', 'text', false),
				'exif_pixelXDimension' => array('Pixel X Dimension', 'text', false),
				'exif_pixelYDimension' => array('Pixel Y Dimension', 'text', false)
				)
			)
		);

		$layout_count = 0;
		foreach ($input as $class_key => $class_value) {
			$wpdb->insert(
				$classes_table_name,
				array(
					'className' => $class_key,
					'displayName' => $class_value[0],
					'layout' => '1,' . ++$layout_count
				)
			);

			$classID = $wpdb->insert_id;

			$order_count = 0;
			foreach ($class_value[1] as $key => $value) {
				$wpdb->insert(
					$terms_table_name,
					array(
						'termName' => $key,
						'displayName' => $value[0],
						'valueType' => $value[1],
						'class' => $classID,
						'enabled' => $value[2],
						'layoutParent' => $classID,
						'layoutOrder' => ++$order_count
					)
				);

				$termID = $wpdb->insert_id;

				if (isset($vocab[$key])) {
					foreach($vocab[$key] as $vocab => $display_vocab) {
						$wpdb->insert(
							$vocabulary_table_name,
							array(
								'termID' => $termID,
								'vocab' => $vocab,
								'displayVocab' => $display_vocab
							)
						);
					}
				}
			}
		}
	}

	public function process_ac_update_classes() {
        if ( wp_verify_nonce( $_POST['ac_update_classes_nonce'], 'ac_update_classes_post' ) ) {
			global $wpdb;

	        $classes_table_name = $wpdb->prefix . 'audubon_core_classes';

        	foreach ($_POST['className'] as $id => $current) {
        		if ($id == 0) {
        			if ($_POST['className'][0] != '' && $_POST['displayName'][0] != '') {

        				$highest_layout = $wpdb->get_results("SELECT " .
        					$classes_table_name.".layout FROM " .
        					$classes_table_name." WHERE " .
        					$classes_table_name.".layout LIKE '1,%' ORDER BY " .
        					$classes_table_name.".layout DESC;", 'ARRAY_A');

        				$layout_row = 0;
        				foreach ($highest_layout as $current) {
		        			$matrix = explode(',', $current['layout']);
        					$layout_row = ($matrix[1] > $layout_row) ? $matrix[1] : $layout_row;
        				}

	        			$wpdb->insert(
							$classes_table_name,
							array(
								'className' => $_POST['className'][0],
								'displayName' => $_POST['displayName'][0],
								'layout' => (($_POST['layout'][0] != '') ? $_POST['layout'][0] : '1,'.++$layout_row),
								'core' => 0
							)
						);
	        		}
        		}
        		else {
	        		$wpdb->update( $classes_table_name,
	        			array(
		        			'className' => $current,
		        			'displayName' => $_POST['displayName'][$id],
		        			'layout' => $_POST['layout'][$id]
		        		),
		        		array(
		        			'classID' => $id
		        		),
		        		array('%s', '%s', '%s'),
		        		'%d'
		        	);
	        	}
        	}
        }
        wp_redirect(get_site_url() .  $_POST['_wp_http_referer']);
	}

	public function process_ac_update_terms() {
        if ( wp_verify_nonce( $_POST['ac_update_terms_nonce'], 'ac_update_terms_post' ) ) {
			global $wpdb;
	        $terms_table_name = $wpdb->prefix . 'audubon_core_terms';

        	foreach ($_POST['termName'] as $id => $current) {
        		if (strpos($id, 'new') !== false) {
        			if ($_POST['termName'][$id] != '' && $_POST['displayName'][$id] != '') {

        				$highest_layout = $wpdb->get_results("SELECT " .
        					$terms_table_name.".layoutOrder FROM " .
        					$terms_table_name." WHERE " .
        					$terms_table_name.".layoutParent=".$_POST['layoutParent'][$id]." ORDER BY " .
        					$terms_table_name.".layoutOrder DESC LIMIT 1;", 'ARRAY_A');

        				$wpdb->insert(
							$terms_table_name,
							array(
								'termName' => $_POST['termName'][$id],
								'displayName' => $_POST['displayName'][$id],
								'valueType' => $_POST['valueType'][$id],
								'class' => 0,
								'enabled' => ((isset($_POST['enabled'][$id]) && $_POST['enabled'][$id] == 'true') ? 1 : 0 ),
								'core' => 0,
								'layoutParent' => $_POST['layoutParent'][$id],
								'layoutOrder' => ++$highest_layout[0]['layoutOrder']
							)
						);
        			}
        		}
        		else {
	        		$wpdb->update( $terms_table_name,
	        			array(
		        			'termName' => $current,
		        			'displayName' => $_POST['displayName'][$id],
		        			'valueType' => $_POST['valueType'][$id],
		        			'layoutParent' => $_POST['layoutParent'][$id],
		        			'layoutOrder' => $_POST['layoutOrder'][$id],
		        			'enabled' => ((isset($_POST['enabled'][$id]) && $_POST['enabled'][$id] == 'true') ? 1 : 0 )
		        		),
		        		array(
		        			'termID' => $id
		        		),
		        		array('%s', '%s', '%s', '%d', '%d', '%d', '%d'),
		        		'%d'
		        	);
	        	}
        	}
        }

        wp_redirect(get_site_url() .  $_POST['_wp_http_referer']);
	}
}
global $wpdb;
$classes_table_name = $wpdb->prefix . 'audubon_core_classes';

if($wpdb->get_var("show tables like '$classes_table_name'") != $classes_table_name) {
	register_activation_hook( __FILE__, array( 'audubon_core', 'audubon_core_install' ) );
	register_activation_hook( __FILE__, array( 'audubon_core', 'audubon_core_install_data' ) );
}

/**
 * The main function responsible for returning the one true audubon_core Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mfsm = audubon_core(); ?>
 *
 * @return The one true audubon_core Instance
 */
function audubon_core() {
	return audubon_core::instance();
}

/**
 * Hook audubon_core early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before Audubon Core, to get their
 * actions, filters, and overrides setup without Audubon Core being in the way.
 */
if ( defined( 'AUDUBON_CORE_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'audubon_core', (int) AUDUBON_CORE_LATE_LOAD );

// "And now here's something we hope you'll really like!"
} else {
	audubon_core();
}

endif; // class_exists check

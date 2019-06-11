<?php

/**
 * Darwin Core Plugin
 *
 * @package myFOSSIL
 */

/**
 * Plugin Name: Darwin Core
 * Plugin URI:  https://myfossil.org
 * Description: Darwin Core Plugin allows for the digital cataloging of biological specimens
 * Author:      Atmosphere Apps
 * Author URI:  https://atmosphereapps.com
 * Version:     0.1.0
 * Text Domain: myFOSSIL
 * Domain Path: /languages/
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $darwin_core_db_version;
$darwin_core_db_version = '1.0';

if ( !class_exists( 'darwin_core' ) ) :
/**
 * Main darwin_core Class
 */
final class darwin_core {
	/** Singleton *************************************************************/

	/**
	 * Main Darwin Core Instance
	 *
	 * Insures that only one instance of Darwin Core exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 */
	public static function instance() {
		// Store the instance locally to avoid private static replication
		static $instance = null;

		// Only run these methods if they haven't been ran previously
		if ( null === $instance ) {
			$instance = new darwin_core;
			$instance->setup();

		}

		// Always return the instance
		return $instance;
	}

	/** Magic Methods *********************************************************/

	/**
	 * A dummy constructor to prevent darwin_core from being loaded more than once.
	 *
	 */
	private function __construct() { /* Do nothing here */ }

	/**
	 * A dummy magic method to prevent darwin_core from being cloned
	 *
	 */
	public function __clone() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'darwin_core' ), '1.0' ); }

	/**
	 * A dummy magic method to prevent darwin_core from being unserialized
	 *
	 */
	public function __wakeup() { _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'darwin_core' ), '1.0' ); }

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
		define( 'BP_DARWIN_CORE_DIR', dirname( __FILE__ ) );

		/**
		 * The code that defines the custom objects to be used.
		 */
		require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/DarwinCoreSpecimen.php';
	  require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/class-dwc-rest-ingestion-controller.php';
	  require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/class-dwc-rest-user-controller.php';

		/**
		 * Functions require to interact with other plugin and wordpress core
		 */
		//require_once plugin_dir_path( realpath( __FILE__ ) ) . 'includes/bp-support.php';

		//hooks

		add_action( 'wp_head', array( $this, 'darwin_core_ajaxurl' ) );					//define ajaxurl for frontend calls
		add_action( 'admin_menu', array($this, 'darwin_core_admin_settings_menu' ) );	//set up admin menu for managing plugin

		add_action( 'init', array( $this, 'register_custom_post_type' ) );
		add_filter( 'single_template', array( $this, 'darwin_core_single_templates' ) );	//override template for single posts for darwin core posts
		add_filter( 'archive_template', array( $this, 'darwin_core_archive_templates' ) );	//override template for archives for darwin core
		add_filter( 'page_template', array( $this, 'darwin_core_page_templates' ) );		//override template for pages for darwin core

		add_action( 'wp_enqueue_scripts', array( $this, 'darwin_core_scripts' ) );			//frontend scripts & styles
		add_action( 'wp_enqueue_scripts', array( $this, 'darwin_core_localize_scripts' ) );	//make variables available to front end scripts

		add_action( 'admin_enqueue_scripts', array( $this, 'darwin_core_admin_scripts' ) );	//admin scripts & styles

    add_action( 'admin_post_dwc_update_classes', array( $this, 'process_dwc_update_classes' ) );	//updates classes from admin console
    add_action( 'admin_post_dwc_update_terms', array( $this, 'process_dwc_update_terms' ) );		//updates terms from admin console

		add_action( 'wp_ajax_ingest_myfossil_specimens', array( $this, 'ingest_myfossil_specimens' ) ); 									//1. processes specimen update
		add_action( 'wp_ajax_historic_bp_activity', array( $this, 'create_historic_bp_activity' ) );
		add_action( 'wp_ajax_publish_image_specimens', array( $this, 'publish_image_specimens' ) );
		add_action( 'wp_ajax_set_specimen_post_content', array( $this, 'set_specimen_post_content' ) );

		add_action( 'bp_setup_nav', array( $this, 'bp_add_dwc_nav_items' ) );	//add navigation items to buddypress

		/*
		 *  filters modifying entries that are specific to myfossil. These should be broken out at some point
		 */
		add_filter( 'darwin-core-add-Taxon-helper', array($this, 'add_taxon_helper' ), 10, 2 );
		//add_filter( 'darwin-core-add-Location-helper', array($this, 'add_location_helper' ), 10, 2 );
		add_filter( 'darwin-core-add-GeologicalContext-helper', array($this, 'add_geologicalContext_helper' ), 10, 2 );

		add_filter( 'darwin-core-alter-owner-Location-html', array($this, 'alter_owner_location_html' ), 10, 3 );
		add_filter( 'darwin-core-alter-Location-html', array($this, 'alter_location_html' ), 10, 3 );

		add_filter( 'dwc-specimen-edit-term-html', array($this, 'term_occurrenceID_edit_html' ), 10, 7 );

		add_filter( 'myfs-api-specimen-get-schema', array($this, 'alter_myfs_api_specimen_get_schema' ) );
		add_filter( 'darwin-core-json-meta-term', array($this, 'alter_darwin_core_json_meta_term' ), 10, 3 );
		add_filter( 'darwin-core-hide-admin-classes', array($this, 'hide_admin_classes' ) );

    add_action( 'wp_ajax_update_dwc_specimen', array( $this, 'update_dwc_specimen' ) ); 									//1. processes specimen update
    add_action( 'wp_ajax_nopriv_update_dwc_specimen', array( $this, 'update_dwc_specimen' ) ); 								//1. processes specimen update

    add_action( 'wp_ajax_dwc_specimen_upload_media', array( $this, 'dwc_specimen_upload_media' ) );
    add_action( 'wp_ajax_nopriv_dwc_specimen_upload_media', array( $this, 'dwc_specimen_upload_media' ) );

    add_action( 'wp_ajax_dwc_specimen_upload_terms', array( $this, 'dwc_specimen_upload_terms' ) );
    add_action( 'wp_ajax_nopriv_dwc_specimen_upload_terms', array( $this, 'dwc_specimen_upload_terms' ) );

    add_action( 'wp_ajax_upload_media_for_dwc_specimen', array( $this, 'upload_media_for_dwc_specimen' ) ); 				//2. processes associated media upload
    add_action( 'wp_ajax_nopriv_upload_media_for_dwc_specimen', array( $this, 'upload_media_for_dwc_specimen' ) ); 			//2. processes associated media upload

    add_action( 'wp_ajax_upload_media_url_for_dwc_specimen', array( $this, 'upload_media_url_for_dwc_specimen' ) ); 		//3. processes associated media url
    add_action( 'wp_ajax_nopriv_upload_media_url_for_dwc_specimen', array( $this, 'upload_media_url_for_dwc_specimen' ) ); 	//3. processes associated media url

    add_action( 'wp_ajax_delete_dwc_specimen', array( $this, 'delete_dwc_specimen' ) ); 									//4. processes specimen deletion
    add_action( 'wp_ajax_nopriv_delete_dwc_specimen', array( $this, 'delete_dwc_specimen' ) ); 								//4. processes specimen deletion

    add_action( 'wp_ajax_curate_specimen', array( $this, 'curate_specimen' ) );
    add_action( 'wp_ajax_nopriv_curate_specimen', array( $this, 'curate_specimen' ) );

		add_action('rest_api_init', array( $this, 'create_rest_routes' ) );
		add_filter('dwc_class_descriptions', array( $this, 'dwc_class_descriptions' ) );
		add_action('pre_get_posts', array($this, 'display_draft_single_posts') );
		add_action('pre_get_posts', array($this, 'show_curation_archive') );

		add_filter( 'get_the_excerpt', 'do_shortcode', 1);
		add_action('init', array($this, 'dwc_add_shortcodes_to_excerpt' ) ); //trying to do shortcodes in search results
	}

	public function dwc_add_shortcodes_to_excerpt() {
		//add_filter( 'get_the_excerpt', 'shortcode_unautop');
    //add_filter('excerpt_length', array( $this, 'isacustom_excerpt_length') );
		add_shortcode( 'dwc-post-content', array( $this, 'dwc_post_content_shortcode' ) );
	}

/*

	function isacustom_excerpt_length($length) {
    global $post;
    if ($post->post_type == 'dwc-specimen')
    	return 10000;
		else
			return $length;
    }
*/
	public function dwc_post_content_shortcode( $atts, $content = '', $tag = '' ) {
		/*
		$specimen = new DarwinCoreSpecimen($atts['id']);
		$result = '<table id="specimen-archive-list"><th>&nbsp;</th><th>Taxon</th><th>Location</th><th>Geological Context</th>
			<tr class="hover-hand" data-href="'.get_post_type_archive_link(DarwinCoreSpecimen::POST_TYPE).$atts['id'].'/">
				<td>'.darwin_core::specimen_featured_thumbnail( $atts['id'] ).'</td>
				<td>'.$specimen->display_precise_meta( 'Taxon', 3 ).'</td>
				<td>'.$specimen->display_precise_meta( 'Location', 2 ).'</td>
				<td>'.$specimen->display_precise_meta( 'GeologicalContext', 3 ).'</td>
			</tr>
		</table>';
		*/
		$result = "testing";
		return $result;
	}

	public function hide_admin_classes( $classes ) {
		if (!current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
			for ($count=1;$count<=count($classes);$count++) {
				foreach ($classes[$count] as $key => $current) {
					if ($current == 'RecordLevel')
						unset($classes[$count][$key]);
				}
			}
		}

		return $classes;
	}

	//somehow this seems to be working even though it isn't hooked up to the add_action
	public function ceo_single_page_published_and_draft_posts( $query ) {
    if( is_single() && $query->get('post_type') == 'dwc_specimen') {
      $query->set('post_status', 'publish,draft');
    }
	}

	public function show_curation_archive( $query ) {
    if( is_archive() && $query->get('post_type') == DarwinCoreSpecimen::POST_TYPE && $_GET['curate'] == 'true') {
      $query->set('meta_query', array(
        array(
            'key'     => 'curated',
            'value'   => 'true',
            'compare' => '!=',
        )));
    }
	}

  public function darwin_core_scripts() {
  	wp_enqueue_script( 'jquery' );
  	wp_enqueue_style( 'darwin-core-css', plugins_url( '/style.css', __FILE__ ), array(), '1.456' );
  	wp_enqueue_script( 'darwin-core-js', plugins_url( '/scripts.js', __FILE__ ), array(), '1.456'  );
  	wp_enqueue_script( 'googlemaps', '//maps.googleapis.com/maps/api/js?key=AIzaSyCiawlM6d6FHKwLVNKP8eaO9eaug8DUSbk', array( 'jquery' ) );
  	wp_enqueue_script( 'popup-overlay-js', '//cdn.rawgit.com/vast-engineering/jquery-popup-overlay/1.7.13/jquery.popupoverlay.js', array( 'jquery' ) );
  }

  public function darwin_core_localize_scripts() {
  	$image_url = plugins_url( 'images/updating.gif', __FILE__ );
    $localizations = array( 'loadingGifURL' => $image_url );

    wp_localize_script( 'darwin-core-js', 'localizedVars', $localizations );
  }

  public function darwin_core_admin_scripts() {
  	wp_enqueue_style( 'darwin-core-admin-css', plugins_url( '/admin-style.css', __FILE__ ) );
  	wp_enqueue_script( 'jquery' );
  	wp_enqueue_script( 'jquery-ui-core' );
  	wp_enqueue_script( 'jquery-ui-sortable' );
  	wp_enqueue_script( 'darwin-core-admin-js', plugins_url( '/admin-scripts.js', __FILE__ ), array(), '1.452' );
  }

	/** Public Methods *******************************************************/

	public function dwc_class_descriptions ($class) {
		switch ($class) {
			case 'Occurrence' :
				$rval = "<div class='dwc-class-description'><p>When you collected this fossil you may have noted specific observations about the rock, location, or fossil. Please include any important information about how you encountered and/or collected the specimen. For example: 'Discovered laying on the sand of North Topsail beach during low tide,' or 'Found while sifting through gravel in Possum Creek.'</p></div>";
				break;
			case 'Taxon' :
				$rval = "<div class='dwc-class-description'>
				<p>Here is how to use basic Linnaean classification structure:</p>
				<ol>
				<li data-target='plus'><a>+</a> <span>Species Name</span>
				<ol>
				<li>Species name includes the generic assignment (genus name) and specific epithet - making it a two part, or binomial, name.</li>
				<li>For example: <i>Canis familiaris</i>, where <i>Canis</i> is the generic assignment (genus name) and <i>familiaris</i> is the specific epithet, together they are the species name.</li><li>Only include <i>'familiaris'</i> in the specific epithet box.</li>
				</ol>
				<li>
				<li>
				<li data-target='plus'><a>+</a> <span>Author</span>
				<ol>
				<li>Here is where you can write the scientific author for the species or subspecies you have found.</li>
				<li>This is not required information and it is okay to leave the field blank.</li>
				</ol>
				</li>
				<li data-target='plus'><a>+</a> <span>Need Help?</span>
				<ol>
				<li>Click on the ‘Taxon Wizard’ button if you are unsure of how to begin. You will be prompted to enter in any level of scientific name you may know.</li>
				<li>If you are unsure of the fossil's identification, head to our <a href='".get_site_url()."/groups/what-is-it/' target='_blank'>What is it? Group</a> or <a href='".get_site_url()."/forums/forum/3-what-is-it/' target='_blank'>What is it? Forum</a> to get help from the community.</li>
				</ol>
				</li>
				</ol>
				</div>";
				break;
			case 'Dimensions' :
				$rval = "<div class='dwc-class-description'><ol><li data-target='plus'><a>+</a> <span>How to be considerate when taking measurements:</span><ol><li>The definitions of height, width, and length vary by the person taking the measurements.</li><li>If you choose to include dimensions make sure to include a comment or image that depicts or explains how you took the measurements.</li><li>It is best to always use the metric system; the default setting is centimeters in the upload screen.</li></ol></li></ol></div>";
				break;
			case 'Location' :
				$rval = "<div class='dwc-class-description'><p>Where did you find the fossil?</p>
				<ol>
				<li data-target='plus'><a>+</a> <span>Try to be specific</span>
				<ol>
				<li>It is best to be as specific as possible, if you have latitude and longitude data please include it.</li>
				<li>Sometimes that is not always possible or the fossil was collected a long time ago.</li>
				</ol>
				</li>
				<li data-target='plus'><a>+</a> <span>DO NOT guess</span>
				<ol>
				<li>If you know a nearby city, input that and leave the latitude and longitude blank. Often other fossils have been found within the same area. This is particularly important for studies on how organisms were distributed around the world at different points in time.</li>
				</ol>
				</li>
				<li data-target='plus'><a>+</a> <span>Need an example?</span>
				<ol>
				<li>Decimal degrees are used here to express latitude and longitude. For example, the coordinates of Gainesville, FL, USA in decimal degrees are 29.651630°, -82.32483°.</li>
				</ol>
				</li>
				<li data-target='plus'><a>+</a> <span>Should you share your location?</span>
				<ol>
				<li>If you collected your fossil from private property or from a special collecting site, you do not have to share this information. You can either not include it on your upload or uncheck the 'Show location' check box.</li>
				</ol>
				</li>
				</ol>
				</div>";
				break;
			case 'GeologicalContext' :
				$rval = "<div class='dwc-class-description'><p>Geochronology translates roughly to telling time with rocks.</p>
				<ol>
				<li data-target='plus'><a>+</a> <span>How do we use it?</span>
				<ol>
				<li>For a research grade specimen, it is best to get to the most specific unit of time possible (Age) to best know when in geologic time the organism lived.</li>
				<li>This is particularly important when seeing when groups of organisms first appear and disappear in the fossil record, which helps us understand changes in biodiversity through time.</li>
				<li>Geological maps can also help identify age and rocks type head to the <a href='https://www.usgs.gov/products/maps/geologic-maps' target='_blank'>United States Geological Survey</a> to learn more.</li>
				<li>Apps can identify the age of the rock through the GPS in your phone <a href='https://itunes.apple.com/us/app/mancos/id541570878?mt=8' target='_blank'>'Mancos'</a> and <a href='https://rockd.org/' target='_blank'>'Rockd'</a>.</li>
				</ol>
				</li>
				</ol>
				</div>";
				break;
			case 'Lithostratigraphy' :
				$rval = "<div class='dwc-class-description'>
				<p>Lithostratigraphy is the study of rock layers.</p>
				<ol>
				<li data-target='plus'><a>+</a> <span>What should you include?</span>
				<ol>
				<li>The Group, Formation, and Member of the rock unit that you found your fossil in.</li>
				<li>Include the Member if possible.</li>
				<li>Apps can identify the rock through the GPS in your phone <a href='https://itunes.apple.com/us/app/mancos/id541570878?mt=8' target='_blank'>'Mancos'</a> and <a href='https://rockd.org/' target='_blank'>'Rockd'</a>.</li>
				<li>These apps are particularly useful because they also describe what other fossils you should expect to find, a description of the rock, in some cases lat/long data, and much more.</li>
				</div>";
				break;
		}
		return $rval;
	}

	public function alter_darwin_core_json_meta_term ($term, $termName, $termValues) {
		if ($termName == 'occurrenceID')
			$term = null;

		return $term;
	}

	public function alter_myfs_api_specimen_get_schema ($meta_keys) {
		$occurrence_terms = $meta_keys["Occurrence"]["terms"];
		unset($occurrence_terms['occurrenceID']);
		$meta_keys['Occurrence']['terms'] = $occurrence_terms;
		unset($meta_keys["RecordLevel"]);

		return $meta_keys;
	}

	protected function guidv4() {
    if (function_exists('com_create_guid') === true)
        return trim(com_create_guid(), '{}');

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	 * Processes a Darwin Core Specimen being labeled as research grade
	 *
	 * @since 1.0.0
	 */
	public function curate_specimen() {
		if ( !wp_verify_nonce( $_POST['curate_dwc_specimen_nonce'], 'curate_dwc_specimen' ) ) {
			wp_send_json_error( new WP_Error( 'research_grade_dwc_specimen_nonce_error', __( "Shenanigans are afoot. Bailing..." ) ), 401 );
		}

		$post = get_post( $_POST['dwc_specimen_id'] );

		if ( $post->post_type !== 'dwc_specimen') {
  		wp_send_json_error( new WP_Error( 'curate_dwc_specimen_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
  	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
  		wp_send_json_error( new WP_Error( 'curate_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
  	}
		$meta = get_post_meta( $post->ID );

		if ($_POST['grade'] == 'downgrade') {
			$specimen = new DarwinCoreSpecimen($post->ID);
			$content = "";

			//disassociate image attachments from their ac-media
			$media = $specimen->get_image_assets();
			foreach ( $media as $current ) {
				$args = array(
					'post_parent' => $current,
					'post_type' => 'attachment'
				);
				$children = get_children($args);

				$child = array_pop($children);
				$attachments[] = $child->ID;
				$description = get_post_meta( $current, "description", true );
				$content .= empty($description) ? "" : "<p>".$description."</p>";

				$child_update = array(
            'ID'            => $child->ID,
            'post_parent'   => 0
        );

				$rval['images'][] = $child->ID;
				wp_update_post( $child_update );
				wp_delete_post( $current );
			}

			global $bp;
			global $activities_template;
			//get buddypress post
			$activity_args = array(
				"page" => 1,
				"per_page" => 1,
				"filter_query" => array(
					array(
						'column' => 'secondary_item_id',
						'value' => $post->ID,
						'compare' => '='
					)
				)
			);

			//edit buddypress post to be app_image_update
			if ( bp_has_activities( $activity_args ) ) {
				foreach ( $activities_template->activities as $activity ) {
					$user_id = $post->post_author;
					$user_link = bp_core_get_user_domain( $user_id );
					$username =  bp_core_get_user_displayname( $user_id );

					$bp_args['id'] = $activity->id;
					$bp_args['user_id'] = $user_id;
					$bp_args['type'] = "app_image_update";
					$bp_args['secondary_item_id'] = 0;
					$bp_args['recorded_time'] = $activity->date_recorded;

					if ($activity->component == 'groups') {
						$group_id = $activity->item_id;
						$group = groups_get_group( array( 'group_id' => $group_id ) );
						$group_link = home_url( $bp->groups->slug . '/' . $group->slug );
						$group_name = $group->name;
						$bp_args['item_id'] = $group_id;
						$bp_args['component'] = "groups";
						$bp_args['action'] = "<a href='{$user_link}'>{$username}</a> posted an image in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app";
						$bp_args['content'] = $content . "\n[myfs-app-image id='".implode(',',$attachments)."']"; //make sure this shortcode can handle multiple images

					} else {
						$bp_args['component'] = "activity";
						$bp_args['action'] = "<a href='{$user_link}'>{$username}</a> posted an image.";
						$bp_args['content'] = $content . "\n[myfs-app-image id='".implode(',',$attachments)."']"; //make sure this shortcode can handle multiple images
					}

					bp_activity_add($bp_args);
					$rval['bp'] = $bp_args;
				}
			}

			//purge dwc-specimen, ac-media, and all of their associated meta
			wp_delete_post( $post->ID );
			$rval['grade'] = $_POST['grade'];
			wp_send_json_success( $rval , 200 );
			die();
		}

		if ($_POST['grade'] == 'research') {
			foreach ($meta as $key => $value)
				$clean_meta[$key] = $value[0];
			$clean_meta['Occurrence_occurrenceID'] = $this->guidv4();
			$specimen = new DarwinCoreSpecimen($post->ID);
			$specimen->save( $clean_meta );
		}
		$curation_count = empty($meta['curation_count'][0]) ? 0 : $meta['curation_count'][0];

		$curation_count++;

		update_post_meta( $_POST['dwc_specimen_id'], 'curation_count', $curation_count );
		update_post_meta( $_POST['dwc_specimen_id'], 'grade', $_POST['grade'] );
		update_post_meta( $_POST['dwc_specimen_id'], 'curated', 'true' );

		$rval = array( "OccurenceID" => $clean_meta['Occurrence_occurrenceID'], "grade" => $_POST['grade']);
		wp_send_json_success( $rval , 200 );
	}

	/**
	 * Processes the update of a Darwin Core specimen
	 *
	 * @since 1.0.0
	 */
	public function dwc_specimen_upload_terms() {
		$rval = new stdClass();
		$rval->hascontent = array();
		$val->terms = array();
		$val->values = array();

		if ( !wp_verify_nonce( $_POST['dwc_specimen_upload_terms_nonce'], 'dwc_specimen_upload_terms' ) ) {
			wp_send_json_error( new WP_Error( 'dwc_specimen_upload_terms_nonce_error', __( "Shenanigans are afoot. Bailing on update..." ) ), 401 );
		}

		$rval->id = ( empty( $_POST['dwc_specimen_id'] ) ) ? self::create_dwc_specimen( 'draft' ) : $_POST['dwc_specimen_id'];

		$post = get_post( $rval->id );

		if ( $post->post_type !== 'dwc_specimen') {
  		wp_send_json_error( new WP_Error( 'dwc_specimen_upload_terms_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
  	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
  		wp_send_json_error( new WP_Error( 'dwc_specimen_upload_terms_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
  	}

    $specimen = new DarwinCoreSpecimen( $post->ID );
    $specimen->save($_POST);

		$class_and_terms = $specimen->get_meta_keys();

		foreach ($class_and_terms as $className => $classSettings) {
			foreach($classSettings['terms'] as $termName => $termSettings) {
				$key_name = $className.'_'.$termName;
				$rval->terms[] = $key_name;
				if (!empty($_POST[$key_name])) {
					$rval->values[] = $_POST[$key_name];
					if (!in_array($className, $rval->hascontent))
						$rval->hascontent[] = $className;
				} else {
					$rval->values[] = "UNKNOWN";
				}
			}
		}

		$rval->author_name = bp_core_get_user_displayname( $post->post_author );
		$rval->last_updated = get_post_modified_time('m-d-Y H:i', false, $rval->id);

		if (get_post_status( $rval->id ) == 'publish') {
			$specimen = new DarwinCoreSpecimen($rval->id);
			$specimen->bp_group_activity_update();
		}
		wp_send_json_success( $rval , 200 );
	}

	/**
	 * Processes the upload of Darwin Core associated media from the creation wizard
	 *
	 * @since 1.0.0
	 *
	 * @todo create handling of associated media when audubon core is not installed
	 */
	public function dwc_specimen_upload_media() {
		$rval = new stdClass();

		if ( !wp_verify_nonce( $_POST['dwc_specimen_upload_media_nonce'], 'dwc_specimen_upload_media' ) ) {
			wp_send_json_error( new WP_Error( 'dwc_specimen_upload_media_nonce_error', __( "Shenanigans are afoot. Bailing on upload..." ) ), 401 );
		}

		if ($_FILES['dwc_media_file']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error( new WP_Error( 'dwc_specimen_upload_media_file_error', __( "There was a problem with the selected file." ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'dwc_specimen_upload_media_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$rval->id = ( empty( $_POST['dwc_specimen_id'] ) ) ? self::create_dwc_specimen( 'draft' ) : $_POST['dwc_specimen_id'];

		$post = get_post( $rval->id );

		if ( $post->post_type !== 'dwc_specimen') {
    		wp_send_json_error( new WP_Error( 'dwc_specimen_upload_media_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'dwc_specimen_upload_media_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

  	if ( class_exists( 'audubon_core' ) ) {
  		$ac_id = audubon_core::instance()->process_media_upload( 'dwc_media_file', $rval->id );

  		if ( !$ac_id )
				wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_file_error', __( "There was a problem with the selected file, but a specimen was created." ) ), 400 );
  	}

		$img_args = array(
			'post_parent' => $ac_id,
			'post_type' => 'attachment'
		);
		$media = array_shift( get_children($img_args) );
		if ( !is_null( $media ) ) {
			$rval->thumb_src = wp_get_attachment_image_src( $media->ID, 'thumbnail', false);
			$is_published = array(
          'ID'            => $rval->id,
          'post_status'   => "publish"
      );
			wp_update_post( $is_published );
		}

		$rval->author_name = bp_core_get_user_displayname( $post->post_author );
		$rval->last_updated = get_post_modified_time('m-d-Y H:i', false, $rval->id);

		if (get_post_status( $rval->id ) == 'publish') {
			$specimen = new DarwinCoreSpecimen($rval->id);
			$specimen->bp_group_activity_update();
		}

		wp_send_json_success( $rval , 201 );
	}

	public function ingest_myfossil_specimens() {
		$ch = curl_init();

		// set url
		curl_setopt($ch, CURLOPT_URL, "http://paleobiodb.org/data1.1/intervals/list.json?scale=1&vocab=pbdb");

		//return the transfer as a string
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// $output contains the output string
		$output = curl_exec($ch);

		$pbdb_obj = json_decode($output);
		$records = $pbdb_obj->records;
		curl_close($ch);

		$equivalence = array(
		  "country" => "Location_country",
		  "state" => "Location_stateProvince",
		  "county" => "Location_county",
		  "city" => "Location_locality",
		  "latitude" => "Location_decimalLatitude",
		  "longitude" => "Location_decimalLongitude",
		  "not_disclosed" => "Location_disclosed",
		  "eon" => "GeologicalContext_earliestEonOrLowestEonothem",
		  "era" => "GeologicalContext_earliestEraOrLowestErathem",
		  "period" => "GeologicalContext_earliestPeriodOrLowestSystem",
		  "epoch" => "GeologicalContext_earliestEpochOrLowestSeries",
		  "age" => "GeologicalContext_earliestAgeOrLowestStage",
		  "group" => "GeologicalContext_group",
		  "formation" => "GeologicalContext_formation",
		  "member" => "GeologicalContext_member",
		  "common" => "Taxon_vernacularName",
		  "kingdom" => "Taxon_kingdom",
		  "phylum" => "Taxon_phylum",
		  "class" => "Taxon_class",
		  "order" => "Taxon_order",
		  "family" => "Taxon_family",
		  "genus" => "Taxon_genus",
		  "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx1" => "Taxon_subgenus",
		  "species" => "Taxon_specificEpithet",
		  "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx2" => "Taxon_infraspecificEpithet",
		  "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx3" => "Taxon_scientificNameAuthorship",
		  "length" => "Dimensions_length",
		  "width" => "Dimensions_width",
		  "height" => "Dimensions_height"
		);
		$response = array();
		$paged = 1;

		$fossil_query_args = array(
		  'post_type' => myFOSSIL\Plugin\Specimen\Fossil::POST_TYPE,
		  'posts_per_page' => 100,
			'paged' => $paged,
		  'post_status' => array('publish','draft')
		);


		global $post;
		$fossils = new WP_Query( $fossil_query_args );
		if ( $fossils->have_posts() ) {
		  while ( $fossils->have_posts() ) {
				$fossils->the_post();
				$fossil_id = get_the_ID();
				$author_id = get_the_author_meta( 'ID' );
				$post_status = get_post_status( $fossil_id );
				$fossil = new myFOSSIL\Plugin\Specimen\Fossil( $fossil_id );

		    $entry = new stdClass();
		    $entry->values = array();
		    $entry->images = array();
				$entry->author_id = $author_id;
				$entry->post_status = $post_status;

		    foreach ( myFOSSIL\Plugin\Specimen\FossilTaxa::get_ranks() as $k ) {
		      $new_taxon = $equivalence[$k];
		      $entry->values[$new_taxon] = $fossil->taxa->{ $k }->name;
		    }
		    foreach ( array( 'length', 'width', 'height' ) as $k ) {
		      $new_dimen = $equivalence[$k];
		      $entry->values[$new_dimen] = $fossil->dimension->{ $k } * 100;
		    }
		    foreach ( array( 'city', 'state', 'county', 'country', 'latitude', 'longitude', 'not_disclosed' ) as $k ) {
		    $new_location = $equivalence[$k];
		      if ($k === 'not_disclosed') {
		        if ($fossil->location->{ $k } == "true")
		          $entry->values[$new_location] = "false";
		        else
		          $entry->values[$new_location] = "true";
		      } else {
		        $entry->values[$new_location] = $fossil->location->{ $k };
		      }
		    }

		    $scales = array(
		      "1" => 'eon',
		      "2" => 'era',
		      "3" => 'period',
		      "4" => 'epoch',
		      "5" => 'age'
		    );

		    $intervals = array();
		    $match = false;

		    foreach ($records as $interval) {
		      $intervals[$interval->interval_no] = $interval;
		      if ($fossil->time_interval->name === $interval->interval_name) {
		          $match = $interval->interval_no;
		      }
		    }


		    //Populate parents of the time interval

		    $current_interval = $match ? $intervals[$match] : null;
		    while ($current_interval) {
		        $new_geochron = $equivalence[$scales[$current_interval->level]];
		        $entry->values[$new_geochron] = $current_interval->interval_name;
		        $current_interval = $intervals[$current_interval->parent_no];
		    }

		    foreach ( myFOSSIL\Plugin\Specimen\Stratum::get_ranks() as $n => $k ) {
		      $new_litho = $equivalence[$k];
		      $entry->values[$new_litho] = $fossil->strata->{ $k }->name;
		    }

		    $images = get_attached_media( 'image', $fossil->id );

		    if ( ! is_array( $images ) ) {
		        $images = array( $images );
		    }
		    foreach ( $images as $image ) {
		      $entry->images[] = $image->ID;
		    }

				//CREATE THE NEW DARWIN CORE AND AUDUBON CORE ENTRIES AND ASSOCIATE THE IMAGES WITH THEM

			 	$post_status = (count($entry->images) > 0) ? 'publish' : 'draft';

		    $specimen_id = self::create_dwc_specimen( $post_status );
		    $entry->specimen_id = $specimen_id;

		    wp_update_post(
					array(
						'ID' => $specimen_id,
						'post_parent' => $fossil_id,
						'post_author' => $author_id,
						'post_date' => $post->post_date,
						'post_date_gmt' => $post->post_date_gmt));

		    $new_specimen = new DarwinCoreSpecimen( $specimen_id );
		    $new_specimen->save($entry->values);

		    foreach ($entry->images as $current_image_id) {
		      $audubon_id = audubon_core::create_ac_media( $specimen_id );
		      wp_update_post(array('ID' => $audubon_id,'post_author' => $author_id));

		      $image_src = wp_get_attachment_url( $current_image_id );
		      preg_match( "#\.([A-Za-z]+)$#", $image_src, $matches );
		      $entry->resource_ext[$current_image_id] = strtolower($matches[1]);
		      $entry->resource_url[$current_image_id] = preg_replace('#^https?:#', '', $image_src );
		  		update_post_meta( $audubon_id, 'resource_ext', strtolower($matches[1]) );
		  		update_post_meta( $audubon_id, 'resource_url', preg_replace('#^https?:#', '', $image_src ) );
		      wp_update_post(array('ID' => $current_image_id,'post_parent' => $audubon_id));
		    }

		    $response[$paged.'_'.get_the_ID()] = $entry;
		  }
		  // Restore original Post Data
		  wp_reset_postdata();
		} else {
		  // no posts found
		}

		echo json_encode($response);
		die();
	}

	public function publish_image_specimens() {
		global $post;
		$rval = array();

		//Any DwC Drafts that have images should now be Published
		$specimen_query_args = array(
		  'post_type' => DarwinCoreSpecimen::POST_TYPE,
		  'posts_per_page' => -1,
		  'post_status' => array('draft')
		);

		$draft_specimens = new WP_Query( $specimen_query_args );
		if ( $draft_specimens->have_posts() ) {
		  while ( $draft_specimens->have_posts() ) {
				$draft_specimens->the_post();
				$obj = new DarwinCoreSpecimen(get_the_ID());

				//if image attached switch to publish
				if ( !empty( $obj->get_image_assets() ) ) {
					$rval['publishing'][] = get_the_ID();

					$dwc_update = array(
	            'ID'           => get_the_ID(),
							'post_status'  => 'publish'
	        );
	        wp_update_post( $dwc_update );
				}
			}
		}

		wp_send_json_success( $rval , 201 );
	}

	public function create_historic_bp_activity() {
		global $post;
		$rval = array();
		$bp_ids = array();

		//get all bp activities dwc_specimen_create
		$activity_args = array(
			"per_page" => 0,
			"filter_query" => array(
				array(
					'column' => 'type',
					'value' => 'dwc_specimen_created',
					'compare' => '='
				)
			)
		);
		$activity = BP_Activity_Activity::get( $activity_args );

		foreach ($activity['activities'] as $current) {
			$bp_ids[] = $current->secondary_item_id;
		}

		//get all darwin-core publish posts
		$specimen_query_args = array(
		  'post_type' => DarwinCoreSpecimen::POST_TYPE,
		  'posts_per_page' => 500,
			'paged' => 5,
		  'post_status' => array('publish')
		);

		$publish_specimens = new WP_Query( $specimen_query_args );
		if ( $publish_specimens->have_posts() ) {
		  while ( $publish_specimens->have_posts() ) {
				$publish_specimens->the_post();
				$obj = new DarwinCoreSpecimen(get_the_ID());

				//if no bp activity, create one
				if (!in_array(get_the_ID(), $bp_ids ) ) {
					$rval['activity_added'][] = get_the_ID();
					$obj->bp_group_activity_update_historic(0, $post->post_date_gmt, get_the_author_meta( 'ID' ));
				}
			}
		}

		wp_send_json_success( $rval , 201 );
	}

	public function set_specimen_post_content() {
		global $post;
		$rval = array();
		$specimen_query_args = array(
			'post_type' => DarwinCoreSpecimen::POST_TYPE,
			'posts_per_page' => 500,
			'paged' => 5,
			'post_status' => array('publish')
		);

		$publish_specimens = new WP_Query( $specimen_query_args );
		if ( $publish_specimens->have_posts() ) {
		  while ( $publish_specimens->have_posts() ) {
				$publish_specimens->the_post();
				$obj = new DarwinCoreSpecimen(get_the_ID());
				$content = $obj->create_content_text();
				$rval['update_content'][] = get_the_ID().":".$content;

				$dwc_update = array(
						'ID'       => get_the_ID(),
						'post_content'  => $content
				);
				wp_update_post( $dwc_update );
			}
		}

		wp_send_json_success( $rval , 201 );
	}

	/**
	 * Register the routes for all of the controllers
	 */
	public function create_rest_routes() {
		// Activities.
		$controller = new DWC_REST_Ingestion_Controller;
		$controller->register_routes();
		$user_controller = new DWC_REST_User_Controller;
		$user_controller->register_routes();
	}

  public function register_custom_post_type() {
    $labels = array(
      'name'                => __( 'DwC Specimens', 'darwin-core' ),
      'singular_name'       => __( 'DwC Specimen', 'darwin-core' ),
      'menu_name'           => __( 'DwC Specimen', 'darwin-core' ),
      'parent_item_colon'   => __( 'Parent DwC Specimen:', 'darwin-core' ),
      'all_items'           => __( 'DwC Specimens', 'darwin-core' ),
      'view_item'           => __( 'View DwC Specimen', 'darwin-core' ),
      'add_new_item'        => __( 'Add New DwC Specimen', 'darwin-core' ),
      'add_new'             => __( 'Add New', 'darwin-core' ),
      'edit_item'           => __( 'Edit DwC Specimen', 'darwin-core' ),
      'update_item'         => __( 'Update DwC Specimen', 'darwin-core' ),
      'search_items'        => __( 'Search DwC Specimens', 'darwin-core' ),
      'not_found'           => __( 'DwC Specimen not found', 'darwin-core' ),
      'not_found_in_trash'  => __( 'DwC Specimen not found in Trash', 'darwin-core' ),
    );

    $args = array(
      'label'               => __( DarwinCoreSpecimen::POST_TYPE, 'darwin-core' ),
      'description'         => __( 'Represents a Darwin Core Specimen', 'darwin-core' ),
      'labels'              => $labels,
      'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields', 'comments', 'post-formats' ),
      'hierarchical'        => false,
      'public'              => true,
      'show_ui'             => true,
      'show_in_menu'        => true,
      'show_in_rest'		  	=> true,
      'menu_position'       => 27,
      'can_export'          => true,
      'has_archive'         => true,
      'rewrite'             => array(
        'slug' => 'dwc-specimen',
        'with_front' => false,
        'feed' => true,
        'pages' => true
      ),
      'exclude_from_search' => false,
      'publicly_queryable'  => true,
      'capability_type'     => 'post',
      'menu_icon'           => 'dashicons-media-document'
    );

    register_post_type( DarwinCoreSpecimen::POST_TYPE, $args );
  }

	function darwin_core_single_templates($single) {
    global $wp_query, $post;

    /* Checks for single template by post type */
    if ($post->post_type == DarwinCoreSpecimen::POST_TYPE) {
      if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/single-dwc-specimen.php') ) {
        return plugin_dir_path( __FILE__ ) . 'templates/single-dwc-specimen.php';
      }
    }
    return $single;
	}


	function darwin_core_archive_templates($archive) {
	    global $wp_query, $post;

	    /* Checks for archive template by post type */
	    if ($wp_query->query_vars['post_type'] == DarwinCoreSpecimen::POST_TYPE) {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/archive-dwc-specimen.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/archive-dwc-specimen.php';
	        }
	    }

	    return $archive;
	}

	function darwin_core_page_templates($archive) {
	    global $wp_query, $post;
	    /* Checks for single template by post type */
	    if ($wp_query->query_vars['pagename'] == 'darwin-core-wizard') {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/darwin-core-wizard.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/darwin-core-wizard.php';
	        }
	    } elseif ($wp_query->query_vars['pagename'] == 'create-specimen') {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/create-specimen.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/create-specimen.php';
	        }
	    } elseif ($wp_query->query_vars['pagename'] == 'dwc-exhibits') {
	        if( file_exists(plugin_dir_path( __FILE__ ) . 'templates/dwc-exhibits.php') ) {
	            return plugin_dir_path( __FILE__ ) . 'templates/dwc-exhibits.php';
	        }
	    }
	    return $archive;
	}

	/**
	 * Displays the featured image thumbnail
	 *
	 * @since 1.0.0
	 *
	 * @param int $id
	 */
	public function specimen_featured_thumbnail( $id ) {
		$displayed = false;

		$ac_args = array(
			'post_parent' => $id,
			'post_type' => 'ac_media'
		);
		$children = get_children($ac_args);

		if ( count( $children ) > 0 ) {
			while ( !$displayed && count( $children ) > 0 ) {
				$child = array_shift( $children );

				if ( $child != null ) {
					$img_args = array(
						'post_parent' => $child->ID,
						'post_type' => 'attachment'
					);
					$attachments = get_children($img_args);
					$media = array_shift( $attachments );
					if ( !is_null( $media ) ) {
						$thumb = wp_get_attachment_image( $media->ID, 'thumbnail', false, array('class'=>'dwc-gallery-thumb wp-image-'.$media->ID) );
						if ( !empty( $thumb ) ) {
							echo $thumb;
							$displayed = true;
						}
					}
				}
			}
		}

		if (!$displayed)
			echo '<img src="/wp-content/uploads/2017/06/stl-no-thumb.png" class="dwc-gallery-thumb" />';
	}

	/**
	 * Creates a Darwin Core Specimen post
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_status	Should the post be a draft of published (draft if from web, published if from app)
	 *
	 * @return int|WP_Error Post ID on success, WP_Error on failure
	 */
	public function create_dwc_specimen( $post_status ) {
		$args = array(
      'post_title' => '',
      'post_status' => $post_status,
      'post_type' => DarwinCoreSpecimen::POST_TYPE
    );

    $id = wp_insert_post( $args );


		$update_args = array(
			'ID'           	=> $id,
			'post_title'   	=> 'Specimen ' . $id,
			'post_name'		=> $id
		);

		wp_update_post( $update_args );

		do_action( 'darwin_core_create_specimen', $id );

		return $id;
	}

	/**
	 * Processes the deletion of a Darwin Core Specimen
	 *
	 * @since 1.0.0
	 */
	public function delete_dwc_specimen() {
		if ( !wp_verify_nonce( $_POST['delete_dwc_specimen_nonce'], 'delete_dwc_specimen' ) ) {
			wp_send_json_error( new WP_Error( 'delete_dwc_specimen_nonce_error', __( "Shenanigans are afoot. Bailing..." ) ), 401 );
		}

		$post = get_post( $_POST['dwc_specimen_id'] );

		if ( $post->post_type !== 'dwc_specimen') {
  		wp_send_json_error( new WP_Error( 'delete_dwc_specimen_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
  	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
  		wp_send_json_error( new WP_Error( 'delete_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
  	}

  	$condemned = $post->ID;

  	$children_args = array(
			'post_parent' => $condemned,
			'post_type' => 'ac_media'
		);

		$children = get_children( $children_args ); //these will be ac-media posts

		foreach ($children as $child) {

	    	$grandchildren_args = array(
				'post_parent' => $child->ID,
				'post_type' => 'any'
			);

			$grandchildren = get_children( $grandchildren_args ); //these will be attachments to ac-media posts

			foreach ( $grandchildren as $grandchild )
				wp_delete_attachment( $grandchild->ID  );

			if ( !empty( $thumb_id = get_post_meta( $child->ID, 'thumb_id', true ) ) )
				wp_delete_attachment( $thumb_id  ); //delete orphaned attachments

			wp_delete_post( $child->ID, true  );
		}

		wp_delete_post( $condemned, true );

/*
		if ( wp_delete_post( $post->ID ) ) {

	    	if ( class_exists( 'audubon_core' ) ) {
				$args = array(
					'post_parent' => $target->ID,
					'post_type' => 'ac_media'
				);
				$children = get_children( $args );

				foreach ($children as $child) {
					$update_args = array(
			            'ID'            => $child->ID,
			            'post_parent'   => 0
			        );
			        wp_update_post( $update_args );
				}
			} else {
	    		//create handling of associated media when audubon core is not installed
			}
		} else {
    		wp_send_json_error( new WP_Error( 'delete_dwc_specimen_failure_error', __( "Something went wrong during deletion." ) ), 400 );
		}
		*/
		wp_send_json_success( $post->ID , 200 );
	}

	/**
	 * Processes the update of a Darwin Core specimen
	 *
	 * @since 1.0.0
	 */
	public function update_dwc_specimen() {
		if ( !wp_verify_nonce( $_POST['update_dwc_specimen_nonce'], 'update_dwc_specimen' ) ) {
			wp_send_json_error( new WP_Error( 'update_dwc_specimen_nonce_error', __( "Shenanigans are afoot. Bailing on update..." ) ), 401 );
		}

		$post = get_post( $_POST['dwc_specimen_id'] );

		if ( $post->post_type !== 'dwc_specimen') {
    		wp_send_json_error( new WP_Error( 'update_dwc_specimen_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'update_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$dwc_title = empty($_POST['post_title'] ) ? 'Specimen '.$post->ID : $_POST['post_title'];

		$args = array(
			'ID'           	=> $post->ID,
			'post_title'   	=> $dwc_title,
			'post_name'		=> $post->ID
		);
		wp_update_post( $args);

        $specimen = new DarwinCoreSpecimen( $post->ID );
        $specimen->save($_POST);

		wp_send_json_success( $post->ID , 200 );
	}

	/**
	 * Processes the upload of Darwin Core associated media
	 *
	 * @since 1.0.0
	 *
	 * @todo create handling of associated media when audubon core is not installed
	 */
	public function upload_media_for_dwc_specimen() {

		if ( !wp_verify_nonce( $_POST['upload_media_for_dwc_specimen_nonce'], 'upload_media_for_dwc_specimen' ) ) {
			wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_nonce_error', __( "Shenanigans are afoot. Bailing on upload..." ) ), 401 );
		}

		if ($_FILES['dwc_media_file']['error'] !== UPLOAD_ERR_OK) {
			wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_file_error', __( "There was a problem with the selected file." ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$id = ( empty( $_POST['dwc_specimen_id'] ) ) ? self::create_dwc_specimen( 'draft' ) : $_POST['dwc_specimen_id'];

		$post = get_post( $id );

		if ( $post->post_type !== 'dwc_specimen') {
    		wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

    	if ( class_exists( 'audubon_core' ) ) {
    		$ac_id = audubon_core::instance()->process_media_upload( 'dwc_media_file', $id );

    		if ( !$ac_id )
				wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_file_error', __( "There was a problem with the selected file, but a specimen was created." ) ), 400 );
    	}

    	//create handling of associated media when audubon core is not installed, this runs regardless of audubon being installed because this needs to continue working if that plugin is removed

		wp_send_json_success( $id , 201 );
	}

	/**
	 * Processes the upload of Darwin Core associated media URL
	 *
	 * @since 1.0.0
	 *
	 * @todo create handling of associated media when audubon core is not installed
	 */
	public function upload_media_url_for_dwc_specimen() {

		if ( !wp_verify_nonce( $_POST['upload_media_url_for_dwc_specimen_nonce'], 'upload_media_url_for_dwc_specimen' ) ) {
			wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_nonce_error', __( "Shenanigans are afoot. Bailing on upload..." ) ), 401 );
		}

		if ( empty( $_POST['media_url'] ) || !filter_var( $_POST['media_url'], FILTER_VALIDATE_URL ) ) {
			wp_send_json_error(new WP_Error( 'upload_media_url_for_dwc_specimen_malformed_url_error', __( "There is something wrong with your URL..." ) ), 400 );
		}

		if ( !current_user_can( 'author' ) && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_media_url_for_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

		$id = ( empty( $_POST['dwc_specimen_id'] ) ) ? self::create_dwc_specimen( 'draft' ) : $_POST['dwc_specimen_id'];

		$post = get_post( $id );

		if ( $post->post_type !== 'dwc_specimen') {
    		wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_post_type_error', __( "That isn't a Darwin Core Specimen..." ) ), 400 );
    	}

		if ( $post->post_author != get_current_user_id() && !current_user_can( 'administrator' ) && !current_user_can( 'dwc_curator' ) ) {
    		wp_send_json_error( new WP_Error( 'upload_media_for_dwc_specimen_permission_error', __( "You don't have permission to play with that..." ) ), 401 );
    	}

    	if ( class_exists( 'audubon_core' ) ) {
    		$ac_id = audubon_core::instance()->process_media_url_upload( $_POST['media_url'], $id );

    		if ( !$ac_id )
				wp_send_json_error( new WP_Error( 'upload_media_url_for_dwc_specimen_error', __( "There was a problem with the selected file, but a specimen was created." ) ), 400 );
    	}

    	//create handling of associated media url when audubon core is not installed, this runs regardless of audubon being installed because this needs to continue working if that plugin is removed

		wp_send_json_success( $id , 201 );
	}

    //all of these should be broken out to my functions.php because I don't think they will be part of Darwin Core core
	public function term_occurrenceID_edit_html( $html, $type, $key, $displayName, $value, $user_access, $summary ) {
		if ($key == 'Occurrence_occurrenceID' && !current_user_can('administrator') && !current_user_can( 'dwc_curator' ))
			$html = '';

		if ($key == 'Location_disclosed' && $user_access == 'guest')
			$html = '';

		if ($key == 'GeologicalContext_group')
			$html = '<h3'.($summary != true ? ' class="dwc-fake-header"' : '').'>Lithostratigraphy</h3>'.$html;

	  return $html;
	}

	public function add_taxon_helper( $html, $user_access ) {
		if ($user_access != 'guest') {
			$html = '<button type="button" id="dwc-improve-fossil-taxon-open" class="btn btn-info dwc-helper improve-fossil-taxon_open">
	                    <i class="fa fa-fw fa-magic"></i>
	                    Taxon Wizard
	                </button><!-- test-->
	                <div id="dwc-improve-fossil-taxon" class="edit-fossil-popup">
		                <div class="edit-fossil">
		                    <div class="edit-fossil-heading">
		                        <h4>Taxonomy</h4>
		                    </div>
		                    <div class="edit-fossil-body">
		                        <!--<form class="form">-->
		                            <div class="form-group">
		                                <label class="control-label">Taxon</label>
		                                <input
		                                    class="form-control"
		                                    id="dwc-edit-fossil-taxon-name"
		                                    placeholder="Begin typing your Taxon"
		                                    type="text"
		                                />
		                            </div>
		                        <!--</form>-->
		                    </div>
		                    <div class="edit-fossil-footer">
		                        <ul id="edit-fossil-taxon-results">
		                        </ul>
		                    </div>
		                </div>
		            </div>';
		}
    return $html;
	}
/*
	public function add_location_helper( $html, $user_access ) {
		if ($user_access != 'guest') {
			$html = '<button type="button" id="dwc-improve-fossil-location" class="btn btn-info dwc-helper">
                    <i class="fa fa-fw fa-magic"></i>
                    Improve Location
                </button>';
		}
    return $html;
	}
*/
	public function alter_owner_location_html( $html, $meta_key, $user_access ) {
		$html .= '<div style="height:300px;margin:20px;" id="dwc-fossil-map-container"></div>';

	    return $html;
	}

	public function alter_location_html( $html, $meta_key, $user_access ) {
		if ($user_access != 'guest') {
			$html .= '<div style="height:300px;margin:20px;" id="dwc-fossil-map-container"></div>';
		} else {
			if ($meta_key['terms']['disclosed']['value'] == 'true') {
				if (empty( $meta_key['terms']['decimalLatitude']['value'] ) || empty( $meta_key['terms']['decimalLongitude']['value'] ) ) {
					$html .= "<p>Latitude and Longitude must be entered to display the map.</p>";
				} else {
					$html .= '<div style="height:300px;margin:20px;" id="dwc-fossil-map-container"></div>';
				}
			}
			else {
				$html = "<p>The owner of this entry has chosen not to disclose the location.</p>";
			}
		}
		return $html;
	}

	public function add_geologicalContext_helper( $html, $user_access ) {
		if ($user_access != 'guest') {
			$html = '<button type="button" id="improve-fossil-geochronolgy-open" class="btn btn-info dwc-helper improve-fossil-geologicalContext_open">
                    <i class="fa fa-fw fa-magic"></i>
                    Geochronology Wizard
                </button>
	            <div id="improve-fossil-geologicalContext" class="edit-fossil-popup">
	                <div class="edit-fossil">
	                    <div class="edit-fossil-heading">
	                        <h4>Geochronology</h4>
	                    </div>
	                    <div class="edit-fossil-body">
	                        <!--<form class="form">-->
	                            <div class="form-group">
	                                <label class="control-label">Time Interval</label>
	                                <select class="form-control" id="dwc-edit-fossil-geologicalContext">
	                                </select>
	                            </div>
	                        <!--</form>-->
	                    </div>
	                    <div class="edit-fossil-footer">
	                    </div>
	                </div>
	            </div>';
		}
    return $html;
	}

    public function bp_add_dwc_nav_items()
    {
        global $bp;

        bp_core_new_nav_item(
            array(
                'name' => 'Specimens',
                'slug' => 'dwc_specimen',
                'default_subnav_slug' => 'dwc_specimen',
                'parent_url' => bp_displayed_user_domain(),
                'parent_slug' => $bp->members->slug . bp_displayed_user_id(),
                'position' => 70,
                'show_for_displayed_user' => true,
                'screen_function' => array( $this, 'bp_display_specimen_page')
            )
        );
    }

    public function bp_display_specimen_page() {
	    //add title and content here - last is to call the members plugin.php template
	    add_action( 'bp_template_title', array( $this, 'bp_show_screen_title') );
	    add_action( 'bp_template_content', array( $this, 'bp_show_screen_content') );
	    bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
    }

	function bp_show_screen_title() {
	    echo 'Darwin Core Specimens';
	}

	function bp_show_screen_content() {
?>
	<div id="darwin-core">
		<div id="primary" class="content-area">
			<main id="main" class="site-main" role="main">
			<?php if (is_user_logged_in()) : ?>
				<div class="row">
					<div class="col-xs-2 col-xs-offset-10">
						<?php wp_nonce_field('dwc_create_specimen_ajax', 'dwc_create_specimen_nonce'); ?>
						<a href="/create-specimen" class="btn btn-primary ajax-btn">New Specimen</a>
					</div>
				</div>
			<?php endif; ?>
				<div class="row">
					<div class="col-xs-12">
					<?php

					//$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;
					$post_status = (bp_displayed_user_id() == get_current_user_id() || current_user_can('administrator')) ? array('publish', 'draft') : 'publish';
					$query_args = array(
						'author' =>  bp_displayed_user_id(),
						'post_type' => 'dwc_specimen',
						'posts_per_page' => -1,
						'post_status' => $post_status
					);

					$wp_query = new WP_Query( $query_args );

					global $post;
					if ( $wp_query->have_posts() ) {
						echo "<table id='specimen-archive-list'><th>&nbsp;</th><th>Taxon</th><th>Location</th><th>Geological Context</th>";
						while ( $wp_query->have_posts() ) :
							$wp_query->the_post();
							$specimen = new DarwinCoreSpecimen( get_the_ID() ); ?>
							<tr class="hover-hand" data-href="<?= get_post_type_archive_link(DarwinCoreSpecimen::POST_TYPE) ?><?= the_id() ?>/">
								<td><?php darwin_core::specimen_featured_thumbnail( get_the_ID() ); ?><p><?= get_the_title() ?><br />by <a href="<?= bp_core_get_user_domain( get_the_author_meta( 'ID' ) ) ?>"><?= get_the_author() ?></a></p></td>
								<td><?php $specimen->display_precise_meta( 'Taxon', 3 ); ?></td>
								<td><?php $specimen->display_precise_meta( 'Location', 2 ); ?></td>
								<td><?php $specimen->display_precise_meta( 'GeologicalContext', 3 ); ?></td>
					  		</tr>
						<?php
						endwhile;

						echo "</table>";

					} else {
						?>
					  <h1>Sorry...</h1>
					  <p><?php _e('Sorry, no posts matched your criteria.'); ?></p>
					  <?php
					}
					?>
					</div>
				</div>
			</main><!-- .site-main -->
		</div><!-- .content-area -->
	</div>
<?php
	}

	public function darwin_core_admin_settings_menu() {
		//add_menu_page( 'My Top Level Menu Example', 'Top Level Menu', 'manage_options', 'myplugin/myplugin-admin-page.php', 'myplguin_admin_page', 'dashicons-tickets', 6  );
		add_options_page( 'Darwin Core Options', 'Darwin Core', 'manage_options', 'darwin-core-terms', array($this, 'darwin_core_options' ) );
	}

	public function darwin_core_options() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		global $wpdb;
        $classes_table_name = $wpdb->prefix . 'darwin_core_classes';
        $terms_table_name = $wpdb->prefix . 'darwin_core_terms';
        $vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';

        $tab = (!empty($_GET['tab']))? esc_attr($_GET['tab']) : 'first';
        echo "<h1>Darwin Core Settings</h1>";
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

				echo "<!--<form id='dwc-ingest-myfossil-form'><input type='hidden' name='action' value='ingest_myfossil_specimens' />".wp_nonce_field('ingest_myfossil_specimens', 'ingest_myfossil_specimens_nonce', true, false)."<a id='dwc-ingest-myfossil' href='#' class='btn btn-primary ajax-btn'>Ingest myfossil_specimens</a></form>-->";
				echo "<form id='dwc-publish-image-specimens-form'><input type='hidden' name='action' value='publish_image_specimens' />".wp_nonce_field('publish_image_specimens', 'publish_image_specimens_nonce', true, false)."<a id='dwc-publish-image-specimens' href='#' class='btn btn-primary ajax-btn'>Publish Image Specimens</a></form>";
				echo "<form id='dwc-create-bp-activity-form'><input type='hidden' name='action' value='historic_bp_activity' />".wp_nonce_field('historic_bp_activity', 'historic_bp_activity_nonce', true, false)."<a id='dwc-create-bp-activity' href='#' class='btn btn-primary ajax-btn'>Create BP Activity</a></form>";
				echo "<form id='dwc-set-specimen-post-content-form'><input type='hidden' name='action' value='set_specimen_post_content' />".wp_nonce_field('set_specimen_post_content', 'set_specimen_post_content_nonce', true, false)."<a id='dwc-set-specimen-post-content' href='#' class='btn btn-primary ajax-btn'>Set Specimen Post Content</a></form>";

		    echo "<form method='post' action='".esc_url( admin_url('admin-post.php') )."'>
	        	<input type='hidden' name='action' value='dwc_update_classes' />";
		    wp_nonce_field('dwc_update_classes_post', 'dwc_update_classes_nonce');
	        echo "<table id='dwc-classes' class='dwc-classes'><tbody>
	        	<tr><th class='dwc-class-heading'><h1>Classes</h1></th></tr>
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

	    echo "<div class='dwc-admin-tab'><form method='post' action='".esc_url( admin_url('admin-post.php') )."'>
        	<input type='hidden' name='action' value='dwc_update_terms' />";
	    wp_nonce_field('dwc_update_terms_post', 'dwc_update_terms_nonce');

      foreach ($org as $class_id => $values) {
      	echo "<h1>".$values['parent']['classDisplayName']."</h1>
      		<table id='dwc-terms' class='dwc-terms'><thead><tr><th>Term Name</th><th>Display Name</th><th>Value Type</th><th>Enabled</th></tr></thead><tbody id='".$class_id."'>";

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
        	echo "<tr class='dwc-term-placeholder'><td></td><td></td><td></td><td></td></tr>";
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
      $html .=  '<a class="nav-tab ' . $class . '" href="options-general.php?page=darwin-core-terms&tab=' . $tab . '">' . $name . '</a>';
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

	public function darwin_core_ajaxurl() {
		echo '<script type="text/javascript">
			      var ajaxurl = "' . admin_url('admin-ajax.php') . '";
			    </script>';
	}
	/*
    public function bp_add_dwc_nav_items()
    {
        global $bp;

        bp_core_new_nav_item(
            array(
                'name' => 'DwC Specimen',
                'slug' => 'dwc_specimen',
                'default_subnav_slug' => 'dwc_specimen',
                'parent_url' => bp_displayed_user_domain(),
                'parent_slug' => $bp->members->slug . bp_displayed_user_id(),
                'position' => 50,
                'show_for_displayed_user' => true,
                'screen_function' => 'darwin_core_bp_display_collection_page'
            )
        );
    }
	*/

	static function darwin_core_install() {
		global $wpdb;
		global $darwin_core_db_version;

		$classes_table_name = $wpdb->prefix . 'darwin_core_classes';
		$terms_table_name = $wpdb->prefix . 'darwin_core_terms';
		$vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';

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

		add_option( 'darwin_core_db_version', $darwin_core_db_version );


		if ( post_exists("Darwin Core Wizard") === 0 ) {
			$post = array(
		          'comment_status' => 'closed',
		          'ping_status' =>  'closed' ,
		          'post_author' => get_current_user_id(),
		          'post_date' => current_time('Y-m-d H:i:s'),
		          'post_name' => 'Darwin Core Wizard',
		          'post_status' => 'publish' ,
		          'post_title' => 'Darwin Core Wizard',
		          'post_type' => 'page',
		    );
		    //insert page and save the id
		    $newvalue = wp_insert_post( $post, false );
		}
	}

	static function darwin_core_install_data() {
		global $wpdb;

		$classes_table_name = $wpdb->prefix . 'darwin_core_classes';
		$terms_table_name = $wpdb->prefix . 'darwin_core_terms';
		$vocabulary_table_name = $wpdb->prefix . 'darwin_core_vocabulary';

		$vocab = array(
			'basisOfRecord' => array(
				'PreservedSpecimen' => 'Preserved Specimen',
				'FossilSpecimen' => 'Fossil Specimen',
				'LivingSpecimen' => 'Living Specimen',
				'HumanObservation' => 'Human Observation',
				'MachineObservation' => 'Machine Observation',
				'MaterialSample' => 'Material Sample',			//IM NOT SURE IF THIS SHOULD BE AN OPTION
				'Occurrence' => 'Occurrence'					//IM NOT SURE IF THIS SHOULD BE AN OPTION
				)
			);

		$input = array(
			'RecordLevel' => array('Record Level', array(
				'type' => array('Type', 'text', false),
				'modified' => array('Modified', 'text', false),
				'language' => array('Type', 'text', false),
				'license' => array('License', 'text', false),
				'rightsHolder' => array('Rights Holder', 'text', false),
				'accessRights' => array('Access Rights', 'text', false),
				'bibliographicCitation' => array('Bibliographic Citation', 'text', false),
				'references' => array('References', 'text', false),
				'institutionID' => array('Institution ID', 'text', false),
				'collectionID' => array('Collection ID', 'text', false),
				'datasetID' => array('Dataset ID', 'text', false),
				'institutionCode' => array('Institution Code', 'text', false),
				'collectionCode' => array('Collection Code', 'text', false),
				'datasetName' => array('Dataset Name', 'text', false),
				'ownerInstitutionCode' => array('Owner Institution Code', 'text', false),
				'basisOfRecord' => array('Basis of Record', 'select', false),
				'informationWithheld' => array('Information Withheld', 'text', false),
				'dataGeneralizations' => array('Data Generalizations', 'text', false),
				'dynamicProperties' => array('Dynamic Properties', 'text', false)
				)
			),
			'Occurrence' => array('Occurrence', array(
				'occurrenceID' => array('Occurrence ID', 'text', false),
				'catalogNumber' => array('Catalog Number', 'text', false),
				'recordNumber' => array('Record Number', 'text', false),
				'recordedBy' => array('Recorded By', 'text', false),
				'individualCount' => array('Individual Count', 'text', false),
				'organismQuantity' => array('Organism Quantity', 'text', false),
				'organismQuantityType' => array('Organism Quantity Type', 'text', false),
				'sex' => array('Sex', 'text', false),
				'lifeStage' => array('Life Stage', 'text', false),
				'reproductiveCondition' => array('Reproductive Condition', 'text', false),
				'behavior' => array('Behavior', 'text', false),
				'establishmentMeans' => array('Establishment Means', 'text', false),
				'occurrenceStatus' => array('Occurrence Status', 'text', false),
				'preparations' => array('Preparations', 'text', false),
				'disposition' => array('Disposition', 'text', false),
				'associatedMedia' => array('Associated Media', 'text', false),
				'associatedReferences' => array('Associated References', 'text', false),
				'associatedSequences' => array('Associated Sequences', 'text', false),
				'associatedTaxa' => array('Associated Taxa', 'text', false),
				'otherCatalogNumbers' => array('Other Catalog Numbers', 'text', false),
				'occurrenceRemarks' => array('Occurrence Remarks', 'text', false)
				)
			),
			'Organism' => array('Organism', array(
				'organismID' => array('Organism ID', 'text', false),
				'organismName' => array('Organism Name', 'text', false),
				'organismScope' => array('Organism Scope', 'text', false),
				'associatedOccurrences' => array('Associated Occurrences', 'text', false),
				'associatedOrganisms' => array('Associated Organisms', 'text', false),
				'previousIdentifications' => array('Previous Identifications', 'text', false),
				'organismRemarks' => array('Organism Remarks', 'text', false)
				)
			),
			'MaterialSample' => array('Material Sample', array(
				'materialSampleID' => array('Material Sample ID', 'text', false),
				)
			),
			'Event' => array('Event', array(
				'eventID' => array('Event ID', 'text', false),
				'parentEventID' => array('Parent Event ID', 'text', false),
				'fieldNumber' => array('Field Number', 'text', false),
				'eventDate' => array('Event Date', 'text', false),
				'eventTime' => array('Event Time', 'text', false),
				'startDayOfYear' => array('Start Day of Year', 'text', false),
				'endDayOfYear' => array('End Day of Year', 'text', false),
				'year' => array('Year', 'text', false),
				'month' => array('Month', 'text', false),
				'day' => array('Day', 'text', false),
				'verbatimEventDate' => array('Verbatim Event Date', 'text', false),
				'habitat' => array('Habitat', 'text', false),
				'samplingProtocol' => array('Sampling Protocol', 'text', false),
				'sampleSizeValue' => array('Sample Size Value', 'text', false),
				'sampleSizeUnit' => array('Sample Size Unit', 'text', false),
				'samplingEffort' => array('Sampling Effort', 'text', false),
				'fieldNotes' => array('Field Notes', 'text', false),
				'eventRemarks' => array('Event Remarks', 'text', false)
				)
			),
			'Location' => array('Location', array(
				'locationID' => array('Location ID', 'text', false),
				'higherGeographyID' => array('Higher Geography ID', 'text', false),
				'higherGeography' => array('Higher Geography', 'text', false),
				'continent' => array('Continent', 'text', false),
				'waterBody' => array('Water Body', 'text', false),
				'islandGroup' => array('Island Group', 'text', false),
				'island' => array('Island', 'text', false),
				'country' => array('Country', 'text', true),
				'countryCode' => array('Country Code', 'text', false),
				'stateProvince' => array('State', 'text', true),
				'county' => array('County', 'text', true),
				'municipality' => array('Municipality', 'text', false),
				'locality' => array('City', 'text', true),
				'verbatimLocality' => array('Verbatim Locality', 'text', false),
				'minimumElevationInMeters' => array('Minimum Elevation in Meters', 'text', false),
				'maximumElevationInMeters' => array('Maximum Elevation in Meters', 'text', false),
				'verbatimElevation' => array('Verbatim Elevation', 'text', false),
				'minimumDepthInMeters' => array('Minimum Depth in Meters', 'text', false),
				'maximumDepthInMeters' => array('Maximum Depth in Meters', 'text', false),
				'verbatimDepth' => array('Verbatim Depth', 'text', false),
				'minimumDistanceAboveSurfaceInMeters' => array('Minimum Distance Above Surface in Meters', 'text', false),
				'maximumDistanceAboveSurfaceInMeters' => array('Maximum Distance Above Surface in Meters', 'text', false),
				'locationAccordingTo' => array('Location According to', 'text', false),
				'locationRemarks' => array('Location Remarks', 'text', false),
				'decimalLatitude' => array('Latitude', 'text', true),
				'decimalLongitude' => array('Longitude', 'text', true),
				'geodeticDatum' => array('Geodetic Datum', 'text', false),
				'coordinateUncertaintyInMeters' => array('Coordinate Uncertainty in Meters', 'text', false),
				'coordinatePrecision' => array('Coordinate Precision', 'text', false),
				'pointRadiusSpatialFit' => array('Point Radius Spatial Fit', 'text', false),
				'verbatimCoordinates' => array('Verbatim Coordinates', 'text', false),
				'verbatimLatitude' => array('Verbatim Latitude', 'text', false),
				'verbatimLongitude' => array('Verbatim Longitude', 'text', false),
				'verbatimCoordinateSystem' => array('Verbatim Coordinate System', 'text', false),
				'verbatimSRS' => array('Verbatim SRS', 'text', false),
				'footprintWKT' => array('Footprint WKT', 'text', false),
				'footprintSRS' => array('Footprint SRS', 'text', false),
				'footprintSpatialFit' => array('Footprint Spatial Fit', 'text', false),
				'georeferencedBy' => array('Georeferenced by', 'text', false),
				'georeferencedDate' => array('Georeferenced Date', 'text', false),
				'georeferenceProtocol' => array('Georeference Protocol', 'text', false),
				'georeferenceSources' => array('Georeference Sources', 'text', false),
				'georeferenceVerificationStatus' => array('Georeference Verification Status', 'text', false),
				'georeferenceRemarks' => array('Georeference Remarks', 'text', false)
				)
			),
			'GeologicalContext' => array('Geological Context', array(
				'geologicalContextID' => array('Geological Context ID', 'text', false),
				'earliestEonOrLowestEonothem' => array('Earliest Eon or Lowest Eonothem', 'text', false),
				'latestEonOrHighestEonothem' => array('Latest Eon or Highest Eonothem', 'text', false),
				'earliestEraOrLowestErathem' => array('Earliest Era or Lowest Erathem', 'text', false),
				'latestEraOrHighestErathem' => array('Latest Era or Highest Erathem', 'text', false),
				'earliestPeriodOrLowestSystem' => array('Earliest Period or Lowest System', 'text', false),
				'latestPeriodOrHighestSystem' => array('Latest Period or Highest System', 'text', false),
				'earliestEpochOrLowestSeries' => array('Earliest Epoch or Lowest Series', 'text', false),
				'latestEpochOrHighestSeries' => array('Latest Epoch or Highest Series', 'text', false),
				'earliestAgeOrLowestStage' => array('Earliest Age or Highest Stage', 'text', false),
				'latestAgeOrHighestStage' => array('Latest Age or Highest Stage', 'text', false),
				'lowestBiostratigraphicZone' => array('Lowest Biostratigraphic Zone', 'text', false),
				'highestBiostratigraphicZone' => array('Highest Biostratigraphic Zone', 'text', false),
				'lithostratigraphicTerms' => array('Lithostratigraphic Terms', 'text', false),
				'group' => array('Group', 'text', false),
				'formation' => array('Formation', 'text', false),
				'member' => array('Member', 'text', false),
				'bed' => array('Bed', 'text', false)
				)
			),
			'Identification' => array('Identification', array(
				'identificationID' => array('Identification ID', 'text', false),
				'identificationQualifier' => array('Identification Qualifier', 'text', false),
				'typeStatus' => array('Type Status', 'text', false),
				'identifiedBy' => array('Identified By', 'text', false),
				'dateIdentified' => array('Date Identified', 'text', false),
				'identificationReferences' => array('Identification References', 'text', false),
				'identificationVerificationStatus' => array('Identification Verification Status', 'text', false),
				'identificationRemarks' => array('Identification Remarks', 'text', false)
				)
			),
			'Taxon' => array('Taxon', array(
				'taxonID' => array('Taxon ID', 'text', false),
				'scientificNameID' => array('Scientific Name ID', 'text', false),
				'acceptedNameUsageID' => array('Accepted Name Usage ID', 'text', false),
				'parentNameUsageID' => array('Parent Name Usage ID', 'text', false),
				'originalNameUsageID' => array('Original Name Usage ID', 'text', false),
				'nameAccordingToID' => array('Name According to ID', 'text', false),
				'namePublishedInID' => array('Name Published in ID', 'text', false),
				'taxonConceptID' => array('Taxon Concept ID', 'text', false),
				'scientificName' => array('Scientific Name', 'text', false),
				'acceptedNameUsage' => array('Accepted Name Usage', 'text', false),
				'parentNameUsage' => array('Parent Name Usage', 'text', false),
				'originalNameUsage' => array('Original Name Usage', 'text', false),
				'nameAccordingTo' => array('Name According to', 'text', false),
				'namePublishedIn' => array('Name Published in', 'text', false),
				'namePublishedInYear' => array('Name Published in Year', 'text', false),
				'higherClassification' => array('Higher Classification', 'text', false),
				'kingdom' => array('Kingdom', 'text', true),
				'phylum' => array('Phylum', 'text', true),
				'class' => array('Class', 'text', true),
				'order' => array('Order', 'text', true),
				'family' => array('Family', 'text', true),
				'genus' => array('Genus', 'text', true),
				'subgenus' => array('Subgenus', 'text', false),
				'specificEpithet' => array('Specific Epithet', 'text', false),
				'infraspecificEpithet' => array('Infraspecific Epithet', 'text', false),
				'taxonRank' => array('Taxon Rank', 'text', false),
				'verbatimTaxonRank' => array('Verbatim Taxon Rank', 'text', false),
				'scientificNameAuthorship' => array('Scientific Name Authorship', 'text', false),
				'vernacularName' => array('Vernacular Name', 'text', false),
				'nomenclaturalCode' => array('Nomenclatural Code', 'text', false),
				'taxonomicStatus' => array('Taxonomic Status', 'text', false),
				'nomenclaturalStatus' => array('Nomenclatural Status', 'text', false),
				'taxonRemarks' => array('Taxon Remarks', 'text', false)
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

	static function darwin_core_add_curator_role() {
		add_role( 'dwc_curator', 'Curator', array( 'read' => true, 'edit_posts' => true ) );
	}

	public function process_dwc_update_classes() {
        if ( wp_verify_nonce( $_POST['dwc_update_classes_nonce'], 'dwc_update_classes_post' ) ) {
			global $wpdb;

	        $classes_table_name = $wpdb->prefix . 'darwin_core_classes';

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

	public function process_dwc_update_terms() {
    if ( wp_verify_nonce( $_POST['dwc_update_terms_nonce'], 'dwc_update_terms_post' ) ) {
			global $wpdb;
	        $terms_table_name = $wpdb->prefix . 'darwin_core_terms';

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
		        		array('%s', '%s', '%s', '%d', '%d', '%d'),
		        		'%d'
		        	);
	        	}
        	}
        }

        wp_redirect(get_site_url() .  $_POST['_wp_http_referer']);
	}
}
global $wpdb;
$classes_table_name = $wpdb->prefix . 'darwin_core_classes';

if($wpdb->get_var("show tables like '$classes_table_name'") != $classes_table_name) {
	register_activation_hook( __FILE__, array( 'darwin_core', 'darwin_core_install' ) );
	register_activation_hook( __FILE__, array( 'darwin_core', 'darwin_core_install_data' ) );
}

if ( !wp_roles()->is_role( 'dwc_curator' ) ) {
	register_activation_hook( __FILE__, array( 'darwin_core', 'darwin_core_add_curator_role' ) );
}

/**
 * The main function responsible for returning the one true darwin_core Instance
 * to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $mfsm = darwin_core(); ?>
 *
 * @return The one true darwin_core Instance
 */
function darwin_core() {
	return darwin_core::instance();
}

/**
 * Hook darwin_core early onto the 'plugins_loaded' action.
 *
 * This gives all other plugins the chance to load before Darwin Core, to get their
 * actions, filters, and overrides setup without Darwin Core being in the way.
 */
if ( defined( 'MYFOSSIL_COLLECTION_LATE_LOAD' ) ) {
	add_action( 'plugins_loaded', 'darwin_core', (int) MYFOSSIL_COLLECTION_LATE_LOAD );

// "And now here's something we hope you'll really like!"
} else {
	darwin_core();
}

endif; // class_exists check

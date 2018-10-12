<?php
/**
 * REST API: MYFS_REST_Activity_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * //Core class to access post types via the REST API.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */
class MYFS_REST_Activity_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'myfs-app/v1';
		$this->rest_base = 'activities';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @since 4.7.0
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => array(
					'context' => array(
						'validate_callback' => function($param, $request, $key) {
							return !is_null( $param );
						}
					),
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'page' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'scope' => array(
						'validate_callback' => function($param, $request, $key) {
							return !is_null( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)/like', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'create_like' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_like' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)/report', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'create_report' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => array(
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( is_user_logged_in() )
			return true;
		else
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to access this.' ), array( 'status' => 400 ) );
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {
		if ( is_user_logged_in() && ( current_user_can('author')  || current_user_can( 'administrator' ) ) )
			return true;
		else
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		global $activities_template;

		$activity_args = array(
			"page" => $page,
			"per_page" => 1,
			"include" => $request['id']
		);

		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to do this.' ), array( 'status' => 400 ) );

		if ( !current_user_can('author') && !current_user_can( 'administrator' ) )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		if ( !bp_has_activities( $activity_args ) )
			return new WP_Error( 'user_not_authorized', __( 'The activity was not found.' ), array( 'status' => 400 ) );

		if ( $activities_template->activities[0]->user_id != get_current_user_id() && !current_user_can('administrator') )
			return new WP_Error( 'user_not_authorized', __( 'You are not authorized to delete this.' ), array( 'status' => 400 ) );

		return true;
	}

	/**
	 * Checks whether a given request has permission to read types.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		global $activities_template;

		$activity_args = array(
			"page" => $page,
			"per_page" => 1,
			"include" => $request['id']
		);

		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to do this.' ), array( 'status' => 400 ) );

		if ( !current_user_can('author') && !current_user_can( 'administrator' ) )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		if ( !bp_has_activities( $activity_args ) )
			return new WP_Error( 'user_not_authorized', __( 'The activity was not found.' ), array( 'status' => 400 ) );

		if ( $activities_template->activities[0]->user_id != get_current_user_id() && !current_user_can('administrator') )
			return new WP_Error( 'user_not_authorized', __( 'You are not authorized to edit this.' ), array( 'status' => 400 ) );

		if ( $activities_template->activities[0]->type != "dwc_specimen_created" )
			return new WP_Error( 'user_not_authorized', __( 'You don\'t have permission to do this.' ), array( 'status' => 400 ) );

		return true;
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function create_like( $request ) {
		$user_id = get_current_user_id();
		$likes = bp_activity_get_meta( $request['id'], "like", false );

		if ( !in_array( $user_id, $likes ) ) {
			bp_activity_add_meta( $request['id'], "like", $user_id );


			$activity_args = array(
				"in" => array( $request['id'] )
			);

			$activity = BP_Activity_Activity::get( $activity_args )['activities'][0];

			//generate the buddypress notification
			$output = array(
		        'user_id'           => $activity->user_id,
		        'item_id'           => $request['id'],
		        'secondary_item_id' => $user_id,
		        'component_name'    => 'myfsapp',
		        'component_action'  => 'activity_liked',
		        'date_notified'     => bp_core_current_time(),
		        'is_new'            => 1,
		    );

		    bp_notifications_add_notification( $output );

		    if ( $activity->user_id != get_current_user_id() ) {

				$notification_args = array(
					'recipient' => $activity->user_id,
					'title' => 'Your post has been liked!',
					'body' => bp_core_get_user_displayname( $user_id ).' liked one of your posts.',
				);
				$push_result = myfs_send_push_notifications( $notification_args );
			}
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function create_report( $request ) {
		$reports = array();
		$user_id = get_current_user_id();
		$previous = bp_activity_get_meta( $request['id'], "report", true );
		$report_list = get_user_meta( $user_id, 'report_list', true );

		if ( $previous ) {
			$reports = unserialize( $previous );

			if ( array_key_exists( $user_id, $reports ) )
				return new WP_Error( 'duplicate_report', __( 'You have already reported this activity.' ), array( 'status' => 400 ) );

			$reports = unserialize( $previous );

			$reports[$user_id] = $request['report'];

			bp_activity_update_meta( $request['id'], "report", serialize( $reports ) );
		} else {
			$reports[$user_id] = $request['report'];
			bp_activity_add_meta( $request['id'], "report", serialize( $reports ) );
		}

		if ( empty( $report_list ) ) {
			add_user_meta( $user_id, 'report_list', serialize( array( $request['id'] ) ) );
		} else {
			$report_list = unserialize( $report_list );
			if ( !in_array( $request['id'], $report_list ) ) {
				array_push( $report_list, $request['id'] );
				update_user_meta( $user_id, 'report_list', serialize( $report_list ) );
			}
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function delete_like( $request ) {
		$user_id = get_current_user_id();
		$likes = bp_activity_get_meta( $request['id'], "like", false );

		if ( in_array( $user_id, $likes ) )
			bp_activity_delete_meta( $request['id'], "like", $user_id );

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function delete_item( $request ) {
		global $activities_template;
		$ghost = array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) );

		$activity_args = array(
			"page" => 1,
			"per_page" => 1,
			"include" => $request['id']
		);

		if ( bp_has_activities( $activity_args ) ) {

			if ( $activities_template->activities[0]->type == "dwc_specimen_created" ) {

				$condemned = $activities_template->activities[0]->secondary_item_id;

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
			}

			bp_activity_delete( array( 'id' => $request['id'] ) );
		}

		return new WP_REST_Response( $ghost, 200 ); //returns the ghost of the deleted activity
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function update_item( $request ) {
		global $activities_template;

		$activity_args = array(
			"page" => $page,
			"per_page" => 1,
			"include" => $request['id']
		);

		if ( bp_has_activities( $activity_args ) ) {
			$dwc_id = $activities_template->activities[0]->secondary_item_id;

			$_POST['post_status'] = "publish";
			$specimen = new DarwinCoreSpecimen( $dwc_id );
			$specimen->save($_POST);

			//$count = 0;
			foreach ($_FILES as $key => $value) {

				if ($_FILES[$key]['error'] === UPLOAD_ERR_OK) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );

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

					$attach_id = media_handle_upload( $key, $ac_id );

					$resource_url = wp_get_attachment_image_src( $attach_id, 'full' )[0];

					preg_match( "#\.([A-Za-z]+)$#", $resource_url, $matches );
					update_post_meta( $ac_id, 'resource_ext', strtolower($matches[1]) );
					update_post_meta( $ac_id, 'resource_url', preg_replace('#^https?:#', '', $resource_url ) );
					update_post_meta( $ac_id, 'description', $_POST["content-".$key] );

					$result->ac_media[] = $ac_id;
					$result->atts[] = $attach_id;
				}
			}

			foreach ($_POST as $key => $value) {
				if ( strpos( $key, 'acmedia_' ) === 0 ) {
					update_post_meta( explode( '_', $key )[1], 'description', $value );
				}
			}

			$args = array(
				'id' => $request['id'],
				'action' => $activities_template->activities[0]->action,
				'content' => bp_core_get_user_displayname( get_current_user_id() )." has contributed a new <a href='/dwc-specimen/".$dwc_id."'>specimen</a> to myFOSSIL!\n[dwc-specimen-created id=".$dwc_id."]".$specimen->json_meta_values()."[/dwc-specimen-created]",
				'component' => $activities_template->activities[0]->component,
				'type' => $activities_template->activities[0]->type,
				'item_id' => $activities_template->activities[0]->item_id,
				'secondary_item_id' => $activities_template->activities[0]->secondary_item_id
			);

			$activity_id = bp_activity_add( $args );

			if ($activity_id) {
				$user_activity = bp_update_user_last_activity( $activities_template->activities[0]->user_id );
			}
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $request['id'], 'all', null) ), 200 );
	}

	/**
	 * SAVE app_image_update
	 *
	 * @param
	 * @return
	 */
	public function save_app_image( $user_id, $key ) {
		$attach_id = false;
	
		if ($_FILES[$key]['error'] === UPLOAD_ERR_OK) {
			// These files need to be included as dependencies when on the front end.
			require_once( ABSPATH . 'wp-admin/includes/image.php' );
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/media.php' );
			$attach_id = media_handle_upload( $key, 0, array( "post_author" => $user_id ) );
		}
		
		return $attach_id;
	}

	/**
	 * SAVE dwc_specimen_created
	 *
	 * @param
	 * @return
	 */
	public function save_dwc_specimen( ) {
		$result = new stdClass();
		$dwc_id = 0;

		if ( class_exists('DarwinCoreSpecimen') && class_exists('AudubonCoreMedia') ) {
			$dwc_defaults = array(
		        'post_title' => '',
		        'post_status' => 'publish',
		        'post_type' => DarwinCoreSpecimen::POST_TYPE
		    );

		    $dwc_id = wp_insert_post( $dwc_defaults );
		    $specimen = new DarwinCoreSpecimen( $dwc_id );
		    $_POST['post_status'] = "publish";
		    $specimen->save($_POST);
		    //$specimen->bp_group_activity_update('app', $_POST['group_id']);

			$dwc_update = array(
				'ID'           	=> $dwc_id,
				'post_title'   	=> 'Specimen ' . $dwc_id,
				'post_name'		=> $dwc_id
			);
			wp_update_post( $dwc_update );

			//$count = 0;
			foreach ($_FILES as $key => $value) {
				if ($_FILES[$key]['error'] === UPLOAD_ERR_OK) {
					require_once( ABSPATH . 'wp-admin/includes/image.php' );
					require_once( ABSPATH . 'wp-admin/includes/file.php' );
					require_once( ABSPATH . 'wp-admin/includes/media.php' );

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

					$attach_id = media_handle_upload( $key, $ac_id );

					$resource_url = wp_get_attachment_image_src( $attach_id, 'full' )[0];

				}

				preg_match( "#\.([A-Za-z]+)$#", $resource_url, $matches );
				update_post_meta( $ac_id, 'resource_ext', strtolower($matches[1]) );
				update_post_meta( $ac_id, 'resource_url', preg_replace('#^https?:#', '', $resource_url ) );
				update_post_meta( $ac_id, 'description', $_POST["content-".$key] );

				$result->ac_media[] = $ac_id;
				$result->atts[] = $attach_id;
			}
		} else {
			return false;
		}

		$result->dwc_specimen = $dwc_id;
		$result->dwc_meta = $specimen->json_meta_values();
		return $result;
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function create_item( $request ) {
	 	global $bp;

	 	$success = true;

		$user_id = get_current_user_id();
		$user_link = bp_core_get_user_domain( $user_id );
		$username =  bp_core_get_user_displayname( $user_id );

		$group_id = $_POST['group_id'];
		$group = groups_get_group( array( 'group_id' => $group_id ) );
		$group_link = home_url( $bp->groups->slug . '/' . $group->slug );
		$group_name = $group->name;

		$args = array(
			'action' => "",
			'content' => "",
			'component' => "groups",
			'type' => $_POST['type'],
			'primary_link' => $user_link,
			'user_id' => $user_id,
			'item_id' => $group_id,
			'secondary_item_id' => 0,
			'recorded_time' => gmdate('Y-m-d H:i:s')
		);

		//switch $request['type'] and call other functions
		switch ( $_POST['type'] ) {
			case "dwc_specimen_created" :
				$success = self::save_dwc_specimen( ); //returns an object containing information on the dwc-specimen and ac-media that was just created
				$args['action'] = "<a href='{$user_link}'>{$username}</a> posted a new specimen in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app";
				$args['content'] = $username." has contributed a new <a href='/dwc-specimen/".$success->dwc_specimen."'>specimen</a> to myFOSSIL!\n[dwc-specimen-created id=".$success->dwc_specimen."]".$success->dwc_meta."[/dwc-specimen-created]";
				$args['secondary_item_id'] = $success->dwc_specimen;
				break;

			case "app_image_update" :

				foreach ($_FILES as $key => $value) {
					$success = self::save_app_image( $user_id, $key ); //returns ID of the new wp_attachment, can make this return an object if I need more data

					if ($success) {
						$args['action'] = "<a href='{$user_link}'>{$username}</a> posted an image in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app";
						$args['content'] = $_POST["content-".$key] . "\n[myfs-app-image id=".$success."]";
					}
				}
				break;

			case "activity_update" :
				$args['action'] = "<a href='{$user_link}'>{$username}</a> posted an update in the group <a href='{$group_link}'>{$group->name}</a> from the myFOSSIL app";
				$args['content'] = $_POST['content1'];
				break;
		}

		if ($success)
			$activity_id = bp_activity_add( $args );

		if ($activity_id) {
			$group_activity = groups_update_last_activity( $group_id );
			$user_activity = bp_update_user_last_activity( $user_id );
		}

		return new WP_REST_Response( array_pop( self::get_activity_from_params("single", 1, null, null, $activity_id, 'all', null) ), 200 );
	}

	/**
	 * Retrieves all public app activities.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$context = empty( $request['context'] ) ? 'all' : $request['context'];
		$page =  empty( $request['page'] ) ? 1 : $request['page'];
		$scope =  empty( $request['scope'] ) ? 'all' : $request['scope'];
		$user_id =  empty( $request['user_id'] ) ? null : $request['user_id'];
		$group_id =  empty( $request['group_id'] ) ? null : $request['group_id'];
		$activity_id =  empty( $request['id'] ) ? null : $request['id'];

		return new WP_REST_Response( self::get_activity_from_params($context, $page, $user_id, $group_id, $activity_id, $scope, null), 200 );
	}

	/**
	 *
	 *
	 * @param
	 * @return
	 */
	function get_activity_from_params( $context="all", $page=1, $user_id=null, $group_id=null, $activity_id=null, $scope="all", $search=null ) {
		global $activities_template;

		$hidden_groups = array('67');

		$current_user_id = get_current_user_id();
		$group_ids = array();
		$group_names = array();
		$result = array();

		$reports = get_user_meta( $current_user_id, 'report_list', true );
		$blocked = get_user_meta( $current_user_id, 'blocked_user', false );

		if ( !empty( $reports ) )
			$reports = unserialize( $reports );
		else
			$reports = array();

		$group_args = array(
			'meta_query' => array(
		        array(
		            'key'     => 'type',
		            'value'   => 'research-project,app-group',
		            'compare' => 'IN'
		        )
		    ),
			'type'=> 'alphabetical',
			'per_page'=> 999
		);

		$activity_args = array(
			"page" => $page,
			"per_page" => 10,
			"action" => "dwc_specimen_created,app_image_update",
			"exclude" => $reports,
			"filter_query" => array(
				array(
					'column' => 'user_id',
					'value' => implode( ",", $blocked ),
					'compare' => 'NOT IN'
				)
			)
		);

		if ( $scope == "all" ) {
			$activity_args['action'] .= ",activity_update";
		}

		switch ($context) {
			case "all" :
				$group_args['show_hidden'] = true;
				break;
			case "my-groups" :
				$group_args['user_id'] = $current_user_id;
				break;
			case "group" :
				$group_args['include'] = array( $group_id );
				break;
			case "user" :
				$activity_args['user_id'] = $user_id;
				$group_args['show_hidden'] = true;
				break;
			case "single" :
				$activity_args['include'] = $activity_id;
				$group_args['show_hidden'] = true;
				break;
			case "search" :
				$activity_args['search_terms'] = $search;
				$group_args['show_hidden'] = true;
				break;
		}

		$groups = BP_Groups_Group::get( $group_args );

		if ( empty( $groups ) ) {
			return null;
		}

		foreach ($groups['groups'] as $key => $value) {
			$group_ids[] = $value->id;
			$group_names[$value->id] = $value->name;
		}

		$activity_args['primary_id'] = $group_ids;

		if ( bp_has_activities( $activity_args ) ) {
			foreach ( $activities_template->activities as $activity ) {
				$new_activity = new stdClass();
				$new_activity->type = $activity->type;
				$new_activity->id = $activity->id;
				$new_activity->title = null;
				$new_activity->author_id = $activity->user_id;
				$new_activity->author_name = $activity->display_name;
				$new_activity->author_nicename = $activity->user_nicename;
				$new_activity->author_avatar = myfs_core_prepare_avatar_url( get_avatar_url($activity->user_id) );
				$new_activity->group_id = ( in_array( $activity->item_id, $hidden_groups ) ) ? null : $activity->item_id;
				$new_activity->group_name = ( in_array( $activity->item_id, $hidden_groups ) ) ? null : $group_names[$activity->item_id];
				$new_activity->post_time = strtotime($activity->date_recorded);
				$new_activity->content = '';
				$new_activity->images = null;

				$likes = bp_activity_get_meta( $activity->id, "like", false );
				$new_activity->is_liked = ( in_array( $current_user_id, $likes ) ) ? true : false;
				$new_activity->like_count = count( $likes );
				$new_activity->comment_count = 0;
				$new_activity->comments = null;
				$new_activity->metadata = null;

				if ($activity->type === "dwc_specimen_created") {
					$specimen = new DarwinCoreSpecimen($activity->secondary_item_id);
					$new_activity->title = get_the_title($activity->secondary_item_id);
					$new_activity->images = null;
					$new_activity->metadata = $specimen->json_meta();

					$temp_comments = get_comments( array( "post_id" => $activity->secondary_item_id ) );
					$new_activity->comment_count = count($temp_comments);

					$ac_args = array(
						'post_parent' => $activity->secondary_item_id,
						'post_type' => 'ac_media'
					);
					$children = get_children($ac_args);

					while ( $current_media = array_pop( $children ) ) {
						$meta = get_post_meta( $current_media->ID, '', false );

						if ($meta['resource_ext'] == 'stl')
							continue;

						$attachment = array_pop( get_children( array( 'post_parent' => $current_media->ID ) ) );

						$new_image_data = new stdClass();
						$new_image_data->id = $current_media->ID;
						$new_image_data->content = ( $meta["description"][0] == null ) ? '' : $meta["description"][0];
						$new_image_data->thumb_url = myfs_core_prepend_https( wp_get_attachment_image_src( $attachment->ID )[0] );
						$new_image_data->url = myfs_core_prepend_https( wp_get_attachment_image_src( $attachment->ID, 'full' )[0] );
						$new_activity->images[] = $new_image_data;
					}

				} else {
					if ($activity->type === "app_image_update") {
						$new_image_data = new stdClass();
						$content = strip_tags( str_replace( array( "\r", "\n" ), "", $activity->content) );
						$found_url = preg_match( "#\[myfs-app-image id\=([0-9]+)\]#", $content, $image_id );
						$new_image_data->content = preg_replace( "#\[myfs-app-image id\=[0-9]+\]#", "", $content );
						$new_image_data->url = wp_get_attachment_image_src( $image_id[1], 'full' )[0];

						$new_image_data->content = ( $new_image_data->content == null ) ? '' : $new_image_data->content;
						$new_activity->images[] = $new_image_data;
					} else {
						$content = strip_tags( str_replace( array( "\r", "\n" ), "", $activity->content) );
						$found_url = preg_match( "#\[bpfb_images\](.*)\[/bpfb_images\]#", $content, $image_urls );
						if ($found_url) {
							$new_image_data = new stdClass();
							$new_image_data->url = myfs_core_prepend_https( $image_urls[1] );
							$new_image_data->content = strip_tags( preg_replace( "#\[bpfb_images\].*\[/bpfb_images\]#", "", $content ) );

							$new_image_data->content = ( $new_image_data->content == null ) ? '' : $new_image_data->content;
							$new_activity->images[] = $new_image_data;
						} else {
							$new_activity->content = strip_tags( $activity->content );
						}
					}
				}

				if ($activity->children !== false) {
					$new_activity->comment_count = count( $activity->children );

					foreach ( $activity->children as $comment ) {
						$new_comment = new stdClass();
						$new_comment->comment_id = $comment->id;
						$new_comment->author_id = $comment->user_id;
						$new_comment->author_nicename = $comment->user_nicename;
						$new_comment->author_avatar = myfs_core_prepare_avatar_url( get_avatar_url( $comment->user_id ) );
						$new_comment->post_time = strtotime($comment->date_recorded);
						$new_comment->content = strip_tags( $comment->content );

						$new_activity->comments[] = $new_comment;
					}
				}

				if ( !empty( $new_activity->images ) )
					$new_activity->images = array_slice($new_activity->images, 0, 5);

				$result[] = $new_activity;
			}
		}

		return $result;
	}
}

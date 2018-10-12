<?php
/**
 * REST API: MYFS_REST_Notification_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * FCM_PUSH_KEY_TEST => AIzaSyDW32_gnJXf8GW8HnrSOxHZ9cL5nJQJUvw
 * FCM_PUSH_KEY_LIVE => AIzaSyAmBedXft2xXDheqGEHAg1EMMWVGHLV3mI
 **/
define( 'FCM_PUSH_KEY', 'AIzaSyDW32_gnJXf8GW8HnrSOxHZ9cL5nJQJUvw' );

/**
 * //Core class to access post types via the REST API.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */
class MYFS_REST_Notification_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'myfs-app/v1';
		$this->rest_base = 'notifications';
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
					'group_id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
					'id' => array(
						'validate_callback' => function($param, $request, $key) {
							return is_numeric( $param );
						}
					),
				),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)', array(
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

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/badge', array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_badge' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/fcm', array(
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'save_fcm_device_id' ),
				'permission_callback' => array( $this, 'save_fcm_device_id_permissions_check' ),
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
	 * Checks whether a given request has permission to join groups.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function delete_item_permissions_check( $request ) {
		if ( is_user_logged_in() )
			return true;
		else 
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to access this.' ), array( 'status' => 400 ) );
	}

	/**
	 * Checks whether a given request has permission to save a new device id for push notifications.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function save_fcm_device_id_permissions_check( $request ) {
		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to access this.' ), array( 'status' => 400 ) );
		
		return true;
	}

	/**
	 * Retrieves all public groups.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function save_fcm_device_id( $request ) {
		if ( !empty( $request['fcm_token'] ) )
			if (! in_array( $request['fcm_token'], get_user_meta( get_current_user_id(),'fcm_token' ) ) ) 
				$success = add_user_meta( get_current_user_id(), 'fcm_token', $request['fcm_token'] );

		return new WP_REST_Response( $success, 200 );
	}


	/**
	 * Retrieves all public groups.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		global $activities_template;

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

		$groups = BP_Groups_Group::get( $group_args );

		if ( empty( $groups ) ) {
			return null;
		}

		foreach ($groups['groups'] as $key => $value) {
			$group_ids[] = $value->id;
			$group_names[$value->id] = $value->name;
		}

		$args = array( 
			'user_id' => get_current_user_id(),
			'component_action' => array(
				'update_reply',
				'new_at_mention',
				'activity_liked'
			),
			'order_by' => 'id',
			'sort_order' => 'DESC'
		);
		
		$notifications = BP_Notifications_Notification::get( $args );

		if ( empty( $notifications ) ) {
			return null;
		}
		//return $notifications;

		foreach ( $notifications as $current ) {
			$activity_args = array(
				"in" => array( $current->item_id ),
				"display_comments" => true
			);

			$activity = BP_Activity_Activity::get( $activity_args ); 

			$target = $activity['activities'][0];

			$n = new stdClass();
			$n->id = $current->id;
			$n->source_avatar = myfs_core_prepare_avatar_url( get_avatar_url($current->secondary_item_id) );
			$n->source_nicename = bp_core_get_username( $current->secondary_item_id );
			$n->post_time = strtotime($current->date_notified);

			if ( $target->component == "groups" && in_array( $target->item_id, $group_ids ) ) {
				
				if ($current->component_action === 'new_at_mention') {
					$n->source_action = " has mentioned you in a post in ";
					$n->action = "<strong>" . bp_core_get_username( $current->secondary_item_id ) . "</strong>" . $n->source_action . "<strong><font color='#eba047'>" . $group_names[$target->item_id] . "</font></strong>";
				} else if ($current->component_action === 'activity_liked') {
					$n->source_action = " has liked your post in ";
					$n->action = "<strong>" . bp_core_get_username( $current->secondary_item_id ) . "</strong>" . $n->source_action . "<strong><font color='#eba047'>" . $group_names[$target->item_id] . "</font></strong>";
				}

				$n->source_group = $group_names[$target->item_id];
				$n->activity = array_pop( MYFS_REST_Activity_Controller::get_activity_from_params("single", 1, null, null, $target->id, 'all', null) );
				$result[] = $n;
			}
			else if ($target->component == "activity" && $target->type == "activity_comment") {

				$parent_args = array(
					"in" => $target->item_id
				);

				$parent = BP_Activity_Activity::get( $parent_args )['activities'][0];

				if ( in_array( $parent->item_id, $group_ids ) ) {
				
					if ($current->component_action === 'new_at_mention') {
						$n->source_action = " has mentioned you in a comment in ";
						$n->action = "<strong>" . bp_core_get_username( $current->secondary_item_id ) . "</strong>" . $n->source_action . "<strong><font color='#eba047'>" . $group_names[$parent->item_id] . "</font></strong>";
					} else if ($current->component_action === 'update_reply') {
						$n->source_action = " has commented on your post in ";
						$n->action = "<strong>" . bp_core_get_username( $current->secondary_item_id ) . "</strong>" . $n->source_action . "<strong><font color='#eba047'>" . $group_names[$parent->item_id] . "</font></strong>";
					}

					$n->source_group = $group_names[$parent->item_id];
					$n->activity = array_pop( MYFS_REST_Activity_Controller::get_activity_from_params("single", 1, null, null, $parent->id, 'all', null) );
					$result[] = $n;
				}
			}
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * 
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function delete_item( $request ) {
		if ( ! bp_notifications_check_notification_access( get_current_user_id(), $request['id'] ) ) {
			return false;
		}

		$result = BP_Notifications_Notification::update(
		    array( 'is_new' => 0 ),
		    array( 'id'     => $request['id'] )
		  );

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * Resets badging on iOS to zero.
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function delete_badge( $request ) {
		$result = update_user_meta( get_current_user_id(), 'badge_num', 0 );

		$result = ( $result != false ) ? true : false;

		return new WP_REST_Response( $result, 200 );
	}
}
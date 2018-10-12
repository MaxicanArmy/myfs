<?php
/**
 * REST API: MYFS_REST_Comment_Controller class
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
class MYFS_REST_Comment_Controller extends WP_REST_Controller {

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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<activity_id>[\d]+)/comments', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
			),
			//'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<activity_id>[\d]+)/comments/(?P<id>[\d]+)', array(
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
		if ( ( is_user_logged_in() && current_user_can('author') ) || current_user_can('administrator') )
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
		if ( !is_user_logged_in() )
			return new WP_Error( 'user_not_logged_in', __( 'You are not logged in.' ), array( 'status' => 400 ) );

		if ( !current_user_can('author') && !current_user_can('administrator' ) )
			return new WP_Error( 'user_not_authorized', __( 'Your account does not have the proper permissions.' ), array( 'status' => 400 ) );

		return true;
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function create_item( $request ) {
		global $activities_template;

		$activity_args = array(
			"page" => 1,
			"per_page" => 1,
			"include" => $request['activity_id']
		);

		if ( bp_has_activities( $activity_args ) ) {
			$args = array(
				'parent_id' => $request['activity_id'],
				'activity_id' => $request['activity_id'],
				'content' => $request['content'],
				'user_id' => get_current_user_id()
			);

			if ( !bp_activity_new_comment( $args ) )
				return new WP_Error( 'myfs_rest_bp_create_comment', __( 'There was an error creating this comment.' ), array( 'status' => 400 ) );
		}

		return new WP_REST_Response ( array_pop( MYFS_REST_Activity_Controller::get_activity_from_params("single", 1, null, null, $request['activity_id'], 'all', null) ), 200 );
	}

	/**
	 * Does some preprocessing on request before saving posted activities
	 *
	 * @param array $request Options for the function.
	 * @return
	 */
	public function delete_item( $request ) {
		global $activities_template;

		$activity_args = array(
			'page' => 1,
        	'per_page' => 1,
			"include" => $request['activity_id']
		);

		if ( bp_has_activities( $activity_args ) ) {
			if ( bp_activity_delete_comment( $activities_template->activities[0]->item_id, $request['id'] ) ) {
  				wp_cache_delete( $request['activity_id'], 'bp_activity_comments' );
				return new WP_REST_Response ( array_pop( MYFS_REST_Activity_Controller::get_activity_from_params("single", 1, null, null, $request['activity_id'], 'all', null) ), 200 );
			}
			else
				return new WP_Error( 'myfs_app_delete_comment', __( 'An error occured while deleting the comment.' ), array( 'status' => 400 ) );

		} else {
			return new WP_Error( 'myfs_app_delete_comment_no_parent', __( 'The parent activity for this comment wasn\'t found.' ), array( 'status' => 400 ) );
		}
	}
}

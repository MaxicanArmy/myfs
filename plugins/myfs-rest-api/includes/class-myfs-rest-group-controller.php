<?php
/**
 * REST API: MYFS_REST_Group_Controller class
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
class MYFS_REST_Group_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'myfs-app/v1';
		$this->rest_base = 'groups';
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

		register_rest_route( $this->namespace, '/' . $this->rest_base .'/(?P<id>[\d]+)/follow', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_follow' ),
				'permission_callback' => array( $this, 'follow_item_permissions_check' ),
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
				'callback'            => array( $this, 'delete_follow' ),
				'permission_callback' => array( $this, 'follow_item_permissions_check' ),
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
	 * Checks whether a given request has permission to join groups.
	 *
	 * @since 4.7.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|true True if the request has read access, WP_Error object otherwise.
	 */
	public function follow_item_permissions_check( $request ) {
		if ( is_user_logged_in() )
			return true;
		else 
			return new WP_Error( 'user_not_logged_in', __( 'You need to be logged in to access this.' ), array( 'status' => 400 ) );
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
		global $members_template;

		$result = array();
		$args = array(
			'meta_query' => array(
		        array(
		            'key'     => 'type',
		            'value'   => 'research-project,app-group',
		            'compare' => 'IN'
		        )
		    ),
			'type'=>'alphabetical',
			'per_page'=>999
		);

		if ( !empty( $request['group_id'] ) ) {
			$args['include'] = array( $request['group_id'] );
		}
		
		$groups = BP_Groups_Group::get( $args );

		if ( empty( $groups ) ) {
			return null;
		}

		foreach ($groups['groups'] as $key => $value) {
			$meta_data = groups_get_groupmeta($value->id);
			$groups['groups'][$key]->meta_data = $meta_data;

			$new_group = new stdClass();
			$new_group->id = $value->id;
			$new_group->name = $value->name;
			$new_group->description = strip_tags( $value->description );
			$new_group->last_activity_time = strtotime($value->meta_data['last_activity'][0]);
			$new_group->avatar = myfs_core_prepare_avatar_url( bp_core_fetch_avatar( array( 'object'=>'group', 'item_id'=>$value->id, 'html'=>false ) ) );
			$new_group->is_member = false;
			$new_group->member_count = 0;
			//$new_group->members = $value->id;

			if ( bp_group_has_members( array( 'group_id'=>$value->id, 'exclude_admins_mods'=>false, 'per_page'=>0 ) ) ) {
				while ( bp_group_members() ) {
					bp_group_the_member();
					$new_member = new stdClass();
					$new_member->id = $members_template->member->user_id;
					$new_member->name = $members_template->member->display_name;
					$new_member->nicename = $members_template->member->user_nicename;
					$new_member->first_name = ( xprofile_get_field_data( 13, $new_member->id  ) ) ?: explode(' ', $new_member->name, 2)[0];
					$new_member->last_name = ( xprofile_get_field_data( 14, $new_member->id  ) ) ?: explode(' ', $new_member->name, 2)[1];
					$new_member->avatar = myfs_core_prepare_avatar_url( get_avatar_url($members_template->member->user_id) );
					$new_member->location = xprofile_get_field_data( 12, $new_member->id );
					$new_member->about = strip_tags( xprofile_get_field_data( 2, $new_member->id ) );
					if ( $members_template->member->last_activity != null )
						$new_member->last_activity = strtotime($members_template->member->last_activity);
					else 
						$new_member->last_activity = strtotime( get_userdata( $new_member->id )->user_registered );
					$new_group->members[] = $new_member;

					$new_group->member_count++;

					if ( $new_member->id == get_current_user_id() )
						$new_group->is_member = true;
				}
			}

			$new_group->city = $value->meta_data['city'][0];
			$new_group->state = $value->meta_data['state'][0];
			$result[] = $new_group;
		}

		return new WP_REST_Response( $result, 200 );
	}

	/**
	 * 
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function create_follow( $request ) {
		if ( !function_exists( 'groups_join_group' ) )
			return new WP_REST_Response( false, 400 );
	 
	    if ( !groups_join_group( $request['id'] ) )
			return new WP_REST_Response( false, 400 );
		
		return new WP_REST_Response( true, 200 );
	}

	/**
	 * 
	 * 
	 * @param array $request Options for the function.
	 * @return 
	 */
	public function delete_follow( $request ) {
		if ( !function_exists( 'groups_leave_group' ) ) 
			return new WP_REST_Response( false, 400 );

		if ( !groups_leave_group( $request['id'] ) )
			return new WP_REST_Response( false, 400 );

		return new WP_REST_Response( true, 200 );
	}
}
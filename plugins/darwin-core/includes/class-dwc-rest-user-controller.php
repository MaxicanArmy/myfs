<?php
/**
 * REST API: DWC_REST_ingestion_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * //Core class to access dwc specimens for ingestion in to other repositories.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */
class DWC_REST_User_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'darwin-core/v1';
		$this->rest_base = 'users';
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
				'args'                => array(),
			)
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
    return true;
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
		$records = array();

		$args = array(
			'number' => -1
		);

		// The Query
		$user_query = new WP_User_Query( $args );

		// User Loop
		if ( ! empty( $user_query->get_results() ) ) {
			foreach ( $user_query->get_results() as $user ) {
				$rUser = new stdClass();
				$rUser->name = $user->display_name;
				$rUser->url = bp_core_get_user_domain( $user->ID );
				$rUser->dwc_records_url = 'http://myfossil.staging.wpengine.com/wp-json/darwin-core/v1/records?user_id='.$user->ID;
				$records[] = $rUser;
			}
		}

		return new WP_REST_Response( $records, 200 );
	}
}

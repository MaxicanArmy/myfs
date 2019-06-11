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
class DWC_REST_Ingestion_Controller extends WP_REST_Controller {

	/**
	 * Constructor.
	 *
	 * @since 4.7.0
	 */
	public function __construct() {
		$this->namespace = 'darwin-core/v1';
		$this->rest_base = 'records';
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
		global $post;
    $records = new stdClass();

    $args = array(
      'posts_per_page' => -1,
      'post_type' => 'dwc_specimen',
			'order' => 'ASC',
			'orderby' => 'post_date'
    );

		if (!empty($request['user_id']))
			$args['author'] = $request['user_id'];

    $the_query = new WP_Query( $args );

    $records->itemCount = $the_query->post_count;
    $records->last_modified = "";
		$records->term_meta = array();
    $records->items = array();

    if ( $the_query->have_posts() ) {
      while ( $the_query->have_posts() ) {
    		$the_query->the_post();
        $specimen = new DarwinCoreSpecimen( get_the_ID() );
        $records->items[] = $specimen->ingestion_json();
      }
			$records->last_modified = $post->post_date_gmt;
			$records->term_meta = $specimen->ingestion_json_meta();
      wp_reset_postdata();
    }

		return new WP_REST_Response( $records, 200 );
	}
}

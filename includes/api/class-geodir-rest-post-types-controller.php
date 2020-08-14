<?php
/**
 * REST Post Types controller
 *
 * Handles requests to the post types endpoints.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0.0
 */
 
class GeoDir_REST_Post_Types_Controller extends WP_REST_Post_Types_Controller {

	/**
	 * GeoDirectory Post types.
	 *
	 * @access protected
	 * @var array
	 */
	protected $gd_post_types;
    
	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
        $this->namespace        = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
        $this->rest_base        = 'types';

        $this->gd_post_types    = geodir_get_posttypes();
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<type>[\w-]+)', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_item' ),
				'permission_callback' => '__return_true',
				'args'     => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Retrieves all public post types.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {
		$data = array();

		foreach ( get_post_types( array(), 'object' ) as $obj ) {
            if ( ! in_array( $obj->name, $this->gd_post_types ) || empty( $obj->show_in_rest ) || ( 'edit' === $request['context'] && ! current_user_can( $obj->cap->edit_posts ) ) ) {
				continue;
			}

			$post_type = $this->prepare_item_for_response( $obj, $request );
			$data[ $obj->name ] = $this->prepare_response_for_collection( $post_type );
		}

		return rest_ensure_response( $data );
	}

	/**
	 * Retrieves a specific post type.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$obj = get_post_type_object( $request['type'] );

		if ( empty( $obj ) || ! in_array( $obj->name, $this->gd_post_types ) ) {
			return new WP_Error( 'rest_type_invalid', __( 'Invalid post type.' ), array( 'status' => 404 ) );
		}

		if ( empty( $obj->show_in_rest ) ) {
			return new WP_Error( 'rest_cannot_read_type', __( 'Cannot view post type.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		if ( 'edit' === $request['context'] && ! current_user_can( $obj->cap->edit_posts ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to edit posts in this post type.' ), array( 'status' => rest_authorization_required_code() ) );
		}

		$data = $this->prepare_item_for_response( $obj, $request );

		return rest_ensure_response( $data );
	}

	/**
	 * Prepares a post type object for serialization.
	 *
	 * @access public
	 *
	 * @param stdClass        $post_type Post type data.
	 * @param WP_REST_Request $request   Full details about the request.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $post_type, $request ) {
		$taxonomies = wp_list_filter( get_object_taxonomies( $post_type->name, 'objects' ), array( 'show_in_rest' => true ) );
		$taxonomies = wp_list_pluck( $taxonomies, 'name' );
		$base = ! empty( $post_type->rest_base ) ? $post_type->rest_base : $post_type->name;
		$data = array(
			'capabilities' => $post_type->cap,
			'description'  => $post_type->description,
			'hierarchical' => $post_type->hierarchical,
			'labels'       => $post_type->labels,
			'name'         => $post_type->label,
			'slug'         => $post_type->name,
			'taxonomies'   => array_values( $taxonomies ),
			'rest_base'    => $base,
		);
		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( array(
			'collection' => array(
				'href'   => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
			),
			'https://api.w.org/items' => array(
				'href' => rest_url( sprintf( '%s/%s', $this->namespace, $base ) ),
			),
			'fields' => array(
				'href'   => rest_url( sprintf( '%s/%s/%s', $this->namespace, $base, 'fields' ) ),
			),
		) );

		/**
		 * Filters a post type returned from the API.
		 *
		 * Allows modification of the post type data right before it is returned.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param object           $item     The original post type object.
		 * @param WP_REST_Request  $request  Request used to generate the response.
		 */
		return apply_filters( 'rest_prepare_post_type', $response, $post_type, $request );
	}

	/**
	 * Retrieves the post type's schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'              => 'http://json-schema.org/schema#',
			'title'                => 'type',
			'type'                 => 'object',
			'properties'           => array(
				'capabilities'     => array(
					'description'  => __( 'All capabilities used by the post type.' ),
					'type'         => 'object',
					'context'      => array( 'edit' ),
					'readonly'     => true,
				),
				'description'      => array(
					'description'  => __( 'A human-readable description of the post type.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'hierarchical'     => array(
					'description'  => __( 'Whether or not the post type should have children.' ),
					'type'         => 'boolean',
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'labels'           => array(
					'description'  => __( 'Human-readable labels for the post type for various contexts.' ),
					'type'         => 'object',
					'context'      => array( 'edit' ),
					'readonly'     => true,
				),
				'name'             => array(
					'description'  => __( 'The title for the post type.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
				'slug'             => array(
					'description'  => __( 'An alphanumeric identifier for the post type.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
				'taxonomies'       => array(
					'description'  => __( 'Taxonomies associated with post type.' ),
					'type'         => 'array',
					'items'        => array(
						'type' => 'string',
					),
					'context'      => array( 'view', 'edit' ),
					'readonly'     => true,
				),
				'rest_base'            => array(
					'description'  => __( 'REST base route for the post type.' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit', 'embed' ),
					'readonly'     => true,
				),
			),
		);
		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Retrieves the query params for collections.
	 *
	 * @access public
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

}

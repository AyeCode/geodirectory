<?php

/**
 * Core class to access countries via the REST API.
 *
 * @since 2.0.0
 *
 * @see WP_REST_Taxonomies_Controller
 */
class GeoDir_REST_Countries_Controller extends WP_REST_Controller {

    /**
     * Constructor.
     *
     * @access public
     */
    public function __construct() {
        $this->namespace = GEODIR_REST_SLUG . '/v' . GEODIR_REST_API_VERSION;
		$this->rest_base = 'countries';
		$this->object_type = 'country';
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
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_items' ),
                'permission_callback' => array( $this, 'get_items_permissions_check' ),
                'args'            => $this->get_collection_params(),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<iso2>[\w-]+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_item' ),
                'permission_callback' => array( $this, 'get_item_permissions_check' ),
                'args'            => array(
                    'context'     => $this->get_context_param( array( 'default' => 'view' ) ),
                ),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    /**
     * Checks whether a given request has permission to read countries.
     *
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        if ( !$this->show_in_rest()) {
            return new WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view countries.' ), array( 'status' => rest_authorization_required_code() ) );
        }
        return true;
    }

    /**
     * Retrieves all public countries.
     *
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response Response object on success, or WP_Error object on failure.
     */
    public function get_items( $request ) {

        // Retrieve the list of registered collection query parameters.
        $registered = $this->get_collection_params();

        $args = array();

        if ( !empty( $request['order'] ) ) {
            $args['order'] = $request['order'];
        }

        if ( !empty( $request['orderby'] ) ) {
            $args['orderby'] = $request['orderby'];
        }

        if ( !empty( $request['search'] ) ) {
            $args['search'] = $request['search'];
        }
        $countries = $this->get_countries( $args );

        $data = array();
        foreach ( $countries as $country ) {
            $item   = $this->prepare_item_for_response( $country, $request );
            $data[] = $this->prepare_response_for_collection( $item );
        }

        if ( empty( $data ) ) {
            // Response should still be returned as a JSON object when it is empty.
            $data = (object) $data;
        }

        return rest_ensure_response( $data );
    }

    /**
     * Checks if a given request has access to a country.
     *
     * @access public
     *
     * @param  WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has read access for the item, otherwise false or WP_Error object.
     */
    public function get_item_permissions_check( $request ) {

        if ( !$this->show_in_rest() ) {
            return new WP_Error( 'rest_cannot_view', __( 'Sorry, you are not allowed to view countries.' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
    }

    /**
     * Retrieves a specific country.
     *
     * @access public
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item( $request ) {
        $country = NULL;

        if ( !empty( $request['iso2'] ) && !( absint( $request['iso2'] ) > 0 && $country = geodir_rest_country_by_id( $request['iso2'] ) ) ) {
            $country = geodir_rest_country_by_iso2( $request['iso2'] );
        }

        if ( empty( $country ) ) {
            return new WP_Error( 'rest_country_code_invalid', __( 'Invalid country id OR ISO2 code.' ), array( 'status' => 404 ) );
        }

        $data = $this->prepare_item_for_response( $country, $request );

        return rest_ensure_response( $data );
    }

    /**
     * Prepares a country object for serialization.
     *
     * @access public
     *
     * @param stdClass        $country Country data.
     * @param WP_REST_Request $request  Full details about the request.
     * @return WP_REST_Response Response object.
     */
    public function prepare_item_for_response( $country, $request ) {
        $data = array(
            'id'            => $country->id,
            'name'          => $country->name,
            'title'         => $country->title, // Translated
            'iso2'          => $country->iso2,
			'iso3'          => $country->iso3,
        );

        $context    = 'view';
        $data       = $this->add_additional_fields_to_object( $data, $request );
        $data       = $this->filter_response_by_context( $data, $context );

        // Wrap the data in a response object.
        $response = rest_ensure_response( $data );

        $response->add_links( array(
            'self'                => array(
                'href'                  => rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $country->id ) ),
            ),
            'collection'                => array(
                'href'                  => rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ),
            ),
        ) );

        /**
         * Filters a country returned from the REST API.
         *
         * @param WP_REST_Response $response The response object.
         * @param object           $country  The original country object.
         * @param WP_REST_Request  $request  Request used to generate the response.
         */
        return apply_filters( 'geodir_rest_prepare_country', $response, $country, $request );
    }

    /**
     * Retrieves the country's schema, conforming to JSON Schema.
     *
     * @access public
     *
     * @return array Item schema data.
     */
    public function get_item_schema() {
        $schema = array(
            '$schema'              => 'http://json-schema.org/schema#',
            'title'                => $this->object_type,
            'type'                 => 'object',
            'properties'           => array(
                'id'               => array(
                    'description'  => __( 'A human-readable description of the country.' ),
                    'type'         => 'integer',
                    'context'      => array( 'view' ),
                ),
                'name'             => array(
                    'description'  => __( 'The name for the country.' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'title'             => array(
                    'description'  => __( 'The translated name for the country.' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'iso2'             => array(
                    'description'  => __( 'The ISO2 code for the country.' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                ),
                'iso3'             => array(
                    'description'  => __( 'The ISO3 code for the country.' ),
                    'type'         => 'string',
                    'context'      => array( 'view' ),
                )
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
        $params = array();
        $params['context'] = $this->get_context_param( array( 'default' => 'view' ) );
        $params['search'] = array(
            'description'        => __( 'Limit results to those matching a string.' ),
            'type'               => 'string',
            'sanitize_callback'  => 'sanitize_text_field',
            'validate_callback'  => 'rest_validate_request_arg',
        );

        return $params;
    }

    /**
     * Show in rest.
     *
     * @since 2.0.0
     *
     * @return bool
     */
    public function show_in_rest() {
        return apply_filters( 'geodir_rest_countries_show_in_rest', true, $this );
    }

    /**
     * Get countries.
     *
     * @since 2.0.0
     *
     * @return array $countries.
     */
	public function get_countries() {
		$countries = geodir_rest_get_countries( $args );

		return $countries;
	}

}

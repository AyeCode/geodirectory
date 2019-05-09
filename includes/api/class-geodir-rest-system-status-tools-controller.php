<?php
/**
 * REST API GD System Status Tools Controller
 *
 * Handles requests to the /system_status/tools/* endpoints.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package GeoDirectory/API
 * @extends GeoDir_REST_Controller
 */
class GeoDir_REST_System_Status_Tools_Controller extends GeoDir_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'geodir/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'system_status/tools';

	/**
	 * Register the routes for /system_status/tools/*.
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'geodirectory' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Check whether a given request has permission to view system status tools.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! geodir_rest_check_manager_permissions( 'system_status', 'read' ) ) {
			return new WP_Error( 'geodir_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check whether a given request has permission to view a specific system status tool.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_item_permissions_check( $request ) {
		if ( ! geodir_rest_check_manager_permissions( 'system_status', 'read' ) ) {
			return new WP_Error( 'geodir_rest_cannot_view', __( 'Sorry, you cannot view this resource.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check whether a given request has permission to execute a specific system status tool.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! geodir_rest_check_manager_permissions( 'system_status', 'edit' ) ) {
			return new WP_Error( 'geodir_rest_cannot_update', __( 'Sorry, you cannot update resource.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	

	/**
	 * Get a list of system status tools.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$tools = array();
		$tools_controller = new GeoDir_Admin_Tools();
		foreach ( $tools_controller->get_tools() as $id => $tool ) {
			$tools[] = $this->prepare_response_for_collection( $this->prepare_item_for_response( array(
				'id'          => $id,
				'name'        => $tool['name'],
				'action'      => $tool['button'],
				'description' => $tool['desc'],
			), $request ) );
		}

		$response = rest_ensure_response( $tools );
		return $response;
	}

	/**
	 * Return a single tool.
	 *
	 * @param  WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$tools_controller = new GeoDir_Admin_Tools();
		$tools = $tools_controller->get_tools();
		if ( empty( $tools[ $request['id'] ] ) ) {
			return new WP_Error( 'geodir_rest_system_status_tool_invalid_id', __( 'Invalid tool ID.', 'geodirectory' ), array( 'status' => 404 ) );
		}
		$tool = $tools[ $request['id'] ];
		return rest_ensure_response( $this->prepare_item_for_response( array(
			'id'          => $request['id'],
			'name'        => $tool['name'],
			'action'      => $tool['button'],
			'description' => $tool['desc'],
		), $request ) );
	}

	/**
	 * Update (execute) a tool.
	 * @param  WP_REST_Request $request
	 * @return WP_Error|WP_REST_Response
	 */
	public function update_item( $request ) {
		$tools_controller = new GeoDir_Admin_Tools();
		$tools = $tools_controller->get_tools();
		if ( empty( $tools[ $request['id'] ] ) ) {
			return new WP_Error( 'geodir_rest_system_status_tool_invalid_id', __( 'Invalid tool ID.', 'geodirectory' ), array( 'status' => 404 ) );
		}

		$tool = $tools[ $request['id'] ];
		$tool = array(
		   'id'          => $request['id'],
		   'name'        => $tool['name'],
		   'action'      => $tool['button'],
		   'description' => $tool['desc'],
		);

		$execute_return = $tools_controller->execute_tool( $request['id'] );
		$tool = array_merge( $tool, $execute_return );

		$request->set_param( 'context', 'edit' );
		$response = $this->prepare_item_for_response( $tool, $request );
		return rest_ensure_response( $response );
	}

	/**
	 * Prepare a tool item for serialization.
	 *
	 * @param  array $item Object.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $item, $request ) {
		$context = empty( $request['context'] ) ? 'view' : $request['context'];
		$data    = $this->add_additional_fields_to_object( $item, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item['id'] ) );

		return $response;
	}

	/**
	 * Get the system status tools schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'system_status_tool',
			'type'       => 'object',
			'properties' => array(
				'id'               => array(
					'description'  => __( 'A unique identifier for the tool.', 'geodirectory' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'name'            => array(
					'description'  => __( 'Tool name.', 'geodirectory' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'action'            => array(
					'description'  => __( 'What running the tool will do.', 'geodirectory' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'description'      => array(
					'description'  => __( 'Tool description.', 'geodirectory' ),
					'type'         => 'string',
					'context'      => array( 'view', 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'success'      => array(
					'description'  => __( 'Did the tool run successfully?', 'geodirectory' ),
					'type'         => 'boolean',
					'context'      => array( 'edit' ),
				),
				'message'      => array(
					'description'  => __( 'Tool return message.', 'geodirectory' ),
					'type'         => 'string',
					'context'      => array( 'edit' ),
					'arg_options'  => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param string $id
	 * @return array
	 */
	protected function prepare_links( $id ) {
		$base  = '/' . $this->namespace . '/' . $this->rest_base;
		$links = array(
			'item' => array(
				'href'       => rest_url( trailingslashit( $base ) . $id ),
				'embeddable' => true,
			),
		);

		return $links;
	}

	/**
	 * Get any query params needed.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}
   
}

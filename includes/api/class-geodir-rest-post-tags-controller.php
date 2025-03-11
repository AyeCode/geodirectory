<?php
/**
 * REST API GeoDirectory Post Tags controller
 *
 * Handles requests to the tags endpoint.
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
 * REST API Post Tags controller class.
 *
 * @package GeoDirectory/API
 * @extends GeoDir_REST_Post_Categories_Controller
 */
class GeoDir_REST_Post_Tags_Controller extends GeoDir_REST_Terms_Controller {

		/**
	 * Prepares a single term output for response.
	 *
	 * @since 2.0.0
	 *
	 * @param obj             $item    Term object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {

		$schema = $this->get_item_schema();
		$data   = array();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = (int) $item->term_id;
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = $item->name;
		}

		if ( ! empty( $schema['properties']['slug'] ) ) {
			$data['slug'] = $item->slug;
		}

		if ( ! empty( $schema['properties']['taxonomy'] ) ) {
			$data['taxonomy'] = $this->taxonomy;
		}

		if ( ! empty( $schema['properties']['count'] ) ) {
			$data['count'] = (int) $item->count;
		}

		if ( ! empty( $schema['properties']['description'] ) ) {
			$data['description'] = $item->description;
		}

		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $item->parent;
		}

		if ( ! empty( $schema['properties']['link'] ) ) {
			$data['link'] = get_term_link( $item );
		}

		if ( ! empty( $schema['properties']['meta'] ) ) {
			$data['meta'] = $this->meta->get_value( $item->term_id, $request );
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item, $request ) );

		/**
		 * Filters a term item returned from the API.
		 *
		 * The dynamic portion of the hook name, `$this->taxonomy`, refers to the taxonomy slug.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @since 2.0.0
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original term object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( "rest_prepare_{$this->taxonomy}", $response, $item, $request );
	}
}

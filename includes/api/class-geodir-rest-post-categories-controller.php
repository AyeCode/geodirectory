<?php
/**
 * REST API Post Categories controller
 *
 * Handles requests to the categories endpoint.
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
 * REST API Post Categories controller class.
 *
 * @package GeoDirectory/API
 * @extends GeoDir_REST_Post_Categories_Controller
 */
class GeoDir_REST_Post_Categories_Controller extends GeoDir_REST_Terms_Controller {

		/**
	 * Prepares a single term output for response.
	 *
	 * @since 2.0.0
	 *
	 * @param object             $item    Term object.
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
		
		if ( ! empty( $schema['properties']['image'] ) ) {
			$image = array();

			// Get category image.
			$attachment_data = get_term_meta( $item->term_id, 'ct_cat_default_img', true );
			if ( is_array( $attachment_data ) && ! empty( $attachment_data['id'] ) && ( $attachment_id = $attachment_data['id'] ) ) {
				$attachment = get_post( $attachment_id );

				if ( ! empty( $attachment ) ) {
					$image = array(
						'id'                => (int) $attachment_id,
						'date_created'      => geodir_rest_prepare_date_response( $attachment->post_date ),
						'date_created_gmt'  => geodir_rest_prepare_date_response( $attachment->post_date_gmt ),
						'date_modified'     => geodir_rest_prepare_date_response( $attachment->post_modified ),
						'date_modified_gmt' => geodir_rest_prepare_date_response( $attachment->post_modified_gmt ),
						'src'               => wp_get_attachment_url( $attachment_id ),
						'title'             => get_the_title( $attachment ),
						'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
					);
				}
			}
			
			$data['image'] = $image;
		}
		
		if ( ! empty( $schema['properties']['icon'] ) ) {
			$icon = array();

			// Get category image.
			$attachment_data = get_term_meta( $item->term_id, 'ct_cat_icon', true );
			if ( is_array( $attachment_data ) && ! empty( $attachment_data['id'] ) && ( $attachment_id = $attachment_data['id'] ) ) {
				$attachment = get_post( $attachment_id );

				if ( ! empty( $attachment ) ) {
					$icon = array(
						'id'                => (int) $attachment_id,
						'date_created'      => geodir_rest_prepare_date_response( $attachment->post_date ),
						'date_created_gmt'  => geodir_rest_prepare_date_response( $attachment->post_date_gmt ),
						'date_modified'     => geodir_rest_prepare_date_response( $attachment->post_modified ),
						'date_modified_gmt' => geodir_rest_prepare_date_response( $attachment->post_modified_gmt ),
						'src'               => wp_get_attachment_url( $attachment_id ),
						'title'             => get_the_title( $attachment ),
						'alt'               => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
					);
				}
			}
			
			$data['icon'] = $icon;
		}

		if ( ! empty( $schema['properties']['fa_icon'] ) ) {
			$data['fa_icon'] = get_term_meta( $item->term_id, 'ct_cat_font_icon', true );
		}

		if ( ! empty( $schema['properties']['fa_icon_color'] ) ) {
			$data['fa_icon_color'] = get_term_meta( $item->term_id, 'ct_cat_color', true );
		}

		if ( ! empty( $schema['properties']['schema'] ) ) {
			$data['schema'] = get_term_meta( $item->term_id, 'ct_cat_schema', true );
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
	
	/**
	 * Additional fields for categories.
	 *
	 * @since 2.0.0
	 *
	 * @return array Item schema data.
	 */
	public function add_additional_fields_schema( $schema ) {

		$schema['properties']['image'] = array(
			'description' => __( 'Category image data.', 'geodirectory' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'id' => array(
					'description' => __( 'Image ID.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'date_created' => array(
					'description' => __( "The date the image was created, in the site's timezone.", 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created_gmt' => array(
					'description' => __( 'The date the image was created, as GMT.', 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified' => array(
					'description' => __( "The date the image was last modified, in the site's timezone.", 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified_gmt' => array(
					'description' => __( 'The date the image was last modified, as GMT.', 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'src' => array(
					'description' => __( 'Image URL.', 'geodirectory' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
				),
				'title' => array(
					'description' => __( 'Image name.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'alt' => array(
					'description' => __( 'Image alternative text.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);
				
		$schema['properties']['icon'] = array(
			'description' => __( 'Category icon data.', 'geodirectory' ),
			'type'        => 'object',
			'context'     => array( 'view' ),
			'properties'  => array(
				'id' => array(
					'description' => __( 'Image ID.', 'geodirectory' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'date_created' => array(
					'description' => __( "The date the image was created, in the site's timezone.", 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_created_gmt' => array(
					'description' => __( 'The date the image was created, as GMT.', 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified' => array(
					'description' => __( "The date the image was last modified, in the site's timezone.", 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'date_modified_gmt' => array(
					'description' => __( 'The date the image was last modified, as GMT.', 'geodirectory' ),
					'type'        => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'src' => array(
					'description' => __( 'Image URL.', 'geodirectory' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view' ),
				),
				'title' => array(
					'description' => __( 'Image name.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
				'alt' => array(
					'description' => __( 'Image alternative text.', 'geodirectory' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
				),
			),
		);

		$schema['properties']['fa_icon'] = array(
			'description'  => __( 'The category font-awesome icon.', 'geodirectory' ),
			'type'         => 'string',
			'context'      => array( 'view' ),
		);

		$schema['properties']['fa_icon_color'] = array(
			'description'  => __( 'The category font-awesome icon color.', 'geodirectory' ),
			'type'         => 'string',
			'context'      => array( 'view' ),
		);
		
		$schema['properties']['schema'] = array(
			'description'  => __( 'The schema type of the category.', 'geodirectory' ),
			'type'         => 'string',
			'context'      => array( 'view' ),
		);

		return $schema;
	}
}

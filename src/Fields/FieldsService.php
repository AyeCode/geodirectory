<?php
namespace AyeCode\GeoDirectory\Fields;

use AyeCode\GeoDirectory\Core\Container;
use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;

class FieldsService {

	/**
	 * @var CustomFieldRepository
	 */
	protected $repository;

	/**
	 * @var FieldRegistry
	 */
	protected $registry;

	public function __construct( CustomFieldRepository $repository, FieldRegistry $registry ) {
		$this->repository = $repository;
		$this->registry   = $registry;
	}

	/**
	 * Render all custom fields for a specific location/context.
	 * * Replaces: geodir_get_custom_fields_html()
	 *
	 * @param int    $post_id    The ID of the post (if editing) or 0.
	 * @param string $post_type  The CPT slug (gd_place, etc).
	 * @param string $location   Where are we? (listing_form, admin, search).
	 * @param string $package_id Current package ID (for visibility checks).
	 *
	 * @return void (Echoes HTML)
	 */
	public function render_fields( $post_id, $post_type, $location = 'listing', $package_id = '' ) {

		$args = [
			'post_type' => $post_type,
			'package_id'=> $package_id,
			'active'    => 1
		];

		// If specific location is requested (e.g. 'sidebar'), add it to query.
		// If 'admin', we generally want ALL fields so we can edit them.
		if ( $location !== 'admin' && $location !== 'all' ) {
			$args['location'] = $location;
		}

		$fields_data = $this->repository->get_fields( $args );

//		print_r($fields_data);

		// 2. Loop and Render
		foreach ( $fields_data as $field_data ) {

			// Skip logic (Hooks from old functions.php)
			$skip = apply_filters( 'geodir_post_custom_fields_skip_field', false, $field_data, $package_id, 'all', $location );
			if ( $skip ) {
//				echo $field_data['htmlvar_name'].'skip###<br>'."\n\n";
				continue;
			}

			// Resolve Class
			$field_class = $this->registry->get( $field_data['field_type'] );

			if ( $field_class && class_exists( $field_class ) ) {

				// Instantiate the specific Field Type
				$field = new $field_class( $field_data, $post_id );

				// Legacy Hook: Before
				do_action( 'geodir_before_custom_form_field_' . $field_data['htmlvar_name'], $post_type, $package_id, $field_data );

				// RENDER
				// We might want to capture this to a variable if we want to wrap it,
				// but for now we echo to match old behavior.
//				echo $field_data['htmlvar_name'].'###<br>'."\n\n";

				echo $field->render_input();

				// Legacy Hook: After
				do_action( 'geodir_after_custom_form_field_' . $field_data['htmlvar_name'], $post_type, $package_id, $field_data );
			}else{
				echo $field_data['htmlvar_name'].' NO CLASS###'.$field_data['field_type'].'<br>'."\n\n";
			}
		}
	}
}

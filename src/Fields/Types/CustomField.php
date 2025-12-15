<?php
/**
 * Custom Field Type
 *
 * Handles custom field types that are entirely filter-driven.
 * This is used for special field types like distanceto, map_directions, twitter_feed, etc.
 * that don't have built-in rendering logic and rely on filters to generate output.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Custom field type class.
 *
 * @since 3.0.0
 */
class CustomField extends AbstractFieldType {

	/**
	 * Render the input HTML for custom field types.
	 *
	 * Custom fields typically have custom rendering via filters.
	 * This method provides a basic fallback but expects filters to override it.
	 *
	 * @return string HTML input.
	 */
	public function render_input(): string {
		// Custom fields should have their own input rendering via filters
		// This is a fallback that should rarely be used
		$html = '';

		/**
		 * Filter custom field input rendering.
		 *
		 * @since 2.0.0
		 *
		 * @param string $html       The HTML output (initially empty).
		 * @param array  $field_data Field configuration array.
		 * @param int    $post_id    Post ID.
		 */
		$html = apply_filters( 'geodir_custom_field_input_custom', $html, $this->field_data, $this->post_id );
		$html = apply_filters( 'geodir_custom_field_input_custom_var_' . $this->field_data['htmlvar_name'], $html, $this->field_data, $this->post_id );

		if ( ! empty( $html ) ) {
			return $html;
		}

		// Ultimate fallback - hidden input
		return '<input type="hidden" name="' . esc_attr( $this->field_data['htmlvar_name'] ) . '" value="' . esc_attr( $this->value ) . '" />';
	}

	/**
	 * Render the output HTML for custom field types.
	 *
	 * For custom fields, the filter system does ALL the work.
	 * This method simply applies the filters and returns the result.
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$output = $this->parse_output_args( $args['show'] );

		// For custom fields, let filters do ALL the work
		// Start with empty HTML - filters will populate it
		$html = '';

		// Apply all filters (base, location, var, key)
		// The base filter "geodir_custom_field_output_custom" is the critical one
		$html = $this->apply_output_filters( $html, $location, $output );

		return $html;
	}

	public function get_value() {
		return '';
	}
}

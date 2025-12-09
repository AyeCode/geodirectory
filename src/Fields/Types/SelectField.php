<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class SelectField
 *
 * Handles Single Select dropdowns.
 * Replaces geodir_cfi_select() and geodir_cf_select().
 */
class SelectField extends AbstractFieldType {

	use RadioSelectOutputTrait;

	public function render_input() {
		$args = $this->get_aui_args();

		// Parse Options
		// Note: using the global helper function for backward compatibility with option string formats
		$options = geodir_string_to_options( $this->field_data['option_values'], true );
		$args['options'] = $options;

		// Select2 attributes
		$args['select2'] = true;
		$args['data-allow-clear'] = true;

		// Placeholder
		if ( empty( $args['placeholder'] ) ) {
			$args['placeholder'] = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), $args['label'] );
		}
		$args['extra_attributes']['data-placeholder'] = $args['placeholder'];
		$args['extra_attributes']['option-ajaxchosen'] = 'false';

		// Allow field-specific filters
		$html = '';
		$hook_name = "geodir_custom_field_input_select_{$this->field_data['htmlvar_name']}";
		if ( has_filter( $hook_name ) ) {
			$html = apply_filters( $hook_name, $html, $this->field_data );
		}

		if ( empty( $html ) ) {
			$html = aui()->select( $args );
		}

		return $html;
	}

	public function sanitize( $value ) {
		// Basic string sanitization. Could validate against option keys if strictness is required.
		return sanitize_text_field( $value );
	}
}

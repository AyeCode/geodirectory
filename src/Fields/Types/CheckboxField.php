<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class CheckboxField
 *
 * Handles single checkbox inputs and outputs.
 * Replaces geodir_cfi_checkbox() and geodir_cf_checkbox().
 */
class CheckboxField extends AbstractFieldType {

	use CheckboxFieldOutputTrait;

	public function render_input() {
		// Admin check for terms (Legacy)
		if ( is_admin() && isset( $this->field_data['field_type_key'] ) && $this->field_data['field_type_key'] == 'terms_conditions' ) {
			return '';
		}

		$args = $this->get_aui_args();

		// Checkbox specific settings
		$args['type']  = 'checkbox';
		$args['value'] = '1'; // The value sent when checked

		// Determine checked state
		// Logic: if value is '1' or default is set and value is empty
		$is_checked = ( $this->value == '1' );
		if ( $this->value === '' && ! empty( $this->field_data['default_value'] ) ) {
			$is_checked = true;
		}
		$args['checked'] = $is_checked;

		// Hidden field to ensure '0' is sent if unchecked
		$html = '<input type="hidden" name="' . esc_attr( $args['name'] ) . '" id="checkbox_' . esc_attr( $args['name'] ) . '" value="0"/>';

		// Special handling for Terms & Conditions help text
		if ( isset( $this->field_data['field_type_key'] ) && $this->field_data['field_type_key'] == 'terms_conditions' ) {
			$tc_id     = geodir_terms_and_conditions_page_id();
			$tc_link   = get_permalink( $tc_id );
			$args['help_text'] = "<a href='$tc_link' target='_blank'>" . $args['help_text'] . " <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\"></i></a>";
		}

		$hook_name = "geodir_custom_field_input_checkbox_{$this->field_data['htmlvar_name']}";
		$custom_html = apply_filters( $hook_name, '', $this->field_data );

		if ( ! empty( $custom_html ) ) {
			return $custom_html;
		}

		return $html . aui()->input( $args );
	}

	public function sanitize( $value ) {
		return $value === '1' ? 1 : 0;
	}
}

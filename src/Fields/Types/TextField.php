<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;
use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldOutput;

/**
 * Class TextField
 *
 * Handles Text, Email, Phone, and URL inputs and outputs.
 *
 * @package AyeCode\GeoDirectory\Fields\Types
 */
class TextField extends AbstractFieldType {

	use TextFieldOutputTrait;

	public function render_input() {
		// Don't show title in admin (WP handles it natively)
		if ( is_admin() && $this->field_data['htmlvar_name'] === 'post_title' ) {
			return '';
		}

		$args = $this->get_aui_args();
		$type = $this->field_data['field_type'];

		// Map DB field_type to HTML5 input type
		switch ( $type ) {
			case 'email':
				$args['type'] = 'email';
				// Blank value if default set for email (Logic from legacy input-functions-aui.php)
				if ( isset( $this->field_data['default_value'] ) && $this->value == $this->field_data['default_value'] ) {
					$args['value'] = '';
				}
				break;
			case 'phone':
			case 'tel':
				$args['type'] = 'tel';
				break;
			case 'url':
				$args['type'] = 'url';
				break;
			default:
				$args['type'] = 'text';

				// Handle INT/FLOAT data types stored in extra metadata
				// This replaces logic found in geodir_cfi_input_output
				if ( isset( $this->field_data['data_type'] ) ) {
					if ( $this->field_data['data_type'] === 'INT' ) {
						$args['type'] = 'number';
						$args['extra_attributes']['lang'] = 'EN';
					} elseif ( $this->field_data['data_type'] === 'FLOAT' ) {
						$args['type'] = 'text'; // 'float' isn't a valid HTML type, text or number+step is used
						$args['extra_attributes']['lang'] = 'EN';
					}
				}
				break;
		}

		// Allow field-specific filters (mimics legacy hooks like geodir_custom_field_input_text_{$name})
		$html = '';
		$hook_name = "geodir_custom_field_input_{$type}_{$this->field_data['htmlvar_name']}";
		if ( has_filter( $hook_name ) ) {
			$html = apply_filters( $hook_name, $html, $this->field_data );
		}

		if ( empty( $html ) ) {
			$html = aui()->input( $args );
		}

		return $html;
	}

	public function sanitize( $value ) {
		$type = $this->field_data['field_type'];

		if ( $type === 'email' ) {
			return sanitize_email( $value );
		}
		if ( $type === 'url' ) {
			return esc_url_raw( $value );
		}
		// Phone/Text fall through to default
		return parent::sanitize( $value );
	}
}

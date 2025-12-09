<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class RadioField
 *
 * Handles radio button groups.
 * Replaces geodir_cfi_radio() and geodir_cf_radio().
 */
class RadioField extends AbstractFieldType {

	use RadioSelectOutputTrait;

	public function render_input() {
		$args = $this->get_aui_args();

		// Parse Options
		// Flatten options for radio (remove optgroups if present as AUI radio doesn't usually support them the same way)
		$options_deep = geodir_string_to_options( $this->field_data['option_values'], true );
		$options = [];

		if ( ! empty( $options_deep ) ) {
			foreach ( $options_deep as $option ) {
				if ( empty( $option['optgroup'] ) ) {
					$options[ $option['value'] ] = $option['label'];
				}
			}
		}
		$args['options'] = $options;
		$args['type']    = 'radio';

		// JS Validation Logic for Radio Groups (Required handling)
		if ( $args['required'] ) {
			$cf_name = esc_attr( $args['name'] );
			$msg = esc_attr( addslashes( $args['validation_text'] ) );

			$args['extra_attributes']['onchange'] = "if(jQuery('input[name=\"{$cf_name}\"]:checked').length || !jQuery('input#{$cf_name}0').is(':visible')){jQuery('#{$cf_name}0').removeAttr('required')}else{jQuery('#{$cf_name}0').attr('required',true)}";
			$args['extra_attributes']['oninput'] = "try{document.getElementById('{$cf_name}0').setCustomValidity('')}catch(e){}";
			$args['extra_attributes']['oninvalid'] = "try{document.getElementById('{$cf_name}0').setCustomValidity('{$msg}')}catch(e){}";

			// AUI radio usually appends '0', '1' etc to ID for individual options
		}

		$hook_name = "geodir_custom_field_input_radio_{$this->field_data['htmlvar_name']}";
		$custom_html = apply_filters( $hook_name, '', $this->field_data );

		if ( ! empty( $custom_html ) ) {
			return $custom_html;
		}

		return aui()->radio( $args );
	}
}

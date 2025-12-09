<?php
/**
 * Radio/Select Field Output Rendering Trait
 *
 * Handles output rendering for radio and select fields (single selection).
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Radio/Select field output methods.
 *
 * @since 3.0.0
 */
trait RadioSelectOutputTrait {

	/**
	 * Render the output HTML for radio/select field types.
	 *
	 * Replaces: geodir_cf_radio(), geodir_cf_select()
	 *
	 * @param object|array $gd_post GeoDirectory post object with custom fields already loaded.
	 * @param array        $args    Output arguments:
	 *                              - 'show' (string|array): What to display.
	 *                              - 'location' (string): Output location.
	 * @return string
	 */
	public function render_output( $gd_post, $args = [] ) {
		// Use the $gd_post directly (no DB call needed - already has all custom fields!)
		if ( ! is_object( $gd_post ) ) {
			$gd_post = (object) $gd_post;
		}

		if ( empty( $gd_post ) ) {
			return '';
		}

		// Parse args with defaults
		$args = wp_parse_args( $args, [
			'show'     => '',
			'location' => '',
		] );

		$location = $args['location'];
		$html_var = $this->field_data['htmlvar_name'];
		$field_type = $this->field_data['field_type'];

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post->{$html_var} = 'Yes';
		}

		// Get field value
		$value = isset( $gd_post->{$html_var} ) ? $gd_post->{$html_var} : '';

		// Empty value check
		if ( $value === '' || $value === null ) {
			return '';
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return stripslashes_deep( $value );
		}

		// Get display label from option_values if available
		$html_val = __( $value, 'geodirectory' );

		if ( ! empty( $this->field_data['option_values'] ) ) {
			$cf_option_values = geodir_string_to_options( $this->field_data['option_values'], true );

			if ( ! empty( $cf_option_values ) ) {
				foreach ( $cf_option_values as $cf_option_value ) {
					if ( isset( $cf_option_value['value'] ) && $cf_option_value['value'] == $value ) {
						$html_val = $cf_option_value['label'];
						break;
					}
				}
			}
		}

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return stripslashes_deep( $html_val );
		}

		// Build full HTML output
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';
		$icon_class = $field_type === 'radio' ? 'geodir-i-radio' : 'geodir-i-select';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon ' . esc_attr( $icon_class ) . '" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
		}

		// Label
		if ( $output == '' || isset( $output['label'] ) ) {
			$frontend_title = isset( $this->field_data['frontend_title'] ) ? trim( $this->field_data['frontend_title'] ) : '';
			if ( $frontend_title ) {
				$html .= '<span class="geodir_post_meta_title ' . esc_attr( $maybe_secondary_class ) . '">' . __( $frontend_title, 'geodirectory' ) . ': </span>';
			}
		}

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}

		// Value
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= stripslashes( $html_val );
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Helper methods from AbstractFieldOutput.
	 */
	abstract protected function parse_output_args( $args );
	abstract protected function apply_output_filters( $html, $location, $output );
	abstract protected function process_icon();
	abstract protected function is_block_demo();
}

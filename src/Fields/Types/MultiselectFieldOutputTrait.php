<?php
/**
 * Multiselect Field Output Rendering Trait
 *
 * Handles output rendering for multiselect fields (multiple selection).
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * Multiselect field output methods.
 *
 * @since 3.0.0
 */
trait MultiselectFieldOutputTrait {

	/**
	 * Render the output HTML for multiselect field type.
	 *
	 * Replaces: geodir_cf_multiselect()
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

		// Parse output arguments (convert string to array)
		$output = $this->parse_output_args( $args['show'] );

		// Block demo content
		if ( $this->is_block_demo() ) {
			$gd_post->{$html_var} = 'Swimming Pool,WiFi,Sea View';
		}

		// Get field value
		$value = isset( $gd_post->{$html_var} ) ? $gd_post->{$html_var} : '';

		// Empty value check
		if ( empty( $value ) ) {
			return '';
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Convert array to string if needed
		if ( is_array( $value ) ) {
			$value = implode( ', ', $value );
		}

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return stripslashes( $value );
		}

		// Parse field values (comma-separated)
		$field_values = explode( ',', trim( $value, ',' ) );

		if ( is_array( $field_values ) ) {
			$field_values = array_map( 'trim', $field_values );
		}

		// Get display labels from option_values if available
		$option_values = [];
		if ( ! empty( $this->field_data['option_values'] ) ) {
			$cf_option_values = geodir_string_to_options( $this->field_data['option_values'], true );

			if ( ! empty( $cf_option_values ) ) {
				foreach ( $cf_option_values as $cf_option_value ) {
					if ( isset( $cf_option_value['value'] ) && in_array( $cf_option_value['value'], $field_values ) ) {
						$option_values[] = $cf_option_value['label'];
					}
				}
			}
		}

		// If no labels found, use raw values
		if ( empty( $option_values ) ) {
			$option_values = $field_values;
		}

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return stripslashes( implode( ', ', $option_values ) );
		}

		// Build full HTML output
		$design_style = $this->get_design_style();
		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';
		$show_as_csv = $design_style && strpos( $css_class, 'gd-comma-list' ) !== false;

		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		// Build list of values
		$field_value = '';
		if ( count( $option_values ) ) {
			$ul_class = $show_as_csv ? 'list-inline d-inline' : '';
			$field_value .= '<ul class="' . esc_attr( $ul_class ) . '">';

			$li_count = 0;
			$total = count( $option_values );

			foreach ( $option_values as $val ) {
				$li_class = '';

				if ( $show_as_csv && $total > 1 && $total != $li_count + 1 ) {
					$val = $val . ',';
					$li_class = ' mx-0 pr-1 pr-0 pl-0 ps-0 d-inline-block';
				}

				$field_value .= '<li class="geodir-fv-' . sanitize_html_class( sanitize_title_with_dashes( $val ) ) . esc_attr( $li_class ) . '">' . esc_html( $val ) . '</li>';
				$li_count++;
			}

			$field_value .= '</ul>';
		}

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
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
			$html .= stripslashes( $field_value );
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
	abstract protected function get_design_style();
}

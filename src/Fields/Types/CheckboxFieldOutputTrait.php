<?php
/**
 * CheckboxField Output Rendering Trait
 *
 * Handles output rendering for checkbox fields.
 *
 * @package GeoDirectory\Fields\Types
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Types;

/**
 * CheckboxField output methods.
 *
 * @since 3.0.0
 */
trait CheckboxFieldOutputTrait {

	/**
	 * Render the output HTML for checkbox field type.
	 *
	 * Replaces: geodir_cf_checkbox()
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
			$gd_post->{$html_var} = '1';
		}

		$html = '';

		// Apply custom filters first
		$html = $this->apply_output_filters( $html, $location, $output );

		// If filters provided custom HTML, return it
		if ( ! empty( $html ) ) {
			return $html;
		}

		// Only show if checkbox is checked (value = 1)
		if ( ! isset( $gd_post->{$html_var} ) || (int) $gd_post->{$html_var} !== 1 ) {
			return '';
		}

		// Get the display value
		$value = $gd_post->{$html_var};

		// Return raw database value
		if ( ! empty( $output['raw'] ) ) {
			return $value;
		}

		// Display text
		$html_val = $value == '1' ? __( 'Yes', 'geodirectory' ) : __( 'No', 'geodirectory' );

		// Return stripped value
		if ( ! empty( $output['strip'] ) ) {
			return $html_val;
		}

		// Build full HTML output
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-checkbox" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
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

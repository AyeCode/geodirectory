<?php
/**
 * Abstract Field Output Base Class
 *
 * Handles common output rendering logic for all field types.
 *
 * @package GeoDirectory\Fields\Abstracts
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Fields\Abstracts;

/**
 * Base class for field output rendering.
 *
 * @since 3.0.0
 */
abstract class AbstractFieldOutput {

	/**
	 * The field data.
	 *
	 * @var array
	 */
	protected $field_data;

	/**
	 * The post object or ID.
	 *
	 * @var mixed
	 */
	protected $post;

	/**
	 * The current field value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Process icon for output.
	 *
	 * Returns processed icon HTML or style attribute.
	 * Replaces: geodir_field_icon_proccess()
	 *
	 * @return array Array with 'style' and 'icon_html' keys.
	 */
	protected function process_icon() {
		$field_icon = isset( $this->field_data['field_icon'] ) ? $this->field_data['field_icon'] : '';
		$field_icon = apply_filters( 'geodir_custom_field_icon', $field_icon, $this->field_data, $this->post );

		$result = [
			'style'     => '',
			'icon_html' => ''
		];

		if ( strpos( $this->field_data['field_icon'], 'http' ) !== false ) {
			$result['style'] = ' background: url(' . esc_url( $this->field_data['field_icon'] ) . ') no-repeat left center;background-size:18px 18px;padding-left: 21px;';
		} elseif ( strpos( $this->field_data['field_icon'], 'fa-' ) !== false ) {
			$result['icon_html'] = '<i class="' . esc_attr( $this->field_data['field_icon'] ) . ' fa-fw" aria-hidden="true"></i> ';
		} else {
			$result['icon_html'] = esc_attr( $this->field_data['field_icon'] );
		}

		return $result;
	}

	/**
	 * Parse output argument string.
	 *
	 * @param string|array $output Output argument.
	 * @return array Parsed output settings.
	 */
	protected function parse_output_args( $output ) {
		if ( is_array( $output ) ) {
			return $output;
		}

		return geodir_field_output_process( $output );

	}

	/**
	 * Apply output filters by location, htmlvar_name, and field_type_key.
	 *
	 * Maintains backwards compatibility with v2 filter structure.
	 *
	 * @param string $html     Current HTML.
	 * @param string $location Output location.
	 * @param array  $output   Output args.
	 * @return string Filtered HTML.
	 */
	protected function apply_output_filters( $html, $location, $output ) {
		$field_type = $this->field_data['field_type'];
		$html_var = $this->field_data['htmlvar_name'];
		$field_type_key = isset( $this->field_data['field_type_key'] ) ? $this->field_data['field_type_key'] : '';

		// Location-specific filter
		if ( has_filter( "geodir_custom_field_output_{$field_type}_loc_{$location}" ) ) {
			$html = apply_filters( "geodir_custom_field_output_{$field_type}_loc_{$location}", $html, $this->field_data, $output );
		}

		// Htmlvar-specific filter
		if ( has_filter( "geodir_custom_field_output_{$field_type}_var_{$html_var}" ) ) {
			$html = apply_filters( "geodir_custom_field_output_{$field_type}_var_{$html_var}", $html, $location, $this->field_data, $output );
		}

		// Field type key filter
		if ( $field_type_key && has_filter( "geodir_custom_field_output_{$field_type}_key_{$field_type_key}" ) ) {
			$html = apply_filters( "geodir_custom_field_output_{$field_type}_key_{$field_type_key}", $html, $location, $this->field_data, $output );
		}

		return $html;
	}

	/**
	 * Build the wrapper HTML for field output.
	 *
	 * @param string $content      The field value content.
	 * @param array  $output       Output args.
	 * @param string $default_icon Default icon if none specified.
	 * @return string Complete HTML wrapper.
	 */
	protected function build_output_wrapper( $content, $output, $default_icon = '' ) {
		$icon_data = $this->process_icon();
		$field_icon_style = $icon_data['style'];
		$field_icon_html = $icon_data['icon_html'];

		// Use default icon if no custom icon set
		if ( ! $field_icon_html && ! $field_icon_style && $default_icon ) {
			$field_icon_html = $default_icon;
		}

		$html_var = $this->field_data['htmlvar_name'];
		$css_class = isset( $this->field_data['css_class'] ) ? $this->field_data['css_class'] : '';
		$field_type = $this->field_data['field_type'];

		$html = '<div class="geodir_post_meta ' . esc_attr( $css_class ) . ' geodir-field-' . esc_attr( $html_var ) . '">';

		$maybe_secondary_class = isset( $output['icon'] ) ? 'gv-secondary' : '';

		// Icon
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-' . esc_attr( $field_type ) . '" style="' . esc_attr( $field_icon_style ) . '">' . $field_icon_html;
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
			$html .= $content;
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Check if this is block demo content.
	 *
	 * @return bool
	 */
	protected function is_block_demo() {
		return function_exists( 'geodir_is_block_demo' ) && geodir_is_block_demo();
	}

	/**
	 * Get design style setting.
	 *
	 * @return bool
	 */
	protected function get_design_style() {
		return function_exists( 'geodir_design_style' ) ? geodir_design_style() : false;
	}
}

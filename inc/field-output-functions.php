<?php
/**
 * Field Output Helper Functions
 *
 * Wrapper functions for rendering field outputs on the frontend.
 *
 * @package GeoDirectory
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render field output HTML.
 *
 * Replaces: geodir_cf_text(), geodir_cf_email(), geodir_cf_phone(), etc.
 *
 * @param array|string $field Field data array or htmlvar_name.
 * @param mixed        $post  Post ID or post object.
 * @param array        $args  Output arguments:
 *                            - 'location' (string): Output location (detail, listing, etc). Default ''.
 *                            - 'show' (string|array): What to display (icon, label, value, raw, strip, etc). Default ''.
 *
 * @return string HTML output.
 */
function geodir_render_field_output( $field, $post, $args = [] ) {
	return geodirectory()->fields->render_field_output( $field, $post, $args );
}

/**
 * Echo field output HTML.
 *
 * @param array|string $field Field data array or htmlvar_name.
 * @param mixed        $post  Post ID or post object.
 * @param array        $args  Output arguments.
 *
 * @return void
 */
function geodir_the_field_output( $field, $post, $args = [] ) {
	echo geodir_render_field_output( $field, $post, $args );
}

/**
 * Get field output value only (no HTML wrapper).
 *
 * @param array|string $field Field data array or htmlvar_name.
 * @param mixed        $post  Post ID or post object.
 * @param string       $mode  Output mode: 'raw' (database value) or 'strip' (formatted, no HTML).
 *
 * @return string Field value.
 */
function geodir_get_field_value( $field, $post, $mode = 'strip' ) {
	return geodir_render_field_output( $field, $post, [ 'show' => $mode ] );
}


/**
 * Get custom field extra fields meta.
 *
 * @since 2.3.110
 *
 * @param array|object $field Field data.
 * @param bool $stripslashes Apply stripslashes() or not. Default true.
 * @param bool $keep_serialize Keep serialized or not. Default false.
 * @return array|string Custom fields meta.
 */
function geodir_parse_cf_extra_fields( $field, $stripslashes = true, $keep_serialize = false ) {
	if ( empty( $field ) ) {
		return $field;
	}

	// Don't apply stripslashes to extra_fields.
	if ( is_array( $field ) && isset( $field['extra_fields'] ) ) {
		$extra_fields = $field['extra_fields'];
	} else if ( is_object( $field ) && isset( $field->extra_fields ) ) {
		$extra_fields = $field->extra_fields;
	} else {
		$extra_fields = array();
	}

	if ( ! empty( $extra_fields ) && is_serialized( $extra_fields ) ) {
		if ( $keep_serialize ) {
			return $extra_fields;
		}

		$extra_fields = maybe_unserialize( $extra_fields );
	}

	if ( ! empty( $extra_fields ) && $stripslashes ) {
		$extra_fields = stripslashes_deep( $extra_fields );
	}

	return $extra_fields;
}

/**
 * Process field icon for output.
 *
 * Backwards compatibility wrapper for the icon processing logic.
 * Returns processed icon HTML or style attribute string.
 *
 * @since 2.0.0
 * @since 3.0.0 Converted to wrapper function.
 *
 * @param array $cf Field configuration array with 'field_icon' key.
 * @return string Processed icon HTML or style attribute.
 */
function geodir_field_icon_proccess( $cf ) {
	if ( empty( $cf['field_icon'] ) ) {
		return '';
	}

	$field_icon = '';

	// URL-based icon (background image)
	if ( strpos( $cf['field_icon'], 'http' ) !== false ) {
		$field_icon = ' background: url(' . esc_url( $cf['field_icon'] ) . ') no-repeat left center;background-size:18px 18px;padding-left: 21px;';
	}
	// FontAwesome icon
	elseif ( strpos( $cf['field_icon'], 'fas fa-' ) !== false || strpos( $cf['field_icon'], 'fa-' ) !== false ) {
		$design_style = function_exists( 'geodir_design_style' ) ? geodir_design_style() : false;
		$field_icon = $design_style
			? '<i class="' . esc_attr( $cf['field_icon'] ) . ' fa-fw" aria-hidden="true"></i> '
			: '<i class="' . esc_attr( $cf['field_icon'] ) . '" aria-hidden="true"></i>';
	}
	// Plain text icon
	else {
		$field_icon = esc_attr( $cf['field_icon'] );
	}

	/**
	 * Filter the processed field icon.
	 *
	 * @since 2.0.0
	 *
	 * @param string $field_icon Processed icon HTML or style.
	 * @param array  $cf         Field configuration array.
	 */
	return apply_filters( 'geodir_field_icon_proccess', $field_icon, $cf );
}

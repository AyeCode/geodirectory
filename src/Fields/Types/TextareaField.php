<?php
namespace AyeCode\GeoDirectory\Fields\Types;

use AyeCode\GeoDirectory\Fields\Abstracts\AbstractFieldType;

/**
 * Class TextareaField
 *
 * Handles Textarea and HTML fields.
 * Replaces geodir_cfi_textarea(), geodir_cfi_html(), geodir_cf_textarea(), geodir_cf_html().
 */
class TextareaField extends AbstractFieldType {

	use TextareaFieldOutputTrait;

	public function render_input() {
		// WP handles post_content in admin
		if ( is_admin() && $this->field_data['htmlvar_name'] === 'post_content' ) {
			return '';
		}

		$args         = $this->get_aui_args();
		$extra_fields = $this->get_extra_fields();
		$is_html_type = $this->field_data['field_type'] === 'html';

		// Check for Advanced Editor setting
		$use_editor = ! empty( $extra_fields['advanced_editor'] );

		// Filter to allow disabling/enabling editor (Legacy support)
		$use_editor = apply_filters( 'geodir_custom_field_allow_html_editor', $use_editor, $this->field_data );

		// Configure WYSIWYG settings
		if ( $use_editor || $is_html_type ) {
			$args['wysiwyg'] = apply_filters( 'geodir_cfi_aui_textarea_wysiwyg_attributes', [ 'quicktags' => true ], $this->field_data );

			// If it's a WYSIWYG, we allow tags.
			$args['allow_tags'] = true;

			// Update extra attributes for validation
			$args['extra_attributes']['field_type'] = 'editor';
		} else {
			$args['wysiwyg'] = false;

			// Check if specific fields allow tags even without editor (e.g. 'video')
			$allow_tags = apply_filters( 'geodir_custom_field_textarea_allow_tags', ( $this->field_data['htmlvar_name'] === 'video' ), $this->field_data );
			$args['allow_tags'] = $allow_tags;
		}

		// Textarea specific defaults
		$args['rows']    = 8;
		$args['no_wrap'] = false;

		// Allow field-specific filters
		$html = '';
		$hook_name = "geodir_custom_field_input_{$this->field_data['field_type']}_{$this->field_data['htmlvar_name']}";
		if ( has_filter( $hook_name ) ) {
			$html = apply_filters( $hook_name, $html, $this->field_data );
		}

		if ( empty( $html ) ) {
			$html = aui()->textarea( $args );
		}

		return $html;
	}

	public function sanitize( $value ) {
		// If it allows HTML, use kses_post, otherwise sanitize textarea
		$extra_fields = $this->get_extra_fields();
		$use_editor   = ! empty( $extra_fields['advanced_editor'] ) || $this->field_data['field_type'] === 'html';

		if ( $use_editor ) {
			return wp_kses_post( $value );
		}

		return sanitize_textarea_field( $value );
	}
}

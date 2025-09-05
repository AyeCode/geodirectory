<?php
/**
 * Form Fields Helper
 *
 * Provides a library of static helper methods for generating the base
 * configuration arrays for various types of form fields. This class acts
 * as the foundational "blueprint" for all form inputs in the plugin.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class FormFields {

	/**
	 * Generates the base configuration for a text input.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function text( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'text',
			'id'          => '',
			'name'        => '',
			'label'       => '',
			'description' => '',
			'placeholder' => '',
			'default'     => '',
			'value'       => '',
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a password input.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function password( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'password' ], $overrides ) );
	}

	/**
	 * Generates the base configuration for a number input.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function number( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'number' ], $overrides ) );
	}

	/**
	 * Generates the base configuration for a textarea.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function textarea( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'textarea',
			'label'       => '',
			'description' => '',
			'placeholder' => '',
			'default'     => '',
			'rows'        => 3,
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a checkbox (toggle switch).
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function toggle( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'toggle',
			'label'       => '',
			'value'       => '1',
			'description' => '',
			'default'     => false,
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a select dropdown.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function select( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'select',
			'label'       => '',
			'description' => '',
			'options'     => [],
			'default'     => '',
			'class'       => '',
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a multiselect dropdown.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function multiselect( array $overrides = [] ): array {
		return self::select( array_merge( [ 'type' => 'multiselect', 'multiple' => true ], $overrides ) );
	}

	/**
	 * Generates the base configuration for an image uploader.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function image( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'image',
			'label'       => '',
			'description' => '',
			'default'     => '',
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a range slider.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function range( array $overrides = [] ): array {
		$defaults = [
			'type'        => 'range',
			'label'       => '',
			'description' => '',
			'default'     => 0,
			'min'         => 0,
			'max'         => 100,
			'step'        => 1,
			'searchable'  => [],
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for an informational alert box.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function alert( array $overrides = [] ): array {
		$defaults = [
			'type'       => 'alert',
			'alert_type' => 'info', // 'info', 'success', 'warning', 'danger'
			'description'=> '',
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a custom-rendered field.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function custom_renderer( array $overrides = [] ): array {
		$defaults = [
			'type'              => 'custom_renderer',
			'label'             => '',
			'description'       => '',
			'renderer_function' => '', // The global function to call for rendering.
		];
		return array_merge( $defaults, $overrides );
	}

	/**
	 * Generates the base configuration for a Font Awesome icon picker.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function font_awesome( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'font-awesome' ], $overrides ) );
	}

	/**
	 * Generates the base configuration for a Google API key field.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function google_api_key( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'google_api_key' ], $overrides ) );
	}

	/**
	 * Generates the base configuration for hidden fields.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function hidden( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'text'], $overrides ) );
	}

	/**
	 * Generates the base configuration for an icon picker.
	 *
	 * @param array $overrides An array of settings to merge with the defaults.
	 * @return array The final configuration array.
	 */
	public static function iconpicker( array $overrides = [] ): array {
		return self::text( array_merge( [ 'type' => 'iconpicker' ], $overrides ) );
	}
}

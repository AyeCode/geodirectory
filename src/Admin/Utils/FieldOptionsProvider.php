<?php
/**
 * Provides sets of options for various form fields.
 *
 * This is a central utility class for generating dynamic option arrays
 * for select, multiselect, and radio fields, ensuring the logic is
 * reusable and easy to find.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class FieldOptionsProvider {

	/**
	 * Generates a list of formatted date options for a select field.
	 *
	 * Defines a default set of PHP date formats, allows them to be filtered,
	 * and then formats them with a human-readable example.
	 *
	 * @return array An associative array of `['format' => 'format (example)']`.
	 */
	public static function get_date_formats(): array {
		$date_formats = [
			'm/d/Y',
			'd/m/Y',
			'Y/m/d',
			'm-d-Y',
			'd-m-Y',
			'Y-m-d',
			'F j, Y',
		];

		/**
		 * Filter the custom field date format options.
		 *
		 * @since 1.6.5
		 * @param array $date_formats The PHP date format array.
		 */
		$date_formats = apply_filters( 'geodir_date_formats', $date_formats );

		$date_formats_rendered = [];
		foreach ( $date_formats as $format ) {
			// The key is the raw format, the value is the user-friendly label.
			$date_formats_rendered[ $format ] = $format . ' (' . date_i18n( $format, time() ) . ')';
		}

		return $date_formats_rendered;
	}

	/**
	 * Generates a structured list of allowed MIME types, grouped into categories.
	 *
	 * Defines a default option for all types, iterates over allowed MIME type categories,
	 * and organizes specific extensions under respective categories as optgroups.
	 *
	 * @return array A nested associative array where top-level keys are category labels
	 *               (or '*' for all types) and their values are associative arrays of
	 *               file extensions mapped to their corresponding label.
	 */
	public static function get_allowed_mime_types(): array {
		// Assume geodir_allowed_mime_types() returns a structured array like:
		// [ 'Image' => [ 'jpg' => 'image/jpeg', ... ], 'Video' => [ ... ] ]
		$allowed_file_types = geodir_allowed_mime_types();

		// Start with the initial ungrouped option.
		$options = [
			'*' => __('All types', 'geodirectory')
		];

		// Loop through the main categories (e.g., Image, Video). These will be the optgroup labels.
		foreach ( $allowed_file_types as $format => $types ) {
			if ( ! empty( $types ) && is_array( $types ) ) {

				// Create the key for the optgroup.
				$optgroup_label = esc_attr( wp_sprintf( __('%s formats', 'geodirectory'), __( $format, 'geodirectory' ) ) );

				// Initialize the nested array for this optgroup.
				$options[$optgroup_label] = [];

				// Loop through the specific mime types (e.g., jpg, png) within the category.
				foreach ( $types as $ext => $type ) {
					// Add the option to the nested array.
					$options[$optgroup_label][esc_attr($ext)] = "." . esc_attr($ext);
				}
			}
		}

		return $options;
	}

}

<?php
/**
 * Form Field Factory
 *
 * Provides a powerful static method to build complex field configuration
 * arrays for the Form Builder from a simple set of instructions.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class FormFieldFactory {

	/**
	 * The master library of all possible sub-field components for the Form Builder.
	 *
	 * This private static array defines the full configuration for every
	 * possible setting a field can have, based on the legacy settings form.
	 * It uses the FormFields utility class as its foundation.
	 *
	 * @return array
	 */
	private static function get_master_library(): array {
		return [
			// Core Identifiers & Labels
			'type'           => FormFields::hidden( [ 'id' => 'type', 'name' => 'type' ] ),
			'admin_title'    => FormFields::text( [ 'id' => 'admin_title', 'name' => 'admin_title', 'label' => __( 'Admin Title', 'geodirectory' ), 'description' => __( 'This is used as the field setting name here in the backend only.', 'geodirectory' ) ] ),
			'frontend_title' => FormFields::text( [ 'id' => 'frontend_title', 'name' => 'frontend_title', 'label' => __( 'Label', 'geodirectory' ), 'description' => __( 'This will be the label for the field input on the frontend.', 'geodirectory' ) ] ),
			'htmlvar_name'   => FormFields::text( [ 'id' => 'htmlvar_name', 'name' => 'htmlvar_name', 'label' => __( 'Key', 'geodirectory' ), 'description' => __( 'A unique identifier used in the database and HTML. Must not contain spaces or special characters.', 'geodirectory' ), 'extra_attributes' => [ 'maxlength' => 50, 'pattern' => '[a-zA-Z0-9_]+' ] ] ),

			// Descriptions & Placeholders
			'frontend_desc'  => FormFields::text( [ 'id' => 'frontend_desc', 'name' => 'frontend_desc', 'label' => __( 'Description', 'geodirectory' ), 'description' => __( 'This will be shown below the field on the add listing form.', 'geodirectory' ) ] ),
			'placeholder'    => FormFields::text( [ 'id' => 'placeholder_value', 'name' => 'placeholder_value', 'label' => __( 'Placeholder', 'geodirectory' ), 'description' => __( 'A placeholder value to use for text input fields.', 'geodirectory' ) ] ),

			// Behavior & Logic
			'is_active'      => FormFields::toggle( [ 'id' => 'is_active', 'name' => 'is_active', 'label' => __( 'Is Active', 'geodirectory' ), 'description' => __( 'If disabled, the field will not be displayed anywhere.', 'geodirectory' ), 'default' => true ] ),
			'for_admin_use'  => FormFields::toggle( [ 'id' => 'for_admin_use', 'name' => 'for_admin_use', 'label' => __( 'Admin Edit Only', 'geodirectory' ), 'description' => __( 'If enabled, only site admins can see and edit this field.', 'geodirectory' ) ] ),
			'is_required'    => FormFields::toggle( [ 'id' => 'is_required', 'name' => 'is_required', 'label' => __( 'Is Required', 'geodirectory' ), 'description' => __( 'Set this field as required on the submission form.', 'geodirectory' ) ] ),
			'cat_sort'       => FormFields::toggle( [ 'id' => 'cat_sort', 'name' => 'cat_sort', 'label' => __( 'Include in Sorting Options', 'geodirectory' ), 'description' => __( 'Allows this field to be used as a sorting option on archive pages.', 'geodirectory' ) ] ),

			// Values & Data
			'default_value'  => FormFields::text( [ 'id' => 'default_value', 'name' => 'default_value', 'label' => __( 'Default Value', 'geodirectory' ), 'description' => __( 'A default value for the field on the submission form. For checkboxes, use 1 for checked, 0 for unchecked.', 'geodirectory' ) ] ),
			'db_default'     => FormFields::text( [ 'id' => 'db_default', 'name' => 'db_default', 'label' => __( 'Database Default Value', 'geodirectory' ), 'description' => __( 'A default value for the field when it is first created in the database.', 'geodirectory' ) ] ),
			'required_msg'   => FormFields::text( [ 'id' => 'required_msg', 'name' => 'required_msg', 'label' => __( 'Required Message', 'geodirectory' ), 'description' => __( 'The error message to show if the field is required and left empty.', 'geodirectory' ), 'show_if' => '[%is_required%:checked]' ] ),

			// Display & Styling
			'field_icon'     => FormFields::font_awesome( [ 'id' => 'field_icon', 'name' => 'field_icon', 'label' => __( 'Icon', 'geodirectory' ), 'description' => __( 'Enter a Font Awesome class (e.g., "fas fa-home") or a URL to an image.', 'geodirectory' ) ] ),
			'css_class'      => FormFields::text( [ 'id' => 'css_class', 'name' => 'css_class', 'label' => __( 'CSS Class', 'geodirectory' ), 'description' => __( 'Enter a custom CSS class for the field wrapper.', 'geodirectory' ) ] ),
			'show_in'        => FormFields::multiselect( [
				'id'          => 'show_in',
				'name'        => 'show_in[]',
				'label'       => __( 'Show in Extra Output Locations', 'geodirectory' ),
				'description' => __( 'Select where you want this field to be displayed.', 'geodirectory' ),
				'placeholder' => __( 'Select locations', 'geodirectory' ),
				// @todo The 'options' array for this field needs to be populated dynamically by a helper function before being passed to the renderer.
				'options'     => [],
			] ),
		];
	}

	/**
	 * Builds a complete 'fields' array from a set of instructions.
	 *
	 * @param array $instructions An array of instructions. Each item can be a string
	 * (the key of the field to include from the master library)
	 * or an associative array (the key with settings to override).
	 * @return array The final, assembled 'fields' array.
	 */
	public static function build( array $instructions ): array {
		$master_library = self::get_master_library();
		$final_fields   = [];

		foreach ( $instructions as $key => $instruction ) {
			if ( is_numeric( $key ) ) {
				// This is a simple instruction, e.g., 'admin_title'
				$field_key = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					$final_fields[] = $master_library[ $field_key ];
				}
			} else {
				// This is an instruction with an override, e.g., 'label' => ['default' => 'New Label']
				$field_key = $key;
				$overrides = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					// Merge the overrides on top of the master definition.
					$final_fields[] = array_merge( $master_library[ $field_key ], $overrides );
				}
			}
		}

		return $final_fields;
	}
}

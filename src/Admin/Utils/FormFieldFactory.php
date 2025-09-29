<?php
/**
 * Form Field Factory
 *
 * Provides powerful static methods to build complex field configuration
 * arrays for the Form Builder from a simple set of instructions.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types=1 );

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
			'type'                         => FormFields::hidden( [ 'id' => 'type', 'name' => 'type' ] ),
			'field_type_key'                         => FormFields::hidden( [ 'id' => 'field_type_key', 'name' => 'field_type_key' ] ),
			'admin_title'                  => FormFields::text( [
				'id'          => 'label',
				'name'        => 'label',
				'label'       => __( 'Admin Title', 'geodirectory' ),
				'extra_attributes' => [
					'required' => true,
				],
				'description' => __( 'This is used as the field setting name here in the backend only.', 'geodirectory' ),
				'syncs_with' => [
					[
						'target' => 'frontend_title',
					],
					[
						'target' => 'htmlvar_name',
						'transform' => 'slugify'
					]
				]
			] ),
			'frontend_title'               => FormFields::text( [
				'id'          => 'frontend_title',
				'name'        => 'frontend_title',
				'label'       => __( 'Label', 'geodirectory' ),
				'description' => __( 'This will be the label for the field input on the frontend.', 'geodirectory' ),
				'extra_attributes' => [
					'required' => true,
				],
				'syncs_with' => [
					[
						'target' => 'label',
					],
					[
						'target' => 'htmlvar_name',
						'transform' => 'slugify'
					]
				]
			] ),
			'htmlvar_name'                 => FormFields::text( [
				'id'               => 'htmlvar_name',
				'name'             => 'htmlvar_name',
				'label'            => __( 'Key', 'geodirectory' ),
				'description'      => __( 'A unique identifier used in the database and HTML. Must not contain spaces or special characters.', 'geodirectory' ),
				'extra_attributes' => [
					'maxlength' => 50,
					'pattern'   => '[a-zA-Z0-9_]+',
					'required' => true,
				],
			] ),

			// Descriptions & Placeholders
			'frontend_desc'                => FormFields::text( [
				'id'          => 'frontend_desc',
				'name'        => 'frontend_desc',
				'label'       => __( 'Description', 'geodirectory' ),
				'description' => __( 'This will be shown below the field on the add listing form.', 'geodirectory' )
			] ),
			'placeholder'                  => FormFields::text( [
				'id'          => 'placeholder_value',
				'name'        => 'placeholder_value',
				'label'       => __( 'Placeholder', 'geodirectory' ),
				'description' => __( 'A placeholder value to use for text input fields.', 'geodirectory' )
			] ),

			// Behavior & Logic
			'is_active'                    => FormFields::toggle( [
				'id'          => 'is_active',
				'name'        => 'is_active',
				'label'       => __( 'Is Active', 'geodirectory' ),
				'description' => __( 'If disabled, the field will not be displayed anywhere.', 'geodirectory' ),
				'default'     => true,
				'show_if'     => '[%htmlvar_name%] != "post_title" && [%htmlvar_name%] != "post_content" && [%htmlvar_name%] != "post_tags" && [%htmlvar_name%] != "post_category"',
			] ),
			'for_admin_use'                => FormFields::toggle( [
				'id'          => 'for_admin_use',
				'name'        => 'for_admin_use',
				'label'       => __( 'Admin Edit Only', 'geodirectory' ),
				'description' => __( 'If enabled, only site admins can see and edit this field.', 'geodirectory' ),
				'show_if'     => '[%htmlvar_name%] != "post_title" && [%htmlvar_name%] != "post_content"  && [%htmlvar_name%] != "post_category"',
			] ),
			'is_required'                  => FormFields::toggle( [
				'id'          => 'is_required',
				'name'        => 'is_required',
				'label'       => __( 'Is Required', 'geodirectory' ),
				'description' => __( 'Set this field as required on the submission form.', 'geodirectory' )
			] ),
			'required_msg'                 => FormFields::text( [
				'id'          => 'required_msg',
				'name'        => 'required_msg',
				'label'       => __( 'Required Message', 'geodirectory' ),
				'description' => __( 'The error message to show if the field is required and left empty.', 'geodirectory' ),
				'show_if'     => '[%is_required%] > 0'
			] ),
			'cat_sort'                     => FormFields::toggle( [
				'id'          => 'cat_sort',
				'name'        => 'cat_sort',
				'label'       => __( 'Include in Sorting Options', 'geodirectory' ),
				'description' => __( 'Allows this field to be used as a sorting option on archive pages.', 'geodirectory' )
			] ),
			'extra_fields.advanced_editor' => FormFields::toggle( [
				'id'          => 'advanced_editor',
				'name'        => 'advanced_editor',
				'label'       => __( 'Advanced editor', 'geodirectory' ),
				'description' => __( 'Select if you want to show the advanced editor on add listing page.', 'geodirectory' ),
				'show_if'     => '[%type%] == "textarea"',
			] ),
			'extra_fields.embed'           => FormFields::toggle( [
				'id'          => 'embed',
				'name'        => 'embed',
				'label'       => __( 'Embed media URLs', 'geodirectory' ),
				'description' => __( 'Tick to allow embed videos, images, tweets, audio, and other content.', 'geodirectory' ),
				'show_if'     => '[%type%] == "textarea" || [%type%] == "html"',
			] ),

			// Values & Data
			'option_values'                => FormFields::textarea( [
				'id'          => 'option_values',
				'name'        => 'option_values',
				'rows'        => 5,
				'label'       => __( 'Option Values', 'geodirectory' ),
				'description' => __( 'Enter each option on a new line with format "LABEL" OR "VALUE : LABEL". To grouping options use "optgroup : OPTGROUP-LABEL" & "optgroup-close".', 'geodirectory' ),
				'placeholder' => esc_html__( 'For Sale
under-offer : Under Offer
optgroup : Electronics
TV
Laptop
optgroup-close', 'geodirectory' ),
				'show_if'     => '[%type%] == "select" || [%type%] == "multiselect" || [%type%] == "radio"',
			] ),
			'default_value'                => FormFields::text( [
				'id'          => 'default_value',
				'name'        => 'default_value',
				'label'       => __( 'Default Value', 'geodirectory' ),
				'description' => __( 'A default value for the field on the submission form. For checkboxes, use 1 for checked, 0 for unchecked.', 'geodirectory' ),
			] ),
			'db_default'                   => FormFields::text( [
				'id'          => 'db_default',
				'name'        => 'db_default',
				'label'       => __( 'Database Default Value', 'geodirectory' ),
				'description' => __( 'A default value for the field when it is first created in the database.', 'geodirectory' ),
				'show_if'     => '[%is_new%]', // only show on new fields
			] ),


			// Display & Styling
			'field_icon'                   => FormFields::font_awesome( [
				'id'          => 'icon',
				'name'        => 'icon',
				'label'       => __( 'Icon', 'geodirectory' ),
				'description' => __( 'Enter a Font Awesome class (e.g., "fas fa-home") or a URL to an image.', 'geodirectory' )
			] ),
			'css_class'                    => FormFields::text( [
				'id'          => 'css_class',
				'name'        => 'css_class',
				'label'       => __( 'CSS Class', 'geodirectory' ),
				'description' => __( 'Enter a custom CSS class for the field wrapper.', 'geodirectory' )
			] ),
			'show_in'                      => FormFields::multiselect( [
				'id'          => 'show_in',
				'name'        => 'show_in',
				'label'       => __( 'Show in Extra Output Locations', 'geodirectory' ),
				'description' => __( 'Select where you want this field to be displayed.', 'geodirectory' ),
				'placeholder' => __( 'Select locations', 'geodirectory' ),
				'options'     => geodir_show_in_locations(),
			] ),
			'extra_fields.cat_display_type'     => FormFields::select( [
				'id'          => 'cat_display_type',
				'name'        => 'cat_display_type',
				'label'       => __( 'Category display type', 'geodirectory' ),
				'description' => __( 'Show categories list as select, multiselect, checkbox or radio', 'geodirectory' ),
				'default'     => 'multiselect',
				'options'     => [
					'select' => __( 'Select', 'geodirectory' ),
					'multiselect' => __( 'Multiselect', 'geodirectory' ),
					'checkbox' => __( 'Checkbox', 'geodirectory' ),
					'radio' => __( 'Radio', 'geodirectory' ),
				],
				'show_if'     => '[%type%] == "categories"',
			] ),

			// Number Stuff
			'data_type'                    => FormFields::select( [
				'id'          => 'data_type',
				'name'        => 'data_type',
				'label'       => __( 'Data Type', 'geodirectory' ),
				'description' => __( 'Select the data type for the field. This can affect things like search filtering.', 'geodirectory' ),
				'default'     => 'XVARCHAR',
				'options'     => [
					'XVARCHAR' => __( 'CHARACTER', 'geodirectory' ),
					'INT'      => __( 'NUMBER', 'geodirectory' ),
					'DECIMAL'  => __( 'DECIMAL', 'geodirectory' ),
				],
				'show_if'     => '[%type%] == "text"',
			] ),
			'is_price'                     => FormFields::toggle( [
				'id'          => 'is_price',
				'name'        => 'is_price',
				'label'       => __( 'Display as price', 'geodirectory' ),
				'description' => __( 'Select if this field should be displayed as a price value.', 'geodirectory' ),
				'show_if'     => '[%data_type%] == "INT" || [%data_type%] == "DECIMAL"',
			] ),
			'currency_symbol'              => FormFields::text( [
				'id'          => 'currency_symbol',
				'name'        => 'currency_symbol',
				'label'       => __( 'Currency symbol', 'geodirectory' ),
				'description' => __( 'Set the currency symbol.', 'geodirectory' ),
				'show_if'     => '[%is_price%] > 0',
			] ),
			'currency_symbol_placement'    => FormFields::select( [
				'id'          => 'currency_symbol_placement',
				'name'        => 'currency_symbol_placement',
				'label'       => __( 'Currency symbol placement', 'geodirectory' ),
				'description' => __( 'Select the currency symbol placement.', 'geodirectory' ),
				'default'     => 'left',
				'options'     => [
					'left'  => __( 'Left', 'geodirectory' ),
					'right' => __( 'Right', 'geodirectory' ),
				],
				'show_if'     => '[%is_price%] > 0 && [%currency_symbol%]',
			] ),
			'thousand_separator'           => FormFields::select( [
				'id'          => 'thousand_separator',
				'name'        => 'thousand_separator',
				'label'       => __( 'Thousand separator', 'geodirectory' ),
				'description' => __( 'Select the thousand separator.', 'geodirectory' ),
				'default'     => 'comma',
				'options'     => [
					'comma'  => __( ', (comma)', 'geodirectory' ),
					'slash'  => __( '\ (slash)', 'geodirectory' ),
					'period' => __( '. (period)', 'geodirectory' ),
					'space'  => __( ' (space)', 'geodirectory' ),
					'none'   => __( '(none)', 'geodirectory' ),
				],
				'show_if'     => '[%is_price%] || [%data_type%] == "INT" || [%data_type%] == "DECIMAL"',
			] ),
			'decimal_separator'            => FormFields::select( [
				'id'          => 'decimal_separator',
				'name'        => 'decimal_separator',
				'label'       => __( 'Decimal separator', 'geodirectory' ),
				'description' => __( 'Decimal point to display after point.', 'geodirectory' ),
				'default'     => 'period',
				'options'     => [
					'period' => __( '. (period)', 'geodirectory' ),
					'comma'  => __( ', (comma)', 'geodirectory' ),
				],
				'show_if'     => '[%data_type%] == "DECIMAL"',
			] ),
			'decimal_point'                => FormFields::select( [
				'id'          => 'decimal_point',
				'name'        => 'decimal_point',
				'label'       => __( 'Decimal points', 'geodirectory' ),
				'description' => __( 'Decimals to display after point.', 'geodirectory' ),
				'placeholder' => __( 'Select', 'geodirectory' ),
				'options'     => array_combine( range( 1, 10 ), range( 1, 10 ) ),
				'show_if'     => '[%data_type%] == "DECIMAL"',
			] ),
			'decimal_display'              => FormFields::select( [
				'id'          => 'decimal_display',
				'name'        => 'decimal_display',
				'label'       => __( 'Decimal display', 'geodirectory' ),
				'description' => __( 'Select how the decimal is displayed if empty.', 'geodirectory' ),
				'default'     => 'if',
				'options'     => [
					'if'     => __( 'Not show if not used', 'geodirectory' ),
					'always' => __( 'Always (.00)', 'geodirectory' ),
				],
				'show_if'     => '[%data_type%] == "DECIMAL" && [%decimal_point%] != ""',
			] ),
			'extra_fields.date_format'     => FormFields::select( [
				'id'          => 'date_format',
				'name'        => 'date_format',
				'label'       => __( 'Date Format', 'geodirectory' ),
				'description' => __( 'Select the date format.', 'geodirectory' ),
				'default'     => 'XVARCHAR',
				'options'     => FieldOptionsProvider::get_date_formats(),
				'show_if'     => '[%type%] == "date"',
			] ),

			// Address stuff
			'extra_fields.show_street2'                     => FormFields::toggle( [
				'id'          => 'show_street2',
				'name'        => 'show_street2',
				'label'       => __( 'Display Address line 2', 'geodirectory' ),
				'description' => __( 'Select if you want to show address line 2 field in address section.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),
			'extra_fields.street2_lable'                     => FormFields::text( [
				'id'          => 'street2_lable',
				'name'        => 'street2_lable',
				'label'       => __( 'Address line 2 label', 'geodirectory' ),
				'description' => __( 'Enter Address line 2 field label in address section.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address" && [%show_street2%] == "1"',
			] ),
			'extra_fields.show_zip'                     => FormFields::toggle( [
				'id'          => 'show_zip',
				'name'        => 'show_zip',
				'label'       => __( 'Display zip/post code', 'geodirectory' ),
				'description' => __( 'Select if you want to show zip/post code field in address section.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),
			'extra_fields.zip_lable'                     => FormFields::text( [
				'id'          => 'zip_lable',
				'name'        => 'zip_lable',
				'label'       => __( 'Zip/Post code label', 'geodirectory' ),
				'description' => __( 'Enter zip/post code field label in address section.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address" && [%show_zip%] == "1"',
			] ),

			'extra_fields.show_mapview'                     => FormFields::toggle( [
				'id'          => 'show_mapview',
				'name'        => 'show_mapview',
				'label'       => __( 'Display map view', 'geodirectory' ),
				'description' => __( 'Select if you want to show `set default map` options in address section. ( Satellite Map, Hybrid Map, Terrain Map)', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),
			'extra_fields.mapview_lable'                     => FormFields::text( [
				'id'          => 'mapview_lable',
				'name'        => 'mapview_lable',
				'label'       => __( 'Map view label', 'geodirectory' ),
				'description' => __( 'Enter mapview field label in address section.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address" && [%show_mapview%] == "1"',
			] ),

			'extra_fields.zip_required'                     => FormFields::toggle( [
				'id'          => 'zip_required',
				'name'        => 'zip_required',
				'label'       => __( 'Make zip code required', 'geodirectory' ),
				'description' => __( 'Tick to set zip/post code field as required. Some countries do not use ZIP codes, please only enable if your directory is limited to countries that do.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),
			'extra_fields.show_mapzoom'                     => FormFields::toggle( [
				'id'          => 'show_mapzoom',
				'name'        => 'show_mapzoom',
				'label'       => __( 'Use user zoom level', 'geodirectory' ),
				'description' => __( 'Do you want to use the user defined map zoom level from the add listing page?', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),
			'extra_fields.show_latlng'                     => FormFields::toggle( [
				'id'          => 'show_latlng',
				'name'        => 'show_latlng',
				'label'       => __( 'Show latitude and longitude', 'geodirectory' ),
				'description' => __( 'This will show/hide the longitude fields in the address section add listing form.', 'geodirectory' ),
				'show_if'     => '[%type%] == "address"',
			] ),

			// Tags stuff
			'extra_fields.disable_new_tags'                     => FormFields::toggle( [
				'id'          => 'disable_new_tags',
				'name'        => 'disable_new_tags',
				'label'       => __( 'Disable New Tags', 'geodirectory' ),
				'description' => __( 'Disable create a new tags dynamically from frontend users.', 'geodirectory' ),
				'show_if'     => '[%type%] == "tags"',
			] ),
			'extra_fields.spellcheck'                     => FormFields::toggle( [
				'id'          => 'spellcheck',
				'name'        => 'spellcheck',
				'label'       => __( 'Spell Check', 'geodirectory' ),
				'description' => __( 'Enable spell check for the new tag.', 'geodirectory' ),
				'show_if'     => '[%type%] == "tags"',
			] ),
			'extra_fields.no_of_tag'      => FormFields::number( [
				'id'          => 'no_of_tag',
				'name'        => 'no_of_tag',
				'default'     => 10,
				'label'       => __( 'Number of allowed tags', 'geodirectory' ),
				'description' => __( 'Enter number of allowed tags. Default: 10', 'geodirectory' ),
				'show_if'     => '[%type%] == "tags"',
			] ),

			// Validation
			'validation_pattern'           => FormFields::text( [
				'id'          => 'validation_pattern',
				'name'        => 'validation_pattern',
				'label'       => __( 'Validation Pattern', 'geodirectory' ),
				'description' => __( 'Enter regex expression for HTML5 pattern validation.', 'geodirectory' )
			] ),
			'validation_msg'               => FormFields::text( [
				'id'          => 'validation_msg',
				'name'        => 'validation_msg',
				'label'       => __( 'Validation Message', 'geodirectory' ),
				'description' => __( 'Enter a extra validation message to show to the user if validation fails.', 'geodirectory' ),
				'show_if'     => '[%validation_pattern%] != ""'
			] ),
			'extra_fields.date_range'      => FormFields::text( [
				'id'          => 'date_range',
				'name'        => 'date_range',
				'label'       => __( 'Date Range', 'geodirectory' ),
				'description' => __( 'Set the date range, eg: 1920:2020 or for current dates: c-100:c+5', 'geodirectory' ),
				'show_if'     => '[%type%] == "date"',
			] ),
			'extra_fields.gd_file_types'   => FormFields::multiselect( [
				'id'          => 'gd_file_types',
				'name'        => 'gd_file_types',
				'default'     => [ '*' ],
				'label'       => __( 'Allowed file types', 'geodirectory' ),
				'description' => __( 'Select file types to allowed for file uploading.', 'geodirectory' ),
				'placeholder' => __( 'Select file types', 'geodirectory' ),
				'options'     => FieldOptionsProvider::get_allowed_mime_types(),
				'show_if'     => '[%type%] == "file"',
			] ),
			'extra_fields.file_limit'      => FormFields::number( [
				'id'          => 'file_limit',
				'name'        => 'file_limit',
				'default'     => 1,
				'label'       => __( 'File upload limit', 'geodirectory' ),
				'description' => __( 'Select the number of files that can be uploaded, Leave blank or 0 to allow unlimited files.', 'geodirectory' ),
				'show_if'     => '[%type%] == "file"',
			] ),
			'extra_fields.conditions'      =>  [
				'id'          => 'conditions',
				'type'        => 'conditions',
				'warning_key' => 'htmlvar_name',
				'warning_fields' => ['post_title', 'post_category', 'address'],
			],
		];
	}

	/**
	 * Defines the default set of fields for each accordion panel.
	 *
	 * This is the "base" configuration. When building a field type, all of these
	 * fields are included by default, and the definition only specifies the exceptions.
	 *
	 * @return array
	 */
	private static function get_default_panel_fields(): array {
		return [
			'general' => [
				'type',
				'field_type_key',
				'admin_title',
				'frontend_title',
				'htmlvar_name'
			],
			'display'           => [
				'frontend_desc',
				'placeholder',
				'field_icon',
				'css_class',
				'show_in',
				'extra_fields.cat_display_type',
				'data_type',
				'is_price',
				'currency_symbol',
				'currency_symbol_placement',
				'thousand_separator',
				'decimal_separator',
				'decimal_point',
				'decimal_display',
				'extra_fields.date_format',

				// address stuff
				'extra_fields.show_street2',
				'extra_fields.street2_lable',
				'extra_fields.show_zip',
				'extra_fields.zip_lable',
				'extra_fields.show_mapview',
				'extra_fields.mapview_lable',
				'extra_fields.show_latlng',
			],
			'behavior'          => [
				'is_active',
				'is_required',
				'required_msg',
				'for_admin_use',
				'cat_sort',
				'extra_fields.show_mapzoom',
				'extra_fields.disable_new_tags',
				'extra_fields.spellcheck',
				'extra_fields.advanced_editor',
				'extra_fields.embed',
				'option_values',
				'default_value',
				'db_default',
			],
			'validation'        => [
				'validation_pattern',
				'validation_msg',
				'extra_fields.date_range',
				'extra_fields.gd_file_types',
				'extra_fields.file_limit',
				'extra_fields.zip_required',
				'extra_fields.no_of_tag',
			],
			'conditional_logic' => [
				'extra_fields.conditions',
			], // Empty by default
		];
	}

	/**
	 * Builds a complete 'fields' array from a set of instructions.
	 *
	 * @param array $instructions
	 *
	 * @return array
	 */
	public static function build( array $instructions ): array {
		$master_library = self::get_master_library();
		$final_fields   = [];

		foreach ( $instructions as $key => $instruction ) {
			if ( is_numeric( $key ) ) {
				// Simple instruction, e.g., 'admin_title'
				$field_key = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					$final_fields[] = $master_library[ $field_key ];
				}
			} else {
				// Instruction with an override, e.g., 'admin_title' => ['default' => 'New Title']
				$field_key = $key;
				$overrides = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					$final_fields[] = array_merge( $master_library[ $field_key ], $overrides );
				}
			}
		}

		return $final_fields;
	}

	/**
	 * Builds the entire settings array for a field type, including accordion structure.
	 *
	 * This is the main entry point. It works by "convention over configuration".
	 * All fields are included by default. The definition only needs to specify
	 * overrides (as an array) or removals (with 'false').
	 *
	 * @param array $definition The definition for the field type.
	 *
	 * @return array The complete configuration array for the field type.
	 */
	public static function build_field_settings( array $definition ): array {
		$default_panels   = self::get_default_panel_fields();
		$accordion_panels = [
			[
				'label'  => '<i class="fa-solid fa-gear me-2"></i>' . esc_attr__( 'General', 'geodirectory' ),
				'id'     => 'general',
				'fields' => []
			],
			[
				'label'  => '<i class="fa-solid fa-palette me-2"></i>' . esc_attr__( 'Display & Style', 'geodirectory' ),
				'id'     => 'display',
				'fields' => []
			],
			[
				'label'  => '<i class="fa-solid fa-sliders me-2"></i>' . esc_attr__( 'Behavior & Values', 'geodirectory' ),
				'id'     => 'behavior',
				'fields' => []
			],
			[
				'label'  => '<i class="fa-solid fa-circle-check me-2"></i>' . esc_attr__( 'Validation', 'geodirectory' ),
				'id'     => 'validation',
				'fields' => []
			],
			[
				'label'  => '<i class="fa-solid fa-code-branch me-2"></i>' . esc_attr__( 'Conditional Logic', 'geodirectory' ),
				'id'     => 'conditional_logic',
				'fields' => []
			],
		];

		// Populate the panels with fields based on the definition.
		foreach ( $accordion_panels as &$panel ) {
			$panel_id           = $panel['id'];
			$base_fields        = $default_panels[ $panel_id ] ?? [];
			$exceptions         = $definition['panels'][ $panel_id ] ?? [];
			$final_instructions = [];

			// Process the base fields against the exceptions.
			foreach ( $base_fields as $field_key ) {
				// Check if an exception exists for this field.
				if ( array_key_exists( $field_key, $exceptions ) ) {
					$exception = $exceptions[ $field_key ];
					if ( $exception === false ) {
						// 'key' => false means remove this field.
						continue; // Skip it.
					}
					// Otherwise, it's an override.
					$final_instructions[ $field_key ] = $exception;
				} else {
					// No exception, so include the field normally.
					$final_instructions[] = $field_key;
				}
			}

			if ( ! empty( $final_instructions ) ) {
				$panel['fields'] = self::build( $final_instructions );
			}
		}
		unset( $panel ); // Unset the reference.

		return [
			'title'  => $definition['title'],
			'id'     => $definition['id'],
			'icon'   => $definition['icon'],
			'description'   => !empty($definition['description']) ? $definition['description'] : '',
			'limit'   => !empty($definition['limit']) ? $definition['limit'] : 0,
			'hidden'   => !empty($definition['hidden']),
			'nestable'   => !empty($definition['nestable']),
			'allowed_children'   => !empty($definition['allowed_children']) ? $definition['allowed_children'] : null,

			'fields' => [
				[
					'id'           => 'builder_fields_accordion',
					'default_open' => 'general',
					'type'         => 'accordion',
					'fields'       => $accordion_panels
				],
			],
		];
	}
}

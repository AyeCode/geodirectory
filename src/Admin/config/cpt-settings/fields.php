<?php
/**
 * V3 CPT Custom Fields Settings for GeoDirectory
 *
 * This configuration file defines the structure and available field
 * types for the custom field form builder.
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Import our new factory class to build the field settings.
use AyeCode\GeoDirectory\Admin\Utils\FormFieldFactory;
use AyeCode\GeoDirectory\Admin\Utils\FormFields;

return [
	'id'        => 'fields_form_builder',
	'name'      => __( 'Fields', 'geodirectory' ),
	'icon'      => 'fa-solid fa-bars',
	'type'      => 'form_builder',
	'templates' => [
		[
			'group_title' => __( 'Standard Fields', 'geodirectory' ),
			'options'     => [
				// Text
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Text', 'geodirectory' ),
					'id'     => 'text',
					'icon'   => 'fas fa-minus',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'text' ],
						],
//						'display' => [
//							'show_in' => ['default'     => [ '[mapbubble]','[listing]' ] ],
//						]
					],
				] ),

				// Textarea
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Textarea', 'geodirectory' ),
					'icon'   => 'fas fa-bars',
					'id'     => 'textarea',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'textarea' ],
						],
					],
				] ),

				// Select
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Select', 'geodirectory' ),
					'icon'   => 'fas fa-caret-square-down',
					'id'     => 'select',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'select' ],
						],
					],
				] ),

				// MultiSelect
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Multiselect', 'geodirectory' ),
					'icon'   => 'fas fa-caret-square-down',
					'id'     => 'multiselect',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'multiselect' ],
						],
					],
				] ),

				// Checkbox
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Checkbox', 'geodirectory' ),
					'icon'   => 'fas fa-check-square',
					'id'     => 'checkbox',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'checkbox' ],
						],
						'behavior' => [
							'default_value' => [
								'type'        => 'toggle',
								'description' => __( 'Should the checkbox be checked by default?', 'geodirectory' ),
							],
							'db_default' => [
								'type'        => 'toggle',
								'description' => __( 'Should the value be set by default in the database?', 'geodirectory' ),
							]
						]
					],
				] ),

				// Radio
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Radio', 'geodirectory' ),
					'icon'   => 'far fa-dot-circle',
					'id'     => 'radio',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'radio' ],
						],
					],
				] ),

				// Email
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Email', 'geodirectory' ),
					'icon'   => 'far fa-envelope',
					'id'     => 'email',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'email' ],
							'admin_title' => [ 'default' => 'Email Address' ],
							'frontend_title' => [ 'default' => 'Email Address' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-envelope' ],
						]
					],
				] ),

				// Phone
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Phone', 'geodirectory' ),
					'icon'   => 'fas fa-phone',
					'id'     => 'phone',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'phone' ],
							'admin_title' => [ 'default' => 'Phone Number' ],
							'frontend_title' => [ 'default' => 'Phone Number' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-phone' ],
						]
					],
				] ),

				// URL
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'URL', 'geodirectory' ),
					'icon'   => 'fas fa-link',
					'id'     => 'url',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'url' ],
							'admin_title' => [ 'default' => 'Website' ],
							'frontend_title' => [ 'default' => 'Website' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-globe' ],
						]
					],
				] ),

				// Date
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Date', 'geodirectory' ),
					'icon'   => 'fas fa-calendar',
					'id'     => 'datepicker',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'date' ],
							'admin_title' => [ 'default' => 'Date' ],
							'frontend_title' => [ 'default' => 'Date' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-calendar' ],
						]
					],
				] ),

				// Time
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Time', 'geodirectory' ),
					'icon'   => 'fas fa-clock',
					'id'     => 'time',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'time' ],
							'admin_title' => [ 'default' => 'Time' ],
							'frontend_title' => [ 'default' => 'Time' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-clock' ],
						]
					],
				] ),

				// HTML
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'HTML', 'geodirectory' ),
					'icon'   => 'fas fa-code',
					'id'     => 'html',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'html' ],
							'admin_title' => [ 'default' => 'HTML' ],
							'frontend_title' => [ 'default' => 'HTML' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'fas fa-code' ],
						]
					],
				] ),

				// File Upload
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'File Upload', 'geodirectory' ),
					'icon'   => 'far fa-file',
					'id'     => 'file',
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'file' ],
							'admin_title' => [ 'default' => 'File Upload' ],
							'frontend_title' => [ 'default' => 'File Upload' ],
						],
						'display' => [
							'field_icon' => [ 'default' => 'far fa-file' ],
						],
						'validation' => [
							'validation_pattern' => [ 'type' => 'hidden' ],
						]
					],
				] ),

			]
		],
		[
			'group_title' => __( 'Predefined Fields', 'geodirectory' ),
			'options'     => [
				[
					'id'       => 'custom_title_skeleton',
					'title'    => 'Listing Title',
					'icon'     => 'fa-solid fa-heading',
					'limit'    => 1,
					'base_id'  => 'text', // <-- The actual field type to create.
					'defaults' => [           // <-- The values to apply to the new instance.
						'label'       => 'Listing Title',
						'key'         => 'listing_title',
						'description' => 'The main title for the listing.',
						'is_active'   => true,
						'is_required' => true,
					]
				],
				[
					'title'  => __( 'Text', 'geodirectory' ),
					'id'     => 'textx',
					'icon'   => 'fas fa-minus',
					'fields' => FormFieldFactory::build( [
						'type'           => [ 'default' => 'text' ],
						'admin_title'    => [ 'default' => 'New Text Field' ],
						'frontend_title' => [ 'default' => 'New Text Field' ],
						'htmlvar_name',
						'field_icon',
						'frontend_desc',
						'placeholder',
						'default_value',
						'is_required',
						'required_msg',
						'is_active',
						'for_admin_use',
						'show_in',
						'css_class',
						'cat_sort',
					] ),
				]
			]
		]
	]
];

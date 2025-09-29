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
	'unique_key_property' => 'htmlvar_name',
	'templates' => [
		[
			'group_title' => __( 'Standard Fields', 'geodirectory' ),
			'options'     => [

				// Hidden Default fields
				FormFieldFactory::build_field_settings( [
					'id'     => 'categories',
					'title'  => __( 'Category', 'geodirectory' ),
					'icon'   => 'fas fa-folder',
					'limit'  => 1,
					'hidden' => true,
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'categories' ],
							'field_type_key' => [ 'default' => 'categories' ],
							'htmlvar_name'   => [ 'default' => 'post_category' ],
						],
					],
				] ),
				FormFieldFactory::build_field_settings( [
					'id'     => 'tags',
					'title'  => __( 'Tags', 'geodirectory' ),
					'icon'   => 'fas fa-tags',
					'limit'  => 1,
					'hidden' => true,
					'panels' => [
						'general' => [
							'type'         => [ 'default' => 'tags' ],
							'field_type_key' => [ 'default' => 'tags' ],
							'htmlvar_name' => [ 'default' => 'post_tags' ],
						],
					],
				] ),
				FormFieldFactory::build_field_settings( [
					'id'     => 'address',
					'title'  => __( 'Address', 'geodirectory' ),
					'icon'   => 'fas fa-map-marker-alt',
					'limit'  => 1,
					'hidden' => true,
					'panels' => [
						'general' => [
							'type'         => [ 'default' => 'address' ],
							'field_type_key'   => [ 'default' => 'address' ],
							'htmlvar_name' => [ 'default' => 'address' ],
						],
					],
				] ),
				FormFieldFactory::build_field_settings( [
					'id'     => 'images',
					'title'  => __( 'Images', 'geodirectory' ),
					'icon'   => 'far fa-image',
					'limit'  => 1,
					'hidden' => true,
					'panels' => [
						'general' => [
							'type'         => [ 'default' => 'images' ],
							'field_type_key'   => [ 'default' => 'images' ],
							'htmlvar_name' => [ 'default' => 'post_images' ],
						],
					],
				] ),


				// Start fo standard fields

				// Fieldset
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Fieldset (section separator)', 'geodirectory' ),
					'id'     => 'fieldset',
					'icon'   => 'fa-solid fa-arrows-left-right',
					'description'  => __( 'This adds a section separator with a title.', 'geodirectory' ),
					'nestable' => true, // Make this field a container too
					'allowed_children' => [ '*' ],
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'fieldset' ],
							'field_type_key' => [ 'default' => 'fieldset' ],
						],
//                 'display' => [
//                    'show_in' => ['default'     => [ '[mapbubble]','[listing]' ] ],
//                 ]
					],
				] ),

				// Text
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Text', 'geodirectory' ),
					'id'     => 'text',
					'icon'   => 'fas fa-minus',
					'description'  => __( 'Add any sort of text field, text or numbers', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'text' ],
						],
		                 'display' => [
		                    'show_in' => ['default'     => [ '[mapbubble]','[listing]' ] ],
		                 ]
					],
				] ),

				// Textarea
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Textarea', 'geodirectory' ),
					'icon'   => 'fas fa-bars',
					'id'     => 'textarea',
					'description'  => __( 'Adds a textarea', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'textarea' ],
							'field_type_key' => [ 'default' => 'textarea' ],
						],
					],
				] ),

				// Select
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Select', 'geodirectory' ),
					'icon'   => 'fas fa-caret-square-down',
					'id'     => 'select',
					'description'  => __( 'Adds a select input', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'select' ],
						],
					],
				] ),

				// MultiSelect
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Multiselect', 'geodirectory' ),
					'icon'   => 'fas fa-caret-square-down',
					'id'     => 'multiselect',
					'description'  => __( 'Adds a multiselect input', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'multiselect' ],
							'field_type_key' => [ 'default' => 'multiselect' ],
						],
					],
				] ),

				// Checkbox
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Checkbox', 'geodirectory' ),
					'icon'   => 'fas fa-check-square',
					'id'     => 'checkbox',
					'description'  => __( 'Adds a checkbox', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type' => [ 'default' => 'checkbox' ],
							'field_type_key' => [ 'default' => 'checkbox' ],
						],
						'behavior' => [
							'default_value' => [
								'type'        => 'toggle',
								'description' => __( 'Should the checkbox be checked by default?', 'geodirectory' ),
							],
							'db_default'    => [
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
					'description'  => __( 'Adds a radio input', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type' => [ 'default' => 'radio' ],
							'field_type_key' => [ 'default' => 'radio' ],
						],
					],
				] ),

				// Email
				FormFieldFactory::build_field_settings( [
					'title'  => __( 'Email', 'geodirectory' ),
					'icon'   => 'far fa-envelope',
					'id'     => 'email',
					'description'  => __( 'Adds a email input', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'email' ],
							'field_type_key' => [ 'default' => 'email' ],
							'admin_title'    => [ 'default' => 'Email Address' ],
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
					'description'  => __( 'Adds a phone input', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'phone' ],
							'field_type_key' => [ 'default' => 'phone' ],
							'admin_title'    => [ 'default' => 'Phone Number' ],
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
					'description'  => __( 'Adds a url input', 'geodirectory' ),
//              'limit'    => 1, @todo not working
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type'     => [ 'default' => 'url' ],
							'admin_title'    => [ 'default' => 'Website' ],
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
					'description'  => __( 'Adds a date picker.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'datepicker' ],
							'field_type_key' => [ 'default' => 'datepicker' ],
							'admin_title'    => [ 'default' => 'Date' ],
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
					'description'  => __( 'Adds a time picker', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'time' ],
							'field_type_key' => [ 'default' => 'time' ],
							'admin_title'    => [ 'default' => 'Time' ],
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
					'description'  => __( 'Adds a html input textarea', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'html' ],
							'field_type_key' => [ 'default' => 'html' ],
							'admin_title'    => [ 'default' => 'HTML' ],
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
					'description'  => __( 'Adds a file input', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'file' ],
							'field_type_key' => [ 'default' => 'file' ],
							'admin_title'    => [ 'default' => 'File Upload' ],
							'frontend_title' => [ 'default' => 'File Upload' ],
						],
						'display'    => [
							'field_icon' => [ 'default' => 'far fa-file' ],
						],
						'validation' => [
							'validation_pattern' => [ 'type' => 'hidden' ],
						]
					],
				] ),
			],
		],
		// Predefined Fields
		[
			'group_title' => __( 'Predefined Fields', 'geodirectory' ),
			'options'     => [

				// Business Hours
				FormFieldFactory::build_field_settings( [
					'id'     => 'business_hours',
					'title'  => __( 'Business Hours', 'geodirectory' ),
					'icon'   => 'fas fa-clock',
					'limit'  => 1,
					'description'  => __( 'Adds a business hours input. This can display when the listing is open/closed/', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'business_hours' ],
							'field_type_key' => [ 'default' => 'business_hours' ],
							'htmlvar_name'   => [ 'default' => 'business_hours' ],
							'admin_title'    => [ 'default' => 'Business Hours' ],
							'frontend_title' => [ 'default' => 'Business Hours' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Select your business opening/operating hours.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-clock' ],
						],
						'behavior' => [
							'default_value' => [ 'default' => '' ],
						]
					],
				] ),

				// Contact Email
				FormFieldFactory::build_field_settings( [
					'id'     => 'contact_email',
					'title'  => __( 'Contact Email', 'geodirectory' ),
					'icon'   => 'far fa-envelope',
					'limit'  => 1,
					'description'  => __( 'Adds a email input. This can be used by other plugins if the field key remains `email`, for example by Ninja Forms.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'email' ],
							'field_type_key' => [ 'default' => 'contact_email' ],
							'htmlvar_name'   => [ 'default' => 'email' ],
							'admin_title'    => [ 'default' => 'Contact Email' ],
							'frontend_title' => [ 'default' => 'Email' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'You can enter the contact email for your listing.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'far fa-envelope' ],
						],
						'behavior' => [
							'is_required' => [ 'default' => true ],
						]
					],
				] ),

				// Company Logo
				FormFieldFactory::build_field_settings( [
					'id'     => 'logo',
					'title'  => __( 'Company Logo', 'geodirectory' ),
					'icon'   => 'far fa-image',
					'description'  => __( 'Adds a logo input. This can be used in conjunction with the `GD > Post Images` widget, there is a setting to allow it to use the logo if available. This can also be used by other plugins if the field key remains `logo`.', 'geodirectory' ),
//              'limit'    => 1,
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'file' ],
							'field_type_key' => [ 'default' => 'logo' ],
							'htmlvar_name'   => [ 'default' => 'logo' ],
							'admin_title'    => [ 'default' => 'Logo' ],
							'frontend_title' => [ 'default' => 'Logo' ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => 'You can upload your company logo.' ],
							'field_icon'    => [ 'default' => 'far fa-image' ],
						],
						'validation' => [
							'extra_fields.gd_file_types' => [ 'default' => '' ], //@todo
						]
					],
				] ),

				// Website
				FormFieldFactory::build_field_settings( [
					'id'     => 'website',
					'title'  => __( 'Website', 'geodirectory' ),
					'icon'   => 'fas fa-external-link-alt',
					'description'  => __( 'Adds a website input. This can be used by other plugins if the htmlvar remains `website`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type_key' => [ 'default' => 'website' ],
							'htmlvar_name'   => [ 'default' => 'website' ],
							'admin_title'    => [ 'default' => 'Website' ],
							'frontend_title' => [ 'default' => 'Website' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'You can enter your business or listing website.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-external-link-alt' ],
						],
					],
				] ),

				// Facebook
				FormFieldFactory::build_field_settings( [
					'id'     => 'facebook',
					'title'  => __( 'Facebook', 'geodirectory' ),
					'icon'   => 'fab fa-facebook',
					'description'  => __( 'Adds a facebook url input. This can be used by other plugins if the htmlvar remains `facebook`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type_key' => [ 'default' => 'facebook' ],
							'htmlvar_name'   => [ 'default' => 'facebook' ],
							'admin_title'    => [ 'default' => 'Facebook' ],
							'frontend_title' => [ 'default' => 'Facebook' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'You can enter your business or listing facebook url.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fab fa-facebook' ],
						],
					],
				] ),

				// X (Twitter)
				FormFieldFactory::build_field_settings( [
					'id'     => 'twitter',
					'title'  => __( 'X', 'geodirectory' ),
					'icon'   => 'fab fa-twitter',
					'description'  => __( 'Adds a X url input. This can be used by other plugins if the htmlvar remains `twitter`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type_key' => [ 'default' => 'twitter' ],
							'htmlvar_name'   => [ 'default' => 'twitter' ],
							'admin_title'    => [ 'default' => 'X' ],
							'frontend_title' => [ 'default' => 'X' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'You can enter your business or listing X url.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fab fa-twitter' ],
						],
					],
				] ),

				// Instagram
				FormFieldFactory::build_field_settings( [
					'id'     => 'instagram',
					'title'  => __( 'Instagram', 'geodirectory' ),
					'icon'   => 'fab fa-instagram',
					'description'  => __( 'Adds a instagram url input. This can be used by other plugins if the htmlvar remains `instagram`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type_key' => [ 'default' => 'instagram' ],
							'htmlvar_name'   => [ 'default' => 'instagram' ],
							'admin_title'    => [ 'default' => 'Instagram' ],
							'frontend_title' => [ 'default' => 'Instagram' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'You can enter your business or listing instagram url.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fab fa-instagram' ],
						],
					],
				] ),

				// TikTok
				FormFieldFactory::build_field_settings( [
					'id'     => 'tiktok',
					'title'  => __( 'TikTok', 'geodirectory' ),
					'icon'   => 'fab fa-tiktok',
					'description'  => __( 'Adds a TikTok url input. This can be used by other plugins if the htmlvar remains `tiktok`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'url' ],
							'field_type_key' => [ 'default' => 'tiktok' ],
							'htmlvar_name'   => [ 'default' => 'tiktok' ],
							'admin_title'    => [ 'default' => 'TikTok' ],
							'frontend_title' => [ 'default' => 'TikTok' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'You can enter your TikTok url.' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fab fa-tiktok' ],
						],
					],
				] ),

				// Terms & Conditions
				FormFieldFactory::build_field_settings( [
					'id'     => 'terms_conditions',
					'title'  => __( 'Terms & Conditions', 'geodirectory' ),
					'icon'   => 'fas fa-file-alt',
					'description'  => __( 'Adds a terms and conditions checkbox to your add listing page, the text links to your GD terms and conditions page set in the pages settings.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'checkbox' ],
							'field_type_key' => [ 'default' => 'terms_conditions' ],
							'htmlvar_name'   => [ 'default' => 'terms_conditions' ],
							'admin_title'    => [ 'default' => 'Terms &amp; Conditions' ],
							'frontend_title' => [ 'default' => 'Terms &amp; Conditions' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'Please accept our terms and conditions', 'geodirectory' ) ],
							'field_icon'    => [ 'default' => 'fas fa-file-alt' ],
						],
						'behavior' => [
							'is_required'  => [ 'default' => true ],
							'required_msg' => [ 'default' => __( 'You MUST accept our terms and conditions to continue.', 'geodirectory' ) ],
						],
					],
				] ),

				// Video
				FormFieldFactory::build_field_settings( [
					'id'     => 'video',
					'title'  => __( 'Video', 'geodirectory' ),
					'icon'   => 'fas fa-video',
					'description'  => __( 'Adds a video url/code input. This can be used by other plugins if the htmlvar remains `video`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'textarea' ],
							'field_type_key' => [ 'default' => 'video' ],
							'htmlvar_name'   => [ 'default' => 'video' ],
							'admin_title'    => [ 'default' => 'Video' ],
							'frontend_title' => [ 'default' => 'Video' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'Add video url or code here, YouTube, Vimeo etc.' ],
							'show_in'       => [ 'default' => [ '[owntab]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-video' ],
						],
					],
				] ),

				// Special Offers
				FormFieldFactory::build_field_settings( [
					'id'     => 'special_offers',
					'title'  => __( 'Special Offers', 'geodirectory' ),
					'icon'   => 'fas fa-gift',
					'description'  => __( 'Adds a Special Offers textarea input. This can be used by other plugins if the htmlvar remains `special_offers`.', 'geodirectory' ),
					'panels' => [
						'general' => [
							'type'           => [ 'default' => 'textarea' ],
							'field_type_key' => [ 'default' => 'special_offers' ],
							'htmlvar_name'   => [ 'default' => 'special_offers' ],
							'admin_title'    => [ 'default' => 'Special Offers' ],
							'frontend_title' => [ 'default' => 'Special Offers' ],
						],
						'display' => [
							'frontend_desc' => [ 'default' => 'Note: List any special offers (optional)' ],
							'show_in'       => [ 'default' => [ '[owntab]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-gift' ],
						],
					],
				] ),

				// Price
				FormFieldFactory::build_field_settings( [
					'id'     => 'price',
					'title'  => __( 'Price', 'geodirectory' ),
					'icon'   => 'fas fa-dollar-sign',
					'description'  => __( 'Adds a input for a price field. This will let you filter and sort by price.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'price' ],
							'htmlvar_name'   => [ 'default' => 'price' ],
							'admin_title'    => [ 'default' => 'Price' ],
							'frontend_title' => [ 'default' => 'Price' ],
						],
						'display'    => [
							'frontend_desc'   => [ 'default' => 'Enter the price in $ (no currency symbol)' ],
							'show_in'         => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'      => [ 'default' => 'fas fa-dollar-sign' ],
							'data_type'       => [ 'default' => 'DECIMAL' ],
							'is_price'        => [ 'default' => true ],
							'currency_symbol' => [ 'default' => '$' ],
							'decimal_point'   => [ 'default' => 2 ],
						],
						'behavior'   => [
							'cat_sort' => [ 'default' => true ],
						],
						'validation' => [
							'validation_pattern' => [ 'default' => '\d+(\.\d{2})?' ],
							'validation_msg'     => [ 'default' => 'Please enter number and decimal only ie: 100.50' ],
						],
					],
				] ),

				// Price Range
				FormFieldFactory::build_field_settings( [
					'id'     => 'price_range',
					'title'  => __( 'Price Range', 'geodirectory' ),
					'icon'   => 'fas fa-dollar-sign',
					'description'  => __( 'Adds a schema price range input.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'price_range' ],
							'htmlvar_name'   => [ 'default' => 'price_range' ],
							'admin_title'    => [ 'default' => 'Price Range' ],
							'frontend_title' => [ 'default' => 'Price Range' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Enter the price range for the business.' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-dollar-sign' ],
						],
						'behavior' => [
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Price Range/', 'geodirectory' ) . ',$,$$,$$$,$$$$' ],
						],
					],
				] ),

				// Property Status
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_status',
					'title'  => __( 'Property Status', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input to be able to set the status of a property ie: For Sale, For Rent', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'property_status' ],
							'htmlvar_name'   => [ 'default' => 'property_status' ],
							'admin_title'    => [ 'default' => 'Property Status' ],
							'frontend_title' => [ 'default' => 'Property Status' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Enter the status of the property.' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-home' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Status/,For Sale,For Rent,Sold,Let', 'geodirectory' ) ],
						],
					],
				] ),

				// Property Furnishing
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_furnishing',
					'title'  => __( 'Property Furnishing', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input to be able to set the furnishing status of a property ie: Unfurnished, Furnished', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'property_furnishing' ],
							'htmlvar_name'   => [ 'default' => 'property_furnishing' ],
							'admin_title'    => [ 'default' => 'Furnishing' ],
							'frontend_title' => [ 'default' => 'Furnishing' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Enter the furnishing status of the property.' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-th-large' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Status/,Unfurnished,Furnished,Partially furnished,Optional', 'geodirectory' ) ],
						],
					],
				] ),

				// Property Type
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_type',
					'title'  => __( 'Property Type', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input for the property type ie: Detached house, Apartment', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'property_type' ],
							'htmlvar_name'   => [ 'default' => 'property_type' ],
							'admin_title'    => [ 'default' => 'Property Type' ],
							'frontend_title' => [ 'default' => 'Property Type' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Select the property type.' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-home' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Type/,Detached house,Semi-detached house,Apartment,Bungalow,Semi-detached bungalow,Chalet,Town House,End-terrace house,Terrace house,Cottage', 'geodirectory' ) ],
						],
					],
				] ),

// Property Bedrooms
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_bedrooms',
					'title'  => __( 'Property Bedrooms', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input for the number of bedrooms.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'property_bedrooms' ],
							'htmlvar_name'   => [ 'default' => 'property_bedrooms' ],
							'admin_title'    => [ 'default' => 'Property Bedrooms' ],
							'frontend_title' => [ 'default' => 'Bedrooms' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Select the number of bedrooms' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-bed' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Bedrooms/,1,2,3,4,5,6,7,8,9,10', 'geodirectory' ) ],
						],
					],
				] ),

// Property Bathrooms
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_bathrooms',
					'title'  => __( 'Property Bathrooms', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input for the number of bathrooms.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'property_bathrooms' ],
							'htmlvar_name'   => [ 'default' => 'property_bathrooms' ],
							'admin_title'    => [ 'default' => 'Property Bathrooms' ],
							'frontend_title' => [ 'default' => 'Bathrooms' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Select the number of bathrooms' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-bath' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Bathrooms/,1,2,3,4,5,6,7,8,9,10', 'geodirectory' ) ],
						],
					],
				] ),

// Property Area
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_area',
					'title'  => __( 'Property Area', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a input for the property area.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'property_area' ],
							'htmlvar_name'   => [ 'default' => 'property_area' ],
							'admin_title'    => [ 'default' => 'Property Area' ],
							'frontend_title' => [ 'default' => 'Area (Sq Ft)' ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => 'Enter the Sq Ft value for the property' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-chart-area' ],
							'data_type'     => [ 'default' => 'DECIMAL' ],
						],
						'behavior'   => [
							'cat_sort' => [ 'default' => true ],
						],
						'validation' => [
							'validation_pattern' => [ 'default' => '\d+(\.\d{2})?' ],
							'validation_msg'     => [ 'default' => 'Please enter the property area in numbers only: 1500' ],
						],
					],
				] ),

// Property Features
				FormFieldFactory::build_field_settings( [
					'id'     => 'property_features',
					'title'  => __( 'Property Features', 'geodirectory' ),
					'icon'   => 'fas fa-home',
					'description'  => __( 'Adds a select input for the property features.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'multiselect' ],
							'field_type_key' => [ 'default' => 'property_features' ],
							'htmlvar_name'   => [ 'default' => 'property_features' ],
							'admin_title'    => [ 'default' => 'Property Features' ],
							'frontend_title' => [ 'default' => 'Features' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => 'Select the property features.' ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-plus-square' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Features/,Gas Central Heating,Oil Central Heating,Double Glazing,Triple Glazing,Front Garden,Garage,Private driveway,Off Road Parking,Fireplace', 'geodirectory' ) ],
						],
					],
				] ),

// X Feed
				FormFieldFactory::build_field_settings( [
					'id'     => 'twitter_feed',
					'title'  => __( 'X feed', 'geodirectory' ),
					'icon'   => 'fab fa-twitter',
					'description'  => __( 'Adds a input for X username and outputs feed.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'twitterusername' ],
							'htmlvar_name'   => [ 'default' => 'twitterusername' ],
							'admin_title'    => [ 'default' => 'X' ],
							'frontend_title' => [ 'default' => 'X' ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => 'Enter your X username' ],
							'show_in'       => [ 'default' => [ '[detail]', '[owntab]' ] ],
							'field_icon'    => [ 'default' => 'fab fa-twitter' ],
						],
						'validation' => [
							'validation_pattern' => [ 'default' => '^[A-Za-z0-9_]{1,32}$' ],
							'validation_msg'     => [ 'default' => 'Please enter a valid X username.' ],
						],
					],
				] ),

// Job Type
				FormFieldFactory::build_field_settings( [
					'id'     => 'job_type',
					'title'  => __( 'Job Type', 'geodirectory' ),
					'icon'   => 'fas fa-briefcase',
					'description'  => __( 'Adds a select input to be able to set the type of a job ie: Full Time, Part Time', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'job_type' ],
							'htmlvar_name'   => [ 'default' => 'job_type' ],
							'admin_title'    => [ 'default' => __( 'Job Type', 'geodirectory' ) ],
							'frontend_title' => [ 'default' => __( 'Job Type', 'geodirectory' ) ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'Select the type of job.', 'geodirectory' ) ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-briefcase' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Type/,Freelance,Full Time,Internship,Part Time,Temporary,Other', 'geodirectory' ) ],
						],
					],
				] ),

// Job Sector
				FormFieldFactory::build_field_settings( [
					'id'     => 'job_sector',
					'title'  => __( 'Job Sector', 'geodirectory' ),
					'icon'   => 'fas fa-briefcase',
					'description'  => __( 'Adds a select input to be able to set the type of a job Sector ie: Private Sector,Public Sector', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'select' ],
							'field_type_key' => [ 'default' => 'job_sector' ],
							'htmlvar_name'   => [ 'default' => 'job_sector' ],
							'admin_title'    => [ 'default' => __( 'Job Sector', 'geodirectory' ) ],
							'frontend_title' => [ 'default' => __( 'Job Sector', 'geodirectory' ) ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'Select the job sector.', 'geodirectory' ) ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-briefcase' ],
						],
						'behavior' => [
							'is_required'   => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'option_values' => [ 'default' => __( 'Select Sector/,Private Sector,Public Sector,Agencies', 'geodirectory' ) ],
						],
					],
				] ),

// Date of Birth
				FormFieldFactory::build_field_settings( [
					'id'     => 'dob',
					'title'  => __( 'Date of birth', 'geodirectory' ),
					'icon'   => 'fas fa-birthday-cake',
					'description'  => __( 'Adds a date input for users to enter their date of birth.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'date' ],
							'field_type_key' => [ 'default' => 'dob' ],
							'htmlvar_name'   => [ 'default' => 'dob' ],
							'admin_title'    => [ 'default' => __( 'Date of birth', 'geodirectory' ) ],
							'frontend_title' => [ 'default' => __( 'Date of birth', 'geodirectory' ) ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => __( 'Enter your date of birth.', 'geodirectory' ) ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-birthday-cake' ],
						],
						'behavior'   => [
							'cat_sort' => [ 'default' => true ],
						],
						'validation' => [
							'extra_fields.date_range' => [ 'default' => 'c-100:c+0' ],
						],
					],
				] ),

// Featured
				FormFieldFactory::build_field_settings( [
					'id'     => 'featured',
					'title'  => __( 'Featured', 'geodirectory' ),
					'icon'   => 'fas fa-certificate',
					'limit'  => 1,
					'description'  => __( 'Mark listing as a featured.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'checkbox' ],
							'field_type_key' => [ 'default' => 'featured' ],
							'htmlvar_name'   => [ 'default' => 'featured' ],
							'admin_title'    => [ 'default' => 'Featured' ],
							'frontend_title' => [ 'default' => 'Is Featured?' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'Mark listing as a featured.', 'geodirectory' ) ],
						],
						'behavior' => [
							'for_admin_use' => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'default_value' => [ 'default' => '0' ],
						],
					],
				] ),

// Distance To
				FormFieldFactory::build_field_settings( [
					'id'     => 'distanceto',
					'title'  => __( 'Distance To', 'geodirectory' ),
					'icon'   => 'fas fa-road',
					'limit'  => 1,
					'description'  => __( 'Adds a input for GPS coordinates that will then output the place distance to that point.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'distanceto' ],
							'htmlvar_name'   => [ 'default' => 'distanceto' ],
							'admin_title'    => [ 'default' => 'Distance To' ],
							'frontend_title' => [ 'default' => 'Distance To' ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => 'Enter GPS coordinates like `53.347302,-6.258953`' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-road' ],
							'css_class'     => [ 'default' => 'gd-distance-to' ],
						],
						'validation' => [
							'validation_pattern' => [ 'default' => '(-?\d{1,3}\.\d+),(-?\d{1,3}\.\d+)' ],
							'validation_msg'     => [ 'default' => 'Please enter valid GPS coordinates.' ],
						],
					],
				] ),

// Service Distance
				FormFieldFactory::build_field_settings( [
					'id'     => 'service_distance',
					'title'  => __( 'Service Distance', 'geodirectory' ),
					'icon'   => 'fas fa-arrows-alt-h',
					'limit'  => 1,
					'description'  => __( 'Adds a input to set service area in distance.', 'geodirectory' ),
					'panels' => [
						'general'    => [
							'type'           => [ 'default' => 'text' ],
							'field_type_key' => [ 'default' => 'service_distance' ],
							'htmlvar_name'   => [ 'default' => 'service_distance' ],
							'admin_title'    => [ 'default' => 'Service Distance' ],
							'frontend_title' => [ 'default' => 'Service Distance' ],
						],
						'display'    => [
							'frontend_desc' => [ 'default' => 'Enter your service area in distance. Ex: 10' ],
							'show_in'       => [ 'default' => [ '[detail]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-arrows-alt-h' ],
							'css_class'     => [ 'default' => 'gd-service-distance' ],
						],
						'validation' => [
							'validation_pattern' => [ 'default' => '\d+(\.\d{2})?' ],
							'validation_msg'     => [ 'default' => 'Please enter valid service area in distance.' ],
						],
					],
				] ),

// Private Address
				FormFieldFactory::build_field_settings( [
					'id'     => 'private_address',
					'title'  => __( 'Private Address', 'geodirectory' ),
					'icon'   => 'fas fa-eye-slash',
					'limit'  => 1,
					'description'  => __( 'Adds a checkbox in add listing page to allow users to mark their listings address as a private.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'checkbox' ],
							'field_type_key' => [ 'default' => 'private_address' ],
							'htmlvar_name'   => [ 'default' => 'private_address' ],
							'admin_title'    => [ 'default' => 'Private Address' ],
							'frontend_title' => [ 'default' => 'Private Address' ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'This will prevent address and location info from displaying to the users.', 'geodirectory' ) ],
							'field_icon'    => [ 'default' => 'fas fa-eye-slash' ],
							'css_class'     => [ 'default' => 'gd-private-address' ],
						],
						'behavior' => [
							'default_value' => [ 'default' => '0' ],
						],
					],
				] ),

// Temporarily Closed
				FormFieldFactory::build_field_settings( [
					'id'     => 'temp_closed',
					'title'  => __( 'Temporarily Closed', 'geodirectory' ),
					'icon'   => 'fas fa-exclamation-circle',
					'limit'  => 1,
					'description'  => __( 'Mark listing as temporarily closed, this will set business hours as closed and show a message in the notifications section.', 'geodirectory' ),
					'panels' => [
						'general'  => [
							'type'           => [ 'default' => 'checkbox' ],
							'field_type_key' => [ 'default' => 'temp_closed' ],
							'htmlvar_name'   => [ 'default' => 'temp_closed' ],
							'admin_title'    => [ 'default' => __( 'Temporarily Closed', 'geodirectory' ) ],
							'frontend_title' => [ 'default' => __( 'Temporarily Closed', 'geodirectory' ) ],
						],
						'display'  => [
							'frontend_desc' => [ 'default' => __( 'If your business is temporarily closed select this to let customers and search engines know.', 'geodirectory' ) ],
							'show_in'       => [ 'default' => [ '[detail]', '[listing]', '[mapbubble]' ] ],
							'field_icon'    => [ 'default' => 'fas fa-exclamation-circle' ],
						],
						'behavior' => [
							'for_admin_use' => [ 'default' => true ],
							'cat_sort'      => [ 'default' => true ],
							'default_value' => [ 'default' => '0' ],
						],
					],
				] ),

				//          [
//              'id'       => 'contact_emailx',
//              'title'    => __('Contact Email','geodirectory'),
//              'icon'     => 'fa-solid fa-envelope',
//              'limit'    => 1,
//              'base_id'  => 'text', // <-- The actual field type to create.
//              'defaults' => [           // <-- The values to apply to the new instance.
//                 'field_type'  => 'email',
//                 'field_type_key'  => 'contact_email',
//                 'label'       =>  __('Contact Email','geodirectory'),
//                 'frontend_title'       =>  __('Contact Email','geodirectory'),
//                 'htmlvar_name'         => 'email',
//                 'frontend_desc' => __('You can enter the contact email for your listing.','geodirectory'),
//                 'is_active'   => true,
//                 'is_required' => true,
//                 'placeholder_value' =>'info@example.com',
//              ]
//           ],
//           [
//              'title'  => __( 'Text', 'geodirectory' ),
//              'id'     => 'textx',
//              'icon'   => 'fas fa-minus',
//              'fields' => FormFieldFactory::build( [
//                 'type'           => [ 'default' => 'text' ],
//                 'admin_title'    => [ 'default' => 'New Text Field' ],
//                 'frontend_title' => [ 'default' => 'New Text Field' ],
//                 'htmlvar_name',
//                 'field_icon',
//                 'frontend_desc',
//                 'placeholder',
//                 'default_value',
//                 'is_required',
//                 'required_msg',
//                 'is_active',
//                 'for_admin_use',
//                 'show_in',
//                 'css_class',
//                 'cat_sort',
//              ] ),
//           ]

			]
		]
	]
];

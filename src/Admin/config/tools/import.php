<?php
/**
 * V3 Import/ Export Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This is a sample configuration array for the Tools page.
 * It would be returned by the get_config() method in your Tools.php class.
 */
return [
	'id'    => 'import',
	'name'  => 'Import',
	'icon'  => 'fa-solid fa-upload',
	'subsections' => [
		[
			'id'    => 'import_listings',
			'name'  => 'Listings',
			'fields' => [
				[
					'type'  => 'alert',
					'alert_type' => 'info',
					'description' => '<strong>Important:</strong> Do not use Excel as it adds characters that breaks the import process. <a href="#">How to prepare CSV file to import</a>.',
				],
				[
					'id'      => 'import_conflict',
					'type'    => 'select',
					'label'   => 'If post ID exists',
					'options' => [
						'skip'   => 'Skip row',
						'update' => 'Update row',
					],
					'default' => 'skip',
				],
				[
					'id'    => 'import_file_upload',
					'type'  => 'custom_html',
					'label' => 'Upload CSV file',
					'html'  => '
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-secondary">Select File</button>
                                    <a href="#" class="btn btn-link ms-3">How To Get Sample CSV File To Prepare Import Listings</a>
                                </div>
                            ',
				],
				[
					'id'          => 'run_import_listings_button',
					'type'        => 'action_button',
					'label'       => 'Import Listings',
					'button_text' => 'Import CSV',
					'button_class'=> 'btn-primary',
					'ajax_action' => 'geodir_import_listings',
				],
			],
		],
		[
			'id'    => 'import_categories',
			'name'  => 'Categories',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Category import options will be configured here.']
			],
		],
		[
			'id'    => 'import_reviews',
			'name'  => 'Reviews',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Review import options will be configured here.']
			],
		],
		[
			'id'    => 'import_settings',
			'name'  => 'Settings',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Settings import options will be configured here.']
			],
		],
		[
			'id'    => 'import_post_types',
			'name'  => 'Post Types',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Post Type import options will be configured here.']
			],
		],
		[
			'id'    => 'import_custom_fields',
			'name'  => 'Custom Fields',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Custom Field import options will be configured here.']
			],
		],
		[
			'id'    => 'import_cpt_tabs',
			'name'  => 'CPT Tabs',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'CPT Tab import options will be configured here.']
			],
		],
		[
			'id'    => 'import_locations',
			'name'  => 'Locations',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Location import options will be configured here.']
			],
		],
		[
			'id'    => 'import_locations_cpt_desc',
			'name'  => 'Locations + CPT Description',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Locations + CPT Description import options will be configured here.']
			],
		],
		[
			'id'    => 'import_category_locations_desc',
			'name'  => 'Category + Locations Description',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Category + Locations Description import options will be configured here.']
			],
		],
	],
];



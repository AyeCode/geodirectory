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
	'id'    => 'export',
	'name'  => 'Export',
	'icon'  => 'fa-solid fa-download',
	'subsections' => [
		[
			'id'    => 'export_listingsz',
			'name'  => 'Listingsz',
			'fields' => [
				[
					'id'      => 'export_post_type',
					'type'    => 'select',
					'label'   => 'Post Type',
					'options' => [
						'gd_place' => 'Places',
						'gd_event' => 'Events',
					],
					'default' => 'gd_place',
				],
				[
					'id'      => 'export_max_entries',
					'type'    => 'select',
					'label'   => 'Max entries per csv file',
					'options' => [
						'5000' => '5000',
						'1000' => '1000',
						'500' => '500',
					],
					'default' => '5000',
				],
				[
					'id'    => 'export_date_filter',
					'type'  => 'custom_html',
					'label' => 'Filter published dates',
					'html'  => '
                                <div class="row g-3">
                                    <div class="col">
                                        <input type="date" class="form-control" placeholder="Start date">
                                    </div>
                                    <div class="col">
                                        <input type="date" class="form-control" placeholder="End date">
                                    </div>
                                </div>
                            '
				],
				[
					'id'          => 'run_export_listings_button',
					'type'        => 'action_button',
					'label'       => 'Export Listings',
					'button_text' => 'Export CSV',
					'button_class'=> 'btn-primary',
					'ajax_action' => 'geodir_export_listings',
				],
			],
		],
		// listings
		/*
		 action
geodir_import_export
task
export_posts
_pt
gd_place
_n
0
_nonce
1e989b8e0d
_p
1
gd_imex[start_date]
2025-08-06
gd_imex[end_date]
2025-08-14
		 */
		[
			'id'             => 'export_listings',
			'name'           => __( 'Listings', 'geodirectory' ),
			'page_title'           => __( 'Export Listings', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=import&subsection=import_listings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Import...', 'geodirectory' ),
			),
			//'icon'           => 'fa-solid fa-bolt',
			'type'           => 'action_page', // The new page type
			'button_text'    => __( 'Export CSV', 'geodirectory' ),
			'button_class'   => 'btn-success',
			'ajax_action'    => 'export_listings', // The unique ID for this page's action
			'fields' => [
				[
					'id'      => 'task',
					'type'    => 'text',
					'label'   => __( 'Source URL', 'geodirectory' ),
					'desc'    => __( 'Enter the URL of the data file to import.', 'geodirectory' ),
					'default'   => 'export_posts',
				],
				[
					'id'      => '_pt',
					'type'    => 'select',
					'options' => geodir_get_posttypes('options-plural' ),
					'label'   => __( 'Post type', 'geodirectory' ),
					'default'   => 'gd_place',
//					'desc'    => __( 'Enable to replace existing entries with imported ones.', 'geodirectory' ),
				],
				[
					'id'      => '_n',
					'type'    => 'select',
					'options' =>  [
						100     => 100,
						200     => 200,
						500     => 500,
						1000    => 1000,
						2000    => 2000,
						5000    => 5000,
						10000   => 10000,
						20000   => 20000,
						50000   => 50000,
						100000  => 100000
					],
					'label'   => __( 'Max entries per csv file', 'geodirectory' ),
					'description'    => __( 'The maximum number of entries per csv file (defaults to 5000, you might want to lower this to prevent memory issues on some installs)', 'geodirectory' ),
					'default'   => 5000,
				],
				[
					'id'      => 'start_date',
					'type'    => 'text',
					'label'   => __( 'Filter published dates (start)', 'geodirectory' ),
					'extra_attributes' => array(
						'data-aui-init' => 'flatpickr'
					)
//					'desc'    => __( 'Enter the URL of the data file to import.', 'geodirectory' ),
//					'default'   => 'export_posts',
				],
				[
					'id'      => 'end_date',
					'type'    => 'text',
					'label'   => __( 'Filter published dates (end)', 'geodirectory' ),
					'extra_attributes' => array(
						'data-aui-init' => 'flatpickr'
					)
//					'desc'    => __( 'Enter the URL of the data file to import.', 'geodirectory' ),
//					'default'   => 'export_posts',
				],
			],
		],
		[
			'id'    => 'export_categories',
			'name'  => 'Categories',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Category export options will be configured here.']
			],
		],
		[
			'id'    => 'export_reviews',
			'name'  => 'Reviews',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Review export options will be configured here.']
			],
		],
		[
			'id'    => 'export_settings',
			'name'  => 'Settings',
			'page_title' => 'Export Settings', // THIS appears as the main <h2> title on the page
			'description'  => sprintf(
				'<a href="#section=import&subsection=import_settings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Import...', 'geodirectory' ),
			),
			'fields' => [
				//['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Settings export options will be configured here.'],
				[
					'id'           => 'export_settings_link',
					'type'         => 'link_button',
					'label'        => __( 'Export Settings', 'geodirectory' ),
					'description'  => __( 'Download a JSON backup of your settings.', 'geodirectory' ),
					'button_text'  => '<i class="fa-solid fa-download me-2"></i>' . __( 'Download Settings' ),
					'button_class' => 'btn-success',
					'url'          => esc_url( add_query_arg( array('action' => 'geodir_import_export','task' => 'export_settings','_nonce'=> wp_create_nonce( 'geodir_import_export_nonce' ) ), admin_url( 'admin-ajax.php' ) ) ),
					//'target'       => '_blank' // Optional: opens the link in a new tab
				]
			],
		],
		[
			'id'    => 'export_post_types',
			'name'  => 'Post Types',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Post Type export options will be configured here.']
			],
		],
		[
			'id'    => 'export_custom_fields',
			'name'  => 'Custom Fields',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Custom Field export options will be configured here.']
			],
		],
		[
			'id'    => 'export_cpt_tabs',
			'name'  => 'CPT Tabs',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'CPT Tab export options will be configured here.']
			],
		],
		[
			'id'    => 'export_locations',
			'name'  => 'Locations',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Location export options will be configured here.']
			],
		],
		[
			'id'    => 'export_locations_cpt_desc',
			'name'  => 'Locations + CPT Description',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Locations + CPT Description export options will be configured here.']
			],
		],
		[
			'id'    => 'export_category_locations_desc',
			'name'  => 'Category + Locations Description',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Category + Locations Description export options will be configured here.']
			],
		],
	],
];

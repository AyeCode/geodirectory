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
	'icon'  => 'fa-solid fa-upload',
	'subsections' => [
		// listings
		[
			'id'             => 'export_posts',
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
			'ajax_action'    => 'export_posts',
			'fields' => [
				[
					'id'      => '_pt',
					'type'    => count( geodir_get_posttypes('options-plural' ) ) == 1 ? 'hidden' : 'select',
					'options' => geodir_get_posttypes('options-plural' ),
					'label'   => __( 'Post type', 'geodirectory' ),
					'default'   => 'gd_place',
					'description'    => __( 'The post type to export.', 'geodirectory' ),
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
					),
					'placeholder'    => __( 'Start date', 'geodirectory' ),
					'description'    => __( 'Filter results no earlier than this date.', 'geodirectory' ),
				],
				[
					'id'      => 'end_date',
					'type'    => 'text',
					'label'   => __( 'Filter published dates (end)', 'geodirectory' ),
					'extra_attributes' => array(
						'data-aui-init' => 'flatpickr'
					),
					'placeholder'    => __( 'End date', 'geodirectory' ),
					'description'    => __( 'Filter results no later than this date.', 'geodirectory' ),
				],
			],
		],
		// Categories
		[
			'id'             => 'export_cats',
			'name'           => __( 'Categories', 'geodirectory' ),
			'page_title'           => __( 'Export Categories', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=import&subsection=import_cats" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Import...', 'geodirectory' ),
			),
			//'icon'           => 'fa-solid fa-bolt',
			'type'           => 'action_page', // The new page type
			'button_text'    => __( 'Export CSV', 'geodirectory' ),
			'button_class'   => 'btn-success',
			'ajax_action'    => 'export_cats',

			'fields' => [
				[
					'id'      => '_pt',
					'type'    => count( geodir_get_posttypes('options-plural' ) ) == 1 ? 'hidden' : 'select',
					'options' => geodir_get_posttypes('options-plural' ),
					'label'   => __( 'Post type', 'geodirectory' ),
					'default'   => 'gd_place',
					'description'    => __( 'The post type to export.', 'geodirectory' ),
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
			],
		],
		// Reviews
		[
			'id'             => 'export_reviews',
			'name'           => __( 'Reviews', 'geodirectory' ),
			'page_title'           => __( 'Export Reviews', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=import&subsection=import_reviews" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Import...', 'geodirectory' ),
			),
			//'icon'           => 'fa-solid fa-bolt',
			'type'           => 'action_page', // The new page type
			'button_text'    => __( 'Export CSV', 'geodirectory' ),
			'button_class'   => 'btn-success',
			'ajax_action'    => 'export_reviews',
			'fields' => [
				[
					'id'      => '_pt',
					'type'    => count( geodir_get_posttypes('options-plural' ) ) == 1 ? 'hidden' : 'select',
					'options' => array('' => __( 'All', 'geodirectory' )) + geodir_get_posttypes('options-plural' ),
					'label'   => __( 'Post type', 'geodirectory' ),
					'default'   => 'gd_place',
					'description'    => __( 'The post type to export.', 'geodirectory' ),
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
					),
					'placeholder'    => __( 'Start date', 'geodirectory' ),
					'description'    => __( 'Filter results no earlier than this date.', 'geodirectory' ),
				],
				[
					'id'      => 'end_date',
					'type'    => 'text',
					'label'   => __( 'Filter published dates (end)', 'geodirectory' ),
					'extra_attributes' => array(
						'data-aui-init' => 'flatpickr'
					),
					'placeholder'    => __( 'End date', 'geodirectory' ),
					'description'    => __( 'Filter results no later than this date.', 'geodirectory' ),
				],
				[
					'id'      => '_min_rating',
					'type'    => 'select',
					'options' =>  [
						''  => __( 'Any', 'geodirectory' ),
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
					],
					'label'   => __( 'Rating (minimum)', 'geodirectory' ),
					'description'    => __( 'Filter by minimum rating.', 'geodirectory' ),
				],
				[
					'id'      => '_max_rating',
					'type'    => 'select',
					'options' =>  [
						''  => __( 'Any', 'geodirectory' ),
						'1' => '1',
						'2' => '2',
						'3' => '3',
						'4' => '4',
						'5' => '5',
					],
					'label'   => __( 'Rating (maximum)', 'geodirectory' ),
					'description'    => __( 'Filter by maximum rating.', 'geodirectory' ),
				],
				[
					'id'      => '_status',
					'type'    => 'select',
					'options' =>  [
						'any'   => __( 'Any', 'geodirectory' ),
						'approve'   => __( 'Approved', 'geodirectory' ),
						'hold'   => __( 'Pending', 'geodirectory' ),
						'spam'   => __( 'Spam', 'geodirectory' ),
						'trash'   => __( 'Trashed', 'geodirectory' ),
					],
					'label'   => __( 'Status', 'geodirectory' ),
					'description'    => __( 'Filter by rating status.', 'geodirectory' ),
				]
			],
		],

		// Reviews
		[
			'id'             => 'export_settings',
			'name'           => __( 'Settings', 'geodirectory' ),
			'page_title'           => __( 'Export Settings', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=import&subsection=import_settings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Import...', 'geodirectory' ),
			),
			//'icon'           => 'fa-solid fa-bolt',
			'type'           => 'action_page', // The new page type
			'button_text'    => __( 'Export JSON', 'geodirectory' ),
			'button_class'   => 'btn-success',
			'ajax_action'    => 'export_settings',
			'fields' => [
				['type' => 'alert', 'alert_type' => 'info', 'description' => __( 'Settings exports can contain sensitive information and should be kept secret.', 'geodirectory' )]
			],
		],

//		[
//			'id'    => 'export_settings',
//			'name'  => 'Settings',
//			'page_title' => 'Export Settings', // THIS appears as the main <h2> title on the page
//			'description'  => sprintf(
//				'<a href="#section=import&subsection=import_settings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
//				__( 'Switch to Import...', 'geodirectory' ),
//			),
//			'fields' => [
//				//['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Settings export options will be configured here.'],
//				[
//					'id'           => 'export_settings_link',
//					'type'         => 'link_button',
//					'label'        => __( 'Export Settings', 'geodirectory' ),
//					'description'  => __( 'Download a JSON backup of your settings.', 'geodirectory' ),
//					'button_text'  => '<i class="fa-solid fa-download me-2"></i>' . __( 'Download Settings' ),
//					'button_class' => 'btn-success',
//					'url'          => esc_url( add_query_arg( array('action' => 'geodir_import_export','task' => 'export_settings','_nonce'=> wp_create_nonce( 'geodir_import_export_nonce' ) ), admin_url( 'admin-ajax.php' ) ) ),
//					//'target'       => '_blank' // Optional: opens the link in a new tab
//				]
//			],
//		],
//		[
//			'id'    => 'export_post_types',
//			'name'  => 'Post Types',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Post Type export options will be configured here.']
//			],
//		],
//		[
//			'id'    => 'export_custom_fields',
//			'name'  => 'Custom Fields',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Custom Field export options will be configured here.']
//			],
//		],
//		[
//			'id'    => 'export_cpt_tabs',
//			'name'  => 'CPT Tabs',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'CPT Tab export options will be configured here.']
//			],
//		],
//		[
//			'id'    => 'export_locations',
//			'name'  => 'Locations',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Location export options will be configured here.']
//			],
//		],
//		[
//			'id'    => 'export_locations_cpt_desc',
//			'name'  => 'Locations + CPT Description',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Locations + CPT Description export options will be configured here.']
//			],
//		],
//		[
//			'id'    => 'export_category_locations_desc',
//			'name'  => 'Category + Locations Description',
//			'fields' => [
//				['type' => 'alert', 'alert_type' => 'secondary', 'description' => 'Category + Locations Description export options will be configured here.']
//			],
//		],
	],
];

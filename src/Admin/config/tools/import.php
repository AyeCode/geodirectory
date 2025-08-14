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
	'icon'  => 'fa-solid fa-download',
	'keywords' => ['upload', 'csv', 'json', 'data', 'migration', 'batch', 'bulk'],
	'subsections' => [
		[
			'id'    => 'import_listings',
			'name'           => __( 'Listings', 'geodirectory' ),
			'page_title'           => __( 'Import Listings', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=export&subsection=export_listings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Export...', 'geodirectory' ),
			),
			'type'           => 'import_page',
			'button_text'    => __( 'Import Listings', 'geodirectory' ),
			'button_class'   => 'btn-warning',
			'ajax_action'    => 'import_listings',
			'accept_file_type' => 'csv',
			'keywords'       => ['posts', 'cpt', 'business', 'places', 'items'],

			'fields' => [
				[
					'id'      => 'update_existing',
					'type'    => 'select',
					'label'   => __( 'If post ID exists','geodirectory' ),
					'description'   => __( 'If the ID column exists in the CSV, you can either update or skip the row.','geodirectory' ),
					'options' => [
						'0'   => __( 'Skip row','geodirectory' ),
						'1' => __('Update row','geodirectory' ),
					],
					'default' => '0',
				],
			],
		],
		[
			'id'    => 'import_cats',
			'name'           => __( 'Categories', 'geodirectory' ),
			'page_title'           => __( 'Import Categories', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=export&subsection=export_cats" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Export...', 'geodirectory' ),
			),
			'type'           => 'import_page',
			'button_text'    => __( 'Import Categories', 'geodirectory' ),
			'button_class'   => 'btn-warning',
			'ajax_action'    => 'import_cats',
			'accept_file_type' => 'csv',
			'keywords'       => ['taxonomy', 'terms', 'tags'],

			'fields' => [
				[
					'id'      => 'update_existing',
					'type'    => 'select',
					'label'   => __( 'If cat_id/cat_slug exists','geodirectory' ),
					'description'   => __( 'If the ID or slug column exists in the CSV, you can either update or skip the row.','geodirectory' ),
					'options' => [
						'0'   => __( 'Skip row','geodirectory' ),
						'1' => __('Update row','geodirectory' ),
					],
					'default' => '0',
				],
			],
		],
		[
			'id'    => 'import_reviews',
			'name'           => __( 'Reviews', 'geodirectory' ),
			'page_title'           => __( 'Import Reviews', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=export&subsection=export_reviews" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Export...', 'geodirectory' ),
			),
			'type'           => 'import_page',
			'button_text'    => __( 'Import Reviews', 'geodirectory' ),
			'button_class'   => 'btn-warning',
			'ajax_action'    => 'import_reviews',
			'accept_file_type' => 'csv',
			'keywords'       => ['comments', 'ratings', 'feedback'],

			'fields' => [
				[
					'id'      => 'update_existing',
					'type'    => 'select',
					'label'   => __( 'If Comment ID exists','geodirectory' ),
					'description'   => __( 'If the comment_ID column exists in the CSV, you can either update the review or it can be skipped.','geodirectory' ),
					'options' => [
						'0'   => __( 'Skip row','geodirectory' ),
						'1' => __('Update row','geodirectory' ),
					],
					'default' => '0',
				],
			],
		],
		[
			'id'    => 'import_settings',
			'name'           => __( 'Settings', 'geodirectory' ),
			'page_title'           => __( 'Import Settings', 'geodirectory' ),
			'description'  => sprintf(
				'<a href="#section=export&subsection=export_settings" ><i class="fa-solid fa-shuffle"></i> %s</a>',
				__( 'Switch to Export...', 'geodirectory' ),
			),
			'type'           => 'import_page',
			'button_text'    => __( 'Import Settings', 'geodirectory' ),
			'button_class'   => 'btn-warning',
			'ajax_action'    => 'import_settings',
			'accept_file_type' => 'json',
			'keywords'       => ['options', 'configuration', 'setup', 'plugin'],

			'fields' => [
				['type' => 'alert', 'alert_type' => 'info', 'description' => __( 'Please make sure you have backed up your settings before attempting an import.', 'geodirectory' )],
			],
		],
	],
];

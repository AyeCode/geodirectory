<?php
/**
 * V3 Import/Export Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'import_export',
	'name'        => __( 'Import & Export', 'geodirectory' ),
	'icon'        => 'fa-solid fa-right-left',
	'description' => __( 'Tools for importing and exporting GeoDirectory data such as listings, categories, reviews, and plugin settings.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: Listings
		 */
		array(
			'id'          => 'listings',
			'name'        => __( 'Listings', 'geodirectory' ),
			'description' => __( 'Import and export listings using a CSV file. You can download a sample CSV file to see the required format.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'import_export_listings_tool',
					'type'    => 'custom', // @todo: This will require a custom field renderer to output the import/export UI.
					'label'   => __( 'Listings Import/Export', 'geodirectory' ),
					'searchable' => array('import', 'export', 'csv', 'listings', 'posts', 'data', 'tool'),
				),
			),
		),

		/**
		 * Subsection: Categories
		 */
		array(
			'id'          => 'categories',
			'name'        => __( 'Categories', 'geodirectory' ),
			'description' => __( 'Import and export listing categories using a CSV file.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'import_export_categories_tool',
					'type'    => 'custom', // @todo: This will require a custom field renderer.
					'label'   => __( 'Categories Import/Export', 'geodirectory' ),
					'searchable' => array('import', 'export', 'csv', 'categories', 'tags', 'taxonomies', 'tool'),
				),
			),
		),

		/**
		 * Subsection: Reviews
		 */
		array(
			'id'          => 'reviews',
			'name'        => __( 'Reviews', 'geodirectory' ),
			'description' => __( 'Import and export listing reviews using a CSV file.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'import_export_reviews_tool',
					'type'    => 'custom', // @todo: This will require a custom field renderer.
					'label'   => __( 'Reviews Import/Export', 'geodirectory' ),
					'searchable' => array('import', 'export', 'csv', 'reviews', 'ratings', 'comments', 'tool'),
				),
			),
		),

		/**
		 * Subsection: Settings
		 */
		array(
			'id'          => 'settings',
			'name'        => __( 'Settings', 'geodirectory' ),
			'description' => __( 'Export your GeoDirectory plugin settings for backup, or import settings from another site.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'import_export_settings_tool',
					'type'    => 'custom', // @todo: This will require a custom field renderer.
					'label'   => __( 'Settings Import/Export', 'geodirectory' ),
					'searchable' => array('import', 'export', 'json', 'settings', 'options', 'configuration', 'backup', 'tool'),
				),
			),
		),
	)
);

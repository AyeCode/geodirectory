<?php
/**
 * V3 Developer Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'developer',
	'name'        => __( 'Developer', 'geodirectory' ),
	'icon'        => 'fa-solid fa-user-gear',
	'description' => __( 'Access developer settings, and other advanced actions.', 'geodirectory' ),
	'subsections' => array(
		/**
		 * Subsection: Developer
		 */
		array(
			'id'          => 'developer',
			'name'        => __( 'Developer', 'geodirectory' ),
			'description' => __( 'Advanced settings for developers, debugging, and performance.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'fast_ajax',
					'type'    => 'toggle',
					'label'   => __( 'Enable Fast AJAX', 'geodirectory' ),
					'description' => __( 'Uses an MU-plugin to speed up WordPress AJAX requests for improved performance.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('ajax', 'fast', 'performance', 'speed', 'mu-plugin'),
				),
				array(
					'id'      => 'enable_hints',
					'type'    => 'toggle',
					'label'   => __( 'Enable admin hints', 'geodirectory' ),
					'description' => __( 'Show helpful hints and notifications in the admin area for new users.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('hints', 'admin', 'tips', 'help', 'notifications'),
				),
				array(
					'id'      => 'enable_404_rescue',
					'type'    => 'toggle',
					'label'   => __( 'Enable 404 rescue', 'geodirectory' ),
					'description' => __( 'When a 404 error occurs for a GD post, attempt to find the correct URL and redirect.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('404', 'redirect', 'rescue', 'not found', 'error'),
				),
				array(
					'id'      => 'enable_big_data',
					'type'    => 'toggle',
					'label'   => __( 'Enable BIG Data Optimizations', 'geodirectory' ),
					'description' => __( 'Optimizations for directories with over 50,000 listings. May slow down smaller sites.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('big data', 'performance', 'speed', 'optimization', 'database', 'large'),
				),
				array(
					'id'      => 'usage_tracking',
					'type'    => 'toggle',
					'label'   => __( 'Allow Usage Tracking', 'geodirectory' ),
					'description' => __( 'Help improve GeoDirectory by sending non-sensitive diagnostic and usage data.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('tracking', 'usage', 'telemetry', 'diagnostics'),
				),
			)
		),

		/**
		 * Subsection: Uninstall
		 */
		array(
			'id'          => 'uninstall',
			'name'        => __( 'Uninstall', 'geodirectory' ),
			'description' => __( 'Control what happens when the plugin is removed from your site.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'admin_uninstall',
					'type'    => 'toggle',
					'label'   => __( 'Remove all data on uninstall', 'geodirectory' ),
					'description' => __( 'DANGER: If enabled, all GeoDirectory settings, listings, and data will be permanently deleted when you deactivate and delete the plugin.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('uninstall', 'delete', 'remove', 'data', 'reset'),
				),
			)
		),
	)
);

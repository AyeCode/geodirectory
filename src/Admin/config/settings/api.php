<?php
/**
 * V3 API Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'id'          => 'api',
	'name'        => __( 'API', 'geodirectory' ),
	'icon'        => 'fa-solid fa-code',
	'description' => __( 'Manage REST API settings and generate API keys for external integrations.', 'geodirectory' ),
	'subsections' => array(

		/**
		 * Subsection: General Settings
		 */
		array(
			'id'          => 'settings',
			'name'        => __( 'General Settings', 'geodirectory' ),
			'description' => __( 'Enable or disable the REST API and configure its general behavior.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'rest_api_enabled',
					'type'    => 'toggle',
					'label'   => __( 'Enable REST API', 'geodirectory' ),
					'description' => __( 'Allow external applications to interact with your site\'s data through the REST API.', 'geodirectory' ),
					'default' => true,
					'searchable' => array('api', 'rest', 'enable', 'disable', 'integration'),
				),
				array(
					'id'      => 'rest_api_external_image',
					'type'    => 'toggle',
					'label'   => __( 'Allow External Images via API', 'geodirectory' ),
					'description' => __( 'Allow listings created via the API to use externally hosted images instead of uploading them to your site.', 'geodirectory' ),
					'default' => false,
					'searchable' => array('api', 'rest', 'images', 'external', 'cdn', 'upload'),
				),
			),
		),

		/**
		 * Subsection: API Keys
		 */
		array(
			'id'          => 'keys',
			'name'        => __( 'API Keys', 'geodirectory' ),
			'description' => __( 'Create and manage API keys to grant access to external applications.', 'geodirectory' ),
			'fields'      => array(
				array(
					'id'      => 'api_keys_manager',
					'type'    => 'custom', // @todo: This will require a custom field renderer to output the API key management UI.
					'label'   => __( 'API Keys Manager', 'geodirectory' ),
					'searchable' => array('api', 'keys', 'rest', 'integration', 'authentication', 'token'),
				),
			),
		),
	)
);

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
		// NEW: API Keys List Table Section
		[
			'id'    => 'api_keys',
			'name'  => __( 'API Keys', 'geodirectory' ),
			'icon'  => 'fa-solid fa-key',
			'type'  => 'list_table',

			'table_config' => [
				'singular' => 'API Key',
				'plural'   => 'API Keys',
				'ajax_action_get' => 'get_api_keys',

				'columns' => [
					'description'   => [ 'label' => __( 'Description', 'geodirectory' ) ],
					'truncated_key' => [ 'label' => __( 'Key Ending In', 'geodirectory' ) ],
//					'user_id'       => [ 'label' => __( 'User ID', 'geodirectory' ) ],
					'user_name'       => [ 'label' => __( 'User', 'geodirectory' ) ], // dynamic column
					'permissions'   => [ 'label' => __( 'Permissions', 'geodirectory' ) ],
					'last_access'   => [ 'label' => __( 'Last Access', 'geodirectory' ) ],
				],
			],

			'modal_config' => [
				'title_add'  => 'Add New API Key',
				'title_edit' => 'Edit API Key',
				'ajax_action_create' => 'create_api_key',
				'ajax_action_update' => 'update_api_key',
				'ajax_action_delete' => 'delete_api_key',

				'fields' => [
					[
						'id'      => 'description',
						'type'    => 'text',
						'label'   => __( 'Description', 'geodirectory' ),
						'extra_attributes' => ['required' => true]
					],
					[
						'id'      => 'user_id',
						'type'    => 'select',
						'label'   => __( 'User', 'geodirectory' ),
						'options' => $this->get_user_options()
					],
					[
						'id'      => 'permissions',
						'type'    => 'select',
						'label'   => __( 'Permissions', 'geodirectory' ),
						'options' => ['read' => 'Read', 'write' => 'Write', 'read_write' => 'Read/Write'],
						'default' => 'read_write'
					]
				]
			],
			'post_create_view' => [
				'title'   => 'API Key Generated Successfully',
				'message' => 'Please copy your Consumer Key and Consumer Secret. You will not be shown the secret key again.',
				'fields'  => [
					[ 'id' => 'consumer_key', 'type' => 'text', 'label' => 'Consumer Key', 'extra_attributes' => ['readonly' => true, 'onclick' => 'this.select();'] ],
					[ 'id' => 'consumer_secret', 'type' => 'text', 'label' => 'Consumer Secret', 'extra_attributes' => ['readonly' => true, 'onclick' => 'this.select();'] ],
				]
			]
		],


//		/**
//		 * Subsection: API Keys
//		 */
//		array(
//			'id'          => 'keys',
//			'name'        => __( 'API Keys', 'geodirectory' ),
//			'description' => __( 'Create and manage API keys to grant access to external applications.', 'geodirectory' ),
//			'fields'      => array(
//				array(
//					'id'      => 'api_keys_manager',
//					'type'    => 'custom', // @todo: This will require a custom field renderer to output the API key management UI.
//					'label'   => __( 'API Keys Manager', 'geodirectory' ),
//					'searchable' => array('api', 'keys', 'rest', 'integration', 'authentication', 'token'),
//				),
//			),
//		),
	)
);

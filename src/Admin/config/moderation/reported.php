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

return [
	'id'   => 'reported_posts',
	'name' => __( 'Reported Posts', 'geodirectory' ),
	'icon' => 'fa-solid fa-flag',
	'type' => 'list_table',

	'table_config' => [
		'singular'        => 'API Key',
		'plural'          => 'API Keys',
		'ajax_action_get' => 'get_api_keys',

		'columns' => [
			'description'   => [ 'label' => __( 'Description', 'geodirectory' ) ],
			'truncated_key' => [ 'label' => __( 'Key Ending In', 'geodirectory' ) ],
//					'user_id'       => [ 'label' => __( 'User ID', 'geodirectory' ) ],
			'user_name'     => [ 'label' => __( 'User', 'geodirectory' ) ], // dynamic column
			'permissions'   => [ 'label' => __( 'Permissions', 'geodirectory' ) ],
			'last_access'   => [ 'label' => __( 'Last Access', 'geodirectory' ) ],
		],
	],

	'modal_config'     => [
		//'title_add'  => 'Add New API Key',
		'title_edit'         => 'Edit API Key',
		'ajax_action_create' => 'create_api_key',
		'ajax_action_update' => 'update_api_key',
		'ajax_action_delete' => 'delete_api_key',

		'fields' => [
			[
				'id'               => 'description',
				'type'             => 'text',
				'label'            => __( 'Description', 'geodirectory' ),
				'extra_attributes' => [ 'required' => true ]
			],
			[
				'id'      => 'user_id',
				'type'    => 'select',
				'label'   => __( 'User', 'geodirectory' ),
				'options' => [],//$this->get_user_options()
			],
			[
				'id'      => 'permissions',
				'type'    => 'select',
				'label'   => __( 'Permissions', 'geodirectory' ),
				'options' => [ 'read' => 'Read', 'write' => 'Write', 'read_write' => 'Read/Write' ],
				'default' => 'read_write'
			]
		]
	],
	'post_create_view' => [
		'title'   => 'API Key Generated Successfully',
		'message' => 'Please copy your Consumer Key and Consumer Secret. You will not be shown the secret key again.',
		'fields'  => [
			[ 'id'               => 'consumer_key',
			  'type'             => 'text',
			  'label'            => 'Consumer Key',
			  'extra_attributes' => [ 'readonly' => true, 'onclick' => 'this.select();' ]
			],
			[ 'id'               => 'consumer_secret',
			  'type'             => 'text',
			  'label'            => 'Consumer Secret',
			  'extra_attributes' => [ 'readonly' => true, 'onclick' => 'this.select();' ]
			],
		]
	]
];

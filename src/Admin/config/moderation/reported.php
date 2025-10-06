<?php
/**
 * Reported Posts Settings for GeoDirectory
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
use AyeCode\GeoDirectory\Common\PostReports;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'id'   => 'reported_posts',
	'name' => __( 'Reported Posts', 'geodirectory' ),
	'icon' => 'fa-solid fa-flag',
	'type' => 'list_table',

	'table_config' => [
		'singular'        => __( 'Report', 'geodirectory' ),
		'plural'          => __( 'Reports', 'geodirectory' ),
		'ajax_action_get' => 'get_reported_posts',
		'ajax_action_bulk' => 'bulk_reported_posts',


		'columns' => [
			'reported_content'    => [ 'label' => __( 'Reported Content', 'geodirectory' ) ],
			'report_details'      => [ 'label' => __( 'Report Details', 'geodirectory' ) ],
			'reported_by'         => [ 'label' => __( 'Reported By', 'geodirectory' ) ],
			'report_date_formatted' => [ 'label' => __( 'Date', 'geodirectory' ) ],
			'status_formatted'              => [ 'label' => __( 'Status', 'geodirectory' ) ],
		],

		/**
		 * NEW: Statuses Configuration
		 * -----------------------------------------------------------------
		 * Defines the property to use for status filtering and the labels
		 * for the status links (like 'All', 'Pending', 'Active', etc.).
		 */
		'statuses' => [
			// This is the property on each item in your data set to filter by.
			// For the "Reported Posts" example, this would likely be 'status'.
			// For our API keys, we'll use 'permissions'.
			'status_key' => 'status',

			// An array mapping the key's values to display-friendly labels.
			// The framework will automatically add an 'All' view.
			'labels' => PostReports::get_statuses(),
//			'counts' => PostReports::get_status_counts(),

			// Optional: Specify which status should be active by default.
//			'default_status' => 'all',
			'default_status' => 'pending',
		],
		/**
		 * NEW: Filters Configuration
		 * -----------------------------------------------------------------
		 * Defines an array of dropdown filters to display above the table.
		 */
		'filters' => [
			[
				// The data key on your items to filter against.
				'id' => 'reason',

				// The text that appears as the default option in the dropdown.
				// This is similar to "All reasons" in your screenshot.
				'placeholder' => __('All Reasons','geodirectory'),

				// An array of value => label pairs for the dropdown options.
				'options' =>  PostReports::get_reasons(),
			],
			// You could add another filter here, for example:
			// [
			//     'id' => 'user_id',
			//     'placeholder' => 'All Users',
			//     'options_callback' => [$this, 'get_user_options'], // For dynamic options
			// ],
		],
		'bulk_actions' => PostReports::get_bulk_actions(),


	],

	'modal_config'     => [
		'title_edit'         => __( 'Edit Report', 'geodirectory' ),
		'ajax_action_update' => 'update_reported_post',
		'ajax_action_delete' => 'delete_reported_post',

		'fields' => [
			[
				'id'      => 'post_id',
				'type'    => 'text',
				'label'   => __( 'Post ID', 'geodirectory' ),
				'extra_attributes'  =>  [
						'disabled' => 'disabled'
					]
			],
			[
				'id'      => 'reason',
				'type'    => 'select',
				'label'   => __( 'Reason', 'geodirectory' ),
				'options' => PostReports::get_reasons()
			],
			[
				'id'      => 'status',
				'type'    => 'select',
				'label'   => __( 'Status', 'geodirectory' ),
				'options' => PostReports::get_statuses(),
			],
			[
				'id'      => 'message',
				'type'    => 'textarea',
				'label'   => __( 'Message', 'geodirectory' ),
			]
		]
	],
	// just here for testing the add button
//	'post_create_view' => [
//		'title'   => 'API Key Generated Successfully',
//		'message' => 'Please copy your Consumer Key and Consumer Secret. You will not be shown the secret key again.',
//		'fields'  => [
//			[ 'id' => 'consumer_key', 'type' => 'text', 'label' => 'Consumer Key', 'extra_attributes' => ['readonly' => true, 'onclick' => 'this.select();'] ],
//			[ 'id' => 'consumer_secret', 'type' => 'text', 'label' => 'Consumer Secret', 'extra_attributes' => ['readonly' => true, 'onclick' => 'this.select();'] ],
//		]
//	]
];

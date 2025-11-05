<?php
/**
 * V3 GeoDirectory Addons
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


return [
	'id'    => 'recommended',
	'name'  => 'Recommended',
	'icon'  => 'fa-solid fa-thumbs-up',
	'type'  => 'extension_list_page',
	'source' => 'static', // Flag this section as having a static list
	'api_config' => [ 'item_type' => 'plugin' ],
	'static_items' => [
		[
			'info'   => [
				'slug'            => 'ayecode-connect',
				'source'          => 'wp.org',
				'link'            => 'https://wordpress.org/plugins/ayecode-connect/',
				'title'           => 'AyeCode Connect',
				'excerpt'         => 'AyeCode Connect is a service plugin, allowing us to provide extra services to your site such as live documentation search and submission of support tickets.',
				'thumbnail'       => 'https://ps.w.org/ayecode-connect/assets/icon-256x256.png',
				'price'           => 0,
				'is_new'          => false,
				'is_subscription' => false,
			],
		],
		[
			'info'   => [
				'slug'            => 'userswp',
				'source'          => 'wp.org',
				'link'            => 'https://wordpress.org/plugins/userswp/',
				'title'           => 'UsersWP',
				'excerpt'         => 'Front-end login form, User Registration, User Profile & Members Directory plugin for WP.',
				'thumbnail'       => 'https://ps.w.org/userswp/assets/icon-256x256.png',
				'price'           => 0,
				'is_new'          => false,
				'is_subscription' => false,
			],
		],
		[
			'info'   => [
				'slug'            => 'invoicing',
				'source'          => 'wp.org',
				'link'            => 'https://wordpress.org/plugins/invoicing/',
				'title'           => 'GetPaid',
				'excerpt'         => 'Payment forms, Buy now buttons, and Invoicing System | GetPaid',
				'thumbnail'       => 'https://ps.w.org/invoicing/assets/icon-256x256.png',
				'price'           => 0,
				'is_new'          => false,
				'is_subscription' => false,
			],
		],
		[
			'info'   => [
				'slug'            => 'wp-jquery-update-test',
				'source'          => 'wp.org',
				'link'            => 'https://wordpress.org/plugins/wp-jquery-update-test/',
				'title'           => 'Test jQuery Updates',
				'excerpt'         => 'Payment forms, Buy now buttons, and Invoicing System | GetPaid',
				'thumbnail'       => 'https://s.w.org/plugins/geopattern-icon/wp-jquery-update-test.svg',
				'price'           => 0,
				'is_new'          => false,
				'is_subscription' => false,
			],
		],

	]
];

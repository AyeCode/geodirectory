<?php
/**
 * V3 GeoDirectory Dashboard
 *
 * @package     GeoDirectory
 * @since       3.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return [
	'id'      => 'dashboard',
	'name'    => 'Dashboard',
	'type'    => 'dashboard',
	'icon'    => 'fas fa-tachometer-alt',
	'widgets' => [
		[
			'id'      => 'welcome_widget',
			'type'    => 'custom_html',
			'width'   => 'full',
			'title'   => __( 'Welcome!', 'geodirectory' ),
			'content' => '<p>' . __( 'These widgets are powered by the core framework. This class only needs to declare them in the configuration.', 'geodirectory' ) . '</p>',
		],
		[
			'id'          => 'stats_widget',
			'type'        => 'stats',
			'width'       => 'half',
			'title'       => __( 'Site Statistics', 'geodirectory' ),
			'ajax_action' => 'get_dashboard_stats',
			'params'      => [ 'show' => [ 'users', 'posts' ] ],
		],
		[
			'id'          => 'system_status_widget',
			'type'        => 'system_status',
			'width'       => 'half',
			'title'       => __( 'System Status', 'geodirectory' ),
			'ajax_action' => 'get_system_status',
			'params'      => [ 'php_version' => '7.4' ],
		],
		[
			'id'          => 'news_feed_widget',
			'type'        => 'rss_feed',
			'width'       => 'half',
			'title'       => __( 'Latest News from GeoDirectory', 'geodirectory' ),
			'ajax_action' => 'get_plugin_news',
			'feed_url'    => 'https://wpgeodirectory.com/feed/',
		],
		[
			'id'    => 'quick_links_widget',
			'type'  => 'quick_links',
			'width' => 'half',
			'title' => __( 'Quick Links', 'geodirectory' ),
			'links' => [
				[
					'label'   => __( 'Go to General Settings', 'geodirectory' ),
					'icon'    => 'fa-solid fa-sliders',
					'section' => 'general',
				],
				[
					'label'   => __( 'Manage API Keys', 'geodirectory' ),
					'icon'    => 'fa-solid fa-key',
					'section' => 'api_keys',
				],
				[
					'label'    => __( 'Read the Documentation', 'geodirectory' ),
					'icon'     => 'fa-solid fa-book',
					'url'      => 'https://wpgeodirectory.com/documentation/',
					'external' => true,
				],
			],
		],
		[
			'id'      => 'promo_widget',
			'type'    => 'custom_html',
			'width'   => 'full',
			'title'   => __( 'Go Pro!', 'geodirectory' ),
			'content' => '
                                <div class="text-center p-3">
                                    <h5 class="h6">Unlock More Powerful Features</h5>
                                    <p class="text-muted small">Upgrade to our Pro version to get access to advanced features, premium support, and more!</p>
                                    <a href="#" class="btn btn-sm btn-success">Learn More About Pro</a>
                                </div>
                            ',
		],
	],
];

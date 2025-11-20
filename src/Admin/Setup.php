<?php
/**
 * GeoDirectory Admin Setup
 *
 * @package GeoDirectory\Admin
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin;

use AyeCode\GeoDirectory\Admin\Features\NavMenuMetaBox;
use AyeCode\GeoDirectory\Admin\Features\PendingBubbles;
use AyeCode\GeoDirectory\Admin\Features\PostListColumns;
use AyeCode\GeoDirectory\Admin\Features\PostMetaBoxCleanup;
use AyeCode\GeoDirectory\Admin\Features\PostMetaBoxes;
use AyeCode\GeoDirectory\Admin\Features\PostStatusScripts;

/**
 * Handles the setup of the admin area, including menus, features, and scripts.
 *
 * @since 3.0.0
 */
final class Setup {
	/**
	 * Registers the necessary WordPress hooks for the admin area.
	 *
	 * This is the entry point. It sets up the core hooks and initializes
	 * our standalone admin features.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Core admin hooks.
		add_action( 'admin_menu', [ $this, 'add_menus' ], 9 );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

		// Initialize standalone features and tell them to register their own hooks.
		$pending_bubbles = new PendingBubbles();
		$pending_bubbles->register_hooks();

		$nav_menu_meta_box = new NavMenuMetaBox();
		$nav_menu_meta_box->register_hooks();

		// Post list customization features.
		$post_list_columns = new PostListColumns();
		$post_list_columns->register_hooks();

		// Post edit screen features.
		$post_meta_boxes = new PostMetaBoxes();
		$post_meta_boxes->register_hooks();

		$post_meta_box_cleanup = new PostMetaBoxCleanup();
		$post_meta_box_cleanup->register_hooks();

		$post_status_scripts = new PostStatusScripts();
		$post_status_scripts->register_hooks();
	}

	/**
	 * Adds the GeoDirectory admin menu and sub-menus.
	 *
	 * @return void
	 */
	public function add_menus(): void {
		// @todo Create the page classes this method depends on.
		//$dashboard_page = new DashboardPage();

		// Note: Settings and Tools page added in Loader.php

		// Add the main menu page.
		add_menu_page(
			__( 'Geodirectory Dashboard', 'geodirectory' ),
			__( 'GeoDirectory', 'geodirectory' ),
			'manage_options',
			'geodirectory',
//			[ $dashboard_page, 'render' ],
			'',
			'dashicons-admin-site', // Replaced admin-site with a more relevant icon.
			'55.1984'
		);

		// Add core submenus.
//		add_submenu_page( 'geodirectory', __( 'Dashboard', 'geodirectory' ), __( 'Dashboard', 'geodirectory' ), 'manage_options', 'geodirectory', [ $dashboard_page, 'render' ] );

//		if ( apply_filters( 'geodirectory_show_addons_page', true ) ) {
//			add_submenu_page( 'geodirectory', __( 'Extensions', 'geodirectory' ), __( 'Extensions', 'geodirectory' ), 'manage_options', 'gd-addons', [ $addons_page, 'render' ] );
//		}

		// Add "Settings" submenu under each GeoDirectory CPT.
	//	$this->add_cpt_settings_menus( $settings_page );
	}

	/**
	 * Adds a "Settings" link under each registered GeoDirectory CPT.
	 *
	 * @param SettingsPage $settings_page The instance of our settings page controller.
	 * @return void
	 */
	private function add_cpt_settings_menus( SettingsPage $settings_page ): void {
		// @todo This should be refactored to get post types from a core service, not a global function.
		$post_types = function_exists( 'geodir_get_option' ) ? geodir_get_option( 'post_types' ) : [];
		if ( empty( $post_types ) || ! is_array( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $name => $cpt ) {
			add_submenu_page(
				'edit.php?post_type=' . $name,
				__( 'Settings', 'geodirectory' ),
				__( 'Settings', 'geodirectory' ),
				'manage_options',
				$name . '-settings',
				[ $settings_page, 'render' ]
			);
		}
	}

	/**
	 * Enqueues scripts and styles for the admin area.
	 *
	 * @return void
	 */
	public function enqueue_assets(): void {
		// @todo Add logic to only load assets on our plugin's pages.
	}
}

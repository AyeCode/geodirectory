<?php
/**
 * GeoDirectory CPT Settings Manager
 *
 * This class finds registered CPTs and dynamically creates settings pages for them.
 *
 * @package     GeoDirectory
 * @subpackage  Admin
 * @since       2.2.0
 */

// Define the namespace for the class.
namespace AyeCode\GeoDirectory\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Import the class we need to instantiate.
use AyeCode\GeoDirectory\Admin\Pages\Dynamic_CPT_Settings;

/**
 * CPT_Settings_Manager Class
 */
final class CPT_Settings_Manager {

	/**
	 * Hooks the manager into WordPress.
	 */
	public function init() {
		// We use admin_menu to ensure all post types are registered.
		// A priority of 99 ensures it runs after most CPTs are added.
		add_action( 'admin_menu', [ $this, 'create_settings_pages' ],8 );
	}

	/**
	 * Finds CPTs and instantiates a settings page for each one.
	 */
	public function create_settings_pages() {
		// Get all public CPTs created by GeoDirectory.
		// You might need to adjust this logic to get the specific CPTs you want.
		$post_types = \geodir_get_posttypes( 'object' );

		// This filter is important! It lets you (or other addons) exclude certain CPTs.
		// For example: unset( $post_types['gd_event'] );
		$post_types = apply_filters( 'geodir_cpt_with_settings_pages', $post_types );

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $cpt_slug => $cpt ) {
			new Dynamic_CPT_Settings( $cpt_slug, $cpt );
		}
	}
}

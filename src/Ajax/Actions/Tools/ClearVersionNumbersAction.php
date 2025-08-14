<?php
/**
 * Class ClearVersionNumbersAction
 * Represents an action to clear version numbers within the GeoDirectory Ajax functionality.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClearVersionNumbersAction
 * Handles the process of clearing version numbers and resetting associated data within the GeoDirectory plugin.
 */
class ClearVersionNumbersAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {
		delete_site_option( 'wp_country_database_version' ); // Delete countries database version.
		delete_option( 'geodirectory_version' );
		wp_cache_delete( 'geodir_noindex_page_ids' );
		do_action( 'geodir_clear_version_numbers');
		wp_send_json_success(array(
			'message'  => __( 'Version numbers cleared. Install/upgrade functions will run on next page load.', 'geodirectory' ),
			'progress' => 100
		));
	}
}

<?php
/**
 * Class ClearVersionNumbersAction
 * Represents an action to clear version numbers within the GeoDirectory Ajax functionality.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace GeoDirectory\Ajax\Actions;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClearVersionNumbersAction
 * Handles the process of clearing version numbers and resetting associated data within the GeoDirectory plugin.
 */
class ClearPagingCacheAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {

		delete_option('gd_found_posts_cache');

		wp_send_json_success(array(
			'message'  => __( 'Cache successfully cleared.', 'geodirectory' ),
			'progress' => 100
		));
	}
}

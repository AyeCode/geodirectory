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
class GenerateKeywordsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {
		$generated = (int) geodir_generate_title_keywords();

		if ( $generated > 0 ) {
			$message = wp_sprintf( _n( '%d keyword generated.', '%d keywords generated.', $generated, 'geodirectory' ), $generated );
		} else {
			$message = __( 'No keyword generated.', 'geodirectory' );
		}
		wp_send_json_success(array(
			'message'  => $message,
			'progress' => 100
		));
	}
}

<?php
/**
 * Class ClearVersionNumbersAction
 * Represents an action to clear version numbers within the GeoDirectory Ajax functionality.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Panes;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClearVersionNumbersAction
 * Handles the process of clearing version numbers and resetting associated data within the GeoDirectory plugin.
 */
class RenderStatusReportAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {

		ob_start();
		include_once( dirname( __FILE__ ) . '/../../../Admin/views/status-report.php' );;

		wp_send_json_success(array('html' => ob_get_clean() ));

	}
}

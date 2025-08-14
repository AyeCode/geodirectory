<?php
/**
 * Class RegenerateThumbnailsAction
 *
 * Handles the regeneration of thumbnails via AJAX requests and returns progress updates
 * to the client side as the process continues.
 *
 * This class encapsulates a static method used for managing the steps involved
 * in regenerating thumbnails for media attachments in bulk.
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Class RegenerateThumbnailsAction
 *
 * Handles the regeneration of media thumbnails through an AJAX request.
 * Provides progress feedback during the batch processing of media items.
 *
 * The process is carried out in steps, with configuration options for the
 * current step and the number of items to process per step. Success or error
 * messages are returned as JSON responses.
 */
class RegenerateThumbnailsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void
	 */
	public static function dispatch() {

		$current_step = ! empty( $_POST['step'] ) ? absint( $_POST['step'] ) : 1;
		$per_page     = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
		$total        = \GeoDir_Media::count_image_attachments();
		$next_step    = null;
		$data         = \GeoDir_Media::generate_bulk_attachment_metadata( $current_step, $per_page );


		if ( is_wp_error( $data ) ) {
			wp_send_json_error( array( 'error' => $data->get_error_message() ) );
		}

		$progress = round( ( $current_step * $per_page / $total ) * 100 );

		if ( $progress >= 100 ) {
			$progress = 100;
			// The job is done, so next_step remains null to stop the chain.
			$message = __( 'Thumbnails regenerated successfully!' );
		} else {
			// The job is not done, so set the next_step to the new progress value.
			$next_step = $current_step + 1;
			$message   = sprintf( __( '%d%% complete...' ), $progress );
		}

		wp_send_json_success( array(
			'message'   => $message,
			'progress'  => $progress,
			'next_step' => $next_step
		) );
	}

}

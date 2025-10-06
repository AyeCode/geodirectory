<?php
/**
 * AJAX action to delete a post report.
 *
 * @package GeoDirectory\Ajax\Actions\Moderation
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\Moderation;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ModerationSettingsHandler;

class DeleteReportedAction {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {
		$handler = new ModerationSettingsHandler();
		$data = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : array();
		$id   = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

		if ( ! $id ) {
			wp_send_json_error( [ 'message' => 'Missing ID.' ] );
		}

		$result = $handler->delete_report( $id );

		if ( ! $result ) {
			wp_send_json_error( [ 'message' => 'Error deleting report.' ] );
		}

		wp_send_json_success( [ 'message' => 'Report deleted successfully.' ] );
	}
}

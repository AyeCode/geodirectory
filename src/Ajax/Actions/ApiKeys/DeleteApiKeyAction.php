<?php
/**
 * AJAX action to delete an API key.
 *
 * @package GeoDirectory\Ajax\Actions\ApiKeys
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\ApiKeys;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ApiKeySettingsHandler;

class DeleteApiKeyAction {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {

		$data = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : array();
		$id   = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

		$handler = new ApiKeySettingsHandler();
		$deleted = $handler->delete_key( $id );

		if ( ! $deleted ) {
			wp_send_json_error( [ 'message' => __( 'Could not delete the API key.', 'geodirectory' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'API Key deleted successfully.', 'geodirectory' ) ] );
	}
}

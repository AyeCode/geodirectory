<?php
/**
 * AJAX action to update an existing API key.
 *
 * @package GeoDirectory\Ajax\Actions\ApiKeys
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\ApiKeys;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ApiKeySettingsHandler;

class UpdateApiKeyAction {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {
		$data = ! empty( $_POST['data'] ) ? json_decode( wp_unslash( $_POST['data'] ), true ) : array();
		$id   = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

		$handler = new ApiKeySettingsHandler();
		$updated = $handler->update_key(  $id, $data );

		if ( ! $updated ) {
			wp_send_json_error( [ 'message' => __( 'Could not update the API key.', 'geodirectory' ) ] );
		}

		wp_send_json_success( [ 'message' => __( 'API Key updated successfully.', 'geodirectory' ) ] );
	}
}

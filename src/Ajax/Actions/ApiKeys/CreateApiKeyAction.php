<?php
/**
 * AJAX action to create a new API key.
 *
 * @package GeoDirectory\Ajax\Actions\ApiKeys
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\ApiKeys;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ApiKeySettingsHandler;

class CreateApiKeyAction {
	/**
	 * Dispatches the AJAX request.
	 */
	public function dispatch(): void {
		$data = ! empty( $_POST['data'] ) ? \json_decode( wp_unslash( $_POST['data'] ), true ) : array();

		$handler = new ApiKeySettingsHandler();
		$new_key = $handler->create_key( $data );

		wp_send_json_success( $new_key );
	}
}

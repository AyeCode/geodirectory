<?php
/**
 * AJAX action to get all API keys.
 *
 * @package GeoDirectory\Ajax\Actions\ApiKeys
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\ApiKeys;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ApiKeySettingsHandler;

class GetApiKeysAction {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {
		$handler = new ApiKeySettingsHandler();
		wp_send_json_success( $handler->get_keys() );
	}
}

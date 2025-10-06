<?php
/**
 * AJAX action to get all post reports.
 *
 * @package GeoDirectory\Ajax\Actions\Moderation
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Ajax\Actions\Moderation;

use AyeCode\GeoDirectory\Admin\Settings\Handlers\ModerationSettingsHandler;

class GetReportedAction {
	/**
	 * Handles the AJAX request.
	 */
	public function dispatch(): void {
//		print_r( $_POST );

		$status = isset($_POST['status']) ? sanitize_key($_POST['status']) : 'all';
		$filters_unsanitized = isset($_POST['filters']) ? json_decode(stripslashes($_POST['filters']), true) : [];

		// Let's not trust the filters values
		$filters = [];
		if(!empty($filters_unsanitized['reason'])) {
			$filters['reason'] = sanitize_key($filters_unsanitized['reason']);
		}
//		print_r($filters);exit;
//		echo '###'.$status ;
		$handler = new ModerationSettingsHandler();
		wp_send_json_success( $handler->get_reports( $status, $filters ) );
	}
}

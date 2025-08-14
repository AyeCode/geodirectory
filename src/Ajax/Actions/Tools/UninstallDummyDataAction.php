<?php
/**
 * This file contains the Action for uninstalling dummy data.
 *
 * @author   AyeCode
 * @category Ajax
 * @package  AyeCode\GeoDirectory\Ajax\Actions\Tools
 */
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

use AyeCode\GeoDirectory\DummyData\DummyDataService;

/**
 * UninstallDummyDataAction Class
 *
 * Handles the single-step AJAX request to remove all dummy data for a CPT.
 */
class UninstallDummyDataAction
{
	/**
	 * Handles the dispatch process for the uninstallation action.
	 */
	public function dispatch() {
		$input_data = !empty($_POST['input_data']) ? json_decode(wp_unslash($_POST['input_data']), true) : [];

		// --- Start of fix: Parse dynamic input_data ---
		$post_type = null;

		foreach ($input_data as $key => $value) {
			if (strpos($key, 'post_type_') === 0) {
				$post_type = sanitize_key($value);
				break;
			}
		}

		if (!$post_type) {
			wp_send_json_error(['message' => __('Could not determine post type from input data.', 'geodirectory')]);
		}
		// --- End of fix ---

		$service = new DummyDataService();
		$deleted_count = $service->uninstall($post_type);

		wp_send_json_success([
			'message'   => sprintf(__('%d dummy items removed successfully.', 'geodirectory'), $deleted_count),
			'progress'  => 100,
			'next_step' => null
		]);
	}
}

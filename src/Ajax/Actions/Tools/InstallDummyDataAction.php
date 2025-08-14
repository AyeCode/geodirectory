<?php
/**
 * This file contains the Action for installing dummy data.
 *
 * @author   AyeCode
 * @category Ajax
 * @package  AyeCode\GeoDirectory\Ajax\Actions\Tools
 */
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

use AyeCode\GeoDirectory\DummyData\DummyDataService;

/**
 * InstallDummyDataAction Class
 *
 * Handles the chained AJAX request for installing a dummy data set.
 */
class InstallDummyDataAction
{
	/**
	 * Handles the dispatch process for the installation action.
	 */
	public function dispatch() {
		$step = isset($_POST['step']) ? absint($_POST['step']) : 0;
//		$total = !empty($_POST['total']) ? absint($_POST['total']) : 0;
		$input_data = !empty($_POST['input_data']) ? json_decode(wp_unslash($_POST['input_data']), true) : [];

		// --- Start of fix: Parse dynamic input_data ---
		$post_type = null;

		// Find the post type slug by looking for a key that starts with 'post_type_'.
		foreach ($input_data as $key => $value) {
			if (strpos($key, 'post_type_') === 0) {
				$post_type = sanitize_key($value);
				break;
			}
		}

		if (!$post_type) {
			wp_send_json_error(['message' => __('Could not determine post type from input data.', 'geodirectory')]);
		}

		// Now use the discovered post type to get the other values.
		$data_type = $input_data['data_type_' . $post_type] ?? '';
		$total = $number_of_posts = !empty($input_data['number_' . $post_type]) ? absint($input_data['number_' . $post_type]) : 30;
		$update_templates = !empty($input_data['update_templates_' . $post_type]);
		$location_data = $input_data['location_data'] ?? []; // For random addresses
		// --- End of fix ---

		$service = new DummyDataService();

		// On Step 0, do the setup.
		if ($step === 0) {
			$result = $service->install_setup($post_type, $data_type);
			if (is_wp_error($result)) {
				wp_send_json_error(['message' => $result->get_error_message()]);
			}

			// If the user specified a number, use it. Otherwise, use the total from the data file.
			$total_posts_to_create = $number_of_posts > 0 ? $number_of_posts : $result['total_posts'];

			wp_send_json_success([
				'message'   => __('Category setup complete. Starting post creation...', 'geodirectory'),
				'progress'  => 3, // fake some progress
				'next_step' => 1,
				'total'     => $total_posts_to_create,
			]);
		}

		// On subsequent steps, create one post.
		$post_index = $step - 1; // Convert 1-based step to 0-based array index.
//echo $total.'###'.$post_index;exit;
		$result = $service->install_post($post_type, $data_type, $post_index, $location_data);
		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()]);
		}

		$progress = $total > 0 ? round(($step / $total) * 100) : 100;
		$next_step = null;

		if ($step >= $total) {
			$progress = 100;
			$message = __('Dummy data installed successfully!', 'geodirectory');
		} else {
			$next_step = $step + 1;
			$message = sprintf(__('%d%% complete...', 'geodirectory'), $progress);
		}

		wp_send_json_success([
			'message'   => $message,
			'progress'  => $progress,
			'next_step' => $next_step,
			'total'     => $total,
		]);
	}
}

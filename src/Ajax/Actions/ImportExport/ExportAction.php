<?php
/**
 * This file contains the AJAX Action for the entire export process.
 *
 * @author   AyeCode
 * @category Ajax
 * @package  GeoDirectory\Ajax\Actions\ImportExport
 */

namespace AyeCode\GeoDirectory\Ajax\Actions\ImportExport;

use AyeCode\GeoDirectory\ImportExport\Exporters\CategoryExporter;
use AyeCode\GeoDirectory\ImportExport\Exporters\PostExporter;
use AyeCode\GeoDirectory\ImportExport\Exporters\ReviewExporter;
use AyeCode\GeoDirectory\ImportExport\Exporters\SettingsExporter;
use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;
use AyeCode\GeoDirectory\ImportExport\Utils\Environment;
use AyeCode\GeoDirectory\ImportExport\Utils\Filesystem;

/**
 * ExportAction Class
 *
 * Manages all export workflows from a single entry point.
 * It can handle chunked CSV exports and single-file JSON exports.
 */
class ExportAction {
	/**
	 * Handles the dispatch process for the export action.
	 */
	public function dispatch() {
		Environment::setExecutionLimits();
		global $wp_filesystem;

		$type = !empty($_POST['tool_action']) ? sanitize_key($_POST['tool_action']) : '';
		$input_data = !empty($_POST['input_data']) ? json_decode(wp_unslash($_POST['input_data']), true) : [];
		$filters = [
			'post_type'  => !empty($input_data['_pt']) ? sanitize_key($input_data['_pt']) : null,
			'start_date' => !empty($input_data['start_date']) ? sanitize_text_field($input_data['start_date']) : null,
			'end_date'   => !empty($input_data['end_date']) ? sanitize_text_field($input_data['end_date']) : null,
		];

		$exporter = $this->getExporter($type, $filters);
		if (!$exporter) {
			wp_send_json_error(['message' => __('Invalid export type specified.', 'geodirectory')]);
		}

		if ($exporter instanceof SettingsExporter) {
			$settings_data = $exporter->getSettingsData();
			Filesystem::ensureCacheDirectoryExists();
			$json_data = wp_json_encode($settings_data, JSON_PRETTY_PRINT);

			// --- Start of fix: New secure filename for settings ---
			$random_hash = strtolower(wp_generate_password(12, false));
			$filename = sprintf('%s_%s_%s.json', $type, date('Ymd-His'), $random_hash);
			// --- End of fix ---

			$filepath = Filesystem::getCacheDirectoryPath(false) . '/' . $filename;

			if ($wp_filesystem->put_contents($filepath, $json_data, FS_CHMOD_FILE)) {
				$download_url = Filesystem::getCacheDirectoryPath(true) . '/' . $filename;
				wp_send_json_success([
					'message'   => __('Settings file created. Download your file below.', 'geodirectory'),
					'progress'  => 100,
					'next_step' => null,
					'file'      => ['url' => $download_url, 'name' => $filename, 'size' => size_format(filesize($filepath))]
				]);
			} else {
				wp_send_json_error(['message' => __('Could not write settings to file. Check permissions.', 'geodirectory')]);
			}
			return;
		}

		$per_page = !empty($input_data['_n']) ? absint($input_data['_n']) : 500;
		$step = isset($_POST['step']) ? absint($_POST['step']) : 0;
		$total = !empty($_POST['total']) ? absint($_POST['total']) : 0;
		$nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';

		if (empty($nonce)) {
			wp_send_json_error(['message' => __('Missing security nonce.', 'geodirectory')]);
		}

		if ($step === 0) {
			$total = $exporter->getTotalCount();
			if ($total === 0) {
				wp_send_json_success(['message' => __('There are no items to export.', 'geodirectory'), 'progress' => 100, 'next_step' => null]);
			}
			Filesystem::ensureCacheDirectoryExists();
			$temp_filepath = Filesystem::getCacheDirectoryPath(false) . '/' . $type . '_' . $nonce . '.tmp';
			$csv_handler = new CsvHandler($wp_filesystem);
			$columns = $exporter->getColumns();
			if (!empty($columns)) $csv_handler->saveRows($temp_filepath, [$columns], true);
		}

		$offset = $step * $per_page;
		$data = $exporter->getData($per_page, $offset);
		if (!empty($data)) {
			$temp_filepath = Filesystem::getCacheDirectoryPath(false) . '/' . $type . '_' . $nonce . '.tmp';
			$csv_handler = new CsvHandler($wp_filesystem);
			$csv_handler->saveRows($temp_filepath, $data, false);
		}

		$processed_count = $offset + count($data);
		$progress = $total > 0 ? round(($processed_count / $total) * 100) : 100;
		$next_step = null;
		$response_data = [];

		if ($progress >= 100) {
			$progress = 100;
			$message = __('Export complete! Download your files below.', 'geodirectory');
			$response_data['file'] = $this->finalizeExport($type, $nonce, $filters);
		} else {
			$next_step = $step + 1;
			$message = sprintf(__('%d%% complete...', 'geodirectory'), $progress);
		}

		$response_data = array_merge($response_data, [
			'message'   => $message,
			'progress'  => $progress,
			'next_step' => $next_step,
			'total'     => $total,
		]);

		wp_send_json_success($response_data);
	}

	/**
	 * Finalizes the export by moving the temp file and returning its details.
	 */
	private function finalizeExport(string $type, string $nonce, array $filters): ?array {
		global $wp_filesystem;
		$temp_filepath = Filesystem::getCacheDirectoryPath(false) . '/' . $type . '_' . $nonce . '.tmp';
		if (!$wp_filesystem->exists($temp_filepath)) return null;

		$cache_dir_path = Filesystem::getCacheDirectoryPath(false);
		$cache_dir_url = Filesystem::getCacheDirectoryPath(true);

		// --- Start of fix: New secure and descriptive filename for CSVs ---
		$post_type_slug = !empty($filters['post_type']) ? '_' . sanitize_key($filters['post_type']) : '';
		$random_hash = strtolower(wp_generate_password(12, false));

		$final_filename = sprintf(
			'%s%s_%s_%s.csv',
			$type,
			$post_type_slug,
			date('Ymd-His'),
			$random_hash
		);
		// --- End of fix ---

		$final_filepath = $cache_dir_path . '/' . $final_filename;
		if ($wp_filesystem->move($temp_filepath, $final_filepath, true)) {
			return [
				'url' => $cache_dir_url . '/' . $final_filename,
				'name' => $final_filename,
				'size' => size_format(filesize($final_filepath))
			];
		}
		return null;
	}

	/**
	 * Gets an instance of the required Exporter class based on the 'tool_action'.
	 */
	private function getExporter(string $type, array $filters = []) {
		$post_type = !empty($filters['post_type']) ? sanitize_key($filters['post_type']) : null;
		switch ($type) {
			case 'export_posts': return $post_type ? new PostExporter($post_type, $filters) : null;
			case 'export_cats': return $post_type ? new CategoryExporter($post_type) : null;
			case 'export_reviews': return new ReviewExporter($filters);
			case 'export_settings': return new SettingsExporter();
			default: return null;
		}
	}
}

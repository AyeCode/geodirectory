<?php
/**
 * This file contains the AJAX Action for processing an import chunk.
 *
 * @author   AyeCode
 * @category Ajax
 * @package  GeoDirectory\Ajax\Actions\ImportExport
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\Ajax\Actions\ImportExport;

use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;
use AyeCode\GeoDirectory\ImportExport\Importers\CategoryImporter;
use AyeCode\GeoDirectory\ImportExport\Importers\PostImporter;
use AyeCode\GeoDirectory\ImportExport\Importers\ReviewImporter;
use AyeCode\GeoDirectory\ImportExport\Utils\Environment;

/**
 * ProcessImportAction Class
 *
 * This class acts as the controller for the AJAX requests that process the
 * import file in chunks. It receives an offset and limit, calls the
 * appropriate Importer service to process that part of the file, and
 * returns a summary of the operation (created, updated, skipped, etc.).
 */
class ProcessImportAction
{
	/**
	 * Handles the AJAX request.
	 *
	 * @return void
	 */
	public function handleRequest()
	{
		// Basic security checks.
		if (!check_ajax_referer('gd-imex-nonce', 'nonce', false) || !current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Security check failed.', 'geodirectory')], 403);
			return;
		}

		// Set higher execution limits for this potentially long-running task.
		Environment::setExecutionLimits();

		// Sanitize all incoming parameters.
		$filepath = isset($_POST['_file']) ? sanitize_text_field($_POST['_file']) : '';
		$type = isset($_POST['_type']) ? sanitize_key($_POST['_type']) : '';
		$offset = isset($_POST['offset']) ? absint($_POST['offset']) : 0;
		$limit = isset($_POST['limit']) ? absint($_POST['limit']) : 50; // Default to 50 rows per chunk.
		$options = isset($_POST['options']) && is_array($_POST['options']) ? $_POST['options'] : [];
		$options['update_existing'] = isset($options['update_existing']) ? (bool)$options['update_existing'] : false;

		if (empty($filepath) || empty($type)) {
			wp_send_json_error(['message' => __('Missing file path or import type.', 'geodirectory')], 400);
			return;
		}

		global $wp_filesystem;
		$csv_handler = new CsvHandler($wp_filesystem);

		// Get the specific importer class based on the type.
		$importer = $this->getImporter($type, $csv_handler);
		$importer->validateFile($filepath); // This sets the internal filepath property of the importer.

		if (!$importer) {
			wp_send_json_error(['message' => __('Invalid import type specified.', 'geodirectory')], 400);
			return;
		}

		// Defer term counting to speed up the import process.
		wp_defer_term_counting(true);

		// Let the importer process the chunk.
		$result = $importer->process($offset, $limit, $options);

		// Turn term counting back on.
		wp_defer_term_counting(false);

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()], 400);
		} else {
			// On success, `process` returns a summary array.
			wp_send_json_success($result);
		}
	}

	/**
	 * Gets an instance of the required Importer class.
	 *
	 * @param string     $type        The type of import (e.g., 'posts').
	 * @param CsvHandler $csv_handler The CSV handler instance.
	 *
	 * @return \AyeCode\GeoDirectory\ImportExport\Contracts\ImporterInterface|null
	 */
	private function getImporter(string $type, CsvHandler $csv_handler)
	{
		switch ($type) {
			case 'posts':
				return new PostImporter($csv_handler);
			case 'categories':
				return new CategoryImporter($csv_handler);
			case 'reviews':
				return new ReviewImporter($csv_handler);
			default:
				return null;
		}
	}
}

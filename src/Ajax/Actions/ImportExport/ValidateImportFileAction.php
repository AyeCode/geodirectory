<?php
/**
 * This file contains the AJAX Action for validating an import file.
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

/**
 * ValidateImportFileAction Class
 *
 * This class acts as the controller for the AJAX request that validates a
 * newly uploaded import file. It determines the import type, uses the
 * appropriate Importer class to validate the file's structure and content,
 * and returns the row count to the front-end to initialize the progress bar.
 */
class ValidateImportFileAction
{
	/**
	 * Handles the AJAX request.
	 *
	 * @return void
	 */
	public function handleRequest()
	{
		// Basic security checks.
		// Assumes a nonce like 'gd-imex-nonce' is passed in the request.
		if (!check_ajax_referer('gd-imex-nonce', 'nonce', false) || !current_user_can('manage_options')) {
			wp_send_json_error(['message' => __('Security check failed.', 'geodirectory')], 403);
			return;
		}

		$filepath = isset($_POST['_file']) ? sanitize_text_field($_POST['_file']) : '';
		$type = isset($_POST['_type']) ? sanitize_key($_POST['_type']) : '';

		if (empty($filepath) || empty($type)) {
			wp_send_json_error(['message' => __('Missing file path or import type.', 'geodirectory')], 400);
			return;
		}

		global $wp_filesystem;
		$csv_handler = new CsvHandler($wp_filesystem);

		// Get the specific importer class based on the type from the request.
		$importer = $this->getImporter($type, $csv_handler);

		if (!$importer) {
			wp_send_json_error(['message' => __('Invalid import type specified.', 'geodirectory')], 400);
			return;
		}

		// Let the importer validate the file.
		$result = $importer->validateFile($filepath);

		if (is_wp_error($result)) {
			wp_send_json_error(['message' => $result->get_error_message()], 400);
		} else {
			// On success, `validateFile` returns an array like ['rows' => 123]
			wp_send_json_success($result);
		}
	}

	/**
	 * Gets an instance of the required Importer class.
	 *
	 * This acts as a simple factory. In a larger system, this could be
	 * replaced with a more advanced service container or manager class
	 * where addons could register their own importers.
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

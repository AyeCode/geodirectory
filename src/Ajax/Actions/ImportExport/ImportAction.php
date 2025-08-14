<?php
/**
 * This file contains the AJAX Action for the entire import process.
 *
 * @author   AyeCode
 * @category Ajax
 * @package  GeoDirectory\Ajax\Actions\ImportExport
 */

namespace AyeCode\GeoDirectory\Ajax\Actions\ImportExport;

use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;
use AyeCode\GeoDirectory\ImportExport\Handlers\JsonHandler;
use AyeCode\GeoDirectory\ImportExport\Importers\CategoryImporter;
use AyeCode\GeoDirectory\ImportExport\Importers\PostImporter;
use AyeCode\GeoDirectory\ImportExport\Importers\ReviewImporter;
use AyeCode\GeoDirectory\ImportExport\Importers\SettingsImporter;
use AyeCode\GeoDirectory\ImportExport\Utils\Environment;
use AyeCode\GeoDirectory\ImportExport\Utils\Filesystem;

/**
 * ImportAction Class
 *
 * Manages the entire import workflow. On step 0, it validates a pre-uploaded
 * file. On subsequent steps, it processes the file in chunks.
 */
class ImportAction {
	/**
	 * Handles the dispatch process for the import action.
	 */
	public function dispatch() {
		Environment::setExecutionLimits();

		$step = isset($_POST['step']) ? absint($_POST['step']) : 0;

		if ($step === 0) {
			$this->validate_pre_uploaded_file();
		} else {
			$this->process_file_chunk();
		}
	}

	/**
	 * Validates a file that has already been uploaded by the framework.
	 */
	private function validate_pre_uploaded_file() {
		$input_data = !empty($_POST['input_data']) ? json_decode(wp_unslash($_POST['input_data']), true) : [];
		$filename = !empty($input_data['import_filename']) ? sanitize_file_name($input_data['import_filename']) : '';

		if (empty($filename)) {
			wp_send_json_error(['message' => __('No import filename provided by the uploader.', 'geodirectory')]);
		}

		$type = !empty($_POST['tool_action']) ? sanitize_key($_POST['tool_action']) : '';
		// Pass the page slug from the action name.
		$page_slug = str_replace('asf_tool_action_', '', $_POST['action']);
		$importer = $this->getImporter($type, $page_slug);

		if (!$importer) {
			wp_send_json_error(['message' => __('Invalid import type.', 'geodirectory')]);
		}

		$validation_result = $importer->validateFile($filename);

		if (is_wp_error($validation_result)) {
			wp_send_json_error(['message' => $validation_result->get_error_message()]);
		}

		wp_send_json_success([
			'message'   => __('File validated successfully. Starting import...', 'geodirectory'),
			'progress'  => 0,
			'next_step' => 1,
			'total'     => $validation_result['rows'],
			// Pass the original filename back to the JS for the next steps.
			'import_filename'  => $filename,
		]);
	}

	/**
	 * Processes a single chunk of a previously uploaded and validated file.
	 */
	private function process_file_chunk() {
		$input_data = !empty($_POST['input_data']) ? json_decode(wp_unslash($_POST['input_data']), true) : [];
		$filename = !empty($input_data['import_filename']) ? sanitize_file_name($input_data['import_filename']) : '';

		$type = !empty($_POST['tool_action']) ? sanitize_key($_POST['tool_action']) : '';
		$page_slug = str_replace('asf_tool_action_', '', $_POST['action']);
		$step = absint($_POST['step']);
		$total = !empty($_POST['total']) ? absint($_POST['total']) : 0;
		$per_page = !empty($input_data['per_page']) ? absint($input_data['per_page']) : 50;

		if (empty($filename)) {
			wp_send_json_error(['message' => __('Import filename not provided.', 'geodirectory')]);
		}

		$importer = $this->getImporter($type, $page_slug);
		if (!$importer) {
			wp_send_json_error(['message' => __('Invalid import type.', 'geodirectory')]);
		}

		$offset = ($step - 1) * $per_page;
		$options = [
			'update_existing' => !empty($input_data['update_existing']),
		];

		$summary = $importer->process($filename, $offset, $per_page, $options);

		$processed_count = $offset + $per_page;
		$progress = $total > 0 ? round(min($processed_count, $total) / $total * 100) : 100;
		$next_step = null;

		if ($processed_count >= $total) {
			$progress = 100;
			$message = __('Import complete!', 'geodirectory');
			$filepath = AYECODE_SF_IMPORT_TEMP_DIR . $page_slug . '/' . $filename;
			if (file_exists($filepath)) {
				unlink($filepath); // Clean up the temp file.
			}
		} else {
			$next_step = $step + 1;
			// The in-progress message can remain generic or include chunk-specific details.
			$message = sprintf(__('%d%% complete...', 'geodirectory'), $progress);
		}

		wp_send_json_success([
			'message'   => $message,
			'progress'  => $progress,
			'next_step' => $next_step,
			'summary'   => $summary, // <-- The important addition
		]);
	}

	/**
	 * Gets an instance of the required Importer class based on the 'tool_action'.
	 */
	private function getImporter(string $type, string $page_slug) {
		Filesystem::init_filesystem();
		global $wp_filesystem;
		$csv_handler = new CsvHandler($wp_filesystem);
		$json_handler = new JsonHandler($wp_filesystem);

		switch ($type) {
			// NOTE: You'll need to update your other importers to accept the page_slug
			// and rebuild their file paths, just like we did for SettingsImporter.
			case 'import_listings': return new PostImporter($csv_handler, $page_slug);
			case 'import_cats': return new CategoryImporter($csv_handler, $page_slug);
			case 'import_reviews': return new ReviewImporter($csv_handler, $page_slug);
			case 'import_settings': return new SettingsImporter($json_handler, $page_slug);
			default: return null;
		}
	}
}

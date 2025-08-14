<?php
/**
 * This file contains the class for importing plugin Settings.
 *
 * @author   AyeCode
 * @category Importers
 * @package  GeoDirectory\ImportExport\Importers
 */

namespace AyeCode\GeoDirectory\ImportExport\Importers;

use AyeCode\GeoDirectory\ImportExport\Contracts\ImporterInterface;
use AyeCode\GeoDirectory\ImportExport\Handlers\JsonHandler;

/**
 * SettingsImporter Class
 *
 * Handles the logic for importing settings from a JSON file.
 */
class SettingsImporter implements ImporterInterface
{
	/**
	 * @var JsonHandler The JSON handler for reading the import file.
	 */
	private $json_handler;

	/**
	 * @var string The slug of the settings page, used for the temp folder path.
	 */
	private $page_slug;

	/**
	 * SettingsImporter constructor.
	 *
	 * @param JsonHandler $json_handler An instance of the JsonHandler.
	 * @param string      $page_slug    The slug of the admin page (e.g., 'geodir_tools').
	 */
	public function __construct(JsonHandler $json_handler, string $page_slug)
	{
		$this->json_handler = $json_handler;
		$this->page_slug = $page_slug;
	}

	/**
	 * Rebuilds the full, absolute path to the temporary import file.
	 *
	 * @param string $filename The base name of the file.
	 * @return string The full server path to the file.
	 */
	private function rebuild_filepath(string $filename): string {
		// Build the path using the framework constant and page slug.
		return AYECODE_SF_IMPORT_TEMP_DIR . $this->page_slug . '/' . $filename;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return 'settings';
	}

	/**
	 * {@inheritDoc}
	 */
	public function validateFile(string $filename)
	{
		$filepath = $this->rebuild_filepath($filename);
		$result = $this->json_handler->decodeFile($filepath);

		if (is_wp_error($result)) {
			return $result;
		}

		// A settings file is treated as a single "row" for the import process.
		return ['rows' => 1];
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(int $offset, int $limit, array $options = []): array
	{
		$summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'invalid' => 0, 'errors' => []];

		// The filename is now passed via the options array from the ImportAction.
		$filename = $options['filename'] ?? '';
		if (empty($filename)) {
			$summary['invalid']++;
			$summary['errors'][] = __('Import filename not provided.', 'geodirectory');
			return $summary;
		}
		$filepath = $this->rebuild_filepath($filename);

		$settings = $this->json_handler->decodeFile($filepath);
		if (is_wp_error($settings)) {
			$summary['invalid']++;
			$summary['errors'][] = $settings->get_error_message();
			return $summary;
		}

		if (empty($settings)) {
			$summary['invalid']++;
			$summary['errors'][] = __('The settings file does not contain any data.', 'geodirectory');
			return $summary;
		}

		$updated_count = 0;
		foreach ($settings as $key => $setting) {
			geodir_update_option($key, $setting);
			$updated_count++;
		}

		// We use 'created' to represent the number of settings updated.
		$summary['created'] = $updated_count;

		return $summary;
	}
}

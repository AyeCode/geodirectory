<?php
/**
 * This file contains the class for importing Posts.
 *
 * @author   AyeCode
 * @category Importers
 * @package  GeoDirectory\ImportExport\Importers
 */

namespace AyeCode\GeoDirectory\ImportExport\Importers;

use AyeCode\GeoDirectory\ImportExport\Contracts\ImporterInterface;
use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;

/**
 * PostImporter Class
 *
 * Handles the business logic for importing GeoDirectory posts from a CSV file.
 * It validates rows, geocodes addresses if necessary, and inserts or
 * updates posts in the database.
 */
class PostImporter implements ImporterInterface
{
	/**
	 * @var CsvHandler The CSV handler for reading the import file.
	 */
	private $csv_handler;

	/**
	 * @var string The slug of the settings page, used for the temp folder path.
	 */
	private $page_slug;

	/**
	 * PostImporter constructor.
	 *
	 * @param CsvHandler $csv_handler An instance of the CsvHandler.
	 * @param string     $page_slug   The slug of the admin page (e.g., 'geodir_tools').
	 */
	public function __construct(CsvHandler $csv_handler, string $page_slug)
	{
		$this->csv_handler = $csv_handler;
		$this->page_slug = $page_slug;
	}

	/**
	 * Rebuilds the full, absolute path to the temporary import file.
	 *
	 * @param string $filename The base name of the file.
	 * @return string The full server path to the file.
	 */
	private function rebuild_filepath(string $filename): string {
		return AYECODE_SF_IMPORT_TEMP_DIR . $this->page_slug . '/' . $filename;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return 'posts';
	}

	/**
	 * {@inheritDoc}
	 */
	public function validateFile(string $filename)
	{
		$filepath = $this->rebuild_filepath($filename);

		if (!is_readable($filepath)) {
			return new \WP_Error('gd-imex-file-unreadable', __('Import file is not readable.', 'geodirectory'));
		}

		$rows = $this->csv_handler->getRows($filepath, 0, 1);
		if (empty($rows)) {
			return new \WP_Error('gd-imex-file-empty', __('Import file is empty or missing headers.', 'geodirectory'));
		}
		$columns = array_keys($rows[0]);
		if (!in_array('post_title', $columns) || !in_array('post_type', $columns)) {
			return new \WP_Error('gd-imex-missing-columns', __('Import file must contain "post_title" and "post_type" columns.', 'geodirectory'));
		}

		$total_rows = 0;
		if (($handle = fopen($filepath, "r")) !== false) {
			while (($data = fgetcsv($handle, 0, ",")) !== false) {
				$total_rows++;
			}
			fclose($handle);
		}

		return ['rows' => $total_rows > 0 ? $total_rows - 1 : 0];
	}

	/**
	 * {@inheritDoc}
	 */
	public function process(string $filename, int $offset, int $limit, array $options = []): array
	{
		$filepath = $this->rebuild_filepath($filename);
		$rows = $this->csv_handler->getRows($filepath, $offset, $limit);

		$summary = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'invalid' => 0, 'errors' => []];

		foreach ($rows as $key => $row) {
			$row_number = $offset + $key + 2;
			$result = $this->processRow($row, $options);

			if (is_wp_error($result)) {
				$summary['invalid']++;
				$summary['errors'][$row_number] = $result->get_error_message();
			} else {
				$summary[$result]++;
			}
		}

		return $summary;
	}

	/**
	 * Processes a single row from the CSV file.
	 */
	private function processRow(array $row, array $options)
	{
		$validated_data = $this->validateRow($row);

		if (is_wp_error($validated_data)) {
			return $validated_data;
		}

		$is_update = !empty($validated_data['ID']);

		if ($is_update && isset($options['update_existing']) && !$options['update_existing']) {
			return 'skipped';
		}

		if ($is_update) {
			$result = wp_update_post($validated_data, true);
			return is_wp_error($result) ? $result : 'updated';
		} else {
			$result = wp_insert_post($validated_data, true);
			return is_wp_error($result) ? $result : 'created';
		}
	}

	/**
	 * Validates and sanitizes a single row of data.
	 */
	private function validateRow(array $row)
	{
		$post_info = array_map('trim', $row);

		if (empty($post_info['post_type']) || !geodir_is_gd_post_type($post_info['post_type'])) {
			return new \WP_Error('invalid-post-type', __('Invalid or missing post type.', 'geodirectory'));
		}
		if (empty($post_info['post_title'])) {
			return new \WP_Error('missing-title', __('Post title is missing.', 'geodirectory'));
		}

		$post_type = $post_info['post_type'];

		if (!empty($post_info['post_date'])) {
			$post_info['post_date'] = gmdate('Y-m-d H:i:s', strtotime($post_info['post_date']));
		}
		if (!empty($post_info['post_modified'])) {
			$post_info['post_modified'] = gmdate('Y-m-d H:i:s', strtotime($post_info['post_modified']));
		}

		if (isset($post_info['post_category'])) {
			$post_info['tax_input'][$post_type.'category'] = array_map('trim', explode(',', $post_info['post_category']));
			unset($post_info['post_category']);
		}
		if (isset($post_info['post_tags'])) {
			$post_info['tax_input'][$post_type.'_tags'] = array_map('trim', explode(',', $post_info['post_tags']));
			unset($post_info['post_tags']);
		}

		if (\geodirectory()->post_types->supports($post_type, 'location') && geodir_cpt_requires_address($post_type)) {
			if (empty($post_info['latitude']) || empty($post_info['longitude'])) {
				$geocoded_info = $this->geocodeAddress($post_info);
				if (is_wp_error($geocoded_info)) {
					return $geocoded_info;
				}
				$post_info = $geocoded_info;
			}
		}

		if (isset($post_info['post_status'])) {
			if ($post_info['post_status'] == 'published') {
				$post_info['post_status'] = 'publish';
			}
			$allowed_statuses = geodir_get_post_stati('import', $post_info);
			if (!in_array($post_info['post_status'], $allowed_statuses)) {
				$post_info['post_status'] = 'pending';
			}
		}

		return apply_filters('geodir_import_validate_post', $post_info, $row);
	}

	/**
	 * Gets GPS coordinates from an address using the geocoding API.
	 */
	private function geocodeAddress(array $post_info)
	{
		$gps = geodir_get_gps_from_address($post_info, true);

		if (is_wp_error($gps)) {
			return new \WP_Error('geocoding-failed', $gps->get_error_message());
		}

		if (empty($gps['latitude']) || empty($gps['longitude'])) {
			return new \WP_Error('geocoding-no-results', __('Geocoding API failed to return coordinates for the address.', 'geodirectory'));
		}

		$post_info['latitude'] = $gps['latitude'];
		$post_info['longitude'] = $gps['longitude'];

		return $post_info;
	}
}

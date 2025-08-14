<?php
/**
 * This file contains the class for importing Categories.
 *
 * @author   AyeCode
 * @category Importers
 * @package  GeoDirectory\ImportExport\Importers
 */

namespace AyeCode\GeoDirectory\ImportExport\Importers;

use AyeCode\GeoDirectory\ImportExport\Contracts\ImporterInterface;
use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;

/**
 * CategoryImporter Class
 *
 * Handles the business logic for importing categories from a CSV file.
 * It validates rows, resolves parent-child relationships, and manages
 * term metadata including custom icons and images.
 */
class CategoryImporter implements ImporterInterface
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
	 * CategoryImporter constructor.
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
		return 'categories';
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
		if (!in_array('cat_name', $columns) || !in_array('cat_posttype', $columns)) {
			return new \WP_Error('gd-imex-missing-columns', __('Import file must contain "cat_name" and "cat_posttype" columns.', 'geodirectory'));
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
	 * Processes a single row from the category CSV file.
	 */
	private function processRow(array $row, array $options)
	{
		$validated_data = $this->validateRow($row);

		if (is_wp_error($validated_data)) {
			return $validated_data;
		}

		$is_update = !empty($validated_data['term_id']);

		if ($is_update && isset($options['update_existing']) && !$options['update_existing']) {
			return 'skipped';
		}

		$term_id = $this->createOrUpdateTerm($validated_data);

		if (is_wp_error($term_id)) {
			return $term_id;
		}

		$this->handleTermMeta($term_id, $validated_data);

		return $is_update ? 'updated' : 'created';
	}

	/**
	 * Validates and sanitizes a single row of category data.
	 */
	private function validateRow(array $row)
	{
		$data = array_map('trim', $row);

		$term_data = [
			'term_id'     => $data['cat_id'] ?? null,
			'name'        => $data['cat_name'] ?? null,
			'slug'        => $data['cat_slug'] ?? null,
			'description' => $data['cat_description'] ?? '',
			'parent'      => $data['cat_parent'] ?? 0,
			'taxonomy'    => isset($data['cat_posttype']) ? $data['cat_posttype'] . 'category' : null,
		];

		if (empty($term_data['name'])) {
			return new \WP_Error('missing-cat-name', __('Category name is missing.', 'geodirectory'));
		}
		if (empty($term_data['taxonomy'])) {
			return new \WP_Error('missing-cat-posttype', __('Category post type is missing.', 'geodirectory'));
		}

		if (!empty($term_data['parent'])) {
			$parent_term = get_term_by('id', $term_data['parent'], $term_data['taxonomy']) ?:
				get_term_by('slug', $term_data['parent'], $term_data['taxonomy']) ?:
					get_term_by('name', $term_data['parent'], $term_data['taxonomy']);
			$term_data['parent'] = $parent_term ? $parent_term->term_id : 0;
		}

		$term_data['meta'] = [
			'cat_schema'             => $data['cat_schema'] ?? '',
			'cat_font_icon'          => $data['cat_font_icon'] ?? '',
			'cat_color'              => $data['cat_color'] ?? '',
			'cat_top_description'    => $data['cat_top_description'] ?? '',
			'cat_bottom_description' => $data['cat_bottom_description'] ?? '',
			'cat_image'              => $data['cat_image'] ?? '',
			'cat_icon'               => $data['cat_icon'] ?? '',
		];

		return apply_filters('geodir_import_category_validate_item', $term_data, $row);
	}

	/**
	 * Creates or updates a term in the database.
	 */
	private function createOrUpdateTerm(array $term_data)
	{
		if (!empty($term_data['term_id'])) {
			$result = wp_update_term($term_data['term_id'], $term_data['taxonomy'], $term_data);
		} else {
			$result = wp_insert_term($term_data['name'], $term_data['taxonomy'], $term_data);
		}

		if (is_wp_error($result)) {
			return $result;
		}

		return $result['term_id'];
	}

	/**
	 * Handles updating all metadata for a given term.
	 */
	private function handleTermMeta(int $term_id, array $term_data): void
	{
		$meta = $term_data['meta'] ?? [];
		if (empty($meta)) {
			return;
		}

		update_term_meta($term_id, 'ct_cat_top_desc', $meta['cat_top_description']);
		update_term_meta($term_id, 'ct_cat_bottom_desc', $meta['cat_bottom_description']);
		update_term_meta($term_id, 'ct_cat_schema', $meta['cat_schema']);
		update_term_meta($term_id, 'ct_cat_font_icon', $meta['cat_font_icon']);
		update_term_meta($term_id, 'ct_cat_color', $meta['cat_color']);

		if (!empty($meta['cat_image'])) {
			$this->attachImageToTerm($term_id, $meta['cat_image'], 'ct_cat_default_img');
		}
		if (!empty($meta['cat_icon'])) {
			$this->attachImageToTerm($term_id, $meta['cat_icon'], 'ct_cat_icon');
		}
	}

	/**
	 * Attaches an image from a URL to a term's metadata.
	 */
	private function attachImageToTerm(int $term_id, string $image_url, string $meta_key): void
	{
		if (filter_var($image_url, FILTER_VALIDATE_URL)) {
			$upload = \GeoDir_Media::upload_image_from_url($image_url);
			if (!is_wp_error($upload) && !empty($upload['file'])) {
				$attachment_id = \GeoDir_Media::set_uploaded_image_as_attachment($upload);
				if (!is_wp_error($attachment_id) && $attachment_id > 0) {
					$attachment_url = wp_get_attachment_url($attachment_id);
					$image_data = [
						'id' => $attachment_id,
						'src' => geodir_file_relative_url($attachment_url)
					];
					update_term_meta($term_id, $meta_key, $image_data);
				}
			}
		}
	}
}

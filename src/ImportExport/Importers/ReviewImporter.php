<?php
/**
 * This file contains the class for importing Reviews.
 *
 * @author   AyeCode
 * @category Importers
 * @package  GeoDirectory\ImportExport\Importers
 */

namespace AyeCode\GeoDirectory\ImportExport\Importers;

use AyeCode\GeoDirectory\ImportExport\Contracts\ImporterInterface;
use AyeCode\GeoDirectory\ImportExport\Handlers\CsvHandler;

/**
 * ReviewImporter Class
 *
 * Handles the business logic for importing reviews from a CSV file.
 */
class ReviewImporter implements ImporterInterface
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
	 * ReviewImporter constructor.
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
		return 'reviews';
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
		$required = ['comment_post_ID', 'rating', 'comment_content'];
		if (count(array_intersect($required, $columns)) !== count($required)) {
			return new \WP_Error('gd-imex-missing-columns', __('Import file must contain "comment_post_ID", "rating", and "comment_content" columns.', 'geodirectory'));
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
	public function process(string $filename, int $offset, int $limit, array $options = []): array {
		$filepath = $this->rebuild_filepath($filename); // Rebuild the path here.
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
	 * Processes a single row from the review CSV file.
	 */
	private function processRow(array $row, array $options)
	{
		$validated_data = $this->validateRow($row);

		if (is_wp_error($validated_data)) {
			return $validated_data;
		}

		$is_update = !empty($validated_data['comment_ID']);

		if ($is_update && isset($options['update_existing']) && $options['update_existing'] === false) {
			return 'skipped';
		}

		$_REQUEST['geodir_overallrating'] = $validated_data['rating'];

		$result = $this->createOrUpdateReview($validated_data, $is_update);

		unset($_REQUEST['geodir_overallrating']);

		if (is_wp_error($result)) {
			return $result;
		}

		return $is_update ? 'updated' : 'created';
	}

	/**
	 * Validates and sanitizes a single row of review data.
	 */
	private function validateRow(array $row)
	{
		$data = array_map('trim', $row);

		if (empty($data['comment_content'])) {
			return new \WP_Error('invalid-content', __('Review content is missing.', 'geodirectory'));
		}
		if (empty($data['comment_post_ID']) || !geodir_is_gd_post_type(get_post_type($data['comment_post_ID']))) {
			return new \WP_Error('invalid-post-id', __('Review has an invalid or missing post ID.', 'geodirectory'));
		}
		if (empty($data['rating']) || !is_numeric($data['rating'])) {
			return new \WP_Error('invalid-rating', __('Review rating is missing or invalid.', 'geodirectory'));
		}
		if (empty($data['user_id']) && (empty($data['comment_author']) || empty($data['comment_author_email']))) {
			return new \WP_Error('invalid-author', __('Review must have a user_id or both an author name and email.', 'geodirectory'));
		}

		$review_data = [
			'comment_ID'           => $data['comment_ID'] ?? null,
			'comment_post_ID'      => $data['comment_post_ID'],
			'comment_author'       => $data['comment_author'] ?? '',
			'comment_author_email' => $data['comment_author_email'] ?? '',
			'comment_author_url'   => $data['comment_author_url'] ?? '',
			'comment_author_IP'    => $data['comment_author_IP'] ?? '',
			'comment_date'         => !empty($data['comment_date']) ? gmdate('Y-m-d H:i:s', strtotime($data['comment_date'])) : current_time('mysql'),
			'comment_content'      => $data['comment_content'],
			'comment_approved'     => $data['comment_approved'] ?? 1,
			'user_id'              => $data['user_id'] ?? 0,
			'rating'               => (int)$data['rating'],
		];

		$approved_stati = ['1', 'approve', 'approved'];
		$review_data['comment_approved'] = in_array($review_data['comment_approved'], $approved_stati) ? 1 : 0;

		return wp_filter_comment($review_data);
	}

	/**
	 * Inserts or updates a review and its associated rating.
	 */
	private function createOrUpdateReview(array $review_data, bool $is_update)
	{
		if ($is_update) {
			$comment_id = wp_update_comment($review_data);
			if ($comment_id === false) {
				return new \WP_Error('update-failed', __('Failed to update the review in the database.', 'geodirectory'));
			}
			\GeoDir_Comments::edit_comment($review_data['comment_ID']);
		} else {
			$comment_id = wp_insert_comment($review_data);
			if (is_wp_error($comment_id) || $comment_id === 0) {
				return new \WP_Error('insert-failed', __('Failed to insert the new review.', 'geodirectory'));
			}
			\GeoDir_Comments::save_rating($comment_id);
		}

		return true;
	}
}

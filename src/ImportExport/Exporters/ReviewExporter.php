<?php
/**
 * This file contains the class for exporting Reviews.
 *
 * @author   AyeCode
 * @category Exporters
 * @package  GeoDirectory\ImportExport\Exporters
 */

namespace AyeCode\GeoDirectory\ImportExport\Exporters;

use AyeCode\GeoDirectory\ImportExport\Contracts\ExporterInterface;

/**
 * ReviewExporter Class
 *
 * Handles the business logic for exporting GeoDirectory reviews to a CSV file.
 */
class ReviewExporter implements ExporterInterface
{
	/**
	 * Optional filter arguments.
	 * @var array
	 */
	private $filters;

	/**
	 * ReviewExporter constructor.
	 *
	 * @param array $filters Optional filters for the review query.
	 */
	public function __construct(array $filters = [])
	{
		$this->filters = $filters;
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
	public function getColumns(): array
	{
		// This defines the exact order for our columns.
		return [
			'comment_ID', 'comment_post_ID', 'rating', 'comment_content', 'comment_date',
			'comment_approved', 'user_id', 'comment_author', 'comment_author_email',
			'comment_author_url', 'comment_author_IP', 'post_type', 'city',
			'region', 'country', 'latitude', 'longitude',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTotalCount(): int
	{
		global $wpdb;
		$args = $this->getCommentQueryArgs(true);
		$query = new \WP_Comment_Query();

		add_filter('comments_clauses', [$this, 'filterReviewClauses'], 10, 2);
		$query->query($args);
		remove_filter('comments_clauses', [$this, 'filterReviewClauses'], 10);

		return (int) $wpdb->get_var($query->request);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(int $limit, int $offset): array
	{
		global $wpdb;
		$args = $this->getCommentQueryArgs(false);
		$args['number'] = $limit;
		$args['offset'] = $offset;

		$query = new \WP_Comment_Query();

		add_filter('comments_clauses', [$this, 'filterReviewClauses'], 10, 2);
		$query->query($args);
		remove_filter('comments_clauses', [$this, 'filterReviewClauses'], 10);

		$results = $wpdb->get_results($query->request);

		if (empty($results)) {
			return [];
		}

		// --- Start of fix ---
		// We now manually build each row to ensure the column order is correct,
		// matching the logic of your original, working code.
		$csv_rows = [];
		$columns = $this->getColumns();

		foreach ($results as $item) {
			$row = [];
			foreach ($columns as $column_key) {
				// Add the value if the property exists on the item, otherwise add an empty string.
				$row[] = $item->{$column_key} ?? '';
			}
			$csv_rows[] = $row;
		}

		return $csv_rows;
		// --- End of fix ---
	}

	/**
	 * Builds the arguments for the WP_Comment_Query.
	 */
	private function getCommentQueryArgs(bool $is_count_query = false): array
	{
		$post_types = geodir_get_posttypes();

		$args = [
			'count'      => $is_count_query,
			'parent'     => 0,
			'status'     => 'any',
			'orderby'    => 'comment_ID',
			'order'      => 'ASC',
		];

		if (!empty($this->filters['post_type']) && in_array($this->filters['post_type'], $post_types)) {
			$args['post_type'] = sanitize_text_field($this->filters['post_type']);
		} else {
			$args['post_type'] = $post_types;
		}

		if (!empty($this->filters['start_date']) || !empty($this->filters['end_date'])) {
			$args['date_query'] = ['inclusive' => true];
			if (!empty($this->filters['start_date'])) $args['date_query']['after'] = $this->filters['start_date'];
			if (!empty($this->filters['end_date'])) $args['date_query']['before'] = $this->filters['end_date'];
		}

		if (!empty($this->filters['status'])) {
			$args['status'] = sanitize_key($this->filters['status']);
		}

		return $args;
	}

	/**
	 * Modifies the WP_Comment_Query clauses to join the GD review table.
	 */
	public function filterReviewClauses(array $clauses): array
	{
		global $wpdb;

		if (strpos($clauses['fields'], 'COUNT') === false) {
			$clauses['fields'] = "{$wpdb->comments}.*, r.*";
		}

		$clauses['join'] .= " INNER JOIN " . GEODIR_REVIEW_TABLE . " AS r ON r.comment_id = {$wpdb->comments}.comment_ID";

		$where = ["r.rating > 0"];
		if (!empty($this->filters['min_rating'])) {
			$where[] = $wpdb->prepare("r.rating >= %d", absint($this->filters['min_rating']));
		}
		if (!empty($this->filters['max_rating'])) {
			$where[] = $wpdb->prepare("r.rating <= %d", absint($this->filters['max_rating']));
		}
		$clauses['where'] .= " AND " . implode(" AND ", $where);

		return $clauses;
	}
}

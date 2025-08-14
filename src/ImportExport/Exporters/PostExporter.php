<?php
/**
 * This file contains the class for exporting Posts.
 *
 * @author   AyeCode
 * @category Exporters
 * @package  GeoDirectory\ImportExport\Exporters
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Exporters;

use AyeCode\GeoDirectory\ImportExport\Contracts\ExporterInterface;

/**
 * PostExporter Class
 *
 * Handles the business logic for exporting GeoDirectory posts to a CSV file.
 * It queries the database, formats the data, and provides it in chunks.
 */
class PostExporter implements ExporterInterface
{
	/**
	 * The post type this exporter is responsible for.
	 * @var string
	 */
	private $post_type;

	/**
	 * Optional filter arguments.
	 * @var array
	 */
	private $filters;

	/**
	 * PostExporter constructor.
	 *
	 * @param string $post_type The GeoDirectory post type to export (e.g., 'gd_place').
	 * @param array  $filters   Optional filters, such as start_date and end_date.
	 */
	public function __construct(string $post_type, array $filters = [])
	{
		$this->post_type = $post_type;
		$this->filters = $filters;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getIdentifier(): string
	{
		return 'posts'; // A generic identifier for post types.
	}

	/**
	 * {@inheritDoc}
	 */
	public function getColumns(): array
	{
		// To get the columns, we fetch one row and get its keys.
		// This ensures the columns are always accurate to the data.
		$first_row = $this->getData(1, 0);

		if (empty($first_row)) {
			return [];
		}

		return array_keys($first_row[0]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getTotalCount(): int
	{
		// This is a global function from the old codebase that we can still leverage.
		// We add our filter to it.
		add_filter('geodir_get_posts_count', [$this, 'applyDateFilters'], 10, 2);
		$count = geodir_get_posts_count($this->post_type);
		remove_filter('geodir_get_posts_count', [$this, 'applyDateFilters'], 10);

		return (int) $count;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData(int $limit, int $offset): array
	{
		global $wpdb, $plugin_prefix;

		$table = $plugin_prefix . $this->post_type . '_detail';
		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) != $table) {
			return []; // Table doesn't exist.
		}

		// This is the main query logic, adapted from the old `get_export_posts` function.
		$columns = $this->getSelectColumns($table);
		$where_clause = $this->getWhereClause();
		$limit_clause = $wpdb->prepare("LIMIT %d, %d", $offset, $limit);

		$query = "SELECT " . implode(', ', $columns) . " FROM {$wpdb->posts} INNER JOIN {$table} ON {$table}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_type = %s {$where_clause} ORDER BY {$wpdb->posts}.ID ASC {$limit_clause}";
		$results = (array) $wpdb->get_results($wpdb->prepare($query, $this->post_type), ARRAY_A);

		return $this->processPostRows($results);
	}

	/**
	 * Processes the raw database rows to add extra computed data.
	 *
	 * @param array $rows The array of rows from the database.
	 * @return array The processed rows.
	 */
	private function processPostRows(array $rows): array
	{
		if (empty($rows)) {
			return [];
		}

		$processed_rows = [];
		foreach ($rows as $row) {
			// Add post images string, as in the old `get_posts_csv`.
			if (isset($row['ID'])) {
				$row['post_images'] = \GeoDir_Media::get_field_edit_string($row['ID'], 'post_images', '', '', true);
			}

			$processed_rows[] = $row;
		}

		return $processed_rows;
	}

	/**
	 * Determines which columns to SELECT from the database.
	 *
	 * @param string $details_table The name of the CPT details table.
	 * @return array A list of columns to select.
	 */
	private function getSelectColumns(string $details_table): array
	{
		global $wpdb;

		$post_columns = [
			"{$wpdb->posts}.ID", "{$wpdb->posts}.post_title", "{$wpdb->posts}.post_content",
			"{$wpdb->posts}.post_status", "{$wpdb->posts}.post_author", "{$wpdb->posts}.post_type",
			"{$wpdb->posts}.post_date", "{$wpdb->posts}.post_modified",
		];

		$cpt_exclude_columns = [
			'post_id', 'post_title', '_search_title', 'post_status', 'submit_ip',
			'overall_rating', 'rating_count', 'mapview', 'mapzoom', 'post_dummy', 'featured_image',
		];

		$detail_columns = [];
		$schema = $wpdb->get_results("DESCRIBE `{$details_table}`");
		if (!empty($schema)) {
			foreach ($schema as $column_schema) {
				if (!in_array($column_schema->Field, $cpt_exclude_columns)) {
					$detail_columns[] = "`" . $column_schema->Field . "`";
				}
			}
		}

		return array_merge($post_columns, $detail_columns);
	}

	/**
	 * Builds the WHERE part of the SQL query.
	 *
	 * @return string The SQL WHERE clause additions.
	 */
	private function getWhereClause(): string
	{
		$where = '';

		// Add status filtering.
		$skip_statuses = geodir_imex_export_skip_statuses();
		if (!empty($skip_statuses) && is_array($skip_statuses)) {
			$where .= " AND `{$GLOBALS['wpdb']->posts}`.`post_status` NOT IN('" . implode( "','", array_map('esc_sql', $skip_statuses)) . "')";
		}

		// Add our date filters.
		$where = $this->applyDateFilters($where, $this->post_type);

		return $where;
	}

	/**
	 * Applies date range filters to a WHERE clause.
	 *
	 * This is designed to be hooked into a WordPress filter.
	 *
	 * @param string $where The existing WHERE clause.
	 * @param string $post_type The post type being queried.
	 * @return string The modified WHERE clause.
	 */
	public function applyDateFilters(string $where, string $post_type): string
	{
		if (empty($this->filters)) {
			return $where;
		}

		if (!empty($this->filters['start_date'])) {
			$where .= " AND `{$GLOBALS['wpdb']->posts}`.`post_date` >= '" . esc_sql($this->filters['start_date']) . " 00:00:00'";
		}
		if (!empty($this->filters['end_date'])) {
			$where .= " AND `{$GLOBALS['wpdb']->posts}`.`post_date` <= '" . esc_sql($this->filters['end_date']) . " 23:59:59'";
		}

		return $where;
	}
}

<?php
/**
 * Post Report Repository
 *
 * Handles all database interactions for the post reports table.
 *
 * @package GeoDirectory\Database\Repository
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Repository;

use AyeCode\GeoDirectory\Database\Schema\ReportedSchema;

final class ReportedRepository {

	/**
	 * @var \wpdb The WordPress database object.
	 */
	private $db;

	/**
	 * @var string The name of the post_reports table.
	 */
	private $table_name;

	/**
	 * @var string The primary key of the table.
	 */
	private $primary_key;

	public function __construct() {
		global $wpdb;
		$this->db          = $wpdb;
		$this->table_name  = geodirectory()->tables->get( 'post_reports' );
		$this->primary_key = ( new ReportedSchema() )->get_primary_key();
	}

	/**
	 * Gets a single report by its ID.
	 *
	 * @param int $report_id The ID of the report.
	 * @return array|null The raw data from the database or null if not found.
	 */
	public function get_by_id( int $report_id ): ?array {
		if ( $report_id <= 0 ) {
			return null;
		}

		$result = $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE {$this->primary_key} = %d", $report_id ),
			ARRAY_A
		);

		return $result ? $result : null;
	}

	/**
	 * Gets all reports with optional filtering.
	 *
	 * @param string $status  Filter by a specific status (e.g., 'pending'). Default 'all'.
	 * @param array  $filters Associative array of additional column => value filters.
	 *
	 * @return array The raw data from the database.
	 */
	public function get_all( $status = 'all', $filters = [] ): array {
		$where_clauses = [ '1=1' ];
		$prepare_args  = [];

		// 1. Handle the primary status filter
		if ( $status !== 'all' ) {
			$where_clauses[] = 'status = %s';
			$prepare_args[]  = $status;
		}

		// 2. Handle the dynamic filters array
		if ( ! empty( $filters ) && is_array( $filters ) ) {
			// Get a list of allowed filterable columns from our schema
			$allowed_columns = array_keys( ( new ReportedSchema() )->get_schema() );

			foreach ( $filters as $column => $value ) {
				//Validate the user-supplied column name against our allowed list
				if ( in_array( $column, $allowed_columns, true ) ) {
					// The column is valid, so we can safely add it to the query
					$where_clauses[] = "`" . $column . "` = %s";
					$prepare_args[]  = $value;
				}
			}
		}

		// 3. Build and execute the query
		$where_sql = implode( ' AND ', $where_clauses );

		$query = "SELECT * FROM {$this->table_name} WHERE {$where_sql}";

//		echo $query;print_r( $prepare_args );exit;

		$results = ! empty( $prepare_args ) ?
			$this->db->get_results(
				$this->db->prepare( $query, $prepare_args ),
				ARRAY_A
			) :
			$this->db->get_results( $query, ARRAY_A );

		return $results ? $results : [];
	}

	/**
	 * Gets the counts for each status.
	 * @return array Associative array of [status => count].
	 */
	public function get_status_counts() {
		$all_keys = $this->get_all();
		$counts = [
			'pending' => 0,
			'rejected' => 0,
			'resolved' => 0,
		];

		foreach ($all_keys as $key) {
			if (isset($key['status']) && isset($counts[$key['status']])) {
				$counts[$key['status']]++;
			}
		}

		// Add the 'all' count
		$counts['all'] = count($all_keys);

		return $counts;
	}

	/**
	 * Deletes a single report by its primary ID.
	 *
	 * @param int $report_id The ID of the report to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_report( int $report_id ): bool {
		if ( $report_id <= 0 ) {
			return false;
		}

		$result = $this->db->delete( $this->table_name, [ $this->primary_key => $report_id ], [ '%d' ] );

		return $result !== false;
	}

	/**
	 * Deletes multiple reports in a single query based on an array of IDs.
	 *
	 * @param array $report_ids An array of report IDs to delete.
	 * @return int|false The number of rows deleted, or false on failure.
	 */
	public function bulk_delete( array $report_ids ) {
		if ( empty( $report_ids ) ) {
			return false;
		}

		// Ensure all IDs are integers for security.
		$ids = array_map( 'absint', $report_ids );

		// Remove any zeros that might have resulted from sanitization.
		$ids = array_filter( $ids );

		if ( empty( $ids ) ) {
			return false;
		}

		// Create a string of placeholders for the IN clause (%d, %d, %d).
		$placeholders = implode( ', ', array_fill( 0, count( $ids ), '%d' ) );

		// Prepare and execute the single SQL statement.
		$result = $this->db->query(
			$this->db->prepare(
				"DELETE FROM {$this->table_name} WHERE {$this->primary_key} IN ( {$placeholders} )",
				$ids
			)
		);

		return $result !== false;
	}

	/**
	 * Adds a new report to the database.
	 *
	 * @param array $data The data for the new report.
	 * @return int|false The ID of the newly inserted report, or false on failure.
	 */
	public function add_report( array $data ) {
		$schema = new ReportedSchema();
		$defaults = $schema->get_defaults();
		$formats = $schema->get_formats();

		$data_to_save = array_merge( $defaults, array_intersect_key( $data, $defaults ) );
		unset( $data_to_save[$this->primary_key] );

		// Add report date if not set
		if ( empty( $data_to_save['report_date'] ) ) {
			$data_to_save['report_date'] = current_time( 'mysql' );
		}

		$ordered_formats = [];
		foreach ($data_to_save as $key => $value) {
			if (isset($formats[$key])) {
				$ordered_formats[] = $formats[$key];
			}
		}

		$result = $this->db->insert(
			$this->table_name,
			$data_to_save,
			$ordered_formats
		);

		return $result ? $this->db->insert_id : false;
	}

	/**
	 * Updates an existing report.
	 *
	 * @param int $report_id The ID of the report to update.
	 * @param array $data The new data for the report.
	 * @return bool True on success, false on failure.
	 */
	public function update_report( int $report_id, array $data ): bool {
		if ( $report_id <= 0 ) {
			return false;
		}

		$schema = new ReportedSchema();
		$formats = $schema->get_formats();

		unset( $data[$this->primary_key] );

		// Add updated date
		$data['updated_date'] = current_time( 'mysql' );

		$ordered_formats = [];
		foreach ($data as $key => $value) {
			if (isset($formats[$key])) {
				$ordered_formats[] = $formats[$key];
			}
		}

		$result = $this->db->update(
			$this->table_name,
			$data,
			[ $this->primary_key => $report_id ],
			$ordered_formats,
			[ '%d' ]
		);

		return $result !== false;
	}
}

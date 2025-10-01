<?php
/**
 * API Key Repository
 *
 * Handles all database interactions for the API keys table.
 *
 * @package GeoDirectory\Database\Repository
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Repository;

use AyeCode\GeoDirectory\Database\Schema\ApiKeySchema;

final class ApiKeyRepository {

	/**
	 * @var \wpdb The WordPress database object.
	 */
	private $db;

	/**
	 * @var string The name of the api_keys table.
	 */
	private $table_name;

	/**
	 * @var string The primary key of the table.
	 */
	private $primary_key;

	public function __construct() {
		global $wpdb;
		$this->db          = $wpdb;
		$this->table_name  = geodirectory()->tables->get( 'api_keys' );
		$this->primary_key = ( new ApiKeySchema() )->get_primary_key();
	}

	/**
	 * Gets a single API key by its ID.
	 *
	 * @param int $key_id The ID of the key.
	 * @return array|null The raw data from the database or null if not found.
	 */
	public function get_by_id( int $key_id ): ?array {
		if ( $key_id <= 0 ) {
			return null;
		}

		$result = $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE {$this->primary_key} = %d", $key_id ),
			ARRAY_A
		);

		return $result ? $result : null;
	}

	/**
	 * Gets all API keys.
	 *
	 * @return array The raw data from the database.
	 */
	public function get_all(): array {
		$results = $this->db->get_results( "SELECT * FROM {$this->table_name}", ARRAY_A );
		return $results ? $results : [];
	}

	/**
	 * Deletes a single API key by its primary ID.
	 *
	 * @param int $key_id The ID of the key to delete.
	 * @return bool True on success, false on failure.
	 */
	public function delete_key( int $key_id ): bool {
		if ( $key_id <= 0 ) {
			return false;
		}

		$result = $this->db->delete( $this->table_name, [ $this->primary_key => $key_id ], [ '%d' ] );

		return $result !== false;
	}

	/**
	 * Adds a new API key to the database.
	 *
	 * @param array $data The data for the new key.
	 * @return int|false The ID of the newly inserted key, or false on failure.
	 */
	public function add_key( array $data ) {
		$schema = new ApiKeySchema();
		$defaults = $schema->get_defaults();
		$formats = $schema->get_formats();

		$data_to_save = array_merge( $defaults, array_intersect_key( $data, $defaults ) );
		unset( $data_to_save[$this->primary_key] );

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
	 * Updates an existing API key.
	 *
	 * @param int $key_id The ID of the key to update.
	 * @param array $data The new data for the key.
	 * @return bool True on success, false on failure.
	 */
	public function update_key( int $key_id, array $data ): bool {
		if ( $key_id <= 0 ) {
			return false;
		}

		$schema = new ApiKeySchema();
		$formats = $schema->get_formats();

		unset( $data[$this->primary_key] );

		$ordered_formats = [];
		foreach ($data as $key => $value) {
			if (isset($formats[$key])) {
				$ordered_formats[] = $formats[$key];
			}
		}

		$result = $this->db->update(
			$this->table_name,
			$data,
			[ $this->primary_key => $key_id ],
			$ordered_formats,
			[ '%d' ]
		);

		return $result !== false;
	}
}

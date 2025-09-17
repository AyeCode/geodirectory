<?php
/**
 * Attachment Repository
 *
 * Handles all database interactions for the custom attachments table.
 *
 * @package GeoDirectory\Database
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Repository;

final class AttachmentRepository {
	private \wpdb $db;
	private string $table_name;

	/**
	 * Constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = \geodirectory()->tables->get( 'attachments' );
	}

	/**
	 * Finds a single attachment by its ID.
	 *
	 * @param int $id The attachment ID.
	 * @return object|null The attachment data row, or null if not found.
	 */
	public function find( int $id ): ?object {
		return $this->db->get_row(
			$this->db->prepare( "SELECT * FROM {$this->table_name} WHERE ID = %d", $id )
		);
	}

	/**
	 * Finds all attachments for a given post and type.
	 *
	 * @param int          $post_id The post ID.
	 * @param string|array $type    The attachment type(s) (e.g., 'post_images').
	 * @param int|null     $limit   Optional. The maximum number of attachments to return.
	 * @return array An array of attachment data rows.
	 */
	public function find_by_post( int $post_id, $type, int $limit = null ): array {
		$types     = (array) $type;
		$types_sql = implode( ',', array_fill( 0, count( $types ), '%s' ) );
		$params    = array_merge( [ $post_id ], $types );
		$limit_sql = '';

		if ( $limit !== null ) {
			$limit_sql = 'LIMIT %d';
			$params[]  = $limit;
		}

		$query = $this->db->prepare(
			"SELECT * FROM {$this->table_name} WHERE post_id = %d AND type IN ({$types_sql}) ORDER BY menu_order ASC, ID DESC {$limit_sql}",
			$params
		);

		return $this->db->get_results( $query );
	}

	/**
	 * Counts all image attachments in the database.
	 *
	 * @return int The total number of image attachments.
	 */
	public function count_all_images(): int {
		return (int) $this->db->get_var(
			$this->db->prepare( "SELECT COUNT(*) FROM {$this->table_name} WHERE mime_type LIKE %s OR type = %s", 'image/%', 'post_images' )
		);
	}

	/**
	 * Creates a new attachment record.
	 *
	 * @param array $data The data to insert.
	 * @return int|false The new attachment ID, or false on error.
	 */
	public function create( array $data ) {
		$result = $this->db->insert( $this->table_name, $data );
		return $result ? $this->db->insert_id : false;
	}

	/**
	 * Updates an existing attachment record.
	 *
	 * @param int   $id   The attachment ID.
	 * @param array $data The data to update.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( int $id, array $data ) {
		return $this->db->update( $this->table_name, $data, [ 'ID' => $id ] );
	}

	/**
	 * Deletes an attachment record from the database.
	 *
	 * @param int $id The attachment ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( int $id ) {
		return $this->db->delete( $this->table_name, [ 'ID' => $id ], [ '%d' ] );
	}
}

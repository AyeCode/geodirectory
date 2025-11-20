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
	 * Retrieves attachments based on a variety of criteria.
	 *
	 * This method replaces the logic found in legacy function GeoDir_Media::get_attachments_by_type.
	 *
	 * @param array $args {
	 * Optional. Arguments to retrieve attachments.
	 *
	 * @type int|array $post_id     Post ID(s).
	 * @type string    $mime_type   Attachment type (e.g., 'post_images'). Matches the 'type' column in DB.
	 * @type int       $limit       Limit results.
	 * @type int       $revision_id Revision ID (for previews).
	 * @type string    $other_id    Temporary ID (for temp uploads).
	 * @type string|int $status     Approval status.
	 * }
	 * @return array Array of attachment objects.
	 */
	public function get_by_type( array $args = [] ): array {
		$defaults = [
			'post_id'     => 0,
			'mime_type'   => '',
			'limit'       => 0,
			'revision_id' => '',
			'other_id'    => '',
			'status'      => '',
		];

//		print_r( $args );exit;

		$args = wp_parse_args( $args, $defaults );

		$where      = [];
		$query_args = [];

		// 1. Post ID logic (Handle Post ID OR Revision ID OR Temp ID)
		$id_conditions = [];

		if ( ! empty( $args['post_id'] ) ) {
			$id_conditions[] = 'post_id = %d';
			$query_args[]    = $args['post_id'];
		}

		if ( ! empty( $args['revision_id'] ) ) {
			// Legacy logic stored revision lookups using post_id = "__$rev_id"
			// Adjust this based on how V3 stores temp data. Assuming legacy compatibility:
			$id_conditions[] = 'post_id = %s';
			$query_args[]    = '__' . $args['revision_id'];
		}

		if ( ! empty( $args['other_id'] ) ) {
			$id_conditions[] = 'post_id = %s';
			$query_args[]    = $args['other_id'];
		}

		if ( ! empty( $id_conditions ) ) {
			// ( post_id = 10 OR post_id = '__10' OR post_id = 'temp_xyz' )
			$where[] = '( ' . implode( ' OR ', $id_conditions ) . ' )';
		}

		// 2. Type (mime_type in arg, 'type' column in DB usually holds 'post_images')
		if ( ! empty( $args['mime_type'] ) ) {
			$where[]      = 'type = %s';
			$query_args[] = $args['mime_type'];
		}

		// 3. Status
		if ( $args['status'] !== '' ) {
			$where[]      = 'is_approved = %d';
			$query_args[] = (int) $args['status'];
		}

		// Build Query
		$sql = "SELECT * FROM {$this->table_name}";

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		$sql .= " ORDER BY menu_order ASC, ID DESC";

		if ( ! empty( $args['limit'] ) && $args['limit'] > 0 ) {
			$sql .= " LIMIT %d";
			$query_args[] = $args['limit'];
		}

		if ( ! empty( $query_args ) ) {
			$sql = $this->db->prepare( $sql, $query_args );
		}

		$results = $this->db->get_results( $sql );

		return $results ? $results : [];
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

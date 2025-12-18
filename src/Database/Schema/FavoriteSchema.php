<?php
/**
 * Defines the database schema for the Favorites table.
 *
 * Stores user favorite posts with timestamps for sorting and filtering.
 *
 * @package GeoDirectory\Database\Schema
 * @since   3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

final class FavoriteSchema extends AbstractTableSchema {

	/**
	 * @inheritdoc
	 */
	protected function get_filter_key(): string {
		return 'favorites';
	}

	/**
	 * @inheritdoc
	 */
	public function get_primary_key(): string {
		return 'id';
	}

	/**
	 * @inheritdoc
	 */
	protected function define_schema(): array {
		return [
			'id'           => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL AUTO_INCREMENT', 'format' => '%d', 'default' => 0, 'ui_key' => 'id', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'user_id'      => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'post_id'      => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'post_type'    => [ 'type' => 'varchar(20)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_key', 'db_sanitize' => 'sanitize_key' ],
			'date_added'   => [ 'type' => 'datetime', 'extra' => 'NOT NULL DEFAULT CURRENT_TIMESTAMP', 'format' => '%s', 'default' => '0000-00-00 00:00:00' ],
			'site_id'      => [ 'type' => 'int(11)', 'extra' => 'NOT NULL DEFAULT 1', 'format' => '%d', 'default' => 1, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
		];
	}

	/**
	 * Get the SQL indexes for the table.
	 *
	 * @return array Array of index definitions.
	 */
	public function get_indexes(): array {
		return [
			'user_id' => 'INDEX user_id (user_id)',
			'post_id' => 'INDEX post_id (post_id)',
			'user_post' => 'UNIQUE KEY user_post (user_id, post_id, site_id)',
			'date_added' => 'INDEX date_added (date_added)',
		];
	}
}

<?php
/**
 * Defines the database schema for the API Keys table.
 *
 * @package GeoDirectory\Database\Schema
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

final class ApiKeySchema extends AbstractTableSchema {

	/**
	 * @inheritdoc
	 */
	protected function get_filter_key(): string {
		return 'api_keys';
	}

	/**
	 * @inheritdoc
	 */
	public function get_primary_key(): string {
		return 'key_id';
	}

	/**
	 * @inheritdoc
	 */
	protected function define_schema(): array {
		return [
			'key_id'          => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL AUTO_INCREMENT', 'format' => '%d', 'default' => 0, 'ui_key' => 'id', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'user_id'         => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'description'     => [ 'type' => 'varchar(200)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'ui_sanitize' => 'sanitize_text_field', 'db_sanitize' => 'sanitize_text_field' ],
			'permissions'     => [ 'type' => 'varchar(10)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_key', 'db_sanitize' => 'sanitize_key' ],
			'consumer_key'    => [ 'type' => 'char(64)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '' ],
			'consumer_secret' => [ 'type' => 'char(43)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '' ],
			'nonces'          => [ 'type' => 'longtext', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'truncated_key'   => [ 'type' => 'char(7)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '' ],
			'last_access'     => [ 'type' => 'datetime', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
		];
	}
}

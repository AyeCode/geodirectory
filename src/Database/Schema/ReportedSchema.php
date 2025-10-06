<?php
/**
 * Defines the database schema for the Reposted Posts table.
 *
 * @package GeoDirectory\Database\Schema
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

final class ReportedSchema extends AbstractTableSchema {


	/**
	 * @inheritdoc
	 */
	protected function get_filter_key(): string {
		return 'reported_posts';
	}

	/**
	 * @inheritdoc
	 */
	public function get_primary_key(): string {
		return 'id';
	}

	/**
	 * @inheritDoc
	 */
	protected function define_schema(): array {
		return [
			'id'           => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL AUTO_INCREMENT', 'format' => '%d', 'default' => 0 ],
			'post_id'      => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'user_id'      => [ 'type' => 'bigint(20) UNSIGNED', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0, 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'user_ip'      => [ 'type' => 'varchar(200)', 'extra' => 'DEFAULT NULL', 'format' => '%s', 'default' => null, 'ui_sanitize' => 'sanitize_text_field', 'db_sanitize' => 'sanitize_text_field' ],
			'user_name'    => [ 'type' => 'varchar(100)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_text_field', 'db_sanitize' => 'sanitize_text_field' ],
			'user_email'   => [ 'type' => 'varchar(100)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_email', 'db_sanitize' => 'sanitize_email' ],
			'reason'       => [ 'type' => 'varchar(200)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_text_field', 'db_sanitize' => 'sanitize_text_field' ],
			'message'      => [ 'type' => 'text', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_textarea_field', 'db_sanitize' => 'sanitize_textarea_field' ],
			'status'       => [ 'type' => 'varchar(50)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_sanitize' => 'sanitize_key', 'db_sanitize' => 'sanitize_key' ],
			'report_date'  => [ 'type' => 'datetime', 'extra' => 'DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'updated_date' => [ 'type' => 'datetime', 'extra' => 'DEFAULT NULL', 'format' => '%s', 'default' => null ],
		];
	}

}

<?php
/**
 * Defines the database schema for the Tabs Layout table.
 *
 * This class provides the single source of truth for the structure of the
 * 'geodir_tabs_layout' table, including UI mapping hints for the DataMapper.
 *
 * @package GeoDirectory\Database\Schema
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

final class TabSchema extends AbstractTableSchema {

	/**
	 * @inheritdoc
	 */
	protected function get_filter_key(): string {
		return 'tabs_layout';
	}

	/**
	 * @inheritdoc
	 */
	protected function define_schema(): array {
		return [
			// DB Column => [ DB Properties..., Optional UI Hints... ]
			'id'          => [
				'type' => 'int(11)', 'extra' => 'NOT NULL AUTO_INCREMENT', 'format' => '%d', 'default' => 0,
				'ui_key' => '_uid', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint'
			],
			'tab_parent'  => [
				'type' => 'varchar(100)', 'extra' => "NOT NULL DEFAULT ''", 'format' => '%s', 'default' => '',
				'ui_key' => '_parent_id', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint'
			],
			'tab_name'    => [
				'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '',
				'ui_key' => 'label' // Sanitizers will use the smart generator's defaults.
			],
			'tab_icon'    => [
				'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '',
				'ui_key' => 'icon'
			],
			'tab_key'     => [
				'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '',
				'ui_key' => 'type', 'db_sanitize' => 'sanitize_key'
			],
			'tab_type'    => [
				'type' => 'varchar(100)', 'extra' => "NOT NULL DEFAULT 'standard'", 'format' => '%s', 'default' => 'standard',
				'db_sanitize' => 'sanitize_key' // ui_key will default to 'tab_type'.
			],
			'tab_content' => [
				'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null,
				'db_sanitize' => 'wp_kses_post'
			],

			// --- Columns without specific UI mappings ---
			// The DataMapper will use sensible defaults for these.
			'post_type'   => [ 'type' => 'varchar(100)', 'extra' => 'NULL', 'format' => '%s', 'default' => '' ],
			'sort_order'  => [ 'type' => 'int(11)', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0 ],
			'tab_layout'  => [ 'type' => 'varchar(100)', 'extra' => "NOT NULL DEFAULT 'post'", 'format' => '%s', 'default' => 'post' ],
			'tab_level'   => [ 'type' => 'int(11)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0 ],
		];
	}
}

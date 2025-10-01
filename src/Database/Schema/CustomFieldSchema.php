<?php
/**
 * Defines the database schema for the Custom Fields table.
 *
 * This class provides the single source of truth for the structure of the
 * 'geodir_custom_fields' table, including UI mapping hints for the DataMapper.
 *
 * @package GeoDirectory\Database\Schema
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

final class CustomFieldSchema extends AbstractTableSchema {

	/**
	 * @inheritdoc
	 */
	protected function get_filter_key(): string {
		return 'custom_fields';
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
			'id'                 => [ 'type' => 'int(11)', 'extra' => 'NOT NULL AUTO_INCREMENT', 'format' => '%d', 'default' => 0, 'ui_key' => '_uid', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
			'is_default'         => [ 'type' => 'tinyint(1)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0, 'ui_key' => '_is_default', 'ui_sanitize' => 'absint', 'db_sanitize' => 'absint' ],
//			'field_type_key'     => [ 'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_key' => 'type', 'db_sanitize' => 'sanitize_key' ],
			'field_type_key'     => [ 'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'db_sanitize' => 'sanitize_key' ],
			'admin_title'        => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'ui_key' => 'label' ],
			'htmlvar_name'       => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'db_sanitize' => 'sanitize_key' ],
			'field_icon'         => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'ui_key' => 'icon' ],
			'show_in'            => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'to_db_transform' => 'array_to_string', 'from_db_transform' => 'string_to_array' ],
//			'is_price'           => [ 'type' => 'tinyint(1)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0, 'db_sanitize' => 'absint' ], // This is a flag, not a price value
			'is_active'          => [ 'type' => 'tinyint(1)', 'extra' => 'NOT NULL DEFAULT 1', 'format' => '%d', 'default' => 1, 'db_sanitize' => 'absint' ],
			'is_required'        => [ 'type' => 'tinyint(1)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0, 'db_sanitize' => 'absint' ],
			'for_admin_use'      => [ 'type' => 'tinyint(1)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0, 'db_sanitize' => 'absint' ],
			'option_values'      => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null, 'ui_sanitize' => 'esc_textarea', 'db_sanitize' => 'sanitize_textarea_field' ],
			'cat_sort'           => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%d', 'default' => null, 'db_sanitize' => 'absint' ],

//			'tab_parent'         => [ 'type' => 'varchar(100)', 'extra' => "NOT NULL DEFAULT '0'", 'format' => '%s', 'default' => '0', 'ui_key' => '_parent_id', 'db_sanitize' => 'absint' ],
			'tab_parent'         => [ 'type' => 'int(11)', 'extra' => "NOT NULL DEFAULT 0", 'format' => '%d', 'default' => 0, 'ui_key' => '_parent_id', 'db_sanitize' => 'absint' ],

			// --- Columns without specific UI mappings or with default behaviour ---
			'post_type'          => [ 'type' => 'varchar(100)', 'extra' => 'NULL', 'format' => '%s', 'default' => '' ],
			'data_type'          => [ 'type' => 'varchar(100)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
//			'field_type'         => [ 'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '' ],
			'field_type'         => [ 'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '', 'ui_key' => 'type' ],
			'frontend_desc'      => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'frontend_title'     => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'default_value'      => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'placeholder_value'  => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'sort_order'         => [ 'type' => 'int(11)', 'extra' => 'NOT NULL', 'format' => '%d', 'default' => 0 ],
			'tab_level'          => [ 'type' => 'int(11)', 'extra' => 'NOT NULL DEFAULT 0', 'format' => '%d', 'default' => 0 ],
			'clabels'            => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'required_msg'       => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'packages'           => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'cat_filter'         => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'css_class'          => [ 'type' => 'varchar(255)', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
			'decimal_point'      => [ 'type' => 'varchar(10)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '.' ],
			'validation_pattern' => [ 'type' => 'varchar(255)', 'extra' => 'NOT NULL', 'format' => '%s', 'default' => '' ],
			'validation_msg'     => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],

			// This is the parent column for packed data. It has no direct UI mapping itself.
			'extra_fields'       => [ 'type' => 'text', 'extra' => 'NULL DEFAULT NULL', 'format' => '%s', 'default' => null ],
		];
	}
}

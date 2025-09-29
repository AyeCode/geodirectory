<?php
/**
 * Abstract Base Class for Database Table Schemas
 *
 * Provides a consistent, DRY (Don't Repeat Yourself) way to define and interact
 * with the structure of custom database tables. Child classes must define the
 * actual schema and a unique filter key.
 *
 * @package GeoDirectory\Database\Schema
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Schema;

abstract class AbstractTableSchema {

    /**
     * The final, filtered schema array for the table.
     * @var array
     */
    protected $_schema;

    /**
     * Returns the unique key used to build the schema's filter name.
     * For example, returning 'custom_fields' creates the filter 'geodirectory_custom_fields_schema'.
     *
     * @return string
     */
    abstract protected function get_filter_key(): string;

    /**
     * Defines the array of columns and their properties for the table.
     *
     * @return array
     */
    abstract protected function define_schema(): array;

    /**
     * The constructor initializes the schema and makes it filterable.
     */
    public function __construct() {
        $schema      = $this->define_schema();
        $filter_name = 'geodirectory_' . $this->get_filter_key() . '_schema';

        $this->_schema = apply_filters( $filter_name, $schema );
    }

    /**
     * Gets the full schema array.
     * @return array
     */
    public function get_schema(): array {
        return $this->_schema;
    }

    /**
     * Gets a simple array of all column names.
     * @return array
     */
    public function get_column_names(): array {
        return array_keys( $this->_schema );
    }

    /**
     * Gets an associative array of [column_name => default_value].
     * @return array
     */
    public function get_defaults(): array {
        return wp_list_pluck( $this->_schema, 'default' );
    }

    /**
     * Gets an associative array of [column_name => wpdb_format].
     * @return array
     */
    public function get_formats(): array {
        return wp_list_pluck( $this->_schema, 'format' );
    }

    /**
     * Programmatically builds the full "CREATE TABLE" SQL statement.
     *
     * @param string $table_name The full, prefixed name of the table.
     * @return string The complete SQL query.
     */
    public function get_create_table_sql( string $table_name ): string {
        global $wpdb;

        $collate     = $wpdb->get_charset_collate();
        $columns_sql = [];

        foreach ( $this->_schema as $name => $props ) {
            $columns_sql[] = "`{$name}` {$props['type']} {$props['extra']}";
        }

        // We assume 'id' is always the primary key.
        // This could be made more flexible if needed later.
        $columns_sql[] = "PRIMARY KEY  (id)";

        $sql = "CREATE TABLE {$table_name} (\n" . implode( ",\n    ", $columns_sql ) . "\n) $collate;";

        return $sql;
    }
}

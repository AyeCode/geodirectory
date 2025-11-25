# CPT Table Schema Management

## Overview

The custom field system now properly manages CPT-specific table columns (e.g., `wp_geodir_gd_place_detail`, `wp_geodir_gd_event_detail`) when custom fields are added, updated, or deleted.

## Architecture

### PostRepository (src/Database/Repository/PostRepository.php)

The `PostRepository` is responsible for managing CPT table schemas because it handles the actual post data storage and operations.

#### Public Methods

1. **`get_table_name(string $post_type): string|false`**
   - Gets the CPT-specific table name
   - Uses `geodir_db_cpt_table()` function
   - Example: `'gd_place'` → `'wp_geodir_gd_place_detail'`

2. **`table_exists(string $post_type): bool`**
   - Checks if a CPT table exists in the database

3. **`add_column(string $post_type, array $field_data): bool`**
   - Adds a new column to the CPT table based on field definition
   - Hooks: `geodir_before_add_custom_field_column`, `geodir_after_add_custom_field_column`
   - Filter: `geodir_post_repo_add_column_definition`
   - Automatically skips if column already exists

4. **`remove_column(string $post_type, string $column_name): bool`**
   - Removes a column from the CPT table
   - Hooks: `geodir_before_remove_custom_field_column`, `geodir_after_remove_custom_field_column`
   - Automatically skips if column doesn't exist

5. **`update_column(string $post_type, array $field_data): bool`**
   - Modifies an existing column (useful when data_type changes)
   - Hooks: `geodir_before_update_custom_field_column`, `geodir_after_update_custom_field_column`
   - Filter: `geodir_post_repo_update_column_definition`

#### Protected Methods

6. **`get_column_definition(array $field_data): string`**
   - Maps field types and data types to MySQL column definitions
   - Handles: text, textarea, html, datepicker, checkbox, select, email, phone, file, taxonomy
   - Supports `data_type` overrides: DECIMAL, FLOAT, INT
   - Filter: `geodir_post_repo_column_definition`

**Supported Field Type → Column Type Mappings:**

| Field Type | Data Type | MySQL Column Definition |
|------------|-----------|------------------------|
| text | - | VARCHAR(254) NULL |
| text | INT | BIGINT(20) |
| text | DECIMAL/FLOAT | DECIMAL(14+dp, dp) |
| textarea | - | TEXT NULL |
| datepicker | - | DATE DEFAULT NULL |
| checkbox | - | TINYINT(1) DEFAULT 0 |
| select/radio | - | VARCHAR(254) NULL |
| file/taxonomy | - | TEXT NULL |
| fieldset/categories | - | (no column created) |

### CustomFieldRepository (src/Database/Repository/CustomFieldRepository.php)

The `CustomFieldRepository` manages custom field definitions and orchestrates column operations.

#### Constructor Changes

```php
public function __construct( PostRepository $post_repository = null )
```

- Now accepts optional `PostRepository` dependency injection
- Falls back to creating new instance if not provided

#### sync_by_post_type() Changes

**On INSERT (new field):**
```php
// After inserting field definition
$this->post_repository->add_column( $post_type, $data_to_save );
```

**On UPDATE (existing field):**
```php
// After updating field definition
$this->post_repository->update_column( $post_type, $data_to_save );
```

**On DELETE (removed fields):**
```php
// Fetch all fields to be deleted in one query
$fields_to_delete = $this->db->get_results(
    "SELECT id, htmlvar_name, field_type FROM {$this->table_name} WHERE id IN (...)"
);

// Loop through each and remove column first, then delete field
foreach ( $fields_to_delete as $field ) {
    $this->post_repository->remove_column( $post_type, $field['htmlvar_name'] );
    $this->delete_field( $field['id'], null, false );
}
```

#### Method Removals

- **Removed:** `delete_fields_by_ids()` - Batch deletion can't handle per-field column operations

#### Method Updates

**`delete_field()` signature:**
```php
public function delete_field(
    int $field_id,
    string $post_type = null,
    bool $remove_column = true
): bool
```

- Can now optionally handle column removal when called standalone
- When called from `sync_by_post_type()`, passes `false` to avoid duplicate removal

## Usage Examples

### Adding a Custom Field
```php
$repository = new CustomFieldRepository();

// This will automatically create the column in the CPT table
$repository->sync_by_post_type( 'gd_place', [
    [
        'htmlvar_name' => 'business_hours',
        'field_type' => 'text',
        'admin_title' => 'Business Hours',
        'is_active' => 1
    ]
]);
```

### Deleting a Custom Field
```php
// Option 1: Delete with automatic column removal
$repository->delete_field( 123, 'gd_place' );

// Option 2: Delete without column removal (if you handle it separately)
$repository->delete_field( 123, null, false );
```

### Updating Field Data Type
```php
// Changing a text field to INT will update the column definition
$repository->sync_by_post_type( 'gd_place', [
    [
        'id' => 123,
        'htmlvar_name' => 'price',
        'field_type' => 'text',
        'data_type' => 'INT', // Changed from VARCHAR to BIGINT
        'is_active' => 1
    ]
]);
```

## Extensibility

### Hooks for Extensions

Extensions can hook into column operations:

```php
// Before adding a column
add_action( 'geodir_before_add_custom_field_column', function( $table_name, $column_name, $field_data, $post_type ) {
    // Your code here
}, 10, 4 );

// After adding a column
add_action( 'geodir_after_add_custom_field_column', function( $success, $table_name, $column_name, $field_data, $post_type ) {
    // Your code here
}, 10, 5 );

// Similar hooks exist for remove and update operations
```

### Filters for Column Definitions

```php
// Modify column definition for all fields
add_filter( 'geodir_post_repo_column_definition', function( $definition, $field_data ) {
    // Modify $definition
    return $definition;
}, 10, 2 );

// Modify column definition before adding
add_filter( 'geodir_post_repo_add_column_definition', function( $definition, $field_data, $post_type ) {
    // Modify $definition
    return $definition;
}, 10, 3 );
```

## Benefits

1. **Single Responsibility:** `PostRepository` handles table schema, `CustomFieldRepository` handles field definitions
2. **Atomic Operations:** Column added/removed at the same time as field definition
3. **Extensibility:** Hooks and filters for extensions to customize behavior
4. **Consistency:** All CPT tables (gd_place, gd_event, etc.) managed the same way
5. **Safety:** Checks for column existence before adding/removing
6. **Efficiency:** Batch fetches fields to delete, then loops for column operations

## Future Enhancements

1. Add `create_table()` method to `PostRepository` for table installation
2. Consider migration/rollback mechanisms for schema changes
3. Add schema versioning for upgrade paths
4. Implement column rename support for when `htmlvar_name` changes

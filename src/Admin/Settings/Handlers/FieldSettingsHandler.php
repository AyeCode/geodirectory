<?php
/**
 * Handles persistence for the custom fields settings builder.
 *
 * This handler is driven by the CustomFieldSchema, which provides all the necessary
 * rules for mapping data between the UI and the database. It also defines special
 * rules for "packed" data stored in serialized columns like 'extra_fields'.
 *
 * @package GeoDirectory\Admin\Settings\Handlers
 */

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Admin\Utils\DataMapper;
use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;
use AyeCode\GeoDirectory\Database\Schema\CustomFieldSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FieldSettingsHandler implements PersistenceHandlerInterface {

	/** @var CustomFieldRepository */
	private $repository;

	/** @var DataMapper */
	private $mapper;

	public function __construct() {
		$this->repository = new CustomFieldRepository();
		$this->mapper     = new DataMapper( $this->get_data_map() );
	}

	/**
	 * Generates a smart data map for all columns defined in the schema.
	 *
	 * This method reads the schema to build the main mapping, then merges in
	 * special rules for packed/serialized data fields.
	 *
	 * @return array
	 */
	private function get_data_map(): array {
		$schema            = new CustomFieldSchema();
		$schema_definition = $schema->get_schema();
		$base_map          = [];

		// Unset any special fields that are re-built later
		unset( $schema_definition['extra_fields'] );

		foreach ( $schema_definition as $db_column => $props ) {
			// Read the UI hints from the schema, providing sensible fallbacks for each.
			$ui_key            = $props['ui_key'] ?? $db_column;
			$ui_sanitize       = $props['ui_sanitize'] ?? 'esc_attr';
			$db_sanitize       = $props['db_sanitize'] ?? 'sanitize_text_field';
			$to_db_transform   = $props['to_db_transform'] ?? null;
			$from_db_transform = $props['from_db_transform'] ?? null;

			// Build the map entry in the exact order the DataMapper expects.
			$base_map[ $db_column ] = [ $ui_key, $ui_sanitize, $db_sanitize, $to_db_transform, $from_db_transform ];
		}

		// Define the special rules for fields packed inside the 'extra_fields' column.
		// The DataMapper knows how to handle the "dot.notation" keys.
		$packed_field_map = [
			'extra_fields.is_price'                  => [ 'is_price', 'absint', 'absint' ],
			'extra_fields.currency_symbol'           => [ 'currency_symbol', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.currency_symbol_placement' => [
				'currency_symbol_placement',
				'esc_attr',
				'sanitize_text_field'
			],
			'extra_fields.thousand_separator'        => [ 'thousand_separator', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.decimal_separator'         => [ 'decimal_separator', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.decimal_display'           => [ 'decimal_display', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.advanced_editor'           => [ 'advanced_editor', 'absint', 'absint' ],
			'extra_fields.embed'                     => [ 'embed', 'absint', 'absint' ],
			'extra_fields.conditions'                => [
				'conditions',
				'pass_through',
				'pass_through',
				'transform_conditions',
				null
			],


			// taxonomy stuff
			'extra_fields.cat_display_type'          => [ 'cat_display_type', 'esc_attr', 'sanitize_text_field' ],

			// Address stuff
			'extra_fields.show_street2'              => [ 'show_street2', 'absint', 'absint' ],
			'extra_fields.street2_lable'             => [ 'street2_lable', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.show_zip'                  => [ 'show_zip', 'absint', 'absint' ],
			'extra_fields.zip_lable'                 => [ 'zip_lable', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.show_mapview'              => [ 'show_mapview', 'absint', 'absint' ],
			'extra_fields.mapview_lable'             => [ 'mapview_lable', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.show_mapzoom'              => [ 'show_mapzoom', 'absint', 'absint' ],
			'extra_fields.show_latlng'               => [ 'show_latlng', 'absint', 'absint' ],
			'extra_fields.zip_required'              => [ 'zip_required', 'absint', 'absint' ],

			// Tags
			'extra_fields.disable_new_tags'          => [ 'disable_new_tags', 'absint', 'absint' ],
			'extra_fields.spellcheck'                => [ 'spellcheck', 'absint', 'absint' ],
			'extra_fields.no_of_tag'                 => [ 'no_of_tag', 'absint', 'absint' ],


			// --- Fields without specific UI mappings or with default behaviour ---
//			'tab_parent'                => [ 'date_range', 'absint', 'absint' ],
		];

		// Merge the two maps. The packed field rules are added to the base map.
		return array_merge( $base_map, $packed_field_map );
	}

	/**
	 * Retrieves custom field settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array The settings, formatted for the UI.
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );

//		print_r( $raw_data );exit;

//		print_r($this->mapper->transform( $raw_data, 'to_db' ));exit;
//		print_r($this->mapper->transform( $raw_data, 'to_ui' ));exit;
		return $this->mapper->transform( $raw_data, 'to_ui' );
	}

	/**
	 * Saves custom field settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $data_from_ui The settings data from the UI.
	 *
	 * @return bool Result of the save operation.
	 */
	public function save( string $post_type, array $data_from_ui ): bool {

//		print_r($data_from_ui);exit;
		$data_for_db = $this->mapper->transform( $data_from_ui, 'to_db' );

//		print_r( $data_for_db );
//		exit;

		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}
}

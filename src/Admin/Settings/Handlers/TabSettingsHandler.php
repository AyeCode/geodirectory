<?php
/**
 * Handles persistence for the detail page tabs builder.
 *
 * This handler is driven by the TabSchema, which provides all the necessary
 * rules for mapping data between the UI and the database.
 *
 * @package GeoDirectory\Admin\Settings\Handlers
 */

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Admin\Utils\DataMapper;
use AyeCode\GeoDirectory\Database\Repository\TabRepository;
use AyeCode\GeoDirectory\Database\Schema\TabSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class TabSettingsHandler implements PersistenceHandlerInterface {

	/** @var TabRepository */
	private $repository;

	/** @var DataMapper */
	private $mapper;

	public function __construct() {
		$this->repository = new TabRepository();
		$this->mapper     = new DataMapper( $this->get_data_map() );
	}

	/**
	 * Generates a smart data map for all columns defined in the schema.
	 *
	 * This method reads the schema, including optional UI hints, to build
	 * the definitive mapping between the database and the UI.
	 *
	 * @return array
	 */
	private function get_data_map(): array {
		$schema            = new TabSchema();
		$schema_definition = $schema->get_schema();
		$final_map         = [];

		foreach ( $schema_definition as $db_column => $props ) {
			// Read the UI hints from the schema, providing sensible fallbacks for each.
			$ui_key            = $props['ui_key'] ?? $db_column;
			$ui_sanitize       = $props['ui_sanitize'] ?? 'esc_attr';
			$db_sanitize       = $props['db_sanitize'] ?? 'sanitize_text_field';
			$to_db_transform   = $props['to_db_transform'] ?? null;
			$from_db_transform = $props['from_db_transform'] ?? null;

			// Build the map entry in the exact order the DataMapper expects.
			$final_map[ $db_column ] = [
				$ui_key,            // Index 0: Key used in UI data arrays.
				$ui_sanitize,       // Index 1: Sanitize when sending TO UI.
				$db_sanitize,       // Index 2: Sanitize when sending TO DB.
				$to_db_transform,   // Index 3: Transform when sending TO DB.
				$from_db_transform, // Index 4: Transform when sending FROM DB.
			];
		}

		return $final_map;
	}

	/**
	 * Retrieves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @return array The tab settings, formatted for the UI.
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );
		return $this->mapper->transform( $raw_data, 'to_ui' );
	}

	/**
	 * Saves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array  $data_from_ui The settings data from the UI.
	 * @return bool Result of the save operation.
	 */
	public function save( string $post_type, array $data_from_ui ): bool {
		$data_for_db = $this->mapper->transform( $data_from_ui, 'to_db' );
		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}
}

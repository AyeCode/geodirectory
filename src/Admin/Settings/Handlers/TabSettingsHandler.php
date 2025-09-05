<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Database\TabRepository;
use AyeCode\GeoDirectory\Admin\Utils\DataMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles persistence for the detail page tabs builder.
 */
class TabSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * @var TabRepository
	 */
	private $repository;

	/**
	 * @var DataMapper
	 */
	private $mapper;

	public function __construct() {
		$this->repository = new TabRepository();
		// Instantiate the mapper with this handler's specific data map.
		$this->mapper = new DataMapper( $this->get_data_map() );
	}

	/**
	 * Defines the mapping between the tabs database table and the UI fields.
	 * This is the single source of truth for the tab data structure.
	 *
	 * @return array
	 */
	private function get_data_map(): array {
		return [
			// DB Column   => [ UI Key,       Sanitize for UI,  Sanitize for DB ]
			'id'           => [ '_uid',         'absint', 'absint' ],
			'tab_parent'   => [ '_parent_id',   'absint', 'absint' ],
			'tab_name'     => [ 'label',        'esc_attr', 'sanitize_text_field' ],
			'tab_icon'     => [ 'icon',         'esc_attr', 'sanitize_text_field' ],
			'tab_key'      => [ 'type',         'esc_attr', 'sanitize_key' ],
			'tab_type'     => [ 'tab_type',     'esc_attr', 'sanitize_key' ],
			'tab_content'  => [ 'tab_content',  'esc_attr', 'wp_kses_post' ],
		];
	}

	/**
	 * Retrieves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @return array The tab settings, formatted for the UI.
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );
		// Delegate the transformation to the mapper.
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
		// Delegate the transformation to the mapper.
		$data_for_db = $this->mapper->transform( $data_from_ui, 'to_db' );
		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}
}

<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Database\SortRepository;
use AyeCode\GeoDirectory\Admin\Utils\DataMapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles persistence for the CPT sorting options builder.
 */
class SortingSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * @var SortRepository
	 */
	private $repository;

	/**
	 * @var DataMapper
	 */
	private $mapper;

	public function __construct() {
		$this->repository = new SortRepository();
		$this->mapper = new DataMapper( $this->get_data_map() );
	}

	/**
	 * Defines the mapping between database columns and UI fields.
	 * This is the single source of truth for this handler's data structure.
	 *
	 * @return array
	 */
	private function get_data_map(): array {
		return [
			// DB Column      => [ UI Key,       Sanitize for UI, Sanitize for DB ]
			'id'             => [ '_uid',         'absint', 'absint' ],
			'tab_parent'     => [ '_parent_id',   'absint', 'absint' ],
			'frontend_title' => [ 'label',        'esc_attr', 'sanitize_text_field' ],
			'htmlvar_name'   => [ 'type',         'esc_attr', 'sanitize_key' ],
			'is_active'      => [ 'is_active',    'absint', 'absint' ],
			'sort'           => [ 'sort',         'esc_attr', 'sanitize_text_field' ],
			'data_type'      => [ 'data_type',    'esc_attr', 'sanitize_text_field' ],
			'field_type'     => [ 'field_type',   'esc_attr', 'sanitize_text_field' ],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );
		return $this->mapper->transform( $raw_data, 'to_ui' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( string $post_type, array $data_from_ui ): bool {
		$data_for_db = $this->mapper->transform( $data_from_ui, 'to_db' );
		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}
}

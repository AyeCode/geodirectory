<?php

namespace AyeCode\GeoDirectory\Admin\Settings\Handlers;

use AyeCode\GeoDirectory\Admin\Settings\PersistenceHandlerInterface;
use AyeCode\GeoDirectory\Admin\Utils\DataMapper;
use AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles persistence for the detail page tabs builder.
 */
class FieldSettingsHandler implements PersistenceHandlerInterface {

	/**
	 * @var TabRepository
	 */
	private $repository;

	/**
	 * @var DataMapper
	 */
	private $mapper;

	public function __construct() {
		$this->repository = new CustomFieldRepository();
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
			// DB Column   => [ UI Key,       Sanitize for UI,  Sanitize for DB,      Transform DB,      Transform UI  ]
			'id'             => [ '_uid', 'absint', 'absint' ],
			'is_default'             => [ '_is_default', 'absint', 'absint' ],

			'field_type'     => [ 'field_type', 'esc_attr', 'sanitize_text_field' ],
			'field_type_key' => [ 'type', 'esc_attr', 'sanitize_key' ],
			// type
			'admin_title'    => [ 'label', 'esc_attr', 'sanitize_text_field' ],

			'frontend_title' => [ 'frontend_title', 'esc_attr', 'sanitize_text_field' ],
			'htmlvar_name'   => [ 'htmlvar_name', 'esc_attr', 'sanitize_key' ],

			// type

			'frontend_desc' => [ 'frontend_desc', 'esc_attr', 'sanitize_text_field' ],
			'placeholder_value' => [ 'placeholder_value', 'esc_attr', 'sanitize_text_field' ],
			'field_icon' => [ 'icon', 'esc_attr', 'sanitize_text_field' ],
			'css_class' => [ 'css_class', 'esc_attr', 'sanitize_text_field' ],
			'show_in' => [
				'show_in',
				'esc_attr',
				'sanitize_text_field',
				'array_to_string',
				'string_to_array'
			],
			'data_type'      => [
				'data_type',
				'esc_attr',
				'sanitize_text_field'
			],
			'is_price' => [ 'is_price', 'absint', 'absint' ],
			'extra_fields.currency_symbol' => [ 'currency_symbol', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.currency_symbol_placement' => [ 'currency_symbol', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.thousand_separator' => [ 'thousand_separator', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.decimal_separator' => [ 'decimal_separator', 'esc_attr', 'sanitize_text_field' ],
			'extra_fields.decimal_display' => [ 'decimal_display', 'esc_attr', 'sanitize_text_field' ],

			// behaviour
			'is_active' => [ 'is_active', 'absint', 'absint' ],
			'is_required' => [ 'is_required', 'absint', 'absint' ],
			'for_admin_use' => [ 'for_admin_use', 'absint', 'absint' ],
			'option_values' => [ 'option_values', 'esc_textarea', 'sanitize_textarea_field' ],
			'default_value' => [ 'default_value', 'esc_attr', 'sanitize_text_field' ],
			'db_default' => [ 'db_default', 'esc_attr', 'sanitize_text_field' ],
			//@todo this does not exists, will validation still work
			'required_msg' => [ 'required_msg', 'esc_attr', 'sanitize_text_field' ],
			'cat_sort' => [ 'cat_sort', 'absint', 'absint' ],
			'extra_fields.advanced_editor' => [ 'advanced_editor', 'absint', 'absint' ],
			'extra_fields.embed' => [ 'embed', 'absint', 'absint' ],

			// validation
			'validation_pattern' => [ 'validation_pattern', 'esc_attr', 'sanitize_text_field' ],
			//@todo this will be kinda regex, we need to find the best validation functions
			'validation_msg' => [ 'validation_msg', 'esc_attr', 'sanitize_text_field' ],

			'extra_fields.conditions' => [
				'conditions',
				'pass_through',           // from_db sanitize: none needed, it's an array
				'pass_through',           // to_db sanitize: the transformer will handle it
				null,                     // from_db transform: none needed
				'transform_conditions'    // to_db transform: use our new custom method
			],


			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			''           => [ '', 'esc_attr', 'sanitize_text_field' ],
			'tab_parent' => [ '_parent_id', 'absint', 'absint' ],
//			'tab_type'     => [ 'tab_type',     'esc_attr', 'sanitize_key' ],
//			'tab_content'  => [ 'tab_content',  'esc_attr', 'wp_kses_post' ],
		];
	}

	/**
	 * Retrieves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return array The tab settings, formatted for the UI.
	 */
	public function get( string $post_type ): array {
		$raw_data = $this->repository->get_by_post_type( $post_type );

//		$stuff =  $this->mapper->transform( $raw_data, 'to_ui' );
////		$stuff[3]['show_in'] = array( '[mapbubble]');
//		print_r( $stuff );exit;
//		return $stuff;
//		print_r( $this->mapper->transform( $raw_data, 'to_ui' ) );
//		print_r( $raw_data );exit;
		// Delegate the transformation to the mapper.
		return $this->mapper->transform( $raw_data, 'to_ui' );
	}

	/**
	 * Saves tab settings for a CPT.
	 *
	 * @param string $post_type The post type slug.
	 * @param array $data_from_ui The settings data from the UI.
	 *
	 * @return bool Result of the save operation.
	 */
	public function save( string $post_type, array $data_from_ui ): bool {

//		print_r( $data_from_ui );
//		exit;

		// Delegate the transformation to the mapper.
		$data_for_db = $this->mapper->transform( $data_from_ui, 'to_db' );


		print_r( $data_for_db );
		exit;

		return $this->repository->sync_by_post_type( $post_type, $data_for_db );
	}
}

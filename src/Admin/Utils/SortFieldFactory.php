<?php
/**
 * Tab Field Factory
 *
 * Provides a static method to build the configuration fields for a Tab item
 * in the drag-and-drop Tab Builder UI.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class SortFieldFactory {

	/**
	 * The master library of all possible sub-field components for the Tab Builder.
	 *
	 * This defines the configuration for every possible setting a tab can have.
	 *
	 * @return array
	 */
	private static function get_master_library(): array {
		return [
			// Core Properties
			'name'    => FormFields::text( [ 'id' => 'label', 'name' => 'frontend_title', 'label' => __( 'Name', 'geodirectory' ), 'placeholder' => __( 'Name', 'geodirectory' ) ] ),
			'icon'    => FormFields::font_awesome( [ 'id' => 'icon', 'name' => 'tab_icon', 'label' => __( 'Icon', 'geodirectory' ) ] ),
			'uid' => FormFields::hidden( [ 'id' => '_uid', 'name' => 'id' ] ),
			'parent_id' => FormFields::hidden( [ 'id' => '_parent_id', 'name' => 'tab_parent' ] ),
			'type'    => FormFields::hidden( [ 'id' => 'type', 'name' => 'type', 'label' => __( 'Key', 'geodirectory' )] ),


			// fields
			'post_type' => FormFields::hidden( [ 'id' => 'post_type', 'name' => 'post_type' ] ),
			'data_type' => FormFields::hidden( [ 'id' => 'data_type', 'name' => 'data_type' ] ),
			'field_type' => FormFields::hidden( [ 'id' => 'field_type', 'name' => 'field_type' ] ),
			'sort' => FormFields::select( [ 'id' => 'sort', 'name' => 'sort', 'label' => __( 'Ascending or Descending', 'geodirectory' ),  'options' => [ 'asc' => 'Ascending', 'desc' => 'Descending' ], 'default' => 'asc' ] ),

			'is_active' => FormFields::toggle( [ 'id' => 'is_active', 'name' => 'is_active', 'label' => __( 'Active', 'geodirectory' ), 'default' => true] ),
		];
	}

	/**
	 * Builds a complete 'fields' array from a set of instructions.
	 * (This method can be identical to the one in FormFieldFactory)
	 *
	 * @param array $instructions An array of instructions.
	 * @return array The final, assembled 'fields' array.
	 */
	public static function build( array $instructions ): array {
		$master_library = self::get_master_library();
		$final_fields   = [];

		foreach ( $instructions as $key => $instruction ) {
			if ( is_numeric( $key ) ) {
				$field_key = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					$final_fields[] = $master_library[ $field_key ];
				}
			} else {
				$field_key = $key;
				$overrides = $instruction;
				if ( isset( $master_library[ $field_key ] ) ) {
					$final_fields[] = array_merge( $master_library[ $field_key ], $overrides );
				}
			}
		}

		return $final_fields;
	}

}

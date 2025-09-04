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

final class TabFieldFactory {

	/**
	 * The master library of all possible sub-field components for the Tab Builder.
	 *
	 * This defines the configuration for every possible setting a tab can have.
	 *
	 * @return array
	 */
	private static function get_master_library(): array {
		return [
			// Core Tab Properties

			'tab_name'    => FormFields::text( [ 'id' => 'label', 'name' => 'tab_name', 'label' => __( 'Name', 'geodirectory' ), 'placeholder' => __( 'Tab Name', 'geodirectory' ) ] ),
			'tab_icon'    => FormFields::font_awesome( [ 'id' => 'icon', 'name' => 'tab_icon', 'label' => __( 'Icon', 'geodirectory' ) ] ),

			'tab_content' => FormFields::textarea( [
				'id' => 'tab_content',
				'name' => 'tab_content',
				'label' => __( 'Content', 'geodirectory' ),
				'description' => __( 'Enter the content for this tab. Shortcodes are supported.', 'geodirectory' ),
				'show_if' => '[%tab_type%] == "shortcode"' // Only show if tab_type is 'shortcode' @todo not working
			] ),

			'uid' => FormFields::hidden( [ 'id' => '_uid', 'name' => 'id' ] ),
			'parent_id' => FormFields::hidden( [ 'id' => '_parent_id', 'name' => 'tab_parent' ] ),
			'post_type' => FormFields::hidden( [ 'id' => 'post_type', 'name' => 'post_type' ] ),
			'tab_layout' => FormFields::hidden( [ 'id' => 'tab_layout', 'name' => 'tab_layout' ] ),
			'tab_type' => FormFields::hidden( [ 'id' => 'tab_type', 'name' => 'tab_type' ] ),
			'tab_key' => FormFields::hidden( [ 'id' => 'tab_key', 'name' => 'tab_key' ] ),
//			'uid' => FormFields::hidden( [ 'id' => '_uid', 'name' => 'id' ] ),
//			'uid' => FormFields::hidden( [ 'id' => '_uid', 'name' => 'id' ] ),



//			'tab_key'     => FormFields::text( [ 'id' => 'tab_key', 'name' => 'tab_key', 'label' => __( 'Key', 'geodirectory' ), 'description' => __( 'A unique key for this tab.', 'geodirectory' ) ] ),

			// Behavior
			'type'    => FormFields::hidden( [ 'id' => 'type', 'name' => 'type', 'label' => __( 'Key', 'geodirectory' )] ),

			// Content (conditional display)


			// You can add all other tab-specific fields here...
//			'tab_layout' => FormFields::hidden( [ 'id' => 'tab_layout', 'name' => 'tab_layout' ] ),
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

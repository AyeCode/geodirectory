<?php
/**
 * A reusable utility for transforming data between two formats.
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class DataMapper {

	/**
	 * The mapping rules.
	 * @var array
	 */
	private $map;

	/**
	 * @param array $map The data map. Format: [ 'source_key' => [ 'target_key', 'sanitize_callback' ] ]
	 */
	public function __construct( array $map ) {
		$this->map = $map;
	}

	/**
	 * Transforms a set of rows from a source format to a target format.
	 *
	 * @param array $source_data The array of data to transform.
	 * @param string $direction Either 'to_ui' or 'to_db'.
	 * @return array The transformed data array.
	 */
	public function transform( array $source_data, string $direction ): array {
		$transformed = [];
		if ( empty( $source_data ) ) {
			return $transformed;
		}

		// Determine which keys and callback index to use based on direction.
		$source_key_index = ( $direction === 'to_db' ) ? 1 : 0;
		$target_key_index = ( $direction === 'to_db' ) ? 0 : 1;
		$callback_index   = ( $direction === 'to_db' ) ? 2 : 1;

		foreach ( $source_data as $row ) {
			$new_row = [];
			foreach ( $this->map as $map_source_key => $rules ) {
				$map_target_key = $rules[0];
				$callback = $rules[$callback_index];

				// Dynamically select which key to look for in the source row.
				$current_source_key = ( $source_key_index === 0 ) ? $map_source_key : $map_target_key;
				$current_target_key = ( $target_key_index === 0 ) ? $map_source_key : $map_target_key;

				if ( isset( $row[ $current_source_key ] ) ) {
					$new_row[ $current_target_key ] = call_user_func( $callback, $row[ $current_source_key ] );
				}
			}
			$transformed[] = $this->add_special_cases( $new_row, $direction );
		}

		return $transformed;
	}

	/**
	 * Handles any special fields that aren't a direct 1-to-1 map.
	 *
	 * @param array $row The row being transformed.
	 * @param string $direction The direction of transformation.
	 * @return array The modified row.
	 */
	private function add_special_cases( array $row, string $direction ): array {
		if ( $direction === 'to_ui' ) {
			// Create the 'template_id' for the UI, based on the 'type' value.
			if ( isset( $row['type'] ) ) {
				$row['template_id'] = $row['type'];
			}
		}
		return $row;
	}
}

<?php
/**
 * A reusable utility for transforming data between two formats (e.g., DB and UI).
 *
 * This class uses a declarative map to transform data, including handling
 * special cases like value replacements, data type conversions, and serialized
 * "packed" fields using dot notation in the map key (e.g., 'extra_fields.my_field').
 *
 * @package     GeoDirectory\Admin\Utils
 * @since       3.0.0
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Admin\Utils;

final class DataMapper {

	/**
	 * @var array The mapping rules provided on instantiation.
	 */
	private $map;

	/**
	 * A list of DB keys that require the firewall-safe "XVARCHAR" transformation.
	 * @var string[]
	 */
	private static $firewall_fix_keys = [ 'data_type' ];

	/**
	 * @param array $map The data map.
	 */
	public function __construct( array $map ) {
		$this->map = $map;
	}

	/**
	 * Transforms a set of rows from a source format to a target format.
	 *
	 * @param array $source_data The array of data to transform.
	 * @param string $direction Either 'to_ui' or 'to_db'.
	 *
	 * @return array The transformed data array.
	 */
	public function transform( array $source_data, string $direction ): array {
		$transformed = [];
		if ( empty( $source_data ) ) {
			return $transformed;
		}

		$is_to_db = ( $direction === 'to_db' );

		foreach ( $source_data as $row ) {
			$new_row       = [];
			$packed_fields = []; // For storing data destined for serialized columns.

			// Unpack serialized data. For 'to_db', this gets existing data to merge with.
			$unpacked_fields = $this->unpack_serialized_fields( $row );

			foreach ( $this->map as $db_key => $rules ) {
				$ui_key     = $rules[0];
				$source_key = $is_to_db ? $ui_key : $db_key;
				$target_key = $is_to_db ? $db_key : $ui_key;

				$parent_key = null;
				$child_key  = null;

				// Check if this is a rule for a packed field (e.g., 'extra_fields.currency_symbol').
				if ( strpos( $db_key, '.' ) !== false ) {
					list( $parent_key, $child_key ) = explode( '.', $db_key, 2 );
					$source_key = $is_to_db ? $ui_key : $child_key;
				}

				$value = null;
				if ( $is_to_db ) {
					// When going to DB, source is the flat UI row.
					if ( isset( $row[ $source_key ] ) ) {
						$value = $row[ $source_key ];
					}
				} else {
					// When going to UI, check for packed data first, then the main row.
					if ( $parent_key && isset( $unpacked_fields[ $parent_key ][ $child_key ] ) ) {
						$value = $unpacked_fields[ $parent_key ][ $child_key ];
					} elseif ( isset( $row[ $source_key ] ) ) {
						$value = $row[ $source_key ];
					}
				}

				if ( $value !== null ) {
					// Apply firewall fix if the key matches our watchlist.
					if ( in_array( $target_key, self::$firewall_fix_keys, true ) && is_string( $value ) ) {
						$value = $is_to_db ? str_replace( 'XVARCHAR', 'VARCHAR', $value ) : str_replace( 'VARCHAR', 'XVARCHAR', $value );
					}

					// Apply transformations and sanitization.
					$processed_value = $this->process_value( $value, $rules, $direction );

					if ( $is_to_db && $parent_key ) {
						// If going to DB and it's a packed field, store it temporarily.
						$packed_fields[ $parent_key ][ $child_key ] = $processed_value;
					} else {
						// Otherwise, add it to the main row.
						$new_row[ $target_key ] = $processed_value;
					}
				}
			}

			// After processing all rules, serialize and add the packed fields for DB storage.
			if ( $is_to_db && ! empty( $packed_fields ) ) {
				foreach ( $packed_fields as $parent => $children ) {
					// Merge with existing data from the same parent to avoid overwriting other serialized fields.
					$existing_data = $unpacked_fields[ $parent ] ?? [];
					$new_row[ $parent ] = serialize( array_merge( $existing_data, $children ) );
				}
			}

			$transformed[] = $this->add_special_cases( $new_row, $direction );
		}

		return $transformed;
	}

	/**
	 * Finds all potential parent columns and unserializes their content.
	 */
	private function unpack_serialized_fields( array $db_row ): array {
		$unpacked = [];
		foreach ( array_keys( $this->map ) as $db_key ) {
			if ( strpos( $db_key, '.' ) !== false ) {
				$parent_key = strtok( $db_key, '.' );
				// Ensure we only try to unserialize if the key exists and the value is a serialized string.
				if ( ! isset( $unpacked[ $parent_key ] ) && isset( $db_row[ $parent_key ] ) && is_string( $db_row[ $parent_key ] ) && is_serialized( $db_row[ $parent_key ] ) ) {
					$unpacked[ $parent_key ] = unserialize( $db_row[ $parent_key ] );
				}
			}
		}
		return $unpacked;
	}

	/**
	 * Applies shape transformations and sanitization to a single value.
	 */
	private function process_value( $value, array $rules, string $direction ) {
		$is_to_db        = ( $direction === 'to_db' );
		$sanitize_index  = $is_to_db ? 2 : 1;
		$transform_index = $is_to_db ? 3 : 4;

		// Apply data shape transformation (e.g., string <> array).
		$transformer = $rules[ $transform_index ] ?? null;
		if ( $transformer && method_exists( $this, $transformer ) ) {
			$value = $this->$transformer( $value );
		}

		// Apply sanitization.
		$sanitizer = $rules[ $sanitize_index ];

		// If the sanitizer is 'pass_through', we don't need to do anything.
		if ( $sanitizer === 'pass_through' ) {
			return $value;
		}

		if ( is_callable( $sanitizer ) ) {
			if ( is_array( $value ) ) {
				return array_map( $sanitizer, $value );
			}

			return call_user_func( $sanitizer, $value );
		}

		return $value;
	}

	private function add_special_cases( array $row, string $direction ): array {
		if ( $direction === 'to_ui' && isset( $row['type'] ) ) {
			$row['template_id'] = $row['type'];
		}

		return $row;
	}

	private function array_to_string( $data ): string {
		if ( ! is_array( $data ) ) {
			return '';
		}

		return implode( ',', array_map( 'sanitize_text_field', $data ) );
	}

	private function string_to_array( $data ): array {
		if ( ! is_string( $data ) || $data === '' ) {
			return [];
		}

		return array_map( 'trim', explode( ',', $data ) );
	}

	/**
	 * A special transformer to sanitize the 'conditions' array from the UI.
	 *
	 * @param mixed $value The incoming data, expected to be an array of condition rules.
	 * @return array The sanitized array of conditions.
	 */
	private function transform_conditions( $value ): array {
		if ( ! is_array( $value ) ) {
			return [];
		}

		$sanitized_conditions = [];
		foreach ( $value as $condition ) {
			if ( ! is_array( $condition ) ) {
				continue;
			}
			$clean_condition = [];
			if ( isset( $condition['action'] ) ) {
				$clean_condition['action'] = sanitize_text_field( $condition['action'] );
			}
			if ( isset( $condition['field'] ) ) {
				$clean_condition['field'] = sanitize_key( $condition['field'] );
			}
			if ( isset( $condition['condition'] ) ) {
				$clean_condition['condition'] = sanitize_text_field( $condition['condition'] );
			}
			if ( isset( $condition['value'] ) ) {
				$clean_condition['value'] = sanitize_text_field( $condition['value'] );
			}
			if ( ! empty( $clean_condition ) ) {
				$sanitized_conditions[] = $clean_condition;
			}
		}

		return $sanitized_conditions;
	}
}

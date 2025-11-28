<?php
/**
 * GeoDirectory Taxonomies Utility Class
 *
 * Pure utility functions for taxonomy validation and type checking.
 * For stateful operations, DB queries, and caching, use Taxonomies Service.
 *
 * @package GeoDirectory\Core\Utils
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Utils;

/**
 * A container for taxonomy-related pure utility functions.
 *
 * @since 3.0.0
 */
final class Taxonomies {

	/**
	 * Check given taxonomy belongs to GD.
	 *
	 * Refactored from geodir_is_gd_taxonomy().
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @param array  $valid_taxonomies Array of valid GD taxonomies.
	 * @return bool True if given taxonomy belongs to GD, otherwise False.
	 */
	public static function is_geodirectory( string $taxonomy, array $valid_taxonomies ): bool {
		if ( empty( $taxonomy ) ) {
			return false;
		}

		if ( ! self::get_type( $taxonomy ) ) {
			return false;
		}

		return in_array( $taxonomy, $valid_taxonomies );
	}

	/**
	 * Check the type of GD taxonomy.
	 *
	 * Refactored from geodir_taxonomy_type().
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Utils, renamed from taxonomy_type().
	 *
	 * @param string $taxonomy The taxonomy.
	 * @return string|null 'category', 'tag', or NULL if not a GD taxonomy.
	 */
	public static function get_type( string $taxonomy ): ?string {
		if ( empty( $taxonomy ) ) {
			return null;
		}

		if ( strpos( $taxonomy, 'gd_' ) !== 0 ) {
			return null;
		}

		if ( substr( $taxonomy, -8 ) === 'category' ) {
			return 'category';
		} elseif ( substr( $taxonomy, -5 ) === '_tags' ) {
			return 'tag';
		}

		return null;
	}
}

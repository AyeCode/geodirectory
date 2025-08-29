<?php
/**
 * Admin Form Helper
 *
 * Provides static helper methods for building admin settings forms.
 *
 * @package     GeoDirectory
 * @subpackage  Admin\Utils
 * @since       3.0.0
 */

namespace AyeCode\GeoDirectory\Admin\Utils;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class FormHelper {

	/**
	 * Retrieves all published WordPress pages and formats them for a select options array.
	 *
	 * Caches the result for 10 minutes to improve performance on sites with many pages.
	 *
	 * @param bool $include_default Whether to prepend a "Default" option.
	 * @return array An associative array of [page_id => page_title].
	 */
	public static function get_pages_as_options( bool $include_default = true ): array {
		// Use a transient to cache the query for better performance.
		$cached_pages = get_transient( 'geodir_pages_for_options2' );
		if ( false !== $cached_pages ) {
			return $cached_pages;
		}

		$options = [];

		if ( $include_default ) {
			// The key '0' or '' is often used for a default/none value.
			$options[0] = __( '-- Default --', 'geodirectory' );
		}

		$pages = get_pages();
		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page ) {
				$options[ $page->ID ] = esc_html( $page->post_title ) . ' (' . $page->post_name . ')';
			}
		}

		// Cache the result for 10 minutes.
		set_transient( 'geodir_pages_for_options2', $options, 10 * MINUTE_IN_SECONDS );

		return $options;
	}
}

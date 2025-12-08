<?php
/**
 * SEO Robots and Noindex Manager
 *
 * Manages robots meta tags and noindex directives for GeoDirectory pages.
 *
 * @package GeoDirectory\Core\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Seo;

use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Handles robots meta tags and noindex rules for GeoDirectory pages.
 */
final class RobotsManager {
	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 */
	public function __construct( private Settings $settings ) {}

	/**
	 * Outputs noindex meta tag for empty archive pages if setting is enabled.
	 *
	 * @return void
	 */
	public function maybe_noindex_empty_archives(): void {
		if ( $this->settings->get( 'noindex_archives' ) && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) ) ) {
			if ( geodir_is_empty_archive() ) {
				echo '<meta name="robots" content="noindex">';
			}
		}
	}

	/**
	 * Gets the IDs of pages that should be noindexed (GD template pages).
	 *
	 * These are pages used as templates that shouldn't appear in search results.
	 *
	 * @return array Array of page IDs.
	 */
	public function get_noindex_page_ids(): array {
		$page_ids = wp_cache_get( 'geodir_noindex_page_ids', 'geodir_noindex_page_ids' );

		if ( $page_ids !== false ) {
			return $page_ids;
		}

		$page_ids = [];
		$page_ids[] = geodir_get_page_id( 'details', '', false );
		$page_ids[] = geodir_get_page_id( 'archive', '', false );
		$page_ids[] = geodir_get_page_id( 'archive_item', '', false );

		// CPT template pages
		$_page_ids = geodir_cpt_template_pages();
		if ( ! empty( $_page_ids ) && is_array( $_page_ids ) ) {
			$page_ids = array_merge( $page_ids, $_page_ids );
		}

		$page_ids = apply_filters( 'geodir_get_noindex_page_ids', $page_ids );

		wp_cache_set( 'geodir_noindex_page_ids', $page_ids, 'geodir_noindex_page_ids' );

		return $page_ids;
	}
}

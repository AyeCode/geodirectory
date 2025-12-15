<?php
/**
 * SEO Defaults Service
 *
 * Provides default values for SEO titles, meta titles, and meta descriptions
 * for all GeoDirectory page types.
 *
 * @package GeoDirectory\Core\Services
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO defaults service class.
 *
 * Centralized location for all SEO-related default values. This replaces
 * the legacy GeoDir_Defaults class for SEO functionality in v3.
 *
 * @since 3.0.0
 */
final class SeoDefaults {

	/**
	 * Cached defaults array.
	 *
	 * @var array|null
	 */
	private ?array $defaults = null;

	/**
	 * Get page title default for a specific page type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page_type Page type identifier (e.g., 'cpt', 'single', 'search').
	 * @return string Default page title or empty string if not found.
	 */
	public function get_title( string $page_type ): string {
		$defaults = $this->get_defaults();
		$key      = 'title_' . $page_type;

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Get meta title default for a specific page type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page_type Page type identifier (e.g., 'cpt', 'single', 'search').
	 * @return string Default meta title or empty string if not found.
	 */
	public function get_meta_title( string $page_type ): string {
		$defaults = $this->get_defaults();
		$key      = 'meta_title_' . $page_type;

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Get meta description default for a specific page type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $page_type Page type identifier (e.g., 'cpt', 'single', 'search').
	 * @return string Default meta description or empty string if not found.
	 */
	public function get_meta_description( string $page_type ): string {
		$defaults = $this->get_defaults();
		$key      = 'meta_description_' . $page_type;

		return isset( $defaults[ $key ] ) ? (string) $defaults[ $key ] : '';
	}

	/**
	 * Get all SEO defaults.
	 *
	 * Returns an associative array of all default SEO values.
	 * Results are cached in memory after first call for performance.
	 *
	 * @since 3.0.0
	 *
	 * @return array All SEO defaults.
	 */
	private function get_defaults(): array {
		if ( $this->defaults !== null ) {
			return $this->defaults;
		}

		$this->defaults = [
			// CPT (Custom Post Type Archive)
			'title_cpt'            => __( 'All %%pt_plural%% %%in_location_single%%', 'geodirectory' ),
			'meta_title_cpt'       => __( '%%pt_plural%% %%in_location%% %%page%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_cpt' => __( '%%pt_plural%% %%in_location%%', 'geodirectory' ),

			// Category Archive
			'title_cat_archive'            => __( 'All %%category%% %%in_location_single%%', 'geodirectory' ),
			'meta_title_cat_archive'       => __( '%%category%% %%in_location%% %%page%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_cat_archive' => __( 'Posts related to Category: %%category%% %%in_location%%', 'geodirectory' ),

			// Tag Archive
			'title_tag_archive'            => __( 'Tag: %%tag%% %%in_location_single%%', 'geodirectory' ),
			'meta_title_tag_archive'       => __( '%%tag%% %%in_location%% %%page%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_tag_archive' => __( 'Posts related to Tag: %%tag%% %%in_location%%', 'geodirectory' ),

			// Single Post
			'title_single'            => __( '%%title%%', 'geodirectory' ),
			'meta_title_single'       => __( '%%title%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_single' => __( '%%excerpt%%', 'geodirectory' ),

			// Location
			'title_location'            => __( '%%location_single%%', 'geodirectory' ),
			'meta_title_location'       => __( '%%title%% %%location%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_location' => __( '%%location%%', 'geodirectory' ),

			// Search
			'title_search'            => __( 'Search results for: %%search_term%% %%search_near%%', 'geodirectory' ),
			'meta_title_search'       => __( '%%pt_plural%% search results for %%search_term%% %%search_near%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_search' => __( '%%pt_plural%% search results for %%search_term%% %%search_near%%', 'geodirectory' ),

			// Add Listing
			'title_add_listing'            => __( 'Add %%pt_single%%', 'geodirectory' ),
			'title_add_listing_edit'       => __( 'Edit %%pt_single%%', 'geodirectory' ),
			'meta_title_add_listing'       => __( 'Add %%pt_single%% %%sep%% %%sitename%%', 'geodirectory' ),
			'meta_description_add_listing' => __( 'Add your %%pt_single%% to %%sitename%%', 'geodirectory' ),
		];

		/**
		 * Filters the SEO defaults array.
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults SEO defaults array.
		 */
		$this->defaults = apply_filters( 'geodir_seo_defaults', $this->defaults );

		return $this->defaults;
	}
}

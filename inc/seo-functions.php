<?php
/**
 * SEO Helper Functions
 *
 * Provides procedural helper functions for SEO functionality,
 * primarily for use by external extensions and themes.
 *
 * @package GeoDirectory
 * @since 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ensures GeoDirectory SEO meta information is set up for the current page.
 *
 * This function is safe to call multiple times and will automatically do nothing
 * if a third-party SEO plugin (Yoast SEO, Rank Math, SEOPress, All in One SEO, etc.)
 * is active and handling the SEO meta information.
 *
 * External extensions can call this before functions like get_the_title() or similar
 * WordPress functions that need meta information to be set up properly.
 *
 * Example usage in extensions:
 * ```php
 * // Option 1: Get the title directly (backward compatible with v2)
 * if ( function_exists( 'geodir_seo_set_meta' ) ) {
 *     $title = geodir_seo_set_meta();
 * }
 *
 * // Option 2: Just ensure meta is set, then use WordPress functions
 * if ( function_exists( 'geodir_seo_set_meta' ) ) {
 *     geodir_seo_set_meta();
 * }
 * $title = get_the_title();
 * ```
 *
 * @since 3.0.0
 * @return string The page title. Returns empty string if a third-party SEO plugin is active
 *                or if GeoDirectory is not loaded.
 */
function geodir_seo_set_meta() {
	if ( ! function_exists( 'geodirectory' ) ) {
		return '';
	}

	try {
		return geodirectory()->seo()->ensure_meta_setup();
	} catch ( Exception $e ) {
		// Silently fail if SEO service isn't available
		// This prevents breaking extensions if something goes wrong
		return '';
	}
}

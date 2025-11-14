<?php
/**
 * Yoast SEO Integration
 *
 * @package GeoDirectory\Integrations\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Integrations\Seo;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Services\Settings;

final class Yoast implements SeoIntegrationInterface {
	private VariableReplacer $replacer;

	public function __construct( private Settings $settings ) {}

	/**
	 * {@inheritDoc}
	 */
	public function is_active(): bool {
		return defined( 'WPSEO_VERSION' ) && ! $this->settings->get( 'wpseo_disable' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;

		if ( version_compare( WPSEO_VERSION, '14.0', '>=' ) ) {
			add_filter( 'wpseo_title', [ $this, 'filter_title' ], 20 );
			add_filter( 'wpseo_metadesc', [ $this, 'filter_metadesc' ], 20 );
			add_filter( 'wpseo_breadcrumb_links', [ $this, 'filter_breadcrumb_links' ] );
			// @todo Add all other Yoast-specific hooks from the old file here.
		}
	}

	/**
	 * Filters the Yoast title and replaces our variables.
	 *
	 * @param string $title The original title from Yoast.
	 * @return string The filtered title.
	 */
	public function filter_title( string $title ): string {
		// If Yoast already has a title, we don't interfere.
		if ( ! empty( $title ) ) {
			return $title;
		}

		$new_title = (string) $this->settings->get( 'seo_' . $this->replacer->gd_page . '_meta_title' ); // Example of getting a setting

		return $this->replacer->replace( $new_title );
	}

	/**
	 * Filters the Yoast meta description and replaces our variables.
	 *
	 * @param string $metadesc The original meta description from Yoast.
	 * @return string The filtered meta description.
	 */
	public function filter_metadesc( string $metadesc ): string {
		if ( ! empty( $metadesc ) ) {
			return $metadesc;
		}

		$new_metadesc = (string) $this->settings->get( 'seo_' . $this->replacer->gd_page . '_meta_description' );

		return $this->replacer->replace( $new_metadesc );
	}

	/**
	 * Filters Yoast breadcrumbs.
	 *
	 * @param array $crumbs The breadcrumb array.
	 * @return array The filtered breadcrumb array.
	 */
	public function filter_breadcrumb_links( array $crumbs ): array {
		// @todo Add the breadcrumb modification logic from the old file here.
		return $crumbs;
	}
}

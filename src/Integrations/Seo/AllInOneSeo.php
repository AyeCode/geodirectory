<?php
/**
 * All in One SEO Integration
 *
 * @package GeoDirectory\Integrations\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Integrations\Seo;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Seo\MetaManager;
use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Integrates GeoDirectory with All in One SEO plugin.
 */
final class AllInOneSeo implements SeoIntegrationInterface {
	private VariableReplacer $replacer;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 * @param MetaManager $meta_manager The meta manager service.
	 */
	public function __construct(
		private Settings $settings,
		private MetaManager $meta_manager
	) {}

	/**
	 * {@inheritDoc}
	 */
	public function is_active(): bool {
		return function_exists( 'aioseo' ) && empty( $this->settings->get( 'aioseo_disable' ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;

		// Page title filters
		add_filter( 'the_title', [ $this->meta_manager, 'output_title' ], 10, 2 );
		add_filter( 'get_the_archive_title', [ $this->meta_manager, 'output_title' ], 10 );

		// Future: Add All in One SEO specific hooks here as needed
		// The old v2 class only had detection for AIOSEO, no specific integration hooks
	}
}

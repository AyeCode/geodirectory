<?php
/**
 * Default SEO Integration (Fallback)
 *
 * Used when no third-party SEO plugin is active. Provides basic SEO functionality.
 *
 * @package GeoDirectory\Integrations\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Integrations\Seo;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Seo\MetaManager;

/**
 * Fallback SEO integration when no SEO plugin is active.
 */
final class DefaultSeo implements SeoIntegrationInterface {
	private VariableReplacer $replacer;
	private MetaManager $meta_manager;

	/**
	 * Constructor.
	 *
	 * @param MetaManager $meta_manager The meta manager service.
	 */
	public function __construct( MetaManager $meta_manager ) {
		$this->meta_manager = $meta_manager;
	}

	/**
	 * {@inheritDoc}
	 *
	 * This integration is always active as the fallback.
	 */
	public function is_active(): bool {
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;

		// Meta title
		add_filter( 'wp_title', [ $this->meta_manager, 'output_meta_title' ], 1000, 2 );
		add_filter( 'pre_get_document_title', [ $this->meta_manager, 'output_meta_title' ], 1000 );

		// Page title
		add_filter( 'the_title', [ $this->meta_manager, 'output_title' ], 10, 2 );
		add_filter( 'get_the_archive_title', [ $this->meta_manager, 'output_title' ], 10 );

		// Meta description
		add_action( 'wp_head', [ $this->meta_manager, 'output_description' ] );
	}
}

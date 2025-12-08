<?php
/**
 * Main SEO Service
 *
 * Orchestrates SEO functionality including plugin integrations, meta management,
 * canonical URLs, robots directives, and sitemap exclusions.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;
use AyeCode\GeoDirectory\Core\Seo\VariableReplacer;
use AyeCode\GeoDirectory\Core\Seo\MetaManager;
use AyeCode\GeoDirectory\Core\Seo\CanonicalManager;
use AyeCode\GeoDirectory\Core\Seo\RobotsManager;
use AyeCode\GeoDirectory\Core\Seo\SitemapManager;

/**
 * Detects the active SEO plugin and loads the correct integration strategy.
 */
final class Seo {
	/**
	 * An array of available SEO integration classes.
	 *
	 * @var SeoIntegrationInterface[]
	 */
	private array $integrations = [];

	/**
	 * The currently active integration.
	 *
	 * @var SeoIntegrationInterface|null
	 */
	private ?SeoIntegrationInterface $active_integration = null;

	/**
	 * Constructor.
	 *
	 * @param VariableReplacer $variable_replacer The variable replacer service.
	 * @param MetaManager $meta_manager The meta manager service.
	 * @param CanonicalManager $canonical_manager The canonical manager service.
	 * @param RobotsManager $robots_manager The robots manager service.
	 * @param SitemapManager $sitemap_manager The sitemap manager service.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\Yoast $yoast The Yoast integration.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\RankMath $rankmath The Rank Math integration.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\SeoPress $seopress The SEOPress integration.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\AllInOneSeo $aioseo The All in One SEO integration.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo $default The fallback SEO.
	 */
	public function __construct(
		private VariableReplacer $variable_replacer,
		private MetaManager $meta_manager,
		private CanonicalManager $canonical_manager,
		private RobotsManager $robots_manager,
		private SitemapManager $sitemap_manager,
		\AyeCode\GeoDirectory\Integrations\Seo\Yoast $yoast,
		\AyeCode\GeoDirectory\Integrations\Seo\RankMath $rankmath,
		\AyeCode\GeoDirectory\Integrations\Seo\SeoPress $seopress,
		\AyeCode\GeoDirectory\Integrations\Seo\AllInOneSeo $aioseo,
		\AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo $default
	) {
		// The order here matters. The first one found to be active will be used
		$this->integrations = [ $yoast, $rankmath, $seopress, $aioseo ];

		// The default is always the last resort
		$this->integrations[] = $default;
	}

	/**
	 * Initializes the SEO service.
	 *
	 * This should be called during plugin initialization.
	 *
	 * @return void
	 */
	public function init(): void {
		add_action( 'init', [ $this, 'maybe_run' ] );

		// Maybe noindex empty archive pages
		add_action( 'wp_head', [ $this->robots_manager, 'maybe_noindex_empty_archives' ] );

		// Common hooks that run regardless of SEO plugin
		$this->register_common_hooks();
	}

	/**
	 * Registers hooks that are common across all SEO implementations.
	 *
	 * @return void
	 */
	private function register_common_hooks(): void {
		// Set a global so we don't change the menu items titles
		add_filter( 'pre_wp_nav_menu', [ MetaManager::class, 'set_menu_global' ], 10, 2 );
		add_filter( 'wp_nav_menu', [ MetaManager::class, 'unset_menu_global' ] );

		// YOOtheme renders own menuwalker
		if ( class_exists( 'YOOtheme\\Theme' ) ) {
			add_filter( 'wp_nav_menu_items', [ MetaManager::class, 'unset_menu_global' ], 999, 1 );
		}

		// Setup vars
		add_action( 'pre_get_document_title', [ $this->meta_manager, 'set_meta' ], 9 );

		// Sitemap exclusions for various sitemap implementations
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', [ $this->sitemap_manager, 'wpseo_exclude_from_sitemap' ], 20, 1 );
		add_filter( 'wp_sitemaps_posts_query_args', [ $this->sitemap_manager, 'wp_sitemaps_exclude_post_ids' ], 20, 2 );

		// The SEO Framework
		if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
			add_filter( 'the_seo_framework_sitemap_hpt_query_args', [ $this->sitemap_manager, 'the_seo_framework_sitemap_exclude_posts' ], 20, 1 );
			add_filter( 'the_seo_framework_sitemap_nhpt_query_args', [ $this->sitemap_manager, 'the_seo_framework_sitemap_exclude_posts' ], 20, 1 );
		}

		// Canonical page link filtering
		if ( ! is_admin() ) {
			add_filter( 'page_link', [ $this->canonical_manager, 'page_link' ], 10, 3 );
		}
	}

	/**
	 * Determines whether to run SEO functionality.
	 *
	 * @return void
	 */
	public function maybe_run(): void {
		$ajax_search = ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'geodir_ajax_search' && ! empty( $_REQUEST['geodir_search'] ) && wp_doing_ajax();

		// Don't run in wp-admin unless it's an AJAX search
		if ( is_admin() && ! $ajax_search ) {
			return;
		}

		// Get the active integration and register its hooks
		$active_integration = $this->get_active_integration();
		$active_integration->register_hooks( $this->variable_replacer );
	}

	/**
	 * Gets the currently active SEO integration.
	 *
	 * It loops through the available integrations and returns the first one
	 * that reports itself as active.
	 *
	 * @return SeoIntegrationInterface The active integration instance.
	 */
	public function get_active_integration(): SeoIntegrationInterface {
		if ( $this->active_integration === null ) {
			foreach ( $this->integrations as $integration ) {
				if ( $integration->is_active() ) {
					$this->active_integration = $integration;
					break;
				}
			}
		}

		return $this->active_integration;
	}

	/**
	 * Gets the variable replacer service.
	 *
	 * @return VariableReplacer
	 */
	public function variable_replacer(): VariableReplacer {
		return $this->variable_replacer;
	}

	/**
	 * Gets the meta manager service.
	 *
	 * @return MetaManager
	 */
	public function meta_manager(): MetaManager {
		return $this->meta_manager;
	}

	/**
	 * Gets the canonical manager service.
	 *
	 * @return CanonicalManager
	 */
	public function canonical_manager(): CanonicalManager {
		return $this->canonical_manager;
	}

	/**
	 * Gets the robots manager service.
	 *
	 * @return RobotsManager
	 */
	public function robots_manager(): RobotsManager {
		return $this->robots_manager;
	}

	/**
	 * Gets the sitemap manager service.
	 *
	 * @return SitemapManager
	 */
	public function sitemap_manager(): SitemapManager {
		return $this->sitemap_manager;
	}

	/**
	 * Ensures meta information is set up for the current page.
	 *
	 * Only sets up meta if no third-party SEO plugin is handling it.
	 * Safe to call multiple times - will only run if needed.
	 *
	 * This is useful for extensions that need to access SEO meta information
	 * before the normal WordPress hooks fire (e.g., before get_the_title()).
	 *
	 * @since 3.0.0
	 * @return string The page title (empty string if third-party SEO plugin is active).
	 */
	public function ensure_meta_setup(): string {
		// Only set up meta if we're using default SEO (no third-party plugin)
		$active = $this->get_active_integration();

		if ( $active instanceof \AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo ) {
			return $this->meta_manager->set_meta();
		}

		// If Yoast/RankMath/SEOPress/AIOSEO is active, return empty - they handle it
		return '';
	}
}

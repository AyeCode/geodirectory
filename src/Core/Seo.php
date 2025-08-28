<?php
/**
 * Main SEO Service
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Interfaces\SeoIntegrationInterface;

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
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo $default  The fallback SEO.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\Yoast      $yoast    The Yoast integration.
	 * @param \AyeCode\GeoDirectory\Integrations\Seo\RankMath   $rankmath The Rank Math integration.
	 * // @todo Add other integrations like SeoPress here.
	 */
	public function __construct(
		\AyeCode\GeoDirectory\Integrations\Seo\DefaultSeo $default,
		\AyeCode\GeoDirectory\Integrations\Seo\Yoast $yoast,
		\AyeCode\GeoDirectory\Integrations\Seo\RankMath $rankmath
	) {
		// The order here matters. The first one found to be active will be used.
		$this->integrations = [ $yoast, $rankmath ];

		// The default is always the last resort.
		$this->integrations[] = $default;
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
}

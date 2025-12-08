<?php
/**
 * SEOPress Integration
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
 * Integrates GeoDirectory with SEOPress plugin.
 */
final class SeoPress implements SeoIntegrationInterface {
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
		return function_exists( 'seopress_activation' ) && ! $this->settings->get( 'seopress_disable' );
	}

	/**
	 * {@inheritDoc}
	 */
	public function register_hooks( VariableReplacer $variable_replacer ): void {
		$this->replacer = $variable_replacer;

		// Variable replacement in titles and descriptions
		add_filter( 'seopress_titles_title', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_titles_desc', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_social_og_title', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_social_og_desc', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_social_twitter_card_title', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_social_twitter_card_summary', [ $this, 'replace_variables' ], 100, 1 );
		add_filter( 'seopress_social_twitter_card_desc', [ $this, 'replace_variables' ], 100, 1 );

		// Breadcrumb filtering
		add_filter( 'seopress_pro_breadcrumbs_crumbs', [ $this, 'breadcrumbs_crumbs' ], 20, 1 );

		// Page title filters
		add_filter( 'the_title', [ $this->meta_manager, 'output_title' ], 10, 2 );
		add_filter( 'get_the_archive_title', [ $this->meta_manager, 'output_title' ], 10 );
	}

	/**
	 * Replaces GD SEO title and meta variables with values.
	 *
	 * @param string $string String to replace variables.
	 * @return string String after GD SEO variables replaced.
	 */
	public function replace_variables( $string ): string {
		if ( ! empty( $string ) && is_scalar( $string ) && strpos( $string, '%%' ) !== false && geodir_is_geodir_page() ) {
			$string = $this->replacer->replace( $string );
		}

		return $string;
	}

	/**
	 * Filters SEOPress breadcrumbs links.
	 *
	 * @param array $crumbs The breadcrumb array.
	 * @return array Filtered breadcrumbs.
	 */
	public function breadcrumbs_crumbs( array $crumbs ): array {
		if ( ! empty( $crumbs ) ) {
			if ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) ) {
				$post_type = geodir_get_current_posttype();
				$cpt_link = get_post_type_archive_link( $post_type );
				$post_type_object = get_post_type() === 'page' ? get_post_type_object( 'page' ) : [];

				$_crumbs = [];

				foreach ( $crumbs as $i => $crumb ) {
					if ( ! empty( $crumb[1] ) && $crumb[1] === $cpt_link && ! empty( $crumbs[ $i + 1 ] ) && ! empty( $crumbs[ $i + 1 ][1] ) && $crumbs[ $i + 1 ][1] === $crumb[1] ) {
						continue;
					}

					if ( ! empty( $post_type_object ) && ! empty( $crumb[0] ) && empty( $crumb[1] ) && $crumb[0] === wp_strip_all_tags( $post_type_object->labels->name ) ) {
						if ( ! empty( $crumbs[ $i + 1 ] ) && ! empty( $crumbs[ $i + 1 ][1] ) && $crumbs[ $i + 1 ][1] === $cpt_link ) {
							continue;
						}

						$_crumbs[] = [
							wp_strip_all_tags( geodir_post_type_name( $post_type, true ) ),
							$cpt_link
						];
					} else {
						$_crumbs[] = $crumb;
					}
				}

				$crumbs = $_crumbs;
			}
		}

		return $crumbs;
	}
}

<?php
/**
 * SEO Sitemap Manager
 *
 * Manages sitemap exclusions for GeoDirectory template pages across various SEO plugins.
 *
 * @package GeoDirectory\Core\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Seo;

/**
 * Handles sitemap exclusions for GeoDirectory pages.
 */
final class SitemapManager {
	/**
	 * Constructor.
	 *
	 * @param RobotsManager $robots_manager The robots manager service.
	 */
	public function __construct( private RobotsManager $robots_manager ) {}

	/**
	 * Excludes GD template pages from Yoast XML sitemap.
	 *
	 * @param array $excluded_posts_ids The posts to exclude.
	 * @return array The filtered posts to exclude.
	 */
	public function wpseo_exclude_from_sitemap( array $excluded_posts_ids ): array {
		if ( ! is_array( $excluded_posts_ids ) ) {
			$excluded_posts_ids = [];
		}

		$gd_excluded_posts_ids = $this->robots_manager->get_noindex_page_ids();
		if ( ! empty( $gd_excluded_posts_ids ) && is_array( $gd_excluded_posts_ids ) ) {
			$excluded_posts_ids = empty( $excluded_posts_ids ) ? $gd_excluded_posts_ids : array_merge( $excluded_posts_ids, $gd_excluded_posts_ids );
		}

		return $excluded_posts_ids;
	}

	/**
	 * Excludes GD template pages from WordPress core XML sitemaps.
	 *
	 * @param array $args Array of WP_Query arguments.
	 * @param string $post_type Post type name.
	 * @return array Filtered query args.
	 */
	public function wp_sitemaps_exclude_post_ids( array $args, string $post_type ): array {
		if ( 'page' === $post_type ) {
			// GD template page ids
			$page_ids = $this->robots_manager->get_noindex_page_ids();

			if ( ! empty( $page_ids ) ) {
				$post_not_in = ! empty( $args['post__not_in'] ) && is_array( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $page_ids ) : $page_ids;

				$args['post__not_in'] = $post_not_in;
			}
		}

		return $args;
	}

	/**
	 * Excludes GD template pages from The SEO Framework sitemap.
	 *
	 * @param array $query_args The query args.
	 * @return array Filtered query args.
	 */
	public function the_seo_framework_sitemap_exclude_posts( array $query_args ): array {
		$exclude_ids = $this->robots_manager->get_noindex_page_ids();

		if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
			if ( ! empty( $query_args['post__not_in'] ) ) {
				$post__not_in = $query_args['post__not_in'];

				if ( ! is_array( $post__not_in ) ) {
					$post__not_in = explode( ',', $post__not_in );
				}

				$post__not_in = array_merge( $post__not_in, $exclude_ids );
			} else {
				$post__not_in = $exclude_ids;
			}

			$query_args['post__not_in'] = $post__not_in;
		}

		return $query_args;
	}
}

<?php
/**
 * SEO Canonical URL Manager
 *
 * Generates canonical URLs for GeoDirectory pages including pagination support.
 *
 * @package GeoDirectory\Core\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Seo;

/**
 * Handles canonical URL generation for GeoDirectory pages.
 */
final class CanonicalManager {
	/**
	 * Gets paged and non-paged canonical URLs for GD post type and archive pages.
	 *
	 * @return array|null Array of URLs with keys: canonical, canonical_paged, canonical_next, canonical_prev.
	 */
	public function get_canonicals(): ?array {
		global $wp_rewrite;

		if ( ! geodir_is_geodir_page() ) {
			return null;
		}

		$canonicals = [];
		$canonical = '';

		// Post type archive page
		if ( geodir_is_page( 'pt' ) ) {
			$post_type = geodir_get_current_posttype();
			$canonical = get_post_type_archive_link( $post_type );
		}
		// Archive (category/tag) page
		elseif ( geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->taxonomy ) ) {
				$term_link = get_term_link( $term, $term->taxonomy );

				if ( ! is_wp_error( $term_link ) ) {
					$canonical = $term_link;
				}
			}
		}

		if ( $canonical ) {
			$canonical_paged = $canonical;
			$canonical_next = $canonical;
			$canonical_prev = $canonical;

			$paged = (int) get_query_var( 'paged' );
			if ( $paged < 1 ) {
				$paged = 1;
			}

			// Non-pretty permalinks
			if ( ! $wp_rewrite->using_permalinks() ) {
				if ( $paged > 1 ) {
					$canonical_paged = add_query_arg( 'paged', $paged, $canonical );

					if ( $paged > 2 ) {
						$canonical_prev = add_query_arg( 'paged', ( $paged - 1 ), $canonical );
					}
				}

				$canonical_next = add_query_arg( 'paged', ( $paged + 1 ), $canonical );
			}
			// Pretty permalinks
			else {
				if ( $paged > 1 ) {
					$canonical_paged = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . $paged );

					if ( $paged > 2 ) {
						$canonical_prev = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . ( $paged - 1 ) );
					}
				}

				$canonical_next = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . ( $paged + 1 ) );
			}

			$canonicals = [
				'canonical' => $canonical,
				'canonical_paged' => $canonical_paged,
				'canonical_next' => $canonical_next,
				'canonical_prev' => $canonical_prev
			];
		}

		return apply_filters( 'geodir_get_canonicals', $canonicals );
	}

	/**
	 * Filters the page link for GD Archive pages.
	 *
	 * This prevents self-linking on archive pages.
	 *
	 * @param string $link The page's permalink.
	 * @param int $page_id The ID of the page.
	 * @param bool $sample Is it a sample permalink.
	 * @return string Filtered link.
	 */
	public function page_link( string $link, int $page_id, bool $sample ): string {
		global $wp_query;

		$query_object_id = '';
		if ( ! empty( $wp_query->post ) && isset( $wp_query->post->ID ) && geodir_is_geodir_page_id( $page_id ) ) {
			$query_object_id = $wp_query->post->ID;
		} elseif ( ! is_null( $wp_query ) ) {
			$query_object_id = get_queried_object_id();
		}

		if ( $query_object_id == $page_id && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) ) ) {
			$link = '#';
		}

		return $link;
	}
}

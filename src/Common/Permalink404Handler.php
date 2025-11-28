<?php
/**
 * Permalink 404 Handler
 *
 * Handles 404 detection for incorrect location/category parameters,
 * and attempts to rescue 404s by redirecting old v1/v2 URLs to v3 structure.
 *
 * @package GeoDirectory\Common
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Common;

use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Support\Hookable;

final class Permalink404Handler {
	use Hookable;

	private LocationsInterface $locations;

	public function __construct( LocationsInterface $locations ) {
		$this->locations = $locations;
	}

	/**
	 * Register all 404 handler hooks.
	 */
	public function hook(): void {
		$this->on( 'wp', [ $this, 'maybe_404' ] );
		$this->on( 'wp', [ $this, 'rescue_404' ] );
	}

	/**
	 * Check if current page should be 404 based on location/category mismatches.
	 */
	public function maybe_404(): void {
		global $wp_query, $gd_post;

		if ( ! geodir_is_page( 'single' ) || empty( $gd_post ) ) {
			$this->maybe_preview_pending_post();
			return;
		}

		$should_404 = false;
		$post_type = $wp_query->query_vars['post_type'] ?? '';
		$post_locations = $this->locations->get_for_post( $gd_post );

		// Check country mismatch
		if ( isset( $wp_query->query_vars['country'] ) && $wp_query->query_vars['country'] && isset( $gd_post->country ) ) {
			if ( isset( $post_locations->country_slug ) && $post_locations->country_slug && $post_locations->country_slug !== $wp_query->query_vars['country'] ) {
				$should_404 = true;
			}
		}

		// Check region mismatch
		if ( ! $should_404 && isset( $wp_query->query_vars['region'] ) && $wp_query->query_vars['region'] && isset( $gd_post->region ) ) {
			if ( isset( $post_locations->region_slug ) && $post_locations->region_slug && $post_locations->region_slug !== $wp_query->query_vars['region'] ) {
				$should_404 = true;
			}
		}

		// Check city mismatch
		if ( ! $should_404 && isset( $wp_query->query_vars['city'] ) && $wp_query->query_vars['city'] && isset( $gd_post->city ) ) {
			if ( isset( $post_locations->city_slug ) && $post_locations->city_slug && $post_locations->city_slug !== $wp_query->query_vars['city'] ) {
				$should_404 = true;
			}
		}

		// Check category mismatch
		if ( ! $should_404 && isset( $wp_query->query_vars[ $post_type . 'category' ] ) && $wp_query->query_vars[ $post_type . 'category' ] && isset( $gd_post->default_category ) && $gd_post->default_category ) {
			$is_cat = get_term_by( 'slug', $wp_query->query_vars[ $post_type . 'category' ], $post_type . 'category' );
			$is_cat = apply_filters( 'geodir_post_url_filter_term', $is_cat, $gd_post, (int) $gd_post->default_category );

			if ( ! $is_cat || ( isset( $is_cat->term_id ) && $is_cat->term_id !== $gd_post->default_category ) ) {
				$should_404 = true;
			}
		}

		if ( $should_404 ) {
			$wp_query->set_404();
			status_header( 404 );
		}
	}

	/**
	 * Allow post author to access their pending listings via preview.
	 */
	private function maybe_preview_pending_post(): void {
		global $wp_query;

		if ( empty( $wp_query ) || empty( $wp_query->query_vars['post_type'] ) || empty( $wp_query->query_vars['p'] ) || ! is_404() || is_preview() ) {
			return;
		}

		$user_id = (int) get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$post_type = $wp_query->query_vars['post_type'];
		$post_id = (int) $wp_query->query_vars['p'];

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return;
		}

		$post_status = get_post_status( $post_id );
		$allowed_statuses = array_keys( geodir_get_post_statuses( $post_type ) );

		if ( ! in_array( $post_status, $allowed_statuses ) ) {
			return;
		}

		if ( ! \GeoDir_Post_Data::owner_check( $post_id, $user_id ) ) {
			return;
		}

		$redirect = get_permalink( $post_id );
		if ( $redirect ) {
			$redirect = add_query_arg( [ 'preview' => 'true' ], $redirect );

			if ( $redirect && $redirect !== geodir_curPageURL() ) {
				wp_safe_redirect( $redirect );
				exit;
			}
		}
	}

	/**
	 * Attempt to rescue 404 pages by detecting old v1/v2 URLs and redirecting.
	 */
	public function rescue_404(): void {
		if ( ! is_404() || ! geodir_get_option( 'enable_404_rescue', 1 ) ) {
			return;
		}

		global $wp_query, $wp;

		$post_type = $wp_query->query_vars['post_type'] ?? '';
		$url_segments = explode( '/', $wp->request );

		// Try to detect GD CPT from URL if not in query vars
		if ( ! $post_type && ! empty( $url_segments ) ) {
			$post_type_slug = $url_segments[0];
			$gd_cpts = geodir_get_posttypes( 'array' );

			if ( ! empty( $gd_cpts ) ) {
				foreach ( $gd_cpts as $cpt => $cpt_options ) {
					if ( empty( $cpt_options['rewrite']['slug'] ) || ! $post_type_slug ) {
						continue;
					}

					if ( $cpt_options['rewrite']['slug'] === $post_type_slug ) {
						$post_type = $cpt;
						break;
					}

					// Match translated slug
					if ( urlencode( geodir_cpt_permalink_rewrite_slug( $cpt ) ) === $post_type_slug ) {
						$post_type = $cpt;
						break;
					}
				}
			}
		}

		if ( ! in_array( $post_type, geodir_get_posttypes() ) ) {
			return;
		}

		$has_location = false;
		if ( $location = $this->locations->get_current() ) {
			if ( ! empty( $location->country_slug ) || ! empty( $location->region_slug ) || ! empty( $location->city_slug ) || ! empty( $location->neighbourhood_slug ) ) {
				$has_location = true;
			}
		}

		$maybe_slug = end( $url_segments );

		if ( ! $maybe_slug ) {
			return;
		}

		array_shift( $url_segments ); // Remove CPT slug
		$location_segments = [];
		$location_string = '';
		$redirect = '';

		// Try category
		$is_cat = get_term_by( 'slug', $maybe_slug, $post_type . 'category' );
		if ( ! empty( $is_cat ) ) {
			foreach ( $url_segments as $url_segment ) {
				if ( $url_segment === $maybe_slug ) {
					continue;
				}

				// Check if it's not a term
				$is_term = get_term_by( 'slug', $url_segment, $post_type . 'category' );
				if ( empty( $is_term ) ) {
					$location_segments[] = $url_segment;
				}
			}

			if ( ! empty( $location_segments ) && ! $has_location ) {
				$location_string = implode( '/', $location_segments );
			}

			$term_link = get_term_link( $maybe_slug, $post_type . 'category' );

			if ( $term_link && ! is_wp_error( $term_link ) ) {
				$redirect = trailingslashit( $term_link ) . $location_string;
				if ( $this->is_trailing_slash() ) {
					$redirect = trailingslashit( $redirect );
				}
			}
		}

		// Try tag
		if ( ! $redirect ) {
			$is_tag = get_term_by( 'slug', $maybe_slug, $post_type . '_tags' );

			if ( ! empty( $is_tag ) ) {
				$tag_slug = geodir_get_option( 'permalink_tag_base', 'tags' );

				foreach ( $url_segments as $url_segment ) {
					// Old URL contains /tags/, so remove it
					if ( $url_segment === $maybe_slug || $url_segment === $tag_slug ) {
						continue;
					}

					// Check if it's not a term
					$is_term = get_term_by( 'slug', $url_segment, $post_type . '_tags' );
					if ( empty( $is_term ) ) {
						$location_segments[] = $url_segment;
					}
				}

				if ( ! empty( $location_segments ) && ! $has_location ) {
					$location_string = implode( '/', $location_segments );
				}

				$term_link = get_term_link( $maybe_slug, $post_type . '_tags' );

				if ( $term_link && ! is_wp_error( $term_link ) ) {
					$redirect = trailingslashit( $term_link ) . $location_string;
					if ( $this->is_trailing_slash() ) {
						$redirect = trailingslashit( $redirect );
					}
				}
			}
		}

		// Try post
		if ( ! $redirect ) {
			$is_post = get_page_by_path( $maybe_slug, OBJECT, $post_type );

			if ( ! empty( $is_post ) ) {
				$redirect = get_permalink( $is_post->ID );
			}
		}

		// Redirect if found and different from current URL
		if ( $redirect && $redirect !== geodir_curPageURL() ) {
			wp_redirect( $redirect, 301 );
			exit;
		}
	}

	/**
	 * Check if WordPress uses trailing slashes.
	 *
	 * @return bool True if trailing slash is used.
	 */
	private function is_trailing_slash(): bool {
		global $wp_rewrite;

		$permalink_structure = $wp_rewrite->permalink_structure ?? '';

		return ! empty( $permalink_structure ) && substr( $permalink_structure, -1 ) === '/';
	}
}

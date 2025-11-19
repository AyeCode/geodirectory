<?php
/**
 * GeoDirectory Post Hooks
 *
 * Centralized hooks for post-related WordPress filters and actions.
 *
 * @package GeoDirectory\Frontend
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Frontend;

use AyeCode\GeoDirectory\Support\Hookable;

/**
 * Manages WordPress hooks for GeoDirectory posts.
 *
 * @since 3.0.0
 */
final class PostHooks {
	use Hookable;

	/**
	 * Registers all WordPress hooks.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Previous/next post navigation filters.
		$this->filter( 'get_previous_post_join', [ $this, 'previous_next_post_join' ], 10, 5 );
		$this->filter( 'get_next_post_join', [ $this, 'previous_next_post_join' ], 10, 5 );
		$this->filter( 'get_previous_post_where', [ $this, 'previous_next_post_where' ], 10, 5 );
		$this->filter( 'get_next_post_where', [ $this, 'previous_next_post_where' ], 10, 5 );

		// Facebook thumbnail meta tag.
		$this->on( 'wp_head', [ $this, 'fb_like_thumbnail' ] );

		// Post updated notification.
		$this->on( 'post_updated', [ $this, 'function_post_updated' ], 16, 3 );

		// Badge filters.
		$this->filter( 'geodir_post_badge_check_match_found', [ $this, 'post_badge_filter_match_found' ], 10, 3 );

		// Custom field validation filters.
		$this->filter( 'geodir_custom_field_value_checkbox', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_datepicker', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_email', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_multiselect', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_phone', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_radio', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_select', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_text', [ $this, 'validate_custom_field_value_text' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_url', [ $this, 'validate_custom_field_value_url' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_html', [ $this, 'validate_custom_field_value_textarea' ], 10, 6 );
		$this->filter( 'geodir_custom_field_value_textarea', [ $this, 'validate_custom_field_value_textarea' ], 10, 6 );
	}

	/**
	 * Filters the JOIN clause in the SQL for an adjacent post query.
	 *
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
	 * @param string   $join           The JOIN clause in the SQL.
	 * @param bool     $in_same_term   Whether post should be in a same taxonomy term.
	 * @param array    $excluded_terms Array of excluded term IDs.
	 * @param string   $taxonomy       Taxonomy.
	 * @param \WP_Post $post           WP_Post object.
	 * @return string Filtered SQL JOIN clause.
	 */
	public function previous_next_post_join( string $join, bool $in_same_term, array $excluded_terms, string $taxonomy, \WP_Post $post ): string {
		global $plugin_prefix;

		if ( ! empty( $post->post_type ) && function_exists( 'geodir_get_posttypes' ) && in_array( $post->post_type, geodir_get_posttypes(), true ) ) {
			$join .= " INNER JOIN " . $plugin_prefix . $post->post_type . "_detail AS gd ON gd.post_id = p.ID";
		}

		return $join;
	}

	/**
	 * Filters the WHERE clause in the SQL for an adjacent post query.
	 *
	 * @global \wpdb  $wpdb          WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global object $gd_post       Current GeoDirectory post.
	 *
	 * @param string   $where          The `WHERE` clause in the SQL.
	 * @param bool     $in_same_term   Whether post should be in a same taxonomy term.
	 * @param array    $excluded_terms Array of excluded term IDs.
	 * @param string   $taxonomy       Taxonomy.
	 * @param \WP_Post $post           WP_Post object.
	 * @return string Filtered SQL WHERE clause.
	 */
	public function previous_next_post_where( string $where, bool $in_same_term, array $excluded_terms, string $taxonomy, \WP_Post $post ): string {
		global $wpdb, $plugin_prefix, $gd_post;

		if ( empty( $post->post_type ) || empty( $gd_post ) || ( empty( $gd_post->country ) && empty( $gd_post->region ) && empty( $gd_post->city ) ) ) {
			return $where;
		}

		if ( ! function_exists( 'geodir_get_posttypes' ) || ! in_array( $post->post_type, geodir_get_posttypes(), true ) ) {
			return $where;
		}

		$post_locations     = '';
		$post_locations_var = [];

		if ( ! empty( $gd_post->country ) ) {
			$post_locations       .= ' AND gd.country = %s';
			$post_locations_var[] = esc_attr( $gd_post->country );
		}

		if ( ! empty( $gd_post->region ) ) {
			$post_locations       .= ' AND gd.region = %s';
			$post_locations_var[] = esc_attr( $gd_post->region );
		}

		if ( ! empty( $gd_post->city ) ) {
			$post_locations       .= ' AND gd.city = %s';
			$post_locations_var[] = esc_attr( $gd_post->city );
		}

		if ( ! empty( $post_locations ) && ! empty( $post_locations_var ) ) {
			$where .= $wpdb->prepare( $post_locations, $post_locations_var );
		}

		return $where;
	}

	/**
	 * Adds the featured image to the place details page header for Facebook sharing.
	 *
	 * @global object $gd_post Current GeoDirectory post.
	 *
	 * @return void
	 */
	public function fb_like_thumbnail(): void {
		// Return if not a single post.
		if ( ! is_single() ) {
			return;
		}

		global $gd_post;

		if ( empty( $gd_post->featured_image ) ) {
			return;
		}

		$upload_dir = wp_upload_dir();
		$thumb      = $upload_dir['baseurl'] . $gd_post->featured_image;

		echo "\n\n<!-- GD Facebook Like Thumbnail -->\n<link rel=\"image_src\" href=\"" . esc_url( $thumb ) . "\" />\n<!-- End GD Facebook Like Thumbnail -->\n\n";
	}

	/**
	 * Called when post updated.
	 *
	 * @global array $geodir_post_published Post ids being published.
	 *
	 * @param int      $post_ID     The post ID.
	 * @param \WP_Post $post_after  The post object after update.
	 * @param \WP_Post $post_before The post object before update.
	 * @return void
	 */
	public function function_post_updated( int $post_ID, \WP_Post $post_after, \WP_Post $post_before ): void {
		global $geodir_post_published;

		$post_type = get_post_type( $post_ID );

		if ( empty( $post_type ) || ! function_exists( 'geodir_get_posttypes' ) || ! in_array( $post_type, geodir_get_posttypes(), true ) ) {
			return;
		}

		$publish_statuses = function_exists( 'geodir_get_publish_statuses' ) ? geodir_get_publish_statuses( [ 'post_type' => $post_type ] ) : [ 'publish' ];

		// Send notification when post moves from non-published to published.
		if ( ! empty( $post_after->post_status )
			&& in_array( $post_after->post_status, $publish_statuses, true )
			&& ! empty( $post_before->post_status )
			&& ! in_array( $post_before->post_status, $publish_statuses, true )
			&& $post_before->post_status !== 'trash'
		) {
			$gd_post = function_exists( 'geodir_get_post_info' ) ? geodir_get_post_info( $post_ID ) : null;

			if ( empty( $gd_post ) ) {
				return;
			}

			if ( ! is_array( $geodir_post_published ) ) {
				$geodir_post_published = [];
			}

			// post_updated executed before data saved in detail table.
			$geodir_post_published[ $post_ID ] = $post_ID;
		}
	}

	/**
	 * Filter post badge match value for category/tag fields.
	 *
	 * @param bool   $match_found True if match found, false otherwise.
	 * @param array  $args        Badge arguments.
	 * @param object $gd_post     The GD post object.
	 * @return bool Filtered match found value.
	 */
	public function post_badge_filter_match_found( bool $match_found, array $args, object $gd_post ): bool {
		$match_field = $args['key'] ?? '';

		if ( $match_field !== 'post_category' && $match_field !== 'post_tags' ) {
			return $match_found;
		}

		$search = $args['search'] ?? '';
		if ( $search !== '' ) {
			$search = array_map( 'trim', explode( ',', stripslashes( $search ) ) );
			$search = array_filter( array_unique( $search ) );
		}

		$value = isset( $gd_post->{$match_field} ) ? $gd_post->{$match_field} : '';
		if ( $value !== '' ) {
			$value = array_map( 'trim', explode( ',', stripslashes( $value ) ) );
			$value = array_filter( array_unique( $value ) );
		}

		$condition = $args['condition'] ?? '';

		if ( $condition === 'is_contains' ) {
			$match_found = false;

			if ( ! empty( $search ) && ! empty( $value ) ) {
				foreach ( $search as $_search ) {
					if ( in_array( $_search, $value, true ) ) {
						$match_found = true; // Contains any value.
						break;
					}
				}
			}
		} elseif ( $condition === 'is_not_contains' ) {
			$match_found = false;

			if ( ! empty( $search ) && ! empty( $value ) ) {
				$matches = 0;
				foreach ( $search as $_search ) {
					if ( ! in_array( $_search, $value, true ) ) {
						$matches++; // Not contains all value.
					}
				}

				if ( $matches === count( $search ) ) {
					$match_found = true;
				}
			}
		}

		return $match_found;
	}

	/**
	 * Sanitize text value.
	 *
	 * @param mixed  $value        Field value.
	 * @param object $gd_post      GeoDirectory post object.
	 * @param object $custom_field Custom field.
	 * @param int    $post_id      Post id.
	 * @param object $post         Post.
	 * @param string $update       Update.
	 * @return mixed Sanitized value.
	 */
	public function validate_custom_field_value_text( $value, object $gd_post, object $custom_field, int $post_id, object $post, string $update ) {
		if ( $value === '' ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'geodir_clean', $value );
		} else {
			$value = is_scalar( $value ) ? geodir_clean( stripslashes( $value ) ) : $value;
		}

		return $value;
	}

	/**
	 * Sanitize url value.
	 *
	 * @param mixed  $value        Field value.
	 * @param object $gd_post      GeoDirectory post object.
	 * @param object $custom_field Custom field.
	 * @param int    $post_id      Post id.
	 * @param object $post         Post.
	 * @param string $update       Update.
	 * @return mixed Sanitized url.
	 */
	public function validate_custom_field_value_url( $value, object $gd_post, object $custom_field, int $post_id, object $post, string $update ) {
		if ( $value === '' ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_url', $value );
		} else {
			$value = is_scalar( $value ) ? sanitize_url( wp_unslash( $value ) ) : $value;
		}

		return $value;
	}

	/**
	 * Sanitize textarea/html value.
	 *
	 * @param mixed  $value        Field value.
	 * @param object $gd_post      GeoDirectory post object.
	 * @param object $custom_field Custom field.
	 * @param int    $post_id      Post id.
	 * @param object $post         Post.
	 * @param string $update       Update.
	 * @return mixed Sanitized value.
	 */
	public function validate_custom_field_value_textarea( $value, object $gd_post, object $custom_field, int $post_id, object $post, string $update ) {
		if ( $value === '' ) {
			return $value;
		}

		$html = false;

		// Post content & video contains html/embed code.
		if ( $custom_field->field_type === 'html' || $custom_field->htmlvar_name === 'post_content' || $custom_field->htmlvar_name === 'video' ) {
			$html = true;
		} else {
			// Check if textarea field has html/embed enabled.
			$extra_fields = ! empty( $custom_field->extra_fields ) ? stripslashes_deep( maybe_unserialize( $custom_field->extra_fields ) ) : null;
			if ( is_array( $extra_fields ) && ( ! empty( $extra_fields['advanced_editor'] ) || ! empty( $extra_fields['embed'] ) ) ) {
				$html = true;
			}
		}

		if ( $html ) {
			$allowed_html = wp_kses_allowed_html( 'post' );

			if ( is_array( $allowed_html ) ) {
				// Add <iframe> support.
				if ( ! isset( $allowed_html['iframe'] ) ) {
					$allowed_html['iframe'] = [
						'class'           => true,
						'id'              => true,
						'src'             => true,
						'width'           => true,
						'height'          => true,
						'frameborder'     => true,
						'marginwidth'     => true,
						'marginheight'    => true,
						'scrolling'       => true,
						'style'           => true,
						'title'           => true,
						'allow'           => true,
						'allowfullscreen' => true,
						'data-*'          => true,
					];
				}
			}

			/**
			 * Filters the HTML that is allowed for a given field.
			 *
			 * @since 2.0.0.68
			 * @since 3.0.0 Moved to PostHooks.
			 *
			 * @param array|string $allowed_html  Allowed html tags.
			 * @param object       $custom_field  Custom field.
			 * @param mixed        $value         Field value.
			 * @param object       $gd_post       GeoDirectory post object.
			 */
			$allowed_html = apply_filters( 'geodir_custom_field_kses_allowed_html', $allowed_html, $custom_field, $value, $gd_post );

			if ( is_array( $value ) ) {
				$value = array_map( function ( $value ) use ( $allowed_html ) {
					return function_exists( 'geodir_sanitize_html_field' ) ? geodir_sanitize_html_field( $value, $allowed_html ) : wp_kses( $value, $allowed_html );
				}, $value );
			} else {
				$value = is_scalar( $value ) ? ( function_exists( 'geodir_sanitize_html_field' ) ? geodir_sanitize_html_field( $value, $allowed_html ) : wp_kses( $value, $allowed_html ) ) : $value;
			}
		} else {
			if ( is_array( $value ) ) {
				$value = array_map( function_exists( 'geodir_sanitize_textarea_field' ) ? 'geodir_sanitize_textarea_field' : 'sanitize_textarea_field', $value );
			} else {
				$value = is_scalar( $value ) ? ( function_exists( 'geodir_sanitize_textarea_field' ) ? geodir_sanitize_textarea_field( $value ) : sanitize_textarea_field( $value ) ) : $value;
			}
		}

		// post_content saved early, so don't need extra sanitization.
		if ( ! empty( $value ) && $custom_field->htmlvar_name !== 'post_content' ) {
			/**
			 * Extra sanitize textarea field value.
			 *
			 * @since 2.0.0
			 * @since 3.0.0 Moved to PostHooks.
			 *
			 * @param mixed $value Field value.
			 * @param array $args  Field arguments.
			 */
			$value = apply_filters( 'geodir_extra_sanitize_textarea_field', $value, [
				'default'   => $value,
				'field_key' => $custom_field->htmlvar_name,
				'gd_post'   => $gd_post,
				'allow_html' => $html,
			] );
		}

		return $value;
	}
}

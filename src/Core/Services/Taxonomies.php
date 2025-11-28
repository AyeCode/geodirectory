<?php
/**
 * Taxonomies Service
 *
 * Manages GeoDirectory taxonomies, including retrieval, validation, term operations, and database queries.
 *
 * @package GeoDirectory\Core\Services
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

/**
 * Taxonomies service class.
 *
 * Handles stateful operations for taxonomies including DB queries, caching, and settings access.
 */
final class Taxonomies {

	/**
	 * Cache for checking if a taxonomy is a GD taxonomy.
	 *
	 * @var array
	 */
	private static $is_gd_taxonomy = [];

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * Get all custom taxonomies.
	 *
	 * Refactored from geodir_get_taxonomies().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param string $post_type    The post type to filter by.
	 * @param bool   $include_tags Whether to include tag taxonomies. Default false.
	 *
	 * @return array Array of taxonomy slugs.
	 */
	public function get_taxonomies( string $post_type = '', bool $include_tags = false ): array {
		$taxonomies = $this->settings->get( 'taxonomies' );
		$gd_taxonomies = [];

		if ( ! empty( $taxonomies ) && is_array( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy => $args ) {
				// Filter by Post Type (if requested)
				if ( $post_type !== '' ) {
					if ( ! isset( $args['object_type'] ) || $args['object_type'] !== $post_type ) {
						continue;
					}
				}

				// Filter Tags (if not requested)
				if ( ! $include_tags && strpos( $taxonomy, '_tag' ) !== false ) {
					continue;
				}

				$gd_taxonomies[] = $taxonomy;
			}
		}

		/**
		 * Filter the taxonomies.
		 *
		 * @since 1.0.0
		 * @param array $gd_taxonomies The taxonomy array.
		 */
		return apply_filters( 'geodir_taxonomy', $gd_taxonomies );
	}

	/**
	 * Get post type listing slug.
	 *
	 * Refactored from geodir_get_listing_slug().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param string $object_type The post type or taxonomy.
	 * @return string|false Slug on success, false on failure.
	 */
	public function get_listing_slug( string $object_type = '' ) {
		if ( empty( $object_type ) ) {
			return false;
		}

		$post_types = geodirectory()->post_types->get_all( 'array' );
		$taxonomies = $this->settings->get( 'taxonomies' );

		// Check if it's a post type
		if ( ! empty( $post_types ) && array_key_exists( $object_type, $post_types ) ) {
			return $post_types[ $object_type ]['listing_slug'] ?? false;
		}

		// Check if it's a taxonomy
		if ( ! empty( $taxonomies ) && array_key_exists( $object_type, $taxonomies ) ) {
			// Remove suffix to get CPT (e.g., gd_placecategory -> gd_place)
			$temp_object_type = $object_type . '...';
			if ( stripos( strrev( $object_type ), 'sgat_' ) === 0 ) {
				// It's a tag taxonomy
				$cpt = str_replace( '_tags...', '', $temp_object_type );
			} else {
				// It's a category taxonomy
				$cpt = str_replace( 'category...', '', $temp_object_type );
			}

			if ( isset( $post_types[ $cpt ]['rewrite']['slug'] ) ) {
				return $post_types[ $cpt ]['rewrite']['slug'];
			}
		}

		return false;
	}

	/**
	 * Get a taxonomy post type.
	 *
	 * Refactored from geodir_get_taxonomy_posttype().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param string $taxonomy The WordPress taxonomy string.
	 * @return string|false Post type on success, false on failure.
	 */
	public function get_taxonomy_posttype( string $taxonomy = '' ) {
		global $wp_query;

		$post_type = [];
		$taxonomies = [];

		if ( ! empty( $taxonomy ) ) {
			$taxonomies[] = $taxonomy;
		} elseif ( isset( $wp_query->tax_query->queries ) ) {
			$tax_arr = $wp_query->tax_query->queries;
			// If tax query has 'relation' set, remove it for wp_list_pluck
			if ( isset( $tax_arr['relation'] ) ) {
				unset( $tax_arr['relation'] );
			}
			$taxonomies = wp_list_pluck( $tax_arr, 'taxonomy' );
		}

		if ( ! empty( $taxonomies ) ) {
			$gd_post_types = geodirectory()->post_types->get_all( 'names' );
			foreach ( $gd_post_types as $pt ) {
				$object_taxonomies = $pt === 'attachment' ? get_taxonomies_for_attachments() : get_object_taxonomies( $pt );
				if ( array_intersect( $taxonomies, $object_taxonomies ) ) {
					$post_type[] = $pt;
				}
			}
		}

		return ! empty( $post_type ) ? $post_type[0] : false;
	}

	/**
	 * Check whether a term exists or not.
	 *
	 * Returns term data on success, false on failure.
	 *
	 * Refactored from geodir_term_exists().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @param int|string $term     The term ID or slug.
	 * @param string     $taxonomy The taxonomy name.
	 * @param int        $parent   Parent term ID.
	 * @return array|int|false Term data on success, false on failure.
	 */
	public function term_exists( $term, string $taxonomy = '', int $parent = 0 ) {
		global $wpdb;

		$select = "SELECT term_id FROM $wpdb->terms as t WHERE ";
		$tax_select = "SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE ";

		if ( is_int( $term ) ) {
			if ( 0 === $term ) {
				return 0;
			}
			$where = 't.term_id = %d';
			if ( ! empty( $taxonomy ) ) {
				return $wpdb->get_row( $wpdb->prepare( $tax_select . $where . " AND tt.taxonomy = %s", $term, $taxonomy ), ARRAY_A );
			} else {
				return $wpdb->get_var( $wpdb->prepare( $select . $where, $term ) );
			}
		}

		$term = trim( wp_unslash( $term ) );
		$slug = sanitize_title( $term );

		if ( '' === $slug ) {
			return 0;
		}

		$where = 't.slug = %s';
		$where_fields = [ $slug ];

		if ( ! empty( $taxonomy ) ) {
			$parent = (int) $parent;
			if ( $parent > 0 ) {
				$where_fields[] = $parent;
				$where .= ' AND tt.parent = %d';
			}

			$where_fields[] = $taxonomy;

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT tt.term_id, tt.term_taxonomy_id FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy as tt ON tt.term_id = t.term_id WHERE $where AND tt.taxonomy = %s", $where_fields ), ARRAY_A );

			return $result ? $result : false;
		}

		$result = $wpdb->get_var( $wpdb->prepare( "SELECT term_id FROM $wpdb->terms as t WHERE $where", $where_fields ) );

		return $result ? $result : false;
	}

	/**
	 * Get term icon using term ID.
	 *
	 * If term ID not passed, returns all icons.
	 *
	 * Refactored from geodir_get_term_icon().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @param int|false $term_id The term ID.
	 * @param bool      $rebuild Force rebuild the icons when set to true.
	 * @return mixed|string|void Term icon(s).
	 */
	public function get_term_icon( $term_id = false, bool $rebuild = false ) {
		global $wpdb;

		$terms_icons = [];

		if ( ! $rebuild ) {
			$terms_icons = $this->settings->get( 'gd_term_icons' );
		}

		if ( empty( $terms_icons ) ) {
			$post_types = geodirectory()->post_types->get_all( 'names' );
			$terms_icons = [];
			$tax_arr = [];

			foreach ( $post_types as $post_type ) {
				$tax_arr[ $post_type . 'category' ] = $post_type;
			}

			$terms = $wpdb->get_results( "SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE taxonomy IN ('" . implode( "','", array_keys( $tax_arr ) ) . "')" );

			if ( ! empty( $terms ) ) {
				$a_terms = [];
				foreach ( $terms as $term ) {
					$a_terms[ $tax_arr[ $term->taxonomy ] ][] = $term;
				}

				foreach ( $a_terms as $pt => $t2 ) {
					foreach ( $t2 as $term ) {
						$terms_icons[ $term->term_id ] = geodir_get_cat_icon( $term->term_id, true, true );
					}
				}
			}

			geodir_update_option( 'gd_term_icons', $terms_icons );
		}

		if ( ! empty( $term_id ) ) {
			if ( isset( $terms_icons[ $term_id ] ) ) {
				return $terms_icons[ $term_id ];
			} else {
				return \GeoDir_Maps::default_marker_icon( true );
			}
		}

		if ( is_ssl() ) {
			$terms_icons = str_replace( 'http:', 'https:', $terms_icons );
		}

		return apply_filters( 'geodir_get_term_icons', $terms_icons, $term_id );
	}

	/**
	 * Recount product terms, ignoring hidden products.
	 *
	 * Refactored from geodir_term_recount().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @param array  $terms                         Terms array.
	 * @param object $taxonomy                      Taxonomy object.
	 * @param string $post_type                     Post type.
	 * @param bool   $callback                      Use standard callback.
	 * @param bool   $terms_are_term_taxonomy_ids   Whether terms are term taxonomy IDs.
	 */
	public function term_recount( array $terms, $taxonomy, string $post_type, bool $callback = true, bool $terms_are_term_taxonomy_ids = true ): void {
		global $wpdb;

		// Standard callback
		if ( $callback ) {
			$id_parent_terms = $terms;
			if ( ! $terms_are_term_taxonomy_ids ) {
				$id_parent_terms = array_filter( (array) array_keys( $terms ) );
			}
			_update_post_term_count( $id_parent_terms, $taxonomy );
		}

		$exclude_term_ids = [];

		$query = [
			'fields' => "SELECT COUNT( DISTINCT ID ) FROM {$wpdb->posts} p",
			'join'   => '',
			'where'  => "
				WHERE 1=1
				AND p.post_status = 'publish'
				AND p.post_type = '{$post_type}'
			",
		];

		if ( count( $exclude_term_ids ) ) {
			$query['join']  .= " LEFT JOIN ( SELECT object_id FROM {$wpdb->term_relationships} WHERE term_taxonomy_id IN ( " . implode( ',', array_map( 'absint', $exclude_term_ids ) ) . " ) ) AS exclude_join ON exclude_join.object_id = p.ID";
			$query['where'] .= " AND exclude_join.object_id IS NULL";
		}

		// Pre-process term taxonomy ids
		if ( ! $terms_are_term_taxonomy_ids ) {
			$terms = array_filter( (array) array_keys( $terms ) );
		} else {
			$term_taxonomy_ids = $terms;
			$terms = [];
			foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {
				$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id, $taxonomy->name );
				if ( $term ) {
					$terms[] = $term->term_id;
				}
			}
		}

		if ( empty( $terms ) ) {
			return;
		}

		// Ancestors need counting
		if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
			foreach ( $terms as $term_id ) {
				$ancestors = get_ancestors( $term_id, $taxonomy->name );
				if ( ! empty( $ancestors ) ) {
					$terms = array_merge( $terms, $ancestors );
				}
			}
		}

		$terms = array_unique( $terms );

		// Count the terms
		foreach ( $terms as $term_id ) {
			$terms_to_count = [ absint( $term_id ) ];

			if ( is_taxonomy_hierarchical( $taxonomy->name ) ) {
				$children = get_term_children( $term_id, $taxonomy->name );
				if ( $children && ! is_wp_error( $children ) ) {
					$terms_to_count = array_unique( array_map( 'absint', array_merge( $terms_to_count, $children ) ) );
				}
			}

			// Generate term query
			$term_query = $query;
			$term_query['join'] .= " INNER JOIN ( SELECT object_id FROM {$wpdb->term_relationships} INNER JOIN {$wpdb->term_taxonomy} using( term_taxonomy_id ) WHERE term_id IN ( " . implode( ',', array_map( 'absint', $terms_to_count ) ) . " ) ) AS include_join ON include_join.object_id = p.ID";

			// Get the count
			$count = $wpdb->get_var( implode( ' ', $term_query ) );

			// Update the count
			update_term_meta( $term_id, '_gd_post_count_' . $taxonomy->name, absint( $count ) );
		}

		delete_transient( 'geodir_term_counts' );
	}

	/**
	 * Get all child terms.
	 *
	 * Refactored from geodir_get_term_children().
	 *
	 * @since 2.0.0.66
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @param int    $child_of Parent term to get child terms.
	 * @param string $taxonomy Taxonomy.
	 * @param array  $terms    Array of terms. Default Empty.
	 * @return array Array of child terms.
	 */
	public function get_term_children( int $child_of, string $taxonomy, array $terms = [] ): array {
		global $wpdb;

		if ( empty( $terms ) && $child_of > 0 ) {
			$row = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.term_id = %d", [ $taxonomy, $child_of ] ) );
			if ( ! empty( $row ) ) {
				$terms[ $row->term_id ] = $row;
			}
		}

		$query = $wpdb->prepare( "SELECT t.*, tt.* FROM {$wpdb->terms} AS t INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND tt.parent = %d", [ $taxonomy, $child_of ] );
		$results = $wpdb->get_results( $query );

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				$terms[ $row->term_id ] = $row;

				if ( ! empty( $row->parent ) ) {
					$terms = $this->get_term_children( $row->term_id, $taxonomy, $terms );
				}
			}
		}

		return $terms;
	}

	/**
	 * Get the term post type.
	 *
	 * Refactored from geodir_term_post_type().
	 *
	 * @since 2.0.0.68
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int $term_id The term id.
	 * @return string Post type.
	 */
	public function get_term_post_type( int $term_id ): string {
		$post_type = wp_cache_get( 'geodir_term_post_type:' . $term_id, 'geodir_term_post_type' );

		if ( $post_type !== false ) {
			return $post_type;
		}

		$post_type = '';
		$term = get_term( $term_id );

		if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
			$taxonomy = get_taxonomy( $term->taxonomy );

			if ( ! empty( $taxonomy ) && ! is_wp_error( $taxonomy ) && ! empty( $taxonomy->object_type ) ) {
				$post_type = $taxonomy->object_type[0];
			}
		}

		wp_cache_set( 'geodir_term_post_type:' . $term_id, $post_type, 'geodir_term_post_type' );

		return $post_type;
	}

	/**
	 * Check given taxonomy belongs to GD with caching.
	 *
	 * Refactored from geodir_is_gd_taxonomy().
	 *
	 * @since 2.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param string $taxonomy The taxonomy.
	 * @return bool True if given taxonomy belongs to GD, otherwise False.
	 */
	public function is_gd_taxonomy( string $taxonomy ): bool {
		if ( empty( $taxonomy ) ) {
			return false;
		}

		if ( ! \AyeCode\GeoDirectory\Core\Utils\Taxonomies::get_type( $taxonomy ) ) {
			return false;
		}

		if ( ! empty( self::$is_gd_taxonomy ) && ! empty( self::$is_gd_taxonomy[ $taxonomy ] ) ) {
			return true;
		}

		$gd_taxonomies = $this->get_taxonomies( '', true );

		if ( ! empty( $gd_taxonomies ) && in_array( $taxonomy, $gd_taxonomies ) ) {
			if ( ! is_array( self::$is_gd_taxonomy ) ) {
				self::$is_gd_taxonomy = [];
			}

			self::$is_gd_taxonomy[ $taxonomy ] = true;

			return true;
		}

		return false;
	}

	/**
	 * Build term link with location parameters.
	 *
	 * Returns the term link with parameters.
	 *
	 * Refactored from geodir_term_link().
	 *
	 * @since 1.0.0
	 * @since 1.5.7 Changes for the neighbourhood system improvement.
	 * @since 1.6.11 Details page add locations to the term links.
	 * @since 3.0.0 Moved to Taxonomies service, renamed from term_link().
	 *
	 * @param string $termlink The term link.
	 * @param object $term     The term object.
	 * @param string $taxonomy The taxonomy name.
	 * @return string The modified term link.
	 */
	public function build_term_link( string $termlink, $term, string $taxonomy ): string {
		$geodir_taxonomies = $this->get_taxonomies( '', true );

		if ( ! isset( $taxonomy ) || empty( $geodir_taxonomies ) || ! in_array( $taxonomy, $geodir_taxonomies ) ) {
			return $termlink;
		}

		global $geodir_add_location_url;
		$include_location = false;
		$request_term = [];
		$add_location_url = $this->settings->get( 'geodir_add_location_url' );
		$location_manager = defined( 'GEODIR_LOCATIONS_TABLE' );

		$listing_slug = $this->get_listing_slug( $taxonomy );

		if ( $geodir_add_location_url !== null && $geodir_add_location_url !== '' ) {
			if ( $geodir_add_location_url && $add_location_url ) {
				$include_location = true;
			}
		} elseif ( $add_location_url ) {
			$include_location = true;
		} elseif ( $add_location_url && $location_manager && geodir_is_page( 'detail' ) ) {
			$include_location = true;
		}

		if ( $include_location ) {
			global $post;

			$neighbourhood_active = $location_manager && $this->settings->get( 'lm_enable_neighbourhoods' );

			if ( geodir_is_page( 'detail' ) && isset( $post->country_slug ) ) {
				$location_terms = [
					'gd_country' => $post->country_slug,
					'gd_region' => $post->region_slug,
					'gd_city' => $post->city_slug
				];

				if ( $neighbourhood_active && ! empty( $location_terms['gd_city'] ) && ( $gd_neighbourhood = get_query_var( 'gd_neighbourhood' ) ) ) {
					$location_terms['gd_neighbourhood'] = $gd_neighbourhood;
				}
			} else {
				$location_terms = geodir_get_current_location_terms( 'query_vars' );
			}

			$location_terms = geodir_remove_location_terms( $location_terms );

			if ( ! empty( $location_terms ) ) {
				if ( get_option( 'permalink_structure' ) !== '' ) {
					$old_listing_slug = '/' . $listing_slug . '/';
					$request_term = implode( '/', $location_terms );
					$new_listing_slug = '/' . $listing_slug . '/' . $request_term . '/';

					$termlink = substr_replace( $termlink, $new_listing_slug, strpos( $termlink, $old_listing_slug ), strlen( $old_listing_slug ) );
				} else {
					$termlink = geodir_getlink( $termlink, $request_term );
				}
			}
		}

		return apply_filters( 'geodir_term_link', $termlink, $term, $taxonomy );
	}

	/**
	 * Get category select dropdown HTML.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_category_select().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param string $post_type The post type.
	 * @param string $selected  The selected value.
	 * @param bool   $is_tag    Is this a tag taxonomy?
	 * @param bool   $echo      Prints the HTML when set to true.
	 * @return string|void Dropdown HTML or void if echoing.
	 */
	public function get_category_select( string $post_type = '', string $selected = '', bool $is_tag = false, bool $echo = true ) {
		$html = '';
		$taxonomies = $this->get_taxonomies( $post_type, $is_tag );

		$categories = get_terms( $taxonomies );

		$html .= '<option value="0">' . __( 'All', 'geodirectory' ) . '</option>';

		foreach ( $categories as $category_obj ) {
			$select_opt = '';
			if ( $selected == $category_obj->term_id ) {
				$select_opt = 'selected="selected"';
			}
			$html .= '<option ' . $select_opt . ' value="' . $category_obj->term_id . '">' . geodir_utf8_ucfirst( $category_obj->name ) . '</option>';
		}

		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Get category icon URL.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_cat_icon().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int  $term_id   Term ID.
	 * @param bool $full_path Get full path.
	 * @param bool $default   Return default if not found.
	 * @return string Category icon URL.
	 */
	public function get_cat_icon( int $term_id, bool $full_path = false, bool $default = false ): string {
		$term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );

		$cat_icon = is_array( $term_meta ) && ! empty( $term_meta['src'] ) ? $term_meta['src'] : '';

		if ( ! $cat_icon && $default ) {
			$cat_icon = \GeoDir_Maps::default_marker_icon( $full_path );
		}

		if ( $cat_icon && $full_path ) {
			$cat_icon = geodir_file_relative_url( $cat_icon, true );
		}

		return apply_filters( 'geodir_get_cat_icon', $cat_icon, $term_id, $full_path, $default );
	}

	/**
	 * Get category icon alt text.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_cat_icon_alt().
	 *
	 * @since 2.3.76
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int          $term_id Category ID.
	 * @param string|bool $default  Default alt text. Default false.
	 * @return string Icon alt text.
	 */
	public function get_cat_icon_alt( int $term_id, $default = false ): string {
		global $geodir_cat_icon_alt;

		if ( ! is_array( $geodir_cat_icon_alt ) ) {
			$geodir_cat_icon_alt = [];
		}

		if ( isset( $geodir_cat_icon_alt[ $term_id ] ) ) {
			return $geodir_cat_icon_alt[ $term_id ];
		}

		$alt = '';
		$attachment_id = 0;

		if ( ! empty( $term_id ) && $term_id != 'd' && $term_id > 0 ) {
			$term_meta = get_term_meta( $term_id, 'ct_cat_icon', true );

			$attachment_id = is_array( $term_meta ) && ! empty( $term_meta['id'] ) ? absint( $term_meta['id'] ) : 0;
			$alt = $attachment_id > 0 ? get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';

			if ( $alt ) {
				$alt = trim( strip_tags( $alt ) );
			}
		}

		// Default alt text
		if ( $alt === '' && $default !== false && is_scalar( $default ) ) {
			$alt = $default;
		}

		$alt = apply_filters( 'geodir_get_cat_icon_alt', $alt, $term_id, $default, $attachment_id );

		$geodir_cat_icon_alt[ $term_id ] = $alt;

		return $alt;
	}

	/**
	 * Get category default image.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_cat_image().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int  $term_id   Term ID.
	 * @param bool $full_path Get full path.
	 * @return string Category image URL.
	 */
	public function get_cat_image( int $term_id, bool $full_path = false ): string {
		$term_meta = get_term_meta( $term_id, 'ct_cat_default_img', true );

		$cat_image = is_array( $term_meta ) && ! empty( $term_meta['src'] ) ? $term_meta['src'] : '';

		if ( $cat_image && $full_path && strpos( $cat_image, 'http://' ) !== 0 && strpos( $cat_image, 'https://' ) !== 0 ) {
			$cat_image = geodir_file_relative_url( $cat_image, true );
		}

		return apply_filters( 'geodir_get_cat_image', $cat_image, $term_id, $full_path );
	}

	/**
	 * Get category top description HTML.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_cat_top_description().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int $term_id Term ID.
	 * @return string Top description HTML.
	 */
	public function get_cat_top_description( int $term_id ): string {
		$top_description = get_term_meta( $term_id, 'ct_cat_top_desc', true );

		if ( $top_description ) {
			// Location variable
			$location_replace_vars = geodir_location_replace_vars();
			foreach ( $location_replace_vars as $lkey => $lval ) {
				if ( strpos( $top_description, $lkey ) !== false ) {
					$top_description = str_replace( $lkey, $lval, $top_description );
				}
			}
		}

		return apply_filters( 'geodir_get_cat_top_description', $top_description, $term_id );
	}

	/**
	 * Get category description HTML.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_category_description().
	 *
	 * @since 2.2.19
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @param int    $term_id Term ID.
	 * @param string $type    Description type.
	 * @return string Category description HTML.
	 */
	public function get_category_description( int $term_id, string $type = 'top' ): string {
		if ( $type && in_array( $type, [ 'bottom', 'main' ] ) ) {
			if ( $type === 'bottom' ) {
				$description = get_term_meta( $term_id, 'ct_cat_bottom_desc', true );
			} else {
				$description = term_description( $term_id );
			}

			if ( $description ) {
				// Location variables
				$replace_vars = geodir_location_replace_vars();

				foreach ( $replace_vars as $key => $value ) {
					if ( strpos( $description, $key ) !== false ) {
						$description = str_replace( $key, $value, $description );
					}
				}
			}
		} else {
			$description = $this->get_cat_top_description( $term_id );
		}

		if ( ! empty( $description ) && $type !== 'main' ) {
			$description = geodir_filter_textarea_output( $description, 'category_description', [ 'type' => $type, 'term_id' => $term_id ] );
		}

		return apply_filters( 'geodir_get_category_description', $description, $term_id, $type );
	}

	/**
	 * Get schemas options array.
	 *
	 * Refactored from GeoDir_Admin_Taxonomies::get_schemas().
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to Taxonomies service.
	 *
	 * @return array Schemas array.
	 */
	public function get_schemas(): array {
		include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/settings/data_schemas.php' );
		$raw_schemas = geodir_data_schemas();
		$schemas = array_merge( [ '' => __( 'Default (LocalBusiness)', 'geodirectory' ) ], $raw_schemas );

		/**
		 * Allows you to add/filter the cat schema types.
		 *
		 * @since 1.5.7
		 */
		return apply_filters( 'geodir_cat_schemas', $schemas );
	}

	/**
	 * Taxonomy Walker.
	 *
	 * Generates the HTML for category lists (Options, Checkboxes, or Radios).
	 * Refactored from GeoDir_Admin_Taxonomies::taxonomy_walker.
	 *
	 * @param string $taxonomy     The taxonomy slug.
	 * @param int    $parent       Parent term ID.
	 * @param int    $padding      Visual depth/padding level.
	 * @param array  $args         Configuration arguments:
	 * - display_type: 'select', 'multiselect', 'radio', 'checkbox'
	 * - selected: array of selected term IDs
	 * - exclude: array of term IDs to exclude
	 * - hide_empty: bool
	 *
	 * @return string HTML output.
	 */
	public function render_walker( $taxonomy, $parent = 0, $padding = 0, $args = [] ) {
		$defaults = [
			'display_type' => 'select',
			'selected'     => [],
			'exclude'      => [],
			'hide_empty'   => false,
		];
		$args = wp_parse_args( $args, $defaults );

		$terms = get_terms( [
			'taxonomy'   => $taxonomy,
			'parent'     => $parent,
			'hide_empty' => $args['hide_empty'],
			'exclude'    => $args['exclude'],
		] );

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return '';
		}

		$output = '';
		$p_margin = $padding * 20;
		$next_padding = $padding + 1;
		$is_bs5 = isset( $GLOBALS['aui_bs5'] ) && $GLOBALS['aui_bs5'];

		// Wrapper for Checkbox/Radio groups (only on root level calls usually, but logic kept recursive)
		// In Field class we wrap the whole thing, so we might just need the items here.
		// Legacy logic added specific divs for parent/child. Let's keep it simple for the Input Field context.

		foreach ( $terms as $term ) {
			$term_name = geodirectory()->helpers->utf8_ucfirst( $term->name );
			$is_selected = in_array( $term->term_id, $args['selected'] );

			// --- RENDER: SELECT / MULTISELECT ---
			if ( in_array( $args['display_type'], [ 'select', 'multiselect' ] ) ) {
				$selected_attr = $is_selected ? 'selected="selected"' : '';
				$style = $p_margin > 0 ? 'style="margin-left:' . $p_margin . 'px;"' : '';
				$child_dash = $p_margin > 0 ? str_repeat( "-", $padding ) . ' ' : ''; // Visual dash for dropdowns

				$output .= sprintf(
					'<option value="%s" %s %s>%s%s</option>',
					esc_attr( $term->term_id ),
					$selected_attr,
					$style,
					$child_dash,
					esc_html( $term_name )
				);

			}
			// --- RENDER: RADIO / CHECKBOX ---
			else {
				$checked_attr = $is_selected ? 'checked="checked"' : '';
				$input_type = $args['display_type']; // 'radio' or 'checkbox'
				// For checkboxes/radios, WP expects tax_input[taxonomy][]
				$input_name = "tax_input[{$taxonomy}][]";

				$margin_class = $is_bs5 ? 'ms-' . ($padding * 3) : 'ml-' . ($padding * 3); // Bootstrap indentation
				$wrapper_style = $padding > 0 ? 'style="margin-left:' . $p_margin . 'px"' : ''; // Fallback inline

				$output .= '<div class="form-check" ' . $wrapper_style . '>';
				$output .= sprintf(
					'<input class="form-check-input" type="%s" name="%s" value="%s" id="gd-cat-%s" %s>',
					$input_type,
					$input_name,
					esc_attr( $term->term_id ),
					esc_attr( $term->term_id ),
					$checked_attr
				);
				$output .= sprintf(
					'<label class="form-check-label" for="gd-cat-%s">%s</label>',
					esc_attr( $term->term_id ),
					esc_html( $term_name )
				);
				$output .= '</div>';
			}

			// --- RECURSION ---
			// Fetch children
			$output .= $this->render_walker( $taxonomy, $term->term_id, $next_padding, $args );
		}

		return $output;
	}
}

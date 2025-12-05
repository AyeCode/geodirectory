<?php
/**
 * Post Types Service
 *
 * Manages GeoDirectory post types, including retrieval, caching, and database operations.
 *
 * @package GeoDirectory\Core\Services
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Services\Settings;
use AyeCode\GeoDirectory\Database\Repository\SortRepository;

/**
 * Post Types service class.
 *
 * Handles stateful operations for post types including DB queries, caching, and settings access.
 */
final class PostTypes {
	/**
	 * Cache for checking if a post type requires an address.
	 *
	 * @var array
	 */
	private static $cpt_requires_address = [];

	/**
	 * Cache for checking if a post type has published posts.
	 *
	 * @var array
	 */
	private static $cpt_has_post = [];

	/**
	 * Cache for checking if a post type is a GeoDirectory post type.
	 *
	 * @var array
	 */
	private static $is_gd_post_type = [];

	/**
	 * Settings service.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Sort repository.
	 *
	 * @var SortRepository
	 */
	private SortRepository $sort_repository;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 * @param SortRepository $sort_repository The sort repository.
	 */
	public function __construct( Settings $settings, SortRepository $sort_repository ) {
		$this->settings = $settings;
		$this->sort_repository = $sort_repository;
	}

	/**
	 * Get list of geodirectory Post Types.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 options case added to get post type options array.
	 * @since 2.0.0 options-plural option added.
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @param string $output The output Type.
	 * @return array|object|string Post Types.
	 */
	public function get_all( string $output = 'names' ) {
		$post_types = [];
		$post_types = $this->settings->get( 'post_types' );

		$post_types = stripslashes_deep( $post_types );
		if ( ! empty( $post_types ) ) {
			switch ( $output ) {
				case 'object':
				case 'Object':
					$post_types = json_decode( json_encode( $post_types ), FALSE );
					break;
				case 'array':
				case 'Array':
					$post_types = (array) $post_types;
					break;
				case 'options':
					$post_types = (array) $post_types;

					$options = array();
					if ( ! empty( $post_types ) ) {
						foreach ( $post_types as $key => $info ) {
							$options[ $key ] = __( $info['labels']['singular_name'], 'geodirectory' );
						}
					}
					$post_types = $options;
					break;
				case 'options-plural':
					$post_types = (array) $post_types;

					$options = array();
					if ( ! empty( $post_types ) ) {
						foreach ( $post_types as $key => $info ) {
							$options[ $key ] = __( $info['labels']['name'], 'geodirectory' );
						}
					}
					$post_types = $options;
					break;
				default:
					$post_types = array_keys( $post_types );
					break;
			}
		}

		if ( ! empty( $post_types ) ) {
			return $post_types;
		} else {
			return array();
		}
	}

	/**
	 * Get Current Post Type.
	 *
	 * @since 1.0.0
	 * @since 1.6.18 Get the post type on map marker info request with preview mode.
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @return string The post type.
	 */
	public function get_current(): string {
		$geodir_post_type = get_query_var( 'post_type' );

		if ( geodir_is_page( 'add-listing' ) || geodir_is_page( 'preview' ) ) {
			if ( isset( $_REQUEST['pid'] ) && $_REQUEST['pid'] != '' ) {
				$geodir_post_type = get_post_type( (int) $_REQUEST['pid'] );
			} elseif ( isset( $_REQUEST['listing_type'] ) ) {
				$geodir_post_type = sanitize_text_field( $_REQUEST['listing_type'] );
			}
		}

		if ( ( geodir_is_page( 'search' ) || geodir_is_page( 'author' ) ) && isset( $_REQUEST['stype'] ) ) {
			$geodir_post_type = sanitize_text_field( $_REQUEST['stype'] );
		}

		if ( is_tax() ) {
			$geodir_post_type = geodir_get_taxonomy_posttype();
		}

		// Retrieve post type for map marker html ajax request on preview page.
		if ( empty( $geodir_post_type ) && defined( 'DOING_AJAX' ) && ! empty( $GLOBALS['post'] ) ) {
			if ( ! empty( $GLOBALS['post']->post_type ) ) {
				$geodir_post_type = $GLOBALS['post']->post_type;
			} else if ( ! empty( $GLOBALS['post']->listing_type ) ) {
				$geodir_post_type = $GLOBALS['post']->listing_type;
			}
		}

		$all_postypes = $this->get_all();
		$all_postypes = stripslashes_deep( $all_postypes );

		if ( is_array( $all_postypes ) && ! in_array( $geodir_post_type, $all_postypes ) ) {
			$geodir_post_type = '';
		}

		if ( defined( 'DOING_AJAX' ) && isset( $_REQUEST['stype'] ) ) {
			$geodir_post_type = sanitize_text_field( $_REQUEST['stype'] );
		}

		// Set default past type on search page when stype is not set.
		if ( empty( $geodir_post_type ) && geodir_is_page( 'search' ) ) {
			$geodir_post_type = $this->get_default();
		}

		/**
		 * Filter the default CPT return.
		 *
		 * @since 1.6.9
		 */
		return apply_filters( 'geodir_get_current_posttype', $geodir_post_type );
	}

	/**
	 * Get default Post Type.
	 *
	 * @since 1.6.9
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @return string The post type.
	 */
	public function get_default(): string {
		$post_types = apply_filters( 'geodir_get_default_posttype', $this->get_all( 'object' ) );

		$stype = false;

		foreach ( $post_types as $post_type => $info ) {
			if ( $this->has_published_posts( $post_type ) ) {
				$stype = $post_type;
				break;
			}
		}

		if ( ! $stype ) {
			$stype = 'gd_place';
		}

		return $stype;
	}

	/**
	 * Get Custom Post Type info.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @param string $post_type The post type.
	 * @return bool|array Post type details.
	 */
	public function get_info( string $post_type = '' ) {
		$post_types = $this->get_all( 'array' );
		$post_types = stripslashes_deep( $post_types );

		if ( ! empty( $post_types ) && $post_type != '' && isset( $post_types[ $post_type ] ) ) {
			return $post_types[ $post_type ];
		} else {
			return false;
		}
	}

	/**
	 * Check post type has published post.
	 *
	 * @since 2.3.85
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @param string $post_type The post type.
	 * @return bool True if has posts or False.
	 */
	public function has_published_posts( string $post_type ): bool {
		$has_post = false;

		// Check global cached.
		if ( ! ( is_array( self::$cpt_has_post ) && isset( self::$cpt_has_post[ $post_type ] ) ) ) {
			$this->refresh_published_posts_cache();
		}

		if ( is_array( self::$cpt_has_post ) && isset( self::$cpt_has_post[ $post_type ] ) ) {
			$has_post = self::$cpt_has_post[ $post_type ];
		}

		return $has_post;
	}

	/**
	 * Set post type has post found.
	 *
	 * @since 2.3.85
	 * @since 3.0.0 Moved to PostTypes service.
	 */
	public function refresh_published_posts_cache(): void {
		global $wpdb;

		if ( empty( self::$cpt_has_post ) ) {
			self::$cpt_has_post = array();
		}

		$post_types = $this->get_all();

		if ( empty( $post_types ) ) {
			return;
		}

		$fields = array();
		$values = array();

		foreach ( $post_types as $post_type ) {
			$fields[] = 'post_type = %s';
			$values[] = $post_type;
		}

		$where = count( $fields ) > 1 ? "( " . implode( " OR ", $fields ) . " )" : $fields[0];

		$col = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_type FROM `{$wpdb->posts}` WHERE post_status = 'publish' AND {$where}", $values ) );

		foreach ( $post_types as $post_type ) {
			self::$cpt_has_post[ $post_type ] = ! empty( $col ) && in_array( $post_type, $col ) ? true : false;
		}
	}

	/**
	 * Returns default sorting order of a post type.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to PostTypes service. Refactored to use SortRepository.
	 *
	 * @param string $post_type The post type.
	 * @return bool|null|string Returns default sort results, when the post type is valid. Otherwise returns false.
	 */
	public function get_default_sort( string $post_type ) {
		// Check cache.
		$cache = wp_cache_get( "geodir_get_posts_default_sort_{$post_type}" );
		if ( $cache !== false ) {
			return $cache;
		}

		$default_sort = '';

		if ( $post_type != '' ) {
			$all_postypes = $this->get_all();

			if ( ! in_array( $post_type, $all_postypes ) ) {
				return false;
			}

			$field = $this->sort_repository->get_default_sort_field( $post_type );

			if ( ! empty( $field ) ) {
				if ( $field->field_type == 'random' ) {
					$default_sort = 'random';
				} else {
					$default_sort = $field->htmlvar_name . '_' . $field->sort;
				}
			}

			/**
			 * Filter post default sort options.
			 *
			 * @since 2.2.4
			 *
			 * @param string $default_sort Default sort.
			 * @param string $post_type The post type.
			 * @param object $field Field object.
			 */
			$default_sort = apply_filters( 'geodir_get_posts_default_sort_by', $default_sort, $post_type, $field );
		}

		wp_cache_set( "geodir_get_posts_default_sort_{$post_type}", $default_sort );

		return $default_sort;
	}

	/**
	 * Returns sort options of a post type.
	 *
	 * @since 1.0.0
	 * @since 3.0.0 Moved to PostTypes service. Refactored to use SortRepository.
	 *
	 * @param string $post_type The post type.
	 * @return bool|array Returns sort results, when the post type is valid. Otherwise returns false.
	 */
	public function get_sort_options( string $post_type ) {
		// Check cache.
		$cache = wp_cache_get( "geodir_get_sort_options_{$post_type}" );
		if ( $cache !== false ) {
			return $cache;
		}

		if ( $post_type != '' ) {
			$all_postypes = $this->get_all();

			if ( ! in_array( $post_type, $all_postypes ) ) {
				return false;
			}

			$sort_field_info = $this->sort_repository->get_active_sort_options( $post_type );

			/**
			 * Filter post sort options.
			 *
			 * @since 1.0.0
			 *
			 * @param array $sort_field_info Unfiltered sort field array.
			 * @param string $post_type      Post type.
			 */
			$sort_field_info = apply_filters( 'geodir_get_sort_options', $sort_field_info, $post_type );

			wp_cache_set( "geodir_get_sort_options_{$post_type}", $sort_field_info );

			return $sort_field_info;
		}

		return false;
	}

	/**
	 * Check if a post type requires an address.
	 *
	 * @since 2.3.39
	 * @since 3.0.0 Moved to PostTypes service.
	 *
	 * @param string $post_type The post type to check.
	 * @return bool Whether the post type requires an address or not.
	 */
	public function requires_address( string $post_type ): bool {
		// check if we have done this before so we don't hit the DB again.
		if ( isset( self::$cpt_requires_address[ $post_type ] ) ) {
			return self::$cpt_requires_address[ $post_type ];
		}

		// set it as default true
		$result = true;

		if ( ! empty( $post_type ) ) {
			$address_field = geodir_get_field_infoby( 'htmlvar_name', 'address', $post_type, false );
			$result = isset( $address_field['is_required'] ) && $address_field['is_required'];
			self::$cpt_requires_address[ $post_type ] = $result;
		}

		return $result;
	}

	/**
	 * Generate keywords from post title.
	 *
	 * @since 3.0.0
	 *
	 * @param bool $force True to copy all search titles. False to copy only empty search titles. Default False.
	 * @return int No. of keywords generated.
	 */
	public function generate_title_keywords( bool $force = false ): int {
		$post_types = $this->get_all();

		$generated = 0;

		// Add _search_title column in details table.
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$generated += (int) $this->generate_title_keywords_for( $post_type, $force );
			}
		}

		return $generated;
	}

	/**
	 * Generate keywords from post title for post type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $post_type The post type.
	 * @param bool $force True to copy all search titles. False to copy only empty search titles. Default False.
	 * @return int No. of keywords generated.
	 */
	public function generate_title_keywords_for( string $post_type, bool $force = false ): int {
		global $wpdb;

		// Check & add column _search_title.
		$this->check_column_search_title( $post_type );

		$table = geodir_db_cpt_table( $post_type );

		// Blank existing search titles.
		if ( $force ) {
			$wpdb->query( "UPDATE `{$table}` SET _search_title = ''" );
		}

		$generated = 0;
		$results = $wpdb->get_results( "SELECT post_id, post_title, _search_title FROM `{$table}` WHERE `post_title` != '' AND `_search_title` = '' ORDER BY `post_id` ASC" );

		if ( ! empty( $results ) ) {
			foreach ( $results as $k => $row ) {
				// Format the data query arguments.
				$data = array(
					'_search_title' => geodir_sanitize_keyword( $row->post_title, $post_type )
				);

				// Format the where query arguments.
				$where = array(
					'post_id' => $row->post_id
				);

				$result = $wpdb->update( $table, $data, $where, array( '%s' ), array( '%d' ) );

				if ( $result ) {
					$generated++;
				}
			}
		}

		return $generated;
	}

	/**
	 * Add _search_title column to detail table.
	 *
	 * @since 3.0.0
	 *
	 * @param string $post_type The post type.
	 * @return void.
	 */
	public function check_column_search_title( string $post_type ): void {
		$table = geodir_db_cpt_table( $post_type );

		geodir_add_column_if_not_exist( $table, '_search_title', "text NOT NULL AFTER `post_title`" );
	}

	/**
	 * Reorder post types by listing_order.
	 *
	 * @since 3.0.0
	 */
	public function reorder(): void {
		$post_types = get_option( 'post_types', array() );

		if ( empty( $post_types ) ) {
			return;
		}

		$temp_post_types = array();
		$temp_keys = array();

		foreach ( $post_types as $post_type => $args ) {
			if ( ! empty( $temp_post_types ) ) {
				if ( empty( $args['listing_order'] ) || ( ! empty( $args['listing_order'] ) && array_key_exists( $args['listing_order'], $temp_post_types ) ) ) {
					$args['listing_order'] = max( array_keys( $temp_post_types ) ) + 1;
				}
			} else {
				if ( empty( $args['listing_order'] ) ) {
					$args['listing_order'] = 1;
				}
			}
			$temp_post_types[ $args['listing_order'] ] = $args;
			$temp_keys[ $args['listing_order'] ] = $post_type;
		}

		ksort( $temp_post_types );

		$save_post_types = array();
		foreach ( $temp_post_types as $post_type => $args ) {
			$save_post_types[ $temp_keys[ $post_type ] ] = $args;
		}

		geodir_update_option( 'post_types', $save_post_types );
	}

	/**
	 * Check a post type's support for a given feature.
	 *
	 * @param string $post_type The post type being checked.
	 * @param string $feature   The feature being checked.
	 * @param bool $default     Default value.
	 * @return bool Whether the post type supports the given feature.
	 */
	public function supports( $post_type, $feature, $default = true ) {
		return apply_filters( 'geodir_post_type_supports', $default, $post_type, $feature );
	}
}

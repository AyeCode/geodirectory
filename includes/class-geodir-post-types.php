<?php
/**
 * Post Types
 *
 * Registers post types and taxonomies.
 *
 * @class     GeoDir_Post_types
 * @since     2.0.0
 * @package   GeoDirectory
 * @category  Class
 * @author    AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Post_types Class.
 */
class GeoDir_Post_types {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_status' ), 9 );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
		add_action( 'geodir_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );

		// Prevent Gutenberg editing GD CPTs, we only allow editing of the template pages.
		if ( version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' ) ) {
			// WP > 5 beta
			add_filter( 'use_block_editor_for_post_type', array( __CLASS__, 'disable_gutenberg' ), 101, 2 );
		} else {
			// WP < 5 beta
			add_filter( 'gutenberg_can_edit_post_type', array( __CLASS__, 'disable_gutenberg' ), 101, 2 );
		}

		add_action( 'geodir_post_type_saved', 'geodir_reorder_post_types', 999 );

		add_filter( 'geodir_post_type_supports', array( __CLASS__, 'default_supports' ), -10, 3 );
	}

	/**
	 * Disable Gutenberg for GD CPTs.
	 *
	 * @param $is_enabled
	 * @param $post_type
	 *
	 * @return bool
	 */
	public static function disable_gutenberg( $is_enabled, $post_type) {
		if ( in_array( $post_type, geodir_get_posttypes() ) ) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Register core taxonomies.
	 *
	 * @since 2.0.0
	 */
	public static function register_taxonomies() {
		if ( ! is_blog_installed() ) {
			return;
		}

		if ( taxonomy_exists( 'gd_placecategory' ) ) {
			return;
		}

		do_action( 'geodirectory_register_taxonomy' );

		$taxonomies = self::get_taxonomy_defaults();

		// If custom taxonomies are present, register them
		if ( is_array( $taxonomies ) ) {
			// Sort taxonomies
			ksort( $taxonomies );

			// Register taxonomies
			foreach ( $taxonomies as $taxonomy => $args ) {
				// Allow taxonomy names to be translated
				if ( ! empty( $args['args']['labels'])) {
					foreach ( $args['args']['labels'] as $key => $tax_label ) {
						$args['args']['labels'][ $key ] = __( $tax_label, 'geodirectory' );
					}
				}

				/**
				 * Filter taxonomy args.
				 *
				 * @since @todo
				 * @param string $args Taxonomy args.
				 * @param string $taxonomy The taxonomy name.
				 * @param string[] $object_type Array of names of object types for the taxonomy.
				 */
				$args = apply_filters( 'geodir_taxonomy_args', $args, $taxonomy, $args['object_type'] );

				register_taxonomy( $taxonomy, $args['object_type'], $args['args']);

				if ( taxonomy_exists( $taxonomy ) ) {
					register_taxonomy_for_object_type( $taxonomy, $args['object_type'] );
				}
			}
		}

		do_action( 'geodirectory_after_register_taxonomy' );
	}

	/**
	 * Get the post type defaults.
	 *
	 * @since 2.0.0
	 */
	private static function get_post_type_defaults() {
		$post_types = geodir_get_option( 'post_types', array() );

		if ( empty( $post_types ) ) {
			$listing_slug = 'places';

			$labels = array(
				'name'               => __( 'Places', 'geodirectory' ),
				'singular_name'      => __( 'Place', 'geodirectory' ),
				'add_new'            => __( 'Add New', 'geodirectory' ),
				'add_new_item'       => __( 'Add New Place', 'geodirectory' ),
				'edit_item'          => __( 'Edit Place', 'geodirectory' ),
				'new_item'           => __( 'New Place', 'geodirectory' ),
				'view_item'          => __( 'View Place', 'geodirectory' ),
				'search_items'       => __( 'Search Places', 'geodirectory' ),
				'not_found'          => __( 'No Place Found', 'geodirectory' ),
				'not_found_in_trash' => __( 'No Place Found In Trash', 'geodirectory' )
			);

			$place_default = array(
				'labels'          => $labels,
				'can_export'      => true,
				'capability_type' => 'post',
				'description'     => 'Place post type.',
				'has_archive'     => $listing_slug,
				'hierarchical'    => false,  // Hierarchical causes memory issues - WP loads all records!
				'map_meta_cap'    => true,
				'menu_icon'       => 'dashicons-location-alt',
				'public'          => true,
				'query_var'       => true,
				'rewrite'         => array(
					'slug'         => $listing_slug,
					'with_front'   => false,
					'hierarchical' => true,
					'feeds'        => true
				),
				'supports'        => array(
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'comments',
					'revisions'
				),
				'taxonomies'      => array( 'gd_placecategory', 'gd_place_tags' )
			);

			// Update custom post types
			$post_types['gd_place'] = $place_default;
			geodir_update_option( 'post_types', $post_types );
		}

		return $post_types;
	}

	/**
	 * Get the taxonomy defaults.
	 *
	 * @since 2.0.0
	 */
	private static function get_taxonomy_defaults() {
		$taxonomies = geodir_get_option( 'taxonomies', array() );
		$post_types = geodir_get_option( 'post_types', array() );

		if ( empty( $taxonomies ) ) {
			$listing_slug = isset( $post_types['gd_place']['rewrite']['slug']) ? $post_types['gd_place']['rewrite']['slug'] : 'places';
			$singular_name = isset( $post_types['gd_place']['labels']['singular_name']) ? $post_types['gd_place']['labels']['singular_name'] : 'Place';

			// Place tags
			$gd_placetags = array();
			$gd_placetags['object_type'] = 'gd_place';
			$gd_placetags['listing_slug'] = $listing_slug . '/tags';
			$gd_placetags['args'] = array(
				'public' => true,
				'hierarchical' => false,
				'rewrite' => array( 'slug' => $listing_slug . '/tags', 'with_front' => false, 'hierarchical' => true),
				'query_var' => true,
				'labels' => array(
					'name' => wp_sprintf( __( '%s Tags', 'geodirectory' ), $singular_name ),
					'singular_name' => wp_sprintf( __( '%s Tag', 'geodirectory' ), $singular_name ),
					'search_items' => wp_sprintf( __( 'Search %s Tags', 'geodirectory' ), $singular_name ),
					'popular_items' => wp_sprintf( __( 'Popular %s Tags', 'geodirectory' ), $singular_name ),
					'all_items' => wp_sprintf( __( 'All %s Tags', 'geodirectory' ), $singular_name ),
					'edit_item' => wp_sprintf( __( 'Edit %s Tag', 'geodirectory' ), $singular_name ),
					'update_item' => wp_sprintf( __( 'Update %s Tag', 'geodirectory' ), $singular_name ),
					'add_new_item' => wp_sprintf( __( 'Add New %s Tag', 'geodirectory' ), $singular_name ),
					'new_item_name' => wp_sprintf( __( 'New %s Tag Name', 'geodirectory' ), $singular_name ),
					'add_or_remove_items' => wp_sprintf( __( 'Add or remove %s tags', 'geodirectory' ), $singular_name ),
					'choose_from_most_used' => wp_sprintf( __( 'Choose from the most used %s tags', 'geodirectory' ), $singular_name ),
					'separate_items_with_commas' => wp_sprintf( __( 'Separate %s tags with commas', 'geodirectory' ), $singular_name ),
				),
			);

			// Place Category
			$gd_placecategory = array();
			$gd_placecategory['object_type'] = 'gd_place';
			$gd_placecategory['listing_slug'] = $listing_slug;
			$gd_placecategory['args'] = array(
				'public' => true,
				'hierarchical' => true,
				'rewrite' => array( 'slug' => $listing_slug, 'with_front' => false, 'hierarchical' => true),
				'query_var' => true,
				'labels' => array(
					'name' => wp_sprintf( __( '%s Categories', 'geodirectory' ), $singular_name ),
					'singular_name' => wp_sprintf( __( '%s Category', 'geodirectory' ), $singular_name ),
					'search_items' => wp_sprintf( __( 'Search %s Categories', 'geodirectory' ), $singular_name ),
					'popular_items' => wp_sprintf( __( 'Popular %s Categories', 'geodirectory' ), $singular_name ),
					'all_items' => wp_sprintf( __( 'All %s Categories', 'geodirectory' ), $singular_name ),
					'edit_item' => wp_sprintf( __( 'Edit %s Category', 'geodirectory' ), $singular_name ),
					'update_item' => wp_sprintf( __( 'Update %s Category', 'geodirectory' ), $singular_name ),
					'add_new_item' => wp_sprintf( __( 'Add New %s Category', 'geodirectory' ), $singular_name ),
					'new_item_name' => wp_sprintf( __( 'New %s Category', 'geodirectory' ), $singular_name ),
					'add_or_remove_items' => wp_sprintf( __( 'Add or remove %s categories', 'geodirectory' ), $singular_name ),
				),
			);

			$taxonomies['gd_place_tags'] = $gd_placetags;
			$taxonomies['gd_placecategory'] = $gd_placecategory;
			geodir_update_option( 'taxonomies', $taxonomies );
		}

		// Loop the taxonomies
		if ( ! empty( $taxonomies ) ) {
			$tag_slug = geodir_get_option( 'permalink_tag_base','tags' );
			$cat_slug = geodir_get_option( 'permalink_category_base','category' );

			foreach( $taxonomies as $key => $taxonomy) {
				// add capability to assign terms to any user, if not added then subscribers listings wont have terms
				$taxonomies[$key]['args']['capabilities']['assign_terms'] = 'read';

				// adjust rewrite rules _tags
				$listing_slug = isset( $post_types[ $taxonomy['object_type'] ]['rewrite']['slug']) ? $post_types[ $taxonomy['object_type'] ]['rewrite']['slug'] : 'places';
				if ( stripos( strrev( $key ), "sgat_" ) === 0 ) { // its a tag
					$taxonomies[ $key ]['args']['rewrite']['slug'] = $tag_slug ? $listing_slug . '/' . $tag_slug : $listing_slug;
				} else {// its a category
					$taxonomies[ $key ]['args']['rewrite']['slug'] = $cat_slug ? $listing_slug . '/' . $cat_slug : $listing_slug;
				}

				// Dynamically create the labels from the CPT labels
				$singular_name = isset( $post_types[ $taxonomy['object_type'] ]['labels']['singular_name']) ? $post_types[ $taxonomy['object_type'] ]['labels']['singular_name'] : 'Place';
				if ( stripos( strrev( $key ), "sgat_" ) === 0 ) { // its a tag
					$taxonomies[ $key ]['args']['labels'] = array(
						'name' => wp_sprintf( __( '%s Tags', 'geodirectory' ), $singular_name ),
						'singular_name' => wp_sprintf( __( '%s Tag', 'geodirectory' ), $singular_name ),
						'search_items' => wp_sprintf( __( 'Search %s Tags', 'geodirectory' ), $singular_name ),
						'popular_items' => wp_sprintf( __( 'Popular %s Tags', 'geodirectory' ), $singular_name ),
						'all_items' => wp_sprintf( __( 'All %s Tags', 'geodirectory' ), $singular_name ),
						'edit_item' => wp_sprintf( __( 'Edit %s Tag', 'geodirectory' ), $singular_name ),
						'update_item' => wp_sprintf( __( 'Update %s Tag', 'geodirectory' ), $singular_name ),
						'add_new_item' => wp_sprintf( __( 'Add New %s Tag', 'geodirectory' ), $singular_name ),
						'new_item_name' => wp_sprintf( __( 'New %s Tag Name', 'geodirectory' ), $singular_name ),
						'add_or_remove_items' => wp_sprintf( __( 'Add or remove %s tags', 'geodirectory' ), $singular_name ),
						'choose_from_most_used' => wp_sprintf( __( 'Choose from the most used %s tags', 'geodirectory' ), $singular_name ),
						'separate_items_with_commas' => wp_sprintf( __( 'Separate %s tags with commas', 'geodirectory' ), $singular_name ),
					);
				} else { // its a category
					$taxonomies[ $key ]['args']['labels'] = array(
						'name' => wp_sprintf( __( '%s Categories', 'geodirectory' ), $singular_name ),
						'singular_name' => wp_sprintf( __( '%s Category', 'geodirectory' ), $singular_name ),
						'search_items' => wp_sprintf( __( 'Search %s Categories', 'geodirectory' ), $singular_name ),
						'popular_items' => wp_sprintf( __( 'Popular %s Categories', 'geodirectory' ), $singular_name ),
						'all_items' => wp_sprintf( __( 'All %s Categories', 'geodirectory' ), $singular_name ),
						'edit_item' => wp_sprintf( __( 'Edit %s Category', 'geodirectory' ), $singular_name ),
						'update_item' => wp_sprintf( __( 'Update %s Category', 'geodirectory' ), $singular_name ),
						'add_new_item' => wp_sprintf( __( 'Add New %s Category', 'geodirectory' ), $singular_name ),
						'new_item_name' => wp_sprintf( __( 'New %s Category', 'geodirectory' ), $singular_name ),
						'add_or_remove_items' => wp_sprintf( __( 'Add or remove %s categories', 'geodirectory' ), $singular_name ),
					);
				}
			}
		}

		// Add rewrite rules
		return $taxonomies;
	}

	/**
	 * Register core post types.
	 *
	 * @since 2.0.0
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'gd_place' ) ) {
			return;
		}

		do_action( 'geodirectory_register_post_type' );

		/**
		 * Get available custom posttypes and taxonomies and register them.
		 */
		_x( 'places', 'URL slug', 'geodirectory' );

		$post_types = self::get_post_type_defaults();

		// Register each post type if array of data is returned
		if ( is_array( $post_types ) ):
			foreach ( $post_types as $post_type => $args ):
				if ( ! empty( $args['rewrite']['slug'] ) ) {
					$args['rewrite']['slug'] = apply_filters( 'geodir_post_type_rewrite_slug', $args['rewrite']['slug'], $post_type );
				}

				$args = stripslashes_deep( $args);

				if ( ! empty( $args['labels'] ) ) {
					foreach ( $args['labels'] as $key => $val) {
						$args['labels'][ $key ] = __( $val, 'geodirectory' );// Allow translation
					}
				}

				// Force support post revisions
				$args['supports'][] = 'revisions';

				// Force to show above GD main menu item
				$args['show_ui'] = true;
				$args['show_in_menu'] = true;
				$listing_order = isset( $args['listing_order']) ? $args['listing_order'] : 1;
				$args['menu_position'] = "56.2". $listing_order ;

				/**
				 * Filter post type args.
				 *
				 * @since 1.0.0
				 * @param string $args Post type args.
				 * @param string $post_type The post type.
				 */
				$args = apply_filters( 'geodir_post_type_args', $args, $post_type );

				register_post_type( $post_type, $args );
			endforeach;
		endif;

		do_action( 'geodirectory_after_register_post_type' );
	}

	/**
	 * Register our custom post statuses, used for listing status.
	 *
	 * @since 2.0.0
	 */
	public static function register_post_status() {
		$listing_statuses = geodir_register_custom_statuses();

		foreach ( $listing_statuses as $listing_status => $values ) {
			register_post_status( $listing_status, $values );
		}
	}

	/**
	 * Flush rewrite rules.
	 *
	 * @since 2.0.0
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}

	/**
	 * Added product for Jetpack related posts.
	 *
	 * @since 2.0.0
	 *
	 * @param  array $post_types Post types.
	 * @return array $post_types.
	 */
	public static function rest_api_allowed_post_types( $post_types ) {
		$post_types[] = 'gd_place';

		return $post_types;
	}

	public static function sanitize_menu_icon( $icon ) {
		if ( empty( $icon ) ) {
			return NULL;
		}

		if ( strpos( $icon, 'dashicons-' ) === false ) {
			$icon = 'dashicons-' . $icon;
		}

		return $icon;
	}

	/**
	 * Check a post type's support for a given feature.
	 *
	 * @param string $post_type The post type being checked.
	 * @param string $feature   The feature being checked.
	 * @param bool $default     Default value.
	 * @return bool Whether the post type supports the given feature.
	 */
	public static function supports( $post_type, $feature, $default = true ) {
		return apply_filters( 'geodir_post_type_supports', $default, $post_type, $feature );
	}

	/**
	 * Set default post type's support for a given feature.
	 *
	 * @param bool $value       True if supports else False.
	 * @param string $post_type The post type being checked.
	 * @param string $feature   The feature being checked.
	 * @return bool Whether the post type supports the given feature.
	 */
	public static function default_supports( $value, $post_type, $feature ) {
		if ( ! is_scalar( $post_type ) ) {
			return $value;
		}

		$post_types = geodir_get_posttypes( 'array' );

		switch ( $feature ) {
			case 'events':
				if ( isset( $post_types[ $post_type ] ) && isset( $post_types[ $post_type ]['supports_events']) && $post_types[ $post_type ]['supports_events'] ) {
					$value = defined( 'GEODIR_EVENT_VERSION' ) ? true : false;
				} else {
					$value = false;
				}
				break;
			case 'business_hours':
			case 'featured':
			case 'special_offers':
			case 'service_distance':
			case 'private_address':
				$cf = geodir_get_field_infoby( 'htmlvar_name', $feature, $post_type );
				if ( ! empty( $cf ) && ! empty( $cf['is_active'] ) ) {
					$value = true;
				} else {
					$value = false;
				}
				break;
			case 'comments':
				if ( isset( $post_types[ $post_type ] ) && ! empty( $post_types[ $post_type ][ 'disable_comments' ] ) ) {
					$value = false;
				}else{
					$value = true;
				}
				break;
			case 'single_review':
				if ( isset( $post_types[ $post_type ] ) && ! empty( $post_types[ $post_type ][ 'single_review' ] ) ) {
					$value = true;
				}else{
					$value = false;
				}
				break;
		}

		return $value;
	}

	/**
	 * Get the post type rewrite slug.
	 *
	 * @param string $post_type The post type being checked.
	 * @param object $post_type_obj   The post type object.
	 * @return string The post type slug.
	 */
	public static function get_rewrite_slug( $post_type, $post_type_obj = NULL ) {
		if ( empty( $post_type_obj ) || ! is_object( $post_type_obj ) ) {
			$post_type_obj = geodir_post_type_object( $post_type );
		}

		$slug = '';
		if ( empty( $post_type_obj ) ) {
			return $slug;
		}

		if ( ! empty( $post_type_obj->rewrite ) ) {
			if ( is_array( $post_type_obj->rewrite ) && ! empty( $post_type_obj->rewrite['slug'] ) ) {
				$slug = trim( $post_type_obj->rewrite['slug'], '/' );
			} else if ( is_object( $post_type_obj->rewrite ) && ! empty( $post_type_obj->rewrite->slug ) ) {
				$slug = trim( $post_type_obj->rewrite->slug, '/' );
			}
		} else {
			if ( ! empty( $post_type_obj->has_archive ) ) {
				$slug = $post_type_obj->has_archive;
			} else if ( ! empty( $post_type_obj->name ) ) {
				$slug = $post_type_obj->name;
			}
		}

		return $slug;
	}
}

GeoDir_Post_types::init();

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
		add_filter( 'gutenberg_can_edit_post_type', array( __CLASS__, 'disable_gutenberg'), 10, 2 );
	}

	/**
	 * Disable Gutenberg for GD CPTs.
	 * 
	 * @param $is_enabled
	 * @param $post_type
	 *
	 * @return bool
	 */
	public function disable_gutenberg($is_enabled, $post_type){
		if (in_array($post_type, geodir_get_posttypes())) {
			return false;
		}

		return $is_enabled;
	}

	/**
	 * Register core taxonomies.
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
		if (is_array($taxonomies)) {
			// Sort taxonomies
			ksort($taxonomies);

			// Register taxonomies
			foreach ($taxonomies as $taxonomy => $args) {
				// Allow taxonomy names to be translated
				if (!empty($args['args']['labels'])) {
					foreach ($args['args']['labels'] as $key => $tax_label) {
						$args['args']['labels'][$key] = __($tax_label, 'geodirectory');
					}
				}

				//print_r($args['args']);

				register_taxonomy($taxonomy, $args['object_type'], $args['args']);

				if (taxonomy_exists($taxonomy)) {
					register_taxonomy_for_object_type($taxonomy, $args['object_type']);
				}
			}
		}


		do_action( 'geodirectory_after_register_taxonomy' );
	}

	/**
	 * Get the post type defaults.
	 */
	private static function get_post_type_defaults() {

		$post_types = geodir_get_option('post_types', array());
		if(empty($post_types)) {

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
					'revisions',
//					'post-formats'
				),
				'taxonomies'      => array( 'gd_placecategory', 'gd_place_tags' )
			);

			//Update custom post types
			$post_types['gd_place'] = $place_default;
			geodir_update_option( 'post_types', $post_types );

		}
		
		return $post_types;

	}

	/**
	 * Get the taxonomy defaults.
	 */
	private static function get_taxonomy_defaults() {

		$taxonomies = geodir_get_option('taxonomies', array());
		$post_types = geodir_get_option('post_types', array());
		if(empty($taxonomies)){


			$listing_slug = isset($post_types['gd_place']['rewrite']['slug']) ? $post_types['gd_place']['rewrite']['slug'] : 'places';


			// Place tags
			$gd_placetags = array();
			$gd_placetags['object_type'] = 'gd_place';
			$gd_placetags['listing_slug'] = $listing_slug . '/tags';
			$gd_placetags['args'] = array(
				'public' => true,
				'hierarchical' => false,
				'rewrite' => array('slug' => $listing_slug . '/tags', 'with_front' => false, 'hierarchical' => true),
				'query_var' => true,

				'labels' => array(
					'name' => __('Place Tags', 'geodirectory'),
					'singular_name' => __('Place Tag', 'geodirectory'),
					'search_items' => __('Search Place Tags', 'geodirectory'),
					'popular_items' => __('Popular Place Tags', 'geodirectory'),
					'all_items' => __('All Place Tags', 'geodirectory'),
					'edit_item' => __('Edit Place Tag', 'geodirectory'),
					'update_item' => __('Update Place Tag', 'geodirectory'),
					'add_new_item' => __('Add New Place Tag', 'geodirectory'),
					'new_item_name' => __('New Place Tag Name', 'geodirectory'),
					'add_or_remove_items' => __('Add or remove Place tags', 'geodirectory'),
					'choose_from_most_used' => __('Choose from the most used Place tags', 'geodirectory'),
					'separate_items_with_commas' => __('Separate Place tags with commas', 'geodirectory'),
				),
			);


			// Place Category
			$gd_placecategory = array();
			$gd_placecategory['object_type'] = 'gd_place';
			$gd_placecategory['listing_slug'] = $listing_slug;
			$gd_placecategory['args'] = array(
				'public' => true,
				'hierarchical' => true,
				'rewrite' => array('slug' => $listing_slug, 'with_front' => false, 'hierarchical' => true),
				'query_var' => true,
				'labels' => array(
					'name' => __('Place Categories', 'geodirectory'),
					'singular_name' => __('Place Category', 'geodirectory'),
					'search_items' => __('Search Place Categories', 'geodirectory'),
					'popular_items' => __('Popular Place Categories', 'geodirectory'),
					'all_items' => __('All Place Categories', 'geodirectory'),
					'edit_item' => __('Edit Place Category', 'geodirectory'),
					'update_item' => __('Update Place Category', 'geodirectory'),
					'add_new_item' => __('Add New Place Category', 'geodirectory'),
					'new_item_name' => __('New Place Category', 'geodirectory'),
					'add_or_remove_items' => __('Add or remove Place categories', 'geodirectory'),
				),
			);


			$taxonomies['gd_place_tags'] = $gd_placetags;
			$taxonomies['gd_placecategory'] = $gd_placecategory;
			geodir_update_option('taxonomies', $taxonomies);

		}


		// loop the taxonomies
		if(!empty($taxonomies)){
			$tag_slug = geodir_get_option('permalink_tag_base','tags');
			$cat_slug = geodir_get_option('permalink_category_base','category');
			foreach($taxonomies as $key => $taxonomy){

				// add capability to assign terms to any user, if not added then subscribers listings wont have terms
				$taxonomies[$key]['args']['capabilities']['assign_terms'] = 'read';

				// adjust rewrite rules _tags
				$listing_slug = isset($post_types[$taxonomy['object_type']]['rewrite']['slug']) ? $post_types[$taxonomy['object_type']]['rewrite']['slug'] : 'places';
				if(stripos(strrev($key), "sgat_") === 0){ // its a tag
					$taxonomies[$key]['args']['rewrite']['slug'] = $tag_slug ? $listing_slug.'/'.$tag_slug : $listing_slug;
				}else{// its a category
					$taxonomies[$key]['args']['rewrite']['slug'] = $cat_slug ? $listing_slug.'/'.$cat_slug : $listing_slug;
				}
			}
		}

		// add rewrite rules



		return $taxonomies;
	}

	/**
	 * Register core post types.
	 */
	public static function register_post_types() {
		if ( ! is_blog_installed() || post_type_exists( 'gd_place' ) ) {
			return;
		}

		do_action( 'geodirectory_register_post_type' );


		/**
		 * Get available custom posttypes and taxonomies and register them.
		 */
		_x('places', 'URL slug', 'geodirectory');

		$post_types = self::get_post_type_defaults();

		// Register each post type if array of data is returned
		if (is_array($post_types)):

			foreach ($post_types as $post_type => $args):

				if (!empty($args['rewrite']['slug'])) {
					$args['rewrite']['slug'] = _x($args['rewrite']['slug'], 'URL slug', 'geodirectory');
				}
				$args = stripslashes_deep($args);

				if (!empty($args['labels'])) {
					foreach ($args['labels'] as $key => $val) {
						$args['labels'][$key] = __($val, 'geodirectory');// allow translation
					}
				}

				// force support post revisions
				$args['supports'][] = 'revisions';

				/**
				 * Filter post type args.
				 *
				 * @since 1.0.0
				 * @param string $args Post type args.
				 * @param string $post_type The post type.
				 */
				$args = apply_filters('geodir_post_type_args', $args, $post_type);

				register_post_type($post_type, $args);

			endforeach;
		endif;


		do_action( 'geodirectory_after_register_post_type' );
	}
	
	/**
	 * Register our custom post statuses, used for listing status.
	 */
	public static function register_post_status() {

		$listing_statuses = apply_filters( 'geodir_register_post_statuses',
			array(
				'gd-closed'    => array(
					'label'                     => _x( 'Closed down', 'Listing status', 'geodirectory' ),
					'public'                    => false,
					'exclude_from_search'       => true,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'label_count'               => _n_noop( 'Closed down <span class="count">(%s)</span>', 'Closed down <span class="count">(%s)</span>', 'geodirectory' ),
				)
			)
		);

		foreach ( $listing_statuses as $listing_status => $values ) {
			register_post_status( $listing_status, $values );
		}
	}

	/**
	 * Flush rewrite rules.
	 */
	public static function flush_rewrite_rules() {
		flush_rewrite_rules();
	}


	/**
	 * Added product for Jetpack related posts.
	 *
	 * @param  array $post_types
	 * @return array
	 */
	public static function rest_api_allowed_post_types( $post_types ) {
		$post_types[] = 'gd_place';

		return $post_types;
	}

}
GeoDir_Post_types::init();
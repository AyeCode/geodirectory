<?php
/**
 * Permalinks
 *
 * Setup GD permalinks.
 *
 * @class     GeoDir_Permalinks
 * @since     2.0.0
 * @package   GeoDirectory
 * @category  Class
 * @author    AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Permalinks Class.
 */
class GeoDir_Permalinks {

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 5 );
		add_action( 'init', array( __CLASS__, 'register_post_types' ), 5 );
		add_filter( 'rest_api_allowed_post_types', array( __CLASS__, 'rest_api_allowed_post_types' ) );
		add_action( 'geodirectory_flush_rewrite_rules', array( __CLASS__, 'flush_rewrite_rules' ) );
		add_filter( 'post_type_link', array( __CLASS__, 'post_permalink_structure'), 10, 4);
		//add_action( 'registered_post_type', array( __CLASS__, 'register_post_type_rules' ), 10, 2 );

		add_action('init', array( __CLASS__, 'rewrite_tags'), 10, 0);
		add_action('init', array( __CLASS__, 'rewrite_rules'), 10, 0);

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
					/*'revisions', 'post-formats'*/
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
		if(empty($taxonomies)){

			$post_types = geodir_get_option('post_types', array());
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

//	$comment_post_cache = array();
//	$gd_permalink_cache = array();
	/**
	 * Returns permalink structure using post link.
	 *
	 * @since 1.0.0
	 * @since 1.5.9 Fix the broken links when domain name contain CPT and home page
	 *              is set to current location.
	 * @since 1.6.18 Fix with WPML the location terms added twice when CPT slug is translated.
	 *
	 * @package GeoDirectory
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global object $wp_query WordPress Query object.
	 * @global object $post WordPress Post object.
	 * @param string $post_link The post link.
	 * @param object $post_obj The post object.
	 * @param string $leavename Not yet implemented.
	 * @param bool $sample Is this a sample post?.
	 * @return string The post link.
	 */
	public static function post_permalink_structure($post_link, $post_obj, $leavename, $sample)
	{
		//echo '###'.$post_link."<br />".$sample." \n" ;
		//print_r($post_obj);


		global $wpdb, $wp_query, $plugin_prefix, $post, $comment_post_cache, $gd_permalink_cache;


		if (isset($post_obj->post_status) && ( $post_obj->post_status == 'auto-draft' || $post_obj->post_status == 'draft' || $post_obj->post_status == 'pending') ) {
			return $post_link; // if draft then return default url.
		} elseif (isset($post_obj->ID) && isset($post->ID) && $post_obj->ID == $post->ID) {
			// check its the correct post.
		} else {
			// backup the original post data first so we can restore it later
			$orig_post = $post;
			$post = $post_obj;
		}

		// Only modify if its a GD post type.
		if (in_array($post->post_type, geodir_get_posttypes())) {


			/*
			 * Available permalink tags:
			 * %country% , %region% , %city% , %category% , %postname% , %post_id%
			 */

			// Check if a pretty permalink is required
			$permalink_structure = geodir_get_option( 'permalink_structure' );
			if (strpos($permalink_structure, '%postname%') === false || empty($permalink_structure)) {
				if (isset($orig_post)) {
					$post = $orig_post;
				}
				return $post_link;
			}


			// if we don't the GD post info then get it.
			if(!isset($post->default_category)){
				$gd_post = geodir_get_post_info($post->ID);
				if(!empty($gd_post)){
					$post = $gd_post;
				}
			}

			if($post->post_type == 'revision'){
				$post->post_type = get_post_type(wp_get_post_parent_id($post->ID));
			}

			/*
			 * Get the site url. ( without any location filters )
			 */
			if (function_exists('geodir_location_geo_home_link')) {
				remove_filter('home_url', 'geodir_location_geo_home_link', 100000);
			}

			$permalink = trailingslashit(get_bloginfo('url'));

			if (function_exists('geodir_location_geo_home_link')) {
				add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
			}



			/*
			 * Add the CPT slug.
			 */
			$post_types = geodir_get_posttypes('array');
			$cpt_slug = $post_types[$post->post_type]['rewrite']['slug'];

			// Alter the CPT slug if WPML is set to do so
			if(geodir_wpml_is_post_type_translated($post->post_type)){
				if ( gd_wpml_slug_translation_turned_on( $post->post_type ) && $language_code = gd_wpml_get_lang_from_url($post_link)) {

					$org_slug = $cpt_slug;
					$cpt_slug = apply_filters( 'wpml_translate_single_string',
						$cpt_slug,
						'WordPress',
						'URL slug: ' . $cpt_slug,
						$language_code);

					if(!$cpt_slug){$cpt_slug = $org_slug;}

				}
			}

			$permalink .= $cpt_slug.$permalink_structure;


			/*
			 * Add Country if needed. (%country%)
			 */
			if (strpos($permalink, '%country%') !== false) {
				$locations = self::get_post_location_slugs($post);
				if(isset($locations->country_slug) && $locations->country_slug){
					$permalink = str_replace('%country%',$locations->country_slug,$permalink);
				}
			}

			/*
			 * Add Region if needed. (%region%)
			 */
			if (strpos($permalink, '%region%') !== false) {
				$locations = isset($locations) ? $locations : self::get_post_location_slugs($post);
				if(isset($locations->region_slug) && $locations->region_slug){
					$permalink = str_replace('%region%',$locations->region_slug,$permalink);
				}
			}

			/*
			 * Add City if needed. (%city%)
			 */
			if (strpos($permalink, '%city%') !== false) {
				$locations = isset($locations) ? $locations : self::get_post_location_slugs($post);
				if(isset($locations->city_slug) && $locations->city_slug){
					$permalink = str_replace('%city%',$locations->city_slug,$permalink);
				}
			}

			/*
			 * Add Category if needed. (%category%)
			 */
			if (strpos($permalink, '%category%') !== false) {
				if(isset($post->default_category) && $post->default_category){
					$term = get_term_by('id', absint($post->default_category), $post->post_type."category");
				}elseif(isset($post->post_categories) && $post->post_categories){
					$cat_id = explode(",", trim($post->post_categories, ","));
					$cat_id = !empty($cat_id) ? absint($cat_id [0]) : 0;
					if($cat_id){
						$term = get_term_by('id', $cat_id, $post->post_type."category");
					}
				}

				if(isset($term) && $term->slug){
					$permalink = str_replace('%category%',$term->slug,$permalink);
				}
			}

			/*
			 * Add post name if needed. (%postname%)
			 */
			if (strpos($permalink, '%postname%') !== false) {
				$permalink = str_replace('%postname%',$post->post_name,$permalink);
			}

			/*
			 * Add post ID if needed. (%post_id%)
			 */
			if (strpos($permalink, '%post_id%') !== false) {
				$permalink = str_replace('%post_id%',$post->ID,$permalink);
			}

			//echo $permalink;
			$post_link = $permalink;

			// @todo we will com back to cache
//			if (isset($comment_post_cache[$post->ID])) {
//				$post = $comment_post_cache[$post->ID];
//			}
//			if (isset($gd_permalink_cache[$post->ID]) && $gd_permalink_cache[$post->ID] && !$sample) {
//				$post_id = $post->ID;
//				if (isset($orig_post)) {
//					$post = $orig_post;
//				}
//				return $gd_permalink_cache[$post_id];
//			}



			// temp cache the permalink
			if (!$sample && (!isset($_REQUEST['geodir_ajax']) || (isset($_REQUEST['geodir_ajax']) && $_REQUEST['geodir_ajax'] != 'add_listing'))) {
				$gd_permalink_cache[$post->ID] = $post_link;
			}
		}
		if (isset($orig_post)) {
			$post = $orig_post;
		}

		return $post_link;
	}

	private static function get_post_location_slugs($post){
		return apply_filters('geodir_post_permalinks',geodir_get_default_location(), $post);

	}

	/**
	 * Register GD rewrite rules.
	 */
	public static function rewrite_rules() {
		$permalink_structure = geodir_get_option( 'permalink_structure' );

		$post_types = geodir_get_posttypes('array');

		if(!empty($post_types) && !empty($permalink_structure)){

			$permalink_arr = explode("/",trim($permalink_structure,"/"));

			foreach($post_types as $cpt => $post_type){
				
				// add the post single permalinks
				$regex = '^'.$post_type['rewrite']['slug'].'/'.implode("", array_fill(0,count($permalink_arr ),'([^/]*)/')).'?';
				$redirect = 'index.php?';
				$match = 1;
				foreach($permalink_arr as $tag){
					$tag = trim($tag,"%");
					if( $tag == "postname"){
						$redirect .= "&$cpt=".'$matches['.$match.']';
					}else{
						$redirect .= "&".trim($tag,"%").'=$matches['.$match.']';
					}
					$match++;
				}
				add_rewrite_rule($regex,$redirect,'top');
			}
		}

		// add search paging rewrite // @todo we need to replace search with the current GD search page slug
		add_rewrite_rule( 'search/page/([^/]+)/?', 'index.php?paged=$matches[1]', 'top' );
	}

	/**
	 * Add GD rewrite tags.
	 */
	public static function rewrite_tags(){
		add_rewrite_tag('%country%', '([^&]+)');
		add_rewrite_tag('%region%', '([^&]+)');
		add_rewrite_tag('%city%', '([^&]+)');
		add_rewrite_tag('%category%', '([^&]+)');
	}


}

GeoDir_Permalinks::init();

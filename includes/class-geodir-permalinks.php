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

		add_filter( 'post_type_link', array( __CLASS__, 'post_permalink_structure'), 10, 4);
		//add_action( 'registered_post_type', array( __CLASS__, 'register_post_type_rules' ), 10, 2 );

		add_action('init', array( __CLASS__, 'rewrite_tags'), 10, 0);
		add_action('init', array( __CLASS__, 'rewrite_rules'), 10, 0);


		//add_action('init', array( __CLASS__, 'author_cpt_rules'), 10, 0);

		add_filter( 'author_rewrite_rules', array( __CLASS__, 'author_cpt_rules' ) );
		//add_action( 'author_rewrite_rules', array( __CLASS__, 'author_cpt_rules' ) );





	}

	

	/**
	 * Add author page pretty urls.
	 *
	 * @param $rules
	 *
	 * @return mixed
	 */
	public static function author_cpt_rules( $rules ){
		global $wp_rewrite;

		$post_types = geodir_get_posttypes( 'array' );

		if(!empty($post_types)){
			foreach($post_types as $post_type => $cpt){

				$cpt_slug = isset($cpt['rewrite']['slug']) ? $cpt['rewrite']['slug'] : '';
				$saves_slug = self::favs_slug( $cpt_slug );

				// add CPT author rewrite rules
				$rules[$wp_rewrite->author_base."/([^/]+)/$cpt_slug/?$"] = 'index.php?author_name=$matches[1]&post_type='.$post_type;
				$rules[$wp_rewrite->author_base."/([^/]+)/$cpt_slug/page/?([0-9]{1,})/?$"] = 'index.php?author_name=$matches[1]&post_type='.$post_type.'&paged=$matches[2]';

				// favs
				$rules[$wp_rewrite->author_base."/([^/]+)/$saves_slug/?$"] = 'index.php?author_name=$matches[1]&gd_favs=1';
				$rules[$wp_rewrite->author_base."/([^/]+)/$saves_slug/page/?([0-9]{1,})/?$"] = 'index.php?author_name=$matches[1]&gd_favs=1&paged=$matches[2]';
				$rules[$wp_rewrite->author_base."/([^/]+)/$saves_slug/$cpt_slug/?$"] = 'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type;
				$rules[$wp_rewrite->author_base."/([^/]+)/$saves_slug/$cpt_slug/page/?([0-9]{1,})/?$"] = 'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type.'&paged=$matches[2]';
			}
		}

		//if(is_admin()){print_r($rules );exit;} // for testing

		return $rules;
	}

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


		global $wpdb, $wp_query, $plugin_prefix, $post, $comment_post_cache, $gd_permalink_cache,$gd_post;

		//print_r($gd_post);

		if (isset($post_obj->post_status) && ( $post_obj->post_status == 'auto-draft' || $post_obj->post_status == 'draft' || $post_obj->post_status == 'pending') ) {
			return $post_link; // if draft then return default url.
		} elseif (isset($post_obj->ID) && isset($gd_post->ID) && $post_obj->ID == $gd_post->ID) {
			// check its the correct post.
		} else {
			// backup the original post data first so we can restore it later
			$orig_post = $gd_post;
			$gd_post = $post_obj;//echo '###';
		}

		// Only modify if its a GD post type.
		if (in_array($gd_post->post_type, geodir_get_posttypes())) {


			/*
			 * Available permalink tags:
			 * %country% , %region% , %city% , %category% , %postname% , %post_id%
			 */

			// Check if a pretty permalink is required
			$permalink_structure = geodir_get_permalink_structure();
			if (strpos($permalink_structure, '%postname%') === false || empty($permalink_structure)) {
				//if (isset($orig_post)) {echo '###x';
					$gd_post = $orig_post;
				//}
				return $post_link;
			}


			// if we don't the GD post info then get it.
			if(!isset($gd_post->default_category)){
				$gd_post = geodir_get_post_info($gd_post->ID);
				if(!empty($gd_post)){
					$gd_post = $gd_post;
				}
			}

			if($gd_post->post_type == 'revision'){
				$gd_post->post_type = get_post_type(wp_get_post_parent_id($gd_post->ID));
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
			$cpt_slug = $post_types[$gd_post->post_type]['rewrite']['slug'];

			// Alter the CPT slug if WPML is set to do so
			if(geodir_wpml_is_post_type_translated($gd_post->post_type)){
				if ( geodir_wpml_slug_translation_turned_on( $gd_post->post_type ) && $language_code = geodir_wpml_get_lang_from_url($post_link)) {

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
				$locations = self::get_post_location_slugs($gd_post);
				if(isset($locations->country_slug) && $locations->country_slug){
					$permalink = str_replace('%country%',$locations->country_slug,$permalink);
				}
			}

			/*
			 * Add Region if needed. (%region%)
			 */
			if (strpos($permalink, '%region%') !== false) {
				$locations = isset($locations) ? $locations : self::get_post_location_slugs($gd_post);
				if(isset($locations->region_slug) && $locations->region_slug){
					$permalink = str_replace('%region%',$locations->region_slug,$permalink);
				}
			}

			/*
			 * Add City if needed. (%city%)
			 */
			if (strpos($permalink, '%city%') !== false) {
				$locations = isset($locations) ? $locations : self::get_post_location_slugs($gd_post);
				if(isset($locations->city_slug) && $locations->city_slug){
					$permalink = str_replace('%city%',$locations->city_slug,$permalink);
				}
			}

			/*
			 * Add Category if needed. (%category%)
			 */
			if (strpos($permalink, '%category%') !== false) {
				if(isset($gd_post->default_category) && $gd_post->default_category){
					$term = get_term_by('id', absint($gd_post->default_category), $gd_post->post_type."category");
				}elseif(isset($gd_post->post_category) && $gd_post->post_category){
					$cat_id = explode(",", trim($gd_post->post_category, ","));
					$cat_id = !empty($cat_id) ? absint($cat_id [0]) : 0;
					if($cat_id){
						$term = get_term_by('id', $cat_id, $gd_post->post_type."category");
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
				$permalink = str_replace('%postname%',$gd_post->post_name,$permalink);
			}

			/*
			 * Add post ID if needed. (%post_id%)
			 */
			if (strpos($permalink, '%post_id%') !== false) {
				$permalink = str_replace('%post_id%',$gd_post->ID,$permalink);
			}

			//echo $permalink;
			$post_link = $permalink;

			// @todo we will com back to cache
//			if (isset($comment_post_cache[$gd_post->ID])) {
//				$gd_post = $comment_post_cache[$gd_post->ID];
//			}
//			if (isset($gd_permalink_cache[$gd_post->ID]) && $gd_permalink_cache[$gd_post->ID] && !$sample) {
//				$post_id = $gd_post->ID;
//				if (isset($orig_post)) {
//					$gd_post = $orig_post;
//				}
//				return $gd_permalink_cache[$post_id];
//			}



			// temp cache the permalink
			if (!$sample && (!isset($_REQUEST['geodir_ajax']) || (isset($_REQUEST['geodir_ajax']) && $_REQUEST['geodir_ajax'] != 'add_listing'))) {
				$gd_permalink_cache[$gd_post->ID] = $post_link;
			}
		}
		if (isset($orig_post)) {
			$gd_post = $orig_post;
		}

		return $post_link;
	}

	private static function get_post_location_slugs($post){
		return apply_filters('geodir_post_permalinks',geodir_get_default_location(), $post);

	}

	/**
	 * Register GD rewrite rules.
	 * @todo if the cate and the place permailnks contain the same amount of arguments they can clash, we need to add js checking for it
	 */
	public static function rewrite_rules() {
		$gd_permalink_structure = geodir_get_permalink_structure();

		$post_types = geodir_get_posttypes('array');

		if ( ! empty( $post_types ) ) {
			if ( empty( $gd_permalink_structure ) ) {
				$gd_permalink_structure = '/%postname%/';
			}
			$permalink_arr = explode( "/", trim( $gd_permalink_structure, "/" ) );

			foreach ( $post_types as $cpt => $post_type ) {

				$cpt_permalink_arr = $permalink_arr;
				foreach($cpt_permalink_arr as $key => $val){
					if($val=='%category%'){
						$cpt_permalink_arr[$key] = "%".$cpt."category%";
					}
				}

				// add the post single permalinks
				//$regex = '^' . $post_type['rewrite']['slug'] . '/' . implode( "", array_fill( 0, count( $cpt_permalink_arr ), '([^/]*)/' ) ) . '?';
				$regex = '' . $post_type['rewrite']['slug'] . '/' . implode( "", array_fill( 0, count( $cpt_permalink_arr ), '([^/]*)/' ) ) . '?';
				$redirect = 'index.php?';
				$match = 1;
				$query_vars = array();

				foreach( $cpt_permalink_arr as $tag ) {
					$tag = trim( $tag, "%" );
					if ( $tag == "postname") {
						$query_vars[] = "$cpt=" . '$matches[' . $match . ']';
					} else {
						$query_vars[] = trim( $tag, "%" ) . '=$matches[' . $match . ']';
					}
					$match++;
				}
				if ( ! empty( $query_vars ) ) {
					$redirect .= implode( '&', $query_vars );
				}
//geodir_error_log( $redirect, $regex, __FILE__, __LINE__ );
				//echo '###'.$redirect."\n";
				$after = $gd_permalink_structure=="/%postname%/" ? 'bottom' : 'top';
				add_rewrite_rule( $regex, $redirect, $after );
			}
		}


		// add search paging rewrite // @todo we need to replace search with the current GD search page slug
		add_rewrite_rule( self::search_slug().'/page/([^/]+)/?', 'index.php?paged=$matches[1]', 'top' );
	}

	/**
	 * Add GD rewrite tags.
	 */
	public static function rewrite_tags(){
		add_rewrite_tag('%country%', '([^&]+)');
		add_rewrite_tag('%region%', '([^&]+)');
		add_rewrite_tag('%city%', '([^&]+)');
		// add_rewrite_tag('%category%', '([^&]+)'); // conflicts with standard category rewrite rule.
		add_rewrite_tag('%gd_favs%', '([^&]+)');
	}

	/**
	 * Get the slug for user favs.
	 *
	 * @param string $cpt_slug
	 *
	 * @return mixed|void
	 */
	public static function favs_slug($cpt_slug = '' ){
		return apply_filters('geodir_rewrite_favs_slug','favs',$cpt_slug);
	}

	/**
	 * Get the slug for the search page.
	 *
	 * @param string $search_slug
	 *
	 * @return string
	 */
	public static function search_slug($search_slug = 'search' ){

		if($page_id = geodir_search_page_id()){
			if($slug = get_post_field( 'post_name', $page_id )){
				$search_slug = $slug;
			}
		}

		return apply_filters('geodir_rewrite_search_slug',$search_slug);
	}


}

GeoDir_Permalinks::init();

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

	public $rewrite_rule_problem = '';
	public $rewrite_rules = array();

	public function __construct() {
		// add rewrite tags (query params)
		add_action('init', array( $this, 'rewrite_tags'), 10, 0);

		// post (single) rewrite rules
		add_action('init', array( $this, 'post_rewrite_rules'), 10, 0);

		// location page rewrite rules
		add_action('init', array( $this, 'location_rewrite_rules'), 11,0);

		// search page rewrite rules
		add_action('init', array( $this, 'search_rewrite_rules'), 11,0);

		// author page permalinks
		add_filter( 'author_rewrite_rules', array( $this, 'author_rewrite_rules' ) );

		// post (single) url filter
		add_filter( 'post_type_link', array( $this, 'post_url'), 0, 4);



		// search page rewrite rules
		add_action('init', array( $this, 'insert_rewrite_rules'), 20,0);


		//add_action( 'registered_post_type', array( __CLASS__, 'register_post_type_rules' ), 10, 2 );

		//add_action('init', array( $this, 'temp_check_rules'),10000000000);
	}

	public function insert_rewrite_rules(){

		if(!empty($this->rewrite_rules)){
			// organise the right order
			usort($this->rewrite_rules, array( $this, "sort_rewrites"));
			foreach($this->rewrite_rules as $rule){
				add_rewrite_rule($rule['regex'],$rule['redirect'],$rule['after']);
			}
		}
	}

	function sort_rewrites($b, $a) {
		if ($a['count'] == $b['count']) {
			return 0;
		}
		return ($a['count'] < $b['count']) ? -1 : 1;
	}

	public function rewrite_rule_problem_notice() {
		?>
		<div class="notice notice-error">
			<p><?php _e( '<b>GeoDirectory permalink error</b> the following rule appears twice:', 'geodirectory' ); echo " ". esc_attr( $this->rewrite_rule_problem ); ?></p>
			<p><?php _e( '<b>Try making the GeoDirectory permalinks more unique.</b>', 'geodirectory' );?></p>
		</div>
		<?php
	}

	public function add_rewrite_rule($regex, $redirect, $after = ''){

		if(isset($this->rewrite_rules[$regex])){
			//echo 'permalink problem';exit;
			$this->rewrite_rule_problem = $regex;
			add_action( 'admin_notices', array($this,'rewrite_rule_problem_notice') );
		}
		$this->rewrite_rules[$regex] = array(
			'regex'     => $regex,
			'redirect'  => $redirect,
			'after'     => $after,
			'count'     => count( explode("/", str_replace(array('([^/]+)','([^/]*)'),'',$regex)) ),
			//'countx'     => explode("/", str_replace(array('([^/]+)','([^/]*)'),'',$regex))
		);
	}
	

	// @todo remove after testing
	public function temp_check_rules($rules){

		if(is_admin()){return;}
		global $wp_rewrite;
		print_r( $wp_rewrite );
		print_r(get_option( 'rewrite_rules' ));

		echo '###';exit;

		return $rules;
	}

	/**
	 * Add the locations page rewrite rules.
	 */
	public function location_rewrite_rules(){
		// locations page
		$this->add_rewrite_rule( "^".$this->location_slug()."/([^/]+)/([^/]+)/([^/]+)/?", 'index.php?pagename='.$this->location_slug().'&country=$matches[1]&region=$matches[2]&city=$matches[3]', 'top' );
		$this->add_rewrite_rule( "^".$this->location_slug()."/([^/]+)/([^/]+)/?", 'index.php?pagename='.$this->location_slug().'&country=$matches[1]&region=$matches[2]', 'top' );
		$this->add_rewrite_rule( "^".$this->location_slug()."/([^/]+)/?", 'index.php?pagename='.$this->location_slug().'&country=$matches[1]', 'top' );
	}

	/**
	 * Add the search page rewrite rules.
	 */
	public function search_rewrite_rules(){
		// add search paging rewrite
		$this->add_rewrite_rule( "^".$this->search_slug() . '/page/([^/]+)/?', 'index.php?paged=$matches[1]', 'top' );
	}

	/**
	 * Add author page pretty urls.
     *
     * @since 2.0.0
	 *
	 * @param array $rules Rules.
	 *
	 * @return array $rules.
	 */
	public function author_rewrite_rules( $rules ){
		global $wp_rewrite;

		$post_types = geodir_get_posttypes( 'array' );

		if(!empty($post_types)){
			foreach($post_types as $post_type => $cpt){

				$cpt_slug = isset($cpt['rewrite']['slug']) ? $cpt['rewrite']['slug'] : '';
				$saves_slug = $this->favs_slug( $cpt_slug );

				// add CPT author rewrite rules
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$cpt_slug/?$",'index.php?author_name=$matches[1]&post_type='.$post_type);
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$cpt_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&post_type='.$post_type.'&paged=$matches[2]');

				// favs
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$saves_slug/?$",'index.php?author_name=$matches[1]&gd_favs=1');
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$saves_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&gd_favs=1&paged=$matches[2]');
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$saves_slug/$cpt_slug/?$",'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type);
				$this->add_rewrite_rule("^".$wp_rewrite->author_base."/([^/]+)/$saves_slug/$cpt_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type.'&paged=$matches[2]');
			}
		}

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
	public function post_url($post_link, $post_obj, $leavename, $sample)
	{
		//echo '###'.$post_link."<br />".$sample." \n" ;
		//print_r($post_obj);


		global $wpdb, $wp_query, $plugin_prefix, $post, $comment_post_cache, $gd_permalink_cache,$gd_post;

		//print_r($gd_post);
		$correct_post = true;

		if (isset($post_obj->post_status) && ( $post_obj->post_status == 'auto-draft' || $post_obj->post_status == 'draft' || $post_obj->post_status == 'pending') ) {
			return $post_link; // if draft then return default url.
		} elseif (isset($post_obj->ID) && isset($gd_post->ID) && $post_obj->ID == $gd_post->ID) {
			// check its the correct post.
		} else {
			$correct_post = false;
		}

		// Only modify if its a GD post type.
		if (in_array($post_obj->post_type, geodir_get_posttypes())) {


			/*
			 * Available permalink tags:
			 * %country% , %region% , %city% , %category% , %postname% , %post_id%
			 */

			// Check if a pretty permalink is required
			$permalink_structure = geodir_get_permalink_structure();
			if (strpos($permalink_structure, '%postname%') === false || empty($permalink_structure)) {
				return $post_link;
			}

			// backup the original post data first so we can restore it later
			if(!$correct_post){
				$orig_post = $gd_post;
				$gd_post = geodir_get_post_info($post_obj->ID);
			}

			if ( empty( $gd_post ) ) {
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

			$permalink = trailingslashit( home_url() );

			if (function_exists('geodir_location_geo_home_link')) {
				add_filter('home_url', 'geodir_location_geo_home_link', 100000, 2);
			}



			/*
			 * Add the CPT slug.
			 */
			$post_types = geodir_get_posttypes('array');
			$cpt_slug = $post_types[$gd_post->post_type]['rewrite']['slug'];

			$cpt_slug = apply_filters( 'geodir_post_permalink_structure_cpt_slug', $cpt_slug, $gd_post, $post_link );

			$permalink .= $cpt_slug.$permalink_structure;


			/*
			 * Add Country if needed. (%country%)
			 */
			if (strpos($permalink, '%country%') !== false) {
				$locations = $this->get_post_location_slugs($gd_post);
				if(isset($locations->country_slug) && $locations->country_slug){
					$permalink = str_replace('%country%',$locations->country_slug,$permalink);
				}
			}

			/*
			 * Add Region if needed. (%region%)
			 */
			if (strpos($permalink, '%region%') !== false) {
				$locations = isset($locations) ? $locations : $this->get_post_location_slugs($gd_post);
				if(isset($locations->region_slug) && $locations->region_slug){
					$permalink = str_replace('%region%',$locations->region_slug,$permalink);
				}
			}

			/*
			 * Add City if needed. (%city%)
			 */
			if (strpos($permalink, '%city%') !== false) {
				$locations = isset($locations) ? $locations : $this->get_post_location_slugs($gd_post);
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

    /**
     * Function get the post location slugs.
     *
     * @since 2.0.0
     *
     * @param object $post Post object.
     * @return object Post location slugs.
     */
	private function get_post_location_slugs($gd_post){
		//print_r($gd_post);echo '###';
		global $geodirectory;
		//return apply_filters('geodir_post_permalinks',geodir_get_default_location(), $gd_post);
		return apply_filters('geodir_post_permalinks',$geodirectory->location->get_post_location($gd_post), $gd_post);

	}

	/**
	 * Register GD rewrite rules.
	 */
	public function post_rewrite_rules() {
		$gd_permalink_structure = geodir_get_permalink_structure();

		$post_types = geodir_get_posttypes( 'array' );

		if ( ! empty( $post_types ) ) {
			if ( empty( $gd_permalink_structure ) ) {
				$gd_permalink_structure = '/%postname%/';
			}
			$permalink_arr = explode( "/", trim( $gd_permalink_structure, "/" ) );

			foreach ( $post_types as $cpt => $post_type ) {

				$cpt_permalink_arr = $permalink_arr;
				foreach ( $cpt_permalink_arr as $key => $val ) {
					if ( $val == '%category%' ) {
						$cpt_permalink_arr[ $key ] = "%" . $cpt . "category%";
					}
				}

				// add the post single permalinks
				$regex      = '^' . $post_type['rewrite']['slug'] . '/' . implode( "", array_fill( 0, count( $cpt_permalink_arr ), '([^/]*)/' ) ) . '?';
				$redirect   = 'index.php?';
				$match      = 1;
				$query_vars = array();

				foreach ( $cpt_permalink_arr as $tag ) {
					$tag = trim( $tag, "%" );
					if ( $tag == "postname" ) {
						$query_vars[] = "$cpt=" . '$matches[' . $match . ']';
					} else {
						$query_vars[] = trim( $tag, "%" ) . '=$matches[' . $match . ']';
					}
					$match ++;
				}
				if ( ! empty( $query_vars ) ) {
					$redirect .= implode( '&', $query_vars );
				}

				$after = $gd_permalink_structure == "/%postname%/" ? 'bottom' : 'top';
				$this->add_rewrite_rule( $regex, $redirect, $after );
			}
		}
	}




	/**
	 * Add GD rewrite tags.
     *
     * @since 2.0.0
	 */
	public function rewrite_tags(){
		add_rewrite_tag('%country%', '([^&]+)');
		add_rewrite_tag('%region%', '([^&]+)');
		add_rewrite_tag('%city%', '([^&]+)');
		add_rewrite_tag('%gd_favs%', '([^&]+)');
		add_rewrite_tag('%sort_by%', '([^&]+)');
	}

	/**
	 * Get the slug for user favs.
     *
     * @since 2.0.0
	 *
	 * @param string $cpt_slug Optional. Custom Posttype slug. Default null.
	 *
	 * @return mixed|void
	 */
	public function favs_slug($cpt_slug = '' ){
		return apply_filters('geodir_rewrite_favs_slug','favs',$cpt_slug);
	}

	/**
	 * Get the slug for the search page.
     *
     * @since 2.0.0
	 *
	 * @param string $search_slug Optional. Search slug. Default search.
	 *
	 * @return string
	 */
	public function search_slug($search_slug = 'search' ){

		if($page_id = geodir_search_page_id()){
			if($slug = get_post_field( 'post_name', $page_id )){
				$search_slug = $slug;
			}
		}

		return apply_filters('geodir_rewrite_search_slug',$search_slug);
	}

	/**
	 * Get the slug for the locations page.
	 *
	 * @since 2.0.0
	 *
	 * @param string $location_slug Optional. Search slug. Default search.
	 *
	 * @return string
	 */
	public function location_slug($location_slug = 'location' ){

		if($page_id = geodir_location_page_id()){
			if($slug = get_post_field( 'post_name', $page_id )){
				$location_slug = $slug;
			}
		}

		return apply_filters('geodir_rewrite_location_slug',$location_slug);
	}


}



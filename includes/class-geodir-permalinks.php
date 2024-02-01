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

		// Add listing page rewrite rules
		add_action( 'init', array( $this, 'add_listing_rewrite_rules' ), 11 );

		// search page rewrite rules
		add_action('init', array( $this, 'search_rewrite_rules'), 11,0);

		// author page permalinks
		add_filter( 'init', array( $this, 'author_rewrite_rules' ) );

		// post (single) url filter
		add_filter( 'post_type_link', array( $this, 'post_url'), 0, 4);

		// search page rewrite rules
		add_action('init', array( $this, 'insert_rewrite_rules'), 20,0);

		// flush rewrite rules
		add_action( 'init', array( __CLASS__, 'flush_rewrite_rules' ), 99 );

		// make child cat not contain parent cat url
		add_filter('term_link', array($this,'term_url_no_parent'), 9, 3);

		// maybe make the details page 404 if the locations vars are wrong
		add_action('wp',array($this,'maybe_404'));

		// try and recover from 404 if GD CPT detected
		add_action('wp',array($this,'_404_rescue'));

		// Arrange rewrite rules to fix paged, feed permalinks on category pages.
		add_filter( 'rewrite_rules_array', array( __CLASS__, 'arrange_rewrite_rules' ), 999, 1 );

		add_filter( 'wp_setup_nav_menu_item', array( $this, 'wp_setup_nav_menu_item' ), 10, 1 );
	}

	public function maybe_404() {
		global $wp_query;

		if(geodir_is_page('single')){
			global $gd_post,$geodirectory;

			$should_404 = false;
			$post_type = isset($wp_query->query_vars['post_type']) ? $wp_query->query_vars['post_type'] : '';
			$post_locations = $geodirectory->location->get_post_location($gd_post);

			// check country
			if(isset($wp_query->query_vars['country']) && isset($gd_post->country) && $wp_query->query_vars['country']){
				if(isset($post_locations->country_slug) && $post_locations->country_slug && $post_locations->country_slug!=$wp_query->query_vars['country']){
					$should_404 = true;
				}
			}

			// check region
			if(!$should_404 && isset($wp_query->query_vars['region']) && isset($gd_post->region) && $wp_query->query_vars['region']){
				if(isset($post_locations->region_slug) && $post_locations->region_slug && $post_locations->region_slug!=$wp_query->query_vars['region']){
					$should_404 = true;
				}
			}

			// check city
			if(!$should_404 && isset($wp_query->query_vars['city']) && isset($gd_post->city) && $wp_query->query_vars['city']){
				if(isset($post_locations->city_slug) && $post_locations->city_slug && $post_locations->city_slug!=$wp_query->query_vars['city']){
					$should_404 = true;
				}
			}

			// check category
			if ( ! $should_404 && isset( $wp_query->query_vars[$post_type."category"] ) && $wp_query->query_vars[ $post_type . "category" ] && isset( $gd_post->default_category ) && $gd_post->default_category ) {
				$is_cat = get_term_by( 'slug', $wp_query->query_vars[ $post_type . "category" ], $post_type . "category" );

				/**
				 * @since 2.0.0.67
				 */
				$is_cat = apply_filters( 'geodir_post_url_filter_term', $is_cat, $gd_post, (int) $gd_post->default_category );

				if ( ! $is_cat || ( isset( $is_cat->term_id ) && $is_cat->term_id != $gd_post->default_category ) ) {
					$should_404 = true;
				}
			}

			if ( $should_404 ) {
				$wp_query->set_404();
				status_header(404);
			}
		}

		// Allow post author to access their pending listings.
		if ( ! empty( $wp_query ) && ! empty( $wp_query->query_vars['post_type'] ) && ! empty( $wp_query->query_vars['p'] ) && is_404() && ! is_preview() && ( $user_id = (int) get_current_user_id() ) ) {
			if ( geodir_is_gd_post_type( $wp_query->query_vars['post_type'] ) && in_array( get_post_status( (int) $wp_query->query_vars['p'] ), array_keys( geodir_get_post_statuses( $wp_query->query_vars['post_type'] ) ) ) && GeoDir_Post_Data::owner_check( (int) $wp_query->query_vars['p'], $user_id ) && ( $redirect = get_permalink( (int) $wp_query->query_vars['p'] ) ) ) {
				$redirect = add_query_arg( array( 'preview' => 'true' ), $redirect );

				if ( $redirect && $redirect != geodir_curPageURL() ) {
					wp_safe_redirect( $redirect );
					exit;
				}
			}
		}

	}

	/**
	 * Check the 404 page to see if its a GD CPT and if we can find the correct page.
	 *
	 * This can help with GDv1 -> GDv2 sites auto tell search engines the urls have moved.
	 */
	public function _404_rescue(){
		if(is_404() && geodir_get_option("enable_404_rescue",1)){
			global $wp_query,$wp, $geodirectory;

			$post_type = isset($wp_query->query_vars['post_type']) ? $wp_query->query_vars['post_type'] : '';
			$url_segments = explode("/",$wp->request);

			// if no post type query var then double check if its maybe a GD CPT but not registering as a query var
			if(!$post_type && !empty($url_segments)){
				$post_type_slug = $url_segments[0];
				$gd_cpts = geodir_get_posttypes('array');
				if(!empty($gd_cpts)){
					foreach($gd_cpts as $cpt => $cpt_options){
						if ( ! empty( $cpt_options['rewrite']['slug'] ) && $post_type_slug ) {
							if ( $cpt_options['rewrite']['slug'] == $post_type_slug ) {
								$post_type = $cpt;
								break;
							} elseif ( urlencode( geodir_cpt_permalink_rewrite_slug( $cpt ) ) == $post_type_slug ) { // Match translated slug
								$post_type = $cpt;
								break;
							}
						}
					}
				}
			}

			if (in_array($post_type, geodir_get_posttypes())) {
				$has_location = false;
				if ( ! empty( $geodirectory->location ) && ( $location = $geodirectory->location ) ) {
					if ( ! empty( $location->country_slug ) || ! empty( $location->region_slug ) || ! empty( $location->city_slug ) || ! empty( $location->neighbourhood_slug ) ) {
						$has_location = true;
					}
				}
				$maybe_slug = end($url_segments);

				if( $maybe_slug ){
					array_shift($url_segments);// remove the CPT slug
					$location_segments = array();
					$location_string = '';
					$redirect = '';
					$is_cat = get_term_by( 'slug', $maybe_slug, $post_type."category");
					if(!empty($is_cat)){

						foreach($url_segments as $url_segment){
							if($url_segment == $maybe_slug ){continue;}

							// check its not a term also
							$is_term = get_term_by( 'slug', $url_segment, $post_type."category");
							if(empty($is_term)){
								$location_segments[] = $url_segment;
							}
						}

						if ( ! empty( $location_segments ) && ! $has_location ) {
							$location_string = implode( "/", $location_segments );
						}

						$term_link = get_term_link( $maybe_slug, $post_type."category" );

						if($term_link){
							$redirect = trailingslashit($term_link).$location_string;
							if(self::is_slash()){
								$redirect = trailingslashit($redirect);
							}
						}

					}elseif($is_tag = get_term_by( 'slug', $maybe_slug, $post_type."_tags")){
						$tag_slug = geodir_get_option( 'permalink_tag_base', 'tags' );

						foreach($url_segments as $url_segment){
							 // Old url contains /tags/, so remove /tags/ as well from url.
							if ( $url_segment == $maybe_slug || $url_segment == $tag_slug ) {
								continue;
							}

							// check its not a term also
							$is_term = get_term_by( 'slug', $url_segment, $post_type."_tags");
							if(empty($is_term)){
								$location_segments[] = $url_segment;
							}
						}

						if ( ! empty( $location_segments ) && ! $has_location ) {
							$location_string = implode( "/", $location_segments );
						}

						$term_link = get_term_link( $maybe_slug, $post_type."_tags" );

						if($term_link){
							$redirect = trailingslashit($term_link).$location_string;
							if(self::is_slash()){
								$redirect = trailingslashit($redirect);
							}
						}
					}elseif($is_post = get_page_by_path($maybe_slug,OBJECT,$post_type)){
						$redirect = get_permalink($is_post->ID);
					}

					// redirect if needed and if its not to the same url
					if($redirect && $redirect != geodir_curPageURL()){
						wp_redirect($redirect,'301');exit;
					}
				}

			}
		}
	}

	/**
	 * Remove the parent slug from the term link.
	 *
	 * @param $termlink
	 * @param $term
	 * @param $taxonomy
	 *
	 * @return mixed
	 */
	public function term_url_no_parent( $termlink, $term, $taxonomy ) {
		if ( ! geodir_is_gd_taxonomy( $taxonomy ) ) {
			return $termlink;
		}

		if ( ! empty( $term ) && is_object( $term ) && ! isset( $term->parent ) && ! empty( $term->term_id ) ) {
			$_term = get_term( $term->term_id );

			if ( ! empty( $_term ) && ! is_wp_error( $_term ) ) {
				$term = $_term;
			}
		}

		if ( ! empty( $term->parent ) ) {
			$parent = self::get_term_parent_info( $term->parent, $taxonomy );
			$parent_slug = isset( $parent->slug ) ? $parent->slug : '';

			if ( $parent_slug ) {
				$termlink = str_replace( "/$parent_slug/", "/", $termlink );
			}
		}

		return $termlink;
	}

	/**
	 * Loop through and get the category parent.
	 *
	 * @param $term_id
	 * @param $taxonomy
	 * @param string $slug
	 *
	 * @return array|null|WP_Error|WP_Term
	 */
	public function get_term_parent_info($term_id, $taxonomy,$slug='' ){
		$parent = get_term( $term_id, $taxonomy );

		if($slug){
			$parent->slug = $parent->slug."/".$slug;
		}
		if (!empty($parent->parent)){

			$term = self::get_term_parent_info($parent->parent, $taxonomy,$parent->slug );
		}else{
			$term = $parent;
		}

		return $term;
	}

	public function insert_rewrite_rules(){

//		print_r($this->rewrite_rules);

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

	public function add_rewrite_rule( $regex, $redirect, $after = '' ) {
		// Check if there are double rules
		if ( isset( $this->rewrite_rules[ $regex ] ) ) {
			global $geodirectory;

			$parts = explode( '/([^/]*)/?$', $regex );

			if ( count( $parts ) == 2 && isset( $geodirectory->settings['permalink_structure'] ) && $geodirectory->settings['permalink_structure'] == '' ) {
			} else {
				$this->rewrite_rule_problem = $regex;
				add_action( 'admin_notices', array( $this, 'rewrite_rule_problem_notice' ) );
			}
		}

		$static_sections = 0;
		$sections = explode( "/", str_replace( '^/', '', $regex ) );

		if ( ! empty( $sections ) ) {
			foreach ( $sections as $section ) {
				if ( substr( $section, 0, 1 ) === "(" ) {
				} else {
					$static_sections++;
				}
			}
		}

		$count = ( 10 * count( explode( "/", str_replace( array( '([^/]+)','([^/]*)' ), '', $regex ) ) ) ) - ( substr_count( $regex, '([^/]+)' ) + substr_count( $regex,'([^/]*)' ) ) + ( $static_sections * 11 ) + ( substr( $regex, -3 ) == '/?$' ? 1 : 0 ); // High priority to "^places/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?$" than "^places/([^/]*)/([^/]*)/([^/]*)/([^/]*)/?" to fix cpt + neighbourhood urls

		$this->rewrite_rules[$regex] = array(
			'regex'     => $regex,
			'redirect'  => $redirect,
			'after'     => $after,
			'count'     => $count
		);
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
	 * Setup add listing page rewrite rules.
	 *
	 * @since 2.3.18
	 */
	public function add_listing_rewrite_rules() {
		$post_types = geodir_get_posttypes( 'array' );

		if ( empty( $post_types ) ) {
			return;
		}

		$page_slug = $this->add_listing_slug();
		$rules = array();

		foreach ( $post_types as $post_type => $cpt ) {
			if ( empty( $cpt['rewrite']['slug'] ) ) {
				continue;
			}

			$cpt_slug = $cpt['rewrite']['slug'];

			$rules[ '^' . $page_slug . '/' . $cpt_slug . '/?$' ] = 'index.php?pagename=' . $page_slug . '&listing_type=' . $post_type;
			$rules[ '^' . $page_slug . '/' . $cpt_slug . '/?([0-9]{1,})/?$' ] = 'index.php?pagename=' . $page_slug . '&listing_type=' . $post_type . '&pid=$matches[1]';

			$cpt_page_slug = $this->add_listing_slug( $post_type );
			if ( $cpt_page_slug != $cpt_slug ) {
				$rules[ '^' . $cpt_page_slug . '/' . $cpt_slug . '/?$' ] = 'index.php?pagename=' . $cpt_page_slug . '&listing_type=' . $post_type;
				$rules[ '^' . $cpt_page_slug . '/' . $cpt_slug . '/?([0-9]{1,})/?$' ] = 'index.php?pagename=' . $cpt_page_slug . '&listing_type=' . $post_type . '&pid=$matches[1]';
			}
		}

		$rules = apply_filters( 'geodir_get_add_listing_rewrite_rules', $rules );

		if ( ! empty( $rules ) ) {
			foreach ( $rules as $regex => $redirect ) {
				$this->add_rewrite_rule( $regex, $redirect, 'top' );
			}
		}
	}

	/**
	 * Add the search page rewrite rules.
	 */
	public function search_rewrite_rules(){
		// add search paging rewrite
		$this->add_rewrite_rule( "^".$this->search_slug() . '/page/([^/]+)/?', 'index.php?pagename='.$this->search_slug().'&paged=$matches[1]', 'top' );
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
	public function author_rewrite_rules( $rules ) {
		global $wp_rewrite;

		$post_types = geodir_get_posttypes( 'array' );
		$saves_slug_arr = array();

		if ( ! empty( $post_types ) ) {
			$author_rewrite_base = $wp_rewrite->author_base . "/([^/]+)";

			// The author permalink structure
			$author_permastruct = $wp_rewrite->get_author_permastruct();
			if ( ! empty( $author_permastruct ) ) {
				$author_rewrite_base = trim( str_replace( '%author%', "([^/]+)", $author_permastruct ), "/" );
			}

			foreach ( $post_types as $post_type => $cpt ) {
				$cpt_slug = isset( $cpt['rewrite']['slug'] ) ? $cpt['rewrite']['slug'] : '';
				$saves_slug = self::favs_slug( $cpt_slug );

				// Add CPT author rewrite rules
				$this->add_rewrite_rule("^" . $author_rewrite_base . "/$cpt_slug/?$",'index.php?author_name=$matches[1]&post_type='.$post_type,'top');
				$this->add_rewrite_rule("^" . $author_rewrite_base . "/$cpt_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&post_type='.$post_type.'&paged=$matches[2]','top');

				// favs
				if(!isset($saves_slug_arr[$saves_slug])){ // only add this once unless the favs slug changes per CPT
					$this->add_rewrite_rule("^" . $author_rewrite_base . "/$saves_slug/?$",'index.php?author_name=$matches[1]&gd_favs=1');
					$this->add_rewrite_rule("^" . $author_rewrite_base . "/$saves_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&gd_favs=1&paged=$matches[2]','top');
				}
				$this->add_rewrite_rule("^" . $author_rewrite_base . "/$saves_slug/$cpt_slug/?$",'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type,'top');
				$this->add_rewrite_rule("^" . $author_rewrite_base . "/$saves_slug/$cpt_slug/page/?([0-9]{1,})/?$",'index.php?author_name=$matches[1]&gd_favs=1&post_type='.$post_type.'&paged=$matches[2]','top');

				// Translate slug
				do_action( 'geodir_permalinks_author_rewrite_rule', $post_type, $cpt, $this, $cpt_slug, $saves_slug, $saves_slug_arr );

				$saves_slug_arr[$saves_slug] = $saves_slug;
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
	 * @global object $wp_query WordPress Query object.
	 * @global object $post WordPress Post object.
	 * @param string $post_link The post link.
	 * @param object $post_obj The post object.
	 * @param string $leavename Not yet implemented.
	 * @param bool $sample Is this a sample post?.
	 * @return string The post link.
	 */
	public function post_url( $post_link, $post_obj, $leavename, $sample ) {
		global $wpdb, $wp_query, $post, $gd_post, $comment_post_cache, $gd_permalink_cache;

		$correct_post = true;

		if ( isset( $post_obj->post_status ) && in_array( $post_obj->post_status, array( 'draft', 'pending', 'auto-draft', 'future' ) ) ) {
			return $post_link; // If draft or post name is empty then return default url.
		} elseif ( isset( $post_obj->ID ) && isset( $gd_post->ID ) && $post_obj->ID == $gd_post->ID ) {
			// Check its the correct post.
			// Update $gd_post on post saved.
			if ( isset( $post_obj->post_status ) && isset( $gd_post->post_status ) && $post_obj->post_status != $gd_post->post_status ) {
				wp_cache_delete( "gd_post_" . $gd_post->ID, 'gd_post' );

				$gd_post = geodir_get_post_info( $gd_post->ID );
			}
		} else {
			$correct_post = false;
		}

		// Only modify if its a GD post type.
		if ( geodir_is_gd_post_type( $post_obj->post_type ) ) {
			/*
			 * Available permalink tags:
			 * %country% , %region% , %city% , %category% , %postname% , %post_id%
			 */

			// Check if a pretty permalink is required
			$permalink_structure = apply_filters( 'geodir_post_permalink_structure', geodir_get_permalink_structure(), $post_obj->post_type );
			if ( strpos( $permalink_structure, '%postname%' ) === false || empty( $permalink_structure ) ) {
				return $post_link;
			}

			// Backup the original post data first so we can restore it later
			if ( ! $correct_post ) {
				$orig_post = $gd_post;
				$gd_post = geodir_get_post_info( $post_obj->ID );
			}

			if ( empty( $gd_post ) ) {
				return $post_link;
			}

			// If we don't the GD post info then get it.
			if ( ! isset( $gd_post->default_category ) ) {
				$gd_post = geodir_get_post_info( $gd_post->ID );

				if ( ! empty( $gd_post ) ) {
					$gd_post = $gd_post;
				}
			}

			if ( $gd_post->post_type == 'revision' ) {
				$gd_post->post_type = get_post_type( wp_get_post_parent_id( $gd_post->ID ) );
			}

			/*
			 * Get the site url. ( without any location filters )
			 */
			if ( function_exists( 'geodir_location_geo_home_link' ) ) {
				remove_filter( 'home_url', 'geodir_location_geo_home_link', 100000 );
			}

			$permalink = trailingslashit( home_url() );

			if ( function_exists( 'geodir_location_geo_home_link' ) ) {
				add_filter( 'home_url', 'geodir_location_geo_home_link', 100000, 2 );
			}

			/*
			 * Add the CPT slug.
			 */
			$post_types = geodir_get_posttypes( 'array' );
			$cpt_slug = $post_types[ $gd_post->post_type ]['rewrite']['slug'];

			$cpt_slug = apply_filters( 'geodir_post_permalink_structure_cpt_slug', $cpt_slug, $gd_post, $post_link );

			$permalink .= $cpt_slug . $permalink_structure;

			/*
			 * Add Country if needed. (%country%)
			 */
			if ( strpos( $permalink, '%country%' ) !== false ) {
				$locations = $this->get_post_location_slugs( $gd_post );

				if ( isset( $locations->country_slug ) && $locations->country_slug ) {
					$permalink = str_replace( '%country%', $locations->country_slug, $permalink );
				}else{
					$permalink = str_replace( '%country%', geodir_get_option('permalink_missing_country_base','global'), $permalink );
				}
			}

			/*
			 * Add Region if needed. (%region%)
			 */
			if ( strpos( $permalink, '%region%' ) !== false ) {
				$locations = isset( $locations ) ? $locations : $this->get_post_location_slugs( $gd_post );

				if ( isset( $locations->region_slug ) && $locations->region_slug ) {
					$permalink = str_replace( '%region%', $locations->region_slug, $permalink );
				}else{
					$permalink = str_replace( '%region%', geodir_get_option('permalink_missing_region_base','discover'), $permalink );
				}
			}

			/*
			 * Add City if needed. (%city%)
			 */
			if ( strpos( $permalink, '%city%' ) !== false ) {
				$locations = isset( $locations ) ? $locations : $this->get_post_location_slugs( $gd_post );

				if ( isset( $locations->city_slug ) && $locations->city_slug ) {
					$permalink = str_replace( '%city%', $locations->city_slug, $permalink );
				}else{
					$permalink = str_replace( '%city%', geodir_get_option('permalink_missing_city_base','explore'), $permalink );
				}
			}

			/*
			 * Add Category if needed. (%category%)
			 */
			if ( strpos( $permalink, '%category%' ) !== false ) {
				if ( is_admin() && isset( $_POST['default_category'] ) && $_POST['default_category'] ) {
					$term = get_term_by( 'id', absint( $_POST['default_category'] ), $gd_post->post_type . "category" );
				} elseif ( isset( $gd_post->default_category ) && $gd_post->default_category ) {
					$term = get_term_by( 'id', absint( $gd_post->default_category ), $gd_post->post_type . "category" );
				} elseif ( isset( $gd_post->post_category ) && $gd_post->post_category ) {
					$cat_id = explode( ",", trim( $gd_post->post_category, "," ) );
					$cat_id = ! empty( $cat_id ) ? absint( $cat_id[0] ) : 0;

					if ( $cat_id ) {
						$term = get_term_by( 'id', $cat_id, $gd_post->post_type . "category" );
					}
				}

				if ( ! empty( $term ) && $term->slug ) {
					/**
					 * @since 2.0.0.67
					 */
					$term = apply_filters( 'geodir_post_url_filter_term', $term, $gd_post );
					$permalink = str_replace( '%category%', $term->slug, $permalink );
				}
			}

			/*
			 * Add post name if needed. (%postname%)
			 */
			if ( ! $leavename && strpos( $permalink, '%postname%' ) !== false ) {
				$permalink = str_replace( '%postname%', $gd_post->post_name, $permalink );
			}

			/*
			 * Add post ID if needed. (%post_id%)
			 */
			if ( strpos( $permalink, '%post_id%' ) !== false ) {
				$permalink = str_replace( '%post_id%', $gd_post->ID, $permalink );
			}

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

			// Temp cache the permalink
			if ( ! $sample && ( ! isset( $_REQUEST['geodir_ajax'] ) || ( isset( $_REQUEST['geodir_ajax'] ) && $_REQUEST['geodir_ajax'] != 'add_listing' ) ) ) {
				$gd_permalink_cache[ $gd_post->ID ] = $post_link;
			}
		}
		if ( isset( $orig_post ) ) {
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
	private function get_post_location_slugs( $gd_post ) {
		global $geodirectory;

		return apply_filters( 'geodir_post_permalinks', $geodirectory->location->get_post_location( $gd_post ), $gd_post );
	}

	/**
	 * Register GD rewrite rules.
	 *
	 * @global WP_Rewrite $wp_rewrite WordPress rewrite component.
	 */
	public function post_rewrite_rules() {
		global $wp_rewrite;

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

				$cpt_permalink_arr = apply_filters( 'geodir_post_permalink_structure_params', $cpt_permalink_arr, $cpt, $post_type );

				// add the post single permalinks
				$regex_part = '/';
				foreach ( $cpt_permalink_arr as $rkey => $rvalue ) {
					if ( strpos( trim( $rvalue ), '%' ) === 0 ) {
						if ( $rvalue == "%post_id%" ) {
							$regex_part .= '([0-9]+)/';
						} else {
							$regex_part .= '([^/]*)/';
						}
					} else {
						// Custom tag
						$regex_part .= $rvalue . '/';
					}
				}
				$regex_part .= '?';
				$regex      = '^' . $post_type['rewrite']['slug'] . $regex_part;
				$redirect   = 'index.php?';
				$match      = 1;
				$query_vars = array();

				foreach ( $cpt_permalink_arr as $tag ) {
					// Skip custom tag
					if ( strpos( trim( $tag ), '%' ) !== 0 ) {
						continue;
					}

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

				// Add rewrite rule for /attachment/.
				$this->add_rewrite_rule( trim( $regex, '?^' ) . 'attachment/([^/]+)/?$', $redirect . '&attachment=$matches[' . $match . ']', 'top' );

				// Create query for /comment-page-xx.
				$comment_regex = trim( $regex, '?^' );
				$comment_regex .= $wp_rewrite->comments_pagination_base . '-([0-9]{1,})/?$';
				$comment_redirect = $redirect . '&cpage=$matches[' . $match . ']';
				$this->add_rewrite_rule( $comment_regex, $comment_redirect, 'top' );

				if ( substr( $regex, -3 ) == ')/?' ) {
					$regex = str_replace( '*)/?', '*)?/?$', $regex ); // Force single post urls to 404 error when it has unnecessary slugs after post slug. Ex: /POSTNAME/xyz/
				}

				$this->add_rewrite_rule( $regex, $redirect, $after );

				// Translate slug
				do_action( 'geodir_permalinks_post_rewrite_rule', $cpt, $post_type, $this, $regex_part, $redirect, $after );
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
		add_rewrite_tag('%latlon%', '((\-?\d+(\.\d+)?),\s*(\-?\d+(\.\d+)?))');
		add_rewrite_tag('%dist%', '((?=.+)(?:[1-9]\d*|0)?(?:\.\d+))');
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
	public static function favs_slug($cpt_slug = '' ){
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

	/**
	 * Get the add listing page slug.
	 *
	 * @since 2.3.18
	 *
	 * @param string $post_type Post type. Default empty.
	 * @param bool   $page_uri  True to build the URI path for a page.
	 * @param string $slug      Add listing page slug. Default add-listing.
	 * @return string Add listing page slug.
	 */
	public function add_listing_slug( $post_type = '', $page_uri = true, $slug = 'add-listing' ) {
		$_slug = '';

		if ( $post_type && ( $page_id = (int) geodir_add_listing_page_id( $post_type ) ) ) {
			$_slug = $page_uri ? get_page_uri( $page_id ) : get_post_field( 'post_name', $page_id );
		}

		if ( ! $_slug && ( $page_id = (int) geodir_add_listing_page_id() ) ) {
			$_slug = $page_uri ? get_page_uri( $page_id ) : get_post_field( 'post_name', $page_id );
		}

		if ( $_slug ) {
			$slug = strpos( $_slug, '%' ) !== false ? urldecode( $_slug ) : $_slug;
		}

		return apply_filters( 'geodir_rewrite_add_listing_slug', $slug, $post_type, $page_uri );
	}

	/**
	 * Tell if the current core permalink structure ends with a slash or not.
	 *
	 * @return bool
	 */
	public function is_slash(){
		global $wp_rewrite;
		$permalink_structure = isset($wp_rewrite->permalink_structure) ? $wp_rewrite->permalink_structure : '';
		if($permalink_structure){
			if(substr($permalink_structure, -1)=='/'){
				return true;
			}
		}

		return false;

	}

	/**
	 * Arrange rewrite rules to work feed on categories pages.
	 *
	 * @since 2.0.0.75
	 *
	 * @global WP_Rewrite $wp_rewrite
	 *
	 * @param array $rules Array of rewrite rules.
	 * @return array Rewrite rules array.
	 */
	public static function arrange_rewrite_rules( $rules ) {
		global $wp_rewrite;

		$post_types = geodir_get_posttypes( 'names' );

		$post_type_slugs = array();

		// Get post type slugs
		foreach ( $rules as $regex => $query ) {
			if ( strpos( $query, 'index.php?post_type=' ) !== 0 ) {
				continue;
			}

			foreach ( $post_types as $post_type ) {
				if ( strpos( $query, 'index.php?post_type=' . $post_type . '&' ) === 0 ) {
					$_regex = explode( '/', $regex );
					$slug = ! empty( $_regex[0] ) ? str_replace( '^', '', $_regex[0] ) : '';

					if ( ! empty( $slug ) && ! in_array( $slug, $post_type_slugs ) ) {
						$post_type_slugs[ $post_type ] = $_regex[0];
					}
				}
			}
		}

		if ( empty( $post_type_slugs ) ) {
			return $rules;
		}

		$_post_type_slugs = array_flip( $post_type_slugs );
		$post_type_slugs = array_unique( array_values( $post_type_slugs ) );

		$_rules = $rules;
		foreach ( $rules as $regex => $query ) {

			// skip api rules
			if ( isset( $_rules[ $regex ] ) && strpos( $query, '&geodir-api=' ) !== false ) {
				unset( $_rules[ $regex ] );
				continue;
			}

			// replace CPT name with CPT slug
			foreach ( $post_type_slugs as $key => $slug ) {
				if ( isset( $_rules[ $regex ] ) && ( strpos( $regex, '^' . $slug . '/' ) === 0 || strpos( $regex, $slug . '/' ) === 0 ) && ( strpos( $query, '?attachment=' ) !== false || strpos( $query, '&attachment=' ) !== false || strpos( $query, '&tb=1' ) !== false || strpos( $query, '&embed=true' ) !== false ) ) {
					if ( strpos( $query, '&' . $_post_type_slugs[ $slug ] . '=' ) === false && strpos( $query, '?' . $_post_type_slugs[ $slug ] . '=' ) === false ) {
						unset( $_rules[ $regex ] );
					}
				}
			}
		}

		// Force static starting structures first.
		$ordered_rules_first  = array();
		$ordered_rules_second = array();

		foreach ( $_rules as $key => $value ) {
			$parts = explode( '/', $key );

			if ( count( $parts ) > 1 && strpos( $parts[0], '.' ) === false && strpos( $parts[0], '[' ) === false && strpos( $parts[1], '[' ) === false && strpos( $parts[1], '?' ) === false ) {
				$ordered_rules_first[ $key ] = $value;
			} else {
				$ordered_rules_second[ $key ] = $value;
			}
		}

		$rules = $ordered_rules_first + $ordered_rules_second;

		return $rules;
	}

	/**
	 * Check & flush rewrite rules.
	 *
	 * @since 2.0.0.92
	 *
	 * @return void
	 */
	public static function flush_rewrite_rules() {
		// Rank Math flush rewrite rules to generate sitemaps.
		if ( class_exists( 'RankMath\\Helper' ) ) {
			if ( ! wp_doing_ajax() && ! wp_doing_cron() && get_option( 'geodir_rank_math_flush_rewrite' ) ) {
				flush_rewrite_rules();
				delete_option( 'geodir_rank_math_flush_rewrite' );
			}
		}
	}

	/**
	 * Filters a navigation menu item object.
	 *
	 * Parse existing old add listing menu links.
	 *
	 * @since 2.3.18
	 *
	 * @param object $menu_item The menu item object.
	 * @return object Filtered menu item.
	 */
	public static function wp_setup_nav_menu_item( $menu_item ) {
		if ( ! empty( $menu_item->type ) && $menu_item->type == 'custom' && ! empty( $menu_item->url ) && ( strpos( $menu_item->url, '?listing_type=gd_' ) || strpos( $menu_item->url, '&listing_type=gd_' ) ) && get_option( 'permalink_structure' ) ) {
			$parse_url = parse_url( $menu_item->url );

			if ( ! empty( $parse_url['query'] ) ) {
				$query_params = wp_parse_args( $parse_url['query'] );

				if ( ! empty( $query_params['listing_type'] ) ) {
					$url = geodir_add_listing_page_url( $query_params['listing_type'] );

					$args = array();

					foreach ( $query_params as $key => $value ) {
						if ( in_array( $key, array( 'listing_type', 'pid' ) ) ) {
							continue;
						}

						$args[ $key ] = $value;
					}

					if ( ! empty( $args ) ) {
						$url = add_query_arg( $args, $url );
					}

					$menu_item->url = $url;
				}
			}
		}

		return $menu_item;
	}
}

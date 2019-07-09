<?php
/**
 * Contains the query functions for GeoDirectory which alter the front-end post queries and loops
 *
 * @class 		GeoDir_Query
 * @version		2.0.0
 * @package		GeoDirectory/Classes
 * @category	Class
 * @author 		AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Query Class.
 */
class GeoDir_Query {

	/** @public array Query vars to add to wp */
	public $query_vars = array();

	/**
	 * Stores chosen attributes
	 * @var array
	 */
	private static $_chosen_attributes;

	/**
	 * Constructor for the query class. Hooks in methods.
	 *
	 * @access public
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			add_action( 'wp_loaded', array( $this, 'get_errors' ), 20 );
			add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );
			add_action( 'parse_request', array( $this, 'parse_request' ), 0 );


			//add_action( 'wp', array( $this, 'add_page_id_in_query_var' )  );
			add_action( 'pre_get_posts', array( $this, 'set_globals' ) );
			add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

			//add_action( 'wp', array( $this, 'remove_product_query' ) );
			//add_action( 'wp', array( $this, 'remove_ordering_args' ) );
		}

		$this->init_query_vars();
	}

	/**
	 * Get a seed value for the RAND() sort order that is set for 24 hours .
	 *
	 * This is used to seed the mySQL RAND($seed) function so paging can be used.
	 *
	 * @return int|mixed
	 */
	public static function get_rand_seed(){

		$rand_seed = get_transient( 'geodir_rand_seed' );

		// if we don't have a transient then set a new one
		if(!$rand_seed){
			$rand_seed = time(); // well thats never gona be the same
			set_transient( 'geodir_rand_seed', $rand_seed, 24 * HOUR_IN_SECONDS );
		}

		// validate
		$rand_seed = absint($rand_seed);

		return apply_filters('geodir_rand_seed',$rand_seed);
	}

	/**
	 * Check if this is the main query and we should add our filters.
	 *
	 * @param $query
	 *
	 * @return bool
	 */
	public static function is_gd_main_query($query){
		$is_main_query = false;

		if((isset($query->query->gd_is_geodir_page) || isset($query->query['gd_is_geodir_page']) ) && geodir_is_page('search') && !isset($_REQUEST['geodir_search'])){
			// if its a search page with no queries then we don't add our filters
			$is_main_query = false;
		}elseif(isset($query->query->gd_is_geodir_page) && $query->query->gd_is_geodir_page) {
			$is_main_query = true;
		}elseif(isset($query->query['gd_is_geodir_page']) && $query->query['gd_is_geodir_page']) {
			$is_main_query = true;
		}

		return $is_main_query;
	}

    /**
     * Set globals.
     *
     * @since 2.0.0
	 *
	 * @param mixed $q query object
	 */
	public function set_globals( $q ){
		global $wp_query, $geodir_post_type;

		if ( empty( $wp_query ) ) {
			$wp_query = $q;
		}

		$geodir_post_type = geodir_get_current_posttype();
	}

	/**
	 * Hook into pre_get_posts to do the main product query.
     *
     * @since 2.0.0
	 *
	 * @param mixed $q query object
	 */
	public function pre_get_posts( $q ) {

		// We only want to affect the main query
		if ( ! $q->is_main_query() ) {
			return;
		}

		// We only want to affect GD pages.
		if (!geodir_is_geodir_page()) {
			return;
		}

		/* remove all pre filters, controversial but should only affect our own queries. */
		remove_all_filters('query');
		remove_all_filters('posts_search');
		remove_all_filters('posts_fields');
		remove_all_filters('posts_join');
		remove_all_filters('posts_groupby');
		remove_all_filters('posts_orderby');
		remove_all_filters('posts_where');


		// @todo for testing only
//		if(geodir_is_page('add-listing')){echo "is page:add-listing ";}
//		if(geodir_is_page('preview')){echo "is page:preview ";}
//		if(geodir_is_page('single')){echo "is page:single ";}
//		if(geodir_is_page('post_type')){echo "is page:post_type ";}
//		if(geodir_is_page('archive')){echo "is page:archive ";}
//		if(geodir_is_page('home')){echo "is page:home ";}
//		if(geodir_is_page('location')){echo "is page:location ";}
//		if(geodir_is_page('author')){echo "is page:author ";}
//		//if(geodir_is_page('search')){echo "is page:search ";}
//		if(geodir_is_page('info')){echo "is page:info ";}
//		if(geodir_is_page('login')){echo "is page:login ";}
//		if(geodir_is_page('checkout')){echo "is page:checkout ";}
//		if(geodir_is_page('invoices')){echo "is page:invoices ";}


		/*
		 * If post_type or archive then add query filters
		 */
		if(geodir_is_page('post_type') || geodir_is_page('archive') ){

			add_filter( 'posts_fields', array( $this, 'posts_fields' ),10,2 );
			add_filter( 'posts_join', array( $this, 'posts_join' ),10,2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ),10,2);
			add_filter( 'posts_where', array( $this, 'author_where' ),10,2 );
			//add_filter( 'posts_where', array( $this, 'posts_having' ), 10000, 2 ); // make sure its the last WHERE param
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ),10,2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ),10,2 );

		}elseif(geodir_is_page('search')){
			// Divi page builder breaks editor.
			if ( ! ( function_exists( 'et_divi_load_scripts_styles' ) && ! empty( $_REQUEST['et_fb'] ) && ! empty( $_REQUEST['et_bfb'] ) ) ) {
				$q->is_page = false;
			}
			$q->is_singular = false;
			$q->is_search = true;
			$q->is_archive = true;
			//$q->is_post_type_archive = true;
			$q->is_paged = true;
//			$q->in_the_loop = true; // this breaks elementor template 


			//$q->set('is_page',false);
			//$q->set('is_search',true);
			//$q->set('post_type','gd_place');
			add_filter( 'posts_join', array( $this, 'posts_join' ), 1, 2 );
			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 1, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );

			//add_filter( 'posts_limits', array( $this, 'posts_limits' ),10,2 );
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 1, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 1, 2 );

			add_filter( 'posts_clauses', array( $this, 'posts_having' ), 99999, 2 ); // make sure its the last WHERE param and after GROUP BY if there

			// setup search globals
			global $wp_query, $wpdb, $geodir_post_type, $table, $dist, $s, $snear, $s, $s_A, $s_SA, $gd_exact_search;

			if (isset($_REQUEST['scat']) && $_REQUEST['scat'] == 'all') $_REQUEST['scat'] = '';
			//if(isset($_REQUEST['s']) && $_REQUEST['s'] == '+') $_REQUEST['s'] = '';

			if (isset($_REQUEST['dist'])) {
				($_REQUEST['dist'] != '0' && $_REQUEST['dist'] != '') ? $dist = esc_attr($_REQUEST['dist']) : $dist = 25000;
			} elseif (geodir_get_option('search_radius') != '') {
				$dist = geodir_get_option('search_radius');//search_radius

			} else {
				$dist = 25000;
			} //  Distance
			

			if (isset($_REQUEST['snear'])) {
				$snear = trim(esc_attr($_REQUEST['snear']));
			}

			if (isset($_REQUEST['s'])) {
				$s = trim(esc_attr(wp_strip_all_tags(get_search_query())));
			}

			// Exact search with quotes
			$gd_exact_search = false;
			if ( $s != '' ) {
				$search_keyword = trim( wp_specialchars_decode( stripslashes( $s ), ENT_QUOTES ), '"' );
				$match_keyword = wp_specialchars_decode( stripslashes( $s ), ENT_QUOTES );

				if ( strpos( $match_keyword, '"' ) !== false && ( '"' . $search_keyword . '"' == $match_keyword ) ) {
					$gd_exact_search = true;
				}
			}

			if (strstr($s, ',')) {
				$s_AA = str_replace(" ", "", $s);
				$s_A = explode(",", $s_AA);
				$s_A = implode('","', $s_A);
				$s_A = '"' . $s_A . '"';
			} else {
				$s_A = '"' . $s . '"';
			}

			if (strstr($s, ' ')) {
				$s_SA = explode(" ", $s);
			} else {
				$s_SA = '';
			}
		}elseif(is_author()){
			add_filter( 'posts_where', array( $this, 'author_where' ) );
			//$q->is_archive = true;
			//$q->is_post_type_archive = true;
		}

		//print_r($q);


		if ( is_search() ) {
			//add_filter( 'posts_where', array( $this, 'search_post_excerpt' ) );
			//add_filter( 'wp', array( $this, 'remove_posts_where' ) );
		}

		// And remove the pre_get_posts hook
		$this->remove_product_query();

	}

	/**
	 * Add the HAVING query part if required.
	 *
	 * @param $where
	 * @param array $query
	 *
	 * @return string
	 */
	public function posts_having( $clauses, $query = array() ) {

		if(self::is_gd_main_query($query)) {
			global $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $snear,$geodirectory;
			$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );
			if ( $support_location && ( $snear != '' || $latlon = $geodirectory->location->get_latlon() ) ) {
				$dist = get_query_var( 'dist' ) ? (float)get_query_var( 'dist' ) : geodir_get_option( 'search_radius', 5 );
				$unit = geodir_get_option( 'search_distance_long', 'miles' );

				/* 
				 * The HAVING clause is often used with the GROUP BY clause to filter groups based on a specified condition. 
				 * If the GROUP BY clause is omitted, the HAVING clause behaves like the WHERE clause.
				 */
				if ( strpos( $clauses['where'], ' HAVING ') === false && strpos( $clauses['groupby'], ' HAVING ') === false ) {
					$having = $wpdb->prepare( " HAVING distance <= %f ", $dist );
					if ( trim( $clauses['groupby'] ) != '' ) {
						$clauses['groupby'] .= $having;
					} else {
						$clauses['where'] .= $having;
					}
				}
			}
		}

		return $clauses;
	}


	/**
	 * Filter the posts fields string.
     *
     * @since 2.0.0
	 *
	 * @param string $fields fields.
     * @param array $query Optional. fields query. Default array.
	 *
	 * @return string
	 */
	public function posts_fields($fields, $query = array()){

		if(self::is_gd_main_query($query)) {

			if ( ! ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) ) {
				global $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $snear, $gd_exact_search,$geodirectory;
				$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );

				$table = geodir_db_cpt_table( $geodir_post_type );

				$fields .= ", " . $table . ".* ";

				if ( $support_location && $latlon = $geodirectory->location->get_latlon()) {
					$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
					$lat = $latlon['lat'];
					$lon = $latlon['lon'];

					$fields .= " , (" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($lat) - ABS(" . $table . ".latitude)) * pi()/180 / 2), 2) +COS(ABS($lat) * pi()/180) * COS( ABS(" . $table . ".latitude) * pi()/180) *POWER(SIN(($lon - " . $table . ".longitude) * pi()/180 / 2), 2) ))) AS distance ";
				}


				global $s;// = get_search_query();
				if ( geodir_is_page( 'search' ) && $s && trim( $s ) != '' ) {
					$gd_titlematch_part = "";

					if ( ! $gd_exact_search ) {
						$keywords = explode( " ", $s );

						if ( is_array( $keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
							foreach ( $keywords as $kkey => $kword ) {
								if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
									unset( $keywords[ $kkey ] );
								}
							}
						}


						if ( count( $keywords ) > 1 ) {
							$parts = array(
								'AND' => 'gd_alltitlematch_part',
								'OR'  => 'gd_titlematch_part'
							);
							$gd_titlematch_part = "";
							foreach ( $parts as $key => $part ) {
								$gd_titlematch_part .= " CASE WHEN ";
								$count = 0;
								foreach ( $keywords as $keyword ) {
									$keyword = trim( $keyword );
									$keyword = wp_specialchars_decode( $keyword, ENT_QUOTES );
									$count ++;
									if ( $count < count( $keywords ) ) {
										// $gd_titlematch_part .= $wpdb->posts . ".post_title LIKE '%%" . $keyword . "%%' " . $key . " ";
										$gd_titlematch_part .= "( " . $wpdb->posts . ".post_title LIKE '" . $keyword . "' OR " . $wpdb->posts . ".post_title LIKE '" . $keyword . "%%' OR " . $wpdb->posts . ".post_title LIKE '%% " . $keyword . "%%' ) " . $key . " ";
									} else {
										//$gd_titlematch_part .= $wpdb->posts . ".post_title LIKE '%%" . $keyword . "%%' ";
										$gd_titlematch_part .= "( " . $wpdb->posts . ".post_title LIKE '" . $keyword . "' OR " . $wpdb->posts . ".post_title LIKE '" . $keyword . "%%' OR " . $wpdb->posts . ".post_title LIKE '%% " . $keyword . "%%' ) ";
									}
								}
								$gd_titlematch_part .= "THEN 1 ELSE 0 END AS " . $part . ",";
							}
						}
					}

					$s = stripslashes_deep( $s );
					$s = wp_specialchars_decode( $s, ENT_QUOTES );

					if ( geodir_column_exist( $table, 'featured' ) ) {
						$fields .= $wpdb->prepare( ", CASE WHEN " . $table . ".featured=%d THEN 1 ELSE 0 END AS gd_featured ", 1 );
					}
					$fields .= $wpdb->prepare( ", CASE WHEN " . $wpdb->posts . ".post_title LIKE %s THEN 1 ELSE 0 END AS gd_exacttitle," . $gd_titlematch_part . " CASE WHEN ( " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s ) THEN 1 ELSE 0 END AS gd_titlematch, CASE WHEN ( " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s ) THEN 1 ELSE 0 END AS gd_content", array(
						$s,
						$s,
						$s . '%',
						'% ' . $s . '%',
						$s,
						$s . ' %',
						'% ' . $s . ' %',
						'%>' . $s . '%',
						'% ' . $s,
						'% ' . $s .','
					) );
				}
			}

			//echo '###fields:'.$fields;
		}

		return apply_filters( 'geodir_posts_fields', $fields, $query );
	}

    /**
     * Posts limits.
     *
     * @since 2.0.0
     *
     * @param string $limits Limits.
     * @param string $query Query.
     * @return string $limits.
     */
	public function posts_limits($limits,$query){
		echo '###limit###'.$limits;

		$limits = " LIMIT 0,10 ";

		return $limits;
	}

	/**
     * Posts where.
     *
	 * @param string $where Where.
     * @param  array $query Optional. Query. Default array.
	 *
	 * @return mixed
	 */
	public function posts_where($where, $query = array()){
		if(self::is_gd_main_query($query)) {
			global $wpdb, $geodir_post_type, $wp_query,$geodirectory;

			$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );
			$table            = geodir_db_cpt_table( $geodir_post_type );

			$where .= $wpdb->prepare( " AND $wpdb->posts.post_type = %s ", $geodir_post_type );

			if ( geodir_is_page( 'search' ) ) {
				global $wpdb, $geodir_post_type, $plugin_prefix, $dist, $snear, $s, $s_A, $s_SA, $search_term;


				$search_term           = 'OR';
				$search_term           = 'AND';
				$geodir_custom_search  = '';
				$category_search_range = '';

				if ( is_single() && get_query_var( 'post_type' ) ) {
					return $where;
				}

				if ( is_tax() ) {
					return $where;
				}

				$s   = trim( $s );
				$s   = wp_specialchars_decode( $s, ENT_QUOTES );
				$s_A = wp_specialchars_decode( $s_A, ENT_QUOTES );

				// Exact search with quotes
				$gd_exact_search = false;
				if ( $s != '' ) {
					$search_keyword = trim( wp_specialchars_decode( stripslashes( $s ), ENT_QUOTES ), '"' );
					$match_keyword = wp_specialchars_decode( stripslashes( $s ), ENT_QUOTES );

					if ( strpos( $match_keyword, '"' ) !== false && ( '"' . $search_keyword . '"' == $match_keyword ) ) {
						$gd_exact_search = true;
					}

					// remove quotes after checking if its an exact search, this is VERY IMPORTANT
					$s = $search_keyword;
				}

				$where               = '';
				$better_search_terms = '';
				$terms_sql           = '';
				if ( isset( $_REQUEST['stype'] ) ) {
					$post_types = esc_attr( wp_strip_all_tags( $_REQUEST['stype'] ) );
				} else {
					$post_types = 'gd_place';
				}

				if ( $s != '' ) {
					if ( $gd_exact_search ) {
						$keywords = array( $s );
					} else {
						$keywords = explode( " ", $s );
						if ( is_array( $keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
							foreach ( $keywords as $kkey => $kword ) {
								if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
									unset( $keywords[ $kkey ] );
								}
							}
						}
					}

					if ( ! empty( $keywords ) ) {
						foreach ( $keywords as $keyword ) {
							$keyword = trim( $keyword );
							$keyword = stripslashes( wp_specialchars_decode( $keyword, ENT_QUOTES ) );
							if ( $keyword != '' ) {
								/**
								 * Filter the search query keywords SQL.
								 *
								 * @since 1.5.9
								 * @package GeoDirectory
								 *
								 * @param string $better_search_terms The query values, default: `' OR ( ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '" OR ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '%" OR ' . $wpdb->posts . '.post_title LIKE "% ' . $keyword . '%" )'`.
								 * @param array $keywords The array of keywords for the query.
								 * @param string $keyword The single keyword being searched.
								 */
								$better_search_terms .= apply_filters( "geodir_search_better_search_terms", ' OR ( ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '" OR ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '%" OR ' . $wpdb->posts . '.post_title LIKE "% ' . $keyword . '%" )', $keywords, $keyword );

							}
						}

					}
				}

				/* get taxonomy */
				$taxonomies = geodir_get_taxonomies( $post_types, true );
				if ( $taxonomies ) {
					$taxonomies = implode( "','", $taxonomies );
					$taxonomies = "'" . $taxonomies . "'";
				} else {
					$taxonomies = '';
				}

				$content_where = $terms_where = '';
				if ( $s != '' ) {
					/**
					 * Filter the search query content where values.
					 *
					 * @since 1.5.0
					 * @package GeoDirectory
					 *
					 * @param string $content_where The query values, default: `" OR ($wpdb->posts.post_content LIKE \"$s\" OR $wpdb->posts.post_content LIKE \"$s%\" OR $wpdb->posts.post_content LIKE \"% $s%\" OR $wpdb->posts.post_content LIKE \"%>$s%\" OR $wpdb->posts.post_content LIKE \"%\n$s%\") ") "`.
					 */
					$content_where = apply_filters( "geodir_search_content_where", " OR ($wpdb->posts.post_content LIKE \"$s\" OR $wpdb->posts.post_content LIKE \"$s%\" OR $wpdb->posts.post_content LIKE \"% $s%\" OR $wpdb->posts.post_content LIKE \"%>$s%\" OR $wpdb->posts.post_content LIKE \"%\n$s%\") " );

					if ( $gd_exact_search ) {
						/**
						 * Filter the search query term values.
						 *
						 * @since 1.5.0
						 * @package GeoDirectory
						 *
						 * @param string $terms_where The separator, default: `" AND ($wpdb->terms.name LIKE \"$s\" OR $wpdb->terms.name LIKE \"$s%\" OR $wpdb->terms.name LIKE \"% $s%\" OR $wpdb->terms.name IN ($s_A)) "`.
						 */
						$terms_where = apply_filters( "geodir_search_terms_where", " AND ($wpdb->terms.name LIKE \"$s\" ) " );
					}else{
						// @see above
						$terms_where = apply_filters( "geodir_search_terms_where", " AND ($wpdb->terms.name LIKE \"$s\" OR $wpdb->terms.name LIKE \"$s%\" OR $wpdb->terms.name LIKE \"% $s%\" OR $wpdb->terms.name IN ($s_A)) " );
					}
				}

				if ( geodir_is_page( 'search' ) && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
					$term_results = array();
				} else {

					if ( $s != '' ) {
						// get term sql
						$term_sql = "SELECT $wpdb->term_taxonomy.term_id,$wpdb->terms.name,$wpdb->term_taxonomy.taxonomy
						FROM $wpdb->term_taxonomy,  $wpdb->terms, $wpdb->term_relationships 
						WHERE $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id 
						AND $wpdb->term_relationships.term_taxonomy_id =  $wpdb->term_taxonomy.term_taxonomy_id 
						AND $wpdb->term_taxonomy.taxonomy in ( {$taxonomies} ) 
						$terms_where 
						GROUP BY $wpdb->term_taxonomy.term_id";

						$term_results = $wpdb->get_results( $term_sql );

						if ( ! empty( $term_results ) ) {
							foreach ( $term_results as $term ) {
								if($term->taxonomy==$post_types."category"){
									$terms_sql .= $wpdb->prepare(" OR FIND_IN_SET( %d , " . $table . ".post_category ) ",$term->term_id);
								}else{
									$terms_sql .= $wpdb->prepare(" OR FIND_IN_SET( %s, " . $table . ".post_tags ) ",$term->name );
								}
							}
						}
					}
				}

				$latlon = $geodirectory->location->get_latlon();
				// fake near if we have GPS
				if ( $snear == '' && $latlon) {
					$snear = ' ';
				}

				if ( $support_location && $snear != '' && $latlon) {

					$lat = $latlon['lat'];
					$lon = $latlon['lon'];
					$between          = geodir_get_between_latlon( $lat, $lon, $dist );
					$post_title_where = $s != "" ? "{$wpdb->posts}.post_title LIKE \"$s\"" : "1=1";
					$where .= " AND ( ($post_title_where $better_search_terms)
			                    $content_where 
								$terms_sql
							)
						AND $wpdb->posts.post_type in ('{$post_types}')
						AND ($wpdb->posts.post_status = 'publish')";

					if(!empty($between)){
						$where .= $wpdb->prepare( " AND latitude between %f and %f AND longitude between %f and %f ", $between['lat1'], $between['lat2'], $between['lon1'], $between['lon2'] );
					}


					if ( isset( $_REQUEST['sdistance'] ) && $_REQUEST['sdistance'] != 'all' ) {
						$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
						$where .= " AND CONVERT((" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($lat) - ABS(" . $table . ".latitude)) * pi()/180 / 2), 2) +COS(ABS($lat) * pi()/180) * COS( ABS(" . $table . ".latitude) * pi()/180) *POWER(SIN(($lon - " . $table . ".longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= " . $dist;
					}

				} else {
					$post_title_where = $s != "" ? "{$wpdb->posts}.post_title LIKE \"$s\"" : "1=1";
					$where .= " AND ( 
						( $post_title_where $better_search_terms )
                        $content_where  
                        $terms_sql 
                    )
                    AND $wpdb->posts.post_type in ('$post_types')
                    AND $wpdb->posts.post_status = 'publish' ";
				}
			}


			// add our own location query vars
			global $geodirectory;
			if ( $support_location ) {
				// only query known location variables
				$location_vars = $geodirectory->location->allowed_query_variables();
				foreach ( $location_vars as $location_var ) {
					if ( get_query_var( $location_var ) ) {
						$method_name = "get_{$location_var}_name_from_slug";
						$var_name    = $location_var == 'neighbourhood' ? get_query_var( $location_var ) : $geodirectory->location->$method_name( get_query_var( $location_var ) );
						if ( $var_name ) {
							$where .= $wpdb->prepare( " AND " . $table . "." . $location_var . " = %s ", $var_name );
						}
					}
				}
			}
		}

		return apply_filters( 'geodir_posts_where', $where, $query );
	}

    /**
     * Author where.
     *
     * @since 2.0.0
     *
     * @param string $where where.
     *
     * @global object $wp_query WordPress query object.
     * @global object $wpdb WordPress Database object.
     *
     * @return mixed|string
     */
	public function author_where($where){
		global $wp_query,$wpdb;

		$cpts = geodir_get_posttypes('array');

		// author saves/favs filter
		if(is_author() && !empty($wp_query->query['gd_favs']) ){

			$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;
			if($author_id){

				//echo $where.'###'.$author_id;exit;
				$where = str_replace("AND ($wpdb->posts.post_author = $author_id)","",$where); // remove the author restriction
				$user_favs = geodir_get_user_favourites( $author_id );
				if(empty($user_favs)){
					$fav_in = "''"; // blank it so we get no results
				}else{
					$fav_in = $user_favs;
					$prepare_ids = implode(",",array_fill(0, count($user_favs), '%d'));
				}
				$where .= $wpdb->prepare(" AND $wpdb->posts.ID IN ($prepare_ids)",$fav_in);

				// replace 'post' with GD post types

				//print_r($post_types);
				if(!isset($wp_query->query['post_type'])){
					$post_types = geodir_get_posttypes();
					$prepare_cpts = implode(",",array_fill(0, count($post_types), '%s'));
					$gd_cpt_replace = $wpdb->prepare("$wpdb->posts.post_type IN ($prepare_cpts)",$post_types);
					$where = str_replace("$wpdb->posts.post_type = 'post'",$gd_cpt_replace,$where);
				}

				$user_id = get_current_user_id();
				$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;
				// check if restricted
				$post_type = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : 'gd_place';
				$author_favorites_private = isset($cpts[$post_type]['author_favorites_private']) && $cpts[$post_type]['author_favorites_private'] ? true : false;
				if($author_favorites_private && $author_id != $user_id){
					$where .= " AND 1=2";
				}

			}
		}elseif(is_author()){
			$user_id = get_current_user_id();

			$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;
			if($author_id && $author_id == $user_id){
				$where = str_replace("{$wpdb->posts}.post_status = 'publish'","{$wpdb->posts}.post_status = 'publish' OR {$wpdb->posts}.post_status = 'draft' OR {$wpdb->posts}.post_status = 'pending'",$where);
			}

			// check if restricted
			$post_type = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : 'gd_place';
			$author_posts_private = isset($cpts[$post_type]['author_posts_private']) && $cpts[$post_type]['author_posts_private'] ? true : false;
			if($author_posts_private && $author_id != $user_id){
				$where .= " AND 1=2";
			}

		}

		return $where;
	}


    /**
     * Posts join.
     *
     * @since 2.0.0
     *
     * @param string $join join.
     * @param array $query Optional. Query. Default array.
     *
     * @global object $wpdb WordPress Database object.
     * @global object $table_prefix WordPress Database object.
     * @global object $geodir_post_type WordPress Database object.
     *
     * @return mixed|void
     */
	public function posts_join($join, $query = array()){

		global $wpdb, $table_prefix, $geodir_post_type;
		if(self::is_gd_main_query($query)){
			$table = geodir_db_cpt_table($geodir_post_type);

			$join .= " INNER JOIN " . $table . " ON (" . $table . ".post_id = $wpdb->posts.ID)  "; // @todo inner join seems faster but we should so tests with large datasets

		}

		return apply_filters( 'geodir_posts_join', $join, $query );
	}

    /**
     * Posts group by.
     *
     * @since 2.0.0
     *
     * @param string $groupby Group by.
     * @param array $query Optional. Query. Default array.
     * @return mixed|void
     */
	public function posts_groupby( $groupby, $query = array() ) {
		return apply_filters( 'geodir_posts_groupby', $groupby, $query );
	}

	/**
     * Posts Order by.
     *
     * @since 2.0.0
     *
	 * @param string $orderby Order by.
	 * @param array $query Optional. Query. Default array.
	 * @return mixed
	 */
	public function posts_orderby($orderby, $query = array()){
		global $wpdb, $table_prefix, $geodir_post_type,$snear,$s;

		if(self::is_gd_main_query($query)) {


			$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );
			$sort_by          = '';
			$orderby          = ' ';
			$default_sort     = '';


			if ( get_query_var( 'order_by' ) ) {
				$sort_by = get_query_var( 'order_by' );
			}

			if ( isset( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] != '' && is_main_query() ) {
				$sort_by = esc_attr( $_REQUEST['sort_by'] );
			}

			if ( $sort_by == '' ) {
//echo '###';exit;

				if ( $support_location && $snear != '' ) {
					//$orderby .= " distance,";
					$sort_by = 'distance_asc';
				} elseif ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' ) {
					$sort_by = 'search_best';
				} else {
					$default_sort = geodir_get_posts_default_sort( $geodir_post_type );
//				echo '###'.$default_sort;exit;
					if ( ! empty( $default_sort ) ) {
						$sort_by = $default_sort;
					}
				}

			}

			//if(geodir_is_page('search')){}

			$table = geodir_db_cpt_table( $geodir_post_type );

			$orderby = self::sort_by_sql( $sort_by, $geodir_post_type );

			$orderby = self::sort_by_children( $orderby, $sort_by, $geodir_post_type );

//		echo '###'.$orderby;exit;
			/**
			 * Filter order by SQL.
			 *
			 * @since 1.0.0
			 *
			 * @param string $orderby The orderby query string.
			 * @param string $sort_by Sortby query string.
			 * @param string $table Listing table name.
			 * @param WP_Query $query The WP_Query.
			 */
			$orderby = apply_filters( 'geodir_posts_order_by_sort', $orderby, $sort_by, $table, $query );

			if ( $sort_by != 'random' ) {
				//$orderby .= $table . ".featured asc, $wpdb->posts.post_date desc, $wpdb->posts.post_title ";
			}
		}

		return $orderby;
	}

	public static function sort_by_children($orderby,$sort_by, $geodir_post_type){
		global $wpdb;

		$sort_array = explode('_', $sort_by);

		$sort_by_count = count($sort_array);

		$order = $sort_array[$sort_by_count - 1];

		$htmlvar_name = str_replace('_' . $order, '', $sort_by);

		if($htmlvar_name && $order) {


			$parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name = %s AND sort = %s AND post_type = %s", $htmlvar_name, $order, $geodir_post_type ) );


			if ( $parent_id ) {
				$children = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type = %s AND tab_parent = %d ORDER BY sort_order ASC", $geodir_post_type, $parent_id ) );
				//print_r($children);exit;

				if ( $children ) {
					//print_r($children);exit;
					foreach ( $children as $child ) {
						$child_sort_by = $child->htmlvar_name . "_" . $child->sort;
						$child_sort    = self::sort_by_sql( $child_sort_by, $geodir_post_type );
						if ( $child_sort ) {
							$orderby .= " ," . $child_sort;
						}
						//$orderby .= " ," . self::sort_by_sql($child_sort_by,$geodir_post_type);
					}
				}
			}
		}

		return $orderby;
	}

    /**
     * Sort by sql.
     *
     * @since 2.0.0
     *
     * @param string $sort_by Optional. Sort by. Default title_asc.
     * @param string $post_type Optional. Post type. Default gd_place.
     *
     * @global object $wpdb WordPress Database object.
     *
     * @return string
     */
	public static function sort_by_sql($sort_by = 'post_title_asc',$post_type = "gd_place"){
		global $wpdb;

		//echo '###'.$sort_by;

		$orderby = '';
		$table = geodir_db_cpt_table($post_type);
		$order_by_parts = array();

		switch ($sort_by):
			case 'distance_asc':
				$order_by_parts[] = "distance";
				$order_by_parts[] = self::search_sort();
				break;
			case 'search_best':
				$order_by_parts[] = self::search_sort();
				break;
			case 'post_status_desc':
			case 'random':
				$rand_seed = self::get_rand_seed();
				$order_by_parts[] = "rand($rand_seed)";
				break;
			case 'az':
			case 'post_title_asc':
			case 'title_asc':
				$order_by_parts[] = "$wpdb->posts.post_title asc";
				break;
			case 'za':
			case 'post_title_desc':
			case 'title_desc':
				$order_by_parts[] = "$wpdb->posts.post_title desc";
				break;
			case 'add_date_asc':
				$order_by_parts[] = "$wpdb->posts.post_date asc";
				break;
			case 'latest':
			case 'add_date_desc':
				$order_by_parts[] = "$wpdb->posts.post_date desc";
				break;
			case 'review_asc':
				$order_by_parts[] = $table . ".rating_count ASC";
				$order_by_parts[] = $table . ".overall_rating ASC";
				break;
			case 'high_review':
			case 'review_desc':
				$order_by_parts[] = $table . ".rating_count DESC";
				$order_by_parts[] = $table . ".overall_rating DESC";
				break;
			case 'rating_asc':
			case 'rating_desc':
			case 'high_rating':
				if ( $sort_by == 'high_rating' ) {
					$sort_by = 'rating_desc';
				}
				$rating_order = $sort_by == 'rating_asc' ? "ASC" : "DESC";
				$use_bayesian = apply_filters('geodir_use_bayesian',true,$table);
				$avg_rating = 0;
				if($use_bayesian){
					$avg_num_votes = get_transient( 'gd_avg_num_votes_'.$table );
					if(!$avg_num_votes){
						$avg_num_votes = $wpdb->get_var("SELECT SUM(rating_count) FROM $table");
						if($avg_num_votes){
							$avg_rating = get_transient( 'gd_avg_rating_'.$table );
							if(!$avg_rating){
								$avg_rating = $wpdb->get_var("SELECT SUM(overall_rating) FROM $table")/$avg_num_votes;
							}
							set_transient( 'gd_avg_num_votes_'.$table, $avg_num_votes, 12 * HOUR_IN_SECONDS );
							set_transient( 'gd_avg_rating_'.$table, $avg_rating , 12 * HOUR_IN_SECONDS );
						}
					}
					if(!$avg_num_votes){ $avg_num_votes = 0;}
					$order_by_parts[] = " (( $avg_num_votes * $avg_rating ) + (" . $table . ".rating_count * " . $table . ".overall_rating ))  / ( $avg_num_votes + " . $table . ".rating_count )  $rating_order";
				}else{
					$order_by_parts[] = $table . ".overall_rating $rating_order";
					$order_by_parts[] = $table . ".rating_count $rating_order";
				}
				break;
			default:
//				echo $sort_by.'###'.$default_sort;
				$default_sort = geodir_get_posts_default_sort( $post_type );
				if($default_sort == '' && $sort_by == $default_sort){
					$order_by_parts[] = "$wpdb->posts.post_date desc";
				}else{
					$order_by_parts[] = self::custom_sort($orderby, $sort_by, $table);
				}
				break;
		endswitch;


		$orderby = implode(", ",array_filter($order_by_parts));

		return $orderby;
	}

	public static function search_sort($orderby = ''){
		global $s, $gd_exact_search;

		if ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' ) {
			if ( $gd_exact_search ) {
				$keywords = array( $s );
			} else {
				$keywords = explode( " ", $s );

				if ( is_array( $keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
					foreach ( $keywords as $kkey => $kword ){
						if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
							unset( $keywords[ $kkey ] );
						}
					}
				}
			}

			if ( count( $keywords ) > 1 ) {
				$orderby = "( gd_titlematch * 2  + gd_exacttitle * 10 + gd_alltitlematch_part * 100 + gd_titlematch_part * 50 + gd_content * 1.5) DESC";
			} else {
				$orderby = "( gd_titlematch * 2  + gd_exacttitle * 10 + gd_content * 1.5) DESC";
			}

//			$orderby_parts = array(); // PART => FACTOR
//			$orderby_parts['gd_titlematch'] = '2';
//			$orderby_parts['gd_exacttitle'] = '2';
//			$orderby_parts['gd_content'] = '2';
//			$orderby_parts['gd_titlematch'] = '2';
//			$orderby_parts['gd_titlematch'] = '2';

		}

		return $orderby;
	}

	/**
	 * Listing orderby custom sort.
     *
     * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 * @param string $orderby The orderby query string.
	 * @param string $sort_by Sortby query string.
	 * @param string $table Listing table name.
	 * @return string Modified orderby query.
	 */
	public static function  custom_sort($orderby, $sort_by, $table)
	{

		global $wpdb;

		if ($sort_by != '' && (!is_search() || ( isset($_REQUEST['s']) && isset($_REQUEST['snear']) && $_REQUEST['snear']=='' && ( $_REQUEST['s']=='' ||  $_REQUEST['s']==' ') ) )) {

			$sort_array = explode('_', $sort_by);

			$sort_by_count = count($sort_array);

			$order = $sort_array[$sort_by_count - 1];

			if ($sort_by_count > 1 && ($order == 'asc' || $order == 'desc')) {

				$sort_by = str_replace('_' . $order, '', $sort_by);

				switch ($sort_by):

					case 'post_date':
					case 'comment_count':

						$orderby = "$wpdb->posts." . $sort_by . " " . $order . ", ".$table . ".overall_rating " . $order;
						break;
					// sort by featured image
					case 'post_images':
						$orderby = $table . ".featured_image " . $order;
						break;

					case 'distance':
						$orderby = $sort_by . " " . $order;
						break;


					// sort by rating
					case 'overall_rating':

						$use_bayesian = apply_filters('gd_use_bayesian',true,$table);
						$avg_rating = 0;
						if($use_bayesian){
							$avg_num_votes = get_transient( 'gd_avg_num_votes_'.$table );
							if(!$avg_num_votes){
								$avg_num_votes = $wpdb->get_var("SELECT SUM(rating_count) FROM $table");
								if($avg_num_votes){

									$avg_rating = get_transient( 'gd_avg_rating_'.$table );
									if(!$avg_rating){
										$avg_rating = $wpdb->get_var("SELECT SUM(overall_rating) FROM $table")/$avg_num_votes;
									}
									set_transient( 'gd_avg_num_votes_'.$table, $avg_num_votes, 12 * HOUR_IN_SECONDS );
									set_transient( 'gd_avg_rating_'.$table, $avg_rating , 12 * HOUR_IN_SECONDS );
								}
							}

							if(!$avg_num_votes){ $avg_num_votes = 0;}

							$orderby = " (( $avg_num_votes * $avg_rating ) + (" . $table . ".rating_count * " . $table . ".overall_rating ))  / ( $avg_num_votes + " . $table . ".rating_count )  $order";

							//$orderby = " ( " . $table . ".rating_count * " . $table . ".overall_rating ) + (" . $table . ".rating_count * " . $table . ".overall_rating )   / ( " . $table . ".rating_count + " . $table . ".rating_count )  $order , "; // seems to work mostly with no extra overheads
						}else{
							$orderby = " " . $table . "." . $sort_by . "  " . $order . ", " . $table . ".rating_count " . $order;
						}

						break;


					default:
						if (self::column_exist($table, $sort_by)) {
//							echo '###'.$table . "." . $sort_by . " " . $order;exit;
							$orderby = $table . "." . $sort_by . " " . $order;
						}else{
							$orderby = "$wpdb->posts.post_date desc";
						}
						break;

				endswitch;

			}

		}

		return $orderby;
	}

	/**
	 * Check table column exist or not.
     *
     * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
     *
	 * @param string $db The table name.
	 * @param string $column The column name.
	 * @return bool If column exists returns true. Otherwise false.
	 */
	public static function column_exist($db, $column)
	{
		global $wpdb;
		$exists = false;
		$columns = $wpdb->get_col("show columns from $db");
		foreach ($columns as $c) {
			if ($c == $column) {
				$exists = true;
				break;
			}
		}
		return $exists;
	}



	/**
	 * Remove the query.
     *
     * @since 2.0.0
	 */
	public function remove_product_query() {
		remove_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );
	}



	/**
	 * Sets a key and value in $wp object if the current page is a geodir page.
	 *
	 * @since   1.0.0
	 * @since   1.5.4 Added check for new style GD homepage.
	 * @since   1.5.6 Added check for GD invoices and GD checkout page.
	 * @package GeoDirectory
	 *
	 * @param object $wp WordPress object.
	 */
	public static function set_is_geodir_page( $wp ) {

		if ( ! is_admin() ) {
			//$wp->query_vars['gd_is_geodir_page'] = false;
			//print_r()
			if ( empty( $wp->query_vars ) || ! array_diff( array_keys( $wp->query_vars ), array(
					'preview',
					'page',
					'paged',
					'cpage'
				) )
			) {
				if ( geodir_is_page( 'home' ) ) {
					$wp->query_vars['gd_is_geodir_page'] = true;
				}


			}

			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['page_id'] ) ) {
				if (
					$wp->query_vars['page_id'] == geodir_add_listing_page_id()
					|| $wp->query_vars['page_id'] == geodir_preview_page_id()
					|| $wp->query_vars['page_id'] == geodir_success_page_id()
					|| $wp->query_vars['page_id'] == geodir_location_page_id()
//					|| $wp->query_vars['page_id'] == geodir_search_page_id()
				) {
					$wp->query_vars['gd_is_geodir_page'] = true;
				}
			}

			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['pagename'] ) ) {
				$page = get_page_by_path( $wp->query_vars['pagename'] );

				if ( ! empty( $page ) && (
						$page->ID == geodir_add_listing_page_id()
						|| $page->ID == geodir_preview_page_id()
						|| $page->ID == geodir_success_page_id()
						|| $page->ID == geodir_location_page_id()
						|| $page->ID == geodir_search_page_id()
					)
				) {
					$wp->query_vars['gd_is_geodir_page'] = true;
				}
			}


			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['post_type'] ) && $wp->query_vars['post_type'] != '' ) {
				$requested_post_type = $wp->query_vars['post_type'];
				// check if this post type is geodirectory post types
				$post_type_array = geodir_get_posttypes();
				if ( in_array( $requested_post_type, $post_type_array ) ) {
					$wp->query_vars['gd_is_geodir_page'] = true;
				}
			}

			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) ) {
				$geodir_taxonomis = geodir_get_taxonomies( '', true );
				if ( ! empty( $geodir_taxonomis ) ) {
					foreach ( $geodir_taxonomis as $taxonomy ) {
						if ( array_key_exists( $taxonomy, $wp->query_vars ) ) {
							$wp->query_vars['gd_is_geodir_page'] = true;
							break;
						}
					}
				}

			}


			// author pages
			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['author_name'] ) && isset( $_REQUEST['geodir_dashbord'] ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $wp->query_vars['gd_favs'] )) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}

			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] ) && isset( $_REQUEST['geodir_search'] ) ) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}

		} // end of is admin
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
     *
     * @global object $wp WordPress object.
     *
     * @since 2.0.0
	 */
	public function parse_request() {
		global $wp;

		if(isset( $_REQUEST['fl_builder'] )){return;} // fix for BB not working on search page

		self::set_is_geodir_page( $wp );

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			} elseif ( isset( $wp->query_vars[ $var ] ) ) {
				$wp->query_vars[ $key ] = $wp->query_vars[ $var ];
			}
		}
	}


	/**
	 * Add the page id to the query variables.
	 *
	 * @since 1.0.0
	 * @global object $wp_query WordPress Query object.
	 * @return array WordPress Query object.
	 */
	public function add_page_id_in_query_var()
	{
		global $wp_query;

		$page_id = $wp_query->get_queried_object_id();

		if (!get_query_var('page_id') && !is_archive()) {
			// fix for WP tags conflict with enfold theme
			$theme_name = geodir_strtolower(wp_get_theme());
			if (!geodir_is_geodir_page() && strpos($theme_name, 'enfold') !== false) {
				return $wp_query;
			}
			if($page_id){
				$wp_query->set('page_id', $page_id);
			}

		}

		return $wp_query;
	}


	###############################################################################################################################
	######################################################### OLD FUNCTION ########################################################
	###############################################################################################################################

	/**
	 * Get any errors from querystring.
     *
     * @since 2.0.0
	 */
	public function get_errors() {
		if ( ! empty( $_GET['gd_error'] ) && ( $error = sanitize_text_field( $_GET['gd_error'] ) ) ) {
			//@todo add some error notice
		}
	}

	/**
	 * Init query vars by loading options.
     *
     * @since 2.0.0
	 */
	public function init_query_vars() {
		// Query vars to add to WP.
		$this->query_vars = array(
			// Location vars.
//			'country'           => 'country',
//			'region'            => 'region',
//			'city'              => 'city',
			'gd_is_geodir_page' => 'gd_is_geodir_page',
		);
	}



	/**
	 * Add query vars.
     *
     * @since 2.0.0
	 *
	 * @access public
	 *
	 * @param array $vars
	 *
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		foreach ( $this->get_query_vars() as $key => $var ) {
			$vars[] = $key;
		}

		return $vars;
	}

	/**
	 * Get query vars.
     *
     * @since 2.0.0
	 *
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'geodirectory_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
     *
     * @since 2.0.0
	 *
	 * @return string
	 */
	public function get_current_endpoint() {
		global $wp;
		foreach ( $this->get_query_vars() as $key => $value ) {
			if ( isset( $wp->query_vars[ $key ] ) ) {
				return $key;
			}
		}

		return '';
	}


    /**
     * Are we currently on the front page?
     *
     * @since 2.0.0
     *
     * @param object $q page object.
     * @return bool
     */
	private function is_showing_page_on_front( $q ) {
		return $q->is_home() && 'page' === get_option( 'show_on_front' );
	}

    /**
     * Is the front page a page we define?
     *
     * @since 2.0.0
     *
     * @param int $page_id Page id.
     * @return bool
     */
	private function page_on_front_is( $page_id ) {
		return absint( get_option( 'page_on_front' ) ) === absint( $page_id );
	}




	/**
	 * Make custom field order by clause for custom sorting.
	 *
	 * @since 1.6.18
	 * @package GeoDirectory
	 *
	 * @param string $sorting Listing sort option.
	 * @param string $table Listing table name.
	 * @return string|null If field exists in table returns order by clause else returns empty.
	 */
	public static function prepare_sort_order( $sorting, $table ) {
		$orderby = '';

		if ( empty( $sorting ) || empty( $table ) ) {
			return $orderby;
		}

		if ( strpos( strtoupper( $sorting ), '_ASC' ) !== false || strpos( strtoupper( $sorting ), '_DESC') !== false ) {
			$sorting_array = explode( '_', $sorting );

			if ( ( $count = count( $sorting_array ) ) > 1 ) {
				$order = !empty( $sorting_array[$count - 1] ) ? strtoupper( $sorting_array[$count - 1] ) : '';
				array_pop( $sorting_array );

				if ( !empty( $sorting_array ) && ( $order == 'ASC' || $order == 'DESC' ) ) {
					$sort_by = implode( '_', $sorting_array );

					if ( geodir_column_exist( $table, $sort_by ) ) {
						$orderby = $table . "." . $sort_by . " " . $order;
					}
				}
			}
		}

		return $orderby;
	}


}

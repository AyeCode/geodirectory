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
			add_filter( 'pre_handle_404', array( __CLASS__, 'pre_handle_404' ), 20, 2 );
		}

		add_filter( 'split_the_query', array( $this, 'split_the_query' ), 100, 2 );
		add_action( 'wp', array( $this, 'set_wp_the_query' ), 1, 1 );

		add_filter( 'geodir_main_query_posts_where', array( $this, 'main_query_posts_where' ), 10, 3 );
		add_filter( 'geodir_posts_order_by_sort', array( $this, 'posts_order_by_sort' ), 10, 4 );

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
		if ( ! geodir_is_geodir_page() ) {
			// Exclude GD templates from WP search.
			$exclude_posts = ! empty( $q->is_search ) && ( ! is_admin() || wp_doing_ajax() ) ? true : false;
			$exclude_posts = apply_filters( 'geodir_wp_search_exclude_posts', $exclude_posts, $q );

			if ( $exclude_posts ) {
				$exclude_ids = GeoDir_SEO::get_noindex_page_ids();

				if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
					$q->set( 'post__not_in', $exclude_ids );
				}
			}

			return;
		}

		// Remove all pre filters, controversial but should only affect our own queries.
		remove_all_filters( 'query' );
		remove_all_filters( 'posts_search' );
		remove_all_filters( 'posts_fields' );
		remove_all_filters( 'posts_join' );
		remove_all_filters( 'posts_groupby' );
		remove_all_filters( 'posts_orderby' );
		remove_all_filters( 'posts_where' );

		// @todo for testing only.
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

		// If post_type or archive then add query filters.
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 10, 2 );
			add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2);
			add_filter( 'posts_where', array( $this, 'author_where' ), 10, 2 );
			//add_filter( 'posts_where', array( $this, 'posts_having' ), 10000, 2 ); // make sure its the last WHERE param
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
		} elseif ( geodir_is_page( 'search' ) ) {
			// Some page builders breaks editor.
			if (
				( ( function_exists( 'et_divi_load_scripts_styles' ) || function_exists( 'dbp_filter_bfb_enabled' ) ) && ! empty( $_REQUEST['et_fb'] ) && ! empty( $_REQUEST['et_bfb'] ) ) // Divi
				|| (
					class_exists( 'Brizy_Editor' ) &&
					(
						( isset( $_GET[ Brizy_Editor::prefix( '-edit' ) ] ) || isset( $_GET[ Brizy_Editor::prefix( '-edit-iframe' ) ] ) ) ||
						Brizy_Editor_Entity::isBrizyEnabled( geodir_search_page_id() )
					)
				) // Brizy
			) {
			} else if ( ! isset( $_REQUEST['elementor-preview'] ) ) {
				$q->is_page = false;
				$q->is_singular = false;
			}

			$q->is_search = true;
			$q->is_archive = true;
			$q->is_paged = true;
			//$q->is_post_type_archive = true;
			//$q->in_the_loop = true; // This breaks elementor template
			//$q->set('is_page',false);
			//$q->set('is_search',true);
			//$q->set('post_type','gd_place');

			add_filter( 'posts_join', array( $this, 'posts_join' ), 1, 2 );
			add_filter( 'posts_fields', array( $this, 'posts_fields' ), 1, 2 );
			add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );
			//add_filter( 'posts_limits', array( $this, 'posts_limits' ),10,2 );
			add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 1, 2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 1, 2 );
			add_filter( 'posts_clauses', array( $this, 'posts_having' ), 99999, 2 ); // Make sure its the last WHERE param and after GROUP BY if there

			// Setup search globals
			global $wp_query, $wpdb, $geodir_post_type, $table, $dist, $s, $snear, $s, $s_A, $s_SA, $gd_exact_search;

			if ( isset( $_REQUEST['scat'] ) && $_REQUEST['scat'] == 'all' ) {
				$_REQUEST['scat'] = '';
			}

			// Distance
			if ( isset( $_REQUEST['dist'] ) ) {
				$dist = ( $_REQUEST['dist'] != '0' && $_REQUEST['dist'] != '' ) ? geodir_sanitize_float( $_REQUEST['dist'] ) : 25000;
			} else if ( geodir_get_option( 'search_radius' ) != '' ) {
				$dist = geodir_get_option( 'search_radius' );
			} else {
				$dist = 25000;
			}

			if ( isset( $_REQUEST['snear'] ) ) {
				$snear = trim( esc_attr( $_REQUEST['snear'] ) );
			}

			if ( isset( $_REQUEST['s'] ) ) {
				$s = get_search_query();
				if ( $s != '' ) {
					$s = str_replace( array( "%E2%80%99", "’" ), array( "%27", "'" ), $s ); // apple suck
				}
				$s = trim( esc_attr( wp_strip_all_tags( $s ) ) );
			}

			if ( is_null( $s ) ) {
				$s = '';
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

			if ( $s !== '' && strstr( $s, ',' ) ) {
				$s_AA = str_replace( " ", "", $s );
				$s_A = explode( ",", $s_AA );
				$s_A = implode( '","', $s_A );
				$s_A = '"' . $s_A . '"';
			} else {
				$s_A = '"' . $s . '"';
			}

			if ( $s !== '' && strstr( $s, ' ' ) ) {
				$s_SA = explode( " ", $s );
			} else {
				$s_SA = '';
			}
		} else if ( is_author() ) {
			add_filter( 'posts_where', array( $this, 'author_where' ), 10, 2 );
			//$q->is_archive = true;
			//$q->is_post_type_archive = true;
		}

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
		if ( self::is_gd_main_query( $query ) && ! defined( 'GEODIR_MAP_SEARCH' ) ) {
			global $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $snear, $geodirectory;

			$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );

			if ( $support_location && ( $latlon = $geodirectory->location->get_latlon() ) ) {
				/*
				 * The HAVING clause is often used with the GROUP BY clause to filter groups based on a specified condition.
				 * If the GROUP BY clause is omitted, the HAVING clause behaves like the WHERE clause.
				 */
				if ( strpos( $clauses['where'], ' HAVING ') === false && strpos( $clauses['groupby'], ' HAVING ') === false ) {
					$dist = get_query_var( 'dist' ) ? geodir_sanitize_float( get_query_var( 'dist' ) ) : geodir_get_option( 'search_radius', 5 );

					if ( GeoDir_Post_types::supports( $geodir_post_type, 'service_distance' ) ) {
						$_table = geodir_db_cpt_table( $geodir_post_type );
						$having = $wpdb->prepare( " HAVING ( ( `{$_table}`.`service_distance` > 0 AND distance <= `{$_table}`.`service_distance` ) OR ( ( `{$_table}`.`service_distance` <= 0 OR `{$_table}`.`service_distance` IS NULL ) AND distance <= %f ) )", $dist );
					} else {
						$having = $wpdb->prepare( " HAVING distance <= %f ", $dist );
					}

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
	public function posts_fields( $fields, $query = array() ) {
		if ( self::is_gd_main_query( $query ) ) {
			if ( ! ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) ) {
				global $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $snear, $gd_exact_search,$geodirectory;
				$support_location = $geodir_post_type && GeoDir_Post_types::supports( $geodir_post_type, 'location' );

				$table = geodir_db_cpt_table( $geodir_post_type );

				$fields .= ", " . $table . ".* ";

				if ( $support_location && ( $latlon = $geodirectory->location->get_latlon() ) ) {
					$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
					$lat = $latlon['lat'];
					$lon = $latlon['lon'];

					$fields .= $wpdb->prepare( " , (%f * 2 * ASIN(SQRT( POWER(SIN(((%f) - (`{$table}`.latitude)) * pi()/180 / 2), 2) +COS((%f) * pi()/180) * COS( (`{$table}`.latitude) * pi()/180) *POWER(SIN((%f - `{$table}`.longitude) * pi()/180 / 2), 2) ))) AS distance ", $DistanceRadius,$lat,$lat,$lon );
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

									$gd_titlematch_part .= $wpdb->prepare( "( " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s ) ", array( $keyword . '%', '% ' . $keyword . '%' ) );

									if ( $count < count( $keywords ) ) {
										$gd_titlematch_part .= $key . " ";
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
					$fields .= $wpdb->prepare( ", CASE WHEN " . $wpdb->posts . ".post_title LIKE %s THEN 1 ELSE 0 END AS gd_exacttitle, GD_TITLEMATCH_PART CASE WHEN ( " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s ) THEN 1 ELSE 0 END AS gd_titlematch, CASE WHEN ( " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s ) THEN 1 ELSE 0 END AS gd_content", array(
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
					$fields = str_replace( "gd_exacttitle, GD_TITLEMATCH_PART", "gd_exacttitle, {$gd_titlematch_part}", $fields );
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
	//	echo '###limit###'.$limits;

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

			// check if we already have the CPT query
			$cpt_query = $wpdb->prepare( "$wpdb->posts.post_type = %s", $geodir_post_type );
			// only add CPT if required (duplicate bad for big DBs)
			if (strpos($where, $cpt_query) === false) {
				$where .= $wpdb->prepare( " AND $wpdb->posts.post_type = %s ", $geodir_post_type );
			}

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
						$keywords = array_unique( explode( " ", $s ) );
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
								$better_search_term = $wpdb->prepare( " OR {$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_title LIKE %s ", array( $keyword . '%', '% ' . $keyword . '%' ) );

								/**
								 * Filter the search query keywords SQL.
								 *
								 * @since 1.5.9
								 * @package GeoDirectory
								 *
								 * @param string $better_search_terms The query values.
								 * @param array $keywords The array of keywords for the query.
								 * @param string $keyword The single keyword being searched.
								 */
								$better_search_terms .= apply_filters( "geodir_search_better_search_terms", $better_search_term, $keywords, $keyword );
							}
						}
					}

					// Search in _search_title
					$_search = geodir_sanitize_keyword( $s, $post_types );

					// If original search & keyword are not same.
					if ( $gd_exact_search ) {
						$_keywords = array( $_search );
					} else {
						$_keywords = array_unique( explode( " ", $_search ) );

						if ( is_array( $_keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
							foreach ( $_keywords as $kkey => $kword ) {
								if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
									unset( $_keywords[ $kkey ] );
								}
							}
						}
					}

					if ( ! empty( $_keywords ) ) {
						$_search_title_where = array();

						foreach ( $_keywords as $_keyword ) {
							if ( empty( $_keyword ) ) {
								continue;
							}

							$_search_title_part = "`{$table}`.`_search_title` LIKE '{$_keyword}%' OR `{$table}`.`_search_title` LIKE '% {$_keyword}%'";

							/**
							 * Filter the search query titles SQL.
							 *
							 * @since 2.0.0.82
							 *
							 * @param string $_search_title_part The query values.
							 * @param string $_keyword The single keyword being searched.
							 * @param array $_keywords The array of keywords for the query.
							 */
							$_search_title_part = apply_filters( "geodir_search_title_keyword_part", $_search_title_part, $_keyword, $_keywords );

							if ( ! empty( $_search_title_part ) ) {
								$_search_title_where[] = $_search_title_part;
							}
						}

						if ( ! empty( $_search_title_where ) ) {
							$_search_title_parts = " OR " . implode( " OR ", $_search_title_where );
							$better_search_terms .= apply_filters( "geodir_search_title_keyword_parts", $_search_title_parts, $keywords );
						}
					}

					/**
					 * Filter the search query keywords SQL.
					 *
					 * @since 2.0.0.82
					 *
					 * @param string $better_search_terms The query values.
					 * @param string $s The searched keyword.
					 */
					$better_search_terms = apply_filters( "geodir_search_better_search_terms_parts", $better_search_terms, $s );
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
					$content_where = $wpdb->prepare( " OR ($wpdb->posts.post_content LIKE %s OR $wpdb->posts.post_content LIKE %s OR $wpdb->posts.post_content LIKE %s OR $wpdb->posts.post_content LIKE %s) ", array( $s . '%', '% ' . $s . '%', '%>' . $s . '%', '%\n' . $s . '%' ) );
					$content_where = str_replace( "\\n", "\n", $content_where ); // $wpdb->prepare() adds slash that unable to match in search.

					/**
					 * Filter the search query content where values.
					 *
					 * @since 1.5.0
					 * @package GeoDirectory
					 *
					 * @param string $content_where The query values.
					 */
					$content_where = apply_filters( "geodir_search_content_where", $content_where );

					if ( $gd_exact_search ) {
						$terms_where = $wpdb->prepare( " AND ($wpdb->terms.name LIKE %s ) ", array( $s ) );
					} else {
						$terms_where = $wpdb->prepare( " AND ($wpdb->terms.name LIKE %s OR $wpdb->terms.name LIKE %s OR $wpdb->terms.name IN ($s_A)) ", array( $s . '%', '% '. $s . '%' ) );
					}

					/**
					 * Filter the search query term values.
					 *
					 * @since 1.5.0
					 * @package GeoDirectory
					 *
					 * @param string $terms_where The SQL where.
					 */
					$terms_where = apply_filters( "geodir_search_terms_where", $terms_where );
				}

				$_post_category = array();
				if ( geodir_is_page( 'search' ) && isset( $_REQUEST['spost_category'] ) && ( ( is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'][0] ) ) || ( ! is_array( $_REQUEST['spost_category'] ) && ! empty( $_REQUEST['spost_category'] ) ) ) ) {
					if ( is_array( $_REQUEST['spost_category'] ) ) {
						$_post_category = array_map( 'absint', $_REQUEST['spost_category'] );
					} else {
						$_post_category = array( absint( $_REQUEST['spost_category'] ) );
					}
				}

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
							if ( ! empty( $_post_category ) && in_array( $term->term_id, $_post_category ) ) {
								continue;
							}

							if ( $term->taxonomy == $post_types . "category" ) {
								$terms_sql .= $wpdb->prepare(" OR FIND_IN_SET( %d , " . $table . ".post_category ) ", $term->term_id );
							} else {
								$terms_sql .= $wpdb->prepare(" OR FIND_IN_SET( %s, " . $table . ".post_tags ) ", $term->name );
							}
						}
					}
				}

				$latlon = $geodirectory->location->get_latlon();
				// fake near if we have GPS
				if ( $snear == '' && $latlon ) {
					$snear = ' ';
				}

				// post_status
				$status = geodir_get_post_stati( 'search', array( 'post_type' => $post_types ) );
				if ( empty( $status ) ) {
					$status = array( 'publish' );
				} elseif ( is_scalar( $status ) ) {
					$status = array( $status );
				}

				if ( count( $status ) > 1 ) {
					$status_where = "AND {$wpdb->posts}.post_status IN( '" . implode( "', '", $status ) . "' )";
				} else {
					$status_where = "AND {$wpdb->posts}.post_status = '{$status[0]}'";
				}

				/**
				 * Filter post_status where condition.
				 *
				 * @soince 2.1.1.5
				 *
				 * @param string $status_where Status where condition.
				 * @param array  $status Post status array.
				 * $param string $post_types Post type.
				 */
				$status_where = apply_filters( 'geodir_posts_where_post_status', $status_where, $status, $post_types );

				if ( $support_location && $snear != '' && $latlon && ! defined( 'GEODIR_MAP_SEARCH' ) ) {
					$lat = $latlon['lat'];
					$lon = $latlon['lon'];
					$between          = geodir_get_between_latlon( $lat, $lon, $dist );
					$post_title_where = $s != "" ? $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", array( $s ) ) : "1=1";
					$where .= " AND ( ( $post_title_where $better_search_terms ) $content_where $terms_sql ) AND $wpdb->posts.post_type = '{$post_types}' {$status_where}";

					if ( ! empty( $between ) && ! ( GeoDir_Post_types::supports( $post_types, 'service_distance' ) && $geodirectory->location->get_latlon() ) ) {
						$where .= $wpdb->prepare( " AND ( latitude BETWEEN %f AND %f ) AND ( longitude BETWEEN %f AND %f ) ", $between['lat1'], $between['lat2'], $between['lon1'], $between['lon2'] );
					}

					if ( isset( $_REQUEST['sdistance'] ) && $_REQUEST['sdistance'] != 'all' ) {
						$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
						$where .= $wpdb->prepare(" AND CONVERT((%f * 2 * ASIN(SQRT( POWER(SIN(((%f) - (`{$table}`.latitude)) * pi()/180 / 2), 2) +COS((%f) * pi()/180) * COS( (`{$table}`.latitude) * pi()/180) *POWER(SIN((%f - `{$table}`.longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= %f",$DistanceRadius, $lat, $lat, $lon,  $dist );
					}

					// Private address
					if ( GeoDir_Post_types::supports( $post_types, 'private_address' ) ) {
						$where .= " AND ( `{$table}`.`private_address` IS NULL OR `{$table}`.`private_address` <> 1 ) ";
					}
				} else {
					$post_title_where = $s != "" ? $wpdb->prepare( "{$wpdb->posts}.post_title LIKE %s", array( $s ) ) : "1=1";
					$where .= " AND ( ( $post_title_where $better_search_terms ) $content_where $terms_sql ) AND $wpdb->posts.post_type = '{$post_types}' {$status_where}";
				}

				// Replace unwanted 1=1 clause.
				$where = str_replace( " AND ( ( 1=1  )   ) AND", "AND", $where );
			}

			/**
			 * @since 2.0.0.67
			 */
			$where = apply_filters( 'geodir_main_query_posts_where', $where, $query, $geodir_post_type );
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
	public function author_where( $where, $query = array() ) {
		global $wp_query, $wpdb;

		if ( ! self::is_gd_main_query( $query ) ) {
			return $where;
		}

		$cpts = geodir_get_posttypes( 'array' );

		// author saves/favs filter
		if ( is_author() && ! empty( $wp_query->query['gd_favs'] ) ) {
			$post_types = array_keys( $cpts );

			$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;

			if ( $author_id ) {
				$where = str_replace( "AND ($wpdb->posts.post_author = $author_id)", "", $where ); // Remove the author restriction

				$user_favs = geodir_get_user_favourites( $author_id );

				if ( empty( $user_favs ) ) {
					$fav_in = "''"; // blank it so we get no results
				} else {
					$fav_in = $user_favs;
					$prepare_ids = implode( ",", array_fill( 0, count( $user_favs ), '%d' ) );
				}

				$where .= $wpdb->prepare( " AND $wpdb->posts.ID IN ($prepare_ids)", $fav_in );

				// Replace 'post' with GD post types
				if ( ! isset( $wp_query->query['post_type'] ) ) {
					$prepare_cpts = implode( ",", array_fill( 0, count( $post_types ), '%s' ) );
					$gd_cpt_replace = $wpdb->prepare( "$wpdb->posts.post_type IN ($prepare_cpts)", $post_types );
					$where = str_replace( "$wpdb->posts.post_type = 'post'", $gd_cpt_replace, $where );
				}

				$user_id = get_current_user_id();
				$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;

				// Check if restricted
				$post_type = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : $post_types[0];
				$author_favorites_private = isset( $cpts[$post_type]['author_favorites_private'] ) && $cpts[$post_type]['author_favorites_private'] ? true : false;

				if ( $author_favorites_private && $author_id != $user_id ) {
					$where .= " AND 1=2";
				}

			}
		} elseif ( is_author() ) {
			$post_type = isset( $wp_query->query['post_type'] ) ? $wp_query->query['post_type'] : $post_types[0];
			$user_id = get_current_user_id();
			$author_id = isset( $wp_query->query_vars['author'] ) ? $wp_query->query_vars['author'] : 0;

			if ( $author_id && $author_id == $user_id ) {
				$statuses = geodir_get_post_stati( 'author-archive', array( 'post_type' => $post_type ) );

				$_statuses = "{$wpdb->posts}.post_status = 'publish'";

				foreach ( $statuses as $status ) {
					if ( strpos( $where, "{$wpdb->posts}.post_status = '" . $status . "'" ) === false ) {
						$_statuses .= $wpdb->prepare(" OR {$wpdb->posts}.post_status = %s",$status);
					}
				}

				$where = str_replace( "{$wpdb->posts}.post_status = 'publish'", $_statuses, $where );
			}

			// check if restricted
			$author_posts_private = isset( $cpts[ $post_type ]['author_posts_private'] ) && $cpts[$post_type]['author_posts_private'] ? true : false;

			if ( $author_posts_private && $author_id != $user_id ) {
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
	public function posts_orderby( $orderby, $query = array() ) {
		global $wpdb, $geodirectory, $geodir_post_type, $s;

		if ( self::is_gd_main_query( $query ) ) {
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
				if ( $support_location && ( $latlon = $geodirectory->location->get_latlon() ) ) {
					$sort_by = 'distance_asc';
				} elseif ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' ) {
					$sort_by = 'search_best';
				} else {
					$default_sort = geodir_get_posts_default_sort( $geodir_post_type );

					if ( ! empty( $default_sort ) ) {
						$sort_by = $default_sort;
					}
				}
			}

			$table = geodir_db_cpt_table( $geodir_post_type );

			$orderby = self::sort_by_sql( $sort_by, $geodir_post_type, $query );

			$orderby = self::sort_by_children( $orderby, $sort_by, $geodir_post_type, $query );

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
		}

		return $orderby;
	}

	public static function sort_by_children( $orderby, $sort_by, $post_type, $wp_query = array(), $parent = 0 ) {
		global $wpdb;

		if ( substr( strtolower( $sort_by ) , -5 ) == '_desc' ) {
			$order = 'desc';
			$htmlvar_name = substr( $sort_by , 0, strlen( $sort_by ) - 5 );
		} else if ( substr( strtolower( $sort_by ) , -4 ) == '_asc' ) {
			$order = 'asc';
			$htmlvar_name = substr( $sort_by , 0, strlen( $sort_by ) - 4 );
		} else {
			$htmlvar_name = '';
		}

		if ( ! empty( $orderby ) && $htmlvar_name ) {
			$parent_id = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE htmlvar_name = %s AND sort = %s AND post_type = %s AND tab_parent = %d", $htmlvar_name, $order, $post_type, $parent ) );

			if ( $parent_id ) {
				$children = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " WHERE post_type = %s AND tab_parent = %d ORDER BY sort_order ASC", $post_type, $parent_id ) );

				if ( $children ) {
					$orderby_parts = array();

					foreach ( $children as $child ) {
						if ( $child->field_type == 'random' ) {
							$child_sort_by = 'random';
						} else {
							$child_sort_by = $child->htmlvar_name . "_" . $child->sort;
						}
						$child_sort = self::sort_by_sql( $child_sort_by, $post_type, $wp_query );

						if ( ! empty( $child_sort ) ) {
							$orderby_parts[] = $child_sort;
						}
					}

					if ( ! empty( $orderby_parts ) ) {
						if ( ! empty( $orderby ) ) {
							$orderby .= ", ";
						}

						$orderby .= implode( ", ", array_filter( $orderby_parts ) );
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
     * @global object $wp_query WordPress query object.
     *
     * @global object $wpdb WordPress Database object.
     *
     * @return string
     */
	public static function sort_by_sql( $sort_by = 'post_title_asc', $post_type = "gd_place", $wp_query = array() ) {
		global $wpdb;

		$orderby = '';
		$table = geodir_db_cpt_table( $post_type );
		$order_by_parts = array();

		switch ( $sort_by ) {
			case 'distance':
			case 'distance_asc':
				$order_by_parts[] = "distance ASC";
				$order_by_parts[] = self::search_sort( '', $sort_by, $wp_query );
				break;
			case 'distance_desc':
				$order_by_parts[] = "distance DESC";
				$order_by_parts[] = self::search_sort( '', $sort_by, $wp_query );
				break;
			case 'search_best':
				$order_by_parts[] = self::search_sort( '', $sort_by, $wp_query );
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
				$use_bayesian = apply_filters( 'geodir_use_bayesian', true, $table );

				if ( $use_bayesian ) {
					$statuses = geodir_get_post_stati( 'public', array( 'post_type' => $post_type ) );

					if ( count( $statuses ) > 1 ) {
						$post_status_where = "WHERE post_status IN( '" . implode( "', '", $statuses ) . "' )";
					} else {
						$post_status_where = "WHERE post_status = '{$statuses[0]}'";
					}

					$avg_num_votes = get_transient( 'gd_avg_num_votes_' . $table );

					if ( $avg_num_votes === false ) {
						$avg_num_votes = (int) $wpdb->get_var( "SELECT SUM( rating_count ) FROM {$table} {$post_status_where}" );
						$avg_rating = false;

						// Save transient for rating count.
						set_transient( 'gd_avg_num_votes_' . $table, $avg_num_votes, 12 * HOUR_IN_SECONDS );
					} else {
						$avg_num_votes = (int) $avg_num_votes;
						$avg_rating = get_transient( 'gd_avg_rating_' . $table );
					}

					if ( $avg_rating === false ) {
						if ( $avg_num_votes > 0 ) {
							$avg_rating = $wpdb->get_var( "SELECT SUM( overall_rating ) FROM {$table} {$post_status_where}" ) / $avg_num_votes;
						} else {
							$avg_rating = 0;
						}

						// Save transient for average rating.
						set_transient( 'gd_avg_rating_' . $table, $avg_rating , 12 * HOUR_IN_SECONDS );
					} else {
						$avg_rating = geodir_sanitize_float( $avg_rating );
					}

					$order_by_parts[] = " ( ( ( $avg_num_votes * $avg_rating ) + ( " . $table . ".rating_count * " . $table . ".overall_rating ) )  / ( $avg_num_votes + " . $table . ".rating_count ) ) $rating_order, " . $table . ".overall_rating $rating_order";
				}else{
					$order_by_parts[] = $table . ".overall_rating $rating_order";
					$order_by_parts[] = $table . ".rating_count $rating_order";
				}
				break;
			default:
				$default_sort = geodir_get_posts_default_sort( $post_type );

				if ( $default_sort == '' && $sort_by == $default_sort ) {
					$order_by_parts[] = "{$wpdb->posts}.post_date desc";
				 }else {
					$order_by_parts[] = self::custom_sort( $orderby, $sort_by, $table, $post_type, $wp_query );
				}
				break;
		}

		if ( ! empty( $order_by_parts ) ) {
			$orderby = implode( ", ", array_filter( $order_by_parts ) );
		}

		return $orderby;
	}

	public static function search_sort( $orderby = '', $sort_by = '', $wp_query = array() ) {
		global $s, $gd_exact_search;

		if ( is_search() && isset( $_REQUEST['geodir_search'] ) && $s && trim( $s ) != '' && ( ! empty( $wp_query ) && self::is_gd_main_query( $wp_query ) ) ) {
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
	 * @param string $post_type Post type.
	 * @param object $wp_query WP_Query object.
	 * @return string Modified orderby query.
	 */
	public static function  custom_sort( $orderby, $sort_by, $table, $post_type = '', $wp_query = array() ) {
		global $wpdb;

		if ( $sort_by != '' && ( ! is_search() || ( isset( $_REQUEST['s'] ) && isset( $_REQUEST['snear'] ) && $_REQUEST['snear'] == '' && ( $_REQUEST['s'] == '' ||  $_REQUEST['s'] == ' ') ) ) ) {
			if ( substr( strtolower( $sort_by ) , -5 ) == '_desc' ) {
				$order = 'desc';
				$sort_key = substr( $sort_by , 0, strlen( $sort_by ) - 5 );
			} else if ( substr( strtolower( $sort_by ) , -4 ) == '_asc' ) {
				$order = 'asc';
				$sort_key = substr( $sort_by , 0, strlen( $sort_by ) - 4 );
			} else {
				$sort_key = '';
			}

			if ( $sort_key ) {
				$sort_by = $sort_key;

				switch ( $sort_by ) {
					case 'post_date':
					case 'comment_count':
						$orderby = "{$wpdb->posts}." . $sort_by . " " . $order . ", ".$table . ".overall_rating " . $order;
						break;
					case 'post_images':
						$orderby = $table . ".featured_image " . $order;
						break;
					case 'distance':
						$orderby = $sort_by . " " . $order;
						break;
					case 'overall_rating':
						$use_bayesian = apply_filters( 'geodir_use_bayesian', true, $table );
						if ( $use_bayesian ) {
							$statuses = geodir_get_post_stati( 'public', array( 'post_type' => $post_type ) );

							if ( count( $statuses ) > 1 ) {
								$post_status_where = "WHERE post_status IN( '" . implode( "', '", $statuses ) . "' )";
							} else {
								$post_status_where = "WHERE post_status = '{$statuses[0]}'";
							}

							$avg_num_votes = get_transient( 'gd_avg_num_votes_' . $table );

							if ( $avg_num_votes === false ) {
								$avg_num_votes = (int) $wpdb->get_var( "SELECT SUM( rating_count ) FROM {$table} {$post_status_where}" );
								$avg_rating = false;

								// Save transient for rating count.
								set_transient( 'gd_avg_num_votes_' . $table, $avg_num_votes, 12 * HOUR_IN_SECONDS );
							} else {
								$avg_num_votes = (int) $avg_num_votes;
								$avg_rating = get_transient( 'gd_avg_rating_' . $table );
							}

							if ( $avg_rating === false ) {
								if ( $avg_num_votes > 0 ) {
									$avg_rating = $wpdb->get_var( "SELECT SUM( overall_rating ) FROM {$table} {$post_status_where}" ) / $avg_num_votes;
								} else {
									$avg_rating = 0;
								}

								// Save transient for average rating.
								set_transient( 'gd_avg_rating_' . $table, $avg_rating , 12 * HOUR_IN_SECONDS );
							} else {
								$avg_rating = geodir_sanitize_float( $avg_rating );
							}

							$orderby = " ( ( ( $avg_num_votes * $avg_rating ) + ( " . $table . ".rating_count * " . $table . ".overall_rating ) )  / ( $avg_num_votes + " . $table . ".rating_count ) ) $order, " . $table . ".overall_rating $order";
						} else {
							$orderby = " " . $table . "." . $sort_by . "  " . $order . ", " . $table . ".rating_count " . $order;
						}
						break;
					default:
						/**
						 * Filters custom key sort.
						 *
						 * @since 2.0.0.74
						 *
						 * @param string $_orderby Custom key default orderby query string. Default NULL.
						 * @param string $sort_by Sortby query string.
						 * @param string $order Sortby order.
						 * @param string $orderby The orderby query string.
						 * @param string $table Listing table name.
						 * @param string $post_type Post type.
						 * @param object $wp_query WP_Query object.
						 */
						$orderby = apply_filters( 'geodir_custom_key_orderby', '', $sort_by, $order, $orderby, $table, $post_type, $wp_query );

						if ( empty( $orderby ) ) {
							if ( self::column_exist( $table, $sort_by ) ) {
								$orderby = $table . "." . $sort_by . " " . $order;
							} else {
								$orderby = "{$wpdb->posts}.post_date desc";
							}
						}
						break;
				}
			}
		}

		/**
		 * Filters custom orderby.
		 *
		 * @since 2.0.0.74
		 *
		 * @param string $orderby The orderby query string.
		 * @param string $sort_by Sortby query string.
		 * @param string $table Listing table name.
		 * @param string $post_type Post type.
		 * @param object $wp_query WP_Query object.
		 */
		return apply_filters( 'geodir_orderby_custom_sort', $orderby, $sort_by, $table, $post_type, $wp_query );
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
	public static function column_exist( $db, $column ) {
		global $wpdb;

		$exists = false;
		$columns = $wpdb->get_col( "SHOW COLUMNS FROM {$db}" );

		foreach ( $columns as $c ) {
			if ( $c == $column ) {
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
			// Post attachment
			if ( ! empty( $wp->query_vars['attachment'] ) && ! empty( $wp->query_vars['post_type'] ) && geodir_is_gd_post_type( $wp->query_vars['post_type'] ) ) {
				if ( isset( $wp->query_vars[ $wp->query_vars['post_type'] ] ) ) {
					unset( $wp->query_vars[ $wp->query_vars['post_type'] ] );
				}
				unset( $wp->query_vars[ 'post_type' ] );
				if ( isset( $wp->query_vars[ 'name' ] ) ) {
					unset( $wp->query_vars[ 'name' ] );
				}
				return;
			}

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
					geodir_is_page_id( $wp->query_vars['page_id'], 'add' )
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
						geodir_is_page_id( $page->ID, 'add' )
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

					// Set embed
					if ( empty( $wp->query_vars[ 'embed' ] ) && ! empty( $wp->query_vars[ $requested_post_type ] ) && ! empty( $wp->request ) && strpos( $wp->request, '/' . $wp->query_vars[ $requested_post_type ] . '/embed' ) > 0 ) {
						$wp->query_vars[ 'embed' ] = true;
					}
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

		// Set add listing query parameters.
		if ( ! empty( $wp ) && ! empty( $wp->query_vars['pagename'] ) && ! empty( $wp->query_vars['listing_type'] ) ) {
			if ( geodir_is_gd_post_type( $wp->query_vars['listing_type'] ) ) {
				$_REQUEST['listing_type'] = geodir_clean( $wp->query_vars['listing_type'] );

				if ( ! empty( $wp->query_vars['pid'] ) ) {
					$_REQUEST['pid'] = absint( $wp->query_vars['pid'] );
				}
			}
		}

		if ( isset( $_REQUEST['fl_builder'] ) ) {
			return; // Fix for BB not working on search page
		}

		self::set_is_geodir_page( $wp );

		// Map query vars to their keys, or get them if endpoints are not supported
		foreach ( $this->get_query_vars() as $key => $var ) {
			if ( isset( $_GET[ $var ] ) ) {
				$wp->query_vars[ $key ] = $_GET[ $var ];
			} else if ( isset( $wp->query_vars[ $var ] ) ) {
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
	 * Get any errors from query string.
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
			'gd_is_geodir_page' => 'gd_is_geodir_page',
			'listing_type' => 'listing_type',
			'pid' => 'pid'
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

	/**
	 * Filters whether to short-circuit default header status to fix 404 status
	 * header when no results found on GD search page.
	 *
	 * Returning a non-false value from the filter will short-circuit the handling
	 * and return early.
	 *
	 * @since 2.0.0.90
	 *
	 * @param bool     $preempt  Whether to short-circuit default header status handling. Default false.
	 * @param WP_Query $wp_query WordPress Query object.
	 * @return bool
	 */
	public static function pre_handle_404( $preempt, $wp_query ) {
		if ( ! is_admin() && ! empty( $wp_query ) && is_object( $wp_query ) && $wp_query->is_main_query() && geodir_is_page( 'search' ) ) {
			// Don't 404 for search queries.
			status_header( 200 );
			return;
		}

		return $preempt;
	}

	/**
	 * Retrieve the variable from query or request.
	 *
	 * @since 2.0.0.96
	 *
	 * @global object $wp WordPress object.
	 *
	 * @param string $var       The variable key to retrieve.
	 * @param mixed  $default   Optional. Value to return if the query variable is not set. Default empty.
	 * @return mixed Contents of the query variable.
	 */
	public static function get_query_var( $var, $default = '' ) {
		global $wp;

		if ( ! empty( $wp ) && ! empty( $wp->query_vars ) && isset( $wp->query_vars[ $var ] ) ) {
			$value = $wp->query_vars[ $var ];
		} elseif ( isset( $_REQUEST[ $var ] ) ) {
			$value = geodir_clean( $_REQUEST[ $var ] );
		} else {
			$value = $default;
		}

		return $value;
	}

	/**
	 * Prevent split query to work sorting.
	 *
	 * Conflicts with Object Cache Pro.
	 *
	 * @since 2.3.24
	 *
	 * @global object $wp WordPress object.
	 *
	 * @param bool     $split_the_query Whether or not to split the query.
     * @param WP_Query $query The WP_Query instance.
	 * @return bool True to spilt query otherwise false.
	 */
	public function split_the_query( $split_the_query, $wp_query ) {
		if ( $split_the_query && ! empty( $wp_query->request ) && strpos( $wp_query->request, 'geodir_gd_' ) !== false && strpos( $wp_query->request, '`_search_title`' ) !== false ) {
			$split_the_query = false;
		}

		return $split_the_query;
	}

	/**
	 * Set global for main WP_Query.
	 *
	 * @since 2.3.68
	 *
	 * @global object $wp_the_query WP_Query object.
	 * @global object $gd_wp_the_query WP_Query object.
	 */
	public function set_wp_the_query( $the_wp ) {
		global $wp_the_query, $gd_wp_the_query;

		if ( ! empty( $wp_the_query ) && isset( $wp_the_query->posts ) && $wp_the_query->is_main_query() && self::is_gd_main_query( $wp_the_query ) ) {
			if ( empty( $wp_the_query->posts ) || ( ! empty( $wp_the_query->posts[0]->post_type ) && geodir_is_gd_post_type( $wp_the_query->posts[0]->post_type ) ) ) {
				$gd_wp_the_query = $wp_the_query;
				$gd_wp_the_query->the_posts = $wp_the_query->posts;
			}
		}
	}

	/**
	 * Set GD posts main query post where clause.
	 *
	 * @since 2.3.73
	 *
	 * @global object $wpdb WordPress database object.
	 *
	 * @param string $where Query posts where clause.
	 * @param object $query WP_Query object.
	 * @param string $geodir_post_type Current post type.
	 * @return string Query posts where clause.
	 */
	public function main_query_posts_where( $where, $query, $geodir_post_type ) {
		global $wpdb;

		// A-Z Search value.
		$value = geodir_az_search_value();

		if ( $value != '' ) {
			$where .= $wpdb->prepare(" AND `{$wpdb->posts}`.`post_title` LIKE %s ", $wpdb->esc_like( $value ) . '%' );
		}

		return $where;
	}

	/**
	 * Set GD posts main query post orderby clause.
	 *
	 * @since 2.3.73
	 *
	 * @global object $wpdb WordPress database object.
	 *
	 * @param string $orderby Query posts orderby clause.
	 * @param string $sort_by Current sort by parameter.
	 * @param string $table Details database table.
	 * @param object $query WP_Query object.
	 * @return string Query posts orderby clause.
	 */
	public function posts_order_by_sort( $orderby, $sort_by, $table, $query ) {
		global $wpdb;

		$value = geodir_az_search_value();

		if ( $value != '' ) {
			$_orderby = "`{$wpdb->posts}`.`post_title` ASC";

			if ( trim( $orderby ) != "" ) {
				$_orderby .= "," . $orderby;
			}

			$orderby = $_orderby;
		}

		return $orderby;
	}
}

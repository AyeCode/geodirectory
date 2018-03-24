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

	public function set_globals(){
		global $geodir_post_type;

		$geodir_post_type = geodir_get_current_posttype();
	}




	/**
	 * Hook into pre_get_posts to do the main product query.
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

			add_filter( 'posts_join', array( $this, 'posts_join' ) );
			add_filter( 'posts_where', array( $this, 'posts_where' ) );
			add_filter( 'posts_where', array( $this, 'author_where' ) );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );

		}elseif(geodir_is_page('search')){
			$q->is_page = false;
			$q->is_singular = false;
			$q->is_search = true;
			$q->is_archive = true;
			//$q->is_post_type_archive = true;
			$q->is_paged = true;
			$q->in_the_loop = true;


			//$q->set('is_page',false);
			//$q->set('is_search',true);
			//$q->set('post_type','gd_place');
			add_filter( 'posts_join', array( $this, 'posts_join' ) );
			add_filter( 'posts_fields', array( $this, 'posts_fields' ) );
			add_filter( 'posts_where', array( $this, 'posts_where' ) );
			//add_filter( 'posts_limits', array( $this, 'posts_limits' ),10,2 );
			add_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );

			// setup search globals
			global $wp_query, $wpdb, $geodir_post_type, $table, $dist, $mylat, $mylon, $s, $snear, $s, $s_A, $s_SA;

			if (isset($_REQUEST['scat']) && $_REQUEST['scat'] == 'all') $_REQUEST['scat'] = '';
			//if(isset($_REQUEST['s']) && $_REQUEST['s'] == '+') $_REQUEST['s'] = '';

			if (isset($_REQUEST['sdist'])) {
				($_REQUEST['sdist'] != '0' && $_REQUEST['sdist'] != '') ? $dist = esc_attr($_REQUEST['sdist']) : $dist = 25000;
			} elseif (geodir_get_option('search_radius') != '') {
				$dist = geodir_get_option('search_radius');//search_radius

			} else {
				$dist = 25000;
			} //  Distance

			if (isset($_REQUEST['sgeo_lat'])) {
				$mylat = (float)esc_attr($_REQUEST['sgeo_lat']);
			} else {
				$mylat = (float)geodir_get_current_city_lat();
			} //  Latitude

			if (isset($_REQUEST['sgeo_lon'])) {
				$mylon = (float)esc_attr($_REQUEST['sgeo_lon']);
			} else {
				$mylon = (float)geodir_get_current_city_lng();
			} //  Distance

			if (isset($_REQUEST['snear'])) {
				$snear = trim(esc_attr($_REQUEST['snear']));
			}

			if (isset($_REQUEST['s'])) {
				$s = trim(esc_attr(wp_strip_all_tags(get_search_query())));
			}

			if ($snear == 'NEAR ME') {
				$ip = $_SERVER['REMOTE_ADDR'];
				$addr_details = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip=' . $ip));
				$mylat = stripslashes(geodir_utf8_ucfirst($addr_details[geoplugin_latitude]));
				$mylon = stripslashes(geodir_utf8_ucfirst($addr_details[geoplugin_longitude]));
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
	 * Filter the posts fields string
	 *
	 * @param $fields
	 *
	 * @return string
	 */
	public function posts_fields($fields){
		global $wpdb, $table_prefix, $geodir_post_type;
		global $wp_query, $wpdb, $geodir_post_type, $table, $plugin_prefix, $dist, $mylat, $mylon, $snear, $gd_session;


		$table = geodir_db_cpt_table($geodir_post_type);

		$fields .= ", " . $table . ".* ";


		if ($snear != '' || $gd_session->get('all_near_me')) {
			$DistanceRadius = geodir_getDistanceRadius(geodir_get_option('search_distance_long'));

			if ($gd_session->get('all_near_me')) {
				$mylat = $gd_session->get('user_lat');
				$mylon = $gd_session->get('user_lon');
			}

			$fields .= " , (" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(" . $table . ".latitude)) * pi()/180 / 2), 2) +COS(ABS($mylat) * pi()/180) * COS( ABS(" . $table . ".latitude) * pi()/180) *POWER(SIN(($mylon - " . $table . ".longitude) * pi()/180 / 2), 2) )))as distance ";
		}


		global $s;// = get_search_query();
		if (geodir_is_page('search') && $s && trim($s) != '') {
			$keywords = explode(" ", $s);

			if(is_array($keywords) && $klimit = geodir_get_option('geodir_search_word_limit')){
				foreach($keywords as $kkey=>$kword){
					if(geodir_utf8_strlen($kword)<=$klimit){
						unset($keywords[$kkey]);
					}
				}
			}


			if (count($keywords) > 1) {
				$parts = array(
					'AND' => 'gd_alltitlematch_part',
					'OR' => 'gd_titlematch_part'
				);
				$gd_titlematch_part = "";
				foreach ($parts as $key => $part) {
					$gd_titlematch_part .= " CASE WHEN ";
					$count = 0;
					foreach ($keywords as $keyword) {
						$keyword = trim($keyword);
						$keyword  = wp_specialchars_decode($keyword ,ENT_QUOTES);
						$count++;
						if ($count < count($keywords)) {
							// $gd_titlematch_part .= $wpdb->posts . ".post_title LIKE '%%" . $keyword . "%%' " . $key . " ";
							$gd_titlematch_part .= "( " . $wpdb->posts . ".post_title LIKE '" . $keyword . "' OR " . $wpdb->posts . ".post_title LIKE '" . $keyword . "%%' OR " . $wpdb->posts . ".post_title LIKE '%% " . $keyword . "%%' ) " . $key . " ";
						} else {
							//$gd_titlematch_part .= $wpdb->posts . ".post_title LIKE '%%" . $keyword . "%%' ";
							$gd_titlematch_part .= "( " . $wpdb->posts . ".post_title LIKE '" . $keyword . "' OR " . $wpdb->posts . ".post_title LIKE '" . $keyword . "%%' OR " . $wpdb->posts . ".post_title LIKE '%% " . $keyword . "%%' ) ";
						}
					}
					$gd_titlematch_part .= "THEN 1 ELSE 0 END AS " . $part . ",";
				}
			} else {
				$gd_titlematch_part = "";
			}
			$s = stripslashes_deep( $s );
			$s = wp_specialchars_decode($s,ENT_QUOTES);
			$fields .= $wpdb->prepare(", CASE WHEN " . $table . ".featured='1' THEN 1 ELSE 0 END AS gd_featured, CASE WHEN " . $wpdb->posts . ".post_title LIKE %s THEN 1 ELSE 0 END AS gd_exacttitle," . $gd_titlematch_part . " CASE WHEN ( " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s OR " . $wpdb->posts . ".post_title LIKE %s ) THEN 1 ELSE 0 END AS gd_titlematch, CASE WHEN ( " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s OR " . $wpdb->posts . ".post_content LIKE %s ) THEN 1 ELSE 0 END AS gd_content", array($s, $s, $s . '%', '% ' . $s . '%', $s, $s . ' %', '% ' . $s . ' %', '% ' . $s));
		}

		//echo '###fileds:'.$fields;

		return $fields;
	}

	public function posts_limits($limits,$query){
		echo '###limit###'.$limits;

		$limits = " LIMIT 0,10 ";

		return $limits;
	}

	/**
	 * @param $where
	 *
	 * @return mixed
	 */
	public function posts_where($where){

		global $wpdb,$geodir_post_type,$wp_query;
		//echo '###'.$where;

		$table = geodir_db_cpt_table($geodir_post_type);

		$where .= $wpdb->prepare(" AND $wpdb->posts.post_type = %s AND $wpdb->posts.post_status = 'publish' ",$geodir_post_type);

		if(geodir_is_page('search')){
			global $wpdb, $geodir_post_type, $plugin_prefix, $dist, $mylat, $mylon, $snear, $s, $s_A, $s_SA, $search_term, $gd_session;


			$search_term = 'OR';
			$search_term = 'AND';
			$geodir_custom_search = '';
			$category_search_range = '';

			if (is_single() && get_query_var('post_type')) {
				return $where;
			}

			if (is_tax()) {
				return $where;
			}

			$s = trim($s);
			$s  = wp_specialchars_decode($s ,ENT_QUOTES);
			$s_A = wp_specialchars_decode($s_A ,ENT_QUOTES);

			$where = '';
			$better_search_terms = '';
			if (isset($_REQUEST['stype']))
				$post_types = esc_attr(wp_strip_all_tags($_REQUEST['stype']));
			else
				$post_types = 'gd_place';

			if ($s != '') {
				$keywords = explode(" ", $s);
				if(is_array($keywords) && $klimit = geodir_get_option('geodir_search_word_limit')){
					foreach($keywords as $kkey=>$kword){
						if(geodir_utf8_strlen($kword)<=$klimit){
							unset($keywords[$kkey]);
						}
					}
				}

				if (!empty($keywords)) {
					foreach ($keywords as $keyword) {
						$keyword = trim($keyword);
						$keyword  = wp_specialchars_decode($keyword ,ENT_QUOTES);
						if ($keyword != '') {
							/**
							 * Filter the search query keywords SQL.
							 *
							 * @since 1.5.9
							 * @package GeoDirectory
							 * @param string $better_search_terms The query values, default: `' OR ( ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '" OR ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '%" OR ' . $wpdb->posts . '.post_title LIKE "% ' . $keyword . '%" )'`.
							 * @param array $keywords The array of keywords for the query.
							 * @param string $keyword The single keyword being searched.
							 */
							$better_search_terms .= apply_filters("geodir_search_better_search_terms",' OR ( ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '" OR ' . $wpdb->posts . '.post_title LIKE "' . $keyword . '%" OR ' . $wpdb->posts . '.post_title LIKE "% ' . $keyword . '%" )',$keywords,$keyword);
						}
					}
				}
			}

			/* get taxonomy */
			$taxonomies = geodir_get_taxonomies($post_types, true);
			if($taxonomies) {
				$taxonomies = implode("','", $taxonomies);
				$taxonomies = "'" . $taxonomies . "'";
			}else{$taxonomies='';}

			$content_where = $terms_where = '';
			if ($s != '') {
				/**
				 * Filter the search query content where values.
				 *
				 * @since 1.5.0
				 * @package GeoDirectory
				 * @param string $content_where The query values, default: `" OR ($wpdb->posts.post_content LIKE \"$s\" OR $wpdb->posts.post_content LIKE \"$s%\" OR $wpdb->posts.post_content LIKE \"% $s%\" OR $wpdb->posts.post_content LIKE \"%>$s%\" OR $wpdb->posts.post_content LIKE \"%\n$s%\") ") "`.
				 */
				$content_where = apply_filters("geodir_search_content_where"," OR ($wpdb->posts.post_content LIKE \"$s\" OR $wpdb->posts.post_content LIKE \"$s%\" OR $wpdb->posts.post_content LIKE \"% $s%\" OR $wpdb->posts.post_content LIKE \"%>$s%\" OR $wpdb->posts.post_content LIKE \"%\n$s%\") ");
				/**
				 * Filter the search query term values.
				 *
				 * @since 1.5.0
				 * @package GeoDirectory
				 * @param string $terms_where The separator, default: `" AND ($wpdb->terms.name LIKE \"$s\" OR $wpdb->terms.name LIKE \"$s%\" OR $wpdb->terms.name LIKE \"% $s%\" OR $wpdb->terms.name IN ($s_A)) "`.
				 */
				$terms_where = apply_filters("geodir_search_terms_where"," AND ($wpdb->terms.name LIKE \"$s\" OR $wpdb->terms.name LIKE \"$s%\" OR $wpdb->terms.name LIKE \"% $s%\" OR $wpdb->terms.name IN ($s_A)) ");
			}

			// get term sql
			$term_sql = "SELECT $wpdb->term_taxonomy.term_id 
                    FROM $wpdb->term_taxonomy,  $wpdb->terms, $wpdb->term_relationships 
                    WHERE $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id 
                    AND $wpdb->term_relationships.term_taxonomy_id =  $wpdb->term_taxonomy.term_taxonomy_id 
                    AND $wpdb->term_taxonomy.taxonomy in ( {$taxonomies} ) 
                    $terms_where 
                    GROUP BY $wpdb->term_taxonomy.term_id";

			$term_results = $wpdb->get_results( $term_sql );
			$term_ids = array();
			$terms_sql = '';

			if ( !empty( $term_results ) ) {
				foreach ( $term_results as $term_id ) {
					$term_ids[] = $term_id;
				}
				if ( !empty ( $term_ids ) ) {

					foreach ( $term_ids as $term ) {
						$terms_sql .= " OR FIND_IN_SET( $term->term_id , ".$table.".post_category ) ";
					}
				}
			}

			if ($snear != '') {
				if (is_numeric($gd_session->get('near_me_range')) && !isset($_REQUEST['sdist'])) {
					$dist = $gd_session->get('near_me_range');
				}
				$lon1 = $mylon - $dist / abs(cos(deg2rad($mylat)) * 69);
				$lon2 = $mylon + $dist / abs(cos(deg2rad($mylat)) * 69);
				$lat1 = $mylat - ($dist / 69);
				$lat2 = $mylat + ($dist / 69);

				$rlon1 = is_numeric(min($lon1, $lon2)) ? min($lon1, $lon2) : '';
				$rlon2 = is_numeric(max($lon1, $lon2)) ? max($lon1, $lon2) : '';
				$rlat1 = is_numeric(min($lat1, $lat2)) ? min($lat1, $lat2) : '';
				$rlat2 = is_numeric(max($lat1, $lat2)) ? max($lat1, $lat2) : '';



				$where .= " AND ( ( $wpdb->posts.post_title LIKE \"$s\" $better_search_terms)
			                    $content_where 
								$terms_sql
							)
						AND $wpdb->posts.post_type in ('{$post_types}')
						AND ($wpdb->posts.post_status = 'publish')
						AND ( " . $table . ".latitude between $rlat1 and $rlat2 )
						AND ( " . $table . ".longitude between $rlon1 and $rlon2 ) ";

				if (isset($_REQUEST['sdist']) && $_REQUEST['sdist'] != 'all') {
					$DistanceRadius = geodir_getDistanceRadius(geodir_get_option('search_distance_long'));
					$where .= " AND CONVERT((" . $DistanceRadius . " * 2 * ASIN(SQRT( POWER(SIN((ABS($mylat) - ABS(" . $table . ".latitude)) * pi()/180 / 2), 2) +COS(ABS($mylat) * pi()/180) * COS( ABS(" . $table . ".latitude) * pi()/180) *POWER(SIN(($mylon - " . $table . ".longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= " . $dist;
				}

			} else {
				$where .= " AND ( 
						($wpdb->posts.post_title LIKE \"$s\" $better_search_terms)
                        $content_where  
                        $terms_sql 
                    )
                    AND $wpdb->posts.post_type in ('$post_types')
                    AND ($wpdb->posts.post_status = 'publish') ";
			}

			########### WPML ###########
			if ( geodir_wpml_is_post_type_translated( $post_types ) ) {
				$lang_code = ICL_LANGUAGE_CODE;

				if ($lang_code && $post_types) {
					$where .= " AND icl_t.language_code = '".$lang_code."' AND icl_t.element_type IN('post_" . $post_types . "') ";
				}
			}
			########### WPML ###########


		}

		return $where;
	}

	public function author_where($where){
		global $wp_query,$wpdb;
//echo '####';exit;
		// author saves/favs filter
		if(is_author() && !empty($wp_query->query['gd_favs']) ){

			$author_id = isset($wp_query->query_vars['author']) ? $wp_query->query_vars['author'] : 0;
			if($author_id){
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

			}
		}

		return $where;
	}

	/**
	 * @param $join
	 *
	 * @return string
	 */
	public function posts_join($join){

		global $wpdb, $table_prefix, $geodir_post_type;

		########### WPML ###########
		if ( geodir_wpml_is_post_type_translated( $geodir_post_type ) ) {
			global $sitepress;
			$lang_code = ICL_LANGUAGE_CODE;
			if ($lang_code) {
				$join .= "JOIN " . $table_prefix . "icl_translations icl_t ON icl_t.element_id = " . $table_prefix . "posts.ID";
			}
		}
		########### WPML ###########

		$table = geodir_db_cpt_table($geodir_post_type);

		$join .= " INNER JOIN " . $table . " ON (" . $table . ".post_id = $wpdb->posts.ID)  "; // @todo inner join seems faster but we should so tests with large datasets

		return $join;
	}

	/**
	 * @param $orderby
	 *
	 * @return mixed
	 */
	public function posts_orderby($orderby){
		global $wpdb, $table_prefix, $geodir_post_type,$snear;

		$sort_by = '';
		$orderby = ' ';

		if ($snear != '') {
			$orderby .= " distance,";
		}

		if ( get_query_var( 'order_by' ) ) {
			$sort_by = get_query_var( 'order_by' );
		}

		if ( isset( $_REQUEST['sort_by'] ) && $_REQUEST['sort_by'] != '' && is_main_query() ) {
			$sort_by = esc_attr( $_REQUEST['sort_by'] );
		}

		if ( $sort_by == '' ) {
			$default_sort = geodir_get_posts_default_sort( $geodir_post_type );
			if ( !empty( $default_sort ) ) {
				$sort_by = $default_sort;
			}
		}

		$table = geodir_db_cpt_table($geodir_post_type);


		switch ($sort_by):
			case 'newest':
				$orderby = "$wpdb->posts.post_date desc, ";
				break;
			case 'oldest':
				$orderby = "$wpdb->posts.post_date asc, ";
				break;
			case 'low_review':
			case 'rating_count_asc':
				$orderby = $table . ".rating_count ASC, " . $table . ".overall_rating ASC, ";
				break;
			case 'high_review':
			case 'rating_count_desc':
				$orderby = $table . ".rating_count DESC, " . $table . ".overall_rating DESC, ";
				break;
			case 'low_rating':
				$orderby = "( " . $table . ".overall_rating  ) ASC, " . $table . ".rating_count ASC,  ";
				break;
			case 'high_rating':
				$orderby = " " . $table . ".overall_rating DESC, " . $table . ".rating_count DESC, ";
				break;
			case 'featured':
				$orderby = $table . ".featured asc, ";
				break;
			case 'nearest':
				$orderby = " distance asc, ";
				break;
			case 'farthest':
				$orderby = " distance desc, ";
				break;
			case 'random':
				$orderby = " rand() ";
				break;
			case 'az':
				$orderby = "$wpdb->posts.post_title asc, ";
				break;
			// sort by rating
			case 'overall_rating_desc':
				$orderby = " " . $table . ".overall_rating DESC, " . $table . ".rating_count DESC, ";
				break;
			case 'overall_rating_asc':
				$orderby = " " . $table . ".overall_rating ASC, " . $table . ".rating_count ASC, ";
				break;
			default:
				$orderby .= " $wpdb->posts.post_date desc";
				break;
		endswitch;

		/**
		 * Filter order by SQL.
		 *
		 * @since 1.0.0
		 * @param string $orderby The orderby query string.
		 * @param string $sort_by Sortby query string.
		 * @param string $table Listing table name.
		 */
		$orderby = apply_filters('geodir_posts_order_by_sort', $orderby, $sort_by, $table);

		if($sort_by != 'random'){
			//$orderby .= $table . ".featured asc, $wpdb->posts.post_date desc, $wpdb->posts.post_title ";
		}

		
		

		//echo '###default###'.$default_sort.'###';

	//echo '###'.$orderby;

		return $orderby;
	}

	/**
	 * Remove the query.
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
					//|| $wp->query_vars['page_id'] == geodir_search_page_id()
					|| $wp->query_vars['page_id'] == geodir_info_page_id()
					|| $wp->query_vars['page_id'] == geodir_login_page_id()
					|| ( function_exists( 'geodir_payment_checkout_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_checkout_page_id() )
					|| ( function_exists( 'geodir_payment_invoices_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_invoices_page_id() )
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
						|| ( isset( $wp->query_vars['page_id'] ) && function_exists( 'geodir_payment_checkout_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_checkout_page_id() )
						|| ( isset( $wp->query_vars['page_id'] ) && function_exists( 'geodir_payment_invoices_page_id' ) && $wp->query_vars['page_id'] == geodir_payment_invoices_page_id() )
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


//check if homepage
			if ( ! isset( $wp->query_vars['gd_is_geodir_page'] )
			     && ! isset( $wp->query_vars['page_id'] )
			     && ! isset( $wp->query_vars['pagename'] )
			     && is_page_geodir_home()
			) {
				$wp->query_vars['gd_is_geodir_page'] = true;
			}
			//echo $wp->query_vars['gd_is_geodir_page'] ;
			/*echo "<pre>" ;
			print_r($wp) ;
			echo "</pre>" ;
		//	exit();
				*/
		} // end of is admin
	}

	/**
	 * Parse the request and look for query vars - endpoints may not be supported.
	 */
	public function parse_request() {
		global $wp;

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
	 */
	public function get_errors() {
		if ( ! empty( $_GET['gd_error'] ) && ( $error = sanitize_text_field( $_GET['gd_error'] ) ) && ! wc_has_notice( $error, 'error' ) ) {
			wc_add_notice( $error, 'error' );
		}
	}

	/**
	 * Init query vars by loading options.
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
	 * @return array
	 */
	public function get_query_vars() {
		return apply_filters( 'geodirectory_get_query_vars', $this->query_vars );
	}

	/**
	 * Get query current active query var.
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
	 * @return boolean
	 */
	private function is_showing_page_on_front( $q ) {
		return $q->is_home() && 'page' === get_option( 'show_on_front' );
	}

	/**
	 * Is the front page a page we define?
	 * @return boolean
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

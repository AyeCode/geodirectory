<?php
/**
 * WHERE Clause Builder
 *
 * Builds WHERE clauses for GeoDirectory post queries including search,
 * location filtering, author pages, and post status conditions.
 *
 * @package GeoDirectory\Database\Query\Builders
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Database\Query\Builders;

use AyeCode\GeoDirectory\Core\Services\QueryVars;
use AyeCode\GeoDirectory\Database\Query\Interfaces\SqlBuilderInterface;

/**
 * WHERE clause builder.
 *
 * @since 3.0.0
 */
final class WhereBuilder implements SqlBuilderInterface {
	/**
	 * WordPress database instance.
	 *
	 * @var \wpdb
	 */
	private \wpdb $db;

	/**
	 * Query variables service.
	 *
	 * @var QueryVars
	 */
	private QueryVars $query_vars;

	/**
	 * Constructor.
	 *
	 * @param QueryVars $query_vars Query variables service.
	 */
	public function __construct( QueryVars $query_vars ) {
		global $wpdb;
		$this->db = $wpdb;
		$this->query_vars = $query_vars;
	}

	/**
	 * Build WHERE clause.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string WHERE SQL.
	 */
	public function build( $query, string $post_type ): string {
		global $geodir_post_type;

		$where = '';
		$table = geodir_db_cpt_table( $post_type );

		// Check if we already have the CPT query
		$cpt_query = $this->db->prepare( "{$this->db->posts}.post_type = %s", $post_type );

		// Only add CPT if required (duplicate bad for big DBs)
		if ( strpos( $where, $cpt_query ) === false ) {
			$where .= $this->db->prepare( " AND {$this->db->posts}.post_type = %s ", $post_type );
		}

		// Search page WHERE
		if ( geodir_is_page( 'search' ) ) {
			$where = $this->build_search_where( $post_type, $table );
		}

		/**
		 * Filter main query WHERE clause.
		 *
		 * @since 2.0.0.67
		 *
		 * @param string $where     WHERE SQL.
		 * @param object $query     WP_Query object.
		 * @param string $post_type Post type.
		 */
		$where = apply_filters( 'geodir_main_query_posts_where', $where, $query, $geodir_post_type );

		/**
		 * Filter WHERE clause.
		 *
		 * @since 2.0.0
		 *
		 * @param string $where WHERE SQL.
		 * @param object $query WP_Query object.
		 */
		return apply_filters( 'geodir_posts_where', $where, $query );
	}

	/**
	 * Build search page WHERE clause.
	 *
	 * @param string $post_type Post type.
	 * @param string $table     Detail table name.
	 * @return string WHERE SQL.
	 */
	private function build_search_where( string $post_type, string $table ): string {
		global $geodirectory;

		// Early returns
		if ( is_single() && get_query_var( 'post_type' ) ) {
			return '';
		}

		if ( is_tax() ) {
			return '';
		}

		$s = $this->query_vars->get_search_term();
		$snear = $this->query_vars->get_search_near();
		$dist = $this->query_vars->get_search_distance();

		$s = trim( $s );
		$s = wp_specialchars_decode( $s, ENT_QUOTES );
		$gd_exact_search = $this->query_vars->is_exact_search( $s );

		// Remove quotes after checking
		if ( $gd_exact_search ) {
			$s = trim( wp_specialchars_decode( stripslashes( $s ), ENT_QUOTES ), '"' );
		}

		$where = '';
		$better_search_terms = '';
		$terms_sql = '';

		// Build keyword search terms
		if ( $s != '' ) {
			$better_search_terms = $this->build_keyword_search( $s, $gd_exact_search );
			$better_search_terms .= $this->build_search_title_terms( $s, $gd_exact_search, $post_type, $table );
		}

		// Get content and taxonomy term WHERE
		$content_where = $this->build_content_where( $s );
		$terms_sql = $this->build_taxonomy_terms_sql( $s, $gd_exact_search, $post_type, $table );

		// Fake near if we have GPS
		$latlon = $geodirectory->location->get_latlon();
		if ( $snear == '' && $latlon ) {
			$snear = ' ';
		}

		// Post status
		$status_where = $this->build_status_where( $post_type );

		$support_location = $post_type && geodirectory()->post_types->supports( $post_type, 'location' ) ;

		// Build final WHERE with or without location
		if ( $support_location && $snear != '' && $latlon && ! defined( 'GEODIR_MAP_SEARCH' ) ) {
			$where = $this->build_location_where( $s, $better_search_terms, $content_where, $terms_sql, $post_type, $status_where, $latlon, $dist, $table );
		} else {
			$post_title_where = $s != '' ? $this->db->prepare( "{$this->db->posts}.post_title LIKE %s", array( $s ) ) : '1=1';
			$where .= " AND ( ( $post_title_where $better_search_terms ) $content_where $terms_sql ) AND {$this->db->posts}.post_type = '{$post_type}' {$status_where}";
		}

		// Replace unwanted 1=1 clause
		$where = str_replace( ' AND ( ( 1=1  )   ) AND', 'AND', $where );

		return $where;
	}

	/**
	 * Build keyword search WHERE terms.
	 *
	 * @param string $s              Search term.
	 * @param bool   $gd_exact_search Is exact search.
	 * @return string Search terms SQL.
	 */
	private function build_keyword_search( string $s, bool $gd_exact_search ): string {
		$better_search_terms = '';

		if ( $gd_exact_search ) {
			$keywords = array( $s );
		} else {
			$keywords = array_unique( explode( ' ', $s ) );
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
					$better_search_term = $this->db->prepare( " OR {$this->db->posts}.post_title LIKE %s OR {$this->db->posts}.post_title LIKE %s ", array( $keyword . '%', '% ' . $keyword . '%' ) );

					/**
					 * Filter the search query keywords SQL.
					 *
					 * @since 1.5.9
					 *
					 * @param string $better_search_terms The query values.
					 * @param array  $keywords            The array of keywords for the query.
					 * @param string $keyword             The single keyword being searched.
					 */
					$better_search_terms .= apply_filters( 'geodir_search_better_search_terms', $better_search_term, $keywords, $keyword );
				}
			}
		}

		return $better_search_terms;
	}

	/**
	 * Build search title WHERE terms.
	 *
	 * @param string $s               Search term.
	 * @param bool   $gd_exact_search Is exact search.
	 * @param string $post_type       Post type.
	 * @param string $table           Detail table name.
	 * @return string Search title terms SQL.
	 */
	private function build_search_title_terms( string $s, bool $gd_exact_search, string $post_type, string $table ): string {
		$_search = geodir_sanitize_keyword( $s, $post_type );

		if ( $gd_exact_search ) {
			$_keywords = array( $_search );
		} else {
			$_keywords = array_unique( explode( ' ', $_search ) );

			if ( is_array( $_keywords ) && ( $klimit = (int) geodir_get_option( 'search_word_limit' ) ) ) {
				foreach ( $_keywords as $kkey => $kword ) {
					if ( geodir_utf8_strlen( $kword ) <= $klimit ) {
						unset( $_keywords[ $kkey ] );
					}
				}
			}
		}

		$better_search_terms = '';

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
				 * @param string $_keyword           The single keyword being searched.
				 * @param array  $_keywords          The array of keywords for the query.
				 */
				$_search_title_part = apply_filters( 'geodir_search_title_keyword_part', $_search_title_part, $_keyword, $_keywords );

				if ( ! empty( $_search_title_part ) ) {
					$_search_title_where[] = $_search_title_part;
				}
			}

			if ( ! empty( $_search_title_where ) ) {
				$_search_title_parts = ' OR ' . implode( ' OR ', $_search_title_where );
				$better_search_terms .= apply_filters( 'geodir_search_title_keyword_parts', $_search_title_parts, array( $s ) );
			}
		}

		/**
		 * Filter the search query keywords SQL.
		 *
		 * @since 2.0.0.82
		 *
		 * @param string $better_search_terms The query values.
		 * @param string $s                   The searched keyword.
		 */
		return apply_filters( 'geodir_search_better_search_terms_parts', $better_search_terms, $s );
	}

	/**
	 * Build content WHERE clause.
	 *
	 * @param string $s Search term.
	 * @return string Content WHERE SQL.
	 */
	private function build_content_where( string $s ): string {
		$content_where = '';

		if ( $s != '' ) {
			$content_where = $this->db->prepare( " OR ({$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s) ", array( $s . '%', '% ' . $s . '%', '%>' . $s . '%', '%\n' . $s . '%' ) );
			$content_where = str_replace( "\\n", "\n", $content_where );

			/**
			 * Filter the search query content where values.
			 *
			 * @since 1.5.0
			 *
			 * @param string $content_where The query values.
			 */
			$content_where = apply_filters( 'geodir_search_content_where', $content_where );
		}

		return $content_where;
	}

	/**
	 * Build taxonomy terms SQL.
	 *
	 * @param string $s               Search term.
	 * @param bool   $gd_exact_search Is exact search.
	 * @param string $post_type       Post type.
	 * @param string $table           Detail table name.
	 * @return string Taxonomy terms SQL.
	 */
	private function build_taxonomy_terms_sql( string $s, bool $gd_exact_search, string $post_type, string $table ): string {
		$terms_sql = '';

		if ( $s == '' ) {
			return $terms_sql;
		}

		// Get comma-separated search term for IN clause
		$s_A = $s;
		if ( strstr( $s, ',' ) ) {
			$s_AA = str_replace( ' ', '', $s );
			$s_A = explode( ',', $s_AA );
			$s_A = implode( '","', $s_A );
			$s_A = '"' . $s_A . '"';
		} else {
			$s_A = '"' . $s . '"';
		}

		// Get taxonomies
		$taxonomies = geodir_get_taxonomies( $post_type, true );
		if ( $taxonomies ) {
			$taxonomies = implode( "','", $taxonomies );
			$taxonomies = "'" . $taxonomies . "'";
		} else {
			return $terms_sql;
		}

		// Terms WHERE for taxonomy query
		if ( $gd_exact_search ) {
			$terms_where = $this->db->prepare( " AND ({$this->db->terms}.name LIKE %s ) ", array( $s ) );
		} else {
			$terms_where = $this->db->prepare( " AND ({$this->db->terms}.name LIKE %s OR {$this->db->terms}.name LIKE %s OR {$this->db->terms}.name IN ($s_A)) ", array( $s . '%', '% ' . $s . '%' ) );
		}

		/**
		 * Filter the search query term values.
		 *
		 * @since 1.5.0
		 *
		 * @param string $terms_where The SQL where.
		 */
		$terms_where = apply_filters( 'geodir_search_terms_where', $terms_where );

		// Get category exclusions
		$_post_category = $this->query_vars->get_search_categories();

		// Get term results
		$term_sql = "SELECT {$this->db->term_taxonomy}.term_id,{$this->db->terms}.name,{$this->db->term_taxonomy}.taxonomy
			FROM {$this->db->term_taxonomy}, {$this->db->terms}, {$this->db->term_relationships}
			WHERE {$this->db->term_taxonomy}.term_id = {$this->db->terms}.term_id
			AND {$this->db->term_relationships}.term_taxonomy_id = {$this->db->term_taxonomy}.term_taxonomy_id
			AND {$this->db->term_taxonomy}.taxonomy in ( {$taxonomies} )
			$terms_where
			GROUP BY {$this->db->term_taxonomy}.term_id";

		$term_results = $this->db->get_results( $term_sql );

		if ( ! empty( $term_results ) ) {
			foreach ( $term_results as $term ) {
				if ( ! empty( $_post_category ) && in_array( $term->term_id, $_post_category ) ) {
					continue;
				}

				if ( $term->taxonomy == $post_type . 'category' ) {
					$terms_sql .= $this->db->prepare( " OR FIND_IN_SET( %d , {$table}.post_category ) ", $term->term_id );
				} else {
					$terms_sql .= $this->db->prepare( " OR FIND_IN_SET( %s, {$table}.post_tags ) ", $term->name );
				}
			}
		}

		return $terms_sql;
	}

	/**
	 * Build post status WHERE clause.
	 *
	 * @param string $post_type Post type.
	 * @return string Status WHERE SQL.
	 */
	private function build_status_where( string $post_type ): string {
		$status = geodir_get_post_stati( 'search', array( 'post_type' => $post_type ) );

		if ( empty( $status ) ) {
			$status = array( 'publish' );
		} elseif ( is_scalar( $status ) ) {
			$status = array( $status );
		}

		if ( count( $status ) > 1 ) {
			$status_where = "AND {$this->db->posts}.post_status IN( '" . implode( "', '", $status ) . "' )";
		} else {
			$status_where = "AND {$this->db->posts}.post_status = '{$status[0]}'";
		}

		/**
		 * Filter post_status where condition.
		 *
		 * @since 2.1.1.5
		 *
		 * @param string $status_where Status where condition.
		 * @param array  $status       Post status array.
		 * @param string $post_type    Post type.
		 */
		return apply_filters( 'geodir_posts_where_post_status', $status_where, $status, $post_type );
	}

	/**
	 * Build location-based WHERE clause.
	 *
	 * @param string $s                  Search term.
	 * @param string $better_search_terms Keyword search SQL.
	 * @param string $content_where       Content WHERE SQL.
	 * @param string $terms_sql           Taxonomy terms SQL.
	 * @param string $post_type           Post type.
	 * @param string $status_where        Status WHERE SQL.
	 * @param array  $latlon              Lat/lon array.
	 * @param float  $dist                Distance radius.
	 * @param string $table               Detail table name.
	 * @return string Location WHERE SQL.
	 */
	private function build_location_where( string $s, string $better_search_terms, string $content_where, string $terms_sql, string $post_type, string $status_where, array $latlon, float $dist, string $table ): string {
		global $geodirectory;

		$lat = $latlon['lat'];
		$lon = $latlon['lon'];

		$between = geodir_get_between_latlon( $lat, $lon, $dist );
		$post_title_where = $s != '' ? $this->db->prepare( "{$this->db->posts}.post_title LIKE %s", array( $s ) ) : '1=1';
		$where = " AND ( ( $post_title_where $better_search_terms ) $content_where $terms_sql ) AND {$this->db->posts}.post_type = '{$post_type}' {$status_where}";

		if ( ! empty( $between ) && ! ( geodirectory()->post_types->supports( $post_type, 'service_distance' ) && $geodirectory->location->get_latlon() ) ) {
			$where .= $this->db->prepare( " AND ( latitude BETWEEN %f AND %f ) AND ( longitude BETWEEN %f AND %f ) ", $between['lat1'], $between['lat2'], $between['lon1'], $between['lon2'] );
		}

		if ( isset( $_REQUEST['sdistance'] ) && $_REQUEST['sdistance'] != 'all' ) {
			$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
			$where .= $this->db->prepare( " AND CONVERT((%f * 2 * ASIN(SQRT( POWER(SIN(((%f) - (`{$table}`.latitude)) * pi()/180 / 2), 2) +COS((%f) * pi()/180) * COS( (`{$table}`.latitude) * pi()/180) *POWER(SIN((%f - `{$table}`.longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= %f", $DistanceRadius, $lat, $lat, $lon, $dist );
		}

		// Private address
		if ( geodirectory()->post_types->supports( $post_type, 'private_address' ) ) {
			$where .= " AND ( `{$table}`.`private_address` IS NULL OR `{$table}`.`private_address` <> 1 ) ";
		}

		return $where;
	}

	/**
	 * Build author page WHERE clause.
	 *
	 * @param string $where     Current WHERE SQL.
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string WHERE SQL.
	 */
	public function build_author_where( string $where, $query, string $post_type ): string {
		global $wp_query;

		$cpts = geodir_get_posttypes( 'array' );

		// Author saves/favs filter
		if ( is_author() && ! empty( $wp_query->query['gd_favs'] ) ) {
			$post_types = array_keys( $cpts );
			$author_id = isset( $wp_query->query_vars['author'] ) ? $wp_query->query_vars['author'] : 0;

			if ( $author_id ) {
				$where = str_replace( "AND ({$this->db->posts}.post_author = $author_id)", '', $where );

				$user_favs = geodir_get_user_favourites( $author_id );

				if ( empty( $user_favs ) ) {
					$fav_in = "''";
				} else {
					$fav_in = $user_favs;
					$prepare_ids = implode( ',', array_fill( 0, count( $user_favs ), '%d' ) );
				}

				$where .= $this->db->prepare( " AND {$this->db->posts}.ID IN ($prepare_ids)", $fav_in );

				// Replace 'post' with GD post types
				if ( ! isset( $wp_query->query['post_type'] ) ) {
					$prepare_cpts = implode( ',', array_fill( 0, count( $post_types ), '%s' ) );
					$gd_cpt_replace = $this->db->prepare( "{$this->db->posts}.post_type IN ($prepare_cpts)", $post_types );
					$where = str_replace( "{$this->db->posts}.post_type = 'post'", $gd_cpt_replace, $where );
				}

				$user_id = get_current_user_id();
				$post_type = isset( $wp_query->query['post_type'] ) ? $wp_query->query['post_type'] : $post_types[0];
				$author_favorites_private = isset( $cpts[ $post_type ]['author_favorites_private'] ) && $cpts[ $post_type ]['author_favorites_private'] ? true : false;

				if ( $author_favorites_private && $author_id != $user_id ) {
					$where .= ' AND 1=2';
				}
			}
		} elseif ( is_author() ) {
			$post_types = array_keys( $cpts );
			$post_type = isset( $wp_query->query['post_type'] ) ? $wp_query->query['post_type'] : $post_types[0];
			$user_id = get_current_user_id();
			$author_id = isset( $wp_query->query_vars['author'] ) ? $wp_query->query_vars['author'] : 0;

			if ( $author_id && $author_id == $user_id ) {
				$statuses = geodir_get_post_stati( 'author-archive', array( 'post_type' => $post_type ) );

				$_statuses = "{$this->db->posts}.post_status = 'publish'";

				foreach ( $statuses as $status ) {
					if ( strpos( $where, "{$this->db->posts}.post_status = '" . $status . "'" ) === false ) {
						$_statuses .= $this->db->prepare( " OR {$this->db->posts}.post_status = %s", $status );
					}
				}

				$where = str_replace( "{$this->db->posts}.post_status = 'publish'", $_statuses, $where );
			}

			// Check if restricted
			$author_posts_private = isset( $cpts[ $post_type ]['author_posts_private'] ) && $cpts[ $post_type ]['author_posts_private'] ? true : false;

			if ( $author_posts_private && $author_id != $user_id ) {
				$where .= ' AND 1=2';
			}
		}

		return $where;
	}
}

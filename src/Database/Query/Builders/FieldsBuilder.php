<?php
/**
 * SELECT Fields Builder
 *
 * Builds SELECT field clauses for GeoDirectory post queries including
 * distance calculations and search relevance scoring fields.
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
 * SELECT fields builder.
 *
 * @since 3.0.0
 */
final class FieldsBuilder implements SqlBuilderInterface {
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
	 * Build SELECT fields clause.
	 *
	 * @param object $query     WP_Query object.
	 * @param string $post_type Post type.
	 * @return string SELECT fields SQL.
	 */
	public function build( $query, string $post_type ): string {
		$fields = '';
		$table = geodir_db_cpt_table( $post_type );

		// Add all detail table fields
		$fields .= ", {$table}.* ";

		// Add distance calculation if location supported and lat/lon present
		$support_location = $post_type && geodirectory()->post_types->supports( $post_type, 'location' ) ;
		if ( $support_location && ( $latlon = $this->query_vars->get_latlon() ) ) {
			$fields .= $this->build_distance_field( $latlon, $table );
		}

		// Add search relevance fields
		if ( geodir_is_page( 'search' ) ) {
			$s = $this->query_vars->get_search_term();
			if ( $s && trim( $s ) != '' ) {
				$fields .= $this->build_search_fields( $s, $table );
			}
		}

		/**
		 * Filter the SELECT fields clause.
		 *
		 * @since 2.0.0
		 *
		 * @param string $fields SELECT fields SQL.
		 * @param object $query  WP_Query object.
		 */
		return apply_filters( 'geodir_posts_fields', $fields, $query );
	}

	/**
	 * Build distance calculation field.
	 *
	 * @param array  $latlon Lat/lon array.
	 * @param string $table  Detail table name.
	 * @return string Distance field SQL.
	 */
	private function build_distance_field( array $latlon, string $table ): string {
		$DistanceRadius = geodir_getDistanceRadius( geodir_get_option( 'search_distance_long' ) );
		$lat = $latlon['lat'];
		$lon = $latlon['lon'];

		return $this->db->prepare(
			" , (%f * 2 * ASIN(SQRT( POWER(SIN(((%f) - (`{$table}`.latitude)) * pi()/180 / 2), 2) +COS((%f) * pi()/180) * COS( (`{$table}`.latitude) * pi()/180) *POWER(SIN((%f - `{$table}`.longitude) * pi()/180 / 2), 2) ))) AS distance ",
			$DistanceRadius,
			$lat,
			$lat,
			$lon
		);
	}

	/**
	 * Build search relevance scoring fields.
	 *
	 * @param string $s     Search term.
	 * @param string $table Detail table name.
	 * @return string Search fields SQL.
	 */
	private function build_search_fields( string $s, string $table ): string {
		$gd_exact_search = $this->query_vars->is_exact_search( $s );
		$gd_titlematch_part = '';

		// Build multi-keyword title match parts
		if ( ! $gd_exact_search ) {
			$keywords = explode( ' ', $s );

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

				foreach ( $parts as $key => $part ) {
					$gd_titlematch_part .= ' CASE WHEN ';
					$count = 0;

					foreach ( $keywords as $keyword ) {
						$keyword = trim( $keyword );
						$keyword = wp_specialchars_decode( $keyword, ENT_QUOTES );
						$count++;

						$gd_titlematch_part .= $this->db->prepare(
							"( {$this->db->posts}.post_title LIKE %s OR {$this->db->posts}.post_title LIKE %s ) ",
							array( $keyword . '%', '% ' . $keyword . '%' )
						);

						if ( $count < count( $keywords ) ) {
							$gd_titlematch_part .= $key . ' ';
						}
					}
					$gd_titlematch_part .= "THEN 1 ELSE 0 END AS {$part},";
				}
			}
		}

		$s = stripslashes_deep( $s );
		$s = wp_specialchars_decode( $s, ENT_QUOTES );

		$fields = '';

		// Featured field
		if ( geodir_column_exist( $table, 'featured' ) ) {
			$fields .= $this->db->prepare( ", CASE WHEN {$table}.featured=%d THEN 1 ELSE 0 END AS gd_featured ", 1 );
		}

		// Search relevance fields
		$fields .= $this->db->prepare(
			", CASE WHEN {$this->db->posts}.post_title LIKE %s THEN 1 ELSE 0 END AS gd_exacttitle, GD_TITLEMATCH_PART CASE WHEN ( {$this->db->posts}.post_title LIKE %s OR {$this->db->posts}.post_title LIKE %s OR {$this->db->posts}.post_title LIKE %s ) THEN 1 ELSE 0 END AS gd_titlematch, CASE WHEN ( {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s OR {$this->db->posts}.post_content LIKE %s ) THEN 1 ELSE 0 END AS gd_content",
			array(
				$s,
				$s,
				$s . '%',
				'% ' . $s . '%',
				$s,
				$s . ' %',
				'% ' . $s . ' %',
				'%>' . $s . '%',
				'% ' . $s,
				'% ' . $s . ','
			)
		);

		// Replace placeholder with actual title match part
		$fields = str_replace( 'gd_exacttitle, GD_TITLEMATCH_PART', "gd_exacttitle, {$gd_titlematch_part}", $fields );

		return $fields;
	}
}

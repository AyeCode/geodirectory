<?php
/**
 * Core Locations Manager
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

use AyeCode\GeoDirectory\Core\Data\LocationData;
use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * The core plugin's implementation of the LocationsInterface.
 * This handles the default, single-location functionality.
 */
final class Locations implements LocationsInterface { // <-- Renamed to plural and implements the correct interface
	private Settings $settings;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings utility class.
	 */
	public function __construct( Settings $settings ) {
		$this->settings = $settings;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_current(): LocationData {
		return $this->get_default();
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_default(): LocationData {


		// check cache
		$cache = wp_cache_get("geodir_get_default_location");
		if($cache !== false){
			return $cache;
		}

		$location = new LocationData();
		$location->city    = (string) $this->settings->get( 'default_location_city' );
		$location->region  = (string) $this->settings->get( 'default_location_region' );
		$location->country = (string) $this->settings->get( 'default_location_country' );
		$location->city_slug = (string) $this->settings->get( 'default_location_city_slug', sanitize_title( $location->city ) );
		$location->region_slug = (string) $this->settings->get( 'default_location_region_slug', sanitize_title( $location->region ) );
		$location->country_slug = (string) $this->settings->get( 'default_location_country_slug', sanitize_title( $location->country ) );
		$location->latitude = (float) $this->settings->get( 'default_location_latitude', 0.0 );
		$location->longitude = (float) $this->settings->get( 'default_location_longitude', 0.0 );
		$location->id = (int) $this->settings->get( 'default_location_id', 0 );


		/**
		 * Filter the default location.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_result The default location object.
		 */
		$location_result = apply_filters('geodir_get_default_location', $location );

		wp_cache_set("geodir_get_default_location", $location_result );


		return $location_result;
	}

	/**
	 * Get location data for a specific post.
	 *
	 * Accepts either a post ID or a post object with location data already loaded.
	 * If multi-city is enabled, extracts location from the post data.
	 * Otherwise returns the default location.
	 *
	 * @param int|object $post_or_id Post ID or post object with location data.
	 * @return LocationData Location data object.
	 */
	public function get_for_post( $post_or_id ): LocationData {
		// If multi-city is enabled, extract location from post data
		if ( $this->core_multi_city() ) {
			// Handle post object with location data already loaded
			if ( is_object( $post_or_id ) ) {
				$gd_post = $post_or_id;
			} else {
				// If just an ID, get the post info
				$gd_post = geodir_get_post_info( (int) $post_or_id );
			}

			if ( empty( $gd_post ) ) {
				return $this->get_default();
			}

			$location = new LocationData();

			// Names
			$location->city = ! empty( $gd_post->city ) ? stripslashes( $gd_post->city ) : '';
			$location->region = ! empty( $gd_post->region ) ? stripslashes( $gd_post->region ) : '';
			$location->country = ! empty( $gd_post->country ) ? stripslashes( $gd_post->country ) : '';

			// Slugs
			$location->city_slug = $location->city ? sanitize_title( $location->city ) : '';
			$location->region_slug = $location->region ? sanitize_title( $location->region ) : '';
			$location->country_slug = $location->country ? sanitize_title( $location->country ) : '';

			// GPS
			$location->latitude = ! empty( $gd_post->latitude ) ? (float) $gd_post->latitude : 0.0;
			$location->longitude = ! empty( $gd_post->longitude ) ? (float) $gd_post->longitude : 0.0;

			return $location;
		}

		return $this->get_default();
	}

	/**
	 * Get current location terms from query vars.
	 *
	 * @since 3.0.0
	 *
	 * @param string|null $location_array_from Deprecated.
	 * @param string $gd_post_type The post type.
	 * @return array The location term array.
	 */
	public function get_current_location_terms( ?string $location_array_from = null, string $gd_post_type = '' ): array {
		global $wp, $geodirectory;

		$location_array = array();

		if ( ( isset( $wp->query_vars['country'] ) && $wp->query_vars['country'] == 'me' ) || ( isset( $wp->query_vars['region'] ) && $wp->query_vars['region'] == 'me' ) || ( isset( $wp->query_vars['city'] ) && $wp->query_vars['city'] == 'me' ) ) {
			return $location_array;
		}

		if ( ! ( ! empty( $geodirectory ) && ! empty( $geodirectory->location ) ) ) {
			return $location_array;
		}

		$location_terms = $geodirectory->location->allowed_query_variables();

		foreach ( $location_terms as $location_term ) {
			$location_array[ $location_term ] = isset( $geodirectory->location->{$location_term . "_slug"} ) ? $geodirectory->location->{$location_term . "_slug"} : '';
		}

		// Set empty locations terms when outside default location active.
		if ( $this->core_multi_city() && ! geodir_is_page( 'single' ) && ! geodir_is_page( 'search' ) ) {
			$location_array = array();
		}

		/**
		 * Filter the location terms.
		 *
		 * @since 1.4.6
		 * @package GeoDirectory
		 *
		 * @param array $location_array {
		 *    Attributes of the location_array.
		 *
		 *    @type string $gd_country The country slug.
		 *    @type string $gd_region The region slug.
		 *    @type string $gd_city The city slug.
		 *
		 * }
		 * @param string $location_array_from Source type of location terms. Default session.
		 * @param string $gd_post_type WP post type.
		 */
		$location_array = apply_filters( 'geodir_current_location_terms', $location_array, $location_array_from, $gd_post_type );

		return $location_array;
	}

	/**
	 * Get all countries from the database.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Query arguments.
	 * @param bool $split Split UK into constituent countries.
	 * @return array Countries array.
	 */
	public function get_countries( array $args = array(), bool $split = true ): array {
		$countries = wp_country_database()->get_countries( $args );

		// Split UK.
		if ( $split && $this->split_uk() && ! empty( $countries ) ) {
			$_countries = array();
			$uk = array();
			$country_id = 0;

			foreach ( $countries as $key => $country ) {
				$country_id = max( $country_id, ( ! empty( $country->ID ) ? absint( $country->ID ) : ( $key + 1 ) ) );

				if ( ( ! empty( $country->name ) && $country->name == 'United Kingdom' ) || ( ! empty( $country->slug ) && $country->slug == 'united-kingdom' ) ) {
					$uk = $country;
					continue; // Skip UK.
				}

				$_countries[] = $country;
			}

			if ( ! empty( $uk ) ) {
				$uk_countries = array(
					array(
						'name' => 'England',
						'slug' => 'england',
						'capital' => 'London',
						'latlng'=> '52.3555177,-1.1743197',
						'population' => 56550000,
						'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-eng.svg'
					),
					array(
						'name' => 'Northern Ireland',
						'slug' => 'northern-ireland',
						'capital' => 'Belfast',
						'latlng'=> '54.7877149,-6.4923145',
						'population' => 1896000,
						'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-nir.svg'
					),
					array(
						'name' => 'Scotland',
						'slug' => 'scotland',
						'capital' => 'Edinburgh',
						'latlng'=> '56.4906712,-4.2026458',
						'population' => 5466000,
						'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-sct.svg'
					),
					array(
						'name' => 'Wales',
						'slug' => 'wales',
						'capital' => 'Cardiff',
						'latlng'=> '52.1306607,-3.7837117',
						'population' => 3170000,
						'flag' => 'https://flagicons.lipis.dev/flags/4x3/gb-wls.svg'
					)
				);

				foreach ( $uk_countries as $country ) {
					$country_id++;

					$uk_country = (array) $uk;
					$uk_country['ID'] = $country_id;
					$uk_country['name'] = $country['name'];
					$uk_country['slug'] = $country['slug'];
					$uk_country['capital'] = $country['capital'];
					$uk_country['latlng'] = $country['latlng'];
					$uk_country['population'] = $country['population'];
					$uk_country['flag'] = $country['flag'];

					$_countries[] = (object) $uk_country;
				}
			}

			$countries = $_countries;
		}

		/**
		 * Filters an array of all countries from wp country database.
		 *
		 * @since 2.2.14
		 *
		 * @param array $countries Countries array.
		 * @param bool $split Split the country like UK when enabled.
		 */
		return apply_filters( 'geodir_wp_countries', $countries, $split );
	}

	/**
	 * Get countries as key-value pairs (name => translated name).
	 *
	 * @since 3.0.0
	 *
	 * @return array Countries array.
	 */
	public function get_countries_list(): array {
		$rows = $this->get_countries();

		$countries = array();
		foreach ( $rows as $row ) {
			$countries[ $row->name ] = __( $row->name, 'geodirectory' );
		}

		asort( $countries );

		/**
		 * Filters an array of all countries by key => value.
		 *
		 * @since 2.2.14
		 *
		 * @param array $countries Countries array with key => value pair.
		 * @param array $rows Countries array.
		 */
		return apply_filters( 'geodir_get_countries', $countries, $rows );
	}

	/**
	 * Get country dropdown HTML.
	 *
	 * @since 3.0.0
	 *
	 * @param string $post_country Selected country.
	 * @param string $prefix Not yet implemented.
	 * @param bool $return_array Return array instead of HTML.
	 * @return string|array Dropdown HTML or array.
	 */
	public function get_country_dropdown( string $post_country = '', string $prefix = '', bool $return_array = false ) {
		$rows = $this->get_countries();

		$ISO2 = array();
		$countries = array();
		$latlng = array();

		foreach ( $rows as $row ) {
			$ISO2[ $row->name ] = $row->alpha2Code;
			$countries[ $row->name ] = __( $row->name, 'geodirectory' );

			$gps = explode( ",", $row->latlng );
			$latlng[ $row->name ]['lat'] = isset( $gps[0] ) ? $gps[0] : '';
			$latlng[ $row->name ]['lon'] = isset( $gps[1] ) ? $gps[1] : '';
		}

		asort( $countries );

		$array_out = array();

		$out_put = '<option ' . selected( '', $post_country, false ) . ' value="">' . __( 'Select Country', 'geodirectory' ) . '</option>';
		foreach ( $countries as $country => $name ) {
			$ccode = $ISO2[ $country ];
			$gps = $latlng[ $country ];
			$value = esc_attr( $country );

			$out_put .= '<option ' . selected( $post_country, $country, false ) . ' value="' . $value . '" data-country_code="' . $ccode . '" data-country_lat="' . $gps['lat'] . '" data-country_lon="' . $gps['lon'] . '" >' . $name . '</option>';

			$array_out[ $value ] = array(
				'label' => $name,
				'value' => $value,
				'extra_attributes' => array(
					'data-country_code' => $ccode,
					'data-country_lat'  => $gps['lat'],
					'data-country_lon'  => $gps['lon']
				)
			);
		}

		return $return_array ? $array_out : $out_put;
	}

	/**
	 * Get regions for a country (stub - extended by Location Manager).
	 *
	 * @since 3.0.0
	 *
	 * @param string $country Country slug or name.
	 * @return array Regions array.
	 */
	public function get_regions( string $country = '' ): array {
		/**
		 * Filter regions for a country.
		 *
		 * @since 3.0.0
		 *
		 * @param array $regions Regions array.
		 * @param string $country Country slug or name.
		 */
		return apply_filters( 'geodir_get_regions', array(), $country );
	}

	/**
	 * Get cities for a country/region (stub - extended by Location Manager).
	 *
	 * @since 3.0.0
	 *
	 * @param string $country Country slug or name.
	 * @param string $region Region slug or name.
	 * @return array Cities array.
	 */
	public function get_cities( string $country = '', string $region = '' ): array {
		/**
		 * Filter cities for a country/region.
		 *
		 * @since 3.0.0
		 *
		 * @param array $cities Cities array.
		 * @param string $country Country slug or name.
		 * @param string $region Region slug or name.
		 */
		return apply_filters( 'geodir_get_cities', array(), $country, $region );
	}

	/**
	 * Get neighbourhoods for a city (stub - extended by Location Manager).
	 *
	 * @since 3.0.0
	 *
	 * @param string $city City slug or name.
	 * @return array Neighbourhoods array.
	 */
	public function get_neighbourhoods( string $city = '' ): array {
		/**
		 * Filter neighbourhoods for a city.
		 *
		 * @since 3.0.0
		 *
		 * @param array $neighbourhoods Neighbourhoods array.
		 * @param string $city City slug or name.
		 */
		return apply_filters( 'geodir_get_neighbourhoods', array(), $city );
	}

	/**
	 * Create location slug from string.
	 *
	 * @since 3.0.0
	 *
	 * @param string $location_string Location string.
	 * @return string Location slug.
	 */
	public function create_location_slug( string $location_string ): string {
		global $geodirectory;

		return $geodirectory->location->create_location_slug( $location_string );
	}

	/**
	 * Check if multi-city mode is active.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if multi-city is active.
	 */
	public function core_multi_city(): bool {
		return ( ! defined( 'GEODIRLOCATION_VERSION' ) && geodir_get_option( 'multi_city' ) );
	}

	/**
	 * Check if UK should be split into constituent countries.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True to split UK.
	 */
	public function split_uk(): bool {
		$split_uk = geodir_get_option( 'split_uk' ) ? true : false;

		/**
		 * Filter the split of UK option.
		 *
		 * @since 2.2.14
		 *
		 * @param bool $split_uk True to split otherwise False.
		 */
		return apply_filters( 'geodir_split_uk', $split_uk );
	}

	/**
	 * Get ISO2 code for a country.
	 *
	 * Converts a country name to its ISO2 code. Handles special case for UK
	 * constituent countries (England, Scotland, Wales, Northern Ireland).
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Locations::get_country_iso2)
	 *
	 * @param string $country The country name.
	 * @return string Country ISO2 code or original country name if not found.
	 */
	public function get_country_iso2( string $country ): string {
		global $wp_country_database;

		// Handle UK constituent countries
		if ( in_array( strtolower( $country ), [ 'england', 'northern ireland', 'scotland', 'wales' ], true ) ) {
			$country = 'United Kingdom';
		}

		// Use the global country database to get ISO2
		if ( ! empty( $wp_country_database ) && method_exists( $wp_country_database, 'get_country_iso2' ) ) {
			$result = $wp_country_database->get_country_iso2( $country );
			if ( $result ) {
				return $result;
			}
		}

		return $country;
	}
}

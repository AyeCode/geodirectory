<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory GeoDir_Location.
 *
 * Locations functions.
 *
 * @class    GeoDir_Location
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_Location {

	public $country;
	public $country_slug;
	public $country_iso2;
	public $country_iso3;
	public $region;
	public $region_slug;
	public $city;
	public $city_slug;
	public $latitude;
	public $longitude;
	public $type;
	public $is_default;
	public $id;



	public function __construct( $data = array()) {

		if(empty($data)){
			//$this->set_current();
			add_action( 'wp', array( $this, 'set_current' ), 0 );
			add_action( 'geodir_settings_save_general', array( $this, 'clear_cache' ));

		}

	}

	public function clear_cache(){
		wp_cache_delete("geodir_get_default_location");
	}

	public function set_current(){
		$location = new stdClass();
		$location->city = geodir_get_option('default_location_city');
		$location->region = geodir_get_option('default_location_region');
		$location->country = geodir_get_option('default_location_country');
		$location->latitude = geodir_get_option('default_location_latitude');
		$location->longitude = geodir_get_option('default_location_longitude');

		// slugs
		$location->city_slug = urldecode( sanitize_title( $location->city ) );
		$location->region_slug = urldecode( sanitize_title( $location->region ) );
		$location->country_slug = urldecode( sanitize_title( $location->country ) );

		// id
		$location->id = 0;

		// type
		$location->type = 'city';

		// is_default
		$location->is_default = 1;
		
		
		// set search values
		if(isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search'] && geodir_is_page('search')){
			$location->city = '';
			$location->region = '';
			$location->country = '';
			$location->latitude = '';
			$location->longitude = '';
			$location->city_slug = '';
			$location->region_slug = '';
			$location->country_slug = '';
			$location->id = 0;
			$location->type = 'search';
			$location->is_default = 0;

			// set GPS
			$latlon = $this->get_latlon();
			if(!empty($latlon['lat'])){$location->latitude = $latlon['lat'];}
			if(!empty($latlon['lon'])){$location->longitude = $latlon['lon'];}
		}
		

		/**
		 * Filter the default location.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_result The default location object.
		 */
		$location_result = apply_filters('geodir_set_current_location', $location );

		$this->country_slug = $location_result->country_slug;
		$this->country = $location_result->country;
		$this->region_slug = $location_result->region_slug;
		$this->region = $location_result->region;
		$this->city_slug  = $location_result->city_slug ;
		$this->city = $location_result->city;
		$this->latitude = $location_result->latitude;
		$this->longitude = $location_result->longitude;
		$this->type = $location_result->type;
		$this->is_default = $location_result->is_default;
		$this->id = $location_result->id;
		
	}

	/**
	 * Returns the default location.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @return object
	 */
	public function get_default_location()
	{

		// check cache
		$cache = wp_cache_get("geodir_get_default_location");
		if($cache !== false){
			return $cache;
		}

		$location = new stdClass();
		$location->city = geodir_get_option('default_location_city');
		$location->region = geodir_get_option('default_location_region');
		$location->country = geodir_get_option('default_location_country');
		$location->latitude = geodir_get_option('default_location_latitude');
		$location->longitude = geodir_get_option('default_location_longitude');

		// slugs
		$location->city_slug = sanitize_title($location->city);
		$location->region_slug = sanitize_title($location->region);
		$location->country_slug = sanitize_title($location->country);

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


	public function get_country_name_from_slug($slug){
		return geodir_get_option('default_location_country');
	}

	public function get_region_name_from_slug($slug){
		return geodir_get_option('default_location_region');
	}

	public function get_city_name_from_slug($slug){
		return geodir_get_option('default_location_city');
	}

	public function get_post_location($gd_post){
		return $this->get_default_location();
	}


	/**
	 * Returns location slug using location string.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @param string $location_string The location string.
	 * @return string The location slug.
	 */
	public function create_location_slug($location_string)
	{

		/**
		 * Filter the location slug.
		 *
		 * @since 1.0.0
		 * @package GeoDirectory
		 *
		 * @param string $location_string Sanitized location string.
		 */
		return urldecode(apply_filters('geodir_location_slug_check', sanitize_title($location_string)));
	}

	/**
	 * Checks whether the default location is set or not.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @return bool
	 */
	public function is_default_location_set()
	{
		$default_location = $this->get_default_location();
		if (!empty($default_location))
			return true;
		else
			return false;
	}

	/**
	 * Get ISO2 of the country.
	 *
	 * @since 1.6.16
	 * @package GeoDirectory
	 * @param string $country The country name.
	 * @return string Country ISO2 code.
	 */
	function get_country_iso2($country) {
		global $wp_country_database;

		if ($result = $wp_country_database->get_country_iso2($country)) {
			return $result;
		}

		return $country;
	}

	/**
	 * Get ISO2 of the country.
	 *
	 * @since 1.6.16
	 * @package GeoDirectory
	 * @param string $country The country name.
	 * @return string Country ISO2 code.
	 */
	function get_country_iso3($country) {
		global $wp_country_database;

		if ($result = $wp_country_database->get_country_iso3($country)) {
			return $result;
		}

		return $country;
	}


	/**
	 * Get the lat and lon from the query var
	 *
	 * @return array
	 */
	public function get_latlon(){
		global $wp;
		$latlon = array();
		if(!empty($wp->query_vars['latlon'])){
			$gps = explode(",",$wp->query_vars['latlon']);
			$latlon['lat'] = isset($gps[0]) ? filter_var($gps[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
			$latlon['lon'] = isset($gps[1]) ? filter_var($gps[1], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
		}elseif( !empty($_REQUEST['sgeo_lat']) && !empty($_REQUEST['sgeo_lon'])){
			$latlon['lat'] = isset($_REQUEST['sgeo_lat']) ? filter_var($_REQUEST['sgeo_lat'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
			$latlon['lon'] = isset($_REQUEST['sgeo_lon']) ? filter_var($_REQUEST['sgeo_lon'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : '';
		}
		return $latlon;
	}


	/**
	 * An array of the used location vars.
	 *
	 * These are used in the where queries.
	 *
	 * @return array
	 */
	public function allowed_query_variables(){
		return array('country','region','city');
	}

	public function is_type_gps() {
		$gps_type = false;

		if ( $this->type && in_array( $this->type, array( 'gps', 'me', 'search' ) ) && ! empty( $this->get_latlon() ) ) {
			$gps_type = true;
		}

		return $gps_type;
	}

}



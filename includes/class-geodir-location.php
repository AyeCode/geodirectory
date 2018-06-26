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

		}

	}

	public function set_current(){
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

		// id
		$location->id = 0;

		// type
		$location->type = 'city';

		// is_default
		$location->is_default = 1;

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
		return $location_result = apply_filters('geodir_get_default_location', $location );
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
	 * Get normal untranslated country name.
	 *
	 * @since 1.6.16
	 * @package GeoDirectory
	 * @param string $country The country name.
	 * @return string Returns the country.
	 */
	public function get_untranslated_country($country) {
		global $wpdb;
		if ($result = geodir_get_country_by_name($country)) {
			return $result;
		}

		$default_location = $this->get_default_location();
		if (!empty($default_location->country) && $result = geodir_get_country_by_name($default_location->country)) {
			return $result;
		}

		if (!empty($default_location->country_slug) && $result = geodir_get_country_by_name($default_location->country_slug)) {
			return $result;
		}

		if (!empty($default_location->country_ISO2) && $result = geodir_get_country_by_name($default_location->country_ISO2, true)) {
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
	function get_country_iso2($country) {
		global $wpdb;

		if ($result = $wpdb->get_var($wpdb->prepare("SELECT ISO2 FROM " . GEODIR_COUNTRIES_TABLE . " WHERE Country LIKE %s", $country))) {
			return $result;
		}
		if ($result = $wpdb->get_var($wpdb->prepare("SELECT ISO2 FROM " . GEODIR_COUNTRIES_TABLE . " WHERE Country LIKE %s", $this->get_untranslated_country($country)))) {
			return $result;
		}

		return $country;
	}

}



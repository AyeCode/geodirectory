<?php
/**
 * Core Locations Manager
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

use AyeCode\GeoDirectory\Core\Data\LocationData;
use AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface;
use AyeCode\GeoDirectory\Core\Utils\Settings;

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
	 * {@inheritDoc}
	 */
	public function get_for_post( int $post_id ): LocationData {
		return $this->get_default();
	}
}

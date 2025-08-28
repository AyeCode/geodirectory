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
		$location = new LocationData();
		$location->city    = (string) $this->settings->get( 'default_location_city' );
		$location->region  = (string) $this->settings->get( 'default_location_region' );
		$location->country = (string) $this->settings->get( 'default_location_country' );
		return $location;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_for_post( int $post_id ): LocationData {
		return $this->get_default();
	}
}

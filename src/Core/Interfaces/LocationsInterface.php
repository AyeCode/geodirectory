<?php
/**
 * Locations Manager Interface
 *
 * @package GeoDirectory\Core\Interfaces
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Interfaces;

use AyeCode\GeoDirectory\Core\Data\LocationData;

/**
 * Defines the contract for any class that manages location data.
 */
interface LocationsInterface { // <-- Renamed to plural "LocationsInterface"
	/**
	 * Gets the current location for the page view.
	 */
	public function get_current(): LocationData;

	/**
	 * Gets the default location set in the admin settings.
	 */
	public function get_default(): LocationData;

	/**
	 * Gets the location data for a specific post.
	 *
	 * @param int $post_id The ID of the post.
	 */
	public function get_for_post( int $post_id ): LocationData;
}

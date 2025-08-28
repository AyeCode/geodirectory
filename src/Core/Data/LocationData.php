<?php
/**
 * Location Data Object
 *
 * @package GeoDirectory\Core\Data
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Data;

/**
 * A simple container for location properties.
 */
class LocationData {
	public string $country = '';
	public string $region = '';
	public string $city = '';
	public string $country_slug = '';
	public string $region_slug = '';
	public string $city_slug = '';
	public float $latitude = 0.0;
	public float $longitude = 0.0;
	public int $id = 0;
}

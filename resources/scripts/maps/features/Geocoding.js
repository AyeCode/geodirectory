/**
 * Geocoding - Utilities for geocoding and reverse geocoding
 *
 * Provides a unified interface for geocoding operations using the map provider.
 */
import { CountryRules } from './CountryRules.js';

export class Geocoding {
	/**
	 * Constructor
	 * @param {MapProvider} mapProvider - The map provider instance
	 */
	constructor(mapProvider) {
		this.mapProvider = mapProvider;
	}

	/**
	 * Geocode an address to coordinates
	 *
	 * @param {string} address - Full address string
	 * @param {string} countryISO - Optional country ISO code to restrict search
	 * @returns {Promise<Object>} Geocoded result with lat, lng, and parsed address
	 */
	async geocodeAddress(address, countryISO = '') {
		try {
			const request = countryISO
				? { address, country: countryISO }
				: address;

			const results = await this.mapProvider.geocode(request);

			if (!results || results.length === 0) {
				throw new Error('No results found');
			}

			// Parse the address components
			const parsed = CountryRules.parseAddress(results, {
				mapType: this.mapProvider.getType()
			});

			// For Google Maps
			if (this.mapProvider.getType() === 'google' && results[0].geometry) {
				const location = results[0].geometry.location;
				return {
					lat: typeof location.lat === 'function' ? location.lat() : location.lat,
					lng: typeof location.lng === 'function' ? location.lng() : location.lng,
					address: parsed,
					rawResults: results
				};
			}

			// For OSM
			if (this.mapProvider.getType() === 'osm') {
				return {
					lat: results[0].lat,
					lng: results[0].lon || results[0].lng,
					address: parsed,
					rawResults: results
				};
			}

			throw new Error('Invalid geocode result format');
		} catch (error) {
			console.error('Geocode error:', error);
			throw error;
		}
	}

	/**
	 * Reverse geocode coordinates to an address
	 *
	 * @param {number} lat - Latitude
	 * @param {number} lng - Longitude
	 * @param {Object} additionalInfo - Additional info from address field (for better results)
	 * @returns {Promise<Object>} Parsed address components
	 */
	async reverseGeocode(lat, lng, additionalInfo = {}) {
		try {
			const results = await this.mapProvider.reverseGeocode(lat, lng);

			if (!results || results.length === 0) {
				throw new Error('Cannot determine address at this location');
			}

			// Parse the address components
			const parsed = CountryRules.parseAddress(results, {
				mapType: this.mapProvider.getType()
			});

			return {
				address: parsed,
				rawResults: results
			};
		} catch (error) {
			console.error('Reverse geocode error:', error);
			throw error;
		}
	}

	/**
	 * Build geocode address string from field values
	 *
	 * @param {Object} fields - Address field values
	 * @param {string} fields.street - Street address
	 * @param {string} fields.city - City
	 * @param {string} fields.region - Region/state
	 * @param {string} fields.country - Country
	 * @param {string} fields.countryISO - Country ISO code
	 * @param {string} fields.zip - Zip/postal code
	 * @param {boolean} isRestrict - Whether map is restricted to specific city
	 * @returns {string} Formatted address string for geocoding
	 */
	static buildAddressString(fields, isRestrict = false) {
		let address = '';

		// For restricted mode, just use street + zip
		if (isRestrict && fields.zip && fields.street) {
			return `${fields.street}, ${fields.zip}`;
		}

		// Ensure we don't duplicate fields
		const street = fields.street || '';
		const city = fields.city || '';
		const region = fields.region || '';
		const country = fields.country || '';
		const zip = fields.zip || '';

		// Don't include street if it matches other fields
		if (street && street !== city && street !== region && street !== country && street !== zip) {
			address += street;
		}

		// UK is special - don't include region
		if (fields.countryISO === 'GB') {
			address = Geocoding.appendToAddress(address, city);
			address = Geocoding.appendToAddress(address, country);
		} else {
			address = Geocoding.appendToAddress(address, city);
			address = Geocoding.appendToAddress(address, region);
			address = Geocoding.appendToAddress(address, country);
		}

		if (zip) {
			address = Geocoding.appendToAddress(address, zip);
		}

		// Clean up multiple commas
		address = address.replace(/,+/g, ',');
		address = address.replace(/(^,)|(,$)/g, '').trim();

		// Replace "null" values
		address = address.replace(/,null,/g, ',');

		return address;
	}

	/**
	 * Helper to append address part with comma
	 */
	static appendToAddress(address, part) {
		if (!part || part === 'null') return address;
		return address ? `${address}, ${part}` : part;
	}
}

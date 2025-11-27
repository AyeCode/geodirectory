/**
 * OSMMapProvider - OpenStreetMap/Leaflet implementation
 *
 * Wraps Leaflet (OSM) in the MapProvider interface.
 * Uses jQuery.goMap for backwards compatibility.
 */
import { MapProvider } from './MapProvider.js';

export class OSMMapProvider extends MapProvider {
	constructor(elementId, options = {}) {
		super(elementId, options);
		this.mapType = 'osm';
	}

	/**
	 * Initialize Leaflet map
	 */
	init() {
		if (!window.L || !L.version) {
			console.error('Leaflet not loaded');
			return false;
		}

		// Use goMap jQuery plugin for compatibility
		const $map = jQuery(`#${this.elementId}`);
		if (!$map.length) {
			console.error(`Map element #${this.elementId} not found`);
			return false;
		}

		$map.goMap({
			latitude: this.options.latitude || 0,
			longitude: this.options.longitude || 0,
			zoom: this.options.zoom || 12,
			maptype: this.options.maptype || 'ROADMAP',
			streetViewControl: this.options.streetViewControl !== false,
			scrollwheel: this.options.scrollwheel !== false,
			...this.options.goMapOptions
		});

		// Store reference to the map
		this.map = jQuery.goMap.map;

		return true;
	}

	/**
	 * Create a marker
	 */
	createMarker(options) {
		const marker = jQuery.goMap.createMarker({
			latitude: options.lat || options.latitude,
			longitude: options.lng || options.longitude,
			id: options.id || `marker_${Date.now()}`,
			icon: options.icon,
			draggable: options.draggable !== false,
			title: options.title || '',
			addToMap: true,
			w: options.w,
			h: options.h
		});

		this.markers.push(marker);
		return marker;
	}

	/**
	 * Get center of map
	 */
	getCenter() {
		if (!this.map) return null;
		const center = this.map.getCenter();
		return {
			lat: center.lat,
			lng: center.lng
		};
	}

	/**
	 * Set center of map
	 */
	setCenter(lat, lng) {
		if (!this.map) return;
		this.map.setView([lat, lng]);
	}

	/**
	 * Pan to location
	 */
	panTo(lat, lng) {
		if (!this.map) return;
		this.map.panTo(new L.LatLng(lat, lng));
	}

	/**
	 * Get zoom level
	 */
	getZoom() {
		if (!this.map) return null;
		return this.map.getZoom();
	}

	/**
	 * Set zoom level
	 */
	setZoom(zoom) {
		if (!this.map) return;
		this.map.setZoom(zoom);
	}

	/**
	 * Forward geocode using OSM Nominatim
	 */
	async geocode(request) {
		const address = typeof request === 'string' ? request : request.address;
		const countryCode = request.country || '';

		if (!address) {
			throw new Error('Address is required for geocoding');
		}

		try {
			const params = new URLSearchParams({
				q: address,
				format: 'json',
				addressdetails: '1',
				limit: '1'
			});

			if (countryCode) {
				params.append('countrycodes', countryCode.toLowerCase());
			}

			const response = await fetch(`https://nominatim.openstreetmap.org/search?${params}`);
			const results = await response.json();

			return results.map(result => this.normalizeOSMResult(result));
		} catch (error) {
			throw new Error(`OSM Geocoding failed: ${error.message}`);
		}
	}

	/**
	 * Reverse geocode using OSM Nominatim
	 */
	async reverseGeocode(lat, lng) {
		try {
			const params = new URLSearchParams({
				lat: lat.toString(),
				lon: lng.toString(),
				format: 'json',
				addressdetails: '1'
			});

			const response = await fetch(`https://nominatim.openstreetmap.org/reverse?${params}`);
			const result = await response.json();

			return [this.normalizeOSMResult(result)];
		} catch (error) {
			throw new Error(`OSM Reverse geocoding failed: ${error.message}`);
		}
	}

	/**
	 * Normalize OSM result to match Google Maps format
	 */
	normalizeOSMResult(osmResult) {
		const address = osmResult.address || {};

		return {
			display_address: osmResult.display_name,
			lat: parseFloat(osmResult.lat),
			lon: parseFloat(osmResult.lon),
			postcode: address.postcode || '',
			city: address.city || address.town || address.village || '',
			state: address.state || '',
			country: address.country || '',
			country_code: address.country_code ? address.country_code.toUpperCase() : '',
			address: address,
			// GB split support
			gb_country: this.getGBCountry(address)
		};
	}

	/**
	 * Get specific country for GB addresses (England, Scotland, Wales, Northern Ireland)
	 */
	getGBCountry(address) {
		if (address.country_code !== 'gb') return null;

		const state = address.state || '';
		const gbCountries = ['England', 'Northern Ireland', 'Scotland', 'Wales'];

		for (const country of gbCountries) {
			if (state.includes(country)) {
				return country;
			}
		}

		return null;
	}

	/**
	 * Add event listener
	 */
	addEventListener(event, callback) {
		if (!this.map) return;

		// Map event names to Leaflet events
		const eventMap = {
			'dragend': 'dragend',
			'zoom_changed': 'zoomend',
			'click': 'click'
		};

		const leafletEvent = eventMap[event] || event;
		this.map.on(leafletEvent, callback);
	}

	/**
	 * Add marker event listener
	 */
	addMarkerListener(marker, event, callback) {
		if (!marker) return;
		marker.on(event, callback);
	}
}

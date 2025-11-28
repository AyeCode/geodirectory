/**
 * GoogleMapProvider - Google Maps implementation
 *
 * Wraps Google Maps API in the MapProvider interface.
 */
import { MapProvider } from './MapProvider.js';

export class GoogleMapProvider extends MapProvider {
	constructor(elementId, options = {}) {
		super(elementId, options);
		this.mapType = 'google';
		this.geocoder = null;
	}

	/**
	 * Initialize Google Maps
	 */
	init() {
		console.log('[GoogleMapProvider] Initializing for element:', this.elementId);
		console.log('[GoogleMapProvider] Options:', this.options);

		if (!window.google || !google.maps) {
			console.error('[GoogleMapProvider] Google Maps not loaded');
			return false;
		}

		console.log('[GoogleMapProvider] Google Maps API is available');

		// Initialize geocoder
		this.geocoder = new google.maps.Geocoder();
		console.log('[GoogleMapProvider] Geocoder initialized');

		// Get map element
		const mapElement = document.getElementById(this.elementId);
		console.log('[GoogleMapProvider] Map element:', mapElement);

		if (!mapElement) {
			console.error(`[GoogleMapProvider] Map element #${this.elementId} not found`);
			return false;
		}

		// Map type conversion
		const mapTypeId = this.options.maptype ?
			google.maps.MapTypeId[this.options.maptype] || google.maps.MapTypeId.ROADMAP :
			google.maps.MapTypeId.ROADMAP;

		console.log('[GoogleMapProvider] Creating map with options:', {
			center: { lat: this.options.latitude || 0, lng: this.options.longitude || 0 },
			zoom: this.options.zoom || 12,
			mapTypeId: mapTypeId
		});

		// Create map directly with Google Maps API
		this.map = new google.maps.Map(mapElement, {
			center: {
				lat: parseFloat(this.options.latitude) || 0,
				lng: parseFloat(this.options.longitude) || 0
			},
			zoom: parseInt(this.options.zoom) || 12,
			mapTypeId: mapTypeId,
			streetViewControl: this.options.streetViewControl !== false,
			scrollwheel: this.options.scrollwheel !== false,
			...this.options.mapOptions
		});

		console.log('[GoogleMapProvider] Map initialized:', this.map ? 'SUCCESS' : 'FAILED');

		return true;
	}

	/**
	 * Create a marker
	 */
	createMarker(options) {
		const lat = parseFloat(options.lat || options.latitude) || 0;
		const lng = parseFloat(options.lng || options.longitude) || 0;

		const markerOptions = {
			position: { lat, lng },
			map: this.map,
			draggable: options.draggable !== false,
			title: options.title || ''
		};

		// Add custom icon if provided
		if (options.icon) {
			const iconOptions = {
				url: options.icon
			};

			// Add size if provided
			if (options.w && options.h) {
				iconOptions.scaledSize = new google.maps.Size(
					parseInt(options.w),
					parseInt(options.h)
				);
				iconOptions.anchor = new google.maps.Point(
					parseInt(options.w) / 2,
					parseInt(options.h)
				);
			}

			markerOptions.icon = iconOptions;
		}

		const marker = new google.maps.Marker(markerOptions);

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
			lat: center.lat(),
			lng: center.lng()
		};
	}

	/**
	 * Set center of map
	 */
	setCenter(lat, lng) {
		if (!this.map) return;
		this.map.setCenter(new google.maps.LatLng(lat, lng));
	}

	/**
	 * Pan to location
	 */
	panTo(lat, lng) {
		if (!this.map) return;
		this.map.panTo(new google.maps.LatLng(lat, lng));
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
	 * Forward geocode
	 */
	geocode(request) {
		return new Promise((resolve, reject) => {
			if (!this.geocoder) {
				reject(new Error('Geocoder not initialized'));
				return;
			}

			const geocodeRequest = typeof request === 'string'
				? { address: request }
				: request;

			this.geocoder.geocode(geocodeRequest, (results, status) => {
				if (status === google.maps.GeocoderStatus.OK) {
					resolve(results);
				} else {
					reject(new Error(`Geocoding failed: ${status}`));
				}
			});
		});
	}

	/**
	 * Reverse geocode
	 */
	reverseGeocode(lat, lng) {
		return new Promise((resolve, reject) => {
			if (!this.geocoder) {
				reject(new Error('Geocoder not initialized'));
				return;
			}

			this.geocoder.geocode({
				latLng: new google.maps.LatLng(lat, lng)
			}, (results, status) => {
				if (status === google.maps.GeocoderStatus.OK) {
					resolve(results);
				} else {
					reject(new Error(`Reverse geocoding failed: ${status}`));
				}
			});
		});
	}

	/**
	 * Add event listener
	 */
	addEventListener(event, callback) {
		if (!this.map) return;

		// Map event names to Google Maps events
		const eventMap = {
			'dragend': 'dragend',
			'zoom_changed': 'zoom_changed',
			'click': 'click'
		};

		const googleEvent = eventMap[event] || event;
		google.maps.event.addListener(this.map, googleEvent, callback);
	}

	/**
	 * Add marker event listener
	 */
	addMarkerListener(marker, event, callback) {
		if (!marker) return;
		google.maps.event.addListener(marker, event, callback);
	}
}

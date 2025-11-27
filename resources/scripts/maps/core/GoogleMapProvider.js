/**
 * GoogleMapProvider - Google Maps implementation
 *
 * Wraps Google Maps API in the MapProvider interface.
 * Uses jQuery.goMap for backwards compatibility with existing code.
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

		// Use goMap jQuery plugin for compatibility
		const $map = jQuery(`#${this.elementId}`);
		console.log('[GoogleMapProvider] Map element jQuery object:', $map);
		console.log('[GoogleMapProvider] Map element exists?', $map.length > 0);

		if (!$map.length) {
			console.error(`[GoogleMapProvider] Map element #${this.elementId} not found`);
			return false;
		}

		console.log('[GoogleMapProvider] Calling goMap with options:', {
			latitude: this.options.latitude || 0,
			longitude: this.options.longitude || 0,
			zoom: this.options.zoom || 12,
			maptype: this.options.maptype || 'ROADMAP'
		});

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
		console.log('[GoogleMapProvider] Map initialized:', this.map ? 'SUCCESS' : 'FAILED');

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

/**
 * MapProvider - Abstract base class for map implementations
 *
 * Provides a common interface for Google Maps and OpenStreetMap implementations.
 * All map provider implementations must extend this class.
 */
export class MapProvider {
	/**
	 * Constructor
	 * @param {string} elementId - The DOM element ID for the map
	 * @param {Object} options - Map configuration options
	 */
	constructor(elementId, options = {}) {
		this.elementId = elementId;
		this.options = options;
		this.map = null;
		this.markers = [];
	}

	/**
	 * Initialize the map
	 * Must be implemented by subclasses
	 */
	init() {
		throw new Error('MapProvider.init() must be implemented by subclass');
	}

	/**
	 * Create a marker on the map
	 * Must be implemented by subclasses
	 * @param {Object} options - Marker options (lat, lng, icon, draggable, etc.)
	 * @returns {Object} The created marker object
	 */
	createMarker(options) {
		throw new Error('MapProvider.createMarker() must be implemented by subclass');
	}

	/**
	 * Get the center of the map
	 * Must be implemented by subclasses
	 * @returns {Object} LatLng object {lat, lng}
	 */
	getCenter() {
		throw new Error('MapProvider.getCenter() must be implemented by subclass');
	}

	/**
	 * Set the center of the map
	 * Must be implemented by subclasses
	 * @param {number} lat - Latitude
	 * @param {number} lng - Longitude
	 */
	setCenter(lat, lng) {
		throw new Error('MapProvider.setCenter() must be implemented by subclass');
	}

	/**
	 * Pan the map to a location
	 * Must be implemented by subclasses
	 * @param {number} lat - Latitude
	 * @param {number} lng - Longitude
	 */
	panTo(lat, lng) {
		throw new Error('MapProvider.panTo() must be implemented by subclass');
	}

	/**
	 * Get the current zoom level
	 * Must be implemented by subclasses
	 * @returns {number} Zoom level
	 */
	getZoom() {
		throw new Error('MapProvider.getZoom() must be implemented by subclass');
	}

	/**
	 * Set the zoom level
	 * Must be implemented by subclasses
	 * @param {number} zoom - Zoom level
	 */
	setZoom(zoom) {
		throw new Error('MapProvider.setZoom() must be implemented by subclass');
	}

	/**
	 * Forward geocode an address to coordinates
	 * Must be implemented by subclasses
	 * @param {string|Object} request - Address string or geocode request object
	 * @returns {Promise<Object>} Geocode results
	 */
	geocode(request) {
		throw new Error('MapProvider.geocode() must be implemented by subclass');
	}

	/**
	 * Reverse geocode coordinates to an address
	 * Must be implemented by subclasses
	 * @param {number} lat - Latitude
	 * @param {number} lng - Longitude
	 * @returns {Promise<Object>} Reverse geocode results
	 */
	reverseGeocode(lat, lng) {
		throw new Error('MapProvider.reverseGeocode() must be implemented by subclass');
	}

	/**
	 * Add event listener to the map
	 * Must be implemented by subclasses
	 * @param {string} event - Event name
	 * @param {Function} callback - Event handler
	 */
	addEventListener(event, callback) {
		throw new Error('MapProvider.addEventListener() must be implemented by subclass');
	}

	/**
	 * Get the map type name
	 * @returns {string} 'google' or 'osm'
	 */
	getType() {
		return this.mapType;
	}

	/**
	 * Check if the map is ready
	 * @returns {boolean}
	 */
	isReady() {
		return this.map !== null;
	}
}

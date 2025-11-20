/**
 * GeoDirectory Map Handler
 * * Isolated logic for handling Google Maps or OpenStreetMaps.
 * Loaded conditionally via Assets class.
 */

class GeoDirMap {
	constructor(elementId, options) {
		this.elementId = elementId;
		this.options = options || {};
		this.init();
	}

	init() {
		console.log(`Initializing Map for #${this.elementId}`);
		// Map logic will go here (Google vs Leaflet)
	}
}

// Expose to window so inline scripts can instantiate it
window.GeoDirMap = GeoDirMap;

/**
 * GeoDirectory Maps
 *
 * Modern modular map system for GeoDirectory.
 * Supports Google Maps and OpenStreetMap (Leaflet).
 */

// Core classes
import { MapProvider } from './core/MapProvider.js';
import { GoogleMapProvider } from './core/GoogleMapProvider.js';
import { OSMMapProvider } from './core/OSMMapProvider.js';
import { MapFactory } from './core/MapFactory.js';

// Features
import { Geocoding } from './features/Geocoding.js';
import { CountryRules } from './features/CountryRules.js';
import { AddressField } from './features/AddressField.js';

// Export everything under GeoDir.Maps namespace
const Maps = {
	// Core
	MapProvider,
	GoogleMapProvider,
	OSMMapProvider,
	MapFactory,

	// Features
	Geocoding,
	CountryRules,
	AddressField,

	// Convenience methods
	createMap(elementId, options = {}) {
		const provider = MapFactory.create(elementId, options);
		if (provider) {
			provider.init();
		}
		return provider;
	},

	getAvailableProvider() {
		return MapFactory.getAvailableProvider();
	},

	isGoogleAvailable() {
		return MapFactory.isGoogleMapsAvailable();
	},

	isOSMAvailable() {
		return MapFactory.isOSMAvailable();
	}
};

// Attach to window for global access
window.GeoDir = window.GeoDir || {};
window.GeoDir.Maps = Maps;

console.log('[GeoDir Maps] Module loaded and attached to window.GeoDir.Maps');
console.log('[GeoDir Maps] Available classes:', Object.keys(Maps));

// Also export for ES6 module usage
export default Maps;
export {
	MapProvider,
	GoogleMapProvider,
	OSMMapProvider,
	MapFactory,
	Geocoding,
	CountryRules,
	AddressField
};

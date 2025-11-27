/**
 * MapFactory - Factory for creating map provider instances
 *
 * Automatically detects which map API is available and creates the appropriate provider.
 */
import { GoogleMapProvider } from './GoogleMapProvider.js';
import { OSMMapProvider } from './OSMMapProvider.js';

export class MapFactory {
	/**
	 * Create a map provider instance
	 *
	 * @param {string} elementId - The DOM element ID for the map
	 * @param {Object} options - Map configuration options
	 * @param {string} options.preferredProvider - 'google', 'osm', or 'auto' (default)
	 * @returns {MapProvider|null} Map provider instance or null if no provider available
	 */
	static create(elementId, options = {}) {
		console.log('[MapFactory] Creating map provider for element:', elementId);
		console.log('[MapFactory] Options:', options);

		const provider = options.preferredProvider || MapFactory.detectProvider();
		console.log('[MapFactory] Detected/preferred provider:', provider);
		console.log('[MapFactory] Google available?', MapFactory.isGoogleMapsAvailable());
		console.log('[MapFactory] OSM available?', MapFactory.isOSMAvailable());

		switch (provider) {
			case 'google':
				if (MapFactory.isGoogleMapsAvailable()) {
					console.log('[MapFactory] Creating GoogleMapProvider');
					return new GoogleMapProvider(elementId, options);
				}
				console.warn('[MapFactory] Google Maps not available, falling back to OSM');
				if (MapFactory.isOSMAvailable()) {
					console.log('[MapFactory] Creating OSMMapProvider (fallback)');
					return new OSMMapProvider(elementId, options);
				}
				break;

			case 'osm':
				if (MapFactory.isOSMAvailable()) {
					console.log('[MapFactory] Creating OSMMapProvider');
					return new OSMMapProvider(elementId, options);
				}
				console.warn('[MapFactory] OSM not available, falling back to Google Maps');
				if (MapFactory.isGoogleMapsAvailable()) {
					console.log('[MapFactory] Creating GoogleMapProvider (fallback)');
					return new GoogleMapProvider(elementId, options);
				}
				break;

			case 'auto':
			default:
				// Try Google first, then OSM
				if (MapFactory.isGoogleMapsAvailable()) {
					console.log('[MapFactory] Creating GoogleMapProvider (auto)');
					return new GoogleMapProvider(elementId, options);
				}
				if (MapFactory.isOSMAvailable()) {
					console.log('[MapFactory] Creating OSMMapProvider (auto)');
					return new OSMMapProvider(elementId, options);
				}
				break;
		}

		console.error('[MapFactory] No map provider available!');
		return null;
	}

	/**
	 * Detect which map provider is available
	 *
	 * @returns {string} 'google', 'osm', or 'none'
	 */
	static detectProvider() {
		console.log('[MapFactory] Detecting provider... window.gdSetMap:', window.gdSetMap);

		// Check global gdSetMap setting first
		if (window.gdSetMap) {
			if ((window.gdSetMap === 'google' || window.gdSetMap === 'auto') && MapFactory.isGoogleMapsAvailable()) {
				console.log('[MapFactory] Using Google Maps (from gdSetMap)');
				return 'google';
			}
			if ((window.gdSetMap === 'osm' || window.gdSetMap === 'auto') && MapFactory.isOSMAvailable()) {
				console.log('[MapFactory] Using OSM (from gdSetMap)');
				return 'osm';
			}
		}

		// Auto-detect based on what's loaded
		if (MapFactory.isGoogleMapsAvailable()) {
			console.log('[MapFactory] Auto-detected Google Maps');
			return 'google';
		}
		if (MapFactory.isOSMAvailable()) {
			console.log('[MapFactory] Auto-detected OSM');
			return 'osm';
		}

		console.log('[MapFactory] No provider detected');
		return 'none';
	}

	/**
	 * Check if Google Maps is available
	 *
	 * @returns {boolean}
	 */
	static isGoogleMapsAvailable() {
		return !!(window.google && typeof google.maps !== 'undefined');
	}

	/**
	 * Check if OSM/Leaflet is available
	 *
	 * @returns {boolean}
	 */
	static isOSMAvailable() {
		return !!(window.L && typeof L.version !== 'undefined');
	}

	/**
	 * Get the name of the currently available provider
	 *
	 * @returns {string} 'google', 'osm', or 'none'
	 */
	static getAvailableProvider() {
		return MapFactory.detectProvider();
	}
}

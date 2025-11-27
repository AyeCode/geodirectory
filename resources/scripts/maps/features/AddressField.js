/**
 * AddressField - Handles add listing address field and map integration
 *
 * This replaces the ~900 lines of inline JavaScript in map-add-listing.php template.
 * Manages the interactive map for adding/editing listing addresses.
 */
import { Geocoding } from './Geocoding.js';

export class AddressField {
	/**
	 * Constructor
	 * @param {string} prefix - Field name prefix (e.g., 'address_')
	 * @param {MapProvider} mapProvider - Map provider instance
	 * @param {Object} options - Configuration options
	 */
	constructor(prefix, mapProvider, options = {}) {
		console.log('[AddressField] Constructor called with prefix:', prefix);
		console.log('[AddressField] mapProvider:', mapProvider);
		console.log('[AddressField] options:', options);

		this.prefix = prefix;
		this.mapProvider = mapProvider;
		this.options = {
			lat: options.lat || '',
			lng: options.lng || '',
			mapZoom: options.mapZoom || 12,
			defaultLocation: options.defaultLocation || {},
			isRestrict: options.isRestrict !== false,
			autoChangeFields: options.autoChangeFields !== false,
			autoChangePinMove: options.autoChangePinMove !== false,
			minZoomLevel: options.minZoomLevel || 5,
			...options
		};

		console.log('[AddressField] Merged options:', this.options);

		this.geocoding = new Geocoding(mapProvider);
		this.marker = null;
		this.userAddress = false;
		this.isGeocoding = false;

		// Set defaults if no lat/lng
		if (!this.options.lat || !this.options.lng) {
			console.log('[AddressField] No lat/lng, using defaults');
			this.options.lat = this.options.defaultLocation.latitude || '';
			this.options.lng = this.options.defaultLocation.longitude || '';
		}

		console.log('[AddressField] Final coordinates:', this.options.lat, this.options.lng);

		this.init();
	}

	/**
	 * Initialize the address field map
	 */
	async init() {
		console.log('[AddressField] init() called');

		// Initialize the map
		console.log('[AddressField] Initializing map provider...');
		const initialized = this.mapProvider.init();
		console.log('[AddressField] Map provider init result:', initialized);

		if (!initialized) {
			console.error('[AddressField] Map initialization failed');
			this.showMapError();
			return;
		}

		// Create the draggable marker
		console.log('[AddressField] Creating marker...');
		this.createMarker();

		// Set up event listeners
		console.log('[AddressField] Setting up listeners...');
		this.setupAddressListeners();
		this.setupMapListeners();
		this.setupSetAddressButton();

		// Hide loading indicator
		console.log('[AddressField] Hiding loading indicator');
		this.hideLoading();

		console.log('[AddressField] Initialization complete!');

		// Set min zoom
		if (this.options.minZoomLevel > 0) {
			this.mapProvider.addEventListener('zoom_changed', () => {
				if (this.mapProvider.getZoom() < this.options.minZoomLevel) {
					this.mapProvider.setZoom(this.options.minZoomLevel);
				}
			});
		}
	}

	/**
	 * Create the draggable marker
	 */
	createMarker() {
		const lat = parseFloat(this.options.lat) || 0;
		const lng = parseFloat(this.options.lng) || 0;

		// Get marker icon from settings
		const icon = this.options.markerIcon || '';
		const iconSize = this.options.markerSize || { w: 20, h: 34 };

		this.marker = this.mapProvider.createMarker({
			lat,
			lng,
			id: 'baseMarker',
			icon,
			draggable: true,
			w: iconSize.w,
			h: iconSize.h
		});

		// Set up marker drag listeners
		this.setupMarkerListeners();
	}

	/**
	 * Set up marker event listeners
	 */
	setupMarkerListeners() {
		if (!this.marker) return;

		// Marker drag events
		this.mapProvider.addMarkerListener(this.marker, 'drag', () => {
			this.updateMarkerPosition();
		});

		this.mapProvider.addMarkerListener(this.marker, 'dragend', () => {
			this.mapProvider.panTo(
				this.getMarkerLat(),
				this.getMarkerLng()
			);

			if (this.options.autoChangePinMove) {
				this.reverseGeocodeMarkerPosition();
			}

			this.updateMarkerPosition();
		});
	}

	/**
	 * Set up map event listeners
	 */
	setupMapListeners() {
		// Map drag events
		this.mapProvider.addEventListener('dragend', () => {
			this.centerMarker();

			if (this.options.autoChangePinMove) {
				this.reverseGeocodeMarkerPosition();
			}

			this.updateMarkerPosition();
		});

		// Zoom change events
		this.mapProvider.addEventListener('zoom_changed', () => {
			this.updateMapZoom();
		});
	}

	/**
	 * Set up address field listeners
	 */
	setupAddressListeners() {
		const streetField = document.getElementById(`${this.prefix}street`);

		if (streetField) {
			// Track when user types in address field
			streetField.addEventListener('keypress', () => {
				this.userAddress = true;
			});
		}
	}

	/**
	 * Set up "Set Address on Map" button
	 */
	setupSetAddressButton() {
		const button = document.getElementById(`${this.prefix}set_address_button`);

		if (button) {
			button.addEventListener('click', () => {
				this.codeAddress(true);
			});
		}
	}

	/**
	 * Geocode address from fields and update map
	 * @param {boolean} setOnMap - Whether this is from "Set Address on Map" button
	 */
	async codeAddress(setOnMap = false) {
		// Build address string from fields
		const address = this.buildAddressFromFields();

		if (!address) {
			return;
		}

		// Get country ISO for better results
		const countryISO = this.getFieldValue('country', true);

		try {
			const result = await this.geocoding.geocodeAddress(address, countryISO);

			// Update marker position
			this.setMarkerPosition(result.lat, result.lng);
			this.mapProvider.setCenter(result.lat, result.lng);
			this.updateMarkerPosition();

			// Reverse geocode to get full address
			await this.reverseGeocodeMarkerPosition();

		} catch (error) {
			console.error('Geocoding failed:', error);
			alert(this.options.txt_geocode_error || 'Geocode was not successful');
		}
	}

	/**
	 * Reverse geocode marker position and update fields
	 */
	async reverseGeocodeMarkerPosition() {
		if (this.isGeocoding) return;
		this.isGeocoding = true;

		try {
			const lat = this.getMarkerLat();
			const lng = this.getMarkerLng();

			const result = await this.geocoding.reverseGeocode(lat, lng);

			// Check map restrictions
			if (this.options.isRestrict) {
				const defaultCity = (this.options.defaultLocation.city || '').toLowerCase();
				const geocodedCity = (result.address.city || '').toLowerCase();

				if (geocodedCity !== defaultCity) {
					alert(this.options.txt_city_restrict || `Please choose any address of the (${defaultCity}) city only.`);
					// Reset to default location
					this.setMarkerPosition(
						this.options.defaultLocation.latitude,
						this.options.defaultLocation.longitude
					);
					this.mapProvider.setCenter(
						this.options.defaultLocation.latitude,
						this.options.defaultLocation.longitude
					);
					this.updateMarkerPosition();
					this.isGeocoding = false;
					return;
				}
			}

			// Update address fields
			this.updateAddressFields(result.address);

		} catch (error) {
			console.error('Reverse geocoding failed:', error);
		} finally {
			this.isGeocoding = false;
		}
	}

	/**
	 * Update address fields from geocoded result
	 */
	updateAddressFields(address) {
		// Update street (only if user hasn't typed or field is empty)
		if (!this.userAddress || !this.getFieldValue('street')) {
			this.setFieldValue('street', address.street);
		}

		// Update street2
		if (address.street2) {
			if (!this.userAddress || !this.getFieldValue('street2')) {
				this.setFieldValue('street2', address.street2);
			}
		}

		// Update zip
		this.setFieldValue('zip', address.zip);

		// Update location fields if auto-change is enabled
		if (this.options.autoChangeFields) {
			if (address.country) {
				this.setCountryValue(address.country, address.countryISO);
			}
			if (address.region) {
				this.setFieldValue('region', address.region);
			}
			if (address.city) {
				this.setFieldValue('city', address.city);
			}
		}
	}

	/**
	 * Build address string from field values
	 */
	buildAddressFromFields() {
		const fields = {
			street: this.getFieldValue('street'),
			city: this.getFieldValue('city'),
			region: this.getFieldValue('region'),
			country: this.getFieldValue('country'),
			countryISO: this.getFieldValue('country', true),
			zip: this.getFieldValue('zip')
		};

		return Geocoding.buildAddressString(fields, this.options.isRestrict);
	}

	/**
	 * Get field value
	 * @param {string} fieldName - Field name without prefix
	 * @param {boolean} getData - Get data attribute instead of value
	 */
	getFieldValue(fieldName, getData = false) {
		const field = document.getElementById(`${this.prefix}${fieldName}`);
		if (!field) return '';

		if (getData) {
			// For country, get ISO code from selected option
			if (fieldName === 'country') {
				const option = field.options[field.selectedIndex];
				return option ? (option.dataset.country_code || '') : '';
			}
			return field.dataset.value || '';
		}

		return field.value || '';
	}

	/**
	 * Set field value
	 */
	setFieldValue(fieldName, value) {
		const field = document.getElementById(`${this.prefix}${fieldName}`);
		if (!field || !value) return;

		// For select2 fields
		if (jQuery(field).hasClass('select2-hidden-accessible')) {
			jQuery(field).val(value).trigger('change.select2');
		} else {
			field.value = value;
		}

		// Trigger blur for validation
		jQuery(field).trigger('blur');
	}

	/**
	 * Set country select value (special handling for country dropdown)
	 */
	setCountryValue(country, countryISO) {
		const field = document.getElementById(`${this.prefix}country`);
		if (!field) return;

		// Try to find by ISO code first
		let option = field.querySelector(`option[data-country_code="${countryISO}"]`);

		if (!option) {
			// Try to find by country name
			option = Array.from(field.options).find(opt => opt.value === country);
		}

		if (option) {
			field.value = option.value;
			if (jQuery(field).hasClass('select2-hidden-accessible')) {
				jQuery(field).trigger('change.select2');
			}
		}
	}

	/**
	 * Update marker position in hidden fields
	 */
	updateMarkerPosition() {
		const latField = document.querySelector(`[name="latitude"]`);
		const lngField = document.querySelector(`[name="longitude"]`);

		if (latField) {
			latField.value = this.getMarkerLat();
			jQuery(latField).trigger('change');
		}

		if (lngField) {
			lngField.value = this.getMarkerLng();
			jQuery(lngField).trigger('change');
		}
	}

	/**
	 * Update map zoom in hidden field
	 */
	updateMapZoom() {
		const zoomField = document.getElementById(`${this.prefix}mapzoom`);
		if (zoomField) {
			zoomField.value = this.mapProvider.getZoom();
		}
	}

	/**
	 * Center marker on map center
	 */
	centerMarker() {
		const center = this.mapProvider.getCenter();
		this.setMarkerPosition(center.lat, center.lng);
	}

	/**
	 * Get marker latitude
	 */
	getMarkerLat() {
		if (!this.marker) return 0;

		if (this.mapProvider.getType() === 'google') {
			const pos = this.marker.getPosition();
			return typeof pos.lat === 'function' ? pos.lat() : pos.lat;
		} else {
			const latlng = this.marker.getLatLng();
			return latlng.lat;
		}
	}

	/**
	 * Get marker longitude
	 */
	getMarkerLng() {
		if (!this.marker) return 0;

		if (this.mapProvider.getType() === 'google') {
			const pos = this.marker.getPosition();
			return typeof pos.lng === 'function' ? pos.lng() : pos.lng;
		} else {
			const latlng = this.marker.getLatLng();
			return latlng.lng;
		}
	}

	/**
	 * Set marker position
	 */
	setMarkerPosition(lat, lng) {
		if (!this.marker) return;

		if (this.mapProvider.getType() === 'google') {
			this.marker.setPosition(new google.maps.LatLng(lat, lng));
		} else {
			this.marker.setLatLng(new L.LatLng(lat, lng));
		}
	}

	/**
	 * Hide loading indicator
	 */
	hideLoading() {
		const loadingDiv = document.getElementById(`${this.prefix}map_loading_div`);
		if (loadingDiv) {
			loadingDiv.style.display = 'none';
		}
	}

	/**
	 * Show map error
	 */
	showMapError() {
		const loadingDiv = document.getElementById(`${this.prefix}map_loading_div`);
		const notFoundDiv = document.getElementById(`${this.prefix}map_nofound`);
		const notLoadedDiv = document.getElementById(`${this.prefix}map_notloaded`);

		if (loadingDiv) loadingDiv.style.display = 'none';
		if (notFoundDiv) notFoundDiv.style.display = 'none';
		if (notLoadedDiv) notLoadedDiv.style.display = 'block';
	}
}

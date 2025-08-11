/**
 * GeoDirectory Global Map Manager
 *
 * A singleton object to manage all map instances on the page,
 * designed to work reliably with the custom goMap library.
 *
 * @version 5.0.0 (Manager Pattern - Full Implementation)
 */
window.GeoDirectoryMapManager = {
	maps: {}, // Stores data for each map, keyed by containerId

	// ########################################################################
	// PUBLIC API
	// ########################################################################

	/**
	 * Public method to initialize a new map. This is the main entry point.
	 * @param {string} containerId - The ID of the map's container div.
	 * @param {object} mapData - The localized data from PHP.
	 * @param {object} [callbacks] - Optional callbacks for JS framework integration.
	 */
	initMap: function(containerId, mapData, callbacks) { // ✨ Added callbacks parameter
		this.maps[containerId] = {
			mapData: mapData,
			prefix: mapData.prefix,
			callbacks: callbacks || {}, // ✨ Store callbacks
			user_address: false,
			baseMarker: null,
			mapObject: null,
			geocoder: null,
			locality: '',
			postal_town: '',
			getCountryISO: ''
		};

		const self = this;
		if (mapData.lazy_load_map) {
			jQuery("#" + containerId).geodirLoadMap({
				loadJS: true,
				forceLoad: mapData.manual_map,
				callback: function() { self._initializeMap(containerId); }
			});
		} else {
			this._initializeMap(containerId);
		}

		jQuery('#' + mapData.prefix + 'street').on("keypress", function() {
			self.maps[containerId].user_address = true;
		});
	},

	/**
	 * Public method to trigger geocoding from the "Set Address" button.
	 * @param {string} containerId The ID of the map to geocode.
	 * @param {boolean} set_on_map The original flag from the button click.
	 */
	codeAddress: function(containerId, set_on_map) {
		const self = this;
		this._syncContextAndRun(containerId, function() {
			self._codeAddressInternal(containerId, set_on_map);
		});
	},

	// ########################################################################
	// INTERNAL METHODS
	// ########################################################################

	/**
	 * Internal method to set up the map after scripts are loaded.
	 * @param {string} containerId - The ID of the map to initialize.
	 */
	_initializeMap: function(containerId) {
		const $ = jQuery;
		const instanceData = this.maps[containerId];
		if (!instanceData) return;

		const mapOptions = { latitude: instanceData.mapData.lat_lng_blank ? null : instanceData.mapData.lat, longitude: instanceData.mapData.lat_lng_blank ? null : instanceData.mapData.lng, zoom: instanceData.mapData.mapzoom, maptype: 'ROADMAP', streetViewControl: true, scrollwheel: !instanceData.mapData.mouse_scroll };

		$("#" + containerId).goMap(mapOptions);

		const goMapInstance = $("#" + containerId).data('goMap');
		if (!goMapInstance) { console.error(`[Map Manager] Failed to retrieve goMap instance for #${containerId}`); return; }

		instanceData.mapObject = goMapInstance.getMap();
		$.goMap = goMapInstance;

		if (!instanceData.mapObject) { console.error(`[Map Manager] Failed to get native map object for #${containerId}`); return; }

		instanceData.geocoder = (window.gdMaps === 'google') ? new google.maps.Geocoder() : [];

		let icon = instanceData.mapData.marker_icon;
		let iconW = parseFloat(instanceData.mapData.icon_size_w);
		let iconH = parseFloat(instanceData.mapData.icon_size_h);
		if (instanceData.mapData.resize_marker && window.geodir_params) {
			const iconMW = window.geodir_params.marker_max_width ? parseFloat(window.geodir_params.marker_max_width) : 0;
			const iconMH = window.geodir_params.marker_max_height ? parseFloat(window.geodir_params.marker_max_height) : 0;
			if (window.geodir_params.resize_marker && (iconW < iconMW || iconH < iconMH) && icon.substr(icon.lastIndexOf(".") + 1).toLowerCase() === "svg") { iconW *= 10; iconH *= 10; }
			if (window.geodir_params.resize_marker && iconW > 5 && iconH > 5 && (iconMW > 5 && iconW > iconMW || iconMH > 5 && iconH > iconMH)) {
				let resizeW = iconW, resizeH = iconH, resize = false;
				if (iconMH > 5 && resizeH > iconMH) { const _resizeH = iconMH; const _resizeW = Math.round(_resizeH * resizeW / resizeH * 10) / 10; resizeW = _resizeW; resizeH = _resizeH; resize = true; }
				if (iconMW > 5 && resizeW > iconMW) { const _resizeW = iconMW; const _resizeH = Math.round(_resizeW * resizeH / resizeW * 10) / 10; resizeW = _resizeW; resizeH = _resizeH; resize = true; }
				if (resize && resizeW > 5 && resizeH > 5) {
					if (window.gdMaps === 'google') { icon = { url: icon, scaledSize: new google.maps.Size(resizeW, resizeH), origin: new google.maps.Point(0, 0), anchor: new google.maps.Point(Math.round(resizeW / 2), resizeH) }; } else { iconW = resizeW; iconH = resizeH; }
				}
			}
		}

		instanceData.baseMarker = $.goMap.createMarker({ latitude: mapOptions.latitude, longitude: mapOptions.longitude, id: 'baseMarker_' + containerId, icon: icon, draggable: true, addToMap: true, w: iconW, h: iconH });

		$('#' + containerId + '_loading_div').hide();
		this._bindMapEvents(containerId);
	},

	_bindMapEvents: function(containerId) {
		const instanceData = this.maps[containerId];
		if (!instanceData || !instanceData.mapObject || !instanceData.baseMarker) return;

		const self = this;

		if (window.gdMaps === 'google') {
			google.maps.event.addListener(instanceData.baseMarker, 'drag', function() {
				self._updateMarkerPosition(containerId);
			});
			google.maps.event.addListener(instanceData.baseMarker, 'dragend', function() {
				self._syncContextAndRun(containerId, function() {
					self._centerMap(containerId);
					if (instanceData.mapData.auto_change_address_fields_pin_move) self._geocodePosition(containerId);
					self._updateMarkerPosition(containerId);
				});
			});
			google.maps.event.addListener(instanceData.mapObject, 'dragend', function() {
				self._syncContextAndRun(containerId, function() {
					self._centerMarker(containerId);
					if (instanceData.mapData.auto_change_address_fields_pin_move) self._geocodePosition(containerId);
					self._updateMarkerPosition(containerId);
				});
			});
			google.maps.event.addListener(instanceData.mapObject, 'zoom_changed', function() {
				self._updateMapZoom(containerId);
			});
		}
	},

	_syncContextAndRun: function(containerId, callback) {
		const goMapInstance = jQuery("#" + containerId).data('goMap');
		if (goMapInstance) {
			jQuery.goMap = goMapInstance;
			callback();
		}
	},

	_codeAddressInternal: function(containerId, set_on_map) {
		const instanceData = this.maps[containerId];
		if (!instanceData || !window.gdMaps) return;
		const $ = jQuery;
		const address = $('#' + instanceData.prefix + 'street').val() || '';
		const zip = $('#' + instanceData.prefix + 'zip').val() || '';
		const city = $('#' + instanceData.prefix + 'city').val() || instanceData.mapData.city;
		const region = $('#' + instanceData.prefix + 'region').val() || instanceData.mapData.region;
		const country = $('#' + instanceData.prefix + 'country').val() || instanceData.mapData.country;
		const country_selected = $('#' + instanceData.prefix + 'country').find('option:selected');
		let ISO2 = country_selected.data('country_code') || $('#' + instanceData.prefix + 'country').data('country_code') || instanceData.mapData.default_iso2;
		if (ISO2 === '--') ISO2 = '';
		let fullAddress;
		if (set_on_map && instanceData.mapData.is_map_restrict) { fullAddress = (address && zip) ? address + ',' + zip : address; } else {
			let tempAddress = address; if (tempAddress === city || tempAddress === region || tempAddress === country || tempAddress === zip) { tempAddress = ''; }
			if (instanceData.mapData.is_admin && (!city.trim() || !region.trim())) { tempAddress = ''; }
			let addressParts = [tempAddress, city, region, country]; if (ISO2 === 'GB') { addressParts = [tempAddress, city, country]; }
			fullAddress = addressParts.filter(Boolean).join(','); if (zip) { fullAddress += ',' + zip; }
		}
		fullAddress = fullAddress.replace(/,+/g, ',').replace(/(^,)|(,$)/g, "");
		if (fullAddress) {
			if (window.gdMaps === 'osm') {
				if (zip) { const searchZip = "," + zip; const nAddress = fullAddress.toLowerCase().lastIndexOf(searchZip.toLowerCase()); if (nAddress > -1) { fullAddress = fullAddress.slice(0, nAddress) + fullAddress.slice(nAddress).replace(new RegExp(searchZip, 'i'), ""); } }
				this._geocodePositionOSM(containerId, null, fullAddress, ISO2, true);
			} else {
				instanceData.geocoder.geocode({ 'address': fullAddress, 'country': ISO2 }, (results, status) => {
					if (status === google.maps.GeocoderStatus.OK) {
						instanceData.baseMarker.setPosition(results[0].geometry.location);
						instanceData.mapObject.setCenter(results[0].geometry.location);
						this._updateMarkerPosition(containerId);
						this._geocodePosition(containerId, { 'address': fullAddress, 'country': ISO2 });
					} else { alert(instanceData.mapData.i18n.geocode_fail + ' ' + status); }
				});
			}
		}
	},

	_updateMarkerPosition: function(containerId) {
		const d = this.maps[containerId];
		const p = d.baseMarker.getPosition();
		const coords = { lat: p.lat(), lng: p.lng() };

		jQuery('#' + d.prefix + 'latitude').val(p.lat()).trigger('change');
		jQuery('#' + d.prefix + 'longitude').val(p.lng()).trigger('change');

		// ✨ Fire the callback for AlpineJS
		if (d.callbacks.onMarkerUpdate) {
			d.callbacks.onMarkerUpdate(coords);
		}
	},
	_updateMarkerPositionOSM: function(containerId) {
		const d = this.maps[containerId]; const p = d.baseMarker.getLatLng(); jQuery('#' + d.prefix + 'latitude').val(p.lat).trigger('change'); jQuery('#' + d.prefix + 'longitude').val(p.lng).trigger('change');
	},
	_updateMapZoom: function(containerId) {
		const d = this.maps[containerId]; const z = d.mapObject.getZoom(); jQuery('#' + d.prefix + 'mapzoom').val(z); if (z < d.mapData.minZoomLevel) { d.mapObject.setZoom(d.mapData.minZoomLevel); }
	},
	_centerMap: function(containerId, latlng) {
		const d = this.maps[containerId]; if (window.gdMaps === 'google') { d.mapObject.panTo(d.baseMarker.getPosition()); } else if (window.gdMaps === 'osm') { latlng = latlng || d.baseMarker.getLatLng(); d.mapObject.panTo(latlng); }
	},
	_centerMarker: function(containerId) {
		const d = this.maps[containerId]; const c = d.mapObject.getCenter(); if (window.gdMaps === 'google') { d.baseMarker.setPosition(c); } else if (window.gdMaps === 'osm') { d.baseMarker.setLatLng(c); }
	},
	_updateMarkerAddress: function(containerId, getAddress, getZip, getCity, getState, getCountry, getAddress2, baseCountry) {
		const d = this.maps[containerId]; const $ = jQuery;
		if (d.user_address === false || $('#' + d.prefix + 'street').val() === '') { $("#" + d.prefix + 'street').val(getAddress).trigger("blur"); }
		if (getAddress2 && (d.user_address === false || $('#' + d.prefix + 'street2').val() === '')) { $("#" + d.prefix + 'street2').val(getAddress2); }
		const old_zip = $("#" + d.prefix + 'zip').val(); const old_city = $("#" + d.prefix + 'city').val(); let updateZip = true;
		if (!getZip && old_zip && old_city && old_city === getCity) { updateZip = false; } if (updateZip) { $("#" + d.prefix + 'zip').val(getZip); }
		if (d.mapData.auto_change_map_fields) {
			if (getCountry) { let setCountry = !baseCountry && $('#' + d.prefix + 'country option[data-country_code="' + d.getCountryISO + '"]').val(); if (!setCountry) { setCountry = getCountry; } else { getCountry = setCountry; } $("#" + d.prefix + 'country').val(setCountry).trigger('change.select2'); }
			if (getState && $("input#" + d.prefix + 'region').length) { $("#" + d.prefix + 'region').val(getState); }
			if (getCity && $("input#" + d.prefix + 'city').length) { $("#" + d.prefix + 'city').val(getCity); }
		}
		$(document).trigger('geodir_after_update_marker_address', { prefix: d.prefix });
	},
	_geocodePosition: function(containerId, address) {
		const d = this.maps[containerId]; let doGeoCode; if (address && (d.locality || d.postal_town) && address.country !== 'TR' && address.country !== 'SG') { doGeoCode = address; } else { doGeoCode = { latLng: d.baseMarker.getPosition() }; } d.geocoder.geocode(doGeoCode, (r, s) => this._geocodeResponse(containerId, r, s));
	},
	_geocodeResponse: function(containerId, responses) {
		const d = this.maps[containerId];
		if (!responses || responses.length === 0) { jQuery(document).trigger('geodir_add_listing_geocode_response_fail'); this._updateMarkerAddress(containerId, d.mapData.i18n.cannot_determine_address, '', '', '', '', '', ''); return; }
		let getAddress = '', getAddress2 = '', getZip = '', getCity = '', getState = '', getCountry = '', baseCountry = '';
		d.getCountryISO = ''; let street_number = {}, route = {}, premise = {}, establishment = {}; let administrative_area_level_1 = {}, administrative_area_level_2 = {}, administrative_area_level_3 = {}; let sublocality_level_1 = {}, sublocality = {}, country = {}, postal_code = {}, postal_code_prefix = {};
		d.locality = {}; d.postal_town = {};
		responses.forEach(response => { if (response.types[0] === "locality") { response.address_components.forEach(addr => { if (addr.types[0] === 'administrative_area_level_1') administrative_area_level_1 = addr; if (addr.types[0] === 'administrative_area_level_2') administrative_area_level_2 = addr; if (addr.types[0] === 'administrative_area_level_3') administrative_area_level_3 = addr; if (addr.types[0] === 'sublocality_level_1') sublocality_level_1 = addr; if (addr.types[0] === 'postal_town') d.postal_town = addr; if (addr.types[0] === 'locality') d.locality = addr; if (addr.types[0] === 'sublocality' || (addr.types[1] && addr.types[1] === 'sublocality')) sublocality = addr; if (addr.types[0] === 'premise') premise = addr; if (addr.types[0] === 'establishment') establishment = addr; }); } });
		responses[0].address_components.forEach(addr => { if (addr.types[0] === 'street_number') street_number = addr; if (addr.types[0] === 'route') route = addr; if (addr.types[0] === 'premise' && !premise.long_name) premise = addr; if (addr.types[0] === 'administrative_area_level_1' && !administrative_area_level_1.long_name) administrative_area_level_1 = addr; if (addr.types[0] === 'administrative_area_level_2' && !administrative_area_level_2.long_name) administrative_area_level_2 = addr; if (addr.types[0] === 'administrative_area_level_3' && !administrative_area_level_3.long_name) administrative_area_level_3 = addr; if (addr.types[0] === 'sublocality_level_1' && !sublocality_level_1.long_name) sublocality_level_1 = addr; if (addr.types[0] === 'postal_town' && !d.postal_town.long_name) d.postal_town = addr; if (addr.types[0] === 'locality' && !d.locality.long_name) d.locality = addr; if ((addr.types[0] === 'sublocality' || (addr.types[1] && addr.types[1] === 'sublocality')) && !sublocality.long_name) sublocality = addr; if (addr.types[0] === 'country') country = addr; if (addr.types[0] === 'postal_code') postal_code = addr; if (addr.types[0] === 'postal_code_prefix') postal_code_prefix = addr; if (addr.types[0] === 'establishment' && !establishment.long_name) establishment = addr; });
		const formatted_address = responses[0].formatted_address || ''; const address_array = formatted_address.split(",", 2);
		if (address_array.length > 1) { const name_checks = ['long_name', 'short_name']; const component_checks = [street_number, premise]; let found = false; for (let c = 0; c < component_checks.length && !found; c++) { for (let n = 0; n < name_checks.length && !found; n++) { const name = component_checks[c][name_checks[n]]; if (name) { if (name.toLowerCase() === address_array[0].toLowerCase().trim()) { getAddress = name + ', ' + address_array[1]; found = true; } else if (name.toLowerCase() === address_array[1].toLowerCase().trim()) { getAddress = address_array[0] + ', ' + name; found = true; } } } } if (!found) getAddress = 'none'; }
		if (getAddress === 'none' && establishment.long_name && address_array[1]) { getAddress = address_array[1]; getAddress2 = address_array[0]; } else if (getAddress === 'none') { getAddress = address_array[0] || ''; }
		if (premise.long_name && premise.long_name !== getAddress) { getAddress2 = premise.long_name; } if (getAddress === '') { getAddress = (street_number.long_name || '') + ' ' + (route.long_name || ''); }
		getZip = postal_code.long_name || postal_code_prefix.long_name || ''; if (!getState && !getZip) { const zipFound = this._geodirGetAnyZip(responses); if (zipFound) getZip = zipFound; }
		getCountry = country.long_name || ''; d.getCountryISO = country.short_name || ''; let rr = d.getCountryISO; if (rr === "BB" && !d.locality && sublocality) d.locality = sublocality;
		if (jQuery.inArray(rr, d.mapData.geocode_region_level) !== -1) { getState = administrative_area_level_2.long_name || administrative_area_level_1.long_name || ''; if (rr === "GB") { if (getState === "Stoke-on-Trent") getState = 'Staffordshire'; if (getState) getState = getState.replace(" Council", ""); } } else { getState = administrative_area_level_1.long_name || administrative_area_level_2.long_name || ''; }
		if (rr === 'IM') getState = "Isle of Man"; else if (rr === 'SG') getState = "Singapore"; else if (rr === 'VA') getState = "Vatican City State"; else if (rr === 'GR' && !getState && administrative_area_level_3.long_name) getState = administrative_area_level_3.long_name; if (getState === 'Brussels Hoofdstedelijk Gewest') getState = 'Brussels';
		const city_country_exceptions = ["IE", "TR", "FR"];
		if (jQuery.inArray(rr, city_country_exceptions) !== -1) {
			if (rr === "IE") { if (administrative_area_level_2.long_name && administrative_area_level_2.long_name.indexOf(" City") >= 0) getCity = administrative_area_level_2.long_name; else getCity = d.locality.long_name || d.postal_town.long_name || sublocality_level_1.long_name || administrative_area_level_3.long_name || ''; }
			else if (rr === "TR") { getCity = d.locality.long_name || d.postal_town.long_name || sublocality_level_1.long_name || administrative_area_level_3.long_name || administrative_area_level_1.long_name || ''; }
			else if (rr === "FR") { if (administrative_area_level_2.long_name === 'Paris') getCity = administrative_area_level_2.long_name; else getCity = d.locality.long_name || d.postal_town.long_name || sublocality_level_1.long_name || administrative_area_level_3.long_name || administrative_area_level_1.long_name || ''; }
		} else { getCity = d.locality.long_name || d.postal_town.long_name || sublocality_level_1.long_name || administrative_area_level_3.long_name || ''; }
		if (rr === "BB" && getState && !getCity) getCity = getState;
		if (d.mapData.split_uk && rr === 'GB' && administrative_area_level_1.long_name && jQuery.inArray(administrative_area_level_1.long_name, ["England", "Northern Ireland", "Scotland", "Wales"]) !== -1) { baseCountry = getCountry; getCountry = administrative_area_level_1.long_name; }
		if (getCity === 'Vatican City') { d.getCountryISO = rr = 'VA'; getState = "Vatican City State"; getCountry = "Holy See"; }
		if (d.mapData.is_map_restrict && getCity.toLowerCase() !== d.mapData.city.toLowerCase()) {
			alert(d.mapData.i18n.choose_address_in_city); const defaultLatLng = new google.maps.LatLng(d.mapData.default_lat, d.mapData.default_lng);
			d.mapObject.setCenter(defaultLatLng); d.baseMarker.setPosition(defaultLatLng); this._updateMarkerPosition(containerId); setTimeout(function() { jQuery('#' + d.prefix + 'street, #' + d.prefix + 'zip').val(''); }, 100);
		} else { this._updateMarkerAddress(containerId, getAddress, getZip, getCity, getState, getCountry, getAddress2, baseCountry); }
	},
	_geodirGetAnyZip: function(responses) { for (let j = 0; j < responses.length; j++) { for (let i = 0; i < responses[j].address_components.length; i++) { const addr = responses[j].address_components[i]; if (addr.types[0] === 'postal_code' && addr.short_name) { return addr.short_name; } } } return ''; },
};

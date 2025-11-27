/**
 * CountryRules - Country-specific address parsing rules
 *
 * Contains all the logic for parsing geocode results differently based on country.
 * This is extracted from the massive inline JavaScript in the original template.
 */
export class CountryRules {
	/**
	 * Countries that use administrative_area_level_2 for region instead of level_1
	 */
	static regionLevel2Countries = ['GB', 'ES'];

	/**
	 * Countries with special city parsing rules
	 */
	static citySpecialCases = ['IE', 'TR', 'FR'];

	/**
	 * Region overrides for countries without regions
	 */
	static regionOverrides = {
		'IM': 'Isle of Man',
		'SG': 'Singapore',
		'VA': 'Vatican City State'
	};

	/**
	 * Region name fixes
	 */
	static regionFixes = {
		'Brussels Hoofdstedelijk Gewest': 'Brussels'
	};

	/**
	 * GB-specific region fixes
	 */
	static gbRegionFixes = {
		'Stoke-on-Trent': 'Staffordshire'
	};

	/**
	 * Parse address components from geocode result
	 *
	 * @param {Array} responses - Geocode responses (Google or normalized OSM)
	 * @param {Object} options - Parsing options
	 * @param {string} options.mapType - 'google' or 'osm'
	 * @returns {Object} Parsed address components
	 */
	static parseAddress(responses, options = {}) {
		if (!responses || responses.length === 0) {
			return null;
		}

		const mapType = options.mapType || 'google';

		// For OSM, the response is already normalized
		if (mapType === 'osm') {
			return CountryRules.parseOSMAddress(responses[0]);
		}

		// For Google Maps
		return CountryRules.parseGoogleAddress(responses);
	}

	/**
	 * Parse OSM address (already normalized by OSMMapProvider)
	 */
	static parseOSMAddress(result) {
		return {
			street: result.display_address || '',
			street2: CountryRules.getStreet2FromOSM(result),
			zip: result.postcode || '',
			city: result.city || '',
			region: result.state || '',
			country: result.country || '',
			countryISO: result.country_code || '',
			baseCountry: result.gb_country ? result.country : ''
		};
	}

	/**
	 * Get street2 from OSM address (building, hotel, etc.)
	 */
	static getStreet2FromOSM(result) {
		const address = result.address || {};

		if (address.building) return address.building;
		if (address.department_store) return address.department_store;
		if (address.hotel) return address.hotel;

		return '';
	}

	/**
	 * Parse Google Maps address
	 */
	static parseGoogleAddress(responses) {
		const parsed = {
			street: '',
			street2: '',
			zip: '',
			city: '',
			region: '',
			country: '',
			countryISO: '',
			baseCountry: ''
		};

		// Extract address components from first result
		const components = CountryRules.extractComponents(responses);

		// Get country first as it affects other parsing
		parsed.country = components.country.long_name || '';
		parsed.countryISO = components.country.short_name || '';

		// Parse street address
		const streetParts = CountryRules.parseStreet(responses[0], components);
		parsed.street = streetParts.street;
		parsed.street2 = streetParts.street2;

		// Parse region
		parsed.region = CountryRules.parseRegion(components, parsed.countryISO);

		// Parse city
		parsed.city = CountryRules.parseCity(components, parsed.countryISO);

		// Parse zip
		parsed.zip = CountryRules.parseZip(responses, components, parsed.region);

		// Handle GB split
		if (parsed.countryISO === 'GB' && CountryRules.isGBSplitEnabled()) {
			const gbCountry = CountryRules.getGBCountry(components);
			if (gbCountry) {
				parsed.baseCountry = parsed.country;
				parsed.country = gbCountry;
			}
		}

		// Vatican City fix
		if (parsed.city === 'Vatican City') {
			parsed.countryISO = 'VA';
			parsed.region = 'Vatican City State';
			parsed.country = 'Holy See';
		}

		return parsed;
	}

	/**
	 * Extract all address components from responses
	 */
	static extractComponents(responses) {
		const components = {
			street_number: {},
			route: {},
			premise: {},
			establishment: {},
			administrative_area_level_1: {},
			administrative_area_level_2: {},
			administrative_area_level_3: {},
			sublocality_level_1: {},
			postal_town: {},
			locality: {},
			sublocality: {},
			country: {},
			postal_code: {},
			postal_code_prefix: {}
		};

		// Get locality info from the locality response if available
		responses.forEach(response => {
			if (response.types && response.types[0] === 'locality') {
				response.address_components.forEach(addr => {
					const types = addr.types || [];
					types.forEach(type => {
						if (components.hasOwnProperty(type) && !components[type].long_name) {
							components[type] = addr;
						}
					});
				});
			}
		});

		// Get info from first response
		if (responses[0] && responses[0].address_components) {
			responses[0].address_components.forEach(addr => {
				const types = addr.types || [];
				types.forEach(type => {
					if (components.hasOwnProperty(type) && !components[type].long_name) {
						components[type] = addr;
					}
					// Handle sublocality as secondary type
					if (type === 'sublocality' || types[1] === 'sublocality') {
						if (!components.sublocality.long_name) {
							components.sublocality = addr;
						}
					}
				});
			});
		}

		return components;
	}

	/**
	 * Parse street address
	 */
	static parseStreet(firstResult, components) {
		let street = '';
		let street2 = '';

		const formatted = firstResult.formatted_address || '';
		const addressArray = formatted.split(',', 2);

		// Try to parse from formatted address
		if (addressArray.length > 1) {
			street = CountryRules.parseStreetFromFormatted(addressArray, components);
		}

		// Fallback to components
		if (!street || street === 'none') {
			if (components.establishment.long_name && addressArray[1]) {
				street = addressArray[1];
				street2 = addressArray[0];
			} else if (street === 'none') {
				street = addressArray[0] || '';
			}
		}

		// Final fallback
		if (!street) {
			if (components.street_number.long_name) {
				street += components.street_number.long_name + ' ';
			}
			if (components.route.long_name) {
				street += components.route.long_name;
			}
		}

		// Street2 from premise if not already set
		if (!street2 && components.premise.long_name && components.premise.long_name !== street) {
			street2 = components.premise.long_name;
		}

		// Handle Japanese addresses
		if (components.country.short_name === 'JP' && formatted) {
			street = CountryRules.parseJapaneseStreet(formatted, components);
		}

		return { street: street.trim(), street2: street2.trim() };
	}

	/**
	 * Parse street from formatted address parts
	 */
	static parseStreetFromFormatted(addressArray, components) {
		const checks = [
			{ component: 'street_number', field: 'long_name' },
			{ component: 'street_number', field: 'short_name' },
			{ component: 'premise', field: 'long_name' },
			{ component: 'premise', field: 'short_name' }
		];

		for (const check of checks) {
			const value = components[check.component][check.field];
			if (value) {
				// Check against both address parts
				if (value.toLowerCase() === addressArray[0].toLowerCase().trim()) {
					return value + ', ' + addressArray[1];
				}
				if (value.toLowerCase() === addressArray[1].toLowerCase().trim()) {
					return addressArray[0] + ', ' + value;
				}
			}
		}

		return 'none';
	}

	/**
	 * Parse Japanese street address
	 */
	static parseJapaneseStreet(formatted, components) {
		const mapLang = window.mapLang || '';
		if (mapLang !== 'ja') return '';

		let address = formatted.replace(components.country.long_name + '、', '');

		if (components.postal_code.long_name) {
			address = address.replace(components.country.long_name + ' 〒' + components.postal_code.long_name, '〒' + components.postal_code.long_name);
			address = address.replace('〒' + components.postal_code.long_name + ' ', '');
		}

		let parts = [];
		if (components.locality.long_name && address.includes(components.locality.long_name)) {
			parts = address.split(components.locality.long_name);
		} else if (components.administrative_area_level_1.long_name && address.includes(components.administrative_area_level_1.long_name)) {
			parts = address.split(components.administrative_area_level_1.long_name);
		}

		return parts.length > 1 ? parts[parts.length - 1] : address;
	}

	/**
	 * Parse region/state
	 */
	static parseRegion(components, countryISO) {
		let region = '';

		// Check for country-specific overrides
		if (CountryRules.regionOverrides[countryISO]) {
			return CountryRules.regionOverrides[countryISO];
		}

		// Determine which administrative level to use
		if (CountryRules.regionLevel2Countries.includes(countryISO)) {
			region = components.administrative_area_level_2.long_name || components.administrative_area_level_1.long_name || '';
		} else {
			region = components.administrative_area_level_1.long_name || components.administrative_area_level_2.long_name || '';
		}

		// Greece special case
		if (countryISO === 'GR' && !region && components.administrative_area_level_3.long_name) {
			region = components.administrative_area_level_3.long_name;
		}

		// GB-specific fixes
		if (countryISO === 'GB') {
			if (CountryRules.gbRegionFixes[region]) {
				region = CountryRules.gbRegionFixes[region];
			}
			// Remove " Council" from region names
			region = region.replace(' Council', '');
		}

		// General region fixes
		if (CountryRules.regionFixes[region]) {
			region = CountryRules.regionFixes[region];
		}

		return region;
	}

	/**
	 * Parse city
	 */
	static parseCity(components, countryISO) {
		// Barbados fix - use locality if sublocality exists
		if (countryISO === 'BB' && !components.locality.long_name && components.sublocality.long_name) {
			components.locality = components.sublocality;
		}

		// Country-specific parsing
		if (countryISO === 'IE') {
			return CountryRules.parseCityIreland(components);
		} else if (countryISO === 'TR') {
			return CountryRules.parseCityTurkey(components);
		} else if (countryISO === 'FR') {
			return CountryRules.parseCityFrance(components);
		}

		// Default city parsing
		let city = components.locality.long_name
			|| components.postal_town.long_name
			|| components.sublocality_level_1.long_name
			|| components.administrative_area_level_3.long_name
			|| '';

		// Barbados - use state as city if no city
		if (countryISO === 'BB' && !city && components.administrative_area_level_1.long_name) {
			city = components.administrative_area_level_1.long_name;
		}

		return city;
	}

	/**
	 * Parse city for Ireland
	 */
	static parseCityIreland(components) {
		if (components.administrative_area_level_2.long_name && components.administrative_area_level_2.long_name.indexOf(' City') >= 0) {
			return components.administrative_area_level_2.long_name;
		}
		return components.locality.long_name
			|| components.postal_town.long_name
			|| components.sublocality_level_1.long_name
			|| components.administrative_area_level_3.long_name
			|| '';
	}

	/**
	 * Parse city for Turkey
	 */
	static parseCityTurkey(components) {
		return components.locality.long_name
			|| components.postal_town.long_name
			|| components.sublocality_level_1.long_name
			|| components.administrative_area_level_3.long_name
			|| components.administrative_area_level_1.long_name
			|| '';
	}

	/**
	 * Parse city for France
	 */
	static parseCityFrance(components) {
		if (components.administrative_area_level_2.long_name === 'Paris') {
			return 'Paris';
		}
		return components.locality.long_name
			|| components.postal_town.long_name
			|| components.sublocality_level_1.long_name
			|| components.administrative_area_level_3.long_name
			|| components.administrative_area_level_1.long_name
			|| '';
	}

	/**
	 * Parse zip/postal code
	 */
	static parseZip(responses, components, region) {
		let zip = components.postal_code.long_name || components.postal_code_prefix.long_name || '';

		// Try to find any zip from responses if region is missing
		if (!region && !zip) {
			zip = CountryRules.findAnyZip(responses);
		}

		return zip;
	}

	/**
	 * Find any zip code from all responses
	 */
	static findAnyZip(responses) {
		for (const response of responses) {
			if (!response.address_components) continue;

			for (const addr of response.address_components) {
				if (addr.types && addr.types[0] === 'postal_code' && addr.short_name) {
					return addr.short_name;
				}
			}
		}
		return '';
	}

	/**
	 * Get GB country (England, Scotland, Wales, Northern Ireland)
	 */
	static getGBCountry(components) {
		const gbCountries = ['England', 'Northern Ireland', 'Scotland', 'Wales'];
		const adminArea = components.administrative_area_level_1.long_name || '';

		for (const country of gbCountries) {
			if (gbCountries.includes(adminArea)) {
				return adminArea;
			}
		}

		return null;
	}

	/**
	 * Check if GB split is enabled (from WordPress filter)
	 */
	static isGBSplitEnabled() {
		// This would be set by PHP via inline script
		return window.geodir_split_uk === true;
	}
}

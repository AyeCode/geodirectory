/**
 * GeoDirectory Frontend Script
 * Handles frontend interactions.
 * Relies on window.Alpine being available (loaded via Composer package).
 */

import { initBusinessHoursDisplay } from './frontend/business-hours.js';

// Initialize GeoDir namespace
window.GeoDir = window.GeoDir || {};

// Wait for Alpine to initialize
document.addEventListener('alpine:init', () => {
	console.log('GeoDirectory: Alpine initialized');

	// Example: Register a global Alpine data object or store here if needed
	// Alpine.data('geoDirSearch', () => ({ ... }));
});

// Standard DOM Ready for non-Alpine logic
document.addEventListener('DOMContentLoaded', () => {
	console.log('GeoDirectory: Frontend DOM loaded');

	// Initialize business hours display
	const bhDisplay = initBusinessHoursDisplay();
	if (bhDisplay) {
		window.GeoDir.businessHoursDisplay = bhDisplay;
		console.log('GeoDirectory: Business hours display initialized');
	}
});

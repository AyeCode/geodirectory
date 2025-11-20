/**
 * GeoDirectory Frontend Script
 * * Handles frontend interactions.
 * Relies on window.Alpine being available (loaded via Composer package).
 */

// Wait for Alpine to initialize
document.addEventListener('alpine:init', () => {
	console.log('GeoDirectory: Alpine initialized');

	// Example: Register a global Alpine data object or store here if needed
	// Alpine.data('geoDirSearch', () => ({ ... }));
});

// Standard DOM Ready for non-Alpine logic
document.addEventListener('DOMContentLoaded', () => {
	console.log('GeoDirectory: Frontend DOM loaded');
});

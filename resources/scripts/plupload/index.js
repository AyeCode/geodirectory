/**
 * Main initialization for Plupload file uploads
 * Finds all upload widgets and initializes them
 */

import { PluploadManager } from './plupload-manager.js';
import { registerThumbnailComponent } from './thumbnail-manager.js';
import './image-meta.js'; // Import to register global functions

// CRITICAL: Register Alpine component listener at TOP LEVEL, not inside DOM ready handler
// This ensures the listener is added BEFORE Alpine auto-starts in the body
document.addEventListener('alpine:init', () => {
	registerThumbnailComponent();
});

/**
 * Initialize all plupload widgets on the page
 */
function initPluploadWidgets() {
	// Find all plupload containers
	const containers = document.querySelectorAll('.plupload-upload-uic');

	if (containers.length === 0) {
		return;
	}

	// Initialize each widget
	containers.forEach(container => {
		const id = container.getAttribute('id');

		if (!id) {
			console.warn('Plupload container missing ID', container);
			return;
		}

		// Extract image ID from container ID
		// Format: "plupload-upload-ui" suffix, so "post_imagesplupload-upload-ui" -> "post_images"
		const imgId = id.replace('plupload-upload-ui', '');

		if (!imgId) {
			console.warn('Could not extract image ID from container', container);
			return;
		}

		// Initialize PluploadManager for this widget
		new PluploadManager(container, imgId);
	});
}

/**
 * Initialize plupload when DOM is ready
 */
function init() {
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initPluploadWidgets);
	} else {
		// DOM already loaded
		initPluploadWidgets();
	}
}

// Run initialization
init();

/**
 * Legacy compatibility - add exists() function to check if elements exist
 * (Many legacy scripts use this jQuery plugin)
 */
if (typeof window !== 'undefined' && !window.hasOwnProperty('elementExists')) {
	/**
	 * Check if element exists
	 * @param {string} selector
	 * @returns {boolean}
	 */
	window.elementExists = function(selector) {
		return document.querySelector(selector) !== null;
	};
}

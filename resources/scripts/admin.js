/**
 * GeoDirectory Admin Script
 * Handles admin dashboard interactions.
 */

import { initRatingInput } from './shared/rating-input.js';

document.addEventListener('DOMContentLoaded', () => {
	'use strict';

	console.log('GeoDirectory: Admin loaded');

	// Initialize rating input (for edit comment screens)
	initRatingInput();
	window.GeoDir = window.GeoDir || {};
	window.GeoDir.initRatingInput = initRatingInput;
	console.log('GeoDirectory: Rating input initialized');
});

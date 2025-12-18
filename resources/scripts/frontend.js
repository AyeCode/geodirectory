/**
 * GeoDirectory Frontend Script
 * Handles frontend interactions.
 * Relies on window.Alpine being available (loaded via Composer package).
 */

import { initBusinessHoursDisplay } from './frontend/business-hours.js';
import { initRatingInput } from './shared/rating-input.js';

// Initialize GeoDir namespace
window.GeoDir = window.GeoDir || {};

/**
 * REST API Client
 * Handles all API requests to GeoDirectory REST endpoints.
 */
window.GeoDir.api = {
	baseURL: window.geodir_params?.rest_url || '/wp-json/geodir/v3',
	nonce: window.geodir_params?.rest_nonce || '',

	/**
	 * Make an API request.
	 *
	 * @param {string} endpoint - API endpoint (e.g., 'places/123')
	 * @param {object} options - Fetch options
	 * @returns {Promise<object>} Response data
	 */
	async request(endpoint, options = {}) {
		const url = `${this.baseURL}/${endpoint}`;
		const headers = {
			'X-WP-Nonce': this.nonce,
			'Content-Type': 'application/json',
			...options.headers
		};

		try {
			const response = await fetch(url, { ...options, headers });
			const data = await response.json();

			if (!response.ok) {
				throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
			}

			return data;
		} catch (error) {
			console.error('GeoDir API Error:', error);
			throw error;
		}
	},

	/**
	 * POST request.
	 *
	 * @param {string} endpoint - API endpoint
	 * @param {object} body - Request body
	 * @returns {Promise<object>}
	 */
	async post(endpoint, body) {
		return this.request(endpoint, {
			method: 'POST',
			body: JSON.stringify(body)
		});
	},

	/**
	 * PUT request.
	 *
	 * @param {string} endpoint - API endpoint
	 * @param {object} body - Request body
	 * @returns {Promise<object>}
	 */
	async put(endpoint, body) {
		return this.request(endpoint, {
			method: 'PUT',
			body: JSON.stringify(body)
		});
	},

	/**
	 * DELETE request.
	 *
	 * @param {string} endpoint - API endpoint
	 * @returns {Promise<object>}
	 */
	async delete(endpoint) {
		return this.request(endpoint, {
			method: 'DELETE'
		});
	},

	/**
	 * Upload a file.
	 *
	 * @param {File} file - File to upload
	 * @param {number} postId - Optional post ID
	 * @returns {Promise<object>}
	 */
	async uploadFile(file, postId = 0) {
		const formData = new FormData();
		formData.append('file', file);
		if (postId) {
			formData.append('post_id', postId);
		}

		const url = `${this.baseURL}/media/upload`;
		const response = await fetch(url, {
			method: 'POST',
			headers: {
				'X-WP-Nonce': this.nonce
			},
			body: formData
		});

		const data = await response.json();
		if (!response.ok) {
			throw new Error(data.message || 'Upload failed');
		}

		return data;
	}
};

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

	// Initialize rating input
	initRatingInput();
	window.GeoDir.initRatingInput = initRatingInput;
	console.log('GeoDirectory: Rating input initialized');
});

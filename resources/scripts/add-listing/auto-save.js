/**
 * Auto-save functionality for add-listing form
 */

import { getFormData, isLocalStorageAvailable } from './utils.js';

// Track if form has changes
export let hasChanges = false;

// Track if currently uploading (don't auto-save during uploads)
export let isUploading = false;

// Store previous form data for comparison
let previousFormData = '';

// Auto-save interval ID
let autoSaveIntervalId = null;

/**
 * Initialize auto-save functionality
 * @param {HTMLFormElement} form
 */
export function initAutoSave(form) {
	const autosaveInterval = window.geodir_params?.autosave || 0;

	if (autosaveInterval <= 0) {
		return; // Auto-save disabled
	}

	// Store initial form data
	previousFormData = getFormData(form);

	// Load saved user login/email from localStorage
	loadUserDataFromStorage(form);

	// Start polling for changes
	startAutoSavePoll(form, autosaveInterval);

	// Set up beforeunload warning
	setupBeforeUnloadWarning();
}

/**
 * Load user login/email from localStorage if available
 * @param {HTMLFormElement} form
 */
function loadUserDataFromStorage(form) {
	if (!isLocalStorageAvailable()) return;

	const userLoginField = form.querySelector('#user_login');
	const userEmailField = form.querySelector('#user_email');

	if (userLoginField && localStorage.getItem('geodirUserLogin')) {
		userLoginField.value = localStorage.getItem('geodirUserLogin');
	}

	if (userEmailField && localStorage.getItem('geodirUserEmail')) {
		userEmailField.value = localStorage.getItem('geodirUserEmail');
	}
}

/**
 * Save user login/email to localStorage
 * @param {HTMLFormElement} form
 */
function saveUserDataToStorage(form) {
	if (!isLocalStorageAvailable()) return;

	const userLoginField = form.querySelector('#user_login');
	const userEmailField = form.querySelector('#user_email');

	if (userLoginField && userLoginField.value) {
		localStorage.setItem('geodirUserLogin', userLoginField.value);
	}

	if (userEmailField && userEmailField.value) {
		localStorage.setItem('geodirUserEmail', userEmailField.value);
	}
}

/**
 * Clear user data from localStorage
 */
export function clearUserDataFromStorage() {
	if (!isLocalStorageAvailable()) return;

	localStorage.removeItem('geodirUserLogin');
	localStorage.removeItem('geodirUserEmail');
}

/**
 * Start auto-save polling
 * @param {HTMLFormElement} form
 * @param {number} interval - Interval in milliseconds
 */
function startAutoSavePoll(form, interval) {
	autoSaveIntervalId = setInterval(() => {
		const currentFormData = getFormData(form);

		// Only auto-save if form data has changed
		if (currentFormData !== previousFormData && !isUploading) {
			console.log('Form has changed, auto-saving...');
			performAutoSave(form);
			hasChanges = true;
			previousFormData = currentFormData;
		}
	}, interval);
}

/**
 * Stop auto-save polling
 */
export function stopAutoSave() {
	if (autoSaveIntervalId) {
		clearInterval(autoSaveIntervalId);
		autoSaveIntervalId = null;
	}
}

/**
 * Perform auto-save via REST API
 * @param {HTMLFormElement} form
 */
async function performAutoSave(form) {
	if (isUploading) {
		return;
	}

	// Save user data to localStorage
	saveUserDataToStorage(form);

	// Get post ID
	const postIdField = form.querySelector('input[name="ID"]');
	if (!postIdField || !postIdField.value) {
		console.log('No post ID found, skipping autosave');
		return;
	}

	const postId = postIdField.value;
	const postType = form.querySelector('input[name="post_type"]')?.value || 'gd_place';

	// Convert FormData to JSON object, handling multiple values and nested arrays
	const formData = new FormData(form);
	const data = {};

	for (let [key, value] of formData.entries()) {
		// Skip non-relevant fields
		if (key === 'action' || key === 'target') continue;

		// Parse PHP-style array notation: tax_input[category][] or tax_input[category]
		const matches = key.match(/^([^\[]+)(?:\[([^\]]*)\])?(?:\[\])?$/);

		if (matches && matches[2] !== undefined) {
			// Has nested structure like tax_input[category][]
			const baseKey = matches[1];
			const subKey = matches[2];
			const isArray = key.endsWith('[]');

			if (!data[baseKey]) {
				data[baseKey] = {};
			}

			if (isArray) {
				// Array notation: tax_input[category][]
				if (!data[baseKey][subKey]) {
					data[baseKey][subKey] = [];
				}
				if (value !== '') { // Skip empty values
					data[baseKey][subKey].push(value);
				}
			} else {
				// Single value: tax_input[category]
				data[baseKey][subKey] = value;
			}
		} else {
			// Regular field or top-level array
			if (data.hasOwnProperty(key)) {
				// Already exists - make it an array if not already
				if (!Array.isArray(data[key])) {
					data[key] = [data[key]];
				}
				data[key].push(value);
			} else {
				// First occurrence of this key
				data[key] = value;
			}
		}
	}

	try {
		const result = await window.GeoDir.api.post(`${postType}/${postId}/autosave`, data);

		if (result.success) {
			if (result.autosaved) {
				console.log('Auto-saved successfully');
			} else {
				console.log('No changes to save');
			}
		}
	} catch (error) {
		console.error('Auto-save error:', error);
	}
}

/**
 * Manually trigger auto-save (used by preview button, etc.)
 * @param {HTMLFormElement} form
 */
export function manualAutoSave(form) {
	return performAutoSave(form);
}

/**
 * Set up beforeunload warning for unsaved changes
 */
function setupBeforeUnloadWarning() {
	window.addEventListener('beforeunload', (e) => {
		if (hasChanges) {
			const message = window.geodir_params?.txt_lose_changes || 'You have unsaved changes. Are you sure you want to leave?';
			e.preventDefault();
			e.returnValue = message;
			return message;
		}
	});
}

/**
 * Reset changes flag (called after successful form submission)
 */
export function resetChangesFlag() {
	hasChanges = false;
}

/**
 * Set uploading flag
 * @param {boolean} uploading
 */
export function setUploading(uploading) {
	isUploading = uploading;
}

// Make uploading flag globally accessible (for legacy compatibility)
if (typeof window !== 'undefined') {
	window.geodirUploading = false;

	// Proxy to keep window.geodirUploading in sync
	Object.defineProperty(window, 'geodirUploading', {
		get() {
			return isUploading;
		},
		set(value) {
			isUploading = value;
		}
	});
}

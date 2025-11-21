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
 * Perform auto-save via AJAX
 * @param {HTMLFormElement} form
 */
async function performAutoSave(form) {
	if (isUploading) {
		return;
	}

	// Save user data to localStorage
	saveUserDataToStorage(form);

	const formData = getFormData(form);
	const params = formData + '&action=geodir_auto_save_post&target=auto';

	try {
		const response = await fetch(window.geodir_params.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: params
		});

		const data = await response.json();

		if (data.success) {
			console.log('Auto-saved successfully');
		} else {
			console.log('Auto-save failed');
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

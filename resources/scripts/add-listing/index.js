/**
 * Main initialization for add-listing form
 * Coordinates all modules and sets up event handlers
 */

import { validateField, validateForm, validateAdminForm } from './validation.js';
import { initAutoSave, manualAutoSave } from './auto-save.js';
import { handleFormSubmit, deleteRevision } from './form-submit.js';
import { initCategoryManager } from './category-manager.js';
import { initBusinessHours } from './business-hours.js';
import { scrollToElement } from './utils.js';

/**
 * Initialize add-listing form
 */
function initAddListingForm() {
	// Frontend form
	const frontendForm = document.querySelector('#geodirectory-add-post');
	if (frontendForm) {
		setupFrontendForm(frontendForm);
	}

	// Backend form (WordPress post edit screen)
	const backendForm = document.querySelector('form#post .postbox#geodir_post_info');
	if (backendForm) {
		setupBackendForm(backendForm);
	}
}

/**
 * Set up frontend add-listing form
 * @param {HTMLFormElement} form
 */
function setupFrontendForm(form) {
	console.log('Initializing frontend add-listing form');

	// Initialize auto-save
	initAutoSave(form);

	// Initialize category manager
	initCategoryManager(form);

	// Initialize business hours widgets
	initBusinessHours(form);

	// Set up field validation on blur
	setupFieldValidation(form);

	// Set up form submission
	setupFormSubmission(form);

	// Set up preview button
	setupPreviewButton(form);

	// Set up conditional fields
	setupConditionalFields(form);

	// Set up hidden lat/lng warning
	setupLatLngWarning(form);

	// Set up geolocation button
	setupGeolocation(form);

	// Set up post_tags spellcheck
	setupTagsSpellcheck(form);
}

/**
 * Set up backend WordPress form validation
 * @param {HTMLElement} metabox
 */
function setupBackendForm(metabox) {
	console.log('Initializing backend add-listing form');

	const form = metabox.closest('form#post');
	if (!form) return;

	// Initialize category manager
	initCategoryManager(form);

	// Initialize business hours widgets
	initBusinessHours(form);

	// Set up field validation on blur
	setupFieldValidation(form);

	// Intercept form submission
	form.addEventListener('submit', (e) => {
		if (!validateAdminForm(form)) {
			e.preventDefault();
			scrollToFirstError(form);
			return false;
		}
	});
}

/**
 * Set up field validation on blur/change events
 * @param {HTMLFormElement} form
 */
function setupFieldValidation(form) {
	// Validation on blur for text inputs
	const textFields = form.querySelectorAll('.required_field:not([style*="display: none"]) [field_type]:not([style*="display: none"]), .required_field:not([style*="display: none"]) .editor textarea');

	textFields.forEach(field => {
		field.addEventListener('blur', function() {
			// Delay to allow other scripts to fill data
			setTimeout(() => {
				validateField(this);
			}, 100);
		});
	});

	// Validation on click for checkboxes and radios
	const clickFields = form.querySelectorAll('.required_field:not([style*="display: none"]) input[type="checkbox"], .required_field:not([style*="display: none"]) input[type="radio"]');

	clickFields.forEach(field => {
		field.addEventListener('click', function() {
			validateField(this);
		});
	});

	// Validation on change for select/multiselect
	const selectFields = form.querySelectorAll('.required_field:not([style*="display: none"]) select.geodir-select');

	selectFields.forEach(field => {
		field.addEventListener('change', function() {
			validateField(this);
		});
	});
}

/**
 * Set up form submission handler
 * @param {HTMLFormElement} form
 */
function setupFormSubmission(form) {
	form.addEventListener('submit', async (e) => {
		await handleFormSubmit(e, form);
	});
}

/**
 * Set up preview button
 * @param {HTMLFormElement} form
 */
function setupPreviewButton(form) {
	const previewButton = document.querySelector('.geodir_preview_button');
	if (!previewButton) return;

	previewButton.addEventListener('click', async () => {
		// Auto-save before preview
		await manualAutoSave(form);

		// Validate form
		return validateForm(form);
	});
}

/**
 * Set up conditional fields
 * @param {HTMLFormElement} form
 */
function setupConditionalFields(form) {
	// Run conditional fields on change
	form.addEventListener('change', () => {
		try {
			if (typeof window.aui_conditional_fields === 'function') {
				window.aui_conditional_fields('#geodirectory-add-post,#post');
			}
		} catch (err) {
			console.log('Conditional fields error:', err.message);
		}
	});

	// Run conditional fields on load
	try {
		if (typeof window.aui_conditional_fields === 'function') {
			window.aui_conditional_fields('#geodirectory-add-post,#post');
		}
	} catch (err) {
		console.log('Conditional fields error:', err.message);
	}
}

/**
 * Set up hidden lat/lng required field warning
 * @param {HTMLFormElement} form
 */
function setupLatLngWarning(form) {
	const hiddenLatLng = form.querySelector('.gd-hidden-latlng');
	if (!hiddenLatLng) return;

	const submitButtons = form.querySelectorAll('[type="submit"]');

	submitButtons.forEach(button => {
		button.addEventListener('click', () => {
			const latField = form.querySelector('[name="latitude"]');
			const lngField = form.querySelector('[name="longitude"]');

			if (latField && lngField) {
				const lat = latField.value.trim();
				const lng = lngField.value.trim();

				if (!lat || !lng) {
					hiddenLatLng.classList.remove('d-none');
				}
			}
		});
	});
}

/**
 * Set up geolocation "locate me" button
 * @param {HTMLFormElement} form
 */
function setupGeolocation(form) {
	const locateMeButtons = form.querySelectorAll('.gd-locate-me-btn');

	locateMeButtons.forEach(button => {
		button.addEventListener('click', function() {
			if (typeof window.gdGeoLocateMe === 'function') {
				window.gdGeoLocateMe(this, 'add-listing');
			}
		});
	});
}

/**
 * Set up post_tags spellcheck
 * @param {HTMLFormElement} form
 */
function setupTagsSpellcheck(form) {
	const tagsSelect = form.querySelector('select#post_tags');
	if (!tagsSelect) return;

	if (tagsSelect.spellcheck) {
		setTimeout(() => {
			const tagsInput = form.querySelector('[data-argument="post_tags"] input.select2-search__field');
			if (tagsInput) {
				tagsInput.spellcheck = true;
			}
		}, 5000);
	}
}

/**
 * Scroll to first error in form
 * @param {HTMLFormElement} form
 */
function scrollToFirstError(form) {
	const firstError = form.querySelector('.geodir_message_error:not([style*="display: none"])');

	if (firstError) {
		const errorWrap = firstError.closest('.required_field');
		if (errorWrap) {
			scrollToElement(errorWrap, 100);
		}
	}
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
	document.addEventListener('DOMContentLoaded', initAddListingForm);
} else {
	initAddListingForm();
}

// Make some functions globally accessible for legacy compatibility
if (typeof window !== 'undefined') {
	window.geodir_validate_field = validateField;
	window.geodir_validate_submit = validateForm;
	window.geodir_validate_admin_submit = (form) => validateAdminForm(form);
	window.geodir_delete_revision = () => {
		const form = document.querySelector('#geodirectory-add-post') || document.querySelector('form#post');
		if (form) {
			deleteRevision(form);
		}
	};
}

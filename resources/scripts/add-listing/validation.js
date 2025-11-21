/**
 * Form validation functions for add-listing form
 * Uses Bootstrap 5.3+ validation classes
 */

/**
 * Validate a single field based on its field_type attribute
 * @param {HTMLElement} field - The field element to validate
 * @returns {boolean} - True if valid, false if invalid
 */
export function validateField(field) {
	const fieldType = field.getAttribute('field_type');
	let isValid = true;
	let errorMessage = window.geodir_params?.field_id_required || 'This field is required.';

	// Get the required field wrapper
	const requiredFieldWrap = field.closest('.required_field');
	if (!requiredFieldWrap) {
		return true; // Not a required field
	}

	switch (fieldType) {
		case 'radio':
		case 'checkbox':
			// Check if at least one is checked
			const checkedInputs = requiredFieldWrap.querySelectorAll(':checked');
			isValid = checkedInputs.length > 0;

			// Check category limit if exists
			const catLimitInput = requiredFieldWrap.querySelector('#cat_limit');
			if (catLimitInput) {
				const catLimit = parseInt(catLimitInput.getAttribute('cat_limit'));
				const catMsg = catLimitInput.value;
				if (checkedInputs.length > catLimit && catLimit > 0) {
					isValid = false;
					errorMessage = catMsg;
				}
			}
			break;

		case 'select':
			// Check if taxonomy field (post_category)
			if (requiredFieldWrap.querySelector('.geodir_taxonomy_field') &&
			    requiredFieldWrap.querySelector('#post_category')) {
				const postCategory = requiredFieldWrap.querySelector('#post_category');
				isValid = postCategory.value !== '';
			} else {
				const selectedOption = field.querySelector('option:selected');
				isValid = selectedOption && selectedOption.value !== '';
			}
			break;

		case 'multiselect':
			// Check category limit
			const multiCatLimit = requiredFieldWrap.querySelector('#cat_limit');
			if (multiCatLimit) {
				const limit = parseInt(multiCatLimit.getAttribute('cat_limit'));
				const msg = multiCatLimit.value;
				const selectedOptions = field.querySelectorAll('option:selected');
				if (selectedOptions.length > limit && limit > 0) {
					isValid = false;
					errorMessage = msg;
				} else {
					isValid = selectedOptions.length > 0;
				}
			} else {
				const selectedOptions = field.querySelectorAll('option:selected');
				isValid = selectedOptions.length > 0;
			}
			break;

		case 'email':
			const emailRegex = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
			isValid = field.value !== '' && emailRegex.test(field.value);
			break;

		case 'url':
			const urlRegex = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
			isValid = field.value !== '' && urlRegex.test(field.value);
			break;

		case 'editor':
			const fieldId = field.getAttribute('field_id');
			const editorField = document.getElementById(fieldId);
			isValid = editorField && editorField.value !== '';
			break;

		case 'address':
			// Special validation for latitude/longitude
			if (field.id === 'post_latitude' || field.id === 'post_longitude') {
				const coordRegex = /^[0-90\-.]*$/;
				if (coordRegex.test(field.value) && field.value !== '') {
					isValid = true;
				} else {
					isValid = false;
					errorMessage = field.id === 'post_latitude'
						? (window.geodir_params?.latitude_error_msg || 'Please enter a valid latitude.')
						: (window.geodir_params?.longgitude_error_msg || 'Please enter a valid longitude.');
				}
			} else {
				isValid = field.value.trim() !== '';
			}
			break;

		case 'datepicker':
		case 'time':
		case 'text':
		case 'hidden': // images
		case 'textarea':
			isValid = field.value.trim() !== '';
			break;

		default:
			isValid = field.value.trim() !== '';
			break;
	}

	// Update UI based on validation result
	updateFieldValidationUI(requiredFieldWrap, field, isValid, errorMessage);

	return isValid;
}

/**
 * Update field validation UI with Bootstrap classes
 * @param {HTMLElement} requiredFieldWrap
 * @param {HTMLElement} field
 * @param {boolean} isValid
 * @param {string} errorMessage
 */
function updateFieldValidationUI(requiredFieldWrap, field, isValid, errorMessage) {
	const errorElement = requiredFieldWrap.querySelector('.geodir_message_error');

	if (isValid) {
		// Remove invalid state
		field.classList.remove('is-invalid');
		field.classList.add('is-valid');

		if (errorElement) {
			errorElement.textContent = '';
			errorElement.style.display = 'none';
		}
	} else {
		// Add invalid state
		field.classList.remove('is-valid');
		field.classList.add('is-invalid');

		if (errorElement) {
			if (errorElement.textContent === '') {
				errorElement.textContent = errorMessage;
			}
			errorElement.style.display = 'block';
		}
	}
}

/**
 * Validate entire form before submission
 * @param {HTMLFormElement} form
 * @returns {boolean} - True if all fields valid
 */
export function validateForm(form) {
	let isValid = true;
	let firstInvalidField = null;

	// Find all required fields
	const requiredFields = form.querySelectorAll('.required_field:not([style*="display: none"])');

	requiredFields.forEach(requiredWrap => {
		// Find fields to validate within this required wrapper
		const fieldsToValidate = requiredWrap.querySelectorAll(
			'[field_type]:not([style*="display: none"]), ' +
			'.geodir_select, ' +
			'.geodir_location_add_listing_chosen, ' +
			'.editor, ' +
			'.event_recurring_dates, ' +
			'.geodir-custom-file-upload, ' +
			'.gd_image_required_field, ' +
			'.g-recaptcha-response, ' +
			'[name="cf-turnstile-response"]'
		);

		fieldsToValidate.forEach(field => {
			if (!validateField(field)) {
				isValid = false;
				if (!firstInvalidField) {
					firstInvalidField = field;
				}
			}
		});
	});

	return isValid;
}

/**
 * Validate admin form (backend)
 * @param {HTMLFormElement} form
 * @returns {boolean}
 */
export function validateAdminForm(form) {
	let isValid = true;

	const requiredFields = form.querySelectorAll('.required_field:not([style*="display: none"])');

	requiredFields.forEach(requiredWrap => {
		const fieldsToValidate = requiredWrap.querySelectorAll(
			'[field_type]:not([style*="display: none"]), ' +
			'.geodir_select, ' +
			'.geodir_location_add_listing_chosen, ' +
			'.editor, ' +
			'.event_recurring_dates, ' +
			'.geodir-custom-file-upload, ' +
			'.gd_image_required_field'
		);

		fieldsToValidate.forEach(field => {
			if (!validateField(field)) {
				isValid = false;
			}
		});
	});

	if (!isValid) {
		// Stop WordPress publish spinner
		const saveSpinner = document.querySelector('#save-action .spinner');
		const saveButton = document.querySelector('#save-action #save-post');
		const publishSpinner = document.querySelector('#publishing-action .spinner');
		const publishButton = document.querySelector('#publishing-action #publish');

		if (saveSpinner) saveSpinner.classList.remove('is-active');
		if (saveButton) saveButton.classList.remove('disabled');
		if (publishSpinner) publishSpinner.classList.remove('is-active');
		if (publishButton) publishButton.classList.remove('disabled');
	}

	return isValid;
}

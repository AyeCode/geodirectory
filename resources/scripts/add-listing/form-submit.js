/**
 * Form submission handling for add-listing form
 */

import { getFormData } from './utils.js';
import { validateForm } from './validation.js';
import { resetChangesFlag, clearUserDataFromStorage } from './auto-save.js';

/**
 * Handle form submission
 * @param {Event} e - Submit event
 * @param {HTMLFormElement} form
 */
export async function handleFormSubmit(e, form) {
	e.preventDefault();

	// Validate form
	if (!validateForm(form)) {
		scrollToFirstError(form);
		return false;
	}

	// Submit the form
	await submitForm(form);
	return false;
}

/**
 * Submit form via REST API
 * @param {HTMLFormElement} form
 */
async function submitForm(form) {
	console.log('Submitting form...');

	const submitButton = form.querySelector('#geodir-add-listing-submit button');
	const originalButtonText = submitButton ? submitButton.innerHTML : '';

	try {
		// Show loading state
		if (submitButton) {
			submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> ' + originalButtonText;
			submitButton.classList.add('gd-disabled');
			submitButton.disabled = true;
		}

		// Clear user data from localStorage
		clearUserDataFromStorage();

		// Get post ID and type
		const postIdField = form.querySelector('input[name="ID"]');
		const postId = postIdField ? postIdField.value : null;
		const postType = form.querySelector('input[name="post_type"]')?.value || 'gd_place';

		// Convert form to JSON, handling multiple values for same key
		const formData = new FormData(form);
		const data = {};

		function parseKey(rawKey) {
			const forceArray = /\[\]$/.test(rawKey);              // ends with []
			const keyNoArray = rawKey.replace(/\[\]$/, "");       // remove only trailing []
			const parts = [...keyNoArray.matchAll(/([^[\]]+)/g)].map(m => m[1]); // tokens inside brackets
			return { parts, forceArray };
		}

		function setDeep(obj, parts, value, forceArray) {
			let cur = obj;

			for (let i = 0; i < parts.length; i++) {
				const k = parts[i];
				const last = i === parts.length - 1;

				if (!last) {
					if (cur[k] == null || typeof cur[k] !== "object" || Array.isArray(cur[k])) {
						cur[k] = {};
					}
					cur = cur[k];
					continue;
				}

				if (forceArray) {
					if (!Array.isArray(cur[k])) cur[k] = [];
					cur[k].push(value);
				} else {
					// plain duplicates: keep LAST value only (checkboxes fix)
					cur[k] = value; // last value wins
				}
			}
		}

		for (const [rawKey, value] of formData.entries()) {
			if (rawKey === "action" || rawKey === "target") continue;

			const { parts, forceArray } = parseKey(rawKey);
			setDeep(data, parts, value, forceArray);
		}

		// console.log('Submitting form data:', data);
		// return;

		// Submit via REST API
		let result;
		if (postId) {
			// Update existing post
			result = await window.GeoDir.api.put(`${postType}/${postId}`, data);
		} else {
			// Create new post
			result = await window.GeoDir.api.post(postType, data);
		}

		if (result.success) {
			console.log('Form submitted successfully');
			console.log(result);

			// Reset changes flag
			resetChangesFlag();

			// Show success message
			showSuccessMessage(result);

			// Handle payment URL if provided
			if (result.payment_url) {
				window.location.href = result.payment_url;
				return true;
			}

			// Redirect to permalink or preview
			if (result.permalink) {
				setTimeout(() => {
					window.location.href = result.permalink;
				}, 2000);
			} else if (result.preview_link) {
				setTimeout(() => {
					window.location.href = result.preview_link;
				}, 2000);
			}

			return true;
		}
	} catch (error) {
		console.error('Form submission error:', error);

		// Restore button state
		if (submitButton) {
			submitButton.innerHTML = originalButtonText;
			submitButton.classList.remove('gd-disabled');
			submitButton.disabled = false;
		}

		// Show error message
		alert(error.message || 'An error occurred. Please try again.');

		// Dispatch event to reset captcha
		document.dispatchEvent(new Event('ayecode_reset_captcha'));

		return false;
	}
}

/**
 * Show success message
 * @param {object} result - API response
 */
function showSuccessMessage(result) {
	// Remove existing notifications
	document.querySelectorAll('.gd-notification').forEach(notif => notif.remove());

	// Create success notification
	const notification = document.createElement('div');
	notification.className = 'gd-notification alert alert-success';
	notification.innerHTML = result.message || 'Listing saved successfully!';

	// Insert at top of form
	const form = document.querySelector('#geodirectory-add-post');
	if (form) {
		form.insertAdjacentElement('beforebegin', notification);

		// Scroll to notification
		const notificationTop = notification.getBoundingClientRect().top + window.pageYOffset - 100;
		window.scrollTo({
			top: notificationTop,
			behavior: 'smooth'
		});
	}
}

/**
 * Delete revision via REST API
 * @param {HTMLFormElement} form
 */
export async function deleteRevision(form) {
	const postIdField = form.querySelector('input[name="ID"]');
	const postId = postIdField ? postIdField.value : null;
	const postType = form.querySelector('input[name="post_type"]')?.value || 'gd_place';

	if (!postId) {
		console.error('No post ID found for revision deletion');
		return false;
	}

	if (!confirm('Are you sure you want to delete this draft/revision and start fresh?')) {
		return false;
	}

	try {
		const result = await window.GeoDir.api.delete(`${postType}/${postId}?force=true`);

		if (result.success) {
			console.log('Revision deleted successfully');
			location.reload();
			return true;
		}
	} catch (error) {
		console.error('Revision deletion error:', error);
		alert(error.message || 'Failed to delete revision. Please try again.');
		return false;
	}
}

/**
 * Scroll to first error message
 * @param {HTMLFormElement} form
 */
function scrollToFirstError(form) {
	// Find first visible error
	const firstError = form.querySelector('.geodir_message_error:not([style*="display: none"])');

	if (firstError) {
		const errorWrap = firstError.closest('.required_field');
		if (errorWrap) {
			const errorTop = errorWrap.getBoundingClientRect().top + window.pageYOffset;
			window.scrollTo({
				top: errorTop,
				behavior: 'smooth'
			});
			return;
		}
	}

	// Fallback: find first invalid field
	const firstInvalidField = form.querySelector('.is-invalid');
	if (firstInvalidField) {
		const fieldTop = firstInvalidField.getBoundingClientRect().top + window.pageYOffset;
		window.scrollTo({
			top: fieldTop,
			behavior: 'smooth'
		});
	}
}

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
 * Submit form via AJAX
 * @param {HTMLFormElement} form
 */
async function submitForm(form) {
	const formData = getFormData(form) + '&target=submit';
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

		const response = await fetch(window.geodir_params.ajax_url, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
			body: formData
		});

		const data = await response.json();

		if (data.success) {
			console.log('Form submitted successfully');
			console.log(data.data);

			// Reset changes flag
			resetChangesFlag();

			// Remove current notifications
			document.querySelectorAll('.gd-notification').forEach(notif => notif.remove());

			// Get container to replace
			const replaceContainerInput = document.querySelector('#gd-add-listing-replace-container');
			const containerSelector = replaceContainerInput ? replaceContainerInput.value : '#geodirectory-add-post';
			const containerToReplace = document.querySelector(containerSelector);

			if (containerToReplace && data.data) {
				// Replace container with response (success message or redirect)
				containerToReplace.outerHTML = data.data;

				// Scroll to notification
				const notification = document.querySelector('.gd-notification');
				if (notification) {
					const notificationTop = notification.getBoundingClientRect().top + window.pageYOffset - 100;
					window.scrollTo({
						top: notificationTop,
						behavior: 'smooth'
					});
				}
			}

			return true;
		} else {
			console.log('Form submission failed');

			// Restore button state
			if (submitButton) {
				submitButton.innerHTML = originalButtonText;
				submitButton.classList.remove('gd-disabled');
				submitButton.disabled = false;
			}

			// Show error message
			if (data.data) {
				alert(data.data);
			}

			// Dispatch event to reset captcha
			document.dispatchEvent(new Event('ayecode_reset_captcha'));

			return false;
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
		const errorMsg = window.geodir_params?.rating_error_msg || 'An error occurred. Please try again.';
		alert(errorMsg);

		// Dispatch event to reset captcha
		document.dispatchEvent(new Event('ayecode_reset_captcha'));

		return false;
	}
}

/**
 * Delete revision via AJAX
 * @param {HTMLFormElement} form
 */
export async function deleteRevision(form) {
	const formData = getFormData(form);
	const params = formData + '&action=geodir_delete_revision&target=revision';

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
			console.log('Revision deleted successfully');
			location.reload();
			return true;
		} else {
			console.log('Revision deletion failed');
			if (data.data) {
				alert(data.data);
			}
			return false;
		}
	} catch (error) {
		console.error('Revision deletion error:', error);
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

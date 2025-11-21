/**
 * Utility functions for add-listing form
 */

/**
 * Save all TinyMCE editors
 * Required before form serialization to capture latest editor content
 */
export function saveTinyMCEEditors() {
	if (typeof tinymce !== 'undefined' && tinymce.editors && tinymce.editors.length > 0) {
		tinymce.editors.forEach(editor => {
			editor.save();
		});
	}
}

/**
 * Check if localStorage is available
 * @returns {boolean}
 */
export function isLocalStorageAvailable() {
	try {
		const test = '__localStorage_test__';
		localStorage.setItem(test, test);
		localStorage.removeItem(test);
		return true;
	} catch (e) {
		return false;
	}
}

/**
 * Get form data as URL-encoded string
 * @param {HTMLFormElement} form
 * @returns {string}
 */
export function getFormData(form) {
	saveTinyMCEEditors();
	const formData = new FormData(form);
	return new URLSearchParams(formData).toString();
}

/**
 * Show Bootstrap toast notification
 * @param {string} message
 * @param {string} type - 'success', 'danger', 'warning', 'info'
 */
export function showToast(message, type = 'info') {
	// Check if Bootstrap Toast container exists, create if not
	let toastContainer = document.querySelector('.toast-container');
	if (!toastContainer) {
		toastContainer = document.createElement('div');
		toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
		document.body.appendChild(toastContainer);
	}

	// Create toast element
	const toastId = 'toast-' + Date.now();
	const toastHTML = `
		<div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
			<div class="d-flex">
				<div class="toast-body">${message}</div>
				<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
			</div>
		</div>
	`;

	toastContainer.insertAdjacentHTML('beforeend', toastHTML);

	const toastElement = document.getElementById(toastId);
	const toast = new bootstrap.Toast(toastElement, { autohide: true, delay: 3000 });
	toast.show();

	// Remove toast element after it's hidden
	toastElement.addEventListener('hidden.bs.toast', () => {
		toastElement.remove();
	});
}

/**
 * Scroll to element with offset
 * @param {HTMLElement} element
 * @param {number} offset
 */
export function scrollToElement(element, offset = 100) {
	if (!element) return;

	const elementPosition = element.getBoundingClientRect().top + window.pageYOffset;
	const offsetPosition = elementPosition - offset;

	window.scrollTo({
		top: offsetPosition,
		behavior: 'smooth'
	});
}

/**
 * Check if device supports touch
 * @returns {boolean}
 */
export function isTouchDevice() {
	return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
}

/**
 * Debounce function
 * @param {Function} func
 * @param {number} wait
 * @returns {Function}
 */
export function debounce(func, wait = 300) {
	let timeout;
	return function executedFunction(...args) {
		const later = () => {
			clearTimeout(timeout);
			func(...args);
		};
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
	};
}

/**
 * Get nested property value from object
 * @param {Object} obj
 * @param {string} path - e.g., 'data.user.name'
 * @returns {*}
 */
export function getNestedValue(obj, path) {
	return path.split('.').reduce((current, prop) => current?.[prop], obj);
}

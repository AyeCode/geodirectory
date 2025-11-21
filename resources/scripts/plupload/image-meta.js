/**
 * Image metadata editing functionality
 * Uses Bootstrap 5 modals to edit image title and caption
 */

import { escapeEntities, parseImageString, serializeImages } from './utils.js';

/**
 * Show image metadata editing modal
 * @param {string} inputId - The field ID (e.g., 'post_images')
 * @param {number} orderId - Index of the image in the array
 */
export function showImageMetaModal(inputId, orderId) {
	const input = document.getElementById(inputId);
	if (!input) return;

	const imageString = input.value;
	const images = parseImageString(imageString);

	if (!images[orderId]) return;

	const image = images[orderId];
	const imageTitle = escapeEntities(image.title);
	const imageCaption = escapeEntities(image.caption);

	// Get modal element
	const modalEl = document.getElementById(`gd_image_meta_${inputId}`);
	if (!modalEl) {
		console.error(`Modal #gd_image_meta_${inputId} not found`);
		return;
	}

	// Build form HTML
	const labelTitle = window.geodir_params?.label_title || 'Title';
	const labelCaption = window.geodir_params?.label_caption || 'Caption';
	const buttonSet = window.geodir_params?.button_set || 'Set';

	const formHTML = `
		<div class="form-group mb-3">
			<label for="gd-image-meta-title" class="text-left text-start form-label">${labelTitle}</label>
			<input id="gd-image-meta-title" value="${imageTitle}" class="form-control">
		</div>
		<div class="form-group mb-3">
			<label for="gd-image-meta-caption" class="text-left text-start form-label">${labelCaption}</label>
			<input id="gd-image-meta-caption" value="${imageCaption}" class="form-control">
		</div>
	`;

	const footerHTML = `
		<button type="button" class="btn btn-primary" data-gd-save-meta data-input-id="${inputId}" data-order-id="${orderId}">
			${buttonSet}
		</button>
	`;

	// Update modal content
	const modalBody = modalEl.querySelector('.modal-body');
	const modalFooter = modalEl.querySelector('.modal-footer');

	if (modalBody) modalBody.innerHTML = formHTML;
	if (modalFooter) modalFooter.innerHTML = footerHTML;

	// Attach save button handler
	const saveButton = modalEl.querySelector('[data-gd-save-meta]');
	if (saveButton) {
		saveButton.addEventListener('click', function() {
			saveImageMeta(inputId, orderId);
		}, { once: true });
	}

	// Show modal using Bootstrap 5 API
	const modal = new bootstrap.Modal(modalEl);
	modal.show();
}

/**
 * Save image metadata and update thumbnails
 * @param {string} inputId - The field ID
 * @param {number} orderId - Index of the image
 */
export function saveImageMeta(inputId, orderId) {
	const input = document.getElementById(inputId);
	if (!input) return;

	const modalEl = document.getElementById(`gd_image_meta_${inputId}`);
	if (!modalEl) return;

	// Get form values
	const titleInput = modalEl.querySelector('#gd-image-meta-title');
	const captionInput = modalEl.querySelector('#gd-image-meta-caption');

	if (!titleInput || !captionInput) return;

	const imageTitle = escapeEntities(titleInput.value);
	const imageCaption = escapeEntities(captionInput.value);

	// Parse images
	const images = parseImageString(input.value);

	if (!images[orderId]) return;

	// Update image metadata
	images[orderId].title = imageTitle;
	images[orderId].caption = imageCaption;

	// Serialize back to string
	const imageString = serializeImages(images);
	input.value = imageString;

	// Trigger change event to update Alpine component
	input.dispatchEvent(new Event('change', { bubbles: true }));

	// Hide modal
	const modal = bootstrap.Modal.getInstance(modalEl);
	if (modal) {
		modal.hide();
	}
}

/**
 * Make functions globally accessible for inline onclick handlers (legacy compatibility)
 */
if (typeof window !== 'undefined') {
	window.gd_edit_image_meta = showImageMetaModal;
	window.gd_set_image_meta = saveImageMeta;
}

/**
 * Thumbnail manager using Alpine.js
 * Handles display, sorting, and removal of uploaded files
 */

import { parseImageString, serializeImages, getFileExtension, getFileName, getFileTypeIcon, isImageFile, escapeEntities } from './utils.js';
import { showImageMetaModal } from './image-meta.js';

/**
 * Register Alpine.js thumbnail component
 */
export function registerThumbnailComponent() {
	if (typeof Alpine === 'undefined') {
		console.error('Alpine.js is not loaded');
		return;
	}

	Alpine.data('gdThumbnails', (inputId) => ({
		images: [],
		inputId: inputId,
		skipNextLoad: false, // Flag to prevent reload after sort
		sortIteration: 0, // Force re-render key

		// Helper method to check if image is an actual image file
		isImageFile(image) {
			if (!image || typeof image.url !== 'string') {
				return false;
			}
			const ext = getFileExtension(image.url);
			return isImageFile(ext);
		},

		// Get file icon class
		getFileIcon(image) {
			if (!image || !image.url) return 'fa-file';
			const ext = getFileExtension(image.url);
			return getFileTypeIcon(ext);
		},

		init() {
			this.loadImages();

			// Watch for changes to hidden input (from plupload)
			const input = document.getElementById(this.inputId);
			if (input) {
				input.addEventListener('change', () => {
					// Skip reload if we just did a sort (to prevent re-render jump)
					if (this.skipNextLoad) {
						this.skipNextLoad = false;
						return;
					}
					this.loadImages();
				});
			}

			// Check image limit after loading
			this.checkImageLimit();
		},

		loadImages() {
			const input = document.getElementById(this.inputId);
			if (!input) return;

			this.images = parseImageString(input.value);
		},

		saveImages() {
			const input = document.getElementById(this.inputId);
			if (!input) return;

			const imageString = serializeImages(this.images);
			input.value = imageString;
			input.dispatchEvent(new Event('change', { bubbles: true }));
		},

		/**
		 * Handle Sort Event from Alpine Sort
		 * @param {string|HTMLElement} item - The value passed from x-sort:item (URL string) OR DOM element
		 * @param {number} position - The new index position
		 */
		handleSort(item, position) {
			console.log('GD Sort:', item, position);

			// Get the unique identifier from the dragged item
			let url = item;

			// Fallback: If item is a DOM element (legacy/missing x-sort:item), get attribute
			if (typeof item === 'object' && item !== null && typeof item.getAttribute === 'function') {
				url = item.getAttribute('data-url');
			}

			if (!url) {
				console.warn('GD Sort: Item identifier missing');
				return;
			}

			// Find current index of this item in the data using raw array to avoid proxy noise
			const rawImages = Alpine.raw(this.images);
			const oldIndex = rawImages.findIndex(img => img.url === url);

			if (oldIndex === -1) {
				console.warn('GD Sort: Item not found in data');
				return;
			}

			if (oldIndex === position) {
				return; // No effective change
			}

			// Set flag to prevent reload when change event fires
			this.skipNextLoad = true;

			// Perform array manipulation
			// Remove from old position and insert at new position
			const movedImage = this.images[oldIndex];
			this.images.splice(oldIndex, 1);
			this.images.splice(position, 0, movedImage);

			// FORCE RE-RENDER: Increment iteration key to resync DOM with Data
			this.sortIteration++;

			// Save to input
			this.saveImages();
		},

		removeImage(index) {
			// Remove from array
			this.images.splice(index, 1);

			// Update total image count
			const totalInput = document.getElementById(`${this.inputId}totImg`);
			if (totalInput) {
				const currentTotal = parseInt(totalInput.value) || 0;
				totalInput.value = Math.max(0, currentTotal - 1);
			}

			// Clear any upload errors
			const errorEl = document.getElementById(`${this.inputId}upload-error`);
			if (errorEl) {
				errorEl.textContent = '';
				errorEl.classList.remove('d-block');
				errorEl.classList.add('d-none');
			}

			// Save changes
			this.saveImages();
		},

		editImage(index) {
			showImageMetaModal(this.inputId, index);
		},

		previewImage(index) {
			const image = this.images[index];
			if (!image) return;

			// Get or create preview modal
			const modalId = `gd_image_preview_${this.inputId}`;
			let modal = document.getElementById(modalId);

			if (!modal) {
				// Create modal if it doesn't exist
				const modalHTML = `
					<div class="modal bsui fade" id="${modalId}" tabindex="-1" role="dialog" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title"></h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body text-center p-0">
								</div>
							</div>
						</div>
					</div>
				`;
				document.body.insertAdjacentHTML('beforeend', modalHTML);
				modal = document.getElementById(modalId);
			}

			// Update modal content
			const title = modal.querySelector('.modal-title');
			const body = modal.querySelector('.modal-body');
			const ext = getFileExtension(image.url);
			const isImage = this.isImageFile(image); // Use internal helper

			title.textContent = image.title || getFileName(image.url);

			if (isImage) {
				body.innerHTML = `<img src="${image.url}" class="img-fluid" alt="${escapeEntities(image.title)}" />`;
			} else {
				const iconClass = getFileTypeIcon(ext);
				body.innerHTML = `
					<div class="p-5">
						<i class="fas ${iconClass} display-1 mb-3"></i>
						<p><a href="${image.url}" target="_blank" class="btn btn-primary">Open File</a></p>
					</div>
				`;
			}

			// Show modal
			new bootstrap.Modal(modal).show();
		},

		checkImageLimit() {
			const limitInput = document.getElementById(`${this.inputId}image_limit`);
			if (!limitInput) return;

			const limit = parseInt(limitInput.value);
			if (limit <= 0) return;

			// Remove excess images
			while (this.images.length > limit) {
				this.images.pop();
			}

			if (this.images.length > 0) {
				this.saveImages();
			}
		}
	}));
}

/**
 * Show thumbnails using vanilla JS (fallback if Alpine not available)
 * This is called by PluploadManager after file upload
 * @param {string} imgId
 */
export function showThumbs(imgId) {
	// First, trigger Alpine component to reload
	const input = document.getElementById(imgId);
	if (input) {
		input.dispatchEvent(new Event('change', { bubbles: true }));
	}
}

/**
 * Make function globally accessible for legacy compatibility
 */
if (typeof window !== 'undefined') {
	window.plu_show_thumbs = showThumbs;
}

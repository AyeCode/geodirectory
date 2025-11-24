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

		// Helper method to check if image is an actual image file
		isImageFile(image) {
			const ext = getFileExtension(image.url);
			return isImageFile(ext);
		},

		// Get file icon class
		getFileIcon(image) {
			const ext = getFileExtension(image.url);
			return getFileTypeIcon(ext);
		},

		init() {
			this.loadImages();

			// Watch for changes to hidden input (from plupload)
			const input = document.getElementById(this.inputId);
			if (input) {
				input.addEventListener('change', () => {
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

		handleSort() {
			// Called by Alpine Sort when drag-drop reorder completes
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
			const isImage = isImageFile(ext);

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

		getThumbHTML(image, index) {
			const ext = getFileExtension(image.url);
			const fileName = getFileName(image.url);
			const isImage = isImageFile(ext);

			let fileDisplay = '';
			let fileDisplayClass = '';
			let imageTitleHTML = '';
			let imageCaptionHTML = '';

			const safeTitle = escapeEntities(image.title);
			const safeCaption = escapeEntities(image.caption);

			if (isImage) {
				fileDisplay = `<img class="gd-file-info embed-responsive-item embed-item-cover-xy"
					src="${image.url}"
					alt="${safeTitle}"
					data-index="${index}" />`;

				if (image.title && String(image.title).trim()) {
					imageTitleHTML = `<span class="gd-title-preview badge badge-light ab-top-left text-truncate mw-100 h-auto text-dark w-auto" style="background: #ffffffc7">${safeTitle}</span>`;
				}

				if (image.caption && String(image.caption).trim()) {
					imageCaptionHTML = `<span class="gd-caption-preview badge badge-light ab-top-left mt-4 text-truncate mw-100 h-auto text-dark w-auto" style="background: #ffffffc7">${safeCaption}</span>`;
				}
			} else {
				const iconClass = getFileTypeIcon(ext);
				fileDisplayClass = 'file-thumb';
				fileDisplay = `<i title="${fileName}"
					class="fas ${iconClass} gd-file-info embed-responsive-item embed-item-cover-xy display-1"
					data-index="${index}"
					aria-hidden="true"></i>`;
			}

			const txtPreview = window.geodir_params?.txt_preview || 'Preview';
			const txtEdit = window.geodir_params?.txt_edit || 'Edit';
			const txtDelete = window.geodir_params?.txt_delete || 'Delete';

			return `
				<div class="col px-2 mb-2">
					<div class="thumb ${fileDisplayClass} ratio ratio-16x9 embed-responsive embed-responsive-16by9 bg-white border c-move">
						${imageTitleHTML}
						${fileDisplay}
						${imageCaptionHTML}
						<div class="gd-thumb-actions position-absolute text-white w-100 d-flex justify-content-around" style="bottom: 0; background: #00000063; top: auto; height: 20px;">
							<a class="thumbpreviewlink text-white" title="${escapeEntities(txtPreview)}" href="${image.url}" target="_blank">
								<i class="far fa-eye" aria-hidden="true"></i>
							</a>
							<span class="thumbeditlink c-pointer" title="${escapeEntities(txtEdit)}" @click="editImage(${index})">
								<i class="far fa-edit" aria-hidden="true"></i>
							</span>
							<span class="thumbremovelink c-pointer" title="${escapeEntities(txtDelete)}" @click="removeImage(${index})">
								<i class="fas fa-trash-alt" aria-hidden="true"></i>
							</span>
						</div>
					</div>
				</div>
			`;
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

	// If Alpine component exists, it will handle the display
	// Otherwise, we could implement a vanilla JS fallback here if needed
}

/**
 * Make function globally accessible for legacy compatibility
 */
if (typeof window !== 'undefined') {
	window.plu_show_thumbs = showThumbs;
}

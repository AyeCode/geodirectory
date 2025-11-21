/**
 * Plupload Manager
 * Handles Plupload initialization and upload events
 */

import { showThumbs } from './thumbnail-manager.js';
import { formatFileSize } from './utils.js';

/**
 * Plupload Manager Class
 */
export class PluploadManager {
	constructor(container, imgId) {
		this.container = container;
		this.imgId = imgId;
		this.uploader = null;
		this.postId = this.getPostId();

		this.init();
	}

	/**
	 * Get post ID from form
	 */
	getPostId() {
		// Frontend form
		const frontendInput = document.querySelector('#geodirectory-add-post input[name="ID"]');
		if (frontendInput) {
			return frontendInput.value;
		}

		// Backend form
		const backendInput = document.querySelector('#post input[name="post_ID"]');
		if (backendInput) {
			return backendInput.value;
		}

		return '';
	}

	/**
	 * Initialize Plupload
	 */
	init() {
		if (typeof plupload === 'undefined') {
			console.error('Plupload library not loaded');
			return;
		}

		if (typeof window.geodir_plupload_params === 'undefined') {
			console.error('geodir_plupload_params not found');
			return;
		}

		// Show existing thumbnails
		showThumbs(this.imgId);

		// Parse base config
		const pconfig = JSON.parse(window.geodir_plupload_params.base_plupload_config);

		// Update config with field-specific IDs
		pconfig.browse_button = this.imgId + pconfig.browse_button;
		pconfig.container = this.imgId + pconfig.container;
		pconfig.file_data_name = this.imgId + pconfig.file_data_name;

		// Add drop element if exists
		const dropbox = document.getElementById(this.imgId + 'dropbox');
		if (dropbox) {
			pconfig.drop_element = this.imgId + 'dropbox';
		}

		// Set multipart params
		pconfig.multipart_params.imgid = this.imgId;
		pconfig.multipart_params.post_id = this.postId;

		// Check for multi-selection
		if (this.container.classList.contains('plupload-upload-uic-multiple')) {
			pconfig.multi_selection = true;
		}

		// Get allowed file types
		const allowedTypesInput = document.getElementById(`${this.imgId}_allowed_types`);
		let allowedExts = allowedTypesInput ? allowedTypesInput.value : '';

		// Special handling for post_images
		if (this.imgId === 'post_images' &&
		    typeof window.geodir_params !== 'undefined' &&
		    window.geodir_params.gd_allowed_img_types) {
			allowedExts = window.geodir_params.gd_allowed_img_types;
		}

		// Set filters if allowed types exist
		if (allowedExts && allowedExts !== '') {
			const txtAllFiles = window.geodir_params?.txt_all_files || 'Allowed files';
			pconfig.filters = [{
				title: txtAllFiles,
				extensions: allowedExts
			}];
		}

		// Create uploader
		this.uploader = new plupload.Uploader(pconfig);

		// Bind events
		this.bindEvents();

		// Initialize uploader
		this.uploader.init();
	}

	/**
	 * Bind Plupload events
	 */
	bindEvents() {
		// Init event
		this.uploader.bind('Init', (up, params) => {
			this.handleInit(up, params);
		});

		// Upload file event
		this.uploader.bind('UploadFile', (up, file) => {
			this.handleUploadFile(up, file);
		});

		// Upload complete event
		this.uploader.bind('UploadComplete', (up, files) => {
			this.handleUploadComplete(up, files);
		});

		// Error event
		this.uploader.bind('Error', (up, error) => {
			this.handleError(up, error);
		});

		// Files added event
		this.uploader.bind('FilesAdded', (up, files) => {
			this.handleFilesAdded(up, files);
		});

		// Upload progress event
		this.uploader.bind('UploadProgress', (up, file) => {
			this.handleUploadProgress(up, file);
		});

		// File uploaded event
		this.uploader.bind('FileUploaded', (up, file, response) => {
			this.handleFileUploaded(up, file, response);
		});
	}

	/**
	 * Handle Init event
	 */
	handleInit(up, params) {
		// Setup drag-drop if supported
		if (up.features.dragdrop) {
			const dropId = this.imgId + 'dropbox';
			const target = document.getElementById(dropId);

			if (target) {
				target.addEventListener('dragenter', () => {
					target.classList.add('dragover');
				});

				target.addEventListener('dragleave', () => {
					target.classList.remove('dragover');
				});

				target.addEventListener('drop', () => {
					target.classList.remove('dragover');
				});
			}
		}

		// Fix iPhone issue with hidden element
		const moxieShim = this.container.querySelector('.moxie-shim');
		if (moxieShim) {
			moxieShim.style.position = 'initial';
		}
	}

	/**
	 * Handle UploadFile event
	 */
	handleUploadFile(up, file) {
		// Set global uploading flag for post_images to prevent auto-save
		if (this.imgId === 'post_images') {
			window.geodirUploading = true;
		}
	}

	/**
	 * Handle UploadComplete event
	 */
	handleUploadComplete(up, files) {
		// Clear global uploading flag
		if (this.imgId === 'post_images') {
			window.geodirUploading = false;
		}
	}

	/**
	 * Handle Error event
	 */
	handleError(up, error) {
		// Clear uploading flag on error
		if (this.imgId === 'post_images') {
			window.geodirUploading = false;
		}

		const errorEl = document.getElementById(`${this.imgId}upload-error`);
		if (!errorEl) return;

		let msgErr = '';

		if (error.code === -600) {
			// File size error
			msgErr = window.geodir_params?.err_max_file_size || 'File size error : You tried to upload a file over %s';
			msgErr = msgErr.replace('%s', window.geodir_plupload_params.upload_img_size);
		} else if (error.code === -601) {
			// File type error
			msgErr = window.geodir_params?.err_file_type || 'File type error. Allowed file types: %s';

			if (this.imgId === 'post_images') {
				const allowedTypesInput = document.getElementById(`${this.imgId}_allowed_types`);
				const allowedExts = allowedTypesInput ? allowedTypesInput.value : '';
				const txtReplace = allowedExts !== '' ? '.' + allowedExts.replace(/,/g, ', .') : '*';
				msgErr = msgErr.replace('%s', txtReplace);
			} else {
				const allowedTypesInput = document.getElementById(`${this.imgId}_allowed_types`);
				const exts = allowedTypesInput ? allowedTypesInput.dataset.exts : '';
				msgErr = msgErr.replace('%s', exts);
			}
		} else {
			msgErr = error.message;
		}

		errorEl.classList.remove('d-none');
		errorEl.classList.add('d-block');
		errorEl.textContent = msgErr;
	}

	/**
	 * Handle FilesAdded event
	 */
	handleFilesAdded(up, files) {
		const totalImgInput = document.getElementById(`${this.imgId}totImg`);
		const limitImgInput = document.getElementById(`${this.imgId}image_limit`);

		const totalImg = totalImgInput ? parseInt(totalImgInput.value) : 0;
		const limitImg = limitImgInput ? parseInt(limitImgInput.value) : 0;

		// Clear error
		const errorEl = document.getElementById(`${this.imgId}upload-error`);
		if (errorEl) {
			errorEl.textContent = '';
			errorEl.classList.remove('d-block');
			errorEl.classList.add('d-none');
		}

		// Check limits for multiple uploads
		if (limitImg && this.container.classList.contains('plupload-upload-uic-multiple') && limitImg > 0) {
			// Check if already at limit
			if (totalImg >= limitImg) {
				while (up.files.length > 0) {
					up.removeFile(up.files[0]);
				}

				let msgErr = window.geodir_params?.err_file_upload_limit || 'You have reached your upload limit of %s files.';
				msgErr = msgErr.replace('%s', limitImg);

				if (errorEl) {
					errorEl.classList.remove('d-none');
					errorEl.classList.add('d-block');
					errorEl.textContent = msgErr;
				}
				return false;
			}

			// Check if upload exceeds limit
			if (up.files.length > limitImg) {
				while (up.files.length > 0) {
					up.removeFile(up.files[0]);
				}

				let msgErr = window.geodir_params?.err_pkg_upload_limit || 'You may only upload %s files with this package, please try again.';
				msgErr = msgErr.replace('%s', limitImg);

				if (errorEl) {
					errorEl.classList.remove('d-none');
					errorEl.classList.add('d-block');
					errorEl.textContent = msgErr;
				}
				return false;
			}
		}

		// Add files to UI
		const fileList = this.container.querySelector('.filelist');
		if (fileList) {
			files.forEach(file => {
				const fileEl = document.createElement('div');
				fileEl.className = 'file';
				fileEl.id = file.id;
				fileEl.innerHTML = `
					<b>${file.name}</b>
					(<span>${formatFileSize(0)}</span>/${formatFileSize(file.size)})
					<div class="progress">
						<div class="fileprogress progress-bar progress-bar-striped progress-bar-animated"
							 role="progressbar"
							 aria-valuenow="0"
							 aria-valuemin="0"
							 aria-valuemax="100"></div>
					</div>
				`;
				fileList.appendChild(fileEl);
			});
		}

		up.refresh();
		up.start();
	}

	/**
	 * Handle UploadProgress event
	 */
	handleUploadProgress(up, file) {
		const fileEl = document.getElementById(file.id);
		if (!fileEl) return;

		const progressBar = fileEl.querySelector('.fileprogress');
		const sizeSpan = fileEl.querySelector('span');

		if (progressBar) {
			progressBar.style.width = file.percent + '%';
		}

		if (sizeSpan) {
			sizeSpan.textContent = formatFileSize(parseInt(file.size * file.percent / 100));
		}
	}

	/**
	 * Handle FileUploaded event
	 */
	handleFileUploaded(up, file, response) {
		const fileEl = document.getElementById(file.id);
		if (fileEl) {
			// Stop animation
			const progressBar = fileEl.querySelector('.fileprogress');
			if (progressBar) {
				progressBar.classList.remove('progress-bar-animated');
			}

			// Fade out
			setTimeout(() => {
				fileEl.style.opacity = '0';
				setTimeout(() => fileEl.remove(), 300);
			}, 500);
		}

		// Get response data
		let responseData = response.response;

		// Parse JSON response if needed
		try {
			const jsonResponse = JSON.parse(responseData);
			if (jsonResponse.success && jsonResponse.data) {
				// WordPress AJAX response format: {"success":true,"data":{"url":"...","id":123}}
				// Convert to our format: url|id|title|caption
				responseData = `${jsonResponse.data.url}|${jsonResponse.data.id}||`;
			}
		} catch (e) {
			// Not JSON or already in correct format (url|id|title|caption)
			// Just use as-is
		}

		// Get hidden input
		const input = document.getElementById(this.imgId);
		if (!input) return;

		// Update total count and value
		const totalImgInput = document.getElementById(`${this.imgId}totImg`);

		if (this.container.classList.contains('plupload-upload-uic-multiple')) {
			// Multiple files
			let currentValue = input.value.trim();

			if (currentValue) {
				currentValue += '::' + responseData;
			} else {
				currentValue = responseData;
			}

			input.value = currentValue;

			// Increment total
			if (totalImgInput) {
				totalImgInput.value = parseInt(totalImgInput.value) + 1;
			}
		} else {
			// Single file
			input.value = responseData;
		}

		// Trigger change event
		input.dispatchEvent(new Event('change', { bubbles: true }));

		// Show thumbnails
		showThumbs(this.imgId);
	}
}

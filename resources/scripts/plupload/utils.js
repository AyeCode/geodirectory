/**
 * Utility functions for plupload file uploads
 */

/**
 * Escape HTML entities in a string
 * @param {string} str
 * @returns {string}
 */
export function escapeEntities(str) {
	// First decode any existing entities
	str = decodeEntities(str);

	const entityMap = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
		'=': '&#x3D;'
	};

	return String(str).replace(/[&<>"'`=\/]/g, (s) => entityMap[s]);
}

/**
 * Decode HTML entities in a string
 * @param {string} str
 * @returns {string}
 */
export function decodeEntities(str) {
	if (!str) {
		return str;
	}

	const entityMap = {
		'&amp;': '&',
		'&lt;': '<',
		'&gt;': '>',
		'&quot;': '"',
		'&#39;': "'",
		'&#x2F;': '/',
		'&#x60;': '`',
		'&#x3D;': '='
	};

	for (const entity in entityMap) {
		const pattern = new RegExp(entity, 'g');
		str = str.replace(pattern, entityMap[entity]);
	}

	return str;
}

/**
 * Get file extension from URL
 * @param {string} url
 * @returns {string}
 */
export function getFileExtension(url) {
	let ext = url.substring(url.lastIndexOf('.') + 1);

	// Remove query params if present
	ext = ext.split('?').shift();

	if (ext) {
		ext = ext.toLowerCase();
	}

	return ext;
}

/**
 * Get filename without extension from URL
 * @param {string} url
 * @returns {string}
 */
export function getFileName(url) {
	const fileNameIndex = url.lastIndexOf('/') + 1;
	const dotIndex = url.lastIndexOf('.');

	if (dotIndex < fileNameIndex) {
		return '';
	}

	return url.substring(fileNameIndex, dotIndex);
}

/**
 * Get Font Awesome icon class for file type
 * @param {string} ext - File extension
 * @returns {string} - Font Awesome class
 */
export function getFileTypeIcon(ext) {
	const iconMap = {
		'pdf': 'fa-file-pdf',
		'zip': 'fa-file-archive',
		'tar': 'fa-file-archive',
		'doc': 'fa-file-word',
		'docx': 'fa-file-word',
		'odt': 'fa-file-word',
		'txt': 'fa-file',
		'text': 'fa-file',
		'csv': 'fa-file-excel',
		'ods': 'fa-file-excel',
		'ots': 'fa-file-excel',
		'xls': 'fa-file-excel',
		'xlsx': 'fa-file-excel',
		'avi': 'fa-file-video',
		'mp4': 'fa-file-video',
		'mov': 'fa-file-video',
		'mp3': 'fa-file-audio',
		'wav': 'fa-file-audio'
	};

	return iconMap[ext] || 'fa-file';
}

/**
 * Check if file extension is an image
 * @param {string} ext - File extension
 * @returns {boolean}
 */
export function isImageFile(ext) {
	const imageExts = ['jpg', 'jpe', 'jpeg', 'png', 'gif', 'bmp', 'ico', 'webp', 'avif', 'svg'];
	return imageExts.includes(ext);
}

/**
 * Format file size for display
 * @param {number} bytes
 * @returns {string}
 */
export function formatFileSize(bytes) {
	if (bytes === 0) return '0 B';

	const k = 1024;
	const sizes = ['B', 'KB', 'MB', 'GB'];
	const i = Math.floor(Math.log(bytes) / Math.log(k));

	return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

/**
 * Parse image string into array of image objects
 * Format: "url|id|title|caption::url|id|title|caption"
 * @param {string} imageString
 * @returns {Array}
 */
export function parseImageString(imageString) {
	if (!imageString || imageString === 'null') {
		return [];
	}

	const images = imageString.split('::');
	return images
		.filter(img => img && img !== 'null')
		.map((img, index) => {
			const parts = img.split('|');
			return {
				url: parts[0] || '',
				id: parts[1] || '',
				title: decodeEntities(parts[2] || ''),
				caption: decodeEntities(parts[3] || ''),
				index: index
			};
		});
}

/**
 * Serialize array of image objects back to string
 * @param {Array} images
 * @returns {string}
 */
export function serializeImages(images) {
	return images
		.map(img => `${img.url}|${img.id}|${escapeEntities(img.title)}|${escapeEntities(img.caption)}`)
		.join('::');
}

<?php
/**
 * This file contains filesystem-related utility functions.
 *
 * @author   AyeCode
 * @category Utils
 * @package  GeoDirectory\ImportExport\Utils
 */

namespace AyeCode\GeoDirectory\ImportExport\Utils;

class Filesystem
{
	/**
	 * Ensures the WordPress Filesystem API is initialized.
	 *
	 * This method checks if the global $wp_filesystem object is available
	 * and initializes it if not. It's a required step before performing
	 * any file read/write operations.
	 */
	public static function init_filesystem() { // <-- Changed from 'private' to 'public'
		global $wp_filesystem;

		if (is_null($wp_filesystem)) {
			if (!function_exists('WP_Filesystem')) {
				require_once ABSPATH . 'wp-admin/includes/file.php';
			}
			WP_Filesystem();
		}
	}

	/**
	 * Gets the path to the GeoDirectory cache directory.
	 */
	public static function getCacheDirectoryPath(bool $relative = true): string
	{
		$upload_dir = wp_upload_dir();
		$folder = 'geodirectory/import-export-cache';
		return $relative ? ($upload_dir['baseurl'] . '/' . $folder) : ($upload_dir['basedir'] . '/' . $folder);
	}

	/**
	 * Ensures the cache directory exists and is protected.
	 */
	public static function ensureCacheDirectoryExists()
	{
		self::init_filesystem();
		global $wp_filesystem;

		$upload_dir = wp_upload_dir();
		$parent_dir = $upload_dir['basedir'] . '/geodirectory';
		$cache_dir = self::getCacheDirectoryPath(false);

		if (!$wp_filesystem->is_dir($parent_dir)) {
			if (!$wp_filesystem->mkdir($parent_dir, FS_CHMOD_DIR)) {
				return new \WP_Error('gd-imex-no-parent-dir', __("ERROR: Could not create parent cache directory.", "geodirectory"));
			}
		}

		if (!$wp_filesystem->is_dir($cache_dir)) {
			if (!$wp_filesystem->mkdir($cache_dir, FS_CHMOD_DIR)) {
				return new \WP_Error('gd-imex-no-cache-dir', __("ERROR: Could not create cache directory.", "geodirectory"));
			}
		}

		if ($wp_filesystem->is_dir($cache_dir) && !$wp_filesystem->exists($cache_dir . '/index.php')) {
			$wp_filesystem->put_contents($cache_dir . '/index.php', '<?php // Silence is golden.', FS_CHMOD_FILE);
		}

		return true;
	}

	/**
	 * Counts the number of lines in a given file.
	 */
	public static function countFileLines(string $filepath): ?int
	{
		self::init_filesystem();
		global $wp_filesystem;

		if ($wp_filesystem->is_file($filepath) && $wp_filesystem->exists($filepath)) {
			$contents = $wp_filesystem->get_contents_array($filepath);
			if (is_array($contents)) {
				return count($contents) - 1;
			}
		}
		return null;
	}
}

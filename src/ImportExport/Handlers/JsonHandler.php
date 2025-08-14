<?php
/**
 * This file contains the handler for JSON file operations.
 *
 * @author   AyeCode
 * @category Handlers
 * @package  GeoDirectory\ImportExport\Handlers
 * @version  1.0.0
 */

namespace AyeCode\GeoDirectory\ImportExport\Handlers;

/**
 * JsonHandler Class
 *
 * Manages reading and decoding JSON files. This is primarily used for
 * handling the import and export of plugin settings.
 */
class JsonHandler
{
	/**
	 * The WordPress Filesystem instance.
	 * @var \WP_Filesystem_Base
	 */
	private $filesystem;

	/**
	 * JsonHandler constructor.
	 *
	 * @param \WP_Filesystem_Base $filesystem An instance of the WP_Filesystem.
	 */
	public function __construct(\WP_Filesystem_Base $filesystem)
	{
		$this->filesystem = $filesystem;
	}

	/**
	 * Reads and decodes a JSON file into a PHP array.
	 *
	 * @param string $filepath The absolute path to the JSON file.
	 *
	 * @return array|\WP_Error The decoded array on success, or a WP_Error on failure.
	 */
	public function decodeFile(string $filepath)
	{
		if ( ! $this->filesystem->is_file( $filepath ) || ! $this->filesystem->exists( $filepath ) ) {
			return new \WP_Error( 'gd-imex-json-no-file', __( "JSON file not found at specified path.", "geodirectory" ) );
		}

		// Check that the file extension is .json
		if (strtolower(pathinfo($filepath, PATHINFO_EXTENSION)) !== 'json') {
			return new \WP_Error( 'gd-imex-json-invalid-ext', __( "The provided file is not a .json file.", "geodirectory" ) );
		}

		$contents = $this->filesystem->get_contents( $filepath );

		if ( empty( $contents ) ) {
			return new \WP_Error( 'gd-imex-json-empty', __( "JSON file is empty.", "geodirectory" ) );
		}

		$data = json_decode( $contents, true );

		// Check for JSON errors and ensure it decodes to an array.
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $data ) ) {
			return new \WP_Error( 'gd-imex-json-invalid', __( "The file contains invalid JSON.", "geodirectory" ) );
		}

		return $data;
	}
}

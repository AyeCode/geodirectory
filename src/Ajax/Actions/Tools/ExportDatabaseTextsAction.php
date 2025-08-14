<?php
/**
 * Class ClearVersionNumbersAction
 * Represents an action to clear version numbers within the GeoDirectory Ajax functionality.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

// Exit if accessed directly
use const AyeCode\GeoDirectory\Ajax\Actions\GEODIRECTORY_VERSION;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClearVersionNumbersAction
 * Handles the process of clearing version numbers and resetting associated data within the GeoDirectory plugin.
 */
class ExportDatabaseTextsAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {
		if (self::load_db_language()) {
			$message = __( 'File successfully created: ', 'geodirectory' ). geodir_plugin_path() . 'db-language.php';
			wp_send_json_success(array(
				'message'  => $message,
				'progress' => 100
			));
		} else {
			$message = __( 'There was a problem creating the file, please check file permissions: ', 'geodirectory' ). geodir_plugin_path() . 'db-language.php';
			wp_send_json_error(array(
				'message'  => $message,
				'progress' => 100
			));
		}

	}

	/**
	 * Load language strings in to file to translate via po editor
	 *
	 * @since   1.4.2
	 * @package GeoDirectory
	 *
	 * @global null|object $wp_filesystem WP_Filesystem object.
	 *
	 * @return bool True if file created otherwise false
	 */
	public static function load_db_language() {
		$wp_filesystem = geodir_init_filesystem();

		$language_file = geodir_plugin_path() . 'db-language.php';

		if ( is_file( $language_file ) && ! is_writable( $language_file ) ) {
			return false;
		} // Not possible to create.

		if ( ! is_file( $language_file ) && ! is_writable( dirname( $language_file ) ) ) {
			return false;
		} // Not possible to create.

		$contents_strings = array();

		/**
		 * Filter the language string from database to translate via po editor
		 *
		 * @since 1.4.2
		 * @since 1.6.16 Register the string for WPML translation.
		 *
		 * @param array $contents_strings Array of strings.
		 */
		$contents_strings = apply_filters( 'geodir_load_db_language', $contents_strings );

		$contents_strings = array_unique( $contents_strings );

		$contents_head   = array();
		$contents_head[] = "<?php";
		$contents_head[] = "/**";
		$contents_head[] = " * Translate language string stored in database. Ex: Custom Fields";
		$contents_head[] = " *";
		$contents_head[] = " * @package GeoDirectory";
		$contents_head[] = " * @since ".GEODIRECTORY_VERSION;
		$contents_head[] = " */";
		$contents_head[] = "";
		$contents_head[] = "// Language keys";

		$contents_foot   = array();
		$contents_foot[] = "";
		$contents_foot[] = "";

		$contents = implode( PHP_EOL, $contents_head );

		if ( ! empty( $contents_strings ) ) {
			foreach ( $contents_strings as $string ) {
				if ( is_scalar( $string ) && $string != '' ) {
					do_action( 'geodir_language_file_add_string', $string );

					$string = str_replace( "'", "\'", $string );

					$contents .= PHP_EOL . "__('" . $string . "', 'geodirectory');";
				}
			}
		}

		$contents .= implode( PHP_EOL, $contents_foot );

		if ( ! $wp_filesystem->put_contents( $language_file, $contents, FS_CHMOD_FILE ) ) {
			return false;
		} // Failure; could not write file.

		return true;
	}
}

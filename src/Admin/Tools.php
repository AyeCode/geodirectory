<?php
/**
 * GeoDirectory Tools Page Handler
 *
 * This class extends the AyeCode Settings Framework to create and manage the
 * GeoDirectory Tools admin page.
 *
 * @package     GeoDirectory
 * @subpackage  Admin
 * @since       2.2.0
 */

// Define the namespace for the class.
namespace GeoDirectory\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Use the abstract base class from the AyeCode namespace.
use AyeCode\SettingsFramework\Settings_Framework;

/**
 * GeoDirectory\Admin\Tools Class
 *
 * Extends the core framework to define the GeoDirectory Tools page.
 */
final class Tools extends Settings_Framework {

	// region Framework Properties
	// These protected properties configure the admin page.
	// ----------------------------------------------------------------------------------

	/**
	 * The option name for the Tools page.
	 *
	 * @var string
	 */
	protected $option_name = 'geodir_tools_page';

	/**
	 * The unique slug for the admin page.
	 *
	 * @var string
	 */
	protected $page_slug = 'geodir_tools';

	/**
	 * The name/logo of the plugin, displayed in the settings header.
	 *
	 * @var string
	 */
	protected $plugin_name = '<span class="fa-stack fa-1x me-1"><i class="fas fa-circle fa-stack-2x text-light"></i><i style="color: #ff8333 !important;" class="fas fa-globe-americas fa-stack-2x text-primary "></i></span> <span class="fw-normal fs-4"><span style="color: #ff8333 !important;">Geo</span>Directory</span>';

	/**
	 * The title displayed in the browser tab.
	 *
	 * @var string
	 */
	protected $page_title = 'Tools';

	/**
	 * The text for the admin menu item.
	 *
	 * @var string
	 */
	protected $menu_title = 'Tools';

	/**
	 * The Tools page is a submenu under the main 'geodirectory' menu.
	 *
	 * @var string
	 */
	protected $parent_slug = 'geodirectory';

	/**
	 * The capability required for a user to access this page.
	 *
	 * @var string
	 */
	protected $capability = 'manage_options';

	// endregion

	/**
	 * Constructor.
	 * Initializes the framework for this page.
	 */
	public function __construct() {

		// Localize translatable properties before the parent constructor uses them.
		$this->page_title = __( 'Tools', 'geodirectory' );
		$this->menu_title = __( 'Tools', 'geodirectory' );

		// Call the parent constructor which handles all the WordPress hooks.
		parent::__construct();
	}

	/**
	 * Builds and returns the tools page configuration array.
	 * This method is required by the abstract parent class.
	 *
	 * @return array The final configuration array.
	 */
	protected function get_config() {
		// Define the list of config files for the Tools page.
		$settings_files = [
			'tools'  => 'config/tools/tools.php',
			'status' => 'config/tools/status.php',
			'import' => 'config/tools/import.php',
			'export' => 'config/tools/export.php',
		];

		$sections = [];

		$base_path = dirname( __FILE__ ) . '/';

		// Loop through the files and collect their returned section arrays.
		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}

		return [ 'sections' => $sections ];
	}

	/**
	 * Helper method to get custom fields that have option values.
	 * This is specific to GeoDirectory and is kept for use in config files.
	 *
	 * @return array
	 */
	public function get_cf_with_option_values() {
		global $wpdb;

		$results = $wpdb->get_results( "SELECT DISTINCT `htmlvar_name`, `frontend_title`, `admin_title` FROM `" . GEODIR_CUSTOM_FIELDS_TABLE . "` WHERE `option_values` != '' AND `option_values` IS NOT NULL AND `field_type` IN ('select', 'multiselect', 'radio', 'checkbox') ORDER BY `admin_title` ASC" );

		$options = [];

		if ( ! empty( $results ) ) {
			foreach ( $results as $row ) {
				if ( ! empty( $row->htmlvar_name ) ) {
					$title = ! empty( $row->admin_title ) ? __( wp_unslash( $row->admin_title ), 'geodirectory' ) : __( wp_unslash( $row->frontend_title ), 'geodirectory' );

					$options[ $row->htmlvar_name ] = $title . ' (' . $row->htmlvar_name . ')';
				}
			}
		}

		$options['country'] = __( 'Country', 'geodirectory' ) . ' (country)';
		$options['region']  = __( 'Region', 'geodirectory' ) . ' (region)';
		$options['city']    = __( 'City', 'geodirectory' ) . ' (city)';
		if ( class_exists( 'GeoDir_Location_Neighbourhood', false ) && \GeoDir_Location_Neighbourhood::is_active() ) {
			$options['neighbourhood'] = __( 'Neighbourhood', 'geodirectory' ) . ' (neighbourhood)';
		}
		$options['zip'] = __( 'Zip', 'geodirectory' ) . ' (zip)';

		return $options;
	}
}

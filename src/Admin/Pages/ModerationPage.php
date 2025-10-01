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
namespace AyeCode\GeoDirectory\Admin\Pages;

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
final class ModerationPage extends Settings_Framework {

	// region Framework Properties
	// These protected properties configure the admin page.
	// ----------------------------------------------------------------------------------

	/**
	 * The option name for the Tools page.
	 *
	 * @var string
	 */
	protected $option_name = 'geodir_moderation_page';

	/**
	 * The unique slug for the admin page.
	 *
	 * @var string
	 */
	protected $page_slug = 'geodir_moderation';

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
	protected $page_title = 'Moderation';

	/**
	 * The text for the admin menu item.
	 *
	 * @var string
	 */
	protected $menu_title = 'Moderation';

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
		$this->page_title = __( 'Moderation', 'geodirectory' );
		$this->menu_title = __( 'Moderation', 'geodirectory' );

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
			'reported' => 'config/moderation/reported.php',
		];

		$sections = [];

		$base_path = dirname( __FILE__ ) . '/../';

		// Loop through the files and collect their returned section arrays.
		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}

		return [ 'sections' => $sections ];
	}


}

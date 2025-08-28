<?php
/**
 * GeoDirectory Settings Handler
 *
 * This class extends the AyeCode Settings Framework to create and manage
 * the main GeoDirectory settings admin page using the new inheritance model.
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
 * GeoDirectory\Admin\Settings Class
 *
 * Extends the core framework to define the GeoDirectory settings page.
 */
final class SettingsPage extends Settings_Framework {

	// region Framework Properties
	// These protected properties are used by the parent Settings_Framework class
	// to configure the admin page, menu items, and storage options.
	// ----------------------------------------------------------------------------------

	/**
	 * The option name used to store settings in the wp_options table.
	 *
	 * @var string
	 */
	protected $option_name = 'geodir_settings';

	/**
	 * The unique slug for the admin page.
	 *
	 * @var string
	 */
	protected $page_slug = 'geodir_settings';

	/**
	 * The name/logo of the plugin, displayed in the settings header.
	 *
	 * @var string
	 */
	protected $plugin_name = '<span class="fa-stack fa-1x me-1"><i class="fas fa-circle fa-stack-2x text-light"></i><i style="color: #ff8333 !important;" class="fas fa-globe-americas fa-stack-2x text-primary "></i></span> <span class="fw-normal fs-4"><span style="color: #ff8333 !important;">Geo</span>Directory</span>';

	/**
	 * The title displayed in the browser tab.
	 * This will be localized in the constructor.
	 *
	 * @var string
	 */
	protected $page_title = 'Settings';

	/**
	 * The text for the admin menu item.
	 * This will be localized in the constructor.
	 *
	 * @var string
	 */
	protected $menu_title = 'Settings';

	/**
	 * The settings page is added as a submenu under the main 'geodirectory' menu.
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
	 * Sets up the class, localizes properties, and calls the parent constructor
	 * to initialize the framework and its hooks.
	 */
	public function __construct() {
		// Localize translatable properties before the parent constructor uses them.
		$this->page_title = __( 'Settings', 'geodirectory' );
		$this->menu_title = __( 'Settings', 'geodirectory' );

		// Call the parent constructor to hook everything into WordPress.
		parent::__construct();

		// We add our own enqueue hook with a later priority to ensure it runs
		// after the parent framework has enqueued its core scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_geodirectory_assets' ], 50 );
	}

	/**
	 * Builds and returns the settings configuration array.
	 * This method is required by the abstract parent class. It loads configuration
	 * from separate files for better organization.
	 *
	 * @return array The final settings configuration array.
	 */
	protected function get_config() {
		// Define the list of settings files to be included.
		$settings_files = [
			'general'        => 'config/settings/general.php',
			'emails'         => 'config/settings/emails.php',
			'design'         => 'config/settings/design.php',
			'seo'            => 'config/settings/seo.php',
			'developer'      => 'config/settings/developer.php',
			'api'            => 'config/settings/api.php',
		];

		$sections = [];

		// Define the base path for the settings files.
		$base_path = dirname( __FILE__ ) . '/../';

		// Loop through the files, include them, and collect their returned section arrays.
		foreach ( $settings_files as $file_path ) {
			$full_path = $base_path . $file_path;
			if ( file_exists( $full_path ) ) {
				$sections[] = include( $full_path );
			}
		}
//		print_r( $sections );exit;

		// The final configuration array required by the framework.
		return [ 'sections' => $sections ];
	}

	public function get_custom_search_links() {
		return [
			[
				'title'       => 'Generate Dummy Data',
				'description' => 'Go to the Tools page to generate sample listings and reviews.',
				'url'         => admin_url('admin.php?page=geodir_tools#section=dummy-data'),
				'keywords'    => ['dummy', 'sample', 'test data', 'generate'],
				'icon'        => 'fas fa-fw fa-database', // Example Font Awesome icon
				'external'    => false, // Is this an external link (opens in new tab)?
			],
			[
				'title'       => 'Read Documentation on Emails',
				'description' => 'Learn how to configure all email templates.',
				'url'         => 'https://docs.example.com/emails/',
				'keywords'    => ['email', 'docs', 'documentation', 'help'],
				'icon'        => 'fas fa-fw fa-book',
				'external'    => true,
			]
		];
	}

	/**
	 * Enqueues GeoDirectory-specific assets on the settings page.
	 * This method runs *after* the parent framework's enqueue method.
	 *
	 * @param string $hook The current admin page hook.
	 */
	public function enqueue_geodirectory_assets( $hook ) {
		// Ensure we are on the correct settings page before loading any assets.
		if ( $hook !== $this->screen_id ) {
			return;
		}


		return; //@todo circle back to this

		// Add GeoDirectory-specific inline scripts and styles.
		wp_add_inline_script( 'ayecode-settings-framework-admin', geodir_settings_map_input_js_function() );

		$suffix          = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$geodir_map_name = \GeoDir_Maps::active_map();

		$required_scripts = [ 'geodir-admin-script' ];

		// Add map scripts if needed.
		if ( in_array( $geodir_map_name, [ 'auto', 'google' ] ) ) {
			$required_scripts[] = 'geodir-google-maps';
			$required_scripts[] = 'geodir-g-overlappingmarker-script';
		} elseif ( $geodir_map_name == 'osm' ) {
			$required_scripts[] = 'geodir-leaflet-script';
			$required_scripts[] = 'geodir-leaflet-geo-script';
			$required_scripts[] = 'geodir-o-overlappingmarker-script';
		}

		$osm_extra = \GeoDir_Maps::footer_script();
		wp_add_inline_script( 'geodir-goMap', "window.gdSetMap = window.gdSetMap || '" . \GeoDir_Maps::active_map() . "';" . $osm_extra, 'before' );
		$required_scripts[] = 'geodir-goMap';

		wp_register_script( 'geodir-setup', GEODIRECTORY_PLUGIN_URL . '/assets/js/setup-wizard' . $suffix . '.js', $required_scripts, GEODIRECTORY_VERSION );
		wp_enqueue_script( 'geodir-setup' );

		wp_localize_script( 'geodir-setup', 'geodir_params', geodir_params() );
		if ( in_array( 'geodir-google-maps', $required_scripts ) ) {
			wp_add_inline_script( 'geodir-google-maps', \GeoDir_Maps::google_map_callback(), 'before' );
		}
	}

	// region Helper Methods
	// These are helper functions specific to GeoDirectory's settings needs.
	// They can be called from the config files (e.g., to populate select options).
	// ----------------------------------------------------------------------------------

	/**
	 * Helper function to get a specific setting value from the database.
	 *
	 * @param string $key The key of the setting to retrieve.
	 * @param mixed $default Optional. The default value to return if the key is not found.
	 *
	 * @return mixed The value of the setting, or the default value.
	 */
	public function get_option( $key, $default = false ) {
		$options = get_option( $this->option_name );

		return isset( $options[ $key ] ) ? $options[ $key ] : $default;
	}

	/**
	 * Retrieves all published pages and formats them for a select field.
	 *
	 * @return array An associative array where keys are page IDs and values are page titles.
	 */
	public function get_pages_options() {
		$pages_options = [];
		$pages         = get_pages( [ 'post_status' => 'publish', 'number' => 200 ] );

		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page ) {
				$pages_options[ $page->ID ] = $page->post_title;
			}
		}

		return $pages_options;
	}

	/**
	 * The list of supported Google maps api languages.
	 *
	 * @return array
	 */
	public static function supported_map_languages() {
		return [
			'ar'    => __( 'ARABIC', 'geodirectory' ),
			'eu'    => __( 'BASQUE', 'geodirectory' ),
			'bg'    => __( 'BULGARIAN', 'geodirectory' ),
			'bn'    => __( 'BENGALI', 'geodirectory' ),
			'ca'    => __( 'CATALAN', 'geodirectory' ),
			'cs'    => __( 'CZECH', 'geodirectory' ),
			'da'    => __( 'DANISH', 'geodirectory' ),
			'de'    => __( 'GERMAN', 'geodirectory' ),
			'el'    => __( 'GREEK', 'geodirectory' ),
			'en'    => __( 'ENGLISH', 'geodirectory' ),
			'en-AU' => __( 'ENGLISH (AUSTRALIAN)', 'geodirectory' ),
			'en-GB' => __( 'ENGLISH (GREAT BRITAIN)', 'geodirectory' ),
			'es'    => __( 'SPANISH', 'geodirectory' ),
			'fa'    => __( 'FARSI', 'geodirectory' ),
			'fi'    => __( 'FINNISH', 'geodirectory' ),
			'fil'   => __( 'FILIPINO', 'geodirectory' ),
			'fr'    => __( 'FRENCH', 'geodirectory' ),
			'gl'    => __( 'GALICIAN', 'geodirectory' ),
			'gu'    => __( 'GUJARATI', 'geodirectory' ),
			'hi'    => __( 'HINDI', 'geodirectory' ),
			'hr'    => __( 'CROATIAN', 'geodirectory' ),
			'hu'    => __( 'HUNGARIAN', 'geodirectory' ),
			'id'    => __( 'INDONESIAN', 'geodirectory' ),
			'it'    => __( 'ITALIAN', 'geodirectory' ),
			'iw'    => __( 'HEBREW', 'geodirectory' ),
			'ja'    => __( 'JAPANESE', 'geodirectory' ),
			'kn'    => __( 'KANNADA', 'geodirectory' ),
			'ko'    => __( 'KOREAN', 'geodirectory' ),
			'lt'    => __( 'LITHUANIAN', 'geodirectory' ),
			'lv'    => __( 'LATVIAN', 'geodirectory' ),
			'ml'    => __( 'MALAYALAM', 'geodirectory' ),
			'mr'    => __( 'MARATHI', 'geodirectory' ),
			'nl'    => __( 'DUTCH', 'geodirectory' ),
			'no'    => __( 'NORWEGIAN', 'geodirectory' ),
			'pl'    => __( 'POLISH', 'geodirectory' ),
			'pt'    => __( 'PORTUGUESE', 'geodirectory' ),
			'pt-BR' => __( 'PORTUGUESE (BRAZIL)', 'geodirectory' ),
			'pt-PT' => __( 'PORTUGUESE (PORTUGAL)', 'geodirectory' ),
			'ro'    => __( 'ROMANIAN', 'geodirectory' ),
			'ru'    => __( 'RUSSIAN', 'geodirectory' ),
			'sk'    => __( 'SLOVAK', 'geodirectory' ),
			'sl'    => __( 'SLOVENIAN', 'geodirectory' ),
			'sr'    => __( 'SERBIAN', 'geodirectory' ),
			'sv'    => __( 'SWEDISH', 'geodirectory' ),
			'tl'    => __( 'TAGALOG', 'geodirectory' ),
			'ta'    => __( 'TAMIL', 'geodirectory' ),
			'te'    => __( 'TELUGU', 'geodirectory' ),
			'th'    => __( 'THAI', 'geodirectory' ),
			'tr'    => __( 'TURKISH', 'geodirectory' ),
			'uk'    => __( 'UKRAINIAN', 'geodirectory' ),
			'vi'    => __( 'VIETNAMESE', 'geodirectory' ),
			'zh-CN' => __( 'CHINESE (SIMPLIFIED)', 'geodirectory' ),
			'zh-TW' => __( 'CHINESE (TRADITIONAL)', 'geodirectory' ),
		];
	}

	/**
	 * Generates the HTML content displaying the available email tags.
	 *
	 * @param string $type The type of email context for which the tags will be retrieved.
	 *
	 * @return string The formatted HTML content containing the template tags.
	 */
	public static function get_email_tags_html( $type ) {
		// This static function's logic remains unchanged.
		$tags        = [];
		$global_tags = [
			'[#blogname#]',
			'[#site_name#]',
			'[#site_url#]',
			'[#site_name_url#]',
			'[#login_url#]',
			'[#login_link#]',
			'[#date#]',
			'[#time#]',
			'[#date_time#]',
			'[#current_date#]',
			'[#to_name#]',
			'[#to_email#]',
			'[#from_name#]',
			'[#from_email#]'
		];
		$global_tags = apply_filters( 'geodir_email_global_email_tags', $global_tags );

		switch ( $type ) {
			case 'admin_post_edit':
			case 'user_pending_post':
			case 'user_publish_post':
			case 'admin_pending_post':
				$tags = array_merge( $global_tags, [
					'[#post_id#]',
					'[#post_status#]',
					'[#post_date#]',
					'[#post_author_ID#]',
					'[#post_author_name#]',
					'[#client_name#]',
					'[#listing_title#]',
					'[#listing_url#]',
					'[#listing_link#]'
				] );
				break;
			case 'admin_moderate_comment':
				$tags = array_merge( $global_tags, [
					'[#post_id#]',
					'[#post_status#]',
					'[#post_date#]',
					'[#post_author_ID#]',
					'[#post_author_name#]',
					'[#client_name#]',
					'[#listing_title#]',
					'[#listing_url#]',
					'[#listing_link#]',
					'[#comment_ID#]',
					'[#comment_author#]',
					'[#comment_author_IP#]',
					'[#comment_author_email#]',
					'[#comment_date#]',
					'[#comment_content#]',
					'[#comment_post_ID#]',
					'[#comment_post_title#]',
					'[#comment_post_url#]',
					'[#comment_approve_link#]',
					'[#comment_trash_link#]',
					'[#comment_spam_link#]',
					'[#comment_moderation_link#]',
					'[#review_rating_star#]',
					'[#review_rating_title#]',
					'[#review_city#]',
					'[#review_region#]',
					'[#review_country#]',
					'[#review_latitude#]',
					'[#review_longitude#]'
				] );
				break;
		}

		$tags = apply_filters( 'geodir_email_tags', $tags, $type );

		if ( ! empty( $tags ) ) {
			return __( 'Available template tags:', 'geodirectory' ) . '<code>' . implode( '</code> <code>', $tags ) . '</code>';
		}

		return '';
	}

	/**
	 * Get the array of possible single page templates to use.
	 *
	 * @return array
	 */
	public static function single_page_templates() {
		// This static function's logic remains unchanged.
		$templates = [ '' => __( "Auto", "geodirectory" ) ];

		if ( locate_template( 'single.php' ) ) {
			$templates['single.php'] = 'single.php';
		}
		if ( locate_template( 'singular.php' ) ) {
			$templates['singular.php'] = 'singular.php';
		}
		if ( locate_template( 'index.php' ) ) {
			$templates['index.php'] = 'index.php';
		}
		if ( locate_template( 'page.php' ) ) {
			$templates['page.php'] = 'page.php';
		}

		$page_templates = wp_get_theme()->get_page_templates( null, 'page' );
		if ( ! empty( $page_templates ) ) {
			foreach ( $page_templates as $name => $page ) {
				$templates[ $page ] = $page . " ( " . $name . " )";
			}
		}

		return $templates;
	}
	// endregion
}

<?php
/**
 * This is the main GeoDirectory plugin file, here we declare and call the important stuff
 *
 * @package     GeoDirectory
 * @copyright   2019 AyeCode Ltd
 * @license     GPL-2.0+
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: GeoDirectory
 * Plugin URI: https://wpgeodirectory.com/
 * Description: GeoDirectory - Business Directory Plugin for WordPress.
 * Version: 2.8.125
 * Author: AyeCode - WP Business Directory Plugins
 * Author URI: https://wpgeodirectory.com
 * Text Domain: geodirectory
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 */

if ( ! class_exists( 'GeoDirectory' ) ) :

	/**
	 * Main GeoDirectory Class.
	 *
	 * @class GeoDirectory
	 * @version 2.0.0
	 */
	final class GeoDirectory {
		/**
		 * GeoDirectory version.
		 *
		 * @var string
		 */
		public $version = '2.8.125';

		/**
		 * GeoDirectory instance.
		 *
		 * @access private
		 * @since  2.0.0
		 * @var    GeoDirectory The one true GeoDirectory
		 */
		private static $instance = null;

		/**
		 * The settings instance variable
		 *
		 * @access public
		 * @since  2.0.0
		 * @var    GeoDirectory_Settings
		 */
		public $settings;

		/**
		 * Query instance.
		 *
		 * @var GeoDir_Query
		 */
		public $query = null;

		/**
		 * API instance
		 *
		 * @var GeoDir_API
		 */
		public $api;

		/**
		 * API instance
		 *
		 * @var GeoDir_Location
		 */
		public $location;

		/**
		 * API instance
		 *
		 * @var GeoDir_Permalinks
		 */
		public $permalinks;

		/**
		 * API instance
		 *
		 * @var GeoDir_Taxonomies
		 */
		public $taxonomies;

		/**
		 * API instance
		 *
		 * @var GeoDir_Notifications
		 */
		public $notifications = null;

		/**
		 * Main GeoDirectory Instance.
		 *
		 * Ensures only one instance of GeoDirectory is loaded or can be loaded.
		 *
		 * @since 2.0.0
		 * @static
		 * @see GeoDir()
		 * @return GeoDirectory - Main instance.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof GeoDirectory ) ) {
				self::$instance = new GeoDirectory;
				self::$instance->setup_constants();

				add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );

				if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
					add_action( 'admin_notices', array( self::$instance, 'php_version_notice' ) );

					return self::$instance;
				}

				self::$instance->includes();
				self::$instance->init_hooks();

				do_action( 'geodirectory_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 2.0.0
		 * @return void
		 */
		private function setup_constants() {
			global $wpdb, $plugin_prefix;

			$upload_dir = wp_upload_dir( null, false );
			$plugin_prefix = $wpdb->prefix . 'geodir_';

			if ( $this->is_request( 'test' ) ) {
				$plugin_path = dirname( __FILE__ );
			} else {
				$plugin_path = plugin_dir_path( __FILE__ );
			}

			$this->define( 'GEODIRECTORY_VERSION', $this->version );
			$this->define( 'GEODIRECTORY_PLUGIN_FILE', __FILE__ );
			$this->define( 'GEODIRECTORY_PLUGIN_DIR', $plugin_path );
			$this->define( 'GEODIRECTORY_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			$this->define( 'GEODIRECTORY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
			$this->define( 'GEODIRECTORY_TEXTDOMAIN', 'geodirectory' );

			// Database tables
			$this->define( 'GEODIR_API_KEYS_TABLE', $plugin_prefix . 'api_keys' ); // rest api keys table
			$this->define( 'GEODIR_ATTACHMENT_TABLE', $plugin_prefix . 'attachments' ); // attachments table
			$this->define( 'GEODIR_CUSTOM_FIELDS_TABLE', $plugin_prefix . 'custom_fields' ); // custom fields table
			$this->define( 'GEODIR_TABS_LAYOUT_TABLE', $plugin_prefix . 'tabs_layout' ); // custom fields table
			$this->define( 'GEODIR_CUSTOM_SORT_FIELDS_TABLE', $plugin_prefix . 'custom_sort_fields' ); // custom sort fields table
			$this->define( 'GEODIR_REVIEW_TABLE', $plugin_prefix . 'post_review' ); // post review table
			$this->define( 'GEODIR_POST_REPORTS_TABLE', $plugin_prefix . 'post_reports' ); // post reports table

			$this->define( 'GEODIR_ROUNDING_PRECISION', 4 );

			// Do not store any revisions (except the one autosave per post).
			$this->define( 'WP_POST_REVISIONS', 0 );

			$this->define( 'GEODIR_REST_SLUG', 'geodir' );
			$this->define( 'GEODIR_REST_API_VERSION', '2' );
		}

		/**
		 * Loads the plugin language files
		 *
		 * @access public
		 * @since 2.0.0
		 * @return void
		 */
		public function load_textdomain() {
			$locale = determine_locale();

			/**
			 * Filter the plugin locale.
			 *
			 * @since   1.4.2
			 * @package GeoDirectory
			 */
			$locale = apply_filters( 'plugin_locale', $locale, 'geodirectory' );

			unload_textdomain( 'geodirectory', true );
			load_textdomain( 'geodirectory', WP_LANG_DIR . '/geodirectory/geodirectory-' . $locale . '.mo' );
			load_plugin_textdomain( 'geodirectory', false, basename( dirname( GEODIRECTORY_PLUGIN_FILE ) ) . '/languages/' );
		}

		/**
		 * Show a warning to sites running PHP < 5.3
		 *
		 * @static
		 * @access private
		 * @since 2.0.0
		 * @return void
		 */
		public static function php_version_notice() {
			echo '<div class="error"><p>' . esc_html__( 'Your version of PHP is below the minimum version of PHP required by GeoDirectory. Please contact your host and request that your version be upgraded to 5.3 or later.', 'geodirectory' ) . '</p></div>';
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 2.0.0
		 * @return void
		 */
		private function includes() {
			global $pagenow, $geodir_options, $wp_version;

			// composer autoloader
			require_once( GEODIRECTORY_PLUGIN_DIR . 'vendor/autoload.php' );

			/**
			 * Class autoloader.
			 */
			include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-autoloader.php' );

			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/formatting-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/deprecated-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/core-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-datetime.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/settings/functions.php' );
			$this->settings = $geodir_options = geodir_get_settings();

			include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-post-types.php' ); // Registers post types

			GeoDir_Email::init();// set up the email class
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/helper-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/validation-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/user-functions.php' );
			GeoDir_AJAX::init();
			GeoDir_Post_Data::init(); // post data
			GeoDir_Post_Limit::init(); // Posts limit
			//GeoDir_Post_Revision::init(); // post revisions @todo not implemented yet
			GeoDir_Compatibility::init(); // plugin/theme comaptibility checks
			GeoDir_Classifieds::init();

			if( defined( 'ELEMENTOR_VERSION' ) ){
				GeoDir_Elementor::init();
			}

			// Block Theme comaptibility
			if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
				GeoDir_Block_Theme::init();
			}

			GeoDir_SEO::init();

			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/general-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/template-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/post-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/image-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/post-types-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/taxonomy-functions.php' );
			if ( geodir_design_style() ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom-fields/input-functions-aui.php' );
			} else {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom-fields/input-functions.php' );
			}
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom-fields/output-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom-fields/output-filter-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom-fields/functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/comments-functions.php' );
			GeoDir_Comments::init();
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/location-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/business-hours-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/classifieds-functions.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-maps.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-frontend-scripts.php' );
			//require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-permalinks.php' );

			if ( geodir_design_style() ) {
				GeoDir_Report_Post::init(); // Report Post
			}

			// BIG Data
			if(!empty($this->settings['enable_big_data'])){
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-big-data.php' );
			}


			/**
			 * REST API.
			 */
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-api.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-auth.php' );
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-register-wp-admin-settings.php' );

			if ( $this->is_request( 'admin' ) || $this->is_request( 'test' ) || $this->is_request( 'cli' ) ) {
				if ( !empty( $_REQUEST['taxonomy'] ) ) {
					require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/class-geodir-admin-taxonomies.php' );
				}

				new GeoDir_Admin(); // init the GD admin class

				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin-functions.php' );
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/dashboard-functions.php' );
				GeoDir_Admin_Install::init(); // init the install class
				GeoDir_Admin_Upgrade::init(); // init the upgrade class
				GeoDir_Admin_Tracker::init(); // tracking data
				require_once( GEODIRECTORY_PLUGIN_DIR . 'upgrade.php' );

				if( 'plugins.php' === $pagenow ) {
					// Better update message
					$file   = basename( GEODIRECTORY_PLUGIN_FILE );
					$folder = basename( dirname( GEODIRECTORY_PLUGIN_FILE ) );
					$hook   = "in_plugin_update_message-{$folder}/{$file}";
					add_action( $hook, 'geodir_admin_upgrade_notice', 20, 2 );
				}

				if ( 'edit.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' == $pagenow || ( defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'inline-save' ) ) {
					GeoDir_Admin_Post_View::init();
				}
			}

			if ( $this->is_request( 'frontend' ) ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-template-loader.php' ); // Template Loader
			}

			// If current WP Version >= 4.9.6.
			if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-privacy.php' );
			}

			// @todo not ready for production yet
			//require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-fse.php' );

			$theme = wp_get_theme();

			if ( 'blockstrap' === $theme->get_stylesheet() || 'blockstrap' === $theme->get_template() ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-blockstrap.php' );
			} else if ( 'Bricks' === $theme->get( 'Name' ) || 'bricks' === $theme->get( 'Template' ) ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/class-geodir-bricks.php' );
				require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/class-geodir-bricks-query-filters.php' );
			}

			$this->query = new GeoDir_Query();
			$this->api   = new GeoDir_API();
		}

		/**
		 * Hook into actions and filters.
		 * @since  2.3
		 */
		private function init_hooks() {
			register_activation_hook( __FILE__, array( $this, 'on_activate' ) );
			register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );

			add_action( 'init', array( $this, 'init' ), 0 );
		}

		/**
		 * Handle plugin activate action.
		 *
		 * @since 2.2.7
		 *
		 * @param bool $network_wide Optional. Whether to enable the plugin for all sites in the network
		 *                             or just the current site. Multisite only. Default false.
		 */
		public function on_activate( $network_wide = false ) {
			if ( ! defined( 'GEODIR_ACTIVATING' ) ) {
				define( 'GEODIR_ACTIVATING', true );
			}

			/**
			 * Fired on plugin activated.
			 *
			 * @param bool $network_wide Whether to enable the plugin for all sites in the network
			 *                             or just the current site. Multisite only. Default false.
			 */
			do_action( 'geodir_on_activate_core_plugin', $network_wide );
		}

		/**
		 * Handle plugin deactivate action.
		 *
		 * @since 2.2.7
		 *
		 * @param bool $network_wide Optional. Whether to enable the plugin for all sites in the network
		 *                             or just the current site. Multisite only. Default false.
		 */
		public function on_deactivate( $network_wide = false ) {
			if ( ! defined( 'GEODIR_DEACTIVATING' ) ) {
				define( 'GEODIR_DEACTIVATING', true );
			}

			// Delete Fast AJAX mu-plugin file.
			if ( is_file( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' ) && file_exists( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' ) ) {
				unlink( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' );
			}

			/**
			 * Fired on plugin deactivated.
			 *
			 * @param bool $network_wide Whether to enable the plugin for all sites in the network
			 *                             or just the current site. Multisite only. Default false.
			 */
			do_action( 'geodir_on_deactivate_core_plugin', $network_wide );
		}

		/**
		 * Init GeoDirectory when WordPress Initialises.
		 */
		public function init() {
			if ( ! defined( 'GEODIR_LATITUDE_ERROR_MSG' ) ) {
				require_once( GEODIRECTORY_PLUGIN_DIR . 'language.php' ); // Define language constants.
			}

			// Before init action.
			do_action( 'geodirectory_before_init' );

			// locations
			$location_class_name = apply_filters('geodir_class_location','GeoDir_Location');
			$this->location = new $location_class_name;

			// permalinks
			$permalinks_class_name = apply_filters('geodir_class_permalinks','GeoDir_Permalinks');
			$this->permalinks = new $permalinks_class_name;

			// taxonomies
			$taxonomies_class_name = apply_filters('geodir_class_taxonomies','GeoDir_Taxonomies');
			$this->taxonomies = new $taxonomies_class_name;

			// notifications
			$notifications_class_name = apply_filters('geodir_class_notifications','GeoDir_Notifications');
			$this->notifications = new $notifications_class_name;

			// GD hints
			if(geodir_get_option('enable_hints',1)){
				if(current_user_can('administrator')) {
					new GeoDir_Hints();
				}
			}

			// Init action.
			do_action( 'geodirectory_init' );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Request type.
		 *
		 * @param  string $type admin, frontend, ajax, cron, test or CLI.
		 * @return bool
		 */
		private function is_request( $type ) {
			switch ( $type ) {
				case 'admin' :
					return is_admin();
					break;
				case 'ajax' :
					return defined( 'DOING_AJAX' );
					break;
				case 'cli' :
					return ( defined( 'WP_CLI' ) && WP_CLI );
					break;
				case 'cron' :
					return defined( 'DOING_CRON' );
					break;
				case 'frontend' :
					return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
					break;
				case 'test' :
					return defined( 'GD_TESTING_MODE' );
					break;
			}

			return null;
		}

		/**
		 * Check the active theme.
		 *
		 * @since  2.0.0
		 * @param  string $theme Theme slug to check.
		 * @return bool
		 */
		private function is_active_theme( $theme ) {
			return get_template() === $theme;
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			return untrailingslashit( plugins_url( '/', GEODIRECTORY_PLUGIN_FILE ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( GEODIRECTORY_PLUGIN_FILE ) );
		}

		/**
		 * Get the template path.
		 *
		 * @return string
		 */
		public function template_path() {
			return trailingslashit( geodir_get_theme_template_dir_name() );
		}

		/**
		 * Get Ajax URL.
		 *
		 * @return string
		 */
		public function ajax_url() {
			return admin_url( 'admin-ajax.php', 'relative' );
		}
	}

endif;

/**
 * The main function responsible for returning the one true GeoDirectory
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $geodirectory = geodirectory(); ?>
 *
 * @since 2.0.0
 * @return GeoDirectory The one true GeoDirectory Instance
 */
function GeoDir() {
	return GeoDirectory::instance();
}

// Global for backwards compatibility.
$GLOBALS['geodirectory'] = GeoDir();

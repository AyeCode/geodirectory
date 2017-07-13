<?php
/**
 * This is the main GeoDirectory plugin file, here we declare and call the important stuff
 *
 * @package     GeoDirectory
 * @copyright   2016 AyeCode Ltd
 * @license     GPL-2.0+
 * @since       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: GeoDirectory
 * Plugin URI: http://wpgeodirectory.com/
 * Description: GeoDirectory plugin for wordpress.
 * Version: 1.6.21
 * Author: GeoDirectory
 * Author URI: http://wpgeodirectory.com
 * Text Domain: geodirectory
 * Domain Path: /geodirectory-languages
 * Requires at least: 3.1
 * Tested up to: 4.8
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
    public $version = '1.6.21';
    
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
        global $wpdb, $plugin_prefix, $geodir_post_custom_fields_cache;
        
        $plugin_prefix = $wpdb->prefix . 'geodir_';
        
        if ( $this->is_request( 'test' ) ) {
            $plugin_path = dirname( __FILE__ );
        } else {
            $plugin_path = plugin_dir_path( __FILE__ );
        }
        
        $this->define( 'GEODIRECTORY_VERSION', $this->version );
        $this->define( 'GEODIRECTORY_PLUGIN_FILE', __FILE__ );
        $this->define( 'GEODIRECTORY_PLUGIN_DIR', $plugin_path );
        $this->define( 'GEODIRECTORY_TEXTDOMAIN', 'geodirectory' );
        
        // Database tables
        $this->define( 'GEODIR_ATTACHMENT_TABLE', $plugin_prefix . 'attachments' ); // attachments table
        $this->define( 'GEODIR_COUNTRIES_TABLE', $plugin_prefix . 'countries' ); // countries table
        $this->define( 'GEODIR_CUSTOM_FIELDS_TABLE', $plugin_prefix . 'custom_fields' ); // custom fields table
        $this->define( 'GEODIR_CUSTOM_SORT_FIELDS_TABLE', $plugin_prefix . 'custom_sort_fields' ); // custom sort fields table
        $this->define( 'GEODIR_ICON_TABLE', $plugin_prefix . 'post_icon' ); // post icon table
        $this->define( 'GEODIR_REVIEW_TABLE', $plugin_prefix . 'post_review' ); // post review table
        
        // Google Analytic app settings
        $this->define( 'GEODIR_GA_CLIENTID', '687912069872-sdpsjssrdt7t3ao1dnv1ib71hkckbt5s.apps.googleusercontent.com' );
        $this->define( 'GEODIR_GA_CLIENTSECRET', 'yBVkDpqJ1B9nAETHy738Zn8C' ); // don't worry - this don't need to be secret in our case
        $this->define( 'GEODIR_GA_REDIRECT', 'urn:ietf:wg:oauth:2.0:oob' );
        $this->define( 'GEODIR_GA_SCOPE', 'https://www.googleapis.com/auth/analytics' ); // .readonly
        
        // Do not store any revisions (except the one autosave per post).
        $this->define( 'WP_POST_REVISIONS', 0 );
        
        // This will store the cached post custom fields per package for each page load so not to run for each listing.
        $geodir_post_custom_fields_cache = array();
    }
    
    /**
     * Loads the plugin language files
     *
     * @access public
     * @since 2.0.0
     * @return void
     */
    public function load_textdomain() {
        global $wp_version;
        
        $locale = $wp_version >= 4.7 ? get_user_locale() : get_locale();
        
        /**
         * Filter the plugin locale.
         *
         * @since   1.4.2
         * @package GeoDirectory
         */
        $locale = apply_filters( 'plugin_locale', $locale, 'geodirectory' );

        load_textdomain( 'geodirectory', WP_LANG_DIR . '/' . 'geodirectory' . '/' . 'geodirectory' . '-' . $locale . '.mo' );
        load_plugin_textdomain( 'geodirectory', FALSE, basename( dirname( GEODIRECTORY_PLUGIN_FILE ) ) . '/languages/' );
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
        echo '<div class="error"><p>' . __( 'Your version of PHP is below the minimum version of PHP required by GeoDirectory. Please contact your host and request that your version be upgraded to 5.3 or later.', 'geodirectory' ) . '</p></div>';
    }
    
    /**
     * Include required files.
     *
     * @access private
     * @since 2.0.0
     * @return void
     */
    private function includes() {
        global $pagenow, $geodir_options;
        
        /**
         * Class autoloader.
         */
        include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-autoloader.php' );
        
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/core-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/settings/register-settings.php' );
        $geodir_options = geodir_get_settings();
        
        if ( !defined( 'GEODIR_LATITUDE_ERROR_MSG' ) ) {
            require_once( GEODIRECTORY_PLUGIN_DIR . 'language.php' ); // Define language constants.
        }
        
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-session.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/email-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/helper_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/user-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/deprecated-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodir-ajax-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/general_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/listing_filters.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/template-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/account-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/post_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/post-types-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/taxonomy-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom_fields_input_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom_fields_output_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom_fields_predefined.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodir-custom-fields-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/comments_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/location_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/google_analytics.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodir-shortcode-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/custom_taxonomy_hooks_actions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodirectory_hooks_actions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodir-widget-functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/maps/map_functions.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/maps/map_template_tags.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodirectory_template_tags.php' );
        require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/geodirectory_template_actions.php' );
        
        if ( $this->is_request( 'admin' ) || $this->is_request( 'test' ) || $this->is_request( 'cli' ) ) {
            if ( !empty( $_REQUEST['taxonomy'] ) ) {
                require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/class-geodir-admin-taxonomies.php' );
            }
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin_functions.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin_dummy_data_functions.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin_hooks_actions.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin_template_tags.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/class.analytics.stats.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/install.php' );
            require_once( GEODIRECTORY_PLUGIN_DIR . 'upgrade.php' );

            if( 'plugins.php' === $pagenow ) {
                // Better update message
                $file   = basename( GEODIRECTORY_PLUGIN_FILE );
                $folder = basename( dirname( GEODIRECTORY_PLUGIN_FILE ) );
                $hook   = "in_plugin_update_message-{$folder}/{$file}";
                add_action( $hook, 'geodire_admin_upgrade_notice', 20, 2 );
            }
        }
        
        $this->load_db_language();
        
        if ( $this->is_request( 'frontend' ) ) {
            require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-shortcodes.php' ); // Shortcodes class
        }
    }
    
    /**
     * Load the language for custom fields, custom text.
     */
    public function load_db_language() {
        $language_file = GEODIRECTORY_PLUGIN_DIR . 'db-language.php';
        
        // Load language string file if not created yet.
        if ( ! file_exists( $language_file ) ) {
            geodirectory_load_db_language();
        }

        if ( file_exists( $language_file ) ) {
            try {
                require_once( $language_file );
            } catch ( Exception $e ) {
                geodir_error_log( $e->getMessage(), 'Language Error' );
            }
        }
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
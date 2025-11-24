<?php
/**
 * GeoDirectory v3
 *
 * @package     GeoDirectory
 * @author      AyeCode Ltd
 * @copyright   2025 AyeCode Ltd
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: GeoDirectory
 * Plugin URI: https://wpgeodirectory.com/
 * Description: GeoDirectory - Business Directory Plugin for WordPress.
 * Version: 3.0.0
 * Author: AyeCode Ltd
 * Author URI: https://ayecode.io
 * Text Domain: geodirectory
 * Domain Path: /languages
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

// Block direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Load the Composer autoloader for classes and the main helper function.
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/functions.php';

// Functions (to be moved to helpers)
require_once __DIR__ . '/inc/wrapper-functions.php';
require_once __DIR__ . '/inc/map-functions.php';
require_once __DIR__ . '/inc/helper-functions.php';
require_once __DIR__ . '/inc/location-functions.php';
require_once __DIR__ . '/inc/core-functions.php';
require_once __DIR__ . '/inc/general-functions.php';
require_once __DIR__ . '/inc/image-functions.php';
require_once __DIR__ . '/inc/rest-functions.php';
require_once __DIR__ . '/inc/template-functions.php';
require_once __DIR__ . '/inc/business-hours-functions.php';
require_once __DIR__ . '/inc/formatting-functions.php';
require_once __DIR__ . '/inc/post-types-functions.php';
require_once __DIR__ . '/inc/post-functions.php';
require_once __DIR__ . '/inc/field-conditional-functions.php';

// 2. Define essential constants.
define( 'GEODIRECTORY_VERSION', '3.0.0' );
define( 'GEODIRECTORY_FILE', __FILE__ );
define( 'GEODIRECTORY_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'GEODIRECTORY_PLUGIN_URL', untrailingslashit( plugins_url( '/', __FILE__ ) )  );
define( 'GEODIR_REST_SLUG', 'geodir');
define( 'GEODIR_REST_API_VERSION', '2');


// 3. Initialize the core path helper. This is done early as it has no dependencies.
\AyeCode\GeoDirectory\Core\Plugin::init( GEODIRECTORY_FILE );

/**
 * The main bootstrap function for the plugin.
 *
 * This function is responsible for building the service container, registering
 * all core services, and initializing the main plugin object.
 *
 * @return void
 */
function geodirectory_boot() {

	// --- Initialize the Loader ---
	// This registers all the AJAX actions and panes.
	new \AyeCode\GeoDirectory\Loader();

	// --- Build the Service Container ---
	$container = new \AyeCode\GeoDirectory\Core\Container();

	// --- Register all core services with the container ---
	// This is the "wiring" for the entire plugin. We tell the container
	// how to build each service.

	// Core Services (Business Logic & Utilities)
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Tables::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface::class, \AyeCode\GeoDirectory\Core\Services\Locations::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Geolocation::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\LocationFormatter::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Utils\Formatter::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Images::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Debug::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\BusinessHours::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Templates::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Utils\Helpers::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Reviews::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Media::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Settings::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Utils\Utils::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Maps::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Statuses::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\PostTypes::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Posts::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Services\Taxonomies::class );

	// Fields Service
	$container->bind( \AyeCode\GeoDirectory\Fields\FieldsService::class );
	$container->bind( \AyeCode\GeoDirectory\Fields\FieldRegistry::class );

	// Database Repositories (Data Layer)
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\ReviewRepository::class );
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository::class );
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\AttachmentRepository::class );
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\PostRepository::class );

	// Common Services (CPTs, Taxonomies, etc.)
	$container->bind( \AyeCode\GeoDirectory\Common\CptConfig::class );
	$container->bind( \AyeCode\GeoDirectory\Common\PostTypesRegistrar::class );
	$container->bind( \AyeCode\GeoDirectory\Common\TaxonomiesRegistrar::class );
	$container->bind( \AyeCode\GeoDirectory\Common\PostStatusesRegistrar::class );
	$container->bind( \AyeCode\GeoDirectory\Common\Assets::class );

	// Frontend Services (Rendering & Hooks)
	$container->bind( \AyeCode\GeoDirectory\Frontend\ReviewForm::class );
	$container->bind( \AyeCode\GeoDirectory\Frontend\ReviewHooks::class );
	$container->bind( \AyeCode\GeoDirectory\Frontend\PostHooks::class );
	$container->bind( \AyeCode\GeoDirectory\Frontend\Ajax\FileUploadAction::class );

	// Service Providers (Orchestrators)
	$container->bind( \AyeCode\GeoDirectory\Common\CommonServiceProvider::class );
	$container->bind( \AyeCode\GeoDirectory\Admin\AdminServiceProvider::class );
	$container->bind( \AyeCode\GeoDirectory\Frontend\FrontendServiceProvider::class );


	// --- Initialize the main plugin object ---
	// We pass the fully configured container to our main helper function.
	// This creates the main GeoDirectory object and makes it globally available.
	geodirectory( $container );

	// --- Run the Service Providers ---
	// Now that everything is registered, we tell the providers to add their hooks.
	$container->get( \AyeCode\GeoDirectory\Common\CommonServiceProvider::class )->register_hooks();

	if ( is_admin() ) {
		$container->get( \AyeCode\GeoDirectory\Admin\AdminServiceProvider::class )->register_hooks();
	} else {
		$container->get( \AyeCode\GeoDirectory\Frontend\FrontendServiceProvider::class )->register_hooks();
	}
}

// 4. Run the bootstrap function on the 'plugins_loaded' hook.
add_action( 'plugins_loaded', 'geodirectory_boot' );

// 5. Set up activation & deactivation hooks.
register_activation_hook( GEODIRECTORY_FILE, [ '\AyeCode\GeoDirectory\Core\Lifecycle', 'activate' ] );
register_deactivation_hook( GEODIRECTORY_FILE, [ '\AyeCode\GeoDirectory\Core\Lifecycle', 'deactivate' ] );

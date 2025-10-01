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

// 2. Define essential constants.
define( 'GEODIRECTORY_VERSION', '3.0.0' );
define( 'GEODIRECTORY_FILE', __FILE__ );

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
	$container->bind( \AyeCode\GeoDirectory\Core\Tables::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Interfaces\LocationsInterface::class, \AyeCode\GeoDirectory\Core\Locations::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Reviews::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Media::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Utils\Settings::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Utils\Utils::class );
	$container->bind( \AyeCode\GeoDirectory\Core\Statuses::class );

	// Database Repositories (Data Layer)
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\ReviewRepository::class );
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\CustomFieldRepository::class );
	$container->bind( \AyeCode\GeoDirectory\Database\Repository\AttachmentRepository::class );

	// Common Services (CPTs, Taxonomies, etc.)
	$container->bind( \AyeCode\GeoDirectory\Common\CptConfig::class );
	$container->bind( \AyeCode\GeoDirectory\Common\PostTypes::class );
	$container->bind( \AyeCode\GeoDirectory\Common\Taxonomies::class );
	$container->bind( \AyeCode\GeoDirectory\Common\PostStatuses::class );

	// Frontend Services (Rendering & Hooks)
	$container->bind( \AyeCode\GeoDirectory\Frontend\ReviewForm::class );
	$container->bind( \AyeCode\GeoDirectory\Frontend\ReviewHooks::class );
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

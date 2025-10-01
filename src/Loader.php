<?php
/**
 * GeoDirectory Loader
 *
 * @package GeoDirectory
 */

declare(strict_types=1);

namespace AyeCode\GeoDirectory;

use AyeCode\GeoDirectory\Admin\CptSettingsManager;
use AyeCode\GeoDirectory\Admin\Pages\ModerationPage;
use AyeCode\GeoDirectory\Admin\Pages\SettingsPage;
use AyeCode\GeoDirectory\Admin\Pages\ToolsPage;
use AyeCode\GeoDirectory\Ajax\ActionRegistry;
use AyeCode\GeoDirectory\Ajax\AjaxHandler;
use AyeCode\GeoDirectory\Ajax\PaneRegistry;
use AyeCode\GeoDirectory\Core\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main bootstrap for GeoDirectory.
 */
final class Loader {

	public function __construct() {
		// --- FIXED ---
		// We must register actions on 'init' so it runs during AJAX requests.
		// We give it a priority of -2 so it's guaranteed to run BEFORE maybe_init_ajax() at -1.
		add_action( 'init', [ $this, 'register' ], -2 );

		// Must be on init so the settings framework can attach admin_menu.
		add_action( 'init', [ $this, 'boot_admin' ], 10 );

		// Shared light includes.
		add_action( 'init', [ $this, 'init' ], 0 );

		// AJAX router only when needed.
		add_action( 'init', [ $this, 'maybe_init_ajax' ], -1 );
	}

	/**
	 * Register tool & pane maps (no heavy work).
	 */
	public function register(): void {
		$this->register_actions();
		$this->register_panes();
	}

	/**
	 * Instantiate admin classes on init so their framework can add menus.
	 */
	public function boot_admin(): void {
		if ( is_admin() && ! wp_doing_ajax() ) {
			// Logic for non-AJAX admin pages.

		}

		// Logic for AJAX admin pages.
		if ( is_admin() ||  wp_doing_ajax() ) {
			new SettingsPage();
			new ModerationPage();
			new ToolsPage();
			( new CptSettingsManager() )->init();
		}



	}

	/**
	 * Shared init for front & admin: include lightweight functions.
	 */
	public function init(): void {
		$functions_file = Plugin::path(  'inc/map-functions.php' );
		if ( is_readable( $functions_file ) ) {
			require_once $functions_file;
		}
	}

	/**
	 * Instantiate the AJAX handler only for AJAX requests.
	 */
	public function maybe_init_ajax(): void {
		if ( wp_doing_ajax() ) {
			new AjaxHandler();
		}
	}

	/**
	 * Register all tools with the Action Registry.
	 */
	private function register_actions(): void {

		//print_r( $_POST );exit;

		// Core GeoDirectory Tools
		ActionRegistry::register( 'clear_version_numbers', \AyeCode\GeoDirectory\Ajax\Actions\Tools\ClearVersionNumbersAction::class );
		ActionRegistry::register( 'check_reviews',         \AyeCode\GeoDirectory\Ajax\Actions\Tools\CheckReviewsAction::class );
		ActionRegistry::register( 'install_pages',         \AyeCode\GeoDirectory\Ajax\Actions\Tools\InstallPagesAction::class );
		ActionRegistry::register( 'merge_missing_terms',   \AyeCode\GeoDirectory\Ajax\Actions\Tools\MergeMissingTermsAction::class );
		ActionRegistry::register( 'recount_terms',         \AyeCode\GeoDirectory\Ajax\Actions\Tools\RecountTermsAction::class );
		ActionRegistry::register( 'generate_keywords',     \AyeCode\GeoDirectory\Ajax\Actions\Tools\GenerateKeywordsAction::class );
		ActionRegistry::register( 'generate_thumbnails',   \AyeCode\GeoDirectory\Ajax\Actions\Tools\RegenerateThumbnailsAction::class );
		ActionRegistry::register( 'export_db_texts',       \AyeCode\GeoDirectory\Ajax\Actions\Tools\ExportDatabaseTextsAction::class );
		ActionRegistry::register( 'clear_paging_cache',    \AyeCode\GeoDirectory\Ajax\Actions\Tools\ClearPagingCacheAction::class );
		ActionRegistry::register( 'search_replace_cf',     \AyeCode\GeoDirectory\Ajax\Actions\Tools\SearchReplaceCustomFieldAction::class );

		// Export Process
		ActionRegistry::register( 'export_posts',    \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class );
		ActionRegistry::register( 'export_cats',     \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class );
		ActionRegistry::register( 'export_reviews',  \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class );
		ActionRegistry::register( 'export_settings', \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class );

		// Import Process
		ActionRegistry::register( 'import_settings', \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class );
		ActionRegistry::register( 'import_reviews',  \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class );
		ActionRegistry::register( 'import_cats',     \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class );
		ActionRegistry::register( 'import_listings', \AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class );

		// Dummy Data
		ActionRegistry::register( 'dummy_data_install',   \AyeCode\GeoDirectory\Ajax\Actions\Tools\InstallDummyDataAction::class );
		ActionRegistry::register( 'dummy_data_uninstall', \AyeCode\GeoDirectory\Ajax\Actions\Tools\UninstallDummyDataAction::class );

		// API Keys
		ActionRegistry::register( 'get_api_keys',   \AyeCode\GeoDirectory\Ajax\Actions\ApiKeys\GetApiKeysAction::class );
		ActionRegistry::register( 'create_api_key',   \AyeCode\GeoDirectory\Ajax\Actions\ApiKeys\CreateApiKeyAction::class );
		ActionRegistry::register( 'update_api_key',   \AyeCode\GeoDirectory\Ajax\Actions\ApiKeys\UpdateApiKeyAction::class );
		ActionRegistry::register( 'delete_api_key',   \AyeCode\GeoDirectory\Ajax\Actions\ApiKeys\DeleteApiKeyAction::class );

	}

	/**
	 * Register all panes with the Pane Registry.
	 */
	private function register_panes(): void {
		PaneRegistry::register( 'status_report', \AyeCode\GeoDirectory\Ajax\Actions\Panes\RenderStatusReportAction::class );
	}
}

<?php

namespace AyeCode\GeoDirectory;

use AyeCode\GeoDirectory\Ajax\ActionRegistry;
use AyeCode\GeoDirectory\Ajax\PaneRegistry;
use AyeCode\GeoDirectory\Ajax\AjaxHandler;

/**
 * Loader Class
 *
 * The main bootstrap class for the plugin. Its responsibility is to
 * instantiate other classes and register all necessary actions,
 * filters, and services.
 */
class Loader {

	/**
	 * Kicks off the plugin initialization.
	 */
	public function __construct() {
		// First, register everything so the system knows what's available.
		$this->register_actions();
		$this->register_panes();

		// Then, hook our main initializer into WordPress.
		add_action('init', [$this, 'init']);
	}

	/**
	 * The main initialization method.
	 *
	 * This method is hooked into 'init' and is responsible for setting up
	 * all major components of the plugin.
	 */
	public function init() {
		// Include non-class files.
		require_once GEODIRECTORY_PLUGIN_DIR . 'inc/map-functions.php';

		// The AjaxHandler is now instantiated here, ready for ANY type of
		// AJAX request (admin, public, etc.).
		new AjaxHandler();

		// Load admin-only classes.
		if (is_admin()) {
			new Admin\Settings();
			new Admin\Tools();
		}

		// Load public-only classes here if needed.
		// new Public\Something();
	}

	/**
	 * Registers all tools with our Action Registry.
	 * This populates the "phonebook" so the AjaxHandler knows what tools are available.
	 */
	private function register_actions() {
		// Core GeoDirectory Tools
		ActionRegistry::register(
			'clear_version_numbers',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\ClearVersionNumbersAction::class
		);
		ActionRegistry::register(
			'check_reviews',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\CheckReviewsAction::class
		);
		ActionRegistry::register(
			'install_pages',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\InstallPagesAction::class
		);
		ActionRegistry::register(
			'merge_missing_terms',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\MergeMissingTermsAction::class
		);
		ActionRegistry::register(
			'recount_terms',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\RecountTermsAction::class
		);
		ActionRegistry::register(
			'generate_keywords',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\GenerateKeywordsAction::class
		);
		ActionRegistry::register(
			'generate_thumbnails',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\RegenerateThumbnailsAction::class
		);
		ActionRegistry::register(
			'export_db_texts',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\ExportDatabaseTextsAction::class
		);
		ActionRegistry::register(
			'clear_paging_cache',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\ClearPagingCacheAction::class
		);
		ActionRegistry::register(
			'search_replace_cf',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\SearchReplaceCustomFieldAction::class
		);

		// --- Export Process Actions ---
		ActionRegistry::register(
			'export_posts', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class
		);
		ActionRegistry::register(
			'export_cats', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class
		);
		ActionRegistry::register(
			'export_reviews', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class
		);
		ActionRegistry::register(
			'export_settings', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ExportAction::class
		);

		// --- Import Process Actions ---
		ActionRegistry::register(
			'import_settings', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class
		);
		ActionRegistry::register(
			'import_reviews', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class
		);
		ActionRegistry::register(
			'import_cats', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class
		);
		ActionRegistry::register(
			'import_listings', // Use the tool name from your AJAX call.
			\AyeCode\GeoDirectory\Ajax\Actions\ImportExport\ImportAction::class
		);

		// --- Dummy Data Actions ---
		ActionRegistry::register(
			'dummy_data_install',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\InstallDummyDataAction::class
		);
		ActionRegistry::register(
			'dummy_data_uninstall',
			\AyeCode\GeoDirectory\Ajax\Actions\Tools\UninstallDummyDataAction::class
		);
	}

	/**
	 * Registers all HTML panes with our Pane Registry.
	 * This populates the "phonebook" for panes.
	 */
	private function register_panes() {
		PaneRegistry::register(
			'status_report',
			\AyeCode\GeoDirectory\Ajax\Actions\Panes\RenderStatusReportAction::class
		);
	}
}

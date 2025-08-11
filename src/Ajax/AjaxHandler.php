<?php
/**
 * Class AjaxHandler
 *
 * Handles AJAX-related operations for the GeoDirectory plugin.
 * @since   3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace GeoDirectory\Ajax;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class AjaxHandler {

	/**
	 * @var null
	 */
	private static $instance = null;

	/**
	 * Ensures only one instance of the class is loaded.
	 * This is a Singleton pattern.
	 *
	 * @return Settings
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	public function __construct() {

		add_action( 'asf_execute_tool_geodir_tools', array( $this, 'execute_tool' ) );
		add_action( 'asf_render_content_pane_geodir_tools', array( $this, 'render_html' ) );
	}

	public function execute_tool( $name ) {

		switch ( $name ) {
			// Tools
			case 'clear_version_numbers':
				Actions\ClearVersionNumbersAction::dispatch();
				break;
			case 'demo_regen_thumbs_progress':
				$this->handle_regen_thumbs();
				break;
			case 'check_reviews':
				Actions\CheckReviewsAction::dispatch();
				break;
			case 'install_pages':
				Actions\InstallPagesAction::dispatch();
				break;
			case 'merge_missing_terms':
				Actions\MergeMissingTermsAction::dispatch();
				break;
			case 'recount_terms':
				Actions\RecountTermsAction::dispatch();
				break;
			case 'generate_keywords':
				Actions\GenerateKeywordsAction::dispatch();
				break;
			case 'generate_thumbnails':
				Actions\RegenerateThumbnailsAction::dispatch();
				break;
			case 'export_db_texts':
				Actions\ExportDatabaseTextsAction::dispatch();
				break;
			case 'clear_paging_cache':
				Actions\ClearPagingCacheAction::dispatch();
				break;
			case 'search_replace_cf':
				Actions\SearchReplaceCustomFieldAction::dispatch();
				break;

			// Export
			case 'export_listings':
				//Actions\SearchReplaceCustomFieldAction::dispatch();
				wp_send_json_success( array() );
				break;

		}

	}

	public function render_html( $name ) {

		switch ( $name ) {
			case 'status_report':
				Actions\RenderStatusReportAction::dispatch();
				break;

		}

	}

}

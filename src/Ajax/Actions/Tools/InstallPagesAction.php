<?php
/**
 * Install Pages Action
 * Handles the installation of GeoDirectory pages via AJAX.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

use AyeCode\GeoDirectory\Core\Services\PageDefaults;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Install Pages Action class
 * Handles the process of installing GeoDirectory pages.
 */
class InstallPagesAction {

	/**
	 * Page defaults service instance.
	 *
	 * @var PageDefaults
	 */
	private PageDefaults $page_defaults;

	/**
	 * Constructor.
	 *
	 * @param PageDefaults $page_defaults Page defaults service.
	 */
	public function __construct( PageDefaults $page_defaults ) {
		$this->page_defaults = $page_defaults;
	}

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public function dispatch(): void {
		$gutenberg = geodir_is_gutenberg();

		$pages = apply_filters( 'geodirectory_create_pages', array(
			'page_add' => array(
				'name'    => _x( 'add-listing', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Add Listing', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'add', $gutenberg ),
			),
			'page_search' => array(
				'name'    => _x( 'search', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Search page', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'search', $gutenberg ),
			),
			'page_terms_conditions' => array(
				'name'    => _x( 'terms-and-conditions', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Terms and Conditions', 'Page title', 'geodirectory'),
				'content' => __('ENTER YOUR SITE TERMS AND CONDITIONS HERE','geodirectory'),
			),
			'page_location' => array(
				'name'    => _x( 'location', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Location', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'location', $gutenberg ),
			),
			'page_archive' => array(
				'name'    => _x( 'gd-archive', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'archive', $gutenberg ),
			),
			'page_archive_item' => array(
				'name'    => _x( 'gd-archive-item', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive Item', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'archive_item', $gutenberg ),
			),
			'page_details' => array(
				'name'    => _x( 'gd-details', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Details', 'Page title', 'geodirectory'),
				'content' => $this->page_defaults->get_content( 'details', $gutenberg ),
			),
		) );

		foreach ( $pages as $key => $page ) {
			geodir_create_page( esc_sql( $page['name'] ), $key , $page['title'], $page['content']);
		}

		delete_transient( 'geodir_cache_excluded_uris' );


		wp_send_json_success(array(
			'message'  => __( 'All missing GeoDirectory pages successfully installed', 'geodirectory' ),
			'progress' => 100
		));
	}
}

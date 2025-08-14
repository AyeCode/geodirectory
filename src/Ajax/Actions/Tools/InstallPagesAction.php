<?php
/**
 * Class ClearVersionNumbersAction
 * Represents an action to clear version numbers within the GeoDirectory Ajax functionality.
 *
 * @since 3.0.0
 */

// Define the namespace for the class. This helps prevent conflicts.
namespace AyeCode\GeoDirectory\Ajax\Actions\Tools;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class ClearVersionNumbersAction
 * Handles the process of clearing version numbers and resetting associated data within the GeoDirectory plugin.
 */
class InstallPagesAction {

	/**
	 * Handles the dispatch process.
	 *
	 * @return void Outputs a JSON success response with a message and progress value.
	 */
	public static function dispatch() {
		$gutenberg = geodir_is_gutenberg();

		$pages = apply_filters( 'geodirectory_create_pages', array(
			'page_add' => array(
				'name'    => _x( 'add-listing', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Add Listing', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_add_content(false, $gutenberg),
			),
			'page_search' => array(
				'name'    => _x( 'search', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Search page', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_search_content(false, $gutenberg),
			),
			'page_terms_conditions' => array(
				'name'    => _x( 'terms-and-conditions', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Terms and Conditions', 'Page title', 'geodirectory'),
				'content' => __('ENTER YOUR SITE TERMS AND CONDITIONS HERE','geodirectory'),
			),
			'page_location' => array(
				'name'    => _x( 'location', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Location', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_location_content(false, $gutenberg),
			),
			'page_archive' => array(
				'name'    => _x( 'gd-archive', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_archive_content(false, $gutenberg),
			),
			'page_archive_item' => array(
				'name'    => _x( 'gd-archive-item', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive Item', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_archive_item_content(false, $gutenberg),
			),
			'page_details' => array(
				'name'    => _x( 'gd-details', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Details', 'Page title', 'geodirectory'),
				'content' => \GeoDir_Defaults::page_details_content(false, $gutenberg),
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

<?php
/**
 * GeoDirectory API
 *
 * Handles GD-API endpoint requests.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_API {

	/**
	 * Setup class.
	 * @since 2.0
	 */
	public function __construct() {
		// Add query vars.
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register API endpoints.
		add_action( 'init', array( $this, 'add_endpoint' ), 0 );

		// Handle geodir-api endpoint requests.
		add_action( 'parse_request', array( $this, 'handle_api_requests' ), 0 );

		// WP REST API.
		$this->rest_api_init();
	}

	/**
	 * Add new query vars.
	 *
	 * @since 2.0.0
	 * @param array $vars
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'geodir-api';
		return $vars;
	}

	/**
	 * GeoDir API for payment gateway IPNs, etc.
	 * @since 2.0.0
	 */
	public static function add_endpoint() {
		add_rewrite_endpoint( 'geodir-api', EP_ALL );
	}

	/**
	 * API request - Trigger any API requests.
	 *
	 * @since   2.0.0
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['geodir-api'] ) ) {
			$wp->query_vars['geodir-api'] = $_GET['geodir-api'];
		}

		// geodir-api endpoint requests.
		if ( ! empty( $wp->query_vars['geodir-api'] ) ) {

			// Buffer, we won't want any output here.
			ob_start();

			// No cache headers.
			geodir_nocache_headers();

			// Clean the API request.
			$api_request = strtolower( geodir_clean( $wp->query_vars['geodir-api'] ) );

			// Trigger generic action before request hook.
			do_action( 'geodir_api_request', $api_request );

			// Is there actually something hooked into this API request? If not trigger 400 - Bad request.
			status_header( has_action( 'geodir_api_' . $api_request ) ? 200 : 400 );

			// Trigger an action which plugins can hook into to fulfill the request.
			do_action( 'geodir_api_' . $api_request );

			// Done, clear buffer and exit.
			ob_end_clean();
			die( '-1' );
		}
	}

	/**
	 * Init WP REST API.
	 * @since 2.0.0
	 */
	private function rest_api_init() {
		// REST API was included starting WordPress 4.4.
		if ( ! class_exists( 'WP_REST_Server' ) ) {
			return;
		}

		$this->rest_api_includes();

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
	}

	/**
	 * Include REST API classes.
	 *
	 * @since 2.0.0
	 */
	private function rest_api_includes() {
		include_once( dirname( __FILE__ ) . '/geodir-rest-functions.php' );

		// Abstract controllers.
		include_once( dirname( __FILE__ ) . '/abstracts/abstract-geodir-rest-controller.php' );
		//include_once( dirname( __FILE__ ) . '/abstracts/abstract-geodir-rest-posts-controller.php' );
		//include_once( dirname( __FILE__ ) . '/abstracts/abstract-geodir-rest-terms-controller.php' );

		// REST API v2 controllers.
		//include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-categories-controller.php' );
		//include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-tags-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-taxonomies-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-types-controller.php' );
		//include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-reviews-controller.php' );
		//include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-posts-controller.php' );
		//include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-reports-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-settings-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-setting-options-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-system-status-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-system-status-tools-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-countries-controller.php' );
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-markers-controller.php' );
	}

	/**
	 * Register REST API routes.
	 * @since 2.0.0
	 */
	public function register_rest_routes() {			
		// Set show_in_rest for post types and taxonomies
		$this->set_show_in_rest(); // @todo integrate this from post type settings

		// Register settings to the REST API.
		$this->register_wp_admin_settings();

		$controllers = array();
		if ( geodir_api_enabled() ) {
			$controllers = array(
				// v2 controllers.
				'Geodir_REST_Taxonomies_Controller',
					//'GeoDir_REST_Post_Categories_Controller',
					//'GeoDir_REST_Post_Reviews_Controller',
					//'GeoDir_REST_Post_Tags_Controller',
				'GeoDir_REST_Post_Types_Controller',
					//'GeoDir_REST_Posts_Controller',
					//'GeoDir_REST_Reports_Controller',
				'GeoDir_REST_Settings_Controller',
				'GeoDir_REST_Setting_Options_Controller',
				'GeoDir_REST_System_Status_Controller',
				'GeoDir_REST_System_Status_Tools_Controller',
				'GeoDir_REST_Countries_Controller',
			);
		}
		$controllers[] = 'GeoDir_REST_Markers_Controller'; // Map markers api should always enabled.

		foreach ( $controllers as $controller ) {
			$this->$controller = new $controller();
			$this->$controller->register_routes();
		}
	}

	/**
	 * Register GeoDir settings from WP-API to the REST API.
	 * @since  2.0.0
	 */
	public function register_wp_admin_settings() {
		$pages = GeoDir_Admin_Settings::get_settings_pages();
		foreach ( $pages as $page ) {
			new GeoDir_Register_WP_Admin_Settings( $page, 'page' );
		}
	}
	
	public function set_show_in_rest() {
		global $wp_post_types, $wp_taxonomies;

		$post_types = geodir_get_posttypes( 'array' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
				if ( isset( $wp_post_types[$post_type] ) ) {
					$wp_post_types[$post_type]->gd_listing = true;
					$wp_post_types[$post_type]->show_in_rest = true;
					$wp_post_types[$post_type]->rest_base = $data['has_archive'];
					
					if ( ! empty( $data['taxonomies'] ) ) {
						foreach ( $data['taxonomies'] as $taxonomy ) {
							if ( isset( $wp_taxonomies[$taxonomy] ) ) {
								$rest_base = $taxonomy;
								
								$wp_taxonomies[$taxonomy]->gd_taxonomy = true;
								$wp_taxonomies[$taxonomy]->show_in_rest = true;
								$wp_taxonomies[$taxonomy]->rest_base = $rest_base;
							}
						}
					}
				}
			}
		}
	}

}

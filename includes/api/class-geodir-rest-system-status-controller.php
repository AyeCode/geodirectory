<?php
/**
 * REST API GD System Status controller
 *
 * Handles requests to the /system_status endpoint.
 *
 * @author   GeoDirectory
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @package GeoDirectory/API
 * @extends GeoDir_REST_Controller
 */
class GeoDir_REST_System_Status_Controller extends GeoDir_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'geodir/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'system_status';

	/**
	 * Register the route for /system_status
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Check whether a given request has permission to view system status.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! geodir_rest_check_manager_permissions( 'system_status', 'read' ) ) {
			return new WP_Error( 'geodir_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'geodirectory' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Get a system status info, by section.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$schema    = $this->get_item_schema();
		$mappings  = $this->get_item_mappings();
		$response  = array();

		foreach ( $mappings as $section => $values ) {
			foreach ( $values as $key => $value ) {
				if ( isset( $schema['properties'][ $section ]['properties'][ $key ]['type'] ) ) {
					settype( $values[ $key ], $schema['properties'][ $section ]['properties'][ $key ]['type'] );
				}
			}
			settype( $values, $schema['properties'][ $section ]['type'] );
			$response[ $section ] = $values;
		}

		$response = $this->prepare_item_for_response( $response, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Get the system status schema, conforming to JSON Schema.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'system_status',
			'type'       => 'object',
			'properties' => array(
				'environment' => array(
					'description' => __( 'Environment.', 'geodirectory' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'home_url' => array(
							'description' => __( 'Home URL.', 'geodirectory' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'site_url' => array(
							'description' => __( 'Site URL.', 'geodirectory' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'geodir_version' => array(
							'description' => __( 'GeoDirectory version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'wp_version' => array(
							'description' => __( 'WordPress version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'wp_multisite' => array(
							'description' => __( 'Is WordPress multisite?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'wp_memory_limit' => array(
							'description' => __( 'WordPress memory limit.', 'geodirectory' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'wp_debug_mode' => array(
							'description' => __( 'Is WordPress debug mode active?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'wp_cron' => array(
							'description' => __( 'Are WordPress cron jobs enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'language' => array(
							'description' => __( 'WordPress language.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'server_info' => array(
							'description' => __( 'Server info.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'php_version' => array(
							'description' => __( 'PHP version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'php_post_max_size' => array(
							'description' => __( 'PHP post max size.', 'geodirectory' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'php_max_execution_time' => array(
							'description' => __( 'PHP max execution time.', 'geodirectory' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'php_max_input_vars' => array(
							'description' => __( 'PHP max input vars.', 'geodirectory' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'curl_version' => array(
							'description' => __( 'cURL version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'suhosin_installed' => array(
							'description' => __( 'Is SUHOSIN installed?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'max_upload_size' => array(
							'description' => __( 'Max upload size.', 'geodirectory' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'mysql_version' => array(
							'description' => __( 'MySQL version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'default_timezone' => array(
							'description' => __( 'Default timezone.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'fsockopen_or_curl_enabled' => array(
							'description' => __( 'Is fsockopen/cURL enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'soapclient_enabled' => array(
							'description' => __( 'Is SoapClient class enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'domdocument_enabled' => array(
							'description' => __( 'Is DomDocument class enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'gzip_enabled' => array(
							'description' => __( 'Is GZip enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'mbstring_enabled' => array(
							'description' => __( 'Is mbstring enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'remote_post_successful' => array(
							'description' => __( 'Remote POST successful?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'remote_post_response' => array(
							'description' => __( 'Remote POST response.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'remote_get_successful' => array(
							'description' => __( 'Remote GET successful?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'remote_get_response' => array(
							'description' => __( 'Remote GET response.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'database' => array(
					'description' => __( 'Database.', 'geodirectory' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'geodirectory_db_version' => array(
							'description' => __( 'GD database version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'database_prefix' => array(
							'description' => __( 'Database prefix.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'database_tables' => array(
							'description' => __( 'Database tables.', 'geodirectory' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'    => 'string',
							),
						),
					),
				),
				'active_plugins' => array(
					'description' => __( 'Active plugins.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'    => 'string',
					),
				),
				'theme' => array(
					'description' => __( 'Theme.', 'geodirectory' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'name' => array(
							'description' => __( 'Theme name.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'version' => array(
							'description' => __( 'Theme version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'version_latest' => array(
							'description' => __( 'Latest version of theme.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'author_url' => array(
							'description' => __( 'Theme author URL.', 'geodirectory' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'is_child_theme' => array(
							'description' => __( 'Is this theme a child theme?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'has_geodirectory_support' => array(
							'description' => __( 'Does the theme declare GeoDirectory support?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'has_outdated_templates' => array(
							'description' => __( 'Does this theme have outdated templates?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'overrides' => array(
							'description' => __( 'Template overrides.', 'geodirectory' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'    => 'string',
							),
						),
						'parent_name' => array(
							'description' => __( 'Parent theme name.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'parent_version' => array(
							'description' => __( 'Parent theme version.', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'parent_author_url' => array(
							'description' => __( 'Parent theme author URL.', 'geodirectory' ),
							'type'        => 'string',
							'format'      => 'uri',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'settings' => array(
					'description' => __( 'Settings.', 'geodirectory' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'api_enabled' => array(
							'description' => __( 'REST API enabled?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'upload_max_filesize' => array(
							'description' => __( 'Max upload file size(in mb)', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'default_status' => array(
							'description' => __( 'New listing default status', 'geodirectory' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'security' => array(
					'description' => __( 'Security.', 'geodirectory' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'secure_connection' => array(
							'description' => __( 'Is the connection to your store secure?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'hide_errors' => array(
							'description' => __( 'Hide errors from visitors?', 'geodirectory' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'pages' => array(
					'description' => __( 'GeoDirectory pages.', 'geodirectory' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'    => 'string',
					),
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Return an array of sections and the data associated with each.
	 *
	 * @return array
	 */
	public function get_item_mappings() {
		return array(
			'environment'    => $this->get_environment_info(),
			'database'       => $this->get_database_info(),
			'active_plugins' => $this->get_active_plugins(),
			'theme'          => $this->get_theme_info(),
			'settings'       => $this->get_settings(),
			'security'       => $this->get_security_info(),
			'pages'          => $this->get_pages(),
		);
	}

	/**
	 * Get array of environment information. Includes thing like software
	 * versions, and various server settings.
	 *
	 * @return array
	 */
	public function get_environment_info() {
		global $wpdb;

		// Figure out cURL version, if installed.
		$curl_version = '';
		if ( function_exists( 'curl_version' ) ) {
			$curl_version = curl_version();
			$curl_version = $curl_version['version'] . ', ' . $curl_version['ssl_version'];
		}

		// WP memory limit
		$wp_memory_limit = geodir_let_to_num( WP_MEMORY_LIMIT );
		if ( function_exists( 'memory_get_usage' ) ) {
			$wp_memory_limit = max( $wp_memory_limit, geodir_let_to_num( @ini_get( 'memory_limit' ) ) );
		}
		
		// User agent
		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';

		// Test POST requests
		$post_response = wp_safe_remote_post( 'http://api.wordpress.org/core/browse-happy/1.1/', array(
			'timeout'     => 10,
			'user-agent'  => 'WordPress/' . get_bloginfo( 'version' ) . '; ' . home_url(),
			'httpversion' => '1.1',
			'body'        => array(
				'useragent'	=> $user_agent,
			),
		) );
		
		$post_response_body = NULL;
		$post_response_successful = false;
		if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
			$post_response_successful = true;
			$post_response_body = json_decode( wp_remote_retrieve_body( $post_response ), true );
		}

		// Test GET requests
		$get_response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/geodirectory/trunk/readme.txt', array(
			'timeout'     => 10,
			'user-agent'  => 'GeoDirectory/' . GeoDir()->version,
			'httpversion' => '1.1'
		) );
		$get_response_successful = false;
		if ( ! is_wp_error( $post_response ) && $post_response['response']['code'] >= 200 && $post_response['response']['code'] < 300 ) {
			$get_response_successful = true;
		}

		// Return all environment info. Described by JSON Schema.
		return array(
			'home_url'                  => get_option( 'home' ),
			'site_url'                  => get_option( 'siteurl' ),
			'version'                   => GeoDir()->version,
			'wp_version'                => get_bloginfo( 'version' ),
			'wp_multisite'              => is_multisite(),
			'wp_memory_limit'           => $wp_memory_limit,
			'wp_debug_mode'             => ( defined( 'WP_DEBUG' ) && WP_DEBUG ),
			'wp_cron'                   => ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ),
			'language'                  => get_locale(),
			'server_info'               => $_SERVER['SERVER_SOFTWARE'],
			'php_version'               => phpversion(),
			'php_post_max_size'         => geodir_let_to_num( ini_get( 'post_max_size' ) ),
			'php_max_execution_time'    => ini_get( 'max_execution_time' ),
			'php_max_input_vars'        => ini_get( 'max_input_vars' ),
			'curl_version'              => $curl_version,
			'suhosin_installed'         => extension_loaded( 'suhosin' ),
			'max_upload_size'           => wp_max_upload_size(),
			'mysql_version'             => ( ! empty( $wpdb->is_mysql ) ? $wpdb->db_version() : '' ),
			'default_timezone'          => date_default_timezone_get(),
			'fsockopen_or_curl_enabled' => ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ),
			'soapclient_enabled'        => class_exists( 'SoapClient' ),
			'domdocument_enabled'       => class_exists( 'DOMDocument' ),
			'gzip_enabled'              => is_callable( 'gzopen' ),
			'mbstring_enabled'          => extension_loaded( 'mbstring' ),
			'remote_post_successful'    => $post_response_successful,
			'remote_post_response'      => ( is_wp_error( $post_response ) ? $post_response->get_error_message() : $post_response['response']['code'] ),
			'remote_get_successful'     => $get_response_successful,
			'remote_get_response'       => ( is_wp_error( $get_response ) ? $get_response->get_error_message() : $get_response['response']['code'] ),
			'platform'       			=> ! empty( $post_response_body['platform'] ) ? $post_response_body['platform'] : '-',
			'browser_name'       		=> ! empty( $post_response_body['name'] ) ? $post_response_body['name'] : '-',
			'browser_version'       	=> ! empty( $post_response_body['version'] ) ? $post_response_body['version'] : '-',
			'user_agent'       			=> $user_agent
		);
	}

	/**
	 * Add prefix to table.
	 *
	 * @param string $table table name
	 * @return stromg
	 */
	protected function add_db_table_prefix( $table ) {
		global $plugin_prefix;
		return $plugin_prefix . $table;
	}

	/**
	 * Get array of database information. Version, prefix, and table existence.
	 *
	 * @return array
	 */
	public function get_database_info() {
		global $wpdb;

		$database_table_sizes = $wpdb->get_results( $wpdb->prepare( "
			SELECT
			    table_name AS 'name',
			    round( ( data_length / 1024 / 1024 ), 2 ) 'data',
			    round( ( index_length / 1024 / 1024 ), 2 ) 'index'
			FROM information_schema.TABLES
			WHERE table_schema = %s
			ORDER BY name ASC;
		", DB_NAME ) );
		
		$post_types = geodir_get_posttypes();
		
		$core_tables = array(
			'api_keys',
			'attachments',
			'custom_fields',
			'custom_sort_fields',
			'post_review',
		);
		foreach ( $post_types as $post_type ) {
			$core_tables[] = $post_type . '_detail';
		}

		// GD Core tables to check existence of
		$core_tables = apply_filters( 'geodir_database_tables', $core_tables );

		/**
		 * Adding the prefix to the tables array, for backwards compatibility.
		 *
		 * If we changed the tables above to include the prefix, then any filters against that table could break.
		 */
		$core_tables = array_map( array( $this, 'add_db_table_prefix' ), $core_tables );

		/**
		 * The countries tabel is not geodir_ prefixed.
		 */
		$prefix = isset( $wpdb->base_prefix ) ? $wpdb->base_prefix : $wpdb->prefix;
		$core_tables[] = $prefix . 'countries';

		/**
		 * Organize GeoDirectory and non-GeoDirectory tables separately for display purposes later.
		 *
		 * To ensure we include all GD tables, even if they do not exist, pre-populate the GD array with all the tables.
		 */
		$tables = array(
			'geodirectory' => array_fill_keys( $core_tables, false ),
			'other' => array()
		);

		$database_size = array(
			'data' => 0,
			'index' => 0
		);

		foreach ( $database_table_sizes as $table ) {
			$table_type = in_array( $table->name, $core_tables ) ? 'geodirectory' : 'other';

			$tables[ $table_type ][ $table->name ] = array(
				'data'  => $table->data,
				'index' => $table->index
			);

			$database_size[ 'data' ] += $table->data;
			$database_size[ 'index' ] += $table->index;
		}

		// Return all database info. Described by JSON Schema.
		return array(
			'geodirectory_db_version'   => get_option( 'geodirectory_db_version' ),
			'database_prefix'        	=> $wpdb->prefix,
			'database_tables'        	=> $tables,
			'database_size'          	=> $database_size,
		);
	}

	/**
	 * Get array of counts of objects. Orders, products, etc.
	 *
	 * @return array
	 */
	public function get_post_type_counts() {
		global $wpdb;

		$post_type_counts = $wpdb->get_results( "SELECT post_type AS 'type', count(1) AS 'count' FROM {$wpdb->posts} GROUP BY post_type;" );

		return is_array( $post_type_counts ) ? $post_type_counts : array();
	}

	/**
	 * Get a list of plugins active on the site.
	 *
	 * @return array
	 */
	public function get_active_plugins() {
		require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		require_once( ABSPATH . 'wp-admin/includes/update.php' );

		if ( ! function_exists( 'get_plugin_updates' ) ) {
			return array();
		}

		// Get both site plugins and network plugins
		$active_plugins = (array) get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$network_activated_plugins = array_keys( get_site_option( 'active_sitewide_plugins', array() ) );
			$active_plugins            = array_merge( $active_plugins, $network_activated_plugins );
		}

		$active_plugins_data = array();
		$available_updates   = get_plugin_updates();

		foreach ( $active_plugins as $plugin ) {
			$data           = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );

			// convert plugin data to json response format.
			$active_plugins_data[] = array(
				'plugin'            => $plugin,
				'name'              => $data['Name'],
				'version'           => $data['Version'],
				'url'               => $data['PluginURI'],
				'author_name'       => $data['AuthorName'],
				'author_url'        => esc_url_raw( $data['AuthorURI'] ),
				'network_activated' => $data['Network'],
				'latest_verison'	=> ( array_key_exists( $plugin, $available_updates ) ) ? $available_updates[$plugin]->update->new_version : $data['Version']
			);
		}

		return $active_plugins_data;
	}

	/**
	 * Get info on the current active theme, info on parent theme (if presnet)
	 * and a list of template overrides.
	 *
	 * @return array
	 */
	public function get_theme_info() {
		$active_theme = wp_get_theme();

		// Get parent theme info if this theme is a child theme, otherwise
		// pass empty info in the response.
		if ( is_child_theme() ) {
			$parent_theme      = wp_get_theme( $active_theme->Template );
			$parent_theme_info = array(
				'parent_name'           => $parent_theme->Name,
				'parent_version'        => $parent_theme->Version,
				'parent_latest_verison' => GeoDir_Admin_Status::get_latest_theme_version( $parent_theme ),
				'parent_author_url'     => $parent_theme->{'Author URI'},
			);
		} else {
			$parent_theme_info = array( 'parent_name' => '', 'parent_version' => '', 'parent_latest_verison' => '', 'parent_author_url' => '' );
		}

		/**
		 * Scan the theme directory for all GD templates to see if our theme
		 * overrides any of them.
		 */
		$override_files     = array();
		$outdated_templates = false;
		$scan_files         = GeoDir_Admin_Status::scan_template_files( GeoDir()->plugin_path() . '/templates/' );

		//print_r($scan_files );exit;
		foreach ( $scan_files as $file ) {
			if ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . $file;
			} elseif ( file_exists( get_stylesheet_directory() . '/' . GeoDir()->template_path() . $file ) ) {
				$theme_file = get_stylesheet_directory() . '/' . GeoDir()->template_path() . $file;
			} elseif ( file_exists( get_template_directory() . '/' . $file ) ) {
				$theme_file = get_template_directory() . '/' . $file;
			} elseif ( file_exists( get_template_directory() . '/' . GeoDir()->template_path() . $file ) ) {
				$theme_file = get_template_directory() . '/' . GeoDir()->template_path() . $file;
			} else {
				$theme_file = false;
			}

			if ( ! empty( $theme_file ) ) {
				$core_version  = GeoDir_Admin_Status::get_file_version( GeoDir()->plugin_path() . '/templates/' . $file );
				$theme_version = GeoDir_Admin_Status::get_file_version( $theme_file );
				if ( $core_version && ( empty( $theme_version ) || version_compare( $theme_version, $core_version, '<' ) ) ) {
					if ( ! $outdated_templates ) {
						$outdated_templates = true;
					}
				}
				$override_files[] = array(
					'file'         => str_replace( WP_CONTENT_DIR . '/themes/', '', $theme_file ),
					'version'      => $theme_version,
					'core_version' => $core_version,
				);
			}
		}

		$active_theme_info = array(
			'name'                    => $active_theme->Name,
			'version'                 => $active_theme->Version,
			'latest_verison'          => GeoDir_Admin_Status::get_latest_theme_version( $active_theme ),
			'author_url'              => esc_url_raw( $active_theme->{'Author URI'} ),
			'is_child_theme'          => is_child_theme(),
			'has_geodirectory_support' => ( current_theme_supports( 'geodirectory' ) || in_array( $active_theme->template, geodir_get_core_supported_themes() ) ),
			'has_outdated_templates'  => $outdated_templates,
			'overrides'               => $override_files,
		);

		return array_merge( $active_theme_info, $parent_theme_info );
	}

	/**
	 * Get some setting values for the site that are useful for debugging
	 * purposes. For full settings access, use the settings api.
	 *
	 * @return array
	 */
	public function get_settings() {
		global $geodirectory;
		// Return array of useful settings for debugging.
		return array(
			'api_enabled'              => geodir_get_option( 'rest_api_enabled' ) ? true : false,
			'upload_max_filesize'      => geodir_get_option( 'upload_max_filesize' ),
			'default_status'      	   => geodir_get_option( 'default_status' ),
			'maps_api_key'      	   => geodir_get_option( 'google_maps_api_key' ) ? true : false,
			'default_location'         => $geodirectory->location->is_default_location_set(),
		);
	}

	/**
	 * Returns security tips.
	 *
	 * @return array
	 */
	public function get_security_info() {
		$check_page = get_home_url();
		return array(
			'secure_connection' => 'https' === substr( $check_page, 0, 5 ),
			'hide_errors'       => ! ( defined( 'WP_DEBUG' ) && defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG && WP_DEBUG_DISPLAY ) || 0 === intval( ini_get( 'display_errors' ) ),
		);
	}

	/**
	 * Returns a mini-report on GD pages and if they are configured correctly:
	 * Present, visible, and including the correct shortcode.
	 *
	 * @return array
	 */
	public function get_pages() {
		// GD pages to check against
		$check_pages = array(
			_x( 'Location page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'location',
				'shortcode' => '',
			),
			_x( 'Add listing page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'add',
				'shortcode' => 'gd_add_listing',
			),
			_x( 'Search Page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'search',
				'shortcode' => '',
			),
			_x( 'Terms and Conditions page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'terms_conditions',
				'shortcode' => '',
			),
			_x( 'Archive page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'archive',
				'shortcode' => '',
			),
			_x( 'Details Page', 'Page setting', 'geodirectory' ) => array(
				'option'    => 'details',
				'shortcode' => '',
			),
		);

		$pages_output = array();
		foreach ( $check_pages as $page_name => $values ) {
			$page_id  = geodir_get_page_id( $values['option'], '', false );
			$page_set = $page_exists = $page_visible = false;
			$shortcode_present = $shortcode_required = false;

			// Page checks
			if ( $page_id ) {
				$page_set = true;
			}
			if ( get_post( $page_id ) ) {
				$page_exists = true;
			}
			if ( 'publish' === get_post_status( $page_id ) ) {
				$page_visible = true;
			}

			// Shortcode checks
			if ( $values['shortcode']  && get_post( $page_id ) ) {
				$shortcode_required = true;
				$page = get_post( $page_id );
				if ( has_shortcode( $page->post_content, $values['shortcode'] ) ) {
					$shortcode_present = true;
				}
			}

			// Wrap up our findings into an output array
			$pages_output[] = array(
				'page_name'          => $page_name,
				'page_id'            => $page_id,
				'page_set'           => $page_set,
				'page_exists'        => $page_exists,
				'page_visible'       => $page_visible,
				'shortcode'          => $values['shortcode'],
				'shortcode_required' => $shortcode_required,
				'shortcode_present'  => $shortcode_present,
			);
		}

		return $pages_output;
	}

	/**
	 * Get any query params needed.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param( array( 'default' => 'view' ) ),
		);
	}

	/**
	 * Prepare the system status response
	 *
	 * @param array $system_status
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $system_status, $request ) {
		$data = $this->add_additional_fields_to_object( $system_status, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		/**
		 * Filter the system status returned from the REST API.
		 *
		 * @param WP_REST_Response   $response The response object.
		 * @param mixed              $system_status System status
		 * @param WP_REST_Request    $request  Request object.
		 */
		return apply_filters( 'geodir_rest_prepare_system_status', $response, $system_status, $request );
	}
}

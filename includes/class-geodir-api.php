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

		// Handle markers endpoint nonce.
		add_action( 'geodir_create_nonce_before', array( __CLASS__, 'rest_nonce_set_hook' ), 10 );
		add_action( 'geodir_create_nonce_after', array( __CLASS__, 'rest_nonce_hook_unset' ), 10, 2 );
		add_filter( 'rest_authentication_errors', array( __CLASS__, 'rest_cookie_check_errors' ), 999, 1 );

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

		// Show / hide CPT from Rest API
		add_action( 'init', array( $this, 'setup_show_in_rest' ), 10 );

		// Init REST API routes.
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 100 );
		add_action( 'rest_api_init', array( $this, 'register_rest_query' ), 101 );
		add_action( 'rest_insert_comment', array( __CLASS__, 'rest_insert_comment' ), 1, 3 );

		add_action( 'pre_get_posts', array( __CLASS__, 'rest_posts_request' ), 10, 2 );
	}

	/**
	 * Include REST API classes.
	 *
	 * @since 2.0.0
	 */
	private function rest_api_includes() {
		require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/admin/admin-functions.php' );
		include_once( dirname( __FILE__ ) . '/geodir-rest-functions.php' );

		// Authentication.
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-authentication.php' );

		// Don't load on markers request to speedup.
		if ( ! geodir_has_request_uri( '/wp-json/geodir/v2/markers' ) ) {
			// Abstract controllers.
			include_once( dirname( __FILE__ ) . '/abstracts/abstract-geodir-rest-controller.php' );
			include_once( dirname( __FILE__ ) . '/abstracts/abstract-geodir-rest-terms-controller.php' );

			// REST API v2 controllers.
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-taxonomies-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-types-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-categories-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-post-tags-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-posts-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-reviews-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-fields-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-settings-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-setting-options-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-system-status-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-system-status-tools-controller.php' );
			include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-countries-controller.php' );
		}
		include_once( dirname( __FILE__ ) . '/api/class-geodir-rest-markers-controller.php' );

		// Load show/hide widget on block widgets page.
		if ( ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/widget-types/' ) !== false ) {
			GeoDir_Admin_Widgets::init();
		}
	}

	/**
	 * Register REST API routes.
	 * @since 2.0.0
	 */
	public function register_rest_routes() {
		global $wp_post_types;

		// Register settings to the REST API.
		$this->register_wp_admin_settings();

		$show_in_rest = array();

		if ( geodir_api_enabled() ) {
            $gd_post_types = geodir_get_posttypes();

            foreach ( $wp_post_types as $post_type ) {
				if ( ! in_array( $post_type->name, $gd_post_types ) ) {
                    continue;
                }

                if ( empty( $post_type->show_in_rest ) ) {
                    continue;
                }

				$show_in_rest[] = $post_type->name;

                $class = ! empty( $post_type->rest_controller_class ) ? $post_type->rest_controller_class : 'WP_REST_Posts_Controller';

                if ( ! class_exists( $class ) ) {
                    continue;
                }
                $controller = new $class( $post_type->name );

                if ( ! ( is_subclass_of( $controller, 'WP_REST_Posts_Controller' ) || is_subclass_of( $controller, 'WP_REST_Controller' ) ) ) {
                    continue;
                }
            }

			$controllers = array(
				// v2 controllers.
				'Geodir_REST_Taxonomies_Controller',
				'GeoDir_REST_Reviews_Controller',
				'GeoDir_REST_Post_Types_Controller',
				'GeoDir_REST_Fields_Controller',
				'GeoDir_REST_Settings_Controller',
				'GeoDir_REST_Setting_Options_Controller',
				'GeoDir_REST_System_Status_Controller',
				'GeoDir_REST_System_Status_Tools_Controller',
				'GeoDir_REST_Countries_Controller',
				'GeoDir_REST_Markers_Controller', // Map markers api should always enabled.
			);
		} else {
			$controllers = array(
				'GeoDir_REST_Markers_Controller', // Map markers api should always enabled.
			);
		}

		foreach ( $controllers as $controller ) {
			$obj_controller = new $controller();
			$obj_controller->register_routes();

			if ( ! empty( $show_in_rest ) && $controller == 'GeoDir_REST_Fields_Controller' ) {
				foreach ( $show_in_rest as $post_type ) {
					$obj_controller = new $controller( $post_type );
					$obj_controller->register_routes();
				}
			}
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

    /**
     * Setup GeoDir show in rest.
     *
     * @since 2.0.0
     */
	public function setup_show_in_rest() {
		global $wp_post_types, $wp_taxonomies;

		if ( ! geodir_api_enabled() ) {
			return;
		}

		$post_types = geodir_get_posttypes( 'array' );

		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type => $data ) {
				if ( isset( $wp_post_types[$post_type] ) ) {
					$wp_post_types[$post_type]->gd_listing = true;
					$wp_post_types[$post_type]->show_in_rest = true;
					$wp_post_types[$post_type]->rest_base = $data['has_archive'];


					// Use the GD controller if its a GD API cal @todo test if this breaks anything
					// maybe force enable block editor for a CPT
					$force_block_editor = apply_filters('geodir_force_block_editor', false, $post_type );
					if ( !$force_block_editor || strpos( $_SERVER['REQUEST_URI'], '/geodir/' ) !== false ) {
						$wp_post_types[ $post_type ]->rest_controller_class = 'GeoDir_REST_Posts_Controller';
					}

					if ( ! empty( $data['taxonomies'] ) ) {
						foreach ( $data['taxonomies'] as $taxonomy ) {
							if ( isset( $wp_taxonomies[$taxonomy] ) ) {
								$wp_taxonomies[$taxonomy]->gd_taxonomy = true;
								$wp_taxonomies[$taxonomy]->show_in_rest = true;
								if ( $taxonomy == $post_type . 'category' ) {
									$rest_base = $data['has_archive'] . '/categories';
									$rest_controller_class = 'GeoDir_REST_Post_Categories_Controller';
								} else if ( $taxonomy == $post_type . '_tags' ) {
									$rest_base = $data['has_archive'] . '/tags';
									$rest_controller_class = 'GeoDir_REST_Post_Tags_Controller';
								} else {
									$rest_base = $taxonomy;
									$rest_controller_class = '';
								}
								$wp_taxonomies[$taxonomy]->rest_base = $rest_base;
								if ( $rest_controller_class ) {
									$wp_taxonomies[$taxonomy]->rest_controller_class = $rest_controller_class;
								}
							}
						}
					}
				}
			}
		}
	}

    /**
     * Function check if rest request.
     *
     * Check if REST_REQUEST then return true else return false.
     *
     * @since 2.0.0
     *
     * @return bool
     */
	public static function is_rest() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		return false;
	}

    /**
     * Register to rest query.
     *
     * @since 2.0.0
     */
	public static function register_rest_query() {
		if ( self::is_rest() ) {
			add_filter( 'posts_clauses_request', array( __CLASS__, 'posts_clauses_request' ), 10, 2 );
		}
	}

    /**
     * Request to posts clauses.
     *
     * @since 2.0.0
     *
     * @param array $clauses A value of posts clauses request.
     * @param object $wp_query wp_query object.
     * @return array $clauses.
     */
	public static function posts_clauses_request( $clauses, $wp_query ) {
		$post_type  = !empty( $wp_query->query_vars['post_type'] ) ? $wp_query->query_vars['post_type'] : '';

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return $clauses;
		}

		$clauses['distinct']    = apply_filters( 'geodir_rest_posts_clauses_distinct', $clauses['distinct'], $wp_query, $post_type );
		$clauses['fields']      = apply_filters( 'geodir_rest_posts_clauses_fields', $clauses['fields'], $wp_query, $post_type );
		$clauses['join']        = apply_filters( 'geodir_rest_posts_clauses_join', $clauses['join'], $wp_query, $post_type );
		$clauses['where']       = apply_filters( 'geodir_rest_posts_clauses_where', $clauses['where'], $wp_query, $post_type );
		$clauses['groupby']     = apply_filters( 'geodir_rest_posts_clauses_groupby', $clauses['groupby'], $wp_query, $post_type );
		$clauses['orderby']     = apply_filters( 'geodir_rest_posts_clauses_orderby', $clauses['orderby'], $wp_query, $post_type );
		$clauses['limits']      = apply_filters( 'geodir_rest_posts_clauses_limits', $clauses['limits'], $wp_query, $post_type );

		return apply_filters( 'geodir_rest_posts_clauses_request', $clauses, $wp_query, $post_type );
	}

    /**
     * Request to rest posts.
     *
     * @since 2.0.0
     *
     * @param $query
     */
	public static function rest_posts_request( $query ) {
		if ( self::is_rest() ) {
			add_filter( 'geodir_rest_posts_clauses_distinct', array( __CLASS__, 'rest_posts_distinct' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_fields', array( __CLASS__, 'rest_posts_fields' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_join', array( __CLASS__, 'rest_posts_join' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_where', array( __CLASS__, 'rest_posts_where' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_groupby', array( __CLASS__, 'rest_posts_groupby' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_orderby', array( __CLASS__, 'rest_posts_orderby' ), 10, 3 );
			add_filter( 'geodir_rest_posts_clauses_limits', array( __CLASS__, 'rest_posts_limits' ), 10, 3 );
		}
	}

    /**
     * Distinct to rest posts.
     *
     * @since 2.0.0
     *
     * @param string $distinct Rest posts distinct value.
     * @param object $wp_query wp_query object.
     * @param string $post_type Post type.
     * @return string $distinct.
     */
	public static function rest_posts_distinct( $distinct, $wp_query, $post_type ) {
		return $distinct;
	}

    /**
     * Rest to posts fields.
     *
     * @since 2.0.0
     *
     * @param string $fields Posts fields.
     * @param object $wp_query Wp_query object.
     * @param string $post_type Post type.
     * @return string $fields.
     */
	public static function rest_posts_fields( $fields, $wp_query, $post_type ) {
		if ( trim( $fields ) != '' ) {
			$fields .= ", ";
		}

		$table = geodir_db_cpt_table( $post_type );

		$fields .= "{$table}.*";

		return $fields;
	}

    /**
     * Join to rest posts.
     *
     * @since 2.0.0
     *
     * @param string $join Query join value.
     * @param object $wp_query Wp_query object.
     * @param string $post_type Post type.
     * @return string $join.
     */
	public static function rest_posts_join( $join, $wp_query, $post_type ) {
		global $wpdb;

		$table = geodir_db_cpt_table( $post_type );

		$join .= " LEFT JOIN {$table} ON ( {$table}.post_id = {$wpdb->posts}.ID )";

		return $join;
	}

    /**
     * GeoDir Where to rest posts.
     *
     * @since 2.0.0
     *
     * @param string $where Query where value.
     * @param object $wp_query Wp_query object.
     * @param string $post_type Post type.
     * @return string $where.
     */
	public static function rest_posts_where( $where, $wp_query, $post_type ) {
		return $where;
	}

    /**
     * Groupby to rest posts.
     *
     * @since 2.0.0
     *
     * @param string $groupby Query groupby value.
     * @param object $wp_query Wp_query object.
     * @param string $post_type Post type.
     * @return string $groupby.
     */
	public static function rest_posts_groupby( $groupby, $wp_query, $post_type ) {
		return $groupby;
	}

	/**
	 * Orderby to rest posts.
	 *
	 * @since 2.0.0
	 *
	 * @param string $orderby Query orderby value.
	 * @param object $wp_query Wp_query object.
	 * @param string $post_type Post type.
	 * @return string $orderby.
	 */
	public static function rest_posts_orderby( $orderby, $wp_query, $post_type ) {
		global $geodir_post_type;

		$geodir_post_type = $post_type;

		$table = geodir_db_cpt_table( $post_type );
		$sort_by = ! empty( $wp_query ) && ! empty( $wp_query->query_vars['orderby'] ) ? $wp_query->query_vars['orderby'] : '';

		$sort_by = apply_filters( 'geodir_rest_posts_order_sort_by_key', $sort_by, $orderby, $post_type, $wp_query );

		if ( ! empty( $wp_query->query_vars['s'] ) && ! empty( $wp_query->query_vars['gd_is_api_posts_call'] ) && 'relevance' === $sort_by ) {
			// Order by relevance search.
		} else {
			$orderby = GeoDir_Query::sort_by_sql( $sort_by, $post_type, $wp_query );
			$orderby = GeoDir_Query::sort_by_children( $orderby, $sort_by, $post_type, $wp_query );
		}

		return apply_filters( 'geodir_posts_order_by_sort', $orderby, $sort_by, $table, $wp_query );
	}

    /**
     * Limits to rest posts.
     *
     * @since 2.0.0
     *
     * @param string $limits
     * @param object $wp_query Wp_query object.
     * @param string $post_type Post type.
     * @return string $limits.
     */
	public static function rest_posts_limits( $limits, $wp_query, $post_type ) {
		return $limits;
	}

	/**
	 * Save review is submitted via the REST API.
	 *
	 * @since 2.0.0.71
	 *
	 * @param WP_Comment      $comment  Inserted or updated comment object.
	 * @param WP_REST_Request $request  Request object.
	 * @param bool            $creating True when creating a comment, false
	 *                                  when updating.
	 */
	public static function rest_insert_comment( $comment, $request, $creating ) {
		global $user_ID;

		if ( empty( $comment->comment_post_ID ) ) {
			return;
		}

		if ( ! geodir_is_gd_post_type( get_post_type( (int) $comment->comment_post_ID ) ) ) {
			return;
		}

		if ( isset( $request['rating'] ) ) {
			$_REQUEST['geodir_overallrating'] = absint( $request['rating'] );

			$backup_user_ID = $user_ID;
			$user_ID = $comment->user_id;
			GeoDir_Comments::save_rating( $comment->comment_ID );
			$user_ID = $backup_user_ID;
		}
	}

	/**
	 *
	 * @since 2.0.0.74
	 *
	 */
	public static function rest_nonce_set_hook( $action ) {
		global $geodir_rest_nonce_hook;

		if ( $action == 'wp_rest' && ! is_user_logged_in() ) {
			add_action( 'nonce_user_logged_out', array( __CLASS__, 'rest_nonce_user_logged_out' ), 9999, 2 );
			$geodir_rest_nonce_hook = true;
		}
	}

	/**
	 *
	 * @since 2.0.0.74
	 *
	 */
	public static function rest_nonce_hook_unset( $action, $nonce ) {
		global $geodir_rest_nonce_hook;

		if ( $geodir_rest_nonce_hook && ( $priority = has_action( 'nonce_user_logged_out', array( __CLASS__, 'rest_nonce_user_logged_out' ) ) ) ) {
			remove_action( 'nonce_user_logged_out', array( __CLASS__, 'rest_nonce_user_logged_out' ), $priority, 2 );
			$geodir_rest_nonce_hook = false;
		}
	}

	/**
	 *
	 * @since 2.0.0.74
	 *
	 */
	public static function rest_nonce_user_logged_out( $uid, $action ) {
		if ( $uid === 0 || $action != 'wp_rest' ) {
			return $uid;
		}

		return geodir_nonce_token( $action );
	}

	/**
	 *
	 * @since 2.0.0.74
	 *
	 */
	public static function rest_cookie_check_errors( $errors ) {
		if ( is_wp_error( $errors ) && ! empty( $_REQUEST['_wpnonce'] ) &&  ! empty( $_SERVER['REQUEST_URI'] ) && strpos( $_SERVER['REQUEST_URI'], '/wp-json/geodir/' ) !== false && strpos( $_SERVER['REQUEST_URI'], '/markers/' ) !== false && is_wp_error( $errors ) && $errors->get_error_code() == 'rest_cookie_invalid_nonce' ) {
			if ( is_user_logged_in() ) { // Logged in user
				return true;
			} elseif ( geodir_create_nonce( 'wp_rest' ) == sanitize_text_field( $_REQUEST['_wpnonce'] ) ) {
				return true;
			} else {
				$parse_referer = wp_parse_url( wp_get_referer() ); // Http referer
				$parse_home = wp_parse_url( home_url( '/' ) ); // Home url

				if ( ! empty( $parse_referer['host'] ) && ! empty( $parse_home['host'] ) && strtolower( $parse_referer['host'] ) == strtolower( $parse_home['host'] ) ) {
					return true;
				}
			}
		}
		return $errors;
	}
}

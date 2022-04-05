<?php
/**
 * GeoDirectory Auth
 *
 * Handles geodir-auth endpoint requests.
 *
 * @author   AyeCode
 * @category API
 * @package  GeoDirectory/API
 * @since    2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Auth {

	/**
	 * Version.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Setup class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		// Add query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// Register auth endpoint
		add_action( 'init', array( __CLASS__, 'add_endpoint' ), 0 );

		// Handle auth requests
		add_action( 'parse_request', array( $this, 'handle_auth_requests' ), 0 );
		
		// Process login
		add_action( 'wp_loaded', array( __CLASS__, 'process_login' ), 20 );
	}

	/**
	 * Add query vars.
	 *
	 * @since  2.0.0
	 *
	 * @param  array $vars
	 *
	 * @return string[]
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'geodir-auth-version';
		$vars[] = 'geodir-auth-route';
		return $vars;
	}

	/**
	 * Add auth endpoint.
	 *
	 * @since 2.0.0
	 */
	public static function add_endpoint() {
		add_rewrite_rule( '^geodir-auth/v([1]{1})/(.*)?', 'index.php?geodir-auth-version=$matches[1]&geodir-auth-route=$matches[2]', 'top' );
	}

	/**
	 * Get scope name.
	 *
	 * @since 2.0.0
	 *
	 * @param  string $scope
	 *
	 * @return string
	 */
	protected function get_i18n_scope( $scope ) {
		$permissions = array(
			'read'       => __( 'Read', 'geodirectory' ),
			'write'      => __( 'Write', 'geodirectory' ),
			'read_write' => __( 'Read/Write', 'geodirectory' ),
		);

		return $permissions[ $scope ];
	}

	/**
	 * Return a list of permissions a scope allows.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $scope
	 *
	 * @return array
	 */
	protected function get_permissions_in_scope( $scope ) {
		$permissions = array();
		switch ( $scope ) {
			case 'read' :
				$permissions[] = __( 'View places', 'geodirectory' );
			break;
			case 'write' :
				$permissions[] = __( 'Create places', 'geodirectory' );
			break;
			case 'read_write' :
				$permissions[] = __( 'View and manage places', 'geodirectory' );
			break;
		}
		return apply_filters( 'geodir_api_permissions_in_scope', $permissions, $scope );
	}

	/**
	 * Build auth urls.
	 *
	 * @since  2.0.0
	 *
	 * @param  array $data
	 * @param  string $endpoint
	 *
	 * @return string
	 */
	protected function build_url( $data, $endpoint ) {
		$url = geodir_get_endpoint_url( 'geodir-auth/v' . self::VERSION, $endpoint, home_url( '/' ) );

		return add_query_arg( array(
			'app_name'            => geodir_clean( $data['app_name'] ),
			'user_id'             => geodir_clean( $data['user_id'] ),
			'return_url'          => rawurlencode( $this->get_formatted_url( $data['return_url'] ) ),
			'callback_url'        => rawurlencode( $this->get_formatted_url( $data['callback_url'] ) ),
			'scope'               => geodir_clean( $data['scope'] ),
		), $url );
	}

	/**
	 * Decode and format a URL.
	 * @param  string $url
	 * @return string
	 */
	protected function get_formatted_url( $url ) {
		$url = urldecode( $url );

		if ( ! strstr( $url, '://' ) ) {
			$url = 'https://' . $url;
		}

		return $url;
	}

	/**
	 * Make validation.
	 *
	 * @since  2.0.0
	 */
	protected function make_validation() {
		$params = array(
			'app_name',
			'user_id',
			'return_url',
			'callback_url',
			'scope',
		);

		foreach ( $params as $param ) {
			if ( empty( $_REQUEST[ $param ] ) ) {
				/* translators: %s: parameter */
				throw new Exception( sprintf( __( 'Missing parameter %s', 'geodirectory' ), $param ) );
			}
		}

		if ( ! in_array( $_REQUEST['scope'], array( 'read', 'write', 'read_write' ) ) ) {
			/* translators: %s: scope */
			throw new Exception( sprintf( __( 'Invalid scope %s', 'geodirectory' ), geodir_clean( $_REQUEST['scope'] ) ) );
		}

		foreach ( array( 'return_url', 'callback_url' ) as $param ) {
			$param = $this->get_formatted_url( $_REQUEST[ $param ] );

			if ( false === filter_var( $param, FILTER_VALIDATE_URL ) ) {
				/* translators: %s: url */
				throw new Exception( sprintf( __( 'The %s is not a valid URL', 'geodirectory' ), $param ) );
			}
		}

		$callback_url = $this->get_formatted_url( $_REQUEST['callback_url'] );

		if ( 0 !== stripos( $callback_url, 'https://' ) ) {
			throw new Exception( __( 'The callback_url needs to be over SSL', 'geodirectory' ) );
		}
	}

	/**
	 * Create keys.
	 *
	 * @since  2.0.0
	 *
	 * @param  string $app_name
	 * @param  string $app_user_id
	 * @param  string $scope
	 *
	 * @return array
	 */
	protected function create_keys( $app_name, $app_user_id, $scope ) {
		global $wpdb;

		/* translators: 1: app name 2: scope 3: date 4: time */
		$description = sprintf(
			__( '%1$s - API %2$s (created on %3$s at %4$s).', 'geodirectory' ),
			geodir_clean( $app_name ),
			$this->get_i18n_scope( $scope ),
			date_i18n( geodir_date_format() ),
			date_i18n( geodir_time_format() )
		);
		$user = wp_get_current_user();

		// Created API keys.
		$permissions     = ( in_array( $scope, array( 'read', 'write', 'read_write' ) ) ) ? sanitize_text_field( $scope ) : 'read';
		$consumer_key    = 'ck_' . geodir_rand_hash();
		$consumer_secret = 'cs_' . geodir_rand_hash();

		$wpdb->insert(
			GEODIR_API_KEYS_TABLE,
			array(
				'user_id'         => $user->ID,
				'description'     => $description,
				'permissions'     => $permissions,
				'consumer_key'    => geodir_api_hash( $consumer_key ),
				'consumer_secret' => $consumer_secret,
				'truncated_key'   => substr( $consumer_key, -7 ),
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		return array(
			'key_id'          => $wpdb->insert_id,
			'user_id'         => $app_user_id,
			'consumer_key'    => $consumer_key,
			'consumer_secret' => $consumer_secret,
			'key_permissions' => $permissions,
		);
	}

	/**
	 * Post consumer data.
	 *
	 * @since  2.0.0
	 *
	 * @param  array  $consumer_data
	 * @param  string $url
	 *
	 * @return bool
	 * @throws Exception
	 */
	protected function post_consumer_data( $consumer_data, $url ) {
		$params = array(
			'body'      => json_encode( $consumer_data ),
			'timeout'   => 60,
			'headers'   => array(
				'Content-Type' => 'application/json;charset=' . get_bloginfo( 'charset' ),
			),
		);

		$response = wp_safe_remote_post( esc_url_raw( $url ), $params );

		if ( is_wp_error( $response ) ) {
			throw new Exception( $response->get_error_message() );
		} elseif ( 200 != $response['response']['code'] ) {
			throw new Exception( __( 'An error occurred in the request and at the time were unable to send the consumer data', 'geodirectory' ) );
		}

		return true;
	}

	/**
	 * Handle auth requests.
	 *
	 * @since 2.0.0
	 */
	public function handle_auth_requests() {
		global $wp;

		if ( ! empty( $_GET['geodir-auth-version'] ) ) {
			$wp->query_vars['geodir-auth-version'] = $_GET['geodir-auth-version'];
		}

		if ( ! empty( $_GET['geodir-auth-route'] ) ) {
			$wp->query_vars['geodir-auth-route'] = $_GET['geodir-auth-route'];
		}

		// geodir-auth endpoint requests
		if ( ! empty( $wp->query_vars['geodir-auth-version'] ) && ! empty( $wp->query_vars['geodir-auth-route'] ) ) {
			$this->auth_endpoint( $wp->query_vars['geodir-auth-route'] );
		}
	}

	/**
	 * Auth endpoint.
	 *
	 * @since 2.0.0
	 *
	 * @param string $route
	 */
	protected function auth_endpoint( $route ) {
		ob_start();

		$consumer_data = array();

		try {
			if ( ! geodir_api_enabled() ) {
				throw new Exception( __( 'GeoDirectory API disabled!', 'geodirectory' ) );
			}

			$route = strtolower( geodir_clean( $route ) );
			$this->make_validation();

			$data = wp_unslash( $_REQUEST ); // WPCS: input var ok, CSRF ok.

			// Login endpoint
			if ( 'login' == $route && ! is_user_logged_in() ) {
				geodir_get_template( 'auth/form-login.php', array(
					'app_name'     => geodir_clean( $data['app_name'] ),
					'return_url'   => add_query_arg( array( 'success' => 0, 'user_id' => geodir_clean( $data['user_id'] ) ), $this->get_formatted_url( $data['return_url'] ) ),
					'redirect_url' => $this->build_url( $data, 'authorize' ),
				) );

				exit;

			// Redirect with user is logged in
			} elseif ( 'login' == $route && is_user_logged_in() ) {
				wp_redirect( esc_url_raw( $this->build_url( $data, 'authorize' ) ) );
				exit;

			// Redirect with user is not logged in and trying to access the authorize endpoint
			} elseif ( 'authorize' == $route && ! is_user_logged_in() ) {
				wp_redirect( esc_url_raw( $this->build_url( $data, 'login' ) ) );
				exit;

			// Authorize endpoint
			} elseif ( 'authorize' == $route && current_user_can( 'manage_options' ) ) { // @todo manage_options
				geodir_get_template( 'auth/form-grant-access.php', array(
					'app_name'    => geodir_clean( $data['app_name'] ),
					'return_url'  => add_query_arg( array( 'success' => 0, 'user_id' => geodir_clean( $data['user_id'] ) ), $this->get_formatted_url( $data['return_url'] ) ),
					'scope'       => $this->get_i18n_scope( geodir_clean( $data['scope'] ) ),
					'permissions' => $this->get_permissions_in_scope( geodir_clean( $data['scope'] ) ),
					'granted_url' => wp_nonce_url( $this->build_url( $data, 'access_granted' ), 'geodir_auth_grant_access', 'geodir_auth_nonce' ),
					'logout_url'  => wp_logout_url( $this->build_url( $data, 'login' ) ),
					'user'        => wp_get_current_user(),
				) );
				exit;

			// Granted access endpoint
			} elseif ( 'access_granted' == $route && current_user_can( 'manage_options' ) ) { // @todo manage_options
				if ( ! isset( $_GET['geodir_auth_nonce'] ) || ! wp_verify_nonce( $_GET['geodir_auth_nonce'], 'geodir_auth_grant_access' ) ) {
					throw new Exception( __( 'Invalid nonce verification', 'geodirectory' ) );
				}

				$consumer_data = $this->create_keys( geodir_clean( $data['app_name'] ), geodir_clean( $data['user_id'] ), geodir_clean( $data['scope'] ) );
				$response      = $this->post_consumer_data( $consumer_data, $this->get_formatted_url( $data['callback_url'] ) );

				if ( $response ) {
					wp_redirect( esc_url_raw( add_query_arg( array( 'success' => 1, 'user_id' => geodir_clean( $data['user_id'] ) ), $this->get_formatted_url( $data['return_url'] ) ) ) );
					exit;
				}
			} else {
				throw new Exception( __( 'You do not have permission to access this page', 'geodirectory' ) );
			}
		} catch ( Exception $e ) {
			$this->maybe_delete_key( $consumer_data );

			/* translators: %s: error message */
			wp_die( sprintf( __( 'Error: %s.', 'geodirectory' ), $e->getMessage() ), __( 'Access denied', 'geodirectory' ), array( 'response' => 401 ) );
		}
	}

	/**
	 * Maybe delete key.
	 *
	 * @since 2.0.0
	 *
	 * @param array $key
	 */
	private function maybe_delete_key( $key ) {
		global $wpdb;

		if ( isset( $key['key_id'] ) ) {
			$wpdb->delete( GEODIR_API_KEYS_TABLE, array( 'key_id' => $key['key_id'] ), array( '%d' ) );
		}
	}
	
	/**
	 * Process the login form.
     *
     * @since 2.0.0
	 */
	public static function process_login() {
		// The global form-login.php template used `_wpnonce` in template versions < 3.3.0.
		$nonce_value = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$nonce_value = isset( $_POST['geodir-auth-login-nonce'] ) ? $_POST['geodir-auth-login-nonce'] : $nonce_value;

		if ( ! empty( $_POST['login'] ) && wp_verify_nonce( $nonce_value, 'geodir-auth-login' ) ) {

			try {
				$creds = array(
					'user_login'    => trim( $_POST['username'] ),
					'user_password' => $_POST['password'],
					'remember'      => isset( $_POST['rememberme'] ),
				);

				$validation_error = new WP_Error();
				$validation_error = apply_filters( 'geodir_process_login_errors', $validation_error, $_POST['username'], $_POST['password'] );

				if ( $validation_error->get_error_code() ) {
					throw new Exception( '<strong>' . __( 'Error:', 'geodirectory' ) . '</strong> ' . $validation_error->get_error_message() );
				}

				if ( empty( $creds['user_login'] ) ) {
					throw new Exception( '<strong>' . __( 'Error:', 'geodirectory' ) . '</strong> ' . __( 'Username is required.', 'geodirectory' ) );
				}

				// On multisite, ensure user exists on current site, if not add them before allowing login.
				if ( is_multisite() ) {
					$user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

					if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
						add_user_to_blog( get_current_blog_id(), $user_data->ID, '' );
					}
				}

				// Perform the login
				$user = wp_signon( apply_filters( 'geodir_login_credentials', $creds ), is_ssl() );

				if ( is_wp_error( $user ) ) {
					$message = $user->get_error_message();
					$message = str_replace( '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', '<strong>' . esc_html( $creds['user_login'] ) . '</strong>', $message );
					throw new Exception( $message );
				} else {

					if ( ! empty( $_POST['redirect'] ) ) {
						$redirect = $_POST['redirect'];
					} elseif ( wp_get_raw_referer() ) {
						$redirect = wp_get_raw_referer();
					} else {
						$redirect = get_author_posts_url( $user->ID );
					}

					wp_redirect( wp_validate_redirect( apply_filters( 'geodir_login_redirect', remove_query_arg( 'gd_error', $redirect ), $user ), get_author_posts_url( $user->ID ) ) );
					exit;
				}
			} catch ( Exception $e ) {
				geodir_error_log( apply_filters( 'login_errors', $e->getMessage() ), 'error', __FILE__, __LINE__ );
				do_action( 'geodir_login_failed' );
			}
		}
	}
}
new GeoDir_Auth();

<?php
/**
 * Display notices in admin.
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 * @info        Uses GeoDir_Admin_Notices class as a base.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Notices Class.
 */
class GeoDir_Admin_Notices {

	/**
	 * Stores notices.
	 * @var array
	 */
	private static $notices = array();

	/**
	 * Array of notices - name => callback.
	 * @var array
	 */
	private static $core_notices = array(
		'install'             => 'install_notice',
		'update'              => 'update_notice',
		'try_aui'             => 'try_aui_notice',
		//'beta'                => 'beta_notice',
	);

	/**
	 * Constructor.
	 */
	public static function init() {
		self::$notices = get_option( 'geodirectory_admin_notices', array() );

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		//add_action( 'admin_notices', array( __CLASS__, 'set_admin_notices' ) );
		add_action( 'switch_theme', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'geodirectory_installed', array( __CLASS__, 'reset_admin_notices' ) );
		add_action( 'wp_loaded', array( __CLASS__, 'hide_notices' ) );
		add_action( 'shutdown', array( __CLASS__, 'store_notices' ) );

		if ( current_user_can( 'manage_options' ) ) {
			add_action( 'admin_print_styles', array( __CLASS__, 'add_notices' ) );
		}
	}
	public static function admin_init() {
		add_action( 'admin_notices', array( __CLASS__, 'set_admin_notices' ) );
	}

	/**
	 * Set admin notices.
	 *
	 * @since 2.1.0.10
	 */
	public static function set_admin_notices() {
		$page = ! empty( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';
		$section = ! empty( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : '';

		/**
		 * Regenerate Thumbnails
		 * Regenerate Thumbnails Advanced
		 * Perfect Images + Retina
		 */
		if ( ( $page == 'regenerate-thumbnails' && class_exists( 'RegenerateThumbnails' ) ) || ( $page == 'rta_generate_thumbnails' && defined( 'RTA_PLUGIN_VERSION' ) ) || ( $page == 'wr2x_dashboard' && defined( 'WR2X_VERSION' ) ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p class="my-2">' . wp_sprintf( __( 'GeoDirectory\'s images are not supported by this tool, please use our tool %1$shere%2$s to regenerate them.', 'geodirectory' ), '<a href="' . esc_url( admin_url( 'admin.php?page=gd-status&tab=tools#geodir_tool_generate_thumbnails' ) ) . '">', '</a>' ) . '</p></div>';
		}

		// Show notice for Fast AJAX mu-plugin file.
		if ( $page == 'gd-settings' && $section != 'developer' && geodir_get_option( 'fast_ajax' ) && ! file_exists( geodir_fast_ajax_plugin_file() ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p class="my-2">' . wp_sprintf( __( 'GeoDirectory Fast AJAX must-use plugin(mu-plugin) is missing. %1$sFast AJAX%2$s f will not work properly.', 'geodirectory' ), '<a href="' . esc_url( admin_url( 'admin.php?page=gd-settings&tab=general&section=developer#mainform' ) ) . '">', '</a>' ) . '</p></div>';
		}
	}

	/**
	 * Store notices to DB
	 */
	public static function store_notices() {
		update_option( 'geodirectory_admin_notices', self::get_notices() );
	}

	/**
	 * Get notices
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	}

	/**
	 * Remove all notices.
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	}

	/**
	 * Reset notices for themes when switched or a new version of GD is installed.
	 */
	public static function reset_admin_notices() {
		if ( ! current_theme_supports( 'geodirectory' ) && ! in_array( get_option( 'template' ), geodir_get_core_supported_themes() ) ) {
			self::add_notice( 'beta' );
		}
		self::add_notice( 'template_files' );
	}

	/**
	 * Show a notice.
	 * @param string $name
	 */
	public static function add_notice( $name ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );
	}

	/**
	 * Remove a notice from being displayed.
	 * @param  string $name
	 */
	public static function remove_notice( $name ) {
		self::$notices = array_diff( self::get_notices(), array( $name ) );
		delete_option( 'geodirectory_admin_notice_' . $name );
	}

	/**
	 * See if a notice is being shown.
	 * @param  string  $name
	 * @return boolean
	 */
	public static function has_notice( $name ) {
		return in_array( $name, self::get_notices() );
	}

	/**
	 * Hide a notice if the GET variable is set.
	 */
	public static function hide_notices() {
		if ( isset( $_GET['gd-hide-notice'] ) && isset( $_GET['_gd_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( $_GET['_gd_notice_nonce'], 'geodir_hide_notices_nonce' ) ) {
				wp_die( __( 'Action failed. Please refresh the page and retry.', 'geodirectory' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_die( __( 'Cheating&#8217; huh?', 'geodirectory' ) );
			}

			$hide_notice = sanitize_text_field( $_GET['gd-hide-notice'] );
			self::remove_notice( $hide_notice );
			do_action( 'geodirectory_hide_' . $hide_notice . '_notice' );
		}
	}

	/**
	 * Add notices + styles if needed.
	 */
	public static function add_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'geodirectory_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( __CLASS__, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( __CLASS__, 'output_custom_notices' ) );
				}
			}
		}
	}

	/**
	 * Add a custom notice.
	 * @param string $name
	 * @param string $notice_html
	 */
	public static function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'geodirectory_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	}

	/**
	 * Output any stored custom notices.
	 */
	public static function output_custom_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_option( 'geodirectory_admin_notice_' . $notice );

					if ( $notice_html ) {
						include( 'views/html-notice-custom.php' );
					}
				}
			}
		}
	}

	/**
	 * If we need to update, include a message with the update button.
	 */
	public static function update_notice() {
		if ( version_compare( get_option( 'geodirectory_db_version' ), '2.0.0.0', '<' ) ) {
			if ( version_compare( get_option( 'geodirectory_db_version' ), GEODIRECTORY_VERSION, '<' ) ) {
				$updater = new GeoDir_Background_Updater();
				if ( $updater->is_updating() || ! empty( $_GET['do_update_geodirectory'] ) ) {
					include( 'views/html-notice-updating.php' );
				} else {
					// gd lightbox
					wp_enqueue_script( 'geodir-lity' );
					wp_enqueue_style('geodir-admin-css');
					include( 'views/html-notice-v2-update.php' );
				}
			} else {
				include( 'views/html-notice-updated.php' );
			}
		} else {
			if ( version_compare( get_option( 'geodirectory_db_version' ), GEODIRECTORY_VERSION, '<' ) ) {
				$updater = new GeoDir_Background_Updater();
				if ( $updater->is_updating() || ! empty( $_GET['do_update_geodirectory'] ) ) {
					include( 'views/html-notice-updating.php' );
				} else {
					include( 'views/html-notice-update.php' );
				}
			} else {
				include( 'views/html-notice-updated.php' );
			}
		}
	}

	/**
	 * If we have just installed, show a message with the install pages button.
	 */
	public static function install_notice() {
		include( 'views/html-notice-install.php' );
	}

	/**
	 * Show the Theme Check notice.
	 */
	public static function beta_notice() {
		include( 'views/html-notice-beta.php' );
	}

	/**
	 * Maybe show a notice to try AUI.
	 */
	public static function try_aui_notice() {
		if(is_admin() && current_user_can( 'manage_options' )  && isset($_REQUEST['page']) && $_REQUEST['page']=='gd-settings' && !empty($_REQUEST['try-bootstrap']) ){
			geodir_update_option('design_style','bootstrap');
			self::remove_notice('try_aui');
			?>
			<div class="notice notice-success">
				<p><strong>GeoDirectory - </strong><?php _e( 'Congratulations your site is now set to use the new Bootstrap styles!', 'geodirectory' ); ?></p>
			</div>
			<?php
		}elseif(geodir_get_option("design_style")!='bootstrap'){
			include( 'views/html-notice-aui.php' );
		}

	}


}

GeoDir_Admin_Notices::init();

<?php
/**
 * Installation related functions and actions.
 *
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Classes
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Admin_Install Class.
 */
class GeoDir_Admin_Install {

	/** @var array DB updates and callbacks that need to be run per version */
	private static $db_updates = array(
		'2.0.0.0' => array(
			'geodir_update_200_settings',
			'geodir_update_200_fields',
			'geodir_update_200_terms',
			'geodir_update_200_posts',
			'geodir_update_200_merge_data',
			'geodir_update_200_db_version',
		),
		'2.0.0.60' => array(
			'geodir_upgrade_20060',
		),
		'2.0.0.64' => array(
			'geodir_upgrade_20064',
		),
		'2.0.0.82' => array(
			'geodir_upgrade_20082',
		),
		'2.0.0.96' => array(
			'geodir_upgrade_20096',
		),
		'2.1.0.16' => array(
			'geodir_upgrade_21016',
		)
	);

	/** @var object Background update class */
	private static $background_updater;

	/**
	 * Hook in tabs.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_action( 'init', array( __CLASS__, 'init_background_updater' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'install_actions' ) );
		//add_filter( 'plugin_action_links_' . GEODIRECTORY_PLUGIN_BASENAME, array( __CLASS__, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
		add_filter( 'wpmu_drop_tables', array( __CLASS__, 'wpmu_drop_tables' ) );
		add_filter( 'cron_schedules', array( __CLASS__, 'cron_schedules' ) );
		//add_action( 'geodir_plugin_background_installer', array( __CLASS__, 'background_installer' ), 10, 2 );

		add_filter('upgrader_package_options',array( __CLASS__, 'maybe_downgrade_v1'));

		// Actions on plugin activated.
		add_action( 'geodir_on_activate_core_plugin', array( __CLASS__, 'plugin_activated' ), 99 );

		// Actions on plugin installed.
		add_action( 'geodirectory_installed', array( __CLASS__, 'plugin_installed' ), 99 );
	}

	/**
	 * Used to allow downgrade if a user installs v2 but is not ready to convert yet.
	 *
	 * @param $options
	 *
	 * @return mixed
	 */
	public static function maybe_downgrade_v1( $options ) {
		if (
			! empty( $_REQUEST['geodir_downgrade'] )
			&& ! empty( $options['package'] )
			&& strpos( $options['package'], "https://downloads.wordpress.org/plugin/geodirectory." ) === 0
//			&& strpos( $options['package'], "https://downloads.wordpress.org/plugin/advanced-cron-manager." ) === 0 // @todo remove after testing
			&& version_compare( get_option( 'geodirectory_db_version' ), '2.0.0.0', '<' )
		) {
			$options['package'] = "https://downloads.wordpress.org/plugin/geodirectory.1.6.38.zip";
//			$options['package'] = "https://downloads.wordpress.org/plugin/advanced-cron-manager.2.2.2.zip"; //@todo remove after testing
			$options['abort_if_destination_exists'] = false;
		}

		return $options;
	}

	/**
	 * Init background updates
	 */
	public static function init_background_updater() {
		include_once( GEODIRECTORY_PLUGIN_DIR . 'includes/class-geodir-background-updater.php' );
		self::$background_updater = new GeoDir_Background_Updater();
	}

	/**
	 * Check GeoDirectory version and run the updater as required.
	 *
	 * This check is done on all requests and runs if the versions do not match.
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) ) {
			if ( self::is_v2_upgrade() ) {
				GeoDir_Admin_Notices::add_notice( 'update' );
			} else if ( get_option( 'geodirectory_version' ) !== GeoDir()->version ) {
				self::install();
				do_action( 'geodirectory_updated' );
			}
		}
	}

	/**
	 * Install actions when a update button is clicked within the admin area.
	 *
	 * This function is hooked into admin_init to affect admin only.
	 */
	public static function install_actions() {
		if ( ! empty( $_GET['do_update_geodirectory'] ) ) {
			self::update();
			GeoDir_Admin_Notices::add_notice( 'update' );
		}
		if ( ! empty( $_GET['force_update_geodirectory'] ) ) {
			$blog_id = get_current_blog_id();

			// Used to fire an action added in WP_Background_Process::_construct() that calls WP_Background_Process::handle_cron_healthcheck().
			// This method will make sure the database updates are executed even if cron is disabled. Nothing will happen if the updates are already running.
			do_action( 'wp_' . $blog_id . '_geodir_updater_cron' );

			wp_safe_redirect( admin_url( 'admin.php?page=gd-settings' ) );
			exit;
		}
	}

	/**
	 * Install GeoDirectory.
	 */
	public static function install() {
		global $wpdb;

		if ( ! is_blog_installed() ) {
			return;
		}

		if ( ! defined( 'GD_INSTALLING' ) ) {
			define( 'GD_INSTALLING', true );
		}

		// Ensure needed classes are loaded
		//include_once( dirname( __FILE__ ) . '/class-geodir-admin-notices.php' );

		self::upgrades(); // do any db upgrades
		self::remove_admin_notices();
		self::create_tables();
		self::create_options();
		self::insert_default_fields();
		self::insert_default_tabs();
		self::create_pages();

		// Register post types
		GeoDir_Post_types::register_post_types();
		// Register listing status
		GeoDir_Post_types::register_post_status();
		GeoDir_Post_types::register_taxonomies();

		self::create_uncategorized_categories();

		// Also register endpoints - this needs to be done prior to rewrite rule flush
		//()->query->init_query_vars();
		//()->query->add_endpoints();
		//_API::add_endpoint();
		//_Auth::add_endpoint();

		self::create_cron_jobs();

		// Queue upgrades/setup wizard
		self::maybe_enable_setup_wizard();

		// Update GD version
		self::update_gd_version();

		// Update DB version
		self::maybe_update_db_version();

		// Flush rules after install
		do_action( 'geodir_flush_rewrite_rules' );

		// Trigger action
		do_action( 'geodirectory_installed' );
	}

	/**
	 * Reset any notices added to admin.
	 *
	 * @since 2.0.0
	 */
	private static function remove_admin_notices() {
		GeoDir_Admin_Notices::remove_all_notices();
	}

	/**
	 * Is this a brand new GeoDirectory install?
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	private static function is_new_install() {
		return is_null( get_option( 'geodirectory_version', null ) ) && is_null( get_option( 'geodirectory_db_version', null ) );
	}

	/**
	 * Is v1 to v2 upgrade.
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	private static function is_v2_upgrade() {
		if ( self::is_new_install() ) {
			return false;
		}

		if ( get_option( 'geodirectory_db_version' ) && version_compare( get_option( 'geodirectory_db_version' ), '2.0.0.0', '<' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Is a DB update needed?
	 *
	 * @since 2.0.0
	 * @return boolean
	 */
	private static function needs_db_update() {
		$current_db_version = get_option( 'geodirectory_db_version', null );
		$updates            = self::get_db_update_callbacks();

		return ! is_null( $current_db_version ) && ! empty( $updates ) && version_compare( $current_db_version, max( array_keys( $updates ) ), '<' );
	}

	/**
	 * See if we need the wizard or not.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_enable_setup_wizard() {
		if ( apply_filters( 'geodirectory_enable_setup_wizard', self::is_new_install() ) ) {
			GeoDir_Admin_Notices::add_notice( 'install' );
			set_transient( '_gd_activation_redirect', 1, 30 );
		} elseif ( ! geodir_get_option( 'page_archive' ) ) {
			GeoDir_Admin_Notices::add_notice( 'install' );
		}
	}

	/**
	 * Insert the default field for the CPTs.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Optional. Post type. Default gd_place.
	 */
	public static function insert_default_fields($post_type = 'gd_place'){
		$fields = GeoDir_Admin_Dummy_Data::default_custom_fields($post_type);

		/**
		 * Filter the array of default custom fields DB table data.
		 *
		 * @since 1.0.0
		 * @param string $fields The default custom fields as an array.
		 */
		$fields = apply_filters('geodir_before_default_custom_fields_saved', $fields);
		foreach ($fields as $field_index => $field) {
			geodir_custom_field_save($field);
		}
	}

	/**
	 * Insert the default field for the CPTs.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Optional. Post type. Default gd_place.
	 */
	public static function insert_default_tabs($post_type = 'gd_place'){
		$fields = array();

		// Profile / description
		$fields[] = array(
			'post_type'     => $post_type,
			'tab_layout'    => 'post',
			'tab_type'      => 'meta',
			'tab_name'      => __('Profile','geodirectory'),
			'tab_icon'      => 'fas fa-home',
			'tab_key'       => 'post_content',
			'tab_content'   => '',
			'sort_order'    => '1',
			'tab_level'     => '0'
		);

		// Photos
		$fields[] = array(
			'post_type'     => $post_type,
			'tab_layout'    => 'post',
			'tab_type'      => 'standard',
			'tab_name'      => __('Photos','geodirectory'),
			'tab_icon'      => 'fas fa-image',
			'tab_key'       => 'post_images',
			'tab_content'   => '[gd_post_images type="gallery" ajax_load="1" slideshow="1" show_title="1" animation="slide" controlnav="1" link_to="lightbox"]',
			'sort_order'    => '2',
			'tab_level'     => '0'
		);

		// Photos
		$fields[] = array(
			'post_type'     => $post_type,
			'tab_layout'    => 'post',
			'tab_type'      => 'standard',
			'tab_name'      => __('Map','geodirectory'),
			'tab_icon'      => 'fas fa-globe-americas',
			'tab_key'       => 'post_map',
			'tab_content'   => '[gd_map width="100%" height="325px" maptype="ROADMAP" zoom="0" map_type="post" map_directions="1"]',
			'sort_order'    => '3',
			'tab_level'     => '0'
		);

		// Reviews
		$fields[] = array(
			'post_type'     => $post_type,
			'tab_layout'    => 'post',
			'tab_type'      => 'standard',
			'tab_name'      => __('Reviews','geodirectory'),
			'tab_icon'      => 'fas fa-comments',
			'tab_key'       => 'reviews',
			'tab_content'   => '',
			'sort_order'    => '4',
			'tab_level'     => '0'
		);

		/**
		 * Filter the array of default tabs DB table data.
		 *
		 * @since 1.0.0
		 * @param string $fields The default tabs fields as an array.
		 */
		$fields = apply_filters('geodir_before_default_tabs_saved', $fields);
		$has_tabs = GeoDir_Settings_Cpt_Tabs::get_tabs_fields($post_type);
		if(empty($has_tabs)){
			foreach ($fields as $field_index => $field) {
				GeoDir_Settings_Cpt_Tabs::save_tab_item( $field );
			}
		}
	}

	/**
	 * See if we need to show or run database updates during install.
	 *
	 * @since 2.0.0
	 */
	private static function maybe_update_db_version() {
		if ( self::needs_db_update() ) {
			if ( apply_filters( 'geodir_enable_auto_update_db', false ) ) {
				self::update();
			} else {
				GeoDir_Admin_Notices::add_notice( 'update' );
			}
		} else {
			self::update_db_version();
		}
	}

	/**
	 * Update GeoDirectory version to current.
	 *
	 * @since 2.0.0
	 */
	private static function update_gd_version() {
		delete_option( 'geodirectory_version' );
		add_option( 'geodirectory_version', GEODIRECTORY_VERSION );
	}

	/**
	 * Get list of DB update callbacks.
	 *
	 * @since  3.0.0
	 * @return array
	 */
	public static function get_db_update_callbacks() {
		return self::$db_updates;
	}

	/**
	 * Push all needed DB updates to the queue for processing.
	 *
	 * @since 2.0.0
	 */
	private static function update() {
		$current_db_version = get_option( 'geodirectory_db_version' );
		$update_queued      = false;

		foreach ( self::get_db_update_callbacks() as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					geodir_error_log( sprintf( 'Queuing %s - %s', $version, $update_callback ) );
					self::$background_updater->push_to_queue( $update_callback );
					$update_queued = true;
				}
			}
		}

		if ( $update_queued ) {
			self::$background_updater->save()->dispatch();
		}
	}

	/**
	 * Update DB version to current.
	 * @param string $version
	 */
	public static function update_db_version( $version = null ) {
		delete_option( 'geodirectory_db_version' );
		add_option( 'geodirectory_db_version', is_null( $version ) ? GEODIRECTORY_VERSION : $version );
	}

	/**
	 * Add more cron schedules.
	 * @param  array $schedules
	 * @return array
	 */
	public static function cron_schedules( $schedules ) {
		// Kadence starter templates page is broken when monthly schedule option is set.
		if ( ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] == 'kadence-starter-templates' ) || ( ! empty( $_REQUEST['action'] ) && in_array( $_REQUEST['action'], array( 'kadence_check_plugin_data', 'kadence_import_get_template_data', 'kadence_import_install_plugins' ) ) ) ) {
			return $schedules;
		}

		$schedules['monthly'] = array(
			'interval' => 2635200,
			'display'  => __( 'Once Monthly', 'geodirectory' ),
		);
		return $schedules;
	}

	/**
	 * Create cron jobs (clear them first).
	 *
	 * @since 2.0.0
	 */
	private static function create_cron_jobs() {
		//@todo add crons here
		wp_clear_scheduled_hook( 'geodirectory_tracker_send_event' );
		wp_schedule_event( time(), apply_filters( 'geodirectory_tracker_event_recurrence', 'daily' ), 'geodirectory_tracker_send_event' );
	}

	/**
	 * Create pages that the plugin relies on, storing page IDs in variables.
	 *
	 * @since 2.0.0
	 */
	public static function create_pages() {
		$gutenberg = geodir_is_gutenberg();

		$pages = apply_filters( 'geodirectory_create_pages', array(
			'page_add' => array(
				'name'    => _x( 'add-listing', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Add Listing', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_add_content(false, $gutenberg),
			),
			'page_search' => array(
				'name'    => _x( 'search', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Search page', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_search_content(false, $gutenberg),
			),
			'page_terms_conditions' => array(
				'name'    => _x( 'terms-and-conditions', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Terms and Conditions', 'Page title', 'geodirectory'),
				'content' => __('ENTER YOUR SITE TERMS AND CONDITIONS HERE','geodirectory'),
			),
			'page_location' => array(
				'name'    => _x( 'location', 'Page slug', 'geodirectory'),
				'title'   => _x( 'Location', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_location_content(false, $gutenberg),
			),
			'page_archive' => array(
				'name'    => _x( 'gd-archive', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_archive_content(false, $gutenberg),
			),
			'page_archive_item' => array(
				'name'    => _x( 'gd-archive-item', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Archive Item', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_archive_item_content(false, $gutenberg),
			),
			'page_details' => array(
				'name'    => _x( 'gd-details', 'Page slug', 'geodirectory'),
				'title'   => _x( 'GD Details', 'Page title', 'geodirectory'),
				'content' => GeoDir_Defaults::page_details_content(false, $gutenberg),
			),
		) );

		foreach ( $pages as $key => $page ) {
			geodir_create_page( esc_sql( $page['name'] ), $key , $page['title'], $page['content']);
		}

		delete_transient( 'geodir_cache_excluded_uris' );
	}

	/**
	 * Create a category for each CPT.
	 *
	 * So users can start adding posts right away.
	 *
	 * @since 2.0.0
	 */
	public static function create_uncategorized_categories(){
		$post_types = geodir_get_posttypes();
		if(!empty($post_types)){
			foreach($post_types as $post_type){
				$taxonomy = $post_type . 'category';

				if ( ! get_option( $taxonomy . '_installed', false ) ) {
					$dummy_categories['uncategorized'] = array(
						'name'        => 'Uncategorized',
						'icon'        => GEODIRECTORY_PLUGIN_URL . '/assets/images/pin.png',
						'schema_type' => ''
					);
					GeoDir_Admin_Dummy_Data::create_taxonomies( $post_type, $dummy_categories );
					update_option($taxonomy.'_installed',true);
				}
			}
		}
	}

	/**
	 * Default options.
	 *
	 * Sets up the default options used on the settings page.
	 *
	 * @since 2.0.0
	 */
	private static function create_options() {
		// Before set default options.
		self::before_create_options();

		// Include settings so that we can run through defaults
		include_once( dirname( __FILE__ ) . '/class-geodir-admin-settings.php' );

		$current_settings = geodir_get_settings();

		$settings = GeoDir_Admin_Settings::get_settings_pages();

		foreach ( $settings as $section ) {
			if ( ! method_exists( $section, 'get_settings' ) ) {
				continue;
			}
			$subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

			foreach ( $subsections as $subsection ) {
				foreach ( $section->get_settings( $subsection ) as $value ) {
					if ( !isset($current_settings[$value['id']]) && isset( $value['default'] ) && isset( $value['id'] ) ) {
						geodir_update_option($value['id'], $value['default']);
					}
				}
			}
		}
	}

	/**
	 * Set up the database tables which the plugin needs to function.
	 *
	 * Tables:
	 *		geodir_api_keys - API keys table.
	 *		geodir_attachments - Listing attachments table.
	 *		geodir_business_hours - Business hours table.
	 *		geodir_custom_fields - Custom fields table.
	 *		geodir_custom_sort_fields - Custom fields sorting table.
	 *		geodir_post_review - Listing reviews table.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @since 2.0.0
	 */
	public static function create_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		dbDelta( self::get_schema() );
	}

	/**
	 * Get Table schema.
	 *
	 * A note on indexes; Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
	 * As of WordPress 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
	 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
	 *
	 * Changing indexes may cause duplicate index notices in logs due to https://core.trac.wordpress.org/ticket/34870 but dropping
	 * indexes first causes too much load on some servers/larger DB.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global object $plugin_prefix WordPress plugin prefix.
	 *
	 * @return string $tables.
	 */
	public static function get_schema() {
		global $wpdb, $plugin_prefix;

		/*
		 * Indexes have a maximum size of 767 bytes. Historically, we haven't need to be concerned about that.
		 * As of 4.2, however, we moved to utf8mb4, which uses 4 bytes per character. This means that an index which
		 * used to have room for floor(767/3) = 255 characters, now only has room for floor(767/4) = 191 characters.
		 */
		$max_index_length = 191;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}

		// Table for storing post custom fields - these are user defined
		$tables = "
CREATE TABLE " . GEODIR_CUSTOM_FIELDS_TABLE . " (
	id int(11) NOT NULL AUTO_INCREMENT,
	post_type varchar(100) NULL,
	data_type varchar(100) NULL DEFAULT NULL,
	field_type varchar(255) NOT NULL COMMENT 'text,checkbox,radio,select,textarea',
	field_type_key varchar(255) NOT NULL,
	admin_title varchar(255) NULL DEFAULT NULL,
	frontend_desc text NULL DEFAULT NULL,
	frontend_title varchar(255) NULL DEFAULT NULL,
	htmlvar_name varchar(255) NULL DEFAULT NULL,
	default_value text NULL DEFAULT NULL,
	placeholder_value text NULL DEFAULT NULL,
	sort_order int(11) NOT NULL,
	tab_parent varchar(100) NOT NULL DEFAULT '0',
	tab_level int(11) NOT NULL DEFAULT '0',
	option_values text NULL DEFAULT NULL,
	clabels text NULL DEFAULT NULL,
	is_active tinyint(1) NOT NULL DEFAULT '1',
	is_default tinyint(1) NOT NULL DEFAULT '0',
	is_required tinyint(1) NOT NULL DEFAULT '0',
	required_msg varchar(255) NULL DEFAULT NULL,
	show_in text NULL DEFAULT NULL,
	for_admin_use tinyint(1) NOT NULL DEFAULT '0',
	packages text NULL DEFAULT NULL,
	cat_sort text NULL DEFAULT NULL,
	cat_filter text NULL DEFAULT NULL,
	extra_fields text NULL DEFAULT NULL,
	field_icon varchar(255) NULL DEFAULT NULL,
	css_class varchar(255) NULL DEFAULT NULL,
	decimal_point varchar( 10 ) NOT NULL,
	validation_pattern varchar( 255 ) NOT NULL,
	validation_msg text NULL DEFAULT NULL,
	PRIMARY KEY  (id)
) $collate;";

		// Table for storing tabs layout settings
		$tables .= "
CREATE TABLE " . GEODIR_TABS_LAYOUT_TABLE . " (
	id int(11) NOT NULL AUTO_INCREMENT,
	post_type varchar(100) NULL,
	sort_order int(11) NOT NULL,
	tab_layout varchar(100) NOT NULL,
	tab_parent varchar(100) NOT NULL,
	tab_type varchar(100) NOT NULL,
	tab_level int(11) NOT NULL,
	tab_name varchar(255) NOT NULL,
	tab_icon varchar(255) NOT NULL,
	tab_key varchar(255) NOT NULL,
	tab_content text NULL DEFAULT NULL,
	PRIMARY KEY  (id)
) $collate;";

		// Run through all GD CPTs
		$post_types = geodir_get_option( 'post_types', array() );

		if ( empty( $post_types ) ) {
			// Table for storing place attribute - these are user defined
			$tables .= "
CREATE TABLE " . $plugin_prefix . "gd_place_detail (
	" . implode( ", \n	", self::db_cpt_default_columns() ) . ",
	" . implode( ", \n	", self::db_cpt_default_keys() ) . "
) $collate;";
		} else {
			foreach ( $post_types as $post_type => $cpt ) {
				// Table for storing place attribute - these are user defined
				$tables .= "
CREATE TABLE " . $plugin_prefix . $post_type . "_detail (
	" . implode( ", \n	", self::db_cpt_default_columns( $cpt, $post_type ) ) . ",
	" . implode( ", \n	", self::db_cpt_default_keys( $cpt, $post_type ) ) . "
) $collate;";
			}
		}

		// Table for storing place images - these are user defined
		$tables .= "
CREATE TABLE " . GEODIR_ATTACHMENT_TABLE . " (
	ID int(11) NOT NULL AUTO_INCREMENT,
	post_id bigint(20) NOT NULL,
	date_gmt datetime NULL default null,
	user_id int(11) DEFAULT NULL,
	other_id int(11) DEFAULT NULL,
	title varchar(254) NULL DEFAULT NULL,
	caption varchar(254) NULL DEFAULT NULL,
	file varchar(254) NOT NULL,
	mime_type varchar(150) NOT NULL,
	menu_order int(11) NOT NULL DEFAULT '0',
	featured tinyint(1) NULL DEFAULT '0',
	is_approved tinyint(1) NULL DEFAULT '1',
	metadata text NULL DEFAULT NULL,
	`type` varchar(254) NULL DEFAULT 'post_images',
	PRIMARY KEY  (ID),
	KEY post_id (post_id),
	KEY `type` (`type`($max_index_length))
) $collate;";

		// Table for storing custom sort fields
		$tables .= "
CREATE TABLE " . GEODIR_CUSTOM_SORT_FIELDS_TABLE . " (
	id int(11) NOT NULL AUTO_INCREMENT,
	post_type varchar(255) NOT NULL,
	data_type varchar(255) NOT NULL,
	field_type varchar(255) NOT NULL,
	frontend_title varchar(255) NOT NULL,
	htmlvar_name varchar(255) NOT NULL,
	sort_order int(11) NOT NULL,
	tab_parent varchar(100) NOT NULL DEFAULT '0',
	tab_level int(11) NOT NULL DEFAULT '0',
	is_active int(11) NOT NULL,
	is_default int(11) NOT NULL,
	sort varchar(5) DEFAULT 'asc',
	PRIMARY KEY  (id)
) $collate;";

		// Table for storing review info
		/**
		 * UNIQUE KEY replaced to PRIMARY KEY to prevent database error:
		 * "Percona-XtraDB-Cluster prohibits use of DML command on a table without an explicit
		 * primary key with pxc_strict_mode = ENFORCING or MASTER"
		 */
		$tables .= "
CREATE TABLE " . GEODIR_REVIEW_TABLE . " (
	comment_id bigint(20) UNSIGNED NOT NULL,
	post_id bigint(20) DEFAULT '0',
	user_id bigint(20) DEFAULT '0',
	rating float DEFAULT '0',
	ratings text NOT NULL,
	attachments text NOT NULL,
	post_type varchar(20) DEFAULT '',
	city varchar(50) DEFAULT '',
	region varchar(50) DEFAULT '',
	country varchar(50) DEFAULT '',
	latitude varchar(22) DEFAULT '',
	longitude varchar(22) DEFAULT '',
	PRIMARY KEY (comment_id)
) $collate;";

		// Table to store api keys
		$tables .= "
CREATE TABLE " . GEODIR_API_KEYS_TABLE . " (
	key_id BIGINT UNSIGNED NOT NULL auto_increment,
	user_id BIGINT UNSIGNED NOT NULL,
	description varchar(200) NULL,
	permissions varchar(10) NOT NULL,
	consumer_key char(64) NOT NULL,
	consumer_secret char(43) NOT NULL,
	nonces longtext NULL,
	truncated_key char(7) NOT NULL,
	last_access datetime NULL default null,
	PRIMARY KEY  (key_id),
	KEY consumer_key (consumer_key),
	KEY consumer_secret (consumer_secret)
) $collate;";

		$tables .= "
CREATE TABLE " . GEODIR_POST_REPORTS_TABLE . " (
	`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`post_id` bigint(20) UNSIGNED NOT NULL,
	`user_id` bigint(20) UNSIGNED NOT NULL,
	`user_ip` varchar(200) DEFAULT NULL,
	`user_name` varchar(100) NOT NULL,
	`user_email` varchar(100) NOT NULL,
	`reason` varchar(200) NOT NULL,
	`message` text NOT NULL,
	`status` varchar(50) NOT NULL,
	`report_date` datetime DEFAULT NULL,
	`updated_date` datetime DEFAULT NULL,
	PRIMARY KEY  (`id`),
	KEY `post_id` (`post_id`),
	KEY `user_id` (`user_id`)
) $collate;";

		return $tables;
	}

	/**
	 * Show plugin changes. Code adapted from W3 Total Cache.
	 *
	 * @since 2.0.0
	 *
	 * @param array $args {
	 *      An array to update message arguments.
	 *
	 *      @type string $Version Plugin version.
	 * }
	 */
	public static function in_plugin_update_message( $args ) {
		$transient_name = 'gd_upgrade_notice_' . $args['Version'];

		if ( false === ( $upgrade_notice = get_transient( $transient_name ) ) ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/geodirectory/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'], $args['new_version'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content
	 * @param  string $new_version
	 * @return string
	 */
	private static function parse_update_notice( $content, $new_version ) {
		// Output Upgrade Notice.
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( GEODIRECTORY_VERSION ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			// Convert the full version strings to minor versions.
			$notice_version_parts  = explode( '.', trim( $matches[1] ) );
			$current_version_parts = explode( '.', GEODIRECTORY_VERSION );

			if ( 3 !== sizeof( $notice_version_parts ) ) {
				return;
			}

			$notice_version  = $notice_version_parts[0] . '.' . $notice_version_parts[1];
			$current_version = $current_version_parts[0] . '.' . $current_version_parts[1];

			// Check the latest stable version and ignore trunk.
			if ( version_compare( $current_version, $notice_version, '<' ) ) {

				$upgrade_notice .= '</p><p class="gd_plugin_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
				}
			}
		}

		return wp_kses_post( $upgrade_notice );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param	mixed $links Plugin Action links
	 * @return	array
	 */
	public static function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=gd-settings' ) . '" aria-label="' . esc_attr__( 'View GeoDirectory settings', 'geodirectory' ) . '">' . esc_html__( 'Settings', 'geodirectory' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @param	mixed $links Plugin Row Meta
	 * @param	mixed $file  Plugin Base file
	 * @return	array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( GEODIRECTORY_PLUGIN_BASENAME == $file ) {
			$row_meta = array(
				'docs'    => '<a href="' . esc_url( apply_filters( 'geodirectory_docs_url', 'https://docs.wpgeodirectory.com/' ) ) . '" aria-label="' . esc_attr__( 'View GeoDirectory documentation', 'geodirectory' ) . '">' . esc_html__( 'Docs', 'geodirectory' ) . '</a>',
				'support' => '<a href="' . esc_url( apply_filters( 'geodirectory_support_url', 'https://wpgeodirectory.com/support/' ) ) . '" aria-label="' . esc_attr__( 'Visit GeoDirectory support', 'geodirectory' ) . '">' . esc_html__( 'Support', 'geodirectory' ) . '</a>',
				'translation' => '<a href="' . esc_url( apply_filters( 'geodirectory_translation_url', 'https://wpgeodirectory.com/translate/projects' ) ) . '" aria-label="' . esc_attr__( 'View translations', 'geodirectory' ) . '">' . esc_html__( 'Translations', 'geodirectory' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Return a list of GeoDirectory tables. Used to make sure all WC tables are dropped when uninstalling the plugin
	 * in a single site or multi site environment.
	 *
	 * @return array GeoDir tables.
	 */
	public static function get_tables() {
		global $wpdb;

		$db_prefix = $wpdb->prefix;
		$gd_prefix = 'geodir_';

		$tables = array();
		$tables["{$gd_prefix}api_keys"] = "{$db_prefix}{$gd_prefix}api_keys";
		$tables["{$gd_prefix}attachments"] = "{$db_prefix}{$gd_prefix}attachments";
		$tables["{$gd_prefix}custom_fields"] = "{$db_prefix}{$gd_prefix}custom_fields";
		$tables["{$gd_prefix}custom_sort_fields"] = "{$db_prefix}{$gd_prefix}custom_sort_fields";
		$tables["{$gd_prefix}post_review"] = "{$db_prefix}{$gd_prefix}post_review";
		$tables["{$gd_prefix}tabs_layout"] = "{$db_prefix}{$gd_prefix}tabs_layout";
		$tables["{$gd_prefix}post_reports"] = "{$db_prefix}{$gd_prefix}post_reports";

		$post_types = array_keys( (array) geodir_get_option( 'post_types' ) );
		if ( ! empty( $post_types ) ) {
			foreach ( $post_types as $post_type ) {
				$tables["{$gd_prefix}{$post_type}_detail"] = "{$db_prefix}{$gd_prefix}{$post_type}_detail";
			}
		}

		/**
		 * Filter the list of known GeoDirectory tables.
		 *
		 * If GeoDirectory plugins need to add new tables, they can inject them here.
		 *
		 * @param array $tables An array of GeoDirectory-specific database table names.
		 */
		$tables = apply_filters( 'geodir_install_get_tables', $tables );

		return $tables;
	}

	/**
	 * Drop GeoDirectory tables.
	 *
	 * @return void
	 */
	public static function drop_tables() {
		global $wpdb;

		$tables = self::get_tables();

		foreach ( $tables as $key => $table ) {
			$wpdb->query( "DROP TABLE IF EXISTS {$table}" ); // phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		}
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables List of tables that will be deleted by WP.
	 * @return string[]
	 */
	public static function wpmu_drop_tables( $tables ) {
		return array_merge( $tables, self::get_tables() );
	}

	/**
	 * Get slug from path
	 * @param  string $key
	 * @return string
	 */
	private static function format_plugin_slug( $key ) {
		$slug = explode( '/', $key );
		$slug = explode( '.', end( $slug ) );
		return $slug[0];
	}

	/**
	 * Install a plugin from .org in the background via a cron job (used by
	 * installer - opt in).
	 * @param string $plugin_to_install_id
	 * @param array $plugin_to_install
	 * @since 2.6.0
	 */
	public static function background_installer( $plugin_to_install_id, $plugin_to_install ) {
		// Explicitly clear the event.
		wp_clear_scheduled_hook( 'geodir_plugin_background_installer', func_get_args() );

		if ( ! empty( $plugin_to_install['repo-slug'] ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
			require_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
			require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

			WP_Filesystem();

			$skin              = new Automatic_Upgrader_Skin;
			$upgrader          = new WP_Upgrader( $skin );
			$installed_plugins = array_map( array( __CLASS__, 'format_plugin_slug' ), array_keys( get_plugins() ) );
			$plugin_slug       = $plugin_to_install['repo-slug'];
			$plugin            = $plugin_slug . '/' . $plugin_slug . '.php';
			$installed         = false;
			$activate          = false;

			// See if the plugin is installed already
			if ( in_array( $plugin_to_install['repo-slug'], $installed_plugins ) ) {
				$installed = true;
				$activate  = ! is_plugin_active( $plugin );
			}

			// Install this thing!
			if ( ! $installed ) {
				// Suppress feedback
				ob_start();

				try {
					$plugin_information = plugins_api( 'plugin_information', array(
						'slug'   => $plugin_to_install['repo-slug'],
						'fields' => array(
							'short_description' => false,
							'sections'          => false,
							'requires'          => false,
							'rating'            => false,
							'ratings'           => false,
							'downloaded'        => false,
							'last_updated'      => false,
							'added'             => false,
							'tags'              => false,
							'homepage'          => false,
							'donate_link'       => false,
							'author_profile'    => false,
							'author'            => false,
						),
					) );

					if ( is_wp_error( $plugin_information ) ) {
						throw new Exception( $plugin_information->get_error_message() );
					}

					$package  = $plugin_information->download_link;
					$download = $upgrader->download_package( $package );

					if ( is_wp_error( $download ) ) {
						throw new Exception( $download->get_error_message() );
					}

					$working_dir = $upgrader->unpack_package( $download, true );

					if ( is_wp_error( $working_dir ) ) {
						throw new Exception( $working_dir->get_error_message() );
					}

					$result = $upgrader->install_package( array(
						'source'                      => $working_dir,
						'destination'                 => WP_PLUGIN_DIR,
						'clear_destination'           => false,
						'abort_if_destination_exists' => false,
						'clear_working'               => true,
						'hook_extra'                  => array(
							'type'   => 'plugin',
							'action' => 'install',
						),
					) );

					if ( is_wp_error( $result ) ) {
						throw new Exception( $result->get_error_message() );
					}

					$activate = true;

				} catch ( Exception $e ) {
					GeoDir_Admin_Notices::add_custom_notice(
						$plugin_to_install_id . '_install_error',
						sprintf(
							__( '%1$s could not be installed (%2$s). <a href="%3$s">Please install it manually by clicking here.</a>', 'geodirectory' ),
							$plugin_to_install['name'],
							$e->getMessage(),
							esc_url( admin_url( 'index.php?gd-install-plugin-redirect=' . $plugin_to_install['repo-slug'] ) )
						)
					);
				}

				// Discard feedback
				ob_end_clean();
			}

			wp_clean_plugins_cache();

			// Activate this thing
			if ( $activate ) {
				try {
					$result = activate_plugin( $plugin );

					if ( is_wp_error( $result ) ) {
						throw new Exception( $result->get_error_message() );
					}
				} catch ( Exception $e ) {
					GeoDir_Admin_Notices::add_custom_notice(
						$plugin_to_install_id . '_install_error',
						sprintf(
							__( '%1$s was installed but could not be activated. <a href="%2$s">Please activate it manually by clicking here.</a>', 'geodirectory' ),
							$plugin_to_install['name'],
							admin_url( 'plugins.php' )
						)
					);
				}
			}
		}
	}

	/**
	 * Get the Custom Post Type database default fields.
	 *
	 * @since 2.0.0
	 * @since 2.0.0.82 Added _search_title column.
	 *
	 * @param bool $cpt CPT parameters.
	 * @param string $post_type The post type.
	 * @return array The array of default fields.
	 */
	public static function db_cpt_default_columns( $cpt = array(), $post_type = '' ) {
		$columns = array();

		// Standard fields
		$columns['post_id'] = "post_id bigint(20) NOT NULL";
		$columns['post_title'] = "post_title text NULL DEFAULT NULL";
		$columns['_search_title'] = "_search_title text NOT NULL";
		$columns['post_status'] = "post_status varchar(20) NULL DEFAULT NULL";
		$columns['post_tags'] = "post_tags text NULL DEFAULT NULL";
		$columns['post_category'] = "post_category text NULL DEFAULT NULL";
		$columns['default_category'] = "default_category INT NULL DEFAULT NULL";
		$columns['featured'] = "featured tinyint(1) NOT NULL DEFAULT '0'";
		$columns['featured_image'] = "featured_image varchar( 254 ) NULL DEFAULT NULL";
		$columns['submit_ip'] = "submit_ip varchar(100) NULL DEFAULT NULL";
		$columns['overall_rating'] = "overall_rating float(11) DEFAULT '0'";
		$columns['rating_count'] = "rating_count int(11) DEFAULT '0'";

		// Location fields
		if ( ! isset( $cpt['disable_location'] ) || ! $cpt['disable_location'] ) {
			$columns['street'] = "street VARCHAR( 254 ) NULL";
			$columns['street2'] = "street2 VARCHAR( 254 ) NULL";
			$columns['city'] = "city VARCHAR( 50 ) NULL";
			$columns['region'] = "region VARCHAR( 50 ) NULL";
			$columns['country'] = "country VARCHAR( 50 ) NULL";
			$columns['zip'] = "zip VARCHAR( 50 ) NULL";
			$columns['latitude'] = "latitude VARCHAR( 22 ) NULL";
			$columns['longitude'] = "longitude VARCHAR( 22 ) NULL";
			$columns['mapview'] = "mapview VARCHAR( 15 ) NULL";
			$columns['mapzoom'] = "mapzoom VARCHAR( 3 ) NULL";
		}

		return apply_filters( 'geodir_db_cpt_default_columns', $columns, $cpt, $post_type );
	}

	/**
	 * Get the Custom Post Type database default keys.
	 *
	 * @param bool $cpt CPT parameters.
	 * @param string $post_type The post type.
	 * @since 2.0.0
	 *
	 * @return array The array of default fields.
	 */
	public static function db_cpt_default_keys( $cpt = array(), $post_type = '' ) {
		$keys = array();

		// Standard keys
		$keys['post_id'] = "PRIMARY KEY  (post_id)";

		// Location keys
		if ( ! isset( $cpt['disable_location'] ) || ! $cpt['disable_location'] ) {
			$keys['country'] = "KEY country (country(50))";
			$keys['region'] = "KEY region (region(50))";
			$keys['city'] = "KEY city (city(50))";
		}

		return apply_filters( 'geodir_db_cpt_default_keys', $keys, $cpt, $post_type );
	}

	/**
	 * Run some upgrade scripts.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	public static function upgrades() {
		/**
		 * DB type change for post_images
		 */
		if ( get_option( 'geodirectory_version' ) && version_compare( get_option( 'geodirectory_version' ), '2.0.0.13-beta', '<=' ) ) {
			global $wpdb;
			$wpdb->query("UPDATE ".GEODIR_ATTACHMENT_TABLE." SET type='post_images' WHERE type='post_image'");
		}
	}

	/**
	 * Executed after plugin activated.
	 *
	 * @since 2.2.7
	 *
	 * @return void
	 */
	public static function plugin_activated() {
		// Check mu-plugins.
		self::check_mu_plugins();
	}

	/**
	 * Executed after plugin installed.
	 *
	 * @since 2.0.0.92
	 *
	 * @return void
	 */
	public static function plugin_installed() {
		// Rank Math schedule flush rewrite rules.
		if ( class_exists( 'RankMath\\Helper' ) ) {
			update_option( 'geodir_rank_math_flush_rewrite', 1 );
		}

		// Check mu-plugins.
		self::check_mu_plugins();
	}

	/**
	 * Execute before default options are set.
	 *
	 * @since 2.1.0.0
	 *
	 * @return void
	 */
	public static function before_create_options() {
		// Maybe add try AUI notice
		self::maybe_try_aui();
	}

	/**
	 * Check and set default AUI option value.
	 *
	 * @since 2.1.0.0
	 *
	 * @return void
	 */
	public static function maybe_try_aui() {
		if ( self::is_new_install() ) {
			// New installs should be set to use it by default.
		} else {
			if ( ( $geodirectory_version = get_option( 'geodirectory_version' ) ) ) {
				// Update blank to set default value for design_style.
				if ( version_compare( $geodirectory_version, '2.0.9.0', '<' ) && ! geodir_get_option( 'design_style' ) ) {
					geodir_update_option( 'design_style', '' );

					GeoDir_Admin_Notices::add_notice( 'try_aui' );
				}

				// Update blank to set default value for fast_ajax.
				if ( version_compare( $geodirectory_version, '2.2.7', '<' ) && ! geodir_get_option( 'fast_ajax' ) ) {
					geodir_update_option( 'fast_ajax', '' );
				}
			}
		}
	}

	/**
	 * Check and copy mu-plugins files.
	 *
	 * @since 2.2.7
	 **/
	public static function check_mu_plugins() {
		// Fast AJAX file check.
		if ( geodir_get_option( 'fast_ajax' ) ) {
			geodir_check_fast_ajax_file( true );
		}
	}
}

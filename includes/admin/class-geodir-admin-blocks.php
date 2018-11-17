<?php
/**
 * GeoDirectory Admin
 *
 * @class    GeoDir_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Admin_Blocks class.
 *
 * Adds blocks for all GD shortcodes.
 */
class GeoDir_Admin_Blocks {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

    /**
     * Includes files.
     *
     * @since 2.0.0
     * @access private
     */
	private function includes() {

		//require_once( plugin_dir_path( __FILE__ ) . 'inc/block-types.php' );
		require_once( dirname( __FILE__ ) . '/block-types.php' );
	}

	/**
	 * Sets up main plugin actions and filters.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	private function setup_actions() {

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );
	}

    /**
     * Enqueue scripts and styles.
     *
     * @since 2.0.0
     */
	public function enqueue() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';


		wp_enqueue_script(
			'gb-tests-gutenberg',
			geodir_plugin_url() . '/assets/js/blocks'.$suffix.'.js',
			array( 'wp-blocks', 'wp-element' ),
			GEODIRECTORY_VERSION
		);

		wp_enqueue_style(
			'gb-tests-gutenberg',
			geodir_plugin_url() . '/assets/css/block_editor.css',
			array( 'wp-edit-blocks','font-awesome' ),
			GEODIRECTORY_VERSION
		);
	}
}
// init the class.
//GeoDir_Admin_Blocks::get_instance();
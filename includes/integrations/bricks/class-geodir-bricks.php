<?php
/**
 * GeoDirectory Bricks
 *
 * Adds compatibility for Bricks builder.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.3.33
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Bricks {

	/**
	 * Init.
	 *
	 * @since 2.3.33
	 */
	public static function init() {
		// Global
		add_action( 'init', array( __CLASS__, 'init_hooks' ), 10 );
		add_action( 'get_header', array( __CLASS__, 'set_post_id' ), 9 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'set_wp_enqueue_scripts' ), 9 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'unset_wp_enqueue_scripts' ), 12 );

		// GD
		add_filter( 'geodir_overwrite_archive_template_content', array( __CLASS__, 'overwrite_archive_template_content' ), 10, 3 );
		add_filter( 'geodir_bypass_archive_item_template_content', array( __CLASS__, 'overwrite_archive_item_template_content' ), 10, 3 );

		// Bricks
		add_filter( 'bricks/builder/i18n', array( __CLASS__, 'builder_i18n' ), 10, 1 );
		add_filter( 'bricks/dynamic_data/register_providers', array( __CLASS__, 'register_provider' ), 10, 1 );
		add_filter( 'bricks/setup/control_options', array( __CLASS__, 'add_template_types' ), 10, 1 );
		add_filter( 'bricks/database/content_type', array( __CLASS__, 'set_content_type' ), 10, 2 );
		add_filter( 'bricks/builder/data_post_id', array( __CLASS__, 'maybe_set_post_id' ), 11, 1 );
		add_filter( 'bricks/active_templates', array( __CLASS__, 'set_active_templates' ), 11, 3 );

		// Bricks Elements
		add_filter( 'bricks/builder/elements', array( __CLASS__, 'setup_elements' ), 10, 1 );
		add_action( 'init', array( __CLASS__, 'register_elements' ), 11 );
	}

	public static function init_hooks() {
		if ( ! class_exists( 'Bricks\Integrations\Dynamic_Data\Providers\Provider_Geodir' ) ) {
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/provider-geodir.php' );
		}
	}

	public static function set_post_id() {
		global $post;

		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) && ! empty( $post ) && geodir_is_gd_post_type( $post->post_type ) ) {
			$page_id = (int) GeoDir_Compatibility::gd_page_id();

			if ( ! empty( $page_id ) && Bricks\Helpers::render_with_bricks( $page_id ) ) {
				$post = get_post( $page_id );
			}
		}
	}

	public static function set_wp_enqueue_scripts() {
		global $post, $geodir_bricks_enqueue_post, $geodir_enqueue_set;

		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) && ! empty( $post ) && geodir_is_gd_post_type( $post->post_type ) ) {
			$page_id = (int) GeoDir_Compatibility::gd_page_id();

			if ( ! empty( $page_id ) && Bricks\Helpers::render_with_bricks( $page_id ) ) {
				$geodir_bricks_enqueue_post = $post;

				$post = get_post( $page_id );
				$geodir_enqueue_set = true;
			}
		}
	}

	public static function unset_wp_enqueue_scripts() {
		global $post, $geodir_bricks_enqueue_post, $geodir_enqueue_set;

		if ( ! empty( $geodir_enqueue_set ) && ! empty( $geodir_bricks_enqueue_post ) ) {
			$post = $geodir_bricks_enqueue_post;
			$geodir_enqueue_set = false;
		}
	}

	public static function register_provider( $providers ) {
		$providers[] = 'geodir';

		return $providers;
	}

	/**
	 * Add template types to control options
	 *
	 * @param array $control_options
	 * @return array
	 *
	 * @since 2.3.33
	 */
	public static function add_template_types( $control_options ) {
		$template_types = $control_options['templateTypes'];

		$template_types['gd_single'] = 'GD - ' . esc_html__( 'Single', 'geodirectory' );
		$template_types['gd_archive'] = 'GD - ' . esc_html__( 'Archive', 'geodirectory' );
		//$template_types['gd_archive_item'] = 'GD - ' . esc_html__( 'Archive Item', 'geodirectory' );
		$template_types['gd_search'] = 'GD - ' . esc_html__( 'Search Results', 'geodirectory' );

		$control_options['templateTypes'] = $template_types;

		return $control_options;
	}

	/**
	 * Before Bricks searchs for the right template, set the content_type if needed.
	 *
	 * @param string $content_type
	 * @param int    $post_id
	 */
	public static function set_content_type( $content_type, $post_id ) {
		if ( $content_type != 'content' && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) || geodir_is_page( 'single' ) ) ) {
			$page_id = (int) GeoDir_Compatibility::gd_page_id();

			if ( ! empty( $page_id ) && Bricks\Helpers::render_with_bricks( $page_id ) ) {
				$content_type = 'content';
			}
		}

		return $content_type;
	}

	/**
	 * Get template data by template type.
	 *
	 * For GD templates inside Bricks theme. Return template data rendered via Bricks template shortcode.
	 *
	 * @since 2.3.33
	 */
	public static function get_template_data_by_type( $type = '', $render = true ) {
		$template_ids = Bricks\Templates::get_templates_by_type( $type );

		// No template found
		if ( empty( $template_ids[0] ) ) {
			return false;
		}

		// Return template id if render is false
		if ( ! $render ) {
			return $template_ids[0];
		}

		return do_shortcode( '[bricks_template id="' . $template_ids[0] . '"]' );
	}

	/**
	 * Page marked as archive.
	 *
	 * In builder or when setting the active templates we need to replace the active post id by the page id
	 *
	 * @param int $post_id
	 */
	public static function maybe_set_post_id( $post_id ) {
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
			if ( ( ( ! empty( $post_id ) && ! Bricks\Helpers::is_bricks_template( $post_id ) ) || empty( $post_id ) ) && ( $page_id = (int) GeoDir_Compatibility::gd_page_id() ) ) {
				if ( ! empty( $page_id ) && Bricks\Helpers::render_with_bricks( $page_id ) ) {
					$post_id = $page_id;
				}
			}
		}

		return $post_id;
	}

	/**
	 * Overwrite archive template content for the Bricks builder page.
	 *
	 * @since 2.3.33
	 *
	 * @param string $content          Overwrite content. Default empty.
	 * @param string $original_content Archive template content.
	 * @param string $page_id          Archive template ID.
	 * @return string Filtered content.
	 */
	public static function overwrite_archive_template_content( $content, $original_content, $page_id ) {
		$bricks_data = Bricks\Helpers::get_bricks_data( $page_id, 'content' );

		if ( ! empty( $bricks_data ) ) {
			remove_filter( 'geodir_overwrite_archive_template_content', array( __CLASS__, 'overwrite_archive_template_content' ), 10, 3 );

			ob_start();
			Bricks\Frontend::render_content( $bricks_data );
			$_content = ob_get_clean();

			$content = trim( $_content );

			add_filter( 'geodir_overwrite_archive_template_content', array( __CLASS__, 'overwrite_archive_template_content' ), 10, 3 );
		}

		return $content;
	}

	/**
	 * Overwrite archive item template content for the Bricks builder page.
	 *
	 * @since 2.3.33
	 *
	 * @param string $content          Overwrite content. Default empty.
	 * @param string $original_content Archive template content.
	 * @param string $page_id          Archive template ID.
	 * @return string Filtered content.
	 */
	public static function overwrite_archive_item_template_content( $content, $original_content, $page_id ) {
		global $gd_post;

		$post_type = ! empty( $gd_post->post_type ) ? '_' . $gd_post->post_type : '';

		$bricks_data = Bricks\Helpers::get_bricks_data( $page_id, 'gd_archive_item' . $post_type );

		if ( ! empty( $bricks_data ) ) {
			remove_filter( 'geodir_bypass_archive_item_template_content', array( __CLASS__, 'overwrite_archive_item_template_content' ), 10, 3 );

			ob_start();
			Bricks\Frontend::render_content( $bricks_data );
			$_content = ob_get_clean();

			$content = trim( $_content );

			add_filter( 'geodir_bypass_archive_item_template_content', array( __CLASS__, 'overwrite_archive_item_template_content' ), 10, 3 );
		}

		return $content;
	}

	public static function set_active_templates( $active_templates, $post_id, $content_type ) {
		$post_types = geodir_get_posttypes();

		$active_templates['gd_archive_item'] = geodir_archive_item_page_id();

		foreach ( $post_types as $post_type ) {
			$active_templates['gd_archive_item_' . $post_type ] = geodir_archive_item_page_id( $post_type );
		}

		return $active_templates;
	}

	public static function setup_elements( $elements ) {
		$elements[] = 'geodir-image-gallery';

		return $elements;
	}

	public static function register_elements() {
		$element_files = array(
			GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-image-gallery.php'
		);

		foreach ( $element_files as $file ) {
			\Bricks\Elements::register_element( $file );
		}
	}

	public static function is_bricks_preview() {
		$result = false;

		if ( function_exists( 'bricks_is_builder' ) && ( bricks_is_builder() || bricks_is_builder_call() ) ) {
			$result = true;
		}

		return $result;
	}

	public static function builder_i18n( $i18n ) {
		$i18n['geodirectory'] = esc_html__( 'GeoDirectory', 'geodirectory' );

		return $i18n;
	}
}

GeoDir_Bricks::init();
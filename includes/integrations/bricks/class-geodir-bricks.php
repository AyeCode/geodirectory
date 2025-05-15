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
		add_filter( 'bricks/dynamic_data/register_providers', array( __CLASS__, 'register_provider' ), 10, 1 );
		add_filter( 'bricks/database/content_type', array( __CLASS__, 'set_content_type' ), 10, 2 );
		add_filter( 'bricks/builder/data_post_id', array( __CLASS__, 'maybe_set_post_id' ), 11, 1 );
		add_filter( 'bricks/active_templates', array( __CLASS__, 'set_active_templates' ), 11, 3 );

		// Bricks Elements
		add_filter( 'bricks/builder/elements', array( __CLASS__, 'setup_elements' ), 10, 1 );
		add_action( 'init', array( __CLASS__, 'register_elements' ), 11 );


		// Filters to make images work
		add_filter( 'get_post_metadata', array( __CLASS__, '_wp_attachment_metadata'), 10, 5 );
		add_filter( 'wp_get_attachment_image', array( __CLASS__, '_wp_get_attachment_image' ), 10, 5 );
		add_filter( 'wp_get_attachment_image_attributes',  array( __CLASS__, '_bricks_set_image_attributes'), 10, 3 );
		add_action( 'after_setup_theme',array( __CLASS__, 'remove_bricks_image_attributes_filter'), 10 );
		add_filter( 'wp_get_attachment_image_src', array( __CLASS__, 'maybe_gd_image_id'), 10, 4 );

		// Remote templates @todo i dont think this will work till v2
		//add_filter( 'bricks/remote_templates/sources', array( __CLASS__, 'remote_templates') );

	}

	/**
	 * Add our remote template sources.
	 *
	 * @param $sources
	 *
	 * @return mixed
	 */
	public static function remote_templates( $sources ) {

		// Cracks Directory Site
		$sources[] = [
			'name' => 'GeoDirectory (Cracka Template)',
			'url'  => 'https://n7dcyg6k.mygeodirectory.com/',
		];

		return $sources;
	}

	/**
	 * Check if the attachment id is prefixed with our marker and if so check our tables for the image src.
	 *
	 * @param $image
	 * @param $attachment_id
	 * @param $size
	 * @param $icon
	 *
	 * @return array|mixed
	 */
	public static function maybe_gd_image_id($image, $attachment_id, $size, $icon){
		global $gd_last_attachment_id;

		// a unique GD prefix (GeoDir in ASCII)
		$geodir_ascii = 7110111168105114;
		if (strpos($attachment_id, $geodir_ascii) === 0 ) {

			$gd_attachment_id_parts = explode( $geodir_ascii, $attachment_id );
			$gd_attachment_id = end( $gd_attachment_id_parts );

			$gd_attachment = GeoDir_Media::get_attachment_by_id( $gd_attachment_id );

			if ( ! empty( $gd_attachment ) ) {
				$gd_last_attachment_id = absint( $gd_attachment_id );
				$meta = isset( $gd_attachment->metadata ) ? maybe_unserialize( $gd_attachment->metadata ) : array();
				$image_src =  geodir_get_image_src( $gd_attachment,$size );
				$img_width = isset($meta['sizes'][$size]['width']) ? absint($meta['sizes'][$size]['width']) : (isset($meta['width']) ? absint($meta['width']) : 0);
				$img_height = isset($meta['sizes'][$size]['height']) ? absint($meta['sizes'][$size]['height']) : (isset($meta['height']) ? absint($meta['height']) : 0);

				if ( $image_src ) {
					$image = [
						$image_src, // image src
						$img_width,
						$img_height,
						$icon

					];
				}
			}
		}

		return $image;
	}

	/**
	 * Remove the standard Bricks filter as it has no check for attachment ID being NULL.
	 * We set this back in the next function with our check in place.
	 *
	 */
	public static function remove_bricks_image_attributes_filter() {
		// Get the Theme singleton instance
		$theme = \Bricks\Theme::instance();

		// Ensure the Theme instance and frontend property exist
		if ( $theme && isset( $theme->frontend ) ) {
			// Remove the filter
			remove_filter( 'wp_get_attachment_image_attributes', [ $theme->frontend, 'set_image_attributes' ], 10, 3 );
		}
	}

	/**
	 * Add a filter to the image attributes to add back the bricks filter action OR add our own.
	 *
	 * @param $attr
	 * @param $attachment
	 * @param $size
	 *
	 * @return mixed
	 */
	public static function _bricks_set_image_attributes($attr, $attachment, $size){

		if ( !is_admin() ) {
			if ( is_null( $attachment ) ) {
				$theme = \Bricks\Theme::instance();
				if ( empty( $theme ) ) {
					$attr = $theme->frontend->set_image_attributes( $attr, $attachment, array( 512, 512 ) );
				}
				$attr['data-type'] = 'string';
			}else{
				$theme = \Bricks\Theme::instance();
				if ( empty( $theme ) ) {
					$attr = $theme->frontend->set_image_attributes($attr, $attachment, $size);
				}
			}
		}

		return $attr;
	}

	/**
	 * We check if an attachment id is prefixed with our long marker number and if so we get the data from our table.
	 *
	 * @param $html
	 * @param $attachment_id
	 * @param $size
	 * @param $icon
	 * @param $attr
	 *
	 * @return mixed|string
	 */
	public static function _wp_get_attachment_image( $html, $attachment_id, $size, $icon, $attr ) {

		// a unique GD prefix (GeoDir in ASCII)
		$geodir_ascii = 7110111168105114;
		if ( strpos( $attachment_id, $geodir_ascii ) === 0 ) {

			$gd_attachment_id_parts = explode( $geodir_ascii, $attachment_id );
			$gd_attachment_id       = end( $gd_attachment_id_parts );
			$gd_attachment          = GeoDir_Media::get_attachment_by_id( $gd_attachment_id );
			$class                  = ! empty( $attr['class'] ) ? esc_attr( $attr['class'] ) : '';
			$html                   = geodir_get_image_tag( $gd_attachment, $size, '', $class );


			$meta = isset( $gd_attachment->metadata ) ? maybe_unserialize( $gd_attachment->metadata ) : '';

			// Only set different sizes if not thumbnail
			if ( $size != 'thumbnail' && ! empty( $meta ) ) {
				$html = wp_image_add_srcset_and_sizes( $html, $meta, 0 );
			}

		}

		return $html;
	}

	/**
	 * We check if an attachment id is prefixed with our long marker number and if so we get the meta data from our table.
	 *
	 * @param $output
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 * @param $meta_type
	 *
	 * @return array|array[]|mixed|string|string[]
	 */
	public static function _wp_attachment_metadata( $output, $object_id, $meta_key, $single, $meta_type ) {

		// a unique GD prefix (GeoDir in ASCII)
		$geodir_ascii = 7110111168105114;
		if ( '_wp_attachment_metadata' === $meta_key && strpos( $object_id, $geodir_ascii ) === 0 ) {


			$gd_attachment_id_parts = explode( $geodir_ascii, $object_id );
			$gd_attachment_id       = end( $gd_attachment_id_parts );
			$gd_attachment          = GeoDir_Media::get_attachment_by_id( $gd_attachment_id );

			$meta = isset( $gd_attachment->metadata ) ? maybe_unserialize( $gd_attachment->metadata ) : '';
			if ( ! empty( $meta ) ) {
				$output = $single ? [ $meta ] : $meta;
			} elseif ( ! empty( $gd_attachment->file ) ) {

				// if its an external image we still need to return some meta or it will fail the bricks checks
				$meta   = [
					'file' => $gd_attachment->file,
				];
				$output = $single ? [ $meta ] : $meta;

			}

		}

		return $output;
	}

	public static function init_hooks() {
		if ( ! class_exists( 'Bricks\Integrations\Dynamic_Data\Providers\Provider_Geodir' ) ) {
			require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/provider-geodir.php' );
		}

		// Bricks
		add_filter( 'bricks/builder/i18n', array( __CLASS__, 'builder_i18n' ), 10, 1 );
		add_filter( 'bricks/setup/control_options', array( __CLASS__, 'add_template_types' ), 10, 1 );

		// Bricks loads some init hooks with priority > 10000.
		if ( defined( 'GEODIR_FAST_AJAX' ) && ! empty( $_REQUEST['gd-ajax'] ) && ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_ajax_search' && empty( $_REQUEST['gd-no-auth'] ) ) {
			if ( $priority = has_action( 'init', array( 'GeoDir_Fast_AJAX', 'do_gd_ajax' ) ) ) {
				remove_action( 'init', array( 'GeoDir_Fast_AJAX', 'do_gd_ajax' ), $priority );

				add_action( 'init', array( 'GeoDir_Fast_AJAX', 'do_gd_ajax' ), 99999 );
			}
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
		global $gd_post, $geodir_brocks_css;

		$post_type = ! empty( $gd_post->post_type ) ? '_' . $gd_post->post_type : '';

		// Set active templates on AJAX call.
		if ( wp_doing_ajax() && Bricks\Helpers::render_with_bricks( $page_id ) ) {
			Bricks\Database::set_active_templates( $page_id );
		}

		$bricks_data = Bricks\Helpers::get_bricks_data( $page_id, 'gd_archive_item' . $post_type );

		if ( ! empty( $bricks_data ) ) {
			remove_filter( 'geodir_bypass_archive_item_template_content', array( __CLASS__, 'overwrite_archive_item_template_content' ), 10, 3 );

			if ( empty( $geodir_brocks_css ) ) {
				$geodir_brocks_css = array();
			}

			ob_start();
			Bricks\Frontend::render_content( $bricks_data );
			$_content = ob_get_clean();

			$content = $_content ? trim( $_content ) : '';

			// Render inline CSS.
			if ( $content && empty( $geodir_brocks_css[ $page_id ] ) ) {
				$additional_data = Bricks\Element_Template::get_builder_call_additional_data( $page_id );

				if ( ! empty( $additional_data['css'] ) ) {
					$content .= "<style type=\"text/css\" data-template-id=\"" . (int) $page_id . "\">" . trim( Bricks\Assets::minify_css( $additional_data['css'] ) ) . "</style>";
				}

				$geodir_brocks_css[ $page_id ] = true;
			}

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
			GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-image-gallery.php',
		);

		if (class_exists('Super_Duper_Bricks_Element')) {
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-add-listing.php';
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-search.php';
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-map.php';
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-post-fav.php';
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-post-rating.php';
			$element_files[] = GEODIRECTORY_PLUGIN_DIR . 'includes/integrations/bricks/element-recent-reviews.php';
		}

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

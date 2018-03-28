<?php
/**
 * Handle frontend scripts
 *
 * @class       GeoDir_Frontend_Scripts
 * @version     2.0.0
 * @package     GeoDirectory
 * @category    Class
 * @author      GeoDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Frontend_Scripts Class.
 */
class GeoDir_Frontend_Scripts {

	/**
	 * Contains an array of script handles registered by GeoDir.
	 * @var array
	 */
	private static $scripts = array();

	/**
	 * Contains an array of script handles registered by GeoDir.
	 * @var array
	 */
	private static $styles = array();

	/**
	 * Contains an array of script handles localized by GeoDir.
	 * @var array
	 */
	private static $wp_localize_scripts = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ) );
		add_action( 'wp_print_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
		add_action( 'wp_print_footer_scripts', array( __CLASS__, 'localize_printed_scripts' ), 5 );
	}

	/**
	 * Get styles for the frontend.
	 *
	 * @return array
	 */
	public static function get_styles() {
		return apply_filters( 'geodir_enqueue_styles', array(
			'geodirectory-smallscreen' => array(
				'src'     => self::get_asset_url( 'assets/css/geodirectory-smallscreen.css' ),
				'deps'    => 'geodirectory-media-queries',
				'version' => GEODIRECTORY_VERSION,
				'media'   => 'only screen and (max-width: ' . apply_filters( 'geodir_style_smallscreen_breakpoint', $breakpoint = '768px' ) . ')',
				'has_rtl' => true,
			),
			'geodirectory-general' => array(
				'src'     => self::get_asset_url( 'assets/css/geodirectory.css' ),
				'deps'    => '',
				'version' => GEODIRECTORY_VERSION,
				'media'   => 'all',
				'has_rtl' => true,
			),
		) );
	}

	/**
	 * Return protocol relative asset URL.
	 * @param string $path
	 */
	private static function get_asset_url( $path ) {
		return str_replace( array( 'http:', 'https:' ), '', plugins_url( $path, geodir_plugin_url() ) );
	}

	/**
	 * Register a script for use.
	 *
	 * @uses   wp_register_script()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = GEODIRECTORY_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
	 *
	 * @uses   wp_enqueue_script()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  boolean  $in_footer
	 */
	private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = GEODIRECTORY_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
	 *
	 * @uses   wp_register_style()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 * @param  boolean  $has_rtl
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = GEODIRECTORY_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
	 *
	 * @uses   wp_enqueue_style()
	 * @access private
	 * @param  string   $handle
	 * @param  string   $path
	 * @param  string[] $deps
	 * @param  string   $version
	 * @param  string   $media
	 * @param  boolean  $has_rtl
	 */
	private static function enqueue_style( $handle, $path = '', $deps = array(), $version = GEODIRECTORY_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register all GeoDir scripts.
	 */
	private static function register_scripts() {

		$map_lang = "&language=" . geodir_get_map_default_language();
		$map_key = geodir_get_map_api_key(true);
		/** This filter is documented in geodirectory_template_tags.php */
		$map_extra = apply_filters('geodir_googlemap_script_extra', '');

		$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$register_scripts = array(
			'select2' => array(
				'src'     => geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
				'version' => '4.0.4',
			),
			'geodir-select2' => array(
				'src'     => geodir_plugin_url() . '/assets/js/geodir-select2' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-jquery-ui-timepicker' => array(
				'src'     => geodir_plugin_url() . '/assets/js/jquery.ui.timepicker' . $suffix . '.js',
				'deps'    => array('jquery-ui-datepicker', 'jquery-ui-slider'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-google-maps' => array(
				'src'     => 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra,
				'deps'    => array(),
				'version' => '',
			),
			'geodir-g-overlappingmarker' => array(
				'src'     => geodir_plugin_url() . '/assets/jawj/oms' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-leaflet' => array(
				'src'     => geodir_plugin_url() . '/assets/leaflet/leaflet' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'photoswipe' => array(
				'src'     => self::get_asset_url( 'assets/js/photoswipe/photoswipe' . $suffix . '.js' ), // @todo we have not added this yet but lets :)
				'deps'    => array(),
				'version' => '4.1.1',
			),
			'photoswipe-ui-default'  => array(
				'src'     => self::get_asset_url( 'assets/js/photoswipe/photoswipe-ui-default' . $suffix . '.js' ),// @todo we have not added this yet but lets :)
				'deps'    => array( 'photoswipe' ),
				'version' => '4.1.1',
			),
			'leaflet-routing-machine' => array(
				'src'     =>  geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine' . $suffix . '.js',
				'deps'    => array('geodir-leaflet'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-leaflet-geo' => array(
				'src'     => geodir_plugin_url() . '/assets/leaflet/osm.geocode' . $suffix . '.js',
				'deps'    => array('geodir-leaflet'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-o-overlappingmarker' => array(
				'src'     =>  geodir_plugin_url() . '/assets/jawj/oms-leaflet' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-goMap' => array(
				'src'     => geodir_plugin_url() . '/assets/js/goMap' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-plupload' => array(
				'src'     => geodir_plugin_url() . '/assets/js/geodirectory-plupload' . $suffix . '.js',
				'deps'    => array('plupload','jquery','jquery-ui-sortable'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodirectory' => array(
				'src'     =>  geodir_plugin_url() . '/assets/js/geodirectory' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'jquery-flexslider' => array(
				'src'     => geodir_plugin_url() . '/assets/js/jquery.flexslider' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-post' => array(
				'src'     => geodir_plugin_url() . '/assets/js/post' . $suffix . '.js#asyncload',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-jquery-lightbox' => array(
				'src'     => geodir_plugin_url() . '/assets/js/jquery.lightbox-0.5' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-add-listing' => array(
				'src'     => geodir_plugin_url() . '/assets/js/add-listing' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
			'lity' => array(
				'src'     => geodir_plugin_url() . '/assets/js/libraries/lity' . $suffix . '.js',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
			),
//			'' => array(
//				'src'     => ,
//				'deps'    => array(),
//				'version' => GEODIRECTORY_VERSION,
//			),
//			'' => array(
//				'src'     => ,
//				'deps'    => array(),
//				'version' => GEODIRECTORY_VERSION,
//			),
		);
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register all GeoDir styles.
	 */
	private static function register_styles() {
		$register_styles = array(
			'photoswipe' => array(
				'src'     => self::get_asset_url( 'assets/css/photoswipe/photoswipe.css' ),// @todo we have not added this yet but lets :)
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'photoswipe-default-skin' => array(
				'src'     => self::get_asset_url( 'assets/css/photoswipe/default-skin/default-skin.css' ),// @todo we have not added this yet but lets :)
				'deps'    => array( 'photoswipe' ),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'select2' => array(
				'src'     =>  geodir_plugin_url() . '/assets/css/select2/select2.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'geodir-core' => array(
				'src'     => geodir_plugin_url() . '/assets/css/gd_core_frontend.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'font-awesome' => array(
				'src'     => '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'geodir-rtl' => array(
				'src'     => geodir_plugin_url() . '/assets/css/rtl-frontend.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => true,
			),
			'leaflet' => array(
				'src'     => geodir_plugin_url() . '/assets/leaflet/leaflet.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
			'leaflet-routing-machine' => array(
				'src'     => geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine.css',
				'deps'    => array(),
				'version' => GEODIRECTORY_VERSION,
				'has_rtl' => false,
			),
//			'' => array(
//				'src'     => ,
//				'deps'    => array(),
//				'version' => GEODIRECTORY_VERSION,
//				'has_rtl' => false,
//			),
//			'' => array(
//				'src'     => ,
//				'deps'    => array(),
//				'version' => GEODIRECTORY_VERSION,
//				'has_rtl' => false,
//			),
//			'' => array(
//				'src'     => ,
//				'deps'    => array(),
//				'version' => GEODIRECTORY_VERSION,
//				'has_rtl' => false,
//			),

		);
		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	/**
	 * Register/queue frontend scripts.
	 */
	public static function load_scripts() {
		global $post;

//		if ( ! did_action( 'before_geodir_init' ) ) {
//			return;
//		}

		// register scripts/styles
		self::register_scripts();
		self::register_styles();

		// geodir_params
		wp_localize_script('geodirectory', 'geodir_params', geodir_params()); //@todo we need to do this the nice way




		// for add listing page only
		self::enqueue_script( 'geodir-plupload' );
		self::enqueue_script( 'geodir-add-listing' );


		// detals page
		



		self::enqueue_style( 'select2' );
		self::enqueue_script( 'select2' );
		self::enqueue_script( 'geodir-select2' );
		
		self::enqueue_script( 'geodir-jquery-ui-timepicker' );

		self::enqueue_style( 'geodir-core' );
		self::enqueue_style( 'font-awesome' );
		if(is_rtl()){
			self::enqueue_style( 'geodir-rtl' );
		}


		self::enqueue_script( 'geodirectory' );
		self::enqueue_script( 'lity' );
		self::enqueue_script( 'jquery-flexslider' );

		// Details only?
		self::enqueue_script( 'geodir-post' );
		//self::enqueue_script( 'geodir-jquery-lightbox' );


		// Map stuff
		// add maps if needed
		$geodir_map_name = geodir_map_name();
		if (in_array($geodir_map_name, array('auto', 'google'))) {
			self::enqueue_script('geodir-google-maps');
			self::enqueue_script('geodir-g-overlappingmarker');
		}elseif($geodir_map_name == 'osm'){
			self::enqueue_style('leaflet');
			self::enqueue_style('leaflet-routing-machine');

			self::enqueue_script('geodir-leaflet');
			self::enqueue_script('geodir-leaflet-geo');
			self::enqueue_script('leaflet-routing-machine');
			self::enqueue_script('geodir-o-overlappingmarker');
		}
		wp_add_inline_script( 'geodir-goMap', "window.gdSetMap = window.gdSetMap || '".geodir_map_name()."';", 'before' );
		wp_enqueue_script( 'geodir-goMap' );

		
	}

	/**
	 * Localize a GeoDir script once.
	 * @access private
	 * @since  2.3.0 this needs less wp_script_is() calls due to https://core.trac.wordpress.org/ticket/28404 being added in WP 4.0.
	 * @param  string $handle
	 */
	private static function localize_script( $handle ) {
		if ( ! in_array( $handle, self::$wp_localize_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_data( $handle ) ) ) {
			$name                        = str_replace( '-', '_', $handle ) . '_params';
			self::$wp_localize_scripts[] = $handle;
			wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
		}
	}

	/**
	 * Return data for script handles.
	 * @access private
	 * @param  string $handle
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {
		global $wp;

		switch ( $handle ) {
			case 'geodirectory' :
				/**
				 * Filter the `geodir_var` data array that outputs the  wp_localize_script() translations and variables.
				 *
				 * This is used by addons to add JS translatable variables.
				 *
				 * @since 1.4.4
				 * @param array $geodir_vars_data {
				 *    geodir var data used by addons to add JS translatable variables.
				 *
				 *    @type string $siteurl Site url.
				 *    @type string $geodir_plugin_url Geodirectory core plugin url.
				 *    @type string $geodir_ajax_url Geodirectory plugin ajax url.
				 *    @type int $geodir_gd_modal Disable GD modal that displays slideshow images in popup?.
				 *    @type int $is_rtl Checks if current locale is RTL.
				 *
				 * }
				 */
				return apply_filters('geodir_vars_data',
					array(
						'siteurl' => get_option('siteurl'),
						'plugin_url' => geodir_plugin_url(),
						'lazy_load' => geodir_get_option('geodir_lazy_load',1),
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'gd_modal' => (int)geodir_get_option('geodir_disable_gb_modal'),
						'is_rtl' => is_rtl() ? 1 : 0, // fix rtl issue
						'basic_nonce' => wp_create_nonce( 'geodir_basic_nonce'),// fix rtl issue
						'text_add_fav'      => apply_filters('geodir_add_favourite_text', ADD_FAVOURITE_TEXT),
						'text_fav'          => apply_filters('geodir_favourite_text', FAVOURITE_TEXT),
						'text_remove_fav'   => apply_filters('geodir_remove_favourite_text', REMOVE_FAVOURITE_TEXT),
						'text_unfav'        => apply_filters('geodir_unfavourite_text', UNFAVOURITE_TEXT),
						'icon_fav'          => apply_filters('geodir_favourite_icon', 'fa fa-heart'),
						'icon_unfav'        => apply_filters('geodir_unfavourite_icon', 'fa fa-heart'),
					)
				);
			break;
			case 'geodir-select2' :
				return array(
					//'countries'                 => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'geodirectory' ),
					'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'geodirectory' ),
					'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_short_n'    => _x( 'Please enter %qty% or more characters', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_long_n'     => _x( 'Please delete %qty% characters', 'enhanced select', 'geodirectory' ),
					'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'geodirectory' ),
					'i18n_selection_too_long_n' => _x( 'You can only select %qty% items', 'enhanced select', 'geodirectory' ),
					'i18n_load_more'            => _x( 'Loading more results&hellip;', 'enhanced select', 'geodirectory' ),
					'i18n_searching'            => _x( 'Searching&hellip;', 'enhanced select', 'geodirectory' ),
				);
			break;
			case 'geodir-plupload' :
				// place js config array for plupload
				$plupload_init = array(
					'runtimes' => 'html5,silverlight,html4',
					'browse_button' => 'plupload-browse-button', // will be adjusted per uploader
					'container' => 'plupload-upload-ui', // will be adjusted per uploader
					//'drop_element' => 'dropbox', // will be adjusted per uploader
					'file_data_name' => 'async-upload', // will be adjusted per uploader
					'multiple_queues' => true,
					'max_file_size' => geodir_max_upload_size(),
					'url' => admin_url('admin-ajax.php'),
					'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
					'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
					'filters' => array(array('title' => __('Allowed Files', 'geodirectory'), 'extensions' => '*')),
					'multipart' => true,
					'urlstream_upload' => true,
					'multi_selection' => false, // will be added per uploader
					// additional post data to send to our ajax hook
					'multipart_params' => array(
						'_ajax_nonce' => wp_create_nonce( "geodir_attachment_upload" ), // will be added per uploader
						'action' => 'geodir_post_attachment_upload', // the ajax action name
						'imgid' => 0, // will be added per uploader
						'post_id' => 0 // will be added per uploader
					)
				);
				$thumb_img_arr = array();

				if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
					$thumb_img_arr = geodir_get_images($_REQUEST['pid']);

				$totImg = '';
				$image_limit = '';
				if (!empty($thumb_img_arr)) {
					$totImg = count($thumb_img_arr);
				}
				$base_plupload_config = json_encode($plupload_init);

				return array('base_plupload_config' => $base_plupload_config,
				             'totalImg' => $totImg,
				             'image_limit' => $image_limit,
				             'upload_img_size' => geodir_max_upload_size()
				);
			break;

		}
		return false;
	}

	/**
	 * Localize scripts only when enqueued.
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}

GeoDir_Frontend_Scripts::init();

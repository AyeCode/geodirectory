<?php
/**
 * Load admin assets
 *
 * @author      AyeCode Ltd
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'GeoDir_Admin_Assets', false ) ) :

/**
 * GeoDir_Admin_Assets Class.
 */
class GeoDir_Admin_Assets {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {

		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';
		$geodir_map_name = geodir_map_name();

		// Register admin styles
		wp_register_style('select2', geodir_plugin_url() . '/assets/css/select2/select2.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-admin-css', geodir_plugin_url() . '/assets/css/admin.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-jquery-ui-timepicker-css', geodir_plugin_url() . '/assets/css/jquery.ui.timepicker.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-jquery-ui-css', geodir_plugin_url() . '/assets/css/jquery-ui.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-pluplodar-css', geodir_plugin_url() . '/assets/css/pluploader.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-rating-style', geodir_plugin_url() . '/assets/css/jRating.jquery.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-rtl-style', geodir_plugin_url() . '/assets/css/rtl.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-leaflet-routing-style', geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION);


		// Admin styles for GD pages only
		if ( in_array( $screen_id, gd_get_screen_ids() ) ) {

			// load OSM styles if needed.
			if($geodir_map_name == 'osm'){
				wp_enqueue_style('geodir-leaflet-style');
				if (geodir_is_page('details') || geodir_is_page('preview')) {//@todo this should not be needed in admin
					wp_enqueue_style('geodir-leaflet-routing-style');
				}
			}

			wp_enqueue_style( 'geodir-admin-css' );
			wp_enqueue_style( 'select2' );
			wp_enqueue_style( 'geodir-jquery-ui-timepicker-css' );
			wp_enqueue_style( 'geodir-jquery-ui-css' );
			wp_enqueue_style( 'font-awesome' );
			wp_enqueue_style( 'geodir-pluplodar-css');
			wp_enqueue_style( 'geodir-rtl-style');

		}


	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $wp_query, $post;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$post_type   = isset($_REQUEST['post_type']) && $_REQUEST['post_type'] ? sanitize_text_field($_REQUEST['post_type']) : '';
		$geodir_map_name = geodir_map_name();
		
		// map arguments
		$map_lang = "&language=" . geodir_get_map_default_language();
		$map_key = "&key=" . geodir_get_map_api_key();
		/**
		 * Filter the variables that are added to the end of the google maps script call.
		 *
		 * This i used to change things like google maps language etc.
		 *
		 * @since 1.0.0
		 * @param string $var The string to filter, default is empty string.
		 */
		$map_extra = apply_filters('geodir_googlemap_script_extra', '');


		// Register scripts
		wp_register_script('select2', geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.4' );
		wp_register_script('geodir-custom-fields-script', geodir_plugin_url() . '/assets/js/custom_fields'.$suffix.'.js', array('select2','jquery','jquery-ui-sortable'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-geo-script', geodir_plugin_url() . '/assets/leaflet/osm.geocode'.$suffix.'.js', array('geodir-leaflet-script'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-goMap-script', geodir_plugin_url() . '/assets/js/goMap'.$suffix.'.js', array(), GEODIRECTORY_VERSION,true);
		wp_register_script('geodir-barrating-js', geodir_plugin_url() . '/assets/js/jquery.barrating'.$suffix.'.js', array('jquery'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-jRating-js', geodir_plugin_url() . '/assets/js/jRating.jquery'.$suffix.'.js', array( 'jquery' ), GEODIRECTORY_VERSION);
		wp_register_script('geodir-on-document-load', geodir_plugin_url() . '/assets/js/on_document_load'.$suffix.'.js', array('jquery'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-plupload', geodir_plugin_url() . '/assets/js/geodirectory-plupload'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-admin-script', geodir_plugin_url() . '/assets/js/admin'.$suffix.'.js', array('jquery','jquery-ui-tooltip'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-admin-term-script', geodir_plugin_url() . '/assets/js/admin-term'.$suffix.'.js', array( 'jquery', 'geodir-admin-script' ), GEODIRECTORY_VERSION );
		wp_register_script('geodir-jquery-ui-timepicker-js', geodir_plugin_url() . '/assets/js/jquery.ui.timepicker'.$suffix.'.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), GEODIRECTORY_VERSION );
		wp_register_script('geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra , array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-routing-script', geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine'.$suffix.'.js', array(), GEODIRECTORY_VERSION);


		// Admin scripts for GD pages only
		if ( in_array( $screen_id, gd_get_screen_ids() ) ) {

			// should prob only be loaded on details page
			wp_enqueue_script('geodir-plupload');

			// add maps if needed
			if (in_array($geodir_map_name, array('auto', 'google'))) {
				wp_enqueue_script('geodir-google-maps');
				wp_enqueue_script('geodir-g-overlappingmarker-script');
			}elseif($geodir_map_name == 'osm'){
				wp_enqueue_script('geodir-leaflet-script');
				wp_enqueue_script('geodir-leaflet-geo-script');

					if (geodir_is_page('details') || geodir_is_page('preview')) { //@todo this should not be needed in admin
						wp_enqueue_script('geodir-leaflet-routing-script');
					}
				wp_enqueue_script('geodir-o-overlappingmarker-script');

			}

			wp_add_inline_script( 'geodir-admin-script', "window.gdSetMap = window.gdSetMap || '".geodir_map_name()."';", 'before' );
			wp_enqueue_script( 'geodir-goMap-script' );
			wp_enqueue_script( 'geodir-admin-script' );
			wp_enqueue_script( 'geodir-custom-fields-script' );
			wp_enqueue_script( 'geodir-jRating-js' );
			wp_enqueue_script( 'geodir-on-document-load' );


			// localised constants
			$ajax_cons_data = array(
				'txt_choose_image' => __( 'Choose an image', 'geodirectory' ),
				'txt_use_image' => __( 'Use image', 'geodirectory' ),
				'img_spacer' => admin_url( 'images/media-button-image.gif' )
			);
			wp_localize_script('geodir-admin-script', 'geodir_ajax', $ajax_cons_data);


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
					'imgid' => 0 // will be added per uploader
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
			$gd_plupload_init = array('base_plupload_config' => $base_plupload_config,
			                          'totalImg' => $totImg,
			                          'image_limit' => $image_limit,
			                          'upload_img_size' => geodir_max_upload_size());

			wp_localize_script('geodir-plupload', 'geodir_plupload_params', $gd_plupload_init);

		}

		// Load only on cat/tag pages
		if (strpos($screen_id, 'edit-'.$post_type.'category') === 0 || strpos($screen_id, 'edit-'.$post_type.'_tags') === 0) {
			wp_enqueue_script( 'geodir-admin-term-script' );
		}





	}


}

endif;

return new GeoDir_Admin_Assets();

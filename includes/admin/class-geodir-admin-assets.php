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
		//enqueue_block_assets
//		add_action( 'enqueue_block_assets', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'fix_script_conflicts' ),99 );

		// Localize jQuery Timepicker
		add_action( 'admin_enqueue_scripts', 'geodir_localize_jquery_ui_timepicker', 1001 );
	}

	/**
	 * Fix style/script conflict from third party plugins/themes.
	 */
	public function fix_script_conflicts() {
		// Fix select2 style conflict after Yoast 14.1.
		if ( defined( 'WPSEO_VERSION' ) && wp_script_is( 'geodir-admin-script', 'enqueued' ) && wp_style_is( 'yoast-seo-select2', 'enqueued' ) && wp_style_is( 'yoast-seo-monorepo', 'enqueued' ) ) {
			wp_deregister_style( 'yoast-seo-select2' );

			// Yoast SEO metabox CSS dependent on yoast-seo-select2.
			if ( wp_style_is( 'yoast-seo-metabox-css', 'registered' ) ) {
				wp_register_style( 'yoast-seo-select2', geodir_plugin_url() . '/assets/css/select2/select2.css', array(), GEODIRECTORY_VERSION );
			}
		}
	}

	/**
	 * Enqueue styles.
	 */
	public function admin_styles() {
		$screen         = get_current_screen();
		$screen_id      = $screen ? $screen->id : '';
		$geodir_map_name = GeoDir_Maps::active_map();
		$page = ! empty( $_GET['page'] ) ? $_GET['page'] : '';
		$post_type = ! empty( $_GET['post_type'] ) ? sanitize_text_field( $_GET['post_type'] ) : '';

		// Register admin styles
		if ( wp_style_is( 'select2', 'registered' ) ) {
			wp_deregister_style( 'select2' ); // Fix conflict with select2 basic version loaded via 3rd party plugins.
		}

		wp_register_style('select2', geodir_plugin_url() . '/assets/css/select2/select2.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-admin-css', geodir_plugin_url() . '/assets/css/admin.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-jquery-ui-timepicker-css', geodir_plugin_url() . '/assets/css/jquery.ui.timepicker.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-jquery-ui-css', geodir_plugin_url() . '/assets/css/jquery-ui.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-pluplodar-css', geodir_plugin_url() . '/assets/css/pluploader.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-rtl-style', geodir_plugin_url() . '/assets/css/rtl.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-leaflet-routing-style', geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine.css', array(), GEODIRECTORY_VERSION);
		wp_register_style('geodir-leaflet-style', geodir_plugin_url() . '/assets/leaflet/leaflet.css', array(), GEODIRECTORY_VERSION);

		// load rating scripts on comments & dashboard page.
		if($screen_id == 'comment' || $screen_id == 'edit-comments' || $screen_id == 'dashboard'){
			wp_enqueue_style('geodir-admin-css');
		}

		// Admin styles for GD pages only
		if ( in_array( $screen_id, geodir_get_screen_ids() ) ) {

			// load OSM styles if needed.
			if($geodir_map_name == 'osm'){
				wp_enqueue_style('geodir-leaflet-style');

			}

			wp_enqueue_style( 'geodir-admin-css' );
			if ( ! empty( $_REQUEST['page'] ) && $_REQUEST['page'] != 'gd-settings' && $_REQUEST['page'] != $post_type . '-settings' ) {
				wp_enqueue_style( 'select2' );
			}

			wp_enqueue_style( 'geodir-jquery-ui-timepicker-css' );
			wp_enqueue_style( 'geodir-jquery-ui-css' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'geodir-pluplodar-css');
			wp_enqueue_style( 'geodir-rtl-style');
		}

		if ( $page == 'geodirectory' ) {
			wp_register_style( 'morris', '//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css', array(), '0.5.1' );
			wp_register_style( 'geodir-admin-dashboard', geodir_plugin_url() . '/assets/css/admin-dashboard.css', array(), GEODIRECTORY_VERSION );
			wp_enqueue_style( 'morris' );
			wp_enqueue_style( 'geodir-admin-dashboard' );
		}

	}


	/**
	 * Enqueue scripts.
	 */
	public function admin_scripts() {
		global $wp_query, $post, $pagenow, $aui_conditional_js;

		$screen       = get_current_screen();
		$screen_id    = $screen ? $screen->id : '';
		$gd_screen_id = sanitize_title( __( 'GeoDirectory', 'geodirectory' ) );
		$suffix       = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$post_type   = isset($_REQUEST['post_type']) && $_REQUEST['post_type'] ? sanitize_text_field($_REQUEST['post_type']) : '';
		$page 		  = ! empty( $_GET['page'] ) ? $_GET['page'] : '';
		$geodir_map_name = GeoDir_Maps::active_map();
		$aui = geodir_design_style() ? '/aui' : '';
		// map arguments
		$map_lang = "&language=" . GeoDir_Maps::map_language();
		$map_key = GeoDir_Maps::google_api_key(true);
		/**
		 * Filter the variables that are added to the end of the google maps script call.
		 *
		 * This i used to change things like google maps language etc.
		 *
		 * @since 1.0.0
		 * @param string $var The string to filter, default is empty string.
		 */
		$map_extra = apply_filters('geodir_googlemap_script_extra', '');

		// add maps if needed
		$map_require = array();
		if (in_array($geodir_map_name, array('auto', 'google'))) {
			$map_require = array('geodir-google-maps','geodir-g-overlappingmarker-script');
		}elseif($geodir_map_name == 'osm'){
			$map_require = array('geodir-leaflet-script','geodir-leaflet-geo-script','geodir-o-overlappingmarker-script');
		}

		$custom_fields_deps = array( 'select2', 'jquery', 'jquery-ui-sortable' );
		if ( wp_is_mobile() ) {
			// jQuery UI Touch Punch to enable sorting on touch device.
			$custom_fields_deps[] = 'jquery-touch-punch';
		}

		// Register scripts
		if ( wp_script_is( 'select2', 'registered' ) ) {
			wp_deregister_script( 'select2' ); // Fix conflict with select2 basic version loaded via 3rd party plugins.
		}
		wp_register_script('select2', geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js', array( 'jquery' ), '4.0.5' );
		wp_register_script('geodir-custom-fields-script', geodir_plugin_url() . '/assets/js/custom_fields'.$suffix.'.js', $custom_fields_deps, GEODIRECTORY_VERSION);
		wp_register_script('geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-geo-script', geodir_plugin_url() . '/assets/leaflet/osm.geocode'.$suffix.'.js', array('geodir-leaflet-script'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-o-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms-leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-goMap', geodir_plugin_url() . '/assets/js/goMap'.$suffix.'.js', $map_require , GEODIRECTORY_VERSION,true);
		wp_register_script('geodir-plupload', geodir_plugin_url() . '/assets'.$aui.'/js/geodirectory-plupload'.$suffix.'.js', array('plupload','jquery-ui-datepicker'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-add-listing', geodir_plugin_url() . '/assets'.$aui.'/js/add-listing'.$suffix.'.js', array('jquery'), GEODIRECTORY_VERSION, true );
		wp_register_script('geodir-admin-script', geodir_plugin_url() . '/assets/js/admin'.$suffix.'.js', array('jquery','jquery-ui-tooltip'), GEODIRECTORY_VERSION);
		wp_register_script('geodir-admin-term-script', geodir_plugin_url() . '/assets/js/admin-term'.$suffix.'.js', array( 'jquery', 'geodir-admin-script' ), GEODIRECTORY_VERSION );
		wp_register_script('geodir-jquery-ui-timepicker', geodir_plugin_url() . '/assets/js/jquery.ui.timepicker'.$suffix.'.js', array( 'jquery-ui-datepicker', 'jquery-ui-slider' ), GEODIRECTORY_VERSION );
		wp_register_script('geodir-g-overlappingmarker-script', geodir_plugin_url() . '/assets/jawj/oms'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-google-maps', 'https://maps.google.com/maps/api/js?' . $map_lang . $map_key . $map_extra , array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-script', geodir_plugin_url() . '/assets/leaflet/leaflet'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-leaflet-routing-script', geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-lity', geodir_plugin_url() . '/assets/js/libraries/gd_lity'.$suffix.'.js', array(), GEODIRECTORY_VERSION);
		wp_register_script('geodir-nestable-script', geodir_plugin_url() . '/assets/js/libraries/jquery.nestable'.$suffix.'.js', array(), GEODIRECTORY_VERSION);

		if ( $page == 'geodirectory' ) {
			wp_register_script( 'raphael', '//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js', array('jquery'), '2.1.0' );
			wp_register_script( 'morris', '//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js', array('jquery'), '0.5.1' );
			wp_register_script( 'geodir-admin-dashboard', geodir_plugin_url() . '/assets/js/admin-dashboard'.$suffix.'.js', array('jquery', 'raphael', 'morris'), GEODIRECTORY_VERSION);
			wp_enqueue_script( 'raphael' );
			wp_enqueue_script( 'morris' );
			wp_enqueue_script( 'geodir-admin-dashboard' );
		}


		// load rating scripts on comments pages
		if($screen_id=='comment'){
			wp_enqueue_script( 'geodir-admin-script' );
			// geodir_params
			wp_localize_script('geodir-admin-script', 'geodir_params', geodir_params());
		}

		// Admin scripts for GD pages only
		if ( in_array( $screen_id, geodir_get_screen_ids() ) ) {
			wp_enqueue_script( 'wp-color-picker' );

			// gd lightbox
			wp_enqueue_script( 'geodir-lity' );

			// timepicker
			wp_enqueue_script( 'geodir-jquery-ui-timepicker' );

			// should prob only be loaded on details page
			wp_enqueue_script('geodir-plupload');
			if ( 'edit.php' === $pagenow || 'post.php' === $pagenow || 'post-new.php' == $pagenow ) {
				wp_enqueue_script('geodir-add-listing');

				// Don't load JS again.
				if ( empty( $aui_conditional_js ) && geodir_design_style() && class_exists( 'AyeCode_UI_Settings' ) ) {
					$aui_settings = AyeCode_UI_Settings::instance();

					if ( is_callable( array( $aui_settings, 'conditional_fields_js' ) ) ) {
						$conditional_fields_js = $aui_settings->conditional_fields_js();

						if ( ! empty( $conditional_fields_js ) ) {
							$aui_conditional_js = wp_add_inline_script( 'geodir-add-listing', $conditional_fields_js );
						}
					}
				}
			}

			$load_gomap_script = apply_filters( 'geodir_load_gomap_script', false );
			// only load maps when needed
			if(
			( strpos( $screen_id, '_page_gd-settings' ) > 0 && isset($_REQUEST['section']) && ($_REQUEST['section']=='location' || $_REQUEST['section']=='dummy_data') ) ||
			( isset($screen->base) && $screen->base=='post' && isset($screen->post_type) &&  substr( $screen->post_type, 0, 3 ) === "gd_" ) ||
			$load_gomap_script
			){
				if ( in_array( 'geodir-google-maps', $map_require ) ) {
					wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
				}
				$osm_extra = GeoDir_Maps::footer_script();
				wp_add_inline_script( 'geodir-goMap', "window.gdSetMap = window.gdSetMap || '".GeoDir_Maps::active_map()."';".$osm_extra, 'before' );
				wp_enqueue_script( 'geodir-goMap' );
			}
			wp_enqueue_script( 'geodir-admin-script' );
			wp_enqueue_script( 'geodir-custom-fields-script' );
			wp_enqueue_script( 'geodir-nestable-script' );



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

			// geodir_params
			wp_localize_script('geodir-admin-script', 'geodir_params', geodir_params());


		}

		// Load only on cat/tag pages
		if (strpos($screen_id, 'edit-'.$post_type.'category') === 0 || strpos($screen_id, 'edit-'.$post_type.'_tags') === 0) {
			wp_enqueue_script( 'geodir-admin-term-script' );
		}

		// load only on widgets screen
		if($screen_id == 'widgets' || $screen_id =='customize'){
			wp_add_inline_script( 'admin-widgets', $this->widget_title_js() );
			wp_add_inline_script( 'customize-controls', $this->widget_title_js() );
		}


		// System status.
		if ( $gd_screen_id . '_page_gd-status' === $screen_id ) {
			wp_register_script( 'geodir-admin-system-status', geodir_plugin_url() . '/assets/js/system-status' . $suffix . '.js', array( 'jquery' ), GEODIRECTORY_VERSION );
			wp_enqueue_script( 'geodir-admin-system-status' );
			wp_localize_script(
				'geodir-admin-system-status',
				'geodir_admin_status_js_vars',
				array(
					'delete_log_confirmation' => esc_js( __( 'Are you sure you want to delete this log?', 'geodirectory' ) ),
				)
			);
		}

		// API keys.
		if ( $gd_screen_id . '_page_gd-settings' === $screen_id ) {
			wp_register_script( 'qrcode', geodir_plugin_url() . '/assets/js/jquery.qrcode' . $suffix . '.js', array( 'jquery' ), GEODIRECTORY_VERSION );
			wp_register_script( 'geodir-admin-api-keys', geodir_plugin_url() . '/assets/js/admin-api-keys' . $suffix . '.js', array( 'jquery', 'wp-util', 'qrcode', 'geodir-admin-script' ), GEODIRECTORY_VERSION );
			wp_enqueue_script( 'geodir-admin-api-keys' );
			wp_localize_script(
				'geodir-admin-api-keys',
				'geodir_admin_api_keys_params',
				array(
					'save_api_nonce' => wp_create_nonce( 'save-api-key' ),
					'clipboard_failed' => esc_html__( 'Copying to clipboard failed. Please press Ctrl/Cmd+C to copy.', 'geodirectory' ),
					'clipboard_copied' => esc_html__( 'Copied to clipboard!', 'geodirectory' ),
				)
			);
		}

	}


    /**
     * Add widget title js.
     *
     * @since 2.0.0
     *
     * @return string Widget title script.
     */
	public function widget_title_js(){
		ob_start();
		?>
		<script>
			// add widget titles
			gd_add_post_meta_widget_titles();

			// inint on widget updated
			jQuery(document).on('widget-updated', function(e, widget){
				gd_add_post_meta_widget_titles();
			});
			/*
			 * Add a title to the post meta widget so it is easer to distinguish them.
			 */
			function gd_add_post_meta_widget_titles(){
				setTimeout(function(){
				jQuery( ".widget-inside .id_base" ).each(function( index ) {
					if(jQuery( this ).val() =='gd_post_meta'){
						var $widget_id = jQuery(this).parent().find('.widget-id').val();
						var $key = jQuery('#widget-'+$widget_id+'-key').val();
						if($key){
							jQuery(this).closest('.widget').find('.in-widget-title').text(": "+$key);
						}
					}
				});
				}, 200);
			}

//			function gd_add_post_meta_widget_titles_customizer(){
//				setTimeout(function(){
//					jQuery( ".widget-inside .id_base" ).each(function( index ) {
//						if(jQuery( this ).val() =='gd_post_meta'){
//
//							var $widget_id = jQuery(this).parent().find('.widget-id').val();
//							var $key = jQuery('#widget-'+$widget_id+'-key').val();
//							alert($widget_id );
//							//alert(1+$key);
//							if($key){
//								jQuery(this).closest('.widget').find('.in-widget-title').html(": "+$key);
//							}
//						}
//					});
//				}, 5000);
//			}

			// init cusotmiser widget actions
			/**
			 * We need to run this before jQuery is ready
			 */
//			if (wp.customize) {
//				//alert(1111);
//				wp.customize.bind('ready', function () {
//					gd_add_post_meta_widget_titles_customizer();
//					//alert(2222);
//					// init widgets on load
//					wp.customize.control.each(function (section) {//alert(11112);
//						//gd_add_post_meta_widget_titles();
//					});
//
//					// init widgets on add
//					wp.customize.control.bind('add', function (section) {//alert(11113);
//						//gd_add_post_meta_widget_titles();
//					});
//
//				});
//			}
			</script>
		<?php
		$output = ob_get_clean();

		/*
		 * We only add the <script> tags for code highlighting, so we strip them from the output.
		 */

		return str_replace( array(
			'<script>',
			'</script>'
		), '', $output );
	}


}

endif;

return new GeoDir_Admin_Assets();

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

		// locations scripts
		if(!isset($_REQUEST['et_fb']))
		add_action('wp_footer', array( __CLASS__, 'js_location_functions' )); //@todo this script needs overhalled

		// fix script conflicts, eg flexslider being added twice
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'fix_script_conflicts'), 100 );
		// Localize jQuery Timepicker
		add_action( 'wp_enqueue_scripts', 'geodir_localize_jquery_ui_timepicker', 1001 );

		// allow async tags
		add_filter('clean_url', array( __CLASS__, 'js_async'), 11, 1);

	}


	/**
	 * Adds async tag to javascript for faster page loading.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @param string $url The javascript file url.
	 * @return string The modified javascript string.
	 */
	public static function js_async($url)
	{
		if (strpos($url, '#asyncload')===false)
			return $url;
		else
			return str_replace('#asyncload', '', $url)."' defer async='async";
	}

	/**
	 * Dequeue scripts to fix JS conflicts.
	 *
	 * @since 1.6.22
	 */
	public static function fix_script_conflicts() {
		if ( geodir_is_page( 'single' ) ) {
			// Some themes don't load comment reply JS.
			if ( ! wp_script_is( 'comment-reply', 'enqueued' ) ) {
				wp_enqueue_script( 'comment-reply' );
			}

			if ( wp_script_is( 'flexslider', 'enqueued' ) && wp_script_is( 'jquery-flexslider', 'enqueued' ) ) {
				wp_dequeue_script( 'flexslider' );
			}
		}
	}

	/**
	 * Prints location related javascript.
	 *
	 * @since 1.0.0
	 * @since 1.6.16 Fix: Single quote in default city name causes problem in add new city.
	 * @package GeoDirectory
	 */
	public static function js_location_functions() {
		global $geodirectory;
		$default_search_for_text = geodir_get_option( 'search_default_text' );
		if ( ! $default_search_for_text ) {
			$default_search_for_text = geodir_get_search_default_text();
		}

		$default_near_text = geodir_get_option( 'search_default_near_text' );
		if ( ! $default_near_text ) {
			$default_near_text = geodir_get_search_default_near_text();
		}

		$search_location = ! empty( $geodirectory ) && ! empty( $geodirectory->location ) ? $geodirectory->location->get_default_location() : array();

		$default_search_for_text = addslashes(stripslashes($default_search_for_text));
		$default_near_text = addslashes(stripslashes($default_near_text));
		$near_my_location_text = addslashes( stripslashes( strip_tags( __( 'Near:', 'geodirectory' ) . ' ' . __( 'My Location', 'geodirectory' ) ) ) );
		$city = !empty($search_location) ? addslashes(stripslashes($search_location->city)) : '';

		/**
		 * Google Geocoder restrict country.
		 *
		 * @since 2.2.9
		 *
		 * @params string $restrict_country Country name or country code.
		 */
		$restrict_country = apply_filters( 'geodir_google_geocode_restrict_country', '' );

		$_properties = array();
		if ( ! empty( $restrict_country ) && is_scalar( $restrict_country ) ) {
			$_properties['componentRestrictions'] = array( 'country' => esc_attr( $restrict_country ) );
		}

		if ( ! empty( $_properties ) ) {
			$properties = wp_json_encode( $_properties );
			$properties = substr( $properties, 1, -1 );
		} else {
			$properties = '';
		}

		/**
		 * Google Geocoder properties.
		 *
		 * @since 2.2.9
		 *
		 * @params string $properties The geocoder properties.
		 */
		$properties = apply_filters( 'geodir_google_geocode_properties', $properties );

		$google_geocode_properties = ! empty( $properties ) ? ',' . ltrim( $properties, ',' ) : '';
		?>
		<script type="text/javascript">
			var default_location = '<?php echo $city ;?>';
			var latlng;
			var address;
			var dist = 0;
			var Sgeocoder = (typeof google!=='undefined' && typeof google.maps!=='undefined') ? new google.maps.Geocoder() : {};

			<?php if ( geodir_lazy_load_map() ) { ?>
			jQuery(function($) {
				if ($('.geodir-listing-search input[name="snear"]').length && !window.geodirMapAllScriptsLoaded) {
					$('.geodir-listing-search input[name="snear"]').each(function() {
						if (!window.geodirMapAllScriptsLoaded) {
							$(this).on('focus', function(e){
								/* Load lazy load scripts */
								if (!window.geodirMapAllScriptsLoaded && !window.geodirApiScriptLoading) {
									$(this).geodirLoadMap({loadJS: true});
									jQuery(window).trigger('resize');
								}
							});
						}
					});
				}
			});
			<?php } ?>

			function geodir_setup_submit_search($form) {
				jQuery('.geodir_submit_search').off('click');// unbind any other click events
				jQuery('.geodir_submit_search').on("click",function(e) {
					e.preventDefault();

					var s = ' ';
					var $form = jQuery(this).closest('form');
					<?php if ( geodir_design_style() && geodir_is_page( 'search' ) ) { ?>
					if ($form.data('show') == 'advanced') {
						if (jQuery('form.geodir-search-show-all:visible').length) {
							$form = jQuery('form.geodir-search-show-all');
						} else if (jQuery('form.geodir-search-show-main:visible').length) {
							$form = jQuery('form.geodir-search-show-main');
						} else if (jQuery('[name="geodir_search"]').closest('form:visible').length) {
							$form = jQuery('[name="geodir_search"]').closest('form');
						}
					}

					if (!(geodir_params.hasAjaxSearch && !window.gdAsCptChanged)) {
						if ($form.data('show') == 'main' && jQuery('form.geodir-search-show-advanced:visible').length) {
							var formData = jQuery('form.geodir-search-show-advanced:visible').serializeArray();
							if (formData && typeof formData == 'object' && formData.length) {
								$form.find('.geodir-advanced-data').remove();
								$form.append('<div class="geodir-advanced-data" style="display:none"></div>');
								jQuery.each(formData, function (i, obj){
									jQuery('<input type="hidden">').prop(obj).appendTo(jQuery('.geodir-advanced-data'));
								});
							}
						}
					} else {
						s = ''; /* Keep placeholder for AJAX search. */
					}
					<?php } ?>

					if (jQuery("#sdistance input[type='radio']:checked").length != 0) dist = jQuery("#sdistance input[type='radio']:checked").val();
					if (jQuery('.search_text', $form).val() == '' || jQuery('.search_text', $form).val() == '<?php echo $default_search_for_text;?>') jQuery('.search_text', $form).val(s);

					// Disable location based search for disabled location post type.
					if (jQuery('.search_by_post', $form).val() != '' && typeof gd_cpt_no_location == 'function') {
						if (gd_cpt_no_location(jQuery('.search_by_post', $form).val())) {
							jQuery('.snear', $form).remove();
							jQuery('.sgeo_lat', $form).remove();
							jQuery('.sgeo_lon', $form).remove();
							jQuery('select[name="sort_by"]', $form).remove();
							jQuery($form).trigger("submit");
							return;
						}
					}

					if (
						dist > 0
						|| (jQuery('select[name="sort_by"]').val() == 'nearest'
						|| jQuery('select[name="sort_by"]', $form).val() == 'farthest')
						|| (jQuery(".snear", $form).val() != '' && jQuery(".snear", $form).val() != '<?php echo $default_near_text;?>' && !jQuery('.geodir-location-search-type', $form).val() )
					) {

						var vNear = jQuery(".snear", $form).val();
						/* OSM can't handle post code with no space so we test for it and add one if needed */
						if(window.gdMaps === 'osm'){
							var $near_val = vNear;
							var $is_post_code = $near_val.match("^([A-Za-z][A-Ha-hJ-Yj-y]?[0-9][A-Za-z0-9]??[0-9][A-Za-z]{2}|[Gg][Ii][Rr] ?0[Aa]{2})$");
							if($is_post_code){
								$near_val = $near_val.replace(/.{3}$/,' $&');
								jQuery(".snear", $form).val($near_val);
							}
						}

						geodir_setsearch($form);
					} else {
						jQuery(".snear", $form).val('');
						jQuery($form).trigger("submit");
					}
				});
				// Clear near search GPS for core
				if (!jQuery('input.geodir-location-search-type').length && jQuery('[name="snear"]').length){
					jQuery('[name="snear"]').off('keyup');
					jQuery('[name="snear"]').on('keyup', function($){
						jQuery('.sgeo_lat').val('');
						jQuery('.sgeo_lon').val('');
					});
				}
			}

			jQuery(document).ready(function() {
				geodir_setup_submit_search();
				//setup advanced search form on form ajax load
				jQuery("body").on("geodir_setup_search_form", function($form){
					geodir_setup_submit_search($form);
				});
			});

			function geodir_setsearch($form) {
				if ((dist > 0 || (jQuery('select[name="sort_by"]', $form).val() == 'nearest' || jQuery('select[name="sort_by"]', $form).val() == 'farthest')) && (jQuery(".snear", $form).val() == '' || jQuery(".snear", $form).val() == '<?php echo $default_near_text;?>')) jQuery(".snear", $form).val(default_location);
				geocodeAddress($form);
			}

			function updateSearchPosition(latLng, $form) {
				if (window.gdMaps === 'google') {
					jQuery('.sgeo_lat').val(latLng.lat());
					jQuery('.sgeo_lon').val(latLng.lng());
				} else if (window.gdMaps === 'osm') {
					jQuery('.sgeo_lat').val(latLng.lat);
					jQuery('.sgeo_lon').val(latLng.lon);
				}
				jQuery($form).trigger("submit"); // submit form after inserting the lat long positions
			}

			function geocodeAddress($form) {
				// Call the geocode function
				Sgeocoder = window.gdMaps == 'google' ? new google.maps.Geocoder() : null;

				if (jQuery('.snear', $form).val() == '' || ( jQuery('.sgeo_lat').val() != '' && jQuery('.sgeo_lon').val() != ''  ) || (jQuery('.snear', $form).val() && jQuery('.snear', $form).val().match("^<?php _e('In:','geodirectory');?>"))) {
					if (jQuery('.snear', $form).val() && jQuery('.snear', $form).val().match("^<?php _e('In:','geodirectory');?>")) {
						jQuery(".snear", $form).val('');
					}
					jQuery($form).trigger("submit");
				} else {
					var address = jQuery(".snear", $form).val();

					if (address && address.trim() == '<?php echo $default_near_text;?>') {
						initialise2();
					} else if(address && address.trim() == '<?php echo $near_my_location_text; ?>') {
						jQuery($form).trigger("submit");
					} else {
						<?php
						$near_add = geodir_get_option('search_near_addition');
						/**
						 * Adds any extra info to the near search box query when trying to geolocate it via google api.
						 *
						 * @since 1.0.0
						 */
						$near_add2 = apply_filters('geodir_search_near_addition', '');
						$near_address = ( $near_add ? '+", ' . $near_add . '"' : '' ) . $near_add2;
						?>
						var search_address = address<?php echo $near_address; ?>;
						<?php
						/**
						 * Add script before geocode address search.
						 *
						 * @since 2.2.9
						 *
						 * @param string $near_address Nearest address filter.
						 */
						do_action( 'geodir_geocode_address_search_script', $near_address );
						?>
						if (window.gdMaps === 'google') {
							var geocodeQueryParams = {'address': search_address<?php echo $google_geocode_properties; ?>};
							if (geodirIsZipCode(address)) {
								if (typeof geocodeQueryParams['componentRestrictions'] != 'undefined') {
									if (typeof geocodeQueryParams['componentRestrictions']['postalCode'] == 'undefined') {
										geocodeQueryParams['componentRestrictions']['postalCode'] = address;
									}
								} else {
									geocodeQueryParams['componentRestrictions'] = {'postalCode': address};
								}
							}
							<?php
							/**
							 * Execute before Google geocode address request.
							 *
							 * @since 2.3.23
							 *
							 * @param string $near_address Nearest address filter.
							 */
							do_action( 'geodir_google_geocode_address_request', $near_address );
							?>
							Sgeocoder.geocode(geocodeQueryParams,
								function (results, status) {
									<?php
									/**
									 * Add script after Google geocode address search.
									 *
									 * @since 2.2.10
									 *
									 * @param string $near_address Nearest address filter.
									 */
									do_action( 'geodir_google_geocode_results_script', $near_address );
									?>
									if (status == google.maps.GeocoderStatus.OK) {
										updateSearchPosition(results[0].geometry.location, $form);
									} else {
										alert("<?php esc_attr_e('Search was not successful for the following reason :', 'geodirectory');?>" + status);
									}
								});
						} else if (window.gdMaps === 'osm') {
							var osmCountryCodes = false;
							<?php
							/**
							 * Execute before OpenStreetMap geocode address request.
							 *
							 * @since 2.3.23
							 *
							 * @param string $near_address Nearest address filter.
							 */
							do_action( 'geodir_osm_geocode_address_request', $near_address );
							?>
							geocodePositionOSM(false, search_address, osmCountryCodes, false,
								function(geo) {
									<?php
									/**
									 * Add script after OpenStreetMaps geocode address search.
									 *
									 * @since 2.2.10
									 *
									 * @param string $near_address Nearest address filter.
									 */
									do_action( 'geodir_osm_geocode_results_script', $near_address );
									?>
									if (typeof geo !== 'undefined' && geo.lat && geo.lon) {
										updateSearchPosition(geo, $form);
									} else {
										alert("<?php esc_attr_e('Search was not successful for the requested address.', 'geodirectory');?>");
									}
								});
						} else {
							jQuery($form).trigger("submit");
						}
					}
				}
			}

			function geodirIsZipCode(string) {
				if (/^\d+$/.test(string)) {
					if (string.length > 3 && string.length < 7) {
						return true;
					}
				}
				return false;
			}

			function initialise2() {
				if (!window.gdMaps) {
					return;
				}

				if (window.gdMaps === 'google') {
					var latlng = new google.maps.LatLng(56.494343, -4.205446);
					var myOptions = {
						zoom: 4,
						mapTypeId: google.maps.MapTypeId.TERRAIN,
						disableDefaultUI: true
					}
				} else if (window.gdMaps === 'osm') {
					var latlng = new L.LatLng(56.494343, -4.205446);
					var myOptions = {
						zoom: 4,
						mapTypeId: 'TERRAIN',
						disableDefaultUI: true
					}
				}
				try { prepareGeolocation(); } catch (e) {}
				doGeolocation();
			}

			function doGeolocation() {
				if (navigator.geolocation) {
					navigator.geolocation.getCurrentPosition(positionSuccess, positionError);
				} else {
					positionError(-1);
				}
			}

			function positionError(err) {
				var msg;
				switch (err.code) {
					case err.UNKNOWN_ERROR:
						msg = "<?php _e('Unable to find your location','geodirectory');?>";
						break;
					case err.PERMISSION_DENINED:
						msg = "<?php _e('Permission denied in finding your location','geodirectory');?>";
						break;
					case err.POSITION_UNAVAILABLE:
						msg = "<?php _e('Your location is currently unknown','geodirectory');?>";
						break;
					case err.BREAK:
						msg = "<?php _e('Attempt to find location took too long','geodirectory');?>";
						break;
					default:
						msg = "<?php _e('Location detection not supported in browser','geodirectory');?>";
				}
				jQuery('#info').html(msg);
			}

			function positionSuccess(position) {
				var coords = position.coords || position.coordinate || position;
				jQuery('.sgeo_lat').val(coords.latitude);
				jQuery('.sgeo_lon').val(coords.longitude);

				jQuery('.geodir-listing-search').trigger("submit");
			}

			/**
			 * On unload page do some cleaning so back button cache does not store these values.
			 */
			jQuery(window).on("beforeunload", function(e) {
				if(jQuery('.sgeo_lat').length ){
					jQuery('.sgeo_lat').val('');
					jQuery('.sgeo_lon').val('');
				}
			});
		</script>
		<?php
	}

    /**
     * Return protocol relative asset URL.
     *
     * @since 2.0.0
     *
     * @param string $path URL Path.
     * @return string
     */
	private static function get_asset_url( $path ) {
		return str_replace( array( 'http:', 'https:' ), '', plugins_url( $path, geodir_plugin_url() ) );
	}

	/**
	 * Register a script for use.
     *
     * @since 2.0.0
	 *
	 * @uses   wp_register_script()
	 * @access private
	 * @param  string   $handle Handle.
	 * @param  string   $path Path.
	 * @param  array $deps Optional.  Deps. Default jquery.
	 * @param  string   $version Optional. Version Default GEODIRECTORY_VERSION.
	 * @param  boolean  $in_footer Optional. In footer. Default true.
	 */
	private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = GEODIRECTORY_VERSION, $in_footer = true ) {
		self::$scripts[] = $handle;
		// BuddyBoss loads basic version of select2.
		if ( $handle == 'select2' && wp_script_is( 'select2', 'registered' ) && ( ! geodir_design_style() || function_exists( 'buddyboss_theme' ) || class_exists( 'BuddyBoss_Theme' ) ) ) {
			wp_deregister_script( 'select2' ); // Fix conflict with select2 basic version loaded via 3rd party plugins.
		}
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Register and enqueue a script for use.
     *
     * @since 2.0.0
	 *
	 * @uses   wp_enqueue_script()
	 * @access private
	 * @param  string   $handle Handle.
	 * @param  string   $path Optional. Script path. Default null.
	 * @param  array $deps Optional. Deps. Default jquery.
	 * @param  string   $version Optional. Version. Default GEODIRECTORY_VERSION.
	 * @param  boolean  $in_footer Optional. In footer. Default true.
	 */
	public static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = GEODIRECTORY_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, self::$scripts ) && $path ) {
			self::register_script( $handle, $path, $deps, $version, $in_footer );
		}

		wp_enqueue_script( $handle );
	}

	/**
	 * Register a style for use.
     *
     * @since 2.0.0
	 *
	 * @uses   wp_register_style()
	 * @access private
	 * @param  string   $handle Handle.
	 * @param  string   $path Style path.
	 * @param  array  $deps Optional. Deps. Default array.
	 * @param  string   $version Optional. Version. Default GEODIRECTORY_VERSION.
	 * @param  string   $media Optional. Media. Default all.
	 * @param  boolean  $has_rtl Optional. Has rtl. Default false.
	 */
	private static function register_style( $handle, $path, $deps = array(), $version = GEODIRECTORY_VERSION, $media = 'all', $has_rtl = false ) {
		self::$styles[] = $handle;
		if ( $handle == 'select2' && wp_style_is( 'select2', 'registered' ) ) {
			wp_deregister_style( 'select2' ); // Fix conflict with select2 basic version loaded via 3rd party plugins.
		}
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $has_rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Register and enqueue a styles for use.
     *
     * @since 2.0.0
	 *
	 * @uses   wp_enqueue_style()
	 * @access private
	 * @param  string   $handle Handle.
	 * @param  string   $path Optional. Style path. Default null.
	 * @param  array $deps Optional. Deps. Default array.
	 * @param  string   $version Optional. Version. Default GEODIRECTORY_VERSION.
	 * @param  string   $media Optional. Media. Default all.
	 * @param  boolean  $has_rtl Optional. Has rtl. Default false.
	 */
	public static function enqueue_style( $handle, $path = '', $deps = array(), $version = GEODIRECTORY_VERSION, $media = 'all', $has_rtl = false ) {
		if ( ! in_array( $handle, self::$styles ) && $path ) {
			self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register all GeoDir scripts.
     *
     * @since 2.0.0
	 */
	private static function register_scripts() {

		$map_lang = "&language=" . GeoDir_Maps::map_language();
		$map_key = GeoDir_Maps::google_api_key(true);
		/** This filter is documented in geodirectory_template_tags.php */
		$map_extra = apply_filters('geodir_googlemap_script_extra', '');

		$suffix           = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$aui = geodir_design_style() ? '/aui' : '';

		$plupload_deps = array( 'plupload', 'jquery', 'jquery-ui-sortable' );
		if ( wp_is_mobile() ) {
			// jQuery UI Touch Punch to enable sorting on touch device.
			$plupload_deps[] = 'jquery-touch-punch';
		}

		$register_scripts = array(
			'select2' => array(
				'src'     => geodir_plugin_url() . '/assets/js/select2/select2.full' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
				'version' => '4.0.4',
			),
			'geodir-select2' => array(
				'src'     => geodir_plugin_url() . '/assets/js/geodir-select2' . $suffix . '.js',
				'deps'    => array( 'jquery', 'select2' ),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-jquery-ui-timepicker' => array(
				'src'     => geodir_plugin_url() . '/assets/js/jquery.ui.timepicker' . $suffix . '.js',
				'deps'    => array('jquery-ui-datepicker', 'jquery-ui-slider'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-map' => array(
				'src'     => geodir_plugin_url() . '/assets/js/geodir-map' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
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
				'deps'    => array('geodir-leaflet'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-goMap' => array(
				'src'     => geodir_plugin_url() . '/assets/js/goMap' . $suffix . '.js',
				'deps'    => array('jquery'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-map-widget' => array(
				'src'     => geodir_plugin_url() . '/assets'.$aui.'/js/map' . $suffix . '.js',
				'deps'    => array('jquery'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-plupload' => array(
				'src'     => geodir_plugin_url() . '/assets'.$aui.'/js/geodirectory-plupload' . $suffix . '.js',
				'deps'    => $plupload_deps,
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir' => array(
				'src'     =>  geodir_plugin_url() . '/assets'.$aui.'/js/geodirectory'. $suffix . '.js',
				'deps'    => array('jquery'),
				'version' => GEODIRECTORY_VERSION,
			),
			'jquery-flexslider' => array(
				'src'     => geodir_plugin_url() . '/assets/js/jquery.flexslider' . $suffix . '.js',
				'deps'    => array('jquery'),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir-add-listing' => array(
				'src'     => geodir_plugin_url() . '/assets'.$aui.'/js/add-listing' . $suffix . '.js',
				'deps'    => array( 'jquery' ),
				'version' => GEODIRECTORY_VERSION,
			),
			'geodir_lity' => array(
				'src'     => geodir_plugin_url() . '/assets/js/libraries/gd_lity' . $suffix . '.js',
				'deps'    => array('jquery'),
				'version' => GEODIRECTORY_VERSION,
			)
		);
		foreach ( $register_scripts as $name => $props ) {
			self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
		}
	}

	/**
	 * Register all GeoDir styles.
     *
     * @since 2.0.0
	 */
	private static function register_styles() {
		$register_styles = array(
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
			'geodir-rtl' => array(
				'src'     => geodir_plugin_url() . '/assets/css/frontend.css',
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
		);
		foreach ( $register_styles as $name => $props ) {
			self::register_style( $name, $props['src'], $props['deps'], $props['version'], 'all', $props['has_rtl'] );
		}
	}

	/**
	 * Register/queue frontend scripts.
     *
     * @since 2.0.0
	 */
	public static function load_scripts() {
		global $post, $geodir_frontend_scripts_loaded;

		$design_style = geodir_design_style();

		if ( geodir_load_scripts_on_call() ) {
			if ( wp_doing_ajax() || wp_doing_cron() ) {
				return;
			}

			if ( $design_style && wp_script_is( 'geodir', 'enqueued' ) ) {
				return;
			} else if ( ! $design_style && wp_script_is( 'geodir-core', 'enqueued' ) ) {
				return;
			}
		}

		// Register scripts/styles
		self::register_scripts();
		self::register_styles();

		if ( ! $design_style ) {
			// global enqueues
			// css
			self::enqueue_style( 'select2' );
			self::enqueue_style( 'geodir-core' );
			// js
			self::enqueue_script( 'select2' );
			self::enqueue_script( 'geodir-select2' );
			self::enqueue_script( 'geodir' );
			self::enqueue_script( 'geodir_lity' );

			// rtl
			if ( is_rtl() ) {
				self::enqueue_style( 'geodir-rtl' );
			}

			// add-listing
			if ( geodir_is_page( 'add-listing' ) && ! isset( $_REQUEST['ct_builder'] ) ) {
				self::enqueue_script( 'geodir-plupload' );
				self::enqueue_script( 'geodir-add-listing' );
				self::enqueue_script( 'geodir-jquery-ui-timepicker' );

				wp_enqueue_script( 'jquery-ui-autocomplete' ); // add listing only?
			}
		} else {
			// js
			self::enqueue_script( 'geodir' ); // original

			// add-listing @todo do we need all these?
			if ( geodir_is_page( 'add-listing' ) && ! isset( $_REQUEST['ct_builder'] ) ) {
				self::enqueue_script( 'geodir-plupload' );
				self::enqueue_script( 'geodir-add-listing' );
			}
		}

		$map_api = GeoDir_Maps::active_map();
		wp_add_inline_script( 'jquery', "window.gdSetMap = window.gdSetMap || '" . $map_api . "';window.gdLoadMap = window.gdLoadMap || '" . geodir_lazy_load_map() . "';");
		wp_add_inline_script( 'jquery-core', "window.gdSetMap = window.gdSetMap || '" . $map_api . "';window.gdLoadMap = window.gdLoadMap || '" . geodir_lazy_load_map() . "';");// wp.com page optimizer plugins breaks if just jquery used here.

		// Maps
		if ( geodir_lazy_load_map() ) {
			// Lazy Load
			if ( $map_api != 'none' && geodir_is_page( 'add-listing' ) ) {
				self::enqueue_script( 'geodir-map' );
				wp_add_inline_script( 'geodir-map', GeoDir_Maps::google_map_callback(), 'before' );
			}
		} else {
			// Normal
			if ( in_array( $map_api, array( 'auto', 'google' ) ) ) {
				if ( ! geodir_load_scripts_on_call() ) {
					self::enqueue_script('geodir-google-maps');
					self::enqueue_script('geodir-g-overlappingmarker'); // Move to map widget.
					wp_add_inline_script( 'geodir-google-maps', GeoDir_Maps::google_map_callback(), 'before' );
				}
			} else if ( $map_api == 'osm' ) {
				if ( ! geodir_load_scripts_on_call() ) {
					self::enqueue_style('leaflet');
					self::enqueue_style('leaflet-routing-machine');

					self::enqueue_script('geodir-leaflet');
					self::enqueue_script('geodir-leaflet-geo');
					self::enqueue_script('leaflet-routing-machine');
					self::enqueue_script('geodir-o-overlappingmarker');
				}
			}

			if ( $map_api != 'none' && ! geodir_load_scripts_on_call() ) {
				self::enqueue_script( 'geodir-goMap' );

				if ( $footer_script = GeoDir_Maps::footer_script() ) {
					wp_add_inline_script( 'geodir-goMap', $footer_script, 'before' );
				}
			}
		}

		if ( geodir_load_scripts_on_call() ) {
			$geodir_frontend_scripts_loaded = true;
		}
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
     *
     * @since 2.0.0
     *
	 * @access private
	 * @param  string $handle
	 * @return array|bool
	 */
	private static function get_script_data( $handle ) {
		global $wp;

		switch ( $handle ) {
			case 'geodir' :
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
						'ajax_url' => geodir_ajax_url(),
						'gd_ajax_url' => geodir_ajax_url( true ),
						'has_gd_ajax' => ( defined( 'GEODIR_FAST_AJAX' ) && geodir_get_option( 'fast_ajax' ) ? 1 : 0 ),
						'gd_modal' => (int)geodir_get_option('geodir_disable_gb_modal'),
						'is_rtl' => (bool) is_rtl(),
						'basic_nonce' => wp_create_nonce( 'geodir_basic_nonce'),
						'text_add_fav'      => apply_filters('geodir_add_favourite_text', __( 'Add to Favorites', 'geodirectory' )),
						'text_fav'          => apply_filters('geodir_favourite_text', __('Favorite', 'geodirectory' ) ),
						'text_remove_fav'   => apply_filters('geodir_remove_favourite_text', __( 'Remove from Favorites', 'geodirectory' )),
						'text_unfav'        => apply_filters('geodir_unfavourite_text', __( 'Unfavorite', 'geodirectory' )),
						'icon_fav'          => apply_filters('geodir_favourite_icon', 'fas fa-heart'),
						'icon_unfav'        => apply_filters('geodir_unfavourite_icon', 'fas fa-heart'),
					) + geodir_params()
				);
			break;
			case 'geodir-select2' :
				return array(
					//'countries'                 => json_encode( array_merge( WC()->countries->get_allowed_country_states(), WC()->countries->get_shipping_country_states() ) ),
					'i18n_select_state_text'    => esc_attr__( 'Select an option&hellip;', 'geodirectory' ),
					'i18n_no_matches'           => _x( 'No matches found', 'enhanced select', 'geodirectory' ),
					'i18n_ajax_error'           => _x( 'Loading failed', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_short_1'    => _x( 'Please enter 1 or more characters', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_short_n'    => _x( 'Please enter %item% or more characters', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_long_1'     => _x( 'Please delete 1 character', 'enhanced select', 'geodirectory' ),
					'i18n_input_too_long_n'     => _x( 'Please delete %item% characters', 'enhanced select', 'geodirectory' ),
					'i18n_selection_too_long_1' => _x( 'You can only select 1 item', 'enhanced select', 'geodirectory' ),
					'i18n_selection_too_long_n' => _x( 'You can only select %item% items', 'enhanced select', 'geodirectory' ),
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
			case 'geodir-map' :
				return geodir_map_params();
				break;

		}
		return false;
	}

	/**
	 * Localize scripts only when enqueued.
     *
     * @since 2.0.0
	 */
	public static function localize_printed_scripts() {
		foreach ( self::$scripts as $handle ) {
			self::localize_script( $handle );
		}
	}
}

GeoDir_Frontend_Scripts::init();

<?php
/**
 * Display Add Listing Map
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/map/map-add-listing.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    3.0.0
 *
 * @var string $prefix Field name prefix (e.g., 'address_')
 * @var string $lat Latitude value
 * @var string $lng Longitude value
 * @var int $mapzoom Zoom level value for the map
 * @var string $map_title Title for "Set Address on Map" button
 * @var object $default_location Default location object
 * @var bool $is_map_restrict Check if map is restricted to specific city
 * @var bool $auto_change_map_fields Whether to auto-fill address fields from map
 * @var bool $auto_change_address_fields_pin_move Whether to update fields when pin is moved
 * @var string $marker_icon Marker icon URL
 * @var array $icon_size Marker icon size {w, h}
 * @var bool $geodir_manual_map Check if manual map
 * @var string $design_style Design style (bootstrap, etc.)
 */

defined( 'ABSPATH' ) || exit;

global $aui_bs5, $gd_move_inline_script, $geodir_label_type;

$horizontal = empty( $geodir_label_type ) || $geodir_label_type == 'horizontal' ? true : false;

// Set default values
$defaultcity = isset( $default_location->city ) ? $default_location->city : '';
$default_lng = isset( $default_location->longitude ) ? $default_location->longitude : '';
$default_lat = isset( $default_location->latitude ) ? $default_location->latitude : '';
$defaultregion = isset( $default_location->region ) ? $default_location->region : '';
$defaultcountry = isset( $default_location->country ) ? $default_location->country : '';

$lat_lng_blank = false;
if ( ( ! isset( $lat ) || $lat == '' ) && ( ! isset( $lng ) || $lng == '' ) ) {
	$lat_lng_blank = true;
	$city = $defaultcity;
	$region = $defaultregion;
	$country = $defaultcountry;
	$lng = $default_lng;
	$lat = $default_lat;
}

if ( is_admin() && isset( $_REQUEST['tab'] ) && $mapzoom == '' ) {
	$mapzoom = isset( $_REQUEST['add_hood'] ) ? 10 : 4;
}

// Get marker icon and size
$resize_marker = apply_filters( 'geodir_map_marker_resize_marker', false );

// Get map language
$mapLang = geodirectory()->maps->map_language();

// Get country ISO for geocoding
$country_iso2 = '';
if ( ! empty( $default_location->country ) ) {
	$country_iso2 = geodirectory()->locations->get_country_iso2( $default_location->country );
}

/**
 * Fires at the start of the add javascript on the add listings map.
 *
 * @since 1.0.0
 * @param string $prefix The prefix for all elements.
 */
do_action( 'geodir_add_listing_js_start', $prefix );

// Initialize the map using our new modern JavaScript
if ( ! empty( $gd_move_inline_script ) ) { ob_start(); } else { ?>
<script type="text/javascript">
/* <![CDATA[ */
<?php } ?>
(function() {
	console.log('[Template] Inline script starting...');
	console.log('[Template] jQuery available?', typeof jQuery !== 'undefined');
	console.log('[Template] window.GeoDir?', !!window.GeoDir);
	console.log('[Template] window.GeoDir.Maps?', !!(window.GeoDir && window.GeoDir.Maps));

	// Wait for jQuery and map provider to be available
	if (typeof jQuery === 'undefined' || !window.GeoDir || !window.GeoDir.Maps) {
		console.error('[Template] Required dependencies not loaded - exiting early');
		return;
	}

	console.log('[Template] Dependencies OK, waiting for jQuery ready...');

	jQuery(function($) {
		console.log('[Template] jQuery ready fired');

		// Detect which map provider is available
		console.log('[Template] window.gdSetMap:', window.gdSetMap);
		console.log('[Template] Google Maps available?', !!(window.google && typeof google.maps !== 'undefined'));
		console.log('[Template] Leaflet available?', !!(typeof L !== 'undefined' && typeof L.version !== 'undefined'));

		var mapProvider = null;
		if ((window.gdSetMap == 'google' || window.gdSetMap == 'auto') && window.google && typeof google.maps !== 'undefined') {
			mapProvider = 'google';
			console.log('[Template] Detected Google Maps');
		} else if ((window.gdSetMap == 'osm' || window.gdSetMap == 'auto') && typeof L !== 'undefined' && typeof L.version !== 'undefined') {
			mapProvider = 'osm';
			console.log('[Template] Detected OSM/Leaflet');
		}

		window.gdMaps = window.gdMaps || mapProvider;
		console.log('[Template] window.gdMaps set to:', window.gdMaps);

		if (!window.gdMaps) {
			console.error('[Template] No map provider detected!');
			$('#<?php echo $prefix; ?>map_nofound').hide();
			$('#<?php echo $prefix; ?>map_notloaded').show();
			return;
		}

		<?php if ( geodir_lazy_load_map() ) { ?>
		console.log('[Template] Lazy load map enabled');
		// Lazy load the map
		$("#<?php echo $prefix; ?>map").geodirLoadMap({
			loadJS: true,
			forceLoad: <?php echo ( isset( $geodir_manual_map ) && $geodir_manual_map ? 'true' : 'false' ); ?>,
			callback: function() {
				console.log('[Template] Lazy load callback fired');
		<?php } else { ?>
		console.log('[Template] Lazy load disabled, creating map immediately');
		<?php } ?>
				// Create map provider instance
				console.log('[Template] About to create MapFactory...');
				var provider = GeoDir.Maps.MapFactory.create('<?php echo $prefix; ?>map', {
					latitude: <?php echo geodir_sanitize_float( $lat ); ?>,
					longitude: <?php echo geodir_sanitize_float( $lng ); ?>,
					zoom: <?php echo absint( $mapzoom ); ?>,
					maptype: 'ROADMAP',
					streetViewControl: true,
					scrollwheel: <?php echo geodir_get_option( 'geodir_add_listing_mouse_scroll' ) ? 'false' : 'true'; ?>,
					preferredProvider: mapProvider
				});

				console.log('[Template] Provider created:', provider);

				if (!provider || !provider.init()) {
					console.error('[Template] Provider initialization failed!');
					$('#<?php echo $prefix; ?>map_notloaded').show();
					return;
				}

				console.log('[Template] Provider initialized successfully');

				// Create AddressField instance with options
				console.log('[Template] Creating AddressField...');
				var addressField = new GeoDir.Maps.AddressField('<?php echo $prefix; ?>', provider, {
					lat: <?php echo $lat_lng_blank ? 'null' : geodir_sanitize_float( $lat ); ?>,
					lng: <?php echo $lat_lng_blank ? 'null' : geodir_sanitize_float( $lng ); ?>,
					mapZoom: <?php echo absint( $mapzoom ); ?>,
					defaultLocation: {
						latitude: <?php echo geodir_sanitize_float( $default_lat ); ?>,
						longitude: <?php echo geodir_sanitize_float( $default_lng ); ?>,
						city: '<?php echo addslashes_gpc( $defaultcity ); ?>',
						region: '<?php echo addslashes_gpc( $defaultregion ); ?>',
						country: '<?php echo addslashes_gpc( $defaultcountry ); ?>'
					},
					isRestrict: <?php echo $is_map_restrict ? 'true' : 'false'; ?>,
					autoChangeFields: <?php echo $auto_change_map_fields ? 'true' : 'false'; ?>,
					autoChangePinMove: <?php echo $auto_change_address_fields_pin_move ? 'true' : 'false'; ?>,
					minZoomLevel: <?php echo $is_map_restrict ? 5 : 0; ?>,
					markerIcon: '<?php echo $marker_icon; ?>',
					markerSize: {
						w: <?php echo $icon_size['w']; ?>,
						h: <?php echo $icon_size['h']; ?>
					},
					mapLang: '<?php echo esc_js( $mapLang ); ?>',
					countryISO: '<?php echo esc_js( $country_iso2 ); ?>',
					txt_geocode_error: '<?php echo esc_js( __( 'Geocode was not successful for the following reason:', 'geodirectory' ) ); ?>',
					txt_city_restrict: '<?php echo esc_js( wp_sprintf( __( 'Please choose any address of the (%s) city only.', 'geodirectory' ), $defaultcity ) ); ?>'
				});

				<?php
				/**
				 * Fires to add custom JavaScript for the add listing map.
				 *
				 * @since 3.0.0
				 * @param string $map_type 'google' or 'osm'
				 * @param string $map_name The active map name
				 * @param bool $geodir_manual_map Whether this is a manual map
				 * @param bool $gd_move_inline_script Whether to move inline scripts
				 */
				do_action( 'geodir_add_listing_map_inline_js', 'window.gdMaps', geodirectory()->maps->active_map(), $geodir_manual_map, $gd_move_inline_script );
				?>

				<?php
				/**
				 * Fires after the add listing map is initialized.
				 *
				 * @since 3.0.0
				 */
				do_action( 'geodir_add_listing_map_initialized' );
				?>
		<?php if ( geodir_lazy_load_map() ) { ?>
			}
		});
		<?php } ?>
	});

	// Set global variables for country-specific parsing
	window.geodir_split_uk = <?php echo geodir_split_uk() ? 'true' : 'false'; ?>;
	window.mapLang = '<?php echo esc_js( $mapLang ); ?>';
})();
<?php
if ( ! empty( $gd_move_inline_script ) ) {
	$inline_script = ob_get_clean();
	$inline_script = apply_filters( 'geodir_add_listing_map_inline_script', trim( $inline_script ), geodirectory()->maps->active_map(), $geodir_manual_map );
	wp_add_inline_script( 'geodir-maps', $inline_script );
} else {
	?>
/* ]]> */
</script>
<?php
}

// Output the "Set Address on Map" button
if ( ! wp_doing_ajax() ) { ?>
<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?><?php echo ( $horizontal ? ' row' : '' ); ?>">
	<?php if ( $horizontal ) { ?>
	<div class="col-sm-2 col-form-label"></div>
	<div class="col-sm-10">
	<?php } ?>
		<input type="button" id="<?php echo $prefix; ?>set_address_button" class="btn btn-primary text-center mx-auto" value="<?php esc_attr_e( $map_title, 'geodirectory' ); ?>" />
		<?php
		aui();
		echo AUI_Component_Helper::help_text( stripslashes( __( 'Click on "Set Address on Map" and then you can also drag map marker to locate the correct address', 'geodirectory' ) ) );
		?>
	<?php if ( $horizontal ) { ?>
	</div>
	<?php } ?>
</div>
<?php } ?>

<div class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> d-block">
	<?php
	/**
	 * Variables for the map template.
	 *
	 * @var array $map_options The map settings options.
	 * @var string $map_type The map type.
	 * @var string $map_canvas The map canvas string.
	 * @var string $height The map height setting.
	 * @var string $width The map width setting.
	 */
	$map_type = 'add_listing';
	$map_canvas = $prefix . 'map';
	$height = '350px';
	$width = '100%';
	$wrap_class = '';
	$hide_expand_map = true;

	$_tmpl_args = array(
		'map_type' => $map_type,
		'wrap_class' => $wrap_class,
		'map_canvas' => $map_canvas,
		'width' => $width,
		'height' => $height,
		'hide_expand_map' => $hide_expand_map,
		'extra_attribs' => ( isset( $extra_attribs ) ? $extra_attribs : '' )
	);

	$_tmpl_args['map_options'] = $_tmpl_args;
	$_template = $design_style . '/map/map.php';

	echo geodir_get_template_html( $_template, $_tmpl_args );
	?>
</div>

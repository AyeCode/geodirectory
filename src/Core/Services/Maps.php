<?php
/**
 * Maps Service
 *
 * @package     GeoDirectory\Core\Utils
 * @since       3.0.0
 * @author      AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Services;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A service for map-related helper functions.
 *
 * This class primarily handles retrieving map-related settings and simple
 * calculations. It is loaded via the main geodirectory() instance.
 *
 * @since 3.0.0
 */
final class Maps {

	/**
	 * Cache for marker sizes to avoid repeated file system checks.
	 *
	 * @var array<string, array{w: int, h: int}>
	 */
	private array $marker_sizes = [];

	/**
	 * Constructor is empty, allowing the container to auto-wire this class.
	 */
	public function __construct() {
		// No dependencies are injected here.
	}

	/**
	 * Get the map JS API provider name.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::active_map)
	 *
	 * @return string The map API provider name. ('google', 'osm', 'none')
	 */
	public function active_map(): string {
		// Access settings via the global function (service location)
		$active_map = (string) geodirectory()->settings->get( 'maps_api', 'google' );

		// If Google is selected but no API key is present, fallback to OSM.
		if ( ( $active_map === 'google' || $active_map === 'auto' ) && ! geodirectory()->settings->get( 'google_maps_api_key' ) ) {
			$active_map = 'osm';
		}

		if ( ! in_array( $active_map, [ 'none', 'auto', 'google', 'osm' ], true ) ) {
			$active_map = 'auto';
		}

		/**
		 * Filters the map JS API provider name.
		 *
		 * @since 1.6.1
		 * @param string $active_map The map API provider name.
		 */
		return apply_filters( 'geodir_map_name', $active_map );
	}

	/**
	 * Get the marker icon size.
	 * This will return width and height of icon in array (ex: w => 36, h => 45).
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::get_marker_size)
	 *
	 * @param string $icon         Marker icon URL.
	 * @param array  $default_size Default size array.
	 * @return array{w: int, h: int} The icon size.
	 */
	public function get_marker_size( string $icon, array $default_size = [ 'w' => 36, 'h' => 45 ] ): array {
		if ( ! empty( $this->marker_sizes[ $icon ] ) ) {
			return $this->marker_sizes[ $icon ];
		}

		if ( empty( $icon ) ) {
			$this->marker_sizes[ $icon ] = $default_size;
			return $default_size;
		}

		$icon_url = $icon;

		// Try to resolve relative paths to absolute paths to check the file.
		if ( ! path_is_absolute( $icon ) ) {
			$uploads = wp_upload_dir(); // Array of key => value pairs
			$icon    = str_replace( $uploads['baseurl'], $uploads['basedir'], $icon );
		}

		if ( ! path_is_absolute( $icon ) && strpos( $icon, WP_CONTENT_URL ) !== false ) {
			$icon = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $icon );
		}

		$sizes = [];
		if ( is_file( $icon ) && file_exists( $icon ) ) {
			// This assumes you have a global geodir_getimagesize() helper function.
			$size = geodir_getimagesize( trim( $icon ) );

			if ( ! empty( $size[0] ) && ! empty( $size[1] ) ) {
				$sizes = [ 'w' => (int) $size[0], 'h' => (int) $size[1] ];
			}
		}

		$sizes = ! empty( $sizes ) ? $sizes : $default_size;

		$this->marker_sizes[ $icon_url ] = $sizes;

		return $sizes;
	}

	/**
	 * Get the default marker icon URL.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::default_marker_icon)
	 *
	 * @param bool $full_path Optional. Return full path instead of URL. Default false.
	 * @return string The marker icon URL or path.
	 */
	public function default_marker_icon( bool $full_path = false ): string {
		// Access settings via the global function
		$icon = geodirectory()->settings->get( 'map_default_marker_icon' );

		if ( ! empty( $icon ) && (int) $icon > 0 ) {
			$icon = wp_get_attachment_url( (int) $icon );
		}

		if ( ! $icon ) {
			// This assumes GEODIRECTORY_PLUGIN_URL is a defined constant.
			$icon = geodir_file_relative_url( GEODIRECTORY_PLUGIN_URL . '/assets/images/pin.png' );
			// Update setting via the service
			geodirectory()->settings->update( 'map_default_marker_icon', $icon );
		}

		// This assumes geodir_file_relative_url is a defined global helper.
		$icon = geodir_file_relative_url( (string) $icon, $full_path );

		return apply_filters( 'geodir_default_marker_icon', $icon, $full_path );
	}

	/**
	 * Returns the default language of the map.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::map_language)
	 *
	 * @return string Returns the default language code (e.g., 'en').
	 */
	public function map_language(): string {
		// Access settings via the global function
		$language = (string) geodirectory()->settings->get( 'map_language', 'en' );

		/**
		 * Filter default map language.
		 *
		 * @since 1.0.0
		 * @param string $language Default map language.
		 */
		return apply_filters( 'geodir_default_map_language', $language );
	}

	/**
	 * Get OpenStreetMap routing language.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::osm_routing_language)
	 *
	 * @return string Routing language.
	 */
	public function osm_routing_language(): string {
		// Use instance method
		$map_lang     = $this->map_language();
		$allowed_langs = [ 'en', 'de', 'sv', 'es', 'sp', 'nl', 'fr', 'it', 'pt', 'sk', 'el', 'ca', 'ru', 'pl', 'uk' ];

		if ( in_array( $map_lang, $allowed_langs, true ) ) {
			$routing_lang = $map_lang;
		} elseif ( in_array( substr( $map_lang, 0, 2 ), $allowed_langs, true ) ) {
			$routing_lang = substr( $map_lang, 0, 2 );
		} else {
			$routing_lang = 'en';
		}

		return apply_filters( 'geodir_osm_routing_language', $routing_lang );
	}

	/**
	 * Returns the Google maps API key.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::google_api_key)
	 *
	 * @param bool $query If true, format as a query string parameter ("&key=...").
	 * @return string Returns the API key.
	 */
	public function google_api_key( bool $query = false ): string {
		// Access settings via the global function
		$key = (string) geodirectory()->settings->get( 'google_maps_api_key' );

		if ( $key && $query ) {
			$key = '&key=' . $key;
		}

		/**
		 * Filter Google maps api key.
		 *
		 * @since 1.6.4
		 * @param string $key   Google maps api key.
		 * @param bool   $query Whether the key is for a URL query.
		 */
		return apply_filters( 'geodir_google_api_key', $key, $query );
	}

	/**
	 * Returns the Google Geocoding API key.
	 *
	 * Falls back to the main Maps API key if not set explicitly.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::google_geocode_api_key)
	 *
	 * @param bool $query If true, format as a query string parameter ("&key=...").
	 * @return string Returns the Geocoding API key.
	 */
	public function google_geocode_api_key( bool $query = false ): string {
		// Access settings via the global function
		$key = (string) geodirectory()->settings->get( 'google_geocode_api_key' );

		if ( empty( $key ) ) {
			// Use instance method
			$key = $this->google_api_key(); // Fallback to main maps key
		}

		if ( $key && $query ) {
			$key = '&key=' . $key;
		}

		/**
		 * Filter Google Geocoding API key.
		 *
		 * @since 2.0.0.64
		 * @param string $key   Google Geocoding API key.
		 * @param bool   $query Whether the key is for a URL query.
		 */
		return apply_filters( 'geodir_google_geocode_api_key', $key, $query );
	}

	/**
	 * Get the map lazy load type.
	 *
	 * @since 3.0.0 (Migrated from GeoDir_Maps::lazy_load_map)
	 *
	 * @return string The map load type ('auto', 'click', or '').
	 */
	public function lazy_load_map(): string {
		// Access settings via the global function
		$lazy_load = (string) geodirectory()->settings->get( 'maps_lazy_load', '' );

		if ( ! in_array( $lazy_load, [ 'auto', 'click' ], true ) ) {
			$lazy_load = '';
		}

		// Never lazy load in the admin area (except during AJAX requests).
		if ( is_admin() && ! wp_doing_ajax() ) {
			$lazy_load = '';
		}

		/**
		 * Filter the map map load type
		 *
		 * @since 2.1.0.0
		 *
		 * @param string $lazy_load The map load type.
		 */
		return apply_filters( 'geodir_lazy_load_map', $lazy_load );
	}

	/**
	 * Adds the Leaflet (OSM) scripts as a fallback.
	 *
	 * This is a direct replacement for the old GeoDir_Maps::footer_script().
	 * It returns an inline script block to be printed in the footer.
	 *
	 * @since 3.0.0
	 *
	 * @return string The inline JavaScript block.
	 */
	public function footer_script(): string {
		$osm_extra = '';

		// Use the internal, non-static methods
		if ( $this->active_map() === 'auto' && ! $this->lazy_load_map() ) {

			// Use the new Plugin class to get the URL
			$plugin_url = \AyeCode\GeoDirectory\Core\Plugin::url();

			ob_start();
			?>
			<script type="text/javascript">
				(function() {
					// Check if Google Maps failed to load
					if ( !( window.google && typeof google.maps !== 'undefined' ) ) {

						// Define assets to load
						var assets = [
							{ type: 'css', id: 'geodirectory-leaflet-style-css', href: '<?php echo $plugin_url; ?>assets/leaflet/leaflet.css?ver=<?php echo GEODIRECTORY_VERSION; ?>' },
							{ type: 'css', id: 'geodirectory-leaflet-routing-style', href: '<?php echo $plugin_url; ?>assets/leaflet/routing/leaflet-routing-machine.css?ver=<?php echo GEODIRECTORY_VERSION; ?>' },
							{ type: 'script', id: 'geodirectory-leaflet-script', src: '<?php echo $plugin_url; ?>assets/leaflet/leaflet.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>' },
							{ type: 'script', id: 'geodirectory-leaflet-geo-script', src: '<?php echo $plugin_url; ?>assets/leaflet/osm.geocode.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>' },
							{ type: 'script', id: 'geodirectory-leaflet-routing-script', src: '<?php echo $plugin_url; ?>assets/leaflet/routing/leaflet-routing-machine.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>' },
							{ type: 'script', id: 'geodirectory-o-overlappingmarker-script', src: '<?php echo $plugin_url; ?>assets/jawj/oms-leaflet.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>' }
						];

						// Load assets without document.write
						var head = document.getElementsByTagName('head')[0];

						assets.forEach(function(asset) {
							if (document.getElementById(asset.id)) return; // Don't load twice

							if (asset.type === 'css') {
								var link = document.createElement('link');
								link.id = asset.id;
								link.rel = 'stylesheet';
								link.type = 'text/css';
								link.href = asset.href;
								link.media = 'all';
								head.appendChild(link);
							} else if (asset.type === 'script') {
								// We use document.write here to maintain the original's blocking behavior.
								// Refactoring this to use async/defer could break map initialization timing.
								document.write('<' + 'script id="' + asset.id + '" src="' + asset.src + '" type="text/javascript"><' + '/script>');
							}
						});
					}
				})();
			</script>
			<?php
			/**
			 * Fires after the map extra script output.
			 *
			 * @since 1.6.1
			 */
			do_action( 'geodir_maps_extra_script' );

			$osm_extra = ob_get_clean();
		}

		return $osm_extra;
	}

	/**
	 * Google Maps JavaScript API callback.
	 *
	 * @since 2.2.23
	 *
	 * @return string Callback script.
	 */
	public function google_map_callback() {
		$script = 'function geodirInitGoogleMap(){window.geodirGoogleMapsCallback=true;try{jQuery(document).trigger("geodir.googleMapsCallback")}catch(err){}}';

		/**
		 * Filters the Google Maps JavaScript callback.
		 *
		 * @since 2.2.23
		 *
		 * @param string $script The callback script.
		 */
		return apply_filters( 'geodir_google_map_callback_script', $script );
	}

	/**
	 * Render add listing map.
	 *
	 * This method renders the map interface for adding/editing listings.
	 * It replaces the inline JavaScript with modern modular code.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Map arguments.
	 * @return string Rendered map HTML.
	 */
	public function render_add_listing_map( array $args = [] ): string {
		// Extract arguments with defaults
		$lat = $args['lat'] ?? '';
		$lng = $args['lng'] ?? '';
		$prefix = $args['prefix'] ?? 'address_';
		$map_title = $args['map_title'] ?? __( 'Set Address on Map', 'geodirectory' );

		// Get default location
		$default_location = geodirectory()->locations->get_default();

		// Get map settings
		$mapzoom = (int) geodir_get_option( 'map_default_zoom', 12 );
		$is_map_restrict = apply_filters( 'geodir_add_listing_map_restrict', true );
		$auto_change_map_fields = apply_filters( 'geodir_auto_change_map_fields', true );
		$auto_change_address_fields_pin_move = apply_filters( 'geodir_auto_change_address_fields_pin_move', true );

		// Get marker icon and size
		$marker_icon = $this->default_marker_icon( true );
		$icon_size = $this->get_marker_size( $marker_icon, [ 'w' => 20, 'h' => 34 ] );

		// Check if manual map
		$geodir_manual_map = $args['manual_map'] ?? false;

		// Prepare template variables
		$template_args = [
			'prefix' => $prefix,
			'lat' => $lat,
			'lng' => $lng,
			'mapzoom' => $mapzoom,
			'map_title' => $map_title,
			'default_location' => $default_location,
			'is_map_restrict' => $is_map_restrict,
			'auto_change_map_fields' => $auto_change_map_fields,
			'auto_change_address_fields_pin_move' => $auto_change_address_fields_pin_move,
			'marker_icon' => $marker_icon,
			'icon_size' => $icon_size,
			'geodir_manual_map' => $geodir_manual_map,
			'design_style' => geodir_design_style()
		];

		/**
		 * Filters the add listing map template arguments.
		 *
		 * @since 3.0.0
		 *
		 * @param array $template_args Template arguments.
		 * @param array $args Original arguments passed to method.
		 */
		$template_args = apply_filters( 'geodir_add_listing_map_args', $template_args, $args );

		// Set global for inline script handling
		global $gd_move_inline_script;
		$gd_move_inline_script = apply_filters( 'geodir_add_listing_move_inline_script', true );

		// Render the template
		return geodir_get_template_html( 'bootstrap/map/map-add-listing.php', $template_args );
	}
}

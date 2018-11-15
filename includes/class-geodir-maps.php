<?php
/**
 * Maps
 *
 * Setup GD maps.
 *
 * @class     GeoDir_Maps
 * @since     2.0.0
 * @package   GeoDirectory
 * @category  Class
 * @author    AyeCode
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDir_Maps Class.
 */
class GeoDir_Maps {

	public function __construct() {
		add_action( 'wp_footer', array( __CLASS__, 'footer_script' ), 10 );
		add_action( 'admin_footer', array( __CLASS__, 'footer_script' ), 10 );
	}

	/**
	 * Get the map JS API provider name.
	 *
	 * @since 1.6.1
	 * @package GeoDirectory
	 *
	 * @return string The map API provider name.
	 */
	public static function active_map() {
		$active_map = geodir_get_option( 'maps_api', 'google' );

		if(($active_map =='google' || $active_map =='auto') && !geodir_get_option( 'google_maps_api_key' )){
			$active_map = 'osm';
		}
		
		if ( ! in_array( $active_map, array( 'none', 'auto', 'google', 'osm' ) ) ) {
			$active_map = 'auto';
		}

		/**
		 * Filter the map JS API provider name.
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
	 * @since 1.6.1
	 * @package GeoDirectory
	 *
	 * @global $gd_marker_sizes Array of the marker icons sizes.
	 *
	 * @param string $icon Marker icon url.
	 * @return array The icon size.
	 */
	public static function get_marker_size( $icon, $default_size = array( 'w' => 36, 'h' => 45 ) ) {
		global $gd_marker_sizes;

		if ( empty( $gd_marker_sizes ) ) {
			$gd_marker_sizes = array();
		}

		if ( ! empty( $gd_marker_sizes[ $icon ] ) ) {
			return $gd_marker_sizes[ $icon ];
		}

		if ( empty( $icon ) ) {
			$gd_marker_sizes[ $icon ] = $default_size;

			return $default_size;
		}

		$icon_url = $icon;

		$uploads = wp_upload_dir(); // Array of key => value pairs

		if ( ! path_is_absolute( $icon ) ) {
			$icon = str_replace( $uploads['baseurl'], $uploads['basedir'], $icon );
		}

		if ( ! path_is_absolute( $icon ) && strpos( $icon, WP_CONTENT_URL ) !== false ) {
			$icon = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $icon );
		}

		$sizes = array();
		if ( is_file( $icon ) && file_exists( $icon ) ) {
			$size = getimagesize( trim( $icon ) );

			if ( ! empty( $size[0] ) && ! empty( $size[1] ) ) {
				$sizes = array( 'w' => $size[0], 'h' => $size[1] );
			}
		}

		$sizes = ! empty( $sizes ) ? $sizes : $default_size;

		$gd_marker_sizes[ $icon_url ] = $sizes;

		return $sizes;
	}

	/**
	 * Adds the marker cluster script for OpenStreetMap when Google JS Library not loaded.
	 *
	 * @since 1.6.1
	 * @package GeoDirectory
	 */
	public static function footer_script() {
		if ( in_array( self::active_map(), array( 'auto', 'google' ) ) && wp_script_is( 'geodirectory-googlemap-script', 'done') ) {
			$plugin_url = geodir_plugin_url();
?>
<script type="text/javascript">
if (!(window.google && typeof google.maps !== 'undefined')) {
	var css = document.createElement("link");css.setAttribute("rel","stylesheet");css.setAttribute("type","text/css");css.setAttribute("media","all");css.setAttribute("id","geodirectory-leaflet-style-css");css.setAttribute("href","<?php echo $plugin_url; ?>/assets/leaflet/leaflet.css?ver=<?php echo GEODIRECTORY_VERSION; ?>");
	document.getElementsByTagName("head")[0].appendChild(css);
	var css = document.createElement("link");css.setAttribute("rel","stylesheet");css.setAttribute("type","text/css");css.setAttribute("media","all");css.setAttribute("id","geodirectory-leaflet-routing-style");css.setAttribute("href","<?php echo $plugin_url; ?>/assets/leaflet/routing/leaflet-routing-machine.css?ver=<?php echo GEODIRECTORY_VERSION; ?>");
	document.getElementsByTagName("head")[0].appendChild(css);
	document.write('<' + 'script id="geodirectory-leaflet-script" src="<?php echo $plugin_url; ?>/assets/leaflet/leaflet.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>" type="text/javascript"><' + '/script>');
	document.write('<' + 'script id="geodirectory-leaflet-geo-script" src="<?php echo $plugin_url; ?>/assets/leaflet/osm.geocode.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>" type="text/javascript"><' + '/script>');
	document.write('<' + 'script id="geodirectory-leaflet-routing-script" src="<?php echo $plugin_url; ?>/assets/leaflet/routing/leaflet-routing-machine.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>" type="text/javascript"><' + '/script>');
	document.write('<' + 'script id="geodirectory-o-overlappingmarker-script" src="<?php echo $plugin_url; ?>/assets/jawj/oms-leaflet.min.js?ver=<?php echo GEODIRECTORY_VERSION; ?>" type="text/javascript"><' + '/script>');
}
</script>
<?php
		}
	}

	/**
	 * Function for get default marker icon.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $full_path Optional. Default marker icon full path. Default false.
	 * @return string $icon.
	 */
	public static function default_marker_icon( $full_path = false ) {
		$icon = geodir_get_option( 'map_default_marker_icon' );

		if ( ! empty( $icon ) && (int) $icon > 0 ) {
			$icon = wp_get_attachment_url( $icon );
		}
		
		if ( ! $icon ) {
			$icon = geodir_file_relative_url( GEODIRECTORY_PLUGIN_URL . '/assets/images/pin.png' );
			geodir_update_option( 'map_default_marker_icon', $icon );
		}
		
		$icon = geodir_file_relative_url( $icon, $full_path );
		
		return apply_filters( 'geodir_default_marker_icon', $icon, $full_path );
	}

	/**
	 * Returns the default language of the map.
	 *
	 * @since   1.0.0
	 * @package GeoDirectory
	 * @return string Returns the default language.
	 */
	public static function map_language() {
		$geodir_default_map_language = geodir_get_option( 'map_language' );
		if ( empty( $geodir_default_map_language ) ) {
			$geodir_default_map_language = 'en';
		}

		/**
		 * Filter default map language.
		 *
		 * @since 1.0.0
		 *
		 * @param string $geodir_default_map_language Default map language.
		 */
		return apply_filters( 'geodir_default_map_language', $geodir_default_map_language );
	}

	/**
	 * Returns the Google maps api key.
	 *
	 * @since   1.6.4
	 * @since   2.0.0 Added $query param.
	 * @param bool $query If this is for a query and if so add the key=.
	 * @package GeoDirectory
	 * @return string Returns the api key.
	 */
	public static function google_api_key( $query = false ) {
		$key = geodir_get_option( 'google_maps_api_key' );

		if ( $key && $query ) {
			$key = "&key=" . $key;
		}

		/**
		 * Filter Google maps api key.
		 *
		 * @since 1.6.4
		 *
		 * @param string $key Google maps api key.
		 */
		return apply_filters( 'geodir_google_api_key', $key );
	}

	/**
	 * Categories list on map.
	 *
	 * @since 2.0.0
	 * @package GeoDirectory
	 *
	 * @param string $post_type The post type e.g gd_place.
	 * @param int $cat_parent Optional. Parent term ID to retrieve its child terms. Default 0.
	 * @param bool $hide_empty Optional. Do you want to hide the terms that has no posts. Default true.
	 * @param int $padding Optional. CSS padding value in pixels. e.g: 12 will be considers as 12px.
	 * @param string $map_canvas Unique canvas name for your map.
	 * @param bool $child_collapse Do you want to collapse child terms by default?.
	 * @param bool $is_home_map Optional. Is this a home page map? Default: false.
	 * @return string|void
	 */
	public static function get_categories_filter( $post_type, $cat_parent = 0, $hide_empty = true, $padding = 0, $map_canvas = '', $child_collapse, $is_home_map = false ) {
		global $cat_count, $geodir_cat_icons;

		$taxonomy = $post_type . 'category';

		$exclude_categories = geodir_get_option( 'exclude_cat_on_map', array() );
		$exclude_categories = !empty($exclude_categories[$taxonomy]) && is_array($exclude_categories[$taxonomy]) ? array_unique($exclude_categories[$taxonomy]) : array();

		$exclude_cat_str = implode(',', $exclude_categories);

		if ($exclude_cat_str == '') {
			$exclude_cat_str = '0';
		}

		$cat_terms = get_terms( array( 'taxonomy' => array( $taxonomy ), 'parent' => $cat_parent, 'exclude' => $exclude_cat_str, 'hide_empty ' => $hide_empty ) );
		
		if ($hide_empty) {
			$cat_terms = geodir_filter_empty_terms($cat_terms);
		}

		$main_list_class = '';
		//If there are terms, start displaying
		if ( count( $cat_terms ) > 0 ) {
			//Displaying as a list
			$p = $padding * 15;
			$padding++;

			if ($cat_parent == 0) {
				$list_class = 'main_list geodir-map-terms';
				$display = '';
			} else {
				$list_class = 'sub_list';
				$display = !$child_collapse ? '' : 'display:none';
			}

			$out = '<ul class="treeview ' . $list_class . '" style="margin-left:' . $p . 'px;' . $display . ';">';

			$geodir_cat_icons = geodir_get_term_icon();

			$untick_terms = geodir_get_option('geodir_home_map_untick' );
			
			foreach ( $cat_terms as $cat_term ) {
				$icon = !empty( $geodir_cat_icons ) && isset( $geodir_cat_icons[ $cat_term->term_id ] ) ? $geodir_cat_icons[ $cat_term->term_id ] : '';

				if ( ! in_array( $cat_term->term_id, $exclude_categories ) ) {
					//Secret sauce.  Function calls itself to display child elements, if any
					$checked = 'checked="checked"';

					// Untick the category by default on home map
					if ( $is_home_map && ! empty( $untick_terms ) ) {
						$term_id = apply_filters( 'geodir_map_categories_term_id', $cat_term->term_id, $post_type );
						if ( ! empty( $untick_terms ) && in_array( $post_type . '_' . $term_id, $untick_terms ) ) {
							$checked = '';
						}
					}

					$term_check = '<input type="checkbox" ' . $checked . ' id="' .$map_canvas.'_tick_cat_'. $cat_term->term_id . '" class="group_selector ' . $main_list_class . '"';
					$term_check .= ' name="' . $map_canvas . '_cat[]" ';
					$term_check .= '  title="' . esc_attr(geodir_utf8_ucfirst($cat_term->name)) . '" value="' . $cat_term->term_id . '" onclick="javascript:build_map_ajax_search_param(\'' . $map_canvas . '\',false, this)">';
					$term_img = '<img height="15" width="15" alt="' . $cat_term->taxonomy . '" src="' . $icon . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '"/>';
					$out .= '<li>' . $term_check . '<label for="' . $map_canvas.'_tick_cat_'. $cat_term->term_id . '">' . $term_img . geodir_utf8_ucfirst($cat_term->name) . '</label><span class="gd-map-cat-toggle"><i class="fas fa-long-arrow-alt-down" aria-hidden="true" style="display:none"></i></span>';
				}

				// get sub category by recursion
				$out .= self::get_categories_filter( $post_type, $cat_term->term_id, $hide_empty, $padding, $map_canvas, $child_collapse, $is_home_map );

				$out .= '</li>';
			}

			$out .= '</ul>';

			return $out;
		} else {
			if ( $cat_parent == 0 ) {
				return _e( 'No category', 'geodirectory' );
			}
		}
		return;
	}

	/**
	 * Function for get map popup content.
	 *
	 * @since 2.0.0
	 *
	 * @param int|object $item Map popup content item int or objects values.
	 * @return string $content.
	 */
	public static function marker_popup_content( $item ) {
		global $post, $gd_post;

		$content = '';

		if ( is_int( $item ) ) {
			$item = geodir_get_post_info( $item );
		}

		if ( ! ( ! empty( $item->post_type ) && geodir_is_gd_post_type( $item->post_type ) ) ) {
			return $content;
		}

		$post		= $item;
		$gd_post 	= $item;

		setup_postdata( $gd_post );

		$content = GeoDir_Template_Loader::map_popup_template_content();

		if ( $content != '' ) {
			$content = trim( $content );
		}

		return $content;
	}

}

return new GeoDir_Maps();

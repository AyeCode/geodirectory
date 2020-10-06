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

		if ( ! path_is_absolute( $icon ) ) {
			$uploads = wp_upload_dir(); // Array of key => value pairs

			$icon = str_replace( $uploads['baseurl'], $uploads['basedir'], $icon );
		}

		if ( ! path_is_absolute( $icon ) && strpos( $icon, WP_CONTENT_URL ) !== false ) {
			$icon = str_replace( WP_CONTENT_URL, WP_CONTENT_DIR, $icon );
		}

		$sizes = array();
		if ( is_file( $icon ) && file_exists( $icon ) ) {
			$size = getimagesize( trim( $icon ) );

			// Check for .svg image
			if ( empty( $size ) && preg_match( '/\.svg$/i', $icon ) ) {
				if ( ( $xml = simplexml_load_file( $icon ) ) !== false ) {
					$attributes = $xml->attributes();

					if ( ! empty( $attributes ) && isset( $attributes->viewBox ) ) {
						$viewbox = explode( ' ', $attributes->viewBox );

						$size = array();
						$size[0] = isset( $attributes->width ) && preg_match( '/\d+/', $attributes->width, $value ) ? (int) $value[0] : ( count( $viewbox ) == 4 ? (int) trim( $viewbox[2] ) : 0 );
						$size[1] = isset( $attributes->height ) && preg_match( '/\d+/', $attributes->height, $value ) ? (int) $value[0] : ( count( $viewbox ) == 4 ? (int) trim( $viewbox[3] ) : 0 );
					}
				}
			}

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
		$osm_extra = '';

		if ( self::active_map() == 'auto' && ! self::lazy_load_map() ) {
			$plugin_url = geodir_plugin_url();

			ob_start();
?>
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
<?php
			do_action( 'geodir_maps_extra_script' );

			$osm_extra = ob_get_clean();
		}

		return $osm_extra;
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
		return apply_filters( 'geodir_google_api_key', $key, $query );
	}

	/**
	 * Returns the Google Geocoding API key.
	 *
	 * @since   2.0.0.64
	 * @param bool $query If this is for a query and if so add the key=.
	 * @package GeoDirectory
	 * @return string Returns the Geocoding api key.
	 */
	public static function google_geocode_api_key( $query = false ) {
		$key = geodir_get_option( 'google_geocode_api_key' );

		if ( empty( $key ) ) {
			$key = self::google_api_key();
		}

		if ( $key && $query ) {
			$key = "&key=" . $key;
		}

		/**
		 * Filter Google Geocoding API key.
		 *
		 * @since 2.0.0.64
		 *
		 * @param string $key Google Geocoding API key.
		 */
		return apply_filters( 'geodir_google_geocode_api_key', $key, $query );
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
	 * @param string $terms Optional. Terms.
	 * @param bool $hierarchical Whether to include terms that have non-empty descendants (even if $hide_empty is set to true). Default false.
	 * @param string $tick_terms Tick/untick terms. Optional.
	 * @return string|void
	 */
	public static function get_categories_filter( $post_type, $cat_parent = 0, $hide_empty = true, $padding = 0, $map_canvas = '', $child_collapse, $terms = '', $hierarchical = false, $tick_terms = '' ) {
		global $cat_count, $geodir_cat_icons;

		$taxonomy = $post_type . 'category';

		$exclude_categories = geodir_get_option( 'exclude_cat_on_map', array() );
		//$exclude_categories = array(70);
		$exclude_categories = !empty($exclude_categories[$taxonomy]) && is_array($exclude_categories[$taxonomy]) ? array_unique($exclude_categories[$taxonomy]) : array();
		$exclude_categories[$taxonomy] = "70";
		$exclude_cat_str = implode(',', $exclude_categories);
		// terms include/exclude
		$include = array();
		$exclude = array();

		if ( $terms !== false && $terms !== true && $terms != '' ) {
			$terms_array = explode( ",", $terms );
			foreach( $terms_array as $term_id ) {
				$tmp_id = trim( $term_id );
				if ( $tmp_id == '' ) {
					continue;
				}
				if ( abs( $tmp_id ) != $tmp_id ) {
					$exclude[] = absint( $tmp_id );
				} else {
					$include[] = absint( $tmp_id );
				}
			}
		}

		$_tick_terms = array();
		$_untick_terms = array();
		// Tick/untick terms
		if ( ! empty( $tick_terms ) ) {
			$tick_terms_arr = explode( ',', $tick_terms );
			foreach( $tick_terms_arr as $term_id ) {
				$tmp_id = trim( $term_id );
				if ( $tmp_id == '' ) {
					continue;
				}

				if ( geodir_term_post_type( absint( $tmp_id ) ) != $post_type ) {
					continue; // Bail for other CPT
				}

				if ( abs( $tmp_id ) != $tmp_id ) {
					$_untick_terms[] = absint( $tmp_id );
				} else {
					$_tick_terms[] = absint( $tmp_id );
				}
			}
		}

		/**
		 * Untick categories on the map.
		 *
		 * @since 2.0.0.68
		 */
		$_tick_terms = apply_filters( 'geodir_map_categories_tick_terms', $_tick_terms, $post_type, $cat_parent );

		/**
		 * Tick categories on the map.
		 *
		 * @since 2.0.0.68
		 */
		$_untick_terms = apply_filters( 'geodir_map_categories_untick_terms', $_untick_terms, $post_type, $cat_parent );

		$term_args = array(
			'taxonomy' => array( $taxonomy ),
			'parent' => $cat_parent,
		    //'exclude' => $exclude_cat_str,
			'hide_empty ' => $hide_empty
		);

		if(!empty($include)){
			$term_args['include'] = $include;
		}

		if(!empty($exclude)){
			$term_args['exclude'] = $exclude;
		}

		/**
		 * Filter terms order by field.
		 *
		 * @since 2.0.0.67
		 */
		$orderby = apply_filters( 'geodir_map_categories_orderby', '', $post_type, $cat_parent, $hierarchical );
		if ( ! empty( $orderby ) ) {
			$term_args['orderby'] = $orderby;
		}

		/**
		 * Filter terms in ascending or descending order.
		 *
		 * @since 2.0.0.67
		 */
		$order = apply_filters( 'geodir_map_categories_order', '', $post_type, $cat_parent, $hierarchical );
		if ( ! empty( $order ) ) {
			$term_args['order'] = $order;
		}

		$cat_terms = get_terms( $term_args );
		
		if ($hide_empty && ! $hierarchical) {
			$cat_terms = geodir_filter_empty_terms($cat_terms);
		}

		$main_list_class = '';
		$design_style = geodir_design_style();
		$ul_class = $design_style ? ' list-unstyled p-0 m-0' : '';
		$li_class = $design_style ? ' list-unstyled p-0 m-0 ' : '';
		//If there are terms, start displaying
		if ( count( $cat_terms ) > 0 ) {
			//Displaying as a list
			$p = $padding * 15;
			$padding++;

			if ($cat_parent == 0) {
				$list_class = 'main_list geodir-map-terms';
				$li_class = $design_style ? ' list-unstyled p-0 m-0 ' : '';
				$display = '';
			} else {
				$list_class = 'sub_list';
				$li_class = $design_style ? ' list-unstyled p-0 m-0 ml-2 ' : '';
				$display = !$child_collapse ? '' : 'display:none';
			}

			$out = '<ul class="treeview ' . $list_class . $ul_class .'" style="margin-left:' . $p . 'px;' . $display . ';">';

			$geodir_cat_icons = geodir_get_term_icon();

			foreach ( $cat_terms as $cat_term ) {
				$icon = !empty( $geodir_cat_icons ) && isset( $geodir_cat_icons[ $cat_term->term_id ] ) ? $geodir_cat_icons[ $cat_term->term_id ] : '';

				if ( ! in_array( $cat_term->term_id, $exclude ) ) {
					//Secret sauce.  Function calls itself to display child elements, if any
					$checked = true;
					if ( empty( $_tick_terms ) && empty( $_untick_terms ) ) {
						// Tick all
					} elseif ( ! empty( $_tick_terms ) && empty( $_untick_terms ) ) {
						if ( ! in_array( $cat_term->term_id, $_tick_terms ) ) {
							$checked = false; // Untick
						}
					} elseif ( empty( $_tick_terms ) && ! empty( $_untick_terms ) ) {
						if ( in_array( $cat_term->term_id, $_untick_terms ) ) {
							$checked = false; // Untick
						}
					} else {
						if ( ! in_array( $cat_term->term_id, $_tick_terms ) || in_array( $cat_term->term_id, $_untick_terms ) ) {
							$checked = false; // Untick
						}
					}

					/**
					 * Tick category on the map.
					 *
					 * @since 2.0.0.68
					 */
					$checked = apply_filters( 'geodir_map_categories_tick_term', $checked, $cat_term->term_id );

					$checked = $checked !== false ? 'checked="checked"' : '';

					$term_check = '<input type="checkbox" ' . $checked . ' id="' .$map_canvas.'_tick_cat_'. $cat_term->term_id . '" class="group_selector ' . $main_list_class . '"';
					$term_check .= ' name="' . $map_canvas . '_cat[]" ';
					$term_check .= '  title="' . esc_attr(geodir_utf8_ucfirst($cat_term->name)) . '" value="' . $cat_term->term_id . '" onclick="javascript:build_map_ajax_search_param(\'' . $map_canvas . '\',false, this)">';

					if($design_style){
						$term_img = '<img class="w-auto mr-1 ml-n1 rounded-circle" style="height:22px;" alt="' . $cat_term->taxonomy . '" src="' . $icon . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '"/>';

						$term_html = '<li class="'.$li_class.'">' .aui()->input(
							array(
								'id'                => "{$map_canvas}_tick_cat_{$cat_term->term_id}",
								'name'              => "{$map_canvas}_cat[]",
								'type'              => "checkbox",
								'value'             => absint( $cat_term->term_id),
								'label'             => $term_img . esc_attr(geodir_utf8_ucfirst($cat_term->name)),
								'class'             => 'group_selector ' . $main_list_class,
								'label_class'       => 'text-light',
								'checked'           => $checked,
								'no_wrap'            => true,
								'extra_attributes'  => array(
									'onclick' => 'javascript:build_map_ajax_search_param(\'' . $map_canvas . '\',false, this)',
								),
							)
						);
					}else{
						$term_img = '<img height="15" width="15" alt="' . $cat_term->taxonomy . '" src="' . $icon . '" title="' . geodir_utf8_ucfirst($cat_term->name) . '"/>';

						$term_html = '<li class="'.$li_class.'">' . $term_check . '<label for="' . $map_canvas.'_tick_cat_'. $cat_term->term_id . '">' . $term_img . geodir_utf8_ucfirst($cat_term->name) . '</label><span class="gd-map-cat-toggle"><i class="fas fa-long-arrow-alt-down" aria-hidden="true" style="display:none"></i></span>';
					}

					$out .= $term_html;
				}

				// get sub category by recursion
				$out .= self::get_categories_filter( $post_type, $cat_term->term_id, $hide_empty, $padding, $map_canvas, $child_collapse, $terms, false, $tick_terms );

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

	/**
	 * Get the map load type.
	 *
	 * @since 2.1.0.0
	 *
	 * @return null|string The map load type.
	 */
	public static function lazy_load_map() {
		$lazy_load = geodir_get_option( 'maps_lazy_load', '' );

		if ( ! in_array( $lazy_load, array( 'auto', 'click' ) ) ) {
			$lazy_load = '';
		}

		if ( is_admin() && ! wp_doing_ajax() ) {
			$lazy_load = '';
		}

		/**
		 * Filter the map map load type
		 *
		 * @since 2.1.0.0
		 *
		 * @param null|string $lazy_load The map load type.
		 */
		return apply_filters( 'geodir_lazy_load_map', $lazy_load );
	}

	/**
	 * Array of map parameters.
	 *
	 * @since 2.1.0.0
	 *
	 * @return array Map params array.
	 */
	public static function get_map_params() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$language = self::map_language();
		$version_tag = '?ver=' . GEODIRECTORY_VERSION;

		// Google Maps API
		$google_api_key = self::google_api_key();
		
		$aui = geodir_design_style() ? '/aui' : '';

		$map_params = array(
			'api' => self::active_map(),
			'lazyLoad' => self::lazy_load_map(),
			'language' => $language,
			'lazyLoadButton' => '<div class="btn btn-light text-center mx-auto align-self-center shadow-lg c-pointer">' . __( 'Load Map', 'geodirectory' ) . '</div>',
			'lazyLoadPlaceholder' => '<svg width="650px" height="406px" viewBox="0 0 650 406" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="width: 100%; max-width: 100%; height: auto; position: absolute; z-index: -1; filter: blur(8px);"> <defs> <polygon id="path-1" points="0.111 405.228 649.111 405.228 649.111 0.228 0.111 0.228"></polygon> </defs> <g id="web-layouts" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"> <g id="Desktop-HD" transform="translate(-488.000000, -376.000000)"> <g id="Group-21" transform="translate(488.000000, 376.000000)"> <mask id="mask-2" fill="white"> <use xlink:href="#path-1"></use> </mask> <use id="Fill-1" fill="#DDDDDD" xlink:href="#path-1"></use> <polygon id="Stroke-2" mask="url(#mask-2)" points="0.111 405.228 649.111 405.228 649.111 0.228 0.111 0.228"></polygon> <polygon id="Fill-3" fill="#FFFFFF" mask="url(#mask-2)" points="334.3684 -23.1909 376.0764 -14.7999 287.9624 423.1491 246.2554 414.7581"></polygon> <polygon id="Fill-4" fill="#FFFFFF" mask="url(#mask-2)" points="421.886 88.4926 463.594 96.8846 399.962 413.1486 358.255 404.7586"></polygon> <polygon id="Fill-5" fill="#FFFFFF" mask="url(#mask-2)" points="-71 348.6271 684.352 387.8891 685.524 365.3491 -69.828 326.0881"></polygon> <polygon id="Fill-6" fill="#FFFFFF" mask="url(#mask-2)" points="199 97.6279 954.352 136.8899 955.524 114.3489 200.172 75.0879"></polygon> <g id="Group-20" mask="url(#mask-2)"> <g transform="translate(-52.000000, -472.772000)"> <path d="M263.9641,474.1958 C265.3221,473.4858 266.6761,472.6648 268.0241,471.9908 C309.3261,450.8878 345.7931,441.3998 375.9201,434.9858 C382.9011,433.5588 389.3361,432.5078 396.0721,431.2498 L584.0181,214.2588 L617.9741,243.6698 L669.7681,122.2158 L721.5321,0.7718 L608.7091,69.3298 L495.8571,137.8988 L529.7891,167.2888 L263.9641,474.1958 Z M817.4721,250.1488 L869.2261,128.6958 L756.4131,197.2628 L643.5951,265.8618 L677.5391,295.2618 C655.9261,320.1698 620.1461,361.4788 610.0031,373.1898 C579.5741,407.3928 553.8781,424.8828 522.6171,439.1698 C476.0021,460.4448 420.9781,465.4028 361.6461,477.4678 C331.7841,483.8778 300.2151,492.4898 265.4391,510.2568 C230.8541,527.7098 192.4861,556.4178 158.5621,595.8868 L10.8721,766.4018 L65.1011,813.3708 L212.7901,642.8568 C233.5681,619.3558 250.8701,608.0778 279.2731,596.8588 C321.3751,580.3298 377.2961,577.0958 441.3181,561.2858 C504.3591,546.0518 586.2971,509.5588 663.7061,420.7428 L731.7341,342.2018 L765.6781,371.6028 L817.4721,250.1488 Z" id="Fill-7" fill="#9ACEE6"></path> <polygon id="Fill-10" fill="#FFFFFF" points="821.5236 717.1211 820.3516 739.6611 65.0006 700.3991 66.1716 677.8601"></polygon> <path d="M666.1105,748 C722.4435,748 768.1105,795.458 768.1105,854 C768.1105,912.542 722.4435,960 666.1105,960 C609.7775,960 564.1105,912.542 564.1105,854 C564.1105,795.458 609.7775,748 666.1105,748" id="Fill-12" fill="#A0D58A"></path> <path d="M656.6105,815 C682.2925,815 703.1105,838.281 703.1105,867 C703.1105,895.719 682.2925,919 656.6105,919 C630.9285,919 610.1105,895.719 610.1105,867 C610.1105,838.281 630.9285,815 656.6105,815" id="Fill-14" fill="#9ACEE6"></path> <polygon id="Fill-16" fill="#FFFFFF" points="265.3689 454.5811 307.0759 462.9721 218.9629 900.9211 177.2549 892.5301"></polygon> <path d="M102.1105,392 C158.4435,392 204.1105,439.458 204.1105,498 C204.1105,556.542 158.4435,604 102.1105,604 C45.7775,604 0.1105,556.542 0.1105,498 C0.1105,439.458 45.7775,392 102.1105,392" id="Fill-18" fill="#A0D58A"></path> </g> </g> </g> </g> </g> </svg>',
			'apis' => array(
				'google' => apply_filters( 'geodir_map_api_google_data',
					array(
						'key' => $google_api_key,
						'scripts' => array(
							array( 
								'id' => 'geodir-google-maps-script',
								'src' => 'https://maps.googleapis.com/maps/api/js?key=' . $google_api_key . '&libraries=places&language=' . $language . '&ver=' . GEODIRECTORY_VERSION,
								'main' => true,
								'onLoad' => true,
								'onError' => true,
							),
							array( 
								'id' => 'geodir-gomap-script',
								'src' => geodir_plugin_url() . '/assets/js/goMap' . $suffix . '.js' . $version_tag,
							),
							array( 
								'id' => 'geodir-g-overlappingmarker-script',
								'src' => geodir_plugin_url() . '/assets/jawj/oms' . $suffix . '.js' . $version_tag,
								'check' => ! geodir_is_page( 'add-listing' )
							),
							array( 
								'id' => 'geodir-map-widget-script',
								'src' => geodir_plugin_url() . '/assets'.$aui.'/js/map' . $suffix . '.js' . $version_tag,
							)
						)
					)
				),
				'osm' => apply_filters( 'geodir_map_api_osm_data',
					array(
						'styles' => array(
							array( 
								'id' => 'geodir-leaflet-css',
								'src' => geodir_plugin_url() . '/assets/leaflet/leaflet.css' . $version_tag
							),
							array( 
								'id' => 'geodir-leaflet-routing-machine-css',
								'src' => geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine.css',
								'check' => ! geodir_is_page( 'add-listing' )
							),
						),
						'scripts' => array(
							array( 
								'id' => 'geodir-leaflet-script',
								'src' => geodir_plugin_url() . '/assets/leaflet/leaflet' . $suffix . '.js' . $version_tag,
								'main' => true,
							),
							array( 
								'id' => 'geodir-leaflet-geo-script',
								'src' => geodir_plugin_url() . '/assets/leaflet/osm.geocode' . $suffix . '.js' . $version_tag
							),
							array( 
								'id' => 'leaflet-routing-machine-script',
								'src' => geodir_plugin_url() . '/assets/leaflet/routing/leaflet-routing-machine' . $suffix . '.js' . $version_tag,
								'check' => ! geodir_is_page( 'add-listing' )
							),
							array( 
								'id' => 'geodir-o-overlappingmarker-script',
								'src' => geodir_plugin_url() . '/assets/jawj/oms-leaflet' . $suffix . '.js' . $version_tag,
								'check' => ! geodir_is_page( 'add-listing' )
							),
							array( 
								'id' => 'geodir-gomap-script',
								'src' => geodir_plugin_url() . '/assets/js/goMap' . $suffix . '.js' . $version_tag,
							),
							array( 
								'id' => 'geodir-map-widget-script',
								'src' => geodir_plugin_url() . '/assets'.$aui.'/js/map' . $suffix . '.js' . $version_tag,
							)
						)
					)
				)
			)
		);

		/**
		 * Filters the map parameters.
		 *
		 * @since 2.1.0.0
		 *
		 * @param array Map params array.
		 */
		return apply_filters( 'geodir_map_params', $map_params );
	}
}

return new GeoDir_Maps();

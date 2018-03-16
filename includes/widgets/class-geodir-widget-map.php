<?php
/**
 * GeoDirectory Map page Widget
 *
 * This will display map.
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

class GeoDir_Widget_Map extends WP_Super_Duper {

    public $arguments;
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    	=> GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    	=> 'location-alt',
            'block-category'	=> 'common',
            'block-keywords'	=> "['geo','google','map']",
            'block-output'  	=> array(
                'element::img'   => array(
                    'src' 			=> geodir_plugin_url()."/assets/images/block-placeholder-map.png",
                    'alt' 			=> __('Placeholder','geodirectory'),
                    'width' 		=> '[%width%]',
                    'height' 		=> '[%height%]',
					'geodirectory' 	=> true,
					'gd_show_pages' => array(),
                )
            ),
            'class_name'    	=> __CLASS__,
            'base_id'       	=> 'gd_widget_map', 									// this us used as the widget id and the shortcode id.
            'name'          	=> __('GD > Map','geodirectory'), 						// the name of the widget.
            'widget_ops'    	=> array(
                'classname'   => 'geodir-wgt-map', 										// widget class
                'description' => esc_html__('Displays the map.','geodirectory'), 		// widget description
            ),
            'arguments'     		=> array(
                'width'  			=> array(
                    'type' 			=> 'text',
                    'title' 		=> __('Width:', 'geodirectory'),
                    'desc' 			=> __('This is the width of the map, you can use % or px here.', 'geodirectory'),
                    'placeholder' 	=> '100%',
                    'desc_tip' 		=> true,
                    'default'  		=> '100%',
                    'advanced' 		=> false
                ),
                'height'  => array(
                    'type' 			=> 'text',
                    'title' 		=> __('Height:', 'geodirectory'),
                    'desc' 			=> __('This is the height of the map, you can use %, px or vh here.', 'geodirectory'),
                    'placeholder' 	=> '425px',
                    'desc_tip' 		=> true,
                    'default'  		=> '425px',
                    'advanced' 		=> false
                ),
                'maptype'  => array(
                    'type' 			=> 'select',
                    'title' 		=> __('Map type:', 'geodirectory'),
                    'desc' 			=> __('This is the type of map view that will be used by default.', 'geodirectory'),
                    'options'   	=>  array(
                        "ROADMAP" 	=> __('Road Map', 'geodirectory'),
                        "SATELLITE" => __('Satellite Map', 'geodirectory'),
                        "HYBRID" 	=> __('Hybrid Map', 'geodirectory'),
                        "TERRAIN" 	=> __('Terrain Map', 'geodirectory'),
                    ),
                    'placeholder' => '425px',
                    'desc_tip' => true,
                    'default'  => 'ROADMAP',
                    'advanced' => false
                ),
                'zoom'  => array(
                    'type' => 'select',
                    'title' => __('Zoom level:', 'geodirectory'),
                    'desc' => __('This is the zoom level of the map, `auto` is recommended.', 'geodirectory'),
					'options'   =>  array_merge(array('0' => __('Auto', 'geodirectory')), array_reverse( range(1, 19) )),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'marker_cluster'  => array(
                    'type' => 'checkbox',
                    'title' => __('Enable marker cluster?', 'geodirectory'),
                    'desc' => '',
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'child_collapse'  => array(
                    'type' => 'checkbox',
                    'title' => __('Collapse sub categories:', 'geodirectory'),
                    'desc' => __('This will hide the sub-categories under the parent, requiring a click to show.', 'geodirectory'),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'enable_cat_filters'  => array(
					'type' => 'checkbox',
                    'title' => __('Enable category filter?', 'geodirectory'),
                    'desc' => __('This enables categories filter on map.', 'geodirectory'),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'post_type_filter'  => array(
					'type' => 'checkbox',
                    'title' => __('Enable post type filter?', 'geodirectory'),
                    'desc' => __('This enables post type filter on map.', 'geodirectory'),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                ),
                'enable_text_search'  => array(
					'type' => 'checkbox',
                    'title' => __('Enable search filter?', 'geodirectory'),
                    'desc' => __('This enables search filter on map.', 'geodirectory'),
                    'placeholder' => '',
                    'desc_tip' => true,
                    'default'  => '0',
                    'advanced' => false
                )
            )
        );

        parent::__construct( $options );
    }


    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        /**
         * @var string $width
         * @var string $height
         * @var string $maptype
         * @var string $zoom
         * @var bool $child_collapse
         */
        extract($args, EXTR_SKIP);

        //print_r($args);echo '####';exit;

        $widget_id = isset($widget_args['widget_id']) ? $widget_args['widget_id'] : 'shortcode';

        $map_args = array();
        $map_args['map_canvas_name'] = str_replace('-', '_', $widget_id); //'home_map_canvas'.$str ;
        $map_args['width'] = $width;
        $map_args['height'] = $height;
        $map_args['maptype'] = $maptype;
        $map_args['fullscreenControl'] = false;
        $map_args['zoom'] = is_int($zoom) ? $zoom : 7;
        $map_args['autozoom'] = is_int($zoom) ? 0 : 1;
        $map_args['child_collapse'] = $child_collapse;
        $map_args['enable_cat_filters'] = isset($enable_cat_filters) ? $enable_cat_filters : false;
        $map_args['enable_text_search'] = isset($enable_text_search) ? $enable_text_search : false;
        $map_args['post_type_filter'] = isset($post_type_filter) ? $post_type_filter : false;
        /** This action is documented in geodirectory_shortcodes.php */
        $map_args['location_filter'] = apply_filters('geodir_home_map_enable_location_filters', false);
        $map_args['jason_on_load'] = false;
        $map_args['marker_cluster'] = false;
        $map_args['map_resize_button'] = true;
        $map_args['map_class_name'] = 'geodir-map-home-page';

		$content = $this->display_map( $map_args );

        return $content;
        
    }

	public static function display_map( $map_args ) {
		add_action('wp_footer', array(__CLASS__, 'add_script'), 100);

		global $map_canvas_arr;
		$map_canvas_name = (!empty($map_args) && $map_args['map_canvas_name'] != '') ? $map_args['map_canvas_name'] : 'home_map_canvas';
		$map_class_name = (!empty($map_args) && isset($map_args['map_class_name'])) ? $map_args['map_class_name'] : '';

		$default_location = geodir_get_default_location();

		$map_default_lat = isset($default_location->city_latitude) ? $default_location->city_latitude : '';
		$map_default_lng = isset($default_location->city_longitude) ? $default_location->city_longitude : '';
		$map_default_zoom = 12;
		// map options default values
		$width = 950;
		$height = 450;
		$child_collapse = '0';
		$sticky = '';
		$enable_cat_filters = false;
		$enable_text_search = false;
		$post_type_filter = false;
		$location_filter = false;
		$jason_on_load = false;
		$enable_map_direction = false;
		$marker_cluster = false;
		$map_resize_button = false;
		$maptype = 'ROADMAP';

		$geodir_map_options = array(
			'width' => $width,
			'height' => $height,
			'child_collapse' => $child_collapse,
			'sticky' => $sticky,
			'map_resize_button' => $map_resize_button,
			'enable_cat_filters' => $enable_cat_filters,
			'enable_text_search' => $enable_text_search,
			'post_type_filter' => $post_type_filter,
			'location_filter' => $location_filter,
			'jason_on_load' => $jason_on_load,
			'enable_map_direction' => $enable_map_direction,
			'marker_cluster' => $marker_cluster,
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'map_canvas_name' => $map_canvas_name,
			'inputText' => __('Title or Keyword', 'geodirectory'),
			'latitude' => $map_default_lat,
			'longitude' => $map_default_lng,
			'zoom' => $map_default_zoom,
			'scrollwheel' => true,
			'streetViewControl' => true,
			'fullscreenControl' => false,
			'maptype' => $maptype,
			'showPreview' => '0',
			'maxZoom' => 21,
			'autozoom' => true,
			'bubble_size' => 'small',
			'token' => '68f48005e256696074e1da9bf9f67f06',
			'navigationControlOptions' => array('position' => 'TOP_LEFT', 'style' => 'ZOOM_PAN'),
			'map_ajax_url' => geodir_rest_markers_url()
		);
		// Terms.
		$terms = ! empty( $map_args['terms'] ) ? $map_args['terms'] : '';
		$terms = is_array( $terms ) ? implode( ',', $terms ) : $terms;

		// Posts.
		$posts = ! empty( $map_args['posts'] ) ? $map_args['posts'] : '';
		$posts = is_array( $posts ) ? implode( ',', $posts ) : $posts;

		if (!empty($map_args)) {
			foreach ($map_args as $map_option_key => $map_option_value) {
				$geodir_map_options[$map_option_key] = $map_option_value;
			}
		}

		if (strpos($geodir_map_options['height'], '%') !== false || strpos($geodir_map_options['height'], 'px') !== false || strpos($geodir_map_options['height'], 'vh') !== false) {
		} else {
			$geodir_map_options['height'] = $geodir_map_options['height'] . 'px';
		}

		if (strpos($geodir_map_options['width'], '%') !== false || strpos($geodir_map_options['width'], 'px') !== false) {
		} else {
			$geodir_map_options['width'] = $geodir_map_options['width'] . 'px';
		}

		/**
		 * Filter the options to use in google map.
		 *
		 * @since 1.0.0
		 *
		 * @param array $geodir_map_options {@see geodir_draw_map()} docblock.
		 */
		$geodir_map_options = apply_filters("geodir_map_options_{$map_canvas_name}", $geodir_map_options);

		$map_canvas_arr[$map_canvas_name] = array();

		/**
		 * Filter the post types to display data on map.
		 *
		 * @since 1.0.0
		 *
		 * @param object $object Objects of post types.
		 */
		$post_types = apply_filters("geodir_map_post_type_list_{$map_canvas_name}", geodir_get_posttypes('object'));

		$exclude_post_types = geodir_get_option('geodir_exclude_post_type_on_map');
		$exclude_post_types = is_array( $exclude_post_types ) ? $exclude_post_types : array();
		/**
		 * Filter the post types to exclude to display data on map.
		 *
		 * @since 1.0.0
		 *
		 * @param array Array of post types to exclude to display data on map.
		 */
		$exclude_post_types = apply_filters("geodir_exclude_post_type_on_map_{$map_canvas_name}", $exclude_post_types);

		if (count((array)$post_types) != count($exclude_post_types) || ($jason_on_load)):
			// Set default map options

			wp_enqueue_script('geodir-map-widget', geodir_plugin_url() . '/includes/maps/js/map.js',array(),false,true); // @TODO change to map.min.js

			wp_localize_script('geodir-map-widget', $map_canvas_name, $geodir_map_options);

			if ($map_canvas_name == 'detail_page_map_canvas' || $map_canvas_name == 'preview_map_canvas') {
				$map_width = '100%';
			} else {
				$map_width = $geodir_map_options['width'];
			}

			/**
			 * Filter the width of map.
			 *
			 * @since 1.0.0
			 *
			 * @param int $map_width Width of map box, eg: gd_place.
			 */
			$map_width = apply_filters('geodir_change_map_width', $map_width);
			?>
			<div id="catcher_<?php echo $map_canvas_name;?>"></div>
			<div class="stick_trigger_container">
				<div class="trigger_sticky triggeroff_sticky"></div>
				<div class="top_banner_section geodir_map_container <?php echo $map_class_name;?>"
					 id="sticky_map_<?php echo $map_canvas_name;?>"
					 style="min-height:<?php echo $geodir_map_options['height'];?>;width:<?php echo $map_width;?>;">

					<div class="map_background">
						<div class="top_banner_section_in clearfix">
							<div class="<?php echo $map_canvas_name;?>_TopLeft TopLeft"><span class="triggermap" id="<?php echo $map_canvas_name;?>_triggermap" <?php if (!$geodir_map_options['map_resize_button']) { ?> <?php }?>><i class="fa fa-arrows-alt"></i></span></div>
							<div class="<?php echo $map_canvas_name;?>_TopRight TopRight"></div>
							<div id="<?php echo $map_canvas_name;?>_wrapper" class="main_map_wrapper"
								 style="height:<?php echo $geodir_map_options['height'];?>;width:<?php echo $map_width;?>;">
								<!-- new map start -->
								<div class="iprelative">
									<div class="geodir_marker_cluster" id="<?php echo $map_canvas_name;?>"
										 style="height:<?php echo $geodir_map_options['height'];?>;width:<?php echo $map_width;?>;"></div>
									<div id="<?php echo $map_canvas_name;?>_loading_div" class="loading_div"
										 style=" height:<?php echo $geodir_map_options['height'];?>;width:<?php echo $map_width;?>;"></div>
									<!--<div id="home_map_counter"></div>        -->
									<div id="<?php echo $map_canvas_name;?>_map_nofound"
										 class="advmap_nofound"><?php echo MAP_NO_RESULTS; ?></div>
									<div id="<?php echo $map_canvas_name;?>_map_notloaded"
										 class="advmap_notloaded"><?php _e('<h3>Google Map Not Loaded</h3><p>Sorry, unable to load Google Maps API.', 'geodirectory'); ?></div>
								</div>
								<!-- new map end -->
							</div>
							<div class="<?php echo $map_canvas_name;?>_BottomLeft BottomLeft"></div>
						</div>
					</div>
					<?php if ($geodir_map_options['jason_on_load']) { ?>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_jason_enabled" value="1"/>
					<?php } else {
						?>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_jason_enabled" value="0"/>
					<?php }

					if (!$geodir_map_options['enable_text_search'] && !$geodir_map_options['enable_cat_filters'])
						$show_entire_cat_panel = "none";
					else
						$show_entire_cat_panel = "''";
					?>

					<?php if ($geodir_map_options['enable_map_direction']) { ?>
						<div class="gd-input-group gd-get-directions">
						  <div class="gd-input-group-addon gd-directions-left">
							<div class="gd-input-group">
								  <input type="text" id="<?php echo $map_canvas_name; ?>_fromAddress" name="from" class="gd-form-control textfield" value="<?php echo ENTER_LOCATION_TEXT; ?>" onblur="if (this.value == '') {this.value = '<?php echo ENTER_LOCATION_TEXT; ?>';}" onfocus="if (this.value == '<?php echo ENTER_LOCATION_TEXT; ?>') {this.value = '';}" />
								  <div id="<?php echo $map_canvas_name; ?>_mylocation" class="gd-input-group-addon gd-map-mylocation" onclick="gdMyGeoDirection();" title="<?php echo esc_attr__('My location', 'geodirectory'); ?>"><i class="fa fa-crosshairs fa-fw"></i></div>
							</div>
						  </div>
						  <div class="gd-input-group-addon gd-directions-right gd-mylocation-go"><input type="button" value="<?php _e('Get Directions', 'geodirectory'); ?>" class="<?php echo $map_canvas_name; ?>_getdirection" id="directions" onclick="calcRoute('<?php echo $map_canvas_name; ?>')" /></div>
						</div>
						<script>
							<?php if(geodir_is_page('detail')){?>
							jQuery(function () {
								gd_initialize_ac();
							});
							<?php }?>

							function gd_initialize_ac() {
								if (window.gdMaps == 'google') {
									// Create the autocomplete object, restricting the search
									// to geographical location types.
									autocomplete = new google.maps.places.Autocomplete(
										/** @type {HTMLInputElement} */(document.getElementById('<?php echo $map_canvas_name;?>_fromAddress')),
										{types: ['geocode']});
									// When the user selects an address from the dropdown,
									// populate the address fields in the form.
									google.maps.event.addListener(autocomplete, 'place_changed', function () {
										gd_fillInAddress_ac();
									});
								} else {
									jQuery('#<?php echo $map_canvas_name; ?>_fromAddress').hide();
									jQuery('.gd-get-directions').hide();
									jQuery('.<?php echo $map_canvas_name; ?>_getdirection').hide();
									
									if (window.gdMaps == 'osm') {
										window.setTimeout(function() {
											calcRoute('<?php echo $map_canvas_name;?>');
										}, 1000);
									}
								}
							}

							function gd_fillInAddress_ac() {
								//submit the form
								jQuery('#directions').trigger('click');
							}

						</script>


						<div id='directions-options' class="hidden">
							<select id="travel-mode" onchange="calcRoute('<?php echo $map_canvas_name; ?>')">
								<option value="driving"><?php _e('Driving', 'geodirectory'); ?></option>
								<option value="walking"><?php _e('Walking', 'geodirectory'); ?></option>
								<option value="bicycling"><?php _e('Bicycling', 'geodirectory'); ?></option>
								<option value="transit"><?php _e('Public Transport', 'geodirectory'); ?></option>
							</select>

							<select id="travel-units" onchange="calcRoute('<?php echo $map_canvas_name; ?>')">
								<option value="miles"><?php _e('Miles', 'geodirectory'); ?></option>
								<option <?php if (geodir_get_option('search_distance_long') == 'km') {
									echo 'selected="selected"';
								} ?> value="kilometers"><?php _e('Kilometers', 'geodirectory'); ?></option>
							</select>
						</div>

						<div id="<?php echo $map_canvas_name; ?>_directionsPanel" style="width:auto;"></div>
					<?php 
					}
					
					$geodir_default_map_search_pt = geodir_get_option('geodir_default_map_search_pt');
					if (empty($geodir_default_map_search_pt))
						$geodir_default_map_search_pt = 'gd_place';

					global $gd_session;
					$homemap_catlist_ptype = $gd_session->get('homemap_catlist_ptype');

					if ($homemap_catlist_ptype) {
						$geodir_default_map_search_pt = $homemap_catlist_ptype;
					}

					/**
					 * Filter the post type to retrieve data for map
					 *
					 * @since 1.0.0
					 *
					 * @param string $geodir_default_map_search_pt Post type, eg: gd_place.
					 */
					$map_search_pt = apply_filters('geodir_default_map_search_pt', $geodir_default_map_search_pt);
					?>
					<div class="map-category-listing-main" style="display:<?php echo $show_entire_cat_panel;?>">
						<?php
						$exclude_post_types = geodir_get_option('geodir_exclude_post_type_on_map');
						$geodir_available_pt_on_map = count(geodir_get_posttypes('array')) - count($exclude_post_types);
						$map_cat_class = '';
						if ($geodir_map_options['post_type_filter']) {
							$map_cat_class = $geodir_available_pt_on_map > 1 ? ' map-cat-ptypes' : ' map-cat-floor';
						}
						?>
						<div
							class="map-category-listing<?php echo $map_cat_class;?>">
							<div class="gd-trigger gd-triggeroff"><i class="fa fa-compress"></i><i class="fa fa-expand"></i></div>
							<div id="<?php echo $map_canvas_name;?>_cat"
								 class="<?php echo $map_canvas_name;?>_map_category  map_category"
								 <?php if ($child_collapse){ ?>checked="checked" <?php }?>
								 style="max-height:<?php echo $geodir_map_options['height'];?>;">
								<input
									onkeydown="if(event.keyCode == 13){build_map_ajax_search_param('<?php echo $map_canvas_name; ?>', false)}"
									type="text"
									class="inputbox <?php echo($geodir_map_options['enable_text_search'] ? '' : 'geodir-hide'); ?>"
									id="<?php echo $map_canvas_name; ?>_search_string" name="search"
									placeholder="<?php _e('Title', 'geodirectory'); ?>"/>
								<?php if ($geodir_map_options['enable_cat_filters']) { ?>
									<?php if ($geodir_map_options['child_collapse']) { $child_collapse = "1"; ?>
										<input type="hidden" id="<?php echo $map_canvas_name; ?>_child_collapse" value="1"/>
									<?php } else {$child_collapse = "0";
										?>
										<input type="hidden" id="<?php echo $map_canvas_name;?>_child_collapse" value="0"/>
									<?php } ?>
									<input type="hidden" id="<?php echo $map_canvas_name; ?>_cat_enabled" value="1"/>
									<div class="geodir_toggle">
										<?php echo home_map_taxonomy_walker(array($map_search_pt.'category'),0,true,0,$map_canvas_name,$child_collapse,true); ?>
										<script>jQuery( document ).ready(function() {
												geodir_show_sub_cat_collapse_button();
											});</script>
									</div>
								<?php } else { // end of cat filter ?>
									<input type="hidden" id="<?php echo $map_canvas_name; ?>_cat_enabled" value="0"/>
									<input type="hidden" id="<?php echo $map_canvas_name; ?>_child_collapse" value="0"/>
								<?php }?>
								<div class="BottomRight"></div>

							</div>
						</div>
					</div>
					<!-- map-category-listings-->

					<?php
					if ($geodir_map_options['location_filter']) {
						$country = get_query_var('gd_country');
						$region = get_query_var('gd_region');
						$city = get_query_var('gd_city');
						$gd_neighbourhood = get_query_var('gd_neighbourhood');
						
						//fix for location/me page
						$country = $country != 'me' ? $country : '';
						$region = $region != 'me' ? $region : '';
						$city = $country != 'me' ? $city : '';
						$gd_neighbourhood = $country != 'me' ? $gd_neighbourhood : '';
						?>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_location_enabled" value="1"/>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_country" name="gd_country"
							   value="<?php echo $country;?>"/>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_region" name="gd_region"
							   value="<?php echo $region;?>"/>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_city" name="gd_city"
							   value="<?php echo $city;?>"/>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_neighbourhood" name="gd_neighbourhood"
							   value="<?php echo $gd_neighbourhood;?>"/>
					<?php } else { //end of location filter
						?>
						<input type="hidden" id="<?php echo $map_canvas_name;?>_location_enabled" value="0"/>
					<?php }?>

					<input type="hidden" id="<?php echo $map_canvas_name;?>_posttype" name="gd_posttype" value="<?php echo $map_search_pt;?>"/>
					<?php if ( ! empty( $terms ) ) { ?>
					<input type="hidden" name="terms" value="<?php echo $terms; ?>"/>
					<?php } ?>
					<?php if ( ! empty( $posts ) ) { ?>
					<input type="hidden" name="posts" value="<?php echo $posts; ?>"/>
					<?php } ?>
					<input type="hidden" name="limitstart" value=""/>



					<?php if ($geodir_map_options['post_type_filter']) {
						$post_types = geodir_get_posttypes('object');
						$all_post_types = geodir_get_posttypes('names');
						$exclude_post_types = geodir_get_option('geodir_exclude_post_type_on_map');
						if (is_array($exclude_post_types)) {
							$map_post_types = array_diff($all_post_types, $exclude_post_types);
						} else {
							$map_post_types = $all_post_types;
						}
						if (count($map_post_types) > 1) {
							?>
							<div class="map-places-listing" id="<?php echo $map_canvas_name;?>_posttype_menu"
								 style="max-width:<?php echo $map_width;?>!important;">

								<?php if (isset($geodir_map_options['is_geodir_home_map_widget']) && $map_args['is_geodir_home_map_widget']) { ?>
								<div class="geodir-map-posttype-list"><?php } ?>
									<ul class="clearfix place-list">
										<?php


										foreach ($post_types as $post_type => $args) {
											if (!in_array($post_type, $exclude_post_types)) {
												$class = $map_search_pt == $post_type ? 'class="gd-map-search-pt"' : '';
												echo '<li id="' . $post_type . '" ' . $class . '><a href="javascript:void(0);" onclick="jQuery(\'#' . $map_canvas_name . '_posttype\').val(\'' . $post_type . '\');build_map_ajax_search_param(\'' . $map_canvas_name . '\', true)">' . __($args->labels->name, 'geodirectory') . '</a></li>';
											}
										}
										?>
									</ul>
									<?php if (isset($geodir_map_options['is_geodir_home_map_widget']) && $map_args['is_geodir_home_map_widget']) { ?>
								</div><?php } ?>
								<div class="geodir-map-navigation">
									<ul>
										<li class="geodir-leftarrow"><a href="#"><i class="fa fa-chevron-left"></i></a></li>
										<li class="geodir-rightarrow"><a href="#"><i class="fa fa-chevron-right"></i></a>
										</li>
									</ul>
								</div>

							</div> <!-- map-places-listings-->
						<?php }
					} // end of post type filter if
					?>

				</div>
			</div> <!--end of stick trigger container-->
			<script type="text/javascript">

				jQuery(document).ready(function () {
					//initMap('<?php echo $map_canvas_name;?>'); // depreciated, no need to load this twice
					build_map_ajax_search_param('<?php echo $map_canvas_name;?>', false);
					map_sticky('<?php echo $map_canvas_name;?>');
				});

			</script>
			<?php

			if (strpos($geodir_map_options['height'], 'vh')) {
				?>
				<script>
					(function () {
						var screenH = jQuery(window).height();
						var heightVH = "<?php echo str_replace("vh", "", $geodir_map_options['height']);?>";

						var ptypeH = '';
						if (jQuery("#<?php echo $map_canvas_name;?>_posttype_menu").length) {
							ptypeH = jQuery("#<?php echo $map_canvas_name;?>_posttype_menu").outerHeight();
						}

						jQuery("#sticky_map_<?php echo $map_canvas_name;?>").css("min-height", screenH * (heightVH / 100) + 'px');
						jQuery("#<?php echo $map_canvas_name;?>_wrapper").height(screenH * (heightVH / 100) + 'px');
						jQuery("#<?php echo $map_canvas_name;?>").height(screenH * (heightVH / 100) + 'px');
						jQuery("#<?php echo $map_canvas_name;?>_loading_div").height(screenH * (heightVH / 100) + 'px');
						jQuery("#<?php echo $map_canvas_name;?>_cat").css("max-height", (screenH * (heightVH / 100)) - ptypeH + 'px');

					}());
				</script>

			<?php

			} elseif (strpos($geodir_map_options['height'], 'px')) {
				?>
				<script>
					(function () {
						var screenH = jQuery(window).height();
						var heightVH = "<?php echo str_replace("px", "", $geodir_map_options['height']);?>";
						var ptypeH = '';
						if (jQuery("#<?php echo $map_canvas_name;?>_posttype_menu").length) {
							ptypeH = jQuery("#<?php echo $map_canvas_name;?>_posttype_menu").outerHeight();
						}

						jQuery("#<?php echo $map_canvas_name;?>_cat").css("max-height", heightVH - ptypeH + 'px');

					}());
				</script>
			<?php
			}

			/**
			 * Action that runs after all the map code has been output;
			 *
			 * @since 1.5.3
			 *
			 * @param array $geodir_map_options Array of map settings.
			 * @param string $map_canvas_name The canvas name and ID for the map.
			 */
			do_action('geodir_map_after_render',$geodir_map_options,$map_canvas_name);
			endif; // Exclude posttypes if end
	}

    /**
     * Adds the javascript in the footer for home page map widget.
     *
     * @since 2.0.0
     */
    public static function add_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                geoDirMapSlide();
                jQuery(window).resize(function () {
                    jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                        jQuery(this).find('.geodir-map-posttype-list').css({'width': 'auto'});
                        jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left': '0px'});
                        geoDirMapPrepare(this);
                    });
                });
            });
            function geoDirMapPrepare($thisMap) {
                var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
                var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
                var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
                var ptw1 = parseFloat($objMpList.outerWidth(true));
                $objMpList.css({'margin-left': wArrL + 'px'});
                $objMpList.attr('data-width', ptw1);
                ptw1 = ptw1 - (wArrL + wArrR);
                $objMpList.width(ptw1);
                var ptw = $objPlList.width();
                var ptw2 = 0;
                $objPlList.find('li').each(function () {
                    var ptw21 = jQuery(this).outerWidth(true);
                    ptw2 += parseFloat(ptw21);
                });
                var doMov = parseFloat(ptw * 0.75);
                ptw2 = ptw2 + ( ptw2 * 0.05 );
                var maxMargin = ptw2 - ptw;
                $objPlList.attr('data-domov', doMov);
                $objPlList.attr('data-maxMargin', maxMargin);
            }
            function geoDirMapSlide() {
                jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
                    var $thisMap = this;
                    geoDirMapPrepare($thisMap);
                    var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
                    jQuery($thisMap).find('.geodir-leftarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        if (cm == 0 || maxMargin < 0) {
                            return;
                        }
                        domargin = cm + doMov;
                        if (domargin > 0) {
                            domargin = 0;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                    jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
                        e.preventDefault();
                        var cm = $objPlList.css('margin-left');
                        var doMov = parseFloat($objPlList.attr('data-domov'));
                        var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
                        cm = parseFloat(cm);
                        domargin = cm - doMov;
                        if (cm == ( maxMargin * -1 ) || maxMargin < 0) {
                            return;
                        }
                        if (( domargin * -1 ) > maxMargin) {
                            domargin = maxMargin * -1;
                        }
                        $objPlList.animate({'margin-left': domargin + 'px'}, 1000);
                    });
                });
            }
        </script>
        <?php
    }
}
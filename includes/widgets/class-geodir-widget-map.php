<?php
/**
 * GeoDirectory Widget: GeoDir_Widget_Map class
 *
 * @package GeoDirectory
 *
 * @since 2.0.0
 */

/**
 * Core class used to implement a Map widget.
 *
 * @since 2.0.0
 *
 */
class GeoDir_Widget_Map extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'location-alt',
			'block-category' => 'common',
			'block-keywords' => "['geo','google','map']",
			'block-output'   => array(
				array(
					'element' => 'img',
					'title'   => __( 'Placeholder map', 'geodirectory' ),
					'src'     => geodir_plugin_url() . "/assets/images/block-placeholder-map.png",
					'alt'     => __( 'Placeholder', 'geodirectory' ),
					'width'   => '[%width%]',
					'height'  => '[%height%]'
				)
			),
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_map',
			// this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Map', 'geodirectory' ),
			// the name of the widget.
			'widget_ops'     => array(
				'classname'     => 'geodir-wgt-map',                                        // widget class
				'description'   => esc_html__( 'Displays the map.', 'geodirectory' ),        // widget description
				'geodirectory'  => true,
				'gd_show_pages' => array(),
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Adds the javascript in the footer for map widget.
	 *
	 * @since 2.0.0
	 */
	public static function add_script() {
		global $gd_map_widget_script;
		if ( ! empty( $gd_map_widget_script ) ) {
			return;
		}
		$gd_map_widget_script = true;
		?>
		<script type="text/javascript">
			if (!window.gdWidgetMap) {
				window.gdWidgetMap = true;
				jQuery(document).ready(function () {
					geoDirMapSlide();
					jQuery(window).resize(function () {
						jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
							jQuery(this).find('.geodir-map-posttype-list').css({
								'width': 'auto'
							});
							jQuery(this).find('.map-places-listing ul.place-list').css({
								'margin-left': '0px'
							});
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
					$objMpList.css({
						'margin-left': wArrL + 'px'
					});
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
					ptw2 = ptw2 + (ptw2 * 0.05);
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
							$objPlList.animate({
								'margin-left': domargin + 'px'
							}, 1000);
						});
						jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
							e.preventDefault();
							var cm = $objPlList.css('margin-left');
							var doMov = parseFloat($objPlList.attr('data-domov'));
							var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
							cm = parseFloat(cm);
							domargin = cm - doMov;
							if (cm == (maxMargin * -1) || maxMargin < 0) {
								return;
							}
							if ((domargin * -1) > maxMargin) {
								domargin = maxMargin * -1;
							}
							$objPlList.animate({
								'margin-left': domargin + 'px'
							}, 1000);
						});
					});
				}
			}
		</script>
		<?php
	}

	public static function custom_content( $map_options ) {
		$map_canvas = $map_options['map_canvas'];
		if ( ! empty( $map_options['map_directions'] ) ) {
			$distance_unit = geodir_get_option( 'search_distance_long' );
			?>
			<div class="geodir-map-directions-wrap">
				<div class="gd-input-group gd-get-directions">
					<div class="gd-input-group-addon gd-directions-left">
						<div class="gd-input-group">
							<input type="text" id="<?php echo $map_canvas; ?>_fromAddress" name="from"
							       class="gd-form-control textfield" value=""
							       placeholder="<?php esc_attr_e( 'Enter your location', 'geodirectory' ); ?>"/>
							<div id="<?php echo $map_canvas; ?>_mylocation"
							     class="gd-input-group-addon gd-map-mylocation"
							     onclick="gdMyGeoDirection('<?php echo $map_canvas; ?>');"
							     title="<?php echo esc_attr__( 'My location', 'geodirectory' ); ?>"><i
									class="fa fa-crosshairs fa-fw"></i></div>
						</div>
					</div>
					<div class="gd-input-group-addon gd-directions-right gd-mylocation-go"><input type="button"
					                                                                              value="<?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?>"
					                                                                              class="gd-map-get-directions <?php echo $map_canvas; ?>_getdirection"
					                                                                              id="directions"
					                                                                              onclick="geodirFindRoute('<?php echo $map_canvas; ?>')"/>
					</div>
				</div>
				<div id='directions-options' class="hidden">
					<select id="travel-mode" onchange="geodirFindRoute('<?php echo $map_canvas; ?>')">
						<option value="driving"><?php _e( 'Driving', 'geodirectory' ); ?></option>
						<option value="walking"><?php _e( 'Walking', 'geodirectory' ); ?></option>
						<option value="bicycling"><?php _e( 'Bicycling', 'geodirectory' ); ?></option>
						<option value="transit"><?php _e( 'Public Transport', 'geodirectory' ); ?></option>
					</select>
					<select id="travel-units" onchange="geodirFindRoute('<?php echo $map_canvas; ?>')">
						<option
							value="miles" <?php selected( 'miles' == $distance_unit, true ); ?>><?php _e( 'Miles', 'geodirectory' ); ?></option>
						<option
							value="kilometers" <?php selected( 'km' == $distance_unit, true ); ?>><?php _e( 'Kilometers', 'geodirectory' ); ?></option>
					</select>
				</div>
				<div id="<?php echo $map_canvas; ?>_directionsPanel" style="width:auto;"></div>
			</div>
			<?php
		}

		if ( ! empty( $map_options['cat_filter'] ) || ! empty( $map_options['search_filter'] ) ) {
			$exclude_post_types = array();
			$cpts_on_map        = count( geodir_get_posttypes( 'array' ) ) - count( $exclude_post_types );
			$cat_filter_class   = '';
			if ( ! empty( $map_options['post_type_filter'] ) ) {
				$cat_filter_class = $cpts_on_map > 1 ? ' gd-map-cat-ptypes' : ' gd-map-cat-floor';
			}
			?>
			<!-- START cat_filter/search_filter -->
			<div class="map-category-listing-main geodir-map-cat-filter-wrap">
				<div class="map-category-listing<?php echo $cat_filter_class; ?>">
					<div class="gd-trigger gd-triggeroff"><i class="fa fa-compress"></i><i class="fa fa-expand"></i>
					</div>
					<div id="<?php echo $map_canvas; ?>_cat"
					     class="<?php echo $map_canvas; ?>_map_category map_category" <?php checked( ! empty( $map_options['child_collapse'] ), true ); ?>
					     style="max-height:<?php echo $map_options['height']; ?>;">
						<input
							onkeydown="if(event.keyCode == 13){build_map_ajax_search_param('<?php echo $map_canvas; ?>', false);}"
							type="text"
							class="inputbox <?php echo( $map_options['search_filter'] ? '' : 'geodir-hide' ); ?>"
							id="<?php echo $map_canvas; ?>_search_string"
							name="search"
							placeholder="<?php esc_attr_e( 'Title', 'geodirectory' ); ?>
							"/>
						<?php if ( ! empty( $map_options['cat_filter'] ) ) { ?>
							<input type="hidden" id="<?php echo $map_canvas; ?>_child_collapse"
							       value="<?php echo absint( $map_options['child_collapse'] ); ?>"/>
							<input type="hidden" id="<?php echo $map_canvas; ?>_cat_enabled" value="1"/>
							<div class="geodir_toggle">
								<?php echo geodir_map_taxonomy_walker( array( $map_options['post_type'] . 'category' ), 0, true, 0, $map_canvas, absint( $map_options['child_collapse'] ), true ); ?>
								<script type="text/javascript">jQuery(function () {
										geodir_show_sub_cat_collapse_button('<?php echo $map_canvas; ?>');
									});</script>
							</div>
						<?php } else { ?>
							<input type="hidden" id="<?php echo $map_canvas; ?>_cat_enabled" value="0"/>
							<input type="hidden" id="<?php echo $map_canvas; ?>_child_collapse" value="0"/>
						<?php } ?>
						<div class="BottomRight"></div>
					</div>
				</div>
			</div><!-- END cat_filter/search_filter -->
			<?php
		}

		if ( ! empty( $map_options['post_type_filter'] ) ) {
			$map_post_types = self::map_post_types();
			if ( ! empty( $map_post_types ) && count( array_keys( $map_post_types ) ) > 1 ) {
				?>
				<!-- START post_type_filter -->
				<div
					class="map-places-listing  geodir-post-type-filter-wrap"" id="<?php echo $map_canvas; ?>_posttype_menu" style="max-width:<?php echo $map_width; ?>!important;">
				<div class="geodir-map-posttype-list">
					<ul class="clearfix place-list">
						<?php
						foreach ( $map_post_types as $cpt => $cpt_name ) {
							$class = $map_options['post_type'] == $cpt ? 'class="gd-map-search-pt"' : '';
							?>
							<li id="<?php echo $cpt . ' ' . $class; ?>><a href="
							    javascript:void(0);" onclick="jQuery('#<?php echo $map_canvas; ?>_posttype').val('<?php echo $cpt; ?>');build_map_ajax_search_param('<?php echo $map_canvas; ?>', true)"><?php echo $cpt_name; ?></a></li>
						<?php } ?>
					</ul>
				</div>
				<div class="geodir-map-navigation">
					<ul>
						<li class="geodir-leftarrow"><a href="#"><i class="fa fa-chevron-left"></i></a></li>
						<li class="geodir-rightarrow"><a href="#"><i class="fa fa-chevron-right"></i></a>
						</li>
					</ul>
				</div>
				</div><!-- END post_type_filter -->
				<?php
			}
		}
	}

	public static function map_post_types() {
		$map_post_types     = geodir_get_posttypes( 'options-plural' );
		$exclude_post_types = geodir_get_option( 'geodir_exclude_post_type_on_map' );
		if ( ! empty( $exclude_post_types ) && is_array( $exclude_post_types ) ) {
			foreach ( $exclude_post_types as $post_type ) {
				if ( ! empty( $map_post_types ) && isset( $map_post_types[ $post_type ] ) ) {
					unset( $map_post_types[ $post_type ] );
				}
			}
		}

		return $map_post_types;
	}

	public static function custom_script( $map_options ) {
		$map_canvas = $map_options['map_canvas'];
		?>
		<script type="text/javascript">
			jQuery(function ($) {
				var gdMapCanvas = '<?php echo $map_canvas; ?>';
				build_map_ajax_search_param(gdMapCanvas, false);
				<?php if ( ! empty( $map_options['sticky'] ) ) { ?>
				map_sticky(gdMapCanvas);
				<?php } ?>
				<?php if ( ! empty( $map_options['map_directions'] ) ) { ?>
				geodir_map_directions_init(gdMapCanvas);
				<?php } ?>
				<?php if ( strpos( $map_options['height'], 'vh' ) !== false ) { $height = str_replace( 'vh', '', $map_options['height'] ); ?>
				var screenH, heightVH, ptypeH = 0;
				screenH = $(window).height();
				heightVH = parseFloat('<?php echo $height; ?>');
				if ($("#" + gdMapCanvas + "_posttype_menu").length) {
					ptypeH = $("#" + gdMapCanvas + "_posttype_menu").outerHeight();
				}
				$("#sticky_map_" + gdMapCanvas).css("min-height", screenH * (heightVH / 100) + 'px');
				$("#" + gdMapCanvas + "_wrapper").height(screenH * (heightVH / 100) + 'px');
				$("#" + gdMapCanvas).height(screenH * (heightVH / 100) + 'px');
				$("#" + gdMapCanvas + "_loading_div").height(screenH * (heightVH / 100) + 'px');
				$("#" + gdMapCanvas + "_cat").css("max-height", (screenH * (heightVH / 100)) - ptypeH + 'px');
				<?php } elseif ( strpos( $map_options['height'], 'px' ) !== false ) { $height = str_replace( 'px', '', $map_options['height'] ); ?>
				var screenH, heightVH, ptypeH = 0;
				screenH = $(window).height();
				heightVH = parseFloat('<?php echo $height; ?>');
				if ($("#" + gdMapCanvas + "_posttype_menu").length) {
					ptypeH = $("#" + gdMapCanvas + "_posttype_menu").outerHeight();
				}
				$("#" + gdMapCanvas + "_cat").css("max-height", heightVH - ptypeH + 'px');
				<?php } ?>
			});
		</script>
		<?php
	}

	/*
	 * Display the map.
	 *
	 */

	public function set_arguments() {
		$arguments = array(
			'title'            => array(
				'type'     => 'text',
				'title'    => __( 'Title:', 'geodirectory' ),
				'desc'     => __( 'The widget title.', 'geodirectory' ),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'width'            => array(
				'type'        => 'text',
				'title'       => __( 'Width:', 'geodirectory' ),
				'desc'        => __( 'This is the width of the map, you can use % or px here.', 'geodirectory' ),
				'placeholder' => '100%',
				'desc_tip'    => true,
				'default'     => '100%',
				'advanced'    => false
			),
			'height'           => array(
				'type'        => 'text',
				'title'       => __( 'Height:', 'geodirectory' ),
				'desc'        => __( 'This is the height of the map, you can use %, px or vh here.', 'geodirectory' ),
				'placeholder' => '425px',
				'desc_tip'    => true,
				'default'     => '425px',
				'advanced'    => false
			),
			'maptype'          => array(
				'type'     => 'select',
				'title'    => __( 'Mapview:', 'geodirectory' ),
				'desc'     => __( 'This is the type of map view that will be used by default.', 'geodirectory' ),
				'options'  => array(
					"ROADMAP"   => __( 'Road Map', 'geodirectory' ),
					"SATELLITE" => __( 'Satellite Map', 'geodirectory' ),
					"HYBRID"    => __( 'Hybrid Map', 'geodirectory' ),
					"TERRAIN"   => __( 'Terrain Map', 'geodirectory' ),
				),
				'desc_tip' => true,
				'default'  => 'ROADMAP',
				'advanced' => true
			),
			'zoom'             => array(
				'type'        => 'select',
				'title'       => __( 'Zoom level:', 'geodirectory' ),
				'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
				'options'     => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ),  range( 1, 19 )  ),
				'placeholder' => '',
				'desc_tip'    => true,
				'default'     => '0',
				'advanced'    => true
			),
			'map_type'         => array(
				'type'     => 'select',
				'title'    => __( 'Map type:', 'geodirectory' ),
				'desc'     => __( 'Select map type.', 'geodirectory' ),
				'options'  => array(
					"auto"      => __( 'Auto', 'geodirectory' ),
					"directory" => __( 'Directory Map', 'geodirectory' ),
					"archive"   => __( 'Archive Map', 'geodirectory' ),
					"post"      => __( 'Post Map', 'geodirectory' ),
				),
				'desc_tip' => true,
				'default'  => 'auto',
				'advanced' => false
			),
			'post_type'        => array(
				'type'            => 'select',
				'title'           => __( 'Default Post Type:', 'geodirectory' ),
				'desc'            => __( 'The custom post types to show by default.', 'geodirectory' ),
				'options'         => $this->post_type_options(),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%map_type%]=="directory" || [%map_type%]=="archive"',
			),
			'terms'            => array(
				'type'            => 'select',
				'title'           => __( 'Categories:', 'geodirectory' ),
				'desc'            => __( 'Displays the posts on map for selected categories.', 'geodirectory' ),
				'multiple'        => true,
				'options'         => $this->get_categories(),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%map_type%]=="archive"',
			),
			'all_posts'        => array(
				'type'            => 'checkbox',
				'title'           => __( 'Show all posts?', 'geodirectory' ),
				'desc'            => __( 'This displays all posts on map from archive page.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="archive"',
			),
			'post_id'          => array(
				'type'            => 'text',
				'title'           => __( 'Post ID:', 'geodirectory' ),
				'desc'            => __( 'Map post id.', 'geodirectory' ),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'element_require' => '[%map_type%]=="post"',
			),
			'search_filter'    => array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable search filter?', 'geodirectory' ),
				'desc'            => __( 'This enables search filter on map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="directory"',
			),
			'post_type_filter' => array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable post type filter?', 'geodirectory' ),
				'desc'            => __( 'This enables post type filter on map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="directory"',
			),
			'cat_filter'       => array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable category filter?', 'geodirectory' ),
				'desc'            => __( 'This enables categories filter on map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="directory"',
			),
			'child_collapse'   => array(
				'type'            => 'checkbox',
				'title'           => __( 'Collapse sub categories?', 'geodirectory' ),
				'desc'            => __( 'This will hide the sub-categories under the parent, requiring a click to show.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="directory"',
			),
			'map_directions'   => array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable map directions?', 'geodirectory' ),
				'desc'            => __( 'Displays post directions for single post map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => false,
				'element_require' => '[%map_type%]=="post"',
			)
		);
		if ( defined( 'GDCLUSTER_VERSION' ) ) { // @todo we can move this in marker cluster plugin.
			$arguments['marker_cluster'] = array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable marker cluster?', 'geodirectory' ),
				'desc'            => __( 'This enables marker cluster on the map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '0',
				'advanced'        => true,
				'element_require' => '[%map_type%]!="post"',
			);
		}

		return $arguments;
	}

	/*
	 * Display the map.
	 *
	 */

	/**
	 * Get the post type options.
	 *
	 * @return array
	 */
	public function post_type_options() {
		$options = array(
			'' => __( 'Auto', 'geodirectory' )
		);

		$post_types = geodir_get_posttypes( 'options-plural' );

		if ( ! empty( $post_types ) ) {
			$options = array_merge( $options, $post_types );
		}

		return $options;
	}

	public function get_categories( $post_type = 'gd_place' ) {
		$options    = array(
			'0' => __( 'All', 'geodirectory' )
		);
		$post_types = geodir_get_posttypes();

		if ( ! in_array( $post_type, $post_types ) ) {
			$post_type = 'gd_place';
		}

		$terms = get_terms( array( 'taxonomy' => $post_type . 'category', 'orderby' => 'count', 'order' => 'DESC' ) );

		if ( ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$options[ $term->term_id ] = geodir_utf8_ucfirst( $term->name );
			}
		}

		return $options;
	}

	public function post_map( $post, $echo = true ) {
		if ( is_int( $post ) ) {
			$post = geodir_get_post_info( $post );
		}
		if ( empty( $post->ID ) ) {
			return false;
		}

		$args = array(
			'map_canvas'     => 'gd_post_map_canvas_' . $post->ID,
			'map_type'       => 'post',
			'width'          => '100%',
			'height'         => 300,
			'zoom'           => ! empty( $post->mapzoom ) ? $post->mapzoom : 7,
			'post_id'        => $post->ID,
			'post_type'      => $post->post_type,
			'terms'          => array(),
			'posts'          => array(),
			'marker_cluster' => false,
			'map_directions' => true,
		);
		if ( ! empty( $post->mapview ) ) {
			$args['maptype'] = $post->mapview;
		}

		$output = self::output( $args );
		if ( $echo ) {
			echo $output;
		} else {
			return $output;
		}
	}

	/**
	 * Outputs the map widget on the front-end.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $gd_post, $wp_query;


		///print_r( $args );exit;

		$defaults = array(
			'map_type'         => 'auto',
			'width'            => '100%',
			'height'           => '425px',
			'maptype'          => 'ROADMAP',
			'zoom'             => '0',
			'post_type'        => 'gd_place',
			'terms'            => array(),
			'post_id'          => 0,
			'all_posts'        => false,
			'search_filter'    => false,
			'post_type_filter' => true,
			'cat_filter'       => true,
			'child_collapse'   => false,
			'map_directions'   => true,
			'marker_cluster'   => false,
		);

		$map_args = wp_parse_args( $args, $defaults );

		if ( ! in_array( $map_args['map_type'], array( 'auto', 'directory', 'archive', 'post' ) ) ) {
			$map_args['map_type'] = 'auto';
		}
		$map_args['default_map_type'] = $map_args['map_type'];

		if ( $map_args['map_type'] == 'auto' ) {
			if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) || geodir_is_page( 'search' ) ) {
				$map_args['map_type'] = 'archive';
			} elseif ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) {
				$map_args['map_type'] = 'post';
			} else {
				$map_args['map_type'] = 'directory';
			}
		}

		if ( ! empty( $widget_args['widget_id'] ) ) {
			$map_args['map_canvas'] = $widget_args['widget_id'];
		}
		if ( is_array( $map_args['terms'] ) && ! empty( $map_args['terms'] ) && $map_args['terms'][0] == '0' ) {
			$map_args['terms'] = array();
		} else if ( ! is_array( $map_args['terms'] ) && $map_args['terms'] == '0' ) {
			$map_args['terms'] = array();
		}

		$post_type = ! empty( $map_args['post_type'] ) ? $map_args['post_type'] : geodir_get_current_posttype();

		switch ( $map_args['map_type'] ) {
			case 'archive':
				if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) || geodir_is_page( 'search' ) ) {
					if ( empty( $map_args['all_posts'] ) ) {
						$map_args['posts'] = array( '-1' ); // No results

						if ( ! empty( $wp_query ) && $wp_query->is_main_query() ) {
							$map_args['posts'] = array();
							$map_args['terms'] = array();
							if ( ! empty( $wp_query->posts ) ) {
								foreach ( $wp_query->posts as $post ) {
									$map_args['posts'][] = $post->ID;
								}
							}
						}
					} else {
						if ( ! empty( $wp_query ) && $wp_query->is_main_query() ) {
							$map_args['posts'] = array();
							$map_args['terms'] = array();
							if ( ! empty( $wp_query->queried_object ) && ! empty( $wp_query->queried_object->term_id ) ) {
								$map_args['terms'][] = $wp_query->queried_object->term_id;
							} else {
								if ( ! empty( $wp_query->posts ) ) {
									foreach ( $wp_query->posts as $post ) {
										$map_args['posts'][] = $post->ID;
									}
								}
							}
						}
					}
				} else {
					if ( empty( $map_args['terms'] ) ) {
						$map_args['posts'] = array( '-1' ); // No results
					}
				}
				break;
			case 'post':
				if ( $map_args['default_map_type'] == 'post' && ! empty( $map_args['post_id'] ) ) {
					$post_id   = $map_args['post_id'];
					$post_type = get_post_type( $post_id );
				} else if ( ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) && ! empty( $gd_post->ID ) ) {
					$post_id   = $gd_post->ID;
					$post_type = $gd_post->post_type;
				} else {
					$post_id = - 1; // No results.
				}

				$map_args['posts'] = array( $post_id );

				// Default for post map
				$map_args['terms']          = array();
				$map_args['marker_cluster'] = false;
				if ( empty( $map_args['zoom'] ) ) {
					$map_args['zoom'] = 7;
				}
				break;
		}

		if ( empty( $post_type ) ) {
			$post_type = 'gd_place'; // @todo implement multiple for CPT
		}
		$map_args['post_type'] = $post_type;

		// directory map
		if ( $map_args['map_type'] == 'directory' ) {
		} else {
			$map_args['post_type_filter'] = false;
			$map_args['cat_filter']       = false;
			$map_args['search_filter']    = false;
		}

		// archive map
		if ( $map_args['map_type'] == 'archive' ) {
		} else {
			$map_args['all_posts'] = false;
			$map_args['terms']     = array();
		}

		// post map
		if ( $map_args['map_type'] == 'post' ) {
		} else {
			$map_args['map_directions'] = false;
			$map_args['post_id']        = 0;
		}

		// geodir_error_log( $map_args, 'map_args', __FILE__, __LINE__ );
		return self::render_map( $map_args );
	}

	public static function render_map( $map_args ) {
		$defaults = array(
			'map_type'       => 'auto',                    // auto, directory, archive, post
			'map_canvas'     => '',
			'map_class'      => '',
			'width'          => '100%',
			'height'         => '425px',
			'maptype'        => 'ROADMAP',
			'zoom'           => '0',
			'autozoom'       => true,
			'post_type'      => 'gd_place',
			'terms'          => '',
			'posts'          => '',
			'sticky'         => false,
			'map_directions' => false,
			'marker_cluster' => false,
		);

		$params = wp_parse_args( $map_args, $defaults );

		$params['marker_cluster'] = false; // @todo Implement via marker cluster plugin.

		// map type
		if ( ! in_array( $params['map_type'], array( 'auto', 'directory', 'archive', 'post' ) ) ) {
			$params['map_type'] = 'auto';
		}
		// map canvas
		if ( empty( $params['map_canvas'] ) ) {
			$params['map_canvas'] = 'gd_map_canvas_' . $params['map_type'];
		}
		// map class
		if ( ! empty( $params['map_class'] ) ) {
			$params['map_class'] = sanitize_html_class( $params['map_class'] );
		}
		$params['map_canvas'] = sanitize_key( str_replace( '-', '_', sanitize_title( $params['map_canvas'] ) ) );
		// width
		if ( empty( $params['width'] ) ) {
			$params['width'] = '100%';
		}
		if ( strpos( $params['width'], '%' ) === false && strpos( $params['width'], 'px' ) === false ) {
			$params['width'] .= 'px';
		}
		// height
		if ( empty( $params['height'] ) ) {
			$params['height'] = '100%';
		}
		if ( strpos( $params['height'], '%' ) === false && strpos( $params['height'], 'px' ) === false && strpos( $params['height'], 'vh' ) === false ) {
			$params['height'] .= 'px';
		}
		// maptype
		if ( empty( $params['maptype'] ) ) {
			$params['maptype'] = 'ROADMAP';
		}
		// zoom
		$params['zoom'] = absint( $params['zoom'] );
		// autozoom
		if ( $params['zoom'] > 0 ) {
			$params['autozoom'] = false;
		}
		// default latitude, longitude
		if ( empty( $params['default_lat'] ) || empty( $params['default_lng'] ) ) {
			$default_location = geodir_get_default_location();

			$params['default_lat'] = $default_location->latitude;
			$params['default_lng'] = $default_location->longitude;
		}

		// terms
		if ( is_array( $params['terms'] ) ) {
			$params['terms'] = ! empty( $params['terms'] ) ? implode( ',', $params['terms'] ) : '';
		}
		// posts
		if ( is_array( $params['posts'] ) ) {
			$params['posts'] = ! empty( $params['posts'] ) ? implode( ',', $params['posts'] ) : '';
		}

		ob_start();

		self::display_map( $params );

		$content = ob_get_clean();

		return $content;
	}

	public static function display_map( $params ) { //geodir_error_log( $params, 'display_map', __FILE__, __LINE__ );
		global $gd_maps_canvas;
		if ( empty( $gd_maps_canvas ) ) {
			$gd_maps_canvas = array();
		}

		add_action( 'wp_footer', array( __CLASS__, 'add_script' ), 100 );
		add_action( 'geodir_map_custom_content', array( __CLASS__, 'custom_content' ), 10 );
		add_action( 'geodir_map_custom_script', array( __CLASS__, 'custom_script' ), 10 );

		$defaults    = array(
			'scrollwheel'              => true,
			'streetViewControl'        => true,
			'fullscreenControl'        => false,
			'maxZoom'                  => 21,
			'token'                    => '68f48005e256696074e1da9bf9f67f06',
			'_wpnonce' => wp_create_nonce('wp_rest'),
			'navigationControlOptions' => array(
				'position' => 'TOP_LEFT',
				'style'    => 'ZOOM_PAN'
			),
			'map_ajax_url'             => geodir_rest_markers_url()
		);
		$map_options = wp_parse_args( $params, $defaults );

		$map_type   = $map_options['map_type'];
		$map_canvas = $map_options['map_canvas'];
		$width      = $map_options['width'];
		$height     = $map_options['height'];
		$map_class  = 'geodir_map_container gd-map-' . $map_type . 'container';

		$gd_maps_canvas[ $map_options['map_canvas'] ] = $map_options;

		// Set map options to JS
		wp_enqueue_script( 'geodir-map-widget', geodir_plugin_url() . '/includes/maps/js/map.js', array(), false, true ); // @TODO change to map.min.js
		wp_localize_script( 'geodir-map-widget', $map_options['map_canvas'], $map_options );

		?>
		<!--START geodir-map-wrap-->
		<div class="geodir-map-wrap geodir-<?php echo $map_type; ?>-map-wrap">
			<div id="catcher_<?php echo $map_canvas; ?>"></div>
			<!--START stick_trigger_container-->
			<div class="stick_trigger_container">
				<div class="trigger_sticky triggeroff_sticky"></div>
				<!--end of stick trigger container-->
				<div class="top_banner_section geodir_map_container <?php echo $map_canvas; ?>"
				     id="sticky_map_<?php echo $map_canvas; ?>"
				     style="width:<?php echo $width; ?>;min-height:<?php echo $height; ?>;">
					<!--END map_background-->
					<div class="map_background">
						<div class="top_banner_section_in clearfix">
							<div class="<?php echo $map_canvas; ?>_TopLeft TopLeft"><span class="triggermap"
							                                                              id="<?php echo $map_canvas; ?>_triggermap"><i
										class="fa fa-arrows-alt"></i></span></div>
							<div class="<?php echo $map_canvas; ?>_TopRight TopRight"></div>
							<div id="<?php echo $map_canvas; ?>_wrapper" class="main_map_wrapper"
							     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;">
								<div class="iprelative">
									<div id="<?php echo $map_canvas; ?>" class="geodir-map-canvas"
									     data-map-type="<?php echo $map_type; ?>"
									     data-map-canvas="<?php echo $map_canvas; ?>"
									     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;"></div>
									<div id="<?php echo $map_canvas; ?>_loading_div" class="loading_div"
									     style="height:<?php echo $height; ?>;width:<?php echo $width; ?>;"></div>
									<div id="<?php echo $map_canvas; ?>_map_nofound"
									     class="advmap_nofound"><?php echo _e( '<h3>No Records Found</h3><p>Sorry, no records were found. Please adjust your search criteria and try again.</p>', 'geodirectory' ); ?></div>
									<div id="<?php echo $map_canvas; ?>_map_notloaded"
									     class="advmap_notloaded"><?php _e( '<h3>Google Map Not Loaded</h3><p>Sorry, unable to load Google Maps API.', 'geodirectory' ); ?></div>
								</div>
							</div>
							<div class="<?php echo $map_canvas; ?>_BottomLeft BottomLeft"></div>
						</div>
					</div><!--END map_background-->
					<?php do_action( 'geodir_map_custom_content', $map_options ); ?>
				</div>
				<?php do_action( 'geodir_map_custom_script', $map_options ); ?>
			</div><!--END stick_trigger_container-->
		</div><!--END geodir-map-wrap-->
		<?php
	}
}
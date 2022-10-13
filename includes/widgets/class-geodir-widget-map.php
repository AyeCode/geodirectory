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
	 * Sets up the widgets name etc.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'location-alt',
			'block-category' => 'geodirectory',
			'block-keywords' => "['geo','google','map']",
			'block-output'   => array(
				array(
					'element'	=> 'BlocksProps',
					'inner_element' => 'div',
					'class'   => 'bsui',
					'style'   => '{overflow: "hidden",height: \'[%height%]\'}',
					array(
						'element' => 'img',
						'title'   => __( 'Placeholder map', 'geodirectory' ),
						'src'     => geodir_plugin_url() . "/assets/images/block-placeholder-map.png",
						'alt'     => __( 'Placeholder', 'geodirectory' ),
						'width'   => '[%width%]',
						'height'  => '[%height%]',
						'style'   => '{height: "inherit","object-fit": "cover"}',
						'class'   => 'mb-[%mb%] mt-[%mt%] ml-[%ml%] mr-[%mr%] pb-[%pb%] pt-[%pt%] pl-[%pl%] pr-[%pr%] border border-[%border%] [%rounded%] [%shadow%] rounded-[%rounded_size%] bg-[%bg%]',
					)
				)
			),
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_map',                                            // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Map', 'geodirectory' ),                    // the name of the widget.
			'widget_ops'     => array(
				'classname'    => 'geodir-wgt-map '.geodir_bsui_class(),             // widget class
				'description'  => esc_html__( 'Displays the map.', 'geodirectory' ), // widget description
				'geodirectory' => true,
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set map arguments.
	 *
	 * @since 2.0.0
	 *
	 * @return array $arguments.
	 */
	public function set_arguments() {
		$arguments = array();

		$arguments['title'] = array(
			'type'     => 'text',
			'title'    => __( 'Title:', 'geodirectory' ),
			'desc'     => __( 'The widget title.', 'geodirectory' ),
			'default'  => '',
			'desc_tip' => true,
			'advanced' => false
		);
		$arguments['width'] = array(
			'type'        => 'text',
			'title'       => __( 'Width:', 'geodirectory' ),
			'desc'        => __( 'This is the width of the map, you can use % or px here. (static map requires px value)', 'geodirectory' ),
			'placeholder' => '100%',
			'desc_tip'    => true,
			'default'     => '100%',
			'advanced'    => false
		);
		$arguments['height'] = array(
			'type'        => 'text',
			'title'       => __( 'Height:', 'geodirectory' ),
			'desc'        => __( 'This is the height of the map, you can use %, px or vh here. (static map requires px value)', 'geodirectory' ),
			'placeholder' => '425px',
			'desc_tip'    => true,
			'default'     => '425px',
			'advanced'    => false
		);
		$arguments['maptype'] = array(
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
		);
		$arguments['zoom'] = array(
			'type'        => 'select',
			'title'       => __( 'Zoom level:', 'geodirectory' ),
			'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
			'options'     => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ), range( 1, 19 ) ),
			'placeholder' => '',
			'desc_tip'    => true,
			'default'     => '0',
			'advanced'    => true
		);
		$arguments['map_type'] = array(
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
		);
		$arguments['post_settings'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Use post map zoom and type?', 'geodirectory' ),
			'desc'            => __( 'This will use the zoom level and map type set in the post over the settings in the widget.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '1',
			'advanced'        => true,
			'element_require' => '[%map_type%]=="post"',
		);
		$arguments['post_type'] = array(
			'type'            => 'select',
			'title'           => __( 'Default Post Type:', 'geodirectory' ),
			'desc'            => __( 'The custom post type to show by default.', 'geodirectory' ),
			'options'         => $this->post_type_options(),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
		);

		// its best to show a text input for not until Gutenberg can support dynamic selects
		//@todo it would be preferable to use <optgroup> here but Gutenberg does not support it yet: https://github.com/WordPress/gutenberg/issues/8426
		//$post_types = geodir_get_posttypes();
		//if(count($post_types)>1){
		if ( 1 == 1 ) {
			$arguments['terms'] = array(
				'type'            => 'text',
				'title'           => __( 'Category restrictions:', 'geodirectory' ),
				'desc'            => __( 'Enter a comma separated list of category ids (1,2,3) to limit the listing to these categories only, or a negative list (-1,-2,-3) to exclude those categories.', 'geodirectory' ),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'placeholder'     => "1,2,3 (default: empty)",
				'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
			);
			$arguments['tick_terms'] = array(
				'type'            => 'text',
				'title'           => __( 'Tick/Untick Categories on Map:', 'geodirectory' ),
				'desc'            => __( 'Enter a comma separated list of category ids (2,3) to tick by default these categories only, or a negative list (-2,-3) to untick those categories by default on the map. Leave blank to tick all categories by default.', 'geodirectory' ),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'placeholder'     => "2,3 (default: empty)",
				'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
			);
		} else {
//			$arguments['terms'] = array(
//				'type'            => 'select',
//				'title'           => __( 'Categories:', 'geodirectory' ),
//				'desc'            => __( 'Displays the posts on map for selected categories.', 'geodirectory' ),
//				'multiple'        => true,
//				'options'         => $this->get_categories(),
//				'default'         => '',
//				'desc_tip'        => true,
//				'advanced'        => false,
//				'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
//			);
		}

		$arguments['tags'] = array(
			'type' => 'text',
			'title' => __( 'Filter by tags:', 'geodirectory' ),
			'desc' => __( 'Insert separate tags with commas to filter listings by tags. On non GD pages use css .geodir-listings or id(ex: #gd_listings-2) of the listings widget/shortcode to show markers on map.', 'geodirectory' ),
			'default' => '',
			'placeholder' => __( 'garden,dinner,pizza', 'geodirectory' ),
			'desc_tip' => true,
			'advanced' => true,
			'element_require' => '[%map_type%]!="post"',
		);

		$arguments['all_posts'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Show all posts?', 'geodirectory' ),
			'desc'            => __( 'This displays all posts on map from archive page.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="archive"',
		);
		$arguments['post_id'] = array(
			'type'            => 'text',
			'title'           => __( 'Post ID:', 'geodirectory' ),
			'desc'            => __( 'Map post id.', 'geodirectory' ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '[%map_type%]=="post"',
		);
		$arguments['search_filter'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable search filter?', 'geodirectory' ),
			'desc'            => __( 'This enables search filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
		);
		$arguments['post_type_filter'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable post type filter?', 'geodirectory' ),
			'desc'            => __( 'This enables post type filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
		);
		$arguments['cat_filter'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable category filter?', 'geodirectory' ),
			'desc'            => __( 'This enables categories filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
		);
		$arguments['child_collapse'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Collapse sub categories?', 'geodirectory' ),
			'desc'            => __( 'This will hide the sub-categories under the parent, requiring a click to show.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
		);
		$arguments['map_directions'] = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable map directions?', 'geodirectory' ),
			'desc'            => __( 'Displays post directions for single post map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="post"',
		);
		$arguments['scrollwheel'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable mouse scroll zoom?', 'geodirectory' ),
			'desc'        => __( 'Lets the map be scrolled with the mouse scroll wheel.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
		);
		$arguments['hide_zoom_control'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Hide Zoom Control?', 'geodirectory' ),
			'desc'        => __( 'Hide zoom control "+" and "-" buttons for changing the zoom level of the map.', 'geodirectory' ),
			'placeholder' => '',
			'value'       => '1',
			'default'     => '0',
			'desc_tip'    => true,
			'advanced'    => true,
		);
		$arguments['hide_street_control'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Hide Street View Control?', 'geodirectory' ),
			'desc'        => __( 'Hide street view control on the Google map.', 'geodirectory' ),
			'placeholder' => '',
			'value'       => '1',
			'default'     => '0',
			'desc_tip'    => true,
			'advanced'    => true,
		);
		$arguments['sticky'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable sticky map?', 'geodirectory' ),
			'desc'        => __( 'When in the sidebar this will attempt to make it stick when scrolling on desktop.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
		);
		$arguments['static'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable static map?', 'geodirectory' ),
			'desc'        => __( 'FOR POST MAP ONLY When enabled this will try to load a static map image.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
		);

		if ( defined( 'GEODIR_MARKERCLUSTER_VERSION' ) ) {
			$arguments['marker_cluster'] = array(
				'type'            => 'checkbox',
				'title'           => __( 'Enable marker cluster?', 'geodirectory' ),
				'desc'            => __( 'This enables marker cluster on the map.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'value'           => '1',
				'default'         => '1',
				'advanced'        => true,
				'element_require' => '[%map_type%]!="post"',
			);
		}

		$design_style = geodir_design_style();

		if ( $design_style ) {
			// background
			$arguments['bg']  = geodir_get_sd_background_input('mt');

			// margins
			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb',array('value'=>3));
			$arguments['ml']  = geodir_get_sd_margin_input('ml');

			// padding
			$arguments['pt']  = geodir_get_sd_padding_input('pt');
			$arguments['pr']  = geodir_get_sd_padding_input('pr');
			$arguments['pb']  = geodir_get_sd_padding_input('pb');
			$arguments['pl']  = geodir_get_sd_padding_input('pl');

			// border
			$arguments['border']  = geodir_get_sd_border_input('border');
			$arguments['rounded']  = geodir_get_sd_border_input('rounded');
			$arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

			// shadow
			$arguments['shadow']  = geodir_get_sd_shadow_input('shadow');
		}

		return $arguments;
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

		$defaults = array(
			'map_type'         => 'auto',
			'width'            => '100%',
			'height'           => '425px',
			'maptype'          => 'ROADMAP',
			'zoom'             => '0',
			'hide_zoom_control'=> false,
			'hide_street_control'=> false,
			'post_type'        => 'gd_place',
			'terms'            => array(), // can be string or array
			'tick_terms'       => '',
			'tags'             => array(), // can be string or array
			'post_id'          => 0,
			'all_posts'        => false,
			'search_filter'    => false,
			'post_type_filter' => true,
			'cat_filter'       => true,
			'child_collapse'   => false,
			'map_directions'   => true,
//			'marker_cluster'   => false,
			'country'          => '',
			'region'           => '',
			'city'             => '',
			'neighbourhood'    => '',
			'lat'              => '',
			'lon'              => '',
			'dist'             => '',
			'bg'    => '',
			'mt'    => '',
			'mb'    => '3',
			'mr'    => '',
			'ml'    => '',
			'pt'    => '',
			'pb'    => '',
			'pr'    => '',
			'pl'    => '',
			'border' => '',
			'rounded' => '',
			'rounded_size' => '',
			'shadow' => '',
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

						if ( ! empty( $wp_query ) && $wp_query->is_main_query() && ! empty( $wp_query->found_posts ) ) {
							/*
							 * If the map is put before the query then we can't get the info here so we simply pull the IDS from the loop container
							 */
							$map_args['posts'] = 'geodir-loop-container';
							$map_args['terms'] = array();
							$map_args['tags'] = array();
//							if ( ! empty( $wp_query->posts ) ) {
//								foreach ( $wp_query->posts as $post ) {
//									$map_args['posts'][] = $post->ID;
//								}
//							}
						}
					} else {
						if ( ! empty( $wp_query ) && $wp_query->is_main_query() ) {
							$map_args['posts'] = array();
							$map_args['terms'] = array();
							$map_args['tags'] = array();
							if ( ! empty( $wp_query->queried_object ) && ! empty( $wp_query->queried_object->term_id ) ) {
								$queried_object = $wp_query->queried_object;
								if ( ! empty( $queried_object->taxonomy ) && ! empty( $queried_object->name ) && geodir_taxonomy_type( $queried_object->taxonomy ) == 'tag' ) {
									$map_args['tags'][] = $queried_object->name; // Tag
								} else {
									$map_args['terms'][] = $queried_object->term_id; // Category
								}
							} else if ( ! empty( $_REQUEST['spost_category'] ) && geodir_is_page( 'search' ) ) { // Search by category
								if ( is_array( $_REQUEST['spost_category'] ) ) {
									$map_args['terms'] = array_map( 'absint', $_REQUEST['spost_category'] );
								} else {
									$map_args['terms'] = array( absint( $_REQUEST['spost_category'] ) );
								}
							} else {
								if ( geodir_is_page( 'pt' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) || geodir_is_page( 'search' ) ) {
									// if post type page and set to show all then don't add a posts param
								} else {
									if ( ! empty( $wp_query->posts ) ) {
										foreach ( $wp_query->posts as $post ) {
											$map_args['posts'][] = $post->ID;
										}
									} else {
										$map_args['posts'] = array( '-1' ); // No results
									}
								}
							}
						}
					}
				} else {
					if ( empty( $map_args['terms'] ) && empty( $map_args['tags'] ) ) {
						$map_args['posts'] = array( '-1' ); // No results
					}
				}
				break;
			case 'post':
				if ( $map_args['default_map_type'] == 'post' && ! empty( $map_args['post_id'] ) ) {
					$post_id   = $map_args['post_id'];
					$post_type = get_post_type( $post_id );
					if ( $map_args['post_settings'] ) {
						if ( ! empty( $gd_post->mapzoom ) ) {
							$map_args['zoom'] = absint( $gd_post->mapzoom );
						}
						if ( ! empty( $gd_post->mapview ) ) {
							$map_args['maptype'] = esc_attr( $gd_post->mapview );
						}
					}
				} else if ( ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) && ! empty( $gd_post->ID ) ) {
					$post_id   = $gd_post->ID;
					$post_type = $gd_post->post_type != 'revision' ? $gd_post->post_type : get_post_type( wp_get_post_parent_id( $gd_post->ID ) );
					if ( $map_args['post_settings'] ) {
						if ( ! empty( $gd_post->mapzoom ) ) {
							$map_args['zoom'] = absint( $gd_post->mapzoom );
						}
						if ( ! empty( $gd_post->mapview ) ) {
							$map_args['maptype'] = esc_attr( $gd_post->mapview );
						}
					}
				} else {
					$post_id = - 1; // No results.
				}

				$map_args['posts'] = array( $post_id );

				// Default for post map
				$map_args['terms']          = array();
				$map_args['tags']           = array();
				$map_args['marker_cluster'] = false;
				if ( empty( $map_args['zoom'] ) ) {
					$map_args['zoom'] = 12;
				}
				break;
		}

		if ( empty( $post_type ) ) {
			$post_types = self::map_post_types();
			$post_types = ! empty( $post_types ) ? array_keys( $post_types ) : array( 'gd_place' );
			$post_type = $post_types[0]; // @todo implement multiple for CPT
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
		}

		// location
		$current_location          = GeoDir()->location;
		$map_args['country']       = ! empty( $current_location->country_slug ) ? $current_location->country_slug : $map_args['country'];
		$map_args['region']        = ! empty( $current_location->region_slug ) ? $current_location->region_slug : $map_args['region'];
		$map_args['city']          = ! empty( $current_location->city_slug ) ? $current_location->city_slug : $map_args['city'];
		$map_args['neighbourhood'] = ! empty( $current_location->neighbourhood_slug ) ? $current_location->neighbourhood_slug : $map_args['neighbourhood'];
		if ( empty( $map_args['country'] ) && empty( $map_args['region'] ) && empty( $map_args['city'] ) && empty( $map_args['neighbourhood'] ) && ! empty( $current_location->latitude ) ) {
			$map_args['lat'] = ! empty( $current_location->latitude ) ? $current_location->latitude : '';
			$map_args['lon'] = ! empty( $current_location->longitude ) ? $current_location->longitude : '';

			if ( ( GeoDir_Query::get_query_var( 'snear' ) || GeoDir_Query::get_query_var( 'near' ) ) && ( $distance = geodir_sanitize_float( GeoDir_Query::get_query_var( 'dist' ) ) ) ) {
				$map_args['dist'] = $distance;
			}
		} elseif ( geodir_core_multi_city() ) { /* Core multi city */
			$map_args['country']       = '';
			$map_args['region']        = '';
			$map_args['city']          = '';
			$map_args['neighbourhood'] = '';
		}

		// post map
		if ( $map_args['map_type'] == 'post' || ( ! empty( $map_args['tags'] ) && is_scalar( $map_args['tags'] ) && ( strpos( $map_args['tags'], '.' ) === 0 || strpos( $map_args['tags'], '#' ) === 0 ) ) ) {
			$map_args['country']       = '';
			$map_args['region']        = '';
			$map_args['city']          = '';
			$map_args['neighbourhood'] = '';
		} else {
			$map_args['map_directions'] = false;
			$map_args['post_id']        = 0;
		}

		return self::render_map( $map_args );
	}


	/**
	 * Custom Content html.
	 *
	 * @since 2.0.0
	 *
	 * @param array $map_options {
	 *      An array for map options.
	 *
	 * @type string $map_canvas Map canvas value.
	 * @type string $map_directions Map Direction values.
	 * @type string $cat_filter Category filter value.
	 * @type string $search_filter Search filter value.
	 * @type string $post_type_filter Post type filter value.
	 * @type string $post_type Post type.
	 * @type string $child_collapse child collapse value.
	 * }
	 */
	public static function custom_content( $map_options ) {
		$design_style = geodir_design_style();
		$map_post_types = array();
		$map_canvas = $map_options['map_canvas'];

		if ( ! empty( $map_options['map_directions'] ) ) {
			$distance_unit = geodir_get_option( 'search_distance_long' );

			// template output
			$template = $design_style ? $design_style."/map/directions.php" : "legacy/map/directions.php";
			$args = array(
				'map_options'  => $map_options,
				'map_canvas'  => $map_canvas,
				'distance_unit'  => $distance_unit,
			);
			echo geodir_get_template_html( $template, $args );
		}

		if ( ! empty( $map_options['post_type_filter'] ) ) {
			$map_post_types = self::map_post_types( true );
		}

		if ( ! empty( $map_options['cat_filter'] ) || ! empty( $map_options['search_filter'] ) || ( ! empty( $map_options['post_type_filter'] ) && ! empty( $design_style ) ) ) {
			$cat_filter_class   = '';
			if ( ! empty( $map_options['post_type_filter'] ) ) {
				$cpts_on_map        = $map_post_types;
				$cat_filter_class = $cpts_on_map > 1 ? ' gd-map-cat-ptypes' : ' gd-map-cat-floor';
			}

			// template output
			$template = $design_style ? $design_style."/map/filter-tax.php" : "legacy/map/filter-tax.php";
			$args = array(
				'map_options'  => $map_options,
				'map_canvas'  => $map_canvas,
				'cat_filter_class'  => $cat_filter_class,
				'map_post_types'  => $map_post_types,
			);
			echo geodir_get_template_html( $template, $args );
		}

		// old design shows on bottom
		if ( ! empty( $map_options['post_type_filter'] )  && empty($design_style)) {
			if ( ! empty( $map_post_types ) && count( array_keys( $map_post_types ) ) > 1 ) {

				// template output
				$template = "legacy/map/filter-cpt.php";
				$args = array(
					'map_options'  => $map_options,
					'map_canvas'  => $map_canvas,
					'map_post_types'  => $map_post_types,
				);
				echo geodir_get_template_html( $template, $args );

			}
		}
	}

	/**
	 * Get Map post types.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $hide_empty Hide CPT if it has no published posts.
	 *
	 * @return array $map_post_types.
	 */
	public static function map_post_types($hide_empty = false) {
		$post_types = geodir_get_posttypes( 'options-plural' );
		$map_post_types = array();
		if ( ! empty( $post_types ) ) {
			$exclude_post_types = geodir_get_option( 'exclude_post_type_on_map' );

			foreach ( $post_types as $post_type => $name ) {

				if($hide_empty){
					$post_counts = wp_count_posts($post_type, 'readable'); // let WP handle the caching
					if(isset($post_counts->publish) && $post_counts->publish==0){
						continue;
					}
				}

				if ( ! empty( $exclude_post_types ) && is_array( $exclude_post_types ) && in_array( $post_type, $exclude_post_types ) ) {
					continue;
				}

				if ( ! GeoDir_Post_types::supports( $post_type, 'location' ) ) {
					continue;
				}

				$map_post_types[ $post_type ] = $name;
			}
		}

		return apply_filters( 'geodir_map_post_types', $map_post_types );
	}

	/**
	 * Custom scripts.
	 *
	 * @since 2.0.0
	 *
	 * @param array $map_options {
	 *      An array for map options.
	 *
	 * @type string $map_canvas Map canvas value.
	 * @type string $sticky map sticky value.
	 * @type string $map_directions map directions.
	 * @type string $height map height.
	 * }
	 */
	public static function custom_script( $map_options ) {
		$map_canvas = $map_options['map_canvas'];
		$load_terms = ! empty( $map_options['cat_filter'] ) && geodir_lazy_load_map() ? 'true' : 'false';

		// Base map latitude/longitude/zoom.
		$base_latitude = '';
		$base_longitude = '';
		$base_zoom = '';

		if ( geodir_is_page( 'search' ) ) {
			if ( ! empty( $map_options['base_lat'] ) && ! empty( $map_options['base_lon'] ) ) {
				$base_latitude = $map_options['base_lat'];
				$base_longitude = $map_options['base_lon'];
			} else if ( ! empty( $map_options['lat'] ) && ! empty( $map_options['lon'] ) ) {
				$base_latitude = $map_options['lat'];
				$base_longitude = $map_options['lon'];
			} else if ( ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
				$base_latitude = sanitize_text_field( $_REQUEST['sgeo_lat'] );
				$base_longitude = sanitize_text_field( $_REQUEST['sgeo_lon'] );
			} else if ( ! empty( $map_options['default_lat'] ) && ! empty( $map_options['default_lng'] ) ) {
				$base_latitude = $map_options['default_lat'];
				$base_longitude = $map_options['default_lng'];
			}

			if ( ! empty( $map_options['base_zoom'] ) && absint( $map_options['base_zoom'] ) > 0 ) {
				$base_zoom = $map_options['base_zoom'];
			} else if ( ! empty( $map_options['zoom'] ) && absint( $map_options['zoom'] ) > 0 ) {
				$base_zoom = $map_options['zoom'];
			} else if ( ! empty( $map_options['nomap_zoom'] ) && absint( $map_options['nomap_zoom'] ) > 0 ) {
				$base_zoom = $map_options['nomap_zoom'];
			} else {
				$base_zoom = 11;
			}
		}
?>
<style>.geodir_map_container .poi-info-window .full-width{width:180px;position:relative;margin-left:inherit;left:inherit;}</style>
<script type="text/javascript">
window.gdBaseLat = <?php echo geodir_sanitize_float( $base_latitude ); ?>;
window.gdBaseLng = <?php echo geodir_sanitize_float( $base_longitude ); ?>;
window.gdBaseZoom = <?php echo absint( $base_zoom ); ?>;
jQuery(function ($) {
	<?php if ( geodir_lazy_load_map() ) { ?>
	jQuery('#<?php echo $map_canvas; ?>').geodirLoadMap({
		map_canvas: '<?php echo $map_canvas; ?>',
		callback: function() {<?php } ?>
			var gdMapCanvas = '<?php echo $map_canvas; ?>';
			<?php
			if($map_options['map_type']=='post' && $map_options['static']){
				echo 'geodir_build_static_map(gdMapCanvas);';
			}else{
				echo 'build_map_ajax_search_param(gdMapCanvas, ' . $load_terms . ');';
			}
			?>
			<?php if ( ! empty( $map_options['sticky'] ) ) { ?>
			geodir_map_sticky(gdMapCanvas);
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
			<?php } ?><?php if ( geodir_lazy_load_map() ) { ?>
		}
	});<?php } ?>
});
</script>
		<?php
	}

	/**
	 * Get the post type options.
	 *
	 * @since 2.0.0
	 *
	 * @return array $options.
	 */
	public function post_type_options() {
		$options = array(
			'' => __( 'Auto', 'geodirectory' )
		);

		$post_types = self::map_post_types();

		if ( ! empty( $post_types ) ) {
			$options = array_merge( $options, $post_types );
		}

		return $options;
	}

	/**
	 * Get categories.
	 *
	 * @since 2.0.0
	 *
	 * @param string $post_type Optional. Posttype. Default gd_place.
	 *
	 * @return array $options.
	 */
	public function get_categories( $post_type = 'gd_place' ) {
		return geodir_category_options($post_type);
	}

	/**
	 * Get Post map.
	 *
	 * @since 2.0.0
	 *
	 * @param object $post Post object.
	 * @param bool $echo Optional. echo. Default true.
	 *
	 * @return string $output.
	 */
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
			'post_settings'  => '1',
			'post_type'      => $post->post_type,
			'terms'          => array(), // can be string or array
			'tick_terms'     => '',
			'tags'           => array(), // can be string or array
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
	 * Render map.
	 *
	 * @since 2.0.0
	 *
	 * @param array $map_args Map arguments array.
	 *
	 * @return string $content.
	 */
	public static function render_map( $map_args ) {
		global $geodirectory, $gd_post;

		$defaults = array(
			'map_type'       => 'auto',                    // auto, directory, archive, post
			'map_canvas'     => '',
			'map_class'      => '',
			'width'          => '100%',
			'height'         => '425px',
			'maptype'        => 'ROADMAP',
			'hide_zoom_control'   => false,
			'hide_street_control' => false,
			'zoom'           => '0',
			'autozoom'       => true,
			'post_type'      => 'gd_place',
			'terms'          => '',
			'tick_terms'     => '',
			'tags'           => '',
			'posts'          => '',
			'sticky'         => false,
			'static'         => false,
			'map_directions' => false,
			'bg'    => '',
			'mt'    => '',
			'mb'    => '3',
			'mr'    => '',
			'ml'    => '',
			'pt'    => '',
			'pb'    => '',
			'pr'    => '',
			'pl'    => '',
			'border'    => '',
			'rounded'    => '',
			'rounded_size'    => '',
			'shadow'    => '',
		);

		$params = wp_parse_args( $map_args, $defaults );

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
			$params['map_class'] = esc_attr( $params['map_class'] );
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
		// zoomControl
		if ( ! empty( $params['hide_zoom_control'] ) ) {
			$params['zoomControl'] = 0;
		}
		// streetViewControl
		if ( ! empty( $params['hide_street_control'] ) ) {
			$params['streetViewControl'] = 0;
		}
		// zoom
		$params['zoom'] = absint( $params['zoom'] );
		// autozoom
		if ( $params['zoom'] > 0 ) {
			$params['autozoom'] = false;
		}
		// default latitude, longitude
		if ( empty( $params['default_lat'] ) || empty( $params['default_lng'] ) ) {
			$default_location = $geodirectory->location->get_default_location();

			$params['default_lat'] = $default_location->latitude;
			$params['default_lng'] = $default_location->longitude;
		}

		// Set latitude, longitude, zoom for empty results on map.
		if ( $params['map_type'] == 'post' && ! empty( $gd_post ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
			$nomap_lat = $gd_post->latitude;
			$nomap_lng = $gd_post->longitude;
		} elseif ( ! empty( $geodirectory->location ) && ! empty( $geodirectory->location->latitude ) && ! empty( $geodirectory->location->longitude ) ) {
			$nomap_lat = $geodirectory->location->latitude;
			$nomap_lng = $geodirectory->location->longitude;
		} elseif ( ( $_nomap_lat = GeoDir_Query::get_query_var( 'sgeo_lat' ) ) && ( $_nomap_lng = GeoDir_Query::get_query_var( 'sgeo_lon' ) ) ) {
			$nomap_lat = $_nomap_lat;
			$nomap_lng = $_nomap_lng;
		} else {
			$nomap_lat = $params['default_lat'];
			$nomap_lng = $params['default_lng'];
		}
		$params['nomap_lat'] = filter_var( $nomap_lat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$params['nomap_lng'] = filter_var( $nomap_lng, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$params['nomap_zoom'] = absint( $params['zoom'] ) > 0 ? absint( $params['zoom'] ) : 11;

		// terms
		if ( is_array( $params['terms'] ) ) {
			$params['terms'] = ! empty( $params['terms'] ) ? implode( ',', $params['terms'] ) : '';
		}

		// tick/untick terms
		if ( is_array( $params['tick_terms'] ) ) {
			$params['tick_terms'] = ! empty( $params['tick_terms'] ) ? implode( ',', $params['tick_terms'] ) : '';
		}

		// tags
		if ( ! empty( $params['tags'] ) && ! is_array( $params['tags'] ) ) {
			$params['tags'] = explode( ',', $params['tags'] );
			$params['tags'] = array_map( 'trim', $params['tags'] );
		}
		if ( is_array( $params['tags'] ) ) {
			$params['tags'] = ! empty( $params['tags'] ) ? implode( ',', array_unique( array_filter( $params['tags'] ) ) ) : '';
		}
		// posts
		if ( is_array( $params['posts'] ) ) {
			$params['posts'] = ! empty( $params['posts'] ) ? implode( ',', $params['posts'] ) : '';
		}

		$params = apply_filters( 'geodir_map_params', $params, $map_args );

		// add post lat/lon if static post map
		if ( $params['map_type'] == 'post' && $params['static'] ) {
			if ( ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
				$params['latitude'] = filter_var( $gd_post->latitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				$params['longitude'] = filter_var( $gd_post->longitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			}

			// set icon url
			if ( ! empty( $gd_post->default_category ) ) {
				$params['icon_url'] = geodir_get_cat_icon( $gd_post->default_category, true, true );
			}
		}

		// wrap class
		$params['wrap_class'] = geodir_build_aui_class($params);

		ob_start();

		self::display_map( $params );

		$content = ob_get_clean();

		return $content;
	}

	/**
	 * Display Map.
	 *
	 * @since 2.0.0
	 *
	 * @param array $params map arguments array.
	 */
	public static function display_map( $params ) {
		global $gd_maps_canvas, $gd_post;

		if ( empty( $gd_maps_canvas ) ) {
			$gd_maps_canvas = array();
		}

		if ( ! apply_filters( 'geodir_check_display_map', true, $params ) ) {
			return;
		}

		add_action( 'wp_footer', array( __CLASS__, 'add_script' ), 100 );
		add_action( 'geodir_map_custom_content', array( __CLASS__, 'custom_content' ), 10 );
		add_action( 'geodir_map_custom_script', array( __CLASS__, 'custom_script' ), 10 );

		$markers_url = geodir_rest_markers_url();
		$markers_ajax_url = $markers_url;
		$url_params = '';
		if ( defined( 'GEODIR_FAST_AJAX' ) && geodir_get_option( 'fast_ajax' ) ) {
			$markers_ajax_url = add_query_arg( array( 'gd-ajax' => 1 ), $markers_ajax_url );
			$url_params = '&gd-ajax=1';
		}

		$defaults = array(
			'scrollwheel'              => true,
			'streetViewControl'        => true,
			'fullscreenControl'        => false,
			'maxZoom'                  => 21,
			'token'                    => '68f48005e256696074e1da9bf9f67f06',
			'_wpnonce'                 => geodir_create_nonce( 'wp_rest' ),
			'navigationControlOptions' => array(
				'position' => 'TOP_LEFT',
				'style'    => 'ZOOM_PAN'
			),
			'map_ajax_url'             => $markers_url,
			'map_markers_ajax_url'     => $markers_ajax_url,
			'map_terms_ajax_url'       => $markers_ajax_url,
			'map_marker_ajax_url'      => $markers_url,
			'map_marker_url_params'    => $url_params,
			'wrap_class'               => ''
		);
		$map_options = wp_parse_args( $params, $defaults );

		$map_options['map_canvas'] = isset($gd_maps_canvas[ $map_options['map_canvas'] ]) ?  $map_options['map_canvas']  . count($gd_maps_canvas) : $map_options['map_canvas'];

		$map_type   = $map_options['map_type'];
		$map_canvas = $map_options['map_canvas'];
		$width      = $map_options['width'];
		$height     = $map_options['height'];
		$wrap_class = !empty($map_options['wrap_class']) ? $map_options['wrap_class'] : '';
		$map_class  = 'geodir_map_container gd-map-' . $map_type . 'container';

		$gd_maps_canvas[ $map_options['map_canvas'] ] = $map_options;

		$map_canvas_attribs = '';
		if ( $map_options['map_type'] == 'post' && ! empty( $gd_post ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
			$map_canvas_attribs .= ' data-lat="' . esc_attr( $gd_post->latitude ) . '" data-lng="' . esc_attr( $gd_post->longitude ) . '" ';
		}

		// Maps
		if ( geodir_lazy_load_map() ) {
			// Lazy Load
			wp_enqueue_script( 'geodir-map' );
			wp_localize_script( 'geodir-map', $map_options['map_canvas'], $map_options );
		} else {
			wp_enqueue_script( 'geodir-map-widget' );
			wp_localize_script( 'geodir-map-widget', $map_options['map_canvas'], $map_options );
		}

		// template output
		$design_style = geodir_design_style();
		$template = $design_style ? $design_style."/map/map.php" : "legacy/map/map.php";

		$args = array(
			'map_options'  => $map_options,
			'map_type'  => $map_type,
			'map_canvas'  => $map_canvas,
			'height'  => $height,
			'width'  => $width,
			'wrap_class'    => $wrap_class,
			'extra_attribs' => $map_canvas_attribs
		);
		echo geodir_get_template_html( $template, $args );

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
				jQuery(function () {
					geoDirMapSlide();
				});
				jQuery(window).on("resize",function () {
					jQuery('.geodir_map_container .geodir-post-type-filter-wrap').each(function () {
						jQuery(this).find('.geodir-map-posttype-list').css({
							'width': 'auto'
						});
						jQuery(this).find('ul.place-list').css({
							'margin-left': '0px'
						});
						geoDirMapPrepare(this);
					});
				});
				function geoDirMapPrepare($thisMap) {
					var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
					var $objPlList = jQuery($thisMap).find('ul.place-list');
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
					jQuery('.geodir_map_container .geodir-post-type-filter-wrap').each(function () {
						var $thisMap = this;
						geoDirMapPrepare($thisMap);
						var $objPlList = jQuery($thisMap).find('ul.place-list');
						jQuery($thisMap).find('.geodir-leftarrow a').on("click",function (e) {
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
						jQuery($thisMap).find('.geodir-rightarrow a').on("click",function (e) {
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
			<?php if ( geodir_lazy_load_map() == 'auto' ) { ?>
			jQuery(function($) {
				$('[aria-controls="collapseMap"]').addClass('geodir-lazy-map');
				$('.geodir-lazy-map').on('click', function(e) {
					window.dispatchEvent(new Event('resize'));
				});
			});
			<?php } ?>
		</script>
		<?php
	}
}

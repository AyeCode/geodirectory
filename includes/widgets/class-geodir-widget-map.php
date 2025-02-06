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

		$aui_settings = is_admin() ? get_option( 'ayecode-ui-settings', array() ) : array();
		$aui_settings = apply_filters( 'ayecode-ui-settings', $aui_settings, array(), array() );
		$bs5          = ! empty($aui_settings['bs_ver']) && '5' === $aui_settings['bs_ver'];

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'location-alt',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['geo','google','map']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'block-api-version' => 1, // this is needed to make the block selectable in the editor if not using innerBlockProps https://wordpress.stackexchange.com/questions/384004/cant-select-my-block-by-clicking-on-it
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_map',                                            // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Map', 'geodirectory' ),                    // the name of the widget.
			'widget_ops'       => array(
				'classname'    => 'geodir-wgt-map ' . geodir_bsui_class(),             // widget class
				'description'  => esc_html__( 'Displays the map.', 'geodirectory' ), // widget description
				'geodirectory' => true,
			),
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Title', 'geodirectory' ),
						__( 'Map Content', 'geodirectory' ),
						__( 'Map Options', 'geodirectory' ),
					),
					'tab'    => array(
						'title'     => __( 'Content', 'geodirectory' ),
						'key'       => 'bs_tab_content',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'styles'   => array(
					'groups' => array(
						__( 'Map', 'geodirectory' ),
					),
					'tab'    => array(
						'title'     => __( 'Styles', 'geodirectory' ),
						'key'       => 'bs_tab_styles',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
				'advanced' => array(
					'groups' => array(
						__( 'Wrapper Styles', 'geodirectory' ),
						__( 'Advanced', 'geodirectory' ),
					),
					'tab'    => array(
						'title'     => __( 'Advanced', 'geodirectory' ),
						'key'       => 'bs_tab_advanced',
						'tabs_open' => true,
						'open'      => true,
						'class'     => 'text-center flex-fill d-flex justify-content-center',
					),
				),
			),
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
			'advanced' => false,
			'group'    => __( 'Title', 'geodirectory' ),
		);

		$arguments['map_type']      = array(
			'type'     => 'select',
			'title'    => __( 'Map type:', 'geodirectory' ),
			'desc'     => __( 'Select map type.', 'geodirectory' ),
			'options'  => array(
				'auto'      => __( 'Auto', 'geodirectory' ),
				'directory' => __( 'Directory Map', 'geodirectory' ),
				'archive'   => __( 'Archive Map', 'geodirectory' ),
				'post'      => __( 'Post Map', 'geodirectory' ),
			),
			'desc_tip' => true,
			'default'  => 'auto',
			'advanced' => false,
			'group'    => __( 'Map Content', 'geodirectory' ),
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
			'group'           => __( 'Map Content', 'geodirectory' ),
		);
		$arguments['post_type']     = array(
			'type'            => 'select',
			'title'           => __( 'Default Post Type:', 'geodirectory' ),
			'desc'            => __( 'The custom post type to show by default.', 'geodirectory' ),
			'options'         => $this->post_type_options(),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);

		// its best to show a text input for not until Gutenberg can support dynamic selects
		//@todo it would be preferable to use <optgroup> here but Gutenberg does not support it yet: https://github.com/WordPress/gutenberg/issues/8426
		//$post_types = geodir_get_posttypes();
		//if(count($post_types)>1){
		$arguments['terms']      = array(
			'type'            => 'text',
			'title'           => __( 'Category restrictions:', 'geodirectory' ),
			'desc'            => __( 'Enter a comma separated list of category ids (1,2,3) to limit the listing to these categories only, or a negative list (-1,-2,-3) to exclude those categories.', 'geodirectory' ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'placeholder'     => '1,2,3 (default: empty)',
			'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);
		$arguments['tick_terms'] = array(
			'type'            => 'text',
			'title'           => __( 'Tick/Untick Categories on Map:', 'geodirectory' ),
			'desc'            => __( 'Enter a comma separated list of category ids (2,3) to tick by default these categories only, or a negative list (-2,-3) to untick those categories by default on the map. Leave blank to tick all categories by default.', 'geodirectory' ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'placeholder'     => '2,3 (default: empty)',
			'element_require' => '([%map_type%]=="directory" || [%map_type%]=="archive")',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);

		$arguments['tags'] = array(
			'type'            => 'text',
			'title'           => __( 'Filter by tags:', 'geodirectory' ),
			'desc'            => __( 'Insert separate tags with commas to filter listings by tags. On non GD pages use css .geodir-listings or id(ex: #gd_listings-2) of the listings widget/shortcode to show markers on map.', 'geodirectory' ),
			'default'         => '',
			'placeholder'     => __( 'garden,dinner,pizza', 'geodirectory' ),
			'desc_tip'        => true,
			'advanced'        => true,
			'element_require' => '[%map_type%]!="post"',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);

		$arguments['all_posts']           = array(
			'type'            => 'checkbox',
			'title'           => __( 'Show all posts?', 'geodirectory' ),
			'desc'            => __( 'This displays all posts on map from archive page.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="archive"',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);
		$arguments['post_id']             = array(
			'type'            => 'text',
			'title'           => __( 'Post ID:', 'geodirectory' ),
			'desc'            => __( 'Map post id.', 'geodirectory' ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'element_require' => '[%map_type%]=="post"',
			'group'           => __( 'Map Content', 'geodirectory' ),
		);
		$arguments['search_filter']       = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable search filter?', 'geodirectory' ),
			'desc'            => __( 'This enables search filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
			'group'           => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['post_type_filter']    = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable post type filter?', 'geodirectory' ),
			'desc'            => __( 'This enables post type filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
			'group'           => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['cat_filter']          = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable category filter?', 'geodirectory' ),
			'desc'            => __( 'This enables categories filter on map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
			'group'           => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['child_collapse']      = array(
			'type'            => 'checkbox',
			'title'           => __( 'Collapse sub categories?', 'geodirectory' ),
			'desc'            => __( 'This will hide the sub-categories under the parent, requiring a click to show.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="directory"',
			'group'           => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['map_directions']      = array(
			'type'            => 'checkbox',
			'title'           => __( 'Enable map directions?', 'geodirectory' ),
			'desc'            => __( 'Displays post directions for single post map.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'value'           => '1',
			'default'         => '0',
			'advanced'        => false,
			'element_require' => '[%map_type%]=="post"',
			'group'           => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['scrollwheel']         = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable mouse scroll zoom?', 'geodirectory' ),
			'desc'        => __( 'Lets the map be scrolled with the mouse scroll wheel.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
			'group'       => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['hide_zoom_control']   = array(
			'type'        => 'checkbox',
			'title'       => __( 'Hide Zoom Control?', 'geodirectory' ),
			'desc'        => __( 'Hide zoom control "+" and "-" buttons for changing the zoom level of the map.', 'geodirectory' ),
			'placeholder' => '',
			'value'       => '1',
			'default'     => '0',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Map Options', 'geodirectory' ),
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
			'group'       => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['sticky']              = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable sticky map?', 'geodirectory' ),
			'desc'        => __( 'When in the sidebar this will attempt to make it stick when scrolling on desktop.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
			'group'       => __( 'Map Options', 'geodirectory' ),
		);
		$arguments['static']              = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable static map?', 'geodirectory' ),
			'desc'        => __( 'FOR POST MAP ONLY When enabled this will try to load a static map image.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => true,
			'group'       => __( 'Map Options', 'geodirectory' ),
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
				'group'           => __( 'Map Options', 'geodirectory' ),
			);
		}

		// map styles
		$arguments['width'] = array(
			'type'        => 'text',
			'title'       => __( 'Width:', 'geodirectory' ),
			'desc'        => __( 'This is the width of the map, you can use % or px here. (static map requires px value)', 'geodirectory' ),
			'placeholder' => '100%',
			'desc_tip'    => true,
			'default'     => '100%',
			'advanced'    => false,
			'group'       => __( 'Map', 'geodirectory' ),
		);

		$arguments['height'] = array(
			'type'        => 'text',
			'title'       => __( 'Height:', 'geodirectory' ),
			'desc'        => __( 'This is the height of the map, you can use %, px or vh here. (static map requires px value)', 'geodirectory' ),
			'placeholder' => '425px',
			'desc_tip'    => true,
			'default'     => '425px',
			'advanced'    => false,
			'group'       => __( 'Map', 'geodirectory' ),
		);

		$arguments['maptype'] = array(
			'type'     => 'select',
			'title'    => __( 'Mapview:', 'geodirectory' ),
			'desc'     => __( 'This is the type of map view that will be used by default.', 'geodirectory' ),
			'options'  => array(
				'ROADMAP'   => __( 'Road Map', 'geodirectory' ),
				'SATELLITE' => __( 'Satellite Map', 'geodirectory' ),
				'HYBRID'    => __( 'Hybrid Map', 'geodirectory' ),
				'TERRAIN'   => __( 'Terrain Map', 'geodirectory' ),
			),
			'desc_tip' => true,
			'default'  => 'ROADMAP',
			'advanced' => true,
			'group'    => __( 'Map', 'geodirectory' ),
		);
		$arguments['zoom']    = array(
			'type'        => 'select',
			'title'       => __( 'Zoom level:', 'geodirectory' ),
			'desc'        => __( 'This is the zoom level of the map, `auto` is recommended.', 'geodirectory' ),
			'options'     => array_merge( array( '0' => __( 'Auto', 'geodirectory' ) ), range( 1, 19 ) ),
			'placeholder' => '',
			'desc_tip'    => true,
			'default'     => '0',
			'advanced'    => true,
			'group'       => __( 'Map', 'geodirectory' ),
		);

		$design_style = geodir_design_style();

		if ( $design_style ) {
			// background
			$arguments['bg'] = geodir_get_sd_background_input( 'mt' );

			// margins
			$arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
			$arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
			$arguments['mb'] = geodir_get_sd_margin_input( 'mb', array( 'value' => 3 ) );
			$arguments['ml'] = geodir_get_sd_margin_input( 'ml' );

			// padding
			$arguments['pt'] = geodir_get_sd_padding_input( 'pt' );
			$arguments['pr'] = geodir_get_sd_padding_input( 'pr' );
			$arguments['pb'] = geodir_get_sd_padding_input( 'pb' );
			$arguments['pl'] = geodir_get_sd_padding_input( 'pl' );

			// border
			$arguments['border']       = geodir_get_sd_border_input( 'border' );
			$arguments['rounded']      = geodir_get_sd_border_input( 'rounded' );
			$arguments['rounded_size'] = geodir_get_sd_border_input( 'rounded_size' );

			// shadow
			$arguments['shadow'] = geodir_get_sd_shadow_input( 'shadow' );

		}

		$arguments['css_class'] = sd_get_class_input();

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
			'map_type'                => 'auto',
			'width'                   => '100%',
			'height'                  => '425px',
			'maptype'                 => 'ROADMAP',
			'zoom'                    => '0',
			'hide_zoom_control'       => false,
			'hide_street_control'     => false,
			'post_type'               => 'gd_place',
			'terms'                   => array(), // can be string or array
			'tick_terms'              => '',
			'tags'                    => array(), // can be string or array
			'post_id'                 => 0,
			'all_posts'               => false,
			'search_filter'           => false,
			'post_type_filter'        => true,
			'cat_filter'              => true,
			'child_collapse'          => false,
			'map_directions'          => true,
			//          'marker_cluster'   => false,
							'country' => '',
			'region'                  => '',
			'city'                    => '',
			'neighbourhood'           => '',
			'lat'                     => '',
			'lon'                     => '',
			'dist'                    => '',
			'bg'                      => '',
			'mt'                      => '',
			'mb'                      => '3',
			'mr'                      => '',
			'ml'                      => '',
			'pt'                      => '',
			'pb'                      => '',
			'pr'                      => '',
			'pl'                      => '',
			'border'                  => '',
			'rounded'                 => '',
			'rounded_size'            => '',
			'shadow'                  => '',
		);

		$map_args = wp_parse_args( $args, $defaults );

		if( $this->is_preview() ){
			$preview_class = sd_build_aui_class($map_args);
//			$preview_style = sd_build_aui_styles($map_args);
			$p_height = esc_attr( $map_args['height'] );
			$p_width = esc_attr( $map_args['width'] );
			$content = '<div class="bsui" style="overflow:hidden;height:'.esc_attr($p_height).'" class="wp-block-geodirectory-geodir-widget-map"><div title="Placeholder map" style="height:'.esc_attr($p_height).';width:'.esc_attr($p_width).';background-size:cover;background-image:url(data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTc2IiBoZWlnaHQ9IjE3NiIgeG1sbnM6dj0iaHR0cHM6Ly92ZWN0YS5pby9uYW5vIj48c3R5bGU+PCFbQ0RBVEFbLkJ7ZmlsbDojZmZmfS5De2ZpbGw6I2VmZjJmNH0uRHtmaWxsOiM4ZGUyNjB9LkV7bWFzazp1cmwoI3IpfV1dPjwvc3R5bGU+PGRlZnM+PHBhdGggaWQ9IkEiIGQ9Ik0wIDBoMTc2djE3NkgweiIvPjxwYXRoIGlkPSJCIiBkPSJNLjMxNi4xNTloNTkuNDA1djU3LjU2MkguMzE2eiIvPjxwYXRoIGlkPSJDIiBkPSJNLjIxMi4xNTZoNjYuNTA1djYzLjgyMUguMjEyeiIvPjxwYXRoIGlkPSJEIiBkPSJNLjA3MS4yODRIOTguNzJ2MTAzLjUyMkguMDcxeiIvPjxwYXRoIGlkPSJFIiBkPSJNLjE2NC4xMTloODIuNTUydjY2LjY4OUguMTY0eiIvPjxwYXRoIGlkPSJGIiBkPSJNLjI4NS4yMTNoNTQuNDM2djM0Ljc2MkguMjg1eiIvPjxwYXRoIGlkPSJHIiBkPSJNLjAyMi4yMzVoMzcuNjk2djExLjQyOUguMDIyeiIvPjxwYXRoIGlkPSJIIiBkPSJNLjMzNy4yNzNoMjkuMzc3djE2LjUxMkguMzM3eiIvPjxwYXRoIGlkPSJJIiBkPSJNLjA3LjI4aDQ0LjY1MXYyNS41MzRILjA3eiIvPjxwYXRoIGlkPSJKIiBkPSJNLjMyOC4wNzNoMjIuMzg0djEuODVILjMyOHoiLz48cGF0aCBpZD0iSyIgZD0iTS4xNjEuMTU5aDEwLjU3MnY0NS42MzZILjE2MXoiLz48cGF0aCBpZD0iTCIgZD0iTS4zMTcuMTYzaDM1LjM0OXYyOC42NDFILjMxN3oiLz48cGF0aCBpZD0iTSIgZD0iTS4xODYuMTY3aDM5LjU4M3YzOC42NDFILjE4NnoiLz48cGF0aCBpZD0iTiIgZD0iTS4wNzUuMjE3aDQwLjgzNHYyMS41ODhILjA3NXoiLz48cGF0aCBpZD0iTyIgZD0iTS4wMDUuMjU3aDMxLjc2NXY0LjUxNEguMDA1eiIvPjxwYXRoIGlkPSJQIiBkPSJNLjI5MS4yMDZoNDEuN3YzOC42MDFILjI5MXoiLz48cGF0aCBpZD0iUSIgZD0iTS4yNjEuMTU3aDExMi43M3Y1NS43MDNILjI2MXoiLz48cGF0aCBpZD0iUiIgZD0iTS4zMjUuMTU3aDkzLjY2NXY1NS43MDNILjMyNXoiLz48cGF0aCBpZD0iUyIgZD0iTS4zNDEuMTU3SDU1LjcydjUxLjY3OEguMzQxeiIvPjxwYXRoIGlkPSJUIiBkPSJNLjMzMS4xOGg2LjYxNHYxLjc4OUguMzMxeiIvPjxwYXRoIGlkPSJVIiBkPSJNLjExMy4xNThoMjIuNzAzdjEyLjczNkguMTEzeiIvPjxwYXRoIGlkPSJWIiBkPSJNMCAxNzVoMjYyVjBIMHoiLz48L2RlZnM+PGcgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj48bWFzayBpZD0iVyIgY2xhc3M9IkIiPjx1c2UgeGxpbms6aHJlZj0iI0EiLz48L21hc2s+PHVzZSBmaWxsPSIjZDhkOGQ4IiB4bGluazpocmVmPSIjQSIvPjxnIG1hc2s9InVybCgjVykiPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKC04NykiPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIwMykiPjxtYXNrIGlkPSJYIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjQiIvPjwvbWFzaz48cGF0aCBkPSJNLjMxNi0uNDI0aDQuMjFzMTQuNDAxIDIxLjAzNyAzMS40NDcgMzUuMjMxYzE4LjI1NiAxMy4xOTIgNTAuNTAxIDE3LjcyMSA1MC41MDEgMTcuNzIxdjUuMTkzcy0zNC40MTctMy4yNzktNTQuNjI3LTE5LjUyN1MuMzE2LS40MjQuMzE2LS40MjQiIGZpbGw9IiM2ZWU0ZmYiIG1hc2s9InVybCgjWCkiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTk2KSI+PG1hc2sgaWQ9IlkiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNDIi8+PC9tYXNrPjxwYXRoIG1hc2s9InVybCgjWSkiIGQ9Ik0uMjEyLS40MTVoNi40NTRsMTQuMzI3IDE5Ljc5MSAxNy40NjggMTcuNTQyIDQxLjAyIDE2LjQ5MyAxNC4yODEgMy4wNTF2Ny41MTRsLTE0LjM5OC0xLjczOS0zOC43MjItLjI2Ni0yLjEyOC0yLjQwMS05LjA5OC0yMy44Nzl6IiBjbGFzcz0iRCIvPjwvZz48cGF0aCBkPSJNMTY3IDc5LjA5N2wzLjM3NS0xLjc4IDQuMTEzIDUuMDk4IDIuMTYyIDMuMjI2TDIxNy41MTcgNDhsMy4xNjQgMy42NjQtNC45MDQgNS41NDlzNC44NTEgNC4xODggNS4yMiA1LjcwNi0zNi45NjUgMzIuODc3LTM2Ljk2NSAzMi44NzctMS41NTYuNzg1LTMuMjU2LS43MzNTMTY3IDc5LjA5NyAxNjcgNzkuMDk3IiBjbGFzcz0iRCIvPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE2NCA3MikiPjxtYXNrIGlkPSJaIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjRCIvPjwvbWFzaz48cGF0aCBkPSJNOC4zNiA4Mi4yMzdTMS43MzYgNzAuODEyLjU4NCA2OC4yNTJzMC00LjkzNyAwLTQuOTM3bDE0LjEwNS0xNS41MzEgMS4wNTYtMy44NDYgMy4wNjEtLjMxN0w2NC40Ni45MjRjLjIxMS4xMDYgNi4yODEtLjY0MSA2LjI4MS0uNjQxbC0uNTI4IDE3LjE4NS01LjExOS0uMTA3cy0xLjU4MS0uMjYyLTEuNjM2Ljk2MWwtLjIxMSA0LjY0M3MtLjI2MyAxLjcwOC0uNzM4IDIuMTM1LTIuMzIyLS4wNTMtMi40MjggMS4yMjcgMS4xNjEgMi4wMjggMS4xNjEgMi4wMjhsLjA1MiAyLjQ1NWg1MC4wODh2NS43NjRsLTMzLjk5LS4zNzR2MTEuNDIyYzAgLjE2LjE4NyAxLjgxMy0xLjI3OSAyLjM3NHMtMi45NDQtLjg3OS0yLjk0NC0uODc5bC0zLjk1OS05Ljg3NC0yOS4xMzUgMTEuMzE0IDEuNTgzIDQuOTFzLTcuMzg5IDMuMDk2LTguNzYxIDQuMDU2LTIuNzk4IDYuOTkyLTMuNTg5IDcuNzM5LTExLjYxNiAyLjA4MS0xMS42MTYgMi4wODFsMzEuNDQ1IDQ0LjQyMS01LjkxIDIuNzc1LjEwNi0yLjc3NS0yMy44NjgtMzcuODcxLTExLjEwNSA2LjM0eiIgbWFzaz0idXJsKCNaKSIgY2xhc3M9IkQiLz48L2c+PGcgY2xhc3M9IkMiPjxwYXRoIGQ9Ik0xODIuMDc0IDhoMjMuNDMyczIxLjM1OSAyNi41ODcgMjEuMDQyIDI3LjI5NlMyMzUgNTkuNTIxIDIzNSA1OS41MjFsLTIyLjI5LTI1Ljc2Yy0uOTUxLTEuMTkxLTQuMDE0LTEuNjE1LTYuMTI3LS40NXMtMzMuMDY2IDMxLjM0Ni0zMy4wNjYgMzEuMzQ2TDE1MS42NDkgNzggMTMyIDU3LjI0NGwzOC44NzgtMzYuMzQ3Yy4yMDMtLjE5LS41My0yLjUxOS0uNTMtMi41MTlMMTgyLjA3NCA4ek0xMTggMTE4LjMzM0wxMzMuNDgxIDEwNGwyNS4wODggMjcuMDZjLjM5NyAxLjAwNC43MjkgMi4xNTQgMCAyLjgxTDE0NC4wMDEgMTQ3IDExOCAxMTguMzMzem0zNy0zMS40NzRsMi4wMTgtMS43NTQgOS4yOTItNS40NzQgMTQuNzA4IDE1LjMwNGMxLjM4MSAxLjU0MSAzLjQ1MS40MjUgMy40NTEuNDI1bDMzLjY2NC0yOS43NThzMS44MzItMS4wODYuNDc4LTIuNDk3bC0zLjc0My0zLjk4NWMtLjY2NC0uNzk3IDAtMi4zMzggMC0yLjMzOEwyMjAuMjU3IDUyIDIzMyA2Ni41MDdsLTUwLjU0OSA0Ni41NXMtLjk1NiAxLjU5NC0yLjQ5Ni42MzdTMTU1IDg2Ljg1OSAxNTUgODYuODU5bTE1LTkuNjM4Yy4xNTctLjA1MiA0LjEwMSA1LjEyNiA0LjEwMSA1LjEyNnMuMzE1IDIuNzcxIDEuNDcyIDMuNDU1IDIuMzY2LS42MzIgMi4zNjYtLjYzMkwyMTcgNDcuNzM5IDIxMC44NDkgNDEgMTcwIDc3LjIyMXpNMTY0LjQxNCAxNDBjLjEyLjA0LTIuNTU4LTUuMzk5LTIuNTU4LTUuMzk5bC45MTktMi43NzktLjkxOS0yLjU4TDEzNiAxMDIuMjkgMTUyLjIyNSA4OGwyNC40NTggMjZjMS44NzkgMi4wMjQgMy4zMTcgMS45MDUgMy4zMTcgMS45MDVsLS42NzkgMy45M3MtMTQuMTg3IDE0LjYwNy0xNC42MjcgMTUuNzU5LS4yOCA0LjQwNi0uMjggNC40MDYiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTgwIDEwOSkiPjxtYXNrIGlkPSJhIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjRSIvPjwvbWFzaz48cGF0aCBkPSJNMzIuNDQzIDc2LjYzNUwuMTY0IDMyLjcwOXMxMC43MjktLjk4NyAxMS43OTQtMi4yNjQgMy4zODYtNy44OTggMy4zODYtNy44OThsOS4xODUtMy41MjdzLTIuMjMtNS4wNTUtMS45NjYtNS4wNTUgMzAuMzQtMTEuNTMgMzAuMzkzLTExLjM3MmE3NTk1LjEzIDc1OTUuMTMgMCAwIDAgMy44NTMgMTAuMjY3czEuMTU0IDEuMDc1IDIuNjkxLjk1MiAxLjUzNS0xLjU1NiAxLjUzNS0xLjU1Nkw2MS4wOS4xMTlsMzQuNDY0LjM2OXYxMy42MzZzLTI3Ljc0OC0uMDc5LTM2LjIzMSAxLjEwNi0zMy40NDcgMTIuMzItMzMuNDQ3IDEyLjMybC0yLjk5Ny00LjQyM2MtLjY1Ni0uOTg3LTMuOTktLjgyOS00LjM1OC42MzJzLjY4NCAxLjc5LjY4NCAxLjc5bDEuNTkyLS4yMTEgMjIuMjI0IDQ2LjIyMy0xMC41NzkgNS4wNzR6IiBtYXNrPSJ1cmwoI2EpIiBjbGFzcz0iQyIvPjwvZz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyMDggMTI0KSI+PG1hc2sgaWQ9ImIiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNGIi8+PC9tYXNrPjxwYXRoIGQ9Ik0uMjg1IDE0LjA4OGMuMjMuMDU1IDkuMDQ3IDIwLjg4NiA5LjA0NyAyMC44ODZsMjMuMjczLTkuODg1YzEuNjgxLS44MjUgMTIuNzYyLTIuNTQ2IDE0LjY1My0yLjUzMWwyMC4wNjMuMTY2Vi41MDJTNDIuMjE2LS42NTQgMzEuNjA3IDEuNjU3LjI4NSAxNC4wODguMjg1IDE0LjA4OCIgbWFzaz0idXJsKCNiKSIgY2xhc3M9IkMiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjI1IDkyKSI+PG1hc2sgaWQ9ImMiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNHIi8+PC9tYXNrPjxwYXRoIGQ9Ik00Ljg1My4yMzVsLS4yNjYgNC40N2MwIDEuMjQ1LTEuMTIzIDMuMDE2LTEuODA1IDMuMjgyTC4wMjIgOS4wNjFsLjA1MyAyLjYwM2g1MC4zNzlWLjIzNUg0Ljg1M3oiIG1hc2s9InVybCgjYykiIGNsYXNzPSJDIi8+PC9nPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIzMyA3NCkiPjxtYXNrIGlkPSJkIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjSCIvPjwvbWFzaz48cGF0aCBtYXNrPSJ1cmwoI2QpIiBkPSJNMS4xNS4yNzNoNS4xMTlsMS4yOTMgMS4wMjZoMzUuMDc5djE1LjM4OWwtNDIuMzA0LjA5N3oiIGNsYXNzPSJDIi8+PC9nPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIxOCAxNTApIj48bWFzayBpZD0iZSIgY2xhc3M9IkIiPjx1c2UgeGxpbms6aHJlZj0iI0kiLz48L21hc2s+PHBhdGggZD0iTS4wNyAxMS4zNTRsOC4xMDEgMTcuNzA0IDE1LjA1LTUuNDUgMTUuNDE4LTIuNzUxIDE3LjI1OS0uNTYxVi4yOEgzNi4zNzZMMjMuNzU4IDIuMDExLjA3IDExLjM1NHoiIG1hc2s9InVybCgjZSkiIGNsYXNzPSJDIi8+PC9nPjxwYXRoIGQ9Ik0yMzIgNzBoNWwtMi41NDYtMnoiIGNsYXNzPSJDIi8+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjQwIDY4KSI+PG1hc2sgaWQ9ImYiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNKIi8+PC9tYXNrPjxwYXRoIG1hc2s9InVybCgjZikiIGQ9Ik0uMzI4LjA3M2wxLjM5NSAxLjUyOCAzMi43OTMuMzIyVi4wNzN6IiBjbGFzcz0iQyIvPjwvZz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyNTIpIj48bWFzayBpZD0iZyIgY2xhc3M9IkIiPjx1c2UgeGxpbms6aHJlZj0iI0siLz48L21hc2s+PHBhdGggbWFzaz0idXJsKCNnKSIgZD0iTS4xNjEtLjQyNGgxOC40MzdsMTcuNjk5IDMuMDJ2NDMuMmwtNC4zNTgtMi44NDd6IiBjbGFzcz0iQyIvPjwvZz48ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxNzIgMTQ3KSI+PG1hc2sgaWQ9ImgiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNMIi8+PC9tYXNrPjxwYXRoIGQ9Ik0yMy4yMTUgNDcuOTc3bDEyLjI4MS02LjI4M2MuMzM1LS4xNTguMDcyLTMuMjIyLjA3Mi0zLjIyMkwxMS42OTguMTYzLjMxNyA2Ljk3M2wyMi44OTggNDEuMDAzeiIgbWFzaz0idXJsKCNoKSIgY2xhc3M9IkMiLz48L2c+PHBhdGggZD0iTTExNy41NjEgMTE5TDE0MyAxNDcuNjE4Yy0uMDcxLS4xNDQtMTUuNDkgMTQuMzgyLTE1LjQ5IDE0LjM4MkwxMDIgMTMzLjk2NSAxMTcuNTYxIDExOXoiIGNsYXNzPSJDIi8+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTI4IDEzNykiPjxtYXNrIGlkPSJpIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjTSIvPjwvbWFzaz48cGF0aCBkPSJNMS41ODkgMjcuNzYxTDMyLjI4OC4xNjdsNy40ODEgMTYuMTA4LTE0LjQ2NyAxMS40ODYtMTMuMjY3IDYuOTM0LTIuMDQ3IDEuMjYxLTEuODQyIDMuMjIydjEuMTkxbC0uNDg3IDEuMjYxTC40NiAzMS4xOTNzLS42MTYtMS41OTEgMC0yLjI0MWwxLjEyOS0xLjE5MSIgbWFzaz0idXJsKCNpKSIgY2xhc3M9IkMiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTM2IDE1NCkiPjxtYXNrIGlkPSJqIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjTiIvPjwvbWFzaz48cGF0aCBtYXNrPSJ1cmwoI2opIiBkPSJNLjA3NSAyNS45MWw1LjUyMyA5Ljc5NSAxOC43NjYtMTAuNjUyIDE2LjU0NS05LjYzNUwzMi4yMzkuMjE3IDE4LjM3IDExLjM1MSAzLjUzNiAxOS4zOGwtMi4xNjcgMy4yNjUuMzE3IDEuMzkyeiIgY2xhc3M9IkMiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTYxIDE3MSkiPjxtYXNrIGlkPSJrIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjTyIvPjwvbWFzaz48cGF0aCBtYXNrPSJ1cmwoI2spIiBkPSJNLjAwNSAxMy42MTRsMTMuMzQgMjguMDk1IDE4LjQyNi0xMi41MDhMMTcuOTEyLjI1N3oiIGNsYXNzPSJDIi8+PC9nPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDg4IDEzNykiPjxtYXNrIGlkPSJsIiBjbGFzcz0iQiI+PHVzZSB4bGluazpocmVmPSIjUCIvPjwvbWFzaz48cGF0aCBkPSJNLjI5MSAxMS4zODlDLjQ0NyAxMS4yODMgMTMuMTE4LjIwNiAxMy4xMTguMjA2bDI1LjEzNSAyNy44MjUtLjUxOSAyLjQzOC4xNTYgMi4wMTQgNC4xMDMgNS43MjQtMTguNzQ3IDEyLjI5NkwuMjkxIDExLjM4OXoiIG1hc2s9InVybCgjbCkiIGNsYXNzPSJDIi8+PC9nPjxwYXRoIGQ9Ik03OSAxMTIuODdMMTEyLjgzNCA4MiAxMjggMTAxLjEgOTEuNTUgMTM1aC0yLjE4NnoiIGNsYXNzPSJDIi8+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoOTMpIj48bWFzayBpZD0ibSIgY2xhc3M9IkIiPjx1c2UgeGxpbms6aHJlZj0iI1EiLz48L21hc2s+PHBhdGggZD0iTTM4LjY0MyA1NS44NTlsMzguMTU2LTM1Ljc0OSAxLjMyMy4yNjJMOTAuMTggOS42MTdsMjIuODExLS43ODUtOC41NzktOS4yNUg1NC4zNTQgMjcuNjYxbDMuOTM0IDExLjY5Yy41NzcgMS4yNDMgMS44MDEgMi44OTEtLjA0IDMuNzMxcy0yLjg2Ny0yLjQ0OS0yLjg2Ny0yLjQ0OUwyNC4yMzgtLjQxN0g4LjEyTC4yNjEgNC4wOSAxNS4yIDI4LjQzNmwyMy40NDIgMjcuNDI0eiIgbWFzaz0idXJsKCNtKSIgY2xhc3M9IkMiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTExKSI+PG1hc2sgaWQ9Im4iIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNSIi8+PC9tYXNrPjxwYXRoIGQ9Ik04NS40NzQtLjQxN0gzOC42NThDMjUuODEgOS45OTcgMTMuMDIxIDIwLjU2MS42NDMgMzIuMjgzYy0uMDk1LjA0OC0uMjAzLjEwNi0uMzE3LjE2OGwxOS44NjMgMjMuNDA4IDM3LjYxOC0zNS41MTljLjQ4My0uMzQxIDEuNTcxLjAzMiAxLjU3MS4wMzJsMTEuOTctMTAuNzU1IDIyLjY0My0uNzg1LTguNTE2LTkuMjV6IiBmaWxsPSIjZTJlMmUyIiBtYXNrPSJ1cmwoI24pIi8+PC9nPjxnIGNsYXNzPSJDIj48cGF0aCBkPSJNMTEzIDgwLjc4NkwxMjkuMjI5IDk5IDE0OSA4MS4zNzUgMTMyLjIzIDYzek05NyA2MS4xOTFMMTEwLjA2IDc2bDE4LjkzOS0xNy4yNDNjLS4xNTYgMC0xMy4wNi0xNC43NTctMTMuMDYtMTQuNzU3TDk3IDYxLjE5MXoiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjA3KSI+PG1hc2sgaWQ9Im8iIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNTIi8+PC9tYXNrPjxwYXRoIGQ9Ik0uMzQxLS40MTlsNDAuNDY5LjIxNiAyNy41NzggMzUuMTIxIDguMTIgMTAuMjQ4IDYuMDU0IDMuMjk5djMuMzY5bC0xNC4xNzQtMi42OTJzLTI1LjE2My0zLjg3LTM5LjA1MS0xNi4xOTJTLjM0MS0uNDE5LjM0MS0uNDE5IiBmaWxsPSIjZTNlNGU1IiBtYXNrPSJ1cmwoI28pIi8+PC9nPjxnIGNsYXNzPSJDIj48cGF0aCBkPSJNNzggNzkuMTE4TDk2LjE4MiA2MiAxMDkgNzcuNDI1IDg2LjI5IDk5ek05Ny4zMDQgNThMMTE1IDQxLjg1N2wtOS41LTEyLjIxNEw5MS41IDYgNzAgMTkuMjg2bDYuNzg2IDguMDcxIDIuOTI4IDMuMDcyIDEuODU3IDEwLjIxNCA2LjcxNCAxMC42NDMgMS42NDMuMzU3ek00MiAzNi4wMTJMNjcuNDY0IDIxbDkuNTY3IDEwLjcxMyAxLjQzOSA5LjU4NSA3LjQ4MSAxMS43N3YxLjc2Mkw5MyA2MS41OTcgNjcuNSA4NC4yMjFsLTIuNjI1IDMuNjY1TDU5LjQ3OSA5MHoiLz48L2c+PGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoOTApIj48bWFzayBpZD0icCIgY2xhc3M9IkIiPjx1c2UgeGxpbms6aHJlZj0iI1QiLz48L21hc2s+PHBhdGggZD0iTS4zMzEtLjQxOWMuMDc0IDAgMS42MyAyLjM4OCAxLjYzIDIuMzg4TDYuOTQ0LS40OC4zMzEtLjQxOXoiIG1hc2s9InVybCgjcCkiIGNsYXNzPSJDIi8+PC9nPjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDY3KSI+PG1hc2sgaWQ9InEiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNVIi8+PC9tYXNrPjxwYXRoIG1hc2s9InVybCgjcSkiIGQ9Ik0uMTEzLS40MjFoMjAuNzFsMS45OTMgMy43NzYtMTUuODcgOS41NHoiIGNsYXNzPSJDIi8+PC9nPjxnIGZpbGw9IiNjNWM3YzkiPjxwYXRoIGQ9Ik0xNDYgMTUyLjM3Nmw1Ljk5MyA2LjYyNCAxMS4wMDQtMTAuMzk3Yy4xNTUgMC02LjI1MS02LjYwMy02LjI1MS02LjYwM0wxNDYgMTUyLjM3NnptLTIwLTgyLjI1NEwxMzEuNzI2IDY1IDEzOSA3Mi45MTYgMTMzLjQ2NyA3OHptNTItNTEuODYzTDE4MS44ODcgMjIgMTg4IDE1LjcwNCAxODQuMDUyIDEyem0xNyA2OC45MzhMMTk4LjM4IDkxbDQuNjItNC4xOTdMMTk5LjQ5MiA4M3oiLz48L2c+PG1hc2sgaWQ9InIiIGNsYXNzPSJCIj48dXNlIHhsaW5rOmhyZWY9IiNWIi8+PC9tYXNrPjxnIGZpbGw9IiNjNWM3YzkiIGNsYXNzPSJFIj48cGF0aCBkPSJNMjU5IDEwMWgzdi04aC0zem0tNjUgNDUuMjg5czUuMjM2IDExLjg2MiA1LjM5NSAxMS43MSA5LjYwNS0zLjkxNSA5LjYwNS0zLjkxNUwyMDMuNDA5IDE0MiAxOTQgMTQ2LjI4OXptLTY0LTk4LjkxMkwxMzIuNzI1IDUwIDEzNyA0NS44ODUgMTM0LjAwMyA0MyAxMzAgNDcuMzc3em0tNDEgNTYuODU0Yy4yMiAwIDIzLjAyNC0xOS4yMzEgMjMuMDI0LTE5LjIzMUwxMTggOTEuMjU2IDk1LjQ2OSAxMTEgODkgMTA0LjIzMXptMi0zMy40MTFMOTUuOTAyIDY2IDEwMSA3MS4yMTNsLTIuODQzIDIuNzU0LTEuMDc5LTEuMDMzTDk0LjkyMiA3NXptMjAtMjAuMjgyTDExNi4zODggNDYgMTI3IDU3LjI1MyAxMjEuNjEyIDYybC0xLjU1OS0xLjk0MSAxLjAxMi0uNzA4LTEuMTQ5LTEuMzExLjc2Ni0uNDcyTDExOSA1NS42MjZsLjY5Ny0uNTUxLTEuNTU5LTEuNzA1LTEuMDQuOTE4LTEuNjE0LTEuOTE1LS43NjYuNzYxLTEuMDEyLTEuMTgtLjY4NC42Mjl6bS03IDEwMC43MzljLjE1Ni0uMDU0IDUuMDMzLTMuMjc3IDUuMDMzLTMuMjc3bDUuOTY3IDkuNzIzLTUuNSAzLjI3Ny01LjUtOS43MjN6Ii8+PC9nPjxwYXRoIGZpbGw9IiNmN2QyYWQiIGQ9Ik0yMzIgMTY2LjUzNWwzLjczNy0xLjUzNSAzLjI2MyA3LjMwNi0zLjUgMS42OTR6IiBjbGFzcz0iRSIvPjxwYXRoIGQ9Ik04MSAxNTIuMDAyYy0uMTA2LS4zMTggMTkuMDk3IDM0Ljg0NSAxOS4wOTcgMzQuODQ1TDEyNiAyMjlsLTExLjA3OS0yMS43MTItMjEuOTk5LTM2LjU0TDgxIDE1Mi4wMDJ6IiBmaWxsPSIjZTJlMmUyIiBjbGFzcz0iRSIvPjwvZz48L2c+PC9nPjwvc3ZnPg==)" class="'.esc_attr($preview_class).'"></div></div>';
			return $content;
		}

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
		} elseif ( ! is_array( $map_args['terms'] ) && $map_args['terms'] == '0' ) {
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
							$map_args['tags']  = array();
							//                          if ( ! empty( $wp_query->posts ) ) {
							//                              foreach ( $wp_query->posts as $post ) {
							//                                  $map_args['posts'][] = $post->ID;
							//                              }
							//                          }
						}
					} else {
						if ( ! empty( $wp_query ) && $wp_query->is_main_query() ) {
							$map_args['posts'] = array();
							$map_args['terms'] = array();
							$map_args['tags']  = array();
							if ( ! empty( $wp_query->queried_object ) && ! empty( $wp_query->queried_object->term_id ) ) {
								$queried_object = $wp_query->queried_object;
								if ( ! empty( $queried_object->taxonomy ) && ! empty( $queried_object->name ) && geodir_taxonomy_type( $queried_object->taxonomy ) == 'tag' ) {
									$map_args['tags'][] = $queried_object->name; // Tag
								} else {
									$map_args['terms'][] = $queried_object->term_id; // Category
								}
							} elseif ( ! empty( $_REQUEST['spost_category'] ) && geodir_is_page( 'search' ) ) { // Search by category
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
					if ( ! ( ! empty( $gd_post ) && ! empty( $gd_post->ID ) && $gd_post->ID == (int) $map_args['post_id'] ) ) {
						$gd_post = geodir_get_post_info( (int) $map_args['post_id'] );
					}
				}

				// bail if no GPS.
				if ( empty( $gd_post->latitude ) || empty( $gd_post->longitude ) ) {
					return;
				}

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
				} elseif ( ( geodir_is_page( 'detail' ) || geodir_is_page( 'preview' ) ) && ! empty( $gd_post->ID ) ) {
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
			$post_type  = $post_types[0]; // @todo implement multiple for CPT
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

		$latitude = ! empty( $current_location->latitude ) ? $current_location->latitude : '';
		$longitude = ! empty( $current_location->longitude ) ? $current_location->longitude : '';

		if ( ! ( ! empty( $latitude ) && ! empty( $longitude ) ) && geodir_is_page( 'search' ) && ( $near_lat = GeoDir_Query::get_query_var( 'sgeo_lat' ) ) && ( $near_lon = GeoDir_Query::get_query_var( 'sgeo_lon' ) ) ) {
			$latitude = geodir_sanitize_float( $near_lat );
			$longitude = geodir_sanitize_float( $near_lon );
		}

		if ( empty( $map_args['country'] ) && empty( $map_args['region'] ) && empty( $map_args['city'] ) && empty( $map_args['neighbourhood'] ) && ! empty( $latitude ) && ! empty( $longitude ) ) {
			$map_args['lat'] = $latitude;
			$map_args['lon'] = $longitude;

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

		return self::render_map( $map_args, $this );
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
		$design_style   = geodir_design_style();
		$map_post_types = array();
		$map_canvas     = $map_options['map_canvas'];

		if ( ! empty( $map_options['map_directions'] ) ) {
			$distance_unit = geodir_get_option( 'search_distance_long' );

			// template output
			$template = $design_style ? $design_style . '/map/directions.php' : 'legacy/map/directions.php';
			$args     = array(
				'map_options'   => $map_options,
				'map_canvas'    => $map_canvas,
				'distance_unit' => $distance_unit,
			);
			echo geodir_get_template_html( $template, $args );
		}

		if ( ! empty( $map_options['post_type_filter'] ) ) {
			$map_post_types = self::map_post_types( true );
		}

		if ( ! empty( $map_options['cat_filter'] ) || ! empty( $map_options['search_filter'] ) || ( ! empty( $map_options['post_type_filter'] ) && ! empty( $design_style ) ) ) {
			$cat_filter_class = '';
			if ( ! empty( $map_options['post_type_filter'] ) ) {
				$cpts_on_map      = $map_post_types;
				$cat_filter_class = $cpts_on_map > 1 ? ' gd-map-cat-ptypes' : ' gd-map-cat-floor';
			}

			// template output
			$template = $design_style ? $design_style . '/map/filter-tax.php' : 'legacy/map/filter-tax.php';
			$args     = array(
				'map_options'      => $map_options,
				'map_canvas'       => $map_canvas,
				'cat_filter_class' => $cat_filter_class,
				'map_post_types'   => $map_post_types,
			);
			echo geodir_get_template_html( $template, $args );
		}

		// old design shows on bottom
		if ( ! empty( $map_options['post_type_filter'] ) && empty( $design_style ) ) {
			if ( ! empty( $map_post_types ) && count( array_keys( $map_post_types ) ) > 1 ) {

				// template output
				$template = 'legacy/map/filter-cpt.php';
				$args     = array(
					'map_options'    => $map_options,
					'map_canvas'     => $map_canvas,
					'map_post_types' => $map_post_types,
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
	public static function map_post_types( $hide_empty = false ) {
		$post_types     = geodir_get_posttypes( 'options-plural' );
		$map_post_types = array();
		if ( ! empty( $post_types ) ) {
			$exclude_post_types = geodir_get_option( 'exclude_post_type_on_map' );

			foreach ( $post_types as $post_type => $name ) {

				if ( $hide_empty ) {
					$post_counts = wp_count_posts( $post_type, 'readable' ); // let WP handle the caching
					if ( isset( $post_counts->publish ) && $post_counts->publish == 0 ) {
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
		$base_latitude  = '';
		$base_longitude = '';
		$base_zoom      = '';

		if ( geodir_is_page( 'search' ) ) {
			if ( ! empty( $map_options['base_lat'] ) && ! empty( $map_options['base_lon'] ) ) {
				$base_latitude  = $map_options['base_lat'];
				$base_longitude = $map_options['base_lon'];
			} elseif ( ! empty( $map_options['lat'] ) && ! empty( $map_options['lon'] ) ) {
				$base_latitude  = $map_options['lat'];
				$base_longitude = $map_options['lon'];
			} elseif ( ! empty( $_REQUEST['sgeo_lat'] ) && ! empty( $_REQUEST['sgeo_lon'] ) ) {
				$base_latitude  = sanitize_text_field( $_REQUEST['sgeo_lat'] );
				$base_longitude = sanitize_text_field( $_REQUEST['sgeo_lon'] );
			} elseif ( ! empty( $map_options['default_lat'] ) && ! empty( $map_options['default_lng'] ) ) {
				$base_latitude  = $map_options['default_lat'];
				$base_longitude = $map_options['default_lng'];
			}

			if ( ! empty( $map_options['base_zoom'] ) && absint( $map_options['base_zoom'] ) > 0 ) {
				$base_zoom = $map_options['base_zoom'];
			} elseif ( ! empty( $map_options['zoom'] ) && absint( $map_options['zoom'] ) > 0 ) {
				$base_zoom = $map_options['zoom'];
			} elseif ( ! empty( $map_options['nomap_zoom'] ) && absint( $map_options['nomap_zoom'] ) > 0 ) {
				$base_zoom = $map_options['nomap_zoom'];
			} else {
				$base_zoom = 11;
			}
		}
		?>
<style>.geodir_map_container .poi-info-window .full-width{width:180px;position:relative;margin-left:inherit;left:inherit;}.geodir-map-canvas .gm-style img{max-width:none}</style>
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
			if ( $map_options['map_type'] == 'post' && $map_options['static'] ) {
				echo 'geodir_build_static_map(gdMapCanvas);';
			} else {
				echo 'build_map_ajax_search_param(gdMapCanvas, ' . $load_terms . ');';
			}
			?>
			<?php if ( ! empty( $map_options['sticky'] ) ) { ?>
			geodir_map_sticky(gdMapCanvas);
			<?php } ?>
			<?php if ( ! empty( $map_options['map_directions'] ) ) { ?>
			geodir_map_directions_init(gdMapCanvas);
			<?php } ?>
			<?php
			if ( strpos( $map_options['height'], 'vh' ) !== false ) {
				$height = str_replace( 'vh', '', $map_options['height'] );
				?>
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
				<?php
			} elseif ( strpos( $map_options['height'], 'px' ) !== false ) {
				$height = str_replace( 'px', '', $map_options['height'] );
				?>
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
			'' => __( 'Auto', 'geodirectory' ),
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
		return geodir_category_options( $post_type );
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
	public static function render_map( $map_args, $widget = array() ) {
		global $geodirectory, $gd_post;

		$defaults = array(
			'map_type'            => 'auto',                    // auto, directory, archive, post
			'map_canvas'          => '',
			'map_class'           => '',
			'width'               => '100%',
			'height'              => '425px',
			'maptype'             => 'ROADMAP',
			'hide_zoom_control'   => false,
			'hide_street_control' => false,
			'zoom'                => '0',
			'autozoom'            => true,
			'post_type'           => 'gd_place',
			'terms'               => '',
			'tick_terms'          => '',
			'tags'                => '',
			'posts'               => '',
			'sticky'              => false,
			'static'              => false,
			'map_directions'      => false,
			'bg'                  => '',
			'mt'                  => '',
			'mb'                  => '3',
			'mr'                  => '',
			'ml'                  => '',
			'pt'                  => '',
			'pb'                  => '',
			'pr'                  => '',
			'pl'                  => '',
			'border'              => '',
			'rounded'             => '',
			'rounded_size'        => '',
			'shadow'              => '',
			'cameraControl'       => false // cameraControl
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
		$params['nomap_lat']  = filter_var( $nomap_lat, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		$params['nomap_lng']  = filter_var( $nomap_lng, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
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
				$params['latitude']  = filter_var( $gd_post->latitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
				$params['longitude'] = filter_var( $gd_post->longitude, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
			}

			// set icon url
			if ( ! empty( $gd_post->default_category ) ) {
				$params['icon_url'] = geodir_get_cat_icon( $gd_post->default_category, true, true );
			}
		}

		// wrap class
		$params['wrap_class'] = sd_build_aui_class( $params );

		$params['wrap_class'] .= ' overflow-hidden';

		ob_start();

		self::display_map( $params, $widget );

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
	public static function display_map( $params, $widget = array() ) {
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

		$markers_url      = geodir_rest_markers_url();
		$markers_ajax_url = $markers_url;
		$url_params       = '';
		if ( defined( 'GEODIR_FAST_AJAX' ) && geodir_get_option( 'fast_ajax' ) ) {
			$markers_ajax_url = add_query_arg( array( 'gd-ajax' => 1 ), $markers_ajax_url );
			$url_params       = '&gd-ajax=1';
		}

		$defaults    = array(
			'scrollwheel'              => true,
			'streetViewControl'        => true,
			'fullscreenControl'        => false,
			'maxZoom'                  => 21,
			'token'                    => '68f48005e256696074e1da9bf9f67f06',
			'_wpnonce'                 => geodir_create_nonce( 'wp_rest' ),
			'navigationControlOptions' => array(
				'position' => 'TOP_LEFT',
				'style'    => 'ZOOM_PAN',
			),
			'map_ajax_url'             => $markers_url,
			'map_markers_ajax_url'     => $markers_ajax_url,
			'map_terms_ajax_url'       => $markers_ajax_url,
			'map_marker_ajax_url'      => $markers_url,
			'map_marker_url_params'    => $url_params,
			'wrap_class'               => '',
		);
		$map_options = wp_parse_args( $params, $defaults );

		$map_options['map_canvas'] = isset( $gd_maps_canvas[ $map_options['map_canvas'] ] ) ? $map_options['map_canvas'] . count( $gd_maps_canvas ) : $map_options['map_canvas'];

		$map_type   = $map_options['map_type'];
		$map_canvas = $map_options['map_canvas'];
		$width      = $map_options['width'];
		$height     = $map_options['height'];
		$wrap_class = ! empty( $map_options['wrap_class'] ) ? $map_options['wrap_class'] : '';
		$map_class  = 'geodir_map_container gd-map-' . $map_type . 'container';

		$gd_maps_canvas[ $map_options['map_canvas'] ] = $map_options;

		$map_canvas_attribs = '';
		if ( $map_options['map_type'] == 'post' && ! empty( $gd_post ) && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
			$map_canvas_attribs .= ' data-lat="' . esc_attr( $gd_post->latitude ) . '" data-lng="' . esc_attr( $gd_post->longitude ) . '" ';
		}

		// Enqueue widget scripts on call.
		geodir_widget_enqueue_scripts( $map_options, $widget );

		// template output
		$design_style = geodir_design_style();
		$template     = $design_style ? $design_style . '/map/map.php' : 'legacy/map/map.php';

		$args = array(
			'map_options'   => $map_options,
			'map_type'      => $map_type,
			'map_canvas'    => $map_canvas,
			'height'        => $height,
			'width'         => $width,
			'wrap_class'    => $wrap_class,
			'extra_attribs' => $map_canvas_attribs,
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

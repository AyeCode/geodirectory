<?php
/**
 * GeoDirectory GeoDirectory Popular Post View Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory listings widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Simple_Archive extends WP_Super_Duper {

	public $post_title_tag;

	/**
	 * Register the popular posts widget.
	 *
	 * @since 1.0.0
	 * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'fas fa-th-list',
			'block-category' => 'geodirectory',
			'block-supports' => array(// 'customClassName'   => false
			),
			'block-wrap'     => '', // the element to wrap the block output in. , ie: div, span or empty for no wrap
			'no_wrap'        => true,
			'block-keywords' => "['archive','listings','posts']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_simple_archive', // this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Simple Archive', 'geodirectory' ), // the name of the widget.
			'widget_ops'     => array(
				'classname'                   => 'geodir-simple-archive ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'Easily build an archive design.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
			),
		);

		parent::__construct( $options );
	}

	/**
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments() {

		$arguments = array(

			'output'            => array(
				'title'    => __( 'Output', 'geodirectory' ),
				'desc'     => __( 'Select what to output', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					''         => __( 'Listings + Map', 'geodirectory' ),
					'listings' => __( 'Listings only', 'geodirectory' ),
					'map'      => __( 'Map only', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Main Output', 'geodirectory' ),
			),
			'ratio'             => array(
				'title'           => __( 'Ratio', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					''      => __( 'Listings 50/50 Map', 'geodirectory' ),
					'60/40' => __( 'Listings 60/40 Map', 'geodirectory' ),
					'70/30' => __( 'Listings 70/30 Map', 'geodirectory' ),
					'40/60' => __( 'Listings 40/60 Map', 'geodirectory' ),
					'30/70' => __( 'Listings 30/70 Map', 'geodirectory' ),
				),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Main Output', 'geodirectory' ),
				'element_require' => '[%output%]==""',
			),
			'show_search'       => array(
				'title'    => __( 'Show search', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'group'    => __( 'Main Output', 'geodirectory' ),
			),
			'show_loop_actions' => array(
				'title'    => __( 'Show loop actions', 'geodirectory' ),
				'type'     => 'checkbox',
				'desc_tip' => true,
				'value'    => '1',
				'default'  => 0,
				'group'    => __( 'Main Output', 'geodirectory' ),
			),

			'ratio'             => array(
				'title'           => __( 'Ratio', 'geodirectory' ),
				'type'            => 'select',
				'options'         => array(
					''      => __( 'Listings 50/50 Map', 'geodirectory' ),
					'60/40' => __( 'Listings 60/40 Map', 'geodirectory' ),
					'70/30' => __( 'Listings 70/30 Map', 'geodirectory' ),
					'40/60' => __( 'Listings 40/60 Map', 'geodirectory' ),
					'30/70' => __( 'Listings 30/70 Map', 'geodirectory' ),
				),
				'default'         => '',
				'desc_tip'        => true,
				'advanced'        => false,
				'group'           => __( 'Main Output', 'geodirectory' ),
				'element_require' => '[%output%]==""',
			),

		);

		$arguments['layout'] = array(
			'title'    => __( 'Layout', 'geodirectory' ),
			'desc'     => __( 'How the listings should laid out by default.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => geodir_get_layout_options(),
			'default'  => '2',
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Main Output', 'geodirectory' ),
		);

		$arguments['row_gap'] = array(
			'title'    => __( 'Card row gap', 'geodirectory' ),
			'desc'     => __( 'This adjusts the spacing between the cards horizontally.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'Default', 'geodirectory' ),
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
			),
			'default'  => '',
			'desc_tip' => false,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['column_gap'] = array(
			'title'    => __( 'Card column gap', 'geodirectory' ),
			'desc'     => __( 'This adjusts the spacing between the cards vertically.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'Default', 'geodirectory' ),
				'1' => '1',
				'2' => '2',
				'3' => '3',
				'4' => '4',
				'5' => '5',
			),
			'default'  => '',
			'desc_tip' => false,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['card_border'] = array(
			'title'    => __( 'Card border', 'geodirectory' ),
			'desc'     => __( 'Set the border style for the card.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''     => __( 'Default', 'geodirectory' ),
				'none' => __( 'None', 'geodirectory' ),
			) + geodir_aui_colors(),
			'default'  => '',
			'desc_tip' => false,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		$arguments['card_shadow'] = array(
			'title'    => __( 'Card shadow', 'geodirectory' ),
			'desc'     => __( 'Set the card shadow style.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'None', 'geodirectory' ),
				'small'  => __( 'Small', 'geodirectory' ),
				'medium' => __( 'Medium', 'geodirectory' ),
				'large'  => __( 'Large', 'geodirectory' ),
			),
			'default'  => '',
			'desc_tip' => false,
			'advanced' => false,
			'group'    => __( 'Card Design', 'geodirectory' ),
		);

		/*
		* Elementor Pro features below here
		*/
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$arguments['skin_id'] = array(
				'type'     => 'select',
				'title'    => __( 'Elementor Skin', 'geodirectory' ),
				'desc'     => '',
				'options'  => GeoDir_Elementor::get_elementor_pro_skins(),
				'default'  => '',
				'desc_tip' => false,
				'advanced' => false,
				'group'    => __( 'Elementor Skin', 'geodirectory' ),
			);

			$arguments['skin_column_gap'] = array(
				'type'     => 'number',
				'title'    => __( 'Skin column gap', 'geodirectory' ),
				'desc'     => __( 'The px value for the column gap.', 'geodirectory' ),
				'default'  => '30',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Elementor Skin', 'geodirectory' ),
			);

			$arguments['skin_row_gap'] = array(
				'type'     => 'number',
				'title'    => __( 'Skin row gap', 'geodirectory' ),
				'desc'     => __( 'The px value for the row gap.', 'geodirectory' ),
				'default'  => '35',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Elementor Skin', 'geodirectory' ),
			);
		}

		$arguments['height']    = array(
			'type'        => 'text',
			'title'       => __( 'Height:', 'geodirectory' ),
			'desc'        => __( 'This is the height of the map, you can use %, px or vh here. (static map requires px value)', 'geodirectory' ),
			'placeholder' => '100vh',
			'desc_tip'    => true,
			'default'     => '100vh',
			'advanced'    => false,
			'group'       => __( 'Map', 'geodirectory' ),
		);
		$arguments['maptype']   = array(
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
			'advanced' => false,
			'group'    => __( 'Map', 'geodirectory' ),
		);
		$arguments['all_posts'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Show all posts?', 'geodirectory' ),
			'desc'        => __( 'This displays all posts on map from archive page.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => false,
			'group'       => __( 'Map', 'geodirectory' ),
		);

		$arguments['sticky'] = array(
			'type'        => 'checkbox',
			'title'       => __( 'Enable sticky map?', 'geodirectory' ),
			'desc'        => __( 'When in the sidebar this will attempt to make it stick when scrolling on desktop.', 'geodirectory' ),
			'placeholder' => '',
			'desc_tip'    => true,
			'value'       => '1',
			'default'     => '0',
			'advanced'    => false,
			'group'       => __( 'Map', 'geodirectory' ),
		);

		if ( defined( 'GEODIR_MARKERCLUSTER_VERSION' ) ) {
			$arguments['marker_cluster'] = array(
				'type'        => 'checkbox',
				'title'       => __( 'Enable marker cluster?', 'geodirectory' ),
				'desc'        => __( 'This enables marker cluster on the map.', 'geodirectory' ),
				'placeholder' => '',
				'desc_tip'    => true,
				'value'       => '1',
				'default'     => '1',
				'advanced'    => false,
				'group'       => __( 'Map', 'geodirectory' ),
			);
		}

		return $arguments;
	}


	/**
	 * The Super block output function.
	 *
	 * @param array  $instance Settings for the current widget instance.
	 * @param array  $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $instance = array(), $widget_args = array(), $content = '' ) {
		global $aui_bs5, $gd_post, $post;

		$is_preview = $this->is_preview();

		$search       = ! empty( $instance['show_search'] ) ? "[gd_search post_type=''  post_type_hide='false'  hide_search_input='false'  hide_near_input='false'  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow=''  show='' ]" : '';
		$loop_actions = ! empty( $instance['show_loop_actions'] ) ? "[gd_loop_actions hide_layouts=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]" : '';
		$paging       = "[gd_loop_paging show_advanced=''  mid_size=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]";

		$args         = array(
			'layout'      => ! empty( $instance['layout'] ) ? absint( $instance['layout'] ) : '2',
			'row_gap'     => ! empty( $instance['row_gap'] ) ? absint( $instance['row_gap'] ) : '',
			'column_gap'  => ! empty( $instance['column_gap'] ) ? absint( $instance['column_gap'] ) : '',
			'card_border' => ! empty( $instance['card_border'] ) ? esc_attr( $instance['card_border'] ) : '',
			'card_shadow' => ! empty( $instance['card_shadow'] ) ? esc_attr( $instance['card_shadow'] ) : '',
		);
		$ele_pro_args = '';
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			$ele_pro_args .= ! empty( $instance['skin_id'] ) ? " skin_id='" . absint( $instance['skin_id'] ) . "'" : '';
			$ele_pro_args .= ! empty( $instance['skin_column_gap'] ) ? " skin_column_gap='" . absint( $instance['skin_column_gap'] ) . "'" : '';
			$ele_pro_args .= ! empty( $instance['skin_row_gap'] ) ? " skin_row_gap='" . absint( $instance['skin_row_gap'] ) . "'" : '';
		}
		$listings = $search . $loop_actions . "[gd_loop layout='" . esc_attr( $args['layout'] ) . "'  row_gap='" . esc_attr( $args['row_gap'] ) . "'  column_gap='" . esc_attr( $args['column_gap'] ) . "'  card_border='" . esc_attr( $args['card_border'] ) . "'  card_shadow='" . esc_attr( $args['card_shadow'] ) . "'  bg=''  mt=''  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' $ele_pro_args]" . $paging;

		$args           = array(
			'height'    => ! empty( $instance['height'] ) ? esc_attr( $instance['height'] ) : '',
			'maptype'   => ! empty( $instance['maptype'] ) ? esc_attr( $instance['maptype'] ) : '',
			'all_posts' => ! empty( $instance['all_posts'] ) ? esc_attr( $instance['all_posts'] ) : 'false',
			'sticky'    => 'false', //! empty( $instance['sticky'] ) ? esc_attr( $instance['sticky'] ) : 'false',
		);
		$sticky_map     = ! empty( $instance['sticky'] ) ? 'sticky-top' : '';
		$marker_cluster = '';
		if ( defined( 'GEODIR_MARKERCLUSTER_VERSION' ) ) {
			$marker_cluster = ! empty( $instance['marker_cluster'] ) ? " marker_cluster='" . esc_attr( $instance['marker_cluster'] ) . "'" : '';
		}
		$map = "[gd_map title=''  width='100%'  height='" . esc_attr( $args['height'] ) . "'  maptype='" . esc_attr( $args['maptype'] ) . "'  zoom='0'  map_type='auto'  post_settings='true'  post_type=''  terms=''  tick_terms=''  tags=''  all_posts='" . esc_attr( $args['all_posts'] ) . "'  post_id=''  search_filter='false'  post_type_filter='false'  cat_filter='false'  child_collapse='false'  map_directions='false'  scrollwheel='false'  hide_zoom_control='false'  hide_street_control='false'  sticky='" . esc_attr( $args['sticky'] ) . "'  static='false'  bg=''  mt=''  mr=''  mb=''  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' $marker_cluster]";
		if ( $is_preview ) {
			$map = '<div style="background-image:url(' . geodir_plugin_url() . '/assets/images/block-placeholder-map.png);width:100%;height:' . esc_attr( $args['height'] ) . '" />';
		}

		$map = "<div class='$sticky_map'>$map</div>";

		// ratios
		$ratios = ! empty( $instance['ratio'] ) ? explode( '/', $instance['ratio'] ) : array( 50, 50 );

		$listing_ratio = 'col-12 col-lg-6';
		$map_ratio     = 'col-12 col-lg-6';
		if ( 'map' === $instance['output'] || 'listings' === $instance['output'] ) {
			$listing_ratio = '';
			$map_ratio     = '';
		} elseif ( '60' === $ratios[0] ) {
			$listing_ratio = 'col-12 col-lg-7';
			$map_ratio     = 'col-12 col-lg-5';
		} elseif ( '70' === $ratios[0] ) {
			$listing_ratio = 'col-12 col-lg-8';
			$map_ratio     = 'col-12 col-lg-4';
		} elseif ( '40' === $ratios[0] ) {
			$listing_ratio = 'col-12 col-lg-5';
			$map_ratio     = 'col-12 col-lg-7';
		} elseif ( '30' === $ratios[0] ) {
			$listing_ratio = 'col-12 col-lg-4';
			$map_ratio     = 'col-12 col-lg-8';
		}
		// open wrappers
		$content = '<div class="bsui"><div class="container-fluid full-widthx"><div class="row">';

		// buttons
		$responsive_buttons = '';
		if ( empty( $instance['output'] ) ) {
			$responsive_buttons .= '<div class="d-lg-none mb-3">';
			$responsive_buttons .= '<button class="btn btn-primary w-100 gd-sa-button-map" onclick="' . esc_attr( 'jQuery( \'.gd-sa-list, .gd-sa-map, .gd-sa-button-map, .gd-sa-button-list\' ).toggleClass( \'d-none d-lg-block\' );if(!jQuery(this).hasClass(\'geodir-map-rendered\')){window.setTimeout(function(){if(jQuery.goMap.map){if(window.gdMaps==\'osm\'){jQuery.goMap.map._onResize();jQuery.goMap.map.invalidateSize()}else{google.maps.event.trigger(jQuery.goMap.map,\'resize\');}if(typeof keepBounds!=\'undefined\'&&keepBounds){jQuery.goMap.map.fitBounds(keepBounds);setZoom=jQuery.goMap.map.getZoom();if(setZoom>13){jQuery.goMap.map.setZoom(13)}}}},100);}jQuery(this).addClass(\'geodir-map-rendered\');' ) . '"> ' . __( 'Show Map', 'geodirectory' ) . ' <i class="fas fa-map-marked ' . ( $aui_bs5 ? 'ms-2' : 'ml-2' ) . '"></i></button>';
			$responsive_buttons .= '<button class="btn btn-primary w-100 gd-sa-button-list d-none"  onclick="' . esc_attr( 'jQuery( \'.gd-sa-list, .gd-sa-map, .gd-sa-button-map, .gd-sa-button-list\' ).toggleClass( \'d-none d-lg-block\' );jQuery(window).trigger(\'resize\');' ) . '">' . __( 'Show Listings', 'geodirectory' ) . ' <i class="fas fa-th-list ' . ( $aui_bs5 ? 'ms-2' : 'ml-2' ) . '"></i></button>';
			$responsive_buttons .= '</div>';
		}

		if ( empty( $instance['output'] ) ) {
			// responsive buttons
			$content .= '<div class="col col-12 gd-sa-buttons">' . $responsive_buttons . '</div>';

			$content .= '<div class="col ' . $listing_ratio . '  gd-sa-list">';
			$content .= $listings;
			$content .= '</div>';

			$content .= '<div class="col ' . $map_ratio . ' d-none d-lg-block gd-sa-map">';
			$content .= $map;
			$content .= '</div>';
		} elseif ( 'listings' === $instance['output'] ) {
			$content .= '<div class="col ' . $listing_ratio . '">';
			$content .= $listings;
			$content .= '</div>';
		} elseif ( 'map' === $instance['output'] ) {
			$content .= '<div class="col ' . $map_ratio . '">';
			$content .= $map;
			$content .= '</div>';
		}

		// close wrappers
		$content .= '</div></div></div>';

		// $content = do_blocks( $content );
		$content = do_shortcode( $content );

		return $content;
	}

	public function get_badge_options( $over_image = false ) {
		$badge_options = array(
			'none'     => __( 'None', 'geodirectory' ),
			'new'      => __( 'New', 'geodirectory' ),
			'featured' => __( 'Featured', 'geodirectory' ),
			'favorite' => __( 'Favorite', 'geodirectory' ),
			'category' => __( 'Category', 'geodirectory' ),
		// 'custom' => __( 'Custom', 'geodirectory' ),
		);

		if ( ! $over_image ) {
			$badge_options['rating']         = __( 'Rating', 'geodirectory' );
			$badge_options['business_hours'] = __( 'Business Hours', 'geodirectory' );

			$custom_fields = $this->get_custom_field_keys();

			if ( ! empty( $custom_fields ) ) {
				foreach ( $custom_fields as $key ) {
					$badge_options[ $key ] = $key;
				}
			}
		} else {
			$badge_options['custom'] = __( 'Custom', 'geodirectory' );
		}

		return $badge_options;
	}

	public function get_custom_badge( $position, $instance ) {

		$keys = array(
			'key',
			'condition',
			'search',
			'icon_class',
			'badge',
			'link',
			'type',
			'color',
			'bg_color',
			'txt_color',
		// '',
		);
		$args = array();

		foreach ( $keys as $key ) {
			$args[ $key ] = isset( $instance[ $position . '_badge_' . $key ] ) ? esc_attr( $instance[ $position . '_badge_' . $key ] ) : '';
		}

		$a = array( 'position' => str_replace( '_', '-', $position ) );

		return $this->get_badge_type( 'custom_badge', $a, $args );

	}

	public function get_badge_type( $type, $args = array(), $badge_args = array() ) {
		global $aui_bs5;

		$type = esc_attr( $type );

		$output = '';

		// position
		$position_args = '';
		$position      = ! empty( $args['position'] ) ? esc_attr( $args['position'] ) : '';
		$mb            = $type == 'favorite' ? 'n1' : '0';
		$mt            = $type == 'favorite' ? '0' : '1';
		if ( $position == 'top-left' ) {
			$position_args = " position='ab-top-left'  mt='$mt'  ml='1' ";
		} elseif ( $position == 'top-right' ) {
			$position_args = " position='ab-top-right'  mt='$mt'  mr='1' ";
		} elseif ( $position == 'bottom-left' ) {
			$position_args = " position='ab-bottom-left'  mt=''  mr=''  mb='$mb'  ml='1' ";
		} elseif ( $position == 'bottom-right' ) {
			$position_args = " position='ab-bottom-right'  mt=''  mr='1'  mb='$mb'  ml='' ";
		}

		// alignment
		$alignment = ! empty( $args['alignment'] ) ? esc_attr( $args['alignment'] ) : '';
		if ( $alignment ) {
			$alignment = '';// "alignment='".esc_attr($alignment)."'";
		}

		if ( $type == 'featured' ) {
			$output = "[gd_post_badge key='featured' condition='is_not_empty' badge='FEATURED' bg_color='#fd4700' txt_color='#ffffff' css_class='' $alignment $position_args]";
		} elseif ( $type == 'new' ) {
			$output = "[gd_post_badge id=''  key='post_date'  condition='is_less_than'  search='+30'  icon_class=''  badge='New'  link=''  new_window='false'  popover_title=''  popover_text=''  cta=''  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color=''  bg_color='#ff0000'  txt_color='#ffffff'  size=''  $alignment  mb=''  ml='' $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'favorite' ) {
			$output = "[gd_post_fav show='icon'  icon=''  icon_color_off='rgba(223,223,223,0.8)'  icon_color_on='#ff0000'  type='link'  shadow=''  color=''  bg_color=''  txt_color=''  size='h5'  $alignment $position_args  list_hide=''  list_hide_secondary='' ]";
		} elseif ( $type == 'price' ) {
			$output = "[gd_post_badge id=''  key='price'  condition='is_not_empty'  search=''  icon_class=''  badge='%%input%% '  link='%%post_url%%'  new_window='false'  popover_title=''  popover_text=''  cta=''  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color='danger'  bg_color='#0073aa'  txt_color='#ffffff'  size=''  $alignment $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'category' ) {
			$output = "[gd_post_badge id=''  key='default_category'  condition='is_not_empty'  search=''  icon_class=''  badge='%%input%%'  link='%%input%%'  new_window='false'  popover_title=''  popover_text=''  cta='0'  tooltip_text=''  hover_content=''  hover_icon=''  type=''  shadow=''  color=''  bg_color='rgba(0,0,0,0.5)'  txt_color='#ffffff'  size=''  $alignment $position_args  list_hide=''  list_hide_secondary=''  css_class='' ]";
		} elseif ( $type == 'rating' ) {
			$output = "[gd_post_rating show='stars'  size=''  $alignment  list_hide=''  list_hide_secondary='' ]";
		} elseif ( $type == 'business_hours' ) {
			$lhs    = $this->is_preview() ? '1' : '2';
			$output = "[gd_post_meta title=''  id=''  key='business_hours'  show=''  no_wrap='false'  $alignment text_alignment=''  list_hide=''  list_hide_secondary='$lhs'  location=''  css_class='' ]";
		} elseif ( $type == 'custom_badge' ) {
			$args_out = '';
			if ( $badge_args ) {
				foreach ( $badge_args as $key => $val ) {
					$args_out .= " $key='$val'";
				}
			}

			$output = "[gd_post_badge $args_out $alignment $position_args]";
		} else {
			$lhs       = $this->is_preview() ? '1' : '2';
			$show      = ! empty( $args['show'] ) ? esc_attr( $args['show'] ) : '';
			$css_class = '';
			if ( $show == 'badge' ) {
				$show      = 'value';
				$css_class = 'badge ' . ( $aui_bs5 ? 'bg-primary' : 'badge-primary' );
			}
			$output = "[gd_post_meta title=''  id=''  key='$type'  show='$show'  no_wrap='false'  $alignment text_alignment=''  list_hide=''  list_hide_secondary='$lhs'  location=''  css_class='$css_class' ]";
		}

		return $output;
	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_keys() {
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		$keys   = array();
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'];
			}
		}

		// Advance fields
		$advance_fields = geodir_post_meta_advance_fields();
		if ( ! empty( $advance_fields ) ) {
			foreach ( $advance_fields as $field => $args ) {
				$keys[ $field ] = $field;
			}
		}

		return $keys;
	}

	public function image_badge( $type = 'top_left' ) {

		if ( $type == 'top_left' ) {
			$title   = __( 'Top Left Badge', 'geodirectory' );
			$group   = __( 'Image Badge (top left)', 'geodirectory' );
			$default = 'featured';
		} elseif ( $type == 'top_right' ) {
			$title   = __( 'Top Right Badge', 'geodirectory' );
			$group   = __( 'Image Badge (top right)', 'geodirectory' );
			$default = 'new';
		} elseif ( $type == 'bottom_left' ) {
			$title   = __( 'Bottom Left Badge', 'geodirectory' );
			$group   = __( 'Image Badge (bottom left)', 'geodirectory' );
			$default = 'category';
		} elseif ( $type == 'bottom_right' ) {
			$title   = __( 'Bottom Right Badge', 'geodirectory' );
			$group   = __( 'Image Badge (bottom right)', 'geodirectory' );
			$default = 'favorite';
		} else {
			return array();
		}

		$badge                            = array();
		$badge[ $type . '_badge_preset' ] = array(
			'title'           => $title,
			'desc'            => __( 'Select the badge to show', 'geodirectory' ),
			'type'            => 'select',
			'options'         => $this->get_badge_options( true ),
			'default'         => $default,
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%image_type%]!="none"',
		);

		$badge[ $type . '_badge_key' ] = array(
			'type'            => 'select',
			'title'           => __( 'Field Key:', 'geodirectory' ),
			'desc'            => __( 'This is the custom field key.', 'geodirectory' ),
			'placeholder'     => '',
			'options'         => $this->get_custom_field_keys(),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);

		$badge[ $type . '_badge_condition' ]  = array(
			'type'            => 'select',
			'title'           => __( 'Field condition:', 'geodirectory' ),
			'desc'            => __( 'Select the custom field condition.', 'geodirectory' ),
			'placeholder'     => '',
			'options'         => $this->get_badge_conditions(),
			'default'         => 'is_equal',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_search' ]     = array(
			'type'            => 'text',
			'title'           => __( 'Value to match:', 'geodirectory' ),
			'desc'            => __( 'Match this text with field value to display post badge. For post date enter value like +7 or -7.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_condition%]!="is_empty" && [%' . $type . '_badge_condition%]!="is_not_empty"',
		);
		$badge[ $type . '_badge_icon_class' ] = array(
			'type'            => 'text',
			'title'           => __( 'Icon class:', 'geodirectory' ),
			'desc'            => __( 'You can show a font-awesome icon here by entering the icon class.', 'geodirectory' ),
			'placeholder'     => 'fas fa-award',
			'default'         => '',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_badge' ]      = array(
			'type'            => 'text',
			'title'           => __( 'Badge:', 'geodirectory' ),
			'desc'            => __( 'Badge text. Ex: FOR SALE. Leave blank to show field title as a badge, or use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_link' ]       = array(
			'type'            => 'text',
			'title'           => __( 'Link url:', 'geodirectory' ),
			'desc'            => __( 'Badge link url. You can use this to make the button link to something, %%input%% can be used here if a link or %%post_url%% for the post url.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);

		$badge[ $type . '_badge_type' ]      = array(
			'title'           => __( 'Type', 'geodirectory' ),
			'desc'            => __( 'Select the badge type.', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				''     => __( 'Badge', 'geodirectory' ),
				'pill' => __( 'Pill', 'geodirectory' ),
			),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_color' ]     = array(
			'title'           => __( 'Badge Color', 'geodirectory' ),
			'desc'            => __( 'Select the the badge color.', 'geodirectory' ),
			'type'            => 'select',
			'options'         => array(
				'' => __( 'Custom colors', 'geodirectory' ),
			) + geodir_aui_colors( true ),
			'default'         => '',
			'desc_tip'        => true,
			'advanced'        => false,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom"',
		);
		$badge[ $type . '_badge_bg_color' ]  = array(
			'type'            => 'color',
			'title'           => __( 'Badge background color:', 'geodirectory' ),
			'desc'            => __( 'Color for the badge background.', 'geodirectory' ),
			'placeholder'     => '',
			'default'         => '#0073aa',
			'desc_tip'        => true,
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_color%]==""',
		);
		$badge[ $type . '_badge_txt_color' ] = array(
			'type'            => 'color',
			'title'           => __( 'Badge text color:', 'geodirectory' ),
			'desc'            => __( 'Color for the badge text.', 'geodirectory' ),
			'placeholder'     => '',
			'desc_tip'        => true,
			'default'         => '#ffffff',
			'group'           => $group,
			'element_require' => '[%' . $type . '_badge_preset%]=="custom" && [%' . $type . '_badge_color%]==""',
		);

		return $badge;
	}

	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions() {
		$conditions = array(
			'is_equal'        => __( 'is equal', 'geodirectory' ),
			'is_not_equal'    => __( 'is not equal', 'geodirectory' ),
			'is_greater_than' => __( 'is greater than', 'geodirectory' ),
			'is_less_than'    => __( 'is less than', 'geodirectory' ),
			'is_empty'        => __( 'is empty', 'geodirectory' ),
			'is_not_empty'    => __( 'is not empty', 'geodirectory' ),
			'is_contains'     => __( 'is contains', 'geodirectory' ),
			'is_not_contains' => __( 'is not contains', 'geodirectory' ),
		);

		return apply_filters( 'geodir_badge_conditions', $conditions );
	}

}

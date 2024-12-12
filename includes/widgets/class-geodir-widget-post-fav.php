<?php
/**
 * GeoDirectory Detail Rating Stars Widget
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDir_Widget_Post_Fav class.
 *
 * @since 2.0.0
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Fav extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'       => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'       => 'fas fa-heart',
			'block-category'   => 'geodirectory',
			'block-keywords'   => "['fav','geo','geodir']",
			'block-supports'   => array(
				'customClassName' => false,
			),
			'class_name'       => __CLASS__,
			'base_id'          => 'gd_post_fav', // this us used as the widget id and the shortcode id.
			'name'             => __( 'GD > Post Favorite', 'geodirectory' ), // the name of the widget.
			'widget_ops'       => array(
				'classname'                   => 'geodir-post-fav ' . geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'This shows a GD post favorite link.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
				'gd_wgt_showhide'             => 'show_on',
				'gd_wgt_restrict'             => array( 'gd-detail' ),
			),
			'no_wrap'          => true,
			'block-wrap'       => '',
			'block_edit_wrap_tag' => 'span',
			'block_group_tabs' => array(
				'content'  => array(
					'groups' => array(
						__( 'Output', 'geodirectory' ),
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
						__( 'Icon', 'geodirectory' ),
						__( 'Design', 'geodirectory' ),
						__( 'Positioning', 'geodirectory' ),
						__( 'Grid Visibility', 'geodirectory' ),
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
	 * Set the arguments later.
	 *
	 * @return array
	 */
	public function set_arguments() {
		$arguments    = array();
		$design_style = geodir_design_style();

		$arguments['show'] = array(
			'name'     => 'show',
			'title'    => __( 'Show:', 'geodirectory' ),
			'desc'     => __( 'What part of the post meta to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''     => __( 'All', 'geodirectory' ),
				'icon' => __( 'Icon', 'geodirectory' ),
				'text' => __( 'Text', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Output', 'geodirectory' ),
		);

		$arguments['icon'] = array(
			'type'        => 'text',
			'title'       => __( 'Icon class (font-awesome)', 'geodirectory' ),
			'desc'        => __( 'FontAwesome icon class to use.', 'geodirectory' ),
			'placeholder' => 'fas fa-heart',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Icon', 'geodirectory' ),

		);
		$arguments['icon_color_off'] = array(
			'type'        => 'color',
			'title'       => __( 'Icon color off', 'geodirectory' ),
			'desc'        => __( 'Color for the icon when not set.', 'geodirectory' ),
			'placeholder' => '',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Icon', 'geodirectory' ),
		);
		$arguments['icon_color_on']  = array(
			'type'        => 'color',
			'title'       => __( 'Icon color on', 'geodirectory' ),
			'desc'        => __( 'Color for the icon when set.', 'geodirectory' ),
			'placeholder' => '',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'       => __( 'Icon', 'geodirectory' ),
		);

		if ( $design_style ) {
			$arguments['type'] = array(
				'title'    => __( 'Type', 'geodirectory' ),
				'desc'     => __( 'Select the badge type.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'link'  => __( 'Link', 'geodirectory' ),
					'badge' => __( 'Badge', 'geodirectory' ),
					'pill'  => __( 'Pill', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Design', 'geodirectory' ),
			);

			$arguments['color'] = array(
				'title'    => __( 'Badge Color', 'geodirectory' ),
				'desc'     => __( 'Select the the badge color.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					'' => __( 'Custom colors', 'geodirectory' ),
				) + geodir_aui_colors( true ),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( 'Design', 'geodirectory' ),
			);

			$arguments['bg_color']  = array(
				'type'            => 'color',
				'title'           => __( 'Badge background color:', 'geodirectory' ),
				'desc'            => __( 'Color for the badge background.', 'geodirectory' ),
				'placeholder'     => '',
				'default'         => '',
				'desc_tip'        => true,
				'group'           => __( 'Design', 'geodirectory' ),
				'element_require' => $design_style ? '[%color%]==""' : '',
			);
			$arguments['txt_color'] = array(
				'type'            => 'color',
				'title'           => __( 'Badge text color:', 'geodirectory' ),
				'desc'            => __( 'Color for the badge text.', 'geodirectory' ),
				'placeholder'     => '',
				'desc_tip'        => true,
				'default'         => '',
				'group'           => __( 'Design', 'geodirectory' ),
				'element_require' => $design_style ? '[%color%]=="" && [%show%]!="icon"' : '',
			);
			$arguments['size'] = sd_get_font_size_input(
				'font_size',
				array(
					'type'     => 'select',
					'title'    => __( 'Badge size:', 'geodirectory' ),
					'desc'     => __( 'Size of the badge.', 'geodirectory' ),
					'default'  => 'h5',
					'desc_tip' => true,
					'group'    => __( 'Design', 'geodirectory' ),
				)
			);
		}

		// Alignment

		$arguments['alignment'] = array(
			'name'     => 'alignment',
			'title'    => __( 'Alignment:', 'geodirectory' ),
			'desc'     => __( 'How the item should be positioned on the page.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''       => __( 'None', 'geodirectory' ),
				'left'   => __( 'Left', 'geodirectory' ),
				'center' => __( 'Center', 'geodirectory' ),
				'right'  => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( 'Positioning', 'geodirectory' ),
		);

		if ( $design_style ) {
			$arguments['position'] = array(
				'type'     => 'select',
				'title'    => __( 'Absolute Positioning', 'geodirectory' ),
				'desc'     => __( 'Set an absolute position for floating badges over other content.', 'geodirectory' ),
				'options'  => array(
					''                      => __( 'None', 'geodirectory' ),
					'ab-left'               => __( 'Left', 'geodirectory' ),
					'ab-left-angle'         => __( 'Left angle', 'geodirectory' ),
					'ab-top-left'           => __( 'Top left', 'geodirectory' ),
					'ab-bottom-left'        => __( 'Bottom left', 'geodirectory' ),
					'ab-top-left-angle'     => __( 'Top left angle', 'geodirectory' ),
					'ab-bottom-left-angle'  => __( 'Bottom left angle', 'geodirectory' ),
					'ab-right'              => __( 'Right', 'geodirectory' ),
					'ab-right-angle'        => __( 'Right angle', 'geodirectory' ),
					'ab-top-right'          => __( 'Top right', 'geodirectory' ),
					'ab-bottom-right'       => __( 'Bottom right', 'geodirectory' ),
					'ab-top-right-angle'    => __( 'Top right angle', 'geodirectory' ),
					'ab-bottom-right-angle' => __( 'Bottom right angle', 'geodirectory' ),
				),
				'desc_tip' => true,
				'group'    => __( 'Positioning', 'geodirectory' ),
			);

			//          $arguments['mt'] = geodir_get_sd_margin_input( 'mt' );
			//          $arguments['mr'] = geodir_get_sd_margin_input( 'mr' );
			//          $arguments['mb'] = geodir_get_sd_margin_input( 'mb' );
			//          $arguments['ml'] = geodir_get_sd_margin_input( 'ml' );
		}

		// Grid visibility
		$arguments['list_hide'] = array(
			'title'    => __( 'Hide item on view:', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the item will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),

		);
		$arguments['list_hide_secondary'] = array(
			'title'    => __( 'Hide secondary info on view', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the secondary info such as label will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				''  => __( 'None', 'geodirectory' ),
				'2' => __( 'Grid view 2', 'geodirectory' ),
				'3' => __( 'Grid view 3', 'geodirectory' ),
				'4' => __( 'Grid view 4', 'geodirectory' ),
				'5' => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( 'Grid Visibility', 'geodirectory' ),
		);

		// margins mobile
		$arguments['mt'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Mobile' ) );
		$arguments['mr'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Mobile' ) );
		$arguments['mb'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Mobile' ) );
		$arguments['ml'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Mobile' ) );

		// margins tablet
		$arguments['mt_md'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Tablet' ) );
		$arguments['mr_md'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Tablet' ) );
		$arguments['mb_md'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Tablet' ) );
		$arguments['ml_md'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Tablet' ) );

		// margins desktop
		$arguments['mt_lg'] = sd_get_margin_input( 'mt', array( 'device_type' => 'Desktop' ) );
		$arguments['mr_lg'] = sd_get_margin_input( 'mr', array( 'device_type' => 'Desktop' ) );
		$arguments['mb_lg'] = sd_get_margin_input( 'mb', array( 'device_type' => 'Desktop' ) );
		$arguments['ml_lg'] = sd_get_margin_input( 'ml', array( 'device_type' => 'Desktop' ) );

		// padding
		$arguments['pt'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Mobile' ) );
		$arguments['pr'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Mobile' ) );
		$arguments['pb'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Mobile' ) );
		$arguments['pl'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Mobile' ) );

		// padding tablet
		$arguments['pt_md'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Tablet' ) );
		$arguments['pr_md'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Tablet' ) );
		$arguments['pb_md'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Tablet' ) );
		$arguments['pl_md'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Tablet' ) );

		// padding desktop
		$arguments['pt_lg'] = sd_get_padding_input( 'pt', array( 'device_type' => 'Desktop' ) );
		$arguments['pr_lg'] = sd_get_padding_input( 'pr', array( 'device_type' => 'Desktop' ) );
		$arguments['pb_lg'] = sd_get_padding_input( 'pb', array( 'device_type' => 'Desktop' ) );
		$arguments['pl_lg'] = sd_get_padding_input( 'pl', array( 'device_type' => 'Desktop' ) );

		// border
		$arguments['border']         = sd_get_border_input( 'border' );
		$arguments['border_type']    = sd_get_border_input( 'type' );
		$arguments['border_width']   = sd_get_border_input( 'width' ); // BS5 only
		$arguments['border_opacity'] = sd_get_border_input( 'opacity' ); // BS5 only
		$arguments['rounded']        = sd_get_border_input( 'rounded' );
		$arguments['rounded_size']   = sd_get_border_input( 'rounded_size' );

		// shadow
		$arguments['shadow'] = sd_get_shadow_input( 'shadow' );

		$arguments['display']    = sd_get_display_input( 'd', array( 'device_type' => 'Mobile' ) );
		$arguments['display_md'] = sd_get_display_input( 'd', array( 'device_type' => 'Tablet' ) );
		$arguments['display_lg'] = sd_get_display_input( 'd', array( 'device_type' => 'Desktop' ) );

		$arguments['css_class'] = sd_get_class_input();

		return $arguments;
	}


	/**
	 * The Super block output function.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $aui_bs5, $post;

		$defaults = array(
			'show'           => '', // icon, text
			'alignment'      => '', // left, center, right
			'icon_color_off' => '',
			'icon_color_on'  => '',
			'icon'           => '',
			'type'           => 'link',
			'shadow'         => '',
			'color'          => '',
			'bg_color'       => '',
			'txt_color'      => '',
			'size'           => 'h5',
			'position'       => '',
			'mt'             => '',
			'mb'             => '',
			'mr'             => '',
			'ml'             => '',
			'list_hide'		 => '',
			'list_hide_secondary'		 => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		// if badge color not empty then blank the custom colors
		if ( ! empty( $args['color'] ) ) {
			$args['bg_color']  = '';
			$args['txt_color'] = '';
		}

		$design_style = geodir_design_style();

		$class = '';

		// set alignment class
		if ( $args['alignment'] != '' ) {
			if ( $design_style ) {
				if ( $args['alignment'] == 'block' ) {
					$class .= ' d-block ';} elseif ( $args['alignment'] == 'left' ) {
					$class .= ' float-left mr-2 ';} elseif ( $args['alignment'] == 'right' ) {
						$class .= ' float-right ml-2 ';} elseif ( $args['alignment'] == 'center' ) {
						$class .= ' text-center ';}
			} else {
				$class .= $args['alignment'] == 'block' ? ' gd-d-block gd-clear-both ' : ' geodir-align-' . sanitize_html_class( $args['alignment'] );
			}
		}

		if ( $args['show'] == 'icon' ) {
			$class .= ' gd-fav-hide-text ';
		} elseif ( $args['show'] == 'text' ) {
			$class .= ' gd-fav-hide-stars ';
		}

		$design_style = geodir_design_style();

		// set list_hide class
		if ( $args['list_hide'] == '2' ) {
			$class .= $design_style ? ' gv-hide-2 ' : ' gd-lv-2 ';
		}
		if ( $args['list_hide'] == '3' ) {
			$class .= $design_style ? ' gv-hide-3 ' : ' gd-lv-3 ';
		}
		if ( $args['list_hide'] == '4' ) {
			$class .= $design_style ? ' gv-hide-4 ' : ' gd-lv-4 ';
		}
		if ( $args['list_hide'] == '5' ) {
			$class .= $design_style ? ' gv-hide-5 ' : ' gd-lv-5 ';
		}

		// set list_hide_secondary class
		if ( $args['list_hide_secondary'] == '2' ) {
			$class .= $design_style ? ' gv-hide-s-2 ' : ' gd-lv-s-2 ';
		}
		if ( $args['list_hide_secondary'] == '3' ) {
			$class .= $design_style ? ' gv-hide-s-3 ' : ' gd-lv-s-3 ';
		}
		if ( $args['list_hide_secondary'] == '4' ) {
			$class .= $design_style ? ' gv-hide-s-4 ' : ' gd-lv-s-4 ';
		}
		if ( $args['list_hide_secondary'] == '5' ) {
			$class .= $design_style ? ' gv-hide-s-5 ' : ' gd-lv-s-5 ';
		}

		// set positioning class
		if ( ! empty( $args['position'] ) ) {
			$class .= sanitize_html_class( $args['position'] );
		}

		// margins
		if ( ! empty( $args['mt'] ) ) {
			$class .= ' mt-' . sanitize_html_class( $args['mt'] ) . ' '; }
		if ( ! empty( $args['mr'] ) ) {
			$class .= ' mr-' . sanitize_html_class( $args['mr'] ) . ' '; }
		if ( ! empty( $args['mb'] ) ) {
			$class .= ' mb-' . sanitize_html_class( $args['mb'] ) . ' '; }
		if ( ! empty( $args['ml'] ) ) {
			$class .= ' ml-' . sanitize_html_class( $args['ml'] ) . ' '; }

		if ( $design_style ) {
			$class .= ' ' . sd_build_aui_class( $args );
		}

		$before = '<div class="geodir_post_meta gd-fav-info-wrap ' . $class . '" >';
		$after  = '</div>';

		$main = $this->get_fav_html( $args );

		return $before . $main . $after;
	}

	/**
	 * Get favorite list html.
	 *
	 * @since 2.0.0
	 *
	 * @return string Favorite Html.
	 */
	public function get_fav_html( $args = array() ) {
		global $gd_post;

		ob_start();
		?>
		<span class="gd-list-favorite">
			<?php geodir_favourite_html( '', ( ! empty( $gd_post ) ? $gd_post->ID : 0 ), $args ); ?>
		</span>
		<?php
		return ob_get_clean();
	}


}

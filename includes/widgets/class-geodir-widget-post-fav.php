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
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'admin-site',
			'block-category' => 'geodirectory',
			'block-keywords' => "['fav','geo','geodir']",

			'class_name' => __CLASS__,
			'base_id'    => 'gd_post_fav', // this us used as the widget id and the shortcode id.
			'name'       => __( 'GD > Post Favorite', 'geodirectory' ), // the name of the widget.
			'widget_ops' => array(
				'classname'                   => 'geodir-post-fav '.geodir_bsui_class(),
				// widget class
				'description'                 => esc_html__( 'This shows a GD post favorite link.', 'geodirectory' ),
				// widget description
				'customize_selective_refresh' => true,
				'geodirectory'                => true,
				'gd_wgt_showhide'             => 'show_on',
				'gd_wgt_restrict'             => array( 'gd-detail' ),
			),
			'arguments'  => array()

		);

		$design_style = geodir_design_style();
		
		$arguments = array();

		$arguments['show']                = array(
			'name'     => 'show',
			'title'    => __( 'Show:', 'geodirectory' ),
			'desc'     => __( 'What part of the post meta to show.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				""     => __( 'All', 'geodirectory' ),
				"icon" => __( 'Icon', 'geodirectory' ),
				"text" => __( 'Text', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'     => __("Design","geodirectory")
		);

		$arguments['icon']                = array(
			'type'        => 'text',
			'title'       => __( 'Icon class (font-awesome)', 'geodirectory' ),
			'desc'        => __( 'FontAwesome icon class to use.', 'geodirectory' ),
			'placeholder' => 'fas fa-heart',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'     => __("Design","geodirectory")

		);
		$arguments['icon_color_off']      = array(
			'type'        => 'color',
			'title'       => __( 'Icon color off', 'geodirectory' ),
			'desc'        => __( 'Color for the icon when not set.', 'geodirectory' ),
			'placeholder' => '',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'     => __("Design","geodirectory"),
		);
		$arguments['icon_color_on']       = array(
			'type'        => 'color',
			'title'       => __( 'Icon color on', 'geodirectory' ),
			'desc'        => __( 'Color for the icon when set.', 'geodirectory' ),
			'placeholder' => '',
			'default'     => '',
			'desc_tip'    => true,
			'advanced'    => true,
			'group'     => __("Design","geodirectory"),
		);



		if ( $design_style ) {
			$arguments['type'] = array(
				'title'    => __( 'Type', 'geodirectory' ),
				'desc'     => __( 'Select the badge type.', 'geodirectory' ),
				'type'     => 'select',
				'options'  => array(
					"link"  => __( 'Link', 'geodirectory' ),
					"badge" => __( 'Badge', 'geodirectory' ),
					"pill"  => __( 'Pill', 'geodirectory' ),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'    => __( "Design", "geodirectory" )
			);

			$arguments['shadow'] = array(
				'title' => __('Shadow', 'geodirectory'),
				'desc' => __('Select the shadow badge type.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"small" => __('small', 'geodirectory'),
					"medium" => __('medium', 'geodirectory'),
					"large" => __('large', 'geodirectory'),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			$arguments['color'] = array(
				'title' => __('Badge Color', 'geodirectory'),
				'desc' => __('Select the the badge color.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					                "" => __('Custom colors', 'geodirectory'),
				                )+geodir_aui_colors(true),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
			);

			$arguments['bg_color']  = array(
				'type' => 'color',
				'title' => __('Badge background color:', 'geodirectory'),
				'desc' => __('Color for the badge background.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory"),
				'element_require' => $design_style ?  '[%color%]==""' : '',
			);
			$arguments['txt_color']  = array(
				'type' => 'color',
				'title' => __('Badge text color:', 'geodirectory'),
				'desc' => __('Color for the badge text.', 'geodirectory'),
				'placeholder' => '',
				'desc_tip' => true,
				'default'  => '',
				'group'     => __("Design","geodirectory"),
				'element_require' => $design_style ?  '[%color%]=="" && [%show%]!="icon"' : '',
			);
			$arguments['size']  = array(
				'type' => 'select',
				'title' => __('Badge size:', 'geodirectory'),
				'desc' => __('Size of the badge.', 'geodirectory'),
				'options' =>  array(
					"" => __('Inherit', 'geodirectory'),
					"h5" => __('h5', 'geodirectory'),
					"h4" => __('h4', 'geodirectory'),
					"h3" => __('h3', 'geodirectory'),
					"h2" => __('h2', 'geodirectory'),
					"h1" => __('h1', 'geodirectory'),
				),
				'default' => 'h5',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
		}
		

		// Alignment

		$arguments['alignment']           = array(
			'name'     => 'alignment',
			'title'    => __( 'Alignment:', 'geodirectory' ),
			'desc'     => __( 'How the item should be positioned on the page.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				""       => __( 'None', 'geodirectory' ),
				"left"   => __( 'Left', 'geodirectory' ),
				"center" => __( 'Center', 'geodirectory' ),
				"right"  => __( 'Right', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => false,
			'group'    => __( "Positioning", "geodirectory" )
		);

		if($design_style) {
			$arguments['position'] = array(
				'type'     => 'select',
				'title'    => __( 'Absolute Positioning', 'geodirectory' ),
				'desc'     => __( 'Set an absolute position for floating badges over other content.', 'geodirectory' ),
				'options'  => array(
					""                      => __( 'None', 'geodirectory' ),
					"ab-left"               => __( 'Left', 'geodirectory' ),
					"ab-left-angle"         => __( 'Left angle', 'geodirectory' ),
					"ab-top-left"           => __( 'Top left', 'geodirectory' ),
					"ab-bottom-left"        => __( 'Bottom left', 'geodirectory' ),
					"ab-top-left-angle"     => __( 'Top left angle', 'geodirectory' ),
					"ab-bottom-left-angle"  => __( 'Bottom left angle', 'geodirectory' ),
					"ab-right"              => __( 'Right', 'geodirectory' ),
					"ab-right-angle"        => __( 'Right angle', 'geodirectory' ),
					"ab-top-right"          => __( 'Top right', 'geodirectory' ),
					"ab-bottom-right"       => __( 'Bottom right', 'geodirectory' ),
					"ab-top-right-angle"    => __( 'Top right angle', 'geodirectory' ),
					"ab-bottom-right-angle" => __( 'Bottom right angle', 'geodirectory' ),
				),
				'desc_tip' => true,
				'group'    => __( "Positioning", "geodirectory" )
			);

			$arguments['mt']  = geodir_get_sd_margin_input('mt');
			$arguments['mr']  = geodir_get_sd_margin_input('mr');
			$arguments['mb']  = geodir_get_sd_margin_input('mb');
			$arguments['ml']  = geodir_get_sd_margin_input('ml');
		}

		// Grid visibility
		$arguments['list_hide']           = array(
			'title'    => __( 'Hide item on view:', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the item will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				""  => __( 'None', 'geodirectory' ),
				"2" => __( 'Grid view 2', 'geodirectory' ),
				"3" => __( 'Grid view 3', 'geodirectory' ),
				"4" => __( 'Grid view 4', 'geodirectory' ),
				"5" => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( "Grid Visibility", "geodirectory" )

		);
		$arguments['list_hide_secondary'] = array(
			'title'    => __( 'Hide secondary info on view', 'geodirectory' ),
			'desc'     => __( 'You can set at what view the secondary info such as label will become hidden.', 'geodirectory' ),
			'type'     => 'select',
			'options'  => array(
				""  => __( 'None', 'geodirectory' ),
				"2" => __( 'Grid view 2', 'geodirectory' ),
				"3" => __( 'Grid view 3', 'geodirectory' ),
				"4" => __( 'Grid view 4', 'geodirectory' ),
				"5" => __( 'Grid view 5', 'geodirectory' ),
			),
			'desc_tip' => true,
			'advanced' => true,
			'group'    => __( "Grid Visibility", "geodirectory" )
		);

		$options['arguments'] = $arguments;

		parent::__construct( $options );
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
		global $post;

		$defaults = array(
			'show'           => '', // icon, text
			'alignment'      => '', // left, center, right
			'icon_color_off' => '',
			'icon_color_on'  => '',
			'icon'           => '',
			'type'           => 'link',
			'shadow'           => '',
			'color'           => '',
			'bg_color'           => '',
			'txt_color'           => '',
			'size'           => 'h5',
			'position'           => '',
			'mt'    => '',
			'mb'    => '',
			'mr'    => '',
			'ml'    => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		// if badge color not empty then blank the custom colors
		if(!empty($args['color'])){
			$args['bg_color'] = '';
			$args['txt_color'] = '';
		}

		$design_style = geodir_design_style();

		$class = '';

		// set alignment class
		if ( $args['alignment'] != '' ) {
			if($design_style){
				if($args['alignment']=='block'){$class .= " d-block ";}
				elseif($args['alignment']=='left'){$class .= " float-left mr-2 ";}
				elseif($args['alignment']=='right'){$class .= " float-right ml-2 ";}
				elseif($args['alignment']=='center'){$class .= " text-center ";}
			}else{
				$class .= $args['alignment']=='block' ? " gd-d-block gd-clear-both " : " geodir-align-" . sanitize_html_class( $args['alignment'] );
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
			$class .= $design_style ? " gv-hide-2 " : " gd-lv-2 ";
		}
		if ( $args['list_hide'] == '3' ) {
			$class .= $design_style ? " gv-hide-3 " : " gd-lv-3 ";
		}
		if ( $args['list_hide'] == '4' ) {
			$class .= $design_style ? " gv-hide-4 " : " gd-lv-4 ";
		}
		if ( $args['list_hide'] == '5' ) {
			$class .= $design_style ? " gv-hide-5 " : " gd-lv-5 ";
		}

		// set list_hide_secondary class
		if ( $args['list_hide_secondary'] == '2' ) {
			$class .= $design_style ? " gv-hide-s-2 " : " gd-lv-s-2 ";
		}
		if ( $args['list_hide_secondary'] == '3' ) {
			$class .= $design_style ? " gv-hide-s-3 " : " gd-lv-s-3 ";
		}
		if ( $args['list_hide_secondary'] == '4' ) {
			$class .= $design_style ? " gv-hide-s-4 " : " gd-lv-s-4 ";
		}
		if ( $args['list_hide_secondary'] == '5' ) {
			$class .= $design_style ? " gv-hide-s-5 " : " gd-lv-s-5 ";
		}

		// set positioning class
		if(!empty($args['position'])){
			$class .= sanitize_html_class( $args['position'] );
		}

		// margins
		if ( !empty( $args['mt'] ) ) { $class .= " mt-".sanitize_html_class($args['mt'])." "; }
		if ( !empty( $args['mr'] ) ) { $class .= " mr-".sanitize_html_class($args['mr'])." "; }
		if ( !empty( $args['mb'] ) ) { $class .= " mb-".sanitize_html_class($args['mb'])." "; }
		if ( !empty( $args['ml'] ) ) { $class .= " ml-".sanitize_html_class($args['ml'])." "; }

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

<?php

/**
 * GeoDir_Widget_Post_Title class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Directions extends WP_Super_Duper {


	public $arguments;
	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'minus',
			'block-wrap'    => '',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['directions','geo','geodir']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_post_directions', // this us used as the widget id and the shortcode id.
			'name'          => __('GD > Directions','geodirectory'), // the name of the widget.
			'widget_ops'    => array(
				'classname'   => 'geodir-post-directions '.geodir_bsui_class(), // widget class
				'description' => esc_html__('This shows a link to map directions to the current post.','geodirectory'), // widget description
				'geodirectory' => true,
			),
		);


		$design_style = geodir_design_style();

		if($design_style){
			$arguments['badge']  = array(
				'type' => 'text',
				'title' => __('Text', 'geodirectory'),
				'desc' => __('Enter the text for the button.', 'geodirectory'),
				'placeholder' => esc_attr__( 'Get Directions', 'geodirectory' ),
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
			);
			$arguments['icon_class']  = array(
				'type' => 'text',
				'title' => __('Icon class:', 'geodirectory'),
				'desc' => __('You can show a font-awesome icon here by entering the icon class.', 'geodirectory'),
				'placeholder' => 'fas fa-location-arrow',
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);


			$arguments['type'] = array(
				'title' => __('Type', 'geodirectory'),
				'desc' => __('Select the badge type.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('Badge', 'geodirectory'),
					"pill" => __('Pill', 'geodirectory'),
				),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false,
				'group'     => __("Design","geodirectory")
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
				'default' => '#0073aa',
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
				'default'  => '#ffffff',
				'group'     => __("Design","geodirectory"),
				'element_require' => $design_style ?  '[%color%]==""' : '',
			);
			$arguments['size']  = array(
				'type' => 'select',
				'title' => __('Badge size:', 'geodirectory'),
				'desc' => __('Size of the badge.', 'geodirectory'),
				'options' =>  array(
					"" => __('h6', 'geodirectory'),
					"h5" => __('h5', 'geodirectory'),
					"h4" => __('h4', 'geodirectory'),
					"h3" => __('h3', 'geodirectory'),
					"h2" => __('h2', 'geodirectory'),
					"h1" => __('h1', 'geodirectory'),

				),
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['alignment']  = array(
				'type' => 'select',
				'title' => __('Alignment:', 'geodirectory'),
				'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"left" => __('Left', 'geodirectory'),
					"center" => __('Center', 'geodirectory'),
					"right" => __('Right', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['list_hide']  = array(
				'title' => __('Hide item on view:', 'geodirectory'),
				'desc' => __('You can set at what view the item will become hidden.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"2" => __('Grid view 2', 'geodirectory'),
					"3" => __('Grid view 3', 'geodirectory'),
					"4" => __('Grid view 4', 'geodirectory'),
					"5" => __('Grid view 5', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['list_hide_secondary']  = array(
				'title' => __('Hide secondary info on view', 'geodirectory'),
				'desc' => __('You can set at what view the secondary info such as label will become hidden.', 'geodirectory'),
				'type' => 'select',
				'options'   =>  array(
					"" => __('None', 'geodirectory'),
					"2" => __('Grid view 2', 'geodirectory'),
					"3" => __('Grid view 3', 'geodirectory'),
					"4" => __('Grid view 4', 'geodirectory'),
					"5" => __('Grid view 5', 'geodirectory'),
				),
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);
			$arguments['css_class']  = array(
				'type' => 'text',
				'title' => __('Extra class:', 'geodirectory'),
				'desc' => __('Give the wrapper an extra class so you can style things as you want.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'group'     => __("Design","geodirectory")
			);

			$options['arguments'] = $arguments;

		}


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
	public function output($args = array(), $widget_args = array(),$content = ''){
		global $gd_post,$geodirectory;

		ob_start();

		$lat = ! empty( $gd_post->latitude ) ? esc_attr( $gd_post->latitude ) : '';
		$lon = ! empty( $gd_post->longitude ) ? esc_attr( $gd_post->longitude ) : '';

		if ( geodir_is_block_demo() && ! $lat && ! $lon ) {
			$default_location = $geodirectory->location->get_default_location();
			$lat = $default_location->latitude;
			$lon = $default_location->longitude;
		}

		if ( $lat && $lon ) {
			// Default options
			$defaults = array(
				'badge' => esc_attr__( 'Get Directions', 'geodirectory' ),
				'icon_class' => 'fas fa-location-arrow',
				'color' => '',
			);

			/**
			 * Parse incoming $args into an array and merge it with $defaults
			 */
			$args = wp_parse_args( $args, $defaults );

			// set defaults
			if(empty($args['badge'])){$args['badge'] = $defaults['badge'];}
			if(empty($args['icon_class'])){$args['icon_class'] = $defaults['icon_class'];}

			$design_style = geodir_design_style();
			if($design_style){
				// set list_hide class
				if($args['list_hide']=='2'){$args['css_class'] .= $design_style ? " gv-hide-2 " : " gd-lv-2 ";}
				if($args['list_hide']=='3'){$args['css_class'] .= $design_style ? " gv-hide-3 " : " gd-lv-3 ";}
				if($args['list_hide']=='4'){$args['css_class'] .= $design_style ? " gv-hide-4 " : " gd-lv-4 ";}
				if($args['list_hide']=='5'){$args['css_class'] .= $design_style ? " gv-hide-5 " : " gd-lv-5 ";}

				// set list_hide_secondary class
				if($args['list_hide_secondary']=='2'){$args['css_class'] .= $design_style ? " gv-hide-s-2 " : " gd-lv-s-2 ";}
				if($args['list_hide_secondary']=='3'){$args['css_class'] .= $design_style ? " gv-hide-s-3 " : " gd-lv-s-3 ";}
				if($args['list_hide_secondary']=='4'){$args['css_class'] .= $design_style ? " gv-hide-s-4 " : " gd-lv-s-4 ";}
				if($args['list_hide_secondary']=='5'){$args['css_class'] .= $design_style ? " gv-hide-s-5 " : " gd-lv-s-5 ";}

				$design_style = geodir_design_style();
				if(!empty($args['size'])){
					switch ($args['size']) {
						case 'small':
							$args['size'] = $design_style ? '' : 'small';
							break;
						case 'medium':
							$args['size'] = $design_style ? 'h4' : 'medium';
							break;
						case 'large':
							$args['size'] = $design_style ? 'h2' : 'large';
							break;
						case 'extra-large':
							$args['size'] = $design_style ? 'h1' : 'extra-large';
							break;
						case 'h6': $args['size'] = 'h6';break;
						case 'h5': $args['size'] = 'h5';break;
						case 'h4': $args['size'] = 'h4';break;
						case 'h3': $args['size'] = 'h3';break;
						case 'h2': $args['size'] = 'h2';break;
						case 'h1': $args['size'] = 'h1';break;
						default:
							$args['size'] = '';
					}
				}

				// set the link
				$args['link'] = 'https://maps.google.com/?daddr='.esc_attr($lat).','. esc_attr($lon);
				$args['new_window'] = true;

				echo geodir_get_post_badge( $gd_post->ID, $args );
			} else {
			?>
			<div class="geodir_post_meta  geodir_get_directions" style="clear:both;">
				<span class="geodir_post_meta_icon geodir-i-address" style=""><i class="fas fa-location-arrow" aria-hidden="true"></i></span>
				<span class="geodir_post_meta_title">
					<a href="https://maps.google.com/?daddr=<?php echo esc_attr($lat);?>,<?php echo esc_attr($lon);?>"
					target="_blank"><?php esc_attr_e( 'Get Directions', 'geodirectory' ); ?></a>
				</span>
			</div>
			<?php
			}
		}

		$output = ob_get_clean();

		if ( ! empty( $output ) && ! empty( $gd_post ) ) {
			$output = geodir_post_address( $output, 'gd_post_directions', $gd_post );
		}

		return $output;
	}

}


<?php
/**
 * GeoDirectory Widget: GeoDir_Widget_Map_Pinpoint class
 *
 * @package GeoDirectory
 *
 * @since 2.0.0
 */

/**
 * Core class used to implement a Map Pinpoint widget.
 *
 * @since 2.0.0
 *
 */
class GeoDir_Widget_Map_Pinpoint extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc.
     *
     * @since 2.0.0
	 */
	public function __construct() {

		$options = array(
			'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'    => 'admin-site',
			'block-category'=> 'geodirectory',
			'block-keywords'=> "['post','map','pinpoint']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_map_pinpoint',
			'name'          => __( 'GD > Map Pinpoint', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-map-pinpoint '.geodir_bsui_class(),
				'description' => esc_html__( 'Shows a link that will open the map marker window on the map.', 'geodirectory' ),
				'geodirectory' => true,
			),
			'arguments'     => array(
                'show'  => array(
                    'name' => 'show',
                    'title' => __('Show:', 'geodirectory'),
                    'desc' => __('What part of the post meta to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('All', 'geodirectory'),
                        "icon" => __('Icon', 'geodirectory'),
                        "text" => __('Text', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'alignment'  => array(
                    'name' => 'alignment',
                    'title' => __('Alignment:', 'geodirectory'),
                    'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('None', 'geodirectory'),
                        "left" => __('Left', 'geodirectory'),
                        "center" => __('Center', 'geodirectory'),
                        "right" => __('Right', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => false
                ),
            )
		);


		$design_style = geodir_design_style();

		if($design_style){
			$arguments['badge']  = array(
				'type' => 'text',
				'title' => __('Text', 'geodirectory'),
				'desc' => __('Enter the text for the button.', 'geodirectory'),
				'placeholder' => esc_attr__( 'Pinpoint', 'geodirectory' ),
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
			);
			$arguments['icon_class']  = array(
				'type' => 'text',
				'title' => __('Icon class:', 'geodirectory'),
				'desc' => __('You can show a font-awesome icon here by entering the icon class.', 'geodirectory'),
				'placeholder' => 'fas fa-map-marker-alt',
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
	 * Outputs the the content.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $wp_query, $gd_post,$gdecs_render_loop;

		$post_id = isset($gd_post->ID) ? $gd_post->ID : 0;

		if ( !$this->is_block_content_call() ) {
			// skip some checks for elementor
			if($gdecs_render_loop || (is_admin())){
				if ( empty( $gd_post->default_category ) ) {
					return;
				}
			}
			else{
				if ( ! ( ! empty( $wp_query ) && $wp_query->is_main_query() && ! empty( $gd_post ) ) ) {
					return;
				}

				if ( empty( $gd_post->default_category ) ) {
					return;
				}

				// Location-less
				if ( ! GeoDir_Post_types::supports( $gd_post->post_type, 'location' ) || geodir_is_page( 'detail' ) ) {
					return;
				}
			}

		}

		$design_style = geodir_design_style();

		$defaults = array(
            'show' => '',
            'alignment' => '',
            'badge' => esc_attr__( 'Pinpoint', 'geodirectory' ),
            'icon_class' => 'fas fa-map-marker-alt',
        );

		$args = wp_parse_args( $args, $defaults );

		// set defaults
		if(empty($args['badge'])){$args['badge'] = $defaults['badge'];}


		$term_icon = get_term_meta( $gd_post->default_category, 'ct_cat_font_icon', true );
		if ( empty( $term_icon ) ) {
			$term_icon = 'fas fa-map-marker-alt';
		}


		ob_start();

		if($design_style){

			if(empty($args['icon_class'])){
				$args['icon_class'] = $term_icon;
			}

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
			$args['link'] = '#open-marker';
			$args['onclick'] = "if(typeof openMarker=='function'){openMarker('listing_map_canvas' ,'$post_id')}";

			$args['extra_attributes'] = array(
				'onmouseover' => "if(typeof animate_marker=='function'){animate_marker('listing_map_canvas' ,'$post_id')}",
				'onmouseout' => "if(typeof stop_marker_animation=='function'){stop_marker_animation('listing_map_canvas' ,'$post_id')}",
			);

			echo geodir_get_post_badge( $post_id, $args );
		}else{
			?>
			<a href="javascript:void(0)" class="geodir-pinpoint-target"
			   onclick="if(typeof openMarker=='function'){openMarker('listing_map_canvas' ,'<?php echo $post_id; ?>')}"
			   onmouseover="if(typeof animate_marker=='function'){animate_marker('listing_map_canvas' ,'<?php echo $post_id; ?>')}"
			   onmouseout="if(typeof stop_marker_animation=='function'){stop_marker_animation('listing_map_canvas' ,'<?php echo $post_id; ?>')}" >
				<?php if ( $args['show'] != 'text' ) { ?>
					<span class="geodir-pinpoint-icon"><i class="<?php echo esc_attr( $term_icon ); ?>" aria-hidden="true"></i> </span>
				<?php } if ( $args['show'] != 'icon' ) { ?>
					<span class="geodir-pinpoint-text"><?php _e( 'Pinpoint', 'geodirectory' ); ?></span>
				<?php } ?>
			</a>
			<?php
		}
		$content = ob_get_clean();


		if ( empty( $content ) ) {
			return;
		}

		$class = '';
        if($args['alignment']=='left'){
            $class = "geodir-align-left";
        }elseif($args['alignment']=='center'){
            $class = "geodir-align-center";
        }elseif($args['alignment']=='right'){
            $class = "geodir-align-right";
        }

        if($args['show']=='icon'){
            $class .= ' gd-pinpoint-hide-text ';
        }elseif($args['show']=='text'){
            $class .= ' gd-pinpoint-hide-icon ';
        }

		$before = '<div class="geodir_post_meta gd-pinpoint-info-wrap '. $class .'" >';
        $after  = '</div>';



        return $before . $content . $after;
	}

	/**
     * Get pinpoint html.
     *
     * @since 2.0.0
     *
     * @return string Pinpoint html.
	 * @deprecated 2.1.0
     */
    public function get_pinpoint_html( $gd_post, $args ) {

    }

}
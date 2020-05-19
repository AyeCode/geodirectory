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
			'block-category'=> 'widgets',
			'block-keywords'=> "['post','map','pinpoint']",
			'class_name'    => __CLASS__,
			'base_id'       => 'gd_map_pinpoint',
			'name'          => __( 'GD > Map Pinpoint', 'geodirectory' ),
			'widget_ops'    => array(
				'classname'   => 'geodir-map-pinpoint',
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



		$defaults = array(
            'show' => '',
            'alignment' => '',
        );

		$args = wp_parse_args( $args, $defaults );

        $content = $this->get_pinpoint_html( $gd_post, $args );

		if ( empty( $content ) ) {
			return;
		}

		$class = '';
        if($args['alignment']=='left'){
            $class = "gd-align-left";
        }elseif($args['alignment']=='center'){
            $class = "gd-align-center";
        }elseif($args['alignment']=='right'){
            $class = "gd-align-right";
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
     */
    public function get_pinpoint_html( $gd_post, $args ) {
        $term_icon = get_term_meta( $gd_post->default_category, 'ct_cat_font_icon', true );
		if ( empty( $term_icon ) ) {
			$term_icon = 'fas fa-map-marker-alt';
		}

        ob_start();
        ?>
		<a href="javascript:void(0)" class="geodir-pinpoint-target" 
		   onclick="if(typeof openMarker=='function'){openMarker('listing_map_canvas' ,'<?php echo $gd_post->ID; ?>')}"
		   onmouseover="if(typeof animate_marker=='function'){animate_marker('listing_map_canvas' ,'<?php echo $gd_post->ID; ?>')}"
		   onmouseout="if(typeof stop_marker_animation=='function'){stop_marker_animation('listing_map_canvas' ,'<?php echo $gd_post->ID; ?>')}" >
			<?php if ( $args['show'] != 'text' ) { ?>
				<span class="geodir-pinpoint-icon"><i class="<?php echo esc_attr( $term_icon ); ?>" aria-hidden="true"></i> </span>
			<?php } if ( $args['show'] != 'icon' ) { ?>
				<span class="geodir-pinpoint-text"><?php _e( 'Pinpoint', 'geodirectory' ); ?></span>
			<?php } ?>
		</a>
        <?php
        return ob_get_clean();
    }

}
<?php
/**
* GeoDirectory Detail Rating Stars Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Post_Rating class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Output_Location extends WP_Super_Duper {

    public $arguments;
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['output','geo','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_output_location', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Output Location','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-output-location', // widget class
                'description' => esc_html__('This can be used to output many custom fields in one location.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_wgt_showhide' => 'show_on',
                'gd_wgt_restrict' => array( 'gd-detail' ),
            ),
            'arguments'     => array(
                'location'  => array(
                    'title' => __('Location:', 'geodirectory'),
                    'desc' => __('The location type to output.', 'geodirectory'),
                    'type' => 'select',
                    'options' => $this->show_in_locations(),
                    'desc_tip' => true,
                    'advanced' => false
                )
            )

        );
        
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

        $defaults = array(
            'location'      => '', //
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );
        if(!empty($args['location'])){
            $args['location'] = str_replace(array('[',']'),'',$args['location']);
        }

        $output = '';

        if (!empty($args['location']) && $geodir_post_detail_fields = geodir_show_listing_info($args['location'])) {
            $output .= "<div class='geodir-output-location geodir-output-location-".esc_attr($args['location'])."' >";
            $output .= $geodir_post_detail_fields;
            $output .= "</div>";
        }

        return $output;

    }

	public function show_in_locations() {
		$locations = GeoDir_Settings_Cpt_Cf::show_in_locations();

		$show_in_locations = array();
		foreach ( $locations as $value => $label ) {
			$value = str_replace( array( '[', ']' ), '', $value );
			$show_in_locations[ $value ] = $label;
		}

		return $show_in_locations;
	}
}

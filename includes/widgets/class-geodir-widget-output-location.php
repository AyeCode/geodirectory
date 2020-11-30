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
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['output','geo','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_output_location', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Output Location','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-output-location '.geodir_bsui_class(), // widget class
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

	    $design_style = geodir_design_style();

	    if($design_style){
		    $options['arguments']['list_style'] = array(
			    'title' => __('List style', 'geodirectory'),
			    'desc' => __('Select the list style', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "wrap" => __('Wrap with lines', 'geodirectory'),
				    "line" => __('Line separators', 'geodirectory'),
				    "none" => __('None', 'geodirectory'),
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );
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

        $defaults = array(
	        'location'      => '', //
	        'list_style'      => 'wrap', //
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );
        if(!empty($args['location'])){
            $args['location'] = str_replace(array('[',']'),'',$args['location']);
        }

	    $wrap_class = '';
	    $inner_class = '';
	    $wrap_style = '';
	    $design_style = geodir_design_style();
	    if($design_style){
		    if(empty($args['list_style'])){$args['list_style'] = $defaults['list_style'];}

		    if($args['list_style']=='wrap'){
			    $wrap_class = 'list-group';
		    }elseif($args['list_style']=='line'){
			    $wrap_class = 'list-group list-group-flush';
		    }

		    if($args['location']=='mapbubble'){
			    $wrap_class = '';
		    }elseif($args['location']=='listing'){
			    $wrap_style .= "clear:both;";
			    $wrap_class .= " mx-n2 ";

			    if($args['list_style']=='wrap'){
				    $inner_class .= ' border-left-0 border-right-0 rounded-0 px-2';
			    }

		    }

	    }

	    $output = '';

	    $geodir_post_detail_fields = geodir_show_listing_info($args['location']);

	    if(! $geodir_post_detail_fields && $design_style  && $this->is_preview() ){
		    $geodir_post_detail_fields = $this->get_dummy_data();
	    }

        if (!empty($args['location']) && $geodir_post_detail_fields ) {
	        if($geodir_post_detail_fields && $design_style && $wrap_class){
		        $geodir_post_detail_fields = str_replace("geodir_post_meta ","geodir_post_meta list-group-item list-group-item-action ".$inner_class.' ',$geodir_post_detail_fields);
	        }
            $output .= "<div class='$wrap_class d-block geodir-output-location geodir-output-location-".esc_attr($args['location'])."' style='$wrap_style' >";
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

	/**
	 * Get some dummy data if empty.
	 * 
	 * @return string
	 */
	public function get_dummy_data(){
		return ' <div href="#" class="geodir_post_meta ">Demo item 1</div>
  <div href="#" class="geodir_post_meta ">Demo item 2</div>';
	}
}

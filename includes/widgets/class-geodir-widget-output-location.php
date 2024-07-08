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
                    'default'  => 'detail',
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

		    // item padding
		    $options['arguments']['item_py'] = array(
			    'title' => __('Item vertical padding', 'geodirectory'),
			    'desc' => __('The padding between items', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('Default', 'geodirectory'),
				    "0" => "0",
				    "1" => "1",
				    "2" => "2",
				    "3" => "3",
				    "4" => "4",
				    "5" => "5",
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );

		    // text alignment
		    $options['arguments']['text_align'] = geodir_get_sd_text_align_input(array('group'     => __( "Design", "geodirectory" )));

		    // margins
		    $options['arguments']['mt']  = geodir_get_sd_margin_input('mt');
		    $options['arguments']['mr']  = geodir_get_sd_margin_input('mr');
		    $options['arguments']['mb']  = geodir_get_sd_margin_input('mb');
		    $options['arguments']['ml']  = geodir_get_sd_margin_input('ml');

		    // padding
		    $options['arguments']['pt']  = geodir_get_sd_padding_input('pt');
		    $options['arguments']['pr']  = geodir_get_sd_padding_input('pr');
		    $options['arguments']['pb']  = geodir_get_sd_padding_input('pb');
		    $options['arguments']['pl']  = geodir_get_sd_padding_input('pl');


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
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $aui_bs5;

		$defaults = array(
			'location' => '',
			'list_style' => 'wrap',
			'item_py' => '',
			'text_align' => '',
			'mt' => '',
			'mr' => '',
			'mb' => '',
			'ml' => '',
			'pt' => '',
			'pr' => '',
			'pb' => '',
			'pl' => '',
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );
		if ( ! empty( $args['location'] ) ) {
			$args['location'] = str_replace( array( '[', ']' ), '', $args['location'] );
		}

		$wrap_class = '';
		$inner_class = '';
		$wrap_style = '';
		$design_style = geodir_design_style();

		if ( $design_style ) {
			$inner_class = 'list-group-item list-group-item-action';

			if ( empty( $args['list_style'] ) ) {
				$args['list_style'] = $defaults['list_style'];
			}

			if ( $args['list_style'] == 'wrap' ) {
				$wrap_class = 'list-group';
			} else if ( $args['list_style'] == 'line' ) {
				$wrap_class = 'list-group list-group-flush';
			} else if ( $args['list_style'] == 'none' ) {
				$inner_class = '';
			}

			if ( $args['location'] == 'mapbubble' ) {
				$wrap_class = '';
			} else if ( $args['location'] == 'listing' ) {
				$wrap_style .= "clear:both;";
				$wrap_class .= " mx-n2 ";

				if ( $args['list_style'] == 'wrap' ) {
					$inner_class .= ( $aui_bs5 ? ' border-start-0 border-end-0' : ' border-left-0 border-right-0' ) . ' rounded-0 px-2';
				} else if ( $args['list_style'] == 'none' ) {
					$wrap_class = '';
				}
			}

			if ( ! empty( $args['item_py'] ) || $args['item_py'] == '0' ) {
				$inner_class .= ' py-' . absint( $args['item_py'] );
			}

			// wrapper class
			$wrap_class .= " " . geodir_build_aui_class( $args );
		}

		$output = '';
		$geodir_post_detail_fields = geodir_show_listing_info( $args['location'] );

		if ( ! $geodir_post_detail_fields && $design_style  && $this->is_preview() ) {
			$geodir_post_detail_fields = $this->get_dummy_data();
		}

		if ( ! empty( $args['location'] ) && $geodir_post_detail_fields ) {
			if ( $geodir_post_detail_fields && $design_style && $inner_class ) {
				$geodir_post_detail_fields = str_replace( "geodir_post_meta ", "geodir_post_meta " . $inner_class . ' ', $geodir_post_detail_fields );
			}

			$output .= "<div class='$wrap_class d-block geodir-output-location geodir-output-location-" . esc_attr( $args['location'] ) . "' style='$wrap_style'>";
			$output .= $geodir_post_detail_fields;
			$output .= "</div>";
		}

		return $output;
	}

	public function show_in_locations() {
		$locations = geodir_show_in_locations();

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

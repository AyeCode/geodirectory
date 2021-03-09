<?php
/**
 * GeoDirectory cpt category description widget.
 *
 * @package GeoDirectory
 * @since 2.0.0
 */

/**
 * GeoDirectory category description widget class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Category_Description extends WP_Super_Duper {

    /**
     * Register the category description with WordPress.
     *
     * @since 2.0.0
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['categories','geo','taxonomy']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_category_description', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Category Description','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-category-description-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the current category description text.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_wgt_showhide' => 'show_on',
                'gd_wgt_restrict' => array( 'gd-listing' ),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
            )
        );

	    $design_style = geodir_design_style();

	    if($design_style) {

		    // background
		    $arguments['bg']  = geodir_get_sd_background_input('mt');

		    // margins
		    $arguments['mt']  = geodir_get_sd_margin_input('mt');
		    $arguments['mr']  = geodir_get_sd_margin_input('mr');
		    $arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
		    $arguments['ml']  = geodir_get_sd_margin_input('ml');

		    // padding
		    $arguments['pt']  = geodir_get_sd_padding_input('pt');
		    $arguments['pr']  = geodir_get_sd_padding_input('pr');
		    $arguments['pb']  = geodir_get_sd_padding_input('pb');
		    $arguments['pl']  = geodir_get_sd_padding_input('pl');

		    // border
		    $arguments['border']  = geodir_get_sd_border_input('border');
		    $arguments['rounded']  = geodir_get_sd_border_input('rounded');
		    $arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

		    // shadow
		    $arguments['shadow']  = geodir_get_sd_shadow_input('shadow');

		    $options['arguments'] = $options['arguments'] + $arguments;

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


        ob_start();

	    $is_preview = $this->is_preview();

	    if(geodir_is_page( 'archive' )){
		    $current_category = get_queried_object();
		    $term_id = isset($current_category->term_id) ?  absint($current_category->term_id) : '';
		    if($term_id){
			    $design_style = geodir_design_style();
			    if($design_style){

				    // wrap class
				    $wrap_class = geodir_build_aui_class($args);

				    echo "<div class='$wrap_class'><p>";
			    }
			    echo geodir_get_cat_top_description( $term_id );
			    if($design_style){
				    echo "</p></div>";
			    }
		    }
	    }elseif($is_preview){
		    // wrap class
		    $wrap_class = geodir_build_aui_class($args);
		    echo $this->get_dummy_description($wrap_class);
	    }

        return ob_get_clean();
    }

	/**
	 * Get placeholder text for the category description.
	 *
	 * @return string
	 */
	public function get_dummy_description($wrap_class){
		return "<div class='$wrap_class'><p class='p-0 m-0'>".__("<b>This is a placeholder</b> for the category description. You set the description under each categories settings, when on the relevant category page the text will show here.","geodirectory") ."</p></div>";
	}


}
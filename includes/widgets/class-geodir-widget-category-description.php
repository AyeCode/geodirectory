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
            'block-category'=> 'widgets',
            'block-keywords'=> "['categories','geo','taxonomy']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_category_description', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Category Description','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-category-description-container', // widget class
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

	    if(geodir_is_page( 'archive' )){
		    $current_category = get_queried_object();
		    $term_id = isset($current_category->term_id) ?  absint($current_category->term_id) : '';
		    if($term_id){
			    echo geodir_get_cat_top_description( $term_id );
		    }
	    }

        return ob_get_clean();
    }


}
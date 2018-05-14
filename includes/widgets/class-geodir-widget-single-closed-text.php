<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Closed_Text extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     * @since 2.0.0
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['category','taxonomies','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_closed_text', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Closed Text','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-taxonomies-container', // widget class
                'description' => esc_html__('Shows a closed down warning text if a post has the closed status.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
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
        global $post;
        ob_start();
        if ( geodir_post_is_closed( $post ) ) {
            geodir_post_closed_text( $post );
        }

        return ob_get_clean();
    }

}
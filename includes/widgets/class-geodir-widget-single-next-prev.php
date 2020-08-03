<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Single_Next_Prev extends WP_Super_Duper {

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
            'block-keywords'=> "['next','prev','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_next_prev', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Next Prev','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-taxonomies-container bsui', // widget class
                'description' => esc_html__('Shows the current post`s next and previous post links on the details page.','geodirectory'), // widget description
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

        $design_style = geodir_design_style();
        $template = $design_style ? $design_style."/single/next-prev.php" : "legacy/single/next-prev.php";
        return geodir_get_template_html( $template, $args );

    }

}
<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Actions extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['loop','actions','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop_actions', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop Actions','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-actions-container', // widget class
                'description' => esc_html__('Shows the archive loop actions such as sort by and grid view,  only used on Archive template page, usually above `gd_loop`.','geodirectory'), // widget description
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

        ob_start();
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search')){
            geodir_loop_actions();
        }
        return ob_get_clean();
    }

}
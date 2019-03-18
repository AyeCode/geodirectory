<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop_Paging extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['loop','paging','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop_paging', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop Paging','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-paging-container', // widget class
                'description' => esc_html__('Shows the pagination links if the current query has multiple pages of results.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'show_advanced'  => array(
                    'title' => __('Show Advanced pagination:', 'geodirectory'),
                    'desc' => __('This will add extra pagination info like `Showing listings x-y of z` before/after pagination.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('Never', 'geodirectory'),
                        "before" => __('Before', 'geodirectory'),
                        "after" => __('After', 'geodirectory'),
                    ),
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
        global $geodir_is_widget_listing;

		$defaults = array(
            'show_advanced' => '',
        );
        $args = wp_parse_args( $args, $defaults );
        if(!empty($args['show_advanced'])){
            global $gd_advanced_pagination;
            $gd_advanced_pagination = $args['show_advanced'];
        }
        ob_start();
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || $geodir_is_widget_listing){
            geodir_loop_paging();
        }
        return ob_get_clean();
    }

}
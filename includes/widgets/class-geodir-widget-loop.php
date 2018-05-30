<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['loop','archive','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-container', // widget class
                'description' => esc_html__('Shows the current posts from the main WP query according to the URL.  This is only used on the `GD Archive template` page.  It loops through each post and outputs the `GD Archive Item` template.','geodirectory'), // widget description
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
        global $wp_query;

        ob_start();
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || (is_author() && !empty($wp_query->query['gd_favs'])) ){

            // check if we have listings or if we are faking it
            if($wp_query->post_count == 1 && empty($wp_query->posts)){
                geodir_no_listings_found();
            }elseif(geodir_is_page('search') && !isset($_REQUEST['geodir_search'])){
                geodir_no_listings_found();
            }else{
                geodir_get_template_part('content', 'archive-listing');
            }
        }else{
            _e("No listings found that match your selection.","geodirectory");
        }

        return ob_get_clean();
    }

}
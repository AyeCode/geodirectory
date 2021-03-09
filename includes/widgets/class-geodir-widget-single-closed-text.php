<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 * @deprecated 2.0.0.38 use GeoDir_Widget_Single_Notifications instead
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
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['category','taxonomies','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_closed_text', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Closed Text','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-taxonomies-container '.geodir_bsui_class(), // widget class
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
        if(current_user_can('administrator')){
            // warning
            $notifications['gd-single-closed-deprecated-warning'] = array(
                'type'  =>  'warning',
                'note'  =>  wp_sprintf( __( 'ADMIN NOTICE: The `gd_single_closed_text` shortcode has been deprecated and should be replaced with `gd_notifications` shortcode/block/widget.', 'geodirectory' ), 'warning' )
            );
            echo geodir_notification($notifications);
        }
        if ( geodir_post_is_closed( $post ) ) {
            geodir_post_closed_text( $post );
        }

        return ob_get_clean();
    }

}
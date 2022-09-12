<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Single_Reviews.
 *
 * @since 2.0.0.63
 */
class GeoDir_Widget_Single_Reviews extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     * @since 2.0.0.63
     */
    public function __construct() {


        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['geo','reviews','comments']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_single_reviews', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Single Reviews','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-single-reviews-container '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Shows the comment/reviews area for a single post. (this will remove any further instances of the comments section on the page)','geodirectory'), // widget description
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
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        global $post;

        ob_start();

        if ( geodir_is_page( 'single' ) ) {
            do_action( 'geodir_single_reviews_widget_content_before' );

            comments_template();

            do_action( 'geodir_single_reviews_widget_content_after' );
        }

        return ob_get_clean();
    }

}

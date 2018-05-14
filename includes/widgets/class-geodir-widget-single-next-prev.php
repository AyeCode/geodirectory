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
                'classname'   => 'geodir-single-taxonomies-container', // widget class
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
        ob_start();
        ?>
        <div class="geodir-pos_navigation clearfix">
            <div class="geodir-post_left"><?php previous_post_link('%link', '' . __('Previous', 'geodirectory'), false) ?></div>
            <div class="geodir-post_right"><?php next_post_link('%link', __('Next', 'geodirectory') . '', false) ?></div>
        </div>
        <?php
        return ob_get_clean();
    }

}
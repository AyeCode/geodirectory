<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Advanced Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Advance_Search extends WP_Widget {
    
    /**
     * Register the advanced search widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir_advance_search_widget',
            'description' => __( 'GD > Search', 'geodirectory' ),
            'post_type' => '',
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        
        parent::__construct( 'geodir_advance_search', __('GD > Search', 'geodirectory'), $widget_ops );
    }


    /**
     * Front-end display content for advanced search widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        /**
         * Filter the search widget arguments.
         *
         * @since 1.5.7
         * @param array $args The widget arguments.
         * @param array $instance The widget instance.
         */
        $args = apply_filters('widget_geodir_advance_search_args',$args,$instance);

        // prints the widget
        extract($args, EXTR_SKIP);

        if(isset($post_type) && $post_type){
            geodir_get_search_post_type($post_type);// set the post type
        }else{
            geodir_get_search_post_type();// set the post type
        }

        echo $before_widget;

        /**
         * Filter the widget title text.
         *
         * @since 1.0.0
         * @global object $current_user Current user object.
         * @param string $title The widget title text.
         */
        $title = empty($instance['title']) ? __('Search', 'geodirectory') : apply_filters('widget_title', __($instance['title'], 'geodirectory'));

        geodir_get_template_part('listing', 'filter-form');

        echo $after_widget;

        // after outputing the search reset the CPT
        global $geodir_search_post_type;
        $geodir_search_post_type = '';
    }

    /**
     * Sanitize advanced search widget form values as they are saved.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update($new_instance, $old_instance) {
        // Save the widget
        return isset($instance) ? $instance : array();
    }

    /**
     * Back-end advanced search widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        echo '<p>' . __("This is a search widget to show advance search for gedodirectory listings.", 'geodirectory') . '</p>';
    }
}
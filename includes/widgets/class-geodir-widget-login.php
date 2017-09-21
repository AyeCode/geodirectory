<?php
/**
* GeoDirectory Login Widget
*
* @since 1.0.0
*
* @package GeoDirectory
*/

/**
 * Login Widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Login extends WP_Widget {
    
    /**
     * Register the login widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir_loginbox',
            'description' => __( 'Geodirectory Loginbox Widget', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        parent::__construct( 'geodir_loginbox', __( 'GD > Loginbox', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for login widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        geodir_loginwidget_output($args, $instance);
    }

    /**
     * Sanitize login widget form values as they are saved.
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
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);

        return $instance;
    }

    /**
     * Back-end login widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'desc1' => ''));
        $title = strip_tags($instance['title']);

        ?>
        <p><label
                for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'geodirectory'); ?>
                : <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                         name="<?php echo $this->get_field_name('title'); ?>" type="text"
                         value="<?php echo esc_attr($title); ?>"/></label></p>


    <?php
    }
}
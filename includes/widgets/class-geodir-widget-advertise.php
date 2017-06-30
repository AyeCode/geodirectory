<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory advertise widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Advertise extends WP_Widget {

    /**
     * Register the advertise widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'GeoDirectory Advertise',
            'description' => __( 'GD > common advertise widget in sidebar, bottom section', 'geodirectory' )
        );
        
        parent::__construct( 'advtwidget', __( 'GD > Advertise', 'geodirectory' ), $widget_ops );
    }


    /**
     * Front-end display content for advertise widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance) {
        // prints the widget
        extract($args, EXTR_SKIP);

        /**
         * Filter the description text.
         *
         * @since 1.0.0
         * @param string $desc1 The widget description text.
         */
        $desc1 = empty($instance['desc1']) ? '&nbsp;' : apply_filters('widget_desc1', $instance['desc1']);
        echo $before_widget;
        ?>
        <?php if ($desc1 <> "") { ?>
        <?php echo $desc1; ?>
    <?php }
        echo $after_widget;
    }

    /**
     * Sanitize advertise widget form values as they are saved.
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
        $instance['desc1'] = ($new_instance['desc1']);
        return $instance;
    }

    /**
     * Back-end advertise widget settings form.
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

        $desc1 = ($instance['desc1']);
        ?>
        <p><label
                for="<?php echo $this->get_field_id('desc1'); ?>"><?php _e('Your Advt code (ex.google adsense, etc.)', 'geodirectory');?>
                <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('desc1'); ?>"
                          name="<?php echo $this->get_field_name('desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label>
        </p>

    <?php
    }
}
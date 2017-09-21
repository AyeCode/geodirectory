<?php

/**
 * GeoDirectory Flickr widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Flickr extends WP_Widget {

    /**
     * Register the flickr widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'Geo Dir Flickr Photos ',
            'description' => __( 'GD > Flickr Photos', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        parent::__construct( 'widget_flickrwidget', __( 'GD > Flickr Photos', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for flickr widget.
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

        echo $before_widget;

        /** This filter is documented in geodirectory_widgets.php */
        $id = empty($instance['id']) ? '&nbsp;' : apply_filters('widget_id', $instance['id']);

        /**
         * Filter the widget number.
         *
         * This is used in the flicker widget to show how many images to show.
         *
         * @since 1.0.0
         * @param string $number The image count.
         */
        $number = empty($instance['number']) ? '&nbsp;' : apply_filters('widget_number', $instance['number']);
        echo $before_title . __('Photo Gallery', 'geodirectory') . $after_title;
        ?>

        <div class="geodir-flickr clearfix">

            <script type="text/javascript"
                    src="http://www.flickr.com/badge_code_v2.gne?count=<?php echo $number; ?>&amp;display=latest&amp;size=s&amp;layout=x&amp;source=user&amp;user=<?php echo $id; ?>"></script>

        </div>


        <?php echo $after_widget;
    }

    /**
     * Sanitize flickr widget form values as they are saved.
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
        $instance['id'] = strip_tags($new_instance['id']);
        $instance['number'] = strip_tags($new_instance['number']);
        return $instance;
    }

    /**
     * Back-end flickr widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 'id' => '', 'number' => ''));
        $id = strip_tags($instance['id']);
        $number = strip_tags($instance['number']);
        ?>

        <p>
            <label
                for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Flickr ID', 'geodirectory');?>
                (<a href="http://www.idgettr.com">idGettr</a>):
                <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>"
                       name="<?php echo $this->get_field_name('id'); ?>" type="text"
                       value="<?php echo esc_attr($id); ?>"/>
            </label>
        </p>

        <p>
            <label
                for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of photos:', 'geodirectory');?>
                <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>"
                       name="<?php echo $this->get_field_name('number'); ?>" type="text"
                       value="<?php echo esc_attr($number); ?>"/>
            </label>
        </p>
    <?php
    }
}

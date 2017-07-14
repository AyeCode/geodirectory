<?php
/**
* GeoDirectory Listing Detail Social Sharing Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDirectory Twitter widget.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Detail_Social_Sharing extends WP_Widget {
    /**
     * Register the Listing Detail Social Sharing widget with WordPress.
     *
     * @since 2.0.0
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'gd-widget-detail-social-sharing',
            'description' => __( 'Display social sharing buttons on the lisitng detail page.', 'geodirectory' ),
            'customize_selective_refresh' => true,
        );
        parent::__construct( 'detail_social_sharing', __( 'GD > Listing Detail Social Sharing', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for Twitter widget.
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
         * Filter the twitter widget description text.
         *
         * @since 1.0.0
         * @param string $desc1 The widget description text.
         */
        $desc1 = empty($instance['gd_tw_desc1']) ? '&nbsp;' : apply_filters('gd_tw_widget_desc1', $instance['gd_tw_desc1']);
        echo $before_widget;
        if ($desc1 <> "") {
            echo $desc1;
        }
        echo $after_widget;
    }

    /**
     * Sanitize twitter widget form values as they are saved.
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
        $instance['gd_tw_desc1'] = ($new_instance['gd_tw_desc1']);
        return $instance;
    }

    /**
     * Back-end twitter widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'gd_tw_desc1' => ''));

        $desc1 = ($instance['gd_tw_desc1']);
        ?>
        <p><label
                for="<?php echo $this->get_field_id('gd_tw_desc1'); ?>"><?php _e('Your twitter code', 'geodirectory');?>
                <textarea class="widefat" rows="6" cols="20"
                          id="<?php echo $this->get_field_id('gd_tw_desc1'); ?>"
                          name="<?php echo $this->get_field_name('gd_tw_desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label>
        </p>
    <?php
    }
}

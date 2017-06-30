<?php
/**
* GeoDirectory Feedburner Subscribe Widget
*
* @since 1.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDirectory Feedburner Subscribe widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Subscribe extends WP_Widget {

    /**
     * Register the feedburner subscribe widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir-subscribe',
            'description' => __( 'GD > Google Feedburner Subscribe', 'geodirectory' )
        );
        parent::__construct( 'widget_subscribeWidget', __( 'GD > Subscribe', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for feedburner subscribe widget.
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
         * Filter the widget instance id.
         *
         * @since 1.0.0
         * @param string $id The widget instance id.
         */
        $id = empty($instance['id']) ? '' : apply_filters('widget_id', $instance['id']);

        /** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'], 'geodirectory'));

        /**
         * Filter the widget text.
         *
         * @since 1.0.0
         * @param string $text The widget text.
         */
        $text = empty($instance['text']) ? '' : apply_filters('widget_text', $instance['text']);

        echo $before_widget;
        ?>

        <?php echo $before_title . $title; ?>  <a href="<?php if ($id) {
        echo 'http://feeds2.feedburner.com/' . $id;
    } else {
        bloginfo('rss_url');
    } ?>"><i class="fa fa-rss-square"></i> </a><?php echo $after_title;?>

        <?php if ($text <> "") { ?>

        <p><?php echo $text; ?> </p>

    <?php } ?>

        <form class="geodir-subscribe-form" action="http://feedburner.google.com/fb/a/mailverify" method="post"
              target="popupwindow"
              onsubmit="window.open('http://feedburner.google.com/fb/a/mailverify?uri=<?php echo $id; ?>', 'popupwindow', 'scrollbars=yes,width=550,height=520');return true">

            <input type="text" class="field"
                   onfocus="if (this.value == '<?php _e('Your Email Address', 'geodirectory')?>') {this.value = '';}"
                   onblur="if (this.value == '') {this.value = '<?php _e('Your Email Address', 'geodirectory')?>';}"
                   name="email" value="<?php _e('Your Email Address', 'geodirectory')?>"/>

            <input type="hidden" value="<?php echo $id; ?>" name="uri"/><input type="hidden" name="loc"
                                                                               value="en_US"/>

            <input class="btn_submit" type="submit" name="submit" value="Submit"/>

        </form>

        <?php
        echo $after_widget;

    }

    /**
     * Sanitize feedburner subscribe widget form values as they are saved.
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
        $instance['title'] = ($new_instance['title']);
        $instance['text'] = ($new_instance['text']);

        return $instance;
    }

    /**
     * Back-end feedburner subscribe widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 'id' => '', 'advt1' => '', 'text' => '', 'twitter' => '', 'facebook' => '', 'digg' => '', 'myspace' => ''));

        $id = strip_tags($instance['id']);
        $title = strip_tags($instance['title']);
        $text = strip_tags($instance['text']);
        ?>
        <p><label
                for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'geodirectory');?>:
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                       name="<?php echo $this->get_field_name('title'); ?>" type="text"
                       value="<?php echo esc_attr($title); ?>"/></label></p>
        <p><label
                for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Feedburner ID (ex :- geotheme)', 'geodirectory');?>
                : <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>"
                         name="<?php echo $this->get_field_name('id'); ?>" type="text"
                         value="<?php echo esc_attr($id); ?>"/></label></p>
        <p><label
                for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Short Description', 'geodirectory');?>
                <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('text'); ?>"
                          name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_attr($text); ?></textarea></label>
        </p>
    <?php
    }
}

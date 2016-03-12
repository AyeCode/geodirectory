<?php
/**
 * GeoDirectory Recent Reviews Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory recent reviews widget class.
 *
 * @since 1.0.0
 */
class geodir_recent_reviews_widget extends WP_Widget
{
    /**
	 * Register the recent reviews widget.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
	 */
    public function __construct() {
        $widget_ops = array('classname' => 'geodir_recent_reviews', 'description' => __('GD > Recent Reviews', 'geodirectory'));
        parent::__construct(
            'geodir_recent_reviews', // Base ID
            __('GD > Recent Reviews', 'geodirectory'), // Name
            $widget_ops// Args
        );
    }

	/**
	 * Front-end display content for recent reviews widget.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance)
    {
        // prints the widget
        extract($args, EXTR_SKIP);

        /** This filter is documented in geodirectory_widgets.php */
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'], 'geodirectory'));
		
		/**
		 * Filter the number of reviews to display.
		 *
		 * @since 1.0.0
		 *
		 * @param int $instance['count'] Number of reviews to display.
		 */
		$count = empty($instance['count']) ? '5' : apply_filters('widget_count', $instance['count']);

		/**
		 * Filter the height and width of the avatar image in pixels.
		 *
		 * @since 1.0.0
		 *
		 * @param int $g_size Height and width of the avatar image in pixels. Default 30.
		 */
		$g_size = apply_filters('geodir_recent_reviews_g_size', 30);
        /**
         * Filter the excerpt length
         *
         * @since 1.0.0
         *
         * @param int $excerpt_length Excerpt length. Default 100.
         */
        $excerpt_length = apply_filters('geodir_recent_reviews_excerpt_length', 100);
        $comments_li = geodir_get_recent_reviews($g_size, $count, $excerpt_length, false);

        if ($comments_li) {
            echo $before_widget;
            ?>
            <div class="widget geodir_recent_reviews_section">
                <?php if ($title) {
                    echo $before_title . $title . $after_title;
                } ?>
                <ul class="geodir_recent_reviews"><?php echo $comments_li; ?></ul>
            </div>
            <?php
            echo $after_widget;
        }
    }

	/**
	 * Sanitize recent reviews widget form values as they are saved.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update($new_instance, $old_instance)
    {
        //save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['count'] = strip_tags($new_instance['count']);
        return $instance;
    }
    
	/**
	 * Back-end recent reviews widget settings form.
	 *
	 * @since 1.0.0
     * @since 1.5.1 Declare function public.
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance)
    {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'count' => ''));
        $title = strip_tags($instance['title']);
        $count = strip_tags($instance['count']);
        ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>">Widget Title: <input class="widefat"
                                                                                         id="<?php echo $this->get_field_id('title'); ?>"
                                                                                         name="<?php echo $this->get_field_name('title'); ?>"
                                                                                         type="text"
                                                                                         value="<?php echo esc_attr($title); ?>"/></label>
        </p>
        <p><label for="<?php echo $this->get_field_id('count'); ?>">Number of Reviews <input class="widefat"
                                                                                             id="<?php echo $this->get_field_id('count'); ?>"
                                                                                             name="<?php echo $this->get_field_name('count'); ?>"
                                                                                             type="text"
                                                                                             value="<?php echo esc_attr($count); ?>"/></label>
        </p>
    <?php
    }
} // class geodir_recent_reviews_widget

register_widget('geodir_recent_reviews_widget');
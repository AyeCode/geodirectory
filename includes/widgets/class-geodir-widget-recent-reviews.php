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
class GeoDir_Widget_Recent_Reviews extends WP_Widget {
    
    /**
     * Register the recent reviews widget.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir_recent_reviews',
            'description' => __( 'GD > Recent Reviews', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array(),
        );
        parent::__construct( 'geodir_recent_reviews', __( 'GD > Recent Reviews', 'geodirectory' ), $widget_ops );
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
    public function widget($args, $instance) {
        // prints the widget
        extract($args, EXTR_SKIP);

        /** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
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

        /**
         * Filters the recent reviews default location filter.
         *
         * @since 2.0.0
         *
         * @param bool   $add_location_filter Whether the location filter is active. Default false.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $add_location_filter = apply_filters( 'geodir_recent_reviews_widget_location_filter', empty( $instance['add_location_filter'] ) ? false : true, $instance, $this->id_base );
        
        /**
         * Filters the recent reviews viewing post type.
         *
         * @since 2.0.0
         *
         * @param bool   $use_viewing_post_type Whether the viewing post type filter is active. Default false.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $use_viewing_post_type = apply_filters( 'geodir_recent_reviews_widget_use_viewing_post_type', empty( $instance['use_viewing_post_type'] ) ? false : true, $instance, $this->id_base );
        $post_type = $use_viewing_post_type ? geodir_get_current_posttype() : '';

        $comments_li = geodir_get_recent_reviews($g_size, $count, $excerpt_length, false, $post_type, $add_location_filter);

        if ($comments_li) {
            echo $before_widget;
            ?>
            <div class="geodir_recent_reviews_section">
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
    public function update($new_instance, $old_instance) {
        // save the widget
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['count'] = strip_tags($new_instance['count']);
        $instance['add_location_filter'] = !empty( $new_instance['add_location_filter'] ) ? 1 : 0;
        $instance['use_viewing_post_type'] = !empty( $new_instance['use_viewing_post_type'] ) ? 1 : 0;
        return $instance;
    }
    
    /**
     * Back-end recent reviews widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     * @since 2.0.0 Location filter & post type filter fields added.
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance) {
        $instance = wp_parse_args( (array)$instance, array( 'title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'count' => '', 'add_location_filter' => '', 'use_viewing_post_type' => '' ) );

        $title = strip_tags($instance['title']);
        $count = strip_tags($instance['count']);
        $add_location_filter = !empty( $instance['add_location_filter'] ) ? true : false;
        $use_viewing_post_type = !empty( $instance['use_viewing_post_type'] ) ? true : false;
        ?>
        <p class="gd-wgt-pwrap <?php echo $this->get_field_id( 'title' ); ?>-wrap">
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Widget Title:', 'geodirectory' ); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>"/></label>
        </p class="gd-wgt-pwrap <?php echo $this->get_field_id( 'count' ); ?>-wrap">
        <p>
            <label for="<?php echo $this->get_field_id('count'); ?>"><?php _e( 'Number of Reviews:', 'geodirectory' ); ?> <input class="widefat" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" type="text" value="<?php echo esc_attr($count); ?>"/></label>
        </p>
        <?php if ( defined( 'GEODIRLOCATION_VERSION' ) ) { ?>
        <p class="gd-wgt-pwrap <?php echo $this->get_field_id( 'add_location_filter' ); ?>-wrap">
            <input class="checkbox" type="checkbox"<?php checked( $add_location_filter, true ); ?> id="<?php echo $this->get_field_id( 'add_location_filter' ); ?>" name="<?php echo $this->get_field_name('add_location_filter'); ?>" />
            <label for="<?php echo $this->get_field_id( 'add_location_filter' ); ?>"><?php _e( 'Enable location filter', 'geodirectory' ); ?></label>
        </p>
        <?php } ?>
        <p class="gd-wgt-pwrap <?php echo $this->get_field_id( 'use_viewing_post_type' ); ?>-wrap">
            <input class="checkbox" type="checkbox"<?php checked( $use_viewing_post_type, true ); ?> id="<?php echo $this->get_field_id( 'use_viewing_post_type' ); ?>" name="<?php echo $this->get_field_name( 'use_viewing_post_type' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'use_viewing_post_type' ); ?>"><?php _e( 'Enable current viewing post type filter', 'geodirectory' ); ?></label>
        </p>
    <?php
    }
}

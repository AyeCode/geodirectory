<?php
/**
* GeoDirectory Social Like Widget
*
* @since 1.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDirectory Social Like Widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Social_Like extends WP_Widget {

    /**
     * Register the social like widget with WordPress.
     *
     * @since 1.0.0
     * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
     */
    public function __construct() {
        $widget_ops = array(
            'classname' => 'geodir_social_like_widget',
            'description' => __( 'GD > Twitter,Facebook and Google+ buttons', 'geodirectory' )
        );
        parent::__construct( 'social_like_widget', __( 'GD > Social Like', 'geodirectory' ), $widget_ops );
    }

    /**
     * Front-end display content for social like widget.
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
        $title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);

        global $current_user, $post;
        echo $before_widget;
        ?>

        <?php //if ( geodir_get_option('gd_tweet_button') ) {
        ?>

        <a href="http://twitter.com/share"
           class="twitter-share-button"><?php _e('Tweet', 'geodirectory');?></a>

        <script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>

        <?php //}
        ?>

        <?php // if ( geodir_get_option('gd_facebook_button') ) {
        ?>

        <iframe <?php if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
            echo 'allowtransparency="true"';
        }?> class="facebook"
            src="//www.facebook.com/plugins/like.php?href=<?php echo urlencode(geodir_curPageURL()); ?>&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light"
            style="border:none; overflow:hidden; width:100px; height:20px"></iframe>


        <?php //}
        ?>

        <?php //if ( geodir_get_option('gd_google_button') ) {
        ?>
        <script>
            window.___gcfg = {
                parsetags: 'explicit'
            }
        </script>
        <script type="text/javascript" src="https://apis.google.com/js/plusone.js"></script>

        <div id="plusone-div"></div>
        <script type="text/javascript">gapi.plusone.render('plusone-div', {
                "size": "medium",
                "count": "true"
            });</script>
        <?php //}
        echo $after_widget;

    }

    /**
     * Sanitize social like widget form values as they are saved.
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
     * Back-end social like widget settings form.
     *
     * @since 1.0.0
     * @since 1.5.1 Declare function public.
     *
     * @param array $instance Previously saved values from database.
     * @return string|void
     */
    public function form($instance) {
        //widgetform in backend
        $instance = wp_parse_args((array)$instance, array('title' => ''));
        $title = strip_tags($instance['title']);
        ?>
        <p>No settings for this widget</p>


    <?php
    }
}

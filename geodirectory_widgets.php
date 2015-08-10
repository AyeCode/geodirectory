<?php
/**
 * GeoDirectory Widgets
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

/**
 * Registers GeoDirectory sidebar.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global array $geodir_sidebars List of geodirectory sidebars.
 */
function geodir_register_sidebar()
{
    global $geodir_sidebars;

    if (function_exists('register_sidebar')) {
        /*===========================*/
        /* Home page sidebars start*/
        /*===========================*/

        /**
         * Filter the `$before_widget` widget opening HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '<section id="%1$s" class="widget geodir-widget %2$s">'.
         * @see 'geodir_after_widget'
         */
        $before_widget = apply_filters('geodir_before_widget', '<section id="%1$s" class="widget geodir-widget %2$s">');
        /**
         * Filter the `$after_widget` widget closing HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '</section>'.
         * @see 'geodir_before_widget'
         */
        $after_widget = apply_filters('geodir_after_widget', '</section>');
        /**
         * Filter the `$before_title` widget title opening HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '<h3 class="widget-title">'.
         * @see 'geodir_after_title'
         */
        $before_title = apply_filters('geodir_before_title', '<h3 class="widget-title">');
        /**
         * Filter the `$after_title` widget title closing HTML tag.
         *
         * @since 1.0.0
         * @param string $var The HTML string to filter. Default = '</h3>'.
         * @see 'geodir_before_title'
         */
        $after_title = apply_filters('geodir_after_title', '</h3>');

        if (get_option('geodir_show_home_top_section')) {
            register_sidebars(1, array('id' => 'geodir_home_top', 'name' => __('GD Home Top Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_top';
        }

        if (get_option('geodir_show_home_contant_section')) {
            register_sidebars(1, array('id' => 'geodir_home_content', 'name' => __('GD Home Content Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_content';
        }

        if (get_option('geodir_show_home_right_section')) {
            register_sidebars(1, array('id' => 'geodir_home_right', 'name' => __('GD Home Right Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_right';
        }

        if (get_option('geodir_show_home_left_section')) {
            register_sidebars(1, array('id' => 'geodir_home_left', 'name' => __('GD Home Left Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_left';
        }

        if (get_option('geodir_show_home_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_home_bottom', 'name' => __('GD Home Bottom Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_home_bottom';
        }

        /*===========================*/
        /* Home page sidebars end*/
        /*===========================*/

        /*===========================*/
        /* Listing page sidebars start*/
        /*===========================*/

        if (get_option('geodir_show_listing_top_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_top', 'name' => __('GD Listing Top Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_top';
        }

        if (get_option('geodir_show_listing_left_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_left_sidebar', 'name' => __('GD Listing Left Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_left_sidebar';
        }

        if (get_option('geodir_show_listing_right_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_right_sidebar', 'name' => __('GD Listing Right Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_right_sidebar';
        }

        if (get_option('geodir_show_listing_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_listing_bottom', 'name' => __('GD Listing Bottom Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_listing_bottom';
        }

        /*===========================*/
        /* Listing page sidebars start*/
        /*===========================*/

        /*===========================*/
        /* Search page sidebars start*/
        /*===========================*/

        if (get_option('geodir_show_search_top_section')) {
            register_sidebars(1, array('id' => 'geodir_search_top', 'name' => __('GD Search Top Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_top';
        }

        if (get_option('geodir_show_search_left_section')) {
            register_sidebars(1, array('id' => 'geodir_search_left_sidebar', 'name' => __('GD Search Left Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_left_sidebar';
        }

        if (get_option('geodir_show_search_right_section')) {
            register_sidebars(1, array('id' => 'geodir_search_right_sidebar', 'name' => __('GD Search Right Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_right_sidebar';
        }

        if (get_option('geodir_show_search_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_search_bottom', 'name' => __('GD Search Bottom Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_search_bottom';
        }

        /*===========================*/
        /* Search page sidebars end*/
        /*===========================*/

        /*==================================*/
        /* Detail/Single page sidebars start*/
        /*==================================*/
        if (get_option('geodir_show_detail_top_section')) {
            register_sidebars(1, array('id' => 'geodir_detail_top', 'name' => __('GD Detail Top Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_detail_top';
        }

        register_sidebars(1, array('id' => 'geodir_detail_sidebar', 'name' => __('GD Detail Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

        $geodir_sidebars[] = 'geodir_detail_sidebar';

        if (get_option('geodir_show_detail_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_detail_bottom', 'name' => __('GD Detail Bottom Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_detail_bottom';
        }

        /*==================================*/
        /* Detail/Single page sidebars end*/
        /*==================================*/

        /*==================================*/
        /* Author page sidebars start       */
        /*==================================*/

        if (get_option('geodir_show_author_top_section')) {
            register_sidebars(1, array('id' => 'geodir_author_top', 'name' => __('GD Author Top Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_top';
        }

        if (get_option('geodir_show_author_left_section')) {
            register_sidebars(1, array('id' => 'geodir_author_left_sidebar', 'name' => __('GD Author Left Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_left_sidebar';
        }

        if (get_option('geodir_show_author_right_section')) {
            register_sidebars(1, array('id' => 'geodir_author_right_sidebar', 'name' => __('GD Author Right Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_right_sidebar';
        }

        if (get_option('geodir_show_author_bottom_section')) {
            register_sidebars(1, array('id' => 'geodir_author_bottom', 'name' => __('GD Author Bottom Section', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

            $geodir_sidebars[] = 'geodir_author_bottom';
        }

        /*==================================*/
        /* Author page sidebars end         */
        /*==================================*/

        /*==================================*/
        /* Add listing page sidebars start       */
        /*==================================*/

        register_sidebars(1, array('id' => 'geodir_add_listing_sidebar', 'name' => __('GD Add Listing Right Sidebar', GEODIRECTORY_TEXTDOMAIN), 'before_widget' => $before_widget, 'after_widget' => $after_widget, 'before_title' => $before_title, 'after_title' => $after_title));

        $geodir_sidebars[] = 'geodir_add_listing_sidebar';

        /*==================================*/
        /* Add listing page sidebars end         */
        /*==================================*/

    }
}


if (!function_exists('register_geodir_widgets')) {
    /**
     * Registers all Widgets.
     *
     * @since 1.0.0
     * @package GeoDirectory
     */
    function register_geodir_widgets()
    {
        /**
         * Login Widget.
         *
         * @since 1.0.0
         */
        class geodir_loginwidget extends WP_Widget
        {
            /**
             * Register the login widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'geodir_loginbox', 'description' => __('Geodirectory Loginbox Widget', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'geodir_loginbox', // Base ID
                    __('GD > Loginbox', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {
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
            public function update($new_instance, $old_instance)
            {
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
            public function form($instance)
            {
                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'desc1' => ''));
                $title = strip_tags($instance['title']);

                ?>
                <p><label
                        for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', GEODIRECTORY_TEXTDOMAIN); ?>
                        : <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                                 name="<?php echo $this->get_field_name('title'); ?>" type="text"
                                 value="<?php echo esc_attr($title); ?>"/></label></p>


            <?php
            }
        }

        register_widget('geodir_loginwidget');


        /**
         * GeoDirectory Social Like Widget.
         *
         * @since 1.0.0
         */
        class geodir_social_like_widget extends WP_Widget
        {

            /**
             * Register the social like widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'geodir_social_like_widget', 'description' => __('GD > Twitter,Facebook and Google+ buttons', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'social_like_widget', // Base ID
                    __('GD > Social Like', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {
                // prints the widget
                extract($args, EXTR_SKIP);

                /**
                 * Filter the widget title text.
                 *
                 * @since 1.0.0
                 * @global object $current_user Current user object.
                 * @param string $title The widget title text.
                 */
                $title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);

                global $current_user, $post;
                echo $before_widget;
                ?>

                <?php //if ( get_option('gd_tweet_button') ) {
                ?>

                <a href="http://twitter.com/share"
                   class="twitter-share-button"><?php _e('Tweet', GEODIRECTORY_TEXTDOMAIN);?></a>

                <script type="text/javascript" src="//platform.twitter.com/widgets.js"></script>

                <?php //}
                ?>

                <?php // if ( get_option('gd_facebook_button') ) {
                ?>

                <iframe <?php if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) {
                    echo 'allowtransparency="true"';
                }?> class="facebook"
                    src="//www.facebook.com/plugins/like.php?href=<?php echo urlencode(geodir_curPageURL()); ?>&amp;layout=button_count&amp;show_faces=false&amp;width=100&amp;action=like&amp;colorscheme=light"
                    style="border:none; overflow:hidden; width:100px; height:20px"></iframe>


                <?php //}
                ?>

                <?php //if ( get_option('gd_google_button') ) {
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
            public function update($new_instance, $old_instance)
            {
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
            public function form($instance)
            {
                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => ''));
                $title = strip_tags($instance['title']);
                ?>
                <p>No settings for this widget</p>


            <?php
            }
        }

        register_widget('geodir_social_like_widget');


        /**
         * GeoDirectory Feedburner Subscribe widget.
         *
         * @since 1.0.0
         */
        class geodirsubscribeWidget extends WP_Widget
        {

            /**
             * Register the feedburner subscribe widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'geodir-subscribe', 'description' => __('GD > Google Feedburner Subscribe', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'widget_subscribeWidget', // Base ID
                    __('GD > Subscribe', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {
                // prints the widget
                extract($args, EXTR_SKIP);

                /**
                 * Filter the widget instance id.
                 *
                 * @since 1.0.0
                 * @param string $id The widget instance id.
                 */
                $id = empty($instance['id']) ? '' : apply_filters('widget_id', $instance['id']);

                /** This filter is documented in geodirectory_widgets.php */
                $title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'], GEODIRECTORY_TEXTDOMAIN));

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
                           onfocus="if (this.value == '<?php _e('Your Email Address', GEODIRECTORY_TEXTDOMAIN)?>') {this.value = '';}"
                           onblur="if (this.value == '') {this.value = '<?php _e('Your Email Address', GEODIRECTORY_TEXTDOMAIN)?>';}"
                           name="email" value="<?php _e('Your Email Address', GEODIRECTORY_TEXTDOMAIN)?>"/>

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
            public function update($new_instance, $old_instance)
            {

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
            public function form($instance)
            {
                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => '', 'id' => '', 'advt1' => '', 'text' => '', 'twitter' => '', 'facebook' => '', 'digg' => '', 'myspace' => ''));

                $id = strip_tags($instance['id']);

                $title = strip_tags($instance['title']);

                $text = strip_tags($instance['text']);



                ?>
                <p><label
                        for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', GEODIRECTORY_TEXTDOMAIN);?>:
                        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                               name="<?php echo $this->get_field_name('title'); ?>" type="text"
                               value="<?php echo esc_attr($title); ?>"/></label></p>

                <p><label
                        for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Feedburner ID (ex :- geotheme)', GEODIRECTORY_TEXTDOMAIN);?>
                        : <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>"
                                 name="<?php echo $this->get_field_name('id'); ?>" type="text"
                                 value="<?php echo esc_attr($id); ?>"/></label></p>

                <p><label
                        for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Short Description', GEODIRECTORY_TEXTDOMAIN);?>
                        <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('text'); ?>"
                                  name="<?php echo $this->get_field_name('text'); ?>"><?php echo esc_attr($text); ?></textarea></label>
                </p>
            <?php
            }
        }

        register_widget('geodirsubscribeWidget');

        /**
         * GeoDirectory advertise widget.
         *
         * @since 1.0.0
         */
        class geodiradvtwidget extends WP_Widget
        {

            /**
             * Register the advertise widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'GeoDirectory Advertise', 'description' => __('GD > common advertise widget in sidebar, bottom section', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'advtwidget', // Base ID
                    __('GD > Advertise', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {

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
            public function update($new_instance, $old_instance)
            {
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
            public function form($instance)
            {
                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'desc1' => ''));

                $desc1 = ($instance['desc1']);
                ?>
                <p><label
                        for="<?php echo $this->get_field_id('desc1'); ?>"><?php _e('Your Advt code (ex.google adsense, etc.)', GEODIRECTORY_TEXTDOMAIN);?>
                        <textarea class="widefat" rows="6" cols="20" id="<?php echo $this->get_field_id('desc1'); ?>"
                                  name="<?php echo $this->get_field_name('desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label>
                </p>

            <?php
            }
        }

        register_widget('geodiradvtwidget');

        /**
         * GeoDirectory Flickr widget.
         *
         * @since 1.0.0
         */
        class GeodirFlickrWidget extends WP_Widget
        {

            /**
             * Register the flickr widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'Geo Dir Flickr Photos ', 'description' => __('GD > Flickr Photos', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'widget_flickrwidget', // Base ID
                    __('GD > Flickr Photos', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {

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
                echo $before_title . __('Photo Gallery', GEODIRECTORY_TEXTDOMAIN) . $after_title;
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
            public function update($new_instance, $old_instance)
            {
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
            public function form($instance)
            {

                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => '', 'id' => '', 'number' => ''));
                $id = strip_tags($instance['id']);
                $number = strip_tags($instance['number']);
                ?>

                <p>
                    <label
                        for="<?php echo $this->get_field_id('id'); ?>"><?php _e('Flickr ID', GEODIRECTORY_TEXTDOMAIN);?>
                        (<a href="http://www.idgettr.com">idGettr</a>):
                        <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>"
                               name="<?php echo $this->get_field_name('id'); ?>" type="text"
                               value="<?php echo esc_attr($id); ?>"/>
                    </label>
                </p>

                <p>
                    <label
                        for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of photos:', GEODIRECTORY_TEXTDOMAIN);?>
                        <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>"
                               name="<?php echo $this->get_field_name('number'); ?>" type="text"
                               value="<?php echo esc_attr($number); ?>"/>
                    </label>
                </p>
            <?php
            }
        }

        register_widget('GeodirFlickrWidget');

        /**
         * GeoDirectory Twitter widget.
         *
         * @since 1.0.0
         */
        class geodir_twitter extends WP_Widget
        {
            /**
             * Register the Twitter widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'Twitter', 'description' => __('GD > Twitter Feed', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'widget_Twidget', // Base ID
                    __('GD > Twitter', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {

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
            public function update($new_instance, $old_instance)
            {
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
            public function form($instance)
            {
                //widgetform in backend
                $instance = wp_parse_args((array)$instance, array('title' => '', 't1' => '', 't2' => '', 't3' => '', 'img1' => '', 'gd_tw_desc1' => ''));

                $desc1 = ($instance['gd_tw_desc1']);
                ?>
                <p><label
                        for="<?php echo $this->get_field_id('gd_tw_desc1'); ?>"><?php _e('Your twitter code', GEODIRECTORY_TEXTDOMAIN);?>
                        <textarea class="widefat" rows="6" cols="20"
                                  id="<?php echo $this->get_field_id('gd_tw_desc1'); ?>"
                                  name="<?php echo $this->get_field_name('gd_tw_desc1'); ?>"><?php echo esc_attr($desc1); ?></textarea></label>
                </p>

            <?php
            }
        }

        register_widget('geodir_twitter');

        /**
         * GeoDirectory Advanced Search widget.
         *
         * @since 1.0.0
         */
        class geodir_advance_search_widget extends WP_Widget
        {
            /**
             * Register the advanced search widget with WordPress.
             *
             * @since 1.0.0
             * @since 1.5.1 Changed from PHP4 style constructors to PHP5 __construct.
             */
            function __construct() {
                $widget_ops = array('classname' => 'geodir_advance_search_widget', 'description' => __('GD > Search', GEODIRECTORY_TEXTDOMAIN));
                parent::__construct(
                    'geodir_advance_search', // Base ID
                    __('GD > Search', GEODIRECTORY_TEXTDOMAIN), // Name
                    $widget_ops// Args
                );
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
            public function widget($args, $instance)
            {

                // prints the widget
                extract($args, EXTR_SKIP);

                echo $before_widget;

                /** This filter is documented in geodirectory_widgets.php */
                $title = empty($instance['title']) ? __('Search', GEODIRECTORY_TEXTDOMAIN) : apply_filters('widget_title', __($instance['title'], GEODIRECTORY_TEXTDOMAIN));

                geodir_get_template_part('listing', 'filter-form');

                echo $after_widget;
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
            public function update($new_instance, $old_instance)
            {
                //save the widget
                //Nothing to save
                return isset($instance) ? $instance : '';
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
            public function form($instance)
            {
                //widgetform in backend
                echo __("This is a search widget to show advance search for gedodirectory listings.", GEODIRECTORY_TEXTDOMAIN);
            }
        }

        register_widget('geodir_advance_search_widget');


        /**
         * Contains all functions for popular widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/geodirectory_popular_widget.php');
        /**
         * Contains all functions for listing slider widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/geodirectory_listing_slider_widget.php');
        /**
         * Contains all functions for home map widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/home_map_widget.php');
        /**
         * Contains all functions for listing map widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/listing_map_widget.php');
        /**
         * Contains all functions for reviews widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/geodirectory_reviews_widget.php');
        /**
         * Contains all functions for related listing widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/geodirectory_related_listing_widget.php');
        /**
         * Contains all functions for bestof widget.
         *
         * @since 1.0.0
         * @package GeoDirectory
         */
        include_once('geodirectory-widgets/geodirectory_bestof_widget.php');
    }

}



<?php
/**
 * GeoDirectory Detail Rating Stars Widget
 *
 * @since 2.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDir_Widget_Post_Fav class.
 *
 * @since 2.0.0
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Fav extends WP_Super_Duper {

    public $arguments;
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['fav','geo','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_post_fav', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Post Favorite','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-post-fav', // widget class
                'description' => esc_html__('This shows a GD post favorite link.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_wgt_showhide' => 'show_on',
                'gd_wgt_restrict' => array( 'gd-detail' ),
            ),
            'arguments'     => array(
                'show'  => array(
                    'name' => 'show',
                    'title' => __('Show:', 'geodirectory'),
                    'desc' => __('What part of the post meta to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('All', 'geodirectory'),
                        "icon" => __('Icon', 'geodirectory'),
                        "text" => __('Text', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'alignment'  => array(
                    'name' => 'alignment',
                    'title' => __('Alignment:', 'geodirectory'),
                    'desc' => __('How the item should be positioned on the page.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('None', 'geodirectory'),
                        "left" => __('Left', 'geodirectory'),
                        "center" => __('Center', 'geodirectory'),
                        "right" => __('Right', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'list_hide'  => array(
                    'title' => __('Hide item on view:', 'geodirectory'),
                    'desc' => __('You can set at what view the item will become hidden.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('None', 'geodirectory'),
                        "2" => __('Grid view 2', 'geodirectory'),
                        "3" => __('Grid view 3', 'geodirectory'),
                        "4" => __('Grid view 4', 'geodirectory'),
                        "5" => __('Grid view 5', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'list_hide_secondary'  => array(
                    'title' => __('Hide secondary info on view', 'geodirectory'),
                    'desc' => __('You can set at what view the secondary info such as label will become hidden.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('None', 'geodirectory'),
                        "2" => __('Grid view 2', 'geodirectory'),
                        "3" => __('Grid view 3', 'geodirectory'),
                        "4" => __('Grid view 4', 'geodirectory'),
                        "5" => __('Grid view 5', 'geodirectory'),
                    ),
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'icon'  => array(
                    'type' => 'text',
                    'title' => __('Icon class (font-awesome)', 'geodirectory'),
                    'desc' => __('FontAwesome icon class to use.', 'geodirectory'),
                    'placeholder' => 'fas fa-heart',
                    'default' => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'icon_color_off'  => array(
                    'type' => 'color',
                    'title' => __('Icon color off', 'geodirectory'),
                    'desc' => __('Color for the icon when not set.', 'geodirectory'),
                    'placeholder' => '',
                    'default' => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
                'icon_color_on'  => array(
                    'type' => 'color',
                    'title' => __('Icon color on', 'geodirectory'),
                    'desc' => __('Color for the icon when set.', 'geodirectory'),
                    'placeholder' => '',
                    'default' => '',
                    'desc_tip' => true,
                    'advanced' => true
                ),
            )

        );

        parent::__construct( $options );
    }


    /**
     * The Super block output function.
     *
     * @param array $args
     * @param array $widget_args
     * @param string $content
     *
     * @return mixed|string|void
     */
    public function output($args = array(), $widget_args = array(),$content = ''){
        global $post;

        $defaults = array(
            'show'      => '', // icon, text
            'alignment'      => '', // left, center, right
            'icon_color_off'      => '',
            'icon_color_on'      => '',
            'icon'  => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        $class = '';
        $main = '';
        if($args['alignment']=='left'){
            $class = "gd-align-left";
        }elseif($args['alignment']=='center'){
            $class = "gd-align-center";
        }elseif($args['alignment']=='right'){
            $class = "gd-align-right";
        }

        if($args['show']=='icon'){
            $class .= ' gd-fav-hide-text ';
        }elseif($args['show']=='text'){
            $class .= ' gd-fav-hide-stars ';
        }

        // set list_hide class
        if($args['list_hide']=='2'){$class .= " gd-lv-2 ";}
        if($args['list_hide']=='3'){$class .= " gd-lv-3 ";}
        if($args['list_hide']=='4'){$class .= " gd-lv-4 ";}
        if($args['list_hide']=='5'){$class .= " gd-lv-5 ";}

        // set list_hide_secondary class
        if($args['list_hide_secondary']=='2'){$class .= " gd-lv-s-2 ";}
        if($args['list_hide_secondary']=='3'){$class .= " gd-lv-s-3 ";}
        if($args['list_hide_secondary']=='4'){$class .= " gd-lv-s-4 ";}
        if($args['list_hide_secondary']=='5'){$class .= " gd-lv-s-5 ";}

        $before = '<div class="geodir_post_meta gd-fav-info-wrap '. $class .'" >';
        $after  = '</div>';

        $main = $this->get_fav_html($args);



        return $before . $main . $after;

    }

    /**
     * Get favorite list html.
     *
     * @since 2.0.0
     *
     * @return string Favorite Html.
     */
    public function get_fav_html($args = array()){
        global $gd_post;
        ob_start();
        ?>
        <span class="gd-list-favorite">
            <?php geodir_favourite_html( '', $gd_post->ID, $args ); ?>
        </span>
        <?php
        return ob_get_clean();
    }


}

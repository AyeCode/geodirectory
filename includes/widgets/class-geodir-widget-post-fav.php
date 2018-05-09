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
            ),
            'arguments'     => array(
                'show'  => array(
                    'name' => 'show',
                    'title' => __('Show:', 'geodirectory'),
                    'desc' => __('What part of the post meta to show.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  array(
                        "" => __('All', 'geodirectory'),
                        "stars" => __('Stars', 'geodirectory'),
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
            'show'      => '', // stars, text
            'alignment'      => '', // left, center, right
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

        $before = '<div class="geodir_post_meta gd-fav-info-wrap '. $class .'" >';
        $after  = '</div>';

        $main = $this->get_fav_html();



        return $before . $main . $after;

    }

    /**
     * Get favorite list html.
     *
     * @since 2.0.0
     *
     * @return string Favorite Html.
     */
    public function get_fav_html(){
        global $post;
        ob_start();
        ?>
        <span class="gd-list-favorite">
            <?php geodir_favourite_html( '', $post->ID ); ?>
        </span>
        <?php
        return ob_get_clean();
    }


}

<?php
/**
* GeoDirectory Detail Rating Stars Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Post_Rating class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Post_Rating extends WP_Super_Duper {

    public $arguments;
    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['rating','geo','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_post_rating', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Post Rating','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-post-rating', // widget class
                'description' => esc_html__('This shows a GD post rating stars.','geodirectory'), // widget description
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

        $before = '<div class="geodir_post_meta gd-rating-info-wrap '. $class .'" >';
        $after  = '</div>';

        if($args['show']=='stars'){
            $main .= $this->get_rating_stars();
        }elseif($args['show']=='text'){
            $main .= $this->get_rating_text();
        }else{
            $main .= $this->get_rating_stars();
            $main .= $this->get_rating_text();
        }

        return $before . $main . $after;

    }

    /**
     * Get rating stars html.
     *
     * @since 2.0.0
     *
     * @return string Rating stars html.
     */
    public function get_rating_stars(){
        global $post;
        ob_start();
        ?>
        <span class="gd-list-rating-stars">
           <?php
           if ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post->post_type ) ) {
               echo '<i class="fa fa-comments"></i>';
           } else {
               if(geodir_is_block_demo()){
                   $post_rating = "5";
               }elseif(isset($post->ID) && $post->ID == geodir_details_page_id()){
                   $post_rating = "5";
               }else{
                   $post_rating = geodir_get_post_rating( $post->ID );
               }
               echo geodir_get_rating_stars( $post_rating, $post->ID );
           }
           ?>
        </span>
        <?php
        return ob_get_clean();
    }

    /**
     * Get rating text html.
     *
     * @since 2.0.0
     *
     * @return string rating text html.
     */
    public function get_rating_text(){
        global $post,$gd_post;
        ob_start();
        ?>
        <span class="gd-list-rating-text">
            <?php
            if ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post->post_type ) ) {
                echo '<i class="fa fa-comments"></i>';
            }
            ?>
            <a href="<?php comments_link(); ?>" class="gd-list-rating-link">
                <?php geodir_comments_number( $gd_post ); ?>
            </a>
        </span>
        <?php
        return ob_get_clean();
    }

}

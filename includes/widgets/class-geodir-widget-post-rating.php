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
 * @since 2.0.0.49 Added list_hide and list_hide_secondary options for more flexible designs.
 */
class GeoDir_Widget_Post_Rating extends WP_Super_Duper {

    public $arguments;

    public $post_rating = '';
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
                )
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
            'list_hide'    => '',
            'list_hide_secondary'    => '',
        );

        /**
         * Parse incoming $args into an array and merge it with $defaults
         */
        $args = wp_parse_args( $args, $defaults );

        $class = '';
        $main = '';

        // Set alignment class
        if($args['alignment']=='left'){
            $class = "gd-align-left";
        }elseif($args['alignment']=='center'){
            $class = "gd-align-center";
        }elseif($args['alignment']=='right'){
            $class = "gd-align-right";
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



        if($args['show']=='stars'){
            $main .= $this->get_rating_stars();
        }elseif($args['show']=='text'){
            $main .= $this->get_rating_text();
        }else{
            $main .= $this->get_rating_stars();
            $main .= $this->get_rating_text();
        }

        $post_rating = $this->post_rating;
        if($post_rating===0){
            $class .= " geodir-post-rating-value-0";
        }elseif($post_rating){
            $class .= " geodir-post-rating-value-".absint($post_rating);
        }

        $before = '<div class="geodir_post_meta gd-rating-info-wrap '. $class .'" data-rating="'.round($post_rating, 1).'">';
        $after  = '</div>';

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
        <div class="gd-list-rating-stars">
           <?php
           if ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post->post_type ) ) {
               echo '<i class="fas fa-comments" aria-hidden="true"></i>';
           } else {
               if(geodir_is_block_demo()){
                   $post_rating = "5";
               }elseif(isset($post->ID) && ( $post->ID == geodir_details_page_id() || $post->ID == geodir_details_page_id( $post->post_type ) )){
                   $post_rating = "5";
               }else{
                   $post_rating = geodir_get_post_rating( $post->ID );
               }
               $this->post_rating = $post_rating;
               echo geodir_get_rating_stars( $post_rating, $post->ID );
           }
           ?>
        </div>
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
        global $gd_post;
        ob_start();
        ?>
        <span class="gd-list-rating-text">
            <a href="<?php comments_link(); ?>" class="gd-list-rating-link">
                <?php geodir_comments_number( $gd_post ); ?>
            </a>
        </span>
        <?php
        return ob_get_clean();
    }

}

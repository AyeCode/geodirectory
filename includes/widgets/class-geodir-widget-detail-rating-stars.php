<?php
/**
* GeoDirectory Detail Rating Stars Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Detail_Rating_Stars class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Detail_Rating_Stars extends WP_Super_Duper {

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
                'gd_show_pages' => array( 'detail' ),
            ),

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

        if(geodir_is_block_demo()){
            $rating = "5";
        }elseif(isset($post->ID) && $post->ID == geodir_details_page_id()){
            $rating = "5";
        }else{
            $rating = "";
        }

        return geodir_get_rating_stars( $rating, $post->ID);

    }

}

<?php
/**
* GeoDirectory Detail Social Sharing Widget
*
* @since 2.0.0
*
* @package GeoDirectory
*/

/**
 * GeoDir_Widget_Detail_Social_Sharing class.
 *
 * @since 2.0.0
 */
class GeoDir_Widget_Detail_Social_Sharing extends WP_Super_Duper {

    /**
     * Sets up the widgets name etc
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['share','social','geodir']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_share_buttons', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Share Buttons','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-share-buttons', // widget class
                'description' => esc_html__('This shows social sharing buttons.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                //'gd_show_pages' => array( 'detail' ),
            ),

            'arguments'     => array(
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

//        if(geodir_is_block_demo()){
//            $rating = "5";
//        }elseif(isset($post->ID) && $post->ID == geodir_details_page_id()){
//            $rating = "5";
//        }else{
//            $rating = "";
//        }

        return "social sharing";

        return geodir_social_sharing_buttons();

    }

    /**
     * Sets up a new Detail Social Sharing widget instance.
     *
     * @since 2.0.0
     * @access public
     */
    public function __constructx() {
        $widget_ops = array(
            'classname' => 'geodir-widget gd-widget-detail-social-sharing',
            'description' => __( 'Display social sharing buttons on the listing detail page.', 'geodirectory' ),
            'customize_selective_refresh' => true,
            'geodirectory' => true,
            'gd_show_pages' => array( 'detail' ),
        );
        parent::__construct( 'detail_social_sharing', __( 'GD > Detail Social Sharing', 'geodirectory' ), $widget_ops );
    }


    public function social_providers(){
        return array(

        );
    }

}

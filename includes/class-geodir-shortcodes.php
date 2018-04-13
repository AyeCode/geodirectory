<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Shortcodes class
 *
 * @class       GeoDir_Shortcodes
 * @version     2.0.0
 * @package     GeoDirectory/Classes
 * @category    Class
 */
class GeoDir_Shortcodes {

    /**
     * Init shortcodes.
     */
    public static function init() {
        $shortcodes = array(
            'gd_loop_actions'           => __CLASS__ . '::gd_loop_actions', // only for GD archive page
            'gd_archive_item_section'            => __CLASS__ . '::gd_archive_item_section',


        );

        foreach ( $shortcodes as $shortcode => $function ) {
            add_shortcode( apply_filters( 'geodir_shortcode_tag_' . $shortcode, $shortcode ), $function );
        }
    }

    /**
     * Shortcode Wrapper.
     *
     * @param string[] $function
     * @param array $atts (default: array())
     * @return string
     */
    public static function shortcode_wrapper( $function, $atts = array(), $content = null, $wrapper = array( 'class' => 'geodirectory', 'before' => null, 'after'  => null ) ) {
        ob_start();

        echo empty( $wrapper['before'] ) ? '<div class="' . esc_attr( $wrapper['class'] ) . '">' : $wrapper['before'];
        echo call_user_func( $function, $atts );
        echo empty( $wrapper['after'] ) ? '</div>' : $wrapper['after'];

        return ob_get_clean();
    }
    

    public static function gd_loop_actions( $atts = array(), $content = null ) {
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search')){
            ob_start();
            geodir_loop_actions();
            return ob_get_clean();
        }else{
            return "";
        }
    }

    // archive item page
    public static function gd_archive_item_section($atts = array(), $content = null ){
        $output = '';
        if(isset($atts['type']) && $atts['type']=='open'){
            $class = !empty($atts['class']) ? esc_attr($atts['class']) : '';
            $position = isset($atts['position']) && $atts['position']=='left' ? 'left' : 'right';
            $output = '<div class="gd-list-item-'.$position.' '.$class.'">';
        }elseif(isset($atts['type']) && $atts['type']=='close'){
            $output = "</div>";
        }

        return $output;//print_r($atts,true);
    }
    
}



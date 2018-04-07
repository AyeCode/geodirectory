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
            'gd_add_listing'            => __CLASS__ . '::gd_add_listing',
            'gd_single_taxonomies'      => __CLASS__ . '::gd_single_taxonomies',
            'gd_single_tabs'            => __CLASS__ . '::gd_single_tabs',
            'gd_single_next_prev'       => __CLASS__ . '::gd_single_next_prev',
            'gd_loop'                   => __CLASS__ . '::gd_loop', // only for GD archive page
            'gd_loop_paging'            => __CLASS__ . '::gd_loop_paging', // only for GD archive page
            'gd_loop_actions'            => __CLASS__ . '::gd_loop_actions', // only for GD archive page
            'gd_single_closed_text'    	=> __CLASS__ . '::gd_single_closed_text', // only on GD detail page
            //'gd_single_meta'    	=> __CLASS__ . '::gd_single_meta', // only on GD detail page

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
    
    public static function gd_add_listing( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_add_listing', $atts, $content  );
    }


    public static function gd_loop( $atts = array(), $content = null ) {
        global $wp_query;
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || (is_author() && !empty($wp_query->query['gd_favs'])) ){
            ob_start();

            // check if we have listings or if we are faking it
            if($wp_query->post_count == 1 && empty($wp_query->posts)){
                geodir_no_listings_found();
            }else{
                geodir_get_template_part('content', 'archive-listing');
            }
            return ob_get_clean();
        }else{
            return __("No listings found that mach your criteria.","geodirectory");
        }
    }

    public static function gd_loop_paging( $atts = array(), $content = null ) {
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search')){
            ob_start();
            geodir_loop_paging();
            return ob_get_clean();
        }else{
            return "";
        }
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


    // single page only shortcodes

    public static function gd_single_slider( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_single_slider', $atts, $content  );
    }

    public static function gd_single_taxonomies( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_single_taxonomies', $atts, $content  );
    }

    public static function gd_single_tabs( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_show_detail_page_tabs', $atts, $content  );
    }

    public static function gd_single_next_prev( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_single_next_prev', $atts, $content  );
    }
	
	public static function gd_single_closed_text( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_single_closed_text', $atts, $content  );
    }
    
}



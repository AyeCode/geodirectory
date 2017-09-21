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
            'gd_advanced_search'        => __CLASS__ . '::gd_advanced_search',
            'gd_bestof_widget'          => __CLASS__ . '::gd_bestof_widget',
            'gd_cpt_categories'         => __CLASS__ . '::gd_cpt_categories',
            'gd_homepage_map'           => __CLASS__ . '::gd_homepage_map',
            'gd_listing_map'            => __CLASS__ . '::gd_listing_map',
            'gd_listing_slider'         => __CLASS__ . '::gd_listing_slider',
            'gd_listings'               => __CLASS__ . '::gd_listings',
            'gd_login_box'              => __CLASS__ . '::gd_login_box',
            'gd_popular_post_category'  => __CLASS__ . '::gd_popular_post_category',
            'gd_popular_post_view'      => __CLASS__ . '::gd_popular_post_view',
            'gd_recent_reviews'         => __CLASS__ . '::gd_recent_reviews',
            'gd_related_listings'       => __CLASS__ . '::gd_related_listings',
            'gd_video'                  => __CLASS__ . '::gd_video',
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
    
    public static function gd_advanced_search( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_advanced_search', $atts, $content  );
    }
    
    public static function gd_bestof_widget( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_bestof_widget', $atts, $content  );
    }
    
    public static function gd_cpt_categories( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_cpt_categories_widget', $atts, $content  );
    }
    
    public static function gd_homepage_map( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_home_map', $atts, $content  );
    }
    
    public static function gd_listing_map( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_listing_map', $atts, $content  );
    }
    
    public static function gd_listing_slider( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_listing_slider', $atts, $content  );
    }
    
    public static function gd_listings( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_gd_listings', $atts, $content  );
    }
    
    public static function gd_login_box( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_login_box', $atts, $content  );
    }
    
    public static function gd_popular_post_category( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_popular_post_category', $atts, $content  );
    }
    
    public static function gd_popular_post_view( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_popular_post_view', $atts, $content  );
    }
    
    public static function gd_recent_reviews( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_recent_reviews', $atts, $content  );
    }
    
    public static function gd_related_listings( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_related_listings', $atts, $content  );
    }
    
    public static function gd_video( $atts = array(), $content = null ) {
        return self::shortcode_wrapper( 'geodir_sc_responsive_videos', $atts, $content  );
    }
}

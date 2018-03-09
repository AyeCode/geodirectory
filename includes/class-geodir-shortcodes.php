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
            'gd_single_slider'          => __CLASS__ . '::gd_single_slider',
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

    public static function gd_loop( $atts = array(), $content = null ) {
        global $wp_query;
        if(geodir_is_post_type_archive() ||  geodir_is_taxonomy() ||  geodir_is_page('search') || (is_author() && !empty($wp_query->query['gd_favs'])) ){
            ob_start();
            // geodir_get_template_part('listing', 'listview');
            geodir_get_template_part('content', 'archive-listing');
            //geodir_action_listings_content();
            return ob_get_clean();
        }else{
            return "";
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



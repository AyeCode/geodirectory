<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * GeoDirectory Search widget.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Loop extends WP_Super_Duper {

    /**
     * Register the advanced search widget with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['loop','archive','geodir']",
            'class_name'    => __CLASS__,
            'base_id'       => 'gd_loop', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Loop','geodirectory'), // the name of the widget.
            'widget_ops'    => array(
                'classname'   => 'geodir-loop-container', // widget class
                'description' => esc_html__('Shows the current posts from the main WP query according to the URL.  This is only used on the `GD Archive template` page.  It loops through each post and outputs the `GD Archive Item` template.','geodirectory'), // widget description
                'geodirectory' => true,
            ),
            'arguments'     => array(
                'layout'  => array(
                    'title' => __('Layout:', 'geodirectory'),
                    'desc' => __('How the listings should laid out by default.', 'geodirectory'),
                    'type' => 'select',
                    'options'   =>  geodir_get_layout_options(),
                    'default'  => 'h3',
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
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
        global $wp_query, $gd_layout_class;

        ob_start();
        if (
            geodir_is_post_type_archive()
           ||  geodir_is_taxonomy()
           ||  geodir_is_page('search')
           || ( is_author() && ! empty( $wp_query->query['gd_favs'] )
           || apply_filters( 'geodir_loop_active', false ) )
        ) {
            $widget_args = wp_parse_args( $args, array(
                'layout' => ''
            ) );

            $gd_layout_class = geodir_convert_listing_view_class( $widget_args['layout'] );

            // Check if we have listings or if we are faking it
            if ( $wp_query->post_count == 1 && empty( $wp_query->posts ) ) {
                geodir_no_listings_found();
            } elseif ( geodir_is_page( 'search' ) && ! isset( $_REQUEST['geodir_search'] ) ) {
                geodir_no_listings_found();
            } else {
                // Check we are not inside a template builder container
                if ( isset( $wp_query->posts[0] ) && $wp_query->posts[0]->post_type == 'page' ) {
                    // Reset the query count so the correct number of listings are output.
                    rewind_posts();
                    // Reset the proper loop content
                    global $wp_query, $gd_temp_wp_query;

                    $wp_query->posts = $gd_temp_wp_query;
                }

                // Check if loop has posts
                if ( ! empty( $wp_query->posts ) ) {
                    geodir_get_template_part( 'content', 'archive-listing' );
                } else {
                    geodir_no_listings_found();
                }

                // Set loop as done @todo this needs testing
                global $wp_query;
                $wp_query->current_post = $wp_query->post_count;
            }
        } else {
            _e( "No listings found that match your selection.", "geodirectory" );
        }

        // add filter to make main page comments closed after the GD loop
        add_filter( 'comments_open', array( __CLASS__, 'comments_open' ), 10, 2 );

        return ob_get_clean();
    }

    /**
     * Filter to close the comments for archive pages after the GD loop.
     * 
     * @param $open
     * @param $post_id
     *
     * @return bool
     */
    public static function comments_open($open, $post_id){

        global $post;
        if(isset($post->ID) && $post->ID==$post_id){
            $open = false;
        }

        return $open;
    }

}
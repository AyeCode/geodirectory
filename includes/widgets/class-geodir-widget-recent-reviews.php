<?php
/**
 * GeoDirectory Recent Reviews Widget
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory recent reviews widget class.
 *
 * @since 1.0.0
 */
class GeoDir_Widget_Recent_Reviews extends WP_Super_Duper {
    
    /**
     * Register the categories with WordPress.
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['geo','reviews','comments']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_recent_reviews', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Recent Reviews','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
            'widget_ops'    => array(
                'classname'   => 'geodir-wgt-recent-reviews', // widget class
                'description' => esc_html__('Display a list of recent reviews from GeoDirectory listings.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
                'gd_show_pages' => array(),
            ),
            'arguments'     => array(
                'title'  => array(
                    'title' => __('Title:', 'geodirectory'),
                    'desc' => __('The widget title.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '',
                    'desc_tip' => true,
                    'advanced' => false
                ),
                'count'  => array(
                    'title' => __('Number of reviews to show:', 'geodirectory'),
                    'desc' => __('Number of reviews to show.', 'geodirectory'),
                    'type' => 'text',
                    'default'  => '5',
                    'desc_tip' => true,
                    'advanced' => true
                ),
			    'add_location_filter'  => array(
				    'title' => __("Enable location filter", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 0,
				    'advanced' => true
			    ),
			    'use_viewing_post_type'  => array(
				    'title' => __("Filter reviews for current viewing post type", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 0,
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
        $defaults = array(
            'title' => '',
            'count' => '5',
			'add_location_filter' => '',
			'use_viewing_post_type' => ''
        );
        $instance = wp_parse_args( $args, $defaults );

		// prints the widget
        extract( $widget_args, EXTR_SKIP );

        /** This filter is documented in includes/widget/class-geodir-widget-advance-search.php.php */
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', __($instance['title'], 'geodirectory'));
        
        /**
         * Filter the number of reviews to display.
         *
         * @since 1.0.0
         *
         * @param int $instance['count'] Number of reviews to display.
         */
        $count = empty($instance['count']) ? '5' : apply_filters('widget_count', $instance['count']);

        /**
         * Filter the height and width of the avatar image in pixels.
         *
         * @since 1.0.0
         *
         * @param int $g_size Height and width of the avatar image in pixels. Default 30.
         */
        $g_size = apply_filters('geodir_recent_reviews_g_size', 30);
        /**
         * Filter the excerpt length
         *
         * @since 1.0.0
         *
         * @param int $excerpt_length Excerpt length. Default 100.
         */
        $excerpt_length = apply_filters('geodir_recent_reviews_excerpt_length', 100);

        /**
         * Filters the recent reviews default location filter.
         *
         * @since 2.0.0
         *
         * @param bool   $add_location_filter Whether the location filter is active. Default false.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $add_location_filter = apply_filters( 'geodir_recent_reviews_widget_location_filter', empty( $instance['add_location_filter'] ) ? false : true, $instance, $this->id_base );
        
        /**
         * Filters the recent reviews viewing post type.
         *
         * @since 2.0.0
         *
         * @param bool   $use_viewing_post_type Whether the viewing post type filter is active. Default false.
         * @param array  $instance An array of the widget's settings.
         * @param mixed  $id_base  The widget ID.
         */
        $use_viewing_post_type = apply_filters( 'geodir_recent_reviews_widget_use_viewing_post_type', empty( $instance['use_viewing_post_type'] ) ? false : true, $instance, $this->id_base );
        $post_type = $use_viewing_post_type ? geodir_get_current_posttype() : '';

        $comments_li = geodir_get_recent_reviews($g_size, $count, $excerpt_length, false, $post_type, $add_location_filter);

		$content = '';
        if ( !empty( $comments_li ) ) {
			ob_start();
			?>
			<div class="geodir_recent_reviews_section">
				<ul class="geodir_recent_reviews"><?php echo $comments_li; ?></ul>
			</div>
			<?php
			$content = ob_get_clean();
        }

		return $content;
    }
}

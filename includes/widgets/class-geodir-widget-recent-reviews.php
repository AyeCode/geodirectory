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
     * @since 2.0.0
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    => GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    => 'admin-site',
            'block-category'=> 'geodirectory',
            'block-keywords'=> "['geo','reviews','comments']",

            'class_name'    => __CLASS__,
            'base_id'       => 'gd_recent_reviews', // this us used as the widget id and the shortcode id.
            'name'          => __('GD > Recent Reviews','geodirectory'), // the name of the widget.
            //'disable_widget'=> true,
            'widget_ops'    => array(
                'classname'   => 'geodir-wgt-recent-reviews '.geodir_bsui_class(), // widget class
                'description' => esc_html__('Display a list of recent reviews from GeoDirectory listings.','geodirectory'), // widget description
                'customize_selective_refresh' => true,
                'geodirectory' => true,
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
                'min_rating'  => array(
	                'title' => __('Minimum rating of reviews:', 'geodirectory'),
	                'desc' => __('This will only show reviews with a rating of this number or above.', 'geodirectory'),
	                'type' => 'number',
	                'default'  => '',
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

	    $design_style = geodir_design_style();

	    if($design_style) {

		    // background
		    $arguments['bg']  = geodir_get_sd_background_input('mt');

		    // margins
		    $arguments['mt']  = geodir_get_sd_margin_input('mt');
		    $arguments['mr']  = geodir_get_sd_margin_input('mr');
		    $arguments['mb']  = geodir_get_sd_margin_input('mb',array('default'=>3));
		    $arguments['ml']  = geodir_get_sd_margin_input('ml');

		    // padding
		    $arguments['pt']  = geodir_get_sd_padding_input('pt');
		    $arguments['pr']  = geodir_get_sd_padding_input('pr');
		    $arguments['pb']  = geodir_get_sd_padding_input('pb');
		    $arguments['pl']  = geodir_get_sd_padding_input('pl');

		    // border
		    $arguments['border']  = geodir_get_sd_border_input('border');
		    $arguments['rounded']  = geodir_get_sd_border_input('rounded');
		    $arguments['rounded_size']  = geodir_get_sd_border_input('rounded_size');

		    // shadow
		    $arguments['shadow']  = geodir_get_sd_shadow_input('shadow');
		    

		    $options['arguments'] = $options['arguments'] + $arguments;
	    }

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
            'min_rating' => 0,
			'add_location_filter' => '',
			'use_viewing_post_type' => '',
            'bg'    => '',
            'mt'    => '',
            'mb'    => '3',
            'mr'    => '',
            'ml'    => '',
            'pt'    => '',
            'pb'    => '',
            'pr'    => '',
            'pl'    => '',
            'border'    => '',
            'rounded'    => '',
            'rounded_size'    => '',
            'shadow'    => '',
        );
        $instance = wp_parse_args( $args, $defaults );

		// prints the widget
        extract( $widget_args, EXTR_SKIP );

	    $design_style = geodir_design_style();

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
        $g_size = apply_filters('geodir_recent_reviews_g_size', $design_style ? 44 : 30);
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

        $comments_li = self::get_recent_reviews($g_size, $count, $excerpt_length, false, $post_type, $add_location_filter,$instance['min_rating']);

	    // wrap class
	    $wrap_class = geodir_build_aui_class($instance);

		$content = '';
        if ( !empty( $comments_li ) ) {
			ob_start();
			?>
			<div class="geodir_recent_reviews_section <?php echo $wrap_class;?>">
				<ul class="geodir_recent_reviews list-unstyled m-0 p-0"><?php echo $comments_li; ?></ul>
			</div>
			<?php
			$content = ob_get_clean();
        }

		return $content;
    }


	/**
	 * Returns the recent reviews.
	 *
	 * @since   1.0.0
	 * @since   1.6.21 Recent reviews doesn't working well with WPML.
	 * @since   2.0.0 Location filter & current post type filter added.
	 * @package GeoDirectory
	 *
	 * @global object $wpdb        WordPress Database object.
	 *
	 * @param int $g_size          Optional. Avatar size in pixels. Default 60.
	 * @param int $no_comments     Optional. Number of reviews you want to display. Default: 10.
	 * @param int $comment_lenth   Optional. Maximum number of characters you want to display. After that read more link
	 *                             will appear.
	 * @param bool $show_pass_post Optional. Not yet implemented.
	 * @param string $post_type    The post type.
	 * @param bool $add_location_filter Whether the location filter is active. Default false.
	 *
	 * @return string Returns the recent reviews html.
	 */
	public static function get_recent_reviews( $g_size = 60, $no_comments = 10, $comment_lenth = 60, $show_pass_post = false, $post_type = '', $add_location_filter = false, $min_rating = 0 ) {
		global $wpdb, $tablecomments, $tableposts, $rating_table_name, $table_prefix;
		$tablecomments = $wpdb->comments;
		$tableposts    = $wpdb->posts;
		$comments_echo  = '';
		$join = "JOIN " . $wpdb->comments . " AS c ON c.comment_ID = r.comment_id JOIN " . $wpdb->posts . " AS p ON p.ID = c.comment_post_ID";
		$where = "c.comment_parent = 0 AND c.comment_approved = 1 AND r.rating > 0 AND p.post_status = 'publish'";

		if(absint($min_rating)){
			$where .= $wpdb->prepare(" AND r.rating >= %d ",absint($min_rating));
		}

		if ( !empty( $post_type ) ) {
			$where .= $wpdb->prepare( " AND p.post_type = %s", $post_type );
		}

		if ( GeoDir_Post_types::supports( $post_type, 'location' ) && $add_location_filter && defined( 'GEODIRLOCATION_VERSION' ) ) {
			$source = geodir_is_page( 'search' ) ? 'session' : 'query_vars';
			$location_terms = geodir_get_current_location_terms( $source );
			$country = !empty( $location_terms['country'] ) ? get_actual_location_name( 'country', $location_terms['country'] ) : '';
			$region = !empty( $location_terms['region'] ) ? get_actual_location_name( 'region', $location_terms['region'] ) : '';
			$city = !empty( $location_terms['city'] ) ? get_actual_location_name( 'city', $location_terms['city'] ) : '';

			if ( $country ) {
				$where .= $wpdb->prepare( " AND r.country LIKE %s", $country );
			}
			if ( $region ) {
				$where .= $wpdb->prepare( " AND r.region LIKE %s", $region );
			}
			if ( $city ) {
				$where .= $wpdb->prepare( " AND r.city LIKE %s", $city );
			}
		}

		$join = apply_filters( 'geodir_recent_reviews_query_join', $join, $post_type, $add_location_filter );
		$where = apply_filters( 'geodir_recent_reviews_query_where', $where, $post_type, $add_location_filter );

		$where = ! empty( $where ) ? "WHERE {$where}" : "";
		$count = $wpdb->prepare( "%d", $no_comments );
		$request = "SELECT c.comment_ID, c.comment_author, c.comment_author_email, c.comment_content, c.comment_date, r.rating, r.user_id, r.post_id, r.post_type FROM " . GEODIR_REVIEW_TABLE . " AS r {$join} {$where} ORDER BY c.comment_date DESC, c.comment_ID DESC LIMIT $count";

		$comments = $wpdb->get_results( $request );

		$design_style = geodir_design_style();

		foreach ( $comments as $comment ) {
			$comment_id      = $comment->comment_ID;
			$comment_content = strip_tags( $comment->comment_content );
			$comment_content = preg_replace( '#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content );

			$permalink            = get_permalink( $comment->post_id ) . "#comment-" . $comment->comment_ID;
			$comment_author_email = $comment->comment_author_email;
			$comment_post_ID      = $comment->post_id;

			$post_title        = get_the_title( $comment_post_ID );
			$permalink         = get_permalink( $comment_post_ID );
			$comment_permalink = $permalink . "#comment-" . $comment->comment_ID;
			$readmore_seo_class = $design_style ? 'sr-only' : '';
			$read_more         = '<a class="comment_excerpt" href="' . $comment_permalink . '">' . __( 'Read more', 'geodirectory' ) . '<span class="gd-visuallyhidden '.$readmore_seo_class.'"> ' . __( 'about this listing', 'geodirectory' ) . '</span></a>';

			$comment_content_length = strlen( $comment_content );
			if ( $comment_content_length > $comment_lenth ) {
				$comment_excerpt = geodir_utf8_substr( $comment_content, 0, $comment_lenth ) . '... ' . $read_more;
			} else {
				$comment_excerpt = $comment_content;
			}

			if ( $comment->user_id ) {
				$user_profile_url = get_author_posts_url( $comment->user_id );
			} else {
				$user_profile_url = '';
			}

			if ( $comment_id ) {

				$avatar_size = apply_filters( 'geodir_comment_avatar_size', $g_size );

				$template = $design_style ? $design_style."/reviews/recent-item.php" : "legacy/reviews/recent-item.php";

				$args = array(
					'comment'  => $comment,
					'comment_id'  => $comment_id,
					'avatar_size'  => $avatar_size,
					'permalink'  =>  $permalink,
					'comment_excerpt'  => $comment_excerpt,
					'post_title'  => $post_title,
					'comment_post_ID'  => $comment_post_ID,
				);
				$comments_echo .= geodir_get_template_html( $template, $args );

			}
		}

		return $comments_echo;
	}

}

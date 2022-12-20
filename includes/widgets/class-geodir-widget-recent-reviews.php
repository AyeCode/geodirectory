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
                    'advanced' => false,
                    'group'     => __("Title","geodirectory")
                ),
                'count'  => array(
                    'title' => __('Number of reviews to show:', 'geodirectory'),
                    'desc' => __('Number of reviews to show.', 'geodirectory'),
                    'type' => 'number',
                    'default'  => '5',
                    'desc_tip' => true,
                    'advanced' => false,
                    'group'     => __("Filter","geodirectory")
                ),
                'min_rating'  => array(
	                'title' => __('Minimum rating of reviews:', 'geodirectory'),
	                'desc' => __('This will only show reviews with a rating of this number or above.', 'geodirectory'),
	                'type' => 'number',
	                'default'  => '',
	                'desc_tip' => true,
	                'advanced' => false,
	                'group'     => __("Filter","geodirectory")
                ),
			    'add_location_filter'  => array(
				    'title' => __("Enable location filter", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 0,
				    'advanced' => false,
				    'group'     => __("Filter","geodirectory")
			    ),
			    'use_viewing_post_type'  => array(
				    'title' => __("Filter reviews for current viewing post type", 'geodirectory'),
				    'type' => 'checkbox',
				    'desc_tip' => true,
				    'value'  => '1',
				    'default'  => 0,
				    'advanced' => false,
				    'group'     => __("Filter","geodirectory")
				),
				'review_by_author'      => array(
					'title'    => __('Reviews by author:', 'geodirectory'),
					'desc'     => __('Filter by current_user, current_author or ID (default = unfiltered). current_user: Filters the reviews by author id of the logged in user. current_author: Filters the reviews by author id of current viewing post/listing/profile', 'geodirectory'),
					'type'     => 'text',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
					'group'    => __( "Filter", "geodirectory" )
				),
				'post_id'    => array(
					'title'    => __('Reviews for Post ID:', 'geodirectory'),
					'desc'     => __('Filter by current or ID or blank (default = unfiltered). current: filters reviews submitted under current viewing post. ID: filters reviews submitted under specific post id. Leave blank to not apply post id filter.', 'geodirectory'),
					'type'     => 'text',
					'default'  => '',
					'desc_tip' => true,
					'advanced' => false,
					'group'    => __( "Filter", "geodirectory" )
				)
            )
        );

	    $design_style = geodir_design_style();

	    if($design_style) {

		    // title styles
		    $title_args = geodir_get_sd_title_inputs();
		    $options['arguments'] = $options['arguments'] + $title_args;

		    $options['arguments']['row_items'] = array(
			    'title' => __('Row Items', 'geodirectory'),
			    'desc' => __('The number of items in a row on desktop view.', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('Default (1)', 'geodirectory'),
				    "2" => "2",
				    "3" => "3",
				    "4" => "4",
				    "5" => "5",
				    "6" => "6",
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );

		    $options['arguments']['carousel'] = array(
			    'title' => __('Carousel', 'geodirectory'),
			    'desc' => __('Display as a carousel.', 'geodirectory'),
			    'type' => 'select',
			    'options'   =>  array(
				    "" => __('None', 'geodirectory'),
				    "slide" => __('Slide', 'geodirectory'),
				    "fade" => __('Fade', 'geodirectory'),
			    ),
			    'default'  => '',
			    'desc_tip' => true,
			    'advanced' => false,
			    'group'     => __("Design","geodirectory")
		    );

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
            'row_items' => '',
            'review_by_author' => '',
            'post_id' => '',
            'carousel' => '',
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
		/**
		 * Filter the widget review_by_author param.
		 *
		 * @since 2.1.0.8
		 *
		 * @param string $instance ['review_by_author'] Filter by author.
		 */
		$review_by_author = empty( $instance['review_by_author'] ) ? '' : apply_filters( 'widget_review_by_author', $instance['review_by_author'], $instance, $this->id_base );
		if ( ! empty( $review_by_author ) ) {
			global $post;
			// 'current' left for backwards compatibility
			if ( $review_by_author === 'current' || $review_by_author === 'current_author' ) {
				if (
					! empty( $post )
					&& is_object( $post )
					&& property_exists( $post, 'post_type' )
					&& property_exists( $post, 'post_author' )
					&& $post->post_type != 'page'
					&& isset( $post->post_author )
				) {
					$review_by_author = $post->post_author;
				} else {
					$review_by_author = -1; // Don't show any review widget.
				}
			} elseif ( $review_by_author === 'current_user' ) {
				if (
					is_user_logged_in()
					&&
					( ! empty( get_current_user_id() ) )
				) {
					$review_by_author = get_current_user_id();
				} else {
					$review_by_author = -1; // If not logged in then don't show review widget.
				}
			} elseif ( absint( $review_by_author ) > 0 ) {
				$review_by_author = absint( $review_by_author );
			} else {
				$review_by_author = -1; // Don't show review widget.
			}
		}

		/**
		 * Filter the widget post_id param.
		 *
		 * @since 2.1.1.0
		 *
		 * @param string $instance ['post_id'] Filter by author.
		 */
		$post_id = empty( $instance['post_id'] ) ? '' : apply_filters( 'widget_review_by_post_id', $instance['post_id'], $instance, $this->id_base );

		if ( ! empty( $post_id ) ) {
			// 'current' left for backwards compatibility.
			if ( $post_id === 'current' ) {
				$post_id = get_the_ID();
			} elseif ( absint( $post_id ) > 0 ) {
				$post_id = absint( $post_id );
			} else {
				$post_id = -1; // Don't show review widget.
			}
		}

	    // wrap class
	    $wrap_class = geodir_build_aui_class($instance);

	    $ul_class = '';
	    $wrap_extra = '';
	    $slider_id = 'gd-reviews-'.$this->get_instance_hash();
	    if ( $design_style ) {


		    if ( ! empty( $instance['carousel'] ) ) {
			    $wrap_extra .= "data-ride='carousel'  data-limit_show='".absint( $instance['row_items'] )."'";
			    $wrap_class .= ' carousel slide carousel-multiple-items';
			    if ( $instance['carousel'] == 'fade' ) {
				    $wrap_class .= ' carousel-fade';
			    }

			    $ul_class .= ' carousel-inner';
		    }else{
			     $ul_class .= " p-0 row row-cols-1 ";
			    if(!empty($instance['row_items'])){
				    $ul_class .= " row-cols-sm-" . absint( $instance['row_items'] );
			    }
		    }
	    }

	    $comments_li = self::get_recent_reviews($g_size, $count, $excerpt_length, false, $post_type, $add_location_filter,$instance['min_rating'],$instance['carousel'], $review_by_author, $post_id );

		$content = '';
        if ( !empty( $comments_li ) ) {
			ob_start();
			?>
			<div id="<?php echo $slider_id;?>" class="geodir_recent_reviews_section <?php echo $wrap_class;?> " <?php echo $wrap_extra;?> >
				<ul class="geodir_recent_reviews list-unstyled my-0 <?php echo $ul_class;?>"><?php echo $comments_li; ?></ul>

			<?php
	        if ( $design_style && $instance['carousel']) {
				$reviews_count = substr_count($comments_li," carousel-item");
		        $loop_count = 0;
		        ?>
		        <ol class="carousel-indicators position-relative m-0">
			        <?php
			        while($loop_count <= $reviews_count) {
				        $active = $loop_count == 0 ? 'active' : '';
				        echo '<li data-target="#'.$slider_id.'" data-slide-to="'.$loop_count.'" class="my-1 mx-1 bg-dark '.$active.'"></li>';
				        $loop_count++;
			        }
			        ?>
		        </ol>
	        <?php }?>

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
	public static function get_recent_reviews( $g_size = 60, $no_comments = 10, $comment_lenth = 60, $show_pass_post = false, $post_type = '', $add_location_filter = false, $min_rating = 0, $carousel = '', $review_by_author = '', $post_id = '' ) {
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
		if ( ! empty( $review_by_author ) ) {
			$where .= $wpdb->prepare( ' AND r.user_id = %s', $review_by_author );
		}
		if ( ! empty( $post_id ) ) {
			$where .= $wpdb->prepare( ' AND p.ID = %s', $post_id );
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
		$i = 0;
		foreach ( $comments as $comment ) {
			$comment_id      = $comment->comment_ID;
			$comment_content = strip_tags( $comment->comment_content );
			$comment_content = preg_replace( '#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content );

			$permalink            = get_permalink( $comment->post_id ) . "#comment-" . $comment->comment_ID;
			$comment_author_email = $comment->comment_author_email;
			$comment_post_ID      = $comment->post_id;

			$post_title        = trim( esc_html( strip_tags( stripslashes( get_the_title( $comment_post_ID ) ) ) ) );
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
					'carousel'  => $carousel,
					'active'  => $carousel && $i===0,
				);
				$comments_echo .= geodir_get_template_html( $template, $args );
				$i++;
			}
		}

		return $comments_echo;
	}

}
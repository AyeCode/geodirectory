<?php
/**
 * GeoDirectory Post Reviews Widget
 *
 * @since 2.0.0.63
 *
 * @package GeoDirectory
 */

/**
 * GeoDirectory post reviews widget class.
 *
 * @since 2.0.0.63
 */
class GeoDir_Widget_Post_Reviews extends WP_Super_Duper {
    
    /**
     * Register the widget with WordPress.
     *
     * @since 2.0.0.63
     *
     */
    public function __construct() {

        $options = array(
            'textdomain'    		=> GEODIRECTORY_TEXTDOMAIN,
            'block-icon'    		=> 'admin-site',
            'block-category'		=> 'widgets',
            'block-keywords'		=> "['geo','reviews','comments']",

            'class_name'    		=> __CLASS__,
            'base_id'       		=> 'gd_post_reviews', // this us used as the widget id and the shortcode id.
            'name'          		=> __('GD > Post Reviews','geodirectory'), // the name of the widget.
            'widget_ops'    		=> array(
                'classname'   		=> 'geodir-wgt-post-reviews', // widget class
                'description' 		=> esc_html__("Display the current listing's revies.",'geodirectory'), // widget description
                'customize_selective_refresh' => true,
				'geodirectory' 		=> true,
				'gd_wgt_showhide'   => 'show_on',
                'gd_wgt_restrict'   => array( 'gd-detail' ),
            ),
            'arguments'     		=> array(
                'title'  			=> array(
                    'title' 		=> __('Title:', 'geodirectory'),
                    'desc' 			=> __('The widget title.', 'geodirectory'),
                    'type' 			=> 'text',
                    'default'  		=> __('Reviews', 'geodirectory'),
                    'desc_tip' 		=> true,
                    'advanced' 		=> false
                ),
                'count'  			=> array(
                    'title' 		=> __('Count:', 'geodirectory'),
                    'desc' 			=> __('Number of reviews to show per page.', 'geodirectory'),
                    'type' 			=> 'text',
                    'default'  		=> '5',
                    'desc_tip' 		=> true,
                    'advanced' 		=> false
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
	 * @since 2.0.0.63
     *
     * @return mixed|string|void
     */
    public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $gd_post;

		//Set current listing id
		$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : '';
		
		if(! $post_id ){
			return '';
		}

        $defaults = array(
            'title' => '',
            'count' => '5',
        );
        $instance = wp_parse_args( $args, $defaults );

        /**
         * Filter the height and width of the avatar image in pixels.
         *
         * @since 2.0.0.63
         *
         * @param int $g_size Height and width of the avatar image in pixels. Default 30.
         */
        $g_size = apply_filters('geodir_post_reviews_g_size', 30);
        /**
         * Filter the excerpt length
         *
         * @since 2.0.0.63
         *
         * @param int $excerpt_length Excerpt length. Default 100.
         */
        $excerpt_length = apply_filters('geodir_post_reviews_excerpt_length', 100);
		
		//Fetch the comments
        $comments_li    = self::get_post_reviews($post_id, $g_size, $instance['count'], $excerpt_length);

		$content = '';
        if ( !empty( $comments_li ) ) {
			ob_start();
			?>
			<div class="geodir_post_reviews_section">
				<ul class="geodir_post_reviews"><?php echo $comments_li; ?></ul>
			</div>
			<?php
			$content = ob_get_clean();
        }

		return $content;
    }


	/**
	 * Returns the post reviews.
	 *
	 * @since 2.0.0.63
	 * @package GeoDirectory
	 *
	 * @global object $wpdb        WordPress Database object.
	 *
	 * @param int $post_id         The post whose reviews should be fetched
	 * @param int $avatar_size     Optional. Avatar size in pixels. Default 30.
	 * @param int $no_comments     Optional. Number of reviews you want to display per page. Default: 5.
	 * @param int $comment_lenth   Optional. Maximum number of characters you want to display. After that read more link
	 *                             will appear.
	 *
	 * @return string Returns the post reviews html.
	 */
	public static function get_post_reviews( $post_id, $avatar_size = 30, $no_comments = 5, $comment_lenth = 60 ) {
		global $wpdb, $post;

		//Prepare the sql needed to fetch comments
		$table = GEODIR_REVIEW_TABLE;
		$sql   = $wpdb->prepare( 
			"SELECT c.comment_ID, c.comment_author, c.comment_author_url, c.comment_author_email, c.comment_content, c.comment_date, r.rating, r.user_id, r.post_id, r.post_type 
				FROM $table AS r 
				JOIN {$wpdb->comments}  AS c ON c.comment_ID = r.comment_id 
				WHERE c.comment_parent = 0 AND c.comment_approved = 1 AND r.rating > 0 AND r.post_id = %d
				LIMIT %d",
			$post_id,
			$no_comments
		);

		//Then fetch them from the db
		$comments = $wpdb->get_results( $sql );

		//output
		$comments_echo = '';

		foreach ( $comments as $comment ) {

			//Maybe abort early
			if (! $comment->comment_ID ) {
				continue;
			}

			//Prepare comment parameters
			$comment_id      	  = $comment->comment_ID;
			$comment_content 	  = strip_tags( $comment->comment_content );
			$comment_content 	  = preg_replace( '#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content );
			$comment_excerpt 	  = $comment_content;
			$read_more 			  = '';
			$comment_author_url   = esc_url( $comment->comment_author_url );
			$comment_author_email = $comment->comment_author_email;
			$comment_post_ID      = $comment->post_id;

			//If the comment is longer than the specified length...
			$comment_content_length = strlen( $comment_content );
			if ( $comment_content_length > $comment_lenth ) {
				$comment_excerpt    = geodir_utf8_substr( $comment_content, 0, $comment_lenth ) . '... ';
				$read_more         	= '<a class="comment-excerpt" href="#">' . __( 'Read more', 'geodirectory' ) . '</a>';
			}

			//Output the comment
			$comments_echo .= '<li class="clearfix"><span class="geodir-review-content">';

			//Maybe link to the comment author's website
			if(! empty( $comment_author_url ) ) {
				$comments_echo .= "<a href='$comment_author_url' rel='external nofollow'>";
			}

			//Add commentors avatar to comment
			if ( function_exists( 'get_avatar' ) ) {
				$comments_echo .= sprintf(
					'<span class="li%s geodir-review-avatar">%s</span>',
					$comment_id,
					get_avatar( $comment_author_email, $avatar_size )
				);
			}

			//Display user name
			$comments_echo .= '<span class="geodir-review-author">' . $comment->comment_author . '</span> ';

			//And maybe let it be known if he is the comment author
			if( $comment->user_id === $post->post_author ){
				$comments_echo .= '<span class="gd-badge"> ' . __( 'Post author', 'geodirectory' ) . '</span>';
			}

			if(! empty( $comment_author_url ) ) {
				$comments_echo .= "</a>";
			}

			//Date
			$comments_echo .= sprintf( 
				'<div><span class="date" title="%s"><i class="far fa-calendar-alt"></i> %s</span></div>',
				$comment->comment_date,
                sprintf( _x( '%s ago', '%s = human-readable time difference', 'geodirectory' ), human_time_diff( strtotime( $comment->comment_date ), current_time( 'timestamp' ) ) )
            );


			//Display rating stars...
			$comments_echo .= geodir_get_rating_stars( $comment->rating, $comment_post_ID );

			//And the main content
			$comments_echo .= "<p class='geodir-review-text'><span class='geodir-review-text-content gd-hide'>$comment_content</span><span class='geodir-review-text-excerpt'>$comment_excerpt $read_more</span></p>";

			$comments_echo .= '</span></li>';
			
		}

		return $comments_echo;
	}

}

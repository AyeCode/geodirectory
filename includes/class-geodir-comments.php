<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class GeoDir_Comments {

	/**
	 * Initiate the comments class.
	 */
	public static function init() {
		add_action( 'comment_form_logged_in_after', array( __CLASS__, 'rating_input' ) );
		add_action( 'comment_form_before_fields', array( __CLASS__, 'rating_input' ) );

		// add ratings to comment text
		add_filter( 'comment_text', array(__CLASS__, 'wrap_comment_text'), 40, 2 );

		// replace comments template
		add_filter( "comments_template", array(__CLASS__, "comments_template") ); // @todo, maybe we want to use the themes own template?

		// remove replies from comments count so only to show reviews
		add_filter( 'get_comments_number', array(__CLASS__, 'review_count_exclude_replies'), 10, 2 );

		// set if listing has comments open
		add_filter( 'comments_open', array(__CLASS__,'comments_open'), 10, 2 ); // @todo we maybe don't need this with the new preview system?

		// comment actions
		add_action( 'comment_post', array(__CLASS__,'save_rating') );
		add_action( 'wp_set_comment_status', array(__CLASS__, 'status_change'), 10, 2 );
		add_action( 'edit_comment', array(__CLASS__, 'edit_comment') );
		add_action( 'delete_comment', array(__CLASS__, 'delete_comment') );
	}
	
	/**
	 * Update post overall rating and rating count.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
	 * @param int $post_id The post ID.
	 * @param string $post_type The post type.
	 * @param bool $delete Depreciated since ver 1.3.6.
	 */
	public static function update_post_rating( $post_id = 0, $post_type = '', $delete = false ) {
		global $wpdb, $plugin_prefix, $comment;
		if ( ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}
		$detail_table         = $plugin_prefix . $post_type . '_detail';
		$post_newrating       = geodir_get_post_rating( $post_id, 1 );
		$post_newrating_count = geodir_get_review_count_total( $post_id );

		if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $detail_table . "'" ) == $detail_table ) {

			$wpdb->query(
				$wpdb->prepare(
					"UPDATE " . $detail_table . " SET
						overall_rating = %f,
						rating_count = %f
						where post_id = %d",
					array( $post_newrating, $post_newrating_count, $post_id )
				)
			);

			update_post_meta( $post_id, 'overall_rating', $post_newrating );
			update_post_meta( $post_id, 'rating_count', $post_newrating_count );
		}
		/**
		 * Called after Updating post overall rating and rating count.
		 *
		 * @since 1.0.0
		 * @since 1.4.3 Added `$post_id` param.
		 * @package GeoDirectory
		 *
		 * @param int $post_id The post ID.
		 */
		do_action( 'geodir_update_post_rating', $post_id );

	}

	/**
	 * Get review details using comment ID.
	 *
	 * Returns review details using comment ID. If no reviews returns false.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|mixed
	 */
	public static function get_review( $comment_id = 0 ) {
		global $wpdb;

		$reatings = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id = %d",
				array( $comment_id )
			)
		);

		if ( ! empty( $reatings ) ) {
			return $reatings;
		} else {
			return false;
		}
	}

	/**
	 * Delete review details when deleting comment.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 */
	public static function delete_comment( $comment_id ) {
		global $wpdb;

		$review_info = self::get_review( $comment_id );
		if ( $review_info ) {
			self::update_post_rating( $review_info->post_id );
		}

		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id=%d",
				array( $comment_id )
			)
		);
	}

	/**
	 * Update comment rating.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global int $user_ID The current user ID.
	 */
	public static function edit_comment( $comment_id = 0 ) {
		global $wpdb;

		if ( ! isset( $_REQUEST['geodir_overallrating'] ) ) {
			return;
		}

		$comment_info = get_comment( $comment_id );
		if ( empty( $comment_info ) ) {
			return;
		}

		$post_id    = $comment_info->comment_post_ID;
		$old_rating = geodir_get_comment_rating( $comment_info->comment_ID );
		$post_type  = get_post_type( $post_id );
		$rating 	= absint($_REQUEST['geodir_overallrating']);

		if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
			if ( isset( $old_rating ) ) {
				$sqlqry = $wpdb->prepare( "UPDATE " . GEODIR_REVIEW_TABLE . " SET
					rating = %f 
					WHERE comment_id = %d ", 
					array(
						$rating,
						$comment_id
					) 
				);

				$wpdb->query( $sqlqry );

				// update rating
				self::update_post_rating( $post_id, $post_type );
			}
		}
	}

	/**
	 * Update comment status when changing the rating.
	 *
	 * @param int $comment_id The comment ID.
	 * @param int|string $status The comment status.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 * @global int $user_ID The current user ID.
	 */
	public static function status_change( $comment_id, $status ) {
		global $wpdb;

		if ( $status == 'delete' ) {
			return;
		}

		$comment_info = get_comment( $comment_id );
		if ( empty( $comment_info ) ) {
			return;
		}

		$post_id 		 = isset( $comment_info->comment_post_ID ) ? $comment_info->comment_post_ID : '';
		$comment_info_ID = isset( $comment_info->comment_ID ) ? $comment_info->comment_ID : '';
		$old_rating      = geodir_get_comment_rating( $comment_info_ID );
		$post_type 		 = get_post_type( $post_id );

		if ( $comment_id ) {
			$rating = $old_rating;

			if ( isset( $old_rating ) ) {
				$sqlqry = $wpdb->prepare( "UPDATE " . GEODIR_REVIEW_TABLE . " SET
					rating = %f 
					WHERE comment_id = %d ", 
					array(
						$rating,
						$comment_id
					) 
				);

				$wpdb->query( $sqlqry );

				// update rating
				self::update_post_rating( $post_id, $post_type );
			}
		}
	}

	/**
	 * Save rating details for a comment.
	 *
	 * @param int $comment The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global int $user_ID The current user ID.
	 */
	public static function save_rating( $comment = 0 ) {
		global $wpdb, $user_ID;
		
		if ( ! isset( $_REQUEST['geodir_overallrating'] ) ) {
			return;
		}

		$comment_info = get_comment( $comment );
		if ( empty( $comment_info ) ) {
			return;
		}

		$post_id = $comment_info->comment_post_ID;

		$gd_post = geodir_get_post_info( $post_id );
		if ( empty( $gd_post ) ) {
			return;
		}

		$rating = absint($_REQUEST['geodir_overallrating']);

		if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
			$sqlqry = $wpdb->prepare( "INSERT INTO " . GEODIR_REVIEW_TABLE . " SET
				post_id		= %d,
				post_type 	= %s,
				user_id		= %d,
				comment_id	= %d,
				rating 		= %f,
				city		= %s, 
				region		= %s, 
				country		= %s,
				longitude	= %s,
				latitude	= %s
				",
				array(
					$post_id,
					$gd_post->post_type,
					$user_ID,
					$comment_info->comment_ID,
					$rating,
					$gd_post->city,
					$gd_post->region,
					$gd_post->country,
					$gd_post->latitude,
					$gd_post->longitude
				)
			);

			$wpdb->query( $sqlqry );

			/**
			 * Called after saving the comment.
			 *
			 * @since 1.0.0
			 * @package GeoDirectory
			 *
			 * @param array $_REQUEST {
			 *    Attributes of the $_REQUEST variable.
			 *
			 * @type string $geodir_overallrating Overall rating.
			 * @type string $comment Comment text.
			 * @type string $submit Submit button text.
			 * @type string $comment_post_ID Comment post ID.
			 * @type string $comment_parent Comment Parent ID.
			 * @type string $_wp_unfiltered_html_comment Unfiltered html comment string.
			 *
			 * }
			 */
			do_action( 'geodir_after_save_comment', $_REQUEST, 'Comment Your Post' );

			self::update_post_rating( $post_id );
		}
	}

	/**
	 * Check whether the current post is open for reviews.
	 *
	 * @param bool $open Whether the current post is open for reviews.
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if allowed otherwise False.
	 */
	public static function comments_open( $open, $post_id ) {
		if ( $open && $post_id && geodir_is_page( 'detail' ) ) {
			if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending', 'auto-draft', 'trash' ) ) ) {
				$open = false;
			}
		}
		return $open;
	}

	/**
	 * Fix comment count by not listing replies as reviews
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 * @global object $post The current post object.
	 *
	 * @param int $count The comment count.
	 * @param int $post_id The post ID.
	 *
	 * @return bool|null|string The comment count.
	 */
	public static function review_count_exclude_replies( $count, $post_id ) {
		if ( ! is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) ) {
			$post_types = geodir_get_posttypes();

			if ( in_array( get_post_type( $post_id ), $post_types ) && ! geodir_cpt_has_rating_disabled( (int) $post_id ) ) {
				$review_count = self::get_post_review_count_total( $post_id );

				return $review_count;
			} else {
				return $count;
			}
		} else {
			return $count;
		}
	}

	/**
	 * Sets the comment template.
	 *
	 * Sets the comment template using filter {@see 'comments_template'}.
	 *
	 * @global object $post The current post object.
	 * @param string $comment_template Old comment template.
	 * @return string New comment template.
	 */
	public static function comments_template( $comment_template ) {
		global $post,$gd_is_comment_template_set;

		$post_types = geodir_get_posttypes();

		if ( ! ( is_singular() && ( have_comments() || ( isset( $post->comment_status ) && 'open' == $post->comment_status ) ) ) ) {
			return $comment_template;
		}
		if ( in_array( $post->post_type, $post_types ) ) { // assuming there is a post type called business

			// if we already loaded the template don't load it again
			if($gd_is_comment_template_set){
				return geodir_plugin_path() . '/index.php'; // a blank template to remove default if called more than once.
			}

			if ( geodir_cpt_has_rating_disabled( $post->post_type ) ) {
				return $comment_template;
			}

			$template = locate_template( array( "geodirectory/reviews.php" ) ); // Use theme template if available
			if ( ! $template ) {
				$template = geodir_plugin_path() . '/templates/reviews.php';
			}
			$gd_is_comment_template_set = true;

			return $template;
		}

		return $comment_template;
	}

	/**
	 * Add rating information in comment text.
	 *
	 * @param string $content The comment content.
	 * @param object|string $comment The comment object.
	 *
	 * @return string The comment content.
	 */
	public static function wrap_comment_text( $content, $comment = '' ) {
		if ( ! empty( $comment->comment_post_ID ) && geodir_cpt_has_rating_disabled( (int) $comment->comment_post_ID ) ) {
			if ( ! is_admin() ) {
				return '<div class="description">' . $content . '</div>';
			} else {
				return $content;
			}
		} else {
			$rating = 0;
			if ( ! empty( $comment ) ) {
				$rating = self::get_comment_rating( $comment->comment_ID );
			}
			if ( $rating != 0 && ! is_admin() ) {
				return '<div><div class="gd-rating-text">' . __( 'Overall Rating', 'geodirectory' ) . ': <div class="rating">' . $rating . '</div></div>' . geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div><div class="description">' . $content . '</div>';
			} else {
				return $content;
			}
		}
	}

	/**
	 * Comment HTML markup.
	 *
	 * @global object $post The current post object.
	 *
	 * @param object $comment The comment object.
	 * @param string|array $args {
	 *     Optional. Formatting options.
	 *
	 * @type object $walker Instance of a Walker class to list comments. Default null.
	 * @type int $max_depth The maximum comments depth. Default empty.
	 * @type string $style The style of list ordering. Default 'ul'. Accepts 'ul', 'ol'.
	 * @type string $callback Callback function to use. Default null.
	 * @type string $end -callback      Callback function to use at the end. Default null.
	 * @type string $type Type of comments to list.
	 *                                     Default 'all'. Accepts 'all', 'comment', 'pingback', 'trackback', 'pings'.
	 * @type int $page Page ID to list comments for. Default empty.
	 * @type int $per_page Number of comments to list per page. Default empty.
	 * @type int $avatar_size Height and width dimensions of the avatar size. Default 32.
	 * @type string $reverse_top_level Ordering of the listed comments. Default null. Accepts 'desc', 'asc'.
	 * @type bool $reverse_children Whether to reverse child comments in the list. Default null.
	 * @type string $format How to format the comments list.
	 *                                     Default 'html5' if the theme supports it. Accepts 'html5', 'xhtml'.
	 * @type bool $short_ping Whether to output short pings. Default false.
	 * @type bool $echo Whether to echo the output or return it. Default true.
	 * }
	 *
	 * @param int $depth Depth of comment.
	 */
	public static function list_comments_callback( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' :
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class( 'geodir-comment' ); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'geodirectory' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'geodirectory' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default :
				// Proceed with normal comments.
				global $post;
				?>
				<li <?php comment_class( 'geodir-comment' ); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="comment-<?php comment_ID(); ?>" class="comment">
					<header class="comment-meta comment-author vcard">
						<?php
						/**
						 * Filter to modify comment avatar size
						 *
						 * You can use this filter to change comment avatar size.
						 *
						 * @since 1.0.0
						 * @package GeoDirectory
						 */
						$avatar_size = apply_filters( 'geodir_comment_avatar_size', 44 );
						echo get_avatar( $comment, $avatar_size );
						printf( '<cite><b class="reviewer">%1$s</b> %2$s</cite>',
							get_comment_author_link(),
							// If current post author is also comment author, make it known visually.
							( $comment->user_id === $post->post_author ) ? '<span>' . __( 'Post author', 'geodirectory' ) . '</span>' : ''
						);
						echo "<span class='item'><small><span class='fn'>$post->post_title</span></small></span>";
						printf( '<a href="%1$s"><time datetime="%2$s" class="dtreviewed">%3$s<span class="value-title" title="%2$s"></span></time></a>',
							esc_url( get_comment_link( $comment->comment_ID ) ),
							get_comment_time( 'c' ),
							/* translators: 1: date, 2: time */
							sprintf( __( '%1$s at %2$s', 'geodirectory' ), get_comment_date(), get_comment_time() )
						);
						?>
					</header>
					<!-- .comment-meta -->

					<?php if ( '0' == $comment->comment_approved ) : ?>
						<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'geodirectory' ); ?></p>
					<?php endif; ?>

					<section class="comment-content comment">
						<?php comment_text(); ?>
					</section>
					<!-- .comment-content -->

					<div class="comment-links">
						<?php edit_comment_link( __( 'Edit', 'geodirectory' ), '<p class="edit-link">', '</p>' ); ?>
						<div class="reply">
							<?php comment_reply_link( array_merge( $args, array(
								'reply_text' => __( 'Reply', 'geodirectory' ),
								'after'      => ' <span>&darr;</span>',
								'depth'      => $depth,
								'max_depth'  => $args['max_depth']
							) ) ); ?>
						</div>
					</div>

					<!-- .reply -->
				</article>
				<!-- #comment-## -->
				<?php
				break;
		endswitch; // end comment_type check
	}

	/**
	 * Add rating fields in comment form.
	 *
	 * Adds a rating input field in comment form.
	 *
	 * @since 1.0.0
	 * @since 1.6.16 Changes for disable review stars for certain post type.
	 * @package GeoDirectory
	 * @global object $post The post object.
	 */
	public static function rating_input( $comment = array() ) {
		global $post;


		if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
			$post_type = get_post_type( $comment->comment_post_ID );
		} else {
			$post_type = $post->post_type;
		}
		$post_types = geodir_get_posttypes();

		if ( ! empty( $post_type )
		     && in_array( $post_type , $post_types )
		     && ! ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post_type ) )
		) {
			$rating = 0;
			if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
				$rating = self::get_comment_rating( $comment->comment_ID );
			}
			echo self::rating_input_html( $rating );
		}
	}
	
	/**
	 * The rating input html.
	 */
	public static function rating_input_html( $rating ) {
		return self::rating_html( $rating, 'input' );
	}

	/**
	 * Get the default rating count.
	 *
	 * @return int
	 */
	public static function rating_input_count() {
		return 5;
	}

	/**
	 * Get the rating input html.
	 *
	 * @param $rating
	 * @param string $type
	 *
	 * @return string
	 */
	public static function rating_html( $rating, $type = 'output' ) {

		$rating_icon  = esc_attr( geodir_get_option( 'rating_icon', 'fa-star' ) );
		$rating_color = esc_attr( geodir_get_option( 'rating_color' ) );
		if ( $rating_color == '#ff9900' ) {
			$rating_color = '';
		}
		$rating_color_off = esc_attr( geodir_get_option( 'rating_color_off' ) );
		if ( $rating_color_off == '#afafaf' ) {
			$rating_color_off = '';
		} else {
			$rating_color_off = "style='color:$rating_color_off;'";
		}
		$rating_texts      = self::rating_texts();
		$rating_wrap_title = '';
		if ( $type == 'output' ) {
			$rating_wrap_title = $rating ? sprintf( __( '%d star rating', 'geodirectory' ), $rating ) : __( "No rating yet!", "geodirectory" );
		}
		$rating_html        = '';
		$rating_input_count = self::rating_input_count();
		$i                  = 1;
		$rating_type        = esc_attr( geodir_get_option( 'rating_type' ) );
		if ( $rating_type == 'image' && $rating_image_id = geodir_get_option( 'rating_image' ) ) {
			$rating_image = wp_get_attachment_url( $rating_image_id );
			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<img alt="rating icon" src="' . $rating_image . '" ' . $rating_title . ' />';
				$i ++;
			}
			if ( $rating_color == '#ff9900' ) {
				$rating_color = '';
			} else {
				$rating_color = "background:$rating_color;";
			}
		} else {

			if($rating_color){
				$rating_color = " color:$rating_color; ";
			}

			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<i class="fa ' . $rating_icon . '" ' . $rating_title . '></i>';
				$i ++;
			}
		}


		$rating_percent   = $type == 'output' ? 'width:' . $rating / $rating_input_count * 100 . '%;' : '';
		$foreground_style = $rating_percent || $rating_color ? "style='$rating_percent $rating_color'" : '';
		$rating_wrap_title = $rating_wrap_title ? 'title="' . esc_attr( $rating_wrap_title ) . '"' : '';
		ob_start();
		?>
		<div class="gd-rating gd-rating-<?php echo esc_attr( $type ); ?> gd-rating-type-<?php echo $rating_type; ?>">
			<span class="gd-rating-wrap" <?php echo $rating_wrap_title; ?>>
				<span class="gd-rating-foreground" <?php echo $foreground_style; ?>>
				<?php echo $rating_html; ?>
				</span>
				<span class="gd-rating-background" <?php echo $rating_color_off; ?>>
				<?php echo $rating_html; ?>
				</span>
			</span>
			<?php if ( $type == 'input' ) { ?>
				<span class="gd-rating-text"
				      data-title="<?php _e( 'Select a rating', 'geodirectory' ); ?>"><?php _e( 'Select a rating', 'geodirectory' ); ?></span>
				<input type="hidden" id="geodir_overallrating" name="geodir_overallrating"
				       value="<?php echo esc_attr( $rating ); ?>"/>
			<?php } ?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get the rating output html.
	 *
	 * @param $rating
	 *
	 * @return string
	 */
	public static function rating_output( $rating ) {
		return self::rating_html( $rating );
	}

	/**
	 * The default rating texts.
	 *
	 * @return mixed|void
	 */
	public static function rating_texts_default() {
		$texts = array(
			1 => __( 'Terrible', 'geodirectory' ),
			2 => __( 'Poor', 'geodirectory' ),
			3 => __( 'Average', 'geodirectory' ),
			4 => __( 'Very Good', 'geodirectory' ),
			5 => __( 'Excellent', 'geodirectory' ),
		);

		return apply_filters( 'geodir_rating_texts_default', $texts );
	}

	/**
	 * The rating texts used on the site.
	 *
	 * @return mixed|void
	 */
	public static function rating_texts() {
		$defaults = self::rating_texts_default();

		$texts = array(
			1 => geodir_get_option( 'rating_text_1' ) ? __( geodir_get_option( 'rating_text_1' ), 'geodirectory' ) : $defaults[1],
			2 => geodir_get_option( 'rating_text_2' ) ? __( geodir_get_option( 'rating_text_2' ), 'geodirectory' ) : $defaults[2],
			3 => geodir_get_option( 'rating_text_3' ) ? __( geodir_get_option( 'rating_text_3' ), 'geodirectory' ) : $defaults[3],
			4 => geodir_get_option( 'rating_text_4' ) ? __( geodir_get_option( 'rating_text_4' ), 'geodirectory' ) : $defaults[4],
			5 => geodir_get_option( 'rating_text_5' ) ? __( geodir_get_option( 'rating_text_5' ), 'geodirectory' ) : $defaults[5],
		);
		
		return apply_filters( 'geodir_rating_texts', $texts );
	}

	/**
	 * Get average overall rating of a Post.
	 *
	 * Returns average overall rating of a Post. If no results, returns false.
	 *
	 * @param int $post_id The post ID.
	 * @param int $force_query Optional. Do you want force run the query? Default: 0.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global object $post The current post object.
	 * @return array|bool|int|mixed|null|string
	 */
	public static function get_post_rating( $post_id = 0, $force_query = 0 ) {
		global $wpdb;

		$gd_post = geodir_get_post_info( $post_id );

		if ( isset( $gd_post->ID ) && $gd_post->ID == $post_id && ! $force_query ) {
			if ( isset( $gd_post->rating_count ) && $gd_post->rating_count > 0 && isset( $gd_post->overall_rating ) && $gd_post->overall_rating > 0 ) {
				return $gd_post->overall_rating;
			} else {
				return 0;
			}
		}

		$results = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT  COALESCE(avg(r.rating),0) FROM " . GEODIR_REVIEW_TABLE . " AS r JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
				array( $post_id )
			)
		);

		if ( ! empty( $results ) ) {
			return $results;
		} else {
			return false;
		}
	}

	/**
	 * Get review count of a Post.
	 *
	 * Returns review count of a Post. If no results, returns false.
	 *
	 * @param int $post_id The post ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|null|string
	 */
	public static function get_post_review_count_total( $post_id = 0 ) {
		global $wpdb;

		$results = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(r.rating) FROM " . GEODIR_REVIEW_TABLE . " AS r JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
				array( $post_id )
			)
		);

		if ( ! empty( $results ) ) {
			return $results;
		} else {
			return false;
		}
	}

	/**
	 * Get overall rating of a comment.
	 *
	 * Returns overall rating of a comment. If no results, returns false.
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|null|string
	 */
	public static function get_comment_rating( $comment_id = 0 ) {
		global $wpdb;

		$reatings = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT rating FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id = %d",
				array( $comment_id )
			)
		);

		if ( $reatings ) {
			return $reatings;
		} else {
			return false;
		}
	}

}
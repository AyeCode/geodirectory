<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class GeoDir_Comments {

	/**
	 * Initiate the comments class.
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		// AUI comment inputs
		add_filter( 'comment_form_defaults', array( __CLASS__, 'aui_comment_form_defaults' ), 11, 1 );

		add_action( 'comment_form_logged_in_after', array( __CLASS__, 'rating_input' ) );
		add_action( 'comment_form_before_fields', array( __CLASS__, 'rating_input' ) );

		// add ratings to comment text
		add_filter( 'comment_text', array( __CLASS__, 'wrap_comment_text' ), 40, 2 );

		// replace comments template
		add_filter( 'comments_template', array( __CLASS__, 'comments_template' ) ); // @todo, maybe we want to use the themes own template?

		// remove replies from comments count so only to show reviews
		add_filter( 'get_comments_number', array( __CLASS__, 'review_count_exclude_replies' ), 10, 2 );

		// set if listing has comments open
		add_filter( 'comments_open', array( __CLASS__, 'comments_open' ), 20, 2 ); // @todo we maybe don't need this with the new preview system?

		// comment actions
		add_action( 'comment_post', array( __CLASS__, 'save_rating' ) );
		add_action( 'wp_set_comment_status', array( __CLASS__, 'status_change' ), 10, 2 );
		add_action( 'edit_comment', array( __CLASS__, 'edit_comment' ) );
		add_action( 'delete_comment', array( __CLASS__, 'delete_comment' ) );

		// change the comment hash to review hash
		add_filter( 'get_comments_link', array( __CLASS__, 'get_comments_link' ), 15, 2 );
	}

	/**
	 * Bootstrap the default comment form inputs with AUI.
	 *
	 * @param $defaults
	 *
	 * @return mixed
	 */
	public static function aui_comment_form_defaults( $defaults ) {
		global $aui_bs5;

		$design_style = geodir_design_style();

		if ( $design_style && geodir_is_page( 'single' ) ) {
			// Gets current commenter's name, email, and URL.
			$commenter = wp_get_current_commenter();

			// comment field
			$defaults['comment_field'] = aui()->textarea(
				array(
					'name'        => 'comment',
					'class'       => '',
					'id'          => 'comment',
					'placeholder' => esc_html__( 'Enter your review comments here (required)...', 'geodirectory' ),
					'required'    => true,
					'label'       => esc_html__( 'Review text', 'geodirectory' ),
					'rows'        => 8,
				)
			);

			// author name
			$defaults['fields']['author'] = aui()->input(
				array(
					'id'               => 'author',
					'name'             => 'author',
					'required'         => true,
					'label'            => esc_html__( 'Name', 'geodirectory' ),
					'type'             => 'text',
					'placeholder'      => esc_html__( 'Name (required)', 'geodirectory' ),
					'value'            => ! empty( $commenter['comment_author'] ) ? $commenter['comment_author'] : '',
					'extra_attributes' => array(
						'maxlength' => '245',
					),
				)
			);

			// author email
			$defaults['fields']['email'] = aui()->input(
				array(
					'id'               => 'email',
					'name'             => 'email',
					'required'         => true,
					'label'            => esc_html__( 'Email', 'geodirectory' ),
					'type'             => 'email',
					'placeholder'      => esc_html__( 'Email (required)', 'geodirectory' ),
					'value'            => ! empty( $commenter['comment_author_email'] ) ? $commenter['comment_author_email'] : '',
					'extra_attributes' => array(
						'maxlength' => '100',
					),
				)
			);

			// website url
			$defaults['fields']['url'] = aui()->input(
				array(
					'id'               => 'url',
					'name'             => 'url',
					'required'         => true,
					'label'            => esc_html__( 'Website', 'geodirectory' ),
					'type'             => 'url',
					'placeholder'      => esc_html__( 'Website', 'geodirectory' ),
					'value'            => ! empty( $commenter['comment_author_url'] ) ? $commenter['comment_author_url'] : '',
					'extra_attributes' => array(
						'maxlength' => '200',
					),
				)
			);

			if ( has_action( 'set_comment_cookies', 'wp_set_comment_cookies' ) && get_option( 'show_comments_cookies_opt_in' ) ) {
				// website url
				$defaults['fields']['cookies'] = aui()->input(
					array(
						'id'       => 'wp-comment-cookies-consent',
						'name'     => 'wp-comment-cookies-consent',
						'required' => true,
						'value'    => 'yes',
						'label'    => esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'geodirectory' ),
						'type'     => 'checkbox',
						'checked'  => ! empty( $commenter['comment_author_email'] ) ? true : false,
					)
				);
			}

			// logged in as
			$defaults['logged_in_as'] = str_replace( 'logged-in-as', 'logged-in-as mb-3', $defaults['logged_in_as'] );

			// logged out notes
			$defaults['comment_notes_before'] = aui()->alert(
				array(
					'type'    => 'info',
					'content' => __( 'Your email address will not be published.', 'geodirectory' ),
				)
			);

			$reply_text = __( 'Leave a Review', 'geodirectory' );

			$defaults['class_submit'] .= ' btn btn-primary form-control text-white';
			$defaults['submit_field']  = '<div class="form-submit ' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . '">%1$s %2$s</div>';
			$defaults['label_submit']  = esc_html__( 'Post Review', 'geodirectory' );
			$defaults['title_reply']   = '<span class="gd-comment-review-title h4" data-review-text="' . esc_attr( $reply_text ) . '" data-reply-text="' . esc_attr( $defaults['title_reply'] ) . '">' . $reply_text . '</span>';
		}

		return $defaults;
	}

	/**
	 * Change the comments url hash to review types.
	 *
	 * @param $comments_link
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public static function get_comments_link( $comments_link, $post_id ) {
		$post_type = get_post_type( $post_id );

		$all_postypes = geodir_get_posttypes();
		if ( in_array( $post_type, $all_postypes ) ) {
			$comments_link = str_replace( '#comments', '#reviews', $comments_link );
			$comments_link = str_replace( '#respond', '#reviews', $comments_link );
		}

		return $comments_link;
	}

	/**
	 * Update post overall rating and rating count.
	 *
	 * @since 2.0.0
	 *
	 * @global object $wpdb WordPress Database object.
	 *
	 * @param int $post_id The post ID.
	 * @param string $post_type The post type.
	 * @param bool $delete Depreciated since ver 1.3.6.
	 */
	public static function update_post_rating( $post_id = 0, $post_type = '', $delete = false ) {
		global $wpdb, $comment;
		if ( ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return;
		}
		$detail_table         = geodir_db_cpt_table( $post_type );
		$post_newrating       = geodir_get_post_rating( $post_id, 1 );
		$post_newrating_count = (int) geodir_get_review_count_total( $post_id, 1 );

		$wpdb->update(
			$detail_table,
			array(
				'overall_rating' => $post_newrating,
				'rating_count'   => $post_newrating_count,
			),
			array( 'post_id' => $post_id ),
			array( '%f', '%d' ),
			array( '%d' )
		);

		// delete post rating cache
		wp_cache_delete( 'gd_post_review_count_total_' . $post_id );

		// Clear the post cache
		wp_cache_delete( 'gd_post_' . $post_id, 'gd_post' );

		// Clear transients.
		delete_transient( 'gd_avg_num_votes_' . $detail_table );
		delete_transient( 'gd_avg_rating_' . $detail_table );

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
	 * @since 2.0.0
	 *
	 * @param int $comment_id Optional. The comment ID. Default 0.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|mixed
	 */
	public static function get_review( $comment_id = 0 ) {
		global $wpdb;

		$ratings = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . GEODIR_REVIEW_TABLE . ' WHERE comment_id = %d',
				array( $comment_id )
			)
		);

		if ( ! empty( $ratings ) ) {
			return $ratings;
		} else {
			return false;
		}
	}

	/**
	 * Delete review details when deleting comment.
	 *
	 * @since 2.0.0
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
				'DELETE FROM ' . GEODIR_REVIEW_TABLE . ' WHERE comment_id=%d',
				array( $comment_id )
			)
		);

		// clear cache
		wp_cache_delete( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );
	}

	/**
	 * Update comment rating.
	 *
	 * @since 2.0.0
	 *
	 * @param int $comment_id Optional. The comment ID. Default 0.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @global string $plugin_prefix Geodirectory plugin table prefix.
	 *
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
		$rating     = absint( $_REQUEST['geodir_overallrating'] );

		if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
			if ( ! empty( $old_rating ) ) {
				$sqlqry = $wpdb->prepare(
					'UPDATE ' . GEODIR_REVIEW_TABLE . ' SET
					rating = %f
					WHERE comment_id = %d ',
					array(
						$rating,
						$comment_id,
					)
				);

				// clear cache
				wp_cache_delete( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );

				$wpdb->query( $sqlqry );

				// update rating
				self::update_post_rating( $post_id, $post_type );
			} elseif ( ! empty( $_REQUEST['geodir_overallrating'] ) ) {
				// create new rating if not exists
				self::save_rating( $comment_id );
			}
		}
	}

	/**
	 * Update comment status when changing the rating.
	 *
	 * @since 2.0.0
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

		$post_id         = isset( $comment_info->comment_post_ID ) ? $comment_info->comment_post_ID : '';
		$comment_info_ID = isset( $comment_info->comment_ID ) ? $comment_info->comment_ID : '';
		$old_rating      = geodir_get_comment_rating( $comment_info_ID );
		$post_type       = get_post_type( $post_id );

		if ( $comment_id ) {
			$rating = $old_rating;

			if ( isset( $old_rating ) ) {
				$sqlqry = $wpdb->prepare(
					'UPDATE ' . GEODIR_REVIEW_TABLE . ' SET
					rating = %f
					WHERE comment_id = %d ',
					array(
						$rating,
						$comment_id,
					)
				);

				// clear cache
				wp_cache_delete( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );

				$wpdb->query( $sqlqry );

				// update rating
				self::update_post_rating( $post_id, $post_type );
			}
		}
	}

	/**
	 * Save rating details for a comment.
	 *
	 * @since 2.0.0
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

		$rating = absint( $_REQUEST['geodir_overallrating'] );

		if ( isset( $comment_info->comment_parent ) && (int) $comment_info->comment_parent == 0 ) {
			$sqlqry = $wpdb->prepare(
				'INSERT INTO ' . GEODIR_REVIEW_TABLE . ' SET
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
				',
				array(
					$post_id,
					$gd_post->post_type,
					$user_ID,
					$comment_info->comment_ID,
					$rating,
					( isset( $gd_post->city ) ? $gd_post->city : '' ),
					( isset( $gd_post->region ) ? $gd_post->region : '' ),
					( isset( $gd_post->country ) ? $gd_post->country : '' ),
					( isset( $gd_post->latitude ) ? $gd_post->latitude : '' ),
					( isset( $gd_post->longitude ) ? $gd_post->longitude : '' ),
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

			self::update_post_rating( $post_id, $gd_post->post_type );
		}
	}

	/**
	 * Check whether the current post is open for reviews.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $open Whether the current post is open for reviews.
	 * @param int $post_id The post ID.
	 *
	 * @return bool True if allowed otherwise False.
	 */
	public static function comments_open( $open, $post_id ) {
		if ( empty( $post_id ) ) {
			return $open;
		}

		if ( $open && geodir_is_page( 'detail' ) ) {
			if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending', 'auto-draft', 'trash' ) ) ) {
				$open = false;
			}
		}

		$post_type = get_post_type( $post_id );

		if ( ! geodir_is_gd_post_type( $post_type ) ) {
			return $open;
		}

		// Check & disable comments for post type.
		if ( $open && ! GeoDir_Post_types::supports( $post_type, 'comments' ) ) {
			$open = false;
		}

		// Check single review.
		if ( $open && ! self::can_submit_post_review( $post_id ) ) {
			$open = false;
		}

		return $open;
	}

	/**
	 * Fix comment count by not listing replies as reviews.
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
			$post_type  = get_post_type( $post_id );
			if ( in_array( $post_type, $post_types ) && ! geodir_cpt_has_rating_disabled( $post_type ) ) {
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
	 * @since 2.0.0
	 *
	 * @global object $post The current post object.
	 * @param string $comment_template Old comment template.
	 * @return string New comment template.
	 */
	public static function comments_template( $comment_template ) {
		global $post,$gd_is_comment_template_set;

		$post_types = geodir_get_posttypes();

		if ( ! ( is_singular() && ( have_comments() || ( isset( $post->comment_status ) && 'open' == $post->comment_status ) ) ) ) {

			// if we already loaded the template don't load it again
			if ( $gd_is_comment_template_set ) {
				return geodir_plugin_path() . '/index.php'; // a blank template to remove default if called more than once.
			}

			return $comment_template;
		}
		if ( in_array( get_post_type( $post->ID ), $post_types ) ) { // assuming there is a post type called business

			// if we already loaded the template don't load it again
			if ( $gd_is_comment_template_set ) {
				return geodir_plugin_path() . '/index.php'; // a blank template to remove default if called more than once.
			}

			if ( geodir_cpt_has_rating_disabled( get_post_type( $post->ID ) ) ) {
				$gd_is_comment_template_set = true;
				return $comment_template;
			}

			$design_style = geodir_design_style();

			$template_path = $design_style ? 'geodirectory/' . $design_style . '/reviews.php' : 'geodirectory/reviews.php';
			$template      = locate_template( array( $template_path ) ); // Use theme template if available
			if ( ! $template ) {
				$template = untrailingslashit( geodir_get_templates_dir() ) . '/' . ( $design_style ? $design_style . '/reviews.php' : 'reviews.php' );
			}
			$gd_is_comment_template_set = true;

			return $template;
		}

		return $comment_template;
	}

	/**
	 * Add rating information in comment text.
	 *
	 * @since 2.0.0
	 *
	 * @param string $content The comment content.
	 * @param object|string $comment The comment object.
	 *
	 * @return string The comment content.
	 */
	public static function wrap_comment_text( $content, $comment = '' ) {
		if ( ! empty( $comment->comment_post_ID ) && geodir_cpt_has_rating_disabled( get_post_type( (int) $comment->comment_post_ID ) ) ) {
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
				return '<div class="description">' . $content . '</div>';
			} else {
				return $content;
			}
		}
	}

	/**
	 * Comment HTML markup.
	 *
	 * @since 2.0.0
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
		global $gd_review_template;
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback':
			case 'trackback':
				// Display trackbacks differently than normal comments.
				?>
				<li <?php comment_class( 'geodir-comment' ); ?> id="comment-<?php comment_ID(); ?>">
				<p><?php _e( 'Pingback:', 'geodirectory' ); ?><?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'geodirectory' ), '<span class="edit-link">', '</span>' ); ?></p>
				<?php
				break;
			default:
				// Proceed with normal comments.
				global $post;

				$design_style = geodir_design_style();

				if ( $design_style && ! empty( $gd_review_template ) ) {
					$gd_review_template = esc_attr(sanitize_file_name($gd_review_template));
					$template = $design_style . '/reviews/item-'.$gd_review_template.'.php';
				}else{
					$template = $design_style ? $design_style . '/reviews/item.php' : 'legacy/reviews/item.php';
				}


				$vars = array(
					'comment' => $comment,
					'args'    => $args,
					'depth'   => $depth,
					'rating'  => self::get_comment_rating( $comment->comment_ID ),
				);
				echo geodir_get_template_html( $template, $vars );

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
		global $aui_bs5, $post;

		if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
			$post_type = get_post_type( $comment->comment_post_ID );
		} else {
			$post_type = $post->post_type;
		}
		$post_types = geodir_get_posttypes();

		if ( ! empty( $post_type )
			 && in_array( $post_type, $post_types )
			 && ! ( ! empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post_type ) )
		) {
			$rating = 0;
			if ( isset( $comment->comment_post_ID ) && $comment->comment_post_ID ) {
				$rating = self::get_comment_rating( $comment->comment_ID );
			}

			$design_style = geodir_design_style();

			if ( $design_style ) {
				echo '<div class="' . ( $aui_bs5 ? 'mb-3' : 'form-group' ) . ' form-control h-auto rounded px-3 pt-3 pb-3 gd-rating-input-group ">';
			}

			echo self::rating_input_html( $rating );

			if ( $design_style ) {
				echo '</div>';
			}
		}
	}

	/**
	 * The rating input html.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rating Rating value.
	 * @return string
	 */
	public static function rating_input_html( $rating ) {
		return self::rating_html( $rating, 'input' );
	}

	/**
	 * Get the default rating count.
	 *
	 * @since 2.0.0
	 *
	 * @return int
	 */
	public static function rating_input_count() {
		return 5;
	}

	/**
	 * Get the rating input html.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rating Rating.
	 * @param string $type Optional. Type. Default output.
	 *
	 * @return string
	 */
	public static function rating_html( $rating, $type = 'output', $overrides = array() ) {
		global $aui_bs5;

		$defaults = array(
			'rating_icon'        => esc_attr( geodir_get_option( 'rating_icon', 'fas fa-star' ) ),
			'rating_icon_fw'     => esc_attr( geodir_get_option( 'rating_icon_fw' ) ),
			'rating_color'       => esc_attr( geodir_get_option( 'rating_color' ) ),
			'rating_color_off'   => esc_attr( geodir_get_option( 'rating_color_off' ) ),
			'rating_label'       => '',
			'rating_texts'       => self::rating_texts(),
			'rating_image'       => geodir_get_option( 'rating_image' ),
			'rating_type'        => esc_attr( geodir_get_option( 'rating_type' ) ),
			'rating_input_count' => self::rating_input_count(),
			'id'                 => 'geodir_overallrating',
			'type'               => $type,
		);

		$args = wp_parse_args( $overrides, $defaults );

		// rating label
		$rating_label = $args['rating_label'];

		if ( ! $rating_label && $type == 'input' ) {
			/**
			 * Filter the label for main rating.
			 *
			 * This is not shown everywhere but is used by reviews manager.
			 */
			$rating_label = apply_filters( 'geodir_overall_rating_label', '' );
		}

		$type        = $args['type'];
		$rating_icon = $args['rating_icon'];

		if ( $args['rating_icon_fw'] ) {
			$rating_icon .= ' fa-fw';
		}

		$rating_color = $args['rating_color'];
		if ( $rating_color == '#ff9900' ) {
			$rating_color = '#ff9900';
		}
		$rating_color_off = $args['rating_color_off'];
		if ( $rating_color_off == '#afafaf' ) {
			$rating_color_off = "style='color:#afafaf;'";
		} else {
			$rating_color_off = "style='color:$rating_color_off;'";
		}
		$rating_texts      = $args['rating_texts'];
		$rating_wrap_title = '';
		if ( $type == 'output' ) {
			if ( $rating > 0 ) {
				/*$int_rating = (int) $rating > 1 ? (int) $rating : 1;
				if ( ! empty( $args ) && ! empty( $args['rating_texts'] ) && ! empty( $args['rating_texts'][ $int_rating ] ) ) {
					$rating_wrap_title = __( $args['rating_texts'][ $int_rating ], 'geodirectory' );
				} else {*/
					$rating_wrap_title = wp_sprintf( __( '%d star rating', 'geodirectory' ), $rating );
				//}
			} else {
				 $rating_wrap_title = __( 'No rating yet!', 'geodirectory' );
			}
			$rating_wrap_title = apply_filters( 'geodir_output_rating_title', $rating_wrap_title, $rating, $args );
		}
		$rating_html        = '';
		$rating_input_count = $args['rating_input_count'];
		$i                  = 1;
		$rating_type        = $args['rating_type'];
		if ( $rating_type == 'image' && $rating_image_id = $args['rating_image'] ) {
			$rating_image = wp_get_attachment_url( $rating_image_id );
			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<img alt="rating icon" src="' . $rating_image . '" ' . $rating_title . ' />';
				$i ++;
			}
			if ( $rating_color == '#ff9900' ) {
				$rating_color = 'background:#ff9900';
			} else {
				$rating_color = "background:$rating_color;";
			}
		} else {

			if ( $rating_color ) {
				$rating_color = " color:$rating_color; ";
			}

			while ( $i <= $rating_input_count ) {
				$rating_title = $type == 'input' ? "title='$rating_texts[$i]'" : '';
				$rating_html .= '<i class="' . $rating_icon . '" aria-hidden="true" ' . $rating_title . '></i>';
				$i ++;
			}
		}

		$rating_percent = $type == 'output' ? 'width:' . $rating / $rating_input_count * 100 . '%;' : '';
		if ( $type == 'input' && ! $rating ) {
			$rating_percent = 'width:50%;';
		}
		$foreground_style  = $rating_percent || $rating_color ? "style='$rating_percent $rating_color'" : '';
		$rating_wrap_title = $rating_wrap_title ? 'title="' . esc_attr( $rating_wrap_title ) . '"' : '';
		ob_start();

		$design_style = geodir_design_style();

		if ( $design_style ) {
			echo '<div class="gd-rating-outer-wrap gd-rating-' . esc_attr( $type ) . '-wrap d-flex d-flex justify-content-between flex-nowrap w-100">';

			$wrap_class = $type == 'input' ? 'c-pointer' : '';
			?>
			<div class="gd-rating gd-rating-<?php echo esc_attr( $type ); ?> gd-rating-type-<?php echo $rating_type; ?>">
			<span class="gd-rating-wrap d-inline-flex text-nowrap position-relative <?php echo $wrap_class; ?>" <?php echo $rating_wrap_title; ?>>
				<span class="gd-rating-foreground position-absolute text-nowrap overflow-hidden" <?php echo $foreground_style; ?>><?php echo $rating_html; ?></span>
				<span class="gd-rating-background" <?php echo $rating_color_off; ?>><?php echo $rating_html; ?></span>
			</span>
				<?php if ( $type == 'input' ) { ?>
					<span class="gd-rating-text badge <?php echo ( $aui_bs5 ? 'text-bg-light' : 'badge-light' ); ?> border" data-title="<?php _e( 'Select a rating', 'geodirectory' ); ?>"><?php _e( 'Select a rating', 'geodirectory' ); ?></span>
					<input type="hidden" id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $rating ); ?>"/>
				<?php } ?>
			</div>
			<?php if ( $rating_label ) { ?>
				<span class="gd-rating-label font-weight-bold fw-bold p-0 m-0 text-nowrap"><?php echo esc_attr( $rating_label ); ?></span>
				<?php
			}
			echo '</div>';
		} else {
			echo '<div class="gd-rating-outer-wrap gd-rating-' . esc_attr( $type ) . '-wrap">';
			if ( $rating_label ) {
				?>
				<span class="gd-rating-label"><?php echo esc_attr( $rating_label ); ?>: </span>
				<?php
			}
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
					<span class="gd-rating-text" data-title="<?php _e( 'Select a rating', 'geodirectory' ); ?>"><?php _e( 'Select a rating', 'geodirectory' ); ?></span>
					<input type="hidden" id="<?php echo $args['id']; ?>" name="<?php echo $args['id']; ?>" value="<?php echo esc_attr( $rating ); ?>"/>
				<?php } ?>
			</div>
			<?php
			echo '</div>';
		}

		return ob_get_clean();
	}

	/**
	 * Get the rating output html.
	 *
	 * @since 2.0.0
	 *
	 * @param string $rating Rating.
	 *
	 * @return string
	 */
	public static function rating_output( $rating, $overrides ) {
		return self::rating_html( $rating, 'output', $overrides );
	}

	/**
	 * The default rating texts.
	 *
	 * @since 2.0.0
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
	 * @since 2.0.0
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
	 * @since 2.0.0
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
				'SELECT  COALESCE(avg(r.rating),0) FROM ' . GEODIR_REVIEW_TABLE . " AS r JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
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
	 * @since 2.0.0
	 *
	 * @param int $post_id The post ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|null|string
	 */
	public static function get_post_review_count_total( $post_id = 0, $force_query = 0 ) {
		global $wpdb,$gd_post;

		if ( isset( $gd_post->ID ) && $gd_post->ID == $post_id && isset( $gd_post->rating_count ) && ! $force_query ) {
			return $gd_post->rating_count;
		}

		// check for cache
		$cache = wp_cache_get( 'gd_post_review_count_total_' . $post_id, 'gd_post_review_count_total' );
		if ( $cache !== false && ! $force_query ) {
			return $cache;
		}

		$results = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(r.rating) FROM ' . GEODIR_REVIEW_TABLE . " AS r JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d AND cmt.comment_approved = '1' AND r.rating > 0",
				array( $post_id )
			)
		);

		if ( ! empty( $results ) ) {
			// set cache
			wp_cache_set( 'gd_post_review_count_total_' . $post_id, $results, 'gd_post_review_count_total' );
			return $results;
		} else {
			return false;
		}
	}

	/**
	 * Get post reviews ratings wise counts.
	 *
	 * @param $post_id
	 * @param $force_query
	 *
	 * @return array|false|mixed|object|stdClass|null
	 */
	public static function get_post_review_rating_counts( $post_id = 0, $force_query = 0 ) {
		global $wpdb;

		// Check for cache.
		$cache = wp_cache_get( 'gd_post_review_rating_counts_' . $post_id, 'gd_post_review_rating_counts' );

		if ( $cache !== false && ! $force_query ) {
			/**
			 * Filter post review rating counts cached results.
			 *
			 * @since 2.3.76
			 *
			 * @param array $cache Cached review rating counts array.
			 * @param int   $post_id Current post ID.
			 * @param bool  $force_query Force query to skip cached results.
			 */
			$cache = apply_filters( 'geodir_post_review_rating_counts', $cache, $post_id, $force_query );

			return $cache;
		}

		$sql = $wpdb->prepare( "SELECT `r`.`rating` FROM `" . GEODIR_REVIEW_TABLE . "` AS `r` JOIN `{$wpdb->comments}` AS `cmt` ON `cmt`.`comment_ID` = `r`.`comment_id` WHERE `r`.`post_id` = %d AND `cmt`.`comment_approved` = '1' AND `r`.`rating` > 0", array( $post_id ) );

		/**
		 * Filter post review rating counts SQL query.
		 *
		 * @since 2.3.76
		 *
		 * @param string $sql SQL Query.
		 * @param int    $post_id Current post ID.
		 * @param bool   $force_query Force query to skip cached results.
		 */
		$sql = apply_filters( 'geodir_post_review_rating_counts_sql', $sql, $post_id, $force_query );

		$results = $wpdb->get_results( $sql );

		$counts = array();

		if ( ! empty( $results ) ) {
			foreach ( $results as $result ) {
				isset( $counts[ $result->rating ] ) ? $counts[ $result->rating ]++ : $counts[ $result->rating ] = 1;
			}
		}

		/**
		 * Filter post review rating counts results.
		 *
		 * @since 2.3.76
		 *
		 * @param array $counts Review rating counts.
		 * @param int   $post_id Current post ID.
		 * @param bool  $force_query Force query to skip cached results.
		 */
		$counts = apply_filters( 'geodir_post_review_rating_counts', $counts, $post_id, $force_query );

		if ( ! empty( $counts ) ) {
			// Set cache.
			wp_cache_set( 'gd_post_review_rating_counts_' . $post_id, $counts, 'gd_post_review_rating_counts' );

			return $counts;
		} else {
			return false;
		}
	}

	/**
	 * Get overall rating of a comment.
	 *
	 * Returns overall rating of a comment. If no results, returns false.
	 *
	 * @since 2.0.0
	 *
	 * @param int $comment_id The comment ID.
	 *
	 * @global object $wpdb WordPress Database object.
	 * @return bool|null|string
	 */
	public static function get_comment_rating( $comment_id = 0 ) {
		global $wpdb;

		// check for cache
		$cache = wp_cache_get( 'gd_comment_rating_' . $comment_id, 'gd_comment_rating' );
		if ( $cache ) {
			return $cache;
		}

		$ratings = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT rating FROM ' . GEODIR_REVIEW_TABLE . ' WHERE comment_id = %d',
				array( $comment_id )
			)
		);

		if ( $ratings ) {
			// set cache
			wp_cache_set( 'gd_comment_rating_' . $comment_id, $ratings, 'gd_comment_rating' );

			return $ratings;
		} else {
			return false;
		}
	}

	/**
	 * Check whether user allowed to submit review or not.
	 *
	 * @since 2.0.0.91
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id The user ID. Default 0.
	 * @param string|null $author_email The author email. Default null.
	 * @return bool True if allowed or false.
	 */
	public static function can_submit_post_review( $post_id, $user_id = 0, $author_email = '' ) {
		$can_review = true;

		if ( ! current_user_can( 'manage_options' ) && GeoDir_Post_types::supports( get_post_type( $post_id ), 'single_review' ) ) {
			$count_reviews = self::count_user_post_reviews( $post_id, $user_id, $author_email );

			if ( $count_reviews > 0 ) {
				$can_review = false;
			}
		}

		return apply_filters( 'geodir_can_submit_post_review', $can_review, $post_id, $user_id, $author_email );
	}

	/**
	 * Count reviews per post submitted by the user.
	 *
	 * @since 2.0.0.91
	 *
	 * @global $wpdb WordPress database object.
	 *
	 * @param int $post_id The post ID.
	 * @param int $user_id The user ID. Default 0.
	 * @param string|null $author_email The author email. Default null.
	 * @return int Reviews count.
	 */
	public static function count_user_post_reviews( $post_id, $user_id = 0, $author_email = '' ) {
		global $wpdb;

		$count = 0;

		if ( empty( $post_id ) ) {
			return $count;
		}

		if ( empty( $user_id ) ) {
			if ( empty( $author_email ) ) {
				$user_id = (int) get_current_user_id();

				if ( empty( $user_id ) ) {
					$commenter = wp_get_current_commenter();
					if ( ! empty( $commenter ) && is_array( $commenter ) && ! empty( $commenter['comment_author_email'] ) ) {
						$author_email = $commenter['comment_author_email'];
					} else {
						$author_email = ( ! empty( $_POST['email'] ) ) ? sanitize_email( $_POST['email'] ) : '';
					}
				}
			}
		}

		if ( empty( $user_id ) && empty( $author_email ) ) {
			return $count = 0;
		}

		$where = "AND cmt.comment_approved = '1'";

		if ( ! geodir_cpt_has_rating_disabled( get_post_type( $post_id ) ) ) {
			$where .= ' AND r.rating > 0';
		}

		if ( ! empty( $user_id ) ) {
			$where .= ' AND cmt.user_id = ' . (int) $user_id;
		}

		if ( ! empty( $author_email ) ) {
			$where .= " AND cmt.comment_author_email = '" . $author_email . "'";
		}

		$sql = $wpdb->prepare( 'SELECT COUNT(*) FROM ' . GEODIR_REVIEW_TABLE . " AS r JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id WHERE r.post_id = %d {$where}", array( $post_id ) );

		$sql = apply_filters( 'geodir_count_user_post_reviews_sql', $sql, $post_id, $user_id, $author_email );

		$count = (int) $wpdb->get_var( $sql );

		return apply_filters( 'geodir_count_user_post_reviews', $count, $post_id, $user_id, $author_email );
	}

	public static function get_overall_box_html( $post_id ) {
		$rating_titles       = self::rating_texts();
		$post_rating         = geodir_get_post_rating( $post_id );
		$post_rating_rounded = round( $post_rating );
		$review_total        = geodir_get_review_count_total( $post_id );
		$stars               = geodir_get_rating_stars( $post_rating, $post_id );
		$stars               = str_replace( 'd-flex', '', $stars );
		$rating_counts       = self::get_post_review_rating_counts( $post_id, 1 );
		$rating_count        = self::rating_input_count();

		ob_start();
		?>
		<div class="row gy-4 mb-5">
			<div class="col-sm-4">
				<div class="card border-0 rounded bg-transparent-primary bg-primary bg-opacity-10" >
					<div class="card-body text-center text-dark">
						<div class="mb-1">
							<?php echo ( isset( $rating_titles[ $post_rating_rounded ] ) ? $rating_titles[ $post_rating_rounded ] : '' ); ?>
						</div>
						<div class="mb-1 display-5">
							<?php echo round( $post_rating, 1 ); ?>
						</div>
						<div class="mb-1">
							<?php echo $stars; ?>
						</div><span class="fs-xs">
							<?php printf( _n( '%d review', '%d reviews', $review_total, 'geodirectory' ), number_format_i18n( $review_total ) ); ?>
						</span>
					</div>
				</div>
<!--				<div class="mt-sm-4 mt-3 text-center"><a class="btn btn-primary rounded-pill w-sm-auto w-100" href="#modal-review" data-bs-toggle="modal"><i class="fi-edit me-1"></i>Add review</a></div>-->
			</div>
			<div class="col-sm-8">
					<?php
					echo self::get_post_rating_counts_html( $post_id );
					?>
			</div>
		</div>
		<?php
		return apply_filters( 'geodir_comments_overall_box_html', ob_get_clean(), $post_id );
	}

	/**
	 * @return false|string
	 */
	public static function get_post_rating_counts_html( $post_id ) {

		$rating_titles = self::rating_texts();
		$post_rating   = geodir_get_post_rating( $post_id );
		$review_total  = geodir_get_review_count_total( $post_id );
		$stars         = geodir_get_rating_stars( $post_rating, $post_id );
		$rating_counts = self::get_post_review_rating_counts( $post_id, 1 );
		$rating_count  = self::rating_input_count();

		$row_class = $rating_count > 5 ? 'row-cols-2' : 'row-cols-1';
		ob_start();
		?>

		<div class="row <?php echo esc_attr($row_class ); ?> gy-3">
			<?php
			while ( $rating_count > 0 ) {

//					$title   = $rating_titles[ $rating_count ];
				$ratings = isset( $rating_counts[ $rating_count ] ) ? absint( $rating_counts[ $rating_count ] ) : 0;
				$percent = $ratings ? round( ( $ratings / $review_total ) * 100 ) : 0;

				?>
				<div class="col">
					<div class="d-flex align-items-center">
						<div class="pe-2 text-nowrap text-center fs-sm" style="min-width: 50px" ><?php echo absint($rating_count); ?> <i class="fas fa-star text-gray" aria-hidden="true"></i></div>
						<div class="progress w-100" style="height: 14px;">
							<div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo absint( $percent ); ?>%" aria-valuenow="<?php echo absint( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
						</div>

					</div>
				</div>
				<?php
				$rating_count--;
			}
			?>
		</div>
		<?php
		return apply_filters( 'geodir_comments_post_rating_counts_html', ob_get_clean(), $post_id );
	}

	/**
	 * @param $rating
	 * @param $overrides
	 *
	 * @return false|string
	 */
	public static function get_bar_rating_html($rating,$overrides){
		$title = !empty($overrides['rating_label']) ? esc_attr($overrides['rating_label']) : '';
		$rating_input_count = !empty($overrides['rating_input_count']) ? esc_attr($overrides['rating_input_count']) :  self::rating_input_count();
		$percent = $rating ? round( ( $rating / $rating_input_count ) * 100 ) : 0;

		$title = !empty($overrides['rating_label']) ? esc_attr($overrides['rating_label']) : '';
		ob_start();
//		print_r($overrides);
		$rating_color = !empty($overrides['rating_color']) ? 'background: ' . esc_attr( $overrides['rating_color'] ) : '';
		$rating_color_class = $rating_color ? '' : 'bg-warning';
		?>
		<div class="col">
			<div class="text-dark"><?php echo esc_attr( $title ); ?></div>
			<div class="d-flex align-items-center">
				<div class="progress w-100" style="height: 4px;">
					<div class="progress-bar <?php echo esc_attr( $rating_color_class ); ?>" role="progressbar" style="width: <?php echo esc_attr( $percent ) ?>%; <?php echo esc_attr( $rating_color ); ?>" aria-valuenow="<?php echo esc_attr( $percent ); ?>" aria-valuemin="0" aria-valuemax="100"></div>
				</div>
				<div class="ms-2 ps-1 fs-sm"><?php echo esc_attr( number_format_i18n( $rating,1) ); ?></div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Get the listing owner label for the review.
	 *
	 * @since 2.3.7
	 *
	 * @param string $post_type Post type. Default empty.
	 * @return string Review post owner label.
	 */
	public static function get_listing_owner_label( $post_type = '' ) {
		$label = geodir_listing_owner_label( $post_type );

		/**
		 * Filter the listing owner label for the review.
		 *
		 * @since 2.3.7
		 *
		 * @param string $label Listing owner label.
		 * @param string $post_type The post type.
		 */
		return apply_filters( 'geodir_review_listing_owner_label', $label, $post_type );
	}
}

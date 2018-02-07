<?php
/**
 * Comment related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */



/**
 * Get review details using comment ID.
 *
 * Returns review details using comment ID. If no reviews returns false.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $comment_id The comment ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|mixed
 */
function geodir_get_review( $comment_id = 0 ) {
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
 * Get review total of a Post.
 *
 * Returns review total of a post. If no results returns false.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 */
function geodir_get_review_total( $post_id = 0 ) {
	global $wpdb;

	$results = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT SUM(overall_rating) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_id = %d AND status=1 AND overall_rating>0",
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
 * Get review count by user ID.
 *
 * Returns review count of a user. If no results returns false.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $user_id
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 */
function geodir_get_review_count_by_user_id( $user_id = 0 ) {
	global $wpdb;
	$results = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(overall_rating) FROM " . GEODIR_REVIEW_TABLE . " WHERE user_id = %d AND status=1 AND overall_rating>0",
			array( $user_id )
		)
	);

	if ( ! empty( $results ) ) {
		return $results;
	} else {
		return false;
	}
}

/**
 * Get average overall rating of a Post.
 *
 * Returns average overall rating of a Post. If no results, returns false.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID.
 * @param int $force_query Optional. Do you want force run the query? Default: 0.
 *
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @return array|bool|int|mixed|null|string
 */
function geodir_get_post_rating( $post_id = 0, $force_query = 0 ) {
	global $wpdb, $post;

	if ( isset( $post->ID ) && $post->ID == $post_id && ! $force_query ) {
		if ( isset( $post->rating_count ) && $post->rating_count > 0 && isset( $post->overall_rating ) && $post->overall_rating > 0 ) {
			return $post->overall_rating;
		} else {
			return 0;
		}
	}

	$results = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COALESCE(avg(overall_rating),0) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_id = %d AND status=1 AND overall_rating>0",
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
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 */
function geodir_get_review_count_total( $post_id = 0 ) {
	global $wpdb;

	$results = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(overall_rating) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_id = %d AND status=1 AND overall_rating>0",
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
 * Get comments count of a Post.
 *
 * Returns comments count of a Post. If no results, returns false.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 * @todo It might be a duplicate function of geodir_get_review_count_total().
 */
function geodir_get_comments_number( $post_id = 0 ) {
	global $wpdb;

	$results = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(overall_rating) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_id = %d AND status=1 AND overall_rating>0",
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
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $comment_id The comment ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 */
function geodir_get_commentoverall( $comment_id = 0 ) {
	global $wpdb;

	$reatings = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT overall_rating FROM " . GEODIR_REVIEW_TABLE . " WHERE comment_id = %d",
			array( $comment_id )
		)
	);

	if ( $reatings ) {
		return $reatings;
	} else {
		return false;
	}
}

/**
 * Returns average overall rating of a Post. Depreciated since ver 1.3.6.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param int $post_id The post ID.
 *
 * @internal Depreciated since ver 1.3.6.
 * @return array|bool|int|mixed|null|string
 */
function geodir_get_commentoverall_number( $post_id = 0 ) {
	return geodir_get_post_rating( $post_id );
}


/**
 * Sets the comment template.
 *
 * Sets the comment template using filter {@see 'comments_template'}.
 *
 * @since 1.0.0
 * @since 1.5.1 Reviews template can be overridden from theme.
 * @package GeoDirectory
 * @global object $post The current post object.
 *
 * @param string $comment_template Old comment template.
 *
 * @return string New comment template.
 */
function geodir_comment_template( $comment_template ) {
	global $post,$gd_is_comment_template_set;

	$post_types = geodir_get_posttypes();

	if ( ! ( is_singular() && ( have_comments() || ( isset( $post->comment_status ) && 'open' == $post->comment_status ) ) ) ) {
		return;
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

add_filter( "comments_template", "geodir_comment_template" );


if ( ! function_exists( 'geodir_comment' ) ) {
	/**
	 * Comment HTML markup.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
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
	function geodir_comment( $comment, $args, $depth ) {
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
}


add_filter( 'get_comments_number', 'geodir_fix_comment_count', 10, 2 );
if ( ! function_exists( 'geodir_fix_comment_count' ) ) {
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
	 * @todo $post is unreachable since the function return the count before that variable.
	 * @return bool|null|string The comment count.
	 */
	function geodir_fix_comment_count( $count, $post_id ) {
		if ( ! is_admin() || strpos( $_SERVER['REQUEST_URI'], 'admin-ajax.php' ) ) {
			global $post;
			$post_types = geodir_get_posttypes();

			if ( in_array( get_post_type( $post_id ), $post_types ) && ! geodir_cpt_has_rating_disabled( (int) $post_id ) ) {
				$review_count = geodir_get_review_count_total( $post_id );

				return $review_count;

				if ( $post && isset( $post->rating_count ) ) {
					return $post->rating_count;
				} else {
					return geodir_get_comments_number( $post_id );
				}
			} else {
				return $count;
			}
		} else {
			return $count;
		}
	}
}



/**
 * Check whether to display ratings or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 *
 * @param string $pageview The view template. Ex: listview, gridview etc.
 *
 * @return mixed|void
 */
function geodir_is_reviews_show( $pageview = '' ) {

	$active_tabs = geodir_get_option( 'geodir_detail_page_tabs_excluded' );

	$is_display = true;
	if ( ! empty( $active_tabs ) && in_array( 'reviews', $active_tabs ) ) {
		$is_display = false;
	}

	/**
	 * Filter to change display value.
	 *
	 * You can use this filter to change the is_display value.
	 *
	 * @since 1.0.0
	 * @package GeoDirectory
	 *
	 * @param bool $is_display Display ratings when set to true.
	 * @param string $pageview The view template. Ex: listview, gridview etc.
	 */
	return apply_filters( 'geodir_is_reviews_show', $is_display, $pageview );
}


/*
 * If Disqus plugin is active, do some fixes to show on blogs but no on GD post types
 */
if ( function_exists( 'dsq_can_replace' ) ) {
	remove_filter( 'comments_template', 'dsq_comments_template' );
	add_filter( 'comments_template', 'dsq_comments_template', 100 );
	add_filter( 'pre_option_disqus_active', 'geodir_option_disqus_active', 10, 1 );
}


/**
 * Disable Disqus plugin on the fly when visiting GeoDirectory post types.
 *
 * @since 1.5.0
 * @package GeoDirectory
 *
 * @param string $disqus_active Hook called before DB call for option so this is empty.
 *
 * @return string `1` if active `0` if disabled.
 */
function geodir_option_disqus_active( $disqus_active ) {
	global $post;
	$all_postypes = geodir_get_posttypes();

	if ( isset( $post->post_type ) && is_array( $all_postypes ) && in_array( $post->post_type, $all_postypes ) ) {
		$disqus_active = '0';
	}

	return $disqus_active;
}

/**
 * Detail page change tab title from reviews to comments.
 *
 * @since 1.0.0
 *
 * @package GeoDirectory
 *
 * @param array $tabs_arr Tabs array {@see geodir_detail_page_tab_headings_change()}.
 *
 * @return array Modified tabs array.
 */
function geodir_detail_reviews_tab_title( $tabs_arr ) {
	$post_type = geodir_get_current_posttype();

	if ( ! empty( $tabs_arr ) && ! empty( $tabs_arr['reviews'] ) && isset( $tabs_arr['reviews']['heading_text'] ) && $post_type != '' && geodir_cpt_has_rating_disabled( $post_type ) ) {
		$label_reviews = __( 'Comments', 'geodirectory' );

		if ( defined( 'GEODIR_CP_VERSION' ) ) {
			$post_types = geodir_get_posttypes( 'array' );

			if ( ! empty( $post_types[ $post_type ]['labels']['label_reviews'] ) ) {
				$label_reviews = stripslashes( __( $post_types[ $post_type ]['labels']['label_reviews'], 'geodirectory' ) );
			}
		}

		$tabs_arr['reviews']['heading_text'] = $label_reviews;
	}

	return $tabs_arr;
}

add_filter( 'geodir_detail_page_tab_list_extend', 'geodir_detail_reviews_tab_title', 1000, 1 );


/**
 * Disable JetPack comments on GD post types.
 *
 * @since 1.6.21
 */
function geodir_jetpack_disable_comments() {
	//only run if jetpack installed
	if ( defined( 'JETPACK__VERSION' ) ) {
		$post_types = geodir_get_posttypes();
		foreach ( $post_types as $post_type ) {
			add_filter( 'jetpack_comment_form_enabled_for_' . $post_type, '__return_false' );
		}
	}
}

add_action( 'plugins_loaded', 'geodir_jetpack_disable_comments' );

/**
 * Check whether the current post is open for reviews.
 *
 * @since 1.6.22
 *
 * @param bool $open Whether the current post is open for reviews.
 * @param int $post_id The post ID.
 *
 * @return bool True if allowed otherwise False.
 */
function geodir_check_reviews_open( $open, $post_id ) {
	if ( $open && $post_id && geodir_is_page( 'detail' ) ) {
		if ( in_array( get_post_status( $post_id ), array( 'draft', 'pending', 'auto-draft', 'trash' ) ) ) {
			$open = false;
		}
	}

	return $open;
}

add_filter( 'comments_open', 'geodir_check_reviews_open', 10, 2 );

function geodir_default_rating_icon( $full_path = false ) {
	$icon = geodir_get_option( 'geodir_default_rating_star_icon' );

	if ( ! $icon ) {
		$icon = geodir_file_relative_url( geodir_plugin_url() . '/assets/images/stars.png' );
		geodir_update_option( 'geodir_default_rating_star_icon', $icon );
	}

	$icon = geodir_file_relative_url( $icon, $full_path );

	return apply_filters( 'geodir_default_rating_icon', $icon, $full_path );
}

function geodir_check_notify_moderator( $maybe_notify, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
		$maybe_notify = '0' == $comment->comment_approved && (bool) GeoDir_Email::is_email_enabled( 'admin_moderate_comment' );
	}

	return $maybe_notify;
}

add_filter( 'notify_moderator', 'geodir_check_notify_moderator', 99999, 2 );

function geodir_comment_moderation_recipients( $emails, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$emails = array( GeoDir_Email::get_admin_email() );
	}

	return $emails;
}

add_filter( 'comment_moderation_recipients', 'geodir_comment_moderation_recipients', 10, 2 );

function geodir_comment_moderation_subject( $subject, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$post = geodir_get_post_info( $comment->comment_post_ID );

		$email_vars = array(
			'email_name' => 'admin_moderate_comment',
			'comment'    => $comment,
			'post'       => $post
		);

		$subject = GeoDir_Email::get_subject( 'admin_moderate_comment', $email_vars );
	}

	return $subject;
}

add_filter( 'comment_moderation_subject', 'geodir_comment_moderation_subject', 10, 2 );

function geodir_comment_moderation_text( $message, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$post       = geodir_get_post_info( $comment->comment_post_ID );
		$email_name = 'admin_moderate_comment';

		$email_vars = array(
			'comment' => $comment,
			'post'    => $post
		);

		$message_body  = GeoDir_Email::get_content( $email_name, $email_vars );

		$plain_text = GeoDir_Email::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$message = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading'	=> '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );
		$message = GeoDir_Email::style_body( $message, $email_name, $email_vars );
		$message = apply_filters( 'geodir_mail_content', $message, $email_name, $email_vars );
		if ( $plain_text ) {
			$message = wp_strip_all_tags( $message );
		}
	}

	return $message;
}

add_filter( 'comment_moderation_text', 'geodir_comment_moderation_text', 10, 2 );

function geodir_comment_moderation_headers( $headers, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$post = geodir_get_post_info( $comment->comment_post_ID );

		$email_vars = array(
			'email_name' => 'admin_moderate_comment',
			'comment'    => $comment,
			'post'       => $post
		);

		$headers = GeoDir_Email::get_headers( 'admin_moderate_comment', $email_vars );
	}

	return $headers;
}

add_filter( 'comment_moderation_headers', 'geodir_comment_moderation_headers', 10, 2 );

function geodir_check_notify_post_author( $maybe_notify, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
		return false;
	}

	return $maybe_notify;
}

add_filter( 'notify_post_author', 'geodir_check_notify_post_author', 99999, 2 );

function geodir_should_notify_comment_author( $comment ) {
	if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
		$comment_id = $comment->comment_ID;
	} else {
		$comment_id = $comment;
	}

	$notify      = GeoDir_Email::is_email_enabled( 'author_comment_approved' );
	$notify_sent = get_comment_meta( $comment_id, 'gd_comment_author_notified', true );

	if ( ! empty( $notify ) && empty( $notify_sent ) ) {
		$notify = true;
	} else {
		$notify = false;
	}

	return apply_filters( 'geodir_should_notify_comment_author', $notify, $comment_id );
}

function geodir_should_notify_listing_author( $comment ) {
	if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
		$comment_id = $comment->comment_ID;
	} else {
		$comment_id = $comment;
	}

	$notify      = GeoDir_Email::is_email_enabled( 'author_comment_approved' );
	$notify_sent = get_comment_meta( $comment_id, 'gd_listing_author_notified', true );

	if ( ! empty( $notify ) && empty( $notify_sent ) ) {
		$notify = true;
	} else {
		$notify = false;
	}

	return apply_filters( 'geodir_should_notify_listing_author', $notify, $comment_id );
}

function geodir_notify_on_comment_approved( $comment ) {
	if ( ! ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) ) {
		return;
	}

	$notify_comment_author = geodir_should_notify_comment_author( $comment );
	$notify_listing_author = geodir_should_notify_listing_author( $comment );

	if ( ! ( $notify_comment_author || $notify_comment_author ) ) {
		return;
	}


	// Notify to comment author
	if ( $notify_comment_author ) {
		update_comment_meta( $comment->comment_ID, 'gd_comment_author_notified', current_time( 'timestamp', 1 ) );

		GeoDir_Email::send_owner_comment_approved_email( $comment );
	}

	// Notify to listing author
	if ( $notify_listing_author ) {
		update_comment_meta( $comment->comment_ID, 'gd_listing_author_notified', current_time( 'timestamp', 1 ) );

		GeoDir_Email::send_author_comment_approved_email( $comment );
	}
}

add_action( 'comment_unapproved_to_approved', 'geodir_notify_on_comment_approved', 10, 2 );

/**
 * Send a notification of a new comment to the post author.
 *
 * @since 2.0.0
 *
 *
 * @param int $comment_ID Comment ID.
 *
 * @return bool True on success, false on failure.
 */
function geodir_new_comment_notify_postauthor( $comment_ID ) {
	$comment = get_comment( $comment_ID );

	$maybe_notify = get_option( 'comments_notify' );

	if ( $maybe_notify && ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
		$maybe_notify = (bool) GeoDir_Email::is_email_enabled( 'owner_comment_submit' );
	}

	/**
	 * Filters whether to send the post author new comment notification emails,
	 * overriding the site setting.
	 *
	 * @since 2.0.0
	 *
	 * @param bool $maybe_notify Whether to notify the post author about the new comment.
	 * @param int $comment_ID The ID of the comment for the notification.
	 */
	$maybe_notify = apply_filters( 'geodir_comment_notify_post_author', $maybe_notify, $comment_ID );

	if ( ! $maybe_notify ) {
		return false;
	}

	return GeoDir_Email::send_owner_comment_submit_email( $comment );
}

add_action( 'comment_post', 'geodir_new_comment_notify_postauthor', 99999, 1 );

function geodir_post_author_moderate_comment() {
	if ( empty( $_REQUEST['_gd_action'] ) ) {
		return;
	}
	$request = $_REQUEST;
	$action  = sanitize_text_field( $request['_gd_action'] );

	if ( ! empty( $request['c'] ) && ! empty( $request['_nonce'] ) && ( $user_ID = get_current_user_id() ) ) {
		$comment_ID = (int) $request['c'];
		if ( md5( $action . '_' . $comment_ID ) != $request['_nonce'] ) {
			return;
		}

		$comment = get_comment( $comment_ID );
		if ( empty( $comment->comment_post_ID ) ) {
			return;
		}

		// Comment approved.
		if ( (int) $comment->comment_approved === 1 ) {
			wp_safe_redirect( get_comment_link( $comment_ID ) );
			exit;
		}

		$post = get_post( $comment->comment_post_ID );
		if ( ! empty( $post ) ) {
			$user     = get_userdata( $user_ID );
			$redirect = get_permalink( $comment->comment_post_ID );

			if ( ! empty( $user ) && ( (int) $user_ID === (int) $post->post_author || $user->has_cap( 'moderate_comments' ) ) && geodir_is_gd_post_type( $post->post_type ) ) {
				if ( $action == 'approve_comment' ) {
					wp_set_comment_status( $comment, 'approve' );
					$redirect = get_comment_link( $comment_ID );
				} elseif ( $action == 'trash_comment' ) {
					wp_trash_comment( $comment );
				} elseif ( $action == 'spam_comment' ) {
					wp_spam_comment( $comment );
				}
			}
			wp_safe_redirect( $redirect );
			exit;
		}
	}
}

add_action( 'template_redirect', 'geodir_post_author_moderate_comment' );

function geodir_handle_comment_status_change( $comment_ID, $comment_status ) {
	if ( ! ( $comment_status == '1' || $comment_status == 'approve' ) ) {
		delete_comment_meta( $comment_ID, 'gd_comment_author_notified' );
		delete_comment_meta( $comment_ID, 'gd_listing_author_notified' );
	}
}

add_action( 'wp_set_comment_status', 'geodir_handle_comment_status_change', 10, 2 );








































/**
 * HTML for rating stars
 *
 * This is the main HTML markup that displays rating stars.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @since 2.0.0 $small depreciated.
 * @package GeoDirectory
 *
 * @param float $rating The post average rating.
 * @param int $post_id The post ID.
 * @param bool $small Depreciated.
 *
 * @return string Rating HTML.
 */
function geodir_get_rating_stars( $rating, $post_id, $small = false ) {
	if ( ! empty( $post_id ) && geodir_cpt_has_rating_disabled( (int) $post_id ) ) {
		return null;
	}

	$r_html = GeoDir_Comments::rating_output($rating);

	return apply_filters( 'geodir_get_rating_stars_html', $r_html, $rating, 5 );
}
<?php
/**
 * Comment related functions.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */


/*######################################################
General functions
######################################################*/

/**
 * Pluralize comment number.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @package GeoDirectory
 *
 * @param int|object $post The current post object.
 */
function geodir_comments_number( $post = 0 ) {
	global $gd_post;

	if ( $post === 0 ) {
		$post = $gd_post;
	} else if ( is_int( $post ) && $post > 0 ) {
		$post = geodir_get_post_info( $post );
	} else if ( is_object( $post ) ) {
		$post = isset( $post->rating_count ) ? $post : geodir_get_post_info( $post->ID );
	} else {
		$post = NULL;
	}

	if ( !empty( $post->post_type ) && geodir_cpt_has_rating_disabled( $post->post_type ) ) {
		$number = get_comments_number( $post->ID );

		if ( $number > 1 ) {
			$output = str_replace( '%', number_format_i18n( $number ), __( '% Comments', 'geodirectory' ) );
		} elseif ( $number == 0 || $number == '' ) {
			$output = __( 'No Comments', 'geodirectory' );
		} else { // must be one
			$output = __( '1 Comment', 'geodirectory' );
		}
	} else {
		$number = ! empty( $post->rating_count ) ? $post->rating_count : 0;

		if ( $number > 1 ) {
			$output = str_replace( '%', number_format_i18n( $number ), __( '% Reviews', 'geodirectory' ) );
		} elseif ( $number == 0 || $number == '' ) {
			$output = __( 'No Reviews', 'geodirectory' );
		} else { // must be one
			$output = __( '1 Review', 'geodirectory' );
		}
	}

	echo $output;
}

/**
 * HTML for rating stars
 *
 * This is the main HTML markup that displays rating stars.
 *
 * @since 1.0.0
 * @since 1.6.16 Changes for disable review stars for certain post type.
 * @since 2.0.0 $small deprecated.
 * @package GeoDirectory
 *
 * @param float $rating The post average rating.
 * @param int $post_id The post ID.
 * @param bool $small Depreciated.
 *
 * @return string Rating HTML.
 */
function geodir_get_rating_stars( $rating = '', $post_id = 0, $label = '' ) {
	if ( ! empty( $post_id ) && geodir_cpt_has_rating_disabled( get_post_type( (int) $post_id ) ) ) {
		return null;
	}

	if(empty($rating)){
		$rating = geodir_get_post_rating( $post_id );
	}

	$args = array();
	if($label){
		$args['rating_label'] = $label;
	}

	$r_html = GeoDir_Comments::rating_output($rating, $args);

	return apply_filters( 'geodir_get_rating_stars_html', $r_html, $rating, 5 );
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
	return GeoDir_Comments::get_post_rating($post_id , $force_query);
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
function geodir_get_review_count_total( $post_id = 0, $force_query = 0 ) {
	return GeoDir_Comments::get_post_review_count_total( $post_id, $force_query  );
}

/**
 * Get overall rating of a comment.
 *
 * Returns overall rating of a comment. If no results, returns false.
 *
 * @since 2.0.0
 * @package GeoDirectory
 *
 * @param int $comment_id The comment ID.
 *
 * @global object $wpdb WordPress Database object.
 * @return bool|null|string
 */
function geodir_get_comment_rating( $comment_id = 0 ) {
	return GeoDir_Comments::get_comment_rating($comment_id);
}

/*######################################################
Email functions
######################################################*/

/**
 * Function for check comment notify to moderator.
 *
 * @since 2.0.0
 *
 * @todo Kiran, is this the best place for these functions?
 *
 * @param bool $maybe_notify Comment notify value.
 * @param int $comment_id Comment id.
 * @return bool $maybe_notify.
 */
function geodir_check_notify_moderator( $maybe_notify, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
		$maybe_notify = '0' == $comment->comment_approved && (bool) GeoDir_Email::is_email_enabled( 'admin_moderate_comment' );
	}

	return $maybe_notify;
}

add_filter( 'notify_moderator', 'geodir_check_notify_moderator', 99999, 2 );

/**
 * The function is use for add admin email address for comment moderation recipients.
 *
 * @since 2.0.0
 *
 * @param array $emails Multiple emails address.
 * @param int $comment_id Comment id.
 * @return array $emails.
 */
function geodir_comment_moderation_recipients( $emails, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$emails = array( GeoDir_Email::get_admin_email() );
	}

	return $emails;
}

add_filter( 'comment_moderation_recipients', 'geodir_comment_moderation_recipients', 10, 2 );

/**
 * Function for get comment moderation subject.
 *
 * @since 2.0.0
 *
 * @param string $subject Comment moderation subject.
 * @param int $comment_id Comment id.
 * @return string $subject.
 */
function geodir_comment_moderation_subject( $subject, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$gd_post = geodir_get_post_info( $comment->comment_post_ID );

		$email_vars = array(
			'email_name' => 'admin_moderate_comment',
			'comment'    => $comment,
			'post'       => $gd_post
		);

		$subject = GeoDir_Email::get_subject( 'admin_moderate_comment', $email_vars );
	}

	return $subject;
}

add_filter( 'comment_moderation_subject', 'geodir_comment_moderation_subject', 10, 2 );

/**
 * Function for get comment moderation text.
 *
 * @since 2.0.0
 *
 * @param $message Comment message.
 * @param int $comment_id Comment id.
 * @return string $message.
 */
function geodir_comment_moderation_text( $message, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$gd_post       = geodir_get_post_info( $comment->comment_post_ID );
		$email_name = 'admin_moderate_comment';

		$email_vars = array(
			'comment' => $comment,
			'post'    => $gd_post
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

/**
 * Function for get comment moderation headers.
 *
 * @since 2.0.0
 *
 * @param string $headers Comment headers.
 * @param int $comment_id Comment id.
 * @return string $headers.
 */
function geodir_comment_moderation_headers( $headers, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) && GeoDir_Email::is_email_enabled( 'admin_moderate_comment' ) ) {
		$gd_post = geodir_get_post_info( $comment->comment_post_ID );

		$email_vars = array(
			'email_name' => 'admin_moderate_comment',
			'comment'    => $comment,
			'post'       => $gd_post
		);

		$headers = GeoDir_Email::get_headers( 'admin_moderate_comment', $email_vars );
	}

	return $headers;
}

add_filter( 'comment_moderation_headers', 'geodir_comment_moderation_headers', 10, 2 );

/**
 * Function for check comment notify to post author.
 *
 * @since 2.0.0
 *
 * @param bool $maybe_notify Comment notify value.
 * @param int $comment_id Comment id.
 * @return bool $maybe_notify.
 */
function geodir_check_notify_post_author( $maybe_notify, $comment_id ) {
	$comment = get_comment( $comment_id );

	if ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) {
		return false;
	}

	return $maybe_notify;
}

add_filter( 'notify_post_author', 'geodir_check_notify_post_author', 99999, 2 );

/**
 * Function for should notify to comment author.
 *
 * @since 2.0.0
 *
 * @param object $comment Comment object.
 * @return bool $notify.
 */
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

/**
 * Function for should notify to listing author.
 *
 * @since 2.0.0
 *
 * @param object $comment Comment object.
 * @return bool $notify..
 */
function geodir_should_notify_listing_author( $comment ) {
	if ( is_object( $comment ) && isset( $comment->comment_ID ) ) {
		$comment_id = $comment->comment_ID;
	} else {
		$comment_id = $comment;
	}

	$notify      = GeoDir_Email::is_email_enabled( 'owner_comment_approved' );
	$notify_sent = get_comment_meta( $comment_id, 'gd_listing_author_notified', true );

	if ( ! empty( $notify ) && empty( $notify_sent ) ) {
		$notify = true;
	} else {
		$notify = false;
	}

	return apply_filters( 'geodir_should_notify_listing_author', $notify, $comment_id );
}

/**
 * Function for notify on comment approved.
 *
 * @since 2.0.0
 *
 * @param object $comment Comment object.
 */
function geodir_notify_on_comment_approved( $comment ) {
	if ( ! ( ! empty( $comment->comment_post_ID ) && geodir_is_gd_post_type( get_post_type( $comment->comment_post_ID ) ) ) ) {
		return;
	}

	$notify_comment_author = geodir_should_notify_comment_author( $comment );
	$notify_listing_author = geodir_should_notify_listing_author( $comment );

	if ( ! ( $notify_comment_author || $notify_listing_author ) ) {
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

	// Only send notifications for approved or pending comments.
	if ( $maybe_notify && ! ( in_array( 'comment_approved', array_keys( (array) $comment ) ) && ( $comment->comment_approved == '0' || $comment->comment_approved === 'hold' || $comment->comment_approved == '1' || $comment->comment_approved === 'approve' ) ) ) {
		$maybe_notify = false;
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

/**
 * Function for template redirect when comment is approve.
 *
 * @since 2.0.0
 *
 */
function geodir_post_author_moderate_comment() {
	if ( empty( $_REQUEST['_gd_action'] ) ) {
		return;
	}

	// If not logged in redirect to login form.
	if ( ! is_user_logged_in() ) {
		$redirect = geodir_curPageURL();
		$login_url = geodir_login_url( $redirect );
		wp_safe_redirect( $login_url );
		exit;
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

		$gd_post = get_post( $comment->comment_post_ID );
		if ( ! empty( $gd_post ) ) {
			$user     = get_userdata( $user_ID );
			$redirect = get_permalink( $comment->comment_post_ID );

			if ( ! empty( $user ) && ( (int) $user_ID === (int) $gd_post->post_author || $user->has_cap( 'moderate_comments' ) ) && geodir_is_gd_post_type( $gd_post->post_type ) ) {
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


/**
 * Function for delete comment metadata by comment id.
 *
 * Check if comment status 1 or approve then delete comment metadata.
 *
 * @since 2.0.0
 *
 * @param int $comment_ID Comment id.
 * @param string $comment_status Comment status.
 */
function geodir_handle_comment_status_change( $comment_ID, $comment_status ) {
	if ( ! ( $comment_status == '1' || $comment_status == 'approve' ) ) {
		delete_comment_meta( $comment_ID, 'gd_comment_author_notified' );
		delete_comment_meta( $comment_ID, 'gd_listing_author_notified' );
	}
}

add_action( 'wp_set_comment_status', 'geodir_handle_comment_status_change', 10, 2 );

/**
 * Check whether current user can reply review.
 *
 * @since 2.3.8
 *
 * @param object $comment Comment object.
 * @return bool True if user allwed to reply else false.
 */
function geodir_user_can_reply_review( $comment ) {
	/**
	 * Filter whether current user can reply review.
	 *
	 * @since 2.3.8
	 *
	 * @param bool   $can_reply_review If allowed then true else false.
	 * @param object $comment Comment object.
	 */
	return apply_filters( 'geodir_user_can_reply_review', true, $comment );
}
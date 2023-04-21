<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var object $comment The comment object.
 * @var string|array $args Formatting options.
 * @var int $depth Depth of comment.
 * @var int $rating The rating number.
 */
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
				( ! empty( $comment->user_id ) && ! empty( $comment->comment_post_ID ) && ( (int) $comment->user_id == (int) get_post_field( 'post_author', (int) $comment->comment_post_ID ) ) ) ? '<span class="geodir-review-author">' . GeoDir_Comments::get_listing_owner_label( get_post_type( (int) $comment->comment_post_ID ) ) . '</span>' : ''
			);

			if($rating != 0){
				echo '<div class="geodir-review-ratings">'. geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div>';
			}
			printf( '<a class="geodir-review-time" href="%1$s"><span class="geodir-review-time" title="%3$s">%2$s</span></a>',
				esc_url( get_comment_link( $comment->comment_ID ) ),
				sprintf( _x( '%s ago', '%s = human-readable time difference', 'geodirectory' ), human_time_diff( get_comment_time( 'U' ), current_time( 'timestamp' ) ) ),
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
			<?php edit_comment_link( __( 'Edit', 'geodirectory' ), '<span class="edit-link">', '</span>' ); ?>
			<?php if ( geodir_user_can_reply_review( $comment ) ) { ?>
			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array(
					'reply_text' => __( 'Reply', 'geodirectory' ),
					'after'      => ' <span>&darr;</span>',
					'depth'      => $depth,
					'max_depth'  => $args['max_depth']
				) ) ); ?>
			</div>
			<?php } ?>
		</div>

		<!-- .reply -->
	</article>
	<!-- #comment-## -->

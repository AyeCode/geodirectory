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
<li <?php comment_class( 'geodir-comment list-unstyled' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="card mt-3 shadow-sm">
		<div class="card-header border-bottom toast-header">

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
			?>
				<?php if ( $avatar_size != 0  ): ?>
					<?php if(!empty($comment->user_id)){ ?><a href="<?php echo get_author_posts_url($comment->user_id); ?>" class="media-object float-left"><?php }?>
						<?php echo get_avatar( $comment, $avatar_size,'mm','', array('class'=>"comment_avatar rounded-circle position-relative") ); ?>
					<?php if(!empty($comment->user_id)){ ?></a><?php }?>
				<?php endif; ?>
				<span class="media-heading pl-2 mr-auto h4 m-0 align-items-center d-flex justify-content-center">
					<?php
					if(!empty($comment->user_id)){ echo "<a href='".get_author_posts_url($comment->user_id)."' class='' >"; }
					echo get_comment_author($comment->comment_ID);
					if(!empty($comment->user_id)){ echo "</a>"; }
					echo $comment->user_id === $post->post_author ? ' <span class="ml-2 h6 m-0"><span class="badge badge-primary">'.__( 'Post author', 'geodirectory' ).'</span></span>' : '';
					?>
				</span>

			<?php
			if($rating != 0){
				echo '<div class="geodir-review-ratings">'. geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div>';
			}
			?>


		</div>
		<!-- .comment-meta -->

		<?php if ( '0' == $comment->comment_approved ) : ?>
			<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'geodirectory' ); ?></p>
		<?php endif; ?>

		<div class="comment-content comment card-body m-0">
			<?php comment_text(); ?>
		</div>
		<!-- .comment-content -->

		<div class="card-footer py-2 px-3 bg-white">
			<div class="row">
				<div class="col-5 align-items-center d-flex">
					<a class="hidden-xs-down text-muted " href="<?php echo esc_url( get_comment_link( $comment->comment_ID, $args ) ); ?>">
						<time class="chip timeago" datetime="<?php comment_time( 'c' ); ?>">
							<?php comment_date() ?>,
							<?php comment_time() ?>
						</time>
					</a>
				</div>
				<div class="col-7 text-right">
					<div class="comment-links">
						<?php
						do_action( "geodir_comment_links_start" , $comment );
						edit_comment_link( __( 'Edit', 'geodirectory' ), '<span class="edit-link btn btn-link">', '</span>' );
						do_action( "geodir_comment_links_after_edit" , $comment );
						?>
						<span class="reply-link">
							<?php $reply_link = get_comment_reply_link( array_merge( $args, array(
								'reply_text' => __( 'Reply', 'geodirectory' ),
								'depth'      => $depth,
								'max_depth'  => $args['max_depth']
							) ) );
							echo str_replace("comment-reply-link","comment-reply-link btn btn-primary",$reply_link);
							?>
						</span>
						<?php do_action( "geodir_comment_links_end" , $comment ); ?>
					</div>
				</div>
			</div>
			
		</div>


		<!-- .reply -->
	</div>
	<!-- #comment-## -->

<?php
/**
 * Review item.
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/reviews/item.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.3.74
 *
 * Variables.
 *
 * @var object $comment The comment object.
 * @var string|array $args Formatting options.
 * @var int $depth Depth of comment.
 * @var int $rating The rating number.
 */

defined( 'ABSPATH' ) || exit;

global $post, $aui_bs5;
?>
<li <?php comment_class( 'geodir-comment list-unstyled' ); ?> id="li-comment-<?php comment_ID(); ?>">
	<div id="comment-<?php comment_ID(); ?>" class="card mt-3 shadow-sm">
		<div class="card-header border-bottom toast-header <?php echo $aui_bs5 ? 'px-2 py-1 border-bottom border-opacity-25' : ''; ?>">
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
					<?php if(!empty($comment->user_id)){ ?><a href="<?php echo get_author_posts_url($comment->user_id); ?>" class="media-object <?php echo ( $aui_bs5 ? 'float-start' : 'float-left' ); ?>" style="min-width:<?php echo (int) $avatar_size; ?>px"><?php }?>
						<?php echo get_avatar( $comment, $avatar_size,'mm','', array('class'=>"comment_avatar rounded-circle position-relative") ); ?>
					<?php if(!empty($comment->user_id)){ ?></a><?php }?>
				<?php endif; ?>
				<span class="media-heading pl-2 ps-2 mr-auto me-auto h4 m-0 align-items-center d-flex flex-wrap justify-content-center h5">
					<?php
					if(!empty($comment->user_id)){ echo "<a href='".get_author_posts_url($comment->user_id)."' class='text-reset' style='min-width:90px'>"; } else { echo "<span class='text-reset' style='min-width:90px'>"; }
					echo get_comment_author($comment->comment_ID);
					if(!empty($comment->user_id)){ echo "</a>"; } else { echo '</span>'; }
					if ( ! empty( $comment->user_id ) && ! empty( $comment->comment_post_ID ) && ( (int) $comment->user_id == (int) get_post_field( 'post_author', (int) $comment->comment_post_ID ) ) ) {
						echo ' <span class="ml-2 ms-2 h6 m-0 fs-sm"><span class="badge ' . ( $aui_bs5 ? 'bg-primary' : 'badge-primary' ) . '">'. GeoDir_Comments::get_listing_owner_label( get_post_type( (int) $comment->comment_post_ID ) ) . '</span></span>';
					}
					?>
				</span>

			<?php
			if($rating != 0){
				$ratings_html = '';
				if ( function_exists( 'geodir_reviewrating_get_comment_rating_by_id' ) ) {
					$comment_ratings = geodir_reviewrating_get_comment_rating_by_id($comment->comment_ID);
					$ratings = ! empty( $comment_ratings->ratings ) ? @unserialize($comment_ratings->ratings) : array();
					$ratings_html = GeoDir_Review_Rating_Template::geodir_reviewrating_draw_ratings($ratings, true);
				}
				echo '<div class="geodir-review-ratings c-pointer"  data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true"  data-bs-content="'.esc_attr($ratings_html).'" data-bs-trigger="hover focus" >'. geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div>';
			}
			?>
		</div>
		<!-- .comment-meta -->

		<?php if ( '0' == $comment->comment_approved ) : ?>
			<p class="comment-awaiting-moderation alert alert-warning m-3 mb-1 py-2"><?php _e( 'Your comment is awaiting moderation.', 'geodirectory' ); ?></p>
		<?php endif; ?>

		<div class="comment-content comment card-body m-0">
			<?php comment_text(); ?>
		</div>
		<!-- .comment-content -->

		<div class="card-footer py-2 px-3 bg-white">
			<div class="row">
				<div class="col-5 align-items-center d-flex">
					<a class="hidden-xs-down text-muted text-nowrap" href="<?php echo esc_url( get_comment_link( $comment->comment_ID, $args ) ); ?>">
						<small><time class="chip timeago" datetime="<?php comment_time( 'c' ); ?>">
							<?php comment_date() ?>,
							<?php comment_time() ?>
						</time></small>
					</a>
				</div>
				<div class="col-7 text-right text-end">
					<div class="comment-links">
						<?php
						do_action( "geodir_comment_links_start" , $comment );
						edit_comment_link( __( 'Edit', 'geodirectory' ), '<span class="edit-link btn btn-link">', '</span>' );
						do_action( "geodir_comment_links_after_edit" , $comment );

						if ( geodir_user_can_reply_review( $comment ) && '0' != $comment->comment_approved ) {
						?>
						<span class="reply-link">
							<?php $reply_link = get_comment_reply_link( array_merge( $args, array(
								'reply_text' => __( 'Reply', 'geodirectory' ),
								'depth'      => $depth,
								'max_depth'  => $args['max_depth']
							) ) );
							echo str_replace("comment-reply-link","comment-reply-link btn btn-sm btn-primary",$reply_link);
							?>
						</span>
						<?php } do_action( "geodir_comment_links_end" , $comment ); ?>
					</div>
				</div>
			</div>
		</div>
		<!-- .reply -->
	</div>
	<!-- #comment-## -->

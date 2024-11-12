<?php
/**
 * Review item clean.
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/reviews/item-clean.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory\Templates
 * @version    2.3.7
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
<li <?php comment_class( 'geodir-comment list-unstyled mb-4 pb-3 border-bottom fs-sm' ); ?>
	id="li-comment-<?php comment_ID(); ?>">
	<div class="" id="comment-<?php comment_ID(); ?>">
		<div class="d-flex justify-content-between mb-3">
			<div class="d-flex align-items-center pe-2">
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
				<?php if ( $avatar_size != 0 ) : ?>
					<?php
					if ( ! empty( $comment->user_id ) ) {
						?>
						<a href="<?php echo get_author_posts_url( $comment->user_id ); ?>" class="media-object <?php echo( $aui_bs5 ? 'float-start' : 'float-left' ); ?>"><?php } ?>
					<?php echo get_avatar( $comment, $avatar_size, 'mm', '', array( 'class' => 'comment_avatar rounded-circle position-relative' ) ); ?>
					<?php
					if ( ! empty( $comment->user_id ) ) {
						?>
						</a><?php } ?>
				<?php endif; ?>
				<div class="ps-2">
					<h6 class="fs-base mb-0 d-flex align-items-center">
						<?php
						if ( ! empty( $comment->user_id ) ) {
							echo "<a href='" . get_author_posts_url( $comment->user_id ) . "' class='text-reset' >";
						}
						echo get_comment_author( $comment->comment_ID );
						if ( ! empty( $comment->user_id ) ) {
							echo '</a>';
						}
						if ( ! empty( $comment->user_id ) && ! empty( $comment->comment_post_ID ) && ( (int) $comment->user_id == (int) get_post_field( 'post_author', (int) $comment->comment_post_ID ) ) ) {
							echo ' <span class="ml-2 ms-2 h6 m-0 fs-xs"><span class="badge ' . ( $aui_bs5 ? 'bg-primary' : 'badge-primary' ) . '">' . GeoDir_Comments::get_listing_owner_label( get_post_type( $comment->comment_post_ID ) ) . '</span></span>';
						}
						?>
					</h6>
					<?php
					if ( $rating != 0 ) {
						$ratings_html = '';
						if ( function_exists( 'geodir_reviewrating_get_comment_rating_by_id' ) ) {
							$comment_ratings = geodir_reviewrating_get_comment_rating_by_id( $comment->comment_ID );
							$ratings         = ! empty( $comment_ratings->ratings ) ? @unserialize($comment_ratings->ratings) : array();
							$ratings_html    = GeoDir_Review_Rating_Template::geodir_reviewrating_draw_ratings( $ratings, true );
						}
						echo '<div class="geodir-review-ratings c-pointer"  data-bs-toggle="popover-html" data-bs-sanitize="false" data-bs-placement="top" data-bs-html="true"  data-bs-content="' . esc_attr( $ratings_html ) . '" data-bs-trigger="hover focus" >' . geodir_get_rating_stars( $rating, $comment->comment_ID ) . '</div>';
					}
					?>
				</div>
			</div>
			<span class="text-muted fs-sm <?php if( '0' != $comment->comment_parent ){ echo 'd-flex align-items-center'; } ?>">
			<time class="chip timeago" datetime="<?php comment_time( 'c' ); ?>">
				<?php comment_date(); ?>,
				<?php comment_time(); ?>
			</time>
		</span>
		</div>

		<div class="comment-content comment m-0 mb-n3">
			<?php
			ob_start();
			comment_text();
			$comment_text = ob_get_clean();
			echo str_replace( '<p>', '<p class="fs-sm">', $comment_text );
			?>
		</div>
		<!-- .comment-content -->

		<div class=" text-right text-end">
			<div class="comment-links d-inline-flex align-items-center text-end">
				<?php
				do_action( 'geodir_comment_links_start', $comment );
				ob_start();
				edit_comment_link( __( 'Edit', 'geodirectory' ), '<span class="edit-link">', '</span>' );
				$edit_link = ob_get_clean();
				echo str_replace( 'comment-edit-link', 'comment-edit-link btn btn-sm btn-link px-1', $edit_link );
				do_action( 'geodir_comment_links_after_edit', $comment );

				if ( geodir_user_can_reply_review( $comment ) ) {
				?>
				<span class="reply-link">
					<?php
					$reply_link = get_comment_reply_link(
						array_merge(
							$args,
							array(
								'reply_text' => __( 'Reply', 'geodirectory' ),
								'depth'      => $depth,
								'max_depth'  => $args['max_depth'],
							)
						)
					);
					echo $reply_link ? str_replace( 'comment-reply-link', 'comment-reply-link btn btn-sm btn-link px-1', $reply_link ) : '';
					?>
				</span>
				<?php } do_action( 'geodir_comment_links_end', $comment ); ?>
			</div>
		</div>
	</div>
	<!-- .reply -->
</li>
<!-- #comment-## -->

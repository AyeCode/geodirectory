<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;

/**
 * Variables.
 *
 * @var object $comment The comment object.
 * @var int $comment_id The comment ID.
 * @var int $avatar_size The advatar size.
 * @var string $permalink The comment permalink.
 * @var string $comment_excerpt The comment excerpt.
 * @var string $post_title The post title.
 * @var int $comment_post_ID The comment post ID.
 * @var string $carousel If the item is in a carousel.
 * @var bool $active If the item is in a carousel and active.
 */
?>
<li class="clearfix list-unstyled mw-100 col <?php if($carousel){echo "carousel-item";}  if($active){echo " active";} ?>">
	<div class="card h-100 mw-100">
		<div class="card-header toast-header <?php echo $aui_bs5 ? 'ps-2 py-1 border-bottom border-opacity-25' : ''; ?>">
			<span class="li<?php echo $comment_id;?> geodir_reviewer_image mr-2 me-2">
			<?php echo get_avatar( $comment, $avatar_size, '', $comment_id . ' comment avatar',array('class'=>"comment_avatar rounded-circle position-static") );?>
			</span>
			<strong class="geodir_reviewer_author h5">
				<?php echo ( ! empty( $comment->user_id ) ? '<a href="' . get_author_posts_url( $comment->user_id ) . '">' : '' ); ?>
				<?php echo esc_attr( $comment->comment_author ); ?>
				<?php echo ( ! empty( $comment->user_id ) ? '</a>' : '' ); ?>

				<small class="text-muted font-weight-normal d-block h6 mb-0 fs-xs">
					<a href="<?php echo esc_url_raw( $comment_permalink ); ?>" class="geodir_reviewer_title text-muted"><?php echo esc_html( $post_title ); ?></a>
				</small>
			</strong>
		</div>

		<div class="card-body py-2 px-3">
			<?php echo geodir_get_rating_stars( $comment->rating, $comment_post_ID ); ?>
			<p class="geodir_reviewer_text p-0 m-0 mt-2"><?php echo $comment_excerpt;?></p>
		</div>
	</div>
</li>

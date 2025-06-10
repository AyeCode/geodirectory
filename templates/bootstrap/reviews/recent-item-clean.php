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
 * @var string $comment_permalink The comment permalink.
 * @var string $post_title The post title.
 * @var int $comment_post_ID The comment post ID.
 * @var string $carousel If the item is in a carousel.
 * @var bool $active If the item is in a carousel and active.
 */

$comment_content = wp_strip_all_tags( $comment->comment_content );
$comment_content = preg_replace( '#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content );
$comment_excerpt = geodir_utf8_substr( $comment_content, 0, 150 ) . '&#8230;';
?>
<li class="clearfix list-unstyled mw-100 col <?php if( $carousel) { echo "carousel-item"; }  if( $active ){ echo " active"; } ?>">
	<div class="card h-100 mw-100 shadow-sm border-0">
		<div class="card-body">
			<div class="fs-xs mb-2"><?php echo geodir_get_rating_stars( $comment->rating, $comment_post_ID ); ?></div>
			<p class="geodir_reviewer_text p-0 m-0 fs-base text-muted"><?php echo esc_html( $comment_excerpt ); ?></p>
		</div>

		<div class="card-footer border-0 pt-0 d-flex align-items-center">
			<span class="li<?php echo absint( $comment_id ); ?> geodir_reviewer_image mr-2 me-2">
			<?php echo get_avatar( $comment, $avatar_size, '', $comment_id . ' comment avatar', array( 'class' => 'comment_avatar rounded-circle position-static' ) ); ?>
			</span>
			<strong class="geodir_reviewer_author text-dark fs-sm">
				<?php
				if ( $comment->user_id ) {
					echo '<a href="' . esc_url_raw( get_author_posts_url( $comment->user_id ) ) . '" class="link-dark">';
				}
				echo esc_attr( $comment->comment_author );
				if ( $comment->user_id ) {
					echo '</a>';
				}
				?>
				<small class="text-muted font-weight-normal d-block h6 mb-0 fs-xs">
					<a href="<?php echo esc_url_raw( $comment_permalink ); ?>" class="geodir_reviewer_title text-muted"><?php echo esc_html( $post_title ); ?></a>
				</small>
			</strong>
		</div>
	</div>
</li>

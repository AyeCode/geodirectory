<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
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
 */
?>
<li class="clearfix">
	<span class="geodir_reviewer_content">
		<span class="li<?php echo $comment_id;?> geodir_reviewer_image">
			<?php echo get_avatar( $comment, $avatar_size, '', $comment_id . ' comment avatar' );?>
		</span>

		<?php
		if ( $comment->user_id ) {
			echo '<a href="' . get_author_posts_url( $comment->user_id ) . '">';
		}
		?>
		<span class="geodir_reviewer_author"><?php echo esc_attr( $comment->comment_author ); ?></span>
		<?php
		if ( $comment->user_id ) {
			echo '</a>';
		}
		?>

		<span class="geodir_reviewer_reviewed"><?php _e( 'reviewed', 'geodirectory' );?></span>

		<a href="<?php echo esc_url_raw( $permalink );?>" class="geodir_reviewer_title"><?php echo esc_html( $post_title );?></a>
		<?php echo geodir_get_rating_stars( $comment->rating, $comment_post_ID ); ?>
		<p class="geodir_reviewer_text"><?php echo $comment_excerpt;?></p>

	</span>
</li>
<?php
/**
 * Next Prev template
 *
 * @var array $args The widget arguments.
 * @var string $wrap_class The wrapper class for styles.
 * @var string $previous_post_link The previous post link.
 * @var string $next_post_link The next post link.
 *
 * @ver 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="geodir-pos_navigation clearfix <?php echo $wrap_class;?>">
	<div class="row">
		<div class="col geodir-post_left text-left"><?php echo str_replace("href=","class='badge badge-secondary' href=", $previous_post_link ); ?></div>
		<div class="col geodir-post_right text-right"><?php echo str_replace("href=","class='badge badge-secondary' href=", $next_post_link ); ?></div>
	</div>
</div>

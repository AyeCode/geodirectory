<?php
/**
 * Next Prev template
 *
 * @var array $args The widget arguments.
 * @var string $wrap_class The wrapper class for styles.
 *
 * @ver 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="geodir-pos_navigation clearfix <?php echo $wrap_class;?>">
	<div class="row">
		<div class="col geodir-post_left text-left"><?php echo str_replace("href=","class='badge badge-secondary' href=", get_previous_post_link('%link', '' . __('Previous', 'geodirectory'), false)); ?></div>
		<div class="col geodir-post_right text-right"><?php echo str_replace("href=","class='badge badge-secondary' href=", get_next_post_link('%link', __('Next', 'geodirectory') . '', false)); ?></div>
	</div>
</div>

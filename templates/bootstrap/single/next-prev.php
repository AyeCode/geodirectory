<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables. (none)
 *
 */
?>
<div class="geodir-pos_navigation clearfix my-3 mx-0">
	<div class="row">
		<div class="col geodir-post_left text-left"><?php echo str_replace("href=","class='badge badge-secondary' href=", get_previous_post_link('%link', '' . __('Previous', 'geodirectory'), false)); ?></div>
		<div class="col geodir-post_right text-right"><?php echo str_replace("href=","class='badge badge-secondary' href=", get_next_post_link('%link', __('Next', 'geodirectory') . '', false)); ?></div>
	</div>
</div>

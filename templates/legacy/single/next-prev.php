<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables. (none)
 *
 */
?>
<div class="geodir-pos_navigation clearfix">
	<div class="geodir-post_left"><?php previous_post_link('%link', '' . __('Previous', 'geodirectory'), false) ?></div>
	<div class="geodir-post_right"><?php next_post_link('%link', __('Next', 'geodirectory') . '', false) ?></div>
</div>

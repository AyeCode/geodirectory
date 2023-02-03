<?php
/**
 * Next Prev template
 *
 * @var array $args The widget arguments.
 * @var string $wrap_class The wrapper class for styles.
 * @var string $previous_post_link The previous post link.
 * @var string $next_post_link The next post link.
 *
 * @ver 2.2.19
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $aui_bs5;

?>
<div class="geodir-pos_navigation clearfix <?php echo $wrap_class;?>">
	<div class="row">
		<div class="col geodir-post_left <?php echo ( $aui_bs5 ? 'text-start' : 'text-left' ); ?>"><?php echo str_replace("href=","class='badge " . ( $aui_bs5 ? 'bg-secondary' : 'badge-secondary' ) . "' href=", $previous_post_link ); ?></div>
		<div class="col geodir-post_right <?php echo ( $aui_bs5 ? 'text-end' : 'text-right' ); ?>"><?php echo str_replace("href=","class='badge " . ( $aui_bs5 ? 'bg-secondary' : 'badge-secondary' ) . "' href=", $next_post_link ); ?></div>
	</div>
</div>

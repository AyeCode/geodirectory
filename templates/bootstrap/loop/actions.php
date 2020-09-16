<?php
/**
 * Loop Actions (default)
 *
 * @var string $wrap_class The wrapper class styles.
 * @ver 1.0.0
 */
?>
<div class="justify-content-end <?php echo $wrap_class;?>" role="toolbar" aria-label="<?php _e("Listing sort and view options","geodirectory");?>">
	<?php geodir_extra_loop_actions();?>
</div>
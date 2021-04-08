<?php
/**
 * Loop Actions
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/loop/actions.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.0.12
 *
 * @param string $wrap_class The wrapper class.
 * @param array  $args Loop arguments.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="clearfix">
	<?php geodir_extra_loop_actions( $args );?>
</div>
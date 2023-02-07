<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * @var array $args Arguments passed from calling function.
 * @var string $wrap_class The wrapper style classes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( $wp_query->max_num_pages <= 1 && empty( $args['preview'] ) ) {
	return;
}


?>
<div class="<?php echo esc_attr( $wrap_class ); ?>">
	<?php

	if ( ! empty( $args['advanced_pagination_only'] ) ) {
		echo wp_kses_post( $args['advanced_pagination_only'] );
	} else {
		/**
		 * Call AyeCode UI Pagination component.
		 */
		echo wp_kses_post( aui()->pagination( $args ) );
	}
	?>
</div>

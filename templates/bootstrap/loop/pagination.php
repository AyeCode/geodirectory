<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 *
 * @var array $args Arguments passed from calling function.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( $wp_query->max_num_pages <= 1 ) {
	return;
}

/**
 * Call AyeCode UI Pagination component.
 */
echo aui()->pagination($args);
?>
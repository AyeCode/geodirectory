<?php
/**
 * Pagination - Show numbered pagination for catalog pages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $wp_query;

if ( $wp_query->max_num_pages <= 1 ) {
	return;
}
?>
<nav class="geodir-pagination">
	<?php

	if(!empty($args['before_paging'])){
		echo $args['before_paging'];
	}

	echo paginate_links( apply_filters( 'geodir_pagination_args', array(
		'base'         => esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) ),
		'format'       => '',
		'add_args'     => false,
		'current'      => max( 1, get_query_var( 'paged' ) ),
		'total'        => $wp_query->max_num_pages,
		'prev_text'    => '&larr;',
		'next_text'    => '&rarr;',
		'type'         => 'list',
		'end_size'     => 3,
		'mid_size'     => 3,
	) ) );

	if(!empty($args['after_paging'])){
		echo $args['after_paging'];
	}

	?>
</nav>

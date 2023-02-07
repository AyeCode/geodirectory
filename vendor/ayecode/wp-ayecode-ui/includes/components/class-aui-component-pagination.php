<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * A component class for rendering a bootstrap pagination.
 *
 * @since 1.0.0
 */
class AUI_Component_Pagination {

	/**
	 * Build the component.
	 *
	 * @param array $args
	 *
	 * @return string The rendered component.
	 */
	public static function get( $args = array() ) {
		global $wp_query, $aui_bs5;

		$defaults = array(
			'class'              => '',
			'mid_size'           => 2,
			'prev_text'          => '<i class="fas fa-chevron-left"></i>',
			'next_text'          => '<i class="fas fa-chevron-right"></i>',
			'screen_reader_text' => __( 'Posts navigation','aui' ),
			'before_paging'      => '',
			'after_paging'       => '',
			'type'               => 'array',
			'total'              => isset( $wp_query->max_num_pages ) ? $wp_query->max_num_pages : 1,
			'links'              => array(), // an array of links if using custom links, this includes the a tag.
			'rounded_style'      => false,
		);

		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		$output = '';

		// Don't print empty markup if there's only one page.
		if ( $args['total'] > 1 ) {
			// Set up paginated links.
			$links = !empty(  $args['links'] ) ? $args['links'] :  paginate_links( $args );

			$class = !empty($args['class']) ? $args['class'] : '';



			// make the output bootstrap ready
			$links_html = "<ul class='pagination m-0 p-0 $class'>";
			if ( ! empty( $links ) ) {
				foreach ( $links as $link ) {
					if ( $aui_bs5 ) {
						$link_class = $args['rounded_style'] ? 'page-link badge rounded-pill border-0 mx-1 fs-base text-dark link-primary' : 'page-link';
						$link_class_active = $args['rounded_style'] ? ' current active fw-bold badge rounded-pill' : ' current active';
						$links_html .= "<li class='page-item mx-0'>";
						$link = str_replace( array( "page-numbers", " current" ), array( $link_class, $link_class_active ), $link );
						$link = str_replace( 'text-dark link-primary current', 'current', $link );
						$links_html .=  $link;
						$links_html .= "</li>";
					} else {
						$active = strpos( $link, 'current' ) !== false ? 'active' : '';
						$links_html .= "<li class='page-item $active'>";
						$links_html .= str_replace( "page-numbers", "page-link", $link );
						$links_html .= "</li>";
					}
				}
			}
			$links_html .= "</ul>";

			if ( $links ) {
				$output .= '<section class="px-0 py-2 w-100">';
				$output .= _navigation_markup( $links_html, 'aui-pagination', $args['screen_reader_text'] );
				$output .= '</section>';
			}

			$output = str_replace( "screen-reader-text", "screen-reader-text sr-only", $output );
			$output = str_replace( "nav-links", "aui-nav-links", $output );
		}

		if ( $output ) {
			if ( ! empty( $args['before_paging'] ) ) {
				$output = $args['before_paging'] . $output;
			}

			if ( ! empty( $args['after_paging'] ) ) {
				$output = $output . $args['after_paging'];
			}
		}

		return $output;
	}

}
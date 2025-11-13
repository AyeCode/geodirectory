<?php
/**
 * Templates Manager
 *
 * Handles template loading, paths, and template-related utilities.
 *
 * @package GeoDirectory\Core
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core;

/**
 * Templates service class.
 */
final class Templates {

	/**
	 * Get templates directory path.
	 *
	 * @since 3.0.0
	 *
	 * @return string Templates dir path.
	 */
	public function get_templates_dir(): string {
		return GEODIRECTORY_PLUGIN_DIR . 'templates';
	}

	/**
	 * Get templates directory URL.
	 *
	 * @since 3.0.0
	 *
	 * @return string Templates dir URL.
	 */
	public function get_templates_url(): string {
		return GEODIRECTORY_PLUGIN_URL . '/templates';
	}

	/**
	 * Get theme template directory name.
	 *
	 * @since 3.0.0
	 *
	 * @return string Theme template dir name.
	 */
	public function get_theme_template_dir_name(): string {
		/**
		 * Filters the theme template directory name.
		 *
		 * @since 2.0.0
		 *
		 * @param string $dir_name Directory name.
		 */
		return untrailingslashit( apply_filters( 'geodir_templates_dir', 'geodirectory' ) );
	}

	/**
	 * Convert listing view to CSS class.
	 *
	 * @since 3.0.0
	 *
	 * @param string $columns Column configuration.
	 * @return string CSS class.
	 */
	public function convert_listing_view_class( string $columns = '' ): string {
		$view = '';

		if ( $columns !== '' ) {
			$columns = (int) $columns;

			switch ( $columns ) {
				case 1:
					$view = 'gridview_onehalf';
					break;
				case 2:
					$view = 'gridview_onehalf';
					break;
				case 3:
					$view = 'gridview_onethird';
					break;
				case 4:
					$view = 'gridview_onefourth';
					break;
				case 5:
					$view = 'gridview_onefifth';
					break;
				default:
					$view = 'gridview_onethird';
					break;
			}
		}

		/**
		 * Filters the listing view class.
		 *
		 * @since 2.0.0
		 *
		 * @param string $view CSS class.
		 * @param string $columns Column configuration.
		 */
		return apply_filters( 'geodir_convert_listing_view_class', $view, $columns );
	}

	/**
	 * Get grid view class based on view number.
	 *
	 * @since 3.0.0
	 *
	 * @param int $view View number.
	 * @return string Grid view class.
	 */
	public function grid_view_class( int $view = 0 ): string {
		$class = '';

		if ( $view == 1 ) {
			$class = 'gridview_onehalf';
		} elseif ( $view == 2 ) {
			$class = 'gridview_onethird';
		} elseif ( $view == 3 ) {
			$class = 'gridview_onefourth';
		} elseif ( $view == 4 ) {
			$class = 'gridview_onefifth';
		} elseif ( $view == 5 ) {
			$class = 'list-view';
		}

		/**
		 * Filters the grid view class.
		 *
		 * @since 2.0.0
		 *
		 * @param string $class Grid view class.
		 * @param int $view View number.
		 */
		return apply_filters( 'geodir_grid_view_class', $class, $view );
	}

	/**
	 * Get advanced toggle class.
	 *
	 * @since 3.0.0
	 *
	 * @param string $default Default class.
	 * @return string Toggle class.
	 */
	public function advanced_toggle_class( string $default = '' ): string {
		$class = ! empty( $default ) ? $default : '';

		/**
		 * Filters the advanced toggle class.
		 *
		 * @since 2.0.0
		 *
		 * @param string $class Toggle class.
		 * @param string $default Default class.
		 */
		return apply_filters( 'geodir_advanced_toggle_class', $class, $default );
	}

	/**
	 * Check if current theme is a block theme.
	 *
	 * @since 3.0.0
	 *
	 * @return bool True if block theme.
	 */
	public function is_block_theme(): bool {
		if ( function_exists( 'wp_is_block_theme' ) ) {
			return wp_is_block_theme();
		}
		return false;
	}

	/**
	 * Get template type options.
	 *
	 * @since 3.0.0
	 *
	 * @return array Template type options.
	 */
	public function template_type_options(): array {
		$options = array(
			'page' => __( 'Page', 'geodirectory' ),
			'part' => __( 'Template Part', 'geodirectory' ),
		);

		/**
		 * Filters the template type options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options Template type options.
		 */
		return apply_filters( 'geodir_template_type_options', $options );
	}

	/**
	 * Get template page options.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array Page options.
	 */
	public function template_page_options( array $args = array() ): array {
		$defaults = array(
			'post_type' => 'page',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
			'suppress_filters' => false,
		);

		$args = wp_parse_args( $args, $defaults );

		$pages = get_posts( $args );
		$options = array();

		if ( ! empty( $pages ) ) {
			foreach ( $pages as $page ) {
				$options[ $page->ID ] = $page->post_title;
			}
		}

		/**
		 * Filters the template page options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options Page options.
		 * @param array $args Query arguments.
		 */
		return apply_filters( 'geodir_template_page_options', $options, $args );
	}

	/**
	 * Get template part options.
	 *
	 * @since 3.0.0
	 *
	 * @param array $args Query arguments.
	 * @return array Template part options.
	 */
	public function template_part_options( array $args = array() ): array {
		$defaults = array(
			'post_type' => 'wp_template_part',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'orderby' => 'title',
			'order' => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		$parts = array();

		// Get template parts
		if ( $this->is_block_theme() && function_exists( 'get_block_templates' ) ) {
			$templates = get_block_templates( array( 'post_type' => 'wp_template_part' ) );
			if ( ! empty( $templates ) ) {
				foreach ( $templates as $template ) {
					$parts[ $template->slug ] = $template->title;
				}
			}
		}

		/**
		 * Filters the template part options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $parts Template part options.
		 * @param array $args Query arguments.
		 */
		return apply_filters( 'geodir_template_part_options', $parts, $args );
	}

	/**
	 * Get template part by slug.
	 *
	 * @since 3.0.0
	 *
	 * @param string $slug Template part slug.
	 * @return object|null Template part object or null.
	 */
	public function get_template_part_by_slug( string $slug ) {
		if ( empty( $slug ) ) {
			return null;
		}

		if ( $this->is_block_theme() && function_exists( 'get_block_templates' ) ) {
			$templates = get_block_templates( array( 'post_type' => 'wp_template_part', 'slug__in' => array( $slug ) ) );
			if ( ! empty( $templates ) ) {
				return $templates[0];
			}
		}

		return null;
	}

	/**
	 * Filter textarea output.
	 *
	 * @since 3.0.0
	 *
	 * @param string $text Text to filter.
	 * @param string $context Context.
	 * @param array $args Additional arguments.
	 * @return string Filtered text.
	 */
	public function filter_textarea_output( string $text, string $context = '', array $args = array() ): string {
		$text = trim( $text );

		if ( empty( $text ) ) {
			return $text;
		}

		// Convert line breaks
		$text = wpautop( $text );

		// Run shortcodes
		$text = do_shortcode( $text );

		/**
		 * Filters the textarea output.
		 *
		 * @since 2.0.0
		 *
		 * @param string $text Filtered text.
		 * @param string $context Context.
		 * @param array $args Additional arguments.
		 */
		return apply_filters( 'geodir_filter_textarea_output', $text, $context, $args );
	}

	/**
	 * Get A-Z search options for a post type.
	 *
	 * @since 3.0.0
	 *
	 * @param string $post_type Post type.
	 * @return array A-Z options.
	 */
	public function az_search_options( string $post_type = '' ): array {
		$options = array();

		// Add numbers
		$options['0-9'] = '0-9';

		// Add letters A-Z
		foreach ( range( 'A', 'Z' ) as $letter ) {
			$options[ $letter ] = $letter;
		}

		/**
		 * Filters the A-Z search options.
		 *
		 * @since 2.0.0
		 *
		 * @param array $options A-Z options.
		 * @param string $post_type Post type.
		 */
		return apply_filters( 'geodir_az_search_options', $options, $post_type );
	}

	/**
	 * Get A-Z search value from request.
	 *
	 * @since 3.0.0
	 *
	 * @return string A-Z search value.
	 */
	public function az_search_value(): string {
		$value = '';

		if ( ! empty( $_REQUEST['gd_az'] ) ) {
			$value = sanitize_text_field( $_REQUEST['gd_az'] );
		}

		/**
		 * Filters the A-Z search value.
		 *
		 * @since 2.0.0
		 *
		 * @param string $value A-Z search value.
		 */
		return apply_filters( 'geodir_az_search_value', $value );
	}

	/**
	 * Make embeds responsive.
	 *
	 * @since 3.0.0
	 *
	 * @param string $html Embed HTML.
	 * @param string $url Embed URL.
	 * @param array $attr Embed attributes.
	 * @param int $post_ID Post ID.
	 * @return string Filtered embed HTML.
	 */
	public function responsive_embeds( string $html, string $url = '', array $attr = array(), int $post_ID = 0 ): string {
		if ( empty( $html ) || strpos( $html, 'iframe' ) === false ) {
			return $html;
		}

		// Wrap iframe in responsive container
		$html = '<div class="gd-responsive-embed">' . $html . '</div>';

		/**
		 * Filters the responsive embed HTML.
		 *
		 * @since 2.0.0
		 *
		 * @param string $html Embed HTML.
		 * @param string $url Embed URL.
		 * @param array $attr Embed attributes.
		 * @param int $post_ID Post ID.
		 */
		return apply_filters( 'geodir_responsive_embeds', $html, $url, $attr, $post_ID );
	}
}

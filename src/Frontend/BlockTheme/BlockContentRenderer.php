<?php
/**
 * Block Content Renderer
 *
 * Renders block-based content for GeoDirectory pages in FSE/Block themes.
 *
 * @package GeoDirectory\Frontend\BlockTheme
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\BlockTheme;

use AyeCode\GeoDirectory\Core\Services\Templates;

/**
 * Handles rendering of block content for GeoDirectory pages.
 *
 * @since 3.0.0
 */
final class BlockContentRenderer {

	/**
	 * GeoDirectory plugin slug used for template storage.
	 */
	private const PLUGIN_SLUG = 'geodirectory/geodirectory';

	/**
	 * Templates service.
	 *
	 * @var Templates
	 */
	private Templates $templates;

	/**
	 * Constructor.
	 *
	 * @param Templates $templates Templates service.
	 */
	public function __construct( Templates $templates ) {
		$this->templates = $templates;
	}

	/**
	 * Get the plugin slug used for block template storage.
	 *
	 * @return string Plugin slug.
	 */
	public function get_plugin_slug(): string {
		return self::PLUGIN_SLUG;
	}

	/**
	 * Get block templates directory name.
	 *
	 * @return string Directory name.
	 */
	public function get_templates_dir_name(): string {
		/**
		 * Filters the block templates directory name.
		 *
		 * @since 3.0.0
		 *
		 * @param string $dir_name Directory name.
		 */
		return apply_filters( 'geodir_block_templates_dir_name', 'block-templates' );
	}

	/**
	 * Get block template parts directory name.
	 *
	 * @return string Directory name.
	 */
	public function get_template_parts_dir_name(): string {
		/**
		 * Filters the block template parts directory name.
		 *
		 * @since 3.0.0
		 *
		 * @param string $dir_name Directory name.
		 */
		return apply_filters( 'geodir_block_template_parts_dir_name', 'block-template-parts' );
	}

	/**
	 * Render template content with blocks.
	 *
	 * Processes both shortcodes and blocks for the given content.
	 *
	 * @param string $content Raw content.
	 * @return string Rendered content.
	 */
	public function render_content( string $content ): string {
		if ( empty( $content ) ) {
			return $content;
		}

		// Run block content processing if available
		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}

		// Run shortcode processing
		$content = do_shortcode( $content );

		/**
		 * Filters the rendered block content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Rendered content.
		 */
		return apply_filters( 'geodir_render_block_content', $content );
	}

	/**
	 * Get template content from a template part.
	 *
	 * @param string $slug Template part slug.
	 * @return string Template content or empty string.
	 */
	public function get_template_part_content( string $slug ): string {
		if ( empty( $slug ) ) {
			return '';
		}

		$template_part = $this->templates->get_template_part_by_slug( $slug );

		if ( empty( $template_part->content ?? '' ) ) {
			return '';
		}

		return $this->render_content( $template_part->content );
	}

	/**
	 * Check if we should use block template rendering.
	 *
	 * @return bool True if should use block templates.
	 */
	public function should_use_block_templates(): bool {
		if ( ! $this->templates->is_block_theme() ) {
			return false;
		}

		/**
		 * Filters whether to use block template rendering.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $use_block_templates Whether to use block templates.
		 */
		return apply_filters( 'geodir_use_block_templates', true );
	}

	/**
	 * Render block template for current GeoDirectory page.
	 *
	 * Sets flags to indicate block template is available.
	 *
	 * @return void
	 */
	public function render_block_template(): void {
		if ( is_embed() || ! $this->should_use_block_templates() ) {
			return;
		}

		if ( ! function_exists( 'geodir_is_geodir_page' ) || ! geodir_is_geodir_page() ) {
			return;
		}

		$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';

		// Check for single posts
		if ( function_exists( 'geodir_is_singular' ) && geodir_is_singular() ) {
			if ( ! $this->theme_has_template( 'single-' . $post_type ) && $this->block_template_is_available( 'single-' . $post_type ) ) {
				add_filter( 'geodir_has_block_template', '__return_true', 10 );
			}
		}
		// Check for taxonomy archives
		elseif ( function_exists( 'geodir_is_taxonomy' ) && geodir_is_taxonomy() ) {
			if ( is_tax( $post_type . 'category' ) && ! $this->theme_has_template( $post_type . 'category' ) && $this->block_template_is_available( $post_type . 'category' ) ) {
				add_filter( 'geodir_has_block_template', '__return_true', 10 );
			} elseif ( is_tax( $post_type . '_tags' ) && ! $this->theme_has_template( $post_type . '_tags' ) && $this->block_template_is_available( $post_type . '_tags' ) ) {
				add_filter( 'geodir_has_block_template', '__return_true', 10 );
			}
		}
		// Check for post type archives
		elseif ( function_exists( 'geodir_is_post_type_archive' ) && geodir_is_post_type_archive() ) {
			$has_theme_template = $this->theme_has_template( 'archive-' . $post_type ) || $this->theme_has_template( 'gd-archive' );
			$has_block_template = $this->block_template_is_available( 'archive-' . $post_type ) || $this->block_template_is_available( 'gd-archive' );

			if ( ! $has_theme_template && $has_block_template ) {
				add_filter( 'geodir_has_block_template', '__return_true', 10 );
			}
		}
	}

	/**
	 * Check if theme has a block template.
	 *
	 * @param string $template_name Template name without .html extension.
	 * @return bool True if template exists in theme.
	 */
	public function theme_has_template( string $template_name ): bool {
		$template_dir = get_template_directory() . '/block-templates/' . $template_name . '.html';
		$stylesheet_dir = get_stylesheet_directory() . '/block-templates/' . $template_name . '.html';

		return is_readable( $template_dir ) || is_readable( $stylesheet_dir );
	}

	/**
	 * Check if theme has a block template part.
	 *
	 * @param string $template_name Template name without .html extension.
	 * @return bool True if template part exists in theme.
	 */
	public function theme_has_template_part( string $template_name ): bool {
		$template_dir = get_template_directory() . '/block-template-parts/' . $template_name . '.html';
		$stylesheet_dir = get_stylesheet_directory() . '/block-template-parts/' . $template_name . '.html';

		return is_readable( $template_dir ) || is_readable( $stylesheet_dir );
	}

	/**
	 * Check if a block template is available.
	 *
	 * @param string $template_name Template name.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return bool True if template is available.
	 */
	public function block_template_is_available( string $template_name, string $template_type = 'wp_template' ): bool {
		if ( empty( $template_name ) ) {
			return false;
		}

		$directory = $this->get_templates_directory( $template_type ) . '/' . $template_name . '.html';

		return is_readable( $directory );
	}

	/**
	 * Get templates directory path.
	 *
	 * @param string $template_type wp_template or wp_template_part.
	 * @return string Templates directory path.
	 */
	public function get_templates_directory( string $template_type = 'wp_template' ): string {
		$templates_dir = untrailingslashit( $this->templates->get_templates_dir() ) . '/';

		if ( function_exists( 'geodir_design_style' ) && ( $design_style = geodir_design_style() ) ) {
			$templates_dir .= $design_style . '/';
		}

		if ( $template_type === 'wp_template_part' ) {
			return $templates_dir . $this->get_template_parts_dir_name();
		}

		return $templates_dir . $this->get_templates_dir_name();
	}
}

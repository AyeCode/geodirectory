<?php
/**
 * Block Theme Hooks
 *
 * Centralized hooks for Full Site Editing (FSE) and Block Theme support.
 *
 * @package GeoDirectory\Frontend\BlockTheme
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\BlockTheme;

use AyeCode\GeoDirectory\Support\Hookable;

/**
 * Manages WordPress hooks for block theme / FSE functionality.
 *
 * @since 3.0.0
 */
final class BlockThemeHooks {
	use Hookable;

	/**
	 * Block template registry service.
	 *
	 * @var BlockTemplateRegistry
	 */
	private BlockTemplateRegistry $registry;

	/**
	 * Block content renderer service.
	 *
	 * @var BlockContentRenderer
	 */
	private BlockContentRenderer $renderer;

	/**
	 * Constructor.
	 *
	 * @param BlockTemplateRegistry $registry Template registry.
	 * @param BlockContentRenderer  $renderer Content renderer.
	 */
	public function __construct( BlockTemplateRegistry $registry, BlockContentRenderer $renderer ) {
		$this->registry = $registry;
		$this->renderer = $renderer;
	}

	/**
	 * Registers all WordPress hooks for block theme support.
	 *
	 * @return void
	 */
	public function register_hooks(): void {
		// Only register hooks if we should use block templates
		if ( ! $this->renderer->should_use_block_templates() ) {
			return;
		}

		// Register custom template types
		$this->filter( 'default_template_types', [ $this, 'register_template_types' ], 10 );

		// Initialize block styles
		$this->on( 'init', [ $this, 'register_block_styles' ], 20 );

		// Template rendering hooks
		$this->on( 'template_redirect', [ $this, 'maybe_render_block_template' ] );

		// Block template filters
		$this->filter( 'pre_get_block_file_template', [ $this, 'maybe_return_blocks_template' ], 10, 3 );
		$this->filter( 'get_block_templates', [ $this, 'filter_block_templates' ], 10, 3 );

		// Template hierarchy filters
		$this->filter( 'archive_template_hierarchy', [ $this, 'set_template_hierarchy' ], 10 );
		$this->filter( 'page_template_hierarchy', [ $this, 'set_template_hierarchy' ], 10 );
		$this->filter( 'search_template_hierarchy', [ $this, 'set_template_hierarchy' ], 10 );
		$this->filter( 'single_template_hierarchy', [ $this, 'set_template_hierarchy' ], 10 );

		// Screen ID filter for admin
		$this->filter( 'geodirectory_screen_ids', [ $this, 'add_screen_id' ], 10 );
	}

	/**
	 * Register GeoDirectory template types.
	 *
	 * @param array $templates Existing template types.
	 * @return array Modified template types.
	 */
	public function register_template_types( array $templates ): array {
		return $this->registry->register_template_types( $templates );
	}

	/**
	 * Register block styles for FSE editor.
	 *
	 * Loads appropriate CSS for the block editor based on design style.
	 *
	 * @return void
	 */
	public function register_block_styles(): void {
		// Determine which styles to load
		if ( function_exists( 'geodir_design_style' ) && geodir_design_style() ) {
			// Load AUI-based styles
			wp_register_style(
				'geodir-fse',
				geodir_plugin_url() . '/assets/css/admin.css',
				array( 'font-awesome', 'ayecode-ui' ),
				GEODIRECTORY_VERSION
			);
		} else {
			// Load default block editor styles
			wp_register_style(
				'geodir-fse',
				geodir_plugin_url() . '/assets/css/block_editor.css',
				array( 'wp-edit-blocks', 'font-awesome' ),
				GEODIRECTORY_VERSION
			);
		}

		// Register block type for loading styles in editor
		register_block_type(
			'geodirectory/fse-styles',
			array(
				'editor_style' => 'geodir-fse',
			)
		);
	}

	/**
	 * Maybe render block template for GeoDirectory pages.
	 *
	 * @return void
	 */
	public function maybe_render_block_template(): void {
		/**
		 * Fires before rendering a GeoDirectory block template.
		 *
		 * @since 3.0.0
		 */
		do_action( 'geodir_before_render_block_template' );

		// Actually render the block template
		$this->renderer->render_block_template();
	}

	/**
	 * Maybe return a block template for GeoDirectory pages.
	 *
	 * @param \WP_Block_Template|null $template Block template object or null.
	 * @param string                  $id Template ID.
	 * @param string                  $template_type Template type (wp_template or wp_template_part).
	 * @return \WP_Block_Template|null Modified template or null.
	 */
	public function maybe_return_blocks_template( $template, string $id, string $template_type ) {
		// Requires WP 5.9+
		if ( ! function_exists( 'get_block_template' ) ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( , $slug ) = $template_name_parts;

		// Remove filter to prevent infinite loop
		remove_filter( 'pre_get_block_file_template', [ $this, 'maybe_return_blocks_template' ], 10 );

		// Check if theme has a saved version
		$maybe_template = get_block_template( $id, $template_type );

		if ( null !== $maybe_template ) {
			add_filter( 'pre_get_block_file_template', [ $this, 'maybe_return_blocks_template' ], 10, 3 );
			return $maybe_template;
		}

		// Try GeoDirectory template
		add_filter( 'get_block_file_template', [ $this, 'get_single_block_template' ], 10, 3 );

		$maybe_template = get_block_template( $this->renderer->get_plugin_slug() . '//' . $slug, $template_type );

		// Re-hook and cleanup
		add_filter( 'pre_get_block_file_template', [ $this, 'maybe_return_blocks_template' ], 10, 3 );
		remove_filter( 'get_block_file_template', [ $this, 'get_single_block_template' ], 10, 3 );

		if ( null !== $maybe_template ) {
			return $maybe_template;
		}

		/**
		 * Filters the block template before returning.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Block_Template|null $template Block template.
		 * @param string                  $id Template ID.
		 * @param string                  $template_type Template type.
		 */
		return apply_filters( 'geodir_pre_get_block_file_template', $template, $id, $template_type );
	}

	/**
	 * Get single block template.
	 *
	 * Runs on get_block_file_template hook.
	 *
	 * @param \WP_Block_Template|null $template The found block template.
	 * @param string                  $id Template unique identifier.
	 * @param string                  $template_type wp_template or wp_template_part.
	 * @return \WP_Block_Template|null Block template or null.
	 */
	public function get_single_block_template( $template, string $id, string $template_type ) {
		// Template already found
		if ( null !== $template ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}

		list( , $slug ) = $template_name_parts;

		// Check if this template exists
		if ( ! $this->renderer->block_template_is_available( $slug, $template_type ) ) {
			return $template;
		}

		// Get available templates from registry
		$available_templates = $this->registry->add_block_templates( array(), array( 'slug__in' => array( $slug ) ), $template_type );

		if ( is_array( $available_templates ) && count( $available_templates ) > 0 ) {
			return $available_templates[0];
		}

		return $template;
	}

	/**
	 * Filter block templates to include GeoDirectory templates.
	 *
	 * @param \WP_Block_Template[] $templates Array of block templates.
	 * @param array                $query Template query args.
	 * @param string               $template_type Template type (wp_template or wp_template_part).
	 * @return \WP_Block_Template[] Modified templates.
	 */
	public function filter_block_templates( array $templates, array $query, string $template_type ): array {
		$templates = $this->registry->add_block_templates( $templates, $query, $template_type );

		/**
		 * Filters the GeoDirectory block templates.
		 *
		 * @since 3.0.0
		 *
		 * @param \WP_Block_Template[] $templates Block templates.
		 * @param array                $query Template query args.
		 * @param string               $template_type Template type.
		 */
		return apply_filters( 'geodir_set_block_templates', $templates, $query, $template_type );
	}

	/**
	 * Set template hierarchy for GeoDirectory pages.
	 *
	 * Ensures GeoDirectory templates are considered in the template hierarchy.
	 *
	 * @param array $templates Template hierarchy.
	 * @return array Modified template hierarchy.
	 */
	public function set_template_hierarchy( array $templates ): array {
		if ( ! function_exists( 'geodir_is_geodir_page' ) || ! geodir_is_geodir_page() ) {
			return $templates;
		}

		$offset = false;
		$push = array();

		if ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'location' ) ) {
			// Location
			$offset = 0;
			$template_id = function_exists( 'geodir_location_template_id' ) ? (int) geodir_location_template_id() : 0;

			if ( $template_id && ( $template_slug = get_post_field( 'post_name', $template_id ) ) ) {
				$push[] = $template_slug;
			}

			$push[] = 'gd-location.php';
		} elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'add-listing' ) ) {
			// Add Listing
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$offset = 0;
			$template_id = function_exists( 'geodir_add_listing_template_id' ) ? (int) geodir_add_listing_template_id( $post_type ) : 0;

			if ( $template_id && ( $template_slug = get_post_field( 'post_name', $template_id ) ) ) {
				$push[] = $template_slug;
			}

			$push[] = 'gd-add-listing.php';
		} elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'single' ) ) {
			// GD Single
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$offset = 1;
			$template_id = function_exists( 'geodir_details_template_id' ) ? (int) geodir_details_template_id( $post_type ) : 0;

			if ( $template_id && ( $template_slug = get_post_field( 'post_name', $template_id ) ) ) {
				$push[] = $template_slug;
			}

			$push[] = 'single-' . $post_type . '.php';
			$push[] = 'gd-single.php';
		} elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'search' ) ) {
			// Search
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$offset = 0;
			$template_id = function_exists( 'geodir_search_template_id' ) ? (int) geodir_search_template_id( $post_type ) : 0;

			if ( $template_id && ( $template_slug = get_post_field( 'post_name', $template_id ) ) ) {
				$push[] = $template_slug;
			}

			$push[] = 'gd-search.php';
			$push[] = 'gd-archive.php';
		} elseif ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'archive' ) ) {
			// Archive
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$offset = 0;

			if ( is_tax() && ( $term_id = get_queried_object_id() ) ) {
				$taxonomy = get_query_var( 'taxonomy' );

				if ( $taxonomy === $post_type . 'category' || $taxonomy === $post_type . '_tags' ) {
					$push[] = 'taxonomy-' . $taxonomy . '-' . $term_id . '.php';
					$push[] = 'taxonomy-' . $taxonomy . '.php';
				}
			}

			$template_id = function_exists( 'geodir_archive_template_id' ) ? (int) geodir_archive_template_id( $post_type ) : 0;

			if ( $template_id && ( $template_slug = get_post_field( 'post_name', $template_id ) ) ) {
				$push[] = $template_slug;
			} else {
				$offset = in_array( 'archive-' . $post_type . '.php', $templates ) ? array_search( 'archive-' . $post_type . '.php', array_values( $templates ) ) + 1 : 0;
			}

			if ( is_tax() ) {
				$push[] = 'gd-taxonomy.php';
			}
			$push[] = 'archive-' . $post_type . '.php';
			$push[] = 'gd-archive.php';
		}

		if ( $offset !== false ) {
			$templates = array_merge( array_slice( $templates, 0, $offset ), $push, array_slice( $templates, $offset ) );
			$templates = array_unique( $templates );
		}

		/**
		 * Filters the GeoDirectory template hierarchy.
		 *
		 * @since 3.0.0
		 *
		 * @param array $templates Template hierarchy.
		 */
		return apply_filters( 'geodir_block_template_hierarchy', $templates );
	}

	/**
	 * Add block editor screen IDs for GeoDirectory.
	 *
	 * @param array $screen_ids Existing screen IDs.
	 * @return array Modified screen IDs.
	 */
	public function add_screen_id( array $screen_ids ): array {
		$screen_ids[] = 'appearance_page_gutenberg-edit-site';
		$screen_ids[] = 'site-editor';

		return $screen_ids;
	}
}

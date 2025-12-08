<?php
/**
 * Block Template Registry
 *
 * Registers GeoDirectory-specific block template types with WordPress.
 * Used for Full Site Editing (FSE) / Block Themes.
 *
 * @package GeoDirectory\Frontend\BlockTheme
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\BlockTheme;

use AyeCode\GeoDirectory\Core\Services\PostTypes;

/**
 * Handles registration of custom template types for GeoDirectory post types.
 *
 * @since 3.0.0
 */
final class BlockTemplateRegistry {

	/**
	 * GeoDirectory plugin slug used for template storage.
	 */
	private const PLUGIN_SLUG = 'geodirectory/geodirectory';

	/**
	 * Block templates directory name.
	 */
	private const TEMPLATES_DIR_NAME = 'block-templates';

	/**
	 * Block template parts directory name.
	 */
	private const TEMPLATE_PARTS_DIR_NAME = 'block-template-parts';

	/**
	 * Post Types service for getting registered CPTs.
	 *
	 * @var PostTypes
	 */
	private PostTypes $post_types;

	/**
	 * Constructor.
	 *
	 * @param PostTypes $post_types Post types service.
	 */
	public function __construct( PostTypes $post_types ) {
		$this->post_types = $post_types;
	}

	/**
	 * Get default template types for GeoDirectory.
	 *
	 * Registers single and archive templates for each GeoDirectory post type.
	 *
	 * @param array $templates Existing template types.
	 * @return array Modified template types.
	 */
	public function register_template_types( array $templates ): array {
		// Get all registered GeoDirectory post types
		$post_types = $this->post_types->get_all( "objects" );

		if ( empty( $post_types ) ) {
			return $templates;
		}

		foreach ( $post_types as $post_type => $args ) {
			$post_type_obj = get_post_type_object( $post_type );

			if ( ! $post_type_obj ) {
				continue;
			}

			$label = $post_type_obj->labels->singular_name ?? ucfirst( $post_type );

			// Register single template type
			$templates[ "single-{$post_type}" ] = array(
				'title'       => sprintf(
					/* translators: %s: Post type singular name */
					_x( 'Single %s', 'Template name', 'geodirectory' ),
					$label
				),
				'description' => sprintf(
					/* translators: %s: Post type singular name */
					__( 'Template used to display a single %s post.', 'geodirectory' ),
					$label
				),
			);

			// Register archive template type
			$templates[ "archive-{$post_type}" ] = array(
				'title'       => sprintf(
					/* translators: %s: Post type singular name */
					_x( 'Archive %s', 'Template name', 'geodirectory' ),
					$label
				),
				'description' => sprintf(
					/* translators: %s: Post type singular name */
					__( 'Template used to display the %s archive.', 'geodirectory' ),
					$label
				),
			);
		}

		/**
		 * Filters the GeoDirectory block template types.
		 *
		 * @since 3.0.0
		 *
		 * @param array $templates Template types.
		 * @param array $post_types GeoDirectory post types.
		 */
		return apply_filters( 'geodir_block_template_types', $templates, $post_types );
	}

	/**
	 * Add GeoDirectory block template objects.
	 *
	 * @param array  $query_result Array of template objects.
	 * @param array  $query Optional arguments to retrieve templates.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array Modified template objects.
	 */
	public function add_block_templates( array $query_result, array $query, string $template_type ): array {
		$post_type      = $query['post_type'] ?? '';
		$slugs          = $query['slug__in'] ?? array();
		$template_files = $this->get_block_templates( $slugs, $template_type );

		foreach ( $template_files as $template_file ) {
			// Avoid duplicates
			$is_duplicate = array_filter(
				$query_result,
				function( $query_result_template ) use ( $template_file ) {
					return $query_result_template->slug === $template_file->slug
						&& $query_result_template->theme === $template_file->theme;
				}
			);

			if ( $is_duplicate ) {
				continue;
			}

			// Skip if post_type doesn't match
			if ( $post_type && isset( $template_file->post_types ) && ! in_array( $post_type, $template_file->post_types, true ) ) {
				continue;
			}

			// Load from filesystem if not custom
			if ( 'custom' !== $template_file->source ) {
				$template = $this->build_template_result_from_file( $template_file, $template_type );
			} else {
				$template_file->title = $this->convert_slug_to_title( $template_file->slug );
				$query_result[] = $template_file;
				continue;
			}

			$is_not_custom   = false === array_search( wp_get_theme()->get_stylesheet() . '//' . $template_file->slug, array_column( $query_result, 'id' ), true );
			$fits_slug_query = ! isset( $query['slug__in'] ) || in_array( $template_file->slug, $query['slug__in'], true );
			$fits_area_query = ! isset( $query['area'] ) || $template_file->area === $query['area'];
			$should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;

			if ( $should_include ) {
				$query_result[] = $template;
			}
		}

		$query_result = $this->remove_theme_templates_with_custom_alternative( $query_result );

		return $query_result;
	}

	/**
	 * Get block templates.
	 *
	 * @param array  $slugs Template slugs to get.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array Template objects.
	 */
	private function get_block_templates( array $slugs = array(), string $template_type = 'wp_template' ): array {
		$templates_from_db = $this->get_block_templates_from_db( $slugs, $template_type );
		$templates_from_gd = $this->get_block_templates_from_gd( $slugs, $templates_from_db, $template_type );

		return array_merge( $templates_from_db, $templates_from_gd );
	}

	/**
	 * Get block templates from database.
	 *
	 * @param array  $slugs Template slugs.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array Template objects from database.
	 */
	private function get_block_templates_from_db( array $slugs = array(), string $template_type = 'wp_template' ): array {
		$check_query_args = array(
			'post_type'      => $template_type,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array(
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => array( self::PLUGIN_SLUG, get_stylesheet() ),
				),
			),
		);

		if ( ! empty( $slugs ) ) {
			$check_query_args['post_name__in'] = $slugs;
		}

		$check_query         = new \WP_Query( $check_query_args );
		$saved_gd_templates = $check_query->posts;

		return array_map(
			function( $saved_gd_template ) {
				return $this->build_template_result_from_post( $saved_gd_template );
			},
			$saved_gd_templates
		);
	}

	/**
	 * Get block templates from GeoDirectory plugin files.
	 *
	 * @param array  $slugs Template slugs.
	 * @param array  $already_found_templates Templates already found.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array Template objects from files.
	 */
	private function get_block_templates_from_gd( array $slugs, array $already_found_templates, string $template_type = 'wp_template' ): array {
		$templates = array();
		$template_files = $this->get_block_template_files( $template_type );

		foreach ( $template_files as $template_file ) {
			// Skip if not in slugs filter
			if ( $slugs && ! in_array( $template_file['slug'], $slugs, true ) ) {
				continue;
			}

			// Skip if already in custom templates
			$is_not_custom = false === array_search(
				self::PLUGIN_SLUG . '//' . $template_file['slug'],
				array_column( $already_found_templates, 'id' ),
				true
			);

			if ( ! $is_not_custom ) {
				continue;
			}

			$templates[] = $this->create_new_block_template_object(
				$template_file['path'],
				$template_type,
				$template_file['slug']
			);
		}

		return $templates;
	}

	/**
	 * Get block template files from filesystem.
	 *
	 * @param string $template_type wp_template or wp_template_part.
	 * @return array Template file info.
	 */
	private function get_block_template_files( string $template_type ): array {
		$template_files = array();
		$dir_name = $template_type === 'wp_template_part' ? self::TEMPLATE_PARTS_DIR_NAME : self::TEMPLATES_DIR_NAME;

		// Plugin templates
		$plugin_templates_dir = GEODIRECTORY_PLUGIN_DIR . 'templates/' . $dir_name . '/';
		if ( is_dir( $plugin_templates_dir ) ) {
			$templates = glob( $plugin_templates_dir . '*.html' );
			if ( $templates ) {
				foreach ( $templates as $template ) {
					$template_files[] = array(
						'slug' => basename( $template, '.html' ),
						'path' => $template,
					);
				}
			}
		}

		// Theme templates
		$theme_templates_dir = get_stylesheet_directory() . '/' . $dir_name . '/';
		if ( is_dir( $theme_templates_dir ) ) {
			$templates = glob( $theme_templates_dir . '*.html' );
			if ( $templates ) {
				foreach ( $templates as $template ) {
					$slug = basename( $template, '.html' );
					// Only add if not already in plugin templates
					if ( ! in_array( $slug, array_column( $template_files, 'slug' ) ) ) {
						$template_files[] = array(
							'slug' => $slug,
							'path' => $template,
						);
					}
				}
			}
		}

		return $template_files;
	}

	/**
	 * Build template object from post.
	 *
	 * @param \WP_Post $post Template post.
	 * @return object Template object.
	 */
	private function build_template_result_from_post( \WP_Post $post ): object {
		$terms = get_the_terms( $post, 'wp_theme' );

		if ( ! is_array( $terms ) || count( $terms ) === 0 ) {
			$theme = wp_get_theme()->get_stylesheet();
		} else {
			$theme = $terms[0]->name;
		}

		$template = new \WP_Block_Template();
		$template->wp_id           = $post->ID;
		$template->id              = $theme . '//' . $post->post_name;
		$template->theme           = $theme;
		$template->content         = $post->post_content;
		$template->slug            = $post->post_name;
		$template->source          = 'custom';
		$template->type            = $post->post_type;
		$template->description     = $post->post_excerpt;
		$template->title           = $post->post_title;
		$template->status          = $post->post_status;
		$template->has_theme_file  = true;
		$template->is_custom       = true;
		$template->post_types      = array();

		return $template;
	}

	/**
	 * Build template result from file.
	 *
	 * @param object $template_file Template file object.
	 * @param string $template_type wp_template or wp_template_part.
	 * @return object Template object.
	 */
	private function build_template_result_from_file( object $template_file, string $template_type ): object {
		$template_content = file_get_contents( $template_file->path );

		$template                 = new \WP_Block_Template();
		$template->id             = $template_file->theme . '//' . $template_file->slug;
		$template->theme          = $template_file->theme;
		$template->content        = $template_content;
		$template->source         = $template_file->source;
		$template->slug           = $template_file->slug;
		$template->type           = $template_type;
		$template->title          = $template_file->title;
		$template->description    = $template_file->description;
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->is_custom      = false;
		$template->post_types     = array();

		return $template;
	}

	/**
	 * Create new block template object.
	 *
	 * @param string $template_file Block template file path.
	 * @param string $template_type wp_template or wp_template_part.
	 * @param string $template_slug Block template slug.
	 * @param bool   $template_is_from_theme If loading from theme.
	 * @return object Block template object.
	 */
	private function create_new_block_template_object(
		string $template_file,
		string $template_type,
		string $template_slug,
		bool $template_is_from_theme = false
	): object {
		$theme_name = wp_get_theme()->get( 'TextDomain' );

		$new_template_item = array(
			'slug'        => $template_slug,
			'id'          => $template_is_from_theme ? $theme_name . '//' . $template_slug : self::PLUGIN_SLUG . '//' . $template_slug,
			'path'        => $template_file,
			'type'        => $template_type,
			'theme'       => $template_is_from_theme ? $theme_name : self::PLUGIN_SLUG,
			'source'      => $template_is_from_theme ? 'theme' : 'plugin',
			'title'       => $this->convert_slug_to_title( $template_slug ),
			'description' => $this->get_block_description( $template_slug ),
			'post_types'  => array(),
		);

		return (object) $new_template_item;
	}

	/**
	 * Convert template slug to title.
	 *
	 * @param string $template_slug Template slug.
	 * @return string Human-friendly title.
	 */
	private function convert_slug_to_title( string $template_slug ): string {
		switch ( $template_slug ) {
			case 'gd-location':
				return __( 'GD Location', 'geodirectory' );
			case 'gd-search':
				return __( 'GD Search', 'geodirectory' );
			case 'gd-add-listing':
				return __( 'GD Add Listing', 'geodirectory' );
			case 'gd-archive':
				return __( 'GD Archive', 'geodirectory' );
			case 'gd-single':
				return __( 'GD Single', 'geodirectory' );
			default:
				return ucwords( preg_replace( '/[\-_]/', ' ', $template_slug ) );
		}
	}

	/**
	 * Get block template description.
	 *
	 * @param string $template_slug Template slug.
	 * @return string Template description.
	 */
	private function get_block_description( string $template_slug ): string {
		switch ( $template_slug ) {
			case 'gd-location':
				return __( 'The GeoDirectory Location page displays a list of locations.', 'geodirectory' );
			case 'gd-search':
				return __( 'The GeoDirectory Search page displays search results.', 'geodirectory' );
			case 'gd-add-listing':
				return __( 'The GeoDirectory Add Listing page allows users to submit listings.', 'geodirectory' );
			case 'gd-archive':
				return __( 'The GeoDirectory Archive displays listings in a specific category or location.', 'geodirectory' );
			case 'gd-single':
				return __( 'The GeoDirectory Single page displays a single listing.', 'geodirectory' );
			default:
				return '';
		}
	}

	/**
	 * Remove theme templates that have custom alternatives.
	 *
	 * @param array $templates Template objects.
	 * @return array Filtered templates.
	 */
	private function remove_theme_templates_with_custom_alternative( array $templates ): array {
		$templates_map = array();
		foreach ( $templates as $template ) {
			$templates_map[ $template->slug ] = $template;
		}

		foreach ( $templates as $key => $template ) {
			if ( $template->source === 'theme' && isset( $templates_map[ $template->slug ] ) && $templates_map[ $template->slug ]->source === 'custom' ) {
				unset( $templates[ $key ] );
			}
		}

		return array_values( $templates );
	}
}

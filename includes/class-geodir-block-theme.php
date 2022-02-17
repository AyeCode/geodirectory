<?php
/**
 * GeoDir_Block_Theme class
 *
 * @package GeoDirectory
 * @since   2.1.1.14
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GeoDir_Block_Theme class.
 */
class GeoDir_Block_Theme {

	/**
	 * GeoDirectory plugin slug
	 *
	 * This is used to save templates to the DB which are stored against this value in the wp_terms table.
	 *
	 * @var string
	 */
	const PLUGIN_SLUG = 'geodirectory/geodirectory';

	/**
	 * Directory name of the block template directory.
	 *
	 * @var string
	 */
	const TEMPLATES_DIR_NAME = 'block-templates';

	/**
	 * Directory name of the block template parts directory.
	 *
	 * @var string
	 */
	const TEMPLATE_PARTS_DIR_NAME = 'block-template-parts';

	/**
	 * Setup.
	 */
	public static function init() {
		add_action( 'template_redirect', array( __CLASS__, 'render_block_template' ) );
		add_filter( 'pre_get_block_file_template', array( __CLASS__, 'maybe_return_blocks_template' ), 10, 3 );
		add_filter( 'get_block_templates', array( __CLASS__, 'set_block_templates' ), 10, 3 );
		add_filter( 'geodirectory_screen_ids', array( __CLASS__, 'set_screen_id' ), 10, 1 );
		//add_filter( 'enqueue_block_assets', array( __CLASS__, 'enqueue_block_assets' ), 20 );
		//add_filter( 'enqueue_block_editor_assets', array( __CLASS__, 'enqueue_block_editor_assets' ), 20 );
		//add_action( 'init', array( __CLASS__, 'on_init' ), 20 );
	}

	// LOADS AUI ON Editor
	/*public static function on_init() {
		register_block_type(
			'geodirectory/sample',
			array(
				'render_callback' => array( __CLASS__, 'render_sample_block' ),
				'editor_style'    => 'ayecodeuisample',
			)
		);
	}*/

	public static function render_sample_block( $attributes, $content ) {
		//if ( ! wp_style_is( 'kadence-blocks-image-overlay', 'enqueued' ) ) {
			//wp_enqueue_style( 'kadence-blocks-image-overlay' );
		//}
		geodir_error_log( $content, 'content', __FILE__, __LINE__ );
		return $content;
	}

	// @TODO
	public static function enqueue_block_assets() {geodir_error_log( 'enqueue_block_assets()', '', __FILE__, __LINE__ );
		$url = geodir_plugin_url() . '/vendor/ayecode/wp-ayecode-ui/assets/css/ayecode-ui-compatibility.css';geodir_error_log( $url, 'enqueue_block_assets', __FILE__, __LINE__ );
		wp_register_style( 'ayecodeuisample', $url, array(), '4.5.4' );
		wp_enqueue_style( 'ayecodeuisample' );
	}

	// @TODO
	public static function enqueue_block_editor_assets() {geodir_error_log( 'enqueue_block_editor_assets()', '', __FILE__, __LINE__ );
		$url = geodir_plugin_url() . '/vendor/ayecode/wp-ayecode-ui/assets/css/ayecode-ui-compatibility.css';geodir_error_log( $url, 'enqueue_block_editor_assets', __FILE__, __LINE__ );
		wp_register_style( 'ayecodeuisample', $url, array(), '4.5.4' );
		wp_enqueue_style( 'ayecodeuisample' );
	}

	/**
	 * Checks to see if they are using a compatible version of WP.
	 *
	 * @return boolean
	 */
	public static function supports_block_templates() {
		if ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) {
			return true;
		}

		return false;
	}

	/**
	 * Load AUI on site editor.
	 *
	 * @return array
	 */
	public static function set_screen_id( $aui_screens ) {
		global $current_screen;

		if ( ! empty( $current_screen ) && ( $current_screen instanceof WP_Screen ) && $current_screen->id == 'site-editor' && self::supports_block_templates() && function_exists( 'wp_should_load_block_editor_scripts_and_styles' ) && wp_should_load_block_editor_scripts_and_styles() ) {
			$aui_screens[] = 'site-editor';
		}

		return $aui_screens;
	}

	/**
	 * Add the block template objects to be used.
	 *
	 * @param array $query_result Array of template objects.
	 * @param array $query Optional. Arguments to retrieve templates.
	 * @param array $template_type wp_template or wp_template_part.
	 * @return array
	 */
	public static function set_block_templates( $query_result, $query, $template_type ) {//geodir_error_log( $template_type, 'template_type', __FILE__, __LINE__ );
		if ( ! self::supports_block_templates() ) {
			return $query_result;
		}

		$post_type      = isset( $query['post_type'] ) ? $query['post_type'] : '';
		$slugs          = isset( $query['slug__in'] ) ? $query['slug__in'] : array();
		$template_files = self::get_block_templates( $slugs, $template_type );//geodir_error_log( $template_files, 'template_files', __FILE__, __LINE__ );

		// @todo: Add apply_filters to _gutenberg_get_template_files() in Gutenberg to prevent duplication of logic.
		foreach ( $template_files as $template_file ) {
			// Avoid adding the same template if it's already in the array of $query_result.
			if (
				array_filter(
					$query_result,
					function( $query_result_template ) use ( $template_file ) {
						return $query_result_template->slug === $template_file->slug && $query_result_template->theme === $template_file->theme;
					}
				)
			) {
				continue;
			}

			// If the current $post_type is set (e.g. on an Edit Post screen), and isn't included in the available post_types
			// on the template file, then lets skip it so that it doesn't get added. This is typically used to hide templates
			// in the template dropdown on the Edit Post page.
			if ( $post_type && isset( $template_file->post_types ) && ! in_array( $post_type, $template_file->post_types, true ) ) {
				continue;
			}

			// It would be custom if the template was modified in the editor, so if it's not custom we can load it from
			// the filesystem.
			if ( 'custom' !== $template_file->source ) {
				$template = self::gutenberg_build_template_result_from_file( $template_file, $template_type );
			} else {
				$template_file->title = self::convert_slug_to_title( $template_file->slug );
				$query_result[]       = $template_file;
				continue;
			}

			$is_not_custom   = false === array_search( wp_get_theme()->get_stylesheet() . '//' . $template_file->slug, array_column( $query_result, 'id' ), true );
			$fits_slug_query = ! isset( $query['slug__in'] ) || in_array( $template_file->slug, $query['slug__in'], true );
			$fits_area_query = ! isset( $query['area'] ) || $template_file->area === $query['area'];
			$should_include  = $is_not_custom && $fits_slug_query && $fits_area_query;
			if ( $should_include ) {
				$query_result[] = $template;
			}
		}//geodir_error_log( $query_result, 'query_result', __FILE__, __LINE__ );

		$query_result = self::remove_theme_templates_with_custom_alternative( $query_result );

		return $query_result;
	}

	/**
	 * Get and build the block template objects from the block template files.
	 *
	 * @param array $slugs An array of slugs to retrieve templates for.
	 * @param array $template_type wp_template or wp_template_part.
	 *
	 * @return array
	 */
	public static function get_block_templates( $slugs = array(), $template_type = 'wp_template' ) {//geodir_error_log( $slugs, $template_type, __FILE__, __LINE__ );
		$templates_from_db  = self::get_block_templates_from_db( $slugs, $template_type );//geodir_error_log( $templates_from_db, 'templates_from_db', __FILE__, __LINE__ );
		$templates_from_gd = self::get_block_templates_from_gd( $slugs, $templates_from_db, $template_type );//geodir_error_log( $templates_from_gd, 'templates_from_gd', __FILE__, __LINE__ );

		return array_merge( $templates_from_db, $templates_from_gd );
	}

	/**
	 * Gets the templates saved in the database.
	 *
	 * @param array $slugs An array of slugs to retrieve templates for.
	 * @param array $template_type wp_template or wp_template_part.
	 *
	 * @return int[]|\WP_Post[] An array of found templates.
	 */
	public static function get_block_templates_from_db( $slugs = array(), $template_type = 'wp_template' ) {
		// This was the previously incorrect slug used to save DB templates against.
		$invalid_plugin_slug = 'geodirectory';

		$check_query_args = array(
			'post_type'      => $template_type,
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'wp_theme',
					'field'    => 'name',
					'terms'    => array( $invalid_plugin_slug, self::PLUGIN_SLUG, get_stylesheet() ),
				),
			),
		);

		if ( is_array( $slugs ) && count( $slugs ) > 0 ) {
			$check_query_args['post_name__in'] = $slugs;
		}

		$check_query         = new \WP_Query( $check_query_args );
		$saved_gd_templates = $check_query->posts;

		return array_map(
			function( $saved_gd_template ) {
				return self::gutenberg_build_template_result_from_post( $saved_gd_template );
			},
			$saved_gd_templates
		);
	}

	/**
	 * Gets the templates from the GD blocks directory, skipping those for which a template already exists
	 * in the theme directory.
	 *
	 * @param string[] $slugs An array of slugs to filter templates by. Templates whose slug does not match will not be returned.
	 * @param array    $already_found_templates Templates that have already been found, these are customised templates that are loaded from the database.
	 * @param string   $template_type wp_template or wp_template_part.
	 *
	 * @return array Templates from the GD blocks plugin directory.
	 */
	public static function get_block_templates_from_gd( $slugs, $already_found_templates, $template_type = 'wp_template' ) {
		$directory      = self::get_templates_directory( $template_type );
		$template_files = _get_block_templates_paths( $directory );
		$templates      = array();

		if ( 'wp_template_part' === $template_type ) {
			$dir_name = self::TEMPLATE_PARTS_DIR_NAME;
		} else {
			$dir_name = self::TEMPLATES_DIR_NAME;
		}

		foreach ( $template_files as $template_file ) {
			$template_slug = self::generate_template_slug_from_path( $template_file, $dir_name );

			// This template does not have a slug we're looking for. Skip it.
			if ( is_array( $slugs ) && count( $slugs ) > 0 && ! in_array( $template_slug, $slugs, true ) ) {
				continue;
			}

			// If the theme already has a template, or the template is already in the list (i.e. it came from the
			// database) then we should not overwrite it with the one from the filesystem.
			if (
				self::theme_has_template( $template_slug ) ||
				count(
					array_filter(
						$already_found_templates,
						function ( $template ) use ( $template_slug ) {
							$template_obj = (object) $template; //phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.Found
							return $template_obj->slug === $template_slug;
						}
					)
				) > 0 ) {
				continue;
			}

			// @TODO
			// If the theme has an archive-gd_place.html template, but not a taxonomy-gd_placecategory.html template let's use the themes archive-gd_place.html template.
			// If the theme has an archive-gd_place.html template, but not a taxonomy-gd_place_tags.html template let's use the themes archive-gd_place.html template.

			// At this point the template only exists in the Blocks filesystem and has not been saved in the DB,
			// or superseded by the theme.
			$templates[] = self::create_new_block_template_object( $template_file, $template_type, $template_slug );
		}

		return $templates;
	}

	/**
	 * Build a unified template object based a post Object.
	 *
	 * @param \WP_Post $post Template post.
	 *
	 * @return \WP_Block_Template|\WP_Error Template.
	 */
	public static function gutenberg_build_template_result_from_post( $post ) {
		$terms = get_the_terms( $post, 'wp_theme' );

		if ( is_wp_error( $terms ) ) {
			return $terms;
		}

		if ( ! $terms ) {
			return new \WP_Error( 'template_missing_theme', __( 'No theme is defined for this template.', 'geodirectory' ) );
		}

		$theme          = $terms[0]->name;
		$has_theme_file = true;

		$template                 = new \WP_Block_Template();
		$template->wp_id          = $post->ID;
		$template->id             = $theme . '//' . $post->post_name;
		$template->theme          = $theme;
		$template->content        = $post->post_content;
		$template->slug           = $post->post_name;
		$template->source         = 'custom';
		$template->type           = $post->post_type;
		$template->description    = $post->post_excerpt;
		$template->title          = $post->post_title;
		$template->status         = $post->post_status;
		$template->has_theme_file = $has_theme_file;
		$template->is_custom      = false;
		$template->post_types     = array();

		if ( 'wp_template_part' === $post->post_type ) {
			$type_terms = get_the_terms( $post, 'wp_template_part_area' );

			if ( ! is_wp_error( $type_terms ) && false !== $type_terms ) {
				$template->area = $type_terms[0]->name;
			}
		}

		if ( self::PLUGIN_SLUG === $theme ) {
			$template->origin = 'plugin';
		}

		return $template;
	}

	/**
	 * Converts template paths into a slug
	 *
	 * @param string $path The template's path.
	 * @param string $directory_name The template's directory name.
	 * @return string slug
	 */
	public static function generate_template_slug_from_path( $path, $directory_name = 'block-templates' ) {
		return substr(
			$path,
			strpos( $path, $directory_name . DIRECTORY_SEPARATOR ) + 1 + strlen( $directory_name ),
			-5
		);
	}

	/**
	 * Gets the directory where templates of a specific template type can be found.
	 *
	 * @param array $template_type wp_template or wp_template_part.
	 *
	 * @return string
	 */
	public static function get_templates_directory( $template_type = 'wp_template' ) {
		$templates_dir = untrailingslashit( geodir_get_templates_dir() ) . '/';

		if ( $design_style = geodir_design_style() ) {
			$templates_dir .= $design_style . '/';
		}

		if ( 'wp_template_part' === $template_type ) {
			return $templates_dir . self::TEMPLATE_PARTS_DIR_NAME;
		}

		return $templates_dir . self::TEMPLATES_DIR_NAME;
	}

	/**
	 * Checks whether a block template with that name exists in Woo Blocks
	 *
	 * @param string $template_name Template to check.
	 * @param array  $template_type wp_template or wp_template_part.
	 *
	 * @return boolean
	 */
	public static function block_template_is_available( $template_name, $template_type = 'wp_template' ) {
		if ( ! $template_name ) {
			return false;
		}
		$directory = self::get_templates_directory( $template_type ) . '/' . $template_name . '.html';

		return is_readable( $directory ) || self::get_block_templates( array( $template_name ), $template_type );
	}

	/**
	 * Renders the default block template from Woo Blocks if no theme templates exist.
	 */
	public static function render_block_template() {
		if ( is_embed() || ! self::supports_block_templates() ) {
			return;
		}

		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		$post_type = geodir_get_current_posttype();

		// @TODO
		if ( geodir_is_singular() && ! self::theme_has_template( 'single-' . $post_type ) && self::block_template_is_available( 'single-' . $post_type ) ) {
			add_filter( 'geodir_has_block_template', '__return_true', 10, 0 );
		} elseif ( ( geodir_is_taxonomy() && is_tax( $post_type . 'category' ) ) && ! self::theme_has_template( $post_type . 'category' ) && self::block_template_is_available( $post_type . 'category' ) ) {
			add_filter( 'geodir_has_block_template', '__return_true', 10, 0 );
		} elseif ( ( geodir_is_taxonomy() && is_tax( $post_type . '_tags' ) ) && ! self::theme_has_template( $post_type . '_tags' ) && self::block_template_is_available( $post_type . '_tags' ) ) {
			add_filter( 'geodir_has_block_template', '__return_true', 10, 0 );
		} elseif ( geodir_is_post_type_archive() && ! ( self::theme_has_template( 'archive-' . $post_type ) || self::theme_has_template( 'geodir-archive' ) ) && ( self::block_template_is_available( 'archive-' . $post_type ) || self::block_template_is_available( 'geodir-archive' ) ) ) {
			add_filter( 'geodir_has_block_template', '__return_true', 10, 0 );
		}
	}

	/**
	 * Check if the theme has a template. So we know if to load our own in or not.
	 *
	 * @param string $template_name name of the template file without .html extension e.g. 'single-product'.
	 * @return boolean
	 */
	public static function theme_has_template( $template_name ) {
		return is_readable( get_template_directory() . '/block-templates/' . $template_name . '.html' ) || is_readable( get_stylesheet_directory() . '/block-templates/' . $template_name . '.html' );
	}

	/**
	 * Check if the theme has a template. So we know if to load our own in or not.
	 *
	 * @param string $template_name name of the template file without .html extension e.g. 'single-product'.
	 * @return boolean
	 */
	public static function theme_has_template_part( $template_name ) {
		return is_readable( get_template_directory() . '/block-template-parts/' . $template_name . '.html' ) || is_readable( get_stylesheet_directory() . '/block-template-parts/' . $template_name . '.html' );
	}

	/**
	 * Build a new template object so that we can make GD Blocks default templates available in the current theme should they not have any.
	 *
	 * @param string $template_file Block template file path.
	 * @param string $template_type wp_template or wp_template_part.
	 * @param string $template_slug Block template slug e.g. single-product.
	 * @param bool   $template_is_from_theme If the block template file is being loaded from the current theme instead of GD Blocks.
	 *
	 * @return object Block template object.
	 */
	public static function create_new_block_template_object( $template_file, $template_type, $template_slug, $template_is_from_theme = false ) {
		$theme_name = wp_get_theme()->get( 'TextDomain' );

		$new_template_item = array(
			'slug'        => $template_slug,
			'id'          => $template_is_from_theme ? $theme_name . '//' . $template_slug : self::PLUGIN_SLUG . '//' . $template_slug,
			'path'        => $template_file,
			'type'        => $template_type,
			'theme'       => $template_is_from_theme ? $theme_name : self::PLUGIN_SLUG,
			'source'      => $template_is_from_theme ? 'theme' : 'plugin',
			'title'       => self::convert_slug_to_title( $template_slug ),
			'description' => self::get_block_description( $template_slug ),
			'post_types'  => array(), // Don't appear in any Edit Post template selector dropdown.
		);

		return (object) $new_template_item;
	}

	/**
	 * Converts template slugs into readable titles.
	 *
	 * @param string $template_slug The templates slug (e.g. single-product).
	 * @return string Human friendly title converted from the slug.
	 */
	public static function convert_slug_to_title( $template_slug ) {
		switch ( $template_slug ) {
			case 'geodir-archive':
				return __( 'GD Archive Page', 'geodirectory' );
			case 'archive-gd_place':
				return __( 'Place Archive Page', 'geodirectory' );
			default:
				// Replace all hyphens and underscores with spaces.
				return ucwords( preg_replace( '/[\-_]/', ' ', $template_slug ) );
		}
	}

	/**
	 * Get the block template description.
	 *
	 * @param string $template_slug The templates slug (e.g. single-product).
	 * @return string Block template description.
	 */
	public static function get_block_description( $template_slug ) {//geodir_error_log( $template_slug, 'template_slug', __FILE__, __LINE__ );
		switch ( $template_slug ) {
			case 'geodir-archive':
				return __( 'Displays post categories, tags, and other archives.', 'geodirectory' );
			case 'archive-gd_place':
				return __( 'Displays place categories, tags, and other archives.', 'geodirectory' );
			default:
				return '';
		}
	}

	/**
	 * Build a unified template object based on a theme file.
	 *
	 * @param array $template_file Theme file.
	 * @param array $template_type wp_template or wp_template_part.
	 *
	 * @return \WP_Block_Template Template.
	 */
	public static function gutenberg_build_template_result_from_file( $template_file, $template_type ) {
		$template_file = (object) $template_file;

		// If the theme has an archive-gd_place.html template but does not have post taxonomy templates
		// then we will load in the archive-gd_place.html template from the theme to use for post taxonomies on the frontend.
		$template_is_from_theme = 'theme' === $template_file->source;
		$theme_name             = wp_get_theme()->get( 'TextDomain' );

		$template_content  = file_get_contents( $template_file->path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$template          = new \WP_Block_Template();
		$template->id      = $template_is_from_theme ? $theme_name . '//' . $template_file->slug : self::PLUGIN_SLUG . '//' . $template_file->slug;
		$template->theme   = $template_is_from_theme ? $theme_name : self::PLUGIN_SLUG;
		$template->content = _inject_theme_attribute_in_block_template_content( $template_content );
		$template->source         = $template_file->source ? $template_file->source : 'plugin';
		$template->slug           = $template_file->slug;
		$template->type           = $template_type;
		$template->title          = ! empty( $template_file->title ) ? $template_file->title : self::convert_slug_to_title( $template_file->slug );
		$template->status         = 'publish';
		$template->has_theme_file = true;
		$template->origin         = $template_file->source;
		$template->is_custom      = false;
		$template->post_types     = array();
		if ( 'wp_template_part' === $template_type ) {
			$template->area = 'uncategorized'; // TODO
		}

		return $template;
	}

	/**
	 * Removes templates that were added to a theme's block-templates directory, but already had a customised version saved in the database.
	 *
	 * @param \WP_Block_Template[]|\stdClass[] $templates List of templates to run the filter on.
	 *
	 * @return array List of templates with duplicates removed. The customised alternative is preferred over the theme default.
	 */
	public static function remove_theme_templates_with_custom_alternative( $templates ) {
		// Get the slugs of all templates that have been customised and saved in the database.
		$customised_template_slugs = array_map(
			function( $template ) {
				return $template->slug;
			},
			array_values(
				array_filter(
					$templates,
					function( $template ) {
						// This template has been customised and saved as a post.
						return 'custom' === $template->source;
					}
				)
			)
		);

		// Remove theme (i.e. filesystem) templates that have the same slug as a customised one. We don't need to check
		// for `geodirectory` in $template->source here because geodirectory templates won't have been added to $templates
		// if a saved version was found in the db. This only affects saved templates that were saved BEFORE a theme
		// template with the same slug was added.
		return array_values(
			array_filter(
				$templates,
				function( $template ) use ( $customised_template_slugs ) {
					// This template has been customised and saved as a post, so return it.
					return ! ( 'theme' === $template->source && in_array( $template->slug, $customised_template_slugs, true ) );
				}
			)
		);
	}

	/**
	 * This function checks if there's a blocks template to return to pre_get_posts short-circuiting the query in Gutenberg.
	 *
	 * @param \WP_Block_Template|null $template Return a block template object to short-circuit the default query,
	 *                                               or null to allow WP to run its normal queries.
	 * @param string                  $id Template unique identifier (example: theme_slug//template_slug).
	 * @param array                   $template_type wp_template or wp_template_part.
	 *
	 * @return mixed|\WP_Block_Template|\WP_Error
	 */
	public static function maybe_return_blocks_template( $template, $id, $template_type ) {
		// 'get_block_template' was introduced in WP 5.9.
		if ( ! function_exists( 'get_block_template' ) ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}
		list( , $slug ) = $template_name_parts;

		// Remove the filter at this point because if we don't then this function will infinite loop.
		remove_filter( 'pre_get_block_file_template', array( __CLASS__, 'maybe_return_blocks_template' ), 10, 3 );

		// Check if the theme has a saved version of this template before falling back to the woo one. Please note how
		// the slug has not been modified at this point, we're still using the default one passed to this hook.
		$maybe_template = get_block_template( $id, $template_type );

		if ( null !== $maybe_template ) {
			add_filter( 'pre_get_block_file_template', array( __CLASS__, 'maybe_return_blocks_template' ), 10, 3 );
			return $maybe_template;
		}

		// Theme-based template didn't exist, try switching the theme to geodirectory and try again. This function has
		// been unhooked so won't run again.
		add_filter( 'get_block_file_template', array( __CLASS__, 'get_single_block_template' ), 10, 3 );

		$maybe_template = get_block_template( self::PLUGIN_SLUG . '//' . $slug, $template_type );

		// Re-hook this function, it was only unhooked to stop recursion.
		add_filter( 'pre_get_block_file_template', array( __CLASS__, 'maybe_return_blocks_template' ), 10, 3 );
		remove_filter( 'get_block_file_template', array( __CLASS__, 'get_single_block_template' ), 10, 3 );

		if ( null !== $maybe_template ) {
			return $maybe_template;
		}

		return $template;
	}

	/**
	 * Runs on the get_block_template hook. If a template is already found and passed to this function, then return it
	 * and don't run.
	 * If a template is *not* passed, try to look for one that matches the ID in the database, if that's not found defer
	 * to Blocks templates files. Priority goes: DB-Theme, DB-Blocks, Filesystem-Theme, Filesystem-Blocks.
	 *
	 * @param \WP_Block_Template $template The found block template.
	 * @param string             $id Template unique identifier (example: theme_slug//template_slug).
	 * @param array              $template_type wp_template or wp_template_part.
	 *
	 * @return mixed|null
	 */
	public static function get_single_block_template( $template, $id, $template_type ) {
		// The template was already found before the filter runs, just return it immediately.
		if ( null !== $template ) {
			return $template;
		}

		$template_name_parts = explode( '//', $id );
		if ( count( $template_name_parts ) < 2 ) {
			return $template;
		}
		list( , $slug ) = $template_name_parts;

		// If this blocks template doesn't exist then we should just skip the function and let Gutenberg handle it.
		if ( ! self::block_template_is_available( $slug, $template_type ) ) {
			return $template;
		}

		$available_templates = self::get_block_templates( array( $slug ), $template_type );

		return ( is_array( $available_templates ) && count( $available_templates ) > 0 ) ? self::gutenberg_build_template_result_from_file( $available_templates[0], $available_templates[0]->type ) : $template;
	}
}
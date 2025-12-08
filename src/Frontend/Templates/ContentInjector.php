<?php
/**
 * Content Injector
 *
 * Injects GeoDirectory content into theme templates for classic themes.
 *
 * @package GeoDirectory\Frontend\Templates
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\Templates;

/**
 * Handles content injection for GeoDirectory pages in classic themes.
 *
 * @since 3.0.0
 */
final class ContentInjector {

	/**
	 * Setup archive loop to display as a page.
	 *
	 * Swaps the main query posts with the archive page template,
	 * then restores the original posts for the loop.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 * @global array     $gd_temp_wp_query Temporary storage for original posts.
	 * @global bool      $gd_temp_wp_query_set Flag indicating temp query is set.
	 * @global bool      $gd_done_archive_loop Flag indicating archive loop has run.
	 *
	 * @param string $default_file Default template file.
	 * @param string $default_template Default template.
	 * @return void
	 */
	public function setup_archive_loop_as_page( string $default_file = '', string $default_template = '' ): void {
		global $wp_query, $gd_temp_wp_query, $gd_temp_wp_query_set, $gd_done_archive_loop;

		/**
		 * Allows bypassing the archive loop setup.
		 *
		 * @since 3.0.0
		 *
		 * @param bool   $bypass Whether to bypass.
		 * @param string $default_file Default file.
		 * @param string $default_template Default template.
		 */
		if ( apply_filters( 'geodir_bypass_setup_archive_loop_as_page', false, $default_file, $default_template ) ) {
			return;
		}

		// Store the original posts
		$gd_temp_wp_query = $wp_query->posts;
		$gd_temp_wp_query_set = true;

		// Get the archive page
		if ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'search' ) ) {
			$archive_page_id = function_exists( 'geodir_search_page_id' ) ? geodir_search_page_id() : 0;
		} else {
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$archive_page_id = function_exists( 'geodir_archive_page_id' ) ? geodir_archive_page_id( $post_type ) : 0;
		}

		$archive_page = ! empty( $archive_page_id ) ? get_post( $archive_page_id ) : null;

		if ( ! empty( $archive_page ) ) {
			$wp_query->posts = array( $archive_page );
		} else {
			$wp_query->posts = array();

			if ( function_exists( 'geodir_error_log' ) ) {
				$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
				geodir_error_log( 'Archive page template not found', $post_type . ':' . $archive_page_id, __FILE__, __LINE__ );
			}
		}

		$wp_query->post = $archive_page;

		// Fake having posts if there are none
		if ( empty( $gd_temp_wp_query ) ) {
			$wp_query->post_count = 1;
		}

		// Initialize the archive loop flag
		$gd_done_archive_loop = false;

		// Add filters for content injection
		add_filter( 'the_content', array( $this, 'setup_archive_page_content' ) );
		add_filter( 'the_excerpt', array( $this, 'setup_archive_page_content' ) );
	}

	/**
	 * Setup the GeoDirectory archive page content.
	 *
	 * Replaces the page content with the actual archive loop content.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 * @global \WP_Post  $post Current post object.
	 * @global bool      $gd_done_archive_loop Flag indicating archive loop has run.
	 * @global bool      $gd_skip_the_content Flag to prevent looping.
	 * @global bool      $gd_in_gd_loop Flag indicating inside GD loop.
	 * @global bool      $gd_archive_content_start Flag indicating archive content start.
	 * @global array     $gd_temp_wp_query Temporary storage for original posts.
	 * @global bool      $gd_is_comment_template_set Flag to prevent comments.
	 *
	 * @param string $content Original content.
	 * @return string Modified content.
	 */
	public function setup_archive_page_content( string $content ): string {
		global $wp_query, $post, $gd_done_archive_loop, $gd_skip_the_content, $gd_in_gd_loop;

		// Bail if we should skip content processing
		if ( $gd_skip_the_content || ! $this->is_archive_page_content() || $gd_in_gd_loop ) {
			return $content;
		}

		// Bail if not in the loop
		if ( ! in_the_loop() ) {
			if ( current_filter() === 'the_excerpt' && $gd_done_archive_loop ) {
				// Exception for excerpt filter that might be outside the loop
			} else {
				/**
				 * Allows bypassing setup archive page content.
				 *
				 * @since 3.0.0
				 *
				 * @param bool|string $bypass_content True to bypass.
				 * @param string      $content Loop content.
				 */
				$bypass_content = apply_filters( 'geodir_bypass_setup_archive_page_content', true, $content );

				if ( $bypass_content === true ) {
					return $content;
				} elseif ( $bypass_content !== false ) {
					return $content;
				}
			}
		}

		global $gd_archive_content_start, $gd_temp_wp_query, $gd_is_comment_template_set;

		// Backup the current post
		$gd_backup_post = $post;
		$gd_archive_content_start = true;

		// Remove filters to prevent loops
		remove_filter( 'the_content', array( $this, 'setup_archive_page_content' ) );
		remove_filter( 'the_excerpt', array( $this, 'setup_archive_page_content' ) );

		// Reset query for listings output
		if ( ! empty( $wp_query->posts ) ) {
			rewind_posts();
		}

		// Restore the proper loop content
		$wp_query->posts = $gd_temp_wp_query;

		// Prevent comments section
		$gd_is_comment_template_set = true;

		// Get the archive page content
		if ( function_exists( 'geodir_is_page' ) && geodir_is_page( 'search' ) ) {
			$archive_page_id = function_exists( 'geodir_search_page_id' ) ? geodir_search_page_id() : 0;
		} else {
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$archive_page_id = function_exists( 'geodir_archive_page_id' ) ? geodir_archive_page_id( $post_type ) : 0;
		}

		$content = get_post_field( 'post_content', $archive_page_id );

		/**
		 * Allows overwriting the archive template content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $overwrite_content Overwrite content.
		 * @param string $content Archive template content.
		 * @param int    $archive_page_id Archive template ID.
		 */
		$overwrite_content = apply_filters( 'geodir_overwrite_archive_template_content', '', $content, $archive_page_id );

		if ( $overwrite_content ) {
			$content = $overwrite_content;
		} else {
			// Use defaults if content is blank
			if ( $content === '' && class_exists( 'GeoDir_Defaults' ) ) {
				$content = \GeoDir_Defaults::page_archive_content();
			}

			// Process blocks
			if ( function_exists( 'do_blocks' ) ) {
				$content = do_blocks( $content );
			}

			// Process shortcodes
			$content = do_shortcode( $content );
		}

		// Restore filters
		add_filter( 'the_content', array( $this, 'setup_archive_page_content' ) );
		add_filter( 'the_excerpt', array( $this, 'setup_archive_page_content' ) );

		// Fake has_posts() to stop further loops
		$wp_query->current_post = $wp_query->post_count;

		// Restore original post
		if ( ! empty( $gd_backup_post ) ) {
			$post = $gd_backup_post;
		}

		// Mark archive loop as complete
		$gd_done_archive_loop = true;

		return $content;
	}

	/**
	 * Setup the GeoDirectory singular page content.
	 *
	 * Replaces the page content with the details page template content.
	 *
	 * @global \WP_Post  $post Current post object.
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @param string $content Original content.
	 * @return string Modified content.
	 */
	public function setup_singular_page( string $content ): string {
		global $post, $wp_query;

		// Bail if not the queried object
		if ( ! ( ! empty( $wp_query ) && ! empty( $post ) && ( $post->ID === get_queried_object_id() ) ) ) {
			return $content;
		}

		// Bail if password protected
		if ( post_password_required() ) {
			return $content;
		}

		/**
		 * Allows bypassing singular page setup.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $bypass Whether to bypass.
		 */
		if ( apply_filters( 'geodir_bypass_setup_singular_page', false ) ) {
			return $content;
		}

		// Remove filter to prevent loops
		remove_filter( 'the_content', array( $this, 'setup_singular_page' ) );

		if ( in_the_loop() ) {
			// Get the details page content
			$post_type = function_exists( 'geodir_get_current_posttype' ) ? geodir_get_current_posttype() : '';
			$page_id = function_exists( 'geodir_details_page_id' ) ? geodir_details_page_id( $post_type ) : 0;
			$content = get_post_field( 'post_content', $page_id );

			/**
			 * Allows overwriting the single template content.
			 *
			 * @since 3.0.0
			 *
			 * @param string $overwrite_content Overwrite content.
			 * @param string $content Single template content.
			 * @param int    $page_id Single template ID.
			 */
			$overwrite_content = apply_filters( 'geodir_overwrite_single_template_content', '', $content, $page_id );

			if ( $overwrite_content ) {
				$content = $overwrite_content;
			} else {
				// Use defaults if content is blank
				if ( $content === '' && class_exists( 'GeoDir_Defaults' ) ) {
					$content = \GeoDir_Defaults::page_details_content();
				}

				// Process blocks
				if ( function_exists( 'do_blocks' ) ) {
					$content = do_blocks( $content );
				}

				// Process shortcodes
				$content = do_shortcode( $content );
			}
		}

		// Restore filter
		add_filter( 'the_content', array( $this, 'setup_singular_page' ) );

		return $content;
	}

	/**
	 * Check if we are processing archive page content.
	 *
	 * @global \WP_Post  $post Current post object.
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @return bool True if archive page content.
	 */
	private function is_archive_page_content(): bool {
		global $post, $wp_query;

		if ( empty( $post ) || $post->post_type !== 'page' || empty( $wp_query ) ) {
			return false;
		}

		if ( empty( $_REQUEST['geodir_search'] ) && empty( get_queried_object() ) ) {
			return false;
		}

		$queried_object = get_queried_object();

		// Check for term archive
		if ( ! empty( $queried_object->term_id ) ) {
			return $this->is_archive_page_id( $post->ID );
		}

		// Check for CPT archive
		if ( ! empty( $queried_object->has_archive ) ) {
			return $this->is_archive_page_id( $post->ID );
		}

		// Check for search page
		if ( ! empty( $queried_object->ID )
			&& function_exists( 'geodir_search_page_id' )
			&& $queried_object->ID === geodir_search_page_id()
		) {
			return true;
		}

		return false;
	}

	/**
	 * Check if a page ID is an archive page.
	 *
	 * @param int $id Page ID.
	 * @return bool True if archive page.
	 */
	private function is_archive_page_id( int $id ): bool {
		// Check default archive page
		$page_archive_id = function_exists( 'geodir_get_option' ) ? geodir_get_option( 'page_archive' ) : 0;

		if ( $id === $page_archive_id ) {
			return true;
		}

		// Check CPT-specific archive page
		if ( function_exists( 'geodir_is_cpt_template_page' ) && geodir_is_cpt_template_page( $id ) ) {
			return true;
		}

		/**
		 * Filters whether a page ID is an archive page.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $is_archive True if archive page.
		 * @param int  $id Page ID.
		 */
		return apply_filters( 'geodir_is_archive_page_id', false, $id );
	}
}

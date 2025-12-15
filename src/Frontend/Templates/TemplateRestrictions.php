<?php
/**
 * Template Restrictions
 *
 * Handles restrictions and special handling for GeoDirectory template pages.
 *
 * @package GeoDirectory\Frontend\Templates
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

declare( strict_types=1 );

namespace AyeCode\GeoDirectory\Frontend\Templates;

use AyeCode\GeoDirectory\Core\Services\PageDefaults;

/**
 * Manages template page restrictions and special behaviors.
 *
 * @since 3.0.0
 */
final class TemplateRestrictions {

	/**
	 * Page defaults service instance.
	 *
	 * @var PageDefaults
	 */
	private PageDefaults $page_defaults;

	/**
	 * Constructor.
	 *
	 * @param PageDefaults $page_defaults Page defaults service.
	 */
	public function __construct( PageDefaults $page_defaults ) {
		$this->page_defaults = $page_defaults;
	}

	/**
	 * Disable GeoDirectory page templates from direct frontend viewing.
	 *
	 * Redirects non-admin users away from template pages that should only
	 * be accessed through GeoDirectory's template system.
	 *
	 * @global \WP_Post $post Current post object.
	 *
	 * @return void
	 */
	public function disable_page_templates_frontend(): void {
		global $post;

		// Only restrict for non-administrators
		if ( ! isset( $post->ID ) || current_user_can( 'administrator' ) ) {
			return;
		}

		// Allow add listing page
		if ( function_exists( 'geodir_is_cpt_template_page' ) && geodir_is_cpt_template_page( $post->ID, 'add' ) ) {
			return;
		}

		// Check if this is a restricted template page
		if ( ! function_exists( 'geodir_get_option' ) ) {
			return;
		}

		$restricted = false;

		// Check if it's the details template page
		if ( $post->ID === geodir_get_option( 'page_details' ) ) {
			$restricted = true;
		}

		// Check if it's the archive template page
		if ( $post->ID === geodir_get_option( 'page_archive' ) ) {
			$restricted = true;
		}

		// Check if it's the archive item template page
		if ( $post->ID === geodir_get_option( 'page_archive_item' ) ) {
			$restricted = true;
		}

		// Check if it's a CPT template page
		if ( function_exists( 'geodir_is_cpt_template_page' ) && geodir_is_cpt_template_page( $post->ID ) ) {
			$restricted = true;
		}

		/**
		 * Filters whether a page should be restricted from direct access.
		 *
		 * @since 3.0.0
		 *
		 * @param bool $restricted Whether page is restricted.
		 * @param int  $post_id Post ID.
		 */
		if ( apply_filters( 'geodir_restrict_template_page', $restricted, $post->ID ) ) {
			wp_redirect( home_url(), 301 );
			exit;
		}
	}

	/**
	 * Disable theme featured image output if configured.
	 *
	 * Prevents the theme from displaying its own featured image on
	 * GeoDirectory detail pages when the setting is enabled.
	 *
	 * @return void
	 */
	public function maybe_disable_theme_featured_output(): void {
		if ( ! function_exists( 'geodir_is_singular' ) || ! function_exists( 'geodir_get_option' ) ) {
			return;
		}

		if ( geodir_is_singular() && geodir_get_option( 'details_disable_featured', false ) ) {
			add_filter( 'get_post_metadata', array( $this, 'filter_thumbnail_id' ), 10, 4 );
		}
	}

	/**
	 * Filter the post thumbnail ID metadata.
	 *
	 * Returns false for the thumbnail ID to prevent theme from displaying
	 * the featured image.
	 *
	 * @global \WP_Query $wp_query WordPress query object.
	 *
	 * @param mixed  $metadata Metadata value.
	 * @param int    $object_id Object ID.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Whether to return single value.
	 * @return mixed Modified metadata.
	 */
	public function filter_thumbnail_id( $metadata, int $object_id, string $meta_key, bool $single ) {
		global $wp_query;

		// Only filter _thumbnail_id for the current post
		if ( $meta_key === '_thumbnail_id' && ! empty( $wp_query ) && $object_id === get_queried_object_id() ) {
			$metadata = false;
		}

		// Only need to fire once
		remove_filter( 'get_post_metadata', array( $this, 'filter_thumbnail_id' ), 10 );

		return $metadata;
	}

	/**
	 * Set flag to clear list view storage when archive page is updated.
	 *
	 * Used to ensure admin sees changes immediately in localStorage.
	 *
	 * @param int      $post_ID Post ID.
	 * @param \WP_Post $post_after Post object after update.
	 * @param \WP_Post $post_before Post object before update.
	 * @return void
	 */
	public function set_clear_list_view_storage( int $post_ID, \WP_Post $post_after, \WP_Post $post_before ): void {
		// Only for pages with gd_loop shortcode
		if ( $post_after->post_type !== 'page' || ! has_shortcode( $post_after->post_content, 'gd_loop' ) ) {
			return;
		}

		if ( function_exists( 'geodir_update_option' ) ) {
			geodir_update_option( 'clear_list_view_storage', true );
		}
	}

	/**
	 * Get archive item template content.
	 *
	 * Returns the content to use for individual items in archive listings.
	 *
	 * @global array $geodir_item_tmpl Template configuration for items.
	 *
	 * @param string $post_type Post type.
	 * @param int    $page_id Page ID (optional).
	 * @return string Template content.
	 */
	public function get_archive_item_template_content( string $post_type = '', int $page_id = 0 ): string {
		global $geodir_item_tmpl;

		$content = '';
		$type = 'page_id';

		// Determine the archive item page ID
		if ( $page_id > 0 ) {
			$archive_page_id = $page_id;
		} elseif ( ! empty( $geodir_item_tmpl['type'] ) && $geodir_item_tmpl['type'] === 'page' && ! empty( $geodir_item_tmpl['id'] ) ) {
			$archive_page_id = (int) $geodir_item_tmpl['id'];
		} elseif ( ! empty( $geodir_item_tmpl['type'] )
			&& $geodir_item_tmpl['type'] === 'template_part'
			&& ! empty( $geodir_item_tmpl['content'] )
			&& function_exists( 'geodir_is_block_theme' )
			&& geodir_is_block_theme()
		) {
			$content = $geodir_item_tmpl['content'];
			$archive_page_id = esc_attr( $geodir_item_tmpl['id'] );
			$type = 'template_part';
		} else {
			$archive_page_id = function_exists( 'geodir_archive_item_page_id' ) ? (int) geodir_archive_item_page_id( $post_type ) : 0;
		}

		// Get content if not already set
		if ( ! $content ) {
			$content = get_post_field( 'post_content', $archive_page_id );
		}

		/**
		 * Allows bypassing archive item template content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $bypass_content Bypass content.
		 * @param string $content Template content.
		 * @param int    $archive_page_id Page ID.
		 * @param string $type Template type.
		 */
		$bypass_content = apply_filters( 'geodir_bypass_archive_item_template_content', '', $content, $archive_page_id, $type );

		if ( $bypass_content ) {
			return $bypass_content;
		}

		// Use defaults if content is blank
		if ( $content === '' ) {
			$content = $this->page_defaults->get_content( 'archive_item' );
		}

		// Process blocks
		if ( function_exists( 'do_blocks' ) ) {
			$content = do_blocks( $content );
		}

		// Process shortcodes
		$content = do_shortcode( $content );

		return $content;
	}

	/**
	 * Get map popup template content.
	 *
	 * Returns the content to use for map popups.
	 *
	 * @return string Popup content.
	 */
	public function get_map_popup_template_content(): string {
		$design_style = function_exists( 'geodir_design_style' ) ? geodir_design_style() : '';
		$template = $design_style ? $design_style . '/map/map-popup.php' : 'map-popup.php';

		$content = function_exists( 'geodir_get_template_html' ) ? geodir_get_template_html( $template ) : '';

		if ( ! empty( $content ) ) {
			// Process blocks
			if ( function_exists( 'do_blocks' ) ) {
				$content = do_blocks( $content );
			}

			// Process shortcodes
			$content = do_shortcode( $content );
		}

		/**
		 * Filters the map popup template content.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Popup content.
		 */
		return apply_filters( 'geodir_map_popup_template_content', $content );
	}
}

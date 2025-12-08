<?php
/**
 * SEO Meta Title and Description Manager
 *
 * Manages page titles, meta titles, and meta descriptions for GeoDirectory pages.
 *
 * @package GeoDirectory\Core\Seo
 * @since 3.0.0
 */

declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Core\Seo;

use AyeCode\GeoDirectory\Core\Services\Settings;

/**
 * Handles SEO meta information for GeoDirectory pages.
 */
final class MetaManager {
	/**
	 * The page title (used in the_title filter).
	 *
	 * @var string
	 */
	public string $title = '';

	/**
	 * The meta title (used in document title).
	 *
	 * @var string
	 */
	public string $meta_title = '';

	/**
	 * The meta description.
	 *
	 * @var string
	 */
	public string $meta_description = '';

	/**
	 * The current GeoDirectory page type.
	 *
	 * @var string
	 */
	public string $gd_page = '';

	/**
	 * Flag to track if we're rendering a menu.
	 *
	 * @var bool
	 */
	public static bool $doing_menu = false;

	/**
	 * Constructor.
	 *
	 * @param Settings $settings The settings service.
	 * @param VariableReplacer $replacer The variable replacer service.
	 */
	public function __construct(
		private Settings $settings,
		private VariableReplacer $replacer
	) {}

	/**
	 * Sets the meta information based on the current page type.
	 *
	 * @return string The page title.
	 */
	public function set_meta(): string {
		$this->gd_page = geodir_get_page_type();

		// Post type page
		if ( geodir_is_page( 'pt' ) ) {
			$this->set_post_type_meta();
		}
		// Archive page
		elseif ( geodir_is_page( 'archive' ) ) {
			$this->set_archive_meta();
		}
		// Single listing page
		elseif ( geodir_is_page( 'single' ) ) {
			$this->set_single_meta();
		}
		// Location page
		elseif ( geodir_is_page( 'location' ) ) {
			$this->set_location_meta();
		}
		// Search page
		elseif ( geodir_is_page( 'search' ) ) {
			$this->set_search_meta();
		}
		// Add listing page
		elseif ( geodir_is_page( 'add-listing' ) ) {
			$this->set_add_listing_meta();
		}

		// Replace variables in all meta strings
		if ( $this->title ) {
			$this->title = $this->replacer->replace( $this->title );
		}
		if ( $this->meta_title ) {
			$this->meta_title = $this->replacer->replace( $this->meta_title );
		}
		if ( $this->meta_description ) {
			$this->meta_description = $this->replacer->replace( $this->meta_description );
		}

		return $this->title;
	}

	/**
	 * Sets meta for post type archive pages.
	 *
	 * @return void
	 */
	private function set_post_type_meta(): void {
		$post_type = geodir_get_current_posttype();
		$post_type_info = get_post_type_object( $post_type );

		// Check for per-post-type SEO settings
		if ( isset( $post_type_info->seo['title'] ) && ! empty( $post_type_info->seo['title'] ) ) {
			$this->title = __( $post_type_info->seo['title'], 'geodirectory' );
		} else {
			$this->title = $this->settings->get( 'seo_cpt_title', GeoDir_Defaults::seo_cpt_title() );
		}

		if ( isset( $post_type_info->seo['meta_title'] ) && ! empty( $post_type_info->seo['meta_title'] ) ) {
			$this->meta_title = __( $post_type_info->seo['meta_title'], 'geodirectory' );
		} else {
			$this->meta_title = $this->settings->get( 'seo_cpt_meta_title', GeoDir_Defaults::seo_cpt_meta_title() );
		}

		if ( isset( $post_type_info->seo['meta_description'] ) && ! empty( $post_type_info->seo['meta_description'] ) ) {
			$this->meta_description = __( $post_type_info->seo['meta_description'], 'geodirectory' );
		} else {
			$this->meta_description = $this->settings->get( 'seo_cpt_meta_description', GeoDir_Defaults::seo_cpt_meta_description() );
		}
	}

	/**
	 * Sets meta for archive (category/tag) pages.
	 *
	 * @return void
	 */
	private function set_archive_meta(): void {
		$queried_object = get_queried_object();

		// Category archives
		if ( isset( $queried_object->taxonomy ) && geodir_taxonomy_type( $queried_object->taxonomy ) === 'category' && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
			$this->title = $this->settings->get( 'seo_cat_archive_title', GeoDir_Defaults::seo_cat_archive_title() );
			$this->meta_title = $this->settings->get( 'seo_cat_archive_meta_title', GeoDir_Defaults::seo_cat_archive_meta_title() );
			$this->meta_description = $this->settings->get( 'seo_cat_archive_meta_description', GeoDir_Defaults::seo_cat_archive_meta_description() );
		}
		// Tag archives
		elseif ( isset( $queried_object->taxonomy ) && geodir_taxonomy_type( $queried_object->taxonomy ) === 'tag' && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
			$this->title = $this->settings->get( 'seo_tag_archive_title', GeoDir_Defaults::seo_tag_archive_title() );
			$this->meta_title = $this->settings->get( 'seo_tag_archive_meta_title', GeoDir_Defaults::seo_tag_archive_meta_title() );
			$this->meta_description = $this->settings->get( 'seo_tag_archive_meta_description', GeoDir_Defaults::seo_tag_archive_meta_description() );
		}
	}

	/**
	 * Sets meta for single listing pages.
	 *
	 * @return void
	 */
	private function set_single_meta(): void {
		$this->title = $this->settings->get( 'seo_single_title', GeoDir_Defaults::seo_single_title() );
		$this->meta_title = $this->settings->get( 'seo_single_meta_title', GeoDir_Defaults::seo_single_meta_title() );
		$this->meta_description = $this->settings->get( 'seo_single_meta_description', GeoDir_Defaults::seo_single_meta_description() );
	}

	/**
	 * Sets meta for location pages.
	 *
	 * @return void
	 */
	private function set_location_meta(): void {
		$this->title = $this->settings->get( 'seo_location_title', GeoDir_Defaults::seo_location_title() );
		$this->meta_title = $this->settings->get( 'seo_location_meta_title', GeoDir_Defaults::seo_location_meta_title() );
		$this->meta_description = $this->settings->get( 'seo_location_meta_description', GeoDir_Defaults::seo_location_meta_description() );
	}

	/**
	 * Sets meta for search pages.
	 *
	 * @return void
	 */
	private function set_search_meta(): void {
		$this->title = $this->settings->get( 'seo_search_title', GeoDir_Defaults::seo_search_title() );
		$this->meta_title = $this->settings->get( 'seo_search_meta_title', GeoDir_Defaults::seo_search_meta_title() );
		$this->meta_description = $this->settings->get( 'seo_search_meta_description', GeoDir_Defaults::seo_search_meta_description() );
	}

	/**
	 * Sets meta for add listing pages.
	 *
	 * @return void
	 */
	private function set_add_listing_meta(): void {
		// Different title for edit vs add
		if ( ! empty( $_REQUEST['pid'] ) ) {
			$this->title = $this->settings->get( 'seo_add_listing_title_edit', GeoDir_Defaults::seo_add_listing_title_edit() );
		} else {
			$this->title = $this->settings->get( 'seo_add_listing_title', GeoDir_Defaults::seo_add_listing_title() );
		}

		$this->meta_title = $this->settings->get( 'seo_add_listing_meta_title', GeoDir_Defaults::seo_add_listing_meta_title() );
		$this->meta_description = $this->settings->get( 'seo_add_listing_meta_description', GeoDir_Defaults::seo_add_listing_meta_description() );
	}

	/**
	 * Outputs the page title for the_title filter.
	 *
	 * @param string $title The original title.
	 * @param int $id The post ID.
	 * @return string The filtered title.
	 */
	public function output_title( string $title = '', int $id = 0 ): string {
		global $wp_query, $gdecs_render_loop, $geodir_query_object_id;

		$ajax_search = ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] === 'geodir_ajax_search' && ! empty( $_REQUEST['geodir_search'] ) && wp_doing_ajax();

		if ( ! empty( $geodir_query_object_id ) ) {
			$query_object_id = $geodir_query_object_id;
		} else {
			// In some themes the object id is missing so we fix it
			if ( $id && isset( $wp_query->post->ID ) && geodir_is_geodir_page_id( $id ) ) {
				$query_object_id = $wp_query->post->ID;
			} elseif ( ! is_null( $wp_query ) ) {
				$query_object_id = get_queried_object_id();
			} else {
				$query_object_id = '';
			}
		}

		$normalize = false;

		if ( $this->title && empty( $id ) && ! self::$doing_menu ) {
			$normalize = true;
			$title = $this->title;
		} elseif ( $this->title && ! empty( $id ) && $query_object_id == $id && ! self::$doing_menu && ( ! $gdecs_render_loop || ( $ajax_search && get_post_type( $id ) === 'page' ) ) ) {
			$normalize = true;
			$title = $this->title;
			/**
			 * Filter page title to replace variables.
			 *
			 * @param string $title The page title including variables.
			 * @param string $id The page id.
			 */
			$title = apply_filters( 'geodir_seo_title', __( $title, 'geodirectory' ), $title, $id );
		}

		// Strip duplicate whitespace
		if ( $title !== '' && $normalize ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Outputs the meta title for wp_title and pre_get_document_title filters.
	 *
	 * @param string $title The original title.
	 * @param string $sep The separator.
	 * @return string The filtered meta title.
	 */
	public function output_meta_title( string $title = '', string $sep = '' ): string {
		if ( $this->meta_title ) {
			$title = $this->meta_title;
		}

		/**
		 * Filter page meta title to replace variables.
		 *
		 * @since 1.5.4
		 * @param string $title The page title including variables.
		 * @param string $gd_page The GeoDirectory page type if any.
		 * @param string $sep The title separator symbol.
		 */
		$title = apply_filters( 'geodir_seo_meta_title', __( $title, 'geodirectory' ), $this->gd_page, $sep );

		// Strip duplicate whitespace
		if ( $title !== '' ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Gets the meta description.
	 *
	 * @param string $description The original description.
	 * @return string The filtered meta description.
	 */
	public function get_description( string $description = '' ): string {
		$meta_description = $this->meta_description;

		if ( ! empty( $meta_description ) ) {
			$description = $meta_description;
		}

		// Escape
		if ( ! empty( $description ) ) {
			$description = esc_attr( $description );
		}

		/**
		 * Filter SEO meta description.
		 *
		 * @since 1.0.0
		 *
		 * @param string $description Meta description.
		 */
		$description = apply_filters( 'geodir_seo_meta_description', $description, $meta_description );

		// Strip duplicate whitespace
		if ( $description !== '' ) {
			$description = normalize_whitespace( $description );
		}

		return $description;
	}

	/**
	 * Gets the meta title.
	 *
	 * @param string $title The original title.
	 * @return string The filtered meta title.
	 */
	public function get_title( string $title = '' ): string {
		$meta_title = $this->meta_title;

		if ( ! empty( $meta_title ) ) {
			$title = $meta_title;
		}

		// Escape
		if ( ! empty( $title ) ) {
			$title = esc_attr( $title );
		}

		/**
		 * Filter SEO meta title.
		 *
		 * @param string $title Meta title.
		 */
		$title = apply_filters( 'geodir_seo_meta_title', $title, $meta_title );

		// Strip duplicate whitespace
		if ( $title !== '' ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Outputs the meta description tag.
	 *
	 * @return void
	 */
	public function output_description(): void {
		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		$description = $this->get_description();

		if ( $description !== '' ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '" />';
		}
	}

	/**
	 * Sets the global var when a menu is being output.
	 *
	 * @param string|null $menu Menu.
	 * @param object $args Menu args.
	 * @return string|null $menu
	 */
	public static function set_menu_global( $menu, $args ) {
		if ( null === $menu ) {
			if ( empty( $args->menu ) && ! empty( $args->theme_location ) && ( $locations = get_nav_menu_locations() ) && ! isset( $locations[ $args->theme_location ] ) ) {
				// Don't set $doing_menu for incorrect menu
			} else {
				self::$doing_menu = true;
			}
		}

		return $menu;
	}

	/**
	 * Unsets the global var when a menu has finished being output.
	 *
	 * @param string $menu Menu.
	 * @return string $menu
	 */
	public static function unset_menu_global( $menu ) {
		self::$doing_menu = false;
		return $menu;
	}
}

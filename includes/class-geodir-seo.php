<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GeoDirectory GeoDir_SEO.
 *
 * Adds meta info and titles to GD pages for SEO.
 *
 * @class    GeoDir_SEO
 * @package  GeoDirectory/Classes
 * @category Class
 * @author   AyeCode
 */
class GeoDir_SEO {

	// Some global values.
	public static $title = '';
	public static $meta_title = '';
	public static $meta_description = '';

	public static $gd_page = '';
	public static $doing_menu = false;

	/**
	 * Initiate the class.
	 *
	 * @since 2.0.0
	 */
	public static function init() {
		add_action( 'init',array(__CLASS__,'maybe_run') );

		// Maybe noindex empty archive pages.
		add_action('wp_head', array(__CLASS__,'maybe_noindex_empty_archives'));
		add_filter( 'wpseo_frontend_presentation', array( __CLASS__, 'wpseo_frontend_presentation' ), 11, 2 );
		add_filter( 'wpseo_breadcrumb_links', array( __CLASS__, 'breadcrumb_links' ) ); // Since Yoast v16.2 causes error in breadcrumb schema.
		add_filter( 'wpseo_robots_array', array( __CLASS__, 'wpseo_robots_array' ), 20, 2 );
		add_filter( 'get_post_metadata', array( __CLASS__, 'filter_post_metadata' ), 99, 5 );
		add_filter( 'rank_math/frontend/breadcrumb/settings', array( __CLASS__, 'rank_math_frontend_breadcrumb_settings' ), 20, 1 );
		add_filter( 'rank_math/frontend/breadcrumb/items', array( __CLASS__, 'rank_breadcrumb_links' ), 10, 2 );
		add_filter( 'rank_math/frontend/breadcrumb/main_term', array( __CLASS__, 'rank_math_frontend_breadcrumb_main_term' ), 20, 2 );

		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'wpseo_exclude_from_sitemap_by_post_ids' ), 20, 1 );
		if ( ! is_admin() ) {
			add_filter( 'page_link', array( __CLASS__, 'page_link' ), 10, 3 );
		}

		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 9999 );

		// WP Sitemaps
		add_filter( 'wp_sitemaps_posts_query_args', array( __CLASS__, 'wp_sitemaps_exclude_post_ids' ), 20, 2 );

		add_action('wp_head', function() {
			if ( geodir_is_page( 'search' ) ) {
				add_filter( 'wpseo_frontend_page_type_simple_page_id', array( __CLASS__ , 'wpseo_frontend_page_type_simple_page_id' ), 10, 1 );
			}
		}, 0 );
		add_action('wp_head', function() {
			if ( geodir_is_page( 'search' ) ) {
				remove_filter( 'wpseo_frontend_page_type_simple_page_id', array( __CLASS__ , 'wpseo_frontend_page_type_simple_page_id' ), 10, 1 );
			}
		}, 99 );

		// The SEO Framework
		if ( defined( 'THE_SEO_FRAMEWORK_VERSION' ) ) {
			add_filter( 'the_seo_framework_sitemap_hpt_query_args', array( __CLASS__ , 'the_seo_framework_sitemap_exclude_posts' ), 20, 1 );
			add_filter( 'the_seo_framework_sitemap_nhpt_query_args', array( __CLASS__ , 'the_seo_framework_sitemap_exclude_posts' ), 20, 1 );
		}

		// SEOPress
		add_filter( 'seopress_pro_breadcrumbs_crumbs', array( __CLASS__, 'seopress_pro_breadcrumbs_crumbs' ), 20, 1 );
		add_filter( 'seopress_titles_title', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_titles_desc', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_social_og_title', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_social_og_desc', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_social_twitter_card_title', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_social_twitter_card_summary', array( __CLASS__ , 'replace_variables' ), 100, 1 );
		add_filter( 'seopress_social_twitter_card_desc', array( __CLASS__ , 'replace_variables' ), 100, 1 );
	}

	/**
	 * If set to do so, add noindex tag to GD archive pages.
	 */
	public static function maybe_noindex_empty_archives(){
		if( geodir_get_option('noindex_archives') && ( geodir_is_page('archive') || geodir_is_page('post_type') ) ){
			if(geodir_is_empty_archive() ){
				echo '<meta name="robots" content="noindex">';
			}
		}
	}

	public static function yoast_enabled() {
		global $geodir_options;

		return defined( 'WPSEO_VERSION' ) && ( ! isset( $geodir_options['wpseo_disable'] ) || ( isset( $geodir_options['wpseo_disable'] ) && $geodir_options['wpseo_disable'] == '0' ) ) ? true : false;
	}

	public static function rank_math_enabled() {
		global $geodir_options;

		return defined( 'RANK_MATH_VERSION' ) && ( ! isset( $geodir_options['rank_math_disable'] ) || ( isset( $geodir_options['rank_math_disable'] ) && $geodir_options['rank_math_disable'] == '0' ) ) ? true : false;
	}

	/**
	 * Check SEOPress options enabled or not.
	 *
	 * @since 2.2.7
	 *
	 * @return bool True if SEOPress is enabled else False.
	 */
	public static function seopress_enabled(){
		global $geodir_options;

		return function_exists( 'seopress_activation' ) && ( ! isset( $geodir_options['seopress_disable'] ) || ( isset( $geodir_options['seopress_disable'] ) && $geodir_options['seopress_disable'] == '0' ) ) ? true : false;
	}

	/**
	 * Replace GD SEO title & metas variables with values.
	 *
	 * @since 2.2.23
	 *
	 * @param string $string String to replace variables.
	 * @return string $string String after GD SEO variables replaced.
	 */
	public static function replace_variables( $string ) {
		if ( ! empty( $string ) && is_scalar( $string ) && strpos( $string, '%%' ) !== false && geodir_is_geodir_page() ) {
			$string = self::replace_variable( $string, self::$gd_page );
		}

		return $string;
	}

	public static function maybe_run() {
		$ajax_search = ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_ajax_search' && ! empty( $_REQUEST['geodir_search'] ) && wp_doing_ajax() ? true : false;

		// Bail if we have a SEO plugin installed.
		if (
			self::yoast_enabled() // Don't run if active and not set to be disabled
			|| self::rank_math_enabled() // Don't run if active and not set to be disabled
			|| self::seopress_enabled() // SEOPress
			|| class_exists( 'All_in_One_SEO_Pack' )  // don't run if active
			|| ( is_admin() && ! $ajax_search ) // No need to run in wp-admin
		) {
			// Even if disabled we still need to replace title vars
			if ( ! is_admin() || $ajax_search ) {
				// Set a global so we don't change the menu items titles
				add_filter( 'pre_wp_nav_menu', array( __CLASS__, 'set_menu_global' ), 10, 2 );
				add_filter( 'wp_nav_menu', array( __CLASS__, 'unset_menu_global' ) );

				// YOOtheme renders own menuwalker.
				if ( class_exists( 'YOOtheme\\Theme' ) ) {
					add_filter( 'wp_nav_menu_items',array( __CLASS__, 'unset_menu_global' ), 999, 1 );
				}

				if ( self::has_yoast_14() ) {
					add_filter( 'wpseo_twitter_title', array( __CLASS__, 'wpseo_twitter_title' ), 10, 2 );
					add_filter( 'wpseo_title', array( __CLASS__, 'wpseo_title' ), 20, 2 );
					add_filter( 'wpseo_metadesc', array( __CLASS__, 'wpseo_metadesc' ), 20, 2 );

					add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'wpseo_opengraph_url' ), 20, 2 );
					add_filter( 'wpseo_add_opengraph_additional_images', array( __CLASS__, 'wpseo_opengraph_image' ), 20, 1 );
					add_filter( 'wpseo_canonical', array( __CLASS__, 'wpseo_canonical' ), 20, 2 );
					add_filter( 'wpseo_adjacent_rel_url', array( __CLASS__, 'wpseo_adjacent_rel_url' ), 20, 3 );

					add_action( 'wpseo_register_extra_replacements', array( __CLASS__, 'wpseo_register_extra_replacements' ), 20 );
				}

				// page title
				add_filter('the_title',array(__CLASS__,'output_title'),10,2);
				add_filter('get_the_archive_title',array(__CLASS__,'output_title'),10);

				// setup vars
				add_action( 'pre_get_document_title', array( __CLASS__, 'set_meta' ), 9 );
			}

			if ( defined( 'RANK_MATH_VERSION' ) ) {
				add_action( 'rank_math/vars/register_extra_replacements', array( __CLASS__, 'rank_math_vars_register_extra_replacements' ), 20 );
				add_filter( 'rank_math/frontend/description', array( __CLASS__, 'rank_math_frontend_description_replace_vars' ), 9, 1 );
			}

			return;
		}

		// Set a global so we don't change the menu items titles
		add_filter( 'pre_wp_nav_menu', array( __CLASS__, 'set_menu_global' ), 10, 2 );
		add_filter( 'wp_nav_menu', array( __CLASS__, 'unset_menu_global' ) );

		// YOOtheme renders own menuwalker.
		if ( class_exists( 'YOOtheme\\Theme' ) ) {
			add_filter( 'wp_nav_menu_items', array( __CLASS__, 'unset_menu_global' ), 999, 1 );
		}

		// Meta title
		add_filter( 'wp_title', array( __CLASS__, 'output_meta_title' ), 1000 , 2 );
		add_filter( 'pre_get_document_title', array( __CLASS__, 'output_meta_title' ), 1000 );

		// page title
		add_filter( 'the_title', array( __CLASS__, 'output_title' ), 10, 2 );
		add_filter( 'get_the_archive_title', array( __CLASS__, 'output_title' ), 10 );

		// setup vars
		add_action( 'pre_get_document_title', array( __CLASS__, 'set_meta' ), 9 );

		// Meta title & meta description
		if ( self::has_yoast() ) {
			// Yoast SEO v14.x
			if ( self::has_yoast_14() ) {
				add_filter( 'wpseo_twitter_title', array( __CLASS__, 'get_title' ), 10, 1 );
				add_filter( 'wpseo_twitter_description', array( __CLASS__, 'get_description' ), 10, 1 );
				add_filter( 'wpseo_opengraph_title', array( __CLASS__, 'get_title' ), 10, 1 );
				add_filter( 'wpseo_opengraph_desc', array( __CLASS__, 'get_description' ), 10, 1 );
				add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'wpseo_opengraph_url' ), 20, 2 );
				add_filter( 'wpseo_add_opengraph_additional_images', array( __CLASS__, 'wpseo_opengraph_image' ), 20, 1 );
				add_filter( 'wpseo_canonical', array( __CLASS__, 'wpseo_canonical' ), 20, 2 );
				add_filter( 'wpseo_adjacent_rel_url', array( __CLASS__, 'wpseo_adjacent_rel_url' ), 20, 3 );
			}

			add_filter( 'wpseo_title', array( __CLASS__, 'get_title' ), 10, 1 );
			add_filter( 'wpseo_metadesc', array( __CLASS__, 'get_description' ), 10, 1 );
		} elseif ( defined( 'RANK_MATH_VERSION' ) ) {
			add_action( 'rank_math/vars/register_extra_replacements', array( __CLASS__, 'rank_math_vars_register_extra_replacements' ), 20 );
			add_filter( 'rank_math/frontend/description', array( __CLASS__,'get_description' ), 10, 1 );
			add_filter( 'rank_math/frontend/title', array( __CLASS__, 'get_title' ), 10, 1 );
		} else {
			add_action( 'wp_head', array( __CLASS__, 'output_description' ) );
		}
	}

	/**
	 * Set the global var when a menu is being output.
     *
     * @since 2.0.0
	 *
	 * @param string $menu Menu.
	 *
	 * @return string $menu
	 */
	public static function set_menu_global($menu,$args){
		if ( null === $menu ) {
			if ( empty( $args->menu ) && ! empty( $args->theme_location ) && ( $locations = get_nav_menu_locations() ) && ! isset( $locations[ $args->theme_location ] ) ) {
				// Don't set $doing_menu for incorrect menu.
			} else {
				self::$doing_menu = true;
			}
		}

		return $menu;
	}

	/**
	 * Unset the global var when a menu has finished being output.
     *
     * @since 2.0.0
	 *
	 * @param string $menu Menu.
	 *
	 * @return string $menu
	 */
	public static function unset_menu_global($menu){
		self::$doing_menu = false;
		return $menu;
	}


	/**
	 * Output a page title.
     *
     * @since 2.0.0
	 *
	 * @param string $title Optional. Title. Default null.
	 * @param int $id Optional. ID. Default 0.
	 *
	 * @return string $title.
	 */
	public static function output_title( $title = '', $id = 0 ) {
		global $wp_query, $gdecs_render_loop, $geodir_query_object_id;

		$ajax_search = ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'geodir_ajax_search' && ! empty( $_REQUEST['geodir_search'] ) && wp_doing_ajax() ? true : false;

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

		if ( self::$title && empty( $id ) && ! self::$doing_menu ) {
			$normalize = true;
			$title = self::$title;
		} else if ( self::$title && ! empty( $id ) && $query_object_id == $id && ! self::$doing_menu && ( ! $gdecs_render_loop || ( $ajax_search && get_post_type( $id ) == 'page' ) ) ) {
			$normalize = true;
			$title = self::$title;
			/**
			 * Filter page title to replace variables.
			 *
			 * @param string $title The page title including variables.
			 * @param string $id The page id.
			 */
			$title = apply_filters( 'geodir_seo_title', __( $title, 'geodirectory' ), $title, $id );
		}

		// Strip duplicate whitespace.
		if ( $title != '' && $normalize ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Output a page meta title.
     *
     * @since 2.0.0
	 *
	 * @param string $title Optional. Title. Default null.
	 * @param string $sep Optional. Separator. Default null.
	 *
	 * @return mixed|void
	 */
	public static function output_meta_title( $title = '', $sep = '' ) {
		if ( self::$meta_title ) {
			$title = self::$meta_title;
		}

		/**
		 * Filter page meta title to replace variables.
		 *
		 * @since 1.5.4
		 * @param string $title The page title including variables.
		 * @param string $gd_page The GeoDirectory page type if any.
		 * @param string $sep The title separator symbol.
		 */
		$title = apply_filters( 'geodir_seo_meta_title', __( $title, 'geodirectory' ), self::$gd_page, $sep );

		// Strip duplicate whitespace.
		if ( $title != '' ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Get a page meta description.
	 *
	 * @since 2.0.0
	 */
	public static function get_description( $description = '' ) {
		$meta_description = self::$meta_description;

		if ( ! empty( $meta_description ) ) {
			$description = $meta_description;
		}

		// escape
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

		// Strip duplicate whitespace.
		if ( $description != '' ) {
			$description = normalize_whitespace( $description );
		}

		return $description;
	}

	/**
	 * Get a page meta title.
	 *
	 * @since 2.0.0
	 */
	public static function get_title( $title = '' ) {
		$meta_title= self::$meta_title;

		if ( ! empty( $meta_title ) ) {
			$title = $meta_title;
		}

		// escape
		if ( !empty( $title ) ) {
			$title = esc_attr( $title );
		}
		/**
		 * Filter SEO meta title.
		 *
		 * @param string $title Meta title.
		 */
		$title = apply_filters( 'geodir_seo_meta_title', $title, $meta_title );

		// Strip duplicate whitespace.
		if ( $title != '' ) {
			$title = normalize_whitespace( $title );
		}

		return $title;
	}

	/**
	 * Output a page meta description.
	 *
	 * @since 2.0.0
	 */
	public static function output_description() {
		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		$description = self::get_description();

		if ( $description != '' ) {
			echo '<meta name="description" content="' . esc_attr( $description ) . '" />';
		}
	}

	/**
	 * Set the title and meta info depending on the current page being visited.
     *
     * @since 2.0.0
	 */
	public static function set_meta() {
		$gd_settings = geodir_get_settings();

		if ( geodir_is_page( 'pt' ) ) {
			self::$gd_page = 'pt';
			$post_type = geodir_get_current_posttype();
			$post_type_info = get_post_type_object( $post_type );

			if ( isset( $post_type_info->seo['title'] ) && ! empty( $post_type_info->seo['title'] ) ) {
				self::$title = __( $post_type_info->seo['title'], 'geodirectory' );
			} else {
				self::$title = ! empty( $gd_settings['seo_cpt_title'] ) ? $gd_settings['seo_cpt_title'] : GeoDir_Defaults::seo_cpt_title();
			}

			if ( isset( $post_type_info->seo['meta_title'] ) && ! empty( $post_type_info->seo['meta_title'] ) ) {
				self::$meta_title = __( $post_type_info->seo['meta_title'], 'geodirectory' );
			} else {
				self::$meta_title = ! empty( $gd_settings['seo_cpt_meta_title'] ) ? $gd_settings['seo_cpt_meta_title'] : GeoDir_Defaults::seo_cpt_meta_title();
			}

			if ( isset( $post_type_info->seo['meta_description'] ) && ! empty( $post_type_info->seo['meta_description'] ) ) {
				self::$meta_description = __( $post_type_info->seo['meta_description'], 'geodirectory' );
			} else {
				self::$meta_description = ! empty( $gd_settings['seo_cpt_meta_description'] ) ? $gd_settings['seo_cpt_meta_description'] : GeoDir_Defaults::seo_cpt_meta_description();
			}
		} elseif ( geodir_is_page( 'archive' ) ) {
			self::$gd_page = 'archive';
			$queried_object = get_queried_object();

			if ( isset( $queried_object->taxonomy ) && geodir_taxonomy_type( $queried_object->taxonomy ) == 'category' && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				self::$title = ! empty( $gd_settings['seo_cat_archive_title'] ) ? $gd_settings['seo_cat_archive_title'] : GeoDir_Defaults::seo_cat_archive_title();
				self::$meta_title = ! empty( $gd_settings['seo_cat_archive_meta_title'] ) ? $gd_settings['seo_cat_archive_meta_title'] : GeoDir_Defaults::seo_cat_archive_meta_title();
				self::$meta_description = ! empty( $gd_settings['seo_cat_archive_meta_description'] ) ? $gd_settings['seo_cat_archive_meta_description'] : GeoDir_Defaults::seo_cat_archive_meta_description();
			} elseif ( isset($queried_object->taxonomy ) && geodir_taxonomy_type( $queried_object->taxonomy ) == 'tag' && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				self::$title = ! empty( $gd_settings['seo_tag_archive_title'] ) ? $gd_settings['seo_tag_archive_title'] : GeoDir_Defaults::seo_tag_archive_title();
				self::$meta_title = ! empty( $gd_settings['seo_tag_archive_meta_title'] ) ? $gd_settings['seo_tag_archive_meta_title'] : GeoDir_Defaults::seo_tag_archive_meta_title();
				self::$meta_description = ! empty( $gd_settings['seo_tag_archive_meta_description'] ) ? $gd_settings['seo_tag_archive_meta_description'] : GeoDir_Defaults::seo_tag_archive_meta_description();
			}
		} elseif ( geodir_is_page( 'single' ) ) {
			self::$gd_page = 'single';

			self::$title = ! empty( $gd_settings['seo_single_title'] ) ? $gd_settings['seo_single_title'] : GeoDir_Defaults::seo_single_title();
			self::$meta_title = ! empty( $gd_settings['seo_single_meta_title'] ) ? $gd_settings['seo_single_meta_title'] : GeoDir_Defaults::seo_single_meta_title();
			self::$meta_description = ! empty( $gd_settings['seo_single_meta_description'] ) ? $gd_settings['seo_single_meta_description'] : GeoDir_Defaults::seo_single_meta_description();
		} elseif ( geodir_is_page( 'location' ) ) {
			self::$gd_page = 'location';

			self::$title = ! empty( $gd_settings['seo_location_title'] ) ? $gd_settings['seo_location_title'] : GeoDir_Defaults::seo_location_title();
			self::$meta_title = ! empty( $gd_settings['seo_location_meta_title'] ) ? $gd_settings['seo_location_meta_title'] : GeoDir_Defaults::seo_location_meta_title();
			self::$meta_description = ! empty( $gd_settings['seo_location_meta_description'] ) ? $gd_settings['seo_location_meta_description'] : GeoDir_Defaults::seo_location_meta_description();
		} elseif ( geodir_is_page( 'search' ) ) {
			self::$gd_page = 'search';

			self::$title = ! empty( $gd_settings['seo_search_title'] ) ? $gd_settings['seo_search_title'] : GeoDir_Defaults::seo_search_title();
			self::$meta_title = ! empty( $gd_settings['seo_search_meta_title'] ) ? $gd_settings['seo_search_meta_title'] : GeoDir_Defaults::seo_search_meta_title();
			self::$meta_description = ! empty( $gd_settings['seo_search_meta_description'] ) ? $gd_settings['seo_search_meta_description'] : GeoDir_Defaults::seo_search_meta_description();
		} elseif ( geodir_is_page( 'add-listing' ) ) {
			self::$gd_page = 'add-listing';

			if ( ! empty( $_REQUEST['pid'] ) ) {
				self::$title = ! empty( $gd_settings['seo_add_listing_title_edit'] ) ? $gd_settings['seo_add_listing_title_edit'] : GeoDir_Defaults::seo_add_listing_title_edit();
			} else {
				self::$title = ! empty( $gd_settings['seo_add_listing_title'] ) ? $gd_settings['seo_add_listing_title'] : GeoDir_Defaults::seo_add_listing_title();
			}

			self::$meta_title = ! empty( $gd_settings['seo_add_listing_meta_title'] ) ? $gd_settings['seo_add_listing_meta_title'] : GeoDir_Defaults::seo_add_listing_meta_title();
			self::$meta_description = ! empty( $gd_settings['seo_add_listing_meta_description'] ) ? $gd_settings['seo_add_listing_meta_description'] : GeoDir_Defaults::seo_add_listing_meta_description();
		}

		if ( self::$title ) {
			self::$title = self::replace_variable( self::$title, self::$gd_page );
		}
		if ( self::$meta_title ) {
			self::$meta_title = self::replace_variable( self::$meta_title, self::$gd_page );
		}
		if ( self::$meta_description ) {
			self::$meta_description = self::replace_variable( self::$meta_description, self::$gd_page );
		}

		return self::$title;
	}

	/**
	 * Replace variables with values.
     *
     * @since 2.0.0
	 *
	 * @param string $string Optional. String. Default null.
	 * @param string $gd_page Optional. Geo directory page. Default null.
     *
     * @global object $post WordPress post object.
     * @global object $gd_post Geo directory post object.
	 *
	 * @return string $string.
	 */
	public static function replace_variable( $string = '', $gd_page = '' ) {
		global $post, $gd_post;

		$post_type = geodir_get_current_posttype();

		// Private address
		$check_address = ( $gd_page == 'single' || geodir_is_page( 'single' ) ) && ! empty( $gd_post ) && GeoDir_Post_types::supports( $post_type, 'private_address' ) ? true : false;

		/**
		 * Filter pre meta title.
		 *
		 * @since 2.0.0.76
		 *
		 * @param string $string Meta string.
		 * @param string $gd_page GeoDirectory page.
		 */
		$string = apply_filters( 'geodir_seo_pre_replace_variable', $string, $gd_page );

		if ( strpos( $string, '%%sep%%' ) !== false ) {
			$string = str_replace( "%%sep%%", self::separator(), $string );
		}

		if ( strpos( $string, '%%title%%' ) !== false ) {
			$string = str_replace( "%%title%%", $post->post_title, $string);
		}

		if ( strpos( $string, '%%sitename%%' ) !== false ) {
			$string = str_replace( "%%sitename%%", get_bloginfo( 'name' ), $string );
		}

		if ( strpos( $string, '%%sitedesc%%' ) !== false ) {
			$string = str_replace( "%%sitedesc%%", get_bloginfo( 'description' ), $string );
		}

		if ( strpos( $string, '%%excerpt%%' ) !== false ) {
			$excerpt_length = apply_filters( 'excerpt_length', 55 );
			$post_content = !empty($post->post_excerpt) ? strip_tags( $post->post_excerpt ) : '';
			if(!$post_content){
				$post_content = !empty($post->post_content) ? strip_tags( wp_trim_words($post->post_content, $excerpt_length,'') ) : '';

			}
			$string = str_replace( "%%excerpt%%",$post_content , $string );
		}

		if ( strpos( $string, '%%id%%' ) !== false ) {
			$string = str_replace( "%%id%%", absint($post->ID), $string );
		}

		// archive
		if ( strpos( $string, '%%category%%' ) !== false || strpos( $string, '%%in_category%%' ) !== false ) {
			$cat_name = '';

			if ( $gd_page == 'single' ) {
				if ( $gd_post->default_category ) {
					$cat      = get_term( $gd_post->default_category, $post->post_type . 'category' );
					$cat_name = ( isset( $cat->name ) ) ? $cat->name : '';
				}
			} else if ( $gd_page == 'archive' ) {
				$queried_object = get_queried_object();
				if ( isset( $queried_object->name ) ) {
					$cat_name = $queried_object->name;
				}
			} else if ( $gd_page == 'search' ) {
				$cat_name = self::get_searched_category_name( $post_type . 'category' );
			}

			$in_cat_name = $cat_name ? wp_sprintf( _x( 'in %s', 'in category', 'geodirectory' ), $cat_name ) : '';

			$string = str_replace( "%%category%%", $cat_name, $string );
			$string = str_replace( "%%in_category%%", $in_cat_name, $string );
		}

		if ( strpos( $string, '%%tag%%' ) !== false ) {
			$cat_name = '';

			if ( $gd_page == 'single' ) {
				if ( $post->default_category ) {
					$cat      = get_term( $post->default_category, $post->post_type . 'category' );
					$cat_name = ( isset( $cat->name ) ) ? $cat->name : '';
				}
			} else if ( $gd_page == 'archive' ) {
				$queried_object = get_queried_object();
				if ( isset( $queried_object->name ) ) {
					$cat_name = $queried_object->name;
				}
			}
			$string = str_replace( "%%tag%%", $cat_name, $string );
		}

		// CPT
		if ( strpos( $string, '%%pt_single%%' ) !== false ) {
			if ( $post_type && $singular_name = geodir_get_post_type_singular_label( $post_type ) ) {
				$string = str_replace( "%%pt_single%%", __( $singular_name, 'geodirectory' ), $string );
			}
		}

		if ( strpos( $string, '%%pt_plural%%' ) !== false ) {
			if ( $post_type && $plural_name = geodir_get_post_type_plural_label( $post_type ) ) {
				$string = str_replace( "%%pt_plural%%", __( $plural_name, 'geodirectory' ), $string );
			}
		}

		// location variable
		$location_replace_vars = geodir_location_replace_vars();
		foreach($location_replace_vars as $lkey=>$lval){
			if ( strpos( $string, $lkey ) !== false ) {
				$string = str_replace( $lkey, $lval, $string );
			}
		}

		// search
		$search_term = '';
		if ( isset( $_REQUEST['s'] ) ) {
			$search_term = esc_attr( $_REQUEST['s'] );
			$search_term = str_replace( array( "%E2%80%99", "’" ), array( "%27", "'" ), $search_term ); // apple suck
			$search_term = trim( stripslashes( $search_term ) );
		}

		// %%search_term%%
		if ( strpos( $string, '%%search_term%%' ) !== false ) {
			$string = str_replace( "%%search_term%%", $search_term, $string );
		}

		// %%for_search_term%%
		if ( strpos( $string, '%%for_search_term%%' ) !== false ) {
			$for_search_term = $search_term != '' ? wp_sprintf( __( 'for %s', 'geodirectory' ), $search_term ) : '';

			$string = str_replace( "%%for_search_term%%", $for_search_term, $string );
		}

		$search_near_term = '';
		$search_near = '';
		if ( isset( $_REQUEST['snear'] ) || isset( $_REQUEST['near'] ) ) {
			$search_near_term = esc_attr( $_REQUEST['snear'] );
			if ( empty( $search_near_term ) && ! empty( $_REQUEST['near'] ) && $_REQUEST['near'] == 'me' ) {
				$search_near_term = __( 'My Location', 'geodirectory' );
			}
			$search_near_term = str_replace( array( "%E2%80%99", "’" ), array( "%27", "'" ), $search_near_term ); // apple suck
			$search_near_term = trim( stripslashes( $search_near_term ) );

			if ( $search_near_term != '' ) {
				$search_near = wp_sprintf( __( 'near %s', 'geodirectory' ), $search_near_term );
			}
		}

		// %%search_near_term%%
		if ( strpos( $string, '%%search_near_term%%' ) !== false ) {
			$string = str_replace( "%%search_near_term%%", $search_near_term, $string );
		}

		// %%search_near%%
		if ( strpos( $string, '%%search_near%%' ) !== false ) {
			$string = str_replace( "%%search_near%%", $search_near, $string );
		}

		// page numbers
		if ( strpos( $string, '%%page%%' ) !== false ) {
			$page = geodir_title_meta_page( self::separator() );
			$page = $page ? $page : '';
			$string = str_replace( "%%page%%", $page, $string );
		}
		if ( strpos( $string, '%%pagenumber%%' ) !== false ) {
			$pagenumber = geodir_title_meta_pagenumber();
			$string      = str_replace( "%%pagenumber%%", $pagenumber, $string );
		}
		if ( strpos( $string, '%%pagetotal%%' ) !== false ) {
			$pagetotal = geodir_title_meta_pagetotal();
			$string     = str_replace( "%%pagetotal%%", $pagetotal, $string );
		}
		if ( strpos( $string, '%%postcount%%' ) !== false ) {
			$postcount = geodir_title_meta_postcount();
			$string     = str_replace( "%%postcount%%", $postcount, $string );
		}

		// Replace _post_images & _featured_image.
		if ( ( strpos( $string, '%%_featured_image%%' ) !== false || strpos( $string, '%%_post_images%%' ) !== false ) && ! empty( $gd_post->ID ) ) {
			$post_image = geodir_get_images( (int) $gd_post->ID, 1, false, 0, array( 'post_images' ), array( 'post_images' ) );

			$post_image_src = ! empty( $post_image ) && ! empty( $post_image[0] ) ? geodir_get_image_src( $post_image[0], 'original' ) : '';

			$string = str_replace( "%%_featured_image%%", $post_image_src, $string );
			$string = str_replace( "%%_post_images%%", $post_image_src, $string );
		}

		// let custom fields be used
		if ( strpos( $string, '%%_' ) !== false ) {
			$address_fields = geodir_post_meta_address_fields( $post_type );

			$matches_count = preg_match_all( '/%%_[^%%]*%%/', $string, $matches );

			if ( $matches_count && ! empty( $matches[0] ) ) {
				$matches = $matches[0];

				foreach ( $matches as $cf ) {
					$field_name = str_replace( array( "%%_", "%%" ), "", $cf );
					$cf_value = isset( $gd_post->{$field_name} ) ? $gd_post->{$field_name} : '';

					// round rating
					if ( $cf_value && $field_name == 'overall_rating' ) {
						$cf_value = round($cf_value, 1);
					}

					// Private address
					if ( ! empty( $cf_value ) && $check_address && isset( $address_fields[ $field_name ] ) ) {
						$cf_value = geodir_post_address( $cf_value, $field_name, $gd_post, '' );
					}

					$string = str_replace( "%%_{$field_name}%%", $cf_value, $string );
				}
			}
		}

		return apply_filters( 'geodir_replace_seo_vars', $string, $gd_page );
	}

	/**
	 * Returns an array of allowed variables and their descriptions.
     *
     * @since 2.0.0
	 *
	 * @param string $gd_page Optional. Geo directory page. Default null.
	 *
	 * @return array $vars.
	 */
	public static function variables( $gd_page = '' ) {
		$vars = array();

		// Generic
		if ( $gd_page != 'location_tags' ) {
			$vars['%%title%%'] = __( 'The current post title.', 'geodirectory' );
			$vars['%%sitename%%'] = __( 'The site name from general settings: site title. ', 'geodirectory' );
			$vars['%%sitedesc%%'] = __( 'The site description from general settings: tagline.', 'geodirectory' );
			$vars['%%sep%%'] = __( 'The separator mostly used in meta titles.', 'geodirectory' );
		}

		if ( $gd_page != 'location_tags' && $gd_page != 'location' ) {
			$vars['%%id%%'] = __( 'The current post id.', 'geodirectory' );
			$vars['%%excerpt%%'] = __( 'The current post excerpt.', 'geodirectory' );
			$vars['%%pt_single%%'] = __( 'Post type singular name.', 'geodirectory' );
			$vars['%%pt_plural%%'] = __( 'Post type plural name.', 'geodirectory' );
			$vars['%%category%%'] = __( 'The current category name.', 'geodirectory' );
			$vars['%%in_category%%'] = __( 'The current category name prefixed with `in` eg: in Attractions', 'geodirectory' );
		}

		// Paging
		if ( $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' ) {
			$vars['%%page%%'] = __( 'Current page number eg: page 2 of 4', 'geodirectory' );
			$vars['%%pagetotal%%'] = __( 'Total pages eg: 101', 'geodirectory' );
			$vars['%%postcount%%'] = __( 'Total post found eg: 10', 'geodirectory' );
			$vars['%%pagenumber%%'] = __( 'Current page number eg: 99', 'geodirectory' );
		}

		// Search page only
		if ( $gd_page == 'search' ) {
			$vars['%%search_term%%'] = __( 'The currently used search for term.', 'geodirectory' );
			$vars['%%for_search_term%%'] = __( 'The currently used search for term with `for`. Ex: for dinner.', 'geodirectory' );
			$vars['%%search_near%%'] = __( 'The currently used search near term with `near`. Ex: near Philadelphia.', 'geodirectory' );
			$vars['%%search_near_term%%'] = __( 'The currently used search near term.', 'geodirectory' );
		}

		// Location tags
		if ( ! $gd_page || $gd_page == 'location_tags' || $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' || $gd_page == 'single' || $gd_page == 'location' ) {
			$vars['%%location%%'] = __( 'The full current location eg: United States, Pennsylvania, Philadelphia', 'geodirectory' );
			$vars['%%location_single%%'] = __( 'The current viewing location type single name eg: Philadelphia', 'geodirectory' );
			$vars['%%in_location%%'] = __( 'The full current location prefixed with `in` eg: in United States, Pennsylvania, Philadelphia', 'geodirectory' );
			$vars['%%in_location_single%%'] = __( 'The current viewing location type single name prefixed with `in` eg: in Philadelphia', 'geodirectory' );
			$vars['%%location_country%%'] = __( 'The current viewing country eg: United States', 'geodirectory' );
			$vars['%%in_location_country%%'] = __( 'The current viewing country prefixed with `in` eg: in United States', 'geodirectory' );
			$vars['%%location_region%%'] = __( 'The current viewing region eg: Pennsylvania', 'geodirectory' );
			$vars['%%in_location_region%%']= __( 'The current viewing region prefixed with `in` eg: in Pennsylvania', 'geodirectory' );
			$vars['%%location_city%%'] = __( 'The current viewing city eg: Philadelphia', 'geodirectory' );
			$vars['%%in_location_city%%'] = __( 'The current viewing city prefixed with `in` eg: in Philadelphia', 'geodirectory' );
		}

		// Single page
		if ( $gd_page == 'single' ) {
			$vars['%%_FIELD-KEY%%'] = __( 'Show any custom field by using its field key prefixed with an _underscore. Ex: _phone.', 'geodirectory' );
		}

		return apply_filters( 'geodir_seo_variables', $vars, $gd_page );
	}

    /**
     * Helper tags.
     *
     * @since 2.0.0
     *
     * @param string $page optional. Page. Default null.
     * @return string $output.
     */
	public static function helper_tags( $page = '' ) {
		$output = '';
		$variables = self::variables( $page );

		if ( ! empty( $variables ) ) {
			$output .= '<ul class="geodir-helper-tags d-block clearfix p-0">';
			foreach( $variables as $variable => $desc ) {
				$output .= "<li><span class='geodir-helper-tag' title='" . esc_attr__( "Click to copy", "geodirectory" ) . "'>" . esc_attr( $variable ) . "</span>" . geodir_help_tip( $desc ) . "</li>";
			}
			$output .= '</ul>';
		}

		return $output;
	}

	/**
	 * Document title separator.
	 *
	 * @since 2.0.0.35
	 *
	 * @return string $sep.
	 */
	public static function separator() {
		$sep = '-';

		// Use RankMath setting separator.
		if ( defined( 'RANK_MATH_VERSION' ) && class_exists( 'RankMath', false ) ) {
			$sep = RankMath\Helper::get_settings( 'titles.title_separator' );
		}

		/**
		 * Filters the separator for the document title.
		 *
		 * @since 2.0.0.35
		 *
		 * @param string $sep Document title separator.
		 */
		return apply_filters( 'document_title_separator', $sep );
	}

	/**
	 * Filter Yoast breadcrumbs to add cat to details page.
	 *
	 * @param $crumbs
	 *
	 * @return mixed
	 */
	public static function breadcrumb_links( $crumbs ) {
		global $gd_post;

		if ( ! empty( $crumbs ) && ! empty( $gd_post->default_category ) && geodir_is_page( 'single' ) ) {
			$term = get_term( (int) $gd_post->default_category, $gd_post->post_type . 'category' );
			$term_added = false;

			$_crumbs = array();

			if ( ! empty( $term->term_id ) ) {
				foreach ( $crumbs as $key => $crumb ) {
					if ( ! empty( $crumb['term_id'] ) ) {
						if ( ! $term_added ) {
							$_crumbs[] = array(
								'url' => get_term_link( $term->term_id, $term->taxonomy ),
								'text' => $term->name,
								'term_id' => $term->term_id
							);
							$term_added = true;
						}
					} else {
						$_crumbs[] = $crumb;
					}
				}

				$crumbs = $_crumbs;
			}
		}

		return $crumbs;
	}

	/**
	 * Filter Rank Math breadcrumbs settings to hide ancestors.
	 *
	 * @since 2.0.0.100
	 *
	 * @param array $settings Breadcrumbs settings.
	 * @return array Breadcrumbs settings
	 */
	public static function rank_math_frontend_breadcrumb_settings( $settings ) {
		if ( ! is_admin() && geodir_is_geodir_page() ) {
			$settings['show_ancestors'] = false;
			$settings['hide_tax_name'] = true;
		}

		return $settings;
	}

	/**
	 * Filter Rank Math breadcrumbs to add cat to details page.
	 *
	 * @param $crumbs
	 *
	 * @return mixed
	 */
	public static function rank_breadcrumb_links( $crumbs, $breadcrumbs = array() ) {
		global $wp_query, $gd_detail_breadcrumb;

		// Maybe add category link to single page
		if ( ( geodir_is_page( 'single' ) || geodir_is_page( 'archive' ) ) && ! $gd_detail_breadcrumb ) {
			$post_type = geodir_get_current_posttype();

			$breadcrumb = array();
			$adjust = 0;

			if ( is_tax() && ! is_post_type_archive() ) {
				$breadcrumb[]= array(
					wp_strip_all_tags( geodir_post_type_name( $post_type, true ) ),
					get_post_type_archive_link( $post_type ),
					'hide_in_schema' => false
				);
				$adjust--;
			} else {
				$category = ! empty( $wp_query->query_vars[ $post_type . "category" ] ) ? $wp_query->query_vars[ $post_type . "category" ] : '';

				if ( $category ) {
					$term  = get_term_by( 'slug', $category, $post_type . "category" );

					if ( ! empty( $term ) ) {
						$breadcrumb[]= array( $term->name, get_term_link( $term->slug, $post_type . "category" ) );
					}
				}
			}

			if ( ! empty( $breadcrumb ) && count( $breadcrumb ) > 0 ) {
				$offset = RankMath\Helper::get_settings( 'general.breadcrumbs_home' ) ? 2 : 1;
				$offset = apply_filters( 'rankmath_breadcrumb_links_offset', ( $offset + $adjust ), $breadcrumb, $crumbs );
				$length = apply_filters( 'rankmath_breadcrumb_links_length', 0, $breadcrumb, $crumbs );

				array_splice( $crumbs, $offset, $length, $breadcrumb );
			}
		}

		return $crumbs;
	}

	/**
	 * Filter Rank Math breadcrumb post main term.
	 *
	 * @since 2.0.0.97
	 *
	 * @global array $gd_post The post.
	 * @global bool $gd_detail_breadcrumb True if term is set in post breadcrumb.
	 *
	 * @param object $term Post main term.
	 * @param array $terms Post terms.
	 * @return object The post main term.
	 */
	public static function rank_math_frontend_breadcrumb_main_term( $term, $terms = array() ) {
		global $gd_post, $gd_detail_breadcrumb;

		if ( ! empty( $terms ) && geodir_is_page( 'detail' ) && ! empty( $gd_post ) && ! empty( $gd_post->default_category ) ) {
			foreach ( $terms as $_term ) {
				if ( $_term->term_id == $gd_post->default_category ) {
					$term = $_term;
					$gd_detail_breadcrumb = true;
				}
			}
		}

		return $term;
	}

	/**
	 * Filter link for GD Archive pages.
	 *
	 * @param string $link    The page's permalink.
	 * @param int    $post_id The ID of the page.
	 * @param bool   $sample  Is it a sample permalink.
	 *
	 * @return string
	 */
	public static function page_link( $link, $page_id, $sample ) {
		global $wp_query;
		$query_object_id = '';
		if ( ! empty( $wp_query->post ) && isset( $wp_query->post->ID ) && geodir_is_geodir_page_id( $page_id ) ) {
			$query_object_id = $wp_query->post->ID;
		} elseif( !is_null($wp_query) ) {
			$query_object_id = get_queried_object_id();
		}

		if ( $query_object_id == $page_id && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) ) ) {
			$link = '#';
		}
		return $link;
	}

	/**
	 * Allow extending and modifying the posts to exclude from Yoast XML sitemap.
	 *
	 * @since 2.0.0.68
	 *
	 * @param array $posts_to_exclude The posts to exclude.
	 * @return array The posts to exclude.
	 */
	public static function wpseo_exclude_from_sitemap_by_post_ids( $excluded_posts_ids ) {
		if ( ! is_array( $excluded_posts_ids ) ) {
			$excluded_posts_ids = array();
		}

		$gd_excluded_posts_ids = self::get_noindex_page_ids();
		if ( ! empty( $gd_excluded_posts_ids ) && is_array( $gd_excluded_posts_ids ) ) {
			$excluded_posts_ids = empty( $excluded_posts_ids ) ? $gd_excluded_posts_ids : array_merge( $excluded_posts_ids, $gd_excluded_posts_ids );
		}

		return $excluded_posts_ids;
	}

	/**
	 * Get nonindex page ids.
	 *
	 * @since 2.0.0.68
	 *
	 * @return array Array of page ids.
	 */
	public static function get_noindex_page_ids() {
		$page_ids = wp_cache_get( 'geodir_noindex_page_ids', 'geodir_noindex_page_ids' );

		if ( $page_ids !== false ) {
			return $page_ids;
		}

		$page_ids = array();
		$page_ids[] = geodir_get_page_id( 'details', '', false );
		$page_ids[] = geodir_get_page_id( 'archive', '', false );
		$page_ids[] = geodir_get_page_id( 'archive_item', '', false );

		$_page_ids = geodir_cpt_template_pages();
		if ( ! empty( $_page_ids ) && is_array( $_page_ids ) ) {
			$page_ids = array_merge( $page_ids, $_page_ids );
		}

		$page_ids = apply_filters( 'geodir_get_noindex_page_ids', $page_ids );

		wp_cache_set( 'geodir_noindex_page_ids', $page_ids, 'geodir_noindex_page_ids' );

		return $page_ids;
	}

	/**
	 * Setup Yoast SEO opengraph meta.
	 *
	 * @since 2.0.0.89
	 *
	 * @return void.
	 */
	public static function template_redirect() {
		if ( self::has_yoast() ) {
			// OpenGraph
			if ( ! self::has_yoast_14() ) {
				add_action( 'wpseo_opengraph', array( __CLASS__, 'wpseo_head_setup_meta' ), 0 );
				add_action( 'wpseo_opengraph', array( __CLASS__, 'wpseo_head_unset_meta' ), 99 );

				// Twitter
				if ( WPSEO_Options::get( 'twitter' ) === true ) {
					add_action( 'wpseo_head', array( __CLASS__, 'wpseo_head_setup_meta' ), 39 );
					add_action( 'wpseo_head', array( __CLASS__, 'wpseo_head_unset_meta' ), 41 );
				}
			}
		}
	}

	/**
	 * Set Yoast SEO opengraph meta.
	 *
	 * @since 2.0.0.89
	 *
	 * @global bool|int $gd_has_filter_thumbnail_id Check whether filter set or not.
	 *
	 * @return void.
	 */
	public static function wpseo_head_setup_meta() {
		global $gd_has_filter_thumbnail_id;

		add_filter( 'wpseo_frontend_page_type_simple_page_id', array( __CLASS__ , 'wpseo_frontend_page_type_simple_page_id' ), 10, 1 );

		if ( geodir_is_page( 'single' ) && ( $gd_has_filter_thumbnail_id = has_filter( 'get_post_metadata', array( 'GeoDir_Template_Loader', 'filter_thumbnail_id' ) ) ) ) {
			remove_filter( 'get_post_metadata', array( 'GeoDir_Template_Loader', 'filter_thumbnail_id' ), 10, 4 );
		}
	}

	/**
	 * Unset Yoast SEO opengraph meta.
	 *
	 * @since 2.0.0.89
	 *
	 * @global bool|int $gd_has_filter_thumbnail_id Check whether filter set or not.
	 *
	 * @return void.
	 */
	public static function wpseo_head_unset_meta() {
		global $gd_has_filter_thumbnail_id;

		remove_filter( 'wpseo_frontend_page_type_simple_page_id', array( __CLASS__ , 'wpseo_frontend_page_type_simple_page_id' ), 10, 1 );

		if ( geodir_is_page( 'single' ) && $gd_has_filter_thumbnail_id && ! has_filter( 'get_post_metadata', array( 'GeoDir_Template_Loader', 'filter_thumbnail_id' ) ) ) {
			add_filter( 'get_post_metadata', array( 'GeoDir_Template_Loader', 'filter_thumbnail_id' ), 10, 4 );

			$gd_has_filter_thumbnail_id = false;
		}
	}

	/**
	 * Filter Yoast SEO simple page id.
	 *
	 * @since 2.0.0.89
	 *
	 * @param int $page_id The page id.
	 * @return int Filtered page id.
	 */
	public static function wpseo_frontend_page_type_simple_page_id( $page_id ) {
		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) || geodir_is_page( 'single' ) ) && ! is_tax() && ( $_page_id = (int) GeoDir_Compatibility::gd_page_id() ) ) {
			$page_id = $_page_id;
		}

		return $page_id;
	}

	/**
	 * Check Yoast SEO is installed or not.
	 *
	 * @since 2.0.0.91
	 *
	 * @return bool True if Yoast SEO is installed else false.
	 */
	public static function has_yoast() {
		return defined( 'WPSEO_VERSION' );
	}

	/**
	 * Check Yoast SEO v14.x installed or not.
	 *
	 * @since 2.0.0.91
	 *
	 * @return bool True if Yoast SEO v14.x is installed else false.
	 */
	public static function has_yoast_14() {
		return ( self::has_yoast() && version_compare( WPSEO_VERSION, '14.0', '>=' ) );
	}

	/**
	 * Register GD variables for Yoast SEO extra replacements.
	 *
	 * @since 2.0.0.93
	 *
	 * @return void
	 */
	public static function wpseo_register_extra_replacements() {
		$pages = array( 'location', 'search', 'post_type', 'archive', 'add-listing', 'single' );

		$variables = array();
		foreach ( $pages as $page ) {
			$_variables = GeoDir_SEO::variables( $page );

			if ( ! empty( $_variables ) ) {
				foreach ( $_variables as $var => $help ) {
					if ( empty( $variables[ $var ] ) ) {
						$variables[ $var ] = $help;
					}
				}
			}
		}

		// Custom fields
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		if ( ! empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( empty( $variables[ '_' . $field['htmlvar_name'] ] ) ) {
					$variables[ '_' . $field['htmlvar_name'] ] = __( stripslashes( $field['admin_title'] ), 'geodirectory' );
				}
			}
		}

		// Advance custom fields
		$advance_fields = geodir_post_meta_advance_fields();
		if ( ! empty( $advance_fields ) ) {
			foreach ( $advance_fields as $key => $field ) {
				if ( empty( $variables[ '_' . $key ] ) ) {
					$variables[ '_' . $key ] = __( stripslashes( $field['frontend_title'] ), 'geodirectory' );
				}
			}
		}

		$variables = apply_filters( 'geodir_wpseo_register_extra_replacements', $variables );

		$replacer = new WPSEO_Replace_Vars();

		foreach ( $variables as $var => $help ) {
			if ( is_string( $var ) && $var !== '' ) {
				$var = trim( $var, '%' );

				if ( ! empty( $var ) ) {
					$var = '_gd_' . $var; // Add prefix to prevent conflict with Yoast default vars.

					if ( ! method_exists( $replacer, 'retrieve_' . $var ) ) {
						wpseo_register_var_replacement( $var, array( __CLASS__, 'wpseo_replacement' ), 'advanced', $help );
					}
				}
			}
		}
	}

	/**
	 * Register GD variables for Yoast SEO extra replacements.
	 *
	 * @since 2.0.0.93
	 *
	 * @param string $var Variable name.
	 * @param array $args Variable args.
	 * @return string Variable value.
	 */
	public static function wpseo_replacement( $var, $args ) {
		$var = strpos( $var, '_gd_' ) === 0 ? substr( $var, 4 ) : $var;

		return self::replace_variable( '%%' . $var . '%%', self::$gd_page );
	}

	/**
	 * Filter Yoast SEO meta title.
	 *
	 * @since 2.0.0.93
	 *
	 * @param string $title Meta title.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Meta title.
	 */
	public static function wpseo_title( $title, $presentation = array() ) {
		if ( ! empty( $title ) || ! geodir_is_geodir_page() ) {
			return $title;
		}

		if ( geodir_is_page( 'archive' ) ) {
			$queried_object = get_queried_object();

			if (  ! empty( $queried_object->term_id ) && ! empty( $queried_object->taxonomy ) && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				if ( $_title = WPSEO_Taxonomy_Meta::get_term_meta( $queried_object->term_id, $queried_object->taxonomy, 'title' ) ) {
					$title = $_title;
				} elseif ( $_title = WPSEO_Options::get( 'title-tax-' . $queried_object->taxonomy ) ) {
					$title = $_title;
				}

				if ( strpos( $title, '%%' ) !== false ) {
					$title = wpseo_replace_vars( $title, $queried_object );
				}

				if ( strpos( $title, '%%' ) !== false ) {
					$title = self::replace_variable( $title, 'archive' );
				}
			}
		}

		return $title;
	}

	/**
	 * Filter Yoast SEO meta description.
	 *
	 * @since 2.0.0.93
	 *
	 * @param string $meta_description Meta description.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Meta description.
	 */
	public static function wpseo_metadesc( $meta_description, $presentation = array() ) {
		if ( ! empty( $meta_description ) || ! geodir_is_geodir_page() ) {
			return $meta_description;
		}

		if ( geodir_is_page( 'archive' ) ) {
			$queried_object = get_queried_object();

			if (  ! empty( $queried_object->term_id ) && ! empty( $queried_object->taxonomy ) && geodir_is_gd_taxonomy( $queried_object->taxonomy ) ) {
				if ( $_meta_description = WPSEO_Taxonomy_Meta::get_term_meta( $queried_object->term_id, $queried_object->taxonomy, 'desc' ) ) {
					$meta_description = $_meta_description;
				} elseif ( $_meta_description = WPSEO_Options::get( 'metadesc-tax-' . $queried_object->taxonomy ) ) {
					$meta_description = $_meta_description;
				}
			}

			if ( strpos( $meta_description, '%%' ) !== false ) {
				$meta_description = wpseo_replace_vars( $meta_description, $queried_object );
			}

			if ( strpos( $meta_description, '%%' ) !== false ) {
				$meta_description = self::replace_variable( $meta_description, 'archive' );
			}
		}

		return $meta_description;
	}

	/**
	 * Filter Yoast SEO generated open graph URL.
	 *
	 * @since 2.0.0.91
	 *
	 * @param string $canonical The URL.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Filtered URL.
	 */
	public static function wpseo_opengraph_url( $canonical, $presentation ) {
		if ( $canonicals = self::get_canonicals() ) {
			if ( ! empty( $canonicals['canonical'] ) ) {
				$canonical = $canonicals['canonical'];
			}
		}

		return $canonical;
	}

	public static function wpseo_opengraph_image( $image_container ) {
		global $gd_post;

		if ( ! geodir_is_geodir_page() ) {
			return;
		}

		if ( $image_container->has_images() ) {
			return;
		}

		if ( geodir_is_page( 'post_type' ) ) {
			$post_type = geodir_get_current_posttype();

			if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
				if ( ! empty( $post_type_obj->default_image ) ) {
					$image_container->add_image_by_id( $post_type_obj->default_image );
				}
			}
		} elseif ( geodir_is_page( 'archive' ) ) {
			$image_id = 0;
			$term = get_queried_object();

			if ( ! empty( $term->term_id ) && ( $image = get_term_meta( $term->term_id, 'ct_cat_default_img', true ) ) ) {
				if ( ! empty( $image['id'] ) ) {
					$image_id = (int) $image['id'];
				}
			}

			if ( empty( $image_id ) ) {
				// Post type default image.
				$post_type = geodir_get_current_posttype();

				if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
					if ( ! empty( $post_type_obj->default_image ) ) {
						$image_id = (int) $post_type_obj->default_image;
					}
				}
			}

			if ( $image_id > 0 ) {
				$image_container->add_image_by_id( $image_id );
			}
		} elseif ( geodir_is_page( 'single' ) ) {
			$image_id = 0;
			$post_image = ! empty( $gd_post->ID ) ? geodir_get_images( (int) $gd_post->ID, 1, false, 0, array( 'post_images' ), array( 'post_images' ) ) : array();

			if ( ! empty( $post_image ) && ! empty( $post_image[0] ) ) {
				$post_image = $post_image[0];

				if ( ! empty( $post_image->metadata ) ) {
					$post_image->metadata = maybe_unserialize( $post_image->metadata );
				}

				$image = array(
					'url' => geodir_get_image_src( $post_image, 'original' )
				);

				if ( ! empty( $post_image->metadata['width'] ) && ! empty( $post_image->metadata['height'] ) ) {
					$image['width'] = (int) $post_image->metadata['width'];
					$image['height'] = (int) $post_image->metadata['height'];
				}

				if ( ! empty( $image['url'] ) ) {
					$image_container->add_image( $image );
					return;
				}
			}

			// Default category image.
			if ( ! empty( $gd_post->default_category ) && ( $image = get_term_meta( (int) $gd_post->default_category, 'ct_cat_default_img', true ) ) ) {
				if ( ! empty( $image['id'] ) ) {
					$image_id = (int) $image['id'];
				}
			}

			if ( empty( $image_id ) ) {
				// Post type default image.
				$post_type = ! empty( $gd_post->post_type ) ? $gd_post->post_type : geodir_get_current_posttype();

				if ( $post_type && ( $post_type_obj = geodir_post_type_object( $post_type ) ) ) {
					if ( ! empty( $post_type_obj->default_image ) ) {
						$image_id = (int) $post_type_obj->default_image;
					}
				}
			}

			if ( $image_id > 0 ) {
				$image_container->add_image_by_id( $image_id );
			}
		}
	}

	/**
	 * Filter Yoast SEO generated canonical URL.
	 *
	 * @since 2.0.0.91
	 *
	 * @param string $canonical The URL.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Filtered URL.
	 */
	public static function wpseo_canonical( $canonical, $presentation ) {
		if ( $canonicals = self::get_canonicals() ) {
			if ( ! empty( $canonicals['canonical_paged'] ) ) {
				$canonical = $canonicals['canonical_paged'];
			}
		}

		return $canonical;
	}

	/**
	 * Filter the rel next/prev URL.
	 *
	 * @since 2.0.0.91
	 *
	 * @param string $rel The next/prev URL.
	 * @param string $type next or prev.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Filtered next/prev URL.
	 */
	public static function wpseo_adjacent_rel_url( $rel, $type, $presentation ) {
		if ( $rel && $type && ( $canonicals = self::get_canonicals() ) ) {
			if ( ! empty( $canonicals['canonical_' . $type ] ) ) {
				$rel = $canonicals['canonical_' . $type ];
			}
		}

		return $rel;
	}

	/**
	 * Filter the meta robots output array of Yoast SEO.
	 *
	 * @since 2.0.0.99
	 *
	 * @param array $robots The meta robots directives to be used.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return array The meta robots array.
	 */
	public static function wpseo_robots_array( $robots, $presentation ) {
		if ( ! empty( $robots ) && geodir_is_page( 'single' ) ) {
			if ( ! empty( $presentation ) && ! empty( $presentation->model->object_id ) ) {
				$indexable = (bool) self::wpseo_is_indexable( (int) $presentation->model->object_id );

				if ( empty( $robots['follow'] ) ) {
					$robots['follow'] = WPSEO_Meta::get_value( 'meta-robots-nofollow', (int) $presentation->model->object_id );
				}
			} else if ( $post_type = geodir_get_current_posttype() ) {
				$indexable = WPSEO_Options::get( 'noindex-' . $post_type, false ) === false ? true : false;
			} else {
				return $robots;
			}

			if ( $indexable ) {
				$robots['index'] = 'index';
			} else {
				$robots['index'] = 'noindex';
			}
		}

		return $robots;
	}

	/**
	 * Yoast SEO: Determines whether a particular post_id is of an indexable post type.
	 *
	 * @since 2.2.8
	 *
	 * @param string $post_id The post ID to check.
	 * @return bool Whether or not it is indexable.
	 */
	public static function wpseo_is_indexable( $post_id ) {
		if ( ! empty( $post_id ) && WPSEO_Meta::get_value( 'meta-robots-noindex', $post_id ) !== '0' ) {
			return WPSEO_Meta::get_value( 'meta-robots-noindex', $post_id ) === '2';
		}

		return WPSEO_Options::get( 'noindex-' . get_post_type( $post_id ), false ) === false;
	}

	/**
	 * Prevent using post type archive index model on term archive page.
	 *
	 * @since 2.0.0.102
	 *
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @param object $context The meta tags context.
	 * @return array Filtered presentation of an indexable.
	 */
	public static function wpseo_frontend_presentation( $presentation, $context ) {
		if ( geodir_is_geodir_page() && is_tax() && is_post_type_archive() && geodir_is_page( 'archive' ) ) {
			try {
				if ( ! empty( $presentation->model->object_id ) && ! empty( $presentation->model->object_sub_type ) ) {
					$term = get_term( $presentation->model->object_id, $presentation->model->object_sub_type );

					if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
						$presentation->source = $term;
						$term_meta = WPSEO_Taxonomy_Meta::get_term_meta( $term, $term->taxonomy, null );

						if ( ! empty( $term_meta ) ) {
							$is_robots_noindex = null;

							if ( array_key_exists( 'wpseo_noindex', $term_meta ) ) {
								$value = $term_meta['wpseo_noindex'];

								if ( $value === 'noindex' ) {
									$is_robots_noindex = true;
								} elseif ( $value === 'index' ) {
									$is_robots_noindex = false;
								} elseif ( $value == 'default' ) {
									$is_robots_noindex = ! WPSEO_Options::get( 'noindex-tax-' . $term->taxonomy, false ) ? false : true;
								}
							}

							$presentation->model->is_robots_noindex = $is_robots_noindex;
							$presentation->model->is_public = ( $presentation->model->is_robots_noindex === null ) ? null : ! $presentation->model->is_robots_noindex;
						}
					}
				}
			} catch ( Exception $e ) { }
		}

		return $presentation;
	}

	/**
	 * Yoast filter the Twitter title.
	 *
	 * @since 2.1.0.9
	 *
	 * @param string                 $title Twitter title.
	 * @param Indexable_Presentation $presentation The presentation of an indexable.
	 * @return string Filtered title.
	 */
	public static function wpseo_twitter_title( $title, $presentation ) {
		if ( geodir_is_page( 'search' ) && ! empty( $presentation ) ) {
			$title = isset( $presentation->model->twitter_title ) && ! is_null( $presentation->model->twitter_title ) ? $presentation->model->twitter_title : '';

			if ( $title && strpos( $title, '%%' ) !== false ) {
				$title = wpseo_replace_vars( $title, get_post( (int) GeoDir_Compatibility::gd_page_id() ) );
			}

			if ( $title && strpos( $title, '%%' ) !== false ) {
				$title = self::replace_variable( $title, 'search' );
			}
		}
		return $title;
	}
	/**
	 * Filter the post meta data.
	 *
	 * @since 2.0.0.99
	 * @access public
	 *
	 * @param mixed  $value     The metadata value or an array
	 *                          of values depending on the value of `$single`. Default null.
	 * @param int    $object_id ID of the object metadata is for.
	 * @param string $meta_key  Metadata key.
	 * @param bool   $single    Whether to return only the first value of the specified `$meta_key`.
	 * @param string $meta_type Type of object metadata is for.
	 * @return mixed Post metadata value.
	 */
	public static function filter_post_metadata( $value, $object_id, $meta_key, $single = false, $meta_type = '' ) {
		global $geodir_post_meta_loop;

		if ( null === $value ) {
			return $value;
		}

		if ( defined( 'WPSEO_VERSION' ) && ! empty( $object_id ) && ! is_admin() && empty( $meta_key ) && is_array( $value ) && empty( $geodir_post_meta_loop ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) ) {
			$geodir_post_meta_loop = true;

			// Check & remove filters
			$has_filter_1 = has_filter( 'get_post_metadata', array( 'GeoDir_Compatibility', 'dynamically_add_post_meta' ) );
			if ( $has_filter_1 ) {
				remove_filter( 'get_post_metadata', array( 'GeoDir_Compatibility', 'dynamically_add_post_meta' ) );
			}

			$has_filter_2 = has_filter( 'get_post_metadata', array( 'GeoDir_SEO', 'filter_post_metadata' ) );
			if ( $has_filter_2 ) {
				remove_filter( 'get_post_metadata', array( 'GeoDir_SEO', 'filter_post_metadata' ) );
			}

			$_value = get_post_custom( $object_id );

			if ( ! empty( $_value ) && is_array( $_value ) ) {
				// Reserved post meta keys for single listing.
				$reserve_keys = array( '_yoast_wpseo_content_score', '_yoast_wpseo_linkdex', '_yoast_wpseo_meta-robots-adv', '_yoast_wpseo_meta-robots-nofollow', '_yoast_wpseo_meta-robots-noindex', '_yoast_wpseo_is_cornerstone', '_yoast_wpseo_title', '_yoast_wpseo_metadesc' );

				// Remove template page post meta values.
				foreach ( $value as $key => $data ) {
					if ( in_array( $key, $reserve_keys ) ) {
						unset( $value[ $key ] );
					}
				}

				// Add single listing post meta values.
				foreach ( $_value as $key => $data ) {
					if ( in_array( $key, $reserve_keys ) ) {
						$value[ $key ] = $data;
					}
				}
			}

			$geodir_post_meta_loop = false;

			// Check & add filters back.
			if ( $has_filter_1 ) {
				add_filter( 'get_post_metadata', array( 'GeoDir_Compatibility', 'dynamically_add_post_meta' ), 10, 4 );
			}

			if ( $has_filter_2 ) {
				add_filter( 'get_post_metadata', array( 'GeoDir_SEO', 'filter_post_metadata' ), 99, 5 );
			}
		}

		return $value;
	}

	/**
	 * Get paged & non paged canonical URLs for GD post type & archive pages.
	 *
	 * @since 2.0.0.91
	 *
	 * @return array|NULL Array of URLs.
	 */
	public static function get_canonicals() {
		global $wp_rewrite;

		if ( ! geodir_is_geodir_page() ) {
			return NULL;
		}

		$canonicals = array();
		$canonical = '';

		if ( geodir_is_page( 'pt' ) ) {
			$post_type = geodir_get_current_posttype();

			$canonical = get_post_type_archive_link( $post_type );
		} elseif ( geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->taxonomy ) ) {
				$term_link = get_term_link( $term, $term->taxonomy );

				if ( ! is_wp_error( $term_link ) ) {
					$canonical = $term_link;
				}
			}
		}

		if ( $canonical ) {
			$canonical_paged = $canonical;
			$canonical_next = $canonical;
			$canonical_prev = $canonical;

			$paged = (int) get_query_var( 'paged' );
			if ( $paged < 1 ) {
				$paged = 1;
			}

			if ( ! $wp_rewrite->using_permalinks() ) {
				if ( $paged > 1 ) {
					$canonical_paged = add_query_arg( 'paged', $paged, $canonical );

					if ( $paged > 2 ) {
						$canonical_prev = add_query_arg( 'paged', ( $paged - 1 ), $canonical );
					}
				}

				$canonical_next = add_query_arg( 'paged', ( $paged + 1 ), $canonical );
			} else {
				if ( $paged > 1 ) {
					$canonical_paged = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . $paged );

					if ( $paged > 2 ) {
						$canonical_prev = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . ( $paged - 1 ) );
					}
				}

				$canonical_next = user_trailingslashit( trailingslashit( $canonical ) . trailingslashit( $wp_rewrite->pagination_base ) . ( $paged + 1 ) );
			}

			$canonicals = array(
				'canonical' => $canonical,
				'canonical_paged' => $canonical_paged,
				'canonical_next' => $canonical_next,
				'canonical_prev' => $canonical_prev
			);
		}

		return apply_filters( 'geodir_get_canonicals', $canonicals );
	}

	/**
	 * Exclude GD template pages from WP XML sitemaps.
	 *
	 * @since 2.0.0.99
	 *
	 * @param array  $args Array of WP_Query arguments.
	 * @param string $post_type Post type name.
	 * @return array The posts to exclude.
	 */
	public static function wp_sitemaps_exclude_post_ids( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			// GD template page ids.
			$page_ids = self::get_noindex_page_ids();

			if ( ! empty( $page_ids ) ) {
				$post_not_in = ! empty( $args['post__not_in'] ) && is_array( $args['post__not_in'] ) ? array_merge( $args['post__not_in'], $page_ids ) : $page_ids;

				$args['post__not_in'] = $post_not_in;
			}
		}

		return $args;
	}

	/**
	 * Get searched category names.
	 *
	 * @since 2.0.0.100
	 *
	 * @param string $taxonomy Category taxonomy. Default empty.
	 * @return string Category names.
	 */
	public static function get_searched_category_name( $taxonomy = '' ) {
		$category_names = '';

		if ( empty( $_REQUEST['spost_category'] ) ) {
			return $category_names;
		}

		$post_category = is_array( $_REQUEST['spost_category'] ) ? array_map( 'absint', $_REQUEST['spost_category'] ) : array( absint( $_REQUEST['spost_category'] ) );
		$_category_names = array();

		if ( ! empty( $post_category ) ) {
			$taxonomy = $taxonomy ? $taxonomy : geodir_get_current_posttype() . 'category';

			foreach ( $post_category as $term_id ) {
				$term = get_term( $term_id, $taxonomy );

				if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
					$_category_names[] = $term->name;
				}
			}
		}

		if ( ! empty( $_category_names ) ) {
			$category_names = implode( ', ', $_category_names );
		}

		return apply_filters( 'geodir_get_searched_category_name', $category_names, $_category_names, $taxonomy );
	}

	/**
	 * Register GD variables for Rank Math SEO extra replacements.
	 *
	 * @since 2.1.0.14
	 *
	 * @return void
	 */
	public static function rank_math_vars_register_extra_replacements() {
		$pages = array( 'location', 'search', 'post_type', 'archive', 'add-listing', 'single' );

		$variables = array();
		foreach ( $pages as $page ) {
			$_variables = GeoDir_SEO::variables( $page );

			if ( ! empty( $_variables ) ) {
				foreach ( $_variables as $var => $help ) {
					if ( empty( $variables[ $var ] ) ) {
						$variables[ $var ] = $help;
					}
				}
			}
		}

		// Custom fields
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		if ( ! empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( empty( $variables[ '_' . $field['htmlvar_name'] ] ) ) {
					$variables[ '_' . $field['htmlvar_name'] ] = __( stripslashes( $field['admin_title'] ), 'geodirectory' );
				}
			}
		}

		// Advance custom fields
		$advance_fields = geodir_post_meta_advance_fields();
		if ( ! empty( $advance_fields ) ) {
			foreach ( $advance_fields as $key => $field ) {
				if ( empty( $variables[ '_' . $key ] ) ) {
					$variables[ '_' . $key ] = __( stripslashes( $field['frontend_title'] ), 'geodirectory' );
				}
			}
		}

		$variables = apply_filters( 'geodir_rank_math_register_extra_replacements', $variables );

		foreach ( $variables as $var => $help ) {
			if ( is_string( $var ) && $var !== '' ) {
				$var = trim( $var, '%' );

				if ( ! empty( $var ) ) {
					$var = '_gd_' . $var; // Add prefix to prevent conflict with Yoast default vars.

					rank_math_register_var_replacement(
						$var,
						array(
							'name'        => esc_html( $help ),
							'description' => esc_html( $help ),
							'variable'    => $var
						)
					);

					add_filter( 'rank_math/vars/' . $var, array( __CLASS__, 'rank_math_replacement' ), 20, 2 );
				}
			}
		}
	}

	/**
	 * Register GD variables for Ran Math SEO extra replacements.
	 *
	 * @since 2.1.0.14
	 *
	 * @param string $args Variable args.
	 * @param array $variable Variable model.
	 * @return string Variable value.
	 */
	public static function rank_math_replacement( $args, $variable = array() ) {
		$var = ! empty( $variable ) ? $variable->get_id() : '';
		if ( empty( $var ) ) {
			return '';
		}

		$var = strpos( $var, '_gd_' ) === 0 ? substr( $var, 4 ) : $var;

		return self::replace_variable( '%%' . $var . '%%', self::$gd_page );
	}

	/**
	* Meta description replace vars.
	*
	* @since 2.2.19
	*
	* @param string $description The description sentence.
	* @return string Filtered description.
	*/
	public static function rank_math_frontend_description_replace_vars( $description ) {
		if ( empty( $description ) ) {
			return $description;
		}

		return self::replace_variable( $description, self::$gd_page );
	}

	/**
	 * The SEO Framework: Exclude GD templates pages from XML sitemap.
	 *
	 * @since 2.2.8
	 *
	 * @param array $query_args The query args.
	 * @return array Filtered query args.
	 */
	public static function the_seo_framework_sitemap_exclude_posts( $query_args ) {
		$exclude_ids = self::get_noindex_page_ids();

		if ( ! empty( $exclude_ids ) && is_array( $exclude_ids ) ) {
			if ( ! empty( $query_args['post__not_in'] ) ) {
				$post__not_in = $query_args['post__not_in'];

				if ( ! is_array( $post__not_in ) ) {
					$post__not_in = explode( ",", $post__not_in );
				}

				$post__not_in = array_merge( $post__not_in, $exclude_ids );
			} else {
				$post__not_in = $exclude_ids;
			}

			$query_args['post__not_in'] = $post__not_in;
		}

		return $query_args;
	}

	/**
	 * Filter SEOPress breadcrumbs links.
	 *
	 * @since 2.3.30
	 *
	 * @param $crumbs
	 *
	 * @return mixed
	 */
	public static function seopress_pro_breadcrumbs_crumbs( $crumbs ) {
		if ( ! empty( $crumbs ) ) {
			if ( geodir_is_page('archive') || geodir_is_page('post_type') ) {
				$post_type = geodir_get_current_posttype();
				$cpt_link = get_post_type_archive_link( $post_type );
				$post_type_object = get_post_type() == 'page' ? get_post_type_object( 'page' ) : array();

				$_crumbs = array();

				foreach ( $crumbs as $i => $crumb ) {
					if ( ! empty( $crumb[1] ) && $crumb[1] == $cpt_link && ! empty( $crumbs[ $i + 1 ] ) && ! empty( $crumbs[ $i + 1 ][1] ) && $crumbs[ $i + 1 ][1] == $crumb[1] ) {
						continue;
					}

					if ( ! empty( $post_type_object ) && ! empty( $crumb[0] ) && empty( $crumb[1] ) && $crumb[0] == wp_strip_all_tags( $post_type_object->labels->name ) ) {
						if ( ! empty( $crumbs[ $i + 1 ] ) && ! empty( $crumbs[ $i + 1 ][1] ) && $crumbs[ $i + 1 ][1] == $cpt_link ) {
							continue;
						}

						$_crumbs[]= array(
							wp_strip_all_tags( geodir_post_type_name( $post_type, true ) ),
							$cpt_link
						);
					} else {
						$_crumbs[] = $crumb;
					}
				}

				$crumbs = $_crumbs;
			}
		}

		return $crumbs;
	}
}

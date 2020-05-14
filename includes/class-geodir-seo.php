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

	// some global values
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

		// maybe noindex empty archive pages
		add_action('wp_head', array(__CLASS__,'maybe_noindex_empty_archives'));
        add_filter('wpseo_breadcrumb_links', array(__CLASS__, 'breadcrumb_links'));
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( __CLASS__, 'wpseo_exclude_from_sitemap_by_post_ids' ), 20, 1 );
		if ( ! is_admin() ) {
			add_filter( 'page_link', array( __CLASS__, 'page_link' ), 10, 3 );
		}

		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ), 9999 );
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

	public static function yoast_enabled(){
		global $geodir_options;
		return defined( 'WPSEO_VERSION')
		       && ( !isset($geodir_options['wpseo_disable'] ) || ( isset($geodir_options['wpseo_disable']) && $geodir_options['wpseo_disable']=='0' ) )   ? true : false;
		//return ( defined( 'WPSEO_VERSION')  )  ? true : false;
	}

	public static function rank_math_enabled(){
		global $geodir_options;
		return defined( 'RANK_MATH_VERSION')
		       && ( !isset($geodir_options['rank_math_disable'] ) || ( isset($geodir_options['rank_math_disable']) && $geodir_options['rank_math_disable']=='0' ) )   ? true : false;
	}


	public static function maybe_run(){

		// bail if we have a SEO plugin installed.
		if(
			self::yoast_enabled() // don't run if active and not set to be disabled
				|| self::rank_math_enabled() // don't run if active and not set to be disabled
		    || class_exists( 'All_in_One_SEO_Pack' )  // don't run if active
		    || is_admin()  // no need to run in wp-admin
		){

			// even if disabled we still need to replace title vars
			if(!is_admin()){
				// set a global so we don't change the menu items titles
				add_filter('pre_wp_nav_menu',array(__CLASS__,'set_menu_global'),10,2);
				add_filter('wp_nav_menu',array(__CLASS__,'unset_menu_global'));
				// YOOtheme renders own menuwalker.
				if ( class_exists( 'YOOtheme\\Theme' ) ) {
					add_filter( 'wp_nav_menu_items',array( __CLASS__, 'unset_menu_global' ), 999, 1 );
				}

				if ( self::has_yoast_14() ) {
					add_filter( 'wpseo_title', array( __CLASS__, 'wpseo_title' ), 20, 2 );
					add_filter( 'wpseo_metadesc', array( __CLASS__, 'wpseo_metadesc' ), 20, 2 );

					add_filter( 'wpseo_opengraph_url', array( __CLASS__, 'wpseo_opengraph_url' ), 20, 2 );
					add_filter( 'wpseo_canonical', array( __CLASS__, 'wpseo_canonical' ), 20, 2 );
					add_filter( 'wpseo_adjacent_rel_url', array( __CLASS__, 'wpseo_adjacent_rel_url' ), 20, 3 );

					add_action( 'wpseo_register_extra_replacements', array( __CLASS__, 'wpseo_register_extra_replacements' ), 20 );
				}

				// page title
				add_filter('the_title',array(__CLASS__,'output_title'),10,2);
				add_filter('get_the_archive_title',array(__CLASS__,'output_title'),10);

				// setup vars
				add_action('pre_get_document_title', array(__CLASS__,'set_meta'),9);
			}
			return;
		}

		// set a global so we don't change the menu items titles
		add_filter('pre_wp_nav_menu',array(__CLASS__,'set_menu_global'),10,2);
		add_filter('wp_nav_menu',array(__CLASS__,'unset_menu_global'));
		// YOOtheme renders own menuwalker.
		if ( class_exists( 'YOOtheme\\Theme' ) ) {
			add_filter( 'wp_nav_menu_items',array( __CLASS__, 'unset_menu_global' ), 999, 1 );
		}

		// meta title
		add_filter('wp_title', array(__CLASS__,'output_meta_title'),1000,2);
		add_filter('pre_get_document_title', array(__CLASS__,'output_meta_title'), 1000);

		// page title
		add_filter('the_title',array(__CLASS__,'output_title'),10,2);
		add_filter('get_the_archive_title',array(__CLASS__,'output_title'),10);

		// setup vars
		add_action('pre_get_document_title', array(__CLASS__,'set_meta'),9);

		// Meta title & meta description
		if ( self::has_yoast() ) {
			// Yoast SEO v14.x
			if ( self::has_yoast_14() ) {
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
		global $wp_query,$gdecs_render_loop;

		// In some themes the object id is missing so we fix it
		$query_object_id = '';

		if ( $id && isset( $wp_query->post->ID ) && geodir_is_geodir_page_id( $id ) ) {
			$query_object_id = $wp_query->post->ID;
		} elseif ( ! is_null( $wp_query ) ) {
			$query_object_id = get_queried_object_id();
		}

		if ( self::$title && empty( $id ) && ! self::$doing_menu ) {
			$title = self::$title;
		} elseif ( self::$title && ! empty( $id ) && $query_object_id == $id && ! self::$doing_menu && !$gdecs_render_loop) {
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
		if ( $title != '' ) {
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
	public static function output_description(){
		$description = self::get_description();
		echo '<meta name="description" content="' . $description . '" />';
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
				self::$title = $post_type_info->seo['title'];
			} else {
				self::$title = ! empty( $gd_settings['seo_cpt_title'] ) ? $gd_settings['seo_cpt_title'] : GeoDir_Defaults::seo_cpt_title();
			}

			if ( isset( $post_type_info->seo['meta_title'] ) && ! empty( $post_type_info->seo['meta_title'] ) ) {
				self::$meta_title = $post_type_info->seo['meta_title'];
			} else {
				self::$meta_title = ! empty( $gd_settings['seo_cpt_meta_title'] ) ? $gd_settings['seo_cpt_meta_title'] : GeoDir_Defaults::seo_cpt_meta_title();
			}

			if ( isset( $post_type_info->seo['meta_description'] ) && ! empty( $post_type_info->seo['meta_description'] ) ) {
				self::$meta_description = $post_type_info->seo['meta_description'];
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
		if ( strpos( $string, '%%category%%' ) !== false ) {
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
			}
			$string = str_replace( "%%category%%", $cat_name, $string );
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
			if ( $post_type && $singular_name = get_post_type_singular_label( $post_type ) ) {
				$string = str_replace( "%%pt_single%%", __( $singular_name, 'geodirectory' ), $string );
			}
		}

		if ( strpos( $string, '%%pt_plural%%' ) !== false ) {
			if ( $post_type && $plural_name = get_post_type_plural_label( $post_type ) ) {
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
		if ( isset( $_REQUEST['snear'] ) ) {
			$search_near_term = esc_attr( $_REQUEST['snear'] );
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
			$page  = geodir_title_meta_page( self::separator() );
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


		// CPT vars
		if($gd_page == 'pt'){

		}

		// let custom fields be used
		if ( strpos( $string, '%%_' ) !== false ) {
			$matches_count = preg_match_all('/%%_[^%%]*%%/',$string,$matches);
			if($matches_count && !empty($matches[0])){
				$matches = $matches[0];
				foreach($matches as $cf){
					$field_name = str_replace(array("%%_","%%"),"",$cf);
					$cf_value = isset($gd_post->{$field_name}) ? $gd_post->{$field_name} : '';//geodir_get_post_meta($post->ID,$field_name,true);

					// round rating
					if($cf_value && $field_name == 'overall_rating'){
						$cf_value = round($cf_value, 1);
					}

					$string     = str_replace( "%%_{$field_name}%%", $cf_value, $string );
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
			$vars['%%excerpt%%'] = __( 'The current post excerpt.', 'geodirectory' );
			$vars['%%pt_single%%'] = __( 'Post type singular name.', 'geodirectory' );
			$vars['%%pt_plural%%'] = __( 'Post type plural name.', 'geodirectory' );
			$vars['%%category%%'] = __( 'The current category name.', 'geodirectory' );
			$vars['%%id%%'] = __( 'The current post id.', 'geodirectory' );
		}

		// Location tags
		if ( ! $gd_page || $gd_page == 'location_tags' || $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' || $gd_page == 'single' || $gd_page == 'location' ) {
			$vars['%%location%%'] = __( 'The full current location eg: United States, Pennsylvania, Philadelphia', 'geodirectory' );
			$vars['%%location_single%%'] = __( 'The current viewing location type single name eg: Philadelphia', 'geodirectory' );
			$vars['%%in_location%%'] = __( 'The full current location prefixed with `in` eg: in United States, Pennsylvania, Philadelphia', 'geodirectory' );
			$vars['%%in_location_single%%'] = __( 'The current viewing location type single name prefixed with `in` eg: Philadelphia', 'geodirectory' );
			$vars['%%location_country%%'] = __( 'The current viewing country eg: United States', 'geodirectory' );
			$vars['%%in_location_country%%'] = __( 'The current viewing country prefixed with `in` eg: in United States', 'geodirectory' );
			$vars['%%location_region%%'] = __( 'The current viewing region eg: Pennsylvania', 'geodirectory' );
			$vars['%%in_location_region%%']= __( 'The current viewing region prefixed with `in` eg: in Pennsylvania', 'geodirectory' );
			$vars['%%location_city%%'] = __( 'The current viewing city eg: Philadelphia', 'geodirectory' );
			$vars['%%in_location_city%%'] = __( 'The current viewing city prefixed with `in` eg: in Philadelphia', 'geodirectory' );
		}

		// Search page only
		if ( $gd_page == 'search' ) {
			$vars['%%search_term%%'] = __( 'The currently used search for term.', 'geodirectory' );
			$vars['%%for_search_term%%'] = __( 'The currently used search for term with `for`. Ex: for dinner.', 'geodirectory' );
			$vars['%%search_near%%'] = __( 'The currently used search near term with `near`. Ex: near Philadelphia.', 'geodirectory' );
			$vars['%%search_near_term%%'] = __( 'The currently used search near term.', 'geodirectory' );
		}

		// Paging
		if ( $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' ) {
			$vars['%%page%%'] = __( 'Current page number eg: page 2 of 4', 'geodirectory' );
			$vars['%%pagetotal%%'] = __( 'Total pages eg: 101', 'geodirectory' );
			$vars['%%postcount%%'] = __( 'Total post found eg: 10', 'geodirectory' );
			$vars['%%pagenumber%%'] = __( 'Current page number eg: 99', 'geodirectory' );
		}

		// Single page
		if ( $gd_page == 'single' ) {
			$vars['%%_FIELD-KEY%%'] = __( 'Show any custom field by using its field key prefixed with an _underscore', 'geodirectory' );
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
	public static function helper_tags($page = ''){
		$output = '';
		$variables = self::variables($page);
		if(!empty($variables)){
			$output .= '<ul class="geodir-helper-tags">';
			foreach($variables as $variable => $desc){
				$output .= "<li><span class='geodir-helper-tag' title='".__("Click to copy","geodirectory")."'>".esc_attr($variable)."</span>".geodir_help_tip( $desc )."</li>";
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
		/**
		 * Filters the separator for the document title.
		 *
		 * @since 2.0.0.35
		 *
		 * @param string $sep Document title separator. Default '-'.
		 */
		return apply_filters( 'document_title_separator', '-' );
	}

	/**
	 * Filter Yoast breadcrumbs to add cat to details page.
	 *
	 * @param $crumbs
	 *
	 * @return mixed
	 */
    public static function breadcrumb_links($crumbs){

	    // maybe add category link to single page

        if ( geodir_is_page( 'detail' ) || geodir_is_page( 'listing' ) ) {
	        global $wp_query;
	        $breadcrumb = array();
	        $post_type   = geodir_get_current_posttype();
	        $category = !empty($wp_query->query_vars[$post_type."category"]) ? $wp_query->query_vars[$post_type."category"] : '';
	        if($category){
		        $term  = get_term_by( 'slug', $category, $post_type."category");
		        if(!empty($term)){
			        $breadcrumb[]['term'] = $term;
		        }
	        }

	        $offset = apply_filters('wpseo_breadcrumb_links_offset', 2, $breadcrumb, $crumbs);
	        $length = apply_filters('wpseo_breadcrumb_links_length', 0, $breadcrumb, $crumbs);

	        if(!empty($breadcrumb) && count($breadcrumb) > 0 ){
		        array_splice( $crumbs, $offset, $length, $breadcrumb );
	        }

        }

        return $crumbs;
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
			$term = get_queried_object();

			if ( ! empty( $term->term_id ) && ( $image = get_term_meta( $term->term_id, 'ct_cat_default_img', true ) ) ) {
				$image_container->add_image_by_id( $image );
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
}

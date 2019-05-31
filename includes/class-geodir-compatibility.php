<?php
/**
 * Compatibility functions for third party plugins.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class GeoDir_Compatibility {

	/**
	 * Initiate the compatibility class.
	 *
	 * @since 2.0.0
	 */
	public static function init() {

		/*######################################################
		Yoast (WP SEO)
		######################################################*/
		add_filter( 'option_wpseo_taxonomy_meta', array( __CLASS__, 'wpseo_taxonomy_meta' ), 10, 2 );
		// add setting to be able to disable yoast on GD pages
		add_filter( 'geodir_seo_options', array( __CLASS__, 'wpseo_disable' ), 10 );

		/*######################################################
		Disqus (comments system) :: If Disqus plugin is active, do some fixes to show on blogs but no on GD post types
		######################################################*/
		if ( function_exists( 'dsq_can_replace' ) ) {
			remove_filter( 'comments_template', array( __CLASS__, 'dsq_comments_template' ) );
			add_filter( 'comments_template', array( __CLASS__, 'dsq_comments_template' ), 100 );
			add_filter( 'pre_option_disqus_active', array( __CLASS__, 'disqus_active' ), 10, 1 );
		}

		/*######################################################
		JetPack :: Disable JetPack comments on GD post types.
		######################################################*/
		add_action( 'plugins_loaded', array( __CLASS__, 'jetpack' ) );

		/*######################################################
		Ninja Forms :: Add our own form tags.
		######################################################*/
		add_action( 'ninja_forms_loaded', array( __CLASS__, 'ninja_forms' ) );

		/*######################################################
		Primer (theme) :: Fix single page title.
		######################################################*/
		add_filter( 'primer_the_page_title', array( __CLASS__, 'primer_title' ) );

		/*######################################################
		Beaver Builder :: Fix Page templates.
		######################################################*/
		add_filter( 'geodir_bypass_setup_archive_loop_as_page', array( __CLASS__, 'beaver_builder_loop_bypass' ) );
		if(class_exists( 'FLBuilderLoader' )){
			add_action('wp_footer', array( __CLASS__, 'beaver_builder_template_warning' ) );
		}

		/*######################################################
		Elementor :: Fix Page templates.
		######################################################*/
		add_filter( 'geodir_bypass_setup_archive_loop_as_page', array( __CLASS__, 'elementor_loop_bypass' ) );
		add_filter( 'geodir_bypass_setup_singular_page', array( __CLASS__, 'elementor_loop_bypass' ) );

		/*######################################################
		WP Easy Updates :: Allow beta addons if set
		######################################################*/
		add_filter( 'wp_easy_updates_api_params', array( __CLASS__, 'wp_easy_updates' ), 10, 2 );

		/*######################################################
		Genesis (theme) :: Fix archive pages excerpt.
		######################################################*/
		add_filter( 'genesis_pre_get_option_content_archive', array( __CLASS__, 'genesis_content_archive' ) );
		add_filter( 'genesis_entry_title_wrap', array( __CLASS__, 'genesis_entry_title_wrap' ) );

		/*######################################################
		Kleo (theme) :: Fix page titles.
		######################################################*/
		add_filter( 'kleo_title_args', array( __CLASS__, 'kleo_title_args' ) );

		// after_setup_theme checks
		add_action( 'after_setup_theme', array( __CLASS__, 'for_later_checks' ) );

		/*######################################################
		Astra (theme) :: Fix page layouts.
		######################################################*/
		add_filter( 'astra_page_layout', array( __CLASS__, 'astra_page_layout' ) );
		add_filter( 'astra_get_content_layout', array( __CLASS__, 'astra_get_content_layout' ) );
		add_action( 'wp', array( __CLASS__, 'astra_wp' ), 20, 1 );


		/*######################################################
		Divi (theme) :: maps api
		######################################################*/
		add_filter( 'et_pb_enqueue_google_maps_script', '__return_false' );
		add_filter( 'et_builder_load_actions', array( __CLASS__,'divi_builder_ajax_load_actions') );

		/*######################################################
		The7 (theme) :: rewind the posts, the_excerpt function call seems to set the current_post number and cause have_posts() to return false.
		######################################################*/
		add_action( 'presscore_body_top', 'rewind_posts' );
		if(isset($_REQUEST['geodir_search'])){
			remove_filter('the_content','wpautop'); // for some reason this is added to the content on search.
		}

		/*######################################################
		BuddyPress
		######################################################*/
		if ( class_exists( 'BuddyPress' ) ) {
			add_action( 'admin_init', array( __CLASS__, 'buddypress_notices' ) );
		}


		/*######################################################
		GENERAL
		######################################################*/
		add_filter( 'get_post_metadata', array( __CLASS__, 'dynamically_add_post_meta' ), 10, 4 );

		// Set custom hook for theme compatibility
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );
	}

	/**
	 * Make sure divi actions fire on some of our ajax calls so builder shortcodes are rendered.
	 * 
	 * @param $actions
	 *
	 * @return array
	 */
	public static function divi_builder_ajax_load_actions( $actions ) {
		$actions[] = 'geodir_recently_viewed_listings';
		$actions[] = 'geodir_widget_listings';
		$actions[] = 'geodir_bestof';

		return $actions;
	}

	/**
	 * Adds a warning message if a user tries to use BB on a template page.
	 */
	public static function beaver_builder_template_warning(){
		// check if we are in builder
		if(!is_admin() && isset($_REQUEST['fl_builder']) && $id = get_the_ID() ){
			if(geodir_is_geodir_page_id($id)){
				global $geodirectory;
				$is_geodir_page_template = false;
				if(!empty($geodirectory->settings['page_search']) && $geodirectory->settings['page_search'] == $id ){
					$is_geodir_page_template = true;
				}elseif(!empty($geodirectory->settings['page_details']) && $geodirectory->settings['page_details'] == $id ){
					$is_geodir_page_template = true;
				}elseif(!empty($geodirectory->settings['page_archive']) && $geodirectory->settings['page_archive'] == $id ){
					$is_geodir_page_template = true;
				}elseif(!empty($geodirectory->settings['page_archive_item']) && $geodirectory->settings['page_archive_item'] == $id ){
					$is_geodir_page_template = true;
				}elseif( geodir_is_cpt_template_page( $id ) ){
					$is_geodir_page_template = true;
				}
				if($is_geodir_page_template ){
					$warning_message = sprintf(
						__('GeoDirectory template pages work much better with %sBeaver Themer%s :: %sLearn more%s', 'geodirectory'),
						'<a href="https://www.wpbeaverbuilder.com/beaver-themer/" target="_blank">', //@todo add affiliate code to beaver themer link
						' <i class="fas fa-external-link-alt"></i></a>',
						'<a href="https://wpgeodirectory.com/docs-v2/integrations/builders/#bb-themer" target="_blank">',
						' <i class="fas fa-external-link-alt"></i></a>'
					);
					$warning_html = '<div class="gd-notification gd-warning  "><i class="fas fa-exclamation-triangle"></i> '.$warning_message.'</div>';
					echo "<script>jQuery(function() {beaver_builder_template_warning = lity('<div class=\"lity-show\">$warning_html <a href=\"#\" onclick=\"beaver_builder_template_warning.close();\">Close</a></div>');});</script>";
				}
			}
		}
	}

	/**
	 * Fix details page title, primer theme breaks it.
	 *
	 * @since 2.0.0
	 */
	public static function for_later_checks() {
		/*######################################################
		Boss. (BuddyBoss)
		######################################################*/
		if ( class_exists( 'BuddyBoss_Theme' ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'buddyboss' ), 100 );
			add_filter( 'body_class', array( __CLASS__, 'buddyboss_body_class' ) );
		}

		/*######################################################
		Genesis theme (Corporate Pro)
		######################################################*/
		if ( function_exists( 'corporate_body_classes' ) ) {
			add_filter( 'body_class', array( __CLASS__, 'genesis_corporate_pro_body_class' ) );
		}

		/*######################################################
		Some themes not setting our meta info via our normal hook
		::
		X
		Newspaper
		######################################################*/
		if ( ! is_admin() &&
		     (
			     function_exists( 'x_get_view' )
			     || defined( 'TD_THEME_VERSION' )
				 || function_exists( 'pi_elv_include_scripts' )
				 || ( ( function_exists( 'mfn_body_classes' ) && function_exists( 'mfn_ID' ) ) )
		     )
		) {
			add_action( 'wp_title', array( 'GeoDir_SEO', 'set_meta' ), 9 );
		}

		/*######################################################
		SEOPress (breaks search page)
		######################################################*/
		if(function_exists('seopress_activation')){
			add_action( 'wp_head', array( __CLASS__, 'seopress' ), 0 );
		}

		/*######################################################
		Enfold
		######################################################*/
		if ( function_exists('avia_lang_setup') ) {
			add_filter( 'avf_preview_window_css_files', array( __CLASS__, 'enfold_preview_styles' ) );
		}

		// Flatsome theme breaks search page.
		if ( isset( $_REQUEST['geodir_search'] ) && function_exists( 'flatsome_contentfix' ) && has_filter( 'the_content', 'flatsome_contentfix' ) ) {
			remove_filter( 'the_content', 'flatsome_contentfix' );
			add_filter( 'the_content', 'flatsome_contentfix', 11 );
		}
	}

	/**
	 * Add some basic styles to the editor preview.
	 * 
	 * @param $css
	 *
	 * @return mixed
	 */
	public static function enfold_preview_styles($css){
		
		// add our preview styles
		$css[geodir_plugin_url() . '/assets/css/block_editor.css'] = 1;
		
		return $css;
	}
	
	/**
	 * SEOPress breaks the search page when nothing is searched, so we remove some filters in that case.
	 */
	public static function seopress(){
		if(geodir_is_page( 'search' )){
			remove_action( 'wp_head', 'seopress_social_fb_desc_hook', 1 );
			remove_action( 'wp_head', 'seopress_social_twitter_desc_hook', 1 );
		}
	}

	/**
	 * Adds warning notices if BuddyPress is active and has issues.
	 */
	public static function buddypress_notices() {
		if ( is_admin() ) {

			// maybe add search slug warning.
			$search_page_id = geodir_search_page_id();
			$search_slug    = get_post_field( 'post_name', $search_page_id );
			if ( GeoDir_Admin_Notices::has_notice( 'buddypress_search_slug_error' ) ) {
				if ( $search_slug != 'search' ) {
					GeoDir_Admin_Notices::remove_notice( 'buddypress_search_slug_error' );
				}
			} else {
				if ( $search_slug == 'search' ) {
					GeoDir_Admin_Notices::add_custom_notice(
						'buddypress_search_slug_error',
						sprintf(
							__( '<b>WARNING:</b> BuddyPress hijacks the slug `search`, GD search will not work until you update the GD search page slug to something different. <a href="%s">Edit page</a>', 'geodirectory' ),
							get_edit_post_link( $search_page_id )
						)
					);
				}
			}
		}
	}

	/**
	 * Stop the GD loop setup if elementor is overriding it.
	 *
	 * @param $bypass
	 *
	 * @return bool
	 */
	public static function elementor_loop_bypass( $bypass ) {
		if ( defined( 'ELEMENTOR_PRO_VERSION' ) && GeoDir_Elementor::is_template_override() ) {
			$bypass = true;
		}

		return $bypass;
	}

	/**
	 * Dynamically add post meta to some GD pages from their page templates.
	 *
	 * This helps trick page builders into using the page template settings.
	 *
	 * @global WP_Query $wp_query Global WP_Query instance.
	 *
	 * @param $metadata
	 * @param $object_id
	 * @param $meta_key
	 * @param $single
	 *
	 * @return null|string
	 */
	public static function dynamically_add_post_meta( $metadata, $object_id, $meta_key, $single ) {
		global $wp_query, $post; // this is needed to make sure get_queried_object_id() if defined.

		// bail if in admin
		if ( is_admin() ) {
			return $metadata;
		}

		// Standard WP fields
		$wp_keys = array(
			'_wp_page_template',
//			'_thumbnail_id',
		);

		// Generic keys
		$gen_keys = array(
			'primer_layout', // primer theme layout
		);

		// FusionBuilder (Avada theme), we need to add these on the fly
		if ( class_exists( 'FusionBuilder' ) ) {
			if ( substr( $meta_key, 0, 5 ) === "pyre_" || substr( $meta_key, 0, 4 ) === "sbg_" ) {
				$gen_keys[] = $meta_key;
			}
		}

		// Astra theme
		if ( defined('ASTRA_THEME_VERSION') ) {
			$gen_keys[] = 'ast-main-header-display';
			$gen_keys[] = 'footer-sml-layout';
			$gen_keys[] = 'site-post-title';
			$gen_keys[] = 'site-sidebar-layout';
			$gen_keys[] = 'site-content-layout';
			$gen_keys[] = 'ast-featured-img';
		}

		// Newspaper theme
		if ( defined('TD_THEME_VERSION') ) {
			if ( substr( $meta_key, 0, 3 ) === "td_") {
				$gen_keys[] = $meta_key;
			}
		}

		// Enfold theme
		if ( function_exists( 'avia_get_option' ) ) {
			if ( substr( $meta_key, 0, 6 ) === "_avia_" ) {
				$gen_keys[] = $meta_key;
			}
			$gen_keys[] = 'header_transparency';
			$gen_keys[] = 'header_title_bar';
			$gen_keys[] = 'footer';
			$gen_keys[] = 'sidebar';
			$gen_keys[] = 'layout';
		}

		// Sky theme
		if ( function_exists( 'vh_setup' ) ) {
			if ( substr( $meta_key, 0, 4 ) === "sbg_" ) {
				$gen_keys[] = $meta_key;
			}
			$gen_keys[] = 'layouts';
		}

		$backup_post = NULL;

		// Elvyre - Retina Ready Wordpress Theme
		if ( function_exists( 'pi_elv_include_scripts' ) ) {
			if ( strpos( $meta_key, 'pg_' ) === 0 ) {
				$elvyre_keys = array( 'pg_sidebar', 'pg_portfolio_style', 'pg_portfolio_taxonomies', 'pg_hide_title', 'pg_page_description', 'pg_title_style', 'pg_title_color', 'pg_title_image', 'pg_additional_title_image', 'pg_parallax' );

				$gen_keys = array_merge( $gen_keys, $elvyre_keys );

				// Archive page set page post.
				if ( in_array( $meta_key, $elvyre_keys ) && ! empty( $wp_query ) && ! empty( $wp_query->post->post_type ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) && $wp_query->post->post_type == 'page' && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) ) {
					$post = $wp_query->post;
					$backup_post = $post;
				}
			}
		}

		// Betheme by Muffin group
		if ( function_exists( 'mfn_body_classes' ) && function_exists( 'mfn_ID' ) ) {
			if ( strpos( $meta_key, 'mfn-post-' ) === 0 ) {
				$gen_keys[] = $meta_key;

				// Archive page set page post.
				if ( ! empty( $wp_query ) && ! empty( $wp_query->post->post_type ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) && $wp_query->post->post_type == 'page' && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) ) {
					$post = $wp_query->post;
					$backup_post = $post;
				}
			}
		}

		// Unicon
		if ( function_exists( 'minti_register_required_plugins' ) ) {
			if ( ( strpos( $meta_key, 'minti_' ) === 0 || empty( $meta_key ) ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) ) {
				if ( geodir_is_page( 'detail' ) ) {
					$template_page_id = geodir_details_page_id( get_post_type( $object_id ) );
				} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
					$template_page_id = geodir_archive_page_id( get_post_type( $object_id ) );
				} else if ( geodir_is_page( 'search' ) ) {
					$template_page_id = geodir_search_page_id();
				} else {
					$template_page_id = 0;
				}

				if ( ! empty( $template_page_id ) ) {
					return empty( $meta_key ) ? get_post_custom( $template_page_id ) : get_post_meta( $template_page_id, $meta_key, $single );
				}
			}
		}

		// Kingstudio
		if ( function_exists( 'kingstudio_ninzio_global_variables' ) ) {
			$kingstudio_keys = array( 'blank', 'one_page', 'rev_slider', 'sidebar', 'sidebar_pos', 'rh_content', 'rh_text_color', 'breadcrumbs_text_color', 'rh_back_color', 'rh_back_img', 'rh_back_img_repeat', 'rh_back_img_position', 'rh_back_img_attachment', 'rh_back_img_size', 'parallax' );

			if ( ( in_array( $meta_key, $kingstudio_keys ) || empty( $meta_key ) ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) ) {
				if ( geodir_is_page( 'detail' ) ) {
					$template_page_id = geodir_details_page_id( get_post_type( $object_id ) );
				} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
					$template_page_id = geodir_archive_page_id( get_post_type( $object_id ) );
				} else if ( geodir_is_page( 'search' ) ) {
					$template_page_id = geodir_search_page_id();
				} else {
					$template_page_id = 0;
				}

				if ( ! empty( $template_page_id ) ) {
					return empty( $meta_key ) ? get_post_custom( $template_page_id ) : get_post_meta( $template_page_id, $meta_key, $single );
				}
			}
		}

		if (
			$meta_key
			&& ( $meta_key[0] == "_" || in_array( $meta_key, $gen_keys ) )
			&& $object_id
			&& ! empty( $wp_query )
			&& $object_id == get_queried_object_id()
			&& ( geodir_is_page( 'single' ) || geodir_is_page( 'archive' ) )
		) {

			$template_page_id = geodir_is_page( 'single' ) ? geodir_details_page_id() : geodir_archive_page_id();

			// if we got this far then we might as well load all the page post meta
			global $gd_compat_post_meta;
			if ( ! is_array( $gd_compat_post_meta ) ) {
				$gd_compat_post_meta = get_post_meta( $template_page_id );
				if ( ! empty( $gd_compat_post_meta ) ) {
					foreach($gd_compat_post_meta as $key=>$val){
						$gd_compat_post_meta[$key] = array_map("maybe_unserialize",$val);
					}
				} else {
					$gd_compat_post_meta = array();
				}
			}

			// WP
			if ( in_array( $meta_key, $wp_keys ) ) {
				$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
			}

			// generic keys
			if ( in_array( $meta_key, $gen_keys ) ) {
				$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
			}

			// Elementor
			if ( function_exists( '_is_elementor_installed' ) && ( isset( $gd_compat_post_meta['_elementor_edit_mode'] ) && $gd_compat_post_meta['_elementor_edit_mode'] == 'builder' ) ) {
				if ( substr( $meta_key, 0, 11 ) === "_elementor_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

			// DIVI (elegant themes)
			if ( function_exists( 'et_pb_is_pagebuilder_used' ) && et_pb_is_pagebuilder_used( $template_page_id ) ) {
				if ( substr( $meta_key, 0, 4 ) === "_et_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

			// Beaver Builder
			if ( class_exists( 'FLBuilderLoader' ) && ( isset( $gd_compat_post_meta['_fl_builder_enabled'] ) && $gd_compat_post_meta['_fl_builder_enabled'] ) ) {
				if ( substr( $meta_key, 0, 4 ) === "_fl_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

			// WPBakery page builder
//			if(class_exists( 'FLBuilderLoader' ) && geodir_get_post_meta_raw( $template_page_id, '_fl_builder_enabled') ){
//				if(substr( $meta_key, 0, 4 ) === "_vc_"){
//			$metadata = isset($gd_compat_post_meta[$meta_key]) ? $gd_compat_post_meta[$meta_key] : '';
//				}
//			}

			// Customify theme
			if ( function_exists( 'Customify' ) ) {
				if ( substr( $meta_key, 0, 11 ) === "_customify_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

			// Kleo theme
			if ( function_exists( 'kleo_setup' ) ) {
				if ( substr( $meta_key, 0, 6 ) === "_kleo_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

			// Genesis theme
			if ( function_exists( 'genesis_constants' ) ) {
				if ( substr( $meta_key, 0, 9 ) === "_genesis_" ) {
					$metadata = isset( $gd_compat_post_meta[ $meta_key ] ) ? $gd_compat_post_meta[ $meta_key ] : '';
				}
			}

		}

		if ( $backup_post !== NULL ) {
			$post = $backup_post;
		}

		return $metadata;
	}

	/**
	 * Get the Astra page content setting for archives.
	 *
	 * @param $layout
	 *
	 * @return mixed
	 */
	public static function astra_get_content_layout( $layout ) {
		global $wp_query;
		$page_id = isset( $wp_query->post->ID ) ? $wp_query->post->ID : '';
		if ( $page_id && geodir_archive_page_id() == $page_id ) {
			$page_layout = get_post_meta( $page_id, 'site-content-layout', true );
			if ( $page_layout != '' ) {
				$layout = $page_layout;
			}
		}

		return $layout;
	}

	/**
	 * Get the Astra page sidebar setting for archives.
	 *
	 * @param $layout
	 *
	 * @return mixed
	 */
	public static function astra_page_layout( $layout ) {
		global $wp_query;
		$page_id = isset( $wp_query->post->ID ) ? $wp_query->post->ID : '';
		if ( $page_id && (geodir_archive_page_id() == $page_id || geodir_search_page_id() == $page_id )) {
			$page_layout = get_post_meta( $page_id, 'site-sidebar-layout', true );
			if ( $page_layout != '' ) {
				$layout = $page_layout;
			}
		}

		return $layout;
	}

	/**
	 * Astra theme setup page title.
	 *
	 * @param $wp
	 *
	 * @return mixed
	 */
	public static function astra_wp( $wp = array() ) {
		global $wp_query;

		if ( function_exists( 'astra_the_title' ) ) {
			$post_id = ! empty( $wp_query->queried_object ) && isset( $wp_query->queried_object->ID ) ? $wp_query->queried_object->ID : '';

			if ( $post_id && geodir_search_page_id() == $post_id ) {
				// Override Search Page Title.
				$title = get_post_meta( $post_id, 'site-post-title', true );

				if ( 'disabled' === $title ) {
					add_filter( 'astra_the_title_enabled', '__return_false', 99 );
				} else {
					add_filter( 'astra_the_search_page_title', array(
						__CLASS__,
						'astra_the_search_page_title'
					), 20, 1 );
				}
			}
		}
	}

	/**
	 * Astra theme filter the search page title.
	 *
	 * @param $title
	 *
	 * @return string
	 */
	public static function astra_the_search_page_title( $title = '' ) {
		return get_the_title();
	}

	/**
	 * Stop the GD loop setup if beaver builder themer is overiding it.
	 *
	 * @param $bypass
	 *
	 * @return bool
	 */
	public static function beaver_builder_loop_bypass( $bypass ) {

		if ( class_exists( 'FLThemeBuilderLayoutData' ) ) {
			$ids = FLThemeBuilderLayoutData::get_current_page_content_ids();

			if ( ! empty( $ids ) ) {
				$bypass = true;
			}
		}

		return $bypass;
	}

	/**
	 * Fix the page title args on Kleo theme.
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function kleo_title_args( $args ) {

		$title = GeoDir_SEO::set_meta();
		if ( $title ) {
			$args['title'] = $title;
		}

		return $args;
	}

	public static function genesis_content_archive( $val ) {

		if ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) {
			$val = 'excerpts';
		}

		return $val;
	}

	/**
	 * Make the GD archive pages use H1 tags for the title.
	 *
	 * @param $wrap
	 *
	 * @return string
	 */
	public static function genesis_entry_title_wrap($wrap){
		if ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) {
			$wrap = 'h1';
		}

		return $wrap;
	}

	/**
	 * @param $api_params
	 * @param $_src
	 *
	 * @return mixed
	 */
	public static function wp_easy_updates( $api_params, $_src ) {

		//@todo until GDv1 auto-updates are retired we need to force beta checks.
		$enabled = 1;// geodir_get_option( 'admin_enable_beta', 1 );

		if ( $enabled && strpos( $_src, 'wpgeodirectory.com' ) !== false ) {
			if ( ! empty( $api_params['update_array'] ) ) {
				foreach ( $api_params['update_array'] as $key => $val ) {
					$api_params['update_array'][ $key ]['beta'] = true;
				}
			}

			$api_params['beta'] = true;

		}

		return $api_params;
	}

	public static function wpseo_disable( $options ) {

		if ( defined( 'WPSEO_VERSION' ) ) {
			$new_options = array(
				array(
					'title' => __( 'Yoast SEO detected', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => geodir_notification( array( 'yoast_detected' => __( 'The Yoast SEO plugin has been detected and will take over the GeoDirectory meta Settings unless disabled below. (titles from here will still be used, but not meta)', 'geodirectory' ) ) ),
					'id'    => 'yoast_detected',
					//'desc_tip' => true,
				),
				array(
					'name'    => __( 'Disable Yoast', 'geodirectory' ),
					'desc'    => __( 'Disable overwrite by Yoast SEO titles & metas on GD pages?', 'geodirectory' ),
					'id'      => 'wpseo_disable',
					'type'    => 'checkbox',
					'default' => '0',
				),
				array( 'type' => 'sectionend', 'id' => 'yoast_detected' )
			);

			array_splice( $options, 1, 0, $new_options ); // splice in at position 1
		}


		return $options;
	}

	/**
	 * Add our own tags to ninja forms.
	 *
	 * @since 2.0.0
	 */
	public static function ninja_forms() {
		require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-ninja-forms.php' );
		Ninja_Forms()->merge_tags['geodirectory'] = new GeoDir_Ninja_Forms_MergeTags();
	}

	/**
	 * Theme single template compatibility check.
	 *
	 * @since 2.0.0
	 *
	 * @param string $theme
	 *
	 * @return mixed|string
	 */
	public static function theme_single_template( $theme = '' ) {


		if ( ! $theme ) {
			$theme = get_template();
		}

//		echo '###'.$theme;

		$themes = array();

		if ( get_theme_support( 'geodirectory' ) ) {
			//$themes[ $theme ] = 'geodirectory.php';
		} else {
			$themes = array(
				'twentyseventeen' => 'single.php',
				'primer'          => 'page.php',
			);
		}


		return isset( $themes[ $theme ] ) ? $themes[ $theme ] : '';
	}


	/**
	 * Fix some layut issues with genesis coroporate pro theme.
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public static function genesis_corporate_pro_body_class( $classes ) {

		if ( geodir_is_geodir_page() ) {
			if ( ( $key = array_search( 'archive', $classes ) ) !== false ) {
				unset( $classes[ $key ] );
			}
			$classes[] = 'page';
		}

		return $classes;
	}

	/**
	 * Primer Title.
	 *
	 * @since 2.0.0
	 *
	 * @param string $title Title.
	 *
	 * @return string $title.
	 */
	public static function primer_title( $title ) {

		if ( geodir_is_page( 'single' ) ) {
			$title = get_the_title();
		}

		return $title;
	}


	/**
	 * Add category meta image in Yoast SEO taxonomy data.
	 *
	 * @since 1.6.9
	 *
	 * @global object $wp_query WordPress Query object.
	 *
	 * @param array $value Taxonomy meta value.
	 * @param string $option Option name.
	 *
	 * @return mixed The taxonomy option value.
	 */
	public static function wpseo_taxonomy_meta( $value, $option = '' ) {
		global $wp_query;

		if ( ! empty( $value ) && ( is_category() || is_tax() ) ) {
			$term = $wp_query->get_queried_object();

			if ( ! empty( $term->term_id ) && ! empty( $term->taxonomy ) && isset( $value[ $term->taxonomy ][ $term->term_id ] ) && geodir_is_gd_taxonomy( $term->taxonomy ) ) {
				$image = geodir_get_cat_image( $term->term_id, true );

				if ( ! empty( $image ) ) {
					$value[ $term->taxonomy ][ $term->term_id ]['wpseo_twitter-image']   = $image;
					$value[ $term->taxonomy ][ $term->term_id ]['wpseo_opengraph-image'] = $image;
				}
			}
		}

		return $value;
	}


	/**
	 * Disable Disqus plugin on the fly when visiting GeoDirectory post types.
	 *
	 * @since 1.5.0
	 * @package GeoDirectory
	 *
	 * @param string $disqus_active Hook called before DB call for option so this is empty.
	 *
	 * @return string `1` if active `0` if disabled.
	 */
	public static function disqus_active( $disqus_active ) {
		global $post;
		$all_postypes = geodir_get_posttypes();

		if ( isset( $post->post_type ) && is_array( $all_postypes ) && in_array( $post->post_type, $all_postypes ) ) {
			$disqus_active = '0';
		}

		return $disqus_active;
	}


	/**
	 * Disable JetPack comments on GD post types.
	 *
	 * @since 1.6.21
	 */
	public static function jetpack() {
		//only run if jetpack installed
		if ( defined( 'JETPACK__VERSION' ) ) {
			$post_types = geodir_get_posttypes();
			foreach ( $post_types as $post_type ) {
				add_filter( 'jetpack_comment_form_enabled_for_' . $post_type, '__return_false' );
			}
		}
	}


	/*######################################################
		Boss. (BuddyBoss)
	######################################################*/

	/**
	 * Disable their select boxes as they break ours.
	 *
	 * @since 2.0.0
	 *
	 */
	public static function buddyboss() {
		wp_dequeue_script( 'selectboxes' );
	}

	/**
	 * Add `page` body class on GD pages to help with padding.
	 *
	 * @since 2.0.0
	 *
	 * @param $classes
	 *
	 * @return array
	 */
	public static function buddyboss_body_class( $classes ) {

		if ( geodir_is_geodir_page() ) {
			$classes[] = 'page';
		}

		return $classes;
	}

	/**
	 * Setup theme compatibility hooks.
	 *
	 * @since 2.0.0
	 */
	public static function template_redirect() {
		// Set Avada theme title bar
		if ( class_exists( 'FusionBuilder' ) && geodir_is_geodir_page() ) {
			add_action( 'avada_override_current_page_title_bar', array( __CLASS__, 'avada_override_current_page_title_bar' ), 10, 1 );
		}
	}

	/**
	 * Set Avada theme title bar.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Current post id.
	 */
	public static function avada_override_current_page_title_bar( $post_id ) {
		$page_title_bar_contents = avada_get_page_title_bar_contents( $post_id );
		$page_title              = get_post_meta( $post_id, 'pyre_page_title', true );

		// Which TO to check for.
		$page_title_option = Avada()->settings->get( 'page_title_bar' );

		if ( 'hide' !== $page_title_option ) {
			$title = GeoDir_SEO::set_meta();

			avada_page_title_bar( $title, $page_title_bar_contents[1], $page_title_bar_contents[2] );
		}
	}

}
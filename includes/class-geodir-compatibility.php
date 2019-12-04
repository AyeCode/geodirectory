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
		Rank Math SEO
		######################################################*/
		// add setting to be able to disable Rank Math on GD pages
		add_filter( 'geodir_seo_options', array( __CLASS__, 'rank_math_disable' ), 10 );
		add_filter( 'rank_math/sitemap/urlimages', array( __CLASS__, 'rank_math_add_images_to_sitemap' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_rank_math_disable' ), 20 );

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
			add_filter( 'geodir_bypass_archive_item_template_content', array( __CLASS__, 'beaver_archive_item_template_content' ), 10, 3 );
		}

		/*######################################################
		Beaver Themer
		######################################################*/
		if ( class_exists( 'FLThemeBuilderLoader' ) ) {
			add_filter( 'geodir_page_options', array( __CLASS__, 'fl_theme_builder_page_options' ), 100, 1 );
			add_filter( 'fl_theme_builder_current_page_layouts', array( __CLASS__, 'fl_theme_builder_current_page_layouts' ), 1, 1 );
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

		if ( ! is_admin() ) {
			// Avada (theme)
			add_filter( 'avada_has_sidebar', array( __CLASS__, 'avada_has_sidebar' ), 100, 3 );
			add_filter( 'avada_has_double_sidebars', array( __CLASS__, 'avada_has_double_sidebars' ), 100, 3 );
			add_filter( 'avada_setting_get_posts_global_sidebar', array( __CLASS__, 'avada_global_sidebar' ), 100, 1 );
			add_filter( 'avada_setting_get_posts_sidebar', array( __CLASS__, 'avada_sidebar' ), 100, 1 );
			add_filter( 'avada_setting_get_posts_sidebar_2', array( __CLASS__, 'avada_sidebar_2' ), 100, 1 );
			add_filter( 'avada_setting_get_blog_archive_sidebar', array( __CLASS__, 'avada_sidebar' ), 100, 1 );
			add_filter( 'avada_setting_get_blog_archive_sidebar_2', array( __CLASS__, 'avada_sidebar_2' ), 100, 1 );
			add_filter( 'avada_setting_get_blog_sidebar_position', array( __CLASS__, 'avada_sidebar_position' ), 100, 1 );
			add_filter( 'avada_setting_get_search_sidebar', array( __CLASS__, 'avada_sidebar' ), 100, 1 );
			add_filter( 'avada_setting_get_search_sidebar_2', array( __CLASS__, 'avada_sidebar_2' ), 100, 1 );
			add_filter( 'avada_setting_get_search_sidebar_position', array( __CLASS__, 'avada_sidebar_position' ), 100, 1 );
			add_filter( 'avada_setting_get_sidebar_sticky', array( __CLASS__, 'avada_sidebar_sticky' ), 100, 1 );

			// Fusion Builder (Avada)
			if ( defined( 'FUSION_BUILDER_VERSION' ) ) {
				// GD Listings
				add_filter( 'geodir_before_template_part',array( __CLASS__, 'avada_get_temp_globals' ), 10);
				add_filter( 'geodir_after_template_part',array( __CLASS__, 'avada_set_temp_globals' ), 10);
				// GD Loop
				add_filter( 'geodir_before_get_template_part',array( __CLASS__, 'avada_get_temp_globals' ), 10);
				add_filter( 'geodir_after_get_template_part',array( __CLASS__, 'avada_set_temp_globals' ), 10);
			}
		}
	}

	/**
	 * Set temp globals before looping listings template so we can reset them to proper values after looping.
	 *
	 * @param $template_name
	 */
	public static function avada_get_temp_globals($template_name){
		if($template_name == 'content-widget-listing.php' || basename($template_name) == 'content-archive-listing.php'){
			global $columns, $global_column_array, $fb_temp_columns, $fb_temp_global_column_array;
			$fb_temp_columns =  $columns;
			$fb_temp_global_column_array = $global_column_array;
		}
	}

	/**
	 * Reset globals after looping listings template so we can reset them to proper values after looping.
	 *
	 * @param $template_name
	 */
	public static function avada_set_temp_globals($template_name){
		if($template_name == 'content-widget-listing.php' || basename($template_name) == 'content-archive-listing.php'){
			global $columns, $global_column_array, $fb_temp_columns, $fb_temp_global_column_array;
			$columns = $fb_temp_columns;
			$global_column_array = $fb_temp_global_column_array;
		}
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
		MightyMag
		######################################################*/
		if ( ! is_admin() &&
		     (
			     function_exists( 'x_get_view' )
			     || defined( 'TD_THEME_VERSION' )
				 || function_exists( 'pi_elv_include_scripts' )
				 || ( ( function_exists( 'mfn_body_classes' ) && function_exists( 'mfn_ID' ) ) )
				 || function_exists( 'mgm_setup' ) 
				 || function_exists( 'genesis_theme_support' ) 
				 || function_exists( 'ffmp_setup' ) // ffmp theme
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
			add_filter( 'avf_title_args', array( __CLASS__, 'enfold_avf_title_args' ), 10, 2 );
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

		// Enfold theme
		if ( function_exists( 'avia_get_option' ) ) {
			if ( strpos( $meta_key, '_avia_' ) === 0 || strpos( $meta_key, '_aviaLayout' ) === 0 ) {
				$gen_keys[] = $meta_key;
			}
			$gen_keys[] = 'header';
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

		// Unicon / GeneratePress
		if ( ( ( function_exists( 'minti_register_required_plugins' ) && ( strpos( $meta_key, 'minti_' ) === 0 || empty( $meta_key ) ) )
			 || ( defined( 'GENERATE_VERSION' ) && ( strpos( $meta_key, '_generate-' ) === 0 || empty( $meta_key ) ) )
			 || ( function_exists( 'inc_sidebars_init' ) && ( strpos( $meta_key, '_cs_replacements' ) === 0 || empty( $meta_key ) ) ) // custom sidebars plugin
			 || ( function_exists( 'et_divi_load_scripts_styles' ) && ( strpos( $meta_key, '_et_' ) === 0 || empty( $meta_key ) ) ) // Divi
			 || ( function_exists( 'tie_admin_bar' ) && ( strpos( $meta_key, 'tie_' ) === 0 || in_array( $meta_key, array( 'post_color', 'post_background', 'post_background_full' ) ) || empty( $meta_key ) ) ) // Jarida
			 || ( function_exists( 'mk_build_main_wrapper' ) && ( empty( $meta_key ) || strpos( $meta_key, '_widget_' ) === 0 || in_array( $meta_key, array( '_layout', '_template', '_padding', 'page_preloader', '_introduce_align', '_custom_page_title', '_page_introduce_subtitle', '_disable_breadcrumb', 'menu_location', '_sidebar' ) ) ) ) // Jupiter
			 || ( defined( 'TD_THEME_VERSION' ) && ( empty( $meta_key ) || strpos( $meta_key, 'td_' ) === 0 ) ) // Newspaper
			 || ( function_exists( 'genesis_theme_support' ) && ( strpos( $meta_key, '_genesis_' ) === 0 || empty( $meta_key ) ) && ! in_array( $meta_key, array( '_genesis_title', '_genesis_description', '_genesis_keywords' ) ) ) // Genesis
			 || ( class_exists( 'The7_Aoutoloader' ) && ( strpos( $meta_key, '_dt_' ) === 0 || empty( $meta_key ) ) ) // The7
			 || ( function_exists( 'avia_get_option' ) && ( ! empty( $meta_key ) && in_array( $meta_key, $gen_keys ) ) ) // Enfold
			 || ( class_exists( 'Avada' ) && class_exists( 'FusionBuilder' ) && ( strpos( $meta_key, 'pyre_' ) === 0 || strpos( $meta_key, 'sbg_' ) === 0 || empty( $meta_key ) ) ) // Avada + FusionBuilder
			 ) && geodir_is_gd_post_type( get_post_type( $object_id ) ) ) {
			if ( geodir_is_page( 'detail' ) ) {
				$template_page_id = geodir_details_page_id( get_post_type( $object_id ) );
			} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
				$template_page_id = geodir_archive_page_id( get_post_type( $object_id ) );
			} else if ( geodir_is_page( 'search' ) ) {
				$template_page_id = geodir_search_page_id();
			} else {
				$template_page_id = 0;
			}

			if ( $meta_key == '_cs_replacements' ) {
				$single = false;
			}

			if ( ! empty( $template_page_id ) ) {
				if ( empty( $meta_key ) ) {
					// Don't overwrite Yoast SEO meta for the individual post.
					$reserve_post_meta = defined( 'WPSEO_VERSION' ) && ! geodir_get_option( 'wpseo_disable' ) && geodir_is_page( 'detail' ) ? true : false;

					// Don't overwrite Rank Math SEO meta for the individual post.
					$reserve_post_meta = defined( 'RANK_MATH_VERSION' ) && ! geodir_get_option( 'rank_math_disable' ) && geodir_is_page( 'detail' ) ? true : $reserve_post_meta;

					if ( $reserve_post_meta ) {
						global $gd_post_metadata;
						if ( $gd_post_metadata ) {
							return null;
						} else {
							$gd_post_metadata = true;
							$reserve_meta = get_post_meta( $object_id, '', $single );
							$gd_post_metadata = false;
						}
					}

					$metadata = get_post_custom( $template_page_id );

					if ( $reserve_post_meta ) {
						if ( ! empty( $reserve_meta ) ) {
							foreach ( $reserve_meta as $key => $meta ) {
								if ( strpos( $key, '_yoast_wpseo_' ) === 0 ) {
									$metadata[ $key ] = $meta;
								}
							}
						}
					}
				} else {
					$metadata = get_post_meta( $template_page_id, $meta_key );
					if ( $single && is_array( $metadata ) && empty( $metadata ) ) {
						$metadata = '';
					}						
				}
				return $metadata;
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

	public static function rank_math_disable( $options ) {

		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$new_options = array(
				array(
					'title' => __( 'Rank Math SEO detected', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => geodir_notification( array( 'rank_math_detected' => __( 'The Rank Math SEO plugin has been detected and will take over the GeoDirectory meta Settings unless disabled below. (titles from here will still be used, but not meta)', 'geodirectory' ) ) ),
					'id'    => 'rank_math_detected',
					//'desc_tip' => true,
				),
				array(
					'name'    => __( 'Disable Rank Math', 'geodirectory' ),
					'desc'    => __( 'Disable overwrite by Rank Math titles & metas on GD pages?', 'geodirectory' ),
					'id'      => 'rank_math_disable',
					'type'    => 'checkbox',
					'default' => '0',
				),
				array( 'type' => 'sectionend', 'id' => 'rank_math_detected' )
			);

			array_splice( $options, 1, 0, $new_options ); // splice in at position 1
		}


		return $options;
	}

	/**
	 * This method enqueues the code required to make Rank Math recognize our fields
	 */
	public static function enqueue_rank_math_disable() {
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$script = self::get_rank_math_script();
			wp_add_inline_script( 'geodir-add-listing', $script );
		}
	}

	/**
	 * Returns Rank Math compat code
	 */
	public static function get_rank_math_script() {
		ob_start();
		?>
	<script>
		/**
		* Rank Math SEO Integration
		*/
	   ; (function ($) {

		   /**
			* RankMath integration class
			*/
		   var RankMathIntegration = function () {
			   this.init()
			   this.hooks()
		   }
	   
		   /**
			* Init the plugin
			*/
		   RankMathIntegration.prototype.init = function () {
			   this.pluginName = 'geodirectory'
		   }
	   
		   /**
			* Hook into Rank Math App eco-system
			*/
		   RankMathIntegration.prototype.hooks = function () {
			   var self = this
	   
			   RankMathApp.registerPlugin(this.pluginName)
			   wp.hooks.addFilter('rank_math_content', this.pluginName, $.proxy(this.filterContent, this))
			   window.setInterval(function () {
				   RankMathApp.reloadPlugin(self.pluginName)
			   }, 2000);
		   }
	   
		   /**
			* Gather ge specific field data for analysis
			*
			* @return {String}
			*/
		   RankMathIntegration.prototype.getContent = function () {
			   var content = ''
	   
			   //Add images
			   $('.plupload-thumbs img').each(function () {
				   var img = $(this).clone()
				   img.attr('alt', img.data('title'))
				   content += '<p>' + img[0].outerHTML + '.</p>'
			   })
	   
			   //Add textarea fields
			   $('.gd-fieldset-details textarea').each(function () {
				   var val = $(this).val()
				   if (val.length) {
					   content += '<p>' + val + '</p>'
				   }
			   })
	   
			   //Finally, input fields
			   $('input.geodir_textfield').each(function () {
				   var val = $(this).val()
				   var label = $(this).closest('.gd-fieldset-details').find('label').text() + ' - ' + val
	   
				   if ('url' == $(this).attr('type') && val.length) {
					   label = '<a href="' + val + '">' + label + '</a>'
				   }
	   
				   if (val.length) {
					   content += '<p>' + label + '.</p>'
				   }
			   })

			return content
	   
		   }
	   
	   
		   /**
			* Filters rankmat content
			*
			* @param {String} content System content.
			*
			* @return {String} Our plugin content concatenated
			*/
		   RankMathIntegration.prototype.filterContent = function (content) {
			   return content + this.getContent()
		   }
	   
	   
		   /**
			* Start Analysing our Fields.
			*/
		   $(document).on('ready', function () {
			   new RankMathIntegration()
		   })
	   
	   })(jQuery)
	</script>
	   <?php
			$output = ob_get_clean();
			return str_replace( array( '<script>', '</script>' ), '', $output );
	}

	public static function rank_math_add_images_to_sitemap( $images, $id ){

		$post_type = get_post_type( $id );

		if(! geodir_is_gd_post_type( $post_type ) ) {
			return $images;
		}

		$geodir_images = geodir_get_images( $id );

		if( is_array( $geodir_images ) ) {
			foreach( $geodir_images as $geodir_image ) {
				$images[] = array(
					'src'   => geodir_get_image_src( $geodir_image, 'original' ),
					'title' => $geodir_image->title,
				);
			}
		}

		return $images;
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
	 * Fix some layout issues with genesis corporate pro theme.
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
		if ( geodir_is_geodir_page() ) {
			if ( class_exists( 'FusionBuilder' ) ) {
				add_action( 'avada_override_current_page_title_bar', array( __CLASS__, 'avada_override_current_page_title_bar' ), 10, 1 );
			}

			// Avada (theme)
			if ( class_exists( 'Avada' ) ) {
				add_filter( 'body_class', array( __CLASS__, 'avada_body_class' ), 999, 1 );
			}

			// Custom sidebars
			if ( function_exists( 'inc_sidebars_init' ) ) {
				if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
					add_filter( 'cs_replace_sidebars', array( __CLASS__, 'cs_replace_sidebars' ), 20, 2 );
				}
			}

			if ( function_exists( 'mk_build_main_wrapper' ) ) {
				add_filter( 'get_header', array( __CLASS__, 'jupiter_mk_build_init' ), 20, 1 );
			}

			// The7 theme set template page id for GD pages.
			if ( class_exists( 'The7_Aoutoloader' ) ) {
				if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
					add_filter( 'the7_archive_page_template_id', array( __CLASS__, 'the7_archive_page_template_id' ), 20, 1 );
					add_filter( 'presscore_get_page_title', array( __CLASS__, 'presscore_get_page_title' ), 20, 1 );
				}
			}

			if ( function_exists( 'avia_get_option' ) ) {
				add_filter( 'avf_header_setting_filter', array( __CLASS__, 'avf_header_setting_filter' ), 20, 1 );

				if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
					add_filter( 'avia_layout_filter', array( __CLASS__, 'avia_layout_filter' ), 20, 2 );
					add_filter( 'avf_custom_sidebar', array( __CLASS__, 'avf_custom_sidebar' ), 20, 1 );
				}
			}
		}

		// GeneratePress theme compatibility
		if ( defined( 'GENERATE_VERSION' ) ) {
			add_filter( 'generate_sidebar_layout', array( __CLASS__, 'generate_sidebar_layout' ), 10, 1 );
			add_filter( 'generate_footer_widgets', array( __CLASS__, 'generate_footer_widgets' ), 10, 1 );
			add_filter( 'generate_show_title', array( __CLASS__, 'generate_show_title' ), 10, 1 );
			add_filter( 'generate_blog_columns', array( __CLASS__, 'generate_blog_columns' ), 10, 1 );
		}

		// Divi theme compatibility
		if ( function_exists( 'et_divi_load_scripts_styles' ) && geodir_is_geodir_page() ) {
			add_filter( 'et_first_image_use_custom_content', array( __CLASS__, 'divi_et_first_image_use_custom_content' ), 999, 3 );
		}

		// Jarida (theme)
		if ( function_exists( 'tie_admin_bar' ) ) {
			add_filter( 'option_tie_options', array( __CLASS__, 'option_tie_options' ), 20, 3 );
			add_filter( 'wp_super_duper_before_widget', array( __CLASS__, 'jarida_super_duper_before_widget' ), 0, 4 );
			add_filter( 'body_class', array( __CLASS__, 'jarida_body_class' ) );
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

	/**
	 * Filter GeneratePress theme page layout.
	 *
	 * @since 2.0.0.60
	 *
	 * @param string $layout Page layout.
	 * @return string Filtered page layout.
	 */
	public static function generate_sidebar_layout( $layout ) {
		if ( geodir_is_page( 'detail' ) ) {
			$template_page_id = geodir_details_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$template_page_id = geodir_archive_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'search' ) ) {
			$template_page_id = geodir_search_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$layout = get_post_meta( $template_page_id, '_generate-sidebar-layout-meta', true );
		}

		return $layout;
	}

	/**
	 * Filter GeneratePress theme page footer widget sections.
	 *
	 * @since 2.0.0.60
	 *
	 * @param int $widgets The no. of widget sections to display on page footer.
	 * @return int Filtered no. of widget sections to display.
	 */
	public static function generate_footer_widgets( $widgets ) {
		if ( geodir_is_page( 'detail' ) ) {
			$template_page_id = geodir_details_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$template_page_id = geodir_archive_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'search' ) ) {
			$template_page_id = geodir_search_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$widgets = get_post_meta( $template_page_id, '_generate-footer-widget-meta', true );
		}

		return $widgets;
	}

	/**
	 * Filter GeneratePress theme page headline.
	 *
	 * @since 2.0.0.60
	 *
	 * @param string $title The page title.
	 * @return string|bool Filtered title visibility.
	 */
	public static function generate_show_title( $title ) {
		if ( geodir_is_page( 'detail' ) ) {
			$template_page_id = geodir_details_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$template_page_id = geodir_archive_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'search' ) ) {
			$template_page_id = geodir_search_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$disable_title = get_post_meta( $template_page_id, '_generate-disable-headline', true );

			if ( $disable_title ) {
				$title = false;
			}
		}

		return $title;
	}

	/**
	 * Filter GeneratePress theme blog page columns.
	 *
	 * @since 2.0.0.60
	 *
	 * @param int|bool $columns The page columns.
	 * @return string|bool Filtered page columns.
	 */
	public static function generate_blog_columns( $columns ) {
		if ( geodir_is_geodir_page() ) {
			$columns = false;
		}

		return $columns;
	}

	/**
	 * Skip use of custom content for first image on GD page templates.
	 *
	 * @since 2.0.0.63
	 *
	 * @param string|bool $custom.
	 * @param string $content.
	 * @param object $post.
	 * @return string|bool.
	 */
	public static function divi_et_first_image_use_custom_content( $custom, $content, $post ) {
		if ( $custom === false ) {
			$custom = $content;
		}

		return $custom;
	}

	/**
	 * Fix the page title args on Enfold theme.
	 *
	 * @since 2.0.0.63
	 *
	 * @param array $args The title arguments.
	 * @param int $id The ID.
	 *
	 * @return array Filtered title arguments.
	 */
	public static function enfold_avf_title_args( $args, $id ) {
		$title = GeoDir_SEO::set_meta();

		if ( $title ) {
			$args['title'] = $title;
		}

		return $args;
	}

	public static function gd_page_id() {
		global $gd_post;

		$page_id = 0;

		if ( ! geodir_is_geodir_page() ) {
			return $page_id;
		}

		global $gd_post;

		$post_type = ! empty( $gd_post ) && ! empty( $gd_post->ID ) ? get_post_type( $gd_post->ID ) : '';

		if ( geodir_is_page( 'detail' ) ) {
			$page_id = geodir_details_page_id( $post_type );
		} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$page_id = geodir_archive_page_id( $post_type );
		} else if ( geodir_is_page( 'search' ) ) {
			$page_id = geodir_search_page_id();
		}

		return $page_id;
	}

	public static function avada_has_sidebar( $has_sidebar, $body_classes, $class ) {
		if ( geodir_is_geodir_page() && ( $body_classes = self::avada_body_classes() ) ) {
			$has_sidebar = in_array( 'has-sidebar', $body_classes ) ? true : false;
		}

		return $has_sidebar;
	}

	public static function avada_has_double_sidebars( $double_sidebars, $body_classes, $class ) {
		if ( geodir_is_geodir_page() && ( $body_classes = self::avada_body_classes() ) ) {
			$double_sidebars = in_array( 'double-sidebars', $body_classes ) ? true : false;
		}

		return $double_sidebars;
	}

	public static function avada_body_classes() {
		$classes = array();

		$page_id = (int) self::gd_page_id();

		if ( empty( $page_id ) ) {
			return $classes;
		}

		$c_page_id = $page_id;
		$sidebar_1 = self::avada_sidebar_context( $c_page_id, 1 );
		$sidebar_2 = self::avada_sidebar_context( $c_page_id, 2 );

		$page_bg_layout = get_post_meta( $c_page_id, 'pyre_page_bg_layout', true );
		if ( ( 'Boxed' === Avada()->settings->get( 'layout' ) && ( ! $page_bg_layout || 'default' === $page_bg_layout ) ) || 'boxed' === $page_bg_layout ) {
			$classes[] = 'layout-boxed-mode';
			$classes[] = 'layout-boxed-mode-' . Avada()->settings->get( 'scroll_offset' );
		} else {
			$classes[] = 'layout-wide-mode';
		}

		if ( is_array( $sidebar_1 ) && ! empty( $sidebar_1 ) && ( $sidebar_1[0] || '0' == $sidebar_1[0] ) && ! is_page_template( '100-width.php' ) && ! is_page_template( 'blank.php' ) ) {
			$classes[] = 'has-sidebar';
		}

		if ( is_array( $sidebar_1 ) && $sidebar_1[0] && is_array( $sidebar_2 ) && $sidebar_2[0] && ! is_page_template( '100-width.php' ) && ! is_page_template( 'blank.php' ) ) {
			$classes[] = 'double-sidebars';
		}

		if ( is_page_template( 'side-navigation.php' ) && 0 !== get_queried_object_id() ) {
			$classes[] = 'has-sidebar';

			if ( is_array( $sidebar_2 ) && $sidebar_2[0] ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( is_archive() || is_search() ) {
			if ( 'None' !== $sidebar_1 && ( ( is_array( $sidebar_1 ) && $sidebar_1[0] !== '' ) || ( ! is_array( $sidebar_1 ) && $sidebar_1 !== '' ) ) ) {
				$classes[] = 'has-sidebar';
			}
			if ( 'None' !== $sidebar_1 && 'None' !== $sidebar_2 && ( ( is_array( $sidebar_1 ) && $sidebar_1[0] !== '' ) || ( ! is_array( $sidebar_1 ) && $sidebar_1 !== '' ) ) && ( ( is_array( $sidebar_2 ) && $sidebar_2[0] !== '' ) || ( ! is_array( $sidebar_2 ) && $sidebar_2 !== '' ) ) ) {
				$classes[] = 'double-sidebars';
			}
		}

		if ( 'no' !== get_post_meta( $c_page_id, 'pyre_display_header', true ) ) {
			if ( 'Left' === Avada()->settings->get( 'header_position' ) || 'Right' === Avada()->settings->get( 'header_position' ) ) {
				$classes[] = 'side-header';
			} else {
				$classes[] = 'fusion-top-header';
			}
			if ( 'Left' === Avada()->settings->get( 'header_position' ) ) {
				$classes[] = 'side-header-left';
			} elseif ( 'Right' === Avada()->settings->get( 'header_position' ) ) {
				$classes[] = 'side-header-right';
			}
			$classes[] = 'menu-text-align-' . strtolower( Avada()->settings->get( 'menu_text_align' ) );
		}

		return array_unique( $classes );
	}

	public static function avada_sidebar_context( $c_page_id, $sidebar = 1 ) {
		$sidebar_1 = get_post_meta( $c_page_id, 'sbg_selected_sidebar_replacement', true );
		$sidebar_2 = get_post_meta( $c_page_id, 'sbg_selected_sidebar_2_replacement', true );

		if ( Avada()->settings->get( 'pages_global_sidebar' ) ) {
			$sidebar_1 = ( 'None' !== Avada()->settings->get( 'pages_sidebar' ) ) ? array( Avada()->settings->get( 'pages_sidebar' ) ) : '';
			$sidebar_2 = ( 'None' !== Avada()->settings->get( 'pages_sidebar_2' ) ) ? array( Avada()->settings->get( 'pages_sidebar_2' ) ) : '';
		} else {
			if ( isset( $sidebar_1[0] ) && 'default_sidebar' === $sidebar_1[0] ) {
				$sidebar_1 = array( ( 'None' !== Avada()->settings->get( 'pages_sidebar' ) ) ? Avada()->settings->get( 'pages_sidebar' ) : '' );
			}

			if ( isset( $sidebar_2[0] ) && 'default_sidebar' === $sidebar_2[0] ) {
				$sidebar_2 = array( ( 'None' !== Avada()->settings->get( 'pages_sidebar_2' ) ) ? Avada()->settings->get( 'pages_sidebar_2' ) : '' );
			}
		}

		if ( 1 == $sidebar ) {
			return $sidebar_1;
		} elseif ( 2 == $sidebar ) {
			return $sidebar_2;
		}
	}

	public static function avada_body_class( $classes ) {
		$body_classes = self::avada_body_classes();

		if ( ! empty( $body_classes ) && ! empty( $classes ) ) {
			$new_classes = array();
			$check_classes = array( 'layout-boxed-mode', 'layout-wide-mode', 'has-sidebar', 'double-sidebars', 'fusion-top-header', 'side-header-left', 'side-header-right' );

			foreach ( $classes as $class ) {
				if ( in_array( $class, $check_classes ) || strpos( $class, 'layout-boxed-mode-' ) === 0 || strpos( $class, 'menu-text-align-' ) === 0 ) {
					continue;
				}
				$new_classes[] = $class;
			}

			$classes = array_merge( $new_classes, $body_classes );
		}

		return $classes;
	}

	public static function avada_global_sidebar( $value ) {
		return Avada()->settings->get( 'pages_global_sidebar' );
	}

	public static function avada_sidebar( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			$meta = get_post_meta( $page_id, 'sbg_selected_sidebar_replacement', true );

			$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
			if ( ! empty( $meta ) ) {
				$value = $meta;
			}
		}
		return $value;
	}

	public static function avada_sidebar_2( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			$meta = get_post_meta( $page_id, 'sbg_selected_sidebar_2_replacement', true );

			$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
			if ( ! empty( $meta ) ) {
				$value = $meta;
			}
		}
		return $value;
	}

	public static function avada_sidebar_position( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			$meta = get_post_meta( $page_id, 'pyre_sidebar_position', true );

			$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
			if ( ! empty( $meta ) ) {
				$value = $meta;
			}
		}
		return $value;
	}

	public static function avada_sidebar_sticky( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			$meta = get_post_meta( $page_id, 'pyre_sidebar_sticky', true );

			$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
			if ( ! empty( $meta ) ) {
				$value = $meta;
			}
		}
		return $value;
	}

	/**
	 * Beaver Builder render archive item layout.
	 *
	 * @since 2.0.0.64
	 *
	 * @param string $content The page layout content.
	 * @param string $original_content The page layout original content.
	 * @param int $page_id The page id.
	 * @return string Filtered page layout content.
	 */
	public static function beaver_archive_item_template_content( $content, $original_content, $page_id ) {
		$enabled = FLBuilderModel::is_builder_enabled( $page_id );

		if ( $enabled ) {
			$rendering = $page_id === FLBuilder::$post_rendering;

			// Allow the builder's render_content filter to run again.
			add_filter( 'fl_builder_do_render_content', '__return_true', 11 );

			$do_render = apply_filters( 'fl_builder_do_render_content', true, $page_id );

			if ( ! $rendering && $do_render ) {
				// Set the post rendering ID.
				FLBuilder::$post_rendering = $page_id;

				// Render the content.
				ob_start();
				// Enqueue styles and scripts for this post.
				FLBuilder::enqueue_layout_styles_scripts_by_id( $page_id );
				FLBuilder::render_content_by_id( $page_id );
				$content = ob_get_clean();

				// Clear the post rendering ID.
				FLBuilder::$post_rendering = null;
			}

			remove_filter( 'fl_builder_do_render_content', '__return_true', 11 );
		}

		return $content;
	}

	/**
	 * Jarida theme filter sidebar option value for GD archive page.
	 *
	 * @since 2.0.0.64
	 *
	 * @param bool|mixed $value Option value.
	 * @param string $option Option name.
	 * @return mixed Filtered option value.
	 */
	public static function option_tie_options( $value, $option ) {
		if ( ! geodir_is_geodir_page() ) {
			return $value;
		}

		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) && ( $page_id = (int) self::gd_page_id() ) ) {
			$sidebar_pos = get_post_meta( $page_id, 'tie_sidebar_pos', true );
			$value['sidebar_pos'] = is_array( $sidebar_pos ) ? $sidebar_pos[0] : $sidebar_pos;

			if ( $value['sidebar_pos'] == 'default' ) {
			} elseif ( $value['sidebar_pos'] == 'full' ) {
				$value['sidebar_archive'] = 'none';
				$value['sidebar_narrow_archive'] = 'none';
			} else {
				$sidebar_archive = get_post_meta( $page_id, 'tie_sidebar_post', true );
				$value['sidebar_archive'] = is_array( $sidebar_archive ) ? $sidebar_archive[0] : $sidebar_archive;

				$sidebar_narrow_archive = get_post_meta( $page_id, 'tie_sidebar_narrow_post', true );
				$value['sidebar_narrow_archive'] = is_array( $sidebar_narrow_archive ) ? $sidebar_narrow_archive[0] : $sidebar_narrow_archive;
			}
		}

		return $value;
	}

	/**
	 * Jarida theme before widget content.
	 *
	 * Jarida theme widget appends & prepends div tags even empty widget title.
	 * This cause extra div tag to widget content rendered via super duper.
	 *
	 * @since 2.0.0.64
	 *
	 * @param string $before_widget HTML content to prepend to each widget.
	 * @param array $args Widget arguments.
	 * @param array $instance Widget parameters.
	 * @param object $super_duper Super Duper widget class.
	 * @return string Filter the content prepend to widget.
	 */
	public static function jarida_super_duper_before_widget( $before_widget, $args, $instance, $super_duper ) {
		if ( empty( $instance['title'] ) && ! empty( $args['after_widget'] ) && $args['after_widget'] == '</div></div><!-- .widget /-->' ) {
			$before_widget .= '<div class="widget-container">';
		}

		return $before_widget;
	}

	public static function jarida_body_class( $classes ) {
		if ( geodir_is_geodir_page() && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) && ( $page_id = (int) self::gd_page_id() ) ) {
			$sidebar_pos = get_post_meta( $page_id, 'tie_sidebar_pos', true );
			$sidebar_pos = is_array( $sidebar_pos ) ? $sidebar_pos[0] : $sidebar_pos;

			if ( $sidebar_pos == 'full' ) {
				$classes[] = 'gd-jarida-full';
			}
		}

		return $classes;
	}

	/**
	 * Custom sidebars filter post type for GD Archive pages.
	 *
	 * @since 2.0.0.65
	 */
	public static function cs_replace_post_type( $post_type, $type ) {
		$post_type = geodir_get_current_posttype();

		return $post_type;
	}

	/**
	 * Filter the replaced custom sidebars.
	 *
	 * @since 2.0.0.65
	 *
	 * @param array $replacements List of the final/replaced sidebars.
	 * @param array $options Custom Sidebars settings.
	 */
	public static function cs_replace_sidebars( $replacements, $options ) {
		$page_id = (int) self::gd_page_id();

		if ( empty( $page_id ) ) {
			return $replacements;
		}

		$sidebars = CustomSidebars::get_options( 'modifiable' );

		// Check if replacements are defined in the post metadata.
		$reps = get_post_meta( $page_id, '_cs_replacements', true );
		foreach ( $sidebars as $sb_id ) {
			if ( is_array( $reps ) && ! empty( $reps[ $sb_id ] ) ) {
				$replacements[ $sb_id ] = array(
					$reps[ $sb_id ],
					'particular',
					-1,
				);
			}
		}

		return $replacements;
	}

	public static function jupiter_mk_build_init( $name ) {
		$page_id = self::gd_page_id();

		if ( empty( $page_id ) ) {
			return;
		}

		// Layout
		$layout = get_post_meta( $page_id, '_layout', true );
		$_REQUEST['layout'] = $layout;
	}

	/**
	 * The7 theme filter GD archive page template ID.
	 *
	 * @since 2.0.0.66
	 *
	 * @param int $page_id The page ID.
	 * @return int The page ID.
	 */
	public static function the7_archive_page_template_id( $page_id ) {
		$page_id = self::gd_page_id();

		return $page_id;
	}

	/**
	 * The7 theme filter GD page title.
	 *
	 * @since 2.0.0.66
	 *
	 * @param string $page_title The page title.
	 * @return string The page title.
	 */
	public static function presscore_get_page_title( $page_title ) {
		$title = GeoDir_SEO::set_meta();

		if ( $title ) {
			$page_title = $title;
		}

		return $page_title;
	}

	/**
	 * Filter Enfold theme GD page header settings.
	 *
	 * @since 2.0.0.68
	 *
	 * @param array $header The header settings.
	 * @return array The header settings.
	 */
	public static function avf_header_setting_filter( $header ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			$header['header_title_bar'] = get_post_meta( $page_id, 'header_title_bar', true );
		}

		return $header;
	}

	/**
	 * Filter Enfold theme GD archive & search page layout.
	 *
	 * @since 2.0.0.68
	 *
	 * @param string $layout The page layout.
	 * @param int $post_id The post ID.
	 * @return string The page layout.
	 */
	public static function avia_layout_filter( $layout, $post_id ) {
		global $avia_config;

		if ( geodir_is_gd_post_type( get_post_type( $post_id ) ) ) {
			if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
				$template_page_id = geodir_archive_page_id( get_post_type( $post_id ) );
			} else if ( geodir_is_page( 'search' ) ) {
				$template_page_id = geodir_search_page_id();
			} else {
				$template_page_id = 0;
			}

			if ( ! empty( $template_page_id ) ) {
				$_layout = get_post_meta( $template_page_id, 'layout', true );

				if ( ! empty( $_layout ) ) {
					$layout['current'] = $avia_config['layout'][$_layout];
					$layout['current']['main'] = $_layout;
				}
			}
		}

		return $layout;
	}

	/**
	 * Filter Enfold theme GD archive & search page sidebar.
	 *
	 * @since 2.0.0.68
	 *
	 * @param string $custom_sidebar The page sidebar.
	 * @return string The page sidebar.
	 */
	public static function avf_custom_sidebar( $custom_sidebar ) {
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$template_page_id = geodir_archive_page_id( geodir_get_current_posttype() );
		} else if ( geodir_is_page( 'search' ) ) {
			$template_page_id = geodir_search_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$custom_sidebar = get_post_meta( $template_page_id, 'sidebar', true );
		}

		return $custom_sidebar;
	}

	/**
	 * Add option to select Beaver Themer search page.
	 *
	 * @since 2.0.0.70
	 *
	 * @param array $options The page options.
	 * @return array The page options.
	 */
	public static function fl_theme_builder_page_options( $options ) {
		global $wpdb;

		$layouts = array( 
			'0' => __( 'Select Themer Layout', 'geodirectory' ) 
		);

		$results = $wpdb->get_results( "SELECT p.ID, p.post_title FROM {$wpdb->postmeta} as pm INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID WHERE pm.meta_key = '_fl_theme_builder_preview_location' AND p.post_type = 'fl-theme-layout' AND p.post_status = 'publish' AND pm.meta_value LIKE '%archive:post%' ORDER BY `p`.`post_title` ASC" );
		if ( ! empty( $results ) ) {
			foreach ( $results as $key => $row ) {
				$layouts[ $row->ID ] = $row->post_title . '( ' . $row->ID . ' )';
			}
		}

		$options[] = array(
			'title' => __( 'Beaver Builder Settings', 'geodirectory' ),
			'type'  => 'title',
			'desc'  => 'Beaver Builder template settings.',
			'id'    => 'fl_theme_builder_settings',
			'desc_tip' => true,
		);
		$options[] = array(
			'name' => __( 'Search Page Themer Layout', 'geodirectory' ),
			'desc' => __( 'To use themer layout for GD search page, create a layout with blank location under Beaver Builder > Themer Layouts > Add New > Archive Layout.', 'geodirectory' ),
			'id' => 'fl_theme_builder_search_layout',
			'default' => '0',
			'type' => 'select',
			'class' => 'geodir-select',
			'options' => $layouts,
			'desc_tip' => true,
		);
		$options[] = array( 
			'type' => 'sectionend', 
			'id' => 'fl_theme_builder_settings' 
		);

		return $options;
	}

	/**
	 * Filter Beaver Themer layouts.
	 *
	 * @since 2.0.0.70
	 *
	 * @param array $layouts The themer layouts.
	 * @return array The themer layouts.
	 */
	public static function fl_theme_builder_current_page_layouts( $layouts = array() ) {
		if ( geodir_is_page( 'search' ) ) {
			$layout_id = absint( geodir_get_option( 'fl_theme_builder_search_layout' ) );
			$layout_id = apply_filters( 'geodir_fl_theme_builder_search_layout_id', $layout_id, $layouts );
			if ( $layout_id && get_post_status( $layout_id ) != 'publish' ) {
				$layout_id = 0;
			}

			if ( ! empty( $layouts ) ) {
				foreach ( $layouts as $type => $posts ) {
					foreach ( $posts as $key => $post ) {
						if ( ! empty( $post['id'] ) && absint( $post['id'] ) == $layout_id ) {
							continue;
						}

						if ( ! empty( $post['locations'] ) && is_array( $post['locations'] ) && in_array( 'general:search', $post['locations'] ) ) {
							unset( $layouts[ $type ][ $key ] );
						}
					}
				}
			}

			if ( ! empty( $layout_id ) ) {
				$layouts[ 'archive' ][0] = array(
					'id' => $layout_id,
					'locations' => array( 'general:search' ),
					'type' => 'archive',
					'hook' => '',
					'order' => 0
				);
			}
		}

		return $layouts;
	}
}

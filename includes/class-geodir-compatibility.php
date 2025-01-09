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
		add_action( 'plugins_loaded', array( __CLASS__, 'plugins_loaded' ) );
		add_action( 'cron_schedules', array( __CLASS__, 'cron_schedules' ), 10, 1 );

		/*######################################################
		Yoast (WP SEO)
		######################################################*/
		add_filter( 'option_wpseo_taxonomy_meta', array( __CLASS__, 'wpseo_taxonomy_meta' ), 10, 2 );
		// add setting to be able to disable yoast on GD pages
		add_filter( 'Yoast\WP\SEO\prominent_words_post_types', array( __CLASS__, 'wpseo_prominent_words_post_types' ), 20, 1 );
		add_filter( 'rank_math/opengraph/url', array( __CLASS__, 'rank_math_location_url_callback' ), 10 );
		add_action( 'rank_math/opengraph/facebook/add_additional_images', array( __CLASS__, 'rank_math_cat_image' ), 10 );
		add_action( 'rank_math/opengraph/twitter/add_additional_images', array( __CLASS__, 'rank_math_cat_image' ), 10 );

		/*######################################################
		Rank Math SEO
		######################################################*/
		// add setting to be able to disable Rank Math on GD pages
		add_filter( 'rank_math/sitemap/urlimages', array( __CLASS__, 'rank_math_add_images_to_sitemap' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_rank_math_disable' ), 20 );

		// SEO Plugin Options
		add_filter( 'geodir_seo_options', array( __CLASS__, 'seo_plugin_options' ), 10, 1 );

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
			add_filter( 'fl_builder_do_render_content', array( __CLASS__, 'beaver_builder_do_render_content' ), 20, 2 );
		}

		/*######################################################
		Beaver Themer
		######################################################*/
		if ( class_exists( 'FLThemeBuilderLoader' ) ) {
			add_filter( 'geodir_page_options', array( __CLASS__, 'fl_theme_builder_page_options' ), 100, 1 );
			add_filter( 'fl_theme_builder_current_page_layouts', array( __CLASS__, 'fl_theme_builder_current_page_layouts' ), 1, 1 );
			add_filter( 'fl_theme_builder_page_archive_get_title', array( __CLASS__, 'fl_theme_builder_page_archive_get_title' ), 1, 1 );
		}

		/*######################################################
		Elementor :: Fix Page templates.
		######################################################*/
		add_filter( 'geodir_bypass_setup_archive_loop_as_page', array( __CLASS__, 'elementor_loop_bypass' ) );
		add_filter( 'geodir_bypass_setup_singular_page', array( __CLASS__, 'elementor_loop_bypass' ) );

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
		add_filter( 'astra_is_content_layout_boxed', array( __CLASS__, 'astra_is_content_layout_boxed' ), 10, 1 );
		add_filter( 'astra_is_sidebar_layout_boxed', array( __CLASS__, 'astra_is_sidebar_layout_boxed' ), 10, 1 );
		add_action( 'wp', array( __CLASS__, 'astra_wp' ), 20, 1 );

		// Astra Theme v4.1 compatibility
		add_filter( 'astra_single_layout_one_banner_visibility', array( __CLASS__, 'astra_single_layout_one_banner_visibility' ), 9999, 1 );
		add_filter( 'astra_primary_class', array( __CLASS__, 'astra_primary_class' ), 100, 2 );
		add_filter( 'astra_entry_header_class', array( __CLASS__, 'astra_entry_header_class' ), 10, 1 );
		add_filter( 'astra_get_option_ast-dynamic-archive-page-structure', array( __CLASS__, 'astra_filter_option' ), 10, 3 );


		/*######################################################
		Divi (theme) :: maps api
		######################################################*/
		add_filter( 'et_pb_enqueue_google_maps_script', '__return_false' );
		add_filter( 'et_builder_load_actions', array( __CLASS__,'divi_builder_ajax_load_actions') );
		add_filter( 'et_theme_builder_template_settings_options', array( __CLASS__, 'et_theme_builder_template_settings_options' ), 20, 1 );
		add_filter( 'et_builder_default_post_types', array( __CLASS__, 'et_builder_default_post_types' ), 20, 1 );

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
		FORCE LEGACY STYLES
		######################################################*/
		add_action( 'init', array( __CLASS__, 'maybe_force_legacy_styles' ) );

		// Set custom hook for theme compatibility
		add_action( 'template_redirect', array( __CLASS__, 'template_redirect' ) );

		// Avada theme
		add_filter( 'avada_hide_page_options', array( __CLASS__, 'avada_hide_page_options' ), 100, 1 );
		add_filter( 'fusion-page-id', array( __CLASS__, 'avada_fusion_page_id' ), 100, 1 );
		add_filter( 'avada_sidebar_context', array( __CLASS__, 'avada_62_sidebar_context' ), 11, 4 );
		add_filter( 'fusion_should_get_page_option', array( __CLASS__, 'fusion_should_get_page_option' ), 999, 1 );
		add_filter( 'is_hundred_percent_template', array( __CLASS__, 'fusion_is_hundred_percent_template' ), 20, 2 );
		add_filter( 'fusion_is_hundred_percent_template', array( __CLASS__, 'fusion_is_hundred_percent_template' ), 20, 2 );
		add_action( 'fusion_builder_admin_scripts_hook', array( __CLASS__, 'avada_fusion_builder_admin_script' ), 11 );

		if ( ! is_admin() ) {
			// Filter post meta
			add_filter( 'get_post_metadata', array( __CLASS__, 'dynamically_add_post_meta' ), 10, 4 );
			add_filter( 'avada_has_sidebar', array( __CLASS__, 'avada_has_sidebar' ), 100, 3 );
			add_filter( 'avada_has_double_sidebars', array( __CLASS__, 'avada_has_double_sidebars' ), 100, 3 );
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

				// Avada header / footer actions
				add_action( 'wp_head', array( __CLASS__, 'avada_wp_head_setup' ), 99 );
				add_action( 'get_footer', array( __CLASS__, 'avada_get_footer_setup' ), 99 );
				add_action( 'fusion_template_content', array( __CLASS__, 'avada_fusion_template_content_pause' ), 1 );
				add_action( 'fusion_template_content', array( __CLASS__, 'avada_fusion_template_content_resume' ), 101 );
				add_action( 'fusion_tb_override', array( __CLASS__, 'avada_fusion_tb_override' ), 10, 2 );
			}
		} else {
			add_action( 'admin_notices', array( __CLASS__, 'page_builder_notices' ) );
		}

		if ( wp_doing_ajax() ) {
			add_action( 'admin_init', array( __CLASS__, 'ajax_admin_init' ), 5 );
		}

		add_filter( 'geodir_link_to_lightbox_attrs', array( __CLASS__, 'link_to_lightbox_attrs' ), 10, 1 );

		// Borlabs Cookie setting
		if ( defined( 'BORLABS_COOKIE_VERSION' ) ) {
			add_filter( 'geodir_get_settings_general', array( __CLASS__, 'borlabs_cookie_setting' ), 20, 3 );
		}

		// Complianz | GDPR/CCPA Cookie Consent plugin integration
		if ( class_exists( 'COMPLIANZ' ) ) {
			add_filter( 'cmplz_integrations', array( __CLASS__, 'complianz_gdpr_integration' ), 21, 1 );
			add_filter( 'cmplz_integration_path', array( __CLASS__, 'complianz_integration_path' ), 21, 2 );
		}

		// Handle pre AJAX widget listings.
		add_action( 'geodir_widget_ajax_listings_before', array( __CLASS__, 'ajax_listings_before' ), 10, 1 );

		// Register scripts on block theme.
		add_action( 'wp_super_duper_widget_init', array( __CLASS__, 'block_theme_load_scripts' ), 5, 2 );

		// Astra Pro
		if ( defined( 'ASTRA_EXT_VER' ) ) {
			add_filter( 'post_class', array( __CLASS__, 'astra_pro_post_class' ), 99, 3 );

			$astra_meta = array(
				'adv-header-id-meta',
				'ast-above-header-display',
				'ast-below-header-display',
				'ast-hfb-above-header-display',
				'ast-hfb-below-header-display',
				'footer-adv-display',
				'footer-sml-layout',
				'header-above-stick-meta',
				'header-below-stick-meta',
				'header-main-stick-meta',
				'stick-header-meta',
				'sticky-header-on-devices',
				'sticky-header-style',
				'sticky-hide-on-scroll',
				'theme-transparent-header-meta',
				'ast-page-background-enabled',
				'ast-page-background-meta',
				'ast-banner-title-visibility',
				'ast-main-header-display',
				'site-post-title',
				'ast-breadcrumbs-content',
				'ast-featured-img'
			);

			foreach ( $astra_meta as $meta_key ) {
				add_filter( 'astra_get_option_meta_' . $meta_key, array( __CLASS__, 'astra_get_option_meta' ), 20, 2 );
			}
		}

		// Relevanssi compatibility
		add_filter( 'relevanssi_search_ok', array( __CLASS__, 'relevanssi_search_ok' ), 10, 2 );
		add_filter( 'relevanssi_prevent_default_request', array( __CLASS__, 'relevanssi_prevent_default_request' ), 10, 2 );

		// Apply filters to textarea output before display.
		add_filter( 'geodir_filter_textarea_output', 'make_clickable', 9 );
		add_filter( 'geodir_filter_textarea_output', 'wptexturize' );
		add_filter( 'geodir_filter_textarea_output', 'convert_chars' );
		add_filter( 'geodir_filter_textarea_output', 'convert_smilies', 20 );
		add_filter( 'geodir_filter_textarea_output', 'force_balance_tags', 25 );
		add_filter( 'geodir_filter_textarea_output', 'wpautop', 30 );
		add_filter( 'geodir_filter_textarea_output', 'capital_P_dangit', 31 );
	}

	/**
	 * Add more cron schedules.
	 *
	 * @since 2.3.10
	 *
	 * @param array $schedules List of WP scheduled cron jobs.
	 * @return array Cron schedules.
	 */
	public static function cron_schedules( $schedules ) {
		// Monthly
		if ( empty( $schedules['monthly'] ) ) {
			$schedules['monthly'] = array(
				'interval' => MONTH_IN_SECONDS,
				'display' => __( 'Once Monthly', 'geodirectory' ),
			);
		}

		return $schedules;
	}

	/**
	 * Add options to manage overwrite titles & meta by SEO plugins.
	 *
	 * @since 2.2.7
	 *
	 * @param array $options SEO settings.
	 * @return array SEO settings.
	 */
	public static function seo_plugin_options( $options ) {
		// Yoast SEO
		if ( defined( 'WPSEO_VERSION' ) ) {
			$new_options = array(
				array(
					'title' => __( 'Yoast SEO detected', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => geodir_notification( array( 'info' => __( 'The Yoast SEO plugin has been detected and will take over the GeoDirectory meta Settings unless disabled below. (titles from here will still be used, but not meta)', 'geodirectory' ) ) ),
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

			array_splice( $options, 2, 0, $new_options );
		}

		// Rank Math SEO
		if ( defined( 'RANK_MATH_VERSION' ) ) {
			$new_options = array(
				array(
					'title' => __( 'Rank Math SEO detected', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => geodir_notification( array( 'info' => __( 'The Rank Math SEO plugin has been detected and will take over the GeoDirectory meta Settings unless disabled below. (titles from here will still be used, but not meta)', 'geodirectory' ) ) ),
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

			array_splice( $options, 2, 0, $new_options ); // splice in at position 1
		}

		// SEOPress
		if ( function_exists( 'seopress_activation' ) ) {
			$new_options = array(
				array(
					'id' => 'seopress_detected',
					'type' => 'title',
					'title' => __( 'SEOPress SEO Detected', 'geodirectory' ),
					'desc' => geodir_notification( array( 'info' => __( 'The SEOPress SEO plugin has been detected and will take over the GeoDirectory meta Settings unless disabled below. (titles from here will still be used, but not meta)', 'geodirectory' ) ) ),
				),
				array(
					'id' => 'seopress_disable',
					'type' => 'checkbox',
					'name' => __( 'Disable SEOPress', 'geodirectory' ),
					'desc' => __( 'Disable overwrite by SEOPress titles & metas on GD pages?', 'geodirectory' ),
					'default' => '0',
				),
				array(
					'id' => 'seopress_detected',
					'type' => 'sectionend'
				)
			);

			array_splice( $options, 2, 0, $new_options );
		}

		return $options;
	}

	/**
	 * Maybe force legacy theme styles.
	 */
	public static function maybe_force_legacy_styles(){

		$design_style = geodir_design_style();

		// Kleo theme (runs Bootstrap v3 which makes new styles incompatible)
		if ( function_exists( 'kleo_setup' ) && $design_style ) {

			// disable if older ver ov Kleo
			if( defined('SVQ_THEME_VERSION') && version_compare(SVQ_THEME_VERSION,'4.9.170','<') ){
				global $aui_disabled_notice;
				add_action( 'admin_notices', array( __CLASS__, 'notice_aui_disabled' ) );
				$settings_link = admin_url("admin.php?page=gd-settings&tab=general&section=developer");
				$aui_disabled_notice = sprintf( __("Kleo theme works best with GeoDirectory legacy styles, please set legacy styles %shere%s","geodirectory"),"<a href='$settings_link'>","</a>");
			}

		}elseif ( function_exists( 'listimia_setup' ) && $design_style ) {
			global $aui_disabled_notice;

			$parent_theme = wp_get_theme();
			if ( ! empty( $parent_theme ) && is_child_theme() ) {
				$parent_theme = wp_get_theme( $parent_theme->Template );
			}

			if ( ! empty( $parent_theme ) && version_compare( $parent_theme->Version, '1.1.7', '<' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'notice_aui_disabled' ) );
				$settings_link = admin_url("admin.php?page=gd-settings&tab=general&section=developer");
				$aui_disabled_notice = sprintf( __("Listimia theme works best with GeoDirectory legacy styles, please set legacy styles %shere%s","geodirectory"),"<a href='$settings_link'>","</a>");
			}
		}
	}

	/**
	 * Show a notice if AUI is disabled.
	 */
	public static function notice_aui_disabled() {
		global $aui_disabled_notice;

		if($aui_disabled_notice && (
				isset($_REQUEST['page']) && ( $_REQUEST['page'] == 'ayecode-ui-settings' || $_REQUEST['page'] == 'gd-settings'  )
			)){
			$class = 'notice notice-error';
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $aui_disabled_notice );
		}

	}

	/**
	 * Disable AUI
	 *
	 * @param $settings
	 *
	 * @return mixed
	 */
	public static function disable_aui($settings){

		$settings['css'] = '';
		$settings['js'] = '';
		$settings['html_font_size'] = '';

		return $settings;
	}

	/**
	 * Setup actions on admin AJAX load.
	 *
	 * @since 2.0.0.97
	 */
	public static function ajax_admin_init() {
		// WPBakery Page Builder render vc tags in AJAX content.
		if ( class_exists( 'WPBMap' ) && ! empty( $_REQUEST['action'] ) && strpos( $_REQUEST['action'], 'geodir_' ) === 0 ) {
			WPBMap::addAllMappedShortcodes();
		}
	}

	/**
	 * Rank Math Category image
	 *
	 * @param object $this_var rankmath class object
	 */
	public static function rank_math_cat_image( $this_var ) {
		global $wp_query;
		if( is_category() || is_tax() ){
			$term = $wp_query->get_queried_object();
			if ( ! empty( $term->term_id ) && ! empty( $term->taxonomy ) && geodir_is_gd_taxonomy( $term->taxonomy ) ) {
				$image_id = get_term_meta( $term->term_id, "rank_math_facebook_image_id", true );
				$this_var->add_image_by_id( $image_id );
			}
		}
	}

	/**
	 * Setup functions on plugins loaded.
	 *
	 * @since 2.0.0.81
	 */
	public static function plugins_loaded() {
		// Simple Page Sidebars plugin add sidebar supports.
		if ( class_exists( 'Simple_Page_Sidebars' ) ) {
			$post_types = geodir_get_posttypes();

			add_post_type_support( 'page', 'simple-page-sidebars' );
			if ( ! empty( $post_types ) ) {
				foreach ( $post_types as $post_type ) {
					add_post_type_support( $post_type, 'simple-page-sidebars' );
				}
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
	 * Setup Avada header actions.
	 *
	 * @since 2.0.0.102
	 */
	public static function avada_wp_head_setup() {
		if ( geodir_is_geodir_page() ) {
			if ( has_action( 'avada_render_header' ) ) {
				add_action( 'avada_render_header', array( __CLASS__, 'avada_pause_the_content' ), 1 );
				add_action( 'avada_render_header', array( __CLASS__, 'avada_resume_the_content' ), 101 );
			}

			if ( has_action( 'avada_override_current_page_title_bar' ) ) {
				add_action( 'avada_override_current_page_title_bar', array( __CLASS__, 'avada_pause_the_content' ), 1 );
				add_action( 'avada_override_current_page_title_bar', array( __CLASS__, 'avada_resume_the_content' ), 101 );
			}
		}
	}

	/**
	 * Setup Avada footer actions.
	 *
	 * @since 2.0.0.102
	 */
	public static function avada_get_footer_setup() {
		if ( geodir_is_geodir_page() && has_action( 'avada_render_footer' ) ) {
			add_action( 'avada_render_footer', array( __CLASS__, 'avada_pause_the_content' ), 1 );
			add_action( 'avada_render_footer', array( __CLASS__, 'avada_resume_the_content' ), 101 );

			add_action( 'awb_remove_third_party_the_content_changes', array( __CLASS__, 'avada_pause_the_content' ), 1 );
			add_action( 'awb_readd_third_party_the_content_changes', array( __CLASS__, 'avada_resume_the_content' ), 101 );
		}
	}

	public static function avada_fusion_template_content_pause() {
		if ( geodir_is_page( 'search' ) ) {
			self::avada_pause_the_content();
		}
	}

	public static function avada_fusion_template_content_resume() {
		if ( geodir_is_page( 'search' ) ) {
			self::avada_resume_the_content();
		}
	}

	public static function avada_fusion_tb_override( $override, $c_page_id ) {
		if ( geodir_is_page( 'search' ) ) {
			$Fusion_Template_Builder = Fusion_Template_Builder();

			$args                  = [
				'post_type'        => 'fusion_tb_layout',
				'post_status'      => 'publish',
				'posts_per_page'   => -1,
				'suppress_filters' => true,
			];

			if ( class_exists( 'WooCommerce' ) ) {
				remove_filter( 'the_posts', [ WC()->query, 'remove_product_query_filters' ] );
				$layouts = fusion_cached_query( $args );
				add_filter( 'the_posts', [ WC()->query, 'remove_product_query_filters' ] );
			} else {
				$layouts = fusion_cached_query( $args );
			}

			$layouts = $layouts->posts;
			$target_post = get_post( geodir_search_page_id() );
			$target_post->is_singular = true;

			/**
			 * Check if whatever is being loaded should have a template override.
			 */
			 $_layout = null;
			if ( is_array( $layouts ) ) {
				foreach ( $layouts as $layout ) {
					if ( self::check_full_conditions( $layout, $target_post, $Fusion_Template_Builder ) ) {
						$layout->permalink = get_permalink( $layout->ID );
						$Fusion_Template_Builder->layout = $layout;
						$_layout = $layout;
					}
				}
			}

			$override = $_layout;

			// We're on purpose using wp_reset_query() instead of wp_reset_postdata() here
			// because we've altered the main query above.
			wp_reset_query(); // phpcs:ignore WordPress.WP.DiscouragedFunctions
		}

		return $override;
	}

	/**
	 * Check if current post matched conditions of template.
	 *
	 * @since 2.2
	 * @param WP_Post $template    Section post object.
	 * @param WP_Post $target_post The target post object.
	 * @return bool Whether it passed or not.
	 * @access public
	 */
	public static function check_full_conditions( $template, $target_post, $Fusion_Template_Builder ) {
		global $pagenow;

		$conditions    = Fusion_Template_Builder::get_conditions( $template );
		$backend_pages = [ 'post.php', 'term.php' ];

		if ( is_array( $conditions ) ) {
			foreach ( $conditions as $condition ) {
				if ( isset( $condition['type'] ) && '' !== $condition['type'] && isset( $condition[ $condition['type'] ] ) ) {
					$type    = $condition['type'];
					$exclude = 'exclude' === $condition['mode'];

					if ( fusion_is_preview_frame() || ( is_admin() && in_array( $pagenow, $backend_pages ) ) ) { // phpcs:ignore WordPress.PHP.StrictInArray
						$pass = 'archives' === $type ? $Fusion_Template_Builder->builder_check_archive_condition( $condition ) : $Fusion_Template_Builder->builder_check_singular_condition( $condition, $target_post );
					} else {
						$pass = 'archives' === $type && ! is_search() ? $Fusion_Template_Builder->check_archive_condition( $condition ) : $Fusion_Template_Builder->builder_check_singular_condition( $condition, $target_post );
					}

					// If it doesn't pass all exclude conditions check is false.
					// If all exclude conditions are valid and we find one valid condition check is true.
					if ( $exclude && ! $pass ) {
						return false;
					} elseif ( ! $exclude && $pass ) {
						return true;
					}
				}
			}
		}
		// The default behaviour.
		return false;
	}

	/**
	 * Remove GD archive page template the_content filter.
	 *
	 * @since 2.0.0.102
	 *
	 * @global bool|null $geodir_avada_the_content Flag to remove filter.
	 */
	public static function avada_pause_the_content() {
		global $geodir_avada_the_content;

		if ( has_filter( 'the_content', array( 'GeoDir_Template_Loader', 'setup_archive_page_content' ) ) ) {
			$geodir_avada_the_content = true;

			remove_filter( 'the_content', array( 'GeoDir_Template_Loader', 'setup_archive_page_content' ) );
		}
	}

	/**
	 * Add GD archive page template the_content filter.
	 *
	 * @since 2.0.0.102
	 *
	 * @global bool|null $geodir_avada_the_content Flag to add filter back.
	 */
	public static function avada_resume_the_content() {
		global $geodir_avada_the_content;

		if ( $geodir_avada_the_content ) {
			$geodir_avada_the_content = false;

			if ( ! has_filter( 'the_content', array( 'GeoDir_Template_Loader', 'setup_archive_page_content' ) ) ) {
				add_filter( 'the_content', array( 'GeoDir_Template_Loader', 'setup_archive_page_content' ) );
			}
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
		$actions[] = 'geodir_ajax_search';

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
				}elseif(!empty($geodirectory->settings['page_add']) && $geodirectory->settings['page_add'] == $id ){
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
						'<a href="https://www.wpbeaverbuilder.com/beaver-themer/" target="_blank">',
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
		if ( function_exists( 'buddyboss_theme' ) || class_exists( 'BuddyBoss_Theme' ) ) {
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

		// Astra + Spectra
		if ( class_exists( 'UAGB_Post_Assets' ) ) {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'spectra_uagb_post_assets_enqueue_scripts' ), 99 );
		}

		// Blocksy Theme
		if ( class_exists( 'Blocksy_Manager', false ) ) {
			add_filter( 'theme_mod_search_hero_elements', array( __CLASS__, 'blocksy_get_theme_mod' ), 11, 1 );
			add_filter( 'theme_mod_custom_description', array( __CLASS__, 'blocksy_get_theme_mod' ), 11, 1 );
			add_filter( 'theme_mod_hero_custom_description', array( __CLASS__, 'blocksy_get_theme_mod' ), 11, 1 );
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
	 * Fix conflicts for title hook priority with SEOPress.
	 *
	 * @since 2.2.7
	 *
	 * @param int $priority Title hook priority.
	 * @return int Filtered priority.
	 */
	public static function seopress_titles_the_title_priority( $priority ) {
		$priority = 1500;

		return $priority;
	}

	/**
	 * Adds warning notices if BuddyPress is active and has issues.
	 */
	public static function buddypress_notices() {
		if ( is_admin() ) {
			// In BuddyPress v6.0.0 no /search/ conflict detected.
			if ( version_compare( bp_get_version(), '6.0.0', '>=' ) ) {
				// Remove existing notice.
				if ( GeoDir_Admin_Notices::has_notice( 'buddypress_search_slug_error' ) ) {
					GeoDir_Admin_Notices::remove_notice( 'buddypress_search_slug_error' );
				}
				return;
			}

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
	 * @global int $gd_skip_the_content
	 *
	 * @param $bypass
	 *
	 * @return bool
	 */
	public static function elementor_loop_bypass( $bypass ) {
		global $gd_skip_the_content;

		if ( defined( 'ELEMENTOR_PRO_VERSION' ) && GeoDir_Elementor::is_template_override() ) {
			$bypass = true;
		}

		if ( ! $bypass && $gd_skip_the_content ) {
			$bypass = true; // Prevent looping on some themes/plugins.
		}

		//if ( ! $bypass && function_exists( 'et_theme_builder_get_template_layouts' ) && et_theme_builder_get_template_layouts() ) {
			//$bypass = true;
		//}

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

		$object_post_type = get_post_type( (int) $object_id );

		// Bail when non related post type.
		if ( in_array( $object_post_type, array( 'post', 'attachment', 'nav_menu_item', 'card_templates', 'advanced_ads','product', 'tve_notifications', 'thrive_typography' ) ) ) {
			return $metadata;
		}

		// Bail when non related meta_key.
		if ( ! empty( $meta_key ) && is_scalar( $meta_key ) && in_array( $meta_key, array( 'amazonS3_cache' ) ) ) {
			return $metadata;
		}

		$query_post_type = ! empty( $wp_query ) ? get_query_var( 'post_type' ) : '';

		// Prevent conflicts custom non GD post type archive page.
		if ( ! empty( $query_post_type ) && is_scalar( $query_post_type ) && in_array( $query_post_type, array( 'product' ) ) ) {
			return $metadata;
		}

		// Thrive Theme
		if ( function_exists( 'thrive_theme' ) && $single && ! is_admin() && $meta_key == 'layout_data' && in_array( $object_post_type, array( 'thrive_template', 'thrive_layout' ) ) && ( geodir_is_page( 'search' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) && ( $page_id = GeoDir_Compatibility::gd_page_id() ) ) {
			$thrive_meta = thrive_post( $page_id )->get_visibility_meta();

			if ( ! empty( $thrive_meta ) && is_array( $thrive_meta ) && ( empty( $metadata ) || is_array( $metadata ) ) ) {
				$_metadata = array();

				if ( ! empty( $metadata[0] ) && is_array( $metadata[0] ) ) {
					$_metadata = $metadata[0];
				}

				if ( ! empty( $thrive_meta['sidebar'] ) && $thrive_meta['sidebar'] == 'hide' ) {
					$_metadata['hide_sidebar'] = true;
				}

				if ( ! empty( $_metadata ) ) {
					if ( ! is_array( $metadata ) ) {
						$metadata = array();
					}

					$metadata[0] = $_metadata;

					return $metadata;
				}
			}
		}

		// Standard WP fields
		$wp_keys = array(
			'_wp_page_template'
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
			$gen_keys[] = 'theme-transparent-header-meta';
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
				if ( in_array( $meta_key, $elvyre_keys ) && ! empty( $wp_query ) && ! empty( $wp_query->post->post_type ) && geodir_is_gd_post_type( $object_post_type ) && $wp_query->post->post_type == 'page' && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) ) {
					$post = $wp_query->post;
					$backup_post = $post;
				}
			}
		}

		// Simple Page Sidebars plugin.
		if ( class_exists( 'Simple_Page_Sidebars' ) ) {
			$gen_keys[] = '_sidebar_name';
		}

		// Betheme by Muffin group
		if ( function_exists( 'mfn_body_classes' ) && function_exists( 'mfn_ID' ) ) {
			if ( strpos( $meta_key, 'mfn-post-' ) === 0 ) {
				$gen_keys[] = $meta_key;

				// Archive page set page post.
				if ( ! empty( $wp_query ) && ! empty( $wp_query->post->post_type ) && geodir_is_gd_post_type( $object_post_type ) && $wp_query->post->post_type == 'page' && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) ) {
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
			 || ( function_exists( 'genesis_theme_support' ) && ( strpos( $meta_key, '_genesis_' ) === 0 || strpos( $meta_key, '_gsm_' ) === 0 || strpos( $meta_key, '_ss_' ) === 0 || empty( $meta_key ) ) && ! in_array( $meta_key, array( '_genesis_title', '_genesis_description', '_genesis_keywords' ) ) ) // Genesis
			 || ( class_exists( 'The7_Aoutoloader' ) && ( strpos( $meta_key, '_dt_' ) === 0 || empty( $meta_key ) ) ) // The7
			 || ( function_exists( 'avia_get_option' ) && ( ! empty( $meta_key ) && in_array( $meta_key, $gen_keys ) ) ) // Enfold
			 || ( class_exists( 'Avada' ) && class_exists( 'FusionBuilder' ) && ( strpos( $meta_key, 'pyre_' ) === 0 || strpos( $meta_key, 'sbg_' ) === 0 || strpos( $meta_key, '_fusion' ) === 0 || empty( $meta_key ) ) || in_array( $meta_key, array( 'pages_sidebar', 'pages_sidebar_2', 'default_sidebar_pos' ) ) ) // Avada + FusionBuilder
			 || ( class_exists( 'OCEANWP_Theme_Class' ) && ( empty( $meta_key ) || strpos( $meta_key, 'ocean_' ) === 0 || strpos( $meta_key, 'menu_item_' ) === 0 || strpos( $meta_key, '_menu_item_' ) === 0 ) ) // OceanWP
			 || ( defined( 'PORTO_VERSION' ) ) // Porto
			 || ( function_exists( 'wpbf_theme_setup' ) && ( empty( $meta_key ) || strpos( $meta_key, 'wpbf_' ) === 0 ) ) // Page Builder Framework
			 || ( function_exists( 'generateblocks_do_activate' ) && strpos( $meta_key, '_generateblocks_' ) === 0 ) // GenerateBlocks plugin
			 || ( defined( 'US_CORE_VERSION' ) && ( strpos( $meta_key, '_us_' ) === 0 || strpos( $meta_key, 'us_' ) === 0 || strpos( $meta_key, '_wpb_' ) === 0 ) ) // UpSolution Core plugin
			 || ( function_exists( 'brivona_setup' ) && strpos( $meta_key, '_themetechmount' ) === 0 ) // Brivona theme
			 || ( defined( 'ZEEN_ENGINE_VER' ) && ( strpos( $meta_key, 'tipi_' ) === 0 || strpos( $meta_key, 'zeen_' ) === 0 || strpos( $meta_key, '_menu_zeen_' ) === 0 ) ) // Zeen Tipi Builder
			 || ( defined( 'KADENCE_VERSION' ) && ( empty( $meta_key ) || strpos( $meta_key, '_kad_' ) === 0 ) ) // Kadence theme
			 || ( function_exists( 'znhg_kallyas_theme_config' ) && ( strpos( $meta_key, 'zn-' ) === 0 || strpos( $meta_key, 'zn_' ) === 0 || strpos( $meta_key, '_zn_' ) === 0 ) || in_array( $meta_key, array( 'show_header', 'show_footer' ) ) ) // Kallyas theme Zion Builder
			 || ( defined( 'ASTRA_THEME_VERSION' ) && ( strpos( $meta_key, 'ast-' ) === 0 ) ) // Astra theme
			 || ( defined( 'UAGB_FILE' ) && ( strpos( $meta_key, 'spectra' ) === 0 || strpos( $meta_key, '_uag_' ) === 0 || strpos( $meta_key, '_uagb_' ) === 0 ) ) // Spectra
			 || ( defined( 'BRICKS_VERSION' ) && strpos( $meta_key, '_bricks_' ) === 0 ) // Bricks Theme
			 || ( function_exists( 'thrive_theme' ) && ( strpos( $meta_key, 'tve_' ) === 0 || strpos( $meta_key, '_tve_' ) === 0 || strpos( $meta_key, 'thrive_' ) === 0 ) || in_array( $meta_key, array( 'default', 'layout', 'layout_data', 'structure', 'sidebar-type', 'sticky-sidebar', 'off-screen-sidebar', 'comments', 'sections', 'icons', 'style', 'format', 'tag', 'no_search_results', 'primary_template', 'secondary_template', 'variable_template', 'sidebar_on_left', 'hide_sidebar', 'content_width' ) ) ) // Thrive Theme
			 ) && geodir_is_gd_post_type( $object_post_type ) ) {
			if ( geodir_is_page( 'single' ) ) {
				$template_page_id = geodir_details_page_id( $object_post_type );
			} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
				$template_page_id = geodir_archive_page_id( $object_post_type );
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

					// Don't overwrite SEOPress SEO meta for the individual post.
					$reserve_post_meta = function_exists( 'seopress_activation' ) && ! geodir_get_option( 'seopress_disable' ) && geodir_is_page( 'detail' ) ? true : $reserve_post_meta;

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
								if ( strpos( $key, '_yoast_wpseo_' ) === 0 || strpos( $key, 'seopress_' ) === 0 ) {
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

			if ( ( in_array( $meta_key, $kingstudio_keys ) || empty( $meta_key ) ) && geodir_is_gd_post_type( $object_post_type ) ) {
				if ( geodir_is_page( 'detail' ) ) {
					$template_page_id = geodir_details_page_id( $object_post_type );
				} else if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
					$template_page_id = geodir_archive_page_id( $object_post_type );
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
			$post_type = $object_post_type;

			if ( ! geodir_is_gd_post_type( $post_type ) ) {
				$post_type = geodir_get_current_posttype();
			}

			$template_page_id = geodir_is_page( 'single' ) ? geodir_details_page_id( $post_type ) : geodir_archive_page_id( $post_type );

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
	 * @param $layout Content layout.
	 *
	 * @return mixed
	 */
	public static function astra_get_content_layout( $layout ) {
		if ( $page_id = self::gd_page_id() ) {
			$content_layout = get_post_meta( $page_id, 'site-content-layout', true );

			if ( function_exists( 'astra_toggle_layout' ) ) {
				if ( empty( $content_layout ) || $content_layout == 'default' ) {
					$content_layout = get_post_meta( $page_id, 'ast-site-content-layout', true );
				}

				// If post meta value is present, apply new layout option.
				if ( ! ( empty( $content_layout ) || $content_layout == 'default' ) ) {
					$content_layout = astra_toggle_layout( 'ast-site-content-layout', 'meta', $page_id );
				}

				if ( ( empty( $content_layout ) || $content_layout == 'default' ) && ( geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) ) && ( $post_type = geodir_get_current_posttype() ) ) {
					$content_layout = astra_toggle_layout( 'archive-' . $post_type . '-ast-content-layout', 'archive', false );
				}

				if ( empty( $content_layout ) || $content_layout == 'default' ) {
					// Get the GLOBAL content layout value.
					$content_layout = astra_toggle_layout( 'ast-site-content-layout', 'global' );
				}
			} else {
				if ( 'default' == $content_layout || empty( $content_layout ) ) {
					$content_layout = 'boxed-container';
				}
			}

			$layout = $content_layout;
		}

		return $layout;
	}

	/**
	 * Get the Astra page sidebar setting for archives.
	 *
	 * @param $layout Page layout.
	 *
	 * @return mixed
	 */
	public static function astra_page_layout( $layout ) {
		if ( $page_id = self::gd_page_id() ) {
			$page_layout = get_post_meta( $page_id, 'site-sidebar-layout', true );

			if ( 'default' == $page_layout || empty( $page_layout ) ) {
				// Get the global sidebar value.
				$page_layout = astra_get_option( 'site-sidebar-layout' );
			}

			$layout = $page_layout;
		}

		return $layout;
	}

	/**
	 * Filter Astra content style.
	 *
	 * @since 2.3.30
	 *
	 * @param bool $is_content_boxed True when boxed.
	 * @return bool
	 */
	public static function astra_is_content_layout_boxed( $is_content_boxed ) {
		if ( $page_id = self::gd_page_id() ) {
			$meta_content_style = get_post_meta( $page_id, 'site-content-style', true );

			if ( ! ( empty( $meta_content_style ) || 'default' === $meta_content_style ) ) {
				if ( 'boxed' === $meta_content_style ) {
					$is_content_boxed = true;
				} else {
					$is_content_boxed = false;
				}
			}
		}

		return $is_content_boxed;
	}

	/**
	 * Filter Astra sidebar style.
	 *
	 * @since 2.3.30
	 *
	 * @param bool $is_sidebar_boxed True when boxed.
	 * @return bool
	 */
	public static function astra_is_sidebar_layout_boxed( $is_sidebar_boxed ) {
		if ( $page_id = self::gd_page_id() ) {
			$meta_sidebar_style = get_post_meta( $page_id, 'site-sidebar-style', true );

			if ( ! ( empty( $meta_sidebar_style ) || 'default' === $meta_sidebar_style ) ) {
				if ( 'boxed' === $meta_sidebar_style ) {
					$is_sidebar_boxed = true;
				} else {
					$is_sidebar_boxed = false;
				}
			}
		}

		return $is_sidebar_boxed;
	}

	/**
	 * Manage post CSS class names for the GD post.
	 *
	 * @since 2.2.16
	 *
	 * @param array $classes An array of post class names.
	 * @param array $class   An array of additional class names added to the post.
	 * @param int   $post_id The post ID.
	 * @return array Post classes.
	 */
	public static function astra_pro_post_class( $classes, $class, $post_id = 0 ) {
		global $wp_query;

		$post_type = $post_id ? get_post_type( (int) $post_id ) : '';

		if ( $post_type && ( geodir_is_gd_post_type( $post_type ) || ( $post_type == 'page' && ( geodir_is_page( 'search' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) ) ) ) {
			if ( ! empty( $wp_query ) && $wp_query->is_main_query() && is_search() && ! geodir_is_page( 'search' ) ) {
				return $classes;
			}

			$_classes = $classes;
			$classes = array();

			foreach ( $_classes as $_class ) {
				if ( $_class == 'ast-article-single' && is_singular( $post_type ) ) {
					$classes[] = $_class;
				} else if ( strpos( $_class, "ast-" ) !== 0 && ! in_array( $_class, array( 'remove-featured-img-padding', 'masonry-brick' ) ) ) {
					$classes[] = $_class;
				} else if ( $post_type == 'page' && strpos( $_class, 'ast-full-width' ) !== false ) {
					$classes[] = 'ast-full-width';
				}
			}
		}

		return $classes;
	}

	/**
	 * Get the astra meta for GD search & archive pages.
	 *
	 * @since 2.2.23
	 *
	 * @param array|string $value   Meta value.
	 * @param array|string $default Default value.
	 * @return array|string Filtered meta value.
	 */
	public static function astra_get_option_meta( $value, $default ) {
		if ( ( $page_id = (int) self::gd_page_id() ) && ! geodir_is_page( 'single' ) && ( $current_filter = current_filter() ) ) {
			$meta_key = strpos( $current_filter, 'astra_get_option_meta_' ) === 0 ? str_replace( 'astra_get_option_meta_', '', $current_filter ) : '';

			if ( $meta_key ) {
				$value = get_post_meta( $page_id, $meta_key, true );

				if ( empty( $value ) || 'default' == $value ) {
					$value = astra_get_option( $meta_key, $default );
				}
			}
		}

		return $value;
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
	 * Set flag to fix Astra v4.1 compatibility issue.
	 *
	 * @since 2.3.5
	 *
	 * @param bool $value True to set single layout one banner visibility.
	 * @return bool True or False.
	 */
	public static function astra_single_layout_one_banner_visibility( $value ) {
		global $gd_astra_41_fix, $gd_skip_the_content;

		if ( $value && defined( 'ASTRA_THEME_VERSION' ) && version_compare( ASTRA_THEME_VERSION, '4.1', '>=' ) && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) ) {
			$gd_astra_41_fix = true;
			$gd_skip_the_content = true;

			if ( geodir_is_page( 'search' ) ) {
				$value = false;
			}
		}

		return $value;
	}

	/**
	 * Unset flag that set to fix Astra v4.1 compatibility issue.
	 *
	 * @since 2.3.5
	 *
	 * @param array $classes CSS classes.
	 * @return array CSS classes.
	 */
	public static function astra_entry_header_class( $classes ) {
		global $gd_astra_41_fix, $gd_skip_the_content;

		if ( $gd_astra_41_fix && $gd_skip_the_content ) {
			$gd_skip_the_content = false;
		}

		return $classes;
	}

	/**
	 * Fix the page classes for GD archives.
	 *
	 * @param $classes
	 * @param $class
	 * @return mixed|string[]
	 */
	public static function astra_primary_class($classes, $class)
	{
		if(geodir_is_page('search') || geodir_is_page('archive')){
			return ['content-area','primary'];
		}
		return $classes;

	}

	/**
	 * Filter Astra option.
	 *
	 * @since 2.3.5
	 *
	 * @param mixed $value Option value.
	 * @param string $option Option name.
	 * @param mixed $default Default value.
	 * @return mixed Filtered value.
	 */
	public static function astra_filter_option( $value, $option, $default ) {
		if ( ! ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) && ! geodir_is_page( 'search' ) ) ) {
			return $value;
		}

		$post_type = geodir_get_current_posttype();

		if ( ! $post_type ) {
			return $value;
		}

		if ( $option == 'ast-dynamic-archive-page-structure' ) {
			$value = astra_get_option( 'ast-dynamic-archive-' . $post_type . '-structure', array( 'ast-dynamic-archive-' . $post_type . '-title' ) );
		}

		return $value;
	}

	/**
	 * Stop the GD loop setup if beaver builder themer is overiding it.
	 *
	 * @global int $gd_skip_the_content
	 *
	 * @param $bypass
	 *
	 * @return bool
	 */
	public static function beaver_builder_loop_bypass( $bypass ) {
		global $gd_skip_the_content;

		if ( class_exists( 'FLThemeBuilderLayoutData' ) ) {
			$ids = FLThemeBuilderLayoutData::get_current_page_content_ids();

			if ( ! empty( $ids ) ) {
				$bypass = true;
			}
		}

		if ( ! $bypass && $gd_skip_the_content ) {
			$bypass = true; // Prevent looping on some themes/plugins.
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
<script>;(function($){var RankMathIntegration=function(){this.init();this.hooks()};RankMathIntegration.prototype.init=function(){this.pluginName="geodirectory"};RankMathIntegration.prototype.hooks=function(){var self=this;if(typeof RankMathApp!=="undefined"){RankMathApp.registerPlugin(this.pluginName)}wp.hooks.addFilter("rank_math_content",this.pluginName,$.proxy(this.filterContent,this));window.setInterval(function(){if(typeof RankMathApp!=="undefined"){RankMathApp.reloadPlugin(self.pluginName)}},2e3)};RankMathIntegration.prototype.getContent=function(){var content="";$(".plupload-thumbs img").each(function(){var img=$(this).clone();img.attr("alt",img.data("title"));content+="<p>"+img[0].outerHTML+".</p>"});$(".gd-fieldset-details textarea").each(function(){var val=$(this).val();if(val.length){content+="<p>"+val+"</p>"}});$("input.geodir_textfield").each(function(){var val=$(this).val();var label=$(this).closest(".gd-fieldset-details").find("label").text()+" - "+val;if("url"==$(this).attr("type")&&val.length){label='<a href="'+val+'">'+label+"</a>"}if(val.length){content+="<p>"+label+".</p>"}});return content};RankMathIntegration.prototype.filterContent=function(content){return content+this.getContent()};$(document).on("ready",function(){new RankMathIntegration})})(jQuery);</script>
	   <?php
			$output = ob_get_clean();
			return str_replace( array( '<script>', '</script>' ), '', $output );
	}

	/**
	 * Function to fix location page og:url with rank math.
	 */
	public static function rank_math_location_url_callback( $url ) {
		// Maybe modify $example in some way.
		if ( geodir_is_page( 'location' ) ) {
			$url = geodir_get_location_link( 'current' );
		}
		return $url;
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
					'title' => stripslashes_deep( $geodir_image->title ),
				);
			}
		}

		return $images;
	}

	/**
	 * Yoast SEO Premium prominent words accessible post types.
	 *
	 * @since 12.9.0
	 *
	 * @param array $post_types The accessible post types.
	 * @return array Filtered post types.
	 */
	public static function wpseo_prominent_words_post_types( $post_types ) {
		if ( ! empty( $post_types ) ) {
			$_post_types = $post_types;

			foreach( $_post_types as $_post_type => $name ) {
				if ( geodir_is_gd_post_type( $_post_type ) ) {
					unset( $post_types[ $_post_type ] );
				}
			}
		}

		return $post_types;
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

		// fix buddyboss dropdown CSS
		$bb_css = "
						.bsui .dropdown {
						    position: relative !important;
						    width: auto !important;
						    overflow: initial !important;
						    box-shadow: unset !important;
						    background: inherit !important;
						    max-height: none !important;
						}
					";
		wp_add_inline_style( 'ayecode-ui', $bb_css );

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
			// FusionBuilder
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
					add_filter( 'template_include', array( __CLASS__, 'avia_template_include' ), 11, 1 );
				}

				if ( geodir_is_page( 'single' ) ) {
					add_filter( 'avf_shortcode_handler_prepare_current_post', array( __CLASS__, 'avf_shortcode_handler_prepare_current_post' ), 20, 1 );
				}
			}

			// Porto (theme)
			if ( defined( 'PORTO_VERSION' ) ) {
				add_filter( 'porto_meta_use_default', array( __CLASS__, 'porto_meta_use_default' ), 99, 1 );
				add_filter( 'porto_meta_layout', array( __CLASS__, 'porto_meta_layout' ), 99, 1 );
			}

			// Genesis (theme)
			if ( function_exists( 'genesis_theme_support' ) ) {
				add_filter( 'genesis_site_layout', array( __CLASS__, 'genesis_site_layout' ), 20, 1 );

				// Genesis Simple Menus
				if ( function_exists( 'genesis_simple_menus' ) ) {
					add_filter( 'theme_mod_nav_menu_locations', array( __CLASS__, 'genesis_simple_menus_set_menu_locations' ), 20, 1 );
				}

				// Genesis Simple Sidebars
				if ( function_exists( 'genesis_simple_sidebars' ) ) {
					add_filter( 'sidebars_widgets', array( __CLASS__, 'genesis_simple_sidebars_set_sidebars_widgets' ), 20, 1 );
				}

				if ( geodir_is_page( 'single' ) ) {
					add_filter( 'genesis_before_comments', array( __CLASS__, 'genesis_before_comments' ), 1 );
				}
			}

			// Fix Divi builder GD pages header
			self::et_builder_divi_fix_stylesheet();

			// SEOPress
			if ( GeoDir_SEO::seopress_enabled() ) {
				add_filter( 'seopress_titles_the_title_priority', array( __CLASS__, 'seopress_titles_the_title_priority' ), 10, 1 );
			}

			// Thrive Theme
			if ( function_exists( 'thrive_theme' ) ) {
				add_action( 'geodir_widget_loop_before', array( __CLASS__, 'thrive_loop_setup_wp_query' ), 1, 2 );
				add_filter( 'thrive_theme_section_default_content', array( __CLASS__, 'thrive_theme_section_default_content' ), 21, 3 );
			}
		} else {
			// SEOPress
			if ( function_exists( 'seopress_activation' ) ) {
				add_filter( 'seopress_titles_the_title_priority', array( __CLASS__, 'seopress_titles_the_title_priority' ), 10, 1 );
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

			if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
				add_filter( 'body_class', array( __CLASS__, 'divi_et_body_class' ), 11, 1 );
			}

			add_filter( 'body_class', array( __CLASS__, 'divi_disable_smooth_scroll' ), 11, 2 );
		}

		// Jarida (theme)
		if ( function_exists( 'tie_admin_bar' ) ) {
			add_filter( 'option_tie_options', array( __CLASS__, 'option_tie_options' ), 20, 3 );
			add_filter( 'wp_super_duper_before_widget', array( __CLASS__, 'jarida_super_duper_before_widget' ), 0, 4 );
			add_filter( 'body_class', array( __CLASS__, 'jarida_body_class' ) );
		}

		// OceanWP theme
		if ( class_exists( 'OCEANWP_Theme_Class' ) ) {
			if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
				add_filter( 'ocean_post_id', array( __CLASS__, 'ocean_post_id' ), 20, 1 );
				add_filter( 'ocean_title', array( __CLASS__, 'ocean_title' ), 20, 1 );
				add_filter( 'ocean_post_subheading', array( __CLASS__, 'ocean_post_subheading' ), 20, 1 );
			}
		}

		// Elementor
		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			if ( version_compare( ELEMENTOR_VERSION, '2.9.0', '>=' ) && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) ) {
				// Page template
				add_filter( 'template_include', array( __CLASS__, 'elementor_template_include' ), 10, 1 );
			}
		}

		// Page Builder Framework theme
		if ( function_exists( 'wpbf_theme_setup' ) ) {
			add_filter( 'wpbf_sidebar_layout', array( __CLASS__, 'wpbf_sidebar_layout' ), 20, 1 );
			add_filter( 'wpbf_inner_content', array( __CLASS__, 'wpbf_inner_content' ), 20, 1 );

			if ( geodir_is_page( 'search' ) ) {
				add_filter( 'body_class', array( __CLASS__, 'wpbf_body_class' ), 20, 1 );
			}
		}

		// GenerateBlocks plugin
		if ( function_exists( 'generateblocks_do_activate' ) && geodir_is_geodir_page() && ( geodir_is_page( 'detail' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'post_type' ) || geodir_is_page( 'search' ) ) ) {
			global $geodir_gb_enqueue_css;

			$geodir_gb_enqueue_css = GenerateBlocks_Enqueue_CSS::get_instance();

			remove_action( 'wp_enqueue_scripts', array( $geodir_gb_enqueue_css, 'enqueue_dynamic_css' ) );
			remove_action( 'wp_head', array( $geodir_gb_enqueue_css, 'print_inline_css' ) );

			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'generateblocks_enqueue_dynamic_css' ) );
			add_action( 'wp_head', array( __CLASS__, 'generateblocks_print_inline_css' ) );
		}

		// UpSolution Core plugin
		if ( defined( 'US_CORE_VERSION' ) && geodir_is_geodir_page() ) {
			add_filter( 'us_get_page_area_id', array( __CLASS__, 'us_get_page_area_id' ), 10, 2 );
		}

		// Oxygen plugin
		if ( defined( 'CT_VERSION' ) ) {
			add_filter( 'geodir_get_template', array( __CLASS__, 'oxygen_override_template' ), 11, 5 );
			add_filter( 'geodir_get_template_part', array( __CLASS__, 'oxygen_override_template_part' ), 11, 3 );
		}

		// Borlabs Cookie Integration
		if ( defined( 'BORLABS_COOKIE_VERSION' ) ) {
			if ( version_compare( BORLABS_COOKIE_VERSION, '3.0', '<' ) &&  ! is_admin() && geodir_get_option( 'borlabs_cookie' ) ) {
				add_filter( 'geodir_lazy_load_map', array( __CLASS__, 'borlabs_cookie_setup' ), 999, 1 );
				add_filter( 'wp_super_duper_widget_output', array( __CLASS__, 'borlabs_cookie_wrap' ), 999, 4 );
			} else if ( version_compare( BORLABS_COOKIE_VERSION, '3.0', '>=' ) ) {
				add_filter( 'geodir_lazy_load_map', array( __CLASS__, 'borlabs_cookie_lazy_load_map' ), 99, 1 );
			}
		}

		// Kallyas theme Zion page builder
		if ( function_exists( 'znhg_kallyas_theme_config' ) ) {
			add_filter( 'znb_edit_url', array( __CLASS__, 'znb_edit_url' ), 9, 1 );
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
		// Avada 6.2
		if ( self::has_avada_62() ) {
			$post_id = $post_id ? $post_id : Avada()->fusion_library->get_page_id();

			if ( 'hide' !== fusion_get_option( 'page_title_bar' ) ) {
				$page_title_bar_contents = Fusion_Helper::fusion_get_page_title_bar_contents( $post_id );
				$title = GeoDir_SEO::set_meta();

				avada_page_title_bar( $title, $page_title_bar_contents[1], $page_title_bar_contents[2] );
			}

			do_action( 'avada_after_page_title_bar' );
		} else {
			$page_title_bar_contents = avada_get_page_title_bar_contents( $post_id );

			$page_title = get_post_meta( $post_id, 'pyre_page_title', true );

			// Which TO to check for.
			$page_title_option = Avada()->settings->get( 'page_title_bar' );

			if ( 'hide' !== $page_title_option ) {
				$title = GeoDir_SEO::set_meta();

				avada_page_title_bar( $title, $page_title_bar_contents[1], $page_title_bar_contents[2] );
			}
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
			$widgets_meta = get_post_meta( (int) $template_page_id, '_generate-footer-widget-meta', true );

			if ( $widgets_meta || '0' === $widgets_meta ) {
				$widgets = $widgets_meta;
			}
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
	 * Print GenerateBlocks enqueue dynamic CSS for GD pages.
	 *
	 * @since 2.0.0.82
	 *
	 * @param object $geodir_gb_enqueue_css GenerateBlocks enqueue object.
	 */
	public static function generateblocks_enqueue_dynamic_css() {
		global $geodir_gb_enqueue_css;

		$page_id = self::gd_page_id();

		if ( ! $page_id ) {
			return;
		}

		$css_version = get_post_meta( $page_id, '_generateblocks_dynamic_css_version', true );

		if ( empty( $css_version ) ) {
			return;
		}

		if ( empty( $geodir_gb_enqueue_css ) ) {
			$geodir_gb_enqueue_css = GenerateBlocks_Enqueue_CSS::get_instance();
		}

		if ( 'file' == $geodir_gb_enqueue_css->mode() ) {
			$option = get_option( 'generateblocks_dynamic_css_posts', array() );

			if ( ! ( is_array( $option ) && ! empty( $option[ $page_id ] ) ) ) {
				// Store our block IDs based on the content we find.
				generateblocks_get_dynamic_css( '', true );
			}

			wp_enqueue_style( 'geodir-generateblocks', esc_url( $geodir_gb_enqueue_css->file( 'uri' ) ), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}
	}

	/**
	 * Print GenerateBlocks inline CSS for GD pages.
	 *
	 * @since 2.0.0.82
	 *
	 * @global object $post The post object.
	 * @param object $geodir_gb_enqueue_css GenerateBlocks enqueue object.
	 */
	public static function generateblocks_print_inline_css() {
		global $post, $geodir_gb_enqueue_css;

		$page_id = self::gd_page_id();

		if ( empty( $page_id ) ) {
			return;
		}

		$the_post = $post;

		// Backup post;
		$post = get_post( $page_id );

		if ( empty( $geodir_gb_enqueue_css ) ) {
			$geodir_gb_enqueue_css = GenerateBlocks_Enqueue_CSS::get_instance();
		}

		if ( 'inline' === $geodir_gb_enqueue_css->mode() || ! wp_style_is( 'generateblocks', 'enqueued' ) ) {
			// Build our CSS based on the content we find.
			generateblocks_get_dynamic_css();

			$css = generateblocks_get_frontend_block_css();

			if ( ! empty( $css ) ) {
				// Add a "dummy" handle we can add inline styles to.
				wp_register_style( 'geodir-generateblocks', false ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				wp_enqueue_style( 'geodir-generateblocks' );

				wp_add_inline_style(
					'geodir-generateblocks',
					wp_strip_all_tags( $css ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				);
			}
		}

		$post = $the_post;
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
	 * Divi theme filter body classes.
	 *
	 * @since 2.0.0.81
	 *
	 * @param array $classes The body classes.
	 * @return array The updated body classes.
	 */
	public static function divi_et_body_class( $classes ) {
		if ( $template_page_id = (int) self::gd_page_id() ) {
			$_classes = implode( '::::', $classes );
			$_classes = str_replace( array( '::::et_left_sidebar', '::::et_right_sidebar', '::::et_no_sidebar', '::::et_full_width_page' ), '', $_classes );
			$classes = explode( '::::', $_classes );

			$page_layout = '';
			$default_sidebar_class = et_get_option( 'divi_sidebar' );
			$is_builder_active = 'on' === get_post_meta( $template_page_id, '_et_pb_use_builder', true ) || et_core_is_fb_enabled();
			$is_blank_page_tpl = self::is_page_template( 'page-template-blank.php' );

			if ( ! $default_sidebar_class ) {
				$default_sidebar_class = is_rtl() ? 'et_left_sidebar' : 'et_right_sidebar';
			}

			if ( ! ( $page_layout = get_post_meta( $template_page_id, '_et_pb_page_layout', true ) ) && ! $is_builder_active ) { // check for the falsy value not for boolean `false`
				// Set post meta layout which will work for all third party plugins.
				$page_layout = $default_sidebar_class;
			} elseif ( $is_builder_active && ( $is_blank_page_tpl || ! $page_layout || is_page() ) ) {
				$page_layout = 'et_no_sidebar';
			}

			if ( $page_layout ) {
				// Add the page layout class.
				$classes[] = $page_layout;
			}
		}

		return $classes;
	}

	/**
	 * Divi theme disable smooth scroll to fix images slider.
	 *
	 * @since 2.1.0.6
	 *
	 * @param array $classes The body classes.
	 * @param string $class An array of additional class names added to the body.
	 * @return array The updated body classes.
	 */
	public static function divi_disable_smooth_scroll( $classes, $class ) {
		$classes[] = 'et_smooth_scroll_disabled';

		return $classes;
	}

	/**
	 * Fix page header on GD Pages + Divi Page Builder.
	 *
	 * @since 2.0.0.93
	 *
	 * @return void.
	 */
	public static function et_builder_divi_fix_stylesheet() {
		if ( ! function_exists( 'et_builder_is_custom_post_type_archive' ) ) {
			return;
		}

		if ( ! et_builder_is_custom_post_type_archive() && ( ! et_builder_post_is_of_custom_post_type( get_the_ID() ) || ! et_pb_is_pagebuilder_used( get_the_ID() ) ) ) {
			return;
		}

		remove_action( 'wp_enqueue_scripts', 'et_divi_replace_stylesheet', 99999998 );
		remove_action( 'wp_enqueue_scripts', 'et_divi_replace_parent_stylesheet', 99999998 );

		add_action( 'wp_head', function() {
			$custom_css = '@media only screen and (min-width:1350px){.et_pb_pagebuilder_layout.geodir-page.et-db #et-boc .et-l.et-l--header .et_pb_section{padding:0;}}.et_pb_pagebuilder_layout.geodir-page #et-main-area #main-content .entry-content .et-l > .et_pb_section{position: relative;z-index:1;}';

			echo '<style type="text/css" id="geodir-et-custom-css">' . $custom_css . '</style>';
		}, 100 );
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

	public static function gd_page_id( $post_type = '' ) {
		global $gd_post;

		$page_id = 0;

		if ( ! geodir_is_geodir_page() ) {
			return $page_id;
		}

		if ( $post_type == 'current' ) {
			$post_type = geodir_get_current_posttype();
		}

		if ( empty( $post_type ) ) {
			if ( ! empty( $gd_post ) && ! empty( $gd_post->post_type ) ) {
				$post_type = $gd_post->post_type;
			} else {
				$post_type = geodir_get_current_posttype();
			}
		}

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
		$post_type = get_post_type( $c_page_id );
		if ( self::has_avada_79() ) {
			$sidebars_option_names = AWB_Widget_Framework()->get_sidebar_post_meta_option_names( $post_type );
		} else {
			$sidebars_option_names = avada_get_sidebar_post_meta_option_names( $post_type );
		}
		$sidebar_1 = (array) fusion_get_option( $sidebars_option_names[0] );
		$sidebar_2 = (array) fusion_get_option( $sidebars_option_names[1] );

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
		if ( ! self::has_avada_62() ) {
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
		}

		if ( geodir_is_page( 'search' ) ) {
			$c_page_id = (int) self::gd_page_id();

			$sidebar_1_original = self::avada_sidebar_context( $c_page_id, 1 );
			$sidebar_2_original = self::avada_sidebar_context( $c_page_id, 2 );

			$override = function_exists( 'Fusion_Template_Builder' ) ? Fusion_Template_Builder()->get_override( 'content' ) : false;
			if ( $override ) {
				$classes = array_unique( $classes );
				$has_sidebar_key = array_search( 'has-sidebar', $classes, true );
				$has_double_sidebars_key = array_search( 'double-sidebars', $classes, true );

				if ( is_array( $sidebar_1_original ) && ! empty( $sidebar_1_original ) && $sidebar_1_original[0] ) {
					$classes[] = 'has-sidebar';

					if ( is_array( $sidebar_2_original ) && ! empty( $sidebar_2_original ) && $sidebar_2_original[0] ) {
						$classes[] = 'double-sidebars';
					} elseif ( $has_double_sidebars_key ) {
						unset( $classes[ $has_double_sidebars_key ] );
					}
				} else {
					if ( $has_sidebar_key ) {
						unset( $classes[ $has_sidebar_key ] );
					}
					if ( $has_double_sidebars_key ) {
						unset( $classes[ $has_double_sidebars_key ] );
					}
				}
			}
		}

		return $classes;
	}

	public static function avada_sidebar( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			if ( self::has_avada_62() ) {
				$page_template = get_post_meta( $page_id, '_wp_page_template', true );

				if ( $page_template == '100-width.php' || $page_template == 'blank.php' ) {
					$value = 'None';
				} else {
					$value = fusion_get_option( 'pages_sidebar', false, $page_id );

					if ( ! is_array( $value ) && $value == 'default_sidebar' ) {
						if ( self::has_avada_79() ) {
							$sidebars_option_names = AWB_Widget_Framework()->get_sidebar_post_meta_option_names( 'page' );
						} else {
							$sidebars_option_names = avada_get_sidebar_post_meta_option_names( 'page' );
						}
						$value = Avada()->settings->get( $sidebars_option_names[0] );
					}
				}

				$value = $value != 'None' ? $value : '';
			} else {
				$meta = get_post_meta( $page_id, 'sbg_selected_sidebar_replacement', true );

				$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
				if ( ! empty( $meta ) ) {
					$value = $meta;
				}
			}
		}
		return $value;
	}

	public static function avada_sidebar_2( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			if ( self::has_avada_62() ) {
				$page_template = get_post_meta( $page_id, '_wp_page_template', true );

				if ( $page_template == '100-width.php' || $page_template == 'blank.php' ) {
					$value = 'None';
				} else {
					$value = fusion_get_option( 'pages_sidebar_2', false, $page_id );

					if ( ! is_array( $value ) && $value == 'default_sidebar' ) {
						if ( self::has_avada_79() ) {
							$sidebars_option_names = AWB_Widget_Framework()->get_sidebar_post_meta_option_names( 'page' );
						} else {
							$sidebars_option_names = avada_get_sidebar_post_meta_option_names( 'page' );
						}
						$value = Avada()->settings->get( $sidebars_option_names[1] );
					}
				}

				$value = $value != 'None' ? $value : '';
			} else {
				$meta = get_post_meta( $page_id, 'sbg_selected_sidebar_2_replacement', true );

				$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
				if ( ! empty( $meta ) ) {
					$value = $meta;
				}
			}
		}
		return $value;
	}

	public static function avada_sidebar_position( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			if ( self::has_avada_62() ) {
			} else {
				$meta = get_post_meta( $page_id, 'pyre_sidebar_position', true );

				$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
				if ( ! empty( $meta ) ) {
					$value = $meta;
				}
			}
		}
		return $value;
	}

	public static function avada_sidebar_sticky( $value ) {
		if ( $page_id = (int) self::gd_page_id() ) {
			if ( self::has_avada_62() ) {
			} else {
				$meta = get_post_meta( $page_id, 'pyre_sidebar_sticky', true );

				$meta = ! empty( $meta ) && is_array( $meta ) ? $meta[0] : $meta;
				if ( ! empty( $meta ) ) {
					$value = $meta;
				}
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
		global $geodir_beaver_archive_item;

		$enabled = FLBuilderModel::is_builder_enabled( $page_id );

		if ( $enabled ) {
			if ( empty( $geodir_beaver_archive_item ) ) {
				$geodir_beaver_archive_item = array();
			}

			if ( isset( $_GET['fl_builder'] ) && ! empty( $geodir_beaver_archive_item[ $page_id ] ) ) {
				return $geodir_beaver_archive_item[ $page_id ];
			}

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

				$geodir_beaver_archive_item[ $page_id ] = $content;
			}

			remove_filter( 'fl_builder_do_render_content', '__return_true', 11 );
		}

		return $content;
	}

	/**
	 * Filter beaver builder render content.
	 *
	 * @since 2.2.10
	 *
	 * @global bool $gd_skip_the_content True when GD loop is active.
	 *
	 * @param bool $render True to render content, else False.
	 * @param int  $post_id Current template post ID.
	 *
	 * @return bool True to render content, else False.
	 */
	public static function beaver_builder_do_render_content( $render, $post_id = 0 ) {
		global $gd_skip_the_content;

		// Skip render content on GD post content output.
		if ( ! empty( $gd_skip_the_content ) ) {
			$render = false;
		}

		return $render;
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
	 * Filter Enfold template builder redirect.
	 *
	 * @since 2.1.0.0
	 *
	 * @global array $avia_config Enfold settings.
	 *
	 * @param string $template The template.
	 * @return string The template.
	 */
	public static function avia_template_include( $template ) {
		global $avia_config;

		if ( $template_page_id = (int) self::gd_page_id() ) {
			$avia_config['builder_redirect_id'] = $template_page_id;
		}

		return $template;
	}

	/**
	 * Filter current post for shortcode.
	 *
	 * @since 2.1.0.0
	 *
	 * @param null|WP_Post $current_post Current post.
	 * @return null|WP_Post Current post.
	 */
	public static function avf_shortcode_handler_prepare_current_post( $post ) {
		if ( $template_page_id = (int) self::gd_page_id() ) {
			$post = get_post( $template_page_id );
		}

		return $post;
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

		$results = $wpdb->get_results( "SELECT p.ID, p.post_title FROM {$wpdb->postmeta} as pm INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID WHERE p.post_type = 'fl-theme-layout' AND p.post_status = 'publish' AND pm.meta_key = '_fl_theme_builder_locations' AND ( pm.meta_value LIKE 'a:0:{}' OR pm.meta_value = '' ) ORDER BY `p`.`post_title` ASC" );
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

	/**
	 * Filter the archive page title.
	 *
	 * @since 2.0.0.84
	 *
	 * @param string $title The archive page title.
	 * @return array The archive page title.
	 */
	public static function fl_theme_builder_page_archive_get_title( $title ) {
		if ( ! geodir_is_geodir_page() ) {
			return $title;
		}

		// Don't overwrite Yoast SEO or Rank Math SEO.
		if ( GeoDir_SEO::yoast_enabled() || GeoDir_SEO::rank_math_enabled() ) {
			return $title;
		}

		if ( $_title = GeoDir_SEO::set_meta() ) {
			$title = $_title;
		}

		return $title;
	}

	/**
	 * OceanWP theme filter GD post ID.
	 *
	 * @since 2.0.0.78
	 *
	 * @param int $post_id The post ID.
	 * @return int The post ID.
	 */
	public static function ocean_post_id( $post_id ) {
		if ( empty( $post_id ) && ( $_page_id = (int) self::gd_page_id() ) ) {
			$post_id = $_page_id;
		}

		return $post_id;
	}

	/**
	 * OceanWP theme filter GD page title.
	 *
	 * @since 2.0.0.75
	 *
	 * @param string $title The page title.
	 * @return string The page title.
	 */
	public static function ocean_title( $title ) {
		$_title = GeoDir_SEO::set_meta();

		if ( $_title ) {
			$title = $_title;
		}

		return $title;
	}

	/**
	 * OceanWP theme filter GD page subheading.
	 *
	 * @since 2.0.0.78
	 *
	 * @param string $subheading The page subheading.
	 * @return string The page subheading.
	 */
	public static function ocean_post_subheading( $subheading ) {
		$subheading = '';

		return $subheading;
	}

	/**
	 *
	 * @since 2.0.0.77
	 */
	public static function avada_hide_page_options( $hide_options = array() ) {
		$post_types = geodir_get_posttypes();

		foreach ( $post_types as $post_type ) {
			$hide_options[] = $post_type;
		}

		return $hide_options;
	}

	/**
	 *
	 * @since 2.0.0.77
	 */
	public static function avada_62_sidebar_context( $sidebar_type, $page_id, $sidebar, $global ) {
		if ( absint( $page_id ) > 0 && geodir_is_geodir_page_id( absint( $page_id ) ) ) {
			$page_template = get_post_meta( $page_id, '_wp_page_template', true );

			if ( $page_template == '100-width.php' || $page_template == 'blank.php' ) {
				$sidebar_type = 'None';
			}
		}

		return $sidebar_type;
	}

	/**
	 *
	 * @since 2.0.0.77
	 */
	public static function avada_fusion_page_id( $page_id ) {
		if ( ! empty( $page_id ) && absint( $page_id ) > 0 && strpos( $page_id, '-archive' ) === false ) {
			$post_type = get_post_type( $page_id );

			if ( $post_type && geodir_is_gd_post_type( $post_type ) ) {
				$page_id = geodir_details_page_id( $post_type );
			}
		} elseif ( $_page_id = (int) self::gd_page_id() ) {
			$page_id = $_page_id;
		}

		return $page_id;
	}

	/**
	 *
	 * @since 2.0.0.77
	 */
	public static function has_avada_62() {
		if ( defined( 'AVADA_VERSION' ) && version_compare( AVADA_VERSION, '6.2', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @since 2.2.19
	 */
	public static function has_avada_79() {
		if ( defined( 'AVADA_VERSION' ) && version_compare( AVADA_VERSION, '7.9', '>=' ) ) {
			return true;
		}

		return false;
	}

	/**
	 *
	 * @since 2.0.0.77
	 */
	public static function fusion_should_get_page_option( $page_option ) {
		if ( ! $page_option && geodir_is_geodir_page() ) {
			$page_option = true;
		}

		return $page_option;
	}

	/**
	 * Checks is the current page is a 100% width page.
	 *
	 * @since 2.0.0.77
	 *
	 * @param bool          $value The value from the filter.
	 * @param integer|false $page_id A custom page ID.
	 * @return bool
	 */
	public static function fusion_is_hundred_percent_template( $value = false, $page_id = false ) {
		if ( $value ) {
			return $value;
		}

		$page_id = (int) self::gd_page_id();
		if ( empty( $page_id ) ) {
			return $value;
		}

		$page_template = get_post_meta( $page_id, '_wp_page_template', true );

		if ( $page_template == '100-width.php' || $page_template == 'blank.php' ) {
			return true;
		}

		return false;
	}

	/**
	 * Add inline custom script to Fusion Builder.
	 *
	 * @since 2.3.70
	 *
	 * @return mixed
	 */
	public static function avada_fusion_builder_admin_script() {
		if ( wp_script_is( 'fusion_builder', 'enqueued' ) ) {
			$basic_nonce = wp_create_nonce( 'geodir_basic_nonce' );
			$cpt_options = geodir_cpt_rewrite_slug_options();

			ob_start();
			if ( 2 === 3 ) { ?><script type="text/javascript"><?php } ?>
jQuery(function($){
	$(document).on('change', '[data-element_type="gd_listings"] .fusion-builder-option select[name="post_type"]', function(){
		var $_wrap = $(this).closest('[data-element_type="gd_listings"]'), _cpt = $(this).val();

		if ($('select[name="category"]', $_wrap).length) {
			if (!window.gdCPTCats) {
				window.gdCPTCats = [];
			}
			var gdCptSlugs = <?php echo "[" . json_encode( $cpt_options ) . "]"; ?>;
			if (gdCptSlugs.length && gdCptSlugs[0][_cpt]) {
				var _optc, $_cat = $('select[name="category"]', $_wrap), gdCatPath = '<?php echo esc_url( rest_url( 'geodir/v2/GDCPTSLUG/categories/?per_page=100' ) ); ?>';gdCatPath = gdCatPath.replace('GDCPTSLUG', gdCptSlugs[0][_cpt]);
				_optc = '';
				if ($('option:first', $_cat).length && $('option:first', $_cat).val() === "0") {
					_optc += '<option value="0">' + $('option:first', $_cat).text() +'</option>';
				}
				if (window.gdCPTCats[gdCatPath]) {
					$.each(window.gdCPTCats[gdCatPath],function(key, term) {
						_optc += '<option value="' + term.id +'">' + term.name +'</option>';
					});
					$_cat.html(_optc).select2({'placeholder': fusionBuilderText.select_categories_or_leave_blank_for_all});
				} else {
					$.ajax({
						url: gdCatPath,
						type: 'GET',
						dataType: 'json',
						data: {}
					}).done(function(res) {
						if (res && typeof res == 'object') {
							window.gdCPTCats[gdCatPath] = res;
							$.each(res, function(key, term) {
								_optc += '<option value="' + term.id +'">' + term.name +'</option>'
							});
						}
						$_cat.html(_optc).select2({'placeholder': fusionBuilderText.select_categories_or_leave_blank_for_all});
					});
				}
			}
		}

		if ($('select[name="sort_by"]', $_wrap).length) {
			if (!window.gdCPTSort) {
				window.gdCPTSort = [];
			}

			if (window.gdCPTSort[_cpt]) {
				var _opts = '', res = window.gdCPTSort[_cpt];
				$.each(res, function(val, lbl) {
					_opts += '<option value="' + val +'">' + lbl +'</option>';
				});
				$('select[name="sort_by"]', $_wrap).html(_opts).select2();
			} else {
				$.ajax({
					url: '<?php echo esc_js( geodir_ajax_url( true ) ) ?>',
					type: 'POST',
					dataType: 'json',
					data: {
						'action': 'geodir_get_sort_options',
						'post_type': _cpt,
						'security': '<?php echo esc_js( $basic_nonce ) ?>'
					}
				}).done(function(res) {
					var _opts = '';
					if (res && typeof res == 'object') {
						window.gdCPTSort[_cpt] = res;
						$.each(res, function(val, lbl) {
							_opts += '<option value="' + val +'">' + lbl +'</option>';
						});
					}
					$('select[name="sort_by"]', $_wrap).html(_opts).select2();
				});
			}
		}
	});
});
		<?php if ( 2 === 3 ) { ?></script><?php }
			$script = ob_get_clean();

			wp_add_inline_script( 'fusion_builder', trim( $script ), 'after' );
		}
	}

	/**
	 * Load elementor template on GD archive pages.
	 *
	 * @since 2.0.0.78
	 *
	 * @param mixed $template The path of the template to include.
	 * @return string The template path.
	 */
	public static function elementor_template_include( $template ) {
		if ( defined( 'ELEMENTOR_VERSION' ) && class_exists( '\Elementor\Plugin' ) && ( $page_id = (int) self::gd_page_id() ) ) {
			$elementor_plugin = \Elementor\Plugin::$instance;
			$document = $elementor_plugin->documents->get_doc_for_frontend( $page_id );

			if ( ! empty( $document ) ) {
				/**
				 * @var \Elementor\Modules\PageTemplates\Module $page_templates_module
				 */
				$page_templates_module = $elementor_plugin->modules_manager->get_modules( 'page-templates' );
				$template_path = $page_templates_module->get_template_path( $document->get_meta( '_wp_page_template' ) );

				if ( $template_path ) {
					$template = $template_path;
				}
			}
		}

		return $template;
	}

	/**
	 * Filter Porto theme layout default option.
	 *
	 * @since 2.0.0.80
	 *
	 * @param bool $default Use default layout option or not.
	 * @return bool Default option.
	 */
	public static function porto_meta_use_default( $default ) {
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->term_id ) ) {
				$value = get_term_meta( $term->term_id, 'default', true );

				if ( 'default' == $value ) {
					return $default;
				}
			}

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$value = get_post_meta( $template_page_id, 'default', true );

			$default = ( 'default' != $value ) ? true : false;
		}

		return $default;
	}

	/**
	 * Filter Porto theme layout options.
	 *
	 * @since 2.0.0.80
	 *
	 * @param array $layout Use layout options.
	 * @return array Layout options.
	 */
	public static function porto_meta_layout( $layout ) {
		global $porto_settings;

		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->term_id ) ) {
				$value = get_term_meta( $term->term_id, 'default', true );

				if ( 'default' == $value ) {
					return $layout;
				}
			}

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$value    = get_post_meta( $template_page_id, 'layout', true );
			$sidebar  = get_post_meta( $template_page_id, 'sidebar', true );
			$sidebar2 = get_post_meta( $template_page_id, 'sidebar2', true );

			if ( empty( $sidebar2 ) ) {
				$sidebar2 = empty( $porto_settings['sidebar2'] ) ? 'secondary-sidebar' : $porto_settings['sidebar2'];
			}

			if ( ! in_array( $value, porto_options_sidebars() ) ) {
				$sidebar  = '';
				$sidebar2 = '';
			} elseif ( ! in_array( $value, porto_options_both_sidebars() ) ) {
				$sidebar2 = '';
			}

			$have_sidebar_menu = porto_have_sidebar_menu();
			if ( 'both-sidebar' == $value || 'wide-both-sidebar' == $value ) {
				if ( ! ( ( $sidebar && is_active_sidebar( $sidebar ) ) || $have_sidebar_menu ) ) {
					$value   = str_replace( 'both-sidebar', 'right-sidebar', $value );
					$sidebar = $sidebar2;
				}
				if ( ! ( ( $sidebar2 && is_active_sidebar( $sidebar2 ) ) || $have_sidebar_menu ) ) {
					$value = str_replace( 'both-sidebar', 'left-sidebar', $value );
				}
			}
			if ( ( 'left-sidebar' == $value || 'right-sidebar' == $value ) && ! ( ( $sidebar && is_active_sidebar( $sidebar ) ) || $have_sidebar_menu ) ) {
				$value = 'fullwidth';
			}
			if ( ( 'wide-left-sidebar' == $value || 'wide-right-sidebar' == $value ) && ! ( ( $sidebar && is_active_sidebar( $sidebar ) ) || $have_sidebar_menu ) ) {
				$value = 'widewidth';
			}

			$layout = array( $value, $sidebar, $sidebar2 );
		}

		return $layout;
	}

	/**
	 * Filter the Genesis layout.
	 *
	 * @since  2.0.0.80
	 *
	 * @param  string $layout Layout.
	 * @return string
	 */
	public static function genesis_site_layout( $layout ) {
		global $gd_post;

		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->term_id ) ) {
				$term_layout = $term ? get_term_meta( $term->term_id, 'layout', true ) : '';
				$site_layout = $term_layout ? $term_layout : genesis_get_option( 'site_layout' );
				$type = array( 'archive', $term->taxonomy, $term->term_id );

				// Use default layout as a fallback, if necessary.
				if ( genesis_get_layout( $site_layout, $type ) ) {
					return $site_layout;
				}
			}

			$post_type = ! empty( $gd_post ) && ! empty( $gd_post->ID ) ? get_post_type( $gd_post->ID ) : '';

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
			$post_type = geodir_get_current_posttype();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$_layout = get_post_meta( $template_page_id, '_genesis_layout', true );
			$site_layout = $_layout ? $_layout : genesis_get_option( 'site_layout' );
			$type = array( 'archive', 'post-type-archive-' . $post_type );

			// Use default layout as a fallback, if necessary.
			if ( genesis_get_layout( $site_layout, $type ) ) {
				$layout = $site_layout;
			}
		}

		return $layout;
	}

	/**
	 * Filter the Genesis simple menus menu locations.
	 *
	 * @since  2.0.0.80
	 *
	 * @param  array $mods Menu locations theme mods.
	 * @return array
	 */
	public static function genesis_simple_menus_set_menu_locations( $mods ) {
		$primary = NULL;
		$secondary = NULL;

		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->term_id ) ) {
				$_primary = get_term_meta( $term->term_id, '_gsm_primary', true );
				if ( ! empty( $_primary ) ) {
					$primary = $_primary;
				}

				$_secondary = get_term_meta( $term->term_id, '_gsm_menu', true );
				if ( ! empty( $_secondary ) ) {
					$secondary = $_secondary;
				}
			}

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			if ( $primary === NULL ) {
				$primary = get_post_meta( $template_page_id, '_gsm_primary', true );
			}

			if ( $secondary === NULL ) {
				$secondary = get_post_meta( $template_page_id, '_gsm_menu', true );
			}
		}

		if ( $primary || $secondary ) {
			if ( ! is_array( $mods ) ) {
				$mods = array();
			}

			if ( ! empty( $primary ) ) {
				$mods['primary'] = (int) $primary;
			}

			if ( ! empty( $secondary ) ) {
				$mods['secondary'] = (int) $secondary;
			}
		}

		return $mods;
	}

	/**
	 * Filter the Genesis simple sidebars widgets in each widget area.
	 *
	 * @since  2.0.0.80
	 *
	 * @param  array $widgets Widgets.
	 * @return array
	 */
	public static function genesis_simple_sidebars_set_sidebars_widgets( $widgets ) {
		if ( is_admin() ) {
			return $widgets;
		}

		$sidebars = array();

		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$term = get_queried_object();

			if ( ! empty( $term ) && ! empty( $term->term_id ) ) {
				$sidebars = array(
					'sidebar'=> get_term_meta( $term->term_id, '_ss_sidebar', true ),
					'sidebar-alt' => get_term_meta( $term->term_id, '_ss_sidebar_alt', true ),
					'header-right' => get_term_meta( $term->term_id, '_ss_header', true )
				);
			}

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			if ( empty( $sidebars['sidebar'] ) ) {
				$sidebars['sidebar'] = get_post_meta( $template_page_id, '_ss_sidebar', true );
			}
			if ( empty( $sidebars['sidebar-alt'] ) ) {
				$sidebars['sidebar-alt'] = get_post_meta( $template_page_id, '_ss_sidebar_alt', true );
			}
			if ( empty( $sidebars['header-right'] ) ) {
				$sidebars['header-right'] = get_post_meta( $template_page_id, '_ss_header', true );
			}
		}

		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $old_sidebar => $new_sidebar ) {
				if ( ! is_registered_sidebar( $old_sidebar ) ) {
					continue;
				}

				if ( $new_sidebar && ! empty( $widgets[ $new_sidebar ] ) ) {
					$widgets[ $old_sidebar ] = $widgets[ $new_sidebar ];
				}
			}
		}

		return $widgets;
	}

	/**
	 * Set post comments to show in Genesis separate comments.
	 *
	 * @since 2.1.0.0
	 *
	 * @global WP_Query $wp_query The WP Query object.
	 */
	public static function genesis_before_comments() {
		global $wp_query;

		// Genesis comments template shows comments separately for comment types.
		if ( empty( $wp_query->comments_by_type['comment'] ) && ! empty( $wp_query->comments ) ) {
			$wp_query->comments_by_type['comment'] = $wp_query->comments;
		}
	}

	/**
	 * Filter Page Builder Framework theme page layout.
	 *
	 * @since 2.0.0.80
	 *
	 * @param string $layout Page layout.
	 * @return string Filtered page layout.
	 */
	public static function wpbf_sidebar_layout( $layout ) {
		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$sidebar_position = get_post_meta( $template_page_id, 'wpbf_sidebar_position', true );
			$archive_sidebar_global = get_theme_mod( 'archive_sidebar_layout', 'global' );

			$sidebar = 'global' !== $archive_sidebar_global ? $archive_sidebar_global : $layout;
			$layout = $sidebar_position && 'global' !== $sidebar_position ? $sidebar_position : $sidebar;
		}

		return $layout;
	}

	/**
	 * Filter Page Builder Framework content.
	 *
	 * @since 2.0.0.80
	 */
	public static function wpbf_inner_content( $inner_content ) {
		global $gd_post;

		if ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) ) {
			$post_type = ! empty( $gd_post ) && ! empty( $gd_post->ID ) ? get_post_type( $gd_post->ID ) : '';

			$template_page_id = (int) self::gd_page_id();
		} elseif ( geodir_is_page( 'search' ) ) {
			$post_type = geodir_get_current_posttype();

			$template_page_id = (int) self::gd_page_id();
		} else {
			$template_page_id = 0;
		}

		if ( ! empty( $template_page_id ) ) {
			$options = get_post_meta( $template_page_id, 'wpbf_options', true );

			// Check if template is set to full width.
			$fullwidth = $options ? in_array( 'full-width', $options ) : false;
			if ( $fullwidth ) {
				return false;
			}

			// Check if template is set to contained.
			$contained = $options ? in_array( 'contained', $options ) : false;

			// Check if Premium Add-On is active and template is not set to contained.
			if ( wpbf_is_premium() && ! $contained ) {
				$wpbf_settings = get_option( 'wpbf_settings' );

				// Get array of post types that are set to full width under Appearance > Theme Settings > Global Template Settings.
				$fullwidth_global = isset( $wpbf_settings['wpbf_fullwidth_global'] ) ? $wpbf_settings['wpbf_fullwidth_global'] : array();

				// If current post type has been set to full-width globally, set $inner_content to false.
				$inner_content = $fullwidth_global && in_array( $post_type, $fullwidth_global ) ? false : $inner_content;
			}
		}

		return $inner_content;
	}

	/**
	 * Body classes.
	 *
	 * @since 2.0.0.80
	 *
	 * @param array $classes The body classes.
	 * @return array The updated body classes.
	 */
	public static function wpbf_body_class( $classes ) {
		if ( $template_page_id = (int) self::gd_page_id() ) {
			if ( in_array( 'wpbf-no-sidebar', $classes ) ) {
				$_classes = implode( '::::', $classes );
				$_classes = str_replace( '::::wpbf-no-sidebar', '', $_classes );
				$classes = explode( '::::', $_classes );
			}

			if ( ! self::is_page_template( 'page-sidebar.php' ) ) {
				$classes[] = 'wpbf-no-sidebar';
			}
		}

		return $classes;
	}

	/**
	 * Determines whether currently in a page template.
	 *
	 * @since 2.0.0.80
	 *
	 *
	 * @param string|array $template The specific template name or array of templates to match.
	 * @return bool True on success, false on failure.
	 */
	public static function is_page_template( $template = '' ) {
		$page_template = get_page_template_slug( get_queried_object_id() );

		if ( empty( $template ) ) {
			return (bool) $page_template;
		}

		if ( $template == $page_template ) {
			return true;
		}

		if ( is_array( $template ) ) {
			if ( ( in_array( 'default', $template, true ) && ! $page_template )
				|| in_array( $page_template, $template, true )
			) {
				return true;
			}
		}

		return ( 'default' === $template && ! $page_template );
	}

	/**
	 * Filters available template settings options.
	 *
	 * @since 2.0.0.81
	 *
	 * @param array
	 */
	public static function et_theme_builder_template_settings_options( $options ) {
		if ( ! empty( $options['other'] ) ) {
			$options['other']['settings'][] = array(
				'id'       => 'gd_search',
				'label'    => et_core_intentionally_unescaped( __( 'GD Search Results', 'geodirectory' ), 'react_jsx' ),
				'priority' => 0,
				'validate' => array( __CLASS__, 'et_theme_builder_template_setting_validate_gd_search' ),
			);
		}

		return $options;
	}

	/**
	 * Validate GD search.
	 *
	 * @since 2.0.0.81
	 *
	 * @param string $type
	 * @param string $subtype
	 * @param integer $id
	 * @param string[] $setting
	 *
	 * @return bool
	 */
	public static function et_theme_builder_template_setting_validate_gd_search( $type, $subtype, $id, $setting ) {
		return geodir_is_page( 'search' );
	}

	/**
	 * Filter Divi default post types.
	 *
	 * @since 2.3.17
	 *
	 * @param array $post_types Post types
	 * @return array Filtered post types.
	 */
	public static function et_builder_default_post_types( $post_types ) {
		if ( geodir_is_geodir_page() && ( $_post_types = geodir_get_posttypes() ) ) {
			$post_types = array_merge( $post_types, $_post_types );
		}

		return $post_types;
	}

	/**
	 * Filter UpSolution Core page area id.
	 *
	 * @since 2.1.0.11
	 *
	 * @param int $area_id Page area id.
	 * @param string $area Page area.
	 * @return int Page area id.
	 */
	public static function us_get_page_area_id( $area_id, $area = 'none' ) {
		$postID = self::gd_page_id();

		if ( ! $postID || $area == 'none' ) {
			return $area_id;
		}

		$area_id = usof_meta( 'us_' . $area . '_id', $postID );

		// Reset Pages defaults
		if ( $area_id == '__defaults__' ) {
			$area_id = us_get_option( $area . '_id', '' );
		}

		// If you have WPML or Polylang plugins then check the translations
		if ( has_filter( 'us_tr_object_id' ) && is_numeric( $area_id ) ) {
			$area_id = (int) apply_filters( 'us_tr_object_id', $area_id );
		}

		return $area_id;
	}

	/**
	 * Get the geodirectory templates theme path.
	 *
	 * @since 2.1.0.11
	 *
	 * @return string Template path.
	 */
	public static function get_theme_template_path() {
		$template = get_template();
		$theme_root = get_theme_root( $template );

		$theme_template_path = $theme_root . '/' . $template . '/' . untrailingslashit( geodir_get_theme_template_dir_name() );

		return $theme_template_path;
	}

	/**
	 * Oxygen locate theme template.
	 *
	 * @since 2.1.0.11
	 *
	 * @param string $template The template.
	 * @return string The theme template.
	 */
	public static function oxygen_locate_template( $template ) {
		$located = '';

		if ( ! $template ) {
			return $located;
		}

		$has_filter = has_filter( 'template', 'ct_oxygen_template_name' );

		// Remove template filter
		if ( $has_filter ) {
			remove_filter( 'template', 'ct_oxygen_template_name' );
		}

		$_located = self::get_theme_template_path() . '/' . $template;

		if ( file_exists( $_located ) ) {
			$located = $_located;
		}

		// Add template filter
		if ( $has_filter ) {
			add_filter( 'template', 'ct_oxygen_template_name' );
		}

		return $located;
	}

	/**
	 * Oxygen override theme template.
	 *
	 * @since 2.1.0.11
	 *
	 * @param string $located Located template.
	 * @param string $template_name Template name.
	 * @param array $located Template args.
	 * @param string $template_path Template path.
	 * @param string $default_path Template default path.
	 * @return string Located template.
	 */
	public static function oxygen_override_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( $_located = self::oxygen_locate_template( $template_name ) ) {
			$located = $_located;
		}

		return $located;
	}

	/**
	 * Oxygen override theme template part.
	 *
	 * @since 2.1.0.11
	 *
	 * @param string $template The template.
	 * @param string $slug Template slug.
	 * @param string $name Template name.
	 * @return string Located template part.
	 */
	public static function oxygen_override_template_part( $template, $slug, $name ) {
		if ( ! $slug && ! $name ) {
			return $template;
		}

		$_template = '';

		if ( $name ) {
			// Look in yourtheme/geodirectory/slug-name.php
			$_template = self::oxygen_locate_template( "{$slug}-{$name}.php" );
		} else {
			// Look in yourtheme/geodirectory/slug.php
			$_template = self::oxygen_locate_template( "{$slug}.php" );
		}

		if ( $_template ) {
			return $_template;
		}

		// Get default slug-name.php
		if ( $name && file_exists( geodir_get_templates_dir() . "/{$slug}-{$name}.php" ) ) {
			$_template = geodir_get_templates_dir() . "/{$slug}-{$name}.php";
		} else if ( ! $name && file_exists( geodir_get_templates_dir() . "/{$slug}.php" ) ) {
			$_template = geodir_get_templates_dir() . "/{$slug}.php";
		}

		if ( $_template ) {
			return $_template;
		}

		// Look in yourtheme/geodirectory/slug.php
		$_template = self::oxygen_locate_template( "{$slug}.php" );

		if ( $_template ) {
			$template = $_template;
		}

		return $template;
	}

	/**
	 * Add attributes to the lightbox link element.
	 *
	 * @since 2.1.0.12
	 *
	 * @param string $attrs Attributes.
	 * @return string Attributes.
	 */
	public static function link_to_lightbox_attrs( $attrs = '' ) {
		// Elementor disable lightbox.
		if( defined( 'ELEMENTOR_VERSION' ) ) {
			$attrs .= ' data-elementor-open-lightbox="no"';
		}

		return $attrs;
	}

	/**
	 * Borlabs Cookie map setting.
	 *
	 * @since 2.1.0.13
	 *
	 * @param array $settings General settings.
	 * @return array General settings.
	 */
	public static function borlabs_cookie_setting( $settings ) {
		$_settings = array();

		foreach ( $settings as $key => $setting ) {
			$_settings[] = $setting;

			if ( ! empty( $setting['id'] ) && $setting['id'] == 'map_cache' ) {
				$_setting = array(
					'name' => __( 'Borlabs Cookie Integration', 'geodirectory'),
					'desc' => __( 'Enable Borlabs Cookie integration for GeoDirectory maps.', 'geodirectory' ),
					'id' => 'borlabs_cookie',
					'type' => 'checkbox',
					'default' => '0',
					'desc_tip' => false,
					'advanced' => true
				);

				if ( defined( 'BORLABS_COOKIE_VERSION' ) && version_compare( BORLABS_COOKIE_VERSION, '3.0', '>=' ) ) {
					$_setting['desc'] = __( 'NOTE: Go to Borlabs Cookie > Library > Search "GeoDirectory" & install package.', 'geodirectory' );
					$_setting['custom_attributes'] = array(
						'disabled' => 'disabled'
					);
				}
				$_settings[] = $_setting;
			}
		}

		return $_settings;
	}

	/**
	 * Borlabs Cookie map id.
	 *
	 * @since 2.1.0.13
	 *
	 * @return string Map id.
	 */
	public static function borlabs_cookie_id() {
		if ( GeoDir_Maps::active_map() == 'osm' ) {
			$content_id = 'openstreetmap'; // OpenStreetMap
		} else if ( GeoDir_Maps::active_map() == 'none' ) {
			$content_id = ''; // None
		} else {
			$content_id = 'googlemaps'; // Google Maps
		}

		return $content_id;
	}

	/**
	 * Borlabs Cookie set lazy load map.
	 *
	 * @since 2.1.0.13
	 *
	 * @param string $lazy_load Lazy load type.
	 * @return string Filtered lazy load map.
	 */
	public static function borlabs_cookie_setup( $lazy_load = '' ) {
		if ( $lazy_load != 'click' && ( $cookie_id = self::borlabs_cookie_id() ) ) {
			$contentBlockerData = BorlabsCookie\Cookie\Frontend\ContentBlocker::getInstance()->getContentBlockerData( $cookie_id );

			// Apply when content blocker is active.
			if ( ! empty( $contentBlockerData ) && ! BorlabsCookie\Cookie\Frontend\Cookies::getInstance()->checkConsent( $cookie_id ) ) {
				$lazy_load = 'click';
			}
		}

		return $lazy_load;
	}

	/**
	 * Borlabs Cookie filter lazy load map.
	 *
	 * @since 2.3.52
	 *
	 * @param string $lazy_load Lazy load type.
	 * @return string Filtered lazy load map.
	 */
	public static function borlabs_cookie_lazy_load_map( $lazy_load = '' ) {
		if ( $lazy_load && is_admin() && ! wp_doing_ajax() ) {
			$lazy_load = ''; // Disable lazy load map in backend.
		}

		return $lazy_load;
	}

	/**
	 * Wrap map content by Borlabs Cookie.
	 *
	 * @since 2.1.0.13
	 *
	 * @param string $output Map widget content.
	 * @param array $instance Widget instance.
	 * @param array $args Widget args.
	 * @param array $super_duper Super Duper class.
	 * @return string Map widget content.
	 */
	public static function borlabs_cookie_wrap( $output, $instance, $args, $super_duper ) {
		if ( $output != '' && ! empty( $super_duper->options['base_id'] ) && in_array( $super_duper->options['base_id'], array( 'gd_map' ) ) && ( $cookie_id = self::borlabs_cookie_id() ) ) {
			$contentBlockerData = BorlabsCookie\Cookie\Frontend\ContentBlocker::getInstance()->getContentBlockerData( $cookie_id );

			// Apply when content blocker is active.
			if ( ! empty( $contentBlockerData ) && ! BorlabsCookie\Cookie\Frontend\Cookies::getInstance()->checkConsent( $cookie_id ) ) {
				$script = geodir_lazy_load_map() == 'click' ? '<script type="text/javascript">jQuery(function($){$(".geodir-lazyload-div").trigger("click");});</script>' : '';

				$output = do_shortcode( '[borlabs-cookie id="' . $cookie_id . '" type="content-blocker"]' . $output . $script . '[/borlabs-cookie]' );
			}
		}

		return $output;
	}

	/**
	 * Complianz GDPR integration.
	 *
	 * @since 2.1.0.17
	 *
	 * @param array $integrations Plugins integrations.
	 * @return array Plugins integrations.
	 */
	public static function complianz_gdpr_integration( $integrations ) {
		$integrations['geodirectory'] = array(
			'constant_or_function' => 'GeoDir',
			'label'                => 'GeoDirectory',
			'firstparty_marketing' => false,
		);

		return $integrations;
	}

	/**
	 * Complianz GDPR integration.
	 *
	 * @since 2.1.0.17
	 *
	 * @param array $integrations Plugins integrations.
	 * @return array Plugins integrations.
	 */
	public static function complianz_integration_path( $path, $plugin ) {
		if ( $plugin == 'geodirectory' ) {
			$path = GEODIRECTORY_PLUGIN_DIR . 'includes/complianz-gdpr.php';
		}

		return $path;
	}

	/**
	 * Set admin notices on GD page templates for Divi builder.
	 *
	 * @since 2.1.0.17
	 *
	 * @global string $pagenow Current page type.
	 * @global object $post The post object.
	 */
	public static function page_builder_notices() {
		global $pagenow, $post;

		if ( $pagenow === 'post.php' && ! empty( $post ) && ! empty( $post->post_type ) && $post->post_type == 'page' && function_exists( 'et_divi_load_scripts_styles' ) && geodir_is_geodir_page_id( (int) $post->ID ) ) {
			echo '<div class="notice notice-warning is-dismissible geodir-builder-notice"><p>';
			echo wp_sprintf( __( 'Divi Users: Please check this %sdocumentation%s to setup GeoDirectory pages with Divi Builder.', 'geodirectory' ), '<a href="https://docs.wpgeodirectory.com/article/210-getting-started-with-divi-builder" target="_blank">', '</a>' );
			echo '</p></div>';
		}
	}

	/**
	 * Handle pre AJAX widget listings.
	 *
	 * @since 2.1.1.12
	 *
	 * @param array $data Listings widget parameters.
	 */
	public static function ajax_listings_before( $data ) {
		// Kadence Blocks Compatibility.
		if ( defined( 'KADENCE_BLOCKS_VERSION' ) ) {
			add_filter( 'kadence_blocks_force_render_inline_css_in_content', '__return_true', 10, 3 );
		}
	}

	/**
	 * Register scripts on block theme.
	 *
	 * Block theme like Twenty Twenty Two has issue in loading scripts.
	 *
	 * @since 2.1.1.14
	 * @since 2.3.30   Breakdance Page Builder(themeless) compatibility.
	 *
	 * @param array  $options Super Duper block options.
	 * @param object $super_duper Super Duper object.
	 */
	public static function block_theme_load_scripts( $options, $super_duper ) {
		global $geodir_frontend_scripts_loaded;

		// Scripts already loaded.
		if ( $geodir_frontend_scripts_loaded ) {
			return;
		}

		// Check block theme / BREAKDANCE theme.
		if ( ! ( ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) || defined( '__BREAKDANCE_VERSION' ) ) ) {
			return;
		}

		if ( geodir_load_scripts_on_call() && ( wp_doing_ajax() || wp_doing_cron() || strpos( $options['base_id'], 'gd_' ) ) ) {
			return;
		}

		if ( ! wp_script_is( 'geodir', 'registered' ) ) {
			$geodir_frontend_scripts_loaded = true;

			// call scripts after the WP object is loaded so we can use page conditions
			add_action('wp', array('GeoDir_Frontend_Scripts','load_scripts'), 10);
		}
	}

	/**
	 * Disable Relevanssi search on GD Search page.
	 *
	 * @since 2.3.14
	 *
	 * @param bool   $search_ok True to use Relevanssi else false.
	 * @param object $query The Query request.
	 */
	public static function relevanssi_search_ok( $search_ok, $query ){
		if ( $search_ok && geodir_is_page( 'search' ) ) {
			$search_ok = false;
		}

		return $search_ok;
	}

	/**
	 * Prevent Relevanssi search request on GD Search page.
	 *
	 * @since 2.3.14
	 *
	 * @param bool   $default_request True to use Relevanssi request else false.
	 * @param object $query The Query request.
	 */
	public static function relevanssi_prevent_default_request( $default_request, $query ){
		if ( $default_request && geodir_is_page( 'search' ) ) {
			$default_request = false;
		}

		return $default_request;
	}

	/**
	 * Filter Kallyas theme Zion builder preview url for GD page.
	 *
	 * @since 2.3.15
	 *
	 * @param string $preview_url Preview url.
	 * @return string Filtered preview url.
	 */
	public static function znb_edit_url( $preview_url ) {
		if ( geodir_is_page( 'single' ) && ( $page_id = (int) self::gd_page_id() ) ) {
			$preview_url = get_preview_post_link( $page_id );
		}

		return $preview_url;
	}

	/**
	 * Astra + Spectra enqueue post assets on GD pages.
	 *
	 * @since 2.3.31
	 */
	public static function spectra_uagb_post_assets_enqueue_scripts() {
		if ( ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'detail' ) || geodir_is_page( 'search' ) ) && ( $page_id = self::gd_page_id() ) ) {
			$current_post_assets = new UAGB_Post_Assets( $page_id );
			$current_post_assets->enqueue_scripts();
			if ( geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) {
				$post_type = geodir_get_current_posttype();
				// also load archive item style.
				$archive_item_page_id = geodir_archive_item_page_id( $post_type );

				$current_post_assets = new UAGB_Post_Assets( $archive_item_page_id );
				$current_post_assets->enqueue_scripts();
			}
		}
	}

	/**
	 * Filter Blocksy theme options.
	 *
	 * @since 2.3.36
	 *
	 * @param mixed $value Option value.
	 * @return mixed Filtered value.
	 */
	public static function blocksy_get_theme_mod( $value ) {
		if ( ! ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) ) ) {
			return $value;
		}

		$current_filter = current_filter();

		if ( $current_filter == 'theme_mod_search_hero_elements' && ! empty( $value ) && is_array( $value ) ) {
			foreach ( $value as $key => $option ) {
				if ( is_array( $option ) && isset( $option['id'] ) && $option['id'] == 'custom_description' ) {
					$option['enabled'] = false;

					$value[ $key ] = $option;
				}
			}
		} else if ( in_array( $current_filter, array( 'theme_mod_custom_description', 'theme_mod_hero_custom_description' ) ) && ! empty( $value ) && is_scalar( $value ) ) {
			$value = false;
		}

		return $value;
	}

	/**
	 * Adjust WP_Query to work loop with Thrive template.
	 *
	 * @since 2.3.59
	 *
	 * @param array  $widget_args Widget args.
	 * @param string $id_base Widget ID.
	 */
	public static function thrive_loop_setup_wp_query( $widget_args, $id_base ) {
		global $wp_query, $gd_temp_wp_query;

		if ( geodir_is_page( 'search' ) && ! empty( $gd_temp_wp_query ) && empty( $wp_query->posts ) && ! empty( $wp_query->query['posts_per_page'] ) ) {
			$wp_query->posts = $gd_temp_wp_query;
			$wp_query->post_count = count( $gd_temp_wp_query );
			$wp_query->found_posts = $wp_query->post_count;
		}
	}

	/**
	 * Set GD page template content to Thrive template.
	 *
	 * @since 2.3.59
	 *
	 * @param string $content Template content.
	 * @param object $object Template object.
	 * @param string $type Section type.
	 * @return string Page template content.
	 */
	public static function thrive_theme_section_default_content( $content, $object, $type ) {
		if ( $type == 'content' && ( geodir_is_page( 'archive' ) || geodir_is_page( 'search' ) || geodir_is_page( 'single' ) ) ) {
			$content = get_post_field( 'post_content', (int) GeoDir_Compatibility::gd_page_id() );

			// Run the shortcodes on the content
			$content = do_shortcode( $content );

			// Run block content if its available
			if ( function_exists( 'do_blocks' ) ) {
				$content = do_blocks( $content );
			}

			if ( $content ) {
				$content = '<style>.thrv_wrapper .bsui div,.thrv_wrapper div.bsui{box-sizing:border-box}</style>' . $content;
			}
		}

		return $content;
	}
}

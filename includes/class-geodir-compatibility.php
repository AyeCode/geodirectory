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
		add_filter( 'option_wpseo_taxonomy_meta', array(__CLASS__,'wpseo_taxonomy_meta'), 10, 2 );
		// add setting to be able to disable yoast on GD pages
		add_filter( 'geodir_seo_options', array(__CLASS__,'wpseo_disable'), 10 );

		/*######################################################
		Disqus (comments system) :: If Disqus plugin is active, do some fixes to show on blogs but no on GD post types
		######################################################*/
		if ( function_exists( 'dsq_can_replace' ) ) {
			remove_filter( 'comments_template', array(__CLASS__,'dsq_comments_template') );
			add_filter( 'comments_template', array(__CLASS__,'dsq_comments_template'), 100 );
			add_filter( 'pre_option_disqus_active', array(__CLASS__,'disqus_active'), 10, 1 );
		}

		/*######################################################
		JetPack :: Disable JetPack comments on GD post types.
		######################################################*/
		add_action( 'plugins_loaded', array(__CLASS__,'jetpack') );

		/*######################################################
		Ninja Forms :: Add our own form tags.
		######################################################*/
		add_action( 'ninja_forms_loaded', array(__CLASS__,'ninja_forms'));

		/*######################################################
		Primer (theme) :: Fix single page title.
		######################################################*/
		add_filter('primer_the_page_title',array(__CLASS__,'primer_title'));

		/*######################################################
		Beaver Builder :: Fix font-awesome.
		######################################################*/
		add_filter('wp_print_scripts',array(__CLASS__,'beaver_builder'),100);

		/*######################################################
		WP Easy Updates :: Allow beta addons if set
		######################################################*/
		add_filter('wp_easy_updates_api_params',array(__CLASS__,'wp_easy_updates'),10, 2);

		/*######################################################
		Genesis (theme) :: Fix archive pages excerpt.
		######################################################*/
		add_filter('genesis_pre_get_option_content_archive',array(__CLASS__,'genesis_content_archive'));

		/*######################################################
		Kleo (theme) :: Fix page titles.
		######################################################*/
		add_filter( 'kleo_title_args', array( __CLASS__, 'kleo_title_args' ) );

		// after_setup_theme checks
		add_action( 'after_setup_theme', array(__CLASS__,'for_later_checks') );

	}

	/**
	 * Fix the page title args on Kleo theme.
	 *
	 * @param $args
	 *
	 * @return mixed
	 */
	public static function kleo_title_args($args){

		$title = GeoDir_SEO::set_meta();
		if($title){
			$args['title'] = $title;
		}

		return $args;
	}

	public static function genesis_content_archive($val){

		if(geodir_is_page('archive') || geodir_is_page('post_type')){
			$val = 'excerpts';
		}

		return $val;
	}

	/**
	 * @param $api_params
	 * @param $_src
	 *
	 * @return mixed
	 */
	public static function wp_easy_updates($api_params,$_src){

		if ( geodir_get_option('admin_enable_beta') && strpos($_src, 'wpgeodirectory.com') !== false) {
			if(!empty($api_params['update_array'])){
				foreach($api_params['update_array'] as $key => $val){
					$api_params['update_array'][$key]['beta'] = true;
				}
			}

			$api_params['beta'] = true;

		}

		return $api_params;
	}

	public static function beaver_builder(){
		if(isset($_REQUEST['fl_builder'])){
			wp_dequeue_script( 'font-awesome' );
		}
	}

	public static function wpseo_disable($options){

		if( defined( 'WPSEO_VERSION' ) ){
			$new_options = array(
				array(
					'title' => __( 'Yoast SEO detected', 'geodirectory' ),
					'type'  => 'title',
					'desc'  => geodir_notification( array('yoast_detected'=>__('The Yoast SEO plugin has been detected and will take over the GeoDirectory Settings unless disabled below.','geodirectory')) ),
					'id'    => 'yoast_detected',
					//'desc_tip' => true,
				),
				array(
					'name' => __( 'Disable Yoast', 'geodirectory' ),
					'desc' => __( 'Disable overwrite by Yoast SEO titles & metas on GD pages?', 'geodirectory' ),
					'id'   => 'wpseo_disable',
					'type' => 'checkbox',
					'default'  => '0',
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
	public static function ninja_forms(){
		require_once( GEODIRECTORY_PLUGIN_DIR . 'includes/widgets/class-geodir-widget-ninja-forms.php' );
		Ninja_Forms()->merge_tags[ 'geodirectory' ] = new GeoDir_Ninja_Forms_MergeTags();
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
	public static function theme_single_template($theme = ''){



		if(!$theme){
			$theme = get_template();
		}

//		echo '###'.$theme;

		$themes = array();

		if(get_theme_support( 'geodirectory' )){
			$themes[$theme] = 'geodirectory.php';
		}else{
			$themes = array(
				'twentyseventeen'   => 'single.php',
				'primer'   => 'page.php',
			);
		}


		return isset($themes[$theme]) ? $themes[$theme] : '';
	}

	/**
	 * Fix details page title, primer theme breaks it.
     *
     * @since 2.0.0
	 */
	public static function for_later_checks(){
		/*######################################################
		Boss. (BuddyBoss)
		######################################################*/
		if(class_exists('BuddyBoss_Theme')){
			add_action( 'wp_enqueue_scripts', array(__CLASS__,'buddyboss'), 100 );
			add_filter( 'body_class', array(__CLASS__,'buddyboss_body_class') );
		}

		/*######################################################
		Genesis theme (Corporate Pro)
		######################################################*/
		if(function_exists('corporate_body_classes')){
			add_filter( 'body_class', array(__CLASS__,'genesis_corporate_pro_body_class') );
		}
	}

	/**
	 * Fix some layut issues with genesis coroporate pro theme.
	 * 
	 * @param $classes
	 *
	 * @return array
	 */
	public static function genesis_corporate_pro_body_class($classes){

		if ( geodir_is_geodir_page() ) {
			if (($key = array_search('archive', $classes)) !== false) {
				unset($classes[$key]);
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
     * @return string $title.
     */
	public static function primer_title($title){

		if(geodir_is_page('single')){
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
	public static function buddyboss_body_class($classes){

		if ( geodir_is_geodir_page() ) {
			$classes[] = 'page';
		}

		return $classes;
	}

}
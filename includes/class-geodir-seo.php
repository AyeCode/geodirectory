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
		if ( ! is_admin() ) {
			add_filter( 'page_link', array( __CLASS__, 'page_link' ), 10, 3 );
		}
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
//
//				// page title
				add_filter('the_title',array(__CLASS__,'output_title'),10,2);
				add_filter('get_the_archive_title',array(__CLASS__,'output_title'),10);
//
//				// setup vars
				add_action('pre_get_document_title', array(__CLASS__,'set_meta'),9);
			}
			return;
		}


		// set a global so we don't change the menu items titles
		add_filter('pre_wp_nav_menu',array(__CLASS__,'set_menu_global'),10,2);
		add_filter('wp_nav_menu',array(__CLASS__,'unset_menu_global'));

		// meta title
		add_filter('wp_title', array(__CLASS__,'output_meta_title'),1000,2);
		add_filter('pre_get_document_title', array(__CLASS__,'output_meta_title'), 1000);

		// page title
		add_filter('the_title',array(__CLASS__,'output_title'),10,2);
		add_filter('get_the_archive_title',array(__CLASS__,'output_title'),10);

		// setup vars
		add_action('pre_get_document_title', array(__CLASS__,'set_meta'),9);

		// meta description
		if(defined( 'WPSEO_VERSION')){
			add_filter('wpseo_metadesc', array(__CLASS__,'get_description'), 10, 1);
		}elseif( defined( 'RANK_MATH_VERSION') ){
			add_filter('rank_math/frontend/description', array(__CLASS__,'get_description'), 10, 1);
			add_filter('rank_math/frontend/title', array(__CLASS__,'get_title'), 10, 1);
		}else{
			add_action('wp_head', array(__CLASS__,'output_description'));
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
	public static function output_title($title = '', $id = 0){
		global $wp_query;
		// in some themes the object id is missing so we fix it

		if($id && isset($wp_query->post->ID) && geodir_is_geodir_page_id($id)){
			$query_object_id = $wp_query->post->ID;
		}else{
			$query_object_id = get_queried_object_id();
		}


//		echo $query_object_id.'###'.$id.self::$title;
		if(self::$title && empty($id)  && !self::$doing_menu ){
			$title = self::$title;
		}elseif(self::$title && !empty($id) && $query_object_id == $id && !self::$doing_menu ){
			$title = self::$title;
			/**
			 * Filter page title to replace variables.
			 *
			 * @param string $title The page title including variables.
			 * @param string $id The page id.
			 */
			$title = apply_filters('geodir_seo_title', __($title, 'geodirectory'), $title, $id);
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
	public static function output_meta_title($title = '', $sep = ''){
		if(self::$meta_title){
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
		return apply_filters('geodir_seo_meta_title', __($title, 'geodirectory'), self::$gd_page, $sep);
	}

	/**
	 * Get a page meta description.
	 *
	 * @since 2.0.0
	 */
	public static function get_description($description=''){
		$meta_description = self::$meta_description;

		if(!empty($meta_description )){
			$description = $meta_description;
		}

		// escape
		if(!empty($description)){
			$description = esc_attr($description);
		}
		/**
		 * Filter SEO meta description.
		 *
		 * @since 1.0.0
		 *
		 * @param string $description Meta description.
		 */
		return apply_filters( 'geodir_seo_meta_description', $description,$meta_description);
	}

	/**
	 * Get a page meta title.
	 *
	 * @since 2.0.0
	 */
	public static function get_title( $title = '' ){
		$meta_title= self::$meta_title;

		if ( !empty( $meta_title ) ) {
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
		return apply_filters( 'geodir_seo_meta_title', $title, $meta_title);
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
	public static function set_meta(){
		$gd_settings = geodir_get_settings();
		//print_r( $gd_settings );
		if(geodir_is_page( 'pt' )){self::$gd_page = 'pt';
			$post_type       = geodir_get_current_posttype();
			$post_type_info  = get_post_type_object( $post_type );
			if(isset($post_type_info->seo['title']) && !empty($post_type_info->seo['title'])){
				self::$title = $post_type_info->seo['title'];
			}else{
				self::$title = !empty($gd_settings['seo_cpt_title']) ? $gd_settings['seo_cpt_title'] : GeoDir_Defaults::seo_cpt_title();
			}
			if(isset($post_type_info->seo['meta_title']) && !empty($post_type_info->seo['meta_title'])){
				self::$meta_title = $post_type_info->seo['meta_title'];
			}else{
				self::$meta_title = !empty($gd_settings['seo_cpt_meta_title']) ? $gd_settings['seo_cpt_meta_title'] : GeoDir_Defaults::seo_cpt_meta_title();
			}
			if(isset($post_type_info->seo['meta_description']) && !empty($post_type_info->seo['meta_description'])){
				self::$meta_description = $post_type_info->seo['meta_description'];
			}else{
				self::$meta_description = !empty($gd_settings['seo_cpt_meta_description']) ? $gd_settings['seo_cpt_meta_description'] : GeoDir_Defaults::seo_cpt_meta_description();
			}
		}elseif(geodir_is_page( 'archive' )){self::$gd_page = 'archive';
			$queried_object = get_queried_object();
			if(isset($queried_object->taxonomy) && geodir_taxonomy_type($queried_object->taxonomy) == 'category' && geodir_is_gd_taxonomy($queried_object->taxonomy)){
				self::$title = !empty($gd_settings['seo_cat_archive_title']) ? $gd_settings['seo_cat_archive_title'] : GeoDir_Defaults::seo_cat_archive_title();
				self::$meta_title = !empty($gd_settings['seo_cat_archive_meta_title']) ? $gd_settings['seo_cat_archive_meta_title'] : GeoDir_Defaults::seo_cat_archive_meta_title();
				self::$meta_description = !empty($gd_settings['seo_cat_archive_meta_description']) ? $gd_settings['seo_cat_archive_meta_description'] : GeoDir_Defaults::seo_cat_archive_meta_description();
			}elseif(isset($queried_object->taxonomy) && geodir_taxonomy_type($queried_object->taxonomy) == 'tag' && geodir_is_gd_taxonomy($queried_object->taxonomy)){
				self::$title = !empty($gd_settings['seo_tag_archive_title']) ? $gd_settings['seo_tag_archive_title'] : GeoDir_Defaults::seo_tag_archive_title();
				self::$meta_title = !empty($gd_settings['seo_tag_archive_meta_title']) ? $gd_settings['seo_tag_archive_meta_title'] : GeoDir_Defaults::seo_tag_archive_meta_title();
				self::$meta_description = !empty($gd_settings['seo_tag_archive_meta_description']) ? $gd_settings['seo_tag_archive_meta_description'] : GeoDir_Defaults::seo_tag_archive_meta_description();
			}
		}elseif(geodir_is_page( 'single' )){self::$gd_page = 'single';
			self::$title = !empty($gd_settings['seo_single_title']) ? $gd_settings['seo_single_title'] : GeoDir_Defaults::seo_single_title();
			self::$meta_title = !empty($gd_settings['seo_single_meta_title']) ? $gd_settings['seo_single_meta_title'] : GeoDir_Defaults::seo_single_meta_title();
			self::$meta_description = !empty($gd_settings['seo_single_meta_description']) ? $gd_settings['seo_single_meta_description'] : GeoDir_Defaults::seo_single_meta_description();
		}elseif(geodir_is_page( 'location' )){self::$gd_page = 'location';
			self::$title = !empty($gd_settings['seo_location_title']) ? $gd_settings['seo_location_title'] : GeoDir_Defaults::seo_location_title();
			self::$meta_title = !empty($gd_settings['seo_location_meta_title']) ? $gd_settings['seo_location_meta_title'] : GeoDir_Defaults::seo_location_meta_title();
			self::$meta_description = !empty($gd_settings['seo_location_meta_description']) ? $gd_settings['seo_location_meta_description'] : GeoDir_Defaults::seo_location_meta_description();
		}elseif(geodir_is_page( 'search' )){self::$gd_page = 'search';
			self::$title = !empty($gd_settings['seo_search_title']) ? $gd_settings['seo_search_title'] : GeoDir_Defaults::seo_search_title();
			self::$meta_title = !empty($gd_settings['seo_search_meta_title']) ? $gd_settings['seo_search_meta_title'] : GeoDir_Defaults::seo_search_meta_title();
			self::$meta_description = !empty($gd_settings['seo_search_meta_description']) ? $gd_settings['seo_search_meta_description'] : GeoDir_Defaults::seo_search_meta_description();
		}elseif(geodir_is_page( 'add-listing' )){self::$gd_page = 'add-listing';
			if(!empty($_REQUEST['pid'])){
				self::$title = !empty($gd_settings['seo_add_listing_title_edit']) ? $gd_settings['seo_add_listing_title_edit'] : GeoDir_Defaults::seo_add_listing_title_edit();
			}else{
				self::$title = !empty($gd_settings['seo_add_listing_title']) ? $gd_settings['seo_add_listing_title'] : GeoDir_Defaults::seo_add_listing_title();
			}
			self::$meta_title = !empty($gd_settings['seo_add_listing_meta_title']) ? $gd_settings['seo_add_listing_meta_title'] : GeoDir_Defaults::seo_add_listing_meta_title();
			self::$meta_description = !empty($gd_settings['seo_add_listing_meta_description']) ? $gd_settings['seo_add_listing_meta_description'] : GeoDir_Defaults::seo_add_listing_meta_description();
		}


		if(self::$title){self::$title = self::replace_variable(self::$title,self::$gd_page);}
		if(self::$meta_title){self::$meta_title = self::replace_variable(self::$meta_title,self::$gd_page);}
		if(self::$meta_description){self::$meta_description = self::replace_variable(self::$meta_description,self::$gd_page);}

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
	public static function replace_variable($string = '',$gd_page = ''){
		global $post,$gd_post;
		// global variables

		$post_type = geodir_get_current_posttype();

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
		if ( strpos( $string, '%%search_term%%' ) !== false ) {
			$search_term = '';
			if ( isset( $_REQUEST['s'] ) ) {
				$search_term = esc_attr( $_REQUEST['s'] );
				$search_term = str_replace(array("%E2%80%99","’"),array("%27","'"),$search_term);// apple suck
				$search_term = trim( stripslashes( $search_term ) );
			}
			$string = str_replace( "%%search_term%%", $search_term, $string );
		}

		if ( strpos( $string, '%%search_near%%' ) !== false ) {
			$search_near = '';
			if ( isset( $_REQUEST['snear'] ) ) {
				$search_near = esc_attr( $_REQUEST['snear'] );
			}
			if($search_near ){
				if($search_term){
					$search_near = ", ".sprintf( __('Near %s', 'geodirectory'), $search_near );
				}else{
					$search_near = sprintf( __('Near %s', 'geodirectory'), $search_near );
				}
			}

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
	public static function variables($gd_page = ''){

		$vars = array();
		// generic
		if($gd_page != 'location_tags'){
			$vars = array(
				'%%title%%' => __('The current post title.','geodirectory'),
				'%%sitename%%' => __('The site name from general settings: site title. ','geodirectory'),
				'%%sitedesc%%' => __('The site description from general settings: tagline.','geodirectory'),
				'%%excerpt%%' => __('The current post excerpt.','geodirectory'),
				'%%sep%%' => __('The separator mostly used in meta titles.','geodirectory'),
				'%%pt_single%%' => __('Post type singular name.','geodirectory'),
				'%%pt_plural%%' => __('Post type plural name.','geodirectory'),
				'%%category%%' => __('The current category name.','geodirectory'),
				'%%id%%' => __('The current post id.','geodirectory'),
			);
		}


		// location tags
		if(!$gd_page || $gd_page == 'location_tags' || $gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive' || $gd_page == 'single' || $gd_page == 'location'){
			$vars['%%location%%'] = __('The full current location eg: United States, Pennsylvania, Philadelphia','geodirectory');
			$vars['%%location_single%%'] = __('The current viewing location type single name eg: Philadelphia','geodirectory');
			$vars['%%in_location%%'] = __('The full current location prefixed with `in` eg: in United States, Pennsylvania, Philadelphia','geodirectory');
			$vars['%%in_location_single%%'] = __('The current viewing location type single name prefixed with `in` eg: Philadelphia','geodirectory');
			$vars['%%location_country%%'] = __('The current viewing country eg: United States','geodirectory');
			$vars['%%in_location_country%%'] = __('The current viewing country prefixed with `in` eg: in United States','geodirectory');
			$vars['%%location_region%%'] = __('The current viewing region eg: Pennsylvania','geodirectory');
			$vars['%%in_location_region%%']= __('The current viewing region prefixed with `in` eg: in Pennsylvania','geodirectory');
			$vars['%%location_city%%'] = __('The current viewing city eg: Philadelphia','geodirectory');
			$vars['%%in_location_city%%'] = __('The current viewing city prefixed with `in` eg: in Philadelphia','geodirectory');
		}



		// search page only
		if($gd_page == 'search' ){
			$vars['%%search_term%%'] = __('The currently used search for term.','geodirectory');
			$vars['%%search_near%%'] = __('The currently used search near term.','geodirectory');
		}

		// paging
		if($gd_page == 'search' || $gd_page == 'pt' || $gd_page == 'archive'){
			$vars['%%page%%'] = __('Current page number eg: page 2 of 4','geodirectory');
			$vars['%%pagetotal%%'] = __('Total pages eg: 101','geodirectory');
			$vars['%%pagenumber%%'] = __('Current page number eg: 99','geodirectory');
		}

		// single page
		if($gd_page == 'single' ){
			$vars['%%_FIELD-KEY%%'] = __('Show any custom field by using its field key prefixed with an _underscore','geodirectory');
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

		if ( ! empty( $wp_query->post ) && isset( $wp_query->post->ID ) && geodir_is_geodir_page_id( $page_id ) ) {
			$query_object_id = $wp_query->post->ID;
		}else{
			$query_object_id = get_queried_object_id();
		}

		if ( $query_object_id == $page_id && ( geodir_is_page( 'post_type' ) || geodir_is_page( 'archive' ) || geodir_is_page( 'author' ) ) ) {
			$link = '#';
		}
		return $link;
	}

}



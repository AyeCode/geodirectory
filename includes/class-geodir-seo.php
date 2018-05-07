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
 * @todo need a way to output the tag variables so users can easily select and insert them
 */
class GeoDir_SEO {

	// some global values
	public static $title = '';
	public static $meta_title = '';
	public static $meta_description = '';

	public static $sep = '&#8211;';
	public static $gd_page = '';
	public static $doing_menu = false;


	/**
	 * Initiate the class.
	 */
	public static function init() {
		// bail if we have a SEO plugin installed.
		if( class_exists( 'WPSEO_Frontend' ) || class_exists( 'All_in_One_SEO_Pack' )  || is_admin() ){
			return;
		}

		// set a global so we don't change the menu items titles
		add_filter('pre_wp_nav_menu',array(__CLASS__,'set_menu_global'));
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
		add_action('wp_head', array(__CLASS__,'output_description'));

	}

	/**
	 * Set the global var when a menu is being output.
	 *
	 * @param $menu
	 *
	 * @return mixed
	 */
	public static function set_menu_global($menu){
		self::$doing_menu = true;
		return $menu;
	}

	/**
	 * Unset the global var when a menu has finished being output.
	 *
	 * @param $menu
	 *
	 * @return mixed
	 */
	public static function unset_menu_global($menu){
		self::$doing_menu = false;
		return $menu;
	}


	/**
	 * Output a page title.
	 *
	 * @param string $title
	 * @param int $id
	 *
	 * @return string
	 */
	public static function output_title($title = '', $id = 0){
		if(self::$title && empty($id)  && !self::$doing_menu ){
			$title = self::$title;
		}elseif(self::$title && !empty($id) && get_queried_object_id() == $id && !in_the_loop()  && !self::$doing_menu ){
			$title = self::$title;
		}
		return $title;
	}

	/**
	 * Output a page meta title.
	 *
	 * @param string $title
	 * @param string $sep
	 *
	 * @return mixed|void
	 */
	public static function output_meta_title($title = '', $sep = ''){
		if($sep){self::$sep = $sep;}
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
	 * Output a page meta description.
	 */
	public static function output_description(){
		$description = self::$meta_description;
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
		echo apply_filters( 'geodir_seo_meta_description', '<meta name="description" content="' . $description . '" />', $description );
	}

	/**
	 * Set the title and meta info depending on the current page being visited.
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
			if(isset($queried_object->taxonomy) && $queried_object->taxonomy == 'gd_placecategory'){
				self::$title = !empty($gd_settings['seo_cat_archive_title']) ? $gd_settings['seo_cat_archive_title'] : GeoDir_Defaults::seo_cat_archive_title();
				self::$meta_title = !empty($gd_settings['seo_cat_archive_meta_title']) ? $gd_settings['seo_cat_archive_meta_title'] : GeoDir_Defaults::seo_cat_archive_meta_title();
				self::$meta_description = !empty($gd_settings['seo_cat_archive_meta_description']) ? $gd_settings['seo_cat_archive_meta_description'] : GeoDir_Defaults::seo_cat_archive_meta_description();
			}elseif(isset($queried_object->taxonomy) && $queried_object->taxonomy == 'gd_place_tags'){
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
	}

	/**
	 * Replace variables with values.
	 *
	 * @param string $string
	 * @param string $gd_page
	 *
	 * @return mixed|string
	 */
	public static function replace_variable($string = '',$gd_page = ''){
		global $post,$gd_post;
		// global variables

		$post_type = geodir_get_current_posttype();

		if ( strpos( $string, '%%sep%%' ) !== false ) {
			$string = str_replace( "%%sep%%", self::$sep, $string );
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
			$string = str_replace( "%%excerpt%%", strip_tags( wp_trim_excerpt($post->post_content) ), $string );
		}

		// archive
		if ( strpos( $string, '%%category%%' ) !== false ) {
			$cat_name = '';

			if ( $gd_page == 'single' ) {
				if ( $gd_post->default_category ) {
					$cat      = get_term( $post->default_category, $post->post_type . 'category' );
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
					$search_near = " ,".sprintf( __('Near %s', 'plugin-domain'), $search_near );
				}else{
					$search_near = sprintf( __('Near %s', 'plugin-domain'), $search_near );
				}
			}

			$string = str_replace( "%%search_near%%", $search_near, $string );
		}

		// page numbers
		if ( strpos( $string, '%%page%%' ) !== false ) {
			$page  = geodir_title_meta_page( self::$sep );
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

		return $string;
	}

	/**
	 * Returns an array of allowed variables and their descriptions.
	 * 
	 * @param string $gd_page
	 *
	 * @return array
	 */
	public static function variables($gd_page = ''){

		// generic
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
			'%%location%%' => __('The full current location eg: United States, Pennsylvania, Philadelphia','geodirectory'),
			'%%in_location%%' => __('The full current location prefixed with `in` eg: in United States, Pennsylvania, Philadelphia','geodirectory'),
			'%%in_location_single%%' => __('The current viewing location type single name eg: Philadelphia','geodirectory'),
			'%%location_country%%' => __('The current viewing country eg: United States','geodirectory'),
			'%%in_location_country%%' => __('The current viewing country prefixed with `in` eg: in United States','geodirectory'),
			'%%location_region%%' => __('The current viewing region eg: Pennsylvania','geodirectory'),
			'%%in_location_region%%' => __('The current viewing region prefixed with `in` eg: in Pennsylvania','geodirectory'),
			'%%location_city%%' => __('The current viewing city eg: Philadelphia','geodirectory'),
			'%%in_location_city%%' => __('The current viewing city prefixed with `in` eg: in Philadelphia','geodirectory'),
//			'' => __('','geodirectory'),
		);


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


		return $vars;
	}
	
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
	
}



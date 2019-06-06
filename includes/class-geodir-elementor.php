<?php
/**
 * GeoDirectory Elementor
 *
 * Adds compatibility for Elementor page builder.
 *
 * @author   AyeCode
 * @category Compatibility
 * @package  GeoDirectory
 * @since    2.0.0.41
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GeoDir_Elementor {

	/**
	 * Version.
	 *
	 * @var int
	 */
	const VERSION = 1;

	/**
	 * Setup class.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		// Add query vars
		//add_filter( 'query_vars', array( $this, 'add_query_vars' ), 0 );

		// enqueue flexslider JS
		

		// add any extra scripts
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ),11);
		add_filter('geodir_bypass_archive_item_template_content',array( $this, 'archive_item_template_content'),10,3);

	}

	/**
	 * Allow to filter the archive itme template content if being edited by elementor.
	 *
	 * @param $content
	 * @param $original_content
	 * @param $page_id
	 *
	 * @return mixed
	 */
	public function archive_item_template_content($content,$original_content,$page_id){

		if(!$original_content && $page_id && self::is_elementor($page_id)){
			$original_content = $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $page_id );
		} else {
			$original_content = $content;
		}

		return $original_content;
	}


	public function is_elementor_preview(){
		return isset($_REQUEST['elementor-preview']) ? true : false;
	}
	
	public function enqueue_scripts(){
		if($this->is_elementor_preview()){
			GeoDir_Frontend_Scripts::enqueue_script( 'jquery-flexslider' );
		}
	}

	/**
	 * Check if a page is being edited by elementor.
	 *
	 * @return bool
	 */
	public function is_elementor($post_id){
		return \Elementor\Plugin::$instance->db->is_built_with_elementor($post_id);
	}

	/**
	 * Check if a GD archive override is in place.
	 * 
	 * @param string $template
	 *
	 * @return bool
	 */
	public static function is_template_override($template=''){
		$result = false;
		$type = '';
		$page_type = '';

		// set post_type
		if(geodir_is_page('post_type') || geodir_is_page('archive')){
			$post_type = geodir_get_current_posttype();

			if(geodir_is_page('post_type')){
				$type = $post_type."_archive";
			}elseif($tax = get_query_var('taxonomy')){
				$type = $tax;
			}
			$page_type = 'archive';
		}elseif(geodir_is_page('single')){
			$type = geodir_get_current_posttype();
			$page_type = 'single';
		}elseif(geodir_is_page('search')){
			$type = 'search';
			$page_type = 'archive';
		}

		if($type && $conditions = get_option('elementor_pro_theme_builder_conditions')){
			if($page_type=='archive' && !empty($conditions['archive'])){
				foreach($conditions['archive'] as $archive_conditions){
					foreach ($archive_conditions as $archive_condition)
					if(stripos(strrev($archive_condition), strrev($type)) === 0){
						$result = true;break 2;
					}
				}
			}elseif($page_type=='single' && !empty($conditions['single'])){
				foreach($conditions['single'] as $archive_conditions){
					foreach ($archive_conditions as $archive_condition)
						if(stripos(strrev($archive_condition), strrev($type)) === 0){
							$result = true;break 2;
						}
				}
			}
		}

		return $result;
	}

	/**
	 * Check the current output is inside a elementor preview.
	 *
	 * @since 2.0.58
	 * @return bool
	 */
	public static function is_elementor_view() {
		$result = false;
		if ( isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor_ajax' ) ) {
			$result = true;
		}

		return $result;
	}

}
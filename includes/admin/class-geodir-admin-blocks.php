<?php
/**
 * GeoDirectory Admin
 *
 * @class    GeoDir_Admin
 * @author   AyeCode
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * GeoDir_Admin_Blocks class.
 *
 * Adds blocks for all GD shortcodes.
 */
class GeoDir_Admin_Blocks {

	/**
	 * Returns the instance.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		static $instance = null;

		if ( is_null( $instance ) ) {
			$instance = new self;
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Constructor method.
	 *
	 * @since  1.0.0
	 * @access private
	 * @return void
	 */
	private function __construct() {}

    /**
     * Includes files.
     *
     * @since 2.0.0
     * @access private
     */
	private function includes() {
		
	}

	/**
	 * Sets up main plugin actions and filters.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	private function setup_actions() {
		global $wp_version;

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue' ) );

		if( version_compare( $wp_version, "5.8-beta", "<" )){
			add_filter( 'block_categories', array( $this, 'block_category') , 8, 2 );
		}else{
			add_filter( 'block_categories_all', array( $this, 'block_category') , 8, 2 );
		}

		add_action( 'init', array( $this, 'register_block_patterns'), 20  );
	}

	public function register_block_patterns(){

		if(function_exists('register_block_pattern_category')){
			register_block_pattern_category(
				'geodirectory-parts',
				array( 'label' => __( 'GeoDirectory Parts', 'geodirectory' ) )
			);

//			register_block_pattern_category(
//				'geodirectory-archives',
//				array( 'label' => __( 'GeoDirectory Archives', 'geodirectory' ) )
//			);

			$this->search_bp();
//			$this->archives_bp();
			$this->category_bp();
		}

	}



	public function archives_bp(){

		$post_types = geodir_get_posttypes('options-plural');

		$post_type_option = count($post_types) > 1 ? "post_type=''" : '';
		$post_type_hide_option = count($post_types) > 1 ? " post_type_hide='false' " : '';
		/**
		 * Filter search block pattern attributes.
		 *
		 * @since 2.1.1.0
		 *
		 * @param string $attrs Attributes.
		 */
		$extra_attrs = apply_filters( 'geodir_register_block_pattern_search_attrs', '' );

		register_block_pattern(
			'geodirectory/search-1',
			array(
				'title'       => __( 'GD > Archive style 1', 'geodirectory' ),
				'description' => '',
				'categories'  => array( 'geodirectory-archives' ),
				'content'     =>
					'<!-- wp:group {"className":"bsui","layout":{"inherit":false}} --><div class="wp-block-group bsui">'.
					'<!-- wp:group {"className":"container","layout":{"inherit":false}} --><div class="wp-block-group container-fluid">'.
					'<!-- wp:group {"className":"row","layout":{"inherit":false}} --><div class="wp-block-group row">'.
					'<!-- wp:group {"className":"col","layout":{"inherit":false}} --><div class="wp-block-group col">'.
					GeoDir_Defaults::page_archive_content(false, true).
					'</div><!-- /wp:group -->'.
					'</div><!-- /wp:group -->'.
					'</div><!-- /wp:group -->'.
					'</div><!-- /wp:group -->',
			)
		);

//		register_block_pattern(
//			'geodirectory/search-2',
//			array(
//				'title'       => __( 'GD > Search Style 2', 'geodirectory' ),
//				'description' => '',
//				'categories'  => array( 'geodirectory-archives' ),
//				'content'     => "<!-- wp:geodirectory/geodir-widget-search {\"show_advanced\":true,\"pt\":\"3\",\"pr\":\"3\",\"pl\":\"3\",\"border\":\"gray\",\"rounded\":\"rounded\",\"shadow\":\"shadow-sm\",\"content\":\"\"} -->
//<div class=\"wp-block-geodirectory-geodir-widget-search\">[gd_search ".$post_type_option.$post_type_hide_option."bg=''  mt=''  mr=''  mb='3'  ml=''  pt='3'  pr='3'  pb=''  pl='3'  border='gray'  rounded='rounded'  rounded_size=''  shadow='shadow-sm'" . $extra_attrs . " ]</div>
//<!-- /wp:geodirectory/geodir-widget-search -->",
//			)
//		);
	}

	public function search_bp() {
		$post_types = geodir_get_posttypes( 'options-plural' );

		$post_type_attrs = count( $post_types ) > 1 ? " post_type=''  post_type_hide='false'" : "";

		/**
		 * Filter search block pattern attributes.
		 *
		 * @since 2.1.1.0
		 *
		 * @param string $attrs Attributes.
		 */
		$extra_attrs = apply_filters( 'geodir_register_block_pattern_search_attrs', '' );

		register_block_pattern(
			'geodirectory/search-1',
			array(
				'title'       => __( 'GD > Search Style 1', 'geodirectory' ),
				'description' => '',
				'categories'  => array( 'geodirectory-parts' ),
				'content'     => "<!-- wp:geodirectory/geodir-widget-search {\"bg\":\"light\",\"pt\":\"3\",\"pr\":\"5\",\"pl\":\"5\",\"border\":\"gray\",\"rounded\":\"rounded-pill\",\"content\":\"\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-search\">[gd_search" . $post_type_attrs . "  hide_search_input='false'  hide_near_input='false'  bg='light'  mt=''  mr=''  mb='3'  ml=''  pt='3'  pr='5'  pb=''  pl='5'  border='gray'  rounded='rounded-pill'  rounded_size=''  shadow=''  show=''" . $extra_attrs . " ]</div>
<!-- /wp:geodirectory/geodir-widget-search -->",
			)
		);

		register_block_pattern(
			'geodirectory/search-2',
			array(
				'title'       => __( 'GD > Search Style 2', 'geodirectory' ),
				'description' => '',
				'categories'  => array( 'geodirectory-parts' ),
				'content'     => "<!-- wp:geodirectory/geodir-widget-search {\"show_advanced\":true,\"pt\":\"3\",\"pr\":\"3\",\"pl\":\"3\",\"border\":\"gray\",\"rounded\":\"rounded\",\"shadow\":\"shadow-sm\",\"content\":\"\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-search\">[gd_search" . $post_type_attrs . "  hide_search_input='false'  hide_near_input='false'  bg=''  mt=''  mr=''  mb='3'  ml=''  pt='3'  pr='3'  pb=''  pl='3'  border='gray'  rounded='rounded'  rounded_size=''  shadow='shadow-sm'  show=''" . $extra_attrs . " ]</div>
<!-- /wp:geodirectory/geodir-widget-search -->",
			)
		);
	}

	public function category_bp(){
		register_block_pattern(
			'geodirectory/categories-1',
			array(
				'title'       => __( 'GD > Categories Style 1', 'geodirectory' ),
				'description' => '',
				'categories'  => array( 'geodirectory-parts' ),
				'content'     => "<!-- wp:geodirectory/geodir-widget-categories {\"hide_empty\":true,\"design_type\":\"icon-top\",\"icon_size\":\"box-medium\",\"content\":\"\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-categories\">[gd_categories title=''  post_type='0'  cpt_title='false'  title_tag='h4'  cpt_ajax='false'  filter_ids=''  hide_empty='true'  hide_count='false'  hide_icon='false'  use_image='false'  image_size='medium'  cpt_left='false'  sort_by='count'  max_level='1'  max_count='all'  max_count_child='all'  no_cpt_filter='false'  no_cat_filter='false'  widget_title_tag=''  widget_title_size_class=''  widget_title_align_class=''  widget_title_color_class=''  widget_title_border_class=''  widget_title_border_color_class=''  widget_title_mt_class=''  widget_title_mr_class=''  widget_title_mb_class=''  widget_title_ml_class=''  widget_title_pt_class=''  widget_title_pr_class=''  widget_title_pb_class=''  widget_title_pl_class=''  design_type='icon-top'  row_items='' row_positioning=''  card_padding_inside=''  card_color=''  card_shadow=''  icon_color=''  icon_size='box-medium'  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]</div>
<!-- /wp:geodirectory/geodir-widget-categories -->",
			)
		);

		register_block_pattern(
			'geodirectory/categories-2',
			array(
				'title'       => __( 'GD > Categories Style 2', 'geodirectory' ),
				'description' => '',
				'categories'  => array( 'geodirectory-parts' ),
				'content'     => "<!-- wp:geodirectory/geodir-widget-categories {\"hide_empty\":true,\"hide_count\":true,\"design_type\":\"icon-left\",\"card_padding_inside\":\"1\",\"card_color\":\"indigo\",\"content\":\"\"} -->
<div class=\"wp-block-geodirectory-geodir-widget-categories\">[gd_categories title=''  post_type='0'  cpt_title='false'  title_tag='h4'  cpt_ajax='false'  filter_ids=''  hide_empty='true'  hide_count='true'  hide_icon='false'  use_image='false'  image_size='medium'  cpt_left='false'  sort_by='count'  max_level='1'  max_count='all'  max_count_child='all'  no_cpt_filter='false'  no_cat_filter='false'  widget_title_tag=''  widget_title_size_class=''  widget_title_align_class=''  widget_title_color_class=''  widget_title_border_class=''  widget_title_border_color_class=''  widget_title_mt_class=''  widget_title_mr_class=''  widget_title_mb_class=''  widget_title_ml_class=''  widget_title_pt_class=''  widget_title_pr_class=''  widget_title_pb_class=''  widget_title_pl_class=''  design_type='icon-left'  row_items=''  row_positioning=''  card_padding_inside='1'  card_color='indigo'  card_shadow=''  icon_color=''  icon_size=''  bg=''  mt=''  mr=''  mb='3'  ml=''  pt=''  pr=''  pb=''  pl=''  border=''  rounded=''  rounded_size=''  shadow='' ]</div>
<!-- /wp:geodirectory/geodir-widget-categories -->",
			)
		);
	}

	/**
	 * Add custom block category.
	 *
	 * @param $categories
	 * @param $post
	 *
	 * @return array
	 */
	function block_category( $categories, $post ) {

		return array_merge(
			array(
				array(
					'slug' => 'geodirectory',
					'title' => __( 'GeoDirectory', 'geodirectory' ),
					'icon'  => 'wordpress', // gets changed via JS
				),
			),
			$categories
		);
	}

    /**
     * Enqueue scripts and styles.
     *
     * @since 2.0.0
     */
	public function enqueue() {
		global $pagenow,$wp_version;
		$design_style = geodir_design_style();
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		$deps = ( $pagenow == 'widgets.php' || ( $pagenow == 'site-editor.php' && ( function_exists( 'wp_is_block_theme' ) && wp_is_block_theme() ) ) ) && version_compare( $wp_version, '5.8', '>=' ) ? array( 'wp-blocks', 'wp-element') : array( 'wp-blocks', 'wp-element', 'wp-editor' );
		wp_enqueue_script(
			'gd-gutenberg',
			geodir_plugin_url() . '/assets/js/blocks'.$suffix.'.js',
			$deps,
			GEODIRECTORY_VERSION
		);

		if(!$design_style){
			wp_enqueue_style(
				'gd-gutenberg',
				geodir_plugin_url() . '/assets/css/block_editor.css',
				array( 'wp-edit-blocks' ),
				GEODIRECTORY_VERSION
			);
		}
		
	}
}
// init the class.
GeoDir_Admin_Blocks::get_instance();
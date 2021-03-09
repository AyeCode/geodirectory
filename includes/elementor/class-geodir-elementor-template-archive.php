<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Archive extends \ElementorPro\Modules\ThemeBuilder\Documents\Archive {

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'archive';
		$properties['condition_type'] = 'geodirectory_archive';

		return $properties;
	}

	public function get_name() {
		return 'geodirectory-archive';
	}

	public static function get_title() {
		return __( 'GD Archive', 'elementor-pro' );
	}

//	public function enqueue_scripts() {
//		// In preview mode it's not a real Woocommerce page - enqueue manually.
//		if ( Plugin::elementor()->preview->is_preview_mode( $this->get_main_id() ) ) {
//			wp_enqueue_script( 'woocommerce' );
//		}
//	}

//	public function get_container_attributes() {
//		$attributes = parent::get_container_attributes();
//
//		$attributes['class'] .= ' product';
//
//		return $attributes;
//	}

//	public function filter_body_classes( $body_classes ) {
//		$body_classes = parent::filter_body_classes( $body_classes );
//
//		if ( get_the_ID() === $this->get_main_id() || Plugin::elementor()->preview->is_preview_mode( $this->get_main_id() ) ) {
//			$body_classes[] = 'woocommerce';
//		}
//
//		return $body_classes;
//	}

	public static function get_preview_as_default() {
		return 'post_type_archive/gd_place';
	}

	public static function get_preview_as_options() {
		$archive_options = [];

		$post_types = geodir_get_posttypes('array');
		foreach($post_types as $key => $post_type){
			// Set root CPT
			$archive_options [ 'post_type_archive/' . $key ] = sprintf(__('%s Archive','geodirectory'),$post_type['labels']['name']);

			// Set taxonomies
			$archive_options [ 'taxonomy/' . $key. 'category' ] = sprintf(__('%s Categories Archive','geodirectory'),$post_type['labels']['singular_name']);
			$archive_options [ 'taxonomy/' . $key. '_tags' ] = sprintf(__('%s Tags Archive','geodirectory'),$post_type['labels']['singular_name']);

		}

		$archive_options['search'] = __( 'Search Results', 'geodirectory' );
		
		return [
			'archive' => [
				'label' => __( 'Archive', 'geodirectory' ),
				'options' => $archive_options,
			],
		];
	}

	public function __construct( array $data = [] ) {
		parent::__construct( $data );

//		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
	}

//	protected static function get_editor_panel_categories() {
//		$categories = [
//			'woocommerce-elements-archive' => [
//				'title' => __( 'Product Archive', 'elementor-pro' ),
//			],
//			// Move to top as active.
//			'woocommerce-elements' => [
//				'title' => __( 'WooCommerce', 'elementor-pro' ),
//				'active' => true,
//			],
//		];
//
//		$categories += parent::get_editor_panel_categories();
//
//		unset( $categories['theme-elements-archive'] );
//
//		return $categories;
//	}

//	public static function get_editor_panel_config() {
//		$config = parent::get_editor_panel_config();
//		$config['widgets_settings']['theme-archive-title']['categories'][] = 'woocommerce-elements-archive';
//
//		return $config;
//	}

	protected function _register_controls() {
		parent::_register_controls();

		$this->update_control(
			'preview_type',
			[
				'default' => 'post_type_archive/gd_place',
			]
		);
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['category'] = 'directory archive';

		return $config;
	}
}

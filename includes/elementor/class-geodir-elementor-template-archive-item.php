<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Archive_Item extends \ElementorPro\Modules\ThemeBuilder\Documents\Single {



	public function __construct( array $data = [] ) {
		parent::__construct( $data );

//		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
	}

	public function print_content() {
		if ( post_password_required() ) {
			echo get_the_password_form(); // WPCS: XSS ok.
			return;
		}

		parent::print_content();
	}

	protected function _register_controls() {
		parent::_register_controls();

		$this->update_control(
			'preview_type',
			[
//				'type' => \Elementor\Controls_Manager::HIDDEN,
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'single/gd_place',
			]
		);

		$latest_posts = get_posts( [
			'posts_per_page' => 1,
			'post_type' => 'gd_place',
		] );

		if ( ! empty( $latest_posts ) ) {
			$this->update_control(
				'preview_id',
				[
					'default' => $latest_posts[0]->ID,
				]
			);
		}
	}

	protected function get_remote_library_config() {
		$config = parent::get_remote_library_config();

		$config['category'] = 'directory archive item';

		return $config;
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'single';
		$properties['condition_type'] = 'geodirectory_archive_item';

		return $properties;
	}

	public function get_name() {
		return 'geodirectory-archive-item';
	}

	public static function get_title() {
		return __( 'GD Archive Item', 'elementor-pro' );
	}

//	public static function get_editor_panel_config() {
//		$config = parent::get_editor_panel_config();
//		$config['widgets_settings']['woocommerce-gd_place-content'] = [
//			'show_in_panel' => true,
//		];
//
//		return $config;
//	}

//	public function enqueue_scripts() {
//		// In preview mode it's not a real Product page - enqueue manually.
//		if ( \ElementorPro\Plugin::elementor()->preview->is_preview_mode( $this->get_main_id() ) ) {
//			global $gd_place;
//
//			if ( is_singular( 'gd_place' ) ) {
//				$gd_place = wc_get_gd_place();
//			}
//
//			if ( current_theme_supports( 'wc-gd_place-gallery-zoom' ) ) {
//				wp_enqueue_script( 'zoom' );
//			}
//			if ( current_theme_supports( 'wc-gd_place-gallery-slider' ) ) {
//				wp_enqueue_script( 'flexslider' );
//			}
//			if ( current_theme_supports( 'wc-gd_place-gallery-lightbox' ) ) {
//				wp_enqueue_script( 'photoswipe-ui-default' );
//				wp_enqueue_style( 'photoswipe-default-skin' );
//				add_action( 'wp_footer', 'woocommerce_photoswipe' );
//			}
//			wp_enqueue_script( 'wc-single-gd_place' );
//
//			wp_enqueue_style( 'photoswipe' );
//			wp_enqueue_style( 'photoswipe-default-skin' );
//			wp_enqueue_style( 'photoswipe-default-skin' );
//			wp_enqueue_style( 'woocommerce_prettyPhoto_css' );
//		}
//	}

//	public function get_depended_widget() {
//		return Plugin::elementor()->widgets_manager->get_widget_types( 'woocommerce-gd_place-data-tabs' );
//	}

//	public function get_container_attributes() {
//		$attributes = parent::get_container_attributes();
//
//		$attributes['class'] .= ' gd_place';
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

//	public function before_get_content() {
//		parent::before_get_content();
//
//		global $gd_place;
//		if ( ! is_object( $gd_place ) ) {
//			$gd_place = wc_get_gd_place( get_the_ID() );
//		}
//
//		do_action( 'woocommerce_before_single_gd_place' );
//	}

//	public function after_get_content() {
//		parent::after_get_content();
//
//		do_action( 'woocommerce_after_single_gd_place' );
//	}



//	protected static function get_editor_panel_categories() {// @todo  https://github.com/elementor/elementor/issues/10970
//		$categories = [
//			'woocommerce-elements-single' => [
//				'title' => __( 'Product', 'elementor-pro' ),
//
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
//		unset( $categories['theme-elements-single'] );
//
//		return $categories;
//	}


}

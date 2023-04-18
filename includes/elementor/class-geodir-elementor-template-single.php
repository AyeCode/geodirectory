<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Single extends \ElementorPro\Modules\ThemeBuilder\Documents\Single {

	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	public function print_content() {
		if ( post_password_required() ) {
			echo get_the_password_form(); // WPCS: XSS ok.
			return;
		}

		parent::print_content();
	}

	protected function register_controls() {
		parent::register_controls();

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

		$config['category'] = 'directory single';

		return $config;
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'single';
		$properties['condition_type'] = 'geodirectory';

		return $properties;
	}

	public static function get_type() {
		return 'geodirectory';
	}

	public function get_name() {
		return 'geodirectory';
	}

	public static function get_title() {
		return esc_html__( 'GD Single', 'geodirectory' );
	}

	public static function get_plural_title() {
		return esc_html__( 'GD Singles', 'geodirectory' );
	}

	public static function get_site_editor_thumbnail_url() {
		return ELEMENTOR_ASSETS_URL . 'images/app/site-editor/single-post.svg';
	}
}

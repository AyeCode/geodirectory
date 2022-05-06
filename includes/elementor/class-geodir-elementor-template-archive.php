<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class GeoDir_Elementor_Template_Archive extends \ElementorPro\Modules\ThemeBuilder\Documents\Archive {

	public function __construct( array $data = [] ) {
		parent::__construct( $data );
	}

	public static function get_properties() {
		$properties = parent::get_properties();

		$properties['location'] = 'archive';
		$properties['condition_type'] = 'geodirectory_archive';

		return $properties;
	}

	public static function get_type() {
		return 'geodirectory-archive';
	}

	public function get_name() {
		return 'geodirectory-archive';
	}

	public static function get_title() {
		return esc_html__( 'GD Archive', 'geodirectory' );
	}

	public static function get_plural_title() {
		return esc_html__( 'GD Archives', 'geodirectory' );
	}

	public static function get_site_editor_thumbnail_url() {
		return ELEMENTOR_ASSETS_URL . 'images/app/site-editor/archive.svg';
	}

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

	protected function register_controls() {
		parent::register_controls();

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

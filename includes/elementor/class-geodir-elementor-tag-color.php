<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.83
 * Class GeoDir_Elementor_Tag_Color
 */
Class GeoDir_Elementor_Tag_Color extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-color';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'Color Field', 'geodirectory' );
	}

	/**
	 * What group should this be added to.
	 *
	 * @return string
	 */
	public function get_group() {
		return 'geodirectory';
	}

	/**
	 * What categories should this be added to.
	 *
	 * @return array
	 */
	public function get_categories() {
		return [
			\Elementor\Modules\DynamicTags\Module::COLOR_CATEGORY
		];

	}

	/**
	 * Render the tag output.
	 */
	public function render() {

		global $gd_post;

		$value = '';
		$key = $this->get_settings( 'key' );
		if ( !empty( $key ) ) {

			$term_id = '';
			if(geodir_is_page('single') || $key == 'post_category_color' ){
				$term_id = isset($gd_post->default_category) ? absint($gd_post->default_category) : '';
			}elseif( geodir_is_page('archive') || $key == 'category_color' ){
				$current_category = get_queried_object();
				$term_id = isset($current_category->term_id) ?  absint($current_category->term_id) : '';
			}

			if($term_id){
				$cat_color = get_term_meta( $term_id, 'ct_cat_color', true );
				if($cat_color){
					$value = $cat_color;
				}
			}

			echo wp_kses_post( $value );
		}
	}

	/**
	 * Set the settings key key.
	 * @return string
	 */
	public function get_panel_template_setting_key() {
		return 'key';
	}

	/**
	 * Register controls for the tag.
	 */
	protected function register_controls() {

		$this->add_control(
			'key',
			[
				'label' => __( 'Key', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_custom_field_options(),
			]
		);
	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_options(){

		$keys = array();
		$keys[] = __('Select Key','geodirectory');
		$keys['post_category_color'] = 'post_category_color';
		$keys['category_color'] = 'category_color';


		return $keys;

	}

	/**
	 * Get what fields are supported for this tag type.
	 *
	 * @return array
	 */
	protected function get_supported_fields() {
		return [
			'text',
			'number',
			'select',
		];
	}
}
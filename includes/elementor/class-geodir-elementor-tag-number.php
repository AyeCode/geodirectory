<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.83
 * Class GeoDir_Elementor_Tag_Number
 */
Class GeoDir_Elementor_Tag_Number extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-number';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'Number Field', 'geodirectory' );
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
			\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY,
			\Elementor\Modules\DynamicTags\Module::POST_META_CATEGORY
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
			if(isset($gd_post->{$key})){
				$value = $gd_post->{$key};
			}
			
			// star rating needs one digit numbers
			if($value && $key=='overall_rating'){
				$value = round($value, 1);
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

		$fields = geodir_post_custom_fields('', 'all', 'all' ,'none');

//		print_r($fields );echo '###';

		$keys = array();
		$keys['overall_rating'] = 'overall_rating';
		$keys['rating_count'] = 'rating_count';
		$keys[] = __('Select Key','geodirectory');
		if(!empty($fields)){
			foreach($fields as $field){
				$keys[$field['htmlvar_name']] = $field['htmlvar_name'];
			}
		}
		
		// add some general types:
		$keys['post_date'] = 'post_date';
		$keys['post_modified'] = 'post_modified';
		$keys['post_author'] = 'post_author';

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
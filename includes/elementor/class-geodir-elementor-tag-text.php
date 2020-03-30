<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.81
 * Class GeoDir_Elementor_Tag_Text
 */
Class GeoDir_Elementor_Tag_Text extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-text';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'Field', 'geodirectory' );
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
			\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
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
		$show = $this->get_settings( 'show' );
		if ( !empty( $key ) ) {
			if(isset($gd_post->{$key})){
				
				if($show == 'value-raw'){
					$value = $gd_post->{$key};
				}else{
					$value = do_shortcode("[gd_post_meta key='$key' show='$show' no_wrap='1']");
				}
				
			}

//			echo  $value ;
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
	protected function _register_controls() {

		$this->add_control(
			'key',
			[
				'label' => __( 'Key', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SELECT,
//				'groups' => GeoDir_Elementor::get_control_options( $this->get_supported_fields() ),
				'options' => $this->get_custom_field_options(),
			]
		);

		$this->add_control(
			'show',
			[
				'label' => __( 'Show', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					"" => __('icon + label + value', 'geodirectory'),
					"icon-value" => __('icon + value', 'geodirectory'),
					"label-value" => __('label + value', 'geodirectory'),
					"label" => __('label', 'geodirectory'),
					"value" => __('value', 'geodirectory'),
					"value-strip" => __('value (strip_tags)', 'geodirectory'),
					"value-raw" => __('value (saved in database)', 'geodirectory'),
				),
				'default' => 'value-raw'
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

		$keys = array();
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
			'textarea',
			'number',
			'email',
			'password',
			'wysiwyg',
			'select',
			'checkbox',
			'radio',
			'true_false',

			// Pro
			'oembed',
			'google_map',
			'date_picker',
			'time_picker',
			'date_time_picker',
			'color_picker',
		];
	}
}
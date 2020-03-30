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
				
			}elseif($key=='latitude,longitude' && !empty($gd_post->latitude) && !empty($gd_post->longitude) ){
				$value = (float)$gd_post->latitude.",".$gd_post->longitude;
			}elseif($key=='address' && !empty($gd_post->city) ){
				$value = do_shortcode("[gd_post_meta key='$key' show='$show' no_wrap='1']");
			}elseif($key=='address_raw' && !empty($gd_post->city) ){
				$address_parts = array();
				if(!empty($gd_post->street)){$address_parts[] = esc_attr($gd_post->street);}
				if(!empty($gd_post->street2)){$address_parts[] = esc_attr($gd_post->street2);}
				if(!empty($gd_post->city)){$address_parts[] = esc_attr($gd_post->city);}
				if(!empty($gd_post->region)){$address_parts[] = esc_attr($gd_post->region);}
				if(!empty($gd_post->country)){$address_parts[] = esc_attr($gd_post->country);}
				if(!empty($gd_post->zip)){$address_parts[] = esc_attr($gd_post->zip);}

				if(!empty($address_parts)){
					$value = implode(", ",$address_parts);
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
	protected function _register_controls() {

		$this->add_control(
			'key',
			[
				'label' => __( 'Key', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'groups' => $this->get_custom_field_group_options(),
//				'options' => $this->get_custom_field_options(),
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
	 * Get and group the key options.
	 *
	 * @return array
	 */
	public function get_custom_field_group_options(){
		$groups = array();

		// Fields
		$fields = geodir_post_custom_fields('', 'all', 'all' ,'none');
		$keys = array();
//		$keys[] = __('Select Key','geodirectory');
		if(!empty($fields)){
			foreach($fields as $field){
				$keys[$field['htmlvar_name']] = $field['htmlvar_name'];
			}
		}

		$groups[] = array(
			'label' => __("Custom Fields","geodirectory"),
			'options'   => $keys
		);

		// Raw values
		$raw_keys = array();
		$raw_keys['_search_title'] = '_search_title';
		$raw_keys['overall_rating'] = 'overall_rating';
		$raw_keys['rating_count'] = 'rating_count';
		$raw_keys['post_date'] = 'post_date';
		$raw_keys['post_modified'] = 'post_modified';
		$raw_keys['post_author'] = 'post_author';
		$raw_keys['default_category'] = 'default_category';
		$raw_keys['address_raw'] = 'address_raw';
		$raw_keys['street'] = 'street';
		$raw_keys['street2'] = 'street2';
		$raw_keys['neighbourhood'] = 'neighbourhood';
		$raw_keys['city'] = 'city';
		$raw_keys['region'] = 'region';
		$raw_keys['country'] = 'country';
		$raw_keys['zip'] = 'zip';
		$raw_keys['latitude'] = 'latitude';
		$raw_keys['longitude'] = 'longitude';
		$raw_keys['latitude,longitude'] = 'latitude,longitude';
		$raw_keys['mapzoom'] = 'mapzoom';

		$groups[] = array(
			'label' => __("Raw Values","geodirectory"),
			'options'   => $raw_keys
		);


		return $groups;
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
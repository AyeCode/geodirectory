<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.81
 * Class GeoDir_Elementor_Tag_URL
 */
Class GeoDir_Elementor_Tag_URL extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-url';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'URL Field', 'geodirectory' );
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
			\Elementor\Modules\DynamicTags\Module::URL_CATEGORY,
		];

	}

	/**
	 * Render the tag output.
	 */
	public function render() {

		global $gd_post,$post;

		$value = '';
		$key = $this->get_settings( 'key' );
		$fallback = $this->get_settings( 'fallback_url' );
		if ( !empty( $key ) ) {
			if(isset($gd_post->{$key})){

				if($key == 'post_category'){
					$term_id = isset($gd_post->default_category) ? absint($gd_post->default_category) : '';
					$term_url = get_term_link( $term_id, $post->post_type."category" );
					$value = $term_url ? esc_url_raw($term_url) : '';
				}else{
					$cf = geodir_get_field_infoby('htmlvar_name', $key, $gd_post->post_type);
					$field_type = !empty($cf['field_type']) ? esc_attr($cf['field_type']) : '';
					$field_value = $gd_post->{$key};
					// maybe add special url types
					if($field_type == 'phone'){
						$value .= "tel:";
					}elseif($field_type == 'email'){
						$value .= "mailto:";
					}elseif($field_type == 'file'){
						$parts = explode("|",$field_value);
						if(!empty($parts[0])){
							$field_value = $parts[0];
						}
					}
					$value = esc_url_raw($value . $field_value);
				}


			}elseif($key == 'post_images'){
				$post_images = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key, 1 );
				if(!empty($post_images)){
					$image = $post_images[0];
					$img_src = geodir_get_image_src($image, '');
					$value = esc_url_raw($img_src);
				}
			}elseif($key == 'post_url'){
				$value = get_permalink( $gd_post->ID );
			}elseif($key == 'map_directions_url'){
				$lat = !empty($gd_post->latitude) ? esc_attr($gd_post->latitude) : '';
				$lon = !empty($gd_post->longitude) ? esc_attr($gd_post->longitude) : '';
				$url = "https://maps.google.com/?daddr=".esc_attr($lat).",".esc_attr($lon);
				$value = esc_url_raw($url);
			}

			// set fallback
			if(empty($value) && !empty($fallback)){
				$value = esc_url_raw($fallback);
			}

			echo  $value ;
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
			'fallback_url',
			[
				'label' => __( 'Fallback URL', 'elementor-pro' ),
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

		$supported_fields = $this->get_supported_fields();
		// remove unneeded types
		foreach($fields as $key => $field){
			if(!empty($field['type']) && !in_array($field['type'],$supported_fields)){
				unset($fields[$key]);
			}
		}


		$keys = array();
		$keys[] = __('Select Key','geodirectory');
		$keys['post_url'] = 'post_url';
		$keys['post_category'] = 'post_category';
		$keys['map_directions_url'] = 'map_directions_url';

		if(!empty($fields)){
			foreach($fields as $field){
				$keys[$field['htmlvar_name']] = $field['htmlvar_name'];
			}
		}



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
			'email',
			'images',
			'image',
			'file',
			'url',
			'phone',
		];
	}
}
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.81
 * Class GeoDir_Elementor_Tag_Image
 */
Class GeoDir_Elementor_Tag_Image extends Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-image';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'Image Field', 'geodirectory' );
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
			\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY,
		];

	}

	/**
	 * Render the tag output.
	 */
//	public function render() {
//
//		global $gd_post;
//echo '####';exit;
//		$value = '';
//		$key = $this->get_settings( 'key' );
//		$fallback = $this->get_settings( 'fallback_url' );
//		if ( !empty( $key ) ) {
//			if(isset($gd_post->{$key})){
//				$cf = geodir_get_field_infoby('htmlvar_name', $key, $gd_post->post_type);
//				$field_type = !empty($cf['field_type']) ? esc_attr($cf['field_type']) : '';
//
//				// maybe add special url types
//				if($field_type == 'phone'){
//					$value .= "tel:";
//				}elseif($field_type == 'email'){
//					$value .= "mailto:";
//				}
//
//				$value = esc_url_raw($value . $gd_post->{$key});
//			}else{
//				$value = esc_url_raw($fallback);
//			}
//
//			echo  $value ;
//		}
//	}

	public function get_value( array $options = [] ) {
		global $gd_post;
		
		$key = $this->get_settings( 'key' );

		$image_data = [
			'id' => null,
			'url' => '',
		];

		if ( ! empty( $key ) && !empty($gd_post->ID)) {

			$cf = geodir_get_field_infoby('htmlvar_name', $key, $gd_post->post_type);
			$field_type = !empty($cf['field_type']) ? esc_attr($cf['field_type']) : '';

			if($field_type == 'url'){
				$field_value = $gd_post->{$key};
				if(!empty($field_value)){
					$field_value = esc_url_raw($field_value);
					$image_data = [
						'id' => null,
						'gd-url'=> $field_value,
						'url' => "https://wordpress.com/mshots/v1/$field_value?w=1024&amp;h=1024",
					];
				}

			}else{
				$post_images = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key, 1 );

				if(!empty($post_images)){
					$image = $post_images[0];
					$size = '';
					$img_src = geodir_get_image_src($image, $size);
					$image_data = [
						'id' => null,
						'gd-id' => $image->ID,
						'gd-key' => $key,
						'url' => $img_src,
					];
				}
			}



		} 

		return $image_data;
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
			'fallback_image',
			[
				'label' => __( 'Fallback', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::MEDIA,
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

//		print_r($fields);exit;

		$supported_fields = $this->get_supported_fields();
		// remove unneeded types
		foreach($fields as $key => $field){
			if(!empty($field['type']) && !in_array($field['type'],$supported_fields)){
				unset($fields[$key]);
			}
		}


		$keys = array();
		$keys[] = __('Select Key','geodirectory');
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
			'image',
			'images',
			'file',
			'url',
		];
	}
}
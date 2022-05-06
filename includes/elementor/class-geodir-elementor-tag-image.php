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

		if(substr( $key, 0, 9 ) === "category_"){
			$image_data = $this->get_category_meta($key);
		}elseif ( ! empty( $key ) && !empty($gd_post->ID)) {
			$cf = geodir_get_field_infoby('htmlvar_name', $key, $gd_post->post_type);
			$field_type = !empty($cf['field_type']) ? esc_attr($cf['field_type']) : '';

			if ( $field_type == 'url' ) {
				$field_value = $gd_post->{$key};

				if ( ! empty( $field_value ) ) {
					$image_data = [
						'id' => null,
						'gd-url'=> $field_value,
						'url' => geodir_get_screenshot( $field_value, array( 'w' => 1024, 'h' => 1024, 'image' => true ) ),
					];
				}
			} else {
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


		// fallback image
		if ( empty( $image_data['url'] ) && $this->get_settings( 'fallback_image' ) ) {
			$value = $this->get_settings( 'fallback_image' );
		}
		if ( ! empty( $value ) && is_array( $value ) ) {
			$image_data['id'] = $value['id'];
			$image_data['url'] = $value['url'];
		}

		return $image_data;
	}

	/**
	 * Get the category meta.
	 *
	 * @param $key
	 * @param $show
	 *
	 * @return mixed|string|void
	 */
	public function get_category_meta($key){
		global $gd_post;
		$image_data = [
			'id' => null,
			'url' => '',
		];

		$term_id = '';
		if( geodir_is_page('archive') ){
			$current_category = get_queried_object();
			$term_id = isset($current_category->term_id) ?  absint($current_category->term_id) : '';
		}elseif(geodir_is_page('single')){
			$term_id = isset($gd_post->default_category) ? absint($gd_post->default_category) : '';
		}

		if($term_id) {

			if($key == 'category_map_icon'){
//				$value = esc_url_raw( geodir_get_term_icon( $term_id ) );

				$cat_img = get_term_meta( $term_id, 'ct_cat_icon', true );
				if(!empty($cat_img)){
					$value = esc_url_raw( geodir_get_cat_icon( $term_id, true ) );
					$image_data = [
						'id' => $cat_img['id'],
						'url' => $value,
					];
				}

			}elseif($key == 'category_image'){
				$cat_img = get_term_meta( $term_id, 'ct_cat_default_img', true );
				if(!empty($cat_img)){
					$value = esc_url_raw( geodir_get_cat_image( $term_id, true ) );
					$image_data = [
						'id' => $cat_img['id'],
						'url' => $value,
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
	protected function register_controls() {

		$this->add_control(
			'key',
			[
				'label' => __( 'Key', 'elementor-pro' ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'groups' => $this->get_custom_field_group_options(),
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
	 * Get and group the key options.
	 *
	 * @return array
	 */
	public function get_custom_field_group_options(){
		$groups = array();

		$fields = geodir_post_custom_fields('', 'all', 'all' ,'none');

		$supported_fields = $this->get_supported_fields();
		// remove unneeded types
		foreach($fields as $key => $field){
			if(!empty($field['type']) && !in_array($field['type'],$supported_fields)){
				unset($fields[$key]);
			}
		}
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

		// category keys
		$cat_keys = array();
		$cat_keys['category_map_icon'] = 'category_map_icon';
		$cat_keys['category_image'] = 'category_image';
		$groups[] = array(
			'label' => __("Category meta","geodirectory"),
			'options'   => $cat_keys
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
			'image',
			'images',
			'file',
			'url',
		];
	}
}
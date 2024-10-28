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

		if ( strpos( $key, 'location_image' ) === 0 ) {
			$image_data = $this->get_location_meta( $key );
		} else if ( substr( $key, 0, 9 ) === "category_" ) {
			$image_data = $this->get_category_meta( $key );
		} else if ( ! empty( $key ) && ! empty( $gd_post->ID ) ) {
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
				$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;
				if ( $post_id && wp_is_post_revision( $post_id ) ) {
					$post_id = wp_get_post_parent_id( $post_id );
				}
				$post_images = GeoDir_Media::get_attachments_by_type( $post_id, $key, 1 );

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
	public function get_category_meta( $key ) {
		global $gd_post;

		$image_data = [
			'id' => null,
			'url' => '',
		];

		$term_id = '';

		if ( geodir_is_page('archive' ) ) {
			$current_category = get_queried_object();
			$term_id = isset($current_category->term_id) ?  absint( $current_category->term_id ) : '';
		} else if ( ! empty( $gd_post ) ) {
			$term_id = ! empty( $gd_post->default_category ) ? absint( $gd_post->default_category ) : '';
		}

		if ( $term_id ) {
			if ( $key == 'category_map_icon' ) {
				$cat_img = get_term_meta( $term_id, 'ct_cat_icon', true );

				if ( ! empty( $cat_img ) ) {
					$value = esc_url_raw( geodir_get_cat_icon( $term_id, true ) );

					$image_data = [
						'id' => $cat_img['id'],
						'url' => $value,
					];
				}
			} else if ( $key == 'category_image' ) {
				$cat_img = get_term_meta( $term_id, 'ct_cat_default_img', true );

				if ( ! empty( $cat_img ) ) {
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
	 * Get locaion meta.
	 *
	 * @since 2.3.14
	 *
	 * @param string $key The setting key.
	 * @return array Setting value.
	 */
	public function get_location_meta( $key ) {
		$meta = array();

		if ( class_exists( 'GeoDir_Location_SEO' ) ) {
			if ( $key == 'location_image' ) {
				$location_seo = $this->get_location_seo();

				if ( ! empty( $location_seo->image ) ) {
					$image_src = wp_get_attachment_image_src( (int) $location_seo->image, 'full' );

					if ( ! empty( $image_src[0] ) ) {
						$meta = array(
							'id' => (int) $location_seo->image,
							'url' => esc_url_raw( $image_src[0] )
						);
					}
				}
			}
		}

		return $meta;
	}

	/**
	 * Get locaion SEO.
	 *
	 * @since 2.3.14
	 *
	 * @return array Location SEO.
	 */
	public function get_location_seo() {
		global $geodirectory, $geodir_ele_location_seo;

		$location = ! empty( $geodirectory ) && ! empty( $geodirectory->location ) ? $geodirectory->location : array();

		$location_seo = array();

		if ( ! empty( $location ) ) {
			if ( empty( $geodir_ele_location_seo ) ) {
				$geodir_ele_location_seo = array();
			}

			$location_key = maybe_serialize( $location );

			if ( ! isset( $geodir_ele_location_seo[ $location_key ] ) ) {
				$geodir_ele_location_seo[ $location_key ] = GeoDir_Location_SEO::get_location_seo();
			}

			$location_seo = $geodir_ele_location_seo[ $location_key ];
		}

		return $location_seo;
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

		if ( class_exists( 'GeoDir_Location_Manager' ) ) {
			// Location Meta
			$groups[] = array(
				'label' => __( 'Location Meta', 'geodirectory' ),
				'options' => array(
					'location_image' => 'location_image'
				)
			);
		}

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

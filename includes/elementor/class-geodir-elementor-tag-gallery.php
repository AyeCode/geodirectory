<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add text field options to the dynamic tags.
 *
 * @since 2.0.0.81
 * Class GeoDir_Elementor_Tag_Gallery
 */
Class GeoDir_Elementor_Tag_Gallery extends Elementor\Core\DynamicTags\Data_Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-gallery';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'Gallery Field', 'geodirectory' );
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
			\Elementor\Modules\DynamicTags\Module::GALLERY_CATEGORY
		];

	}


	/**
	 * Get the values for gallery.
	 *
	 * We only set one image here as we set them properly via filter.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function get_value( array $options = [ ] ) {
		global $gd_post;

		$key    = $this->get_settings( 'key' );
		$images = array();
		if ( ! empty( $key ) && ! empty( $gd_post->ID ) ) {

			$post_images = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $key, 1 );

			if ( ! empty( $post_images ) ) {
				$size = '';
				foreach ( $post_images as $image ) {

					$img_src  = geodir_get_image_src( $image, $size );
					$images[] = array(
						'id'    => null,
						'gd-id' => $image->ID,
						'url'   => $img_src,
					);
				}

			}
		}

		return $images;
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
				'label'   => __( 'Key', 'geodirectory' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => $this->get_custom_field_options(),
			]
		);

	}

	/**
	 * Gets an array of custom field keys.
	 *
	 * @return array
	 */
	public function get_custom_field_options() {

		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );

		$supported_fields = $this->get_supported_fields();
		// remove unneeded types
		foreach ( $fields as $key => $field ) {
			if ( ! empty( $field['type'] ) && ! in_array( $field['type'], $supported_fields ) ) {
				unset( $fields[ $key ] );
			}
		}

		$keys   = array();
		$keys[] = __( 'Select Key', 'geodirectory' );
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'];
			}
		}

		$keys['comment_images'] = 'comment_images';
		
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
		];
	}
}
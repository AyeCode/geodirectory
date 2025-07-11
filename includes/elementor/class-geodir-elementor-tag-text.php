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
		global $post, $gd_post;

		$value = '';
		$key = $this->get_settings( 'key' );
		$show = $this->get_settings( 'show' );
		$raw_value = '';

		if ( ! empty( $key ) ) {
			if ( isset( $gd_post->{$key} ) ) {
				$raw_value = $gd_post->{$key};

				$value = do_shortcode( "[gd_post_meta key='$key' show='$show' no_wrap='1']" );
			} else if ( $key == 'latitude,longitude' && ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
				$value = geodir_sanitize_float( $gd_post->latitude ) . "," . geodir_sanitize_float( $gd_post->longitude );
			} else if ( $key == 'address' && ! empty( $gd_post->city ) ) {
				$value = do_shortcode( "[gd_post_meta key='$key' show='$show' no_wrap='1']" );
			} else if ( $key == 'address_raw' && ! empty( $gd_post->city ) ) {
				$address_parts = array();

				if ( ! empty( $gd_post->street ) ) {
					$address_parts[] = esc_attr( $gd_post->street );
				}
				if ( ! empty( $gd_post->street2 ) ) {
					$address_parts[] = esc_attr( $gd_post->street2 );
				}
				if ( ! empty( $gd_post->city ) ) {
					$address_parts[] = esc_attr( $gd_post->city );
				}
				if ( ! empty( $gd_post->region ) ) {
					$address_parts[] = esc_attr( $gd_post->region );
				}
				if ( ! empty( $gd_post->country ) ) {
					$address_parts[] = esc_attr( $gd_post->country );
				}
				if ( ! empty( $gd_post->zip ) ) {
					$address_parts[] = esc_attr( $gd_post->zip );
				}

				if ( ! empty( $address_parts ) ) {
					$value = implode( ", ", $address_parts );
				}

				$value = geodir_post_address( $value, 'address', $gd_post );
			} elseif ( substr( $key, 0, 9 ) === "category_" ) {
				$value = $this->get_category_meta( $key, $show );
			}

			if ( ( ! empty( $raw_value ) && is_email( $raw_value ) ) || $key == 'email' ) {
				$skip_wp_kses  = true;
			} else {
				$skip_wp_kses = false;
			}

			/*
			 * Skip wp_kses for some values.
			 *
			 * @since 2.3.25
			 *
			 * @param bool   $skip_wp_kses To skip or not.
			 * @param mixed  $value Tag value.
			 * @param string $key Tag key.
			 * @param object $this Tag object.
			 */
			$skip_wp_kses = apply_filters( 'geodir_elementor_tag_text_skip_wp_kses', $skip_wp_kses, $value, $key, $this );

			if ( ! $skip_wp_kses ) {
				$value = wp_kses_post( $value );
			}

			/*
			 * Filter text render value.
			 *
			 * @since 2.1.0.6
			 *
			 * @param mixed  $value Tag value.
			 * @param string $key Tag key.
			 * @param object $this Tag object.
			 */
			$value = apply_filters( 'geodir_elementor_tag_text_render_value', $value, $key, $this );

			echo $value;
		}
	}

	/**
	 * Get the category meta.
	 *
	 * @param $key
	 * @param $show
	 *
	 * @return mixed|string|void
	 */
	public function get_category_meta( $key, $show ) {
		global $gd_post;

		$value = '';
		$term_id = '';

		if ( geodir_is_page( 'archive' ) ) {
			$current_category = get_queried_object();
			$term_id = isset( $current_category->term_id ) ?  absint( $current_category->term_id ) : '';
		} else if ( ! empty( $gd_post ) ) {
			$term_id = ! empty( $gd_post->default_category ) ? absint( $gd_post->default_category ) : '';
		}

		if ( $term_id ) {
			if ( $key == 'category_top_description' ) {
				$cat_desc = do_shortcode( "[gd_category_description]" );
				$value = $cat_desc ? trim( $cat_desc ) : '';
			} else if ( $key == 'category_bottom_description' ) {
				$cat_desc = do_shortcode( "[gd_category_description type='bottom']" );
				$value = $cat_desc ? trim( $cat_desc ) : '';
			} else if ( $key == 'category_icon' ) {
				$value = get_term_meta( $term_id, 'ct_cat_font_icon', true );

				if ( $show == 'value' ) {
					$value = "<i class='" . esc_attr( $value ) . "' aria-hidden='true'></i>";
				}
			} else if ( $key == 'category_map_icon' ) {
				$value = esc_url_raw( geodir_get_term_icon( $term_id ) );

				if ( $show == 'value' ) {
					$value = "<img src='" . esc_attr( $value ) . "' />";
				}
			} else if ( $key == 'category_color' ) {
				$value = get_term_meta( $term_id, 'ct_cat_color', true );
			} else if ( $key == 'category_schema' ) {
				$value = get_term_meta( $term_id, 'ct_cat_schema', true );
			} else if ( $key == 'category_image' ) {
				$value = esc_url_raw( geodir_get_cat_image( $term_id, true ) );

				if ( $show == 'value' ) {
					$value = "<img src='" . esc_attr( $value ) . "' />";
				}
			}
		}

		if ( $value && ( $show =='value-raw' || $show == 'value-strip' ) ) {
			$value = wp_strip_all_tags( $value );
		}

		return $value;
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
				'groups' => $this->get_custom_field_group_options()
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
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );

		$keys = array();

		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'];
			}
		}

		$groups[] = array(
			'label' => __( "Custom Fields", "geodirectory" ),
			'options' => $keys
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
			'label' => __( "Raw Values", "geodirectory" ),
			'options' => $raw_keys
		);

		// category keys
		$cat_keys = array();
		$cat_keys['category_top_description'] = 'category_top_description';
		$cat_keys['category_bottom_description'] = 'category_bottom_description';
		$cat_keys['category_icon'] = 'category_icon';
		$cat_keys['category_map_icon'] = 'category_map_icon';
		$cat_keys['category_color'] = 'category_color';
		$cat_keys['category_image'] = 'category_image';
		$cat_keys['category_schema'] = 'category_schema';
		$groups[] = array(
			'label' => __( "Category meta", "geodirectory" ),
			'options' => $cat_keys
		);

		/*
		 * Filter text custom fields.
		 *
		 * @since 2.2.12
		 *
		 * @param array  $groups Field groups.
		 * @param object $this Tag object.
		 */
		$groups = apply_filters( 'geodir_elementor_tag_text_fields', $groups, $this );

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
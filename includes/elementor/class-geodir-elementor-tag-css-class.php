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
Class GeoDir_Elementor_Tag_CSS_Class extends \Elementor\Core\DynamicTags\Tag {

	/**
	 * Get the type name.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'gd-text-css';
	}

	/**
	 * Get the title for the tag type.
	 *
	 * @return string
	 */
	public function get_title() {
		return 'GD ' . __( 'CSS Hide Condition', 'geodirectory' );
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
		global $gd_post, $post;

		$value = '';
		$key = $this->get_settings( 'key' );

		if ( $key == 'post_images' ) {
			$key = 'featured_image';
		}

		$condition = $this->get_settings( 'condition' );
		$match = $this->get_settings( 'match' );
		$show = 'value-raw';

		if ( ! empty( $key ) ) {
			if ( isset( $gd_post->{$key} ) ) {
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
			} elseif ( substr( $key, 0, 9 ) === "category_" ) {
				$value = $this->get_category_meta( $key, $show );
			} elseif ( ! in_array( $key, array_keys( (array) $gd_post ) ) ) {
				$key = ''; // Key doen't exists.
			}

			// conditions
			$match_found = false;

			if ( $key ) {
				$search = $match;
				$match_value = $value;

				if ( ! $match_found ) {
					if ( ( $key == 'post_date' || $key == 'post_modified' ) && ( empty( $condition ) || $condition == 'is_greater_than' || $condition == 'is_less_than' ) ) {
						if ( strpos( $search, '+' ) === false && strpos( $search, '-' ) === false ) {
							$search = '+' . $search;
						}
						$the_time = $key == 'post_modified' ? get_the_modified_date( 'Y-m-d', $gd_post ) : get_the_time( 'Y-m-d', $gd_post );
						$until_time = strtotime( $the_time . ' ' . $search . ' days' );
						$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );
						if ( ( empty( $condition ) || $condition == 'is_less_than' ) && $until_time > $now_time ) {
							$match_found = true;
						} elseif ( $condition == 'is_greater_than' && $until_time < $now_time ) {
							$match_found = true;
						}
					} else {
						switch ( $condition ) {
							case 'is_equal':
								$match_found = (bool) ( $search != '' && $match_value == $search );
								break;
							case 'is_not_equal':
								$match_found = (bool) ( $search != '' && $match_value != $search );
								break;
							case 'is_greater_than':
								$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value > $search );
								break;
							case 'is_less_than':
								$match_found = (bool) ( $search != '' && ( is_float( $search ) || is_numeric( $search ) ) && ( is_float( $match_value ) || is_numeric( $match_value ) ) && $match_value < $search );
								break;
							case 'is_empty':
								$match_found = (bool) ( $match_value === '' || $match_value === false || $match_value === '0' || is_null( $match_value ) );
								break;
							case 'is_not_empty':
								$match_found = (bool) ( $match_value !== '' && $match_value !== false && $match_value !== '0' && ! is_null( $match_value ) );
								break;
							case 'is_contains':
								$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) !== false );
								break;
							case 'is_not_contains':
								$match_found = (bool) ( $search != '' && stripos( $match_value, $search ) === false );
								break;
							case 'is_contains_any':
							case 'is_not_contains_any':
								if ( $match_value !== '' ) {
									$match_value = geodir_strtolower( stripslashes( $match_value ) );
									$search_value = geodir_strtolower( stripslashes( $search ) );
									$match_value = explode( ",", $match_value );
									$search_value = explode( ",", $search_value );

									foreach ( $search_value as $value ) {
										$value = trim( $value );

										if ( $value !== '' && in_array( $value, $match_value ) ) {
											$match_found = true;
											break;
										}
									}

									if ( $condition == 'is_not_contains_any' ) {
										$match_found = $match_found ? false : true;
									}
								}
								break;
						}
					}
				}
			}

			if ( $match_found ) {
				echo "elementor-hidden gd-dont-render";
			}
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
	public function get_category_meta( $key) {
		global $gd_post;

		$value   = '';
		$term_id = '';

		if ( geodir_is_page( 'archive' ) ) {
			$current_category = get_queried_object();
			$term_id          = isset( $current_category->term_id ) ? absint( $current_category->term_id ) : '';
		} else if ( ! empty( $gd_post ) ) {
			$term_id = ! empty( $gd_post->default_category ) ? absint( $gd_post->default_category ) : '';
		}


		if ( $term_id ) {
			if ( $key == 'category_top_description' ) {
				$cat_desc = do_shortcode( "[gd_category_description]" );
				$value    = $cat_desc ? trim( $cat_desc ) : '';
			} else if ( $key == 'category_bottom_description' ) {
				$cat_desc = do_shortcode( "[gd_category_description type='bottom']" );
				$value    = $cat_desc ? trim( $cat_desc ) : '';
			} else if ( $key == 'category_icon' ) {
				$value = get_term_meta( $term_id, 'ct_cat_font_icon', true );
			} else if ( $key == 'category_map_icon' ) {
				$value = esc_url_raw( geodir_get_term_icon( $term_id ) );
			} else if ( $key == 'category_color' ) {
				$value = get_term_meta( $term_id, 'ct_cat_color', true );
			} else if ( $key == 'category_schema' ) {
				$value = get_term_meta( $term_id, 'ct_cat_schema', true );
			} else if ( $key == 'category_image' ) {
				$value = esc_url_raw( geodir_get_cat_image( $term_id, true ) );
			}
		}

		if ( $value ) {
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
			'note',
			[
				'label' => __( 'Conditions note', 'geodirectory' ),
				'show_label'    => false,
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw' => __( 'If the conditions match the content will be hidden.', 'geodirectory' ),
				'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
			]
		);

		$this->add_control(
			'key',
			[
				'label'  => __( 'Key', 'geodirectory' ),
				'type'   => \Elementor\Controls_Manager::SELECT,
				'groups' => $this->get_custom_field_group_options(),
			]
		);

		$this->add_control(
			'condition',
			[
				'label'   => __( 'Condition', 'geodirectory' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'options' => array(
					'is_equal' => __( 'is equal', 'geodirectory' ),
					'is_not_equal' => __( 'is not equal', 'geodirectory' ),
					'is_greater_than' => __( 'is greater than', 'geodirectory' ),
					'is_less_than' => __( 'is less than', 'geodirectory' ),
					'is_empty' => __( 'is empty', 'geodirectory' ),
					'is_not_empty' => __( 'is not empty', 'geodirectory' ),
					'is_contains' => __( 'is contains', 'geodirectory' ),
					'is_not_contains' => __( 'is not contains', 'geodirectory' ),
					'is_contains_any' => __( 'is contains any', 'geodirectory' ),
					'is_not_contains_any' => __( 'is not contains any', 'geodirectory' )
				),
				'default' => 'is_empty'
			]
		);

		$this->add_control(
			'match',
			[
				'label' => __( 'Value to match', 'geodirectory' ),
				'type' => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '',
				'description' => __('Match this text with field value to hide. For post date enter value like +7 or -7.', 'geodirectory'),
				'condition' => [
					'condition!' => [ 'is_empty', 'is_not_empty' ]
				]
			]
		);
	}

	/**
	 * Get and group the key options.
	 *
	 * @return array
	 */
	public function get_custom_field_group_options() {
		$groups = array();

		// Fields
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );
		$keys   = array();
//		$keys[] = __('Select Key','geodirectory');
		if ( ! empty( $fields ) ) {
			foreach ( $fields as $field ) {
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'];
			}
		}

		$groups[] = array(
			'label'   => __( "Custom Fields", "geodirectory" ),
			'options' => $keys
		);

		// Raw values
		$raw_keys                       = array();
		$raw_keys['_search_title']      = '_search_title';
		$raw_keys['overall_rating']     = 'overall_rating';
		$raw_keys['rating_count']       = 'rating_count';
		$raw_keys['post_date']          = 'post_date';
		$raw_keys['post_modified']      = 'post_modified';
		$raw_keys['post_author']        = 'post_author';
		$raw_keys['default_category']   = 'default_category';
		$raw_keys['address_raw']        = 'address_raw';
		$raw_keys['street']             = 'street';
		$raw_keys['street2']            = 'street2';
		$raw_keys['neighbourhood']      = 'neighbourhood';
		$raw_keys['city']               = 'city';
		$raw_keys['region']             = 'region';
		$raw_keys['country']            = 'country';
		$raw_keys['zip']                = 'zip';
		$raw_keys['latitude']           = 'latitude';
		$raw_keys['longitude']          = 'longitude';
		$raw_keys['latitude,longitude'] = 'latitude,longitude';
		$raw_keys['mapzoom']            = 'mapzoom';

		$groups[] = array(
			'label'   => __( "Raw Values", "geodirectory" ),
			'options' => $raw_keys
		);

		// category keys
		$cat_keys                             = array();
		$cat_keys['category_top_description'] = 'category_top_description';
		$cat_keys['category_bottom_description'] = 'category_bottom_description';
		$cat_keys['category_icon']            = 'category_icon';
		$cat_keys['category_map_icon']        = 'category_map_icon';
		$cat_keys['category_color']           = 'category_color';
		$cat_keys['category_image']           = 'category_image';
		$cat_keys['category_schema']          = 'category_schema';
		$groups[]                             = array(
			'label'   => __( "Category meta", "geodirectory" ),
			'options' => $cat_keys
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

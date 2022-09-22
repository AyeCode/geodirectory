<?php

/**
 * GeoDir_Widget_Dynamic_Content class.
 *
 * @since 2.0.0.75
 */
class GeoDir_Widget_Dynamic_Content extends WP_Super_Duper {

	public $arguments;

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {

		$options = array(
			'textdomain'     => GEODIRECTORY_TEXTDOMAIN,
			'block-icon'     => 'location-alt',
			'block-category' => 'geodirectory',
			'block-keywords' => "['dynamic','geodir','geodirectory']",
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_dynamic_content',												// this us used as the widget id and the shortcode id.
			'name'           => __( 'GD > Dynamic Content', 'geodirectory' ),						// the name of the widget.
			'no_wrap'       => true,
			'widget_ops'     => array(
				'classname'     => 'geodir-dynamic-content '.geodir_bsui_class(),                                     	// widget class
				'description'   => esc_html__( 'Display dynamic content using post fields.', 'geodirectory' ),	// widget description
				'geodirectory'  => true,
			)
		);

		parent::__construct( $options );
	}

	/**
	 * Set widget arguments.
	 *
	 */
	public function set_arguments() {
		$arguments = array(
			'id'  	=> array(
				'type' => 'number',
				'title' => __('Post ID:', 'geodirectory'),
				'desc' => __('Leave blank to use current post id.', 'geodirectory'),
				'placeholder' => 'Leave blank to use current post id.',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'key'  => array(
				'type' => 'select',
				'title' => __('Field Key:', 'geodirectory'),
				'desc' => __('This is the custom field key.', 'geodirectory'),
				'placeholder' => '',
				'options' => $this->get_custom_field_keys(),
				'default'  => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'condition'  => array(
				'type' => 'select',
				'title' => __('Field condition:', 'geodirectory'),
				'desc' => __('Select the custom field condition.', 'geodirectory'),
				'placeholder' => '',
				'options' => $this->get_badge_conditions(),
				'default' => 'is_equal',
				'desc_tip' => true,
				'advanced' => false
			),
			'search'  => array(
				'type' => 'text',
				'title' => __('Value to match:', 'geodirectory'),
				'desc' => __('Match this text with field value to display post badge. For post date enter value like +7 or -7.', 'geodirectory'),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '[%condition%]!="is_empty" && [%condition%]!="is_not_empty"'
			),
			'html' => array(
				'title'       => __( 'Text:', 'geodirectory' ),
				'desc' => __('Use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory'),
				'type'        => 'textarea',
				'placeholder' => '', // __( '', 'geodirectory' ), @todo any reason to use empty?
				'default'     => '',
				'desc_tip'    => true,
				'advanced'    => false
			),
		);

		return $arguments;
	}

	/**
	 * Outputs the post badge on the front-end.
	 *
	 * @param array $args
	 * @param array $widget_args
	 * @param string $content
	 *
	 * @return mixed|string|void
	 */
	public function output( $args = array(), $widget_args = array(), $content = '' ) {
		global $post,$gd_post;

		$is_preview = $this->is_preview();
		$block_preview = $this->is_block_content_call();

		$post_id 	= ! empty( $args['id'] ) ? $args['id'] : ( ! empty( $post->ID ) ? $post->ID : 0 );
		$post_type 	= $post_id ? get_post_type( $post_id ) : '';

		if(empty($args['id']) && $is_preview ){
			$post_id  = geodir_get_post_id_with_content( $args['key'] );
			$post_type = 'gd_place';
		}

		$args['id'] = $post_id;

		// options
		$defaults = array(
			'id' => '',
			'key' => '',
			'condition' => '',
			'search' => '',
			'html' => '',
		);



		/**
		 * Parse incoming $args into an array and merge it with $defaults
		 */
		$args = wp_parse_args( $args, $defaults );

		// Errors.
		$errors = array();
		if ( empty( $args['id'] ) ) {
			$errors[] = __('post id is missing','geodirectory');
		}
		if ( empty( $post_type ) ) {
			$errors[] = __('invalid post type','geodirectory');
		}
		if ( empty( $args['key'] ) ) {
			$errors[] = __('field key is missing', 'geodirectory');
		}

		$output = '';
		if ( ! empty( $errors ) ) {
			$output .= implode( ", ", $errors );
		}

		$html = $args['html'];

		// Decode &lt; & &gt;
		if ( ! empty( $html ) ) {
			$trans   = array(
				'&lt;' => '<',
				'&gt;' => '>'
			);

			$html = strtr( $html, $trans );

			$html = geodir_unwptexturize( $html );
		}

		if ( $args['key'] == 'street' ) {
			$args['key'] = 'address';
		}

		$match_field = $_match_field = $args['key'];
		if ( $match_field == 'address' ) {
			$match_field = 'street';
		} elseif ( $match_field == 'post_images' ) {
			$match_field = 'featured_image';
		}

		$find_post = ! empty( $gd_post->ID ) && $gd_post->ID == $post_id ? $gd_post : geodir_get_post_info( $post_id );

//		print_r($find_post );echo '###'.$post_id;print_r($args);

		if ($match_field === '' || ( ! empty( $find_post ) && ( isset( $find_post->{$match_field} ) || isset( $find_post->{$_match_field} ) ) ) ) {
			$address_fields = array( 'street2', 'neighbourhood', 'city', 'region', 'country', 'zip', 'latitude', 'longitude' ); // Address fields
			$field = array();
			$search = $args['search'];

			if ( $match_field && ! in_array( $match_field, array( 'post_date', 'post_modified', 'default_category', 'post_id', 'post_status' ) ) && ! in_array( $match_field, $address_fields ) ) {
				$package_id = $is_preview ? 0 : geodir_get_post_package_id( $post_id, $post_type );
				$fields = geodir_post_custom_fields( $package_id, 'all', $post_type, 'none' );

				foreach ( $fields as $field_info ) {
					if ( $match_field == $field_info['htmlvar_name'] ) {
						$field = $field_info;
						break;
					} elseif( $_match_field == $field_info['htmlvar_name'] ) {
						$field = $field_info;
						break;
					}
				}

				if ( empty( $field ) ) {
					return $output; // Field not allowed.
				}
			}

			// Address fields.
			if ( in_array( $match_field, $address_fields ) && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
				if ( ! empty( $address_fields[ $match_field ] ) ) {
					$field = $address_fields[ $match_field ];
				}
			}

			$is_date = ( ! empty( $field['type'] ) && $field['type'] == 'datepicker' ) || in_array( $match_field, array( 'post_date', 'post_modified' ) ) ? true : false;
			/**
			 * @since 2.0.0.95
			 */
			$is_date = apply_filters( 'geodir_dynamic_content_is_date', $is_date, $match_field, $field, $args, $find_post );

			$match_value = isset( $find_post->{$match_field} ) ? esc_attr( trim( $find_post->{$match_field} ) ) : ''; // escape user input
			$match_found = $match_field === '' ? true : false;

			if ( ! $match_found ) {
				if ( ( $match_field == 'post_date' || $match_field == 'post_modified' ) && ( empty( $args['condition'] ) || $args['condition'] == 'is_greater_than' || $args['condition'] == 'is_less_than' ) ) {
					if ( strpos( $search, '+' ) === false && strpos( $search, '-' ) === false ) {
						$search = '+' . $search;
					}
					$the_time = $match_field == 'post_modified' ? get_the_modified_date( 'Y-m-d', $find_post ) : get_the_time( 'Y-m-d', $find_post );
					$until_time = strtotime( $the_time . ' ' . $search . ' days' );
					$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );
					if ( ( empty( $args['condition'] ) || $args['condition'] == 'is_less_than' ) && $until_time > $now_time ) {
						$match_found = true;
					} elseif ( $args['condition'] == 'is_greater_than' && $until_time < $now_time ) {
						$match_found = true;
					}
				} else {
					switch ( $args['condition'] ) {
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
					}
				}
			}

			/**
			 * @since 2.0.0.95
			 */
			$match_found = apply_filters( 'geodir_dynamic_content_check_match_found', $match_found, $args, $find_post );

			if ( $match_found ) {
				// check for price format
				if ( isset( $field['data_type'] ) && ( $field['data_type'] == 'INT' || $field['data_type'] == 'FLOAT' || $field['data_type'] == 'DECIMAL' ) && isset( $field['extra_fields'] ) && $field['extra_fields'] ) {
					$extra_fields = stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) );

					if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) && $extra_fields['is_price'] ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_currency_format_number( $match_value, $field );
						}
					} else if ( isset( $field['data_type'] ) && $field['data_type'] == 'INT' ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_cf_format_number( $match_value, $field );
						}
					} else if ( isset( $field['data_type'] ) && ( $field['data_type'] == 'FLOAT' || $field['data_type'] == 'DECIMAL' ) ) {
						if ( ceil( $match_value ) > 0 ) {
							$match_value = geodir_cf_format_decimal( $match_value, $field );
						}
					}
				}

				if ( $is_date && ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ) {
					$args['datetime'] = mysql2date( 'c', $match_value, false );
				}

				// Option value
				if ( ! empty( $field['option_values'] ) ) {
					$option_values = geodir_string_values_to_options( stripslashes_deep( $field['option_values'] ), true );

					if ( ! empty( $option_values ) ) {
						if ( ! empty( $field['field_type'] ) && $field['field_type'] == 'multiselect' ) {
							$values = explode( ',', trim( $match_value, ', ' ) );

							if ( is_array( $values ) ) {
								$values = array_map( 'trim', $values );
							}

							$_match_value = array();
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && in_array( $option_value['value'], $values ) ) {
									$_match_value[] = $option_value['label'];
								}
							}

							$match_value = ! empty( $_match_value ) ? implode( ', ', $_match_value ) : '';
						} else {
							foreach ( $option_values as $option_value ) {
								if ( isset( $option_value['value'] ) && $option_value['value'] == $match_value ) {
									$match_value = $option_value['label'];
								}
							}
						}
					}
				}

				/**
				 * @since 2.0.0.95
				 */
				$match_value = apply_filters( 'geodir_dynamic_content_match_value', $match_value, $match_field, $args, $find_post, $field );

				if ( empty( $html ) && empty( $args['icon_class'] ) ) {
					if ( isset( $field['frontend_title'] ) ) {
						$html = $field['frontend_title'];
					} else if ( $match_field == 'default_category' ) {
						$html = __( 'Default Category', 'geodirectory' ); // default_category don't have frontend_title.
					}
				}

				if ( ! empty( $html ) && $html = str_replace( "%%input%%", $match_value, $html ) ) {
					// will be replace in condition check
				}

				if( ! empty( $html ) && $post_id && $html = str_replace( "%%post_url%%", get_permalink( $post_id ),$html ) ) {
					// will be replace in condition check
				}

				if ( empty( $html ) ) {
					if ( empty( $html ) && $match_field == 'post_date' ) {
						$badge = __( 'NEW', 'geodirectory' );
					} elseif ( empty( $html ) && $match_field == 'post_modified' ) {
						$html = __( 'UPDATED', 'geodirectory' );
					}
				}

				if ( empty( $output ) && $is_preview && $args['html'] ) {
					$output = $args['html'];
				}

				// replace other post variables
				if ( ! empty( $html ) ) {
					$html = geodir_replace_variables( $html );

					if ( ! empty( $html ) ) {
						$output .= do_shortcode( $html );
					}
				}
			}
		}

		if ( empty( $output ) && $is_preview && $args['html'] ) {
			$output = $args['html'];
		}

		return $output;
	}

	/**
	 * Gets an array of custom field keys for post badge.
	 *
	 * @return array
	 */
	public function get_custom_field_keys(){
		$fields = geodir_post_custom_fields( '', 'all', 'all', 'none' );

		$keys = array();
		if ( ! empty( $fields ) ) {
			foreach( $fields as $field ) {
				if ( apply_filters( 'geodir_badge_field_skip_key', false, $field ) ) {
					continue;
				}
				$keys[ $field['htmlvar_name'] ] = $field['htmlvar_name'] . ' ( ' . __( $field['admin_title'], 'geodirectory' ) . ' )';

				if ( $field['htmlvar_name'] == 'post_category' ) {
					$keys['default_category'] = 'default_category ( ' . __( 'Default Category', 'geodirectory' ) . ' )';
				} else if ( $field['htmlvar_name'] == 'address' && ( $address_fields = geodir_post_meta_address_fields( '' ) ) ) {
					foreach ( $address_fields as $_field => $args ) {
						if ( $_field != 'map_directions' && $_field != 'street' ) {
							$keys[ $_field ] = $_field . ' ( ' . $args['frontend_title'] . ' )';
						}
					}
				}
			}
		}

		$keys['post_date'] = 'post_date ( ' . __( 'post date', 'geodirectory' ) . ' )';
		$keys['post_modified'] = 'post_modified ( ' . __( 'post modified', 'geodirectory' ) . ' )';
		$keys['post_id'] = 'post_id ( ' . __( 'post id', 'geodirectory' ) . ' )';
		$keys['post_status'] = 'post_status ( ' . __( 'Post Status', 'geodirectory' ) . ' )';

		return apply_filters( 'geodir_badge_field_keys', $keys );
	}

	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions(){
		$conditions = array(
			'is_equal' => __( 'is equal', 'geodirectory' ),
			'is_not_equal' => __( 'is not equal', 'geodirectory' ),
			'is_greater_than' => __( 'is greater than', 'geodirectory' ),
			'is_less_than' => __( 'is less than', 'geodirectory' ),
			'is_empty' => __( 'is empty', 'geodirectory' ),
			'is_not_empty' => __( 'is not empty', 'geodirectory' ),
			'is_contains' => __( 'is contains', 'geodirectory' ),
			'is_not_contains' => __( 'is not contains', 'geodirectory' ),
		);

		return apply_filters( 'geodir_badge_conditions', $conditions );
	}
}

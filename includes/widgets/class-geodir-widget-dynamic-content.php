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
			'block-supports' => array(
				'customClassName' => false,
			),
			'class_name'     => __CLASS__,
			'base_id'        => 'gd_dynamic_content',
			'name'           => __( 'GD > Dynamic Content', 'geodirectory' ),
			'no_wrap'       => true,
			'widget_ops'     => array(
				'classname'    => 'geodir-dynamic-content ' . geodir_bsui_class(),
				'description'  => esc_html__( 'Display dynamic content using post fields.', 'geodirectory' ),
				'geodirectory' => true
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
			'id' => array(
				'type' => 'number',
				'title' => __( 'Post ID:', 'geodirectory' ),
				'desc' => __( 'Leave blank to use current post id.', 'geodirectory' ),
				'placeholder' => 'Leave blank to use current post id.',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
			),
			'key' => array(
				'type' => 'select',
				'title' => __( 'Field Key:', 'geodirectory' ),
				'desc' => __( 'This is the custom field key.', 'geodirectory' ),
				'placeholder' => '',
				'options' => $this->get_custom_field_keys(),
				'default'  => 'post_title',
				'desc_tip' => true,
				'advanced' => false
			),
			'condition' => array(
				'type' => 'select',
				'title' => __( 'Field condition:', 'geodirectory' ),
				'desc' => __( 'Select the custom field condition.', 'geodirectory' ),
				'placeholder' => '',
				'options' => $this->get_badge_conditions(),
				'default' => 'is_not_equal',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '([%key%]!="logged_in" && [%key%]!="logged_out")'
			),
			'search' => array(
				'type' => 'text',
				'title' => __( 'Value to match:', 'geodirectory' ),
				'desc' => __( 'Match this text with field value to display post badge. For post date enter value like +7 or -7. Use current_user to match with current logged in user. For user_roles use comma separated user roles & with condition is_contains or is_not_contains.', 'geodirectory' ),
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false,
				'element_require' => '([%condition%]!="is_empty" && [%condition%]!="is_not_empty" && [%key%]!="logged_in" && [%key%]!="logged_out")'
			),
			'html' => array(
				'title' => __( 'Text:', 'geodirectory' ),
				'desc' => __( 'Use %%input%% to use the input value of the field or %%post_url%% for the post url, or the field key for any other info %%email%%.', 'geodirectory' ),
				'type' => 'textarea',
				'placeholder' => '',
				'default' => '',
				'desc_tip' => true,
				'advanced' => false
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

		$post_id = ! empty( $args['id'] ) ? $args['id'] : ( ! empty( $post->ID ) ? $post->ID : 0 );
		$post_type = $post_id ? get_post_type( $post_id ) : '';

		if ( empty( $args['id']) && $is_preview ) {
			$post_id  = geodir_get_post_id_with_content( $args['key'] );
			$post_type = 'gd_place';
		}

		$args['id'] = $post_id;

		// Options
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
			$errors[] = __( 'post id is missing','geodirectory' );
		}
		if ( empty( $post_type ) ) {
			$errors[] = __( 'invalid post type','geodirectory' );
		}
		if ( empty( $args['key'] ) ) {
			$errors[] = __( 'field key is missing', 'geodirectory' );
		}

		$output = '';
		if ( ! empty( $errors ) ) {
			$output .= implode( ", ", $errors );
		}

		$html = $args['html'];

		// Decode &lt; & &gt;
		if ( ! empty( $html ) ) {
			$trans = array(
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
		} else if ( $match_field == 'post_images' ) {
			$match_field = 'featured_image';
		}

		$find_post = ! empty( $gd_post->ID ) && $gd_post->ID == $post_id ? $gd_post : geodir_get_post_info( $post_id );
		$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();

		if ( ! empty( $find_post->ID ) && ! in_array( 'post_category', $find_post_keys ) ) {
			$find_post = geodir_get_post_info( (int) $find_post->ID );
			$find_post_keys = ! empty( $find_post ) ? array_keys( (array) $find_post ) : array();
		}

		$non_cf_keys = array_keys( $this->get_non_cf_keys() );

		if ( $match_field === '' || ( ! empty( $find_post_keys ) && ( in_array( $match_field, $find_post_keys ) || in_array( $_match_field, $find_post_keys ) || in_array( $match_field, $non_cf_keys ) ) ) ) {
			$address_fields = array( 'street2', 'neighbourhood', 'city', 'region', 'country', 'zip', 'latitude', 'longitude' ); // Address fields
			$field = array();
			$search = $args['search'];

			if ( $search == 'current_user' ) {
				if ( is_user_logged_in() && ( $current_user_id = get_current_user_id() ) ) {
					$search = $current_user_id;
				} else {
					$search = - 1; // If not logged in treat as 0.
				}
			}

			if ( $match_field && ! in_array( $match_field, $non_cf_keys ) && ! in_array( $match_field, $address_fields ) ) {
				$package_id = $is_preview ? 0 : geodir_get_post_package_id( $post_id, $post_type );
				$fields = geodir_post_custom_fields( $package_id, 'all', $post_type, 'none' );

				foreach ( $fields as $field_info ) {
					if ( $match_field == $field_info['htmlvar_name'] ) {
						$field = $field_info;
						break;
					} else if ( $_match_field == $field_info['htmlvar_name'] ) {
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

			$is_date = ( ! empty( $field['type'] ) && $field['type'] == 'datepicker' ) || in_array( $match_field, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ) ) ? true : false;
			/**
			 * @since 2.0.0.95
			 */
			$is_date = apply_filters( 'geodir_dynamic_content_is_date', $is_date, $match_field, $field, $args, $find_post );

			$match_value = in_array( $match_field, $find_post_keys ) ? esc_attr( trim( $find_post->{$match_field} ) ) : '';
			$match_found = $match_field === '' ? true : false;

			if ( in_array( $match_field, array( 'logged_in', 'logged_out', 'user_roles' ) ) ) {
				$match_found = false;

				switch ( $match_field ) {
					case 'logged_in':
						$match_found = (bool) is_user_logged_in();

						break;
					case 'logged_out':
						$match_found = ! is_user_logged_in();

						break;
					case 'user_roles':
						if ( ! empty( $search ) ) {
							$match_roles = is_scalar( $search ) ? explode( ",", $search ) : $search;

							if ( is_array( $match_roles ) ) {
								$match_roles = array_filter( array_map( 'trim', $match_roles ) );
							}

							if ( ! empty( $match_roles ) && is_array( $match_roles ) && is_user_logged_in() && ( $current_user = wp_get_current_user() ) ) {
								$user_roles = $current_user->roles;

								if ( $args['condition'] == 'is_not_contains' ) {
									$match_found = true;

									foreach ( $match_roles as $role ) {
										if ( in_array( $role, $user_roles ) ) {
											$match_found = false;
										}
									}
								} else {
									$match_roles = array_intersect( $match_roles, $user_roles );

									if ( ! empty( $match_roles ) ) {
										$match_found = true;
									}
								}
							}
						}

						break;
				}
			} else {
				if ( ! $match_found ) {
					$is_date_search = ! empty( $search[0]) && ( $search[0] === '+' || $search[0] === '-' );

					if ( ( in_array( $match_field, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ) ) || ( $is_date && $is_date_search ) ) && ( empty( $args['condition'] ) || $args['condition'] == 'is_greater_than' || $args['condition'] == 'is_less_than' ) ) {
						if ( strpos( $search, '+' ) === false && strpos( $search, '-' ) === false ) {
							$search = '+' . $search;
						}

						if ( $match_field == 'post_modified' ) {
							$the_time = get_the_modified_date( 'Y-m-d', $find_post );
						} else if ( $match_field == 'post_modified_gmt' ) {
							$the_time = get_post_modified_time( 'Y-m-d', true, $find_post, true );
						} else if ( $match_field == 'post_date' ) {
							$the_time = get_the_time( 'Y-m-d', $find_post );
						} else if ( $match_field == 'post_date_gmt' ) {
							$the_time = get_post_time( 'Y-m-d', true, $find_post, true );
						} else {
							$the_time =  $match_value ;
						}

						$until_time = strtotime( $the_time . ' ' . $search . ' days' );
						$now_time   = strtotime( date_i18n( 'Y-m-d', current_time( 'timestamp' ) ) );

						if ( ( empty( $args['condition'] ) || $args['condition'] == 'is_less_than' ) && $until_time > $now_time ) {
							$match_found = true;
						} else if ( $args['condition'] == 'is_greater_than' && $until_time < $now_time ) {
							$match_found = true;
						}
					} else {
						switch ( $args['condition'] ) {
							case 'is_equal':
								$match_found = (bool) ( ( $search != '' || $search === '' ) && $match_value == $search );
								break;
							case 'is_not_equal':
								$match_found = (bool) ( ( $search != '' || $search === '' ) && $match_value != $search );
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
			}

			/**
			 * @since 2.0.0.95
			 */
			$match_found = apply_filters( 'geodir_dynamic_content_check_match_found', $match_found, $args, $find_post );

			if ( $match_found ) {
				// Check for price format
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

				if ( ! ( ! empty( $html ) || $html === '0' ) && empty( $args['icon_class'] ) ) {
					if ( isset( $field['frontend_title'] ) ) {
						$html = $field['frontend_title'];
					} else if ( $match_field == 'default_category' ) {
						$html = __( 'Default Category', 'geodirectory' ); // Default_category don't have frontend_title.
					}
				}

				if ( ( ! empty( $html ) || $html === '0' ) && ( $html = str_replace( "%%input%%", $match_value, $html ) ) ) {
					// Will be replace in condition check
				}

				if ( ( ! empty( $html ) || $html === '0' ) && $post_id && ( $html = str_replace( "%%post_url%%", get_permalink( $post_id ),$html ) ) ) {
					// Will be replace in condition check
				}

				if ( ! ( ! empty( $html ) || $html === '0' ) ) {
					if ( empty( $html ) && $match_field == 'post_date' ) {
						$html = __( 'NEW', 'geodirectory' );
					} else if ( empty( $html ) && $match_field == 'post_modified' ) {
						$html = __( 'UPDATED', 'geodirectory' );
					}
				}

				if ( ! ( ! empty( $output ) || $output === '0' ) && $is_preview && ( $args['html'] || $args['html'] === '0' ) ) {
					$output = $args['html'];
				}

				// Replace other post variables
				if ( ! empty( $html ) || $html === '0' ) {
					$html = geodir_replace_variables( $html, $post_id );

					if ( ! empty( $html ) || $html === '0' ) {
						if ( $is_preview || $block_preview ) {
							$output = '';
						}

						$output = do_shortcode( $html );
					}
				}
			}
		}

		if ( ! ( ! empty( $output ) || $output === '0' ) && $is_preview && ( $args['html'] || $args['html'] === '0' ) ) {
			$output = $args['html'];
		}

		return $output;
	}

	/**
	 * Gets an array of custom field keys for post badge.
	 *
	 * @return array
	 */
	public function get_custom_field_keys() {
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

		$keys = array_merge( $keys, $this->get_non_cf_keys() );

		return apply_filters( 'geodir_badge_field_keys', $keys );
	}

	/**
	 * Non custom fields keys.
	 *
	 * @since 2.3.64
	 *
	 * @return array Non custom fields keys.
	 */
	public function get_non_cf_keys() {
		$keys = array();
		$keys['default_category'] = 'default_category ( ' . __( 'Default Category', 'geodirectory' ) . ' )';
		$keys['overall_rating'] = 'overall_rating ( ' . __( 'Overall Rating', 'geodirectory' ) . ' )';
		$keys['rating_count'] = 'rating_count ( ' . __( 'Rating Count', 'geodirectory' ) . ' )';
		$keys['post_id'] = 'post_id ( ' . __( 'post id', 'geodirectory' ) . ' )';
		$keys['post_type'] = 'post_type ( ' . __( 'Post Type', 'geodirectory' ) . ' )';
		$keys['post_author'] = 'post_author ( ' . __( 'Post Author', 'geodirectory' ) . ' )';
		$keys['post_status'] = 'post_status ( ' . __( 'Post Status', 'geodirectory' ) . ' )';
		$keys['post_date'] = 'post_date ( ' . __( 'post date', 'geodirectory' ) . ' )';
		$keys['post_date_gmt'] = 'post_date_gmt ( ' . __( 'post date gmt', 'geodirectory' ) . ' )';
		$keys['post_modified'] = 'post_modified ( ' . __( 'post modified', 'geodirectory' ) . ' )';
		$keys['post_modified_gmt'] = 'post_modified_gmt ( ' . __( 'post modified gmt', 'geodirectory' ) . ' )';
		$keys['logged_in'] = 'logged_in ( ' . __( 'Logged In', 'geodirectory' ) . ' )';
		$keys['logged_out'] = 'logged_out ( ' . __( 'Logged Out', 'geodirectory' ) . ' )';
		$keys['user_roles'] = 'user_roles ( ' . __( 'Specific User Roles', 'geodirectory' ) . ' )';

		return apply_filters( 'geodir_badge_non_cf_keys', $keys );
	}

	/**
	 * Gets an array of badge field conditions.
	 *
	 * @return array
	 */
	public function get_badge_conditions() {
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

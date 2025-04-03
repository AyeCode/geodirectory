<?php
/**
 * Custom fields output functions for the listing location.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * Filter the custom field output.
 *
 * @param string $html The html to be output.
 * @param string $location The location name of the output location.
 * @param object $cf The custom field object info.
 *
 * @since 1.6.9
 * @return string The html to output.
 */
function geodir_predefined_custom_field_output_twitter_feed($html,$location,$cf){
	global $gd_post;


	if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != '' ):

		$class = ($cf['htmlvar_name'] == 'geodir_timing') ? "geodir-i-time" : "geodir-i-text";

		$field_icon = geodir_field_icon_proccess($cf);
		if (strpos($field_icon, 'http') !== false) {
			$field_icon_af = '';
		} elseif ($field_icon == '') {
			$field_icon_af = ($cf['htmlvar_name'] == 'geodir_timing') ? '<i class="fas fa-clock" aria-hidden="true"></i>' : "";
		} else {
			$field_icon_af = $field_icon;
			$field_icon = '';
		}

		// Database value.
		if ( ! empty( $output ) && isset( $output['raw'] ) ) {
			return $gd_post->{$cf['htmlvar_name']};
		}

		$value = '<a class="twitter-timeline" data-height="600" data-dnt="true" href="https://x.com/'.$gd_post->{$cf['htmlvar_name']}.'">' . wp_sprintf( __( 'Tweets by %s', 'geodirectory' ), $gd_post->{$cf['htmlvar_name']} ) . '</a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';

		// Stripped value.
		if ( ! empty( $output ) && isset( $output['strip'] ) ) {
			return $value;
		}

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;">';
		$html .= $value;
		$html .= '</div>';

	endif;

	return $html;
}
add_filter('geodir_custom_field_output_text_key_twitter_feed','geodir_predefined_custom_field_output_twitter_feed',10,3);

/**
 * Filter distance to custom field output.
 *
 * @since 2.0.0.67
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_predefined_custom_field_output_distanceto( $html, $location, $cf, $output ) {
	global $gd_post;

	$htmlvar_name = $cf['htmlvar_name'];

	if ( ! empty( $gd_post->{$htmlvar_name} ) ) {
		$label = trim( $cf['frontend_title'] );
		$value = $gd_post->{$htmlvar_name};
		$_value = explode( ',', $value );
		$latitude = ! empty( $_value[0] ) ? trim( $_value[0] ) : '';
		$longitude = ! empty( $_value[1] ) ? trim( $_value[1] ) : '';
		$post_latitude = ! empty( $gd_post->latitude ) ? $gd_post->latitude : '';
		$post_longitude = ! empty( $gd_post->longitude ) ? trim( $gd_post->longitude ) : '';

		if ( empty( $latitude ) || empty( $longitude ) || empty( $post_latitude ) || empty( $post_longitude ) ) {
			return '<!-- -->';
		}

		$start_point = array( 'latitude' => $latitude, 'longitude' => $longitude );
		$end_point = array( 'latitude' => $post_latitude, 'longitude' => $post_longitude );

		$unit = geodir_get_option( 'search_distance_long', 'miles' );
		$distance = geodir_calculateDistanceFromLatLong( $start_point, $end_point, $unit );
		$_distance = geodir_show_distance( geodir_sanitize_float( $distance ) );

		$field_icon = geodir_field_icon_proccess( $cf );
		$output = geodir_field_output_process($output);
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '<i class="fas fa-road" aria-hidden="true"></i>';
		} else {
			$field_icon_af = $field_icon;
			$field_icon    = '';
		}

		if ( ! empty( $output ) && isset( $output['raw'] ) ) {
			// Database value.
			return $value;
		} elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
			// Stripped value.
			return $_distance;
		}

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-distanceto" style="' . $field_icon . '">' . $field_icon_af;
		if ( $output == '' || isset( $output['label'] ) ) $html .= $label ? '<span class="geodir_post_meta_title">' . __( $label, 'geodirectory' ) . ': ' . '</span>' : '';
		if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';
		if ( $output == '' || isset( $output['value'] ) ) {
			$google_map_link = 'https://www.google.com/maps/dir//' . $start_point['latitude'] . ',' . $start_point['longitude'] . '/' . $end_point['latitude'] . ',' . $end_point['longitude'] . '/';
			$google_map_link = apply_filters( 'geodir_custom_field_output_distanceto_on_google_map', $google_map_link, $start_point, $end_point );

			if ( $google_map_link ) {
				$html .= '<a href="' . esc_url( $google_map_link ) . '" target="_blank" title="' . esc_attr__( 'View on Google map', 'geodirectory' ) . '">';
			}
			$html .= $_distance;
			if ( $google_map_link ) {
				$html .= '</a>';
			}
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_text_key_distanceto', 'geodir_predefined_custom_field_output_distanceto', 10, 4 );

/**
 * Filter post badge match value.
 *
 * @since 2.0.0.75
 *
 * @param string $match_value Match value.
 * @param string $match_field Match field.
 * @param array $args The badge parameters.
 * @param array $find_post Post object.
 * @param array $field The custom field array.
 * @return string Filtered value.
 */
function geodir_post_badge_match_value( $match_value, $match_field, $args, $find_post, $field ) {
	if ( $match_field ) {
		// Post Dates
		if ( in_array( $match_field, array( 'post_date', 'post_modified', 'post_date_gmt', 'post_modified_gmt' ) ) ) {
			$date_format = geodir_date_time_format();
			$date_format = apply_filters( 'geodir_post_badge_date_time_format', $date_format, $match_field, $args, $find_post, $field );

			if ( $date_format ) {
				$match_value = ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ? date_i18n( $date_format, strtotime( $match_value ) ) : '';
			}
		}

		// Date Fields
		if ( ! empty( $field ) && ! empty( $field['type'] ) && $field['type'] == 'datepicker' ) {
			$date_format = geodir_date_format();

			if ( ! empty( $field['extra_fields'] ) ) {
				$_date_format = stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) );
				if ( ! empty( $_date_format['date_format'] ) ) {
					$date_format = $_date_format['date_format'];
				}
			}

			return ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ? date_i18n( $date_format, strtotime( $match_value ) ) : $match_value;
		}

		// Time Fields
		if ( ! empty( $field ) && ! empty( $field['type'] ) && $field['type'] == 'time' ) {
			$time_format = geodir_time_format();

			if ( ! empty( $field['extra_fields'] ) ) {
				$_time_format = stripslashes_deep( maybe_unserialize( $field['extra_fields'] ) );
				if ( ! empty( $_time_format['time_format'] ) ) {
					$time_format = $_time_format['time_format'];
				}
			}

			return $match_value != '' ? date_i18n( $time_format, strtotime( $match_value ) ) : $match_value;
		}

		// Featured image
		if ( ! empty( $match_value ) && $match_field == 'featured_image' && ! empty( $args['badge'] ) && strpos( $args['badge'], '%%input%%' ) !== false ) {
			$upload_dir = wp_upload_dir();
			$upload_baseurl = $upload_dir['baseurl'];

			$match_value = str_replace( array( '%%input%%', '&lt;', '&gt;' ), array( $upload_baseurl . $match_value, '<', '>' ), $args['badge'] );
		}

		// File
		if ( ! empty( $field['type'] ) && $field['type'] == 'file' && ! empty( $args['badge'] ) && strpos( $args['badge'], '%%input%%' ) !== false ) {
			$attachments = GeoDir_Media::get_attachments_by_type( $find_post->ID, $match_field );

			if ( ! empty( $attachments ) ) {
				$upload_dir = wp_upload_dir();
				$upload_baseurl = $upload_dir['baseurl'];

				$attachment_urls = array();

				foreach ( $attachments as $attachment ) {
					if ( ! empty( $attachment->file ) ) {
						$attachment_urls[] = str_replace( array( '%%input%%', '&lt;', '&gt;' ), array( $upload_baseurl . $attachment->file, '<', '>' ), $args['badge'] );
					}
				}

				if ( ! empty( $attachment_urls ) ) {
					$match_value = implode( " ", $attachment_urls );
				}
			}
		}

		// Default category
		if ( $match_field == 'default_category' && ! empty( $find_post->default_category ) ) {
			$term = get_term_by( 'id', absint( $find_post->default_category ), $find_post->post_type . 'category' );

			if ( ! empty( $term ) && ! is_wp_error( $term ) ) {
				$match_value = esc_attr( $term->name );
			}
		}
	}

	return $match_value;
}
add_filter( 'geodir_post_badge_match_value', 'geodir_post_badge_match_value', 10, 5 );

/**
 * Filter the badge link.
 *
 * @since 2.2.9
 *
 * @param string $link Badge link.
 * @param string $match_field Match field.
 * @param array $args The badge parameters.
 * @param array $find_post Post object.
 * @param array $field The custom field array.
 * @return string Filtered badge link.
 */
function geodir_post_badge_link( $link, $match_field, $args, $find_post, $field ) {
	if ( $match_field ) {
		// File
		if ( ! empty( $field['type'] ) && $field['type'] == 'file' && ! empty( $args['link'] ) && strpos( $args['link'], '%%input%%' ) !== false ) {
			$attachments = GeoDir_Media::get_attachments_by_type( $find_post->ID, $match_field, 1 );

			if ( ! empty( $attachments ) ) {
				$upload_dir = wp_upload_dir();
				$upload_baseurl = $upload_dir['baseurl'];

				if ( ! empty( $attachments[0]->file ) ) {
					$link = str_replace( array( '%%input%%' ), array( $upload_baseurl . $attachments[0]->file ), $args['link'] );
				}
			}
		}
	}

	return $link;
}
add_filter( 'geodir_post_badge_link', 'geodir_post_badge_link', 10, 5 );

function geodir_cf_custom( $html, $location, $cf, $p = '', $output = '', $the_post = array() ) {
	// Check we have the post value
	if ( is_numeric( $p ) ) {
		if ( ! empty( $p ) && ! empty( $the_post ) && ! empty( $the_post->post_id ) && (int) $the_post->post_id == (int) $p ) {
			$gd_post = $the_post;
		} else {
			$gd_post = geodir_get_post_info( $p );
		}
	} else {
		global $gd_post;
	}

	if ( empty( $gd_post ) ) {
		return $html;
	}

	if ( ! is_array( $cf ) && $cf != '' ) {
		$cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );
	}

	if ( empty( $cf['htmlvar_name'] ) ) {
		return $html;
	}

	$htmlvar_name = $cf['htmlvar_name'];

	// Check if there is a location specific filter.
	if ( has_filter( "geodir_custom_field_output_custom_loc_{$location}" ) ) {
		/**
		 * Filter the event field html by location.
		 *
		 * @since 2.0.0.0
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 */
		$html = apply_filters( "geodir_custom_field_output_custom_loc_{$location}", $html, $cf, $output, $gd_post );
	}

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_output_custom_var_{$htmlvar_name}" ) ) {
		/**
		 * Filter the event field html by individual custom field.
		 *
		 * @since 2.0.0.0
		 *
		 * @param string $html The html to filter.
		 * @param string $location The location to output the html.
		 * @param array $cf The custom field array.
		 */
		$html = apply_filters( "geodir_custom_field_output_custom_var_{$htmlvar_name}", $html, $location, $cf, $output, $gd_post );
	}

	if ( empty( $html ) ) {
		$value = isset( $gd_post->{$htmlvar_name} ) ? $gd_post->{$htmlvar_name} : '';

		if ( ! empty( $value ) ) {
			$value = stripslashes_deep( $value );

			// Private address
			$address_fields = geodir_post_meta_address_fields( $gd_post->post_type );

			if ( ! empty( $value ) && ! empty( $address_fields ) && isset( $address_fields[ $htmlvar_name ] ) ) {
				$value = geodir_post_address( $value, $htmlvar_name, $gd_post );
			}
		}

		if ( $value != '' ) {
			$class = "geodir-i-custom";
			$field_icon = geodir_field_icon_proccess( $cf );
			$output = geodir_field_output_process( $output );
			if ( strpos( $field_icon, 'http' ) !== false ) {
				$field_icon_af = '';
			} elseif ( $field_icon == '' ) {
				$field_icon_af = '';
			} else {
				$field_icon_af = $field_icon;
				$field_icon = '';
			}

			// Database value.
			if ( ! empty( $output ) && isset( $output['raw'] ) ) {
				return $value;
			}

			$value = apply_filters( 'geodir_custom_field_output_field_value', $value, $location, $cf, $gd_post );

			// round rating
			if ( $value && $htmlvar_name == 'overall_rating' ) {
				$value = round( $value, 1 );
			}

			// Translate country.
			if ( $htmlvar_name == 'country' ) {
				$value = __( $value, 'geodirectory' );
			}

			if ( isset( $cf['data_type'] ) && ( $cf['data_type'] == 'INT' || $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) && isset( $cf['extra_fields'] ) && $cf['extra_fields'] ) {
				$extra_fields = stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) );

				if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) && $extra_fields['is_price'] ) {
					if ( ! ceil( $value ) > 0 ) {
						return '';// dont output blank prices
					}
					$value = geodir_currency_format_number( $value, $cf );
				} else if ( isset( $cf['data_type'] ) && $cf['data_type'] == 'INT' ) {
					if ( ceil( $value ) > 0 ) {
						$value = geodir_cf_format_number( $value, $cf );
					}
				} else if ( isset( $cf['data_type'] ) && ( $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) ) {
					if ( ceil( $value ) > 0 ) {
						$value = geodir_cf_format_decimal( $value, $cf );
					}
				}
			}

			// Return stripped value.
			if ( ! empty( $output ) && isset( $output['strip'] ) ) {
				return $value;
			}

			$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

			if ( $output == '' || isset( $output['icon'] ) ) {
				$html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
			}
			if ( $output == '' || isset( $output['label'] ) ) {
				$html .= $cf['frontend_title'] != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
			}
			if ( $output == '' || isset( $output['icon'] ) ) {
				$html .= '</span>';
			}
			if ( $output == '' || isset( $output['value'] ) ) {
				$html .= $value;
			}

			$html .= '</div>';
		}
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom', 'geodir_cf_custom', 10, 6 );

/**
 * Filter map directions to custom field output.
 *
 * @since 2.0.0.86
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_custom_field_output_map_directions( $html, $location, $cf, $output, $_gd_post ) {
	if ( ! empty( $_gd_post ) ) {
		$gd_post = $_gd_post;
	} else {
		global $gd_post;
	}

	$htmlvar_name = $cf['htmlvar_name'];

	if ( ! empty( $gd_post->latitude ) && ! empty( $gd_post->longitude ) ) {
		$label = trim( $cf['frontend_title'] );
		$latitude = $gd_post->latitude;
		$longitude = $gd_post->longitude;

		$field_icon = geodir_field_icon_proccess( $cf );
		$output = geodir_field_output_process( $output );
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '<i class="fas fa-road" aria-hidden="true"></i>';
		} else {
			$field_icon_af = $field_icon;
			$field_icon    = '';
		}

		$map_directions_url = 'https://www.google.com/maps/dir//' . $latitude . ',' . $longitude . '/';
		$map_directions_url = apply_filters( 'geodir_custom_field_output_directions_on_map', $map_directions_url, $latitude, $longitude );

		$map_directions_url = geodir_post_address( $map_directions_url, 'map_directions', $gd_post );

		if ( empty( $map_directions_url ) ) {
			return '';
		}

		if ( ! empty( $output ) && ( isset( $output['raw'] ) || isset( $output['strip'] ) ) ) {
			// Stripped value.
			return $map_directions_url;
		}

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon geodir-i-custom" style="' . $field_icon . '">' . $field_icon_af;
		}
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}
		if ( $output == '' || isset( $output['value'] ) ) {
			if ( $map_directions_url ) {
				$html .= '<a href="' . esc_url( $map_directions_url ) . '" target="_blank" title="' . esc_attr__( 'View on Map', 'geodirectory' ) . '">';
			}

			if ( $output == '' || isset( $output['label'] ) ) {
				$html .= $label;
			} else {
				$html .= $map_directions_url;
			}

			if ( $map_directions_url ) {
				$html .= '</a>';
			}
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom_var_map_directions', 'geodir_custom_field_output_map_directions', 10, 5 );

/**
 * Filter post status post meta field output.
 *
 * @since 2.0.0.94
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_custom_field_output_post_status( $html, $location, $cf, $output, $_gd_post ) {
	if ( ! empty( $_gd_post ) ) {
		$gd_post = $_gd_post;
	} else {
		global $gd_post;
	}

	$htmlvar_name = $cf['htmlvar_name'];

	if ( isset( $gd_post->{$htmlvar_name} ) && $gd_post->{$htmlvar_name} != '' ) {
		$class = "geodir-i-custom";
		$field_icon = geodir_field_icon_proccess( $cf );
		$output = geodir_field_output_process( $output );
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '';
		} else {
			$field_icon_af = $field_icon;
			$field_icon = '';
		}

		$value = $gd_post->{$htmlvar_name};

		// Database value.
		if ( ! empty( $output ) && isset( $output['raw'] ) ) {
			return $value;
		}

		$value = geodir_get_post_status_name( $value );

		// Return stripped value.
		if ( ! empty( $output ) && isset( $output['strip'] ) ) {
			return $value;
		}

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
		}
		if ( $output == '' || isset( $output['label'] ) ) {
			$html .= $cf['frontend_title'] != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
		}
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $value;
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom_var_post_status', 'geodir_custom_field_output_post_status', 10, 5 );

/**
 * Filter default category post meta field output.
 *
 * @since 2.0.0.94
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_custom_field_output_default_category( $html, $location, $cf, $output, $_gd_post ) {
	if ( ! empty( $_gd_post ) ) {
		$gd_post = $_gd_post;
	} else {
		global $gd_post;
	}

	$htmlvar_name = $cf['htmlvar_name'];

	if ( isset( $gd_post->{$htmlvar_name} ) && ! empty( $gd_post->{$htmlvar_name} ) ) {
		$class = "geodir-i-custom";
		$field_icon = geodir_field_icon_proccess( $cf );
		$output = geodir_field_output_process( $output );

		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} else if ( $field_icon == '' ) {
			$field_icon_af = '';
		} else {
			$cat_font_icon = get_term_meta( (int) $gd_post->{$htmlvar_name}, 'ct_cat_font_icon', true );

			if ( $cat_font_icon ) {
				$field_icon = geodir_design_style() ? '<i class="' . esc_attr( $cat_font_icon ) . ' fa-fw" aria-hidden="true"></i> ' : '<i class="' . esc_attr( $cat_font_icon ) . '" aria-hidden="true"></i>';
			}

			$field_icon_af = $field_icon;
			$field_icon = '';
		}

		$value = $gd_post->{$htmlvar_name};

		// Database value.
		if ( ! empty( $output ) && isset( $output['raw'] ) ) {
			return $value;
		}

		$term = get_term_by( 'id', absint( $value ), $gd_post->post_type . 'category' );

		if ( ! ( ! empty( $term ) && is_object( $term ) && ! is_wp_error( $term ) ) ) {
			return NULL;
		}

		$value = $term->name;

		// Return stripped value.
		if ( ! empty( $output ) && isset( $output['strip'] ) ) {
			return $value;
		}

		$term_link = get_term_link( $term );

		if ( ! empty( $term_link ) && ! is_wp_error( $term_link ) ) {
			$value = '<a href="' . esc_url( $term_link ) . '">' . $value . '</a>';
		}

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
		}
		if ( $output == '' || isset( $output['label'] ) ) {
			$html .= $cf['frontend_title'] != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
		}
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $value;
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom_var_default_category', 'geodir_custom_field_output_default_category', 10, 5 );

/**
 * Output business hours for the day.
 *
 * @since 2.1.0.7
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param int $p The post id.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_custom_field_output_business_hours_day( $html, $location, $cf, $p = '', $output = '' ) {
	global $aui_bs5;

	if ( ! empty( $cf['name'] ) && ! empty( $cf['extra_fields'] ) && is_array( $cf['extra_fields'] ) && isset( $cf['extra_fields']['day'] ) && $cf['name'] == 'business_hours_' . $cf['extra_fields']['day'] ) {
		$htmlvar_name = 'business_hours';
		$day = $cf['extra_fields']['day'];
		$design_style = geodir_design_style();

		if ( $day == 'today' ) {
			$day_nos = geodir_day_short_names();
			$day = $day_nos[ date( 'N' ) ];
		} else {
			$day = ucfirst( substr( $day, 0, 2 ) );
		}

		if ( is_numeric( $p ) ) {
			$gd_post = geodir_get_post_info( $p );
		} else {
			global $gd_post;
		}

		$package_id = ! empty( $gd_post->package_id ) ? $gd_post->package_id : '';

		if ( ! ( ! empty( $gd_post ) && ! empty( $gd_post->{$htmlvar_name} ) && geodir_check_field_visibility( $package_id, $htmlvar_name, $gd_post->post_type ) ) ) {
			return $html;
		}

		$value = stripslashes_deep( $gd_post->{$htmlvar_name} );
		$business_hours = geodir_get_business_hours( $value, ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) );

		if ( ! ( ! empty( $business_hours['days'] ) && ! empty( $business_hours['days'][ $day ] ) ) ) {
			return $html;
		}
		$hours = $business_hours['days'][ $day ];

		$class = "geodir-i-custom";
		$output = geodir_field_output_process( $output );
		$field_icon = geodir_field_icon_proccess( $cf );
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '';
		} else {
			$field_icon_af = $field_icon;
			$field_icon = '';
		}

		$css_class = ' ' . $cf['css_class'];
		$has_open = false;
		if ( ! empty( $hours['open'] ) ) {
			$has_open = true;
		}
		if ( ! empty( $hours['closed'] ) ) {
			$css_class .= ' gd-bh-days-closed';
		}

		$slots_attr = 'data-bhs-day="' . (int) date( 'd' ) . '" data-bhs-id="' . (int) $gd_post->ID . '"';
		$slots_class = ' gd-bh-s' . sanitize_html_class( $cf['extra_fields']['day'] );
		if ( $design_style ) {
			$class .= ' d-inline-block align-top ' . ( $aui_bs5 ? 'me-1' : 'mr-1' );
			$slots_class .= ' d-inline-block';
			$css_class .= ' py-1';
		}

		$slots = '';
		foreach ( $hours['slots'] as $i => $slot ) {
			$slot_class = '';
			if ( ! empty( $slot['open'] ) ) {
				$slot_class .= ' gd-bh-open-now';

				if ( ! $has_open ) {
					$has_open = true;
				}
			}
			$slots .= '<div class="gd-bh-slot' . $slot_class . '"><div class="gd-bh-slot-r">' . $slot['range'] . '</div></div>';
		}

		$value = '<div class="gd-bh-slots' . $slots_class . '" ' . $slots_attr . '>';
		$value .= $slots;
		$value .= '</div>';
		if ( ! empty( $has_open ) ) {
			$css_class .= ' gd-bh-open-today';
		}

		$html = '<div class="geodir_post_meta gd-bh-day-hours' . $css_class . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon ' . $class . '" style="' . $field_icon . '">' . $field_icon_af;
		}
		if ( $output == '' || isset( $output['label'] ) ) {
			$html .= $cf['frontend_title'] != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
		}
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $value;
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom', 'geodir_custom_field_output_business_hours_day', 50, 5 );

/**
 * Filter post link post meta field output.
 *
 * @since 2.1.0.20
 *
 * @param string $html The html to filter.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array.
 * @param string $output The output string that tells us what to output.
 * @return string The html to output.
 */
function geodir_custom_field_output_post_link( $html, $location, $cf, $output, $_gd_post ) {
	if ( ! empty( $_gd_post ) ) {
		$gd_post = $_gd_post;
	} else {
		global $gd_post;
	}

	$htmlvar_name = $cf['htmlvar_name'];

	if ( ! empty( $gd_post ) ) {
		$class = "geodir-i-custom";
		$field_icon = geodir_field_icon_proccess( $cf );
		$output = geodir_field_output_process( $output );
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '';
		} else {
			$field_icon_af = $field_icon;
			$field_icon = '';
		}

		$value = get_permalink( $gd_post->ID );

		// Database value.
		if ( ! empty( $output ) && isset( $output['raw'] ) ) {
			return $value;
		}

		$value = geodir_get_post_status_name( $value );

		// Return stripped value.
		if ( ! empty( $output ) && isset( $output['strip'] ) ) {
			return $value;
		}

		$title = trim( esc_html( strip_tags( stripslashes( get_the_title( $gd_post->ID ) ) ) ) );

		$value = '<a href="' . $value . '" title="' . esc_attr( wp_sprintf( _x( 'View: %s', 'listing title hover', 'geodirectory' ), $title ) ) . '">' . $title . '</a>';

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
		}
		if ( $output == '' || isset( $output['label'] ) ) {
			$html .= $cf['frontend_title'] != '' ? '<span class="geodir_post_meta_title" >' . __( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
		}
		if ( $output == '' || isset( $output['icon'] ) ) {
			$html .= '</span>';
		}
		if ( $output == '' || isset( $output['value'] ) ) {
			$html .= $value;
		}

		$html .= '</div>';
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_custom_var_post_link', 'geodir_custom_field_output_post_link', 10, 5 );

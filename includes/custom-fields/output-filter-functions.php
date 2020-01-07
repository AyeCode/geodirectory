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


		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;">';

		$html .= '<a class="twitter-timeline" data-height="600" data-dnt="true" href="https://twitter.com/'.$gd_post->{$cf['htmlvar_name']}.'">' . wp_sprintf( __( 'Tweets by %s', 'geodirectory' ), $gd_post->{$cf['htmlvar_name']} ) . '</a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
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
		if ( round( $distance, 2 ) == 0 ) {
			$unit = geodir_get_option( 'search_distance_short', 'feet' );
			$distance = geodir_calculateDistanceFromLatLong( $start_point, $end_point, $unit );
			if ( $unit == 'feet' ) {
				$unit = __( 'feet', 'geodirectory' );
			} else {
				$unit = __( 'meters', 'geodirectory' );
			}
			$distance =  round( $distance );
		} else {
			if ( $unit == 'miles' ) {
				$unit = __( 'miles', 'geodirectory' );
			} else {
				$unit = __( 'km', 'geodirectory' );
			}
			$distance =  round( $distance, 2 );
		}
		$_distance =  $distance . ' ' . $unit;

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

		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $htmlvar_name . '">';

		if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-distanceto" style="' . $field_icon . '">' . $field_icon_af;
		if ( $output == '' || isset( $output['label'] ) ) $html .= $label ? '<span class="geodir_post_meta_title">' . __( $label, 'geodirectory' ) . ': ' . '</span>' : '';
		if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';
		if ( $output == '' || isset( $output['value'] ) ) {
			$google_map_link = 'https://www.google.com/maps/dir/' . $start_point['latitude'] . ',' . $start_point['longitude'] . '/' . $end_point['latitude'] . ',' . $end_point['longitude'] . '/';
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
	if ( $match_field && in_array( $match_field, array( 'post_date', 'post_modified', 'post_date_gmt', 'post_modified_gmt' ) ) ) {
		$date_format = geodir_date_time_format();
		$date_format = apply_filters( 'geodir_post_badge_date_time_format', $date_format, $match_field, $args, $find_post, $field );

		if ( $date_format ) {
			$match_value = ! empty( $match_value ) && strpos( $match_value, '0000-00-00' ) === false ? date_i18n( $date_format, strtotime( $match_value ) ) : '';
		}
	}

	return $match_value;
}
add_filter( 'geodir_post_badge_match_value', 'geodir_post_badge_match_value', 10, 5 );
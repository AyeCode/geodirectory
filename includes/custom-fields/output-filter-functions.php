<?php
/**
 * Custom fields output functions for the listing location.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

function geodir_custom_field_output_textarea_var_post_content($html,$location,$cf){

	if($location=='listing'){
		global $post;
		$html = '<div class="geodir_more_info gd-read-more-wrap ' . $cf['css_class'] . ' ' . $cf['htmlvar_name'] . '" ><p>';
		$html .= wp_strip_all_tags( $post->{$cf['htmlvar_name']}, true );
		$html .= '</p><p class="gd-read-more"><a href="#" class="gd-read-more-button">'.esc_attr__('Read More','geodirectory').'</a></p></div>';
	}

	return $html;
}

add_filter('geodir_custom_field_output_textarea_var_post_content','geodir_custom_field_output_textarea_var_post_content',15,3);

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
	global $post;


	if (isset($post->{$cf['htmlvar_name']}) && $post->{$cf['htmlvar_name']} != '' ):

		$class = ($cf['htmlvar_name'] == 'geodir_timing') ? "geodir-i-time" : "geodir-i-text";

		$field_icon = geodir_field_icon_proccess($cf);
		if (strpos($field_icon, 'http') !== false) {
			$field_icon_af = '';
		} elseif ($field_icon == '') {
			$field_icon_af = ($cf['htmlvar_name'] == 'geodir_timing') ? '<i class="fa fa-clock-o"></i>' : "";
		} else {
			$field_icon_af = $field_icon;
			$field_icon = '';
		}


		$html = '<div class="geodir_more_info ' . $cf['css_class'] . ' ' . $cf['htmlvar_name'] . '" style="clear:both;">';

		$html .= '<a class="twitter-timeline" data-height="600" data-dnt="true" href="https://twitter.com/'.$post->{$cf['htmlvar_name']}.'">Tweets by '.$post->{$cf['htmlvar_name']}.'</a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
		$html .= '</div>';

	endif;

	return $html;
}
add_filter('geodir_custom_field_output_text_key_twitter_feed','geodir_predefined_custom_field_output_twitter_feed',10,3);

/**
 * Filter the get_directions custom field output to show a link.
 *
 * @param string $html The html to be output.
 * @param string $location The location name of the output location.
 * @param object $cf The custom field object info.
 *
 * @since 1.6.9
 * @return string The html to output.
 */
function geodir_predefined_custom_field_output_get_directions($html,$location,$cf) {
	global $post;


	if ( isset( $post->{$cf['htmlvar_name']} ) && $post->{$cf['htmlvar_name']} != '' && isset( $post->post_latitude ) && $post->post_latitude ){

		$field_icon = geodir_field_icon_proccess( $cf );
		if ( strpos( $field_icon, 'http' ) !== false ) {
			$field_icon_af = '';
		} elseif ( $field_icon == '' ) {
			$field_icon_af = '<i class="fa fa-location-arrow"></i>';
		} else {
			$field_icon_af = $field_icon;
			$field_icon    = '';
		}

		$link_text = isset( $post->{$cf['default_value']} ) ? $post->{$cf['default_value']} : __( 'Get Directions', 'geodirectory' );

		$html = '<div class="geodir_more_info ' . $cf['css_class'] . ' ' . $cf['htmlvar_name'] . '" style="clear:both;">';

		if(isset( $cf['field_icon'] ) && $cf['field_icon']){
			$html .= $field_icon_af;
		}

		// We use maps.apple.com here because it will handle redirects nicely in most cases
		$html .= '<a href="https://maps.apple.com/?daddr=' . $post->post_latitude . ',' . $post->post_longitude . '" target="_blank" >' . $link_text . '</a>';
		$html .= '</div>';

	}else{
		$html ='';
	}

	return $html;
}
add_filter('geodir_custom_field_output_text_key_get_directions','geodir_predefined_custom_field_output_get_directions',10,3);
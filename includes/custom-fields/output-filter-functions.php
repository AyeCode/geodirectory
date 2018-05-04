<?php
/**
 * Custom fields output functions for the listing location.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

/**
 * function for post content textarea custom field output.
 *
 * @param string $html Custom field textarea html.
 * @param string $location Location values.
 * @param array $cf Custom fields values.
 * @return string $html
 */
function geodir_custom_field_output_textarea_var_post_content($html,$location,$cf){

	if($location=='listing'){
		global $post;
		$html = '<div class="geodir_post_meta gd-read-more-wrap ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" ><p>';
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


		$html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;">';

		$html .= '<a class="twitter-timeline" data-height="600" data-dnt="true" href="https://twitter.com/'.$post->{$cf['htmlvar_name']}.'">Tweets by '.$post->{$cf['htmlvar_name']}.'</a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>';
		$html .= '</div>';

	endif;

	return $html;
}
add_filter('geodir_custom_field_output_text_key_twitter_feed','geodir_predefined_custom_field_output_twitter_feed',10,3);
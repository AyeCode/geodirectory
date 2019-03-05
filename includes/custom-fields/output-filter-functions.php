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
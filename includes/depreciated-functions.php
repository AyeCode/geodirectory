<?php
/**
 * Created by PhpStorm.
 * User: stiofan
 * Date: 18/12/17
 * Time: 16:24
 */

/**
 * Loads custom CSS and JS on header.
 *
 * WP Admin -> Geodirectory -> Design -> Scripts -> Custom style css code.
 * WP Admin -> Geodirectory -> Design -> Scripts -> Header script code.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @deprecated @todo i think its best to remove this but we will need to keep it for a while.
 */
function geodir_header_scripts()
{
	echo '<style>' . stripslashes(geodir_get_option('geodir_coustem_css')) . '</style>';
	echo stripslashes(geodir_get_option('geodir_header_scripts'));
}
add_action('wp_head', 'geodir_header_scripts');
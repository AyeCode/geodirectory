<?php
/**
 * Template for the list of places
 *
 * This is used mostly on the listing (category) pages and outputs the actual grid or list of listings.
 * See the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $post The current post object.
 * @global object $wp_query WordPress Query object.
 * @global string $gridview_columns The girdview style of the listings.
 * @global object $gd_session GeoDirectory Session object.
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$template = get_option( 'template' );

switch ( $template ) {
    case 'twentyeleven' :
        echo '</div>';
        get_sidebar( 'geodirectory' );
        echo '</div>';
        break;
    case 'twentytwelve' :
        echo '</div></div>';
        break;
    case 'twentythirteen' :
        echo '</div></div>';
        break;
    case 'twentyfourteen' :
        echo '</div></div></div>';
        get_sidebar( 'content' );
        break;
    case 'twentyfifteen' :
        echo '</div></div>';
        break;
    case 'twentysixteen' :
        echo '</main></div>';
        break;
    default :
        echo '</div></div>';
        break;
}

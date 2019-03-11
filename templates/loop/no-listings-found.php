<?php
/**
 * Displayed when no listings are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$show_add_listing = GeoDir_User::show_add_listings('array');
?>
<p class="geodir-info">
    <?php

    if( isset( $show_add_listing[0]['url'] ) && !empty( $show_add_listing[0]['url'] )){
        printf( __( "No listings were found matching your selection, <a href='%s'>Add Listing</a>.", 'geodirectory' ), $show_add_listing[0]['url'] );
    } else{
	    _e( "No listings were found matching your selection.", 'geodirectory' );
    }
    ?>
</p>

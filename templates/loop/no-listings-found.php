<?php
/**
 * Displayed when no listings are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$show_add_listing = GeoDir_User::show_add_listings('gd_place');
?>
<p class="geodir-info">
    <?php

    if( isset( $show_add_listing['url'] ) && !empty( $show_add_listing['url'] )){
        printf( __( "No listings were found matching your selection. Something missing? Why not <a href='%s'>add a listing?</a>.", 'geodirectory' ), $show_add_listing['url'] );
    } else{
	    _e( "No listings were found matching your selection.", 'geodirectory' );
    }
    ?>
</p>

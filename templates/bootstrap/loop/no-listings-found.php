<?php
/**
 * Displayed when no listings are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $geodir_widget_cpt;
?>
<div class="geodir-info d-block mx-1">
    <?php
    $show_add_listing = GeoDir_User::show_add_listings('array');
    $current_pt = ! empty( $geodir_widget_cpt ) ? $geodir_widget_cpt : geodir_get_current_posttype();
    $add_listing_link = array( 'url' => '' );
    if ( isset( $show_add_listing[ $current_pt ] ) ) {
	    $add_listing_link = $show_add_listing[ $current_pt ];
    } else{
	    $add_listing_link = reset( $show_add_listing );
    }

    if ( isset( $add_listing_link['url'] ) && ! empty( $add_listing_link['url'] ) ) {
        $message = wp_sprintf( __( "No listings were found matching your selection. Something missing? Why not <a href='%s'>add a listing?</a>.", 'geodirectory' ), $add_listing_link['url'] );
    } else{
	    $message = __( "No listings were found matching your selection.", 'geodirectory' );
    }

    $message = apply_filters( 'geodir_no_listings_found_message', $message, $current_pt );

    echo aui()->alert(array(
		    'type'=> 'info',
		    'content'=> $message
	    )
    );
    ?>
</div>

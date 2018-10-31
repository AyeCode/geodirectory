<?php
/**
 * Displayed when no listings are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$listing_page_id = geodir_get_option( 'page_add' );

?>
<p class="geodir-info">
    <?php _e( 'No listings were found matching your selection.', 'geodirectory' ); ?>
    <?php if( !empty( $listing_page_id ) ){ ?>
        <a href="<?php echo get_the_permalink($listing_page_id); ?>"><?php echo get_the_title($listing_page_id);  ?></a>
    <?php } ?>
</p>

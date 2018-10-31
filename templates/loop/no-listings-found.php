<?php
/**
 * Displayed when no listings are found matching the current query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$listing_page_id = geodir_get_option( 'page_add' );

$get_search_text = !empty( $_REQUEST['s'] ) ? $_REQUEST['s'] :'';
$snear = !empty( $_REQUEST['snear'] ) ? $_REQUEST['snear'] :'';

$key = geodir_get_option( 'google_maps_api_key' );
$address = str_replace(' ','+',$snear);

$api_url ="https://maps.google.com/maps/api/geocode/json?key=$key&address=$address";
$address_response = wp_remote_get( $api_url );

$get_geo_lat ='';
$get_geo_long ='';

if( !empty( $address_response ) && 200 == $address_response['response']['code'] ) {

    $address_decode = !empty($address_response['body']) ? json_decode($address_response['body']) : '';

    if( !empty( $address_decode->results[0]->geometry ) && $address_decode->results[0]->geometry !='' ) {

        $get_geo_lat = !empty( $address_decode->results[0]->geometry->location->lat ) ? $address_decode->results[0]->geometry->location->lat :'';
        $get_geo_long = !empty( $address_decode->results[0]->geometry->location->lng ) ? $address_decode->results[0]->geometry->location->lng :'';

    }

}

$extra_para = '';
if( !empty( $get_search_text ) && $get_search_text !='' ) {

    $get_term = get_term_by('name',$get_search_text,'gd_placecategory');

    if( !empty( $get_term ) && $get_term !='' ) {
        $extra_para .='&cat='.$get_search_text;
    }

}

if( !empty( $get_geo_lat ) && !empty( $get_geo_long ) ) {
    $extra_para .='&location='.$snear.'&geo_lat='.$get_geo_lat.'&geo_long='.$get_geo_long;
}

?>
<p class="geodir-info">
    <?php _e( 'No listings were found matching your selection.', 'geodirectory' ); ?>
    <?php if( !empty( $listing_page_id ) ){ ?>
        <a href="<?php echo get_the_permalink($listing_page_id).'?listing_type=gd_place'.$extra_para; ?>"><?php echo get_the_title($listing_page_id);  ?></a>
    <?php } ?>
</p>

<?php
/**
 * Template for search bar used in the GD Search widget
 *
 * You can make most changes via hooks or see the link below for info on how to replace the template in your theme.
 *
 * @link http://docs.wpgeodirectory.com/customizing-geodirectory-templates/
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wp_query WordPress Query object.
 * @global object $wpdb WordPress Database object.
 */
global $wp_query, $current_term, $query;

$new_style = geodir_get_option('geodir_show_search_old_search_from') ? false : true;
$form_class = 'geodir-listing-search';

if ( $new_style ) {
    $form_class .= ' gd-search-bar-style';
}

/**
 * Filters the GD search form class.
 *
 * @since 1.0.0
 * @param string $form_class The class for the search form, default: 'geodir-listing-search'.
 */
$form_class = apply_filters('geodir_search_form_class', $form_class);
?>
<form class="<?php echo $form_class; ?>" name="geodir-listing-search" action="<?php echo geodir_search_page_base_url(); ?>" method="get">
    <input type="hidden" name="geodir_search" value="1" />
    <div class="geodir-loc-bar">
        <?php
        /**
         * Called inside the search form but before any of the fields.
         *
         * @since 1.0.0
         */
        do_action('geodir_before_search_form') ?>

        <div class="clearfix geodir-loc-bar-in">
            <div class="geodir-search">

                <?php

                /**
                 * Adds the input fields to the search form.
                 *
                 * @since 1.6.9
                 */
                do_action('geodir_search_form_inputs');



                /**
                 * Called on the GD search form just before the search button.
                 *
                 * @since 1.0.0
                 */
                do_action('geodir_before_search_button');

                
                /**
                 * Called on the GD search form just after the search button.
                 *
                 * @since 1.0.0
                 */
                do_action('geodir_after_search_button');

                
                ?>
            </div>


        </div>

        <?php
        /**
         * Called inside the search form but after all the input fields.
         *
         * @since 1.0.0
         */
        do_action('geodir_after_search_form') ?>


    </div>
    <?php
    global $geodirectory;
    $latlon = $geodirectory->location->get_latlon();
    $slat = !empty($latlon['lat']) ? $latlon['lat'] : ''; // already escaped
    $slon = !empty($latlon['lon']) ? $latlon['lon'] : ''; // already escaped
    ?>
    <input name="sgeo_lat" class="sgeo_lat" type="hidden" value="<?php echo sanitize_text_field($slat);?>"/>
    <input name="sgeo_lon" class="sgeo_lon" type="hidden" value="<?php echo sanitize_text_field($slon);?>"/>
    <?php do_action('geodir_search_hidden_fields');?>
</form>

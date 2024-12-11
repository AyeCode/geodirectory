<?php
/**
 * Custom fields output functions
 *
 * @since 1.6.6
 * @package GeoDirectory
 */

/**
 * Get the html output for the custom field: checkbox
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_checkbox($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '1';
    }

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_checkbox_loc_{$location}")){
        /**
         * Filter the checkbox html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_checkbox_var_{$html_var}")){
        /**
         * Filter the checkbox html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_checkbox_key_{$cf['field_type_key']}")){
        /**
         * Filter the checkbox html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ( (int) $gd_post->{$html_var} == 1 ):

            if ( $gd_post->{$html_var} == '1' ):
                $html_val = __( 'Yes', 'geodirectory' );
            else:
                $html_val = __( 'No', 'geodirectory' );
            endif;

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $gd_post->{$html_var};
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $html_val;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-checkbox" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $html_val );

            $html .= '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_checkbox','geodir_cf_checkbox',10,5);

/**
 * Get the html output for the custom field: fieldset
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_fieldset( $html, $location, $cf, $p = '', $output = '' ) {

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_fieldset_loc_{$location}")){
        /**
         * Filter the fieldset html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_fieldset_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_fieldset_var_{$html_var}")){
        /**
         * Filter the fieldset html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_fieldset_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_fieldset_key_{$cf['field_type_key']}")){
        /**
         * Filter the fieldset html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_fieldset_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        global $field_set_start;
        $output = geodir_field_output_process( $output );
        $fieldset_class = 'fieldset-'.sanitize_html_class(sanitize_title_with_dashes($cf['frontend_title']));

        if ($field_set_start == 1) {
            $html = '';
        } else {
            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return stripslashes( $cf['frontend_title'] );
            }

            $html = '<h2 class="'.$fieldset_class.'">'. stripslashes( __($cf['frontend_title'], 'geodirectory') ) . '</h2>';
            //$field_set_start = 1;
        }

    }

    return $html;
}
add_filter( 'geodir_custom_field_output_fieldset','geodir_cf_fieldset', 10, 5 );


/**
 * Get the html output for the custom field: url
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_url($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'https://example.com';
    }

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_url_loc_{$location}")){
        /**
         * Filter the url html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_url_var_{$html_var}")){
        /**
         * Filter the url html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_url_key_{$cf['field_type_key']}")){
        /**
         * Filter the url html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {

                if ($cf['name'] == 'facebook') {
                    $field_icon_af = $design_style ?  '<i class="fab fa-facebook-square fa-fw" aria-hidden="true"></i> ' : '<i class="fab fa-facebook-square" aria-hidden="true"></i>';
                } elseif ($cf['name'] == 'twitter') {
                    $field_icon_af = $design_style ? '<i class="fab fa-twitter-square fa-fw" aria-hidden="true"></i> ' : '<i class="fab fa-twitter-square" aria-hidden="true"></i>';
                } else {
                    $field_icon_af = $design_style ? '<i class="fas fa-link fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-link" aria-hidden="true"></i>';
                }

            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $a_url = geodir_parse_custom_field_url($gd_post->{$cf['htmlvar_name']});

            $website = !empty($a_url['url']) ? $a_url['url'] : '';
            $title = !empty($a_url['label']) ? $a_url['label'] : $cf['frontend_title'];
            if(!empty($cf['default_value'])){$title = $cf['default_value'];}
            $title = $title != '' ? __(stripslashes($title), 'geodirectory') : '';
            $post_id =  isset($gd_post->ID) ? $gd_post->ID : 0;

            // all search engines that use the nofollow value exclude links that use it from their ranking calculation
            $rel = strpos($website, get_site_url()) !== false ? '' : 'rel="nofollow"';

            $value = '<a href="' . $website . '" target="_blank" ' . $rel . ' >' . apply_filters( 'geodir_custom_field_website_name', $title, $website, $post_id ) . '</a>';

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $gd_post->{$cf['htmlvar_name']};
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $website;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-website" style="' . $field_icon . '">' . $field_icon_af;
            //if($output=='' || isset($output['label']))$html .= $field_icon_af ? '<span class="geodir_post_meta_title" >'.$field_icon_af . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            /**
             * Filter custom field website name.
             *
             * @since 1.0.0
             *
             * @param string $title Website Title.
             * @param string $website Website URL.
             * @param int $gd_post->ID Post ID.
             */
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_url','geodir_cf_url',10,5);


/**
 * Get the html output for the custom field: phone
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_phone($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '0001010101010';
    }

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_phone_loc_{$location}")){
        /**
         * Filter the phone html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_phone_var_{$html_var}")){
        /**
         * Filter the phone html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_phone_key_{$cf['field_type_key']}")){
        /**
         * Filter the phone html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = $design_style ? '<i class="fas fa-phone fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-phone" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $raw_value = stripslashes( $gd_post->{$cf['htmlvar_name']} );
            $value = '<a href="tel:' . preg_replace('/[^0-9+]/', '', $gd_post->{$cf['htmlvar_name']}) . '">' . $raw_value . '</a>';

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $raw_value;
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $value;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-phone" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_phone','geodir_cf_phone',10,5);


/**
 * Get the html output for the custom field: time
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_time($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '10:30';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_time_loc_{$location}")){
        /**
         * Filter the time html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_time_var_{$html_var}")){
        /**
         * Filter the time html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_time_key_{$cf['field_type_key']}")){
        /**
         * Filter the time html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if ( empty( $html ) ) {
        if ( $gd_post->{$html_var} ) {
            $value = date_i18n( get_option('time_format'), strtotime( $gd_post->{$html_var} ) );
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = $design_style ? '<i class="fas fa-clock fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-clock" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $gd_post->{$html_var};
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $value;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-time" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_time','geodir_cf_time',10,5);


/**
 * Get the html output for the custom field: datepicker
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_datepicker($html,$location,$cf,$p='',$output=''){
    global $preview;
    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '25/12/2020'; // blank works to show current date
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_datepicker_loc_{$location}")){
        /**
         * Filter the datepicker html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_datepicker_var_{$html_var}")){
        /**
         * Filter the datepicker html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_datepicker_key_{$cf['field_type_key']}")){
        /**
         * Filter the datepicker html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if ( empty( $html ) ) {
        if ( ! empty( $gd_post->{$html_var} ) && $gd_post->{$html_var} != '0000-00-00' ) {
            $design_style = geodir_design_style();
            $date_format = geodir_date_format();

            if ( $cf['extra_fields'] != '' ) {
                $_date_format = stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) );
                if ( ! empty( $_date_format['date_format'] ) ) {
                   $date_format = $_date_format['date_format'];
               }
            }

            $value = date_i18n( $date_format, strtotime( $gd_post->{$html_var} ) );

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = $design_style ? '<i class="fas fa-calendar fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-calendar" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $gd_post->{$html_var};
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $value;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_datepicker','geodir_cf_datepicker',10,5);


/**
 * Get the html output for the custom field: text
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_text( $html, $location, $cf, $p = '', $output = '' ) {
	// Check we have the post value
	if ( is_numeric( $p ) ) {
		$gd_post = geodir_get_post_info( $p );
	} else {
		global $gd_post;
	}

	if ( ! is_array( $cf ) && $cf != '' ) {
		$cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );

		if ( ! $cf ) {
			return NULL;
		}
	}

	$html_var = $cf['htmlvar_name'];

	// Block demo content
	if ( geodir_is_block_demo() ) {
		$value = '';

		if ( ! empty( $cf ) && isset( $cf['data_type'] ) ) {
			if ( $cf['data_type'] == 'INT' ) {
				$value = 100;
			} elseif ( $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) {
				$value = 100.50;
			}
		}

		$gd_post->{$html_var} = $value ? $value : __( 'Some demo text.', 'geodirectory' );
	}

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_text_loc_{$location}")){
        /**
         * Filter the text html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_text_var_{$html_var}")){
        /**
         * Filter the text html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_text_key_{$cf['field_type_key']}")){
        /**
         * Filter the text html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != '' ):
            $design_style = geodir_design_style();
            $class = ($cf['htmlvar_name'] == 'geodir_timing') ? "geodir-i-time" : "geodir-i-text";

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = ($cf['htmlvar_name'] == 'timing') ? ( $design_style ? '<i class="fas fa-clock fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-clock" aria-hidden="true"></i>' ) : "";
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $value = stripslashes_deep( $gd_post->{$cf['htmlvar_name']} );

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return $value;
            }

            if ( isset( $cf['data_type'] ) && ( $cf['data_type'] == 'INT' || $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) && isset( $cf['extra_fields'] ) && $cf['extra_fields'] ) {
                $extra_fields = stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) );

                if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) && $extra_fields['is_price'] ) {
                    if ( ! ceil( $value ) > 0 ) {
                        return '';// dont output blank prices
                    }
                    $value = geodir_currency_format_number( $value, $cf );
                } else if ( isset( $cf['data_type'] ) && $cf['data_type'] == 'INT' ) {
                    if ( ceil( $value ) > 0 ) {
                        $value = geodir_cf_format_number( $value, $cf );
                    }
                } else if ( isset( $cf['data_type'] ) && ( $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) ) {
                    if ( ceil( $value ) > 0 ) {
                        $value = geodir_cf_format_decimal( $value, $cf );
                    }
                }
            }

            if ( $cf['htmlvar_name'] == 'service_distance' && ! empty( $value ) ) {
                $value = geodir_show_distance( $value );
            }

            // Return stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                return $value;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_text','geodir_cf_text',10,5);


/**
 * Get the html output for the custom field: radio
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_radio($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'Yes';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_radio_loc_{$location}")){
        /**
         * Filter the radio html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_radio_var_{$html_var}")){
        /**
         * Filter the radio html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_radio_key_{$cf['field_type_key']}")){
        /**
         * Filter the radio html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        $html_val = isset($gd_post->{$cf['htmlvar_name']}) ? __($gd_post->{$cf['htmlvar_name']}, 'geodirectory') : '';
        if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != ''):

            if (!empty($cf['option_values'])) {
                $cf_option_values = geodir_string_to_options( $cf['option_values'], true );

                if (!empty($cf_option_values)) {
                    foreach ($cf_option_values as $cf_option_value) {
                        if (isset($cf_option_value['value']) && $cf_option_value['value'] == $gd_post->{$cf['htmlvar_name']}) {
                            $html_val = $cf_option_value['label'];
                        }
                    }
                }
            }

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return stripslashes_deep( $gd_post->{$cf['htmlvar_name']} );
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return stripslashes_deep( $html_val );
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-radio" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $html_val );

            $html .= '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_radio','geodir_cf_radio',10,5);


/**
 * Get the html output for the custom field: select
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 * @since 1.6.18 Fix: Custom field should display label instead of value if set in option values.
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_select($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'Yes';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_select_loc_{$location}")){
        /**
         * Filter the select html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_select_var_{$html_var}")){
        /**
         * Filter the select html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_select_key_{$cf['field_type_key']}")){
        /**
         * Filter the select html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != ''):
            $output = geodir_field_output_process($output);
            $field_value = isset($gd_post->{$cf['htmlvar_name']}) ? __($gd_post->{$cf['htmlvar_name']}, 'geodirectory') : '';

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return stripslashes( $gd_post->{$cf['htmlvar_name']} );
            }

            if (!empty($cf['option_values'])) {
                $cf_option_values = geodir_string_to_options( $cf['option_values'], true );

                if (!empty($cf_option_values)) {
                    foreach ($cf_option_values as $cf_option_value) {
                        if (isset($cf_option_value['value']) && $cf_option_value['value'] == $gd_post->{$cf['htmlvar_name']}) {
                            $field_value = $cf_option_value['label']; // no longer needed here. Removed comment because it displays number instead of label if option vales set like "Good/1,Fair/2".
                        }
                    }
                }
            }

            // Stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                return stripslashes( $field_value );
            }

            $design_style = geodir_design_style();

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $field_value );

            $html .= '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_select','geodir_cf_select',10,5);


/**
 * Get the html output for the custom field: multiselect
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_multiselect($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }
//print_r($gd_post);
    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'Swimming Pool,WiFi,Sea View';
    }


    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_multiselect_loc_{$location}")){
        /**
         * Filter the multiselect html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_multiselect_var_{$html_var}")){
        /**
         * Filter the multiselect html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_multiselect_key_{$cf['field_type_key']}")){
        /**
         * Filter the multiselect html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){


        if (!empty($gd_post->{$cf['htmlvar_name']})):

            if (is_array($gd_post->{$cf['htmlvar_name']})) {
                $gd_post->{$cf['htmlvar_name']} = implode(', ', $gd_post->{$cf['htmlvar_name']});
            }
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return stripslashes( $gd_post->{$cf['htmlvar_name']} );
            }

            $field_values = explode(',', trim($gd_post->{$cf['htmlvar_name']}, ","));

            if(is_array($field_values)){
                $field_values = array_map('trim', $field_values);
            }

            $option_values = array();
            if (!empty($cf['option_values'])) {
                $cf_option_values = geodir_string_to_options( $cf['option_values'], true );

                if (!empty($cf_option_values)) {
                    foreach ($cf_option_values as $cf_option_value) {
                        if (isset($cf_option_value['value']) && in_array($cf_option_value['value'], $field_values)) {
                            $option_values[] = $cf_option_value['label'];
                        }
                    }
                }
            }

            // Stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                return ( ! empty( $option_values ) ? stripslashes( implode( ', ', $option_values ) ) : '' );
            }

            $field_value = '';

            $show_as_csv = $design_style && strpos($cf['css_class'], 'gd-comma-list') !== false ? true : false;

            if (count($option_values) ) {
                $ul_class = $show_as_csv ? 'list-inline d-inline' : '';
                $li_class = '';
                $field_value .= '<ul class="'.$ul_class.'">';

                $li_count = 0;
                foreach ($option_values as $val) {
                    if( $show_as_csv && count($option_values) > 1 && count($option_values)!=$li_count+1 ){
                        $val = count($option_values)!=$li_count+1 ? $val."," : $val;
                        $li_class = ' mx-0 pr-1 pr-0 pl-0 ps-0 d-inline-block';
                    }

                    $field_value .= '<li class="geodir-fv-' . sanitize_html_class( sanitize_title_with_dashes( $val ) ) . $li_class.'">' . $val . '</li>';
                    $li_count++;
                }

                $field_value .= '</ul>';
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $field_value );

            $html .= '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_multiselect','geodir_cf_multiselect',10,5);


/**
 * Get the html output for the custom field: email
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_email($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'testing@example.com';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_email_loc_{$location}")){
        /**
         * Filter the email html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_email_var_{$html_var}")){
        /**
         * Filter the email html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_email_key_{$cf['field_type_key']}")){
        /**
         * Filter the email html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        global $preview;
        if ($cf['htmlvar_name'] == 'geodir_email' && !(geodir_is_page('detail'))) {
            return ''; // Remove Send Enquiry from listings page
        }


        if ($gd_post->{$cf['htmlvar_name']}) {
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = $design_style ? '<i class="far fa-envelope fa-fw" aria-hidden="true"></i> ' : '<i class="far fa-envelope" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $is_elementor_preview = defined( 'ELEMENTOR_VERSION' ) && class_exists( 'GeoDir_Elementor' ) && GeoDir_Elementor::is_elementor_view() ? true : false; // Check if elementor preview
            $email = sanitize_email( $gd_post->{$cf['htmlvar_name']} ) ;
            $value = '';
            if ( ! empty( $email ) && ( $email != 'testing@example.com' ) && ( $e_split = explode( '@', $email ) ) && ! defined( 'REST_REQUEST' ) && ! $is_elementor_preview && ! wp_doing_ajax() && !isset( $output['strip'] ) ) {
                /**
                 * Filter email custom field name output.
                 *
                 * @since 1.5.3
                 *
                 * @param string $email The email string being output.
                 * @param array $cf Custom field variables array.
                 */
                $email_name = apply_filters( 'geodir_email_field_name_output', $email, $cf );
                $value .= '<a href="javascript:void(0)" onclick="javascript:window.open(\'mailto:\'+([\'' . $e_split[0] . '\',\'' . $e_split[1] . '\']).join(\'@\'),\'_blank\')">' . str_replace( "@", "<!---->@<!---->", $email_name ) . '</a>';
            } elseif ( ! empty( $email ) && ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $is_elementor_preview || wp_doing_ajax() ) && !isset( $output['strip'] ) ) {
                /**
                 * Filter email custom field name output.
                 *
                 * @since 1.5.3
                 *
                 * @param string $email The email string being output.
                 * @param array $cf Custom field variables array.
                 */
                $email_name = apply_filters( 'geodir_email_field_name_output', $email, $cf );
                $value .= "<a href='mailto:$email' target='_blank'>$email_name</a>";
            } else {
                $value .= $email;
            }

            $value = apply_filters( 'geodir_custom_field_output_email_value', $value, $gd_post, $location, $cf, $output );

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return stripslashes( $email );
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $value;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-email" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $value );

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_email','geodir_cf_email',10,5);


/**
 * Get the html output for the custom field: file
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_file( $html, $location, $cf, $p = '', $output = '' ) {
    // check we have the post value
    if ( is_numeric( $p ) ) {
        $gd_post = geodir_get_post_info( $p );
    } else {
        global $gd_post;
    }

    if ( ! is_array( $cf ) && $cf != '' ){
        $cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );
        if ( ! $cf ) {
            return NULL;
        }
    }

    $html_var = $cf['htmlvar_name'];

    // Block demo content
    //@todo this custom field is not working, so we need to fix it and then test
    if ( geodir_is_block_demo() ) {
        $gd_post->{$html_var} = 'testing@example.com';
    }

    // Check if there is a location specific filter.
    if ( has_filter( "geodir_custom_field_output_file_loc_{$location}" ) ) {
        /**
         * Filter the file html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_file_loc_{$location}", $html, $cf, $output );
    }

    // Check if there is a custom field specific filter.
    if ( has_filter( "geodir_custom_field_output_file_var_{$html_var}" ) ) {
        /**
         * Filter the file html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_file_var_{$html_var}", $html, $location, $cf, $output );
    }

    // Check if there is a custom field key specific filter.
    if ( has_filter( "geodir_custom_field_output_file_key_{$cf['field_type_key']}" ) ) {
        /**
         * Filter the file html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_file_key_{$cf['field_type_key']}", $html, $location, $cf, $output );
    }

    // If not html then we run the standard output.
    if ( empty( $html ) ) {
        $post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;

        $design_style = geodir_design_style();
        $extra_fields = ! empty( $cf['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) ) : NULL;
        $file_limit = ! empty( $extra_fields ) && ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
        $file_limit = apply_filters( "geodir_custom_field_file_limit", $file_limit, $cf, $gd_post );
        $revision_id = '';
        $file_status = '1';

        // Preview
        if ( is_preview() ) {
            if ( ! empty( $post_id ) ) {
                $revision_id = $post_id;
            }

            // Show files with all statuses to admin & post author.
            if ( geodir_listing_belong_to_current_user( $post_id ) ) {
                $file_status = '';
            }
        }

        $files = GeoDir_Media::get_attachments_by_type( $gd_post->ID, $html_var, $file_limit, $revision_id, '', $file_status );

        if ( ! empty( $files ) ) {
            $output = geodir_field_output_process( $output );

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                $value_raw = ( isset( $gd_post->{$html_var} ) ? stripslashes_deep( $gd_post->{$html_var} ) : '' );

                return apply_filters( 'geodir_cf_file_output_value_raw', $value_raw, $files, $location, $cf, $output );
            }

            $allowed_file_types = ! empty( $extra_fields['gd_file_types'] ) && is_array( $extra_fields['gd_file_types'] ) && ! in_array( "*", $extra_fields['gd_file_types'] ) ? $extra_fields['gd_file_types'] : '';

            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];
            $upload_baseurl = $upload_dir['baseurl'];
            $file_paths = '';
            $file_urls = array();

            foreach ( $files as $file ) {
                $file_path = isset( $file->file ) ? $file->file : '';
                $title = isset( $file->title ) && $file->title != '' ? strip_tags( stripslashes_deep( $file->title ) ) : '';
                $desc = isset( $file->caption ) ? stripslashes_deep( $file->caption ) : '';
                $url = $upload_baseurl . $file_path;
                $file_urls[] = $url;
                $outout_item = '';

                if ( ! empty( $file ) ) {
                    $image_name_arr = explode( '/', $url );
                    $curr_img_dir = $image_name_arr[count( $image_name_arr ) - 2];
                    $filename = end( $image_name_arr );
                    $img_name_arr = explode( '.', $filename );

                    $arr_file_type = wp_check_filetype( $filename );
                    if ( empty( $arr_file_type['ext'] ) || empty( $arr_file_type['type'] ) ) {
                        continue;
                    }

                    $uploaded_file_type = $arr_file_type['type'];
                    $file_ext = $arr_file_type['ext'];

                    if ( ! empty( $allowed_file_types ) && ! in_array( $file_ext, $allowed_file_types ) ) {
                        continue; // Invalid file type.
                    }

                    $ext_path = '_' . $html_var . '_';
                    if ( $title ) {
                        $_filename = $title;
                    } else {
                        $_filename = explode( $ext_path, $filename );
                        $_filename = $_filename[count( $_filename ) - 1];
                    }
                    /**
                     * @since 2.0.0.67
                     */
                    if ( has_filter( 'geodir_cf_file_' . $html_var . '_filename' ) ) {
                        $_filename = apply_filters( 'geodir_cf_file_' . $html_var . '_filename', $_filename, $gd_post, $file, $cf );
                    }

                    //$allowed_file_types = array('application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain');
                    $image_file_types = geodir_image_mime_types();
                    $audio_file_types = array( 'audio/mpeg', 'audio/ogg', 'audio/mp4', 'audio/vnd.wav', 'audio/basic', 'audio/mid' );

                    // If the uploaded file is image
                    $file_type = 'unknown';
					$wrap_class = '';
                    if ( in_array( $uploaded_file_type, $image_file_types ) ) { // Image
                        $file_type = 'image';
						$wrap_class = ' geodir-images';

						$image_wrap_class = '';
						$image_class = 'img-responsive';
						if ( $design_style ) {
							$image_wrap_class = 'embed-has-action';
							$image_class .= ' mw-100 embed-responsive-item embed-item-cover-xy';
						}
						$lightbox_attrs = apply_filters( 'geodir_link_to_lightbox_attrs', '' );

                        $outout_item .= '<span class="geodir-cf-file-name text-break clearfix mb-1"><i aria-hidden="true" class="fa fa-file-image"></i> ' . $_filename . '</span>';
                        $outout_item .= '<a href="' . $url . '" class="geodir-lightbox-image ' . $image_wrap_class . '" data-lity ' . $lightbox_attrs . '>';
                        $outout_item .= '';//@todo this function needs replaced ::::::: geodir_show_image(array('src' => $file), 'thumbnail', false, false)

						$image_params = array(
							'size' => 'medium',
							'align' => '',
							'class' => $image_class
						);
						/**
						  * Filter image file output parameters.
						  *
						  * @since 2.1.0.17
						  *
						  * @param array  $image_params Image parameters.
						  * @param object $file Image file object.
						  */
						$image_params = apply_filters( 'geodir_cf_file_output_image_params', $image_params, $file );

                        $image_tag = geodir_get_image_tag( $file, $image_params['size'], $image_params['align'], $image_params['class'] );
						$metadata = ! empty( $file->metadata ) ? maybe_unserialize( $file->metadata ) : array();
						if ( $image_params['size'] != 'thumbnail' ) {
							$image_tag =  wp_image_add_srcset_and_sizes( $image_tag, $metadata , 0 );
						}
						$outout_item .= $image_tag;
						if ( $design_style ) {
							$outout_item .= '<i class="fas fa-search-plus" aria-hidden="true"></i>';
						}
                        $outout_item .= '</a>';
                    } elseif ( in_array( $uploaded_file_type, $audio_file_types ) || in_array( $file_ext, wp_get_audio_extensions() ) ) { // Audio
                        $file_type = 'audio';
                        $outout_item .= '<span class="geodir-cf-file-name text-break clearfix"><i aria-hidden="true" class="fa fa-file-audio"></i> ' . $_filename . '</span>';
                        $outout_item .= do_shortcode( '[audio src="' . $url . '" ]' );
                    } elseif ( in_array( $file_ext, wp_get_video_extensions() ) ) { // Video
                        $file_type = 'video';
                        $outout_item .= '<span class="geodir-cf-file-name text-break clearfix"><i aria-hidden="true" class="fa fa-file-video"></i> ' . $_filename . '</span>';
                        $outout_item .= do_shortcode( wp_embed_handler_video( array(), array(), $url, array() ) );
                    } else {
                        $outout_item .= '<a class="gd-meta-file" href="' . $url . '" target="_blank" data-lity title="' . esc_attr( $title ) . '"><i aria-hidden="true" class="fa fa-file"></i> ' . $_filename . '</a>';
                    }
                    $outout_item = '<div class="geodir-custom-field-file clearfix geodir-cf-file-' . $file_ext . ' geodir-cf-type-' . $file_type . $wrap_class . '"> ' . $outout_item . '</div>';

                     /**
                     * Filter the file output html.
                     *
                     * @since 2.0.0.81
                     *
                     * @param string $outout_item The file html outout.
                     * @param object $file The file object.
                     * @param string $location The location to output the html.
                     * @param array $cf The custom field array.
                     * @param string $output The output string that tells us what to output.
                     */
                    $file_paths .= apply_filters( 'geodir_cf_file_output_item', $outout_item, $file, $location, $cf, $output );
                }
            }

            $field_icon = geodir_field_icon_proccess( $cf );
            if ( strpos( $field_icon, 'http' ) !== false ) {
                $field_icon_af = '';
            } elseif ( $field_icon == '' ) {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            // Stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                $value_strip = ! empty( $file_urls ) ? $file_urls[0] : '';

                return apply_filters( 'geodir_cf_file_output_value_strip', $value_strip, $file_urls, $files, $location, $cf, $output );
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if ( $output=='' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-file" style="' . $field_icon . '">' . $field_icon_af;
            if ( $output=='' || isset( $output['label'] ) ) $html .= trim( $cf['frontend_title'] ) != '' ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__( $cf['frontend_title'], 'geodirectory' ) . ': </span>' : '';
            if ( $output=='' || isset( $output['icon'] ) ) $html .= '</span>';
            if ( $output=='' || isset( $output['value'] ) ) $html .= $file_paths;

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter( 'geodir_custom_field_output_file','geodir_cf_file', 10, 5 );

/**
 * Get the html output for the custom field: textarea
 *
 * @global int $gd_skip_the_content
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_textarea( $html, $location, $cf, $p = '', $output = '' ) {
    global $gd_skip_the_content;

    // Check we have the post value
    if ( is_numeric( $p ) ) {
        $gd_post = geodir_get_post_info( $p );
    } else{
        global $gd_post;
    }

    if ( ! is_array( $cf ) && $cf != '' ){
        $cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );
        if ( ! $cf ) {
            return NULL;
        }
    }

    $html_var = $cf['htmlvar_name'];

    // Block demo content
    if ( geodir_is_block_demo() ) {
		if($html_var == 'video'){
			$gd_post->{$html_var} = 'https://www.youtube.com/watch?v=eEzD-Y97ges';
		}elseif($html_var == 'virtual_tour' || $html_var == 'cf360_tour'){
		    $gd_post->{$html_var} = '<iframe border="0" loading="lazy" src="https://my.matterport.com/show/?m=Zh14WDtkjdC"></iframe>';
	    }else{
			$gd_post->{$html_var} = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam risus metus, rutrum in nunc eu, vestibulum iaculis lacus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aenean tristique arcu et eros convallis elementum. Maecenas sit amet quam eu velit euismod viverra. Etiam magna augue, mollis id nisi sit amet, eleifend sagittis tortor. Suspendisse vitae dignissim arcu, ac elementum eros. Mauris hendrerit at massa ut pellentesque.';
		}
    }

    // Check if there is a location specific filter.
    if ( has_filter( "geodir_custom_field_output_textarea_loc_{$location}" ) ) {
        /**
         * Filter the textarea html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_textarea_loc_{$location}", $html, $cf, $output );
    }

    // Check if there is a custom field specific filter.
    if ( has_filter( "geodir_custom_field_output_textarea_var_{$html_var}" ) ) {
        /**
         * Filter the textarea html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_textarea_var_{$html_var}", $html, $location, $cf, $output );
    }

    // Check if there is a custom field key specific filter.
    if ( has_filter( "geodir_custom_field_output_textarea_key_{$cf['field_type_key']}" ) ) {
        /**
         * Filter the textarea html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_output_textarea_key_{$cf['field_type_key']}", $html, $location, $cf, $output );
    }

    // If not html then we run the standard output.
    if ( empty( $html ) ) {
        if ( ! empty( $gd_post->{$html_var} ) ) {
            $design_style = geodir_design_style();
            $extra_fields = ! empty( $cf['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) ) : NULL;
            $field_icon = geodir_field_icon_proccess( $cf );
            $output = geodir_field_output_process( $output );
            $embed = ! empty( $extra_fields['embed'] ) || $html_var == 'video' ? true : false;
            if ( strpos( $field_icon, 'http' ) !== false ) {
                $field_icon_af = '';
            } elseif ( $field_icon == '' ) {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return stripslashes( $gd_post->{$html_var} );
            }

            if( $design_style ){
                $cf['css_class'] .= " position-relative";
            }

            $max_height = ! empty( $output['fade'] ) ? absint( $output['fade'] )."px" : '';
            $max_height_style = $max_height ? " style='max-height:$max_height;overflow:hidden;' " : '';

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $html_var . '" ' . $max_height_style . '>';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            if ( $output == '' || isset( $output['label'] ) ) $html .= trim( $cf['frontend_title'] ) != '' ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__( $cf['frontend_title'], 'geodirectory' ) . ': '.'</span>' : '';
            if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';

            if ( $output == '' || isset( $output['value'] ) ) {
                $value = stripslashes( $gd_post->{$html_var} );

                $gd_skip_the_content = true; // Set global variable to prevent looping on some themes/plugins.
                $content = '';
                if ( $html_var != 'post_content' ) {
                    if ( isset( $output['strip'] ) ) {
                        $content =  wp_strip_all_tags( do_shortcode( wpautop( $value ) ) );
                    } else {
                        if ( $embed ) {
                             // Embed media.
                            global $wp_embed;

                            $matterport = $value ? parse_url( $value, PHP_URL_HOST ) : '';
                            $matterport = $matterport ? strpos( $matterport, "my.matterport.com" ) === 0 : 0;
                            $value = $wp_embed->autoembed( $value );

                            if ( $matterport ) {
                                $value = str_replace('sandbox="allow-scripts"','sandbox="allow-scripts allow-same-origin"', $value);
                            }
                        }

                        $content = do_shortcode( wpautop( $value ) );
                    }
                } else {
                    // Post content
                    if ( isset( $output['strip'] ) ) {
                        $content = wp_strip_all_tags( apply_filters( 'the_content', $value ) );
                    } else {
                        $content = apply_filters( 'the_content', $value );
                    }
                }

                if ( $design_style ) {
                    // check if we have any media in iframe first, if so maybe wrap in responsive wrapper.
                    $content = str_replace( array( "<iframe ", "</iframe>" ), array( '<div class="geodir-embed-container embed-responsive embed-responsive-16by9 ratio ratio-16x9"><iframe ', '</iframe></div>' ), $content );
                }

                $gd_skip_the_content = false;

                if ( $content ) {
                    // Set a limit if it exists
                    if ( ! empty( $output['limit'] ) ) {
                        $limit = absint( $output['limit'] );
                        $content = wp_trim_words( $content, $limit, '' );
                    }

                    $html .= $content;

                    // add read more
                    if ( isset( $output['more'] ) ) {
                        $post_id = isset( $gd_post->id ) ? absint( $gd_post->id ) : 0;
                        $more_text = empty( $output['more'] ) ? __( "Read more...", "geodirectory" ) : __( $output['more'], "geodirectory" );
                        $link =  get_permalink( $post_id );
                        $link = $link . "#" . $html_var; // Set the hash value
                        $link_class = ! empty( $output['fade'] ) ? 'gd-read-more-fade' : '';
                        $link_style = '';

                        if( $design_style && $max_height ){
                            $link_class .= " w-100 position-absolute text-center pt-5";
                            $link_style .= "bottom:0;left:0;background-image: linear-gradient(to bottom,transparent,#fff);";
                        }

                        $html .= " <a href='$link' class='gd-read-more  $link_class' style='$link_style'>" . esc_attr( $more_text ) . "</a>";
                    }
                }
            }

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_textarea','geodir_cf_textarea',10,5);

/**
 * Get the html output for the custom field: html
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_html($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }


	$html_var = $cf['htmlvar_name'];

	// Block demo content
	if ( geodir_is_block_demo() ) {
		if($html_var == 'video'){
			$gd_post->{$html_var} = 'https://www.youtube.com/watch?v=eEzD-Y97ges';
		}elseif($html_var == 'virtual_tour' ){
			$gd_post->{$html_var} = '<iframe border="0" loading="lazy" src="https://my.matterport.com/show/?m=Zh14WDtkjdC"></iframe>';
		}else{
			$gd_post->{$cf['htmlvar_name']} = '<b>This is some bold HTML</b>';
		}
	}

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_html_loc_{$location}")){
        /**
         * Filter the html html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_html_var_{$html_var}")){
        /**
         * Filter the html html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_html_key_{$cf['field_type_key']}")){
        /**
         * Filter the html html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (!empty($gd_post->{$cf['htmlvar_name']})) {

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return stripslashes( $gd_post->{$cf['htmlvar_name']} );
            }

            $value = wpautop(do_shortcode(stripslashes($gd_post->{$cf['htmlvar_name']})));

            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return $value;
            }

            if ( geodir_design_style() ) {
                // check if we have any media in iframe first, if so maybe wrap in responsive wrapper.
                $value = str_replace( array( "<iframe ", "</iframe>" ), array( '<div class="geodir-embed-container embed-responsive embed-responsive-16by9 ratio ratio-16x9"><iframe ', '</iframe></div>' ), $value );
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_html','geodir_cf_html',10,5);



/**
 * Get the html output for the custom field: taxonomy
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_taxonomy( $html, $location, $cf, $p = '', $output = '' ) {
	// Check we have the post value.
	if ( is_numeric( $p ) ) {
		$gd_post = geodir_get_post_info( $p );
	} else {
		global $gd_post;
	}

	if ( ! is_array( $cf ) && $cf != '' ) {
		$cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );

		if ( ! $cf ) {
			return NULL;
		}
	}

    // Block demo content
    if ( geodir_is_block_demo() ) {
        if ( $cf['htmlvar_name'] == 'post_category' ) {
            $demo_tax = 'gd_placecategory';
        } else if ( $cf['htmlvar_name'] == 'post_tags' ) {
            $demo_tax = 'gd_place_tags';
        }

        $demoterms = get_terms( array(
            'taxonomy' => $demo_tax,
            'hide_empty' => false,
            'number'    => 2
        ) );
        $demo_terms = '';
        if ( ! empty( $demoterms ) ) {
            foreach( $demoterms as $demoterm ) {
                $demo_terms .= $demoterm->term_id . ",";
            }
        }
        $gd_post->{$cf['htmlvar_name']} = $demo_terms;
        $gd_post->post_type  = 'gd_place';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_taxonomy_loc_{$location}")){
        /**
         * Filter the taxonomy html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_taxonomy_var_{$html_var}")){
        /**
         * Filter the taxonomy html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_taxonomy_key_{$cf['field_type_key']}")){
        /**
         * Filter the taxonomy html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){
		$post_id = ! empty( $gd_post->ID ) ? absint( $gd_post->ID ) : 0;
        if ( $post_id && wp_is_post_revision( $post_id ) ) {
            $post_id = wp_get_post_parent_id( $post_id );
        }
        $post_type = $post_id ? get_post_type( $post_id ) : 'gd_place';

        if($html_var=='post_category'){
            $post_taxonomy = $post_type . 'category';
        }elseif($html_var == 'post_tags'){
            $post_taxonomy = $post_type . '_tags';
        }else{
            $post_taxonomy = '';
        }
        if ($post_taxonomy && !empty($gd_post->{$html_var})) {

            $output = geodir_field_output_process($output);
            $field_value = $gd_post->{$html_var};

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                return $field_value;
            }

            $links = array();
            $terms = array();
            $termsOrdered = array();
            if (!is_array($field_value)) {
                $field_value = explode(",", trim($field_value, ","));
            }

            $field_value = array_unique($field_value);

            if (!empty($field_value)) {
                foreach ($field_value as $term) {
                    $term = trim($term);

                    if ($term != '') {
                        $ttype = $html_var == 'post_tags' ? 'name' : 'id';
                        $term = get_term_by( $ttype, $term, $post_taxonomy);
                        if (is_object($term)) {
                            $links[] = "<a href='" . esc_attr(get_term_link($term, $post_taxonomy)) . "'>" . $term->name . "</a>";
                            $terms[] = $term;
                        }
                    }
                }
                if (!empty($links)) {
                    // order alphabetically
                    asort($links);
                    foreach (array_keys($links) as $key) {
                        $termsOrdered[$key] = $terms[$key];
                    }
                    $terms = $termsOrdered;
                }
            }
            $html_value = !empty($links) && !empty($terms) ? wp_sprintf('%l', $links, (object)$terms) : '';

            // Stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                return $html_value;
            }

            if ($html_value != '') {
                $field_icon = geodir_field_icon_proccess($cf);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } else if ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

                $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

                $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

                if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-taxonomy" style="' . $field_icon . '">' . $field_icon_af;
                if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
                if($output=='' || isset($output['icon']))$html .= '</span>';
                if($output=='' || isset($output['value']))$html .= $html_value;

                $html .= '</div>';
            }
        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_tags','geodir_cf_taxonomy',10,5);
add_filter('geodir_custom_field_output_categories','geodir_cf_taxonomy',10,5);
add_filter('geodir_custom_field_output_taxonomy','geodir_cf_taxonomy',10,5);


/**
 * Get the html output for the custom field: address
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 * @since 1.6.21 New hook added to filter address fields being displayed.
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_address($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }


    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '123 Demo Street';
        $gd_post->street = '123 Demo Street';
        $gd_post->street2 = 'Street line 2';
        $gd_post->region = 'Pennsylvania';
        $gd_post->city = 'Philadelphia';
        $gd_post->zip = '19107';
        $gd_post->neighbourhood = 'Chinatown';

    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_address_loc_{$location}")){
        /**
         * Filter the address html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_address_var_{$html_var}")){
        /**
         * Filter the address html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_address_key_{$cf['field_type_key']}")){
        /**
         * Filter the address html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        $show_street_in_address = true;
        $show_street2_in_address = true;
        $show_city_in_address = true;
        $show_region_in_address = true;
        $show_country_in_address = true;
        $show_zip_in_address = true;

        /**
         * Filter "show street in address" value.
         *
         * @since 2.8.92
         */
        $show_street_in_address = apply_filters( 'geodir_show_street_in_address', $show_street_in_address, $gd_post );

        if (!empty($cf['extra_fields'])) {
            $extra_fields = stripslashes_deep(maybe_unserialize($cf['extra_fields']));
            $addition_fields = '';
            if (!empty($extra_fields)) {
                $show_street2_in_address = false;
                if (isset($extra_fields['show_street2']) && $extra_fields['show_street2']) {
                    $show_street2_in_address = true;
                }
                /**
                 * Filter "show city in address" value.
                 *
                 * @since 1.0.0
                 */
                $show_street2_in_address = apply_filters('geodir_show_street2_in_address', $show_street2_in_address);
                $show_city_in_address = false;
                if (isset($extra_fields['show_city']) && $extra_fields['show_city']) {
                    $show_city_in_address = true;
                }
                /**
                 * Filter "show city in address" value.
                 *
                 * @since 1.0.0
                 */
                $show_city_in_address = apply_filters('geodir_show_city_in_address', $show_city_in_address);
                $show_region_in_address = false;
                if (isset($extra_fields['show_region']) && $extra_fields['show_region']) {
                    $show_region_in_address = true;
                }
                /**
                 * Filter "show region in address" value.
                 *
                 * @since 1.6.6
                 */
                $show_region_in_address = apply_filters('geodir_show_region_in_address', $show_region_in_address);
                $show_country_in_address = false;
                if (isset($extra_fields['show_country']) && $extra_fields['show_country']) {
                    $show_country_in_address = true;
                }
                /**
                 * Filter "show country in address" value.
                 *
                 * @since 1.6.6
                 */
                $show_country_in_address = apply_filters('geodir_show_country_in_address', $show_country_in_address);
                $show_zip_in_address = false;
                if (isset($extra_fields['show_zip']) && $extra_fields['show_zip']) {
                    $show_zip_in_address = true;
                }
                /**
                 * Filter "show zip in address" value.
                 *
                 * @since 1.6.6
                 */
                $show_zip_in_address = apply_filters('geodir_show_zip_in_address', $show_zip_in_address);
            }
        }

        if ( $gd_post->street || $gd_post->city || $gd_post->region || $gd_post->country ) {
            $design_style = geodir_design_style();
            $field_icon = geodir_field_icon_proccess( $cf );
            $output = geodir_field_output_process($output);
            if ( strpos( $field_icon, 'http' ) !== false ) {
                $field_icon_af = '';
            } elseif ( $field_icon == '' ) {
                $field_icon_af = $design_style ? '<i class="fas fa-home fa-fw" aria-hidden="true"></i> ' : '<i class="fas fa-home" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon    = '';
            }

            $address_items = array(
                'post_title',
                'street',
                'street2',
                'neighbourhood',
                'city',
                'region',
                'zip',
                'country',
                'latitude',
                'longitude'
            );

            $address_template = !empty($cf['address_template']) ? $cf['address_template'] : '%%street_br%% %%street2_br%% %%neighbourhood_br%% %%city_br%% %%region_br%% %%zip_br%% %%country%%';

            $address_template = apply_filters(
                "geodir_cf_address_template",
                $address_template,
                $cf,
                $location
            );

            $address_fields = array();

            if ( isset($gd_post->post_title) ) {
                $address_fields['post_title'] = '<span itemprop="placeName">' . $gd_post->post_title . '</span>';
            }
            if ( $show_street_in_address && isset( $gd_post->street ) && $gd_post->street ) {
                $address_fields['street'] = '<span itemprop="streetAddress">' . $gd_post->street . '</span>';
            }
            if ( $show_street2_in_address && isset( $gd_post->street2 ) && $gd_post->street2 ) {
                $address_fields['street2'] = '<span itemprop="streetAddress2">' . $gd_post->street2. '</span>';
            }
            if ( $show_city_in_address && isset( $gd_post->city ) && $gd_post->city ) {
                $address_fields['city'] = '<span itemprop="addressLocality">' . $gd_post->city. '</span>';
            }
            if ($show_region_in_address && isset( $gd_post->region ) && $gd_post->region ) {
                $address_fields['region'] = '<span itemprop="addressRegion">' . $gd_post->region . '</span>';
            }
            if ( $show_zip_in_address && isset( $gd_post->zip ) && $gd_post->zip ) {
                $address_fields['zip'] = '<span itemprop="postalCode">' . $gd_post->zip . '</span>';
            }
            if ($show_country_in_address && isset( $gd_post->country ) && $gd_post->country ) {
                $address_fields['country'] = '<span itemprop="addressCountry">' . __( $gd_post->country, 'geodirectory' ) . '</span>';
            }
            if ( isset( $gd_post->latitude ) && $gd_post->latitude ) {
                $address_fields['latitude'] = '<span itemprop="addressLatitude">' . $gd_post->latitude . '</span>';
            }
            if ( isset( $gd_post->longitude ) && $gd_post->longitude ) {
                $address_fields['longitude'] = '<span itemprop="addressLongitude">' . $gd_post->longitude . '</span>';
            }

            // trick LM to add hoods if
            if (strpos($address_template, '%%neighbourhood') !== false) {
                if(!empty($cf['extra_fields'])){
                  $extras = maybe_unserialize($cf['extra_fields']);
                    $extras['show_neighbourhood'] = true;
                    $cf['extra_fields'] = maybe_serialize($extras);
                }else{
                    $cf['extra_fields']['show_neighbourhood'] = true;
                }
            }

            /**
             * Filter the address fields array being displayed.
             *
             * @param array $address_fields The array of address fields.
             * @param object $gd_post The current post object.
             * @param array $cf The custom field array details.
             * @param string $location The location to output the html.
             *
             * @since 1.6.21
             */
            $address_fields = apply_filters('geodir_custom_field_output_address_fields', $address_fields, $gd_post, $cf, $location);

            $address_fields_extra = array(
                'c' => ', ', // Value with comma
                'br' => '<br>', // Value with line break
                'brc' => ',<br>', // Value with comma & line break
                'space' => ' ', // Value with space
                'dash' => ' - ' // Value with dash
            );

            /**
             * Filter the address fields array being displayed.
             *
             * @param array $address_fields The array of address fields.
             * @param object $gd_post The current post object.
             * @param array $cf The custom field array details.
             * @param string $location The location to output the html.
             *
             * @since 2.1.1.13
             */
            $address_fields_extra = apply_filters( 'geodir_custom_field_output_address_fields_extra', $address_fields_extra, $gd_post, $cf, $location );

            foreach ( $address_items as $type ) {
                // Normal value
                $value = isset( $address_fields[ $type ] ) ? $address_fields[ $type ] : '';
                $address_template = str_replace( '%%' . $type . '%%', $value, $address_template );

                foreach ( $address_fields_extra as $_var => $_rep ) {
                    $address_template = str_replace( '%%' . $_var . '_' . $type . '%%', ( $value != '' ? $_rep . $value : '' ), $address_template );
                    $address_template = str_replace( '%%' . $type  . '_' . $_var . '%%', ( $value != '' ? $value . $_rep : '' ), $address_template );
                }
            }

            foreach ( $address_fields_extra as $_var => $_rep ) {
                $address_template = str_replace( '%%' . $_var . '%%', $_rep, $address_template );
            }

            $address_fields = $address_template;

            // Render private address.
            $address_fields = geodir_post_address( $address_fields, 'address', $gd_post );

            $plain_value = wp_strip_all_tags( $address_fields, true );
            if ( $plain_value == '' ) {
                return $html;
            }

            // Database value.
            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                $address_fields = str_replace( "<br>", "", $address_fields );
                return stripslashes( wp_strip_all_tags( $address_fields, true ) );
            }

            // Stripped value.
            if ( ! empty( $output ) && isset( $output['strip'] ) ) {
                $address_fields = str_replace( "<br>", ",", $address_fields );
                return stripslashes( wp_strip_all_tags( $address_fields, true ) );
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" itemscope itemtype="http://schema.org/PostalAddress">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-address" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $address_fields );
			if ( ! empty( $output ) && isset( $output['link'] ) ) {
				$value = stripslashes( $address_fields );
				$address = normalize_whitespace( wp_strip_all_tags( $value ) );
				$map_link = 'https://www.google.com/maps?q=' . urlencode( $address );

				/**
				 * Filter address map link.
				 *
				 * @since 2.1.1.9
				 *
				 * @param string $map_link Address map link.
				 * @param string $address Full address.
				 * @param object $gd_post Post object.
				 * @param array  $cf Custom field.
				 */
				$map_link = apply_filters( 'geodir_custom_field_output_address_map_link', $map_link, $address, $gd_post, $cf );

				$html .= '<a href="' . esc_url( $map_link ) . '" target="_blank" title="' . esc_attr__( 'View on map', 'geodirectory' ) . '">';
				$html .= $value;
				$html .= '</a>';
			}

            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_address','geodir_cf_address',10,5);

/**
 * Filter the business hours custom field output to show a link.
 *
 * @param string $html The html to be output.
 * @param string $location The location name of the output location.
 * @param object $cf The custom field object info.
 * @param string $output The output string that tells us what to output.
 *
 * @since 2.0.0
 * @return string The html to output.
 */
function geodir_cf_business_hours( $html, $location, $cf, $p = '', $output = '' ) {
	global $aui_bs5;

	// Check we have the post value.
	if ( is_numeric( $p ) ) {
		$gd_post = geodir_get_post_info( $p );
	} else {
		global $gd_post;
	}

	if ( ! is_array( $cf ) && $cf != '' ) {
		$cf = geodir_get_field_infoby( 'htmlvar_name', $cf, $gd_post->post_type );

		if ( ! $cf ) {
			return NULL;
		}
	}

	// Block demo content
	if ( geodir_is_block_demo() ) {
		$gd_post->{$cf['htmlvar_name']} = '["Mo 09:00-17:00","Tu 09:00-17:00","We 09:00-17:00","Th 09:00-17:00","Fr 09:00-17:00"],["UTC":"+0"]';
	}

	$html_var = $cf['htmlvar_name'];

	// Check if there is a location specific filter.
	if ( has_filter( "geodir_custom_field_output_business_hours_loc_{$location}" ) ) {
		/**
		 * Filter the business hours html by location.
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 * @param string $output The output string that tells us what to output.
		 * @since 2.0.0
		 */
		$html = apply_filters( "geodir_custom_field_output_business_hours_loc_{$location}", $html, $cf, $output );
	}

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_output_business_hours_var_{$html_var}" ) ) {
		/**
		 * Filter the business hours html by individual custom field.
		 *
		 * @param string $html The html to filter.
		 * @param string $location The location to output the html.
		 * @param array $cf The custom field array.
		 * @param string $output The output string that tells us what to output.
		 * @since 2.0.0
		 */
		$html = apply_filters( "geodir_custom_field_output_business_hours_var_{$html_var}", $html, $location, $cf, $output );
	}

	// Check if there is a custom field key specific filter.
	if ( has_filter( "geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}" ) ) {
		/**
		 * Filter the business hours html by field type key.
		 *
		 * @param string $html The html to filter.
		 * @param string $location The location to output the html.
		 * @param array $cf The custom field array.
		 * @param string $output The output string that tells us what to output.
		 * @since 2.0.0
		 */
		$html = apply_filters( "geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}", $html, $location, $cf, $output );
	}

	// If not html then we run the standard output.
	if ( empty( $html ) ) {
		if ( ! empty( $gd_post->{$cf['htmlvar_name']} ) ) {
			$value = stripslashes_deep( $gd_post->{$cf['htmlvar_name']} );
			$business_hours = geodir_get_business_hours( $value, ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) );

			if ( empty( $business_hours['days'] ) ) {
				return $html;
			}

			$show_value = geodir_is_block_demo() ? __( "Open now","geodirectory" ) : $business_hours['extra']['today_range'];
			$preview_class = geodir_is_block_demo() ? 'text-success' : '';
			$offset = isset( $business_hours['extra']['offset'] ) ? (int) $business_hours['extra']['offset'] : '';
			$utc_offset = isset( $business_hours['extra']['utc_offset'] ) ? $business_hours['extra']['utc_offset'] : '';

			if ( ! empty( $business_hours['extra']['is_dst'] ) ) {
				$offset = isset( $business_hours['extra']['offset_dst'] ) ? (int) $business_hours['extra']['offset_dst'] : $offset;
				$utc_offset = isset( $business_hours['extra']['utc_offset_dst'] ) ? $business_hours['extra']['utc_offset_dst'] : $utc_offset;
			}

			if ( ! empty( $show_value ) ) {
				$bh_expanded = $location == 'owntab' || strpos( $cf['css_class'], 'gd-bh-expanded' ) !== false ? true : false;
				$design_style = geodir_design_style();
				$dropdown_class =  $design_style ? ' dropdown ' : '';
				$dropdown_toggle_class =  $design_style ? ' dropdown-toggle ' : '';
				$dropdown_item_class =  $design_style ? ' dropdown-item py-1 ' : '';
				$dropdown_item_inline_class =  $design_style ? ' d-inline-block ' : '';
				if ( $aui_bs5 ) {
					$dropdown_item_mr_class =  $design_style ? ' me-3 ' : '';
					$dropdown_item_float_class =  $design_style ? ' float-end' : '';
				} else {
					$dropdown_item_mr_class =  $design_style ? ' mr-3 ' : '';
					$dropdown_item_float_class =  $design_style ? ' float-right' : '';
				}
				$dropdown_menu_class =  $design_style ? ' dropdown-menu dropdown-caret-0 my-3 ' : '';

				if ( $design_style && $bh_expanded ) {
					$dropdown_class = '';
					$dropdown_menu_class = '';
					$dropdown_toggle_class = '';
				}

				$cf['field_icon'] = $design_style ? $cf['field_icon'] : $cf['field_icon'];

				$field_icon = geodir_field_icon_proccess( $cf );
				$output = geodir_field_output_process( $output );

				if ( strpos( $field_icon, 'http' ) !== false ) {
					$field_icon_af = '';
				} else if ( $field_icon == '' ) {
					$field_icon_af = '';
				} else {
					$field_icon_af = $field_icon;
					$field_icon = '';
				}

				// Database value.
				if ( ! empty( $output ) && isset( $output['raw'] ) ) {
					return $value;
				}

				$extra_class = $location == 'owntab' || strpos($cf['css_class'], 'gd-bh-expanded') !== false ? ' gd-bh-expanded' : ' gd-bh-toggled';
				if ( ! empty( $business_hours['extra']['has_closed'] ) ) {
					$extra_class .= ' gd-bh-closed';
				}

				$html = '<div class="geodir_post_meta gd-bh-show-field ' . $cf['css_class'] . ' geodir-field-' . $html_var . $extra_class . $dropdown_class. '" style="">';
				$html .= $design_style ? '<a class=" text-reset ' . $dropdown_toggle_class . ' d-block text-truncate" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' : '';
				$html .= '<span class="geodir-i-business_hours geodir-i-biz-hours '.$preview_class.'" style="' . $field_icon . '">' . $field_icon_af . '<font></font>' . ': </span>';
				$html .= '<span class="gd-bh-expand-range '.$preview_class.'" data-offset="' . $utc_offset  . '" data-offsetsec="' . $offset . '" title="' . esc_attr__( 'Expand opening hours' , 'geodirectory' ) . '"><span class="gd-bh-today-range gv-secondary">' . $show_value . '</span>';
				$html .= $design_style ? '' : '<span class="gd-bh-expand"><i class="fas fa-caret-up" aria-hidden="true"></i><i class="fas fa-caret-down" aria-hidden="true"></i></span>';
				$html .= '</span>';
				$html .= $design_style ? '</a>' : '';
				$html .= '<div class="gd-bh-open-hours ' . $dropdown_menu_class . '" style="min-width:250px;">';

				foreach ( $business_hours['days'] as $day => $slots ) {
					/**
					 * Filter business hours slot display day name.
					 *
					 * @since 2.3.29
					 */
					$day_name = apply_filters( 'geodir_output_business_hours_slot_day_name', $slots['day_short'], $day, $slots, $location, $cf );

					$class = '';
					if ( ! empty( $slots['closed'] ) ) {
						$class .= 'gd-bh-days-closed ';
					}
					$html .= '<div data-day="' . $slots['day_no'] . '" data-closed="' . $slots['closed'] . '" class="' . $dropdown_item_class . ' gd-bh-days-list ' . trim( $class ) . '"><div class="gd-bh-days-d ' . $dropdown_item_inline_class . $dropdown_item_mr_class . '">' . $day_name . '</div><div class="gd-bh-slots ' . $dropdown_item_inline_class . $dropdown_item_float_class . '">';

					foreach ( $slots['slots'] as $i => $slot ) {
						$attrs = '';
						$class = '';
						if ( ! empty( $slot['time'] ) ) {
							$attrs .= 'data-open="' . $slot['time'][0] . '"  data-close="' . $slot['time'][1] . '"';
							// Next day close
							if ( (int) $slot['time'][0] == (int)$slot['time'][1] || (int) $slot['time'][1] < (int) $slot['time'][0] ) {
								$class .= ' gd-bh-next-day';
							}
						}
						$html .= '<div ' . $attrs . ' class="gd-bh-slot' . $class . '"><div class="gd-bh-slot-r">' . $slot['range'] . '</div>';
						$html .= '</div>';
					}
					$html .= '</div></div>';
				}
				$html .= '</div></div>';
			}
		}
	}

	return $html;
}
add_filter( 'geodir_custom_field_output_business_hours', 'geodir_cf_business_hours', 10 , 5 );

/**
 * Get the html output for the custom field: author
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @param string $output The output string that tells us what to output.
 * @since 2.0.0 $output param added.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_author($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'https://example.com/author/admin/';
    }

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_url_loc_{$location}")){
        /**
         * Filter the url html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_url_var_{$html_var}")){
        /**
         * Filter the url html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_url_key_{$cf['field_type_key']}")){
        /**
         * Filter the url html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $author_id = isset($gd_post->post_author) ? absint($gd_post->post_author) : '0';
            $author_name = get_the_author_meta( 'user_nicename' , $author_id);
            $author_url = get_author_posts_url( $author_id, $author_name );
            $author_link = '<a href="'.$author_url.'" >'. $author_name.'</a>';

            if ( ! empty( $output ) && isset( $output['raw'] ) ) {
                // Database value.
                return $author_id;
            } elseif ( ! empty( $output ) && isset( $output['strip'] ) ) {
                // Stripped value.
                return stripslashes( $author_link );
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            $maybe_secondary_class = isset($output['icon']) ? 'gv-secondary' : '';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-address" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title '.$maybe_secondary_class.'" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $author_link );

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_author','geodir_cf_author',10,5);

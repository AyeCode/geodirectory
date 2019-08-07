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

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-checkbox" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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
function geodir_cf_fieldset($html,$location,$cf,$p=''){

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
        $fieldset_class = 'fieldset-'.sanitize_html_class(sanitize_title_with_dashes($cf['frontend_title']));

        if ($field_set_start == 1) {
            $html = '';
        } else {
            $html = '<h2 class="'.$fieldset_class.'">'. stripslashes( __($cf['frontend_title'], 'geodirectory') ) . '</h2>';
            //$field_set_start = 1;
        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_fieldset','geodir_cf_fieldset',10,4);


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

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {

                if ($cf['name'] == 'geodir_facebook') {
                    $field_icon_af = '<i class="fab fa-facebook-square" aria-hidden="true"></i>';
                } elseif ($cf['name'] == 'geodir_twitter') {
                    $field_icon_af = '<i class="fab fa-twitter-square" aria-hidden="true"></i>';
                } else {
                    $field_icon_af = '<i class="fas fa-link" aria-hidden="true"></i>';
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
            if($output=='' || isset($output['value']))$html .= '<a href="' . $website . '" target="_blank" ' . $rel . ' ><strong>' . apply_filters('geodir_custom_field_website_name', $title, $website, $post_id) . '</strong></a>';

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

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fas fa-phone" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-phone" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= '<a href="tel:' . preg_replace('/[^0-9+]/', '', $gd_post->{$cf['htmlvar_name']}) . '">' . stripslashes( $gd_post->{$cf['htmlvar_name']} ) . '</a>';

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
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $value = '';
            if ($gd_post->{$cf['htmlvar_name']} != '')
                //$value = date('h:i',strtotime($gd_post->{$cf['htmlvar_name']}));
                $value = date(get_option('time_format'), strtotime($gd_post->{$cf['htmlvar_name']}));

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fas fa-clock" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-time" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';

        endif;

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
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $date_format = geodir_date_format();
            if ($cf['extra_fields'] != '') {
                $date_format = stripslashes_deep(unserialize($cf['extra_fields']));
                $date_format = $date_format['date_format'];
            }
            // check if we need to change the format or not
            $date_format_len = strlen(str_replace(' ', '', $date_format));
            if($date_format_len>5){// if greater then 4 then it's the old style format.

                $search = array('dd','d','DD','mm','m','MM','yy'); //jQuery UI datepicker format
                $replace = array('d','j','l','m','n','F','Y');//PHP date format

                $date_format = str_replace($search, $replace, $date_format);

                $post_htmlvar_value = ($date_format == 'd/m/Y' || $date_format == 'j/n/Y' ) ? str_replace('/', '-', $gd_post->{$cf['htmlvar_name']}) : $gd_post->{$cf['htmlvar_name']}; // PHP doesn't work well with dd/mm/yyyy format
            }else{
                $post_htmlvar_value = $gd_post->{$cf['htmlvar_name']};
            }

            if ($gd_post->{$cf['htmlvar_name']} != '' && $gd_post->{$cf['htmlvar_name']}!="0000-00-00") {
                $date_format_from = $preview ? $date_format : 'Y-m-d';
                $value = geodir_date($post_htmlvar_value, $date_format, $date_format_from); // save as sql format Y-m-d
                //$post_htmlvar_value = strpos($post_htmlvar_value, '/') !== false ? str_replace('/', '-', $post_htmlvar_value) : $post_htmlvar_value;
                //$value = date_i18n($date_format, strtotime($post_htmlvar_value));
            }else{
                return '';
            }

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fas fa-calendar" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }



            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $value;

            $html .= '</div>';

        endif;

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
function geodir_cf_text($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = __('Some demo text.','geodirectory');
    }

    $html_var = $cf['htmlvar_name'];

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

            $class = ($cf['htmlvar_name'] == 'geodir_timing') ? "geodir-i-time" : "geodir-i-text";

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = ($cf['htmlvar_name'] == 'geodir_timing') ? '<i class="fas fa-clock" aria-hidden="true"></i>' : "";
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $value = $gd_post->{$cf['htmlvar_name']};
            if ( isset( $cf['data_type'] ) && ( $cf['data_type'] == 'INT' || $cf['data_type'] == 'FLOAT' || $cf['data_type'] == 'DECIMAL' ) && isset( $cf['extra_fields'] ) && $cf['extra_fields'] ) {
                $extra_fields = stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) );

                if ( ! empty( $extra_fields ) && isset( $extra_fields['is_price'] ) && $extra_fields['is_price'] ) {
                    if ( ! ceil( $value ) > 0 ) {
						return '';// dont output blank prices
					}
                    $value = geodir_currency_format_number( $value, $cf );
                }
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon '.$class.'" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $value );

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
                $cf_option_values = geodir_string_values_to_options(stripslashes_deep($cf['option_values']), true);

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

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-radio" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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
            $field_value = isset($gd_post->{$cf['htmlvar_name']}) ? __($gd_post->{$cf['htmlvar_name']}, 'geodirectory') : '';

            if (!empty($cf['option_values'])) {
                $cf_option_values = geodir_string_values_to_options(stripslashes_deep($cf['option_values']), true);

                if (!empty($cf_option_values)) {
                    foreach ($cf_option_values as $cf_option_value) {
                        if (isset($cf_option_value['value']) && $cf_option_value['value'] == $gd_post->{$cf['htmlvar_name']}) {
                            $field_value = $cf_option_value['label']; // no longer needed here. Removed comment because it displays number instead of label if option vales set like "Good/1,Fair/2".
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

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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

            $field_values = explode(',', trim($gd_post->{$cf['htmlvar_name']}, ","));

            if(is_array($field_values)){
                $field_values = array_map('trim', $field_values);
            }

            $option_values = array();
            if (!empty($cf['option_values'])) {
                $cf_option_values = geodir_string_values_to_options(stripslashes_deep($cf['option_values']), true);

                if (!empty($cf_option_values)) {
                    foreach ($cf_option_values as $cf_option_value) {
                        if (isset($cf_option_value['value']) && in_array($cf_option_value['value'], $field_values)) {
                            $option_values[] = $cf_option_value['label'];
                        }
                    }
                }
            }

            $field_value = '';

            if (count($option_values) ) {
                $field_value .= '<ul>';

                foreach ($option_values as $val) {
                    $field_value .= '<li>' . $val . '</li>';
                }

                $field_value .= '</ul>';
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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

            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="far fa-envelope" aria-hidden="true"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $is_elementor_preview = class_exists( 'GeoDir_Elementor' ) && GeoDir_Elementor::is_elementor_view() ? true : false; // Check if elementor preview
            $email = $gd_post->{$cf['htmlvar_name']} ;
            $value = '';
            if ( ! empty( $email ) && ( $email != 'testing@example.com' ) && ( $e_split = explode( '@', $email ) ) && ! defined( 'REST_REQUEST' ) && ! $is_elementor_preview && ! wp_doing_ajax() ) {
                /**
                 * Filter email custom field name output.
                 *
                 * @since 1.5.3
                 *
                 * @param string $email The email string being output.
                 * @param array $cf Custom field variables array.
                 */
                $email_name = apply_filters( 'geodir_email_field_name_output', $email, $cf );
                $value .= "<script>document.write('<a href=\"mailto:'+'$e_split[0]' + '@' + '$e_split[1]'+'\">$email_name</a>')</script>";
            } elseif ( ! empty( $email ) && ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || $is_elementor_preview || wp_doing_ajax() ) ) {
                /**
                 * Filter email custom field name output.
                 *
                 * @since 1.5.3
                 *
                 * @param string $email The email string being output.
                 * @param array $cf Custom field variables array.
                 */
                $email_name = apply_filters( 'geodir_email_field_name_output', $email, $cf );
				$value .= "<a href='mailto:$email'>$email_name</a>";
            } else {
                $value .= $email;
            }

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-email" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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
function geodir_cf_file($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    //@todo this custom field is not working, so we need to fix it and then test
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'testing@example.com';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_file_loc_{$location}")){
        /**
         * Filter the file html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_file_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_file_var_{$html_var}")){
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
        $html = apply_filters("geodir_custom_field_output_file_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_file_key_{$cf['field_type_key']}")){
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
        $html = apply_filters("geodir_custom_field_output_file_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        $files = GeoDir_Media::get_attachments_by_type($gd_post->ID,$html_var);

        //echo '####';
        //print_r($files );
        if (!empty($files)):

            $extra_fields = !empty($cf['extra_fields']) ? stripslashes_deep(maybe_unserialize($cf['extra_fields'])) : NULL;
            $allowed_file_types = !empty($extra_fields['gd_file_types']) && is_array($extra_fields['gd_file_types']) && !in_array("*", $extra_fields['gd_file_types'] ) ? $extra_fields['gd_file_types'] : '';

            $upload_dir = wp_upload_dir();
            $upload_basedir = $upload_dir['basedir'];
            $upload_baseurl = $upload_dir['baseurl'];
            $file_paths = '';
            foreach ($files as $file) {

                //print_r($file);
                $file_path = isset($file->file) ? $file->file : '';
                $title = isset($file->title) ? $file->title : '';
                $desc = isset($file->caption) ? $file->caption : '';
                $url = $upload_baseurl.$file_path;
//echo $file_path.'###'.$title.'###'.$desc;

                //continue;

                //$file = !empty( $file ) ? geodir_file_relative_url( $file, true ) : '';

                if ( !empty( $file ) ) {
                    $image_name_arr = explode('/', $url);
                    $curr_img_dir = $image_name_arr[count($image_name_arr) - 2];
                    $filename = end($image_name_arr);
                    $img_name_arr = explode('.', $filename);

                    $arr_file_type = wp_check_filetype($filename);
                    if (empty($arr_file_type['ext']) || empty($arr_file_type['type'])) {
                        continue;
                    }

                    $uploaded_file_type = $arr_file_type['type'];
                    $uploaded_file_ext = $arr_file_type['ext'];

                    if (!empty($allowed_file_types) && !in_array($uploaded_file_ext, $allowed_file_types)) {
                        continue; // Invalid file type.
                    }

                    //$allowed_file_types = array('application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain');
                    $image_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-icon', 'image/webp');
                    $audio_file_types = array('audio/mpeg', 'audio/ogg', 'audio/mp4', 'audio/vnd.wav', 'audio/basic', 'audio/mid');

                    // If the uploaded file is image
                    if (in_array($uploaded_file_type, $image_file_types)) {
                        $file_paths .= '<div class="geodir-custom-field-file" class="clearfix">';
                        $file_paths .= '<a href="'.$url.'" data-lity>';
                        $file_paths .= '';//@todo this function needs replaced ::::::: geodir_show_image(array('src' => $file), 'thumbnail', false, false);
                        $file_paths .= geodir_get_image_tag($file);
                        $file_paths .= '</a>';
                        // $file_paths .= '<img src="'.$url.'"  />';
                        $file_paths .= '</div>';
                    }elseif (1==2 && in_array($uploaded_file_type, $audio_file_types)) {// if audio
                        $ext_path = '_' . $html_var . '_';
                        $filename = explode($ext_path, $filename);
                        $file_paths .= '<span class="gd-audio-name">'.$filename[count($filename) - 1].'</span>';
                        $file_paths .= do_shortcode('[audio src="'.$url.'" ]');
                    } else {
                        $ext_path = '_' . $html_var . '_';
                        $filename = explode($ext_path, $filename);
                        $file_paths .= '<a class="gd-meta-file" href="' . $url . '" target="_blank" data-lity title="'.esc_attr($title).'">' . $filename[count($filename) - 1] . '</a>';
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

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-file" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= $file_paths;

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_file','geodir_cf_file',10,5);



/**
 * Get the html output for the custom field: textarea
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
function geodir_cf_textarea($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam risus metus, rutrum in nunc eu, vestibulum iaculis lacus. Interdum et malesuada fames ac ante ipsum primis in faucibus. Aenean tristique arcu et eros convallis elementum. Maecenas sit amet quam eu velit euismod viverra. Etiam magna augue, mollis id nisi sit amet, eleifend sagittis tortor. Suspendisse vitae dignissim arcu, ac elementum eros. Mauris hendrerit at massa ut pellentesque.';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_textarea_loc_{$location}")){
        /**
         * Filter the textarea html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0 $output param added.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_textarea_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_textarea_var_{$html_var}")){
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
        $html = apply_filters("geodir_custom_field_output_textarea_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_textarea_key_{$cf['field_type_key']}")){
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
        $html = apply_filters("geodir_custom_field_output_textarea_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (!empty($gd_post->{$cf['htmlvar_name']})) {
			$extra_fields = isset( $cf['extra_fields'] ) && $cf['extra_fields'] != '' ? stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) ) : NULL;
            $field_icon = geodir_field_icon_proccess($cf);
            $output = geodir_field_output_process($output);
			$embed = ! empty( $extra_fields['embed'] ) || $cf['htmlvar_name'] == 'video' ? true : false;
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $max_height = !empty($output['fade']) ? absint($output['fade'])."px" : '';
            $max_height_style = $max_height ? " style='max-height:$max_height;overflow:hidden;' " : '';

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" '.$max_height_style.'>';

            if ( $output == '' || isset( $output['icon'] ) ) $html .= '<span class="geodir_post_meta_icon geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            if ( $output == '' || isset( $output['label'] ) ) $html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if ( $output == '' || isset( $output['icon'] ) ) $html .= '</span>';

            if ( $output == '' || isset( $output['value'] ) ) {
				$value = stripslashes( $gd_post->{$cf['htmlvar_name']} );

                $content = '';
                if ( $cf['htmlvar_name'] != 'post_content' ) {
					if ( isset( $output['strip'] ) ) {
                        $content =  wp_strip_all_tags( do_shortcode( wpautop( $value ) ) );
                    } else {
						if ( $embed ) { // Embed media.
							global $wp_embed;
							$value = $wp_embed->autoembed( $value );
						}
                        $content = do_shortcode( wpautop( $value ) );
                    }
				} else { // Post content
					if ( isset($output['strip'] ) ) {
                        $content = wp_strip_all_tags( apply_filters( 'the_content', $value ) );
                    } else {
                        $content = apply_filters( 'the_content', $value );
                    }
				}

                if($content){

                    // set a limit if it exists
                    if(!empty($output['limit'])){
                        $limit = absint($output['limit']);
                        $content = wp_trim_words( $content, $limit, '' );
                    }

                    $html .= $content;

//                    print_r( $output );echo '###';

                    // add read more
                    if(isset( $output['more'] )){
                        $post_id = isset($gd_post->id) ? absint($gd_post->id) : 0;
                        $more_text = empty( $output['more'] ) ? __("Read more...","geodirectory") : __($output['more'],"geodirectory");
                        $link =  get_permalink($post_id);
                        $link = $link."#".$cf['htmlvar_name'];// set the hash value
                        $link_class = !empty($output['fade']) ? 'gd-read-more-fade' : '';
                        $html .= " <a href='$link' class='gd-read-more  $link_class'>".esc_attr($more_text)."</a>";
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

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '<b>This is some bold HTML</b>';
    }

    $html_var = $cf['htmlvar_name'];

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

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= wpautop(do_shortcode(stripslashes($gd_post->{$cf['htmlvar_name']})));

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
function geodir_cf_taxonomy($html,$location,$cf,$p='',$output=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        if($cf['htmlvar_name']=='post_category'){
            $demo_tax = 'gd_placecategory';
        }elseif($cf['htmlvar_name']=='post_tags'){
            $demo_tax = 'gd_place_tags';
        }
        $demoterms = get_terms( array(
            'taxonomy' => $demo_tax,
            'hide_empty' => false,
            'number'    => 2
        ) );
        $demo_terms = '';
        if(!empty($demoterms)){
            foreach($demoterms as $demoterm){
                $demo_terms .= $demoterm->term_id.",";
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

        if($html_var=='post_category'){
            $post_taxonomy = $gd_post->post_type . 'category';
        }elseif($html_var == 'post_tags'){
            $post_taxonomy = $gd_post->post_type . '_tags';
        }else{
            $post_taxonomy = '';
        }
        if ($post_taxonomy && !empty($gd_post->{$html_var})) {

            //$post_taxonomy = $gd_post->post_type . 'category';
            $field_value = $gd_post->{$html_var};
            $links = array();
            $terms = array();
            $termsOrdered = array();
            if (!is_array($field_value)) {
                $field_value = explode(",", trim($field_value, ","));
            }

            $field_value = array_unique($field_value);

            //print_r($field_value);

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

            if ($html_value != '') {
                $field_icon = geodir_field_icon_proccess($cf);
                $output = geodir_field_output_process($output);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } else if ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

                $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

                if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-taxonomy" style="' . $field_icon . '">' . $field_icon_af;
                if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
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

        $show_street2_in_address = true;
        $show_city_in_address = true;
        $show_region_in_address = true;
        $show_country_in_address = true;
        $show_zip_in_address = true;

        if (!empty($cf['extra_fields'])) {
            $extra_fields = stripslashes_deep(unserialize($cf['extra_fields']));
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

        if ($gd_post->street) {

            $field_icon = geodir_field_icon_proccess( $cf );
            $output = geodir_field_output_process($output);
            if ( strpos( $field_icon, 'http' ) !== false ) {
                $field_icon_af = '';
            } elseif ( $field_icon == '' ) {
                $field_icon_af = '<i class="fas fa-home" aria-hidden="true"></i>';
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
            if ( isset($gd_post->street) ) {
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


            foreach($address_items as $type){
                // normal value
                $value = isset($address_fields[$type]) ? $address_fields[$type] : '';
                $address_template = str_replace('%%'.$type.'%%', $value ,$address_template);

                // value with line break
                $value_br = isset($address_fields[$type]) ? $address_fields[$type]."<br>" : '';
                $address_template = str_replace('%%'.$type.'_br%%', $value_br ,$address_template);

                // value with coman and then line break
                $value_br = isset($address_fields[$type]) ? $address_fields[$type].",<br>" : '';
                $address_template = str_replace('%%'.$type.'_brc%%', $value_br ,$address_template);
            }

            // add line breaks
            $address_template = str_replace('%%br%%', "<br>" ,$address_template);

            $address_fields = $address_template;

            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-address" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $address_fields );

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
function geodir_cf_business_hours($html,$location,$cf,$p='',$output=''){
    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    if( geodir_is_block_demo() ){
        $gd_post->{$cf['htmlvar_name']} = '["Mo 09:00-17:00","Tu 09:00-17:00","We 09:00-17:00","Th 09:00-17:00","Fr 09:00-17:00"],["UTC":"+0"]';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a location specific filter.
    if(has_filter("geodir_custom_field_output_business_hours_loc_{$location}")){
        /**
         * Filter the business hours html by location.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_loc_{$location}",$html,$cf,$output);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_business_hours_var_{$html_var}")){
        /**
         * Filter the business hours html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_var_{$html_var}",$html,$location,$cf,$output);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}")){
        /**
         * Filter the business hours html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @param string $output The output string that tells us what to output.
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}",$html,$location,$cf,$output);
    }

    // If not html then we run the standard output.
    if ( empty( $html ) ) {
        if ( ! empty( $gd_post->{$cf['htmlvar_name']} ) ) {
            $value = stripslashes_deep($gd_post->{$cf['htmlvar_name']});
            $business_hours = geodir_get_business_hours( $value );

            if ( empty( $business_hours['days'] ) ) {
                return $html;
            }
            $show_value = $business_hours['extra']['today_range'];
            $offset = isset( $business_hours['extra']['offset'] ) ? $business_hours['extra']['offset'] : '';

            if (!empty($show_value)) {
                $field_icon = geodir_field_icon_proccess($cf);
                $output = geodir_field_output_process($output);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } else if ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

                $extra_class = $location == 'owntab' || strpos($cf['css_class'], 'gd-bh-expanded') !== false ? ' gd-bh-expanded' : ' gd-bh-toggled';
                if ( ! empty( $business_hours['extra']['has_closed'] ) ) {
                    $extra_class .= ' gd-bh-closed';
                }

                $html = '<div class="geodir_post_meta gd-bh-show-field ' . $cf['css_class'] . ' geodir-field-' . $html_var . $extra_class . '" style="clear:both;">';
                $html .= '<span class="geodir-i-business_hours geodir-i-biz-hours" style="' . $field_icon . '">' . $field_icon_af . '<font></font>' . ': </span>';
                $html .= '<span class="gd-bh-expand-range" data-offset="' . geodir_gmt_offset( $offset ) . '" data-offsetsec="' . geodir_gmt_offset( $offset, false ) . '" title="' . esc_attr__( 'Expand opening hours' , 'geodirectory' ) . '"><span class="gd-bh-today-range">' . $show_value . '</span>';
                $html .= '<span class="gd-bh-expand"><i class="fas fa-caret-up" aria-hidden="true"></i><i class="fas fa-caret-down" aria-hidden="true"></i></span></span>';
                $html .= '<div class="gd-bh-open-hours">';
                foreach ( $business_hours['days'] as $day => $slots ) {
                    $class = '';
                    if ( ! empty( $slots['closed'] ) ) {
                        $class .= 'gd-bh-days-closed ';
                    }
                    $html .= '<div data-day="' . $slots['day_no'] . '" data-closed="' . $slots['closed'] . '" class="gd-bh-days-list ' . trim( $class ) . '"><div class="gd-bh-days-d">' . $slots['day_short'] . '</div><div class="gd-bh-slots">';
                    foreach ( $slots['slots'] as $i => $slot ) {
                        $attrs = '';
                        if ( ! empty( $slot['time'] ) ) {
                            $attrs .= 'data-open="' . $slot['time'][0] . '"  data-close="' . $slot['time'][1] . '"';
                        }
                        $html .= '<div ' . $attrs . ' class="gd-bh-slot"><div class="gd-bh-slot-r">' . $slot['range'] . '</div>';
                        $html .= '</div>';
                    }
                    $html .= '</div></div>';
                }
                $html .= '</div></div>';

                ###
//                $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';
//
//                if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-field-' . $html_var . $extra_class . '" style="' . $field_icon . '">' . $field_icon_af;
//                if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
//                if($output=='' || isset($output['icon']))$html .= '</span>';
//                if($output=='' || isset($output['value']))$html .= $address_fields;
//
//                $html .= '</div>';
            }
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_business_hours','geodir_cf_business_hours',10,4);

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

            if ( isset( $output['strip'] ) ) {
                $author_link = wp_strip_all_tags( $author_link );
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '">';

            if($output=='' || isset($output['icon'])) $html .= '<span class="geodir_post_meta_icon geodir-i-address" style="' . $field_icon . '">' . $field_icon_af;
            if($output=='' || isset($output['label']))$html .= (trim($cf['frontend_title'])) ? '<span class="geodir_post_meta_title" >'.__($cf['frontend_title'], 'geodirectory') . ': '.'</span>' : '';
            if($output=='' || isset($output['icon']))$html .= '</span>';
            if($output=='' || isset($output['value']))$html .= stripslashes( $author_link );

            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_author','geodir_cf_author',10,5);
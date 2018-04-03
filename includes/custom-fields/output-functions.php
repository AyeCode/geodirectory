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
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_checkbox($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_checkbox_var_{$html_var}")){
        /**
         * Filter the checkbox html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_checkbox_key_{$cf['field_type_key']}")){
        /**
         * Filter the checkbox html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_checkbox_key_{$cf['field_type_key']}",$html,$location,$cf);
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
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-checkbox" style="' . $field_icon . '">' . $field_icon_af;
            $html .= ( trim( $cf['frontend_title'] ) ) ? __( $cf['frontend_title'], 'geodirectory' ) . ': ' : '';
            $html .= '</span>' . $html_val . '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_checkbox','geodir_cf_checkbox',10,4);


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
        $fieldset_class = 'fieldset-'.sanitize_title_with_dashes($cf['frontend_title']);

        if ($field_set_start == 1) {
            $html = '';
        } else {
            $html = '<h2 class="'.$fieldset_class.'">'. __($cf['frontend_title'], 'geodirectory') . '</h2>';
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
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_url($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_url_var_{$html_var}")){
        /**
         * Filter the url html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_url_key_{$cf['field_type_key']}")){
        /**
         * Filter the url html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_url_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {

                if ($cf['name'] == 'geodir_facebook') {
                    $field_icon_af = '<i class="fa fa-facebook-square"></i>';
                } elseif ($cf['name'] == 'geodir_twitter') {
                    $field_icon_af = '<i class="fa fa-twitter-square"></i>';
                } else {
                    $field_icon_af = '<i class="fa fa-link"></i>';
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



            // all search engines that use the nofollow value exclude links that use it from their ranking calculation
            $rel = strpos($website, get_site_url()) !== false ? '' : 'rel="nofollow"';
            /**
             * Filter custom field website name.
             *
             * @since 1.0.0
             *
             * @param string $title Website Title.
             * @param string $website Website URL.
             * @param int $gd_post->ID Post ID.
             */
            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '"><span class="geodir-i-website" style="' . $field_icon . '">' . $field_icon_af . '<a href="' . $website . '" target="_blank" ' . $rel . ' ><strong>' . apply_filters('geodir_custom_field_website_name', $title, $website, $gd_post->ID) . '</strong></a></span></div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_url','geodir_cf_url',10,4);


/**
 * Get the html output for the custom field: phone
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_phone($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_phone_var_{$html_var}")){
        /**
         * Filter the phone html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_phone_key_{$cf['field_type_key']}")){
        /**
         * Filter the phone html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_phone_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fa fa-phone"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-contact" style="' . $field_icon . '">' . $field_icon_af .
                    $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '&nbsp;';
            $html .= '</span><a href="tel:' . preg_replace('/[^0-9+]/', '', $gd_post->{$cf['htmlvar_name']}) . '">' . $gd_post->{$cf['htmlvar_name']} . '</a></div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_phone','geodir_cf_phone',10,4);


/**
 * Get the html output for the custom field: time
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_time($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_time_var_{$html_var}")){
        /**
         * Filter the time html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_time_key_{$cf['field_type_key']}")){
        /**
         * Filter the time html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_time_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):

            $value = '';
            if ($gd_post->{$cf['htmlvar_name']} != '')
                //$value = date('h:i',strtotime($gd_post->{$cf['htmlvar_name']}));
                $value = date(get_option('time_format'), strtotime($gd_post->{$cf['htmlvar_name']}));

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fa fa-clock-o"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-time" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '&nbsp;';
            $html .= '</span>' . $value . '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_time','geodir_cf_time',10,4);


/**
 * Get the html output for the custom field: datepicker
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_datepicker($html,$location,$cf,$p=''){
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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_datepicker_var_{$html_var}")){
        /**
         * Filter the datepicker html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_datepicker_key_{$cf['field_type_key']}")){
        /**
         * Filter the datepicker html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_datepicker_key_{$cf['field_type_key']}",$html,$location,$cf);
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

            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fa fa-calendar"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }



            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-datepicker" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>' . $value . '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_datepicker','geodir_cf_datepicker',10,4);


/**
 * Get the html output for the custom field: text
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_text($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_text_var_{$html_var}")){
        /**
         * Filter the text html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_text_key_{$cf['field_type_key']}")){
        /**
         * Filter the text html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_text_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    

    // If not html then we run the standard output.
    if(empty($html)){

        if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != '' ):

            $class = ($cf['htmlvar_name'] == 'geodir_timing') ? "geodir-i-time" : "geodir-i-text";

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = ($cf['htmlvar_name'] == 'geodir_timing') ? '<i class="fa fa-clock-o"></i>' : "";
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="'.$class.'" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>';

            $value = $gd_post->{$cf['htmlvar_name']};
            if(isset($cf['data_type']) && ($cf['data_type']=='INT' || $cf['data_type']=='FLOAT') && isset($cf['extra_fields']) && $cf['extra_fields']){
                $extra_fields = stripslashes_deep(maybe_unserialize($cf['extra_fields']));
                if(isset($extra_fields['is_price']) && $extra_fields['is_price']){
                    if(!ceil($value) > 0){return '';}// dont output blank prices
                    $value = geodir_currency_format_number($value,$cf);
                }
            }


            $html .= $value;
            $html .= '</div>';

        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_text','geodir_cf_text',10,4);


/**
 * Get the html output for the custom field: radio
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_radio($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_radio_var_{$html_var}")){
        /**
         * Filter the radio html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_radio_key_{$cf['field_type_key']}")){
        /**
         * Filter the radio html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_radio_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        $html_val = isset($gd_post->{$cf['htmlvar_name']}) ? __($gd_post->{$cf['htmlvar_name']}, 'geodirectory') : '';
        if (isset($gd_post->{$cf['htmlvar_name']}) && $gd_post->{$cf['htmlvar_name']} != ''):

            if ($gd_post->{$cf['htmlvar_name']} == 'f' || $gd_post->{$cf['htmlvar_name']} == '0') {
                $html_val = __('No', 'geodirectory');
            } else if ($gd_post->{$cf['htmlvar_name']} == 't' || $gd_post->{$cf['htmlvar_name']} == '1') {
                $html_val = __('Yes', 'geodirectory');
            } else {
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
            }

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-radio" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>' . $html_val . '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_radio','geodir_cf_radio',10,4);


/**
 * Get the html output for the custom field: select
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 * @since 1.6.18 Fix: Custom field should display label instead of value if set in option values.
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_select($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_select_var_{$html_var}")){
        /**
         * Filter the select html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_select_key_{$cf['field_type_key']}")){
        /**
         * Filter the select html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_select_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if ($gd_post->{$cf['htmlvar_name']}):
            $field_value = __($gd_post->{$cf['htmlvar_name']}, 'geodirectory');

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
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>' . $field_value . '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_select','geodir_cf_select',10,4);


/**
 * Get the html output for the custom field: multiselect
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_multiselect($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_multiselect_var_{$html_var}")){
        /**
         * Filter the multiselect html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_multiselect_key_{$cf['field_type_key']}")){
        /**
         * Filter the multiselect html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_multiselect_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){


        if (!empty($gd_post->{$cf['htmlvar_name']})):

            if (is_array($gd_post->{$cf['htmlvar_name']})) {
                $gd_post->{$cf['htmlvar_name']} = implode(', ', $gd_post->{$cf['htmlvar_name']});
            }

            $field_icon = geodir_field_icon_proccess($cf);
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


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>';

            if (count($option_values) > 1) {
                $html .= '<ul>';

                foreach ($option_values as $val) {
                    $html .= '<li>' . $val . '</li>';
                }

                $html .= '</ul>';
            } else {
                $html .= __($gd_post->{$cf['htmlvar_name']}, 'geodirectory');
            }

            $html .= '</div>';
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_multiselect','geodir_cf_multiselect',10,4);


/**
 * Get the html output for the custom field: email
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_email($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_email_var_{$html_var}")){
        /**
         * Filter the email html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_email_key_{$cf['field_type_key']}")){
        /**
         * Filter the email html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_email_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        global $preview;
        if ($cf['htmlvar_name'] == 'geodir_email' && !(geodir_is_page('detail') || geodir_is_page('preview'))) {
            return ''; // Remove Send Enquiry | Send To Friend from listings page
        }

        $package_info = (array)geodir_post_package_info(array(), $gd_post, $gd_post->post_type);

        if ($cf['htmlvar_name'] == 'geodir_email' && ((isset($package_info['sendtofriend']) && $package_info['sendtofriend']) || $gd_post->{$cf['htmlvar_name']})) {
            global $send_to_friend;
            $send_to_friend = true;
            $b_send_inquiry = '';
            $b_sendtofriend = '';

            $html = '';
            if (!$preview) {
                $b_send_inquiry = 'b_send_inquiry';
                $b_sendtofriend = 'b_sendtofriend';
                $html = '<input type="hidden" name="geodir_popup_post_id" value="' . $gd_post->ID . '" /><div class="geodir_display_popup_forms"></div>';
            }

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '<i class="fa fa-envelope"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $html .= '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '"><span class="geodir-i-email" style="' . $field_icon . '">' . $field_icon_af;
            $seperator = '';
            if ($gd_post->{$cf['htmlvar_name']}) {
                $b_send_inquiry_url = apply_filters('b_send_inquiry_url', 'javascript:void(0);');
                $html .= '<a href="'.$b_send_inquiry_url.'" class="' . $b_send_inquiry . '" >' . SEND_INQUIRY . '</a>';
                $seperator = ' | ';
            }

            if (isset($package_info['sendtofriend']) && $package_info['sendtofriend']) {
                $b_sendtofriend_url = apply_filters('b_sendtofriend_url', 'javascript:void(0);');
                $html .= $seperator . '<a href="'.$b_sendtofriend_url.'" class="' . $b_sendtofriend . '">' . SEND_TO_FRIEND . '</a>';
            }

            $html .= '</span></div>';


            if (isset($_REQUEST['send_inquiry']) && $_REQUEST['send_inquiry'] == 'success') {
                $html .= '<p class="sucess_msg">' . SEND_INQUIRY_SUCCESS . '</p>';
            } elseif (isset($_REQUEST['sendtofrnd']) && $_REQUEST['sendtofrnd'] == 'success') {
                $html .= '<p class="sucess_msg">' . SEND_FRIEND_SUCCESS . '</p>';
            } elseif (isset($_REQUEST['emsg']) && $_REQUEST['emsg'] == 'captch') {
                $html .= '<p class="error_msg_fix">' . WRONG_CAPTCH_MSG . '</p>';
            }

            /*if(!$preview){require_once (geodir_plugin_path().'/includes/templates/popup-forms.php');}*/

        } else {

            if ($gd_post->{$cf['htmlvar_name']}) {

                $field_icon = geodir_field_icon_proccess($cf);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } elseif ($field_icon == '') {
                    $field_icon_af = '<i class="fa fa-envelope"></i>';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }


                $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-email" style="' . $field_icon . '">' . $field_icon_af;
                $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
                $html .= '</span><span class="geodir-email-address-output">';
                $email = $gd_post->{$cf['htmlvar_name']} ;
                if(!empty($e_split = explode('@',$email)) && $email!='testing@example.com'){
                    /**
                     * Filter email custom field name output.
                     *
                     * @since 1.5.3
                     *
                     * @param string $email The email string being output.
                     * @param array $cf Custom field variables array.
                     */
                    $email_name = apply_filters('geodir_email_field_name_output',$email,$cf);
                    $html .=  "<script>document.write('<a href=\"mailto:'+'$e_split[0]' + '@' + '$e_split[1]'+'\">$email_name</a>')</script>";
                }else{
                    $html .=  $email;
                }
                $html .= '</span></div>';
            }

        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_email','geodir_cf_email',10,4);


/**
 * Get the html output for the custom field: file
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_file($html,$location,$cf,$p=''){

    // check we have the post value
    if(is_numeric($p)){$gd_post = geodir_get_post_info($p);}
    else{ global $gd_post;}

    if(!is_array($cf) && $cf!=''){
        $cf = geodir_get_field_infoby('htmlvar_name', $cf, $gd_post->post_type);
        if(!$cf){return NULL;}
    }

    // Block demo content
    //@todo this custom field is not working, so we need to fix it and thentest
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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_file_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_file_var_{$html_var}")){
        /**
         * Filter the file html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_file_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_file_key_{$cf['field_type_key']}")){
        /**
         * Filter the file html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_file_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (!empty($gd_post->{$cf['htmlvar_name']})):

            $files = explode(",", $gd_post->{$cf['htmlvar_name']});
            if (!empty($files)):

                $extra_fields = !empty($cf['extra_fields']) ? stripslashes_deep(maybe_unserialize($cf['extra_fields'])) : NULL;
                $allowed_file_types = !empty($extra_fields['gd_file_types']) && is_array($extra_fields['gd_file_types']) && !in_array("*", $extra_fields['gd_file_types'] ) ? $extra_fields['gd_file_types'] : '';

                $file_paths = '';
                foreach ($files as $file) {
                    $file = !empty( $file ) ? geodir_file_relative_url( $file, true ) : '';
                    
                    if ( !empty( $file ) ) {
                        $image_name_arr = explode('/', $file);
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
                        $image_file_types = array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/x-icon');
                        $audio_file_types = array('audio/mpeg', 'audio/ogg', 'audio/mp4', 'audio/vnd.wav', 'audio/basic', 'audio/mid');

                        // If the uploaded file is image
                        if (in_array($uploaded_file_type, $image_file_types)) {
                            $file_paths .= '<div class="geodir-custom-post-gallery" class="clearfix">';
                            $file_paths .= '<a href="'.$file.'">';
                            $file_paths .= geodir_show_image(array('src' => $file), 'thumbnail', false, false);
                            $file_paths .= '</a>';
                            //$file_paths .= '<img src="'.$file.'"  />';	
                            $file_paths .= '</div>';
                        }elseif (in_array($uploaded_file_type, $audio_file_types)) {// if audio
                            $ext_path = '_' . $html_var . '_';
                            $filename = explode($ext_path, $filename);
                            $file_paths .= '<span class="gd-audio-name">'.$filename[count($filename) - 1].'</span>';
                            $file_paths .= do_shortcode('[audio src="'.$file.'" ]');
                        } else {
                            $ext_path = '_' . $html_var . '_';
                            $filename = explode($ext_path, $filename);
                            $file_paths .= '<a href="' . $file . '" target="_blank">' . $filename[count($filename) - 1] . '</a>';
                        }
                    }
                }

                $field_icon = geodir_field_icon_proccess($cf);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } elseif ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

                $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-custom-file-box geodir-field-' . $cf['htmlvar_name'] . '"><div class="geodir-i-select" style="' . $field_icon . '">' . $field_icon_af;
                $html .= '<span style="display: inline-block; vertical-align: top; padding-right: 14px;">';
                $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
                $html .= '</span>';
                $html .= $file_paths . '</div></div>';

            endif;
        endif;

    }

    return $html;
}
add_filter('geodir_custom_field_output_file','geodir_cf_file',10,4);



/**
 * Get the html output for the custom field: textarea
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_textarea($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_textarea_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_textarea_var_{$html_var}")){
        /**
         * Filter the textarea html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_textarea_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_textarea_key_{$cf['field_type_key']}")){
        /**
         * Filter the textarea html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_textarea_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (!empty($gd_post->{$cf['htmlvar_name']})) {

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }


            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>' . wpautop($gd_post->{$cf['htmlvar_name']}) . '</div>';

        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_textarea','geodir_cf_textarea',10,4);



/**
 * Get the html output for the custom field: html
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_html($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_html_var_{$html_var}")){
        /**
         * Filter the html html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_html_key_{$cf['field_type_key']}")){
        /**
         * Filter the html html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_html_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        if (!empty($gd_post->{$cf['htmlvar_name']})) {

            $field_icon = geodir_field_icon_proccess($cf);
            if (strpos($field_icon, 'http') !== false) {
                $field_icon_af = '';
            } elseif ($field_icon == '') {
                $field_icon_af = '';
            } else {
                $field_icon_af = $field_icon;
                $field_icon = '';
            }

            $html = '<div class="geodir_post_meta  ' . $cf['css_class'] . ' geodir-field-' . $cf['htmlvar_name'] . '" style="clear:both;"><span class="geodir-i-text" style="' . $field_icon . '">' . $field_icon_af;
            $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
            $html .= '</span>' . wpautop($gd_post->{$cf['htmlvar_name']}) . '</div>';

        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_html','geodir_cf_html',10,4);



/**
 * Get the html output for the custom field: taxonomy
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_taxonomy($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_taxonomy_var_{$html_var}")){
        /**
         * Filter the taxonomy html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_taxonomy_key_{$cf['field_type_key']}")){
        /**
         * Filter the taxonomy html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_taxonomy_key_{$cf['field_type_key']}",$html,$location,$cf);
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
                        $term = get_term_by('id', $term, $post_taxonomy);
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
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } else if ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

                $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $html_var . '" style="clear:both;"><span class="geodir-i-taxonomy geodir-i-category" style="' . $field_icon . '">' . $field_icon_af;
                $html .= (trim($cf['frontend_title'])) ? __($cf['frontend_title'], 'geodirectory') . ': ' : '';
                $html .= '</span> ' . $html_value . '</div>';
            }
        }

    }

    return $html;
}
add_filter('geodir_custom_field_output_tags','geodir_cf_taxonomy',10,4);
add_filter('geodir_custom_field_output_categories','geodir_cf_taxonomy',10,4);
add_filter('geodir_custom_field_output_taxonomy','geodir_cf_taxonomy',10,4);


/**
 * Get the html output for the custom field: address
 *
 * @param string $html The html to be filtered.
 * @param string $location The location to output the html.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 * @since 1.6.21 New hook added to filter address fields being displayed.
 *
 * @return string The html to output for the custom field.
 */
function geodir_cf_address($html,$location,$cf,$p=''){

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
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_address_var_{$html_var}")){
        /**
         * Filter the address html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_address_key_{$cf['field_type_key']}")){
        /**
         * Filter the address html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_output_address_key_{$cf['field_type_key']}",$html,$location,$cf);
    }

    // If not html then we run the standard output.
    if(empty($html)){

        global $preview;
        $html_var = $cf['htmlvar_name'] . '_address';

        if ($cf['extra_fields']) {

            $extra_fields = stripslashes_deep(unserialize($cf['extra_fields']));

            $addition_fields = '';

            if (!empty($extra_fields)) {

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

        //print_r($gd_post);

        if ($gd_post->street) {

            $field_icon = geodir_field_icon_proccess( $cf );
            if ( strpos( $field_icon, 'http' ) !== false ) {
                $field_icon_af = '';
            } elseif ( $field_icon == '' ) {
                $field_icon_af = '<i class="fa fa-home"></i>';
            } else {
                $field_icon_af = $field_icon;
                $field_icon    = '';
            }
            
            $html = '<div class="geodir_post_meta ' . $cf['css_class'] . ' geodir-field-' . $html_var . '" style="clear:both;"  itemscope itemtype="https://schema.org/PostalAddress">';
            $html .= '<span class="geodir-i-location" style="' . $field_icon . '">' . $field_icon_af;
            $html .= ( trim( $cf['frontend_title'] ) ) ? __( $cf['frontend_title'], 'geodirectory' ) . ': ' : '&nbsp;';
            $html .= '</span>';
            
            $address_fields = array();

            if ( isset($gd_post->street) ) {
                $address_fields['street'] = '<span itemprop="streetAddress">' . $gd_post->street . '</span>';
            }
            if ($show_city_in_address && isset( $gd_post->city ) && $gd_post->city ) {
                $address_fields['city'] = '<span itemprop="addressLocality">' . $gd_post->city. '</span>';
            }
            if ($show_region_in_address && isset( $gd_post->region ) && $gd_post->region ) {
                $address_fields['region'] = '<span itemprop="addressRegion">' . $gd_post->region . '</span>';
            }
            if ($show_zip_in_address && isset( $gd_post->zip ) && $gd_post->zip ) {
                $address_fields['zip'] = '<span itemprop="postalCode">' . $gd_post->zip . '</span>';
            }
            if ($show_country_in_address && isset( $gd_post->country ) && $gd_post->country ) {
                $address_fields['country'] = '<span itemprop="addressCountry">' . __( $gd_post->country, 'geodirectory' ) . '</span>';
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
            
            if (!empty($address_fields) && is_array($address_fields)) {
                $address_fields = array_values($address_fields);
                $html .= implode('<br>', $address_fields);
            }
            
            $html .= '</div>';
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_address','geodir_cf_address',10,4);



/**
 * Filter the business hours custom field output to show a link.
 *
 * @param string $html The html to be output.
 * @param string $location The location name of the output location.
 * @param object $cf The custom field object info.
 *
 * @since 2.0.0
 * @return string The html to output.
 */
function geodir_cf_business_hours($html,$location,$cf,$p=''){
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
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_loc_{$location}",$html,$cf);
    }

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_output_business_hours_var_{$html_var}")){
        /**
         * Filter the business hours html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_var_{$html_var}",$html,$location,$cf);
    }

    // Check if there is a custom field key specific filter.
    if(has_filter("geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}")){
        /**
         * Filter the business hours html by field type key.
         *
         * @param string $html The html to filter.
         * @param string $location The location to output the html.
         * @param array $cf The custom field array.
         * @since 2.0.0
         */
        $html = apply_filters("geodir_custom_field_output_business_hours_key_{$cf['field_type_key']}",$html,$location,$cf);
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

            if (!empty($show_value)) {
                $field_icon = geodir_field_icon_proccess($cf);
                if (strpos($field_icon, 'http') !== false) {
                    $field_icon_af = '';
                } else if ($field_icon == '') {
                    $field_icon_af = '';
                } else {
                    $field_icon_af = $field_icon;
                    $field_icon = '';
                }

				$extra_class = $location == 'owntab' ? ' gd-bh-expanded' : ' gd-bh-toggled';
				if ( ! empty( $business_hours['extra']['has_closed'] ) ) {
					$extra_class .= ' gd-bh-closed';
				}
				
                $html = '<div class="geodir_post_meta gd-bh-show-field ' . $cf['css_class'] . ' geodir-field-' . $html_var . $extra_class . '" style="clear:both;">';
				$html .= '<span class="geodir-i-business_hours geodir-i-biz-hours" style="' . $field_icon . '">' . $field_icon_af . '<font></font>' . ': </span>';
                $html .= '<span class="gd-bh-expand-range" data-offset="' . geodir_gmt_offset() . '" data-offsetsec="' . ( geodir_gmt_offset( false ) * HOUR_IN_SECONDS ) . '" title="' . esc_attr__( 'Expand opening hours' , 'geodirectory' ) . '"><span class="gd-bh-today-range">' . $show_value . '</span>';
				$html .= '<span class="gd-bh-expand"><i class="fa fa-caret-up"></i><i class="fa fa-caret-down"></i></span></span>';
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
            }
        }
    }

    return $html;
}
add_filter('geodir_custom_field_output_business_hours','geodir_cf_business_hours',10,4);

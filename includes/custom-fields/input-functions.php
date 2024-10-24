<?php
/**
 * Custom fields input functions
 *
 * @since 1.6.6
 * @package GeoDirectory
 */



/**
 * Get the html input for the custom field: fieldset
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_fieldset($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_fieldset_{$html_var}")){
        /**
         * Filter the fieldset html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_fieldset_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        ?>
        <h5 id="geodir_fieldset_<?php echo (int) $cf['id']; ?>" class="geodir-fieldset-row"
            gd-fieldset="<?php echo (int) $cf['id']; ?>"><?php echo __( $cf['frontend_title'], 'geodirectory' ); ?>
            <?php if ( $cf['desc'] != '' ) {
                echo '<small>( ' . __( $cf['desc'], 'geodirectory' ) . ' )</small>';
            } ?></h5>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_fieldset','geodir_cfi_fieldset',10,2);



/**
 * Get the html input for the custom field: text
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_text($html,$cf){

    // don't show title in admin
    if(is_admin() && isset($cf['htmlvar_name']) && $cf['htmlvar_name']=='post_title'){return '';}

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_text_{$html_var}")){
        /**
         * Filter the text html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_text_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;

        $value = geodir_get_cf_value($cf);
        $type = $cf['type'];
        $fix_html5_decimals = '';
        //number and float validation $validation_pattern
        if(isset($cf['data_type']) && $cf['data_type']=='INT'){$type = 'number'; $fix_html5_decimals =' lang="EN" ';}
        elseif(isset($cf['data_type']) && $cf['data_type']=='FLOAT'){$type = 'float';$fix_html5_decimals =' lang="EN" ';}

        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $validation = ' pattern="'.$cf['validation_pattern'].'" ';
        }else{$validation='';}

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $validation_msg = 'title="'.$cf['validation_msg'].'"';
        }else{$validation_msg='';}

        $frontend_title = __( $cf['frontend_title'], 'geodirectory' );
        if ( $cf['name'] == 'service_distance' ) {
            $type = 'number';
            $fix_html5_decimals =' lang="EN" ';

            if ( geodir_get_option( 'search_distance_long' ) == 'km' ) {
                $frontend_title .= ' ' . __( '(km)', 'geodirectory');
            } else {
                $frontend_title .= ' ' . __( '(miles)', 'geodirectory');
            }
        }
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <input field_type="<?php echo $type;?>" name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                   <?php echo $fix_html5_decimals;
                   if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; }
                   ?>
                   value="<?php echo esc_attr(stripslashes($value));?>" type="<?php echo $type;?>" class="geodir_textfield" <?php echo $validation;echo $validation_msg;?> />
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_text','geodir_cfi_text',10,2);


/**
 * Get the html input for the custom field: email
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_email($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_email_{$html_var}")){
        /**
         * Filter the email html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_email_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if ($value == $cf['default']) {
            $value = '';
        }

        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $validation = ' pattern="'.$cf['validation_pattern'].'" ';
        }else{$validation='';}

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $validation_msg = 'title="'.$cf['validation_msg'].'"';
        }else{$validation_msg='';}
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <input field_type="<?php echo $cf['type'];?>" name="<?php  echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                   <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                   value="<?php echo esc_attr(stripslashes($value));?>" type="email" class="geodir_textfield" <?php echo $validation;echo $validation_msg;?> />
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_email','geodir_cfi_email',10,2);



/**
 * Get the html input for the custom field: phone
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_phone($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_phone_{$html_var}")){
        /**
         * Filter the phone html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_phone_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if ($value == $cf['default']) {
            $value = '';
        }

        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $validation = ' pattern="'.$cf['validation_pattern'].'" ';
        }else{$validation='';}

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $validation_msg = 'title="'.$cf['validation_msg'].'"';
        }else{$validation_msg='';}
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <input field_type="<?php echo $cf['type'];?>" name="<?php  echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                   value="<?php echo esc_attr(stripslashes($value));?>" type="tel" class="geodir_textfield" <?php echo $validation;echo $validation_msg;?> />
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_phone','geodir_cfi_phone',10,2);



/**
 * Get the html input for the custom field: url
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_url($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_url_{$html_var}")){
        /**
         * Filter the url html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_url_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if ($value == $cf['default']) {
            $value = '';
        }
        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $validation = ' pattern="'.$cf['validation_pattern'].'" ';
        }else{$validation='';}

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $validation_msg = __( $cf['validation_msg'], 'geodirectory' );
        }else{$validation_msg = __('Please enter a valid URL including http://', 'geodirectory');}
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <input field_type="<?php echo $cf['type'];?>" name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                   value="<?php echo esc_attr(stripslashes($value));?>" type="url" class="geodir_textfield"
                   oninvalid="setCustomValidity('<?php echo esc_attr($validation_msg); ?>')"
                   onchange="try{setCustomValidity('')}catch(e){}"
                <?php echo $validation;?>
            />
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_url','geodir_cfi_url',10,2);


/**
 * Get the html input for the custom field: radio
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_radio($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_radio_{$html_var}")){
        /**
         * Filter the radio html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_radio_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {
        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        ?>
        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label>
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <?php if ($cf['option_values']) {
                $option_values = geodir_string_to_options($cf['option_values'], true);

                if (!empty($option_values)) {
                    foreach ($option_values as $option_value) {
                        if (empty($option_value['optgroup'])) {
                            ?>
                            <span class="gd-radios" role="radio"><input name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>" <?php checked(stripslashes($value), $option_value['value']);?> value="<?php echo esc_attr($option_value['value']); ?>" class="gd-checkbox" field_type="<?php echo $cf['type'];?>" type="radio" aria-label="<?php esc_attr_e( $option_value['label'] ); ?>" /><?php echo $option_value['label']; ?></span>
                            <?php
                        }
                    }
                }
            }
            ?>
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();

    }


    return $html;
}
add_filter('geodir_custom_field_input_radio','geodir_cfi_radio',10,2);


/**
 * Get the html input for the custom field: checkbox
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_checkbox($html,$cf){

    // check it its a terms and conditions in admin area
    if(is_admin() && isset($cf['field_type_key']) && $cf['field_type_key']=='terms_conditions'){ return $html;}

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_checkbox_{$html_var}")){
        /**
         * Filter the checkbox html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_checkbox_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        // Set default checked.
        if ( $value === '' && $cf['default'] ) {
            $value = '1';
        }
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <?php if ($value != '1') {
                $value = '0';
            }?>
            <input type="hidden" name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>" value="<?php echo esc_attr($value);?>"/>
            <input  <?php if ($value == '1') {
                echo 'checked="checked"';
            }?>  value="1" class="gd-checkbox" field_type="<?php echo $cf['type'];?>" type="checkbox"
                 onchange="if(this.checked){jQuery('#<?php echo $cf['name'];?>').val('1');} else{ jQuery('#<?php echo $cf['name'];?>').val('0');}"/>
            <?php
            if(isset($cf['field_type_key']) && $cf['field_type_key']=='terms_conditions'){
                $tc = geodir_terms_and_conditions_page_id();
                $tc_link = get_permalink($tc);
                echo "<a href='$tc_link' target='_blank'>".__($cf['desc'], 'geodirectory')." <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\"></i></a>";
            }else{ ?>
               <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
               <?php
            }
            ?>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_checkbox','geodir_cfi_checkbox',10,2);


/**
 * Get the html input for the custom field: textarea
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_textarea($html,$cf){

    // don't show title in admin
    if(is_admin() && isset($cf['htmlvar_name']) && $cf['htmlvar_name']=='post_content'){return '';}

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_textarea_{$html_var}")){
        /**
         * Filter the textarea html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_textarea_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        $extra_fields = maybe_unserialize($cf['extra_fields']);
		$html_editor = ! empty( $extra_fields['advanced_editor'] );
        ?>

        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label><?php


            if ( apply_filters( 'geodir_custom_field_allow_html_editor', $html_editor, $cf ) ) {

                $editor_settings = array('media_buttons' => false, 'textarea_rows' => 10);?>

            <div class="editor" field_id="<?php echo $cf['name'];?>" field_type="editor">
                <?php wp_editor(stripslashes($value), $cf['name'], $editor_settings); ?>
                </div><?php

            } else {
				$attributes = apply_filters( 'geodir_cfi_textarea_attributes', array(), $cf );
				$attributes = is_array( $attributes ) && ! empty( $attributes ) ? implode( ' ', $attributes ) : '';
                ?><textarea field_type="<?php echo $cf['type'];?>" class="geodir_textarea" name="<?php echo $cf['name'];?>"
                <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                            id="<?php echo $cf['name'];?>" <?php echo $attributes; ?>><?php echo stripslashes($value);?></textarea><?php

            }?>


            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_textarea','geodir_cfi_textarea',10,2);


/**
 * Get the html input for the custom field: select
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_select($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_select_{$html_var}")){
        /**
         * Filter the select html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_select_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);
		$frontend_title = __($cf['frontend_title'], 'geodirectory');
		$placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : '';
        ?>
        <div id="<?php echo $cf['name'];?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row geodir_custom_fields clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <?php
            $option_values_arr = geodir_string_to_options($cf['option_values'], true);
            $select_options = '';
            if (!empty($option_values_arr)) {
                foreach ($option_values_arr as $key => $option_row) {
					if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
                        $option_label = isset($option_row['label']) ? $option_row['label'] : '';

                        $select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
                    } else {
                        $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                        $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                        $selected = selected($option_value,stripslashes($value), false);

                        $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                    }

					if ( $key == 0 && empty( $option_row['optgroup'] ) && ! empty( $option_label ) && isset( $option_row['value'] ) && $option_row['value'] === '' ) {
						$placeholder = $option_label;
					}
                }
            }

			if ( empty( $placeholder ) ) {
				$placeholder = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), $frontend_title );
			}
            ?>
            <select field_type="<?php echo $cf['type'];?>" name="<?php echo $cf['name'];?>" id="<?php echo $cf['name'];?>"
                    class="geodir_textfield textfield_x geodir-select"
                    data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
                    option-ajaxchosen="false" data-allow_clear="true"><?php echo $select_options;?></select>
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_select','geodir_cfi_select',10,2);


/**
 * Get the html input for the custom field: multiselect
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_multiselect($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_multiselect_{$html_var}")){
        /**
         * Filter the multiselect html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_multiselect_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        $frontend_title = __( $cf['frontend_title'], 'geodirectory' );
        $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), $frontend_title );
        $extra_fields = !empty($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : NULL;
        $multi_display = !empty($extra_fields['multi_display_type']) ? $extra_fields['multi_display_type'] : 'select';
        ?>
        <div id="<?php echo $cf['name']; ?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field'; ?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>'; ?>
            </label>
            <input type="hidden" name="<?php echo $cf['name']; ?>" value=""/>
            <?php if ($multi_display == 'select') { ?>
            <div class="geodir_multiselect_list">
                <select field_type="<?php echo $cf['type']; ?>" name="<?php echo $cf['name']; ?>[]" id="<?php echo $cf['name']; ?>"
                        multiple="multiple" class="geodir_textfield textfield_x geodir-select"
                        data-placeholder="<?php echo esc_attr( $placeholder ); ?>"
                        option-ajaxchosen="false" data-allow_clear="true">
                    <?php
                    } else {
                        echo '<ul class="gd_multi_choice gd-ios-scrollbars">';
                    }

                    $option_values_arr = geodir_string_to_options($cf['option_values'], true);
                    $select_options = '';
                    if (!empty($option_values_arr)) {
                        foreach ($option_values_arr as $option_row) {
                            if (isset($option_row['optgroup']) && ($option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end')) {
                                $option_label = isset($option_row['label']) ? $option_row['label'] : '';

                                if ($multi_display == 'select') {
                                    $select_options .= $option_row['optgroup'] == 'start' ? '<optgroup label="' . esc_attr($option_label) . '">' : '</optgroup>';
                                } else {
                                    $select_options .= $option_row['optgroup'] == 'start' ? '<li>' . $option_label . '</li>' : '';
                                }
                            } else {
                                if (!is_array($value) && $value != '') {
                                    $value = trim($value);
                                }

                                $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                                $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                                $selected = $option_value == $value ? 'selected="selected"' : '';
                                $selected = '';
                                $checked = '';

                                if ((!is_array($value) && trim($value) != '') || (is_array($value) && !empty($value))) {
                                    if (!is_array($value)) {
                                        $value_array = explode(',', $value);
                                    } else {
                                        $value_array = $value;
                                    }

                                    $value_array = stripslashes_deep($value_array);

                                    if (is_array($value_array)) {
                                        $value_array = array_map('trim', $value_array);

                                        if (in_array($option_value, $value_array)) {
                                            $selected = 'selected="selected"';
                                            $checked = 'checked="checked"';
                                        }
                                    }
                                }

                                if ($multi_display == 'select') {
                                    $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                                } else {
                                    $select_options .= '<li><input name="' . $cf['name'] . '[]" ' . $checked . ' value="' . esc_attr($option_value) . '" class="gd-' . $multi_display . '" field_type="' . $multi_display . '" type="' . $multi_display . '" />&nbsp;' . $option_label . ' </li>';
                                }
                            }
                        }
                    }
                    echo $select_options;

                    if ($multi_display == 'select') { ?></select></div>
        <?php } else { ?></ul><?php } ?>
            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory'); ?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_multiselect','geodir_cfi_multiselect',10,2);


/**
 * Get the html input for the custom field: html
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_html($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_html_{$html_var}")){
        /**
         * Filter the html html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_html_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);
        ?>

        <div id="<?php echo $cf['name']; ?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field'; ?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>'; ?>
            </label>

            <?php $editor_settings = array('media_buttons' => false, 'textarea_rows' => 10); ?>

            <div class="editor" field_id="<?php echo $cf['name']; ?>" field_type="editor">
                <?php wp_editor(stripslashes($value), $cf['name'], $editor_settings); ?>
            </div>

            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory'); ?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>

        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_html','geodir_cfi_html',10,2);



/**
 * Get the html input for the custom field: datepicker
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_datepicker($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_datepicker_{$html_var}")){
        /**
         * Filter the datepicker html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_datepicker_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

		$name = $cf['name'];
        $extra_fields = ! empty( $cf['extra_fields'] ) ? maybe_unserialize( $cf['extra_fields'] ) : NULL;
        $date_format = ! empty( $extra_fields['date_format'] ) ? $extra_fields['date_format'] : 'yy-mm-dd';
        $jquery_date_format = $date_format;

        // check if we need to change the format or not
        $date_format_len = strlen(str_replace(' ', '', $date_format));
        if($date_format_len>5){// if greater then 5 then it's the old style format.

            $search = array('dd','d','DD','mm','m','MM','yy'); //jQuery UI datepicker format
            $replace = array('d','j','l','m','n','F','Y');//PHP date format

            $date_format = str_replace($search, $replace, $date_format);
        }else{
            $jquery_date_format = geodir_date_format_php_to_jqueryui( $jquery_date_format );
        }

        if($value=='0000-00-00'){$value='';}//if date not set, then mark it empty
        if($value && !isset($_REQUEST['backandedit'])) {
            //$time = strtotime($value);
            //$value = date_i18n($date_format, $time);
        }
        $value = geodir_date($value, 'Y-m-d', $date_format);

        ?>
        <script type="text/javascript">
            jQuery(function() {
                jQuery("#<?php echo $cf['name'];?>_show").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: "<?php echo $jquery_date_format;?>",
                    altFormat: "yy-mm-dd",
                    altField: "#<?php echo $cf['name'];?>",
                    onClose: function(dateText, inst) {
                        if(dateText == '') {
                            jQuery(inst.settings["altField"]).val('');
                        }
                    }<?php
                    // Check for default value date range
                    if ( ! empty( $extra_fields['date_range'] ) ) {
                       echo ',yearRange: "' . esc_attr( $extra_fields['date_range'] ) . '"';
                    }
                    /**
                     * Used to add extra option to datepicker per custom field.
                     *
                     * @since 1.5.7
                     * @param string $name The custom field name.
                     */
                    echo apply_filters( "gd_datepicker_extra_{$name}", '' ); ?>
                });
                <?php if ( ! empty( $value ) ) { ?>
                jQuery("#<?php echo $name;?>_show").datepicker("setDate", '<?php echo $value;?>');
                <?php } ?>
                jQuery("input#<?php echo $name;?>_show").on('change', function(e) {
                    if (!jQuery(this).val()) {
                        jQuery("input#<?php echo $cf['name'];?>").val('');
                    }
                });
            });
        </script>
        <div id="<?php echo $name;?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $name ); ?>">

                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>

            <input field_type="<?php echo $cf['type'];?>" name="<?php echo $name;?>_show" id="<?php echo $name;?>_show"
                <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                   value="<?php echo esc_attr($value);?>" type="text" class="geodir_textfield"/>
            <input type="hidden" name="<?php echo $name;?>" id="<?php echo $name;?>">

            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_datepicker','geodir_cfi_datepicker',10,2);


/**
 * Get the html input for the custom field: time
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_time($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_time_{$html_var}")){
        /**
         * Filter the time html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_time_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        $name = $cf['name'];

        if ($value != '')
            $value = date('H:i', strtotime($value));
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {

                jQuery('#<?php echo $name;?>').timepicker({
                    showPeriod: true,
                    showLeadingZero: true,
                    showPeriod: true,
                });
            });
        </script>
        <div id="<?php echo $name;?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $name ); ?>">

                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>';?>
            </label>
            <input readonly="readonly" field_type="<?php echo $cf['type'];?>" name="<?php echo $name;?>"
                <?php if(!empty($cf['placeholder_value'])){ echo 'placeholder="'.esc_html__( $cf['placeholder_value'], 'geodirectory').'"'; } ?>
                   id="<?php echo $name;?>" value="<?php echo esc_attr($value);?>" type="text" class="geodir_textfield"/>

            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory');?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_time','geodir_cfi_time',10,2);


/**
 * Get the html input for the custom field: address
 *
 * @global null|int $mapzoom Map zoom level.
 * @global bool $gd_move_inline_script Move JavaScript to inline script.
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_address( $html, $cf ) {
    global $mapzoom, $gd_move_inline_script;

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_address_{$html_var}")){
        /**
         * Filter the address html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_address_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if ( empty( $html ) ) {
        global $post, $gd_post, $geodirectory;

        if ( empty( $gd_post ) ) {
            $gd_post = geodir_get_post_info( $post->ID );
        }

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);
        $name = $cf['name'];
        $type = $cf['type'];
        $frontend_desc = $cf['desc'];
        $is_required = $cf['is_required'];
        $required_msg = $cf['required_msg'];
        $frontend_title = $cf['frontend_title'];
        $is_admin = $cf['for_admin_use'];
        $extra_fields = stripslashes_deep(maybe_unserialize($cf['extra_fields']));
        $prefix = $name . '_';

        // steet2
        if(!isset($extra_fields['street2_lable'])){$extra_fields['street2_lable'] = '';}

        ($frontend_title != '') ? $address_title = $frontend_title : $address_title = geodir_ucwords($prefix . ' street');
        ($extra_fields['street2_lable'] != '') ? $street2_title = $extra_fields['street2_lable'] : $zip_title = geodir_ucwords($prefix . ' street2');
        ($extra_fields['zip_lable'] != '') ? $zip_title = $extra_fields['zip_lable'] : $zip_title = geodir_ucwords($prefix . ' zip/post code ');
        ($extra_fields['map_lable'] != '') ? $map_title = $extra_fields['map_lable'] : $map_title = geodir_ucwords('set address on map');
        ($extra_fields['mapview_lable'] != '') ? $mapview_title = $extra_fields['mapview_lable'] : $mapview_title = geodir_ucwords($prefix . ' mapview');

        $street  = $gd_post->street;
        $street2 = isset( $gd_post->street2 ) ? $gd_post->street2 : '';
        $zip     = isset( $gd_post->zip ) ? $gd_post->zip : '';
        $lat     = isset( $gd_post->latitude) ? $gd_post->latitude : '';
        $lng     = isset( $gd_post->longitude) ? $gd_post->longitude : '';
        $mapview = isset( $gd_post->mapview ) ? $gd_post->mapview : '';
        $post_mapzoom = isset( $gd_post->mapzoom ) ? $gd_post->mapzoom : '';

        // Map zoom
        if ( ! empty( $post_mapzoom ) ) {
            $_mapzoom = absint( $post_mapzoom );
        } elseif ( ! empty( $_REQUEST['mapzoom'] ) ) {
            $_mapzoom = absint( $_REQUEST['mapzoom'] );
        } else {
            $_mapzoom = 0;
        }

        if ( ! empty( $_mapzoom ) && $_mapzoom > 0 && $_mapzoom < 21 ) {
            $mapzoom = $_mapzoom;
        } else {
            $mapzoom = 12; // Default zoom
        }

        /**
         * Filter add listing page map zoom.
         *
         * @since 2.0.0.94
         *
         * @param int $mapzoom Map zoom level.
         * @param object $gd_post The GeoDirectory post object.
         */
        $mapzoom = apply_filters( 'geodir_add_listing_page_map_zoom', $mapzoom, $gd_post );

        $location = $geodirectory->location->get_default_location();
        if (empty($city)) $city = isset($location->city) ? $location->city : '';
        if (empty($region)) $region = isset($location->region) ? $location->region : '';
        if (empty($country)) $country = isset($location->country) ? $location->country : '';

        $lat_lng_blank = false;
        if (empty($lat) && empty($lng)) {
            $lat_lng_blank = true;
        }

        if ( empty( $lat ) ) {
            $lat = ! empty( $location->latitude ) ? $location->latitude : ( isset( $location->city_latitude ) ? $location->city_latitude : '' );
        }

        if ( empty( $lng ) ) {
            $lng = ! empty( $location->longitude ) ? $location->longitude : ( isset( $location->city_longitude ) ? $location->city_longitude : '' );
        }

        /**
         * Filter the default latitude.
         *
         * @since 1.0.0
         *
         * @param float $lat Default latitude.
         * @param bool $is_admin For admin use only?.
         */
        $lat = apply_filters('geodir_default_latitude', $lat, $is_admin);
        /**
         * Filter the default longitude.
         *
         * @since 1.0.0
         *
         * @param float $lat Default longitude.
         * @param bool $is_admin For admin use only?.
         */
        $lng = apply_filters('geodir_default_longitude', $lng, $is_admin);

        $locate_me = !empty($extra_fields['show_map']) && GeoDir_Maps::active_map() != 'none' ? true : false;
        $locate_me_class = $locate_me ? ' gd-form-control' : '';
        $placeholder = $cf['placeholder_value'] != '' ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Enter a location', 'geodirectory' );
        ?>
        <div id="geodir_<?php echo $prefix . 'street';?>_row"
             class="<?php if ($is_required) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $prefix . 'street' ); ?>"><?php _e($address_title, 'geodirectory'); ?> <?php if ($is_required) echo '<span>*</span>';?></label>
            <?php if ($locate_me) { ?>
            <div class="gd-input-group gd-locate-me">
            <?php }
            // NOTE autocomplete="new-password" seems to be the only way to disable chrome autofill and others.
            ?>
                <input autocomplete="new-password" type="text" field_type="<?php echo $type;?>" name="<?php echo 'street';?>" id="<?php echo $prefix . 'street';?>" class="geodir_textfield<?php echo $locate_me_class;?>" value="<?php echo esc_attr(stripslashes($street)); ?>" <?php if(!empty($placeholder)){ echo 'placeholder="' . esc_html( $placeholder ) . '"'; } ?> />
                <?php if ($locate_me) { ?>
                <span class="gd-locate-me-btn gd-input-group-addon" title="<?php esc_attr_e('My location', 'geodirectory'); ?>"><i class="fas fa-crosshairs fa-fw" aria-hidden="true"></i></span>
            </div>
            <?php } ?>
            <span class="geodir_message_note"><?php _e($frontend_desc, 'geodirectory');?></span>
            <?php if ($is_required) { ?>
                <span class="geodir_message_error"><?php _e($required_msg, 'geodirectory'); ?></span>
            <?php } ?>
        </div>
        <?php
        if (isset($extra_fields['show_street2']) && $extra_fields['show_street2']) { ?>
            <div id="geodir_<?php echo $prefix . 'street2'; ?>_row"
                 class="geodir_form_row clearfix gd-fieldset-details">
                <label for="<?php echo esc_attr( $prefix . 'street2' ); ?>">
                    <?php _e($street2_title, 'geodirectory'); ?>
                </label>
                <input type="text" field_type="<?php echo $type; ?>" name="<?php echo 'street2'; ?>"
                       id="<?php echo $prefix . 'street2'; ?>" class="geodir_textfield autofill"
                       value="<?php echo esc_attr(stripslashes($street2)); ?>"/>
                <span class="geodir_message_note"><?php echo sprintf( __('Please enter listing %s', 'geodirectory'), __($street2_title, 'geodirectory') );?></span>
            </div>
        <?php
        }

        /**
         * Called after the address input on the add listings.
         *
         * This is used by the location manage to add further locations info etc.
         *
         * @since 1.0.0
         * @param array $cf The array of setting for the custom field. {@see geodir_custom_field_save()}.
         */
        do_action('geodir_address_extra_listing_fields', $cf);

        if (isset($extra_fields['show_zip']) && $extra_fields['show_zip']) { ?>
            <div id="geodir_<?php echo $prefix . 'zip'; ?>_row"
                 class="<?php echo ( ! empty( $extra_fields['zip_required'] ) ? 'required_field ' : '' ); ?>geodir_form_row clearfix gd-fieldset-details">
                <label for="<?php echo esc_attr( $prefix . 'zip' ); ?>">
                    <?php _e($zip_title, 'geodirectory'); ?> <?php echo ( ! empty( $extra_fields['zip_required'] ) ? '<span>*</span>' : '' ); ?>
                </label>
                <input type="text" field_type="<?php echo $type; ?>" name="<?php echo 'zip'; ?>" id="<?php echo $prefix . 'zip'; ?>" class="geodir_textfield autofill" value="<?php echo esc_attr(stripslashes($zip)); ?>"/>
                <span class="geodir_message_note"><?php echo sprintf( __('Please enter listing %s', 'geodirectory'), __($zip_title, 'geodirectory') );?></span>
                <?php echo ( ! empty( $extra_fields['zip_required'] ) ? '<span class="geodir_message_error">' . __( 'Zip/Post Code is required!', 'geodirectory' ) . '</span>' : '' ); ?>
            </div>
        <?php } ?>

        <?php  if (isset($extra_fields['show_map']) && $extra_fields['show_map']) { ?>
            <div id="geodir_<?php echo $prefix . 'map'; ?>_row" class="geodir_form_row clearfix gd-fieldset-details">
                <?php
                if ( geodir_core_multi_city() ) {
                    add_filter( 'geodir_add_listing_map_restrict', '__return_false' );
                }

				/**
				 * Move add listing JavaScript to inline script.
				 *
				 * @since 2.2.14
				 *
				 * @param bool $gd_move_inline_script Whether to move inline .
				 */
				$gd_move_inline_script = apply_filters( 'geodir_add_listing_move_inline_script', true );

                /**
                 * Contains add listing page map functions.
                 *
                 * @since 1.0.0
                 */
                include( GEODIRECTORY_PLUGIN_DIR . 'templates/map.php' );
                if ($lat_lng_blank) {
                    $lat = '';
                    $lng = '';
                }
                ?>
                <span class="geodir_message_note"><?php echo stripslashes( __( 'Click on "Set Address on Map" and then you can also drag pinpoint to locate the correct address', 'geodirectory' ) ); ?></span>
            </div>
            <?php
            /* show lat lng */
            $style_latlng = ((isset($extra_fields['show_latlng']) && $extra_fields['show_latlng']) || is_admin()) ? '' : 'style="display:none"'; ?>
            <div id="geodir_<?php echo $prefix . 'latitude'; ?>_row"
                 class="<?php if ($is_required) echo 'required_field'; ?> geodir_form_row clearfix gd-fieldset-details" <?php echo $style_latlng; ?>>
                <label for="<?php echo esc_attr( $prefix . 'latitude' ); ?>">
                    <?php _e( 'Address Latitude', 'geodirectory' ); ?>
                    <?php if ($is_required) echo '<span>*</span>'; ?>
                </label>
                <input type="number" field_type="<?php echo $type; ?>" name="<?php echo 'latitude'; ?>" id="<?php echo $prefix . 'latitude'; ?>" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($lat)); ?>" size="25" min="-90" max="90" step="any" lang='EN' />
                <span class="geodir_message_note"><?php _e( 'Please enter latitude for google map perfection. eg. : <b>39.955823048131286</b>', 'geodirectory' ); ?></span>
                <?php if ($is_required) { ?>
                    <span class="geodir_message_error"><?php _e($required_msg, 'geodirectory'); ?></span>
                <?php } ?>
            </div>

            <div id="geodir_<?php echo $prefix . 'longitude'; ?>_row"
                 class="<?php if ($is_required) echo 'required_field'; ?> geodir_form_row clearfix gd-fieldset-details" <?php echo $style_latlng; ?>>
                <label for="<?php echo esc_attr( $prefix . 'longitude' ); ?>">
                    <?php _e( 'Address Longitude', 'geodirectory' ); ?>
                    <?php if ($is_required) echo '<span>*</span>'; ?>
                </label>
                <input type="number" field_type="<?php echo $type; ?>" name="<?php echo 'longitude'; ?>"
                       id="<?php echo $prefix . 'longitude'; ?>" class="geodir_textfield"
                       value="<?php echo esc_attr(stripslashes($lng)); ?>" size="25"
                       min="-180" max="180" step="any" lang='EN'
                />
                <span class="geodir_message_note"><?php _e( 'Please enter longitude for google map perfection. eg. : <b>-75.14408111572266</b>', 'geodirectory' ); ?></span>
                <?php if ($is_required) { ?>
                    <span class="geodir_message_error"><?php _e($required_msg, 'geodirectory'); ?></span>
                <?php } ?>
            </div>
        <?php } ?>

        <?php if (isset($extra_fields['show_mapview']) && $extra_fields['show_mapview']) { ?>
            <div id="geodir_<?php echo $prefix . 'mapview'; ?>_row" class="geodir_form_row clearfix gd-fieldset-details">
                <label for="<?php echo esc_attr( $prefix . 'mapview' ); ?>"><?php _e($mapview_title, 'geodirectory'); ?></label>
                <select  name="<?php echo 'mapview'; ?>" id="<?php echo $prefix . 'mapview'; ?>" class="geodir-select">
                    <?php
                    $mapview_options = array(
                        'ROADMAP'=>__('Default Map', 'geodirectory'),
                        'SATELLITE'=>__('Satellite Map', 'geodirectory'),
                        'HYBRID'=>__('Hybrid Map', 'geodirectory'),
                        'TERRAIN'=>__('Terrain Map', 'geodirectory'),
                    );
                    foreach($mapview_options as $val => $name){
                        echo "<option value='$val' ".selected($val,$mapview,false)." >$name</option>";
                    }
                    ?>
                </select>
                <span class="geodir_message_note"><?php _e('Please select listing map view to use', 'geodirectory');?></span>
            </div>
        <?php }?>

        <?php if (isset($post_mapzoom)) { ?>
            <input type="hidden" value="<?php if (isset($post_mapzoom)) {
                echo esc_attr($post_mapzoom);
            } ?>" name="<?php echo 'mapzoom'; ?>" id="<?php echo $prefix . 'mapzoom'; ?>"/>
        <?php }

        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_address','geodir_cfi_address',10,2);

/**
 * Get the html input for the custom field: taxonomy
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_taxonomy($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_taxonomy_{$html_var}")){
        /**
         * Filter the taxonomy html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_taxonomy_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if(is_admin() && $cf['name']=='post_tags'){return;}

        //print_r($cf);echo '###';
        $name = $cf['name'];
        $frontend_title = $cf['frontend_title'];
        $frontend_desc = $cf['desc'];
        $is_required = $cf['is_required'];
        $is_admin = $cf['for_admin_use'];
        $required_msg = $cf['required_msg'];
        $taxonomy = $cf['post_type']."category";
        $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Select Category', 'geodirectory' );

        if ($value == $cf['default']) {
            $value = '';
        } ?>
        <div id="<?php echo $taxonomy;?>_row"
             class="<?php if ($is_required) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $taxonomy ); ?>">
                <?php $frontend_title = __($frontend_title, 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($is_required) echo '<span>*</span>';?>
            </label>

            <div id="<?php echo $taxonomy;?>" class="geodir_taxonomy_field" style="float:left; width:70%;">
                <?php
                global $wpdb, $gd_post, $cat_display, $post_cat, $package_id, $exclude_cats;

                $exclude_cats = array();

				$package = geodir_get_post_package( $gd_post, $cf['post_type'] );
                //if ($is_admin == '1') {
					if ( ! empty( $package ) && isset( $package->exclude_category ) ) {
						if ( is_array( $package->exclude_category ) ) {
							$exclude_cats = $package->exclude_category;
						} else {
							$exclude_cats = $package->exclude_category != '' ? explode( ',', $package->exclude_category ) : array();
						}
                    }
                //}

                $extra_fields = maybe_unserialize( $cf['extra_fields'] );
				if ( is_array( $extra_fields ) && ! empty( $extra_fields['cat_display_type'] ) ) {
					$cat_display = $extra_fields['cat_display_type'];
				} else {
					$cat_display = 'select';
				}

                if (isset($_REQUEST['backandedit']) && !empty($post_cat['post_category']) && is_array($post_cat['post_category'])) {
                    $post_cat = implode(",", $post_cat['post_category']);
                } else {
                    if (isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
                        $post_cat = geodir_get_post_meta($_REQUEST['pid'], 'post_category', true);
                }

                $category_limit = ! empty( $package ) && isset( $package->category_limit ) ? absint( $package->category_limit ) : 0;
				$category_limit = (int) apply_filters( 'geodir_cfi_post_categories_limit', $category_limit, $post, $package );

                if ( $cat_display != '' ) {
                    $required_limit_msg = '';
                    if ( $category_limit > 0 && $cat_display != 'select' && $cat_display != 'radio' ) {
                        $required_limit_msg = wp_sprintf( __( 'Only select %d categories for this package.', 'geodirectory' ), array( $category_limit ) );
                    } else {
                        $required_limit_msg = $required_msg;
                    }

                    echo '<input type="hidden" cat_limit="' . $category_limit . '" id="cat_limit" value="' . esc_attr($required_limit_msg) . '" name="cat_limit[' . $taxonomy . ']"  />';
					echo '<input type="hidden" name="default_category" value="' . esc_attr( geodir_get_cf_default_category_value() ) . '">';


                    if ($cat_display == 'select' || $cat_display == 'multiselect') {
                        $multiple = '';
						$default_field = '';
                        if ($cat_display == 'multiselect') {
                            $multiple = 'multiple="multiple"';
							$default_field = 'data-cmultiselect="default_category"';
						} else {
							$default_field = 'data-cselect="default_category"';
						}
						echo '<select id="' .$taxonomy . '" ' . $multiple . ' type="' . $taxonomy . '" name="tax_input['.$taxonomy.'][]" alt="' . $taxonomy . '" field_type="' . $cat_display . '" class="geodir_textfield textfield_x geodir-select" data-placeholder="' . esc_attr( $placeholder ) . '" ' . $default_field . '>';


                        if ($cat_display == 'select')
                            echo '<option value="">' . __('Select Category', 'geodirectory') . '</option>';

                    }

                    echo geodir_custom_taxonomy_walker($taxonomy);

                    if ($cat_display == 'select' || $cat_display == 'multiselect')
                        echo '</select>';

                }

                ?>
            </div>

            <span class="geodir_message_note"><?php _e($frontend_desc, 'geodirectory');?></span>
            <?php if ($is_required) { ?>
                <span class="geodir_message_error"><?php _e($required_msg, 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_taxonomy','geodir_cfi_taxonomy',10,2);


/**
 * Get the html input for the custom field: taxonomy
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_categories($html,$cf){

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if(has_filter("geodir_custom_field_input_taxonomy_{$html_var}")){
        /**
         * Filter the taxonomy html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_taxonomy_{$html_var}",$html,$cf);
    }

    // If no html then we run the standard output.
    if(empty($html)) {

        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if(is_admin() && $cf['name']=='post_tags'){return;}

        //print_r($cf);echo '###';
        $name = $cf['name'];
        $frontend_title = $cf['frontend_title'];
        $frontend_desc = $cf['desc'];
        $is_required = $cf['is_required'];
        $is_admin = $cf['for_admin_use'];
        $required_msg = $cf['required_msg'];
        $taxonomy = $cf['post_type']."category";
        $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Select Category', 'geodirectory' );

        if ($value == $cf['default']) {
            $value = '';
        } ?>
        <div id="<?php echo $taxonomy;?>_row"
             class="<?php echo esc_attr( $cf['css_class'] ); ?> <?php if ($is_required) echo 'required_field';?> geodir_form_row clearfix gd-fieldset-details">
            <label for="cat_limit">
                <?php $frontend_title = __($frontend_title, 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($is_required) echo '<span>*</span>';?>
            </label>

            <div id="<?php echo $taxonomy;?>" class="geodir_taxonomy_field" style="float:left; width:70%;">
                <?php

                global $wpdb, $gd_post, $cat_display, $post_cat, $package_id, $exclude_cats;

                $exclude_cats = array();

				$package = geodir_get_post_package( $gd_post, $cf['post_type'] );
                //if ($is_admin == '1') {
					if ( ! empty( $package ) && isset( $package->exclude_category ) ) {
						if ( is_array( $package->exclude_category ) ) {
							$exclude_cats = $package->exclude_category;
						} else {
							$exclude_cats = $package->exclude_category != '' ? explode( ',', $package->exclude_category ) : array();
						}
                    }
                //}

                $extra_fields = maybe_unserialize( $cf['extra_fields'] );
				if ( is_array( $extra_fields ) && ! empty( $extra_fields['cat_display_type'] ) ) {
					$cat_display = $extra_fields['cat_display_type'];
				} else {
					$cat_display = 'select';
				}

                $post_cat = geodir_get_cf_value($cf);

				$category_limit = ! empty( $package ) && isset( $package->category_limit ) ? absint( $package->category_limit ) : 0;
				$category_limit = (int) apply_filters( 'geodir_cfi_post_categories_limit', $category_limit, $gd_post, $package );

                if ($cat_display != '') {

                    $required_limit_msg = '';
                    if ($category_limit > 0 && $cat_display != 'select' && $cat_display != 'radio') {

                        $required_limit_msg = wp_sprintf( __('Only select %d categories for this package.', 'geodirectory'), $category_limit );

                    } else {
                        $required_limit_msg = $required_msg;
                    }

                    echo '<input type="hidden" cat_limit="' . $category_limit . '" id="cat_limit" value="' . esc_attr($required_limit_msg) . '" name="cat_limit[' . $taxonomy . ']"  />';
					echo '<input type="hidden" name="default_category" value="' . esc_attr( geodir_get_cf_default_category_value() ) . '">';

                    if ($cat_display == 'select' || $cat_display == 'multiselect') {
                        $multiple = '';
						$default_field = '';
                        if ($cat_display == 'multiselect') {
                            $multiple = 'multiple="multiple"';
							$default_field = 'data-cmultiselect="default_category"';
						} else {
							$default_field = 'data-cselect="default_category"';
						}

                        echo '<select id="' .$taxonomy . '" ' . $multiple . ' type="' . $taxonomy . '" name="tax_input['.$taxonomy.'][]" alt="' . $taxonomy . '" field_type="' . $cat_display . '" class="geodir_textfield textfield_x geodir-select" data-placeholder="' . esc_attr( $placeholder ) . '" ' . $default_field . ' aria-label="' . esc_attr( $placeholder ) . '">';

                        if ($cat_display == 'select')
                            echo '<option value="">' . __('Select Category', 'geodirectory') . '</option>';

                    }

                    echo GeoDir_Admin_Taxonomies::taxonomy_walker($taxonomy);

                    if ($cat_display == 'select' || $cat_display == 'multiselect')
                        echo '</select>';

                }

                ?>
            </div>

            <span class="geodir_message_note"><?php _e($frontend_desc, 'geodirectory');?></span>
            <?php if ($is_required) { ?>
                <span class="geodir_message_error"><?php _e($required_msg, 'geodirectory'); ?></span>
            <?php } ?>
        </div>

        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_categories','geodir_cfi_categories',10,2);


/**
 * Get the html input for the custom field: tags
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_tags( $html, $cf ) {
    // we use the standard WP tags UI in backend
    if ( is_admin() ) {
        return '';
    }

    $html_var = $cf['htmlvar_name'];

    // Check if there is a custom field specific filter.
    if ( has_filter("geodir_custom_field_input_tags_{$html_var}" ) ) {
        /**
         * Filter the multiselect html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters( "geodir_custom_field_input_tags_{$html_var}", $html, $cf );
    }

    // If no html then we run the standard output.
    if ( empty( $html ) ) {
        ob_start(); // Start  buffering;

        $extra_fields = maybe_unserialize( $cf['extra_fields'] );
        $value = geodir_get_cf_value( $cf );

        $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Enter tags separated by a comma ,', 'geodirectory' );
        $cf['option_values'] = "tag1,tag2";

        $post_type = isset( $_REQUEST['listing_type'] ) ? geodir_clean_slug( $_REQUEST['listing_type'] ) : '';
        $term_array = array();

        if ( $post_type ) {
            $tag_no       = 10;
            if ( is_array( $extra_fields ) && ! empty( $extra_fields['no_of_tag'] ) ) {
                $tag_no = absint( $extra_fields['no_of_tag'] );
            }
            $tag_filter = array(
                'taxonomy'   => $post_type . '_tags',
                'hide_empty' => false,
                'orderby'    => 'count',
                'order'      => 'DESC',
                'number'     => $tag_no,
            );
            $tag_args   = apply_filters( 'geodir_custom_field_input_tag_args', $tag_filter );
            $terms      = get_terms( $tag_args );
            if ( ! empty( $terms ) ) {
                foreach( $terms as $term ) {
                    $term_array[] = $term->name;
                }
            }

            if ( ! empty( $term_array ) ) {
                $cf['option_values'] = implode( ",", $term_array );
            }
        }

        // Enable spell check
        $spellcheck = is_array( $extra_fields ) && ! empty( $extra_fields['spellcheck'] ) ? 'true' : '';

        ?>
        <div id="<?php echo $cf['name']; ?>_row"
             class="<?php if ($cf['is_required']) echo 'required_field'; ?> geodir_form_row clearfix gd-fieldset-details">
            <label for="<?php echo esc_attr( $cf['name'] ); ?>">
                <?php $frontend_title = __($cf['frontend_title'], 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; ?>
                <?php if ($cf['is_required']) echo '<span>*</span>'; ?>
            </label>
            <input type="hidden" name="gd_field_<?php echo $cf['name']; ?>" value="1"/>
            <div class="geodir_multiselect_list">
                <select field_type="<?php echo $cf['type']; ?>" name="tax_input[<?php echo wp_strip_all_tags( esc_attr($post_type ) ) ."_tags"; ?>][]" id="<?php echo $cf['name']; ?>" multiple="multiple" class="geodir_textfield textfield geodir-select-tags" data-placeholder="<?php echo esc_attr( $placeholder ); ?>" spellcheck="<?php echo $spellcheck; ?>">
                    <?php
                    // current tags
                    $current_tags_arr = geodir_string_to_options($value, true);

                    // popular tags
                    $option_values_arr = geodir_string_to_options($cf['option_values'], true);

                    // add the popular tags
                    $select_options = '';
                    $value_array = array();
                    if(!empty($value)){
                        $value_array = array_map('trim',explode(",",$value));
                    }
                    if (!empty($option_values_arr) || !empty($current_tags_arr)) {
                        $select_options .= '<optgroup label="'.__('Popular tags','geodirectory').'">';
                        foreach ($option_values_arr as $option_row) {
                            if(is_array($current_tags_arr) && in_array($option_row,$current_tags_arr)){
                                continue;
                            }
                            $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                            $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                            $selected = in_array($option_value,$value_array ) ? 'selected="selected"' : '';
                            $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                        }
                        $select_options .= '</optgroup>';
                    }

                    // add the post current tags
                    if (!empty($current_tags_arr)) {
                        $select_options .= '<optgroup label="'.__('Your tags','geodirectory').'">';
                        foreach ($current_tags_arr as $option_row) {
                            $option_label = isset($option_row['label']) ? $option_row['label'] : '';
                            $option_value = isset($option_row['value']) ? $option_row['value'] : '';
                            $selected = in_array($option_value,$value_array ) ? 'selected="selected"' : '';
                            $select_options .= '<option value="' . esc_attr($option_value) . '" ' . $selected . '>' . $option_label . '</option>';
                        }
                        $select_options .= '</optgroup>';
                    }



                    echo $select_options;
?>
                    </select></div>

            <span class="geodir_message_note"><?php _e($cf['desc'], 'geodirectory'); ?></span>
            <?php if ($cf['is_required']) { ?>
                <span class="geodir_message_error"><?php _e($cf['required_msg'], 'geodirectory'); ?></span>
            <?php } ?>
        </div>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_tags','geodir_cfi_tags',10,2);



/**
 * Get the html input for the custom field: business_hours
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 2.0.0
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_business_hours( $html, $cf ) {
	global $gd_post;

	if ( empty( $html ) ) {
		$htmlvar_name = $cf['htmlvar_name'];
		$name = $cf['name'];
		$label = __( $cf['frontend_title'], 'geodirectory' );
		$description = __( $cf['desc'], 'geodirectory' );
		$value = geodir_get_cf_value( $cf );

		$locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
		$time_format = geodir_bh_input_time_format();
		$timezone_string = geodir_timezone_string();
		$weekdays = geodir_get_short_weekdays();

		$hours = array();
		$display = 'none';

		if ( ! empty( $value ) ) {
			$display = '';
			$value = stripslashes_deep( $value );
			$periods = geodir_schema_to_array( $value, ( ! empty( $gd_post->country ) ? $gd_post->country : '' ) );
			if ( ! empty( $periods['hours'] ) ) {
				$hours = $periods['hours'];
			}

			if ( ! empty( $periods['timezone_string'] ) ) {
				$timezone_string = $periods['timezone_string'];
			}
		} else {
			$hours = geodir_bh_default_values(); // Default value
		}

		$timezone_data = geodir_timezone_data( $timezone_string );

		ob_start();
		?>
		<script type="text/javascript">jQuery(function($){GeoDir_Business_Hours.init({'field':'<?php echo $htmlvar_name; ?>','value':'<?php echo $value; ?>','json':'<?php echo stripslashes_deep(json_encode($value)); ?>','offset':<?php echo (int) $timezone_data['offset']; ?>,'utc_offset':'<?php echo $timezone_data['utc_offset']; ?>','offset_dst':<?php echo (int) $timezone_data['offset_dst']; ?>,'utc_offset_dst':'<?php echo $timezone_data['utc_offset_dst']; ?>','has_dst':<?php echo (int) $timezone_data['has_dst']; ?>,'is_dst':<?php echo (int) $timezone_data['is_dst']; ?>});});</script>
        <div id="<?php echo $name;?>_row" class="geodir_form_row clearfix gd-fieldset-details gd-bh-row">
            <label for="<?php echo $htmlvar_name; ?>_f_active_1"><?php echo $label; ?></label>
			<div class="gd-bh-field" data-field-name="<?php echo $htmlvar_name; ?>" role="radiogroup">
				<span class="gd-radios" role="radio"><input name="<?php echo $htmlvar_name; ?>_f[active]" id="<?php echo $htmlvar_name; ?>_f_active_1" value="1" class="gd-checkbox" field_type="radio" type="radio" <?php checked( ! empty( $value ), true ); ?> data-field="active" aria-label="<?php esc_attr_e( 'Yes', 'geodirectory' ); ?>"> <?php _e( 'Yes', 'geodirectory' ); ?></span>
				<span class="gd-radios" role="radio"><input name="<?php echo $htmlvar_name; ?>_f[active]" id="<?php echo $htmlvar_name; ?>_f_active_0" value="0" class="gd-checkbox" field_type="radio" type="radio" <?php checked( empty( $value ), true ); ?> data-field="active" aria-label="<?php esc_attr_e( 'No', 'geodirectory' ); ?>"> <?php _e( 'No', 'geodirectory' ); ?></span>
				<div class="gd-bh-items" style="display:<?php echo $display; ?>" data-12am="<?php echo esc_attr( date_i18n( $time_format, strtotime( '00:00' ) ) ); ?>">
					<table class="form-table widefat fixed">
						<thead>
							<tr><th class="gd-bh-day"><?php _e( 'Day', 'geodirectory' ); ?></th><th class="gd-bh-24hours"><?php _e( 'Open 24 hours', 'geodirectory' ); ?></th><th class="gd-bh-time"><?php _e( 'Opening Hours', 'geodirectory' ); ?></th><th class="gd-bh-act"><span class="sr-only visually-hidden"><?php _e( 'Add', 'geodirectory' ); ?></span></th></tr>
						</thead>
						<tbody>
							<tr style="display:none!important"><td colspan="4" class="gd-bh-blank"><div class="gd-bh-hours"><input type="text" id="GD_UNIQUE_ID_o" data-field-alt="open" data-bh="time" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" readonly> - <input type="text" id="GD_UNIQUE_ID_c" data-field-alt="close" data-bh="time" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" readonly><input id="GD_UNIQUE_ID_oa" type="hidden" data-field="open"><input type="hidden" id="GD_UNIQUE_ID_ca" data-field="close"> <span class="gd-bh-remove"><i class="fas fa-minus-circle" aria-hidden="true"></i></span></div></td></tr>
							<?php foreach ( $weekdays as $day_no => $day ) { ?>
							<tr class="gd-bh-item<?php echo ( empty( $hours[ $day_no ] ) ? ' gd-bh-item-closed' : '' ); ?>">
								<td class="gd-bh-day"><?php echo $day; ?></td>
								<td class="gd-bh-24hours"><input type="checkbox" value="1"></td>
								<td class="gd-bh-time" data-day="<?php echo $day_no; ?>" data-field="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>]">
									<?php
										if ( ! empty( $hours[ $day_no ] ) ) {
											$slots = $hours[ $day_no ];

											foreach ( $slots as $slot ) {
												$open = $close = $open_display = $close_display = $open_His = $close_His = '';

												$unique_id = uniqid( rand() );

												if ( ! empty( $slot['opens'] ) ) {
													$open = $slot['opens'];
													$open_time = strtotime( $open );
													$open_display = date_i18n( $time_format, $open_time );
													$open_His = date_i18n( 'H:i:s', $open_time );
												}

												if ( ! empty( $slot['closes'] ) ) {
													$close = $slot['closes'];
													$close_time = strtotime( $close );
													$close_display = date_i18n( $time_format, $close_time );
													$close_His = date_i18n( 'H:i:s', $close_time );
												}
										?>
										<div class="gd-bh-hours<?php echo ( ( $open == '00:00' && $open == $close ) ? ' gd-bh-has24' : '' ); ?>">
											<input type="text" id="<?php echo $unique_id; ?>_o" data-field-alt="open" data-bh="time" value="<?php echo esc_attr( $open_display ); ?>" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" data-time="<?php echo $open_His; ?>" readonly> - <input type="text" id="<?php echo $unique_id; ?>_c" data-field-alt="close" data-bh="time" value="<?php echo esc_attr( $close_display ); ?>" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" data-time="<?php echo $close_His; ?>" readonly><input type="hidden" id="<?php echo $unique_id; ?>_oa" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][open][]" data-field="open" value="<?php echo esc_attr( $open ); ?>"><input type="hidden" id="<?php echo $unique_id; ?>_ca" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][close][]" data-field="close" value="<?php echo esc_attr( $close ); ?>"> <span class="gd-bh-remove"><i class="fas fa-minus-circle" aria-hidden="true"></i></span>
										</div>
										<?php } ?>
									<?php } else { ?>
									<div class="gd-bh-closed"><?php _e( 'Closed', 'geodirectory' ); ?></div>
									<?php } ?>
								</td>
								<td class="gd-bh-act"><span class="gd-bh-add"><i class="fas fa-plus-circle" aria-hidden="true"></i></span></td>
							</tr>
							<?php } ?>
							<tr class="gd-tz-item">
								<td colspan="4"><label for="<?php echo $htmlvar_name; ?>_f_timezone_string"><?php _e( 'Timezone', 'geodirectory' ); ?></label>
									<select data-field="timezone_string" id="<?php echo $htmlvar_name; ?>_f_timezone_string" class="geodir_textfield textfield_x geodir-select" data-placeholder="<?php esc_attr_e( 'Select a city/timezone&hellip;', 'geodirectory' ); ?>" data-allow_clear="true"><?php echo geodir_timezone_choice( $timezone_string, $locale ) ;?></select>
                                </td>
                            </tr>
						</tbody>
					</table>
				</div>
				<input type="hidden" name="<?php echo $htmlvar_name; ?>" value="<?php echo esc_attr( $value ); ?>">
			</div>
            <span class="geodir_message_note"><?php echo $description; ?></span>
        </div>
        <?php
        $html = ob_get_clean();
    }

	return $html;
}
add_filter( 'geodir_custom_field_input_business_hours', 'geodir_cfi_business_hours', 10, 2 );



/**
 * Get the html input for the custom field: images
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_files( $html, $cf ) {
    $html_var = $cf['htmlvar_name'];

    // we use the standard WP tags UI in backend
    if ( is_admin() && $html_var == 'post_images' ) {
        return '';
    }

    // Check if there is a custom field specific filter.
    if ( has_filter("geodir_custom_field_input_files_{$html_var}" ) ) {
        /**
         * Filter the multiselect html by individual custom field.
         *
         * @param string $html The html to filter.
         * @param array $cf The custom field array.
         * @since 1.6.6
         */
        $html = apply_filters("geodir_custom_field_input_files_{$html_var}", $html, $cf );
    }

    // If no html then we run the standard output.
    if ( empty( $html ) ) {
        global $gd_post, $post;

		if ( empty( $gd_post ) && ! empty( $post ) ) {
            $gd_post = geodir_get_post_info( $post->ID );
        }

        ob_start(); // Start buffering;

        $extra_fields = maybe_unserialize( $cf['extra_fields'] );
        $file_limit = ! empty( $extra_fields ) && ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
        $file_limit = apply_filters( "geodir_custom_field_file_limit", $file_limit, $cf, $gd_post );

        $allowed_file_types = isset( $extra_fields['gd_file_types'] ) ? maybe_unserialize( $extra_fields['gd_file_types'] ) : geodir_image_extensions();
        $display_file_types = $allowed_file_types != '' ? '.' . implode( ", .", $allowed_file_types ) : '';
        if ( ! empty( $allowed_file_types ) ) {
			$allowed_file_types = implode( ",", $allowed_file_types );
		}

        // adjust values here
        $id = $cf['htmlvar_name']; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == �img1� then $_POST[�img1�] will have all the image urls

        $revision_id = isset( $gd_post->post_parent ) && $gd_post->post_parent ? $gd_post->ID : '';
        $post_id = isset( $gd_post->post_parent ) && $gd_post->post_parent ? $gd_post->post_parent : $gd_post->ID;

        // check for any auto save temp media values first
        $temp_media = get_post_meta( $post_id, "__" . $revision_id, true );
        if ( ! empty( $temp_media ) && isset( $temp_media[ $html_var ] ) ) {
            $files = $temp_media[ $html_var ];
        } else {
            $files = GeoDir_Media::get_field_edit_string( $post_id, $html_var, $revision_id );
        }

        if ( ! empty( $files ) ) {
            $total_files = count( explode( '::', $files ) );
        } else {
            $total_files = 0;
        }

        $image_limit = absint( $file_limit );
        $multiple = $image_limit == 1 ? false : true; // Allow multiple files upload
        $show_image_input_box = true;
        /**
         * Filter to be able to show/hide the image upload section of the add listing form.
         *
         * @since 1.0.0
         * @param bool $show_image_input_box Set true to show. Set false to not show.
         * @param string $listing_type The custom post type slug.
         */
        $show_image_input_box = apply_filters( 'geodir_file_uploader_on_add_listing', $show_image_input_box, $cf['post_type'] );

        if ( $show_image_input_box ) {
            add_thickbox();
            ?>

            <div id="<?php echo $cf['name']; ?>_row" class="<?php if ( $cf['is_required'] ) {echo 'required_field';} ?> geodir_form_row clearfix gd-fieldset-details ">

                <label for="<?php echo $id; ?>">
                    <?php $frontend_title = esc_attr__( $cf['frontend_title'], 'geodirectory' );
                    echo ( trim( $frontend_title ) ) ? $frontend_title : '&nbsp;'; ?>
                    <?php if ( $cf['is_required'] ) {
                        echo '<span>*</span>';
                    } ?>
                </label>
                <span class="geodir_message_note gd_images_desc"><?php _e( $cf['desc'], 'geodirectory' ); ?></span>
                <?php
                // params for file upload
                $is_required = $cf['is_required'];

                // the file upload template
                echo geodir_get_template_html( "file-upload.php", array(
                    'id'                  => $id,
                    'is_required'         => $is_required,
                    'files'	              => $files,
                    'image_limit'         => $image_limit,
                    'total_files'         => $total_files,
                    'allowed_file_types'  => $allowed_file_types,
                    'display_file_types'  => $display_file_types,
                    'multiple'            => $multiple,
                ) );

                if ( $is_required ) { ?>
                    <span class="geodir_message_error"><?php esc_attr_e($cf['required_msg'], 'geodirectory'); ?></span>
                <?php } ?>
            </div>
            <?php
        }
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_images','geodir_cfi_files',10,2);
add_filter('geodir_custom_field_input_file','geodir_cfi_files',10,2);

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
	global $aui_bs5;

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
    if ( empty( $html ) ) {
        $horizontal = true;

        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        ob_start(); // Start  buffering;
        ?>
        <fieldset class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>" id="geodir_fieldset_<?php echo (int) $cf['id']; ?>"<?php echo $conditional_attrs; ?>>
            <h3 class="h3"><?php echo __( $cf['frontend_title'], 'geodirectory' ); ?></h3>
            <?php if ( $cf['desc'] != '' ) {
                echo '<small class="text-muted">( ' . __( $cf['desc'], 'geodirectory' ) . ' )</small>';
            } ?>
        </fieldset>
        <?php
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_fieldset','geodir_cfi_fieldset',10,2);

function geodir_cfi_admin_only($cf){
    return !empty($cf['for_admin_use']) ? ' <i class="far fa-eye text-warning c-pointer" data-toggle="tooltip"  title="'.__("Admin only","geodirectory").'"></i>' : '';
}

/**
 * Many input types share the same output function.
 *
 * @param $cf
 *
 * @return string
 */
function geodir_cfi_input_output($cf){
    global $geodir_label_type;
    $extra_attributes = array();
    $title = '';
    $value = geodir_get_cf_value($cf);

    $type = $cf['type'];

    // blank value if default set for email. @todo, not sure we need this?
    if($type == 'email' && $value == $cf['default']){
        $value = '';
    }

    //number and float validation $validation_pattern
    if(isset($cf['data_type']) && $cf['data_type']=='INT'){$type = 'number'; $extra_attributes['lang'] ='EN';}
    elseif(isset($cf['data_type']) && $cf['data_type']=='FLOAT'){$type = 'float'; $extra_attributes['lang'] ='EN';}

    //validation
    if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
        $extra_attributes['pattern'] = $cf['validation_pattern'];
    }

    // validation message
    if(isset($cf['validation_msg']) && $cf['validation_msg']){
        $title = $cf['validation_msg'];
    }

    // field type (used for validation)
    $extra_attributes['field_type'] = $cf['type'];

    // required
    $required = !empty($cf['is_required']) ? ' <span class="text-danger">*</span>' : '';
    $required_msg = $required && $cf['required_msg'] != '' ? __( $cf['required_msg'] , 'geodirectory') : '';
    $validation_msg = $title != '' ? __( $title , 'geodirectory') : $required_msg;

    // admin only
    $admin_only = geodir_cfi_admin_only($cf);
    $conditional_attrs = geodir_conditional_field_attrs( $cf );

    $label = __( $cf['frontend_title'], 'geodirectory' );

    if ( $cf['name'] == 'service_distance' ) {
        $type = 'number';

        if ( geodir_get_option( 'search_distance_long' ) == 'km' ) {
            $label .= ' ' . __( '(km)', 'geodirectory');
        } else {
            $label .= ' ' . __( '(miles)', 'geodirectory');
        }
    }

    return aui()->input(
        array(
            'id'                => $cf['name'],
            'name'              => $cf['name'],
            'required'          => !empty($cf['is_required']) ? true : false,
            'label'             => $label . $admin_only . $required,
            'label_show'        => true,
            'label_type'        => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
            'type'              => $type,
            'title'             => $title,
            'validation_text'   => $validation_msg,
            'validation_pattern'=> ! empty( $cf['validation_pattern'] ) ? $cf['validation_pattern'] : '',
            'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
            'class'             => !empty($cf['css_class']) ? $cf['css_class'] : '',
            'value'             => $value,
            'help_text'         => __( $cf['desc'], 'geodirectory' ),
            'extra_attributes'  => $extra_attributes,
            'wrap_attributes'   => $conditional_attrs
        )
    );
}

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

        $html = geodir_cfi_input_output($cf);

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

        $html = geodir_cfi_input_output($cf);

    }

    return $html;
}
add_filter('geodir_custom_field_input_email','geodir_cfi_text',10,2);



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

        $html = geodir_cfi_input_output($cf);

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

        $html = geodir_cfi_input_output($cf);
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
function geodir_cfi_radio( $html, $cf ) {
	$html_var = $cf['htmlvar_name'];

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_input_radio_{$html_var}" ) ) {
		/**
		 * Filter the radio html by individual custom field.
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 * @since 1.6.6
		 */
		$html = apply_filters( "geodir_custom_field_input_radio_{$html_var}", $html, $cf );
	}

	// If no html then we run the standard output.
	if ( empty( $html ) ) {
		global $geodir_label_type;

		$option_values_deep = geodir_string_to_options( $cf['option_values'], true );
		$option_values = array();

		if ( ! empty( $option_values_deep ) ) {
			foreach( $option_values_deep as $option ) {
				if ( empty( $option['optgroup'] ) ) {
					$option_values[ $option['value'] ] = $option['label'];
				}
			}
		}

		// admin only
		$admin_only = geodir_cfi_admin_only($cf);
		$conditional_attrs = geodir_conditional_field_attrs( $cf );

		// Help text
		$help_text = $cf['desc'] != '' ? __( $cf['desc'], 'geodirectory' ) : '';

		$value = geodir_get_cf_value( $cf );

		// required
		$_required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';
		$required = false;
		$extra_attributes = array();

		if ( ! empty( $_required ) ) {
			$cf_name = esc_attr( $cf['name'] );
			$extra_attributes['onchange'] = "if(jQuery('input[name=\"" . $cf_name . "\"]:checked').length || !jQuery('input#" . $cf_name . "0').is(':visible')){jQuery('#" . $cf_name . "0').removeAttr('required')}else{jQuery('#" . $cf_name . "0').attr('required',true)}";
			$extra_attributes['oninput'] = "try{document.getElementById('" . $cf_name . "0').setCustomValidity('')}catch(e){}";
			$extra_attributes['oninvalid'] = 'try{document.getElementById(\'' . $cf_name . '0\').setCustomValidity(\'' . esc_attr( addslashes( __( $cf['required_msg'], 'geodirectory' ) ) ) . '\')}catch(e){}';

			if ( empty( $value ) ) {
				$required = true;
			}
		}

		$html = aui()->radio(
			array(
				'id'                => $cf['name'],
				'name'              => $cf['name'],
				'type'              => "radio",
				'title'             => esc_attr__( $cf['frontend_title'], 'geodirectory' ),
				'label'             => esc_attr__( $cf['frontend_title'], 'geodirectory' ) . $admin_only . $_required,
				'label_type'        => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'help_text'         => $help_text,
				'class'             => '',
				'value'             => $value,
				'required'          => $required,
				'options'           => $option_values,
				'wrap_attributes'   => $conditional_attrs,
				'extra_attributes'  => $extra_attributes
			)
		);
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
function geodir_cfi_checkbox( $html, $cf ) {
	// check it its a terms and conditions in admin area
	if ( is_admin() && isset( $cf['field_type_key'] ) && $cf['field_type_key'] == 'terms_conditions' ) {
		return $html;
	}

	$html_var = $cf['htmlvar_name'];

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_input_checkbox_{$html_var}" ) ) {
		/**
		 * Filter the checkbox html by individual custom field.
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 * @since 1.6.6
		 */
		$html = apply_filters( "geodir_custom_field_input_checkbox_{$html_var}", $html, $cf );
	}

	// If no html then we run the standard output.
	if ( empty( $html ) ) {
		global $geodir_label_type;

		$title = '';
		$value = geodir_get_cf_value( $cf );
		// Set default checked.
		if ( $value === '' && $cf['default'] ) {
			$value = '1';
		}
		$checked          = $value == '1' ? true : false;
		$extra_attributes = array();
		$validation_text = '';

		//validation
		if ( isset( $cf['validation_pattern'] ) && $cf['validation_pattern'] ) {
			$extra_attributes['pattern'] = $cf['validation_pattern'];
		}

		// required message
		if ( ! empty( $cf['is_required'] ) && ! empty( $cf['required_msg'] ) ) {
			$title = __( $cf['required_msg'], 'geodirectory' );
			$validation_text = $title;
		}

		// validation message
		if ( isset( $cf['validation_msg'] ) && $cf['validation_msg'] ) {
			$title = $cf['validation_msg'];
			$validation_text = $title;
		}

		// field type (used for validation)
		$extra_attributes['field_type'] = $cf['type'];

		// required
		$required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

		// help text
		$help_text = __( $cf['desc'], 'geodirectory' );

		if ( isset( $cf['field_type_key'] ) && $cf['field_type_key'] == 'terms_conditions' ) {
			$tc        = geodir_terms_and_conditions_page_id();
			$tc_link   = get_permalink( $tc );
			$help_text = "<a href='$tc_link' target='_blank'>" . __( $cf['desc'], 'geodirectory' ) . " <i class=\"fas fa-external-link-alt\" aria-hidden=\"true\"></i></a>";
		}

		$html = '<input type="hidden" name="' . $cf['name'] . '" id="checkbox_' . $cf['name'] . '" value="0"/>'; // this ensures a value is sent, if the next one is checked then that will set it as true

		// admin only
		$admin_only = geodir_cfi_admin_only($cf);
		$conditional_attrs = geodir_conditional_field_attrs( $cf );

		$html .= aui()->input(
			array(
				'id'               => $cf['name'],
				'name'             => $cf['name'],
				'type'             => "checkbox",
				'value'            => '1',
				'title'            => $title,
				'label'            => __( $cf['frontend_title'], 'geodirectory' ) . $admin_only. $required,
				'label_show'       => true,
				'required'         => ! empty( $cf['is_required'] ) ? true : false,
				'label_type'       => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'checked'          => $checked,
				'help_text'        => $help_text,
				'wrap_class'       => ! empty( $cf['css_class'] ) ? geodir_sanitize_html_class( $cf['css_class'] ) : '',
				'extra_attributes' => $extra_attributes,
				'validation_text'  => $validation_text,
				'wrap_attributes'  => $conditional_attrs
			)
		);
	}

	return $html;
}
add_filter( 'geodir_custom_field_input_checkbox','geodir_cfi_checkbox', 10, 2 );


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

        global $geodir_label_type;

        $validation_text = '';
        $extra_attributes = array();
        $value = geodir_get_cf_value($cf);
        $extra_fields = maybe_unserialize($cf['extra_fields']);
        $html_editor = ! empty( $extra_fields['advanced_editor'] );

        //validation
        if ( isset( $cf['validation_pattern'] ) && $cf['validation_pattern'] ) {
            $extra_attributes['pattern'] = $cf['validation_pattern'];
        }

        // Required message
        if ( ! empty( $cf['is_required'] ) && ! empty( $cf['required_msg'] ) ) {
            $validation_text = __( $cf['required_msg'], 'geodirectory' );
        }

        // Validation message
        if ( ! empty( $cf['validation_msg'] ) ) {
            $validation_text = __( $cf['validation_msg'], 'geodirectory' );
        }

        // wysiwyg
        $wysiwyg = apply_filters( 'geodir_custom_field_allow_html_editor', $html_editor, $cf );

        // Allow html tags
        $allow_tags = apply_filters( 'geodir_custom_field_textarea_allow_tags', ( $wysiwyg || $cf['name'] == 'video' ), $cf );

        // field type (used for validation)
        $extra_attributes['field_type'] =  $wysiwyg ? 'editor' :  $cf['type'];

        $extra_attributes = apply_filters( 'geodir_cfi_aui_textarea_attributes', $extra_attributes, $cf );
        $wysiwyg_attributes = apply_filters( 'geodir_cfi_aui_textarea_wysiwyg_attributes', array('quicktags' => true), $cf );

        // required
        $required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

        // admin only
        $admin_only = geodir_cfi_admin_only($cf);
        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        // help text
        $help_text = __( $cf['desc'], 'geodirectory' );

        $html = aui()->textarea(array(
            'name'       => $cf['name'],
            'class'      => '',
            'id'         => $cf['name'],
            'placeholder'=> esc_html__( $cf['placeholder_value'], 'geodirectory'),
            'title'      => $validation_text,
            'value'      => stripslashes($value),
            'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
            'required'   => !empty($cf['is_required']) ? true : false,
            'label'      => __($cf['frontend_title'], 'geodirectory').$admin_only . $required,
            'validation_text'   => $validation_text,
            'validation_pattern' => !empty($cf['validation_pattern']) ? $cf['validation_pattern'] : '',
            'no_wrap'    => false,
            'rows'      => 8,
            'wysiwyg'   => $wysiwyg ? $wysiwyg_attributes : false,
            'allow_tags' => $allow_tags,
            'help_text'        => $help_text,
            'extra_attributes' => $extra_attributes,
            'wrap_attributes' => $conditional_attrs
        ));
    }

    return $html;
}
add_filter('geodir_custom_field_input_textarea','geodir_cfi_textarea',10,2);


/**
 * Get the html input for the custom field: select
 *
 * @since 1.6.6
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @return string The html to output for the custom field.
 */
function geodir_cfi_select( $html, $cf ) {
	$html_var = $cf['htmlvar_name'];

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_input_select_{$html_var}" ) ) {
		/**
		 * Filter the select html by individual custom field.
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 * @since 1.6.6
		 */
		$html = apply_filters( "geodir_custom_field_input_select_{$html_var}", $html, $cf );
	}

	// If no html then we run the standard output.
	if ( empty( $html ) ) {
		global $geodir_label_type;

		$extra_attributes = array();
		$value = geodir_get_cf_value( $cf );
		$validation_text = '';

		// Required
		$required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

		// Required message
		if ( $required && ! empty( $cf['required_msg'] ) ) {
			$validation_text = __( $cf['required_msg'], 'geodirectory' );
		}

		// Validation message
		if ( ! empty( $cf['validation_msg'] ) ) {
			$validation_text = __( $cf['validation_msg'], 'geodirectory' );
		}

		// Validation
		if ( ! empty( $cf['validation_pattern'] ) ) {
			$extra_attributes['pattern'] = $cf['validation_pattern'];
		}

		$title = $validation_text;

		// help text
		$help_text = __( $cf['desc'], 'geodirectory' );

		// placeholder
		$placeholder = esc_attr__( $cf['placeholder_value'], 'geodirectory' );
		if ( empty( $placeholder ) ) {
			$placeholder = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), __( $cf['frontend_title'], 'geodirectory' ) );
		}

		//extra
		$extra_attributes['data-placeholder'] = esc_attr( $placeholder );
		$extra_attributes['option-ajaxchosen'] = 'false';

		// Set validation message
		if ( ! empty( $validation_text ) ) {
			$extra_attributes['oninvalid'] = 'try{this.setCustomValidity(\'' . esc_attr( addslashes( $validation_text ) ) . '\')}catch(e){}';
			$extra_attributes['onchange'] = 'try{this.setCustomValidity(\'\')}catch(e){}';
		}

		// Admin only
		$admin_only = geodir_cfi_admin_only( $cf );
		$conditional_attrs = geodir_conditional_field_attrs( $cf );

		$html .= aui()->select( array(
			'id' => $cf['name'],
			'name' => $cf['name'],
			'title' => $title,
			'placeholder' => $placeholder,
			'value' => $value,
			'required' => ! empty( $cf['is_required'] ) ? true : false,
			'label_show' => true,
			'label_type' => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
			'label' => __( $cf['frontend_title'], 'geodirectory' ) . $admin_only . $required,
			'validation_text' => $validation_text,
			'validation_pattern' => ! empty( $cf['validation_pattern'] ) ? $cf['validation_pattern'] : '',
			'help_text' => $help_text,
			'extra_attributes' => $extra_attributes,
			'options' => geodir_string_to_options( $cf['option_values'], true ),
			'select2' => true,
			'data-allow-clear' => true,
			'wrap_attributes' => $conditional_attrs
		) );
	}

	return $html;
}
add_filter( 'geodir_custom_field_input_select', 'geodir_cfi_select', 10, 2 );


/**
 * Get the html input for the custom field: multiselect
 *
 * @param string $html The html to be filtered.
 * @param array $cf The custom field array details.
 * @since 1.6.6
 *
 * @return string The html to output for the custom field.
 */
function geodir_cfi_multiselect( $html, $cf ) {
	global $aui_bs5;

	$html_var = $cf['htmlvar_name'];

	// Check if there is a custom field specific filter.
	if ( has_filter( "geodir_custom_field_input_multiselect_{$html_var}" ) ) {
		/**
		 * Filter the multiselect html by individual custom field.
		 *
		 * @param string $html The html to filter.
		 * @param array $cf The custom field array.
		 * @since 1.6.6
		 */
		$html = apply_filters( "geodir_custom_field_input_multiselect_{$html_var}", $html, $cf );
	}

	// If no html then we run the standard output.
	if ( empty( $html ) ) {
		global $geodir_label_type;
		$extra_attributes = array();
		$_value = geodir_get_cf_value($cf);
		$extra_fields = !empty($cf['extra_fields']) ? maybe_unserialize($cf['extra_fields']) : NULL;
		$multi_display = !empty($extra_fields['multi_display_type']) ? $extra_fields['multi_display_type'] : 'select';
		$validation_text = '';
		$id = $cf['htmlvar_name'];

		// required
		$required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

		// Required message
		if ( $required && ! empty( $cf['required_msg'] ) ) {
			$validation_text = __( $cf['required_msg'], 'geodirectory' );
		}

		// Validation message
		if ( ! empty( $cf['validation_msg'] ) ) {
			$validation_text = __( $cf['validation_msg'], 'geodirectory' );
		}

				// Validation
		if ( ! empty( $cf['validation_pattern'] ) ) {
			$extra_attributes['pattern'] = $cf['validation_pattern'];
		}

		$title = $validation_text;

		// help text
		$help_text = __( $cf['desc'], 'geodirectory' );

		// placeholder
		$placeholder = esc_attr__( $cf['placeholder_value'], 'geodirectory' );
		if ( empty( $placeholder ) ) {
			$placeholder = wp_sprintf( __( 'Select %s&hellip;', 'geodirectory' ), __($cf['frontend_title'], 'geodirectory'));
		}

		$value = ( ! is_array( $_value ) && $_value !== '' ) ? trim( $_value ) : $_value;
		if ( ! is_array( $value ) ) {
			$value = explode( ',', $value );
		}

		if ( ! empty( $value ) ) {
			$value = stripslashes_deep( $value );
			$value = array_map( 'trim', $value );
		}
		$value = array_filter( $value );

		//extra
		$extra_attributes['data-placeholder'] = esc_attr( $placeholder );
		$extra_attributes['option-ajaxchosen'] = 'false';

		// admin only
		$admin_only = geodir_cfi_admin_only( $cf );

		if ( $multi_display == 'select' ) {
			// Set validation message
			if ( ! empty( $validation_text ) ) {
				$extra_attributes['oninvalid'] = 'try{this.setCustomValidity(\'' . esc_attr( addslashes( $validation_text ) ) . '\')}catch(e){}';
				$extra_attributes['onchange'] = 'try{this.setCustomValidity(\'\')}catch(e){}';
			}

			$conditional_attrs = geodir_conditional_field_attrs( $cf );

			$html .= aui()->select( array(
				'id'                 => $cf['name'],
				'name'               => $cf['name'],
				'title'              => $title,
				'placeholder'        => $placeholder,
				'value'              => $value,
				'required'           => ! empty( $cf['is_required'] ) ? true : false,
				'label_show'         => true,
				'label_type'         => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
				'label'              => __( $cf['frontend_title'], 'geodirectory' ) . $admin_only . $required,
				'validation_text'    => $validation_text,
				'validation_pattern' => ! empty( $cf['validation_pattern'] ) ? $cf['validation_pattern'] : '',
				'help_text'          => $help_text,
				'extra_attributes'   => $extra_attributes,
				'options'            => geodir_string_to_options( $cf['option_values'], true ),
				'select2'            => true,
				'multiple'           => true,
				'data-allow-clear'   => false,
				'wrap_attributes'    => $conditional_attrs
			) );
		} elseif ( $multi_display == 'radiox' ) {
			$option_values_deep = geodir_string_to_options( $cf['option_values'], true );
			$option_values = array();

			if ( ! empty( $option_values_deep ) ) {
				foreach( $option_values_deep as $option ) {
					$option_values[$option['value']] = $option['label'];
				}
			}

			$conditional_attrs = geodir_conditional_field_attrs( $cf, '', 'radio' );

			$html .= aui()->radio(
				array(
					'id'                => $cf['name'],
					'name'              => $cf['name'],
					'type'              => "radio",
					'inline'            => false,
					'title'             => esc_attr__($cf['frontend_title'], 'geodirectory'),
					'label'             => esc_attr__($cf['frontend_title'], 'geodirectory').$admin_only.$required,
					'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
					'class'             => '',
					'value'             => $value,
					'options'           => $option_values,
					'wrap_attributes'   => $conditional_attrs
				)
			);
		} else {
			$conditional_attrs = geodir_conditional_field_attrs( $cf, '', $multi_display );
			$horizontal = true;
			ob_start();
			?>
			<div id="<?php echo $cf['name']; ?>_row" class="<?php if ( $cf['is_required'] ) {echo 'required_field';} ?> <?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row"<?php echo $conditional_attrs; ?>>
				<label for="<?php echo $id; ?>" class="<?php echo $horizontal ? '  col-sm-2 col-form-label' : '';?>">
					<?php $frontend_title = esc_attr__( $cf['frontend_title'], 'geodirectory' );
					echo ( trim( $frontend_title ) ) ? $frontend_title : '&nbsp;'; echo $admin_only;?>
					<?php if ( $cf['is_required'] ) {
						echo '<span class="text-danger">*</span>';
					} ?>
				</label>
			<?php

			// Set hidden input to save empty value for checkbox set.
			if ( $multi_display == 'checkbox' && ! empty( $cf['name'] ) ) {
				echo '<input type="hidden" name="' . esc_attr( $cf['name'] ) . '" value=""/>';
			}

			if ( $horizontal ) {
				echo "<div class='col-sm-10 mt-2' ><div class=' border rounded px-2 scrollbars-ios' style='max-height: 150px;overflow-y:auto;overflow-x: hidden;'>";
			}

			$option_values_arr = geodir_string_to_options( $cf['option_values'], true );

			if ( ! empty( $option_values_arr ) ) {
				foreach ( $option_values_arr as $i => $option_row ) {
					if ( isset( $option_row['optgroup'] ) && ( $option_row['optgroup'] == 'start' || $option_row['optgroup'] == 'end' ) ) {
						$option_label = isset( $option_row['label'] ) ? $option_row['label'] : '';

						echo $option_row['optgroup'] == 'start' ? '<h6>' . $option_label . '</h6>' : '';
					} else {
						if ( ! is_array( $value) && $value != '' ) {
							$value = trim( $value );
						}

						$option_label = isset($option_row['label']) ? $option_row['label'] : '';
						$option_value = isset($option_row['value']) ? $option_row['value'] : '';
						$checked = '';

						if ( ( ! is_array( $value ) && trim( $value ) != '' ) || ( is_array( $value ) && ! empty( $value ) ) ) {
							if ( ! is_array( $value ) ) {
								$value_array = explode( ',', $value );
							} else {
								$value_array = $value;
							}

							$value_array = stripslashes_deep( $value_array );

							if ( is_array( $value_array ) ) {
								$value_array = array_map( 'trim', $value_array );

								if ( in_array( $option_value, $value_array ) ) {
									$checked = 'checked="checked"';
								}
							}
						}

						if ( $multi_display == 'checkbox' ) {
							// Set checkbox required.
							$required = false;
							$extra_attributes = array();

							if ( ! empty( $cf['is_required'] ) ) {
								$cf_name = esc_attr( $cf['name'] );
								$extra_attributes['onchange'] = "if(jQuery('[name=\"" . $cf_name . "[]\"]:checked').length || !jQuery('input#" . $cf_name . "_0').is(':visible')){jQuery('#" . $cf_name . "_0').removeAttr('required')}else{jQuery('#" . $cf_name . "_0').attr('required',true)}";
								$extra_attributes['oninput'] = "try{document.getElementById('" . $cf_name . "_0').setCustomValidity('')}catch(e){}";

								if ( $i === 0 ) {
									$extra_attributes['oninvalid'] = 'try{document.getElementById(\'' . $cf_name . '_0\').setCustomValidity(\'' . esc_attr( addslashes( __( $cf['required_msg'], 'geodirectory' ) ) ) . '\')}catch(e){}';

									if ( empty( $value ) ) {
										$required = true;
									}
								}
							}

							echo aui()->input(
									array(
										'name'             => $cf['name'] . '[]',
										'id'               => $cf['name'] .'_'. $i,
										'type'             => esc_attr( $multi_display ),
										'value'            => esc_attr( $option_value ),
										'title'            => $title,
										'label'            => $option_label,
										'label_type'       => 'hidden',
										'no_wrap'          => true,
										'checked'          => $checked,
										'required'         => $required,
										'extra_attributes' => $extra_attributes
									)
								);
						} else {
							// Set redio required.
							$required = false;
							$extra_attributes = array();

							if ( ! empty( $cf['is_required'] ) ) {
								$cf_name = esc_attr( $cf['name'] );
								$extra_attributes['onchange'] = "if(jQuery('input[name=\"" . $cf_name . "\"]:checked').length || !jQuery('input#" . $cf_name . "_00').is(':visible')){jQuery('#" . $cf_name . "_00').removeAttr('required')}else{jQuery('#" . $cf_name . "_00').attr('required',true)}";
								$extra_attributes['oninput'] = "try{document.getElementById('" . $cf_name . "_00').setCustomValidity('')}catch(e){}";

								if ( $i === 0 ) {
									$extra_attributes['oninvalid'] = 'try{document.getElementById(\'' . $cf_name . '_00\').setCustomValidity(\'' . esc_attr( addslashes( __( $cf['required_msg'], 'geodirectory' ) ) ) . '\')}catch(e){}';

									if ( empty( $value ) ) {
										$required = true;
									}
								}
							}

							if ( is_array( $value ) ) {
								$value = ! empty( $value ) ? $value[0] : '';
							}
							echo aui()->radio(
								array(
									'name'              => $cf['name'],
									'id'                => $cf['name'] .'_'. $i,
									'type'              => "radio",
									'inline'            => false,
									'label'             => '',
									'label_type'        => 'hidden',
									'wrap_class'        => 'mb-1',
									'class'             => '',
									'value'             => $value,
									'options'           => array( esc_attr( $option_value ) => $option_label ),
									'required'         => $required,
									'extra_attributes' => $extra_attributes
								)
							);
						}
					}
				}
			}

			$description = $help_text != '' && class_exists( "AUI_Component_Helper" ) ? AUI_Component_Helper::help_text( $help_text ) : '';

			if ( $horizontal ) {
				echo "</div>" . $description . "</div>";
			}

			echo "</div>";

			$html .= ob_get_clean();
		}
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
        global $geodir_label_type;

        $title = '';
        $extra_attributes = array();
        $value = geodir_get_cf_value($cf);
        $extra_fields = maybe_unserialize($cf['extra_fields']);
        $html_editor = ! empty( $extra_fields['advanced_editor'] );

        //validation
        if ( isset( $cf['validation_pattern'] ) && $cf['validation_pattern'] ) {
            $extra_attributes['pattern'] = $cf['validation_pattern'];
        }

        // validation message
        if ( isset( $cf['validation_msg'] ) && $cf['validation_msg'] ) {
            $title = $cf['validation_msg'];
        }

        // field type (used for validation)
        $extra_attributes['field_type'] = 'editor' ;

        // required
        $required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

        // help text
        $help_text = __( $cf['desc'], 'geodirectory' );

        // $editor_settings = array('media_buttons' => false, 'textarea_rows' => 10);

        // admin only
        $admin_only = geodir_cfi_admin_only($cf);

        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        $html = aui()->textarea(array(
            'name'       => $cf['name'],
            'class'      => '',
            'id'         => $cf['name'],
            'placeholder'=> esc_html__( $cf['placeholder_value'], 'geodirectory'),
            'title'      => $title,
            'value'      => stripslashes($value),
            'label_show'       => true,
            'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
            'required'   => !empty($cf['is_required']) ? true : false,
            'label'      => __($cf['frontend_title'], 'geodirectory').$admin_only.$required,
            'validation_text'   => !empty($cf['validation_msg']) ? $cf['validation_msg'] : '',
            'validation_pattern' => !empty($cf['validation_pattern']) ? $cf['validation_pattern'] : '',
            'no_wrap'    => false,
            'rows'      => 8,
            'wysiwyg'   => array('quicktags' => true),
            'help_text'        => $help_text,
            'extra_attributes' => $extra_attributes,
            'wrap_attributes'  => $conditional_attrs
        ));

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
        global $geodir_label_type;

        $extra_attributes = array();
        $title = '';
        $value = geodir_get_cf_value($cf);

        $name = $cf['name'];
        $extra_fields = ! empty( $cf['extra_fields'] ) ? maybe_unserialize( $cf['extra_fields'] ) : NULL;
        $date_format = ! empty( $extra_fields['date_format'] ) ? $extra_fields['date_format'] : 'yy-mm-dd';
        $jquery_date_format = $date_format;

        // Check if we need to change the format or not
        $date_format_len = strlen( str_replace( ' ', '', $date_format ) );

        // If greater then 5 then it's the old style format.
        if ( $date_format_len > 5 ) {
            $search = array( 'dd', 'd', 'DD', 'mm', 'm', 'MM', 'yy' ); // jQuery UI datepicker format.
            $replace = array( 'd', 'j', 'l', 'm', 'n', 'F', 'Y' ); // PHP date format

            $date_format = str_replace( $search, $replace, $date_format );
        } else {
            $jquery_date_format = geodir_date_format_php_to_aui( $jquery_date_format );
        }

        if ( $value == '0000-00-00' ) {
            $value = ''; // If date not set, then mark it empty.
        }

        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $extra_attributes['pattern'] = $cf['validation_pattern'];
        }

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $title = $cf['validation_msg'];
        }

        // field type (used for validation)
        $extra_attributes['field_type'] = $cf['type'];

        // flatpickr attributes
        $extra_attributes['data-alt-input'] = 'true';
        $extra_attributes['data-alt-format'] = $date_format;
        $extra_attributes['data-date-format'] = 'Y-m-d';

        // minDate / maxDate
        if ( ! empty( $extra_fields['date_range'] ) ) {
            $year_range = geodir_input_parse_year_range( $extra_fields['date_range'] );

            // minDate
            if ( ! empty( $year_range['min_year'] ) ) {
                $extra_attributes['data-min-date'] = $year_range['min_year'] . '-01-01';
            }

            // maxDate
            if ( ! empty( $year_range['max_year'] ) ) {
                $extra_attributes['data-max-date'] = $year_range['max_year'] . '-12-31';
            }
        }

        /**
         * Filter datepicker field extra attributes.
         *
         * @since 2.1.1.11
         *
         * @param array $extra_attributes Field attributes.
         * @param array $cf The custom field array.
         */
        $extra_attributes = apply_filters( 'geodir_cfi_datepicker_extra_attrs', $extra_attributes, $cf );

        // required
        $required = !empty($cf['is_required']) ? ' <span class="text-danger">*</span>' : '';

        // admin only
        $admin_only = geodir_cfi_admin_only($cf);

        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        $html = aui()->input(
            array(
                'id'                => $cf['name'],
                'name'              => $cf['name'],
                'required'          => !empty($cf['is_required']) ? true : false,
                'label'              => __($cf['frontend_title'], 'geodirectory').$admin_only . $required,
                'label_show'       => true,
                'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
                'type'              => 'datepicker',
                'title'             =>  $title,
                'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
                'class'             => '',
                'value'             => $value, // esc_attr(stripslashes($value))
                'help_text'         => __($cf['desc'], 'geodirectory'),
                'extra_attributes'  => $extra_attributes,
                'wrap_attributes'   => $conditional_attrs
            )
        );

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
        global $geodir_label_type;

        $extra_attributes = array();
        $title = '';
        $value = geodir_get_cf_value($cf);

        if ($value != '')
            $value = date('H:i', strtotime($value));

        //validation
        if(isset($cf['validation_pattern']) && $cf['validation_pattern']){
            $extra_attributes['pattern'] = $cf['validation_pattern'];
        }

        // validation message
        if(isset($cf['validation_msg']) && $cf['validation_msg']){
            $title = $cf['validation_msg'];
        }

        // field type (used for validation)
        $extra_attributes['field_type'] = $cf['type'];

        // flatpickr attributes
        $extra_attributes['data-enable-time'] = 'true';
        $extra_attributes['data-no-calendar'] = 'true';
        $extra_attributes['data-date-format'] = 'H:i';

        // required
        $required = !empty($cf['is_required']) ? ' <span class="text-danger">*</span>' : '';

        // admin only
        $admin_only = geodir_cfi_admin_only($cf);

        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        $html = aui()->input(
            array(
                'id'                => $cf['name'],
                'name'              => $cf['name'],
                'required'          => !empty($cf['is_required']) ? true : false,
                'label'              => __($cf['frontend_title'], 'geodirectory').$admin_only.$required,
                'label_show'       => true,
                'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
                'type'              => 'timepicker',
                'title'             =>  $title,
                'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
                'class'             => '',
                'value'             => $value, // esc_attr(stripslashes($value))
                'help_text'         => __($cf['desc'], 'geodirectory'),
                'extra_attributes'  => $extra_attributes,
                'wrap_attributes'   => $conditional_attrs
            )
        );
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
    global $aui_bs5, $mapzoom, $gd_move_inline_script;

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
        global $post, $gd_post, $geodirectory, $geodir_label_type;

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
        $extra_fields = ! empty( $cf['extra_fields'] ) ? stripslashes_deep( maybe_unserialize( $cf['extra_fields'] ) ) : array();
        $prefix = $name . '_';
        $street2_title = '';

        // street2
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

        $extra_attributes = array();
        $title = '';
        $value = geodir_get_cf_value($cf);

        // field type (used for validation)
        $extra_attributes['field_type'] = 'text';

        // required
        $required = !empty($cf['is_required']) ? ' <span class="text-danger">*</span>' : '';

        // NOTE autocomplete="new-password" seems to be the only way to disable chrome autofill and others.
        $extra_attributes['autocomplete'] = 'new-password';

        // make hint appear when field selected
        $extra_attributes['onfocus'] = "jQuery('.gd-locate-me-btn').tooltip('show');jQuery(this).attr('autocomplete','new-password');";
        $extra_attributes['onblur'] = "jQuery('.gd-locate-me-btn').tooltip('hide');";

        $address_label_type = !empty($geodir_label_type) ? $geodir_label_type : 'horizontal';
        if( $address_label_type == 'floating'){  $address_label_type = 'hidden';}
        $placeholder = $cf['placeholder_value'] != '' ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Enter a location', 'geodirectory' );

        $conditional_attrs = geodir_conditional_field_attrs( $cf, 'street', 'text' );

        echo aui()->input(
            array(
                'id'                => $prefix . 'street',
                'name'              => 'street',
                'required'          => !empty($cf['is_required']) ? true : false,
                'label'              => esc_attr__($address_title, 'geodirectory') .$required,
                'label_show'       => true,
                'label_type'       => $address_label_type,
                'type'              => 'text',
                'title'             =>  $title,
                'placeholder'       => esc_html( $placeholder ),
                'class'             => 'gd-add-listing-address-input',
                'value'             => esc_attr(stripslashes($value)),
                'help_text'         => __($cf['desc'], 'geodirectory'),
                'input_group_right' => $locate_me ? '<div class="gd-locate-me-btn input-group-text c-pointer" data-toggle="tooltip" title="' . esc_attr__( 'use my location', 'geodirectory' ) . '"><i class="fas fa-location-arrow"></i></div>' : '',
                'extra_attributes'  => $extra_attributes,
                'wrap_attributes'   => $conditional_attrs
            )
        );

        if (isset($extra_fields['show_street2']) && $extra_fields['show_street2']) {
            $extra_attributes = array();
            $title = '';
            // field type (used for validation)
            $extra_attributes['field_type'] = 'text';
            echo aui()->input(
                array(
                    'id'                => $prefix . 'street2',
                    'name'              => 'street2',
                    'required'          => false,
                    'label'              => esc_attr__($street2_title, 'geodirectory'),
                    'label_show'       => true,
                    'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
                    'type'              => 'text',
                    'title'             =>  $title,
                    'placeholder'       => esc_html__( $cf['placeholder_value'], 'geodirectory'),
                    'class'             => '',
                    'value'             => esc_attr(stripslashes($street2)),
                    'help_text'         => sprintf( __('Please enter listing %s', 'geodirectory'), __($street2_title, 'geodirectory') ),
                    'extra_attributes'  => $extra_attributes,
                    'wrap_attributes'   => geodir_conditional_field_attrs( $cf, 'street2', 'text' )
                )
            );
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

        if ( isset( $extra_fields['show_zip'] ) && $extra_fields['show_zip'] ) {
            $title = ! empty( $extra_fields['zip_required'] ) ? __( 'Zip/Post Code is required!', 'geodirectory' ) : '';
            $extra_attributes = array();
            // field type (used for validation)
            $extra_attributes['field_type'] = 'text';
            echo aui()->input(
                array(
                    'id'                => $prefix . 'zip',
                    'name'              => 'zip',
                    'required'          => ( ! empty( $extra_fields['zip_required'] ) ? true : false ),
                    'label'             => esc_attr__( $zip_title, 'geodirectory' ) . ( ! empty( $extra_fields['zip_required'] ) ? $required : '' ),
                    'label_show'        => true,
                    'label_type'        => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                    'type'              => 'text',
                    'title'             =>  $title,
                    'class'             => '',
                    'value'             => esc_attr( stripslashes( $zip ) ),
                    'help_text'         => wp_sprintf( __( 'Please enter listing %s', 'geodirectory' ), __( $zip_title, 'geodirectory' ) ),
                    'extra_attributes'  => $extra_attributes,
                    'wrap_attributes'   => geodir_conditional_field_attrs( $cf, 'zip', 'text' )
                )
            );
        } ?>

        <?php  if (isset($extra_fields['show_map']) && $extra_fields['show_map']) { ?>
            <div id="geodir_<?php echo $prefix . 'map'; ?>_row" class="geodir_form_row clearfix gd-fieldset-details" <?php echo geodir_conditional_field_attrs( $cf, 'map', 'fieldset' ); ?>>
                <?php
                if ( geodir_core_multi_city() ) {
                    add_filter( 'geodir_add_listing_map_restrict', '__return_false' );
                }

				$design_style   = geodir_design_style();

				/**
				 * Move add listing JavaScript to inline script.
				 *
				 * @since 2.2.14
				 *
				 * @param bool $gd_move_inline_script Whether to move inline .
				 */
				$gd_move_inline_script = apply_filters( 'geodir_add_listing_move_inline_script', true );

				$tmpl_args = array(
					'prefix' => $prefix,
					'map_title' => $map_title,
					'country' => $country,
					'region' => $region,
					'city' => $city,
					'lat' => $lat,
					'lng' => $lng,
					'design_style' => $design_style
				);
				$template = $design_style . '/map/map-add-listing.php';

				echo geodir_get_template_html( $template, $tmpl_args );

                if ( $lat_lng_blank ) {
                    $lat = '';
                    $lng = '';
                }
                ?>
            </div>
            <?php
            /* Hide latitude/longitude */
            $wrap_class = ( ( isset( $extra_fields['show_latlng'] ) && $extra_fields['show_latlng'] ) || is_admin() ) ? '' : 'd-none gd-hidden-latlng';

            $title = '';
            $extra_attributes = array();
            // required
            $required = !empty($cf['is_required']) ? ' <span class="text-danger">*</span>' : '';

            // field type (used for validation)
            $extra_attributes['field_type'] = 'number';

            // number extras
            $extra_attributes['min'] = '-90';
            $extra_attributes['max'] = '90';
            $extra_attributes['step'] = 'any';
            $extra_attributes['lang'] = 'EN';
            $extra_attributes['size'] = '25';

            echo aui()->input(
                array(
                    'id'                => $prefix . 'latitude',
                    'name'              => 'latitude',
                    'required'          => $is_required,
                    'label'             => esc_attr__( 'Address Latitude', 'geodirectory' ) . $required,
                    'label_show'        => true,
                    'label_type'        => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                    'type'              => 'number',
                    'title'             =>  $title,
                    'value'             => esc_attr( stripslashes( $lat ) ),
                    'help_text'         => __( 'Please enter latitude for google map perfection. eg. : <b>39.955823048131286</b>', 'geodirectory' ),
                    'extra_attributes'  => $extra_attributes,
                    'wrap_class'        => $wrap_class,
                    'wrap_attributes'   => geodir_conditional_field_attrs( $cf, 'latitude' )
                )
            );

            $title = '';
            $extra_attributes = array();

            // field type (used for validation)
            $extra_attributes['field_type'] = 'number';

            // number extras
            $extra_attributes['min'] = '-180';
            $extra_attributes['max'] = '180';
            $extra_attributes['step'] = 'any';
            $extra_attributes['lang'] = 'EN';
            $extra_attributes['size'] = '25';

            echo aui()->input(
                array(
                    'id'                => $prefix . 'longitude',
                    'name'              => 'longitude',
                    'required'          => $is_required,
                    'label'             => esc_attr__( 'Address Longitude', 'geodirectory' ) . $required,
                    'label_show'        => true,
                    'label_type'        => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                    'type'              => 'number',
                    'title'             =>  $title,
                    'value'             => esc_attr( stripslashes( $lng ) ),
                    'help_text'         => __( 'Please enter longitude for google map perfection. eg. : <b>-75.14408111572266</b>', 'geodirectory' ),
                    'extra_attributes'  => $extra_attributes,
                    'wrap_class'        => $wrap_class,
                    'wrap_attributes'   => geodir_conditional_field_attrs( $cf, 'longitude' )
                )
            );
        } ?>

        <?php if (isset($extra_fields['show_mapview']) && $extra_fields['show_mapview']) {

            $title = '';
            $extra_attributes = array();
            // field type (used for validation)
            $extra_attributes['field_type'] = 'text';
            echo aui()->select(
                array(
                    'id'                => $prefix . 'mapview',
                    'name'              => 'mapview',
                    'required'          => false,
                    'label'              => esc_attr__($mapview_title, 'geodirectory'),
                    'label_show'       => true,
                    'label_type'       => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
                    'type'              => 'text',
                    'title'             =>  $title,
                    'class'             => '',
                    'value'             => esc_attr(stripslashes($mapview)),
                    'help_text'         => __('Please select listing map view to use', 'geodirectory'),
                    'extra_attributes'  => $extra_attributes,
                    'options'           => array(
                        'ROADMAP'=>__('Default Map', 'geodirectory'),
                        'SATELLITE'=>__('Satellite Map', 'geodirectory'),
                        'HYBRID'=>__('Hybrid Map', 'geodirectory'),
                        'TERRAIN'=>__('Terrain Map', 'geodirectory'),
                    ),
                    'wrap_attributes'   => geodir_conditional_field_attrs( $cf, 'mapview', 'select' )
                )
            );

        }?>

        <?php if ( isset( $post_mapzoom ) ) { ?>
            <div data-argument="<?php echo $prefix . 'mapzoom'; ?>" class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row d-none" <?php echo geodir_conditional_field_attrs( $cf, 'mapzoom', 'hidden' ); ?>><input type="hidden" value="<?php echo esc_attr( $post_mapzoom ); ?>" name="<?php echo 'mapzoom'; ?>" id="<?php echo $prefix . 'mapzoom'; ?>"/></div>
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
                global $wpdb, $gd_post, $cat_display, $post_cat, $package_id, $exclude_cats, $post;

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
                        $data_attrs = '';
                        if ( $category_limit == 1 ) {
                            $cat_display = 'select'; // Force single select.
                        } elseif ( $category_limit > 0 ) {
                            $data_attrs .= ' data-maximum-selection-length="' . $category_limit . '"';
                        }
                        $multiple = '';
                        $default_field = '';
                        if ($cat_display == 'multiselect') {
                            $multiple = 'multiple="multiple"';
                            $default_field = 'data-aui-cmultiselect="default_category"';
                        } else {
                            $default_field = 'data-cselect="default_category"';
                        }
                        echo '<select id="' .$taxonomy . '" ' . $multiple . ' type="' . $taxonomy . '" name="tax_input['.$taxonomy.'][]" alt="' . $taxonomy . '" field_type="' . $cat_display . '" class="geodir_textfield textfield_x geodir-select" data-placeholder="' . esc_attr( $placeholder ) . '" ' . $default_field . '' . $data_attrs . '>';

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
	global $aui_bs5;

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
        global $geodir_label_type;
        ob_start(); // Start  buffering;
        $value = geodir_get_cf_value($cf);

        if(is_admin() && $cf['name']=='post_tags'){return;}

        $horizontal = empty( $geodir_label_type ) || $geodir_label_type == 'horizontal' ? true : false;
        //print_r($cf);echo '###';
        $name = $cf['name'];
        $frontend_title = $cf['frontend_title'];
        $frontend_desc = $cf['desc'];
        $is_required = $cf['is_required'];
        $is_admin = $cf['for_admin_use'];
        $required_msg = $cf['required_msg'];
        $taxonomy = $cf['post_type']."category";
        $placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Select Category', 'geodirectory' );

        // admin only
        $admin_only = geodir_cfi_admin_only($cf);
        $conditional_attrs = geodir_conditional_field_attrs( $cf );

        if ($value == $cf['default']) {
            $value = '';
        } ?>

        <div id="<?php echo $taxonomy;?>_row"
             class="<?php echo esc_attr( $cf['css_class'] ); ?> <?php if ($is_required) echo 'required_field';?> <?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> <?php echo $horizontal ? ' row' : '';?>"  data-argument="<?php echo esc_attr($taxonomy);?>"<?php echo $conditional_attrs; ?>>
            <label for="cat_limit" class=" <?php echo $horizontal ? ' col-sm-2 col-form-label' : ''; echo $geodir_label_type == 'hidden' || $geodir_label_type=='floating' ? ' sr-only visually-hidden ' : '';?>">
                <?php $frontend_title = __($frontend_title, 'geodirectory');
                echo (trim($frontend_title)) ? $frontend_title : '&nbsp;'; echo $admin_only;?>
                <?php if ($is_required) echo '<span class="text-danger">*</span>';?>
            </label>

            <div id="<?php echo $taxonomy;?>_wrap" class="geodir_taxonomy_field <?php echo $horizontal ? ' col-sm-10' : '';?>" >
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

                    if ( $cat_display == 'checkbox' && $category_limit == 1 ) {
                        $cat_display = 'radio';
                    }

                    if ($category_limit > 0 && $cat_display != 'select' && $cat_display != 'radio') {
                        if ( $category_limit > 1 ) {
                            $required_limit_msg = wp_sprintf( __('Only select %d categories for this package.', 'geodirectory' ), $category_limit );
                        } else {
                            $required_limit_msg = __('Only select 1 category for this package.', 'geodirectory' );
                        }
                    } else {
                        $required_limit_msg = $required_msg;
                    }

                    if ($cat_display == 'select' || $cat_display == 'multiselect') {
                        $data_attrs = '';
                        if ( $category_limit == 1 ) {
                            $cat_display = 'select'; // Force single select.
                        } elseif ( $category_limit > 0 ) {
                            $data_attrs .= ' data-maximum-selection-length="' . $category_limit . '"';
                        }
                        $multiple = '';
                        $default_field = '';
                        if ($cat_display == 'multiselect') {
                            $multiple = 'multiple="multiple"';
                            $default_field = 'data-aui-cmultiselect="default_category"';
                        } else {
                            $default_field = 'data-cselect="default_category"';
                        }

                        // Required category message.
                        if ( ! empty( $required_msg ) ) {
                            $required_msg = __( $required_msg, 'geodirectory' );
                        } else {
                            $required_msg = __( 'Select at least one category from the list!', 'geodirectory' );
                        }
                        $data_attrs .= ' required oninvalid="setCustomValidity(\'' . esc_attr( $required_msg ) . '\')" onchange="try{setCustomValidity(\'\')}catch(e){}"';

                        echo '<select  id="' .$taxonomy . '" ' . $multiple . ' type="' . $taxonomy . '" name="tax_input['.$taxonomy.'][]" alt="' . $taxonomy . '" field_type="' . $cat_display . '" class="geodir-category-select ' . ( $aui_bs5 ? 'form-select' : 'geodir-select' ) . ' aui-select2" data-placeholder="' . esc_attr( $placeholder ) . '" ' . $default_field . ' aria-label="' . esc_attr( $placeholder ) . '" style="width:100%"' . $data_attrs . '>';

                        if ($cat_display == 'select')
                            echo '<option value="">' . __('Select Category', 'geodirectory') . '</option>';
                    }

                    echo GeoDir_Admin_Taxonomies::taxonomy_walker($taxonomy);

                    if ($cat_display == 'select' || $cat_display == 'multiselect')
                        echo '</select>';
                }

                $help_text = $category_limit > 0 && $required_limit_msg ? ' (' . $required_limit_msg . ')' : '';
                echo class_exists("AUI_Component_Helper") ? AUI_Component_Helper::help_text( __( $frontend_desc, 'geodirectory' ) . esc_attr( $help_text ) ) : '';
                ?>
                <div class="geodir_message_error alert alert-danger my-2 px-3 py-2" style="display:none"></div>
            </div>
            <input type="hidden" cat_limit="<?php echo (int) $category_limit; ?>" id="cat_limit" value="<?php echo esc_attr( $required_limit_msg ); ?>" name="cat_limit[<?php echo esc_attr( $taxonomy ); ?>]" />
        </div>
        <?php
        $html = ob_get_clean();

        // Default category select
        if ( $cat_display == 'multiselect' || $cat_display == 'checkbox' ) {
            // required
            $required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

            $default_category = (int) geodir_get_cf_default_category_value();

            $html .= aui()->select( array(
                'id'              => "default_category",
                'name'            => "default_category",
                'placeholder'     => esc_attr__( "Select Default Category", 'geodirectory' ),
                'value'           => $default_category,
                'required'        => true,
                'label_type'      => ! empty( $geodir_label_type ) ? $geodir_label_type : 'horizontal',
                'label'           => __( "Default Category", 'geodirectory' ) . $required,
                'help_text'       => esc_attr__( "The default category can affect the listing URL and map marker.", 'geodirectory' ),
                'multiple'        => false,
                'options'         => $default_category ? array( $default_category => '' ) : array(),
                'element_require' => '[%' . $taxonomy . '%]!=null',
                'wrap_attributes' => geodir_conditional_field_attrs( $cf, 'default_category', 'select' )
            ) );
        } else {
            // leaving this out should set the default as the main cat anyway
            $html .= '<input type="hidden" id="default_category" name="default_category" value="' . esc_attr( geodir_get_cf_default_category_value() ) . '">';
        }
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
	global $gd_post;

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
		global $geodir_label_type;

		$extra_attributes = array();
		$value = geodir_get_cf_value($cf);
		$title = '';

		$cf['option_values'] = "tag1,tag2";

		$package = geodir_get_post_package( $gd_post, $cf['post_type'] );

		// Tag limit
		$tag_limit = ! empty( $package ) && isset( $package->tag_limit ) ? absint( $package->tag_limit ) : 0;
		$tag_limit = (int) apply_filters( 'geodir_cfi_post_tags_limit', $tag_limit, $gd_post, $package );
		if ( $tag_limit > 0 ) {
			 $extra_attributes['data-maximum-selection-length'] = $tag_limit;
		}

		//validation
		if ( isset( $cf['validation_pattern'] ) && $cf['validation_pattern'] ) {
			$extra_attributes['pattern'] = $cf['validation_pattern'];
		}

		// validation message
		if ( isset( $cf['validation_msg'] ) && $cf['validation_msg'] ) {
			$title = $cf['validation_msg'];
		}

		// required
		$required = ! empty( $cf['is_required'] ) ? ' <span class="text-danger">*</span>' : '';

		// help text
		$help_text = __( $cf['desc'], 'geodirectory' );

		// field type (used for validation)
		$extra_attributes['field_type'] = $cf['type'];

		// placeholder
		$placeholder = ! empty( $cf['placeholder_value'] ) ? __( $cf['placeholder_value'], 'geodirectory' ) : __( 'Enter tags separated by a comma ,', 'geodirectory' );

		$extra_fields = maybe_unserialize( $cf['extra_fields'] );

		//extra
		$extra_attributes['data-placeholder'] = esc_attr( $placeholder );
		$extra_attributes['option-ajaxchosen'] = 'false';
		$extra_attributes['data-token-separators'] = "[',']";
		$extra_attributes['data-tags'] = 'true';
		$extra_attributes['spellcheck'] = 'false';

		if ( is_array( $extra_fields ) ) {
			// Disable new tags
			if ( ! empty( $extra_fields['disable_new_tags'] ) ) {
				$extra_attributes['data-tags'] = 'false';
			}

			// Enable spell check
			if ( ! empty( $extra_fields['spellcheck'] ) && empty( $extra_fields['disable_new_tags'] ) ) {
				$extra_attributes['spellcheck'] = 'true';
			}
		}

		$post_type = isset( $_REQUEST['listing_type'] ) ? geodir_clean_slug( $_REQUEST['listing_type'] ) : '';
		$term_array = array();
		$options = array();
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

		$value_array = array();
		if(!empty($value)){
			$value_array = array_map('trim',explode(",",$value));
		}

		// Popular tags
		$option_values_arr = geodir_string_to_options( $cf['option_values'], true );

		if ( ! empty( $option_values_arr ) ) {
			$options =  array_merge( array( array( 'label' => __( 'Popular tags', 'geodirectory' ), 'optgroup' => 'start' ) ) ,  $option_values_arr );
			$options =  array_merge( $options , array( array( 'optgroup' => 'end' ) ) );

			// remove from the popular list if already selected
			foreach( $options as $key => $val ) {
				if ( ! empty( $val['value'] ) && ! empty( $value_array ) && in_array( $val['value'], $value_array ) ) {
					unset( $options[$key] );
				}
			}
		}

		// Current tags
		$current_tags_arr = geodir_string_to_options( $value, true );
		if ( ! empty( $current_tags_arr ) ) {
			$current_options =  array_merge( array( array( 'label' => __( 'Your tags', 'geodirectory' ), 'optgroup' => 'start' ) ) , $current_tags_arr );
			$current_options =  array_merge( $current_options , array( array( 'optgroup' => 'end' ) ) );
			$options = array_merge( $options, $current_options );
		}

		// admin only
		$admin_only = geodir_cfi_admin_only($cf);
		$conditional_attrs = geodir_conditional_field_attrs( $cf );

		$validation_text = '';
		// Required message
		if ( $required && ! empty( $cf['required_msg'] ) ) {
			$validation_text = __( $cf['required_msg'], 'geodirectory' );
		}

		// Validation message
		if ( ! empty( $cf['validation_msg'] ) ) {
			$validation_text = __( $cf['validation_msg'], 'geodirectory' );
		}

		// Set validation message
		if ( ! empty( $validation_text ) ) {
			$extra_attributes['oninvalid'] = 'try{this.setCustomValidity(\'' . esc_attr( addslashes( $validation_text ) ) . '\')}catch(e){}';
			$extra_attributes['onchange'] = 'try{this.setCustomValidity(\'\')}catch(e){}';
		}

		/**
		 * Filter the post tags extra attributes.
		 *
		 * @since 2.2.4
		 *
		 * @param array $extra_attributes Tags attributes.
		 */
		$extra_attributes = apply_filters( 'geodir_cfi_aui_post_tags_attributes', $extra_attributes, $cf );

		$html = aui()->select( array(
			'id'                 => $cf['name'],
			'name'               => "tax_input[".wp_strip_all_tags( esc_attr($post_type ) ) ."_tags"."][]" ,
			'title'              => $title,
			'placeholder'        => $placeholder,
			'value'              => $value_array,
			'required'           => !empty($cf['is_required']) ? true : false,
			'label_show'         => true,
			'label_type'         => !empty($geodir_label_type) ? $geodir_label_type : 'horizontal',
			'label'              => __($cf['frontend_title'], 'geodirectory').$admin_only.$required,
			'validation_text'    => $validation_text,
			'validation_pattern' => !empty($cf['validation_pattern']) ? $cf['validation_pattern'] : '',
			'help_text'          => $help_text,
			'multiple'           => true,
			'extra_attributes'   => $extra_attributes,
			'options'            => $options,
			'select2'            => true,
			'data-allow-clear'   => false,
			'style'              => 'width:100%;height:inherit;',
			'wrap_attributes'    => $conditional_attrs
		) );
	}

	return $html;
}
add_filter( 'geodir_custom_field_input_tags', 'geodir_cfi_tags', 10, 2 );

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
    global $aui_bs5, $gd_post, $geodir_label_type;

    if ( empty( $html ) ) {
        $horizontal = empty( $geodir_label_type ) || $geodir_label_type == 'horizontal' ? true : false;

        $htmlvar_name = $cf['htmlvar_name'];
        $name = $cf['name'];
        $label = __( $cf['frontend_title'], 'geodirectory' );
        $description = __( $cf['desc'], 'geodirectory' );
        $value = geodir_get_cf_value( $cf );

        $locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
        $time_format = geodir_bh_input_time_format();
        $timepicker_format = geodir_bh_input_time_format( true );
        $timezone_string = geodir_timezone_string();
        $weekdays = geodir_get_short_weekdays();
        $display = 'none';

        $time_24hr = strpos( $timepicker_format, 'H' ) !== false ? true : false;
        if ( $time_24hr && strpos( $timepicker_format, '\H' ) !== false ) {
            if ( strpos( $timepicker_format, '\H' ) + 1 === strpos( $timepicker_format, 'H' ) ) {
                $time_24hr = false;
            }
        }

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


        // enqueue the script
        $aui_settings = AyeCode_UI_Settings::instance();
        $aui_settings->enqueue_flatpickr();

        $conditional_attrs = geodir_conditional_field_attrs( $cf, $htmlvar_name, 'hidden' );

        ob_start();
        ?>
        <script type="text/javascript">jQuery(function($){GeoDir_Business_Hours.init({'field':'<?php echo $htmlvar_name; ?>','value':'<?php echo $value; ?>','json':'<?php echo stripslashes_deep(json_encode($value)); ?>','offset':<?php echo (int) $timezone_data['offset']; ?>,'utc_offset':'<?php echo $timezone_data['utc_offset']; ?>','offset_dst':<?php echo (int) $timezone_data['offset_dst']; ?>,'utc_offset_dst':'<?php echo $timezone_data['utc_offset_dst']; ?>','has_dst':<?php echo (int) $timezone_data['has_dst']; ?>,'is_dst':<?php echo (int) $timezone_data['is_dst']; ?>});});</script>
        <div id="<?php echo $name;?>_row" class="gd-bh-row<?php echo ( $horizontal ? ' row' : '' ); ?> <?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?>"<?php echo $conditional_attrs; ?>>
            <label for="<?php echo $htmlvar_name; ?>_f_active_1" class="<?php echo ( $horizontal ? 'pt-0 col-sm-2 col-form-label' : ( $aui_bs5 ? 'form-label' : '' ) ); ?>"><?php echo $label; ?></label>
            <div class="gd-bh-field<?php echo ( $horizontal ? ' col-sm-10' : '' ); ?>" data-field-name="<?php echo $htmlvar_name; ?>" role="radiogroup">
                <?php echo aui()->radio(
                    array(
                        'id' => $htmlvar_name . '_f_active',
                        'name' => $htmlvar_name . '_f_active',
                        'required' => true,
                        'label' => '',
                        'label_type' => 'vertical',
                        'type' => 'radio',
                        'value' => ( $value ? 1 : 0 ),
                        'options' => array(
                            '1' => __( 'Yes','geodirectory' ),
                            '0' => __( 'No','geodirectory' )
                        ),
                        'extra_attributes' => array(
                            'data-field' => 'active',
                            'data-no-rule' => 1
                        )
                    )
                );
                ?>
                <div class="gd-bh-items" style="display:<?php echo $display; ?>" data-12am="<?php echo esc_attr( strtoupper( date_i18n( $time_format, strtotime( '00:00' ) ) ) ); ?>">
                    <table class="table table-borderless table-striped">
                        <thead class="<?php echo ( $aui_bs5 ? 'table-light' : 'thead-light' ); ?>">
                        <tr><th class="gd-bh-day"><?php _e( 'Day', 'geodirectory' ); ?></th><th class="gd-bh-24hours text-nowrap"><?php _e( 'Open 24 hours', 'geodirectory' ); ?></th><th class="gd-bh-time"><?php _e( 'Opening Hours', 'geodirectory' ); ?></th><th class="gd-bh-act"><span class="sr-only visually-hidden"><?php _e( 'Add', 'geodirectory' ); ?></span></th></tr>
                        </thead>
                        <tbody>
                        <tr style="display:none!important">
                            <td colspan="4" class="gd-bh-blank">
                                <div class="gd-bh-hours row">
                                    <div class="col-10 p-0 mb-1"><div class="input-group">
                                        <div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo ( $time_24hr ? 'true' : 'false' ); ?>" data-alt-input-class="gd-alt-open form-control text-center bg-white rounded-0 w-100 GD_UNIQUE_ID_oa" class="form-control text-center bg-white rounded-0 w-100" id="GD_UNIQUE_ID_o" data-field-alt="open" data-bh="time" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" value="09:00"></div>
                                        <div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo ( $time_24hr ? 'true' : 'false' ); ?>" data-alt-input-class="gd-alt-close form-control text-center bg-white rounded-0 w-100 GD_UNIQUE_ID_oa" class="form-control text-center bg-white rounded-0 w-100" id="GD_UNIQUE_ID_c" data-field-alt="close" data-bh="time" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" value="17:00"></div>
                                    </div></div>
                                    <div class="col-2 text-left text-start gd-bh-remove"><i class="fas fa-minus-circle text-danger c-pointer mt-2" title="<?php _e("Remove hours","geodirectory"); ?>" data-toggle="tooltip" aria-hidden="true"></i></div>
                                </div>
                            </td>
                        </tr>
                        <?php foreach ( $weekdays as $day_no => $day ) { ?>
                            <tr class="gd-bh-item<?php echo ( empty( $hours[ $day_no ] ) ? ' gd-bh-item-closed' : '' ); ?>">
                                <td class="gd-bh-day align-top"><?php echo $day; ?></td>
                                <td class="gd-bh-24hours align-top"><div class="form-check mt-1"><input type="checkbox" value="1" class="form-check-input" <?php echo ( empty( $hours[ $day_no ] ) ? 'style="display:none"' : '' ); ?>></div></td>
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
                                            <div class="gd-bh-hours<?php echo ( ( $open == '00:00' && $open == $close ) ? ' gd-bh-has24' : '' ); ?> row">
                                                <div class="col-10 p-0 mb-1"><div class="input-group">
                                                    <div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo ( $time_24hr ? 'true' : 'false' ); ?>" data-alt-input-class="gd-alt-open form-control text-center bg-white rounded-0 w-100 <?php echo $unique_id; ?>_oa" data-aui-init="flatpickr" class="form-control text-center bg-white rounded-0 w-100" id="<?php echo $unique_id; ?>_o" data-field-alt="open" data-bh="time" value="<?php echo esc_attr( $open_His ); ?>" aria-label="<?php esc_attr_e( 'Open', 'geodirectory' ); ?>" data-time="<?php echo $open_His; ?>" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][open][]"></div>
                                                    <div class="col-md-6 col-sm-12 m-0 p-0"><input type="text" field_type="time" data-enable-time="true" data-no-calendar="true" data-date-format="H:i" data-alt-input="true" data-alt-format="<?php echo esc_attr( $timepicker_format ); ?>" data-time_24hr="<?php echo ( $time_24hr ? 'true' : 'false' ); ?>" data-alt-input-class="gd-alt-close form-control text-center bg-white rounded-0 w-100 <?php echo $unique_id; ?>_oa" data-aui-init="flatpickr" class="form-control text-center bg-white rounded-0 w-100" id="<?php echo $unique_id; ?>_c" data-field-alt="close" data-bh="time" value="<?php echo esc_attr( $close_His ); ?>" aria-label="<?php esc_attr_e( 'Close', 'geodirectory' ); ?>" data-time="<?php echo $close_His; ?>" name="<?php echo $htmlvar_name; ?>_f[hours][<?php echo $day_no; ?>][close][]"></div>
                                                </div></div>
                                                <div class="col-2 text-left text-start gd-bh-remove"><i class="fas fa-minus-circle text-danger c-pointer mt-2" title="<?php _e( "Remove hours", "geodirectory" ); ?>" data-toggle="tooltip" aria-hidden="true"></i></div>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="gd-bh-closed text-center"><?php _e( 'Closed', 'geodirectory' ); ?></div>
                                    <?php } ?>
                                </td>
                                <td class="gd-bh-act align-top"><span class="gd-bh-add c-pointer" title="<?php _e("Add new set of hours","geodirectory"); ?>" data-toggle="tooltip"><i class="fas fa-plus-circle text-primary" aria-hidden="true"></i></span></td>
                            </tr>
                        <?php } ?>
                        <tr class="gd-tz-item">
                            <td colspan="4"><div class="row mb-0"><div class="col-sm-2 col-form-label"><label for="<?php echo $htmlvar_name; ?>_f_timezone_string" class="mb-0"><?php _e( 'Timezone:', 'geodirectory' ); ?></label></div><div class="col-sm-10 pt-1"><select data-field="timezone_string" id="<?php echo $htmlvar_name; ?>_f_timezone_string" class="<?php echo ( $aui_bs5 ? 'form-select form-select-sm' : 'custom-select custom-select-sm' ); ?> aui-select2" data-placeholder="<?php esc_attr_e( 'Select a city/timezone&hellip;', 'geodirectory' ); ?>" data-allow-clear="1" option-ajaxchosen="false" tabindex="-1" aria-hidden="true" data-select2-id="<?php echo $htmlvar_name; ?>"><?php echo geodir_timezone_choice( $timezone_string, $locale ) ;?></select></div></div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="<?php echo $htmlvar_name; ?>" value="<?php echo esc_attr( $value ); ?>">
                <?php if($horizontal){echo '<small class="form-text text-muted d-block">'. $description.'</small>';} ?>
            </div>
            <?php if(!$horizontal){echo '<small class="form-text text-muted d-block">'. $description.'</small>';} ?>
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
	global $aui_bs5;

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

        $horizontal = true;

        $extra_fields = maybe_unserialize( $cf['extra_fields'] );
        $file_limit = ! empty( $extra_fields ) && ! empty( $extra_fields['file_limit'] ) ? absint( $extra_fields['file_limit'] ) : 0;
        $file_limit = apply_filters( "geodir_custom_field_file_limit", $file_limit, $cf, $gd_post );
        $file_types = isset( $extra_fields['gd_file_types'] ) ? maybe_unserialize( $extra_fields['gd_file_types'] ) : geodir_image_extensions();

        if ( ! empty( $file_types ) ) {
            if ( is_scalar( $file_types ) ) {
                $file_types = explode( ",", $file_types );
            }

            $file_types = array_filter( $file_types );
        } else {
            $file_types = array();
        }

        $display_file_types = ! empty( $file_types ) ? '.' . implode( ", .", $file_types ) : '';
        $allowed_file_types = ! empty( $file_types ) ? implode( ",", $file_types ) : '';

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
            $frontend_title = __( $cf['frontend_title'], 'geodirectory' );
            $extra_attributes = array();
            if ( ! empty( $cf['is_required'] ) ) {
                $extra_attributes['required'] = 'required';
                $extra_attributes['style'] = 'height:0!important;padding:0!important;margin:0!important;font-size:0!important;line-height:0!important;border:0!important;outline:none!important;position:absolute!important;top:80px!important';
                $extra_attributes['aria-label'] = esc_html( $frontend_title );
            }

            $validation_text = '';
            // Required message
            if ( ! empty( $cf['is_required'] ) && ! empty( $cf['required_msg'] ) ) {
                $validation_text = __( $cf['required_msg'], 'geodirectory' );
            }

            // Validation message
            if ( ! empty( $cf['validation_msg'] ) ) {
                $validation_text = __( $cf['validation_msg'], 'geodirectory' );
            }

            if ( ! empty( $cf['is_required'] ) && empty( $validation_text ) ) {
                $validation_text = wp_sprintf( __( '%s is a required field.', 'geodirectory' ), $frontend_title );
            }

            // Set validation message
           if ( ! empty( $validation_text ) ) {
                $extra_attributes['oninvalid'] = 'try{jQuery(this).closest(".geodir-files-dropbox").find(".geodir-req-err").remove();jQuery(this).closest(".geodir-files-dropbox").append(\'<div class="alert alert-danger geodir-req-err mb-0 mt-1" role="alert">' . esc_attr( addslashes( $validation_text ) ) . '</div>\');this.setCustomValidity(\'' . esc_attr( addslashes( $validation_text ) ) . '\')}catch(e){}';
                $extra_attributes['onchange'] = 'try{jQuery(this).closest(".geodir-files-dropbox").find(".geodir-req-err").remove();if(!this.value){jQuery(this).closest(".geodir-files-dropbox").append(\'<div class="alert alert-danger geodir-req-err mb-0 mt-1" role="alert">' . esc_attr( addslashes( $validation_text ) ) . '</div>\');}this.setCustomValidity(\'\')}catch(e){}';
            }

            $extra_attributes = class_exists( "AUI_Component_Helper" ) ? AUI_Component_Helper::extra_attributes( $extra_attributes ) : '';

            // admin only
            $admin_only = geodir_cfi_admin_only($cf);
            $conditional_attrs = geodir_conditional_field_attrs( $cf, $cf['name'], 'hidden' );
            ?>
            <div id="<?php echo $cf['name']; ?>_row" class="<?php if ( $cf['is_required'] ) {echo 'required_field';} ?> <?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row"<?php echo $conditional_attrs; ?>>
                <label for="<?php echo $id; ?>" class="<?php echo $horizontal ? '  col-sm-2 col-form-label' : '';?>">
                    <?php
                    echo ( trim( $frontend_title ) ) ? esc_html( $frontend_title ) : '&nbsp;'; echo $admin_only;?>
                    <?php if ( $cf['is_required'] ) {
                        echo '<span class="text-danger">*</span>';
                    } ?>
                </label>
                <?php
                if($horizontal){echo "<div class='col-sm-10'>";}
                echo class_exists("AUI_Component_Helper") ? AUI_Component_Helper::help_text(__( $cf['desc'], 'geodirectory' )) : '';
                if($horizontal){echo "</div>";}

                // params for file upload
                $is_required = $cf['is_required'];

                if($horizontal){echo $aui_bs5 ? "<div class='w-100'>" : "<div class='mx-3 w-100'>";}
                // the file upload template
                echo geodir_get_template_html( "bootstrap/file-upload.php", array(
                    'id'                  => $id,
                    'is_required'         => $is_required,
                    'files'               => $files,
                    'image_limit'         => $image_limit,
                    'total_files'         => $total_files,
                    'allowed_file_types'  => $allowed_file_types,
                    'display_file_types'  => $display_file_types,
                    'multiple'            => $multiple,
                    'extra_attributes'    => $extra_attributes
                ) );
                if($horizontal){echo "</div>";}
            ?></div><?php
        }
        $html = ob_get_clean();
    }

    return $html;
}
add_filter('geodir_custom_field_input_images','geodir_cfi_files',10,2);
add_filter('geodir_custom_field_input_file','geodir_cfi_files',10,2);

function geodir_input_parse_year_range( $range ) {
	$current_year = (int) date_i18n( 'Y' );

	$year_range = array(
		'min_year' => 0,
		'max_year' => 0
	);

	$_range = explode( ":", trim( $range ) );

	if ( isset( $_range[0] ) ) {
		$year_range['min_year'] = geodir_input_parse_year( $_range[0] );
	}

	if ( isset( $_range[1] ) ) {
		$year_range['max_year'] = geodir_input_parse_year( $_range[1] );
	}

	if ( ! empty( $year_range['min_year'] ) && ! empty( $year_range['max_year'] ) ) {
		$_year_range = array_values( $year_range );
		$year_range['min_year'] = min( $_year_range );
		$year_range['max_year'] = max( $_year_range );
	} else if ( ! empty( $year_range['min_year'] ) && empty( $year_range['max_year'] ) && (int) $year_range['min_year'] > $current_year ) {
		$year_range['max_year'] = $year_range['min_year'];
		$year_range['min_year'] = 0;
	} else if ( empty( $year_range['min_year'] ) && ! empty( $year_range['max_year'] ) && $year_range['max_year'] < $current_year ) {
		$year_range['min_year'] = $year_range['max_year'];
		$year_range['max_year'] = 0;
	}

	return $year_range;
}

function geodir_input_parse_year( $range ) {
	$current_year = (int) date_i18n( 'Y' );

	$range = str_replace( "c", $current_year, $range );
	$year = 0;

	if ( strpos( $range, '-' ) !== false ) {
		$_year = explode( "-", $range );

		$min = trim( $_year[0] );
		$diff = trim( $_year[1] );

		$year = strlen( $min ) == 4 ? $min : $current_year;
		$diff = trim( $_year[1] ) !== "" ? $_year[1] : "";

		if ( $diff !== "" ) {
			$year = $year - (int) $diff;
		}
	} else if ( strpos( $range, '+' ) !== false ) {
		$_year = explode( "+", $range );

		$min = trim( $_year[0] );
		$diff = trim( $_year[1] );

		$year = strlen( $min ) == 4 ? $min : $current_year;
		$diff = trim( $_year[1] ) !== "" ? $_year[1] : "";

		if ( $diff !== "" ) {
			$year = $year + (int) $diff;
		}
	} else {
		$_year = trim( $range );

		if ( $_year !== "" ) {
			if ( strlen( $_year ) == 4 ) {
				$year = (int) $_year;
			} else {
				$year = $current_year + (int) $_year;
			}
		}
	}

	if ( strlen( $year ) != 4 ) {
		$year = 0;
	}

	return $year;
}

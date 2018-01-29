/**
 * Functions for saving and updating listings.
 */


/**
 * Document load functions
 */
jQuery(function() {
    console.log( "ready!" );
    // Start polling the form for auto saves
    geodir_auto_save_poll(geodir_get_form_data());

    /// check validation on blur
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("[field_type]:visible, .editor textarea").blur(function () {
        geodir_validate_field(this);
    });

    // Check for validation on click for checkbox, radio
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("input[type='checkbox'],input[type='radio']").click(function () {
        geodir_validate_field(this);
    });

});

/**
 * Prevent navigation away if there are unsaved changes.
 */
var geodir_changes_made = false;
window.onbeforeunload = function() {
    return geodir_changes_made ? "You may lose changes if you navigate away now!" : null; // @todo make translatable
};

/**
 * Poll the form looking for changes every 10 seconds, if we detect a change then auto save
 *
 * @param old_form_data
 */
function geodir_auto_save_poll(old_form_data){
    if(jQuery("#geodirectory-add-post").length){
        setTimeout(function(){
            // only save if the forum data has changed
            if(old_form_data != geodir_get_form_data()){
                console.log('form has changed');
                geodir_auto_save_post();
                geodir_changes_made = true; // flag changes have been made
            }
            geodir_auto_save_poll(geodir_get_form_data()); // run the function again.
        }, 10000);
    }
}

/**
 * Saves the post in the background via ajax.
 */
function geodir_auto_save_post(){
    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_auto_save_post";

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('auto saved');
            }else{
                console.log('auto save failed');
            }
        }
    });
}

/**
 * Get all the form data.
 *
 * @returns {*}
 */
function geodir_get_form_data(){
    return jQuery("#geodirectory-add-post").serialize();
}

/**
 * Save the post and redirect to where needed.
 */
function geodir_save_post(){
    var form_data = geodir_get_form_data();

    console.log(form_data);

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('saved');
                console.log(data.data);
                geodir_changes_made = false; // set the changes flag to false.
                jQuery('.gd-notification').remove(); // remove current notes
                jQuery('#geodirectory-add-post').replaceWith(data.data); // remove the form and replae with the notification
                jQuery(window).scrollTop(jQuery('.gd-notification').offset().top-100);// scroll to new notification

                return true;
            }else{
                console.log('save failed');
                return false;
            }
        }
    });

}

/**
 * Delete a post revision.
 */
function geodir_delete_revision(){

    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_delete_revision";

    jQuery.ajax({
        type: "POST",
        url: geodirectory_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data)
        {
            if(data.success){
                console.log('deleted');
                location.reload();
                return true;
            }else{
                console.log('delete failed');
                alert(data.data);
                return false;
            }
        }
    });
}

/**
 * Save the post on preview link click.
 */
jQuery( ".geodir_preview_button" ).click(function() {
    geodir_auto_save_post();
    $form = jQuery("#geodirectory-add-post");

    return geodir_validate_submit($form);
});

/**
 * Save the post via ajax.
 */
jQuery("#geodirectory-add-post").submit(function(e) {

    $valid = geodir_validate_submit(this);

    if($valid){
        $result = geodir_save_post();
    }

    e.preventDefault(); // avoid to execute the actual submit of the form.
});



/**
 * Validate all required fields before submit.
 *
 * @returns {boolean}
 */
function geodir_validate_submit(form){
    var is_validate = true;

    jQuery(form).find(".required_field:visible").each(function () {
        jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload").each(function () {

            // if (jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')) {
            //     var chosen_ele = jQuery(this);
            //     jQuery('#' + jQuery(this).attr('id') + '_chzn').mouseleave(function () {
            //         geodir_validate_field(chosen_ele);
            //     });
            // }

            if (!geodir_validate_field(this)){
                is_validate = false;
                //console.log(false);
            }else{
                //console.log(true);
            }

            //console.log(this);


        });


    });


    if (is_validate) {
        return true;
    } else {

        jQuery(window).scrollTop(jQuery(".geodir_message_error:visible:first").closest('.required_field').offset().top);
        return false;
    }
}


/**
 * Validate add listing fields.
 *
 * @param field
 * @returns {boolean}
 */
function geodir_validate_field(field) {

    var is_error = true;
    switch (jQuery(field).attr('field_type')) {
        case 'radio':
        case 'checkbox':

            if (jQuery(field).closest('.required_field').find('#cat_limit').length) {

                var cat_limit = jQuery(field).closest('.required_field').find('#cat_limit').attr('cat_limit');
                var cat_msg = jQuery(field).closest('.required_field').find('#cat_limit').val();

                if (jQuery(field).closest('.required_field').find(":checked").length > cat_limit && cat_limit > 0) {

                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(cat_msg);
                    return false;

                }

            }

            if (jQuery(field).closest('.required_field').find(":checked").length > 0) {
                is_error = false;
            }
            break;

        case 'select':
            if (jQuery(field).closest('.geodir_form_row').find(".geodir_taxonomy_field").length > 0 && jQuery(field).closest('.geodir_form_row').find("#post_category").length > 0) {
                if (jQuery(field).closest('.geodir_form_row').find("#post_category").val() != '') {
                    is_error = false;
                }
            } else {
                if (jQuery(field).find("option:selected").length > 0 && jQuery(field).find("option:selected").val() != '') {
                    is_error = false;
                }
            }
            break;

        case 'multiselect':
            if (jQuery(field).closest('.required_field').find('#cat_limit').length) {

                var cat_limit = jQuery(field).closest('.required_field').find('#cat_limit').attr('cat_limit');
                var cat_msg = jQuery(field).closest('.required_field').find('#cat_limit').val();

                if (jQuery(field).find("option:selected").length > cat_limit && cat_limit > 0) {
                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(cat_msg);
                    return false;

                }

            }

            if (jQuery(field).find("option:selected").length > 0) {
                is_error = false;
            }


            break;

        case 'email':
            var filter = /^[a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
            if (field.value != '' && filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'url':
            var filter = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
            if (field.value != '' && filter.test(field.value)) {
                is_error = false;
            }
            break;

        case 'editor':
            if (jQuery('#' + jQuery(field).attr('field_id')).val() != '') {
                is_error = false;
            }
            break;

        case 'datepicker':
        case 'time':
        case 'text':
        case 'textarea':
            if (field.value != '') {
                is_error = false;
            }
            break;

        case 'address':

            if (jQuery(field).attr('id') == 'post_latitude' || jQuery(field).attr('id') == 'post_longitude') {

                if (/^[0-90\-.]*$/.test(field.value) == true && field.value != '') {
                    is_error = false;
                } else {

                    var error_msg = geodir_params.latitude_error_msg;
                    if (jQuery(field).attr('id') == 'post_longitude')
                        error_msg = geodir_params.longgitude_error_msg;

                    jQuery(field).closest('.required_field').find('.geodir_message_error').show();
                    jQuery(field).closest('.required_field').find('.geodir_message_error').html(error_msg);

                }

            } else {

                if (field.value != '')
                    is_error = false;
            }

            break;

        default:
            if (field.value != '') {
                is_error = false;
            }
            break;

    }


    if (is_error) {
        if (jQuery(field).closest('.required_field').find('span.geodir_message_error').html() == '') {
            jQuery(field).closest('.required_field').find('span.geodir_message_error').html(geodir_params.field_id_required)
        }

        jQuery(field).closest('.required_field').find('span.geodir_message_error').fadeIn();

        return false;
    } else {

        jQuery(field).closest('.required_field').find('span.geodir_message_error').html('');
        jQuery(field).closest('.required_field').find('span.geodir_message_error').fadeOut();

        return true;
    }
}




jQuery(document).ready(function () {


    //fire the toggle displays on advanced search show/hide
    jQuery(".gd-advanced-toggle").click(function () {
        setTimeout(function () {
            gd_toggle_switch_display();
        }, 100);

    });


    jQuery(".gd-form-builder-tab ul li a").click(function () {
        console.log(1);
        if (!jQuery(this).attr('id')) {
            return;
        }
        console.log(2);
        //var type = jQuery(this).attr('id').replace('gd-', '');
        var type = jQuery(this).data("field-type");
        var type_key = jQuery(this).data("field-type-key");
        var custom_type = jQuery(this).data("field-custom-type");
        var post_type = jQuery('#new_post_type').val();
        var id = 'new' + jQuery(".field_row_main ul.core li:last").index();
        var manage_field_type = jQuery(".manage_field_type").val();
        var gd_nonce = jQuery("#gd_new_field_nonce").val();
        console.log(3);
        console.log(manage_field_type);
        if (manage_field_type == 'general' || manage_field_type == 'cpt-sorting') {

            // check if there is an unsaved field
            if (jQuery('#licontainer_' + id).length) {
                alert(geodir_params.txt_save_other_setting);
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_" + id).offset().top
                }, 1000);
                return;
            }

            // close all other settings first
            jQuery('.core.ui-sortable li .field_frm').hide();

            if (manage_field_type == 'general') {
                var action = "geodir_get_custom_field_form";
            } else if (manage_field_type == 'cpt-sorting') {
                var action = "geodir_get_custom_field_sorting_form";
            }


            jQuery.get(ajaxurl + '?action=' + action, {
                field_type: type,
                field_type_key: type_key,
                listing_type: post_type,
                field_id: id,
                field_ins_upd: 'new',
                manage_field_type: manage_field_type,
                custom_type: custom_type,
                security: gd_nonce
            }, function (data) {

                if (jQuery('.field_row_main ul.core li').length > 0) {
                    jQuery('.field_row_main ul.core').append(data);
                } else {
                    jQuery('.field_row_main ul.core').html(data);
                }
                jQuery('#licontainer_' + id).find('#sort_order').val(parseInt(jQuery('#licontainer_' + id).index()) + 1);

                // int the new select2 boxes
                jQuery("select.geodir-select").trigger('geodir-select-init');
                jQuery("select.geodir-select-nostd").trigger('geodir-select-init');

                gd_show_hide('field_frm' + id);
                jQuery('html, body').animate({
                    scrollTop: jQuery("#licontainer_" + id).offset().top
                }, 1000);

                // init new tooltips
                gd_init_tooltips();

                // init the toggle displays
                gd_toggle_switch_display();

                // init the advanced settings toggle
                if(jQuery('.gd-advanced-toggle').hasClass('gda-hide')){
                    jQuery("#licontainer_" + id + " .gd-advanced-toggle").addClass("gda-hide");
                    jQuery("#licontainer_" + id + " .gd-advanced-setting, #default_location_set_address_button").addClass("gda-show");
                }
                init_advanced_settings();

                // Bind event
                if (manage_field_type == 'general') {
                    jQuery(document.body).trigger('geodir_on_get_custom_field', [{id: id, field: data}]);
                } else if (manage_field_type == 'cpt-sorting') {
                    jQuery(document.body).trigger('geodir_on_sort_custom_field', [{id: id, field: data}]);
                }
            });
            if (manage_field_type == 'sorting_options') {
                jQuery(this).closest('li').hide();
            }
        }
    });
    jQuery(".field_row_main ul.core").sortable({
        opacity: 0.8,
        cursor: 'move',
        placeholder: "ui-state-highlight",
        cancel: "input,label,select",
        update: function () {
            // var manage_field_type = jQuery(this).closest('#geodir-selected-fields').find(".manage_field_type").val();
            var manage_field_type = jQuery(".manage_field_type").val();

            //var order = jQuery(this).sortable("serialize") + '&update=update&manage_field_type=' + manage_field_type;
            var order = jQuery(this).sortable("serialize");


            // update the sort values on drag/drop
            if (manage_field_type == 'general') {
                gd_order_custom_fields(order);
            } else if (manage_field_type == 'cpt-sorting') {
                gd_order_custom_sort_fields(order);
            }
        }
    });

    //gd_toggle_display();
    gd_toggle_switch_display();
    jQuery('body').bind('geodir_on_save_custom_field', function (e, data) {
    });
    jQuery('body').bind('geodir_on_get_custom_field', function (e, data) {
    });
});

function gd_data_type_changed(obj, cont) {
    if (obj && cont) {
        jQuery('#licontainer_' + cont).find('.decimal-point-wrapper').hide();
        if (jQuery(obj).val() == 'FLOAT') {
            jQuery('#licontainer_' + cont).find('.decimal-point-wrapper').show();
        }

        if (jQuery(obj).val() == 'FLOAT' || jQuery(obj).val() == 'INT') {
            jQuery('#licontainer_' + cont).find('.gdcf-price-extra-set').show();

            if (jQuery('#licontainer_' + cont).find(".gdcf-price-extra-set input[name='extra[is_price]']:checked").val() == '1') {
                jQuery('#licontainer_' + cont).find('.gdcf-price-extra').show();
            }

        } else {
            jQuery('#licontainer_' + cont).find('.gdcf-price-extra-set').hide();
            jQuery('#licontainer_' + cont).find('.gdcf-price-extra').hide();
        }
    }
}

function gd_save_custom_field(id) {


    // we will add a html_var from the title input during validation
    if (jQuery('#licontainer_' + id + ' #frontend_title').length > 0) {

        var frontend_title = jQuery('#licontainer_' + id + ' #frontend_title').val();

        if (frontend_title == '') {

            alert(geodir_params.custom_field_not_blank_var);

            return false;
        }

    }

    var fieldrequest = jQuery('#licontainer_' + id).find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;


    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_save_custom_field',
        'type': 'POST',
        'data': request_data,
        'success': function (result) {

            var res = result;
            if (res.success == false) {
                alert(res.data);
            } else {
                jQuery('#licontainer_' + id).replaceWith(jQuery.trim(result));

                var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type=custom_fields';

                // jQuery.get(ajaxurl + '?action=geodir_ajax_action&create_field=true', order,
                //     function (theResponse) {
                //         //alert(theResponse);
                //     });

                jQuery('.field_frm').hide();
                var field_id = jQuery(result).find('#field_id').val();
                field_id = field_id ? field_id : id;
                jQuery(document.body).trigger('geodir_on_save_custom_field', [{id: field_id, field: res}]);
            }

            // int the new select2 boxes
            jQuery("select.geodir-select").trigger('geodir-select-init');
            jQuery("select.geodir-select-nostd").trigger('geodir-select-init');

            // init new tooltips
            gd_init_tooltips();

        }
    });


}


function gd_show_hide(id) {
    // if its a CPT setting then close the other settings first
    if (id.substring(0, 9) == "field_frm") {
        jQuery('.field_frm').not('#' + id).hide()
    }
    jQuery('#' + id).toggle();
}

function gd_show_hide_radio(id, sh, cl) {

    setTimeout(function () {
        gd_toggle_switch_display();
    }, 100);
    // if(sh=='hide'){
    //     jQuery( id ).closest( '.widefat' ).find('.'+cl).hide('fast');
    // }else{
    //     jQuery( id ).closest( '.widefat' ).find('.'+cl).show('fast');
    // }

}


function gd_delete_custom_field(id, nonce) {

    var confarmation = confirm(geodir_params.custom_field_delete);

    if (confarmation == true) {

        // if its a new field not yet added then we just dump it, no need to run ajax
        if (id.substring(0, 3) == "new") {
            jQuery('#licontainer_' + id).remove();
        } else {
            jQuery.get(ajaxurl + '?action=geodir_delete_custom_field', {
                    field_id: id,
                    field_ins_upd: 'delete',
                    security: nonce
                },
                function (data) {
                    var res = data;
                    if (!res.success) {
                        alert(res.data);
                    } else {
                        jQuery('#licontainer_' + id).remove();
                    }
                });
        }

    }

}

/**
 * Sort the custom fields when drag & dropped.
 *
 * @param order
 */
function gd_order_custom_fields(order) {
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    order = order + "&security=" + gd_nonce;
    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_order_custom_fields',
        'type': 'POST',
        'data': order,
        'success': function (result) {
            if (result.success == false) {
                alert(result.data);
            }
        }
    });
}

/**
 * Sort the custom sort fields when drag & dropped.
 *
 * @param order
 */
function gd_order_custom_sort_fields(order) {
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    order = order + "&security=" + gd_nonce;
    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_order_custom_sort_fields',
        'type': 'POST',
        'data': order,
        'success': function (result) {
            if (result.success == false) {
                alert(result.data);
            }
        }
    });
}


/* ================== CUSTOM SORT FIELDS =================== */

function gd_save_sort_field(id) {

    var fieldrequest = jQuery('#licontainer_' + id).find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;

    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_save_custom_sort_field',
        'type': 'POST',
        'data': request_data,
        'success': function (result) {

            jQuery('#licontainer_' + id).replaceWith(jQuery.trim(result));

            var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type=sorting_options';

            // check and set the default
            // jQuery.get(ajaxurl + '?action=geodir_ajax_action&create_field=true', order,
            //     function (theResponse) {

            jQuery('#licontainer_' + id).find('input[name="is_default"]').each(function () {
                if (jQuery(this).attr('checked') == 'checked') {
                    jQuery('.field_row_main').find('input[name="is_default"]').not(this).attr('checked', false);
                }
            });


            // });

            jQuery('.field_frm').hide();


        }
    });

}


function gd_delete_sort_field(id, nonce, obj) {

    var confarmation = confirm(geodir_params.custom_field_delete);

    if (confarmation == true) {
        // if its a new field not yet added then we just dump it, no need to run ajax
        if (id.substring(0, 3) == "new") {
            jQuery('#licontainer_' + id).remove();
        } else {

            jQuery.get(ajaxurl + '?action=geodir_delete_custom_sort_field', {
                    field_id: id,
                    _wpnonce: nonce
                },
                function (data) {
                    jQuery('#licontainer_' + id).remove();

                    var field_type = jQuery(obj).closest('li').find('#field_type').val();
                    var htmlvar_name = jQuery(obj).closest('li').find('#htmlvar_name').val();

                    jQuery('#gd-' + field_type + '-_-' + htmlvar_name).closest('li').show();

                });
        }

    }

}


/**
 * Hides selected fields if they are only supposed to show if a switch field is set to true.
 */
function gd_toggle_switch_display() {
    jQuery("[data-gdat-display-switch-set]").each(function () {


        var toggleClass = jQuery(this).data('gdat-display-switch-set');
        var switchValue = jQuery(this).find('input:checked').val();

        jQuery(this).parent().find('.' + toggleClass).each(function () {


            // if advanced item
            if (jQuery(this).hasClass('gd-advanced-setting')) {

                // if visible
                if (jQuery(this).hasClass('gda-show') && switchValue == 1) {
                    jQuery(this).show();
                } else {
                    jQuery(this).hide();
                }
            } else { // its not advanced but we still need to check if its should be shown
                // if visible
                if (switchValue == 1) {
                    jQuery(this).show();
                } else {
                    jQuery(this).hide();
                }
            }


        });


    });
}
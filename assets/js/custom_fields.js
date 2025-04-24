var gd_doing_field_auto_save = false;
jQuery(document).ready(function($) {
    //fire the toggle displays on advanced search show/hide
    jQuery(".gd-advanced-toggle").on("click", function() {
        setTimeout(function() {
            gd_toggle_switch_display();
        }, 100);
    });
    jQuery("#geodir-available-fields ul li a.gd-draggable-form-items").on("click", function() {
        if (!jQuery(this).attr('id')) {
            return;
        }
        // check if its a single use item and if its already in use.
        var type = jQuery(this).data("field-type");
        var type_key = jQuery(this).data("field-type-key");
        var custom_type = jQuery(this).data("field-custom-type");
        var post_type = jQuery('#gd_new_post_type').val();
        var id = 'new' + jQuery(".field_row_main ul.core li:last").index();
        var manage_field_type = jQuery(".manage_field_type").val();
        var gd_nonce = jQuery("#gd_new_field_nonce").val();
        var single_use = jQuery(this).data("field-single-use");
        if (single_use) {
            var is_used = false;
            jQuery('input[name^="htmlvar_name"]').each(function(i) {
                if (jQuery(this).val() == single_use) {
                    is_used = true;
                    alert(geodir_params.txt_single_use);
                    jQuery('#setName_new-1').trigger("click");
                }
            });
            if (is_used) {
                return false;
            }
        }
        if (manage_field_type == 'general' || manage_field_type == 'cpt-sorting') {
            //setName_16
            // check if there is an unsaved field
            if (jQuery('#setName_' + id).length) {
                alert(geodir_params.txt_save_other_setting);
                jQuery('html, body').animate({
                    scrollTop: jQuery("#setName_" + id).offset().top
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
            }, function(data) {
                if (jQuery('.field_row_main ul.dd-list li').length > 0) {
                    jQuery('.field_row_main ul.dd-list').append(data);
                } else {
                    jQuery('.field_row_main ul.dd-list').html(data);
                }
                jQuery('#setName_' + id).find('#sort_order').val(parseInt(jQuery('#setName_' + id).index()) + 1);
                // int the new select2 boxes
                jQuery("select.geodir-select").trigger('geodir-select-init');
                jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
                //gd_show_hide('field_frm' + id);
                jQuery("#setName_" + id + " .dd-form > .fa-caret-down").trigger("click");
                jQuery('.gd-tabs-sortable > li:last-child .dd-form').trigger('click');
                jQuery('html, body').animate({
                    scrollTop: jQuery("#setName_" + id).offset().top - (jQuery(window).height() * 0.8)
                }, 1000);
                // init new tooltips
                gd_init_tooltips();
                // init the toggle displays
                gd_toggle_switch_display();
                // init the advanced settings toggle
                // if (jQuery('.gd-advanced-toggle').hasClass('gda-hide')) {
                //     jQuery("#setName_" + id + " .gd-advanced-toggle").addClass("gda-hide");
                //     jQuery("#setName_" + id + " .gd-advanced-setting, #default_location_set_address_button").addClass("gda-show");
                // }
                init_advanced_settings();
                // Bind event
                if (manage_field_type == 'general') {
                    jQuery(document.body).trigger('geodir_on_get_custom_field', [{
                        id: id,
                        field: data
                    }]);
                } else if (manage_field_type == 'cpt-sorting') {
                    jQuery(document.body).trigger('geodir_on_sort_custom_field', [{
                        id: id,
                        field: data
                    }]);
                }
            });
            if (manage_field_type == 'sorting_options') {
                jQuery(this).closest('li').hide();
            }
        }
    });
    var gdDoingAutoSave = false;
    $(document).on('change', '#geodir-field-settings input,#geodir-field-settings select,#geodir-field-settings textarea', function(e) {
        if (!gdDoingAutoSave) {
            var $this = this;
            setTimeout(function() {
                gd_doing_field_auto_save = true;
                jQuery($this).closest('#geodir-field-settings').find('.card-footer #save').trigger("click");
                gdDoingAutoSave = false;
                //geodir_auto_save_custom_field($this, $);
            }, 250);
        }
        gdDoingAutoSave = e;
    });
    gd_toggle_switch_display();
    jQuery('body').on('geodir_on_save_custom_field', function(e, data) {});
    jQuery('body').on('geodir_on_get_custom_field', function(e, data) {});
    // init sort options
    gd_init_sort_options();
    // init custom fields settings
    gd_init_custom_fields_sortable();
    // init tabs layout settings
    gd_init_tabs_layout();
    /* Conditional Fields Init */
    geodir_field_init_conditional($);
});

function gd_data_type_changed(obj, cont) {
    var dtype, $el;
    if (obj && cont) {
        dtype = jQuery(obj).val();
        $el = jQuery('#setName_' + cont);
        jQuery('[data-setting="price_heading"]', $el).hide();
        jQuery('[data-setting="is_price"]', $el).hide();
        jQuery('[data-setting="currency_symbol"]', $el).hide();
        jQuery('[data-setting="currency_symbol_placement"]', $el).hide();
        jQuery('[data-setting="thousand_separator"]', $el).hide();
        jQuery('[data-setting="decimal_separator"]', $el).hide();
        jQuery('[data-setting="decimal_point"]', $el).hide();
        jQuery('[data-setting="decimal_display"]', $el).hide();
        if (dtype == 'INT' || dtype == 'FLOAT' || dtype == 'DECIMAL') {
            jQuery('[data-setting="price_heading"]', $el).show();
            jQuery('[data-setting="is_price"]', $el).show();
            jQuery('[data-setting="thousand_separator"]', $el).show();
            if (dtype == 'FLOAT' || dtype == 'DECIMAL') {
                jQuery('[data-setting="decimal_separator"]', $el).show();
                jQuery('[data-setting="decimal_point"]', $el).show();
                jQuery('[data-setting="decimal_display"]', $el).show();
            }
            if (jQuery('input[name="extra[is_price]"]', $el).is(':checked')) {
                jQuery('[data-setting="currency_symbol"]', $el).show();
                jQuery('[data-setting="currency_symbol_placement"]', $el).show();
            }
        }
    }
}

function gd_save_custom_field(id, ev) {
    // we will add a html_var from the title input during validation
    if (jQuery('#geodir-field-settings #frontend_title').length > 0) {
        var frontend_title = jQuery('#geodir-field-settings #frontend_title').val();
        if (frontend_title == '') {
            aui_toast('gd_save_custom_field_r', 'error', geodir_params.rating_error_msg, '', geodir_params.custom_field_not_blank_var);
            setTimeout(function() { // text change needs a delay as we just changed it
                jQuery('#geodir-field-settings #save').html(jQuery('#geodir-field-settings #save').data('save-text')).removeClass('disabled');
            }, 50);
            return false;
        }
    }
    var fieldrequest = jQuery('#geodir-field-settings .dd-setting').find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;
    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_save_custom_field',
        'type': 'POST',
        'data': request_data,
        'beforeSend': function(xhr, obj) {
            jQuery('.gd-form-settings-form [name="htmlvar_name"]').prop('readonly', true);
        },
        'success': function(result) {
            var res = result;
            if (res.success == false) {
                aui_toast('gd_save_custom_field_e', 'error', geodir_params.rating_error_msg, '', res.data);
                jQuery('#geodir-field-settings #save').html(jQuery('#geodir-field-settings #save').data('save-text')).removeClass('disabled');
            } else {
                jQuery('#setName_' + id).replaceWith(jQuery.trim(result));
                var field_id = jQuery(result).attr('id').replace("setName_", "");
                field_id = field_id ? field_id : id;
                jQuery(document.body).trigger('geodir_on_save_custom_field', [{
                    id: field_id,
                    field: res
                }]);
                jQuery('#geodir-field-settings #save').html(jQuery('#geodir-field-settings #save').data('save-text')).removeClass('disabled');
                var new_id = field_id;
                // if its an auto-save then we must do a little extra
                if (gd_doing_field_auto_save || (ev && ev.currentTarget)) {
                    gd_doing_field_auto_save = false;
                    if (id == 'new-1') {
                        jQuery('#geodir-field-settings #field_id').val(new_id);
                        jQuery('#geodir-field-settings #save[onclick]').attr('onclick', function(i, v) {
                            return v.replace(id, new_id);
                        });
                        var new_nonce = jQuery(result).data('field-nonce');
                        jQuery('#geodir-field-settings [name="security"]').val(new_nonce);
                        if (!jQuery('.gd-form-settings-form [name="htmlvar_name"]').val() && jQuery(result).data('htmlvar_name')) {
                            jQuery('.gd-form-settings-form [name="htmlvar_name"]').val(jQuery(result).data('htmlvar_name'));
                        }
                    }
                    jQuery('#setName_' + new_id + ' .dd-form').addClass('border-width-2 border-primary');
                } else {
                    aui_toast('gd_tabs_save_tab_success', 'success', geodir_params.txt_saved);
                    jQuery('#geodir-selected-fields .dd-form').removeClass('border-width-2 border-primary');
                    jQuery('#gd-fields-tab').tab('show');
                }
                // save order on save
                gd_tabs_save_order('geodir_order_custom_fields', true);
                aui_init();
            }
            if (!jQuery('.gd-form-settings-form [name="htmlvar_name"]').val()) {
                jQuery('.gd-form-settings-form [name="htmlvar_name"]').prop('readonly', false);
            }
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
    setTimeout(function() {
        $show = jQuery(id).is(":checked");
        if ($show) {
            console.log('checked');
            jQuery(id).closest('.dd-setting').find('.' + cl).show('fast');
        } else {
            console.log('unchecked');
            jQuery(id).closest('.dd-setting').find('.' + cl).hide('fast');
        }
    }, 100);
}
/**
 * Sort the custom fields when drag & dropped.
 *
 * @param order
 */
function gd_order_custom_fields(order) {
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    order = order + "&action=geodir_order_custom_fields&security=" + gd_nonce;
    jQuery.ajax({
        'url': geodir_params.gd_ajax_url,
        'type': 'POST',
        'data': order,
        'success': function(result) {
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
        'success': function(result) {
            if (result.success == false) {
                alert(result.data);
            }
        }
    });
}
/* CUSTOM SORT FIELDS */
function gd_delete_sort_field(id, nonce, obj) {
    aui_confirm(geodir_params.txt_are_you_sure, geodir_params.txt_delete, geodir_params.txt_cancel, true).then(function(confirmed) {
        if (confirmed) {
            // if its a new field not yet added then we just dump it, no need to run ajax
            if (id.substring(0, 3) == "new") {
                jQuery('#setName_' + id).remove();
            } else {
                jQuery.get(ajaxurl + '?action=geodir_delete_custom_sort_field', {
                        field_id: id,
                        _wpnonce: nonce
                    },
                    function(data) {
                        jQuery('#setName_' + id).remove();
                        var field_type = jQuery(obj).closest('li').find('#field_type').val();
                        var htmlvar_name = jQuery(obj).closest('li').find('#htmlvar_name').val();
                        jQuery('#gd-' + field_type + '-_-' + htmlvar_name).closest('li').show();
                        aui_toast('gd_delete_sort_field_success', 'success', geodir_params.txt_deleted);
                        jQuery('#gd-fields-tab').tab('show');
                    });
            }
        }
    });
}
/**
 * Hides selected fields if they are only supposed to show if a switch field is set to true.
 */
function gd_toggle_switch_display() {
    jQuery("[data-gdat-display-switch-set]").each(function() {
        var toggleClass = jQuery(this).data('gdat-display-switch-set');
        var switchValue = jQuery(this).find('input:checked').val();
        jQuery(this).parent().find('.' + toggleClass).each(function() {
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
/* SORT OPTIONS */
function gd_init_sort_options() {
    jQuery('.gd-sortable-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper: 'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function(event, ui) {
            gd_tabs_save_order('geodir_order_custom_sort_fields');
        }
    });
    // int the new select2 boxes
    jQuery("select.geodir-select").trigger('geodir-select-init');
    jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
}

function gd_save_sort_field(id, ev) {
    if(!gd_doing_field_auto_save) {
        jQuery('#geodir-field-settings #save').addClass('geodir-item-saved');
    } else {
        if (jQuery('#geodir-field-settings #save').length && jQuery('#geodir-field-settings #save').hasClass('geodir-item-saved')) {
            jQuery('#geodir-field-settings #save').removeClass('geodir-item-saved');
            return;
        }
    }
    var fieldrequest = jQuery('.gd-form-settings-view .dd-setting').find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;
    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_save_custom_sort_field',
        'type': 'POST',
        'data': request_data,
        'success': function(result) {
            if (result.success == false) {
                alert(result.data);
            } else {
                jQuery('#geodir-field-settings #save').html(jQuery('#geodir-field-settings #save').data('save-text')).removeClass('disabled');
                // if we are setting default sort then remvoe from all others.
                if (!jQuery(result).find('i.gd-is-default').hasClass('d-none')) {
                    jQuery('#geodir-selected-fields i.gd-is-default').addClass('d-none');
                    jQuery('#geodir-selected-fields .dd-setting').html(function(index, html) {
                        return html.replace('id="is_default"  value="1"  checked', 'id="is_default"  value="1" ');
                    });
                }
                jQuery('#setName_' + id).replaceWith(jQuery.trim(result));
                var new_id = jQuery(result).attr('id').replace("setName_", "");
                // if its an auto-save then we must do a little extra
                if (gd_doing_field_auto_save || (ev && ev.currentTarget)) {
                    gd_doing_field_auto_save = false;
                    if (id == 'new-1') {
                        jQuery('#geodir-field-settings #field_id').val(new_id);
                        jQuery('#geodir-field-settings #save[onclick]').attr('onclick', function(i, v) {
                            return v.replace(id, new_id);
                        });
                        var new_nonce = jQuery(result).data('field-nonce');
                        jQuery('#geodir-field-settings [name="_wpnonce"]').val(new_nonce);
                    }
                    jQuery('#setName_' + new_id + ' .dd-form').addClass('border-width-2 border-primary');
                } else {
                    aui_toast('gd_tabs_save_tab_success', 'success', geodir_params.txt_saved);
                    jQuery('#geodir-selected-fields .dd-form').removeClass('border-width-2 border-primary');
                    jQuery('#gd-fields-tab').tab('show');
                }
                gd_tabs_save_order('geodir_order_custom_sort_fields', true);
                aui_init();
            }
        }
    });
}
/* CUSTOM FIELDS */
function gd_init_custom_fields_sortable() {
    jQuery('.gd-custom-fields-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper: 'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function(event, ui) {
            gd_tabs_save_order('geodir_order_custom_fields');
        }
    });
    // int the new select2 boxes
    jQuery("select.geodir-select").trigger('geodir-select-init');
    jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
}

function gd_delete_custom_field(id, nonce) {
    // check if it has children, if so don't delete
    if (jQuery('#setName_' + id + ' ul li.dd-item').length) {
        alert(geodir_params.custom_field_delete_children);
    } else {
        aui_confirm(geodir_params.txt_are_you_sure, geodir_params.txt_delete, geodir_params.txt_cancel, true).then(function(confirmed) {
            if (confirmed) {
                // if its a new field not yet added then we just dump it, no need to run ajax
                if (id.substring(0, 3) == "new") {
                    jQuery('#setName_' + id).remove();
                } else {
                    jQuery.get(ajaxurl + '?action=geodir_delete_custom_field', {
                            field_id: id,
                            field_ins_upd: 'delete',
                            security: nonce
                        },
                        function(data) {
                            var res = data;
                            if (!res.success) {
                                alert(res.data);
                            } else {
                                jQuery('#setName_' + id).remove();
                                aui_toast('gd_delete_custom_field_success', 'success', geodir_params.txt_deleted);
                                jQuery('#gd-fields-tab').tab('show');
                                // save order on save
                                gd_tabs_save_order('geodir_order_custom_fields', true);
                            }
                        });
                }
            }
        });
    }
}
/* TABS LAYOUT */
function gd_init_tabs_layout() {
    jQuery('.gd-tabs-layout-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper: 'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function(event, ui) {
            gd_tabs_save_order('geodir_save_tabs_order');
        }
    });
    // int the new select2 boxes
    jQuery("select.geodir-select").trigger('geodir-select-init');
    jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
}
/**
 * Show the tab settings, closing all other open setting first.
 *
 * @param $this
 */
function gd_tabs_item_settings($this) {
    // if navigating away without save then remove
    if (jQuery('#setName_').length && jQuery($this).parent().attr("id") != 'setName_') {
        jQuery('#setName_').remove();
    } else if (jQuery('#setName_new-1').length && jQuery($this).parent().attr("id") != 'setName_new-1') {
        jQuery('#setName_new-1').remove();
    }
    // $settings = jQuery($this).parent().find('.dd-setting').first().clone();
    $settings = jQuery($this).parent().find('.dd-setting').first().html();
    $settings = jQuery('<div class="dd-setting">' + $settings + '</div>');
    $settings.removeClass('d-none');
    $id = $settings.find('[name="id"]').val();
    $type = $settings.find('[name="tab_type"]').val();
    if (jQuery($this).closest('ul').hasClass('dd-list') || $type == 'fieldset') {
        $settings.find('.alert-info').addClass('d-none');
    } else {
        $settings.find('[data-argument="gd-tab-name-' + $id + '"],[data-argument="gd-tab-icon-' + $id + '"]').addClass('d-none');
    }
    jQuery('#geodir-selected-fields .dd-form').removeClass('border-width-2 border-primary');
    jQuery($this).parent().find('.dd-form').first().addClass('border-width-2 border-primary');
    jQuery('#geodir-field-settings .card-body').html($settings);
    jQuery('#geodir-field-settings .card-body').find('.iconpicker-input').removeClass('iconpicker-input');
    jQuery('#gd-field-settings-tab').tab('show');
    jQuery('#geodir-field-settings .card-footer').html('');
    jQuery('#geodir-field-settings .gd-tab-actions').detach().appendTo('#geodir-field-settings .card-footer');
    init_advanced_settings_field();
    // init iconpicker
    // aui_init_iconpicker();
    aui_init();
    /* Conditional Fields Init */
    geodir_field_init_conditional(jQuery);
    // select2
    var select2_args = jQuery.extend({}, aui_select2_locale());
    jQuery(".gd-form-settings-view select.aui-select2").select2(select2_args);
    // Conditional Fields on change
    jQuery(".gd-form-settings-form").off('change').on("change", function() {
        try {
            aui_conditional_fields('.gd-form-settings-form');
            console.log('on-change');
        } catch (err) {
            console.log(err.message);
        }
    });
    // Conditional Fields on load
    try {
        aui_conditional_fields(".gd-form-settings-form");
    } catch (err) {
        console.log(err.message);
    }
}

function gd_tabs_add_tab($this) {
    // check if there is an unsaved field
    if (jQuery('#setName_').length) {
        alert(geodir_params.txt_save_other_setting);
        jQuery('html, body').animate({
            scrollTop: jQuery("#setName_").offset().top - (jQuery(window).height() * 0.8)
        }, 1000);
        return;
    }
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    var $post_type = jQuery("#gd_new_post_type").val();
    var $tab_layout = jQuery($this).data('tab_layout');
    var $tab_type = jQuery($this).data('tab_type');
    var $tab_name = jQuery($this).data('tab_name');
    var $tab_icon = jQuery($this).data('tab_icon');
    var $tab_key = jQuery($this).data('tab_key');
    var $tab_content = jQuery($this).data('tab_content');
    var data = {
        'action': 'geodir_get_tabs_form',
        'security': gd_nonce,
        'post_type': $post_type,
        'tab_layout': $tab_layout,
        'tab_name': $tab_name,
        'tab_type': $tab_type,
        'tab_icon': $tab_icon,
        'tab_key': $tab_key,
        'tab_content': $tab_content
    };
    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function(result) {
            console.log(result);
            if (result.success) {
                jQuery('.gd-tabs-sortable').append(result.data);
                gd_init_tabs_layout();
                jQuery('.gd-tabs-sortable > li:last-child .dd-form').trigger('click');
                jQuery('html, body').animate({
                    scrollTop: jQuery("#setName_").offset().top
                }, 1000);
            } else {
                alert("something went wrong");
            }
        }
    });
}

function gd_tabs_save_tab($this, ev) {
    var $form = jQuery($this).closest("#geodir-field-settings");
    var $id = jQuery($form).find("input[name='id']").val();
    console.log($form.find("select, textarea, input").serialize());
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    var data = $form.find("select, textarea, input").serialize() + "&security=" + gd_nonce + "&action=geodir_save_tab_item";
    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function(result) {
            console.log(result);
            if (result.success) {
                jQuery('#geodir-field-settings #save').html(jQuery('#geodir-field-settings #save').data('save-text')).removeClass('disabled');
                var $li = jQuery('#geodir-selected-fields #setName_' + $id);
                jQuery($li).replaceWith(result.data);
                var new_id = jQuery(result.data).attr('id').replace("setName_", "");
                // if its an auto-save then we must do a little extra
                if (gd_doing_field_auto_save || (ev && ev.currentTarget)) {
                    gd_doing_field_auto_save = false;
                    if ($id == '') {
                        jQuery('#geodir-field-settings [name="id"]').val(new_id);
                        // var new_nonce = jQuery(result).data('field-nonce');
                        // jQuery('#geodir-field-settings [name="_wpnonce"]').val(new_nonce);
                    }
                    jQuery('#setName_' + new_id + ' .dd-form').addClass('border-width-2 border-primary');
                } else {
                    aui_toast('gd_tabs_save_tab_success', 'success', geodir_params.txt_saved);
                    jQuery('#geodir-selected-fields .dd-form').removeClass('border-width-2 border-primary');
                    jQuery('#gd-fields-tab').tab('show');
                }
                gd_init_tabs_layout();
                gd_tabs_save_order('geodir_save_tabs_order', true);
            } else {
                aui_toast('gd_tabs_save_tab_e', 'error', geodir_params.rating_error_msg, '', result.data);
            }
        }
    });
}

function gd_tabs_close_settings($this) {
    jQuery('#gd-fields-tab').tab('show');
    jQuery('#geodir-selected-fields .dd-form').removeClass('border-width-2 border-primary');
    // if not saved then remove
    $id = jQuery('#geodir-field-settings').find('[name="id"],[name="field_id"]').val();
    if (!$id || $id == 'new-1') {
        jQuery('#setName_,#setName_new-1').remove();
    }
}

function gd_tabs_delete_tab($this) {
    jQuery('#gd-fields-tab').tab('show');
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    var $post_type = jQuery("#gd_new_post_type").val();
    var $tab_id = jQuery($this).closest("li").data('id');
    if (!$tab_id) {
        jQuery($this).closest("li").remove();
        return;
    }
    aui_confirm(geodir_params.txt_are_you_sure, geodir_params.txt_delete, geodir_params.txt_cancel, true).then(function(confirmed) {
        if (confirmed) {
            //alert($tab_id);return;
            var data = {
                'action': 'geodir_delete_tab',
                'security': gd_nonce,
                'post_type': $post_type,
                'tab_id': $tab_id
            };
            jQuery.ajax({
                'url': geodir_params.gd_ajax_url,
                'type': 'POST',
                'data': data,
                'success': function(result) {
                    console.log(result);
                    if (result.success) {
                        jQuery($this).closest("li").remove();
                        gd_init_tabs_layout();
                        aui_toast('gd_tabs_delete_tab_success', 'success', geodir_params.txt_deleted);
                    } else {
                        alert(result.data);
                    }
                }
            });
        }
    });
}
/**
 * Save the order of the items to the DB.
 */
function gd_tabs_save_order($action, $hide_success) {
    $tabs = jQuery('.gd-tabs-sortable').nestedSortable('toArray', {
        startDepthCount: 0
    });
    console.log($tabs);
    var $order = {};
    jQuery.each($tabs, function(index, tab) {
        if (tab.id) {
            $order[index] = {
                id: tab.id,
                tab_level: tab.depth,
                tab_parent: tab.parent_id
            };
        }
    });
    console.log($order);
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    var data = {
        'action': $action,
        'security': gd_nonce,
        'tabs': $order
    };
    jQuery.ajax({
        'url': geodir_params.gd_ajax_url,
        'type': 'POST',
        'data': data,
        'success': function(result) {
            console.log(result);
            if (result.success) {
                if (!$hide_success) {
                    aui_toast('gd_tabs_save_order_success', 'success', geodir_params.txt_order_saved);
                }
            } else {
                aui_toast('gd_tabs_save_order_e', 'error', geodir_params.rating_error_msg, '', result.data);
            }
        }
    });
}

function geodir_auto_save_custom_field(el, jQ) {
    var $li = jQuery(el).closest('.dd-setting'),
        title = jQuery('[name="frontend_title"]', $li).val(),
        fData, data, $btn, fieldId, attr;
    title = title.trim();
    if (!title) {
        return;
    }
    $btn = jQuery("[name='save']", $li);
    fData = jQuery("input,select,textarea", $li).serialize();
    data = 'create_field=true&field_ins_upd=submit&' + fData;
    if ($li.hasClass('geodir-cf-saving')) {
        return;
    }
    jQuery.ajax({
            url: ajaxurl + '?action=geodir_auto_save_custom_field',
            type: 'POST',
            data: data,
            dataType: 'json',
            beforeSend: function(xhr, obj) {
                jQuery('[name="sort_order"]', $li).val(parseInt($li.index()) + 1);
                jQuery('[name="htmlvar_name"]', $li).prop('readonly', true);
                $li.removeClass("geodir-cf-saved").addClass("geodir-cf-saving");
                $btn.prop("disabled", true);
                jQuery(".geodir-cf-status,.geodir-cf-tstatus", $li).remove();
                jQuery(".dd-setting", $li).append('<span class="geodir-cf-tstatus"><i class="fas fa-sync fa-spin" aria-hidden="true"></i> ' + geodir_params.txt_saving + '</span>');
                $btn.after('<span class="geodir-cf-status"><i class="fas fa-sync fa-spin" aria-hidden="true"></i> ' + geodir_params.txt_saving + '</span>');
            }
        })
        .done(function(data, textStatus, jqXHR) {
            if (typeof data == 'object' && data.success && data.data.field_id) {
                if (data.data.field_icon) {
                    jQuery('.dd-handle .dd-icon i', $li).prop("class", data.data.field_icon);
                }
                if (data.data.admin_title) {
                    jQuery('.dd-handle .dd-title', $li).text(data.data.admin_title);
                }
                if (jQuery('[name="field_id"]', $li).val() == 'new-1') {
                    fieldId = data.data.field_id;
                    jQuery('[name="field_id"]', $li).val(fieldId);
                    jQuery('[name="htmlvar_name"]', $li).val(data.data.htmlvar_name);
                    jQuery('[name="security"]', $li).val(data.data.nonce);
                    $li.prop("id", "setName_" + fieldId);
                    $li.prop("data-select2-id", "setName_" + fieldId);
                    jQuery('[name="admin_title"]', $li).prop("id", "gd-admin-title-" + fieldId);
                    attr = jQuery('[name="data_type"]', $li).attr("onchange");
                    if (attr) {
                        jQuery('[name="data_type"]', $li).attr("onchange", attr.replace("new-1", fieldId));
                    }
                    attr = jQuery('[name="save"]', $li).attr("onclick");
                    if (attr) {
                        jQuery('[name="save"]', $li).attr("onclick", attr.replace("new-1", fieldId));
                    }
                    jQuery('a.item-delete', $li).attr("onclick", "gd_delete_custom_field('" + fieldId + "','" + data.data.nonce + "');return false;");
                }
                $li.addClass("geodir-cf-saved");
                jQuery(".geodir-cf-status", $li).html('<i class="fas fa-check-circle" aria-hidden="true"></i> ' + geodir_params.txt_saved);
            } else {
                jQuery(".geodir-cf-status", $li).html('');
            }
        })
        .always(function(data, textStatus, jqXHR) {
            $btn.prop("disabled", false);
            $li.removeClass("geodir-cf-saving");
            jQuery(".geodir-cf-tstatus", $li).remove();
            if (!jQuery('[name="htmlvar_name"]', $li).val()) {
                jQuery('[name="htmlvar_name"]', $li).prop('readonly', false);
            }
        });
}
/**
 * Init conditional fields.
 */
function geodir_field_init_conditional($) {
    if (!$('#geodir_conditional_fields').length) {
        return;
    }
    $(document.body).off('click', '.geodir-conditional-add').on('click', '.geodir-conditional-add', function() {
        geodir_field_add_condition(this);
    });
    $(document.body).off('click', '.geodir-conditional-remove').on('click', '.geodir-conditional-remove', function() {
        geodir_field_remove_condition(this);
    });
    $(document.body).off('click', '[data-setting="conditional_fields_heading"]').on('click', '[data-setting="conditional_fields_heading"]', function() {
        if ($(this).hasClass('geodir-con-fields-open')) {
            $(this).removeClass('geodir-con-fields-open').addClass('geodir-con-fields-hidden');
            // $(this).closest('.dd-setting').find('#geodir_conditional_fields').hide();
        } else {
            $(this).removeClass('geodir-con-fields-hidden').addClass('geodir-con-fields-open');
            // $(this).closest('.dd-setting').find('#geodir_conditional_fields').show();
        }
    });
}
/**
 * Add field condition.
 */
function geodir_field_add_condition(el) {
    if (jQuery(el).hasClass('disabled')) {
        return false;
    }
    var $cont = jQuery(el).closest('#geodir_conditional_fields'),
        $items = $cont.find('.geodir-conditional-items');
    template = $cont.find('.geodir-conditional-template').html();
    jQuery(template).find('.geodir-conditional-el').val('');
    $items.append(template);
    geodir_field_refresh_conditions($items);
}
/**
 * Remove field condition.
 */
function geodir_field_remove_condition(el) {
    var $fields = jQuery(el).closest('#geodir_conditional_fields'),
        $items = $fields.find('.geodir-conditional-items');;
    jQuery(el).closest('.geodir-conditional-row').remove();
    geodir_field_refresh_conditions($items);
    $fields.find('.geodir-conditional-template .geodir-conditional-action').trigger('change');
}
/**
 * Refresh conditions.
 */
function geodir_field_refresh_conditions($items) {
    if ($items.find('.geodir-conditional-row').length) {
        $items.find('.geodir-conditional-row').each(function(i) {
            jQuery(this).attr('data-condition-index', i);
            jQuery(this).find('.geodir-conditional-action').prop('id', 'conditional_action_' + i).prop('name', 'conditional_fields[' + i + '][action]');
            jQuery(this).find('.geodir-conditional-field').prop('id', 'conditional_field_' + i).prop('name', 'conditional_fields[' + i + '][field]');
            jQuery(this).find('.geodir-conditional-condition').prop('id', 'conditional_condition_' + i).prop('name', 'conditional_fields[' + i + '][condition]');
            jQuery(this).find('.geodir-conditional-value').prop('id', 'conditional_value_' + i).prop('name', 'conditional_fields[' + i + '][value]');
        });
    }
}
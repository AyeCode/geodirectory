jQuery(document).ready(function () {


    //fire the toggle displays on advanced search show/hide
    jQuery(".gd-advanced-toggle").click(function () {
        setTimeout(function () {
            gd_toggle_switch_display();
        }, 100);

    });


    jQuery(".gd-form-builder-tab ul li.gd-cf-tooltip-wrap a").click(function () {
        console.log(1);
        if (!jQuery(this).attr('id')) {
            return;
        }

        // check if its a single use item and if its already in use.



        console.log(2);
        //var type = jQuery(this).attr('id').replace('gd-', '');
        var type = jQuery(this).data("field-type");
        var type_key = jQuery(this).data("field-type-key");
        var custom_type = jQuery(this).data("field-custom-type");
        var post_type = jQuery('#gd_new_post_type').val();
        var id = 'new' + jQuery(".field_row_main ul.core li:last").index();
        var manage_field_type = jQuery(".manage_field_type").val();
        var gd_nonce = jQuery("#gd_new_field_nonce").val();
        var single_use = jQuery(this).data("field-single-use");
        console.log(3);

        if(single_use){
            console.log('single use');
            var is_used = false;
            jQuery('input[name^="htmlvar_name"]').each(function(i){
                if(jQuery(this).val() == single_use){
                    is_used = true;
                    alert(geodir_params.txt_single_use);
                }
            });
            if(is_used){
                return false;
            }
        }

        console.log(manage_field_type);
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
            }, function (data) {

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
                jQuery('html, body').animate({
                    scrollTop: jQuery("#setName_" + id).offset().top
                }, 1000);

                // init new tooltips
                gd_init_tooltips();

                // init the toggle displays
                gd_toggle_switch_display();

                // init the advanced settings toggle
                if(jQuery('.gd-advanced-toggle').hasClass('gda-hide')){
                    jQuery("#setName_" + id + " .gd-advanced-toggle").addClass("gda-hide");
                    jQuery("#setName_" + id + " .gd-advanced-setting, #default_location_set_address_button").addClass("gda-show");
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


    // jQuery(".field_row_main ul.core").sortable({
    //     opacity: 0.8,
    //     cursor: 'move',
    //     placeholder: "ui-state-highlight",
    //     cancel: "input,label,select",
    //     update: function () {
    //         // var manage_field_type = jQuery(this).closest('#geodir-selected-fields').find(".manage_field_type").val();
    //         var manage_field_type = jQuery(".manage_field_type").val();
    //
    //         //var order = jQuery(this).sortable("serialize") + '&update=update&manage_field_type=' + manage_field_type;
    //         var order = jQuery(this).sortable("serialize");
    //
    //
    //         // update the sort values on drag/drop
    //         if (manage_field_type == 'general') {
    //             gd_order_custom_fields(order);
    //         } else if (manage_field_type == 'cpt-sorting') {
    //             gd_order_custom_sort_fields(order);
    //         }
    //     }
    // });

    //gd_toggle_display();
    gd_toggle_switch_display();
    jQuery('body').bind('geodir_on_save_custom_field', function (e, data) {
    });
    jQuery('body').bind('geodir_on_get_custom_field', function (e, data) {
    });


    // init sort options
    gd_init_sort_options();
    // init custom fields settings
    gd_init_custom_fields_sortable();
    // init tabs layout settings
    gd_init_tabs_layout();
});

function gd_data_type_changed(obj, cont) {
    if (obj && cont) {
        jQuery('#setName_' + cont).find('.decimal-point-wrapper').hide();
        if (jQuery(obj).val() == 'FLOAT') {
            jQuery('#setName_' + cont).find('.decimal-point-wrapper').show();
        }

        if (jQuery(obj).val() == 'FLOAT' || jQuery(obj).val() == 'INT') {
            jQuery('#setName_' + cont).find('.gdcf-price-extra-set').show();

            if (jQuery('#setName_' + cont).find(".gdcf-price-extra-set input[name='extra[is_price]']:checked").val() == '1') {
                jQuery('#setName_' + cont).find('.gdcf-price-extra').show();
            }

        } else {
            jQuery('#setName_' + cont).find('.gdcf-price-extra-set').hide();
            jQuery('#setName_' + cont).find('.gdcf-price-extra').hide();
        }
    }
}

function gd_save_custom_field(id) {


    // we will add a html_var from the title input during validation
    if (jQuery('#setName_' + id + ' #frontend_title').length > 0) {

        var frontend_title = jQuery('#setName_' + id + ' #frontend_title').val();

        if (frontend_title == '') {

            alert(geodir_params.custom_field_not_blank_var);

            return false;
        }

    }

    var fieldrequest = jQuery('#setName_' + id +' > .dd-form').find("select, textarea, input").serialize();
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
                jQuery('#setName_' + id).replaceWith(jQuery.trim(result));

                jQuery('.field_frm').hide();
                var field_id = jQuery(result).find('#field_id').val();
                field_id = field_id ? field_id : id;
                jQuery(document.body).trigger('geodir_on_save_custom_field', [{id: field_id, field: res}]);

                // save order on save
                gd_tabs_save_order('geodir_order_custom_fields');
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

    // setTimeout(function () {
    //     gd_toggle_switch_display();
    // }, 100);
    // if(sh=='hide'){
    //     jQuery( id ).closest( '.widefat' ).find('.'+cl).hide('fast');
    // }else{
    //     jQuery( id ).closest( '.widefat' ).find('.'+cl).show('fast');
    // }


    setTimeout(function () {
        $show = jQuery( id ).is(":checked");
        console.log( $show );

        if($show ){console.log( 'checked' );
            jQuery( id ).closest('.dd-setting').find( '.'+cl ).show('fast');
        }else{console.log( 'unchecked' );
            jQuery( id ).closest('.dd-setting').find( '.'+cl ).hide('fast');
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



function gd_delete_sort_field(id, nonce, obj) {

    var confarmation = confirm(geodir_params.custom_field_delete);

    if (confarmation == true) {
        // if its a new field not yet added then we just dump it, no need to run ajax
        if (id.substring(0, 3) == "new") {
            jQuery('#setName_' + id).remove();
        } else {

            jQuery.get(ajaxurl + '?action=geodir_delete_custom_sort_field', {
                    field_id: id,
                    _wpnonce: nonce
                },
                function (data) {
                    jQuery('#setName_' + id).remove();

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

///////////////////////////////////////////////////////////
//////////////// SORT OPTIONS /////////////////////////////
///////////////////////////////////////////////////////////
function gd_init_sort_options(){
    jQuery('.gd-sortable-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper:	'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function (event, ui) {
            gd_tabs_save_order('geodir_order_custom_sort_fields');
        }
    });
    // int the new select2 boxes
    jQuery("select.geodir-select").trigger('geodir-select-init');
    jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
}

function gd_save_sort_field(id) {

    var fieldrequest = jQuery('#setName_' + id + ' > .dd-form').find("select, textarea, input").serialize();
    var request_data = 'create_field=true&field_ins_upd=submit&' + fieldrequest;

    jQuery.ajax({
        'url': ajaxurl + '?action=geodir_save_custom_sort_field',
        'type': 'POST',
        'data': request_data,
        'success': function (result) {

            console.log(result);
            if (result.success == false) {
                alert(result.data);
            }else{
                jQuery('#setName_' + id).replaceWith(jQuery.trim(result));

                var order = jQuery(".field_row_main ul.core").sortable("serialize") + '&update=update&manage_field_type=sorting_options';
                
                jQuery('#setName_' + id).find('input[name="is_default"]').each(function () {
                    if (jQuery(this).attr('checked') == 'checked') {
                        jQuery('.field_row_main').find('input[name="is_default"]').not(this).attr('checked', false);
                    }
                });

                gd_tabs_save_order('geodir_order_custom_sort_fields');
            }

        }
    });

}
///////////////////////////////////////////////////////////
//////////////// CUSTOM FIELDS ////////////////////////////
///////////////////////////////////////////////////////////
function gd_init_custom_fields_sortable(){
    jQuery('.gd-custom-fields-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper:	'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function (event, ui) {
            gd_tabs_save_order('geodir_order_custom_fields');
        }
    });
    // int the new select2 boxes
    jQuery("select.geodir-select").trigger('geodir-select-init');
    jQuery("select.geodir-select-nostd").trigger('geodir-select-init');
}

function gd_delete_custom_field(id, nonce) {

    // check if it has children, if so don't delete
    if(jQuery('#setName_' + id + ' ul li.dd-item').length){
        alert(geodir_params.custom_field_delete_children);
    }else{
        var confarmation = confirm(geodir_params.custom_field_delete);

        if (confarmation == true) {

            // if its a new field not yet added then we just dump it, no need to run ajax
            if (id.substring(0, 3) == "new") {
                jQuery('#setName_' + id).remove();
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
                            jQuery('#setName_' + id).remove();
                            // save order on save
                            gd_tabs_save_order('geodir_order_custom_fields');
                        }
                    });
            }
        }
    }


}

///////////////////////////////////////////////////////////
//////////////// TABS LAYOUT //////////////////////////////
///////////////////////////////////////////////////////////

function gd_init_tabs_layout(){
    jQuery('.gd-tabs-layout-sortable').nestedSortable({
        maxLevels: 2,
        handle: 'div.dd-handle',
        items: 'li',
        //toleranceElement: 'form', // @todo remove this if problems
        disableNestingClass: 'mjs-nestedSortable-no-nesting',
        helper:	'clone',
        placeholder: 'placeholder',
        forcePlaceholderSize: true,
        listType: 'ul',
        update: function (event, ui) {
            gd_tabs_save_order('geodir_save_tabs_order');
            //console.log(jQuery('.gd-tabs-sortable').nestedSortable('serialize'));
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
function gd_tabs_item_settings($this){

    var is_open = !jQuery($this).parent().find('.dd-setting').is(':hidden');
    jQuery('.dd-setting').hide();
    if(is_open){
        jQuery($this).addClass("fa-caret-down").removeClass( "fa-caret-up");
        jQuery($this).parent().find('.dd-setting').hide().removeClass( "gd-tab-settings-open" );
    }else{
        jQuery($this).addClass("fa-caret-up").removeClass( "fa-caret-down");
        jQuery($this).parent().find('.dd-setting').show().addClass( "gd-tab-settings-open" );
    }


}

function gd_tabs_add_tab($this){

    // check if there is an unsaved field
    if (jQuery('#setName_').length) {
        alert(geodir_params.txt_save_other_setting);
        jQuery('html, body').animate({
            scrollTop: jQuery("#setName_").offset().top
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
        'action':           'geodir_get_tabs_form',
        'security':          gd_nonce,
        'post_type':         $post_type,
        'tab_layout':        $tab_layout,
        'tab_name':          $tab_name,
        'tab_type':          $tab_type,
        'tab_icon':          $tab_icon,
        'tab_key':           $tab_key,
        'tab_content':       $tab_content
    };

    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function (result) {

            console.log(result);
            if(result.success){
                jQuery('.gd-tabs-sortable').append(result.data);
                gd_init_tabs_layout();
                jQuery('.gd-tabs-sortable > li:last-child .fa-caret-down').trigger('click');
                jQuery('html, body').animate({
                    scrollTop: jQuery("#setName_").offset().top
                }, 1000);
            }else{
                alert("something went wrong");
            }
        }
    });
}

function gd_tabs_save_tab($this){

    var $form = jQuery($this).closest(".dd-form");
    console.log($form.find("select, textarea, input").serialize());
    var gd_nonce = jQuery("#gd_new_field_nonce").val();

    var data = $form.find("select, textarea, input").serialize() + "&security="+gd_nonce + "&action=geodir_save_tab_item";
    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function (result) {

            console.log(result);
            if(result.success){
                var $li = jQuery($form).closest("li");
                jQuery( $li ).replaceWith( result.data );
                gd_init_tabs_layout();
                gd_tabs_save_order('geodir_save_tabs_order');
            }else{
                alert(result.data);
            }
        }
    });
}

function gd_tabs_delete_tab($this){

    var $form = jQuery($this).closest(".dd-form");
    var $li = jQuery($form).closest("li");
    console.log($form.serialize());
    var gd_nonce = jQuery("#gd_new_field_nonce").val();
    var $post_type = jQuery("#gd_new_post_type").val();
    var $tab_id = jQuery($form).find("input[name=id]").val();

    if(!$tab_id){
        jQuery($form).closest("li").remove();
        return;
    }
    //alert($tab_id);return;

    var data = {
        'action':           'geodir_delete_tab',
        'security':          gd_nonce,
        'post_type':         $post_type,
        'tab_id':            $tab_id
    };

    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function (result) {

            console.log(result);
            if(result.success){
                var $li = jQuery($form).closest("li").remove();
                gd_init_tabs_layout();
            }else{
                alert(result.data);
            }
        }
    });
}

/**
 * Save the order of the items to the DB.
 */
function gd_tabs_save_order($action){
    $tabs = jQuery('.gd-tabs-sortable').nestedSortable('toArray', {startDepthCount: 0});
    console.log($tabs);
    var $order = {};
    jQuery.each($tabs, function( index, tab ) {
        console.log( index + ": " + tab );
        if(tab.id){
            $order[index] = {id:tab.id, tab_level: tab.depth,tab_parent: tab.parent_id};
        }
    });
    console.log($order);

    var gd_nonce = jQuery("#gd_new_field_nonce").val();

    var data = {
        'action':           $action,
        'security':          gd_nonce,
        'tabs':              $order
    };
    jQuery.ajax({
        'url': ajaxurl,
        'type': 'POST',
        'data': data,
        'success': function (result) {

            console.log(result);
            if(result.success){
                // var $li = jQuery($form).closest("li");
                // jQuery( $li ).replaceWith( result.data );
                // gd_init_tabs_layout();
            }else{
                alert(result.data);
            }
        }
    });
}
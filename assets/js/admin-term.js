jQuery(function($) {
    jQuery('#addtag #submit').on("click",function() {
        try {
            var mceField = typeof tinymce != 'undefined' && typeof tinymce.editors != 'undefined' && typeof tinymce.editors['ct_cat_top_desc'] == 'object' ? tinymce.editors['ct_cat_top_desc'] : null;
            if (mceField) {
                mceField.editorManager.triggerSave();
            }
        } catch (e) {
            console.log(e);
        }
        try {
            var mceField = typeof tinymce != 'undefined' && typeof tinymce.editors != 'undefined' && typeof tinymce.editors['ct_cat_bottom_desc'] == 'object' ? tinymce.editors['ct_cat_bottom_desc'] : null;
            if (mceField) {
                mceField.editorManager.triggerSave();
            }
        } catch (e) {
            console.log(e);
        }
    });
    
    jQuery(document).ajaxComplete(function(e, request, options) {
        if (request && 4 === request.readyState && 200 === request.status && options.data && 0 <= options.data.indexOf('action=add-tag')) {
            var res = wpAjax.parseAjaxResponse(request.responseXML, 'ajax-response');
            if (!res || res.errors) {
                return;
            }
            jQuery('#addtag .gd-term-form-field textarea').val('');
            jQuery('.gd-upload-img').each(function() {
                var $this = jQuery(this);
                var field = $this.data('field');
                if (field) {
                    jQuery('[name="' + field + '[id]"]').val('');
                    jQuery('[name="' + field + '[src]"]').val('');
                }
                jQuery('.gd-upload-display', $this).find('img').attr('src', geodir_params.img_spacer);
                jQuery('.gd_remove_image_button', $this).hide();
            });
            jQuery('#addtag .gd-term-form-field checkbox').prop('checked', false);
            jQuery('#addtag .gd-term-form-field select option').removeAttr('selected');
            jQuery("#addtag .gd-term-form-field iframe").contents().find("body").html('');
            jQuery("#addtag .gd-term-form-field .select2-hidden-accessible").val(null).trigger("change");
            jQuery( "#addtag .gd-term-form-field .wp-color-result" ).css( 'background-color', '' );
            return;
        }
    });
});
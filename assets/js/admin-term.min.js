jQuery(function($) {
    jQuery('#addtag #submit').click(function() {
        try {
            var mceField = typeof tinymce != 'undefined' && typeof tinymce.editors != 'undefined' && typeof tinymce.editors['ct_cat_top_desc'] == 'object' ? tinymce.editors['ct_cat_top_desc'] : null;
            if (mceField) {
                mceField.editorManager.triggerSave();
            }
        } catch (e) {
            console.log(e);
        }
    });
    jQuery('.gd-upload-img').each(function() {
        var $wrap = jQuery(this);
        var field = $wrap.data('field');
        if (!jQuery('[name="' + field + '[id]"]').val()) {
            jQuery('.gd_remove_image_button', $wrap).hide();
        }
    });

    var media_frame = [];
    jQuery(document).on('click', '.gd_upload_image_button', function(e) {
        e.preventDefault();
        
        var $this = jQuery(this);
        var $wrap = $this.closest('.gd-upload-img');
        var field = $wrap.data('field');
        
        if ( !field ) {
            return
        }
        
        if (media_frame && media_frame[field]) {
            media_frame[field].open();
            return;
        }
        
        media_frame[field] = wp.media.frames.downloadable_file = wp.media({
            title: geodir_ajax.txt_choose_image,
            button: {
                text: geodir_ajax.txt_use_image
            },
            multiple: false
        });
        
        // When an image is selected, run a callback.
        media_frame[field].on('select', function() {
            var attachment = media_frame[field].state().get('selection').first().toJSON();
            
            var thumbnail = attachment.sizes.medium || attachment.sizes.full;
            if (field) {
                jQuery('[name="' + field + '[id]"]').val(attachment.id);
                jQuery('[name="' + field + '[src]"]').val(attachment.url);
            }
            $wrap.closest('.form-field.form-invalid').removeClass('form-invalid');
            jQuery('.gd-upload-display', $wrap).find('img').attr('src', thumbnail.url);
            jQuery('.gd_remove_image_button').show();
        });
        // Finally, open the modal.
        media_frame[field].open();
    });

    jQuery(document).on('click', '.gd_remove_image_button', function() {
        var $this = jQuery(this);
        var $wrap = $this.closest('.gd-upload-img');
        var field = $wrap.data('field');
        jQuery('.gd-upload-display', $wrap).find('img').attr('src', geodir_ajax.img_spacer);
        if (field) {
            jQuery('[name="' + field + '[id]"]').val('');
            jQuery('[name="' + field + '[src]"]').val('');
        }
        $this.hide();
        return false;
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
                jQuery('.gd-upload-display', $this).find('img').attr('src', geodir_ajax.img_spacer);
                jQuery('.gd_remove_image_button', $this).hide();
            });
            jQuery('#addtag .gd-term-form-field checkbox').prop('checked', false);
            jQuery('#addtag .gd-term-form-field select option').removeAttr('selected');
            jQuery("#addtag .gd-term-form-field iframe").contents().find("body").html('');
            return;
        }
    });
});
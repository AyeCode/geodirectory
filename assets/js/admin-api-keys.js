var GeoDir_Admin_API_Keys = {
    initialize: function() {
        var $self = this;
        this.el = jQuery('#key-fields');
        this.form = jQuery('form#mainform');
        if (jQuery('#key_description').length) {
            this.form.attr('action', 'javascript:void(0);');
        }
        jQuery("#update_api_key", this.el).on("click", function(e) {
            $self.saveKey(e);
        });
    },
    block: function() {
        jQuery('#update_api_key', this.el).prop('disabled', true);
        jQuery(this.el).css({
            opacity: 0.6
        });
    },
    unblock: function() {
        jQuery('#update_api_key', this.el).prop('disabled', false);
        jQuery(this.el).css({
            opacity: 1
        });
    },
    initTipTip: function(css_class) {
        jQuery(document.body).on('click', css_class, function(evt) {
            evt.preventDefault();
            if (!document.queryCommandSupported('copy')) {
                jQuery(css_class).closest('.input-group').find('input').focus().select();
                jQuery('#copy-error').text(geodir_admin_api_keys_params.clipboard_failed);
            } else {
                jQuery('#copy-error').text('');
                gdClearClipboard();
                gdSetClipboard(jQuery.trim(jQuery(css_class).closest('.input-group').find('input').val()), jQuery(css_class));
            }
        }).on('aftercopy', css_class, function() {
            jQuery('#copy-error').text('');
            jQuery('#copy-error').text(geodir_admin_api_keys_params.clipboard_copied);
        }).on('aftercopyerror', css_class, function() {
            jQuery(css_class).closest('.input-group').find('input').focus().select();
            jQuery('#copy-error').text(geodir_admin_api_keys_params.clipboard_failed);
        });
    },
    createQRCode: function(consumer_key, consumer_secret) {
        jQuery('#keys-qrcode').qrcode({
            text: consumer_key + '|' + consumer_secret,
            width: 120,
            height: 120
        });
    },
    saveKey: function(e) {
        e.preventDefault();
        var $self = this;
        $self.block();
        var data = {
            action: 'geodir_save_api_key',
            security: geodir_admin_api_keys_params.save_api_nonce,
            key_id: jQuery('#key_id', $self.el).val(),
            description: jQuery('#key_description', $self.el).val(),
            user: jQuery('#key_user', $self.el).val(),
            permissions: jQuery('#key_permissions', $self.el).val()
        };
        jQuery.ajax({
            url: geodir_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function() {},
            success: function(res, textStatus, xhr) {
                jQuery('.gd-api-message', $self.el).remove();
                if (res.success) {
                    var data = res.data;
                    if (0 < data.consumer_key.length && 0 < data.consumer_secret.length) {
                        jQuery('#gd_ie_imreviews', $self.el).html('<div class="gd-api-message updated message alert alert-success mb-4 mt-0">' + data.message + '</div>');
                        var template = wp.template('api-keys-template');
                        jQuery('#gd_ie_imreviews', $self.el).append(template({
                            consumer_key: data.consumer_key,
                            consumer_secret: data.consumer_secret
                        }));
                        jQuery('#gd_ie_imreviews', $self.el).append(data.revoke_url);
                        $self.createQRCode(data.consumer_key, data.consumer_secret);
                        $self.initTipTip('.copy-key');
                        $self.initTipTip('.copy-secret');
                    } else {
                        jQuery('#gd_ie_imreviews', $self.el).prepend('<div class="gd-api-message updated message alert alert-success mb-4 mt-0">' + data.message + '</div>');
                        jQuery('#key_description', $self.el).val(data.description);
                        jQuery('#key_user', $self.el).val(data.user_id);
                        jQuery('#key_permissions', $self.el).val(data.permissions);
                    }
                } else {
                    jQuery('#gd_ie_imreviews', $self.el).prepend('<div class="gd-api-message error message alert alert-warning mb-4 mt-0">' + res.data.message + '</div>');
                }
                $self.unblock();
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                $self.unblock();
            }
        });
    }
}
jQuery(function() {
    GeoDir_Admin_API_Keys.initialize();
});
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
            console.log('this');
            evt.preventDefault();
            if (!document.queryCommandSupported('copy')) {
                jQuery(css_class).parent().find('input').focus().select();
                jQuery('#copy-error').text(geodir_admin_api_keys_params.clipboard_failed);
            } else {
                jQuery('#copy-error').text('');
                gdClearClipboard();
                gdSetClipboard(jQuery.trim(jQuery(this).prev('input').val()), jQuery(css_class));
            }
        }).on('aftercopy', css_class, function() {
            jQuery('#copy-error').text('');
            jQuery('#copy-error').text(geodir_admin_api_keys_params.clipboard_copied);
        }).on('aftercopyerror', css_class, function() {
            jQuery(css_class).parent().find('input').focus().select();
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
        console.log(data);
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
                    jQuery('#api-keys-options', $self.el).before('<div class="gd-api-message updated"><p>' + data.message + '</p></div>');
                    if (0 < data.consumer_key.length && 0 < data.consumer_secret.length) {
                        jQuery('#api-keys-options', $self.el).remove();
                        jQuery('p.submit', $self.el).empty().append(data.revoke_url);
                        var template = wp.template('api-keys-template');
                        jQuery('p.submit', $self.el).before(template({
                            consumer_key: data.consumer_key,
                            consumer_secret: data.consumer_secret
                        }));
                        $self.createQRCode(data.consumer_key, data.consumer_secret);
                        $self.initTipTip('.copy-key');
                        $self.initTipTip('.copy-secret');
                    } else {
                        jQuery('#key_description', $self.el).val(data.description);
                        jQuery('#key_user', $self.el).val(data.user_id);
                        jQuery('#key_permissions', $self.el).val(data.permissions);
                    }
                } else {
                    jQuery('#api-keys-options', $self.el).before('<div class="gd-api-message error"><p>' + res.data.message + '</p></div>');
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
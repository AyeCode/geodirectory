/**
 * Functions for saving and updating listings.
 */
/**
 * Document load functions
 */
jQuery(function($) {
    // Start polling the form for auto saves
    setTimeout(function() {
        if (typeof geodir_is_localstorage === 'function' && jQuery("#geodirectory-add-post input#user_login").length && jQuery("#geodirectory-add-post input#user_email").length && geodir_is_localstorage()) {
            if (localStorage.getItem('geodirUserLogin')) {
                jQuery("#geodirectory-add-post input#user_login").val(localStorage.getItem('geodirUserLogin'));
            }
            if (localStorage.getItem('geodirUserEmail')) {
                jQuery("#geodirectory-add-post input#user_email").val(localStorage.getItem('geodirUserEmail'));
            }
        }
        geodir_auto_save_poll(geodir_get_form_data());
    }, 1);
    /// check validation on blur
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("[field_type]:visible, .editor textarea").delay(2000).on("blur",function() {
        // give some time inc ase another script is filling data
        $this = this;
        setTimeout(function() {
            geodir_validate_field($this);
        }, 100)
    });
    // Check for validation on click for checkbox, radio
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("input[type='checkbox'],input[type='radio']").on("click", function() {
        geodir_validate_field(this);
    });
    // Check for validation on click for select2
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("select.geodir-select").on('change', function() {
        geodir_validate_field(this);
    });
    // backend validation
    if ($('form#post .postbox#geodir_post_info').length) {
        var $form = $('.postbox#geodir_post_info').closest('form#post');
        // check validation on blur
        $('.required_field:visible', $form).find("[field_type]:visible, .editor textarea").on("blur",function() {
            geodir_validate_field(this);
        });
        // Check for validation on click for checkbox, radio
        $('.required_field:visible', $form).find("input[type='checkbox'],input[type='radio']").on("click", function() {
            geodir_validate_field(this);
        });
        // Check for validation on click for select2
        $('.required_field:visible', $form).find("select.geodir-select").on('change', function() {
            geodir_validate_field(this);
        });
        $(document).delegate("form#post", "submit", function(ele) {
            return geodir_validate_admin_submit(this);
        });
    }
    if ($('.geodir_form_row input[data-ccheckbox]').length) {
        $('.geodir_form_row input[data-ccheckbox]').on('change', function(e) {
            var $this, $parent, name, $field, $input, value, c = 0;
            $this = $(this);
            $parent = $this.closest('.geodir_form_row');
            $parent.removeClass('gd-term-handle');
            $('.gd-term-checked', $parent).removeClass('gd-term-checked');
            $('.gd-default-term', $parent).removeClass('gd-default-term');
            $field = $this.closest('form').find('input[name=' + $this.data('ccheckbox') + ']');
            value = $field.val() != 'undefined' ? $field.val() : '';
            name = $this.attr('name');
            field = $this.data('ccheckbox');
            $('[name="' + name + '"]', $parent).each(function() {
                if ($(this).prop("checked") == true) {
                    c++;
                    $(this).parent().addClass('gd-term-checked');
                    if (c == 1) {
                        $input = $(this);
                    }
                } else {}
            });
            if (c > 1) {
                $parent.addClass('gd-term-handle');
            }
            if ($('#gd-cat-' + value, $parent).prop("checked") == true) {
                $input = $('#gd-cat-' + value, $parent);
            }
            if ($input) {
                $input.parent().find('.gd-make-default-term').trigger('click');
            } else {
                $field.val('');
                $field.trigger('change');
            }
        });
        $('.gd-make-default-term').on('click', function() {
            var $parent, $row, $field, $chkbox, value;
            $row = $(this).closest('.geodir_form_row');
            $parent = $(this).parent();
            $chkbox = $('[type="checkbox"]', $parent);
            $field = $(this).closest('form').find('input[name=' + $chkbox.data('ccheckbox') + ']');
            $('.gd-default-term', $row).removeClass('gd-default-term');
            $parent.addClass('gd-default-term');
            value = $chkbox.val();
            $field.val(value);
            $field.trigger('change');
        });
        $('.geodir_form_row input[data-ccheckbox]:first').trigger('change');
    }
    if ($('.geodir_form_row input[data-cradio]').length) {
        $('.geodir_form_row input[data-cradio]').on('change', function(e) {
            var value = '';
            if ($('[name="' + $(this).attr('name') + '"]:checked').length > 0) {
                value = $('[name="' + $(this).attr('name') + '"]:checked').val();
            }
            $(this).closest('form').find('input[name=' + $(this).data('cradio') + ']').val(value);
        });
        $('.geodir_form_row input[data-cradio]:first').trigger('change');
    }
    if ($('.gd-locate-me-btn').length) {
        $('.gd-locate-me-btn').on('click', function(e) {
            gdGeoLocateMe(this, 'add-listing');
        });
    }
    /**
     * Save the post on preview link click.
     */
    jQuery(".geodir_preview_button").on("click", function() {
        geodir_auto_save_post();
        $form = jQuery("#geodirectory-add-post");
        return geodir_validate_submit($form);
    });
    /**
     * Save the post via ajax.
     */
    jQuery("#geodirectory-add-post").on("submit", function(e) {
        $valid = geodir_validate_submit(this);
        if ($valid) {
            $result = geodir_save_post();
        }
        e.preventDefault(); // avoid to execute the actual submit of the form.
    });

    // Conditional Fields on change
    jQuery("#geodirectory-add-post,#post").on("change", function() {
        try {
            aui_conditional_fields("#geodirectory-add-post,#post");
        } catch(err) {
            console.log(err.message);
        }
    });

    // Handle hidden latitude/longitude required fields.
    if (jQuery('.gd-hidden-latlng').length) {
        var $_form = jQuery("#geodirectory-add-post");
        jQuery("[type='submit']", $_form).on('click', function(e) {
            if (!(jQuery('[name="latitude"]', $_form).val().trim() && jQuery('[name="longitude"]', $_form).val().trim())) {
                jQuery('.gd-hidden-latlng').removeClass('d-none');
            }
        });
    }

    // Conditional Fields on load
    try {
        aui_conditional_fields("#geodirectory-add-post,#post");
    } catch(err) {
        console.log(err.message);
    }

    // Default cat set
    jQuery(".geodir_taxonomy_field .geodir-category-select, .geodir_taxonomy_field [data-ccheckbox='default_category'], .geodir_taxonomy_field input[data-cradio]").on("change", function() {
        geodir_populate_default_category_input();jQuery('[name="default_category"]').trigger('change');
    });
    geodir_populate_default_category_input();

    /* post_tags spellcheck */
    if (jQuery('select#post_tags').prop('spellcheck')) {
        setTimeout(function(){
            jQuery('[data-argument="post_tags"] input.select2-search__field').prop('spellcheck', 'true');
        }, 5000);
    }
});
/**
 * Prevent navigation away if there are unsaved changes.
 */
var geodir_changes_made = false;
window.geodirUploading = false; // Don't run auto save when image upload is in progress.
window.onbeforeunload = function() {
    return geodir_changes_made ? geodir_params.txt_lose_changes : null;
};
/**
 * Poll the form looking for changes every 10 seconds, if we detect a change then auto save
 *
 * @param old_form_data
 */
function geodir_auto_save_poll(old_form_data) {
    if (jQuery("#geodirectory-add-post").length && geodir_params.autosave > 0) {
        setTimeout(function() {
            // only save if the forum data has changed
            if (jQuery("#geodirectory-add-post").length && old_form_data != geodir_get_form_data()) {
                console.log('form has changed');
                geodir_auto_save_post();
                geodir_changes_made = true; // flag changes have been made
            }
            geodir_auto_save_poll(geodir_get_form_data()); // run the function again.
        }, geodir_params.autosave);
    }
}
/**
 * Saves the post in the background via ajax.
 */
function geodir_auto_save_post() {
    if (window.geodirUploading) {
        return;
    }

    if (typeof geodir_is_localstorage === 'function' && jQuery("#geodirectory-add-post input#user_login").length && jQuery("#geodirectory-add-post input#user_email").length && geodir_is_localstorage()) {
        localStorage.setItem('geodirUserLogin', jQuery("#geodirectory-add-post input#user_login").val());
        localStorage.setItem('geodirUserEmail', jQuery("#geodirectory-add-post input#user_email").val());
    }

    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_auto_save_post&target=auto";
    jQuery.ajax({
        type: "POST",
        url: geodir_params.ajax_url,
        data: form_data,
        success: function(data) {
            if (data.success) {
                console.log('auto saved');
            } else {
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
function geodir_get_form_data() {
    geodir_save_all_tinymce_editors();
    return jQuery("#geodirectory-add-post").serialize();
}
/**
 * Save the post and redirect to where needed.
 */
function geodir_save_post() {
    var form_data = geodir_get_form_data() + '&target=submit';
    console.log(form_data);
    $button_text = jQuery('#geodir-add-listing-submit button').html();
    jQuery.ajax({
        type: "POST",
        url: geodir_params.ajax_url,
        data: form_data, // serializes the form's elements.
        beforeSend: function() {
            if (typeof geodir_is_localstorage === 'function' && jQuery("#geodirectory-add-post input#user_login").length && jQuery("#geodirectory-add-post input#user_email").length && geodir_is_localstorage()) {
                localStorage.removeItem('geodirUserLogin');
                localStorage.removeItem('geodirUserEmail');
            }
            jQuery('#geodir-add-listing-submit button').html('<i class="fas fa-circle-notch fa-spin"></i> ' + $button_text).addClass('gd-disabled').prop('disabled', true);
        },
        success: function(data) {
            if (data.success) {
                console.log('saved');
                console.log(data.data);
                geodir_changes_made = false; // set the changes flag to false.
                jQuery('.gd-notification').remove(); // remove current notes
                $container = jQuery('#gd-add-listing-replace-container').length ? jQuery('#gd-add-listing-replace-container').val() : '#geodirectory-add-post'
                jQuery($container).replaceWith(data.data); // remove the form and replace with the notification
                jQuery(window).scrollTop(jQuery('.gd-notification').offset().top - 100); // scroll to new notification
                return true;
            } else {
                jQuery('#geodir-add-listing-submit button').html($button_text).removeClass('gd-disabled').prop('disabled', false);
                console.log('save failed');
                if (typeof data == 'object' && data.success === false && data.data) {
                    alert(data.data);
                }
				document.dispatchEvent(new Event('ayecode_reset_captcha'));
                return false;
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            jQuery('#geodir-add-listing-submit button').html($button_text).removeClass('gd-disabled').prop('disabled', false);
            alert(geodir_params.rating_error_msg);
            console.log(textStatus);
			document.dispatchEvent(new Event('ayecode_reset_captcha'));
        }
    });
}
/**
 * Delete a post revision.
 */
function geodir_delete_revision() {
    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_delete_revision&target=revision";
    jQuery.ajax({
        type: "POST",
        url: geodir_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data) {
            if (data.success) {
                console.log('deleted');
                location.reload();
                return true;
            } else {
                console.log('delete failed');
                alert(data.data);
                return false;
            }
        }
    });
}
/**
 * Validate all required fields before submit.
 *
 * @returns {boolean}
 */
function geodir_validate_admin_submit(form) {
    var is_validate = true;
    jQuery(form).find(".required_field:visible").each(function() {
        jQuery(this).find("[field_type]:visible, .geodir_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload, .gd_image_required_field").each(function() {
            if (!geodir_validate_field(this)) {
                is_validate = false;
            }
        });
    });
    if (is_validate) {
        return true;
    } else {
        jQuery(window).scrollTop(jQuery(".geodir_message_error:visible:first").closest('.required_field').offset().top);
        jQuery('#save-action .spinner').removeClass('is-active');
        jQuery('#save-action #save-post').removeClass('disabled');
        jQuery('#publishing-action .spinner').removeClass('is-active');
        jQuery('#publishing-action #publish').removeClass('disabled');
        return false;
    }
}
/**
 * Validate all required fields before submit.
 *
 * @returns {boolean}
 */
function geodir_validate_submit(form) {
    var is_validate = true;
    var $field = false;
    jQuery(form).find(".required_field:visible").each(function() {
        jQuery(this).find("[field_type]:visible, .geodir_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload, .gd_image_required_field, .g-recaptcha-response, [name='cf-turnstile-response']").each(function() {
            if (!geodir_validate_field(this)) {
                is_validate = false;
                if (!$field) {
                    $field = jQuery(this);
                }
            } else {
                //console.log(true);
            }
        });
    });
    if (is_validate) {
        return true;
    } else {
        var $el = jQuery(".geodir_message_error:visible:first").closest('.required_field');
        var $offset = false;
        if ($el && $el.length) {
            $offset = $el.offset();
        } else if ($field) {
            $offset = $field.offset();
        }
        if ($offset && typeof $offset != 'undefined') {
            var $top = $offset.top;
            if ($top != 'undefined') {
                jQuery(window).scrollTop($top);
            }
        }
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
        case 'hidden': // images
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
var GeoDir_Business_Hours = {
    init: function(params) {
        var $this = this;
        this.params = params;
        this.field = this.params.field;
        this.$field = jQuery('[name="' + this.field + '"]');
        this.$wrap = this.$field.closest('.gd-bh-row');
        this.sample = jQuery('.gd-bh-items .gd-bh-blank').html();
        this.default_timezone_string = geodir_params.timezone_string;
        this.default_offset = geodir_params.gmt_offset;
        this.gmt_offset = (params.offset ? params.offset : geodir_params.gmt_offset);
        jQuery('[data-field="active"]', this.$wrap).on("change", function(e) {
            $wrap = this.$wrap;
            if (jQuery(this).val() == '1') {
                jQuery('.gd-bh-items', $wrap).slideDown(200);
                jQuery('[data-field="timezone_string"]', $wrap).each(function(){
                    if (jQuery(this).hasClass('select2-hidden-accessible')) {
                        jQuery(this).select2('destroy');
                        jQuery(this).removeClass('select2-hidden-accessible');
                        aui_init_select2();
                    }
                });
            } else {
                jQuery('.gd-bh-items', $wrap).slideUp(200);
            }
            $this.setValue();
            e.preventDefault();
        });
        jQuery('[data-field="timezone_string"]', this.$wrap).on("change", function(e) {
            $this.setValue();
            e.preventDefault();
        });
        jQuery('[name="latitude"], [name="longitude"]', this.$wrap.closest('form')).on("change", function(e) {
            if (!window.gdTzApi) {
                window.gdTzApi = true;
                setTimeout(function() {
                    $this.getTimezone('[data-field="timezone_string"]');
                }, 1000);
            }
            e.preventDefault();
        });
        // add slot
        jQuery(".gd-bh-add", this.$wrap).on("click", function(e) {
            $this.addSlot(jQuery(this));
            $this.onAddSlot();
            e.preventDefault();
        });
        setTimeout(function() {
            if (jQuery('.gd-bh-has24').length) {
                jQuery('.gd-bh-has24').each(function(e) {
                    $this.handle24Hours(jQuery(this).closest('.gd-bh-item'));
                });
            }
            if (jQuery('.gd-bh-hours').length) {
                $this.onAddSlot();
            }
            $this.onChangeValue();
        }, 100);
        jQuery('.gd-bh-items .gd-bh-24hours [type="checkbox"]').on('click', function(e) {
            $this.onChange24Hours(jQuery(this));
        });
    },
    onChangeValue: function() {
        var $this;
        $this = this;
        jQuery('[name^="' + this.field + '_f[hours]"]', this.$wrap).on("change", function(e) {
            $this.handle24Hours(jQuery(this).closest('.gd-bh-item'));
            $this.setValue();
            e.preventDefault();
        });
    },
    addSlot: function($el) {
        var sample = this.sample;
        var $item = $el.closest('.gd-bh-item');
        var uniqueid = Math.floor(Math.random() * 100000000000).toString();

        jQuery('.gd-bh-closed', $item).remove();
        $item.removeClass('gd-bh-item-closed');
        jQuery('.gd-bh-24hours [type="checkbox"]', $item).show();
        sample = sample.replace(/GD_UNIQUE_ID/g, uniqueid);
        sample = sample.replace('data-field-alt="open"', 'data-field-alt="open" name="' + jQuery('.gd-bh-time', $item).data('field') + '[open][]" data-aui-init="flatpickr"');
        sample = sample.replace('data-field-alt="close"', 'data-field-alt="close" name="' + jQuery('.gd-bh-time', $item).data('field') + '[close][]" data-aui-init="flatpickr"');

        jQuery('.gd-bh-time', $item).append(sample);
    },
    cancelSlot: function($el) {
        var $item = $el.closest('.gd-bh-time');
        jQuery('i', $el).tooltip('dispose');
        $el.closest('.gd-bh-hours').remove();
        if (jQuery('.gd-bh-hours', $item).length < 1) {
            $item.closest('.gd-bh-item').addClass('gd-bh-item-closed');
            jQuery('.gd-bh-24hours [type="checkbox"]', $item.closest('.gd-bh-item')).hide();
            $item.html('<div class="gd-bh-closed text-center">' + geodir_params.txt_closed + '</div>');
        }
    },
    onAddSlot: function() {
        this.attachEvents();
        this.timepickers();
    },
    onCancelSlot: function() {
        this.setValue();
    },
    attachEvents: function() {
        var $this = this;
        jQuery(".gd-bh-remove").on("click", function(e) {
            $this.cancelSlot(jQuery(this));
            $this.onCancelSlot();
            e.preventDefault();
        });
        $this.onChangeValue();
    },
    setValue: function() {
        var v;
        if (jQuery('[name="' + this.field + '_f_active"]:checked', this.$wrap).val() == '1') {
            v = this.toSchema();
        } else {
            v = '';
        }
        this.$field.val(v);
        this.$field.trigger('change');
    },
    toSchema: function() {
        var $this, $item, $slot, d, o, c, ha, h, pa, v, tz;
        $this = this;
        pa = [];
        jQuery('.gd-bh-item', $this.$wrap).each(function() {
            $item = jQuery(this);
            d = jQuery('.gd-bh-time', $item).data('day');
            if (d) {
                ha = [];
                jQuery('.gd-bh-hours', $item).each(function() {
                    $slot = jQuery(this);
                    o = jQuery('[data-field-alt="open"]', $slot).val().trim();
                    c = jQuery('[data-field-alt="close"]', $slot).val().trim();
                    if (o) {
                        h = o;
                        h += '-';
                        if (!c) {
                            c = '00:00';
                        }
                        h += c;
                        ha.push(h);
                    }
                });
                if (ha.length) {
                    pa.push(d + ' ' + ha.join(","));
                }
            }
        });
        v = '';
        if (pa.length) {
            v += JSON.stringify(pa);
            v += ',';
        }
        tzstring = tz = '';
        if (jQuery('[data-field="timezone_string"]', $this.$wrap).length) {
            $tzstring = jQuery('[data-field="timezone_string"]', $this.$wrap);
            tzstring = $tzstring.val();

            if ($tzstring.find(':selected').length) {
                tz = $tzstring.find(':selected').data('offset');
            }
        }
        if (tzstring === '' || tzstring === null || tzstring == 'undefined') {
            tzstring = this.default_timezone_string;
            tz = this.default_offset;
        }
        v += '["UTC":"' + tz + '","Timezone":"' + tzstring + '"]';
        return v;
    },
    timepickers: function() {
        aui_init();
    },
    getTimezone: function(el, prefix) {
        var $this = this,
            $form, lat, lng, url;
        if (!prefix) {
            prefix = '';
        }

        $form = jQuery(el).closest('form');
        lat = jQuery('[name="' + prefix + 'latitude"]', $form).val();
        lng = jQuery('[name="' + prefix + 'longitude"]', $form).val();
        lat = lat ? lat.trim() : '';
        lng = lng ? lng.trim() : '';
        if (lat && lng) {
            jQuery.ajax({
                url: geodir_params.gd_ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'geodir_timezone_data',
                    security: geodir_params.basic_nonce,
                    lat: lat,
                    lon: lng,
                    ts: (Math.round((new Date().getTime()) / 1000)).toString()
                }
            }).done(function(res) {
                if (res && typeof res == 'object') {
                    if (res.success) {
                        data = res.data;
                        if (typeof data.timeZoneId != 'undefined') {

                            jQuery(el).val(data.timeZoneId).trigger("change");
                        }
                    } else if (res.data) {
                        data = res.data;
                        if (data.error) {
                            console.log(data.error);
                        }
                    }
                }
                window.gdTzApi = false;
            });
        }
    },
    onChange24Hours: function($el) {
        $item = $el.closest('.gd-bh-item');
        $hours = jQuery('.gd-bh-hours:first', $item);
        if ($el.is(':checked')) {
            $12am = $el.closest('.gd-bh-items').data('12am').trim();
            $item.addClass('gd-bh-item-24hours');
            jQuery('.input-group', $item).css({
                "opacity": 0.67
            });
            jQuery('.gd-alt-open', $hours).val($12am);
            jQuery('.gd-alt-close', $hours).val($12am);
            jQuery('[data-field-alt="open"]', $hours).val('00:00');
            jQuery('[data-field-alt="close"]', $hours).val('00:00');
            jQuery('[data-field-alt="open"]', $hours).trigger('change');
        } else {
            jQuery('.input-group', $item).css({
                "opacity": 1
            });
        }
    },
    handle24Hours: function($item) {
        var o, c, has24 = false;
        jQuery('.gd-bh-hours', $item).each(function() {
            o = jQuery('[data-field-alt="open"]', jQuery(this)).val().trim();
            c = jQuery('[data-field-alt="close"]', jQuery(this)).val().trim();
            if (o == '00:00' && o == c) {
                has24 = true;
            }
        });
        if (has24) {
            jQuery('.gd-bh-24hours input[type="checkbox"]', $item).prop('checked', 'checked');
            $item.addClass('gd-bh-item-24hours');
            jQuery('.input-group', $item).css({
                "opacity": 0.67
            });
        } else {
            jQuery('.gd-bh-24hours input[type="checkbox"]', $item).prop('checked', false);
            $item.removeClass('gd-bh-item-24hours');
            jQuery('.input-group', $item).css({
                "opacity": 1
            });
        }
    },
    secondsToHM: function(value) {
        var $this = this,
            prefix, hours, minutes, result;
        prefix = value < 0 ? '-' : '+';
        value = Math.abs(value);
        hours = Math.floor(value / 3600);
        minutes = Math.floor((value - (hours * 3600)) / 60);
        result = hours;
        result += ":" + (minutes < 10 ? "0" + minutes : minutes);
        result = prefix + '' + result;
        return result;
    }
};

/**
 * Save all the tinymce editors or if they are in HTML mode it will not save the last changes.
 */
function geodir_save_all_tinymce_editors() {
    if (typeof tinymce !== 'undefined' && tinymce.editors && tinymce.editors.length > 0) {
        for (var i = 0; i < tinymce.editors.length; i++) {
            // you need to do what is needed here
            // example: write the content back to the form foreach editor instance
            tinymce.editors[i].save();
        }
    }
}

function geodirIsTouchDevice() {
    return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
}

function geodir_populate_default_category_input() {
    var default_cat = jQuery('#default_category').val();

    if (jQuery(".geodir_taxonomy_field .geodir-category-select").length) {
        jQuery('#default_category').html('');
        var selected_cats = jQuery('.geodir-category-select').val();
        if (selected_cats && selected_cats.length) {
            if (typeof selected_cats == 'object' || typeof selected_cats == 'array') {
                jQuery(".geodir_taxonomy_field .geodir-category-select option").each(function(index) {
                    if (jQuery.inArray(jQuery(this).val(), selected_cats) !== -1) {
                        jQuery('#default_category').append(jQuery('<option>', {
                            value: jQuery(this).val(),
                            text: jQuery(this).text(),
                            selected: default_cat == jQuery(this).val() || (!default_cat && selected_cats[0] == jQuery(this).val())
                        }));
                    }
                });
            } else {
                jQuery('#default_category').val(selected_cats);
            }
        } else {
            jQuery('#default_category').val('');
        }
    } else if (jQuery(".geodir_taxonomy_field [data-ccheckbox='default_category']").length) {
        jQuery('#default_category').html('');
        var selected_cats = [];
        jQuery("[data-ccheckbox='default_category']:checked").each(function(i){
            selected_cats[i] = jQuery(this).val();
        });
        if (selected_cats && selected_cats.length) {
            jQuery("[data-ccheckbox='default_category']:checked").each(function(index) {
                if (jQuery.inArray(jQuery(this).val(), selected_cats) !== -1) {
                    jQuery('#default_category').append(jQuery('<option>', {
                        value: jQuery(this).val(),
                        text: jQuery(this).prop('title'),
                        selected: default_cat == jQuery(this).val() || (!default_cat && selected_cats[0] == jQuery(this).val())
                    }));
                }
            });
        } else {
            jQuery('#default_category').val('');
        }
    } else if (jQuery(".geodir_taxonomy_field [data-cradio='default_category']").length) {
        var selected_cats = jQuery("[data-cradio='default_category']:checked").val();
        if (selected_cats) {
            jQuery('#default_category').val(selected_cats);
        } else {
            jQuery('#default_category').val('');
        }
    }
}

/**
 * Functions for saving and updating listings.
 */
/**
 * Document load functions
 */
jQuery(function($) {
    // Start polling the form for auto saves
    geodir_auto_save_poll(geodir_get_form_data());
    /// check validation on blur
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("[field_type]:visible, .editor textarea").delay( 2000 ).blur(function() {
        // give some time inc ase another script is filling data
        $this = this;
        setTimeout(function() {
            geodir_validate_field($this);
        }, 100)
    });
    // Check for validation on click for checkbox, radio
    jQuery('#geodirectory-add-post').find(".required_field:visible").find("input[type='checkbox'],input[type='radio']").click(function() {
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
        $('.required_field:visible', $form).find("[field_type]:visible, .editor textarea").blur(function() {
            geodir_validate_field(this);
        });
        // Check for validation on click for checkbox, radio
        $('.required_field:visible', $form).find("input[type='checkbox'],input[type='radio']").click(function() {
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
	if ($('.gd-locate-me .gd-locate-me-btn').length) {
        $('.gd-locate-me .gd-locate-me-btn').on('click', function(e) {
            gdGeoLocateMe(this, 'add-listing');
        });
    }
});
/**
 * Prevent navigation away if there are unsaved changes.
 */
var geodir_changes_made = false;
window.onbeforeunload = function() {
    return geodir_changes_made ? geodir_params.txt_lose_changes : null; // @todo make translatable
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
    var form_data = geodir_get_form_data();
    form_data += "&action=geodir_auto_save_post&target=auto";
    jQuery.ajax({
        type: "POST",
        url: geodir_params.ajax_url,
        data: form_data, // serializes the form's elements.
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
    jQuery.ajax({
        type: "POST",
        url: geodir_params.ajax_url,
        data: form_data, // serializes the form's elements.
        success: function(data) {
            if (data.success) {
                console.log('saved');
                console.log(data.data);
                geodir_changes_made = false; // set the changes flag to false.
                jQuery('.gd-notification').remove(); // remove current notes
                jQuery('#geodirectory-add-post').replaceWith(data.data); // remove the form and replae with the notification
                jQuery(window).scrollTop(jQuery('.gd-notification').offset().top - 100); // scroll to new notification
                return true;
            } else {
                console.log('save failed');
                return false;
            }
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
 * Save the post on preview link click.
 */
jQuery(".geodir_preview_button").click(function() {
    geodir_auto_save_post();
    $form = jQuery("#geodirectory-add-post");
    return geodir_validate_submit($form);
});
/**
 * Save the post via ajax.
 */
jQuery("#geodirectory-add-post").submit(function(e) {
    $valid = geodir_validate_submit(this);
    if ($valid) {
        $result = geodir_save_post();
    }
    e.preventDefault(); // avoid to execute the actual submit of the form.
});
/**
 * Validate all required fields before submit.
 *
 * @returns {boolean}
 */
function geodir_validate_admin_submit(form) {
    var is_validate = true;
    jQuery(form).find(".required_field:visible").each(function() {
        jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload, .gd_image_required_field").each(function() {
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
    jQuery(form).find(".required_field:visible").each(function() {
        jQuery(this).find("[field_type]:visible, .chosen_select, .geodir_location_add_listing_chosen, .editor, .event_recurring_dates, .geodir-custom-file-upload, .gd_image_required_field").each(function() {
            // if (jQuery(this).is('.chosen_select, .geodir_location_add_listing_chosen')) {
            //     var chosen_ele = jQuery(this);
            //     jQuery('#' + jQuery(this).attr('id') + '_chzn').mouseleave(function () {
            //         geodir_validate_field(chosen_ele);
            //     });
            // }
            if (!geodir_validate_field(this)) {
                is_validate = false;
                //console.log(false);
            } else {
                //console.log(true);
            }
            // console.log(this);
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
    //console.log(field);
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
		this.default_offset = geodir_params.gmt_offset;
        this.gmt_offset = (params.offset ? params.offset : geodir_params.gmt_offset);
        jQuery('[data-field="active"]', this.$wrap).on("change", function(e) {
            $wrap = this.$wrap;
            if (jQuery(this).val() == '1') {
                jQuery('.gd-bh-items', $wrap).slideDown(200);
            } else {
                jQuery('.gd-bh-items', $wrap).slideUp(200);
            }
            $this.setValue();
            e.preventDefault();
        });
		jQuery('[data-field="timezone"]', this.$wrap).on("change", function(e) {
			$this.setValue();
            e.preventDefault();
		});
		jQuery('[name="latitude"], [name="longitude"]', this.$wrap.closest('form')).on("change", function(e) {
			if (!window.gdTzApi) {
				window.gdTzApi = true;
				setTimeout(function() {
					$this.getTimezone('[data-field="timezone"]');
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
        if (jQuery('.gd-bh-hours').length) {
            $this.onAddSlot();
        }
        $this.onChangeValue();
    },
    onChangeValue: function() {
        var $this;
        $this = this;
        jQuery('[name^="' + this.field + '_f[hours]"]', this.$wrap).on("change", function(e) {
            $this.setValue();
            e.preventDefault();
        });
    },
    addSlot: function($el) {
        var sample = this.sample;
        var $item = $el.closest('.gd-bh-item');
        jQuery('.gd-bh-closed', $item).remove();
        sample = sample.replace('data-field="open"', 'data-field="open" name="' + jQuery('.gd-bh-time', $item).data('field') + '[open][]"');
        sample = sample.replace('data-field="close"', 'data-field="close" name="' + jQuery('.gd-bh-time', $item).data('field') + '[close][]"');
        jQuery('.gd-bh-time', $item).append(sample);
    },
    cancelSlot: function($el) {
        var $item = $el.closest('.gd-bh-time');
        $el.closest('.gd-bh-hours').remove();
        if (jQuery('.gd-bh-hours', $item).length < 1) {
            $item.html('<div class="gd-bh-closed">' + geodir_params.txt_closed + '</div>');
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
        if (jQuery('[name="' + this.field + '_f[active]"]:checked').val() == '1') {
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
                    o = jQuery('[data-field="open"]', $slot).val().trim();
                    c = jQuery('[data-field="close"]', $slot).val().trim();
                    if (o) {
                        h = o;
                        h += '-';
                        if (!c) {
                            c = '23:59';
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
		tz = jQuery('[data-field="timezone"]', $this.$wrap).val().trim();
		if (tz === '' || tz === null || tz == 'undefined') {
			tz = this.default_offset;
		}
        v += '["UTC":"' + tz + '"]';
        return v;
    },
    timepickers: function() {
        jQuery(this.$wrap).find('[data-bh="time"]').each(function() {
            var $el = jQuery(this);
            if (!$el.hasClass('hasDatepicker')) {
                $el.timepicker({
                    timeFormat: 'HH:mm',
                    showPeriod: true,
                    showLeadingZero: true,
                    showPeriod: true,
                });
            }
        });
    },
	getTimezone: function(el, prefix) {
		var $this = this, $form, lat, lng, url;
		if (!prefix) {
			prefix = '';
		}

		$form = jQuery(el).closest('form');
		lat = jQuery('[name="' + prefix + 'latitude"]', $form).val();
		lng = jQuery('[name="' + prefix + 'longitude"]', $form).val();
		lat = lat ? lat.trim() : '';
		lng = lng ? lng.trim() : '';
		if (lat && lng) {
			url = 'https://maps.googleapis.com/maps/api/timezone/json';
			url += '?location=' + lat + ',' + lng;
			url += '&timestamp=' + (Math.round((new Date().getTime())/1000)).toString();
			url += '&key=' + geodir_params.google_api_key;
			jQuery.ajax({
			   url:url,
			}).done(function(response){
                console.log(response);
			   if (response && typeof response == 'object') {
				   if (typeof response.rawOffset != 'undefined') {
					   offset = response.rawOffset;
					   offset = $this.secondsToHM(offset);
					   jQuery(el).val(offset).trigger('change');
				   }
				   if (response.errorMessage) {
					   console.log(response.errorMessage);
				   }
			   }
			   window.gdTzApi = false;
			});
		}
	},
	secondsToHM: function(value) {
		var $this = this, prefix, hours, minutes, result;
		prefix = value < 0 ? '-' : '+';
		value = Math.abs(value);
		hours = Math.floor(value / 3600);
		minutes = Math.floor((value - (hours * 3600)) / 60);
		result = hours;
		result += ":" + (minutes < 10 ? "0" + minutes : minutes);
		result = prefix + '' +  result;
		return result;
	}
};

/**
 * Save all the tinymce editors or if they are in HTML mode it will not save the last changes.
 */
function geodir_save_all_tinymce_editors() {
    if (typeof tinymce !== 'undefined' && tinymce.editors.length > 0) {
        for (var i = 0; i < tinymce.editors.length; i++) {
            // you need to do what is needed here
            // example: write the content back to the form foreach editor instance
            tinymce.editors[i].save();
        }
    }
}


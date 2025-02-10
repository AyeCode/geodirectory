gd_infowindow = window.gdMaps == 'google' ? new google.maps.InfoWindow() : null;

jQuery(window).on("load",function() {
    
    // tooltips
    gd_init_tooltips();

    // rating click
    jQuery( 'a.gd-rating-link' ).on("click", function() {
        jQuery.post( ajaxurl, { action: 'geodirectory_rated', '_gdnonce': jQuery(this).data('nonce') } );
        jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
    });

    // image uploads
    jQuery('.gd-upload-img').each(function() {
        var $wrap = jQuery(this);
        var field = $wrap.data('field');
        if (jQuery('[name="' + field + '[id]"]').length && !jQuery('[name="' + field + '[id]"]').val()) {
            jQuery('.gd_remove_image_button', $wrap).hide();
        }
    });

    if(jQuery('.gd-import-export .geodir-csv-tips').length && jQuery('.gd-import-export .plupload-upload-uic').length) {
        jQuery('.gd-import-export .plupload-upload-uic:first').closest('.card-body').prepend(jQuery('.gd-import-export .geodir-csv-tips').html());
    }

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
            title: geodir_params.txt_choose_image,
            button: {
                text: geodir_params.txt_use_image
            },
            multiple: false
        });

        // When an image is selected, run a callback.
        media_frame[field].on('select', function() {
            var attachment = media_frame[field].state().get('selection').first().toJSON();

            var thumbnail = attachment.sizes.medium || attachment.sizes.full;
            if (field) {
                if(jQuery('[name="' + field + '[id]"]').length){
                    jQuery('[name="' + field + '[id]"]').val(attachment.id);
                }
                if(jQuery('[name="' + field + '[src]"]').length){
                    jQuery('[name="' + field + '[src]"]').val(attachment.url);
                }
                if(jQuery('[name="' + field + '"]').length){
                    jQuery('[name="' + field + '"]').val(attachment.id);
                }


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
        jQuery('.gd-upload-display', $wrap).find('img').attr('src', geodir_params.img_spacer).removeAttr('width height sizes alt class srcset');
		if (field) {
			if (jQuery('[name="' + field + '[id]"]').length > 0) {
				jQuery('[name="' + field + '[id]"]').val('');
				jQuery('[name="' + field + '[src]"]').val('');
			}
			if (jQuery('[name="' + field + '"]').length > 0) {
				jQuery('[name="' + field + '"]').val('');
			}
		}
        $this.hide();
        return false;
    });

	// Load color picker
	var gdColorPicker = jQuery('.gd-color-picker');
	console.log('gdColorPicker');
	if (gdColorPicker.length) {
		gdColorPicker.wpColorPicker();
	}

    // Save settings validation
    gd_settings_validation();

    // init helper tags
    geodir_init_helper_tags();

    setTimeout(function(){geodir_admin_init_rating_input();}, 200);

	// Tick all addons option if core uninstall ticked
	var $coreUn = jQuery('.geodirectory_page_gd-settings input[name="admin_uninstall"]');
	if ($coreUn.length) {
		$coreUn.on("click",function() {
			geodir_handle_uninstall_option(jQuery(this));
		});
		if ($coreUn.is(':checked')) {
			geodir_handle_uninstall_option($coreUn);
		}
	}

    jQuery('.geodirectory .active-placeholder').on("focus",function() {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if( '' == current_val ){
            jQuery(this).val( placeholder );
        }
    }).on("blur",function() {
        var placeholder = jQuery(this).attr('placeholder');
        var current_val = jQuery(this).val();
        if( current_val == placeholder ){
            jQuery(this).val('');
        }
    });

    /* Regenerate Thumbnails */
    if (jQuery('#geodir_tool_generate_thumbnails').length) {
        geodir_setup_generate_thumbs();
    }

    jQuery('[data-action="geodir-regenerate-thumbnails"]').on('click', function(e) {
        geodir_post_generate_thumbs(this);
    });

    // Conditional Fields on change
    jQuery(".gd-settings-wrap").off('change').on("change", function() {
        try {
            aui_conditional_fields('.gd-settings-wrap');
            console.log('on-change');
        } catch(err) {
            console.log(err.message);
        }
    });

    // Conditional Fields on load
    try {
        aui_conditional_fields(".gd-settings-wrap");
    } catch(err) {
        console.log(err.message);
    }

    jQuery('.geodir-report-view').on('click', function(e) {
        if(jQuery(this).closest('tr').prop('id') && jQuery('#geodir-view-' + jQuery(this).closest('tr').prop('id')).text()) {
            $lightbox = lity('#geodir-view-' + jQuery(this).closest('tr').prop('id'));
            return false;
        }
    });

	jQuery('.gd-wp-tmpl-new').on('click', function(e) {
        if(jQuery(this).data('page')) {
            geodir_new_wp_template(this);
        }
    })
});

function geodir_setup_generate_thumbs() {
    var $el = jQuery('#geodir_tool_generate_thumbnails'),
        total, per_page;
    jQuery('.button.generate_thumbnails', $el).attr('href', 'javascript:void(0)');

    total = jQuery('.geodir-tool-stats', $el).data('total');
    if (!total) {
        return;
    }
    per_page = parseInt(jQuery('.geodir-tool-stats', $el).data('per-page'));
    if (per_page < 1) {
        per_page = 10;
    }

    jQuery('.button.generate_thumbnails', $el).on('click', function(e) {
        if (!jQuery(this).attr('disabled')) {
            geodir_bulk_generate_thumbs(total, 1, per_page);
        }
    })
}

function geodir_bulk_generate_thumbs(total, page, per_page) {
    var $el = jQuery('#geodir_tool_generate_thumbnails'),
        _seconds = 1;

    var data = {
        'action': 'geodir_tool_regenerate_thumbnails',
        'page': page,
        'per_page': per_page
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        beforeSend: function() {
            if (page == 1) {
                window.clearInterval(window._timer);
                window._timer = window.setInterval(function() {
                    _seconds++;
                    jQuery(".gd_timer", $el).text(geodir_toHMS(_seconds));
                }, 1000);
                jQuery('.geodir-tool-stats', $el).removeClass('gd-hidden');
                jQuery('#gd_progressbar', $el).progressbar({
                    value: 0
                });
                jQuery('#gd_progressbar .gd-progress-label', $el).html('<i class="fas fa-sync fa-spin" aria-hidden="true"></i> 0 / ' + total);
                jQuery('.button.generate_thumbnails', $el).attr("disabled", true);
            }
        },
        success: function(res) {
            if (res && typeof res.success != 'undefined' && res.data && typeof res.data.processed != 'undefined' && res.data.processed === 0) {
                jQuery('.button.generate_thumbnails', $el).attr("disabled", false);
                window.clearInterval(window._timer);
                jQuery('#gd_progressbar').progressbar({
                    value: 100
                });
                jQuery('#gd_progressbar .gd-progress-label', $el).html(total + ' / ' + total + ' (100%)');
            } else {
                processed = ((page - 1) * per_page) + res.data.processed;
                jQuery('#gd_progressbar').progressbar({
                    value: (total > 0 ? processed / total * 100 : 100)
                });
                jQuery('#gd_progressbar .gd-progress-label', $el).html('<i class="fas fa-sync fa-spin" aria-hidden="true"></i> ' + processed + ' / ' + total + ' (' + Math.floor(processed / total * 100) + '%)');
                geodir_bulk_generate_thumbs(total, (page + 1), per_page);
            }
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        complete: function(xhr, textStatus) {}
    });
}

function geodir_post_generate_thumbs(el) {
    var $el = jQuery(el),
        post_id;

    post_id = $el.data('post-id');
    if (!post_id) {
        return;
    }

    var data = {
        'action': 'geodir_regenerate_thumbnails',
        'post_id': post_id
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data,
        beforeSend: function() {
            jQuery($el).append('<span class="geodir-regenerate-loading"> <i class="fas fa-sync fa-spin" aria-hidden="true"></i></span>').attr("disabled", true);
        },
        success: function(data) {
            jQuery('.geodir-regenerate-loading', $el).html(' <i class="fas fa-check text-success" aria-hidden="true"></i>');
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(errorThrown);
        },
        complete: function(xhr, textStatus) {
            jQuery($el).attr("disabled", false);
            setTimeout(function() {
                jQuery('.geodir-regenerate-loading', $el).fadeOut('slow');
            }, 1250);
        }
    });
}

function geodir_toHMS(sec_num) {
    var sec_num = parseInt(sec_num, 10); // don't forget the second param
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) {
        hours = "0" + hours;
    }
    if (minutes < 10) {
        minutes = "0" + minutes;
    }
    if (seconds < 10) {
        seconds = "0" + seconds;
    }
    var time = hours + ':' + minutes + ':' + seconds;
    return time;
}

function geodir_handle_uninstall_option($el) {
	var $form = $el.closest('#mainform');
	if ($el.is(':checked')) {
		$form.find('input[type="checkbox"]').each(function(){
			if (jQuery(this).prop('id') != 'admin_uninstall') {
				jQuery(this).attr('onclick', 'return false').prop('checked', true);
				jQuery(this).css({opacity:0.5});
			}
		});
	} else {
		$form.find('input[type="checkbox"]').each(function(){
			jQuery(this).removeAttr('onclick');
			jQuery(this).css({opacity:1});
		});
	}
}

/**
 * Init the tooltips
 *
 * @since 2.0.0.69 Added check for bootstrap tooltips.
 */
function gd_init_tooltips(){

    // we create, then destroy then create so we can ajax load and then call this function with impunity.
    var $tooltips = jQuery('.gd-help-tip').tooltip();

    /**
     * 'dispose' used in Bootstrap Tooltip v4 and newer
     * 'destroy' used in Bootstrap Tooltip v3 and older
     * 'destroy' used in jQuery UI Tooltip
     */
    var $method = geodir_tooltip_version() >= 4 ? 'dispose' : 'destroy';

    $tooltips.tooltip($method).tooltip({
        content: function () {
            return jQuery(this).prop('title');
        },
        tooltipClass: 'gd-ui-tooltip',
        position: {
            my: 'center top',
            at: 'center bottom+10',
            collision: 'flipfit'
        },
        show: null,
        close: function (event, ui) {
            ui.tooltip.hover(

                function () {
                    jQuery(this).stop(true).fadeTo(400, 1);
                },

                function () {
                    jQuery(this).fadeOut("400", function () {
                        jQuery(this).remove();
                    })
                });
        }
    });

}

/* Check Uncheck All Related Options Start*/
jQuery(document).ready(function() {
    jQuery('#geodir_add_location_url').on("click",function() {
        if (jQuery(this).is(':checked')) {
            jQuery(this).closest('td').find('input').attr('checked', true).not(this).prop('disabled', false);
        } else {
            jQuery(this).closest('td').find('input').attr('checked', false).not(this).prop('disabled', true);
        }
    });

    if (jQuery('#geodir_add_location_url').is(':checked')) {
        jQuery('#geodir_add_location_url').closest('td').find('input').not(jQuery('#geodir_add_location_url')).prop('disabled', false);
    } else {
        jQuery('#geodir_add_location_url').closest('td').find('input').not(jQuery('#geodir_add_location_url')).prop('disabled', true);
    }

    function location_validation(fields) {
        var error = false;

        if (fields.val() == '') {
            jQuery(fields).closest('.gtd-formfeild').find('.gd-location_message_error').show();
            error = true;
        } else {
            jQuery(fields).closest('.gtd-formfeild').find('.gd-location_message_error').hide();
        }

        if (error) {
            return false;
        } else {
            return true;
        }
    }

    jQuery('#location_save').on("click",function() {
        var is_validate = true;

        jQuery(this).closest('form').find('.required:visible').each(function() {
            var fields = jQuery(this).find('input, select');
            if (!location_validation(fields))
                is_validate = false;
        });

        if (!is_validate) {
            return false;
        }
    });

    jQuery('.default_location_form').find(".required:visible").find('input').on("blur",function() {
        location_validation(jQuery(this));
    });

    jQuery('.default_location_form').find(".required:visible").find('select').on("change", function() {
        location_validation(jQuery(this));
    });

    jQuery('.gd-cats-display-checkbox input[type="checkbox"]').on("click",function() {
        var isChecked = jQuery(this).is(':checked');

        if (!isChecked) {
            var chkVal = jQuery(this).val();
            jQuery(this).closest('.gd-parent-cats-list').find('.gd-cat-row-' + chkVal + ' input[type="checkbox"]').prop("checked", isChecked);
        }
    });

    jQuery('.gd-import-export [data-type="date"]').each(function() {
        jQuery(this).datepicker({changeMonth: true, changeYear: true, dateFormat:'yy-mm-dd'});
    });
    jQuery('#gd-wrapper-main .wp-editor-wrap').each(function() {
        var elH = parseFloat(jQuery(this).find('.wp-editor-container').height());
        if (elH > 30) {
            jQuery(this).find('.wp-editor-container').attr('data-height', elH);
        }
    });
    setTimeout(function() {
        jQuery('#gd-wrapper-main .wp-editor-wrap').each(function() {
            var elH = parseFloat(jQuery(this).find('.wp-editor-container').attr('data-height'));
            if (elH > 30) {
                jQuery(this).find('iframe').css({
                    'height': elH + 'px'
                });
            }
        });
    }, 1000);
});
/* Check Uncheck All Related Options End*/

// Diagnosis related js starts here
function gd_progressbar(el, value, label) {
    var value = parseFloat(value);
    if ( value <= 100 ) {
        jQuery(el).find('#gd_progressbar').removeClass('ui-progressbar ui-corner-all ui-widget ui-widget-content').addClass('progress').css({"height":"2em"});
        jQuery(el).find(".ui-progressbar-value").removeClass('ui-corner-left ui-widget-header').addClass('progress-bar').css({"width":value+"%"}).show();
        if (typeof label != 'undefined') {
            jQuery(el).find('#gd_progressbar .gd-progress-label').addClass('w-100 text-center text-dark position-absolute').html(label);
            if(value>60){
                jQuery(el).find('#gd_progressbar .gd-progress-label').removeClass('text-dark').addClass('text-light');
            }
        }
    }else if(value==100){
        jQuery(el).find(".ui-progressbar-value").addClass('bg-success');
    }
}

jQuery(document).ready(function($) {

    jQuery('#geodir_location_prefix').attr('disabled', 'disabled');
    jQuery('.button-primary').on("click",function() {
        var error = false;
        var characterReg = /^\s*[a-zA-Z0-9,\s]+\s*$/;
        var listing_prefix = jQuery('#geodir_listing_prefix').val();
        var location_prefix = jQuery('#geodir_location_prefix').val();
        var listingurl_separator = jQuery('#geodir_listingurl_separator').val();
        var detailurl_separator = jQuery('#geodir_detailurl_separator').val();

        if (listing_prefix == '') {
            alert(geodir_params.listing_url_prefix_msg);
            jQuery('#geodir_listing_prefix').focus();
            error = true;
        }

        if (/^[a-z0-90\_9_-]*$/.test(listing_prefix) == false && listing_prefix != '') {
            jQuery('#geodir_listing_prefix').focus();
            alert(geodir_params.invalid_listing_prefix_msg);
            error = true;
        }

        if (error == true) {
            return false;
        } else {
            return true;
        }
    });

    jQuery('.map_post_type').on("click",function() {
        var divshow = jQuery(this).val();

        if (jQuery(this).is(':checked')) {
            jQuery('#' + divshow + ' input').each(function() {
                jQuery(this).attr('checked', 'checked');
            });
        } else {
            jQuery('#' + divshow + ' input').each(function() {
                jQuery(this).removeAttr('checked');
            });
        }
    });
});

// fix for related accents search.
function gd_replace_accents (s) {
    var chars = [{'base':'A', 'letters':/[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g},
        {'base':'AA','letters':/[\uA732]/g},
        {'base':'AE','letters':/[\u00C6\u01FC\u01E2]/g},
        {'base':'AO','letters':/[\uA734]/g},
        {'base':'AU','letters':/[\uA736]/g},
        {'base':'AV','letters':/[\uA738\uA73A]/g},
        {'base':'AY','letters':/[\uA73C]/g},
        {'base':'B', 'letters':/[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g},
        {'base':'C', 'letters':/[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g},
        {'base':'D', 'letters':/[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g},
        {'base':'DZ','letters':/[\u01F1\u01C4]/g},
        {'base':'Dz','letters':/[\u01F2\u01C5]/g},
        {'base':'E', 'letters':/[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g},
        {'base':'F', 'letters':/[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g},
        {'base':'G', 'letters':/[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g},
        {'base':'H', 'letters':/[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g},
        {'base':'I', 'letters':/[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g},
        {'base':'J', 'letters':/[\u004A\u24BF\uFF2A\u0134\u0248]/g},
        {'base':'K', 'letters':/[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g},
        {'base':'L', 'letters':/[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g},
        {'base':'LJ','letters':/[\u01C7]/g},
        {'base':'Lj','letters':/[\u01C8]/g},
        {'base':'M', 'letters':/[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g},
        {'base':'N', 'letters':/[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g},
        {'base':'NJ','letters':/[\u01CA]/g},
        {'base':'Nj','letters':/[\u01CB]/g},
        {'base':'O', 'letters':/[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g},
        {'base':'OI','letters':/[\u01A2]/g},
        {'base':'OO','letters':/[\uA74E]/g},
        {'base':'OU','letters':/[\u0222]/g},
        {'base':'P', 'letters':/[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g},
        {'base':'Q', 'letters':/[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g},
        {'base':'R', 'letters':/[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g},
        {'base':'S', 'letters':/[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g},
        {'base':'T', 'letters':/[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g},
        {'base':'TZ','letters':/[\uA728]/g},
        {'base':'U', 'letters':/[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g},
        {'base':'V', 'letters':/[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g},
        {'base':'VY','letters':/[\uA760]/g},
        {'base':'W', 'letters':/[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g},
        {'base':'X', 'letters':/[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g},
        {'base':'Y', 'letters':/[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g},
        {'base':'Z', 'letters':/[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g},
        {'base':'a', 'letters':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g},
        {'base':'aa','letters':/[\uA733]/g},
        {'base':'ae','letters':/[\u00E6\u01FD\u01E3]/g},
        {'base':'ao','letters':/[\uA735]/g},
        {'base':'au','letters':/[\uA737]/g},
        {'base':'av','letters':/[\uA739\uA73B]/g},
        {'base':'ay','letters':/[\uA73D]/g},
        {'base':'b', 'letters':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g},
        {'base':'c', 'letters':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g},
        {'base':'d', 'letters':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g},
        {'base':'dz','letters':/[\u01F3\u01C6]/g},
        {'base':'e', 'letters':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g},
        {'base':'f', 'letters':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g},
        {'base':'g', 'letters':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g},
        {'base':'h', 'letters':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g},
        {'base':'hv','letters':/[\u0195]/g},
        {'base':'i', 'letters':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g},
        {'base':'j', 'letters':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g},
        {'base':'k', 'letters':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g},
        {'base':'l', 'letters':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g},
        {'base':'lj','letters':/[\u01C9]/g},
        {'base':'m', 'letters':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g},
        {'base':'n', 'letters':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g},
        {'base':'nj','letters':/[\u01CC]/g},
        {'base':'o', 'letters':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g},
        {'base':'oi','letters':/[\u01A3]/g},
        {'base':'ou','letters':/[\u0223]/g},
        {'base':'oo','letters':/[\uA74F]/g},
        {'base':'p','letters':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g},
        {'base':'q','letters':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g},
        {'base':'r','letters':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g},
        {'base':'s','letters':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g},
        {'base':'t','letters':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g},
        {'base':'tz','letters':/[\uA729]/g},
        {'base':'u','letters':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g},
        {'base':'v','letters':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g},
        {'base':'vy','letters':/[\uA761]/g},
        {'base':'w','letters':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g},
        {'base':'x','letters':/[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g},
        {'base':'y','letters':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g},
        {'base':'z','letters':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g}];
    for (var i=0; i < chars.length; i++) {
        s = s.replace(chars[i].letters, chars[i].base);
    }
    return s;
}

jQuery(function($) {

    try {
        $(document.body).on('geodir-select-init', function() {
            // Regular select boxes
            $(':input.geodir-select').filter(':not(.enhanced)').each(function() {
                var $this = $(this);
				var select2_args = $.extend({
                    minimumResultsForSearch: ($this.data('tags') ? 0 : (parseInt($this.data('min-results')) > 0 ? parseInt($this.data('min-results')) : 10)),
                    allowClear: $(this).data('allow_clear') ? true : false,
                    containerCssClass: 'gd-select2-selection',
                    dropdownCssClass: 'gd-select2-dropdown',
                    placeholder: $(this).data('placeholder'),
                    width: 'element',
                    dropdownAutoWidth : true,
                    templateSelection: function(data) {
						return geodirSelect2TemplateSelection($this, data, true);
					},
					templateResult: function(data) {
						return geodirSelect2TemplateSelection($this, data);
					}
                }, geodirSelect2FormatString());
                var $select2 = $(this).select2(select2_args);
                $select2.addClass('enhanced');
                $select2.data('select2').$container.addClass('gd-select2-container');
                $select2.data('select2').$dropdown.addClass('gd-select2-container');
				if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');
					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
				}
				$this.on('change.select2', function(e) {
					geodirSelect2OnChange($this, $select2);
				});
				if ($this.data('cmultiselect') || $this.data('cselect')) {
					$this.trigger('change.select2');
				}
            });
            $(':input.geodir-select-nostd').filter(':not(.enhanced)').each(function() {
                var $this = $(this);
				var select2_args = $.extend({
                    minimumResultsForSearch: ($this.data('tags') ? 0 : (parseInt($this.data('min-results')) > 0 ? parseInt($this.data('min-results')) : 10)),
                    allowClear: true,
                    containerCssClass: 'gd-select2-selection',
                    dropdownCssClass: 'gd-select2-dropdown',
                    placeholder: $(this).data('placeholder'),
					templateSelection: function(data) {
						return geodirSelect2TemplateSelection($this, data, true);
					},
					templateResult: function(data) {
						return geodirSelect2TemplateSelection($this, data);
					}
                }, geodirSelect2FormatString());
                var $select2 = $(this).select2(select2_args);
                $select2.addClass('enhanced');
                $select2.data('select2').$container.addClass('gd-select2-container');
                $select2.data('select2').$dropdown.addClass('gd-select2-container');
				if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');
					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
				}
				$this.on('change.select2', function(e) {
					geodirSelect2OnChange($this, $select2);
				});
				if ($this.data('cmultiselect') || $this.data('cselect')) {
					$this.trigger('change.select2');
				}
            });
			// Ajax user search
			$(':input.geodir-user-search').filter(':not(.enhanced)').each(function() {
				var select2_args = {
					allowClear: $(this).data('allow_clear') ? true : false,
					placeholder: $(this).data('placeholder'),
					minimumInputLength: $(this).data('min_input_length') ? $(this).data('min_input_length') : '1',
					escapeMarkup: function(m) {
						return m;
					},
					ajax: {
						url: geodir_params.gd_ajax_url,
						dataType: 'json',
						delay: 1000,
						data: function(params) {
							return {
								term: params.term,
								action: 'geodir_json_search_users',
								security: geodir_params.search_users_nonce,
								exclude: $(this).data('exclude')
							};
						},
						processResults: function(data) {
							var terms = [];
							if (data) {
								$.each(data, function(id, text) {
									terms.push({
										id: id,
										text: text
									});
								});
							}
							return {
								results: terms
							};
						},
						cache: true
					}
				};
				select2_args = $.extend(select2_args, geodirSelect2FormatString());
				var $select2 = $(this).select2(select2_args);
				$select2.addClass('enhanced');
				$select2.data('select2').$container.addClass('gd-select2-container');
				$select2.data('select2').$dropdown.addClass('gd-select2-container');
				if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');
					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
				}
			});
			// select2 autocomplete search
			$(':input.geodir-select-search').filter(':not(.enhanced)').each(function() {
				var search = $(this).data('select-search');
				if ( ! search ) {
					return true;
				}
				var select2_args = {
					allowClear: $(this).data('allow_clear') ? true : false,
					placeholder: $(this).data('placeholder'),
					minimumInputLength: $(this).data('min-input-length') ? $(this).data('min-input-length') : '2',
					escapeMarkup: function(m) {
						return m;
					},
					ajax: {
						url: geodir_params.ajax_url,
						type: 'POST',
						dataType: 'json',
						delay: 250,
						data: function(params) {
							var data = {
								term: params.term,
								action: 'geodir_json_search_' + search,
								security: $(this).data('nonce')
							};
							if ( $(this).data('exclude') ) {
								data.exclude = $(this).data('exclude');
							}
							if ( $(this).data('include') ) {
								data.include = $(this).data('include');
							}
							if ( $(this).data('limit') ) {
								data.limit = $(this).data('limit');
							}
							return data;
						},
						processResults: function(data) {
							var terms = [];
							if (data) {
								$.each(data, function(id, text) {
									terms.push({
										id: id,
										text: text
									});
								});
							}
							return {
								results: terms
							};
						},
						cache: true
					}
				};
				select2_args = $.extend(select2_args, geodirSelect2FormatString());
				var $select2 = $(this).select2(select2_args);
				$select2.addClass('enhanced');
				$select2.data('select2').$container.addClass('gd-select2-container');
				$select2.data('select2').$dropdown.addClass('gd-select2-container');

				if ($(this).data('sortable')) {
					var $select = $(this);
					var $list = $(this).next('.select2-container').find('ul.select2-selection__rendered');

					$list.sortable({
						placeholder: 'ui-state-highlight select2-selection__choice',
						forcePlaceholderSize: true,
						items: 'li:not(.select2-search__field)',
						tolerance: 'pointer',
						stop: function() {
							$($list.find('.select2-selection__choice').get().reverse()).each(function() {
								var id = $(this).data('data').id;
								var option = $select.find('option[value="' + id + '"]')[0];
								$select.prepend(option);
							});
						}
					});
					// Keep multiselects ordered alphabetically if they are not sortable.
				} else if ($(this).prop('multiple')) {
					$(this).on('change', function() {
						var $children = $(this).children();
						$children.sort(function(a, b) {
							var atext = a.text.toLowerCase();
							var btext = b.text.toLowerCase();

							if (atext > btext) {
								return 1;
							}
							if (atext < btext) {
								return -1;
							}
							return 0;
						});
						$(this).html($children);
					});
				}
			});
        }).trigger('geodir-select-init');
        $('html').on('click', function(event) {
            if (this === event.target) {
                $('.geodir-select, :input.geodir-user-search, :input.geodir-select-search').filter('.select2-hidden-accessible').select2('close');
            }
        });
    } catch (err) {
        window.console.log(err);
    }
});

function geodirSelect2FormatString() {
    return {
        'language': {
            errorLoading: function() {
                // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
                return geodir_params.i18n_searching;
            },
            inputTooLong: function(args) {
                var overChars = args.input.length - args.maximum;
                if (1 === overChars) {
                    return geodir_params.i18n_input_too_long_1;
                }
                return geodir_params.i18n_input_too_long_n.replace('%item%', overChars);
            },
            inputTooShort: function(args) {
                var remainingChars = args.minimum - args.input.length;
                if (1 === remainingChars) {
                    return geodir_params.i18n_input_too_short_1;
                }
                return geodir_params.i18n_input_too_short_n.replace('%item%', remainingChars);
            },
            loadingMore: function() {
                return geodir_params.i18n_load_more;
            },
            maximumSelected: function(args) {
                if (args.maximum === 1) {
                    return geodir_params.i18n_selection_too_long_1;
                }
                return geodir_params.i18n_selection_too_long_n.replace('%item%', args.maximum);
            },
            noResults: function() {
                return geodir_params.i18n_no_matches;
            },
            searching: function() {
                return geodir_params.i18n_searching;
            }
        }
    };
}

function geodirSelect2TemplateSelection($el, data, main) {
    if (typeof main != 'undefined' && main && $el.data('cmultiselect')) {
        var rEl;
        rEl = '<span class="select2-selection_gd_custom">';
          rEl += '<span class="select2-selection_gd_text">' + data.text + '</span>';
          rEl += '<span class="select2-selection_gd_field">';
            rEl += '<input type="radio" title="'+geodir_params.i18n_set_as_default+'" class="select2-selection_gd_v_' + (data.id != 'undefined' ? data.id : '') + '" onchange="jQuery(this).closest(\'form\').find(\'input[name=' + $el.data('cmultiselect') + ']\').val(jQuery(this).val());" value="' + (data.id != 'undefined' ? data.id : '') + '" name="' + $el.data('cmultiselect') + '_radio">';
          rEl += '</span>';
        rEl += '</span>';
        return jQuery(rEl);
    }
	$option = jQuery(data.element);
	if ($el.data('fa-icons') && $option.data('fa-icon')) {
        var style = '';
		if (typeof main != 'undefined' && main) {
			if ($el.data('fa-color')) {
				style = ' style="color:' + $el.data('fa-color') + '"';
			} else if ($option.data('fa-color')) {
				style = ' style="color:' + $option.data('fa-color') + '"';
			}
		}
		rEl = '<span class="select2-selection_gd_custom">';
          rEl += '<i class="' + $option.data('fa-icon') + '"' + style + '></i> ';
		  rEl += data.text;
        rEl += '</span>';
        return jQuery(rEl);
    }
	if ($el.data('dashicons') && $option.data('dashicon')) {
		return jQuery('<span class="select2-selection_gd_dashicon"><span class="dashicons ' + $option.data('dashicon') + '"></span><span class="gd-dashicon-text"> ' + data.text + '</span></span>');
    }
    return data.text;
}

function geodirSelect2OnChange($this, $select2) {
    var $cont, $field, value, $input;
	$cont = $select2.data('select2').$container;
    if ($this.data('cmultiselect')) {
        $field = $this.closest('form').find('input[name=' + $this.data('cmultiselect') + ']');
        value = $field.val() != 'undefined' ? $field.val() : '';
        if (jQuery('.select2-selection_gd_field', $cont).length > 0) {
            if (jQuery('.select2-selection_gd_v_' + value).length > 0) {
                $input = jQuery('.select2-selection_gd_v_' + value);
            } else {
                $input = jQuery('.select2-selection_gd_field:first', $cont).find('[type="radio"]');
            }
            $input.prop('checked', true).trigger('change');
        } else {
            $field.val('');
        }
    }
	if ($this.data('cselect')) {
        $field = $this.closest('form').find('input[name=' + $this.data('cselect') + ']');
        $field.val($this.val());
    }
}

function gdSetClipboard( data, $el ) {
	if ( 'undefined' === typeof $el ) {
		$el = jQuery( document );
	}
	var $temp_input = jQuery( '<textarea style="opacity:0">' );
	jQuery( 'body' ).append( $temp_input );
	$temp_input.val( data ).select();

	$el.trigger( 'beforecopy' );
	try {
		document.execCommand( 'copy' );
		$el.trigger( 'aftercopy' );
	} catch ( err ) {
		$el.trigger( 'aftercopyfailure' );
	}

	$temp_input.remove();
}

function gdClearClipboard() {
	gdSetClipboard( '' );
}

jQuery(function(){
    if (window.gdMaps === 'google') {
        console.log('Google Maps API Loaded :)');
        jQuery('body').addClass('gd-google-maps');
    } else if (window.gdMaps === 'osm') {
        console.log('Leaflet | OpenStreetMap API Loaded :)');
        jQuery('body').addClass('gd-osm-gmaps');
    } else {
        console.log('Maps API Not Loaded :(');
        jQuery('body').addClass('gd-no-gmaps');
    }
});
var gdMaps = null
if ((window.gdSetMap=='google' || window.gdSetMap=='auto') && window.google && typeof google.maps!=='undefined') {
    gdMaps = 'google';
} else if ((window.gdSetMap=='osm' || window.gdSetMap=='auto') && typeof L!=='undefined' && typeof L.version!=='undefined') {
    gdMaps = 'osm';
}
window.gdMaps = window.gdMaps || gdMaps;


function init_advanced_settings(){
    jQuery( ".gd-advanced-toggle" ).off("click").on("click",function() {
        jQuery(".gd-advanced-toggle").toggleClass("gda-hide");
        console.log('toggle');
        // jQuery(".gd-advanced-setting, #default_location_set_address_button").toggleClass("gda-show d-none");
        jQuery(".gd-advanced-setting, #default_location_set_address_button").collapse('toggle');
    });
}

function init_advanced_settings_field(){
    jQuery( "#geodir-field-settings .gd-advanced-toggle-field" ).off("click").on("click",function() {
        jQuery("#geodir-field-settings .gd-advanced-toggle-field").toggleClass("gda-hide");
        console.log('toggle');
        // jQuery(".gd-advanced-setting, #default_location_set_address_button").toggleClass("gda-show d-none");
        jQuery("#geodir-field-settings .gd-advanced-setting").collapse('toggle');
    });
}

function gd_recommended_install_plugin($this,$slug,$nonce){
    //alert($slug);

    var data = {
        'action':           'install-plugin',
        '_ajax_nonce':       $nonce,
        'slug':              $slug
    };

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery($this).html('<i class="fas fa-sync fa-spin" ></i> ' + jQuery($this).data("text-installing")).attr("disabled", true);
        },
        success: function(data)
        {
            // if(data.data){
            //     jQuery( ".geodir-wizard-widgets-result" ).text(data.data);
            // }
            console.log(data);
            if(data.success){

                // old way
                // jQuery($this).html(jQuery($this).data("text-installed")).removeClass('button-primary').addClass('button-secondary');
                // if(data.data.activateUrl){
                //     gd_recommended_activate_plugin($this,data.data.activateUrl,$slug);
                // }

                // new way
                if(data.data.activateUrl){
                    jQuery($this).html(jQuery($this).data("text-activate")).removeAttr('target').removeAttr('onclick').attr('href',data.data.activateUrl).attr("disabled", false);
                }else{
                    jQuery($this).html(jQuery($this).data("text-installed")).removeClass('button-primary').addClass('button-secondary');
                }

            }else{
                jQuery($this).html(jQuery($this).data("text-error"));
                alert('something went wrong');
            }
        }
    });
}

function gd_recommended_buy_popup($this,$slug,$nonce,$item_id){

    $url = jQuery($this).attr("href");
    $title = jQuery($this).parent().parent().find(".gd-product-title h3").html();
    jQuery('#gd-recommended-buy .gd-recommended-buy-title').html($title);
    jQuery('#gd-recommended-buy .gd-recommended-buy-link').attr("href",$url);
    $licenced = jQuery($this).data("licensing");
    $single_licence = jQuery($this).data("licence");

    if($licenced && !$single_licence){
        $lightbox = lity('#gd-recommended-buy');

        jQuery(".gd-recommended-buy-button").off('click').on("click",function(){
            $licence =  jQuery(".gd-recommended-buy-key").val();
            if($licenced && $licence==''){
                alert("Please enter a key");
            }else{
                jQuery(".gd-recommended-buy-key").val('');
                $lightbox.close();
                gd_recommended_addon_install_plugin($this,$slug,$nonce,$item_id,$licence);
            }
        });
    }else if($single_licence){
        // exup_silent_activate_licence_key(pluginName,key,exupNonce)
        gd_recommended_addon_install_plugin($this,$slug,$nonce,$item_id,$single_licence);
    }else{
        gd_recommended_addon_install_plugin($this,$slug,$nonce,$item_id,'free');
    }

}

function gd_recommended_addon_install_plugin($this,$slug,$nonce,$item_id,$licence){

    // @todo remove once out of beta
    //alert("This feature is not yet implemented in the beta");
    //return false;

    var data = {
        'action':           'install-plugin',
        '_ajax_nonce':       $nonce,
        'slug':              $slug,
        'update_url':        "https://wpgeodirectory.com",
        'item_id':           $item_id,
        'wpeu_activate':     true
        //'license':           $licence
    };

    if($licence && $licence!='free'){
        data.license = $licence;
    }else if($licence=='free'){
        data.free_download = '1';
    }

    jQuery.ajax({
        type: "POST",
        url: ajaxurl,
        data: data, // serializes the form's elements.
        beforeSend: function()
        {
            jQuery($this).html('<i class="fas fa-sync fa-spin" ></i> ' + jQuery($this).data("text-installing")).attr("disabled", true);
        },
        success: function(data)
        {
            // if(data.data){
            //     jQuery( ".geodir-wizard-widgets-result" ).text(data.data);
            // }
            console.log(data);
            if(data.success){
                jQuery($this).html(jQuery($this).data("text-installed")).removeClass('button-primary').addClass('button-secondary');

                //gd_wizard_check_plugins();
                //gd_wizard_install_plugins($nonce);
                if(data.data.activateUrl){
                    gd_recommended_activate_plugin($this,data.data.activateUrl,$slug);
                }
            }else{
                jQuery($this).html(jQuery($this).data("text-error"));
                alert('something went wrong');

            }
        }
    });
}

/**
 * Try to silently activate the plugin after install.
 *
 * @param $url
 */
function gd_recommended_activate_plugin($this,$url,$slug){

    jQuery.post($url, function(data, status){
        console.log($slug+'plugin activated')
    });
}

// Some settings validation


function gd_set_button_installing($this){
    jQuery($this).html('<i class="fas fa-sync fa-spin" ></i> ' + jQuery($this).data("text-installing")).attr("disabled", true);
}

function gd_settings_validation(){
    jQuery("#mainform").on("submit",function(e){
        $error = '';

        if(jQuery('#page_location').length){
            var arr = [];
            jQuery("#mainform select").each(function(){
                var value = jQuery(this).val();
                if(value ){
                    if (arr.indexOf(value) == -1)
                        arr.push(value);
                    else
                        $error = geodir_params.txt_page_settings;
                }
               });
        }

        if($error != ''){
            console.log(arr);
            alert($error );
            return false;
        }

    });
}

function geodir_fill_timezone(prefix, tz_prefix) {
	if (!tz_prefix) {
		tz_prefix = prefix;
	}
	var $form = jQuery('[name="' + tz_prefix + 'timezone_string"]').closest('form');
	var lat = jQuery('[name="' + prefix + 'latitude"]', $form).val();
	var lng = jQuery('[name="' + prefix + 'longitude"]', $form).val();
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
						jQuery('[name="' + tz_prefix + 'timezone_string"]', $form).val(data.timeZoneId).trigger("change");
					}
				} else if (res.data) {
					data = res.data;
					if (data.error) {
						console.log(data.error);
					}
				}
			}
		});
	}
}

function geodir_seconds_to_hm(value) {
	var prefix = value < 0 ? '-' : '+';
	value = Math.abs(value);
	var hours = Math.floor(value / 3600);
	var minutes = Math.floor((value - (hours * 3600)) / 60);
	var result = hours;
	result += ":" + (minutes < 10 ? "0" + minutes : minutes);
	result = prefix + '' +  result;
	return result;
}

function geodir_init_helper_tags(){
    jQuery(".geodir-helper-tags li").on("click",function(event){



        $tag = jQuery(this).find('.geodir-helper-tag').text();
        if(jQuery('#geodir-helper-tag-input').length){
            jQuery('#geodir-helper-tag-input').val($tag);
            jQuery('#geodir-helper-tag-input').select();
        }else{
            jQuery( "body" ).append( "<input type='text' id='geodir-helper-tag-input'>" );
            jQuery('#geodir-helper-tag-input').val($tag);
            jQuery('#geodir-helper-tag-input').select();
        }
        //$temp_input = jQuery('#geodir-helper-tag-input').
        //jQuery(this).selText();
        if(document.execCommand("Copy")){
            jQuery('#geodir-helper-tag-input').remove();
            jQuery(this).find('.geodir-helper-tag').addClass('geodir-tag-copied');
            $this = this;
            setTimeout(function(){
                jQuery($this).find('.geodir-helper-tag').removeClass('geodir-tag-copied');
            }, 1000);
        }

    });
}


/**
 * Init the rating inputs.
 */
function geodir_admin_init_rating_input(){
    /**
     * Rating script for ratings inputs.
     * @info This is shared in both post.js and admin.js any changes shoudl be made to both.
     */
    jQuery(".gd-rating-input").each(function () {
        $total = jQuery(this).find('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img').length;
        $parent = this;

        // set the current star value and text
        $value = jQuery(this).closest('.gd-rating-input').find('input').val();
        if($value > 0){
            jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground').width( $value / $total * 100 + '%');
            jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text( jQuery(this).closest('.gd-rating-input').find('svg, img'+':eq('+ ($value - 1) +'), i'+':eq('+ ($value - 1) +')').attr("title"));
        }

        // loop all rating stars
        jQuery(this).find('i,svg, img').each(function (index) {
            $original_rating = jQuery(this).closest('.gd-rating-input').find('input').val();
            $total = jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img').length;
            $original_percent = $original_rating / $total * 100;
            $rating_set = false;

            jQuery(this).hover(
                function () {
                    $total = jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img').length;
                    $original_rating = jQuery(this).closest('.gd-rating-input').find('input').val();
                    $original_percent = $original_rating / $total * 100;
                    $original_rating_text = jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text();

                    $percent = 0;
                    $rating = index + 1;
                    $rating_text = jQuery(this).attr("title");
                    if ($rating > $total) {
                        $rating = $rating - $total;
                    }
                    $percent = $rating / $total * 100;
                    jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground').width($percent + '%');
                    jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text($rating_text);
                },
                function () {
                    if (!$rating_set) {
                        jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground').width($original_percent + '%');
                        jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text($original_rating_text);
                    } else {
                        $rating_set = false;
                    }
                }
            );

            jQuery(this).on("click",function () {
                $original_percent = $percent;
                $original_rating = $rating;
                jQuery(this).closest('.gd-rating-input').find('input').val($rating);
                jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text($rating_text);
                $rating_set = true;
            });

        });

    });
}

/**
 * Get Bootstrap tooltip version.
 */
function geodir_tooltip_version() {
    var ttv = 0;
    if (typeof jQuery.fn === 'object' && typeof jQuery.fn.tooltip === 'function' && typeof jQuery.fn.tooltip.Constructor === 'function' && typeof jQuery.fn.tooltip.Constructor.VERSION != 'undefined') {
        ttv = parseFloat(jQuery.fn.tooltip.Constructor.VERSION);
    }
    return ttv;
}

var gd_console_logging = false;
var gd_has_map_error = false;
function geodir_validate_google_api_key($key,$id){
    gd_has_map_error = false;
    console.log($key);

    if($key.length < 10 ){
        aui_toast($id,'error', geodir_params.txt_google_key_error_missing );
        return;
    }

    //brave browser blocks this check if shield is up
    if(navigator.brave){
        aui_toast($id,'error', geodir_params.txt_google_key_error_brave);
    }

    if(!gd_console_logging){
        gd_console_logging = true;
        console.defaultError = console.error.bind(console);
        console.errors = [];
        console.error = function(){
            // default &  console.error()
            console.defaultError.apply(console, arguments);
            // our check
            geodir_get_map_error(arguments);
        }

        console.defaultWarn = console.warn.bind(console);
        console.warns = [];
        console.warn = function(){
            // default &  console.warn()
            console.defaultWarn.apply(console, arguments);

            // our check
            geodir_get_map_error(arguments);
        }
    }

    //aui_toast($id,$type,$title,$title_small,$body,$time,$can_close)
    $title = geodir_params.txt_google_key_verifying + ' <div class="spinner-border spinner-border-sm" role="status"><span class="sr-only visually-hidden"></span></div>';
    aui_toast('geodir_validate_google_api_key','info', $title ,'','',10000,false);
    setTimeout(function (){
        if( !gd_has_map_error ){
            aui_toast('geodir_validate_google_api_key_success','success', 'Key Looks Good' ,'','',10000,false);
        }
    }, 10000);

    jQuery.getScript( "https://maps.google.com/maps/api/js?language=en&key="+$key+"&libraries=places" )
        .done(function( script, textStatus ) {
            console.log( textStatus );
            jQuery('#hidden-map-test').remove();
            jQuery(document.body).append("<div id='hidden-map-test' style='height: 1px;width:1px;margin-top: -100px;'></div>");
            map = new google.maps.Map(document.getElementById("hidden-map-test"), {
                center: { lat: 0, lng: 0 },
                zoom: 14,
            });

        })
        .fail(function( jqxhr, settings, exception ) {
            alert( "Triggered ajaxError handler." );
        });
}

function geodir_get_map_error($message){
    //alert(JSON.stringify(arguments));
    jQuery.each($message, function(propName, propVal) {
       $body = '';$id = ''; $docs = '';
        //console.log(propName, propVal);
        if (propVal.indexOf("#key-looks-like-project-number") >= 0){
            $id = 'geodir_validate_google_api_key_error_project';
            $body = geodir_params.txt_google_key_error_project;
            $docs = '<a href="https://docs.wpgeodirectory.com/article/186-google-api" target="_blank" class="btn btn-light d-block mt-2 text-dark">'+geodir_params.txt_documentation+'</a>';
        }else if(propVal.indexOf("#invalid-key") >= 0){
            $id = 'geodir_validate_google_api_key_error_invalid';
            $body = geodir_params.txt_google_key_error_invalid;
            $docs = '<a href="https://docs.wpgeodirectory.com/article/186-google-api" target="_blank" class="btn btn-light d-block mt-2 text-dark">'+geodir_params.txt_documentation+'</a>';
        }else if(propVal.indexOf("#referer-not-allowed-map-error") >= 0){
            $id = 'geodir_validate_google_api_key_error_referer';
            $body = geodir_params.txt_google_key_error_referer;
            $docs = '<a href="https://docs.wpgeodirectory.com/article/186-google-api#step-4-restrict-api-key-access" target="_blank" class="btn btn-light d-block mt-2 text-dark">'+geodir_params.txt_documentation+'</a>';
        }else if(propVal.indexOf("You must enable Billing") >= 0){
            $id = 'geodir_validate_google_api_key_error_billing';
            $body = geodir_params.txt_google_key_error_billing;
            $docs = '<a href="https://console.cloud.google.com/project/_/billing/enable" target="_blank" class="btn btn-light d-block mt-2 text-dark">'+geodir_params.txt_google_key_enable_billing+'</a>';
        }

        if($id){
            gd_has_map_error = true;
            jQuery('#geodir_validate_google_api_key').toast('hide');
            aui_toast($id,'error', geodir_params.txt_google_key_error ,'',$body + $docs ,60000,true);
        }
    });
}

function geodir_new_wp_template(el) {
	var $btn = jQuery(el);

	aui_confirm(geodir_params.confirm_new_wp_template, geodir_params.txt_continue, geodir_params.txt_cancel).then(function(confirmed) {
		if (confirmed) {
			var page = $btn.data('page');

			var data = {
				'action': 'geodir_new_wp_template',
				'gd_page': page,
				'gd_post_type': $btn.data('post-type'),
				'gd_cpt_name': jQuery('#mainform input#name').val(),
				security: geodir_params.basic_nonce
			};

			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: data,
				dataType: 'json',
				beforeSend: function(xhr, obj) {
					$btn.prop("disabled", true);
				}
			})
			.done(function(data, textStatus, jqXHR) {
				if (typeof data == 'object') {
					if (data.data.post_id && data.data.post_title) {
						jQuery("select#" + page).append('<option value="' + data.data.post_id + '">' + data.data.post_title + '</option>');
                        jQuery("select#" + page).val(data.data.post_id).trigger("change.select2");
					}

					if (data.data.message) {
						alert(data.data.message);
						aui_toast('geodir_new_wp_template_error','error', data.data.message);
					}

					if (true === data.data.reload && parseInt($btn.data('reload')) === 1) {
						window.location.reload();
						return;
					}
				}
			})
			.always(function(data, textStatus, jqXHR) {
				$btn.prop("disabled", false);
			});
		}
	});
}
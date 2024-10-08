jQuery.fn.exists = function() {
    return jQuery(this).length > 0;
}

jQuery(document).ready(function($) {
    if ($(".plupload-upload-uic").exists()) {
        var pconfig = false;
        var msgErr = '';
        var post_id = '';
        // set the post id
        if (jQuery("#geodirectory-add-post input[name='ID']").length) {
            post_id = jQuery("#geodirectory-add-post input[name='ID']").val(); // frontend
        } else {
            post_id = jQuery("#post input[name='post_ID']").val(); // backend
        }


        $(".plupload-upload-uic").each(function() {
            var $this = $(this);
            var id1 = $this.attr("id");
            var imgId = id1.replace("plupload-upload-ui", "");

            plu_show_thumbs(imgId);

            pconfig = JSON.parse(geodir_plupload_params.base_plupload_config);
            pconfig["browse_button"] = imgId + pconfig["browse_button"];
            pconfig["container"] = imgId + pconfig["container"];
            if (jQuery('#' + imgId + 'dropbox').length) {
                pconfig["drop_element"] = imgId + 'dropbox';
            } // only add drop area if there is one
            pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
            pconfig["multipart_params"]["imgid"] = imgId;
            pconfig["multipart_params"]["post_id"] = post_id;
            //pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");

            if ($this.hasClass("plupload-upload-uic-multiple")) {
                pconfig["multi_selection"] = true;
            }

            var allowed_exts = jQuery('#' + imgId + '_allowed_types').val();
            allowed_exts = allowed_exts && allowed_exts != '' ? allowed_exts : '';
            if (imgId == 'post_images' && typeof geodir_params.gd_allowed_img_types != 'undefined' && geodir_params.gd_allowed_img_types != '') {
                allowed_exts = geodir_params.gd_allowed_img_types;
            }

            if (allowed_exts && allowed_exts != '') {
                var txt_all_files = (typeof geodir_params.txt_all_files != 'undefined' && geodir_params.txt_all_files != '') ? geodir_params.txt_all_files : 'Allowed files';
                pconfig['filters'] = [{
                    'title': txt_all_files,
                    'extensions': allowed_exts
                }];
            }

            var uploader = new plupload.Uploader(pconfig);
            uploader.bind('Init', function(up) {
                //alert(1);
            });

            uploader.bind('Init', function(up, params) {
                if (uploader.features.dragdrop) {
                    var drop_id = imgId + 'dropbox';
                    var target = jQuery('#' + drop_id);

                    target.on("dragenter", function(event) {
                        target.addClass("dragover");
                    });

                    target.on("dragleave", function(event) {
                        target.removeClass("dragover");
                    });

                    target.on("drop", function() {
                        target.removeClass("dragover");
                    });
                }

                /* Fix: iPhone(iOS Safari) don't triggers upload on hidden element */
                if ($this.find('.moxie-shim').length) {
                    $this.find('.moxie-shim').css({'position':'initial'});
                }
            });

            uploader.init();

            /* Fires when a file is to be uploaded by the runtime. */
            uploader.bind('UploadFile', function(up, file) {
                if (imgId == 'post_images') {
                    window.geodirUploading = true;
                }
            });

            /* Fires when all files in a queue are uploaded. */
            uploader.bind('UploadComplete', function(up, files) {
                if (imgId == 'post_images') {
                    window.geodirUploading = false;
                }
            });

            uploader.bind('Error', function(up, files) {
                if (imgId == 'post_images') {
                    window.geodirUploading = false;
                }
                if (files.code == -600) {
                    jQuery('#' + imgId + 'upload-error').removeClass('d-none').addClass('d-block');

                    if (typeof geodir_params.err_max_file_size != 'undefined' && geodir_params.err_max_file_size != '') {
                        msgErr = geodir_params.err_max_file_size;
                    } else {
                        msgErr = 'File size error : You tried to upload a file over %s';
                    }
                    msgErr = msgErr.replace("%s", geodir_plupload_params.upload_img_size);

                    jQuery('#' + imgId + 'upload-error').html(msgErr);
                } else if (files.code == -601) {
                    jQuery('#' + imgId + 'upload-error').removeClass('d-none').addClass('d-block');

                    if (typeof geodir_params.err_file_type != 'undefined' && geodir_params.err_file_type != '') {
                        msgErr = geodir_params.err_file_type;
                    } else {
                        msgErr = 'File type error. Allowed file types: %s';
                    }
                    if (imgId == 'post_images') {
                        var txtReplace = allowed_exts != '' ? "." + allowed_exts.replace(/,/g, ", .") : '*';
                        msgErr = msgErr.replace("%s", txtReplace);
                    } else {
                        msgErr = msgErr.replace("%s", jQuery("#" + imgId + "_allowed_types").attr('data-exts'));
                    }

                    jQuery('#' + imgId + 'upload-error').html(msgErr);
                } else {
                    jQuery('#' + imgId + 'upload-error').removeClass('d-none').addClass('d-block');
                    jQuery('#' + imgId + 'upload-error').html(files.message);
                }
            });

            //a file was added in the queue
            //totalImg = geodir_plupload_params.totalImg;
            //limitImg = geodir_plupload_params.image_limit;
            uploader.bind('FilesAdded', function(up, files) {
                var totalImg = parseInt(jQuery("#" + imgId + "totImg").val());
                var limitImg = parseInt(jQuery("#" + imgId + "image_limit").val());
                jQuery('#' + imgId + 'upload-error').html('');
                jQuery('#' + imgId + 'upload-error').removeClass('d-block').addClass('d-none');

                if (limitImg && $this.hasClass("plupload-upload-uic-multiple") && limitImg > 0) {
                    if (totalImg >= limitImg && limitImg > 0) {
                        while (up.files.length > 0) {
                            up.removeFile(up.files[0]);
                        } // remove images

                        if (typeof geodir_params.err_file_upload_limit != 'undefined' && geodir_params.err_file_upload_limit != '') {
                            msgErr = geodir_params.err_file_upload_limit;
                        } else {
                            msgErr = 'You have reached your upload limit of %s files.';
                        }
                        msgErr = msgErr.replace("%s", limitImg);

                        jQuery('#' + imgId + 'upload-error').removeClass('d-none').addClass('d-block');

                        jQuery('#' + imgId + 'upload-error').html(msgErr);
                        return false;
                    }

                    if (up.files.length > limitImg && limitImg > 0) {
                        while (up.files.length > 0) {
                            up.removeFile(up.files[0]);
                        } // remove images

                        if (typeof geodir_params.err_pkg_upload_limit != 'undefined' && geodir_params.err_pkg_upload_limit != '') {
                            msgErr = geodir_params.err_pkg_upload_limit;
                        } else {
                            msgErr = 'You may only upload %s files with this package, please try again.';
                        }
                        msgErr = msgErr.replace("%s", limitImg);

                        jQuery('#' + imgId + 'upload-error').removeClass('d-none').addClass('d-block');
                        jQuery('#' + imgId + 'upload-error').html(msgErr);
                        return false;
                    }
                }

                $.each(files, function(i, file) {
                    $this.find('.filelist').append('<div class="file" id="' + file.id + '"><b>' + file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' + '<div class="progress"><div class="fileprogress progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div></div>');
                });

                up.refresh();
                up.start();
            });

            uploader.bind('UploadProgress', function(up, file) {
                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });

            var timer;
            var i = 0;
            var indexes = new Array();
            uploader.bind('FileUploaded', function(up, file, response) {

                // stop the animated bar
                $('#' + file.id + ' .fileprogress').removeClass('progress-bar-animated');
                //up.removeFile(up.files[0]); // remove images
                var totalImg = parseInt(jQuery("#" + imgId + "totImg").val());
                indexes[i] = up;
                clearInterval(timer);
                timer = setTimeout(function() {
                    //geodir_remove_file_index(indexes);
                }, 1000);
                i++;
                $('#' + file.id).fadeOut();
                response = response["response"];
                // add url to the hidden field
                if ($this.hasClass("plupload-upload-uic-multiple")) {
                    totalImg++;
                    jQuery("#" + imgId + "totImg").val(totalImg);
                    // multiple
                    var v1 = $.trim($("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val());
                    if (v1) {
                        v1 = v1 + "::" + response;
                    } else {
                        v1 = response;
                    }
                    $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val(v1).trigger('change');
                    //console.log(v1);
                } else {
                    // single
                    $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val(response + "").trigger('change');
                    //console.log(response);
                }
                // show thumbs
                plu_show_thumbs(imgId);
            });
        });
    }
});

function geodir_esc_entities(str){
    // Check & decode entities.
    str = geodir_decode_entities(str);

    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
      };

    return String(str).replace(/[&<>"'`=\/]/g, function(s) {
        return entityMap[s];
    });
}

function geodir_decode_entities(str){
    if (!str) {
        return str;
    }
    var entityMap = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        "&#39;": "'",
        '&#x2F;': '/',
        '&#x60;': '`',
        '&#x3D;': '='
      };

    for (k in entityMap) {
        var pat = new RegExp(k, 'g');
        str = str.replace(pat, entityMap[k]);
    }

    return str;
}

function geodir_remove_file_index(indexes) {
    for (var i = 0; i < indexes.length; i++) {
        if (indexes[i].files.length > 0) {
            indexes[i].removeFile(indexes[i].files[0]);
        }
    }
}

function plu_show_thumbs(imgId) {
    //console.log("plu_show_thumbs");
    var totalImg = parseInt(jQuery("#" + imgId + "totImg").val());
    var limitImg = parseInt(jQuery("#" + imgId + "image_limit").val());
    var $ = jQuery;
    var thumbsC = $("#" + imgId + "plupload-thumbs");
    thumbsC.html("");
    // get urls
    var imagesS = $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val();

    var txtRemove = 'Remove';
    if (typeof geodir_params.action_remove != 'undefined' && geodir_params.action_remove != '') {
        txtRemove = geodir_params.action_remove;
    }

    if (!imagesS) { return; }

    var images = imagesS.split("::");

    for (var i = 0; i < images.length; i++) {
        if (images[i] && images[i] != 'null') {

            var img_arr = images[i].split("|");
            var image_url = img_arr[0];
            var image_id = img_arr[1];
            var image_title = img_arr[2];
            var image_caption = img_arr[3];
            var image_title_html = '';
            var image_caption_html = '';

            // fix undefined id
            if (typeof image_id === "undefined") {
                image_id = '';
            }
            // fix undefined title
            if (typeof image_title === "undefined") {
                image_title = '';
            }
            // fix undefined title
            if (typeof image_caption === "undefined") {
                image_caption = '';
            }

            //Esc title and caption
            image_title   = geodir_esc_entities(image_title);
            image_caption = geodir_esc_entities(image_caption);

            var file_ext = image_url.substring(image_url.lastIndexOf('.') + 1);

            file_ext = file_ext.split('?').shift(); // in case the image url has params
			if (file_ext) {
				file_ext = file_ext.toLowerCase();
			}
            var fileNameIndex = image_url.lastIndexOf("/") + 1;
            var dotIndex = image_url.lastIndexOf('.');
            if (dotIndex < fileNameIndex) {
                continue;
            }
            var file_name = image_url.substr(fileNameIndex, dotIndex < fileNameIndex ? loc.length : dotIndex);

            var file_display = '';
            var file_display_class = '';
            if (file_ext == 'jpg' || file_ext == 'jpe' || file_ext == 'jpeg' || file_ext == 'png' || file_ext == 'gif' || file_ext == 'bmp' || file_ext == 'ico' || file_ext == 'webp' || file_ext == 'avif' || file_ext == 'svg') {
                file_display = '<img class="gd-file-info embed-responsive-item embed-item-cover-xy " data-id="' + image_id + '" data-title="' + image_title + '" data-caption="' + image_caption + '" data-src="' + image_url + '" src="' + image_url + '" alt=""  />';
                if(!!image_title.trim()){
                    image_title_html = '<span class="gd-title-preview badge badge-light ab-top-left text-truncate mw-100 h-auto text-dark w-auto" style="background: #ffffffc7">' + image_title + '</span>';
                }
                if(!!image_caption.trim()){
                    image_caption_html = '<span class="gd-caption-preview badge badge-light ab-top-left mt-4 text-truncate mw-100 h-auto text-dark w-auto" style="background: #ffffffc7">' + image_caption + '</span>';
                }
            } else {
                var file_type_class = 'fa-file';
                if (file_ext == 'pdf') {
                    file_type_class = 'fa-file-pdf';
                } else if (file_ext == 'zip' || file_ext == 'tar') {
                    file_type_class = 'fa-file-archive';
                } else if (file_ext == 'doc' || file_ext == 'odt') {
                    file_type_class = 'fa-file-word';
                } else if (file_ext == 'txt' || file_ext == 'text') {
                    file_type_class = 'fa-file';
                } else if (file_ext == 'csv' || file_ext == 'ods' || file_ext == 'ots') {
                    file_type_class = 'fa-file-excel';
                } else if (file_ext == 'avi' || file_ext == 'mp4' || file_ext == 'mov') {
                    file_type_class = 'fa-file-video';
                }
                file_display_class = 'file-thumb';
                file_display = '<i title="' + file_name + '" class="fas ' + file_type_class + ' gd-file-info embed-responsive-item embed-item-cover-xy display-1" data-id="' + image_id + '" data-title="' + image_title + '" data-caption="' + image_caption + '" data-src="' + image_url + '" aria-hidden="true"></i>';
            }

            var thumb = $('<div class="col px-2 mb-2"><div class="thumb ' + file_display_class + ' ratio ratio-16x9 embed-responsive embed-responsive-16by9 bg-white border c-move" id="thumb' + imgId + i + '">' +
                image_title_html +
                file_display +
                image_caption_html +
                '<div class="gd-thumb-actions position-absolute text-white w-100  d-flex justify-content-around" style="bottom: 0;background: #00000063;top: auto; height:20px;">' +
                '<a class="thumbpreviewlink text-white" title="' + geodir_esc_entities( geodir_params.txt_preview ) + '" id="thumbpreviewlink' + imgId + i + '" href="' + image_url + '" target="_blank"><i class="far fa-eye" aria-hidden="true"></i></a> ' +
                '<span class="thumbeditlink c-pointer" title="' + geodir_esc_entities( geodir_params.txt_edit ) + '" onclick="gd_edit_image_meta(\'' + imgId + '\',' + i + ');"><i class="far fa-edit" aria-hidden="true"></i></span>' +
                '<span class="thumbremovelink c-pointer" title="' + geodir_esc_entities( geodir_params.txt_delete ) + '" id="thumbremovelink' + imgId + i + '"><i class="fas fa-trash-alt" aria-hidden="true"></i></span>' +
                '</div>' +
                '</div></div>');

            thumbsC.append(thumb);

            thumb.find(".thumbremovelink").on("click", function() {
                //console.log("plu_show_thumbs-thumbremovelink");
                if (jQuery('#' + imgId + 'plupload-upload-ui').hasClass("plupload-upload-uic-multiple")) {
                    totalImg--; // remove image from total
                    jQuery("#" + imgId + "totImg").val(totalImg);
                }
                jQuery('#' + imgId + 'upload-error').html('');
                jQuery('#' + imgId + 'upload-error').removeClass('d-block').addClass('d-none');
                var ki = $(this).attr("id").replace("thumbremovelink" + imgId, "");
                ki = parseInt(ki);
                var kimages = [];
                imagesS = $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val();
                images = imagesS.split("::");
                for (var j = 0; j < images.length; j++) {
                    if (j != ki) {
                        kimages[kimages.length] = images[j];
                    }
                }
                $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val(kimages.join("::")).trigger('change');
                //console.log("plu_show_thumbs-thumbremovelink-run");
                plu_show_thumbs(imgId);
                return false;
            });

            // Delete images if limit exceeds
            if (limitImg > 0 && !(limitImg > i)) {
                thumb.find(".thumbremovelink").trigger('click');
            }
        }
    }

    if (images.length > 1) {
        //console.log("plu_show_thumbs-sortable");
        thumbsC.sortable({
            update: function(event, ui) {
                var kimages = [];
                thumbsC.find(".gd-file-info").each(function() {
                    kimages[kimages.length] = $(this).data("src") + "|" + $(this).data("id") + "|" + $(this).data("title") + "|" + $(this).data("caption");
                    $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val(kimages.join("::")).trigger('change');
                    plu_show_thumbs(imgId);
                    //console.log("plu_show_thumbs-sortable-run");
                });
            }
        });
        thumbsC.disableSelection();
    }

    // we need to run the basics here.
    //console.log("run basics");

    var kimages = [];
    thumbsC.find(".gd-file-info").each(function() {
        kimages[kimages.length] = $(this).data("src") + "|" + $(this).data("id") + "|" + $(this).data("title") + "|" + $(this).data("caption");
        $("#" + imgId, $('#' + imgId + 'plupload-upload-ui').parent()).val(kimages.join("::")).trigger('change');
    });
}

function gd_edit_image_meta(inputId, orderId) {
    var imagesS = jQuery("#" + inputId, jQuery('#' + inputId + 'plupload-upload-ui').parent()).val(), images = imagesS.split("::"), img_arr = images[orderId].split("|"), image_title = geodir_esc_entities(img_arr[2]), image_caption = geodir_esc_entities(img_arr[3]), html = '', hBtn = '';

    html = html + "<div class='form-group mb-3'><label for='gd-image-meta-title' class='text-left text-start form-label'>" + geodir_params.label_title + "</label><input id='gd-image-meta-title' value='" + image_title + "' class='form-control'></div>"; // title value
    html = html + "<div class='form-group mb-3'><label for='gd-image-meta-caption' class='text-left text-start form-label'>" + geodir_params.label_caption + "</label><input id='gd-image-meta-caption' value='" + image_caption + "' class='form-control'></div>"; // caption value
    hBtn = "<span class='btn btn-primary c-pointer' onclick='gd_set_image_meta(\"" + inputId + "\"," + orderId + ")'>" + geodir_params.button_set + "</span>"; // set button
    jQuery('#gd_image_meta_' + inputId + ' .modal-body').html(html);
    jQuery('#gd_image_meta_' + inputId + ' .modal-footer').html(hBtn);
    jQuery('#gd_image_meta_' + inputId).modal('show');
}

function gd_set_image_meta(inputId, orderId) {
    var imagesS = jQuery("#" + inputId, jQuery('#' + inputId + 'plupload-upload-ui').parent()).val(), images = imagesS.split("::"), img_arr = images[orderId].split("|"), image_url = img_arr[0], image_id = img_arr[1], image_title = geodir_esc_entities(jQuery('#gd_image_meta_' + inputId + ' #gd-image-meta-title').val()), image_caption = geodir_esc_entities(jQuery('#gd_image_meta_' + inputId + ' #gd-image-meta-caption').val());
    images[orderId] = image_url + "|" + image_id + "|" + image_title + "|" + image_caption;
    imagesS = images.join("::");
    jQuery("#" + inputId, jQuery('#' + inputId + 'plupload-upload-ui').parent()).val(imagesS).trigger('change');
    plu_show_thumbs(inputId);
    jQuery('#gd_image_meta_' + inputId).modal('hide');
}

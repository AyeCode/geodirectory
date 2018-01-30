jQuery.fn.exists = function () {
    return jQuery(this).length > 0;
}

jQuery(document).ready(function ($) {
    if ($(".plupload-upload-uic").exists()) {
        var pconfig = false;
        var msgErr = '';
        var post_id = '';
        // set the post id
        if(jQuery("#geodirectory-add-post input[name='ID']").length){
            var post_id = jQuery("#geodirectory-add-post input[name='ID']").val(); // frontend
        }else{
            post_id = jQuery("#post input[name='post_ID']").val(); // backend
        }

        $(".plupload-upload-uic").each(function () {
            var $this = $(this);
            var id1 = $this.attr("id");
            var imgId = id1.replace("plupload-upload-ui", "");

            plu_show_thumbs(imgId);

            pconfig = JSON.parse(geodir_plupload_params.base_plupload_config);
            pconfig["browse_button"] = imgId + pconfig["browse_button"];
            pconfig["container"] = imgId + pconfig["container"];
            if(pconfig["drop_element"]){ pconfig["drop_element"] = imgId + pconfig["drop_element"];} // only add drop area if there is one
            pconfig["file_data_name"] = imgId + pconfig["file_data_name"];
            pconfig["multipart_params"]["imgid"] = imgId;
            pconfig["multipart_params"]["post_id"] = post_id;
            //pconfig["multipart_params"]["_ajax_nonce"] = $this.find(".ajaxnonceplu").attr("id").replace("ajaxnonceplu", "");

            if ($this.hasClass("plupload-upload-uic-multiple")) {
                pconfig["multi_selection"] = true;
            }

            // resizing in JS can actually cause a small image to grow in size and can lower quality of image even if set to 100%
            /* if ($this.find(".plupload-resize").exists()) {
                var w = parseInt($this.find(".plupload-width").attr("id").replace("plupload-width", ""));
                var h = parseInt($this.find(".plupload-height").attr("id").replace("plupload-height", ""));
                pconfig["resize"] = {
                    width: w,
                    height: h,
                    quality: 90
                };
            } */
			var allowed_exts = jQuery('#' + imgId + '_allowed_types').val();
			allowed_exts = allowed_exts && allowed_exts != '' ? allowed_exts : '';
			if (imgId == 'post_images' && typeof geodir_params.gd_allowed_img_types != 'undefined' && geodir_params.gd_allowed_img_types != '') {
				allowed_exts = geodir_params.gd_allowed_img_types;
			}

			if (allowed_exts && allowed_exts != '') {
				var txt_all_files = (typeof geodir_params.txt_all_files != 'undefined' && geodir_params.txt_all_files != '') ? geodir_params.txt_all_files : 'Allowed files'; 
				pconfig['filters'] = [{'title':txt_all_files, 'extensions':allowed_exts}];
			}
			
            var uploader = new plupload.Uploader(pconfig);
            uploader.bind('Init', function (up) {
                //alert(1);
            });
            uploader.init();
            uploader.bind('Error', function (up, files) {
                if (files.code == -600) {
                    jQuery('#' + imgId + 'upload-error').addClass('upload-error');

                    if (typeof geodir_params.err_max_file_size != 'undefined' && geodir_params.err_max_file_size != '') {
                        msgErr = geodir_params.err_max_file_size;
                    } else {
                        msgErr = 'File size error : You tried to upload a file over %s';
                    }
                    msgErr = msgErr.replace("%s", geodir_plupload_params.upload_img_size);

                    jQuery('#' + imgId + 'upload-error').html(msgErr);
                } else if (files.code == -601) {
                    jQuery('#' + imgId + 'upload-error').addClass('upload-error');

                    if (typeof geodir_params.err_file_type != 'undefined' && geodir_params.err_file_type != '') {
                        msgErr = geodir_params.err_file_type;
                    } else {
                        msgErr = 'File type error. Allowed file types: %s';
                    }
					if(imgId == 'post_images') {
						var txtReplace = allowed_exts != '' ? "." + allowed_exts.replace(/,/g, ", .") : '*';
						msgErr = msgErr.replace("%s", txtReplace);
					} else {
						msgErr = msgErr.replace("%s", jQuery("#" + imgId + "_allowed_types").attr('data-exts'));
					}

                    jQuery('#' + imgId + 'upload-error').html(msgErr);
                } else {
                    jQuery('#' + imgId + 'upload-error').addClass('upload-error');
                    jQuery('#' + imgId + 'upload-error').html(files.message);
                }
            });
            totalImg = jQuery("#" + imgId + "totImg").val();
            limitImg = jQuery("#" + imgId + "image_limit").val();

            //a file was added in the queue
            //totalImg = geodir_plupload_params.totalImg;
            //limitImg = geodir_plupload_params.image_limit;
            uploader.bind('FilesAdded', function (up, files) {
                jQuery('#' + imgId + 'upload-error').html('');
                jQuery('#' + imgId + 'upload-error').removeClass('upload-error');

                if (limitImg && $this.hasClass("plupload-upload-uic-multiple") && jQuery("#" + imgId + "image_limit").val()) {
                    if (totalImg == limitImg && parseInt(limitImg) > 0) {
                        while (up.files.length > 0) {
                            up.removeFile(up.files[0]);
                        } // remove images

                        if (typeof geodir_params.err_file_upload_limit != 'undefined' && geodir_params.err_file_upload_limit != '') {
                            msgErr = geodir_params.err_file_upload_limit;
                        } else {
                            msgErr = 'You have reached your upload limit of %s files.';
                        }
                        msgErr = msgErr.replace("%s", limitImg);

                        jQuery('#' + imgId + 'upload-error').addClass('upload-error');

                        jQuery('#' + imgId + 'upload-error').html(msgErr);
                        return false;
                    }

                    if (up.files.length > limitImg && parseInt(limitImg) > 0) {
                        while (up.files.length > 0) {
                            up.removeFile(up.files[0]);
                        } // remove images

                        if (typeof geodir_params.err_pkg_upload_limit != 'undefined' && geodir_params.err_pkg_upload_limit != '') {
                            msgErr = geodir_params.err_pkg_upload_limit;
                        } else {
                            msgErr = 'You may only upload %s files with this package, please try again.';
                        }
                        msgErr = msgErr.replace("%s", limitImg);

                        jQuery('#' + imgId + 'upload-error').addClass('upload-error');
                        jQuery('#' + imgId + 'upload-error').html(msgErr);
                        return false;
                    }

                    /*if((parseInt(up.files.length)+parseInt(totalImg)>parseInt(limitImg)) && parseInt(limitImg) > 0){
                     while(up.files.length > 0) {up.removeFile(up.files[0]);} // remove images
                     jQuery('#'+imgId+'upload-error').addClass('upload-error');
                     jQuery('#'+imgId+'upload-error').html('You may only upload another '+(parseInt(limitImg)-parseInt(totalImg))+' with this package, please try again.');
                     return false;
                     }*/
                }

                $.each(files, function (i, file) {
                    $this.find('.filelist').append('<div class="file" id="' + file.id + '"><b>' + file.name + '</b> (<span>' + plupload.formatSize(0) + '</span>/' + plupload.formatSize(file.size) + ') ' + '<div class="fileprogress"></div></div>');
                });

                up.refresh();
                up.start();
            });

            uploader.bind('UploadProgress', function (up, file) {
                $('#' + file.id + " .fileprogress").width(file.percent + "%");
                $('#' + file.id + " span").html(plupload.formatSize(parseInt(file.size * file.percent / 100)));
            });

            var timer;
            var i = 0;
            var indexes = new Array();
            uploader.bind('FileUploaded', function (up, file, response) {
                //totalImg++;
                //up.removeFile(up.files[0]); // remove images
                indexes[i] = up;
                clearInterval(timer);
                timer = setTimeout(function () {
                    //geodir_remove_file_index(indexes);
                }, 1000);
                i++;
                $('#' + file.id).fadeOut();
                response = response["response"];
                // add url to the hidden field
                if ($this.hasClass("plupload-upload-uic-multiple")) {
                    totalImg++;
                    // multiple
                    var v1 = $.trim($("#" + imgId).val());
                    if (v1) {
                        v1 = v1 + "," + response;
                    } else {
                        v1 = response;
                    }
                    $("#" + imgId).val(v1);
                    console.log(v1);
                } else {
                    // single
                    $("#" + imgId).val(response + "");
                    console.log(response);
                }
                // show thumbs
                plu_show_thumbs(imgId);
            });
        });
    }
});

function geodir_remove_file_index(indexes) {
    for (var i = 0; i < indexes.length; i++) {
        if (indexes[i].files.length > 0) {
            indexes[i].removeFile(indexes[i].files[0]);
        }
    }
}

function plu_show_thumbs(imgId) {
    console.log("plu_show_thumbs");
    var $ = jQuery;
    var thumbsC = $("#" + imgId + "plupload-thumbs");
    thumbsC.html("");
    // get urls
    var imagesS = $("#" + imgId).val();

    var txtRemove = 'Remove';
    if (typeof geodir_params.action_remove != 'undefined' && geodir_params.action_remove != '') {
        txtRemove = geodir_params.action_remove;
    }

    var images = imagesS.split(",");

    for (var i = 0; i < images.length; i++) {
        if (images[i] && images[i] != 'null') {

            var img_arr = images[i].split("|");
            var image_url = img_arr[0];
            var image_id = img_arr[1];
            var image_title = img_arr[2];
            var image_caption = img_arr[3];
            //console.log(img_arr);

            // fix undefined id
            if(typeof image_id === "undefined"){
                image_id = '';
            }
            // fix undefined title
            if(typeof image_title === "undefined"){
                image_title = '';
            }
            // fix undefined title
            if(typeof image_caption === "undefined"){
                image_caption = '';
            }

            var file_ext = image_url.substring(images[i].lastIndexOf('.') + 1);

            file_ext = file_ext.split('?').shift();// in case the image url has params
            var fileNameIndex = image_url.lastIndexOf("/") + 1;
            var dotIndex = image_url.lastIndexOf('.');
            if(dotIndex < fileNameIndex){continue;}
            var file_name = image_url.substr(fileNameIndex, dotIndex < fileNameIndex ? loc.length : dotIndex);

            /*if (file_ext == 'pdf' || file_ext == 'xlsx' || file_ext == 'xls' || file_ext == 'csv' || file_ext == 'docx' || file_ext == 'doc' || file_ext == 'txt') {
                file_name = file_name.split(imgId + '_');
                var thumb = $('<div class="thumb geodir_file" id="thumb' + imgId + i + '"><div class="thumbi"><a id="thumbremovelink' + imgId + i + '" href="#">' + txtRemove + '</a></div><a target="_blank" href="' + images[i] + '">' + file_name[file_name.length - 1] + '</a></div>');
            } else {
                var thumb = $('<div class="thumb" id="thumb' + imgId + i + '"><div class="thumbi"><a id="thumbremovelink' + imgId + i + '" href="#">' + txtRemove + '</a></div><img src="' + images[i] + '" alt=""  /></div>');
            }*/
			if (file_ext == 'jpg' || file_ext == 'jpe' || file_ext == 'jpeg' || file_ext == 'png' || file_ext == 'gif' || file_ext == 'bmp' || file_ext == 'ico') {
                var thumb = $('<div class="thumb" id="thumb' + imgId + i + '">' +
                    '<img data-id="'+image_id+'" data-title="'+image_title+'" data-caption="'+image_caption+'" src="' + image_url + '" alt=""  />' +
                    '<div class="gd-thumb-actions">'+
                    '<span class="thumbeditlink" onclick="gd_edit_image_meta('+imgId+','+i+');"><i class="fa fa-pencil-square-o" aria-hidden="true"></i></span>' +
                    '<span class="thumbremovelink" id="thumbremovelink' + imgId + i + '"><i class="fa fa-trash-o" aria-hidden="true"></i></span>' +
                    '</div>'+
                    '</div>');
            } else {
                file_name = file_name.split(imgId + '_');
                var thumb = $('<div class="thumb geodir_file" id="thumb' + imgId + i + '"><div class="thumbi"><a id="thumbremovelink' + imgId + i + '" href="#">' + txtRemove + '</a></div><a target="_blank" href="' +image_url + '">' + file_name[file_name.length - 1] + '</a></div>');
            }

            thumbsC.append(thumb);

            thumb.find(".thumbremovelink").click(function () {

                console.log("plu_show_thumbs-thumbremovelink");

                if (jQuery('#' + imgId + 'plupload-upload-ui').hasClass("plupload-upload-uic-multiple")) totalImg--; // remove image from total
                jQuery('#' + imgId + 'upload-error').html('');
                jQuery('#' + imgId + 'upload-error').removeClass('upload-error');
                var ki = $(this).attr("id").replace("thumbremovelink" + imgId, "");
                ki = parseInt(ki);
                var kimages = [];
                imagesS = $("#" + imgId).val();
                images = imagesS.split(",");
                for (var j = 0; j < images.length; j++) {
                    if (j != ki) {
                        kimages[kimages.length] = images[j];
                    }
                }
                $("#" + imgId).val(kimages.join());
                console.log("plu_show_thumbs-thumbremovelink-run");
                plu_show_thumbs(imgId);
                return false;
            });
        }
    }

    if (images.length > 1) {console.log("plu_show_thumbs-sortable");
        thumbsC.sortable({
            update: function (event, ui) {
                var kimages = [];
                thumbsC.find("img").each(function () {
                    kimages[kimages.length] = $(this).attr("src")+"|"+$(this).data("id")+"|"+$(this).data("title")+"|"+$(this).data("caption");
                    $("#" + imgId).val(kimages.join());
                    plu_show_thumbs(imgId);
                    console.log("plu_show_thumbs-sortable-run");
                });
            }
        });
        thumbsC.disableSelection();
    }


    // we need to run the basics here.
    console.log("run basics");

    var kimages = [];
    thumbsC.find("img").each(function () {
        kimages[kimages.length] = $(this).attr("src")+"|"+$(this).data("id")+"|"+$(this).data("title")+"|"+$(this).data("caption");
        $("#" + imgId).val(kimages.join());
        //plu_show_thumbs(imgId);
        console.log("run basics-run");
    });
}


/*
@todo this needs UI bad.
 */
function gd_edit_image_meta(input,order_id){
//alert(order_id);

    var imagesS = jQuery("#" + input.id).val();
console.log(imagesS);
    var images = imagesS.split(",");
    var img_arr = images[order_id].split("|");
    console.log(img_arr);
    var image_url = img_arr[0];
    var image_id = img_arr[1];
    var image_title = img_arr[2];
    var image_caption = img_arr[3];
    var html = '';
// <div class="txt-fld">
//         <label for="">Username</label>
//         <input id="" class="good_input" name="" type="text">
//
//     </div>
    html  = html + "<div class='gd-modal-text'><label for=''>Title</label><input id='gd-image-meta-title' value='"+image_title+"'></div>"; // title value
    html  = html + "<div class='gd-modal-text'><label for=''>Caption</label><input id='gd-image-meta-caption' value='"+image_caption+"'></div>"; // caption value
    // html  = html + "<input id='gd-image-meta-title' value='"+image_title+"'>"; // title value
    // html  = html + "<input id='gd-image-meta-caption' value='"+image_caption+"'>"; // caption value
    html  = html + "<div class='gd-modal-button'><button class='button button-primary button-large' onclick='gd_set_image_meta(\""+input.id+"\","+order_id+")'>Set</button></div>"; // caption value
    jQuery('#gd-image-meta-input').html(html);
    tb_show("Edit image meta", "#TB_inline?height=300&amp;width=400&amp;inlineId=gd-image-meta-input"); //@todo title needs to be translatable

}

function gd_set_image_meta(input_id,order_id){
//alert(order_id);
    var imagesS = jQuery("#" + input_id).val();
    var images = imagesS.split(",");
    var img_arr = images[order_id].split("|");
    var image_url = img_arr[0];
    var image_id = img_arr[1];
    var image_title = jQuery('#gd-image-meta-title').val();
    var image_caption = jQuery('#gd-image-meta-caption').val();
    images[order_id] = image_url+"|"+image_id+"|"+image_title+"|"+image_caption;
    imagesS = images.join(",");
    jQuery("#" + input_id).val(imagesS);
    plu_show_thumbs(input_id);
    tb_remove();
}
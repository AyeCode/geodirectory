window.gdJSMap = true;
var is_zooming = false;
var zoom_listener = '';
var gd_current_query = '';
var gd_map_first_load = true;
var gd_fullscreen_parent = '';
var gd_toggle_bsui = true;

function initMap(map_options) {
    // Set if the bsui class on html should be toggled
    if (jQuery('html').hasClass('bsui')) {
        gd_toggle_bsui = false;
    }

    if (window.gdMaps == 'osm') {
        initMapOSM(map_options);
        return;
    }
    // alert(map_options)
    map_options = eval(map_options);
    map_options.zoom = parseInt(map_options.zoom);
    var options = map_options;
    var pscaleFactor;
    var pstartmin;
    var ajax_url = options.ajax_url;
    var token = options.token;
    var search_string = options.token;
    var mm = 0; // marker array
    var maptype = options.maptype;
    var zoom = options.zoom;
    var latitude = options.latitude;
    var longitude = options.longitude;
    var maxZoom = options.maxZoom;
    var etype = options.etype;
    var autozoom = options.autozoom;
    var scrollwheel = options.scrollwheel;
    var fullscreenControl = options.fullscreenControl;
    var streetview = options.streetViewControl;
    var bubble_size = options.bubble_size;
    var map_canvas = options.map_canvas;
    var enable_map_direction = options.enable_map_direction;
    var enable_cat_filters = options.enable_cat_filters;
    var marker_cluster = options.marker_cluster;
    options.token = '68f48005e256696074e1da9bf9f67f06';
    options.navigationControlOptions = {
        position: 'TOP_LEFT',
        style: 'ZOOM_PAN'
    };

    // Create map
    jQuery("#" + map_canvas).goMap(options);
    // set max zoom
    var styles = [{
        featureType: "poi.business",
        elementType: "labels",
        stylers: [{
            visibility: "off"
        }]
    }];
    if (!(typeof geodir_custom_map_style === 'undefined'))
        styles = geodir_custom_map_style;
    /* custom google map style */
    if (typeof options.mapStyles != 'undefined') {
        try {
            var mapStyles = JSON.parse(options.mapStyles);
            if (typeof mapStyles == 'object' && mapStyles) {
                styles = mapStyles;
            }
        } catch (err) {
            console.log(err.message);
        }
    }
    /* custom google map style */
    jQuery.goMap.map.setOptions({
        styles: styles
    });

    google.maps.event.addListenerOnce(jQuery.goMap.map, 'idle', function() {
        jQuery("#" + map_canvas).goMap();
        for (var i in google.maps.MapTypeId) {
            jQuery.goMap.map.mapTypes[google.maps.MapTypeId[i]].maxZoom = options.maxZoom;
        }
    });
    google.maps.event.addListener(jQuery.goMap.map, 'idle', function() {
        //jQuery("#" + map_canvas).goMap();
        //jQuery.goMap.map.removeListener('bounds_changed');
        if (eval(map_canvas).marker_cluster_server) {
            if (gd_map_first_load) {
                gd_map_first_load = false;
                eval(map_canvas).enable_marker_cluster_no_reposition = true;
            } // first load do nothing
            else {
                if (is_zooming) {} else {
                    is_zooming = true;
                    build_map_ajax_search_param(map_canvas, false, false, true);
                    is_zooming = false;
                }
            }
        }
    });
    var maxMap = document.getElementById(map_canvas + '_triggermap');
    if (!jQuery(maxMap).hasClass('gd-triggered-map')) { // skip multiple click listener after reload map via ajax
        jQuery(maxMap).addClass('gd-triggered-map');
        maxMap.addEventListener('click', gdMaxMap);
    }

    function gdMaxMap() {

        if (jQuery('body').hasClass('body_fullscreen')) {
            jQuery('#catcher_' + map_canvas + '').removeClass('position-fixed');
            jQuery(window).scroll();
            jQuery('#placeholder_' + map_canvas).after(jQuery(gd_fullscreen_parent));
            jQuery('#placeholder_' + map_canvas).remove();
        } else {
            jQuery('#catcher_' + map_canvas + '').addClass('position-fixed');
            jQuery(window).scroll();
            gd_fullscreen_parent = jQuery('#' + map_canvas).parents(".stick_trigger_container"); //.parent();
            jQuery(gd_fullscreen_parent).before('<div id="placeholder_' + map_canvas + '"></div>');
            jQuery(gd_fullscreen_parent).prependTo("body");
        }

        // new
        if (gd_toggle_bsui) {
            jQuery('html').toggleClass('bsui');
        }
        jQuery('html').attr('style', function(index, attr) {
            return attr == 'margin-top:0 !important;' ? '' : 'margin-top:0 !important;';
        });
        jQuery('body').toggleClass('body_fullscreen overflow-hidden');
        jQuery('#' + map_canvas + ', #sticky_map_' + map_canvas + ',#' + map_canvas + '_wrapper').toggleClass('vw-100 vh-100');
        jQuery('#' + map_canvas + '_triggermap i, .geodir-map-directions-wrap, #wpadminbar').toggleClass('d-none');

        window.setTimeout(function() {
            var center = jQuery.goMap.map.getCenter();
            jQuery("#" + map_canvas).goMap();
            google.maps.event.trigger(jQuery.goMap.map, 'resize');
            jQuery.goMap.map.setCenter(center);
            setGeodirMapSize(true);
        }, 100);
    }

    // Overlapping Marker Spiderfier
    window.oms = jQuery.goMap.oms;
}

function geodir_build_static_map(map_canvas) {
    var width, height, width_raw, height_raw, wrapWi, wrapHe, maptype;

    if (window.gdMaps != 'google') {
        build_map_ajax_search_param(map_canvas, false);
        return;
    }
    options = eval(map_canvas);

    // Width
    width_raw = options.width ? options.width : 0;
    wrapWi = jQuery("#" + map_canvas).width();

    if (wrapWi < 10) {
        wrapWi = jQuery("#" + map_canvas).closest('.geodir-map-wrap').width();
    }
    if (width_raw.indexOf('%') !== -1) {
        width = parseInt(parseInt(width_raw) * wrapWi / 100);
    } else {
        width = parseInt(width_raw.replace(/\D/g, ''));
    }
    if (width < 10) {
        width = parseInt(wrapWi);
    }

    // Height
    height_raw = options.height ? options.height : 0;
    wrapHe = jQuery("#" + map_canvas).height();
    if (wrapHe < 10) {
        wrapHe = jQuery("#" + map_canvas).closest('.geodir-map-wrap').height();
    }
    if (height_raw.indexOf('%') !== -1) {
        height = parseInt(parseInt(height_raw) * wrapHe / 100);
    } else {
        height = parseInt(height_raw.replace(/\D/g, ''));
    }
    if (height < 10) {
        height = parseInt(wrapHe);
    }

    maptype = options.maptype ? (options.maptype).toLowerCase() : 'roadmap';

    var img_url = "https://maps.googleapis.com/maps/api/staticmap?" + "size=" + width + "x" + height + "&maptype=" + maptype + "&language=" + geodir_params.mapLanguage + "&zoom=" + options.zoom + "&center=" + options.latitude + "," + options.longitude + "&markers=icon:" + options.icon_url + "|" + options.latitude + "," + options.longitude + "&key=" + geodir_params.google_api_key;

    var img = "<img class='geodir-static-map-image' src='" + img_url + "' onclick='build_map_ajax_search_param(\"" + map_canvas + "\",false);' />";

    jQuery("#" + map_canvas).html(img);
    jQuery("." + map_canvas + "_TopLeft").hide();
    jQuery('#' + map_canvas + '_loading_div').hide();
}

function geodir_no_map_api(map_canvas) {
    jQuery('#' + map_canvas + '_loading_div').hide();
    jQuery('#' + map_canvas + '_map_notloaded').show();
    jQuery('#sticky_map_' + map_canvas).find('.map-category-listing-main').hide();
    jQuery('#sticky_map_' + map_canvas).find('#' + map_canvas + '_posttype_menu').hide();
    jQuery('#sticky_map_' + map_canvas).find('.' + map_canvas + '_TopLeft').hide();
    jQuery('#sticky_map_' + map_canvas).find('.' + map_canvas + '_TopRight').hide();
}

function build_map_ajax_search_param(map_canvas, reload_cat_list, catObj, hide_loading) {
    if (!window.gdMaps) {
        geodir_no_map_api(map_canvas);
        return false;
    }
    var $container, options, map_type, post_type, query_string = '',
        search, custom_loop;

    $container = jQuery('#sticky_map_' + map_canvas).closest('.stick_trigger_container');
    options = eval(map_canvas);
    map_type = options.map_type;
    post_type = options.post_type;
    post_type_filter = jQuery('#' + map_canvas + '_posttype').val();
    if (post_type_filter) {
        post_type = post_type_filter;
    }

    jQuery("." + map_canvas + "_TopLeft").show(); // static maps hide this so we show it when loading

    // post type
    query_string += 'post_type=' + post_type;
    query_string += '&_wpnonce=' + options._wpnonce;

    // locations
    if (options.country) {
        query_string += "&country=" + options.country;
    }
    if (options.region) {
        query_string += "&region=" + options.region;
    }
    if (options.city) {
        query_string += "&city=" + options.city;
    }
    if (options.neighbourhood) {
        query_string += "&neighbourhood=" + options.neighbourhood;
    }
    if (options.lat) {
        query_string += "&lat=" + options.lat;
    }
    if (options.lon) {
        query_string += "&lon=" + options.lon;
    }
    if (options.dist) {
        query_string += "&dist=" + options.dist;
    }

    if (reload_cat_list) {
        return geodir_map_post_type_terms(options, post_type, query_string);
    }

    // MC
    var map_info = '';
    if (jQuery.goMap.map && options.marker_cluster_server) { // map loaded so we know the bounds
        bounds = jQuery.goMap.map.getBounds();
        gd_zl = jQuery.goMap.map.getZoom();

        if (bounds) {
            if (window.gdMaps == 'osm') {
                gd_lat_ne = bounds.getNorthEast().lat;
                gd_lon_ne = bounds.getNorthEast().lng;
                gd_lat_sw = bounds.getSouthWest().lat;
                gd_lon_sw = bounds.getSouthWest().lng;
            } else {
                gd_lat_ne = bounds.getNorthEast().lat();
                gd_lon_ne = bounds.getNorthEast().lng();
                gd_lat_sw = bounds.getSouthWest().lat();
                gd_lon_sw = bounds.getSouthWest().lng();
            }
            map_info = "&zl=" + gd_zl + "&lat_ne=" + gd_lat_ne + "&lon_ne=" + gd_lon_ne + "&lat_sw=" + gd_lat_sw + "&lon_sw=" + gd_lon_sw;
        }

    } else if (options.marker_cluster_server && !options.autozoom) { // map not loaded and auto zoom not set
        gd_zl = options.zoom;
        gd_map_h = jQuery('#' + map_canvas).height();
        gd_map_w = jQuery('#' + map_canvas).width();
        map_info = "&zl=" + gd_zl + "&gd_map_h=" + gd_map_h + "&gd_map_w=" + gd_map_w;
    } else if (options.marker_cluster_server && options.autozoom) { // map not loaded and auto zoom set
        gd_zl = options.zoom;
        gd_map_h = jQuery('#' + map_canvas).height();
        gd_map_w = jQuery('#' + map_canvas).width();
        map_info = "&zl=" + gd_zl + "&gd_map_h=" + gd_map_h + "&gd_map_w=" + gd_map_w;
    }

    query_string += map_info;
    // /MC

    search = jQuery('#' + map_canvas + '_search_string').val();
    if (!search && options.searchKeyword) {
        search = options.searchKeyword;
    }

    // Terms
    var terms_filters = false;

    jQuery('[name="' + map_canvas + '_cat[]"]:checked').each(function() {
        terms_filters = true;
        if (jQuery(this).val()) {
            query_string += '&term[]=' + jQuery(this).val();
        }
    });

    // Terms
    terms = options.terms;
    if (!terms_filters && terms) {
        if (typeof terms == 'object' || typeof terms == 'array') {} else {
            terms = terms.split(',');
        }
        if (terms.length > 0) {
            query_string += '&term[]=' + terms.join("&term[]=");
        }
    }

    // Tags
    var tags = options.tags;
    if (tags) {
        if (typeof tags == 'object' || typeof tags == 'array') {} else {
            tags = tags.split(',');
        }
        if (tags.length > 0) {
            custom_loop = tags[0] && tags[0].indexOf(".") === 0 || tags[0].indexOf("#") === 0 ? tags[0] : false; // css class or id.
            if (custom_loop && jQuery(custom_loop + ' .geodir-category-list-view').length) {
                // custom loop from listings widget on non gd pages.
                var loopIds = jQuery(custom_loop + ' .geodir-category-list-view')
                    .find(".geodir-post.type-" + post_type) //Find the spans
                    .map(function() {
                        return jQuery(this).data("post-id")
                    }) //Project Ids
                    .get(); //ToArray

                if ((typeof loopIds == 'object' || typeof loopIds == 'array') && loopIds.length > 0) {
                    query_string += '&post[]=' + loopIds.join("&post[]=");
                } else {
                    query_string += '&post[]=-1';
                }
            } else if (custom_loop && jQuery(custom_loop + ' .elementor-posts').length) {
                // Elementor posts loop.
                var loopIds = jQuery(custom_loop + ' .elementor-posts').find(".elementor-post.type-" + post_type)
                    .map(function() {
                        return jQuery(this).attr('id').match(/post-\d+/)[0].replace("post-","");
                    }).get();

                if ((typeof loopIds == 'object' || typeof loopIds == 'array') && loopIds.length > 0) {
                    query_string += '&post[]=' + loopIds.join("&post[]=");
                } else {
                    query_string += '&post[]=-1';
                }
            } else {
                query_string += '&tag[]=' + tags.join("&tag[]=");
            }
        }
    }

    // Posts
    posts = options.posts;
    if (posts) {
        // archive pages
        if (posts == 'geodir-loop-container') {
            var idarray = jQuery(".geodir-loop-container")
                .find(".geodir-post") //Find the spans
                .map(function() {
                    return jQuery(this).data("post-id")
                }) //Project Ids
                .get(); //ToArray

            // check if elementor loop
            if (!idarray.length && jQuery('.elementor-posts-container').length) {
                $containerClass = jQuery('.geodir-loop-container').length ? jQuery(".geodir-loop-container") : (jQuery('.elementor-widget-archive-posts .elementor-posts-container:visible').length ? jQuery('.elementor-widget-archive-posts .elementor-posts-container:visible') : jQuery('.elementor-posts-container'));
                idarray = $containerClass
                    .find(".elementor-post ") //Find the spans
                    .map(function() {
                        return jQuery(this).attr('class').match(/post-\d+/)[0].replace("post-", "");
                    }) //Project Ids
                    .get(); //ToArray
            }

            if (idarray.length) {
                posts = idarray;
            } else {
                posts = '-1';
            }
        }

        if (typeof posts == 'object' || typeof posts == 'array') {} else {
            posts = posts.split(',');
        }
        if (posts.length > 0) {
            query_string += '&post[]=' + posts.join("&post[]=");
        }
    }

    search = search ? search.trim() : '';
    if (search && search != options.inputText) {
        query_string += '&search=' + search;
    }

    map_ajax_search(map_canvas, query_string, '', hide_loading);
}

function geodir_show_sub_cat_collapse_button() {

    setTimeout(function() {
        jQuery('ul.main_list li').each(function(i) {
            var sub_cat_list = jQuery(this).find('ul.sub_list');
            if (!(typeof sub_cat_list.attr('class') === 'undefined')) {

                // insert
                jQuery(sub_cat_list).parent('li').find('> .custom-checkbox label, > .form-check label').after('<span class="gd-map-cat-toggle ml-2 ms-2 c-pointer"><i class="fas fa-caret-down" aria-hidden="true" style="display:none"></i></span>');

                if (sub_cat_list.is(':visible')) {
                    jQuery(this).find('i,svg').removeClass('fa-caret-down');
                    jQuery(this).find('i,svg').addClass('fa-caret-up');
                } else {
                    jQuery(this).find('i,svg').removeClass('fa-caret-up');
                    jQuery(this).find('i,svg').addClass('fa-caret-down');
                }
                jQuery(this).find('i,svg').show();
                /**/
            } else {
                jQuery(this).find('i,svg').hide();
                /**/
            }
        });
        geodir_activate_collapse_pan();
    }, 100);
}

function geodir_activate_collapse_pan() {
    jQuery('ul.main_list').find('.gd-map-cat-toggle').off('click').on("click",function() {
        jQuery(this)
            .parent()
            .parent('li')
            .find('ul.sub_list')
            .toggle(200,
                function() {
                    if (jQuery(this).is(':visible')) {
                        jQuery(this).parent('li').find('i,svg').removeClass('fa-caret-down');
                        jQuery(this).parent('li').find('i,svg').addClass('fa-caret-up');
                    } else {
                        jQuery(this).parent('li').find('i,svg').removeClass('fa-caret-up');
                        jQuery(this).parent('li').find('i,svg').addClass('fa-caret-down');
                    }
                });
    });
}

function map_ajax_search(map_canvas_var, query_string, marker_jason, hide_loading, keep_markers) {
    if (!window.gdMaps) {
        jQuery('#' + map_canvas_var + '_loading_div').hide();
        jQuery('#' + map_canvas_var + '_map_notloaded').show();
        jQuery('#sticky_map_' + map_canvas_var).find('.map-category-listing-main').hide();
        jQuery('#sticky_map_' + map_canvas_var).find('#' + map_canvas_var + '_posttype_menu').hide();
        jQuery('#sticky_map_' + map_canvas_var).find('.' + map_canvas_var + '_TopLeft').hide();
        jQuery('#sticky_map_' + map_canvas_var).find('.' + map_canvas_var + '_TopRight').hide();
        return false;
    }

    if (hide_loading) {} //dont reposition after load
    else {
        jQuery('#' + map_canvas_var + '_loading_div').show();
    }
    if (marker_jason != '') {
        parse_marker_jason(marker_jason, map_canvas_var, keep_markers);
        //document.getElementById( map_canvas+'_loading_div').style.display="none";
        jQuery('#' + map_canvas_var + '_loading_div').hide();
        return;
    }
    var query_url = eval(map_canvas_var).map_markers_ajax_url;
    if (query_string) {
        u = query_url.indexOf('?') === -1 ? '?' : '&';
        query_url += u + query_string;
    }
    if (gd_current_query == map_canvas_var + '-' + query_url) {
        jQuery('#' + map_canvas_var + '_loading_div').hide();
    } //dont run again
    else {
        gd_current_query = map_canvas_var + '-' + query_url;
        jQuery.ajax({
            type: "GET",
            url: query_url,
            dataType: "json",
            success: function(data) {
                jQuery('#' + map_canvas_var + '_loading_div').hide();
                parse_marker_jason(data, map_canvas_var, keep_markers);
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(errorThrown);
            }
        });
    }
    return;
} // End  map_ajax_search

// read the data, create markers
var bounds = '';

function parse_marker_jason(json, map_canvas_var, keep_markers) {
    if (window.gdMaps == 'osm') {
        parse_marker_jason_osm(json, map_canvas_var, keep_markers);
        return;
    }
    var options = eval(map_canvas_var);
    if (jQuery('#' + map_canvas_var).val() == '') { // if map not loaded then load it
        initMap(map_canvas_var);
    }
    jQuery("#" + map_canvas_var).goMap();
    // get the bounds of the map
    bounds = new google.maps.LatLngBounds();
    if (options.marker_cluster) {
        if (typeof remove_cluster_markers == 'function') {
            remove_cluster_markers(map_canvas_var)
        }
    }
	var markerReposition = options.enable_marker_cluster_no_reposition;
	var animation = geodir_params.gMarkerAnimation ? geodir_params.gMarkerAnimation : null;
	if (animation === true) {
		animation = google.maps.Animation.DROP;
	}
	if (keep_markers) {
		gd_map_first_load = true;
		markerReposition = false;
		if (typeof keepBounds != 'undefined' && keepBounds) {
			bounds = keepBounds;
		}
	} else {
		// clear old markers
		jQuery.goMap.clearMarkers();
		keepBounds = '';
	}

	// Don't reposition
	if (geodir_params.gMarkerReposition) {
		markerReposition = true;
	}

    // if no markers found, display home_map_nofound div with no search criteria met message
    if (json.total && parseInt(json.total) > 0) {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'none';
        var mapcenter = new google.maps.LatLng(options.latitude, options.longitude);
        list_markers(json, map_canvas_var, animation);
        var center = bounds.getCenter();
        if (options.autozoom && parseInt(json.total) > 1) {
            if (markerReposition) {} //don't reposition after load
            else {
                jQuery.goMap.map.fitBounds(bounds);
            }
        } else {
            if (markerReposition) {} //dont reposition after load
            else {
                if (options.autozoom && parseInt(json.total) == 1) {
                    jQuery.goMap.map.setZoom(13);
                }
                jQuery.goMap.map.setCenter(center);
            }
        }
        if (jQuery.goMap.map.getZoom() > parseInt(options.maxZoom)) {
            jQuery.goMap.map.setZoom(parseInt(options.maxZoom));
        }
		if (!(typeof keepBounds != 'undefined' && keepBounds)) {
			keepBounds = bounds;
		}
    } else {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'flex';
        var nLat = options.nomap_lat ? options.nomap_lat : (options.default_lat ? options.default_lat : '39.952484');
        var nLng = options.nomap_lng ? options.nomap_lng : (options.default_lng ? options.default_lng : '-75.163786');
        var nZoom = parseInt(options.nomap_zoom) > 0 ? parseInt(options.nomap_zoom) : (parseInt(options.zoom) > 0 ? parseInt(options.zoom) : 11);
        var mapcenter = new google.maps.LatLng(nLat, nLng);
        list_markers(json, map_canvas_var);
        if (markerReposition) {} //dont reposition after load
        else {
            jQuery.goMap.map.setCenter(mapcenter);
            jQuery.goMap.map.setZoom(nZoom);
        }
    }
    if (options.marker_cluster) {
        if (typeof create_marker_cluster == 'function') {
            create_marker_cluster(map_canvas_var)
        }
    }

    // Add near me marker.
    geodir_map_show_near_me(options);

    jQuery('#' + map_canvas_var + '_loading_div').hide();
    jQuery("body").trigger("map_show", map_canvas_var);
}

function list_markers(json, map_canvas_var, animation) {
    var map_options = eval(map_canvas_var);
    var total = parseInt(json.total);
    if (total > 0 && json.items) {
        var baseurl, content_url, icons, icon, icon_url;
        baseurl = json.baseurl;
        content_url = json.content_url;
        icons = json.icons;
        for (var i = 0; i < total; i++) {
            marker = json.items[i];
            if (marker['i'] && icons && icons[marker['i']]['i']) {
                icon = icons[marker['i']];
                if (icon['i']) {
                    icon_url = icon['i'];
                    if (!(icon_url.indexOf("http://") === 0 || icon_url.indexOf("https://") === 0)) {
                        icon_url = icon_url.indexOf("plugins/") === 0 || icon_url.indexOf("plugins/") > 0 ? content_url + icon_url : baseurl + '/' + icon_url
                    }
                    marker['icon'] = icon_url;
                    marker['w'] = icon['w'];
                    marker['h'] = icon['h'];
                    if ( icon['a'] ) {
                        marker['alt'] = icon['a'];
                    }
                }
            }
            if (marker && !marker.animation && animation) {
                marker['animation'] = animation;
            }
            if (map_options.map_type == 'post' && i == 0) {
                jQuery('#' + map_canvas_var).data('lat', marker.lt);
                jQuery('#' + map_canvas_var).data('lng', marker.ln);
            }
            var marker = create_marker(marker, map_canvas_var);
        }
        if (window.gdMaps == 'osm') {
            jQuery.goMap.map.addLayer(jQuery.goMap.gdlayers);
            try {
                if (jQuery.goMap.gdUmarker) {
                    bounds.extend(jQuery.goMap.gdUmarker.getLatLng());
                }
            } catch (e) {}
        }
    }
}

function geodir_htmlEscape(str) {
    return String(str)
        .replace(/&prime;/g, "'")
        .replace(/&frasl;/g, '/')
        .replace(/&ndash;/g, '-')
        .replace(/&ldquo;/g, '"')
        .replace(/&gt;/g, '>')
        .replace(/&quot;/g, '"')
        .replace(/&apos;/g, "'")
        .replace(/&amp;quot;/g, '"')
        .replace(/&amp;apos;/g, "'");
}

// create the marker and set up the event window
function create_marker(item, map_canvas) {
    if (window.gdMaps == 'osm') {
        return create_marker_osm(item, map_canvas);
    }
    var map_options = eval(map_canvas);
    jQuery("#" + map_canvas).goMap();
    gd_infowindow = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.InfoWindow({
        maxWidth: 200
    }) : null;
    if (item.lt && item.ln) {
        var marker_id, title, icon, cs, isSvg, resize = false;
        marker_id = item['m'];
        title = geodir_htmlEscape(item['t']);
        cs = item['cs'];
        icon = item['icon'] ? item['icon'] : geodir_params.default_marker_icon;
        iconW = item['w'] ? parseFloat(item['w']) : 0;
        iconH = item['h'] ? parseFloat(item['h']) : 0;
        iconMW = geodir_params.marker_max_width ? parseFloat(geodir_params.marker_max_width) : 0;
        iconMH = geodir_params.marker_max_height ? parseFloat(geodir_params.marker_max_height) : 0;
        isSvg = icon && icon.substr((icon.lastIndexOf('.')+1)).toLowerCase() == 'svg' ? true : false;
        /* Some svg files has dimensions with different unit */
        if (geodir_params.resize_marker && ( iconW < iconMW || iconH < iconMH ) && isSvg) {
            iconW = iconW * 10;
            iconH = iconH * 10;
        }
        if (geodir_params.resize_marker && iconW > 5 && iconH > 5 && ((iconMW > 5 && iconW > iconMW) || (iconMH > 5 && iconH > iconMH))) {
            resizeW = iconW;
            resizeH = iconH;

            if (iconMH > 5 && resizeH > iconMH) {
                _resizeH = iconMH;
                _resizeW = Math.round(((_resizeH * resizeW) / resizeH) * 10) / 10;

                resizeW = _resizeW;
                resizeH = _resizeH;
                resize = true;
            }

            if (iconMW > 5 && resizeW > iconMW) {
                _resizeW = iconMW;
                _resizeH = Math.round(((_resizeW * resizeH) / resizeW) * 10) / 10;

                resizeW = _resizeW;
                resizeH = _resizeH;
                resize = true;
            }
            if (resize && resizeW > 5 && resizeH > 5) {
                icon = {
                    url: icon,
                    scaledSize: new google.maps.Size(resizeW, resizeH),
                    origin: new google.maps.Point(0, 0),
                    anchor: new google.maps.Point((Math.round(resizeW / 2)), resizeH)
                };
            }
        }
        if (isSvg && !resize && iconW > 5 && iconH > 5) {
            icon = {
                url: icon,
                scaledSize: new google.maps.Size(iconW, iconH),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point((Math.round(iconW / 2)), iconH)
            };
        }
        var latlng = new google.maps.LatLng(item.lt, item.ln);
        var marker = jQuery.goMap.createMarker({
            id: marker_id,
            title: title,
            position: latlng,
            visible: true,
            clickable: true,
            icon: icon,
            label: cs,
            zIndex: (item.zIndex ? item.zIndex : 0),
            zIndexOrg: (item.zIndexOrg ? item.zIndexOrg : 0),
            animation: (item.animation ? item.animation : null)
        });
        bounds.extend(latlng);
        // Adding a click event to the marker
        google.maps.event.addListener(marker, 'spider_click', function() { // 'click' => normal, 'spider_click' => Overlapping Marker Spiderfier
            var marker_url = map_options.map_marker_ajax_url;
            is_zooming = true;
            jQuery("#" + map_canvas).goMap();
            var preview_query_str = '';
            if (item.post_preview) {
                preview_query_str = '&post_preview=' + item.post_preview;
            }
            marker_url = marker_url + '' + item.m;
            post_data = marker_url.indexOf('?') === -1 ? '?' : '&';
            post_data += '_wpnonce=' + map_options._wpnonce;
            if (map_options.bubble_size) {
                post_data += '&small=1';
            }
            if (map_options.map_marker_url_params) {
                post_data += map_options.map_marker_url_params;
            }
            post_data += '&_gdmap=google';
            var loading = '<div id="map_loading" class="p-2 text-center"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></div>';
            gd_infowindow.open(jQuery.goMap.map, marker);
            gd_infowindow.setContent(loading);
            jQuery.ajax({
                type: "GET",
                url: marker_url + post_data,
                cache: false,
                dataType: "json",
                error: function(xhr, error) {
                    alert(error);
                },
                success: function(response) {
                    jQuery("#" + map_canvas).goMap();
                    html = typeof response == 'object' && response.html ? geodir_htmlEscape(response.html) : '';
                    gd_infowindow.setContent(html);
                    gd_infowindow.open(jQuery.goMap.map, marker);
                    setTimeout(function() {
                        jQuery(document.body).trigger('geodir_map_infowindow_open', [{
                            map: 'google',
                            canvas: map_canvas,
                            content: html
                        }]);
                    }, 100);
                    // give the map 1 second to reposition before allowing it to reload
                    setTimeout(function() {
                        is_zooming = false;
                    }, 1000);
                }
            });
            return;
        });
        // Overlapping Marker Spiderfier
        jQuery.goMap.oms.addMarker(marker);
        // Adding a visible_changed event to the marker
        google.maps.event.addListener(marker, 'visible_changed', function() {
            gd_infowindow.close(jQuery.goMap.map, marker);
        });
        return true;
    } else {
        //no lat & long, return no marker
        return false;
    }
}

function geodir_fix_marker_pos(map_canvas_var) {
    // Reference to the DIV that wraps the bottom of infowindow
    if (window.gdMaps == 'osm') {
        var iwOuter = jQuery('#' + map_canvas_var + ' .leaflet-popup-content-wrapper');
    } else {
        var iwOuter = jQuery('#' + map_canvas_var + ' .gm-style-iw');
    }

    var iwBackground = iwOuter.parent();
    org_height = iwBackground.height();
    if (window.gdMaps == 'osm') {
        var mainH = jQuery('#' + map_canvas_var).height();
        org_height = mainH < org_height ? mainH : org_height;
        org_height -= (org_height * 0.10);
    }
    //jQuery('#' + map_canvas_var + ' .geodir-bubble_desc').attr('style', 'height:' + org_height + 'px !important');
}

function openMarker(map_canvas, id) {
    if (!window.gdMaps) {
        return;
    }

    jQuery("#" + map_canvas).goMap();

    if (jQuery('.stickymap').legnth) {} else {
        mTag = false;
        if (jQuery(".geodir-sidebar-wrap .stick_trigger_container").offset()) {
            mTag = jQuery(".geodir-sidebar-wrap .stick_trigger_container").offset().top;
        } else if (jQuery(".stick_trigger_container").offset()) {
            mTag = jQuery(".stick_trigger_container").offset().top;
        }
        if (mTag) {
            jQuery('html,body').animate({
                scrollTop: mTag
            }, 'slow');
        }
    }
    try {
        if (window.gdMaps == 'google') {
            google.maps.event.trigger(jQuery.goMap.mapId.data(id), 'spider_click');
        } else if (window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id) {
                    marker.fireEvent('spider_click');
                }
            });
        }
    } catch (e) {
        console.log(e.message);
    }
}

function animate_marker(map_canvas, id) {
    jQuery("#" + map_canvas).goMap();
    try {
        if (window.gdMaps == 'google') {
            if (jQuery.goMap.mapId.data(id) != null) {
                var anim = geodir_params.markerAnimation;
                if (anim == 'drop' || anim == 'DROP') {
                    _anim = google.maps.Animation.DROP;
                } else if (anim == 'null' || anim == 'none' || anim == null || anim == '') {
                    _anim = null;
                } else {
                    _anim = google.maps.Animation.BOUNCE;
                }
                jQuery.goMap.mapId.data(id).setAnimation(_anim);
            }
        } else if (window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id) {
                    if (!jQuery(marker._icon).hasClass('gd-osm-marker-bounce')) {
                        jQuery(marker._icon).addClass('gd-osm-marker-bounce');
                    }
                }
            });
        }
    } catch (e) {
        console.log(e.message);
    }
}

function stop_marker_animation(map_canvas, id) {
    jQuery("#" + map_canvas).goMap();
    try {
        if (window.gdMaps == 'google') {
            if (jQuery.goMap.mapId.data(id) != null) {
                jQuery.goMap.mapId.data(id).setAnimation(null);
            }
        } else if (window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id) {
                    jQuery(marker._icon).removeClass('gd-osm-marker-bounce');
                }
            });
        }
    } catch (e) {
        console.log(e.message);
    }
}

function geodir_map_sticky(map_options) {
    if (!window.gdMaps) {
        return;
    }
    var cstatus = '';
    var optionsname = map_options;
    map_options = eval(map_options);

    // set if the map should be hidden by default
    if (geodir_is_localstorage()) {
        cstatus = localStorage.getItem("gd_sticky_map");
        if (cstatus == 'shide') {
            jQuery('body').addClass('stickymap_hide');
        }
    }

    if (map_options.sticky && jQuery(window).width() > 1250) {
        jQuery.fn.scrollBottom = function() {
            return this.scrollTop() + this.height();
        };
        var content = jQuery(".geodir-sidebar-wrap").scrollBottom();
        var stickymap = jQuery("#sticky_map_" + optionsname + "").scrollBottom();
        var catcher = jQuery('#catcher_' + optionsname + '');
        var sticky = jQuery('#sticky_map_' + optionsname + '');
        var map_parent = sticky.parent();
        var sticky_show_hide_trigger = sticky.closest('.stick_trigger_container').find('.trigger_sticky');
        var mapheight = jQuery("#sticky_map_" + optionsname + "").height();
        var widthpx = sticky.width();
        var widthmap = map_options.width;

        if (widthmap.indexOf('%') != -1) {
            jQuery('.main_map_wrapper', sticky).width('100%');
            jQuery('.geodir_marker_cluster', sticky).width('100%');
        }

        jQuery(window).scroll(function() {
            jQuery("#" + optionsname + "").goMap(map_options);

            // get the bounds of the map
            if (window.gdMaps == 'osm') {
                bounds = new L.LatLngBounds([]);
            } else {
                bounds = new google.maps.LatLngBounds();
            }

            var wheight = jQuery(window).height();
            var wScrTop = jQuery(window).scrollTop();
            var maxScr = 0;
            if (jQuery('.geodir-category-list-view').length) {
                maxScr = parseFloat(jQuery('.geodir-category-list-view:last').offset().top) + parseFloat(jQuery('.geodir-category-list-view:last').innerHeight()) - 50;
            }
            if (jQuery('.elementor-posts').length) {
                var _maxScr = parseFloat(jQuery('.elementor-posts:last').offset().top) + parseFloat(jQuery('.elementor-posts:last').innerHeight()) - 50;
                if (_maxScr > maxScr) {
                    maxScr = _maxScr;
                }
            }
            if (maxScr < catcher.offset().top) {
                maxScr = 0;
            }
            var noSticky = maxScr > 0 && wScrTop > maxScr && wScrTop > catcher.offset().top ? true : false;

            if (wScrTop >= catcher.offset().top && !noSticky) {
                if (!sticky.hasClass('stickymap')) {
                    sticky.addClass('stickymap');
                    /// sticky.hide();
                    sticky.appendTo('body');
                    sticky.removeClass('position-relative').addClass('position-fixed');
                    sticky.css({
                        'position': 'fixed',
                        'right': '0',
                        'border': '1px solid red'
                    });
                    sticky.css({
                        'top': '25%',
                        'width': (widthpx + 2)
                    });
                    catcher.css({
                        'height': mapheight
                    });

                    if (geodir_is_localstorage()) {
                        cstatus = localStorage.getItem("gd_sticky_map");
                    }
                    window.dispatchEvent(new Event('resize')); // OSM does not work with the jQuery trigger so we do it old skool.
                }
                sticky_show_hide_trigger.removeClass('position-relative').addClass('position-fixed');
                sticky_show_hide_trigger.css({
                    'top': '25%',
                    'position': 'fixed',
                    'right': '0'
                });
                sticky_show_hide_trigger.show();
            }

            if (wScrTop < catcher.offset().top || noSticky) {
                if (sticky.hasClass('stickymap')) {
                    sticky.appendTo(map_parent);
                    sticky.hide();
                    sticky.removeClass('stickymap');
                    sticky.removeClass('position-fixed').addClass('position-relative');
                    sticky.css({
                        'position': 'relative',
                        'border': 'none',
                        'top': '0',
                        'width': widthmap
                    });
                    sticky.fadeIn('slow');
                    catcher.css({
                        'height': '0'
                    });
                    sticky_show_hide_trigger.removeClass('triggeroff_sticky');
                    sticky_show_hide_trigger.addClass('triggeron_sticky');
                    sticky_show_hide_trigger.removeClass('position-fixed').addClass('position-relative');
                    window.dispatchEvent(new Event('resize')); // OSM does not work with the jQuery trigger so we do it old skool.
                }
                sticky_show_hide_trigger.hide();
            }
        });
        jQuery(window).on("resize",function() {
            jQuery(window).scroll();
        });

    } // sticky if end
}
var rendererOptions = {
    draggable: true
};
var directionsDisplay = (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.DirectionsRenderer !== 'undefined') ? new google.maps.DirectionsRenderer(rendererOptions) : {};
var directionsService = (typeof google !== 'undefined' && typeof google.maps !== 'undefined' && typeof google.maps.DirectionsService !== 'undefined') ? new google.maps.DirectionsService() : {};
var renderedDirections = [];

function geodirFindRoute(map_canvas) {
    var map_options, destLat, destLng, $wrap;
    initMap(map_canvas);
    map_options = eval(map_canvas);
    destLat = jQuery('#' + map_canvas).data('lat');
    destLng = jQuery('#' + map_canvas).data('lng');
    $wrap = jQuery('#' + map_canvas).closest('.geodir-map-wrap');
    if (window.gdMaps == 'osm') {
        try {
            var control = L.Routing.control({
                waypoints: [
                    L.latLng(destLat, destLng)
                ],
                routeWhileDragging: true,
                geocoder: L.Control.Geocoder.nominatim(),
                language: geodir_params.osmRouteLanguage,
                waypointNameFallback: function(latLng) {
                    function zeroPad(n) {
                        n = Math.round(n);
                        return n < 10 ? '0' + n : n;
                    }

                    function hexagecimal(p, pos, neg) {
                        var n = Math.abs(p),
                            degs = Math.floor(n),
                            mins = (n - degs) * 60,
                            secs = (mins - Math.floor(mins)) * 60,
                            frac = Math.round((secs - Math.floor(secs)) * 100);
                        return (n >= 0 ? pos : neg) + degs + '°' + zeroPad(mins) + '\'' + zeroPad(secs) + '.' + zeroPad(frac) + '"';
                    }

                    return hexagecimal(latLng.lat, 'N', 'S') + ' ' + hexagecimal(latLng.lng, 'E', 'W');
                }
            });
            var cExists = typeof jQuery.goMap.map._container != 'undefined' && jQuery('.leaflet-control.leaflet-routing-container', jQuery.goMap.map._container).length ? true : false;
            if (!cExists) {
                control.addTo(jQuery.goMap.map);
            }

            L.Routing.errorControl(control).addTo(jQuery.goMap.map);

            var $routing = jQuery('#' + map_canvas + ' .leaflet-routing-geocoders .leaflet-routing-search-info');
            if (!$routing.find('#' + map_canvas + '_mylocation').length) {
                $routing.append('<span title="' + geodir_params.geoMyLocation + '" onclick="gdMyGeoDirection(' + map_canvas + ');" id="' + map_canvas + '_mylocation" class="gd-map-mylocation c-pointer ml-1 ms-1"><i class="fas fa-crosshairs" aria-hidden="true"></i></span>');
            }
        } catch (e) {
            console.log(e.message);
        }
    } else if (window.gdMaps == 'google') {
        // Direction map
        var rendererOptions = {
            draggable: true
        };
        if (renderedDirections.length) {
            for(var i in renderedDirections) {
                renderedDirections[i].setMap(null);
            }
        }
        var directionsDisplay = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.DirectionsRenderer(rendererOptions) : {};
        var directionsService = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.DirectionsService() : {};
        directionsDisplay.setMap(jQuery.goMap.map);
        directionsDisplay.setPanel(document.getElementById(map_canvas + "_directionsPanel"));
        renderedDirections.push(directionsDisplay);
        google.maps.event.addListener(directionsDisplay, 'directions_changed', function() {
            geodirComputeTotalDistance(directionsDisplay.directions, map_canvas);
        });
        jQuery('#directions-options', $wrap).show();
        var from_address = document.getElementById(map_canvas + '_fromAddress').value;
        var request = {
            origin: from_address,
            destination: destLat + ',' + destLng,
            travelMode: gdGetTravelMode($wrap),
            unitSystem: gdGetTravelUnits($wrap)
        };
        directionsService.route(request, function(response, status) {
            if (status == google.maps.DirectionsStatus.OK) {
                jQuery('#' + map_canvas + '_directionsPanel', $wrap).html('');
                directionsDisplay.setDirections(response);
            } else {
                alert(geodir_params.address_not_found_on_map_msg + from_address);
            }
        });
    }
}

function gdGetTravelMode($wrap) {
    var mode = jQuery('#travel-mode', $wrap).val();
    if (mode == 'driving') {
        return google.maps.DirectionsTravelMode.DRIVING;
    } else if (mode == 'walking') {
        return google.maps.DirectionsTravelMode.WALKING;
    } else if (mode == 'bicycling') {
        return google.maps.DirectionsTravelMode.BICYCLING;
    } else if (mode == 'transit') {
        return google.maps.DirectionsTravelMode.TRANSIT;
    } else {
        return google.maps.DirectionsTravelMode.DRIVING;
    }
}

function gdGetTravelUnits($wrap) {
    var mode = jQuery('#travel-units', $wrap).val();
    if (mode == 'kilometers') {
        return google.maps.DirectionsUnitSystem.METRIC;
    } else {
        return google.maps.DirectionsUnitSystem.IMPERIAL;
    }
}

function geodirComputeTotalDistance(result, map_canvas) {
    var total = 0;
    var myroute = result.routes[0];
    for (i = 0; i < myroute.legs.length; i++) {
        total += myroute.legs[i].distance.value;
    }
    totalk = total / 1000
    totalk_round = Math.round(totalk * 100) / 100
    totalm = total / 1609.344
    totalm_round = Math.round(totalm * 100) / 100
    //document.getElementById(map_canvas+"_directionsPanel").innerHTML = "<p>Total Distance: <span id='totalk'>" + totalk_round + " km</span></p><p>Total Distance: <span id='totalm'>" + totalm_round + " miles</span></p>";
}
jQuery(function($) {
    setGeodirMapSize(false);
    $(window).on("resize",function() {
        setGeodirMapSize(true);
    });
})

function setGeodirMapSize(resize) {
    var isAndroid = navigator.userAgent.toLowerCase().indexOf("android") > -1 ? true : false;
    var dW = parseInt(jQuery(window).width());
    var dH = parseInt(jQuery(window).height());
    if (GeodirIsiPhone() || (isAndroid && (((dW > dH && dW == 640 && dH == 360) || (dH > dW && dW == 360 && dH == 640)) || ((dW > dH && dW == 533 && dH == 320) || (dH > dW && dW == 320 && dH == 533)) || ((dW > dH && dW == 960 && dH == 540) || (dH > dW && dW == 540 && dH == 960))))) {
        jQuery(document).find('.geodir_map_container').each(function() {
            jQuery(this).addClass('geodir-map-iphone');
        });
    } else {
        jQuery(document).find('.geodir_map_container').each(function() {
            var $this = this;
            var gmcW = parseInt(jQuery($this).width());
            var gmcH = parseInt(jQuery($this).height());
            if (gmcW >= 400 && gmcH >= 350) {
                jQuery($this).removeClass('geodir-map-small').addClass('geodir-map-full');
            } else {
                jQuery($this).removeClass('geodir-map-full').addClass('geodir-map-small');
            }
        });
        if (resize) {
            jQuery(document).find('.geodir_map_container_fullscreen').each(function() {
                var $this = this;
                var gmcW = parseInt(jQuery(this).find('.gm-style').width());
                var gmcH = parseInt(jQuery(this).find('.gm-style').height());
                if (gmcW >= 400 && gmcH >= 370) {
                    jQuery($this).removeClass('geodir-map-small').addClass('geodir-map-full');
                } else {
                    jQuery($this).removeClass('geodir-map-full').addClass('geodir-map-small');
                }
            });
        }
    }
}

function GeodirIsiPhone() {
    if ((navigator.userAgent.toLowerCase().indexOf("iphone") > -1) || (navigator.userAgent.toLowerCase().indexOf("ipod") > -1) || (navigator.userAgent.toLowerCase().indexOf("ipad") > -1)) {
        return true;
    } else {
        return false;
    }
}

function initMapOSM(map_options) {
    map_options = eval(map_options);
    map_options.zoom = parseInt(map_options.zoom);
    var options = map_options;
    var pscaleFactor;
    var pstartmin;
    var ajax_url = options.ajax_url;
    var token = options.token;
    var search_string = options.token;
    var mm = 0; // marker array
    var maptype = options.maptype;
    var zoom = options.zoom;
    var latitude = options.latitude;
    var longitude = options.longitude;
    var maxZoom = options.maxZoom;
    var etype = options.etype;
    var autozoom = options.autozoom;
    var scrollwheel = options.scrollwheel;
    var fullscreenControl = options.fullscreenControl;
    var streetview = options.streetViewControl;
    var bubble_size = options.bubble_size;
    var map_canvas = options.map_canvas;
    var enable_map_direction = options.enable_map_direction;
    var enable_cat_filters = options.enable_cat_filters;
    var marker_cluster = options.marker_cluster;
    options.token = '68f48005e256696074e1da9bf9f67f06';
    options.navigationControlOptions = {
        position: 'topleft'
    };

    // Create map
    jQuery("#" + map_canvas).goMap(options);

    var styles = [{
        featureType: "poi.business",
        elementType: "labels",
        stylers: [{
            visibility: "off"
        }]
    }];

    if (typeof geodir_custom_map_style !== 'undefined') {
        styles = geodir_custom_map_style;
    }

    /* custom google map style */
    if (typeof options.mapStyles != 'undefined') {
        try {
            var mapStyles = JSON.parse(options.mapStyles);
            if (typeof mapStyles == 'object' && mapStyles) {
                styles = mapStyles;
            }
        } catch (err) {
            console.log(err.message);
        }
    }

    /* custom google map style */ // TODO for styles
    //jQuery.goMap.map = L.Util.setOptions(jQuery.goMap.map, { styles: styles });

    L.DomEvent.addListener(jQuery.goMap.map, 'moveend', function() {
        if (eval(map_canvas).marker_cluster_server) {
            if (gd_map_first_load) { // first load do nothing
                gd_map_first_load = false;
                eval(map_canvas).enable_marker_cluster_no_reposition = true;
            } else {
                if (is_zooming) {} else {
                    is_zooming = true;
                    build_map_ajax_search_param(map_canvas, false, false, true);
                    is_zooming = false;
                }
            }
        }
    });

    // maxMap
    var btnCSS = jQuery("." + map_canvas + "_TopLeft").attr("style");
    jQuery("." + map_canvas + "_TopLeft").attr("style", "margin-top: 85px !important;" + btnCSS);

    var maxMap = document.getElementById(map_canvas + '_triggermap');

    if (!jQuery(maxMap).hasClass('gd-triggered-map')) { // skip multiple click listener after reload map via ajax
        jQuery(maxMap).addClass('gd-triggered-map');
        L.DomEvent.addListener(maxMap, 'click', gdMaxMapOSM);
    }

    function gdMaxMapOSM() {

        if (jQuery('body').hasClass('body_fullscreen')) {
            jQuery('#catcher_' + map_canvas + '').removeClass('position-fixed');
            jQuery(window).scroll();
            jQuery('#placeholder_' + map_canvas).after(jQuery(gd_fullscreen_parent));
            jQuery('#placeholder_' + map_canvas).remove();
        } else {
            jQuery('#catcher_' + map_canvas + '').addClass('position-fixed');
            jQuery(window).scroll();
            gd_fullscreen_parent = jQuery('#' + map_canvas).parents(".stick_trigger_container"); //.parent();
            jQuery(gd_fullscreen_parent).before('<div id="placeholder_' + map_canvas + '"></div>');
            jQuery(gd_fullscreen_parent).prependTo("body");
        }

        // new
        if (gd_toggle_bsui) {
            jQuery('html').toggleClass('bsui');
        }
        jQuery('html').attr('style', function(index, attr) {
            return attr == 'margin-top:0 !important;' ? '' : 'margin-top:0 !important;';
        });
        jQuery('body').toggleClass('body_fullscreen overflow-hidden');
        jQuery('#' + map_canvas + ', #sticky_map_' + map_canvas + ',#' + map_canvas + '_wrapper,#' + map_canvas + '_loading_div,#' + map_canvas + '_map_nofound,#' + map_canvas + '_map_notloaded').toggleClass('vw-100 vh-100');
        jQuery('#' + map_canvas + '_triggermap i, .geodir-map-directions-wrap, #wpadminbar').toggleClass('d-none');

        window.setTimeout(function() {
            setGeodirMapSize(true);
            jQuery.goMap.map._onResize();
            jQuery.goMap.map.invalidateSize();
            window.dispatchEvent(new Event('resize'));
        }, 100);
    }

    // Overlapping Marker Spiderfier LeafLet

    jQuery.goMap.oms.addListener('spiderfy', function(markers) {
        jQuery.goMap.map.closePopup();
    });

    window.oms = jQuery.goMap.oms;
}

function parse_marker_jason_osm(json, map_canvas_var, keep_markers) {
    var options = eval(map_canvas_var);
    if (jQuery('#' + map_canvas_var).val() == '') { // if map not loaded then load it
        initMapOSM(map_canvas_var);
    } else {
        jQuery("#" + map_canvas_var).goMap();
    }
    // get the bounds of the map
    bounds = new L.LatLngBounds([]);
	var markerReposition = options.enable_marker_cluster_no_reposition;
    if (keep_markers) {
		gd_map_first_load = true;
		markerReposition = false;
		if (typeof keepBounds != 'undefined' && keepBounds) {
			bounds = keepBounds;
		}
	} else {
		// clear old markers
		jQuery.goMap.clearMarkers();
		keepBounds = '';
	}
	// Don't reposition
	if (geodir_params.gMarkerReposition) {
		markerReposition = true;
	}
    // if no markers found, display home_map_nofound div with no search criteria met message
    if (json.total && parseInt(json.total) > 0) {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'none';
        list_markers(json, map_canvas_var);
        var center = bounds.getCenter();
        if (options.autozoom && parseInt(json.total) > 1) {
            if (markerReposition) {
                //dont reposition after load
            } else {
                jQuery.goMap.map.fitBounds(bounds);
            }
        } else {
            if (markerReposition) {
                //dont reposition after load
            } else {
                setZoom = jQuery.goMap.map.getZoom();
                if (options.autozoom && parseInt(json.total) == 1) {
                    setZoom = 13;
                }
                jQuery.goMap.map.setView(center, setZoom);
            }
        }
        if (jQuery.goMap.map.getZoom() > parseInt(options.maxZoom)) {
            jQuery.goMap.map.setZoom(parseInt(options.maxZoom));
        }
		if (!(typeof keepBounds != 'undefined' && keepBounds)) {
			keepBounds = bounds;
		}
    } else {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'flex';
        var nLat = options.nomap_lat ? options.nomap_lat : (options.default_lat ? options.default_lat : '39.952484');
        var nLng = options.nomap_lng ? options.nomap_lng : (options.default_lng ? options.default_lng : '-75.163786');
        var nZoom = parseInt(options.nomap_zoom) > 0 ? parseInt(options.nomap_zoom) : (parseInt(options.zoom) > 0 ? parseInt(options.zoom) : 11);
        var mapcenter = new L.latLng(nLat, nLng);
        list_markers(json, map_canvas_var);
        if (markerReposition) {} //dont reposition after load
        else {
            jQuery.goMap.map.setView(mapcenter, nZoom);
        }
    }

    // Add near me marker.
    geodir_map_show_near_me_osm(options);

    jQuery('#' + map_canvas_var + '_loading_div').hide();
    jQuery("body").trigger("map_show", map_canvas_var);
}

function create_marker_osm(item, map_canvas) {
    var options = eval(map_canvas);
    jQuery("#" + map_canvas).goMap();
    if (item.lt && item.ln) {
        var marker_id, title, icon, iconW, iconH, cs;
        marker_id = item['m'];
        title = geodir_htmlEscape(item['t']);
        cs = item['cs'];
        icon = item['icon'] ? item['icon'] : geodir_params.default_marker_icon;
        iconW = item['w'] ? item['w'] : geodir_params.default_marker_w;
        iconH = item['h'] ? item['h'] : geodir_params.default_marker_h;
        iconMW = geodir_params.marker_max_width ? parseFloat(geodir_params.marker_max_width) : 0;
        iconMH = geodir_params.marker_max_height ? parseFloat(geodir_params.marker_max_height) : 0;
        /* Some svg files has dimensions with different unit */
        if (geodir_params.resize_marker && ( iconW < iconMW || iconH < iconMH ) && icon.substr((icon.lastIndexOf('.')+1)).toLowerCase() == 'svg') {
            iconW = iconW * 10;
            iconH = iconH * 10;
        }
        if (geodir_params.resize_marker && iconW > 5 && iconH > 5 && ((iconMW > 5 && iconW > iconMW) || (iconMH > 5 && iconH > iconMH))) {
            resizeW = iconW;
            resizeH = iconH;
            resize = false;

            if (iconMH > 5 && resizeH > iconMH) {
                _resizeH = iconMH;
                _resizeW = Math.round(((_resizeH * resizeW) / resizeH) * 10) / 10;

                resizeW = _resizeW;
                resizeH = _resizeH;
                resize = true;
            }

            if (iconMW > 5 && resizeW > iconMW) {
                _resizeW = iconMW;
                _resizeH = Math.round(((_resizeW * resizeH) / resizeW) * 10) / 10;

                resizeW = _resizeW;
                resizeH = _resizeH;
                resize = true;
            }
            if (resize && resizeW > 5 && resizeH > 5) {
                iconW = resizeW;
                iconH = resizeH;
            }
        }
        var coord = new L.latLng(item.lt, item.ln);
        var marker = jQuery.goMap.createMarker({
            id: marker_id,
            title: title,
            alt: item.alt ? geodir_htmlEscape(item.alt) : '',
            position: coord,
            visible: true,
            clickable: true,
            icon: icon,
            label: cs,
            w: iconW,
            h: iconH,
            clustered: (parseInt(options.marker_cluster) === 1) && typeof item.cs !== 'undefined' ? true : false,
            zIndex: (item.zIndex ? item.zIndex : 0),
            zIndexOrg: (item.zIndexOrg ? item.zIndexOrg : 0)
        });
        if ((parseInt(options.marker_cluster) === 1) && cs) {
            var labels = cs.split("_");
            bounds.extend(new L.latLng(labels[1], labels[2]));
            if (labels[1] != labels[3] && labels[2] != labels[4]) {
                bounds.extend(new L.latLng(labels[3], labels[4]));
            }
        } else {
            bounds.extend(coord);
        }
        // Adding a click event to the marker
        L.DomEvent.addListener(marker, 'click', function() {
            marker.fireEvent('spider_click');
        });
        L.DomEvent.addListener(marker, 'spider_click', function() {// 'click' => normal, 'spider_click' => Overlapping Marker Spiderfier
            var marker_url = options.map_marker_ajax_url;
            if (marker.options.clustered) {
                jQuery("#" + map_canvas).goMap();
                marker.closePopup().unbindPopup();
                var fitBounds = false;
                if (marker.options.label) {
                    var labels = marker.options.label.split("_");
                    var newBounds = new L.LatLngBounds([]);
                    var lat1 = labels[1];
                    var lng1 = labels[2];
                    var lat2 = labels[3];
                    var lng2 = labels[4];
                    newBounds.extend(new L.latLng(lat1, lng1));
                    if (lat1 == lat2 && lng1 == lng2) {
                        var lat2 = lat2 * 1.00000001;
                        var lng2 = lng2 * 1.00000001;
                    }
                    newBounds.extend(new L.latLng(lat2, lng2));
                    jQuery.goMap.map.fitBounds(newBounds);
                    bounds = newBounds;
                    if (jQuery.goMap.map.getZoom() > parseInt(options.maxZoom)) {
                        jQuery.goMap.map.setZoom(parseInt(options.maxZoom));
                    }
                } else {
                    zoom = parseInt(jQuery.goMap.map.getZoom()) + 1 > parseInt(options.maxZoom) && parseInt(options.maxZoom) > 0 ? parseInt(options.maxZoom) : parseInt(jQuery.goMap.map.getZoom()) + 1;
                    jQuery.goMap.map.setView(marker.getLatLng(), zoom);
                }
                return;
            } else {
                is_zooming = true;
                jQuery("#" + map_canvas).goMap();
            }
            marker_url = marker_url + '' + item.m;
            post_data = marker_url.indexOf('?') === -1 ? '?' : '&';
            post_data += '_wpnonce=' + options._wpnonce;
            if (options.bubble_size) {
                post_data += '&small=1';
            }
            if (options.map_marker_url_params) {
                post_data += options.map_marker_url_params;
            }
            post_data += '&_gdmap=osm';
            var loading = '<div id="map_loading" class="p-2 text-center"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i></div>';
            var maxH = jQuery("#" + map_canvas).height();
            maxH -= (maxH * 0.10) + jQuery(marker._icon).outerHeight() + 20;
            marker.closePopup().unbindPopup().bindPopup(loading, {
                className: 'gd-osm-bubble',
                maxHeight: maxH
            }).openPopup();
            jQuery.ajax({
                type: "GET",
                url: marker_url + post_data,
                cache: false,
                dataType: "json",
                error: function(xhr, error) {
                    alert(error);
                },
                success: function(response) {
                    jQuery("#" + map_canvas).goMap();
                    html = typeof response == 'object' && response.html ? geodir_htmlEscape(response.html) : '';
                    marker.bindPopup(html);
                    setTimeout(function() {
                        jQuery(document.body).trigger('geodir_map_infowindow_open', [{
                            map: 'osm',
                            canvas: map_canvas,
                            content: html
                        }]);
                    }, 100);
                    // give the map 1 second to reposition before allowing it to reload
                    setTimeout(function() {
                        is_zooming = false;
                    }, 1000);
                }
            });
            return;
        });
        // Overlapping Marker Spiderfier LeafLet
        jQuery.goMap.oms.addMarker(marker);
        // Adding a visible_changed event to the marker
        L.DomEvent.addListener(marker, 'visible_changed', function() {
            marker.closePopup();
        });
        return true;
    } else {
        //no lat & long, return no marker
        return false;
    }
}

function gdMyGeoDirection(map_canvas) {
    window.currentMapCanvas = map_canvas;
    gd_get_user_position(gdMyGeoPositionSuccess);
}

function gdMyGeoPositionSuccess(myLat, myLng) {
    if (myLat && myLng) {
        var geoAddress = myLat + ', ' + myLng;
        if (window.gdMaps == 'google' || window.gdMaps == 'osm') {
            gdMyGeoGetDirections(geoAddress);
        }
    }
}

function gdMyGeoGetDirections(address) {
    var map_canvas = window.currentMapCanvas;
    if (!address) {
        return false;
    }
    window.gdMyGeo = true;
    if (window.gdMaps == 'google') {
        jQuery('#' + map_canvas + '_fromAddress').val(address);
        geodirFindRoute(map_canvas);
    } else if (window.gdMaps == 'osm') {
        jQuery('.leaflet-routing-geocoders .leaflet-routing-geocoder:last input').val(address).focus();
        setTimeout(function() {
            jQuery('.leaflet-routing-geocoders .leaflet-routing-geocoder:last input').trigger({
                type: 'keypress',
                which: 13,
                keyCode: 13
            });
        }, 1000);
    }
}

function geodir_map_directions_init(map_canvas) {
    if (window.gdMaps == 'google') {
        try {
            // Create the autocomplete object, restricting the search
            // to geographical location types.
            autocomplete = new google.maps.places.Autocomplete(
                /** @type {HTMLInputElement} */
                (document.getElementById(map_canvas + '_fromAddress')), {
                    types: ['geocode']
                });
            // When the user selects an address from the dropdown,
            // populate the address fields in the form.
            google.maps.event.addListener(autocomplete, 'place_changed', function() {
                geodirFindRoute(map_canvas);
            });
        } catch (e) {
            console.log(e.message);
        }
    } else {
        jQuery('#' + map_canvas + '_fromAddress, .geodir-map-directions-wrap').hide();
        jQuery('.gd-get-directions').hide();
        jQuery('.' + map_canvas + '_getdirection').hide();

        if (window.gdMaps == 'osm') {
            window.setTimeout(function() {
                geodirFindRoute(map_canvas);
            }, 1000);
        }
    }
}

function geodir_map_post_type_terms(options, post_type, query_string) {
    var terms_query_url, map_canvas, tick_terms;
    terms_query_url = options.map_terms_ajax_url;
    map_canvas = options.map_canvas;

    jQuery('#' + map_canvas + '_posttype_menu li').removeClass('gd-map-search-pt');
    jQuery('#' + map_canvas + '_posttype_menu li#' + post_type).addClass('gd-map-search-pt');

    query_string += "&output=terms";
    query_string += "&map_canvas=" + map_canvas;
    query_string += "&child_collapse=" + jQuery('#' + map_canvas + '_child_collapse').val();

    terms = options.terms;
    if (terms) {
        query_string += "&terms=" + terms;
    }
    tick_terms = options.tick_terms;
    if (tick_terms) {
        query_string += "&tick_terms=" + tick_terms;
    }

    u = terms_query_url.indexOf('?') === -1 ? '?' : '&';
    terms_query_url += u + query_string;

    jQuery('#' + map_canvas + '_loading_div').show();
    jQuery.ajax({
        type: "GET",
        url: terms_query_url,
        dataType: "json",
        success: function(data) {
            jQuery('#' + map_canvas + '_loading_div').hide();
            if (data && data.terms_filter) {
                jQuery('#' + map_canvas + '_cat .geodir_toggle').html(data.terms_filter);
                geodir_show_sub_cat_collapse_button();
                build_map_ajax_search_param(map_canvas, false);
            }
            return false;
        },
        error: function(xhr, textStatus, errorThrown) {
            jQuery('#' + map_canvas + '_loading_div').hide();
            console.log(errorThrown);
        }
    });
    return false;
}

/**
 * Show near me marker on Google map.
 */
function geodir_map_show_near_me(options) {
    var iMarker, oMarker, bDrag;

    if (options.nearLat && options.nearLng && options.nearIcon) {
        bDrag = options.nearDraggable ? true : false;

        iMarker = {
            url: options.nearIcon,
            size: null,
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(8, 8),
            scaledSize: new google.maps.Size(17, 17)
        };

        oMarker = jQuery.goMap.createMarker({
            optimized: true,
            flat: true,
            draggable: bDrag,
            id: 'nearme',
            title: options.nearTitle,
            position: new google.maps.LatLng(options.nearLat, options.nearLng),
            visible: true,
            clickable: false,
            icon: iMarker
        });

        jQuery.goMap.gdUmarker = oMarker;
    }
}

/**
 * Show near me marker on OpenstreetMap.
 */
function geodir_map_show_near_me_osm(options) {
    var oMarker, bDrag;

    if (options.nearLat && options.nearLng && options.nearIcon && !jQuery.goMap.gdUmarker) {
        bDrag = options.nearDraggable ? true : false;

        oMarker = jQuery.goMap.createMarker({
            optimized: false,
            flat: true,
            draggable: bDrag,
            id: 'mapme',
            title: options.nearTitle,
            position: new L.latLng(options.nearLat, options.nearLng),
            visible: true,
            clickable: false,
            addToMap: true,
            zIndex: 0
        });

        oMarker.setIcon(L.divIcon({
            iconSize: [17, 17],
            iconAnchor: [8.5, 8.5],
            className: 'geodir-near-marker',
            html: '<div class="geodir-near-marker-wrap"><div class="geodir-near-marker-animate"></div><img class="geodir-near-marker-img" src="' + options.nearIcon + '" /></div>'
        }));

        jQuery.goMap.gdUmarker = oMarker;
    }
}

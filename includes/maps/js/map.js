var is_zooming = false;
var zoom_listener = '';
var gd_current_query = '';
var gd_map_first_load = true;
var gd_fullscreen_parent = '';

function initMap(map_options) {
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
	var map_ajax_url = options.map_ajax_url;
    options.token = '68f48005e256696074e1da9bf9f67f06';
    options.navigationControlOptions = { position: 'TOP_LEFT', style: 'ZOOM_PAN' };

    // Create map
    jQuery("#" + map_canvas).goMap(options);
    // set max zoom
    var styles = [{
        featureType: "poi.business",
        elementType: "labels",
        stylers: [
            { visibility: "off" }
        ]
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
    jQuery.goMap.map.setOptions({ styles: styles });

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
                if (is_zooming) {
                } else {
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
        google.maps.event.addDomListener(maxMap, 'click', gdMaxMap);
    }


    function gdMaxMap() {


        if(jQuery('body').hasClass('body_fullscreen')){
            jQuery('#placeholder_' + map_canvas).after(jQuery(gd_fullscreen_parent ));
            jQuery('#placeholder_' + map_canvas).remove();
        }else{
            gd_fullscreen_parent  = jQuery('#' + map_canvas).parents(".stick_trigger_container");//.parent();
            jQuery(gd_fullscreen_parent ).before('<div id="placeholder_' + map_canvas+ '"></div>');
            jQuery(gd_fullscreen_parent).prependTo("body");
        }

        jQuery('#' + map_canvas).toggleClass('map-fullscreen');
        jQuery('.' + map_canvas + '_map_category').toggleClass('map_category_fullscreen');
        jQuery('#' + map_canvas + '_trigger').toggleClass('map_category_fullscreen');
        jQuery('body').toggleClass('body_fullscreen');
        jQuery('#' + map_canvas + '_loading_div').toggleClass('loading_div_fullscreen');
        jQuery('#' + map_canvas + '_map_nofound').toggleClass('nofound_fullscreen');
        jQuery('#' + map_canvas + '_triggermap').toggleClass('triggermap_fullscreen');
        jQuery('.trigger').toggleClass('triggermap_fullscreen');
        jQuery('.map-places-listing').toggleClass('triggermap_fullscreen');
        jQuery('.' + map_canvas + '_TopLeft').toggleClass('TopLeft_fullscreen');
        jQuery('#' + map_canvas + '_triggermap').closest('.geodir_map_container').toggleClass('geodir_map_container_fullscreen');
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

function build_map_ajax_search_param(map_canvas, reload_cat_list, catObj, hide_loading) {
    if (!window.gdMaps) {
        jQuery('#' + map_canvas + '_loading_div').hide();
        jQuery('#' + map_canvas + '_map_notloaded').show();
        jQuery('#sticky_map_' + map_canvas).find('.map-category-listing-main').hide();
        jQuery('#sticky_map_' + map_canvas).find('#' + map_canvas + '_posttype_menu').hide();
        jQuery('#sticky_map_' + map_canvas).find('.' + map_canvas + '_TopLeft').hide();
        jQuery('#sticky_map_' + map_canvas).find('.' + map_canvas + '_TopRight').hide();
        return false;
    }
	var $container, options, map_type, post_type, query_string = '', search;

	$container = jQuery('#sticky_map_' + map_canvas).closest('.stick_trigger_container');
	options = eval(map_canvas);
	map_type = options.map_type;
	post_type = options.post_type;

	// @TODO implement country, region, city, hood for multilocation etc
	search = jQuery('#' + map_canvas + '_search_string').val();

	query_string += '?post_type=' + post_type;
	jQuery('[name="' + map_canvas + '_cat[]"]:checked').each(function() {
		if (jQuery(this).val()) {
			query_string += '&term[]=' + jQuery(this).val();
		}
	});

	// Terms
	terms = options.terms;
	if (terms) {
		if ( typeof terms == 'object' || typeof terms == 'array' ) {
		} else {
			terms = terms.split(',');
		}
		if (terms.length > 0) {
			query_string += '&term[]=' + terms.join("&term[]=");
		}
	}
	
	// Posts
	posts = options.posts;
	if (posts) {
		if ( typeof posts == 'object' || typeof posts == 'array' ) {
		} else {
			posts = posts.split(',');
		}
		if (posts.length > 0) {
			query_string += '&post[]=' + posts.join("&post[]=");
		}
	}

	search = search ? search.trim() : '';
	if (search && search != options.inputText) {
		query_string += '&search=' + jQuery('#' + map_canvas + '_search_string').val();
	}

    map_ajax_search(map_canvas, query_string, '', hide_loading);
	return
    var child_collapse = jQuery('#' + map_canvas + '_child_collapse').val();
    var ptype = new Array(),
        search_string = '',
        stype = ''
    var gd_posttype = '';
    var gd_cat_posttype = '';
    var gd_pt = '';
    var gd_zl = '';
    var gd_lat_ne = '';
    var gd_lon_ne = '';
    var gd_lat_sw = '';
    var gd_lon_sw = '';
    var my_lat = '';
    var $gd_country = '';
    var $gd_region = '';
    var $gd_city = '';
    var $gd_neighbourhood = '';

    //var mapObject = new google.maps.Map(document.getElementById("map"), _mapOptions);
    // jQuery.goMap.map
    var map_info = '';
    if (jQuery.goMap.map && options.marker_cluster_server) { // map loaded so we know the bounds
        bounds = jQuery.goMap.map.getBounds();
        gd_zl = jQuery.goMap.map.getZoom();

        if(bounds){
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
        map_info = "&zl=" + gd_zl;
    } else if (options.marker_cluster_server && options.autozoom) { // map not loaded and auto zoom set
        gd_zl = options.zoom;
        gd_map_h = jQuery('#' + map_canvas).height();
        gd_map_w = jQuery('#' + map_canvas).width();
        map_info = "&zl=" + gd_zl + "&gd_map_h=" + gd_map_h + "&gd_map_w=" + gd_map_w;
    }
    //check for near me page
    if (typeof my_location !== 'undefined' && my_location && lat && lon) {
        my_lat = lat;
        my_lon = lon;
        map_info = map_info + "&my_lat=" + my_lat + "&my_lon=" + my_lon;
    }
    if (jQuery('#' + map_canvas + '_posttype').val() != '' && jQuery('#' + map_canvas + '_posttype').val() != '0') {
        gd_posttype = jQuery('#' + map_canvas + '_posttype').val();
        gd_pt = gd_posttype;
        gd_cat_posttype = jQuery('#' + map_canvas + '_posttype').val();
        gd_posttype = '&gd_posttype=' + gd_posttype;
    }
    // Set class for searched post type
    if (gd_pt && gd_pt != '') {
        var elList = jQuery('#' + map_canvas + '_posttype_menu .geodir-map-posttype-list ul');
        jQuery(elList).find('li').removeClass('gd-map-search-pt');
        jQuery(elList).find('li#' + gd_pt).addClass('gd-map-search-pt');
    }
    // Check/uncheck child categories
    if (typeof catObj == 'object') {
        if (jQuery(catObj).is(':checked')) {
            jQuery(catObj).parent('li').find('input[name="' + map_canvas + '_cat[]"]').attr('checked', true);
        } else {
            jQuery(catObj).parent('li').find('input[name="' + map_canvas + '_cat[]"]').attr('checked', false);
        }
    }
    if (jQuery('#' + map_canvas + '_jason_enabled').val() == 1) {
        parse_marker_jason(eval(map_canvas + '_jason_args.' + map_canvas + '_jason'), map_canvas)
        return false;
    }

    var location_string = '';
    var hood_string = '';
    if (jQuery('#' + map_canvas + '_country').val() != '') {
        $gd_country = jQuery('#' + map_canvas + '_country').val();
        location_string = location_string + '&gd_country=' + $gd_country;
    }
    if (jQuery('#' + map_canvas + '_region').val() != '') {
        $gd_region = jQuery('#' + map_canvas + '_region').val();
        location_string = location_string + '&gd_region=' + $gd_region;
    }
    if (jQuery('#' + map_canvas + '_city').val() != '') {
        $gd_city = jQuery('#' + map_canvas + '_city').val();
        location_string = location_string + '&gd_city=' + $gd_city;
    }
    if (jQuery('#' + map_canvas + '_neighbourhood').val() != '') {
        $gd_neighbourhood = jQuery('#' + map_canvas + '_neighbourhood').val();
        //location_string = location_string+'&gd_neighbourhood='+$gd_neighbourhood;
        hood_string = location_string + '&gd_neighbourhood=' + $gd_neighbourhood;
    }

    if (reload_cat_list) // load the category listing in map canvas category list panel
    {
        jQuery.get(options.ajax_url, {
            geodir_ajax: 'map_ajax',
            ajax_action: 'homemap_catlist',
            post_type: gd_cat_posttype,
            map_canvas: map_canvas,
            child_collapse: child_collapse,
            gd_country: $gd_country,
            gd_region: $gd_region,
            gd_city: $gd_city,
            gd_neighbourhood: $gd_neighbourhood
        }, function(data) {
            if (data) {
                jQuery('#' + map_canvas + '_cat .geodir_toggle').html(data);
                //show_category_filter(map_canvas);
                geodir_show_sub_cat_collapse_button();
                build_map_ajax_search_param(map_canvas, false);
                return false;
            }
        });
        return false;
    }
    search_string = (jQuery('#' + map_canvas + '_search_string').val() != eval(map_canvas).inputText) ? jQuery('#' + map_canvas + '_search_string').val() : '';

    //loop through available categories
    var mapcat = document.getElementsByName(map_canvas + "_cat[]");
    var checked = "";
    var none_checked = "";
    var i = 0;
    for (i = 0; i < mapcat.length; i++) {
        if (mapcat[i].checked) {
            checked += mapcat[i].value + ",";
        } else {
            none_checked += mapcat[i].value + ",";
        }
    }
    if (checked == "") {
        checked = none_checked;
    }
    var strLen = checked.length;
    checked = checked.slice(0, strLen - 1);
    var search_query_string = '&geodir_ajax=map_ajax&ajax_action=cat&cat_id=' + checked + "&search=" + search_string + hood_string + map_info;
    if (gd_posttype != ''){
        search_query_string = search_query_string + gd_posttype;
    }

    if(location_string != ''){
        search_query_string = search_query_string+location_string;
    }
}

function geodir_show_sub_cat_collapse_button() {
    jQuery('ul.main_list li').each(function(i) {
        var sub_cat_list = jQuery(this).find('ul.sub_list');
        //alert((typeof sub_cat_list.attr('class') ==='undefined')) ;
        if (!(typeof sub_cat_list.attr('class') === 'undefined')) {
            if (sub_cat_list.is(':visible')) {
                jQuery(this).find('i').removeClass('fa-long-arrow-down');
                jQuery(this).find('i').addClass('fa-long-arrow-up');
            } else {
                jQuery(this).find('i').removeClass('fa-long-arrow-up');
                jQuery(this).find('i').addClass('fa-long-arrow-down');
            }
            jQuery(this).find('i').show();
            /**/
        } else {
            jQuery(this).find('i').hide();
            /**/
        }
    });
    geodir_activate_collapse_pan();
}

function geodir_activate_collapse_pan() {
    jQuery('ul.main_list').find('i').click(function() {
        jQuery(this)
            .parent('li')
            .find('ul.sub_list')
            .toggle(200,
                function() {
                    if (jQuery(this).is(':visible')) {
                        jQuery(this).parent('li').find('i').removeClass('fa-long-arrow-down');
                        jQuery(this).parent('li').find('i').addClass('fa-long-arrow-up');
                    } else {
                        jQuery(this).parent('li').find('i').removeClass('fa-long-arrow-up');
                        jQuery(this).parent('li').find('i').addClass('fa-long-arrow-down');
                    }
                });
    });
}

function map_ajax_search(map_canvas_var, query_string, marker_jason, hide_loading) {
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
    else { jQuery('#' + map_canvas_var + '_loading_div').show(); }
    if (marker_jason != '') {
        parse_marker_jason(marker_jason, map_canvas_var);
        //document.getElementById( map_canvas+'_loading_div').style.display="none";
        jQuery('#' + map_canvas_var + '_loading_div').hide();
        return;
    }
    var query_url = eval(map_canvas_var).map_ajax_url + query_string;
    if (gd_current_query == map_canvas_var + '-' + query_url) { jQuery('#' + map_canvas_var + '_loading_div').hide(); } //dont run again
    else {
        gd_current_query = map_canvas_var + '-' + query_url;
        jQuery.ajax({
            type: "GET",
            url: query_url,
            success: function(data) {
				jQuery('#' + map_canvas_var + '_loading_div').hide();
                parse_marker_jason(data, map_canvas_var);
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

function parse_marker_jason(json, map_canvas_var) {
	if (window.gdMaps == 'osm') {
		parse_marker_jason_osm(json, map_canvas_var);
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
	// clear old markers
	jQuery.goMap.clearMarkers();
	// if no markers found, display home_map_nofound div with no search criteria met message
	if (json.total && parseInt(json.total) > 0) {
		document.getElementById(map_canvas_var + '_map_nofound').style.display = 'none';
		var mapcenter = new google.maps.LatLng(options.latitude, options.longitude);
		list_markers(json, map_canvas_var);
		var center = bounds.getCenter();
		if (options.autozoom && parseInt(json.total) > 1) {
			if (options.enable_marker_cluster_no_reposition) {} //dont reposition after load
			else {
				jQuery.goMap.map.fitBounds(bounds);
			}
		} else {
			if (options.enable_marker_cluster_no_reposition) {} //dont reposition after load
			else {
				jQuery.goMap.map.setCenter(center);
			}
		}
		if (jQuery.goMap.map.getZoom() > parseInt(options.maxZoom)) {
			jQuery.goMap.map.setZoom(parseInt(options.maxZoom));
		}
	} else {
		document.getElementById(map_canvas_var + '_map_nofound').style.display = 'block';
		var mapcenter = new google.maps.LatLng(options.latitude, options.longitude);
		list_markers(json, map_canvas_var);
		if (options.enable_marker_cluster_no_reposition) {} //dont reposition after load
		else {
			jQuery.goMap.map.setCenter(mapcenter);
			jQuery.goMap.map.setZoom(options.zoom);
		}
	}
	if (options.marker_cluster) {
		if (typeof create_marker_cluster == 'function') {
			create_marker_cluster(map_canvas_var)
		}
	}
	jQuery('#' + map_canvas_var + '_loading_div').hide();
	jQuery("body").trigger("map_show", map_canvas_var);
}

function list_markers(json, map_canvas_var) {
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
					marker['icon'] = icon_url.indexOf("plugins/") === 0 || icon_url.indexOf("plugins/") > 0 ? content_url + icon_url : baseurl + '/' + icon_url;
					marker['w'] = icon['w'];
					marker['h'] = icon['h'];
				}
			}
			if (map_options.map_type == 'post' && i == 0 ) {
				jQuery('#' + map_canvas_var ).data('lat', marker.lt);
				jQuery('#' + map_canvas_var ).data('lng', marker.ln);
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
    if (item.lt && item.ln) {
        var marker_id, title, icon, cs;
        marker_id = item['m'];
        title = geodir_htmlEscape(item['t']);
        cs = item['cs'];
        icon = item['icon'] ? item['icon'] : geodir_params.default_marker_icon;
        var latlng = new google.maps.LatLng(item.lt, item.ln);
        var marker = jQuery.goMap.createMarker({
            id: marker_id,
            title: title,
            position: latlng,
            visible: true,
            clickable: true,
            icon: icon,
            label: cs
        });
        bounds.extend(latlng);
        // Adding a click event to the marker
        google.maps.event.addListener(marker, 'spider_click', function() { // 'click' => normal, 'spider_click' => Overlapping Marker Spiderfier
            var marker_url = map_options.map_ajax_url;
            is_zooming = true;
            jQuery("#" + map_canvas).goMap();
            var preview_query_str = '';
            if (item.post_preview) {
                preview_query_str = '&post_preview=' + item.post_preview;
            }
            marker_url = marker_url + '' + item.m;
            if (map_options.bubble_size) {
                marker_url += '?small=1';
            }
            var loading = '<div id="map_loading"></div>';
            gd_infowindow.open(jQuery.goMap.map, marker);
            gd_infowindow.setContent(loading);
            jQuery.ajax({
                type: "GET",
                url: marker_url,
                cache: false,
                dataType: "json",
                error: function(xhr, error) {
                    alert(error);
                },
                success: function(response) {
                    jQuery("#" + map_canvas).goMap();
                    response = geodir_htmlEscape(response);
                    gd_infowindow.setContent(response);
                    gd_infowindow.open(jQuery.goMap.map, marker);
                    geodir_fix_marker_pos(map_canvas);
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
    jQuery('#' + map_canvas_var + ' .geodir-bubble_desc').attr('style', 'height:' + org_height + 'px !important');
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
            jQuery('html,body').animate({ scrollTop: mTag }, 'slow');
        }
    }
    try {
        if (window.gdMaps == 'google') {
            google.maps.event.trigger(jQuery.goMap.mapId.data(id), 'spider_click');
        } else if(window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id){
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
                jQuery.goMap.mapId.data(id).setAnimation(google.maps.Animation.BOUNCE);
            }
        } else if(window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id){
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
        } else if(window.gdMaps == 'osm') {
            jQuery.goMap.gdlayers.eachLayer(function(marker) {
                if (id && marker.options.id == id){
                    jQuery(marker._icon).removeClass('gd-osm-marker-bounce');
                }
            });
        }
    } catch (e) {
        console.log(e.message);
    }
}
// Listing map sticky script //
function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i].trim();
        if (c.indexOf(name) == 0) return c.substring(name.length, c.length);
    }
    return "";
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toGMTString();
    document.cookie = cname + "=" + cvalue + "; " + expires + ";Path=/";
}

function map_sticky(map_options) {
    if (!window.gdMaps) {
        return;
    }
    
    var optionsname = map_options;
    map_options = eval(map_options);
    
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
            jQuery("#" + optionsname + "").goMap();
            
            // get the bounds of the map
            if (window.gdMaps == 'osm') {
                bounds = new L.LatLngBounds([]);
            } else {
                bounds =  new google.maps.LatLngBounds();
            }
            
            var wheight = jQuery(window).height();
            
            if (jQuery(window).scrollTop() >= catcher.offset().top) {
                if (!sticky.hasClass('stickymap')) {
                    sticky.addClass('stickymap');
                    sticky.hide();
                    sticky.appendTo('body');
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
                    var cstatus = getCookie('geodir_stickystatus');
                    if (cstatus != 'shide') {
                        sticky.show('slow');
                        sticky_show_hide_trigger.removeClass('triggeron_sticky');
                        sticky_show_hide_trigger.addClass('triggeroff_sticky');
                    } else {
                        sticky_show_hide_trigger.removeClass('triggeroff_sticky');
                        sticky_show_hide_trigger.addClass('triggeron_sticky');
                    }
                }
                sticky_show_hide_trigger.css({
                    'top': '25%',
                    'width': '1%',
                    'padding-right': '3px',
                    'padding-left': '0px'
                });
                sticky_show_hide_trigger.css({
                    'position': 'fixed',
                    'right': '0'
                });
                sticky_show_hide_trigger.show();
            }
            
            if (jQuery(window).scrollTop() < catcher.offset().top) {
                if (sticky.hasClass('stickymap')) {
                    sticky.appendTo(map_parent);
                    sticky.hide();
                    sticky.removeClass('stickymap');
                    sticky.css({
                        'position': 'relative',
                        'border': 'none'
                    });
                    sticky.css({
                        'top': '0',
                        'width': widthmap
                    });
                    sticky.fadeIn('slow');
                    catcher.css({
                        'height': '0'
                    });
                    sticky_show_hide_trigger.removeClass('triggeroff_sticky');
                    sticky_show_hide_trigger.addClass('triggeron_sticky');
                }
                sticky_show_hide_trigger.hide();
            }
        });
        jQuery(window).resize(function() {
            jQuery(window).scroll();
        });
    } // sticky if end
}
var rendererOptions = { draggable: true };
var directionsDisplay = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.DirectionsRenderer(rendererOptions) : {};
var directionsService = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.DirectionsService() : {};

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
            control.addTo(jQuery.goMap.map);
            
            L.Routing.errorControl(control).addTo(jQuery.goMap.map);
            
            jQuery('.leaflet-routing-geocoders .leaflet-routing-search-info').append('<span title="' + geodir_params.geoMyLocation + '" onclick="gdMyGeoDirection(' + map_canvas + ');" id="detail_page_map_canvas_mylocation" class="gd-map-mylocation"><i class="fa fa-crosshairs" aria-hidden="true"></i></span>');
        } catch(e) {
            console.log(e.message);
        }
    } else if (window.gdMaps == 'google') {
        // Direction map
        directionsDisplay.setMap(jQuery.goMap.map);
        directionsDisplay.setPanel(document.getElementById(map_canvas + "_directionsPanel"));
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
                directionsDisplay.setDirections(response);
                //map = new google.maps.Map(document.getElementById(map_canvas), map_options);
                //directionsDisplay.setMap(map);
            } else {
                alert(geodir_params.address_not_found_on_map_msg + from_address);
            }
        });
    }
}

function gdGetTravelMode($wrap) {
    var mode = jQuery('#travel-mode').val();
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
    var mode = jQuery('#travel-units').val();
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
    $(window).resize(function() {
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
    options.navigationControlOptions = { position: 'topleft' };
    
    // Create map
    jQuery("#" + map_canvas).goMap(options);

    var styles = [{
        featureType: "poi.business",
        elementType: "labels",
        stylers: [
            { visibility: "off" }
        ]
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
                if (is_zooming) {
                } else {
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
        L.DomEvent.addListener(maxMap, 'click', gdMaxMapOSM);
    }

    function gdMaxMapOSM() {
        jQuery('#' + map_canvas).toggleClass('map-fullscreen');
        jQuery('.' + map_canvas + '_map_category').toggleClass('map_category_fullscreen');
        jQuery('#' + map_canvas + '_trigger').toggleClass('map_category_fullscreen');
        jQuery('body').toggleClass('body_fullscreen');
        jQuery('#' + map_canvas + '_loading_div').toggleClass('loading_div_fullscreen');
        jQuery('#' + map_canvas + '_map_nofound').toggleClass('nofound_fullscreen');
        jQuery('#' + map_canvas + '_triggermap').toggleClass('triggermap_fullscreen');
        jQuery('.trigger').toggleClass('triggermap_fullscreen');
        jQuery('.map-places-listing').toggleClass('triggermap_fullscreen');
        jQuery('.' + map_canvas + '_TopLeft').toggleClass('TopLeft_fullscreen');
        jQuery('#' + map_canvas + '_triggermap').closest('.geodir_map_container').toggleClass('geodir_map_container_fullscreen');
        
        window.setTimeout(function() {
            setGeodirMapSize(true);
            jQuery.goMap.map._onResize();
            jQuery.goMap.map.invalidateSize();
        }, 100);
    }
    
    // Overlapping Marker Spiderfier LeafLet
    
    jQuery.goMap.oms.addListener('spiderfy', function(markers) {
        jQuery.goMap.map.closePopup();
    });
    
    window.oms = jQuery.goMap.oms;
}

function parse_marker_jason_osm(data, map_canvas_var) {    
    if (jQuery('#' + map_canvas_var).val() == '') { // if map not loaded then load it
        initMapOSM(map_canvas_var);
    } else {
        jQuery("#" + map_canvas_var).goMap();
    }
    // get the bounds of the map
    bounds = new L.LatLngBounds([]);
    
    // clear old markers
    jQuery.goMap.clearMarkers();
    
    //json evaluate returned data
    jsonData = jQuery.parseJSON(data);
    
    // if no markers found, display home_map_nofound div with no search criteria met message
    if (jsonData[0].totalcount <= 0) {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'block';
        var mapcenter = new L.latLng(eval(map_canvas_var).latitude, eval(map_canvas_var).longitude);
        
        list_markers(jsonData, map_canvas_var);
        
        if (eval(map_canvas_var).enable_marker_cluster_no_reposition) {
        } //dont reposition after load
        else {
            jQuery.goMap.map.setView(mapcenter, eval(map_canvas_var).zoom);
        }
    } else {
        document.getElementById(map_canvas_var + '_map_nofound').style.display = 'none';
        
        if (map_canvas_var === 'detail_page_map_canvas') {
            jsonData[0].totalcount = 1;
            jsonData = [jsonData[0]];
        }

        list_markers(jsonData, map_canvas_var);
        
        var center = bounds.getCenter();
        if (eval(map_canvas_var).autozoom && parseInt(jsonData[0].totalcount) > 1) {
            if (eval(map_canvas_var).enable_marker_cluster_no_reposition) {
                //dont reposition after load
            } else {
                jQuery.goMap.map.fitBounds(bounds);
            }
        } else {
            if (eval(map_canvas_var).enable_marker_cluster_no_reposition) {
                //dont reposition after load
            } else {
                jQuery.goMap.map.setView(center, jQuery.goMap.map.getZoom());
            }
        }

        if (jQuery.goMap.map.getZoom() > parseInt(eval(map_canvas_var).maxZoom)) {
            jQuery.goMap.map.setZoom(parseInt(eval(map_canvas_var).maxZoom));
        }
    }
    
    jQuery('#' + map_canvas_var + '_loading_div').hide();
    jQuery("body").trigger("map_show", map_canvas_var);
}

function create_marker_osm(input, map_canvas_var) {
    jQuery("#" + map_canvas_var).goMap();
    
    if (input.lt && input.ln) {
        var coord = new L.latLng(input.lt, input.ln);
        var marker_id = 0;
        if (eval(map_canvas_var).enable_cat_filters)
            marker_id = input.mk_id
        else
            marker_id = input.id
        var title = geodir_htmlEscape(input.t);

        if (!input.i) {
            input.i = geodir_params.default_marker_icon;
            input.w = geodir_params.default_marker_w;
            input.h = geodir_params.default_marker_h;
        }
        
        cs = input.cs;
        
        var marker = jQuery.goMap.createMarker({
            id: marker_id,
            title: title,
            position: coord,
            visible: true,
            clickable: true,
            icon: input.i,
            label: cs,
            w: input.w,
            h: input.h,
            clustered: (parseInt(eval(map_canvas_var).marker_cluster) === 1) && typeof input.cs !== 'undefined' ? true : false
        });
        
        if ((parseInt(eval(map_canvas_var).marker_cluster) === 1) && cs) {
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
            if (marker.options.clustered) {
                jQuery("#" + map_canvas_var).goMap();
                
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
                    
                    if (jQuery.goMap.map.getZoom() > parseInt(eval(map_canvas_var).maxZoom)) {
                        jQuery.goMap.map.setZoom(parseInt(eval(map_canvas_var).maxZoom));
                    }
                } else {
                    zoom = parseInt(jQuery.goMap.map.getZoom()) + 1 > parseInt(eval(map_canvas_var).maxZoom) && parseInt(eval(map_canvas_var).maxZoom) > 0 ? parseInt(eval(map_canvas_var).maxZoom) : parseInt(jQuery.goMap.map.getZoom()) + 1;
                    jQuery.goMap.map.setView(marker.getLatLng(), zoom);
                }
                return;
            } else {
                is_zooming = true;
                jQuery("#" + map_canvas_var).goMap();
            }
            
            var preview_query_str = '';
            
            if (input.post_preview) {
                preview_query_str = '&post_preview=' + input.post_preview;
            }
            
            if (eval(map_canvas_var).bubble_size) {
                var marker_url = eval(map_canvas_var).ajax_url + "&geodir_ajax=map_ajax&ajax_action=info&m_id=" + input.id + "&small=1" + preview_query_str;
            } else {
                var marker_url = eval(map_canvas_var).ajax_url + "&geodir_ajax=map_ajax&ajax_action=info&m_id=" + input.id + preview_query_str;
            }
            var loading = '<div id="map_loading"></div>';
            var maxH = jQuery("#" + map_canvas_var).height();
            maxH -= ( maxH * 0.10) + jQuery(marker._icon).outerHeight() + 20;
            marker.closePopup().unbindPopup().bindPopup(loading, {className: 'gd-osm-bubble', maxHeight: maxH}).openPopup();
            
            jQuery.ajax({
                type: "GET",
                url: marker_url,
                cache: false,
                dataType: "html",
                error: function(xhr, error) {
                    alert(error);
                },
                success: function(response) {
                    jQuery("#" + map_canvas_var).goMap();
                    response = geodir_htmlEscape(response);
                    marker.bindPopup(response);
                    geodir_fix_marker_pos(map_canvas_var);
                    // give the map 1 second to reposition before allowing it to reload
                    setTimeout(function() { is_zooming = false; }, 1000);
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
	if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(gdMyGeoPositionSuccess, gdMyGeoPositionError);
    } else {
        gdMyGeoPositionError(-1);
    }
}

function gdMyGeoPositionError(err) {
    var msg;
    switch (err.code) {
        case err.UNKNOWN_ERROR:
            msg = geodir_params.geoErrUNKNOWN_ERROR;
            break;
        case err.PERMISSION_DENINED:
            msg = geodir_params.geoErrPERMISSION_DENINED;
            break;
        case err.POSITION_UNAVAILABLE:
            msg = geodir_params.geoErrPOSITION_UNAVAILABLE;
            break;
        case err.BREAK:
            msg = geodir_params.geoErrBREAK;
            break;
        default:
            msg = geodir_params.geoErrDEFAULT;
    }
    alert(msg);
}

function gdMyGeoPositionSuccess(position) {
    var coords = position.coords || position.coordinate || position;
    if (coords && coords.latitude && coords.longitude) {
        var myLat = coords.latitude,
            myLng = coords.longitude;
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
		jQuery('#' + map_canvas + '_fromAddress').hide();
		jQuery('.gd-get-directions').hide();
		jQuery('.' + map_canvas + '_getdirection').hide();

		if (window.gdMaps == 'osm') {
			window.setTimeout(function() {
				geodirFindRoute(map_canvas);
			}, 1000);
		}
	}
}
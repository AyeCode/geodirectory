/**
 * jQuery goMap
 *
 * @url        http://www.pittss.lv/jquery/gomap/
 * @author    Jevgenijs Shtrauss <pittss@gmail.com>
 * @version    1.3.2 2011.07.01
 * This software is released under the MIT License <http://www.opensource.org/licenses/mit-license.php>
 */
function geodirGoMapInit() {
(function ($) {
    if ((window.gdSetMap=='google' || window.gdSetMap=='auto') && window.google && typeof google.maps!=='undefined') {
        gdMaps = 'google';
    } else if ((window.gdSetMap=='osm' || window.gdSetMap=='auto') && typeof L!=='undefined' && typeof L.version!=='undefined') {
        gdMaps = 'osm';
    } else {
        gdMaps = null;
    }
    window.gdMaps = window.gdMaps || gdMaps;

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
    console.log(window.gdSetMap);
    console.log(gdMaps);

    var geocoder = window.gdMaps == 'google' ? new google.maps.Geocoder() : {};

    function MyOverlay(map, $this) {
        if (typeof $this !== 'undefined') {
            $this.setMap(map);
        } else {
            this.setMap(map);
        }
    };
    MyOverlay.prototype = window.gdMaps == 'google' ? new google.maps.OverlayView() : {};
    MyOverlay.prototype.onAdd = function () {
    };
    MyOverlay.prototype.onRemove = function () {
    };
    MyOverlay.prototype.draw = function () {
    };

    $.goMap = {};

    $.fn.goMap = function (options) {
        return this.each(function () {
            var goMap = $(this).data('goMap');
            if (!goMap && window.gdMaps == 'google') {
                var goMapBase = $.extend(true, {}, $.goMapBase);
                $(this).data('goMap', goMapBase.init(this, options));
                $.goMap = goMapBase;
            } else if (!goMap && window.gdMaps == 'osm') {
                var osmMapBase = $.extend(true, {}, $.osmMapBase);
                $(this).data('goMap', osmMapBase.init(this, options));
                $.goMap = osmMapBase;
            } else {
                $.goMap = goMap;
            }
        });
    };

    $.osmMapBase = {
        defaults: {
            address: '', // Street, City, Country
            latitude: 56.9,
            longitude: 24.1,
            zoom: 4,
            delay: 200,
            hideByClick: true,
            oneInfoWindow: true,
            prefixId: 'gomarker',
            polyId: 'gopoly',
            groupId: 'gogroup',
            navigationControl: true, // Show or hide navigation control
            navigationControlOptions: {
                position: 'topleft', // TOP, TOP_LEFT, TOP_RIGHT, BOTTOM, BOTTOM_LEFT, BOTTOM_RIGHT, LEFT, RIGHT
                style: 'DEFAULT' 	// DEFAULT, ANDROID, SMALL, ZOOM_PAN
            },
            mapTypeControl: true, // Show or hide map control
            mapTypeControlOptions: {
                position: 'topright', // TOP, TOP_LEFT, TOP_RIGHT, BOTTOM, BOTTOM_LEFT, BOTTOM_RIGHT, LEFT, RIGHT
                style: 'DEFAULT'// DEFAULT, DROPDOWN_MENU, HORIZONTAL_BAR
            },
            zoomControl: true,
            zoomControlOptions: {
                position: 'topleft'
            },
            scaleControl: false, // Show or hide scale
            scrollwheel: true, // Mouse scroll whell
            fullscreenControl: false, // disable fullscreen button as we add our own
            directions: false,
            directionsResult: null,
            disableDoubleClickZoom: false,
            touchZoom: '*', // Boolean|String. Default: *. Whether the map can be zoomed by touch-dragging with two fingers.
            streetViewControl: true,
            streetViewControlOptions: {
                position: 'bottomleft'
            },
            markers: [],
            overlays: [],
            polyline: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2
            },
            polygon: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            circle: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            rectangle: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            maptype: 'ROADMAP', // Map type - HYBRID, ROADMAP, SATELLITE, TERRAIN
            html_prepend: '<div class=gomapMarker>',
            html_append: '</div>',
            addMarker: false,
            osmBaseLayer: null,
            osmOverlays: [],
        },
        map: null,
        count: 0,
        markers: [],
        polylines: [],
        polygons: [],
        circles: [],
        rectangles: [],
        tmpMarkers: [],
        geoMarkers: [],
        lockGeocode: false,
        bounds: null,
        overlays: null,
        overlay: null,
        mapId: null,
        plId: null,
        pgId: null,
        cId: null,
        rId: null,
        opts: null,
        centerLatLng: null,

        init: function (el, options) {
            var opts = $.extend(true, {}, $.osmMapBase.defaults, options), baseLayer, customMap;
            this.mapId = $(el);
            this.opts = opts;

            if (opts.address)
                this.geocode({address: opts.address, center: true});
            else if ($.isArray(opts.markers) && opts.markers.length > 0) {
                if (opts.markers[0].address)
                    this.geocode({address: opts.markers[0].address, center: true});
                else
                    this.centerLatLng = L.latLng(opts.markers[0].latitude, opts.markers[0].longitude);
            }
            else
                this.centerLatLng = L.latLng(opts.latitude, opts.longitude);

            var myOptions = {
                center: this.centerLatLng,
                zoom: parseInt(opts.zoom),
                minZoom: opts.minZoom ? opts.minZoom : 1,
                maxZoom: opts.maxZoom > 18 ? 18 : opts.maxZoom,
                zoomControl: opts.zoomControl === "0" || !opts.zoomControl ? false : true,
                touchZoom: opts.touchZoom,
                doubleClickZoom: opts.disableDoubleClickZoom === "0" || !opts.disableDoubleClickZoom ? true : false,
                dragging: typeof opts.dragging !== 'undefined' ? opts.dragging : !L.Browser.mobile,
                tap: typeof opts.dragging !== 'undefined' ? opts.dragging : !L.Browser.mobile,
                worldCopyJump: true,
                scrollWheelZoom: opts.scrollwheel === "0" || !opts.scrollwheel ? false : opts.scrollwheel,
                attributionControl: typeof opts.attributionControl !== 'undefined' ? opts.attributionControl : true,
                defaultBaseLayer: typeof opts.osmBaseLayer !== 'undefined' && opts.osmBaseLayer ? opts.osmBaseLayer : null,
                defaultOverlays: typeof opts.osmOverlays !== 'undefined' && opts.osmOverlays ? opts.osmOverlays : [],
            }

            if (myOptions.defaultBaseLayer) {
                try {
                    baseLayer = L.tileLayer.provider(myOptions.defaultBaseLayer);
                    var apikey = typeof opts.osmApiKey !== 'undefined' && opts.osmApiKey ? opts.osmApiKey : '';
                    if(apikey){
                        baseLayer._url += "?apikey=5e8a41b90fc240c09f61eb6bbbfb8395";
                    }
                    customMap = true;
                } catch(e) {
                    console.log(e);
                }
            }

            if (!baseLayer) {
                var osmUrl = '//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                osmAttrib = 'Map data &copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                baseLayer = L.tileLayer(osmUrl, {maxZoom: opts.maxZoom, attribution: osmAttrib});
            }

            this.map = new L.Map(el, myOptions).addLayer(baseLayer);

            if (customMap && myOptions.defaultOverlays && myOptions.defaultOverlays.length > 0) {
                for (var i in myOptions.defaultOverlays) {
                    if (myOptions.defaultOverlays[i]) {
                        try {
                            L.tileLayer.provider(myOptions.defaultOverlays[i]).addTo(this.map);
                        } catch(e) {
                            console.log(e);
                        }
                    }
                }
            }

            if (myOptions.zoomControl && (zoomPosition = this.parsePosition(opts.zoomControlOptions.position, 'topleft')) !== 'topleft') {
                this.map.zoomControl.setPosition(zoomPosition);
            }

            if (parseInt(options.marker_cluster) === 1 && !options.marker_cluster_server) {
                jQuery('#gdOSMprogress').remove();
                jQuery('#gdOSMprogressBar').remove();
                jQuery(el).before('<div id="gdOSMprogress"><div id="gdOSMprogressBar"></div></div>');

                function gdOSMUpdateProgressBar(processed, total, elapsed, layersArray) {
                    processed = Math.min(processed, total);
                    if (elapsed > 1000) {
                        // if it takes more than a second to load, display the progress bar:
                        jQuery('#gdOSMprogress').show();
                        jQuery('#gdOSMprogressBar').width(Math.round(processed/total * 100) + '%');
                    }

                    if (processed === total) {
                        // all markers processed - hide the progress bar:
                        jQuery('#gdOSMprogress').hide();
                    }
                }
                this.gdlayers = new L.markerClusterGroup({spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true, chunkedLoading: true, chunkProgress: gdOSMUpdateProgressBar});
            } else {
                this.gdlayers = new L.featureGroup([]);
            }

            // Overlapping Marker Spiderfier LeafLet
			try {
				this.oms = new OverlappingMarkerSpiderfier(this.map);
			} catch(e){
			}

            for (var j = 0, l = opts.markers.length; j < l; j++)
                this.createMarker(opts.markers[j]);

            var goMap = this;

            if (opts.addMarker == true || opts.addMarker == 'multi') {
                L.DomEvent.addListener(goMap.map, 'click', function (event) {
                    var options = {
                        position: event.latLng,
                        draggable: true
                    };

                    var marker = goMap.createMarker(options);

                    L.DomEvent.addListener(marker, 'dblclick', function (event) {
                        marker.setMap(null);
                        goMap.removeMarker(marker.id);
                    });

                });
            }
            else if (opts.addMarker == 'single') {
                L.DomEvent.addListener(goMap.map, 'click', function (event) {
                    if (!goMap.singleMarker) {
                        var options = {
                            position: event.latLng,
                            draggable: true
                        };

                        var marker = goMap.createMarker(options);
                        goMap.singleMarker = true;

                        L.DomEvent.addListener(marker, 'dblclick', function (event) {
                            marker.setMap(null);
                            goMap.removeMarker(marker.id);
                            goMap.singleMarker = false;
                        });
                    }
                });
            }

            delete opts.markers;
            delete opts.overlays;

            return this;
        },

        ready: function (f) {
            this.map.addOneTimeEventListener('moveend', function () {
                return f();
            });
        },

        Geocoder: function (options) {
            return new L.Control.gdGeoCode({
                provider: new L.gdGeoCode.Provider.OpenStreetMap()
            });
        },

        geocode: function (address, options) {
            var gdcoder = new L.Control.gdGeoCode({ provider: new L.gdGeoCode.Provider.OpenStreetMap() });
            var results = gdcoder.geosearch(address);
        },

        parsePosition: function (position, $default) {
            var parsed = position;
            if (position && typeof position == 'string') {
                switch(position.toUpperCase()) {
                    case 'TOP':
                    case 'LEFT':
                    case 'TOP_LEFT':
                    case 'topleft':
                        parsed = 'topleft';
                    break;
                    case 'RIGHT':
                    case 'TOP_RIGHT':
                    case 'topright':
                        parsed = 'topright';
                    break;
                    case 'BOTTOM':
                    case 'BOTTOM_LEFT':
                    case 'bottomleft':
                        parsed = 'bottomleft';
                    break;
                    case 'BOTTOM_RIGHT':
                    case 'bottomright':
                        parsed = 'bottomright';
                    break;
                    default:
                        parsed = typeof $default !== 'undefined' ? $default : parsed;
                    break;
                }
            } else if (typeof $default !== 'undefined') {
                parsed = $default;
            }

            return parsed;
        },

        geoMarker: function () {
            if (this.geoMarkers.length > 0 && !this.lockGeocode) {
                this.lockGeocode = true;
                var current = this.geoMarkers.splice(0, 1);
                this.geocode({address: current[0].address}, current[0]);
            }
            else if (this.lockGeocode) {
                var goMap = this;
                setTimeout(function () {
                    goMap.geoMarker();
                }, this.opts.delay);
            }
        },

        setMap: function (options) {
            delete options.mapTypeId;

            if (options.address) {
                this.geocode({address: options.address, center: true});
                delete options.address;
            }
            else if (options.latitude && options.longitude) {
                options.center = L.latLng(options.latitude, options.longitude);
                delete options.longitude;
                delete options.latitude;
            }

            if (options.mapTypeControlOptions && options.mapTypeControlOptions.position)
                options.mapTypeControlOptions.position = options.mapTypeControlOptions.position.toLowerCase();

            if (options.navigationControlOptions && options.navigationControlOptions.position)
                options.navigationControlOptions.position = options.navigationControlOptions.position.toLowerCase();

            L.Util.setOptions(this.map, options);
        },

        getMap: function () {
            return this.map;
        },

        setInfoWindow: function (marker, html) {
            var goMap = this;
            html.content = goMap.opts.html_prepend + html.content + goMap.opts.html_append;
            var infowindow = marker.bindPopup(html);
            infowindow.show = false;

            $(goMap.mapId).data(marker.id + 'info', infowindow);

            if (html.popup) {
                goMap.openWindow(infowindow, marker, html);
                infowindow.show = true;
            }

            L.DomEvent.addListener(marker, 'click', function () {
                if (infowindow.show && goMap.opts.hideByClick) {
                    infowindow.close();
                    infowindow.show = false;
                }
                else {
                    goMap.openWindow(infowindow, marker, html);
                    infowindow.show = true;
                }
            });
        },

        openWindow: function (infowindow, marker, html) {
            if (this.opts.oneInfoWindow)
                this.clearInfo();

            if (html.ajax) {
                infowindow.openPopup();
                $.ajax({
                    url: html.ajax,
                    success: function (html) {
                        infowindow.setContent(html);
                    }
                });
            } else if (html.id) {
                infowindow.setContent($(html.id).html());
                infowindow.openPopup();
            } else {
                infowindow.openPopup();
            }
        },

        setInfo: function (id, text) {
            var info = $(this.mapId).data(id + 'info');

            if (typeof text == 'object')
                info.setOptions(text);
            else
                info.setContent(text);
        },

        getInfo: function (id, hideDiv) {
            var info = $(this.mapId).data(id + 'info').getContent();
            if (hideDiv)
                return $(info).html();
            else
                return info;
        },

        clearInfo: function () {
            for (var i = 0, l = this.markers.length; i < l; i++) {
                var info = $(this.mapId).data(this.markers[i] + 'info');
                if (info) {
                    info.popupclose();
                    info.show = false;
                }
            }
        },

        fitBounds: function (type, markers) {
            var goMap = this;
            if (this.getMarkerCount() != this.getTmpMarkerCount())
                setTimeout(function () {
                    goMap.fitBounds(type, markers);
                }, this.opts.delay);
            else {
                this.bounds = new L.LatLngBounds([]);

                if (!type || (type && type == 'all')) {
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        this.bounds.extend($(this.mapId).data(this.markers[i]).position);
                    }
                }
                else if (type && type == 'visible') {
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        if (this.getVisibleMarker(this.markers[i]))
                            this.bounds.extend($(this.mapId).data(this.markers[i]).position);
                    }

                }
                else if (type && type == 'markers' && $.isArray(markers)) {
                    for (var i = 0, l = markers.length; i < l; i++) {
                        this.bounds.extend($(this.mapId).data(markers[i]).position);
                    }
                }
                this.map.fitBounds(this.bounds);
            }
        },

        getBounds: function () {
            return this.map.getBounds();
        },

        getGDMarkers: function () {
            return this.gdlayers;
        },

        getMarkerCount: function () {
            return this.markers.length;
        },

        getTmpMarkerCount: function () {
            return this.tmpMarkers.length;
        },

        getMarkers: function (type, name) {
            var array = [];
            switch (type) {
                case "markers":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = $(this.mapId).data(this.markers[i]);
                        array.push(temp);
                    }
                    break;
                default:
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = $(this.mapId).data(this.markers[i]).getPosition().toUrlValue();
                        array.push(temp);
                    }
                    break;
            }
            return array;
        },

        createMarker: function (marker) {
            if (!marker.geocode) {
                this.count++;
                if (!marker.id)
                    marker.id = this.opts.prefixId + this.count;
                this.tmpMarkers.push(marker.id);
            }
            if (marker.address && !marker.geocode) {
                this.geoMarkers.push(marker);
                this.geoMarker();
            } else if (marker.latitude && marker.longitude || marker.position) {
                var options = {map: this.map};
                options.id = marker.id;
                options.group = marker.group ? marker.group : this.opts.groupId;
                options.zIndex = marker.zIndex ? marker.zIndex : 0;
                options.zIndexOrg = marker.zIndexOrg ? marker.zIndexOrg : 0;

                if (marker.visible == false)
                    options.visible = marker.visible;

                if (marker.title)
                    options.title = marker.title;

                if (marker.label)
                    options.label = marker.label;

                if (marker.draggable)
                    options.draggable = marker.draggable;

                if (marker.icon && marker.icon.image) {
                    options.icon = marker.icon.image;
                    if (marker.icon.shadow)
                        options.shadow = marker.icon.shadow;
                }
                else if (marker.icon)
                    options.icon = marker.icon;

                else if (this.opts.icon && this.opts.icon.image) {
                    options.icon = this.opts.icon.image;
                    if (this.opts.icon.shadow)
                        options.shadow = this.opts.icon.shadow;
                }
                else if (this.opts.icon)
                    options.icon = this.opts.icon;

                if (options.icon) {
                    iconOptions = {
                        iconUrl: options.icon,
                        iconSize: null,
                        iconAnchor: [17, 0],
                        popupAnchor: null,
                    }
                    if (marker.iconSize) {
                        iconOptions.iconSize = marker.iconSize;
                    }
                    if (marker.iconAnchor) {
                        iconOptions.iconAnchor = marker.iconAnchor;
                    }
                    if (marker.popupAnchor) {
                        iconOptions.popupAnchor = marker.popupAnchor;
                    }
                    if (marker.iconRetinaUrl) {
                        iconOptions.iconRetinaUrl = marker.iconRetinaUrl;
                    }
                    if (marker.shadowUrl) {
                        iconOptions.shadowUrl = marker.shadowUrl;
                    }
                    if (marker.shadowRetinaUrl) {
                        iconOptions.shadowRetinaUrl = marker.shadowRetinaUrl;
                    }
                    if (marker.shadowSize) {
                        iconOptions.shadowSize = marker.shadowSize;
                    }
                    if (marker.shadowAnchor) {
                        iconOptions.shadowAnchor = marker.shadowAnchor;
                    }
                    if (marker.className) {
                        iconOptions.className = marker.className;
                    }

                    if (marker.clustered) {
                        options.clustered = true;
                        var c = 'marker-cluster marker-cluster-';

                        if (marker.title < 10) {
                            c += 'small';
                        } else if (marker.title < 100) {
                            c += 'medium';
                        } else {
                            c += 'large';
                        }
                        iconOptions.className = c;
                        iconOptions.html = '<div><span>' + marker.title + '</span></div>';
                        marker.w = 40;
                        marker.h = 40;
                    }

                    if (!iconOptions.iconSize && marker.w && marker.h && parseInt(marker.w) > 0 && parseInt(marker.h) > 0) {
                        var w = parseFloat(marker.w);
                        var h = parseFloat(marker.h);
                        iconOptions.iconSize = [w, h];
                        iconOptions.iconAnchor = [((w / 2) - 1), h];
                        iconOptions.popupAnchor = [0, (h * -1) + (h * 0.05)];
                    }

                    if (marker.clustered) {
                        options.icon = new L.DivIcon(iconOptions);
                    } else {
                        options.icon = L.icon(iconOptions);
                    }

                } else {
                    options.icon = new L.Icon.Default();
                }

                options.position = marker.position ? marker.position : L.latLng(marker.latitude, marker.longitude);

                var cmarker = new L.Marker(options.position, options);

                if (marker.html && !marker.clustered) {
                    if (!marker.html.content && !marker.html.ajax && !marker.html.id)
                        marker.html = {content: marker.html};
                    else if (!marker.html.content)
                        marker.html.content = null;

                    this.setInfoWindow(cmarker, marker.html);
                }
                if (marker.addToMap) {
                    cmarker.addTo(this.map);
                    return cmarker;
                }
                this.addMarker(cmarker);
                return cmarker;
            }
        },

        addMarker: function (marker) {
            this.gdlayers.addLayer(marker);
        },

        removeMarker: function (marker) {
            if (marker== 'map_me') {
                if (jQuery.goMap.gdUmarker) {
                    this.map.removeLayer(jQuery.goMap.gdUmarker);
                    jQuery.goMap.gdUmarker = null;
                }
                return true;
            }

            var index = $.inArray(marker, this.markers), current;
            if (index > -1) {
                this.tmpMarkers.splice(index, 1);
                current = this.markers.splice(index, 1);
                var markerId = current[0];
                var marker = $(this.mapId).data(markerId);
                var info = $(this.mapId).data(markerId + 'info');

                marker.setVisible(false);
                marker.setMap(null);
                $(this.mapId).removeData(markerId);

                if (info) {
                    info.close();
                    info.show = false;
                    $(this.mapId).removeData(markerId + 'info');
                }
                return true;
            }
            return false;
        },

        clearMarkers: function () {
            var allMarkers = this.gdlayers;
            this.gdlayers.eachLayer(function(marker) {
                allMarkers.removeLayer(marker);
            });
            this.gdlayers = allMarkers;

            this.singleMarker = false;
            this.lockGeocode = false;
            this.markers = [];
            this.tmpMarkers = [];
            this.geoMarkers = [];
        },

        isVisible: function (latlng) {
            return this.map.getBounds().contains(latlng);
        }
    }

    $.goMapBase = {
        defaults: {
            address: '', // Street, City, Country
            latitude: 56.9,
            longitude: 24.1,
            zoom: 4,
            delay: 200,
            hideByClick: true,
            oneInfoWindow: true,
            prefixId: 'gomarker',
            polyId: 'gopoly',
            groupId: 'gogroup',
            navigationControl: true, // Show or hide navigation control
            navigationControlOptions: {
                position: 'TOP_LEFT', // TOP, TOP_LEFT, TOP_RIGHT, BOTTOM, BOTTOM_LEFT, BOTTOM_RIGHT, LEFT, RIGHT
                style: 'DEFAULT' 	// DEFAULT, ANDROID, SMALL, ZOOM_PAN
            },
            mapTypeControl: true, // Show or hide map control
            mapTypeControlOptions: {
                position: 'TOP_RIGHT', // TOP, TOP_LEFT, TOP_RIGHT, BOTTOM, BOTTOM_LEFT, BOTTOM_RIGHT, LEFT, RIGHT
                style: 'DEFAULT'// DEFAULT, DROPDOWN_MENU, HORIZONTAL_BAR
            },
            zoomControl: true,
            zoomControlOptions: {
                position: 'LEFT_CENTER'
            },
            scaleControl: false, // Show or hide scale
            scrollwheel: true, // Mouse scroll whell
            fullscreenControl: false, // disable fullscreen button as we add our own
            directions: false,
            directionsResult: null,
            disableDoubleClickZoom: false,
            streetViewControl: true,
            streetViewControlOptions: {
                position: 'LEFT_CENTER'
            },
            markers: [],
            overlays: [],
            polyline: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2
            },
            polygon: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            circle: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            rectangle: {
                color: '#FF0000',
                opacity: 1.0,
                weight: 2,
                fillColor: '#FF0000',
                fillOpacity: 0.2
            },
            maptype: 'ROADMAP', // Map type - HYBRID, ROADMAP, SATELLITE, TERRAIN
            html_prepend: '<div class=gomapMarker>',
            html_append: '</div>',
            addMarker: false
        },
        map: null,
        count: 0,
        markers: [],
        polylines: [],
        polygons: [],
        circles: [],
        rectangles: [],
        tmpMarkers: [],
        geoMarkers: [],
        lockGeocode: false,
        bounds: null,
        overlays: null,
        overlay: null,
        mapId: null,
        plId: null,
        pgId: null,
        cId: null,
        rId: null,
        opts: null,
        centerLatLng: null,

        init: function (el, options) {
            var opts = $.extend(true, {}, $.goMapBase.defaults, options);
            this.mapId = $(el);
            this.opts = opts;

            if (opts.address)
                this.geocode({address: opts.address, center: true});
//			else if (opts.latitude != $.goMapBase.defaults.latitude && opts.longitude != $.goMapBase.defaults.longitude)
//				this.centerLatLng = new google.maps.LatLng(opts.latitude, opts.longitude);
            else if ($.isArray(opts.markers) && opts.markers.length > 0) {
                if (opts.markers[0].address)
                    this.geocode({address: opts.markers[0].address, center: true});
                else
                    this.centerLatLng = new google.maps.LatLng(opts.markers[0].latitude, opts.markers[0].longitude);
            }
            else
                this.centerLatLng = new google.maps.LatLng(opts.latitude, opts.longitude);

            var myOptions = {
                center: this.centerLatLng,
                disableDoubleClickZoom: opts.disableDoubleClickZoom,
                mapTypeControl: opts.mapTypeControl,
                streetViewControl: opts.streetViewControl,
                streetViewControlOptions: {
                    position: google.maps.ControlPosition[opts.streetViewControlOptions.position.toUpperCase()]
                },
                mapTypeControlOptions: {
                    position: google.maps.ControlPosition[opts.mapTypeControlOptions.position.toUpperCase()],
                    style: google.maps.MapTypeControlStyle[opts.mapTypeControlOptions.style.toUpperCase()]
                },
                mapTypeId: google.maps.MapTypeId[opts.maptype.toUpperCase()],
                navigationControl: opts.navigationControl,
                navigationControlOptions: {
                    position: google.maps.ControlPosition[opts.navigationControlOptions.position.toUpperCase()],
                    style: google.maps.NavigationControlStyle[opts.navigationControlOptions.style.toUpperCase()]
                },
                zoomControl: opts.zoomControl === "0" || !opts.zoomControl ? false : true,
                zoomControlOptions: {
                    position: google.maps.ControlPosition[opts.zoomControlOptions.position.toUpperCase()]
                },
                scaleControl: opts.scaleControl,
                scrollwheel: opts.scrollwheel,
                fullscreenControl: opts.fullscreenControl,
                zoom: parseInt(opts.zoom),
                minZoom: parseInt(opts.minZoom),
                maxZoom: parseInt(opts.maxZoom)
            };

            this.map = new google.maps.Map(el, myOptions);
            this.overlay = new MyOverlay(this.map);

            // Overlapping Marker Spiderfier
			try {
				this.oms = new OverlappingMarkerSpiderfier(this.map, {
					markersWontMove: true,   // we promise not to move any markers, allowing optimizations
					markersWontHide: true,   // we promise not to change visibility of any markers, allowing optimizations
					basicFormatEvents: true  // allow the library to skip calculating advanced formatting information
				});
			} catch(e) {
			}

            this.overlays = {
                polyline: {id: 'plId', array: 'polylines', create: 'createPolyline'},
                polygon: {id: 'pgId', array: 'polygons', create: 'createPolygon'},
                circle: {id: 'cId', array: 'circles', create: 'createCircle'},
                rectangle: {id: 'rId', array: 'rectangles', create: 'createRectangle'}
            };

            this.plId = $('<div style="display:none;"/>').appendTo(this.mapId);
            this.pgId = $('<div style="display:none;"/>').appendTo(this.mapId);
            this.cId = $('<div style="display:none;"/>').appendTo(this.mapId);
            this.rId = $('<div style="display:none;"/>').appendTo(this.mapId);

            for (var j = 0, l = opts.markers.length; j < l; j++)
                this.createMarker(opts.markers[j]);

            for (var j = 0, l = opts.overlays.length; j < l; j++)
                this[this.overlays[opts.overlays[j].type].create](opts.overlays[j]);

            var goMap = this;
            if (opts.addMarker == true || opts.addMarker == 'multi') {
                google.maps.event.addListener(goMap.map, 'click', function (event) {
                    var options = {
                        position: event.latLng,
                        draggable: true
                    };

                    var marker = goMap.createMarker(options);

                    google.maps.event.addListener(marker, 'dblclick', function (event) {
                        marker.setMap(null);
                        goMap.removeMarker(marker.id);
                    });

                });
            }
            else if (opts.addMarker == 'single') {
                google.maps.event.addListener(goMap.map, 'click', function (event) {
                    if (!goMap.singleMarker) {
                        var options = {
                            position: event.latLng,
                            draggable: true
                        };

                        var marker = goMap.createMarker(options);
                        goMap.singleMarker = true;

                        google.maps.event.addListener(marker, 'dblclick', function (event) {
                            marker.setMap(null);
                            goMap.removeMarker(marker.id);
                            goMap.singleMarker = false;
                        });
                    }
                });
            }
            delete opts.markers;
            delete opts.overlays;

            return this;
        },

        ready: function (f) {
            google.maps.event.addListenerOnce(this.map, 'bounds_changed', function () {
                return f();
            });
        },

        geocode: function (address, options) {
            var goMap = this;
            setTimeout(function () {
                geocoder.geocode({'address': address.address}, function (results, status) {
                    if (status == google.maps.GeocoderStatus.OK && address.center)
                        goMap.map.setCenter(results[0].geometry.location);

                    if (status == google.maps.GeocoderStatus.OK && options && options.markerId)
                        options.markerId.setPosition(results[0].geometry.location);

                    else if (status == google.maps.GeocoderStatus.OK && options) {
                        if (goMap.lockGeocode) {
                            goMap.lockGeocode = false;
                            options.position = results[0].geometry.location;
                            options.geocode = true;
                            goMap.createMarker(options);
                        }
                    }
                    else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
                        goMap.geocode(address, options);
                    }
                });
            }, this.opts.delay);
        },

        geoMarker: function () {
            if (this.geoMarkers.length > 0 && !this.lockGeocode) {
                this.lockGeocode = true;
                var current = this.geoMarkers.splice(0, 1);
                this.geocode({address: current[0].address}, current[0]);
            }
            else if (this.lockGeocode) {
                var goMap = this;
                setTimeout(function () {
                    goMap.geoMarker();
                }, this.opts.delay);
            }
        },

        setMap: function (options) {
            delete options.mapTypeId;

            if (options.address) {
                this.geocode({address: options.address, center: true});
                delete options.address;
            }
            else if (options.latitude && options.longitude) {
                options.center = new google.maps.LatLng(options.latitude, options.longitude);
                delete options.longitude;
                delete options.latitude;
            }

            if (options.mapTypeControlOptions && options.mapTypeControlOptions.position)
                options.mapTypeControlOptions.position = google.maps.ControlPosition[options.mapTypeControlOptions.position.toUpperCase()];

            if (options.mapTypeControlOptions && options.mapTypeControlOptions.style)
                options.mapTypeControlOptions.style = google.maps.MapTypeControlStyle[options.mapTypeControlOptions.style.toUpperCase()];

            if (options.navigationControlOptions && options.navigationControlOptions.position)
                options.navigationControlOptions.position = google.maps.ControlPosition[options.navigationControlOptions.position.toUpperCase()];

            if (options.navigationControlOptions && options.navigationControlOptions.style)
                options.navigationControlOptions.style = google.maps.NavigationControlStyle[options.navigationControlOptions.style.toUpperCase()];

            this.map.setOptions(options);
        },

        getMap: function () {
            return this.map;
        },

        createListener: function (type, event, data) {
            var target;

            if (typeof type != 'object')
                type = {type: type};

            if (type.type == 'map')
                target = this.map;
            else if (type.type == 'marker' && type.marker)
                target = $(this.mapId).data(type.marker);
            else if (type.type == 'info' && type.marker)
                target = $(this.mapId).data(type.marker + 'info');

            if (target)
                return google.maps.event.addListener(target, event, data);
            else if ((type.type == 'marker' || type.type == 'info') && this.getMarkerCount() != this.getTmpMarkerCount())
                var goMap = this;
            setTimeout(function () {
                goMap.createListener(type, event, data);
            }, this.opts.delay);
        },

        removeListener: function (listener) {
            google.maps.event.removeListener(listener);
        },

        setInfoWindow: function (marker, html) {
            var goMap = this;
            html.content = goMap.opts.html_prepend + html.content + goMap.opts.html_append;
            var infowindow = new google.maps.InfoWindow(html);
            infowindow.show = false;

            $(goMap.mapId).data(marker.id + 'info', infowindow);

            if (html.popup) {
                goMap.openWindow(infowindow, marker, html);
                infowindow.show = true;
            }

            google.maps.event.addListener(marker, 'click', function () {
                if (infowindow.show && goMap.opts.hideByClick) {
                    infowindow.close();
                    infowindow.show = false;
                }
                else {
                    goMap.openWindow(infowindow, marker, html);
                    infowindow.show = true;
                }
            });
        },

        openWindow: function (infowindow, marker, html) {
            if (this.opts.oneInfoWindow)
                this.clearInfo();

            if (html.ajax) {
                infowindow.open(this.map, marker);
                $.ajax({
                    url: html.ajax,
                    success: function (html) {
                        infowindow.setContent(html);
                    }
                });
            }
            else if (html.id) {
                infowindow.setContent($(html.id).html());
                infowindow.open(this.map, marker);
            }
            else
                infowindow.open(this.map, marker);
        },

        setInfo: function (id, text) {
            var info = $(this.mapId).data(id + 'info');

            if (typeof text == 'object')
                info.setOptions(text);
            else
                info.setContent(text);
        },

        getInfo: function (id, hideDiv) {
            var info = $(this.mapId).data(id + 'info').getContent();
            if (hideDiv)
                return $(info).html();
            else
                return info;
        },

        clearInfo: function () {
            for (var i = 0, l = this.markers.length; i < l; i++) {
                var info = $(this.mapId).data(this.markers[i] + 'info');
                if (info) {
                    info.close();
                    info.show = false;
                }
            }
        },

        fitBounds: function (type, markers) {
            var goMap = this;
            if (this.getMarkerCount() != this.getTmpMarkerCount())
                setTimeout(function () {
                    goMap.fitBounds(type, markers);
                }, this.opts.delay);
            else {
                this.bounds = new google.maps.LatLngBounds();

                if (!type || (type && type == 'all')) {
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        this.bounds.extend($(this.mapId).data(this.markers[i]).position);
                    }
                }
                else if (type && type == 'visible') {
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        if (this.getVisibleMarker(this.markers[i]))
                            this.bounds.extend($(this.mapId).data(this.markers[i]).position);
                    }

                }
                else if (type && type == 'markers' && $.isArray(markers)) {
                    for (var i = 0, l = markers.length; i < l; i++) {
                        this.bounds.extend($(this.mapId).data(markers[i]).position);
                    }
                }
                this.map.fitBounds(this.bounds);
            }
        },

        getBounds: function () {
            return this.map.getBounds();
        },

        createPolyline: function (poly) {
            poly.type = 'polyline';
            return this.createOverlay(poly);
        },

        createPolygon: function (poly) {
            poly.type = 'polygon';
            return this.createOverlay(poly);
        },

        createCircle: function (poly) {
            poly.type = 'circle';
            return this.createOverlay(poly);
        },

        createRectangle: function (poly) {
            poly.type = 'rectangle';
            return this.createOverlay(poly);
        },

        createOverlay: function (poly) {
            var overlay = [];
            if (!poly.id) {
                this.count++;
                poly.id = this.opts.polyId + this.count;
            }
            switch (poly.type) {
                case 'polyline':
                    if (poly.coords.length > 0) {
                        for (var j = 0, l = poly.coords.length; j < l; j++)
                            overlay.push(new google.maps.LatLng(poly.coords[j].latitude, poly.coords[j].longitude));

                        overlay = new google.maps.Polyline({
                            map: this.map,
                            path: overlay,
                            strokeColor: poly.color ? poly.color : this.opts.polyline.color,
                            strokeOpacity: poly.opacity ? poly.opacity : this.opts.polyline.opacity,
                            strokeWeight: poly.weight ? poly.weight : this.opts.polyline.weight
                        });
                    }
                    else
                        return false;
                    break;
                case 'polygon':
                    if (poly.coords.length > 0) {
                        for (var j = 0, l = poly.coords.length; j < l; j++)
                            overlay.push(new google.maps.LatLng(poly.coords[j].latitude, poly.coords[j].longitude));

                        overlay = new google.maps.Polygon({
                            map: this.map,
                            path: overlay,
                            strokeColor: poly.color ? poly.color : this.opts.polygon.color,
                            strokeOpacity: poly.opacity ? poly.opacity : this.opts.polygon.opacity,
                            strokeWeight: poly.weight ? poly.weight : this.opts.polygon.weight,
                            fillColor: poly.fillColor ? poly.fillColor : this.opts.polygon.fillColor,
                            fillOpacity: poly.fillOpacity ? poly.fillOpacity : this.opts.polygon.fillOpacity
                        });
                    }
                    else
                        return false;
                    break;
                case 'circle':
                    overlay = new google.maps.Circle({
                        map: this.map,
                        center: new google.maps.LatLng(poly.latitude, poly.longitude),
                        radius: poly.radius,
                        strokeColor: poly.color ? poly.color : this.opts.circle.color,
                        strokeOpacity: poly.opacity ? poly.opacity : this.opts.circle.opacity,
                        strokeWeight: poly.weight ? poly.weight : this.opts.circle.weight,
                        fillColor: poly.fillColor ? poly.fillColor : this.opts.circle.fillColor,
                        fillOpacity: poly.fillOpacity ? poly.fillOpacity : this.opts.circle.fillOpacity
                    });
                    break;
                case 'rectangle':
                    overlay = new google.maps.Rectangle({
                        map: this.map,
                        bounds: new google.maps.LatLngBounds(new google.maps.LatLng(poly.sw.latitude, poly.sw.longitude), new google.maps.LatLng(poly.ne.latitude, poly.ne.longitude)),
                        strokeColor: poly.color ? poly.color : this.opts.circle.color,
                        strokeOpacity: poly.opacity ? poly.opacity : this.opts.circle.opacity,
                        strokeWeight: poly.weight ? poly.weight : this.opts.circle.weight,
                        fillColor: poly.fillColor ? poly.fillColor : this.opts.circle.fillColor,
                        fillOpacity: poly.fillOpacity ? poly.fillOpacity : this.opts.circle.fillOpacity
                    });
                    break;
                default:
                    return false;
                    break;
            }
            this.addOverlay(poly, overlay);
            return overlay;
        },

        addOverlay: function (poly, overlay) {
            $(this[this.overlays[poly.type].id]).data(poly.id, overlay);
            this[this.overlays[poly.type].array].push(poly.id);
        },

        setOverlay: function (type, overlay, options) {
            overlay = $(this[this.overlays[type].id]).data(overlay);

            if (options.coords && options.coords.length > 0) {
                var array = [];
                for (var j = 0, l = options.coords.length; j < l; j++)
                    array.push(new google.maps.LatLng(options.coords[j].latitude, options.coords[j].longitude));

                options.path = array;
                delete options.coords;
            }
            else if (options.ne && options.sw) {
                options.bounds = new google.maps.LatLngBounds(new google.maps.LatLng(options.sw.latitude, options.sw.longitude), new google.maps.LatLng(options.ne.latitude, options.ne.longitude));
                delete options.ne;
                delete options.sw;
            }
            else if (options.latitude && options.longitude) {

                options.center = new google.maps.LatLng(options.latitude, options.longitude);
                delete options.latitude;
                delete options.longitude;
            }
            overlay.setOptions(options);
        },

        showHideOverlay: function (type, overlay, display) {
            if (typeof display === 'undefined') {
                if (this.getVisibleOverlay(type, overlay))
                    display = false;
                else
                    display = true;
            }

            if (display)
                $(this[this.overlays[type].id]).data(overlay).setMap(this.map);
            else
                $(this[this.overlays[type].id]).data(overlay).setMap(null);
        },

        getVisibleOverlay: function (type, overlay) {
            if ($(this[this.overlays[type].id]).data(overlay).getMap())
                return true;
            else
                return false;
        },

        getOverlaysCount: function (type) {
            return this[this.overlays[type].array].length;
        },

        removeOverlay: function (type, overlay) {
            var index = $.inArray(overlay, this[this.overlays[type].array]), current;
            if (index > -1) {
                current = this[this.overlays[type].array].splice(index, 1);
                var markerId = current[0];
                $(this[this.overlays[type].id]).data(markerId).setMap(null);
                $(this[this.overlays[type].id]).removeData(markerId);

                return true;
            }
            return false;
        },

        clearOverlays: function (type) {
            for (var i = 0, l = this[this.overlays[type].array].length; i < l; i++) {
                var markerId = this[this.overlays[type].array][i];
                $(this[this.overlays[type].id]).data(markerId).setMap(null);
                $(this[this.overlays[type].id]).removeData(markerId);
            }
            this[this.overlays[type].array] = [];
        },

        showHideMarker: function (marker, display) {
            if (typeof display === 'undefined') {
                if (this.getVisibleMarker(marker)) {
                    $(this.mapId).data(marker).setVisible(false);
                    var info = $(this.mapId).data(marker + 'info');
                    if (info && info.show) {
                        info.close();
                        info.show = false;
                    }
                }
                else
                    $(this.mapId).data(marker).setVisible(true);
            }
            else
                $(this.mapId).data(marker).setVisible(display);
        },

        showHideMarkerByGroup: function (group, display) {
            for (var i = 0, l = this.markers.length; i < l; i++) {
                var markerId = this.markers[i];
                var marker = $(this.mapId).data(markerId);
                if (marker.group == group) {
                    if (typeof display === 'undefined') {
                        if (this.getVisibleMarker(markerId)) {
                            marker.setVisible(false);
                            var info = $(this.mapId).data(markerId + 'info');
                            if (info && info.show) {
                                info.close();
                                info.show = false;
                            }
                        }
                        else {
                            marker.setVisible(true);
                        }
                    }
                    else
                        marker.setVisible(display);
                }
            }
        },

        getVisibleMarker: function (marker) {
            return $(this.mapId).data(marker).getVisible();
        },

        getMarkerCount: function () {
            return this.markers.length;
        },

        getTmpMarkerCount: function () {
            return this.tmpMarkers.length;
        },

        getVisibleMarkerCount: function () {
            return this.getMarkers('visiblesInMap').length;
        },

        getMarkerByGroupCount: function (group) {
            return this.getMarkers('group', group).length;
        },

        getMarkers: function (type, name) {
            var array = [];
            switch (type) {
                case "json":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = "'" + i + "': '" + $(this.mapId).data(this.markers[i]).getPosition().toUrlValue() + "'";
                        array.push(temp);
                    }
                    array = "{'markers':{" + array.join(",") + "}}";
                    break;
                case "data":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = "marker[" + i + "]=" + $(this.mapId).data(this.markers[i]).getPosition().toUrlValue();
                        array.push(temp);
                    }
                    array = array.join("&");
                    break;
                case "visiblesInBounds":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        if (this.isVisible($(this.mapId).data(this.markers[i]).getPosition()))
                            array.push(this.markers[i]);
                    }
                    break;
                case "visiblesInMap":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        if (this.getVisibleMarker(this.markers[i]))
                            array.push(this.markers[i]);
                    }
                    break;
                case "group":
                    if (name)
                        for (var i = 0, l = this.markers.length; i < l; i++) {
                            if ($(this.mapId).data(this.markers[i]).group == name)
                                array.push(this.markers[i]);
                        }
                    break;
                case "markers":
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = $(this.mapId).data(this.markers[i]);
                        array.push(temp);
                    }
                    break;
                default:
                    for (var i = 0, l = this.markers.length; i < l; i++) {
                        var temp = $(this.mapId).data(this.markers[i]).getPosition().toUrlValue();
                        array.push(temp);
                    }
                    break;
            }
            return array;
        },

        getVisibleMarkers: function () {
            return this.getMarkers('visiblesInBounds');
        },

        createMarker: function (marker) {
            if (!marker.geocode) {
                this.count++;
                if (!marker.id)
                    marker.id = this.opts.prefixId + this.count;
                this.tmpMarkers.push(marker.id);
            }
            if (marker.address && !marker.geocode) {
                this.geoMarkers.push(marker);
                this.geoMarker();
            }
            else if (marker.latitude && marker.longitude || marker.position) {
                var options = {map: this.map};
                options.id = marker.id;
                options.group = marker.group ? marker.group : this.opts.groupId;
                options.zIndex = marker.zIndex ? marker.zIndex : 0;
                options.zIndexOrg = marker.zIndexOrg ? marker.zIndexOrg : 0;


                options.optimized = false; // fixes this bug https://code.google.com/p/gmaps-api-issues/issues/detail?id=4431

                if (marker.visible == false)
                    options.visible = marker.visible;

                if (marker.title)
                    options.title = marker.title;

                if (marker.label)
                    options.label = marker.label;

                if (marker.draggable)
                    options.draggable = marker.draggable;

                if (marker.icon && marker.icon.image) {
                    options.icon = marker.icon.image;
                    if (marker.icon.shadow)
                        options.shadow = marker.icon.shadow;
                }
                else if (marker.icon)
                    options.icon = marker.icon;

                else if (this.opts.icon && this.opts.icon.image) {
                    options.icon = this.opts.icon.image;
                    if (this.opts.icon.shadow)
                        options.shadow = this.opts.icon.shadow;
                }
                else if (this.opts.icon)
                    options.icon = this.opts.icon;

                options.position = marker.position ? marker.position : new google.maps.LatLng(marker.latitude, marker.longitude);

                var cmarker = new google.maps.Marker(options);

                if (marker.html) {
                    if (!marker.html.content && !marker.html.ajax && !marker.html.id)
                        marker.html = {content: marker.html};
                    else if (!marker.html.content)
                        marker.html.content = null;

                    this.setInfoWindow(cmarker, marker.html);
                }
                this.addMarker(cmarker);
                return cmarker;
            }
        },

        addMarker: function (marker) {
            $(this.mapId).data(marker.id, marker);
            this.markers.push(marker.id);
        },

        setMarker: function (marker, options) {
            var tmarker = $(this.mapId).data(marker);

            delete options.id;
            delete options.visible;

            if (options.icon) {
                var toption = options.icon;
                delete options.icon;

                if (toption && toption == 'default') {
                    if (this.opts.icon && this.opts.icon.image) {
                        options.icon = this.opts.icon.image;
                        if (this.opts.icon.shadow)
                            options.shadow = this.opts.icon.shadow;
                    }
                    else if (this.opts.icon)
                        options.icon = this.opts.icon;
                }
                else if (toption && toption.image) {
                    options.icon = toption.image;
                    if (toption.shadow)
                        options.shadow = toption.shadow;
                }
                else if (toption)
                    options.icon = toption;
            }

            if (options.address) {
                this.geocode({address: options.address}, {markerId: tmarker});
                delete options.address;
                delete options.latitude;
                delete options.longitude;
                delete options.position;
            }
            else if (options.latitude && options.longitude || options.position) {
                if (!options.position)
                    options.position = new google.maps.LatLng(options.latitude, options.longitude);
            }
            tmarker.setOptions(options);
        },

        removeMarker: function (marker) {
            var index = $.inArray(marker, this.markers), current;
            if (index > -1) {
                this.tmpMarkers.splice(index, 1);
                current = this.markers.splice(index, 1);
                var markerId = current[0];
                var marker = $(this.mapId).data(markerId);
                var info = $(this.mapId).data(markerId + 'info');

                marker.setVisible(false);
                marker.setMap(null);
                $(this.mapId).removeData(markerId);

                if (info) {
                    info.close();
                    info.show = false;
                    $(this.mapId).removeData(markerId + 'info');
                }
                return true;
            }
            return false;
        },

        clearMarkers: function () {
            for (var i = 0, l = this.markers.length; i < l; i++) {
                var markerId = this.markers[i];
                var marker = $(this.mapId).data(markerId);
                var info = $(this.mapId).data(markerId + 'info');

                marker.setVisible(false);
                marker.setMap(null);
                $(this.mapId).removeData(markerId);

                if (info) {
                    info.close();
                    info.show = false;
                    $(this.mapId).removeData(markerId + 'info');
                }
            }
            this.singleMarker = false;
            this.lockGeocode = false;
            this.markers = [];
            this.tmpMarkers = [];
            this.geoMarkers = [];
        },

        clearMarkersDelay: function () {

            var old_markers =this.markers;
            setTimeout(function(){
            for (var i = 0, l = old_markers.length; i < l; i++) {
                var markerId = old_markers[i];
                var marker = $(this.mapId).data(markerId);
                var info = $(this.mapId).data(markerId + 'info');

                marker.setVisible(false);
                marker.setMap(null);
                $(this.mapId).removeData(markerId);

                if (info) {
                    info.close();
                    info.show = false;
                    $(this.mapId).removeData(markerId + 'info');
                }
            }
            this.singleMarker = false;
            this.lockGeocode = false;
            this.markers = [];
            this.tmpMarkers = [];
            this.geoMarkers = [];
            }, 3000);
        },

        isVisible: function (latlng) {
            return this.map.getBounds().contains(latlng);
        }
    }
})(jQuery);
}

/* Do not load for lazy load */
if ( ! window.gdLoadMap ) {
	geodirGoMapInit();
}

function gdGeoLocateMe(el, type) {
    window.gdLocate = '';
    if (typeof type != 'undefined') {
        window.gdLocate = type;
    }
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(gdGeoLocateMeSuccess, gdGeoLocateMeError);
    } else {
        gdGeoLocateMeError(-1);
    }
}
function gdGeoLocateMeSuccess(position) {
    var coords = position.coords || position.coordinate || position;
    if (coords && coords.latitude && coords.longitude) {
        var myLat = coords.latitude, myLng = coords.longitude;
        var geoAddress = myLat + ', ' + myLng;
        if (window.gdMaps == 'google' || window.gdMaps == 'osm') {
            if (window.gdLocate && window.gdLocate == 'add-listing') {
                if (typeof geocodePosition != 'undefined') {
                    jQuery("#postmap").goMap();
                    user_address = false;
                    if (window.gdMaps == 'google') {
                        jQuery.goMap.map.setCenter(new google.maps.LatLng(myLat, myLng));
                        baseMarker.setPosition(new google.maps.LatLng(myLat, myLng));
                        updateMarkerPosition(baseMarker.getPosition());
                        geocodePosition(baseMarker.getPosition());
                    } else if (window.gdMaps == 'osm') {
                        centerMap(new L.latLng(myLat, myLng));
                        baseMarker.setLatLng(new L.latLng(myLat, myLng));
                        updateMarkerPositionOSM(baseMarker.getLatLng());
                        geocodePositionOSM(baseMarker.getLatLng());
                    }
                }
            } else {
                //
            }
        }
    }
}
function gdGeoLocateMeError(err) {
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
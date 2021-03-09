(function(f) {
    if (typeof exports === "object" && typeof module !== "undefined") module.exports = f();
    else if (typeof define === "function" && define.amd) define([], f);
    else {
        var g;
        if (typeof window !== "undefined") g = window;
        else if (typeof global !== "undefined") g = global;
        else if (typeof self !== "undefined") g = self;
        else g = this;
        (g.L || (g.L = {})).Routing = f()
    }
})(function() {
    var define, module, exports;
    return function e(t, n, r) {
        function s(o, u) {
            if (!n[o]) {
                if (!t[o]) {
                    var a = typeof require == "function" && require;
                    if (!u && a) return a(o, !0);
                    if (i) return i(o, !0);
                    var f = new Error("Cannot find module '" + o + "'");
                    throw f.code = "MODULE_NOT_FOUND", f;
                }
                var l = n[o] = {
                    exports: {}
                };
                t[o][0].call(l.exports, function(e) {
                    var n = t[o][1][e];
                    return s(n ? n : e)
                }, l, l.exports, e, t, n, r)
            }
            return n[o].exports
        }
        var i = typeof require == "function" && require;
        for (var o = 0; o < r.length; o++) s(r[o]);
        return s
    }({
        1: [function(require, module, exports) {
            function corslite(url, callback, cors) {
                var sent = false;
                if (typeof window.XMLHttpRequest === "undefined") return callback(Error("Browser not supported"));
                if (typeof cors === "undefined") {
                    var m = url.match(/^\s*https?:\/\/[^\/]*/);
                    cors = m && m[0] !== location.protocol + "//" + location.domain + (location.port ? ":" + location.port : "")
                }
                var x = new window.XMLHttpRequest;

                function isSuccessful(status) {
                    return status >= 200 && status < 300 || status === 304
                }
                if (cors && !("withCredentials" in x)) {
                    x = new window.XDomainRequest;
                    var original = callback;
                    callback = function() {
                        if (sent) original.apply(this, arguments);
                        else {
                            var that = this,
                                args = arguments;
                            setTimeout(function() {
                                original.apply(that, args)
                            }, 0)
                        }
                    }
                }

                function loaded() {
                    if (x.status === undefined || isSuccessful(x.status)) callback.call(x, null, x);
                    else callback.call(x, x, null)
                }
                if ("onload" in x) x.onload = loaded;
                else x.onreadystatechange = function readystate() {
                    if (x.readyState === 4) loaded()
                };
                x.onerror = function error(evt) {
                    callback.call(this, evt || true, null);
                    callback = function() {}
                };
                x.onprogress = function() {};
                x.ontimeout = function(evt) {
                    callback.call(this, evt, null);
                    callback = function() {}
                };
                x.onabort = function(evt) {
                    callback.call(this, evt, null);
                    callback = function() {}
                };
                x.open("GET", url, true);
                x.send(null);
                sent = true;
                return x
            }
            if (typeof module !== "undefined") module.exports = corslite
        }, {}],
        2: [function(require, module, exports) {
            var polyline = {};

            function encode(coordinate, factor) {
                coordinate = Math.round(coordinate * factor);
                coordinate <<= 1;
                if (coordinate < 0) coordinate = ~coordinate;
                var output = "";
                while (coordinate >= 32) {
                    output += String.fromCharCode((32 | coordinate & 31) + 63);
                    coordinate >>= 5
                }
                output += String.fromCharCode(coordinate + 63);
                return output
            }
            polyline.decode = function(str, precision) {
                var index = 0,
                    lat = 0,
                    lng = 0,
                    coordinates = [],
                    shift = 0,
                    result = 0,
                    byte = null,
                    latitude_change, longitude_change, factor = Math.pow(10, precision || 5);
                while (index < str.length) {
                    byte = null;
                    shift = 0;
                    result = 0;
                    do {
                        byte = str.charCodeAt(index++) - 63;
                        result |= (byte & 31) << shift;
                        shift += 5
                    } while (byte >= 32);
                    latitude_change = result & 1 ? ~(result >> 1) : result >> 1;
                    shift = result = 0;
                    do {
                        byte = str.charCodeAt(index++) - 63;
                        result |= (byte & 31) << shift;
                        shift += 5
                    } while (byte >= 32);
                    longitude_change = result & 1 ? ~(result >> 1) : result >> 1;
                    lat += latitude_change;
                    lng += longitude_change;
                    coordinates.push([lat / factor, lng / factor])
                }
                return coordinates
            };
            polyline.encode = function(coordinates, precision) {
                if (!coordinates.length) return "";
                var factor = Math.pow(10, precision || 5),
                    output = encode(coordinates[0][0], factor) + encode(coordinates[0][1], factor);
                for (var i = 1; i < coordinates.length; i++) {
                    var a = coordinates[i],
                        b = coordinates[i - 1];
                    output += encode(a[0] - b[0], factor);
                    output += encode(a[1] - b[1], factor)
                }
                return output
            };
            if (typeof module !== undefined) module.exports = polyline
        }, {}],
        3: [function(require, module, exports) {
            (function() {
                L.Routing = L.Routing || {};
                L.Routing.Autocomplete = L.Class.extend({
                    options: {
                        timeout: 500,
                        blurTimeout: 100,
                        noResultsMessage: "No results found."
                    },
                    initialize: function(elem, callback, context, options) {
                        L.setOptions(this, options);
                        this._elem = elem;
                        this._resultFn = options.resultFn ? L.Util.bind(options.resultFn, options.resultContext) : null;
                        this._autocomplete = options.autocompleteFn ? L.Util.bind(options.autocompleteFn, options.autocompleteContext) : null;
                        this._selectFn = L.Util.bind(callback, context);
                        this._container = L.DomUtil.create("div", "leaflet-routing-geocoder-result");
                        this._resultTable = L.DomUtil.create("table", "", this._container);
                        L.DomEvent.addListener(this._elem, "input", this._keyPressed, this);
                        L.DomEvent.addListener(this._elem, "keypress", this._keyPressed, this);
                        L.DomEvent.addListener(this._elem, "keydown", this._keyDown, this);
                        L.DomEvent.addListener(this._elem, "blur", function() {
                            if (this._isOpen) this.close()
                        }, this)
                    },
                    close: function() {
                        L.DomUtil.removeClass(this._container, "leaflet-routing-geocoder-result-open");
                        this._isOpen = false
                    },
                    _open: function() {
                        jQuery(this._elem).parent().removeClass("leaflet-routing-searching");
                        var rect = this._elem.getBoundingClientRect();
                        if (!this._container.parentElement) {
                            var scrollX = window.pageXOffset !== undefined ? window.pageXOffset : (document.documentElement || document.body.parentNode || document.body).scrollLeft;
                            var scrollY = window.pageYOffset !== undefined ? window.pageYOffset : (document.documentElement || document.body.parentNode || document.body).scrollTop;
                            this._container.style.left = rect.left + scrollX + "px";
                            this._container.style.top = rect.bottom + scrollY + "px";
                            this._container.style.width = rect.right - rect.left + "px";
                            document.body.appendChild(this._container)
                        }
                        L.DomUtil.addClass(this._container, "leaflet-routing-geocoder-result-open");
                        this._isOpen = true
                    },
                    _setResults: function(results) {
                        var i, tr, td, text;
                        delete this._selection;
                        this._results = results;
                        while (this._resultTable.firstChild) this._resultTable.removeChild(this._resultTable.firstChild);
                        for (i = 0; i < results.length; i++) {
                            tr = L.DomUtil.create("tr", "", this._resultTable);
                            tr.setAttribute("data-result-index", i);
                            td = L.DomUtil.create("td", "", tr);
                            text = document.createTextNode(results[i].name);
                            td.appendChild(text);
                            L.DomEvent.addListener(td, "mousedown", L.DomEvent.preventDefault);
                            L.DomEvent.addListener(td, "click", this._createClickListener(results[i]))
                        }
                        if (!i) {
                            tr = L.DomUtil.create("tr", "", this._resultTable);
                            td = L.DomUtil.create("td", "leaflet-routing-geocoder-no-results", tr);
                            td.innerHTML = this.options.noResultsMessage
                        }
                        this._open();
                        if (results.length > 0) this._select(1)
                    },
                    _createClickListener: function(r) {
                        var resultSelected = this._resultSelected(r);
                        return L.bind(function() {
                            this._elem.blur();
                            resultSelected()
                        }, this)
                    },
                    _resultSelected: function(r) {
                        return L.bind(function() {
                            this.close();
                            this._elem.value = r.name;
                            this._lastCompletedText = r.name;
                            this._selectFn(r)
                        }, this)
                    },
                    _keyPressed: function(e) {
                        var index;
                        if (this._isOpen && e.keyCode === 13 && this._selection) {
                            index = parseInt(this._selection.getAttribute("data-result-index"), 10);
                            this._resultSelected(this._results[index])();
                            L.DomEvent.preventDefault(e);
                            return
                        }
                        if (e.keyCode === 13) {
                            this._complete(this._resultFn, true);
                            return
                        }
                        if (this._autocomplete && document.activeElement === this._elem) {
                            if (this._timer) clearTimeout(this._timer);
                            this._timer = setTimeout(L.Util.bind(function() {
                                this._complete(this._autocomplete)
                            }, this), this.options.timeout);
                            return
                        }
                        this._unselect()
                    },
                    _select: function(dir) {
                        var sel = this._selection;
                        if (sel) {
                            L.DomUtil.removeClass(sel.firstChild, "leaflet-routing-geocoder-selected");
                            sel = sel[dir > 0 ? "nextSibling" : "previousSibling"]
                        }
                        if (!sel) sel = this._resultTable[dir > 0 ? "firstChild" : "lastChild"];
                        if (sel) {
                            L.DomUtil.addClass(sel.firstChild, "leaflet-routing-geocoder-selected");
                            this._selection = sel
                        }
                    },
                    _unselect: function() {
                        if (this._selection) L.DomUtil.removeClass(this._selection.firstChild, "leaflet-routing-geocoder-selected");
                        delete this._selection
                    },
                    _keyDown: function(e) {
                        if (this._isOpen) switch (e.keyCode) {
                            case 27:
                                this.close();
                                L.DomEvent.preventDefault(e);
                                return;
                            case 38:
                                this._select(-1);
                                L.DomEvent.preventDefault(e);
                                return;
                            case 40:
                                this._select(1);
                                L.DomEvent.preventDefault(e);
                                return
                        }
                    },
                    _complete: function(completeFn, trySelect) {
                        var v = this._elem.value;

                        function completeResults(results) {
                            this._lastCompletedText = v;
                            if (!results) return;
                            if (trySelect && results.length === 1) this._resultSelected(results[0])();
                            else this._setResults(results)
                        }
                        if (!v) return;
                        if (v !== this._lastCompletedText) completeFn(v, completeResults, this);
                        else if (trySelect) completeResults.call(this, this._results)
                    }
                })
            })()
        }, {}],
        4: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.Itinerary"));
                    L.extend(L.Routing, require("./L.Routing.Line"));
                    L.extend(L.Routing, require("./L.Routing.Plan"));
                    L.extend(L.Routing, require("./L.Routing.OSRMv1"));
                    L.extend(L.Routing, require("./L.Routing.Mapbox"));
                    L.extend(L.Routing, require("./L.Routing.ErrorControl"));
                    L.Routing.Control = L.Routing.Itinerary.extend({
                        options: {
                            fitSelectedRoutes: "smart",
                            routeLine: function(route, options) {
                                return L.Routing.line(route, options)
                            },
                            autoRoute: true,
                            routeWhileDragging: false,
                            routeDragInterval: 500,
                            waypointMode: "connect",
                            showAlternatives: false,
                            defaultErrorHandler: function(e) {
                                console.error("Routing error:", e.error)
                            }
                        },
                        initialize: function(options) {
                            L.Util.setOptions(this, options);
                            this._router = this.options.router || new L.Routing.OSRMv1(options);
                            this._plan = this.options.plan || L.Routing.plan(this.options.waypoints, options);
                            this._requestCount = 0;
                            L.Routing.Itinerary.prototype.initialize.call(this, options);
                            this.on("routeselected", this._routeSelected, this);
                            if (this.options.defaultErrorHandler) this.on("routingerror", this.options.defaultErrorHandler);
                            this._plan.on("waypointschanged", this._onWaypointsChanged, this);
                            if (options.routeWhileDragging) this._setupRouteDragging();
                            if (this.options.autoRoute) this.route()
                        },
                        onAdd: function(map) {
                            if (!map) map = jQuery.goMap.map;
                            var container = L.Routing.Itinerary.prototype.onAdd.call(this, map);
                            this._map = map;
                            this._map.on("zoomend", function() {
                                if (!this._selectedRoute || !this._router.requiresMoreDetail) return;
                                var map = this._map;
                                if (this._router.requiresMoreDetail(this._selectedRoute, map.getZoom(), map.getBounds())) this.route({
                                    callback: L.bind(function(err, routes) {
                                        var i;
                                        if (!err) {
                                            for (i = 0; i < routes.length; i++) this._routes[i].properties = routes[i].properties;
                                            this._updateLineCallback(err, routes)
                                        }
                                    }, this),
                                    simplifyGeometry: false,
                                    geometryOnly: true
                                })
                            }, this);
                            if (this._plan.options.geocoder) container.insertBefore(this._plan.createGeocoders(), container.firstChild);
                            return container
                        },
                        onRemove: function(map) {
                            if (this._line) map.removeLayer(this._line);
                            map.removeLayer(this._plan);
                            return L.Routing.Itinerary.prototype.onRemove.call(this, map)
                        },
                        getWaypoints: function() {
                            return this._plan.getWaypoints()
                        },
                        setWaypoints: function(waypoints) {
                            this._plan.setWaypoints(waypoints);
                            return this
                        },
                        spliceWaypoints: function() {
                            var removed = this._plan.spliceWaypoints.apply(this._plan, arguments);
                            return removed
                        },
                        getPlan: function() {
                            return this._plan
                        },
                        getRouter: function() {
                            return this._router
                        },
                        _routeSelected: function(e) {
                            var route = this._selectedRoute = e.route,
                                alternatives = this.options.showAlternatives && e.alternatives,
                                fitMode = this.options.fitSelectedRoutes,
                                fitBounds = fitMode === "smart" && !this._waypointsVisible() || fitMode !== "smart" && fitMode;
                            this._updateLines({
                                route: route,
                                alternatives: alternatives
                            });
                            if (fitBounds) this._map.fitBounds(this._line.getBounds());
                            if (this.options.waypointMode === "snap") {
                                this._plan.off("waypointschanged", this._onWaypointsChanged, this);
                                this.setWaypoints(route.waypoints);
                                this._plan.on("waypointschanged", this._onWaypointsChanged, this)
                            }
                        },
                        _waypointsVisible: function() {
                            var wps = this.getWaypoints(),
                                mapSize, bounds, boundsSize, i, p;
                            try {
                                mapSize = this._map.getSize();
                                for (i = 0; i < wps.length; i++) {
                                    p = this._map.latLngToLayerPoint(wps[i].latLng);
                                    if (bounds) bounds.extend(p);
                                    else bounds = L.bounds([p])
                                }
                                boundsSize = bounds.getSize();
                                return (boundsSize.x > mapSize.x / 5 || boundsSize.y > mapSize.y / 5) && this._waypointsInViewport()
                            } catch (e) {
                                return false
                            }
                        },
                        _waypointsInViewport: function() {
                            var wps = this.getWaypoints(),
                                mapBounds, i;
                            try {
                                mapBounds = this._map.getBounds()
                            } catch (e) {
                                return false
                            }
                            for (i = 0; i < wps.length; i++)
                                if (mapBounds.contains(wps[i].latLng)) return true;
                            return false
                        },
                        _updateLines: function(routes) {
                            var addWaypoints = this.options.addWaypoints !== undefined ? this.options.addWaypoints : true;
                            this._clearLines();
                            this._alternatives = [];
                            if (routes.alternatives) routes.alternatives.forEach(function(alt, i) {
                                this._alternatives[i] = this.options.routeLine(alt, L.extend({
                                    isAlternative: true
                                }, this.options.altLineOptions || this.options.lineOptions));
                                this._alternatives[i].addTo(this._map);
                                this._hookAltEvents(this._alternatives[i])
                            }, this);
                            this._line = this.options.routeLine(routes.route, L.extend({
                                addWaypoints: addWaypoints,
                                extendToWaypoints: this.options.waypointMode === "connect"
                            }, this.options.lineOptions));
                            this._line.addTo(this._map);
                            this._hookEvents(this._line)
                        },
                        _hookEvents: function(l) {
                            l.on("linetouched", function(e) {
                                this._plan.dragNewWaypoint(e)
                            }, this)
                        },
                        _hookAltEvents: function(l) {
                            l.on("linetouched", function(e) {
                                var alts = this._routes.slice();
                                var selected = alts.splice(e.target._route.routesIndex, 1)[0];
                                this.fire("routeselected", {
                                    route: selected,
                                    alternatives: alts
                                })
                            }, this)
                        },
                        _onWaypointsChanged: function(e) {
                            if (this.options.autoRoute) this.route({});
                            if (!this._plan.isReady()) {
                                this._clearLines();
                                this._clearAlts()
                            }
                            this.fire("waypointschanged", {
                                waypoints: e.waypoints
                            })
                        },
                        _setupRouteDragging: function() {
                            var timer = 0,
                                waypoints;
                            this._plan.on("waypointdrag", L.bind(function(e) {
                                waypoints = e.waypoints;
                                if (!timer) timer = setTimeout(L.bind(function() {
                                    this.route({
                                        waypoints: waypoints,
                                        geometryOnly: true,
                                        callback: L.bind(this._updateLineCallback, this)
                                    });
                                    timer = undefined
                                }, this), this.options.routeDragInterval)
                            }, this));
                            this._plan.on("waypointdragend", function() {
                                if (timer) {
                                    clearTimeout(timer);
                                    timer = undefined
                                }
                                this.route()
                            }, this)
                        },
                        _updateLineCallback: function(err, routes) {
                            if (!err) {
                                routes = routes.slice();
                                var selected = routes.splice(this._selectedRoute.routesIndex, 1)[0];
                                this._updateLines({
                                    route: selected,
                                    alternatives: routes
                                })
                            } else this._clearLines()
                        },
                        route: function(options) {
                            var ts = ++this._requestCount,
                                wps;
                            options = options || {};
                            if (this._plan.isReady()) {
                                if (this.options.useZoomParameter) options.z = this._map && this._map.getZoom();
                                wps = options && options.waypoints || this._plan.getWaypoints();
                                this.fire("routingstart", {
                                    waypoints: wps
                                });
                                this._router.route(wps, options.callback || function(err, routes) {
                                    if (ts === this._requestCount) {
                                        this._clearLines();
                                        this._clearAlts();
                                        if (err) {
                                            this.fire("routingerror", {
                                                error: err
                                            });
                                            return
                                        }
                                        routes.forEach(function(route, i) {
                                            route.routesIndex = i
                                        });
                                        if (!options.geometryOnly) {
                                            this.fire("routesfound", {
                                                waypoints: wps,
                                                routes: routes
                                            });
                                            this.setAlternatives(routes)
                                        } else {
                                            var selectedRoute = routes.splice(0, 1)[0];
                                            this._routeSelected({
                                                route: selectedRoute,
                                                alternatives: routes
                                            })
                                        }
                                    }
                                }, this, options)
                            }
                        },
                        _clearLines: function() {
                            if (this._line) {
                                this._map.removeLayer(this._line);
                                delete this._line
                            }
                            if (this._alternatives && this._alternatives.length) {
                                for (var i in this._alternatives) this._map.removeLayer(this._alternatives[i]);
                                this._alternatives = []
                            }
                        }
                    });
                    L.Routing.control = function(options) {
                        return new L.Routing.Control(options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.ErrorControl": 5,
            "./L.Routing.Itinerary": 8,
            "./L.Routing.Line": 10,
            "./L.Routing.Mapbox": 12,
            "./L.Routing.OSRMv1": 13,
            "./L.Routing.Plan": 14
        }],
        5: [function(require, module, exports) {
            (function() {
                L.Routing = L.Routing || {};
                L.Routing.ErrorControl = L.Control.extend({
                    options: {
                        header: "Routing error",
                        formatMessage: function(error) {
                            if (error.status < 0) return "Calculating the route caused an error. Technical description follows: <code><pre>" + error.message + "</pre></code";
                            else return "The route could not be calculated. " + error.message
                        }
                    },
                    initialize: function(routingControl, options) {
                        L.Control.prototype.initialize.call(this, options);
                        routingControl.on("routingerror", L.bind(function(e) {
                            if (this._element) {
                                this._element.children[1].innerHTML = this.options.formatMessage(e.error);
                                this._element.style.visibility = "visible"
                            }
                        }, this)).on("routingstart", L.bind(function() {
                            if (this._element) this._element.style.visibility = "hidden"
                        }, this))
                    },
                    onAdd: function() {
                        var header, message;
                        this._element = L.DomUtil.create("div", "leaflet-bar leaflet-routing-error");
                        this._element.style.visibility = "hidden";
                        header = L.DomUtil.create("h3", null, this._element);
                        message = L.DomUtil.create("span", null, this._element);
                        header.innerHTML = this.options.header;
                        return this._element
                    },
                    onRemove: function() {
                        delete this._element
                    }
                });
                L.Routing.errorControl = function(routingControl, options) {
                    return new L.Routing.ErrorControl(routingControl, options)
                }
            })()
        }, {}],
        6: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.Localization"));
                    L.Routing.Formatter = L.Class.extend({
                        options: {
                            units: "metric",
                            unitNames: null,
                            language: "en",
                            roundingSensitivity: 1,
                            distanceTemplate: "{value} {unit}"
                        },
                        initialize: function(options) {
                            L.setOptions(this, options);
                            var langs = L.Util.isArray(this.options.language) ? this.options.language : [this.options.language, "en"];
                            this._localization = new L.Routing.Localization(langs)
                        },
                        formatDistance: function(d, sensitivity) {
                            var un = this.options.unitNames || this._localization.localize("units"),
                                simpleRounding = sensitivity <= 0,
                                round = simpleRounding ? function(v) {
                                    return v
                                } : L.bind(this._round, this),
                                v, yards, data, pow10;
                            if (this.options.units === "imperial") {
                                yards = d / .9144;
                                if (yards >= 1E3) data = {
                                    value: round(d / 1609.344, sensitivity),
                                    unit: un.miles
                                };
                                else data = {
                                    value: round(yards, sensitivity),
                                    unit: un.yards
                                }
                            } else {
                                v = round(d, sensitivity);
                                data = {
                                    value: v >= 1E3 ? v / 1E3 : v,
                                    unit: v >= 1E3 ? un.kilometers : un.meters
                                }
                            }
                            if (simpleRounding) data.value = data.value.toFixed(-sensitivity);
                            return L.Util.template(this.options.distanceTemplate, data)
                        },
                        _round: function(d, sensitivity) {
                            var s = sensitivity || this.options.roundingSensitivity,
                                pow10 = Math.pow(10, (Math.floor(d / s) + "").length - 1),
                                r = Math.floor(d / pow10),
                                p = r > 5 ? pow10 : pow10 / 2;
                            return Math.round(d / p) * p
                        },
                        formatTime: function(t) {
                            var un = this.options.unitNames || this._localization.localize("units");
                            t = Math.round(t / 30) * 30;
                            if (t > 86400) return Math.round(t / 3600) + " " + un.hours;
                            else if (t > 3600) return Math.floor(t / 3600) + " " + un.hours + " " + Math.round(t % 3600 / 60) + " " + un.minutes;
                            else if (t > 300) return Math.round(t / 60) + " " + un.minutes;
                            else if (t > 60) return Math.floor(t / 60) + " " + un.minutes + (t % 60 !== 0 ? " " + t % 60 + " " + un.seconds : "");
                            else return t + " " + un.seconds
                        },
                        formatInstruction: function(instr, i) {
                            if (instr.text === undefined) return this.capitalize(L.Util.template(this._getInstructionTemplate(instr, i), L.extend({}, instr, {
                                exitStr: instr.exit ? this._localization.localize("formatOrder")(instr.exit) : "",
                                dir: this._localization.localize(["directions", instr.direction]),
                                modifier: this._localization.localize(["directions", instr.modifier])
                            })));
                            else return instr.text
                        },
                        getIconName: function(instr, i) {
                            switch (instr.type) {
                                case "Head":
                                    if (i === 0) return "depart";
                                    break;
                                case "WaypointReached":
                                    return "via";
                                case "Roundabout":
                                    return "enter-roundabout";
                                case "DestinationReached":
                                    return "arrive"
                            }
                            switch (instr.modifier) {
                                case "Straight":
                                    return "continue";
                                case "SlightRight":
                                    return "bear-right";
                                case "Right":
                                    return "turn-right";
                                case "SharpRight":
                                    return "sharp-right";
                                case "TurnAround":
                                case "Uturn":
                                    return "u-turn";
                                case "SharpLeft":
                                    return "sharp-left";
                                case "Left":
                                    return "turn-left";
                                case "SlightLeft":
                                    return "bear-left"
                            }
                        },
                        capitalize: function(s) {
                            return s.charAt(0).toUpperCase() + s.substring(1)
                        },
                        _getInstructionTemplate: function(instr, i) {
                            var type = instr.type === "Straight" ? i === 0 ? "Head" : "Continue" : instr.type,
                                strings = this._localization.localize(["instructions", type]);
                            if (!strings) strings = [this._localization.localize(["directions", type]), " " + this._localization.localize(["instructions", "Onto"])];
                            return strings[0] + (strings.length > 1 && instr.road ? strings[1] : "")
                        }
                    });
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.Localization": 11
        }],
        7: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.Autocomplete"));

                    function selectInputText(input) {
                        if (input.setSelectionRange) input.setSelectionRange(0, 9999);
                        else input.select()
                    }
                    L.Routing.GeocoderElement = L.Class.extend({
                        includes: ((typeof L.Evented !== 'undefined' && L.Evented.prototype) || L.Mixin.Events),
                        options: {
                            createGeocoder: function(i, nWps, options) {
                                var container = L.DomUtil.create("div", "leaflet-routing-geocoder leaflet-routing-geocoder-" + i),
                                    input = L.DomUtil.create("input", "", container),
                                    remove = options.addWaypoints ? L.DomUtil.create("span", "leaflet-routing-remove-waypoint", container) : undefined;
                                input.disabled = !options.addWaypoints;
                                return {
                                    container: container,
                                    input: input,
                                    closeButton: remove
                                }
                            },
                            geocoderPlaceholder: function(i, numberWaypoints, geocoderElement) {
                                var l = (new L.Routing.Localization(geocoderElement.options.language)).localize("ui");
                                var startPlaceholder = typeof geodir_params !== "undefined" ? geodir_params.osmStart : l.startPlaceholder;
                                var viaPlaceholder = typeof geodir_params !== "undefined" ? geodir_params.osmVia : l.viaPlaceholder;
                                var endPlaceholder = typeof geodir_params !== "undefined" ? geodir_params.osmEnd : l.endPlaceholder;
                                return i === 0 ? startPlaceholder : i < numberWaypoints - 1 ? L.Util.template(viaPlaceholder, {
                                    viaNumber: i
                                }) : endPlaceholder
                            },
                            geocoderClass: function() {
                                return ""
                            },
                            waypointNameFallback: function(latLng) {
                                var ns = latLng.lat < 0 ? "S" : "N",
                                    ew = latLng.lng < 0 ? "W" : "E",
                                    lat = (Math.round(Math.abs(latLng.lat) * 1E4) / 1E4).toString(),
                                    lng = (Math.round(Math.abs(latLng.lng) * 1E4) / 1E4).toString();
                                return ns + lat + ", " + ew + lng
                            },
                            maxGeocoderTolerance: 200,
                            autocompleteOptions: {},
                            language: "en"
                        },
                        initialize: function(wp, i, nWps, options) {
                            L.setOptions(this, options);
                            var g = this.options.createGeocoder(i, nWps, this.options),
                                closeButton = g.closeButton,
                                geocoderInput = g.input;
                            geocoderInput.setAttribute("placeholder", this.options.geocoderPlaceholder(i, nWps, this));
                            geocoderInput.className = this.options.geocoderClass(i, nWps);
                            this._element = g;
                            this._waypoint = wp;
                            this.update();
                            geocoderInput.value = wp.name;
                            L.DomEvent.addListener(geocoderInput, "click", function() {
                                selectInputText(this)
                            }, geocoderInput);
                            if (closeButton) L.DomEvent.addListener(closeButton, "click", function() {
                                this.fire("delete", {
                                    waypoint: this._waypoint
                                })
                            }, this);
                            new L.Routing.Autocomplete(geocoderInput, function(r) {
                                geocoderInput.value = r.name;
                                wp.name = r.name;
                                wp.latLng = r.center;
                                this.fire("geocoded", {
                                    waypoint: wp,
                                    value: r
                                })
                            }, this, L.extend({
                                resultFn: this.options.geocoder.geocode,
                                resultContext: this.options.geocoder,
                                autocompleteFn: this.options.geocoder.suggest,
                                autocompleteContext: this.options.geocoder
                            }, this.options.autocompleteOptions))
                        },
                        getContainer: function() {
                            return this._element.container
                        },
                        setValue: function(v) {
                            this._element.input.value = v
                        },
                        update: function(force) {
                            var wp = this._waypoint,
                                wpCoords;
                            wp.name = wp.name || "";
                            if (wp.latLng && (force || !wp.name)) {
                                wpCoords = this.options.waypointNameFallback(wp.latLng);
                                if (this.options.geocoder && this.options.geocoder.reverse) this.options.geocoder.reverse(wp.latLng, 67108864, function(rs) {
                                    if (rs.length > 0 && rs[0].center.distanceTo(wp.latLng) < this.options.maxGeocoderTolerance) wp.name = rs[0].name;
                                    else wp.name = wpCoords;
                                    this._update()
                                }, this);
                                else {
                                    wp.name = wpCoords;
                                    this._update()
                                }
                            }
                        },
                        focus: function() {
                            var input = this._element.input;
                            input.focus();
                            selectInputText(input)
                        },
                        _update: function() {
                            var wp = this._waypoint,
                                value = wp && wp.name ? wp.name : "";
                            this.setValue(value);
                            this.fire("reversegeocoded", {
                                waypoint: wp,
                                value: value
                            })
                        }
                    });
                    L.Routing.geocoderElement = function(wp, i, nWps, plan) {
                        return new L.Routing.GeocoderElement(wp, i, nWps, plan)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.Autocomplete": 3
        }],
        8: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.Formatter"));
                    L.extend(L.Routing, require("./L.Routing.ItineraryBuilder"));
                    L.Routing.Itinerary = L.Control.extend({
                        includes: ((typeof L.Evented !== 'undefined' && L.Evented.prototype) || L.Mixin.Events),
                        options: {
                            pointMarkerStyle: {
                                radius: 5,
                                color: "#03f",
                                fillColor: "white",
                                opacity: 1,
                                fillOpacity: .7
                            },
                            summaryTemplate: "<h2>{name}</h2><h3>{distance}, {time}</h3>",
                            timeTemplate: "{time}",
                            containerClassName: "",
                            alternativeClassName: "",
                            minimizedClassName: "",
                            itineraryClassName: "",
                            totalDistanceRoundingSensitivity: -1,
                            show: true,
                            collapsible: true,
                            collapseBtn: function(itinerary) {
                                var collapseBtn = L.DomUtil.create("span", itinerary.options.collapseBtnClass);
                                L.DomEvent.on(collapseBtn, "click", itinerary._toggle, itinerary);
                                itinerary._container.insertBefore(collapseBtn, itinerary._container.firstChild)
                            },
                            collapseBtnClass: "leaflet-routing-collapse-btn"
                        },
                        initialize: function(options) {
                            L.setOptions(this, options);
                            this._formatter = this.options.formatter || new L.Routing.Formatter(this.options);
                            this._itineraryBuilder = this.options.itineraryBuilder || new L.Routing.ItineraryBuilder({
                                containerClassName: this.options.itineraryClassName
                            })
                        },
                        onAdd: function(map) {
                            var collapsible = this.options.collapsible;
                            collapsible = collapsible || collapsible === undefined && map.getSize().x <= 640;
                            this._container = L.DomUtil.create("div", "leaflet-routing-container leaflet-bar " + (!this.options.show ? "leaflet-routing-container-hide " : "") + (collapsible ? "leaflet-routing-collapsible " : "") + this.options.containerClassName);
                            this._altContainer = this.createAlternativesContainer();
                            this._container.appendChild(this._altContainer);
                            L.DomEvent.disableClickPropagation(this._container);
                            L.DomEvent.addListener(this._container, "mousewheel", function(e) {
                                L.DomEvent.stopPropagation(e)
                            });
                            if (collapsible) this.options.collapseBtn(this);
                            return this._container
                        },
                        onRemove: function() {},
                        createAlternativesContainer: function() {
                            return L.DomUtil.create("div", "leaflet-routing-alternatives-container")
                        },
                        setAlternatives: function(routes) {
                            var i, alt, altDiv;
                            this._clearAlts();
                            this._routes = routes;
                            for (i = 0; i < this._routes.length; i++) {
                                alt = this._routes[i];
                                altDiv = this._createAlternative(alt, i);
                                this._altContainer.appendChild(altDiv);
                                this._altElements.push(altDiv)
                            }
                            this._selectRoute({
                                route: this._routes[0],
                                alternatives: this._routes.slice(1)
                            });
                            return this
                        },
                        show: function() {
                            L.DomUtil.removeClass(this._container, "leaflet-routing-container-hide")
                        },
                        hide: function() {
                            L.DomUtil.addClass(this._container, "leaflet-routing-container-hide")
                        },
                        _toggle: function() {
                            var collapsed = L.DomUtil.hasClass(this._container, "leaflet-routing-container-hide");
                            this[collapsed ? "show" : "hide"]()
                        },
                        _createAlternative: function(alt, i) {
                            var altDiv = L.DomUtil.create("div", "leaflet-routing-alt " + this.options.alternativeClassName + (i > 0 ? " leaflet-routing-alt-minimized " + this.options.minimizedClassName : "")),
                                template = this.options.summaryTemplate,
                                data = L.extend({
                                    name: alt.name,
                                    distance: this._formatter.formatDistance(alt.summary.totalDistance, this.options.totalDistanceRoundingSensitivity),
                                    time: this._formatter.formatTime(alt.summary.totalTime)
                                }, alt);
                            altDiv.innerHTML = typeof template === "function" ? template(data) : L.Util.template(template, data);
                            L.DomEvent.addListener(altDiv, "click", this._onAltClicked, this);
                            this.on("routeselected", this._selectAlt, this);
                            altDiv.appendChild(this._createItineraryContainer(alt));
                            return altDiv
                        },
                        _clearAlts: function() {
                            var el = this._altContainer;
                            while (el && el.firstChild) el.removeChild(el.firstChild);
                            this._altElements = []
                        },
                        _createItineraryContainer: function(r) {
                            var container = this._itineraryBuilder.createContainer(),
                                steps = this._itineraryBuilder.createStepsContainer(),
                                i, instr,
                                step, distance, text, icon;
                            container.appendChild(steps);
                            for (i = 0; i < r.instructions.length; i++) {
                                instr = r.instructions[i];
                                text = this._formatter.formatInstruction(instr, i);
                                distance = this._formatter.formatDistance(instr.distance);
                                icon = this._formatter.getIconName(instr, i);
                                step = this._itineraryBuilder.createStep(text, distance, icon, steps);
                                this._addRowListeners(step, r.coordinates[instr.index])
                            }
                            return container
                        },
                        _addRowListeners: function(row, coordinate) {
                            L.DomEvent.addListener(row, "mouseover", function() {
                                this._marker = L.circleMarker(coordinate, this.options.pointMarkerStyle).addTo(this._map)
                            }, this);
                            L.DomEvent.addListener(row, "mouseout", function() {
                                if (this._marker) {
                                    this._map.removeLayer(this._marker);
                                    delete this._marker
                                }
                            }, this);
                            L.DomEvent.addListener(row, "click", function(e) {
                                this._map.panTo(coordinate);
                                L.DomEvent.stopPropagation(e)
                            }, this)
                        },
                        _onAltClicked: function(e) {
                            var altElem = e.target || window.event.srcElement;
                            while (!L.DomUtil.hasClass(altElem, "leaflet-routing-alt")) altElem = altElem.parentElement;
                            var j = this._altElements.indexOf(altElem);
                            var alts = this._routes.slice();
                            var route = alts.splice(j, 1)[0];
                            this.fire("routeselected", {
                                route: route,
                                alternatives: alts
                            })
                        },
                        _selectAlt: function(e) {
                            var altElem, j, n, classFn;
                            altElem = this._altElements[e.route.routesIndex];
                            if (L.DomUtil.hasClass(altElem, "leaflet-routing-alt-minimized"))
                                for (j = 0; j < this._altElements.length; j++) {
                                    n = this._altElements[j];
                                    classFn = j === e.route.routesIndex ? "removeClass" : "addClass";
                                    L.DomUtil[classFn](n, "leaflet-routing-alt-minimized");
                                    if (this.options.minimizedClassName) L.DomUtil[classFn](n, this.options.minimizedClassName);
                                    if (j !== e.route.routesIndex) n.scrollTop = 0
                                }
                            L.DomEvent.stop(e)
                        },
                        _selectRoute: function(routes) {
                            if (this._marker) {
                                this._map.removeLayer(this._marker);
                                delete this._marker
                            }
                            this.fire("routeselected", routes)
                        }
                    });
                    L.Routing.itinerary = function(options) {
                        return new L.Routing.Itinerary(options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.Formatter": 6,
            "./L.Routing.ItineraryBuilder": 9
        }],
        9: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.Routing.ItineraryBuilder = L.Class.extend({
                        options: {
                            containerClassName: ""
                        },
                        initialize: function(options) {
                            L.setOptions(this, options)
                        },
                        createContainer: function(className) {
                            var table = L.DomUtil.create("table", className || ""),
                                colgroup = L.DomUtil.create("colgroup", "", table);
                            L.DomUtil.create("col", "leaflet-routing-instruction-icon", colgroup);
                            L.DomUtil.create("col", "leaflet-routing-instruction-text", colgroup);
                            L.DomUtil.create("col", "leaflet-routing-instruction-distance", colgroup);
                            return table
                        },
                        createStepsContainer: function() {
                            return L.DomUtil.create("tbody", "")
                        },
                        createStep: function(text, distance, icon, steps) {
                            var row = L.DomUtil.create("tr", "", steps),
                                span, td;
                            td = L.DomUtil.create("td", "", row);
                            span = L.DomUtil.create("span", "leaflet-routing-icon leaflet-routing-icon-" + icon, td);
                            td.appendChild(span);
                            td = L.DomUtil.create("td", "", row);
                            td.appendChild(document.createTextNode(text));
                            td = L.DomUtil.create("td", "", row);
                            td.appendChild(document.createTextNode(distance));
                            return row
                        }
                    });
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {}],
        10: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.Routing.Line = L.LayerGroup.extend({
                        includes: ((typeof L.Evented !== 'undefined' && L.Evented.prototype) || L.Mixin.Events),
                        options: {
                            styles: [{
                                color: "black",
                                opacity: .15,
                                weight: 9
                            }, {
                                color: "white",
                                opacity: .8,
                                weight: 6
                            }, {
                                color: "red",
                                opacity: 1,
                                weight: 2
                            }],
                            missingRouteStyles: [{
                                color: "black",
                                opacity: .15,
                                weight: 7
                            }, {
                                color: "white",
                                opacity: .6,
                                weight: 4
                            }, {
                                color: "gray",
                                opacity: .8,
                                weight: 2,
                                dashArray: "7,12"
                            }],
                            addWaypoints: true,
                            extendToWaypoints: true,
                            missingRouteTolerance: 10
                        },
                        initialize: function(route, options) {
                            L.setOptions(this, options);
                            L.LayerGroup.prototype.initialize.call(this, options);
                            this._route = route;
                            if (this.options.extendToWaypoints) this._extendToWaypoints();
                            this._addSegment(route.coordinates, this.options.styles, this.options.addWaypoints)
                        },
                        getBounds: function() {
                            return L.latLngBounds(this._route.coordinates)
                        },
                        _findWaypointIndices: function() {
                            var wps = this._route.inputWaypoints,
                                indices = [],
                                i;
                            for (i = 0; i < wps.length; i++) indices.push(this._findClosestRoutePoint(wps[i].latLng));
                            return indices
                        },
                        _findClosestRoutePoint: function(latlng) {
                            var minDist = Number.MAX_VALUE,
                                minIndex, i, d;
                            for (i = this._route.coordinates.length - 1; i >= 0; i--) {
                                d = latlng.distanceTo(this._route.coordinates[i]);
                                if (d < minDist) {
                                    minIndex = i;
                                    minDist = d
                                }
                            }
                            return minIndex
                        },
                        _extendToWaypoints: function() {
                            var wps = this._route.inputWaypoints,
                                wpIndices = this._getWaypointIndices(),
                                i, wpLatLng, routeCoord;
                            for (i = 0; i < wps.length; i++) {
                                wpLatLng = wps[i].latLng;
                                routeCoord = L.latLng(this._route.coordinates[wpIndices[i]]);
                                if (wpLatLng.distanceTo(routeCoord) > this.options.missingRouteTolerance) this._addSegment([wpLatLng, routeCoord], this.options.missingRouteStyles)
                            }
                        },
                        _addSegment: function(coords, styles, mouselistener) {
                            var i, pl;
                            for (i = 0; i < styles.length; i++) {
                                pl = L.polyline(coords, styles[i]);
                                this.addLayer(pl);
                                if (mouselistener) pl.on("mousedown", this._onLineTouched, this)
                            }
                        },
                        _findNearestWpBefore: function(i) {
                            var wpIndices = this._getWaypointIndices(),
                                j = wpIndices.length - 1;
                            while (j >= 0 && wpIndices[j] > i) j--;
                            return j
                        },
                        _onLineTouched: function(e) {
                            var afterIndex = this._findNearestWpBefore(this._findClosestRoutePoint(e.latlng));
                            this.fire("linetouched", {
                                afterIndex: afterIndex,
                                latlng: e.latlng
                            })
                        },
                        _getWaypointIndices: function() {
                            if (!this._wpIndices) this._wpIndices = this._route.waypointIndices || this._findWaypointIndices();
                            return this._wpIndices
                        }
                    });
                    L.Routing.line = function(route, options) {
                        return new L.Routing.Line(route, options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {}],
        11: [function(require, module, exports) {
            (function() {
                L.Routing = L.Routing || {};
                L.Routing.Localization = L.Class.extend({
                    initialize: function(langs) {
                        this._langs = L.Util.isArray(langs) ? langs : [langs, "en"];
                        for (var i = 0, l = this._langs.length; i < l; i++)
                            if (!L.Routing.Localization[this._langs[i]]) throw new Error('No localization for language "' + this._langs[i] + '".');
                    },
                    localize: function(keys) {
                        var dict, key, value;
                        keys = L.Util.isArray(keys) ? keys : [keys];
                        for (var i = 0, l = this._langs.length; i < l; i++) {
                            dict = L.Routing.Localization[this._langs[i]];
                            for (var j = 0, nKeys = keys.length; dict && j < nKeys; j++) {
                                key = keys[j];
                                value = dict[key];
                                dict = value
                            }
                            if (value) return value
                        }
                    }
                });
                L.Routing.Localization = L.extend(L.Routing.Localization, {
                    'en': {
                        directions: {
                            N: 'north',
                            NE: 'northeast',
                            E: 'east',
                            SE: 'southeast',
                            S: 'south',
                            SW: 'southwest',
                            W: 'west',
                            NW: 'northwest',
                            SlightRight: 'slight right',
                            Right: 'right',
                            SharpRight: 'sharp right',
                            SlightLeft: 'slight left',
                            Left: 'left',
                            SharpLeft: 'sharp left',
                            Uturn: 'Turn around'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Head {dir}', ' on {road}'],
                            'Continue': ['Continue {dir}'],
                            'TurnAround': ['Turn around'],
                            'WaypointReached': ['Waypoint reached'],
                            'Roundabout': ['Take the {exitStr} exit in the roundabout', ' onto {road}'],
                            'DestinationReached': ['Destination reached'],
                            'Fork': ['At the fork, turn {modifier}', ' onto {road}'],
                            'Merge': ['Merge {modifier}', ' onto {road}'],
                            'OnRamp': ['Turn {modifier} on the ramp', ' onto {road}'],
                            'OffRamp': ['Take the ramp on the {modifier}', ' onto {road}'],
                            'EndOfRoad': ['Turn {modifier} at the end of the road', ' onto {road}'],
                            'Onto': 'onto {road}'
                        },
                        formatOrder: function(n) {
                            var i = n % 10 - 1,
                                suffix = ['st', 'nd', 'rd'];

                            return suffix[i] ? n + suffix[i] : n + 'th';
                        },
                        ui: {
                            startPlaceholder: 'Start',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'End'
                        },
                        units: {
                            meters: 'm',
                            kilometers: 'km',
                            yards: 'yd',
                            miles: 'mi',
                            hours: 'h',
                            minutes: 'min',
                            seconds: 's'
                        }
                    },

                    'de': {
                        directions: {
                            N: 'Norden',
                            NE: 'Nordosten',
                            E: 'Osten',
                            SE: 'Südosten',
                            S: 'Süden',
                            SW: 'Südwesten',
                            W: 'Westen',
                            NW: 'Nordwesten',
                            SlightRight: 'leicht rechts',
                            Right: 'rechts',
                            SharpRight: 'scharf rechts',
                            SlightLeft: 'leicht links',
                            Left: 'links',
                            SharpLeft: 'scharf links',
                            Uturn: 'Wenden'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Richtung {dir}', ' auf {road}'],
                            'Continue': ['Geradeaus Richtung {dir}', ' auf {road}'],
                            'SlightRight': ['Leicht rechts abbiegen', ' auf {road}'],
                            'Right': ['Rechts abbiegen', ' auf {road}'],
                            'SharpRight': ['Scharf rechts abbiegen', ' auf {road}'],
                            'TurnAround': ['Wenden'],
                            'SharpLeft': ['Scharf links abbiegen', ' auf {road}'],
                            'Left': ['Links abbiegen', ' auf {road}'],
                            'SlightLeft': ['Leicht links abbiegen', ' auf {road}'],
                            'WaypointReached': ['Zwischenhalt erreicht'],
                            'Roundabout': ['Nehmen Sie die {exitStr} Ausfahrt im Kreisverkehr', ' auf {road}'],
                            'DestinationReached': ['Sie haben ihr Ziel erreicht'],
                            'Fork': ['An der Kreuzung {modifier}', ' auf {road}'],
                            'Merge': ['Fahren Sie {modifier} weiter', ' auf {road}'],
                            'OnRamp': ['Fahren Sie {modifier} auf die Auffahrt', ' auf {road}'],
                            'OffRamp': ['Nehmen Sie die Ausfahrt {modifier}', ' auf {road}'],
                            'EndOfRoad': ['Fahren Sie {modifier} am Ende der Straße', ' auf {road}'],
                            'Onto': 'auf {road}'
                        },
                        formatOrder: function(n) {
                            return n + '.';
                        },
                        ui: {
                            startPlaceholder: 'Start',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Ziel'
                        }
                    },

                    'sv': {
                        directions: {
                            N: 'norr',
                            NE: 'nordost',
                            E: 'öst',
                            SE: 'sydost',
                            S: 'syd',
                            SW: 'sydväst',
                            W: 'väst',
                            NW: 'nordväst',
                            SlightRight: 'svagt höger',
                            Right: 'höger',
                            SharpRight: 'skarpt höger',
                            SlightLeft: 'svagt vänster',
                            Left: 'vänster',
                            SharpLeft: 'skarpt vänster',
                            Uturn: 'Vänd'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Åk åt {dir}', ' till {road}'],
                            'Continue': ['Fortsätt {dir}'],
                            'SlightRight': ['Svagt höger', ' till {road}'],
                            'Right': ['Sväng höger', ' till {road}'],
                            'SharpRight': ['Skarpt höger', ' till {road}'],
                            'TurnAround': ['Vänd'],
                            'SharpLeft': ['Skarpt vänster', ' till {road}'],
                            'Left': ['Sväng vänster', ' till {road}'],
                            'SlightLeft': ['Svagt vänster', ' till {road}'],
                            'WaypointReached': ['Viapunkt nådd'],
                            'Roundabout': ['Tag {exitStr} avfarten i rondellen', ' till {road}'],
                            'DestinationReached': ['Framme vid resans mål'],
                            'Fork': ['Tag av {modifier}', ' till {road}'],
                            'Merge': ['Anslut {modifier} ', ' till {road}'],
                            'OnRamp': ['Tag påfarten {modifier}', ' till {road}'],
                            'OffRamp': ['Tag avfarten {modifier}', ' till {road}'],
                            'EndOfRoad': ['Sväng {modifier} vid vägens slut', ' till {road}'],
                            'Onto': 'till {road}'
                        },
                        formatOrder: function(n) {
                            return ['första', 'andra', 'tredje', 'fjärde', 'femte',
                                'sjätte', 'sjunde', 'åttonde', 'nionde', 'tionde'
                                /* Can't possibly be more than ten exits, can there? */
                            ][n - 1];
                        },
                        ui: {
                            startPlaceholder: 'Från',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Till'
                        }
                    },

                    'es': {
                        directions: {
                            N: 'norte',
                            NE: 'noreste',
                            E: 'este',
                            SE: 'sureste',
                            S: 'sur',
                            SW: 'suroeste',
                            W: 'oeste',
                            NW: 'noroeste',
                            SlightRight: 'leve giro a la derecha',
                            Right: 'derecha',
                            SharpRight: 'giro pronunciado a la derecha',
                            SlightLeft: 'leve giro a la izquierda',
                            Left: 'izquierda',
                            SharpLeft: 'giro pronunciado a la izquierda',
                            Uturn: 'media vuelta'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Derecho {dir}', ' sobre {road}'],
                            'Continue': ['Continuar {dir}', ' en {road}'],
                            'TurnAround': ['Dar vuelta'],
                            'WaypointReached': ['Llegó a un punto del camino'],
                            'Roundabout': ['Tomar {exitStr} salida en la rotonda', ' en {road}'],
                            'DestinationReached': ['Llegada a destino'],
                            'Fork': ['En el cruce gira a {modifier}', ' hacia {road}'],
                            'Merge': ['Incorpórate {modifier}', ' hacia {road}'],
                            'OnRamp': ['Gira {modifier} en la salida', ' hacia {road}'],
                            'OffRamp': ['Toma la salida {modifier}', ' hacia {road}'],
                            'EndOfRoad': ['Gira {modifier} al final de la carretera', ' hacia {road}'],
                            'Onto': 'hacia {road}'
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Inicio',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Destino'
                        },
                        units: {
                            meters: 'm',
                            kilometers: 'km',
                            yards: 'yd',
                            miles: 'mi',
                            hours: 'h',
                            minutes: 'min',
                            seconds: 's'
                        }
                    },
                    'sp': {
                        directions: {
                            N: 'norte',
                            NE: 'noreste',
                            E: 'este',
                            SE: 'sureste',
                            S: 'sur',
                            SW: 'suroeste',
                            W: 'oeste',
                            NW: 'noroeste',
                            SlightRight: 'leve giro a la derecha',
                            Right: 'derecha',
                            SharpRight: 'giro pronunciado a la derecha',
                            SlightLeft: 'leve giro a la izquierda',
                            Left: 'izquierda',
                            SharpLeft: 'giro pronunciado a la izquierda',
                            Uturn: 'media vuelta'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Derecho {dir}', ' sobre {road}'],
                            'Continue': ['Continuar {dir}', ' en {road}'],
                            'TurnAround': ['Dar vuelta'],
                            'WaypointReached': ['Llegó a un punto del camino'],
                            'Roundabout': ['Tomar {exitStr} salida en la rotonda', ' en {road}'],
                            'DestinationReached': ['Llegada a destino'],
                            'Fork': ['En el cruce gira a {modifier}', ' hacia {road}'],
                            'Merge': ['Incorpórate {modifier}', ' hacia {road}'],
                            'OnRamp': ['Gira {modifier} en la salida', ' hacia {road}'],
                            'OffRamp': ['Toma la salida {modifier}', ' hacia {road}'],
                            'EndOfRoad': ['Gira {modifier} al final de la carretera', ' hacia {road}'],
                            'Onto': 'hacia {road}'
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Inicio',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Destino'
                        },
                        units: {
                            meters: 'm',
                            kilometers: 'km',
                            yards: 'yd',
                            miles: 'mi',
                            hours: 'h',
                            minutes: 'min',
                            seconds: 's'
                        }
                    },

                    'nl': {
                        directions: {
                            N: 'noordelijke',
                            NE: 'noordoostelijke',
                            E: 'oostelijke',
                            SE: 'zuidoostelijke',
                            S: 'zuidelijke',
                            SW: 'zuidewestelijke',
                            W: 'westelijke',
                            NW: 'noordwestelijke'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Vertrek in {dir} richting', ' de {road} op'],
                            'Continue': ['Ga in {dir} richting', ' de {road} op'],
                            'SlightRight': ['Volg de weg naar rechts', ' de {road} op'],
                            'Right': ['Ga rechtsaf', ' de {road} op'],
                            'SharpRight': ['Ga scherpe bocht naar rechts', ' de {road} op'],
                            'TurnAround': ['Keer om'],
                            'SharpLeft': ['Ga scherpe bocht naar links', ' de {road} op'],
                            'Left': ['Ga linksaf', ' de {road} op'],
                            'SlightLeft': ['Volg de weg naar links', ' de {road} op'],
                            'WaypointReached': ['Aangekomen bij tussenpunt'],
                            'Roundabout': ['Neem de {exitStr} afslag op de rotonde', ' de {road} op'],
                            'DestinationReached': ['Aangekomen op eindpunt'],
                        },
                        formatOrder: function(n) {
                            if (n === 1 || n >= 20) {
                                return n + 'ste';
                            } else {
                                return n + 'de';
                            }
                        },
                        ui: {
                            startPlaceholder: 'Vertrekpunt',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Bestemming'
                        }
                    },
                    'fr': {
                        directions: {
                            N: 'nord',
                            NE: 'nord-est',
                            E: 'est',
                            SE: 'sud-est',
                            S: 'sud',
                            SW: 'sud-ouest',
                            W: 'ouest',
                            NW: 'nord-ouest'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Tout droit au {dir}', ' sur {road}'],
                            'Continue': ['Continuer au {dir}', ' sur {road}'],
                            'SlightRight': ['Légèrement à droite', ' sur {road}'],
                            'Right': ['A droite', ' sur {road}'],
                            'SharpRight': ['Complètement à droite', ' sur {road}'],
                            'TurnAround': ['Faire demi-tour'],
                            'SharpLeft': ['Complètement à gauche', ' sur {road}'],
                            'Left': ['A gauche', ' sur {road}'],
                            'SlightLeft': ['Légèrement à gauche', ' sur {road}'],
                            'WaypointReached': ['Point d\'étape atteint'],
                            'Roundabout': ['Au rond-point, prenez la {exitStr} sortie', ' sur {road}'],
                            'DestinationReached': ['Destination atteinte'],
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Départ',
                            viaPlaceholder: 'Intermédiaire {viaNumber}',
                            endPlaceholder: 'Arrivée'
                        }
                    },
                    'it': {
                        directions: {
                            N: 'nord',
                            NE: 'nord-est',
                            E: 'est',
                            SE: 'sud-est',
                            S: 'sud',
                            SW: 'sud-ovest',
                            W: 'ovest',
                            NW: 'nord-ovest'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Dritto verso {dir}', ' su {road}'],
                            'Continue': ['Continuare verso {dir}', ' su {road}'],
                            'SlightRight': ['Mantenere la destra', ' su {road}'],
                            'Right': ['A destra', ' su {road}'],
                            'SharpRight': ['Strettamente a destra', ' su {road}'],
                            'TurnAround': ['Fare inversione di marcia'],
                            'SharpLeft': ['Strettamente a sinistra', ' su {road}'],
                            'Left': ['A sinistra', ' sur {road}'],
                            'SlightLeft': ['Mantenere la sinistra', ' su {road}'],
                            'WaypointReached': ['Punto di passaggio raggiunto'],
                            'Roundabout': ['Alla rotonda, prendere la {exitStr} uscita'],
                            'DestinationReached': ['Destinazione raggiunta'],
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Partenza',
                            viaPlaceholder: 'Intermedia {viaNumber}',
                            endPlaceholder: 'Destinazione'
                        }
                    },
                    'pt': {
                        directions: {
                            N: 'norte',
                            NE: 'nordeste',
                            E: 'leste',
                            SE: 'sudeste',
                            S: 'sul',
                            SW: 'sudoeste',
                            W: 'oeste',
                            NW: 'noroeste',
                            SlightRight: 'curva ligeira a direita',
                            Right: 'direita',
                            SharpRight: 'curva fechada a direita',
                            SlightLeft: 'ligeira a esquerda',
                            Left: 'esquerda',
                            SharpLeft: 'curva fechada a esquerda',
                            Uturn: 'Meia volta'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Siga {dir}', ' na {road}'],
                            'Continue': ['Continue {dir}', ' na {road}'],
                            'SlightRight': ['Curva ligeira a direita', ' na {road}'],
                            'Right': ['Curva a direita', ' na {road}'],
                            'SharpRight': ['Curva fechada a direita', ' na {road}'],
                            'TurnAround': ['Retorne'],
                            'SharpLeft': ['Curva fechada a esquerda', ' na {road}'],
                            'Left': ['Curva a esquerda', ' na {road}'],
                            'SlightLeft': ['Curva ligueira a esquerda', ' na {road}'],
                            'WaypointReached': ['Ponto de interesse atingido'],
                            'Roundabout': ['Pegue a {exitStr} saída na rotatória', ' na {road}'],
                            'DestinationReached': ['Destino atingido'],
                            'Fork': ['Na encruzilhada, vire a {modifier}', ' na {road}'],
                            'Merge': ['Entre à {modifier}', ' na {road}'],
                            'OnRamp': ['Vire {modifier} na rampa', ' na {road}'],
                            'OffRamp': ['Entre na rampa na {modifier}', ' na {road}'],
                            'EndOfRoad': ['Vire {modifier} no fim da rua', ' na {road}'],
                            'Onto': 'na {road}'
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Origem',
                            viaPlaceholder: 'Intermédio {viaNumber}',
                            endPlaceholder: 'Destino'
                        }
                    },
                    'sk': {
                        directions: {
                            N: 'sever',
                            NE: 'serverovýchod',
                            E: 'východ',
                            SE: 'juhovýchod',
                            S: 'juh',
                            SW: 'juhozápad',
                            W: 'západ',
                            NW: 'serverozápad'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Mierte na {dir}', ' na {road}'],
                            'Continue': ['Pokračujte na {dir}', ' na {road}'],
                            'SlightRight': ['Mierne doprava', ' na {road}'],
                            'Right': ['Doprava', ' na {road}'],
                            'SharpRight': ['Prudko doprava', ' na {road}'],
                            'TurnAround': ['Otočte sa'],
                            'SharpLeft': ['Prudko doľava', ' na {road}'],
                            'Left': ['Doľava', ' na {road}'],
                            'SlightLeft': ['Mierne doľava', ' na {road}'],
                            'WaypointReached': ['Ste v prejazdovom bode.'],
                            'Roundabout': ['Odbočte na {exitStr} výjazde', ' na {road}'],
                            'DestinationReached': ['Prišli ste do cieľa.'],
                        },
                        formatOrder: function(n) {
                            var i = n % 10 - 1,
                                suffix = ['.', '.', '.'];

                            return suffix[i] ? n + suffix[i] : n + '.';
                        },
                        ui: {
                            startPlaceholder: 'Začiatok',
                            viaPlaceholder: 'Cez {viaNumber}',
                            endPlaceholder: 'Koniec'
                        }
                    },
                    'el': {
                        directions: {
                            N: 'βόρεια',
                            NE: 'βορειοανατολικά',
                            E: 'ανατολικά',
                            SE: 'νοτιοανατολικά',
                            S: 'νότια',
                            SW: 'νοτιοδυτικά',
                            W: 'δυτικά',
                            NW: 'βορειοδυτικά'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Κατευθυνθείτε {dir}', ' στην {road}'],
                            'Continue': ['Συνεχίστε {dir}', ' στην {road}'],
                            'SlightRight': ['Ελαφρώς δεξιά', ' στην {road}'],
                            'Right': ['Δεξιά', ' στην {road}'],
                            'SharpRight': ['Απότομη δεξιά στροφή', ' στην {road}'],
                            'TurnAround': ['Κάντε αναστροφή'],
                            'SharpLeft': ['Απότομη αριστερή στροφή', ' στην {road}'],
                            'Left': ['Αριστερά', ' στην {road}'],
                            'SlightLeft': ['Ελαφρώς αριστερά', ' στην {road}'],
                            'WaypointReached': ['Φτάσατε στο σημείο αναφοράς'],
                            'Roundabout': ['Ακολουθήστε την {exitStr} έξοδο στο κυκλικό κόμβο', ' στην {road}'],
                            'DestinationReached': ['Φτάσατε στον προορισμό σας'],
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Αφετηρία',
                            viaPlaceholder: 'μέσω {viaNumber}',
                            endPlaceholder: 'Προορισμός'
                        }
                    },
                    'ca': {
                        directions: {
                            N: 'nord',
                            NE: 'nord-est',
                            E: 'est',
                            SE: 'sud-est',
                            S: 'sud',
                            SW: 'sud-oest',
                            W: 'oest',
                            NW: 'nord-oest',
                            SlightRight: 'lleu gir a la dreta',
                            Right: 'dreta',
                            SharpRight: 'gir pronunciat a la dreta',
                            SlightLeft: 'gir pronunciat a l\'esquerra',
                            Left: 'esquerra',
                            SharpLeft: 'lleu gir a l\'esquerra',
                            Uturn: 'mitja volta'
                        },
                        instructions: {
                            'Head': ['Recte {dir}', ' sobre {road}'],
                            'Continue': ['Continuar {dir}'],
                            'TurnAround': ['Donar la volta'],
                            'WaypointReached': ['Ha arribat a un punt del camí'],
                            'Roundabout': ['Agafar {exitStr} sortida a la rotonda', ' a {road}'],
                            'DestinationReached': ['Arribada al destí'],
                            'Fork': ['A la cruïlla gira a la {modifier}', ' cap a {road}'],
                            'Merge': ['Incorpora\'t {modifier}', ' a {road}'],
                            'OnRamp': ['Gira {modifier} a la sortida', ' cap a {road}'],
                            'OffRamp': ['Pren la sortida {modifier}', ' cap a {road}'],
                            'EndOfRoad': ['Gira {modifier} al final de la carretera', ' cap a {road}'],
                            'Onto': 'cap a {road}'
                        },
                        formatOrder: function(n) {
                            return n + 'º';
                        },
                        ui: {
                            startPlaceholder: 'Origen',
                            viaPlaceholder: 'Via {viaNumber}',
                            endPlaceholder: 'Destí'
                        },
                        units: {
                            meters: 'm',
                            kilometers: 'km',
                            yards: 'yd',
                            miles: 'mi',
                            hours: 'h',
                            minutes: 'min',
                            seconds: 's'
                        }
                    },
                    'ru': {
                        directions: {
                            N: 'север',
                            NE: 'северовосток',
                            E: 'восток',
                            SE: 'юговосток',
                            S: 'юг',
                            SW: 'югозапад',
                            W: 'запад',
                            NW: 'северозапад',
                            SlightRight: 'плавно направо',
                            Right: 'направо',
                            SharpRight: 'резко направо',
                            SlightLeft: 'плавно налево',
                            Left: 'налево',
                            SharpLeft: 'резко налево',
                            Uturn: 'развернуться'
                        },
                        instructions: {
                            'Head': ['Начать движение на {dir}', ' по {road}'],
                            'Continue': ['Продолжать движение на {dir}', ' по {road}'],
                            'SlightRight': ['Плавный поворот направо', ' на {road}'],
                            'Right': ['Направо', ' на {road}'],
                            'SharpRight': ['Резкий поворот направо', ' на {road}'],
                            'TurnAround': ['Развернуться'],
                            'SharpLeft': ['Резкий поворот налево', ' на {road}'],
                            'Left': ['Поворот налево', ' на {road}'],
                            'SlightLeft': ['Плавный поворот налево', ' на {road}'],
                            'WaypointReached': ['Точка достигнута'],
                            'Roundabout': ['{exitStr} съезд с кольца', ' на {road}'],
                            'DestinationReached': ['Окончание маршрута'],
                            'Fork': ['На развилке поверните {modifier}', ' на {road}'],
                            'Merge': ['Перестройтесь {modifier}', ' на {road}'],
                            'OnRamp': ['Поверните {modifier} на съезд', ' на {road}'],
                            'OffRamp': ['Съезжайте на {modifier}', ' на {road}'],
                            'EndOfRoad': ['Поверните {modifier} в конце дороги', ' на {road}'],
                            'Onto': 'на {road}'
                        },
                        formatOrder: function(n) {
                            return n + '-й';
                        },
                        ui: {
                            startPlaceholder: 'Начало',
                            viaPlaceholder: 'Через {viaNumber}',
                            endPlaceholder: 'Конец'
                        },
                        units: {
                            meters: 'м',
                            kilometers: 'км',
                            yards: 'ярд',
                            miles: 'ми',
                            hours: 'ч',
                            minutes: 'м',
                            seconds: 'с'
                        }
                    },

                    'pl': {
                        directions: {
                            N: 'północ',
                            NE: 'północny wschód',
                            E: 'wschód',
                            SE: 'południowy wschód',
                            S: 'południe',
                            SW: 'południowy zachód',
                            W: 'zachód',
                            NW: 'północny zachód',
                            SlightRight: 'lekko w prawo',
                            Right: 'w prawo',
                            SharpRight: 'ostro w prawo',
                            SlightLeft: 'lekko w lewo',
                            Left: 'w lewo',
                            SharpLeft: 'ostro w lewo',
                            Uturn: 'zawróć'
                        },
                        instructions: {
                            // instruction, postfix if the road is named
                            'Head': ['Kieruj się na {dir}', ' na {road}'],
                            'Continue': ['Jedź dalej przez {dir}'],
                            'TurnAround': ['Zawróć'],
                            'WaypointReached': ['Punkt pośredni'],
                            'Roundabout': ['Wyjedź {exitStr} zjazdem na rondzie', ' na {road}'],
                            'DestinationReached': ['Dojechano do miejsca docelowego'],
                            'Fork': ['Na rozwidleniu {modifier}', ' na {road}'],
                            'Merge': ['Zjedź {modifier}', ' na {road}'],
                            'OnRamp': ['Wjazd {modifier}', ' na {road}'],
                            'OffRamp': ['Zjazd {modifier}', ' na {road}'],
                            'EndOfRoad': ['Skręć {modifier} na końcu drogi', ' na {road}'],
                            'Onto': 'na {road}'
                        },
                        formatOrder: function(n) {
                            return n + '.';
                        },
                        ui: {
                            startPlaceholder: 'Początek',
                            viaPlaceholder: 'Przez {viaNumber}',
                            endPlaceholder: 'Koniec'
                        },
                        units: {
                            meters: 'm',
                            kilometers: 'km',
                            yards: 'yd',
                            miles: 'mi',
                            hours: 'godz',
                            minutes: 'min',
                            seconds: 's'
                        }
                    },
                    'uk': {
                        directions: {
                            N: 'північ',
                            NE: 'північний схід',
                            E: 'схід',
                            SE: 'південний схід',
                            S: 'південь',
                            SW: 'південний захід',
                            W: 'захід',
                            NW: 'північний захід',
                            SlightRight: 'плавно направо',
                            Right: 'направо',
                            SharpRight: 'різко направо',
                            SlightLeft: 'плавно наліво',
                            Left: 'наліво',
                            SharpLeft: 'різко наліво',
                            Uturn: 'розвернутися',
                        },
                        instructions: {
                            'Head': ['Почати рух на {dir}', 'по {road}'],
                            'Continue': ['Продовжувати рух на {dir}', 'по {road}'],
                            'SlightRight': ['Плавний поворот направо', 'на {road}'],
                            'Right': ['Направо', 'на {road}'],
                            'SharpRight': ['Різкий поворот направо', 'на {road}'],
                            'TurnAround': ['Розгорнутися'],
                            'SharpLeft': ['Різкий поворот наліво', 'на {road}'],
                            'Left': ['Поворот наліво', 'на {road}'],
                            'SlightLeft': ['Плавний поворот наліво', 'на {road}'],
                            'WaypointReached': ['Точка досягнута'],
                            'Roundabout': ["{ExitStr} з'їзд з кільця", 'на {road}'],
                            'DestinationReached': ['Закінчення маршруту'],
                            'Fork': ['На розвилці поверніть {modifier}', 'на {road}'],
                            'Merge': ['Візьміть {modifier}', 'на {road}'],
                            'OnRamp': ["Поверніть {modifier} на з'їзд", 'на {road}'],
                            'OffRamp': ["З'їжджайте на {modifier}", 'на {road}'],
                            'EndOfRoad': ['Поверніть {modifier} в кінці дороги', 'на {road}'],
                            'Onto': 'на {road}'
                        },
                        formatOrder: function(n) {
                            return n + '-й';
                        },
                        ui: {
                            startPlaceholder: 'Початок',
                            viaPlaceholder: 'Через {viaNumber}',
                            endPlaceholder: 'Кінець'
                        },
                        units: {
                            meters: 'м',
                            kilometers: 'км',
                            yards: 'ярд',
                            miles: 'ми',
                            hours: 'г',
                            minutes: 'хв',
                            seconds: 'сек'
                        }
                    }
                });
                module.exports = L.Routing
            })()
        }, {}],
        12: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.OSRMv1"));
                    L.Routing.Mapbox = L.Routing.OSRMv1.extend({
                        options: {
                            serviceUrl: "https://api.mapbox.com/directions/v5",
                            profile: "mapbox/driving",
                            useHints: false
                        },
                        initialize: function(accessToken, options) {
                            L.Routing.OSRMv1.prototype.initialize.call(this, options);
                            this.options.requestParameters = this.options.requestParameters || {};
                            this.options.requestParameters.access_token = accessToken
                        }
                    });
                    L.Routing.mapbox = function(accessToken, options) {
                        return new L.Routing.Mapbox(accessToken, options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.OSRMv1": 13
        }],
        13: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null,
                        corslite = require("corslite"),
                        polyline = require("polyline");
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.Waypoint"));
                    L.Routing.OSRMv1 = L.Class.extend({
                        options: {
                            serviceUrl: "https://router.project-osrm.org/route/v1",
                            profile: "driving",
                            timeout: 30 * 1E3,
                            routingOptions: {
                                alternatives: true,
                                steps: true
                            },
                            polylinePrecision: 5,
                            useHints: true
                        },
                        initialize: function(options) {
                            L.Util.setOptions(this, options);
                            this._hints = {
                                locations: {}
                            }
                        },
                        route: function(waypoints, callback, context, options) {
                            var timedOut = false,
                                wps = [],
                                url, timer, wp, i;
                            options = L.extend({}, this.options.routingOptions, options);
                            url = this.buildRouteUrl(waypoints, options);
                            if (this.options.requestParameters) url += L.Util.getParamString(this.options.requestParameters, url);
                            timer = setTimeout(function() {
                                timedOut = true;
                                callback.call(context || callback, {
                                    status: -1,
                                    message: "OSRM request timed out."
                                })
                            }, this.options.timeout);
                            for (i = 0; i < waypoints.length; i++) {
                                wp = waypoints[i];
                                wps.push(new L.Routing.Waypoint(wp.latLng, wp.name, wp.options))
                            }
                            corslite(url, L.bind(function(err, resp) {
                                var data, errorMessage, statusCode;
                                clearTimeout(timer);
                                if (!timedOut) {
                                    errorMessage = "HTTP request failed: " + err;
                                    statusCode = -1;
                                    if (!err) try {
                                        data = JSON.parse(resp.responseText);
                                        try {
                                            return this._routeDone(data, wps, options, callback, context)
                                        } catch (ex) {
                                            statusCode = -3;
                                            errorMessage = ex.toString()
                                        }
                                    } catch (ex$0) {
                                        statusCode = -2;
                                        errorMessage = "Error parsing OSRM response: " + ex$0.toString()
                                    }
                                    callback.call(context || callback, {
                                        status: statusCode,
                                        message: errorMessage
                                    })
                                }
                            }, this));
                            return this
                        },
                        requiresMoreDetail: function(route, zoom, bounds) {
                            if (!route.properties.isSimplified) return false;
                            var waypoints = route.inputWaypoints,
                                i;
                            for (i = 0; i < waypoints.length; ++i)
                                if (!bounds.contains(waypoints[i].latLng)) return true;
                            return false
                        },
                        _routeDone: function(response, inputWaypoints, options, callback, context) {
                            var alts = [],
                                actualWaypoints, i, route;
                            context = context || callback;
                            if (response.code !== "Ok") {
                                callback.call(context, {
                                    status: response.code
                                });
                                return
                            }
                            actualWaypoints = this._toWaypoints(inputWaypoints, response.waypoints);
                            for (i = 0; i < response.routes.length; i++) {
                                route = this._convertRoute(response.routes[i]);
                                route.inputWaypoints = inputWaypoints;
                                route.waypoints = actualWaypoints;
                                route.properties = {
                                    isSimplified: !options || !options.geometryOnly || options.simplifyGeometry
                                };
                                alts.push(route)
                            }
                            this._saveHintData(response.waypoints, inputWaypoints);
                            callback.call(context, null, alts)
                        },
                        _convertRoute: function(responseRoute) {
                            var result = {
                                    name: "",
                                    coordinates: [],
                                    instructions: [],
                                    summary: {
                                        totalDistance: responseRoute.distance,
                                        totalTime: responseRoute.duration
                                    }
                                },
                                legNames = [],
                                index = 0,
                                legCount = responseRoute.legs.length,
                                hasSteps = responseRoute.legs[0].steps.length > 0,
                                i, j, leg, step, geometry, type, modifier;
                            for (i = 0; i < legCount; i++) {
                                leg = responseRoute.legs[i];
                                legNames.push(leg.summary && leg.summary.charAt(0).toUpperCase() + leg.summary.substring(1));
                                for (j = 0; j < leg.steps.length; j++) {
                                    step = leg.steps[j];
                                    geometry = this._decodePolyline(step.geometry);
                                    result.coordinates.push.apply(result.coordinates, geometry);
                                    type = this._maneuverToInstructionType(step.maneuver, i === legCount - 1);
                                    modifier = this._maneuverToModifier(step.maneuver);
                                    if (type) result.instructions.push({
                                        type: type,
                                        distance: step.distance,
                                        time: step.duration,
                                        road: step.name,
                                        direction: this._bearingToDirection(step.maneuver.bearing_after),
                                        exit: step.maneuver.exit,
                                        index: index,
                                        mode: step.mode,
                                        modifier: modifier
                                    });
                                    index += geometry.length
                                }
                            }
                            result.name = legNames.join(", ");
                            if (!hasSteps) result.coordinates = this._decodePolyline(responseRoute.geometry);
                            return result
                        },
                        _bearingToDirection: function(bearing) {
                            var oct = Math.round(bearing / 45) % 8;
                            return ["N", "NE", "E", "SE", "S", "SW", "W", "NW"][oct]
                        },
                        _maneuverToInstructionType: function(maneuver, lastLeg) {
                            switch (maneuver.type) {
                                case "new name":
                                    return "Continue";
                                case "depart":
                                    return "Head";
                                case "arrive":
                                    return lastLeg ? "DestinationReached" : "WaypointReached";
                                case "roundabout":
                                case "rotary":
                                    return "Roundabout";
                                case "merge":
                                case "fork":
                                case "on ramp":
                                case "off ramp":
                                case "end of road":
                                    return this._camelCase(maneuver.type);
                                default:
                                    return this._camelCase(maneuver.modifier)
                            }
                        },
                        _maneuverToModifier: function(maneuver) {
                            var modifier = maneuver.modifier;
                            switch (maneuver.type) {
                                case "merge":
                                case "fork":
                                case "on ramp":
                                case "off ramp":
                                case "end of road":
                                    modifier = this._leftOrRight(modifier)
                            }
                            return modifier && this._camelCase(modifier)
                        },
                        _camelCase: function(s) {
                            var words = s.split(" "),
                                result = "";
                            for (var i = 0, l = words.length; i < l; i++) result += words[i].charAt(0).toUpperCase() + words[i].substring(1);
                            return result
                        },
                        _leftOrRight: function(d) {
                            return d.indexOf("left") >= 0 ? "Left" : "Right"
                        },
                        _decodePolyline: function(routeGeometry) {
                            var cs = polyline.decode(routeGeometry, this.options.polylinePrecision),
                                result = new Array(cs.length),
                                i;
                            for (i = cs.length - 1; i >= 0; i--) result[i] = L.latLng(cs[i]);
                            return result
                        },
                        _toWaypoints: function(inputWaypoints, vias) {
                            var wps = [],
                                i;
                            for (i = 0; i < vias.length; i++) wps.push(L.Routing.waypoint(L.latLng(vias[i].location), inputWaypoints[i].name, inputWaypoints[i].options));
                            return wps
                        },
                        buildRouteUrl: function(waypoints, options) {
                            var locs = [],
                                hints = [],
                                wp, latLng, computeInstructions, computeAlternative = true;
                            for (var i = 0; i < waypoints.length; i++) {
                                wp = waypoints[i];
                                latLng = wp.latLng;
                                locs.push(latLng.lng + "," + latLng.lat);
                                hints.push(this._hints.locations[this._locationKey(latLng)] || "")
                            }
                            computeInstructions = !(options && options.geometryOnly);
                            return this.options.serviceUrl + "/" + this.options.profile + "/" + locs.join(";") + "?" + (options.geometryOnly ? options.simplifyGeometry ? "" : "overview=full" : "overview=false") + "&alternatives=" + computeAlternative.toString() + "&steps=" + computeInstructions.toString() + (this.options.useHints ? "&hints=" + hints.join(";") : "") + (options.allowUTurns ? "&continue_straight=" + !options.allowUTurns : "")
                        },
                        _locationKey: function(location) {
                            return location.lat + "," + location.lng
                        },
                        _saveHintData: function(actualWaypoints, waypoints) {
                            var loc;
                            this._hints = {
                                locations: {}
                            };
                            for (var i = actualWaypoints.length - 1; i >= 0; i--) {
                                loc = waypoints[i].latLng;
                                this._hints.locations[this._locationKey(loc)] = actualWaypoints[i].hint
                            }
                        }
                    });
                    L.Routing.osrmv1 = function(options) {
                        return new L.Routing.OSRMv1(options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.Waypoint": 15,
            "corslite": 1,
            "polyline": 2
        }],
        14: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.extend(L.Routing, require("./L.Routing.GeocoderElement"));
                    L.extend(L.Routing, require("./L.Routing.Waypoint"));
                    L.Routing.Plan = L.Class.extend({
                        includes: ((typeof L.Evented !== 'undefined' && L.Evented.prototype) || L.Mixin.Events),
                        options: {
                            dragStyles: [{
                                color: "black",
                                opacity: .15,
                                weight: 9
                            }, {
                                color: "white",
                                opacity: .8,
                                weight: 6
                            }, {
                                color: "red",
                                opacity: 1,
                                weight: 2,
                                dashArray: "7,12"
                            }],
                            draggableWaypoints: true,
                            routeWhileDragging: false,
                            addWaypoints: true,
                            reverseWaypoints: false,
                            addButtonClassName: "",
                            language: "en",
                            createGeocoderElement: L.Routing.geocoderElement,
                            createMarker: function(i, wp) {
                                var options = {
                                    draggable: this.draggableWaypoints
                                };
                                if (i == 0) {
                                    options.draggable = false;
                                    options.opacity = 0
                                }
                                var marker = L.marker(wp.latLng, options);
                                return marker
                            },
                            geocodersClassName: ""
                        },
                        initialize: function(waypoints, options) {
                            L.Util.setOptions(this, options);
                            this._waypoints = [];
                            this.setWaypoints(waypoints)
                        },
                        isReady: function() {
                            var i;
                            for (i = 0; i < this._waypoints.length; i++)
                                if (!this._waypoints[i].latLng) return false;
                            return true
                        },
                        getWaypoints: function() {
                            var i, wps = [];
                            for (i = 0; i < this._waypoints.length; i++) wps.push(this._waypoints[i]);
                            return wps
                        },
                        setWaypoints: function(waypoints) {
                            var args = [0, this._waypoints.length].concat(waypoints);
                            this.spliceWaypoints.apply(this, args);
                            return this
                        },
                        spliceWaypoints: function() {
                            var args = [arguments[0],
                                    arguments[1]
                                ],
                                i;
                            for (i = 2; i < arguments.length; i++) args.push(arguments[i] && arguments[i].hasOwnProperty("latLng") ? arguments[i] : L.Routing.waypoint(arguments[i]));
                            [].splice.apply(this._waypoints, args);
                            while (this._waypoints.length < 2) this.spliceWaypoints(this._waypoints.length, 0, null);
                            this._updateMarkers();
                            this._fireChanged.apply(this, args)
                        },
                        onAdd: function(map) {
                            this._map = map;
                            if (!this._map) this._map = jQuery.goMap.map;
                            this._updateMarkers()
                        },
                        onRemove: function() {
                            var i;
                            this._removeMarkers();
                            if (this._newWp)
                                for (i = 0; i < this._newWp.lines.length; i++) this._map.removeLayer(this._newWp.lines[i]);
                            delete this._map
                        },
                        createGeocoders: function() {
                            var container = L.DomUtil.create("div", "leaflet-routing-geocoders " + this.options.geocodersClassName),
                                waypoints = this._waypoints,
                                addWpBtn, goWpInfo, reverseBtn;
                            this._geocoderContainer = container;
                            this._geocoderElems = [];
                            if (this.options.addWaypoints) {
                                addWpBtn = L.DomUtil.create("button", "leaflet-routing-add-waypoint " + this.options.addButtonClassName, container);
                                addWpBtn.setAttribute("type", "button");
                                L.DomEvent.addListener(addWpBtn, "click", function() {
                                    this.spliceWaypoints(waypoints.length, 0, null)
                                }, this)
                            }
                            goWpInfo = L.DomUtil.create("div", "leaflet-routing-search-info ", container);
                            goWpInfo.innerHTML = (typeof geodir_params !== "undefined" ? geodir_params.osmPressEnter : "Press Enter key to search");
                            if (this.options.reverseWaypoints) {
                                reverseBtn = L.DomUtil.create("button", "leaflet-routing-reverse-waypoints", container);
                                reverseBtn.setAttribute("type", "button");
                                L.DomEvent.addListener(reverseBtn, "click", function() {
                                    this._waypoints.reverse();
                                    this.setWaypoints(this._waypoints)
                                }, this)
                            }
                            this._updateGeocoders();
                            this.on("waypointsspliced", this._updateGeocoders);
                            return container
                        },
                        _createGeocoder: function(i) {
                            var geocoder = this.options.createGeocoderElement(this._waypoints[i], i, this._waypoints.length, this.options);
                            geocoder.on("delete", function() {
                                if (i > 0 || this._waypoints.length > 2) this.spliceWaypoints(i, 1);
                                else this.spliceWaypoints(i, 1, new L.Routing.Waypoint)
                            }, this).on("geocoded", function(e) {
                                this._updateMarkers();
                                this._fireChanged();
                                this._focusGeocoder(i + 1);
                                this.fire("waypointgeocoded", {
                                    waypointIndex: i,
                                    waypoint: e.waypoint
                                })
                            }, this).on("reversegeocoded", function(e) {
                                this.fire("waypointgeocoded", {
                                    waypointIndex: i,
                                    waypoint: e.waypoint
                                })
                            }, this);
                            return geocoder
                        },
                        _updateGeocoders: function() {
                            var elems = [],
                                i, geocoderElem;
                            for (i = 0; i < this._geocoderElems.length; i++) this._geocoderContainer.removeChild(this._geocoderElems[i].getContainer());
                            for (i = this._waypoints.length - 1; i >= 0; i--) {
                                geocoderElem = this._createGeocoder(i);
                                this._geocoderContainer.insertBefore(geocoderElem.getContainer(), this._geocoderContainer.firstChild);
                                elems.push(geocoderElem)
                            }
                            this._geocoderElems = elems.reverse()
                        },
                        _removeMarkers: function() {
                            var i;
                            if (jQuery.goMap.gdwpmarkers)
                                for (i = 0; i < jQuery.goMap.gdwpmarkers.length; i++)
                                    if (jQuery.goMap.gdwpmarkers[i]) this._map.removeLayer(jQuery.goMap.gdwpmarkers[i]);
                            jQuery.goMap.gdwpmarkers = []
                        },
                        _updateMarkers: function() {
                            var i, m;
                            if (!this._map) this._map = jQuery.goMap.map;
                            if (!this._map) return;
                            this._removeMarkers();
                            for (i = 0; i < this._waypoints.length; i++) {
                                if (this._waypoints[i].latLng) {
                                    m = this.options.createMarker(i, this._waypoints[i], this._waypoints.length);
                                    if (m) {
                                        m.addTo(this._map);
                                        if (this.options.draggableWaypoints) this._hookWaypointEvents(m, i)
                                    }
                                } else m = null;
                                jQuery.goMap.gdwpmarkers.push(m)
                            }
                        },
                        _fireChanged: function() {
                            this.fire("waypointschanged", {
                                waypoints: this.getWaypoints()
                            });
                            if (arguments.length >= 2) this.fire("waypointsspliced", {
                                index: Array.prototype.shift.call(arguments),
                                nRemoved: Array.prototype.shift.call(arguments),
                                added: arguments
                            })
                        },
                        _hookWaypointEvents: function(m, i, trackMouseMove) {
                            var eventLatLng = function(e) {
                                    return trackMouseMove ? e.latlng : e.target.getLatLng()
                                },
                                dragStart = L.bind(function(e) {
                                    this.fire("waypointdragstart", {
                                        index: i,
                                        latlng: eventLatLng(e)
                                    })
                                }, this),
                                drag = L.bind(function(e) {
                                    this._waypoints[i].latLng = eventLatLng(e);
                                    this.fire("waypointdrag", {
                                        index: i,
                                        latlng: eventLatLng(e)
                                    })
                                }, this),
                                dragEnd = L.bind(function(e) {
                                    this._waypoints[i].latLng = eventLatLng(e);
                                    this._waypoints[i].name = "";
                                    if (this._geocoderElems) this._geocoderElems[i].update(true);
                                    this.fire("waypointdragend", {
                                        index: i,
                                        latlng: eventLatLng(e)
                                    });
                                    this._fireChanged()
                                }, this),
                                mouseMove, mouseUp;
                            if (trackMouseMove) {
                                mouseMove = L.bind(function(e) {
                                    jQuery.goMap.gdwpmarkers[i].setLatLng(e.latlng);
                                    drag(e)
                                }, this);
                                mouseUp = L.bind(function(e) {
                                    this._map.dragging.enable();
                                    this._map.off("mouseup", mouseUp);
                                    this._map.off("mousemove", mouseMove);
                                    dragEnd(e)
                                }, this);
                                this._map.dragging.disable();
                                this._map.on("mousemove", mouseMove);
                                this._map.on("mouseup", mouseUp);
                                dragStart({
                                    latlng: this._waypoints[i].latLng
                                })
                            } else {
                                m.on("dragstart", dragStart);
                                m.on("drag", drag);
                                m.on("dragend", dragEnd)
                            }
                        },
                        dragNewWaypoint: function(e) {
                            var newWpIndex = e.afterIndex + 1;
                            if (this.options.routeWhileDragging) {
                                this.spliceWaypoints(newWpIndex, 0, e.latlng);
                                this._hookWaypointEvents(jQuery.goMap.gdwpmarkers[newWpIndex], newWpIndex, true)
                            } else this._dragNewWaypoint(newWpIndex, e.latlng)
                        },
                        _dragNewWaypoint: function(newWpIndex, initialLatLng) {
                            var wp = new L.Routing.Waypoint(initialLatLng),
                                prevWp = this._waypoints[newWpIndex - 1],
                                nextWp = this._waypoints[newWpIndex],
                                marker = this.options.createMarker(newWpIndex, wp, this._waypoints.length + 1),
                                lines = [],
                                mouseMove = L.bind(function(e) {
                                    var i;
                                    if (marker) marker.setLatLng(e.latlng);
                                    for (i = 0; i < lines.length; i++) lines[i].spliceLatLngs(1, 1, e.latlng)
                                }, this),
                                mouseUp = L.bind(function(e) {
                                    var i;
                                    if (marker) this._map.removeLayer(marker);
                                    for (i = 0; i < lines.length; i++) this._map.removeLayer(lines[i]);
                                    this._map.off("mousemove", mouseMove);
                                    this._map.off("mouseup", mouseUp);
                                    this.spliceWaypoints(newWpIndex, 0, e.latlng)
                                }, this),
                                i;
                            if (marker) marker.addTo(this._map);
                            for (i = 0; i < this.options.dragStyles.length; i++) lines.push(L.polyline([prevWp.latLng, initialLatLng, nextWp.latLng], this.options.dragStyles[i]).addTo(this._map));
                            this._map.on("mousemove", mouseMove);
                            this._map.on("mouseup", mouseUp)
                        },
                        _focusGeocoder: function(i) {
                            if (this._geocoderElems[i]) this._geocoderElems[i].focus();
                            else document.activeElement.blur()
                        }
                    });
                    L.Routing.plan = function(waypoints, options) {
                        return new L.Routing.Plan(waypoints, options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {
            "./L.Routing.GeocoderElement": 7,
            "./L.Routing.Waypoint": 15
        }],
        15: [function(require, module, exports) {
            (function(global) {
                (function() {
                    var L = typeof window !== "undefined" ? window.L : typeof global !== "undefined" ? global.L : null;
                    L.Routing = L.Routing || {};
                    L.Routing.Waypoint = L.Class.extend({
                        options: {
                            allowUTurn: false
                        },
                        initialize: function(latLng, name, options) {
                            L.Util.setOptions(this, options);
                            this.latLng = L.latLng(latLng);
                            this.name = name
                        }
                    });
                    L.Routing.waypoint = function(latLng, name, options) {
                        return new L.Routing.Waypoint(latLng, name, options)
                    };
                    module.exports = L.Routing
                })()
            }).call(this, typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
        }, {}]
    }, {}, [4])(4)
});
(function(factory) {
    var L;
    if (typeof define === "function" && define.amd) define(["leaflet"], factory);
    else if (typeof module !== "undefined") {
        L = require("leaflet");
        module.exports = factory(L)
    } else {
        if (typeof window.L === "undefined") throw "Leaflet must be loaded first";
        factory(window.L)
    }
})(function(L) {
    L.Control.Geocoder = L.Control.extend({
        options: {
            showResultIcons: false,
            collapsed: true,
            expand: "click",
            position: "topright",
            placeholder: "Search...",
            errorMessage: "Nothing found."
        },
        _callbackId: 0,
        initialize: function(options) {
            L.Util.setOptions(this, options);
            if (!this.options.geocoder) this.options.geocoder = new L.Control.Geocoder.Nominatim
        },
        onAdd: function(map) {
            var className = "leaflet-control-geocoder",
                container = L.DomUtil.create("div", className),
                icon = L.DomUtil.create("div", "leaflet-control-geocoder-icon", container),
                form = this._form = L.DomUtil.create("form", className + "-form", container),
                input;
            this._map = map;
            this._container = container;
            input = this._input = L.DomUtil.create("input");
            input.type = "text";
            input.placeholder = this.options.placeholder;
            L.DomEvent.addListener(input, "keydown", this._keydown, this);
            this._errorElement = document.createElement("div");
            this._errorElement.className = className + "-form-no-error";
            this._errorElement.innerHTML = this.options.errorMessage;
            this._alts = L.DomUtil.create("ul", className + "-alternatives leaflet-control-geocoder-alternatives-minimized");
            form.appendChild(input);
            form.appendChild(this._errorElement);
            container.appendChild(this._alts);
            L.DomEvent.addListener(form, "submit", this._geocode, this);
            if (this.options.collapsed)
                if (this.options.expand === "click") L.DomEvent.addListener(icon, "click", function(e) {
                    if (e.button === 0 && e.detail === 1) this._toggle()
                }, this);
                else {
                    L.DomEvent.addListener(icon, "mouseover", this._expand, this);
                    L.DomEvent.addListener(icon, "mouseout", this._collapse, this);
                    this._map.on("movestart", this._collapse, this)
                }
            else this._expand();
            L.DomEvent.disableClickPropagation(container);
            return container
        },
        _geocodeResult: function(results) {
            L.DomUtil.removeClass(this._container, "leaflet-control-geocoder-throbber");
            if (results.length === 1) this._geocodeResultSelected(results[0]);
            else if (results.length > 0) {
                this._alts.innerHTML = "";
                this._results = results;
                L.DomUtil.removeClass(this._alts, "leaflet-control-geocoder-alternatives-minimized");
                for (var i = 0; i < results.length; i++) this._alts.appendChild(this._createAlt(results[i], i))
            } else L.DomUtil.addClass(this._errorElement, "leaflet-control-geocoder-error")
        },
        markGeocode: function(result) {
            this._map.fitBounds(result.bbox);
            if (this._geocodeMarker) this._map.removeLayer(this._geocodeMarker);
            this._geocodeMarker = (new L.Marker(result.center)).bindPopup(result.html || result.name).addTo(this._map).openPopup();
            return this
        },
        _geocode: function(event) {
            L.DomEvent.preventDefault(event);
            L.DomUtil.addClass(this._container, "leaflet-control-geocoder-throbber");
            this._clearResults();
            this.options.geocoder.geocode(this._input.value, this._geocodeResult, this);
            return false
        },
        _geocodeResultSelected: function(result) {
            if (this.options.collapsed) this._collapse();
            else this._clearResults();
            this.markGeocode(result)
        },
        _toggle: function() {
            if (this._container.className.indexOf("leaflet-control-geocoder-expanded") >= 0) this._collapse();
            else this._expand()
        },
        _expand: function() {
            L.DomUtil.addClass(this._container, "leaflet-control-geocoder-expanded");
            this._input.select()
        },
        _collapse: function() {
            this._container.className = this._container.className.replace(" leaflet-control-geocoder-expanded", "");
            L.DomUtil.addClass(this._alts, "leaflet-control-geocoder-alternatives-minimized");
            L.DomUtil.removeClass(this._errorElement, "leaflet-control-geocoder-error")
        },
        _clearResults: function() {
            L.DomUtil.addClass(this._alts, "leaflet-control-geocoder-alternatives-minimized");
            this._selection = null;
            L.DomUtil.removeClass(this._errorElement, "leaflet-control-geocoder-error")
        },
        _createAlt: function(result, index) {
            var li = document.createElement("li"),
                a = L.DomUtil.create("a", "", li),
                icon = this.options.showResultIcons && result.icon ? L.DomUtil.create("img", "", a) : null,
                text = result.html ? undefined : document.createTextNode(result.name);
            if (icon) icon.src = result.icon;
            a.href = "#";
            a.setAttribute("data-result-index", index);
            if (result.html) a.innerHTML = result.html;
            else a.appendChild(text);
            L.DomEvent.addListener(li, "click", function clickHandler(e) {
                L.DomEvent.preventDefault(e);
                this._geocodeResultSelected(result)
            }, this);
            return li
        },
        _keydown: function(e) {
            var _this = this,
                select = function select(dir) {
                    if (_this._selection) {
                        L.DomUtil.removeClass(_this._selection.firstChild, "leaflet-control-geocoder-selected");
                        _this._selection = _this._selection[dir > 0 ? "nextSibling" : "previousSibling"]
                    }
                    if (!_this._selection) _this._selection = _this._alts[dir > 0 ? "firstChild" : "lastChild"];
                    if (_this._selection) L.DomUtil.addClass(_this._selection.firstChild, "leaflet-control-geocoder-selected")
                };
            switch (e.keyCode) {
                case 27:
                    this._collapse();
                    break;
                case 38:
                    select(-1);
                    L.DomEvent.preventDefault(e);
                    break;
                case 40:
                    select(1);
                    L.DomEvent.preventDefault(e);
                    break;
                case 13:
                    if (this._selection) {
                        var index = parseInt(this._selection.firstChild.getAttribute("data-result-index"), 10);
                        this._geocodeResultSelected(this._results[index]);
                        this._clearResults();
                        L.DomEvent.preventDefault(e)
                    }
            }
            return true
        }
    });
    L.Control.geocoder = function(id, options) {
        return new L.Control.Geocoder(id, options)
    };
    L.Control.Geocoder.callbackId = 0;
    L.Control.Geocoder.jsonp = function(url, params, callback, context, jsonpParam) {
        var callbackId = "_l_geocoder_" + L.Control.Geocoder.callbackId++;
        params[jsonpParam || "callback"] = callbackId;
        window[callbackId] = L.Util.bind(callback, context);
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = url + L.Util.getParamString(params);
        script.id = callbackId;
        document.getElementsByTagName("head")[0].appendChild(script)
    };
    L.Control.Geocoder.getJSON = function(url, params, callback) {
        var xmlHttp = new XMLHttpRequest;
        xmlHttp.open("GET", url + L.Util.getParamString(params), true);
        xmlHttp.send(null);
        xmlHttp.onreadystatechange = function() {
            if (xmlHttp.readyState != 4) return;
            if (xmlHttp.status != 200 && req.status != 304) return;
            callback(JSON.parse(xmlHttp.response))
        }
    };
    L.Control.Geocoder.template = function(str, data, htmlEscape) {
        return str.replace(/\{ *([\w_]+) *\}/g, function(str, key) {
            var value = data[key];
            if (value === undefined) value = "";
            else if (typeof value === "function") value = value(data);
            return L.Control.Geocoder.htmlEscape(value)
        })
    };
    L.Control.Geocoder.htmlEscape = function() {
        var badChars = /[&<>"'`]/g;
        var possible = /[&<>"'`]/;
        var escape = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#x27;",
            "`": "&#x60;"
        };

        function escapeChar(chr) {
            return escape[chr]
        }
        return function(string) {
            if (string == null) return "";
            else if (!string) return string + "";
            string = "" + string;
            if (!possible.test(string)) return string;
            return string.replace(badChars, escapeChar)
        }
    }();
    L.Control.Geocoder.Nominatim = L.Class.extend({
        options: {
            serviceUrl: "//nominatim.openstreetmap.org/",
            geocodingQueryParams: {},
            reverseQueryParams: {},
            htmlTemplate: function(r) {
                var a = r.address,
                    parts = [];
                if (a.road || a.building) parts.push("{building} {road} {house_number}");
                if (a.city || a.town || a.village) parts.push('<span class="' + (parts.length > 0 ? "leaflet-control-geocoder-address-detail" : "") + '">{postcode} {city}{town}{village}</span>');
                if (a.state || a.country) parts.push('<span class="' + (parts.length > 0 ? "leaflet-control-geocoder-address-context" : "") + '">{state} {country}</span>');
                return L.Control.Geocoder.template(parts.join("<br/>"), a, true)
            }
        },
        initialize: function(options) {
            L.Util.setOptions(this, options)
        },
        geocode: function(query, cb, context) {
            jQuery(context._elem).parent().removeClass("leaflet-routing-searching").addClass("leaflet-routing-searching");
            L.Control.Geocoder.jsonp(this.options.serviceUrl + "search/", L.extend({
                q: query,
                limit: 5,
                format: "json",
                addressdetails: 1
            }, this.options.geocodingQueryParams), function(data) {
                jQuery(context._elem).parent().removeClass("leaflet-routing-searching");
                var results = [];
                for (var i = data.length - 1; i >= 0; i--) {
                    var bbox = data[i].boundingbox;
                    for (var j = 0; j < 4; j++) bbox[j] = parseFloat(bbox[j]);
                    results[i] = {
                        icon: data[i].icon,
                        name: data[i].display_name,
                        html: this.options.htmlTemplate ? this.options.htmlTemplate(data[i]) : undefined,
                        bbox: L.latLngBounds([bbox[0], bbox[2]], [bbox[1], bbox[3]]),
                        center: L.latLng(data[i].lat, data[i].lon),
                        properties: data[i]
                    }
                }
                cb.call(context, results)
            }, this, "json_callback")
        },
        reverse: function(location, scale, cb, context) {
            L.Control.Geocoder.jsonp(this.options.serviceUrl + "reverse/", L.extend({
                lat: location.lat,
                lon: location.lng,
                zoom: Math.round(Math.log(scale / 256) / Math.log(2)),
                addressdetails: 1,
                format: "json"
            }, this.options.reverseQueryParams), function(data) {
                var result = [],
                    loc;
                if (data && data.lat && data.lon) {
                    loc = L.latLng(data.lat, data.lon);
                    result.push({
                        name: data.display_name,
                        html: this.options.htmlTemplate ? this.options.htmlTemplate(data) : undefined,
                        center: loc,
                        bounds: L.latLngBounds(loc, loc),
                        properties: data
                    })
                }
                cb.call(context, result)
            }, this, "json_callback")
        }
    });
    L.Control.Geocoder.nominatim = function(options) {
        return new L.Control.Geocoder.Nominatim(options)
    };
    return L.Control.Geocoder
});
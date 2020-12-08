window.geodirMapScriptCallback = function() {
    jQuery(window).trigger('geodirMapScriptCallbackBefore');
    jQuery(window).trigger('geodirMapScriptCallback');
    jQuery(window).trigger('geodirMapScriptCallbackAfter');
};

window.geodirMapScriptOnLoad = function(el) {
    jQuery(window).trigger('geodirMapScriptOnLoad', el);
};

window.geodirMapMainScriptOnLoad = function(el) {
    jQuery(window).trigger('geodirMapMainScriptOnLoad', el);
};

window.geodirMapScriptOnError = function(el) {
    jQuery(window).trigger('geodirMapScriptOnError', el);
};

;(function($, window, document, undefined) {
    'use strict';

    var $window = $(window),
        $body = $('body'),
        windowHeight = parseFloat($window.height()),
        windowScrollTop = 0,

        geodirDebounce = function(delay, fn) {
            var timer = null;

            return function() {
                var context = this,
                    args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function() {
                    fn.apply(context, args);
                }, delay);
            };
        },

        geodirThrottle = function(delay, fn) {
            var last, deferTimer;
            return function() {
                var context = this,
                    args = arguments,
                    now = +new Date;
                if (last && now < last + delay) {
                    clearTimeout(deferTimer);
                    deferTimer = setTimeout(function() {
                        last = now;
                        fn.apply(context, args);
                    }, delay);
                } else {
                    last = now;
                    fn.apply(context, args);
                }
            };
        }

    window.geodirApiLoaded = geodir_map_params.api;
    window.geodirApiScriptLoaded = false;
    window.geodirApiScriptLoading = false;

    var $containers = $([]),

        geodirLoadScriptsStyles = function(mainLoaded) {
            var geodirMapApi, apiStyles, apiScripts, apiCallback;

            if ((window.geodirApiLoaded == 'google' || window.geodirApiLoaded == 'auto') && geodir_map_params.apis.google) {
                geodirMapApi = geodir_map_params.apis.google;
            } else if (window.geodirApiLoaded == 'osm' && geodir_map_params.apis.osm) {
                geodirMapApi = geodir_map_params.apis.osm;
            }

            if (geodirMapApi) {
                /* css */
                if (geodirMapApi.styles) {
                    apiStyles = geodirMapApi.styles;
                }

                /* javascript */
                if (geodirMapApi.scripts) {
                    apiScripts = geodirMapApi.scripts;
                }

                /* callback */
                if (geodirMapApi.callback) {
                    apiCallback = geodirMapApi.callback;
                }
            }

            /* Load styles */
            if (apiStyles && !mainLoaded) {
                geodirLoadStyles(apiStyles);
            }

            /* Load scripts */
            if (apiScripts && geodirLoadScripts(apiScripts, mainLoaded)) {
                window.geodirApiScriptLoading = true;
                if (apiCallback) {
                    try {
                        eval(apiCallback);
                    } catch (err) {
                        console.log(err.message)
                    }
                }
            }
            return true;
        },

        geodirLoadStyles = function(styles) {
            $(styles).each(function(i, file) {
                if (!($('link#' + (file.id)).length) && !(typeof file.check !== 'undefined' && file.check === false)) {
                    var el = document.createElement("link");
                    el.setAttribute("rel", "stylesheet");
                    el.setAttribute("type", "text/css");
                    el.setAttribute("id", file.id);
                    el.setAttribute("href", file.src);
                    document.getElementsByTagName("head")[0].appendChild(el);
                }
            });
            return true;
        },

        geodirLoadScripts = function(scripts, mainLoaded) {
            $(scripts).each(function(i, file) {
                if (!($('script#' + (file.id)).length) && !(typeof file.check !== 'undefined' && file.check === false)) {
                    if (!window.geodirMapScripts) {
                        window.geodirMapScripts = [];
                    }
                    if (!window.geodirMapScripts[window.geodirApiLoaded]) {
                        window.geodirMapScripts[window.geodirApiLoaded] = [];
                    }
                    if ((window.geodirMapScripts[window.geodirApiLoaded]).indexOf(file.id) === -1) {
                        (window.geodirMapScripts[window.geodirApiLoaded]).push(file.id);
                    }
                    if ((i == 0 && !mainLoaded) || (i > 0 && mainLoaded)) {
                        if (file.callback) {
                            file.src += ((file.src).indexOf('?') === -1 ? '?' : '&') + 'callback=' + file.callback;
                        }
                        var el = document.createElement("script");
                        el.setAttribute("type", "text/javascript");
                        el.setAttribute("id", file.id);
                        el.setAttribute("src", file.src);
                        el.setAttribute("async", true);
                        var onLoad = 'javascript:geodirMapScriptOnLoad(this);';
                        if (file.main) {
                            onLoad += 'javascript:geodirMapMainScriptOnLoad(this);';
                        }
                        if (file.onLoad) {
                            onLoad += file.onLoad;
                        }
                        el.setAttribute("onload", onLoad);
                        if (file.onError) {
                            el.setAttribute("onerror", (typeof file.onError == 'string' ? file.onError : 'javascript:geodirMapScriptOnError(this);'));
                        }
                        document.getElementsByTagName("head")[0].appendChild(el);
                    }
                }
            });
            return true;
        },

        geodirMapInit = function(callback) {
            windowScrollTop = $window.scrollTop();

            $containers.each(function() {
                var $this = $(this),
                    thisOptions = $this.data('options');

                if (geodir_map_params.lazyLoad == 'auto') {
                    if (!$this.data('loadJS') && $this.offset().top - windowScrollTop > windowHeight * 1) {
                        return true;
                    } else {
                        if ($this.is(':visible') && !$this.data('loadMap') || $this.data('loadJS')) {
                            $this.data('loadMap', true);
                        }
                    }
                }

                if (!$this.data('loadMap')) {
                    return true;
                }

                if (!window.geodirApiScriptLoaded && !window.geodirApiScriptLoading) {
                    geodirLoadScriptsStyles();
                }

                if (!window.geodirApiScriptLoaded) {
                    return false;
                }

                if (thisOptions.callback !== false) {
                    thisOptions.callback(this);
                }

                $containers = $containers.not($this);
            });
        };

    $window.on('geodirMapScriptOnLoad', function(el, file) {
            if (file.id && typeof window.geodirMapScripts == 'object' && window.geodirApiLoaded && typeof window.geodirMapScripts[window.geodirApiLoaded] == 'object') {
                var $loaded = window.geodirMapScripts[window.geodirApiLoaded];
                $loaded.splice($loaded.indexOf(file.id), 1);
                window.geodirMapScripts[window.geodirApiLoaded] = $loaded;
                if ($loaded.length === 0) {
                    window.geodirMapAllScriptsLoaded = true;
                    jQuery(window).trigger('geodirMapAllScriptsLoaded');
                }
            }
        })
        .on('geodirMapAllScriptsLoaded', function() {
            geodirMapScriptCallback();
        })
        .on('geodirMapScriptCallbackBefore', function() {
            /* goMap init */
            geodirGoMapInit();
        })
        .on('geodirMapMainScriptOnLoad', function(scripts) {
            /* Load dependency scripts */
            geodirLoadScriptsStyles(true);
        })
        .on('geodirMapScriptCallback', function() {
            window.geodirApiScriptLoaded = true;

            geodirMapInit();
        }).on('geodirMapScriptOnError', function() {
            /* Load OSM */
            if (window.geodirApiLoaded == 'auto') {
                window.geodirApiLoaded = 'osm';
                window.geodirApiScriptLoading = false;

                geodirMapInit();
            }
        })
        .on('scroll', geodirThrottle(500, function() {
            geodirMapInit();
        }))
        .on('resize', geodirDebounce(1000, function() {
            windowHeight = $window.height();

            geodirMapInit();
        }));

    $.fn.geodirLoadMap = function(options) {
        options = $.extend({
                map_canvas: '',
                loadJS: false,
                callback: false,
            },
            options);

        this.each(function() {
            var $this = $(this), $map_wrap, $loading, $lazyload;
            $this.data('options', options);
            $this.data('loadMap', $this.is(':visible'));

            if (geodir_map_params.lazyLoad == 'click') {
                if (options.map_canvas) {
                    $this.data('loadMap', false);
                    $map_wrap = $('.geodir_map_container.' + options.map_canvas);

                    if (!$('.geodir-lazyload-div', $map_wrap).length) {
                        $loading = $('.loading_div', $map_wrap);
                        $lazyload = $loading.clone();
                        $lazyload.css('background-image',"url('"+geodir_map_params.lazyLoadPlaceholder+"')").css('background-size','cover');
                        $lazyload = $lazyload.html(geodir_map_params.lazyLoadButton);
                        $lazyload.attr('id', options.map_canvas + '_lazyload_div');
                        $lazyload.removeClass('loading_div').addClass('geodir-lazyload-div overflow-hidden');
                        $loading.hide();

                        $lazyload.on('click', function() {
                            $(this).hide();
                            $(this).parent().find('.loading_div').show();
                            $(this).closest('.geodir_map_container').find('.geodir-map-cat-filter-wrap').show();

                            $this.data('loadMap', true);

                            geodirMapInit();
                        });

                        $loading.after($lazyload);
                    }
                } else if (options.forceLoad) {
                    $this.data('loadMap', true);
                }
            }

            if (options.loadJS) {
                $this.data('loadJS', true);
            }

            $containers = $containers.add($this);
        });

        if (geodir_map_params.lazyLoad == 'auto') {
            geodirMapInit();
        }

        this.geodirDebounce = geodirDebounce;
        this.geodirThrottle = geodirThrottle;

        return this;
    };

})(jQuery, window, document);
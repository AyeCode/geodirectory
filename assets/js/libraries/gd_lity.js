/*! Lity - v3.0.0-dev - 2017-07-17
* http://sorgalla.com/lity/
* Copyright (c) 2015-2017 Jan Sorgalla; Licensed MIT
*
* AyeCode ver 1.0.0
* */
(function(window, factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], function($) {
            return factory(window, $);
        });
    } else if (typeof module === 'object' && typeof module.exports === 'object') {
        module.exports = factory(window, require('jquery'));
    } else {
        window.lity = factory(window, window.jQuery || window.Zepto);
    }
}(typeof window !== "undefined" ? window : this, function(window, $) {
    'use strict';

    var document = window.document;

    var _win = $(window);
    var _deferred = $.Deferred;
    var _html = $('html');
    var _instances = [];

    var _attrAriaHidden = 'aria-hidden';
    var _dataAriaHidden = 'lity-' + _attrAriaHidden;

    var _focusableElementsSelector = 'a[href],area[href],input:not([disabled]),select:not([disabled]),textarea:not([disabled]),button:not([disabled]),iframe,object,embed,[contenteditable],[tabindex]:not([tabindex^="-"])';

    var _defaultOptions = {
        esc: true,
        handler: null,
        handlers: {
            image: imageHandler,
            inline: inlineHandler,
            youtube: youtubeHandler,
            vimeo: vimeoHandler,
            googlemaps: googlemapsHandler,
            facebookvideo: facebookvideoHandler,
            iframe: iframeHandler
        },
        template: '<div class="lity" role="dialog" aria-label="Dialog Window (Press escape to close)" tabindex="-1"><div class="lity-wrap" data-lity-close role="document"><div class="lity-loader" aria-hidden="true"><i class="fas fa-circle-notch fa-spin"></i></div><div class="lity-container"><div class="lity-content"></div><button class="lity-close" type="button" aria-label="Close (Press escape to close)" data-lity-close>&times;</button></div></div></div>'
    };

    var _imageRegexp = /(^data:image\/)|(\.(png|jpe?g|gif|svg|webp|bmp|ico|tiff?)(\?\S*)?$)/i;
    var _youtubeRegex = /(youtube(-nocookie)?\.com|youtu\.be)\/(watch\?v=|v\/|u\/|embed\/?)?([\w-]{11})(.*)?/i;
    var _vimeoRegex =  /(vimeo(pro)?\.com)\/(?:[^\d]+)?(\d+)\??(.*)?$/;
    var _googlemapsRegex = /((maps|www)\.)?google\.([^\/\?]+)\/?((maps\/?)?\?)(.*)/i;
    var _facebookvideoRegex = /(facebook\.com)\/([a-z0-9_-]*)\/videos\/([0-9]*)(.*)?$/i;

    var _transitionEndEvent = (function() {
        var el = document.createElement('div');

        var transEndEventNames = {
            WebkitTransition: 'webkitTransitionEnd',
            MozTransition: 'transitionend',
            OTransition: 'oTransitionEnd otransitionend',
            transition: 'transitionend'
        };

        for (var name in transEndEventNames) {
            if (el.style[name] !== undefined) {
                return transEndEventNames[name];
            }
        }

        return false;
    })();

    function transitionEnd(element) {
        var deferred = _deferred();

        if (!_transitionEndEvent || !element.length) {
            deferred.resolve();
        } else {
            element.one(_transitionEndEvent, deferred.resolve);
            setTimeout(deferred.resolve, 500);
        }

        return deferred.promise();
    }

    function settings(currSettings, key, value) {
        if (arguments.length === 1) {
            return $.extend({}, currSettings);
        }

        if (typeof key === 'string') {
            if (typeof value === 'undefined') {
                return typeof currSettings[key] === 'undefined'
                    ? null
                    : currSettings[key];
            }

            currSettings[key] = value;
        } else {
            $.extend(currSettings, key);
        }

        return this;
    }

    function parseQueryParams(params) {
        var pos = params.indexOf('?');

        if (pos > -1) {
            params = params.substr(pos + 1);
        }

        var pairs = decodeURI(params.split('#')[0]).split('&');
        var obj = {}, p;

        for (var i = 0, n = pairs.length; i < n; i++) {
            if (!pairs[i]) {
                continue;
            }

            p = pairs[i].split('=');
            obj[p[0]] = p[1];
        }

        return obj;
    }

    function appendQueryParams(url, params) {
        if (!params) {
            return url;
        }

        if ('string' === $.type(params)) {
            params = parseQueryParams(params);
        }

        if (url.indexOf('?') > -1) {
            var split = url.split('?');
            url = split.shift();

            params = $.extend(
                {},
                parseQueryParams(split[0]),
                params
            )
        }

        return url + '?' + $.param(params);
    }

    function transferHash(originalUrl, newUrl) {
        var pos = originalUrl.indexOf('#');

        if (-1 === pos) {
            return newUrl;
        }

        if (pos > 0) {
            originalUrl = originalUrl.substr(pos);
        }

        return newUrl + originalUrl;
    }

    function iframe(iframeUrl, instance, queryParams, hashUrl) {
        instance && instance.element().addClass('lity-iframe');

        if (queryParams) {
            iframeUrl = appendQueryParams(iframeUrl, queryParams);
        }

        if (hashUrl) {
            iframeUrl = transferHash(hashUrl, iframeUrl);
        }

        return '<div class="lity-iframe-container"><iframe onload="jQuery(\'.lity-iframe-container .svg-inline--fa, .lity-iframe-container i\').remove();" frameborder="0" allowfullscreen src="' + iframeUrl + '"></iframe><i class="fas fa-sync fa-spin fa-3x fa-fw"></i></div>';
    }

    function error(msg) {
        return $('<span class="lity-error"></span>').append(msg);
    }

    function imageHandler(target, instance) {
        // Generate Gallery
        var has_next = instance.opener().parent().next('li').find('a').length;
        var has_prev = instance.opener().parent().prev('li').find('a').length;
        var gallery_nav = '';
        if(has_next || has_prev){
            gallery_nav = '<div class="lity-nav">';
            if(has_prev){gallery_nav = gallery_nav + '<span class="lity-nav-left"><i class="fas fa-chevron-left" aria-hidden="true"></i></span>';}
            if(has_next){gallery_nav = gallery_nav + '<span class="lity-nav-right"><i class="fas fa-chevron-right" aria-hidden="true"></i></span>';}
            gallery_nav = gallery_nav + '</div>';

            var initGallery = function() {
                if(has_next){
                    // add keybaord support
                    jQuery('.lity-opened').keydown(function(e) {
                        if (e.which == 39) {
                            instance.close(); // close the original
                            instance.opener().parent().next('li').find('a').trigger( "click" ); // open the next one
                        }
                    });
                    jQuery( ".lity-nav-right" ).click(function() {
                        instance.close(); // close the original
                        instance.opener().parent().next('li').find('a').trigger( "click" ); // open the next one
                    });
                }
                if(has_prev){
                    // add keybaord support
                    jQuery('.lity-opened').keydown(function(e) {
                        if (e.which == 37) {
                            instance.close(); // close the original
                            instance.opener().parent().prev('li').find('a').trigger( "click" ); // open the next one
                        }
                    });
                    jQuery( ".lity-nav-left" ).click(function() {
                        instance.close(); // close the original
                        instance.opener().parent().prev('li').find('a').trigger( "click" ); // open the next one
                    });
                }
            };
        }

        // Generate caption
        var caption = '';
        //var desc = (instance.opener() && instance.opener().data('lity-desc')) || 'Image with no description';
        var title = (instance.opener() && instance.opener().find('img').attr('title')) || '';
        var alt = (instance.opener() && instance.opener().find('img').attr('alt')) || '';
        var desc = (instance.opener() && instance.opener().find('img').data('caption')) || '';

        // responsive
        var srcset = (instance.opener() && instance.opener().find('img').attr('srcset')) || '';
		if (!srcset && instance.opener() && instance.opener().find('img').data('srcset')) {
			srcset = instance.opener().find('img').attr('data-srcset');
		}
        if(title || desc){
            caption = '<div class="lity-caption">';
            if(title){
                caption = caption + '<span class="lity-caption-title">'+title+'</span>';
            }
            if(desc){
                caption = caption + '<span class="lity-caption-desc">'+desc+'</span>';
            }
            caption = caption + '</div>';
        }
        var img = $('<img src="' + target + '" alt="' + alt + '" srcset="'+srcset+'"/>'+caption+gallery_nav);//+gallery_nav;
        var deferred = _deferred();
        var failed = function() {
            deferred.reject(error('Failed loading image'));
        };

        img
            .on('load', function() {
                if (this.naturalWidth === 0) {
                    return failed();
                }

                deferred.resolve(img);
                if (gallery_nav) {
                    var gdLityTo;
                    clearTimeout(gdLityTo);
                    /* WP 5.6 with jQuery v3.5.x broken lity lightbox next/prev slideshow */
                    gdLityTo = setTimeout(function() {
                        initGallery();
                    }, 50);
                }
            })
            .on('error', failed)
        ;

        return deferred.promise();
    }

    imageHandler.test = function(target) {
        return _imageRegexp.test(target);
    };

    function inlineHandler(target, instance) {
        var el, placeholder, hasHideClass;

        // check for loader
        if(target==='loading'){
            return 'loading';
        }

        try {
            el = $(target);
        } catch (e) {
            return false;
        }

        if (!el.length) {
            return false;
        }

        placeholder = $('<i style="display:none !important"></i>');
        hasHideClass = el.hasClass('lity-hide');

        instance
            .element()
            .one('lity:remove', function() {
                placeholder
                    .before(el)
                    .remove()
                ;

                if (hasHideClass && !el.closest('.lity-content').length) {
                    el.addClass('lity-hide');
                }
            })
        ;

        return el
            .removeClass('lity-hide')
            .after(placeholder)
        ;
    }

    function youtubeHandler(target, instance) {
        var matches = _youtubeRegex.exec(target);

        if (!matches) {
            return false;
        }

        return iframe(
            'https://www.youtube' + (matches[2] || '') + '.com/embed/' + matches[4] + '?autoplay=1',
            instance,
            matches[5],
            target
        );
    }

    function vimeoHandler(target, instance) {
        var matches = _vimeoRegex.exec(target);

        if (!matches) {
            return false;
        }

        return iframe(
            'https://player.vimeo.com/video/' + matches[3] + '?autoplay=1',
            instance,
            matches[4],
            target
        );
    }

    function facebookvideoHandler(target, instance) {
        var matches = _facebookvideoRegex.exec(target);

        if (!matches) {
            return false;
        }

        if (0 !== target.indexOf('http')) {
            target = 'https:' + target;
        }

        return iframe(
            'https://www.facebook.com/plugins/video.php?href=' + target + '&autoplay=1',
            instance,
            matches[4],
            target
        );
    }

    function googlemapsHandler(target, instance) {
        var matches = _googlemapsRegex.exec(target);

        if (!matches) {
            return false;
        }

        return iframe(
            'https://www.google.' + matches[3] + '/maps?' + matches[6],
            instance,
            {
                output: matches[6].indexOf('layer=c') > 0 ? 'svembed' : 'embed'
            },
            target
        );
    }

    function iframeHandler(target, instance) {
        return iframe(target, instance);
    }

    function winHeight() {
        return document.documentElement.clientHeight
            ? document.documentElement.clientHeight
            : Math.round(_win.height());
    }

    function keydown(e) {
        var current = currentInstance();

        if (!current) {
            return;
        }

        // ESC key
        if (e.keyCode === 27 && !!current.options('esc')) {
            current.close();
        }

        // TAB key
        if (e.keyCode === 9) {
            handleTabKey(e, current);
        }
    }

    function handleTabKey(e, instance) {
        var focusableElements = instance.element().find(_focusableElementsSelector);
        var focusedIndex = focusableElements.index(document.activeElement);

        if (e.shiftKey && focusedIndex <= 0) {
            focusableElements.get(focusableElements.length - 1).focus();
            e.preventDefault();
        } else if (!e.shiftKey && focusedIndex === focusableElements.length - 1) {
            focusableElements.get(0).focus();
            e.preventDefault();
        }
    }

    function resize() {
        $.each(_instances, function(i, instance) {
            instance.resize();
        });
    }

    function registerInstance(instanceToRegister) {
        if (1 === _instances.unshift(instanceToRegister)) {
            _html.addClass('lity-active');

            _win
                .on({
                    resize: resize,
                    keydown: keydown
                })
            ;
        }

        $('body > *').not(instanceToRegister.element())
            .addClass('lity-hidden')
            .each(function() {
                var el = $(this);

                if (undefined !== el.data(_dataAriaHidden)) {
                    return;
                }

                el.data(_dataAriaHidden, el.attr(_attrAriaHidden) || null);
            })
            .attr(_attrAriaHidden, 'true')
        ;
    }

    function removeInstance(instanceToRemove) {
        var show;

        instanceToRemove
            .element()
            .attr(_attrAriaHidden, 'true')
        ;

        if (1 === _instances.length) {
            _html.removeClass('lity-active');

            _win
                .off({
                    resize: resize,
                    keydown: keydown
                })
            ;
        }

        _instances = $.grep(_instances, function(instance) {
            return instanceToRemove !== instance;
        });

        if (!!_instances.length) {
            show = _instances[0].element();
        } else {
            show = $('.lity-hidden');
        }

        show
            .removeClass('lity-hidden')
            .each(function() {
                var el = $(this), oldAttr = el.data(_dataAriaHidden);

                if (!oldAttr) {
                    el.removeAttr(_attrAriaHidden);
                } else {
                    el.attr(_attrAriaHidden, oldAttr);
                }

                el.removeData(_dataAriaHidden);
            })
        ;
    }

    function currentInstance() {
        if (0 === _instances.length) {
            return null;
        }

        return _instances[0];
    }

    function factory(target, instance, handlers, preferredHandler) {
        var handler = 'inline', content;

        var currentHandlers = $.extend({}, handlers);

        if (preferredHandler && currentHandlers[preferredHandler]) {
            content = currentHandlers[preferredHandler](target, instance);
            handler = preferredHandler;
        } else {
            // Run inline and iframe handlers after all other handlers
            $.each(['inline', 'iframe'], function(i, name) {
                delete currentHandlers[name];

                currentHandlers[name] = handlers[name];
            });

            $.each(currentHandlers, function(name, currentHandler) {
                // Handler might be "removed" by setting callback to null
                if (!currentHandler) {
                    return true;
                }

                if (
                    currentHandler.test &&
                    !currentHandler.test(target, instance)
                ) {
                    return true;
                }

                content = currentHandler(target, instance);

                if (false !== content) {
                    handler = name;
                    return false;
                }
            });
        }

        return {handler: handler, content: content || ''};
    }

    function Lity(target, options, opener, activeElement) {
        var self = this;
        var result;
        var isReady = false;
        var isClosed = false;
        var element;
        var content;

        options = $.extend(
            {},
            _defaultOptions,
            options
        );

        element = $(options.template);

        // -- API --

        self.element = function() {
            return element;
        };

        self.opener = function() {
            return opener;
        };

        self.content = function() {
            return content;
        };

        self.options  = $.proxy(settings, self, options);
        self.handlers = $.proxy(settings, self, options.handlers);

        self.resize = function() {
            if (!isReady || isClosed) {
                return;
            }

            content
                .css('max-height', winHeight() + 'px')
                .trigger('lity:resize', [self])
            ;
        };

        self.close = function() {
            if (!isReady || isClosed) {
                return;
            }

            isClosed = true;

            removeInstance(self);

            var deferred = _deferred();

            // We return focus only if the current focus is inside this instance
            if (
                activeElement &&
                (
                    document.activeElement === element[0] ||
                    $.contains(element[0], document.activeElement)
                )
            ) {
                try {
                    activeElement.focus();
                } catch (e) {
                    // Ignore exceptions, eg. for SVG elements which can't be
                    // focused in IE11
                }
            }

            content.trigger('lity:close', [self]);

            element
                .removeClass('lity-opened')
                .addClass('lity-closed')
            ;

            transitionEnd(content.add(element))
                .always(function() {
                    content.trigger('lity:remove', [self]);
                    element.remove();
                    element = undefined;
                    deferred.resolve();
                })
            ;

            // remove gallery eventsZ
            jQuery('.lity-opened').unbind("keydown");

            return deferred.promise();
        };

        // -- Initialization --

        result = factory(target, self, options.handlers, options.handler);

        element
            .attr(_attrAriaHidden, 'false')
            .addClass('lity-loading lity-opened lity-' + result.handler)
            .appendTo('body')
            .focus()
            .on('click', '[data-lity-close]', function(e) {
                if ($(e.target).is('[data-lity-close]')) {
                    self.close();
                }
            })
            .trigger('lity:open', [self])
        ;

        registerInstance(self);

        $.when(result.content)
            .always(ready)
        ;

        function ready(result) {

            // check if we are just loading the loader
            if(result==='loading'){
                result = ".lity-loader";
                content = $(result);
                isReady = true;
            }else{
                content = $(result)
                    .css('max-height', winHeight() + 'px')
                ;

                element
                    .find('.lity-loader')
                    .each(function() {
                        var loader = $(this);

                        transitionEnd(loader)
                            .always(function() {
                                loader.remove();
                            })
                        ;
                    })
                ;

                element
                    .removeClass('lity-loading')
                    .find('.lity-content')
                    .empty()
                    .append(content)
                ;

                isReady = true;

                content
                    .trigger('lity:ready', [self])
                ;
            }
        }
    }

    function lity(target, options, opener) {
        if (!target.preventDefault) {
            opener = $(opener);
        } else {
            target.preventDefault();
            opener = $(this);
            target = opener.data('lity-target') || opener.attr('href') || opener.attr('src');
        }

        var instance = new Lity(
            target,
            $.extend(
                {},
                opener.data('lity-options') || opener.data('lity'),
                options
            ),
            opener,
            document.activeElement
        );

        if (!target.preventDefault) {
            return instance;
        }
    }

    lity.version  = '3.0.0-dev';
    lity.options  = $.proxy(settings, lity, _defaultOptions);
    lity.handlers = $.proxy(settings, lity, _defaultOptions.handlers);
    lity.current  = currentInstance;
    lity.iframe   = iframe;

    $(document).on('click.lity', '[data-lity]', lity);

    return lity;
}));

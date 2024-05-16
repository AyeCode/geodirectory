// get url paramiters
var gdUrlParam = function gdUrlParam(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

/* GD lazy load images */
jQuery.fn.gdunveil = function(threshold, callback, extra1) {

    var $w = jQuery(window),
        th = threshold || 0,
        retina = window.devicePixelRatio > 1,
        attrib = retina ? "data-src-retina" : "data-src",
        images = this,
        loaded;

    if (extra1) {
        var $e1 = jQuery(extra1),
            th = threshold || 0,
            retina = window.devicePixelRatio > 1,
            attrib = retina ? "data-src-retina" : "data-src",
            images = this,
            loaded;
    }

    this.one("gdunveil", function() {
        var source = this.getAttribute(attrib);
        var srcset = this.getAttribute("data-srcset");
        source = source || this.getAttribute("data-src");
        if (source) {
            // set the srcset from the data-srcset
            if (srcset) {
                this.setAttribute("srcset", srcset);
            }
            // set the src from data-src
            this.setAttribute("src", source);

            jQuery(this).removeClass('geodir-lazy-load');
            if (typeof callback === "function") callback.call(this);

            jQuery(this).trigger("gdlazyLoaded");
        }
    });

    function gdunveil() {
        var inview = images.filter(function() {
            var $e = jQuery(this);
            if ($e.is(":hidden")) return;

            var wt = $w.scrollTop(),
                wb = wt + $w.height(),
                et = $e.offset().top,
                eb = et + $e.height();

            return eb >= wt - th && et <= wb + th;
        });

        loaded = inview.trigger("gdunveil");
        images = images.not(loaded);
    }

    $w.on("scroll.gdunveil resize.gdunveil lookup.gdunveil", gdunveil);
    if (extra1) {
        $e1.on("scroll.gdunveil resize.gdunveil lookup.gdunveil", gdunveil);
    }

    gdunveil();

    return this;

};

function geodir_init_lazy_load(gdQuery) {
    if (!gdQuery) {
        gdQuery = jQuery;
    }
    // load for GD images
    var _opacity = 1;
    if ('objectFit' in document.documentElement.style === false) {
        _opacity = 0;
    }
    gdQuery(".geodir-lazy-load").gdunveil(100, function() {
        this.style.opacity = _opacity;
    }, '#geodir_content, .dialog-lightbox-message');

    // fire when the image tab is clicked on details page
    jQuery('#gd-tabs').on("click",function() {
        setTimeout(function() {
            jQuery(window).trigger("lookup");
        }, 100);
    });

    // fire after document load, just incase
    jQuery(document).ready(function() {
        setTimeout(function() {
            jQuery(window).trigger("lookup");
        }, 100);
    });
}

//Pollyfill Object Fit in browsers that don't support it
function geodir_object_fit_fix(_img) {

    //Image, its url and its parent li
    var _li = jQuery(_img).closest('li'),
        _url = jQuery(_img).data('src')

    //Abort if url is unset
    if (!_url) {
        return;
    }

    //Hide the image and use it as the parent's bg
    jQuery(_img).css({
        opacity: 0
    })
    _li.css({
        backgroundImage: 'url(' + _url + ')',
        backgroundSize: 'cover',
        borderRadius: '4px',
        backgroundPosition: 'center center',
    })
}

function geodir_load_badge_class() {
    jQuery('.gd-badge-meta .gd-badge').each(function() {
        var badge = jQuery(this).data('badge');
        var badge_condition = jQuery(this).data('badge-condition');
        if (badge && jQuery(this).closest('.post-' + jQuery(this).data('id')).length) {
            badge_class = 'geodir-badge-' + badge; // name
            badge_class += ' geodir-badge-' + badge + '-' + badge_condition; // name and condition
            jQuery(this).closest('.post-' + jQuery(this).data('id')).removeClass(badge_class).addClass(badge_class);
        }
    });
}

jQuery(function($) {
    // start lazy load if it's turned on
    geodir_init_lazy_load($);

    if ('objectFit' in document.documentElement.style === false) {
        //Fix after document loads
        $(document).ready(
            function() {
                $('.geodir-image-container ul.geodir-images li img').each(function() {
                    geodir_object_fit_fix(this)
                    $(this).on('gdlazyLoaded', geodir_object_fit_fix)
                })
            }
        );
    }

    $(document).on('click', '.gd-bh-show-field .gd-bh-expand-range', function(e) {
        var $wrap = $(this).closest('.geodir_post_meta')
        var $hours = $wrap.find('.gd-bh-open-hours')
        if ($hours.is(':visible')) {
            $hours.slideUp(100);
            $wrap.removeClass('gd-bh-expanded').addClass('gd-bh-toggled');
        } else {
            $hours.slideDown(100);
            $wrap.removeClass('gd-bh-toggled').addClass('gd-bh-expanded');
        }
    });
    if ($('.gd-bh-show-field').length) {
        setInterval(function(e) {
            geodir_refresh_business_hours();
        }, 60000);
        geodir_refresh_business_hours();
    }
    $('body').on('geodir_map_infowindow_open', function(e, data) {
        /* Render business hours */
        if (data.content && $(data.content).find('.gd-bh-show-field').length) {
            geodir_refresh_business_hours();
        }
        geodir_init_lazy_load();
        geodir_init_flexslider();
        geodir_load_badge_class();
        geodir_fix_marker_pos(data.canvas);
    });

    // add badge related class
    geodir_load_badge_class();

    // init reply link text changed
    gd_init_comment_reply_link();

    // bounce the map markers
    geodir_animate_markers();

    // Bounce the map markers on lazy load map
    $(window).on('geodirMapAllScriptsLoaded', function() {
        geodir_animate_markers();
    });

    // fix accessibility issues
    $('.geodir-sort-by[name="sort_by"], #geodir_my_favourites[name="geodir_my_favourites"], #geodir_my_listings[name="geodir_my_listings"], #geodir_add_listing[name="geodir_add_listing"]').on("change", function(e) {
        if ($(this).val()) window.location = $(this).val();
    });

    // if we have the reviews input but no reviews id then we add it on the fly so /#reviews anchor links work
    if (jQuery('.geodir-comments-area').length && !jQuery('#reviews').length) {
        jQuery('.geodir-comments-area').prepend('<span id="reviews"></span>');
    }

	// Listings carousel
    $('.geodir-posts-carousel').each(function(index) {
        geodir_init_listings_carousel(this, index);
    });

    $(document).on('elementor/popup/show', (e, id, ins) => {
        if ($('.elementor-popup-modal .geodir-lazy-load').length) {
            geodir_init_lazy_load($);
        }
    });
});

/* Placeholders.js v3.0.2  fixes placeholder support for older browsers */
(function(t) {
    "use strict";

    function e(t, e, r) {
        return t.addEventListener ? t.addEventListener(e, r, !1) : t.attachEvent ? t.attachEvent("on" + e, r) : void 0
    }

    function r(t, e) {
        var r, n;
        for (r = 0, n = t.length; n > r; r++)
            if (t[r] === e) return !0;
        return !1
    }

    function n(t, e) {
        var r;
        t.createTextRange ? (r = t.createTextRange(), r.move("character", e), r.select()) : t.selectionStart && (t.focus(), t.setSelectionRange(e, e))
    }

    function a(t, e) {
        try {
            return t.type = e, !0
        } catch (r) {
            return !1
        }
    }

    t.Placeholders = {
        Utils: {
            addEventListener: e,
            inArray: r,
            moveCaret: n,
            changeType: a
        }
    }
})(this),
function(t) {
    "use strict";

    function e() {}

    function r() {
        try {
            return document.activeElement
        } catch (t) {}
    }

    function n(t, e) {
        var r, n, a = !!e && t.value !== e,
            u = t.value === t.getAttribute(V);
        return (a || u) && "true" === t.getAttribute(D) ? (t.removeAttribute(D), t.value = t.value.replace(t.getAttribute(V), ""), t.className = t.className.replace(R, ""), n = t.getAttribute(F), parseInt(n, 10) >= 0 && (t.setAttribute("maxLength", n), t.removeAttribute(F)), r = t.getAttribute(P), r && (t.type = r), !0) : !1
    }

    function a(t) {
        var e, r, n = t.getAttribute(V);
        return "" === t.value && n ? (t.setAttribute(D, "true"), t.value = n, t.className += " " + I, r = t.getAttribute(F), r || (t.setAttribute(F, t.maxLength), t.removeAttribute("maxLength")), e = t.getAttribute(P), e ? t.type = "text" : "password" === t.type && M.changeType(t, "text") && t.setAttribute(P, "password"), !0) : !1
    }

    function u(t, e) {
        var r, n, a, u, i, l, o;
        if (t && t.getAttribute(V)) e(t);
        else
            for (a = t ? t.getElementsByTagName("input") : b, u = t ? t.getElementsByTagName("textarea") : f, r = a ? a.length : 0, n = u ? u.length : 0, o = 0, l = r + n; l > o; o++) i = r > o ? a[o] : u[o - r], e(i)
    }

    function i(t) {
        u(t, n)
    }

    function l(t) {
        u(t, a)
    }

    function o(t) {
        return function() {
            m && t.value === t.getAttribute(V) && "true" === t.getAttribute(D) ? M.moveCaret(t, 0) : n(t)
        }
    }

    function c(t) {
        return function() {
            a(t)
        }
    }

    function s(t) {
        return function(e) {
            return A = t.value, "true" === t.getAttribute(D) && A === t.getAttribute(V) && M.inArray(C, e.keyCode) ? (e.preventDefault && e.preventDefault(), !1) : void 0
        }
    }

    function d(t) {
        return function() {
            n(t, A), "" === t.value && (t.blur(), M.moveCaret(t, 0))
        }
    }

    function g(t) {
        return function() {
            t === r() && t.value === t.getAttribute(V) && "true" === t.getAttribute(D) && M.moveCaret(t, 0)
        }
    }

    function v(t) {
        return function() {
            i(t)
        }
    }

    function p(t) {
        t.form && (T = t.form, "string" == typeof T && (T = document.getElementById(T)), T.getAttribute(U) || (M.addEventListener(T, "submit", v(T)), T.setAttribute(U, "true"))), M.addEventListener(t, "focus", o(t)), M.addEventListener(t, "blur", c(t)), m && (M.addEventListener(t, "keydown", s(t)), M.addEventListener(t, "keyup", d(t)), M.addEventListener(t, "click", g(t))), t.setAttribute(j, "true"), t.setAttribute(V, x), (m || t !== r()) && a(t)
    }

    var b, f, m, h, A, y, E, x, L, T, N, S, w, B = ["text", "search", "url", "tel", "email", "password", "number", "textarea"],
        C = [27, 33, 34, 35, 36, 37, 38, 39, 40, 8, 46],
        k = "#ccc",
        I = "placeholdersjs",
        R = RegExp("(?:^|\\s)" + I + "(?!\\S)"),
        V = "data-placeholder-value",
        D = "data-placeholder-active",
        P = "data-placeholder-type",
        U = "data-placeholder-submit",
        j = "data-placeholder-bound",
        q = "data-placeholder-focus",
        z = "data-placeholder-live",
        F = "data-placeholder-maxlength",
        G = document.createElement("input"),
        H = document.getElementsByTagName("head")[0],
        J = document.documentElement,
        K = t.Placeholders,
        M = K.Utils;
    if (K.nativeSupport = void 0 !== G.placeholder, !K.nativeSupport) {
        for (b = document.getElementsByTagName("input"), f = document.getElementsByTagName("textarea"), m = "false" === J.getAttribute(q), h = "false" !== J.getAttribute(z), y = document.createElement("style"), y.type = "text/css", E = document.createTextNode("." + I + " { color:" + k + "; }"), y.styleSheet ? y.styleSheet.cssText = E.nodeValue : y.appendChild(E), H.insertBefore(y, H.firstChild), w = 0, S = b.length + f.length; S > w; w++) N = b.length > w ? b[w] : f[w - b.length], x = N.attributes.placeholder, x && (x = x.nodeValue, x && M.inArray(B, N.type) && p(N));
        L = setInterval(function() {
            for (w = 0, S = b.length + f.length; S > w; w++) N = b.length > w ? b[w] : f[w - b.length], x = N.attributes.placeholder, x ? (x = x.nodeValue, x && M.inArray(B, N.type) && (N.getAttribute(j) || p(N), (x !== N.getAttribute(V) || "password" === N.type && !N.getAttribute(P)) && ("password" === N.type && !N.getAttribute(P) && M.changeType(N, "text") && N.setAttribute(P, "password"), N.value === N.getAttribute(V) && (N.value = x), N.setAttribute(V, x)))) : N.getAttribute(D) && (n(N), N.removeAttribute(V));
            h || clearInterval(L)
        }, 100)
    }
    M.addEventListener(t, "beforeunload", function() {
        K.disable()
    }), K.disable = K.nativeSupport ? e : i, K.enable = K.nativeSupport ? e : l
}(this);

jQuery(document).ready(function($) {

    // ini read more
    init_read_more();

    // init any sliders
    geodir_init_flexslider();

    //toggle detail page tabs mobile menu
    jQuery('#geodir-tab-mobile-menu').on("click",function() {
        jQuery('#gd-tabs .geodir-tab-head').toggle();
    });

    gd_infowindow = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.InfoWindow({
        maxWidth: 200
    }) : null;

    jQuery('.gd-cats-display-checkbox input[type="checkbox"]').on("click",function() {
        var isChecked = jQuery(this).is(':checked');
        if (!isChecked) {
            var chkVal = jQuery(this).val();
            jQuery(this).closest('.gd-parent-cats-list').find('.gd-cat-row-' + chkVal + ' input[type="checkbox"]').prop("checked", isChecked);
        }
    });

    jQuery('.geodir-delete').on("click",function() {
        var message = geodir_params.my_place_listing_del;

        if (jQuery(this).closest('.geodir-gridview').hasClass('gdp-franchise-m') || jQuery(this).closest('.geodir-listview').hasClass('gdp-franchise-m')) {
            message = geodir_params.my_main_listing_del;
        }

        if (confirm(message)) {
            return true;
        } else {
            return false;
        }
    });

    jQuery('.gd-category-dd').on("hover",function() {
        jQuery('.gd-category-dd ul').show();
    });

    jQuery('.gd-category-dd ul li a').on("click",function(ele) {
        jQuery('.gd-category-dd').find('input').val(jQuery(this).attr('data-slug'));
        jQuery('.gd-category-dd > a').html(jQuery(this).attr('data-name'));
        jQuery('.gd-category-dd ul').hide();
    });

    // setup search forms
    geodir_setup_search_form();

    // init the rating inputs, delay needed for font awesome to load
    setTimeout(function() {
        gd_init_rating_input();
    }, 250);

});

// init any sliders
function geodir_init_flexslider() {

    jQuery('.geodir-slider.geodir-slider-loading').each(function(i, obj) {
        // init the sliders
        geodir_init_slider(obj.id);
    });
}

jQuery(window).on("load", function() {

    /*-----------------------------------------------------------------------------------*/
    /* Tabs
    /*-----------------------------------------------------------------------------------*/
    jQuery('.geodir-tabs-content').show(); // set the tabs to show once js loaded to avoid double scroll bar in chrome
    tabNoRun = false;

    function activateTab(tab) {
        if (!jQuery(".geodir-tab-head").length) {
            return;
        }
        // change name for mobile tabs menu
        tabName = urlHash = tab.find('a').html();

        if (tabName && jQuery('.geodir-mobile-active-tab').length) {
            jQuery('.geodir-mobile-active-tab').html(tabName);
        }

        if (tabNoRun) {
            tabNoRun = false;
            return;
        }
        var activeTab = tab.closest('dl').find('dd.geodir-tab-active'),
            contentLocation = tab.find('a').attr("data-tab") + 'Tab',
            scrollTo;
        urlHash = tab.find('a').attr("data-tab");

        if (jQuery(tab).hasClass("geodir-tab-active")) {} else {
            if (typeof urlHash === 'undefined') {
                if (window.location.hash.substring(0, 8) == '#comment') {
                    tab = jQuery('*[data-tab="#reviews"]').parent();
                    tabNoRun = true;
                }
            } else {
                if (history.pushState) {
                    //history.pushState(null, null, urlHash);
                    history.replaceState(null, null, urlHash); // wont make the browser back button go back to prev has value
                } else {
                    window.location.hash = urlHash;
                }
            }
        }

        //Make Tab Active
        activeTab.removeClass('geodir-tab-active');
        tab.addClass('geodir-tab-active');

        //Show Tab Content
        jQuery(contentLocation).closest('.geodir-tabs-content').children('li').hide();
        jQuery(contentLocation).fadeIn();
        jQuery(contentLocation).css({
            'display': 'block'
        });

        if (urlHash == '#post_map' && window.gdMaps) {
            window.setTimeout(function() {
                var map_canvas = jQuery('.geodir-map-canvas', jQuery('#post_mapTab')).data('map-canvas');
                var options = map_canvas ? eval(map_canvas) : {};
                jQuery("#" + map_canvas).goMap(options);
                var center = jQuery.goMap.map.getCenter();
                if (window.gdMaps == 'osm') {
                    jQuery.goMap.map.invalidateSize();
                    jQuery.goMap.map._onResize();
                    jQuery.goMap.map.panTo(center);
                } else {
                    google.maps.event.trigger(jQuery.goMap.map, 'resize');
                    jQuery.goMap.map.setCenter(center);
                }
            }, 100);
        }

        if (history.pushState && window.location.hash && jQuery('#publish_listing').length === 0) {
            if (jQuery(window).width() < 1060) {
                jQuery('#gd-tabs .geodir-tab-head').toggle();
                /* ElementorPro has visible all tabs contant & tabs with list option. */
                if (jQuery('.geodir-single-tabs-container .geodir-tabs-as-list ' + urlHash + 'List').length) {
                    scrollTo = jQuery('.geodir-single-tabs-container .geodir-tabs-as-list ' + urlHash + 'List').offset().top;
                } else {
                    scrollTo = jQuery('#geodir-tab-mobile-menu').offset().top;
                }
                jQuery('html, body').animate({
                    scrollTop: scrollTo
                }, 500);
            }
        }

        // trigger the window resize to content can adjust
        jQuery(window).trigger('resize');
    } // end activateTab()

    jQuery('dl.geodir-tab-head').each(function() {
        //Get all tabs
        var tabs = jQuery(this).children('dd');
        tabs.on("click",function(e) {
            if (jQuery(this).find('a').attr('data-status') == 'enable') {
                activateTab(jQuery(this));
            }
        });
    });

    if (window.location.hash) {
        activateTab(jQuery('a[data-tab="' + window.location.hash + '"]').parent());
    }

    jQuery('.gd-tabs .gd-tab-next').on("click",function(ele) {
        var is_validate = true;

        if (is_validate) {
            var tab = jQuery('dl.geodir-tab-head').find('dd.geodir-tab-active').next();
            if (tab.find('a').attr('data-status') == 'enable') {
                activateTab(tab);
            }
            if (!jQuery('dl.geodir-tab-head').find('dd.geodir-tab-active').next().is('dd')) {
                jQuery(this).hide();
                jQuery('#gd-add-listing-submit').show();
            }
        }
    });

    jQuery('#gd-login-options input').on("change",function() {
        jQuery('.gd-login_submit').toggle();
    });

    jQuery('ul.geodir-tabs-content').css({
        'z-index': '0',
        'position': 'relative'
    });

    jQuery('dl.geodir-tab-head dd.geodir-tab-active').trigger('click');

    // Show the tab if an anchor link is clicked
    jQuery('a[href*=\\#]').on('click', function(event) {
        if (this.pathname === window.location.pathname) {
            if (jQuery(this.hash + 'Tab').length) {
                activateTab(jQuery('a[data-tab="' + this.hash + '"]').parent());
            } else if (this.hash == '#respond' && jQuery('#reviewsTab').length) {
                activateTab(jQuery('a[data-tab="#reviews"]').parent());
            }
        }
    });

    // Set times to time ago
    if (jQuery('.gd-timeago').length) {
        geodir_time_ago('.gd-timeago');
    }
});

/*-----------------------------------------------------------------------------------*/
/* Auto Fill
/*-----------------------------------------------------------------------------------*/
function autofill_click(ele) {
    var fill_value = jQuery(ele).html();
    jQuery(ele).closest('div.gd-autofill-dl').closest('div.gd-autofill').find('input[type=text]').val(fill_value);
    jQuery(ele).closest('.gd-autofill-dl').remove();
};

jQuery(document).ready(function() {
    jQuery('input[type=text]').on("keyup",function() {
        var input_field = jQuery(this);
        if (input_field.attr('data-type') == 'autofill' && input_field.attr('data-fill') != '') {
            var data_fill = input_field.attr('data-fill');
            var fill_value = jQuery(this).val();
            jQuery.get(geodir_params.ajax_url, {
                autofill: data_fill,
                fill_str: fill_value
            }, function(data) {
                if (data != '') {
                    if (input_field.closest('div.gd-autofill').length == 0) input_field.wrap('<div class="gd-autofill"></div>');
                    input_field.closest('div.gd-autofill').find('.gd-autofill-dl').remove();
                    input_field.after('<div class="gd-autofill-dl"></div>');
                    input_field.next('.gd-autofill-dl').html(data);
                    input_field.focus();
                }
            });
        }
    });

    jQuery('input[type=text]').parent().on("mouseleave",function() {
        jQuery(this).find('.gd-autofill-dl').remove();
    });

    jQuery(".gd-trigger").on("click",function() {
        jQuery(this).toggleClass("active").next().slideToggle("slow");
        var aD = jQuery(this).toggleClass("active").next().hasClass('map_category') ? true : false;
        if (jQuery(".gd-trigger").hasClass("gd-triggeroff")) {
            jQuery(".gd-trigger").removeClass('gd-triggeroff');
            jQuery(".gd-trigger").addClass('gd-triggeron');
            if (aD) {
                gd_compress_animate(this, 0);
            }
        } else {
            jQuery(".gd-trigger").removeClass('gd-triggeron');
            jQuery(".gd-trigger").addClass('gd-triggeroff');
            if (aD) {
                gd_compress_animate(this, parseFloat(jQuery(this).toggleClass("active").next().outerWidth()));
            }
        }
    });

    jQuery(".gd-trigger").each(function() {
        if (jQuery(this).hasClass('gd-triggeroff') && jQuery(this).next().hasClass('map_category')) {
            gd_compress_animate(this, parseFloat(jQuery(this).next().outerWidth()));
        }
    });

    jQuery(".trigger_sticky").on("click",function() {
        var effect = 'slide';
        var options = {
            direction: 'right'
        };
        var duration = 500;
        var tigger_sticky = jQuery(this);
        // tigger_sticky.hide();
        // jQuery('div.stickymap').toggle(effect, options, duration, function() {
        //     tigger_sticky.show();
        // });

        jQuery('body').toggleClass('stickymap_hide');

        if (tigger_sticky.hasClass("triggeroff_sticky")) {
            tigger_sticky.removeClass('triggeroff_sticky');
            tigger_sticky.addClass('triggeron_sticky');
            // setCookie('geodir_stickystatus', 'shide', 1);
            if (geodir_is_localstorage()) {
                localStorage.setItem("gd_sticky_map", 'shide');
            }
        } else {
            tigger_sticky.removeClass('triggeron_sticky');
            tigger_sticky.addClass('triggeroff_sticky');
            // setCookie('geodir_stickystatus', 'sshow', 1);
            if (geodir_is_localstorage()) {
                localStorage.setItem("gd_sticky_map", 'sshow');
            }
        }
    });

    function gd_compress_animate(e, r) {
        jQuery(e).animate({
            "margin-right": r + "px"
        }, "fast");
    }

    var gd_modal = "undefined" != typeof geodir_params.gd_modal && 1 == parseInt(geodir_params.gd_modal) ? false : true;

    /* Show Hide Rating for reply */
    jQuery('.gd_comment_replaylink a').on('click', function() {
        jQuery('#commentform #err_no_rating').remove();
        jQuery('#commentform .gd_rating').hide();
        jQuery('#commentform .br-wrapper.br-theme-fontawesome-stars').hide();
        jQuery('#commentform #geodir_overallrating').val('0');
        jQuery('#respond .form-submit input#submit').val(geodir_params.gd_cmt_btn_post_reply);
        jQuery('#respond .comment-form-comment label').html(geodir_params.gd_cmt_btn_reply_text);
    });

    jQuery('.gd-cancel-replaylink a').on('click', function() {
        jQuery('#commentform #err_no_rating').remove();
        jQuery('#commentform .gd_rating').show();
        jQuery('#commentform .br-wrapper.br-theme-fontawesome-stars').show();
        jQuery('#commentform #geodir_overallrating').val('0');
        jQuery('#respond .form-submit input#submit').val(geodir_params.gd_cmt_btn_post_review);
        jQuery('#respond .comment-form-comment label').html(geodir_params.gd_cmt_btn_review_text);
    });

    jQuery('#commentform .gd-rating-input-wrap').each(function() {
        var rat_obj = this;
        var $frm_obj = jQuery(rat_obj).closest('#commentform');

        if (parseInt($frm_obj.find('#comment_parent').val()) > 0) {
            jQuery('#commentform #err_no_rating').remove();
            jQuery('#commentform .gd_rating').hide();
            jQuery('#respond .form-submit input#submit').val(geodir_params.gd_cmt_btn_post_reply);
            jQuery('#respond .comment-form-comment label').html(geodir_params.gd_cmt_btn_reply_text);
        }

        if (!geodir_params.multirating) {
            $frm_obj.find('input[name="submit"]').on("click",function(e) {
                $frm_obj.find('#err_no_rating').remove();

                // skip rating stars validation if rating stars disabled
                if (typeof geodir_params.gd_cmt_disable_rating != 'undefined' && geodir_params.gd_cmt_disable_rating) {
                    return true;
                }
                //
                var is_review = parseInt($frm_obj.find('#comment_parent').val());
                is_review = is_review == 0 ? true : false;

                if (is_review) {
                    var btn_obj = this;
                    var invalid = 0;

                    $frm_obj.find('input[name^=geodir_overallrating]').each(function() {
                        var star_obj = this;
                        var star = parseInt(jQuery(star_obj).val());
                        if (!star > 0) {
                            invalid++;
                        }
                    });

                    if (invalid > 0) {
                        jQuery(rat_obj).after('<div id="err_no_rating" class="err-no-rating">' + geodir_params.gd_cmt_err_no_rating + '</div>');
                        return false;
                    }
                    return true;
                }
            });
        }
    });
});

/* Show Hide Filters End */
/* Hide Pinpoint If Listing MAP Not On Page */
jQuery(window).on("load", function() {
    if (jQuery(".map_background").length == 0) {
        jQuery('.geodir-pinpoint').hide();
    } else {
        jQuery('.geodir-pinpoint').show();
    }
});

//-------count post according to term--
function geodir_get_post_term(el) {
    limit = jQuery(el).data('limit');
    term = jQuery(el).val(); //data('term');
    var parent_only = parseInt(jQuery(el).data('parent')) > 0 ? 1 : 0;
    jQuery(el).parent().parent().find('.geodir-popular-cat-list').html('<i class="fas fa-cog fa-spin" aria-hidden="true"></i>');
    jQuery(el).parent().parent().parent().find('.geodir-cat-list-more').hide();
    jQuery.post(geodir_params.ajax_url + '?action=geodir_ajax_action', {
        ajax_action: "geodir_get_term_list",
        term: term,
        limit: limit,
        parent_only: parent_only
    }).done(function(data) {
        if (jQuery.trim(data) != '') {
            jQuery(el).parent().parent().find('.geodir-popular-cat-list').hide().html(data).fadeIn('slow');
            if (jQuery(el).parent().parent().find('.geodir-popular-cat-list li').length > limit) {
                jQuery(el).parent().parent().parent().find('.geodir-cat-list-more').fadeIn('slow');
            }
        }
    });
}

/* we recalc the stars because some browsers can't do subpixle percents, we should be able to remove this in a few years. */
jQuery(window).on("load", function() {
    geodir_resize_rating_stars();

    jQuery(document).on('click', '.geodir-rating,.gd-star-rating', function(e) {
        if (reviewLink = jQuery(this).closest('.geodir-category-listing').find('a.geodir-pcomments').attr('href')) {
            window.location = reviewLink;
        } else if (reviewLink = jQuery(this).closest('.gd-bubble').find('a.geodir-pcomments').attr('href')) {
            window.location = reviewLink;
        }
    });
    jQuery('.geodir-details-sidebar-rating').on('click', '.geodir-rating,.gd-star-rating', function(e) {
        jQuery('#gd-tabs [data-tab="#reviews"]').trigger('click');
        jQuery('html, body').animate({
            scrollTop: jQuery('#reviews-wrap').offset().top
        }, 500);
    });
});

jQuery(window).on("resize",function() {
    geodir_resize_rating_stars(true);
});

/* Adjust/resize rating stars width. */
function geodir_resize_rating_stars(re) {
    jQuery('.geodir-rating').each(function() {
        var $this = jQuery(this);
        var parent_width = $this.width();
        if (!parent_width) {
            return true;
        }
        var star_width = $this.find(".geodir_Star img").width();
        var star_count = $this.find(".geodir_Star img").length;
        var width_calc = star_width * star_count;
        width_calc = typeof re != 'undefined' && re ? 'auto' : width_calc;
        $this.width(width_calc);
    });
}

function geodir_load_search_form(stype, el) {
    var $adv_show = jQuery(el).closest('.geodir-search-container').attr('data-show-adv');

    jQuery.ajax({
        url: geodir_params.ajax_url,
        type: 'POST',
        dataType: 'html',
        data: {
            action: 'geodir_search_form',
            stype: stype,
            adv: $adv_show
        },
        beforeSend: function() {
            geodir_search_wait(1);
        },
        success: function(data, textStatus, xhr) {
            var $container = jQuery(el).closest('.geodir-search-container');
            if (jQuery('select.search_by_post', $container).length && jQuery('.gd-search-input-wrapper.gd-search-field-near', $container).length) {
                var before = jQuery('.gd-search-input-wrapper.gd-search-field-near', $container).is(':visible');
                var nearH = jQuery('.gd-search-input-wrapper.gd-search-field-near').prop('outerHTML');

                if (jQuery('input[name="sgeo_lat"]', $container).length && jQuery('input[name="sgeo_lon"]', $container).length) {
                    var latH = jQuery('input[name="sgeo_lat"]', $container).prop('outerHTML');
                    var lngH = jQuery('input[name="sgeo_lon"]', $container).prop('outerHTML');
                }
                if (jQuery('input.geodir-location-search-type', $container).length) {
                    var typeH = jQuery('input.geodir-location-search-type', $container).prop('outerHTML');
                }
            }

            // replace whole form
            $container.html(data);

            if (typeof nearH != 'undefined') {
                var after = jQuery('.gd-search-input-wrapper.gd-search-field-near', $container).is(':visible');
                jQuery('.gd-search-input-wrapper.gd-search-field-near', $container).replaceWith(nearH);
                var $near = jQuery('.gd-search-input-wrapper.gd-search-field-near', $container);
                if (before && !after) {
                    $near.hide();
                    jQuery('input[name="snear"]', $near).hide();
                } else if (!before && after) {
                    $near.show();
                    jQuery('input[name="snear"]', $near).show();
                }

                if (typeof latH != 'undefined' && typeof lngH != 'undefined') {
                    jQuery('input[name="sgeo_lat"]', $container).replaceWith(latH);
                    jQuery('input[name="sgeo_lon"]', $container).replaceWith(lngH);
                }
                if (typeof typeH != 'undefined') {
                    jQuery('input.geodir-location-search-type', $container).replaceWith(typeH);
                }
            }

            geodir_setup_search_form();
            // trigger a custom event wen setting up the search form so we can hook to it from addons
            jQuery("body").trigger("geodir_setup_search_form", $container.find('fome[name="geodir-listing-search"]'));

            geodir_search_wait(0);
        },
        error: function(xhr, textStatus, errorThrown) {
            console.log(textStatus);
            geodir_search_wait(0);
        }
    });
}

function geodir_setup_search_form() {
    //  new seach form change
    if (jQuery('.search_by_post').val()) {
        gd_s_post_type = jQuery('.search_by_post').val();
    } else {
        gd_s_post_type = "gd_place";
    }

    setTimeout(function() {
        jQuery('.search_by_post').on("change",function() {
            gd_s_post_type = jQuery(this).val();

            geodir_load_search_form(gd_s_post_type, this);

        });
    }, 100);
}

gdSearchDoing = 0;
var gdNearChanged = 0;
gd_search_icon = '';

function geodir_search_wait(on) {
    waitTime = 300;

    if (on) {
        if (gdSearchDoing) {
            return;
        }
        gdSearchDoing = 1;
        jQuery('.geodir_submit_search').addClass('gd-wait-btnsearch').prop('disabled', true);
        jQuery('.showFilters').prop('disabled', true);
        searchPos = 1;
        gd_search_icon = jQuery('.geodir_submit_search').html();

        function geodir_search_wait_animate() {
            if (!searchPos) {
                return;
            }
            if (searchPos == 1) {
                jQuery('input[type="button"].geodir_submit_search').val('  ');
                searchPos = 2;
                window.setTimeout(geodir_search_wait_animate, waitTime);
                return;
            }
            if (searchPos == 2) {
                jQuery('input[type="button"].geodir_submit_search').val('  ');
                searchPos = 3;
                window.setTimeout(geodir_search_wait_animate, waitTime);
                return;
            }
            if (searchPos == 3) {
                jQuery('input[type="button"].geodir_submit_search').val('  ');
                searchPos = 1;
                window.setTimeout(geodir_search_wait_animate, waitTime);
                return;
            }

        }
        geodir_search_wait_animate();
        jQuery('button.geodir_submit_search').html('<i class="fas fa-hourglass fa-spin" aria-hidden="true"></i>');
    } else {
        searchPos = 0;
        gdSearchDoing = 0;
        jQuery('.geodir_submit_search').removeClass('gd-wait-btnsearch').prop('disabled', false);
        jQuery('.showFilters').prop('disabled', false);
        gdsText = jQuery('input[type="button"].geodir_submit_search').data('title');
        jQuery('input[type="button"].geodir_submit_search').val(gdsText);

        jQuery('button.geodir_submit_search').html(gd_search_icon);
    }

}

function geodir_click_search($this) {
    //we delay this so other functions have a change to change setting before search
    setTimeout(function() {
        jQuery($this).parent().find('.geodir_submit_search').trigger("click");
    }, 100);
}

function gd_fav_save(post_id) {
    var ajax_action;
    if (jQuery('.favorite_property_' + post_id + ' a').hasClass('geodir-removetofav-icon')) {
        ajax_action = 'remove';
    } else {
        ajax_action = 'add';
    }
    jQuery.ajax({
        url: geodir_params.gd_ajax_url,
        type: 'GET',
        dataType: 'json',
        data: {
            action: 'geodir_user_add_fav',
            type_action: ajax_action,
            security: geodir_params.basic_nonce,
            pid: post_id
        },
        timeout: 20000,
        error: function() {
            alert(geodir_params.loading_listing_error_favorite);
        },
        success: function(data) {
            if (data.success) {
                var action_text = (data.data && data.data.action_text) ? data.data.action_text : '';
                if (ajax_action == 'remove') {
                    jQuery('.favorite_property_' + post_id + ' a').each(function(index) {
                        $color_value = jQuery(this).data("color-off");
                        $icon_value = jQuery(this).data("icon");
                        $style = $color_value ? "style='color:" + $color_value + "'" : "";
                        $icon = $icon_value ? $icon_value : geodir_params.icon_fav;
                        jQuery(this)
                            .removeClass('geodir-removetofav-icon')
                            .addClass('geodir-addtofav-icon')
                            .attr("title", geodir_params.text_add_fav)
                            .html('<i ' + $style + ' class="' + $icon + '"></i> <span class="geodir-fav-text">' + ' ' + (action_text ? action_text : geodir_params.text_fav) + '</span>');
                    });

                } else {

                    jQuery('.favorite_property_' + post_id + ' a').each(function(index) {
                        $color_value = jQuery(this).data("color-on");
                        $icon_value = jQuery(this).data("icon");
                        $style = $color_value ? "style='color:" + $color_value + "'" : "";
                        $icon = $icon_value ? $icon_value : geodir_params.icon_fav;
                        jQuery(this)
                            .removeClass('geodir-addtofav-icon')
                            .addClass('geodir-removetofav-icon')
                            .attr("title", geodir_params.text_remove_fav)
                            .html('<i ' + $style + ' class="' + $icon + '"></i> <span class="geodir-fav-text">' + ' ' + (action_text ? action_text : geodir_params.text_unfav) + '</span>');
                    });

                }
            } else {
                alert(geodir_params.loading_listing_error_favorite);
            }
        }
    });
    return false;
}

function geodir_refresh_business_hours() {
    jQuery('.gd-bh-show-field').each(function() {
        geodir_refresh_business_hour(jQuery(this));
    });
}

function geodir_refresh_business_hour($this) {
    var d, $d, hours, day, mins, time, hasOpen = false,
        hasPrevOpen = false,
        hasClosed = false,
        isOpen, o, c, nd, label, times = [],
        opens = [],
        prevtimes = [],
        prevopens = [];
    d = new Date(), utc = d.getTime() + (d.getTimezoneOffset() * 60000), d = new Date(utc + (parseInt(jQuery('.gd-bh-expand-range', $this).data('offsetsec')) * 1000));
    date = d.getFullYear() + '-' + (("0" + (d.getMonth() + 1)).slice(-2)) + '-' + (("0" + (d.getDate())).slice(-2)) + 'T' + (("0" + (d.getHours())).slice(-2)) + ':' + (("0" + (d.getMinutes())).slice(-2)) + ':' + (("0" + (d.getSeconds())).slice(-2));
    console.log(date + jQuery('.gd-bh-expand-range', $this).data('offset'));
    jQuery('.gd-bh-expand-range', $this).attr('data-date', date);
    hours = d.getHours(), mins = d.getMinutes(), day = d.getDay();
    if (day < 1) {
        day = 7;
    }
    time = ("0" + hours).slice(-2) + ("0" + mins).slice(-2);
    $this.attr('data-t', time);
    $d = $this.find('[data-day="' + parseInt(day) + '"]');

    // close on next day
    prevD = day > 1 ? day - 1 : 7;
    if ($this.find('[data-day="' + prevD + '"] .gd-bh-next-day').length) {
        $pd = $this.find('[data-day="' + prevD + '"]');
        $this.removeClass('gd-bh-open gd-bh-close');
        $this.find('div').removeClass('gd-bh-open gd-bh-close gd-bh-days-open gd-bh-days-close gd-bh-slot-open gd-bh-slot-close gd-bh-days-today');
        $pd.addClass('gd-bh-days-prevday');
        $pd.find('.gd-bh-slot').each(function() {
            isOpen = false;
            o = jQuery(this).data('open'), c = jQuery(this).data('close');
            if (o != 'undefined' && c != 'undefined' && o !== '' && c !== '') {
                if (time <= parseInt(c)) {
                    isOpen = true;
                }
            }
            if (isOpen) {
                hasPrevOpen = true;
                jQuery(this).addClass('gd-bh-slot-open');
                prevopens.push($pd.find('.gd-bh-days-d').text() + " " + jQuery(this).find('.gd-bh-slot-r').html());
            } else {
                jQuery(this).addClass('gd-bh-slot-close');
            }
            prevtimes.push($pd.find('.gd-bh-days-d').text() + " " + jQuery(this).find('.gd-bh-slot-r').html());
        });
        if (hasPrevOpen) {
            prevtimes = prevopens;
            $pd.addClass('gd-bh-days-open');
        } else {
            $pd.addClass('gd-bh-days-close');
        }
        jQuery('.gd-bh-today-range', $this).html(prevtimes.join(', '));
    }
    if ($d.length) {
        dayname = '';
        if (hasPrevOpen) {
            times = prevtimes;
            opens = prevopens;
            dayname = $d.find('.gd-bh-days-d').text() + " ";
        } else {
            $this.removeClass('gd-bh-open gd-bh-close');
            $this.find('div').removeClass('gd-bh-open gd-bh-close gd-bh-days-open gd-bh-days-close gd-bh-slot-open gd-bh-slot-close gd-bh-days-today');
        }
        $d.addClass('gd-bh-days-today');
        if ($d.data('closed') != '1') {
            $d.find('.gd-bh-slot').each(function() {
                isOpen = false;
                o = jQuery(this).data('open'), c = jQuery(this).data('close'), nd = jQuery(this).hasClass('gd-bh-next-day');
                if (o != 'undefined' && c != 'undefined' && o !== '' && c !== '') {
                    if (parseInt(o) <= time && (time <= parseInt(c) || nd)) {
                        isOpen = true;
                    }
                }
                if (isOpen) {
                    hasOpen = true;
                    jQuery(this).addClass('gd-bh-slot-open');
                    opens.push(dayname + jQuery(this).find('.gd-bh-slot-r').html());
                } else {
                    jQuery(this).addClass('gd-bh-slot-close');
                }
                if ((hasPrevOpen && hasOpen) || !hasPrevOpen) {
                    times.push(dayname + jQuery(this).find('.gd-bh-slot-r').html());
                }
            });
        } else {
            hasClosed = true;
        }
        if (hasOpen) {
            times = opens;
            $d.addClass('gd-bh-days-open');
        } else {
            $d.addClass('gd-bh-days-close');
        }
        if (times) {
            times = jQuery.uniqueSort(times);
        }
        jQuery('.gd-bh-today-range', $this).html(times.join(', '));
    }
    if (hasOpen || hasPrevOpen) {
        label = geodir_params.txt_open_now;
        $this.addClass('gd-bh-open');
    } else {
        label = hasClosed ? geodir_params.txt_closed_today : geodir_params.txt_closed_now;
        $this.addClass('gd-bh-close');
    }
    jQuery('.geodir-i-biz-hours font', $this).html(label);
}

/**
 * Our own switchClass so we don't have to add jQuery UI.
 */
(function($) {
    $.fn.GDswitchClass = function(remove, add) {
        var style = {
            'transition-property': 'all',
            'transition-duration': '0.6s',
            'transition-timing-function': 'ease-out'
        };

        return this.each(function() {
            $(this).css(style).removeClass(remove).addClass(add)
        });
    };
}(jQuery));

function init_read_more() {
    var $el, $ps, $up, totalHeight;

    jQuery('.geodir-category-list-view  .geodir-post-meta-container .geodir-field-post_content').each(function() {
        jQuery(this).addClass('gd-read-more-wrap').wrapInner("<p></p>").append('<p class="gd-read-more-fade"><a href="#" class="gd-read-more-button">' + geodir_params.txt_read_more + '</a></p>');
    });

    // Make the read more visable if the text needs it
    jQuery('.gd-read-more-wrap').each(function() {
        var height = jQuery(this).height();
        var maxHeight = parseInt(jQuery(this).css('max-height'), 10);
        if (height >= maxHeight) {
            jQuery(this).find('.gd-read-more-fade').show();
        }
    });

    jQuery(".gd-read-more-wrap .gd-read-more-button").on("click",function() {

        totalHeight = 0;

        $el = jQuery(this);
        $p = $el.parent();
        $up = $p.parent();
        $ps = $up.find("p:not('.gd-read-more-fade')");

        // measure how tall inside should be by adding together heights of all inside paragraphs (except read-more paragraph)
        $ps.each(function() {
            totalHeight += jQuery(this).outerHeight();
        });

        $up
            .css({
                // Set height to prevent instant jumpdown when max height is removed
                "height": $up.height(),
                "max-height": 9999
            })
            .animate({
                "height": totalHeight
            });

        // fade out read-more
        $p.fadeOut();

        // prevent jump-down
        return false;

    });
}

function gd_delete_post($post_id) {
    var message = geodir_params.my_place_listing_del;
    if (confirm(message)) {

        jQuery.ajax({
            url: geodir_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'geodir_user_delete_post',
                security: geodir_params.basic_nonce,
                post_id: $post_id
            },
            timeout: 20000,
            success: function(data) {

                if (data.success) {
                    lity('<div class="gd-notification gd-success"><i class="fas fa-check-circle"></i> ' + data.data.message + '</div>');
                    jQuery('.post-' + $post_id + '[data-post-id="' + $post_id + '"]').fadeOut();
                    if (data.data.redirect_to && jQuery('body').hasClass('single') && jQuery('body').hasClass('postid-' + $post_id)) {
                        setTimeout(function() {
                            window.location = data.data.redirect_to;
                        }, 3000);
                    }
                } else {
                    lity('<div class="gd-notification gd-error"><i class="fas fa-exclamation-circle"></i> ' + data.data.message + '</div>');
                }
            }
        });

        return true;
    } else {
        return false;
    }
}

function geodir_post_author_action(el, post_id, action) {
    var _confirm = geodir_params.confirmPostAuthorAction;
    if (jQuery(el).text()) {
        _confirm = jQuery(el).text() + ': ' + _confirm;
    }

    if (confirm(_confirm)) {
        var data = {
            action: 'geodir_post_author_action',
            _action: action,
            post_id: post_id,
            security: geodir_params.basic_nonce,
        };

        jQuery.ajax({
                url: geodir_params.ajax_url,
                type: 'POST',
                data: data,
                dataType: 'json',
                beforeSend: function(xhr, obj) {
                    jQuery(el).addClass('disabled');
                }
            })
            .done(function(data, textStatus, jqXHR) {
                if (data.data.message) {
                    if (data.success) {
                        lity('<div class="gd-notification gd-success"><i class="fas fa-check-circle"></i> ' + data.data.message + '</div>');
                    } else {
                        lity('<div class="gd-notification gd-error"><i class="fas fa-exclamation-circle"></i> ' + data.data.message + '</div>');
                    }
                }

                if (data.data.redirect_to) {
                    setTimeout(function() {
                        if (data.data.redirect_to === true || data.data.redirect_to === 1) {
                            window.location.reload()
                        } else {
                            window.location = data.data.redirect_to;
                        }
                    }, 3000);
                }
            })
            .always(function(data, textStatus, jqXHR) {
                jQuery(el).removeClass('disabled');
            });

        return true;
    } else {
        return false;
    }
}

/**
 * Loads an action inot a lity lighbox, such as the ninja form shortcode.
 *
 * @param $action
 * @param $nonce
 * @param $post_id
 * @param $extra
 */
function gd_ajax_lightbox($action, $nonce, $post_id, $extra) {
    if ($action) {
        if (!$nonce || $nonce == '') {
            $nonce = geodir_params.basic_nonce;
        }
        $content = "<div class='geodir-ajax-content ''>Loading content</div>";
        $lightbox = '';

        if ($action == 'geodir_ninja_forms') {
            // clear all form data so we can reload the same form via ajax
            delete form;
            delete formDisplay;
            delete nfForms;
        }

        $lightbox = lity(geodir_params.ajax_url + "?action=" + $action + "&security=" + $nonce + "&p=" + $post_id + "&extra=" + $extra);
        return;

        jQuery.ajax({
            url: geodir_params.ajax_url,
            type: 'POST',
            // dataType: 'json',
            data: {
                action: $action,
                security: $nonce,
                post_id: $post_id,
                extra: $extra
            },
            //timeout: 20000,
            beforeSend: function() {
                $lightbox = lity($content);
            },
            success: function(content) {
                //jQuery('.geodir-ajax-content').html(content);
                jQuery('.geodir-ajax-content').addClass('lity-show').html(content);
            }
        });
    }
}

/**
 * Change the texts for the reply/review comments section.
 */
function gd_init_comment_reply_link() {
    jQuery(".geodir-page-single .comment-reply-link").on('click', function(e) {
        geodirOnReplyClick(this, e);
    });

    /* Detect reply button click event on touch screen device */
    jQuery(".geodir-page-single .comment-reply-link").on('touchstart', function(e) {
        geodirOnReplyClick(this, e);
    });

    jQuery(".geodir-page-single #cancel-comment-reply-link").on('click', function(e) {
        geodirOnCancelReplyClick(this, e);
    });

    /* Detect cancel reply button click on touch screen device */
    jQuery(".geodir-page-single #cancel-comment-reply-link").on('touchstart', function(e) {
        geodirOnCancelReplyClick(this, e);
    });
}

function geodirOnReplyClick(el, e) {
    setTimeout(function() {
        jQuery('#reply-title').contents().filter(function() {
            return this.nodeType == 3
        }).each(function() {
            this.textContent = this.textContent.replace(geodir_params.txt_leave_a_review, geodir_params.txt_leave_a_reply);
        });

        $html = jQuery('#respond .comment-form-comment').html();
        $new_html = $html.replace(geodir_params.txt_review_text, geodir_params.txt_reply_text);
        jQuery('#respond .comment-form-comment').html($new_html);

        var btnEl = jQuery('#respond input.submit').length ? '#respond input.submit' : '#respond input#submit';
        $html = jQuery(btnEl).val();
        $new_html = $html.replace(geodir_params.txt_post_review, geodir_params.txt_post_reply);
        jQuery(btnEl).val($new_html);
        jQuery(btnEl).closest('form').toggleClass('geodir-form-review-reply', true);

        jQuery('#respond .gd-rating-input-wrap').hide();
    }, 10);
}

function geodirOnCancelReplyClick(el, e) {
    setTimeout(function() {
        jQuery('#reply-title').contents().filter(function() {
            return this.nodeType == 3
        }).each(function() {
            this.textContent = this.textContent.replace(geodir_params.txt_leave_a_reply, geodir_params.txt_leave_a_review);
        });

        $html = jQuery('#respond .comment-form-comment').html();
        $new_html = $html.replace(geodir_params.txt_reply_text, geodir_params.txt_review_text);
        jQuery('#respond .comment-form-comment').html($new_html);

        var btnEl = jQuery('#respond input.submit').length ? '#respond input.submit' : '#respond input#submit';
        $html = jQuery(btnEl).val();
        $new_html = $html.replace(geodir_params.txt_post_reply, geodir_params.txt_post_review);
        jQuery(btnEl).val($new_html);
        jQuery(btnEl).closest('form').toggleClass('geodir-form-review-reply', false);

        jQuery('#respond .gd-rating-input-wrap').show();
    }, 10);
}

/**
 * Load a slider image via ajax.
 *
 * @param slide
 */
function geodir_ajax_load_slider(slide) {
    // fix the srcset
    if (real_srcset = jQuery(slide).find('img').attr("data-srcset")) {
        if (!jQuery(slide).find('img').attr("srcset")) jQuery(slide).find('img').attr("srcset", real_srcset);
    }
    // fix the src
    if (real_src = jQuery(slide).find('img').attr("data-src")) {
        if (!jQuery(slide).find('img').attr("srcset")) jQuery(slide).find('img').attr("src", real_src);
    }
}

/**
 * Init a slider by id.
 *
 * @param $id
 */
function geodir_init_slider($id) {

    // chrome 53 introduced a bug, so we need to repaint the slider when shown.
    jQuery('#' + $id + ' .geodir-slides').addClass('flexslider-fix-rtl');
    jQuery("#" + $id + "_carousel").flexslider({
        animation: "slide",
        namespace: "geodir-",
        selector: ".geodir-slides > li",
        controlNav: !1,
        directionNav: !1,
        animationLoop: !1,
        slideshow: !1,
        itemWidth: 75,
        itemMargin: 5,
        asNavFor: "#" + $id,
        rtl: 1 == parseInt(geodir_params.is_rtl) ? !0 : !1
    }), jQuery("#" + $id).flexslider({

        // enable carousel if settings are right
        itemWidth: jQuery("#" + $id).attr("data-item-width") ? parseInt(jQuery("#" + $id).attr("data-item-width")) : (jQuery("#" + $id).attr("data-limit_show") ? 210 : ""), // needed to be considered a carousel
        itemMargin: jQuery("#" + $id).attr("data-item-margin") ? parseInt(jQuery("#" + $id).attr("data-item-margin")) : (jQuery("#" + $id).attr("data-limit_show") ? 3 : ""),
        minItems: jQuery("#" + $id).attr("data-limit_show") ? 1 : "",
        maxItems: jQuery("#" + $id).attr("data-limit_show") ? jQuery("#" + $id).attr("data-limit_show") : "",

        animation: jQuery("#" + $id).attr("data-animation") == 'fade' ? "fade" : "slide",
        selector: jQuery("#" + $id).attr("data-selector") ? jQuery("#" + $id).attr("data-selector") : ".geodir-slides > li",
        namespace: "geodir-",
        // controlNav: !0,
        controlNav: parseInt(jQuery("#" + $id).attr("data-controlnav")),
        directionNav: typeof jQuery("#" + $id).attr("data-directionnav") != 'undefined' ? parseInt(jQuery("#" + $id).attr("data-directionnav")) : 1,
        prevText: '<i class="fas fa-angle-right"></i>', // we flip with CSS
        nextText: '<i class="fas fa-angle-right"></i>',
        animationLoop: !0,
        slideshow: parseInt(jQuery("#" + $id).attr("data-slideshow")),
        sync: "#" + $id + "_carousel",
        slideshowSpeed: parseInt(jQuery("#" + $id).attr("data-slideshow-speed")) > 100 ? parseInt(jQuery("#" + $id).attr("data-slideshow-speed")) : 7000,
        start: function(slider) {

            // chrome 53 introduced a bug, so we need to repaint the slider when shown.
            jQuery('#' + $id + ' .geodir-slides').removeClass('flexslider-fix-rtl');
            jQuery("#" + $id).removeClass('geodir-slider-loading');

            jQuery("#" + $id).closest('.geodir-image-container,.geodir_flex-container').find(".geodir_flex-loader").hide();
            jQuery("#" + $id).css({
                visibility: "visible"
            }), jQuery("#" + $id + "_carousel").css({
                visibility: "visible"
            });

            // Ajax load the slides that are visible
            var $visible = slider.visible ? slider.visible : 1;

            // Load current slides
            var i = 0;
            for (; i < $visible;) {
                slide = slider.slides.eq(i);
                geodir_ajax_load_slider(slide);
                i++;
                // load next slide also
                slide_next = slider.slides.eq(i);
                geodir_ajax_load_slider(slide_next);
            }
        },
        before: function(slider) {
            // Ajax load the slides that are visible
            var $visible = slider.visible ? slider.visible : 1;

            if (isNaN($visible)) {
                $visible = 1;
            }

            // Load current slides
            var i = slider.animatingTo * $visible - 1;
            var $visible_next = i + $visible + 1;

            for (; i < $visible_next;) {
                slide = slider.slides.eq(i);
                geodir_ajax_load_slider(slide);
                i++;
                // load next slide also
                slide_next = slider.slides.eq(i);
                geodir_ajax_load_slider(slide_next);
            }
        },
        rtl: 1 == parseInt(geodir_params.is_rtl) ? !0 : !1
    });

}

/**
 * Init the rating inputs.
 */
function gd_init_rating_input() {
    /**
     * Rating script for ratings inputs.
     * @info This is shared in both post.js and admin.js any changes shoudl be made to both.
     */
    jQuery(".gd-rating-input").each(function() {
        $total = jQuery(this).find('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img').length;
        $parent = this;

        // set the current star value and text
        $value = jQuery(this).closest('.gd-rating-input').find('input').val();
        if ($value > 0) {
            jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground').width($value / $total * 100 + '%');
            jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text(jQuery(this).closest('.gd-rating-input').find('svg, img' + ':eq(' + ($value - 1) + '), i' + ':eq(' + ($value - 1) + ')').attr("title"));
        }

        // loop all rating stars
        jQuery(this).find('i,svg, img').each(function(index) {
            $original_rating = jQuery(this).closest('.gd-rating-input').find('input').val();
            $total = jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground > i, .gd-rating-foreground > svg, .gd-rating-foreground > img').length;
            $original_percent = $original_rating / $total * 100;
            $rating_set = false;

            jQuery(this).hover(
                function() {
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
                function() {
                    if (!$rating_set) {
                        jQuery(this).closest('.gd-rating-input').find('.gd-rating-foreground').width($original_percent + '%');
                        jQuery(this).closest('.gd-rating-input').find('.gd-rating-text').text($original_rating_text);
                    } else {
                        $rating_set = false;
                    }
                }
            );

            jQuery(this).on("click",function() {
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
 * Bounce the marker when the listing item is hovered over.
 */
function geodir_animate_markers() {
    if (typeof(animate_marker) == 'function') {
        var groupTab = jQuery("ul.geodir-category-list-view").children("li");
        groupTab.hover(function() {
            animate_marker('listing_map_canvas', String(jQuery(this).data("post-id")));
        }, function() {
            stop_marker_animation('listing_map_canvas', String(jQuery(this).data("post-id")));
        });

        // maybe elementor animate
        if (jQuery('body.archive .elementor-widget-archive-posts').length) {
            var ePosts = jQuery("body.archive .elementor-widget-archive-posts .elementor-posts").children(".elementor-post");
            ePosts.hover(function() {
                $post_id = jQuery(this).attr('class').match(/post-\d+/)[0].replace("post-", "");
                animate_marker('listing_map_canvas', String($post_id));
            }, function() {
                $post_id = jQuery(this).attr('class').match(/post-\d+/)[0].replace("post-", "");
                stop_marker_animation('listing_map_canvas', String($post_id));
            });
        }
    } else {
        window.animate_marker = function() {};
        window.stop_marker_animation = function() {};
    }
}

/**
 * Test if browser local storage is available.
 *
 * @returns {boolean}
 */
function geodir_is_localstorage() {
    var test = 'geodirectory';
    try {
        localStorage.setItem(test, test);
        localStorage.removeItem(test);
        return true;
    } catch (e) {
        return false;
    }
}

/**
 * Prevent onclick affecting parent elements.
 *
 * @param e
 */
function geodir_cancelBubble(e) {
    var evt = e ? e : window.event;
    if (evt.stopPropagation) evt.stopPropagation();
    if (evt.cancelBubble != null) evt.cancelBubble = true;
}

/**
 * Get the user GPS position.
 *
 * @param $success The function to call on sucess.
 * @param $fail The function to all on fail.
 */
function gd_get_user_position($success, $fail) {
    window.gd_user_position_success_callback = $success;
    window.gd_user_position_fail_callback = $fail;
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(gd_user_position_success, gd_user_position_fail);
    } else {
        gd_user_position_fail(-1)
    }
}

/**
 * The function called on success of getting user position, it will then call the callback function.
 * Not used direct.
 *
 * @param position
 */
function gd_user_position_success(position) {
    var coords = position.coords || position.coordinate || position;
    if (coords && coords.latitude && coords.longitude) {
        // run the success function
        var fn = window.gd_user_position_success_callback;
        if (typeof fn === 'function') {
            fn(coords.latitude, coords.longitude);
        }
    }
}

/**
 * The function called on fail of getting user position, it will then call the callback function.
 * Not used direct.
 *
 * @param err
 */
function gd_user_position_fail(err) {
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
    if (window.gd_user_position_success) {
        $success = window.gd_user_position_success_callback;
    } else {
        $success = '';
    }
    gd_manually_set_user_position(msg, $success);
}

/**
 * Lets the user set their position manually on a map.
 * Not called direct.
 *
 * @param $msg
 */
function gd_manually_set_user_position($msg) {
    if (window.confirm(geodir_params.confirm_lbl_error + " " + $msg + "\n" + geodir_params.confirm_set_location)) {

        var $prefix = "geodir_manual_location_";

        jQuery.post(geodir_params.ajax_url, {
            action: 'geodir_manual_map',
            trigger: $prefix + '_trigger'
            //trigger: $successFunction
        }, function(data) {
            if (data) {
                $lity = lity("<div class='lity-show'>" + data + "</div>");
                // map center is off due to lightbox zoom effect so we resize to fix
                setTimeout(function() {
                    jQuery('.lity-show .geodir_map_container').css('width', '90%').css('width', '99.99999%');
                    window.dispatchEvent(new Event('resize')); // OSM does not work with the jQuery trigger so we do it old tool.
                }, 500);

                jQuery(window).off($prefix + '_trigger');
                jQuery(window).on($prefix + '_trigger', function(event, lat, lon) {
                    if (lat && lon) {
                        var position = {};
                        position.latitude = lat;
                        position.longitude = lon;
                        // window[$successFunction](position);
                        var fn = window.gd_user_position_success_callback;
                        if (typeof fn === 'function') {
                            fn(lat, lon);
                        }
                        $lity.close();
                    }
                });

                return false;
            }
        });

    } else {
        // call the fail function if exists
        if (window.gd_user_position_fail_callback) {
            var fn = window.gd_user_position_fail_callback;
            if (typeof fn === 'function') {
                fn();
            }
        }
    }
}

function gd_set_get_directions($lat, $lon) {
    if (jQuery('#gd_map_canvas_post_fromAddress').length) {
        jQuery('#gd_map_canvas_post_fromAddress').val($lat + "," + $lon);
        jQuery('.gd-map-get-directions').trigger('click');
    }
}

function geodir_widget_listings_pagination(id, params) {
    var $container, pagenum;
    $container = jQuery('#' + id);

    jQuery('.geodir-loop-paging-container', $container).each(function() {
        var $paging = jQuery(this);
        if (!$paging.hasClass('geodir-paging-setup')) {
            if (jQuery('.page-numbers .page-numbers', $paging).length) {
                jQuery('.page-numbers a.page-numbers', $paging).each(function() {
                    href = jQuery(this).attr('href');
                    hrefs = href.split("#");
                    page = (hrefs.length > 1 && parseInt(hrefs[1]) > 0 ? parseInt(hrefs[1]) : (parseInt(jQuery(this).text()) > 0 ? parseInt(jQuery(this).text()) : 1));
                    jQuery(this).attr('data-geodir-pagenum', page);
                    jQuery(this).attr('href', 'javascript:void(0)');
                });
            }
            $paging.addClass('geodir-paging-setup');
        }
    });

    jQuery("a.page-numbers", $container).on("click", function(e) {
        pagenum = parseInt(jQuery(this).data('geodir-pagenum'));
        if (!pagenum > 0) {
            return;
        }
        $widget = $container.closest('.geodir-listings');
        $listings = jQuery('.geodir-widget-posts', $container);

        params['pageno'] = pagenum;

        $widget.addClass('geodir-listings-loading');

        jQuery.ajax({
            type: "POST",
            url: geodir_params.ajax_url,
            data: params,
            success: function(res) {
                if (res.success && res.data) {
                    if (res.data.content) {
                        $widget.find('.geodir_locations.geodir-wgt-pagination').replaceWith(res.data.content);

                        init_read_more();
                        geodir_init_flexslider();
                        geodir_init_lazy_load();
                        geodir_refresh_business_hours();
                        geodir_load_badge_class();
                    }
                }
                $widget.removeClass('geodir-listings-loading');
            },
            fail: function(data) {
                $widget.removeClass('geodir-listings-loading');
            }
        });

        e.preventDefault();
    });
}

/**
 * A function to convert a time value to a "ago" time text.
 *
 * @param selector string The .class selector
 */
function geodir_time_ago(selector) {
    var templates = {
        prefix_ago: "",
        suffix_ago: " ago",
        prefix_after: "",
        suffix_after: "after ",
        seconds: "less than a minute",
        minute: "about a minute",
        minutes: "%d minutes",
        hour: "about an hour",
        hours: "about %d hours",
        day: "a day",
        days: "%d days",
        month: "about a month",
        months: "%d months",
        year: "about a year",
        years: "%d years"
    };
    for (var i in templates) {
        var _t = templates[i];
        if (geodir_params.time_ago && typeof geodir_params.time_ago[i] != 'undefined') {
            templates[i] = geodir_params.time_ago[i];
        }
    }

    var template = function(t, n) {
        return templates[t] && templates[t].replace(/%d/i, Math.abs(Math.round(n)));
    };

    var timer = function(time) {
        var _time, _time_now;
        if (!time) {
            return null;
        }
        time = time.replace(/\.\d+/, ""); // remove milliseconds
        time = time.replace(/-/, "/").replace(/-/, "/");
        time = time.replace(/T/, " ").replace(/Z/, " UTC");
        time = time.replace(/([\+\-]\d\d)\:?(\d\d)/, " $1$2"); // -04:00 -> -0400
        time = new Date(time * 1000 || time);

        var future = false;
        var now = new Date();
        _time = time.getTime();
        if (isNaN(_time)) {
            return null;
        }
        _time_now = now.getTime();
        var seconds = ((_time_now - _time) * 0.001);
        if (seconds < 0) {
            future = true;
            seconds = seconds * (-1);
        }
        var minutes = seconds / 60;
        var hours = minutes / 60;
        var days = hours / 24;
        var years = days / 365;

        if (future) {
            prefix = templates.prefix_after;
            suffix = templates.suffix_after;
        } else {
            prefix = templates.prefix_ago;
            suffix = templates.suffix_ago;
        }

        return prefix + (
            seconds < 45 && template('seconds', seconds) ||
            seconds < 90 && template('minute', 1) ||
            minutes < 45 && template('minutes', minutes) ||
            minutes < 90 && template('hour', 1) ||
            hours < 24 && template('hours', hours) ||
            hours < 42 && template('day', 1) ||
            days < 30 && template('days', days) ||
            days < 45 && template('month', 1) ||
            days < 365 && template('months', days / 30) ||
            years < 1.5 && template('year', 1) ||
            template('years', years)
        ) + suffix;
    };

    jQuery(selector).each(function() {
        var $this = jQuery(this),
            $_this, datetime = '',
            _datetime;
        if ($this.attr('datetime')) {
            $_this = $this;
            datetime = $this.attr('datetime').trim();
        } else if ($this.find('[datetime]').length && $this.find('[datetime]:first').attr('datetime')) {
            $_this = $this.find('[datetime]:first');
            datetime = $_this.attr('datetime').trim();
        } else if ($this.attr('title')) {
            $_this = $this;
            datetime = $this.attr('title').trim();
        }
        if ($_this && datetime) {
            _datetime = timer(datetime);
            if (_datetime) {
                _datetime = '<i class="far fa-clock"></i> ' + _datetime;
                $_this.html(_datetime);
            }
        }
    });
    // Update time every minute
    setTimeout(geodir_time_ago, 60000);
}

function geodir_init_listings_carousel(el, index) {
    var $el = jQuery(el),
        wEl = '.geodir-widget-posts',
        rEl = '.geodir-post',
        isElementor = false;
    if (!$el.find(wEl).length && $el.find('.elementor-posts').length) {
        var wEl = '.elementor-posts';
        var rEl = '.elementor-post';
        isElementor = true;
    }
    var items = $el.find(wEl + ' > ' + rEl).length,
        pitems = parseInt($el.data('with-items')),
        fWidth = parseFloat(jQuery(window).width());
    if (items > 0 && items > pitems) {
        var $item = $el.find(wEl + ' > ' + rEl + ':first').next();
        var iW = parseFloat($item.width()),
            iM = parseFloat($item.css('marginLeft')) + parseFloat($item.css('marginRight'));
        if (isElementor && !iM) {
            iM = 30;
        }
        $el.find(wEl + ' > ' + rEl).css("maxWidth", iW + "px");
        $el.parent().addClass('geodir_flex-container');
        $el.addClass('geodir_flexslider geodir-slider geodir-carousel');
        $el.attr({
            'data-slideshow': $el.data('ride') == 'carousel' ? 1 : 0,
            'data-controlnav': parseInt($el.data('with-controls')),
            'data-directionnav': parseInt($el.data('with-indicators')),
            'data-slideshow-speed': parseInt($el.data('interval')),
            'data-limit_show': pitems,
            'data-item-width': iW,
            'data-item-margin': iM,
            'data-selector': (isElementor ? '.geodir-slides > .elementor-post' : '')
        });
        $el.before('<div class="geodir_flex-loader"><i class="fas fa-sync fa-spin" aria-hidden="true"></i></div>');
        $el.find(wEl).addClass('geodir-slides');
        geodir_init_slider($el.prop('id'));
    }
}

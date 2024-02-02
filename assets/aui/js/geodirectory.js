jQuery(document).ready(function($) {
	if (typeof geodir_params === 'undefined') {
		geodir_params = [];
	}
	geodir_params.loader = null;
	geodir_params.addPopup = null;

	// Open a lightbox for embeded items
	jQuery('.geodir-lightbox-image, .geodir-lightbox-iframe').off('click').on("click",function(ele) {
		geodir_lightbox_embed(this,ele);
	});

	// add trigger for carousel multiple items
	jQuery( window ).on( "aui_carousel_multiple", function() {
		// Open a lightbox for embeded items
		jQuery('.geodir-lightbox-image, .geodir-lightbox-iframe').off('click').on("click",function(ele) {
			geodir_lightbox_embed(this,ele);
		});
	});

	// Maybe ajax load the next slide
	gd_init_carousel_ajax();

	jQuery('.nav-tabs,.nav-pills').on('shown.bs.tab', 'a', function (e) {
		if (this.hash && jQuery(this.hash).length) {
			if (jQuery(this.hash).find('.geodir-map-wrap').length) {
				window.dispatchEvent(new Event('resize')); // OSM do not load properly when rendered in hidden tab.
			}
		}
	});

	// fix tabs url hash on click and load
	jQuery(function(){
		var hash = window.location.hash;
		hash && jQuery('ul.nav a[href="' + hash + '"]').tab('show');

		jQuery('.nav-tabs a').on("click",function (e) {
			var $greedyLink = '', greedyHash = '';
			if(jQuery(this).closest('.greedy').length && jQuery(this).hasClass('greedy-nav-link')){
				if (!jQuery(this).closest(".greedy").find(".greedy-links .nav-link.active").length) {
					$greedyLink = jQuery(this).closest(".greedy").find(".greedy-links .nav-link:first");
					$greedyLink.tab('show');
					if ($greedyLink.attr('aria-controls')) {
						greedyHash = $greedyLink.attr('aria-controls');
					}
				} else {
					$greedyLink = jQuery(this).closest(".greedy").find(".greedy-links .nav-link.active");
				}
			} else {
				jQuery(this).tab('show');
			}
			var scrollmem = jQuery('body').scrollTop() || jQuery('html').scrollTop();
			if($greedyLink){
				if (greedyHash) {
					window.location.hash = greedyHash;
				}
			} else {
				window.location.hash = this.hash;
			}
			jQuery('html,body').scrollTop(scrollmem);
		});
	});

    $(document).on("geodir.init-posts-carousel", function(ev, params) {
        $cInner = $(params.element);
        if (params.slides && parseInt(params.slides) > 1 && !$cInner.closest('.bs-carousel').length) {
            $carousel = $cInner.closest('.carousel');
            $carousel.addClass('bs-carousel');
        }
        cId = "bs-carousel-" + params.index;
        $carousel.after('<div class="bs-carousel-wrapper position-relative"><div class="bs-carousel-outer p-0"><div id="' + cId + '"  class="bs-carousel-viewport overflow-hidden"></div></div></div>');
        $("#" + $carousel.attr('id')).appendTo("#" + cId);

        bs_carousel_clone_slides();

        if ($carousel.find('.carousel-control-next').length && $carousel.find('.carousel-control-prev').length) {
            mLeft = parseFloat($carousel.css('margin-left'));
            mRight = parseFloat($carousel.css('margin-right'));
            pLeft = (mLeft / 2) - $carousel.find('.carousel-control-prev').width();
            pRight = (mRight / 2) - $carousel.find('.carousel-control-next').width();

            if (pLeft && pRight) {
                $carousel.find('.carousel-control-prev').css({
                    'left': (pLeft * -1) + 'px'
                });
                $carousel.find('.carousel-control-next').css({
                    'right': (pRight * -1) + 'px'
                });
            }
        }
    });

    // Listings carousel
    $('.geodir-posts-carousel').each(function(index) {
        geodir_init_listings_carousel(this, index);
    });

    bs_carousel_handle_events();

    $(document).on('elementor/popup/show', (e, id, ins) => {
        if ($('.elementor-popup-modal .geodir-lazy-load').length) {
            geodir_init_lazy_load($);
        }
    });

    /* Scroll to reviews/comment on detail page */
    if ($('#gd-tabs #reviews').length && window.location.hash) {
        var lHash = window.location.hash, $sEl = '';
        if (lHash.substring(0, 9) == '#comment-' || lHash.substring(0, 8) == '#reviews') {
            if ($('#gd-tabs #reviews').find(lHash).length) {
                $sEl = $('#gd-tabs #reviews').find(lHash);
            } else {
                $sEl = $('#gd-tabs #reviews');
            }
        }
        if ($sEl) {
            if (!$('#gd-tabs #reviews').is(':visible')) {
                $('#gd-tabs [href="#reviews"]').trigger('click');
            }
            setTimeout(function() {
                $('html,body').animate({
                    scrollTop: $sEl.offset().top
                }, 'slow');
            }, 200);
        }
    }
    /* Open reviews tab on post rating click on single page */
    $('.geodir-page-single').find('.geodir-trigger-reviews,.gd-list-rating-link').on('click', function(e) {
        if ($('#gd-tabs #reviews').length) {
            if (!$('#gd-tabs #reviews').is(':visible')) {
                $('#gd-single-tabs [href="#reviews"]').trigger('click');
            }
            setTimeout(function() {
                $('html,body').animate({
                    scrollTop: $('#gd-tabs #reviews').offset().top
                }, 'slow');
            }, 200);
        }
    });
});

/**
 * Set the carousel to ajax load the next slide.
 */
function gd_init_carousel_ajax(){
	jQuery('.carousel').on('slide.bs.carousel', function (el) {
		jQuery(this).find('iframe').attr('src', '');
		geodir_ajax_load_slider(el.relatedTarget);
	});
}

/**
 * Open a lightbox when an embed item is clicked.
 */
function geodir_lightbox_embed($link,ele){
	ele.preventDefault();

	var bs5_prefix = jQuery('body').hasClass('aui_bs5') ? 'bs-' : '';

	// remove it first
	jQuery('.geodir-carousel-modal').remove();

	var $modal = '<div class="modal fade geodir-carousel-modal bsui" tabindex="-1" role="dialog" aria-labelledby="uwp-profile-modal-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-xl mw-100"><div class="modal-content bg-transparent border-0 shadow-none"><div class="modal-header"><h5 class="modal-title" id="uwp-profile-modal-title"></h5></div><div class="modal-body text-center"><i class="fas fa-circle-notch fa-spin fa-3x"></i></div></div></div></div>';
	jQuery('body').append($modal);

	jQuery('.geodir-carousel-modal').on('shown.bs.modal', function (e) {
		jQuery('.geodir-carousel-modal .carousel-item.active').find('iframe').each(function () {
			// fix the src
			if(real_src = jQuery(this).attr("data-src")){
				if(!jQuery(this).attr("srcset"))  jQuery(this).attr("src",real_src);
			}
		});
	});

	jQuery('.geodir-carousel-modal').modal({
		//backdrop: 'static'
	});
	jQuery('.geodir-carousel-modal').on('hidden.bs.modal', function (e) {
		jQuery(".geodir-carousel-modal iframe").attr('src', '');
	});

	$container = jQuery($link).closest('.geodir-images');

	$clicked_href = jQuery($link).attr('href');
	$images = [];
	$container.find('.geodir-lightbox-iframe, .geodir-lightbox-image').each(function() {
		var a = this;
		var href = jQuery(a).attr('href');
		if (href) {
			$images.push(href);
		}
	});

	if( $images.length ){
		var $carousel = '<div id="geodir-embed-slider-modal" class="carousel slide" >';

		// indicators
		if($images.length > 1){
			$i = 0;
			$carousel  += '<ol class="carousel-indicators position-fixed">';
			$container.find('.geodir-lightbox-iframe, .geodir-lightbox-image').each(function() {
				$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
				$carousel  += '<li data-'+ bs5_prefix +'target="#geodir-embed-slider-modal" data-slide-to="'+$i+'" class="'+$active+'"></li>';
				$i++;
			});
			$carousel  += '</ol>';
		}

		// items
		$i = 0;
		$carousel  += '<div class="carousel-inner">';
		$container.find('.geodir-lightbox-image').each(function() {
			var a = this;

			$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
			$carousel  += '<div class="carousel-item '+ $active+'"><div>';

			// image
			var css_height = window.innerWidth > window.innerHeight ? '90vh' : 'auto';
			var img = jQuery(a).find('img').clone().removeClass().addClass('mx-auto d-block w-auto mw-100 rounded').css('height',css_height).get(0).outerHTML;
			$carousel  += img;
			// captions
			if(jQuery(a).parent().find('.carousel-caption').length ){
				$carousel  += jQuery(a).parent().find('.carousel-caption').clone().removeClass('sr-only visually-hidden').get(0).outerHTML;
			}
			$carousel  += '</div></div>';
			$i++;
		});
		$container.find('.geodir-lightbox-iframe').each(function() {
			var a = this;

			$active = $clicked_href == jQuery(this).attr('href') ? 'active' : '';
			$carousel  += '<div class="carousel-item '+ $active+'"><div class="modal-xl mx-auto embed-responsive embed-responsive-16by9 ratio ratio-16x9">';

			// iframe
			var css_height = window.innerWidth > window.innerHeight ? '95vh' : 'auto';
			var url = jQuery(a).attr('href');
			var iframe = '<iframe class="embed-responsive-item" style="height:'+css_height +'" src="" data-src="'+url+'?rel=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1" id="video" allow="autoplay"></iframe>';
			var img = iframe ;//.css('height',css_height).get(0).outerHTML;
			$carousel  += img;

			$carousel  += '</div></div>';
			$i++;
		});
		$carousel  += '</div>';
		// next/prev indicators
		if($images.length > 1) {
			$carousel += '<a class="carousel-control-prev" href="#geodir-embed-slider-modal" role="button" data-slide="prev">';
			$carousel += '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
			$carousel += ' <a class="carousel-control-next" href="#geodir-embed-slider-modal" role="button" data-slide="next">';
			$carousel += '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
			$carousel += '</a>';
		}
		$carousel  += '</div>';

		var $close = '<button type="button" class="close text-white text-right text-end position-fixed" style="font-size: 2.5em;right: 20px;top: 10px; z-index: 1055;" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';

		jQuery('.geodir-carousel-modal .modal-content').html($carousel).prepend($close);

		// enable ajax load
		gd_init_carousel_ajax();
	}

}

/**
 ################################## old stuff ###################################
 */
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
jQuery.fn.gdunveil = function(threshold, callback,extra1) {

	var $w = jQuery(window),
		th = threshold || 0,
		retina = window.devicePixelRatio > 1,
		attrib = retina? "data-src-retina" : "data-src",
		images = this,
		loaded;

	if(extra1){
		var $e1 = jQuery(extra1),
			th = threshold || 0,
			retina = window.devicePixelRatio > 1,
			attrib = retina? "data-src-retina" : "data-src",
			images = this,
			loaded;
	}

	this.one("gdunveil", function() {
		var source = this.getAttribute(attrib);
		var srcset = this.getAttribute("data-srcset");
		source = source || this.getAttribute("data-src");
		if (source) {
			// set the srcset from the data-srcset
			if(srcset){this.setAttribute("srcset", srcset );}
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
	if(extra1){
		$e1.on("scroll.gdunveil resize.gdunveil lookup.gdunveil", gdunveil);
	}

	gdunveil();

	return this;

};

function geodir_init_lazy_load(gdQuery){
	if (!gdQuery) {
		gdQuery = jQuery;
	}

	// load for GD images
	var _opacity = 1;
	if ('objectFit' in document.documentElement.style === false) {
		_opacity = 0;
	}
	gdQuery(".geodir-lazy-load").gdunveil(100,function() {this.style.opacity = _opacity;},'#geodir_content, .dialog-lightbox-message');

	// fire when the image tab is clicked on details page
	jQuery('#gd-tabs').on("click",function() {
		setTimeout(function(){jQuery(window).trigger("lookup"); }, 100);
	});

	// fire after document load, just incase
	jQuery(document).ready(function() {
		setTimeout(function(){jQuery(window).trigger("lookup"); }, 100);
	});
}

//Pollyfill Object Fit in browsers that don't support it
function geodir_object_fit_fix( _img ) {

	//Image, its url and its parent li
	var _li = jQuery( _img ).closest( 'li' ),
		_url = jQuery( _img ).data('src')

	//Abort if url is unset
	if (!_url) {
		return;
	}

	//Hide the image and use it as the parent's bg
	jQuery( _img ).css({
		opacity: 0
	})
	_li.css({
		backgroundImage: 'url(' + _url + ')',
		backgroundSize: 'cover',
		borderRadius: '4px',
		backgroundPosition: 'center center',
	})
}

function geodir_load_badge_class(){
	jQuery('.gd-badge-meta .gd-badge').each(function(){
		var badge = jQuery(this).data('badge');
		var badge_condition = jQuery(this).data('badge-condition');
		if (badge && jQuery(this).closest('.post-' + jQuery(this).data('id')).length) {
			badge_class = 'geodir-badge-' + badge; // name
			badge_class += ' geodir-badge-' + badge + '-'+ badge_condition; // name and condition
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
			function(){
				$('.geodir-image-container ul.geodir-images li img').each( function(){
					geodir_object_fit_fix( this )
					$( this ).on( 'gdlazyLoaded', geodir_object_fit_fix)
				} )
			}
		);
	}

	if ($('.gd-bh-show-field').length) {
		setInterval(function(e) {
			geodir_refresh_business_hours();
		}, 60000);
		geodir_refresh_business_hours();
	} else {
		geodir_refresh_business_hours_today();
	}
	$('body').on('geodir_map_infowindow_open', function(e, data) {
		/* Render business hours */
		if (data.content && ($(data.content).find('.gd-bh-show-field').length || $(data.content).find('.gd-bh-stoday').length)) {
			geodir_refresh_business_hours();
		}
		geodir_init_lazy_load();
		geodir_init_flexslider();
		geodir_load_badge_class();
		jQuery(window).trigger("aui_carousel_multiple");
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
	if(jQuery('.geodir-comments-area').length && !jQuery('#reviews').length){
		jQuery('.geodir-comments-area').prepend('<span id="reviews"></span>');
	}

	// Report post submit
	$(document).on('submit', 'form.geodir-report-post-form', function(e) {
		e.preventDefault();
		geodir_report_post(this);
		return false;
	});
});



/* Placeholders.js v3.0.2  fixes placeholder support for older browsers */
(function (t) {
	"use strict";
	function e(t, e, r) {
		return t.addEventListener ? t.addEventListener(e, r, !1) : t.attachEvent ? t.attachEvent("on" + e, r) : void 0
	}

	function r(t, e) {
		var r, n;
		for (r = 0, n = t.length; n > r; r++)if (t[r] === e)return !0;
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

	t.Placeholders = {Utils: {addEventListener: e, inArray: r, moveCaret: n, changeType: a}}
})(this), function (t) {
	"use strict";
	function e() {
	}

	function r() {
		try {
			return document.activeElement
		} catch (t) {
		}
	}

	function n(t, e) {
		var r, n, a = !!e && t.value !== e, u = t.value === t.getAttribute(V);
		return (a || u) && "true" === t.getAttribute(D) ? (t.removeAttribute(D), t.value = t.value.replace(t.getAttribute(V), ""), t.className = t.className.replace(R, ""), n = t.getAttribute(F), parseInt(n, 10) >= 0 && (t.setAttribute("maxLength", n), t.removeAttribute(F)), r = t.getAttribute(P), r && (t.type = r), !0) : !1
	}

	function a(t) {
		var e, r, n = t.getAttribute(V);
		return "" === t.value && n ? (t.setAttribute(D, "true"), t.value = n, t.className += " " + I, r = t.getAttribute(F), r || (t.setAttribute(F, t.maxLength), t.removeAttribute("maxLength")), e = t.getAttribute(P), e ? t.type = "text" : "password" === t.type && M.changeType(t, "text") && t.setAttribute(P, "password"), !0) : !1
	}

	function u(t, e) {
		var r, n, a, u, i, l, o;
		if (t && t.getAttribute(V))e(t); else for (a = t ? t.getElementsByTagName("input") : b, u = t ? t.getElementsByTagName("textarea") : f, r = a ? a.length : 0, n = u ? u.length : 0, o = 0, l = r + n; l > o; o++)i = r > o ? a[o] : u[o - r], e(i)
	}

	function i(t) {
		u(t, n)
	}

	function l(t) {
		u(t, a)
	}

	function o(t) {
		return function () {
			m && t.value === t.getAttribute(V) && "true" === t.getAttribute(D) ? M.moveCaret(t, 0) : n(t)
		}
	}

	function c(t) {
		return function () {
			a(t)
		}
	}

	function s(t) {
		return function (e) {
			return A = t.value, "true" === t.getAttribute(D) && A === t.getAttribute(V) && M.inArray(C, e.keyCode) ? (e.preventDefault && e.preventDefault(), !1) : void 0
		}
	}

	function d(t) {
		return function () {
			n(t, A), "" === t.value && (t.blur(), M.moveCaret(t, 0))
		}
	}

	function g(t) {
		return function () {
			t === r() && t.value === t.getAttribute(V) && "true" === t.getAttribute(D) && M.moveCaret(t, 0)
		}
	}

	function v(t) {
		return function () {
			i(t)
		}
	}

	function p(t) {
		t.form && (T = t.form, "string" == typeof T && (T = document.getElementById(T)), T.getAttribute(U) || (M.addEventListener(T, "submit", v(T)), T.setAttribute(U, "true"))), M.addEventListener(t, "focus", o(t)), M.addEventListener(t, "blur", c(t)), m && (M.addEventListener(t, "keydown", s(t)), M.addEventListener(t, "keyup", d(t)), M.addEventListener(t, "click", g(t))), t.setAttribute(j, "true"), t.setAttribute(V, x), (m || t !== r()) && a(t)
	}

	var b, f, m, h, A, y, E, x, L, T, N, S, w, B = ["text", "search", "url", "tel", "email", "password", "number", "textarea"], C = [27, 33, 34, 35, 36, 37, 38, 39, 40, 8, 46], k = "#ccc", I = "placeholdersjs", R = RegExp("(?:^|\\s)" + I + "(?!\\S)"), V = "data-placeholder-value", D = "data-placeholder-active", P = "data-placeholder-type", U = "data-placeholder-submit", j = "data-placeholder-bound", q = "data-placeholder-focus", z = "data-placeholder-live", F = "data-placeholder-maxlength", G = document.createElement("input"), H = document.getElementsByTagName("head")[0], J = document.documentElement, K = t.Placeholders, M = K.Utils;
	if (K.nativeSupport = void 0 !== G.placeholder, !K.nativeSupport) {
		for (b = document.getElementsByTagName("input"), f = document.getElementsByTagName("textarea"), m = "false" === J.getAttribute(q), h = "false" !== J.getAttribute(z), y = document.createElement("style"), y.type = "text/css", E = document.createTextNode("." + I + " { color:" + k + "; }"), y.styleSheet ? y.styleSheet.cssText = E.nodeValue : y.appendChild(E), H.insertBefore(y, H.firstChild), w = 0, S = b.length + f.length; S > w; w++)N = b.length > w ? b[w] : f[w - b.length], x = N.attributes.placeholder, x && (x = x.nodeValue, x && M.inArray(B, N.type) && p(N));
		L = setInterval(function () {
			for (w = 0, S = b.length + f.length; S > w; w++)N = b.length > w ? b[w] : f[w - b.length], x = N.attributes.placeholder, x ? (x = x.nodeValue, x && M.inArray(B, N.type) && (N.getAttribute(j) || p(N), (x !== N.getAttribute(V) || "password" === N.type && !N.getAttribute(P)) && ("password" === N.type && !N.getAttribute(P) && M.changeType(N, "text") && N.setAttribute(P, "password"), N.value === N.getAttribute(V) && (N.value = x), N.setAttribute(V, x)))) : N.getAttribute(D) && (n(N), N.removeAttribute(V));
			h || clearInterval(L)
		}, 100)
	}
	M.addEventListener(t, "beforeunload", function () {
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

	gd_infowindow = (typeof google !== 'undefined' && typeof google.maps !== 'undefined') ? new google.maps.InfoWindow({maxWidth: 200}) : null;

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
	setTimeout(function(){gd_init_rating_input();}, 250);



});

// init any sliders
function geodir_init_flexslider(){

}


jQuery(window).on("load",function() {

	// Set times to time ago
	if(jQuery('.gd-timeago').length){
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
		jQuery(this).find('i').toggleClass("d-none");

	});


	jQuery(".trigger_sticky").on("click",function() {
		var tigger_sticky = jQuery(this);

		jQuery('body').toggleClass('stickymap_hide');

		if (tigger_sticky.hasClass("triggeroff_sticky")) {
			tigger_sticky.removeClass('triggeroff_sticky');
			tigger_sticky.addClass('triggeron_sticky');
			if(geodir_is_localstorage()){localStorage.setItem("gd_sticky_map",'shide');}
		} else {
			tigger_sticky.removeClass('triggeron_sticky');
			tigger_sticky.addClass('triggeroff_sticky');
			if(geodir_is_localstorage()){localStorage.setItem("gd_sticky_map",'sshow');}
		}

		window.dispatchEvent(new Event('resize')); // OSM does not work with the jQuery trigger so we do it old skool.

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
jQuery(window).on("load",function () {
	if (jQuery(".map_background").length == 0) {
		jQuery('.geodir-pinpoint').hide();
	} else {
		jQuery('.geodir-pinpoint').show();
	}
});

//-------count post according to term--
function geodir_get_post_term(el) {
	limit = jQuery(el).data('limit');
	term = jQuery(el).val();//data('term');
	var parent_only = parseInt(jQuery(el).data('parent')) > 0 ? 1 : 0;
	jQuery(el).parent().parent().find('.geodir-popular-cat-list').html('<i class="fas fa-cog fa-spin" aria-hidden="true"></i>');
	jQuery(el).parent().parent().parent().find('.geodir-cat-list-more').hide();
	jQuery.post(geodir_params.ajax_url + '?action=geodir_ajax_action', {
		ajax_action: "geodir_get_term_list",
		term: term,
		limit: limit,
		parent_only: parent_only
	}).done(function (data) {
		if (jQuery.trim(data) != '') {
			jQuery(el).parent().parent().find('.geodir-popular-cat-list').hide().html(data).fadeIn('slow');
			if (jQuery(el).parent().parent().find('.geodir-popular-cat-list li').length > limit) {
				jQuery(el).parent().parent().parent().find('.geodir-cat-list-more').fadeIn('slow');
			}
		}
	});
}

/* we recalc the stars because some browsers can't do subpixle percents, we should be able to remove this in a few years. */
jQuery(window).on("load",function() {
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
	var $container = jQuery(el).closest('.geodir-search-container');
	var $adv_show = $container.attr('data-show-adv');
	var $form = jQuery(el).closest('form');
	var data = {
		action: 'geodir_search_form',
		stype: stype,
		adv: $adv_show
	};
	if (jQuery('.geodir-keep-args', $container).length && jQuery('.geodir-keep-args', $container).text()) {
		data.keepArgs = jQuery('.geodir-keep-args', $container).text();
	}

	if ($form.data('show') == 'main' && jQuery('form.geodir-search-show-advanced').length) {
		data.advanced = true;
	}

	/* Keep location when CPT changed */
	if (jQuery('input[name="snear"]', $form).length && jQuery('input[name="snear"]', $form).is(':visible')) {
		var lname = jQuery('input.geodir-location-search-type', $form).prop('name'),
			lval = jQuery('input.geodir-location-search-type', $form).val(),
			_gdSLoc = {};
		if (lval && (lname == 'country' || lname == 'region' || lname == 'city' || lname == 'neighbourhood')) {
			_gdSLoc[lname] = lval;
		} else {
			if (jQuery('input.sgeo_lat', $form).val() && jQuery('input.sgeo_lon', $form).val()) {
				data['sgeo_lat'] = jQuery('input.sgeo_lat', $form).val();
				data['sgeo_lon'] = jQuery('input.sgeo_lon', $form).val();
				_gdSLoc['sgeo_lat'] = data['sgeo_lat'];
				_gdSLoc['sgeo_lon'] = data['sgeo_lon'];
			}
			if (lname == 'near' && lval == 'me') {
				_gdSLoc[lname] = lval;
			} else {
				_gdSLoc = {};
				data['snear'] = jQuery('input[name="snear"]', $form).val();
			}
		}
		if (_gdSLoc && Object.keys(_gdSLoc).length) {
			data['_gd_keep_loc'] = _gdSLoc;
		}
	}

	jQuery.ajax({
		url: geodir_params.ajax_url,
		type: 'POST',
		dataType: 'html',
		data: data,
		beforeSend: function() {
			geodir_search_wait(1);
		},
		success: function(res, textStatus, xhr) {
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
			$container.html(res);
			if (data.advanced && $container.find('.geodir-search-has-advanced').length) {
				jQuery('form.geodir-search-show-advanced').html($container.find('.geodir-search-has-advanced').html());
				jQuery('form.geodir-search-show-advanced').each(function(){
					if (jQuery(this).find('.geodir-filter-cat').length) {
						jQuery(this).closest('.geodir-search-container').removeClass('d-none');
					} else {
						jQuery(this).closest('.geodir-search-container').addClass('d-none');
						jQuery(this).find('.geodir_submit_search').closest('.gd-search-field-search').hide();
					}
				});
				$container.find('.geodir-search-has-advanced').remove();
			}

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
			jQuery("body").trigger("geodir_setup_search_form", $container.find('form[name="geodir-listing-search"]'));

			geodir_search_wait(0);
		},
		error: function(xhr, textStatus, errorThrown) {
			console.log(textStatus);geodir_search_wait(0);
		}
	});
}

function geodir_setup_search_form(){
	//  new seach form change
	if (jQuery('.search_by_post').val()) {
		gd_s_post_type = jQuery('.search_by_post').val();
	} else {
		gd_s_post_type = "gd_place";
	}

	setTimeout(function(){
		jQuery('.search_by_post').on("change", function() {
			gd_s_post_type = jQuery(this).val();
			window.gdAsCptChanged = gd_s_post_type;
			geodir_load_search_form(gd_s_post_type, this);
		});
	}, 100);

	if (typeof aui_init === "function") {
		aui_init();
	}
}

gdSearchDoing = 0;
var gdNearChanged = 0;
gd_search_icon ='';
function geodir_search_wait(on){
	waitTime = 300;

	if(on){
		if(gdSearchDoing){return;}
		gdSearchDoing = 1;
		jQuery('.geodir_submit_search').addClass('gd-wait-btnsearch').prop('disabled', true);
		jQuery('.showFilters').prop('disabled', true);
		searchPos = 1;
		gd_search_icon = jQuery('.geodir_submit_search').html();
		function geodir_search_wait_animate() {
			if(!searchPos){return;}
			if(searchPos==1){jQuery('input[type="button"].geodir_submit_search').val('  ');searchPos=2;window.setTimeout(geodir_search_wait_animate, waitTime );return;}
			if(searchPos==2){jQuery('input[type="button"].geodir_submit_search').val('  ');searchPos=3;window.setTimeout(geodir_search_wait_animate, waitTime );return;}
			if(searchPos==3){jQuery('input[type="button"].geodir_submit_search').val('  ');searchPos=1;window.setTimeout(geodir_search_wait_animate, waitTime );return;}
		}
		geodir_search_wait_animate();
		jQuery('button.geodir_submit_search').html('<i class="fas fa-hourglass fa-spin" aria-hidden="true"></i>');
	} else {
		searchPos = 0;
		gdSearchDoing = 0;
		jQuery('.geodir_submit_search').removeClass('gd-wait-btnsearch').prop('disabled', false);
		jQuery('.showFilters').prop('disabled', false);
		gdsText = jQuery('input[type="button"].geodir_submit_search').data('title');
		if (window.gdAsBtnTitle) {
			gdsText = window.gdAsBtnTitle;
		}
		jQuery('input[type="button"].geodir_submit_search').val(gdsText);
		if (window.gdAsBtnText) {
			gd_search_icon = window.gdAsBtnText;
		}
		jQuery('button.geodir_submit_search').html(gd_search_icon);
	}
}

function geodir_click_search($this) {
	//we delay this so other functions have a change to change setting before search
	setTimeout(function() {
		jQuery($this).closest('.geodir-search').find('.geodir_submit_search').trigger("click");
	}, 100);
}

function gd_fav_save(post_id) {
    var ajax_action;
    if (jQuery('.favorite_property_' + post_id + ' .geodir-act-fav').hasClass('geodir-removetofav-icon')) {
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
        beforeSend: function(xhr, obj) {
            jQuery('.favorite_property_' + post_id + ' .geodir-act-fav').css('opacity', '0.6');
        },
        success: function(data) {
            jQuery('.favorite_property_' + post_id + ' .geodir-act-fav').css('opacity', 1);
            if (data.success) {
                var action_text = (data.data && data.data.action_text) ? data.data.action_text : '';
                jQuery('.favorite_property_' + post_id).each(function(index) {
                    if (jQuery(this).find('.geodir-act-fav').length) {
                        var $el = jQuery(this).find('.geodir-act-fav');
                        $icon_value = $el.data("icon");
                        $icon = $icon_value ? $icon_value : geodir_params.icon_fav;
						var textColor = $el.data("text-color");
						var textStyle = textColor ? 'color:' + textColor + ';' : '';
						textStyle = textStyle ? ' style="' + textStyle + '"' : '';

                        if (ajax_action == 'remove') {
                            $color_value = $el.data("color-off");
                            $text_classes = $el.find('.geodir-fav-text').attr('class');
                            $style = $color_value ? "style='color:" + $color_value + "'" : "";
                            $el.tooltip('dispose')
                                .removeClass('geodir-removetofav-icon')
                                .addClass('geodir-addtofav-icon')
                                .attr("title", geodir_params.text_add_fav)
                                .html('<i ' + $style + ' class="' + $icon + '"></i> <span class="' + $text_classes + '"' + textStyle + '>' + ' ' + (action_text ? action_text : geodir_params.text_fav) + '</span>').tooltip('enable');
                        } else {
                            $color_value = $el.data("color-on");
                            $text_classes = $el.find('.geodir-fav-text').attr('class');
                            $style = $color_value ? "style='color:" + $color_value + "'" : "";
                            $el.tooltip('dispose')
                                .removeClass('geodir-addtofav-icon')
                                .addClass('geodir-removetofav-icon')
                                .attr("title", geodir_params.text_remove_fav)
                                .html('<i ' + $style + ' class="' + $icon + '"></i> <span class="' + $text_classes + '"' + textStyle + '>' + ' ' + (action_text ? action_text : geodir_params.text_unfav) + '</span>').tooltip('enable');
                        }
                    }
                });
            } else {
                alert(geodir_params.loading_listing_error_favorite);
            }
        }
    });
    return false;
}

function geodir_refresh_business_hours() {
	if (jQuery('.gd-bh-show-field').length) {
		jQuery('.gd-bh-show-field').each(function() {
			geodir_refresh_business_hour(jQuery(this));
		});
	}

	if(jQuery('.gd-bh-stoday').length) {
		geodir_refresh_business_hours_today();
	}
}

function geodir_refresh_business_hour($this) {
	var d, $d, hours, day, mins, time, hasOpen = false, hasPrevOpen = false, hasClosed = false, isOpen, o, c, nd, label, times = [], opens = [], prevtimes = [], prevopens = [];
	d = new Date(), utc = d.getTime() + (d.getTimezoneOffset() * 60000), d = new Date(utc + (parseInt(jQuery('.gd-bh-expand-range', $this).data('offsetsec')) * 1000));
	date = d.getFullYear() + '-' + (("0" + (d.getMonth()+1)).slice(-2)) + '-' + (("0" + (d.getDate())).slice(-2)) + 'T' + (("0" + (d.getHours())).slice(-2)) + ':' + (("0" + (d.getMinutes())).slice(-2)) + ':' + (("0" + (d.getSeconds())).slice(-2));
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
		$this.find('div').removeClass('gd-bh-open gd-bh-close gd-bh-days-open gd-bh-days-close gd-bh-slot-open gd-bh-slot-close gd-bh-days-today text-primary');
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
			$this.find('div,span').removeClass('gd-bh-open gd-bh-close gd-bh-days-open gd-bh-days-close gd-bh-slot-open gd-bh-slot-close gd-bh-days-today text-success text-danger text-primary');
		}
		$d.addClass('gd-bh-days-today');
		$d.find('.gd-bh-days-d').addClass('text-primary');
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
				if ((hasPrevOpen && hasOpen) || ! hasPrevOpen) {
					times.push(dayname + jQuery(this).find('.gd-bh-slot-r').html());
				}
			});
		} else {
			hasClosed = true;
		}
		if (hasOpen) {
			times = opens;
			$d.addClass('gd-bh-days-open').find('.gd-bh-slots').addClass('text-success');
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
		$this.addClass('gd-bh-open').find('.geodir-i-business_hours').addClass('text-success');
	} else {
		label = hasClosed ? geodir_params.txt_closed_today : geodir_params.txt_closed_now;
		$this.addClass('gd-bh-close').find('.geodir-i-business_hours').addClass('text-danger');
	}
	jQuery('.geodir-i-biz-hours font', $this).html(label);
}

function geodir_refresh_business_hours_today() {
	var d = new Date(),ids = new Array(),iId, iDay, sDate;
	jQuery('.gd-bh-stoday').each(function() {
		if (!jQuery(this).hasClass('gd-bh-done')) {
			iId = parseInt(jQuery(this).data('bhs-id'));
			iDay = parseInt(jQuery(this).data('bhs-day'));
			if (iId > 0 && iDay > 0 && iDay != parseInt(d.getDate())) {
				ids.push(iId);
			}
			jQuery(this).addClass('gd-bh-done');
		}
	});

	if (ids.length) {
		const bhUnique = (v, i, s) => { return s.indexOf(v) === i; }
		sDate = d.getFullYear() + '-' + (("0" + (d.getMonth() + 1)).slice(-2)) + '-' + (("0" + (d.getDate())).slice(-2)) + ' ' + (("0" + (d.getHours())).slice(-2)) + ':' + (("0" + (d.getMinutes())).slice(-2)) + ':00';

		jQuery.ajax({
			url: geodir_params.gd_ajax_url,
			type: 'POST',
			data: {
				action: 'geodir_business_hours_post_meta',
				post_id: ids.filter(bhUnique),
				date: sDate,
				security: geodir_params.basic_nonce
			},
			dataType: 'json',
			beforeSend: function(xhr, obj) {}
		}).done(function(res, textStatus, jqXHR) {
			if (typeof res == 'object' && res.data.slots && typeof res.data.slots == 'object') {
				jQuery.each(res.data.slots, function(p, r) {
					if (jQuery('.gd-bh-stoday[data-bhs-id="' + p + '"]').length && r.slot) {
						jQuery('.gd-bh-stoday[data-bhs-id="' + p + '"]').each(function() {
							jQuery(this).closest('.gd-bh-day-hours').removeClass('gd-bh-open-today gd-bh-days-closed').addClass(r.css_class);
							jQuery(this).replaceWith(r.slot);
						});
					}
				});
			}
		}).always(function(data, textStatus, jqXHR) {});
	}
}

/**
 * Our own switchClass so we don't have to add jQuery UI.
 */
(function($){
	$.fn.GDswitchClass = function(remove, add){
		var style = {
			'transition-property'        : 'all',
			'transition-duration'        : '0.6s',
			'transition-timing-function' : 'ease-out'
		};

		return this.each(function(){
			$(this).css(style).removeClass(remove).addClass(add)
		});
	};
}(jQuery));


function init_read_more(){
	var $el, $ps, $up, totalHeight;

	jQuery('.geodir-category-list-view  .geodir-post-meta-container .geodir-field-post_content').each(function() {
		jQuery(this).addClass('gd-read-more-wrap').wrapInner( "<p></p>").append('<p class="gd-read-more-fade"><a href="#" class="gd-read-more-button">'+geodir_params.txt_read_more+'</a></p>');
	});

	// Make the read more visable if the text needs it
	jQuery('.gd-read-more-wrap').each(function() {
		var height = jQuery( this ).height();
		var maxHeight = parseInt(jQuery( this ).css('max-height'),10);
		if(height >= maxHeight){
			jQuery( this ).find('.gd-read-more-fade').show();
		}
	});


	jQuery(".gd-read-more-wrap .gd-read-more-button").on("click",function() {

		totalHeight = 0;

		$el = jQuery(this);
		$p  = $el.parent();
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

function gd_delete_post($post_id){
	var message = geodir_params.my_place_listing_del;
	if (confirm(message)) {
		jQuery('.post-' + $post_id + '[data-post-id="' + $post_id + '"] .gd_user_action.delete_link').addClass('opacity-2');
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
				if(data.success){
					aui_modal("",'<div class="gd-notification gd-success"><i class="fas fa-check-circle"></i> '+ data.data.message +'</div>','',true);
					jQuery('.post-' + $post_id + '[data-post-id="' + $post_id + '"]').fadeOut();
					if (data.data.redirect_to && jQuery('body').hasClass('single') && jQuery('body').hasClass('postid-' + $post_id)) {
						setTimeout(function() {
							window.location = data.data.redirect_to;
						}, 3000);
					}
				}else{
					aui_modal("",'<div class="gd-notification gd-error"><i class="fas fa-exclamation-circle"></i> '+ data.data.message +'</div>','',true);
				}
				jQuery('.post-' + $post_id + '[data-post-id="' + $post_id + '"] .gd_user_action.delete_link').removeClass('opacity-2');
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
						aui_modal("", '<div class="gd-notification gd-success"><i class="fas fa-check-circle"></i> ' + data.data.message + '</div>', '', true);
					} else {
						aui_modal("", '<div class="gd-notification gd-error"><i class="fas fa-exclamation-circle"></i> ' + data.data.message + '</div>', '', true);
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
 * Loads the ninja lightbox.
 *
 * @param $action
 * @param $nonce
 * @param $post_id
 * @param $extra
 */
function gd_ninja_lightbox($action,$nonce,$post_id,$extra){
	if($action){
		if(!$nonce || $nonce==''){$nonce = geodir_params.basic_nonce;}
		$content = "<div class='geodir-ajax-content ''>Loading content</div>";
		$lightbox = '';

		if($action=='geodir_ninja_forms'){
			// clear all form data so we can reload the same form via ajax
			delete form;
			delete formDisplay;
			delete nfForms;
		}

		url = geodir_params.ajax_url+"?action="+$action+"&security="+$nonce+"&p="+$post_id+"&extra="+$extra;
		aui_modal(' ','<div class="modal-loading text-center mt-5"><div class="spinner-border" role="status"></div></div><iframe onload="jQuery(\'.modal-loading\').hide();jQuery(this).css(\'height\',this.contentWindow.document.body.offsetHeight + 50 + \'px\');" id="gd-ninja-iframe" src="'+url+'" width="100%" height="0" frameborder="0" allowtransparency="true"></iframe>','',true,'','modal-xl');
		return;
	}
}

/**
 * Change the texts for the reply/review comments section.
 */
function gd_init_comment_reply_link(){

	jQuery(".geodir-comments-area .comment-reply-link").on("click",function(event){

		setTimeout(function(){

			jQuery('#reply-title').contents().filter(function() {
				return this.nodeType == 3
			}).each(function(){
				this.textContent = this.textContent.replace(geodir_params.txt_leave_a_review,geodir_params.txt_leave_a_reply);
			});

			// add padding to the top of the form
			jQuery('#respond').addClass('mt-3');

			// change the title
			$reply_text = jQuery('.gd-comment-review-title').data('reply-text');
			jQuery('.gd-comment-review-title').text($reply_text);

			// replace placeholder text
			$placeholder = jQuery('#respond #comment').attr('placeholder');
			jQuery('#respond #comment').data('placeholder',$placeholder).attr('placeholder',geodir_params.txt_reply_text);

			// replace button value
			$btn_text = jQuery('#respond input.submit').val();
			jQuery('#respond input.submit').data('value',$btn_text).val(geodir_params.txt_post_reply);

			jQuery('#respond .gd-rating-input-group, #respond .geodir-add-files').hide();
		}, 10);
	});

	jQuery("#cancel-comment-reply-link").on("click",function(event){

		setTimeout(function(){

			jQuery('#reply-title').contents().filter(function() {
				return this.nodeType == 3
			}).each(function(){
				this.textContent = this.textContent.replace(geodir_params.txt_leave_a_reply,geodir_params.txt_leave_a_review);
			});

			// change the title
			$review_text = jQuery('.gd-comment-review-title').data('review-text');
			jQuery('.gd-comment-review-title').text($review_text);

			// replace placeholder text
			$placeholder = jQuery('#respond #comment').data('placeholder');
			jQuery('#respond #comment').attr('placeholder',$placeholder);

			// replace button value
			$btn_text = jQuery('#respond input.submit').data('value');
			jQuery('#respond input.submit').val($btn_text);

			jQuery('#respond .gd-rating-input-group, #respond .geodir-add-files').show();
		}, 10);
	});
}

/**
 * Load a slider image via ajax.
 *
 * @param slide
 */
function geodir_ajax_load_slider(slide){
	jQuery(slide).find('img').each(function () {
		// fix the srcset
		if(real_srcset = jQuery(this).attr("data-srcset")){
			if(!jQuery(this).attr("srcset")) jQuery(this).attr("srcset",real_srcset);
		}
		// fix the src
		if(real_src = jQuery(this).attr("data-src")){
			if(!jQuery(this).attr("srcset"))  jQuery(this).attr("src",real_src);
		}
	});

	jQuery(slide).find('iframe').each(function () {
		// fix the src
		if(real_src = jQuery(this).attr("data-src")){
			if(!jQuery(this).attr("srcset"))  jQuery(this).attr("src",real_src);
		}
	});
}

/**
 * Init a slider by id.
 *
 * @param $id
 */
function geodir_init_slider($id){

	// chrome 53 introduced a bug, so we need to repaint the slider when shown.
	jQuery('.geodir-slides').addClass('flexslider-fix-rtl');

	jQuery("#"+$id+"_carousel").flexslider({
		animation: "slide",
		namespace: "geodir-",
		selector: ".geodir-slides > li",
		controlNav: !1,
		directionNav: !1,
		animationLoop: !1,
		slideshow: !1,
		itemWidth: 75,
		itemMargin: 5,
		asNavFor: "#"+$id,
		rtl: 1 == parseInt(geodir_params.is_rtl) ? !0 : !1
	}), jQuery("#"+$id).flexslider({


		// enable carousel if settings are right
		itemWidth: jQuery("#"+$id).attr("data-limit_show") ? 210 : "", // needed to be considered a carousel
		itemMargin: jQuery("#"+$id).attr("data-limit_show") ? 3 : "",
		minItems: jQuery("#"+$id).attr("data-limit_show") ? 1 : "",
		maxItems: jQuery("#"+$id).attr("data-limit_show") ? jQuery("#"+$id).attr("data-limit_show") : "",

		animation: jQuery("#"+$id).attr("data-animation")=='fade' ? "fade" : "slide",
		selector: ".geodir-slides > li",
		namespace: "geodir-",
		// controlNav: !0,
		controlNav: parseInt(jQuery("#"+$id).attr("data-controlnav")),
		directionNav: 1,
		prevText: '<i class="fas fa-angle-right"></i>',// we flip with CSS
		nextText: '<i class="fas fa-angle-right"></i>',
		animationLoop: !0,
		slideshow: parseInt(jQuery("#"+$id).attr("data-slideshow")),
		sync: "#"+$id+"_carousel",
		start: function(slider) {

			// chrome 53 introduced a bug, so we need to repaint the slider when shown.
			jQuery('.geodir-slides').removeClass('flexslider-fix-rtl');
			jQuery("#"+$id).removeClass('geodir-slider-loading');

			jQuery(".geodir_flex-loader").hide(), jQuery("#"+$id).css({
				visibility: "visible"
			}), jQuery("#"+$id+"_carousel").css({
				visibility: "visible"
			});


			// Ajax load the slides that are visible
			var $visible = slider.visible ? slider.visible : 1;

			// Load current slides
			var i = 0;
			for (; i < $visible ; ) {
				slide = slider.slides.eq( i );
				geodir_ajax_load_slider(slide);
				i++;
				// load next slide also
				slide_next = slider.slides.eq( i );
				geodir_ajax_load_slider( slide_next );
			}
		},
		before: function(slider){
			// Ajax load the slides that are visible
			var $visible = slider.visible ? slider.visible : 1;

			if(isNaN($visible)){ $visible = 1;}

			// Load current slides
			var i = slider.animatingTo * $visible - 1;
			var $visible_next = i + $visible + 1;


			for (; i < $visible_next ; ) {
				slide = slider.slides.eq( i );
				geodir_ajax_load_slider(slide);
				i++;
				// load next slide also
				slide_next = slider.slides.eq( i );
				geodir_ajax_load_slider( slide_next );
			}
		},
		rtl: 1 == parseInt(geodir_params.is_rtl) ? !0 : !1
	});

}

/**
 * Init the rating inputs.
 */
function gd_init_rating_input(){
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
 * Bounce the marker when the listing item is hovered over.
 */
function geodir_animate_markers(){
	if (typeof(animate_marker) == 'function') {
		var groupTab = jQuery(".geodir-category-list-view").children(".geodir-post");
		groupTab.hover(function () {
			animate_marker('listing_map_canvas', String(jQuery(this).data("post-id")));
		}, function () {
			stop_marker_animation('listing_map_canvas', String(jQuery(this).data("post-id")));
		});

		// maybe elementor animate
		if(jQuery('body.archive .elementor-widget-archive-posts').length){
			var ePosts = jQuery("body.archive .elementor-widget-archive-posts .elementor-posts").children(".elementor-post");
			ePosts.hover(function () {
				$post_id = jQuery(this).attr('class').match(/post-\d+/)[0].replace("post-","");
				animate_marker('listing_map_canvas', String($post_id));
			}, function () {
				$post_id = jQuery(this).attr('class').match(/post-\d+/)[0].replace("post-","");
				stop_marker_animation('listing_map_canvas', String($post_id));
			});
		}
	} else {
		window.animate_marker = function () {
		};
		window.stop_marker_animation = function () {
		};
	}
}

/**
 * Test if browser local storage is available.
 *
 * @returns {boolean}
 */
function geodir_is_localstorage(){
	var test = 'geodirectory';
	try {
		localStorage.setItem(test, test);
		localStorage.removeItem(test);
		return true;
	} catch(e) {
		return false;
	}
}

/**
 * Prevent onclick affecting parent elements.
 *
 * @param e
 */
function geodir_cancelBubble(e){
	var evt = e ? e:window.event;
	if (evt.stopPropagation)    evt.stopPropagation();
	if (evt.cancelBubble!=null) evt.cancelBubble = true;
}



/**
 * Get the user GPS position.
 *
 * @param $success The function to call on sucess.
 * @param $fail The function to all on fail.
 */
function gd_get_user_position($success,$fail){
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
		if(typeof fn === 'function') {
			fn(coords.latitude,coords.longitude);
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
	if(window.gd_user_position_success){
		$success = window.gd_user_position_success_callback;
	}else{
		$success = '';
	}
	gd_manually_set_user_position(msg,$success);
}

/**
 * Lets the user set their position manually on a map.
 * Not called direct.
 *
 * @param $msg
 */
function gd_manually_set_user_position($msg){

	var $prefix = "geodir_manual_location_";

	jQuery.post(geodir_params.ajax_url, {
		action: 'geodir_manual_map',
		trigger: $prefix+'_trigger'
		//trigger: $successFunction
	}, function(data) {
		if (data) {
			aui_modal("",data);

			// map center is off due to lightbox zoom effect so we resize to fix
			setTimeout(function(){
				jQuery('.aui-modal .geodir_map_container').css('width','90%').css('width','99.99999%');
				window.dispatchEvent(new Event('resize')); // OSM does not work with the jQuery trigger so we do it old kool.
			}, 500);

			jQuery( window ).off($prefix+'_trigger');
			jQuery( window ).on( $prefix+'_trigger', function (event,lat,lon)
			{
				if(lat && lon){
					var position ={};
					position.latitude = lat;
					position.longitude = lon;
					// window[$successFunction](position);
					var fn = window.gd_user_position_success_callback;
					if(typeof fn === 'function') {
						fn(lat,lon);
					}
					jQuery('.aui-modal').modal('hide');
				}
			});

			return false;
		}
	});
}

function gd_set_get_directions($lat,$lon){
	if(jQuery('.geodir-map-directions-wrap input[name="from"]').length){
		var $el = jQuery('.geodir-map-directions-wrap input[name="from"]:visible').length ? jQuery('.geodir-map-directions-wrap input[name="from"]:visible:first') : jQuery('.geodir-map-directions-wrap input[name="from"]:first');
		var $map = $el.closest('.geodir-map-wrap');
		jQuery('#gd-single-tabs a[href="#post_map"]').tab('show');
		setTimeout(function(){
			jQuery('html, body').animate({
				scrollTop: $map.offset().top-20
			}, 1000);
		}, 300);
		$el.val($lat+","+$lon);
		if($map.find('.leaflet-routing-geocoder').length) {
			jQuery('.leaflet-routing-geocoder:last input', $map).val($lat+","+$lon).trigger('focus');
		}
		jQuery('.geodir-map-directions-wrap button', $map).trigger('click');
	}
}

function geodir_widget_listings_pagination(id, params) {
	var $container, pagenum;
	$container = jQuery('#' + id);

	jQuery('.geodir-loop-paging-container', $container).each(function() {
		var $paging = jQuery(this);
		if (!$paging.hasClass('geodir-paging-setup')) {
			if (jQuery('.pagination .page-link', $paging).length) {
				jQuery('.pagination a.page-link', $paging).each(function() {
					href = jQuery(this).attr('href');
					hrefs = href.split("#");
					page = (hrefs.length > 1 && parseInt(hrefs[1]) > 0 ? parseInt(hrefs[1]) : (parseInt(jQuery(this).text()) > 0 ? parseInt(jQuery(this).text()) : 0));
					if (!page > 0) {
						var ePage = jQuery(this).closest('.pagination').find('[aria-current="page"]');
						if (!ePage.length) {
							ePage = jQuery(this).closest('.pagination').find('.page-link.current');
						}
						if (!ePage.length) {
							ePage = jQuery(this).closest('.pagination').find('.page-link.active');
						}
						var cpage = ePage.length ? parseInt(ePage.text()) : 0;
						if (cpage > 0) {
							if (jQuery(this).hasClass('next')) {
								page = cpage + 1;
							} else if (jQuery(this).hasClass('prev')) {
								page = cpage - 1;
							}
						}
					}
					if (!page > 0) {
						page = 1;
					}
					jQuery(this).attr('data-geodir-pagenum', page);
					jQuery(this).attr('href', 'javascript:void(0)');
				});
			}
			$paging.addClass('geodir-paging-setup');
		}
	});

	jQuery("a.page-link", $container).on("click", function(e) {
		pagenum = parseInt(jQuery(this).data('geodir-pagenum'));
		if (!pagenum > 0) {
			return;
		}
		$widget = $container.closest('.geodir-listings');
		$listings = jQuery('.geodir-widget-posts', $container);

		params['pageno'] = pagenum;

		$widget.addClass('geodir-listings-loading').find('.geodir-ajax-listings-loader').show();

		jQuery.ajax({
			type: "POST",
			url: geodir_params.ajax_url,
			data: params,
			success: function(res) {
				if (res.success && res.data) {
					if (res.data.content) {
						var pagiScroll = $widget.offset().top;
						if (pagiScroll > 100) {
							jQuery("html,body").animate({
								scrollTop: pagiScroll - 100
							},500);
						}
						$widget.find('.geodir_locations.geodir-wgt-pagination').replaceWith(res.data.content);
						init_read_more();
						geodir_init_lazy_load();
						geodir_refresh_business_hours();
						geodir_load_badge_class();
					}
				}
				$widget.removeClass('geodir-listings-loading').find('.geodir-ajax-listings-loader').hide();
			},
			fail: function(data) {
				$widget.removeClass('geodir-listings-loading').find('.geodir-ajax-listings-loader').hide();
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
    var $el = jQuery(el);
    var wEl = '.geodir-widget-posts';
    var rEl = '.col';
    if (!$el.find(wEl).length && $el.find('.elementor-posts').length) {
        var wEl = '.elementor-posts';
        var rEl = '.elementor-post';
    }
    var items = $el.find(wEl + ' > ' + rEl).length;
    var pitems = parseInt($el.data('with-items'));
    var fWidth = parseFloat(jQuery(window).width());
    if (pitems > 2 && fWidth < 768) {
        pitems = 2;
    }
    if (pitems < 1 || fWidth < 576) {
        pitems = 1;
    }
    if (items > 0 && items > pitems) {
        var slides = Math.ceil(items / pitems);
        var $carousel = $el.parent();
        var cid = $carousel.prop('id');
        var bsDash = jQuery('body').hasClass('aui_bs5') ? 'bs-' : '';
        if (!cid) {
            cid = $el.prop('id') + '_' + index;
            $carousel.prop('id', cid);
        }
        $carousel.addClass('carousel slide mx-n2').addClass('d-block').attr('data-' + bsDash + 'interval', $el.data(bsDash + 'interval')).attr('data-' + bsDash + 'ride', $el.data(bsDash + 'ride')).attr('data-' + bsDash + 'pause', $el.data(bsDash + 'pause'));
        $el.removeAttr('data-' + bsDash + 'interval data-' + bsDash + 'ride').removeClass('carousel slide');
        $el.addClass('carousel-inner');
        var $content = jQuery($el.html()).empty();
        $content = $content[0].outerHTML;
        $content = '<div class="carousel-item bs-carousel-item">' + $content.replace('</div>', '');
        var $html = $content;
        for (var i = 1; i <= items; i++) {
            var $_html = $el.find(wEl + ' > ' + rEl + ':nth-child(' + i + ')');
            $html += $_html[0].outerHTML;
            if (i % pitems === 0 && i < items) {
                $html += '</div></div>' + $content;
            }
        }
        $el.html($html + '</div>');
        var pdLeft = $el.find(wEl + ' > ' + rEl).css('padding-left'), pdRight = $el.find(wEl + ' > ' + rEl).css('padding-right');
        if (pdLeft && pdRight) {
            if (!$el.data('center-slide')) {
                $carousel.css({
                    'padding-left' : pdLeft,
                    'padding-right' : pdRight
                });
                pdLeft = 0;
                pdRight = 0;
            }
            $el.find('.bs-carousel-item').css({
                'padding-left' : pdLeft,
                'padding-right' : pdRight
            });
        }
        // Indicators
        if ($el.data('with-controls')) {
            var indicators = '<ol class="carousel-indicators position-relative">';
            for (var i = 0; i < slides; i++) {
                var cls = i == 0 ? ' active' : '';
                var attrs = i == 0 ? ' aria-current="true"' : '';
                indicators += '<li data-' + bsDash + 'target="#' + cid + '" data-' + bsDash + 'slide-to="' + i + '" class="bg-dark' + cls + '"' + attrs + '></li>';
            }
            indicators += '</ol>';
            jQuery(el).after(indicators);
        }
        // Controls
        if ($el.data('with-indicators')) {
            var winW = parseFloat(jQuery(window).outerWidth()), mN,i_mb = $el.data(bsDash + 'indicators-class');
            if (winW > 0 && winW <= 576) {
                mN = 'n2';
                $carousel.parent().addClass('px-3');
            } else if (winW > 576 && winW < 992) {
                mN = 'n3';
                $carousel.parent().addClass('px-3');
            } else {
                mN = 'n4';
                $carousel.parent().addClass('px-4');
            }
            var controls = '<a class="carousel-control-prev text-dark mr-2 ml-n2 me-2 ms-' + mN + ' w-auto '+i_mb+'" href="#' + cid + '" role="button" data-' + bsDash + 'slide="prev"><i class="fas fa-chevron-left fa-lg" aria-hidden="true"></i><span class="sr-only visually-hidden">' + geodir_params.txt_previous + '</span></a><a class="carousel-control-next text-dark ml-2 w-auto mr-n2 me-' + mN + ' ms-2 '+i_mb+'" href="#' + cid + '" role="button" data-' + bsDash + 'slide="next"><i class="fas fa-chevron-right fa-lg" aria-hidden="true"></i><span class="sr-only visually-hidden">' + geodir_params.txt_next + '</span></a>';
            jQuery(el).after(controls);
        }

        geodir_ajax_load_slider($el);
        $el.find('.bs-carousel-item:first').addClass('active');
        $el.find('.geodir-image-container .carousel-inner').removeClass('carousel-inner');
        $el.find('.geodir-image-container .carousel-item.active').removeClass('active');
        $el.find('.geodir-image-container .carousel-item').removeClass('carousel-item');

        if (bsDash && $el.data('center-slide') && slides > 1 && fWidth >= 576) {
            jQuery(document).trigger("geodir.init-posts-carousel", [{
                element: el,
                index: index,
                slides: slides
            }]);
        }
    }
}

/**
 * Loads the AUI modal.
 *
 * @param action
 * @param nonce
 * @param post_id
 * @param extra
 */
function geodir_aui_ajax_modal(action, nonce, post_id, extra) {
	if (action) {
		if (!nonce) {
			nonce = geodir_params.basic_nonce;
		}

		/* Close any instance of the popup */
		if ( geodir_params.addPopup ) {
			geodir_params.addPopup.remove();
		}

		/* Show loading screen */
		geodir_params.loader = aui_modal();

		var data = {
			action: action,
			security: nonce,
			post_id: post_id,
			extra: extra
		};

		jQuery.ajax({
			url: geodir_params.ajax_url,
			type: 'POST',
			data: data,
			dataType: 'json',
			beforeSend: function(xhr, obj) {}
		})
			.done(function(res, textStatus, jqXHR) {
				if (typeof res == 'object') {
					var _title, _body, _footer, _dismiss, _class;

					_title = res.data.title ? res.data.title : '';
					_body = res.data.body ? res.data.body : '';
					_footer = res.data.footer ? res.data.footer : '';
					_dismiss = typeof res.data.dismissible !== 'undefined' ? res.data.dismissible : true;
					_class = res.data.class ? res.data.class : 'geodir-aui-imodal';

					geodir_params.addPopup = aui_modal(_title,_body,_footer,_dismiss,_class);
				}
			})
			.always(function(data, textStatus, jqXHR) {
			});
	}
}

/**
 * Submit the report post.
 *
 * @param el
 */
function geodir_report_post(el) {
	var $form = jQuery(el), $button = $form.find('.geodir-report-post-button'), nonce = geodir_params.basic_nonce;

	// Recaptcha check
	if ($form.find('.g-recaptcha-response').length && $form.find('.g-recaptcha-response').val()=='') {
		return;
	}

	jQuery.ajax({
		url: geodir_params.ajax_url,
		type: 'POST',
		data: 'action=geodir_submit_report_post&security=' + nonce + '&' + $form.serialize(),
		dataType: 'json',
		beforeSend: function(xhr, obj) {
			$form.find('.geodir-report-post-msg').remove();
			$button.parent().find('.fa-spin').remove();
			$button.prop('disabled', true).after('<i class="fas fa-circle-notch fa-spin ml-2 ms-2"></i>');
		}
	})
		.done(function(res, textStatus, jqXHR) {
			if (typeof res == 'object'&& res.data.message ) {
				if ( res.success ) {
					$form.html(res.data.message);
				} else {
					$button.before(res.data.message);
				}
			}
		})
		.always(function(data, textStatus, jqXHR) {
			$button.parent().find('.fa-spin').remove();
			$button.prop('disabled', false);
		});
}

function bs_carousel_clone_slides() {
    jQuery('.bs-carousel .carousel-inner').each(function() {
        if (jQuery(this).find('.bs-carousel-item').length > 1 && !jQuery(this).find('.bs-carousel-item.bs-item-cloned').length) {
            jQuery(this).find('.bs-carousel-item:first').addClass('carousel-item-first');
            jQuery(this).find('.bs-carousel-item:last').addClass('carousel-item-last');
            cloneFirst = jQuery(this).find('.bs-carousel-item:first').clone();
            cloneLast = jQuery(this).find('.bs-carousel-item:last').clone();
            cloneFirst.removeClass('active carousel-item-first carousel-item-last').addClass('bs-item-cloned');
            cloneLast.removeClass('active carousel-item-first carousel-item-last').addClass('bs-item-cloned');
            jQuery(this).prepend(cloneLast);
            jQuery(this).append(cloneFirst);

            jQuery(this).closest('.bs-carousel-viewport').find('.carousel-indicators').each(function() {
                jQuery(this).find('[data-bs-slide-to]').each(function(i) {
                    jQuery(this).attr('data-bs-slide-to', parseInt(jQuery(this).attr('data-bs-slide-to')) + 1);
                });
            });
            el = {
                relatedTarget: jQuery(this).find('.bs-carousel-item.active'),
                to: jQuery(this).find('.bs-carousel-item.active').index()
            }
            bs_carousel_transform(el);
        }
    });
}

function bs_carousel_data(el) {
    var cData = [],
        $ci, slides = [],
        transform = 0,
        tItem = null;

    if (!el.relatedTarget) {
        return cData;
    }

    $ci = jQuery(el.relatedTarget).closest('.carousel-inner');

    if ($ci.length && $ci.find('.bs-carousel-item').length && $ci.find('.bs-carousel-item').length > 1) {
        var to = typeof el.to ? parseInt(el.to) : parseInt($ci.find('.bs-carousel-item.active').index());
        cW = parseFloat($ci.closest('.bs-carousel').outerWidth(false));
        mL = parseFloat($ci.closest('.bs-carousel').css('margin-left'));
        mR = parseFloat($ci.closest('.bs-carousel').css('margin-right'));
        ciW = (cW * $ci.find('.bs-carousel-item').length) + mL + mR;

        $ci.closest('.bs-carousel').css({
            'margin-left': mL + 'px',
            'margin-right': mR + 'px'
        });
        $ci.find('.bs-carousel-item').css({
            'width': cW + 'px'
        });
        $ci.css({
            'width': ciW + 'px'
        });

        $ci = jQuery(el.relatedTarget).closest('.carousel-inner');

        $ci.find('.bs-carousel-item').each(function(i) {
            var item = {
                'width': cW,
                'transform': transform * -1,
            }
            if (i == to) {
                tItem = item;
            }
            transform += cW;
            slides[i] = item;
        });

        cData = {
            'slides': slides,
            'aciveItem': tItem
        }
    }

    return cData;
}

function bs_carousel_transform(el) {
    var bsData = bs_carousel_data(el);

    if (bsData && typeof bsData.aciveItem != 'undefined') {
        var tf = 'translate3d(' + bsData.aciveItem.transform + 'px, 0px, 0px)';
        jQuery(el.relatedTarget).closest('.carousel-inner').css({
            '-webkit-transform': tf,
            '-moz-transform': tf,
            '-ms-transform': tf,
            '-o-transform': tf,
            'transform': tf
        });
    }
}

function bs_carousel_handle_events() {
    jQuery(".bs-carousel").on('slide.bs.carousel', function(el) {
        if (el.to > (jQuery(el.relatedTarget).closest('.carousel-inner').find('.bs-carousel-item').length - 2)) {
            el.to = 1;
        } else if (el.to < 1) {
            el.to = jQuery(el.relatedTarget).closest('.carousel-inner').find('.bs-carousel-item').length - 2;
        }
        bs_carousel_transform(el);
    });

    jQuery(".bs-carousel").on('slid.bs.carousel', function(el) {
        var $ci = jQuery(el.relatedTarget).closest('.carousel-inner');
        if ($ci.find('.bs-item-cloned').length) {
            $ci.find('.bs-item-cloned').removeClass('active');
            if (el.to > ($ci.find('.bs-carousel-item').length - 2)) {
                $ci.find('.carousel-item-first').addClass('active');
                $ci.closest('.bs-carousel-viewport').find('.carousel-indicators [data-bs-slide-to]:first').addClass('active').attr('aria-current', true);
            } else if (el.to < 1) {
                $ci.find('.carousel-item-last').addClass('active');
                $ci.closest('.bs-carousel-viewport').find('.carousel-indicators [data-bs-slide-to]:last').addClass('active').attr('aria-current', true);
            }
        }
    });
}

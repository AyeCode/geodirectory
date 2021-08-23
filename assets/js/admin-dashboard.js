jQuery(function($) {
    $('.gd-dashboard .gd-collapse').on('click', function(e) {
        var $parent = $(this).closest('.panel');
        if ($('.gd-collapsable', $parent).is(':visible')) {
            $('.gd-collapsable', $parent).slideUp(200, function() {
                $parent.addClass('gd-collapsed');
            });
            $(this).find('.fa-angle-up').addClass('fa-angle-down').removeClass('fa-angle-up');
        } else {
            $(this).find('.fa-angle-down', $parent).addClass('fa-angle-up').removeClass('fa-angle-down');
            $parent.removeClass('gd-collapsed');
            $('.gd-collapsable', $parent).slideDown(200);
        }
    });
    $('.gd-handlediv').on('click', function(e) {
        if ($(this).hasClass('gd-closed')) {
			$(this).removeClass('gd-closed');
			$('.' + $(this).data('handle')).slideDown(200);
		} else {
			$(this).addClass('gd-closed');
			$('.' + $(this).data('handle')).slideUp(200);
		}
    });
	 $('.gd-stats-nav .btn').on('click', function(e) {
        var type;
        $('.gd-stats-nav .btn.btn-primary').removeClass('btn-primary').addClass('btn-default');
        $(this).removeClass('btn-default').addClass('btn-primary');
        type = $(this).data('type');
        if (!type) {
            return false;
        }
        $('input#gd_stats_type').val(type);
        geodir_dashboard_stats();
    });
    $('#gd_stats_period').on('change', function(e) {
        geodir_dashboard_stats();
    });
    $('.gd-stats-nav [data-type="all"]').trigger('click');

    function geodir_dashboard_stats() {
        var $wrap, type, period, stats, stats_html = '',
            stat_html;
        $wrap = $('.gd-stats-data');
        type = $('#gd_stats_type').val();
        period = $('#gd_stats_period').val();
        geodir_dashboard_wait('start');
        var data = {
            action: 'geodir_stats_ajax',
            type: type,
            period: period,
        };
        $.ajax({
            url: geodir_params.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: data,
            beforeSend: function() {},
            success: function(res, textStatus, xhr) {
                if (typeof res == 'object') {
                    if (res.stats) {
                        stat_html = $('.gd-stat-format', $wrap).html();
                        $.each(res.stats, function(key, item) {
                            stats_html += stat_html.replace('{stat}', key).replace('{icon}', item.icon).replace('{label}', item.label).replace('{value}', item.value);
                        });
                        $('.gd-stats-items', $wrap).html(stats_html);
                    }
                    if (res.chart_params) {
                        if (res.chart_type && res.chart_type == 'bar') {
							geodir_dashboard_bar_chart(res.chart_params);
						} else {
							geodir_dashboard_line_chart(res.chart_params);
						}
                    }
                }
                geodir_dashboard_wait('end');
            },
            error: function(xhr, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                geodir_dashboard_wait('end');
            }
        });
    }

    function geodir_dashboard_wait(ev) {
        var $navs, $wrap;
        $navs = $('.gd-stats-nav');
        $wrap = $('.gd-stats-data');
        if (ev == 'start') {
            $('.btn', $navs).addClass('disabled');
            $wrap.addClass('gd-stats-wait');
        } else {
            $('.btn', $navs).removeClass('disabled');
            $wrap.removeClass('gd-stats-wait');
        }
    }

    function geodir_dashboard_bar_chart(custom_params) {
        var default_params, chart_params, gd_chart, labels = '', chartId;
        default_params = {
            element: 'gd-dashboard-chart',
            data: [],
            xkey: 'key',
            ykeys: [],
            labels: [],
            hideHover: 'auto',
            resize: true,
            parseTime: false,
            yLabelFormat: function(y) {
                return y != Math.round(y) ? '' : y;
            },
        };
        chart_params = $.extend(default_params, custom_params);
		chartId = default_params.element;
		$('#' + chartId).html('');
        try {
			gd_chart = Morris.Bar(chart_params);
			$('#' + chartId).closest('.gd-stats-chart').find('.gd-chart-legends').html('');
		} catch (err) {
			console.log(err.message);
		}
    }

	function geodir_dashboard_line_chart(custom_params) {
        var default_params, chart_params, gd_chart, labels = '', chartId;
        default_params = {
            element: 'gd-dashboard-chart',
            data: [],
            xkey: 'key',
            ykeys: [],
            labels: [],
            pointSize: 3.5,
			lineWidth: 2,
            hideHover: 'auto',
            resize: true,
            xLabelAngle: 45,
            parseTime: false,
            yLabelFormat: function(y) {
                return y != Math.round(y) ? '' : y;
            },
        };
        chart_params = $.extend(default_params, custom_params);
		chartId = default_params.element;
		$('#' + chartId).html('');
        try {
			gd_chart = Morris.Line(chart_params);
			if (gd_chart.options.labels && gd_chart.options.labels.length) {
				gd_chart.options.labels.forEach(function(label, i) {
					labels += '<span class="gd-dash-legend" data-color="' + gd_chart.options.lineColors[i] + '"><span class="color" style="background-color:' + gd_chart.options.lineColors[i] + '"></span> <span class="gd-dash-label">' + label + '</span></span>';
				});
			}
			$('#' + chartId).closest('.gd-stats-chart').find('.gd-chart-legends').html(labels);
			$('.gd-dash-legend').on('click', function(e) {
				if ($(this).hasClass('gd-morris-hidden')) {
					$(this).removeClass('gd-morris-hidden');
					$('#gd-morris-style', $(this)).remove();
				} else {
					var c = $(this).data('color');
					var cL = $(this).data('color').toLowerCase();
					$(this).addClass('gd-morris-hidden');
					$(this).append('<style id="gd-morris-style">#gd-dashboard-chart circle[fill="' + cL + '"],#gd-dashboard-chart .morris-hover-point[style="color: ' + c + '"],#gd-dashboard-chart path[stroke="' + cL + '"]{display:none;}</style>');
				}
			});
		} catch (err) {
			console.log(err.message);
		}
    }
});
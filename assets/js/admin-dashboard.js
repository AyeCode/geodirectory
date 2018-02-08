jQuery(function($) {
	$('.gd-dashboard .gd-collapse').on('click',function(e){
		var $parent = $(this).closest('.panel');
		if ($('.gd-collapsable', $parent).is(':visible')) {
			//$('.panel-footer.gd-collapsable', $parent).slideUp(100);
			$('.gd-collapsable', $parent).slideUp(200, function(){
				$parent.addClass('gd-collapsed');
			});
			$(this).find('.fa-caret-up').addClass('fa-caret-down').removeClass('fa-caret-up');
		} else {
			$(this).find('.fa-caret-down', $parent).addClass('fa-caret-up').removeClass('fa-caret-down');
			$parent.removeClass('gd-collapsed');
			$('.gd-collapsable', $parent).slideDown(200, function(){
				//$('.panel-footer.gd-collapsable', $parent).slideDown(100);
			});
		}
	});
	/*
	var chart = Morris.Line({
        element: 'gd-dashboard-chart',
        data: [{
            date: '2018-02-01',
            listings: 2666,
            reviews: 0,
            users: 2647
        }, {
            date: '2018-02-02',
            listings: 2778,
            reviews: 2294,
            users: 2441
        }, {
            date: '2018-02-03',
            listings: 0,
            reviews: 1969,
            users: 2501
        }, {
            date: '2018-02-04',
            listings: 3767,
            reviews: 3597,
            users: 5689
        }, {
            date: '2018-02-05',
            listings: 6810,
            reviews: 0,
            users: 2293
        }, {
            date: '2018-02-06',
            listings: 5670,
            reviews: 4293,
            users: 0
        }, {
            date: '2018-02-07',
            listings: 4820,
            reviews: 3795,
            users: 1588
        }, {
            date: '2018-02-08',
            listings: 15073,
            reviews: 5175,
            users: 5967
        }, {
            date: '2018-02-09',
            listings: 10687,
            reviews: 4460,
            users: 2028
        }, {
            date: '2018-02-10',
            listings: 8432,
            reviews: 5713,
            users: 1791
        }],
        xkey: 'date',
        ykeys: ['listings', 'reviews', 'users'],
        labels: ['Listings', 'Reviews', 'Users'],
        pointSize: 4,
        hideHover: 'auto',
        resize: true,
		xLabels: 'day',
		//behaveLikeLine: true
    });
	chart.options.labels.forEach(function(label, i){
		var legend = '<span class="gd-dash-legend"><span class="color" style="background-color:' + chart.options.lineColors[i] + '"></span> <span class="gd-dash-label">' + label + '</span></span>';
		$('#gd-dashboard-chart').closest('.gd-stats-chart').find('.gd-chart-legends').append(legend);
	});
	*/
});
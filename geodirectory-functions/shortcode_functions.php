<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

function gdsc_validate_measurements( $value ) {
	if ( ( strlen( $value ) - 1 ) == strpos( trim( $value ), '%' ) ) {
		// $value is entered as a percent, so it can't be less than 0 or more than 100
		$value = preg_replace( '/\D/', '', $value );
		if ( 100 < $value ) {
			$value = 100;
		}
		// Re-add the percent symbol
		$value = $value . '%';
	} elseif ( ( strlen( $value ) - 2 ) == strpos( trim( $value ), 'px' ) ) {
		// Get the absint & re-add the 'px'
		$value = preg_replace( '/\D/', '', $value ) . 'px';
	} else {
		$value = preg_replace( '/\D/', '', $value );
	}

	return $value;
}

function gdsc_validate_map_args( $params ) {

	$params['width']  = gdsc_validate_measurements( $params['width'] );
	$params['height'] = gdsc_validate_measurements( $params['height'] );

	// Only accept our 3 maptypes. Otherwise, revert to the default.
	if ( ! ( in_array( strtoupper( $params['maptype'] ), array( 'HYBRID', 'SATELLITE', 'ROADMAP' ) ) ) ) {
		$params['maptype'] = 'ROADMAP';
	} else {
		$params['maptype'] = strtoupper( $params['maptype'] );
	}

	// Zoom accepts a value between 1 and 19
	$params['zoom'] = absint( $params['zoom'] );
	if ( 19 < $params['zoom'] ) {
		$params['zoom'] = '19';
	}
	if ( 0 == $params['zoom'] ) {
		$params['zoom'] = '1';
	}

	// Child_collapse must be boolean
	$params['child_collapse'] = gdsc_to_bool_val( $params['child_collapse'] );

	// Scrollwheel must be boolean
	$params['scrollwheel'] = gdsc_to_bool_val( $params['scrollwheel'] );

	// Scrollwheel must be boolean
	$params['autozoom'] = gdsc_to_bool_val( $params['autozoom'] );

	return $params;
}

/** Checks a variable to see if it should be considered a boolean true or false.
 *     Also takes into account some text-based representations of true of false,
 *     such as 'false','N','yes','on','off', etc.
 * @author Samuel Levy <sam+nospam@samuellevy.com>
 *
 * @param mixed $in The variable to check
 * @param bool $strict If set to false, consider everything that is not false to
 *                     be true.
 *
 * @return bool The boolean equivalent or null
 */
function gdsc_to_bool_val( $in, $strict = false ) {
	$out = null;

	// if not strict, we only have to check if something is false
	if ( in_array( $in, array(
		'false',
		'False',
		'FALSE',
		'no',
		'No',
		'n',
		'N',
		'0',
		'off',
		'Off',
		'OFF',
		false,
		0,
		null
	), true ) ) {
		$out = false;
	} else if ( $strict ) {
		// if strict, check the equivalent true values
		if ( in_array( $in, array(
			'true',
			'True',
			'TRUE',
			'yes',
			'Yes',
			'y',
			'Y',
			'1',
			'on',
			'On',
			'ON',
			true,
			1
		), true ) ) {
			$out = true;
		}
	} else {
		// not strict? let the regular php bool check figure it out (will
		//     largely default to true)
		$out = ( $in ? true : false );
	}

	return $out;
}

function gdsc_is_post_type_valid( $incoming_post_type ) {
	$post_types      = geodir_get_posttypes();
	$post_types      = array_map( 'strtolower', $post_types );
	$post_type_found = false;
	foreach ( $post_types as $type ) {
		if ( strtolower( $incoming_post_type ) == strtolower( $type ) ) {
			$post_type_found = true;
		}
	}

	return $post_type_found;
}

function gdsc_listing_loop_filter( $query ) {
	global $wp_query, $geodir_post_type, $table, $plugin_prefix, $table, $term;

	$geodir_post_type = geodir_get_current_posttype();

	if ( isset( $wp_query->tax_query->queries ) && $wp_query->tax_query->queries ) {
		$taxonomies = wp_list_pluck( $wp_query->tax_query->queries, 'taxonomy' );

		if ( isset( $wp_query->query[ $taxonomies[0] ] ) ) {
			$request_term = explode( "/", $wp_query->query[ $taxonomies[0] ] );
			$request_term = end( $request_term );
			if ( ! term_exists( $request_term ) ) {
				$args      = array( 'number' => '1', );
				$terms_arr = get_terms( $taxonomies[0], $args );
				foreach ( $terms_arr as $location_term ) {
					$term_arr       = $location_term;
					$term_arr->name = ucwords( str_replace( '-', ' ', $request_term ) );
				}
				$wp_query->queried_object_id = 1;
				$wp_query->queried_object    = $term_arr;
				//print_r($wp_query) ;
			}
		}

	}
	if ( isset( $query->query_vars['is_geodir_loop'] ) && $query->query_vars['is_geodir_loop'] ) {

		$table = $plugin_prefix . $geodir_post_type . '_detail';

		add_filter( 'posts_fields', 'geodir_posts_fields', 1 );
		add_filter( 'posts_join', 'geodir_posts_join', 1 );
		geodir_post_where();
		if ( ! is_admin() ) {
			add_filter( 'posts_orderby', 'geodir_posts_orderby', 1 );
		}

		// advanced filter for popular post view widget
		global $wp_query;
		if ( ! is_admin() ) {
			if ( ! empty( $wp_query->query['with_pics_only'] ) ) {
				add_filter( 'posts_join', 'geodir_filter_widget_join', 1000 );
			}
			add_filter( 'posts_where', 'geodir_filter_widget_where', 1000 );
		}

	}

	return $query;
}

function gdsc_manage_category_choice( $post_type, $category ) {
	if ( 0 == $category || '' == $category ) {
		return '';
	}

	if ( ! ( gdsc_is_post_type_valid( $post_type ) ) ) {
		return '';
	}

	$taxonomies = geodir_get_taxonomies( $post_type );

	$categories = get_terms( array( 'taxonomy' => $taxonomies[0] ) );

	$cat_id = 0;

	foreach ( $categories as $cat ) {
		if ( is_numeric( $category ) ) {
			if ( absint( $category ) == $cat->term_id ) {
				$cat_id = $cat->term_id;
				break;
			}
		} else {
			if ( $category == $cat->slug ) {
				$cat_id = $cat->term_id;
				break;
			}

			if ( $category == $cat->name ) {
				$cat_id = $cat->term_id;
				break;
			}
		}
	}

	return $cat_id;
}

// @todo: Extract this
// This is wrong, it should be in JS and CSS files.
if ( ! ( function_exists( 'geodir_home_map_add_script' ) ) ) {
	function geodir_home_map_add_script() {
		echo '<style>.geodir-map-home-page .geodir-map-posttype-list li{margin-left:0;} .geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list{padding-left:0;margin-left:0;}.geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list > li{display:inline-block;float:none}.geodir-map-home-page.geodir_map_container .map-places-listing ul.place-list>li:first-child{padding-left:0;}.geodir-map-home-page .geodir-map-posttype-list{display:block;overflow:hidden;white-space:nowrap;width:100%;word-wrap:normal;position:relative;}</style>';
		?>
		<script type="text/javascript">
			jQuery(document).ready(function () {
				geoDirMapSlide();
				jQuery(window).resize(function () {
					jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
						jQuery(this).find('.geodir-map-posttype-list').css({'width': 'auto'});
						jQuery(this).find('.map-places-listing ul.place-list').css({'margin-left': '0px'});
						geoDirMapPrepare(this);
					});
				});
			});
			function geoDirMapPrepare($thisMap) {
				var $objMpList = jQuery($thisMap).find('.geodir-map-posttype-list');
				var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
				var wArrL = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-leftarrow').outerWidth(true));
				var wArrR = parseFloat(jQuery($thisMap).find('.geodir-map-navigation .geodir-rightarrow').outerWidth(true));
				var ptw1 = parseFloat($objMpList.outerWidth(true));
				$objMpList.css({'margin-left': wArrL + 'px'});
				$objMpList.attr('data-width', ptw1);
				ptw1 = ptw1 - (wArrL + wArrR);
				$objMpList.width(ptw1);
				var ptw = $objPlList.width();
				var ptw2 = 0;
				$objPlList.find('li').each(function () {
					var ptw21 = jQuery(this).outerWidth(true);
					ptw2 += parseFloat(ptw21);
				});
				var doMov = parseFloat(ptw * 0.75);
				ptw2 = ptw2 + ( ptw2 * 0.05 );
				var maxMargin = ptw2 - ptw;
				$objPlList.attr('data-domov', doMov);
				$objPlList.attr('data-maxMargin', maxMargin);
			}
			function geoDirMapSlide() {
				jQuery('.geodir_map_container.geodir-map-home-page').each(function () {
					var $thisMap = this;
					geoDirMapPrepare($thisMap);
					var $objPlList = jQuery($thisMap).find('.map-places-listing ul.place-list');
					jQuery($thisMap).find('.geodir-leftarrow a').click(function (e) {
						e.preventDefault();
						var cm = $objPlList.css('margin-left');
						var doMov = parseFloat($objPlList.attr('data-domov'));
						var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
						cm = parseFloat(cm);
						if (cm == 0 || maxMargin < 0) {
							return;
						}
						domargin = cm + doMov;
						if (domargin > 0) {
							domargin = 0;
						}
						$objPlList.animate({'margin-left': domargin + 'px'}, 1000);
					});
					jQuery($thisMap).find('.geodir-rightarrow a').click(function (e) {
						e.preventDefault();
						var cm = $objPlList.css('margin-left');
						var doMov = parseFloat($objPlList.attr('data-domov'));
						var maxMargin = parseFloat($objPlList.attr('data-maxMargin'));
						cm = parseFloat(cm);
						domargin = cm - doMov;
						if (cm == ( maxMargin * -1 ) || maxMargin < 0) {
							return;
						}
						if (( domargin * -1 ) > maxMargin) {
							domargin = maxMargin * -1;
						}
						$objPlList.animate({'margin-left': domargin + 'px'}, 1000);
					});
				});
			}
		</script>
	<?php
	}
}

function geodir_popular_category_add_scripts() {
	?>

	<script type="text/javascript">
		jQuery(function ($) {
			$('.geodir-showcat').click(function () {
				var objCat = $(this).closest('.geodir-category-list-in');
				$(objCat).find('li.geodir-pcat-hide').removeClass('geodir-hide');
				$(objCat).find('a.geodir-showcat').addClass('geodir-hide');
				$(objCat).find('a.geodir-hidecat').removeClass('geodir-hide');
			});
			$('.geodir-hidecat').click(function () {
				var objCat = $(this).closest('.geodir-category-list-in');
				$(objCat).find('li.geodir-pcat-hide').addClass('geodir-hide');
				$(objCat).find('a.geodir-hidecat').addClass('geodir-hide');
				$(objCat).find('a.geodir-showcat').removeClass('geodir-hide');
			});
		});
	</script>
<?php
}

function gdsc_validate_layout_choice( $layout_choice ) {
	switch ( strtolower( $layout_choice ) ) {
		case 'list';
		case 'one';
		case 'one_column';
		case 'onecolumn';
		case '1';
			$layout_choice = 'list';
			break;
		case 'gridview_onehalf';
		case 'two';
		case 'two_column';
		case 'two_columns';
		case 'twocolumn';
		case 'twocolumns';
		case '2';
			$layout_choice = 'gridview_onehalf';
			break;
		case 'gridview_onethird';
		case 'three';
		case 'three_column';
		case 'three_columns';
		case 'threecolumn';
		case 'threecolumns';
		case '3';
			$layout_choice = 'gridview_onethird';
			break;
		case 'gridview_onefourth';
		case 'four';
		case 'four_column';
		case 'four_columns';
		case 'fourcolumn';
		case 'fourcolumns';
		case '4';
			$layout_choice = 'gridview_onefourth';
			break;
		case 'gridview_onefifth';
		case 'five';
		case 'five_column';
		case 'five_columns';
		case 'fivecolumn';
		case 'fivecolumns';
		case '5';
			$layout_choice = 'gridview_onefifth';
			break;
		default:
			$layout_choice = 'gridview_onehalf';
			break;
	}

	return $layout_choice;
}

function gdsc_validate_sort_choice( $sort_choice ) {
	$sorts = array(
		'az',
		'latest',
		'featured',
		'high_review',
		'high_rating',
		'random',
	);

	if ( ! ( in_array( $sort_choice, $sorts ) ) ) {
		$sort_choice = 'latest';
	}

	return $sort_choice;
}

function gdsc_validate_listing_width( $width_choice ) {
	if ( ! ( empty( $width_choice ) ) ) {
		$width_choice = absint( $width_choice );
	} else {
		return '';
	}

	if ( 100 < $width_choice ) {
		$width_choice = 100;
	}

	// If listing_width is too narrow, it won't work, arbitrarily set to 10% here
	if ( 10 > $width_choice ) {
		$width_choice = 10;
	}

	return $width_choice;
}

function gdsc_validate_list_filter_choice( $filter_choice ) {
	$filters = array(
		'all',
		'today',
		'upcoming',
		'past',
	);

	if ( ! ( in_array( $filter_choice, $filters ) ) ) {
		$filter_choice = 'all';
	}

	return $filter_choice;
}

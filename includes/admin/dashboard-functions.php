<?php
add_filter( 'geodir_dashboard_get_stats', 'geodir_dashboard_listings_stats', 1, 3 ); 			// 1. Listings
add_filter( 'geodir_dashboard_get_stats', 'geodir_dashboard_reviews_stats', 2, 3 ); 			// 2. Reviews
// @todo implement via related plugin
//add_filter( 'geodir_dashboard_get_stats', 'geodir_dashboard_paid_listings_stats', 4, 3 ); 		// 4. Paid Listings
//add_filter( 'geodir_dashboard_get_stats', 'geodir_dashboard_revenues_stats', 5, 3 ); 			// 5. Revenues
add_filter( 'geodir_dashboard_get_stats', 'geodir_dashboard_users_stats', 6, 3 ); 				// 6. Users

/**
 * Dashboard starts and end dates.
 *
 * @since 2.0.0
 *
 * @param string $period Optional. Dates Month period. Default this_month.
 *
 * @return array $dates.
 */
function geodir_dashboard_start_end_dates( $period = 'this_month' ) {
	$dates = array();

	switch ( $period ) {
		case 'this_year':
		case 'last_year':
			$year = $period == 'this_year' ? date( 'Y', time() ) : date( 'Y', strtotime( '-1 year' ) );

			for ( $m = 1; $m <= 12; $m++ ) {
				$time = strtotime( date( $year . '-' . $m . '-01' ) );
				$dates[ date( 'M', $time ) ] = array (
					'start' => date( $year . '-m-01 00:00:00', $time ),
					'end' => date( $year . '-m-t 23:59:59', $time )
				);
			}
		break;
		case 'this_month':
		case 'last_month':
			$time = $period == 'this_month' ? time() : strtotime( '-1 month' );
			$year = date( 'Y', $time );
			$month = date( 'm', $time );
			$last_day = date( 't', $time );

			for ( $d = 1; $d <= $last_day; $d++ ) {
				$dates[ $d ] = array (
					'start' => $year . '-' . $month . '-' . sprintf( '%02d', $d ) . ' 00:00:00',
					'end' => $year . '-' . $month . '-' . sprintf( '%02d', $d ) . ' 23:59:59'
				);
			}
		break;
		case 'this_week':
		case 'last_week':
			$time = $period == 'this_week' ? time() : strtotime( '-1 week' );
			$year = date( 'Y', $time );
			$month = date( 'm', $time );
			$week = date( 'W', $time );

			for ( $d = 1; $d <= 7; $d++ ) {
				$time = strtotime( $year . '-W' . $week . '-' . $d );
				$dates[ date( 'D', $time ) ] = array (
					'start' => date( 'Y-m-d 00:00:00', $time ),
					'end' => date( 'Y-m-d 23:59:59', $time ),
				);
			}
		break;
	}
	
	return apply_filters( 'geodir_dashboard_start_end_dates', $dates, $period );
}

/**
 * Dashboard default chart parameters.
 *
 * @since 2.0.0
 *
 * @param $type Type.
 * @param $period Start and end date Month period.
 *
 * @return array $params
 */
function geodir_dashboard_default_chart_params( $type, $period ) {
	$params = array();

	$dates = geodir_dashboard_start_end_dates( $period );
	$data = array();
	if ( ! empty( $dates ) ) {
		foreach ( $dates as $day => $days ) {
			$data[] = array( 'key' => $day );
		}
	}
	$params['ykeys'] = array();
	$params['labels'] = array();
	$params['data'] = $data;

	return apply_filters( 'geodir_dashboard_default_chart_params', $params, $type, $period );
}

/**
 * Dashboard ajax state.
 *
 * @since 2.0.0
 */
function geodir_dashboard_ajax_stats() {
	$data = array();
	
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json( $data );
		exit;
	}
	
	$type = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : 'all';
	$period = isset( $_REQUEST['period'] ) ? sanitize_text_field( $_REQUEST['period'] ) : 'this_month';
	
	$data = array( 
		'stats' => array(), 
		'chart_params' => geodir_dashboard_default_chart_params( $type, $period )
	);
	$data = apply_filters( 'geodir_dashboard_get_stats', $data, $type, $period );
	
	wp_send_json( $data );
	exit;
}
add_action( 'wp_ajax_geodir_stats_ajax', 'geodir_dashboard_ajax_stats' );

/**
 * Dashboard listings states.
 *
 * @since 2.0.0
 *
 * @param array $stats Listing states array.
 * @param string $type Listing type.
 * @param string $period period.
 * @return array $stats
 */
function geodir_dashboard_listings_stats( $stats, $type, $period ) {
	$post_types	= geodir_get_posttypes();
	if ( ! ( $type == 'all' || in_array( $type, $post_types ) ) ) {
		return $stats;
	}

	$listing_stats = geodir_dashboard_post_types_stats( $type, $period );

	$stat_key = 'listings';
	$stat_label = __( 'Listings', 'geodirectory' );

	$stats['stats'][ $stat_key ] = array(
		'icon' => 'fas fa-list-ul',
		'label' => $stat_label,
		'value' => ! empty( $listing_stats['total'] ) ? (int)$listing_stats['total'] : 0
	);

	$stats['chart_params']['ykeys'][] = $stat_key;
	$stats['chart_params']['labels'][] = $stat_label;

	if ( ! empty( $stats['chart_params']['data'] ) ) {
		foreach ( $stats['chart_params']['data'] as $key => $data ) {
			$count = 0;
			if ( ! empty( $listing_stats['post_types'] ) ) {
				foreach ( $listing_stats['post_types'] as $post_type => $cpt_data ) {
					if ( isset( $cpt_data['dates'] ) && isset( $data['key'] ) && isset( $cpt_data['dates'][ $data['key'] ] ) ) {
						$count += $cpt_data['dates'][ $data['key'] ];
					}
				}
			}
			$stats['chart_params']['data'][$key][ $stat_key ] = $count;
		}
	}

	return $stats;
}

/**
 * Dashboard post types states.
 *
 * @since 2.0.0
 *
 * @param string $type Optional. Listing type Default all.
 * @param string $period Optional. Period. Default all.
 * @param array $statuses Optional. Statuses. Default array.
 * @return array $stats.
 */
function geodir_dashboard_post_types_stats( $type = 'all', $period = 'all', $statuses = array() ) {
	$post_types	= geodir_get_posttypes( 'array' );
	if ( ! empty( $type ) && $type != 'all' ) {
		$post_types = isset( $post_types[ $type ] ) ? array( $type => $post_types[ $type ] ) : array();
	}

	$total = 0;
	$items = array();
	foreach ( $post_types as $post_type => $cpt ) {
		$post_type_stats = geodir_dashboard_post_type_stats( $post_type, $period, $statuses );
		$items[ $post_type ] = $post_type_stats;
		if ( ! empty( $post_type_stats['total'] ) ) {
			$total += (int)$post_type_stats['total'];
		}
	}
	$stats = array( 'post_types' => $items , 'total' => $total );
	
	return apply_filters( 'geodir_dashboard_post_types_stats', $stats, $type, $period, $statuses );
}

/**
 * Dashboard post type states.
 *
 * @since 2.0.0
 *
 * @param $post_type Post type.
 * @param string $period Optional. Period. Default all.
 * @param array $statuses Optional. Statuses. Default array.
 * @return array $stats.
 */
function geodir_dashboard_post_type_stats( $post_type, $period = 'all', $statuses = array() ) {
	$dates = geodir_dashboard_start_end_dates( $period );

	$stats = array();
	if ( ! empty( $dates ) ) {
		$total = 0;
		$date_stats = array();
		foreach ( $dates as $day => $days ) {
			$count = geodir_dashboard_query_posts_count( $post_type, $statuses, "AND post_date >= '" . $days['start'] . "' AND post_date <= '" . $days['end'] . "'" );
			$date_stats[ $day ] = $count;
			$total += $count;
		}
		$stats['dates'] = $date_stats;
		$stats['total'] = $total;
	} else {
		$stats['dates'] = array();
		$stats['total'] = geodir_dashboard_query_posts_count( $post_type, $statuses );
	}
	
	return apply_filters( 'geodir_dashboard_post_type_stats', $stats, $period, $post_type, $statuses );
}

/**
 * Dashboard posts Query count.
 *
 * @since 2.0.0
 *
 * @param $post_type Post type.
 * @param array $statuses Optional. Statuses. Default array.
 * @param string $where Optional. Where. Default null.
 * @return array $count.
 */
function geodir_dashboard_query_posts_count( $post_type, $statuses = array(), $where = '' ) {
	global $wpdb;

	if ( empty( $statuses ) ) {
		$statuses = array_keys( geodir_get_post_statuses( $post_type ) );
		$statuses = apply_filters( 'geodir_dashboard_query_posts_statuses', $statuses, $post_type );
	} else {
		if ( ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}
	}

	$query = "SELECT COUNT(ID) FROM " . $wpdb->posts . " WHERE post_type = '" . $post_type . "' AND post_status IN('" . implode( "','", $statuses ) . "') " . $where;
	$count = $wpdb->get_var( $query );

	return apply_filters( 'geodir_dashboard_query_posts_count', $count, $post_type, $statuses, $where );
}

/**
 * Dashboard reviews stats.
 *
 * @since 2.0.0
 *
 * @param $stats State.
 * @param $type Type.
 * @param $period Period.
 * @return array $stats.
 */
function geodir_dashboard_reviews_stats( $stats, $type, $period ) {
	$post_types	= geodir_get_posttypes();
	if ( ! ( $type == 'all' || in_array( $type, $post_types ) ) ) {
		return $stats;
	}

	$review_stats = geodir_dashboard_post_types_reviews_stats( $type, $period );

	$stat_key = 'reviews';
	$stat_label = __( 'Reviews', 'geodirectory' );

	$stats['stats'][ $stat_key ] = array(
		'icon' => 'fas fa-star',
		'label' => $stat_label,
		'value' => ! empty( $review_stats['total'] ) ? (int)$review_stats['total'] : 0
	);

	$stats['chart_params']['ykeys'][] = $stat_key;
	$stats['chart_params']['labels'][] = $stat_label;

	if ( ! empty( $stats['chart_params']['data'] ) ) {
		foreach ( $stats['chart_params']['data'] as $key => $data ) {
			$count = 0;
			if ( ! empty( $review_stats['post_types'] ) ) {
				foreach ( $review_stats['post_types'] as $post_type => $cpt_data ) {
					if ( isset( $cpt_data['dates'] ) && isset( $data['key'] ) && isset( $cpt_data['dates'][ $data['key'] ] ) ) {
						$count += $cpt_data['dates'][ $data['key'] ];
					}
				}
			}
			$stats['chart_params']['data'][$key][ $stat_key ] = $count;
		}
	}

	return $stats;
}

/**
 * Dashboard Post type reviews stats.
 *
 * @since 2.0.0
 *
 * @param string $type Optional. Type. Default all.
 * @param string $period Optional. Period. Default all.
 * @param array $statuses Optional. Statuses. Default array.
 * @return array $stats.
 */
function geodir_dashboard_post_types_reviews_stats( $type = 'all', $period = 'all', $statuses = array() ) {
	$post_types	= geodir_get_posttypes( 'array' );
	if ( ! empty( $type ) && $type != 'all' ) {
		$post_types = isset( $post_types[ $type ] ) ? array( $type => $post_types[ $type ] ) : array();
	}

	$total = 0;
	$items = array();
	foreach ( $post_types as $post_type => $cpt ) {
		$post_type_stats = geodir_dashboard_post_type_reviews_stats( $post_type, $period, $statuses );
		$items[ $post_type ] = $post_type_stats;
		if ( ! empty( $post_type_stats['total'] ) ) {
			$total += (int)$post_type_stats['total'];
		}
	}
	$stats = array( 'post_types' => $items , 'total' => $total );
	
	return apply_filters( 'geodir_dashboard_post_type_reviews_stats', $stats, $type, $period, $statuses );
}

/**
 * Dashboard post type reviews state.
 *
 * @since 2.0.0
 *
 * @param string $post_type Post type.
 * @param string $period Optional. Period Default all.
 * @param array $statuses Optional. Statuses. Default array.
 * @return array $stats.
 */
function geodir_dashboard_post_type_reviews_stats( $post_type, $period = 'all', $statuses = array() ) {
	$dates = geodir_dashboard_start_end_dates( $period );

	$stats = array();
	if ( ! empty( $dates ) ) {
		$total = 0;
		$date_stats = array();
		foreach ( $dates as $day => $days ) {
			$count = geodir_dashboard_query_reviews_count( $post_type, $statuses, "AND cmt.comment_date >= '" . $days['start'] . "' AND cmt.comment_date <= '" . $days['end'] . "'" );
			$date_stats[ $day ] = $count;
			$total += $count;
		}
		$stats['dates'] = $date_stats;
		$stats['total'] = $total;
	} else {
		$stats['dates'] = array();
		$stats['total'] = geodir_dashboard_query_reviews_count( $post_type, $statuses );
	}
	
	return apply_filters( 'geodir_dashboard_post_type_reviews_stats', $stats, $period, $post_type, $statuses );
}

/**
 * Dashboard reviews count.
 *
 * @since 2.0.0
 *
 * @param string $post_type Posttype.
 * @param array $statuses Optional. Statuses. Default array.
 * @param string $where Optional. Where. Default null.
 * @return array $count.
 */
function geodir_dashboard_query_reviews_count( $post_type, $statuses = array(), $where = '' ) {
	global $wpdb;

	if ( empty( $statuses ) ) {
		$statuses = array( '1', '0' );
		$statuses = apply_filters( 'geodir_dashboard_query_reviews_statuses', $statuses, $post_type );
	} else {
		if ( ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}
	}

	$query = "SELECT COUNT(r.comment_id) FROM " . GEODIR_REVIEW_TABLE . " AS r INNER JOIN {$wpdb->comments} AS cmt ON cmt.comment_ID = r.comment_id INNER JOIN {$wpdb->posts} AS p ON p.ID = cmt.comment_post_ID WHERE p.post_type = '" . $post_type . "' AND p.post_status = 'publish' AND cmt.comment_approved IN('" . implode( "','", $statuses ) . "') AND r.rating > 0 " . $where;
	$count = $wpdb->get_var( $query );

	return apply_filters( 'geodir_dashboard_query_reviews_count', $count, $post_type, $statuses, $where );
}

/**
 * Dashboard users stats.
 *
 * @since 2.0.0
 *
 * @param array $stats Stats.
 * @param string $type Type.
 * @param string $period Period.
 * @return array $stats.
 */
function geodir_dashboard_users_stats( $stats, $type, $period ) {
	if ( $type != 'all' ) {
		return $stats;
	}

	$user_stats = geodir_dashboard_get_users_stats( $type, $period );

	$stat_key = 'users';
	$stat_label = __( 'Users', 'geodirectory' );

	$stats['stats'][ $stat_key ] = array(
		'icon' => 'fas fa-users',
		'label' => $stat_label,
		'value' => ! empty( $user_stats['total'] ) ? (int)$user_stats['total'] : 0
	);

	$stats['chart_params']['ykeys'][] = $stat_key;
	$stats['chart_params']['labels'][] = $stat_label;

	if ( ! empty( $stats['chart_params']['data'] ) ) {
		foreach ( $stats['chart_params']['data'] as $key => $data ) {
			$count = 0;
			if ( ! empty( $user_stats['dates'] ) ) {
				if ( isset( $user_stats['dates'] ) && isset( $data['key'] ) && isset( $user_stats['dates'][ $data['key'] ] ) ) {
					$count += $user_stats['dates'][ $data['key'] ];
				}
			}
			$stats['chart_params']['data'][$key][ $stat_key ] = $count;
		}
	}

	return $stats;
}

/**
 * Dashboard get users stats.
 *
 * @since 2.0.0
 *
 * @param string $type Optional. Type. Default all.
 * @param string $period Optional. Period. Default all.
 * @param array $statuses Optional. Statuses. Default array.
 * @return array $stats.
 */
function geodir_dashboard_get_users_stats( $type = 'all', $period = 'all', $statuses = array() ) {
	$dates = geodir_dashboard_start_end_dates( $period );

	$stats = array();
	if ( ! empty( $dates ) ) {
		$total = 0;
		$date_stats = array();
		foreach ( $dates as $day => $days ) {
			$count = geodir_dashboard_query_users_count( $statuses, "AND user_registered >= '" . $days['start'] . "' AND user_registered <= '" . $days['end'] . "'" );
			$date_stats[ $day ] = $count;
			$total += $count;
		}
		$stats['dates'] = $date_stats;
		$stats['total'] = $total;
	} else {
		$stats['dates'] = array();
		$stats['total'] = geodir_dashboard_query_users_count( $statuses );
	}
	
	return apply_filters( 'geodir_dashboard_get_users_stats', $stats, $type, $period, $statuses );
}

/**
 * Dashboard users count.
 *
 * @since 2.0.0
 *
 * @param array $statuses Optional. Statuses. Default array.
 * @param string $where Optional. Where. Default null.
 * @return array $count.
 */
function geodir_dashboard_query_users_count( $statuses = array(), $where = '' ) {
	global $wpdb;

	if ( empty( $statuses ) ) {
		$statuses = array( '1', '0' );
		$statuses = apply_filters( 'geodir_dashboard_query_users_statuses', $statuses );
	} else {
		if ( ! is_array( $statuses ) ) {
			$statuses = array( $statuses );
		}
	}

	$query = "SELECT COUNT(ID) FROM {$wpdb->users} WHERE user_status IN('" . implode( "','", $statuses ) . "') " . $where;
	$count = $wpdb->get_var( $query );

	return apply_filters( 'geodir_dashboard_query_users_count', $count, $statuses, $where );
}

/**
 * Dashboard paid listings stats.
 *
 * @since 2.0.0
 *
 * @param array $stats Stats.
 * @param string $type Type.
 * @param string $period Period.
 * @return array $stats.
 */
function geodir_dashboard_paid_listings_stats( $stats, $type, $period ) {
	$stat_key = 'paid_listings';
	$stat_label = __( 'Paid Listings', 'geodirectory' );

	$stats['stats'][ $stat_key ] = array(
		'icon' => 'fas fa-shopping-cart',
		'label' => $stat_label,
		'value' => 0
	);

	$stats['chart_params']['ykeys'][] = $stat_key;
	$stats['chart_params']['labels'][] = $stat_label;

	if ( ! empty( $stats['chart_params']['data'] ) ) {
		foreach ( $stats['chart_params']['data'] as $key => $data ) {
			$stats['chart_params']['data'][$key][ $stat_key ] = 0;
		}
	}

	return $stats;
}

/**
 * Dashboard revenue stats.
 *
 * @since 2.0.0
 *
 * @param array $stats Stats.
 * @param string $type Type.
 * @param string $period Period.
 * @return array $stats.
 */
function geodir_dashboard_revenues_stats( $stats, $type, $period ) {
	$stat_key = 'revenues';
	$stat_label = __( 'Revenues', 'geodirectory' );

	$stats['stats'][ $stat_key ] = array(
		'icon' => 'fas fa-dollar-sign',
		'label' => $stat_label,
		'value' => 0
	);

	$stats['chart_params']['ykeys'][] = $stat_key;
	$stats['chart_params']['labels'][] = $stat_label;

	if ( ! empty( $stats['chart_params']['data'] ) ) {
		foreach ( $stats['chart_params']['data'] as $key => $data ) {
			$stats['chart_params']['data'][$key][ $stat_key ] = 0;
		}
	}

	return $stats;
}

/**
 * Dashboard get pending stats.
 *
 * @since 2.0.0
 *
 * @return array $stats.
 */
function geodir_dashboard_get_pending_stats() {
	$stats = array();
	$stats['listings'] = array(
		'icon' => 'fas fa-list-ul',
		'label' => __( 'Pending Listings', 'geodirectory' ),
		'total' => 0,
		'url' => '',
	);
	$stats['reviews'] = array(
		'icon' => 'fas fa-star',
		'label' => __( 'Pending Reviews', 'geodirectory' ),
		'total' => 0,
		'url' => admin_url( 'edit-comments.php?comment_type=comment&comment_status=moderated' ),
	);
	
	
	
	$item_stats = geodir_dashboard_post_types_stats( 'all', 'all', array( 'pending' ) );
	if ( ! empty( $item_stats['post_types'] ) ) {
		$items = array();
		foreach ( $item_stats['post_types'] as $post_type => $data ) {
			$items[] = array(
				'icon' => 'fas fa-map-marker-alt',
				'label' => geodir_post_type_name( $post_type ),
				'total' => $data['total'],
				'url' => admin_url( 'edit.php?post_status=pending&post_type=' . $post_type ),
			);
		}
		$stats['listings']['items'] = $items;
		$stats['listings']['total'] = $item_stats['total'];
	}
	
	$item_stats = geodir_dashboard_post_types_reviews_stats( 'all', 'all', array( '0' ) );
	if ( ! empty( $item_stats['post_types'] ) ) {
		$items = array();
		foreach ( $item_stats['post_types'] as $post_type => $data ) {
			$items[] = array(
				'icon' => 'fas fa-map-marker-alt',
				'label' => geodir_post_type_name( $post_type ),
				'total' => $data['total'],
				'url' => '',
			);
		}
		$stats['reviews']['items'] = $items;
		$stats['reviews']['total'] = $item_stats['total'];
	}

	
	return apply_filters( 'geodir_dashboard_get_pending_stats', $stats );
}
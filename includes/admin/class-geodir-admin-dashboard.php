<?php
/**
 * GeoDirectory Admin Dashboard
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_Admin_Dashboard', false ) ) {

/**
 * GeoDir_Admin_Dashboard Class.
 */
class GeoDir_Admin_Dashboard {

	/**
	 * GeoDirectory Dashboard instance.
	 */
	private static $instance;
	
	public $pages;
	public $type;
	public $subtype;
	public $gd_post_types;
	
	/**
	 * Main GeoDirectory Dashboard Instance.
	 *
	 * @since 2.0.0
	 */
	public static function instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof GeoDir_Admin_Dashboard ) ) {
			self::$instance = new GeoDir_Admin_Dashboard;

			self::$instance->setup_actions();
			self::$instance->setup_constants();
		}
		return self::$instance;
	}
	
	/**
	 * A dummy constructor to prevent GeoDirectory Dashboard from being loaded more than once.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		$this->gd_post_types	= geodir_get_posttypes( 'array' );
		$this->type 			= ! empty( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : 'index';
		$this->subtype 			= ! empty( $_REQUEST['subtype'] ) ? sanitize_text_field( $_REQUEST['subtype'] ) : '';
	}
	
	/**
	 * Include required files
	 *
	 * @access private
	 */
	private function setup_actions() {
		add_filter( 'geodir_admin_dashboard_pages', array( $this, 'setup_subtypes' ), 10, 2 );
		add_action( 'geodir_admin_dashboard_top', array( $this, 'breadcrumb' ), -10, 1 );
		add_action( 'geodir_admin_dashboard_top', array( $this, 'title' ), -10.1, 1 );
		add_action( 'geodir_admin_dashboard_content', array( $this, 'dashboard_stats' ), 10, 1 );
		add_action( 'geodir_admin_dashboard_bottom', array( $this, 'dashboard_chart' ), 10, 1 );
		
		add_filter( 'geodir_dashboard_stats_item_index_listings', array( $this, 'listings_stats' ), 10, 6 );
		add_filter( 'geodir_dashboard_stats_item_index_reviews', array( $this, 'reviews_stats' ), 10, 6 );
		add_filter( 'geodir_dashboard_stats_item_index_users', array( $this, 'users_stats' ), 10, 6 );
		add_filter( 'geodir_dashboard_get_chart_js_params', array( $this, 'filter_chart_js_params' ), 10, 4 );
		
		foreach ( $this->gd_post_types as $post_type => $info ) {
			add_filter( 'geodir_dashboard_stats_item_listings_' . $post_type, array( $this, 'listings_post_type_stats' ), 10, 6 );
			add_filter( 'geodir_dashboard_stats_item_reviews_' . $post_type, array( $this, 'reviews_post_type_stats' ), 10, 6 );
		}
		
		add_filter( 'geodir_dashboard_get_listings_chart_stats', array( $this, 'get_listings_chart_stats' ), 10, 1 );
		add_filter( 'geodir_dashboard_get_reviews_chart_stats', array( $this, 'get_reviews_chart_stats' ), 10, 1 );
		add_filter( 'geodir_dashboard_get_users_chart_stats', array( $this, 'get_users_chart_stats' ), 10, 1 );
	}

	/**
	 * Setup constants
	 *
	 * @access private
	 */
	private function setup_constants() {
		$current_url 			= geodir_curPageURL();

		$this->pages 			= apply_filters( 'geodir_admin_dashboard_pages', array(
			'index' => array(
				'link' => admin_url( 'admin.php?page=geodirectory' ),
				'title' => __( 'Dashboard', 'geodirectory' ),
				'icon' => 'fa-tachometer'
			),
			'listings' => array(
				'link' => add_query_arg( array( 'type' => 'listings', 'stats' => true ), $current_url ),
				'title' => __( 'Listings', 'geodirectory' ),
				'icon' => 'fa-th-list'
			),
			'reviews' => array(
				'link' => add_query_arg( array( 'type' => 'reviews', 'stats' => true ), $current_url ),
				'title' => __( 'Reviews', 'geodirectory' ),
				'icon' => 'fa-star'
			),
			'users' => array(
				'link' => add_query_arg( array( 'type' => 'users', 'chart' => true ), $current_url ),
				'title' => __( 'Users', 'geodirectory' ),
				'icon' => 'fa-user'
			),
		) );
	}
	
	public function setup_subtypes( $pages ) {
		
		if ( ! empty( $pages['listings'] ) ) {
			$listings_link = remove_query_arg( array( 'stats' ), $pages['listings']['link'] );
			$listings_link = add_query_arg( array( 'chart' => true ), $listings_link );

			$subtypes = array();
			foreach ( $this->gd_post_types as $post_type => $info ) {
				$subtypes[ $post_type ] = array(
					'link' 		=> add_query_arg( array( 'subtype' => $post_type ), $listings_link ),
					'title'		=> __( $info['labels']['name'], 'geodirectory' ),
					'icon' 		=> $pages['listings']['icon'],
					'chart' 	=> true
				);
			}

			$pages['listings']['subtypes'] = $subtypes;
		}
		
		if ( ! empty( $pages['reviews'] ) ) {
			$reviews_link = remove_query_arg( array( 'stats' ), $pages['reviews']['link'] );
			$reviews_link = add_query_arg( array( 'chart' => true ), $reviews_link );
			
			$subtypes = array();
			foreach ( $this->gd_post_types as $post_type => $info ) {
				$subtypes[ $post_type ] = array(
					'link' 		=> add_query_arg( array( 'subtype' => $post_type ), $reviews_link ),
					'title' 	=> __( $info['labels']['name'], 'geodirectory' ),
					'icon' 		=> $pages['reviews']['icon']
				);
			}

			$pages['reviews']['subtypes'] = $subtypes;
		}

		return $pages;
	}
	
	/**
	 * Handles output of the dashboard page in admin.
	 */
	public function output() {
		do_action( 'geodir_admin_dashboard_before', $this );
		?>
		<div class="wrap gd-dashboard <?php echo 'gd-dasht-' . $this->type . ' gd-dashst-' . $this->subtype; ?>">
			<div class="container">
				<?php do_action( 'geodir_admin_dashboard_top', $this ); ?>
				<?php do_action( 'geodir_admin_dashboard_content', $this ); ?>
				<?php do_action( 'geodir_admin_dashboard_bottom', $this ); ?>
			</div>
		</div>
		<?php
		do_action( 'geodir_admin_dashboard_after' );
	}

	/**
	 * Dashboard page breadcrumb.
	 */
	public function breadcrumb( $instance ) {
		if ( $this->type == 'index' && empty( $this->subtype ) ) {
			return;
		}
		
		$type = isset( $this->pages[ $this->type ] ) ? $this->pages[ $this->type ] : NULL;
		$subtype = $this->subtype && ! empty( $type ) && isset( $type['subtypes'][ $this->subtype ] ) ? $type['subtypes'][ $this->subtype ] : NULL;
		
		$breadcrumbs = array();
		$breadcrumbs['index'] = array(
			'link' => $this->pages['index']['link'],
			'title' => $this->pages['index']['title'],
		);
		if ( ! empty( $type ) ) {
			$breadcrumbs[ $this->type ] = array(
				'link' => ! empty( $type['link'] ) ? $type['link'] : '#',
				'title' => ! empty( $type['title'] ) ? $type['title'] : geodir_utf8_ucfirst( $this->type ),
				'active' => false
			);
		}
		
		if ( ! empty( $subtype ) ) {
			$breadcrumbs[ $this->subtype ] = array(
				'link' => ! empty( $subtype['link'] ) ? $subtype['link'] : '#',
				'title' => ! empty( $subtype['title'] ) ? $subtype['title'] : geodir_utf8_ucfirst( $this->subtype ),
			);
		}
		?>
		<div class="row breadcrumb-row">
			<nav class="breadcrumb">
				<?php $c = 1; foreach ( $breadcrumbs as $id => $breadcrumb ) { ?>
					<?php if ( $c < count( $breadcrumbs ) ) { ?>
						<a class="breadcrumb-item gd-dashb-<?php echo $id; ?>" href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['title']; ?></a> / 
					<?php } else { ?>
						<span class="breadcrumb-item active gd-dashb-<?php echo $id; ?>"><?php echo $breadcrumb['title'] ; ?></span>
					<?php } ?>
				<?php $c++; } ?>
			</nav>
		</div>
		<?php
	}
	
	/**
	 * Dashboard page title.
	 */
	public function title( $instance ) {
		$type = isset( $this->pages[ $this->type ] ) ? $this->pages[ $this->type ] : NULL;
		$subtype = $this->subtype && ! empty( $type ) && isset( $type['subtypes'][ $this->subtype ] ) ? $type['subtypes'][ $this->subtype ] : NULL;
		
		if ( ! empty( $subtype ) ) {
			$title = ! empty( $subtype['title'] ) ? $subtype['title'] : geodir_utf8_ucfirst( $this->subtype );
			$url = ! empty( $subtype['link'] ) ? $subtype['link'] : '#';
			$icon = ! empty( $subtype['icon'] ) ? $subtype['icon'] : '';
		} else {
			$title = ! empty( $type['title'] ) ? $type['title'] : geodir_utf8_ucfirst( $this->type );
			$url = ! empty( $type['link'] ) ? $type['link'] : '#';
			$icon = ! empty( $type['icon'] ) ? $type['icon'] : '';
		}
		$fa_icon = ! empty( $icon ) ? '<i class="fa ' . $icon . '"></i> ' : '';
		?>
		<div class="row title-row">
			<h2 class="gd-dash-title"><?php echo $fa_icon; ?><?php echo apply_filters( 'geodir_dashboard_title', $title, $this ); ?></h2>
		</div>
		<?php
	}
	
	public function get_stats() {
		$parent = 'index';
		
		$parent_item = $this->pages;
		if ( ! empty( $this->subtype ) ) {
			$items = isset( $this->pages[ $this->type ] ) && isset( $this->pages[ $this->type ]['subtypes'][ $this->subtype ] ) ? $this->pages[ $this->type ]['subtypes'][ $this->subtype ] : array();
			$parent = $this->type . '_' . $this->subtype;
			$parent_item = $this->pages[ $this->type ];
		} elseif ( ! empty( $this->type ) && $this->type != 'index' ) {
			$items = isset( $this->pages[ $this->type ] ) && isset( $this->pages[ $this->type ]['subtypes'] ) ? $this->pages[ $this->type ]['subtypes'] : array();
			$parent = $this->type;
			$parent_item = $this->pages[ $this->type ];
		} else {
			$items = $this->pages;
		}
		
		$stats = array();

		if ( ! empty( $items ) ) {
			foreach ( $items as $key => $item ) {
				if ( ! is_array( $item ) ) {
					continue;
				}
				
				$item_stats = apply_filters( 'geodir_dashboard_stats_' . $parent, array(), $this, $key, $parent, $item, $parent_item );
				$item_stats = apply_filters( 'geodir_dashboard_stats_item_' . $parent . '_' . $key, array(), $this, $key, $parent, $item, $parent_item );

				$item_stats = array( 'stats' => $item_stats );
				$item_stats['filters'] = array( 'geodir_dashboard_stats_' . $parent, 'geodir_dashboard_stats_item_' . $parent . '_' . $key );

				$stats[ $key ] = array_merge( $item, $item_stats );
			}
		}

		return apply_filters( 'geodir_dashboard_get_stats', $stats, $this );
	}
	
	public function dashboard_stats( $instance ) {
		if ( empty( $_GET['stats'] ) && $this->type != 'index' ) {
			return;
		}
		
		$items = $this->get_stats();
		if ( empty( $items ) ) {
			return;
		}
		?>
		<div class="row gd-dash-stats-wrap">
		<?php foreach ( $items as $key => $item ) { ?>
			<?php if ( !empty( $item['stats'] ) ) { ?>
			<?php echo $this->get_stats_grid( $key, $item ); ?>
			<?php } ?>
		<?php } ?>
		</div>
		<?php
	}
	
	public function dashboard_chart( $instance ) {
		if ( empty( $_GET['chart'] ) ) {
			return;
		}

		?>
		<div class="wrap gd-chart-wrap">
			<?php echo $this->get_chart_tabs(); ?>
			<?php echo $this->get_chart_html(); ?>
        </div>
		<?php
	}
	
	public function get_stats_grid( $type, $args ) {
		$defaults = array(
            'link' => '',
            'icon' => "fa-th-list",
            'title' => "",
            'stats' => array()
        );
        $args = wp_parse_args( $args, $defaults );
		
		$link = ! empty( $args['link'] ) ? $args['link'] : '#';
		$icon = ! empty( $args['icon'] ) ? '<i class="fa ' . $args['icon'] . '"></i>' : 'fa fa-th-list';

		ob_start();
		?>
		<div class="gd-dash-box-wrap">
            <section class="gd-dash-box">
                <div class="gd-dash-box-inner">
                    <a class="gd-dash-box-icon" href="<?php echo $link; ?>"><?php echo $icon ?></a>
                    <div class="gd-dash-box-title"><?php echo $args['title']; ?></div>
                    <div class="gd-dash-box-sep"></div>
                    <?php foreach ( $args['stats'] as $key => $value ) { ?>
						<h4 class="gd-dash-box-stat"><strong><?php echo $value; ?></strong><small><?php echo $key; ?></small></h4>
					<?php } ?>
                </div>
            </section>
        </div>
		<?php
		$content = ob_get_contents();
        ob_end_clean();
		return $content;
	}
	
	public function listings_stats( $stats, $instance, $current, $parent, $current_item, $parent_item ) {
		$stats[__( 'Listings', 'geodirectory' )] = $this->get_listings_count();

		return $stats;
	}
	
	public function reviews_stats( $stats, $instance, $current, $parent, $current_item, $parent_item ) {
		$stats[__( 'Reviews', 'geodirectory' )] = $this->get_reviews_count();

		return $stats;
	}
	
	public function users_stats( $stats, $instance, $current, $parent, $current_item, $parent_item ) {
		$stats[__( 'Users', 'geodirectory' )] = $this->get_users_count();

		return $stats;
	}
	
	public function listings_post_type_stats( $stats, $instance, $current, $parent, $current_item, $parent_item ) {
		$title = ! empty( $current_item['title'] ) ? $current_item['title'] : $current;
		$stats[ $title  ] 														= $this->get_post_type_count( $current );
		$stats[ wp_sprintf( __( '%s Categpries', 'geodirectory' ), $title ) ]	= wp_count_terms( $current . 'category');
		$stats[ wp_sprintf( __( '%s Tags', 'geodirectory' ), $title ) ] 		= wp_count_terms( $current . '_tags');

		return $stats;
	}
	
	public function reviews_post_type_stats( $stats, $instance, $current, $parent, $current_item, $parent_item ) {
		$title = ! empty( $parent_item['title'] ) ? $parent_item['title'] : $parent;
		$stats[ $title  ] = $this->get_post_type_reviews_count( $current );

		return $stats;
	}
	
	public function get_listings_count() {
        $count = 0;

		foreach ( $this->gd_post_types as $post_type => $info ) {
			$count += (int)$this->get_post_type_count( $post_type );
		}

        return $count;
    }
	
	public function get_post_type_count( $post_type ) {
        $count_posts = wp_count_posts( $post_type );

		$count = (int)$count_posts->publish + (int)$count_posts->draft + (int)$count_posts->trash + (int)$count_posts->pending;
		
        return $count;
    }
	
	public function get_users_count() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(ID) FROM {$wpdb->users}" );
        return (int)$count;
    }
	
	public function get_reviews_count() {
        $count = 0;

		foreach ( $this->gd_post_types as $post_type => $info ) {
			$count += (int)$this->get_post_type_reviews_count( $post_type );
		}

        return $count;
    }
	
	public function get_post_type_reviews_count( $post_type ) {
        global $wpdb;

        $count = (int)$wpdb->get_var( $wpdb->prepare( "SELECT COUNT( overall_rating ) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_type = %s AND post_status = 1 AND status=1 AND overall_rating > 0", $post_type ) );

        return $count;
    }
	
	public function get_chart_tabs() {
		$current_url = geodir_curPageURL();
		$duration = ! empty( $_GET['duration'] ) ? sanitize_text_field( $_GET['duration'] ) : 'this_year';
		
		?>
		<div class="gd-dash-btn-group" data-toggle="buttons">
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'this_week' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'this_week', $current_url ); ?>"><?php _e( 'This Week', 'geodirectory' ); ?></a>
			</label>
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'last_week' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'last_week', $current_url ); ?>"><?php _e( 'Last Week', 'geodirectory' ); ?></a>
			</label>
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'this_month' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'this_month', $current_url ); ?>"><?php _e( 'This Month', 'geodirectory' ); ?></a>
			</label>
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'last_month' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'last_month', $current_url ); ?>"><?php _e( 'Last Month', 'geodirectory' ); ?></a>
			</label>
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'this_year' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'this_year', $current_url ); ?>"><?php _e( 'This Year', 'geodirectory' ); ?></a>
			</label>
			<label class="gd-dash-btn gd-dash-btn-sm gd-dash-btn-white <?php echo ( $duration == 'last_year' ? "active" : '' ); ?>">
				<a href="<?php echo add_query_arg( 'duration', 'last_year', $current_url ); ?>"><?php _e( 'Last Year', 'geodirectory' ); ?></a>
			</label>
		</div>
		<div class="gd-dash-box-sep"></div>
		<?php
	}
	
	public function get_chart_html() {
		$chart_stats = apply_filters( 'geodir_dashboard_get_' . $this->type . '_chart_stats', array(), $this );
		
		if ( empty( $chart_stats ) ) {
			return;
		}
		
		$chart_js_data = $this->get_chart_js_params( $chart_stats );

		?>
		<div class="gd-chart-html">
			<canvas id="gdDashListings"></canvas>
			<script type="text/javascript">
				var gdChartId = document.getElementById('gdDashListings').getContext('2d');
				var myChart = new Chart(gdChartId, <?php echo json_encode( $chart_js_data ); ?>);
			</script>
		</div>
		<?php
	}
	
	public function get_chart_js_params( $stats, $chart_type = 'line' ) {
		if ( empty( $stats ) ) {
			return array();
		}

		$datasets_data = array();
		foreach ( $stats as $label => $values ) {
			foreach ( $values as $label => $value ) {
				if ( ! isset( $datasets_data[ $label ] ) )  {
					$datasets_data[ $label ] = array();
				}
				$datasets_data[ $label ][] = $value;
			}
		}

		$data = array();
		$data['labels'] = array_keys( $stats );
		$data['datasets'] = array();
		foreach ( $datasets_data as $label => $values ) {
			$data['datasets'][] = array(
				'label' => ucfirst( $label ),
				'data' => $values,
				'fillColor' => 'rgba(220,220,220,0.5)',
				'strokeColor' => 'rgba(220,220,220,1)',
				'pointColor' => 'rgba(220,220,220,1)',
				'pointStrokeColor' => '#fff',
				'backgroundColor' => 'rgba(220,220,220,0.5)'
			);
		}
		
		$js_params = array();
		$js_params['type'] = $chart_type;
		$js_params['data'] = $data;
		
		return apply_filters( 'geodir_dashboard_get_chart_js_params', $js_params, $stats, $chart_type, $this );
	}
	
	public function get_listings_chart_stats() {
		$duration 	= ! empty( $_GET['duration'] ) ? sanitize_text_field( $_GET['duration'] ) : 'this_year';
		$post_type 	= $this->subtype;
		if ( empty( $this->gd_post_types[ $post_type ] ) ) {
			return;
		}
		
		return $this->get_listings_stats_by( $duration, $post_type );
	}
	
	public function get_reviews_chart_stats() {
		$duration 	= ! empty( $_GET['duration'] ) ? sanitize_text_field( $_GET['duration'] ) : 'this_year';
		$post_type 	= $this->subtype;
		if ( empty( $this->gd_post_types[ $post_type ] ) ) {
			return;
		}
		
		return $this->get_reviews_stats_by( $duration, $post_type );
	}
	
	public function get_users_chart_stats() {
		$duration 	= ! empty( $_GET['duration'] ) ? sanitize_text_field( $_GET['duration'] ) : 'this_year';
		
		return $this->get_users_stats_by( $duration );
	}
	
	public function get_listings_stats_by( $stats_by, $post_type ) {
		$dates = $this->get_start_end_dates( $stats_by );

		$stats = array();
		foreach ( $dates as $day => $days ) {
			$stats[ $day ]['total'] = $this->query_posts_count( $post_type, "AND post_date <= '" . $days['end'] . "'" );
			$stats[ $day ]['new'] = $this->query_posts_count( $post_type, "AND post_date >= '" . $days['start'] . "' AND post_date <= '" . $days['end'] . "'" );
		}
		
		return apply_filters( 'geodir_dashboard_listings_stats_by', $stats, $stats_by, $post_type );
	}
	
	public function get_reviews_stats_by( $stats_by, $post_type ) {
		$dates = $this->get_start_end_dates( $stats_by );

		$stats = array();
		foreach ( $dates as $day => $days ) {
			$stats[ $day ]['total'] = $this->query_reviews_count( $post_type, "AND post_date <= '" . $days['end'] . "'" );
			$stats[ $day ]['new'] = $this->query_reviews_count( $post_type, "AND post_date >= '" . $days['start'] . "' AND post_date <= '" . $days['end'] . "'" );
		}
		
		return apply_filters( 'geodir_dashboard_reviews_stats_by', $stats, $stats_by, $post_type );
	}
	
	public function get_users_stats_by( $stats_by ) {
		$dates = $this->get_start_end_dates( $stats_by );

		$stats = array();
		foreach ( $dates as $day => $days ) {
			$stats[ $day ]['total'] = $this->query_users_count( "AND user_registered <= '" . $days['end'] . "'" );
			$stats[ $day ]['new'] = $this->query_users_count( "AND user_registered >= '" . $days['start'] . "' AND user_registered <= '" . $days['end'] . "'" );
		}
		
		return apply_filters( 'geodir_dashboard_users_stats_by', $stats, $stats_by );
	}
	
	public function query_posts_count( $post_type, $where ) {
		global $wpdb;

		$statuses = array_keys( get_post_statuses() );

		$query = "SELECT COUNT(ID) FROM " . $wpdb->posts . " WHERE post_type = '" . $post_type . "' AND post_status IN('" . implode( "','", $statuses ) . "') " . $where;

		return $wpdb->get_var( $query );
	}
	
	public function query_reviews_count( $post_type, $where ) {
		global $wpdb;

		$query = "SELECT COUNT(id) FROM " . GEODIR_REVIEW_TABLE . " WHERE post_type = '" . $post_type . "' AND post_status = 1 AND status = 1 AND overall_rating > 0 " . $where;

		return $wpdb->get_var( $query );
	}
	
	public function query_users_count( $where ) {
		global $wpdb;

		$query = "SELECT COUNT(ID) FROM " . $wpdb->users . " WHERE 1 " . $where;

		return $wpdb->get_var( $query );
	}

	public function get_start_end_dates( $type = 'this_year' ) {
		$dates = array();

		switch ( $type ) {
			case 'this_year':
			case 'last_year':
				$year = $type == 'this_year' ? date( 'Y', time() ) : date( 'Y', strtotime( '-1 year' ) );

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
				$time = $type == 'this_month' ? time() : strtotime( '-1 month' );
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
				$time = $type == 'this_week' ? time() : strtotime( '-1 week' );
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
		
		return $dates;
	}
	
	public function filter_chart_js_params( $params, $stats, $chart_type, $class ) {
		if ( ! empty( $params['data']['datasets'] ) ) {
			$datasets = array();

			foreach ( $params['data']['datasets'] as $key => $data ) {
				$dataset = $data;

				if ( ! empty( $data['label'] ) ) {
					if ( $data['label'] == 'Total' ) {
						$dataset['fillColor'] = 'rgba(220,220,220,0.5)';
						$dataset['strokeColor'] = 'rgba(220,220,220,1)';
						$dataset['pointColor'] = 'rgba(220,220,220,1)';
						$dataset['pointStrokeColor'] = '#fff';
						$dataset['backgroundColor'] = 'rgba(220,220,220,0.5)';
					} elseif ( $data['label'] == 'New' ) {
						$dataset['fillColor'] = 'rgba(151,187,205,0.5)';
						$dataset['strokeColor'] = 'rgba(151,187,205,1)';
						$dataset['pointColor'] = 'rgba(151,187,205,1)';
						$dataset['pointStrokeColor'] = '#fff';
						$dataset['backgroundColor'] = 'rgba(151,187,205,0.5)';
					}					
				}

				$datasets[$key] = $dataset;
			}

			$params['data']['datasets'] = $datasets;
		}

		return $params;
	}
}

}

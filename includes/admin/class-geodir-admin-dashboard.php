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

			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->setup_actions();
		}
		return self::$instance;
	}
	
	/**
	 * A dummy constructor to prevent GeoDirectory Dashboard from being loaded more than once.
	 *
	 * @since 2.0.0
	 */
	private function __construct() {
		/* Do nothing here */ 
	}
	
	/**
	 * Setup constants
	 *
	 * @access private
	 */
	private function setup_constants() {
		$current_url = geodir_curPageURL();
		$this->gd_post_types = geodir_get_posttypes( 'array' );

		$this->pages = apply_filters( 'geodir_admin_dashboard_pages', array(
			'index' => array(
				'link' => admin_url( 'admin.php?page=geodirectory' ),
				'title' => __( 'Dashboard', 'geodirectory' ),
				'icon' => 'fa-tachometer'
			),
			'listings' => array(
				'link' => add_query_arg( 'type', 'listings', $current_url ),
				'title' => __( 'Listings', 'geodirectory' ),
				'icon' => 'fa-th-list'
			),
			'reviews' => array(
				'link' => add_query_arg( 'type', 'reviews', $current_url ),
				'title' => __( 'Reviews', 'geodirectory' ),
				'icon' => 'fa-star'
			),
			'users' => array(
				'link' => add_query_arg( 'type', 'users', $current_url ),
				'title' => __( 'Users', 'geodirectory' ),
				'icon' => 'fa-user'
			),
		) );
		
		$this->type = ! empty( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : 'index';
		$this->subtype = ! empty( $_REQUEST['subtype'] ) ? sanitize_text_field( $_REQUEST['subtype'] ) : '';
	}
	
	/**
	 * Include required files
	 *
	 * @access private
	 */
	private function includes() {
	}
	
	/**
	 * Include required files
	 *
	 * @access private
	 */
	private function setup_actions() {
		add_action( 'geodir_admin_dashboard_top', array( $this, 'breadcrumb' ), -10, 1 );
		add_action( 'geodir_admin_dashboard_top', array( $this, 'title' ), -10.1, 1 );
		add_action( 'geodir_admin_dashboard_content', array( $this, 'dashboard_stats' ), 10, 1 );
		add_action( 'geodir_admin_dashboard_bottom', array( $this, 'dashboard_chart' ), 10, 1 );
		
		add_filter( 'geodir_dashboard_stats_item_index_listings', array( $this, 'index_listings_stats' ), 10, 2 );
		add_filter( 'geodir_dashboard_stats_item_index_reviews', array( $this, 'index_reviews_stats' ), 10, 2 );
		add_filter( 'geodir_dashboard_stats_item_index_users', array( $this, 'index_users_stats' ), 10, 2 );
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
						<a class="breadcrumb-item gd-dashb-<?php echo $id; ?>" href="<?php echo $breadcrumb['link']; ?>"><?php echo $breadcrumb['title']; ?></a>
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
			<h2 class="gd-dash-title"><?php echo $fa_icon; ?><?php echo $title; ?></h2>
		</div>
		<?php
	}
	
	public function get_stats() {
		$parent = 'index';
		
		if ( ! empty( $this->subtype ) ) {
			$items = isset( $this->pages[ $this->type ] ) && isset( $this->pages[ $this->type ]['subtypes'][ $this->subtype ] ) ? $this->pages[ $this->type ]['subtypes'][ $this->subtype ] : array();
			$parent = $this->type . '_' . $this->subtype;
		} elseif ( ! empty( $this->type ) && $this->type != 'index' ) {
			$items = isset( $this->pages[ $this->type ] ) && isset( $this->pages[ $this->type ]['subtypes'] ) ? $this->pages[ $this->type ]['subtypes'] : array();
			$parent = $this->type;
		} else {
			$items = $this->pages;
		}
		
		$stats = array();
		if ( ! empty( $items ) ) {
			foreach ( $items as $key => $item ) {
				$item_stats = apply_filters( 'geodir_dashboard_stats_' . $parent, array(), $key, $this );
				$item_stats = apply_filters( 'geodir_dashboard_stats_item_' . $parent . '_' . $key, array(), $this );

				$item_stats = array( 'stats' => $item_stats );
				$item_stats['filters'] = array( 'geodir_dashboard_stats_' . $parent, 'geodir_dashboard_stats_item_' . $parent . '_' . $key );

				$stats[ $key ] = array_merge( $item, $item_stats );
			}
		}
		//gddev_log( $stats, 'stats', __FILE__, __LINE__ );
		return apply_filters( 'geodir_dashboard_get_stats', $stats, $this );
	}
	
	public function dashboard_stats( $instance ) {
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
		$items = $this->get_stats();
		
		if ( empty( $items ) ) {
			return;
		}
		$labels = array();
		$data_total = array();
		$data_new = array();
		?>
		<div class="row gd-dash-chart-wrap">
		<canvas id="gdDashListings"></canvas>
        <script type="text/javascript">
            var ctx = document.getElementById('gdDashListings').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Listings", "Reviews", "Users"],
                    datasets: [{
                        label: 'Total',
                        data: [10, 3, 2],
                        fillColor : "rgba(220,220,220,0.5)",
                        strokeColor : "rgba(220,220,220,1)",
                        pointColor : "rgba(220,220,220,1)",
                        pointStrokeColor : "#fff",
                        backgroundColor: "rgba(220,220,220,0.5)"
                    },{
                        label: 'New',
                        data: [2, 1, 1],
                        fillColor : "rgba(151,187,205,0.5)",
                        strokeColor : "rgba(151,187,205,1)",
                        pointColor : "rgba(151,187,205,1)",
                        pointStrokeColor : "#fff",
                        backgroundColor: "rgba(151,187,205,0.5)"
                    }]
                }
            });
        </script>
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
	
	public function index_listings_stats( $stats, $instance ) {
		$stats[__( 'Listings', 'geodirectory' )] = $this->get_listings_count();

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
	
	public function index_reviews_stats( $stats, $instance ) {
		$stats[__( 'Reviews', 'geodirectory' )] = $this->get_reviews_count();

		return $stats;
	}
	
	public function index_users_stats( $stats, $instance ) {
		$stats[__( 'Users', 'geodirectory' )] = $this->get_users_count();

		return $stats;
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
}

}

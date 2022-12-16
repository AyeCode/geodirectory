<?php // @todo implement events data once addon done
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

	public $gd_post_types;
	public $period_options;
	public $pending_stats;
	public $navs;
	
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
	}
	
	/**
	 * Include required files
	 *
	 * @access private
	 */
	private function setup_actions() {
	}
	
	/**
	 * Setup constants
	 *
	 * @access private
	 */
	private function setup_constants() {
		$periods = array(
			'this_week' => __( 'This Week', 'geodirectory' ),
			'last_week' => __( 'Last Week', 'geodirectory' ),
			'this_month' => __( 'This Month', 'geodirectory' ),
			'last_month' => __( 'Last Month', 'geodirectory' ),
			'this_year' => __( 'This Year', 'geodirectory' ),
			'last_year' => __( 'Last Year', 'geodirectory' )
		);

		$this->period_options = apply_filters( 'geodir_dashboard_period_options', $periods );
		$this->pending_stats = geodir_dashboard_get_pending_stats();
		$navs = array();
		$navs['all'] = __( 'Total', 'geodirectory' );
		foreach ( $this->gd_post_types as $post_type => $data ) {
			$navs[$post_type] = geodir_post_type_name( $post_type );
		}

		$this->navs = apply_filters( 'geodir_dashboard_navs', $navs );
	}
	
	/**
	 * Handles output of the dashboard page in admin.
	 */
	public function output() {
		do_action( 'geodir_admin_dashboard_before', $this );
		?>
		<div class="wrap gd-dashboard">
			<div class="row gd-dash-row gd-row-head">
				<div class="col-lg-12">
					<h1 class="page-header gd-dash-title"><?php _e( 'Dashboard', 'geodirectory' ); ?> <button type="button" data-handle="gd-toggle-dashboard" class="gd-handlediv"><span class="gd-toggle-indicator"><i class="fas fa-angle-down" aria-hidden="true"></i><i class="fas fa-angle-up" aria-hidden="true"></i></span></button></h1>
				</div>
			</div>
			<div class="gd-toggle-dashboard">
			<?php if ( ! empty( $this->pending_stats ) ) { ?>
			<div class="row gd-dash-row gd-row-actions">
				<div class="col-lg-12">
					<h1 class="gd-dash-title"><?php _e( 'Actions Required', 'geodirectory' ); ?></h1>
					<div class="row gd-pending-items">
			<?php foreach ( $this->pending_stats as $item => $item_data ) { ?>
				<div class="col-lg-4 col-md-6" data-pending-type="<?php echo $item; ?>">
					<div class="panel panel-info gd-collapsed gd-pending-item">
						<div class="panel-heading gd-collapse"><h3 class="panel-title"><span class="badge gd-icon"><i class="fas <?php echo $item_data['icon']; ?>" aria-hidden="true"></i></span> <?php echo $item_data['label']; ?><span class="badge gd-stat-no"><?php echo $item_data['total']; ?></span> <span class="pull-right"><i class="fas fa-angle-down" aria-hidden="true"></i></span></h3></div>
						<div class="gd-collapsable">
							<div class="panel-body text-center">
								<div class="row gd-row-cpt">
									<?php if ( ! empty( $item_data['items'] ) ) { ?>
										<?php foreach ( $item_data['items'] as $cpt => $cpt_data ) { ?>
											<div class="col-xs-12 col-md-6">
												<?php if ( ! empty( $cpt_data['url'] ) ) { ?><a href="<?php echo esc_url( $cpt_data['url'] ); ?>"><?php } ?>
												<div class="panel panel-default">
													<div class="panel-body">
														<span class="gd-stat-lbl"><i class="fas <?php echo $cpt_data['icon']; ?>" aria-hidden="true"></i> <?php echo $cpt_data['label']; ?></span>
														<span class="gd-stat-val"><?php echo $cpt_data['total']; ?></span>
													</div>
												</div>
												<?php if ( ! empty( $cpt_data['url'] ) ) { ?></a><?php } ?>
											</div>
										<?php } ?>
									<?php } ?>
								</div>
							</div>
							<?php if ( ! empty( $item_data['url'] ) ) { ?>
							<div class="panel-footer gd-dash-link-all"><a href="<?php echo esc_url( $item_data['url'] ); ?>"> <?php _e( 'See All', 'geodirectory' ); ?></a></div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php } ?>
					</div>
				</div>
			</div>
			<?php } ?>
			<div class="row gd-dash-row gd-row-stats">
				<div class="col-lg-12">
					<div class="panel panel-default gd-dash-panel gd-panel-stats">
						<input type="hidden" id="gd_stats_type" value="all">
						<div class="panel-heading"><h1 class="gd-dash-title"><?php _e( 'Statistics', 'geodirectory' ); ?></h1></div>
						<div class="panel-body">
							<div class="gd-stats-nav"> 
								<div class="row">
									<div class="col-xs-12 col-sm-8">
										<div class="btn-group">
											<?php foreach ( $this->navs as $nave_id => $nav_label ) { ?>
											<a class="btn btn-primary" data-type="<?php echo $nave_id; ?>" href="javascript:void(0)"><?php echo $nav_label; ?></a>
											<?php } ?>
										</div>
									</div>
									<div class="col-xs-12 col-sm-4 gd-stats-periods">
										<select id="gd_stats_period" class="geodir-select">
											<?php foreach ( $this->period_options as $value => $label ) { ?>
											<option value="<?php echo $value; ?>" <?php selected( $value, 'this_month' ); ?>><?php echo $label; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
							</div>
							<div class="gd-stats-data gd-stats-wait"> 
								<div class="gd-stat-loader" style="display:none;"><i class="fas fa-spinner fa-spin fa-3x fa-fw" aria-hidden="true"></i><span class="sr-only visually-hidden"><?php _e("Loading...","geodirectory");?></span></div>
								<div class="gd-stat-format" style="display:none!important"><div class="col-xs-12 col-sm-6 col-md-4 col-lg-2 gd-stat gd-stat-{stat}"><div class="well text-center"><span class="gd-stat-icon"><i class="{icon}" aria-hidden="true"></i></span><span class="gd-stat-name">{label}</span><span class="gd-stat-no">{value}</span></div></div></div>
								<div class="gd-stats-details">
									<div class="row gd-stats-items">
									</div>
								</div>
								<div class="gd-stats-chart">
									<div class="gd-chart-legends">
									</div>
									<div class="well gd-m0">
										<div id="gd-dashboard-chart"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
		<?php
		do_action( 'geodir_admin_dashboard_after' );
	}
}

}

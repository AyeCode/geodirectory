<?php
/**
 * Admin Dashboard
 *
 * @author      AyeCode
 * @category    Admin
 * @package     GeoDirectory/Admin
 * @version     2.1.0
 * @todo replace this with our GD Dashbaord addon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GeoDir_WP_Dashboard', false ) ) :

/**
 * GeoDir_WP_Dashboard Class.
 */
class GeoDir_WP_Dashboard {

    /**
     * GeoDir_WP_Dashboard constructor.
     *
     * Hook in tabs.
     *
     * @since 20.0
     */
	public function __construct() {
		// Only hook in admin parts if the user has admin access
		if ( current_user_can( 'manage_options' ) && get_option( 'geodirectory_version' ) ) {
			add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
		}
	}

    /**
     * GeoDir_WP_Dashboard Init widgets.
     *
     * @since 2.0.0
     */
	public function init() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget( 'geodir_dashboard_recent_reviews', __( 'GeoDirectory Recent Reviews', 'geodirectory' ), array( $this, 'recent_reviews' ) );
		}
		// wp_add_dashboard_widget( 'geodir_dashboard_status', __( 'GeoDirectory Status', 'geodirectory' ), array( $this, 'status_widget' ) ); // @todo Implement this after all addons converted to v2
	}

    /**
     * Show status widget.
     *
     * @since 2.0.0.
     */
	public function status_widget() {
		// GD dashboard stats
	}

	/**
	 * Recent reviews widget.
     *
     * @since 2.0.0
	 */
	public function recent_reviews() {
		$recent_reviews = $this->recent_reviews_html();

		if ( !$recent_reviews ) {
			echo '<div class="no-activity">';
			echo '<p class="smiley" aria-hidden="true"></p>';
			echo '<p>' . __( 'There is no review yet!', 'geodirectory' ) . '</p>';
			echo '</div>';
		}
	}

    /**
     * Recent review html.
     *
     * @since 2.0.0
     *
     * @param int $total_items Optional. Display review items. Default 5.
     *
     * @return bool
     */
	public function recent_reviews_html( $total_items = 5 ) {
		$design_style = geodir_design_style();
		$reviews = GeoDir_Widget_Recent_Reviews::get_recent_reviews( 50, $total_items, 140 );

		if ( ! empty( $reviews ) ) {
			echo '<div id="gd-latest-reviews" class="activity-block' . ( $design_style ? ' bsui' : '' ) . '">';
				echo '<ul id="gd-review-list" data-wp-lists="list:comment">';
					echo $reviews;
				echo '</ul>';
			echo '</div>';

			if ( $design_style ) {
				echo '<style>#geodir_dashboard_recent_reviews .bsui .card .geodir_reviewer_image{float:left;margin:0 10px 0 -65px;}#geodir_dashboard_recent_reviews .bsui .card{word-wrap:break-word;display:block;margin-top:0;padding:0 0 0 65px;min-width:inherit;max-width:inherit;border:none;background:transparent;box-shadow:none;position: initial;}</style>';
			}
		} else {
			return false;
		}
		return true;
	}
}

endif;

return new GeoDir_WP_Dashboard();

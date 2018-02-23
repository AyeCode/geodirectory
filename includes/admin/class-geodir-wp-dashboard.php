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
	 * Hook in tabs.
	 */
	public function __construct() {
		// Only hook in admin parts if the user has admin access
		if ( current_user_can( 'manage_options' )  ) {
			add_action( 'wp_dashboard_setup', array( $this, 'init' ) );
		}
	}

	/**
	 * Init dashboard widgets.
	 */
	public function init() {
		if ( current_user_can( 'manage_options' ) ) {
			wp_add_dashboard_widget( 'geodir_dashboard_recent_reviews', __( 'GeoDirectory recent reviews', 'geodirectory' ), array( $this, 'recent_reviews' ) );
		}
		wp_add_dashboard_widget( 'geodir_dashboard_status', __( 'GeoDirectory status', 'geodirectory' ), array( $this, 'status_widget' ) );
	}

	/**
	 * Get top seller from DB.
	 * @return object
	 */
	private function get_top_seller() {
		global $wpdb;

		$query            = array();
		$query['fields']  = "SELECT SUM( order_item_meta.meta_value ) as qty, order_item_meta_2.meta_value as product_id
			FROM {$wpdb->posts} as posts";
		$query['join']    = "INNER JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id ";
		$query['join']   .= "INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id ";
		$query['where']   = "WHERE posts.post_type IN ( '" . implode( "','", wc_get_order_types( 'order-count' ) ) . "' ) ";
		$query['where']  .= "AND posts.post_status IN ( 'wc-" . implode( "','wc-", apply_filters( 'geodir_reports_order_statuses', array( 'completed', 'processing', 'on-hold' ) ) ) . "' ) ";
		$query['where']  .= "AND order_item_meta.meta_key = '_qty' ";
		$query['where']  .= "AND order_item_meta_2.meta_key = '_product_id' ";
		$query['where']  .= "AND posts.post_date >= '" . date( 'Y-m-01', current_time( 'timestamp' ) ) . "' ";
		$query['where']  .= "AND posts.post_date <= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) . "' ";
		$query['groupby'] = "GROUP BY product_id";
		$query['orderby'] = "ORDER BY qty DESC";
		$query['limits']  = "LIMIT 1";

		return $wpdb->get_row( implode( ' ', apply_filters( 'geodir_dashboard_status_widget_top_seller_query', $query ) ) );
	}

	/**
	 * Get sales report data.
	 * @return object
	 */
	private function get_sales_report_data() {
		include_once( dirname( __FILE__ ) . '/reports/class-wc-report-sales-by-date.php' );

		$sales_by_date                 = new WC_Report_Sales_By_Date();
		$sales_by_date->start_date     = strtotime( date( 'Y-m-01', current_time( 'timestamp' ) ) );
		$sales_by_date->end_date       = current_time( 'timestamp' );
		$sales_by_date->chart_groupby  = 'day';
		$sales_by_date->group_by_query = 'YEAR(posts.post_date), MONTH(posts.post_date), DAY(posts.post_date)';

		return $sales_by_date->get_report_data();
	}

	/**
	 * Show status widget.
	 */
	public function status_widget() {

		return ;
		include_once( dirname( __FILE__ ) . '/reports/class-wc-admin-report.php' );

		$reports = new GeoDir_Admin_Report();

		echo '<ul class="gd_status_list">';

		if ( current_user_can( 'view_woocommerce_reports' ) && ( $report_data = $this->get_sales_report_data() ) ) {
			?>
			<li class="sales-this-month">
				<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=orders&range=month' ); ?>">
					<?php echo $reports->sales_sparkline( '', max( 7, date( 'd', current_time( 'timestamp' ) ) ) ); ?>
					<?php
						/* translators: %s: net sales */
						printf(
							__( '%s net sales this month', 'geodirectory' ),
							'<strong>' . wc_price( $report_data->net_sales ) . '</strong>'
							);
					?>
				</a>
			</li>
			<?php
		}

		if ( current_user_can( 'view_woocommerce_reports' ) && ( $top_seller = $this->get_top_seller() ) && $top_seller->qty ) {
			?>
			<li class="best-seller-this-month">
				<a href="<?php echo admin_url( 'admin.php?page=wc-reports&tab=orders&report=sales_by_product&range=month&product_ids=' . $top_seller->product_id ); ?>">
					<?php echo $reports->sales_sparkline( $top_seller->product_id, max( 7, date( 'd', current_time( 'timestamp' ) ) ), 'count' ); ?>
					<?php
						/* translators: 1: top seller product title 2: top seller quantity */
						printf(
							__( '%1$s top seller this month (sold %2$d)', 'geodirectory' ),
							'<strong>' . get_the_title( $top_seller->product_id ) . '</strong>',
							$top_seller->qty
						);
					?>
				</a>
			</li>
			<?php
		}

		$this->status_widget_listing_rows();

		do_action( 'geodir_after_dashboard_status_widget', $reports );
		echo '</ul>';
	}

	/**
	 * Show order data is status widget.
	 */
	private function status_widget_listing_rows() {
	}

	/**
	 * Recent reviews widget.
	 */
	public function recent_reviews() {
		global $wpdb;
		$comments = $wpdb->get_results( "
			SELECT posts.ID, posts.post_title, comments.comment_author, comments.comment_ID, SUBSTRING(comments.comment_content,1,100) AS comment_excerpt
			FROM $wpdb->comments comments
			LEFT JOIN $wpdb->posts posts ON (comments.comment_post_ID = posts.ID)
			WHERE comments.comment_approved = '1'
			AND comments.comment_type = ''
			AND posts.post_password = ''
			AND posts.post_type = 'product'
			AND comments.comment_parent = 0
			ORDER BY comments.comment_date_gmt DESC
			LIMIT 5
		" );

		if ( $comments ) {
			echo '<ul>';
			foreach ( $comments as $comment ) {

				echo '<li>';

				echo get_avatar( $comment->comment_author, '32' );

				$rating = intval( get_comment_meta( $comment->comment_ID, 'rating', true ) );

				/* translators: %s: rating */
				echo '<div class="star-rating"><span style="width:' . ( $rating * 20 ) . '%">' . sprintf( __( '%s out of 5', 'geodirectory' ), $rating ) . '</span></div>';

				/* translators: %s: review author */
				echo '<h4 class="meta"><a href="' . get_permalink( $comment->ID ) . '#comment-' . absint( $comment->comment_ID ) . '">' . esc_html( apply_filters( 'geodir_admin_dashboard_recent_reviews', $comment->post_title, $comment ) ) . '</a> ' . sprintf( __( 'reviewed by %s', 'geodirectory' ), esc_html( $comment->comment_author ) ) . '</h4>';
				echo '<blockquote>' . wp_kses_data( $comment->comment_excerpt ) . ' [...]</blockquote></li>';

			}
			echo '</ul>';
		} else {
			echo '<p>' . __( 'There are no product reviews yet.', 'geodirectory' ) . '</p>';
		}
	}
}

endif;

return new GeoDir_WP_Dashboard();

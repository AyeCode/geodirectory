<?php
/**
 * GeoDir_Admin_Report_Post_List_Table class
 *
 * @author   AyeCode
 * @package  GeoDirectory
 * @since    2.1.1.12
 */

/**
 * Core class used to implement displaying report posts in a list table.
 *
 *
 * @see WP_List_Table
 */
class GeoDir_Admin_Report_Post_List_Table extends WP_List_Table {

	public $checkbox = true;

	private $user_can;

	/**
	 * Constructor.
	 *
	 * @global int $post_id
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		global $post_id;

		$post_id = isset( $_REQUEST['p'] ) ? absint( $_REQUEST['p'] ) : 0;

		parent::__construct(
			array(
				'plural'   => 'report-posts',
				'singular' => 'report-post',
				'ajax'     => true,
			)
		);
	}

	/**
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @global int $post_id
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = GeoDir_Report_Post::get_admin_columns();

		return $columns;
	}

	/**
	 * Returns the list of columns.
	 *
	 * @access protected
	 *
	 * @return array An associative array containing all the columns that should be sortable.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'post_title'  => array( 'post_title', false ),
			'post_author' => array( 'post_author', false ),
			'post_date'   => array( 'post_date', true ),
			'user_name'   => array( 'user_name', false ),
			'report_date' => array( 'report_date', true ),
		);
		return $sortable_columns;
	}

	/**
	 * @global int    $post_id
	 * @global string $status
	 * @global string $reason
	 * @global string $search
	 */
	public function prepare_items() {
		global $wpdb, $post_id, $status, $reason, $search;

		$table = GEODIR_POST_REPORTS_TABLE;

		$nonce = ! empty( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( $_REQUEST['_wpnonce'] ) : '';

		if ( ! empty( $nonce ) && ! check_admin_referer( 'bulk-' . $this->_args['plural'] ) ) {
			wp_die();
		}

			$status = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'pending';
		if ( ! in_array( $status, array( 'all', 'resolved', 'pending', 'rejected' ), true ) ) {
			$status = 'pending';
		}

		$allowed_orderby = array(
			'post_title',
			'post_author',
			'post_date',
			'user_name',
			'report_date',
		);

		$reason  = ! empty( $_REQUEST['reason'] ) ? sanitize_text_field( $_REQUEST['reason'] ) : '';
		$search  = ( isset( $_REQUEST['s'] ) ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$user_id = ( isset( $_REQUEST['user_id'] ) ) ? absint( $_REQUEST['user_id'] ) : '';
		$orderby = ( isset( $_REQUEST['orderby'] ) && in_array( $_REQUEST['orderby'], $allowed_orderby, true ) ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'report_date';
		$order   = ( isset( $_REQUEST['order'] ) && 'ASC' === $_REQUEST['order'] ) ? 'ASC' : 'DESC';

		$reports_per_page = $this->get_per_page( $status );

		$doing_ajax = wp_doing_ajax();

		$number = ! empty( $_REQUEST['number'] ) ? absint( $_REQUEST['number'] ) : $reports_per_page;
		$page   = $this->get_pagenum();

		if ( isset( $_REQUEST['start'] ) ) {
			$start = absint( $_REQUEST['start'] );
		} else {
			$start = ( $page - 1 ) * $reports_per_page;
		}

		if ( $doing_ajax && isset( $_REQUEST['offset'] ) ) {
			$start += absint( $_REQUEST['offset'] );
		}

		$args = array(
			'status'  => $status,
			'search'  => $search,
			'user_id' => $user_id,
			'offset'  => $start,
			'number'  => $number,
			'post_id' => $post_id,
			'type'    => $reason,
			'orderby' => $orderby,
			'order'   => $order,
			'reason'  => $reason,
		);

		/**
		 * Filters the arguments for the report query in the reports list table.
		 *
		 * @since 2.1.1.12
		 *
		 * @param array $args An array of arguments.
		 */
		$args = apply_filters( 'geodir_admin_post_reports_list_table_query_args', $args );

		$where = '';
		// post_id
		if ( ! empty( $args['post_id'] ) ) {
			$where .= $wpdb->prepare( ' AND r.post_id = %d', array( absint( $args['post_id'] ) ) );
		}

		// status
		if ( ! empty( $args['status'] ) && 'all' !== $args['status'] ) {
			if ( 'pending' === $args['status'] ) {
				$args['status'] = '';
			}
			$where .= $wpdb->prepare( ' AND r.status = %s', array( $args['status'] ) );
		}

		// reason
		if ( ! empty( $args['reason'] ) ) {
			$where .= $wpdb->prepare( ' AND r.reason LIKE %s', array( $args['reason'] ) );
		}

		if ( ! empty( $args['search'] ) ) {
			if ( is_email( $args['search'] ) ) {
				$where .= $wpdb->prepare( ' AND r.user_email LIKE %s', array( $args['search'] ) );
			} else {
				$keyword = '%' . $wpdb->esc_like( $args['search'] ) . '%';
				$where  .= $wpdb->prepare( ' AND ( r.user_ip = %s OR r.user_name LIKE %s OR p.post_title LIKE %s OR r.user_email LIKE %s OR r.message LIKE %s )', array( $args['search'], $keyword, $keyword, $keyword, $keyword ) );
			}
		}

		$commmon_sql = "FROM {$table} AS r LEFT JOIN {$wpdb->posts} AS p ON p.ID = r.post_id WHERE p.ID IS NOT NULL {$where}";

		$total_items = $wpdb->get_var( "SELECT COUNT(*) {$commmon_sql}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $common_sql uses prepare above.


		$_orderby = ! empty( $orderby ) ? "ORDER BY {$orderby} {$order}" : '';
		$_orderby = sanitize_sql_orderby( $_orderby ) ? sanitize_sql_orderby( $_orderby ) : 'ORDER BY report_date DESC';

		$_limit = $wpdb->prepare( 'LIMIT %d, %d', array( $args['offset'], $args['number'] ) );

		$this->items = $wpdb->get_results( "SELECT p.post_title, p.post_author, p.post_status, p.post_date, r.* {$commmon_sql} {$_orderby} {$_limit}" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- All items secure.

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $reports_per_page,
			)
		);
	}

	/**
	 * Handles bulk actions.
	 *
	 * @see $this->prepare_items()
	 */
	public function process_bulk_action() {
		global $wpdb;

		$doaction = $this->current_action();

		if ( ! $doaction || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'bulk-' . $this->_args['plural'] );

		$report_posts = isset( $_REQUEST['report_posts'] ) ? wp_parse_id_list( wp_unslash( $_REQUEST['report_posts'] ) ) : array();

		if ( empty( $report_posts ) ) {
			return;
		}

		$resolved = 0;
		$rejected = 0;
		$deleted_ = 0;
		$pending  = 0;
		$draft    = 0;
		$trash    = 0;
		$deleted  = 0;

		$pagenum = $this->get_pagenum();

		$redirect_to = remove_query_arg( array( 'resolved', 'rejected', 'deleted_', 'deleted', 'pending', 'draft', 'trash', 'report_posts' ), wp_get_referer() );
		$redirect_to = add_query_arg( 'paged', $pagenum, $redirect_to );

		foreach ( $report_posts as $id ) {
			switch ( $doaction ) {
				case 'resolved':
					$resolved += (int) GeoDir_Report_Post::set_status( 'resolved', (int) $id );
					break;
				case 'rejected':
					$rejected += (int) GeoDir_Report_Post::set_status( 'rejected', (int) $id );
					break;
				case 'delete-reports':
					$deleted_ += (int) GeoDir_Report_Post::delete( (int) $id );
					break;
				case 'pending':
					$pending += (int) GeoDir_Report_Post::set_post_status( 'pending', (int) $id );
					break;
				case 'draft':
					$draft += (int) GeoDir_Report_Post::set_post_status( 'draft', (int) $id );
					break;
				case 'trash':
					$trash += (int) GeoDir_Report_Post::delete_post( (int) $id );
					break;
				case 'delete':
					$deleted += (int) GeoDir_Report_Post::delete_post( (int) $id, true );
					break;
			}
		}

		if ( $resolved ) {
			$redirect_to = add_query_arg( 'resolved', $resolved, $redirect_to );
		}
		if ( $rejected ) {
			$redirect_to = add_query_arg( 'rejected', $rejected, $redirect_to );
		}
		if ( $deleted_ ) {
			$redirect_to = add_query_arg( 'deleted_', $deleted_, $redirect_to );
		}
		if ( $pending ) {
			$redirect_to = add_query_arg( 'pending', $pending, $redirect_to );
		}
		if ( $deleted ) {
			$redirect_to = add_query_arg( 'deleted', $deleted, $redirect_to );
		}
		if ( $draft ) {
			$redirect_to = add_query_arg( 'draft', $draft, $redirect_to );
		}
		if ( $trash ) {
			$redirect_to = add_query_arg( 'trash', $trash, $redirect_to );
		}

		wp_safe_redirect( $redirect_to );
		exit;
	}

	/**
	 * @param string $status
	 * @return int
	 */
	public function get_per_page( $status = 'all' ) {
		$reports_per_page = 10;

		/**
		 * Filters the number of reports listed per page in the reports list table.
		 *
		 * @since 2.1.1.12
		 *
		 * @param int    $reports_per_page The number of reports to list per page.
		 * @param string $status    The report status name. Default 'All'.
		 */
		return apply_filters( 'geodir_admin_post_reports_per_page', $reports_per_page, $status );
	}

	/**
	 * @global string $status
	 */
	public function no_items() {
		global $status;

		if ( 'pending' === $status ) {
			_e( 'No items awaiting moderation.', 'geodirectory' );
		} else {
			_e( 'No items found.', 'geodirectory' );
		}
	}

	/**
	 * @global int $post_id
	 * @global string $status
	 * @global string $reason
	 */
	protected function get_views() {
		global $post_id, $status, $reason;

		$status_links = array();
		$post_id      = ! empty( $post_id ) ? absint( $post_id ) : 0;
		$num_reports  = GeoDir_Report_Post::get_counts( $post_id );
		if ( empty( $status ) ) {
			$status = 'all';
		}

		$stati = array(
			'all'      => _nx_noop(
				'All <span class="count">(%s)</span>',
				'All <span class="count">(%s)</span>',
				'reported posts',
				'geodirectory'
			),
			'pending'  => _nx_noop(
				'Pending <span class="count">(%s)</span>',
				'Pending <span class="count">(%s)</span>',
				'reported posts',
				'geodirectory'
			),
			'resolved' => _nx_noop(
				'Resolved <span class="count">(%s)</span>',
				'Resolved <span class="count">(%s)</span>',
				'reported posts',
				'geodirectory'
			),
			'rejected' => _nx_noop(
				'Rejected <span class="count">(%s)</span>',
				'Rejected <span class="count">(%s)</span>',
				'reported posts',
				'geodirectory'
			),
		);

		$link = admin_url( 'admin.php?page=gd-settings&tab=general&section=report_post' );

		if ( ! empty( $reason ) && 'all' !== $reason ) {
			$link = add_query_arg( 'reason', $reason, $link );
		}

		foreach ( $stati as $_status => $label ) {
			$current_link_attributes = '';

			if ( empty( $_status ) ) {
				$_status = 'pending';
			}

			if ( $_status === $status ) {
				$current_link_attributes = ' class="current" aria-current="page"';
			}

			if ( ! isset( $num_reports->{$_status} ) ) {
				$num_reports->{$_status} = 10;
			}

			if ( empty( $num_reports->{$_status} ) ) {
				continue;
			}

			$link = add_query_arg( 'status', $_status, $link );

			if ( $post_id ) {
				$link = add_query_arg( 'p', absint( $post_id ), $link );
			}

			$status_links[ $_status ] = "<a href='$link'$current_link_attributes>" . wp_sprintf(
				translate_nooped_plural( $label, $num_reports->{$_status} ),
				wp_sprintf( '<span class="%s-count">%s</span>', 'pending', number_format_i18n( $num_reports->{$_status} ) )
			) . '</a>';
		}

		return apply_filters( 'geodir_report_post_status_links', $status_links );
	}

	/**
	 * @global string $status
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		global $status;

		$actions = array(
			'pending'        => __( 'Unpublish', 'geodirectory' ),
			'draft'          => __( 'Move to Draft', 'geodirectory' ),
			'trash'          => __( 'Move to Trash', 'geodirectory' ),
			'delete'         => __( 'Delete Permanently', 'geodirectory' ),
			'resolved'       => __( 'Mark as Resolved', 'geodirectory' ),
			'rejected'       => __( 'Mark as Rejected', 'geodirectory' ),
			'delete-reports' => __( 'Delete Reports', 'geodirectory' ),
		);

		return $actions;
	}

	/**
	 * @global string $status
	 * @global string $reason
	 *
	 * @param string $which
	 */
	protected function extra_tablenav( $which ) {
		global $status, $reason;
		static $has_items;

		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();
		}

		echo '<div class="alignleft actions">';

		if ( 'top' === $which ) {
			ob_start();

			$this->reason_dropdown( $reason );

			/**
			 * Fires just before the Filter submit button for reasons.
			 *
			 * @since 2.1.1.12
			 */
			do_action( 'geodir_admin_restrict_manage_report_posts' );

			$output = ob_get_clean();

			if ( ! empty( $output ) && $this->has_items() ) {
				echo $output;
				submit_button( __( 'Filter', 'geodirectory' ), '', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			}
		}

		/**
		 * Fires after the Filter submit button for reasons.
		 *
		 * @since 2.1.1.12
		 *
		 * @param string $status The reason name. Default 'All'.
		 * @param string $which          The location of the extra table nav markup: 'top' or 'bottom'.
		 */
		do_action( 'geodir_admin_manage_report_posts_nav', $status, $which );

		echo '</div>';
	}

	/**
	 * @return string|false
	 */
	public function current_action() {
		return parent::current_action();
	}

	/**
	 * Displays a reason drop-down for filtering on the reports list table.
	 *
	 * @since 2.1.1.12
	 *
	 * @param string $reason The current reason slug.
	 */
	protected function reason_dropdown( $reason ) {
		$reasons = GeoDir_Report_Post::get_reasons();

		if ( $reasons && is_array( $reasons ) ) {
			printf( '<label class="screen-reader-text" for="filter-by-reason">%s</label>', __( 'Filter by reason', 'geodirectory', 'geodirectory' ) );

			echo '<select id="filter-by-reason" name="reason">';

			printf( "\t<option value=''>%s</option>", __( 'All reasons', 'geodirectory' ) );

			foreach ( $reasons as $type => $label ) {
				printf(
					"\t<option value='%s'%s>%s</option>\n",
					esc_attr( $type ),
					selected( $reason, $type, false ),
					esc_html( $label )
				);
			}

			echo '</select>';
		}
	}

	/**
	 * Get the name of the default primary column.
	 *
	 *
	 * @return string Name of the default primary column, in this case, 'post'.
	 */
	protected function get_default_primary_column_name() {
		return 'report-post';
	}

	/**
	 * Displays the comments table.
	 *
	 * Overrides the parent display() method to render extra comments.
	 *
	 * @since 3.1.0
	 */
	public function display() {
		static $has_items;

		if ( ! isset( $has_items ) ) {
			$has_items = $this->has_items();

			if ( $has_items ) {
				$this->display_tablenav( 'top' );

				wp_nonce_field( 'bulk-' . $this->_args['plural'] );
			}
		}

		$this->screen->render_screen_reader_content( 'heading_list' );

		?>
<input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr( remove_query_arg( '_wp_http_referer', wp_unslash( $_SERVER['REQUEST_URI'] ) ) ); ?>" />
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
	<thead>
	<tr>
		<?php $this->print_column_headers(); ?>
	</tr>
	</thead>

	<tbody id="the-report-post-list" data-wp-lists="list:report-post">
		<?php $this->display_rows_or_placeholder(); ?>
	</tbody>

	<tbody id="the-extra-report-post-list" data-wp-lists="list:report-post" style="display: none;">
		<?php
			$items = $this->items;
			$this->display_rows_or_placeholder();
			$this->items = $items;
		?>
	</tbody>

	<tfoot>
	<tr>
		<?php $this->print_column_headers( false ); ?>
	</tr>
	</tfoot>

</table>
		<?php

		$this->display_tablenav( 'bottom' );
	}

	/**
	 * @global WP_Post $post    Global post object.
	 * @global object  $report_item Global report item object.
	 *
	 * @param WP_Comment $item
	 */
	public function single_row( $item ) {
		global $post, $report_item;

		$report_item = $item;

		$the_report_class = $report_item->status;

		if ( empty( $the_report_class ) ) {
			$the_report_class = 'pending';
		}

		$the_report_class = implode( ' ', $this->get_item_class( $the_report_class, $report_item, $report_item->post_id ) );

		if ( $report_item->post_id > 0 ) {
			$post = get_post( $report_item->post_id );
		}

		$this->user_can = current_user_can( 'manage_options' );

		echo "<tr id='report-post-$report_item->id' class='$the_report_class'>";
		$this->single_row_columns( $report_item );
		echo "</tr>\n";

		unset( $GLOBALS['post'], $GLOBALS['report_item'] );
	}

	public function get_item_class( $class = '', $item = null, $post_id = null ) {
		global $report_post_alt;

		$classes = array();

		if ( empty( $report_post_alt ) ) {
			$report_post_alt = 0;
		}

		if ( $report_post_alt % 2 ) {
			$classes[] = 'odd';
			$classes[] = 'alt';
		} else {
			$classes[] = 'even';
		}

		$report_post_alt++;

		if ( ! empty( $class ) ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_merge( $classes, $class );
		}

		$classes = array_map( 'esc_attr', $classes );

		/**
		 * Filters the returned CSS classes for the current report item.
		 *
		 * @since 2.1.1.12
		 *
		 * @param string[]    $classes An array of report item classes.
		 * @param string[]    $class An array of additional classes added to the list.
		 * @param int         $item_id The report ID.
		 * @param object      $item The report item object.
		 * @param int|WP_Post $post_id The post ID or WP_Post object.
		 */
		return apply_filters( 'geodir_admin_report_post_class', $classes, $class, $item->id, $item, $post_id );
	}

	/**
	 * Generate and display row actions links.
	 *
	 * @since 2.1.1.12
	 *
	 * @global string $status Status for the current listed reports.
	 *
	 * @param object     $item     The item object.
	 * @param string     $column_name Current column name.
	 * @param string     $primary     Primary column name.
	 * @return string Row actions output for comments. An empty string
	 *                if the current column is not the primary column,
	 *                or if the current user cannot edit the item.
	 */
	protected function handle_row_actions( $item, $column_name, $primary ) {
		global $status;

		if ( $primary !== $column_name ) {
			return '';
		}

		if ( ! $this->user_can ) {
			return '';
		}

		$post = get_post( (int) $item->post_id );

		$out = '';

		$actions = array(
			'id'   => '',
			'view' => '',
		);

		// Not looking at all comments.
		if ( $post->post_status != 'trash' ) {
			$actions['id']   = wp_sprintf( __( 'ID: %s', 'geodirectory' ), $post->ID );
			$actions['view'] = wp_sprintf( '<a href="%s" rel="bookmark" aria-label="%s">%s</a>', get_permalink( $post->ID ), esc_attr( wp_sprintf( __( 'View &#8220;%s&#8221;', 'geodirectory' ), _draft_or_post_title( (int) $post->ID ) ) ), wp_sprintf( __( 'View %s', 'geodirectory' ), geodir_post_type_singular_name( $post->post_type, true ) ) );
		}

		$actions = apply_filters( 'geodir_report_post_row_actions', array_filter( $actions ), $item );

		$out .= '<div class="row-actions">';

		$i = 0;

		foreach ( $actions as $action => $link ) {
			++$i;

			if ( 1 === $i ) {
				$sep = '';
			} else {
				$sep = ' | ';
			}

			$out .= "<span class='$action'>$sep$link</span>";
		}

		$out .= '</div>';

		$out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details', 'geodirectory' ) . '</span></button>';

		return $out;
	}

	/**
	 * @param WP_Comment $comment The item object.
	 */
	public function column_cb( $item ) {
		if ( $this->user_can ) {
			?>
		<label class="screen-reader-text" for="cb-select-<?php echo $item->id; ?>"><?php _e( 'Select item' ); ?></label>
		<input id="cb-select-<?php echo $item->id; ?>" type="checkbox" name="report_posts[]" value="<?php echo $item->id; ?>" />
			<?php
		}
	}

	public function column_post_title( $item ) {
		$title         = _draft_or_post_title( (int) $item->post_id );
		$post          = get_post( (int) $item->post_id );
		$can_edit_post = current_user_can( 'edit_post', $post->ID );

		$value = '<strong>';

		if ( $can_edit_post && 'trash' !== $post->post_status ) {
			$value .= wp_sprintf( '<a class="row-title" href="%s" aria-label="%s">%s</a>', get_edit_post_link( $post->ID ), esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)', 'geodirectory' ), $title ) ), $title );
		} else {
			$value .= wp_sprintf( '<span>%s</span>', $title );
		}

		$value .= "</strong>\n";

		return $value;
	}

	public function column_post_author( $item ) {
		$post = get_post( (int) $item->post_id );

		$label = get_the_author_meta( 'display_name', $post->post_author );

		if ( current_user_can( 'edit_user', $post->post_author ) ) {
			$edit_link = get_edit_user_link( $post->post_author );
			$value     = "<strong><a href=\"{$edit_link}\">{$label}</a></strong>";
		} else {
			$value = "<strong>{$label}</strong>";
		}

		return $value;
	}

	public function column_post_date( $item ) {
		$post = get_post( (int) $item->post_id );

		$value = '';

		if ( '0000-00-00 00:00:00' === $post->post_date ) {
			$t_time    = __( 'Unpublished', 'geodirectory' );
			$time_diff = 0;
		} else {
			$t_time = date_i18n( geodir_date_time_format(), strtotime( $post->post_date ) );

			$time      = get_post_timestamp( $post );
			$time_diff = time() - $time;
		}

		if ( 'publish' === $post->post_status ) {
			$status = __( 'Published', 'geodirectory' );
		} elseif ( 'future' === $post->post_status ) {
			if ( $time_diff > 0 ) {
				$status = '<strong class="error-message">' . __( 'Missed schedule', 'geodirectory' ) . '</strong>';
			} else {
				$status = __( 'Scheduled', 'geodirectory' );
			}
		} else {
			$status = geodir_get_post_status_name( $post->post_status );
		}

		if ( $status ) {
			$value .= $status . '<br />';
		}

		$value .= $t_time;

		return $value;
	}

	public function column_user_name( $item ) {
		$label = esc_html( wp_unslash( $item->user_name ) );

		if ( ! empty( $item->user_id ) && current_user_can( 'edit_user', (int) $item->user_id ) ) {
			$edit_link = get_edit_user_link( (int) $item->user_id );
			$value     = "<strong><a href=\"{$edit_link}\">{$label}</a></strong><br>";
		} else {
			$value = "<strong>{$label}</strong><br>";
		}
		$value .= $item->user_email;

		if ( ! empty( $item->user_email ) ) {
			$value .= '<br>' . $item->user_ip;
		}

		return $value;
	}

	public function column_report_date( $item ) {
		$post = get_post( (int) $item->post_id );

		$value = '';

		$t_time = date_i18n( geodir_date_time_format(), strtotime( $item->report_date ) );

		if ( 'resolved' === $item->status ) {
			$status = __( 'Resolved', 'geodirectory' );
		} elseif ( 'rejected' === $item->status ) {
			$status = __( 'Rejected', 'geodirectory' );
		} else {
			$status = __( 'Pending', 'geodirectory' );
		}

		if ( $status ) {
			$value .= $status . '<br />';
		}

		$value .= $t_time;

		return $value;
	}

	public function column_reports( $item ) {
		$post = get_post( (int) $item->post_id );

		$value = __( esc_html( $item->reason ), 'geodirectory' );
		if ( ! empty( $item->message ) ) {
			$value .= '<br><a href="javascipt:void(0)" class="geodir-report-view">' . __( 'View Message', 'geodirectory' ) . '</a><div style="display:none"><div class="lity-show" id="geodir-view-report-post-' . $item->id . '" style="white-space:pre-wrap;"><strong class="gd-settings-title ">' . __( 'Message:', 'geodirectory' ) . '</strong><br><hr>' . esc_html( $item->message ) . '</div></div>';
		}

		return $value;
	}

	/**
	 * @param object $item     The item object.
	 * @param string $column_name The custom column's name.
	 */
	public function column_default( $item, $column_name ) {
		/**
		 * Fires when the default column output is displayed for a single row.
		 *
		 * @since 2.1.1.12
		 *
		 * @param string $column_name The custom column's name.
		 * @param int    $comment_id  The custom column's unique ID number.
		 */
		do_action( 'geodir_admin_manage_report_posts_custom_column', $column_name, $item );
	}
}

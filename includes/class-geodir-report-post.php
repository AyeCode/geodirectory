<?php
/**
 * GeoDir_Report_Post class
 *
 * @package GeoDirectory
 * @since   2.1.1.12
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * GeoDir_Report_Post class.
 */
class GeoDir_Report_Post {

	/**
	 * Setup.
	 */
	public static function init() {
		if ( is_admin() ) {
			add_filter( 'geodir_admin_email_settings', array( __CLASS__, 'filter_admin_email_settings' ), 1, 1 );

			add_action( 'geodir_settings_general', array( __CLASS__, 'setup_admin_page' ), 1, 1 );
			add_filter( 'geodir_get_sections_general', array( __CLASS__, 'add_admin_section' ), 10, 1 );
			add_filter( 'geodir_general_default_options', array( __CLASS__, 'add_admin_setting' ), 10, 2 );
			add_action( 'geodir_admin_field_list_reported_posts', array( __CLASS__, 'output_list_table' ), 10, 1 );

			if ( ! empty( $_REQUEST['section'] ) && $_REQUEST['section'] == 'report_post' ) {
				add_filter( 'manage_geodirectory_page_gd-settings_columns', array( __CLASS__, 'get_admin_columns' ), 20, 1 );
			}
		}

		add_action( 'geodir_report_post_created', array( __CLASS__, 'on_report_post_created' ), 10, 3 );
	}

	public static function get_admin_columns( $columns = array() ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'post_title' => __( 'Post Title', 'geodirectory' ),
			'post_author' => __( 'Post Author', 'geodirectory' ),
			'post_date' => __( 'Post Date', 'geodirectory' ),
			'user_name' => __( 'Reported By', 'geodirectory' ),
			'report_date' => __( 'Reported On', 'geodirectory' ),
			'reports' => __( 'Reports', 'geodirectory' ),
		);

		return $columns;
	}

	public static function setup_admin_page() {
		global $current_section, $hide_save_button, $hide_advanced_toggle;

		if ( $current_section == 'report_post' ) {
			$hide_advanced_toggle = true;
			$hide_save_button = true;
		}
	}

	public static function add_admin_section( $sections ) {
		$offset = array_search( 'pages', array_keys( $sections ) );
		$offset = $offset === false ? 2 : $offset + 1;
		$num_reports = GeoDir_Report_Post::get_counts();
		if ( ! empty( $num_reports->pending ) ) {
			$pending_count = ' <span class="awaiting-mod count-' . (int) $num_reports->pending . '"><span class="pending-count" aria-hidden="true">' . (int) $num_reports->pending . '</span><span class="screen-reader-text">' . wp_sprintf( __( '%d reported posts awaiting moderation', 'geodirectory' ), (int) $num_reports->pending ) . '</span></span>';
		} else {
			$pending_count = '';
		}
		$push_sections = array( 'report_post' => __( 'Reported Posts', 'geodirectory' ) . $pending_count );

		$sections = array_merge( array_slice( $sections, 0, $offset ), $push_sections, array_slice( $sections, $offset ) );

		return $sections;
	}

	public static function add_admin_setting( $settings, $current_section ) {
		if ( $current_section == 'report_post' && isset( $_REQUEST['section'] ) && $_REQUEST['section'] == 'report_post' ) {
			if ( ! empty( $post_id ) ) {
				$title = wp_sprintf( __( 'Reports for "%s"', 'geodirectory' ), _draft_or_post_title( $post_id ) );
			} else {
				$title = __( 'Reported Posts', 'geodirectory' );
			}

			$settings = apply_filters( 'geodir_admin_report_post_settings',
				array(
					array(
						'type'  => 'page-title',
						'id'    => 'report_post',
						'title' => $title,
						'desc'  => ''
					),
					array(
						'name' => '',
						'desc' => '',
						'id' => 'list_reported_posts',
						'type' => 'list_reported_posts',
						'css' => 'min-width:300px;',
						'std' => '40'
					)
				)
			);
		}

		return $settings;
	}

	public static function output_list_table( $setting ) {
		global $post_id, $status, $reason;

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				'<h1>' . __( 'You need a higher level of permission.', 'geodirectory' ) . '</h1>' .
				'<p>' . __( 'Sorry, you are not allowed to manage post reports.', 'geodirectory' ) . '</p>',
				403
			);
		}
		//echo '<tr style="display:none"><td></td></table>';
		echo '<tr style="display:none"><td></td></table></form><form method="get" enctype="multipart/form-data" action="' . admin_url( 'admin.php' ) . '">';

		$args = array();

		$wp_list_table = new GeoDir_Admin_Report_Post_List_Table( $args );
		$pagenum = $wp_list_table->get_pagenum();

		// Handle bulk actions.
		$wp_list_table->process_bulk_action();

		$wp_list_table->prepare_items();

		add_screen_option( 'per_page' );
		?>
		<div class="wrap">
<?php
if ( isset( $_REQUEST['resolved'] ) || isset( $_REQUEST['rejected'] ) || isset( $_REQUEST['deleted_'] ) || isset( $_REQUEST['deleted'] ) || isset( $_REQUEST['pending'] ) || isset( $_REQUEST['draft'] ) || isset( $_REQUEST['trash'] ) ) {
	$resolved  = isset( $_REQUEST['resolved'] ) ? (int) $_REQUEST['resolved'] : 0;
	$rejected   = isset( $_REQUEST['rejected'] ) ? (int) $_REQUEST['rejected'] : 0;
	$deleted_   = isset( $_REQUEST['deleted_'] ) ? (int) $_REQUEST['deleted_'] : 0;
	$deleted   = isset( $_REQUEST['deleted'] ) ? (int) $_REQUEST['deleted'] : 0;
	$pending   = isset( $_REQUEST['pending'] ) ? (int) $_REQUEST['pending'] : 0;
	$draft   = isset( $_REQUEST['draft'] ) ? (int) $_REQUEST['draft'] : 0;
	$trash   = isset( $_REQUEST['trash'] ) ? (int) $_REQUEST['trash'] : 0;

	if ( $resolved > 0 || $rejected > 0 || $deleted_ > 0 || $deleted > 0 || $pending > 0 || $draft > 0 || $trash > 0 ) {
		if ( $resolved > 0 ) {
			$messages[] = sprintf( _n( '%s item marked as resolved.', '%s items marked as resolved.', $resolved ), $resolved );
		}

		if ( $rejected > 0 ) {
			$messages[] = sprintf( _n( '%s item marked as rejected.', '%s items marked as rejected.', $rejected ), $rejected );
		}

		if ( $deleted_ > 0 ) {
			$messages[] = sprintf( _n( '%s item deleted.', '%s items deleted.', $deleted_ ), $deleted_ );
		}

		if ( $deleted > 0 ) {
			$messages[] = sprintf( _n( '%s post deleted permanently.', '%s posts deleted permanently.', $deleted ), $deleted );
		}

		if ( $pending > 0 ) {
			$messages[] = sprintf( _n( '%s post unpublished.', '%s posts unpublished.', $pending ), $pending );
		}

		if ( $draft > 0 ) {
			$messages[] = sprintf( _n( '%s post moved to draft.', '%s posts moved to draft.', $draft ), $draft );
		}

		if ( $trash > 0 ) {
			$messages[] = sprintf( _n( '%s post moved to trash.', '%s posts moved to trash.', $trash ), $trash );
		}

		echo '<div id="moderated" class="updated notice is-dismissible"><p>' . implode( "<br/>\n", $messages ) . '</p></div>';
	}
}
?>
			<input type="hidden" name="page" value="gd-settings" />
			<input type="hidden" name="tab" value="general" />
			<input type="hidden" name="section" value="report_post" />
		<?php $wp_list_table->views(); ?>

		<?php $wp_list_table->search_box( __( 'Search Items', 'geodirectory' ), 'report_post' ); ?>

		<?php if ( ! empty( $post_id ) ) { ?>
		<input type="hidden" name="p" value="<?php echo esc_attr( (int) $post_id ); ?>" />
		<?php } ?>
		<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>" />
		<input type="hidden" name="_total" value="<?php echo esc_attr( $wp_list_table->get_pagination_arg( 'total_items' ) ); ?>" />
		<input type="hidden" name="_per_page" value="<?php echo esc_attr( $wp_list_table->get_pagination_arg( 'per_page' ) ); ?>" />
		<input type="hidden" name="_page" value="<?php echo esc_attr( $wp_list_table->get_pagination_arg( 'page' ) ); ?>" />

		<?php if ( isset( $_REQUEST['paged'] ) ) { ?>
			<input type="hidden" name="paged" value="<?php echo esc_attr( absint( $_REQUEST['paged'] ) ); ?>" />
		<?php } ?>

		<?php $wp_list_table->display(); ?>
		</div>

		<div id="ajax-response"></div></form><form>
	<?php
	}

	public static function get_reasons() {
		$reasons = array(
			__( 'Copyright Issue', 'geodirectory' ),
			__( 'Harassment', 'geodirectory' ),
			__( 'Inappropriate', 'geodirectory' ),
			__( 'Incorrect Details', 'geodirectory' ),
			__( 'Offensive or Hateful', 'geodirectory' ),
			__( 'Privacy Concern', 'geodirectory' ),
			__( 'Spam', 'geodirectory' ),
			__( 'Violence', 'geodirectory' ),
			__( 'Other', 'geodirectory' )
		);

		$reasons = apply_filters( 'geodir_report_post_reasons', $reasons );

		return array_combine( $reasons, $reasons );
	}

	public static function filter_admin_email_settings( $settings ) {
		if ( $merge_settings = self::admin_email_settings() ) {
			$position = count( $settings );
			$settings = array_merge( array_slice( $settings, 0, $position ), $merge_settings, array_slice( $settings, $position ) );
		}

		return $settings;
	}

	public static function admin_email_settings() {
		$settings = array(
			array(
				'type' => 'title',
				'id' => 'email_admin_report_post_settings',
				'name' => __( 'Post reported by user', 'geodirectory' ),
				'desc' => '',
			),
			array(
				'type' => 'checkbox',
				'id' => 'email_admin_report_post',
				'name' => __( 'Enable email', 'geodirectory' ),
				'desc' => __( 'Send an email to admin after post reported by user.', 'geodirectory' ),
				'default' => 1,
			),
			array(
				'type' => 'text',
				'id' => 'email_admin_report_post_subject',
				'name' => __( 'Subject', 'geodirectory' ),
				'desc' => __( 'The email subject.', 'geodirectory' ),
				'class' => 'active-placeholder',
				'desc_tip' => true,
				'placeholder' => GeoDir_Defaults::email_admin_report_post_subject(),
				'advanced' => true
			),
			array(
				'type' => 'textarea',
				'id' => 'email_admin_report_post_body',
				'name' => __( 'Body', 'geodirectory' ),
				'desc' => __( 'The email body, this can be text or HTML.', 'geodirectory' ),
				'class' => 'code gd-email-body',
				'desc_tip' => true,
				'advanced' => true,
				'placeholder' => GeoDir_Defaults::email_admin_report_post_body(),
				'custom_desc' => __( 'Available template tags:', 'geodirectory' ) . ' ' . self::admin_report_post_email_tags()
			),
			array(
				'type' => 'sectionend',
				'id' => 'email_admin_report_post_settings'
			)
		);

		return $settings;
	}

	public static function admin_report_post_email_tags( $inline = true ) {
		$tags = array( '[#blogname#]', '[#site_name#]', '[#site_url#]', '[#site_name_url#]', '[#login_url#]', '[#login_link#]', '[#date#]', '[#time#]', '[#date_time#]', '[#current_date#]', '[#to_name#]', '[#to_email#]', '[#from_name#]', '[#from_email#]', '[#post_id#]', '[#post_status#]', '[#post_date#]', '[#post_author_ID#]', '[#post_author_name#]', '[#client_name#]', '[#listing_title#]', '[#listing_url#]', '[#listing_link#]', '[#report_id#]', '[#report_post_user_name#]', '[#report_post_user_email#]', '[#report_post_user_ip#]', '[#report_post_date#]', '[#report_post_reason#]', '[#report_post_message#]', '[#report_post_section_link#]' );

		$tags = apply_filters( 'geodir_email_admin_report_post_email_tags', $tags );

		if ( $inline ) {
			$tags = '<code>' . implode( '</code> <code>', $tags ) . '</code>';
		}

		return $tags;
	}

	public static function on_report_post_created( $report_id, $item, $data ) {
		$post = geodir_get_post_info( (int) $item->post_id );
		if ( empty( $post ) ) {
			return;
		}

		$email_name = 'admin_report_post';

		if ( ! GeoDir_Email::is_email_enabled( $email_name ) ) {
			return false;
		}

		$recipient = GeoDir_Email::get_admin_email();

		if ( empty( $post ) || ! is_email( $recipient ) ) {
			return;
		}

		$email_vars = array(
			'post' => $post,
			'to_email' => $recipient,
			'report_id' => $item->id,
			'report_post_user_id' => ! empty( $item->user_id ) ? $item->user_id : __( 'n/a', 'geodirectory' ),
			'report_post_user_name' => $item->user_name,
			'report_post_user_email' => $item->user_email,
			'report_post_user_ip' => $item->user_ip,
			'report_post_date' => date_i18n( geodir_date_time_format(), strtotime( $item->report_date ) ),
			'report_post_reason' => $item->reason,
			'report_post_message' => ! empty( $item->message ) ? $item->message : __( 'n/a', 'geodirectory' ),
			'report_post_section_link' => admin_url( 'admin.php?page=gd-settings' )
		);

		/**
		 * Skip email send.
		 *
		 * @since 2.3.58
		 */
		$skip = apply_filters( 'geodir_skip_email_send', false, $email_name, $email_vars );

		if ( $skip === true ) {
			return;
		}

		do_action( 'geodir_pre_' . $email_name . '_email', $email_name, $email_vars );

		$subject      = GeoDir_Email::get_subject( $email_name, $email_vars );
		$message_body = GeoDir_Email::get_content( $email_name, $email_vars );
		$headers      = GeoDir_Email::get_headers( $email_name, $email_vars );
		$attachments  = GeoDir_Email::get_attachments( $email_name, $email_vars );

		$plain_text = GeoDir_Email::get_email_type() != 'html' ? true : false;
		$template   = $plain_text ? 'emails/plain/geodir-email-' . $email_name . '.php' : 'emails/geodir-email-' . $email_name . '.php';

		$content = geodir_get_template_html( $template, array(
			'email_name'    => $email_name,
			'email_vars'    => $email_vars,
			'email_heading'	=> '',
			'sent_to_admin' => true,
			'plain_text'    => $plain_text,
			'message_body'  => $message_body,
		) );

		$sent = GeoDir_Email::send( $recipient, $subject, $content, $headers, $attachments, $email_name, $email_vars );

		do_action( 'geodir_post_' . $email_name . '_email', $email_vars );
	}

	public static function get_form( $gd_post, $inline = false ) {
		if ( empty( $gd_post ) ) {
			return false;
		}

		$content = apply_filters( 'geodir_report_post_pre_get_form', NULL, $gd_post, $inline );
		if ( $content ) {
			return $content;
		}

		$user_name = '';
		$user_email = '';
		if ( $user_id = (int) get_current_user_id() ) {
			$user_name = geodir_get_client_name( $user_id );
			$userdata = get_userdata( $user_id );

			if ( ! empty( $userdata->user_email ) ) {
				$user_email = $userdata->user_email;
			}
		}

		$design_style = geodir_design_style();

		$template = $design_style ? $design_style . '/report-post-form.php' : 'report-post-form.php';

		$params = array(
			'post' => $gd_post,
			'user_name' => $user_name,
			'user_email' => $user_email,
			'inline' => $inline,
			'post_type_name' => geodir_post_type_singular_name( $gd_post->post_type, true )
		);
		$params = apply_filters( 'geodir_report_post_form_template_params', $params, $gd_post );

		$content = geodir_get_template_html( $template, $params );

		return $content;
	}

	public static function handle_request( $request ) {
		$response = GeoDir_Report_Post::process_request( $request );

		if ( is_wp_error( $response ) ) {
			$message = aui()->alert(
				array(
					'type'=> 'warning',
					'content'=> $response->get_error_message(),
					'class' => 'geodir-report-post-msg mb-3 text-left text-start'
				)
			);
			wp_send_json_error( array( 'message' => $message ) );
		} else {
			if ( ! empty( $response['message'] ) ) {
				$response['message'] = aui()->alert(
					array(
						'type'=> 'success',
						'content'=> $response['message'],
						'class' => 'geodir-report-post-msg m-3 text-left text-start'
					)
				);
			}

			wp_send_json_success( $response );
		}
	}

	public static function process_request( $request ) {
		global $wpdb;

		$gd_post = ! empty( $request['post_id'] ) ? geodir_get_post_info( absint( $request['post_id'] ) ) : array();

		if ( empty( $gd_post ) ) {
			return new WP_Error( 'invalid_post', __( 'Invalid post.', 'geodirectory' ) );
		}

		if ( ! apply_filters( 'geodir_allow_report_post', true, $gd_post ) ) {
			return new WP_Error( 'invalid_access', __( 'You can\' report this post.', 'geodirectory' ) );
		}

		$request['user_id'] = (int) get_current_user_id();

		if ( $request['user_id'] ) {
			$request['geodir_name'] = geodir_get_client_name( $request['user_id'] );
			$userdata = get_userdata( $request['user_id'] );

			if ( ! empty( $userdata->user_email ) ) {
				$request['geodir_email'] = $userdata->user_email;
			}
		}

		// Sanitize data
		$data = self::sanitize_data( $request, $gd_post );

		$data['post_id'] = absint( $gd_post->ID );
		$data['user_ip'] = sanitize_text_field( geodir_get_ip() );
		$data['report_date'] = date_i18n( 'Y-m-d H:i:s' );

		// Pre validation
		$data = self::validate_data( $data, $request, $gd_post );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		$report_id = self::save( $data, true );

		if ( is_wp_error( $report_id ) ) {
			return $report_id;
		}

		if ( ! $report_id ) {
			return new WP_Error( 'report_post_error', __( 'Something went wrong in saving report post item.', 'geodirectory' ) );
		}

		$response = array( 'id' => (int) $report_id,'message' => wp_sprintf( __( '%s has been reported successfully.', 'geodirectory' ), geodir_post_type_singular_name( $gd_post->post_type, true ) ) );

		return apply_filters( 'geodir_report_post_response', $response, $gd_post, $request );
	}

	public static function sanitize_data( $request, $gd_post ) {
		$data = array();
		if ( isset( $request['user_id'] ) ) {
			$data['user_id'] = absint( $request['user_id'] );
		}

		if ( isset( $request['geodir_name'] ) ) {
			$data['user_name'] = sanitize_text_field( wp_unslash( $request['geodir_name'] ) );
		}

		if ( isset( $request['geodir_email'] ) ) {
			$data['user_email'] = sanitize_email( $request['geodir_email'] );
		}

		if ( isset( $request['geodir_reason'] ) ) {
			$data['reason'] = sanitize_text_field( wp_unslash( $request['geodir_reason'] ) );
		}

		if ( isset( $request['geodir_message'] ) ) {
			$data['message'] = sanitize_textarea_field( wp_unslash( $request['geodir_message'] ) );
		}

		return apply_filters( 'geodir_report_post_sanitize_data', $data, $request, $gd_post );
	}

	public static function validate_data( $data, $request, $gd_post ) {
		if ( ! is_wp_error( $data ) ) {
			if ( empty( $data['user_name'] ) ) {
				return new WP_Error( 'report_post_validation_error', __( 'A valid full name is required.', 'geodirectory' ) );
			}

			if ( ! ( ! empty( $data['user_email'] ) && is_email( $data['user_email'] ) ) ) {
				return new WP_Error( 'report_post_validation_error', __( 'A valid email address is required.', 'geodirectory' ) );
			}

			if ( empty( $data['reason'] ) ) {
				return new WP_Error( 'report_post_validation_error', __( 'Select reason for reporting the item.', 'geodirectory' ) );
			}

			if ( ! empty( $data['user_email'] ) && self::get_item_by( array( 'post_id' => $gd_post->ID, 'user_email' => $data['user_email'], 'status' => '' ) ) ) {
				return new WP_Error( 'report_post_validation_error', __( 'You have already reported this item before.', 'geodirectory' ) );
			}
		}

		return apply_filters( 'geodir_report_post_validate_data', $data, $request, $gd_post );
	}

	public static function has_reported( $request, $post_id ) {
		return apply_filters( 'geodir_user_has_reported', $data, $request, $gd_post );
	}

	public static function save( $data, $wp_error = false ) {
		global $wpdb;

		$update = false;
		$item = array();
		if ( ! empty( $data['id'] ) ) {
			$item = self::get_item( (int) $data['id'] );

			if ( empty( $item ) ) {
				if ( $wp_error ) {
					return new WP_Error( 'report_post_error', __( 'Could not find report post item.', 'geodirectory' ) );
				} else {
					return 0;
				}
			}

			$update = true;
		}

		if ( $update ) {
			$report_id = $data['id'];

			$data = apply_filters( 'geodir_report_post_update_data', $data, $item );

			if ( false === $wpdb->update( GEODIR_POST_REPORTS_TABLE, $data, array( 'id' => $report_id ) ) ) {
				if ( $wp_error ) {
					return new WP_Error( 'db_update_error', __( 'Could not save report post item.', 'geodirectory' ), $wpdb->last_error );
				} else {
					return 0;
				}
			}

			$item_after = self::get_item( (int) $report_id );

			do_action( 'geodir_report_post_updated', $report_id, $item_after, $item, $data );
		} else {
			if ( isset( $data['id'] ) ) {
				unset( $data['id'] );
			}

			$data = apply_filters( 'geodir_report_post_insert_data', $data );

			if ( false === $wpdb->insert( GEODIR_POST_REPORTS_TABLE, $data ) ) {
				if ( $wp_error ) {
					return new WP_Error( 'db_insert_error', __( 'Could not save report post.', 'geodirectory' ), $wpdb->last_error );
				} else {
					return 0;
				}
			}

			$report_id = (int) $wpdb->insert_id;

			$item_after = self::get_item( $report_id );

			do_action( 'geodir_report_post_created', $report_id, $item_after, $data );
		}

		wp_cache_delete( "geodir-post-report-0", 'counts' );
		if ( ! empty( $item_after ) ) {
			wp_cache_delete( "geodir-post-report-" . $item_after->post_id, 'counts' );
		}

		do_action( 'geodir_report_post_saved', $report_id, $data, $item_after, $update );

		return $report_id;
	}

	public static function get_item( $id ) {
		global $wpdb;

		$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_POST_REPORTS_TABLE . " WHERE id = %d LIMIT 1", array( $id ) ) );

		return $item;
	}

	public static function get_item_by( $params ) {
		global $wpdb;

		if ( empty( $params ) ) {
			return array();
		}

		$where = array();
		$args = array();

		foreach ( $params as $field => $value ) {
			if ( in_array( $field, array( 'post_id', 'user_id' ) ) ) {
				$where[] = $field . ' = %d';
				$args[] = $value;
			} elseif ( in_array( $field, array( 'user_ip', 'user_name', 'user_email', 'status' ) ) ) {
				$where[] = $field . ' = %s';
				$args[] = $value;
			}
		}

		if ( empty( $where ) ) {
			return array();
		}

		$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . GEODIR_POST_REPORTS_TABLE . " WHERE " . implode( ' AND ', $where ) . " LIMIT 1", $args ) );

		return $item;
	}

	public static function get_counts( $post_id = 0 ) {
		global $wpdb;

		$post_id = (int) $post_id;

		$count = wp_cache_get( "geodir-post-report-{$post_id}", 'counts' );
		if ( false !== $count ) {
			return $count;
		}

		$where = '';
		if ( $post_id > 0 ) {
			$where = $wpdb->prepare( 'WHERE post_id = %d', $post_id );
		}

		$totals = (array) $wpdb->get_results( "SELECT status, COUNT( * ) AS total FROM " . GEODIR_POST_REPORTS_TABLE . " {$where} GROUP BY status", ARRAY_A );

		$report_count = array(
			'resolved' => 0,
			'pending'  => 0,
			'rejected' => 0,
			'total' => 0,
			'all' => 0,
		);

		foreach ( $totals as $row ) {
			switch ( $row['status'] ) {
				case 'resolved':
					$report_count['resolved'] = $row['total'];
					break;
				case 'rejected':
					$report_count['rejected'] = $row['total'];
					break;
				default:
					$report_count['pending'] = $row['total'];
					break;
			}

			$report_count['total'] += $row['total'];
			$report_count['all'] += $row['total'];
		}

		$stats = $report_count;

		$stats_object = (object) $stats;

		wp_cache_set( "geodir-post-report-{$post_id}", $stats_object, 'counts' );

		return $stats_object;
	}

	public static function set_status( $status, $id ) {
		global $wpdb;

		$item = self::get_item( (int) $id );

		if ( empty( $item ) ) {
			return false;
		}

		if ( $item->status === $status ) {
			return false;
		}

		$result = $wpdb->update( GEODIR_POST_REPORTS_TABLE, array( 'status' => $status, 'updated_date' => date_i18n( 'Y-m-d H:i:s' ) ), array( 'id' => $id ) );

		if ( ! $result ) {
			return false;
		}

		self::delete_cache( $item->post_id );

		do_action( 'geodir_report_post_set_status', $status, $id, $item );

		return true;
	}

	public static function delete( $id ) {
		global $wpdb;

		$item = self::get_item( (int) $id );

		if ( empty( $item ) ) {
			return false;
		}

		$result = $wpdb->delete( GEODIR_POST_REPORTS_TABLE, array( 'id' => $id ) );

		if ( ! $result ) {
			return false;
		}

		self::delete_cache( $item->post_id );

		do_action( 'geodir_report_post_item_deleted', $id, $item );

		return true;
	}

	public static function set_post_status( $post_status, $id ) {
		$item = self::get_item( (int) $id );

		if ( empty( $item->post_id ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $item->post_id );
		if ( empty( $gd_post ) ) {
			return false;
		}

		if ( $gd_post->post_status === $post_status ) {
			return false;
		}

		$post_data = array();
		$post_data['ID'] = $gd_post->ID;
		$post_data['post_status'] = $post_status;

		$post_data = apply_filters( 'geodir_repost_post_set_post_status_data', $post_data, $gd_post, $post_status, $item );

		$result = wp_update_post( $post_data );

		do_action( 'geodir_repost_post_after_set_post_status', $gd_post, $post_status, $item );

		if ( ! $result ) {
			return false;
		}

		// Mark as resolved.
		self::set_status( 'resolved', $item->id );

		self::delete_cache( $item->post_id );

		do_action( 'geodir_repost_post_set_post_status', $post_status, $item, $gd_post );

		return true;
	}

	public static function delete_post( $id, $force = false ) {
		$item = self::get_item( (int) $id );

		if ( empty( $item->post_id ) ) {
			return false;
		}

		$gd_post = geodir_get_post_info( $item->post_id );
		if ( empty( $gd_post ) ) {
			return false;
		}

		do_action( 'geodir_repost_post_before_delete_post', $gd_post, $item, $force );

		if ( $force ) {
			$result = wp_delete_post( $gd_post->ID, true );
		} else {
			$result = wp_trash_post( $gd_post->ID );
		}

		if ( ! $result ) {
			return false;
		}

		if ( $force ) {
			// Delete
			self::delete( $item->id );
		} else {
			// Mark as resolved.
			self::set_status( 'resolved', $item->id );
		}

		self::delete_cache( $gd_post->ID );

		do_action( 'geodir_repost_post_after_delete_post', $gd_post, $item, $force );

		return true;
	}

	public static function delete_cache( $post_id = 0 ) {
		wp_cache_delete( "geodir-post-report-0", 'counts' );

		if ( $post_id ) {
			wp_cache_delete( "geodir-post-report-" . (int) $post_id, 'counts' );
		}
	}
}

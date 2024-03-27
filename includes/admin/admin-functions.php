<?php
/**
 * GeoDirectory Admin Functions
 *
 * @author   AyeCode Ltd
 * @category Core
 * @package  GeoDirectory/Admin/Functions
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Get all GeoDirectory screen ids.
 *
 * @return array
 */
function geodir_get_screen_ids() {
	$screen_ids = array(
		'toplevel_page_geodirectory',
		'geodirectory_page_gd-settings',
		'geodirectory_page_gd-status',
		'geodirectory_page_gd-addons',
		'comment'
	);

	// Check for translated screen id.
	$geodir_screen_id = sanitize_title( __( 'GeoDirectory', 'geodirectory' ) );

	if ( $geodir_screen_id != 'geodirectory' ) {
		$screen_ids[] = 'toplevel_page_' . $geodir_screen_id;
		$screen_ids[] = $geodir_screen_id . '_page_gd-settings';
		$screen_ids[] = $geodir_screen_id . '_page_gd-status';
		$screen_ids[] = $geodir_screen_id . '_page_gd-addons';
	}

	// Add the CPT screens
	$post_types = geodir_get_posttypes( 'names' );
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$screen_ids[] = $post_type; // CPT add new
			$screen_ids[] = 'edit-' . $post_type; // CPT view screen
			$screen_ids[] = 'edit-' . $post_type . '_tags'; // CPT tags screen
			$screen_ids[] = 'edit-' . $post_type . 'category'; // CPT category screen
			$screen_ids[] = $post_type . '_page_'.$post_type.'-settings'; // CPT settings page
		}
	}

	return apply_filters( 'geodirectory_screen_ids', $screen_ids );
}

/**
 * Save or Update custom fields into the database.
 *
 * @since 1.0.0
 * @since 1.5.6 Fix for saving multiselect custom field "Display Type" on first attempt.
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param array $field {
 *    Attributes of the request field array.
 *
 * @type string $action Ajax Action name. Default "geodir_ajax_action".
 * @type string $manage_field_type Field type Default "custom_fields".
 * @type string $create_field Create field Default "true".
 * @type string $field_ins_upd Field ins upd Default "submit".
 * @type string $_wpnonce WP nonce value.
 * @type string $listing_type Listing type Example "gd_place".
 * @type string $field_type Field type Example "radio".
 * @type string $field_id Field id Example "12".
 * @type string $data_type Data type Example "VARCHAR".
 * @type string $is_active Either "1" or "0". If "0" is used then the field will not be displayed anywhere.
 * @type array $show_on_pkg Package list to display this field.
 * @type string $admin_title Personal comment, it would not be displayed anywhere except in custom field settings.
 * @type string $frontend_title Section title which you wish to display in frontend.
 * @type string $frontend_desc Section description which will appear in frontend.
 * @type string $htmlvar_name Html variable name. This should be a unique name.
 * @type string $clabels Section Title which will appear in backend.
 * @type string $default_value The default value (for "link" this will be used as the link text).
 * @type string $sort_order The display order of this field in backend. e.g. 5.
 * @type string $is_default Either "1" or "0". If "0" is used then the field will be displayed as main form field or additional field.
 * @type string $for_admin_use Either "1" or "0". If "0" is used then only site admin can edit this field.
 * @type string $is_required Use "1" to set field as required.
 * @type string $required_msg Enter text for error message if field required and have not full fill requirement.
 * @type string $show_on_listing Want to show this on listing page?.
 * @type string $show_in What locations to show the custom field in.
 * @type string $show_on_detail Want to show this in More Info tab on detail page?.
 * @type string $show_as_tab Want to display this as a tab on detail page? If "1" then "Show on detail page?" must be Yes.
 * @type string $option_values Option Values should be separated by comma.
 * @type string $field_icon Upload icon using media and enter its url path, or enter font awesome class.
 * @type string $css_class Enter custom css class for field custom style.
 * @type array $extra_fields An array of extra fields to store.
 *
 * }
 * @param bool $default Not yet implemented.
 *
 * @return int|string If field is unique returns inserted row id. Otherwise returns error string.
 */
function geodir_custom_field_save( $field = array() ) {

	$cfs = new GeoDir_Settings_Cpt_Cf();

	return $cfs->save_custom_field( $field );
}

/**
 * Initiate the WordPress file system and provide fallback if needed.
 *
 * @since 1.4.8
 * @package GeoDirectory
 * @return bool|string Returns the file system class on success. False on failure.
 */
function geodir_init_filesystem() {

	if ( ! function_exists( 'get_filesystem_method' ) ) {
		require_once( ABSPATH . "/wp-admin/includes/file.php" );
	}
	$access_type = get_filesystem_method();
	if ( $access_type === 'direct' ) {
		/* you can safely run request_filesystem_credentials() without any issues and don't need to worry about passing in a URL */
		$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

		/* initialize the API */
		if ( ! WP_Filesystem( $creds ) ) {
			/* any problems and we exit */
			return false;
		}

		global $wp_filesystem;

		return $wp_filesystem;
		/* do our file manipulations below */
	} elseif ( defined( 'FTP_USER' ) ) {
		$creds = request_filesystem_credentials( trailingslashit( site_url() ) . 'wp-admin/', '', false, false, array() );

		/* initialize the API */
		if ( ! WP_Filesystem( $creds ) ) {
			/* any problems and we exit */
			return false;
		}

		global $wp_filesystem;

		return $wp_filesystem;

	} else {
		/* don't have direct write access. Prompt user with our notice */
		//add_action('admin_notice', 'geodir_filesystem_notice');
		return false;
	}

}

/**
 * Get the posts counts for the current post type.
 *
 * @since 1.4.6
 * @since 1.6.4 Updated to filter posts.
 * @package GeoDirectory
 *
 * @global object $wpdb WordPress Database object.
 * @global string $plugin_prefix Geodirectory plugin table prefix.
 *
 * @param string $post_type Post type.
 *
 * @return int Posts count.
 */
function geodir_get_posts_count( $post_type ) {
	global $wpdb, $plugin_prefix;

	if ( ! post_type_exists( $post_type ) ) {
		return 0;
	}

	$table = $plugin_prefix . $post_type . '_detail';

	// Skip listing with statuses trash, auto-draft etc...
	$skip_statuses  = geodir_imex_export_skip_statuses();
	$where_statuses = '';
	if ( ! empty( $skip_statuses ) && is_array( $skip_statuses ) ) {
		$where_statuses = "AND `" . $wpdb->posts . "`.`post_status` NOT IN('" . implode( "','", $skip_statuses ) . "')";
	}

	/**
	 * Filter the SQL where clause part to filter posts count in import/export.
	 *
	 * @since 1.6.4
	 * @package GeoDirectory
	 *
	 * @param string $where SQL where clause part.
	 */
	$where_statuses = apply_filters( 'geodir_get_posts_count', $where_statuses, $post_type );

	$query = $wpdb->prepare( "SELECT COUNT({$wpdb->posts}.ID) FROM {$wpdb->posts} INNER JOIN {$table} ON {$table}.post_id = {$wpdb->posts}.ID WHERE {$wpdb->posts}.post_type = %s " . $where_statuses, $post_type );

	$posts_count = (int) $wpdb->get_var( $query );

	/**
	 * Modify returned post counts for the current post type.
	 *
	 * @since 1.4.6
	 * @package GeoDirectory
	 *
	 * @param int $posts_count Post counts.
	 * @param string $post_type Post type.
	 */
	$posts_count = apply_filters( 'geodir_imex_count_posts', $posts_count, $post_type );

	return $posts_count;
}

/**
 * Retrieve terms count for given post type.
 *
 * @since 1.4.6
 * @package GeoDirectory
 *
 * @param  string $post_type Post type.
 *
 * @return int Total terms count.
 */
/**
 * Retrieve terms count for given post type.
 *
 * @since 1.4.6
 * @package GeoDirectory
 *
 * @param  string $post_type Post type.
 *
 * @return int Total terms count.
 */
function geodir_get_terms_count( $post_type ) {
	$args = array( 'hide_empty' => 0 );

	remove_all_filters( 'get_terms' );

	$post_type = sanitize_text_field( $post_type );

	$taxonomy = $post_type . 'category';

	do_action( 'geodir_before_count_terms', $post_type, $taxonomy, $args );

	$count_terms = wp_count_terms( $taxonomy, $args );

	do_action( 'geodir_after_count_terms', $post_type, $taxonomy, $args );

	$count_terms = ! is_wp_error( $count_terms ) ? $count_terms : 0;

	return $count_terms;
}

/**
 * Create a page.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @global object $current_user Current user object.
 *
 * @param string $slug The page slug.
 * @param string $option The option meta key.
 * @param string $page_title The page title.
 * @param string $page_content The page description.
 * @param int $post_parent Parent page ID.
 * @param string $status Post status.
 */
function geodir_create_page( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0, $status = 'publish' ) {
	global $wpdb, $current_user;

	$option_value = geodir_get_option( $option );

	if ( $option_value > 0 ) :
		if ( get_post( $option_value ) ) :
			// Page exists
			return;
		endif;
	endif;

	$page_found = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s AND post_type = 'page' LIMIT 1;",
			array( $slug )
		)
	);

	if ( $page_found ) :
		// Page exists
		if ( ! $option_value ) {
			geodir_update_option( $option, $page_found );
		}

		return;
	endif;

	$page_data = array(
		'post_status'    => $status,
		'post_type'      => 'page',
		'post_author'    => $current_user->ID,
		'post_name'      => $slug,
		'post_title'     => $page_title,
		'post_content'   => $page_content,
		'post_parent'    => $post_parent,
		'comment_status' => 'closed'
	);
	$page_id   = wp_insert_post( $page_data );

	geodir_update_option( $option, $page_id );
}

/**
 * Displays an update message for plugin list screens.
 * Shows only the version updates from the current until the newest version
 *
 * @param (array) $plugin_data
 * @param (object) $r
 *
 * @return (string) $output
 */
function geodir_admin_upgrade_notice() {
	$result = get_transient( 'geodir_admin_upgrade_notice' );
	if ( ! empty( $result ) ) {
		geodir_in_plugin_upgrade_message( $result );
	}else{
		// readme contents
		$args = array(
			'timeout'     => 15,
			'redirection' => 5
		);
		$url  = "http://plugins.svn.wordpress.org/geodirectory/trunk/readme.txt";
		$data = wp_remote_get( $url, $args );

		if ( ! is_wp_error( $data ) && $data['response']['code'] == 200 ) {
			$result = $data['body'];
			set_transient( 'geodir_admin_upgrade_notice', $result, HOUR_IN_SECONDS );
			geodir_in_plugin_upgrade_message( $result );
		}
	}
}

/**
 * Format the update message.
 *
 * @param $content
 */
function geodir_in_plugin_upgrade_message( $content ) {
	// Output Upgrade Notice
	$matches        = null;
	$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( GEODIRECTORY_VERSION ) . '\s*=|$)~Uis';
	$upgrade_notice = '';
	if ( preg_match( $regexp, $content, $matches ) ) {
		if ( empty( $matches ) ) {
			return;
		}

		$version = trim( $matches[1] );
		if ( $version && $version > GEODIRECTORY_VERSION ) {


			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );
			if ( version_compare( GEODIRECTORY_VERSION, $version, '<' ) ) {
				$upgrade_notice .= '<div class="gd_plugin_upgrade_notice">';
				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
				}
				$upgrade_notice .= '</div> ';
			}
		}
	}
	echo $upgrade_notice;
}

/**
 * Get the statuses to skip during GD export listings.
 *
 * @package GeoDirectory
 * @since 1.6.0
 *
 * @return array Listing statuses to be skipped.
 */
function geodir_imex_export_skip_statuses() {
	$statuses = array( 'trash', 'auto-draft' );

	/**
	 * Filter the statuses to skip during GD export listings.
	 *
	 * @since 1.6.0
	 * @package GeoDirectory
	 *
	 * @param array $statuses Listing statuses to be skipped.
	 */
	$statuses = apply_filters( 'geodir_imex_export_skip_statuses', $statuses );

	return $statuses;
}

/**
 * Get the current post type in the WordPress admin
 *
 * @since 1.4.2
 * @package GeoDirectory
 *
 * @global null|WP_Post $post Post object.
 * @global string $typenow Post type.
 * @global object|WP_Screen $current_screen Current screen object
 *
 * @return string Post type ex: gd_place
 */
function geodir_admin_current_post_type() {
	global $post, $typenow, $current_screen;

	$post_type = null;
	if ( isset( $_REQUEST['post_type'] ) ) {
		$post_type = sanitize_key( $_REQUEST['post_type'] );
	} elseif ( isset( $_REQUEST['post'] ) && get_post_type( $_REQUEST['post'] ) ) {
		$post_type = get_post_type( $_REQUEST['post'] );
	} elseif ( $post && isset( $post->post_type ) ) {
		$post_type = $post->post_type;
	} elseif ( $typenow ) {
		$post_type = $typenow;
	} elseif ( $current_screen && isset( $current_screen->post_type ) ) {
		$post_type = $current_screen->post_type;
	}

	return $post_type;
}





/**
 * Add column if table column not exist.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param string $db The table name.
 * @param string $column The column name.
 * @param string $column_attr The column attributes.
 */
function geodir_add_column_if_not_exist( $db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL" ) {
	global $wpdb;

	$result = 0; // No rows affected

	if ( ! empty( $db ) && ! empty( $column ) && ! geodir_column_exist( $db, $column ) ) {
		$result = $wpdb->query( "ALTER TABLE `$db` ADD `$column` $column_attr" );
	}

	return $result;
}

/**
 * GeoDirectory Core Supported Themes.
 *
 * @since 2.0.0
 * @return string[]
 */
function geodir_get_core_supported_themes() {
	return array( 'whoop','supreme-directory','directory-starter', 'twentyseventeen', 'twentysixteen', 'twentyfifteen', 'twentyfourteen', 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten', 'twentytwentytwo' );
}

function geodir_setup_timezone_api( $prefix ) {
	?>
	if (jQuery('[name="<?php echo $prefix; ?>region"]').length && jQuery('[name="<?php echo $prefix; ?>timezone_string"]').length) {
		if (getState && getState != jQuery('[name="<?php echo $prefix; ?>region"]').data('prev-value')) {
			geodir_fill_timezone('<?php echo $prefix; ?>');
		}
		jQuery('[name="<?php echo $prefix; ?>region"]').attr('data-prev-value', getState);
	}
	<?php
}
add_action( 'geodir_update_marker_address', 'geodir_setup_timezone_api', 1, 1 );

/**
 * Add body class for current active map.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @param string $class The class of the HTML element.
 * @return string Modified class string.
 */
function geodir_admin_body_class_active_map($class = '') {
	$class .= ' gd-map-' . GeoDir_Maps::active_map();

	// Add original geodirectory page class when geodirectory screen is translated.
	if ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], array( 'gd-settings', 'gd-status', 'gd-addons' ) ) ) {
		$geodir_screen_id = sanitize_title( __( 'GeoDirectory', 'geodirectory' ) );

		if ( $geodir_screen_id != 'geodirectory' ) {
			$class .= ' geodirectory_page_' . esc_attr( sanitize_text_field( $_GET['page'] ) );
		}
	}

	// Add body class for admin pages.
	$screen = get_current_screen();
	$screen_id = $screen ? $screen->id : '';

	if ( $screen_id && in_array( $screen_id, geodir_get_screen_ids() ) ) {
		$class .= ' geodir-admin-page geodir-admin-page-' . sanitize_key( $screen_id );
	}

	return $class;
}
add_filter('admin_body_class', 'geodir_admin_body_class_active_map', 100);

function geodir_dashicon_options() {
	$dashicons = array(
		'dashicons-admin-appearance' => 'f100',
		'dashicons-admin-collapse' => 'f148',
		'dashicons-admin-comments' => 'f101',
		'dashicons-admin-customizer' => 'f540',
		'dashicons-admin-generic' => 'f111',
		'dashicons-admin-home' => 'f102',
		'dashicons-admin-links' => 'f103',
		'dashicons-admin-media' => 'f104',
		'dashicons-admin-multisite' => 'f541',
		'dashicons-admin-network' => 'f112',
		'dashicons-admin-page' => 'f105',
		'dashicons-admin-plugins' => 'f106',
		'dashicons-admin-post' => 'f109',
		'dashicons-admin-settings' => 'f108',
		'dashicons-admin-site' => 'f319',
		'dashicons-admin-tools' => 'f107',
		'dashicons-admin-users' => 'f110',
		'dashicons-album' => 'f514',
		'dashicons-align-center' => 'f134',
		'dashicons-align-left' => 'f135',
		'dashicons-align-none' => 'f138',
		'dashicons-align-right' => 'f136',
		'dashicons-analytics' => 'f183',
		'dashicons-archive' => 'f480',
		'dashicons-arrow-down' => 'f140',
		'dashicons-arrow-down-alt' => 'f346',
		'dashicons-arrow-down-alt2' => 'f347',
		'dashicons-arrow-left' => 'f141',
		'dashicons-arrow-left-alt' => 'f340',
		'dashicons-arrow-left-alt2' => 'f341',
		'dashicons-arrow-right' => 'f139',
		'dashicons-arrow-right-alt' => 'f344',
		'dashicons-arrow-right-alt2' => 'f345',
		'dashicons-arrow-up' => 'f142',
		'dashicons-arrow-up-alt' => 'f342',
		'dashicons-arrow-up-alt2' => 'f343',
		'dashicons-art' => 'f309',
		'dashicons-awards' => 'f313',
		'dashicons-backup' => 'f321',
		'dashicons-book' => 'f330',
		'dashicons-book-alt' => 'f331',
		'dashicons-building' => 'f512',
		'dashicons-businessman' => 'f338',
		'dashicons-calendar' => 'f145',
		'dashicons-calendar-alt' => 'f508',
		'dashicons-camera' => 'f306',
		'dashicons-carrot' => 'f511',
		'dashicons-cart' => 'f174',
		'dashicons-category' => 'f318',
		'dashicons-chart-area' => 'f239',
		'dashicons-chart-bar' => 'f185',
		'dashicons-chart-line' => 'f238',
		'dashicons-chart-pie' => 'f184',
		'dashicons-clipboard' => 'f481',
		'dashicons-clock' => 'f469',
		'dashicons-cloud' => 'f176',
		'dashicons-controls-back' => 'f518',
		'dashicons-controls-forward' => 'f519',
		'dashicons-controls-pause' => 'f523',
		'dashicons-controls-play' => 'f522',
		'dashicons-controls-repeat' => 'f515',
		'dashicons-controls-skipback' => 'f516',
		'dashicons-controls-skipforward' => 'f517',
		'dashicons-controls-volumeoff' => 'f520',
		'dashicons-controls-volumeon' => 'f521',
		'dashicons-dashboard' => 'f226',
		'dashicons-desktop' => 'f472',
		'dashicons-dismiss' => 'f153',
		'dashicons-download' => 'f316',
		'dashicons-edit' => 'f464',
		'dashicons-editor-aligncenter' => 'f207',
		'dashicons-editor-alignleft' => 'f206',
		'dashicons-editor-alignright' => 'f208',
		'dashicons-editor-bold' => 'f200',
		'dashicons-editor-break' => 'f474',
		'dashicons-editor-code' => 'f475',
		'dashicons-editor-contract' => 'f506',
		'dashicons-editor-customchar' => 'f220',
		'dashicons-editor-expand' => 'f211',
		'dashicons-editor-help' => 'f223',
		'dashicons-editor-indent' => 'f222',
		'dashicons-editor-insertmore' => 'f209',
		'dashicons-editor-italic' => 'f201',
		'dashicons-editor-justify' => 'f214',
		'dashicons-editor-kitchensink' => 'f212',
		'dashicons-editor-ol' => 'f204',
		'dashicons-editor-outdent' => 'f221',
		'dashicons-editor-paragraph' => 'f476',
		'dashicons-editor-paste-text' => 'f217',
		'dashicons-editor-paste-word' => 'f216',
		'dashicons-editor-quote' => 'f205',
		'dashicons-editor-removeformatting' => 'f218',
		'dashicons-editor-rtl' => 'f320',
		'dashicons-editor-spellcheck' => 'f210',
		'dashicons-editor-strikethrough' => 'f224',
		'dashicons-editor-table' => 'f535',
		'dashicons-editor-textcolor' => 'f215',
		'dashicons-editor-ul' => 'f203',
		'dashicons-editor-underline' => 'f213',
		'dashicons-editor-unlink' => 'f225',
		'dashicons-editor-video' => 'f219',
		'dashicons-email' => 'f465',
		'dashicons-email-alt' => 'f466',
		'dashicons-exerpt-view' => 'f164',
		'dashicons-external' => 'f504',
		'dashicons-facebook' => 'f304',
		'dashicons-facebook-alt' => 'f305',
		'dashicons-feedback' => 'f175',
		'dashicons-filter' => 'f536',
		'dashicons-flag' => 'f227',
		'dashicons-format-aside' => 'f123',
		'dashicons-format-audio' => 'f127',
		'dashicons-format-chat' => 'f125',
		'dashicons-format-gallery' => 'f161',
		'dashicons-format-image' => 'f128',
		'dashicons-format-quote' => 'f122',
		'dashicons-format-status' => 'f130',
		'dashicons-format-video' => 'f126',
		'dashicons-forms' => 'f314',
		'dashicons-googleplus' => 'f462',
		'dashicons-grid-view' => 'f509',
		'dashicons-groups' => 'f307',
		'dashicons-hammer' => 'f308',
		'dashicons-heart' => 'f487',
		'dashicons-hidden' => 'f530',
		'dashicons-id' => 'f336',
		'dashicons-id-alt' => 'f337',
		'dashicons-image-crop' => 'f165',
		'dashicons-image-filter' => 'f533',
		'dashicons-image-flip-horizontal' => 'f169',
		'dashicons-image-flip-vertical' => 'f168',
		'dashicons-image-rotate' => 'f531',
		'dashicons-image-rotate-left' => 'f166',
		'dashicons-image-rotate-right' => 'f167',
		'dashicons-images-alt' => 'f232',
		'dashicons-images-alt2' => 'f233',
		'dashicons-index-card' => 'f510',
		'dashicons-info' => 'f348',
		'dashicons-laptop' => 'f547',
		'dashicons-layout' => 'f538',
		'dashicons-leftright' => 'f229',
		'dashicons-lightbulb' => 'f339',
		'dashicons-list-view' => 'f163',
		'dashicons-location' => 'f230',
		'dashicons-location-alt' => 'f231',
		'dashicons-lock' => 'f160',
		'dashicons-marker' => 'f159',
		'dashicons-media-archive' => 'f501',
		'dashicons-media-audio' => 'f500',
		'dashicons-media-code' => 'f499',
		'dashicons-media-default' => 'f498',
		'dashicons-media-document' => 'f497',
		'dashicons-media-interactive' => 'f496',
		'dashicons-media-spreadsheet' => 'f495',
		'dashicons-media-text' => 'f491',
		'dashicons-media-video' => 'f490',
		'dashicons-megaphone' => 'f488',
		'dashicons-menu' => 'f333',
		'dashicons-microphone' => 'f482',
		'dashicons-migrate' => 'f310',
		'dashicons-minus' => 'f460',
		'dashicons-money' => 'f526',
		'dashicons-move' => 'f545',
		'dashicons-nametag' => 'f484',
		'dashicons-networking' => 'f325',
		'dashicons-no' => 'f158',
		'dashicons-no-alt' => 'f335',
		'dashicons-palmtree' => 'f527',
		'dashicons-paperclip' => 'f546',
		'dashicons-performance' => 'f311',
		'dashicons-phone' => 'f525',
		'dashicons-playlist-audio' => 'f492',
		'dashicons-playlist-video' => 'f493',
		'dashicons-plus' => 'f132',
		'dashicons-plus-alt' => 'f502',
		'dashicons-portfolio' => 'f322',
		'dashicons-post-status' => 'f173',
		'dashicons-pressthis' => 'f157',
		'dashicons-products' => 'f312',
		'dashicons-randomize' => 'f503',
		'dashicons-redo' => 'f172',
		'dashicons-rss' => 'f303',
		'dashicons-schedule' => 'f489',
		'dashicons-screenoptions' => 'f180',
		'dashicons-search' => 'f179',
		'dashicons-share' => 'f237',
		'dashicons-share-alt' => 'f240',
		'dashicons-share-alt2' => 'f242',
		'dashicons-shield' => 'f332',
		'dashicons-shield-alt' => 'f334',
		'dashicons-slides' => 'f181',
		'dashicons-smartphone' => 'f470',
		'dashicons-smiley' => 'f328',
		'dashicons-sort' => 'f156',
		'dashicons-sos' => 'f468',
		'dashicons-star-empty' => 'f154',
		'dashicons-star-filled' => 'f155',
		'dashicons-star-half' => 'f459',
		'dashicons-sticky' => 'f537',
		'dashicons-store' => 'f513',
		'dashicons-tablet' => 'f471',
		'dashicons-tag' => 'f323',
		'dashicons-tagcloud' => 'f479',
		'dashicons-testimonial' => 'f473',
		'dashicons-text' => 'f478',
		'dashicons-thumbs-down' => 'f542',
		'dashicons-thumbs-up' => 'f529',
		'dashicons-tickets' => 'f486',
		'dashicons-tickets-alt' => 'f524',
		'dashicons-translation' => 'f326',
		'dashicons-trash' => 'f182',
		'dashicons-twitter' => 'f301',
		'dashicons-undo' => 'f171',
		'dashicons-universal-access' => 'f483',
		'dashicons-universal-access-alt' => 'f507',
		'dashicons-unlock' => 'f528',
		'dashicons-update' => 'f463',
		'dashicons-upload' => 'f317',
		'dashicons-vault' => 'f178',
		'dashicons-video-alt' => 'f234',
		'dashicons-video-alt2' => 'f235',
		'dashicons-video-alt3' => 'f236',
		'dashicons-visibility' => 'f177',
		'dashicons-warning' => 'f534',
		'dashicons-welcome-add-page' => 'f133',
		'dashicons-welcome-comments' => 'f117',
		'dashicons-welcome-learn-more' => 'f118',
		'dashicons-welcome-view-site' => 'f115',
		'dashicons-welcome-widgets-menus' => 'f116',
		'dashicons-welcome-write-blog' => 'f119',
		'dashicons-wordpress' => 'f120',
		'dashicons-wordpress-alt' => 'f324',
		'dashicons-yes' => 'f147',
	);

	return apply_filters( 'geodir_dashicon_options', $dashicons );
}

/**
 * A helper function for margin inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_margin_input($type = 'mt', $overwrite = array(), $include_negatives = true ){
	$options = array(
		"" => __('None', 'geodirectory'),
		"0" => "0",
		"1" => "1",
		"2" => "2",
		"3" => "3",
		"4" => "4",
		"5" => "5",
	);

	if ( $include_negatives ) {
		$options['n1'] = '-1';
		$options['n2'] = '-2';
		$options['n3'] = '-3';
		$options['n4'] = '-4';
		$options['n5'] = '-5';
	}

	$defaults = array(
		'type' => 'select',
		'title' => __('Margin top', 'geodirectory'),
		'options' =>  $options,
		'default' => '',
		'desc_tip' => true,
		'group'     => __("Wrapper Styles","geodirectory")
	);

	// title
	if( $type == 'mt' ){
		$defaults['title'] = __('Margin top', 'geodirectory');
		$defaults['icon']  = 'box-top';
		$defaults['row'] = array(
			'title' => __('Margins', 'geodirectory'),
			'key'   => 'wrapper-margins',
			'open' => true,
			'class' => 'text-center',
		);
	}elseif( $type == 'mr' ){
		$defaults['title'] = __('Margin right', 'geodirectory');
		$defaults['icon']  = 'box-right';
		$defaults['row'] = array(
			'key'   => 'wrapper-margins',
		);
	}elseif( $type == 'mb' ){
		$defaults['title'] = __('Margin bottom', 'geodirectory');
		$defaults['icon']  = 'box-bottom';
		$defaults['row'] = array(
			'key'   => 'wrapper-margins',
		);
	}elseif( $type == 'ml' ){
		$defaults['title'] = __('Margin left', 'geodirectory');
		$defaults['icon']  = 'box-left';
		$defaults['row'] = array(
			'key'   => 'wrapper-margins',
			'close'   => true,
		);
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for padding inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_padding_input($type = 'pt', $overwrite = array() ){
	$options = array(
		"" => __('None', 'geodirectory'),
		"0" => "0",
		"1" => "1",
		"2" => "2",
		"3" => "3",
		"4" => "4",
		"5" => "5",
	);

	$defaults = array(
		'type' => 'select',
		'title' => __('Padding top', 'geodirectory'),
		'options' =>  $options,
		'default' => '',
		'desc_tip' => true,
		'group'     => __("Wrapper Styles","geodirectory")
	);

	// title
	if( $type == 'pt' ){
		$defaults['title'] = __('Padding top', 'geodirectory');
		$defaults['icon']  = 'box-top';
		$defaults['row'] = array(
			'title' => __('Padding', 'geodirectory'),
			'key'   => 'wrapper-padding',
			'open' => true,
			'class' => 'text-center',
		);
	}elseif( $type == 'pr' ){
		$defaults['title'] = __('Padding right', 'geodirectory');
		$defaults['icon']  = 'box-right';
		$defaults['row'] = array(
			'key'   => 'wrapper-padding',
		);
	}elseif( $type == 'pb' ){
		$defaults['title'] = __('Padding bottom', 'geodirectory');
		$defaults['icon']  = 'box-bottom';
		$defaults['row'] = array(
			'key'   => 'wrapper-padding',
		);
	}elseif( $type == 'pl' ){
		$defaults['title'] = __('Padding left', 'geodirectory');
		$defaults['icon']  = 'box-left';
		$defaults['row'] = array(
			'key'   => 'wrapper-padding',
			'close'   => true,

		);
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for border inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_border_input($type = 'border', $overwrite = array() ){

	$defaults = array(
		'type' => 'select',
		'title' => __('Border', 'geodirectory'),
		'options' =>  array(),
		'default' => '',
		'desc_tip' => true,
		'group'     => __("Wrapper Styles","geodirectory")
	);

	// title
	if( $type == 'rounded' ){
		$defaults['title'] = __('Border radius type', 'geodirectory');
		$defaults['options'] = array(
			''  =>  __("Default","geodirectory"),
			'rounded'  =>  'rounded',
			'rounded-top'  =>  'rounded-top',
			'rounded-right'  =>  'rounded-right',
			'rounded-bottom'  =>  'rounded-bottom',
			'rounded-left'  =>  'rounded-left',
			'rounded-circle'  =>  'rounded-circle',
			'rounded-pill'  =>  'rounded-pill',
			'rounded-0'  =>  'rounded-0',
		);
	}elseif( $type == 'rounded_size' ){
		$defaults['title'] = __('Border radius size', 'geodirectory');
		$defaults['options'] = array(
			''  =>  __("Default","geodirectory"),
			'sm'  =>  __("Small","geodirectory"),
			'lg'  =>  __("Large","geodirectory"),
		);
	}elseif( $type == 'type' ){
		$defaults['title'] = __('Border type', 'geodirectory');
		$defaults['options'] = array(
			''  =>  __("None","geodirectory"),
			'border'  =>  __("Full","geodirectory"),
			'border-top'  =>  __("Top","geodirectory"),
			'border-bottom'  =>  __("Bottom","geodirectory"),
			'border-left'  =>  __("Left","geodirectory"),
			'border-right'  =>  __("Right","geodirectory"),
		);
	}else{
		$defaults['title'] = __('Border color', 'geodirectory');
		$defaults['options'] = array(
           ''  =>  __("Default","geodirectory"),
           '0'  =>  __("None","geodirectory"),
       ) + geodir_aui_colors();
	}

	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for padding inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_shadow_input($type = 'shadow', $overwrite = array() ){
	$options = array(
		"" => __('None', 'geodirectory'),
		"shadow-sm" => __('Small', 'geodirectory'),
		"shadow" => __('Regular', 'geodirectory'),
		"shadow-lg" => __('Large', 'geodirectory'),
	);

	$defaults = array(
		'type' => 'select',
		'title' => __('Shadow', 'geodirectory'),
		'options' =>  $options,
		'default' => '',
		'desc_tip' => true,
		'group'     => __("Wrapper Styles","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for padding inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_background_input($type = 'bg', $overwrite = array() ){
	$options = array(
		           ''  =>  __("None","geodirectory"),
	           ) + geodir_aui_colors();

	$defaults = array(
		'type' => 'select',
		'title' => __('Background color', 'geodirectory'),
		'options' =>  $options,
		'default' => '',
		'desc_tip' => true,
		'group'     => __("Wrapper Styles","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for title tag inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_title_tag_input( $overwrite = array() ){

	$defaults = array(
		'title' => __('Title HTML tag', 'geodirectory'),
		'desc' => __('Set the HTML tag for the title.', 'geodirectory'),
		'type' => 'select',
		'options'   =>  array(
			"" => __("Default (theme widget default)","geodirectory"),
			"h1" => "h1",
			"h2" => "h2",
			"h3" => "h3",
			"h4" => "h4",
			"h5" => "h5",
			"h6" => "h6",
		),
		'default'  => '',
		'desc_tip' => true,
		'advanced' => false,
		'group'     => __("Title","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for font size inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_font_size_input( $overwrite = array() ){

	$defaults = array(
		'title' => __('Font size', 'geodirectory'),
		'type' => 'select',
		'options'   =>  array(
			"" => __("Default (title tag size)","geodirectory"),
			"h1" => 'XXL',
			"h2" => 'XL',
			"h3" => 'L',
			"h4" => 'M',
			"h5" => 'S',
			"h6" => 'XS',
			"display-1" => "display-1",
			"display-2" => "display-2",
			"display-3" => "display-3",
			"display-4" => "display-4",
		),
		'default'  => '',
		'desc_tip' => true,
		'advanced' => false,
		'group'     => __("Title","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}


/**
 * A helper function for title tag inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_text_align_input( $overwrite = array() ){

	$defaults = array(
		'title' => __('Text align', 'geodirectory'),
		'type' => 'select',
		'options'   =>  array(
			"" => __("Default (left)","geodirectory"),
			"text-left" => __("Left","geodirectory"),
			"text-center" => __("Center","geodirectory"),
			"text-right" => __("Right","geodirectory"),
			"text-justify" => __("Justify","geodirectory"),
		),
		'default'  => '',
		'desc_tip' => true,
		'advanced' => false,
		'group'     => __("Title","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}

/**
 * A helper function for padding inputs.
 *
 * @param string $type
 * @param array $overwrite
 *
 * @return array
 */
function geodir_get_sd_text_color_input( $overwrite = array() ){
	$options = array(
		           ''  =>  __("Default (theme color)","geodirectory"),
	           ) + geodir_aui_colors();

	$defaults = array(
		'type' => 'select',
		'title' => __('Text color', 'geodirectory'),
		'options' =>  $options,
		'default' => '',
		'desc_tip' => true,
		'advanced' => false,
		'group'     => __("Title","geodirectory")
	);


	$input = wp_parse_args( $overwrite, $defaults );


	return $input;
}


function geodir_get_sd_title_inputs(){

	$arguments = array();

	$arguments['widget_title_tag']  = geodir_get_sd_title_tag_input();
	$arguments['widget_title_size_class']  = geodir_get_sd_font_size_input(
		array(
			'element_require' => '[%widget_title_tag%]!=""',
			'row'       => array(
				'key'   => 'title-attr',
				'open' => true,
			)
		)
	);
	$arguments['widget_title_align_class'] = geodir_get_sd_text_align_input(
		array(
			'element_require' => '[%widget_title_tag%]!=""',
			'row'       => array(
				'key'   => 'title-attr',
			)
		)
	);
	$arguments['widget_title_color_class'] = geodir_get_sd_text_color_input(
		array(
			'element_require' => '[%widget_title_tag%]!=""',
			'row'       => array(
				'key'   => 'title-attr',
				'close' => true,
			)
		)
	);

	// Border
	$arguments['widget_title_border_class'] = geodir_get_sd_border_input('type',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'row'       => array(
				'key'   => 'title-border',
				'open' => true,
			)
		)
	);
	$arguments['widget_title_border_color_class'] = geodir_get_sd_border_input('border',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'row'       => array(
				'key'   => 'title-border',
				'close' => true,
			)
		)
	);

	// margins
	$arguments['widget_title_mt_class']  = geodir_get_sd_margin_input('mt',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-top',
			'row'       => array(
				'title' => __('Margins', 'geodirectory'),
				'desc_tip' => true,
				'key'   => 'title-margins',
				'open' => true,
				'class' => 'text-center',
				'element_require' => '[%widget_title_tag%]!=""',
			)
		) );
	$arguments['widget_title_mr_class']  = geodir_get_sd_margin_input('mr',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-right',
			'row'       => array(
				'key'   => 'title-margins',
			)
		));
	$arguments['widget_title_mb_class']  = geodir_get_sd_margin_input('mb',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-bottom',
			'row'       => array(
				'key'   => 'title-margins',
			)
		));
	$arguments['widget_title_ml_class']  = geodir_get_sd_margin_input('ml',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-left',
			'row'       => array(
				'key'   => 'title-margins',
				'close' => true,
			)
		));

	// padding
	$arguments['widget_title_pt_class']  = geodir_get_sd_padding_input('pt',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-top',
			'row'       => array(
				'title' => __('Padding', 'geodirectory'),
				'desc_tip' => true,
				'key'   => 'title-padding',
				'open' => true,
				'class' => 'text-center',
				'element_require' => '[%widget_title_tag%]!=""',
			)
		));
	$arguments['widget_title_pr_class']  = geodir_get_sd_padding_input('pr',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-right',
			'row'       => array(
				'key'   => 'title-padding',
			)
		));
	$arguments['widget_title_pb_class']  = geodir_get_sd_padding_input('pb',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-bottom',
			'row'       => array(
				'key'   => 'title-padding',
			)
		));
	$arguments['widget_title_pl_class']  = geodir_get_sd_padding_input('pl',
		array(
			'group' => __("Title","geodirectory"),
			'element_require' => '[%widget_title_tag%]!=""',
			'icon'  => 'box-left',
			'row'       => array(
				'key'   => 'title-padding',
				'close' => true,
			)
		));

	return $arguments;

}

/**
 * Build AUI classes from settings.
 *
 * @todo find best way to use px- py- or general p-
 * @param $args
 *
 * @return string
 */
function geodir_build_aui_class( $args ) {
	global $aui_bs5;

	if( ! geodir_design_style() ) {
		return '';
	}

	$classes = array();

	// margins
	if ( ! empty( $args['mt'] ) || ( isset( $args['mt'] ) && $args['mt'] == '0' ) ) { $classes[] = "mt-".sanitize_html_class($args['mt']); }
	if ( ! empty( $args['mr'] ) || ( isset( $args['mr'] ) && $args['mr'] == '0' ) ) { $classes[] = "mr-".sanitize_html_class($args['mr']); }
	if ( ! empty( $args['mb'] ) || ( isset( $args['mb'] ) && $args['mb'] == '0' ) ) { $classes[] = "mb-".sanitize_html_class($args['mb']); }
	if ( ! empty( $args['ml'] ) || ( isset( $args['ml'] ) && $args['ml'] == '0' ) ) { $classes[] = "ml-".sanitize_html_class($args['ml']); }

	// padding
	if ( ! empty( $args['pt'] ) || ( isset( $args['pt'] ) && $args['pt'] == '0' ) ) { $classes[] = "pt-".sanitize_html_class($args['pt']); }
	if ( ! empty( $args['pr'] ) || ( isset( $args['pr'] ) && $args['pr'] == '0' ) ) { $classes[] = "pr-".sanitize_html_class($args['pr']); }
	if ( ! empty( $args['pb'] ) || ( isset( $args['pb'] ) && $args['pb'] == '0' ) ) { $classes[] = "pb-".sanitize_html_class($args['pb']); }
	if ( ! empty( $args['pl'] ) || ( isset( $args['pl'] ) && $args['pl'] == '0' ) ) { $classes[] = "pl-".sanitize_html_class($args['pl']); }

	// border
	if ( ! empty( $args['border'] ) && ( $args['border']=='none' || $args['border']==='0') ) { $classes[] = "border-0"; }
	else if ( !empty( $args['border'] ) ) { $classes[] = "border border-".sanitize_html_class($args['border']); }

	// border radius type
	if ( ! empty( $args['rounded'] ) ) { $classes[] = sanitize_html_class($args['rounded']); }

	// border radius size
	if ( ! empty( $args['rounded_size'] ) ) {
		$classes[] = "rounded-" . sanitize_html_class( $args['rounded_size'] );

		// if we set a size then we need to remove "rounded" if set
		if ( ( $key = array_search( "rounded", $classes ) ) !== false ) {
			unset( $classes[ $key ] );
		}
	}

	// shadow
	if ( ! empty( $args['shadow'] ) ) { $classes[] = sanitize_html_class($args['shadow']); }

	// background
	if ( ! empty( $args['bg'] ) ) { $classes[] = "bg-".sanitize_html_class($args['bg']); }

	// text_color
	if ( ! empty( $args['text_color'] ) ) { $classes[] = "text-".sanitize_html_class($args['text_color']); }

	// text_align
	if ( ! empty( $args['text_align'] ) ) { $classes[] = sanitize_html_class($args['text_align']); }

	$classes = implode( " ", $classes );

	if ( ! empty( $classes ) && $aui_bs5 ) {
		$classes = str_replace( array( '-left', '-right', 'ml-', 'mr-', 'pl-', 'pr-' ), array( '-start', '-end', 'ms-', 'me-', 'ps-', 'pe-' ), $classes );
	}

	return $classes;
}

/**
 * Check if we are on the archive item edit page.
 *
 * @return bool
 */
function geodir_is_archive_item_template_page(){
	global $geodirectory;

	$result = false;

	$post_id = is_admin() && !empty($_REQUEST['post_id']) ? absint($_REQUEST['post_id']) : 0;

	if ( $post_id ) {
		// main post check
		if( !empty( $geodirectory->settings['page_archive_item'] ) && $geodirectory->settings['page_archive_item'] == $post_id ){
			$result = true;
		}

		// check all CPTs
		if( !$result && !empty($geodirectory->settings['post_types'])){

			foreach($geodirectory->settings['post_types'] as $post_type){
				if ( ! empty( $post_type['page_archive_item'] ) && $post_type['page_archive_item'] == $post_id ) {
					$result = true;
					break;
				}
			}
		}
	}

	return $result;
}

/**
 * Tell AyeCode UI to load on certain admin pages.
 *
 * @param $screen_ids
 *
 * @return array
 */
function geodir_add_aui_screens( $screen_ids ) {
	// Load on these pages if set
	if ( geodir_design_style() || ( is_admin() && ! wp_doing_ajax() ) ) {
		$screen_ids = array_merge( $screen_ids, geodir_get_screen_ids() );
	}

	// AUI is also needed for setup wizard
	$screen_ids[] = 'gd-setup';

	return $screen_ids;
}
add_filter( 'aui_screen_ids', 'geodir_add_aui_screens' );

/**
 * Create a template.
 *
 * @since 2.2.4
 *
 * @global object $wpdb WordPress Database object.
 * @global object $current_user Current user object.
 *
 * @param string $option The option meta key.
 * @param array  $args Template arguments.
 * @param bool   $update_option True to update option.
 * @return int|WP_Error Post ID on success and WP Error on fail.
 */
function geodir_create_wp_template( $option, $args = array(), $update_option = true ) {
	global $wpdb, $current_user;

	$defaults = array(
		'post_status'    => 'publish',
		'post_type'      => 'wp_template',
		'post_author'    => $current_user->ID,
		'post_name'      => '',
		'post_title'     => '',
		'post_content'   => '',
		'post_parent'    => 0,
		'comment_status' => 'closed',
		'tax_input'      => array(),
	);

	$page_data = wp_parse_args( $args, $defaults );

	$post_id = wp_insert_post( $page_data, true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	if ( absint( $post_id ) > 0 && $update_option ) {
		geodir_update_option( $option, absint( $post_id ) );
	}

	return $post_id;
}

/**
 * @param $limit
 * @param $type
 *
 * @return false|string
 */
function geodir_generate_lorem_ipsum($limit = 100, $type = 'characters') {
	// Predefined Lorem Ipsum text
	$loremIpsum = "Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.";

	// Return based on type
	if ($type === 'words') {
		$words = explode(' ', $loremIpsum);
		$words = array_slice($words, 0, $limit);
		return implode(' ', $words);
	} else {
		// Default to characters
		return substr($loremIpsum, 0, $limit);
	}
}

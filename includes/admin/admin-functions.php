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
function gd_get_screen_ids() {

	$screen_ids = array(
		'geodirectory_page_gd-settings',
		'geodirectory_page_gd-status',
		'geodirectory_page_gd-addons',
		'toplevel_page_geodirectory',
	);


	// Add the CPT screens
	$post_types = geodir_get_posttypes( 'names' );
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$screen_ids[] = $post_type; // CPT add new
			$screen_ids[] = 'edit-' . $post_type; // CPT view screen
			$screen_ids[] = 'edit-' . $post_type . '_tags'; // CPT tags screen
			$screen_ids[] = 'edit-' . $post_type . 'category'; // CPT category screen
			$screen_ids[] = $post_type . '_page_gd-cpt-settings'; // CPT settings page
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

	$taxonomy = $post_type . 'category';

	// WPML
	$is_wpml     = geodir_is_wpml();
	$active_lang = 'all';
	if ( $is_wpml ) {
		global $sitepress;
		$active_lang = $sitepress->get_current_language();

		if ( $active_lang != 'all' ) {
			$sitepress->switch_lang( 'all', true );
		}
	}
	// WPML

	$count_terms = wp_count_terms( $taxonomy, $args );

	// WPML
	if ( $is_wpml && $active_lang !== 'all' ) {
		global $sitepress;
		$sitepress->switch_lang( $active_lang, true );
	}
	// WPML
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
			"SELECT ID FROM " . $wpdb->posts . " WHERE post_name = %s LIMIT 1;",
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
		return $result;
	}

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
				$upgrade_notice .= '<div class="geodir_plugin_upgrade_notice">';
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
 * Check table column exist or not.
 *
 * @since 1.0.0
 * @package GeoDirectory
 * @global object $wpdb WordPress Database object.
 * @param string $db The table name.
 * @param string $column The column name.
 * @return bool If column exists returns true. Otherwise false.
 */
function geodir_column_exist($db, $column)
{
	global $wpdb;
	$exists = false;
	$columns = $wpdb->get_col("show columns from $db");
	foreach ($columns as $c) {
		if ($c == $column) {
			$exists = true;
			break;
		}
	}
	return $exists;
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
function geodir_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL")
{
	global $wpdb;
	$result = 0;// no rows affected
	if (!geodir_column_exist($db, $column)) {
		if (!empty($db) && !empty($column))
			$result = $wpdb->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
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
	return array( 'twentyseventeen', 'twentysixteen', 'twentyfifteen', 'twentyfourteen', 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten' );
}
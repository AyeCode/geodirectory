<?php
/**
 * Uninstall GeoDirectory
 *
 * Uninstalling GeoDirectory deletes data, tables and options.
 *
 * @package GeoDirectory
 * @since 1.6.9
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

if ( !defined( 'GEODIRECTORY_VERSION' ) ) {
    // Load plugin file.
    include_once( 'geodirectory.php' );
}

// Clear schedules
wp_clear_scheduled_hook( 'geodirectory_tracker_send_event' );
wp_clear_scheduled_hook( 'geodir_plugin_background_installer' );

if ( geodir_get_option( 'admin_uninstall' ) ) {
    include_once dirname( __FILE__ ) . '/includes/admin/class-geodir-admin-install.php';

	$wpdb->hide_errors();

	$post_types = array_keys( (array) geodir_get_option( 'post_types' ) );

	// Pages.
	wp_delete_post( geodir_get_option( 'page_location' ), true );
	wp_delete_post( geodir_get_option( 'page_add' ), true );
	wp_delete_post( geodir_get_option( 'page_search' ), true );
	wp_delete_post( geodir_get_option( 'page_terms_conditions' ), true );
	wp_delete_post( geodir_get_option( 'page_details' ), true );
	wp_delete_post( geodir_get_option( 'page_archive' ), true );
	wp_delete_post( geodir_get_option( 'page_archive_item' ), true );

	// Tables.
	GeoDir_Admin_Install::drop_tables();

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'gd\_user\_favourite\_post%';" );

	// Delete posts.
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type LIKE '{$post_type}';" );

			// Delete post menu
			$wpdb->query( "DELETE posts FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id WHERE posts.post_type= 'nav_menu_item' AND meta.meta_key = '_menu_item_object' AND meta.meta_value = '{$post_type}';" );
			$wpdb->query( "DELETE posts FROM {$wpdb->posts} posts LEFT JOIN {$wpdb->postmeta} meta ON posts.ID = meta.post_id WHERE posts.post_type= 'nav_menu_item' AND meta.meta_key = '_menu_item_url' AND meta.meta_value LIKE '%listing_type={$post_type}%';" );
		}
	}

	// Delete post meta.
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

	// Delete orphan attachment.
	$wpdb->query( "DELETE post1 FROM {$wpdb->posts} post1 LEFT JOIN {$wpdb->posts} post2 ON post1.post_parent = post2.ID WHERE post1.post_parent > 0 AND post1.post_type = 'attachment' AND post2.ID IS NULL;" );

	// Delete term taxonomies.
	if ( ! empty( $post_types ) ) {
		foreach ( $post_types as $post_type ) {
			$wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy LIKE '{$post_type}category' OR taxonomy LIKE '{$post_type}_tags';" );
		}
	}

	// Delete orphan relationships.
	$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL;" );

	// Delete orphan terms.
	$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

	// Delete orphan term meta.
	$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

	// Comments
	$wpdb->query( "DELETE comments FROM {$wpdb->comments} AS comments LEFT JOIN {$wpdb->posts} AS posts ON posts.ID = comments.comment_post_ID WHERE posts.ID IS NULL;" );
	$wpdb->query( "DELETE meta FROM {$wpdb->commentmeta} meta LEFT JOIN {$wpdb->comments} comments ON comments.comment_ID = meta.comment_id WHERE comments.comment_ID IS NULL;" );

	// Options
	// Delete settings
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'geodir_settings' OR option_name LIKE 'geodirectory\_%' OR ( option_name LIKE 'gd\_%' AND option_name LIKE '%category\_installed' );" );

	// Delete widgets
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'widget\_gd\_%';" );

	// Delete transients
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient__gd_activation_redirect' OR option_name LIKE '\_transient\_geodir\_%' OR option_name LIKE '\_transient\_gd_addons_section\_%' OR option_name LIKE '\_transient\_gd_avg\_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout__gd_activation_redirect' OR option_name LIKE '\_timeout\_transient\_geodir\_%' OR option_name LIKE '\_timeout\_transient\_gd_addons_section\_%' OR option_name LIKE '\_timeout\_transient\_gd_avg\_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient__gd_activation_redirect' OR option_name LIKE '\_site\_transient\_geodir\_%' OR option_name LIKE '\_site\_transient\_gd_addons_section\_%' OR option_name LIKE '\_site\_transient\_gd_avg\_%'" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_site_transient_timeout__gd_activation_redirect' OR option_name LIKE '\_site\_transient\_timeout\_geodir\_%' OR option_name LIKE '\_site\_transient\_timeout\_gd_addons_section\_%' OR option_name LIKE '\_site\_transient\_timeout\_gd_avg\_%'" );

	// Clear any cached data that has been removed.
	wp_cache_flush();
}

// Delete Fast AJAX mu-plugin file.
if ( defined( 'WPMU_PLUGIN_DIR' ) && is_file( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' ) && file_exists( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' ) ) {
	unlink( WPMU_PLUGIN_DIR . '/geodir-fast-ajax.php' );
}

// remove crons
$timestamp = wp_next_scheduled('geodir_weekly_cache_clear_hook');
if ($timestamp) {
	wp_unschedule_event($timestamp, 'geodir_weekly_cache_clear_hook');
}

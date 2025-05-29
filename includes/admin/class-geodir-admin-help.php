<?php
/**
 * Add some content to the help tab
 *
 * @package     GeoDir/Admin
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( class_exists( 'GeoDir_Admin_Help', false ) ) {
	return new GeoDir_Admin_Help();
}

/**
 * GeoDir_Admin_Help Class.
 */
class GeoDir_Admin_Help {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'current_screen', array( $this, 'add_tabs' ), 50 );
	}

	/**
	 * Add help tabs.
	 */
	public function add_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || ! in_array( $screen->id, geodir_get_screen_ids() ) ) {
			return;
		}

		$screen->add_help_tab( array(
			'id'        => 'geodir_help_support_tab',
			'title'     => __( 'Help &amp; Support', 'geodirectory' ),
			'content'   =>
				'<h2>' . __( 'Help &amp; Support', 'geodirectory' ) . '</h2>' .
				'<p>' . sprintf(
					/* translators: %s: Documentation URL */
					__( 'Should you need help understanding, using, or extending GeoDirectory, <a href="%s">please read our documentation</a>. You will find all kinds of resources including snippets, tutorials and much more.', 'geodirectory' ),
					'https://wpgeodirectory.com/documentation/?utm_source=helptab&utm_medium=product&utm_content=docs&utm_campaign=geodirectoryplugin'
				) . '</p>' .
				'<p>' . sprintf(
					/* translators: %s: Forum URL */
					__( 'For further assistance with GeoDirectory core or with premium extensions sold by GeoDirectory you can open a <a href="%1$s">support ticket</a>.', 'geodirectory' ),
					'https://wpgeodirectory.com/support/?utm_source=helptab&utm_medium=product&utm_content=forum&utm_campaign=geodirectoryplugin'
				) . '</p>' .
				'<p>' . __( 'Before asking for help we recommend checking the system status page to identify any problems with your configuration.', 'geodirectory' ) . '</p>' .
				'<p><a href="' . admin_url( 'admin.php?page=gd-status' ) . '" class="button button-primary">' . __( 'System status', 'geodirectory' ) . '</a> <a href="https://wpgeodirectory.com/support/?utm_source=helptab&utm_medium=product&utm_content=forum&utm_campaign=geodirectoryplugin" class="button">' . __( 'GeoDirectory support', 'geodirectory' ) . '</a></p>',
		) );

		$screen->add_help_tab( array(
			'id'        => 'geodir_help_bugs_tab',
			'title'     => __( 'Found a bug?', 'geodirectory' ),
			'content'   =>
				'<h2>' . __( 'Found a bug?', 'geodirectory' ) . '</h2>' .
				/* translators: 1: GitHub issues URL 2: GitHub contribution guide URL 3: System status report URL */
				'<p>' . sprintf( __( 'If you find a bug within GeoDirectory core you can create a ticket via <a href="%1$s">Github issues</a>. Ensure you read the <a href="%2$s">contribution guide</a> prior to submitting your report. To help us solve your issue, please be as descriptive as possible and include your <a href="%3$s">system status report</a>.', 'geodirectory' ), 'https://github.com/AyeCode/geodirectory/issues?state=open', 'https://github.com/AyeCode/geodirectory/blob/master/CONTRIBUTING.md', admin_url( 'admin.php?page=gd-status' ) ) . '</p>' .
				'<p><a href="https://github.com/AyeCode/geodirectory/issues?state=open" class="button button-primary">' . __( 'Report a bug', 'geodirectory' ) . '</a> <a href="' . admin_url( 'admin.php?page=gd-status' ) . '" class="button">' . __( 'System status', 'geodirectory' ) . '</a></p>',

		) );

		$screen->add_help_tab( array(
			'id'        => 'geodir_help_onboard_tab',
			'title'     => __( 'Setup wizard', 'geodirectory' ),
			'content'   =>
				'<h2>' . __( 'Setup wizard', 'geodirectory' ) . '</h2>' .
				'<p>' . __( 'If you need to access the setup wizard again, please click on the button below.', 'geodirectory' ) . '</p>' .
				'<p>' . __( 'Running the wizard again will not delete your current settings. You will have the option to change any current settings.', 'geodirectory' ) . '</p>' .
				'<p><a href="' . admin_url( 'index.php?page=gd-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'geodirectory' ) . '</a></p>',

		) );

		$screen->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'geodirectory' ) . '</strong></p>' .
			'<p><a href="https://wpgeodirectory.com/?utm_source=helptab&utm_medium=product&utm_content=about&utm_campaign=geodirectoryplugin" target="_blank">' . __( 'About GeoDirectory', 'geodirectory' ) . '</a></p>' .
			'<p><a href="https://wordpress.org/plugins/geodirectory/" target="_blank">' . __( 'WordPress.org project', 'geodirectory' ) . '</a></p>' .
			'<p><a href="https://github.com/AyeCode/geodirectory" target="_blank">' . __( 'Github project', 'geodirectory' ) . '</a></p>' .
			'<p><a href="https://wpgeodirectory.com/downloads/category/themes/?utm_source=helptab&utm_medium=product&utm_content=geodirthemes&utm_campaign=geodirectoryplugin" target="_blank">' . __( 'Official themes', 'geodirectory' ) . '</a></p>' .
			'<p><a href="https://wpgeodirectory.com/downloads/category/addons/?utm_source=helptab&utm_medium=product&utm_content=geodirextensions&utm_campaign=geodirectoryplugin" target="_blank">' . __( 'Official extensions', 'geodirectory' ) . '</a></p>'
		);
	}
}

return new GeoDir_Admin_Help();

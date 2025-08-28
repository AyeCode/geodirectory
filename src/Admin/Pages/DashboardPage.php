<?php
/**
 * GeoDirectory Admin Dashboard Page
 *
 * @package GeoDirectory\Admin\Pages
 * @since   3.0.0
 * @author  AyeCode Ltd
 */

// Use strict types for better code quality.
declare( strict_types = 1 );

namespace AyeCode\GeoDirectory\Admin\Pages;
use AyeCode\GeoDirectory\Core\Plugin;
/**
 * Handles the rendering of the main admin dashboard page.
 *
 * @since 3.0.0
 */
final class DashboardPage {
	/**
	 * Renders the dashboard page content.
	 *
	 * This method is called by the `add_menu_page` function in the Setup class.
	 * Its job is to gather any necessary data and then load the corresponding
	 * view file to display the page.
	 *
	 * @return void
	 */
	public function render(): void {
		// In the future, you can gather data here to pass to the view.
		// For example: $stats = StatsRepository::get_dashboard_stats();

		// We keep the HTML output in a separate 'view' file for organization.
		$view_file = Plugin::path( 'src/Admin/views/dashboard-page.php' );

		if ( file_exists( $view_file ) ) {
			include $view_file;
		} else {
			// Fallback in case the view file is missing.
			echo '<div class="wrap"><h1>' . esc_html__( 'GeoDirectory Dashboard', 'geodirectory' ) . '</h1><p>Dashboard view file not found.</p></div>';
		}
	}
}

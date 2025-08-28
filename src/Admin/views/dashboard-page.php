<?php
/**
 * The main view file for the GeoDirectory admin dashboard.
 *
 * This file is included by the render() method in the DashboardPage class.
 *
 * @package GeoDirectory
 * @since 3.0.0
 */

// Direct access protection.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap geodirectory-dashboard">
	<h1><?php echo esc_html__( 'GeoDirectory Dashboard', 'geodirectory' ); ?></h1>

	<div class="welcome-panel">
		<div class="welcome-panel-content">
			<h2><?php esc_html_e( 'Welcome to GeoDirectory!', 'geodirectory' ); ?></h2>
			<p class="about-description">
				<?php esc_html_e( 'Thank you for choosing GeoDirectory. Here are a few links to help you get started.', 'geodirectory' ); ?>
			</p>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<h4><?php esc_html_e( 'Get Started', 'geodirectory' ); ?></h4>
					<ul>
						<li><a href="#" class="welcome-icon welcome-add-page"><?php esc_html_e( 'Configure Settings', 'geodirectory' ); ?></a></li>
						<li><a href="#" class="welcome-icon welcome-add-post"><?php esc_html_e( 'Add a Listing', 'geodirectory' ); ?></a></li>
						<li><a href="#" class="welcome-icon welcome-view-site"><?php esc_html_e( 'View Your Directory', 'geodirectory' ); ?></a></li>
					</ul>
				</div>
				<div class="welcome-panel-column">
					<h4><?php esc_html_e( 'Next Steps', 'geodirectory' ); ?></h4>
					<ul>
						<li><a href="#" class="welcome-icon welcome-learn-more"><?php esc_html_e( 'Read the Documentation', 'geodirectory' ); ?></a></li>
						<li><a href="#" class="welcome-icon welcome-widgets-menus"><?php esc_html_e( 'Browse Extensions', 'geodirectory' ); ?></a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>

	<?php // @todo Add dashboard widgets for stats, latest reviews, etc. ?>
</div>

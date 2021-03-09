<?php
/**
 * Admin View: Page - Status
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_tab = ! empty( $_REQUEST['tab'] ) ? sanitize_title( $_REQUEST['tab'] ) : 'status';
$tabs        = array(
	'status' => __( 'System status', 'geodirectory' ),
	'tools'  => __( 'Tools', 'geodirectory' ),
);
$tabs        = apply_filters( 'geodir_admin_status_tabs', $tabs );
?>
<div class="wrap geodirectory">
	<nav class="nav-tab-wrapper gd-nav-tab-wrapper">
		<?php
			foreach ( $tabs as $name => $label ) {
				echo '<a href="' . admin_url( 'admin.php?page=gd-status&tab=' . $name ) . '" class="nav-tab ';
				if ( $current_tab == $name ) {
					echo 'nav-tab-active';
				}
				echo '">' . $label . '</a>';
			}
		?>
	</nav>
	<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
	<?php
		switch ( $current_tab ) {
			case "tools" :
				wp_enqueue_script( 'jquery-ui-progressbar' );

				GeoDir_Admin_Status::status_tools();
			break;
			default :
				if ( array_key_exists( $current_tab, $tabs ) && has_action( 'geodir_admin_status_content_' . $current_tab ) ) {
					do_action( 'geodir_admin_status_content_' . $current_tab );
				} else {
					GeoDir_Admin_Status::status_report();
				}
			break;
		}
	?>
</div>

<?php
/**
 * Admin View: Settings
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="wrap geodirectory" style="font-size: 14px;">
	<form class="containerx" method="<?php echo esc_attr( apply_filters( 'geodir_settings_form_method_tab_' . $current_tab, 'post' ) ); ?>" id="mainform" action="" enctype="multipart/form-data">
		<nav class="nav-tab-wrapper gd-nav-tab-wrapper">
			<?php
			$cpt = isset($_REQUEST['post_type']) ? sanitize_title( $_REQUEST['post_type'] ) : 'gd_place';

			foreach ( $tabs as $name => $label ) {
					if(isset($_REQUEST['page']) && $_REQUEST['page']== $cpt.'-settings'){
						echo '<a href="' . admin_url( 'edit.php?post_type=' . $cpt . '&page='.$cpt.'-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
					}else{
						$label = isset($label['label']) ? $label['label'] : $label;
						echo '<a href="' . admin_url( 'admin.php?page=gd-settings&tab=' . $name ) . '" class="nav-tab ' . ( $current_tab == $name ? 'nav-tab-active' : '' ) . '">' . $label . '</a>';
					}
				}
				do_action( 'geodir_settings_tabs' );
			?>
		</nav>
		<div class="gd-settings-wrap bsui">
		<h1 class="screen-reader-text"><?php echo esc_html( $tabs[ $current_tab ] ); ?></h1>
		<?php
			do_action( 'geodir_sections_' . $current_tab );

			self::show_messages();

			do_action( 'geodir_settings_' . $current_tab );
			do_action( 'geodir_settings_tabs_' . $current_tab ); // @deprecated hook
		?>
		<p class="submit mt-2 text-right text-end">
			<?php if ( empty( $GLOBALS['hide_save_button'] ) ) : ?>
				<input name="save" class="btn btn-primary geodir-save-button" type="submit" value="<?php esc_attr_e( 'Save changes', 'geodirectory' ); ?>" />
			<?php endif; ?>
			<?php wp_nonce_field( 'geodirectory-settings' ); ?>
		</p>
		</div>
	</form>
</div>

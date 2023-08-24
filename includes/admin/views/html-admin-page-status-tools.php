<?php
/**
 * Admin View: Page - Status Tools
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<form method="post" action="options.php">
	<?php settings_fields( 'geodir_status_settings_fields' ); ?>
	<table class="gd_status_table gd_status_table--tools widefat" cellspacing="0">
		<tbody class="tools">
			<?php foreach ( $tools as $action => $tool ) : ?>
				<tr class="<?php echo sanitize_html_class( $action ); ?>" id="geodir_tool_<?php echo sanitize_html_class( $action ); ?>">
					<th>
						<strong class="name"><?php echo esc_html( $tool['name'] ); ?></strong>
						<p class="description"><?php echo wp_kses_post( $tool['desc'] ); ?></p>
						<?php do_action( 'geodir_status_tool_after_desc', $action, $tool ); ?>
					</th>
					<td class="run-tool">
						<a href="<?php echo ( ! empty( $tool['link'] ) ? $tool['link'] : wp_nonce_url( admin_url( 'admin.php?page=gd-status&tab=tools&action=' . $action ), 'debug_action' ) ); ?>" class="button button-large <?php echo esc_attr( $action ); ?>"><?php echo esc_html( $tool['button'] ); ?></a>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</form>

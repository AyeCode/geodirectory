<?php
/**
 * Auth form login
 *
 * @author  AyeCode
 * 
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<?php do_action( 'geodir_auth_page_header' ); ?>

<h1>
	<?php
	/* translators: %s: app name */
	printf( esc_html__( '%s would like to connect to your store', 'geodirectory' ), esc_html( $app_name ) );
	?>
</h1>

<?php do_action( 'geodir_auth_page_print_notices' ); ?>

<p>
	<?php
	/* translators: %1$s: app name, %2$s: URL */
	echo wp_kses_post( sprintf( __( 'To connect to %1$s you need to be logged in. Log in to your store below, or <a href="%2$s">cancel and return to %1$s</a>', 'geodirectory' ), esc_html( geodir_clean( $app_name ) ), esc_url( $return_url ) ) );
	?>
</p>

<form method="post" class="geodir-auth-login">
	<p class="form-row form-row-wide">
		<label for="username"><?php esc_html_e( 'Username or email address', 'geodirectory' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="username" id="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( $_POST['username'] ) : ''; ?>" /><?php //@codingStandardsIgnoreLine ?>
	</p>
	<p class="form-row form-row-wide">
		<label for="password"><?php esc_html_e( 'Password', 'geodirectory' ); ?> <span class="required">*</span></label>
		<input class="input-text" type="password" name="password" id="password" />
	</p>
	<p class="geodir-auth-actions">
		<?php wp_nonce_field( 'geodir-auth-login' ); ?>
		<button type="submit" class="button button-large button-primary geodir-auth-login-button" name="login" value="<?php esc_attr_e( 'Login', 'geodirectory' ); ?>"><?php esc_html_e( 'Login', 'geodirectory' ); ?></button>
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect_url ); ?>" />
	</p>
</form>

<?php do_action( 'geodir_auth_page_footer' ); ?>

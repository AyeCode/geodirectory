<?php
/**
 * Auth form grant access
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

<h1><?php printf( __( '%s would like to connect to your store', 'geodirectory' ), esc_html( $app_name ) ); ?></h1>

<?php do_action( 'geodir_auth_page_print_notices' ); ?>

<p><?php printf( __( 'This will give "%1$s" %2$s access which will allow it to:', 'geodirectory' ), '<strong>' . esc_html( $app_name ) . '</strong>', '<strong>' . esc_html( $scope ) . '</strong>' ); ?></p>

<ul class="geodir-auth-permissions">
	<?php foreach ( $permissions as $permission ) : ?>
		<li><?php echo esc_html( $permission ); ?></li>
	<?php endforeach; ?>
</ul>

<div class="geodir-auth-logged-in-as">
	<?php echo get_avatar( $user->ID, 70 ); ?>
	<p><?php printf( __( 'Logged in as %s', 'geodirectory' ), esc_html( $user->display_name ) ); ?> <a href="<?php echo esc_url( $logout_url ); ?>" class="geodir-auth-logout"><?php _e( 'Logout', 'geodirectory' ); ?></a>
</div>

<p class="geodir-auth-actions">
	<a href="<?php echo esc_url( $granted_url ); ?>" class="button button-primary geodir-auth-approve"><?php _e( 'Approve', 'geodirectory' ); ?></a>
	<a href="<?php echo esc_url( $return_url ); ?>" class="button geodir-auth-deny"><?php _e( 'Deny', 'geodirectory' ); ?></a>
</p>

<?php do_action( 'geodir_auth_page_footer' ); ?>

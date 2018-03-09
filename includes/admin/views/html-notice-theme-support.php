<?php
/**
 * Admin View: Notice - Theme Support
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div id="message" class="notice-warning notice notice-alt geodir-message">
	<p><?php printf( __( '<strong>Your theme does not declare GeoDirectory support</strong> &#8211; Please read our <a href="%1$s" target="_blank">integration</a> guide or check out our <a href="%2$s" target="_blank">Directory Starter</a> theme which is totally free to download and designed specifically for use with GeoDirectory.', 'geodirectory' ), esc_url( apply_filters( 'geodir_docs_url', 'https://wpgeodirectory.com/docs/core-theme/', 'theme-compatibility' ) ), esc_url( 'https://wpgeodirectory.com/downloads/directory-starter/' ) ); ?></p>
	<p class="submit">
		<a href="https://wpgeodirectory.com/downloads/directory-starter/?utm_source=notice&amp;utm_medium=product&amp;utm_content=directory-starter&amp;utm_campaign=geodirectoryplugin" class="button-primary" target="_blank"><?php _e( 'Read more about Directory Starter', 'geodirectory' ); ?></a>
		<a href="<?php echo esc_url( apply_filters( 'geodir_docs_url', 'https://wpgeodirectory.com/docs/core-theme/?utm_source=notice&utm_medium=product&utm_content=themecompatibility&utm_campaign=geodirectoryplugin' ) ); ?>" class="button-secondary" target="_blank"><?php _e( 'Theme integration guide', 'geodirectory' ); ?></a>
		<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'gd-hide-notice', 'theme_support' ), 'geodir_hide_notices_nonce', '_gd_notice_nonce' ) ); ?>" class="button-secondary"><?php _e( 'Dismiss', 'geodirectory' ); ?></a>
	</p>
</div>
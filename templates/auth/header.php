<?php
/**
 * Auth header
 *
 * @author  AyeCode
 * 
 * @version 2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta name="viewport" content="width=device-width" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="robots" content="noindex, nofollow" />
	<title><?php _e( 'Application authentication request', 'geodirectory' ); ?></title>
	<?php wp_admin_css( 'install', true ); ?>
	<link rel="stylesheet" href="<?php echo esc_url( str_replace( array( 'http:', 'https:' ), '', geodir_plugin_url() ) . '/assets/css/auth.css' ); ?>" type="text/css" />
</head>
<body class="geodir-auth wp-core-ui">
	<h1 id="geodir-logo"><img src="<?php echo geodir_plugin_url(); ?>/assets/images/geo-logoalter.png" alt="GeoDirectory" /></h1>
	<div class="geodir-auth-content">

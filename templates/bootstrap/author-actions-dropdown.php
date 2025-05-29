<?php
/**
 * Author Actions Dropdown
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/author-actions-dropdown.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.8.107
 *
 * @param array $author_actions The author actions.
 * @param string $wrap_class The wrapper class styles.
 * @param string $btn_class The button class.
 * @param array $args Widget args.
 */

defined( 'ABSPATH' ) || exit;

global $aui_bs5;

if ( ! empty( $author_actions ) ) {
	$dropdown_id = '_' . uniqid();

	echo '<div class="gd-author-actions ' . esc_attr( $wrap_class ) . '" role="btn-group" aria-label="' . __( "Author Actions", "geodirectory" ) . '">';

	if ( $args['display'] == 'dropdown-dots' ) {
		echo '<a href="javascript:void(0)" class="dropdown h5 m-0 text-' . sanitize_html_class( $args['color'] ) . '" data-' . ( $aui_bs5 ? 'bs-' : '' ) . 'toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="' . esc_attr( $dropdown_id ) . '" title="' . esc_attr__( "Author Actions", "geodirectory" ) . '"><i class="fas fa-ellipsis-v fa-fw"></i></a>';
	} else {
		echo '<button id="' . esc_attr( $dropdown_id ) . '" type="button" class="btn btn-' . sanitize_html_class( $args['color'] ) . ' text-' . sanitize_html_class( $args['text_color'] ) . ' btn-' . sanitize_html_class( $args['alignment'] ) . ' btn-' . sanitize_html_class( $args['size'] ) . ' dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-bars mr-1 me-1" aria-hidden="true"></i> ' . __( "Author Actions", "geodirectory" ) , '</button>';
	}

	echo '<div class="dropdown-menu' . ( $args['display'] == 'dropdown-dots' ? ' dropdown-caret-0' : '' ) . '" aria-labelledby="' . esc_attr( $dropdown_id ) . '">';

	foreach ( $author_actions as $type => $action ) {
		$button_class = 'gd_user_action ' . esc_attr( $type ) . '_link dropdown-item';

		$onclick = ! empty( $action['onclick'] ) ? $action['onclick'] : '';

		echo aui()->button(
			array(
				'type'      => 'a',
				'class'     => $button_class,
				'content'   => $action['title'],
				'icon'      => ! empty( $action['icon'] ) ? $action['icon'] : '',
				'href'      => ! empty( $action['url'] ) ? $action['url'] : '#',
				'onclick'   => $onclick
			)
		);
	}

	echo "</div></div>";
}
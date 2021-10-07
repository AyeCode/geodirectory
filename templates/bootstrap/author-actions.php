<?php
/**
 * Author Actions
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/author-actions.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.1.5
 *
 * @param array $author_actions The author actions.
 * @param string $wrap_class The wrapper class styles.
 * @param string $btn_class The button class.
 * @param array $args Widget args.
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $author_actions ) ) {
	echo '<div class="gd-author-actions ' . $wrap_class . '" role="group" aria-label="' . __( "Author Actions", "geodirectory" ) . '">';

	foreach ( $author_actions as $type => $action ) {
		$button_class = 'gd_user_action ' . esc_attr( $type ) . '_link btn';
		$button_class .= ' '. $btn_class;

		// Button Size
		if ( ! empty( $action['size'] ) ) {
			$button_class .= ' btn-' . sanitize_html_class( $action['size'] );
		} elseif ( ! empty( $args['size'] ) ) {
			$button_class .= ' btn-' . sanitize_html_class( $args['size'] );
		}

		// Button Color
		if ( ! empty( $action['color'] ) ) {
			$button_class .= ' btn-' . sanitize_html_class( $action['color'] );
		} elseif ( ! empty( $args['color'] ) ) {
			$button_class .= ' btn-' . sanitize_html_class( $args['color'] );
		} else {
			$button_class .= ' btn-primary';
		}

		// Text Color
		if ( ! empty( $action['text_color'] ) ) {
			$button_class .= ' text-' . sanitize_html_class( $action['text_color'] );
		} elseif ( ! empty( $args['text_color'] ) ) {
			$button_class .= ' text-' . sanitize_html_class( $args['text_color'] );
		} else {
			$button_class .= ' text-white';
		}

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

	echo "</div>";
}
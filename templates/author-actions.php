<?php
/**
 * Author Actions (default)
 *
 * @ver 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if(!empty($author_actions)) {
	echo '<div class="geodir_post_meta  gd-author-actions">';

	foreach ( $author_actions as $type => $action ) {
		echo '<span class="gd_user_action ' . esc_attr( $type ) . '_link">';
		echo ! empty( $action['icon'] ) ? '<i class="' . $action['icon'] . '" aria-hidden="true"></i> ' : '';
		$onclick = ! empty( $action['onclick'] ) ? 'onclick="' . $action['onclick'] . '"' : '';
		echo ! empty( $action['url'] ) ? '<a href="' . $action['url'] . '"  ' . $onclick . '>' : '';
		echo ! empty( $action['title'] ) ? $action['title'] : '';
		echo ! empty( $action['url'] ) ? '</a>' : '';
		echo '</span>';
	}

	echo "</div>";
}
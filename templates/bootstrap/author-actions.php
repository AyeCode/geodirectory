<?php
/**
 * Author Actions
 *
 * @ver 1.0.0
 *
 * @param array $author_actions The author actions.
 * @param string $wrap_class The wrapper class styles.
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if(!empty($author_actions)) {
    echo '<div class="gd-author-actions '.$wrap_class.'" role="group" aria-label="'.__("Author Actions","geodirectory").'">';

    foreach ( $author_actions as $type => $action ) {
        $btn_color = $type == 'delete' ? 'btn-danger' : 'btn-primary';
        $button_class = 'gd_user_action my-1 ' . esc_attr( $type ) . '_link btn btn-sm text-white '.$btn_color;
        $onclick = ! empty( $action['onclick'] ) ? $action['onclick'] : '';

        echo aui()->button(
            array(
                'type'       => 'a',
                'class'      =>  $button_class,
                'content'    => $action['title'],
                'icon'      => !empty($action['icon']) ? $action['icon'] : '',
                'href'       => !empty($action['url']) ? $action['url'] : '#',
                'onclick'   => $onclick
            )
        );
    }

    echo "</div>";
}
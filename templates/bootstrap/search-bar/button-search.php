<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var string $default_search_button_label The search button label text or font awesome class.
 * @var boolean $fa_class If a font awesome class is being used as the button text.
 */
?>
<div class='gd-search-field-search col-auto flex-grow-1'>
	<div class='form-group'>
		<?php
		echo aui()->button(
			array(
				'type'       => 'button',
				'class'      => 'geodir_submit_search btn btn-primary w-100 ',
				'content'    => $fa_class ? '<i class="fas ' . esc_attr( $default_search_button_label ) . '" aria-hidden="true"></i><span class="sr-only">' . __( 'Search', 'geodirectory' ) . '</span>' : __( $default_search_button_label, 'geodirectory' ) . '<span class="sr-only">' . $default_search_button_label . '</span>',
				'data-title' => esc_attr__( $default_search_button_label, 'geodirectory' ),
				'aria-label' => $fa_class ? __( 'Search', 'geodirectory' ) : esc_attr__( $default_search_button_label, 'geodirectory' ),
			)
		);
		?>
	</div>
</div>
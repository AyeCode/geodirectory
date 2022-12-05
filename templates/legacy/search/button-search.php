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
<button class="geodir_submit_search" data-title="<?php esc_attr_e( $default_search_button_label ,'geodirectory'); ?>" aria-label="<?php esc_attr_e( $default_search_button_label ,'geodirectory'); ?>"><?php if($fa_class){echo '<i class="fas '.esc_attr($default_search_button_label).'" aria-hidden="true"></i><span class="sr-only visually-hidden">' . __( 'Search', 'geodirectory' ). '</span>';}else{ echo __( $default_search_button_label ,'geodirectory') . '<span class="sr-only visually-hidden">' . $default_search_button_label . '</span>'; }?></button>

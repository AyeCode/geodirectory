<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Variables.
 *
 * @var string $near The current near search term.
 * @var string $default_near_text The placeholder text.
 * @var string $near_class The near class.
 */
?>
<div class='gd-search-field-near <?php echo $near_class;?> col-auto flex-fill' style="flex-grow: 9999 !important;">
	<?php
	do_action( 'geodir_before_search_near_input' );

	$input_group_html = '<span class="geodir-search-input-label hover-swap text-muted" onclick="jQuery(\'.snear\').val(\'\').trigger(\'change\').trigger(\'keyup\');jQuery(\'.sgeo_lat,.sgeo_lon\').val(\'\');">';
	$input_group_html .= '<i class="fas fa-map-marker-alt hover-content-original"></i>';
	$input_group_html .= '<i class="fas fa-times geodir-search-input-label-clear hover-content c-pointer" title="' . __( 'Clear field', 'geodirectory' ) . '"></i>';
	$input_group_html .= '</span>';

	echo aui()->input(
		apply_filters('geodir_search_near_input_args',array(
			'value'                   => $near,
			'name'                    => 'snear',
			'placeholder'             => esc_html__( $default_near_text, 'geodirectory' ),
			'class'                   => 'snear pl-4 w-100',
			'label'                   => esc_html__( $default_near_text, 'geodirectory' ),
			'label_class'             => 'sr-only',
			'extra_attributes'        => array(
				'onkeydown'    => 'javascript: if(event.keyCode == 13) geodir_click_search(this);',
				'onClick'      => 'this.select();',
				'autocomplete' => 'off',
				'size' => 16, // this is the HTML minz size and affects flex wrapping
				'aria-label' => esc_html__( $default_near_text, 'geodirectory' )
			),
			'input_group_left'        => '<div class="input-group-text px-2 bg-transparent border-0">' . $input_group_html . '</div>',
			'input_group_left_inside' => true
		))
	);

	do_action( 'geodir_after_search_near_input' );
	?>
</div>
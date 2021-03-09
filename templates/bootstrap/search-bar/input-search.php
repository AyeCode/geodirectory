<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variables.
 *
 * @var string $search_term The current search term.
 * @var string $default_search_for_text The placeholder text.
 */
?>
<div class='gd-search-field-search col-auto flex-fill' style="flex-grow: 9999 !important;">
	<?php

	do_action('geodir_before_search_for_input');

	$input_group_html = '<span class="geodir-search-input-label hover-swap text-muted" onclick="jQuery(\'.search_text\').val(\'\').trigger(\'change\').trigger(\'keyup\');">';
	$input_group_html .= '<i class="fas fa-search hover-content-original"></i>';
	$input_group_html .= '<i class="fas fa-times geodir-search-input-label-clear hover-content c-pointer" title="' . __( 'Clear field', 'geodirectory' ) . '"></i>';
	$input_group_html .= '</span>';

	echo aui()->input(
		apply_filters('geodir_search_for_input_args',array(
			'value' =>  $search_term,
			'name'  => 's',
			'placeholder'  => esc_html__($default_search_for_text,'geodirectory'),
			'class' => 'search_text gd_search_text pl-4 w-100',
			'label'   => esc_html__($default_search_for_text,'geodirectory'),
			'label_type'       => 'hidden',//hidden, top, horizontal, floating
			'extra_attributes' => array(
				'onkeydown' =>  'if(event.keyCode == 13) geodir_click_search(this);',
				'onClick'   =>  'this.select();',
				'autocomplete'  =>  'off',
				'size' => 16, // this is the HTML minz size and affects flex wrapping,
				'aria-label' => esc_html__( $default_search_for_text, 'geodirectory' )
			),
			'input_group_left' => '<div class="input-group-text px-2 bg-transparent border-0">'.$input_group_html.'</div>',
			'input_group_left_inside' => true
		))
	);

	do_action('geodir_after_search_for_input');

	?>
</div>

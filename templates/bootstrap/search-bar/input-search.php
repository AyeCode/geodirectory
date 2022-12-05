<?php
/**
 * Main Search Input
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/search-bar/input-search.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.2.8
 *
 * Variables.
 *
 * @var string $search_term The current search term.
 * @var string $default_search_for_text The placeholder text.
 * @var string $input_wrap_class Input wrap CSS class.
 */

defined( 'ABSPATH' ) || exit;
global $aui_bs5;
?>
<div class='gd-search-field-search col-auto flex-fill<?php echo $input_wrap_class; echo $aui_bs5 ? ' px-0' : ''; ?>' style="flex-grow:9999 !important;" <?php echo geodir_conditional_field_attrs( array( 'type' => 'text' ), 's', 'text' ); ?>>
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
			'class' => 'search_text gd_search_text pl-4 ps-4 w-100',
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

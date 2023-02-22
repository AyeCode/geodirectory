<?php
/**
 * Near Search Input
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/search-bar/input-near.php.
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
 * @var string $near The current near search term.
 * @var string $default_near_text The placeholder text.
 * @var string $near_class The near wrapper classes.
 * @var string $near_input_extra The near wrapper extras.
 */

defined( 'ABSPATH' ) || exit;
global $aui_bs5,$geodir_search_widget_params;
?>
<div class='gd-search-field-near col-auto flex-fill
<?php
echo $near_class;
echo $aui_bs5 ? ' px-0' : '';
?>
' style="flex-grow:9999 !important;" <?php echo geodir_conditional_field_attrs( array( 'type' => 'text' ), 'near', 'text' ); ?>>
	<?php
	do_action( 'geodir_before_search_near_input' );

	$input_group_html  = '<span class="geodir-search-input-label hover-swap text-muted" onclick="jQuery(\'.snear\').val(\'\').trigger(\'change\').trigger(\'keyup\');jQuery(\'.sgeo_lat,.sgeo_lon\').val(\'\');">';
	$input_group_html .= '<i class="fas fa-map-marker-alt hover-content-original"></i>';
	$input_group_html .= '<i class="fas fa-times geodir-search-input-label-clear hover-content c-pointer" title="' . __( 'Clear field', 'geodirectory' ) . '"></i>';
	$input_group_html .= '</span>';

	$input_padding       = $aui_bs5 ? '' : ' pl-4';
	$input_group_padding = $aui_bs5 ? '' : ' px-2';

	echo aui()->input(
		apply_filters(
			'geodir_search_near_input_args',
			array(
				'value'                   => $near,
				'name'                    => 'snear',
				'size'                    => $geodir_search_widget_params['input_size'],
				'placeholder'             => esc_html__( $default_near_text, 'geodirectory' ),
				'class'                   => 'snear w-100 ' . $geodir_search_widget_params['main_search_inputs_class'] . $input_padding,
				'label'                   => esc_html__( $default_near_text, 'geodirectory' ),
				'label_class'             => 'sr-only visually-hidden',
				'extra_attributes'        => array(
					'onkeydown'    => 'javascript: if(event.keyCode == 13) geodir_click_search(this);',
					'onClick'      => 'this.select();',
					'autocomplete' => 'off',
					'size'         => 16, // this is the HTML minz size and affects flex wrapping
					'aria-label'   => esc_html__( $default_near_text, 'geodirectory' ),
				),
				'input_group_left'        => '<div class="input-group-text ' . $input_group_padding . ' bg-transparent border-0">' . $input_group_html . '</div>',
				'input_group_left_inside' => true,
				'form_group_class'        => $aui_bs5 ? ' ' : '', // remove bottom margin for BS5
			)
		)
	);

	do_action( 'geodir_after_search_near_input' );
	?>
</div>

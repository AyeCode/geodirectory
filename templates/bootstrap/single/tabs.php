<?php
/**
 * Single Listing Tabs
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/single/tabs.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://wpgeodirectory.com/documentation/article/how-tos/customizing-templates/
 * @package    GeoDirectory
 * @version    2.2
 *
 * @var string $default_search_button_label The search button label text or font awesome class.
 * @var boolean $fa_class If a font awesome class is being used as the button text.
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $tabs_array ) ) {

	$wrap_class = sd_build_aui_class( $args );

	echo '<div class="geodir-tabs ' . $wrap_class . '" id="gd-tabs">';

	// Tabs head
	if ( ! $args['show_as_list'] && $args['output'] != 'body' || ( $args['show_as_list'] && $args['output'] == 'head' ) ) {
		$greedy_menu_class = empty( $args['disable_greedy'] ) ? 'greedy' : '';

		$layout_shift_fix_class = $greedy_menu_class ? ' overflow-hidden flex-nowrap ' : '';
		$border_class           = $args['remove_separator_line'] ? '' : ' border-bottom';
		$tab_style              = empty( $args['tab_style'] ) ? 'nav-tabs mb-3 ' . $layout_shift_fix_class : 'nav-pills pb-3' . $border_class;

		echo '<nav class="geodir-tab-head ' . $greedy_menu_class . '"><ul class="nav list-unstyled ' . $tab_style . ' mx-0" id="gd-single-tabs" role="tablist">';

		$count = 0;
		foreach ( $tabs_array as $tab ) {
			// Tab icon
			$icon = empty( $args['hide_icon'] ) ? trim( $tab['tab_icon'] ) : '';
			if ( geodir_is_fa_icon( $icon ) ) {
				$tab_icon = '<i class="' . esc_attr( $icon ) . ' fa-fw mr-1 me-1" aria-hidden="true"></i>';
			} elseif ( strpos( $icon, 'fa-' ) === 0 ) {
				$tab_icon = '<i class="fas ' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			} else {
				$tab_icon = '';
			}

			$active   = $count == 0 ? 'active' : '';
			$selected = $active ? 'true' : 'false';
			$key      = esc_attr( $tab['tab_key'] );
			if ( $key && is_numeric( substr( $key, 0, 1 ) ) ) {
				$key = 'gdtab' . $key; // Tab is not working when ID start with number.
			}
			$name = esc_attr__( stripslashes( $tab['tab_name'] ), 'geodirectory' );
			if ( $args['output'] == 'head' && empty( $args['show_as_list'] ) && ! empty( $args['tab_style'] ) ) {
				$pill_js     = 'data-toggle="tab"';
				$data_toggle = '';
			} else {
				$data_toggle = $args['show_as_list'] ? '' : 'data-toggle="tab"';
				if ( $args['output'] == 'head' ) {
					$data_toggle = 'data-tab="' . $key . '"';
				}
				$pill_js = ( $args['show_as_list'] || $args['output'] == 'head' ) && $args['tab_style'] ? 'onclick="jQuery(this).parent().parent().find(\'a\').removeClass(\'active\');jQuery(this).addClass(\'active\');"' : '';
			}

			// `list-unstyled` class added for some themes like Kadence that will prevent scroll when used as list
			echo '<li class="nav-item list-unstyled mb-0"><a class="nav-link text-nowrap scroll-ignore ' . $active . '" ' . $pill_js . ' ' . $data_toggle . ' href="#' . $key . '" role="tab" aria-controls="' . $key . '" aria-selected="' . $selected . '">' . $tab_icon . $name . '</a></li>';

			$count++;
		}

		echo '</ul></nav>';
	}

	if ( $args['output'] != 'head' ) {
		// Tabs content
		$tab_content_class = $args['show_as_list'] ? 'geodir-tabs-as-list' : 'tab-content';
		$list_mb           = $args['lists_mb'] ? 'mb-' . esc_attr( $args['lists_mb'] ) : 'mb-4';
		$tab_pane_class    = $args['show_as_list'] ? $list_mb : 'tab-pane fade';
		echo '<div class="geodir-tabs-content geodir-entry-content ' . $tab_content_class . '" id="gd-single-tabs-content">';
		$count = 0;
		foreach ( $tabs_array as $tab ) {
			$active  = $count == 0 ? ' show active' : '';
			$add_tab = '';//$args['show_as_list'] ? 'List' : 'Tab';
			$key     = esc_attr( $tab['tab_key'] );
			if ( $key && is_numeric( substr( $key, 0, 1 ) ) ) {
				$key = 'gdtab' . $key; // Tab is not working when ID start with number.
			}
			echo '<div id="' . $key . $add_tab . '" class="' . $tab_pane_class . ' ' . $active . '" role="tabpanel" aria-labelledby="' . $key . '">';
			echo "<span id='" . $key . "-anchor' class='geodir-tabs-anchor'></span>";
			if ( $args['show_as_list'] ) {
				$tab_icon = '';

				if ( $tab['tab_icon'] && empty( $args['hide_icon'] ) ) {
					$tab_icon = '<i class=" ' . esc_attr( $tab['tab_icon'] ) . ' mr-1 me-1" aria-hidden="true"></i>';
				}

				$tab_tag         = $args['heading_tag'] ? esc_attr( $args['heading_tag'] ) : 'h2';
				$tab_font_size   = $args['heading_font_size'] ? esc_attr( $args['heading_font_size'] ) : 'h3';
				$tab_title_class = sd_build_aui_class(
					array(
						'font_size'   => $tab_font_size,
						'font_weight' => $args['heading_font_weight'],
					)
				);

				$color_class    = $args['heading_text_color'] ? esc_attr( $args['heading_text_color'] ) : 'text-reset';
				$tab_link_class = sd_build_aui_class(
					array(
						'text_color' => $color_class,
					)
				);

				$tab_title  = '<' . $tab_tag . ' class="gd-tab-list-title ' . $tab_title_class . '" ><a href="#' . $key . '" class="' . $tab_link_class . '">' . $tab_icon . esc_attr__( stripslashes( $tab['tab_name'] ), 'geodirectory' ) . '</a></' . $tab_tag . '>';
				$tab_title .= $args['remove_separator_line'] ? '' : '<hr />';

				/**
				 * Filter the tab list title html.
				 *
				 * @since 1.6.1
				 *
				 * @param string $tab_title      The html for the tab title.
				 * @param array $tab             The array of values including title text.
				 */
				echo apply_filters( 'geodir_tab_list_title', $tab_title, (object) $tab );
			}
			echo '<div id="geodir-tab-content-' . $key . '" class="hash-offset"></div>';

			echo $tab['tab_content_rendered'];

			echo '</div>';
			$count++;
		}
		echo '</div>';
	}
	echo '</div>';

	if ( ! $args['show_as_list'] ) { ?>
<script type="text/javascript">/* <![CDATA[ */
	var hashVal;
	if (window.location.hash) {
		if (window.location.hash.indexOf('&') === -1) {
			if (jQuery(window.location.hash + 'Tab').length) {
				hashVal = window.location.hash;
			}
		}
	}
	if (!hashVal) {
		hashVal = jQuery('dl.geodir-tab-head dd.geodir-tab-active').find('a').attr('data-tab');
	}
	jQuery('dl.geodir-tab-head dd').each(function() {
		/* Get all tabs */
		var tabs = jQuery(this).children('dd');
		var tab = '';
		tab = jQuery(this).find('a').attr('data-tab');
		if (hashVal != tab) {
			jQuery(tab + 'Tab').hide();
		}
	});
	/* ]]> */</script>
		<?php
	}
}

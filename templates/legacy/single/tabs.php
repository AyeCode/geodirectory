<?php
/**
 * Single Listing Tabs
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/legacy/single/tabs.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.0.16
 *
 * @var string $default_search_button_label The search button label text or font awesome class.
 * @var boolean $fa_class If a font awesome class is being used as the button text.
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $tabs_array ) ) {
	echo '<div class="geodir-tabs" id="gd-tabs">';

	// Tabs head
	if ( ! $args['show_as_list'] && $args['output'] != 'body' || ( $args['show_as_list'] && $args['output'] == 'head' ) ) {
		?>
		<div id="geodir-tab-mobile-menu">
			<span class="geodir-mobile-active-tab"></span>
			<i class="fas fa-sort-down" aria-hidden="true"></i>
		</div>
		<?php
		echo '<dl class="geodir-tab-head">';

		$count = 0;
		foreach( $tabs_array as $tab ) {
			// Tab icon
			$icon = trim( $tab['tab_icon'] );
			if ( geodir_is_fa_icon( $icon ) ) {
				$tab_icon = '<i class="' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			} elseif ( strpos( $icon, 'fa-' ) === 0 ) {
				$tab_icon = '<i class="fas ' . esc_attr( $icon ) . '" aria-hidden="true"></i>';
			} else {
				$tab_icon = '';
			}

			$tab_class = $count==0 ? 'geodir-tab-active' :'';
			$data_status = '';//$count==0 ? 'data-status="enable"' : '';
			echo '<dt></dt> <!-- added to comply with validation -->';
			echo '<dd class="'.$tab_class .'">';
			$href = $args['show_as_list'] ? ' href="#' . esc_attr( $tab['tab_key'] ) . '" ' : '';
			echo '<a data-tab="#' . esc_attr( $tab['tab_key'] ) . '" data-status="enable" '. $href .'>';
			echo $tab_icon;
			echo esc_attr__( stripslashes( $tab['tab_name'] ), 'geodirectory' ).'</a>';
			echo '</dd>';
			$count++;
		}

		echo '</dl>';
	}

	if ( $args['output'] != 'head' ) {
		// Tabs content
		$list_class =  $args['show_as_list'] ? 'geodir-tabs-as-list' : '';
		echo '<ul class="geodir-tabs-content geodir-entry-content ' . $list_class . '">';
		foreach( $tabs_array as $tab ) {
			$add_tab = $args['show_as_list'] ? 'List' : 'Tab';
			echo '<li id="' . esc_attr( $tab['tab_key'] ) . $add_tab . '" >';
			echo "<span id='" . esc_attr( $tab['tab_key'] ) . "' class='geodir-tabs-anchor'></span>";
			if ( $args['show_as_list'] ) {
				$tab_icon = '';

				if ( $tab['tab_icon'] ) {
					$tab_icon = '<i class=" ' . esc_attr( $tab['tab_icon'] ) . '" aria-hidden="true"></i>';
				}
				$tab_title = '<span class="gd-tab-list-title" ><a href="#' . esc_attr( $tab['tab_key'] ) . '">' . $tab_icon . esc_attr__(  stripslashes( $tab['tab_name'] ), 'geodirectory' ) . '</a></span><hr />';

				/**
				 * Filter the tab list title html.
				 *
				 * @since 1.6.1
				 *
				 * @param string $tab_title      The html for the tab title.
				 * @param array $tab             The array of values including title text.
				 */
				echo apply_filters( 'geodir_tab_list_title', $tab_title, (object)$tab );
			}
			echo '<div id="geodir-tab-content-' . esc_attr( $tab['tab_key'] ) . '" class="hash-offset"></div>';

			echo $tab['tab_content_rendered'];

			echo '</li>';
		}
		echo '</ul>';
	}

	echo '</div>';

	if ( ! $args['show_as_list']) { ?>
		<script type="text/javascript">/* <![CDATA[ */
			if (window.location.hash && window.location.hash.indexOf('&') === -1 && jQuery(window.location.hash + 'Tab').length) {
				hashVal = window.location.hash;
			} else {
				hashVal = jQuery('dl.geodir-tab-head dd.geodir-tab-active').find('a').attr('data-tab');
			}
			jQuery('dl.geodir-tab-head dd').each(function() {
				//Get all tabs
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

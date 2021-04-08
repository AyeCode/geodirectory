<?php
/**
 * Select Layout
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/loop/select-layout.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.0.12
 *
 * @param string $post_type The post type.
 * @param array  $layouts Layout options.
 * @param array  $args Template arguments.
 */

defined( 'ABSPATH' ) || exit;
?>
<script type="text/javascript">/* <![CDATA[ */
	<?php
	/**
	 * If the user saves gd_loop shortcode then we blank the localStorage setting for them so they can see the change instantly.
	 */
	if ( current_user_can( 'manage_options' ) && geodir_get_option( 'clear_list_view_storage' ) ) {
		echo 'localStorage.removeItem("gd_list_view", "");';
		geodir_delete_option( 'clear_list_view_storage' );
	}
	?>
	function geodir_list_view_select($list, $noStore) {
		val = $list.val();
		if (!val) {
			return;
		}
		var listSel = $list.parents().find('.geodir-loop-container .geodir-category-list-view');
		if (val != 0) {
			jQuery(listSel).addClass('geodir-gridview');
			jQuery(listSel).removeClass('geodir-listview');
		} else {
			jQuery(listSel).addClass('geodir-listview');
		}

		if (val == 0) {
			jQuery(listSel).removeClass('geodir-gridview gridview_one gridview_onehalf gridview_onethird gridview_onefourth gridview_onefifth');
		} else if (val == 1) {
			jQuery(listSel).removeClass('gridview_onehalf gridview_onethird gridview_onefourth gridview_onefifth');
			jQuery(listSel).addClass('gridview_one');
		}else if (val == 2) {
			jQuery(listSel).removeClass('gridview_one gridview_onethird gridview_onefourth gridview_onefifth');
			jQuery(listSel).addClass('gridview_onehalf');
		} else if (val == 3) {
			jQuery(listSel).removeClass('gridview_one gridview_onehalf gridview_onefourth gridview_onefifth');
			jQuery(listSel).addClass('gridview_onethird');
		} else if (val == 4) {
			jQuery(listSel).removeClass('gridview_one gridview_onehalf gridview_onethird gridview_onefifth');
			jQuery(listSel).addClass('gridview_onefourth');
		} else if (val == 5) {
			jQuery(listSel).removeClass('gridview_one gridview_onehalf gridview_onethird gridview_onefourth');
			jQuery(listSel).addClass('gridview_onefifth');
		}

		// triger the window resize so the slider can resize to fit, animation takes 0.6s
		jQuery(window).trigger('resize');
		setTimeout(function () {
			jQuery(window).trigger('resize');
		}, 600);

		// only store if it was a user action
		if (!$noStore) {
			// store the user selection
			localStorage.setItem("gd_list_view", val);
		}
	}

	// set the current user selection if set
	if (typeof(Storage) !== "undefined") {
		var $noStore = false;
		var gd_list_view = localStorage.getItem("gd_list_view");
		setTimeout(function () {
			if (!gd_list_view) {
				$noStore = true;
				$ul = jQuery('.geodir-loop-container .geodir-category-list-view');
				if ($ul.hasClass('gridview_onefifth')) {
					gd_list_view = 5;
				} else if ($ul.hasClass('gridview_onefourth')) {
					gd_list_view = 4;
				} else if ($ul.hasClass('gridview_onethird')) {
					gd_list_view = 3;
				} else if ($ul.hasClass('gridview_onehalf')) {
					gd_list_view = 2;
				} else if ($ul.hasClass('gridview_one')) {
					gd_list_view = 1;
				} else {
					gd_list_view = 0;
				}
			}
			jQuery('#gd_list_view[name="gd_list_view"]').val(gd_list_view).trigger('change');
			geodir_list_view_select(jQuery('#gd_list_view[name="gd_list_view"]'), $noStore);
		}, 10); // we need to give it a very short time so the page loads the actual html
	}
	jQuery(function ($) {
		setTimeout(function () {
			$('#gd_list_view[name="gd_list_view"]').on('change', function (e) {
				geodir_list_view_select($(this));
			});
		}, 100); // set the on change action after the select has been set to the value
	});
	/* ]]> */</script>
<div class="geodir-list-view-select">
	<select name="gd_list_view" id="gd_list_view" class="geodir-select" style="min-width:140px;border-radius:4px;" aria-label="<?php esc_attr_e( 'Layout', 'geodirectory' ) ?>">
		<?php
		if ( ! empty( $layouts ) ) {
			foreach ( $layouts as $key => $layout ) {
				$layout_name = $key ? wp_sprintf( __( 'View: Grid %d', 'geodirectory' ), $key ) : __( 'View: List', 'geodirectory' );
				echo '<option value="' . absint( $key ) . '">' . esc_attr( $layout ) . '</option>';
			}
		}
		?>
	</select>
</div>
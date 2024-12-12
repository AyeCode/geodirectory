<?php
/**
 * Select Layout
 *
 * This template can be overridden by copying it to yourtheme/geodirectory/bootstrap/loop/select-layout.php.
 *
 * HOWEVER, on occasion GeoDirectory will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://docs.wpgeodirectory.com/article/346-customizing-templates/
 * @package    GeoDirectory
 * @version    2.1.0.19
 *
 * @param string $post_type The post type.
 * @param array  $layouts Layout options.
 * @param array  $args Template arguments.
 */

defined( 'ABSPATH' ) || exit;

global $aui_bs5;
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
	function geodir_list_view_select($val, $noStore) {

		var $storage_key = "gd_list_view";
		var $list = jQuery('.geodir-loop-container > .row');
		if(!$list.length){
			$list = jQuery('.geodir-listings > .row');
			$storage_key = "gd_widget_list_view";
		}

		var $listSelect = jQuery('.gd-list-view-select');

		/* Hide button on non-GD layouts */
		if ($listSelect.length && ! $list.length) {
			$listSelect.addClass('d-none');
		}

		$listSelect.find('button').removeClass('active');
		$listSelect.find('button[data-gridview="'+$val+'"]').addClass('active');
		$list.removeClass('row-cols-md-0 row-cols-md-1 row-cols-md-2 row-cols-md-3 row-cols-md-4 row-cols-md-5').addClass('row-cols-sm-2 row-cols-md-'+$val);

		// only store if it was a user action
		if (!$noStore) {
			// store the user selection
			localStorage.setItem($storage_key, $val);
		}

		// trigger the window resize event to force any image/map resize
		jQuery(window).trigger('resize');
	}

	// set the current user selection if set
	setTimeout(function () {
		if (typeof(Storage) !== "undefined") {
			var $storage_key = "gd_list_view";
			var $list = jQuery('.geodir-loop-container > .row');
			if(!$list.length){
				$list = jQuery('.geodir-listings > .row');
				$storage_key = "gd_widget_list_view";
			}
			var $noStore = false;
			var gd_list_view = localStorage.getItem($storage_key);
			if (!gd_list_view) {
				$noStore = true;
				if ($list.hasClass('row-cols-md-0')) {
					gd_list_view = 0;
				} else if($list.hasClass('row-cols-md-1')){
					gd_list_view = 1;
				} else if($list.hasClass('row-cols-md-2')){
					gd_list_view = 2;
				} else if($list.hasClass('row-cols-md-3')){
					gd_list_view = 3;
				} else if($list.hasClass('row-cols-md-4')){
					gd_list_view = 4;
				}else if($list.hasClass('row-cols-md-5')){
					gd_list_view = 5;
				} else {
					gd_list_view = 3;
				}
			}
			geodir_list_view_select(gd_list_view, $noStore);
		}
	}, 10); // we need to give it a very short time so the page loads the actual html
	/* ]]> */</script>
<div class="btn-group btn-group-sm gd-list-view-select" role="group" aria-label="<?php esc_attr_e( 'List View', 'geodirectory' ); ?>">
	<div class="btn-group btn-group-sm" role="group">
		<button id="gd-list-view-select-grid" type="button" class="btn btn-outline-primary <?php echo $aui_bs5 ? 'dropdown-toggle dropdown-toggle-0 rounded-end' : 'rounded-right'; ?> gd-list-view-select-grid" data-<?php echo ( $aui_bs5 ? 'bs-' : '' ); ?>toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="<?php esc_attr_e( 'Select View', 'geodirectory' ); ?>">
			<i class="fas fa-th"></i>
		</button>
		<div class="dropdown-menu dropdown-caret-0 my-3 p-0" aria-labelledby="gd-list-view-select-grid">
			<?php
			if ( ! empty( $layouts ) ) {
				foreach ( $layouts as $key => $layout ) {
					$layout_name = $key ? wp_sprintf( __( 'View: Grid %d', 'geodirectory' ), $key ) : __( 'View: List', 'geodirectory' );
					?>
					<button class="dropdown-item" data-gridview="<?php echo absint( $key ); ?>" onclick="geodir_list_view_select(<?php echo absint( $key ); ?>);return false;"><?php echo esc_attr( $layout_name ); ?></button>
					<?php
				}
			}
			?>
		</div>
	</div>
</div>

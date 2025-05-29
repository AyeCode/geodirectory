<?php
/**
 * Display import/export requirements.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Check if ini_set will work or not on the server.
$current_max_ex_time = @ini_get( 'max_execution_time' );
$new_max_ex_time = /** @scrutinizer ignore-unhandled */ @ini_set( 'max_execution_time', 999999 ); // Returns the old value on success, FALSE on failure. 

if ( $current_max_ex_time && $new_max_ex_time !== false ) {
	/** @scrutinizer ignore-unhandled */ @ini_set( 'max_execution_time', $current_max_ex_time ); // Restore value.
} else {
	// only show these setting to the user if we can't change the ini setting
?>
<div id="gd_ie_reqs" class="metabox-holder">
	<div class="meta-box-sortables ui-sortable">
		<div class="postbox">
			<button class="handlediv button-link" type="button"><span class="screen-reader-text"><?php _e( 'Toggle panel - PHP Requirements for GD Import & Export CSV', 'geodirectory' );?></span><span aria-hidden="true" class="toggle-indicator"></span></button>
			<h3 class="hndle gd-hndle-click"><span style='vertical-align:top;'><?php echo __( 'PHP Requirements for GD Import & Export CSV', 'geodirectory' );?></span></h3>
			<div class="inside">
				<span class="description"><?php echo __( 'Note: In case GD import & export csv not working for larger data then please check and configure following php settings.', 'geodirectory' );?></span>
				<table class="form-table">
					<thead>
						<tr>
							<th><?php _e( 'PHP Settings', 'geodirectory' );?></th>
							<th><?php _e( 'Current Value', 'geodirectory' );?></th>
							<th><?php _e( 'Recommended Value', 'geodirectory' );?></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>max_input_time</td>
							<td><?php echo @ini_get( 'max_input_time' );?></td>
							<td>3000</td>
						</tr>
						<tr>
							<td>max_execution_time</td>
							<td><?php echo @ini_get( 'max_execution_time' );?></td>
							<td>3000</td>
						</tr>
						<tr>
							<td>memory_limit</td>
							<td><?php echo @ini_get( 'memory_limit' );?></td>
							<td>256M</td>
						</tr>
						<?php do_action( 'geodir_import_export_requirements' ); ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php } ?>
<?php if ( ! ( isset( $_REQUEST['section'] ) && $_REQUEST['section'] == 'settings' ) ) { ?>
<div class="geodir-csv-tips d-none">
<?php
aui()->alert( array(
		'type' => 'info',
		'content' => wp_sprintf( __( '<b>Important:</b> Do not use <b>Excel</b> file as it adds characters that breaks the import process. %sHow to prepare CSV file to import.%s', 'geodirectory' ), '<a href="https://wpgeodirectory.com/documentation/article/how-tos/csv-imports-useful-tips" target="_blank">', '</a>' ),
		'class' => 'mb-4'
	),
	true
);
?>
</div>
<?php } ?>
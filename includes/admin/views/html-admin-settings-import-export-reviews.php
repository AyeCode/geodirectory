<?php
/**
 * Display the page to manage import/export reviews.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

$nonce = wp_create_nonce( 'geodir_import_export_nonce' );
$sample_csv = geodir_plugin_url() . '/assets/sample_reviews.csv';
/**
* Filter sample reviews data csv file url.
*
* @since 1.0.0
* @package GeoDirectory
*
* @param string $sample_csv Sample reviews data csv file url.
*/
$sample_csv = apply_filters( 'geodir_export_reviews_sample_csv', $sample_csv );

$post_types = geodir_post_type_options( true, true );

$post_type_options = '<option value="">' . __( 'All', 'geodirectory' ) . '</option>';
foreach ( $post_types as $post_type => $name ) {
	$post_type_options .= '<option value="' . $post_type . '">' . $name . '</option>';
}
wp_enqueue_script( 'jquery-ui-progressbar' );

$gd_chunksize_options = array();
$gd_chunksize_options[100] = 100;
$gd_chunksize_options[200] = 200;
$gd_chunksize_options[500] = 500;
$gd_chunksize_options[1000] = 1000;
$gd_chunksize_options[2000] = 2000;
$gd_chunksize_options[5000] = 5000;
$gd_chunksize_options[10000] = 10000;
$gd_chunksize_options[20000] = 20000;
$gd_chunksize_options[50000] = 50000;
$gd_chunksize_options[100000] = 100000;

/**
* Filter max entries per export csv file.
*
* @since 1.5.6
* @package GeoDirectory
*
* @param string $gd_chunksize_options Entries options.
*/
$gd_chunksize_options = apply_filters( 'geodir_export_csv_chunksize_options', $gd_chunksize_options );

$gd_chunksize_option = '';
foreach ($gd_chunksize_options as $value => $title) {
	$gd_chunksize_option .= '<option value="' . $value . '" ' . selected($value, 5000, false) . '>' . $title . '</option>';
}
// @todo move style in css file
?>
<div class="inner_content_tab_main gd-import-export">
	<div class="gd-content-heading">
		<?php /**
		 * Contains template for import/export requirements.
		 *
		 * @since 2.0.0
		 */
		include_once( dirname( dirname( __FILE__ ) ) . '/views/html-admin-settings-import-export-reqs.php' );
		?>
		<div id="gd_ie_imreviews" class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="gd_ie_imreviews" class="postbox gd-hndle-pbox">
					<button class="handlediv button-link" type="button"><span class="screen-reader-text"><?php _e( 'Toggle panel - Reviews: Import CSV', 'geodirectory' );?></span><span aria-hidden="true" class="toggle-indicator"></span></button>
					<h3 class="hndle gd-hndle-click"><span style='vertical-align:top;'><?php echo __( 'Reviews: Import CSV', 'geodirectory' );?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
							<tr>
								<td class="gd-imex-box">
									<div class="gd-im-choices">
										<p><input type="radio" value="update" name="gd_im_choicereview" id="gd_im_choice_u" /><label for="gd_im_choice_u"><?php _e( 'Update item if review with comment_ID already exists.', 'geodirectory' );?></label></p>
										<p><input type="radio" checked="checked" value="skip" name="gd_im_choicereview" id="gd_im_choice_s" /><label for="gd_im_choice_s"><?php _e( 'Ignore item if review with comment_ID already exists.', 'geodirectory' );?></label></p>
									</div>
									<div class="plupload-upload-uic hide-if-no-js" id="gd_im_reviewplupload-upload-ui">
										<input type="text" readonly="readonly" name="gd_im_review_file" class="gd-imex-file gd_im_review_file" id="gd_im_review" onclick="jQuery('#gd_im_reviewplupload-browse-button').trigger('click');" />
										<input id="gd_im_reviewplupload-browse-button" type="button" value="<?php esc_attr_e( 'Select & Upload CSV', 'geodirectory' ); ?>" class="gd-imex-cupload button-primary" /> <input type="button" value="<?php esc_attr_e( 'Download Sample CSV', 'geodirectory' );?>" class="button-secondary" name="gd_ie_download_sample" id="gd_ie_download_sample" data-sample-csv="<?php echo $sample_csv;?>">
										<input type="hidden" id="gd_im_review_allowed_types" data-exts=".csv" value="csv" />
										<?php
										/**
										 * Called just after the sample CSV download link.
										 *
										 * @since 2.0.0
										 * @package GeoDirectory
										 */
										do_action('geodir_sample_reviews_csv_download_link');
										?>
										<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( 'gd_im_reviewpluploadan' ); ?>"></span>
										<div class="filelist"></div>
									</div>
									<span id="gd_im_reviewupload-error" style="display:none"></span>
									<span class="description"></span>
									<div id="gd_importer" style="display:none">
										<input type="hidden" id="gd_total" value="0"/>
										<input type="hidden" id="gd_prepared" value="continue"/>
										<input type="hidden" id="gd_processed" value="0"/>
										<input type="hidden" id="gd_created" value="0"/>
										<input type="hidden" id="gd_updated" value="0"/>
										<input type="hidden" id="gd_skipped" value="0"/>
										<input type="hidden" id="gd_invalid" value="0"/>
										<input type="hidden" id="gd_images" value="0"/>
										<input type="hidden" id="gd_terminateaction" value="continue"/>
									</div>
									<div class="gd-import-progress" id="gd-import-progress" style="display:none">
										<div class="gd-import-file"><b><?php _e("Import Data Status :", 'geodirectory');?> </b><font
												id="gd-import-done">0</font> / <font id="gd-import-total">0</font>&nbsp;( <font
												id="gd-import-perc">0%</font> )
											<div class="gd-fileprogress"></div>
										</div>
									</div>
									<div class="gd-import-msg" id="gd-import-msg" style="display:none">
										<div id="message" class="message fade"></div>
									</div>
									<div class="gd-imex-btns" style="display:none;">
										<input type="hidden" class="geodir_import_file" name="geodir_import_file" value="save"/>
										<input onclick="gd_imex_PrepareImport(this, 'review')" type="button" value="<?php esc_attr_e('Import data now', 'geodirectory'); ?>" id="gd_import_data" class="button-primary" />
										<input onclick="gd_imex_ContinueImport(this, 'review')" type="button" value="<?php _e( "Continue Import Data", 'geodirectory' );?>" id="gd_continue_data" class="button-primary" style="display:none"/>
										<input type="button" value="<?php _e("Terminate Import Data", 'geodirectory');?>" id="gd_stop_import" class="button-primary" name="gd_stop_import" style="display:none" onclick="gd_imex_TerminateImport(this, 'review')"/>
										<div id="gd_process_data" style="display:none">
											<span class="spinner is-active" style="display:inline-block;margin:0 5px 0 5px;float:left"></span><?php _e("Wait, processing import data...", 'geodirectory');?>
										</div>
									</div>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<div id="gd_ie_exreviews" class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="gd_ie_ex_reviews" class="postbox gd-hndle-pbox">
					<button class="handlediv button-link" type="button"><span class="screen-reader-text"><?php _e( 'Toggle panel - Reviews: Export CSV', 'geodirectory' );?></span><span aria-hidden="true" class="toggle-indicator"></span></button>
					<h3 class="hndle gd-hndle-click"><span style='vertical-align:top;'><?php echo __( 'Reviews: Export CSV', 'geodirectory' );?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
							<tr>
								<td class="fld"><label for="gd_post_type"><?php _e( 'Post Type:', 'geodirectory' );?></label></td>
								<td><select name="gd_imex[post_type]" id="gd_post_type" style="min-width:140px"><?php echo $post_type_options;?></select></td>
							</tr>
							<tr>
								<td class="fld" style="vertical-align:top"><label for="gd_chunk_size"><?php _e( 'Max entries per csv file:', 'geodirectory' );?></label></td>
								<td><select name="gd_chunk_size" id="gd_chunk_size" style="min-width:140px"><?php echo $gd_chunksize_option;?></select><span class="description"><?php _e( 'Please select the maximum number of entries per csv file (defaults to 5000, you might want to lower this to prevent memory issues on some installs)', 'geodirectory' );?></span></td>
							</tr>
							<tr class="gd-imex-dates">
								<td class="fld"><label><?php _e( 'Date:', 'geodirectory' );?></label></td>
								<td>
									<input type="text" id="gd_imex_start_date" name="gd_imex[start_date]" data-type="date" /> - <input type="text" id="gd_imex_end_date" name="gd_imex[end_date]" data-type="date" />
									<span class="description"><?php _e( 'The date interval of which the reviews are to be exported', 'geodirectory' );?></span>
								</td>
							</tr>
							<tr class="gd-imex-ratings">
								<td class="fld"><label><?php _e( 'Rating:', 'geodirectory' );?></label></td>
								<td>
									<select name="gd_imex[min_rating]" data-type="rating" style="min-width:90px">
										<option value=""><?php _e( 'Any', 'geodirectory' );?></option>
										<?php for ( $n = 1; $n <= 5; $n++ ) { ?>
										<option value="<?php echo $n; ?>"><?php echo $n; ?></option>
										<?php } ?>
									</select> - 
									<select name="gd_imex[max_rating]" data-type="rating" style="min-width:90px">
										<option value=""><?php _e( 'Any', 'geodirectory' );?></option>
										<?php for ( $n = 1; $n <= 5; $n++ ) { ?>
										<option value="<?php echo $n; ?>"><?php echo $n; ?></option>
										<?php } ?>
									</select>
									<span class="description"><?php _e( 'Min & max rating star range of which the reviews are to be exported', 'geodirectory' );?></span>
								</td>
							</tr>
							<tr class="gd-imex-status">
								<td class="fld"><label><?php _e( 'Status:', 'geodirectory' );?></label></td>
								<td>
									<select name="gd_imex[status]" data-type="status" style="min-width:140px">
										<option value="any"><?php _e( 'Any', 'geodirectory' );?></option>
										<option value="approve"><?php _e( 'Approved', 'geodirectory' );?></option>
										<option value="hold"><?php _e( 'Pending', 'geodirectory' );?></option>
										<option value="spam"><?php _e( 'Spam', 'geodirectory' );?></option>
										<option value="trash"><?php _e( 'Trashed', 'geodirectory' );?></option>
									</select>
									<span class="description"><?php _e( 'The review status of which the reviews are to be exported', 'geodirectory' );?></span>
								</td>
							</tr>
							<tr>
								<td class="fld" style="vertical-align:top"><label><?php _e( 'Progress:', 'geodirectory' );?></label></td>
								<td><div id='gd_progressbar_box'><div id="gd_progressbar" class="gd_progressbar"><div class="gd-progress-label"></div></div></div><p style="display:inline-block"><?php _e( 'Elapsed Time:', 'geodirectory' );?></p>&nbsp;&nbsp;<p id="gd_timer" class="gd_timer">00:00:00</p></td>
							</tr>
							<tr class="gd-ie-actions">
								<td style="vertical-align:top">
									<input data-export="reviews" type="submit" value="<?php echo esc_attr( __( 'Export CSV', 'geodirectory' ) );?>" class="button-primary" name="gd_start_export" id="gd_start_export">
								</td>
								<td id="gd_ie_ex_files" class="gd-ie-files"></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
		/**
		 * Allows you to add more setting to the GD > Import & Export page.
		 *
		 * Called after the last setting on the GD > Import & Export page.
		 * @since 2.0.0
		 * @package GeoDirectory
		 *
		 * @param array $gd_posttypes GD post types.
		 * @param array $gd_chunksize_options File chunk size options.
		 * @param string $nonce Wordpress security token for GD import & export.
		 */
		do_action( 'geodir_import_export_reviews', $gd_chunksize_options, $nonce );
		?>
	</div>
</div>
<?php GeoDir_Settings_Import_Export::get_import_export_js($nonce);?>
<?php
/**
 * Display the page to manage import/export categories/listings.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

global $aui_bs5;

$nonce = wp_create_nonce( 'geodir_import_export_nonce' );
$gd_cats_sample_csv = geodir_plugin_url() . '/assets/gd_sample_categories.csv';
/**
* Filter sample category data csv file url.
*
* @since 1.0.0
* @package GeoDirectory
*
* @param string $gd_cats_sample_csv Sample category data csv file url.
*/
$gd_cats_sample_csv = apply_filters( 'geodir_export_cats_sample_csv', $gd_cats_sample_csv );

$gd_posttypes = geodir_get_posttypes( 'options-plural' );

wp_enqueue_script( 'jquery-ui-progressbar' );

$gd_chunksize_options = array();
//$gd_chunksize_options[10] = 10;
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
		<div id="gd_ie_imcategs" class="metabox-holder accordion ">

			<div class="card p-0 mw-100 border-0 shadow-sm" style="overflow: initial;">
				<div class="card-header bg-white rounded-top">
					<h2	class="gd-settings-title h5 mb-0 "><?php echo __( 'Categories: Import CSV', 'geodirectory' ); ?></h2>
				</div>
				<div id="gd_ie_imcats" class="gd-hndle-pbox card-body">

					<?php
					$settings = array(
						array(
							'name'       => __( 'If cat_id/cat_slug exists', 'geodirectory' ),
							'desc'       => __( 'If the cat_id/cat_slug column exists in the CSV, you can either update the category or it can be skipped', 'geodirectory' ),
							'id'         => 'gd_im_choicecat',
							'default'    => 'skip',
							'type'       => 'select',
							'options' => array_unique(array(
								'skip' => __('Skip row', 'geodirectory'),
								'update' => __('Update Category', 'geodirectory'),

							)),
							'desc_tip' => true,
						)
					);
					GeoDir_Admin_Settings::output_fields( $settings );

					?>

					<div data-argument="gd_im_choicepost" class="<?php echo ( $aui_bs5 ? 'mb-3' : 'form-group' ); ?> row">
						<label for="gd_im_choicepost" class="font-weight-bold fw-bold  col-sm-3 col-form-label"><?php _e('Upload CSV file', 'geodirectory'); ?></label>
						<div class="col-sm-9">
							<?php
							echo aui()->button(
								array(
									'type'      => 'a',
									'content'   => __('Select file', 'geodirectory'),
									//'icon'      => ! empty( $action['icon'] ) ? $action['icon'] : '',
									'href'      => 'javascript:void(0)',
									'onclick'   => "jQuery('#gd_im_catplupload-browse-button').trigger('click');"
								)
							);

							echo aui()->button(
								array(
									'type'      => 'a',
									'class'     => 'btn btn-outline-primary',
									'content'   => __('Download Sample CSV', 'geodirectory'),
									'icon'      => 'fas fa-download',
									'href'      => esc_url($gd_cats_sample_csv),
								)
							);
							?>
						</div>
					</div>

					<div class="plupload-upload-uic hide-if-no-js" id="gd_im_catplupload-upload-ui">
						<input type="hidden" readonly="readonly" name="gd_im_cat_file" class="gd-imex-file gd_im_cat_file" id="gd_im_cat" onclick="jQuery('#gd_im_catplupload-browse-button').trigger('click');" />
						<input id="gd_im_catplupload-browse-button" type="hidden" value="<?php esc_attr_e( 'Select & Upload CSV', 'geodirectory' ); ?>" class="gd-imex-cupload button-primary"/>
						<input type="hidden" id="gd_im_cat_allowed_types" data-exts=".csv" value="csv"/>
						<?php
						/**
						 * Called just after the sample CSV download link.
						 *
						 * @since 1.0.0
						 * @package GeoDirectory
						 */
						do_action( 'geodir_sample_cats_csv_download_link' );
						?>
						<span class="ajaxnonceplu"
						      id="ajaxnonceplu<?php echo wp_create_nonce( 'gd_im_catpluploadan' ); ?>"></span>
						<div class="filelist"></div>
					</div>
					<span id="gd_im_catupload-error" style="display:none"></span>
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
						<div class="gd-import-file">
							<b><?php _e( "Import Data Status :", 'geodirectory' ); ?> </b><font
								id="gd-import-done">0</font> / <font id="gd-import-total">0</font>&nbsp;(
							<font
								id="gd-import-perc">0%</font> )
							<div class="gd-fileprogress"></div>
						</div>
					</div>
					<div class="gd-import-msg" id="gd-import-msg" style="display:none">
						<div id="message" class="message alert alert-success fade show"></div>
					</div>
					<div class="gd-import-csv-msg" id="gd-import-errors" style="display:none">
						<div id="gd-csv-errors" class="message fade"></div>
					</div>
					<div class="gd-imex-btns" style="display:none;">
						<input type="hidden" class="geodir_import_file" name="geodir_import_file"
						       value="save"/>
						<input onclick="gd_imex_PrepareImport(this, 'cat')" type="button"
						       value="<?php esc_attr_e( 'Import data now', 'geodirectory' ); ?>"
						       id="gd_import_data" class="btn btn-primary"/>
						<input onclick="gd_imex_ContinueImport(this, 'cat')" type="button"
						       value="<?php _e( "Continue Import Data", 'geodirectory' ); ?>"
						       id="gd_continue_data" class="btn btn-primary" style="display:none"/>
						<input type="button" value="<?php _e( "Terminate Import Data", 'geodirectory' ); ?>" id="gd_stop_import" class="btn btn-outline-danger" name="gd_stop_import" style="display:none" onclick="gd_imex_TerminateImport(this, 'cat')"/>
						<div id="gd_process_data" style="display:none">
							<span class="spinner is-active float-left float-start" style="display:inline-block;margin:0 5px 0 5px;"></span><?php _e( "Wait, processing import data...", 'geodirectory' ); ?>
						</div>
					</div>

				</div>
			</div>
		</div>
		<div id="gd_ie_excategs" class="metabox-holder accordion ">
			<div class="card p-0 mw-100 border-0 shadow-sm" style="overflow: initial;">
				<div class="card-header bg-white rounded-top">
					<h2	class="gd-settings-title h5 mb-0 "><?php echo __( 'Categories: Export CSV', 'geodirectory' ); ?></h2>
				</div>

				<div id="gd_ie_ex_cats" class="gd-hndle-pbox card-body">
					<div class="inside">

						<?php

						$cpt_with_count = array();

						foreach($gd_posttypes as $cpt => $name){
							$cpt_with_count[] = array(
								'value' => $cpt,
								'label' => $name,
								'extra_attributes' => array(
									'data-cats' => absint(geodir_get_terms_count( $cpt ))
								)
							);
						}

						echo aui()->select(
							array(
								'label_col'        => '3',
								'label_class'=> 'font-weight-bold fw-bold',
								'label_type'        => 'horizontal',
								'label'       => __( 'Post Type', 'geodirectory' ),
								'class'     => 'mw-100',
								'wrap_class'      => count($gd_posttypes) < 2 ? 'd-none' : '',
//								'desc'       => __( 'If the ID column exists in the CSV, you can either update the listing or it can be skipped', 'geodirectory' ),
								'id'         => 'gd_post_type',
								'name'         => 'gd_post_type',
//								'default'    => 'pending',
								'options' => $cpt_with_count,
//								'desc_tip' => true,

							)
						);


						echo aui()->select(
							array(
								'label_col'        => '3',
								'label_class'=> 'font-weight-bold fw-bold',
								'label_type'        => 'horizontal',
								'label'       => __( 'Max entries per csv file', 'geodirectory' ) . geodir_help_tip( __( 'Please select the maximum number of entries per csv file (defaults to 5000, you might want to lower this to prevent memory issues on some installs)', 'geodirectory' )),
								'class'     => 'mw-100',
//								'wrap_class'      => count($gd_posttypes) < 2 ? 'd-none' : '',
//								'desc'       => __( 'If the ID column exists in the CSV, you can either update the listing or it can be skipped', 'geodirectory' ),
								'id'         => 'gd_chunk_size',
								'name'         => 'gd_chunk_size',
								'value'    => '5000',
								'options' => $gd_chunksize_options,
//								'desc_tip' => true,

							)
						);
						?>

						<div class="pt-3 gd-export-categories-progress" style="display:none;">
							<div id='gd_progressbar_box'>
								<div id="gd_progressbar" class="gd_progressbar">
									<div class="gd-progress-label"></div>
								</div>
							</div>
							<p style="display:inline-block"><?php _e( 'Elapsed Time:', 'geodirectory' ); ?></p>&nbsp;&nbsp;
							<p id="gd_timer" class="gd_timer">00:00:00</p>
						</div>

						<div class="gd-ie-actions d-flex flex-row align-items-center">
							<input type="submit" value="<?php echo esc_attr( __( 'Export CSV', 'geodirectory' ) );?>" class="btn btn-primary" name="gd_ie_excats_submit" id="gd_ie_excats_submit">
							<div id="gd_ie_ex_files" class="gd-ie-files ml-4 ms-4 mt-2"></div>
						</div>


<!--						<table class="form-table">-->
<!--							<tbody>-->
<!--							<tr>-->
<!--								<td class="fld"><label for="gd_post_type">--><?php //_e( 'Post Type:', 'geodirectory' );?><!--</label></td>-->
<!--								<td><select name="gd_post_type" id="gd_post_type" style="min-width:140px">--><?php //echo $gd_posttypes_option;?><!--</select></td>-->
<!--							</tr>-->
<!--							<tr>-->
<!--								<td class="fld" style="vertical-align:top"><label for="gd_chunk_size">--><?php //_e( 'Max entries per csv file:', 'geodirectory' );?><!--</label></td>-->
<!--								<td><select name="gd_chunk_size" id="gd_chunk_size" style="min-width:140px">--><?php //echo $gd_chunksize_option;?><!--</select><span class="description">--><?php //_e( 'Please select the maximum number of entries per csv file (defaults to 5000, you might want to lower this to prevent memory issues on some installs)', 'geodirectory' );?><!--</span></td>-->
<!--							</tr>-->
<!--							<tr>-->
<!--								<td class="fld" style="vertical-align:top"><label>--><?php //_e( 'Progress:', 'geodirectory' );?><!--</label></td>-->
<!--								<td>-->
<!--									<div id='gd_progressbar_box'><div id="gd_progressbar" class="gd_progressbar"><div class="gd-progress-label"></div></div></div><p style="display:inline-block">--><?php //_e( 'Elapsed Time:', 'geodirectory' );?><!--</p>&nbsp;&nbsp;<p id="gd_timer" class="gd_timer">00:00:00</p>-->
<!---->
<!--								</td>-->
<!--							</tr>-->
<!--							<tr class="gd-ie-actions">-->
<!--								<td style="vertical-align:top">-->
<!--									<input type="submit" value="--><?php //echo esc_attr( __( 'Export CSV', 'geodirectory' ) );?><!--" class="button-primary" name="gd_ie_excats_submit" id="gd_ie_excats_submit">-->
<!--								</td>-->
<!--								<td id="gd_ie_ex_files" class="gd-ie-files"></td>-->
<!--							</tr>-->
<!--							</tbody>-->
<!--						</table>-->
					</div>
				</div>
			</div>
		</div>
		<?php
		/**
		 * Allows you to add more setting to the GD > Import & Export page.
		 *
		 * Called after the last setting on the GD > Import & Export page.
		 * @since 1.4.6
		 * @package GeoDirectory
		 *
		 * @param array $gd_posttypes GD post types.
		 * @param array $gd_chunksize_options File chunk size options.
		 * @param string $nonce Wordpress security token for GD import & export.
		 */
		do_action( 'geodir_import_export_categories', $gd_posttypes, $gd_chunksize_options, $nonce );
		?>
	</div>
</div>
<?php GeoDir_Settings_Import_Export::get_import_export_js($nonce);?>

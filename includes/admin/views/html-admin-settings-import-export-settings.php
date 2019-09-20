<?php
/**
 * Display the page to manage import/export categories/listings.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

$nonce = wp_create_nonce( 'geodir_import_export_nonce' );

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
		<div id="gd_ie_imposts" class="metabox-holder">
			<div class="meta-box-sortables ui-sortable">
				<div id="gd_ie_im_posts" class="postbox gd-hndle-pbox">
					<button class="handlediv button-link" type="button"><span class="screen-reader-text"><?php _e( 'Toggle panel', 'geodirectory' );?></span><span aria-hidden="true" class="toggle-indicator"></span></button>
					<h3 class="hndle gd-hndle-click"><span style='vertical-align:top;'><?php echo __( 'Import / Export Settings', 'geodirectory' );?></span></h3>
					<div class="inside">
						<table class="form-table">
							<tbody>
							<tr>
								<td class="gd-imex-box">
									<div class="gd-im-choices">
										<p><?php _e( 'Import and export your GeoDirectory Settings between sites or simply back them up via a .json file.', 'geodirectory' );?></p>
									</div>
									<?php
									$uploader_id = "gd_im_settings";
									?>
									<div class="plupload-upload-uic hide-if-no-js" id="<?php echo $uploader_id; ?>plupload-upload-ui">
										<input type="text" readonly="readonly" name="<?php echo $uploader_id; ?>_file" class="gd-imex-file <?php echo $uploader_id; ?>_file" id="<?php echo $uploader_id; ?>" onclick="jQuery('#<?php echo $uploader_id; ?>plupload-browse-button').trigger('click');" />
										<input id="<?php echo $uploader_id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Import Settings','geodirectory')?>" class="gd-imex-pupload button-primary"  />
										<a class="button-secondary" name="gd_export_settingsx" href="<?php echo esc_url( add_query_arg( array('action' => 'geodir_import_export','task' => 'export_settings','_nonce'=> $nonce), admin_url( 'admin-ajax.php' ) ) ); ?>" target="_blank">
											<?php echo esc_attr( __( 'Download Settings', 'geodirectory' ) );?>
										</a>
										<input type="hidden" id="<?php echo $uploader_id; ?>_allowed_types" data-exts=".json" value="json" />
										<?php
										/**
										 * Called just after the sample CSV download link.
										 *
										 * @since 1.0.0
										 */
										do_action('geodir_sample_csv_download_link');
										?>
										<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce( $uploader_id.'pluploadan' ); ?>"></span>
										<div class="filelist"></div>
									</div>
									<span id="<?php echo $uploader_id; ?>upload-error" style="display:none"></span>
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
										<input onclick="gd_imex_import_settings(this, 'settings')" type="button" value="<?php esc_attr_e('Import data now', 'geodirectory'); ?>" id="gd_import_data" class="button-primary" />
										<input onclick="gd_imex_ContinueImport(this, 'settings')" type="button" value="<?php _e( "Continue Import Data", 'geodirectory' );?>" id="gd_continue_data" class="button-primary" style="display:none"/>
										<input type="button" value="<?php _e("Terminate Import Data", 'geodirectory');?>" id="gd_stop_import" class="button-primary" name="gd_stop_import" style="display:none" onclick="gd_imex_TerminateImport(this, 'cat')"/>
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
		do_action( 'geodir_import_export_settings');
		?>
	</div>
</div>

<?php GeoDir_Settings_Import_Export::get_import_export_js($nonce);?>

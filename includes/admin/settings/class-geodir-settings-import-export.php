<?php
/**
 * GeoDirectory Import/Export Settings
 *
 * @author   GeoDirectory
 * @category Admin
 * @package  GeoDirectory/Admin
 * @version  2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'GD_Settings_Import_Export', false ) ) :

	/**
	 * GeoDir_Settings_Products.
	 */
	class GeoDir_Settings_Import_Export extends GeoDir_Settings_Page {

		/**
		 * Constructor.
		 */
		public function __construct() {

			$this->id    = 'import-export';
			$this->label = __( 'Import/Export', 'geodirectory' );

			add_filter( 'geodir_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
			add_action( 'geodir_settings_' . $this->id, array( $this, 'output' ) );
			add_action( 'geodir_settings_save_' . $this->id, array( $this, 'save' ) );
			add_action( 'geodir_sections_' . $this->id, array( $this, 'output_sections' ) );
		}

		/**
		 * Get sections.
		 *
		 * @return array
		 */
		public function get_sections() {

			$sections = array(
				''				=> __( 'Listings', 'geodirectory' ),
				'categories'    => __( 'Categories', 'geodirectory' ),
				'reviews'    	=> __( 'Reviews', 'geodirectory' ),
				'settings' 		=> __( 'Settings', 'geodirectory' ),
			);

			return apply_filters( 'geodir_get_sections_' . $this->id, $sections );
		}

		/**
		 * Output the settings.
		 */
		public function output() {
			global $current_section,$hide_save_button;

			$hide_save_button = true; // hide the save button

			$settings = $this->get_settings( $current_section );

			GeoDir_Admin_Settings::output_fields( $settings );
		}

		/**
		 * Save settings.
		 */
		public function save() {
			global $current_section;

			$settings = $this->get_settings( $current_section );
			GeoDir_Admin_Settings::save_fields( $settings );
		}

		/**
		 * Get settings array.
		 *
		 * @return array
		 */
		public function get_settings( $current_section = '' ) {
			if ( $current_section == 'categories' ) {
				$settings = apply_filters( 'geodir_import_export_listings_settings', array(
//					array(
//						'title' 	=> '',//__( 'Import & Export Categories', 'geodirectory' ),
//						'type' 		=> 'title',
//						'id' 		=> 'import_export_options',
//					),

					array(
						'id'       => 'import_export_categories',
						'type'     => 'import_export_categories',
					),

//					array(
//						'type' 	=> 'sectionend',
//						'id' 	=> 'import_export_options',
//					),

				));

			} elseif ( $current_section == 'reviews' ) {
				$settings = apply_filters( 'geodir_import_export_reviews_settings', array(
//					array(
//						'title' 	=> '',
//						'type' 		=> 'title',
//						'id' 		=> 'import_export_options',
//					),

					array(
						'id'       => 'import_export_reviews',
						'type'     => 'import_export_reviews',
					),

//					array(
//						'type' 	=> 'sectionend',
//						'id' 	=> 'import_export_options',
//					),

				));

			} elseif ( 'settings' == $current_section ) {

				$settings = apply_filters( 'geodir_import_export_geodirectory_settings', array(
//					array(
//						'title' 	=> '',//__( 'Import & Export Categories', 'geodirectory' ),
//						'type' 		=> 'title',
//						'id' 		=> 'import_export_options',
//					),

					array(
						'id'       => 'import_export_settings',
						'type'     => 'import_export_settings',
					),

//					array(
//						'type' 	=> 'sectionend',
//						'id' 	=> 'import_export_options',
//					),

				));

			} else {
				$settings = apply_filters( 'geodir_import_export_listings_settings', array(
//					array(
//						'title' 	=> '',//__( 'Import & Export Listings', 'geodirectory' ),
//						'type' 		=> 'title',
//						'id' 		=> 'import_export_options',
//					),

					array(
						'id'       => 'import_export_listings',
						'type'     => 'import_export_listings',
					),

//					array(
//						'type' 	=> 'sectionend',
//						'id' 	=> 'import_export_options',
//					),

				));
			}

			return apply_filters( 'geodir_get_settings_' . $this->id, $settings, $current_section );
		}

		/**
		 * JS code for import/export view.
		 * 
		 * @param $nonce
		 */
		public static function get_import_export_js($nonce){
			$uploads = wp_upload_dir();
			$upload_dir = wp_sprintf( __( "Please transfer all new images to <b>'%s'</b> folder.", 'geodirectory' ), str_replace( ABSPATH, '', $uploads['path'] ) );
			?>
			<script type="text/javascript">
				var timoutC, timoutP, timoutR;

				function gd_imex_PrepareImport(el, type) {
					var cont = jQuery(el).closest('.card-body');
					var gd_prepared = jQuery('#gd_prepared', cont).val();
					var uploadedFile = jQuery('#gd_im_' + type, cont).val();
					jQuery('gd-import-msg', cont).hide();
					jQuery('#gd-import-errors').hide();
					jQuery('#gd-import-errors #gd-csv-errors').html('');

					if(gd_prepared == uploadedFile) {
						gd_imex_ContinueImport(el, type);
						jQuery('#gd_import_data', cont).attr('disabled', 'disabled');
					} else {
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=prepare_import&_pt=' + type + '&_file=' + uploadedFile + '&_nonce=<?php echo $nonce;?>',
							dataType: 'json',
							cache: false,
							success: function(data) {
								if(typeof data == 'object') {
									if(data.success == false) {
										jQuery('#gd-import-msg', cont).find('#message').removeClass('updated').addClass('error').html('<p>' + data.data + '</p>');
										jQuery('#gd-import-msg', cont).show();
									} else if(!data.error && typeof data.rows != 'undefined') {
										jQuery('#gd_total', cont).val(data.rows);
										jQuery('#gd_prepared', cont).val(uploadedFile);
										jQuery('#gd_processed', cont).val('0');
										jQuery('#gd_created', cont).val('0');
										jQuery('#gd_updated', cont).val('0');
										jQuery('#gd_skipped', cont).val('0');
										jQuery('#gd_invalid', cont).val('0');
										jQuery('#gd_images', cont).val('0');
										if(type == 'post') {
											jQuery('#gd_invalid_addr', cont).val('0');
										}
										gd_imex_StartImport(el, type);
									}
								}
							},
							error: function(errorThrown) {
								console.log(errorThrown);
							}
						});
					}
				}

				function gd_imex_StartImport(el, type) {
					var cont = jQuery(el).closest('.card-body');

					var limit = 1;
					var total = parseInt(jQuery('#gd_total', cont).val());
					var total_processed = parseInt(jQuery('#gd_processed', cont).val());
					var uploadedFile = jQuery('#gd_im_' + type, cont).val();
					var choice = jQuery('#gd_im_choice' + type, cont).val();

					if (!uploadedFile) {
						jQuery('#gd_import_data', cont).removeAttr('disabled').show();
						jQuery('#gd_stop_import', cont).hide();
						jQuery('#gd_process_data', cont).hide();
						jQuery('#gd-import-progress', cont).hide();
						jQuery('.gd-fileprogress', cont).width(0);
						jQuery('#gd-import-done', cont).text('0');
						jQuery('#gd-import-total', cont).text('0');
						jQuery('#gd-import-perc', cont).text('0%');

						jQuery(cont).find('.filelist .file').remove();

						jQuery('#gd-import-msg', cont).find('#message').removeClass('updated').addClass('error').html("<p><?php esc_attr_e( 'Please select csv file.', 'geodirectory' ); ?></p>");
						jQuery('#gd-import-msg', cont).show();

						return false;
					}

					jQuery('#gd-import-total', cont).text(total);
					jQuery('#gd_stop_import', cont).show();
					jQuery('#gd_process_data', cont).css({
						'display': 'inline-block'
					});
					jQuery('#gd-import-progress', cont).show();
					if ((parseInt(total) / 100) > 0) {
						limit = parseInt(parseInt(total) / 100);
					}
					if (limit == 1) {
						if (parseInt(total) > 50) {
							limit = 5;
						} else if (parseInt(total) > 10 && parseInt(total) < 51) {
							limit = 2;
						}
					}
					if (limit > 10) {
						limit = 10;
					}
					if (limit < 1) {
						limit = 1;
					}

					if ( parseInt(limit) > parseInt(total) )
						limit = parseInt(total);
					if (total_processed >= total) {
						jQuery('#gd_import_data', cont).removeAttr('disabled').show();
						jQuery('#gd_stop_import', cont).hide();
						jQuery('#gd_process_data', cont).hide();

						gd_imex_showStatusMsg(el, type);

						jQuery('#gd_im_' + type, cont).val('');
						jQuery('#gd_prepared', cont).val('');

						return false;
					}
					jQuery('#gd-import-msg', cont).hide();

					var gd_processed = parseInt(jQuery('#gd_processed', cont).val());
					var gd_created = parseInt(jQuery('#gd_created', cont).val());
					var gd_updated = parseInt(jQuery('#gd_updated', cont).val());
					var gd_skipped = parseInt(jQuery('#gd_skipped', cont).val());
					var gd_invalid = parseInt(jQuery('#gd_invalid', cont).val());
					var gd_images = parseInt(jQuery('#gd_images', cont).val());
					if (type=='post') {
						var gd_invalid_addr = parseInt(jQuery('#gd_invalid_addr', cont).val());
					}

					var gddata = '&limit=' + limit + '&processed=' + gd_processed;
					jQuery.ajax({
						url: ajaxurl,
						type: "POST",
						data: 'action=geodir_import_export&task=import_' + type + '&_pt=' + type + '&_file=' + uploadedFile + gddata + '&_ch=' + choice + '&_nonce=<?php echo $nonce;?>',
						dataType : 'json',
						cache: false,
						success: function (data) {

							// log any errors
							if(data.errors){
								gd_imex_log_errors(data.errors);
							}


							if (typeof data == 'object') {
								if(data.success == false) {
									jQuery('#gd_import_data', cont).removeAttr('disabled').show();
									jQuery('#gd_stop_import', cont).hide();
									jQuery('#gd_process_data', cont).hide();
									jQuery('#gd-import-msg', cont).find('#message').removeClass('updated').addClass('error').html('<p>' + data.data + '</p>');
									jQuery('#gd-import-msg', cont).show();
								} else {
									gd_created = gd_created + parseInt(data.created);
									gd_updated = gd_updated + parseInt(data.updated);
									gd_skipped = gd_skipped + parseInt(data.skipped);
									gd_invalid = gd_invalid + parseInt(data.invalid);
									gd_images = gd_images + parseInt(data.images);
									if (type=='post' && typeof data.invalid_addr != 'undefined') {
										gd_invalid_addr = gd_invalid_addr + parseInt(data.invalid_addr);
									}

									jQuery('#gd_processed', cont).val(gd_processed);
									jQuery('#gd_created', cont).val(gd_created);
									jQuery('#gd_updated', cont).val(gd_updated);
									jQuery('#gd_skipped', cont).val(gd_skipped);
									jQuery('#gd_invalid', cont).val(gd_invalid);
									jQuery('#gd_images', cont).val(gd_images);
									if (type=='post') {
										jQuery('#gd_invalid_addr', cont).val(gd_invalid_addr);
									}

									if (parseInt(gd_processed) == parseInt(total)) {
										jQuery('#gd-import-done', cont).text(total);
										jQuery('#gd-import-perc', cont).text('100%');
										jQuery('.gd-fileprogress', cont).css({
											'width': '100%'
										});
										jQuery('#gd_im_' + type, cont).val('');
										jQuery('#gd_prepared', cont).val('');

										gd_imex_showStatusMsg(el, type);
										gd_imex_FinishImport(el, type);

										jQuery('#gd_stop_import', cont).hide();
									}
									if (parseInt(gd_processed) < parseInt(total)) {
										var terminate_action = jQuery('#gd_terminateaction', cont).val();
										if (terminate_action == 'continue') {
											var nTmpCnt = parseInt(total_processed) + parseInt(limit);
											nTmpCnt = nTmpCnt > total ? total : nTmpCnt;

											jQuery('#gd_processed', cont).val(nTmpCnt);

											jQuery('#gd-import-done', cont).text(nTmpCnt);
											if (parseInt(total) > 0) {
												var percentage = ((parseInt(nTmpCnt) / parseInt(total)) * 100);
												percentage = percentage > 100 ? 100 : percentage;
												jQuery('#gd-import-perc', cont).text(parseInt(percentage) + '%');
												jQuery('.gd-fileprogress', cont).css({
													'width': percentage + '%'
												});
											}

											if (type=='cat') {
												clearTimeout(timoutC);
												timoutC = setTimeout(function () {
													gd_imex_StartImport(el, type);
												}, 0);
											}
											if (type=='post') {
												clearTimeout(timoutP);
												timoutP = setTimeout(function () {
													gd_imex_StartImport(el, type);
												}, 0);
											}
											if (type=='review') {
												clearTimeout(timoutR);
												timoutR = setTimeout(function () {
													gd_imex_StartImport(el, type);
												}, 0);
											}
										} else {
											jQuery('#gd_import_data', cont).hide();
											jQuery('#gd_stop_import', cont).hide();
											jQuery('#gd_process_data', cont).hide();
											jQuery('#gd_continue_data', cont).show();
											return false;
										}
									} else {
										jQuery('#gd_import_data', cont).removeAttr('disabled').show();
										jQuery('#gd_stop_import', cont).hide();
										jQuery('#gd_process_data', cont).hide();
										return false;
									}
								}
							} else {
								jQuery('#gd_import_data', cont).removeAttr('disabled').show();
								jQuery('#gd_stop_import', cont).hide();
								jQuery('#gd_process_data', cont).hide();
							}
						},
						error: function (errorThrown) {
							jQuery('#gd_import_data', cont).removeAttr('disabled').show();
							jQuery('#gd_stop_import', cont).hide();
							jQuery('#gd_process_data', cont).hide();
							console.log(errorThrown);
						}
					});
				}

				function gd_imex_log_errors(errors){
					jQuery.each(errors, function( index, value ) {
						jQuery( "#gd-csv-errors" ).append( "<p class='m-0 p-0 small'>"+value+"</p>" );
						jQuery( "#gd-csv-errors" ).addClass('show error py-2');
						jQuery( "#gd-import-errors" ).show();
					});
				}

				function gd_imex_TerminateImport(el, type) {
					var cont = jQuery(el).closest('.gd-imex-box');
					if (!cont.length) {
						cont = jQuery('#gd_importer').parent();
					}
					jQuery('#gd_terminateaction', cont).val('terminate');
					jQuery('#gd_import_data', cont).hide();
					jQuery('#gd_stop_import', cont).hide();
					jQuery('#gd_process_data', cont).hide();
					jQuery('#gd_continue_data', cont).show();
				}

				function gd_imex_ContinueImport(el, type) {
					var cont = jQuery(el).closest('.gd-imex-box');
					if (!cont.length) {
						cont = jQuery('#gd_importer').parent();
					}
					var processed = jQuery('#gd_processed', cont).val();
					var total = jQuery('#gd_total', cont).val();
					if (parseInt(processed) > parseInt(total)) {
						jQuery('#gd_stop_import', cont).hide();
					} else {
						jQuery('#gd_stop_import', cont).show();
					}
					jQuery('#gd_import_data', cont).show();
					jQuery('#gd_import_data', cont).attr('disabled', 'disabled');
					jQuery('#gd_process_data', cont).css({
						'display': 'inline-block'
					});
					jQuery('#gd_continue_data', cont).hide();
					jQuery('#gd_terminateaction', cont).val('continue');

					if (type=='cat') {
						clearTimeout(timoutC);
						timoutC = setTimeout(function () {
							gd_imex_StartImport(el, type);
						}, 0);
					}

					if (type=='post') {
						clearTimeout(timoutP);
						timoutP = setTimeout(function () {
							gd_imex_StartImport(el, type);
						}, 0);
					}
					if (type=='review') {
						clearTimeout(timoutR);
						timoutR = setTimeout(function () {
							gd_imex_StartImport(el, type);
						}, 0);
					}
				}

				function gd_imex_showStatusMsg(el, type) {
					var cont = jQuery(el).closest('.gd-imex-box');
					if (!cont.length) {
						cont = jQuery('#gd_importer').parent();
					}

					var total = parseInt(jQuery('#gd_total', cont).val());
					var processed = parseInt(jQuery('#gd_processed', cont).val());
					var created = parseInt(jQuery('#gd_created', cont).val());
					var updated = parseInt(jQuery('#gd_updated', cont).val());
					var skipped = parseInt(jQuery('#gd_skipped', cont).val());
					var invalid = parseInt(jQuery('#gd_invalid', cont).val());
					var images = parseInt(jQuery('#gd_images', cont).val());
					if (type=='post') {
						var invalid_addr = parseInt(jQuery('#gd_invalid_addr', cont).val());
					}

					var gdMsg = '';
					if ( processed > 0 ) {
						var msgParse = '<p><?php echo addslashes( sprintf( __( 'Total %s item(s) found.', 'geodirectory' ), '%s' ) );?></p>';
						msgParse = msgParse.replace("%s", processed);
						gdMsg += msgParse;
					}

					if ( updated > 0 ) {
						var msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) updated.', 'geodirectory' ), '%s', '%d' ) );?></p>';
						msgParse = msgParse.replace("%s", updated);
						msgParse = msgParse.replace("%d", processed);
						gdMsg += msgParse;
					}

					if ( created > 0 ) {
						var msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) added.', 'geodirectory' ), '%s', '%d' ) );?></p>';
						msgParse = msgParse.replace("%s", created);
						msgParse = msgParse.replace("%d", processed);
						gdMsg += msgParse;
					}

					if ( skipped > 0 ) {
						var msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) ignored due to already exists.', 'geodirectory' ), '%s', '%d' ) );?></p>';
						msgParse = msgParse.replace("%s", skipped);
						msgParse = msgParse.replace("%d", processed);
						gdMsg += msgParse;
					}

					if (type=='post' && invalid_addr > 0) {
						var msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) could not be added due to blank/invalid address(city, region, country, latitude, longitude).', 'geodirectory' ), '%s', '%d' ) );?></p>';
						msgParse = msgParse.replace("%s", invalid_addr);
						msgParse = msgParse.replace("%d", total);
						gdMsg += msgParse;
					}

					if (invalid > 0) {
						var msgParse;

						if (type=='review') {
							msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) could not be added due to invalid comment content / post ID / rating / user details(user id or author name, author email).', 'geodirectory' ), '%s', '%d' ) );?></p>';
						} else {
							msgParse = '<p><?php echo addslashes( sprintf( __( '%s / %s item(s) could not be added due to blank title/invalid post type/invalid characters used in data.', 'geodirectory' ), '%s', '%d' ) );?></p>';
						}
						msgParse = msgParse.replace("%s", invalid);
						msgParse = msgParse.replace("%d", total);
						gdMsg += msgParse;
					}

					if (images > 0) {
						gdMsg += '<p><?php echo addslashes( $upload_dir );?></p>';
					}
					if ( ( updated > 0 || created > 0 ) && type=='post' ) {
						gdMsg += '<p><i class="fas fa-info-circle" aria-hidden="true"></i> <?php echo addslashes( sprintf( __( 'Visit GeoDirectory -> Status -> Tools -> Term counts. Run that tool to update term counts after your import. Check more at %shere%s.', 'geodirectory' ), '<a href="https://wpgeodirectory.com/documentation/article/explore-settings/settings-overview-for-gd-status-tools" target="_blank">', '</a>' ) ); ?></p>';
					}
					jQuery('#gd-import-msg', cont).find('#message').removeClass('error').addClass('updated').html(gdMsg);
					jQuery('#gd-import-msg', cont).show();
					return;
				}



				jQuery(function($){

					var intIp;
					var intIc;

					jQuery(".gd-imex-pupload").on("click",function () {
						var $this = this;
						var $cont = jQuery($this).closest('.card-body');
						clearInterval(intIp);
						intIp = setInterval(function () {
							if (jQuery($cont).find('.gd-imex-file').val()) {
								jQuery($cont).find('.gd-imex-btns').show();
							}
						}, 1000);
					});

					jQuery(".gd-imex-cupload").on("click",function () {
						var $this = this;
						var $cont = jQuery($this).closest('.card-body');
						clearInterval(intIc);
						intIc = setInterval(function () {
							if (jQuery($cont).find('.gd-imex-file').val()) {
								jQuery($cont).find('.gd-imex-btns').show();
							}
						}, 1000);
					});
					
					$('#gd_ie_download_sample').on("click",function(e) {
						if ($(this).data('sample-csv')) {
							window.location.href = $(this).data('sample-csv');
							return false;
						}
					});

					jQuery('.gd-import-export .geodir_event_csv_download a').addClass('button-secondary');

					jQuery( '.gd_progressbar' ).each(function(){
						jQuery(this).progressbar({value:0});
					});

					var timer_posts;
					var pseconds;
					jQuery('#gd_ie_exposts_submit').on("click",function(){
						pseconds = 1;

						jQuery('.gd-export-listings-progress').show();

						var el = jQuery(this).closest('#gd_ie_ex_posts');
						var post_type = jQuery(el).find('#gd_post_type').val();
						if ( !post_type ) {
							jQuery(el).find('#gd_post_type').focus();
							return false;
						}
						window.clearInterval(timer_posts);

						jQuery(this).prop('disabled', true);

						timer_posts = window.setInterval( function() {
							jQuery(el).find(".gd_timer").gdposts_timer();
						}, 1000);

						var chunk_size = parseInt(jQuery('#gd_chunk_size', el).val());
						var total_posts = parseInt(jQuery('option:selected', jQuery(el).find('#gd_post_type')).attr('data-posts'));
						chunk_size = chunk_size < 50 || chunk_size > 100000 ? 5000 : chunk_size;
						if (chunk_size > total_posts) {
							chunk_size = total_posts;
						}
						var pages = Math.ceil( total_posts / chunk_size );

						var filters = '';
						var v;
						jQuery('[name^="gd_imex["]', el).each(function() {
							v = jQuery(this).val();
							v = typeof v == 'string' && v !== '' ? v.trim() : '';
							if (v !== '') {
								filters += '&' + jQuery(this).prop('name') + '=' + v;
							}
						});

						gd_process_export_posts(el, post_type, total_posts, chunk_size, pages, 1, filters, true);
						return false; // prevent submit
					});

					jQuery.fn.gdposts_timer = function() {
						pseconds++;
						jQuery(this).text( pseconds.toString().toHMS() );
					}

					var timer_cats;
					var cseconds;
					jQuery('#gd_ie_excats_submit').on("click",function(){
						cseconds = 1;

						jQuery('.gd-export-categories-progress').show();

						var el = jQuery(this).closest('.card-body');
						var post_type = jQuery(el).find('#gd_post_type').val();
						if ( !post_type ) {
							jQuery(el).find('#gd_post_type').focus();
							return false;
						}
						window.clearInterval(timer_cats);

						jQuery(this).prop('disabled', true);

						timer_cats = window.setInterval( function() {
							jQuery(el).find(".gd_timer").gdcats_timer();
						}, 1000);

						var chunk_size = parseInt(jQuery('#gd_chunk_size', el).val());
						var total_cats = parseInt(jQuery('option:selected', jQuery(el).find('#gd_post_type')).attr('data-cats'));
						chunk_size = chunk_size < 50 || chunk_size > 100000 ? 5000 : chunk_size;
						if (chunk_size > total_cats) {
							chunk_size = total_cats;
						}
						var pages = Math.ceil( total_cats / chunk_size );

						gd_process_export_cats(el, post_type, total_cats, chunk_size, pages, 1);
						return false;//prevent form submit
					});

					jQuery.fn.gdcats_timer = function() {
						cseconds++;
						jQuery(this).text( cseconds.toString().toHMS() );
					}

					String.prototype.toHMS = function () {
						var sec_num = parseInt(this, 10); // don't forget the second param
						var hours   = Math.floor(sec_num / 3600);
						var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
						var seconds = sec_num - (hours * 3600) - (minutes * 60);

						if (hours   < 10) {hours   = "0"+hours;}
						if (minutes < 10) {minutes = "0"+minutes;}
						if (seconds < 10) {seconds = "0"+seconds;}
						var time    = hours+':'+minutes+':'+seconds;
						return time;
					}

					function gd_process_export_posts(el, post_type, total_posts, chunk_size, pages, page, filters, doFilter) {

						var attach = (typeof filters !== 'undefined' && filters) ? filters : '';
						var getTotal = false;
						if (page < 2) {
							if (typeof filters !== 'undefined' && filters && doFilter) {
								getTotal = true;
								attach += '&_c=1';
								gd_progressbar(el, 0, '<i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Preparing...', 'geodirectory' ) );?>');
							} else {
								gd_progressbar(el, 0, '0% (0 / ' + total_posts + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Exporting...', 'geodirectory' ) );?>');
							}
							jQuery(el).find('#gd_timer').text('00:00:01');
							jQuery('#gd_ie_ex_files', el).html('');
						}

						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=export_posts&_pt=' + post_type + '&_n=' + chunk_size + '&_nonce=<?php echo $nonce;?>&_p=' + page + attach,
							dataType : 'json',
							cache: false,
							beforeSend: function (jqXHR, settings) {},
							success: function( data ) {
								jQuery(el).find('input[type="submit"]').prop('disabled', false);

								if (typeof data == 'object') {
									if (typeof data.success != 'undefined' && data.success == false) {
										gd_progressbar(el, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + data.data);
										window.clearInterval(timer_posts);
									} else {
										if (getTotal) {
											if (typeof data.total != 'undefined' ) {
												total_posts = parseInt(data.total);
												if (chunk_size > total_posts) {
													chunk_size = total_posts;
												}
												pages = Math.ceil( total_posts / chunk_size );

												return gd_process_export_posts(el, post_type, total_posts, chunk_size, pages, 1, filters);
											}
										} else {
											if (pages < page || pages == page) {
												window.clearInterval(timer_posts);
												gd_progressbar(el, 100, '100% (' + total_posts + ' / ' + total_posts + ') <i class="fas fa-check" aria-hidden="true"></i><?php echo esc_attr( __( 'Complete!', 'geodirectory' ) );?>');
											} else {
												var percentage = Math.round(((page * chunk_size) / total_posts) * 100);
												percentage = percentage > 100 ? 100 : percentage;
												gd_progressbar(el, percentage, '' + percentage + '% (' + ( page * chunk_size ) + ' / ' + total_posts + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Exporting...', 'geodirectory' ) );?>');
											}
											if (typeof data.files != 'undefined' && jQuery(data.files).length ) {
												var obj_files = data.files;
												var files = '';
												for (var i in data.files) {
													files += '<p>'+ obj_files[i].i +' <a download="' + ((obj_files[i].u).replace(/^.*[\\\/]/, '')) + '" class="gd-ie-file" href="' + obj_files[i].u + '" target="_blank">' + obj_files[i].u + '</a> (' + obj_files[i].s + ')</p>';
												}
												jQuery('#gd_ie_ex_files', el).append(files);
												if (pages > page) {
													return gd_process_export_posts(el, post_type, total_posts, chunk_size, pages, (page + 1));
												}
												return true;
											}
										}
									}
								}
							},
							error: function( data ) {
								jQuery(el).find('input[type="submit"]').prop('disabled', false);
								window.clearInterval(timer_posts);
								return;
							},
							complete: function( jqXHR, textStatus  ) {
								return;
							}
						});
					}

					function gd_process_export_cats(el, post_type, total_cats, chunk_size, pages, page) {
						if (page < 2) {
							gd_progressbar(el, 0, '0% (0 / ' + total_cats + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Exporting...', 'geodirectory' ) );?>');
							jQuery(el).find('#gd_timer').text('00:00:01');
							jQuery('#gd_ie_ex_files', el).html('');
						}

						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=export_cats&_pt=' + post_type + '&_n=' + chunk_size + '&_nonce=<?php echo $nonce;?>&_p=' + page,
							dataType : 'json',
							cache: false,
							beforeSend: function (jqXHR, settings) {},
							success: function( data ) {
								jQuery(el).find('input[type="submit"]').prop('disabled', false);

								if (typeof data == 'object') {
									if (typeof data.success != 'undefined' && data.success == false) {
										gd_progressbar(el, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + data.data);
										window.clearInterval(timer_cats);
									} else {
										if (pages < page || pages == page) {
											window.clearInterval(timer_cats);
											gd_progressbar(el, 100, '100% (' + total_cats + ' / ' + total_cats + ') <i class="fas fa-check" aria-hidden="true"></i><?php echo esc_attr( __( 'Complete!', 'geodirectory' ) );?>');
										} else {
											var percentage = Math.round(((page * chunk_size) / total_cats) * 100);
											percentage = percentage > 100 ? 100 : percentage;
											gd_progressbar(el, percentage, '' + percentage + '% (' + ( page * chunk_size ) + ' / ' + total_cats + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php esc_attr_e( 'Exporting...', 'geodirectory' );?>');
										}
										if (typeof data.files != 'undefined' && jQuery(data.files).length ) {
											var obj_files = data.files;
											var files = '';
											for (var i in data.files) {
												files += '<p>'+ obj_files[i].i +' <a class="gd-ie-file" download="' + ((obj_files[i].u).replace(/^.*[\\\/]/, '')) + '" href="' + obj_files[i].u + '" target="_blank">' + obj_files[i].u + '</a> (' + obj_files[i].s + ')</p>';
											}
											jQuery('#gd_ie_ex_files', el).append(files);
											if (pages > page) {
												return gd_process_export_cats(el, post_type, total_cats, chunk_size, pages, (page + 1));
											}
											return true;
										}
									}
								}
							},
							error: function( data ) {
								jQuery(el).find('input[type="submit"]').prop('disabled', false);
								window.clearInterval(timer_cats);
								return;
							},
							complete: function( jqXHR, textStatus  ) {
								return;
							}
						});
					}
					
					// Export
					var timer, vSec;
					jQuery('#gd_start_export').on('click', function(e) {
						geodir_start_export(this);
						e.preventDefault();
						return false;
					});
					$.fn.gdtimer = function(vSec) {
						$(this).text(vSec.toString().toHMS());
					}

					function geodir_start_export(el) {
						var $this, $parent, sExport, fields = '', iPerPage;
						$this = $(el);
						jQuery('.gd-export-reviews-progress').show();
						$parent = $this.closest('.card-body');
						sExport = $this.data('export');
						iPerPage = parseInt($('#gd_chunk_size', $parent).val());
						if (!sExport) {
							return false;
						}
						vSec = 1;
						jQuery('[name^="gd_imex["]', $parent).each(function() {
							v = $(this).val(), name=$(this).prop('name');
							if ($(this).is(':checkbox')) {
								if (!$(this).is(':checked')) {
									return true;
								}
							}
							if (v && (typeof v == 'object' || typeof v == 'array')) {
								jQuery.each(v, function(_i, _v) {
									fields += '&' + name + '=' + (_v !== '' ? _v.trim() : '');
								});
							} else {
								v = typeof v == 'string' && v !== '' ? v.trim() : '';
								if (v != 'undefined') {
									fields += '&' + name + '=' + v;
								}
							}
						});
						$this.prop('disabled', true);
						window.clearInterval(timer);
						timer = window.setInterval(function() {
							vSec++;
							$('.gd_timer', $parent).gdtimer(vSec);
						}, 1000);
						
						gd_progressbar($parent, 0, '<i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Preparing export...', 'geodirectory' ) ); ?>');
						$('#gd_timer', $parent).text('00:00:01');
						$('#gd_ie_ex_files', $parent).html('');
						
						data = fields + '&_export=' + sExport; 
						
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=prepare_export&_nonce=<?php echo $nonce;?>' + data,
							dataType: 'json',
							cache: false,
							beforeSend: function(xhr, settings) {},
							success: function(res) {
								var err = true;
								if (typeof res == 'object') {
									if (res.total != 'undefined') {
										if ( parseInt(res.total) > 0) {
											err = false;
											$this.data('total', res.total);
											gd_progressbar($parent, 0, '0% (0 / ' + res.total + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php echo esc_attr( __( 'Exporting...', 'geodirectory' ) );?>');
											geodir_export($parent, sExport, fields, iPerPage, 1);
										} else {
											gd_progressbar($parent, 0, '<i class="fas fa-info-circle" aria-hidden="true"></i><?php echo esc_attr( __( 'No records to export.', 'geodirectory' ) );?>');
										}
									} else {
										if (typeof res.success != 'undefined' && res.success == false) {
											gd_progressbar($parent, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + res.data);
										}
									}
								}
								if (err) {
									$this.prop('disabled', false);
									window.clearInterval(timer);
								}
							},
							error: function(xhr, sStatus, oErr) {
								console.log(sStatus + ': ' + oErr.message);
								$this.prop('disabled', false);
								gd_progressbar($parent, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + sStatus + ': ' + xhr.responseText);
								window.clearInterval(timer);
								return;
							},
							complete: function(xhr, sStatus) {
								return;
							}
						});
					}

					function geodir_export($parent, sExport, fields, iPerPage, iPage) {
						var $submit, iTotal, iPages;
						$submit = $('input[type="submit"]', $parent);
						iTotal = parseInt($submit.data('total'));
						iPages = Math.ceil( iTotal / iPerPage );
						
						data = fields + '&_export=' + sExport + '&_c=' + iTotal + '&_n=' + iPerPage + '&_p=' + iPage; 
						
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=export&_nonce=<?php echo $nonce;?>' + data,
							dataType: 'json',
							cache: false,
							beforeSend: function(xhr, settings) {},
							success: function(res) {
								$submit.prop('disabled', false);
								if (typeof res == 'object') {
									if (typeof res.success != 'undefined' && res.success == false) {
										gd_progressbar($parent, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + res.data);
										window.clearInterval(timer);
									} else {
										if (iPages < iPage || iPages == iPage) {
											window.clearInterval(timer);
											gd_progressbar($parent, 100, '100% (' + iTotal + ' / ' + iTotal + ') <i class="fas fa-check" aria-hidden="true"></i><?php echo esc_attr( __( 'Complete!', 'geodirectory ' ) );?>');
										} else {
											var percentage = Math.round(((iPage * iPerPage) / iTotal) * 100);
											percentage = percentage > 100 ? 100 : percentage;
											gd_progressbar($parent, percentage, percentage + '% (' + (iPage * iPerPage) + ' / ' + iTotal + ') <i class="fas fa-sync fa-spin" aria-hidden="true"></i><?php esc_attr_e( 'Exporting...', 'geodirectory ' );?>');
										}
										if (typeof res.files != 'undefined' && $(res.files).length) {
											var obj_files = res.files;
											var files = '';
											for (var i in res.files) {
												files += '<p>' + obj_files[i].i + ' <a class="gd-ie-file" download="' + ((obj_files[i].u).replace(/^.*[\\\/]/, '')) + '" href="' + obj_files[i].u + '" target="_blank">' + obj_files[i].u + '</a> (' + obj_files[i].s + ')</p>';
											}
											$('#gd_ie_ex_files', $parent).append(files);
											if (iPages > iPage) {
												return geodir_export($parent, sExport, fields, iPerPage, (iPage + 1));
											}
											return true;
										}
									}
								}
							},
							error: function(xhr, sStatus, oErr) {
								console.log(sStatus + ': ' + oErr.message);
								$submit.prop('disabled', false);
								if (page < 2) {
									gd_progressbar($parent, 0, '<i class="fas fa-exclamation-triangle" aria-hidden="true"></i>' + sStatus + ': ' + xhr.responseText);
								}
								window.clearInterval(timer);
								return;
							},
							complete: function(xhr, sStatus) {
								return;
							}
						});
					}
				});

				function gd_imex_FinishImport(el, type) {
					if (type=='post') {
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=import_finish&_pt=' + type + '&_nonce=<?php echo $nonce; ?>',
							dataType : 'json',
							cache: false,
							success: function (data) {
								//import done
							}
						});
					}
				}

				function gd_imex_import_settings(el, type) {

					//alert('import');

					var cont = jQuery(el).closest('.card-body');
					var gd_prepared = jQuery('#gd_prepared', cont).val();
					var uploadedFile = jQuery('#gd_im_' + type, cont).val();
					jQuery('gd-import-msg', cont).hide();
					if(gd_prepared == uploadedFile) {
						gd_imex_ContinueImport(el, type);
						jQuery('#gd_import_data', cont).attr('disabled', 'disabled');
					} else {
						jQuery.ajax({
							url: ajaxurl,
							type: "POST",
							data: 'action=geodir_import_export&task=import_settings&_file=' + uploadedFile + '&_nonce=<?php echo $nonce;?>',
							dataType: 'json',
							cache: false,
							success: function(data) {
								if(typeof data == 'object') {
									if(data.success) {
										jQuery('#gd-import-msg', cont).find('#message').removeClass('error').addClass('updated').html( data.data );
										jQuery('#gd-import-msg', cont).show();
									} else{
										jQuery('#gd-import-msg', cont).find('#message').removeClass('updated').addClass('error').html( data.data );
										jQuery('#gd-import-msg', cont).show();
									}
								}
							},
							error: function(errorThrown) {
								console.log(errorThrown);
							}
						});
					}
				}
				
			</script>
			<?php
		}
	}

endif;

return new GeoDir_Settings_Import_Export();

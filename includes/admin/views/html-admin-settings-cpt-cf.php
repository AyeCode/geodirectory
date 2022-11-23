<div id="container_general" class="container-fluid p-0">

	<input type="hidden" name="gd_new_field_nonce" id="gd_new_field_nonce" value="<?php echo wp_create_nonce( 'gd_new_field_nonce' ); ?>"/>
	<input type="hidden" name="listing_type" id="gd_new_post_type" value="<?php echo self::$post_type; ?>"/>
	<input type="hidden" name="manage_field_type" class="manage_field_type" value="<?php echo self::$sub_tab; ?>" />

	<div class="general-form-builder-frame row text-dark">

		<div class="col">
			<!-- required for tabs to work -->
			<ul class="nav nav-tabs d-none " role="tablist">
				<li class="nav-item" role="presentation">
					<a class="nav-link active" id="gd-fields-tab" data-toggle="tab" href="#geodir-available-fields" role="tab" aria-controls="home" aria-selected="true"></a>
				</li>
				<li class="nav-item" role="presentation">
					<a class="nav-link" id="gd-field-settings-tab" data-toggle="tab" href="#geodir-field-settings" role="tab" aria-controls="profile" aria-selected="false"></a>
				</li>
			</ul>
			<!-- required for tabs to work -->


			<div class="tab-content sticky-top" style="top: 40px;">

				<div class="side-sortables side-sortables tab-pane fade show active card p-0 mw-100 w-100 border-0 shadow-sm" id="geodir-available-fields" role="tabpanel">
					<div class="card-header bg-white">
						<h2 class="h5 mb-0 text-dark py-1">
							<?php
							echo self::left_panel_title();
							echo geodir_help_tip( self::left_panel_note() );
							?>
						</h2>
					</div>

					<div class="card-body scrollbars-ios" style="max-height: 70vh; overflow-y:auto;">
					<?php echo self::left_panel_content(); ?>
					</div>

				</div>

				<div class="gd-form-settings-view tab-pane fade card p-0 mw-100 w-100 border-0 shadow-sm" id="geodir-field-settings" role="tabpanel">
					<div class="card-header bg-white d-flex justify-content-between">
						<h2 class="h5 mb-0 text-dark py-1">
							<?php
							_e( 'Field Settings', 'geodirectory' );
							?>
						</h2>
						<?php
						global $aui_bs5;
						if ( $aui_bs5 ) {
							?>
							<button type="button" class="btn-close align-self-center" aria-label="Close" onclick="gd_tabs_close_settings(this);"></button>
						<?php } else { ?>
							<button type="button" class="close" aria-label="Close" onclick="gd_tabs_close_settings(this);">
								<span aria-hidden="true">&times;</span>
							</button>
						<?php } ?>
					</div>

					<form></form> <!-- required as chrome removes first empty form -->
					<form class="gd-form-settings-form">
					<div class="card-body scrollbars-ios" style="max-height: 70vh; overflow-y:auto;">
					</div>
					</form>

					<div class="card-footer text-right text-end">
					</div>

				</div>



			</div>
		</div>
		<!--side-sortables -->

		<div class="col">
			<div class="side-sortables card p-0 mw-100 w-100 border-0 shadow-sm" id="geodir-selected-fields">
				<div class="card-header bg-white">
					<h2 class="h5 mb-0 text-dark py-1">
						<?php
						echo self::right_panel_title();
						echo geodir_help_tip( self::right_panel_note() );
						?>
					</h2>
				</div>

				<div class="card-body">
				 <?php echo self::right_panel_content(); ?>
				</div>

			</div>
		</div>

	</div>
	<!--general-form-builder-frame -->
</div> <!--container_general -->

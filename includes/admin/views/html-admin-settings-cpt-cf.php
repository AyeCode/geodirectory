<div id="container_general" class="clearfix">

	<input type="hidden" name="gd_new_field_nonce" id="gd_new_field_nonce" value="<?php echo wp_create_nonce( 'gd_new_field_nonce' );?>"/>
	<input type="hidden" name="listing_type" id="gd_new_post_type" value="<?php echo self::$post_type;?>"/>
	<input type="hidden" name="manage_field_type" class="manage_field_type" value="<?php echo self::$sub_tab; ?>" />

	<div class="general-form-builder-frame">

		<div class="side-sortables" id="geodir-available-fields">
			
			<h3 class="hndle">
				<span>
					<?php echo self::left_panel_title(); ?>
				</span>
			</h3>


			<p><?php echo self::left_panel_note(); ?></p>

			<?php echo self::left_panel_content(); ?>

		</div>
		<!--side-sortables -->


		<div class="side-sortables" id="geodir-selected-fields">
			<h3 class="hndle">
				<span>
					<?php echo self::right_panel_title(); ?>
				</span>
			</h3>
			<?php
			/**
			 * Filter custom field selected fields note text.
			 *
			 * @since 1.0.0
			 *
			 * @param string $sub_tab Sub tab name.
			 * @param string $listing_type Post type.
			 */
			?>
			<p><?php echo self::right_panel_note(); ?></p>

			<?php echo self::right_panel_content(); ?>
			
			
		</div>

	</div>
	<!--general-form-builder-frame -->
</div> <!--container_general -->
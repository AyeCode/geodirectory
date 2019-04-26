<li class="dd-item" data-id="1" id="setName_<?php echo $tab->id;?>">
	<div class="dd-form">
		<i class="fas fa-caret-down" aria-hidden="true" onclick="gd_tabs_item_settings(this);"></i>
		<div class="dd-handle">
			<?php echo $tab_icon; ?>
			<?php echo esc_attr($tab->tab_name);?>
			<span class="dd-key" title="<?php _e('Open/Close','geodirectory');?>"><?php echo esc_attr($tab->tab_key). ' ('.esc_attr($tab->tab_type).')';?></span>
		</div>
		<div class="dd-setting <?php echo 'dd-type-'.$tab->tab_type;?>">
				<?php echo geodir_notification( array('gd-warn'=>__('Name and Icon settings are not used for sub items except fieldset.','geodirectory')) );?>

			<p class="dd-setting-name">
				<label for="gd-tab-name-<?php echo $tab->id;?>">
					<?php _e('Name:','geodirectory') ?><br>
					<input type="text" name="tab_name" id="gd-tab-name-<?php echo $tab->id;?>" value="<?php echo esc_attr($tab->tab_name);?>">
				</label>
			</p>
			<p class="dd-setting-icon">
				<label for="gd-tab-icon-<?php echo $tab->id;?>">
					<?php _e('Icon (optional):','geodirectory'); ?><br>
					<select
						id="gd-tab-icon-<?php echo $tab->id;?>"
						name="tab_icon"
						class="regular-text geodir-select"
						data-fa-icons="1"  tabindex="-1" aria-hidden="true"
					>
						<?php
						include_once( dirname( __FILE__ ) . '/../settings/data_fontawesome.php' );
						echo "<option value=''>".__('None','geodirectory')."</option>";
						$tab_icon = $tab->tab_icon;
						foreach ( geodir_font_awesome_array() as $key => $val ) {
							?>
							<option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php
							selected( $tab_icon, $key );
							?>><?php echo $key ?></option>
							<?php
						}
						?>
					</select>
				</label>
			</p>
			<?php
			if($tab->tab_type=='shortcode'){
				?>
				<p>
					<label for="gd-tab-content-<?php echo $tab->id;?>">
						<?php _e('Tab content:','geodirectory');
						if($tab->tab_type=='shortcode'){
							echo WP_Super_Duper::shortcode_button("'gd-tab-content-".$tab->id."'");
//							echo ' <a href="#TB_inline?width=100%&height=550&inlineId=super-duper-content" class="thickbox button super-duper-content-open" title="'. __('Add Shortcode','geodirectory').'"><i class="fas fa-cubes" aria-hidden="true"></i></a>';
						}
						?><br>
						<textarea name="tab_content" id="gd-tab-content-<?php echo $tab->id;?>" placeholder="<?php _e('Add shortcode here.','geodirectory');?>"><?php echo stripslashes($tab->tab_content);?></textarea>
					</label>
				</p>
				<?php
			}else{
				echo '<input type="hidden" name="tab_content" value=\''.stripslashes($tab->tab_content).'\'>';
			}
			?>
			<input type="hidden" name="id" value="<?php echo $tab->id;?>">
			<input type="hidden" name="post_type" value="<?php echo $tab->post_type;?>">
			<input type="hidden" name="tab_layout" value="<?php echo $tab->tab_layout;?>">
			<input type="hidden" name="tab_type" value="<?php echo $tab->tab_type;?>">
			<input type="hidden" name="tab_key" value="<?php echo $tab->tab_key;?>">
			

			<p class="gd-tab-actions">
				<a class="item-delete submitdelete deletion" id="delete-16" href="javascript:void(0);" onclick="gd_tabs_delete_tab(this);return false;"><?php _e("Remove","geodirectory");?></a>
				<input type="button" class="button button-primary" name="save" id="save" value="<?php _e("Save","geodirectory");?>" onclick="gd_tabs_save_tab(this);return false;">
			</p>
		</div>
	</div>
</li>

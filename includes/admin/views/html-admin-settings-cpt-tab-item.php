<li class="dd-item" data-id="1">
	<form>
		<i class="fa fa-caret-down" aria-hidden="true" onclick="gd_tabs_item_settings(this);"></i>
		<div class="dd-handle">
			<i class="fa <?php echo esc_attr($tab->tab_icon);?>" aria-hidden="true"></i>
			<?php echo esc_attr($tab->tab_name);?>
			<span class="dd-key"><?php echo esc_attr($tab->tab_key);?></span>
		</div>
		<div class="dd-setting">
			<p>
				<label for="gd-tab-name-<?php echo $tab->id;?>">
					<?php _e('Name:','geodirectory') ?><br>
					<input type="text" id="gd-tab-name-<?php echo $tab->id;?>" value="<?php echo esc_attr($tab->tab_name);?>">
				</label>
			</p>
			<p>
				<label for="gd-tab-icon-<?php echo $tab->id;?>">
					<?php _e('Icon (optional):','geodirectory') ?><br>
					<select
						id="gd-tab-icon-<?php echo $tab->id;?>"
						class="regular-text geodir-select"
						data-fa-icons="1"  tabindex="-1" aria-hidden="true"
					>
						<?php
						include_once( dirname( __FILE__ ) . '/../settings/data_fontawesome.php' );
						echo "<option value=''>".__('None','geodirectory')."</option>";
						foreach ( geodir_font_awesome_array() as $key => $val ) {
							?>
							<option value="<?php echo esc_attr( $key ); ?>" data-fa-icon="<?php echo esc_attr( $key ); ?>" <?php


							selected( $tab->tab_icon, $key );

							?>><?php echo $key ?></option>
							<?php
						}
						?>
					</select>
				</label>
			</p>
		</div>
	</form>
</li>

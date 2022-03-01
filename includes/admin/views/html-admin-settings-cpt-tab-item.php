<li class="dd-item " data-id="<?php echo esc_attr( $tab->id );?>" id="setName_<?php echo esc_attr( $tab->id );?>" >
	<div class="hover-shadow dd-form d-flex justify-content-between rounded c-pointer list-group-item border rounded-smx text-left bg-light" onclick="gd_tabs_item_settings(this);">
		<div class="  flex-fill font-weight-bold">
			<?php echo $tab_icon; ?>
			<?php echo esc_attr($tab->tab_name);?>
			<span class="float-right text-right small" title="<?php _e('Open/Close','geodirectory');?>"><?php echo esc_attr($tab->tab_key). ' ('.esc_attr($tab->tab_type).')';?></span>
		</div>
		<div class="dd-handle">

			<i class="far fa-trash-alt text-danger ml-2" id="delete-16"  onclick="gd_tabs_delete_tab(this);event.stopPropagation();return false;"></i>
			<i class="fas fa-grip-vertical text-muted ml-2" style="cursor: move" aria-hidden="true" ></i>

		</div>
		<script type="text/template" class="dd-setting <?php echo 'dd-type-'.esc_attr( $tab->tab_type );?> d-none ">
			<?php
			//include_once( dirname( __FILE__ ) . '/../settings/data_fontawesome.php' );
			echo geodir_notification( array('info'=>__('Name and Icon settings are not used for sub items except fieldset.','geodirectory')) );

			echo aui()->input(
				array(
					'id'                => 'gd-tab-name-'.esc_attr( $tab->id ),
					'name'              => 'tab_name',
					'label_type'        => 'top',//'horizontal',
//				'label_class'=> 'font-weight-bold',
					'label'              => __('Name','geodirectory'),
					'type'              =>   'text',
					'value' => esc_attr($tab->tab_name),
				)
			);

			echo aui()->input(
				array(
					'id'                => 'gd-tab-icon-'.esc_attr( $tab->id ),
					'name'              => 'tab_icon',
					'label_type'        => 'top',//'horizontal',
//				'label_class'=> 'font-weight-bold',
					'placeholder'      => esc_attr__( 'Select icon', 'geodirectory' ),
					'label'              => __('Icon','geodirectory'),
					'type'              =>   'iconpicker',
					'value' => esc_attr($tab->tab_icon),
				)
			);


			if($tab->tab_type=='shortcode'){
				$textarea =  aui()->textarea(array(
					'name'       => 'tab_content',
					'class'      => '',
					'id'         => 'gd-tab-content-'.absint($tab->id),
					'placeholder'=> esc_html__( "Add shortcode here.", 'geodirectory'),
					'required'   => true,
					'label_type'        => 'top',
					'label'      => esc_html__( "Tab content", 'geodirectory')." {shortcode button}",
					'rows'      => 2,
					'value' =>  stripslashes($tab->tab_content)
				));

//			$sc_btn = WP_Super_Duper::shortcode_button("'gd-tab-content-".absint($tab->id)."'");
//			echo str_replace("{shortcode button}",$sc_btn,$textarea);
				echo str_replace("{shortcode button}"," <a onclick=\"sd_ajax_get_picker('gd-tab-content-".absint($tab->id)."');\" href=\"#TB_inline?width=100%&height=550&inlineId=super-duper-content-ajaxed\" class='thickbox sd-lable-shortcode-inserter super-duper-content-open badge badge-primary'>".__( "Add shortcode", 'geodirectory')."</a>",$textarea);
			}else{
				echo '<input type="hidden" name="tab_content" value=\''.stripslashes($tab->tab_content).'\'>';
			}
			?>
			<input type="hidden" name="id" value="<?php echo esc_attr( $tab->id );?>">
			<input type="hidden" name="post_type" value="<?php echo esc_attr( $tab->post_type );?>">
			<input type="hidden" name="tab_layout" value="<?php echo esc_attr( $tab->tab_layout );?>">
			<input type="hidden" name="tab_type" value="<?php echo esc_attr( $tab->tab_type );?>">
			<input type="hidden" name="tab_key" value="<?php echo esc_attr( $tab->tab_key );?>">


			<div class="gd-tab-actions text-right mb-0">
				<a class=" btn btn-link text-muted" href="javascript:void(0);" onclick="gd_tabs_close_settings(this); return false;"><?php _e("Close","geodirectory");?></a>
				<button type="button" class="btn btn-primary" name="save" id="save" data-save-text="<?php _e("Save","geodirectory");?>" onclick="gd_tabs_save_tab(this,event);jQuery(this).html('<span class=\'spinner-border spinner-border-sm\' role=\'status\'></span> <?php esc_attr_e( 'Saving', 'geodirectory' ); ?>').addClass('disabled');return false;">
					<?php _e("Save","geodirectory");?>
				</button>
			</div>
		</script>
	</div>

	<ul></ul>


</li>

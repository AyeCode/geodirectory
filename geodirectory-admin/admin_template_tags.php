<?php  
/**
 * Geodirectory Backend Admin Panel 
 * 
 * Handles the display of the main geodirectory admin panel.
 */
if (!function_exists('geodir_admin_panel')) {
	function geodir_admin_panel() {
		global $geodirectory;
		global $current_tab ;
		
		
		?>
		
        <div  id="gd-wrapper-main" class="wrap geodirectory">
            <?php 
			do_action('geodir_before_admin_panel');
            ?>
            
            <div class="gd-wrapper gd-wrapper-vr clearfix">
            
                  <div class="gd-left-nav">
                        <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/geo-logo.png" alt="geo-logo" class="geo-logo" />
                        <img src="<?php echo geodir_plugin_url();?>/geodirectory-assets/images/geo-logoalter.png" alt="geo-logo" class="geo-logoalter" />
                        <ul>
                            <?php
							$tabs = array();
                            $tabs = apply_filters('geodir_settings_tabs_array', $tabs);
                            update_option( 'geodir_tabs', $tabs );// Important to show settings menu dropdown  
											
        					foreach ($tabs as $name => $args) :
                                $label = $args['label'];
								
								
								$query_string = '';
								if(isset($args['subtabs']) && !empty($args['subtabs'])):
									
									$subtabs = $args['subtabs'];
									
									$query_string = '&subtab='.$subtabs[0]['subtab'];
										
								endif;
								
																	
                
								$tab_link = admin_url( 'admin.php?page=geodirectory&tab='. $name.$query_string );
								
								if(isset($args['url']) && $args['url'] != '')
								{
									$tab_link = $args['url'];
								}
								
                                if(!empty($args['request']))
                                    $tab_link = geodir_getlink($tab_link,$args['request']);
                              	
								if(isset($args['target']) && $args['target'] != '')
								{
									$tab_target = " target='" .$args['target']."' ";
								}
								else
									$tab_target ='';
									
								$tab_active = '';
							    if( $current_tab == $name ) 
									$tab_active = ' class="tab-active" ';
										do_action('geodir_before_settings_tabs',$name);								
                                echo '<li '.$tab_active.' ><a href="' . $tab_link . '"  '. $tab_target .' >' . $label . '</a></li>';
           				 do_action('geodir_after_settings_tabs',$name);                    
                            endforeach;
                            
                            do_action( 'geodir_settings_tabs' ); 
                            ?>
                         </ul>
                    </div> <!--gd-left-nav ends here-->
                    
                    
                    <div class="gd-content-wrapper">  
                        <div class="gd-tabs-main" >
                        		
						<?php 
						unset($subtabs) ;
                        if(isset($tabs[$current_tab]['subtabs']))
	                        $subtabs = $tabs[$current_tab]['subtabs'];
                        $form_action = '';
                        
                        if(!empty($subtabs)):?>
                        
                            <dl class="gd-tab-head">
                                <?php
                                    foreach($subtabs as $sub){
                                        
                                        $subtab_active = '';
                                        if($sub['subtab'] == $_REQUEST['subtab']){
                                            $subtab_active = 'class="gd-tab-active"';	
                                            $form_action = isset($sub['form_action']) ? $sub['form_action'] : '';
                                        }
                          
													 
													 
													$sub_tabs_link = admin_url().'admin.php?page=geodirectory&tab='.$current_tab.'&subtab='.$sub['subtab'];           
													if(isset($sub['request']) && is_array($sub['request']) && !empty($sub['request'])){
														
														$sub_tabs_link = geodir_getlink($sub_tabs_link,$sub['request']);
														
													}
															
															
																				
                                        echo '<dd '.$subtab_active.' id="claim_listing"><a href="'. $sub_tabs_link .'" >'.$sub['label'].'</a></dd>';
                                        
                                    }
                                    ?>
                            </dl>
                        
                        <?php endif;?>
														
                            <div class="gd-tab-content <?php if(empty($subtabs)){ echo "inner_contet_tabs";} ?>" >
                            	<form method="post" id="mainform" class="geodir_optionform <?php echo $current_tab.' ';?><?php if(isset($sub['subtab'])){ echo $sub['subtab'];}?>" action="<?php echo $form_action;?>" enctype="multipart/form-data">
															<input type="hidden" class="active_tab" name="active_tab" value="<?php if(isset($_REQUEST['active_tab'])){ echo $_REQUEST['active_tab'];}?>" />
								<?php wp_nonce_field( 'geodir-settings', '_wpnonce', true, true ); ?>
                                <?php wp_nonce_field( 'geodir-settings-'. $current_tab, '_wpnonce-'.$current_tab, true, true ); ?>
                                <?php do_action( 'geodir_admin_option_form' , $current_tab  ); ?>
                                </form>
                            </div>
														
                        </div>
                    </div>
                
            </div> 
        </div>
        <script type="text/javascript">
                jQuery(window).load(function(){
                
                    // Subsubsub tabs
                    jQuery('ul.subsubsub li a:eq(0)').addClass('current');
                    jQuery('.subsubsub_section .section:gt(0)').hide();
                    
                    jQuery('ul.subsubsub li a').click(function(){
                        /*jQuery('a', jQuery(this).closest('ul.subsubsub')).removeClass('current');
                        jQuery(this).addClass('current');
                        jQuery('.section', jQuery(this).closest('.subsubsub_section')).hide();
                        jQuery( jQuery(this).attr('href') ).show();
                        jQuery('#last_tab').val( jQuery(this).attr('href') );
                        return false;*/
                    });
                    
                    <?php if (isset($_GET['subtab']) && $_GET['subtab']) echo 'jQuery("ul.subsubsub li a[href=#'.$_GET['subtab'].']").click();'; ?>
                    
                    // Countries
                    jQuery('select#geodirectory_allowed_countries').change(function(){
                        if (jQuery(this).val()=="specific") {
                            jQuery(this).parent().parent().next('tr').show();
                        } else {
                            jQuery(this).parent().parent().next('tr').hide();
                        }
                    }).change();
                    
                    // Color picker
                    jQuery('.colorpick').each(function(){
                        jQuery('.colorpickdiv', jQuery(this).parent()).farbtastic(this);
                        jQuery(this).click(function() {
                            if ( jQuery(this).val() == "" ) jQuery(this).val('#');
                            jQuery('.colorpickdiv', jQuery(this).parent() ).show();
                        });	
                    });
                    jQuery(document).mousedown(function(){
                        jQuery('.colorpickdiv').hide();
                    });
                    
                    // Edit prompt
                    jQuery(function(){
                        var changed = false;
                        
                        jQuery('input, textarea, select, checkbox').change(function(){
                            changed = true;
                        });
                        
                        jQuery('.geodirectory-nav-tab-wrapper a').click(function(){
                            if (changed) {
                                window.onbeforeunload = function() {
                                    return '<?php echo __( 'The changes you made will be lost if you navigate away from this page.', GEODIRECTORY_TEXTDOMAIN); ?>';
                                }
                            } else {
                                window.onbeforeunload = '';
                            }
                        });
                        
                        jQuery('.submit input').click(function(){
                            window.onbeforeunload = '';
                        });
                    });
                    
                    // Sorting
                    jQuery('table.wd_gateways tbody').sortable({
                        items:'tr',
                        cursor:'move',
                        axis:'y',
                        handle: 'td',
                        scrollSensitivity:40,
                        helper:function(e,ui){
                            ui.children().each(function(){
                                jQuery(this).width(jQuery(this).width());
                            });
                            ui.css('left', '0');
                            return ui;
                        },
                        start:function(event,ui){
                            ui.item.css('background-color','#f6f6f6');
                        },
                        stop:function(event,ui){
                            ui.item.removeAttr('style');
                        }
                    });
                    
                    // Chosen selects
                    jQuery("select.chosen_select").chosen();
                    
                    jQuery("select.chosen_select_nostd").chosen({
                        allow_single_deselect: 'true'
                    });
                    
                });
            </script> 
        <?php 
								
	}
}


function geodir_admin_option_form($tab_name)
{
	
	//echo $tab_name.'_array.php' ;
	global $geodir_settings, $is_default, $mapzoom;
	if(file_exists(dirname(__FILE__). '/option-pages/'.$tab_name.'_array.php'))
	{
		include_once('option-pages/'.$tab_name.'_array.php');
	}
	
	$listing_type = isset($_REQUEST['listing_type']) ? $_REQUEST['listing_type'] : ''; 
	
	switch ($tab_name)
	{
		
		case 'general_settings':
		
			geodir_admin_fields( $geodir_settings['general_settings'] );
			/**
			*
			* Update Taxonomy Options *
			*
			**/
			/*add_action('updated_option_place_prefix','update_listing_prefix');
			function update_listing_prefix(){
				geodir_register_defaults();
			}*/
			
			if(isset($_REQUEST['active_tab']) && ($_REQUEST['active_tab']=='dummy_data_settings' || $_REQUEST['active_tab']=='csv_upload_settings'))
				$hide_save_button = "style='display:none;'" ;
			else
				$hide_save_button = '';
			
			$hide_save_button = apply_filters('geodir_hide_save_button', $hide_save_button);
?>

            <p class="submit">
            <input <?php echo $hide_save_button ;?> name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRECTORY_TEXTDOMAIN ); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
            </p>
            
            </div>
            
		<?php break;
		case 'design_settings' :
			geodir_admin_fields( $geodir_settings['design_settings'] );



			?>
			<p class="submit">
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRECTORY_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="subtab" id="last_tab" />
			</p>
			</div>
        <?php break;
		case 'permalink_settings' :
		  	geodir_admin_fields( $geodir_settings['permalink_settings'] ); ?>
            <p class="submit">
            <input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes', GEODIRECTORY_TEXTDOMAIN ); ?>" />
            <input type="hidden" name="subtab" id="last_tab" />
            </p>
            </div>	
		<?php break;	
		case 'notifications_settings' :
			geodir_admin_fields( $geodir_settings['notifications_settings'] ); ?>
			
			<p class="submit">
				
			<input name="save" class="button-primary" type="submit" value="<?php _e( 'Save changes',GEODIRECTORY_TEXTDOMAIN ); ?>" />
			<input type="hidden" name="subtab" id="last_tab" />
			</p>
			</div>
			
		<?php break;
		case 'default_location_settings' :
		?>
						<div class="inner_content_tab_main">
            <div class="gd-content-heading" >
            <?php global $wpdb;
            
						
						$location_result = geodir_get_default_location();
						
						$prefix = '';
						
						
						$lat = isset($location_result->city_latitude) ? $location_result->city_latitude : '';
						$lng = isset($location_result->city_longitude) ? $location_result->city_longitude : '';
						$city = isset($location_result->city) ? $location_result->city : '';
						$region = isset($location_result->region) ? $location_result->region : '';
						$country = isset($location_result->country) ? $location_result->country : '';
						

						$map_title = __("Set Address On Map" , GEODIRECTORY_TEXTDOMAIN) ;
						
						?>
            
                <h3><?php _e('Set Default Location',GEODIRECTORY_TEXTDOMAIN);?></h3>
                
                <input type="hidden" name="add_location" value="location">
                    
                <input type="hidden" name="update_city" value="<?php if(isset($location_result->location_id)){ echo $location_result->location_id;} ?>">
               
							 <input type="hidden" name="address" id="<?php echo $prefix;?>address" value="">
								
                <table class="form-table default_location_form">
                    <tbody>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('City',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild required">
                               <input class="require" type="text"  size="80" style="width:440px" id="<?php echo $prefix;?>city" name="city" value="<?php if(isset($location_result->city)){ echo $location_result->city;} ?>" />
                               <div class="gd-location_message_error"> <?php _e('This field is required.' , GEODIRECTORY_TEXTDOMAIN); ?></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('Region',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                             <div class="gtd-formfeild required">
                               <input class="require" type="text"  size="80" style="width:440px" id="<?php echo $prefix;?>region" name="region" value="<?php if(isset($location_result->region)){ echo $location_result->region;} ?>" />
                               <div class="gd-location_message_error"> <?php _e('This field is required.' , GEODIRECTORY_TEXTDOMAIN); ?></div>
                            </div>
                            <span class="description"></span>        
                            </td>
                        </tr>
                        <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('Country',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required" style="padding-top:10px;">
                                  <?php 
																	
																	$country_result = isset($location_result->country) ? $location_result->country : '';
																	?>
                                                                   <select id="<?php echo $prefix ?>country" class="chosen_select"data-location_type="country" name="<?php echo $prefix ?>country"  data-placeholder="<?php _e('Choose a country.', GEODIRECTORY_TEXTDOMAIN) ;?>" data-addsearchtermonnorecord="1" data-ajaxchosen="0" data-autoredirect="0" data-showeverywhere="0" >
			<?php geodir_get_country_dl($country,$prefix); ?>
			</select>								
																	<div class="gd-location_message_error"><?php _e('This field is required.' , GEODIRECTORY_TEXTDOMAIN); ?></div>
																	
                                </div>
																
																
                            <span class="description"></span>        
                            </td>
                        </tr>
                         <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('Set Location on Map',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                                <?php 
																
																include( geodir_plugin_path() . "/geodirectory-functions/map-functions/map_on_add_listing_page.php");?>   
                            </td>
                        </tr>
                         <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('City Latitude',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                                 <div class="gtd-formfeild required" style="padding-top:10px;">
                                   <input type="text"  class="require" size="80" style="width:440px" id="<?php echo $prefix;?>latitude" name="latitude" value="<?php if(isset($location_result->city_latitude)){ echo $location_result->city_latitude;} ?>" />
                                   <div class="gd-location_message_error"><?php _e('This field is required.' , GEODIRECTORY_TEXTDOMAIN); ?></div>
                                </div>
                            <span class="description"></span>        
                            </td>
                        </tr>
                         <tr valign="top" class="single_select_page">
                            <th class="titledesc" scope="row"><?php _e('City Longitude',GEODIRECTORY_TEXTDOMAIN);?></th>
                            <td class="forminp">
                                <div class="gtd-formfeild required" style="padding-top:10px;">
                                   <input type="text" class="require"  size="80" style="width:440px" id="<?php echo $prefix;?>longitude" name="longitude" value="<?php if(isset($location_result->city_longitude)){ echo $location_result->city_longitude;} ?>" />
                                   <div class="gd-location_message_error"><?php _e('This field is required.' , GEODIRECTORY_TEXTDOMAIN); ?></div>
                                </div>
                            <span class="description"></span>        
                            </td>
                        </tr>
                        <?php if(isset($location_result->location_id) && $location_result->location_id >= 0){ ?>
                            <tr valign="top" class="single_select_page">
                                <th class="titledesc" scope="row"><?php _e('Action For Listing',GEODIRECTORY_TEXTDOMAIN);?></th>
                                <td class="forminp">
                                    <div class="gtd-formfeild" style="padding-top:10px;">
                                       <input style="display:none;" type="radio" name="listing_action" checked="checked" value="delete" /> 
                                      <label><?php _e('Post will be updated if both city and map marker position has been changed.',GEODIRECTORY_TEXTDOMAIN);?></label> 
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>    
                    </tbody>
                </table>
                
                <p class="submit">
                      <input type="hidden"  name="is_default" value="1" /> 
                      <input id="location_save" type="submit" value="Save changes" class="button-primary" name="save">
                </p>
            
            </div>
						</div>
		<?php break;
		case $listing_type.'_fields_settings' :
				
				geodir_custom_post_type_form();
				
			break;
		case 'tools_settings' :
			geodir_diagnostic_tools_setting_page();
			break;
			
		
	}// end of switch
}


function geodir_custom_post_type_form()
{
	$listing_type	= ($_REQUEST['listing_type'] != '') ? $_REQUEST['listing_type'] : 'gd_place';
	
	$sub_tab	= ($_REQUEST['subtab'] != '') ? $_REQUEST['subtab'] : '';
	
	
				?>
				
				<div class="gd-content-heading">
				<h3><?php echo apply_filters('geodir_custom_fields_panel_head' , '' ,$sub_tab , $listing_type )  ;?></h3>
				</div>
				<div id="container_general" class="clearfix">
					<div class="general-form-builder-frame">
						
						<div class="side-sortables" id="geodir-available-fields"> 
							<h3 class="hndle"><span><?php echo apply_filters('geodir_cf_panel_available_fields_head' , '' ,$sub_tab , $listing_type )  ;?>
							</span></h3>
							<p><?php echo apply_filters('geodir_cf_panel_available_fields_note' , '' ,$sub_tab , $listing_type )  ;?></p>	
							<div class="inside">
								
								<div id="gt-form-builder-tab" class="gt-tabs-panel">
									
									<?php do_action('geodir_manage_available_fields', $sub_tab); ?>
									
									<div style="clear:both"></div>
								</div>
								
							</div>
						</div>  <!--side-sortables -->
						
						
						<div class="side-sortables" id="geodir-selected-fields"> 
							<h3 class="hndle"><span><?php echo apply_filters('geodir_cf_panel_selected_fields_head' , '' ,$sub_tab , $listing_type )  ;?></span></h3>
							<p><?php echo apply_filters('geodir_cf_panel_selected_fields_note' , '' ,$sub_tab , $listing_type )  ;?></p>	
							<div class="inside">
								
								<div id="gt-form-builder-tab" class="gt-tabs-panel">
									<div class="field_row_main">
									<?php do_action('geodir_manage_selected_fields', $sub_tab); ?>
									</div>
									<div style="clear:both"></div>
								</div>
								
							</div>
						</div>
						
					</div>  <!--general-form-builder-frame -->
				</div> <!--container_general -->
				
<?php
}

function geodir_diagnostic_tools_setting_page()
{
?>
	<div class="inner_content_tab_main">
            <div class="gd-content-heading" >
           
            
                <h3><?php _e('GD Diagnostic Tool',GEODIRECTORY_TEXTDOMAIN);?></h3>
                
                 <table class="form-table">
                    <tbody>
                        <tr valign="top" >
                           
                            <td class="forminp"><?php _e('Geodirectory default pages diagnosis',GEODIRECTORY_TEXTDOMAIN);?>
                            <input type="button" value="<?php _e('Run',GEODIRECTORY_TEXTDOMAIN);?>" class="geodir_diagnosis_button" data-diagnose="default_pages" />
                            <div class="geodir_diagnostic_result"></div>        
                            </td>
                            
                        </tr>
                        <?php do_action('geodir_diagnostic_tool');?>
                        
                    </tbody>
                </table>
                
          	</div>
	</div>
<?php
}
?>

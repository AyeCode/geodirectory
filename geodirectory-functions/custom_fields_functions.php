<?php
global $wpdb,$table_prefix;

if (!function_exists('geodir_column_exist')) {
function geodir_column_exist($db, $column){
  global $wpdb;
    $exists = false;
    $columns = $wpdb->get_col("show columns from $db");
    foreach($columns as $c){
		if($c == $column){
        	$exists = true;
            break;
        }
    }      
	return $exists;	
}}

if (!function_exists('geodir_add_column_if_not_exist')) {
function geodir_add_column_if_not_exist($db, $column, $column_attr = "VARCHAR( 255 ) NOT NULL" ){
  global $wpdb;
      
    if( !geodir_column_exist($db, $column) ){
				if(!empty($db) && !empty($column))
        	$wpdb->query("ALTER TABLE `$db` ADD `$column`  $column_attr");
    }
}}

function geodir_post_custom_fields($package_id = '',$default = 'all',$post_type = 'gd_place', $fields_location='none')
{
	global $wpdb,$geodir_post_custom_fields_cache;
	
	$cache_stored = $post_type.'_'.$package_id.'_'.$default.'_'.$fields_location;
	
	if(array_key_exists($cache_stored,$geodir_post_custom_fields_cache)){return $geodir_post_custom_fields_cache[$cache_stored];}
	
	$default_query = '';
	
	if($default == 'default')
		$default_query = " and is_default IN ('1') ";	
	elseif($default == 'custom')
		$default_query = " and is_default = '0' ";	
	
	if($fields_location == 'detail'){
		 $default_query =  " and show_on_detail='1' ";
	}elseif($fields_location == 'listing'){
		$default_query =  " and show_on_listing='1' ";
	}
	
	$post_meta_info =	$wpdb->get_results(
											$wpdb->prepare(
												"select * from ".GEODIR_CUSTOM_FIELDS_TABLE." where is_active = '1' and post_type = %s {$default_query} order by sort_order asc,admin_title asc",
												array($post_type)
											)
										);
	
	
	$return_arr = array();
	if($post_meta_info){
		
		foreach($post_meta_info as $post_meta_info_obj){	
			
			$custom_fields = array(
					"name"		=> $post_meta_info_obj->htmlvar_name,
					"label" 	=> $post_meta_info_obj->clabels,
					"default" 	=> $post_meta_info_obj->default_value,
					"type" 		=> $post_meta_info_obj->field_type,
					"desc"      => $post_meta_info_obj->admin_desc );
			
			if($post_meta_info_obj->field_type){
				$options = explode(',',$post_meta_info_obj->option_values);
				$custom_fields["options"]=$options;
			}
			
			foreach($post_meta_info_obj as $key => $val)
			{ $custom_fields[$key] = $val;	}
		
			$pricearr =array(); 
			$pricearr = explode(',',$post_meta_info_obj->packages);  
			
			if($package_id !='' && in_array($package_id, $pricearr))
			{ $return_arr[$post_meta_info_obj->sort_order] = $custom_fields; }
			elseif($package_id =='')
			{ $return_arr[$post_meta_info_obj->sort_order] = $custom_fields; }
		}
	}
	$geodir_post_custom_fields_cache[$cache_stored] = $return_arr;
	
	if(has_filter('geodir_filter_geodir_post_custom_fields')){
	 	$return_arr = apply_filters('geodir_filter_geodir_post_custom_fields', $return_arr, $package_id, $post_type, $fields_location);
	}
	
	return $return_arr;
}

if (!function_exists('geodir_custom_field_adminhtml')) {
function geodir_custom_field_adminhtml($field_type , $result_str, $field_ins_upd = '', $default = false)
{
	global $wpdb;
	$cf = $result_str;
	if(!is_object($cf))
	{
		
		$field_info =	$wpdb->get_row($wpdb->prepare("select * from ".GEODIR_CUSTOM_FIELDS_TABLE." where id= %d",array($cf)));
		
	}
	else
	{
		$field_info = $cf;
		$result_str = $cf->id;
	}
	include('custom_field_html.php'); 
	
}
}

if (!function_exists('geodir_custom_field_delete')) {
function geodir_custom_field_delete( $field_id = '' ){
	
	global $wpdb, $plugin_prefix;
	if($field_id != ''){
		$cf = trim($field_id, '_');
		
		if($field = $wpdb->get_row($wpdb->prepare("select htmlvar_name,post_type,field_type from ".GEODIR_CUSTOM_FIELDS_TABLE." where id= %d", array($cf)))){
			
			
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_CUSTOM_FIELDS_TABLE." where id= %d ",array($cf)));
			
			$post_type = $field->post_type;
			
			do_action('geodir_after_custom_field_deleted', $cf, $field->htmlvar_name, $post_type);
			
			if($field->field_type == 'address'){
			
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_address`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_city`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_region`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_country`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_zip`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_latitude`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_longitude`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_mapview`");
			$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."_mapzoom`");
				
			}else{
				
				if($field->field_type != 'fieldset'){
					
					$wpdb->query("ALTER TABLE ".$plugin_prefix.$post_type."_detail DROP `".$field->htmlvar_name."`");
				
				}
			}
			
			return $field_id;
			
		}else
			return 0;	
	}else
		return 0;	
			
}
}

if (!function_exists('geodir_custom_field_save')) {
function geodir_custom_field_save( $request_field = array() , $default = false ){
	
	global $wpdb, $plugin_prefix;
	
	$old_html_variable = '';
	
	$data_type = trim($request_field['data_type']);
	
	$result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';
	
	geodir_add_column_if_not_exist(GEODIR_CUSTOM_FIELDS_TABLE, 'cat_sort', 'text NOT NULL');
	geodir_add_column_if_not_exist(GEODIR_CUSTOM_FIELDS_TABLE, 'cat_filter', 'text NOT NULL');
	// add column to store decimal point
	geodir_add_column_if_not_exist( GEODIR_CUSTOM_FIELDS_TABLE, 'decimal_point', 'VARCHAR( 10 ) NOT NULL');
	
	$cf = trim($result_str, '_');
	
	
	/*-------- check dublicate validation --------*/
	
	$cehhtmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
	$post_type = $request_field['listing_type'];
	
	if($request_field['field_type'] != 'address' && $request_field['field_type'] != 'taxonomy' && $request_field['field_type'] != 'fieldset'){
			$cehhtmlvar_name = 'geodir_'.$cehhtmlvar_name;
	}
	
	$check_html_variable = 	$wpdb->get_var(
														$wpdb->prepare(
																"select htmlvar_name from ".GEODIR_CUSTOM_FIELDS_TABLE." where id <> %d and htmlvar_name = %s and post_type = %s ",
																array($cf, $cehhtmlvar_name, $post_type)		
														)
												);
	
	
	
	if(!$check_html_variable || $request_field['field_type'] == 'fieldset'){
		
		if($cf != ''){
			
			$post_meta_info =	$wpdb->get_row(
													$wpdb->prepare(
														"select * from ".GEODIR_CUSTOM_FIELDS_TABLE." where id = %d",
														array($cf)
													)
												);
			
		}
		
		if(!empty($post_meta_info)){
			$post_val = $post_meta_info;
			$old_html_variable = $post_val->htmlvar_name;
			
		}
		/*else{
		$post_val->sort_order = $wpdb->get_var("select max(sort_order)+1 from  ".GEODIR_CUSTOM_FIELDS_TABLE);
		}*/
	
		
		if($post_type == '') $post_type = 'gd_place';
		
		
		$detail_table = $plugin_prefix . $post_type . '_detail' ;
		
		$admin_title = $request_field['admin_title'];
		$site_title = $request_field['site_title'];
		$data_type = $request_field['data_type'];
		$field_type = $request_field['field_type'];
		$htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
		$admin_desc = $request_field['admin_desc'];
		$clabels = $request_field['clabels'];
		$default_value = isset($request_field['default_value']) ? $request_field['default_value'] : '';
		$sort_order = isset($request_field['sort_order']) ? $request_field['sort_order'] : '';
		$is_active = isset($request_field['is_active']) ? $request_field['is_active'] : '';
		$is_required = isset($request_field['is_required']) ? $request_field['is_required'] : '';
		$required_msg = isset($request_field['required_msg']) ? $request_field['required_msg'] : '';
		$css_class = isset($request_field['css_class']) ? $request_field['css_class'] : '';
		$field_icon = isset($request_field['field_icon']) ? $request_field['field_icon'] : '';
		$show_on_listing = isset($request_field['show_on_listing']) ? $request_field['show_on_listing'] : '';
		$show_on_detail = isset($request_field['show_on_detail']) ? $request_field['show_on_detail'] : '';
		$show_as_tab = isset($request_field['show_as_tab']) ? $request_field['show_as_tab'] : '';
		$decimal_point = isset( $request_field['decimal_point'] ) ? trim( $request_field['decimal_point'] ) : ''; // decimal point for DECIMAL data type
		$decimal_point = $decimal_point > 0 ? ( $decimal_point > 10 ? 10 : $decimal_point ) : '';
		$for_admin_use = isset( $request_field['for_admin_use'] ) ? $request_field['for_admin_use'] : '';
		
		if($field_type != 'address' && $field_type != 'taxonomy' && $field_type != 'fieldset'){
			$htmlvar_name = 'geodir_'.$htmlvar_name;
		}
		
		$option_values = '';
		if(isset($request_field['option_values']))
			$option_values = $request_field['option_values'];
		
		$cat_sort = '';
		if(isset($request_field['cat_sort']) && !empty($request_field['cat_sort']))
			$cat_sort = implode(",",$request_field['cat_sort']);
		
		$cat_filter = '';
		if(isset($request_field['cat_filter']) && !empty($request_field['cat_filter']))
			$cat_filter = implode(",",$request_field['cat_filter']);
		
		if(isset($request_field['show_on_pkg']) && !empty($request_field['show_on_pkg']))
			$price_pkg = implode(",",$request_field['show_on_pkg']); 
		else{
			$package_info = array() ;
			
			$package_info = geodir_post_package_info($package_info , '', $post_type);
			$price_pkg =$package_info->pid; 
		}
		
		
		if(isset($request_field['extra']) && !empty($request_field['extra']))
			$extra_fields = $request_field['extra'];
		
		if(isset($request_field['is_default']) && $request_field['is_default'] != '')
			$is_default = $request_field['is_default'];
		else
			$is_default = '0';	
			
		if(isset($request_field['is_admin']) && $request_field['is_admin'] != '')
			$is_admin = $request_field['is_admin'];
		else
			$is_admin = '0';	
			
		
		if($is_active == '') $is_active = 1;
		if($is_required == '') $is_required = 0;
		
		
		if($sort_order == '')
		{
			
			$last_order =	$wpdb->get_var("SELECT MAX(sort_order) as last_order FROM ".GEODIR_CUSTOM_FIELDS_TABLE);
			
			$sort_order = (int)$last_order + 1;
		}
		
		$default_value_add = '';
	
		if(!empty($post_meta_info))
		{
			switch($field_type):
			
				case 'address':
					
					if($htmlvar_name != '') { $prefix = $htmlvar_name.'_'; }
					$old_prefix = $old_html_variable.'_';
					
					
					$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."address` `".$prefix."address` VARCHAR( 254 ) NULL";
					
					if($default_value != '')
					{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
				
					$wpdb->query($meta_field_add);
					
					if($extra_fields != ''){					
						
						if(isset($extra_fields['show_city']) && $extra_fields['show_city']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."city'");
							if($is_column){
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."city` `".$prefix."city` VARCHAR( 30 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								$wpdb->query($meta_field_add);
							}else{
							
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."city` VARCHAR( 30 ) NULL";
								$meta_field_add = "VARCHAR( 30 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								geodir_add_column_if_not_exist( $detail_table, $prefix."city", $meta_field_add );
							
							}
							
					
						}
						
						
						if(isset($extra_fields['show_region']) && $extra_fields['show_region']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."region'");
							
							if($is_column){
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."region` `".$prefix."region` VARCHAR( 30 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								
								$wpdb->query($meta_field_add);
							}else
							{
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."region` VARCHAR( 30 ) NULL";
								$meta_field_add = "VARCHAR( 30 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								
								geodir_add_column_if_not_exist( $detail_table, $prefix."region", $meta_field_add );
							}
							
						}
						if(isset($extra_fields['show_country']) && $extra_fields['show_country']){
							
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."country'");
							
							if($is_column){
								
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."country` `".$prefix."country` VARCHAR( 30 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								
								$wpdb->query($meta_field_add);
							}else{
								
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."country` VARCHAR( 30 ) NULL";
							
								$meta_field_add = "VARCHAR( 30 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								
								geodir_add_column_if_not_exist( $detail_table, $prefix."country", $meta_field_add );
							
							}
							
						}
						if(isset($extra_fields['show_zip']) && $extra_fields['show_zip']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."zip'");
							
							if($is_column){
								
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."zip` `".$prefix."zip` VARCHAR( 15 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
								
								$wpdb->query($meta_field_add);
							}else{
								
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."zip` VARCHAR( 15 ) NULL";
								$meta_field_add = "VARCHAR( 15 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								geodir_add_column_if_not_exist( $detail_table, $prefix."zip", $meta_field_add );
							
							}
							
						}
						if(isset($extra_fields['show_map']) && $extra_fields['show_map']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."latitude'");
							if($is_column){
							
							$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."latitude` `".$prefix."latitude` VARCHAR( 20 ) NULL";
							
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							$wpdb->query($meta_field_add);
							}else{
								
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."latitude` VARCHAR( 20 ) NULL";
								$meta_field_add = "VARCHAR( 20 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								geodir_add_column_if_not_exist( $detail_table, $prefix."latitude",$meta_field_add );
								
							}
							
							
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."longitude'");
							
							if($is_column){
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."longitude` `".$prefix."longitude` VARCHAR( 20 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								$wpdb->query($meta_field_add);
							}else{
								
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."longitude` VARCHAR( 20 ) NULL";
								$meta_field_add = "VARCHAR( 20 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								geodir_add_column_if_not_exist( $detail_table,$prefix."longitude",$meta_field_add );
							}
							
						}
						if(isset($extra_fields['show_mapview']) && $extra_fields['show_mapview']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."mapview'");
							
							if($is_column){
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."mapview` `".$prefix."mapview` VARCHAR( 15 ) NULL";
								
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								$wpdb->query($meta_field_add);
							}else{
								
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."mapview` VARCHAR( 15 ) NULL";
							
								$meta_field_add = "VARCHAR( 15 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								geodir_add_column_if_not_exist( $detail_table,$prefix."mapview",$meta_field_add );
							}
							
							
						}
						if(isset($extra_fields['show_mapzoom']) && $extra_fields['show_mapzoom']){
						
							$is_column=$wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."mapzoom'");
							if($is_column){
							$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."mapzoom` `".$prefix."mapzoom` VARCHAR( 3 ) NULL";
							
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							$wpdb->query($meta_field_add);
							
							}else{
							
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."mapzoom` VARCHAR( 3 ) NULL";
							
								$meta_field_add = "VARCHAR( 3 ) NULL";	
								if($default_value != '')
								{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
								geodir_add_column_if_not_exist( $detail_table,$prefix."mapzoom",$meta_field_add );
							}
							
						}
						// show lat lng
						if (isset($extra_fields['show_latlng']) && $extra_fields['show_latlng']) {
							$is_column = $wpdb->get_var("SHOW COLUMNS FROM ".$detail_table." where field='".$old_prefix."latlng'");
							
							if ($is_column) {
								$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_prefix."latlng` `".$prefix."latlng` VARCHAR( 3 ) NULL";
								$meta_field_add .= " DEFAULT '1'";
						
								$wpdb->query($meta_field_add);
							} else {
								$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."latlng` VARCHAR( 3 ) NULL";
							
								$meta_field_add = "VARCHAR( 3 ) NULL";	
								$meta_field_add .= " DEFAULT '1'";
							
								geodir_add_column_if_not_exist( $detail_table,$prefix."latlng",$meta_field_add );
							}
							
						}
					}// end extra
					
					break;
				
				case 'checkbox':
				case 'multiselect':
				case 'taxonomy':
					
					$meta_field_add = "ALTER TABLE ".$detail_table." CHANGE `".$old_html_variable."` `".$htmlvar_name."`VARCHAR( 500 ) NULL";
						
						if($default_value != '')
						{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
						$wpdb->query($meta_field_add);
						
						if(isset($request_field['cat_display_type']))
							$extra_fields = $request_field['cat_display_type'];
							
						if(isset($request_field['multi_display_type']))
							$extra_fields = $request_field['multi_display_type'];
						
							
					break;	
					
				case 'textarea':
				case 'html':
				
						$wpdb->query("ALTER TABLE ".$detail_table." CHANGE `".$old_html_variable."` `".$htmlvar_name."` TEXT NULL");
						if(isset($request_field['advanced_editor']))
								$extra_fields = $request_field['advanced_editor'];
							
					break;
				
				case 'fieldset': 
					// Nothig happend for fieldset
				break;
				
				default:
					if ( $data_type != 'VARCHAR' && $data_type != '' ) {
						if ( $data_type == 'FLOAT' && $decimal_point > 0 ) {
							$default_value_add = "ALTER TABLE " . $detail_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` DECIMAL(11, " . (int)$decimal_point . ") NULL";
						} else {
							$default_value_add = "ALTER TABLE " . $detail_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` " . $data_type . " NULL";
						}
						
						if ( is_numeric( $default_value ) && $default_value != '' ) {
							$default_value_add .= " DEFAULT '" . $default_value . "'";
						}
					} else {
						$default_value_add = "ALTER TABLE " . $detail_table . " CHANGE `" . $old_html_variable . "` `" . $htmlvar_name . "` VARCHAR( 254 ) NULL";
						if ( $default_value != '' ) {
							$default_value_add .= " DEFAULT '" . $default_value . "'";
						}
					}
					
					$wpdb->query( $default_value_add );
				break;
			endswitch;
			
			$extra_field_query = '';
			if(!empty($extra_fields)){ $extra_field_query = serialize( $extra_fields ) ;  }
			
			$decimal_point = $field_type == 'text' && $data_type == 'FLOAT' ? $decimal_point : '';

			$wpdb->query(
				
				$wpdb->prepare(
					
					"update ".GEODIR_CUSTOM_FIELDS_TABLE." set 
					post_type = %s,
					admin_title = %s,
					site_title = %s,
					field_type = %s,
					htmlvar_name = %s,
					admin_desc = %s,
					clabels = %s,
					default_value = %s,
					sort_order = %s,
					is_active = %s,
					is_default  = %s,
					is_required = %s,
					required_msg = %s,
					css_class = %s,
					field_icon = %s,
					field_icon = %s,
					show_on_listing = %s,
					show_on_detail = %s, 
					show_as_tab = %s, 
					option_values = %s, 
					packages = %s, 
					cat_sort = %d, 
					cat_filter = %s, 
					data_type = %s,
					extra_fields = %s,
					decimal_point = %s,
					for_admin_use = %s  
					where id = %d",
					
					array($post_type,$admin_title,$site_title,$field_type,$htmlvar_name,$admin_desc,$clabels,$default_value,$sort_order,$is_active,$is_default,$is_required,$required_msg,$css_class,$field_icon,$field_icon,$show_on_listing,$show_on_detail,$show_as_tab,$option_values,$price_pkg,$cat_sort,$cat_filter,$data_type,$extra_field_query, $decimal_point, $for_admin_use, $cf)
				)
				
			);
			
			$lastid = trim($cf);
			
			
			$wpdb->query(
				$wpdb->prepare(
					"update ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." set 
					 	site_title=%s
					where post_type = %s and htmlvar_name = %s",
						array($site_title, $post_type, $htmlvar_name)
					)
				);
				
			
			if($cat_sort == '')
				$wpdb->query($wpdb->prepare("delete from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where post_type = %s and htmlvar_name = %s", array($post_type, $htmlvar_name)));
			
			
			do_action('geodir_after_custom_fields_updated', $lastid);
			
		}else{
		
			switch($field_type):
				
				case 'address':
					
					$data_type = '';
					
					if($htmlvar_name != '') { $prefix = $htmlvar_name.'_'; }
					$old_prefix = $old_html_variable;
										
					//$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."address` VARCHAR( 254 ) NULL";
					
					$meta_field_add = "VARCHAR( 254 ) NULL";						
					if($default_value != '')
					{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
				
					geodir_add_column_if_not_exist( $detail_table, $prefix."address", $meta_field_add );
					//$wpdb->query($meta_field_add);
					
					
					if(!empty($extra_fields)){
						
						if(isset($extra_fields['show_city']) && $extra_fields['show_city']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."city` VARCHAR( 30 ) NULL";
							$meta_field_add = "VARCHAR( 30 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table, $prefix."city", $meta_field_add );
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_region']) && $extra_fields['show_region']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."region` VARCHAR( 30 ) NULL";
							$meta_field_add = "VARCHAR( 30 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table, $prefix."region", $meta_field_add );
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_country']) && $extra_fields['show_country']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."country` VARCHAR( 30 ) NULL";
							
							$meta_field_add = "VARCHAR( 30 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
							
							geodir_add_column_if_not_exist( $detail_table, $prefix."country", $meta_field_add );
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_zip']) && $extra_fields['show_zip']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."zip` VARCHAR( 15 ) NULL";
							$meta_field_add = "VARCHAR( 15 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table, $prefix."zip", $meta_field_add );
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_map']) && $extra_fields['show_map']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."latitude` VARCHAR( 20 ) NULL";
							$meta_field_add = "VARCHAR( 20 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table, $prefix."latitude",$meta_field_add );
							//$wpdb->query($meta_field_add);
							
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."longitude` VARCHAR( 20 ) NULL";
							
							$meta_field_add = "VARCHAR( 20 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table,$prefix."longitude",$meta_field_add );
						
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_mapview']) && $extra_fields['show_mapview']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."mapview` VARCHAR( 15 ) NULL";
							
							$meta_field_add = "VARCHAR( 15 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table,$prefix."mapview",$meta_field_add );
						
							//$wpdb->query($meta_field_add);
						}
						if(isset($extra_fields['show_mapzoom']) && $extra_fields['show_mapzoom']){
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."mapzoom` VARCHAR( 3 ) NULL";
							
							$meta_field_add = "VARCHAR( 3 ) NULL";	
							if($default_value != '')
							{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
						
							geodir_add_column_if_not_exist( $detail_table,$prefix."mapzoom",$meta_field_add );
						
							//$wpdb->query($meta_field_add);
						}
						// show lat lng
						if (isset($extra_fields['show_latlng']) && $extra_fields['show_latlng']) {
							$meta_field_add = "ALTER TABLE ".$detail_table." ADD `".$prefix."latlng` VARCHAR( 3 ) NULL";
							
							$meta_field_add = "VARCHAR( 3 ) NULL";
							$meta_field_add .= " DEFAULT '1'";
							
							geodir_add_column_if_not_exist( $detail_table,$prefix."latlng",$meta_field_add );
							//$wpdb->query($meta_field_add);
						}
					}
					
				break;
				
				case 'checkbox':
				case 'multiselect':
					
					$data_type = 'VARCHAR';
					
					$default_value_add = " `".$htmlvar_name."` ".$data_type."( 500 ) NULL  ";
					
					$meta_field_add = $data_type."( 500 ) NULL ";	
					if($default_value != '')
					{ $meta_field_add .= " DEFAULT '".$default_value."'"; }
					
					geodir_add_column_if_not_exist( $detail_table,$htmlvar_name,$meta_field_add );
					
					//$wpdb->query("ALTER TABLE ".$detail_table." ADD".$default_value_add);
					
				break;
				case 'textarea':
				case 'html':
					
					$data_type = 'TEXT';
					
					$default_value_add = " `".$htmlvar_name."` ".$data_type." NULL ";
					
					$meta_field_add = $data_type." NULL ";	
					/*if($default_value != '')
					{ $meta_field_add .= " DEFAULT '".$default_value."'"; }*/
						
					geodir_add_column_if_not_exist( $detail_table,$htmlvar_name,$meta_field_add );
					
				break;
				
				case 'datepicker':
					
					$data_type = 'DATE';
					
					$default_value_add = " `".$htmlvar_name."` ".$data_type." NULL ";
					
					$meta_field_add = $data_type." NULL ";
						
					geodir_add_column_if_not_exist( $detail_table,$htmlvar_name,$meta_field_add );
					
				break;
				
				case 'time':
					
					$data_type = 'TIME';
					
					$default_value_add = " `".$htmlvar_name."` ".$data_type." NULL ";
					
					$meta_field_add = $data_type." NULL ";
						
					geodir_add_column_if_not_exist( $detail_table,$htmlvar_name,$meta_field_add );
					
				break;
				
				
				default:
				
					if( $data_type != 'VARCHAR' && $data_type != '' ) {
						$meta_field_add = $data_type . " NULL ";	
						
						if ( $data_type == 'FLOAT' && $decimal_point > 0 ) {
							$meta_field_add = "DECIMAL(11, " . (int)$decimal_point . ") NULL ";
						}
					
						if ( is_numeric( $default_value ) && $default_value != '' ) {
							$default_value_add .= " DEFAULT '" . $default_value . "'";
							$meta_field_add .= " DEFAULT '" . $default_value . "'";
						}						
					} else {
						$meta_field_add = " VARCHAR( 254 ) NULL ";	
						
						if ( $default_value != '' ) {
							$default_value_add .= " DEFAULT '" . $default_value . "'";
							$meta_field_add .= " DEFAULT '" . $default_value . "'";
						}
					}
					
					geodir_add_column_if_not_exist( $detail_table, $htmlvar_name, $meta_field_add );
				break;	
			endswitch;	
			
			$extra_field_query = '';
			if(!empty($extra_fields)){ $extra_field_query = serialize($extra_fields);  }
						
			$decimal_point = $field_type == 'text' && $data_type == 'FLOAT' ? $decimal_point : '';
			
			$wpdb->query(
							
				$wpdb->prepare( 
								
					"insert into ".GEODIR_CUSTOM_FIELDS_TABLE." set 
					post_type = %s,
					admin_title = %s,
					site_title = %s,
					field_type = %s,
					htmlvar_name = %s,
					admin_desc = %s,
					clabels = %s,
					default_value = %s,
					sort_order = %d,
					is_active = %s,
					is_default  = %s,
					is_admin = %s,
					is_required = %s,
					required_msg = %s,
					css_class = %s,
					field_icon = %s,
					show_on_listing = %s,
					show_on_detail = %s, 
					show_as_tab = %s, 
					option_values = %s, 
					packages = %s, 
					cat_sort = %s, 
					cat_filter = %s, 
					data_type = %s,
					extra_fields = %s,
					decimal_point = %s,
					for_admin_use = %s ",
					
					array($post_type,$admin_title,$site_title,$field_type,$htmlvar_name,$admin_desc,$clabels,$default_value,$sort_order,$is_active,$is_default,$is_admin,$is_required,$required_msg,$css_class,$field_icon,$show_on_listing,$show_on_detail,$show_as_tab,$option_values,$price_pkg,$cat_sort,$cat_filter,$data_type,$extra_field_query, $decimal_point, $for_admin_use )
					
				)
			
			);
			
			$lastid = $wpdb->insert_id;
			
			$lastid = trim($lastid);
			
		}
		
		return (int)$lastid;
		
	
	}else{
		return 'HTML Variable Name should be a unique name';
	}

}
}

function godir_set_field_order($field_ids = array()){
	
	global $wpdb;	
	
	$count = 0;
	if( !empty( $field_ids ) ):
		foreach ($field_ids as $id) {
		
			$cf = trim($id, '_');
		
		$post_meta_info = $wpdb->query(
														$wpdb->prepare( 
															"update ".GEODIR_CUSTOM_FIELDS_TABLE." set 
															sort_order=%d 
															where id= %d",
															array($count, $cf)
														)
												);
			$count ++;	
		}
		
		return $field_ids;
	else:
		return false;
	endif;
}


function geodir_get_custom_fields_html($package_id = '', $default = 'custom',$post_type = 'gd_place'){

	global $is_default, $mapzoom;
	
	$show_editors = array();
	$listing_type = $post_type;
	
	$custom_fields = geodir_post_custom_fields($package_id,$default,$post_type); 

	foreach($custom_fields as $key=>$val)
	{
		$name = $val['name'];
		$site_title = $val['site_title'];
		$type = $val['type'];
		$admin_desc = $val['desc'];
		$option_values = $val['option_values'];
		$is_required = $val['is_required'];
		$is_default =  $val['is_default'];
		$is_admin =  $val['is_admin'];
		$required_msg = $val['required_msg'];
		$extra_fields = unserialize($val['extra_fields']);
		$value='';
		
		/* field available to site admin only for edit */
		$for_admin_use = isset( $val['for_admin_use'] ) && (int)$val['for_admin_use'] == 1 ? true : false;
		if ( $for_admin_use && !is_super_admin() ) {
			continue;
		}
		
		if(is_admin()){ 
			
			global $post; 
			
			if(isset($_REQUEST['post']))
				$_REQUEST['pid'] = $_REQUEST['post'];
		}
		
		if( isset($_REQUEST['backandedit']) && $_REQUEST['backandedit'] && isset($_SESSION['listing']) ){ 
			$post = unserialize($_SESSION['listing']);
			$value = isset($post[$name]) ? $post[$name] : '';
		}elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
			$value = geodir_get_post_meta($_REQUEST['pid'], $name,true);
		}else{
			if($value == '' ){ $value= $val['default']; }
		}
		
		do_action('geodir_before_custom_form_field_'.$name, $listing_type, $package_id, $val);
			 
		if($type=='fieldset'){	
		
			?><h5><?php echo $site_title;?>
				<?php if($admin_desc != '')echo '<small>( '.$admin_desc.' )</small>';?>
			</h5><?php
		
		}elseif($type=='address'){	
				
				$prefix = $name.'_'; 
				
				($site_title != '') ? $address_title = $site_title : $address_title = ucwords($prefix . ' address');
				($extra_fields['zip_lable'] != '') ? $zip_title = $extra_fields['zip_lable'] : $zip_title = ucwords($prefix.' zip/post code ');
				($extra_fields['map_lable'] != '') ? $map_title = $extra_fields['map_lable'] : $map_title = ucwords('set address on map');
				($extra_fields['mapview_lable'] != '') ? $mapview_title = $extra_fields['mapview_lable'] : $mapview_title = ucwords($prefix.' mapview');
				
				$address = '';
				$zip = '';
				$mapview = '';
				$mapzoom = '';
				$lat = '';
				$lng = '';
				
				if(isset($_REQUEST['backandedit']) &&  $_REQUEST['backandedit'] && isset($_SESSION['listing']) ){ 
					
					$post = unserialize($_SESSION['listing']);
					$address = $post[$prefix.'address'];
					$zip = isset($post[$prefix.'zip']) ? $post[$prefix.'zip'] : '';
					$lat = isset($post[$prefix.'latitude']) ? $post[$prefix.'latitude'] : '';
					$lng = isset($post[$prefix.'longitude']) ? $post[$prefix.'longitude'] : '';
					$mapview = isset($post[$prefix.'mapview']) ? $post[$prefix.'mapview'] : '';
					$mapzoom = isset($post[$prefix.'mapzoom']) ? $post[$prefix.'mapzoom'] : '';
					
				}elseif( isset($_REQUEST['pid']) && $_REQUEST['pid']!='' && $post_info = geodir_get_post_info($_REQUEST['pid']) ){ 
					
					$post_info = (array)$post_info;
					
					$address = $post_info[$prefix.'address'];
					$zip = isset($post_info[$prefix.'zip']) ? $post_info[$prefix.'zip'] : '';
					$lat = isset($post_info[$prefix.'latitude']) ? $post_info[$prefix.'latitude'] : '';
					$lng = isset($post_info[$prefix.'longitude']) ? $post_info[$prefix.'longitude'] : '';
					$mapview = isset($post_info[$prefix.'mapview']) ? $post_info[$prefix.'mapview'] : '';
					$mapzoom = isset($post_info[$prefix.'mapzoom']) ? $post_info[$prefix.'mapzoom'] : '';
					
				}
				
				$location = geodir_get_default_location();
				if(empty($city)) $city = isset($location->city) ? $location->city : '';
				if(empty($region)) $region = isset($location->region) ? $location->region : '';
				if(empty($country)) $country = isset($location->country) ? $location->country : '';
				
				$lat_lng_blank = false;
				if(empty($lat) && empty($lng)){$lat_lng_blank = true;}
				
				if(empty($lat)) $lat = isset($location->city_latitude) ? $location->city_latitude : '';
				if(empty($lng)) $lng = isset($location->city_longitude) ? $location->city_longitude : '';
				
				
				
				
				$lat = apply_filters('geodir_default_latitude', $lat, $is_admin);
				$lng = apply_filters('geodir_default_longitude', $lng, $is_admin);
				
				?>
			
				<div id="geodir_<?php echo $prefix.'address';?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
					<label>
						<?php _e($address_title,GEODIRECTORY_TEXTDOMAIN); ?>
						<?php if($is_required) echo '<span>*</span>';?>
					</label>
					<input type="text" field_type="<?php echo $type;?>" name="<?php echo $prefix.'address';?>" id="<?php echo $prefix.'address';?>" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($address)); ?>"  />
					<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
					<?php if($is_required) {?> 
						<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
					<?php } ?>
				</div>
				
							
				<?php
			
				do_action('geodir_address_extra_listing_fields', $val);
						  
				if(isset($extra_fields['show_zip']) && $extra_fields['show_zip']) {?> 
				  
				<div id="geodir_<?php echo $prefix.'zip';?>_row" class="<?php /*if($is_required) echo 'required_field';*/?> geodir_form_row clearfix">
					<label>
						<?php _e($zip_title,GEODIRECTORY_TEXTDOMAIN);?>
						<?php /*if($is_required) echo '<span>*</span>';*/?>
					</label>
					<input type="text" field_type="<?php echo $type;?>" name="<?php echo $prefix.'zip';?>" id="<?php echo $prefix.'zip';?>" class="geodir_textfield autofill" value="<?php echo esc_attr(stripslashes($zip)); ?>"  />
					<?php /*if($is_required) {?>
					<span class="geodir_message_error"><?php echo _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
					<?php }*/ ?>
				</div>
				<?php } ?>
				
				<?php if(isset($extra_fields['show_map']) && $extra_fields['show_map']) {?>  
			   
				<div id="geodir_<?php echo $prefix.'map';?>_row" class="geodir_form_row clearfix"> 
					<?php include( geodir_plugin_path() . "/geodirectory-functions/map-functions/map_on_add_listing_page.php");
					if($lat_lng_blank){$lat='';$lng='';}
					?>
					<span class="geodir_message_note"><?php echo GET_MAP_MSG;?></span>
				</div> 
				<?php 
				/* show lat lng */
				$style_latlng = ((isset($extra_fields['show_latlng']) && $extra_fields['show_latlng']) || is_admin()) ? '' : 'style="display:none"';?>
				<div id="geodir_<?php echo $prefix.'latitude';?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix" <?php echo $style_latlng;?>>
					<label>
						<?php echo PLACE_ADDRESS_LAT;?>
						<?php if($is_required) echo '<span>*</span>';?>
					</label>
					<input type="text" field_type="<?php echo $type;?>" name="<?php echo $prefix.'latitude';?>" id="<?php echo $prefix.'latitude';?>" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($lat)); ?>" size="25"  />
					<span class="geodir_message_note"><?php echo GET_LATITUDE_MSG;?></span>
					<?php if($is_required) {?>
					<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
					<?php } ?>
				 </div>
				 
				 <div id="geodir_<?php echo $prefix.'longitude';?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix" <?php echo $style_latlng;?>>
					<label>
						<?php echo PLACE_ADDRESS_LNG;?>
						<?php if($is_required) echo '<span>*</span>';?>
					</label>
					<input type="text" field_type="<?php echo $type;?>" name="<?php echo $prefix.'longitude';?>" id="<?php echo $prefix.'longitude';?>" class="geodir_textfield" value="<?php echo esc_attr(stripslashes($lng)); ?>" size="25"  />
					<span class="geodir_message_note"><?php echo GET_LOGNGITUDE_MSG;?></span>
					<?php if($is_required) {?>
					<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
					<?php } ?>
				 </div>
				<?php } ?>
				
				<?php if(isset($extra_fields['show_mapview']) && $extra_fields['show_mapview']) {?>  
						<div id="geodir_<?php echo $prefix.'mapview';?>_row" class="geodir_form_row clearfix ">
							<label><?php _e($mapview_title,GEODIRECTORY_TEXTDOMAIN);?></label>
							
						   
							
								<span class="geodir_user_define"><input field_type="<?php echo $type;?>" type="radio" class="gd-checkbox" name="<?php echo $prefix.'mapview';?>" id="<?php echo $prefix.'mapview';?>" <?php if($mapview == 'ROADMAP' || $mapview == '' ){echo 'checked="checked"';}?>  value="ROADMAP" size="25"  /> <?php _e('Default Map',GEODIRECTORY_TEXTDOMAIN);?></span>
								<span class="geodir_user_define"> <input field_type="<?php echo $type;?>" type="radio"  class="gd-checkbox" name="<?php echo $prefix.'mapview';?>" id="map_view1" <?php if($mapview=='SATELLITE'){echo 'checked="checked"';}?> value="SATELLITE" size="25"  /> <?php _e('Satellite Map',GEODIRECTORY_TEXTDOMAIN);?></span>
								
								<span class="geodir_user_define"><input field_type="<?php echo $type;?>" type="radio" class="gd-checkbox"  name="<?php echo $prefix.'mapview';?>" id="map_view2" <?php if($mapview=='HYBRID'){echo 'checked="checked"';}?>  value="HYBRID" size="25"  /> <?php _e('Hybrid Map',GEODIRECTORY_TEXTDOMAIN);?></span>
							
							
						</div>
					<?php }?>
                    
         <?php if(isset($extra_fields['show_mapzoom']) && $extra_fields['show_mapzoom']) {?>  
				<input type="hidden" value="<?php if(isset($mapzoom)){ echo $mapzoom;}?>" name="<?php echo $prefix.'mapzoom';?>" id="<?php echo $prefix.'mapzoom';?>" />
				<?php }?>						
		<?php }
		elseif($type=='text'){?>
		 
		 <div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label>
			<input field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo stripslashes($value);?>" type="text" class="geodir_textfield" />
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		 </div>
		
		<?php }
		elseif($type=='email'){
			if($value== $val['default']){$value='';}?>
		 
			<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
				<label>
					<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
					<?php if($is_required) echo '<span>*</span>';?>
				</label>
				<input field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo stripslashes($value);?>" type="text" class="geodir_textfield" />
				<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
				<?php if($is_required) {?>
				<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
				<?php } ?>
			</div>
		
		<?php }
		elseif($type=='phone'){ 
				if($value== $val['default']){$value='';} ?>
			  
				<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
					<label>
						<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
						<?php if($is_required) echo '<span>*</span>';?>
					</label>
					<input field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo stripslashes($value);?>" type="text" class="geodir_textfield" />
					<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
					<?php if($is_required) {?>
					<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
					<?php } ?>
				</div> 
		
		<?php }
		elseif($type=='url'){ 
			if($value== $val['default']){$value='';}?>
		
			<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
				<label>
					<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
					<?php if($is_required) echo '<span>*</span>';?>
				</label>
				<input field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo stripslashes($value);?>" type="text" class="geodir_textfield" />
				<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
				<?php if($is_required) {?>
				<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
				<?php } ?>
			 </div>
	
		<?php }
		elseif($type=='radio'){ ?>     
			<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
				<label>
					<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
					<?php if($is_required) echo '<span>*</span>';?>
				</label>
				
				<?php if($option_values){ $option_values_arr = explode(',',$option_values);
						
						for($i=0;$i<count($option_values_arr);$i++){ 
							if(strstr($option_values_arr[$i],"/")){
								$radio_attr = explode("/",$option_values_arr[$i]);
								$radio_lable = ucfirst($radio_attr[0]);
								$radio_value = $radio_attr[1];
							}else{
								$radio_lable = ucfirst($option_values_arr[$i]);
								$radio_value = $option_values_arr[$i];
							}
								
						?>
					
						<input name="<?php echo $name;?>" id="<?php echo $name;?>" <?php if($radio_value == $value){ echo 'checked="checked"';}?>  value="<?php echo $radio_value; ?>" class="gd-checkbox" field_type="<?php echo $type;?>" type="radio"  /><?php _e($radio_lable); ?>
						
						<?php	
						} 
					}	
				?>
				<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
				<?php if($is_required) {?>
				<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
				<?php } ?>
			</div>
			
		<?php }
		elseif($type=='checkbox'){	?>     
		
		<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label>
					<?php	if($value != '1'){	$value = '0';}?>
					<input type="hidden" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>" />
			<input  <?php if($value == '1'){ echo 'checked="checked"';}?>  value="1" class="gd-checkbox" field_type="<?php echo $type;?>" type="checkbox" onchange="if(this.checked){jQuery('#<?php echo $name;?>').val('1');} else{ jQuery('#<?php echo $name;?>').val('0');}" /> 
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		</div>
		
		<?php }
		elseif($type=='textarea'){?>
		
		<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label><?php
			
			
			if(is_array($extra_fields) && in_array('1', $extra_fields)){
				
				$editor_settings = array('media_buttons'=>false, 'textarea_rows'=>10);?>
													
				<div class="editor" field_id="<?php echo $name;?>" field_type="editor">
				<?php wp_editor( stripslashes($value), $name, $editor_settings ); ?>
				</div><?php
			
			}else{
				
				?><textarea field_type="<?php echo $type;?>" class="geodir_textarea" name="<?php echo $name;?>" id="<?php echo $name;?>"><?php echo stripslashes($value);?></textarea><?php
			
			}?>
			
			
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		</div>     
		
		<?php }
		elseif($type=='select'){	?>
		<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row geodir_custom_fields clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label>
			<select field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" class="geodir_textfield textfield_x chosen_select" data-placeholder="<?php echo  __('Choose',GEODIRECTORY_TEXTDOMAIN ) .' '.$site_title .'&hellip;' ;?>" option-ajaxchosen="false" >
	
			<?php if($option_values){   $option_values_arr = explode(',',$option_values);
			
					for($i=0;$i<count($option_values_arr);$i++)   {   
					
					if(strstr($option_values_arr[$i],"/")){
							$select_attr = explode("/",$option_values_arr[$i]);
							$select_lable = ucfirst($select_attr[0]);
							$select_value = $select_attr[1];
						}else{
							$select_lable = ucfirst($option_values_arr[$i]);
							$select_value = $option_values_arr[$i];
						}
					
					?>
							<option value="<?php echo $select_value; ?>" <?php if($value==$select_value){ echo 'selected="selected"';}?>><?php echo $select_lable; ?></option>
					<?php }
				}
			?>
			</select>
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		</div>
			
		<?php 
		} else if( $type == 'multiselect' ) { 
			$multi_display = 'select';
			if( !empty( $val['extra_fields'] ) ) {
				$multi_display = unserialize( $val['extra_fields'] );	
			}
			?>
			<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
				<label>
					<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
					<?php if($is_required) echo '<span>*</span>';?>
				</label>
				<input type="hidden" name="gd_field_<?php echo $name;?>" value="1" />
				<?php if ( $multi_display == 'select' ) { ?>
				<div class="geodir_multiselect_list">
					<select field_type="<?php echo $type;?>" name="<?php echo $name;?>[]" id="<?php echo $name;?>" multiple="multiple" class="geodir_textfield textfield_x chosen_select" data-placeholder="<?php _e( 'Select', GEODIRECTORY_TEXTDOMAIN );?>" option-ajaxchosen="false">					
			<?php 
			} else {
				echo '<ul class="gd_multi_choice">';
			}
			
			if ( $option_values ) {
				$option_values_arr = explode( ',', $option_values );
				
				for ( $i=0; $i < count( $option_values_arr ); $i++ ) { 
									
					if ( strstr( $option_values_arr[$i], "/" ) ) {
						$multi_select_attr = explode( "/", $option_values_arr[$i] );
						$multi_select_lable = ucfirst( $multi_select_attr[0] );
						$multi_select_value = $multi_select_attr[1];
					} else {
						$multi_select_lable = ucfirst( $option_values_arr[$i] );
						$multi_select_value = $option_values_arr[$i];
					}
									
					$selected = '';
					$checked = '';
					
					if ( ( !is_array( $value ) && trim( $value ) != '' ) || ( is_array( $value ) && !empty( $value ) ) ) {
						if ( !is_array( $value ) ) {
							$value_array = explode( ',', $value );
						} else {
							$value_array = $value;
						}
						
						if ( is_array( $value_array ) ) {
							if ( in_array( $multi_select_value, $value_array ) ) {
								$selected = 'selected="selected"';
								$checked = 'checked="checked"';
							}
						}
					}
					
					if ( $multi_display == 'select' ) {
					?>
						<option value="<?php echo $multi_select_value; ?>" <?php echo $selected; ?>><?php echo $multi_select_lable; ?></option>
					<?php
					} else {
					?> 
						<li>
							<input name="<?php echo $name;?>[]" <?php echo $checked;?>  value="<?php echo $multi_select_value; ?>" class="gd-<?php echo $multi_display; ?>" field_type="<?php echo $multi_display;?>" type="<?php echo $multi_display; ?>" /> <?php echo $multi_select_lable; ?>
						</li>
					<?php
					}
				}
			}
			
			if ( $multi_display == 'select' ) { ?>
				</select></div>
			<?php } else { ?></ul><?php } ?>
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		</div>
		<?php
		} else if( $type=='html' ) {
		?>

		<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label>
			
			<?php $editor_settings = array('media_buttons'=>false, 'textarea_rows'=>10); ?>
			
			<div class="editor" field_id="<?php echo $name;?>" field_type="editor">
			<?php wp_editor( stripslashes($value), $name, $editor_settings ); ?>
			</div>
			
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		     
		</div>
		<?php }
		elseif($type=='datepicker'){
					
					if($extra_fields['date_format'] == '')
						$extra_fields['date_format'] = 'yy-mm-dd';
						
				?>
				<script type="text/javascript" >
				
				jQuery(function() {
							
					jQuery( "#<?php echo $name;?>" ).datepicker({changeMonth: true,	changeYear: true,});
					
					jQuery( "#<?php echo $name;?>" ).datepicker( "option", "dateFormat", '<?php echo $extra_fields['date_format'];?>');
					
					<?php if(!empty($value)){?>
					jQuery( "#<?php echo $name;?>" ).datepicker( "setDate" , "<?php echo $value;?>" );
					<?php } ?>
				
				});
				
				</script>
		 <div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
		  </label>
				
			<input field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>" type="text" class="geodir_textfield"  />
					
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		 </div>
		
		<?php }
		elseif($type=='time'){
				
				if($value != '')
					$value = date('H:i',strtotime($value));
		?>
			<script type="text/javascript" >
			jQuery(document).ready(function(){
			
				jQuery('#<?php echo $name;?>').timepicker({
						showPeriod: true,
						showLeadingZero: true,
						showPeriod: true,
				});
			});
			</script>
		 <div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
		  </label>
			<input readonly="readonly" field_type="<?php echo $type;?>" name="<?php echo $name;?>" id="<?php echo $name;?>" value="<?php echo $value;?>" type="text" class="geodir_textfield"  />
					
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		 </div>
		
		<?php } 
		elseif($type=='taxonomy'){	if($value == $val['default']){$value='';} ?>
		<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix">
			<label>
				<?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo (trim($site_title)) ? $site_title : '&nbsp;'; ?>
				<?php if($is_required) echo '<span>*</span>';?>
			</label>
			
			<div id="<?php echo $name;?>" class="geodir_taxonomy_field" style="float:left; width:70%;">
			<?php 
			global $wpdb,$post,$cat_display,$post_cat,$package_id, $exclude_cats;
			
			$exclude_cats = array();
			
			if($is_admin == '1'){
			
				$post_type = get_post_type();
				
				$package_info = array() ;
				
				$package_info = (array)geodir_post_package_info($package_info , $post, $post_type);
				
				if(!empty($package_info)){
				
					if(isset($package_info['cat']) && $package_info['cat'] != ''){
						
						$exclude_cats = explode(',',$package_info['cat']);
						
					}	
				}
			}
			
			$cat_display = unserialize($val['extra_fields']);
			
			if(isset($_REQUEST['backandedit']) && (is_array($post_cat[$name]) && !empty($post_cat[$name]))){
					
				$post_cat = implode(",",$post_cat[$name]);
					
			}else{
				if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
				$post_cat = geodir_get_post_meta($_REQUEST['pid'], $name,true);
			}
			
			
			global $geodir_addon_list;
			if(!empty($geodir_addon_list) && array_key_exists('geodir_payment_manager', $geodir_addon_list) && $geodir_addon_list['geodir_payment_manager'] == 'yes') {
				
				$catadd_limit =	$wpdb->get_var(
													$wpdb->prepare(
														"SELECT cat_limit FROM ".GEODIR_PRICE_TABLE." WHERE pid = %d",
														array($package_id)
													)
												);
				
			
			}else{
				$catadd_limit = 0;
			}
			
			
			if($cat_display != '' && $cat_display != 'ajax_chained'){
				
				$required_limit_msg = '';
				if($catadd_limit > 0 && $cat_display!='select' && $cat_display!='radio'){
					
					$required_limit_msg = __('Only select',GEODIRECTORY_TEXTDOMAIN).' '.$catadd_limit.__(' categories for this package.',GEODIRECTORY_TEXTDOMAIN);
				
				}else{
					$required_limit_msg = $required_msg;
				}
				
				echo '<input type="hidden" cat_limit="'.$catadd_limit.'" id="cat_limit" value="'.$required_limit_msg.'" name="cat_limit['.$name.']"  />';
				
				
				if($cat_display == 'select' || $cat_display == 'multiselect')	{
					
					$cat_display == '';
					$multiple = '';
					if($cat_display == 'multiselect')
						$multiple = 'multiple="multiple"';
							
					echo '<select id="'.$name.'" '.$multiple.' type="'.$name.'" name="post_category['.$name.'][]" alt="'.$name.'" field_type="'.$cat_display.'">';
					
					if($cat_display == 'select')
						echo '<option value="">'.__('Select Category',GEODIRECTORY_TEXTDOMAIN).'</option>';
					
				}
				
				echo geodir_custom_taxonomy_walker($name,$catadd_limit=0);
				
				if($cat_display == 'select' || $cat_display == 'multiselect')
					echo '</select>';
			
			}else{
				
				echo geodir_custom_taxonomy_walker2($name,$catadd_limit);
			
			}
			
			?>
			</div>
			
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
		</div>
			
		<?php }
		elseif($type=='file'){ ?>
		
			<?php
			
		
			 
			// adjust values here
			$file_id = $name; // this will be the name of form field. Image url(s) will be submitted in $_POST using this key. So if $id == “img1” then $_POST[“img1”] will have all the image urls
			 
			if($value != ''){
				
			$file_value = trim($value,","); // this will be initial value of the above form field. Image urls.
			
			}else
				$file_value = '';
			 
			if($extra_fields['file_multiple'])
				$file_multiple = true; // allow multiple files upload
			else
				$file_multiple = false;	
				
			if($extra_fields['image_limit'])	
				$file_image_limit = $extra_fields['image_limit'];
			else
				$file_image_limit = 1;	
			 
			$file_width = geodir_media_image_large_width(); // If you want to automatically resize all uploaded images then provide width here (in pixels)
			 
			$file_height = geodir_media_image_large_height(); // If you want to automatically resize all uploaded images then provide height here (in pixels)
			
			if(!empty($file_value)){
				$curImages = explode(',',$file_value);
				if(!empty($curImages))
				$file_totImg = count($curImages);
			}
			
			?>				
           <?php /*?> <h5 class="geodir-form_title"> <?php echo $site_title; ?>
				 <?php if($file_image_limit!=0 && $file_image_limit==1 ){echo '<br /><small>('.__('You can upload').' '.$file_image_limit.' '.__('image with this package').')</small>';} ?>
				 <?php if($file_image_limit!=0 && $file_image_limit>1 ){echo '<br /><small>('.__('You can upload').' '.$file_image_limit.' '.__('images with this package').')</small>';} ?>
				 <?php if($file_image_limit==0){echo '<br /><small>('.__('You can upload unlimited images with this package').')</small>';} ?>
			</h5>   <?php */?>
			      
			<div id="<?php echo $name;?>_row" class="<?php if($is_required) echo 'required_field';?> geodir_form_row clearfix" >
			
			<div id="<?php echo $file_id; ?>dropbox" align="center" style="">
			<label style="text-align:left; padding-top:10px;"><?php $site_title = __($site_title,GEODIRECTORY_TEXTDOMAIN); echo $site_title; ?><?php if($is_required) echo '<span>*</span>';?></label>
				<input class="geodir-custom-file-upload" field_type="file" type="hidden" name="<?php echo $file_id; ?>" id="<?php echo $file_id; ?>" value="<?php echo $file_value; ?>" />
				<input type="hidden" name="<?php echo $file_id; ?>image_limit" id="<?php echo $file_id; ?>image_limit" value="<?php echo $file_image_limit; ?>" />
				<input type="hidden" name="<?php echo $file_id; ?>totImg" id="<?php echo $file_id; ?>totImg" value="<?php if(isset($file_totImg)){ echo $file_totImg;}else{ echo '0';} ?>" />
				<div style="float:left; width:55%;">
				<div class="plupload-upload-uic hide-if-no-js <?php if ($file_multiple): ?>plupload-upload-uic-multiple<?php endif; ?>" id="<?php echo $file_id; ?>plupload-upload-ui" style="float:left; width:30%;">
					<?php /*?><h4><?php _e('Drop files to upload');?></h4><br/><?php */?>
					<input id="<?php echo $file_id; ?>plupload-browse-button" type="button" value="<?php esc_attr_e('Select Files',GEODIRECTORY_TEXTDOMAIN); ?>" class="geodir_button" style="margin-top:10px;"  />
					<span class="ajaxnonceplu" id="ajaxnonceplu<?php echo wp_create_nonce($file_id.'pluploadan'); ?>"></span>
					<?php if ($file_width && $file_height): ?>
						<span class="plupload-resize"></span>
						<span class="plupload-width" id="plupload-width<?php echo $file_width; ?>"></span>
						<span class="plupload-height" id="plupload-height<?php echo $file_height; ?>"></span>
					<?php endif; ?>
					<div class="filelist"></div>
				</div>			
				<div class="plupload-thumbs <?php if ($file_multiple): ?>plupload-thumbs-multiple<?php endif; ?> " id="<?php echo $file_id; ?>plupload-thumbs" style=" clear:inherit; margin-top:0; margin-left:15px; padding-top:10px; float:left; width:50%;">
				</div>
				<?php /*?><span id="upload-msg" ><?php _e('Please drag &amp; drop the images to rearrange the order');?></span><?php */?>
								
				<span id="<?php echo $file_id; ?>upload-error" style="display:none"></span>
				
			</div></div>
			<span class="geodir_message_note"><?php _e($admin_desc,GEODIRECTORY_TEXTDOMAIN);?></span>
			<?php if($is_required) {?>
			<span class="geodir_message_error"><?php _e($required_msg,GEODIRECTORY_TEXTDOMAIN);?></span> 
			<?php } ?>
			</div>
		
			
		<?php }
		
		do_action('geodir_after_custom_form_field_'.$name, $listing_type, $package_id, $val);
	     
	}
	
}



if(!function_exists('geodir_get_field_infoby')){
function geodir_get_field_infoby($key='', $value='', $geodir_post_type=''){
	
	global $wpdb;
	
	$filter =	$wpdb->get_row(
							$wpdb->prepare(
								"SELECT * FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE post_type=%s AND ".$key."='".$value."'",
								array($geodir_post_type)
							)
						);
	
	if($filter)
	{
		return $filter;
	}
	else
	{
		return false;
	}
	
}
}



if(!function_exists('geodir_show_listing_info')){
function geodir_show_listing_info($fields_location=''){
	
	global $post, $preview, $wpdb;
	
	
	$payment_info = array();	
	$package_info = array();
	
	$package_info = geodir_post_package_info($package_info , $post);
	
	$post_package_id = $package_info->pid;
	
	ob_start();	$fields_info = geodir_post_custom_fields($post_package_id,'default',geodir_get_current_posttype(), $fields_location);
	
	if(!empty($fields_info))
	{
		//echo '<div class="geodir-company_info field-group">';
		$field_set_start = 0;
		
		
		if($fields_location == 'detail')
		
		$i = 1;
		foreach($fields_info as $type)
		{
			$html = '';
			$html_var = '';
			$field_icon = '';
			
			$variables_array = array();
			
			if ( $fields_location == 'detail' && isset( $type['show_as_tab'] ) && (int)$type['show_as_tab'] == 1 && in_array( $type['type'], array( 'text', 'datepicker', 'textarea', 'time', 'phone', 'email', 'select', 'multiselect', 'url', 'html', 'fieldset', 'radio', 'checkbox' ) ) ) {
				continue;
			}			
			
			if($type['type'] != 'fieldset'):
				$variables_array['post_id'] = $post->ID;
				$variables_array['label'] = __($type['site_title'],GEODIRECTORY_TEXTDOMAIN);
				$variables_array['value']= '';
				if(isset($post->$type['htmlvar_name']))
					$variables_array['value'] = $post->$type['htmlvar_name'];
			endif;
			
			//if($type['field_icon'])
			
			if (strpos($type['field_icon'],'http') !== false)
			{
				$field_icon = ' background: url('.$type['field_icon'].') no-repeat left center;background-size:18px 18px;padding-left: 21px;';
			}
			elseif (strpos($type['field_icon'],'fa fa-') !== false){
				$field_icon = '<i class="'.$type['field_icon'].'"></i>';
			}
			//else{$field_icon = $type['field_icon'];}
			
			
			switch($type['type'])
			{
				
				case 'fieldset':
						
						if($field_set_start == 1){
							echo '</div><div class="geodir-company_info field-group '.$type['htmlvar_name'].'"><h2>'.__($type['site_title'],GEODIRECTORY_TEXTDOMAIN).'</h2>';
						}else{
							echo '<h2>'.__($type['site_title'],GEODIRECTORY_TEXTDOMAIN).'</h2>';
							$field_set_start = 1;
						}
					
				break;
				
				case 'address':
						
						$html_var = $type['htmlvar_name'].'_address';
						
						if($type['extra_fields'])
						{
							
							$extra_fields = unserialize($type['extra_fields']);
							
							$addition_fields = '';
							
							if(!empty($extra_fields))
							{
							
								$show_city_in_address = apply_filters('geodir_show_city_in_address' , false);
								if(isset($extra_fields['show_city']) && $extra_fields['show_city'] && $show_city_in_address )
								{
									$field = $type['htmlvar_name'].'_city';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
								
								if(isset($extra_fields['show_zip']) && $extra_fields['show_zip'])
								{
									$field = $type['htmlvar_name'].'_zip';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
							}
						
						}
						/*if($type['extra_fields'])
						{
							
							$extra_fields = unserialize($type['extra_fields']);
							
							$addition_fields = '';
							
							if(!empty($extra_fields))
							{
								if($extra_fields['show_city'])
								{
									$field = $type['htmlvar_name'].'_city';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
								if($extra_fields['show_region'])
								{
									$field = $type['htmlvar_name'].'_region';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
								if($extra_fields['show_country'])
								{
									$field = $type['htmlvar_name'].'_country';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
								if($extra_fields['show_zip'])
								{
									$field = $type['htmlvar_name'].'_zip';
									if($post->$field)
									{
										$addition_fields .= ', '.$post->$field;
									}
								}
								
							}
						
						}*/
						
						if($post->$html_var):
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-home"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"  itemscope itemtype="http://schema.org/PostalAddress">';
							$html .= '<span class="geodir-i-location" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '&nbsp;';
							$html .= '</span>';
							//print_r($_POST);
							if($preview){	$html .= stripslashes($post->$html_var).$addition_fields.'</p>' ;}
							else{
								if($post->post_address){ $html .= '<span itemprop="streetAddress">'.$post->post_address.'</span><br>';}
								if($post->post_city){ $html .= '<span itemprop="addressLocality">'.$post->post_city.'</span><br>';}
								if($post->post_region){ $html .= '<span itemprop="addressRegion">'.$post->post_region.'</span><br>';}
								if($post->post_zip){ $html .= '<span itemprop="postalCode">'.$post->post_zip.'</span><br>';}
								if($post->post_country){ $html .= '<span itemprop="addressCountry">'.__( $post->post_country, GEODIRECTORY_TEXTDOMAIN ).'</span><br>';}
								$html .= '</div>';
							}
							
										
						endif;
					
						$variables_array['value'] = $post->$html_var;
					
				break;
				
				case 'url':
						
						$html_var = $type['htmlvar_name'];
						
						if($post->$type['htmlvar_name']):
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){
								
								if($type['name']=='geodir_facebook'){$field_icon_af = '<i class="fa fa-facebook-square"></i>';}
								elseif($type['name']=='geodir_twitter'){$field_icon_af = '<i class="fa fa-twitter-square"></i>';}
								else{$field_icon_af = '<i class="fa fa-link"></i>';}
								
								}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							if(!strstr($post->$type['htmlvar_name'],'http'))
							$website = 'http://'.$post->$type['htmlvar_name'];
							else
							$website = $post->$type['htmlvar_name'];	
							
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
														
							// all search engines that use the nofollow value exclude links that use it from their ranking calculation
							$rel = strpos($website, get_site_url())!==false ? '' : 'rel="nofollow"';
														
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'"><span class="geodir-i-website" style="'.$field_icon.'">'.$field_icon_af.'<a href="'.$website.'" target="_blank" '.$rel.' ><strong>'.apply_filters( 'geodir_custom_field_website_name', stripslashes(__($type['site_title'],GEODIRECTORY_TEXTDOMAIN)),$website, $post->ID ).'</strong></a></span></div>';
						
						endif;
						
				break;
				
				case 'phone':
						
						$html_var = $type['htmlvar_name'];
						
						if($post->$type['htmlvar_name']):
						
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-phone"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-contact" style="'.$field_icon.'">'.$field_icon_af.
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '&nbsp;';
							$html .= '</span>'.stripslashes($post->$type['htmlvar_name']).'</div>';
										
						endif;
						
				break;
				
				case 'time':
				
						$html_var = $type['htmlvar_name'];
						
						if($post->$type['htmlvar_name']):
							
							$value = '';
							if($post->$type['htmlvar_name'] != '')
								//$value = date('h:i',strtotime($post->$type['htmlvar_name']));
								$value = date(get_option( 'time_format' ),strtotime($post->$type['htmlvar_name']));
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-clock-o"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-time" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '&nbsp;';
							$html .= '</span>'.stripslashes($value).'</div>';

						endif;
						
				break;
				
				case 'datepicker':
						
						if($post->$type['htmlvar_name']):
							
							$date_format = geodir_default_date_format();
							if($type['extra_fields'] != ''){
								$date_format = unserialize($type['extra_fields']);
								$date_format = $date_format['date_format'];
							}
							
							$search = array('dd', 'mm', 'yy');
							$replace = array('d', 'm', 'Y');
							
							$date_format = str_replace($search, $replace, $date_format);
							
							$post_htmlvar_value = $date_format == 'd/m/Y' ? str_replace( '/', '-', $post->$type['htmlvar_name'] ) : $post->$type['htmlvar_name']; // PHP doesn't work well with dd/mm/yyyy format
							
							$value = '';
							if( $post->$type['htmlvar_name'] != '' ) {
								$value = date( $date_format, strtotime( $post_htmlvar_value ) );
							}
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-calendar"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-datepicker" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.$value.'</div>';
						
						endif;
						
				break;
				
				case 'text':
				
						$html_var = $type['htmlvar_name'];
						
						if( isset( $post->$type['htmlvar_name'] ) && $post->$type['htmlvar_name'] != '' && $type['htmlvar_name'] == 'geodir_timing'):
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-clock-o"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-time" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '&nbsp;';
							$html .= '</span>'.stripslashes($post->$type['htmlvar_name']).'</div>';
							
						elseif( isset( $post->$type['htmlvar_name'] ) && $post->$type['htmlvar_name'] ):
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-text" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.stripslashes($post->$type['htmlvar_name']).'</div>';	
								
						endif;
						
				break;
				
				case 'radio':
				
						$html_var = $type['htmlvar_name'];
						$html_val = $post->$type['htmlvar_name'];
						if($post->$type['htmlvar_name'] != ''):
							
							if($post->$type['htmlvar_name'] == 'f' || $post->$type['htmlvar_name'] == '0'):
								$html_val = __('No',GEODIRECTORY_TEXTDOMAIN);
							elseif($post->$type['htmlvar_name'] == 't' || $post->$type['htmlvar_name'] == '1'):
								$html_val = __('Yes',GEODIRECTORY_TEXTDOMAIN);
							endif;
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-radio" style="'.$field_icon.'">'.$field_icon_af ;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.$html_val.'</div>';		
						endif;
						
				break;
				
				case 'checkbox':
				
						$html_var = $type['htmlvar_name'];
						$html_val = $type['htmlvar_name'];
						
						if( (int)$post->$html_var == 1 ):
							
							if($post->$type['htmlvar_name'] == '1'):
									$html_val = __('Yes',GEODIRECTORY_TEXTDOMAIN);
							else:
									$html_val = __('No',GEODIRECTORY_TEXTDOMAIN);
							endif;
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-checkbox" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.$html_val.'</div>';		
						endif;
						
				break;
				
				case 'select':
				
						$html_var = $type['htmlvar_name'];
						
						if($post->$type['htmlvar_name']):
						
						if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-select" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.stripslashes($post->$type['htmlvar_name']).'</div>';		
						endif;
						
				break;
				
				
				case 'multiselect':
				
						$html_var = $type['htmlvar_name'];
						
						if(!empty($post->$type['htmlvar_name'])):
							
							if(is_array($post->$type['htmlvar_name']))
							{
								$post->$type['htmlvar_name'] = implode(', ', $post->$type['htmlvar_name']);
							}
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							
							$option_values = explode(',', $post->$type['htmlvar_name']);
							
							if($type['option_values'])
							{
								
								if(strstr($type['option_values'],"/")){
									
									$option_values = array();
									
									$field_values = explode(',', $type['option_values']);
									
									foreach($field_values as $data){
										
										$val = explode('/', $data);
										
										if( isset($val[1]) && in_array($val[1], explode(',', $post->$type['htmlvar_name'])))
											$option_values[] = $val[0];
									
									}
									
								}
							
							}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-select" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>';
							
							if(count($option_values) > 1){
								
								$html .= '<ul>';
									
									foreach($option_values as $val){
									
										$html .= '<li>'.stripslashes($val).'</li>';
										
									}
								
								$html .= '</ul>';
							
							}else{
								$html .= stripslashes(trim($post->$type['htmlvar_name'], ','));
							}
							
							$html .= '</div>';
									
						endif;
						
				break;
				
				
				case 'email':
				
						
						if( $type['htmlvar_name'] == 'geodir_email' && ((isset($package_info->sendtofriend) && $package_info->sendtofriend) || $post->$type['htmlvar_name'])){ 
								
								$b_send_inquiry = '';
								$b_sendtofriend = '';
								
								$html = '';		
								if(!$preview){
										$b_send_inquiry = 'b_send_inquiry';
										$b_sendtofriend = 'b_sendtofriend';
										$html = '<input type="hidden" name="geodir_popup_post_id" value="'.$post->ID.'" /><div class="geodir_display_popup_forms"></div>';
									}
									
									if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-envelope"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
							if($fields_location == 'detail'){
								
								$geodir_odd_even = 'geodir_more_info_odd';
								if($i%2==0)
									$geodir_odd_even = 'geodir_more_info_even';
							
								$i++;
							}
							
									$html .= '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'"><span class="geodir-i-email" style="'.$field_icon.'">'.$field_icon_af;
								$seperator = '';
								if($post->$type['htmlvar_name']){
									$html .= '<a href="javascript:void(0);" class="'.$b_send_inquiry.'" >'.SEND_INQUIRY.'</a>';
									$seperator = ' | ';
								}
								
								if(isset($package_info->sendtofriend) && $package_info->sendtofriend)
									$html .= $seperator.'<a href="javascript:void(0);" class="'.$b_sendtofriend.'">'.SEND_TO_FRIEND.'</a>';
							
								$html .= '</span></div>';
								
            		
                if(isset($_REQUEST['send_inquiry']) && $_REQUEST['send_inquiry']=='success'){
                    $html .= '<p class="sucess_msg">'.SEND_INQUIRY_SUCCESS.'</p>';
                 }elseif(isset($_REQUEST['sendtofrnd']) && $_REQUEST['sendtofrnd']=='success'){
                    $html .= '<p class="sucess_msg">'.SEND_FRIEND_SUCCESS.'</p>';
                 }elseif(isset($_REQUEST['emsg']) && $_REQUEST['emsg']=='captch'){
                    $html .= '<p class="error_msg_fix">'.WRONG_CAPTCH_MSG.'</p>';
                 } 
								
                /*if(!$preview){require_once (geodir_plugin_path().'/geodirectory-templates/popup-forms.php');}*/
           	
						}
						else
						{
							
							if($post->$type['htmlvar_name'])
							{	
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '<i class="fa fa-envelope"></i>';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
								$geodir_odd_even = '';
								if($fields_location == 'detail'){
									
									$geodir_odd_even = 'geodir_more_info_odd';
									if($i%2==0)
										$geodir_odd_even = 'geodir_more_info_even';
								
									$i++;
								}
								
								$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-email" style="'.$field_icon.'">'.$field_icon_af;
								$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
								$html .= '</span>'.stripslashes($post->$type['htmlvar_name']).'</div>';
							}
					
						}
						
				break;
				
				case 'file':
					$html_var = $type['htmlvar_name'];
						
					if(!empty($post->$type['htmlvar_name'])):
						
						$files = explode(",",$post->$type['htmlvar_name']);
						if(!empty($files)):
							
							$file_paths = '';
							foreach($files as $file){
								if(!empty($file)){
									
									$filetype = wp_check_filetype($file);
									
									$image_name_arr = explode('/',$file);
									$curr_img_dir = $image_name_arr[count($image_name_arr)-2];
									$filename = end($image_name_arr);
									$img_name_arr = explode('.',$filename);
									
									$arr_file_type = wp_check_filetype($filename);
									$uploaded_file_type = $arr_file_type['type'];
									
									$allowed_file_types = array('application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain');
									
									// If the uploaded file is the right format
									if(in_array($uploaded_file_type, $allowed_file_types)) 
									{
										$ext_path = '_'.$html_var.'_';
										
										$filename = explode($ext_path, $filename);
										
										$file_paths .= '<a href="'.$file.'" target="_blank">'.$filename[count($filename)-1].'</a>';	
										
									}else{
									
										$file_paths .= '<div class="geodir-custom-post-gallery" class="clearfix">';
										$file_paths .= geodir_show_image(array('src'=>$file),'thumbnail',false,false);
										//$file_paths .= '<img src="'.$file.'"  />';	
										$file_paths .= '</div>';
									}
									
								}
							}
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
								if($fields_location == 'detail'){
									
									$geodir_odd_even = 'geodir_more_info_odd';
									if($i%2==0)
										$geodir_odd_even = 'geodir_more_info_even';
								
									$i++;
								}
								
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' geodir-custom-file-box '.$type['htmlvar_name'].'"><div class="geodir-i-select" style="'.$field_icon.'">'.$field_icon_af;
							$html .= '<span style="display: inline-block; vertical-align: top; padding-right: 14px;">';
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>';
							$html .= $file_paths.'</div></div>';
							
						endif;
					endif;
					
				break;
				
				case 'textarea':
				
						if( !empty($post->$type['htmlvar_name']) ){
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
							$geodir_odd_even = '';
								if($fields_location == 'detail'){
									
									$geodir_odd_even = 'geodir_more_info_odd';
									if($i%2==0)
										$geodir_odd_even = 'geodir_more_info_even';
								
									$i++;
								}
								
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-text" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.wpautop(stripslashes($post->$type['htmlvar_name'])).'</div>';	
							
						}
				break;
				
				case 'html':
						if(!empty($post->$type['htmlvar_name'])){
							
							if (strpos($field_icon,'http') !== false) {$field_icon_af = '';}
							elseif($field_icon==''){$field_icon_af = '';}
							else{$field_icon_af = $field_icon; $field_icon='';}
							
								$geodir_odd_even = '';
								if($fields_location == 'detail'){
									
									$geodir_odd_even = 'geodir_more_info_odd';
									if($i%2==0)
										$geodir_odd_even = 'geodir_more_info_even';
								
									$i++;
								}
								
							$html = '<div class="geodir_more_info '.$geodir_odd_even.' '.$type['css_class'].' '.$type['htmlvar_name'].'" style="clear:both;"><span class="geodir-i-text" style="'.$field_icon.'">'.$field_icon_af;
							$html .= (trim($type['site_title'])) ? __($type['site_title'],GEODIRECTORY_TEXTDOMAIN).': ' : '';
							$html .= '</span>'.wpautop(stripslashes($post->$type['htmlvar_name'])).'</div>';	
							
						}
				break;
				
			}
			
			if($html):
				
				do_action('geodir_before_show_'.$html_var);
				if($html) echo apply_filters('geodir_show_'.$html_var, $html, $variables_array);
				do_action('geodir_after_show_'.$html_var);
				
			endif;
		
		}
		
		//echo '</div>';
		
	}
	
	
	return $html = ob_get_clean();
	
}
}




/*----------------Funtion for get date and time format-----------------------------*/

if(!function_exists('geodir_default_date_format')){
function geodir_default_date_format(){
	if($format = get_option('date_format'))
		return $format;
	else	 
		return 'dd-mm-yy';
}
}

if(!function_exists('geodir_get_formated_date')){
function geodir_get_formated_date($date)
{
	return mysql2date(get_option('date_format'), $date);
}}

if(!function_exists('geodir_get_formated_time')){
function geodir_get_formated_time($time)
{
	return mysql2date(get_option('time_format'), $time, $translate=true);
}}


/**
 * Save post file fields
 */



 
if (!function_exists('geodir_save_post_file_fields')) {
function geodir_save_post_file_fields($post_id = 0, $field_id='', $post_image = array()){
	
	global $wpdb,$plugin_prefix,$current_user;
	
	$post_type = get_post_type( $post_id );
	//echo $field_id; exit;
	$table = $plugin_prefix . $post_type . '_detail';
	
	$postcurr_images = array();
	$postcurr_images = geodir_get_post_meta($post_id,$field_id,true);
	$file_urls = '';
	
	
	if(!empty($post_image))
	{
	
		$invalid_files = array();
		
		//Get and remove all old images of post from database to set by new order
		$geodir_uploaddir = '';
		$uploads = wp_upload_dir(); 
		$uploads_dir = $uploads['path'];	
		
		$geodir_uploadpath = $uploads['path'];
		$geodir_uploadurl = $uploads['url']; 	
		$sub_dir = $uploads['subdir'];	
		 
		for($m=0;$m < count($post_image);$m++)
		{
		
			/* --------- start ------- */
			
			if(!$find_image = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM ".$table." WHERE $field_id = %s AND post_id = %d",array($post_image[$m],$post_id))))
			{
				
				
				$curr_img_url =  $post_image[$m];
				$image_name_arr = explode('/',$curr_img_url);
				$curr_img_dir = $image_name_arr[count($image_name_arr)-2];
				$filename = end($image_name_arr);
				$img_name_arr = explode('.',$filename);
				
				$arr_file_type = wp_check_filetype($filename);
				
				$uploaded_file_type = $arr_file_type['type'];
				
				// Set an array containing a list of acceptable formats
				$allowed_file_types = array('image/jpg','image/jpeg','image/gif','image/png','application/pdf','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'text/plain');
				
				// If the uploaded file is the right format
				if(in_array($uploaded_file_type, $allowed_file_types)) 
				{
					
					if ( ! function_exists( 'wp_handle_upload' ) ) 
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
					
					if(!is_dir($geodir_uploadpath))
						mkdir($geodir_uploadpath);
						
					$new_name = $post_id.'_'.$field_id.'_'.$img_name_arr[0].'.'.$img_name_arr[1];
					
					if($curr_img_dir == end(explode("/",$sub_dir)) ){	
						$img_path = $geodir_uploadpath.'/'.$filename;
						$img_url = $geodir_uploadurl.'/'.$filename;
					}else{
						$img_path = $uploads_dir.'/temp_'.$current_user->data->ID.'/'.$filename;
						$img_url = $uploads['url'].'/temp_'.$current_user->data->ID.'/'.$filename;
					}	
					
					$uploaded_file = '';
					if( file_exists($img_path) )
						$uploaded_file = copy($img_path, $geodir_uploadpath.'/'.$new_name);
						
					if($curr_img_dir != $geodir_uploaddir)	{
						if(file_exists($img_path))
							unlink($img_path);
					}
					
					if(!empty($uploaded_file)) 
						$file_urls = $geodir_uploadurl.'/'.$new_name;
					 
				}
				
			}
			else{
				
				$file_urls = $post_image[$m];
			}
		}
	
		
	}	
	
		//Remove all old attachments and temp images
		if(!empty($postcurr_images)){
			
			if($file_urls != $postcurr_images){
				$invalid_files[] = (object)array('src' => $postcurr_images);
				$invalid_files = (object) $invalid_files;
			}
		}
		
		
		geodir_save_post_meta($post_id,$field_id,$file_urls);
		
		if(!empty($invalid_files))
				geodir_remove_attachments($invalid_files);

}
}



/* ============================ ADD BY COMP 5 =======================
=============================== ADD BY COMP 5 ======================= */

add_filter('upload_mimes', 'geodir_custom_upload_mimes');
function geodir_custom_upload_mimes ( $existing_mimes=array() ) {
		$existing_mimes['wif'] = 'text/plain';
		$existing_mimes['jpg|jpeg'] = 'image/jpeg';
		$existing_mimes['gif'] = 'image/gif';
  	$existing_mimes['png'] = 'image/png';
    $existing_mimes['pdf'] = 'application/pdf';
		$existing_mimes['txt'] = 'text/text';
		$existing_mimes['csv'] = 'application/octet-stream';
    $existing_mimes['doc'] = 'application/msword';
    $existing_mimes['xla|xls|xlt|xlw'] = 'application/vnd.ms-excel';
    $existing_mimes['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    $existing_mimes['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    return $existing_mimes;
}

/**
 * Pluploader
 **/
if (!function_exists('geodir_plupload_action')) {
	
	function geodir_upload_dir($upload) {
		global $current_user;
		$upload['subdir']	= $upload['subdir'].'/temp_'.$current_user->data->ID;
		$upload['path']		= $upload['basedir'] . $upload['subdir'];
		$upload['url']		= $upload['baseurl'] . $upload['subdir'];
		return $upload;
	}
	
	function geodir_plupload_action() {
	 
		// check ajax noonce
		$imgid = $_POST["imgid"];
		
		check_ajax_referer($imgid . 'pluploadan');
	 	
		// handle custom file uploaddir
		add_filter('upload_dir', 'geodir_upload_dir');
		
		// change file orinetation if needed
		$fixed_file = geodir_exif($_FILES[$imgid . 'async-upload']);
				
		// handle file upload
		$status = wp_handle_upload($fixed_file, array('test_form' => true, 'action' => 'plupload_action'));
	 	// remove handle custom file uploaddir
	 	remove_filter('upload_dir', 'geodir_upload_dir');
	 
		// send the uploaded file url in response
		if(isset($status['url'])){echo $status['url'];}else{echo '';}
		exit;
	}
}

function geodir_get_video($post_id){
	global $wpdb,$plugin_prefix;
	
	$post_type = get_post_type( $post_id );
	
	$table = $plugin_prefix.$post_type.'_detail';
	
	$results = 	$wpdb->get_results($wpdb->prepare("SELECT geodir_video FROM ".$table." WHERE post_id=%d",array($post_id)));
	
	if($results){return $results[0]->geodir_video;}
		
}

function geodir_get_special_offers($post_id){
	global $wpdb,$plugin_prefix;
	
	$post_type = get_post_type( $post_id );
	
	$table = $plugin_prefix.$post_type.'_detail';
	
	$results =	$wpdb->get_results($wpdb->prepare("SELECT geodir_special_offers FROM ".$table." WHERE post_id=%d",array($post_id)));
	
	if($results){return $results[0]->geodir_special_offers;}
		
}

if(!function_exists('geodir_max_upload_size')) {
function geodir_max_upload_size() {
	//return  '2mb'; 
	return apply_filters('geodir_default_image_upload_size_limit', '2mb');
}}



/*	START SORTING OPTIONS IN BACKEND --------------------------*/


add_filter('geodir_add_custom_sort_options', 'geodir_add_custom_sort_options', 0, 2);

function geodir_add_custom_sort_options($fields, $post_type){
	global $wpdb;
	
	if( $post_type != '' ){
		
		$all_postypes = geodir_get_posttypes();
		
		if(in_array($post_type, $all_postypes)){
			
			$custom_fields =	$wpdb->get_results(
				$wpdb->prepare(
					"select post_type,data_type,field_type,site_title,htmlvar_name from ".GEODIR_CUSTOM_FIELDS_TABLE." where post_type = %s and is_active='1' and cat_sort='1' order by sort_order asc",
					array($post_type)
				), 'ARRAY_A'
			);
			
			if(!empty($custom_fields)){
			
				foreach($custom_fields as $val){
					$fields[] = $val;
				}
			}
			
		}
	
	}
	
	return $fields;
}


function geodir_get_custom_sort_options($post_type = ''){
	
	global $wpdb;
	
	if($post_type != ''){
		
		$all_postypes = geodir_get_posttypes();
		
		if(!in_array($post_type, $all_postypes))
			return false;
		
		$fields = array();
		
		$fields[] = array(
									'post_type' => $post_type,
									'data_type' => '',
									'field_type' => 'random',
									'site_title' => 'Random',
									'htmlvar_name' => 'post_title'
							);
					
		$fields[] = array(
										'post_type' => $post_type,
										'data_type' => '',
										'field_type' => 'datetime',
										'site_title' => 'Add date',
										'htmlvar_name' => 'post_date'
								);
		$fields[] = array(
										'post_type' => $post_type,
										'data_type' => '',
										'field_type' => 'bigint',
										'site_title' => 'Review',
										'htmlvar_name' => 'comment_count'
								);
		$fields[] = array(
									'post_type' => $post_type,
									'data_type' => '',
									'field_type' => 'float',
									'site_title' => 'Rating',
									'htmlvar_name' => 'overall_rating'
							);		
		$fields[] = array(
									'post_type' => $post_type,
									'data_type' => '',
									'field_type' => 'text',
									'site_title' => 'Title',
									'htmlvar_name' => 'post_title'
							);
		
		
		return $fields = apply_filters('geodir_add_custom_sort_options', $fields, $post_type);
		
	}
	
	return false;	
}


function godir_set_sort_field_order($field_ids = array()){
	
	global $wpdb;	
	
	$count = 0;
	if( !empty( $field_ids ) ):
		foreach ($field_ids as $id) {
		
			$cf = trim($id, '_');
		
		$post_meta_info = $wpdb->query(
														$wpdb->prepare( 
															"update ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." set 
															sort_order=%d 
															where id= %d",
															array($count, $cf)
														)
												);
			$count ++;	
		}
		
		return $field_ids;
	else:
		return false;
	endif;
}


if (!function_exists('geodir_custom_sort_field_save')) {
function geodir_custom_sort_field_save( $request_field = array() , $default = false ){
	
	global $wpdb, $plugin_prefix;
	
	$result_str = isset($request_field['field_id']) ? trim($request_field['field_id']) : '';
	
	$cf = trim($result_str, '_');
	
	/*-------- check dublicate validation --------*/
	
	$field_type = isset($request_field['field_type']) ? $request_field['field_type'] : '';
	$cehhtmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
		
		$post_type = $request_field['listing_type'];
		$data_type = isset( $request_field['data_type']) ?  $request_field['data_type'] : '';
		$field_type = isset( $request_field['field_type']) ?  $request_field['field_type'] : '';
		$site_title = isset($request_field['site_title']) ? $request_field['site_title'] : '';
		$htmlvar_name = isset($request_field['htmlvar_name']) ? $request_field['htmlvar_name'] : '';
		$sort_order = isset($request_field['sort_order']) ? $request_field['sort_order'] : 0;
		$is_active = isset($request_field['is_active']) ? $request_field['is_active'] : 0;
		$is_default = isset($request_field['is_default']) ? $request_field['is_default'] : '';
		$asc = isset($request_field['asc']) ? $request_field['asc'] : 0;
		$desc = isset($request_field['desc']) ? $request_field['desc'] : 0;
		$asc_title = isset($request_field['asc_title']) ? $request_field['asc_title'] : '';
		$desc_title = isset($request_field['desc_title']) ? $request_field['desc_title'] : '';
		
		$default_order = '';
		if($is_default != ''){
			$default_order = $is_default;
			$is_default = '1';
		}
		
		
		$check_html_variable = 	$wpdb->get_var(
														$wpdb->prepare(
																"select htmlvar_name from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where htmlvar_name = %s and post_type = %s and field_type=%s ",
																array($cehhtmlvar_name, $post_type, $field_type)		
														)
												);
		
		if($is_default == 1){
			
			$wpdb->query($wpdb->prepare("update ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." set is_default='0', default_order='' where post_type = %s", array($post_type)));
		
		}
		
		
		if(!$check_html_variable){
			
			$wpdb->query(
			
			$wpdb->prepare(
				
				"insert into ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." set 
				post_type = %s,
				data_type = %s,
				field_type = %s,
				site_title = %s,
				htmlvar_name = %s,
				sort_order = %d,
				is_active = %d,
				is_default = %d,
				default_order = %s,
				sort_asc = %d,
				sort_desc = %d,
				asc_title = %s,
				desc_title = %s",
				
					array($post_type, $data_type, $field_type, $site_title, $htmlvar_name, $sort_order, $is_active, $is_default, $default_order, $asc, $desc, $asc_title, $desc_title)
				)
			
			);
			
			
			$lastid = $wpdb->insert_id;
			
			$lastid = trim($lastid);
			
		}else{
			
			$wpdb->query(
			
			$wpdb->prepare(
				
				"update ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." set 
				post_type = %s,
				data_type = %s,
				field_type = %s,
				site_title = %s,
				htmlvar_name = %s,
				sort_order = %d,
				is_active = %d,
				is_default = %d,
				default_order = %s,
				sort_asc = %d,
				sort_desc = %d,
				asc_title = %s,
				desc_title = %s
				where id = %d",
				
					array($post_type, $data_type, $field_type, $site_title, $htmlvar_name, $sort_order, $is_active, $is_default, $default_order, $asc, $desc, $asc_title, $desc_title,$cf)
				)
			
			);
			
			$lastid = trim($cf);
		
		}									
		
		
	
	
	return (int)$lastid;

}
}


if (!function_exists('geodir_custom_sort_field_delete')) {
function geodir_custom_sort_field_delete( $field_id = '' ){
	
	global $wpdb, $plugin_prefix;
	if($field_id != ''){
		$cf = trim($field_id, '_');
			
			$wpdb->query($wpdb->prepare("delete from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where id= %d ",array($cf)));
			
			return $field_id;
			
	}else
		return 0;	
			
}
}


if (!function_exists('geodir_custom_sort_field_adminhtml')) {
function geodir_custom_sort_field_adminhtml($field_type , $result_str, $field_ins_upd = '', $default = false)
{
	global $wpdb;
	$cf = $result_str;
	if(!is_object($cf))
	{
		
		$field_info =	$wpdb->get_row($wpdb->prepare("select * from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where id= %d",array($cf)));
		
	}
	else
	{
		$field_info = $cf;
		$result_str = $cf->id;
	}
	
	
	if(!isset($field_info->post_type)){
		$post_type = $_REQUEST['listing_type'];
	}else
		$post_type = $field_info->post_type;
		
	
	$field_types = explode('-_-', $field_type);
	$field_type = $field_types[0];
	$htmlvar_name = isset($field_types[1]) ? $field_types[1] : '';
	
	$site_title = '';
	if($site_title == '')
		$site_title = isset($field_info->site_title) ? $field_info->site_title : '';
	
	if($site_title == ''){
		
		$fields = geodir_get_custom_sort_options($post_type);
		
		foreach($fields as $val){
			
			if($val['field_type'] == $field_type && $val['htmlvar_name'] == $htmlvar_name){
				$site_title = isset($val['site_title']) ? $val['site_title'] : '';
			}
			
		}
		
	}
		
	if($htmlvar_name == '')
		$htmlvar_name = isset($field_info->htmlvar_name) ? $field_info->htmlvar_name : '';
		
	$nonce = wp_create_nonce( 'custom_fields_'.$result_str );
	
	?>
	<li class="text" id="licontainer_<?php echo $result_str;?>">
			<div class="title title<?php echo $result_str;?> gt-fieldset"   title="<?php _e('Double Click to toggle and drag-drop to sort',GEODIRECTORY_TEXTDOMAIN);?>" ondblclick="show_hide('field_frm<?php echo $result_str;?>')">
	 <?php
		
		$nonce = wp_create_nonce( 'custom_fields_'.$result_str );
	 ?>
	  
					<div title="<?php _e('Click to remove field',GEODIRECTORY_TEXTDOMAIN);?>" onclick="delete_sort_field('<?php echo $result_str;?>', '<?php echo $nonce;?>', this)" class="handlediv close"></div>
					
				 <b style="cursor:pointer;" onclick="show_hide('field_frm<?php echo $result_str;?>')"><?php echo ucwords(__('Field:',GEODIRECTORY_TEXTDOMAIN).' ('.$site_title.')');?></b>
					
			</div>
	
			<div id="field_frm<?php echo $result_str;?>" class="field_frm" style="display:<?php if($field_ins_upd == 'submit'){echo 'block;';}else{echo 'none;';} ?>">
					<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>" />
					<input type="hidden" name="listing_type" id="listing_type" value="<?php echo $post_type;?>" />
					<input type="hidden" name="field_type" id="field_type" value="<?php echo $field_type;?>" />
					<input type="hidden" name="field_id" id="field_id" value="<?php echo $result_str;?>" />
				<input type="hidden" name="data_type" id="data_type" value="<?php if(isset($field_info->data_type)){ echo $field_info->data_type;}?>" />
				<input type="hidden" name="htmlvar_name" id="htmlvar_name" value="<?php echo $htmlvar_name;?>" />
				
				 
					<table class="widefat post fixed" border="0" style="width:100%;">
					
					<?php if($field_type != 'random'){?>
					
					<input type="hidden" name="site_title" id="site_title" value="<?php echo $site_title;?>" />
					
					<tr>
					<td>Select Ascending</td>
						<td>
						 <input type="checkbox"  name="asc" id="asc"  value="1" <?php if(isset($field_info->sort_asc) && $field_info->sort_asc == '1'){ echo 'checked="checked"';}?>/>
						
						 <input type="text" name="asc_title" id="asc_title" placeholder="<?php _e('Ascending title', GEODIRECTORY_TEXTDOMAIN); ?>" value="<?php if(isset($field_info->asc_title)){ echo $field_info->asc_title;}?>" style="width:45%;" />
						 
						 <input type="radio" name="is_default" value="<?php echo $htmlvar_name;?>_asc" <?php if(isset($field_info->default_order) && $field_info->default_order == $htmlvar_name.'_asc'){ echo 'checked="checked"';}?>/><span><?php _e('Set as default sort.',GEODIRECTORY_TEXTDOMAIN);?></span>
						 
						 <br />
										<span><?php _e('Select if you want to show option in sort.',GEODIRECTORY_TEXTDOMAIN);?></span>
						</td>
					</tr>
					
					<tr>
					<td>Select Descending</td>
						<td>
						 <input type="checkbox"  name="desc" id="desc"  value="1" <?php if(isset($field_info->sort_desc) && $field_info->sort_desc=='1'){ echo 'checked="checked"';}?>/>
						
						 <input type="text" name="desc_title" id="desc_title" placeholder="<?php _e('Descending title', GEODIRECTORY_TEXTDOMAIN); ?>" value="<?php if(isset($field_info->desc_title)){ echo $field_info->desc_title;}?>" style="width:45%;" />
						 <input type="radio" name="is_default" value="<?php echo $htmlvar_name;?>_desc" <?php if(isset($field_info->default_order) && $field_info->default_order == $htmlvar_name.'_desc'){ echo 'checked="checked"';}?>/><span><?php _e('Set as default sort.',GEODIRECTORY_TEXTDOMAIN);?></span>
						 <br />
							<span><?php _e('Select if you want to show option in sort.',GEODIRECTORY_TEXTDOMAIN);?></span>
						</td>
					</tr>
					
					<?php }else{ ?>
						
						
						<tr>
                <td ><strong><?php _e('Frontend title :',GEODIRECTORY_TEXTDOMAIN);?></strong></td>
                <td align="left">
                	<input type="text" name="site_title" id="site_title" value="<?php echo $site_title;?>" size="50" />
                	<br /><span><?php _e('Section title which you wish to display in frontend',GEODIRECTORY_TEXTDOMAIN);?></span>
                </td>
            </tr>
						
						<tr>
						<td ><strong><?php _e('Default sort option :',GEODIRECTORY_TEXTDOMAIN);?></strong></td>
						<td align="left">
								<input type="checkbox" name="is_default" value="<?php echo $field_type;?>"  <?php if(isset($field_info->is_default) && $field_info->is_default == '1'){ echo 'checked="checked"';}?>/>
								<br />    <span><?php _e('If field is checked then the field will be use as default sort.',GEODIRECTORY_TEXTDOMAIN);?></span>
						</td>
				</tr>
					
					<?php } ?>
				
				<tr>
						<td ><strong><?php _e('Is active :',GEODIRECTORY_TEXTDOMAIN);?></strong></td>
						<td align="left">
								<select name="is_active" id="is_active">
								<option value="1" <?php if(isset($field_info->is_active) && $field_info->is_active=='1'){ echo 'selected="selected"';}?>><?php _e('Yes',GEODIRECTORY_TEXTDOMAIN);?></option>
								<option value="0" <?php if(isset($field_info->is_active) && $field_info->is_active=='0'){ echo 'selected="selected"';}?>><?php _e('No',GEODIRECTORY_TEXTDOMAIN);?></option>
								</select>
								<br /> <span><?php _e('Select yes or no. If no is selected then the field will not be displayed anywhere.',GEODIRECTORY_TEXTDOMAIN);?></span>
							</td>
					</tr>
					
					 <tr>
                <td ><strong><?php _e('Display order :',GEODIRECTORY_TEXTDOMAIN);?></strong></td>
                <td align="left"><input type="text" readonly="readonly" name="sort_order" id="sort_order"  value="<?php if(isset($field_info->sort_order)){ echo $field_info->sort_order;}?>" size="50" />
                <br />    <span><?php _e('Enter the display order of this field in backend. e.g. 5',GEODIRECTORY_TEXTDOMAIN);?></span>
                </td>
            </tr>
							
					<tr>
							<td >&nbsp;</td>
							<td align="left">
							<input type="button" class="button" name="save" id="save" value="Save" onclick="save_sort_field('<?php echo $result_str;?>')" /> 
							
							<a href="javascript:void(0)"><input type="button" name="delete" value="Delete" onclick="delete_sort_field('<?php echo $result_str;?>', '<?php echo $nonce;?>', this)" class="button_n" /></a>
							
							</td>
					</tr>
			</table>
			
		</div>
	</li> <?php
	
}
}

// filter field as per price package
if (!function_exists('check_field_visibility')) {
function check_field_visibility($package_id, $field_name, $post_type) {
	global $wpdb, $geodir_addon_list;
	if (!(isset($geodir_addon_list['geodir_payment_manager']) && $geodir_addon_list['geodir_payment_manager']=='yes')) {
		return true;
	}
	global $wpdb;
	if (!$package_id || !$field_name || !$post_type) {
		return true;
	}
	$sql = $wpdb->prepare("SELECT id FROM ".GEODIR_CUSTOM_FIELDS_TABLE." WHERE is_active='1' AND htmlvar_name=%s AND post_type=%s AND FIND_IN_SET(%s, packages)",array($field_name, $post_type, (int)$package_id));

	if ($wpdb->get_var($sql)) {
		return true;
	}
	return false;
}
}
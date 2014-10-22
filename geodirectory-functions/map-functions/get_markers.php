<?php 
if ( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'homemap_catlist' ) {
	$post_taxonomy = geodir_get_taxonomies( $_REQUEST['post_type'] );
	$map_canvas_name = $_REQUEST['map_canvas'];
	$child_collapse= $_REQUEST['child_collapse'];
	echo home_map_taxonomy_walker( $post_taxonomy, 0, true, 0, $map_canvas_name, $child_collapse, true );
	die;
}
	 
header("Content-type: text/javascript");

if( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'cat' ){

	echo  get_markers();

}else if( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'info' ){

	global $wpdb,$plugin_prefix;
	
	if( $_REQUEST['m_id'] != ''){$pid = $_REQUEST['m_id'];}
	else{ echo 'no marker data found'; exit;}

	if(isset($_REQUEST['post_preview']) && $_REQUEST['post_preview'] != '' && isset($_SESSION['listing'])){
		
		$post = (object)unserialize($_SESSION['listing']);
		echo geodir_get_infowindow_html($post, $_REQUEST['post_preview']);
	
	}else{
		
		
		$geodir_post_type = get_post_type($pid);
		
		$table = $plugin_prefix . $geodir_post_type . '_detail';
		
		$sql = $wpdb->prepare("SELECT * FROM ".$table." WHERE post_id = %d" , array($pid) );
		
		$postinfo = $wpdb->get_results($sql);
		
		$data_arr = array();
		
		if($postinfo)
		{
			$srcharr = array("'","/","-",'"','\\');
			$replarr = array("&prime;","&frasl;","&ndash;","&ldquo;",'');
			
			foreach($postinfo as $postinfo_obj)
			{
				echo geodir_get_infowindow_html($postinfo_obj);
			}
		}
	
	}
	}
	
function get_markers(){	
	
	global $wpdb, $plugin_prefix,$geodir_cat_icons;
	
	$search = '';
	$main_query_array ;
	
	$srcharr = array("'","/","-",'"','\\');
	$replarr = array("&prime;","&frasl;","&ndash;","&ldquo;",'');
	
	$post_type = isset($_REQUEST['gd_posttype']) ? $_REQUEST['gd_posttype'] : 'gd_place';
	
	$map_cat_ids_array = array('0');
	$cat_find_array = array(" FIND_IN_SET(%d, pd.".$post_type."category)");
	
	
	$field_default_cat = '';
	if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] != ''){	
		
		$map_cat_arr = trim($_REQUEST['cat_id'], ',');
		
		if(!empty($map_cat_arr))
		{
			
			$field_default_cat .= "when (default_category IN (".$map_cat_arr.")) then default_category ";
			
			$map_cat_ids_array = explode(',',$map_cat_arr);
			$cat_find_array = array();
			foreach($map_cat_ids_array as $cat_id){
				$field_default_cat .= "when   ( find_in_set($cat_id,  `".$post_type."category`) > 0) then $cat_id ";
				$cat_find_array[] = " FIND_IN_SET(%d, pd.".$post_type."category)";
				$main_query_array[] = $cat_id;
			}
			
		}	
	}
	
	if(!empty($field_default_cat))
		$field_default_cat = ', case '.$field_default_cat.' end as default_icon ';
	
	if(!empty($cat_find_array))
		$search .= "AND (". implode(' OR ', $cat_find_array). ")";
	
	
	
	$main_query_array = $map_cat_ids_array ;
	/*$map_cat_length = count($map_cat_ids_array);
	$map_cat_format = array_fill(0, $map_cat_length, '%d');
	$format = implode(',', $map_cat_format);	*/
	
	if(isset($_REQUEST['search']) && !empty($_REQUEST['search'])){
		
			$search .= " AND p.post_title like %s";
			$main_query_array[] = "%".$_REQUEST['search']."%";
			
	}
	
	
	$gd_posttype = '';
	if(isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] != '')
	{	
		$table = $plugin_prefix . $_REQUEST['gd_posttype'].'_detail';
		$gd_posttype = " AND p.post_type = %s";
		$main_query_array[] = $_REQUEST['gd_posttype'];
		
	}else
		$table = $plugin_prefix .'gd_place_detail';	
	
	/*$join = $table." as pd,"
					.GEODIR_ICON_TABLE." as pi ";*/
	
	$join = $table." as pd ";
					
	$join = apply_filters('geodir_home_map_listing_join', $join);
	$search = apply_filters('geodir_home_map_listing_where', $search);
		
	/*$catsql = 	$wpdb->prepare("SELECT pi.* FROM "
				.$wpdb->posts." as p," 
				.$join." WHERE p.ID = pd.post_id 
				AND pd.post_id = pi.post_id 
				AND p.post_status = 'publish'  AND pi.cat_id in ($format) " . $search . $gd_posttype , $main_query_array);*/
	
	$catsql = 	$wpdb->prepare("SELECT pd.* $field_default_cat FROM "
				.$wpdb->posts." as p," 
				.$join." WHERE p.ID = pd.post_id 
				AND p.post_status = 'publish' " . $search . $gd_posttype , $main_query_array);
		
	
	$catsql = apply_filters('geodir_home_map_listing_query' , $catsql , $search) ;
	
	$catinfo = $wpdb->get_results($catsql);
	
	//print_r($catinfo);
	$cat_content_info = array();
	$content_data = array();
	$post_ids = array();
	if(!empty($catinfo))
	{
		foreach($catinfo as $catinfo_obj)
		{ 	
			//$content_data[] = $catinfo_obj->json; 
			
			$icon = '';
			$default_cat = $catinfo_obj->default_icon;
			//$default_cat = $catinfo_obj->default_category;
			
		 if($default_cat != ''){
			
			if(!empty($geodir_cat_icons) && is_array($geodir_cat_icons) && array_key_exists($default_cat,$geodir_cat_icons)){
					
					$icon = $geodir_cat_icons[$default_cat];		
					
			}else{
			
				$post_type = isset($_REQUEST['gd_posttype']) ? $_REQUEST['gd_posttype'] : '';
				$term_icon_url = get_tax_meta($default_cat,'ct_cat_icon', false, $post_type);
				$icon = isset($term_icon_url['src']) ? $term_icon_url['src'] : '';
				
				$geodir_cat_icons[$default_cat] = $icon;
				
			}
			
		 }
		 
	
			$e_dates = '';
			if($post_type=='gd_event'){//print_r($catinfo_obj);
	
			$event_arr = unserialize($catinfo_obj->recurring_dates);
			$e_arr = explode(",",$event_arr['event_recurring_dates']);
			global $geodir_date_format;
			$e=0;
			foreach($e_arr as $e_date){
				//echo '###'.strtotime($e_date);
				if(strtotime($e_date) >= strtotime(date("Y-m-d"))){
					$e++;
					$e_dates .= ' :: '.date($geodir_date_format,strtotime($e_date));
					if($e==3){break;}// only show 3 event dates
					}
			}
			
			if($e_dates == ''){ continue;} // if the event is old don't show it on the map
			}
			$post_title = $catinfo_obj->post_title.$e_dates;
			$title = str_replace($srcharr,$replarr,$post_title);
		 
			$content_data[] = '{"id":"'.$catinfo_obj->post_id.'","t": "'.$title.'","lt": "'.$catinfo_obj->post_latitude.'","ln": "'.$catinfo_obj->post_longitude.'","mk_id":"'.$catinfo_obj->post_id.'_'.$catinfo_obj->default_category.'","i":"'.$icon.'"}';
			
			$post_ids[] = $catinfo_obj->post_id; 
		}
	}
	
	if(!empty($content_data))
	{	$cat_content_info[]= implode(',',$content_data); }
			
	$totalcount = count( array_unique($post_ids) );
	
	if(!empty($cat_content_info))	
		return '[{"totalcount":"'.$totalcount.'",'.substr(implode(',',$cat_content_info),1).']';
	else
		return '[{"totalcount":"0"}]';
	
}	


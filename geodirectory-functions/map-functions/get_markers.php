<?php 
if( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'homemap_catlist' ){
	$post_taxonomy = geodir_get_taxonomies($_REQUEST['post_type']);
	$map_canvas_name = $_REQUEST['map_canvas'];
	$child_collapse= $_REQUEST['child_collapse'];
	echo home_map_taxonomy_walker($post_taxonomy, 0, true, 0 ,  $map_canvas_name,$child_collapse);
	die;
}
	 
header("Content-type: text/javascript");

if( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'cat' ){

	echo  get_markers();

}else if( isset( $_REQUEST['ajax_action'] ) && $_REQUEST['ajax_action'] == 'info' ){

	global $wpdb,$plugin_prefix;
	
	if( $_REQUEST['m_id'] != ''){$pid = mysql_real_escape_string($_REQUEST['m_id']);}
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
	
	global $wpdb, $plugin_prefix;
	
	$search = '';
	$main_query_array ;
	$map_cat_ids_array = array('0');
	if(isset($_REQUEST['cat_id']) && $_REQUEST['cat_id'] != ''){	
		$map_cat_arr = mysql_real_escape_string($_REQUEST['cat_id']);
		
		if(!empty($map_cat_arr))
		{
			$map_cat_ids_array = explode(',',$map_cat_arr);
		}	
	}
	$main_query_array = $map_cat_ids_array ;
	$map_cat_length = count($map_cat_ids_array);
	$map_cat_format = array_fill(0, $map_cat_length, '%d');
	$format = implode(',', $map_cat_format);	
	
	if(isset($_REQUEST['search']) && !empty($_REQUEST['search'])){
		
			$search .= " AND p.post_title like %s";
			$main_query_array[] = "%".mysql_real_escape_string($_REQUEST['search'])."%";
			
	}
	
	
	$gd_posttype = '';
	if(isset($_REQUEST['gd_posttype']) && $_REQUEST['gd_posttype'] != '')
	{	
		$table = $plugin_prefix . $_REQUEST['gd_posttype'].'_detail';
		$gd_posttype = " AND p.post_type = %s";
		$main_query_array[] = mysql_real_escape_string($_REQUEST['gd_posttype']);
		
	}else
		$table = $plugin_prefix .'gd_place_detail';	
	
	$join = $table." as pd,"
					.GEODIR_ICON_TABLE." as pi ";
	
	$join = apply_filters('geodir_home_map_listing_join', $join);
	$search = apply_filters('geodir_home_map_listing_where', $search);
		
	$catsql = 	$wpdb->prepare("SELECT pi.* FROM "
				.$wpdb->posts." as p," 
				.$join." WHERE p.ID = pd.post_id 
				AND pd.post_id = pi.post_id 
				AND p.post_status = 'publish'  AND pi.cat_id in ($format) " . $search . $gd_posttype , $main_query_array);
		
	
	$catsql = apply_filters('geodir_home_map_listing_query' , $catsql , $search) ;
	
	$catinfo = $wpdb->get_results($catsql);
	
	
	$cat_content_info = array();
	$content_data = array();
	$post_ids = array();
	
	foreach($catinfo as $catinfo_obj)
	{ 	
		$content_data[] = $catinfo_obj->json; 
		$post_ids[] = $catinfo_obj->post_id; 
	}
	
	if(!empty($content_data))
	{	$cat_content_info[]= implode(',',$content_data); }
			
	$totalcount = count( array_unique($post_ids) );
	
	if(!empty($cat_content_info))	
		return '[{"totalcount":"'.$totalcount.'",'.substr(implode(',',$cat_content_info),1).']';
	else
		return '[{"totalcount":"0"}]';
	
}	


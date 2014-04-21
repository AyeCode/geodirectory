<?php
/*-------------------------------------------------*/
/* All map related functions
/*-------------------------------------------------*/

function  geodir_init_map_jason()
{
	global $map_jason;
	$map_jason = array();
}

function geodir_init_map_canvas_array()
{
	global $map_canvas_arr;
	$map_canvas_arr = array();
}

/* Create marker jason for map script */
function create_marker_jason_of_posts($post){
	global $wpdb,$map_jason,$add_post_in_marker_array;
	
	if( ( is_main_query() || $add_post_in_marker_array ) && $post->marker_json != '')
	{	
		$map_jason[] =  $post->marker_json;
	}
}


/* Send jason data to script and show listing map */
function send_marker_jason_to_js(){
	global $map_jason,$map_canvas_arr;
	
	if(is_array($map_canvas_arr) && !empty($map_canvas_arr))
	{
		foreach($map_canvas_arr as $canvas => $jason)
		{
			if(is_array($map_jason) && !empty($map_jason))
			{
			
				$canvas_jason = $canvas."_jason" ;
				$map_canvas_arr[$canvas] =  array_unique($map_jason);
				unset($cat_content_info) ;
				$cat_content_info[]= implode(',',$map_canvas_arr[$canvas]); 
				$totalcount = count( array_unique($map_jason) );
				if(!empty($cat_content_info))	
					$canvas_jason =  '[{"totalcount":"'.$totalcount.'",'.substr(implode(',',$cat_content_info),1).']'; 
				else
					$canvas_jason = '[{"totalcount":"0"}]';
				
				$map_canvas_jason_args = array($canvas.'_jason'=>$canvas_jason );
				$map_canvas_jason_args = apply_filters('geodir_map_canvas_jason_'. $canvas  ,$map_canvas_jason_args ) ;
				
				wp_localize_script( 'geodir-map-widget', $canvas.'_jason_args', $map_canvas_jason_args);
			}
			else
			{
				$canvas_jason = '[{"totalcount":"0"}]';
				$map_canvas_jason_args = array($canvas.'_jason'=>$canvas_jason );
				$map_canvas_jason_args = apply_filters('geodir_map_canvas_jason_' . $canvas  ,$map_canvas_jason_args ) ;
				wp_localize_script( 'geodir-map-widget', $canvas.'_jason_args', $map_canvas_jason_args);
			}
		}
		
	}
}

/* Home map Taxonomy walker */
function home_map_taxonomy_walker($cat_taxonomy, $cat_parent = 0,$hide_empty = true,$pading = 0 , $map_canvas_name ='',$child_collapse)
{
	global $cat_count;
	$exclude_categories = get_option('geodir_exclude_cat_on_map');;
	$exclude_cat_str = implode(',' , $exclude_categories );
	if($exclude_cat_str =='')
		$exclude_cat_str='0' ;
	$cat_terms = get_terms($cat_taxonomy, array('parent' => $cat_parent,'exclude'=> $exclude_cat_str , 'hide_empty ' => $hide_empty));
	
		
	$main_list_class = '';
	//exit();
	//If there are terms, start displaying
	if(count($cat_terms) > 0)
	{
		//Displaying as a list
		$p = $pading * 15; $pading++;
		
		if($cat_parent == 0)
		{	$list_class = 'main_list'; 	
			$display = '';
		}	
		else
		{	$list_class = 'sub_list';	
			if(!$child_collapse)
				$display = '';
			else
				$display = 'display:none';
		}
		
		$exclude_categories = get_option('geodir_exclude_cat_on_map');
		$out = '<ul class="treeview '.$list_class.'" style="margin-left:'.$p.'px;'.$display.';">';
		foreach ($cat_terms as $cat_term):
			
			//Get Term icon
			$term_icon_url = get_tax_meta($cat_term->term_id, 'ct_cat_icon');
			if($term_icon_url){$icon =  $term_icon_url['src'];}
			else{$icon = geodir_plugin_url().'/geodirectory-functions/map-functions/icons/pin.png';}
			if(!in_array($cat_term->term_id,$exclude_categories)):
				//Secret sauce.  Function calls itself to display child elements, if any
				$term_check = '<input type="checkbox" checked="checked" class="group_selector '.$main_list_class.'"'; 
				$term_check .= ' name="'.$map_canvas_name.'_cat[]" group="catgroup'.$cat_term->term_id.'"'; 
				$term_check .= ' alt="'.$cat_term->taxonomy.'" title="'.ucfirst($cat_term->name).'" value="'.$cat_term->term_id.'" " onclick="javascript:build_map_ajax_search_param(\''.$map_canvas_name.'\',false)">';
				$term_check .= '<img height="15" width="15" alt="" src="'.$icon.'"/>';
				$out .= '<li>'.$term_check.'<label>'.ucfirst($cat_term->name).'</label>'; 
			endif;
			
			
		
				// get sub category by recurson
				$out .=  home_map_taxonomy_walker($cat_taxonomy, $cat_term->term_id,$hide_empty,$pading, $map_canvas_name, $child_collapse);
		
			$out .= '</li>'; 
				
		endforeach;
		
		$out .= '</ul>'; 
		
		return $out;
	}
	else
	{
		if($cat_parent==0)
			return _e('No category',GEODIRECTORY_TEXTDOMAIN) ;
	}
	return ;	
}




?>
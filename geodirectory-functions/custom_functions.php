<?php 

function geodir_post_package_info($package_info, $post='', $post_type = '')
{
	$package_info['pid'] = 0;
	$package_info['days'] = 0 ;
	$package_info['amount'] = 0 ;
	$package_info['is_featured'] = 0 ;
	$package_info['image_limit'] ='';
	$package_info['google_analytics'] = 0 ;
	$package_info['sendtofriend'] =1;
	
	return (object)apply_filters('geodir_post_package_info' , $package_info, $post, $post_type);
	
}


function geodir_send_inquiry($request){
	global $wpdb;
	$yourname = $request['inq_name'];
	$youremail = $request['inq_email'];
	$inq_phone = $request['inq_phone'];
	$frnd_comments = $request['inq_msg'];
	$pid = $request['pid'];
	
	$author_id = '';
	$post_title = '';
		
	if($request['pid'])
	{
		
		$productinfosql = $wpdb->prepare(
												"select ID,post_author,post_title from $wpdb->posts where ID =%d", 
												array($request['pid'])
											);
		$productinfo = $wpdb->get_row($productinfosql);
		
		$author_id = $productinfo->post_author;
		$post_title = $productinfo->post_title;
	}
	
	$post_title = '<a href="'.get_permalink($pid).'">'.$post_title.'</a>'; 
			
	$user_info = get_userdata($author_id);
	$to_email = geodir_get_post_meta($pid,'geodir_email',true);
	$to_name = $user_info->first_name;
	
	if($to_email=='')
	{
		$to_email = get_option('admin_email');	
	}
	
	do_action('geodir_after_send_enquiry', $request, 'Enquiry');
	
	$client_message = $frnd_comments;
	$client_message .= '<br>'.__('From :',GEODIRECTORY_TEXTDOMAIN).' '.$yourname.'<br>'.__('Phone :',GEODIRECTORY_TEXTDOMAIN).' '.$inq_phone.'<br><br>'. __('Send from',GEODIRECTORY_TEXTDOMAIN).' - <b><a href="'.get_option('siteurl').'">'.get_option('blogname').'</a></b>.';

	if($to_email)
	{	
		geodir_sendEmail($youremail,$yourname,$to_email,$to_name,'',$client_message,$extra='','send_enquiry',$request['pid']);//To client email
	}
	
	$url = get_permalink($pid);
	if(strstr($url,'?'))
	  {
		  $url = $url."&send_inquiry=success";
	  }else
	  {
			$url = $url."?send_inquiry=success";			  
	  }
	wp_redirect($url);
	exit;

}

function geodir_send_friend($request){

	global $wpdb;
	
	$yourname = $request['yourname'];
	$youremail = $request['youremail'];
	$frnd_subject = $request['frnd_subject'];
	$frnd_comments = $request['frnd_comments'];
	$pid = $request['pid'];
	$to_email = $request['to_email'];
	$to_name = $request['to_name'];
	if($request['pid'])
	{
		$productinfosql = $wpdb->prepare(
												"select ID,post_title from $wpdb->posts where ID =%d", 
												array($request['pid'])
											);
		$productinfo = $wpdb->get_results($productinfosql);
		foreach($productinfo as $productinfoObj)
		{
			$post_title = $productinfoObj->post_title; 
		}
	}
	
	geodir_sendEmail($youremail,$yourname,$to_email,$to_name,$frnd_subject,$frnd_comments,$extra='','send_friend',$request['pid']);//To client email
		
	$url = get_permalink($pid);
	if(strstr($url,'?'))
	  {
		  $url = $url."&sendtofrnd=success";
	  }else
	  {
			$url = $url."?sendtofrnd=success";			  
	  }
	wp_redirect($url);
	exit;
}

function geodir_before_tab_content($hash_key)
{
	switch($hash_key)
	{
		case 'post_info' :
			echo '<div class="geodir-company_info field-group">' ;
			break;
		case 'post_images' :
			echo ' <div id="geodir-post-gallery" class="clearfix" >' ;
			break;
		case 'reviews' :
			echo '<div id="reviews-wrap" class="clearfix"> ' ;
			break;
		case 'post_video':
			echo ' <div id="post_video-wrap" class="clearfix">';
			break;
		case 'special_offers':
			echo '<div id="special_offers-wrap" class="clearfix">' ;
			break;
	}
}

function geodir_after_tab_content($hash_key)
{
	switch($hash_key)
	{
		case 'post_info' :
			echo '</div>' ;
			break;
		case 'post_images' :
			echo '</div>' ;
			break;
		case 'reviews' :
			echo '</div>' ;
			break;
		case 'post_video':
			echo '</div>';
			break;
		case 'special_offers':
			echo '</div>' ;
			break;
	}
}


function geodir_get_posts_default_sort($post_type){
	
	global $wpdb;
	
	if($post_type != ''){
	
		$all_postypes = geodir_get_posttypes();
	
		if(!in_array($post_type, $all_postypes))
			return false;
		
		$sort_field_info =	$wpdb->get_var($wpdb->prepare("select default_order from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where	post_type= %s and is_active=%d and is_default=%d",array($post_type, 1, 1)));
		
		if(!empty($sort_field_info))
			return $sort_field_info;
		
	}

}


function geodir_get_sort_options($post_type){
	global $wpdb;
	
	if($post_type != ''){
	
		$all_postypes = geodir_get_posttypes();
	
		if(!in_array($post_type, $all_postypes))
			return false;
			
		
		$sort_field_info =	$wpdb->get_results($wpdb->prepare("select * from ".GEODIR_CUSTOM_SORT_FIELDS_TABLE." where	post_type= %s and is_active=%d and (sort_asc=1 ||	sort_desc=1 || field_type='random') order by sort_order asc",array($post_type, 1)));
		
		return $sort_field_info;
	}

}


function geodir_display_sort_options(){
	
	global $wp_query;
	
	$sort_by = '';
		
	if(isset($_REQUEST['sort_by'])) $sort_by = $_REQUEST['sort_by'];
	
	$gd_post_type = geodir_get_current_posttype();
	
	$sort_options = geodir_get_sort_options($gd_post_type);
	
	
	$sort_field_options = '';
			
	if(!empty($sort_options)){
		foreach($sort_options as $sort) { 
			
			$label = $sort->site_title;
			
			if($sort->field_type == 'random'){
				$key = $sort->field_type;
				($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by'])) ) ? $selected = 'selected="selected"' :  $selected = '';	
				$sort_field_options .= '<option '.$selected.' value="'.add_query_arg( 'sort_by', $key ).'">'.$label.'</option>';
			}
			
			if($sort->sort_asc){
				 $key = $sort->htmlvar_name.'_asc';
				 $label = $sort->site_title;
				 if($sort->asc_title)
					$label = $sort->asc_title;
				 ($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by']))) ? $selected = 'selected="selected"' :  $selected = '';	
				 $sort_field_options .= '<option '.$selected.' value="'.add_query_arg( 'sort_by', $key ).'">'.$label.'</option>';
			}
			
			if($sort->sort_desc){
				$key = $sort->htmlvar_name.'_desc';
				$label = $sort->site_title;
				if($sort->desc_title)
					$label = $sort->desc_title;
				($sort_by == $key || ($sort->is_default == '1' && !isset($_REQUEST['sort_by']))) ? $selected = 'selected="selected"' :  $selected = '';	
				$sort_field_options .= '<option '.$selected.' value="'.add_query_arg( 'sort_by', $key ).'">'.$label.'</option>';
			}
			
		}
	}
	
	if($sort_field_options != ''){
		
		?>
		
		<div class="geodir-tax-sort">
		
			<select name="sort_by" id="sort_by" onchange="javascript:window.location=this.value;">
			
				<option value="<?php echo add_query_arg( 'sort_by', '' );?>" <?php if($sort_by == '') echo 'selected="selected"';?>><?php _e('Sort By',GEODIRECTORY_TEXTDOMAIN);?></option><?php 
			
				echo $sort_field_options;?>
			
			</select>
		
		</div>
		<?php
	
	}

}


function geodir_advance_customfields_heading($title, $field_type){
	
	if(in_array($field_type,array('multiselect','textarea', 'taxonomy'))){
		$title = '';
	}
	return $title;
}


function geodir_related_posts_display($request){
	
	if(!empty($request)){
		
		$title =( isset($request['title']) && !empty($request['title'])) ? $request['title'] : __('Related Listing',GEODIRECTORY_TEXTDOMAIN);
		$post_number =(isset($request['post_number']) && !empty($request['post_number'])) ? $request['post_number'] : '5' ;
		$relate_to = (isset($request['relate_to']) && !empty($request['relate_to'])) ? $request['relate_to'] : 'category';
		$layout = (isset($request['layout']) && !empty($request['layout'])) ? $request['layout'] : 'gridview_onehalf';
		$add_location_filter = (isset($request['add_location_filter']) && !empty($request['add_location_filter'])) ? $request['add_location_filter'] : '0';
		$listing_width = (isset($request['listing_width']) && !empty($request['listing_width'])) ? $request['listing_width'] : '';
		$list_sort = (isset($request['list_sort']) && !empty($request['list_sort'])) ? $request['list_sort'] : 'latest';
		$character_count = (isset($request['character_count']) && !empty($request['character_count'])) ? $request['character_count'] : 20;
		
		global $wpdb,$post;
		
		$post_type = '';
		$post_id = '';
		$category_taxonomy = '';
		$tax_field = 'id';
		$category = array();
		
		if(isset($_REQUEST['backandedit'])){
			$post = (object)unserialize($_SESSION['listing']);
			$post_type = $post->listing_type;	
			if(isset($_REQUEST['pid']) && $_REQUEST['pid'] != '')
				$post_id = $_REQUEST['pid'];
		}elseif(isset($_REQUEST['pid']) && $_REQUEST['pid'] != ''){
			$post = geodir_get_post_info($_REQUEST['pid']);
			$post_type = $post->post_type;
			$post_id = $_REQUEST['pid'];
		}elseif(isset($post->post_type) && $post->post_type != ''){
			$post_type = $post->post_type;
			$post_id = $post->ID;
		}
		
		if($relate_to == 'category'){
		
			$category_taxonomy = $post_type.$relate_to;
			if($post->$category_taxonomy!= '')
				$category = explode(',',trim($post->$category_taxonomy, ','));
				
		}elseif($relate_to == 'tags'){
		
			$category_taxonomy = $post_type.'_'.$relate_to;
			if($post->post_tags!= '')
				$category = explode(',',trim($post->post_tags, ','));
			$tax_field = 'name';
		}
		
		/* --- return false in invalid request --- */
		if(empty($category))
			return false;
		
		$all_postypes = geodir_get_posttypes();
	
		if(!in_array($post_type, $all_postypes))
			return false;
		
		/* --- return false in invalid request --- */
			
		$location_url = '';
		if($add_location_filter != '0'){
			$location_url = array();
			if( get_query_var('gd_city') ){
				
				if(get_option('geodir_show_location_url') == 'all'){
					if($country = get_query_var('gd_country'))
						$location_url[] = $country;
					
					if($region = get_query_var('gd_region'))
						$location_url[] = $region;
				}		
				
				if($city = get_query_var('gd_city'))
					$location_url[] = $city;
				
			}else{
			
				$location = geodir_get_default_location();
				
				if(get_option('geodir_show_location_url') == 'all'){
					$location_url[] = isset($location->country_slug) ? $location->country_slug : '';
					$location_url[] = isset($location->region_slug) ? $location->region_slug : '';
				}
				$location_url[] = isset($location->city_slug) ? $location->city_slug : '';
			}
			
			$location_url = implode("/",$location_url);
			
		}
		
		
		if(!empty($category)){
			global $geodir_add_location_url;
			$geodir_add_location_url = '0';
			if($add_location_filter != '0'){
				$geodir_add_location_url = '1';
			}
			$viewall_url = get_term_link( (int)$category[0], $post_type.$category_taxonomy);
			$geodir_add_location_url = NULL;
		}
		
		ob_start();
		?>
		
		
				<div class="geodir_locations geodir_location_listing">
   						
							<?php if(isset($request['is_widget']) && $request['is_widget'] == '1'){?>
							<div class="locatin_list_heading clearfix">
								<h3><?php echo ucfirst($title);?></h3> 
							</div><?php }
							
					
								$query_args = array(
									'posts_per_page' => $post_number,
									'is_geodir_loop' => true,
									'gd_location' 	 => ($add_location_filter) ? true : false,
									'post_type' => $post_type,
									'order_by' =>$list_sort,
									'post__not_in'   => array($post_id),
									'excerpt_length' => $character_count,
									);
									
									$tax_query = array( 'taxonomy' => $category_taxonomy,
														'field' => $tax_field,
														'terms' => $category
														);
									
									$query_args['tax_query'] = array( $tax_query );
								
								
								global $gridview_columns;
							
								query_posts( $query_args );
								
								if(strstr($layout,'gridview')){
									
									$listing_view_exp = explode('_',$layout);
									
									$gridview_columns = $layout;
									
									$layout = $listing_view_exp[0];
									
								}
								
								if($layout == 'gridview'){
									
									$template = apply_filters( "geodir_template_part-related-listing-gridview", geodir_plugin_path() . '/geodirectory-templates/listing-gridview.php' );
								
								}else{
									
									$template = apply_filters( "geodir_template_part-related-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
								}
								
								include( $template );
							  
								wp_reset_query();
							 ?>
						   
						</div>						
		<?php
		return $html = ob_get_clean();
		
	}

}
//-----count post according term --------
add_action('wp_footer','geodir_category_count_script',10);
function geodir_category_count_script()
{
	global $geodir_post_category_str;
	
	if(!empty($geodir_post_category_str)){
		$geodir_post_category_str = serialize($geodir_post_category_str);
	}
	
	$all_var['post_category_array']  = html_entity_decode( (string) $geodir_post_category_str, ENT_QUOTES, 'UTF-8');
	$script = "var post_category_array = ".json_encode($all_var).';';
	echo '<script>';
		echo $script ;	
	echo '</script>';

}

function geodir_get_map_default_language()
{
	$geodir_default_map_language = get_option('geodir_default_map_language');
	if(empty($geodir_default_map_language))
		$geodir_default_map_language ='en' ;
	return apply_filters('geodir_default_map_language' , $geodir_default_map_language);
}


function geodir_add_meta_keywords()
{
	global $post,$wp_query;
	
	$current_term = $wp_query->get_queried_object();
	
	$all_postypes = geodir_get_posttypes();
	
	?>
		<meta name="description" content="<?php if (have_posts() && is_single() OR is_page()){while(have_posts()){the_post();
					if(has_excerpt()){$out_excerpt = str_replace(array("\r\n", "\r", "\n"), "", get_the_excerpt());}
					else{$out_excerpt = str_replace(array("\r\n", "\r", "\n"), "", substr($post->post_content,0,160));}
					echo strip_tags($out_excerpt);
				}
			}
			elseif(is_category() || is_tag()){
				if(is_category()){
					echo __("Posts related to Category:", GEODIRECTORY_TEXTDOMAIN)." ".ucfirst(single_cat_title("", FALSE));
				}elseif(is_tag()){ 
					echo __("Posts related to Tag:", GEODIRECTORY_TEXTDOMAIN)." ".ucfirst(single_tag_title("", FALSE));
				}
			}
			elseif(isset($current_term->taxonomy)){
				echo isset($current_term->description) ? $current_term->description : '';
			}
			 ?>" />
		
		<meta name="keywords" content="<?php 
		if(isset($post->post_type) && in_array($post->post_type, $all_postypes)){
		
			$place_tags = wp_get_post_terms($post->ID, $post->post_type.'_tags', array("fields" => "names"));
			$place_cats = wp_get_post_terms($post->ID, $post->post_type.'category', array("fields" => "names"));	
			echo implode(", ", array_merge((array)$place_cats, (array)$place_tags));	
		
		}else{
		
			$posttags = get_the_tags();
			
			if ($posttags) {
				foreach($posttags as $tag) {
					echo $tag->name . ' '; 
				}
				
			}else{
			
				$tags = get_tags(array('orderby' => 'count', 'order' => 'DESC'));
				
				$xt = 1;
					foreach ($tags as $tag) {
						if ($xt <= 20) {
							echo $tag->name.", ";
						}
						$xt++;
					}
			}
		}
		?>" />
		<?php
}

/* =================================== start code for detail page tabs */


function geodir_detail_page_tabs_key_value_array()
{
	$geodir_detail_page_tabs_key_value_array = array();
	
	$geodir_detail_page_tabs_array = geodir_detail_page_tabs_array();
	
	foreach($geodir_detail_page_tabs_array as $key => $tabs_obj)
	{
		$geodir_detail_page_tabs_key_value_array[$key] = $tabs_obj['heading_text'];
	}
	return 	$geodir_detail_page_tabs_key_value_array;
}
/**/

function geodir_detail_page_tabs_array(){

		$arr_tabs = array();
		$arr_tabs['post_profile'] =	array( 
																'heading_text' =>  __('Profile',GEODIRECTORY_TEXTDOMAIN),
																'is_active_tab' => true,
																'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'post_profile'),
																'tab_content' => ''
																);
		$arr_tabs['post_info'] = 		array( 
																'heading_text' =>  __('More Info',GEODIRECTORY_TEXTDOMAIN),
																'is_active_tab' => false,
																'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'post_info'),
																'tab_content' => ''
																);

		$arr_tabs['post_images'] = 	array( 
																'heading_text' =>  __('Photo',GEODIRECTORY_TEXTDOMAIN),
																'is_active_tab' => false,
																'is_display' => apply_filters('geodir_detail_page_tab_is_display', true, 'post_images'),
																'tab_content' => ''
																);

		$arr_tabs['post_video'] =	array( 
															'heading_text' =>  __('Video',GEODIRECTORY_TEXTDOMAIN),
															'is_active_tab' => false,
															'is_display' =>  apply_filters('geodir_detail_page_tab_is_display', true, 'post_video'),
															'tab_content' => ''
															);

		$arr_tabs['special_offers'] = array( 
															'heading_text' =>  __('Special Offers',GEODIRECTORY_TEXTDOMAIN),
															'is_active_tab' => false,
															'is_display' =>  apply_filters('geodir_detail_page_tab_is_display', true, 'special_offers'),
															'tab_content' => ''
															);

		$arr_tabs['post_map'] = 	array(
														'heading_text' =>  __('Map',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' =>  apply_filters('geodir_detail_page_tab_is_display', true, 'post_map'),
														'tab_content' => ''
														);
	
		$arr_tabs['reviews'] = 	array( 
														'heading_text' =>  __('Reviews',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' =>  apply_filters('geodir_detail_page_tab_is_display', true, 'reviews'),
														'tab_content' => 'review display'
														);
		
		$arr_tabs['related_listing'] = array( 
														'heading_text' =>  __('Related Listing',GEODIRECTORY_TEXTDOMAIN),
														'is_active_tab' => false,
														'is_display' =>  apply_filters('geodir_detail_page_tab_is_display', true, 'related_listing'),
														'tab_content' => ''
														);
	
	return apply_filters('geodir_detail_page_tab_list_extend' ,$arr_tabs )	;
	
	
}


function geodir_detail_page_tabs_list(){
	
	
	$tabs_excluded  = get_option('geodir_detail_page_tabs_excluded');
	$tabs_array = geodir_detail_page_tabs_array();
	if(!empty($tabs_excluded)){
			foreach($tabs_excluded as $tab)
			{
				if(array_key_exists($tab, $tabs_array))
					unset($tabs_array[$tab]);
			}
	}
	return $tabs_array ;

}



function geodir_show_detail_page_tabs(){
	
	global $post,$post_images,$video,$special_offers, $related_listing,$geodir_post_detail_fields;
	
	$geodir_post_detail_fields = geodir_show_listing_info('detail');
	
	if(geodir_is_page('detail')){
	
		$video = geodir_get_video($post->ID);
		$special_offers = geodir_get_special_offers($post->ID);
		$related_listing_array = array();
		if(get_option('geodir_add_related_listing_posttypes'))
			$related_listing_array = get_option('geodir_add_related_listing_posttypes');
		
		$related_listing = '';
		if(in_array($post->post_type, $related_listing_array))
		{	
			$request = array('post_number'=>get_option('geodir_related_post_count'),
								'relate_to'=>get_option('geodir_related_post_relate_to'),
								'layout'=>get_option('geodir_related_post_listing_view'),
								'add_location_filter'=>get_option('geodir_related_post_location_filter'),
								'list_sort'=>get_option('geodir_related_post_sortby'),
								'character_count'=>get_option('geodir_related_post_excerpt'));
						
						$related_listing = geodir_related_posts_display($request);
		}
		
		$post_images = geodir_get_images($post->ID,'thumbnail');
		$thumb_image = '';
		if(!empty($post_images)){
			foreach($post_images as $image){
				$thumb_image .=	'<a href="'.$image->src.'">';
				$thumb_image .= geodir_show_image($image,'thumbnail',true,false);
				$thumb_image .= '</a>';
			}
		}
		
		$map_args = array();
		$map_args['map_canvas_name'] = 'detail_page_map_canvas' ;
		$map_args['width'] = '600';
		$map_args['height'] = '300';
		if($post->post_mapzoom){$map_args['zoom'] = ''.$post->post_mapzoom.'';}
		$map_args['autozoom'] = false;
		$map_args['child_collapse'] = '0';
		$map_args['enable_cat_filters'] = false;
		$map_args['enable_text_search'] = false;
		$map_args['enable_post_type_filters'] = false;
		$map_args['enable_location_filters'] = false;
		$map_args['enable_jason_on_load'] = true;
		$map_args['enable_map_direction'] = true;
		
	}elseif(geodir_is_page('preview')){
		
		$video = isset($post->geodir_video) ? $post->geodir_video : '';
		$special_offers = isset($post->geodir_special_offers) ? $post->geodir_special_offers : '';
		
		if(isset($post->post_images))
		$post->post_images = trim($post->post_images,",");
		
		if(isset($post->post_images) && !empty($post->post_images))
			$post_images = explode(",",$post->post_images);
		
		$thumb_image = '';
		if(!empty($post_images)){
			foreach($post_images as $image){
				if($image != ''){	   
					$thumb_image .=	'<a href="'.$image.'">';
					$thumb_image .= geodir_show_image(array('src'=>$image),'thumbnail',true,false);
					$thumb_image .= '</a>';	
				}
			}
		}
		
		global $map_jason ;
		$map_jason[] = $post->marker_json;
		
		$address_latitude = isset($post->post_latitude) ? $post->post_latitude : '';
		$address_longitude = isset($post->post_longitude) ? $post->post_longitude : '';
		$mapview = isset($post->post_mapview) ? $post->post_mapview : '';
		$mapzoom = isset($post->post_mapzoom) ? $post->post_mapzoom : '';
		if(!$mapzoom){$mapzoom=12;}
		
		$map_args = array();
		$map_args['map_canvas_name'] = 'preview_map_canvas' ;
		$map_args['width'] = '950';
		$map_args['height'] = '300';
		$map_args['child_collapse'] = '0';
		$map_args['maptype'] = $mapview;
		$map_args['autozoom'] =  false;
		$map_args['zoom'] =  "$mapzoom";
		$map_args['latitude'] = $address_latitude;
		$map_args['longitude'] = $address_longitude;
		$map_args['enable_cat_filters'] = false;
		$map_args['enable_text_search'] = false;
		$map_args['enable_post_type_filters'] = false;
		$map_args['enable_location_filters'] = false;
		$map_args['enable_jason_on_load'] = true;
		$map_args['enable_map_direction'] = true;

	}
	
	?>
	
	<div class="geodir-tabs" id="gd-tabs" style="position:relative;">
                   <dl class="geodir-tab-head">
                   <?php do_action('geodir_before_tab_list') ; ?>
                   <?php 
				   		$arr_detail_page_tabs = geodir_detail_page_tabs_list();
						foreach($arr_detail_page_tabs as $tab_index => $detail_page_tab)
						{
							if($detail_page_tab['is_display'])
							{
						?>	<dt></dt> <!-- added to comply with validation -->
                            <dd <?php if($detail_page_tab['is_active_tab']){?>class="geodir-tab-active"<?php }?> >
                                <a data-tab="#<?php echo $tab_index;?>" data-status="enable"><?php echo $detail_page_tab['heading_text'] ;?></a>
                            </dd>
                            
							<?php
                            ob_start() // start tab content buffering 
                            ?>
							 <li id="<?php echo $tab_index;?>Tab" <?php if($tab_index=='post_profile'){echo 'itemprop="description"';}?>>
                             	<div id="<?php echo $tab_index;?>"  class="hash-offset"></div>
                             <?php 
							 	do_action('geodir_before_tab_content' ,$tab_index );
								do_action('geodir_before_' . $tab_index.'_tab_content');
						   		/// write a code to generate content of each tab 
								switch($tab_index){
						   			case 'post_profile':
											do_action('geodir_before_description_on_listing_detail');
											if(geodir_is_page('detail')){ the_content(); }else { echo apply_filters( 'the_content', stripslashes($post->post_desc) ) ;}
											do_action('geodir_after_description_on_listing_detail');
										break;
						  	 	case 'post_info':
								        echo $geodir_post_detail_fields;
                               			break;
								case 'post_images':
								       echo $thumb_image;
									break;
								case 'post_video':
									echo $video; 
									break;
								case 'special_offers':
									echo wpautop(stripslashes($special_offers));
								
                                  	break;
								case 'post_map':
									geodir_draw_map($map_args);
									break;	
								case 'reviews':
									comments_template(); 
									break;
								case 'related_listing':
									echo $related_listing;
									break;		
								default:
									break;
						   	}
							do_action('geodir_after_tab_content' ,$tab_index );
						   	do_action('geodir_after_' . $tab_index.'_tab_content');
							?> </li>
                           <?php 
						  	$arr_detail_page_tabs[$tab_index]['tab_content'] = apply_filters("geodir_modify_" .$detail_page_tab['tab_content']. "_tab_content"  ,  ob_get_clean()) ;
						  } // end of if for is_display
						}// end of foreach
						
						do_action('geodir_after_tab_list') ; 
					 ?>
                    </dl>
                   <ul class="geodir-tabs-content entry-content" style="z-index:-999; position:relative;">
                   		<?php 
						foreach($arr_detail_page_tabs as $detail_page_tab)
						{
							if($detail_page_tab['is_display'] && !empty($detail_page_tab['tab_content']))
							{
								echo $detail_page_tab['tab_content'] ;
                        	}// end of if 
						}// end of foreach 
						 do_action('geodir_add_tab_content') ; ?>
                    </ul> <!--gd-tabs-content ul end-->
               </div>
	
	<?php

}
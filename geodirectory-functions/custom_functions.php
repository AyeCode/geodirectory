<?php 

function geodir_list_view_select(){
?>
<script type="text/javascript">
function geodir_list_view_select(list){
	//alert(listval);
val = list.value;	
if(!val){return;}

//var listSel = jQuery(list).closest('.geodir_category_list_view');
var listSel = jQuery(list).parent().parent().next('.geodir_category_list_view');
if(val!=1){jQuery(listSel).children('li').addClass('geodir-gridview');}

if(val==1){jQuery(listSel).children('li').removeClass('geodir-gridview gridview_onehalf gridview_onethird gridview_onefourth gridview_onefifth');}
else if(val==2){jQuery(listSel).children('li').switchClass('gridview_onethird gridview_onefourth gridview_onefifth','gridview_onehalf',600);}
else if(val==3){jQuery(listSel).children('li').switchClass('gridview_onehalf gridview_onefourth gridview_onefifth','gridview_onethird',600);}
else if(val==4){jQuery(listSel).children('li').switchClass('gridview_onehalf gridview_onethird gridview_onefifth','gridview_onefourth',600);}
else if(val==5){jQuery(listSel).children('li').switchClass('gridview_onehalf gridview_onethird gridview_onefourth','gridview_onefifth',600);}

jQuery.post( "<?php echo geodir_get_ajax_url();?>&gd_listing_view="+val, function( data ) {
  //alert(data );
});

}
</script>
<div class="geodir-list-view-select">
	<select name="gd_list_view" id="gd_list_view" onchange="geodir_list_view_select(this);">
<?php if(isset($_SESSION['gd_listing_view']) && $_SESSION['gd_listing_view']!=''){$sel=$_SESSION['gd_listing_view'];}else{$sel='';}?>			
		<option  value=""><?php _e('View:',GEODIRECTORY_TEXTDOMAIN);?></option>
		<option value="1" <?php if($sel=='1'){echo 'selected="selected"';}?> ><?php _e('View: List',GEODIRECTORY_TEXTDOMAIN);?></option>
		<option value="2" <?php if($sel=='2'){echo 'selected="selected"';}?>><?php _e('View: Grid 2',GEODIRECTORY_TEXTDOMAIN);?></option>
		<option value="3" <?php if($sel=='3'){echo 'selected="selected"';}?>><?php _e('View: Grid 3',GEODIRECTORY_TEXTDOMAIN);?></option>
		<option value="4" <?php if($sel=='4'){echo 'selected="selected"';}?>><?php _e('View: Grid 4',GEODIRECTORY_TEXTDOMAIN);?></option>
		<option value="5" <?php if($sel=='5'){echo 'selected="selected"';}?>><?php _e('View: Grid 5',GEODIRECTORY_TEXTDOMAIN);?></option>
			
	</select>
</div>
<?php	
	
}


//add_action('geodir_before_listing_post_listview', 'geodir_list_view_select');
add_action('geodir_before_listing', 'geodir_list_view_select', 100);


function geodir_max_excerpt($charlength) {
	if($charlength=='0'){return;}
	$excerpt = get_the_excerpt();
	$charlength++;

	if ( mb_strlen( $excerpt ) > $charlength ) {
		$subex = mb_substr( $excerpt, 0, $charlength - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if ( $excut < 0 ) {
			echo mb_substr( $subex, 0, $excut );
		} else {
			echo $subex;
		}
		echo apply_filters('geodir_max_excerpt_end','[...]');
	} else {
		echo $excerpt;
	}
}

function geodir_post_package_info($package_info, $post='', $post_type = '')
{
	$package_info['pid'] = 0;
	$package_info['days'] = 0 ;
	$package_info['amount'] = 0 ;
	$package_info['is_featured'] = 0 ;
	$package_info['image_limit'] ='';
	$package_info['google_analytics'] = 1 ;
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
			echo ' <div id="'. apply_filters('geodir_post_gallery_id' ,'geodir-post-gallery') .'" class="clearfix" >' ;
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
							<div class="location_list_heading clearfix">
								<?php echo $before_title.$title.$after_title;?> 
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
								$related_posts = true;
								$template = apply_filters( "geodir_template_part-related-listing-listview", geodir_plugin_path() . '/geodirectory-templates/listing-listview.php' );
							
								
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
	
	$geodir_taxonomies = geodir_get_taxonomies('',true);

	?>
		<meta name="description" content="<?php if (have_posts() && is_single() OR is_page()){while(have_posts()){the_post();
					if(has_excerpt()){$out_excerpt = str_replace(array("\r\n", "\r", "\n"), "", get_the_excerpt());}
					else{$out_excerpt = str_replace(array("\r\n", "\r", "\n"), "", substr($post->post_content,0,160));}
					echo strip_tags($out_excerpt);
				}
			}
			elseif((is_category() || is_tag()) && isset($current_term->taxonomy) && in_array($current_term->taxonomy,$geodir_taxonomies) ){
				if(is_category()){
					echo __("Posts related to Category:", GEODIRECTORY_TEXTDOMAIN)." ".ucfirst(single_cat_title("", FALSE));
				}elseif(is_tag()){ 
					echo __("Posts related to Tag:", GEODIRECTORY_TEXTDOMAIN)." ".ucfirst(single_tag_title("", FALSE));
				}
			}
			elseif(isset($current_term->taxonomy) && in_array($current_term->taxonomy,$geodir_taxonomies)){
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
									echo apply_filters( 'the_content', stripslashes($video) );// we apply the_content filter so oembed works also; 
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


function geodir_exif($file) {
        //This line reads the EXIF data and passes it into an array
		$file['file']=$file['tmp_name'];
		 if($file['type']=="image/jpg" || $file['type']=="image/jpeg" || $file['type']=="image/pjpeg"){}else{return $file;}
		if(!function_exists('read_exif_data')){return $file;}
        $exif = read_exif_data($file['file']);

        //We're only interested in the orientation
        $exif_orient = isset($exif['Orientation'])?$exif['Orientation']:0;
        $rotateImage = 0;

        //We convert the exif rotation to degrees for further use
        if (6 == $exif_orient) {
            $rotateImage = 90;
            $imageOrientation = 1;
        } elseif (3 == $exif_orient) {
            $rotateImage = 180;
            $imageOrientation = 1;
        } elseif (8 == $exif_orient) {
            $rotateImage = 270;
            $imageOrientation = 1;
        }

        //if the image is rotated
        if ($rotateImage) {

            //WordPress 3.5+ have started using Imagick, if it is available since there is a noticeable difference in quality
            //Why spoil beautiful images by rotating them with GD, if the user has Imagick

            if (class_exists('Imagick')) {
                $imagick = new Imagick();
                $imagick->readImage($file['file']);
                $imagick->rotateImage(new ImagickPixel(), $rotateImage);
                $imagick->setImageOrientation($imageOrientation);
                $imagick->writeImage($file['file']);
                $imagick->clear();
                $imagick->destroy();
            } else {

                //if no Imagick, fallback to GD
                //GD needs negative degrees
                $rotateImage = -$rotateImage;

                switch ($file['type']) {
                    case 'image/jpeg':
                        $source = imagecreatefromjpeg($file['file']);
                        $rotate = imagerotate($source, $rotateImage, 0);
                        imagejpeg($rotate, $file['file']);
                        break;
                    case 'image/png':
                        $source = imagecreatefrompng($file['file']);
                        $rotate = imagerotate($source, $rotateImage, 0);
                        imagepng($rotate, $file['file']);
                        break;
                    case 'image/gif':
                        $source = imagecreatefromgif($file['file']);
                        $rotate = imagerotate($source, $rotateImage, 0);
                        imagegif($rotate, $file['file']);
                        break;
                    default:
                        break;
                }
            }
        }
        // The image orientation is fixed, pass it back for further processing
        return $file;
    }
	
###########################################
############ RECENT REVIEWS ###############
###########################################
function geodir_get_recent_reviews($g_size = 30, $no_comments = 10, $comment_lenth = 60, $show_pass_post = false) {
        global $wpdb, $tablecomments, $tableposts,$rating_table_name;
		$tablecomments = $wpdb->comments;
		$tableposts = $wpdb->posts;
		
		$comments_echo ='';
		
		$review_table = GEODIR_REVIEW_TABLE;
		$request = "SELECT p.ID, r.post_type, c.comment_ID, c.comment_content, c.comment_author, c.comment_date,r.overall_rating, r.user_id, c.comment_author_email,c.comment_author FROM $review_table as r LEFT JOIN $wpdb->comments as c ON r.comment_id=c.comment_ID LEFT JOIN $wpdb->posts as p ON r.post_id=p.ID WHERE p.post_status = 'publish' AND c.comment_parent=0 AND comment_approved = '1' ORDER BY c.comment_date DESC LIMIT $no_comments";
	 	/*echo $request;
		$comments = $wpdb->get_results($request);
		
		print_r($comments);
		return;*/
		
################################### FIX BY STIOFAN HEBTECH.CO.UK TO HIDE BLOG COMMENTS IN REVIEWS #####################################		
		//$request = "SELECT ID, comment_ID, comment_content, comment_author,comment_post_ID, comment_author_email FROM $tableposts, $tablecomments WHERE $tableposts.ID=$tablecomments.comment_post_ID AND post_status = 'publish' ";
		/*$city_id = mysql_real_escape_string ($_SESSION['multi_city']);
		if($_SESSION['multi_city']){$request = "SELECT p.ID, p.post_type, co.comment_ID, co.user_id, co.comment_content, co.comment_author,co.comment_post_ID, co.comment_author_email FROM $tableposts  as p join $tablecomments co on p.ID=co.comment_post_ID join $wpdb->postmeta pm on pm.post_id=p.ID WHERE  p.post_status = 'publish' AND co.comment_parent=0 AND p.post_type IN('place','event') AND pm.meta_key='post_city_id' and pm.meta_value in ($city_id) AND co.comment_approved = '1' ORDER BY co.comment_date DESC LIMIT $no_comments";
		}
		else{$request = "SELECT ID, post_type, comment_ID, comment_content, comment_author,comment_post_ID, user_id, comment_author_email FROM $tableposts, $tablecomments WHERE $tableposts.ID=$tablecomments.comment_post_ID AND post_status = 'publish' AND $tablecomments.comment_parent=0 AND post_type IN('place','event') AND comment_approved = '1' ORDER BY $tablecomments.comment_date DESC LIMIT $no_comments";
		}*/
        $comments = $wpdb->get_results($request);

        foreach ($comments as $comment) {
		$comment_id ='';
		$comment_id = $comment->comment_ID;
		$comment_content = strip_tags($comment->comment_content);
		
		$comment_content = preg_replace('#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_content);
		$comment_excerpt = mb_substr($comment_content, 0, $comment_lenth)."";
		$permalink = get_permalink($comment->ID)."#comment-".$comment->comment_ID;
		$comment_author_email = $comment->comment_author_email;
		$comment_post_ID = $comment->ID;

		$na=true;
		if(function_exists('icl_object_id') && icl_object_id($comment_post_ID, $comment->post_type, true)){
		$comment_post_ID2 = icl_object_id($comment_post_ID, $comment->post_type, false);
		if($comment_post_ID==$comment_post_ID2){}else{$na=false;}
		}
		
		$post_title = get_the_title($comment_post_ID);
		$permalink = get_permalink($comment_post_ID);
		if($comment->user_id){$user_profile_url = get_author_posts_url($comment->user_id);}
		else{$user_profile_url ='';}
		
		if($comment_id && $na){
   $comments_echo .= '<li class="clearfix">';
  $comments_echo .=  "<span class=\"li".$comment_id." geodir_reviewer_image\">";
		if (function_exists('get_avatar')) {
					  if (!isset($comment->comment_type) ) {
						 if($user_profile_url){ $comments_echo .=   '<a href="'.$user_profile_url.'">';}
						 $comments_echo .=  get_avatar($comment->comment_author_email, 60, get_bloginfo('template_directory').'/images/gravatar2.png');
						if($user_profile_url){ $comments_echo .=  '</a>';}
					  } elseif ( (isset($comment->comment_type) && $comment->comment_type == 'trackback') || (isset($comment->comment_type) && $comment->comment_type=='pingback') ) {
					if($user_profile_url){	 $comments_echo .=   '<a href="'.$user_profile_url.'">';}
						 $comments_echo .=  get_avatar($comment->comment_author_url, 60, get_bloginfo('template_directory').'/images/gravatar2.png');
					  }
				   } elseif (function_exists('gravatar')) {
					if($user_profile_url){  $comments_echo .=   '<a href="'.$user_profile_url.'">';}
					  $comments_echo .=  "<img src=\"";
					  if ('' == $comment->comment_type) {
						 $comments_echo .=  gravatar($comment->comment_author_email,60, get_bloginfo('template_directory').'/images/gravatar2.png');
						if($user_profile_url){  $comments_echo .=  '</a>';}
					  } elseif ( ('trackback' == $comment->comment_type) || ('pingback' == $comment->comment_type) ) {
					if($user_profile_url){	$comments_echo .=   '<a href="'.$user_profile_url.'">';}
						$comments_echo .=  gravatar($comment->comment_author_url,60, get_bloginfo('template_directory').'/images/gravatar2.png');
						if($user_profile_url){ $comments_echo .=  '</a>';}
					  }
					 $comments_echo .=  "\" alt=\"\" class=\"avatar\" />";
				   }
    $comments_echo .=  "</span>\n";
	
    $comments_echo .=  '<span class="geodir_reviewer_content">' ;

           //if($comment->user_id){$comments_echo .=   '<a href="'.get_author_posts_url( $comment->user_id ).'">';}
		   
 			$comments_echo .=   '<span class="geodir_reviewer_author">'.$comment->comment_author.'</span> ';
			
			$comments_echo .= '<span class="geodir_reviewer_reviewed">'.__('reviewed',GEODIRECTORY_TEXTDOMAIN).'</span> ';
			//if($comment->user_id){'</a> ';}
			
 			$comments_echo .=   '<a href="'.$permalink.'" class="geodir_reviewer_title">'.$post_title.'</a>';
			$comments_echo .=  geodir_get_rating_stars($comment->overall_rating, $comment_post_ID);
			 
 			//$comments_echo .=  "<a class=\"comment_excerpt\" href=\"" . $permalink . "\" title=\"View the entire comment\">";
			$comments_echo .=  '<span class="geodir_reviewer_text">'.$comment_excerpt.'</span>';
			//echo preg_replace('#(\\[img\\]).+(\\[\\/img\\])#', '', $comment_excerpt);
			//$comments_echo .=  "</a>";
			
	$comments_echo .=  "</span>\n";
			
			$comments_echo .=  '</li>';

	            }
		}

return $comments_echo;
}
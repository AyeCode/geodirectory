<?php  
 
function geodir_session_start(){
	if( !session_id() ) session_start();
	global $geodir_add_location_url;
	
	$geodir_add_location_url = NULL;
	
}

function geodir_modified_query( $query ) {
   if ( $query->is_main_query() && (
   		(geodir_is_page('add-listing') && isset($_REQUEST['pid']) && $_REQUEST['pid']!='' )
		 || geodir_is_page('listing') 
		 || geodir_is_page('author') 
		 || geodir_is_page('search') 
		 || geodir_is_page('detail')  ) 
		 ){
		 
		  $query->set( 'is_geodir_loop', true );
   }
   
   return $query;
}
  
function set_listing_request(){
	
	global $wp_query,$wpdb,$geodir_post_type,$table, $dist, $mylat, $mylon, $s, $snear, $s, $s_A, $s_SA;
	
	if(get_query_var('ignore_sticky_posts')){
		print_r($query);exit;}
	
	/* remove all pre filters */
	remove_all_filters('query');
	remove_all_filters('posts_search');
	remove_all_filters('posts_fields');
	remove_all_filters('posts_join');
	remove_all_filters('posts_orderby');
	remove_all_filters('posts_where');
	
	
	if( (is_page() && get_query_var('page_id') == get_option( 'geodir_listing_page' ) ) ||  ( is_search() && isset($_REQUEST['geodir_search']) && $_REQUEST['geodir_search'] != '' ) ):
		
		if(isset($_REQUEST['scat']) && $_REQUEST['scat'] == 'all') $_REQUEST['scat'] = '';
		//if(isset($_REQUEST['s']) && $_REQUEST['s'] == '+') $_REQUEST['s'] = '';
		
		if(isset($_REQUEST['sdist'])){
			($_REQUEST['sdist'] != '0' && $_REQUEST['sdist'] != '') ? $dist=mysql_real_escape_string($_REQUEST['sdist']) : $dist = 25000;
		}elseif(get_option('gd_search_dist')!=''){$dist = get_option('gd_search_dist');
		}else{$dist = 25000;} //  Distance
		
		if(isset($_REQUEST['sgeo_lat'])){$mylat=(float)mysql_real_escape_string($_REQUEST['sgeo_lat']);}
		else{$mylat= (float)geodir_get_current_city_lat();} //  Latatude
		
		if(isset($_REQUEST['sgeo_lon'])){$mylon=(float)mysql_real_escape_string($_REQUEST['sgeo_lon']);}
		else{$mylon= (float)geodir_get_current_city_lng();} //  Distance 
		
		if(isset($_REQUEST['snear'])){$snear = mysql_real_escape_string(trim($_REQUEST['snear']));}
		
		if(isset($_REQUEST['s'])){$s = mysql_real_escape_string(trim($_REQUEST['s']));}
		
		if($snear == 'NEAR ME'){
			$ip = $_SERVER['REMOTE_ADDR'];
			$addr_details = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip));
			$mylat = stripslashes(ucfirst($addr_details[geoplugin_latitude]));
			$mylon = stripslashes(ucfirst($addr_details[geoplugin_longitude]));
		}
		
		
		if(strstr($s,',')){$s_AA = str_replace(" ", "", $s); $s_A = explode(",", $s_AA); $s_A = implode('","', $s_A); $s_A = '"'.$s_A.'"';}else{$s_A = '"'.$s.'"';}
		
		if(strstr($s,' ')){$s_SA = explode(" ", $s); }else{$s_SA = '';}
		
	endif; 
	
	/* ===old code start ===
	if($wp_query->is_main_query() || get_query_var('gd_location')):
	
	$url_separator = get_option('geodir_listingurl_separator');
	
	//Set Location 
	
		
	if( geodir_is_page('location') || ( geodir_is_page('search') && isset($_REQUEST['gd_location']) ) ){
			
		if( geodir_is_page('search') ){
			$search_location_request =  explode(",",urldecode($_REQUEST['gd_location']));
			@list($gd_country, $gd_region, $gd_city) = $search_location_request;
		}else{
			$gd_country = get_query_var('gd_country');    
			$gd_region = get_query_var('gd_region');
			$gd_city = get_query_var('gd_city');
		}
		
		$_SESSION['gd_country'] = $gd_country;
		$_SESSION['gd_region'] = $gd_region;
		$_SESSION['gd_city'] = $gd_city;
		
			
	}else{
		$request_term = '';
		$location_request = array();
		
		if(isset($wp_query->tax_query->queries) && $wp_query->tax_query->queries){
			$taxonomies = wp_list_pluck( $wp_query->tax_query->queries, 'taxonomy' );
			$request_term = isset($wp_query->query[$taxonomies[0]]) ? $wp_query->query[$taxonomies[0]] : '';
			
		}
		
		if ( get_option('permalink_structure') != '' ){
			
			if( strpos($request_term,'/'.$url_separator.'/') ){
					$location_request = explode('/'.$url_separator.'/',$request_term);
					$location_request = explode("/",$location_request[0]);
					
			}elseif( isset($taxonomies) && !term_exists( $request_term, $taxonomies[0] ) ){
				// here i have to check if location plugin installed or not 
				// if the location plugin is not installed then dont set location parameter
				global $geodir_addon_list;
				if(!empty($geodir_addon_list) && array_key_exists('geodir_location_manager', $geodir_addon_list) && $geodir_addon_list['geodir_location_manager'] == 'yes') {
					$location_request = explode("/",$request_term);
				}
			}
			
		
		}else{
			if(isset($_REQUEST['gd_country']))
				$location_request[] = $_REQUEST['gd_country'];
			if(isset($_REQUEST['gd_region']))
				$location_request[] = $_REQUEST['gd_region'];
			if(isset($_REQUEST['gd_city']))
				$location_request[] = $_REQUEST['gd_city'];
		}
		
		
		if(!empty($location_request)){
			if(get_option('geodir_show_location_url') == 'all'){
				@list($gd_country, $gd_region, $gd_city) = $location_request;
			}else{
				$gd_city = end($location_request);
			}	
			
			if($gd_city != '' || $gd_country != ''){
			unset(	$_SESSION['gd_city'],
					$_SESSION['gd_region'],
					$_SESSION['gd_country'] );
			}		
		}
	}
	unset(	$_SESSION['gd_multi_location'],
				$_SESSION['gd_city'],
				$_SESSION['gd_region'],
				$_SESSION['gd_country'] );
	
	if(get_option('geodir_show_location_url') == 'all'){
		
		if(isset($gd_country) && $gd_country != '' ){
			$wp_query->set('gd_country',$gd_country);
			$_SESSION['gd_country'] = $gd_country;
		}elseif( isset($_SESSION['gd_country']) && $_SESSION['gd_country'] != '' ){
			$wp_query->set('gd_country',$_SESSION['gd_country']);
		}
		
		if( isset($gd_region) && $gd_region != '' ){
			$wp_query->set('gd_region',$gd_region);
			$_SESSION['gd_region'] = $gd_region;
		}elseif( isset($_SESSION['gd_region']) && $_SESSION['gd_region'] != '' ){
			$wp_query->set('gd_region',$_SESSION['gd_region']);
		}
	}
	
	if( isset($gd_city) && $gd_city != '' ){
		$wp_query->set('gd_city',$gd_city);
		$_SESSION['gd_city'] = $gd_city;
	}elseif( isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != '' ){
			$wp_query->set('gd_city',$_SESSION['gd_city']);
	}
	
	if( isset($_REQUEST['neighbourhood']) && $_REQUEST['neighbourhood'] != '' ){
		$wp_query->set('gd_neighbourhood',$_REQUEST['neighbourhood']);
	}
	

	if((isset($_SESSION['gd_country']) && $_SESSION['gd_country']!='') || (isset($_SESSION['gd_region']) && $_SESSION['gd_region'] !='') || (isset($_SESSION['gd_city']) && $_SESSION['gd_city'] != ''))
			$_SESSION['gd_multi_location'] = true ;
	else{
		
		if(isset($_SESSION['gd_multi_location']))
		unset(	$_SESSION['gd_multi_location'],
				$_SESSION['gd_city'],
				$_SESSION['gd_region'],
				$_SESSION['gd_country'] );
	}
	
	endif; // End if Set Location 
	/* ===old code end ===*/
	
}



/* ====== Place Listing Geodir loop filters ===== */

function geodir_listing_loop_filter($query){
	
	global $wp_query,$geodir_post_type,$table,$plugin_prefix,$table,$term;
	//echo "<pre >";
	//print_r($wp_query) ;
	$geodir_post_type = geodir_get_current_posttype();
	
	if(isset($wp_query->tax_query->queries) && $wp_query->tax_query->queries){
		$taxonomies = wp_list_pluck( $wp_query->tax_query->queries, 'taxonomy' );
		
		if(isset($wp_query->query[$taxonomies[0]])){
			$request_term = explode("/",$wp_query->query[$taxonomies[0]]);
			$request_term = end($request_term);
			if(!term_exists($request_term ))
			{	if(!is_array($term)){$term = array();}
				$term['name']=ucwords(str_replace('-' , ' ' ,$request_term))  ;
				$term['taxonomy']=$taxonomies[0] ;
				
				$wp_query->queried_object =  (object)$term;
			}
		}
		
	}	
	if ( isset($query->query_vars['is_geodir_loop']) && $query->query_vars['is_geodir_loop']) {
		
		$table = $plugin_prefix . $geodir_post_type . '_detail';
			
		add_filter('posts_fields', 'geodir_posts_fields' ,1 );
		add_filter('posts_join', 'geodir_posts_join',1);
		geodir_post_where();
		if(!is_admin())
			add_filter('posts_orderby', 'geodir_posts_orderby' ,1 );
		
	}
	
	return $query;
}


/*
* Listing fields filter *
*/
function geodir_posts_fields($fields){

	global $wp_query, $wpdb,$geodir_post_type,$table, $plugin_prefix, $dist, $mylat, $mylon, $snear;
		
		//Filter-Location-Manager to add location table.		
		
		//$fields .= ", ".$table.".*".", ".POST_LOCATION_TABLE.".* ";//===old code
		$fields .= ", ".$table.".* ";
		if($snear!=''){
			$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
			/*$lon1 = $mylon-$dist/abs(cos(deg2rad($mylat))*69); 
			$lon2 = $mylon+$dist/abs(cos(deg2rad($mylat))*69);
			$lat1 = $mylat-($dist/69);
			$lat2 = $mylat+($dist/69);*/
			
			$fields .= " , (".$DistanceRadius." * 2 * ASIN(SQRT( POWER(SIN(($mylat - ABS(".$table.".post_latitude)) * pi()/180 / 2), 2) +COS($mylat * pi()/180) * COS( ABS(".$table.".post_latitude) * pi()/180) *POWER(SIN(($mylon - ".$table.".post_longitude) * pi()/180 / 2), 2) )))as distance ";
		}
	
	return $fields;
}


/*
* Listing tables join filter *
*/
function geodir_posts_join($join)
{
	global $wpdb,$geodir_post_type,$table,$table_prefix,$plugin_prefix;
	
		########### WPML ###########
		
		if(function_exists('icl_object_id')){
		global $sitepress;
		$lang_code = ICL_LANGUAGE_CODE;
		$default_lang_code = $sitepress->get_default_language();
			if($lang_code){
			$join .= "JOIN ".$table_prefix."icl_translations icl_t ON icl_t.element_id = ".$table_prefix."posts.ID";
			}

		}
		########### WPML ###########
			
		$join .= " INNER JOIN ".$table." ON (".$table.".post_id = $wpdb->posts.ID)  " ;
		//===old code start
		//$join .= " INNER JOIN ".POST_LOCATION_TABLE." ON (".$table.".post_location_id = ".POST_LOCATION_TABLE.".location_id)  " ;//===old code end
		
	return $join;
}


/*
* Listing orderby filters *
*/

function geodir_posts_orderby($orderby) {
	global $wpdb,$wp_query,$geodir_post_type,$table, $plugin_prefix, $snear, $default_sort;
	
	$sort_by = '';
	$orderby = ' ';
	
	if(get_query_var('order_by'))
		$sort_by = get_query_var('order_by');
	
	/*if(isset($wp_query->tax_query->queries) && $wp_query->tax_query->queries){
		$current_term = $wp_query->get_queried_object();
	}
	
	if(isset($current_term->term_id)){
		
		$current_term->term_id;
		
		if(get_tax_meta($current_term->term_id,'ct_cat_sort')){		
			$sort_by = get_tax_meta($current_term->term_id,'ct_cat_sort');	
		}
	}*/
	
		
	if($snear != '' ){$orderby .= " distance,";}
	
	if(isset($_REQUEST['sort_by']) && $_REQUEST['sort_by'] != '' && is_main_query())
		$sort_by = $_REQUEST['sort_by'];
	
	
	if($sort_by == ''){
		$default_sort = geodir_get_posts_default_sort($geodir_post_type);
		if(!empty($default_sort))
			$sort_by = $default_sort;
	}
	
	
	switch($sort_by):
		case 'newest':
			$orderby = "$wpdb->posts.post_date desc, ";
		break;
		case 'oldest':
			$orderby = "$wpdb->posts.post_date asc, ";
		break;
		case 'low_review':
			$orderby = "$wpdb->posts.comment_count asc, ";
		break;
		case 'high_review':
			$orderby = "$wpdb->posts.comment_count desc, ";
		break;
		case 'low_rating':
			$orderby = $table.".overall_rating asc, ";
		break;
		case 'high_rating':
			$orderby = $table.".overall_rating desc, ";
		break;
		case 'featured':
			$orderby = $table.".is_featured asc, ";
		break;
		case 'nearest':
			$orderby = " distance asc, ";
		break;
		case 'farthest':
			$orderby = " distance desc, ";
		break;
		case 'random':
			$orderby = " rand(), ";
		break;
		case 'az':
			$orderby = "$wpdb->posts.post_title asc, ";
		break;
		default:
			
		break;
	endswitch;
	
	$orderby = apply_filters('geodir_posts_order_by_sort', $orderby, $sort_by, $table);
	
	$orderby .= $table.".is_featured asc, $wpdb->posts.post_date desc, $wpdb->posts.post_title ";
	
	return $orderby;
}


function geodir_posts_order_by_custom_sort($orderby, $sort_by, $table){
	
	global $wpdb;
	
	if($sort_by != ''){
	
		$sort_array = explode('_', $sort_by);
		
		$sort_by_count = count($sort_array);
		
		$order = $sort_array[$sort_by_count-1];
		
		if($sort_by_count > 1 && ($order == 'asc' || $order == 'desc')){
			
			$sort_by = str_replace('_'.$order,'',$sort_by);
			
			switch($sort_by):
				
				case 'post_date':
				case 'comment_count':
					$orderby = "$wpdb->posts.".$sort_by." ".$order.", ";
				break;
				
				case 'distance':
					$orderby = $sort_by." ".$order.", ";
				break;
				
				default:
					$orderby = $table.".".$sort_by." ".$order.", ";
				break;
			
			endswitch;
			
		}
		
	}
	
	return $orderby;
}

/*
* Listing where filters *
*/

function geodir_post_where(){
	
	
	global $wpdb,$geodir_post_type,$table, $s, $snear;
	
	if(!is_admin())
	{
		
		if( geodir_is_page('add-listing') && isset($_REQUEST['pid']) && $_REQUEST['pid']!='' ){	
				add_filter('posts_where', 'geodir_edit_listing_where', 1 );
		
		}elseif( ( is_search() && $_REQUEST['geodir_search'] )  ){
			
			add_filter('posts_where', 'searching_filter_where', 1);		
			
			if($snear!='')
				add_filter('posts_where', 'searching_filter_where', 1);		
			
			add_filter('posts_orderby', 'geodir_posts_orderby', 1);
		
		}elseif( geodir_is_page('author') ){
			
			add_filter('posts_where', 'author_filter_where', 1);	
				
		}
		
		if( !geodir_is_page('detail') )
			add_filter('posts_where', 'geodir_default_where', 1);/**/
		
	}
}

/*
* Listing edit filter *
*/
function geodir_edit_listing_where($where){
	global $wpdb;
	$where = " AND $wpdb->posts.ID = ".$_REQUEST['pid'];
	return $where;
}


/*
* Listing location filters *
*/

function geodir_default_where($where){
	global $wp_query,$wpdb;

	//print_r($wp_query);
		########### WPML ###########
		
		if(function_exists('icl_object_id')){
		global $sitepress,$table_prefix;
		$lang_code = ICL_LANGUAGE_CODE;
		$default_lang_code = $sitepress->get_default_language();
		$q_post_type = isset($wp_query->query['post_type']) ? $wp_query->query['post_type'] : '';
		//echo '##########'.$q_post_type;
			if($lang_code && $q_post_type){
			$where .= " AND icl_t.language_code = '$lang_code' AND icl_t.element_type IN('post_".$q_post_type."') ";
			//$where .= " AND icl_t.language_code = '$lang_code' ";
			}

		}
		########### WPML ###########



	return $where = str_replace("0 = 1", "1=1", $where);
	
	/* ====== old code start === 
	$where = str_replace("0 = 1", "1=1", $where);
	$country = get_query_var('gd_country');
	$region = get_query_var('gd_region');
	$city = get_query_var('gd_city');
	$neighbourhood = get_query_var('gd_neighbourhood');
	
	
	if($country != '')
		$where .= " AND ".POST_LOCATION_TABLE.".country_slug = '".$country."' ";
	
	if($region != '')
		$where .= " AND ".POST_LOCATION_TABLE.".region_slug = '".$region."' ";

	if($city != '')
		$where .= " AND ".POST_LOCATION_TABLE.".city_slug = '".$city."' ";

	if($neighbourhood != '')
		$where .= " AND ".$table.".post_neighbourhood = '".$neighbourhood."' ";
		
	return $where;
	/* === old code end ===*/

}


/*
* Listing search filter *
*/

function searching_filter_where($where) {
	
	global $wpdb,$geodir_post_type,$table, $plugin_prefix, $dist, $mylat, $mylon, $s, $snear, $s,$s_A,$s_SA;
	
	global $search_term;
	$search_term = 'OR';
	$search_term = 'AND';
	$geodir_custom_search = '';
	
	$category_search_range = '';
	
	if( is_single() && get_query_var('post_type') ) return $where;
	
	if( is_tax()) return $where;
		
	$where ='';

	$better_search_terms ='';
	$better_search = array();
	
	if(!empty($s_SA)){
		foreach($s_SA as $s_term){
			$better_search[] = " OR $wpdb->posts.post_title LIKE\"%$s_term%\" ";
		}
	}
	
	
	
	if(is_array($better_search)){$better_search_terms = implode(' ', $better_search);}
	
	$better_search_terms ='';
	if(isset($_REQUEST['stype']))
		$post_types = $_REQUEST['stype'];
	else
		$post_types = 'gd_place';
	
	
	/* get taxonomy */
	$taxonomies = geodir_get_taxonomies($post_types,true);
	$taxonomies = implode("','",$taxonomies);	
	$taxonomies = "'". $taxonomies ."'";
		
	if($snear!='')
	{
			$lon1 = $mylon-$dist/abs(cos(deg2rad($mylat))*69); 
			$lon2 = $mylon+$dist/abs(cos(deg2rad($mylat))*69);
			$lat1 = $mylat-($dist/69);
			$lat2 = $mylat+($dist/69);
			$where .= " AND ( ( $wpdb->posts.post_title LIKE \"%$s%\" $better_search_terms)
								OR ($wpdb->posts.post_content LIKE \"%$s%\") 
								OR ($wpdb->posts.ID IN( 
										SELECT $wpdb->term_relationships.object_id as post_id 
										FROM $wpdb->term_taxonomy,  $wpdb->terms, $wpdb->term_relationships 
										WHERE $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id
										AND $wpdb->term_relationships.term_taxonomy_id =  $wpdb->term_taxonomy.term_taxonomy_id
										AND $wpdb->term_taxonomy.taxonomy in ({$taxonomies})
										AND ($wpdb->terms.name LIKE\"%$s%\"  OR $wpdb->terms.name IN ($s_A))  
										)
									) 
							)
						AND $wpdb->posts.post_type in ('{$post_types}') 
						AND ($wpdb->posts.post_status = 'publish') 
						AND ( ".$table.".post_latitude between $lat1 and $lat2 ) 
						AND ( ".$table.".post_longitude between $lon1 and $lon2 ) ";
						
		if(isset($_REQUEST['sdist']) && $_REQUEST['sdist'] != 'all'){
			$DistanceRadius = geodir_getDistanceRadius(get_option('geodir_search_dist_1'));
			$where .= " AND CONVERT((".$DistanceRadius." * 2 * ASIN(SQRT( POWER(SIN(($mylat - ABS(".$table.".post_latitude)) * pi()/180 / 2), 2) +COS($mylat * pi()/180) * COS( ABS(".$table.".post_latitude) * pi()/180) *POWER(SIN(($mylon - ".$table.".post_longitude) * pi()/180 / 2), 2) ))),DECIMAL(64,4)) <= ".$dist;
		}
		
	}else
	{
		$where .= " AND (	( $wpdb->posts.post_title LIKE \"%$s%\" $better_search_terms) 
							OR ( $wpdb->posts.post_content LIKE \"%$s%\") 
							OR ( $wpdb->posts.ID IN(	
									SELECT $wpdb->term_relationships.object_id as post_id                     
									FROM $wpdb->term_taxonomy,  $wpdb->terms, $wpdb->term_relationships
								WHERE $wpdb->term_taxonomy.term_id =  $wpdb->terms.term_id
								AND $wpdb->term_relationships.term_taxonomy_id =  $wpdb->term_taxonomy.term_taxonomy_id
								AND $wpdb->term_taxonomy.taxonomy in ( {$taxonomies} )
								AND ($wpdb->terms.name LIKE\"%$s%\" OR $wpdb->terms.name IN ($s_A)) 
								)
						) 
					) 
				AND $wpdb->posts.post_type in ('$post_types') 
				AND ($wpdb->posts.post_status = 'publish') ";
	}
	return $where;
}


/*
* Listing author filter *
*/

function author_filter_where($where){
	
	global $wpdb,$geodir_post_type,$table,$curr;
	
		
	$user_id = get_current_user_id();
	if(isset($_REQUEST['stype'])){
		$where = " AND $wpdb->posts.post_type IN ('".$_REQUEST['stype']."') ";
	}else{
		$where = " AND $wpdb->posts.post_type IN ('gd_place') ";
	}	 
		
	if(isset($_REQUEST['list']) && $_REQUEST['list'] == 'favourite'){	
		if($user_fav_posts = get_user_meta($user_id,'gd_user_favourite_post', true))
					$user_fav_posts = implode("','",$user_fav_posts);
		$where .= " AND $wpdb->posts.ID IN ('$user_fav_posts')  ";
	}else
		$where .= " AND $wpdb->posts.post_author = $user_id ";
	
	########### WPML ###########
		
	if(function_exists('icl_object_id')){
	$lang_code = ICL_LANGUAGE_CODE;
		if($lang_code){
		$where .= " AND icl_t.language_code='".$lang_code."' ";
		}

	}
	########### WPML ###########
	
	return $where;
}


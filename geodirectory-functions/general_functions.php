<?php  
/*-----------------------------------------------------------------------------------*/
/* Get All Plugin functions */
/*-----------------------------------------------------------------------------------*/ 

include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/*-----------------------------------------------------------------------------------*/
/* Helper functions */
/*-----------------------------------------------------------------------------------*/ 

/**
 * Get the plugin url
 */
function geodir_plugin_url() { 

	if (is_ssl()) :
		return str_replace('http://', 'https://', WP_PLUGIN_URL) . "/" . plugin_basename( dirname(dirname(__FILE__))); 
	else :
		return WP_PLUGIN_URL . "/" . plugin_basename( dirname(dirname(__FILE__))); 
	endif;
}

/**
 * Get the plugin path
 */
function geodir_plugin_path() { 	
	return WP_PLUGIN_DIR . "/" . plugin_basename( dirname(dirname(__FILE__))); 
 }
	
/**
 * Check for plugin addons
 */		 
function geodir_is_plugin_active($plugin){
	$active_plugins = get_option( 'active_plugins');
	foreach($active_plugins as $key=>$active_plugin){
		if(strstr($active_plugin,$plugin)) return true;
	}
	return false;
}

/**
 * Get date and time formate
 */
function get_formated_date($date)
{
	return mysql2date(get_option('date_format'), $date);
}
function get_formated_time($time)
{
	return mysql2date(get_option('time_format'), $time, $translate=true);
}

/**
 * Create Url through query string var
 **/
function geodir_getlink($url,$params=array(),$use_existing_arguments=false) {
    if($use_existing_arguments) $params = $params + $_GET;
    if(!$params) return $url;
    $link = $url;
    if(strpos($link,'?') === false) $link .= '?'; //If there is no '?' add one at the end
    elseif(!preg_match('/(\?|\&(amp;)?)$/',$link)) $link .= '&'; //If there is no '&' at the END, add one.
    
    $params_arr = array();
    foreach($params as $key=>$value) {
        if(gettype($value) == 'array') { //Handle array data properly
            foreach($value as $val) {
                $params_arr[] = $key . '[]=' . urlencode($val);
            }
        } else {
            $params_arr[] = $key . '=' . urlencode($value);
        }
    }
    $link .= implode('&',$params_arr);
    
    return $link;
} 

function geodir_get_addlisting_link($post_type = ''){
	global $wpdb;
	
	//$check_pkg  = $wpdb->get_var("SELECT pid FROM ".GEODIR_PRICE_TABLE." WHERE post_type='".$post_type."' and status != '0'");
	$check_pkg =1 ;
	if(post_type_exists( $post_type ) && $check_pkg){
		
		$add_listing_link = get_page_link(get_option('geodir_add_listing_page'));
		
		return add_query_arg(array('listing_type'=>$post_type),$add_listing_link);
	}else
		return get_bloginfo('url');
}

function geodir_curPageURL() {
 $pageURL = 'http';
 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
 $pageURL .= "://";
 if ($_SERVER["SERVER_PORT"] != "80") {
  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
 } else {
  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
 }
 return str_replace("www.", "", $pageURL);
}


/**
 * Clean variables
 This function is used to create posttype, posts, taxonomy and terms slug
 **/
function geodir_clean( $string ) {
	
	$string = trim(strip_tags(stripslashes($string)));
   	$string = str_replace(" ", "-", $string); // Replaces all spaces with hyphens.
   	$string = preg_replace('/[^A-Za-z0-9\-\_]/', '', $string); // Removes special chars.
	$string = preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.

	return $string;
}

/**
 * Get Week Days
 **/
function geodir_get_weekday() {
	return array(__('Sunday',GEODIRECTORY_TEXTDOMAIN),__('Monday',GEODIRECTORY_TEXTDOMAIN),__('Tuesday',GEODIRECTORY_TEXTDOMAIN),__('Wednesday',GEODIRECTORY_TEXTDOMAIN),__('Thursday',GEODIRECTORY_TEXTDOMAIN),__('Friday',GEODIRECTORY_TEXTDOMAIN),__('Saturday',GEODIRECTORY_TEXTDOMAIN));
}

/**
 * Get Weeks
 **/
function geodir_get_weeks() {
	return array(__('First',GEODIRECTORY_TEXTDOMAIN),__('Second',GEODIRECTORY_TEXTDOMAIN),__('Third',GEODIRECTORY_TEXTDOMAIN),__('Fourth',GEODIRECTORY_TEXTDOMAIN),__('Last',GEODIRECTORY_TEXTDOMAIN));
}


/**
 * Check that page is
 **/
function geodir_is_page($gdpage = ''){
	
	global $wp_query;
	//if(!is_admin()):
	
		switch($gdpage):
			case 'add-listing':
					
					if(is_page() && get_query_var('page_id') == get_option( 'geodir_add_listing_page' ) )
						return true;
				
			break;
			case 'preview':
					if((is_page() && get_query_var('page_id') == get_option( 'geodir_preview_page' ) ) && isset($_REQUEST['listing_type'])
						&& in_array( $_REQUEST['listing_type'], geodir_get_posttypes() ) )
						return true;
			break;
			case 'listing-success':
					if(is_page() && get_query_var('page_id') == get_option( 'geodir_success_page' ) )
						return true;
			break;
			case 'detail':
					if ( is_single() && in_array( get_query_var('post_type'), geodir_get_posttypes() ) )
						return true;
			break;
			case 'listing':
					if(is_page() && get_query_var('page_id') == get_option( 'geodir_listing_page' ) )
						return true;
					if ( is_tax() && geodir_get_taxonomy_posttype() ){
						global $current_term,$taxonomy,$term;
		
						return true;
					}	
					if ( is_post_type_archive() && in_array(get_query_var('post_type'),geodir_get_posttypes()) )
						return true;
					
			break;
			case 'location':
					if( is_page() && get_query_var('page_id') == get_option('geodir_location_page'))
						return true;
			break;
			case 'author':
					if( is_author() && isset($_REQUEST['geodir_dashbord'] ) )
						return true;
			break;
			case 'search':
					if( is_search() && isset($_REQUEST['geodir_search']) )
						return true;
			break;
			default:
				return false;
			break;	
			
		endswitch;
	
	//endif;
	
	return false;	
}


/**
 * Get plugin image sizes
 */
if (!function_exists('geodir_get_imagesize')) {
function geodir_get_imagesize($size = ''){
	
	$imagesizes = array(	'list-thumb' => array('w' => 283, 'h' => 188 ),
							'thumbnail' => array('w' => 125, 'h' => 125 ),
							'widget-thumb' => array('w' => 50, 'h' => 50 ),
							'slider-thumb' => array('w' => 100, 'h' => 100 )
						);
	
	$imagesizes = apply_filters('geodir_imagesizes', $imagesizes);
	
	if( !empty($size) && array_key_exists($size, $imagesizes) ){
		
		return apply_filters('geodir_get_imagesize_'.$size, $imagesizes[$size]);
	
	}elseif( !empty($size) ){
		
		return  new WP_Error('geodir_no_imagesize', __("Given image size is not valid",GEODIRECTORY_TEXTDOMAIN) );
	
	}
	
	return $imagesizes;
}
}
		
/**
 * Get an image size
 *
 * Variable is filtered by geodir_get_image_size_{image_size}
 */
 /*
function geodir_get_image_size( $image_size ) {
	$return = '';
	switch ($image_size) :
		case "list_thumbnail_size" : $return = get_option('geodirectory_list_thumbnail_size'); break;
	endswitch;
	return apply_filters( 'geodir_get_image_size_'.$image_size, $return );
}
*/


if (!function_exists('createRandomString')) {
function createRandomString() { 
	$chars = "abcdefghijkmlnopqrstuvwxyz1023456789"; 
	srand((double)microtime()*1000000); 
	$i = 0; 
	$rstring = '' ; 
	while ($i <= 25) { 
		$num = rand() % 33; 
		$tmp = substr($chars, $num, 1); 
		$rstring = $rstring . $tmp; 
		$i++; 
	} 
	return $rstring; 
} 
}

if (!function_exists('geodir_getDistanceRadius')) {
function geodir_getDistanceRadius($uom = 'km') {
//	Use Haversine formula to calculate the great circle distance between two points identified by longitude and latitude
	switch (strtolower($uom)):
		case 'km'	:
		$earthMeanRadius = 6371.009; // km
		break;
		case 'm'	:
		case 'meters'	:
			$earthMeanRadius = 6371.009 * 1000; // km
		break;
		case 'miles'	:
			$earthMeanRadius = 3958.761; // miles
		break;
		case 'yards'	:
		case 'yds'	:
			$earthMeanRadius = 3958.761 * 1760; // yards
		break;
		case 'feet'	:
		case 'ft'	:
			$earthMeanRadius = 3958.761 * 1760 * 3; // feet
		break;
		case 'nm'	:
			$earthMeanRadius = 3440.069; //  miles
		break;
		default:	
			$earthMeanRadius = 3958.761; // miles
		break;
	endswitch;
	return $earthMeanRadius;
}
}


if (!function_exists('geodir_calculateDistanceFromLatLong')) {
function geodir_calculateDistanceFromLatLong($point1,$point2,$uom='km') {
//	Use Haversine formula to calculate the great circle distance between two points identified by longitude and latitude
	
	$earthMeanRadius = geodir_getDistanceRadius($uom);

	$deltaLatitude = deg2rad($point2['latitude'] - $point1['latitude']);
	$deltaLongitude = deg2rad($point2['longitude'] - $point1['longitude']);
	$a = sin($deltaLatitude / 2) * sin($deltaLatitude / 2) +
	cos(deg2rad($point1['latitude'])) * cos(deg2rad($point2['latitude'])) *
	sin($deltaLongitude / 2) * sin($deltaLongitude / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1-$a));
	$distance = $earthMeanRadius * $c;
	return $distance;
}
}


if (!function_exists('geodir_sendEmail')) {
function geodir_sendEmail($fromEmail,$fromEmailName,$toEmail,$toEmailName,$to_subject,$to_message,$extra='',$message_type,$post_id='',$user_id='')
{
	$login_details ='';
	
	if($message_type=='send_friend'){
		$subject = stripslashes(get_option('geodir_email_friend_subject')); 
		$message = stripslashes(get_option('geodir_email_friend_content'));
	}elseif($message_type=='send_enquiry'){
		$subject = stripslashes(get_option('geodir_email_enquiry_subject')); 
		$message = stripslashes(get_option('geodir_email_enquiry_content'));
	}elseif($message_type=='forgot_password'){
		$subject = stripslashes(get_option('geodir_forgot_password_subject')); 
		$message = stripslashes(get_option('geodir_forgot_password_content')); 
		$login_details =$to_message; 
	}elseif($message_type=='registration'){
		$subject = stripslashes(get_option('geodir_registration_success_email_subject'));
		$message = stripslashes(get_option('geodir_registration_success_email_content')); 
		$login_details =$to_message; 
	}elseif($message_type=='post_submit'){
		$subject = stripslashes(get_option('geodir_post_submited_success_email_subject')); 
		$message = stripslashes(get_option('geodir_post_submited_success_email_content')); 
	}
	
	
	$to_message = nl2br($to_message);
	$sitefromEmail = get_option('site_email');
	$sitefromEmailName = get_site_emailName();
	$productlink = get_permalink($post_id);
	
	$posted_date = '';
	$listingLink ='';
	
	$post_info = get_post($post_id);
	
	if($post_info){
		$posted_date = $post_info->post_date;
		$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
	}
	$siteurl = site_url();
	$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
	$loginurl = site_url().'/?geodir_signup=true';
	$loginurl_link = '<a href="'.$loginurl.'">login</a>';
	
	if($fromEmail==''){$fromEmail = get_option('site_email_name');}
	
	if($fromEmailName==''){$fromEmailName = get_option('site_email');}
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName, $posted_date);
	$message = str_replace($search_array,$replace_array,$message);
	
	$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]', '[#posted_date#]');
	$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
	$subject = str_replace($search_array,$replace_array,$subject);
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= "Reply-To: ".$fromEmail. "\r\n";
	$headers .= 'To: '.$toEmailName.' <'.$toEmail.'>' . "\r\n";
	$headers .= 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>' . "\r\n";
	
	
	@wp_mail($toEmail, $subject, $message, $headers);
	
	///////// ADMIN BCC EMIALS
	$adminEmail = get_bloginfo('admin_email');
	
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
	$headers .= "Reply-To: ".$fromEmail. "\r\n";
	$headers .= 'To: <'.$adminEmail.'>' . "\r\n";
	$headers .= 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>' . "\r\n";
	
	
	if($message_type=='post_submit'){
		
		$subject = stripslashes(get_option('geodir_post_submited_success_email_subject_admin')); 
		$message = stripslashes(get_option('geodir_post_submited_success_email_content_admin'));
		
		
		$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]', '[#posted_date#]');
		$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName, $posted_date);
		$message = str_replace($search_array,$replace_array,$message);
		
		$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]','[#posted_date#]');
		$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName, $posted_date);
		$subject = str_replace($search_array,$replace_array,$subject);
		
		$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);
		
	}
	
	if($message_type=='registration' && get_option('geodir_bcc_new_user')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}
	
	if($message_type=='send_friend' && get_option('geodir_bcc_friend')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}
	
	if($message_type=='send_enquiry' && get_option('geodir_bcc_enquiry')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}

}}



function geodir_taxonomy_breadcrumb() {
	
	$term = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
 	$parent = $term->parent;
	
	while ($parent):
		$parents[] = $parent;
		$new_parent = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
		$parent = $new_parent->parent;
	endwhile;
	
	if(!empty($parents)):
		$parents = array_reverse($parents);
		 
		foreach ($parents as $parent):
			$item = get_term_by( 'id', $parent, get_query_var( 'taxonomy' ));
			$url = get_term_link( $item, get_query_var( 'taxonomy' ) ); 
			echo '<li> > <a href="'.$url.'">'.$item->name.'</a></li>';
		endforeach;

	endif;
	 
	echo '<li> > '.$term->name.'</li>';
}




function geodir_breadcrumb($saprator = ' > ') {
    global $wp_query, $geodir_add_location_url;   
	
	if (!is_home()) {
		$breadcrumb = '';
		
		$url_categoris = '';
		$breadcrumb .= '<div class="geodir-breadcrumb clearfix"><ul id="breadcrumbs">';
        $breadcrumb .= '<li><a href="'.get_option('home').'">'.__('Home',GEODIRECTORY_TEXTDOMAIN).'</a></li>';
       	
		$gd_post_type = geodir_get_current_posttype();
		$post_type_info = get_post_type_object( $gd_post_type );
		$listing_link = get_post_type_archive_link( $gd_post_type );
		
		$geodir_add_location_url = true;
		$archive_link = get_post_type_archive_link( $gd_post_type );
		$geodir_add_location_url = NULL;
		
		$locatin_array = array();
		$locatin_array[] = get_query_var('gd_country');
		$locatin_array[] = get_query_var('gd_region');
		$locatin_array[] = get_query_var('gd_city');
		
		
		if( geodir_is_page('detail') ){
			
			global $post;
			
			$breadcrumb .= '<li>';
			
			if( get_query_var($gd_post_type.'category') )
				$gd_taxonomy = $gd_post_type.'category';
			elseif( get_query_var($gd_post_type.'_tags') )
				$gd_taxonomy = $gd_post_type.'_tags';
			
			$breadcrumb .= $saprator.'<a href="'.$archive_link.'">' . ucfirst($post_type_info->label) .'</a>';
				
				
			if(!empty($gd_taxonomy)){
				
				if( get_query_var($gd_post_type.'_tags') ){
					$cat_link = $listing_link.'tags/';
				}	
				else	
					$cat_link = $listing_link;
						
				$request_terms = explode( "/", trim($wp_query->query[$gd_taxonomy],"/" ) );
				
				$current_term_link = '';
				if(get_option('geodir_add_categories_url')){
					$url_separator = get_option('geodir_listingurl_separator');
					if( strpos(get_permalink($post->ID),$url_separator) ){
						$current_term = end($request_terms);
						$current_term_link = $url_separator."/".$current_term;
					}	
				}	
				
				
				if ( get_option('permalink_structure') == '' )
					$cat_link = $listing_link.'&'.$gd_taxonomy.'=';
				
				foreach($request_terms as $request_term){
					
					if( $request_term == get_option('geodir_detailurl_separator') 
						||  $request_term == get_option('geodir_listingurl_separator') )
						continue;
					
					$link = '';
						
					//$cat_name = str_replace("_"," ",$request_term); 
					$cat_name = preg_replace('/[_-]/', ' ', $request_term);
					
					if(isset($current_term) && $request_term == $current_term){
						$link = $cat_link.$current_term_link;
					}else{
						$cat_link .= $request_term."/";
						$link = $cat_link.$current_term_link;
					}
					
					$breadcrumb .= $saprator.'<a href="'.$link.'">' . ucwords($cat_name) .'</a>';
				}
			
			}
			
			if( geodir_is_page('detail') )	
				$breadcrumb .= $saprator.get_the_title();
			
			$breadcrumb .= '</li>';
			
		
		}else
		if(geodir_is_page('listing')){
			$breadcrumb .= '<li>';
			
			if( get_query_var($gd_post_type.'category') )
				$gd_taxonomy = $gd_post_type.'category';
			elseif( get_query_var($gd_post_type.'_tags') )
				$gd_taxonomy = $gd_post_type.'_tags';
			
			if(is_post_type_archive() && empty($gd_taxonomy))
				$breadcrumb .= $saprator . ucwords($post_type_info->label);	
			else
				$breadcrumb .= $saprator.'<a href="'.$archive_link.'">' . ucwords($post_type_info->label) .'</a>';
				
			if(!empty($gd_taxonomy)){
						
				$request_terms = explode( "/", trim($wp_query->query[$gd_taxonomy],"/" ) );
				//$current_term = end($request_terms);
				
				$add_categories = get_option('geodir_add_categories_url');
				if($add_categories)
				{	
					$url_separator = get_option('geodir_listingurl_separator');
					$url_terms = explode($url_separator, trim($wp_query->query[$gd_taxonomy],"/" ) );
					$url_location = $url_terms[0];
					
					if(count($url_terms) > 1){
						$url_categoris = $url_separator.$url_terms[1];
						$url_categoris_array = explode( "/", trim($url_terms[1],"/") ) ;
					}	
					
				}	
				
				
				if( get_query_var($gd_post_type.'_tags') ){
					$cat_link = $listing_link.'tags/';
				}	
				else	
					$cat_link = $listing_link;
				
				if ( get_option('permalink_structure') == '' )
					$cat_link = $listing_link.'&'.$gd_taxonomy.'=';
				
				foreach($request_terms as $request_term){
					
					if( $request_term == get_option('geodir_listingurl_separator')
						|| $request_term == get_option('geodir_detailurl_separator')
						|| $request_term == 'tags')
						continue;
					
					$link = '';
						
					//$cat_name = str_replace("_"," ",$request_term); 
					$cat_name = preg_replace('/[_-]/', ' ', $request_term);
					
					if(in_array($request_term,$locatin_array)){
					  $link = $url_categoris;
					}
					
					if(isset($url_categoris_array[0]) && $request_term == $url_categoris_array[0]){
					  $cat_link = $url_separator."/";
					}
					
					$cat_link .= $request_term."/";
					
					$link = $cat_link.$link;
					
					if( $request_term != end($request_terms) || geodir_is_page('detail') )
					$breadcrumb .= $saprator.'<a href="'.$link.'">' . ucwords($cat_name) .'</a>';
					else $breadcrumb .= $saprator . ucwords($cat_name);
				}
			
			}
				
			$breadcrumb .= '</li>';
			
		}elseif( geodir_is_page('author') ){
			$user_id = get_current_user_id();
			$author_link = get_author_posts_url( $user_id );
			$default_author_link = geodir_getlink($author_link,array('geodir_dashbord'=>'true','stype'=>'gd_place'),false);
			$breadcrumb .= '<li>';
			$breadcrumb .= $saprator.'<a href="'.$default_author_link.'">' . __('My Dashboard',GEODIRECTORY_TEXTDOMAIN) .'</a>';
			
			if(isset($_REQUEST['list'])){
				$author_link = geodir_getlink($author_link,array('geodir_dashbord'=>'true','stype'=>$_REQUEST['stype']),false);
				$breadcrumb .= $saprator.'<a href="'.$author_link.'">' . ucfirst($post_type_info->label).'</a>';
				$breadcrumb .= $saprator . ucfirst(__('My ',GEODIRECTORY_TEXTDOMAIN).$_REQUEST['list']);
			}else
				$breadcrumb .= $saprator . ucfirst($post_type_info->label);
				
			$breadcrumb .= '</li>';
		}else
	    if (is_category() || is_single()) {
            $breadcrumb .= '<li>';
            the_category(' </li><li>'.$saprator);
            if (is_single()) {
                $breadcrumb .= "</li><li>";
                $saprator.get_the_title();
                $breadcrumb .= '</li>';
            }
        }elseif (is_page()) {
            $breadcrumb .= '<li>'.$saprator;
            $breadcrumb .= stripslashes(get_the_title());
            $breadcrumb .= '</li>';}
		elseif (is_tag()) {$saprator.single_tag_title();}
		elseif (is_day()) {$breadcrumb .= "<li> ".$saprator.__(" Archive for ",GEODIRECTORY_TEXTDOMAIN); the_time('F jS, Y'); $breadcrumb .=  '</li>';}
		elseif (is_month()) {$breadcrumb .= "<li> ".$saprator.__(" Archive for ",GEODIRECTORY_TEXTDOMAIN); the_time('F, Y'); $breadcrumb .=  '</li>';}
		elseif (is_year()) {$breadcrumb .=   "<li> ".$saprator.__(" Archive for ",GEODIRECTORY_TEXTDOMAIN); the_time('Y'); $breadcrumb .= '</li>';}
		elseif (is_author()) {$breadcrumb .= "<li> ".$saprator.__(" Author Archive",GEODIRECTORY_TEXTDOMAIN); $breadcrumb .= '</li>';}
		elseif (isset($_GET['paged']) && !empty($_GET['paged'])) {$breadcrumb .= "<li>".$saprator.__("Blog Archives",GEODIRECTORY_TEXTDOMAIN); $breadcrumb .= '</li>';}
		elseif (is_search()) {$breadcrumb .= "<li> ".$saprator.__(" Search Results",GEODIRECTORY_TEXTDOMAIN); $breadcrumb .= '</li>';}
	
	
    $breadcrumb .=  '</ul></div>';
		
		echo $breadcrumb = apply_filters('geodir_breadcrumb', $breadcrumb, $saprator);
	}
}



add_action("admin_init", "geodir_allow_wpadmin"); // check user is admin
if (!function_exists('geodir_allow_wpadmin')) {
function geodir_allow_wpadmin(){
	global $wpdb;
	if(get_option('geodir_allow_wpadmin') == '0' && is_user_logged_in() && (!isset($_REQUEST['action'])) ) // checking action in request to allow ajax request go through
	{
		if( current_user_can( 'manage_options' ) ) {}
		else{  
		
		wp_redirect( home_url() ); exit; 
		}			
		
	}
}
}


/* Move Images from a url to upload directory */
function fetch_remote_file( $url ) {
	// extract the file name and extension from the url
	$file_name = basename( $url );

	// get placeholder file in the upload dir with a unique, sanitized filename
	
	$post_upload_date = isset($post['upload_date']) ? $post['upload_date'] : '';
	
	$upload = wp_upload_bits( $file_name, 0, '', $post_upload_date );
	if ( $upload['error'] )
		return new WP_Error( 'upload_dir_error', $upload['error'] );

	// fetch the remote url and write it to the placeholder file
	$headers = wp_get_http( $url, $upload['file'] );

	// request failed
	if ( ! $headers ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Remote server did not respond',GEODIRECTORY_TEXTDOMAIN) );
	}

	// make sure the fetch was successful
	if ( $headers['response'] != '200' ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', sprintf( __('Remote server returned error response %1$d %2$s',GEODIRECTORY_TEXTDOMAIN ), esc_html($headers['response']), get_status_header_desc($headers['response']) ) );
	}

	$filesize = filesize( $upload['file'] );

	if ( isset( $headers['content-length'] ) && $filesize != $headers['content-length'] ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Remote file is incorrect size',GEODIRECTORY_TEXTDOMAIN) );
	}

	if ( 0 == $filesize ) {
		@unlink( $upload['file'] );
		return new WP_Error( 'import_file_error', __('Zero size file downloaded',GEODIRECTORY_TEXTDOMAIN) );
	}

	

	return $upload;
}

function geodir_max_upload_size() {
	return '2mb'; 
}

/* ------------------------------------------------------------------*/
/* Check if dummy folder exists or not , if not then fatch from live url */
/*--------------------------------------------------------------------*/
function geodir_dummy_folder_exists()
{
	$path = geodir_plugin_path(). '/geodirectory-admin/dummy/' ;
	if(!is_dir($path))
		return false;
	else
		return true;
		
}

/* ------------------------------------------------------------------*/
/* Get the author info*/
/*--------------------------------------------------------------------*/
function  geodir_get_author_info($aid)
{
	global $wpdb;
	/*$infosql = "select * from $wpdb->users where ID=$aid";*/
	$infosql = $wpdb->prepare("select * from $wpdb->users where ID=%d",array($aid));
	$info = $wpdb->get_results($infosql);
	if($info)
	{
		return $info[0];
	}
}

if (!function_exists('adminEmail')) {
function adminEmail($page_id,$user_id,$message_type,$custom_1=''){
global $wpdb;
if($message_type=='expiration'){$subject = stripslashes(get_option('renew_email_subject')); $client_message = stripslashes(get_option('renew_email_content'));}
elseif($message_type=='post_submited'){$subject = get_option('post_submited_success_email_subject_admin'); $client_message = get_option('post_submited_success_email_content_admin');}
elseif($message_type=='renew'){$subject = get_option('post_renew_success_email_subject_admin'); $client_message = get_option('post_renew_success_email_content_admin');}
elseif($message_type=='upgrade'){$subject = get_option('post_upgrade_success_email_subject_admin'); $client_message = get_option('post_upgrade_success_email_content_admin');}
elseif($message_type=='claim_approved'){$subject = get_option('claim_approved_email_subject'); $client_message = get_option('claim_approved_email_content');}
elseif($message_type=='claim_rejected'){$subject = get_option('claim_rejected_email_subject'); $client_message = get_option('claim_rejected_email_content');}
elseif($message_type=='claim_requested'){$subject = get_option('claim_email_subject_admin'); $client_message = get_option('claim_email_content_admin');}
elseif($message_type=='auto_claim'){$subject = get_option('auto_claim_email_subject'); $client_message = get_option('auto_claim_email_content');}
elseif($message_type=='payment_success'){$subject = get_option('post_payment_success_admin_email_subject'); $client_message = get_option('post_payment_success_admin_email_content');}
elseif($message_type=='payment_fail'){$subject = get_option('post_payment_fail_admin_email_subject'); $client_message = get_option('post_payment_fail_admin_email_content');}
$transaction_details = $custom_1;	
$approve_listing_link = '<a href="'.get_option('siteurl').'/?ptype=verify&rs='.$custom_1.'">'.VERIFY_TEXT.'</a>';	
$fromEmail = get_option('site_email');
$fromEmailName = get_site_emailName();
//$alivedays = get_post_meta($page_id,'alive_days',true);
$pkg_limit = get_property_price_info_listing($page_id);
$alivedays = $pkg_limit['days'];
$productlink = get_permalink($page_id);
$post_info = get_post($page_id);
$post_date =  date('dS F,Y',strtotime($post_info->post_date));
$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
$loginurl = home_url().'/?ptype=login';
$loginurl_link = '<a href="'.$loginurl.'">login</a>';
$siteurl = home_url();
$siteurl_link = '<a href="'.$siteurl.'">'.$fromEmailName.'</a>';
$user_info = get_userdata($user_id);
$user_email = $user_info->user_email;
$display_name = $user_info->first_name;
$user_login = $user_info->user_login;
$number_of_grace_days = get_option('ptthemes_listing_preexpiry_notice_days');
if($number_of_grace_days==''){$number_of_grace_days=1;}
if($post_info->post_type == 'event'){$post_type='event';}else{$post_type='listing';}
$renew_link = '<a href="'.$siteurl.'?ptype=post_'.$post_type.'&renew=1&pid='.$page_id.'">'.RENEW_LINK.'</a>';
$search_array = array('[#client_name#]','[#listing_link#]','[#posted_date#]','[#number_of_days#]','[#number_of_grace_days#]','[#login_url#]','[#username#]','[#user_email#]','[#site_name_url#]','[#renew_link#]','[#post_id#]','[#site_name#]','[#approve_listing_link#]','[#transaction_details#]');
$replace_array = array($display_name,$listingLink,$post_date,$alivedays,$number_of_grace_days,$loginurl_link,$user_login,$user_email,$siteurl_link,$renew_link,$page_id,$fromEmailName,$approve_listing_link,$transaction_details);
$client_message = str_replace($search_array,$replace_array,$client_message);	
$subject = str_replace($search_array,$replace_array,$subject);	
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
//$headers .= 'To: <'.$fromEmail.'>' . "\r\n";
$headers .= 'From: '.$fromEmailName.' <'.$fromEmail.'>' . "\r\n";
@wp_mail($fromEmail,$subject,$client_message,$headers);///To client email
}}
################################# ADMIN EMAIL FUNCTION END ##############################################################
################################# SEND EMAIL FUNCTION START #############################################################
if (!function_exists('sendEmail')) {
function sendEmail($fromEmail,$fromEmailName,$toEmail,$toEmailName,$to_subject,$to_message,$extra='',$message_type,$post_id='',$user_id='')
{
$login_details ='';
if($message_type=='send_friend'){$subject = stripslashes(get_option('email_friend_subject')); $message = stripslashes(get_option('email_friend_content'));}
elseif($message_type=='send_enquiry'){$subject = get_option('email_enquiry_subject'); $message = get_option('email_enquiry_content');}
elseif($message_type=='forgot_password'){$subject = get_option('forgot_password_subject'); $message = get_option('forgot_password_content'); $login_details =$to_message; }
elseif($message_type=='registration'){$subject = get_option('registration_success_email_subject'); $message = get_option('registration_success_email_content'); $login_details =$to_message; }
$to_message = nl2br($to_message);
$sitefromEmail = get_option('site_email');
$sitefromEmailName = get_site_emailName();
$productlink = get_permalink($post_id);
$post_info = get_post($post_id);
$listingLink ='<a href="'.$productlink.'"><b>'.$post_info->post_title.'</b></a>';
$siteurl = home_url();
$siteurl_link = '<a href="'.$siteurl.'">'.$siteurl.'</a>';
$loginurl = home_url().'/?ptype=login';
$loginurl_link = '<a href="'.$loginurl.'">login</a>';
if($fromEmail==''){$fromEmail = get_option('site_email_name');}
if($fromEmailName==''){$fromEmailName = get_option('site_email');}
$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#comments#]','[#login_url#]','[#login_details#]','[#client_name#]');
$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$to_message,$loginurl_link,$login_details,$toEmailName);
$message = str_replace($search_array,$replace_array,$message);

$search_array = array('[#listing_link#]','[#site_name_url#]','[#post_id#]','[#site_name#]','[#to_name#]','[#from_name#]','[#subject#]','[#client_name#]');
$replace_array = array($listingLink,$siteurl_link,$post_id,$sitefromEmailName,$toEmailName,$fromEmailName,$to_subject,$toEmailName);
$subject = str_replace($search_array,$replace_array,$subject);
$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
$headers .= "Reply-To: ".$fromEmail. "\r\n";
//$headers .= 'To: '.$toEmailName.' <'.$toEmail.'>' . "\r\n";
$headers .= 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>' . "\r\n";
@wp_mail($toEmail, $subject, $message, $headers);

///////// ADMIN BCC EMIALS
if($message_type=='registration'){
$message_raw = explode(__("Password:",GEODIRECTORY_TEXTDOMAIN), $message);
$message_raw2 = explode("</p>", $message_raw[1],2);
$message = $message_raw[0].__('Password:',GEODIRECTORY_TEXTDOMAIN).' **********</p>'.$message_raw2[1];
}
$adminEmail = get_bloginfo('admin_email');

$headers  = 'MIME-Version: 1.0' . "\r\n";
$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
$headers .= "Reply-To: ".$fromEmail. "\r\n";
//$headers .= 'To: <'.$adminEmail.'>' . "\r\n";
$headers .= 'From: '.$sitefromEmailName.' <'.$sitefromEmail.'>' . "\r\n";
if($message_type=='registration' && get_option('bcc_new_user')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}
if($message_type=='send_friend' && get_option('bcc_friend')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}
if($message_type=='send_enquiry' && get_option('bcc_enquiry')){$subject.=' - ADMIN BCC COPY'; @wp_mail($adminEmail, $subject, $message, $headers);}
}}
/*--------------------------------------------------------------------*/
/* Functions */
/*--------------------------------------------------------------------*/

/*
Language translation helper functions
*/

// function to get the translated category id's
function gd_lang_object_ids($ids_array, $type) {
 if(function_exists('icl_object_id')) {
  $res = array();
  foreach ($ids_array as $id) {
   $xlat = icl_object_id($id,$type,false);
   if(!is_null($xlat)) $res[] = $xlat;
  }
  return $res;
 } else {
  return $ids_array;
 }
}

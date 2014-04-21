<?php 
/**
 * Geodirectory Custom Post Types/Taxonomies
 * 
 * Inits custom post types and taxonomies
 *
 * @package		GeoDirectory
 * @category	Core
 * @author		WPGeoDirectory
 */
global $flush_rewrite_rules;
 

function geodir_register_taxonomies() {

	$taxonomies = array();
	$taxonomies = get_option('geodir_taxonomies');
	// If custom taxonomies are present, register them
	if ( is_array( $taxonomies ) ) {
		// Sort taxonomies
		ksort( $taxonomies );
		
		// Register taxonomies
		foreach ( $taxonomies as $taxonomy => $args ){
			$tax = register_taxonomy( $taxonomy, $args['object_type'], $args['args'] );
			
			if ( taxonomy_exists( $taxonomy ) ) {
				$tax = register_taxonomy_for_object_type( $taxonomy, $args['object_type'] );
			}
		}	
	}
}


/**
 * Get available custom posttypes and taxonomies and register them.
 **/	
 
function geodir_register_post_types() {
	
	global $wp_post_types;
		
	$post_types = array();
	$post_types = get_option('geodir_post_types');
	
	// Register each post type if array of data is returned
	if ( is_array( $post_types ) ):
		
		foreach ( $post_types as $post_type => $args ):
			$args = apply_filters('geodir_post_type_args' ,    $args , $post_type ) ;
			$post_type = register_post_type( $post_type, $args );
			
		endforeach;
	endif;
}


function geodir_post_type_args_modify( $args ,$post_type)
{
	if(isset($_REQUEST['geodir_listing_prefix']) && $_REQUEST['geodir_listing_prefix'] != '')
	{
			
			$listing_slug = htmlentities(trim($_REQUEST['geodir_listing_prefix']));
			
			if($post_type == 'gd_place')
			{
				if(array_key_exists('has_archive' ,$args ))
					$args['has_archive']  = $listing_slug ;
					
				if(array_key_exists('rewrite' ,$args ))
				{
					if(array_key_exists('slug' ,$args['rewrite']))
						$args['rewrite']['slug'] = $listing_slug.'/%gd_taxonomy%' ;		
				}
				
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				if (array_key_exists($post_type, $geodir_post_types)){
					
						if(array_key_exists('has_archive', $geodir_post_types[$post_type]))
							$geodir_post_types[$post_type]['has_archive'] = $listing_slug;
						
						if(array_key_exists('rewrite', $geodir_post_types[$post_type]))
							if(array_key_exists('slug', $geodir_post_types[$post_type]['rewrite']))	
									$geodir_post_types[$post_type]['rewrite']['slug'] = $listing_slug . '/%gd_taxonomy%';
						
						update_option( 'geodir_post_types', $geodir_post_types );
					
				}
				
				$geodir_post_types = get_option( 'geodir_post_types' );
				
				/* --- update taxonomies (category) --- */
				
				$geodir_taxonomies = get_option('geodir_taxonomies');
				
				if (array_key_exists('listing_slug', $geodir_taxonomies[$post_type.'category'])){
							$geodir_taxonomies[$post_type.'category']['listing_slug'] = $listing_slug;
						
						if(array_key_exists('args', $geodir_taxonomies[$post_type.'category']))
							if(array_key_exists('rewrite', $geodir_taxonomies[$post_type.'category']['args']))
								if(array_key_exists('slug', $geodir_taxonomies[$post_type.'category']['args']['rewrite']))	
									$geodir_taxonomies[$post_type.'category']['args']['rewrite']['slug'] = $listing_slug;
						
						update_option( 'geodir_taxonomies', $geodir_taxonomies );
					
				}
				
				/* --- update taxonomies (tags) --- */
				$geodir_taxonomies_tag = get_option('geodir_taxonomies');
				if (array_key_exists('listing_slug', $geodir_taxonomies_tag[$post_type.'_tags'])){
							$geodir_taxonomies_tag[$post_type.'_tags']['listing_slug'] = $listing_slug.'/tags';
						
						if(array_key_exists('args', $geodir_taxonomies_tag[$post_type.'_tags']))
							if(array_key_exists('rewrite', $geodir_taxonomies_tag[$post_type.'_tags']['args']))
								if(array_key_exists('slug', $geodir_taxonomies_tag[$post_type.'_tags']['args']['rewrite']))	
									$geodir_taxonomies_tag[$post_type.'_tags']['args']['rewrite']['slug'] = $listing_slug.'/tags';
						
						update_option( 'geodir_taxonomies', $geodir_taxonomies_tag );
					
				}
				
			}
					
	}
	
	return $args ;
}
 
 //Give everyone else a chance to set rules etc.

function geodir_flush_rewrite_rules() {
	global $wp_rewrite;
		$wp_rewrite->flush_rules(false);
} 



function geodir_listing_rewrite_rules( $rules )
{
	$newrules = array();
	$taxonomies = array();
	$taxonomies = get_option('geodir_taxonomies');
	$detail_url_seprator = get_option('geodir_detailurl_separator');
	//create rules for post listing
	if ( is_array( $taxonomies ) ):
		foreach ( $taxonomies as $taxonomy => $args ):
			
			$post_type = $args['object_type'];
			$listing_slug = $args['listing_slug'];
			
			if(strpos($taxonomy,'tags'))
				$newrules[$listing_slug.'/(.+?)/?$'] = 'index.php?'.$taxonomy.'=$matches[1]';
			
			$newrules[$listing_slug.'/'.$detail_url_seprator.'/([^/]+)/?$'] = 'index.php?'.$post_type.'=$matches[1]';
			$newrules[$listing_slug.'/(.+?)/'.$detail_url_seprator.'/([^/]+)/?$'] = 'index.php?'.$taxonomy.'=$matches[1]&'.$post_type.'=$matches[2]';
			
			
			
		endforeach; 
	endif;	
	
	//create rules for location listing
	$location_page = get_option('geodir_location_page');
	$location_prefix = get_option('geodir_location_prefix');
	if($location_prefix == '')
		$location_prefix  = 'location';
	
	if(get_option('geodir_show_location_url') == 'all'){
		$newrules[$location_prefix.'/([^/]+)/([^/]+)/([^/]+)/?$'] = 'index.php?page_id='.$location_page.'&gd_country=$matches[1]&gd_region=$matches[2]&gd_city=$matches[3]';
		$newrules[$location_prefix.'/([^/]+)/([^/]+)/?$'] = 'index.php?page_id='.$location_page.'&gd_country=$matches[1]&gd_region=$matches[2]';
		$newrules[$location_prefix.'/([^/]+)/?$'] = 'index.php?page_id='.$location_page.'&gd_country=$matches[1]';
	}else{
				
		$newrules[$location_prefix.'/([^/]+)/?$'] = 'index.php?page_id='.$location_page.'&gd_city=$matches[1]';
	}	
		
	$newrules[$location_prefix.'/?$'] = 'index.php?page_id='.$location_page;		
	 
	$rules = array_merge($newrules,$rules);
	return $rules;
}



function add_location_var($public_query_vars) {
	$public_query_vars[] = 'gd_country';
	$public_query_vars[] = 'gd_region';
	$public_query_vars[] = 'gd_city';
	return $public_query_vars;
}


function add_page_id_in_query_var(){
	global $wp_query;
	
	$page_id = $wp_query->get_queried_object_id();
	
	if(!get_query_var('page_id')){
		$wp_query->set( 'page_id', $page_id );
	}
	
	return $wp_query;
}



/**
 * Ragister Custom Post Status
 **/
// This will add a new post status in the system called: Virtual

function geodir_custom_post_status(){
	
	// Vertual Page Status
	register_post_status( 'virtual', array(
		'label'                     => _x( 'Virtual', 'page',GEODIRECTORY_TEXTDOMAIN ),
		'public'                    => true,
		'exclude_from_search'       => true,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Virtual <span class="count">(%s)</span>', 'Virtual <span class="count">(%s)</span>' ,GEODIRECTORY_TEXTDOMAIN),
	) );
	
	do_action('geodir_custom_post_status');
}



function geodir_get_term_link($termlink, $term, $taxonomy) {
	return geodir_term_link($termlink, $term, $taxonomy) ; // taxonomy_functions.php
}


function geodir_get_posttype_link($link, $post_type){
	return geodir_posttype_link($link, $post_type); // taxonomy_functions.php
}

/**
 * Exclude Virtual Pages From Pages List
 **/
function exclude_from_wp_list_pages($exclude_array){
	$pages_ids = array();
	$pages_array = get_posts( array('post_type'	=> 'page','post_status'  => 'virtual') );
	foreach($pages_array as $page){
		$pages_ids[] = $page->ID;
	}
	$exclude_array = $exclude_array + $pages_ids;
	return $exclude_array;
}

/**
 * Exclude Virtual Pages From Admin List 
 **/
function geodir_exclude_page( $query ) {
	add_filter('posts_where', 'geodir_exclude_page_where',100);
	return $query;
}

function geodir_exclude_page_where($where){
	global $wpdb;
	if(is_admin())
		$where .= " AND $wpdb->posts.post_status != 'virtual'";
	
	return $where;	
}

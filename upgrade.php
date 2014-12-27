<?php 
global $wpdb;

if(get_option(GEODIRECTORY_TEXTDOMAIN.'_db_version') != GEODIRECTORY_VERSION){
	include_once('geodirectory-admin/admin_db_install.php');
	add_action( 'plugins_loaded', 'geodirectory_upgrade_all' );
	update_option( GEODIRECTORY_TEXTDOMAIN.'_db_version',  GEODIRECTORY_VERSION );
}


function geodirectory_upgrade_all(){
	geodir_create_tables();
	geodir_update_review_db();
	geodir_upgrade_128();
}
// 1.2.8 Upgrades

function geodir_upgrade_128(){
global $wpdb,$plugin_prefix;

}



















function geodir_update_review_db(){
global $wpdb,$plugin_prefix;	
// Add columns to review table
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_status', 'INT(11) DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_date', 'DATETIME NOT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_city', 'varchar(30) NULL DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_region', 'varchar(30) NULL DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_country', 'varchar(30) NULL DEFAULT NULL');
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_latitude', 'varchar(20) NULL DEFAULT NULL');
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_longitude', 'varchar(20) NULL DEFAULT NULL');
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'comment_content', 'TEXT NULL DEFAULT NULL');

	// this should not be needed anymore becasue of geodir_fix_review_location()
	/*$reviews = $wpdb->get_results("SELECT * FROM ".GEODIR_REVIEW_TABLE." WHERE post_city='' OR post_city IS NULL OR post_latitude='' OR post_latitude IS NULL");
	foreach($reviews as $review){
	$location = $wpdb->get_row("SELECT * FROM ".$plugin_prefix.$review->post_type."_detail WHERE post_id=".$review->post_id);
	$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEW_TABLE." gdr SET gdr.post_city=%s, gdr.post_region=%s , gdr.post_country=%s , gdr.post_latitude=%s, gdr.post_longitude=%s WHERE gdr.id=%d",$location->post_city,$location->post_region,$location->post_country,$review->id,$location->post_latitude,$location->post_longitude));
	}*/
	
geodir_fix_review_date();
geodir_fix_review_post_status();
geodir_fix_review_content();
geodir_fix_review_location();
}

function geodir_fix_review_date(){
	global $wpdb;
	$wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN $wpdb->comments c ON gdr.comment_id=c.comment_ID SET gdr.post_date = c.comment_date WHERE gdr.post_date='0000-00-00 00:00:00'");
}

function geodir_fix_review_post_status(){
	global $wpdb;
	$wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN $wpdb->posts p ON gdr.post_id=p.ID SET gdr.post_status = 1 WHERE gdr.post_status IS NULL AND p.post_status='publish'");
}

function geodir_fix_review_content(){
	global $wpdb;
	if($wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN $wpdb->comments c ON gdr.comment_id=c.comment_ID SET gdr.comment_content = c.comment_content WHERE gdr.comment_content IS NULL")){
	return true;	
	}else{
	return false;	
	}
}

function geodir_fix_review_location(){
	global $wpdb;
	
	$all_postypes = geodir_get_posttypes();
	
	if(!empty($all_postypes))
		{
			foreach($all_postypes as $key)
			{
			// update each GD CTP
			
			$wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN ".$wpdb->prefix."geodir_".$key."_detail d ON gdr.post_id=d.post_id SET gdr.post_latitude = d.post_latitude, gdr.post_longitude = d.post_longitude, gdr.post_city = d.post_city,  gdr.post_region=d.post_region, gdr.post_country=d.post_country WHERE gdr.post_latitude IS NULL OR gdr.post_city IS NULL");
				
			}
			return true;
		}
	return false;	
}

function geodir_fix_review_overall_rating(){
	global $wpdb;
	
	$all_postypes = geodir_get_posttypes();
	
	if(!empty($all_postypes))
		{
			foreach($all_postypes as $key)
			{
			// update each GD CTP
			$reviews = $wpdb->get_results("SELECT post_id FROM ".$wpdb->prefix."geodir_".$key."_detail d");
					if(!empty($reviews)){
						foreach($reviews as $post_id){
						geodir_update_postrating($post_id);
						}
			
					  }
				
			}
			
		}
}
















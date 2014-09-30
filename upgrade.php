<?php 
global $wpdb;

if(get_option('geodir_db_version') != GEODIRECTORY_VERSION){
	function upgrade($gd_db_version){
		if($gd_db_version < '3.0.2')
			upgrade_302();
	}
	
	upgrade(get_option('geodir_db_version'));
}

function upgrade_302(){


}

// 1.1.9 Upgrades

if(is_admin() && !get_option('geodir_upgrade_1.1.9')){
geodir_upgrade_119();
}
function geodir_upgrade_119(){
global $wpdb;
// Add columns to review table
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_status', 'INT(11) DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_date', 'DATETIME NOT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_city', 'varchar(30) NULL DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_region', 'varchar(30) NULL DEFAULT NULL');	
geodir_add_column_if_not_exist(GEODIR_REVIEW_TABLE,'post_country', 'varchar(30) NULL DEFAULT NULL');

// Now the the columns are added let's update the reviews.
geodir_update_review_db();


// ADD CODE TO ADD OPTION 'geodir_upgrade_1.1.9'
update_option( 'geodir_upgrade_1.1.9', 1 );
}


function geodir_update_review_db(){
global $wpdb,$plugin_prefix;	
	$reviews = $wpdb->get_results("SELECT * FROM ".GEODIR_REVIEW_TABLE." WHERE post_city='' OR post_city IS NULL");
	
	foreach($reviews as $review){
	$location = $wpdb->get_row("SELECT * FROM ".$plugin_prefix.$review->post_type."_detail WHERE post_id=".$review->post_id);
	$wpdb->query($wpdb->prepare("UPDATE ".GEODIR_REVIEW_TABLE." gdr SET gdr.post_city=%s, gdr.post_region=%s , gdr.post_country=%s WHERE gdr.id=%d",$location->post_city,$location->post_region,$location->post_country,$review->id));
		
	}
	
geodir_fix_review_date();
geodir_fix_review_post_status();
}

function geodir_fix_review_date(){
	global $wpdb;
	$wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN $wpdb->comments c ON gdr.comment_id=c.comment_ID SET gdr.post_date = c.comment_date WHERE gdr.post_date='0000-00-00 00:00:00'");
}
function geodir_fix_review_post_status(){
	global $wpdb;
	$wpdb->query("UPDATE ".GEODIR_REVIEW_TABLE." gdr JOIN $wpdb->posts p ON gdr.post_id=p.ID SET gdr.post_status = 1 WHERE gdr.post_status IS NULL AND p.post_status='publish'");
}

















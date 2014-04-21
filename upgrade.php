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
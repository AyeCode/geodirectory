<?php  

if(isset($_REQUEST['create_field']))
{ include_once( geodir_plugin_path(). '/geodirectory-admin/option-pages/create_field.php'); die; }


if(isset($_REQUEST['ajax_action']) && $_REQUEST['ajax_action'] != '')
{ 
	switch($_REQUEST['ajax_action']):
		case 'get_cat_dl':
			geodir_get_categories_dl($_REQUEST['post_type'],$_REQUEST['selected'],false,true);
		break;
	endswitch;	
}
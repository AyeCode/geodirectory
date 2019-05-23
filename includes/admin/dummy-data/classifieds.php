<?php
/**
 * GD Classifieds dummy data.
 *
 * @since 2.0.0.59
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'http://localhost/classifieds/'; // CDN URL will be faster

// Set the dummy categories
$dummy_categories  = array();

//Icons from https://www.shareicon.net
$dummy_categories['electronics'] = array(
	'name'        => __( 'Electronics', 'geodirectory'),
	'icon'        => $dummy_image_url . 'icons/cable.png',
	'font_icon'   => 'fas fa-award',
	'color'       => '#62ab43',
);
$dummy_categories['vehicles'] = array(
	'name'        => __( 'Vehicles', 'geodirectory'),
	'icon'        => $dummy_image_url . 'icons/car.png',
	'font_icon'   => 'fas fa-baby',
	'color'       => '#1e73be',
);
$dummy_categories['furniture'] = array(
	'name'        => __( 'Furniture', 'geodirectory'),
	'icon'        => $dummy_image_url . 'icons/winter.png',
	'font_icon'   => 'fas fa-adn',
	'color'       => '#eeee22',
);
$dummy_categories['phones'] = array(
	'name'        => __( 'Mobile Phones', 'geodirectory'),
	'icon'        => $dummy_image_url . 'icons/rotate.png',
	'font_icon'   => 'fas fa-archive',
	'color'       => '#84612d',
);

// Set any custom fields
$dummy_custom_fields = GeoDir_Admin_Dummy_Data::extra_custom_fields($post_type); // set extra default fields

// Set any sort fields
$dummy_sort_fields = array();

// date added
$dummy_sort_fields[]  = array(
	'post_type' 				=> $post_type,
	'data_type' 				=> '',
	'field_type' 				=> 'datetime',
	'frontend_title' 		=> __('Newest','geodirectory'),
	'htmlvar_name' 			=> 'post_date',
	'sort' 							=> 'desc',
	'is_active' 				=> '1',
	'is_default' 				=> '1',
);

// title
$dummy_sort_fields[]  = array(
	'post_type' 				=> $post_type,
	'data_type' 				=> 'VARCHAR',
	'field_type' 				=> 'text',
	'frontend_title' 		=> __('Title','geodirectory'),
	'htmlvar_name' 			=> 'post_title',
	'sort' 							=> 'asc',
	'is_active' 				=> '1',
	'is_default' 				=> '0',
);

// price
$dummy_sort_fields[]  = array(
	'post_type' 				=> $post_type,
	'data_type' 				=> 'FLOAT',
	'field_type' 				=> 'text',
	'frontend_title' 		=> __('Price','geodirectory'),
	'htmlvar_name' 			=> 'price',
	'sort' 							=> 'asc',
	'is_active' 				=> '1',
	'is_default' 				=> '0',
);

// rating
$dummy_sort_fields[]  = array(
	'post_type' 				=> $post_type,
	'data_type' 				=> 'VARCHAR',
	'field_type' 				=> 'float',
	'frontend_title' 		=> __('Rating','geodirectory'),
	'htmlvar_name' 			=> 'overall_rating',
	'sort' 							=> 'desc',
	'is_active' 				=> '1',
	'is_default' 				=> '0',
);

// Set dummy posts
$dummy_posts   = array();

//phones
$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('Samsung S10+','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/phone1.jpg",
		"$dummy_image_url/images/phone2.jpg",
		"$dummy_image_url/images/phone3.jpg",
		"$dummy_image_url/images/phone4.jpg",
		"$dummy_image_url/images/phone5.jpg"
	),
	"post_category" 			=> array( __('Mobile Phones','geodirectory') ),
	"post_tags" 					=> array( 'samsung', 'smart phone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '450',
	"property_status" 		=> 'Used',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Samsung Note",
	"post_images"   			=> array(
		"$dummy_image_url/images/phone2.jpg",
		"$dummy_image_url/images/phone1.jpg",
		"$dummy_image_url/images/phone3.jpg",
		"$dummy_image_url/images/phone4.jpg",
		"$dummy_image_url/images/phone5.jpg"
	),
	"post_category" 			=> array( __('Mobile Phones','geodirectory') ),
	"post_tags" 					=> array( 'samsung', 'smart phone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '450',
	"property_status" 		=> 'Used',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Huawei P10",
	"post_images"   			=> array(
		"$dummy_image_url/images/phone3.jpg",
		"$dummy_image_url/images/phone2.jpg",
		"$dummy_image_url/images/phone1.jpg",
		"$dummy_image_url/images/phone4.jpg",
		"$dummy_image_url/images/phone5.jpg"
	),
	"post_category" 			=> array( __('Mobile Phones','geodirectory') ),
	"post_tags" 					=> array( 'huawei', 'smart phone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '650',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "iPhone X",
	"post_images"   			=> array(
		"$dummy_image_url/images/phone4.jpg",
		"$dummy_image_url/images/phone2.jpg",
		"$dummy_image_url/images/phone3.jpg",
		"$dummy_image_url/images/phone1.jpg",
		"$dummy_image_url/images/phone5.jpg"
	),
	"post_category" 			=> array( __('Mobile Phones','geodirectory') ),
	"post_tags" 					=> array( 'iphones', 'smart phone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '850',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Huawei P10 Lite",
	"post_images"   			=> array(
		"$dummy_image_url/images/phone5.jpg",
		"$dummy_image_url/images/phone2.jpg",
		"$dummy_image_url/images/phone3.jpg",
		"$dummy_image_url/images/phone4.jpg",
		"$dummy_image_url/images/phone1.jpg"
	),
	"post_category" 			=> array( __('Mobile Phones','geodirectory') ),
	"post_tags" 					=> array( 'huawei', 'smart phone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '250',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

//furniture
$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('5 SEATER FABRIC RECLINER SOFA','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/fun1.jpg",
		"$dummy_image_url/images/fun2.jpg",
		"$dummy_image_url/images/fun3.jpg",
		"$dummy_image_url/images/fun4.jpg"
	),
	"post_category" 			=> array( __('Furniture','geodirectory') ),
	"post_tags" 					=> array( 'seats', 'SOFA' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '900',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('Pure Mahogany Chest of Drawers','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/fun2.jpg",
		"$dummy_image_url/images/fun1.jpg",
		"$dummy_image_url/images/fun3.jpg",
		"$dummy_image_url/images/fun4.jpg"
	),
	"post_category" 			=> array( __('Furniture','geodirectory') ),
	"post_tags" 					=> array( 'Drawers' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '360',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=>  __('Dressing table','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/fun3.jpg",
		"$dummy_image_url/images/fun2.jpg",
		"$dummy_image_url/images/fun1.jpg",
		"$dummy_image_url/images/fun4.jpg"
	),
	"post_category" 			=> array(  __('Furniture','geodirectory')  ),
	"post_tags" 					=> array(),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '75',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('Tv cabinet','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/fun4.jpg",
		"$dummy_image_url/images/fun2.jpg",
		"$dummy_image_url/images/fun3.jpg",
		"$dummy_image_url/images/fun1.jpg"
	),
	"post_category" 			=> array( __('Furniture','geodirectory') ),
	"post_tags" 					=> array( 'cabinet' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '5',
	"property_status" 		=> 'New',
	'property_features' 	=> '',
	"post_dummy" 					=> '1'
);

//vehicles
$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('Isuzu dmax pick up local hiace shark Premio hilux','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/car1.jpg",
		"$dummy_image_url/images/car2.jpg",
		"$dummy_image_url/images/car3.jpg"
	),
	"post_category" 			=> array( __('Vehicles','geodirectory') ),
	"post_tags" 					=> array( 'Isuzu', 'pick up' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '950',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Mileage: 6000 Miles
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> __('SUZUKI SWIFT','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/car2.jpg",
		"$dummy_image_url/images/car1.jpg",
		"$dummy_image_url/images/car3.jpg"
	),
	"post_category" 			=> array( __('Vehicles','geodirectory') ),
	"post_tags" 					=> array( 'SUZUKI', 'car' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '570',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Mileage: 6000 Miles
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=>  __('Nissan note new shape head lamps','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/car3.jpg",
		"$dummy_image_url/images/car2.jpg",
		"$dummy_image_url/images/car1.jpg"
	),
	"post_category" 			=> array(  __('Vehicles','geodirectory')  ),
	"post_tags" 					=> array( 'Nissan', 'suv', ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '3400',
	"property_status" 		=> 'New',
	'property_features' 	=> '
		Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=>   __('Toyota Harrier','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/car1.jpg",
		"$dummy_image_url/images/car2.jpg",
		"$dummy_image_url/images/car3.jpg"
	),
	"post_category" 			=> array(  __('Vehicles','geodirectory')  ),
	"post_tags" 					=> array( 'Toyota', 'Harrier', ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '2000',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=>   __('Range Rover Sport','geodirectory'),
	"post_images"   			=> array(
		"$dummy_image_url/images/car3.jpg",
		"$dummy_image_url/images/car1.jpg",
		"$dummy_image_url/images/car2.jpg"
	),
	"post_category" 			=> array(  __('Vehicles','geodirectory')  ),
	"post_tags" 					=> array( 'sports car', 'Range Rover', ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '5000',
	"property_status" 		=> 'New',
	'property_features' 	=> '
		Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);


//electronics
$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Professional Shotgun Condenser Microphone",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv4.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'Microphone' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '35',
	"property_status" 		=> 'New',
	'property_features' 	=> '
		Technology: Bluetooth
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Charge 3 JBL Bluetooth speaker",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv4.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'Bluetooth', 'speaker', 'JBL'),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '60',
	"property_status" 		=> 'New',
	'property_features' 	=> '
		Technology: Bluetooth
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Tcl 32inches brand new",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv4.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'television', 'Tcl', 'flat screen' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '100',
	"property_status" 		=> 'New',
	'property_features' 	=> '
		Screen Size: 32 inch
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> '73" LG smart Digital TV 2019',
	"post_images"   			=> array(
		"$dummy_image_url/images/tv4.jpg",
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv1.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'Digital TV', 'LG', 'smart TV' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '360',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Screen Size: 73 inch
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Skyworth  55inch smart digital led tv 4K led tv",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv4.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'television', '4k', 'Skyworth', 'flat screen' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '250',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Screen Size: 55 inch
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Wall Mounts",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv4.jpg",
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv3.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'accessories', 'samsung', 'television' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '130',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Screen Range: 21-48 inch
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_posts[] 					= array(
	"post_type" 					=> $post_type,
	"post_title" 					=> "Samsung HD TV 32",
	"post_images"   			=> array(
		"$dummy_image_url/images/tv2.jpg",
		"$dummy_image_url/images/tv1.jpg",
		"$dummy_image_url/images/tv3.jpg",
		"$dummy_image_url/images/tv4.jpg"
	),
	"post_category" 			=> array( __('Electronics','geodirectory') ),
	"post_tags" 					=> array( 'samsung', 'television' ),
	"video" 							=> '',
	"phone" 							=> '(111) 677-4444',
	"email" 							=> 'info@example.com',
	"website" 						=> 'http://example.com/',
	"twitter" 						=> 'http://example.com/',
	"facebook" 						=> 'http://example.com/',
	"price" 							=> '260',
	"property_status" 		=> 'Used',
	'property_features' 	=> '
		Screen Size: 32 inch
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
		Another Feature: Value
	',
	"post_dummy" 					=> '1'
);

$dummy_post_content = "
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet fringilla metus vitae tincidunt. Nullam porttitor porta ex, quis fringilla elit viverra vel. Pellentesque vitae orci ut mi tincidunt varius. Praesent sed leo tincidunt lacus porttitor laoreet. Proin molestie erat a vestibulum lobortis. Nullam tincidunt elit sem, non fermentum nisl convallis at. Vivamus eu diam dapibus, tempor lorem in, vestibulum est. Pellentesque venenatis pellentesque dapibus. Donec dapibus ac est a volutpat.

Nulla vel nisi bibendum, scelerisque velit in, auctor magna. In hac habitasse platea dictumst. Cras nec augue vitae odio tincidunt consequat. Vivamus mattis eu magna a commodo. Fusce vel massa quam. Vivamus ligula turpis, dignissim pretium leo non, aliquet euismod tellus. Donec tincidunt convallis diam nec convallis. Vestibulum sed diam euismod ex porttitor imperdiet et non lectus. Pellentesque blandit ex vitae pretium dictum. Mauris eu lorem eu ex auctor eleifend aliquam eget turpis. Aliquam erat volutpat. Proin et sapien a magna mollis pharetra.

Maecenas pulvinar neque risus, nec feugiat elit hendrerit quis. Phasellus ut nibh vitae eros suscipit consectetur. Etiam tristique felis quis metus venenatis sollicitudin. Sed orci lorem, fringilla at finibus eget, vehicula eget ipsum. Etiam egestas feugiat nisi ut lobortis. Maecenas tellus risus, consequat nec pellentesque eget, dictum eu libero. Etiam accumsan risus ac justo pretium, at malesuada urna facilisis. Vestibulum sit amet vulputate diam. Nulla metus dui, ultricies sed dui sed, porttitor mollis erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit.

Praesent eget molestie dolor. Nulla pretium cursus ipsum molestie laoreet. Morbi efficitur quis neque sit amet ornare. Pellentesque fringilla sem eu nibh tristique rhoncus. Morbi id eros nec ligula interdum ultricies at tincidunt mi. Mauris ac accumsan ante. Integer sem eros, mollis eu sapien sit amet, facilisis porta arcu.

Aliquam erat volutpat. Suspendisse quis nulla sodales sapien varius ultrices eu at felis. Suspendisse pretium pellentesque leo ac ornare. Nam tristique turpis id neque venenatis ullamcorper. Nulla venenatis, sem a fringilla hendrerit, libero augue venenatis ex, eget consectetur augue urna eu ipsum. Donec faucibus sit amet arcu vitae imperdiet. Suspendisse suscipit ac nunc ultrices tristique. Duis risus risus, tincidunt ut consectetur ac, congue vel sem. Quisque quis erat quis odio mattis aliquet nec ut urna.
";

foreach( $dummy_posts as $key => $args) {
	$dummy_posts[$key]['post_content'] = $dummy_post_content;
}

function geodir_extra_custom_fields_classifieds( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	foreach( $fields as $key => $field ){
		$fields[$key]['show_in'] .= ',[listing]';
	}

	// price
    $fields[] 						= array(
		'post_type' 					=> $post_type,
		'field_type'					=> 'text',
		'data_type'						=> 'FLOAT',
		'decimal_point'				=> '2',
		'admin_title'					=> 'Price',
		'frontend_title'			=> 'Price',
		'frontend_desc'				=> 'Enter the price in $ (no currency symbol)',
		'htmlvar_name'				=> 'price',
		'is_active'						=> true,
		'for_admin_use'				=> false,
		'default_value'				=> '',
		'option_values' 			=> '',
		'show_in'							=> '[detail],[listing]',
		'is_required'					=> false,
		'validation_pattern'	=> addslashes_gpc( '\d+(\.\d{2})?' ), // add slashes required
		'validation_msg'			=> 'Please enter number and decimal only e.g: 100.50',
		'required_msg'				=> '',
		'field_icon'					=> 'fas fa-dollar-sign',
		'css_class'						=> '',
		'cat_sort'						=> true,
		'cat_filter'					=> true,
		'extra'								=> array(
			'is_price'                  => 1,
			'thousand_separator'        => 'comma',
			'decimal_separator'         => 'period',
			'decimal_display'           => 'if',
			'currency_symbol'           => '$',
			'currency_symbol_placement' => 'left'
		),
		'show_on_pkg' 				=> $package,
		'clabels' 						=> 'Price'
    );

	// property status
	$fields[] = array(
		'post_type' 			=> $post_type,
		'data_type' 			=> 'VARCHAR',
		'field_type' 			=> 'select',
		'field_type_key' 	=> 'select',
		'is_active' 			=> 1,
		'for_admin_use' 	=> 0,
		'is_default' 			=> 0,
		'admin_title' 		=> __('Condition', 'geodirectory'),
		'frontend_desc' 	=> __('Property condition.', 'geodirectory'),
		'frontend_title' 	=> __('Condition', 'geodirectory'),
		'htmlvar_name' 		=> 'property_status',
		'default_value' 	=> 'used',
		'is_required' 		=> '1',
		'required_msg' 		=> '',
		'show_in'   			=> '[detail],[listing]',
		'show_on_pkg' 		=> $package,
		'option_values' 	=> 'Select Status/,New,Used',
		'field_icon' 			=> 'fas fa-home',
		'css_class' 			=> '',
		'cat_sort' 				=> 1,
		'cat_filter' 			=> 1,
		'show_on_pkg' 		=> $package,
		'clabels' 				=> 'Property Status'
	);

	// property features
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'textarea',
		'data_type'           => 'TEXT',
		'admin_title'         => __('Features', 'geodirectory'),
		'frontend_title'      => __('Features', 'geodirectory'),
		'frontend_desc'       => __('Provide the features of this property.', 'geodirectory'),
		'htmlvar_name'        => 'property_features',
		'is_active'           => '1',
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[listing]',
		'is_required'         => false,
		'option_values'       => '',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-plus-square',
		'css_class'           => 'gd-comma-list',
		'cat_sort'            => true,
		'cat_filter'	      	=> true,
		'show_on_pkg' 		  	=> $package,
		'clabels' 			  		=> __('Property Features', 'geodirectory')
	);
						  
	return $fields;
}
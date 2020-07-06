<?php
/**
 * GD doctors dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/weed/images';
$dummy_caticon_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/weed/icons';


// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['flower'] = array(
	'name'        => 'Flower',
	'icon'        => $dummy_caticon_url . '/Flower.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['edibles'] = array(
	'name'        => 'Edibles',
	'icon'        => $dummy_caticon_url . '/Edibles.png',
	'font_icon'   => 'fas fa-cookie-bite',
	'color'       => '#254e4e',
);

$dummy_categories['vapour'] = array(
	'name'        => 'Vapour',
	'icon'        => $dummy_caticon_url . '/Vapour.png',
	'font_icon'   => 'fas fa-wind',
	'color'       => '#254e4e',
);

$dummy_categories['concentrates'] = array(
	'name'        => 'Concentrates',
	'icon'        => $dummy_caticon_url . '/Concentrates.png',
	'font_icon'   => 'fas fa-oil-can',
	'color'       => '#254e4e',
);

$dummy_categories['pre-rolles'] = array(
	'name'        => 'Pre-Rolles',
	'icon'        => $dummy_caticon_url . '/Pre-Rolles.png',
	'font_icon'   => 'fas fa-paint-roller',
	'color'       => '#254e4e',
);

$dummy_categories['tinctures'] = array(
	'name'        => 'Tinctures',
	'icon'        => $dummy_caticon_url . '/Tinctures.png',
	'font_icon'   => 'fas fa-water',
	'color'       => '#254e4e',
);

$dummy_categories['capsules'] = array(
	'name'        => 'Capsules',
	'icon'        => $dummy_caticon_url . '/Capsules.png',
	'font_icon'   => 'fas fa-capsules',
	'color'       => '#254e4e',
);

$dummy_categories['topicals'] = array(
	'name'        => 'Topicals',
	'icon'        => $dummy_caticon_url . '/Topicals.png',
	'font_icon'   => 'fas fa-eye-dropper',
	'color'       => '#254e4e',
);


// Set any custom fields
$dummy_custom_fields = array();
$dummy_custom_fields = GeoDir_Admin_Dummy_Data::extra_custom_fields($post_type); // set extra default fields

// Set any sort fields
$dummy_sort_fields = array();

// date added
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => '',
	'field_type' => 'datetime',
	'frontend_title' => __('Newest','geodirectory'),
	'htmlvar_name' => 'post_date',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '1',
);

// rating
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'float',
	'frontend_title' => __('Best Rated','geodirectory'),
	'htmlvar_name' => 'overall_rating',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '0',
);

// Set dummy posts
$dummy_posts = array();

//dummy post 1
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Stark Weed Industries',
	"post_content" => 'Stark Weed Industries is a delivery service in Los Angeles, we deliver to Hollywood, West Hollywood, Korea Town, USC, Santa Monica, West Los Angeles, Glendale, Silverlake, Eagler Rock areas. Our mission is to provide TOP SHELF HIGH QUALITY products with the BEST PRICES available. We take pride in taking care of our costumers and pack each order with outstanding knowledge and care.  Your happiness is OURS! ',
	"post_category" => array( 'Edibles', 'Pre-Rolles', 'Capsules' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'stark@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('Cash Only', 'Veteran Discount', 'On-Site Smoking', '21+ Only', 'Online Ordering'),
	"special_offers" => "10% OFF on your first purchase.",

	"post_images"   => array(
		"$dummy_image_url/wr-1.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 2
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Oceanic Weed lines',
	"post_content" => 'Oceanic Weed lines here, we are Express Delivery service in Los Angeles  Due to a High call Volume please send your I\'d picture with delivery Address. Your happiness is OURS! ',
	"post_category" => array( 'Flower', 'Tinctures', 'Topicals' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'oceanic@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('Cash Only', 'Veteran Discount', 'Debit Cards', '18+ Only', 'Terminally ill Discount'),
	"special_offers" => "10% OFF on your 3rd purchase.",
	"post_images"   => array(
		"$dummy_image_url/wr-2.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 3
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Oscorp Shop',
	"post_content" => 'Welcome to Oscorp Shop. Our medical delivery is dedicated to bringing affordable alternative care to our patients with speedy service. We offer some of the best organically grown California strains, an extensive list of edibles, concentrates, and other items to meet all of our patients needs including indoor hydro products. Our staff are trained and knowledgeable about our medical products to guide you through your experience to fulfill your specific needs. The quality of care, service and products provided by our company comes directly from our long standing history in medicine and customer service. Oscorp Shop is partnered with a restaurant and pharmacy group in its desire tohelp those who suffer from diseases such as arthritis, cancer, epilepsy, and other debilitating illnesses. Oscorp Shop\'s sole directive is to help those who choose non-addictive, natural remedies in order to live a higher productive way of life. We hope to hear from you soon.',
	"post_category" => array( 'Concentrates', 'Tinctures', 'Capsules' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'oscorp@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Recreational' ),
	"store_features" => array('Terminally ill Discount', 'On-Site ATM', 'Adult Use', 'Pickup', 'Security'),
	"special_offers" => "50% OFF on your first purchase, max 10$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-3.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 4
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Yubaba Weed and Stuff',
	"post_content" => 'Yubaba Weed and Stuff rock the city Since 2005, Short ETA all deliveries are beetween 45 to 90 minutes. Visit our website for daily deals and online ordering, $40 minimum',
	"post_category" => array( 'Concentrates', 'Tinctures', 'Capsules' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'yubaba@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Recreational', 'Medical' ),
	"store_features" => array('Wheelchair Accessible', 'Security', 'Online Ordering', 'Delivery Only', 'Credit Cards'),
	"special_offers" => "First Time Patients ONE PRE ROLL FOR FREE!!",
	"post_images"   => array(
		"$dummy_image_url/wr-4.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 5
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Pide Piper Hub',
	"post_content" => 'Get Your California Medical Cannabis Recommendation Online Easy Process! No appointment necessary! Lowest Price! Only $35 and Only Billed if Approved!',
	"post_category" => array( 'Concentrates', 'Tinctures', 'Capsules' ),
	"post_tags" => array( 'Medical', 'quality', 'capsules' ),
	"email" => 'piper@example.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Medical' ),
	"store_features" => array('Medical Use', 'On-Site ATM', 'Adult Use', 'On-Site Smoking', 'Cash Only'),
	"special_offers" => "10% OFF on your first purchase, max 100$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-5.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 6
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Hooper\'s Store',
	"post_content" => 'Hollywood High grade is a city of Los Angeles PRE-ICO Prop D compliant collective, and takes pride in providing patients with safe access to medicinal marijuana.',
	"post_category" => array( 'Tinctures', 'Concentrates', 'Vapour' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'hooper@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Recreational' ),
	"store_features" => array('Cash Only', 'Veteran Discount', 'Terminally ill Discount', '18+ Allowed', 'On-Site ATM'),
	"special_offers" => "5% OFF on all purchase during June 2020.",

	"post_images"   => array(
		"$dummy_image_url/wr-6.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 7
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Globex Weeds',
	"post_content" => 'Globex Weeds here, we are Express Delivery service in Los Angeles  Due to a High call Volume please send your I\'d picture with delivery Address. Your happiness is OURS! ',
	"post_category" => array( 'Flower', 'Edibles', 'Vapour' ),
	"post_tags" => array( 'Recreational', 'Medical', 'Vapour', 'edible', 'candies' ),
	"email" => 'globex@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array( '21+ Only', 'Cash Only', 'Veteran Discount', 'Debit Cards', 'Security', 'On-Site Smoking'),
	"special_offers" => "50% OFF on first 10 customers.",
	"post_images"   => array(
		"$dummy_image_url/wr-7.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 8
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Weyland Yutani',
	"post_content" => 'Welcome to Weyland Yutani. Our medical delivery is dedicated to bringing affordable alternative care to our patients with speedy service. We offer some of the best organically grown California strains, an extensive list of edibles, concentrates, and other items to meet all of our patients needs including indoor hydro products. Our staff are trained and knowledgeable about our medical products to guide you through your experience to fulfill your specific needs. The quality of care, service and products provided by our company comes directly from our long standing history in medicine and customer service. Oscorp Shop is partnered with a restaurant and pharmacy group in its desire tohelp those who suffer from diseases such as arthritis, cancer, epilepsy, and other debilitating illnesses. Oscorp Shop\'s sole directive is to help those who choose non-addictive, natural remedies in order to live a higher productive way of life. We hope to hear from you soon.',
	"post_category" => array( 'Concentrates', 'Tinctures', 'Pre-Rolles' ),
	"post_tags" => array( 'Recreational', 'Medical', 'rolles', 'tinctures' ),
	"email" => 'weyland@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Medical' ),
	"store_features" => array('Debit Cards', 'Credit Cards', 'Cash Only', 'On-Site ATM', 'Security'),
	"special_offers" => "50% OFF on your first purchase, max 50$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-8.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 9
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Relaxi Weed and Stuff',
	"post_content" => 'Relaxi Weed and Stuff rock the city Since 2005, Short ETA all deliveries are beetween 45 to 90 minutes. Visit our website for daily deals and online ordering, $40 minimum',
	"post_category" => array( 'Capsules', 'Topicals', 'Flower' ),
	"post_tags" => array( 'Recreational', 'Medical', 'flower', 'bags' ),
	"email" => 'relaxi@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('Wheelchair Accessible', 'Online Ordering', 'Delivery Only', 'Veteran Discount', 'Pickup'),
	"special_offers" => "First Time Patients ONE PRE ROLL FOR FREE!!",
	"post_images"   => array(
		"$dummy_image_url/wr-9.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 10
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Pearson Specter Joint',
	"post_content" => 'Get Your California Medical Cannabis Recommendation Online Easy Process! No appointment necessary! Lowest Price! Only $35 and Only Billed if Approved!',
	"post_category" => array( 'Edibles', 'Vapour', 'Concentrates' ),
	"post_tags" => array( 'Medical', 'quality', 'capsules', 'concentrate', 'quality' ),
	"email" => 'psj@example.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('18+ Allowed', 'Terminally ill Discount', 'Security', 'On-Site Smoking', 'Cash Only', 'On-Site ATM'),
	"special_offers" => "5% OFF on your first purchase, max 100$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-10.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 11
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Parker Weed Shop',
	"post_content" => 'Parker Weed Shop is a delivery service in Los Angeles, we deliver to Hollywood, West Hollywood, Korea Town, USC, Santa Monica, West Los Angeles, Glendale, Silverlake, Eagler Rock areas. Our mission is to provide TOP SHELF HIGH QUALITY products with the BEST PRICES available. We take pride in taking care of our costumers and pack each order with outstanding knowledge and care.  Your happiness is OURS! ',
	"post_category" => array( 'Flower', 'Edibles', 'Vapour', 'Concentrates' ),
	"post_tags" => array( 'Recreational', 'Medical', 'vapor', 'candy' ),
	"email" => 'parker@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Recreational' ),
	"store_features" => array('Debit Cards', 'Credit Cards', 'On-Site ATM', 'Wheelchair Accessible', 'Online Ordering'),
	"special_offers" => "10% OFF on your second purchase.",

	"post_images"   => array(
		"$dummy_image_url/wr-11.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 12
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Rekall High Grade',
	"post_content" => 'Rekall High Grade here, we are Express Delivery service in Los Angeles  Due to a High call Volume please send your I\'d picture with delivery Address. Your happiness is OURS! ',
	"post_category" => array( 'Pre-Rolles', 'Tinctures', 'Capsules' ),
	"post_tags" => array( 'Recreational', 'Medical', 'capsule', 'tablets' ),
	"email" => 'rekall@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('Delivery Only', 'Veteran Discount', 'Medical Use', 'Adult Use', 'Pickup'),
	"special_offers" => "10% OFF on your 3rd purchase.",
	"post_images"   => array(
		"$dummy_image_url/wr-12.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 13
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Springfield Plants',
	"post_content" => 'Welcome to Springfield Plants. Our medical delivery is dedicated to bringing affordable alternative care to our patients with speedy service. We offer some of the best organically grown California strains, an extensive list of edibles, concentrates, and other items to meet all of our patients needs including indoor hydro products. Our staff are trained and knowledgeable about our medical products to guide you through your experience to fulfill your specific needs. The quality of care, service and products provided by our company comes directly from our long standing history in medicine and customer service. Springfield Plants is partnered with a restaurant and pharmacy group in its desire tohelp those who suffer from diseases such as arthritis, cancer, epilepsy, and other debilitating illnesses. Springfield Plants\'s sole directive is to help those who choose non-addictive, natural remedies in order to live a higher productive way of life. We hope to hear from you soon.',
	"post_category" => array( 'Topicals', 'Flower', 'Vapour', 'Concentrates', 'Pre-Rolles
' ),
	"post_tags" => array( 'Recreational', 'Medical', 'candy', 'rolles', '100% nature' ),
	"email" => 'spring@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('18+ Allowed', 'On-Site ATM', 'Adult Use', 'On-Site Smoking', 'Security'),
	"special_offers" => "50% OFF on your first purchase, max 10$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-13.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 14
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'The Very Big Weed store of America',
	"post_content" => 'The Very Big Weed store of America rock the city Since 2005, Short ETA all deliveries are beetween 45 to 90 minutes. Visit our website for daily deals and online ordering, $40 minimum',
	"post_category" => array( 'Flower', 'Tinctures', 'Capsules' ),
	"post_tags" => array( 'Recreational', 'Medical', 'flower', 'herbs', 'weed' ),
	"email" => 'tvbwsa@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Recreational', 'Medical' ),
	"store_features" => array('Debit Cards', 'Credit Cards', 'On-Site ATM', 'Veteran Discount' ),
	"special_offers" => "First Time Patients ONE PRE ROLL FOR FREE!!",
	"post_images"   => array(
		"$dummy_image_url/wr-14.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 15
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'The Estelle Leonard Farmacy',
	"post_content" => 'Get Your California Medical Cannabis Recommendation Online Easy Process! No appointment necessary! Lowest Price! Only $35 and Only Billed if Approved!',
	"post_category" => array( 'Edibles', 'Vapour', 'Concentrates' ),
	"post_tags" => array( 'Medical', 'quality', 'capsules' ),
	"email" => 'estelle@example.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Medical' ),
	"store_features" => array('Medical Use', 'On-Site ATM', 'Adult Use', 'On-Site Smoking', 'Cash Only'),
	"special_offers" => "10% OFF on your first purchase, max 100$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-15.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 16
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Stratton Oakmont Lab',
	"post_content" => 'Hollywood High grade is a city of Los Angeles PRE-ICO Prop D compliant collective, and takes pride in providing patients with safe access to medicinal marijuana.',
	"post_category" => array( 'Pre-Rolles', 'Tinctures', 'Capsules', 'Topicals' ),
	"post_tags" => array( 'Recreational', 'Medical' ),
	"email" => 'stratton@industries.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Recreational' ),
	"store_features" => array('Cash Only', 'Veteran Discount', 'Terminally ill Discount', '18+ Allowed', 'On-Site ATM'),
	"special_offers" => "5% OFF on all purchase during June 2020.",

	"post_images"   => array(
		"$dummy_image_url/wr-16.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 17
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'The Bluth Dispensary',
	"post_content" => 'In The Bluth Dispensary we are Express Delivery service in Los Angeles  Due to a High call Volume please send your I\'d picture with delivery Address. Your happiness is OURS! ',
	"post_category" => array( 'Topicals', 'Pre-Rolles', 'Concentrates' ),
	"post_tags" => array( 'Recreational', 'Medical', 'Vapour', 'edible', 'candies' ),
	"email" => 'bluth@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array( 'On-Site Smoking', 'Cash Only', 'Veteran Discount', 'Debit Cards', 'Security', 'On-Site Smoking'),
	"special_offers" => "50% OFF on first 10 customers.",
	"post_images"   => array(
		"$dummy_image_url/wr-17.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);


//dummy post 18
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Sterling Cannabis',
	"post_content" => 'Welcome to Sterling Cannabis. Our medical delivery is dedicated to bringing affordable alternative care to our patients with speedy service. We offer some of the best organically grown California strains, an extensive list of edibles, concentrates, and other items to meet all of our patients needs including indoor hydro products. Our staff are trained and knowledgeable about our medical products to guide you through your experience to fulfill your specific needs. The quality of care, service and products provided by our company comes directly from our long standing history in medicine and customer service. Oscorp Shop is partnered with a restaurant and pharmacy group in its desire tohelp those who suffer from diseases such as arthritis, cancer, epilepsy, and other debilitating illnesses. Oscorp Shop\'s sole directive is to help those who choose non-addictive, natural remedies in order to live a higher productive way of life. We hope to hear from you soon.',
	"post_category" => array( 'Concentrates', 'Tinctures', 'Pre-Rolles', 'Topicals', 'Vapour' ),
	"post_tags" => array( 'Recreational', 'Medical', 'rolles', 'tinctures' ),
	"email" => 'sterling@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Medical' ),
	"store_features" => array('Debit Cards', 'Credit Cards', 'Cash Only', 'On-Site ATM', 'Security'),
	"special_offers" => "50% OFF on your first purchase, max 50$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-18.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 19
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Tyrell Collectives',
	"post_content" => 'Tyrell Collectives rock the city Since 2005, Short ETA all deliveries are beetween 45 to 90 minutes. Visit our website for daily deals and online ordering, $40 minimum',
	"post_category" => array( 'Capsules', 'Topicals', 'Flower', 'Topicals', 'Edibles' ),
	"post_tags" => array( 'Recreational', 'Medical', 'flower', 'bags' ),
	"email" => 'tyrell@weed.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 1,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('Wheelchair Accessible', 'Online Ordering', 'Delivery Only', 'Veteran Discount', 'Pickup'),
	"special_offers" => "First Time Patients ONE PRE ROLL FOR FREE!!",
	"post_images"   => array(
		"$dummy_image_url/wr-19.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 20
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Paper Street Remedy Company',
	"post_content" => 'Get Your California Medical Cannabis Recommendation Online Easy Process! No appointment necessary! Lowest Price! Only $35 and Only Billed if Approved!',
	"post_category" => array( 'Flower', 'Edibles', 'Vapour', 'Concentrates', 'Pre-Rolles', 'Tinctures', 'Capsules', 'Topicals' ),
	"post_tags" => array( 'Medical', 'quality', 'capsules', 'concentrate', 'quality' ),
	"email" => 'psrcompany@example.com',
	"website" => 'http://example.com/',
	"for_online_orders" => 0,
	"use_recreational_medical" => array( 'Both' ),
	"store_features" => array('18+ Allowed', 'Terminally ill Discount', 'Security', 'On-Site Smoking', 'Cash Only', 'On-Site ATM'),
	"special_offers" => "5% OFF on your first purchase, max 100$ discount",
	"post_images"   => array(
		"$dummy_image_url/wr-20.jpg",
		"$dummy_image_url/wrp-1.jpg",
		"$dummy_image_url/wrp-2.jpg",
		"$dummy_image_url/wrp-3.jpg",
		"$dummy_image_url/wrp-4.jpg"
	),
	"post_dummy" => '1'
);

function geodir_extra_custom_fields_weed( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

    // Online Orders
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TINYINT', 
		'field_type' => 'radio', 
		'field_type_key' => 'for_online_orders', 
		'admin_title' => __('Online Orders', 'geodirectory'), 
		'frontend_desc' => __('Tick "Yes" if your store send products via online.', 'geodirectory'), 
		'frontend_title' => __('Online Orders?', 'geodirectory'), 
		'htmlvar_name' => 'for_online_orders', 
		'sort_order' => '0',
		'option_values' => 'Yes/1,No/0',
		'clabels' => __('Online Orders?', 'geodirectory'), 
		'is_active' => '1',
		'field_icon' => 'fas fa-phone-volume'
	);

	// Recreational or Medical
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'select',
		'field_type_key' => 'select',
		'is_active' => 1,
		'for_admin_use' => 0,
		'is_default' => 0,
		'admin_title' => __('Recreational or Medical', 'geodirectory'),
		'frontend_desc' => __('Recreational or Medical.', 'geodirectory'),
		'frontend_title' => __('Recreational or Medical', 'geodirectory'),
		'htmlvar_name' => 'use_recreational_medical',
		'default_value' => '',
		'is_required' => '1',
		'required_msg' => __('Recreational or Medical', 'geodirectory'),
		'show_on_pkg' => $package,
		'option_values' => __( 'Recreational, Medical, Both', 'geodirectory'),
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_on_pkg' => $package,
		'clabels' => __('Recreational or Medical', 'geodirectory'),
	);


	// store features
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'multiselect',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Store Features', 'geodirectory'),
		'frontend_title'      => __('Store Features', 'geodirectory'),
		'frontend_desc'       => __('Select the store features.', 'geodirectory'),
		'htmlvar_name'        => 'store_features',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => false,
		'option_values'       => 'Debit Cards, Credit Cards, Cash Only, On-Site ATM, Wheelchair Accessible, Online Ordering, Delivery Only, Veteran Discount, Medical Use, Adult Use, Pickup, 18+ Allowed, 21+ Only, Terminally ill Discount, Security, On-Site Smoking',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-store',
		'css_class'           => 'gd-comma-list',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Store Features'
	);

							  
	return $fields;
}

// Dummy page templates
$dummy_page_templates['archive_item'] = "[gd_archive_item_section type='open' position='left']
[gd_post_badge key='featured' condition='is_not_empty' badge='FEATURED' bg_color='#fd4700' txt_color='#ffffff' css_class='gd-ab-top-left-angle gd-badge-shadow']
[gd_post_badge key='video' condition='is_not_empty' icon_class='fas fa-video' badge='Video' link='%%input%%' bg_color='#0073aa' txt_color='#ffffff' list_hide_secondary='2' css_class='gd-badge-shadow gd-ab-top-right gd-lity']

[gd_post_images type='image' ajax_load='true' link_to='post' types='logo,post_images']
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']

[gd_author_actions author_page_only='1']

[gd_post_fav show='' alignment='right' list_hide_secondary='2']
[gd_post_rating alignment='block' list_hide_secondary='2']

[gd_post_meta key='website' alignment='right' text_alignment='left']
[gd_post_meta key='use_recreational_medical' alignment='left' text_alignment='left']

[gd_post_meta key='post_category' alignment='block' text_alignment='left']
[gd_post_meta key='store_features' alignment='block' text_alignment='left']
[gd_post_meta key='special_offers' alignment='block' text_alignment='left']
[gd_post_badge key='for_online_orders' condition='is_not_empty' icon_class='fas fa-phone-volume' badge='Online Order: %%input%%' bg_color='#19be00' txt_color='#ffffff' alignment='block']

[gd_post_content key='post_content' limit='60']
[gd_archive_item_section type='close' position='right']";
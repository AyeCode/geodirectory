<?php
/**
 * GD Car Sales dummy data.
 *
 * @since 2.0.0.88
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/classifieds/'; // CDN URL will be faster

// Set the dummy categories
$dummy_categories = array();

$dummy_categories['suvs'] = array(
	'name'      => __( 'SUVs', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/electronics.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#fcdc1f',
);
$dummy_categories['trucks']      = array(
	'name'        => __( 'Trucks', 'geodirectory' ),
	'icon'        => $dummy_image_url . 'icons/phones.png',
	'font_icon'   => 'fas fa-car',
	'color'       => '#fb9e05',
	'parent-name' => __( 'Electronics', 'geodirectory' )
);
$dummy_categories['crossovers']         = array(
	'name'        => __( 'Crossovers', 'geodirectory' ),
	'icon'        => $dummy_image_url . 'icons/tvs.png',
	'font_icon'   => 'fas fa-car',
	'color'       => '#f44e3b',
	'parent-name' => __( 'Electronics', 'geodirectory' )
);

$dummy_categories['sedans'] = array(
	'name'      => __( 'Sedans', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/instruments.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#009ce0',
);
$dummy_categories['coupes']     = array(
	'name'      => __( 'Coupes', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/guitars.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#15a5a5',
);

$dummy_categories['convertibles'] = array(
	'name'      => __( 'Convertibles', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/furniture.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#6abd21',
);
$dummy_categories['luxury']      = array(
	'name'      => __( 'Luxury', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/beds.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#ab3b9e',
);
$dummy_categories['sports-cars']      = array(
	'name'      => __( 'Sports Cars', 'geodirectory' ),
	'icon'      => $dummy_image_url . 'icons/beds.png',
	'font_icon' => 'fas fa-car',
	'color'     => '#ab3b9e',
);


// Set any custom fields
$dummy_custom_fields = GeoDir_Admin_Dummy_Data::extra_custom_fields( $post_type ); // set extra default fields

// Set any sort fields
$dummy_sort_fields = array();

// date added
$dummy_sort_fields[] = array(
	'post_type'      => $post_type,
	'data_type'      => '',
	'field_type'     => 'datetime',
	'frontend_title' => __( 'Newest', 'geodirectory' ),
	'htmlvar_name'   => 'post_date',
	'sort'           => 'desc',
	'is_active'      => '1',
	'is_default'     => '1',
);

// title
$dummy_sort_fields[] = array(
	'post_type'      => $post_type,
	'data_type'      => 'VARCHAR',
	'field_type'     => 'text',
	'frontend_title' => __( 'Title', 'geodirectory' ),
	'htmlvar_name'   => 'post_title',
	'sort'           => 'asc',
	'is_active'      => '1',
	'is_default'     => '0',
);

// price
$dummy_sort_fields[] = array(
	'post_type'      => $post_type,
	'data_type'      => 'FLOAT',
	'field_type'     => 'text',
	'frontend_title' => __( 'Price', 'geodirectory' ),
	'htmlvar_name'   => 'price',
	'sort'           => 'asc',
	'is_active'      => '1',
	'is_default'     => '0',
);

// rating
$dummy_sort_fields[] = array(
	'post_type'      => $post_type,
	'data_type'      => 'VARCHAR',
	'field_type'     => 'float',
	'frontend_title' => __( 'Rating', 'geodirectory' ),
	'htmlvar_name'   => 'overall_rating',
	'sort'           => 'desc',
	'is_active'      => '1',
	'is_default'     => '0',
);

// Set dummy posts
$dummy_posts = array();

//phones
$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => __( 'Samsung S10+', 'geodirectory' ),
	"post_images"   => array(
		$dummy_image_url . "images/phone1.jpg",
		$dummy_image_url . "images/phone2.jpg",
		$dummy_image_url . "images/phone3.jpg",
		$dummy_image_url . "images/phone4.jpg",
		$dummy_image_url . "images/phone5.jpg"
	),
	"post_category" => array( __( 'Electronics', 'geodirectory' ), __( 'Mobile Phones', 'geodirectory' ) ),
	"post_tags"     => array( 'samsung', 'smart phone' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '450',
	'condition'     => 'New',
	'brand'         => 'Samsung',
	'model'         => 'S10+',
	'seller_type'   => 'Private',
	'payment_types' => 'PayPal,Cash',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => "Samsung Note",
	"post_images"   => array(
		$dummy_image_url . "images/phone2.jpg",
		$dummy_image_url . "images/phone1.jpg",
		$dummy_image_url . "images/phone3.jpg",
		$dummy_image_url . "images/phone4.jpg",
		$dummy_image_url . "images/phone5.jpg"
	),
	"post_category" => array( __( 'Electronics', 'geodirectory' ), __( 'Mobile Phones', 'geodirectory' ) ),
	"post_tags"     => array( 'samsung', 'smart phone' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '450',
	'condition'     => 'Good',
	'brand'         => 'Samsung',
	'model'         => 'Note',
	'seller_type'   => 'Private',
	'payment_types' => 'PayPal,Cash',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => "Huawei P10",
	"post_images"   => array(
		$dummy_image_url . "images/phone3.jpg",
		$dummy_image_url . "images/phone2.jpg",
		$dummy_image_url . "images/phone1.jpg",
		$dummy_image_url . "images/phone4.jpg",
		$dummy_image_url . "images/phone5.jpg"
	),
	"post_category" => array( __( 'Electronics', 'geodirectory' ), __( 'Mobile Phones', 'geodirectory' ) ),
	"post_tags"     => array( 'huawei', 'smart phone' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '650',
	'condition'     => 'New',
	'brand'         => 'Huawei',
	'model'         => 'P10',
	'seller_type'   => 'Trade',
	'payment_types' => 'Cash,Cheque,PayPal,Credit Card,Debit Card',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => "iPhone X",
	"post_images"   => array(
		$dummy_image_url . "images/phone4.jpg",
		$dummy_image_url . "images/phone2.jpg",
		$dummy_image_url . "images/phone3.jpg",
		$dummy_image_url . "images/phone1.jpg",
		$dummy_image_url . "images/phone5.jpg"
	),
	"post_category" => array( __( 'Electronics', 'geodirectory' ), __( 'Mobile Phones', 'geodirectory' ) ),
	"post_tags"     => array( 'iphones', 'smart phone' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '850',
	'condition'     => 'Fair',
	'brand'         => 'Apple',
	'model'         => 'X',
	'seller_type'   => 'Private',
	'payment_types' => 'PayPal',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => "Huawei P10 Lite",
	"post_images"   => array(
		$dummy_image_url . "images/phone5.jpg",
		$dummy_image_url . "images/phone2.jpg",
		$dummy_image_url . "images/phone3.jpg",
		$dummy_image_url . "images/phone4.jpg",
		$dummy_image_url . "images/phone1.jpg"
	),
	"post_category" => array( __( 'Electronics', 'geodirectory' ), __( 'Mobile Phones', 'geodirectory' ) ),
	"post_tags"     => array( 'huawei', 'smart phone' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '250',
	'condition'     => 'Poor',
	'brand'         => 'Huawei',
	'model'         => 'P10 Lite',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash',
	"post_dummy"    => '1'
);

//furniture
$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => __( 'King Sized bed', 'geodirectory' ),
	"post_images"   => array(
		$dummy_image_url . "images/bed1.jpg",
		$dummy_image_url . "images/bed2.jpg",
		$dummy_image_url . "images/bed3.jpg",
		$dummy_image_url . "images/bed4.jpg",
		$dummy_image_url . "images/bed5.jpg",
		$dummy_image_url . "images/bed6.jpg",
	),
	"post_category" => array( __( 'Furniture', 'geodirectory' ), __( 'Beds', 'geodirectory' ) ),
	"post_tags"     => array( 'king size', 'bed' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '900',
	'condition'     => 'New',
	'brand'         => 'Silentnight',
	'model'         => 'Geltex 3000',
	'seller_type'   => 'Trade',
	'payment_types' => 'Cash,Credit Card,Debit Card',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => __( 'Sleepeezee double bed', 'geodirectory' ),
	"post_images"   => array(
		$dummy_image_url . "images/bed2.jpg",
		$dummy_image_url . "images/bed3.jpg",
		$dummy_image_url . "images/bed4.jpg",
		$dummy_image_url . "images/bed5.jpg",
		$dummy_image_url . "images/bed6.jpg",
		$dummy_image_url . "images/bed1.jpg",
	),
	"post_category" => array( __( 'Furniture', 'geodirectory' ), __( 'Beds', 'geodirectory' ) ),
	"post_tags"     => array( 'Drawers' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '360',
	'condition'     => 'New',
	'brand'         => 'Sleepeezee',
	'model'         => 'Easy sleep 2000',
	'seller_type'   => 'Trade',
	'payment_types' => 'Credit Card,Debit Card',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => __( 'Slumber 1000', 'geodirectory' ),
	"post_images"   => array(
		$dummy_image_url . "images/bed3.jpg",
		$dummy_image_url . "images/bed4.jpg",
		$dummy_image_url . "images/bed5.jpg",
		$dummy_image_url . "images/bed6.jpg",
		$dummy_image_url . "images/bed1.jpg",
		$dummy_image_url . "images/bed2.jpg",
	),
	"post_category" => array( __( 'Furniture', 'geodirectory' ), __( 'Beds', 'geodirectory' ) ),
	"post_tags"     => array(),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '750',
	'condition'     => 'New',
	'brand'         => 'Slumberland',
	'model'         => 'Slumber 1000',
	'seller_type'   => 'Trade',
	'payment_types' => 'PayPal,Cash,Credit Card,Debit Card',
	"post_dummy"    => '1'
);

$dummy_posts[] = array(
	"post_type"     => $post_type,
	"post_title"    => __( 'Slumber 5000', 'geodirectory' ),
	"post_images"   => array(
		$dummy_image_url . "images/bed2.jpg",
		$dummy_image_url . "images/bed3.jpg",
		$dummy_image_url . "images/bed4.jpg",
		$dummy_image_url . "images/bed5.jpg",
		$dummy_image_url . "images/bed6.jpg",
		$dummy_image_url . "images/bed1.jpg",
	),
	"post_category" => array( __( 'Furniture', 'geodirectory' ), __( 'Beds', 'geodirectory' ) ),
	"post_tags"     => array( 'bed' ),
	"video"         => '',
	"phone"         => '(111) 677-4444',
	"email"         => 'info@example.com',
	"price"         => '5000',
	'condition'     => 'New',
	'brand'         => 'Slumberland',
	'model'         => 'Slumber 5000',
	'seller_type'   => 'Trade',
	'payment_types' => 'Credit Card,Debit Card',
	"post_dummy"    => '1'
);

// instruments
$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => __( 'Gibson Worn Bourbon', 'geodirectory' ),
	"post_images"       => array(
		$dummy_image_url . "images/guitar1.jpg",
		$dummy_image_url . "images/guitar2.jpg",
		$dummy_image_url . "images/guitar3.jpg",
		$dummy_image_url . "images/guitar4.jpg",
		$dummy_image_url . "images/guitar5.jpg",
		$dummy_image_url . "images/guitar6.jpg",
	),
	"post_category"     => array( __( 'Instruments', 'geodirectory' ),__( 'Guitars', 'geodirectory' ) ),
	"post_tags"         => array( 'Gibson', 'Les Paul' ),
	"video"             => 'https://www.youtube.com/watch?v=pEOnUxj9Esg',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '599',
	'condition'     => 'Fair',
	'brand'         => 'Gibson',
	'model'         => 'Les Paul Faded 2018 Worn Bourbon',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash,PayPal',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => __( 'Gibson 2018 Mojave Burst', 'geodirectory' ),
	"post_images"       => array(
		$dummy_image_url . "images/guitar6.jpg",
		$dummy_image_url . "images/guitar1.jpg",
		$dummy_image_url . "images/guitar2.jpg",
		$dummy_image_url . "images/guitar3.jpg",
		$dummy_image_url . "images/guitar4.jpg",
		$dummy_image_url . "images/guitar5.jpg",
	),
	"post_category"     => array( __( 'Instruments', 'geodirectory' ),__( 'Guitars', 'geodirectory' ) ),
	"post_tags"         => array( 'SUZUKI', 'car' ),
	"video"             => 'https://www.youtube.com/watch?v=pEOnUxj9Esg',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '1799',
	'condition'     => 'New',
	'brand'         => 'Gibson',
	'model'         => 'Les Paul 2018 Mojave Burst',
	'seller_type'   => 'Trade',
	'payment_types' => 'Cash,Cheque,PayPal,Credit Card,Debit Card',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => __( 'Fender Jazzmaster', 'geodirectory' ),
	"post_images"       => array(
		$dummy_image_url . "images/guitar5.jpg",
		$dummy_image_url . "images/guitar6.jpg",
		$dummy_image_url . "images/guitar1.jpg",
		$dummy_image_url . "images/guitar2.jpg",
		$dummy_image_url . "images/guitar3.jpg",
		$dummy_image_url . "images/guitar4.jpg",
	),
	"post_category"     => array( __( 'Instruments', 'geodirectory' ),__( 'Guitars', 'geodirectory' ) ),
	"post_tags"         => array( 'Fender' ),
	"video"             => 'https://www.youtube.com/watch?v=p8WA0pPzBjE',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '2400',
	'condition'     => 'New',
	'brand'         => 'Fender',
	'model'         => 'Jazzmaster',
	'seller_type'   => 'Trade',
	'payment_types' => 'Cash,Credit Card,Debit Card',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => __( 'Fender Stratocaster', 'geodirectory' ),
	"post_images"       => array(
		$dummy_image_url . "images/guitar4.jpg",
		$dummy_image_url . "images/guitar5.jpg",
		$dummy_image_url . "images/guitar6.jpg",
		$dummy_image_url . "images/guitar1.jpg",
		$dummy_image_url . "images/guitar2.jpg",
		$dummy_image_url . "images/guitar3.jpg",
	),
	"post_category"     => array( __( 'Instruments', 'geodirectory' ),__( 'Guitars', 'geodirectory' ) ),
	"post_tags"         => array( 'Fender', 'Stratocaster', 'Used' ),
	"video"             => 'https://www.youtube.com/watch?v=p8WA0pPzBjE',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '250',
	'condition'     => 'Poor',
	'brand'         => 'Fender',
	'model'         => 'Stratocaster',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => __( 'Yamaha Pacifica 612V', 'geodirectory' ),
	"post_images"       => array(
		$dummy_image_url . "images/guitar3.jpg",
		$dummy_image_url . "images/guitar4.jpg",
		$dummy_image_url . "images/guitar5.jpg",
		$dummy_image_url . "images/guitar6.jpg",
		$dummy_image_url . "images/guitar1.jpg",
		$dummy_image_url . "images/guitar2.jpg",
	),
	"post_category"     => array( __( 'Instruments', 'geodirectory' ),__( 'Guitars', 'geodirectory' ) ),
	"post_tags"         => array( 'Yamaha', 'Used', ),
	"video"             => 'https://www.youtube.com/watch?v=sQ0oRotYW58',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '500',
	'condition'     => 'Good',
	'brand'         => 'Yamaha',
	'model'         => 'Pacifica 612V',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash,PayPal',
	"post_dummy"        => '1'
);


//electronics
$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "Samsung 55 inch tv",
	"post_images"       => array(
		$dummy_image_url . "images/tv1.jpg",
		$dummy_image_url . "images/tv2.jpg",
		$dummy_image_url . "images/tv3.jpg",
		$dummy_image_url . "images/tv4.jpg",
		$dummy_image_url . "images/tv5.jpg",
		$dummy_image_url . "images/tv6.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','55"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '350',
	'condition'     => 'Good',
	'brand'         => 'Samsung',
	'model'         => 'XCT55',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash,PayPal',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "Samsung 65 inch OLED",
	"post_images"       => array(
		$dummy_image_url . "images/tv6.jpg",
		$dummy_image_url . "images/tv1.jpg",
		$dummy_image_url . "images/tv2.jpg",
		$dummy_image_url . "images/tv3.jpg",
		$dummy_image_url . "images/tv4.jpg",
		$dummy_image_url . "images/tv5.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','65"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '1150',
	'condition'     => 'Fair',
	'brand'         => 'Samsung',
	'model'         => 'OLED65',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash,PayPal',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "LG 85 inch LCD",
	"post_images"       => array(
		$dummy_image_url . "images/tv5.jpg",
		$dummy_image_url . "images/tv6.jpg",
		$dummy_image_url . "images/tv1.jpg",
		$dummy_image_url . "images/tv2.jpg",
		$dummy_image_url . "images/tv3.jpg",
		$dummy_image_url . "images/tv4.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','85"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '2150',
	'condition'     => 'New',
	'brand'         => 'LG',
	'model'         => 'LCD85',
	'seller_type'   => 'Trade',
	'payment_types' => 'Cash,Cheque,PayPal,Credit Card,Debit Card',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "LG 42 inch LCD",
	"post_images"       => array(
		$dummy_image_url . "images/tv4.jpg",
		$dummy_image_url . "images/tv5.jpg",
		$dummy_image_url . "images/tv6.jpg",
		$dummy_image_url . "images/tv1.jpg",
		$dummy_image_url . "images/tv2.jpg",
		$dummy_image_url . "images/tv3.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','42"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '150',
	'condition'     => 'Fair',
	'brand'         => 'LG',
	'model'         => 'LCD42',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "LG 80 inch LCD poor condition",
	"post_images"       => array(
		$dummy_image_url . "images/tv3.jpg",
		$dummy_image_url . "images/tv4.jpg",
		$dummy_image_url . "images/tv5.jpg",
		$dummy_image_url . "images/tv6.jpg",
		$dummy_image_url . "images/tv1.jpg",
		$dummy_image_url . "images/tv2.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','80"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '190',
	'condition'     => 'Poor',
	'brand'         => 'LG',
	'model'         => 'LCD80',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash,PayPal',
	"post_dummy"        => '1'
);

$dummy_posts[] = array(
	"post_type"         => $post_type,
	"post_title"        => "LG TV Quick Sale needed",
	"post_images"       => array(
		$dummy_image_url . "images/tv2.jpg",
		$dummy_image_url . "images/tv3.jpg",
		$dummy_image_url . "images/tv4.jpg",
		$dummy_image_url . "images/tv5.jpg",
		$dummy_image_url . "images/tv6.jpg",
		$dummy_image_url . "images/tv1.jpg",
	),
	"post_category"     => array( __( 'Electronics', 'geodirectory' ),__( 'Televisions', 'geodirectory' ) ),
	"post_tags"         => array( 'TV','65"' ),
	"video"             => '',
	"phone"             => '(111) 677-4444',
	"email"             => 'info@example.com',
	"price"             => '200',
	'condition'     => 'Good',
	'brand'         => 'LG',
	'model'         => 'LCD65',
	'seller_type'   => 'Private',
	'payment_types' => 'Cash',
	"post_dummy"        => '1'
);


$dummy_post_content = "
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec aliquet fringilla metus vitae tincidunt. Nullam porttitor porta ex, quis fringilla elit viverra vel. Pellentesque vitae orci ut mi tincidunt varius. Praesent sed leo tincidunt lacus porttitor laoreet. Proin molestie erat a vestibulum lobortis. Nullam tincidunt elit sem, non fermentum nisl convallis at. Vivamus eu diam dapibus, tempor lorem in, vestibulum est. Pellentesque venenatis pellentesque dapibus. Donec dapibus ac est a volutpat.

Nulla vel nisi bibendum, scelerisque velit in, auctor magna. In hac habitasse platea dictumst. Cras nec augue vitae odio tincidunt consequat. Vivamus mattis eu magna a commodo. Fusce vel massa quam. Vivamus ligula turpis, dignissim pretium leo non, aliquet euismod tellus. Donec tincidunt convallis diam nec convallis. Vestibulum sed diam euismod ex porttitor imperdiet et non lectus. Pellentesque blandit ex vitae pretium dictum. Mauris eu lorem eu ex auctor eleifend aliquam eget turpis. Aliquam erat volutpat. Proin et sapien a magna mollis pharetra.

Maecenas pulvinar neque risus, nec feugiat elit hendrerit quis. Phasellus ut nibh vitae eros suscipit consectetur. Etiam tristique felis quis metus venenatis sollicitudin. Sed orci lorem, fringilla at finibus eget, vehicula eget ipsum. Etiam egestas feugiat nisi ut lobortis. Maecenas tellus risus, consequat nec pellentesque eget, dictum eu libero. Etiam accumsan risus ac justo pretium, at malesuada urna facilisis. Vestibulum sit amet vulputate diam. Nulla metus dui, ultricies sed dui sed, porttitor mollis erat. Lorem ipsum dolor sit amet, consectetur adipiscing elit.

Praesent eget molestie dolor. Nulla pretium cursus ipsum molestie laoreet. Morbi efficitur quis neque sit amet ornare. Pellentesque fringilla sem eu nibh tristique rhoncus. Morbi id eros nec ligula interdum ultricies at tincidunt mi. Mauris ac accumsan ante. Integer sem eros, mollis eu sapien sit amet, facilisis porta arcu.

Aliquam erat volutpat. Suspendisse quis nulla sodales sapien varius ultrices eu at felis. Suspendisse pretium pellentesque leo ac ornare. Nam tristique turpis id neque venenatis ullamcorper. Nulla venenatis, sem a fringilla hendrerit, libero augue venenatis ex, eget consectetur augue urna eu ipsum. Donec faucibus sit amet arcu vitae imperdiet. Suspendisse suscipit ac nunc ultrices tristique. Duis risus risus, tincidunt ut consectetur ac, congue vel sem. Quisque quis erat quis odio mattis aliquet nec ut urna.
";

foreach ( $dummy_posts as $key => $args ) {
	$dummy_posts[ $key ]['post_content'] = $dummy_post_content;
}

function geodir_extra_custom_fields_classifieds( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// price
	$fields[] = array(
		'post_type'          => $post_type,
		'show_on_pkg'        => $package,
		'field_type'         => 'text',
		'data_type'          => 'FLOAT',
		'decimal_point'      => '2',
		'admin_title'        => 'Price',
		'frontend_title'     => 'Price',
		'frontend_desc'      => 'Enter the price in $ (no currency symbol)',
		'htmlvar_name'       => 'price',
		'is_active'          => true,
		'for_admin_use'      => false,
		'default_value'      => '',
		'option_values'      => '',
		'show_in'            => '[detail]',
		'is_required'        => false,
		'validation_pattern' => addslashes_gpc( '\d+(\.\d{2})?' ), // add slashes required
		'validation_msg'     => 'Please enter number and decimal only e.g: 100.50',
		'required_msg'       => '',
		'field_icon'         => 'fas fa-dollar-sign',
		'css_class'          => '',
		'cat_sort'           => true,
		'cat_filter'         => true,
		'extra'              => array(
			'is_price'                  => 1,
			'thousand_separator'        => 'comma',
			'decimal_separator'         => 'period',
			'decimal_display'           => 'if',
			'currency_symbol'           => '$',
			'currency_symbol_placement' => 'left'
		),
		'clabels'            => 'Price'
	);

	// Condition
	$fields[] = array(
		'post_type'      => $post_type,
		'data_type'      => 'VARCHAR',
		'field_type'     => 'select',
		'field_type_key' => 'select',
		'is_active'      => 1,
		'for_admin_use'  => 0,
		'is_default'     => 0,
		'admin_title'    => __( 'Condition', 'geodirectory' ),
		'frontend_desc'  => __( 'Condition.', 'geodirectory' ),
		'frontend_title' => __( 'Condition', 'geodirectory' ),
		'htmlvar_name'   => 'condition',
		'default_value'  => 'used',
		'is_required'    => '1',
		'required_msg'   => '',
		'show_in'        => '[detail]',
		'show_on_pkg'    => $package,
		'option_values'  => 'Select Status/,New,Good,Fair,Poor',
		'field_icon'     => 'fas fa-search',
		'css_class'      => '',
		'cat_sort'       => 1,
		'cat_filter'     => 1,
		'clabels'        => __( 'Condition', 'geodirectory' )
	);

	// Brand
	$fields[] = array(
		'post_type'      => $post_type,
		'data_type'      => 'VARCHAR',
		'field_type'     => 'select',
		'field_type_key' => 'select',
		'is_active'      => 1,
		'for_admin_use'  => 0,
		'is_default'     => 0,
		'admin_title'    => __( 'Brand', 'geodirectory' ),
		'frontend_desc'  => __( 'Please select the brand.', 'geodirectory' ),
		'frontend_title' => __( 'Brand', 'geodirectory' ),
		'htmlvar_name'   => 'brand',
		'default_value'  => '',
		'is_required'    => '',
		'required_msg'   => '',
		'show_in'        => '[detail]',
		'show_on_pkg'    => $package,
		'option_values'  => 'Select Brand/,Samsung,LG,Huawei,Apple,Silentnight,Sleepeezee,Slumberland,Fender,Gibson,Yamaha',
		'field_icon'     => 'fas fa-box',
		'css_class'      => '',
		'cat_sort'       => 1,
		'cat_filter'     => 1,
		'clabels'        => __( 'Brand', 'geodirectory' )
	);

	// Model
	$fields[] = array(
		'post_type'      => $post_type,
		'data_type'      => 'VARCHAR',
		'field_type'     => 'text',
		'is_active'      => 1,
		'for_admin_use'  => 0,
		'is_default'     => 0,
		'admin_title'    => __( 'Model', 'geodirectory' ),
		'frontend_desc'  => __( 'Please select the model.', 'geodirectory' ),
		'frontend_title' => __( 'Model', 'geodirectory' ),
		'htmlvar_name'   => 'model',
		'default_value'  => '',
		'is_required'    => '',
		'required_msg'   => '',
		'show_in'        => '[detail]',
		'show_on_pkg'    => $package,
		'field_icon'     => 'fas fa-box-open',
		'css_class'      => '',
		'cat_sort'       => 1,
		'cat_filter'     => 1,
		'clabels'        => __( 'Model', 'geodirectory' )
	);

	// Seller Type
	$fields[] = array(
		'post_type'      => $post_type,
		'data_type'      => 'VARCHAR',
		'field_type'     => 'select',
		'field_type_key' => 'select',
		'is_active'      => 1,
		'for_admin_use'  => 0,
		'is_default'     => 0,
		'admin_title'    => __( 'Seller Type', 'geodirectory' ),
		'frontend_desc'  => __( 'Please select if you are a private or trade seller.', 'geodirectory' ),
		'frontend_title' => __( 'Seller Type', 'geodirectory' ),
		'htmlvar_name'   => 'seller_type',
		'default_value'  => '',
		'is_required'    => '1',
		'required_msg'   => '',
		'show_in'        => '[detail]',
		'show_on_pkg'    => $package,
		'option_values'  => 'Select Status/,Private,Trade',
		'field_icon'     => 'fas fa-user',
		'css_class'      => '',
		'cat_sort'       => 1,
		'cat_filter'     => 1,
		'clabels'        => __( 'Seller Type', 'geodirectory' )
	);

	// Payment Types
	$fields[] = array(
		'post_type'          => $post_type,
		'field_type'         => 'multiselect',
		'data_type'          => 'VARCHAR',
		'admin_title'        => __( 'Payment Types', 'geodirectory' ),
		'frontend_title'     => __( 'Payment Types', 'geodirectory' ),
		'frontend_desc'      => __( 'Select the accepted payment types.', 'geodirectory' ),
		'htmlvar_name'       => 'payment_types',
		'is_active'          => true,
		'for_admin_use'      => false,
		'default_value'      => '',
		'show_in'            => '[detail],[listing]',
		'is_required'        => false,
		'option_values'      => 'Cash,Cheque,PayPal,Credit Card,Debit Card',
		'validation_pattern' => '',
		'validation_msg'     => '',
		'required_msg'       => '',
		'field_icon'         => 'far fa-money-bill-alt',
		'css_class'          => 'gd-comma-list',
		'cat_sort'           => true,
		'cat_filter'         => true,
		'show_on_pkg'        => $package,
		'clabels'            => 'Payment Types'
	);

	return $fields;
}

// Dummy page templates
$dummy_page_templates['archive_item'] = "[gd_archive_item_section type='open' position='left']
[gd_post_badge key='featured' condition='is_not_empty' badge='FEATURED' bg_color='#fd4700' txt_color='#ffffff' css_class='gd-ab-top-left-angle gd-badge-shadow']
[gd_post_badge key='video' condition='is_not_empty' icon_class='fas fa-video' badge='Video' link='%%input%%' bg_color='#0073aa' txt_color='#ffffff' list_hide_secondary='2' css_class='gd-badge-shadow gd-ab-top-right gd-lity']
[gd_post_badge key='price' condition='is_not_empty'  badge='%%input%%' bg_color='#f70c0c' txt_color='#ffffff' css_class='gd-ab-bottom-right']
[gd_post_badge key='seller_type' condition='is_equal' search='Trade' icon_class='fas fa-user-tie' badge='Trade' bg_color='#46c40d' txt_color='#ffffff' list_hide='5' list_hide_secondary='3' css_class='gd-ab-bottom-left']
[gd_post_badge key='seller_type' condition='is_equal' search='Private' icon_class='fas fa-user' badge='Private' bg_color='#00bf9f' txt_color='#ffffff' list_hide='5' list_hide_secondary='3' css_class='gd-ab-bottom-left']
[gd_post_images type='image' ajax_load='true' link_to='post' types='logo,post_images']
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']
[gd_post_badge key='brand' condition='is_not_empty' badge='%%input%%' bg_color='#1664b1' txt_color='#ffffff' alignment='left']
[gd_post_badge key='model' condition='is_not_empty' badge='%%input%%' bg_color='#4c95de' txt_color='#ffffff' alignment='left']
[gd_post_badge key='condition' condition='is_equal' search='New' badge='Condition: %%input%%' bg_color='#19be00' txt_color='#ffffff' alignment='left']
[gd_post_badge key='condition' condition='is_equal' search='Good' badge='Condition: %%input%%' bg_color='#d9d702' txt_color='#ffffff' alignment='left']
[gd_post_badge key='condition' condition='is_equal' search='Fair' badge='Condition: %%input%%' bg_color='#df8d00' txt_color='#ffffff' alignment='left']
[gd_post_badge key='condition' condition='is_equal' search='Poor' badge='Condition: %%input%%' bg_color='#c93a0d' txt_color='#ffffff' alignment='left']
[gd_post_badge key='payment_types' condition='is_contains' search='PayPal' icon_class='fab fa-paypal' badge='Accepts PayPal' bg_color='#0076c1' txt_color='#ffffff' alignment='left' list_hide_secondary='3']
[gd_author_actions author_page_only='1']
[gd_post_distance]
[gd_post_rating alignment='left' list_hide_secondary='2']
[gd_post_fav show='' alignment='right' list_hide_secondary='2']
[gd_post_meta key='business_hours' location='listing' list_hide_secondary='2']
[gd_output_location location='listing']
[gd_post_content key='post_content' limit='60' max_height='120']
[gd_archive_item_section type='close' position='right']";




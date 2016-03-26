<?php
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['SERVER_NAME'] = '';
$PHP_SELF = $GLOBALS['PHP_SELF'] = $_SERVER['PHP_SELF'] = '/index.php';

define( 'GD_USE_PHP_SESSIONS', false );
define( 'GD_TESTING_MODE', true );

$_tests_dir = getenv( 'WP_TESTS_DIR' );

$is_selenium_test = getenv( 'IS_SELENIUM_TEST' );

if ( ! $is_selenium_test ) {
	$is_selenium_test = false;
} else {
	$is_selenium_test = true;
}

if ( ! $_tests_dir ) {
	$_tests_dir = dirname( __FILE__ )  . '/lib/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';
require $_tests_dir . '/includes/bootstrap.php';

if ( $is_selenium_test ) {
	require dirname( __FILE__ ) . '/selenium/base.php';
}

function _manually_load_plugin() {
	require dirname( __FILE__ ) . '/../geodirectory.php';
}
if ( ! $is_selenium_test ) {
	tests_add_filter('muplugins_loaded', '_manually_load_plugin');
}

function place_dummy_image_url($url) {
	$gd_dummy_base_url = getenv( 'GD_DUMMY_BASE_URL' );
	if ($gd_dummy_base_url) {
		return $gd_dummy_base_url;
	} else {
		return $url;
	}
}
if ( ! $is_selenium_test ) {
	tests_add_filter('place_dummy_image_url', 'place_dummy_image_url');
}

function place_dummy_cat_image_url($url) {
	$gd_dummy_base_url = getenv( 'GD_DUMMY_BASE_URL' );
	if ($gd_dummy_base_url) {
		return $gd_dummy_base_url."/cat_icon";
	} else {
		return $url;
	}
}
if ( ! $is_selenium_test ) {
	tests_add_filter('place_dummy_cat_image_url', 'place_dummy_cat_image_url');
}

if ( ! $is_selenium_test ) {
	global $current_user;
	$current_user = new WP_User(1);
	$current_user->set_role('administrator');
	wp_update_user(array('ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User'));
//Add subscriber
	wp_create_user('testuser', '12345', 'testuser@test.com');

	echo "Activating GeoDirectory...\n";
	activate_plugin('geodirectory/geodirectory.php');

	echo "Installing GeoDirectory...\n";
	geodir_install();

	echo "Setting default location...\n";
	$location_args = array(
			'city' => 'New York',
			'region' => 'New York',
			'country' => 'United States',
			'geo_lat' => '40.7127837',
			'geo_lng' => '-74.00594130000002',
			'is_default' => '1',
			'update_city' => '0'
	);

	geodir_add_new_location($location_args);

	echo "Installing dummy data...\n";

	global $geodir_post_custom_fields_cache;
	$geodir_post_custom_fields_cache = array();

//geodir_delete_dummy_posts();

	global $dummy_post_index, $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2;

	$dummy_post_index = 1;
	$city_bound_lat1 = 40.4960439;
	$city_bound_lng1 = -74.2557349;
	$city_bound_lat2 = 40.91525559999999;
	$city_bound_lng2 = -73.7002721;

	geodir_insert_dummy_posts();
	test_create_dummy_posts();

}

function print_mail($data) {
	print_r($data);
	return $data;
}

function test_create_dummy_posts($max = 10) {
//	$i = 1;
//	while($i <= 10) {
//		$_REQUEST['insert_dummy_post_index'] = $i;
//		$_REQUEST['city_bound_lat1'] = 40.4960439;
//		$_REQUEST['city_bound_lng1'] = -74.2557349;
//		$_REQUEST['city_bound_lat2'] = 40.91525559999999;
//		$_REQUEST['city_bound_lng2'] = -73.7002721;
//
//		$_REQUEST['geodir_autofill'] = 'geodir_dummy_insert';
//		$_REQUEST['posttype'] = 'gd_place';
//		$_REQUEST['_wpnonce'] = wp_create_nonce('geodir_dummy_posts_insert_noncename');;
//		geodir_ajax_handler();
//
////	geodir_insert_dummy_posts();
//		$i++;
//	}
//
//	unset($_REQUEST['insert_dummy_post_index']);
//	unset($_REQUEST['city_bound_lat1']);
//	unset($_REQUEST['city_bound_lng1']);
//	unset($_REQUEST['city_bound_lat2']);
//	unset($_REQUEST['city_bound_lng2']);
//	unset($_REQUEST['geodir_autofill']);
//	unset($_REQUEST['posttype']);
//	unset($_REQUEST['_wpnonce']);

	$i = 2;
	while($i <= $max) {
		global $dummy_post_index, $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2;

		$dummy_post_index = $i;
		$city_bound_lat1 = 40.4960439;
		$city_bound_lng1 = -74.2557349;
		$city_bound_lat2 = 40.91525559999999;
		$city_bound_lng2 = -73.7002721;

		include dirname( __FILE__ ) . '/../geodirectory-admin/place_dummy_post.php';
		$i++;
	}
}

function generateRandomString($length = 10) {
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

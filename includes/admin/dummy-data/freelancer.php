<?php
/**
 * GD Freelancers dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/'; // CDN URL will be faster

// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['back-end'] = array(
	'name'        => 'Back End',
	'icon'        => $dummy_image_url . 'cat_icon/Apartments.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);
$dummy_categories['front-end'] = array(
	'name'        => 'Front End',
	'icon'        => $dummy_image_url . 'cat_icon/Houses.png',
	'font_icon'   => 'fas fa-file-code',
	'color'       => '#5551b9',
);
$dummy_categories['full-stack'] = array(
	'name'        => 'Full Stack',
	'icon'        => $dummy_image_url . 'cat_icon/Commercial.png',
	'font_icon'   => 'fas fa-cubes',
	'color'       => '#852d2d',
);
$dummy_categories['implementer'] = array(
	'name'        => 'Implementer',
	'icon'        => $dummy_image_url . 'cat_icon/Land.png',
	'font_icon'   => 'fas fa-star',
	'color'       => '#84612d',
);
$dummy_categories['seo'] = array(
	'name'        => 'SEO',
	'icon'        => $dummy_image_url . 'cat_icon/Land.png',
	'font_icon'   => 'fas fa-search-plus',
	'color'       => '#84612d',
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

// Hourly Price (least)
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'float',
	'frontend_title' => __('Hourly Price (Least)','geodirectory'),
	'htmlvar_name' => 'hourly_price',
	'sort' => 'asc',
	'is_active' => '1',
	'is_default' => '0',
);

// Hourly Price (most)
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'float',
	'frontend_title' => __('Hourly Price (Most)','geodirectory'),
	'htmlvar_name' => 'hourly_price',
	'sort' => 'desc',
	'is_active' => '1',
	'is_default' => '0',
);


// Set dummy posts
$dummy_posts = array();
// dummy post 1
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Paddy O\'Furniture',
	"post_content" => 'Expert in Backend development, have more than 10+ years of experience.',
	"post_category" => array( 'Back End' ),
	"post_tags" => array( 'mySQL', 'Linux Server' ),
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '100',
	"for_hire" => 1,
	"area_of_expertise" => array( 'PHP' ),
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
);

// dummy post 2
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Mario Speedwagon',
	"post_content" => 'Expert in Frontend development, have more than 15+ years of experience.',
	"post_category" => array( 'Back End' ),
	"post_tags" => array( 'CSS', 'SCSS', 'NODEJS' ),
	"email" => 'hello@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '150',
	"for_hire" => 0,
	"area_of_expertise" => array( 'CSS' ),
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
);

// dummy post 3
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Anna Sthesia',
	"post_content" => 'Expert in SEO, have more than 20+ years of experience.',
	"post_category" => array( 'SEO' ),
	"post_tags" => array( 'SEO', 'Page Index' ),
	"email" => 'se0@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '400',
	"for_hire" => 1,
	"area_of_expertise" => array( 'PHP', 'NodeJS' ),
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
);

// dummy post 4
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Bob Frapples',
	"post_content" => 'Expert in GeoDirectory themes and Plugins, have more than 10+ years of experience in Web Development.',
	"post_category" => array( 'Full Stack' ),
	"post_tags" => array( 'mySQL', 'Linux Server' ),
	"email" => 'bob@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"area_of_expertise" => array( 'PHP', 'NodeJS' ),
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
);

// dummy post 5
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Gail Forcewind',
	"post_content" => 'Expert in Backend development and SEO, have more than 10+ years of experience.',
	"post_category" => array( 'Implementer' ),
	"post_tags" => array( 'mySQL', 'Linux Server', 'implementer' ),
	"email" => 'gail@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '150',
	"for_hire" => 1,
	"area_of_expertise" => array( 'SEO', 'SQL' ),
	"post_images"   => array(
		"$dummy_image_url/ps/psf1.jpg",
		"$dummy_image_url/ps/psl1.jpg",
		"$dummy_image_url/ps/psb1.jpg",
		"$dummy_image_url/ps/psk.jpg",
		"$dummy_image_url/ps/psbr.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
);

function geodir_extra_custom_fields_freelancer( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// EMail
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'text',
		'admin_title' => __('EMail', 'geodirectory'),
		'frontend_desc' => __('You can enter your business or listing email.', 'geodirectory'),
		'frontend_title' => __('EMail', 'geodirectory'),
		'htmlvar_name' => 'email',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'clabels' => __('EMail', 'geodirectory'),
		'is_required' => true,
		'required_msg' => __('Entrer email to get leads!', 'geodirectory'),
	);
	
	// website
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'VARCHAR',
		'admin_title'			=> __('Website', 'geodirectory'),
		'frontend_title'		=> __('Website', 'geodirectory'),
		'frontend_desc'			=> __('You can enter your business or listing website.
', 'geodirectory' ),
		'htmlvar_name'			=> 'website',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'clabels' 				=> 'Website'
    );

    // Hourly rate
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'FLOAT',
		'decimal_point'			=> '2',
		'admin_title'			=> __('Hourly Price', 'geodirectory'),
		'frontend_title'		=> __('Hourly Price', 'geodirectory'),
		'frontend_desc'			=> __('Enter the hourly price in $ (no currency symbol). Example : 40
', 'geodirectory'),
		'htmlvar_name'			=> 'hourly_price',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'is_required'			=> true,
		'required_msg' => __('Please enter your hourly fee', 'geodirectory'),
		'validation_pattern'	=> addslashes_gpc( '\d+(\.\d{2})?' ), // add slashes required
		'validation_msg'		=> 'Please enter number and decimal only ie: 100.50',
		'required_msg'			=> '',
		'field_icon'			=> 'fas fa-dollar-sign',
		'css_class'				=> '',
		'cat_sort'				=> true,
		'cat_filter'			=> true,
		'extra'					=> array(
			'is_price'                  => 1,
			'thousand_separator'        => 'comma',
			'decimal_separator'         => 'period',
			'decimal_display'           => 'if',
			'currency_symbol'           => '$',
			'currency_symbol_placement' => 'left'
		),
		'show_on_pkg' 			=> $package,
		'clabels' 				=> 'hourly_price'
    );

    // For Hire
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TINYINT', 
		'field_type' => 'radio', 
		'field_type_key' => 'for_hire', 
		'admin_title' => __('For Hire?', 'geodirectory'), 
		'frontend_desc' => __('Tick "Yes" if you are avaible for Hire.', 'geodirectory'), 
		'frontend_title' => __('For Hire?', 'geodirectory'), 
		'htmlvar_name' => 'for_hire', 
		'sort_order' => '0',
		'option_values' => 'Yes/1,No/0',
		'clabels' => __('For Hire?', 'geodirectory'), 
		'is_active' => '1',
		'field_icon' => 'fas fa-sitemap'
	);

	
	// Areas of Expertise
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'select',
		'field_type_key' => 'select',
		'is_active' => 1,
		'for_admin_use' => 0,
		'is_default' => 0,
		'admin_title' => __('Areas of Expertise', 'geodirectory'),
		'frontend_desc' => __('Areas of Expertise.', 'geodirectory'),
		'frontend_title' => __('Areas of Expertise', 'geodirectory'),
		'htmlvar_name' => 'areas_of_expertise',
		'default_value' => '',
		'is_required' => '1',
		'required_msg' => __('Select your areas of expertise', 'geodirectory'),
		'show_on_pkg' => $package,
		'option_values' => __('PHP, JS, HTML, CSS, SQL, Installing GD, Setting GD Up, Graphics Design, SEO, Data Entry', 'geodirectory'),
		'field_icon' => 'fas fa-home',
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_on_pkg' => $package,
		'clabels' => __('Areas of Expertise', 'geodirectory'),
	);

	$fields[] = array(
				'post_type' => $post_type,
				'data_type' => 'TEXT',
				'field_type' => 'url',
				'admin_title' => __('Portfolio link 1', 'geodirectory'),
				'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
				'frontend_title' => __('Portfolio link 1', 'geodirectory'),
				'htmlvar_name' => 'portfolio_link_1',
				'default_value' => '',
				'is_active' => '1',
				'option_values' => '',
				'is_default' => '0',
				'show_in' => '[detail]',
				'show_on_pkg' => $package,
				'clabels' => __('Portfolio link 1', 'geodirectory')
			);

	$fields[] = array(
			'post_type' => $post_type,
			'data_type' => 'TEXT',
			'field_type' => 'url',
			'admin_title' => __('Portfolio link 2', 'geodirectory'),
			'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
			'frontend_title' => __('Portfolio link 2', 'geodirectory'),
			'htmlvar_name' => 'portfolio_link_2',
			'default_value' => '',
			'is_active' => '1',
			'option_values' => '',
			'is_default' => '0',
			'show_in' => '[detail]',
			'show_on_pkg' => $package,
			'clabels' => __('Portfolio link 2', 'geodirectory')
		);
	
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 3', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 3', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_3',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 3', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 4', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 4', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_4',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 4', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 5', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 5', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_5',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 5', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 6', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 6', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_6',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 6', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 7', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 7', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_7',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 7', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 8', 'geodirectory'),
					'frontend_desc' => __('Please add any links to GeoDirectory sites you have built. This will help you become a verified expert.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 8', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_8',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '[detail]',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 8', 'geodirectory')
				);
						  
	return $fields;
}
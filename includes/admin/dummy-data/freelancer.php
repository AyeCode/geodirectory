<?php
/**
 * GD Freelancers dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/freelancers/images';
$dummy_caticon_url = 'https://ayecode.b-cdn.net/dummy/freelancers/icons';

// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['back-end'] = array(
	'name'        => 'Back End',
	'icon'        => $dummy_caticon_url . '/back-end.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);
$dummy_categories['front-end'] = array(
	'name'        => 'Front End',
	'icon'        => $dummy_caticon_url . '/front-end.png',
	'font_icon'   => 'fas fa-file-code',
	'color'       => '#5551b9',
);
$dummy_categories['full-stack'] = array(
	'name'        => 'Full Stack',
	'icon'        => $dummy_caticon_url . '/full-stack.png',
	'font_icon'   => 'fas fa-cubes',
	'color'       => '#852d2d',
);
$dummy_categories['implementer'] = array(
	'name'        => 'Implementer',
	'icon'        => $dummy_caticon_url . '/implementer.png',
	'font_icon'   => 'fas fa-star',
	'color'       => '#84612d',
);
$dummy_categories['seo'] = array(
	'name'        => 'SEO',
	'icon'        => $dummy_caticon_url . '/seo.png',
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
	"post_content" => 'We have worked with GeoTheme in the past and GeoDirectory since it’s initial release. We also contributed code to both GeoDirectory and some of its addons.

We can help you design, implement and customize every aspect of your directory. We can develop both GeoDirectory Themes and Add-ons.',
	"post_category" => array( 'Full Stack', 'Front End', 'Back End', 'SEO', 'Implementer' ),
	"post_tags" => array( 'PHP', 'JS', 'SQL', 'CSS', 'HTML' ),
	"email" => 'paddy@paddy.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,JS,SQL,CSS,HTML',
	"post_images"   => array(
		"$dummy_image_url/fr-1.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 2
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Mario Speedwagon',
	"post_content" => 'Contact me if you want to upgrade to GDV2, develop a custom feature, or get a native mobile app for your GDV2 site.

I also provide amazing custom template layouts for GDV2 with builders to really set your site apart, as well as quick setup for new sites..',
	"post_category" => array( 'Back End' ),
	"post_tags" => array( 'CSS', 'SCSS', 'NODEJS' ),
	"email" => 'mario@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '30',
	"for_hire" => 1,
	"areas_of_expertise" =>  'Graphic Design' ,
	"post_images"   => array(
		"$dummy_image_url/fr-2.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 3
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Anna Sthesia',
	"post_content" => 'Allow me to introduce myself, my name is Anna Sthesia, I am a fulltime pro-active WordPress GeoDirectory developer having 6+ years of experience on designing and developing WordPress Geodirectory websites.

I’m friendly, professional, and very passionate about what I do. My promise to you is to deliver the best quality work, in the shortest amount of time, for a price that will fit your budget.

',
	"post_category" => array( 'SEO', 'Implementer' ),
	"post_tags" => array( 'SEO', 'Page Index' ),
	"email" => 'se0@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,NodeJS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-3.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 4
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Bob Frapples',
	"post_content" => 'I have been a WordPress tragic for a long time now. I also am part of the GeoDirectory team since the beginning of that plugin and its add-ons, and my role there is to provide customer support, not only for the GeoDirectory plugins and plugins, but also the WP Invoicing and UsersWP plugins.',
	"post_category" => array( 'Full Stack' ),
	"post_tags" => array( 'mySQL', 'Linux Server' ),
	"email" => 'bob@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,NodeJS',
	"post_images"   => array(
		"$dummy_image_url/fr-4.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 5
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Gail Forcewind',
	"post_content" => 'I can help you with design, implementation and customization aspects of your directory. I can develop both GeoDirectory custom hemes and Add-ons.

Importantly, I Enjoy my job. Just give me a chance to work with you and I will achieve complex functionality and help you to grow your business in the best possible way.',
	"post_category" => array( 'Implementer', 'Back End', 'Front End' ),
	"post_tags" => array( 'mySQL', 'Linux Server', 'implementer' ),
	"email" => 'gail@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '14',
	"for_hire" => 1,
	"areas_of_expertise" => 'SEO,SQL' ,
	"post_images"   => array(
		"$dummy_image_url/fr-5.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 6
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Minnie Van Ryder',
	"post_content" => 'Hello Minnie Van Ryder, web designer, theme developer and founder of example.com, with over 8 years of experience in : UI/UX design, Front-End development, WordPress, I have the creative flair, originality and strong visual sense needed to satisfy the requirements of the most demanding of clients.',
	"post_category" => array( 'Front End', 'SEO', 'Back End' ),
	"post_tags" => array( 'CSS', 'saas', 'scss' ),
	"email" => 'van@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '50',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-6.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 7
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Eileen Sideways',
	"post_content" => 'Expert in SQL development and CSS, have more than 5+ years of experience.',
	"post_category" => array( 'Implementer', 'SEO' ),
	"post_tags" => array( 'SQL', 'saas', 'CSS' ),
	"email" => 'eileen@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '30',
	"for_hire" => 0,
	"areas_of_expertise" => 'SQL,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-7.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 8
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Paige Turner',
	"post_content" => 'Expert in SEO development and CSS, have more than 5+ years of experience.',
	"post_category" => array( 'SEO', 'Back End' ),
	"post_tags" => array( 'SQL', 'SEO' ),
	"email" => 'paige@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"areas_of_expertise" => 'SQL,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-8.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 9
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Wes Yabinlatelee',
	"post_content" => 'Expert in Full stack development, have more than 15+ years of experience.',
	"post_category" => array( 'Full Stack', 'Implementer' ),
	"post_tags" => array( 'css', 'SEO', 'sass', 'html' ),
	"email" => 'paige@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '90',
	"for_hire" => 1,
	"areas_of_expertise" => 'SEO,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-9.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 10
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Karen Onnabit',
	"post_content" => 'Expert in GD Ecosystem development, have more than 3+ years of experience of GD themes and plugins.',
	"post_category" => array( 'Implementer', 'Front End' ),
	"post_tags" => array( 'css', 'SEO', 'sass', 'html' ),
	"email" => 'karen@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '65',
	"for_hire" => 1,
	"areas_of_expertise" => 'GD,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-10.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 11
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Percy Vere',
	"post_content" => 'We have worked with GeoTheme in the past and GeoDirectory since it’s initial release. We also contributed code to both GeoDirectory and some of its addons.

We can help you design, implement and customize every aspect of your directory. We can develop both GeoDirectory Themes and Add-ons.	',
	"post_category" => array( 'Back End', 'SEO', 'Front End' ),
	"post_tags" => array( 'mySQL', 'Linux Server' ),
	"email" => 'info@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '78',
	"for_hire" => 1,
	"areas_of_expertise" =>  'PHP' ,
	"post_images"   => array(
		"$dummy_image_url/fr-11.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 12
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Percy Kewshun',
	"post_content" => 'Expert in Frontend development, have more than 15+ years of experience.',
	"post_category" => array( 'Back End', 'Implementer', 'Front End' ),
	"post_tags" => array( 'CSS', 'SCSS', 'NODEJS' ),
	"email" => 'hello@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '48',
	"for_hire" => 0,
	"areas_of_expertise" => 'CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-12.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 13
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Fay Daway',
	"post_content" => 'Expert in SEO, have more than 20+ years of experience.',
	"post_category" => array( 'SEO', 'Implementer' ),
	"post_tags" => array( 'SEO', 'Page Index' ),
	"email" => 'se0@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '76',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,NodeJS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-13.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 14
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Toi Story',
	"post_content" => 'Expert in GeoDirectory themes and Plugins, have more than 10+ years of experience in Web Development.',
	"post_category" => array( 'Full Stack', 'Back End', 'Front End' ),
	"post_tags" => array( 'mySQL', 'Linux Server' ),
	"email" => 'bob@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '110',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,NodeJS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-14.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 15
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Gene Eva Convenshun',
	"post_content" => 'Expert in Backend development and SEO, have more than 10+ years of experience.',
	"post_category" => array( 'Implementer', 'Front End', 'Full Stack' ),
	"post_tags" => array( 'mySQL', 'Linux Server', 'implementer' ),
	"email" => 'gail@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '150',
	"for_hire" => 1,
	"areas_of_expertise" =>  'SEO,SQL' ,
	"post_images"   => array(
		"$dummy_image_url/fr-15.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 16
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Polly Ester Undawair',
	"post_content" => 'Expert in Frontend development and Sass, have more than 5+ years of experience.',
	"post_category" => array( 'Front End', 'Implementer', 'SEO' ),
	"post_tags" => array( 'CSS', 'saas', 'scss' ),
	"email" => 'van@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '50',
	"for_hire" => 1,
	"areas_of_expertise" => 'PHP,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-16.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 17
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Mike Rowe-Soft',
	"post_content" => 'Expert in SQL development and CSS, have more than 5+ years of experience.',
	"post_category" => array( 'Implementer', 'Front End', 'Back End' ),
	"post_tags" => array( 'SQL', 'saas', 'CSS' ),
	"email" => 'eileen@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '30',
	"for_hire" => 0,
	"areas_of_expertise" => 'SQL,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-17.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 18
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Stanley Knife',
	"post_content" => 'Expert in SEO development and CSS, have more than 5+ years of experience.',
	"post_category" => array( 'SEO', 'Implementer', 'Full Stack' ),
	"post_tags" => array( 'SQL', 'SEO' ),
	"email" => 'paige@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '20',
	"for_hire" => 1,
	"areas_of_expertise" => 'SQL,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-18.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 19
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Clyde Stale',
	"post_content" => 'Expert in Full stack development, have more than 15+ years of experience.',
	"post_category" => array( 'Full Stack', 'Front End', 'Back End', 'SEO', 'Implementer' ),
	"post_tags" => array( 'css', 'SEO', 'sass', 'html' ),
	"email" => 'paige@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '90',
	"for_hire" => 1,
	"areas_of_expertise" => 'SEO,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-19.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

// dummy post 20
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Al Annon',
	"post_content" => 'Expert in GD Ecosystem development, have more than 3+ years of experience of GD themes and plugins.',
	"post_category" => array( 'Full Stack', 'Front End', 'Back End', 'SEO', 'Implementer' ),
	"post_tags" => array( 'css', 'SEO', 'sass', 'html' ),
	"email" => 'karen@example.com',
	"website" => 'http://example.com/',
	"hourly_price" => '65',
	"for_hire" => 1,
	"areas_of_expertise" =>  'JS,CSS' ,
	"post_images"   => array(
		"$dummy_image_url/fr-20.jpg",
		"$dummy_image_url/frp-1.jpg",
		"$dummy_image_url/frp-2.jpg",
		"$dummy_image_url/frp-3.jpg",
		"$dummy_image_url/frp-4.jpg"
	),
	"portfolio_link_1" => 'http://example.com/',
	"portfolio_link_2" => 'http://example.com/',
	"portfolio_link_3" => 'http://example.com/',
	"portfolio_link_4" => 'http://example.com/',
	"portfolio_link_5" => 'http://example.com/',
	"portfolio_link_6" => 'http://example.com/',
	"portfolio_link_7" => 'http://example.com/',
	"portfolio_link_8" => 'http://example.com/',
	"post_dummy" => '1'
);

function geodir_extra_custom_fields_freelancer( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// EMail
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'text',
		'admin_title' => __('Email', 'geodirectory'),
		'frontend_desc' => __('Please enter your contact email address.', 'geodirectory'),
		'frontend_title' => __('Email', 'geodirectory'),
		'htmlvar_name' => 'email',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'clabels' => __('Email', 'geodirectory'),
		'is_required' => true,
		'required_msg' => __('Enter email to get leads!', 'geodirectory'),
	);

	// website
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'VARCHAR',
		'admin_title'			=> __('Website', 'geodirectory'),
		'frontend_title'		=> __('Website', 'geodirectory'),
		'frontend_desc'			=> __('You can enter your business website.', 'geodirectory' ),
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
		'required_msg'          => __('Please enter your hourly rate', 'geodirectory'),
		'validation_pattern'	=> addslashes_gpc( '\d+(\.\d{2})?' ), // add slashes required
		'validation_msg'		=> 'Please enter number and decimal only ie: 100.50',
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
		'admin_title' => __('For Hire?', 'geodirectory'),
		'frontend_desc' => __('Tick "Yes" if you are available for Hire.', 'geodirectory'),
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
		'field_type' => 'multiselect',
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
		'option_values' => 'PHP,JS,HTML,CSS,SQL,UX,UI,NodeJS,Graphic Design,SEO,Data Entry',
		'field_icon' => 'fas fa-home',
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_in' => '[detail]',
		'clabels' => __('Areas of Expertise', 'geodirectory'),
	);

	$fields[] = array(
				'post_type' => $post_type,
				'data_type' => 'TEXT',
				'field_type' => 'url',
				'admin_title' => __('Portfolio link 1', 'geodirectory'),
				'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
				'frontend_title' => __('Portfolio link 1', 'geodirectory'),
				'htmlvar_name' => 'portfolio_link_1',
				'default_value' => '',
				'is_active' => '1',
				'option_values' => '',
				'is_default' => '0',
				'show_in' => '',
				'show_on_pkg' => $package,
				'clabels' => __('Portfolio link 1', 'geodirectory')
			);

	$fields[] = array(
			'post_type' => $post_type,
			'data_type' => 'TEXT',
			'field_type' => 'url',
			'admin_title' => __('Portfolio link 2', 'geodirectory'),
			'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
			'frontend_title' => __('Portfolio link 2', 'geodirectory'),
			'htmlvar_name' => 'portfolio_link_2',
			'default_value' => '',
			'is_active' => '1',
			'option_values' => '',
			'is_default' => '0',
			'show_in' => '',
			'show_on_pkg' => $package,
			'clabels' => __('Portfolio link 2', 'geodirectory')
		);

		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 3', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 3', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_3',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 3', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 4', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 4', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_4',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 4', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 5', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 5', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_5',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 5', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 6', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 6', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_6',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 6', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 7', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 7', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_7',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 7', 'geodirectory')
				);
		$fields[] = array(
					'post_type' => $post_type,
					'data_type' => 'TEXT',
					'field_type' => 'url',
					'admin_title' => __('Portfolio link 8', 'geodirectory'),
					'frontend_desc' => __('Please add a link to a website you have created.', 'geodirectory'),
					'frontend_title' => __('Portfolio link 8', 'geodirectory'),
					'htmlvar_name' => 'portfolio_link_8',
					'default_value' => '',
					'is_active' => '1',
					'option_values' => '',
					'is_default' => '0',
					'show_in' => '',
					'show_on_pkg' => $package,
					'clabels' => __('Portfolio link 8', 'geodirectory')
				);

	return $fields;
}

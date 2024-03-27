<?php
/**
 * GD Freelancers dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 * @var $post_type string The post type.
 */

$dummy_image_url = 'https://ayecode.b-cdn.net/dummy/job_board/images';
$dummy_caticon_url = 'https://ayecode.b-cdn.net/dummy/job_board/icons';

// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['writing-translation'] = array(
	'name'        => 'Writing &amp; Translation',
	'icon'        => $dummy_caticon_url . '/back-end.png',
	'font_icon'   => 'fab fa-accessible-icon',
	'color'       => '#254e4e',
);
$dummy_categories['customer-service'] = array(
	'name'        => 'Customer Service',
	'icon'        => $dummy_caticon_url . '/front-end.png',
	'font_icon'   => 'fas fa-bolt',
	'color'       => '#fcdc1f',
);
$dummy_categories['marketing-sales'] = array(
	'name'        => 'Marketing &amp; Sales',
	'icon'        => $dummy_caticon_url . '/full-stack.png',
	'font_icon'   => 'fas fa-mobile',
	'color'       => '#fb9e05',
);
$dummy_categories['product-management'] = array(
	'name'        => 'Product Management',
	'icon'        => $dummy_caticon_url . '/implementer.png',
	'font_icon'   => 'fas fa-tv',
	'color'       => '#f44e3b',
);
$dummy_categories['legal-finance'] = array(
	'name'        => 'Legal &amp; Finance',
	'icon'        => $dummy_caticon_url . '/seo.png',
	'font_icon'   => 'fas fa-music',
	'color'       => '#009ce0',
);
$dummy_categories['development-it'] = array(
	'name'        => 'Development &amp; IT',
	'icon'        => $dummy_caticon_url . '/seo.png',
	'font_icon'   => 'fas fa-music',
	'color'       => '#15a5a5',
);
$dummy_categories['design-creative'] = array(
	'name'        => 'Design &amp; Creative',
	'icon'        => $dummy_caticon_url . '/seo.png',
	'font_icon'   => 'fas fa-bed',
	'color'       => '#6abd21',
);
$dummy_categories['analytics'] = array(
	'name'        => 'Analytics',
	'icon'        => $dummy_caticon_url . '/seo.png',
	'font_icon'   => 'fas fa-bed',
	'color'       => '#ab3b9e',
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
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Sales Specialist',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Marketing &amp; Sales'),
	"post_tags" => array('CSS', 'SCSS', 'NODEJS'),
	"email" => 'info@example.com', // Assuming a generic email as placeholder
	"website" => 'https://www.example.com',
	"job_type" => 'Internship', // Directly from the job post data
	"job_sector" => 'Agencies', // Directly from the job post data
	"company_name" => 'Microsoft', // Directly from the job post data
	"logo" => array(
		"$dummy_image_url/logo_8.png" // Assuming a placeholder image path
	),
	"price_range" => '3000 - 5000', // Assuming a placeholder price range
	"deadline_date" => '2024-07-28', // Assuming a placeholder deadline date
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Project Manager',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Customer Service', 'Marketing & Sales', 'Product Management'),
	"post_tags" => array('Agile', 'SCRUM', 'Project Management'),
	"email" => 'project.manager@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Technology',
	"company_name" => 'Tech Innovations Inc.',
	"logo" => array(
		"$dummy_image_url/logo_1.png"
	),
	"price_range" => '5000 - 7000',
	"deadline_date" => '2024-08-15',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Marketing Manager',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Marketing & Sales'),
	"post_tags" => array('Digital Marketing', 'SEO', 'Ad Campaigns'),
	"email" => 'marketing.manager@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Contract',
	"job_sector" => 'Advertising',
	"company_name" => 'Adventures Marketing',
	"logo" => array(
		"$dummy_image_url/logo_2.png"
	),
	"price_range" => 'Negotiable',
	"deadline_date" => '2024-09-30',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'iOS Developer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('Swift', 'iOS', 'Mobile Development'),
	"email" => 'ios.dev@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Part-time',
	"job_sector" => 'Software Development',
	"company_name" => 'Mobile Masters',
	"logo" => array(
		"$dummy_image_url/logo_3.png"
	),
	"price_range" => '4000 - 6000',
	"deadline_date" => '2024-12-20',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'UI/UX Designer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Design', 'User Experience'),
	"post_tags" => array('Figma', 'Sketch', 'Adobe XD'),
	"email" => 'uiux.designer@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Freelance',
	"job_sector" => 'Design',
	"company_name" => 'Creative Minds',
	"logo" => array(
		"$dummy_image_url/logo_4.png"
	),
	"price_range" => '2000 - 4000',
	"deadline_date" => '2024-10-05',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Data Analyst',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Data Analysis'),
	"post_tags" => array('SQL', 'Python', 'Data Visualization'),
	"email" => 'data.analyst@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Analytics',
	"company_name" => 'Insight Analytics',
	"logo" => array(
		"$dummy_image_url/logo_5.png"
	),
	"price_range" => '5000 - 7000',
	"deadline_date" => '2024-11-30',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Blockchain Developer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Blockchain'),
	"post_tags" => array('Ethereum', 'Smart Contracts', 'Solidity'),
	"email" => 'blockchain.dev@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Contract',
	"job_sector" => 'Blockchain Technology',
	"company_name" => 'Blockchain Solutions',
	"logo" => array(
		"$dummy_image_url/logo_6.png"
	),
	"price_range" => '7000 - 10000',
	"deadline_date" => '2024-06-15',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Cybersecurity Specialist',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Cybersecurity'),
	"post_tags" => array('Network Security', 'Vulnerability Assessment', 'Penetration Testing'),
	"email" => 'cybersecurity.specialist@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Security',
	"company_name" => 'SecureTech Innovations',
	"logo" => array(
		"$dummy_image_url/logo_7.png"
	),
	"price_range" => '8000 - 12000',
	"deadline_date" => '2024-07-20',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Frontend Developer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('HTML', 'CSS', 'JavaScript', 'React'),
	"email" => 'frontend.dev@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Web Development',
	"company_name" => 'WebFront Technologies',
	"logo" => array(
		"$dummy_image_url/logo_8.png"
	),
	"price_range" => '4000 - 6000',
	"deadline_date" => '2024-08-01',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Content Writer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Writing &amp; Translation'),
	"post_tags" => array('SEO Writing', 'Blogging', 'Copywriting'),
	"email" => 'content.writer@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Freelance',
	"job_sector" => 'Content Creation',
	"company_name" => 'Creative Content Co.',
	"logo" => array(
		"$dummy_image_url/logo_1.png"
	),
	"price_range" => 'Per Article / Per Word',
	"deadline_date" => '2024-09-10',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Social Media Manager',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Marketing & Sales'),
	"post_tags" => array('Facebook', 'Instagram', 'Twitter', 'LinkedIn'),
	"email" => 'social.media@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Part-time',
	"job_sector" => 'Social Media',
	"company_name" => 'Social Buzz',
	"logo" => array(
		"$dummy_image_url/logo_2.png"
	),
	"price_range" => '3000 - 5000',
	"deadline_date" => '2024-10-15',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Product Manager',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Product Management'),
	"post_tags" => array('Product Lifecycle Management', 'Roadmapping', 'Agile'),
	"email" => 'product.manager@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Product Development',
	"company_name" => 'Innovative Product Solutions',
	"logo" => array(
		"$dummy_image_url/logo_3.png"
	),
	"price_range" => '6000 - 8000',
	"deadline_date" => '2024-11-05',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Graphic Designer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Design'),
	"post_tags" => array('Adobe Photoshop', 'Illustrator', 'InDesign'),
	"email" => 'graphic.designer@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Freelance',
	"job_sector" => 'Graphic Design',
	"company_name" => 'Creative Designs Studio',
	"logo" => array(
		"$dummy_image_url/logo_4.png"
	),
	"price_range" => 'Per Project / Per Hour',
	"deadline_date" => '2024-12-31',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'HR Manager',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Human Resources'),
	"post_tags" => array('Recruitment', 'Employee Relations', 'Performance Management'),
	"email" => 'hr.manager@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Human Resources',
	"company_name" => 'HR Solutions Inc.',
	"logo" => array(
		"$dummy_image_url/logo_5.png"
	),
	"price_range" => '5000 - 7000',
	"deadline_date" => '2024-08-20',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Business Analyst',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Data Analysis'),
	"post_tags" => array('Data Modeling', 'Business Intelligence', 'SQL'),
	"email" => 'business.analyst@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Contract',
	"job_sector" => 'Consulting',
	"company_name" => 'Business Insights',
	"logo" => array(
		"$dummy_image_url/logo_6.png"
	),
	"price_range" => 'Negotiable',
	"deadline_date" => '2024-09-30',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Cloud Engineer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('AWS', 'Azure', 'Cloud Architecture'),
	"email" => 'cloud.engineer@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Cloud Computing',
	"company_name" => 'Cloud Tech Innovations',
	"logo" => array(
		"$dummy_image_url/logo_7.png"
	),
	"price_range" => '7000 - 9000',
	"deadline_date" => '2024-07-15',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'DevOps Engineer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('CI/CD', 'Kubernetes', 'Docker', 'AWS'),
	"email" => 'devops@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Information Technology',
	"company_name" => 'DevOps Tech Solutions',
	"logo" => array(
		"$dummy_image_url/logo_8.png"
	),
	"price_range" => '8000 - 12000',
	"deadline_date" => '2024-10-01',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Network Architect',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('Network Infrastructure', 'Cisco', 'Cloud Networking'),
	"email" => 'network.architect@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Networking',
	"company_name" => 'Network Solutions Inc.',
	"logo" => array(
		"$dummy_image_url/logo_1.png"
	),
	"price_range" => '9000 - 11000',
	"deadline_date" => '2024-11-20',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Machine Learning Engineer',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Technology'),
	"post_tags" => array('Python', 'TensorFlow', 'Neural Networks', 'Data Science'),
	"email" => 'ml.engineer@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'AI & Machine Learning',
	"company_name" => 'AI Tech Innovations',
	"logo" => array(
		"$dummy_image_url/logo_2.png"
	),
	"price_range" => '10000 - 15000',
	"deadline_date" => '2024-12-15',
	"post_dummy" => '1'
);

$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Digital Marketing Specialist',
	"post_content" => geodir_generate_lorem_ipsum(50, 'words') . PHP_EOL . PHP_EOL . geodir_generate_lorem_ipsum(50, 'words'),
	"post_category" => array('Marketing & Sales'),
	"post_tags" => array('SEO', 'PPC', 'Content Marketing', 'Social Media Marketing'),
	"email" => 'digital.marketing@example.com',
	"website" => 'https://www.example.com',
	"job_type" => 'Full-time',
	"job_sector" => 'Marketing',
	"company_name" => 'Digital Marketing Pros',
	"logo" => array(
		"$dummy_image_url/logo_3.png"
	),
	"price_range" => '6000 - 8000',
	"deadline_date" => '2024-09-05',
	"post_dummy" => '1'
);


function geodir_extra_custom_fields_job_board( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// Company Name
	$fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'VARCHAR',
		'admin_title'			=> __('Company Name', 'geodirectory'),
		'frontend_title'		=> __('Company Name', 'geodirectory'),
		'frontend_desc'			=> __('You can enter your company name.', 'geodirectory' ),
		'htmlvar_name'			=> 'company_name',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'clabels' 				=> 'Company Name'
	);

	// logo
	$fields[] = array(
		'post_type'      => $post_type,
		'data_type'      => 'TEXT',
		'field_type'     => 'file',
		'admin_title'    => __( 'Company Logo', 'geodirectory' ),
		'frontend_desc'  => __( 'Add your company logo here', 'geodirectory' ),
		'frontend_title' => __( 'Company Logo', 'geodirectory' ),
		'htmlvar_name'   => 'logo',
		'default_value'  => '',
		'option_values'  => '',
		'is_default'     => '0',
		'is_active'      => '1',
		'is_required'    => '0',
		'show_on_pkg'    => $package,
		'clabels'        => __( 'Company Logo', 'geodirectory' ),
		'field_icon'     => 'far fa-image',
		'extra_fields'       => array(
			'gd_file_types'     => array( 'jpg','jpe','jpeg','gif','png','bmp','ico','webp'),
			'file_limit'        => 1,
		),
		'single_use'         => true,
	);

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

    // Price range
	$fields[] = array(
		'post_type'            => $post_type,
		'field_type'           => 'select',
		'data_type'            => 'VARCHAR',
		'admin_title'          => __('Salary Range', 'geodirectory'),
		'frontend_title'       => __('Salary Range', 'geodirectory'),
		'frontend_desc'        => __('Select the salary range for the job.', 'geodirectory'),
		'htmlvar_name'         => 'price_range',
		'is_active'            => true,
		'for_admin_use'        => false,
		'default_value'        => '',
		'option_values'        => 'Select Price Range/,1300 - 2000, 2000 - 3000,3000 - 5000',
		'is_required'          => false,
		'required_msg'         => '',
		'validation_pattern'   => '',
		'validation_msg'       => '',
		'field_icon'           => 'fas fa-dollar-sign',
		'css_class'            => '',
		'cat_sort'             => true,
		'cat_filter'           => true,
		'extra_fields'         => array(
			'currency_symbol'               => '$',
			'currency_symbol_placement'     => 'left',
			'thousand_separator'            => 'comma',
			'decimal_separator'             => 'period',
			'decimal_display'               => 'if',
			'decimal_point'                 => '2',
		),
		'show_on_pkg'          => $package,
		'clabels'              => 'price_range'
	);


	$fields[] = array(
		'post_type'            => $post_type,
		'field_type'           => 'datepicker',
		'data_type'            => 'DATE',
		'admin_title'          => __('Deadline date', 'geodirectory'),
		'frontend_title'       => __('Deadline date', 'geodirectory'),
		'frontend_desc'        => __('Select the deadline date for the job application.', 'geodirectory'),
		'htmlvar_name'         => 'deadline_date',
		'is_active'            => true,
		'for_admin_use'        => false,
		'default_value'        => '',
		'option_values'        => '',
		'is_required'          => false,
		'required_msg'         => '',
		'validation_pattern'   => '',
		'validation_msg'       => '',
		'field_icon'           => '',
		'css_class'            => '',
		'decimal_point'        => '0',
		'extra_fields'         => array(
			'date_format'        => 'F j, Y',
			'date_range'         => '',
		),
		'show_on_pkg'          => $package,
		'clabels'              => 'deadline_date'
	);

	$fields[] = array(
		'post_type'            => $post_type,
		'field_type'           => 'select',
		'data_type'            => 'VARCHAR',
		'admin_title'          => __('Job Type', 'geodirectory'),
		'frontend_title'       => __('Job Type', 'geodirectory'),
		'frontend_desc'        => __('Select the type of job.', 'geodirectory'),
		'htmlvar_name'         => 'job_type',
		'is_active'            => true,
		'for_admin_use'        => false,
		'default_value'        => '',
		'option_values'        => 'Select Type/,Freelance,Full Time,Internship,Part Time,Temporary,Other',
		'is_required'          => true,
		'required_msg'         => __('Please select the job type.', 'geodirectory'),
		'validation_pattern'   => '',
		'validation_msg'       => '',
		'field_icon'           => 'fas fa-briefcase',
		'css_class'            => '',
		'cat_sort'             => true,
		'cat_filter'           => true,
		'extra_fields'         => array(),
		'show_on_pkg'          => $package,
		'clabels'              => 'job_type'
	);

	$fields[] = array(
		'post_type'            => $post_type,
		'field_type'           => 'select',
		'data_type'            => 'VARCHAR',
		'admin_title'          => __('Job Sector', 'geodirectory'),
		'frontend_title'       => __('Job Sector', 'geodirectory'),
		'frontend_desc'        => __('Select the job sector.', 'geodirectory'),
		'htmlvar_name'         => 'job_sector',
		'is_active'            => true,
		'for_admin_use'        => false,
		'default_value'        => '',
		'option_values'        => 'Select Sector/,Private Sector,Public Sector,Agencies',
		'is_required'          => true,
		'required_msg'         => __('Please select the job sector.', 'geodirectory'),
		'validation_pattern'   => '',
		'validation_msg'       => '',
		'field_icon'           => 'fas fa-briefcase',
		'css_class'            => '',
		'cat_sort'             => true,
		'cat_filter'           => true,
		'extra_fields'         => array(),
		'show_on_pkg'          => $package,
		'clabels'              => 'job_sector',
	);


	return $fields;
}

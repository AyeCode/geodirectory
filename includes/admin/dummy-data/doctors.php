<?php
/**
 * GD doctors dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/doctors/images';
$dummy_caticon_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/doctors/icons';


// Set the dummy categories
$dummy_categories  = array();

$dummy_categories['alternative-medicine'] = array(
	'name'        => 'Alternative Medicine',
	'icon'        => $dummy_caticon_url . '/alternative-medicine.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);
$dummy_categories['ayurveda'] = array(
	'name'        => 'Ayurveda',
	'parent-name' => 'Alternative Medicine',
	'icon'        => $dummy_caticon_url . '/ayurveda.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['allopathy-doctors'] = array(
	'name'        => 'Allopathy Doctors',
	'icon'        => $dummy_caticon_url . '/Allopathy.png',
	'font_icon'   => 'fas fa-file-code',
	'color'       => '#5551b9',
);
$dummy_categories['ophthalmology'] = array(
	'name'        => 'Ophthalmology',
	'parent-name' => 'Allopathy Doctors',
	'icon'        => $dummy_caticon_url . '/Ophthalmology.png',
	'font_icon'   => 'fas fa-cubes',
	'color'       => '#852d2d',
);
$dummy_categories['general-medicine'] = array(
	'name'        => 'General Medicine',
	'icon'        => $dummy_caticon_url . '/general-medecine.png',
	'font_icon'   => 'fas fa-star',
	'color'       => '#84612d',
);

$dummy_categories['oncology'] = array(
	'name'        => 'Oncology',
	'icon'        => $dummy_caticon_url . '/Oncology.png',
	'font_icon'   => 'fas fa-star',
	'color'       => '#84612d',
);

$dummy_categories['dermatology'] = array(
	'name'        => 'Dermatology',
	'icon'        => $dummy_caticon_url . '/Dermatology.png',
	'font_icon'   => 'fas fa-search-plus',
	'color'       => '#84612d',
);

$dummy_categories['obstetrics-and-gynecology'] = array(
	'name'        => 'Obstetrics and Gynecology',
	'icon'        => $dummy_caticon_url . '/Obstetrics.png',
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

// experience
$dummy_sort_fields[] = array(
	'post_type' => $post_type,
	'data_type' => 'VARCHAR',
	'field_type' => 'INT',
	'frontend_title' => __('Experience','geodirectory'),
	'htmlvar_name' => 'experience',
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
	"post_title" => 'Dr. Diana Walton',
	"post_content" => 'We are a 30 year old, pioneering institution specializing in delivering starvation free, life time slimming, superb fitness and optimum physical and mental health under medical supervision',
	"post_category" => array( 'Allopathy Doctors', 'Oncology', 'General Medicine' ),
	"post_tags" => array( 'Bariatric Surgery', 'Oncology' ),
	"email" => 'edward@edward.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - MADURAI MEDICAL COLLEGE - 2001
						MD - PGIMER CHANDIGARH - 2008
						DM CLINICAL HAEMATOLOGY - CMC, IHTM ,KOLKATA MEDICAL COLLEGE - 2011',
	"gender" => 0,
	"experience" => 2,
	"experience_words" => 'Haemato-oncology, Bone Marrow Transplant July - 2011 - October - 2015',
	"for_online" => 1,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Oncology' ),
	"expertise_words" => 'Haematology, Anemias, Coagulation Disorder, Leukemias , Myelomas, Lymphomas, Bone Marrow Transplantation',
	"post_images"   => array(
		"$dummy_image_url/dr-1.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 2
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Charles Burton',
	"post_content" => 'Dr Charles Burton is Eminent Infertility Specialist , Obstetrician, Gynaecologist and Laproscopic Surgeon in Middle East India specialy in England. He has completed his medical education and specialist training in Obstetrics & Gynaecology. He has been working in England as Infertility Consultant since 2008, and has earned respect among both the medical fraternity and her patients.',
	"post_category" => array( 'Allopathy Doctors', 'Obstetrics and Gynecology' ),
	"post_tags" => array( 'Bariatric Surgery', 'Oncology' ),
	"email" => 'paddy@paddy.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - MADURAI MEDICAL COLLEGE - 2001
						DGO - College Of Physician And Surgery Of England - 2008',
	"gender" => 1,
	"experience" => 21,
	"experience_words" => '21 Years January - 1999 - June - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Obstetrics and Gynecology' ),
	"expertise_words" => '21 years of experience in Obstetrics and Gynecology',
	"post_images"   => array(
		"$dummy_image_url/dr-2.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 3
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Sarah Lynch',
	"post_content" => 'I completed my mbbs from London in 2016 and has been working as junior resident in KEMI hospital London',
	"post_category" => array( 'Allopathy Doctors', 'Obstetrics and Gynecology' ),
	"post_tags" => array( 'Allopathy', 'Gynecology' ),
	"email" => 'paddy@paddy.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - London Govt. Hospital - 2016
	MD - KEMI Hospital London - 2019',
	"gender" => 0,
	"experience" => 3,
	"experience_words" => '3 Years  October - 2016 - October - 2019',
	"for_online" => 1,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Preventive Medicine' ),
	"expertise_words" => '3 years of experience in General Medicine and Preventive Medicine',
	"post_images"   => array(
		"$dummy_image_url/dr-3.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 4
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Doctor Blenkinsop',
	"post_content" => 'Consultant in the Department of Medical Oncology, Hemato-Oncology, Pediatric Oncology and Bone Marrow Transplant',
	"post_category" => array( 'Oncology' ),
	"post_tags" => array( 'Bariatric Surgery', 'Oncology' ),
	"email" => 'blenkinsop@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - London Govt. Hospital - 2016
	MD - KEMI Hospital London - 2019',
	"gender" => 1,
	"experience" => 3,
	"experience_words" => 'Junior Consultant July - 2019 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Emergency Medicine' ),
	"expertise_words" => 'Hemato-Oncology, Pediatric Oncology, Bone Marrow Transplant, Medical Oncology, Palliative Care
',
	"post_images"   => array(
		"$dummy_image_url/dr-4.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 5
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Pamela Isley',
	"post_content" => 'Dr. Pamela Isley MD, DNB Certified in Positive Psychology (university of Penn) Consultant Psychiatrist Professor in Physiology With 20 yrs of experience in Medical field. Currently working as professor and consultant psychiatrist at St.John\'s medical College and research hospital, Los Angeles, Consultant psychiatrist at Life care hospital.',
	"post_category" => array( 'Allopathy Doctors', 'General Medicine' ),
	"post_tags" => array( 'Psychology', 'General Medicine' ),
	"email" => 'pamela@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'Certificate Course In Positive Psychology - University Of Penn - 2018
						DNB Psychiatry - NBE - 2016
						MD - RGUHS - 2006',
	"gender" => 0,
	"experience" => 20,
	"experience_words" => '20 September - 2001 - May - 2020',
	"for_online" => 1,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Psychiatry' ),
	"expertise_words" => '20 Years experience in adult psychiatry [Depression, Anxiety disorders, OCD, Schizophrenia and Bipolar disorder, Addiction disorders] and also in working with children and the elderly mental health, Positive Psychology.',
	"post_images"   => array(
		"$dummy_image_url/dr-5.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 6
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Victoria Helsing',
	"post_content" => 'Dr. Victoria Helsing joined the department in September 2014 after his higher training at the Christian Medical College at Ukraine.

He remains committed to the CMC values of service, care and commitment. Recognized by the American Society of Clinical Oncology for his potential to be a leader in the field of oncology, he has additionally undergone advanced training in stem cell transplantation including haplo-identical and cord blood transplantation at the Seattle Cancer Care Alliance of the University of Washington, USA. His areas of active interests include Acute Leukemias, Thromboses and Stem Cell Transplantation.

His philosophy, the CMC motto - "My Work is for a King"',
	"post_category" => array( 'Dermatology' ),
	"post_tags" => array( 'Dermatology', 'Blood' ),
	"email" => 'helsing@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - Christian Medical College And Hospital, Ludhiana - 2003
MD - Christian Medical College And Hospital, Ludhiana - 2010
DM - Christian Medical College, Vellore - 2014',
	"gender" => 0,
	"experience" => 20,
	"experience_words" => '20 September - 2010 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Leukemias' ),
	"expertise_words" => '10 Years experience in Thalassaemia, Stem Cell/Bone Marrow Transplantation, Acute Leukemias, Multiple Myeloma, Lymphoma, Thrombosis
',
	"post_images"   => array(
		"$dummy_image_url/dr-6.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 7
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Victor Frankenstein',
	"post_content" => 'Dr. Victor Frankenstein is a B.H.M.S., M.D. Homeopathy doctor in Pune, offering effective homeopathy treatment for children as well as adults of all age groups. She is also conducting weekly Garbhsanskar and Pregnancy yoga classes as she has interest and good experience in gynecology obstetrics, mother & child care.',
	"post_category" => array( 'Alternative Medicine' ),
	"post_tags" => array( 'Family Medicine', 'General Medicine' ),
	"email" => 'victor@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MD - Dr. D. Y. Patil Homoeopathic Medical College And Research Centre - 2018
BHMS - Foster Developments Homoeopathic Medical College, Affiliated To Ukaraine University Of Health Sciences - 2012',
	"gender" => 1,
	"experience" => 20,
	"experience_words" => '20 September - 2010 - May - 2020',
	"for_online" => 1,
	"languages" => array( 'Japanese' ),
	"doctor_areas_of_expertise" => array( 'Neurology' ),
	"expertise_words" => 'Homeopathy Treatment for Skin Diseases Eczema, Dermatitis, Urticaria, Allergies, Acne, Warts. Treatment for children diseases Teething difficulty, Tonsillitis, Diarrhoea, Vomiting, Worm infestation, Behavioral problems eg. Bed wetting Nutritional disorders, Allergies, Infectious Diseases. Treatment for nutritional disorders, Anemia, Poor weight gain, Deficiencies, Treatment for female complaints Menstrual irregularities, Premenstrual ailm and Pregnancy Care',
	"post_images"   => array(
		"$dummy_image_url/dr-7.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 8
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Sarah Cooper',
	"post_content" => 'Dr. Sarah Cooper is a young talented homeopathic physician.',
	"post_category" => array( 'Alternative Medicine', 'Ayurveda' ),
	"post_tags" => array( 'Family Medicine', 'Gastroenterology', 'Psychology' ),
	"email" => 'bardamu@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'BHMS - 2009',
	"gender" => 0,
	"experience" => 8,
	"experience_words" => '20 September - 2012 - May - 2020',
	"for_online" => 1,
	"languages" => array( 'Hindi' ),
	"doctor_areas_of_expertise" => array( 'Ophthalmology' ),
	"expertise_words" => 'INFERTILITY AND PCOD IMPOTENCY JOINT PAINS ONCOLOGY ENDOCRINOLOGY NEPHROLOGY NEUROLOGY',
	"post_images"   => array(
		"$dummy_image_url/dr-8.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 9
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Charles Bovary	',
	"post_content" => 'Dr. Charles Bovary is A Speech Language Pathologist, Audiologist And Speech Therapist. He did his B.Sc. (Hons) Speech and Hearing from AIIMS Delhi and M.Sc. Speech language Pathology from AIISH Mysore.
He Is The Founder Of Charles Bovary Speech Hearing And Rehabilitation Center Where People From Different Part Of The World Are Also Coming For Their Communication Disorders Treatment. Also Working As Chief Speech Language Pathologist And Audiologist At This Center.

He Strongly Believes In Evidence Based Practices.',
	"post_category" => array( 'Ophthalmology', 'General Medicine' ),
	"post_tags" => array( 'Audiology', 'Speech Therapy' ),
	"email" => 'charles@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'B.Sc. (Hons) Speech and Hearing - 2000',
	"gender" => 1,
	"experience" => 6,
	"experience_words" => '20 September - 2014 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'French' ),
	"doctor_areas_of_expertise" => array( 'Psychiatry' ),
	"expertise_words" => 'Hearing Aids Fitting/Dispensing, Hearing Testing , Voice Modulation, Speech Language Therapy,Speech Therapy',
	"post_images"   => array(
		"$dummy_image_url/dr-9.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 10
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Timothy Flyte',
	"post_content" => 'Dr. Timothy Flyte is a super-specialist in the field of ONCOLOGY. He has completed his DM in Oncology/ Hematology from Asia\'s premier cancer institute "',
	"post_category" => array( 'Ophthalmology' ),
	"post_tags" => array( 'Oncologist', 'cancer', 'hormonal treatment' ),
	"email" => 'timothy@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS, MD (Hons) Oncology - 1990',
	"gender" => 1,
	"experience" => 30,
	"experience_words" => '20 Jan - 1990 - Present',
	"for_online" => 1,
	"languages" => array( 'French' ),
	"doctor_areas_of_expertise" => array( 'Urology' ),
	"expertise_words" => 'He is well renowned as an expert in diagnosing and management of cancer and an expert in chemotherapy, molecular targeted therapy, hormonal and immunology treatment.',
	"post_images"   => array(
		"$dummy_image_url/dr-10.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 11
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Jennifer Paige',
	"post_content" => 'We are a 30 year old, pioneering institution specializing in delivering starvation free, life time slimming, superb fitness and optimum physical and mental health under medical supervision',
	"post_category" => array( 'General Medicine' ),
	"post_tags" => array( 'Bariatric Surgery', 'Oncology' ),
	"email" => 'paige@paige.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - MADURAI MEDICAL COLLEGE - 2001
						MD - PGIMER CHANDIGARH - 2008
						DM CLINICAL HAEMATOLOGY - CMC, IHTM ,KOLKATA MEDICAL COLLEGE - 2011',
	"gender" => 1,
	"experience" => 2,
	"experience_words" => 'Haemato-oncology, Bone Marrow Transplant July - 2011 - October - 2015',
	"for_online" => 0,
	"languages" => array( 'Hindi' ),
	"doctor_areas_of_expertise" => array( 'Pediatrics' ),
	"expertise_words" => 'Haematology, Anemias, Coagulation Disorder, Leukemias , Myelomas, Lymphomas, Bone Marrow Transplantation',
	"post_images"   => array(
		"$dummy_image_url/dr-11.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 12
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Hannibal Lecter',
	"post_content" => 'Dr. Hannibal Lecter is Eminent Infertility Specialist , Obstetrician, Gynaecologist and Laproscopic Surgeon in Middle East India specialy in England. He has completed his medical education and specialist training in Obstetrics & Gynaecology. He has been working in England as Infertility Consultant since 2008, and has earned respect among both the medical fraternity and her patients.',
	"post_category" => array( 'Dermatology' ),
	"post_tags" => array( 'hair', 'skin' ),
	"email" => 'hanni@paddy.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - MADURAI MEDICAL COLLEGE - 2001
						DGO - College Of Physician And Surgery Of England - 2008',
	"gender" => 1,
	"experience" => 21,
	"experience_words" => '21 Years January - 1999 - June - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Allergy & Immunology' ),
	"expertise_words" => '21 years of experience in Obstetrics and Gynecology',
	"post_images"   => array(
		"$dummy_image_url/dr-12.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 13
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Sandra Mornay',
	"post_content" => 'I completed my mbbs from London in 2016 and has been working as junior resident in KEMI hospital London',
	"post_category" => array( 'Allopathy Doctors', 'Obstetrics and Gynecology' ),
	"post_tags" => array( 'Allopathy', 'Gynecology' ),
	"email" => 'sandra@paddy.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - London Govt. Hospital - 2016
	MD - KEMI Hospital London - 2019',
	"gender" => 0,
	"experience" => 3,
	"experience_words" => '3 Years  October - 2016 - October - 2019',
	"for_online" => 1,
	"languages" => array( 'Hindi' ),
	"doctor_areas_of_expertise" => array( 'Ophthalmology' ),
	"expertise_words" => '3 years of experience in General Medicine and Preventive Medicine',
	"post_images"   => array(
		"$dummy_image_url/dr-13.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 14
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. J.S. Hirsch',
	"post_content" => 'Consultant in the Department of Medical Oncology, Hemato-Oncology, Pediatric Oncology and Bone Marrow Transplant',
	"post_category" => array( 'Obstetrics and Gynecology' ),
	"post_tags" => array( 'Surgery', 'Gynecology' ),
	"email" => 'hirsch@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - London Govt. Hospital - 2016
	MD - KEMI Hospital London - 2019',
	"gender" => 0,
	"experience" => 7,
	"experience_words" => 'Junior Consultant July - 2019 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Dermatology' ),
	"expertise_words" => 'Hemato-Oncology, Pediatric Oncology, Bone Marrow Transplant, Medical Oncology, Palliative Care
',
	"post_images"   => array(
		"$dummy_image_url/dr-14.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 15
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Martin Arrowsmith',
	"post_content" => 'Dr. Martin Arrowsmith MD, DNB Certified in Positive Psychology (university of Penn) Consultant Ophthalmology Professor in Ophthalmology With 20 yrs of experience in Medical field. Currently working as professor and consultant Ophthalmology at St.John\'s medical College and research hospital, Los Angeles, Consultant Ophthalmology at Life care hospital.',
	"post_category" => array( 'Ophthalmology', 'General Medicine' ),
	"post_tags" => array( 'Ophthalmology', 'General Medicine' ),
	"email" => 'martin@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'Certificate Course In Ophthalmology - University Of Penn - 2018
						DNB Ophthalmology - NBE - 2016
						MD - RGUHS - 2006',
	"gender" => 1,
	"experience" => 2,
	"experience_words" => '20 September - 2017 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Diagnostic Radiology' ),
	"expertise_words" => '2 Years experience in Ophthalmology',
	"post_images"   => array(
		"$dummy_image_url/dr-15.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 16
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Sharon Golban',
	"post_content" => 'Dr. Sharon Golban joined the department in September 2014 after his higher training at the Christian Medical College at Ukraine. His philosophy, - "Prevention is better than cure."',
	"post_category" => array( 'Ayurveda' ),
	"post_tags" => array( 'Ayurveda', 'herbes' ),
	"email" => 'robin@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS - Christian Medical College And Hospital, Ludhiana - 2003
MD - Christian Medical College And Hospital, Ludhiana - 2010
DM - Christian Medical College, Vellore - 2014',
	"gender" => 0,
	"experience" => 5,
	"experience_words" => '20 September - 2015 - May - 2020',
	"for_online" => 1,
	"languages" => array( 'English' ),
	"doctor_areas_of_expertise" => array( 'Allergy & Immunology' ),
	"expertise_words" => '10 Years experience in Thalassaemia, Stem Cell/Bone Marrow Transplantation, Acute Leukemias, Multiple Myeloma, Lymphoma, Thrombosis
',
	"post_images"   => array(
		"$dummy_image_url/dr-16.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 17
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => ' Dr. Jane Carlson',
	"post_content" => ' Dr. Jane Carlson is a B.H.M.S., M.D. Homeopathy doctor in Pune, offering effective homeopathy treatment for children as well as adults of all age groups. She is also conducting weekly Garbhsanskar and Pregnancy yoga classes as she has interest and good experience in gynecology obstetrics, mother & child care.',
	"post_category" => array( 'General Medicine' ),
	"post_tags" => array( 'Medicine', 'no-pills', 'clinic' ),
	"email" => 'miguel@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MD - Dr. D. Y. Patil Homoeopathic Medical College And Research Centre - 2018
BHMS - Foster Developments Homoeopathic Medical College, Affiliated To Ukaraine University Of Health Sciences - 2012',
	"gender" => 0,
	"experience" => 7,
	"experience_words" => '2013 - Present',
	"for_online" => 0,
	"languages" => array( 'Arabic' ),
	"doctor_areas_of_expertise" => array( 'Diagnostic Radiology' ),
	"expertise_words" => 'Homeopathy Treatment for Skin Diseases Eczema, Dermatitis, Urticaria, Allergies, Acne, Warts. Treatment for children diseases Teething difficulty, Tonsillitis, Diarrhoea, Vomiting, Worm infestation, Behavioral problems eg. Bed wetting Nutritional disorders, Allergies, Infectious Diseases. Treatment for nutritional disorders, Anemia, Poor weight gain, Deficiencies, Treatment for female complaints Menstrual irregularities, Premenstrual ailm and Pregnancy Care',
	"post_images"   => array(
		"$dummy_image_url/dr-17.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 18
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Allison Cameron',
	"post_content" => 'Dr. Allison Cameron is a young talented homeopathic physician.',
	"post_category" => array( 'Obstetrics and Gynecology', 'Ayurveda' ),
	"post_tags" => array( 'Medicine', 'Gastroenterology', 'Ayurveda' ),
	"email" => 'ramon@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'BHMS - 2009',
	"gender" => 0,
	"experience" => 3,
	"experience_words" => '7th April - 2017 - June - 2020',
	"for_online" => 1,
	"languages" => array( 'Russian' ),
	"doctor_areas_of_expertise" => array( 'Emergency Medicine' ),
	"expertise_words" => 'INFERTILITY AND PCOD IMPOTENCY JOINT PAINS ONCOLOGY ENDOCRINOLOGY NEPHROLOGY NEUROLOGY',
	"post_images"   => array(
		"$dummy_image_url/dr-18.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 19
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Thomas Wayne',
	"post_content" => 'Dr. Thomas Wayne	 is A Speech Language Pathologist, Audiologist And Speech Therapist. He did his B.Sc. (Hons) Speech and Hearing from AIIMS Delhi and M.Sc. Speech language Pathology from AIISH Mysore.
He Is The Founder Of Charles Bovary Speech Hearing And Rehabilitation Center Where People From Different Part Of The World Are Also Coming For Their Communication Disorders Treatment. Also Working As Chief Speech Language Pathologist And Audiologist At This Center.

He Strongly Believes In Evidence Based Practices.',
	"post_category" => array( 'Dermatology', 'General Medicine' ),
	"post_tags" => array( 'Dermatology', 'hair', 'shampoo' ),
	"email" => 'thomas@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'B.Sc. (Hons) Speech and Hearing - 2000',
	"gender" => 1,
	"experience" => 13,
	"experience_words" => '20 April - 2003 - May - 2020',
	"for_online" => 0,
	"languages" => array( 'French' ),
	"doctor_areas_of_expertise" => array( 'Ophthalmology' ),
	"expertise_words" => 'Hearing Aids Fitting/Dispensing, Hearing Testing , Voice Modulation, Speech Language Therapy,Speech Therapy',
	"post_images"   => array(
		"$dummy_image_url/dr-19.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

//dummy post 20
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Vitus Werdegast',
	"post_content" => 'Dr. Vitus Werdegast is a super-specialist in the field of ONCOLOGY. He has completed his DM in Oncology/ Hematology from Asia\'s premier cancer institute "',
	"post_category" => array( 'Oncology' ),
	"post_tags" => array( 'Oncology', 'cancer', 'hormonal treatment' ),
	"email" => 'timothy@example.com',
	"website" => 'http://example.com/',
	"qualification" => 'MBBS, MD (Hons) Oncology - 1990',
	"gender" => 1,
	"experience" => 6,
	"experience_words" => '20 Jan - 2014 - Present',
	"for_online" => 1,
	"languages" => array( 'German' ),
	"doctor_areas_of_expertise" => array( 'Urology' ),
	"expertise_words" => 'He is well renowned as an expert in diagnosing and management of cancer and an expert in chemotherapy, molecular targeted therapy, hormonal and immunology treatment.',
	"post_images"   => array(
		"$dummy_image_url/dr-20.jpg",
		"$dummy_image_url/drp-1.jpg",
		"$dummy_image_url/drp-2.jpg",
		"$dummy_image_url/drp-3.jpg",
		"$dummy_image_url/drp-4.jpg"
	),
	"post_dummy" => '1'
);

function geodir_extra_custom_fields_doctors( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';

	// EMail
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'text',
		'admin_title' => __('EMail', 'geodirectory'),
		'frontend_desc' => __('You can enter your organization or doctor\'s email.', 'geodirectory'),
		'frontend_title' => __('EMail', 'geodirectory'),
		'htmlvar_name' => 'email',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'clabels' => __('EMail', 'geodirectory'),
		'is_required' => true,
		'required_msg' => __('Entrer email of organization or doctor', 'geodirectory'),
	);
	
	// website
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'VARCHAR',
		'admin_title'			=> __('Website', 'geodirectory'),
		'frontend_title'		=> __('Website', 'geodirectory'),
		'frontend_desc'			=> __('You can enter your business or listing website.', 'geodirectory' ),
		'htmlvar_name'			=> 'website',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'clabels' 				=> 'Website'
    );

    // Qualification
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'VARCHAR',
		'admin_title'			=> __('Qualification', 'geodirectory'),
		'frontend_title'		=> __('Qualification', 'geodirectory'),
		'frontend_desc'			=> __('You can enter the doctor\'s Qualification in few words.', 'geodirectory' ),
		'htmlvar_name'			=> 'qualification',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'default_value'			=> '',
		'option_values' 		=> '',
		'clabels' 				=> 'Qualification'
    );

    // Gender
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TINYINT', 
		'field_type' => 'radio', 
		'field_type_key' => 'gender', 
		'admin_title' => __('Gender', 'geodirectory'), 
		'frontend_desc' => __('Choose the doctor\'s Gender', 'geodirectory'), 
		'frontend_title' => __('Gender', 'geodirectory'), 
		'htmlvar_name' => 'gender', 
		'sort_order' => '0',
		'option_values' => 'Male/1,Female/0',
		'clabels' => __('Gender', 'geodirectory'), 
		'is_active' => '1',
		'field_icon' => 'fas fa-venus-mars'
	);

    // Experience / Tranining
    $fields[] = array(
		'post_type' 			=> $post_type,
		'field_type'			=> 'text',
		'data_type'				=> 'INT',
		'extra'             	=> array(
										'is_price'	=> 0
									),
		'admin_title'			=> __('Experience / Tranining in years', 'geodirectory'),
		'frontend_title'		=> __('Experience / Tranining in years', 'geodirectory'),
		'frontend_desc'			=> __('Enter the Experience / Tranining in Years. Example : 2
', 'geodirectory'),
		'htmlvar_name'			=> 'experience',
		'is_active'				=> true,
		'for_admin_use'			=> false,
		'is_required'			=> true,
		'required_msg'			=> 'Please enter number of years of experience.',
		'field_icon'			=> 'fas fa-user-md',
		'css_class'				=> '',
		'cat_sort'				=> true,
		'cat_filter'			=> true,
		'show_on_pkg' 			=> $package,
		'clabels' 				=> 'experience'
    );

    // Experience in words
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TEXT',
		'field_type' => 'text',
		'admin_title' => __('Experience in words', 'geodirectory'),
		'frontend_desc' => __('Experience in words', 'geodirectory'),
		'frontend_title' => __('Experience in words', 'geodirectory'),
		'htmlvar_name' => 'experience_words',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'show_in' => '[detail]',
		'show_on_pkg' => $package,
		'clabels' => __('Experience in words', 'geodirectory')
	);

    // Online Consultations
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TINYINT', 
		'field_type' => 'radio', 
		'field_type_key' => 'for_online', 
		'admin_title' => __('Online Consultations', 'geodirectory'), 
		'frontend_desc' => __('Tick "Yes" if you are avaible for Online Consultations.', 'geodirectory'), 
		'frontend_title' => __('Online Consultations?', 'geodirectory'), 
		'htmlvar_name' => 'for_online', 
		'sort_order' => '0',
		'option_values' => 'Yes/1,No/0',
		'clabels' => __('Online Consultations?', 'geodirectory'), 
		'is_active' => '1',
		'field_icon' => 'fas fa-phone-volume'
	);

	// Languages known
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'VARCHAR',
		'field_type' => 'select',
		'field_type_key' => 'select',
		'is_active' => 1,
		'for_admin_use' => 0,
		'is_default' => 0,
		'admin_title' => __('Languages', 'geodirectory'),
		'frontend_desc' => __('Languages.', 'geodirectory'),
		'frontend_title' => __('Languages', 'geodirectory'),
		'htmlvar_name' => 'languages',
		'default_value' => '',
		'is_required' => '1',
		'required_msg' => __('Select your known languages', 'geodirectory'),
		'show_on_pkg' => $package,
		'option_values' => __('English, French, Spanish, Arabic, Russian, Japanese, Mandarin Chinese, Tamil, Hindi, Portuguese, German'),
		'field_icon' => 'fas fa-language',
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_on_pkg' => $package,
		'clabels' => __('Select your known languages', 'geodirectory'),
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
		'htmlvar_name' => 'doctor_areas_of_expertise',
		'default_value' => '',
		'is_required' => '1',
		'required_msg' => __('Select your areas of expertise', 'geodirectory'),
		'show_on_pkg' => $package,
		'option_values' => __( 'Oncology, Obstetrics and Gynecology, Pediatrics, Allergy & Immunology, Dermatology, Diagnostic Radiology, Emergency Medicine, Neurology, Ophthalmology, Preventive Medicine, Psychiatry, Urology', 'geodirectory'),
		'field_icon' => 'fas fa-home',
		'css_class' => '',
		'cat_sort' => 1,
		'cat_filter' => 1,
		'show_on_pkg' => $package,
		'clabels' => __('Areas of Expertise', 'geodirectory'),
	);

	// Areas of special interest and expertise.
    $fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TEXT',
		'field_type' => 'text',
		'admin_title' => __('Areas of special interest and expertise', 'geodirectory'),
		'frontend_desc' => __('Areas of special interest and expertise', 'geodirectory'),
		'frontend_title' => __('Areas of special interest and expertise', 'geodirectory'),
		'htmlvar_name' => 'expertise_words',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'show_in' => '[detail]',
		'show_on_pkg' => $package,
		'clabels' => __('Areas of special interest and expertise', 'geodirectory')
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

[gd_post_badge key='for_online' condition='is_not_empty' icon_class='fas fa-phone-volume' badge='Online Consultation: %%input%%' bg_color='#19be00' txt_color='#ffffff' alignment='block']
[gd_post_badge key='experience' condition='is_not_empty' icon_class='fas fa-user-md' badge='Experience: %%input%% years' alignment='block']

[gd_author_actions author_page_only='1']

[gd_post_fav show='' alignment='right' list_hide_secondary='2']
[gd_post_rating alignment='block' list_hide_secondary='2']

[gd_post_meta key='gender' alignment='left' text_alignment='left']
[gd_post_meta key='website' alignment='right' text_alignment='left']
[gd_post_meta key='post_category' alignment='block' text_alignment='left']
[gd_post_meta key='languages' alignment='block' text_alignment='left']
[gd_post_meta key='doctor_areas_of_expertise' alignment='block' text_alignment='left']

[gd_output_location location='listing']
[gd_post_content key='post_content' limit='60' max_height='120']
[gd_archive_item_section type='close' position='right']";
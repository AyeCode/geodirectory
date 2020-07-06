<?php
/**
 * GD doctors dummy data.
 *
 * @since 2.0.0
 * @package GeoDirectory
 */

// Set the dummy image url
$dummy_image_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/pets/images';
$dummy_caticon_url = 'https://wpgd-jzgngzymm1v50s3e3fqotwtenpjxuqsmvkua.netdna-ssl.com/dummy/pets/icons';


// Set the dummy categories
$dummy_categories  = array();
$dummy_categories['veterinary'] = array(
	'name'        => 'Veterinary',
	'icon'        => $dummy_caticon_url . '/Veterinary.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['rescue-centres'] = array(
	'name'        => 'Rescue Centres',
	'icon'        => $dummy_caticon_url . '/Rescue%20Centres.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['Aquariums'] = array(
	'name'        => 'Aquariums',
	'icon'        => $dummy_caticon_url . '/Aquariums.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['lost-found'] = array(
	'name'        => 'Lost and Found',
	'icon'        => $dummy_caticon_url . '/Lost%20and%20Found.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['pet-health'] = array(
	'name'        => 'Pet Health',
	'icon'        => $dummy_caticon_url . '/Pet%20Health.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['pet-breeders'] = array(
	'name'        => 'Pet Breeders',
	'parent-name' => 'Pet Health',
	'icon'        => $dummy_caticon_url . '/Pet%20Breeders.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-trainers'] = array(
	'name'        => 'Pet Trainers',
	'parent-name' => 'Pet Health',
	'icon'        => $dummy_caticon_url . '/Pet%20Trainers.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-walkers'] = array(
	'name'        => 'Pet Walkers',
	'parent-name' => 'Pet Health',
	'icon'        => $dummy_caticon_url . '/Pet%20Walkers.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pets'] = array(
	'name'        => 'Pets',
	'icon'        => $dummy_caticon_url . '/pets.png',
	'font_icon'   => 'fas fa-fan',
	'color'       => '#254e4e',
);

$dummy_categories['pet-shops'] = array(
	'name'        => 'Pet Shops',
	'parent-name' => 'Pets',
	'icon'        => $dummy_caticon_url . '/Pet%20Shops.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-adoption'] = array(
	'name'        => 'Pet Adoption',
	'parent-name' => 'Pets',
	'icon'        => $dummy_caticon_url . '/Pet%20Adoption.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-care'] = array(
	'name'        => 'Pet Care',
	'icon'        => $dummy_caticon_url . '/Pet%20Care.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-grooming'] = array(
	'name'        => 'Pet Grooming',
	'parent-name' => 'Pet Care',
	'icon'        => $dummy_caticon_url . '/Pet%20Grooming.png',
	'font_icon'   => 'fas fa-database',
	'color'       => '#254e4e',
);

$dummy_categories['pet-clothing'] = array(
	'name'        => 'Pet Clothing',
	'parent-name' => 'Pet Care',
	'icon'        => $dummy_caticon_url . '/Pet%20Clothing.png',
	'font_icon'   => 'fas fa-database',
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

//dummy post for Aquariums #1
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Stark Pet Shop',
	"post_content" => 'Stark Pet Shop is the suppliers of high grade Japanese koi imported from the most famous breeders & Japan’s stockists. Goods include food, pumps, pipework, Evolubia Aqua main dealer, Bioqube dealer, koi to suit all budgets, set in 6 acres and well worth the visit.',
	"post_category" => array( 'Aquariums' ),
	"post_tags" => array( 'aquatics', 'Koi Corp', 'fish' ),
	"email" => 'stark@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "10% OFF on your first purchase.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Veteran Discount'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-1.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Cash,Apple Pay',
	"post_dummy" => '1'
);

//dummy post for Aquariums #2
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Ocean Design Aquarium',
	"post_content" => 'Ocean Design Aquarium Established since 2003, we specialise in installations and maintenance of all types of aquariums, including cold water, freshwater, tropical, and saltwater. We can also work on marine fish only systems and marine reef tanks. We are located in Northwood, covering the M25 area.

	Our stock includes fish tanks, fish food, fishes, corals and much more. Delivery service is available for customers based in the M25 area. We are available 24 hours for emergency call-outs.',
	"post_category" => array( 'Aquariums' ),
	"post_tags" => array( 'aquarium and Pond Supplies', 'Aquarium Filters', 'Aquarium pumps' ),
	"email" => 'oda@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "20% OFF on your first purchase.",
	"shop_features" => array('Debit Cards', 'Wheelchair Accessible', 'Online Ordering'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-2.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Bank transfers',
	"post_dummy" => '1'
);

//dummy post for Aquariums #3
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Fishy Queen',
	"post_content" => 'Fishy Queen are the premier manufacturer of pond liners in the United Kingdom. Supplying pond liners to all parts of Great Britain, Europe and North Africa.

All at competitive prices, with free delivery on mainland UK.

Most items are available on free next day delivery if ordered before 1pm.',
	"post_category" => array( 'Aquariums' ),
	"post_tags" => array( 'Pond Accessories', 'Pond Liners', 'Fawcetts Liners' ),
	"email" => 'fishyqueen@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "15% OFF on all Pond Accessories.",
	"shop_features" => array('Debit Cards', 'Online Ordering'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-3.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Bank transfers,Apple Pay',
	"post_dummy" => '1'
);

//dummy post for Aquariums #4
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Aqua Forest',
	"post_content" => 'Aqua Forest are an on site fibreglass and grp moulding product manufacture based in Stones Green, Essex. Fibreglass is the perfect product for ponds and Koi ponds as it is strong, durability and waterproof.',
	"post_category" => array( 'Aquariums' ),
	"post_tags" => array( 'aquatics', 'marine fish', 'Pond design', 'Pond construction' ),
	"email" => 'aquaforest@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "5% OFF on all Pond Construction.",
	"shop_features" => array('Debit Cards', 'Veteran Discount', 'Wheelchair Accessible'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-4.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Cash',
	"post_dummy" => '1'
);

//dummy post for Aquariums #5
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Exotic Aquariums',
	"post_content" => 'We have been established since 2005 and have a loyal customer base and pride ourselves on our customer care and customer relations that is second to none. We always strive to meet every customers Exotic fishes need with a smile. We have a 5 star 3 year license rating by Hertsmere Borough Council.',
	"post_category" => array( 'Aquariums' ),
	"post_tags" => array( 'fish products', 'fish supplies', 'exotic fishes', 'shark' ),
	"email" => 'exoticaquariums@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "10% OFF on all Exotic Fishes.",
	"shop_features" => array('Credit Cards', 'On-Site ATM', 'Online Ordering'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-5.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Cash',
	"post_dummy" => '1'
);

//1. dummy post for Rescue Centres #6
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Happy Tails Rescue Centre',
	"post_content" => 'Happy Tails Rescue Centre, our reputable dog rescue centre finds loving homes for stray dogs taken off the streets of Hull, East Yorkshire, by the council’s dog wardens. Established since 1994, our family-run kennels have been taking in abandoned dogs and providing a safe haven for them, regardless of their breed.',
	"post_category" => array( 'Rescue Centres' ),
	"post_tags" => array( 'Animal Rescue', 'Ambulance', 'RESCUE CENTRES', 'Animal welfare' ),
	"email" => 'happy@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "5% OFF on your first purchase.",
	"shop_features" => array('Veteran Discount', 'Cash Only', 'Wheelchair Accessible'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-6.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Debit Card,Cash',
	"post_dummy" => '1'
);

//2. dummy post for Rescue Centres #7
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Missing Aiders',
	"post_content" => 'We at Missing Aiders are a well established Dog Rescue Centers located in UK. The sanctuary was originally founded by the late John Doe in 1958, and even now, we pride ourselves on our quality services we provide for dogs. We have rehomed several thousands of unwanted dogs into responsible loving homes, where they have spent the rest of the lives happy and content.',
	"post_category" => array( 'Rescue Centres' ),
	"post_tags" => array( 'Animal welfare society', 'Ambulance', 'rehoming centres', 'Animal rescue' ),
	"email" => 'happy@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "20% OFF on your first rescue.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-7.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Cash,Cheque',
	"post_dummy" => '1'
);

//3. dummy post for Rescue Centres #8
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => ' Wyld\'s Wingdom Informative',
	"post_content" => 'Promoting chinchilla welfare. Wyld\'s Wingdom Informative provides education, support, advice, and general discussion about all aspects of chinchilla keeping. A free online resource offering correct information, support, & advice on all aspects of keeping chinchillas as pets.

Here at Wyld\'s Wingdom Informative we provide education, support, advice, and general discussion about all aspects of chinchilla keeping; including health, behaviour, environment, feeding, welfare, and general care.',
	"post_category" => array( 'Rescue Centres' ),
	"post_tags" => array( 'Animal education', 'Animal Support', 'rehoming centres', 'Animal rescue' ),
	"email" => 'happy@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "50% OFF on your first rescue.",
	"shop_features" => array('On-Site ATM', 'Wheelchair Accessible', 'Online Ordering'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-8.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//4. dummy post for Lost and Found #9
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => ' Rescue Welfare Association',
	"post_content" => 'We opened in April 2001 with two kennels to help out a local organisation, because they had nowhere to place a dog with attitude. From that moment on we started to grow, funded entirely by our own finances and a lot of hard work, we built another six kennels, we have now 36 all with lighting and heating for the winter months.',
	"post_category" => array( 'Lost and Found' ),
	"post_tags" => array( 'Veterinary Practice', 'Pet Support', 'rehoming centres', 'Animal rescue' ),
	"email" => 'rescue@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "75% OFF on your first rescue.",
	"shop_features" => array('Delivery Only', 'Veteran Discount', 'Debit Cards'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-9.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Debit Card,Bank transfers',
	"post_dummy" => '1'
);

//5. dummy post for Lost and Found #10
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Wag My Tail Pet Refuge',
	"post_content" => 'Wag My Tail Pet Refuge,  We are a UK based registered charity, home to over 250 unwanted, abandoned or neglected exotic animals and rescued British wildlife.

The Exotic Pet Refuge can only survive from help from the public – we receive no government funding, and need over GBP65,000 a year to survive as we are now! We are also always looking to expand so that we can care for more and more animals.',
	"post_category" => array( 'Lost and Found' ),
	"post_tags" => array( 'Rescue Centres', 'Exotic Pets', 'Exotic Pet Refuge', 'Animal rescue' ),
	"email" => 'rescue@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "75% OFF on your first rescue.",
	"shop_features" => array('Credit Cards', 'Cash Only', 'On-Site ATM'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-10.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Cash,Cheque',
	"post_dummy" => '1'
);

//1. dummy post for Veterinary #11
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Hamster',
	"post_content" => 'A gentle and kind caretaker who loves animals. She knows many things about them and is well-respected by the others. She appears to be soft-spoken. She also seems to speak with a Northern England accent. She thinks Giraffes are smaller than Hamsters.',
	"post_category" => array( 'Veterinary' ),
	"post_tags" => array( 'Pet Services', 'Pet Care', 'Pet Healing', 'Animal rescue' ),
	"email" => 'drhamster@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "15% OFF on your first appointment.",
	"shop_features" => array('Wheelchair Accessible', 'Veteran Discount', 'On-Site ATM'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-11.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Cash,Apple Pay',
	"post_dummy" => '1'
);

//2. dummy post for Veterinary #12
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Dr. Hershel Greene',
	"post_content" => 'A gentle and kind caretaker who loves animals. He knows many things about them and is well-respected by the others. He appears to be soft-spoken. She also seems to speak with a Northern England accent. but he\'s a a good man to have around in a crisis, big-hearted, caring, and tough.',
	"post_category" => array( 'Veterinary' ),
	"post_tags" => array( 'Pet Services', 'Pet Care', 'Pet Healing', 'Animal rescue' ),
	"email" => 'drhershel@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "50% OFF on your Annual Pet Service Scheme.",
	"shop_features" => array('Debit Cards', 'Veteran Discount', 'Credit Cards'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-12.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Cash',
	"post_dummy" => '1'
);

//3. dummy post for Veterinary #13
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Doctor John Dolittle',
	"post_content" => 'He is a physician who shuns human patients in favour of animals, with whom he can speak in their own languages. He later becomes a naturalist, using his abilities to speak with animals to better understand nature and the history of the world!',
	"post_category" => array( 'Veterinary' ),
	"post_tags" => array( 'Pet Services', 'Pet Care', 'Pet Healing', 'Animal rescue' ),
	"email" => 'drhershel@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "72% OFF on your Annual Pet Service Scheme.",
	"shop_features" => array('On-Site ATM', 'Wheelchair Accessible', 'Debit Cards'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-13.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Cash',
	"post_dummy" => '1'
);

//4. dummy post for Veterinary #14
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Doctor Gerry Harding',
	"post_content" => ' Dr. Gerald "Gerry" Harding was Jurassic Park\'s chief veterinarian. He supervised the treatment of sick pet dinosaurs and the recapture of escaped animals. Whenever a dinosaur was sick, he would be the one out in the field treating it. ',
	"post_category" => array( 'Veterinary' ),
	"post_tags" => array( 'Pet Services', 'Pet Care', 'Pet Healing', 'Animal rescue' ),
	"email" => 'drhershel@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "Free First Consultation!",
	"shop_features" => array('On-Site ATM', 'Wheelchair Accessible', 'Debit Cards'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-14.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Credit Card,Cash',
	"post_dummy" => '1'
);

//5. dummy post for Veterinary #15
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Doctor Barry Goodman',
	"post_content" => 'Dr. Barry Goodman, M.D., is the head doctor on the Animal Aid Organization.',
	"post_category" => array( 'Veterinary' ),
	"post_tags" => array( 'Pet Services', 'Pet Surgery', 'Pet Healing', 'Ambulance Service' ),
	"email" => 'drhershel@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "100% Off on First two Consultation!",
	"shop_features" => array('Veteran Discount', 'Wheelchair Accessible', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-15.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Debit Card,Cash',
	"post_dummy" => '1'
);

//1. dummy post for Pet Health #16
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Animal Lovers Pet Shop',
	"post_content" => 'We are constantly updating our website with available dogs and relevant information. There is also a wealth of information on the adoption process. We must say however that if you are interested in a particular dog, we are more than happy to reserve them for you so you can meet them, but we can only reserve them for 24 hours.',
	"post_category" => array( 'Pet Health', 'Pet Breeders' ),
	"post_tags" => array( 'Cat Breeders', 'Dog Breeders', 'animal feedstuffs', 'animal runs (cages)' ),
	"email" => 'animallovers@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "10% Off on Dog and Cat Cages!",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-16.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//2. dummy post for Pet Health #17
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Furbabies Trainers and Breeders',
	"post_content" => 'Established in 2000 and with over 15 years experience looking after animals, we have built a large portfolio of happy and satisfied customers. We are a well established, reliable and family run business based in Hartlepool. Fully licenced, small & friendly cattery. Holiday, weekend & long term boarding. All diets catered for – heated beds & pens. Cats groomed daily – nervous cats reassured. Open all year. Home of British Shorthairs.',
	"post_category" => array( 'Pet Health', 'Pet Trainers', 'Pet Walkers' ),
	"post_tags" => array( 'Cat Walkers', 'Dog Trainers', 'Cat breeders', 'Cat food' ),
	"email" => 'animallovers@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We train cat and dogs, and help you walk your pets.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-17.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//3. dummy post for Pet Health #18
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Meow Meow Cattery',
	"post_content" => 'Established in 2000 and with over 15 years experience looking after animals, Bengal cats and kittens Dog and cat micro chipping Open all year. Home of Persian Cats.',
	"post_category" => array( 'Pet Breeders', 'Pet Trainers', 'Pet Walkers' ),
	"post_tags" => array( 'Cat Chipping', 'Cat Walkers', 'Cat Trainers', 'Cat breeders', 'Cat food' ),
	"email" => 'meow@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We train cats, and help registering the cats and chipping.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-18.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//4. dummy post for Pet Health #19
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Fifi\'s Pet House',
	"post_content" => 'Based in the UK. We sell home reared pedigree Bengal kittens and German Dogs, under my TICA Registered Prefix Affectionate. Bengal cats have markings similar to wild cats. They have a wonderful exotic and exclusive appeal, amazing intelligence, energy, and a love for fun. We welcome anyone who can offer our kittens a loving home.We breed kittens in professional conditions in our family home. Kittens are treated like family pets until the are sold and leave for their new homes at 13 weeks old. All kittens are Fully Vaccinated, TICA Registered, and Microchiped.',
	"post_category" => array( 'Pet Breeders', 'Pet Trainers', 'Pet Walkers' ),
	"post_tags" => array( 'Pet Chipping', 'Pet Walkers', 'Pet Trainers', 'Pet Chipping' ),
	"email" => 'fifi@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We Sell Bengal kittens at an affordable price.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-19.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//5. dummy post for Pet Health #20
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Old Town Horse Stables',
	"post_content" => 'We are a friendly professional riding school set in beautiful surroundings in the Chessington/Claygate area. We are B.H.S. Approved and our aim is to instruct all ages and abilities to a high standard with safety as our top priority. We have a sympathetic approach to our horses and teach people to ride as we believe the horses wish to ridden, which achieves the best out of both horse and rider.',
	"post_category" => array( 'Pet Trainers', 'Pet Walkers' ),
	"post_tags" => array( 'Horse Chipping', 'Horse Walkers', 'Horse Trainers' ),
	"email" => 'oldtown@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We teach horse riding @30% offer.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-20.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//1. dummy post for Pet Care #21
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => '4 Paws Boutique & Spa',
	"post_content" => 'We pride ourselves in not just being a sales person on the end of a phone knowing very little about their products – we are always on hand to give any advice needed on what will best suit you and answer any questions you may have. We are able to ship to any country in the world and we accept all major credit cards including Apple Pay.',
	"post_category" => array( 'Pet Care', 'Pet Grooming', 'Pet Clothing' ),
	"post_tags" => array( 'Clothes', 'Pet Alarms', 'Pet Hair Style' ),
	"email" => '4paws@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "30% for your first grooming kit for your lovely kittens.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-21.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//2. dummy post for Pet Care #22
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Bubbly Cuddly Pets',
	"post_content" => 'Bubbly Cuddly Pets is a large family run pet shop in East Yorkshire. We offer a fantastic range of products at competitive prices. We stock a vast range of animal, fish, birds and reptiles plus all the accessories, housing, feed, toys. etc.',
	"post_category" => array( 'Pet Care' ),
	"post_tags" => array( 'Pet Supplies', 'Pet Cage', 'Pet Pool' ),
	"email" => 'bubblycuddly@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "25% for your first pet cage.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-22.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//3. dummy post for Pet Care #23
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Waggles Bros Pet Resort & Spa',
	"post_content" => 'We are a family business running 30 years, Dog Food, Wild Bird Seed, Horse Feed, everything for your pets needs and feeds, delivery throughout the county. We specialise in all aspects of Wild Bird Care. Premium seed, Sunflower seed, Nyger seed and peanuts. Also a large range of fatballs, cocnuts, suet logs & pellets and seeds and nut feeders. Delivery service available.',
	"post_category" => array( 'Pet Care', 'Pet Clothing' ),
	"post_tags" => array( 'Premium Seeds', 'peanuts', 'Pet Dress' ),
	"email" => 'waggles@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "25% for your first pack of seeds..",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-23.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//4. dummy post for Pet Care #24
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Wag My Tail Pet Grooming',
	"post_content" => 'Wag My Tail Pet Grooming now offers the best in class dog grooming services right at your doorstep! With professional dog groomers  who take care of every aspect of grooming, we have the right tools and modern equipment to fully service your pets grooming requirements.

We not only offer luxury bath service at your doorstep, but also lay emphasis on your pets health, safety and comfort.',
	"post_category" => array( 'Pet Clothing', 'Pet Grooming' ),
	"post_tags" => array( 'Hair shed controls', 'mats and dirt', 'Dog Hygiene' ),
	"email" => 'mytail@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "25% off on GET THE BEST PROFESSIONAL DOG GROOMING SERVICES",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-24.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//5. dummy post for Pet Care #25
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Splash and Dash Groomerie',
	"post_content" => 'Shiny clean is achievable! Our groomers take utmost care, as they groom your pets in the comfort of your home.

We not only offer luxury bath service at your doorstep, but also lay emphasis on your pets health, safety and comfort.',
	"post_category" => array( 'Pet Clothing', 'Pet Grooming' ),
	"post_tags" => array( 'Blow Drying', 'Nail Clipping', 'Ear & Eye Cleaning' ),
	"email" => 'splash@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "10% off on Designer Haircut, Regular Bath and Anal Gland Cleaning",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-25.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//1. dummy post for Pets #26
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Bella Woof Reptile Den',
	"post_content" => 'We have extensive knowledge about all reptiles Amphibians, Arachnids, Birds and Exotic Mammals including Crocodiles, Owls, Parrots, Monkeys and Fruit Bats with over 30 years experience.
			We stock a variety of reptiles and amphibians:-
			Lizards
			Snakes
			Tortoises & Turtles
			Amphibians
			Spiders & Scorpions',
	"post_category" => array( 'Pets', 'Pet Shops' ),
	"post_tags" => array( 'Live Food', 'Frozen Food', 'Pet Housing & Decor' ),
	"email" => 'bella@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "10% off on Reptiles",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-26.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//2. dummy post for Pets #27
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Rubeus Hagrid Menagerie',
	"post_content" => 'Rubeus Hagrid Menagerie\'s newest exotic pet shop and educational centre. Our 4,500 sqft Centre is home to over 300 animals and we stock a range of over 250 animal products, and we can order in much more. So from exotic rare animals to domestic common stuff, you will find it all here.',
	"post_category" => array( 'Pet Shops', 'Pet Adoption' ),
	"post_tags" => array( 'Reptiles', 'Rescue centre', 'Reptile Decor', 'Reptile Toys' ),
	"email" => 'hagrid@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "50% off on Reptile Decor",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-27.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//3. dummy post for Pets #28
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Bird Man',
	"post_content" => 'We specialise in giving you access to the very best bedding products available whether you are a keen amateur, serious breeder or competitive bird fancier.

	Our range has been developed over years and sourced with particular care and responsibility. Uniquely every product we offer has been manufactured from virgin untreated solid wood, specifically for the purpose intended – bedding. We only use first generation material from virgin timber as in our opinion recycled second generation material / by-products of other manufacturing process cannot be traced nor offer a guarantee with regards hygiene and undesirable additions.',
	"post_category" => array( 'Pet Shops', 'Pets' ),
	"post_tags" => array( 'Gold Flakes', 'Bird Food', 'Bird Cage', 'Bird Rescue' ),
	"email" => 'hagrid@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "MULTIPLE BAG DISCOUNTS, ORDER NOW FOR DELIVERY WITHIN 3 DAYS OR CONTACT US FOR A FREE SAMPLE.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-28.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//4. dummy post for Pets #29
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Jungle Pet Shop',
	"post_content" => 'Animal Magic first opened its doors in 2007 and has never looked back. The family business which is run and owned by Claire Parker specialises in parrots and parrot sitting (bird boarding). There is also a wide selection of guinea pigs, rabbits, pet food, enclosures, cages and huts.

	We at Jungle Pet Shop are a quality pet shop who are not only selling a wide variety of pet food, bedding and accessories but also a wide selection of pets. From birds and rabbits to kittens, we guarantee that you will find your future best friend in our store! Our friendly, family run business is located in UK, in Kent and is open everday apart from Sunday from 9:00am till 5:30pm.',
	"post_category" => array( 'Pet Shops', 'Pets' ),
	"post_tags" => array( 'Bird sitting', 'Small furry pets', 'Toys and accessories' ),
	"email" => 'jungle@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We also specialise in parrots and bird boarding.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-29.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);

//5. dummy post for Pets #30
$dummy_posts[] = array(
	"post_type" => $post_type,
	"post_status" => 'publish',
	"post_title" => 'Angel\'s Paradise Pet Shop',
	"post_content" => 'Welcome to Hutton Aquatic products. We specialize in high quality aquarium/marine/tank/pond sealers, tapes and paints, to name a few!
We began in 1986 with the aim of treating customers as human beings who deserve to be served with honesty and respect. We are a family owned business based in the coastal town of Christchurch, Dorset and will always try to answer your questions and queries as soon as possible, and with honesty.
',
	"post_category" => array( 'Pet Shops', 'Pets', 'Pet Adoption' ),
	"post_tags" => array( 'Algae Scraper', 'Pond Liner Patch', 'Cleaning Solvent' ),
	"email" => 'Angel@example.com',
	"website" => 'http://example.com/',
	"special_offers" => "We are very proud to also offer amazing services such as: grooming, day care, training, puppy classes.",
	"shop_features" => array('Debit Cards', 'Credit Cards', 'Cash Only'),
	"phone" => '+01212125781',
	"twitter" => 'https://twitter.com/',
	"facebook" => 'https://facebook.com/',
	"post_images"   => array(
		"$dummy_image_url/pp-30.jpg",
		"$dummy_image_url/ppr-1.jpg",
		"$dummy_image_url/ppr-2.jpg",
		"$dummy_image_url/ppr-3.jpg",
		"$dummy_image_url/ppr-4.jpg"
	),
	"business_hours"=> '["Mo 13:00-22:00","Tu 13:00-22:00","We 13:00-22:00","Th 13:00-22:00","Fr 13:00-22:00","Sa 13:00-23:00","Su 15:00-23:00"]',
	'payment_types' => 'Apple Pay,Credit Card',
	"post_dummy" => '1'
);


function geodir_extra_custom_fields_pets( $fields, $post_type, $package_id ) {
	$package = $package_id != '' ? array( $package_id ) : '';


	// add business hours
	$fields[] = array(
		'post_type' => $post_type,
		'data_type' => 'TEXT',
		'field_type' => 'business_hours',
		'admin_title' => __('Business Hours', 'geodirectory'),
		'frontend_desc' => __('Select your business opening/operating hours.', 'geodirectory'),
		'frontend_title' => __('Business Hours', 'geodirectory'),
		'htmlvar_name' => 'business_hours',
		'default_value' => '',
		'is_active' => '1',
		'option_values' => '',
		'is_default' => '0',
		'show_in' => '[owntab],[detail]',
		'show_on_pkg' => '',
		'field_icon' => 'fas fa-clock',
		'clabels' => __('Business Hours', 'geodirectory')
	);

	// shop features
	$fields[] = array(
		'post_type'           => $post_type,
		'field_type'          => 'multiselect',
		'data_type'           => 'VARCHAR',
		'admin_title'         => __('Features', 'geodirectory'),
		'frontend_title'      => __('Features', 'geodirectory'),
		'frontend_desc'       => __('Select the features.', 'geodirectory'),
		'htmlvar_name'        => 'shop_features',
		'is_active'           => true,
		'for_admin_use'       => false,
		'default_value'       => '',
		'show_in' 	          => '[detail],[listing]',
		'is_required'         => false,
		'option_values'       => 'Debit Cards, Credit Cards, Cash Only, On-Site ATM, Wheelchair Accessible, Online Ordering, Delivery Only, Veteran Discount',
		'validation_pattern'  => '',
		'validation_msg'      => '',
		'required_msg'        => '',
		'field_icon'          => 'fas fa-store',
		'css_class'           => 'gd-comma-list',
		'cat_sort'            => true,
		'cat_filter'	      => true,
		'show_on_pkg' 		  => $package,
		'clabels' 			  => 'Features'
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
		'option_values'      => 'Cash,Cheque,Apple Pay,Credit Card,Debit Card,Bank transfers',
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

[gd_post_images type='image' ajax_load='true' link_to='post' types='logo,post_images']
[gd_archive_item_section type='close' position='left']
[gd_archive_item_section type='open' position='right']
[gd_post_title tag='h2']

[gd_author_actions author_page_only='1']

[gd_post_rating alignment='block' list_hide_secondary='2']
[gd_post_badge key='payment_types' condition='is_not_empty' icon_class='far fa-money-bill-alt' badge='Payment: %%input%%' bg_color='#19be00' txt_color='#ffffff' alignment='block']

[gd_post_meta key='website' alignment='left' text_alignment='left']
[gd_post_fav show='' alignment='right' list_hide_secondary='2']

[gd_post_meta key="business_hours" alignment="block" text_alignment="left"]

[gd_post_meta key='post_category' alignment='block' text_alignment='left']
[gd_post_meta key='post_tags' alignment='block' text_alignment='left']
[gd_post_meta key='shop_features' alignment='block' text_alignment='left']
[gd_post_meta key='special_offers' alignment='block' text_alignment='left']


[gd_post_content key='post_content' limit='60']
[gd_archive_item_section type='close' position='right']";
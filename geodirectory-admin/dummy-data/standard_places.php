<?php
/**
 * GD Dummy data.
 *
 * @since 1.0.0
 * @package GeoDirectory
 */

global $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2,$wpdb, $current_user,$dummy_post_index;
$post_info = array();
$image_array = array();
$post_meta = array();

if($dummy_post_index==1){
    $category_array = array('Attractions', 'Hotels', 'Restaurants', 'Food Nightlife', 'Festival', 'Videos', 'Feature');
    geodir_dummy_data_taxonomies($post_type,$category_array );
    update_option($post_type.'_dummy_data_type','standard_places');
}

if (geodir_dummy_folder_exists())
    $dummy_image_url = geodir_plugin_url() . "/geodirectory-admin/dummy";
else
    $dummy_image_url = 'https://wpgeodirectory.com/dummy';

$dummy_image_url = apply_filters('place_dummy_image_url', $dummy_image_url);

switch ($dummy_post_index) {

    case(1):
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";
        $image_array[] = "$dummy_image_url/a10.jpg";
        $image_array[] = "$dummy_image_url/a11.jpg";


        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Franklin Square',
            "post_desc" => ' <h3> Location </h3>
		
		6th and Race Streets in Historic Philadelphia
		<h3>The Experience</h3>
		
		One of Philadelphia&acute;s newest historic attractions is also one of its oldest.
		
		Franklin Square, one of the five public squares that William Penn laid out in his original plan for the city, has undergone a dramatic renovation.
		
		The park now boasts several all new, family-friendly attractions, including a miniature golf course, a classic carousel, storytelling benches, a picnic area and more.
		
		<h3>Mini Golf </h3>
		
		At Philly Mini Golf, an 18-hole miniature golf course decorated with some of Philadelphia&acute;s favorite icons, play a round of putt-putt and learn a little history at the same time.
		<h3>Carousel </h3>
		
		Close your eyes and take a nostalgic ride on the Philadelphia Park Liberty Carousel, a classic tribute to Philadelphia&acute;s great heritage of carousel-making. It&acute;s sure to be a instant kid favorite.
		Storytelling Benches
		
		Then catch up on your history at one of the storytelling benches located throughout the park, where you can hear tales of Franklin Square&acute;s past, or learn about the many communities touched by the Square, courtesy of the friendly storytellers of Once Upon a Nation.
		<h3>Fountain</h3>
		
		And emanating from the corners of the historic park, four new herringbone brick walking paths with nighttime lighting bring even more charm to the Square after dark. The paths lead to the centerpiece of the Square, the Franklin Square Fountain, a marble masterpiece built in 1838 surrounded by wrought iron fences, which is currently still going under cosmetic restoration.
		<h3>The History </h3>
		
		Originally named “North East Publick Square,” the 7.5-acre green is one of five original squares that William Penn laid out in his original plan of the city in 1682. The Square was renamed in honor of Benjamin Franklin in 1825.
		
		Over the years, the area has been used as a cattle pasture, a horse and cattle market, a burial ground, a drill and parade ground for the American military during the War of 1812 and, finally, a city park.
		
		In 1837, the city made Franklin Square into a public park and an elegant fountain was constructed in its center, a fountain thought to be the oldest surviving fountain in William Penn&acute;s five historic squares. The others are Rittenhouse, Washington, Logan and Center Square, where City Hall is now located.
		<h3>SquareBurger </h3>
		
		Just in time for summer, Franklin Square has opened SquareBurger, a Stephen Starr-run “burger shack” selling summer staples: hot dogs, fries, milkshakes (made with Tasty Kakes) and, of course, hamburgers and cheeseburgers.
		
		SquareBurger is open until October - perfect for a couple bites between rounds of miniature golf!',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions', 'Feature')),
            "post_tags" => array('Tags', 'Sample Tags'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 1 p.m., Sunday 10 am to 9 pm',
            "geodir_contact" => '(111) 677-4444',
            "geodir_email" => 'info@franklinsq.com',
            "geodir_website" => 'http://franklinsquare.com',
            "geodir_twitter" => 'http://twitter.com/franklinsquare',
            "geodir_facebook" => 'http://facebook.com/franklinsquare',
            "post_dummy" => '1'
        );


        break;
    case 2:
        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 2///
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";
        $image_array[] = "$dummy_image_url/a10.jpg";
        $image_array[] = "$dummy_image_url/a11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Please Touch Museum',
            "post_desc" => '<h3>New Location! </h3>
		
		Who doesn&acute;t love the Please Touch Museum? And now, taking kids to the Museum is better than ever. The nation&acute;s premier children&acute;s museum - which has been a beloved landmark since it opened in 1976 - has a new home in Fairmount Park, opening its doors to a world of educational, hands-on fun.
		
		The new location in Memorial Hall - a National Historic Landmark built in 1876 for the Centennial Exhibition celebrating the country&acute;s 100th birthday - will boast three times more space for exhibitions and programs.
		
		Just outside the museum, kids and adults will also delight in riding the meticulously restored 1908 Woodside Park Dentzel Carousel, built in Philadelphia for a now-defunct amusement park 10 blocks from Memorial Hall.
		
		Visit The Please Touch Museum for more info!
		<h3>The Experience </h3>
		
		The city&acute;s award-winning children&acute;s museum is fun-filled, totally hands-on, and so delightful that adults are entertained, too. Each nook and cranny has a different theme - from the fantastic to the practical. In Alice&acute;s Adventures in Wonderland, kids can play croquet with the Queen and sip tea with the Mad Hatter; nearby, oversized props bring Maurice Sendak&acute;s classics to life.
		
		Kids can take the wheel of a real bus and sail a boat on a mini-Delaware River; in “Nature&acute;s Pond,” the youngest visitors (age 3 and under) can discover animals nestled among high grass and a lily pond, or enjoy stories and nursery rhymes in “Fairytale Garden.” Please Touch is also a first live theater experience for young children - Please Touch Playhouse performances are original and interactive and take place daily!
		
		Please Touch Museum tends to be busier on rainy days. You may want to schedule your visit on fair weather days. Mornings are also a busy time with most school groups visiting during this time. Afternoons are a great time to visit the museum as well as Mondays when groups are not scheduled.
		<h3>History </h3>
		
		One of the lasting museums from the tourist upgrade of Philadelphia that coincided with the 1976 Bicentennial celebration, Please Touch Museum® filled a gap in the city&acute;s cultural scene. Other museums in the area certainly have sections for children, but Please Touch Museum&acute;s new home not only offers three toddler areas, but also exciting exhibit components for older siblings (for ages 7 and up).
		<h3>Visiting Tips </h3>
		
		Please Touch Museum tends to be busier on rainy days. You may want to schedule your visit on fair weather days. Mornings are also a busy time with most school groups visiting during this time. Afternoons are a great time to visit the museum as well as Mondays when groups are not scheduled.
		<h3>Insider Tip </h3>
		
		The museum has a full schedule of craft activities and music, dance and storytelling performances, which are entertaining for both kids and adults.
		<h3>Great Kids’ Stuff </h3>
		
		In The Supermarket, kids take control: They can stock the shelves, load their cart and ring up the order.
		Buy Tickets Online In Advance
		
		You can buy admission tickets to the Please Touch Museum online through our partners at the Independence Visitor Center. Just click the button below.',

            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions', 'Feature')),
            "post_tags" => array('Tags', 'Sample Tags'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 1 p.m., Sunday 10 am to 9 pm',
            "geodir_contact" => '(222) 777-1111',
            "geodir_email" => 'info@pleasetouchmuseum.com',
            "geodir_website" => 'http://pleasetouchmuseum.com',
            "geodir_twitter" => 'http://twitter.com/pleasetouchmuseum',
            "geodir_facebook" => 'http://facebook.com/pleasetouchmuseum',
            "post_dummy" => '1'
        );

        break;
    case 3:
        $image_array = array();
        $post_meta = array();

        ////post end///
        /// Attractions ////post start 3///
        $image_array[] = "$dummy_image_url/a9.jpg";
        $image_array[] = "$dummy_image_url/a10.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Longwood Gardens',
            "post_desc" => '<h3>The Experience </h3>
		
		When you&acute;re at Longwood Gardens, it&acute;s easy to imagine that you&acute;re at a giant, royal garden in Europe. Stroll along the many paths through acres of exquisitely maintained grounds featuring 11,000 different types of plants.
		
		Encounter a new vista at each turn: the Italian Water Garden, Flower Garden Walk, aquatic display gardens and many others. Amble through Peirce&acute;s Woods, eight outdoor “rooms” of distinct woodland habitats.
		
		Inside the Conservatory is a lush world of exotic flowers, cacti, bromeliads, ferns and bonsai. Each season brings a different pleasure: spring magnolias and azaleas; summer roses and water lilies; fall foliage and chrysanthemums; and winter camellias, orchids and palms.
		
		On land Quaker settler George Peirce purchased from William Penn, Peirce&acute;s grandsons planted an impressive arboretum. The presence of a sawmill on the property prompted industrialist Pierre Samuel du Pont to buy the land in 1906 to save the trees.
		
		Christmas is spectacularly celebrated with carillon concerts, poinsettias and thousands of lights; summer evenings are embellished with concerts, illuminated fountain displays and occasional fireworks.
		<h3>Come Prepared </h3>
		
		Longwood Gardens is open daily, year-round.
		<h3>Don&acute;t Miss </h3>
		
		Indoor Children&acute;s Garden - Surrounded by tree-covered seating and Longwood&acute;s famous fountains, the new Indoor Children&acute;s Garden provides a safe and engaging space where children can learn about nature with amazing plants and fun activities around every corner.
		
		The Garden features a Central Cove, a Rain Pavilion and a Bamboo Maze, filled with a jungle of tree-sized bamboos for children to explore.
		<h3>Outsider&acute;s Tip </h3>
		
		There are 17 fountains in the Indoor Children&acute;s Garden to enjoy, where children will want to splash and play. An extra shirt or small towel might come in handy!
		<h3>Buy Tickets Online In Advance </h3>
		
		You can buy admission tickets to Longwood Gardens online through our partners at the Independence Visitor Center. Just click the button below.',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('wood', 'garden'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 1 p.m., Sunday 10 am to 9 pm',
            "geodir_contact" => '(111) 888-1111',
            "geodir_email" => 'info@longwoodgardens.com',
            "geodir_website" => 'http://longwoodgardens.com',
            "geodir_twitter" => 'http://twitter.com/longwoodgardens',
            "geodir_facebook" => 'http://facebook.com/longwoodgardens',
            "post_dummy" => '1'
        );
        break;
    ////post end///
    /// Attractions ////post start 4///

    case 4:

        $image_array = array();
        $post_meta = array();
        $image_array[] = "$dummy_image_url/a11.jpg";
        $image_array[] = "$dummy_image_url/a10.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'The Philadelphia Zoo',
            "post_desc" => '<h3>The Zoo 150th Birthday</h3>
		
		The Philadelphia Zoo celebrated its 150th anniversary in 2009. So stop by and celebrate this major achievement at America&acute;s first zoo!
		
		<h3>McNeil Avian Center </h3>
		
		On May 30, 2009 the 17.5-million McNeil Avian Center opened to the public.
		
		This new aviary incorporates lush, walk-through habitats where visitors can discover more than 100 spectacular birds from around the world, many of them rare and endangered. And in the multi-sensory 4-D Migration Theater, viewers can follow Otis the Oriole on his first migration south from where he hatched in Fairmount Park.
		
		
		<h3>The Experience at the Zoo</h3>
		
		One of the best laid-out and most animal-packed zoos in the country is set among a charming 42-acre Victorian garden with tree-lined walks, formal shrubbery, ornate iron cages and animal sculptures. The zoo has garnered many “firsts” in addition to being the first zoo charted in the United States (1859).
		
		The first orangutan and chimp births in a U.S. zoo (1928), world&acute;s first Children&acute;s Zoo (1957), and the first U.S. exhibit of white lions (1993), among others.
		
		In addition to its animals, the zoo is known for its historic architecture, which includes the country home of William Penn&acute;s grandson, its botanical collections of over 500 plant species, its groundbreaking research and its fine veterinary facilities.
		Big Cat Falls
		
		The highly anticipated pride of the Philadelphia Zoo, Bank of America Big Cat Falls, home to felines from around the world, opened in 2006. The lush new exhibition features waterfalls, pools, authentic plantings and a simulated research station for aspiring zoologists.
		
		Lions, leopards, jaguars, pumas, tigers and seven new cubs are the star attractions.
		<h3>Visitor Details </h3>
		
		Open daily, year-round. Parking can be tight so public transit is a great option.
		
		Check out the Zoo&acute;s trolley shuttle, available through October, making hourly stops at the Independence Visitor Center and 30th Street Station. Service is available starting at 10 a.m. seven days a week through August 31, 2008, with weekends-only service in September and October.
		
		SEPTA Routes 15 and 32 Buses stop within blocks of the zoo. Find specific stops and schedules here.
		<h3>History</h3>
		
		The nation&acute;s oldest zoo was chartered in 1859, but the impending Civil War delayed its opening until 1874. In addition to its animals, the zoo is known for its historic architecture, which includes the country home of William Penn&acute;s grandson; its botanical collections of over 500 plant species; its groundbreaking research and its fine veterinary facilities.
		
		The Primate Reserve, Carnivore Kingdom, and Rare Animal Conservation Center, with its tree kangaroos and blue-eyed lemurs, are brand new, but there&acute;s still fun to be had in the historic, old-style bird, pachyderm and carnivore houses. In the Treehouse, kids can investigate the world from an animal&acute;s perspective; outdoors, the Zoo Balloon lifts passengers 400 feet into the air for a bird&acute;s-eye view of the zoo.',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('wood', 'garden'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 11.30 a.m., Sunday 11 am to 7 pm',
            "geodir_contact" => '(211) 143-1900',
            "geodir_email" => 'info@philadelphiazoo.com',
            "geodir_website" => 'http://philadelphiazoo.com',
            "geodir_twitter" => 'http://twitter.com/philadelphiazoo',
            "geodir_facebook" => 'http://facebook.com/philadelphiazoo',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 4///
        break;
    case 5:


        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 5///
        $image_array[] = "$dummy_image_url/a12.jpg";
        $image_array[] = "$dummy_image_url/a13.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'National Constitution Center',
            "post_desc" => '<h3>The Experience</h3>
	
	It only four pages long, but the U.S. Constitution is among the most influential and important documents in the history of the world.
	
	The 160,000-square-foot National Constitution Center explores and explains this amazing document through high-tech exhibits, artifacts, and interactive displays. The Kimmel Theater, a 350-seat star-shaped theater, features Freedom Rising, a multimedia production combining film, a live actor and video projection on a 360° screen to tell the stirring story of We the people.
	
	Then experience it yourself: don judicial robes to render your opinion on key Supreme Court cases, then take the Presidential oath of the office.
	
	In Signers Hall, where life-size bronze figures of the Constitution&acute;s signers and dissenters are displayed, visitors can choose to sign or dissent.
	
	One of the rare original public copies of the Constitution is on display.
	<h3>History </h3>
	
	Freedom of speech, protection from unlawful search and seizure, and other individual rights were not part of the original Constitution. Recognizing its imperfections, the authors built in a mechanism to amend the Constitution, making it adaptable for unknown eventualities.
	
	The first ten amendments guaranteeing numerous personal freedoms - The Bill of Rights - were not ratified until 1791.
	<h3>Insider Tip </h3>
	
	While the Center hosts amazing evergreen presentations, take a look at the Events Calendar for the latest premiere or traveling exhibit.
	<h3>Kids Stuff </h3>
	
	The Center frequently hosts special events with a focus on children that include informative and engaging hands-on activities. For specific information, check out the Center website.',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions', 'Feature')),
            "post_tags" => array('Tag', 'Center'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 9.30 a.m., Sunday 11 am to 7 pm',
            "geodir_contact" => '(111) 111-1111',
            "geodir_email" => 'info@ncc.com',
            "geodir_website" => 'http://ncc.com',
            "geodir_twitter" => 'http://twitter.com/ncc',
            "geodir_facebook" => 'http://facebook.com/ncc',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 5///
        break;
    case 6:


        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 6///
        $image_array[] = "$dummy_image_url/a14.jpg";
        $image_array[] = "$dummy_image_url/a13.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Sadsbury Woods Preserve',
            "post_desc" => 'A more than 500-acre nature preserve ideal for walking and hiking, Sadsbury Woods is also an important habitat for interior nesting birds and small mammals. An increasingly rare area of interior woodlands, defined as an area at least 300 feet from any road, lawn or meadow, provides a critical habitat for many species of birds, especially neo-tropical migrant songbirds.
	
	Situated on the western edge of Chester County, the land remains much as it did centuries ago, and now serves as a permanent refuge in an area facing dramatically increasing development pressure.
	
	The colorful birds that breed in the forest during the spring and summer months fly to South America for the winter. To survive here, they need abundant food and protection from the weather and predators, something they&acute;re able to find in Sadsbury Woods. A recent bird count identified more than 40 different species in just one morning.
	
	The preserve has been assembled from more than one dozen parcels, an effort that was made possible thanks to landowners who were willing to sell their land for conservation purposes. One such landowner recalled exploring these woods as a child and wanted to ensure that his grandchildren and great-grandchildren would be able to do the same. Natural Lands Trust is working to expand the preserve, and hopes to eventually protect a total of 600 acres.
	Support the Natural Lands Trust
	
	The Natural Lands Trust seeks volunteers and members to help protect and care for Sadsbury Woods and its many other natural areas. Members are invited to dozens of outings each year including canoe trips, bird walks, hikes and much more. 
	Come Prepared
	
	The preserve is open from sunrise to sunset. Pets must be leashed. Alcoholic beverages, motorized vehicles and mountain bikes are not permitted. Horseback riders are welcome, but you must ride in, because there nowhere to park a trailer. Maps and other material are available in the kiosk by the parking area.
	Outsider Tip
	
	The deep forest is a great place for spotting neo-tropical songbirds in the spring and summer months',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('sample', 'tags'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 12.30 p.m., Sunday 12 pm to 7 pm',
            "geodir_contact" => '(222) 999-9999',
            "geodir_email" => 'info@swp.com',
            "geodir_website" => 'http://swp.com',
            "geodir_twitter" => 'http://twitter.com/swp',
            "geodir_facebook" => 'http://facebook.com/swp',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 6///

        break;
    case 7:

        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 7///
        $image_array[] = "$dummy_image_url/a15.jpg";
        $image_array[] = "$dummy_image_url/a16.jpg";
        $image_array[] = "$dummy_image_url/a17.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Museum Without Walls',
            "post_desc" => '<h3>The Experience </h3>
	
	Museum Without Walls: AUDIO is a multi-platform, interactive audio tour, designed to allow locals and visitors alike to experience Philadelphia extensive collection of public art and outdoor sculpture along the Benjamin Franklin Parkway and Kelly Drive. This innovative program invites passersby to stop, look, listen and see this city public art in a new way. Discover the untold histories of the 51 outdoor sculptures at 35 stops through these professionally produced three-minute interpretive audio segments. The many narratives have been spoken by more than 100 individuals, all with personal connections to the pieces of art.
	
	Works in Museum Without Walls: AUDIO include the sculpture Jesus Breaking Bread, which is located in front of the Cathedral Basilica of Saints Peter and Paul at 18th and Race Streets. The sculpture&acute;s audio program features the voices of three people who are each intimately, yet distinctly, connected to the piece. Listeners can hear Martha Erlebacher, the wife of the now-deceased sculptor and an artist herself, recall the personal challenge Walter Erlebacher set to humanize the figure. Monsignor John Miller, who oversaw the commission of the sculpture for the Archdiocese of Philadelphia, discusses the artist confrontation with historic interpretation, and Sister Mary Scullion, who runs the renowned program for the homeless in Philadelphia, Project H.O.M.E., and who also attended the sculpture dedication as a student, talks about the importance of placing the figure outside of the church.
	
	In the audio program for the sculpture Iroquois, listeners will hear a first-person account from Mark di Suvero, the artist himself, who discusses the abstract sculpture and its open shapes that invite public interaction and viewing from multiple angles. I think that in order to experience [Iroquois] … you have to walk in through the piece, you have to have it all the way around you and at that moment, you can feel what that sculpture can do, says di Suvero. Lowell McKegney, di Suvero construction manager and longtime friend, compares the sculpture to music and encourages listeners to appreciate it in the same way.
	<h3>History </h3>
	
	Philadelphia has more outdoor sculpture than any other American city, yet this extensive collection often goes unnoticed. This program is intended to reveal the distinct stories behind each of these works, that have become visual white noise for so many of the city residents and visitors. ',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('Museum'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 10.30 a.m., Sunday 10 am to 7 pm',
            "geodir_contact" => '(222) 999-9999',
            "geodir_email" => 'info@mwwalls.com',
            "geodir_website" => 'http://museumwithoutwallsaudio.org/',
            "geodir_twitter" => 'http://twitter.com/mwwalls',
            "geodir_facebook" => 'http://facebook.com/mwwalls',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 7///

        break;
    case 8:

        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 8///
        $image_array[] = "$dummy_image_url/a18.jpg";
        $image_array[] = "$dummy_image_url/a10.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Audacious Freedom',
            "post_desc" => 'Audacious Freedom, the major, new exhibit at the African American Museum in Philadelphia , explores the lives of people of African descent living in Philadelphia between 1776 and 1876.
	
	Discover how African Americans in Philadelphia lived and worked while helping to shape the young nation in its formative stages.
	
	Exhibit themes include entrepreneurship, environment, education, religion and family traditions of the African American population, played out through interactive displays, video projections and vivid photography.
	
	The groundbreaking exhibit allows visitors to “walk the streets” of Historic Philadelphia using a large-scale map. Young children can join the action with Children&acute;s Corner, which highlights the daily lives of children during that period.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('Tag1'),
            "geodir_video" => '',
            "geodir_timing" => 'Open today until 11.30 a.m., Sunday 1 pm to 7 pm',
            "geodir_contact" => '(777) 777-7777',
            "geodir_email" => 'info@aampmuseum.com',
            "geodir_website" => 'http://www.aampmuseum.org/',
            "geodir_twitter" => 'http://twitter.com/aampmuseum',
            "geodir_facebook" => 'http://facebook.com/aampmuseum',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 8///


        break;
    case 9:
        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 9///
        $image_array[] = "$dummy_image_url/a19.jpg";
        $image_array[] = "$dummy_image_url/a20.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'The Liberty Bell Center',
            "post_desc" => '<h3>The Experience </h3>
	
	The Liberty Bell has a new home, and it is as powerful and dramatic as the Bell itself. Throughout the expansive, light-filled Center, larger-than-life historic documents and graphic images explore the facts and the myths surrounding the Bell.
	
	X-rays give an insider&acute;s view, literally, of the Bell&acute;s crack and inner-workings. In quiet alcoves, a short History Channel film, available in English and eight other languages, traces how abolitionists, suffragists and other groups adopted the Bell as its symbol of freedom.
	
	Other exhibits show how the Bell&acute;s image was used on everything from ice cream molds to wind chimes. Keep your camera handy. Soaring glass walls offer dramatic and powerful views of both the Liberty Bell and Independence Hall, just a few steps away.
	<h3>History</h3>
	
	The bell now called the Liberty Bell was cast in the Whitechapel Foundry in the East End of London and sent to the building currently known as Independence Hall, then the Pennsylvania State House, in 1753.
	
	It was an impressive looking object, 12 feet in circumference around the lip with a 44-pound clapper. Inscribed at the top was part of a Biblical verse from Leviticus, “Proclaim Liberty throughout all the Land unto all the Inhabitants thereof.”
	
	Unfortunately, the clapper cracked the bell on its first use. A couple of local artisans, John Pass and John Stow, recast the bell twice, once adding more copper to make it less brittle and then adding silver to sweeten its tone. No one was quite satisfied, but it was put in the tower of the State House anyway.
	<h3>Fast Facts </h3>
	
	The Liberty Bell is composed of approximately 70 percent copper, 25 percent tin and traces of lead, zinc, arsenic, gold and silver.
	
	The Bell is suspended from what is believed to be its original yoke, made of American elm.
	
	The Liberty Bell weighs 2,080 pounds. The yoke weighs about 100 pounds.',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions', 'Feature')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'The center is open year round, 9 a.m. – 5 p.m., with extended hours in the summer.',
            "geodir_contact" => '(777) 666-6666',
            "geodir_email" => 'info@nps.com',
            "geodir_website" => 'http://www.nps.gov/inde',
            "geodir_twitter" => 'http://twitter.com/nps',
            "geodir_facebook" => 'http://facebook.com/nps',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 9///
        break;
    case 10:


        $image_array = array();
        $post_meta = array();

        /// Attractions ////post start 10///
        $image_array[] = "$dummy_image_url/a19.jpg";
        $image_array[] = "$dummy_image_url/a20.jpg";
        $image_array[] = "$dummy_image_url/a3.jpg";
        $image_array[] = "$dummy_image_url/a4.jpg";
        $image_array[] = "$dummy_image_url/a5.jpg";
        $image_array[] = "$dummy_image_url/a2.jpg";
        $image_array[] = "$dummy_image_url/a7.jpg";
        $image_array[] = "$dummy_image_url/a8.jpg";
        $image_array[] = "$dummy_image_url/a6.jpg";
        $image_array[] = "$dummy_image_url/a1.jpg";
        $image_array[] = "$dummy_image_url/a9.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Rittenhouse Square',
            "post_desc" => '
	
	Unlike the other squares, the early Southwest Square was never used as a burial ground, although it offered pasturage for local livestock and a convenient dumping spot for “night soil”.
	<h3> History </h3>
	
	By the late 1700s the square was surrounded by brickyards as the area&acute;s clay terrain was better suited for kilns than crops. In 1825 the square was renamed in honor of Philadelphian David Rittenhouse, the brilliant astronomer, instrument maker and patriotic leader of the Revolutionary era.
	
	A building boom began by the 1850s, and in the second half of the 19th century the Rittenhouse Square neighborhood became the most fashionable residential section of the city, the home of Philadelphia&acute;s “Victorian aristocracy.” Some mansions from that period still survive on the streets facing the square, although most of the grand homes gave way to apartment buildings after 1913.
	
	In 1816, local residents loaned funds to the city to buy a fence to enclose Rittenhouse Square. In the decade before the Civil War, the Square boasted not only trees and walkways, but also fountains donated by local benefactors – prematurely, it turned out, for the fountains created so much mud that City Council ordered them removed. The square&acute;s present layout dates from 1913, when the newly formed Rittenhouse Square Improvement Association helped fund a redesign by Paul Philippe Cret, a French-born architect who contributed to the design of the Benjamin Franklin Parkway and the Rodin Museum. Although some changes have been made since then, the square still reflects Cret&acute;s original plan.
	
	<h3>Layout </h3>
	
	The main walkways are diagonal, beginning at the corners and meeting at a central oval. The plaza, which contains a large planter bed and a reflecting pool, is surrounded by a balustrade and ringed by a circular walk. Classical urns, many bearing relief figures of ancient Greeks, rest on pedestals at the entrances and elsewhere throughout the square. Ornamental lampposts contribute to an air of old-fashioned gentility. A low fence surrounds the square, and balustrades adorn the corner entrances. Oaks, maples, locusts, plane trees, and others stand within and around the enclosure, and the flowerbeds and blooming shrubs add a splash of color in season.
	
	Rittenhouse Square is the site of annual flower markets and outdoor art exhibitions. More than any of the other squares, it also functions as a neighborhood park. Office workers eat their lunches on the benches; parents bring children to play; and many people stroll through to admire the plants, sculptures, or the fat and saucy squirrels.
	
	<h3>Public Art </h3>
	
	Like Logan Square, you can see several of the city&acute;s best-loved outdoor sculptures in Rittenhouse Square. The dramatic Lion Crushing a Serpent by the French Romantic sculptor Antoine-Louis Barye is in the central plaza. Originally created in 1832, the work is Barye&acute;s allegory of the French Revolution of 1830, symbolizing the power of good (the lion) conquering evil (the serpent). This bronze cast was made about 1890.
	
	At the other end of the central plaza, within the reflecting pool, is Paul Manship&acute;s Duck Girl of 1911, a lyrical bronze of a young girl carrying a duck under one arm – an early work by the same sculptor who designed the Aero Memorial for Logan Square. A favorite of the children is Albert Laessle&acute;s Billy, a two-foot-high bronze billy goat in a small plaza halfway down the southwest walk. Billy&acute;s head, horns, and spine have been worn to a shiny gold color by countless small admirers.
	
	In a similar plaza in the northeast walkway stands the Evelyn Taylor Price Memorial Sundial, a sculpture of two cheerful, naked children who hold aloft a sundial in the form of a giant sunflower head. Created by Philadelphia artist Beatrice Fenton, the sundial memorializes a woman who served as the president of the Rittenhouse Square Improvement Association and Rittenhouse Square Flower Association. In the flower bed between the sundial and the central plaza is Cornelia Van A. Chapin&acute;s Giant Frog, a large and sleek granite amphibian. Continuing the animal theme, two small stone dogs, added in 1988, perch on the balustrades at the southwest corner entrance.
	
	<h3>At Night </h3>
	
	Once predominantly a daytime destination, Rittenhouse Square is now a popular nightspot as well, with a string of restaurants - including Rouge, Devon, Parc and Barclay Prime - that have sprouted up along the east side of the park on 18th Street.
	
	So these days, you can take in the serenity of the natural landscape from a park bench in the sunshine and then sip cocktails under the stars at one of many candlelit outdoor tables.
	
	Meanwhile, several more restaurants, bars and clubs have opened along the surrounding blocks in recent years, like Parc, Tria, Continental Midtown, Alfa, Walnut Room, and Twenty Manning just to name a few.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Attractions')),
            "post_tags" => array('Museum'),
            "geodir_video" => '',
            "geodir_timing" => 'The center is open year round, 9 a.m. – 5 p.m., with extended hours in the summer.',
            "geodir_contact" => '(777) 666-6666',
            "geodir_email" => 'info@fairmountpark.com',
            "geodir_website" => 'http://www.fairmountpark.org/rittenhousesquare.asp',
            "geodir_twitter" => 'http://twitter.com/fairmountpark',
            "geodir_facebook" => 'http://facebook.com/fairmountpark',
            "post_dummy" => '1'
        );

        ////post end///
        /// Attractions ////post start 10///
        break;
    case 11:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 1///
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";
        $image_array[] = "$dummy_image_url/hotels3.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels5.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Loews Philadelphia Hotel',
            "post_desc" => '
	
	<h3>OVERVIEW </h3>
	
	One of the most important architectural works of the 20th Century, the PSFS (Philadelphia Savings Fund Society) Building has been converted into the new 585-room Loews Philadelphia Hotel. Designed by George Howe and William Lescaze, the building was erected in 1932 and was the first international style, modernist high-rise building.
	
	Today, the building retains period details, such as Cartier clocks, bank vault doors and polished granite, as well as modern amenities such as a full service health spa, business center, spinning room, lap pool and over 40,000 square feet of multi-purpose space, including three ballrooms.
	
	<h3>THE HOTEL </h3>
	
	Loews Hotels is proud to have restored the landmark PSFS Building to its original grandeur, while transforming it into a hotel that people from all over the world can experience and enjoy.
	
	The hotel takes full advantage of the building&acute;s historical features. The three-story former banking room has been preserved as Millennium Hall, a dramatic banquet space. The historic, rooftop boardroom has been converted to a spectacular setting for catered events.
	
	The building retains period details, such as Cartier clocks, bank vault doors and polished granite, as well as modern amenities such as a full service health spa, business center, spinning room, lap pool and over 40,000 square feet of multi-purpose space, including three ballrooms.
	
	Feel the comforts of home in accommodations that perfectly balance the contemporary with the elegant. Where every detail from the lofty ten-foot ceilings to the miles of spectacular views is designed to serve one purpose – yours. Whether you&acute;re working hard or playing hard, you can always rest easy.
	
	The Loews is perfect for families. The hotel offers special kid-friendly programs and features dedicated to the principle: “the family that stays together plays together” (and that includes four-legged family members too).
	
	Learn more about Loews Signature Family Travel Benefits.
	DINING AT THE HOTEL
	
	<h3>Solefood </h3>
	
	SoleFood is a fusion of seafood and cutting edge culinary expertise, offering seafood inspired dishes at breakfast, lunch and dinner. Guests can enjoy a cozy table for two or make new friends at one of the communal tables featuring a center display of river rocks and candles.
	
	In order to create a memorable culinary experience in an upscale, hip environment which mixes eclectic cool with classic style, Solefood Restaurant continues to create exciting food and drinks that are mixed with just the right amount of attitude. SoleFood has received local and regional accolades from the media including 2008 Best of Philadelphia Award, Philadelphia City Paper Best Bar and Best Seafood restaurant.
	
	SoleFood features hard to find wines, served by the glass, bottle and half bottle for when a bottle is too much and a glass is too little.
	
	Special Prix Fixe Dinner Offer
	
	SoleFood is offering a special “Diversify your Palate” prix-fixe dinner menu through 2010. For $29, you get to choose an entree and two “investments,” which can be an appetizer, a glass of wine, a cocktail, a dessert or a draft beer.
	
	To make a reservation at SoleFood restaurant please call (215) 231-7300 or visit opentable.com
	
	<h3>Hours: </h3>
	
	Breakfast: Daily, 6:30 am – 11:00 am
	Brunch: Saturday & Sunday,11:30 am – 2:00 pm
	Lunch: Monday – Friday, 11:30 am – 2:00 pm
	Dinner: Daily, 5:30 pm – 10:00 pm
	
	<h3>SoleFood Lounge & Happy Hour </h3>
	
	SoleFood Lounge provides one of the best happy hour options in the city. Gather with your friends and take advantage of some great specials, including hors d’oeuvres, wines by the glass, draft beer, and a wide selection of martinis from 5 to 7 p.m. daily. The lounge is the perfect place to meet up with old friends and make new ones.
	
	SoleFood Lounge has earned recognition for its creative bar menu that includes a wide array of signature drinks and one of the best martinis in Philly.
	
	Solefood Lounge Hours: Daily, 11:30 am – 2:00 am
	Lounge Menu is offered daily: 11:00 am – 12:00 am
	
	Solstice and SoleFood Special Events & Private Parties
	
	Solstice and SoleFood provide fabulous settings for receptions, private parties and meetings. Solstice Private Dining Room is a great place to host cocktails receptions, dinners and meetings.
	
	SoleFood is available for private parties and events. The main dining room can accommodate up to 85 people; each of the two communal tables seats 16; The Bar and Lounge at SoleFood with its luxe decor and inviting banquettes and white leather chairs can accommodate 200 for cocktails.
	
	Menus can be customized to meet your needs, including family-style.
	
	<h3>Starbucks Morning Coffee Bar </h3>
	
	Daily, 6:30 am – 10:30 am
	
	SoleFood Restaurant is proud to be serving Starbucks. Come in and enjoy a fresh cup of coffee during your morning rush. The Coffee Bar also offer small breakfast items for your enjoyment.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Feature')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily, 6:30 am – 12:00 pm',
            "geodir_contact" => '(111) 111-0000',
            "geodir_email" => 'info@loewshotels.com',
            "geodir_website" => 'http://www.loewshotels.com/en/hotels/philadelphia-hotel/overview.aspx',
            "geodir_twitter" => 'http://twitter.com/loewshotels',
            "geodir_facebook" => 'http://facebook.com/loewshotels',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 1///
        break;
    case 12:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 2///
        $image_array[] = "$dummy_image_url/hotels5.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";
        $image_array[] = "$dummy_image_url/hotels3.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Embassy Suites Philadelphia',
            "post_desc" => '
	The newly renovated Embassy Suites Philadelphia – Center City hotel is conveniently situated in the heart of downtown Philadelphia, Pennsylvania and Philadelphia&acute;s Center City business district. This hotel in Philadelphia is located only eight miles from Philadelphia International Airport and just minutes from top Philadelphia attractions, including:
	
	Philadelphia Museum of Art
	Philadelphia City Hall
	Philadelphia Zoo
	Franklin Institute
	Historic landmarks such as the Liberty Bell & Independence Hall
	Pennsylvania Convention Center
	University of Pennsylvania
	Upon entering these suites at the Embassy Suites Philadelphia – Center City hotel, the spaciousness of the living room gives way to the warmth of each of the appointments. All of the newly renovated 288 two-room accommodations feature an entry foyer, queen-size sofa bed, and a range of in-suite amenities, including: well-lit work area, high-speed Internet access, dining area with balcony, kitchen area with microwave, coffee maker, refrigerator, and wet bar.
	
	Guests of the Embassy Suites Philadelphia – Center City hotel in downtown Philadelphia are also welcome to enjoy a range of hotel-wide amenities and services, including: fitness center, hotel business center, and meeting rooms.
	
	A delicious, complimentary cooked-to-order breakfast is offered each morning, and a hotel Manager&acute;s Reception every night – featuring complimentary refreshments and great company.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily, 10:30 am – 10 pm',
            "geodir_contact" => '(111) 111-0000',
            "geodir_email" => 'info@embassysuites1.com',
            "geodir_website" => 'http://embassysuites1.hilton.com/en_US/es/hotel/PHLDTES-Embassy-Suites-Philadelphia-Center-City-Pennsylvania/index.do',
            "geodir_twitter" => 'http://twitter.com/embassysuites1',
            "geodir_facebook" => 'http://facebook.com/embassysuites1',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 2///

        break;
    case 13:

        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 3///
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels11.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Doubletree Hotel Philadelphia',
            "post_desc" => '
	With 434 rooms, the Doubletree Hotel is a great option for your upcoming stay in Philadelphia.
	
	<h3>Location </h3>
	
	Located right on the Avenue of the Arts at Broad and Locust Streets, this high rise occupies one of the city&acute;s most ideal locations. The Kimmel Center for the Performing Arts, the Academy of Music, and the Merriam and Wilma Theaters are all within a block.
	
	To the west you have great shopping and dining in Rittenhouse Square. To the east are Philadelphia&acute;s famous historic attractions, South Street, Washington Square and Old City.
	
	<h3>Guest Rooms </h3>
	
	Spacious and well-appointed guest rooms offer paroramic views of the city, traditional décor, generous work areas and high-speed internet access. Other amenities include a restaurant, lounge and a health club with an indoor pool.
	
	The Doubletree&acute;s spacious guest rooms are decorated in a warm contemporary style, which includes a Herman Miller ergonomic chair at an oversized desk featuring task lighting and easy-access power source. Work with ease and efficiency from your room, utilizing two dual-line telephones with data port, speakerphone, and private voicemail. High-speed internet access ensures productivity by providing you with quick and convenient access to email and the Internet.
	
	All rooms feature the popular Sweet Dreams by Doubletree bedding, one king or two queens.
	
	<h3>Suites</h3>
	
	If you prefer additional space, try a suite. The Junior Suite is an oversized guest room with a seating area separated from the sleeping space by a half wall. For more privacy, reserve an elegant two-room suite, which offers twice the square footage of a standard guest room, with a door to separate bedroom and sitting areas.
	
	The suites at the Doubletree are perfect for business stays when you need convenient space to conduct a small meeting or the ability to spread out and get the job done. Guest suite living areas also feature a sleeper sofa, great for vacationing families. And closets in both areas ensure you&acute;ll have plenty of wardrobe and hanging space for relocation or extended stays.
	
	<h3>The Standing O Bistro and Bar </h3>
	
	The Doubletree Hotel Philadelphia boasts a great option for enjoying a bite before heading out into the city: The Standing O Bistro.
	
	Stop in the restaurant - which serves lunch and dinner daily - for a drink and some light fare. With its location right on Broad Street, you&acute;re close to everything you could ever want in a night on the town.
	',

            "post_images" => $image_array,

            "post_category" => array($post_type.'category' => array('Hotels')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily, 10:30 am – 10 pm',
            "geodir_contact" => '(111) 111-0000',
            "geodir_email" => 'info@doubletree1.com',
            "geodir_website" => 'http://doubletree1.hilton.com/en_US/dt/hotel/PHLBLDT-Doubletree-Hotel-Philadelphia-Pennsylvania/index.do',
            "geodir_twitter" => 'http://twitter.com/doubletree1',
            "geodir_facebook" => 'http://facebook.com/doubletree1',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 3///

        break;
    case 14:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 4///
        $image_array[] = "$dummy_image_url/hotels15.jpg";
        $image_array[] = "$dummy_image_url/hotels16.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Philadelphia Marriott Downtown',
            "post_desc" => '
	Get ready to stay and play at the new aloft Philadelphia Airport!
	
	This incredibly modern hotel is located just five minutes from Philadelphia International Airport, offering a great convenience to travelers looking for fresh and fun accommodations.
	<h3>Guest Rooms </h3>
	
	The hotel&acute;s spacious guest rooms make you feel right at home with extra large windows, iPod docking stations, high-speed wireless internet, 42” LCD televisions and king- or queen-sized beds. Like the rest of the hotel, the guest rooms feature ultra-modern touches and a fun, energetic design.
	<h3>Things to Do </h3>
	
	Want to socialize? That&acute;s easy at aloft - just step into the re:mix lobby to relax and chat, work on your laptop or shoot a few games of pool. Ready for cocktail hour? The w xyz bar has great drink specials and tasty bar fare. Time for a snack? The re:fuel shop offers self-serve bites like sandwiches, salads and fresh fruit.
	
	The Splash indoor pool and re:charge fitness center complete your overnight experience. And lucky for you - self check-in kiosks allow you to print out your next flight&acute;s boarding pass! Talk about convenient.
	<h3> Re:Fuel </h3>
	
	Just off the plane and craving something to nibble? Thanks to Aloft Philadelphia Airport&acute;s innovative eating options, you don&acute;t have to make do with bland in-flight meals or unhealthy airport fare. Enticing edibles are here, from sweet treats to healthy eats and more.
	
	There is something to please your palate at any hour. Help yourself at the 24-7 re:fuel by Aloft(SM) for a quick bite whenever hunger strikes. Or mix and mingle with a drink and snack at the w xyz(SM) bar.
	<h3>Fun </h3>
	
	For the traveler open to possibilities, Aloft Philadelphia Airport is a fresh, fun, forward-thinking alternative. Breeze into a hotel that offers more than a comfy bed and a friendly smile, and enjoy a whole new travel experience. Energy flows and personalities mingle in a setting that combines urban-influenced design, accessible technology, and a social scene that&acute;s always abuzz.
	
	Energizing public spaces draw you from your room to socialize, or just enjoy the hum of activity as you do your own thing. Sip a drink, read the paper, or work on your laptop in the re:mix(SM) lounge or w xyz(SM) bar, where lighting and music change throughout the day to set the perfect mood.
	
	The hotel&acute;s open flow of features and help-yourself services inspire you to step outside the one-size-fits-all travel routine. Customize your stay and celebrate your style in a place where anything can happen.
	
	Aahh…breathe deep at Aloft. This hotel is smoke-free.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Feature')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => '24 Hours',
            "geodir_contact" => '(123) 111-2222',
            "geodir_email" => 'info@marriott.com',
            "geodir_website" => 'http://www.marriott.com/hotels/travel/phldt-philadelphia-marriott-downtown/',
            "geodir_twitter" => 'http://twitter.com/marriott',
            "geodir_facebook" => 'http://facebook.com/marriott',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 4///
        break;
    case 15:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 5///
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels16.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Hilton Inn at Penn',
            "post_desc" => '
	Located in the heart of Penn&acute;s campus in the beautiful University City neighborhood of Philadelphia, The Hilton Inn at Penn is a great choice for accommodations during your upcoming visit to Philadelphia.
	
	The location puts you right in the middle of the prestigious University of Pennsylvania and its many nearby educational, medical and corporate centers. And Center City Philadelphia is only a short cab ride away. So if you want to get out and explore the city, you are set.
	
	Take in a show at the Annenberg Theater; visit one of the many museums the city has to offer; dine at area restaurants that boast a range of cuisines, from Thai to Indian to Japanese to classic comfort cuisine; peerless boutique shopping along Walnut Street from University City to Old City.
	
	The beautifully appointed guest rooms are equipped for the technologically sophisticated and include two dual-line phones with voice mail, data ports and high speed and wireless Internet access. Each room also offers WEBTV, plush terry cloth robes and luxurious bath amenities provide a touch of indulgence. Additionally, a refreshment center is now located in each guestroom with snacks and refreshments along with an in room safe for valuables and laptops.
	
	The Hilton Inn at Penn is a recipient of the AAA Four Diamond rating. There is also a 24-hour fitness center with a full range of cardiovascular and weight training equipment.
	<h3>Penne Restaurant and Wine Bar </h3>
	
	One of University City&acute;s finest Italian restaurants is Penne at the Inn at Penn. Featuring innovative, regional Italian cuisine and hand-made pasta made fresh daily, Penne is a great choice for lunch or dinner.
	
	The pasta is handmade right in front of you and then dished up along side delectable entrées such as grilled veal tenderloin and honey glazed sea scallops. And the wine bar offers more than 30 varieties by the glass and more than 100 by the bottle.  
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Food Nightlife')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 11 am to 11 pm',
            "geodir_contact" => '(888) 888-8888',
            "geodir_email" => 'info@theinnatpenn.com',
            "geodir_website" => 'http://www.theinnatpenn.com/',
            "geodir_twitter" => 'http://twitter.com/theinnatpenn',
            "geodir_facebook" => 'http://facebook.com/theinnatpenn',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 5///
        break;
    case 16:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 6///
        $image_array[] = "$dummy_image_url/hotels17.jpg";
        $image_array[] = "$dummy_image_url/hotels18.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Courtyard Philadelphia Downtown',
            "post_desc" => '
	<h3>Overview </h3>
	
	The Philadelphia Downtown Courtyard opened it&acute;s doors after a grand $75 million restoration, recapturing the grandeur of its 1926 origins while incorporating state of the art systems throughout.
	
	Designed by renowned architect Phillip H. Johnson, the 18-story, 498-room hotel is listed on the “National Register of Historic Places” and stands as a charming testament to time with elegant bronze work, plaster detailing, striking marble finishes and unique architectural details.
	
	Catering to both leisure and business travelers, the historic full-service hotel is ideally located in the “Heart of Center City” across from City Hall, one block to the Pennsylvania Convention Center and within walking distance of the Financial & Historic Districts, Avenue of the Arts and some of the finest restaurants and shopping the city has to offer.
	
	
	<h3>Guestroom Features </h3>
	
	The hotel features stylishly appointed oversized guestrooms with 11ft-high ceilings, a 42” LCD TV, Refrigerator, I-Pod Docking Station Alarm Clock, complimentary Wireless or Wired internet access, and Marriott&acute;s plush Revive bedding package.
	
	In addition, the property offers 61 suites for those who like additional room and added comfort as well as 50 rooms which include a striking Wall Mural of Philadelphia&acute;s Independence Hall.
	
	<h3>Hotel Services </h3>
	
	As the largest Courtyard by Marriott in the United States, this hotel is truly unique offering all the full-service features and amenities you would expect from a premier hotel.
	
	The Annex Grille & Lounge serves American Cuisine for breakfast, lunch & dinner as well as is a great location for a refreshing beverage or cocktail. Or you can dine in the convenience of your guestroom with the hotel&acute;s evening Room Service.
	
	The hotel&acute;s Lobby Concierge Services and Bellman are ready to assist you with any request as well as information on all Philadelphia has to offer.
	
	Stay in shape in the hotel&acute;s State of the Art Fitness Center, and then unwind in the Indoor Pool and Whirlpool. If you are looking for a quiet place to getaway, visit our Philip H. Johnson Library where you can read all about Historic Philadelphia.
	
	<h3>Meetings & Events </h3>
	
	Recently featured on WE TV&acute;s “My Fair Wedding”, the Courtyard Marriott Philadelphia is one of the city&acute;s leading venues for corporate and social affairs with over 10,000 sq ft of flexible meeting space, including two Grand Ballrooms each with over 3,000 square feet accommodating up to 250 people. In addition, the hotel has a total of 11 meeting rooms making it an ideal home for all occasions. The hotel boasts an experienced full-service Event and Culinary Teams, ready to take care of all the details and ensure your event is not only a success, but a lasting memory. 
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Food Nightlife')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 11 am to 11 pm',
            "geodir_contact" => '(888) 888-8888',
            "geodir_email" => 'info@theinnatpenn.com',
            "geodir_website" => 'http://www.theinnatpenn.com/',
            "geodir_twitter" => 'http://twitter.com/theinnatpenn',
            "geodir_facebook" => 'http://facebook.com/theinnatpenn',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 6///

        break;
    case 17:

        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 7///
        $image_array[] = "$dummy_image_url/hotels11.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Four Seasons Philadelphia',
            "post_desc" => '
	<h3>Overview </h3>
	
	The Philadelphia Downtown Courtyard opened it&acute;s doors after a grand $75 million restoration, recapturing the grandeur of its 1926 origins while incorporating state of the art systems throughout.
	
	Designed by renowned architect Phillip H. Johnson, the 18-story, 498-room hotel is listed on the “National Register of Historic Places” and stands as a charming testament to time with elegant bronze work, plaster detailing, striking marble finishes and unique architectural details.
	
	Catering to both leisure and business travelers, the historic full-service hotel is ideally located in the “Heart of Center City” across from City Hall, one block to the Pennsylvania Convention Center and within walking distance of the Financial & Historic Districts, Avenue of the Arts and some of the finest restaurants and shopping the city has to offer.
	
	
	<h3>Guestroom Features </h3>
	
	The hotel features stylishly appointed oversized guestrooms with 11ft-high ceilings, a 42” LCD TV, Refrigerator, I-Pod Docking Station Alarm Clock, complimentary Wireless or Wired internet access, and Marriott&acute;s plush Revive bedding package.
	
	In addition, the property offers 61 suites for those who like additional room and added comfort as well as 50 rooms which include a striking Wall Mural of Philadelphia&acute;s Independence Hall.
	
	<h3>Hotel Services </h3>
	
	As the largest Courtyard by Marriott in the United States, this hotel is truly unique offering all the full-service features and amenities you would expect from a premier hotel.
	
	The Annex Grille & Lounge serves American Cuisine for breakfast, lunch & dinner as well as is a great location for a refreshing beverage or cocktail. Or you can dine in the convenience of your guestroom with the hotel&acute;s evening Room Service.
	
	The hotel&acute;s Lobby Concierge Services and Bellman are ready to assist you with any request as well as information on all Philadelphia has to offer.
	
	Stay in shape in the hotel&acute;s State of the Art Fitness Center, and then unwind in the Indoor Pool and Whirlpool. If you are looking for a quiet place to getaway, visit our Philip H. Johnson Library where you can read all about Historic Philadelphia.
	
	<h3>Meetings & Events </h3>
	
	Recently featured on WE TV&acute;s “My Fair Wedding”, the Courtyard Marriott Philadelphia is one of the city&acute;s leading venues for corporate and social affairs with over 10,000 sq ft of flexible meeting space, including two Grand Ballrooms each with over 3,000 square feet accommodating up to 250 people. In addition, the hotel has a total of 11 meeting rooms making it an ideal home for all occasions. The hotel boasts an experienced full-service Event and Culinary Teams, ready to take care of all the details and ensure your event is not only a success, but a lasting memory. 
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Food Nightlife')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 11 am to 11 pm',
            "geodir_contact" => '(143) 888-8888',
            "geodir_email" => 'info@fourseasons.com',
            "geodir_website" => 'http://www.fourseasons.com/philadelphia/',
            "geodir_twitter" => 'http://twitter.com/fourseasons',
            "geodir_facebook" => 'http://facebook.com/fourseasons',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 7///
        break;
    case 18:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 8///
        $image_array[] = "$dummy_image_url/hotels11.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Alexander Inn',
            "post_desc" => '
	The Alexander Inn is one of Philadelphia&acute;s most popular and reasonably priced small hotels.
	
	Conveniently located in the heart of the Washington Square West neighborhood in Center City Philadelphia, the Alexander Inn is a great place to base your stay in Philadelphia.
	
	The décor of the hotel&acute;s 48 designer rooms is inspired by the style of the grand cruise ships of the 1930s, which is reflected in the rooms’ hand selected furnishings, fabrics and accessories. Beautiful artwork adorns the walls of each rooms, which all include private baths with plush towels.
	
	Rooms are also fitted with DirecTV (including many complimentary channels like CNN, ESPN, eight movie channels, etc.) and telephones with modem ports and direct dial. You will also have access to the hotel&acute;s free 24-hour fitness and e-mail centers.  
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 11 am to 11 pm',
            "geodir_contact" => '(143) 888-8888',
            "geodir_email" => 'info@alexanderinn.com',
            "geodir_website" => 'http://www.alexanderinn.com/',
            "geodir_twitter" => 'http://twitter.com/alexanderinn',
            "geodir_facebook" => 'http://facebook.com/alexanderinn',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 8///
        break;
    case 19:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 9///
        $image_array[] = "$dummy_image_url/hotels5.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Best Western Center City Hotel',
            "post_desc" => '
	The Alexander Inn is one of Philadelphia&acute;s most popular and reasonably priced small hotels.
	
	Conveniently located in the heart of the Washington Square West neighborhood in Center City Philadelphia, the Alexander Inn is a great place to base your stay in Philadelphia.
	
	The décor of the hotel&acute;s 48 designer rooms is inspired by the style of the grand cruise ships of the 1930s, which is reflected in the rooms’ hand selected furnishings, fabrics and accessories. Beautiful artwork adorns the walls of each rooms, which all include private baths with plush towels.
	
	Rooms are also fitted with DirecTV (including many complimentary channels like CNN, ESPN, eight movie channels, etc.) and telephones with modem ports and direct dial. You will also have access to the hotel&acute;s free 24-hour fitness and e-mail centers.  
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Food Nightlife')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 10 am to 11 pm',
            "geodir_contact" => '(243) 222-12344',
            "geodir_email" => 'info@alexanderinn.com',
            "geodir_website" => 'http://book.bestwestern.com/bestwestern/productInfo.do?propertyCode=39087',
            "geodir_twitter" => 'http://twitter.com/bestwestern',
            "geodir_facebook" => 'http://facebook.com/bestwestern',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 9///
        break;
    case 20:


        $image_array = array();
        $post_meta = array();

        /// Hotels ////post start 10///
        $image_array[] = "$dummy_image_url/hotels7.jpg";
        $image_array[] = "$dummy_image_url/hotels10.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels4.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels6.jpg";
        $image_array[] = "$dummy_image_url/hotels12.jpg";
        $image_array[] = "$dummy_image_url/hotels8.jpg";
        $image_array[] = "$dummy_image_url/hotels9.jpg";
        $image_array[] = "$dummy_image_url/hotels1.jpg";
        $image_array[] = "$dummy_image_url/hotels2.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Chestnut Hill Hotel',
            "post_desc" => '
	The Chestnut Hill Hotel is located in the historic community of Chestnut Hill, approximately nine miles northwest from Center City Philadelphia. Although Chestnut Hill is close to Center City by today&acute;s standards, it was originally a distant “suburb” on the outskirts of the Philadelphia countryside.
	
	Today, it is one of the region&acute;s most charming neighborhoods. Tree-lined streets and grand estates surround its main street, Germantown Avenue, where you can stroll and shop at more than 200 specialty shops and restaurants, along with trendy salons and other modern boutiques.
	
	The Chestnut Hill Hotel fits perfectly in this setting - the hotel&acute;s 36 rooms and suites, decorated in an 18th-century style, hold the hotel to its boutique roots. It&acute;s a perfect place at which to enjoy a romantic getaway in Philadelphia. 
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Hotels', 'Feature')),
            "post_tags" => array(''),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 10 am to 11 pm',
            "geodir_contact" => '(243) 222-12344',
            "geodir_email" => 'info@chestnuthillhotel.com',
            "geodir_website" => 'http://www.chestnuthillhotel.com/',
            "geodir_twitter" => 'http://twitter.com/chestnuthillhotel',
            "geodir_facebook" => 'http://facebook.com/chestnuthillhotel',
            "post_dummy" => '1'
        );

        ////post end///
        /// Hotels ////post start 10///

        break;
    case 21:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 1//
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Village Whiskey',
            "post_desc" => '
	
	
	Located in a Rittenhouse Square space evoking the free-wheeling spirit of a speakeasy, Village Whiskey is prolific Chef Jose Garces’ intimate, 30-seat tribute to the time-honored liquor.
	
	In fact, Village Whiskey features a veritable library of 80-100 varieties of whiskey, bourbon, rye and scotch from Scotland, Canada, Ireland, United States and even Japan.
	
	Much as Village Whiskey could be a scene for toasting and roasting, it also comes from the culinary imagination of Jose Garces (of Amada, Tinto, Distrito and Chifa fame), meaning the food is no less than outstanding.
	<h3>Cuisine </h3>
	
	Village Whiskey&acute;s specialty from the kitchen is “bar snacks,” but that doesn&acute;t mean a bowl of cashews. Rather, it means deviled eggs, spicy popcorn shrimp, soft pretzels and an à la carte raw bar, all treated with the culinary care that made Jose Garces a finalist on The Next Iron Chef.
	
	Perhaps you seek something heartier. The lobster roll, raw bar selections and Kentucky fried quail are standouts, but you’d really ought to order the Whiskey King: a 10 oz patty of ground-to-order sustainable angus topped with maple bourbon glazed cipollini, Rogue blue cheese, applewood smoked bacon and foie gras. Bring your appetite.
	<h3>Cocktails </h3>
	
	Whiskey-based cocktails are divided into two categories: Prohibition (classic cocktails) and Repeal (more contemporary, modern takes). Meanwhile, the venerable Manhattan is a mainstay, mixed using house-made bitters.
	
	Prohibition cocktails include: Old Fashioned (Bottle in Bond Bourbon and house bitters); Aviation (Creme de Violette and gin); and Philadelphia Fish House Punch (dark rum, peach brandy and tea). Repeal cocktails include: APA (hops-infused vodka, ginger and egg white); De Riguer (rye, aperol, grapefruit and mint); and Horse With No Name (scotch, Stone Pine Liqueur and pineapple).
	<h3>Atmosphere </h3>
	
	The speakeasy atmosphere is accomplished through dim lighting, posters for various alcohols, a tin ceiling and antique mirrors. Black-and-white white tiled floors, marble topped tables and wooden drink rails add to the traditional bar decor.
	
	Behind the pewter bar, whiskies are proudly displayed like leather-bound books.
	
	During the warmer months, diners can sit at large, wooden tables placed along Sansom Street for whiskey alfresco.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Feature')),
            "post_tags" => array('Sample Tag1'),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 10 am to 11 pm',
            "geodir_contact" => '(243) 222-12344',
            "geodir_email" => 'info@villagewhiskey.com',
            "geodir_website" => 'http://www.villagewhiskey.com/',
            "geodir_twitter" => 'http://twitter.com/villagewhiskey',
            "geodir_facebook" => 'http://facebook.com/villagewhiskey',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 1///
        break;
    case 22:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 2//
        $image_array[] = "$dummy_image_url/restaurants4.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Zavino Pizzeria and Wine Bar',
            "post_desc" => '
	Zavino is a new pizzeria and wine bar located at the epicenter of the city&acute;s trendy Midtown Village neighborhood. The restaurant features a seasonal menu, classic cocktails, an approachable selection of wine and beer and some of the best late night menu offerings in the area.
	
	The restaurant&acute;s interior looks great - it has a simple, rustic feel with an original brick wall, large picture windows, a long bar and a large outdoor cafe coming this spring.
	
	And the menu is great too - it boasts affordable snacks ranging from pizza to pasta to charcuterie to satisfy diners’ hunger, and then cocktails, including Italy&acute;s venerable Negroni and Bellini, and an ever-evolving assortment of wine and beer offerings, to quench their thirst.
	
	Menu items vary seasonally, as is customary in Italy, and may include: House-Made Beef Ravioli with brown butter and sage; Roasted Red and Golden Beets with pistachios and goat cheese; Roasted Lamb with fried eggplant and mint; a delicious house-made gnocchi; and traditional Panzanella, a tomato and bread salad. There is also a nice selection of cheese and charcuterie available a la carte.
	
	<h3>The Pizza </h3>
	
	The gourmet pizzas are baked in a special wood-burning oven that reaches temperatures of up to 900 degrees. The pizzas are approximately 12 inches in diameter. And Chef Gonzalez describes the crust as neither too thin or too thick, but rather somewhere right between Neapolitan and Sicilian, “crunchy and tender, and just exactly right.”
	
	Three classic pizzas will be available year-round: Rosa, with tomato sauce and roasted garlic; Margherita, with tomato sauce and buffalo mozzarella, topped with fresh basil; and Polpettini, tomato sauce and provolone cheese with veal mini-meatballs.
	
	The specialty pizzas that are on the opening winter menu include: Philly, with bechamel, provolone, roasted onions and bresaola; Kennett, with bechamel, claudio&acute;s mozzarella, roasted onions with oyster, cremini and shitake mushrooms; Sopressata, with tomato sauce, claudio&acute;s mozzarella, sopressata olives, pickled red onion and pecorino; and Fratello, with bechamel, broccoli, roasted garlic and claudio&acute;s mozzarella.
	
	Pizzas vary in price from $8 to $12.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants')),
            "post_tags" => array('Sample Tag1'),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 10 am to 11 pm',
            "geodir_contact" => '(243) 222-12344',
            "geodir_email" => 'info@chestnuthillhotel.com',
            "geodir_website" => 'http://www.villagewhiskey.com/',
            "geodir_twitter" => 'http://twitter.com/villagewhiskey',
            "geodir_facebook" => 'http://facebook.com/villagewhiskey',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 2///

        break;
    case 23:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 3//
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants11.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Parc',
            "post_desc" => '
	If you love Paris in the springtime, Parc is a veritable grand cru.
	
	With Parc, famed restaurateur Stephen Starr brings a certain je ne sais quoi to Rittenhouse Square. Parc offers an authentic French bistro experience, fully equipped with a chic Parisian ambiance and gorgeous sidewalk seating overlooking the Square.
	<h3>Cuisine </h3>
	
	Parc menu encourages a joyful dining experience, where croissants, champagne and conversation are enjoyed in equal measure.
	
	Sample hors d’oeuvres include salade lyonnaise with warm bacon vinaigrette and poached egg, escargots served in their shells with hazelnut butter and a crispy duck confit with frisée salad and pickled chanterelles.
	
	Outstanding entrées include boeuf bourguignon with fresh buttered pasta and steak frites with peppercorn sauce. A variety of plats du jour are also offered, including a seafood-rich bouillabaisse on Fridays and a sumptuous coq au vin, perfect for Sunday night suppers.
	
	And what&acute;s an authentic French meal without wine? More than 160 expertly chosen varietals are offered by the bottle, with more than 20 available by the glass.
	<h3>See and Be Seen </h3>
	
	With seating for more than 75 at its sidewalk and window seating, Parc has instantly become one of the best places in Philadelphia for alfresco drinking and dining.
	
	The awning-covered seating wraps around the restaurant&acute;s two sides and overlooks Rittenhouse Square, one of Philadelphia&acute;s most popular public spaces.
	<h3>Atmosphere </h3>
	
	The aroma of freshly baked breads fills the air as one enters Parc&acute;s casual front room, which is clad in hand-laid Parisian tiles in shades of ecru and green.
	
	Red leather banquettes flanked by frosted glass offer subtle intimacy, while well-worn wooden chairs, reclaimed bistro tables and mahogany paneled walls give the room a sense of place.
	
	The more formal dining room provides a slightly more sophisticated experience while maintaining the energy and emotion of a bustling brasserie.
	
	To put it simply, Parc is nothing short of an authentic Parisian dining experience - right here in the heart of Rittenhouse Square.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants')),
            "post_tags" => array('Sample Tag1'),
            "geodir_video" => '',
            "geodir_timing" => 'Daily : 10 am to 12 pm',
            "geodir_contact" => '(143) 222-12344',
            "geodir_email" => 'info@parc-restaurant.com',
            "geodir_website" => 'http://www.parc-restaurant.com/',
            "geodir_twitter" => 'http://twitter.com/parc-restaurant',
            "geodir_facebook" => 'http://facebook.com/parc-restaurant',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 3///
        break;
    case 24:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 4//
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Percy Street Barbecue',
            "post_desc" => '
	Percy Street Barbecue sees the South Street debut of restaurateurs Steven Cook and Michael Solomonov (Zahav, Xochitl).
	
	Serving a straightforward selection of slowly smoked meats and homey side dishes alongside craft beers and tasty cocktails, Percy Street is an ideal venue for Chef Erin OShea much-lauded Southern cooking, and is on its way to become the city top spot for barbecue.
	
	Working with J&R smokers sourced from Texas, Chef O&acute;shea and her crack team of barbecue wizards headed down to Texas - tested no fewer than 20 beef briskets - as they perfected the ideal balance of salt, smoke and seasoning. Check out this video about their culinary field trip to the Lone Star State.
	
	<h3>The Eats </h3>
	
	That Brisket which is Percy Street&acute;s signature dish, served - as is the custom in Texas - by the half pound or pound, in three distinct cuts: Moist, Lean and Burnt Ends.
	
	Other menu items include: Spare Ribs; house-made Sausage; half or whole Chicken; and Pork Belly, all slowly smoked and served with white bread and pickles. Sides, available small or large, include: Pinto Beans; Green Bean Casserole, Root beer Chili, Coleslaw; Collard Greens; Macaroni and Cheese; and Vegan Chili.
	<h3>The Drinks </h3>
	
	In keeping with their bare-bones, Texas-frontier aesthetic, Percy Street&acute;s craft beers are served exclusively on draft at the poured concrete bar, lit from above by illuminated green glass beer growlers. Beers include Sly Fox Rauchbier (available in Pennsylvania exclusively at the restaurant) as well as a hand-crafted Root Beer from Yard&acute;s Brewing Company.
	
	Cocktails include: FM 423, with Tito handmade vodka, peach juice and sweet tea; Jack & Ginger, with Jack Daniels, Canton ginger liqueur, lime cordial and ginger ale; and Cherry Cola, with Beam rye, cherry Heering, DiSaronno and cola.
	
	<h3>Atmosphere </h3>
	
	Percy Street&acute;s simple, rustic decor was created by Elisabeth Knapp, who also designed Cook and Solomonov Xochitl and Zahav restaurants.
	
	Her frontier-influenced design focuses on the fire engine red smokers, visible through a window in the dining room and bar area. The restaurant features light wood floors, weathered red paint, a working jukebox and custom “blackboard walls,” large panels of schoolhouse blackboards that can be rearranged to create private dining areas throughout the 80-seat space.
	
	Seating in the form of repurposed church pews, and bare light bulbs overhead in the dining room lend to the restaurant Texas-esque aesthetic.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Feature')),
            "post_tags" => array('Sample Tag1'),
            "geodir_video" => '',
            "geodir_timing" => 'Percy Street is closed on Mondays. The restaurant is also open for weekend lunch/brunch from 11:30 a.m. to 2:30 p.m.',
            "geodir_contact" => '(143) 222-12344',
            "geodir_email" => 'info@percystreet.com',
            "geodir_website" => 'http://www.percystreet.com/',
            "geodir_twitter" => 'http://twitter.com/percystreet',
            "geodir_facebook" => 'http://facebook.com/percystreet',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 4///

        break;
    case 25:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 5//
        $image_array[] = "$dummy_image_url/restaurants4.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'The Fountain Restaurant',
            "post_desc" => '
	The Fountain Restaurant in the Four Seasons Hotel Philadelphia has received seemingly every type of accolade there is, from top honors in Gourmet magazine to Forbes Travel Guide&acute;s 2010 Five Star award to a perfect Five Diamond rating from AAA. It&acute;s been a Philadelphia favorite for special occasion meals for decades.
	
	Additionally rated as the best restaurant in Philadelphia by Zagat&acute;s, the Fountain Restaurant overlooks the majestic Swann Memorial Fountain sculpture by Alexander Stirling Calder in the center of Logan Square. You&acute;ll also enjoy sweeping views of the grand Benjamin Franklin Parkway and its gorgeous Beaux Arts architecture.
	
	Fountain is definitely an incredibly romantic restaurant, so if you&acute;re visiting with a special someone, you will surely impress them with a meal at Fountain.
	
	You can order a la carte or select the prix fix option to enjoy the “spontaneous tastes” menu which gives the chef control of a few courses. The menu changes regularly, but you can expect to see globaly influenced items like Pan-fried Veal Sweetbreads, Braised Dover Sole Roulade, Sautéed Venison Medallions and Roasted Australian Lamb Saddle.
	
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants')),
            "post_tags" => array('food'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 11:30 a.m. to 2:30 p.m.',
            "geodir_contact" => '(103) 100-12344',
            "geodir_email" => 'info@fourseasons.com',
            "geodir_website" => 'http://www.fourseasons.com/philadelphia/dining',
            "geodir_twitter" => 'http://twitter.com/fourseasons',
            "geodir_facebook" => 'http://facebook.com/fourseasons',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 5///
        break;
    case 26:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 6//
        $image_array[] = "$dummy_image_url/restaurants11.jpg";
        $image_array[] = "$dummy_image_url/restaurants10.jpg";
        $image_array[] = "$dummy_image_url/restaurants3.jpg";
        $image_array[] = "$dummy_image_url/restaurants1.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Lacroix at The Rittenhouse',
            "post_desc" => '
	A deluxe hotel like The Rittenhouse deserves a deluxe restaurant, a fitting description for Lacroix, named “Restaurant of the Year” in 2003 by Esquire magazine.
	
	Located on the second floor of the Rittenhouse Hotel, Lacroix features elegant décor and a broad view of Rittenhouse Square, which combine to make the ambiance at Lacroix as enjoyable as the meal itself.
	
	The creative French menu changes with the season and in the past has included favorites like pumpkin soup with fried shallots and tuna steak with salmis sauce. The wine list is excellent and extensive - thanks to the 4,000-bottle wine cellar .
	
	The tasting menus can be catered to your preference - three-, four- and five-course selections are offered at set prices during lunch and dinner.
	
	Sunday Brunch at Lacroix - which features such delectable dishes as baby lamb chops with garlic crust and banyuls sauce, niman ranch smoked bacon, quail eggs with artichoke, golden beet and shiitakes, and french baguette toast with apple, raspberry and rosemary jam - is also highly recommended.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants')),
            "post_tags" => array('food'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 10:30 a.m. to 6:30 p.m.',
            "geodir_contact" => '(113) 121-12344',
            "geodir_email" => 'info@rittenhousehotel.com',
            "geodir_website" => 'http://www.rittenhousehotel.com/lacroix.cfm',
            "geodir_twitter" => 'http://twitter.com/rittenhousehotel',
            "geodir_facebook" => 'http://facebook.com/rittenhousehotel',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 6///

        break;
    case 27:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 7//
        $image_array[] = "$dummy_image_url/restaurants12.jpg";
        $image_array[] = "$dummy_image_url/restaurants13.jpg";
        $image_array[] = "$dummy_image_url/restaurants14.jpg";
        $image_array[] = "$dummy_image_url/restaurants15.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Lacroix at The Rittenhouse',
            "post_desc" => '
	A deluxe hotel like The Rittenhouse deserves a deluxe restaurant, a fitting description for Lacroix, named “Restaurant of the Year” in 2003 by Esquire magazine.
	
	Located on the second floor of the Rittenhouse Hotel, Lacroix features elegant décor and a broad view of Rittenhouse Square, which combine to make the ambiance at Lacroix as enjoyable as the meal itself.
	
	The creative French menu changes with the season and in the past has included favorites like pumpkin soup with fried shallots and tuna steak with salmis sauce. The wine list is excellent and extensive - thanks to the 4,000-bottle wine cellar .
	
	The tasting menus can be catered to your preference - three-, four- and five-course selections are offered at set prices during lunch and dinner.
	
	Sunday Brunch at Lacroix - which features such delectable dishes as baby lamb chops with garlic crust and banyuls sauce, niman ranch smoked bacon, quail eggs with artichoke, golden beet and shiitakes, and french baguette toast with apple, raspberry and rosemary jam - is also highly recommended.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Food Nightlife')),
            "post_tags" => array('food'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 10:30 a.m. to 6:30 p.m.',
            "geodir_contact" => '(113) 121-12344',
            "geodir_email" => 'info@zamarestaurant.com',
            "geodir_website" => 'http://www.zamarestaurant.com/',
            "geodir_twitter" => 'http://twitter.com/zamarestaurant',
            "geodir_facebook" => 'http://facebook.com/zamarestaurant',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 7///

        break;
    case 28:

        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 8//
        $image_array[] = "$dummy_image_url/restaurants16.jpg";
        $image_array[] = "$dummy_image_url/restaurants17.jpg";
        $image_array[] = "$dummy_image_url/restaurants18.jpg";
        $image_array[] = "$dummy_image_url/restaurants19.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Sampan',
            "post_desc" => '
	Chef and charismatic television star Michael Schulson returns to Philadelphia with the opening of Sampan, a modern Asian restaurant where he serves the acclaimed cuisine that has made him one of the country&acute;s highly sought-after culinary talents.
	
	Schulson returns to Philadelphia after having opened Buddakan in New York City for Stephen Starr and Izakaya at the Borgata in Atlantic City and then having gone on to star in Style network&acute;s popular series Pantry Raid and TLC Ultimate Cake Off.
	
	Chef Schulson has been looking forward to a time when he could come back to Philadelphia and cook in a small, personal space, which he has now achieved with Sampan. To him, Sampan is a place where he can prepare serious food from across Asia while interacting with guests and sharing his love of the cuisine with them.
	
	<h3>Design </h3>
	
	Designed by Philadelphia&acute;s Sparks Design, Sampan features distressed metals, reclaimed timber and a rustic, natural aesthetic anchored by a custom-crafted, color washed painting that lends a warm ambiance to the space. In contrast to the large scale restaurants such as Manhattan&acute;s Buddakan and West Philadelphia&acute;s Pod, where Chef Schulson served as executive chef, this 80-seat gem is a cozy setting that allows his passion for Asian flavors, thoughtfully prepared, to shine.
	
	<h3>Cuisine </h3>
	
	Schulson&acute;s says his mission at Sampan is to make the more exotic and unfamiliar flavors of Asian cuisine accessible and inviting to American palates.
	
	Sampan menu is composed of a variety of small plates - Chef Schulson&acute;s preferred way to cook because it is ideal for sampling and sharing. Tempting dishes include: his signature Edamame Dumplings, with truffles, shoots and sake broth; Thai Chicken Wings with pickles, mint and basil; Pekin Duck with tamarind pancakes, scallions and cucumbers; Lamb Satay with yakitori, penko and ginger; Crispy Chili Crab with Hong Kong noodles, black beans and ginger chips; Mao Pao Tofu with pork, ginger and garlic; and Wild Mushroom Salad with goat cheese, puffed rice and truffles.
	
	Prices range from $5 to $19.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Food Nightlife')),
            "post_tags" => array('restaurant'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 10:30 a.m. to 6:30 p.m.',
            "geodir_contact" => '(000) 111-2222',
            "geodir_email" => 'info@sampanphilly.com',
            "geodir_website" => 'http://www.sampanphilly.com/',
            "geodir_twitter" => 'http://twitter.com/sampanphilly',
            "geodir_facebook" => 'http://facebook.com/sampanphilly',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 8///

        break;
    case 29:

        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 9//
        $image_array[] = "$dummy_image_url/restaurants17.jpg";
        $image_array[] = "$dummy_image_url/restaurants16.jpg";
        $image_array[] = "$dummy_image_url/restaurants18.jpg";
        $image_array[] = "$dummy_image_url/restaurants19.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Morimoto',
            "post_desc" => '
	Stephen Starr creative Japanese restaurant has garnered all kinds of national and international attention since opening a few years back. Located a block from Independence Hall on Chestnut Street, Morimoto has an interior - awash in glass and colors - that is both striking and serene in its design.
	
	The restaurant&acute;s namesake and head chef, Morimoto (of Food Network&acute;s Iron Chef fame), has created a menu offering the very best in contemporary Japanese cusine. While regulars flock here for the exquisitely prepared sushi, Morimoto offers diners a broad spectrum of flavors that delve beyond nigiri and sashimi.
	
	In recent years, the restaurant has made it onto Gourmet magazine&acute;s “Best Restaurants in America” list and Conde Nast Traveler magazine 50 Hot Tables in America. Today Morimoto remains one of the hottest spots to dine in Center City and continues to receive rave reviews from regulars and first-timers alike.
	
	That said, be sure to call ahead for reservations.
	
	<h3>Insider Tip </h3>
	
	The mezzanine level lounge is a great spot to have a pre-meal cocktail while waiting for your table. You can enjoy a sake or try a “Sakura” - a cosmo made with Sake - in the sleek space that overlooks the brilliant restaurant below.
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Food Nightlife', 'Feature')),
            "post_tags" => array('America'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 10:30 a.m. to 6:30 p.m.',
            "geodir_contact" => '(000) 111-2222',
            "geodir_email" => 'info@morimotorestaurant.com',
            "geodir_website" => 'http://www.morimotorestaurant.com/',
            "geodir_twitter" => 'http://twitter.com/morimotorestaurant',
            "geodir_facebook" => 'http://facebook.com/morimotorestaurant',
            "post_dummy" => '1'
        );

        ////post end///
        /// Restaurants ////post start 9///
        break;
    case 30:


        $image_array = array();
        $post_meta = array();

        /// Restaurants ////post start 10//
        $image_array[] = "$dummy_image_url/restaurants19.jpg";
        $image_array[] = "$dummy_image_url/restaurants17.jpg";
        $image_array[] = "$dummy_image_url/restaurants18.jpg";
        $image_array[] = "$dummy_image_url/restaurants16.jpg";
        $image_array[] = "$dummy_image_url/restaurants5.jpg";
        $image_array[] = "$dummy_image_url/restaurants6.jpg";
        $image_array[] = "$dummy_image_url/restaurants7.jpg";
        $image_array[] = "$dummy_image_url/restaurants8.jpg";
        $image_array[] = "$dummy_image_url/restaurants9.jpg";
        $image_array[] = "$dummy_image_url/restaurants2.jpg";
        $image_array[] = "$dummy_image_url/restaurants4.jpg";

        $post_info[] = array(
            "listing_type" => $post_type,
            "post_title" => 'Buddakan',
            "post_desc" => '
	<h3>The Experience </h3>
	
	A towering gilded statue of the Buddha generates elegant calm in this 175-seat, Pan Asian restaurant with sleek, modern decor. Immensely popular, Buddakan is a restaurant that is great for both large parties and intimate dinners.
	
	Located in the heart of the bustling Old City neighborhood, Buddakan features two full bars as well as a popular (and hard to reserve) 20-person, lit-from-within, community table for sharing food and conversation.
	
	The fare is top notch - appetizers include seared kobe beef carpaccio, endamme ravioli, miso tuna tartare and tea smoked spareribs. For the main course, delve into delicious dishes like Japanese black cod, wasabi crusted filet mignon, roasted ponzu chicken and collosal tempura shrimp. For dessert, the chocolate bento box will please just about anyone.
	
	Be sure to make your reservation before coming to town as Buddakan fills up quickly especially on weekends. Better yet, make your reservation right now .
	',
            "post_images" => $image_array,
            "post_category" => array($post_type.'category' => array('Restaurants', 'Food Nightlife')),
            "post_tags" => array('America'),
            "geodir_video" => '',
            "geodir_timing" => 'The restaurant is also open for weekend lunch/brunch from 10:30 a.m. to 6:30 p.m.',
            "geodir_contact" => '(000) 111-2222',
            "geodir_email" => 'info@buddakan.com',
            "geodir_website" => 'http://www.buddakan.com/',
            "geodir_twitter" => 'http://twitter.com/buddakan',
            "geodir_facebook" => 'http://facebook.com/buddakan',
            "post_dummy" => '1'
        );
        break;

    ////post end///
    /// Restaurants ////post start 10///
} // end of switch

foreach ($post_info as $post_info) {
    $default_location = geodir_get_default_location();
    if ($city_bound_lat1 > $city_bound_lat2)
        $dummy_post_latitude = geodir_random_float(geodir_random_float($city_bound_lat1, $city_bound_lat2), geodir_random_float($city_bound_lat2, $city_bound_lat1));
    else
        $dummy_post_latitude = geodir_random_float(geodir_random_float($city_bound_lat2, $city_bound_lat1), geodir_random_float($city_bound_lat1, $city_bound_lat2));


    if ($city_bound_lng1 > $city_bound_lng2)
        $dummy_post_longitude = geodir_random_float(geodir_random_float($city_bound_lng1, $city_bound_lng2), geodir_random_float($city_bound_lng2, $city_bound_lng1));
    else
        $dummy_post_longitude = geodir_random_float(geodir_random_float($city_bound_lng2, $city_bound_lng1), geodir_random_float($city_bound_lng1, $city_bound_lng2));

    $load_map = get_option('geodir_load_map');
    
    if ($load_map == 'osm') {
        $post_address = geodir_get_osm_address_by_lat_lan($dummy_post_latitude, $dummy_post_longitude);
    } else {
        $post_address = geodir_get_address_by_lat_lan($dummy_post_latitude, $dummy_post_longitude);
    }

    $postal_code = '';
    if (!empty($post_address)) {
        if ($load_map == 'osm') {
            $address = !empty($post_address->formatted_address) ? $post_address->formatted_address : '';
            $postal_code = !empty($post_address->address->postcode) ? $post_address->address->postcode : '';
        } else {
            $addresses = array();
            $addresses_default = array();
            
            foreach ($post_address as $add_key => $add_value) {
                if ($add_key < 2 && !empty($add_value->long_name)) {
                    $addresses_default[] = $add_value->long_name;
                }
                if ($add_value->types[0] == 'postal_code') {
                    $postal_code = $add_value->long_name;
                }
                if ($add_value->types[0] == 'street_number') {
                    $addresses[] = $add_value->long_name;
                }
                if ($add_value->types[0] == 'route') {
                    $addresses[] = $add_value->long_name;
                }
                if ($add_value->types[0] == 'neighborhood') {
                    $addresses[] = $add_value->long_name;
                }
                if ($add_value->types[0] == 'sublocality') {
                    $addresses[] = $add_value->long_name;
                }
            }
            $address = !empty($addresses) ? implode(', ', $addresses) : (!empty($addresses_default) ? implode(', ', $addresses_default) : '');
        }

        $post_info['post_address'] = !empty($address) ? $address : $default_location->city;
        $post_info['post_city'] = $default_location->city;
        $post_info['post_region'] = $default_location->region;
        $post_info['post_country'] = $default_location->country;
        $post_info['post_zip'] = $postal_code;
        $post_info['post_latitude'] = $dummy_post_latitude;
        $post_info['post_longitude'] = $dummy_post_longitude;
    }
    
    geodir_save_listing($post_info, true);
}

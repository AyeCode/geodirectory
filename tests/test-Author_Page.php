<?php
class AuthorPage extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testAuthorPage()
    {
        global $current_user;

        $user_id = $current_user->ID;



        // Add listing

        $args = array(
            'listing_type' => 'gd_place',
            'post_title' => 'Test Listing Title 2',
            'post_desc' => 'Test Desc',
            'post_tags' => 'test1,test2',
            'post_address' => 'New York City Hall',
            'post_zip' => '10007',
            'post_mapview' => 'ROADMAP',
            'post_mapzoom' => '10',
            'geodir_timing' => '10.00 am to 6 pm every day',
            'geodir_contact' => '1234567890',
            'geodir_email' => 'test@test.com',
            'geodir_website' => 'http://test.com',
            'geodir_twitter' => 'http://twitter.com/test',
            'geodir_facebook' => 'http://facebook.com/test',
            'geodir_special_offers' => 'Test offer'
        );
        $post_id = geodir_save_listing($args, true);

        $this->assertTrue(is_int($post_id));

        $count = count_user_posts( $user_id, "gd_place"  );

        $this->assertTrue(is_int((int) $count));



    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>
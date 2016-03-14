<?php
class EditListing extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
    }

    public function testEditListing()
    {
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place',
            'posts_per_page' => 1,
        );

        $all_posts = new WP_Query( $query_args );
        $post_id = null;
        while ( $all_posts->have_posts() ) : $all_posts->the_post();
            $post_id = get_the_ID();
        endwhile;

        $this->assertTrue(is_int($post_id));

        $args = array(
            'pid' => $post_id,
            'listing_type' => 'gd_place',
            'post_title' => 'Test Listing Title Modified',
            'post_desc' => 'Test Desc',
            'post_tags' => 'test1,test2',
            'post_address' => 'New York City Hall',
            'post_zip' => '10007',
            'post_latitude' => '40.7127837',
            'post_longitude' => '-74.00594130000002',
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
        $saved_post_id = geodir_save_listing($args, true);

        $this->assertTrue(is_int($saved_post_id));

        $title = get_the_title($post_id);

        $this->assertEquals('Test Listing Title Modified', $title);


    }


    public function tearDown()
    {
        parent::tearDown();
    }
}
?>
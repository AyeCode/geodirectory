<?php
class TestDummyData extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
        geodir_delete_dummy_posts();
    }

    public function testCreatePlaces()
    {
        global $dummy_post_index, $city_bound_lat1, $city_bound_lng1, $city_bound_lat2, $city_bound_lng2;

        global $geodir_post_custom_fields_cache;
        $geodir_post_custom_fields_cache = array();

        $city_bound_lat1 = 40.4960439;
        $city_bound_lng1 = -74.2557349;
        $city_bound_lat2 = 40.91525559999999;
        $city_bound_lng2 = -73.7002721;


        $dummy_post_index = 30;
        test_create_dummy_posts(30);

        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place'
        );

        $all_posts = new WP_Query( $query_args );

        $total_posts = $all_posts->found_posts;

        $this->assertTrue((int) $total_posts > 0);

    }

    public function testDeletePlaces()
    {
        geodir_delete_dummy_posts();
    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>
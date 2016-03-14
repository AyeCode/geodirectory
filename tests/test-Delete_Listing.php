<?php
class DeleteListing extends WP_UnitTestCase
{
    public function setUp()
    {
        parent::setUp();
        wp_set_current_user(1);
        deactivate_plugins('buddypress/bp-loader.php');
        remove_filter( 'muplugins_loaded', '_tests_load_bp_registration_options' );
    }

    public function testDeleteListing()
    {
        $query_args = array(
            'post_status' => 'publish',
            'post_type' => 'gd_place',
            'posts_per_page' => 1,
            'author' => 1
        );

        $all_posts = new WP_Query( $query_args );
        $post_id = null;
        while ( $all_posts->have_posts() ) : $all_posts->the_post();
            $post_id = get_the_ID();
        endwhile;

        $this->assertTrue(is_int($post_id));

        $lastid = wp_delete_post($post_id);

        $this->assertTrue(is_int($lastid->ID));

    }

    public function tearDown()
    {
        parent::tearDown();
    }
}
?>